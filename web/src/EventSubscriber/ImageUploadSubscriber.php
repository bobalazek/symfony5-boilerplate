<?php

namespace App\EventSubscriber;

use App\Entity\User;
use App\Manager\FileUploadManager;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use Symfony\Component\Security\Core\Security;

/**
 * Class ImageUploadSubscriber.
 */
class ImageUploadSubscriber implements EventSubscriber
{
    /**
     * @var FileUploadManager
     */
    private $fileUploadManager;

    /**
     * @var Security
     */
    private $security;

    public function __construct(
        FileUploadManager $fileUploadManager,
        Security $security
    ) {
        $this->fileUploadManager = $fileUploadManager;
        $this->security = $security;
    }

    public function getSubscribedEvents()
    {
        return [
            Events::postPersist,
            Events::postRemove,
            Events::postUpdate,
        ];
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        $this->_process('persist', $args);
    }

    public function postRemove(LifecycleEventArgs $args)
    {
        $this->_process('remove', $args);
    }

    public function postUpdate(LifecycleEventArgs $args)
    {
        $this->_process('update', $args);
    }

    private function _process(string $action, LifecycleEventArgs $args)
    {
        $entity = $args->getObject();

        if ('remove' === $action) {
            if (!method_exists($entity, 'getImageFileKey')) {
                return;
            }

            if ($entity->getImageFileKey()) {
                $this->fileUploadManager->delete(
                    $entity->getImageFileKey()
                );
            }

            return;
        }

        if (
            method_exists($entity, 'setUser') &&
            method_exists($entity, 'getImageFileUrl')
        ) {
            $entity->setUser(
                $this->security->getUser()
            );
        }

        if ($entity instanceof User) {
            $this->_processImageUpload(
                $entity,
                $entity->getId(),
                'user_image_files/original/',
                $args
            );
        }
    }

    private function _processImageUpload(
        $entity,
        $entityId,
        $uploadPath,
        LifecycleEventArgs $args
    ) {
        $imageFile = $entity->getImageFile();
        if ($imageFile) {
            if ($entity->getImageFileKey()) {
                $this->fileUploadManager->delete(
                    $entity->getImageFileKey()
                );
            }

            $imageFileName = $entityId . '.' .
                md5(random_bytes(32)) . '.' .
                $imageFile->guessExtension();
            $response = $this->fileUploadManager->upload(
                $imageFile,
                $imageFileName,
                $uploadPath
            );

            $entity
                ->setImageFileKey($response['key'])
                ->setImageFileUrl($response['url'])
                ->setImageFile(null)
                // this function will run 2x in the lifecycle (persist & update),
                //   so we need to prevent it from uploading,
                //   and deleting the file 2x
            ;
        }
    }
}
