<?php

namespace App\Controller\Admin;

use App\Entity\UserBlock;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;

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
        $id = IdField::new('id');
        $user = AssociationField::new('user');
        $userBlocked = AssociationField::new('userBlocked');
        $createdAt = DateTimeField::new('createdAt');

        return [
            $id,
            $user,
            $userBlocked,
            $createdAt,
        ];
    }
}
