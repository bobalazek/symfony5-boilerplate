<?php

namespace App\Controller\Admin;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
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
        $username = TextField::new('username');
        $email = TextField::new('email');
        $roles = ChoiceField::new('roles')->setChoices(array_flip($this->params->get('app.roles')))->allowMultipleChoices(true);
        $countryCode = TextField::new('countryCode');
        $city = TextField::new('city');
        $private = Field::new('private');
        $locked = Field::new('locked');
        $lockedReason = TextField::new('lockedReason');
        $createdAt = DateTimeField::new('createdAt');
        $updatedAt = DateTimeField::new('updatedAt');
        $emailConfirmedAt = DateTimeField::new('emailConfirmedAt');
        $id = IntegerField::new('id', 'ID');
        $name = TextField::new('name');
        $locale = TextField::new('locale');
        $newEmail = TextField::new('newEmail');
        $bio = TextareaField::new('bio');
        $tfaEnabled = Field::new('tfaEnabled');
        $deletedAt = DateTimeField::new('deletedAt');

        if (Crud::PAGE_INDEX === $pageName) {
            return [$id, $username, $email, $roles, $countryCode, $city, $private, $locked, $lockedReason, $createdAt, $updatedAt];
        }
        if (Crud::PAGE_DETAIL === $pageName) {
            return [$id, $username, $email, $name, $roles, $locale, $newEmail, $bio, $city, $countryCode, $locked, $lockedReason, $private, $tfaEnabled, $emailConfirmedAt, $deletedAt, $createdAt, $updatedAt];
        }
        if (Crud::PAGE_NEW === $pageName) {
            return [$username, $email, $roles, $countryCode, $city, $private, $locked, $lockedReason];
        }
        if (Crud::PAGE_EDIT === $pageName) {
            return [$username, $email, $roles, $countryCode, $city, $locked, $lockedReason];
        }
    }
}
