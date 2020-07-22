<?php

namespace App\Controller\Admin;

use App\Entity\UserPoint;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class UserPointCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return UserPoint::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setSearchFields(['id', 'key', 'amount', 'data'])
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
        $key = TextField::new('key');
        $amount = IntegerField::new('amount');
        $createdAt = DateTimeField::new('createdAt');
        $updatedAt = DateTimeField::new('updatedAt');
        $user = AssociationField::new('user');
        $id = IntegerField::new('id', 'ID');
        $data = TextField::new('data');

        if (Crud::PAGE_INDEX === $pageName) {
            return [$id, $key, $amount, $createdAt, $user];
        }
        if (Crud::PAGE_DETAIL === $pageName) {
            return [$id, $key, $amount, $data, $createdAt, $updatedAt, $user];
        }
        if (Crud::PAGE_NEW === $pageName) {
            return [$key, $amount, $createdAt, $updatedAt, $user];
        }
        if (Crud::PAGE_EDIT === $pageName) {
            return [$key, $amount, $createdAt, $updatedAt, $user];
        }
    }
}
