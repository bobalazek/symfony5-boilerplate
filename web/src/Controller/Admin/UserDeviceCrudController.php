<?php

namespace App\Controller\Admin;

use App\Entity\UserDevice;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
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
        $id = IdField::new('id');
        $uuid = TextField::new('uuid');
        $name = TextField::new('name');
        $trusted = BooleanField::new('trusted');
        $invalidated = BooleanField::new('invalidated');
        $lastActiveAt = DateTimeField::new('lastActiveAt');
        $ip = TextField::new('ip');
        $userAgent = TextareaField::new('userAgent');
        $sessionId = TextField::new('sessionId');
        $user = AssociationField::new('user');
        $createdAt = DateTimeField::new('createdAt');

        return [
            $id,
            $uuid,
            $name,
            $trusted,
            $invalidated,
            $lastActiveAt,
            $ip,
            $userAgent,
            $sessionId,
            $user,
            $createdAt,
        ];
    }
}
