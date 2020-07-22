<?php

namespace App\EventSubscriber;

use App\Manager\UserActionManager;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Event\AfterEntityDeletedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\AfterEntityPersistedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\AfterEntityUpdatedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityPersistedEvent;
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
        $this->em = $em;
        $this->userActionManager = $userActionManager;
    }

        public function beforeEntityPersisted(BeforeEntityPersistedEvent $event)
    {
        $entity = $event->getEntityInstance();

        $userMyself = $this->security->getUser();
        $uow = $this->em->getUnitOfWork();
        $uow->computeChangeSets();
        $changeset = $uow->getEntityChangeSet($entity);

        if (!($entity instanceof User)) {
            return;
        }

        if (
            $userMyself === $entity &&
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

        if (!isset($changeset['roles'])) {
            return;
        }

        $oldRoles = $changeset['roles'][0];
        $newRoles = $changeset['roles'][1];

        foreach ($newRoles as $newRole) {
            if (
                in_array($newRole, ['ROLE_ADMIN', 'ROLE_SUPER_ADMIN']) &&
                !in_array($newRole, $oldRoles) &&
                !$userMyself->isSuperAdmin()
            ) {
                throw new \Exception('Only a super admin can assign new admin users');
            }
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

    public function afterEntityUpdated(AfterEntityUpdatedEvent $event)
    {
        $entity = $event->getEntityInstance();

        $uow = $this->em->getUnitOfWork();
        $uow->computeChangeSets();
        $changeset = $uow->getEntityChangeSet($entity);

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
            BeforeEntityPersistedEvent::class => ['beforeEntityPersisted'],
            AfterEntityPersistedEvent::class => ['afterEntityPersisted'],
            AfterEntityUpdatedEvent::class => ['afterEntityUpdated'],
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
}
