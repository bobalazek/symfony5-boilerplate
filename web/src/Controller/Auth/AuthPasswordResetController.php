<?php

namespace App\Controller\Auth;

use App\Entity\User;
use App\Manager\EmailManager;
use App\Manager\UserActionManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class AuthPasswordResetController.
 */
class AuthPasswordResetController extends AbstractController
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
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;

    /**
     * @var UserActionManager
     */
    private $userActionManager;

    /**
     * @var EmailManager
     */
    private $emailManager;

    public function __construct(
        TranslatorInterface $translator,
        ParameterBagInterface $params,
        EntityManagerInterface $em,
        UserPasswordEncoderInterface $passwordEncoder,
        UserActionManager $userActionManager,
        EmailManager $emailManager
    ) {
        $this->translator = $translator;
        $this->params = $params;
        $this->em = $em;
        $this->passwordEncoder = $passwordEncoder;
        $this->userActionManager = $userActionManager;
        $this->emailManager = $emailManager;
    }

    /**
     * @Route("/auth/password-reset", name="auth.password_reset")
     */
    public function index(Request $request): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('home');
        }

        $email = $request->query->get('email');
        $passwordResetCode = $request->query->get('password_reset_code');
        $isRequest = !$email && !$passwordResetCode;

        $user = null;
        if (!$isRequest) {
            $user = $this->em->getRepository(User::class)
                ->findOneBy([
                    'email' => $email,
                    'passwordResetCode' => $passwordResetCode,
                ])
            ;
            if (!$user) {
                throw $this->createNotFoundException($this->translator->trans('password_reset.user_not_found', [], 'auth'));
            }
        }

        $formBuilder = $this->createFormBuilder(new User(), [
            'validation_groups' => [
                'password_reset' . ($isRequest ? '_request' : ''),
            ],
        ]);

        if ($isRequest) {
            $formBuilder->add('email', EmailType::class);
        } else {
            $formBuilder->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'The password fields must match.',
                'first_options' => ['label' => 'Password'],
                'second_options' => ['label' => 'Repeat Password'],
            ]);
        }

        $form = $formBuilder->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $response = null;

            if ($isRequest) {
                $response = $this->_handleRequest($form);
            } else {
                $response = $this->_handle($form, $user);
            }

            if ($response) {
                return $response;
            }
        }

        return $this->render('contents/auth/password_reset/index.html.twig', [
            'form' => $form->createView(),
            'form_errors' => $form->getErrors(),
        ]);
    }

    private function _handle($form, $user)
    {
        $user
            ->setPassword(
                $this->passwordEncoder->encodePassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            )
            ->setPasswordResetCode(null)
        ;

        $this->em->persist($user);
        $this->em->flush();

        $this->emailManager->sendPasswordResetSuccess($user);

        $this->userActionManager->add(
            'password_reset.confirm',
            'User successfully reset their password'
        );

        return $this->render('contents/auth/password_reset/success.html.twig');
    }

    private function _handleRequest($form)
    {
        $email = $form->get('email')->getData();

        $user = $this->em->getRepository(User::class)
            ->findOneByEmail($email)
        ;

        if (!$user) {
            $this->addFlash(
                'danger',
                $this->translator->trans('password_reset.request.flash.non_existing_user', [], 'auth')
            );

            return null;
        }

        $lastPasswordResetRequestedAt = $user->getLastPasswordResetRequestedAt();
        if ($lastPasswordResetRequestedAt) {
            $difference = (new \DateTime())->getTimestamp() - $lastPasswordResetRequestedAt->getTimestamp();

            if ($difference < 900) {
                $this->addFlash(
                    'danger',
                    $this->translator->trans('password_reset.request.flash.already_requested_recently', [], 'auth')
                );

                return null;
            }
        }

        $user
            ->setPasswordResetCode(md5(random_bytes(32)))
            ->setLastPasswordResetRequestedAt(new \DateTime())
        ;

        $this->em->persist($user);
        $this->em->flush();

        $this->userActionManager->add(
            'password_reset.request',
            'User requested a password reset'
        );

        $this->emailManager->sendPasswordReset($user);

        return $this->render('contents/auth/password_reset/request_success.html.twig');
    }
}
