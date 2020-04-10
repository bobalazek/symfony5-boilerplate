<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\UserTfaMethod;
use App\Entity\UserTfaRecoveryCode;
use App\Form\SettingsTfaType;
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

        $form = $this->createForm(SettingsTfaType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($user);
            $this->em->flush();

            $this->addFlash(
                'success',
                $this->translator->trans('tfa.flash.success', [], 'settings')
            );

            $this->userActionManager->add(
                'settings.tfa',
                'User 2FA was changed'
            );

            return $this->redirectToRoute('settings.tfa');
        }

        return $this->render('contents/settings/tfa/index.html.twig', [
            'form' => $form->createView(),
            'methods' => $methods,
            'methods_enabled' => $methodsEnabled,
        ]);
    }

    /**
     * @Route("/settings/tfa/email", name="settings.tfa.email")
     */
    public function email(Request $request): Response
    {
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
                'User 2FA method "email" was edited.'
            );

            return $this->redirectToRoute('settings.tfa.email');
        }

        return $this->render('contents/settings/tfa/email.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/settings/tfa/google-authenticator", name="settings.tfa.google_authenticator")
     */
    public function googleAuthenticator(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $user = $this->getUser();
        $action = $request->query->get('action');

        $userTfaMethod = $this->_getUserTfaMethod(
            UserTfaMethod::METHOD_GOOGLE_AUTHENTICATOR,
            $user
        );

        $userTfaMethodData = $userTfaMethod->getData();
        $secret = $userTfaMethodData['secret'];

        $methods = $this->params->get('app.tfa_methods');
        $googleAuthenticatorData = $methods[UserTfaMethod::METHOD_GOOGLE_AUTHENTICATOR];

        $qrCodeUrl = $this->googleAuthenticatorManager->generateQrUrl(
            $user->getId() . '@' . $googleAuthenticatorData['hostname'],
            $secret,
            $googleAuthenticatorData['issuer']
        );

        if ('reset' === $action) {
            $this->addFlash(
                'success',
                $this->translator->trans('tfa.flash.success', [], 'settings')
            );

            $this->userActionManager->add(
                'settings.tfa.google_authenticator.reset',
                'User 2FA google authenticator was successfully reset.'
            );

            $userTfaMethod->setEnabled(false);

            $this->em->persist($userTfaMethod);
            $this->em->flush();

            return $this->redirectToRoute('settings.tfa.google_authenticator');
        }

        $form = $this->createForm(SettingsUserTfaMethodType::class, $userTfaMethod, [
            'hide_enabled_field' => true,
            'show_code_field' => true,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $code = $userTfaMethod->getCode();
            $codeValid = $this->googleAuthenticatorManager->checkCode(
                $secret ?? '',
                $code
            );
            if (!$codeValid) {
                $this->addFlash(
                    'danger',
                    $this->translator->trans('tfa.flash.code_invald', [], 'settings')
                );

                return $this->redirectToRoute('settings.tfa.google_authenticator');
            }

            $userTfaMethod->setEnabled(true);

            $this->em->persist($userTfaMethod);
            $this->em->flush();

            $this->addFlash(
                'success',
                $this->translator->trans('tfa.flash.success', [], 'settings')
            );

            $this->userActionManager->add(
                'settings.tfa.google_authenticator',
                'User 2FA method "google_authenticator" was edited.'
            );

            return $this->redirectToRoute('settings.tfa.google_authenticator');
        }

        return $this->render('contents/settings/tfa/google_authenticator.html.twig', [
            'form' => $form->createView(),
            'user_tfa_method' => $userTfaMethod,
            'secret' => $secret,
            'qr_code_url' => $qrCodeUrl,
        ]);
    }

    /**
     * @Route("/settings/tfa/recovery-codes", name="settings.tfa.recovery_codes")
     */
    public function recoveryCodes(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $user = $this->getUser();
        $action = $request->query->get('action');

        if ('regenerate' === $action) {
            $userTfaRecoveryCodes = $user->getUserTfaRecoveryCodes();
            foreach ($userTfaRecoveryCodes as $userTfaRecoveryCode) {
                $this->em->remove($userTfaRecoveryCode);
            }
            $this->em->flush();

            $this->_generateUserTfaRecoveryCodes(
                $user
            );

            $this->addFlash(
                'success',
                $this->translator->trans('tfa.recovery_codes.regenerate.flash.success', [], 'settings')
            );

            $this->userActionManager->add(
                'settings.tfa.recovery_codes.regenerate',
                'User 2FA recovery codes were successfully regenerated.'
            );

            return $this->redirectToRoute('settings.tfa.recovery_codes');
        }

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
                'User 2FA method "recovery_codes" was edited.'
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
            $data = null;

            if (UserTfaMethod::METHOD_GOOGLE_AUTHENTICATOR === $method) {
                $data = [
                    'secret' => $this->googleAuthenticatorManager->generateSecret(),
                ];
            } elseif (UserTfaMethod::METHOD_RECOVERY_CODES === $method) {
                $methods = $this->params->get('app.tfa_methods');
                $methodData = $methods[$method];
                $this->_generateUserTfaRecoveryCodes(
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

    private function _generateUserTfaRecoveryCodes(User $user, $count = null)
    {
        if (null === $count) {
            $methods = $this->params->get('app.tfa_methods');
            $count = $methods[UserTfaMethod::METHOD_RECOVERY_CODES]['initial_count'];
        }

        $codesGenerated = 0;
        while ($codesGenerated < $count) {
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

            ++$codesGenerated;
        }

        return true;
    }

    /**
     * @return string
     */
    private function _generateRandomString(int $length = 4)
    {
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';

        for ($i = 0; $i < $length; ++$i) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }

        return $randomString;
    }
}
