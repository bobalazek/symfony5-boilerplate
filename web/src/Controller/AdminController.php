<?php

namespace App\Controller;

use App\Entity\User;
use App\Manager\UserActionManager;
use EasyCorp\Bundle\EasyAdminBundle\Controller\EasyAdminController;
use Psr\Container\ContainerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;

/**
 * Class AdminController.
 */
class AdminController extends EasyAdminController
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;

    /**
     * @var UserActionManager
     */
    private $userActionManager;

    /**
     * @var object|UserInterface|null
     */
    private $userMyself;

    public function __construct(
        ContainerInterface $container,
        UserPasswordEncoderInterface $passwordEncoder,
        UserActionManager $userActionManager
    ) {
        $this->container = $container;
        $this->passwordEncoder = $passwordEncoder;
        $this->userActionManager = $userActionManager;

        $this->userMyself = $this->getUser();
    }

    public function persistEntity($entity)
    {
        if ($entity instanceof User) {
            $this->_encodeUserPassword($entity);
        }

        $this->userActionManager->add(
            'admin.' . $this->_getClassKey($entity) . '.add',
            'User added a new entity',
            $entity->toArray()
        );

        parent::persistEntity($entity);
    }

    public function updateEntity($entity)
    {
        $uow = $this->em->getUnitOfWork();
        $uow->computeChangeSets();
        $changeset = $uow->getEntityChangeSet($entity);

        if ($entity instanceof User) {
            $this->_encodeUserPassword($entity);

            if (
                $this->userMyself === $entity &&
                $entity->isLocked()
            ) {
                throw new \Exception('You can not lock yourself');
            }

            if (
                $entity->isSuperAdmin() &&
                $entity->isLocked()
            ) {
                throw new \Exception('You can not lock a super admin user');
            }

            if (isset($changeset['roles'])) {
                $oldRoles = $changeset['roles'][0];
                $newRoles = $changeset['roles'][1];

                foreach ($newRoles as $newRole) {
                    if (
                        in_array($newRole, ['ROLE_ADMIN', 'ROLE_SUPER_ADMIN']) &&
                        !in_array($newRole, $oldRoles) &&
                        !$this->userMyself->isSuperAdmin()
                    ) {
                        throw new \Exception('Only a super admin can assign new admin users');
                    }
                }
            }
        }

        $this->userActionManager->add(
            'admin.' . $this->_getClassKey($entity) . '.edit',
            'User edited an entity',
            [
                'id' => $entity->getId(),
                'changeset' => $changeset,
            ]
        );

        parent::updateEntity($entity);
    }

    public function removeEntity($entity)
    {
        $this->userActionManager->add(
            'admin.' . $this->_getClassKey($entity) . '.delete',
            'User deleted an entity',
            $entity->toArray()
        );

        parent::removeEntity($entity);
    }

    /* Helpers */
    private function _getClassKey($entity)
    {
        $converter = new CamelCaseToSnakeCaseNameConverter();

        return $converter->normalize(
            lcfirst((new \ReflectionClass($entity))->getShortName())
        );
    }

    private function _encodeUserPassword($entity)
    {
        $plainPassword = $entity->getPlainPassword();
        if ($plainPassword) {
            $entity->setPassword(
                $this->passwordEncoder->encodePassword(
                    $entity,
                    $plainPassword
                )
            );
        }
    }
}
