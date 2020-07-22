<?php

namespace App\Controller\Admin;

use App\Entity\Thread;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;

class ThreadCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Thread::class;
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
        $lastNewMessageEmailCheckedAt = DateTimeField::new('lastNewMessageEmailCheckedAt');
        $threadUsers = AssociationField::new('threadUsers');
        $createdAt = DateTimeField::new('createdAt');

        return [
            $id,
            $lastNewMessageEmailCheckedAt,
            $threadUsers,
            $createdAt,
        ];
    }
}
