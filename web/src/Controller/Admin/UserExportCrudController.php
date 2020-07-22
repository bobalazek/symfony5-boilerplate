<?php

namespace App\Controller\Admin;

use App\Entity\UserExport;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class UserExportCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return UserExport::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setSearchFields(['id', 'status', 'token', 'failedMessage', 'embeddedFile.name', 'embeddedFile.originalName', 'embeddedFile.mimeType', 'embeddedFile.size', 'embeddedFile.dimensions'])
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
        $status = TextField::new('status');
        $token = TextField::new('token');
        $failedMessage = TextareaField::new('failedMessage');
        $startedAt = DateTimeField::new('startedAt');
        $completedAt = DateTimeField::new('completedAt');
        $failedAt = DateTimeField::new('failedAt');
        $expiresAt = DateTimeField::new('expiresAt');
        $createdAt = DateTimeField::new('createdAt');
        $updatedAt = DateTimeField::new('updatedAt');
        $user = AssociationField::new('user');
        $id = IntegerField::new('id', 'ID');

        if (Crud::PAGE_INDEX === $pageName) {
            return [$id, $status, $token, $startedAt, $completedAt, $failedAt, $expiresAt];
        }
        if (Crud::PAGE_DETAIL === $pageName) {
            return [$id, $status, $token, $failedMessage, $startedAt, $completedAt, $failedAt, $expiresAt, $createdAt, $updatedAt, $user];
        }
        if (Crud::PAGE_NEW === $pageName) {
            return [$status, $token, $failedMessage, $startedAt, $completedAt, $failedAt, $expiresAt, $createdAt, $updatedAt, $user];
        }
        if (Crud::PAGE_EDIT === $pageName) {
            return [$status, $token, $failedMessage, $startedAt, $completedAt, $failedAt, $expiresAt, $createdAt, $updatedAt, $user];
        }
    }
}
