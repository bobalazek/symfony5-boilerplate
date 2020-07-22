<?php

namespace App\Controller\Admin;

use App\Entity\UserOauthProvider;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class UserOauthProviderCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return UserOauthProvider::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setSearchFields(['id', 'provider', 'providerId', 'data'])
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
        $provider = TextField::new('provider');
        $providerId = TextField::new('providerId');
        $createdAt = DateTimeField::new('createdAt');
        $updatedAt = DateTimeField::new('updatedAt');
        $user = AssociationField::new('user');
        $id = IntegerField::new('id', 'ID');
        $data = TextField::new('data');

        if (Crud::PAGE_INDEX === $pageName) {
            return [$id, $provider, $providerId, $createdAt, $user];
        }
        if (Crud::PAGE_DETAIL === $pageName) {
            return [$id, $provider, $providerId, $data, $createdAt, $updatedAt, $user];
        }
        if (Crud::PAGE_NEW === $pageName) {
            return [$provider, $providerId, $createdAt, $updatedAt, $user];
        }
        if (Crud::PAGE_EDIT === $pageName) {
            return [$provider, $providerId, $createdAt, $updatedAt, $user];
        }
    }
}
