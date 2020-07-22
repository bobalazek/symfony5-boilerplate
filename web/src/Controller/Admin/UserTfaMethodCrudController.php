<?php

namespace App\Controller\Admin;

use App\Entity\UserTfaMethod;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
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
        $id = IdField::new('id');
        $enabled = BooleanField::new('enabled');
        $method = TextField::new('method');
        $data = TextField::new('data');
        $user = AssociationField::new('user');
        $createdAt = DateTimeField::new('createdAt');

        return [
            $id,
            $enabled,
            $method,
            $data,
            $user,
            $createdAt,
        ];
    }
}
