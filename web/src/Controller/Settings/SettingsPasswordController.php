<?php

namespace App\Controller\Settings;

use App\Form\SettingsPasswordType;
use App\Manager\UserActionManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class SettingsPasswordController.
 */
class SettingsPasswordController extends AbstractController
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
     * @var \Swift_Mailer
     */
    private $mailer;

    public function __construct(
        TranslatorInterface $translator,
        ParameterBagInterface $params,
        EntityManagerInterface $em,
        UserActionManager $userActionManager,
        \Swift_Mailer $mailer
    ) {
        $this->translator = $translator;
        $this->params = $params;
        $this->em = $em;
        $this->userActionManager = $userActionManager;
        $this->mailer = $mailer;
    }

    /**
     * @Route("/settings/password", name="settings.password")
     */
    public function index(
        Request $request,
        UserPasswordEncoderInterface $passwordEncoder
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $user = $this->getUser();
        $form = $this->createForm(SettingsPasswordType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user
                ->setPassword(
                    $passwordEncoder->encodePassword(
                        $user,
                        $form->get('plainPassword')->getData()
                    )
                )
            ;

            $this->em->persist($user);
            $this->em->flush();

            $this->addFlash(
                'success',
                $this->translator->trans('password.flash.success', [], 'settings')
            );

            $this->userActionManager->add(
                'settings.password',
                'User password was changed'
            );

            return $this->redirectToRoute('settings.password');
        }

        $this->em->refresh($user);

        return $this->render('contents/settings/password.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
