<?php

namespace App\Controller\Admin;

use App\Entity\ThreadUserMessage;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
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
        $id = IdField::new('id');
        $body = TextareaField::new('body');
        $threadUser = AssociationField::new('threadUser');
        $createdAt = DateTimeField::new('createdAt');

        return [
            $id,
            $body,
            $threadUser,
            $createdAt,
        ];
    }
}
