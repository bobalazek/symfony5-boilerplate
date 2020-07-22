<?php

namespace App\Controller\Admin;

use App\Entity\UserTfaRecoveryCode;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class UserTfaRecoveryCodeCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return UserTfaRecoveryCode::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setSearchFields(['id', 'recoveryCode'])
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
        $recoveryCode = TextField::new('recoveryCode');
        $usedAt = DateTimeField::new('usedAt');
        $createdAt = DateTimeField::new('createdAt');
        $updatedAt = DateTimeField::new('updatedAt');
        $user = AssociationField::new('user');
        $id = IntegerField::new('id', 'ID');

        if (Crud::PAGE_INDEX === $pageName) {
            return [$id, $recoveryCode, $usedAt, $createdAt, $user];
        }
        if (Crud::PAGE_DETAIL === $pageName) {
            return [$id, $recoveryCode, $usedAt, $createdAt, $updatedAt, $user];
        }
        if (Crud::PAGE_NEW === $pageName) {
            return [$recoveryCode, $usedAt, $createdAt, $updatedAt, $user];
        }
        if (Crud::PAGE_EDIT === $pageName) {
            return [$recoveryCode, $usedAt, $createdAt, $updatedAt, $user];
        }
    }
}
