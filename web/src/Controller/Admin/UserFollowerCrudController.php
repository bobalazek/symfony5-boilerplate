<?php

namespace App\Controller\Admin;

use App\Entity\UserFollower;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class UserFollowerCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return UserFollower::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setSearchFields(['id', 'status'])
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
        $status = TextField::new('status');
        $createdAt = DateTimeField::new('createdAt');
        $updatedAt = DateTimeField::new('updatedAt');
        $user = AssociationField::new('user');
        $userFollowing = AssociationField::new('userFollowing');
        $id = IntegerField::new('id', 'ID');

        if (Crud::PAGE_INDEX === $pageName) {
            return [$id, $status, $createdAt, $user, $userFollowing];
        }
        if (Crud::PAGE_DETAIL === $pageName) {
            return [$id, $status, $createdAt, $updatedAt, $user, $userFollowing];
        }
        if (Crud::PAGE_NEW === $pageName) {
            return [$status, $createdAt, $updatedAt, $user, $userFollowing];
        }
        if (Crud::PAGE_EDIT === $pageName) {
            return [$status, $createdAt, $updatedAt, $user, $userFollowing];
        }
    }
}
