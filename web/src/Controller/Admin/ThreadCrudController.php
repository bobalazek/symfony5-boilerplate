<?php

namespace App\Controller\Admin;

use App\Entity\Thread;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;

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
        $lastNewMessageEmailCheckedAt = DateTimeField::new('lastNewMessageEmailCheckedAt');
        $createdAt = DateTimeField::new('createdAt');
        $updatedAt = DateTimeField::new('updatedAt');
        $threadUsers = AssociationField::new('threadUsers');
        $id = IntegerField::new('id', 'ID');

        if (Crud::PAGE_INDEX === $pageName) {
            return [$id, $lastNewMessageEmailCheckedAt, $createdAt, $threadUsers];
        }
        if (Crud::PAGE_DETAIL === $pageName) {
            return [$id, $lastNewMessageEmailCheckedAt, $createdAt, $updatedAt, $threadUsers];
        }
        if (Crud::PAGE_NEW === $pageName) {
            return [$lastNewMessageEmailCheckedAt, $createdAt, $updatedAt, $threadUsers];
        }
        if (Crud::PAGE_EDIT === $pageName) {
            return [$lastNewMessageEmailCheckedAt, $createdAt, $updatedAt, $threadUsers];
        }
    }
}
