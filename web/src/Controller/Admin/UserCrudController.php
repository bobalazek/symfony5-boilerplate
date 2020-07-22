<?php

namespace App\Controller\Admin;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class UserCrudController extends AbstractCrudController
{
    /**
     * @var ParameterBagInterface
     */
    private $params;

    public function __construct(ParameterBagInterface $params)
    {
        $this->params = $params;
    }

    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setSearchFields(['id', 'username', 'email', 'name', 'roles', 'locale', 'newEmail', 'avatarImage', 'bio', 'city', 'countryCode', 'lockedReason'])
        ;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->disable('new', 'delete')
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        $id = IdField::new('id');
        $name = TextField::new('name');
        $username = TextField::new('username');
        $email = TextField::new('email');
        $roles = ChoiceField::new('roles')
            ->setChoices(array_flip($this->params->get('app.roles')))
            ->allowMultipleChoices(true)
        ;
        $locale = TextField::new('locale');
        $countryCode = TextField::new('countryCode');
        $city = TextField::new('city');
        $private = Field::new('private');
        $locked = Field::new('locked');
        $lockedReason = TextField::new('lockedReason');
        $deletedAt = DateTimeField::new('deletedAt');
        $createdAt = DateTimeField::new('createdAt');

        return [
            $id,
            $name,
            $username,
            $email,
            $roles,
            $locale,
            $countryCode,
            $city,
            $private,
            $locked,
            $lockedReason,
            $deletedAt,
            $createdAt,
        ];
    }
}
