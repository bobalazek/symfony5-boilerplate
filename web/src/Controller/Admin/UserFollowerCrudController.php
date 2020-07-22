<?php

namespace App\Controller\Admin;

use App\Entity\UserFollower;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
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
        $id = IdField::new('id');
        $status = TextField::new('status');
        $user = AssociationField::new('user');
        $userFollowing = AssociationField::new('userFollowing');
        $createdAt = DateTimeField::new('createdAt');

        return [
            $id,
            $status,
            $user,
            $userFollowing,
            $createdAt,
        ];
    }
}
