<?php

namespace App\Controller\Admin;

use App\Entity\UserNotification;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class UserNotificationCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return UserNotification::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setSearchFields(['id', 'type', 'data'])
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
        $type = TextField::new('type');
        $seenAt = DateTimeField::new('seenAt');
        $readAt = DateTimeField::new('readAt');
        $createdAt = DateTimeField::new('createdAt');
        $updatedAt = DateTimeField::new('updatedAt');
        $user = AssociationField::new('user');
        $id = IntegerField::new('id', 'ID');
        $data = TextField::new('data');

        if (Crud::PAGE_INDEX === $pageName) {
            return [$id, $type, $seenAt, $readAt, $createdAt, $user];
        }
        if (Crud::PAGE_DETAIL === $pageName) {
            return [$id, $type, $data, $seenAt, $readAt, $createdAt, $updatedAt, $user];
        }
        if (Crud::PAGE_NEW === $pageName) {
            return [$type, $seenAt, $readAt, $createdAt, $updatedAt, $user];
        }
        if (Crud::PAGE_EDIT === $pageName) {
            return [$type, $seenAt, $readAt, $createdAt, $updatedAt, $user];
        }
    }
}
