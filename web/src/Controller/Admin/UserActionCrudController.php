<?php

namespace App\Controller\Admin;

use App\Entity\UserAction;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
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
            ->setDefaultSort(['id' => 'DESC'])
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
        $key = TextField::new('key');
        $message = TextareaField::new('message');
        $data = ArrayField::new('data')
            ->setTemplatePath('contents/admin/fields/data_field.html.twig')
        ;
        $ip = TextField::new('ip');
        $userAgent = TextareaField::new('userAgent');
        $sessionId = TextField::new('sessionId');
        $createdAt = DateTimeField::new('createdAt');

        return [
            $id,
            $user,
            $key,
            $message,
            // $data, // TODO: reenable when fixed
            $ip,
            $userAgent,
            $sessionId,
            $createdAt,
        ];
    }
}
