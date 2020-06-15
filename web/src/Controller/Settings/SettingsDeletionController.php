<?php

namespace App\Controller\Settings;

use App\Entity\User;
use App\Manager\EmailManager;
use App\Manager\UserActionManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class SettingsDeletionController.
 */
class SettingsDeletionController extends AbstractController
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
     * @var EmailManager
     */
    private $emailManager;

    public function __construct(
        TranslatorInterface $translator,
        ParameterBagInterface $params,
        EntityManagerInterface $em,
        UserActionManager $userActionManager,
        EmailManager $emailManager
    ) {
        $this->translator = $translator;
        $this->params = $params;
        $this->em = $em;
        $this->userActionManager = $userActionManager;
        $this->emailManager = $emailManager;
    }

    /**
     * @Route("/settings/deletion", name="settings.deletion")
     */
    public function index(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        /** @var User $user */
        $user = $this->getUser();
        $queryParamsResponse = $this->_handleDeletionQueryParams(
            $request,
            $user
        );
        if ($queryParamsResponse) {
            return $queryParamsResponse;
        }

        $form = $this->createFormBuilder()->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $lastDeletionConfirmationEmailSentAt = $user->getLastDeletionConfirmationEmailSentAt();
            if ($lastDeletionConfirmationEmailSentAt) {
                $difference = (new \DateTime())->getTimestamp() -
                    $lastDeletionConfirmationEmailSentAt->getTimestamp();

                if ($difference < 900) {
                    $this->addFlash(
                        'danger',
                        $this->translator->trans('deletion.flash.already_requested_recently', [], 'settings')
                    );

                    return $this->redirectToRoute('settings.deletion');
                }
            }

            $user
                ->setDeletionConfirmCode(md5(random_bytes(32)))
                ->setLastDeletionConfirmationEmailSentAt(new \DateTime())
            ;

            $this->em->persist($user);
            $this->em->flush();

            $this->emailManager->sendDeletionConfirm($user);

            $this->userActionManager->add(
                'settings.deletion',
                'User send a deletion request'
            );

            $this->addFlash(
                'success',
                $this->translator->trans('deletion.flash.success', [], 'settings')
            );

            return $this->redirectToRoute('settings.deletion');
        }

        return $this->render('contents/settings/deletion.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    private function _handleDeletionQueryParams($request, $user)
    {
        $action = $request->query->get('action');
        if ('cancel_deletion' === $action) {
            $user->setDeletionConfirmCode(null);

            $this->em->persist($user);
            $this->em->flush();

            $this->userActionManager->add(
                'settings.deletion.cancel',
                'User deletion was canceled'
            );

            $this->addFlash(
                'success',
                $this->translator->trans('deletion.flash.cancel_deletion_success', [], 'settings')
            );

            return $this->redirectToRoute('settings.deletion');
        }

        if ('resend_deletion' === $action) {
            $lastDeletionConfirmationEmailSentAt = $user->getLastDeletionConfirmationEmailSentAt();
            if ($lastDeletionConfirmationEmailSentAt) {
                $difference = (new \DateTime())->getTimestamp() - $lastDeletionConfirmationEmailSentAt->getTimestamp();

                if ($difference < 900) {
                    $this->addFlash(
                        'danger',
                        $this->translator->trans('deletion.deletion_resend.flash.already_requested_recently', [], 'settings')
                    );

                    return $this->redirectToRoute('settings.deletion');
                }
            }

            $user->setLastDeletionConfirmationEmailSentAt(new \DateTime());

            $this->em->persist($user);
            $this->em->flush();

            $this->emailManager->sendDeletionConfirm($user);

            $this->userActionManager->add(
                'settings.deletion.email_resend',
                'New user email was resend'
            );

            $this->addFlash(
                'success',
                $this->translator->trans('deletion.flash.resend_deletion_email_success', [], 'settings')
            );

            return $this->redirectToRoute('settings');
        }

        $deletionConfirmCode = $request->query->get('deletion_confirm_code');
        if ($deletionConfirmCode) {
            if ($user->getDeletionConfirmCode() !== $deletionConfirmCode) {
                $this->addFlash(
                    'danger',
                    $this->translator->trans('deletion.flash.deletion_confirm_code_invalid', [], 'settings')
                );

                return $this->redirectToRoute('settings.deletion');
            }

            $this->_deleteUser($user);

            $this->emailManager->sendDeletionConfirmSuccess($user);

            $this->addFlash(
                'success',
                $this->translator->trans('flash.deletion_confirmed_success', [], 'settings')
            );

            return $this->redirectToRoute('auth.login');
        }
    }

    private function _deleteUser($user)
    {
        if ($this->isGranted('ROLE_MODERATOR')) {
            throw new \Exception('A moderator user or higher can not be deleted.');
        }

        // TODO: actually delete the user
        // TODO: flag this it somewhere, so that in case of a backup rollback,
        //   you can delete this user again.

        $this->em->remove($user);
        $this->em->flush();
    }
}
