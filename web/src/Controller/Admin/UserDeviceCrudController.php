<?php

namespace App\Controller\Admin;

use App\Entity\UserDevice;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class UserDeviceCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return UserDevice::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setSearchFields(['id', 'uuid', 'name', 'ip', 'userAgent', 'sessionId'])
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
        $uuid = TextField::new('uuid');
        $name = TextField::new('name');
        $trusted = BooleanField::new('trusted');
        $invalidated = BooleanField::new('invalidated');
        $lastActiveAt = DateTimeField::new('lastActiveAt');
        $ip = TextField::new('ip');
        $userAgent = TextareaField::new('userAgent');
        $sessionId = TextField::new('sessionId');
        $createdAt = DateTimeField::new('createdAt');
        $updatedAt = DateTimeField::new('updatedAt');
        $user = AssociationField::new('user');
        $id = IntegerField::new('id', 'ID');

        if (Crud::PAGE_INDEX === $pageName) {
            return [$id, $name, $trusted, $invalidated, $lastActiveAt, $ip, $sessionId];
        }
        if (Crud::PAGE_DETAIL === $pageName) {
            return [$id, $uuid, $name, $trusted, $invalidated, $lastActiveAt, $ip, $userAgent, $sessionId, $createdAt, $updatedAt, $user];
        }
        if (Crud::PAGE_NEW === $pageName) {
            return [$uuid, $name, $trusted, $invalidated, $lastActiveAt, $ip, $userAgent, $sessionId, $createdAt, $updatedAt, $user];
        }
        if (Crud::PAGE_EDIT === $pageName) {
            return [$uuid, $name, $trusted, $invalidated, $lastActiveAt, $ip, $userAgent, $sessionId, $createdAt, $updatedAt, $user];
        }
    }
}
