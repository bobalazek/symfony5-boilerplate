<?php

namespace App\Controller\Admin;

use App\Entity\UserTfaMethod;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class UserTfaMethodCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return UserTfaMethod::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setSearchFields(['id', 'method', 'data'])
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
        $enabled = BooleanField::new('enabled');
        $method = TextField::new('method');
        $createdAt = DateTimeField::new('createdAt');
        $updatedAt = DateTimeField::new('updatedAt');
        $user = AssociationField::new('user');
        $id = IntegerField::new('id', 'ID');
        $data = TextField::new('data');

        if (Crud::PAGE_INDEX === $pageName) {
            return [$id, $enabled, $method, $createdAt, $user];
        }
        if (Crud::PAGE_DETAIL === $pageName) {
            return [$id, $enabled, $method, $data, $createdAt, $updatedAt, $user];
        }
        if (Crud::PAGE_NEW === $pageName) {
            return [$enabled, $method, $createdAt, $updatedAt, $user];
        }
        if (Crud::PAGE_EDIT === $pageName) {
            return [$enabled, $method, $createdAt, $updatedAt, $user];
        }
    }
}
