<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\UserTfaEmail;
use App\Entity\UserTfaMethod;
use App\Entity\UserTfaRecoveryCode;
use App\Form\LoginTfaType;
use App\Manager\GoogleAuthenticatorManager;
use App\Manager\UserActionManager;
use App\Manager\UserTfaManager;
use App\Utils\StringHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class LoginTfaController.
 */
class LoginTfaController extends AbstractController
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var ParameterBagInterface
     */
    private $params;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var UserActionManager
     */
    private $userActionManager;

    /**
     * @var GoogleAuthenticatorManager
     */
    private $googleAuthenticatorManager;

    /**
     * @var UserTfaManager
     */
    private $userTfaManager;

    public function __construct(
        TranslatorInterface $translator,
        ParameterBagInterface $params,
        EntityManagerInterface $em,
        UserActionManager $userActionManager,
        GoogleAuthenticatorManager $googleAuthenticatorManager,
        UserTfaManager $userTfaManager,
        \Swift_Mailer $mailer
    ) {
        $this->translator = $translator;
        $this->params = $params;
        $this->em = $em;
        $this->userActionManager = $userActionManager;
        $this->googleAuthenticatorManager = $googleAuthenticatorManager;
        $this->userTfaManager = $userTfaManager;
        $this->mailer = $mailer;
    }

    /**
     * @Route("/login/tfa", name="login.tfa")
     */
    public function index(Request $request): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('login');
        }

        $method = $request->getSession()->get('tfa_method');
        $inProgress = $request->getSession()->get('tfa_in_progress');
        if (!$inProgress) {
            return $this->redirectToRoute('home');
        }

        $methods = $this->params->get('app.tfa_methods');
        $availableMethods = $this->userTfaManager->getAvailableMethods();

        // Code query
        $code = $request->query->get('code');
        if ($code) {
            return $this->_handleEmailCodeQuery($request, $user, $code);
        }

        // Switch method query
        $switchMethod = $request->query->get('switch_method');
        if (
            $switchMethod &&
            in_array($switchMethod, $availableMethods)
        ) {
            $request->getSession()->set('tfa_method', $switchMethod);

            return $this->redirectToRoute('login.tfa');
        }

        $isEmailMethod = UserTfaMethod::METHOD_EMAIL === $method;

        $form = $this->createForm(LoginTfaType::class, [], [
            'show_code_field' => !$isEmailMethod,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($isEmailMethod) {
                return $this->_handleEmailMethod($user);
            }

            $formData = $form->getData();
            return $this->_handleNonEmailMethod(
                $request,
                $user,
                trim($formData['code'])
            );
        }

        return $this->render('contents/login/tfa.html.twig', [
            'form' => $form->createView(),
            'method' => $method,
            'methods' => $methods,
            'available_methods' => $availableMethods,
        ]);
    }

    private function _handleNonEmailMethod(Request $request, User $user, $code)
    {
        // TODO: prevent infinite attempts

        if (UserTfaMethod::METHOD_GOOGLE_AUTHENTICATOR === $method) {
            $userTfaMethod = $this->em
                ->getRepository(UserTfaMethod::class)
                ->findOneBy([
                    'user' => $user,
                    'method' => UserTfaMethod::METHOD_GOOGLE_AUTHENTICATOR,
                ])
            ;
            $userTfaMethodData = $userTfaMethod->getData();
            $secret = $userTfaMethodData['secret'];
            $isCodeValid = $this->googleAuthenticatorManager->checkCode($secret, $code);
            if (!$isCodeValid) {
                $this->addFlash(
                    'danger',
                    $this->translator->trans('tfa.google_authenticator.flash.code_invalid', [], 'login')
                );

                $this->userActionManager->add(
                    'login.tfa.fail',
                    'User tried to enter 2FA but failed',
                    [
                        'method' => $method,
                        'code' => $code,
                    ]
                );

                return $this->redirectToRoute('login.tfa');
            }
        } elseif (UserTfaMethod::METHOD_RECOVERY_CODES === $method) {
            $userTfaRecoveryCode = $this->em
                ->getRepository(UserTfaRecoveryCode::class)
                ->findOneBy([
                    'user' => $user,
                    'recoveryCode' => strtoupper($code),
                    'usedAt' => null,
                ])
            ;
            if (!$userTfaRecoveryCode) {
                $this->addFlash(
                    'danger',
                    $this->translator->trans('tfa.recovery_codes.flash.code_invalid', [], 'login')
                );

                $this->userActionManager->add(
                    'login.tfa.fail',
                    'User tried to enter 2FA but failed',
                    [
                        'method' => $method,
                        'code' => $code,
                    ]
                );

                return $this->redirectToRoute('login.tfa');
            }

            $userTfaRecoveryCode->setUsedAt(new \DateTime());

            $this->em->persist($userTfaRecoveryCode);
            $this->em->flush();
        }

        return $this->_afterSuccess($request, $method);
    }

    private function _handleEmailMethod(User $user)
    {
        $userTfaEmail = new UserTfaEmail();
        $userTfaEmail
            ->setCode(StringHelper::generate(32, false))
            ->setUser($user)
        ;

        $this->em->persist($userTfaEmail);
        $this->em->flush();

        $this->addFlash(
            'success',
            $this->translator->trans('tfa.email.flash.code_sent', [], 'login')
        );

        $this->userActionManager->add(
            'login.tfa.email',
            'User was sent an TFA email'
        );

        $emailSubject = $this->translator->trans('tfa_confirm.subject', [
            '%app_name%' => $this->params->get('app.name'),
        ], 'emails');
        $message = (new \Swift_Message($emailSubject))
            ->setFrom($this->params->get('app.mailer_from'))
            ->setTo($user->getEmail())
            ->setBody(
                $this->renderView(
                    'emails/tfa_confirm.html.twig',
                    [
                        'user' => $user,
                        'user_tfa_email' => $userTfaEmail,
                    ]
                )
            )
        ;
        $this->mailer->send($message);

        return $this->redirectToRoute('login.tfa');
    }

    private function _handleEmailCodeQuery(Request $request, User $user, string $code)
    {
        $userTfaEmail = $this->em
            ->getRepository(UserTfaEmail::class)
            ->findOneBy([
                'user' => $user,
                'code' => strtoupper($code),
                'usedAt' => null,
                // TODO: is expired?
            ])
        ;
        if (!$userTfaEmail) {
            $this->addFlash(
                'danger',
                $this->translator->trans('tfa.email.flash.code_invalid', [], 'login')
            );

            $this->userActionManager->add(
                'login.tfa.fail',
                'User tried to enter 2FA but failed',
                [
                    'method' => UserTfaMethod::METHOD_EMAIL,
                    'code' => $code,
                ]
            );

            return $this->redirectToRoute('login.tfa');
        }

        $userTfaEmail->setUsedAt(new \DateTime());

        $this->em->persist($userTfaEmail);
        $this->em->flush();

        return $this->_afterSuccess($request, UserTfaMethod::METHOD_EMAIL);
    }

    private function _afterSuccess(Request $request, string $method)
    {
        $request->getSession()->remove('tfa_method');
        $request->getSession()->remove('tfa_in_progress');

        $this->addFlash(
            'success',
            $this->translator->trans('tfa.flash.success', [], 'login')
        );

        $this->userActionManager->add(
            'login.tfa',
            'User successfully entered the 2FA code',
            [
                'method' => $method,
            ]
        );

        return $this->redirectToRoute('home');
    }
}
