<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\UserTfaMethod;
use App\Entity\UserTfaRecoveryCode;
use App\Form\SettingsUserTfaMethodType;
use App\Manager\GoogleAuthenticatorManager;
use App\Manager\UserActionManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class SettingsTfaController.
 */
class SettingsTfaController extends AbstractController
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

    public function __construct(
        TranslatorInterface $translator,
        ParameterBagInterface $params,
        EntityManagerInterface $em,
        UserActionManager $userActionManager,
        GoogleAuthenticatorManager $googleAuthenticatorManager
    ) {
        $this->translator = $translator;
        $this->params = $params;
        $this->em = $em;
        $this->userActionManager = $userActionManager;
        $this->googleAuthenticatorManager = $googleAuthenticatorManager;
    }

    /**
     * @Route("/settings/tfa", name="settings.tfa")
     */
    public function index(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $user = $this->getUser();

        $methods = $this->params->get('app.tfa_methods');
        $methodsEnabled = [];
        foreach ($user->getUserTfaMethods() as $userTfaMethod) {
            if (!$userTfaMethod->isEnabled()) {
                continue;
            }

            $methodsEnabled[] = $userTfaMethod->getMethod();
        }

        return $this->render('contents/settings/tfa/index.html.twig', [
            'methods' => $methods,
            'methods_enabled' => $methodsEnabled,
        ]);
    }

    /**
     * @Route("/login/tfa/email", name="settings.tfa.email")
     */
    public function email(Request $request): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $user = $this->getUser();
        $userTfaMethod = $this->_getUserTfaMethod(
            UserTfaMethod::METHOD_EMAIL,
            $user
        );

        $form = $this->createForm(SettingsUserTfaMethodType::class, $userTfaMethod);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($userTfaMethod);
            $this->em->flush();

            $this->addFlash(
                'success',
                $this->translator->trans('tfa.flash.success', [], 'settings')
            );

            $this->userActionManager->add(
                'settings.tfa.email',
                'User TFA method "email" was edited.'
            );

            return $this->redirectToRoute('settings.tfa.email');
        }

        return $this->render('contents/settings/tfa/email.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/login/tfa/google-authenticator", name="settings.tfa.google_authenticator")
     */
    public function googleAuthenticator(Request $request): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $user = $this->getUser();
        $userTfaMethod = $this->_getUserTfaMethod(
            UserTfaMethod::METHOD_GOOGLE_AUTHENTICATOR,
            $user
        );

        $form = $this->createForm(SettingsUserTfaMethodType::class, $userTfaMethod);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($userTfaMethod);
            $this->em->flush();

            $this->addFlash(
                'success',
                $this->translator->trans('tfa.flash.success', [], 'settings')
            );

            $this->userActionManager->add(
                'settings.tfa.google_authenticator',
                'User TFA method "google_authenticator" was edited.'
            );

            return $this->redirectToRoute('settings.tfa.google_authenticator');
        }

        return $this->render('contents/settings/tfa/google_authenticator.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/login/tfa/recovery-codes", name="settings.tfa.recovery_codes")
     */
    public function recoveryCodes(Request $request): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $user = $this->getUser();
        $userTfaMethod = $this->_getUserTfaMethod(
            UserTfaMethod::METHOD_RECOVERY_CODES,
            $user
        );

        $form = $this->createForm(SettingsUserTfaMethodType::class, $userTfaMethod);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($userTfaMethod);
            $this->em->flush();

            $this->addFlash(
                'success',
                $this->translator->trans('tfa.flash.success', [], 'settings')
            );

            $this->userActionManager->add(
                'settings.tfa.recovery_codes',
                'User TFA method "recovery_codes" was edited.'
            );

            return $this->redirectToRoute('settings.tfa.recovery_codes');
        }

        return $this->render('contents/settings/tfa/recovery_codes.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    private function _getUserTfaMethod(string $method, User $user)
    {
        $userTfaMethod = $this->em
            ->getRepository(UserTfaMethod::class)
            ->findOneBy([
                'user' => $user,
                'method' => $method,
            ])
        ;
        if (!$userTfaMethod) {
            $methodData = $methods[$method];
            $data = null;

            if (UserTfaMethod::METHOD_GOOGLE_AUTHENTICATOR === $method) {
                $data = [
                    'secret' => $this->googleAuthenticatorManager->generateSecret(),
                ];
            } elseif (UserTfaMethod::METHOD_RECOVERY_CODES === $method) {
                $this->_generateUserTfaRecoveryCodes(
                    $methodData['initial_count'],
                    $user
                );
            }

            $userTfaMethod = new UserTfaMethod();
            $userTfaMethod
                ->setMethod($method)
                ->setData($data)
                ->setUser($user)
            ;

            $this->em->persist($userTfaMethod);
            $this->em->flush();
        }

        return $userTfaMethod;
    }

    private function _generateUserTfaRecoveryCodes(int $count, User $user)
    {
        $codesGenerated = 0;
        while ($codesGenerated < $initialCount) {
            $recoveryCode = $this->_generateRandomString(4) .
                '-' . $this->_generateRandomString(4);

            $existingUserTfaRecoveryCode = $this->em
                ->getRepository(UserTfaRecoveryCode::class)
                ->findOneBy([
                    'recoveryCode' => $recoveryCode,
                    'user' => $user,
                ])
            ;

            if ($existingUserTfaRecoveryCode) {
                continue;
            }

            $userTfaRecoveryCode = new UserTfaRecoveryCode();
            $userTfaRecoveryCode
                ->setRecoveryCode($recoveryCode)
                ->setUser($user)
            ;

            $this->em->persist($userTfaRecoveryCode);
            $this->em->flush();

            $codesGenerated++;
        }

        return true;
    }

    /**
     * @param int $length
     *
     * @return string
     */
    private function _generateRandomString(int $length = 4)
    {
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';

        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }

        return $randomString;
    }
}
