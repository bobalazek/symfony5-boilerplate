<?php

namespace App\Controller\Admin;

use App\Entity\Thread;
use App\Entity\ThreadUser;
use App\Entity\ThreadUserMessage;
use App\Entity\User;
use App\Entity\UserAction;
use App\Entity\UserBlock;
use App\Entity\UserDevice;
use App\Entity\UserExport;
use App\Entity\UserFollower;
use App\Entity\UserNotification;
use App\Entity\UserOauthProvider;
use App\Entity\UserPoint;
use App\Entity\UserTfaMethod;
use App\Entity\UserTfaRecoveryCode;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractDashboardController
{
    /**
     * @Route("/admin", name="admin")
     */
    public function index(): Response
    {
        return $this->render('contents/admin/index.html.twig');
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('S5BP Admin')
        ;
    }

    public function configureCrud(): Crud
    {
        return Crud::new();
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToCrud('Users', 'fas fa-folder-open', User::class);
        yield MenuItem::linkToCrud('User Actions', 'fas fa-folder-open', UserAction::class);
        yield MenuItem::linkToCrud('User Devices', 'fas fa-folder-open', UserDevice::class);
        yield MenuItem::linkToCrud('User OAuth Providers', 'fas fa-folder-open', UserOauthProvider::class);
        yield MenuItem::linkToCrud('User TFA Methods', 'fas fa-folder-open', UserTfaMethod::class);
        yield MenuItem::linkToCrud('User TFA Recovery Codes', 'fas fa-folder-open', UserTfaRecoveryCode::class);
        yield MenuItem::linkToCrud('User Notifications', 'fas fa-folder-open', UserNotification::class);
        yield MenuItem::linkToCrud('User Blocks', 'fas fa-folder-open', UserBlock::class);
        yield MenuItem::linkToCrud('User Followers', 'fas fa-folder-open', UserFollower::class);
        yield MenuItem::linkToCrud('User Points', 'fas fa-folder-open', UserPoint::class);
        yield MenuItem::linkToCrud('User Exports', 'fas fa-folder-open', UserExport::class);
        yield MenuItem::linkToCrud('Threads', 'fas fa-folder-open', Thread::class);
        yield MenuItem::linkToCrud('Thread Users', 'fas fa-folder-open', ThreadUser::class);
        yield MenuItem::linkToCrud('Thread User Messages', 'fas fa-folder-open', ThreadUserMessage::class);
    }
}
