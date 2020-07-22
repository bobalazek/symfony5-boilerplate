<?php

namespace App\Controller\Admin;

use App\Entity\UserNotification;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
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
        $id = IdField::new('id');
        $type = TextField::new('type');
        $data = ArrayField::new('data')
            ->setTemplatePath('contents/admin/fields/data_field.html.twig')
        ;
        $seenAt = DateTimeField::new('seenAt');
        $readAt = DateTimeField::new('readAt');
        $user = AssociationField::new('user');
        $createdAt = DateTimeField::new('createdAt');

        return [
            $id,
            $type,
            // $data, // TODO: see UserActionCrudController.php
            $seenAt,
            $readAt,
            $user,
            $createdAt,
        ];
    }
}
