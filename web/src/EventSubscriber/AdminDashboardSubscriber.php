<?php

namespace App\EventSubscriber;

use App\Entity\User;
use App\Manager\UserActionManager;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Event\AfterEntityDeletedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\AfterEntityPersistedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityPersistedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityUpdatedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;

class AdminDashboardSubscriber implements EventSubscriberInterface
{
    /**
     * @var Security
     */
    private $security;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var UserActionManager
     */
    private $userActionManager;

    public function __construct(
        Security $security,
        EntityManagerInterface $em,
        UserActionManager $userActionManager
    ) {
        $this->security = $security;
        $this->em = $em;
        $this->userActionManager = $userActionManager;
    }

    public function beforeEntityPersisted(BeforeEntityPersistedEvent $event)
    {
        $entity = $event->getEntityInstance();

        if ($entity instanceof User) {
            $this->_validateUserChange($entity);
        }
    }

    public function afterEntityPersisted(AfterEntityPersistedEvent $event)
    {
        $entity = $event->getEntityInstance();

        $this->userActionManager->add(
            'admin.' . $this->_getClassKey($entity) . '.add',
            'User added a new entity',
            $entity->toArray()
        );
    }

    public function beforeEntityUpdated(BeforeEntityUpdatedEvent $event)
    {
        $entity = $event->getEntityInstance();

        $uow = $this->em->getUnitOfWork();
        $uow->computeChangeSets();
        $changeset = $uow->getEntityChangeSet($entity);

        if ($entity instanceof User) {
            $this->_validateUserChange($entity, $changeset);
        }

        $this->userActionManager->add(
            'admin.' . $this->_getClassKey($entity) . '.edit',
            'User edited an entity',
            [
                'id' => $entity->getId(),
                'changeset' => $changeset,
            ]
        );
    }

    public function afterEntityDeleted(AfterEntityDeletedEvent $event)
    {
        $entity = $event->getEntityInstance();

        $this->userActionManager->add(
            'admin.' . $this->_getClassKey($entity) . '.delete',
            'User deleted an entity',
            $entity->toArray()
        );
    }

    public static function getSubscribedEvents()
    {
        return [
            BeforeEntityUpdatedEvent::class => ['beforeEntityUpdated'],
            BeforeEntityPersistedEvent::class => ['beforeEntityPersisted'],
            AfterEntityPersistedEvent::class => ['afterEntityPersisted'],
            AfterEntityDeletedEvent::class => ['afterEntityDeleted'],
        ];
    }

    /* Helpers */
    private function _getClassKey($entity)
    {
        $converter = new CamelCaseToSnakeCaseNameConverter();

        return $converter->normalize(
            lcfirst((new \ReflectionClass($entity))->getShortName())
        );
    }

    private function _validateUserChange(User $entity, array $changeset = null)
    {
        /** @var User $userMyself */
        $userMyself = $this->security->getUser();
        $adminRoles = ['ROLE_ADMIN', 'ROLE_SUPER_ADMIN'];

        if (!$changeset) {
            $uow = $this->em->getUnitOfWork();
            $uow->computeChangeSets();
            $changeset = $uow->getEntityChangeSet($entity);
        }

        // General
        if (
            $userMyself === $entity &&
            $entity->isLocked()
        ) {
            throw new \Exception('You can not lock yourself.');
        }

        if (
            $entity->isSuperAdmin() &&
            $entity->isLocked()
        ) {
            throw new \Exception('You can not lock a super admin user.');
        }

        // Roles
        if (!isset($changeset['roles'])) {
            return;
        }

        $oldRoles = $changeset['roles'][0];
        $newRoles = $changeset['roles'][1];

        if (
            $userMyself === $entity && // Am I changing it myself?
            in_array('ROLE_SUPER_ADMIN', $oldRoles) && // Had a super admin role before
            !in_array('ROLE_SUPER_ADMIN', $newRoles) // Now I don't have it anymore
        ) {
            throw new \Exception('A super admin can not take away their own super admin role.');
        }

        if (
            !$userMyself->isSuperAdmin() && // The user that is changing the roles isn't a super admin
            array_intersect($oldRoles, $adminRoles) && // Was an admin or super admin
            !(in_array('ROLE_ADMIN', $newRoles) || in_array('ROLE_SUPER_ADMIN', $newRoles)) // Not an admin anymore
        ) {
            throw new \Exception('You can not take the admin roles away from an admin.');
        }

        foreach ($newRoles as $newRole) {
            if (
                !$userMyself->isSuperAdmin() && // The user that is changing isn't a super admin
                in_array($newRole, $adminRoles) && // Is a newly added role an admin role?
                !in_array($newRole, $oldRoles) // Did the user had that role before?
            ) {
                throw new \Exception('Only a super admin can assign new admin users.');
            }
        }
    }
}
