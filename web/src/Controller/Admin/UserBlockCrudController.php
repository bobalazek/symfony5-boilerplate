<?php

namespace App\Controller\Admin;

use App\Entity\UserBlock;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;

class UserBlockCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return UserBlock::class;
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
        $createdAt = DateTimeField::new('createdAt');
        $updatedAt = DateTimeField::new('updatedAt');
        $user = AssociationField::new('user');
        $userBlocked = AssociationField::new('userBlocked');
        $id = IntegerField::new('id', 'ID');

        if (Crud::PAGE_INDEX === $pageName) {
            return [$id, $createdAt, $user, $userBlocked];
        }
        if (Crud::PAGE_DETAIL === $pageName) {
            return [$id, $createdAt, $updatedAt, $user, $userBlocked];
        }
        if (Crud::PAGE_NEW === $pageName) {
            return [$createdAt, $updatedAt, $user, $userBlocked];
        }
        if (Crud::PAGE_EDIT === $pageName) {
            return [$createdAt, $updatedAt, $user, $userBlocked];
        }
    }
}
