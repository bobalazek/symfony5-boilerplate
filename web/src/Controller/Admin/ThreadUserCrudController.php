<?php

namespace App\Controller\Admin;

use App\Entity\ThreadUser;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;

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
        $lastSeenAt = DateTimeField::new('lastSeenAt');
        $lastActiveAt = DateTimeField::new('lastActiveAt');
        $lastNewMessageEmailSentAt = DateTimeField::new('lastNewMessageEmailSentAt');
        $createdAt = DateTimeField::new('createdAt');
        $updatedAt = DateTimeField::new('updatedAt');
        $thread = AssociationField::new('thread');
        $user = AssociationField::new('user');
        $threadUserMessages = AssociationField::new('threadUserMessages');
        $id = IntegerField::new('id', 'ID');

        if (Crud::PAGE_INDEX === $pageName) {
            return [$id, $lastSeenAt, $lastActiveAt, $lastNewMessageEmailSentAt, $createdAt, $thread, $user];
        }
        if (Crud::PAGE_DETAIL === $pageName) {
            return [$id, $lastSeenAt, $lastActiveAt, $lastNewMessageEmailSentAt, $createdAt, $updatedAt, $thread, $user, $threadUserMessages];
        }
        if (Crud::PAGE_NEW === $pageName) {
            return [$lastSeenAt, $lastActiveAt, $lastNewMessageEmailSentAt, $createdAt, $updatedAt, $thread, $user, $threadUserMessages];
        }
        if (Crud::PAGE_EDIT === $pageName) {
            return [$lastSeenAt, $lastActiveAt, $lastNewMessageEmailSentAt, $createdAt, $updatedAt, $thread, $user, $threadUserMessages];
        }
    }
}
