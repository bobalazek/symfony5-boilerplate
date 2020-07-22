<?php

namespace App\Controller\Admin;

use App\Entity\ThreadUserMessage;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;

class ThreadUserMessageCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ThreadUserMessage::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setSearchFields(['id', 'body'])
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
        $body = TextareaField::new('body');
        $createdAt = DateTimeField::new('createdAt');
        $updatedAt = DateTimeField::new('updatedAt');
        $threadUser = AssociationField::new('threadUser');
        $id = IntegerField::new('id', 'ID');

        if (Crud::PAGE_INDEX === $pageName) {
            return [$id, $body, $createdAt, $threadUser];
        }
        if (Crud::PAGE_DETAIL === $pageName) {
            return [$id, $body, $createdAt, $updatedAt, $threadUser];
        }
        if (Crud::PAGE_NEW === $pageName) {
            return [$body, $createdAt, $updatedAt, $threadUser];
        }
        if (Crud::PAGE_EDIT === $pageName) {
            return [$body, $createdAt, $updatedAt, $threadUser];
        }
    }
}
