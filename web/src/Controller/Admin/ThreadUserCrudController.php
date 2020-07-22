<?php

namespace App\Controller\Admin;

use App\Entity\ThreadUser;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;

class ThreadUserCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ThreadUser::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setSearchFields(['id'])
        ;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->disable('new', 'edit', 'delete')
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        $id = IdField::new('id');
        $lastSeenAt = DateTimeField::new('lastSeenAt');
        $lastActiveAt = DateTimeField::new('lastActiveAt');
        $lastNewMessageEmailSentAt = DateTimeField::new('lastNewMessageEmailSentAt');
        $thread = AssociationField::new('thread');
        $user = AssociationField::new('user');
        $threadUserMessages = AssociationField::new('threadUserMessages');
        $createdAt = DateTimeField::new('createdAt');

        return [
            $id,
            $lastSeenAt,
            $lastActiveAt,
            $lastNewMessageEmailSentAt,
            $thread,
            $user,
            $threadUserMessages,
            $createdAt,
        ];
    }
}
