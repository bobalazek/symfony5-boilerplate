<?php

namespace App\Controller\Auth;

use App\Entity\User;
use App\Entity\UserAction;
use App\Entity\UserTfaEmail;
use App\Entity\UserTfaMethod;
use App\Entity\UserTfaRecoveryCode;
use App\Form\LoginTfaType;
use App\Manager\EmailManager;
use App\Manager\GoogleAuthenticatorManager;
use App\Manager\UserActionManager;
use App\Manager\UserDeviceManager;
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
 * Class AuthLoginTfaController.
 */
class AuthLoginTfaController extends AbstractController
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
     * @var UserDeviceManager
     */
    private $userDeviceManager;

    /**
     * @var GoogleAuthenticatorManager
     */
    private $googleAuthenticatorManager;

    /**
     * @var UserTfaManager
     */
    private $userTfaManager;

    /**
     * @var EmailManager
     */
    private $emailManager;

    public function __construct(
        TranslatorInterface $translator,
        ParameterBagInterface $params,
        EntityManagerInterface $em,
        UserActionManager $userActionManager,
        UserDeviceManager $userDeviceManager,
        GoogleAuthenticatorManager $googleAuthenticatorManager,
        UserTfaManager $userTfaManager,
        EmailManager $emailManager
    ) {
        $this->translator = $translator;
        $this->params = $params;
        $this->em = $em;
        $this->userActionManager = $userActionManager;
        $this->userDeviceManager = $userDeviceManager;
        $this->googleAuthenticatorManager = $googleAuthenticatorManager;
        $this->userTfaManager = $userTfaManager;
        $this->emailManager = $emailManager;
    }

    /**
     * @Route("/auth/login/tfa", name="auth.login.tfa")
     */
    public function index(Request $request): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('auth.login');
        }

        $method = $request->getSession()->get('tfa_method');
        $inProgress = $request->getSession()->get('tfa_in_progress');
        if (!$inProgress) {
            $this->addFlash(
                'danger',
                $this->translator->trans('login.flash.already_logged_in', [], 'auth')
            );

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

            return $this->redirectToRoute('auth.login.tfa');
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
                $method,
                trim($formData['code'])
            );
        }

        return $this->render('contents/auth/login/tfa.html.twig', [
            'form' => $form->createView(),
            'method' => $method,
            'methods' => $methods,
            'available_methods' => $availableMethods,
        ]);
    }

    private function _handleNonEmailMethod(Request $request, User $user, $method, $code)
    {
        $failedAttemptsCount = $this->_countFailedAttempts($user);
        if ($failedAttemptsCount > 5) {
            $this->addFlash(
                'danger',
                $this->translator->trans('login.tfa.flash.too_many_attempts', [], 'auth')
            );

            return $this->redirectToRoute('auth.login.tfa');
        }

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
                    $this->translator->trans('login.tfa.google_authenticator.flash.code_invalid', [], 'auth')
                );

                $this->userActionManager->add(
                    'login.tfa.fail',
                    'User tried to enter 2FA but failed',
                    [
                        'method' => UserTfaMethod::METHOD_GOOGLE_AUTHENTICATOR,
                        'code' => $code,
                    ]
                );

                return $this->redirectToRoute('auth.login.tfa');
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
                    $this->translator->trans('login.tfa.recovery_codes.flash.code_invalid', [], 'auth')
                );

                $this->userActionManager->add(
                    'login.tfa.fail',
                    'User tried to enter 2FA but failed',
                    [
                        'method' => UserTfaMethod::METHOD_RECOVERY_CODES,
                        'code' => $code,
                    ]
                );

                return $this->redirectToRoute('auth.login.tfa');
            }

            $userTfaRecoveryCode->setUsedAt(new \DateTime());

            $this->em->persist($userTfaRecoveryCode);
            $this->em->flush();
        }

        return $this->_afterSuccess($request, $method);
    }

    private function _handleEmailMethod(User $user)
    {
        $recentUserTfaEmail = $this->em
            ->getRepository(UserTfaEmail::class)
            ->createQueryBuilder('ute')
            ->where('ute.user = :user AND ute.createdAt > :createdAt')
            ->setMaxResults(1)
            ->setParameter('user', $user)
            ->setParameter('createdAt', new \DateTime('-15 minutes'))
            ->getQuery()
            ->getOneOrNullResult()
        ;
        if ($recentUserTfaEmail) {
            $this->addFlash(
                'danger',
                $this->translator->trans('login.tfa.email.flash.code_already_sent_recently', [], 'auth')
            );

            return $this->redirectToRoute('auth.login.tfa');
        }

        $userTfaEmail = new UserTfaEmail();
        $userTfaEmail
            ->setCode(StringHelper::generate(32, false))
            ->setUser($user)
        ;

        $this->em->persist($userTfaEmail);
        $this->em->flush();

        $this->emailManager->sendTfaConfirm($user, $userTfaEmail);

        $this->userActionManager->add(
            'login.tfa.email',
            'User was sent an TFA email'
        );

        $this->addFlash(
            'success',
            $this->translator->trans('login.tfa.email.flash.code_sent', [], 'auth')
        );

        return $this->redirectToRoute('auth.login.tfa');
    }

    private function _handleEmailCodeQuery(Request $request, User $user, string $code)
    {
        $userTfaEmail = $this->em
            ->getRepository(UserTfaEmail::class)
            ->createQueryBuilder('ute')
            ->where('
                ute.user = :user AND
                ute.code = :code AND
                ute.usedAt IS NULL AND
                ute.createdAt > :createdAt
            ')
            ->setMaxResults(1)
            ->setParameter('user', $user)
            ->setParameter('code', strtoupper($code))
            ->setParameter('createdAt', new \DateTime('-15 minutes'))
            ->getQuery()
            ->getOneOrNullResult()
        ;
        if (!$userTfaEmail) {
            $this->addFlash(
                'danger',
                $this->translator->trans('login.tfa.email.flash.code_invalid', [], 'auth')
            );

            $this->userActionManager->add(
                'login.tfa.fail',
                'User tried to enter 2FA but failed',
                [
                    'method' => UserTfaMethod::METHOD_EMAIL,
                    'code' => $code,
                ]
            );

            return $this->redirectToRoute('auth.login.tfa');
        }

        $userTfaEmail->setUsedAt(new \DateTime());

        $this->em->persist($userTfaEmail);
        $this->em->flush();

        return $this->_afterSuccess($request, UserTfaMethod::METHOD_EMAIL);
    }

    private function _countFailedAttempts(User $user): int
    {
        return $this->em
            ->getRepository(UserAction::class)
            ->createQueryBuilder('ua')
            ->select('COUNT(ua.id)')
            ->where('
                ua.user = :user AND
                ua.key = :key AND
                ua.createdAt > :createdAt
            ')
            ->setParameter('user', $user)
            ->setParameter('key', 'login.tfa.fail')
            ->setParameter('createdAt', new \DateTime('-15 minutes'))
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    private function _afterSuccess(Request $request, string $method)
    {
        $request->getSession()->remove('tfa_method');
        $request->getSession()->remove('tfa_in_progress');

        $user = $this->getUser();

        $this->userDeviceManager->setCurrentAsTrusted($user, $request);

        $this->addFlash(
            'success',
            $this->translator->trans('login.tfa.flash.success', [], 'auth')
        );

        $this->userActionManager->add(
            'login.tfa',
            'User successfully entered the 2FA code',
            [
                'method' => $method,
            ]
        );

        $this->emailManager->sendNewLogin($user, $request);

        return $this->redirectToRoute('home');
    }
}
