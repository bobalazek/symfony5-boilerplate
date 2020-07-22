<?php

namespace App\Controller\Admin;

use App\Entity\UserAction;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class UserActionCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return UserAction::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setSearchFields(['id', 'key', 'message', 'data', 'ip', 'userAgent', 'sessionId'])
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
        $message = TextareaField::new('message');
        $ip = TextField::new('ip');
        $userAgent = TextareaField::new('userAgent');
        $sessionId = TextField::new('sessionId');
        $createdAt = DateTimeField::new('createdAt');
        $updatedAt = DateTimeField::new('updatedAt');
        $user = AssociationField::new('user');
        $id = IntegerField::new('id', 'ID');
        $data = ArrayField::new('data')->setTemplatePath('contents/admin/fields/user_action_data.html.twig');

        if (Crud::PAGE_INDEX === $pageName) {
            return [$id, $user, $message, $key, $ip, $userAgent, $sessionId, $data, $createdAt];
        }
        if (Crud::PAGE_DETAIL === $pageName) {
            return [$id, $key, $message, $data, $ip, $userAgent, $sessionId, $createdAt, $updatedAt, $user];
        }
        if (Crud::PAGE_NEW === $pageName) {
            return [$key, $message, $ip, $userAgent, $sessionId, $createdAt, $updatedAt, $user];
        }
        if (Crud::PAGE_EDIT === $pageName) {
            return [$key, $message, $ip, $userAgent, $sessionId, $createdAt, $updatedAt, $user];
        }
    }
}
