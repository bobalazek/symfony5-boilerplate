<?php

namespace App\Controller\Settings;

use App\Entity\User;
use App\Form\Type\SettingsImageType;
use App\Manager\AvatarManager;
use App\Manager\UserActionManager;
use App\Manager\UserManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class SettingsImageController.
 */
class SettingsImageController extends AbstractController
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
     * @var UserManager
     */
    private $userManager;

    /**
     * @var UserActionManager
     */
    private $userActionManager;

    public function __construct(
        TranslatorInterface $translator,
        ParameterBagInterface $params,
        EntityManagerInterface $em,
        UserManager $userManager,
        UserActionManager $userActionManager
    ) {
        $this->translator = $translator;
        $this->params = $params;
        $this->em = $em;
        $this->userManager = $userManager;
        $this->userActionManager = $userActionManager;
    }

    /**
     * @Route("/settings/image", name="settings.image")
     */
    public function index(Request $request, AvatarManager $avatarManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $action = $request->query->get('action');
        if ('clear_image_file' === $action) {
            $this->userManager->removeUploadedImage();

            $this->em->persist($user);
            $this->em->flush();

            $this->addFlash(
                'success',
                $this->translator->trans('flash.clear_image_file_success', [], 'settings')
            );

            $this->userActionManager->add(
                'settings.image_file_clear',
                'User image was cleared'
            );

            return $this->redirectToRoute('settings.image');
        }

        /** @var User $user */
        $user = $this->getUser();
        $form = $this->createForm(SettingsImageType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // We must force that, else the image upload subscriber won't kick in
            $user->setUpdatedAt(new \DateTime());

            $imageFile = $user->getImageFile();
            if ($imageFile) {
                $this->userManager->removeUploadedImage();
                $user->setAvatarImage(null);
            }

            $this->em->persist($user);
            $this->em->flush();

            $this->userActionManager->add(
                'settings.image',
                'New user image/avatar was set'
            );

            $this->addFlash(
                'success',
                $this->translator->trans('image.flash.success', [], 'settings')
            );

            return $this->redirectToRoute('settings.image');
        }

        $avatarImages = [];
        foreach ($avatarManager->getFiles() as $file) {
            $filename = $file->getRelativePathname();
            $avatarImages[] = $filename;
        }

        return $this->render('contents/settings/image.html.twig', [
            'form' => $form->createView(),
            'avatar_images' => $avatarImages,
        ]);
    }
}
