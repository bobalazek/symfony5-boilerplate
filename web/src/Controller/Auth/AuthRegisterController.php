<?php

namespace App\Controller\Auth;

use App\Entity\User;
use App\Entity\UserOauthProvider;
use App\Form\Type\RegisterType;
use App\Manager\EmailManager;
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
 * Class AuthRegisterController.
 */
class AuthRegisterController extends AbstractController
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
     * @var EmailManager
     */
    private $emailManager;

    public function __construct(
        TranslatorInterface $translator,
        ParameterBagInterface $params,
        EntityManagerInterface $em,
        EmailManager $emailManager
    ) {
        $this->translator = $translator;
        $this->params = $params;
        $this->em = $em;
        $this->emailManager = $emailManager;
    }

    /**
     * @Route("/auth/register", name="auth.register")
     */
    public function index(
        Request $request,
        UserPasswordEncoderInterface $passwordEncoder,
        OauthManager $oauthManager
    ): Response {
        if ($this->getUser()) {
            return $this->redirectToRoute('home');
        }

        $oauth = $request->query->get('oauth');

        $user = new User();

        if ($oauth) {
            try {
                $oauthUser = $oauthManager->getUser($oauth);

                $user->setEmail($oauthUser->getEmail());

                $userOauthProvider = new UserOauthProvider();
                $userOauthProvider
                    ->setProvider($provider)
                    ->setProviderId($oauthUser->getId())
                    ->setData($oauthUser->getRawData())
                ;

                $user->addUserOauthProvider($userOauthProvider);
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

            $this->emailManager->sendEmailConfirm($user);

            return $this->render('contents/auth/register/success.html.twig');
        }

        return $this->render('contents/auth/register/index.html.twig', [
            'form' => $form->createView(),
            'oauth' => $oauth,
        ]);
    }

    /**
     * @Route("/auth/email-confirm", name="auth.email_confirm")
     */
    public function emailConfirm(
        Request $request,
        GuardAuthenticatorHandler $guardHandler,
        LoginFormAuthenticator $authenticator
    ): Response {
        $email = $request->query->get('email');
        $emailConfirmCode = $request->query->get('email_confirm_code');

        /** @var User|null $user */
        $user = $this->em
            ->getRepository(User::class)
            ->findOneBy([
                'email' => $email,
                'emailConfirmCode' => $emailConfirmCode,
            ])
        ;
        if (!$user) {
            throw $this->createNotFoundException($this->translator->trans('login.email_confirm.user_not_found', [], 'auth'));
        }

        $user->setEmailConfirmedAt(new \DateTime());

        $this->em->persist($user);
        $this->em->flush();

        $this->emailManager->sendEmailConfirmSuccess($user);

        $this->addFlash(
            'success',
            $this->translator->trans('login.email_confirm.flash.success', [], 'auth')
        );

        return $guardHandler->authenticateUserAndHandleSuccess(
            $user,
            $request,
            $authenticator,
            'main'
        );
    }
}
