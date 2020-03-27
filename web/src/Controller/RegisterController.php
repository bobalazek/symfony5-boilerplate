<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\UserFollower;
use App\Form\RegisterType;
use App\Manager\OauthManager;
use App\Security\Guard\Authenticator\LoginFormAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class RegisterController.
 */
class RegisterController extends AbstractController
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
     * @var \Swift_Mailer
     */
    private $mailer;

    /**
     * @var OauthManager
     */
    private $oauthManager;

    public function __construct(
        TranslatorInterface $translator,
        ParameterBagInterface $params,
        EntityManagerInterface $em,
        \Swift_Mailer $mailer,
        OauthManager $oauthManager
    ) {
        $this->translator = $translator;
        $this->params = $params;
        $this->em = $em;
        $this->mailer = $mailer;
        $this->oauthManager = $oauthManager;
    }

    /**
     * @Route("/register", name="register")
     */
    public function index(
        Request $request,
        UserPasswordEncoderInterface $passwordEncoder
    ): Response {
        if ($this->getUser()) {
            return $this->redirectToRoute('home');
        }

        $oauth = $request->query->get('oauth');

        $user = new User();

        if ('facebook' === $oauth) {
            try {
                $oauthUser = $this->oauthManager->getFacebookUser($request);
                $user
                    ->setOauthFacebookId($oauthUser['id'])
                    ->setEmail($oauthUser['email'])
                ;
            } catch (\Exception $e) {
                $oauth = null;

                $this->addFlash(
                    'danger',
                    $e->getMessage()
                );
            }
        } elseif ('google' === $oauth) {
            try {
                $oauthUser = $this->oauthManager->getGoogleUser($request);
                $user
                    ->setOauthGoogleId($oauthUser['id'])
                    ->setEmail($oauthUser['email'])
                ;
            } catch (\Exception $e) {
                $oauth = null;

                $this->addFlash(
                    'danger',
                    $e->getMessage()
                );
            }
        }

        $form = $this->createForm(RegisterType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user
                ->setPassword(
                    $passwordEncoder->encodePassword(
                        $user,
                        $form->get('plainPassword')->getData()
                    )
                )
                ->setEmailConfirmCode(md5(random_bytes(32)))
            ;

            $this->em->persist($user);
            $this->em->flush();

            $emailSubject = $this->translator->trans('email_confirm.subject', [
                '%app_name%' => $this->params->get('app.name'),
            ], 'emails');
            $message = (new \Swift_Message($emailSubject))
                ->setFrom($this->params->get('app.mailer_from'))
                ->setTo($user->getEmail())
                ->setBody(
                    $this->renderView(
                        'emails/email_confirm.html.twig',
                        ['user' => $user]
                    )
                )
            ;
            $this->mailer->send($message);

            // The default user (corco) should follow the newly registered user
            $defaultUser = $this->em->getRepository(User::class)->findOneById(1);
            if ($defaultUser) {
                $userFollower = new UserFollower();
                $userFollower
                    ->setUser($user)
                    ->setUserFollowing($defaultUser)
                    ->setStatus(UserFollower::STATUS_APPROVED)
                ;

                $this->em->persist($userFollower);
                $this->em->flush();
            }

            return $this->render('contents/register/success.html.twig');
        }

        return $this->render('contents/register/index.html.twig', [
            'form' => $form->createView(),
            'oauth' => $oauth,
        ]);
    }

    /**
     * @Route("/email-confirm", name="email_confirm")
     */
    public function emailConfirm(
        Request $request,
        GuardAuthenticatorHandler $guardHandler,
        LoginFormAuthenticator $authenticator
    ): Response {
        $email = $request->query->get('email');
        $emailConfirmCode = $request->query->get('email_confirm_code');

        $user = $this->em->getRepository(User::class)
            ->findOneBy([
                'email' => $email,
                'emailConfirmCode' => $emailConfirmCode,
            ]);
        if (!$user) {
            throw $this->createNotFoundException($this->translator->trans('email_confirm.user_not_found', [], 'login'));
        }

        $user->setEmailConfirmedAt(new \DateTime());

        $this->em->persist($user);
        $this->em->flush();

        $emailSubject = $this->translator->trans('email_confirm_success.subject', [
            '%app_name%' => $this->params->get('app.name'),
        ], 'emails');
        $message = (new \Swift_Message($emailSubject))
            ->setFrom($this->params->get('app.mailer_from'))
            ->setTo($user->getEmail())
            ->setBody(
                $this->renderView(
                    'emails/email_confirm_success.html.twig',
                    ['user' => $user]
                )
            )
        ;
        $this->mailer->send($message);

        $this->addFlash(
            'success',
            $this->translator->trans('email_confirm.flash.success', [], 'login')
        );

        return $guardHandler->authenticateUserAndHandleSuccess(
            $user,
            $request,
            $authenticator,
            'main'
        );
    }
}
