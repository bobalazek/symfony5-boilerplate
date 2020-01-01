<?php

namespace App\MessageHandler;

use App\Entity\User;
use App\Entity\UserExport;
use App\Manager\FileUploadManager;
use App\Message\UserExportRequest;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

/**
 * Class UserExportRequestHandler.
 */
class UserExportRequestHandler implements MessageHandlerInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var FileUploadManager
     */
    private $fileUploadManager;

    public function __construct(
        EntityManagerInterface $em,
        FileUploadManager $fileUploadManager
    ) {
        $this->em = $em;
        $this->fileUploadManager = $fileUploadManager;
    }

    public function __invoke(UserExportRequest $userExportRequest)
    {
        $userExport = $this->em
            ->getRepository(UserExport::class)
            ->findOneById($userExportRequest->getUserExportId())
        ;
        if (!$userExport) {
            throw new UnrecoverableMessageHandlingException();
        }

        $userExport
            ->setStatus(UserExport::STATUS_IN_PROGRESS)
            ->setStartedAt(new \DateTime())
        ;
        $this->em->persist($userExport);
        $this->em->flush();

        try {
            $zipFilePath = $this->_saveZip($userExport);
            $file = new File($zipFilePath);

            $response = $this->fileUploadManager->upload(
                $file,
                'export_' . md5($userExport->getId() . '.' . $userExport->getToken()) . '.zip',
                'user_exports/'
            );
            $expiresAt = (new \DateTime())->modify('+30 days');

            $userExport
                ->setStatus(UserExport::STATUS_COMPLETED)
                ->setFileKey($response['key'])
                ->setFileUrl($response['url'])
                ->setCompletedAt(new \DateTime())
                ->setExpiresAt($expiresAt)
            ;

            // TODO: unset the zip locally
        } catch (\Exception $e) {
            $userExport
                ->setStatus(UserExport::STATUS_FAILED)
                ->setFailedMessage($e->getMessage())
                ->setFailedAt(new \DateTime())
            ;
        }

        $this->em->persist($userExport);
        $this->em->flush();
    }

    public function _saveZip(UserExport $userExport)
    {
        $filesystem = new Filesystem();

        $sysTempDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR;
        $exportsDir = $sysTempDir . 'exports' . DIRECTORY_SEPARATOR;
        $exportsContentsDir = $exportsDir . 'contents' . DIRECTORY_SEPARATOR;
        $exportsArchivesDir = $exportsDir . 'archives' . DIRECTORY_SEPARATOR;
        $exportDir = $exportsContentsDir . $userExport->getId() . DIRECTORY_SEPARATOR;

        $filesystem->mkdir($exportsContentsDir);
        $filesystem->mkdir($exportsArchivesDir);
        $filesystem->mkdir($exportDir);

        $user = $userExport->getUser();

        $zipFile = $exportsArchivesDir . $userExport->getId() . '.zip';
        $zip = new \ZipArchive();
        $zip->open($zipFile, \ZipArchive::CREATE);

        // User
        $userJson = $exportDir . 'user.json';
        file_put_contents(
            $userJson,
            json_encode($user->toArray(), JSON_PRETTY_PRINT)
        );
        $zip->addFile($userJson, 'user.json');

        // User - avatar
        if ($user->getImageFileKey()) {
            $url = $user->getImageFileUrl();
            $ext = pathinfo($url, PATHINFO_EXTENSION);
            $name = 'avatar.' . $ext;
            $path = $exportDir . $name;

            file_put_contents(
                $path,
                file_get_contents($url)
            );
            $zip->addFile($path, $name);
        }

        $files = [
            [
                'filename' => 'user_actions.json',
                'method' => 'getUserActions',
            ],
            [
                'filename' => 'user_blocks.json',
                'method' => 'getUserBlocks',
            ],
            [
                'filename' => 'user_followers.json',
                'method' => 'getUserFollowers',
            ],
            [
                'filename' => 'user_notifications.json',
                'method' => 'getUserNotifications',
            ],
            [
                'filename' => 'user_points.json',
                'method' => 'getUserPoints',
            ],
        ];

        foreach ($files as $fileData) {
            if (isset($fileData['directory'])) {
                $imagesDir = $exportsDir . $fileData['directory'] . DIRECTORY_SEPARATOR;
                $filesystem->mkdir($imagesDir);

                $images = $user->{$fileData['method']}()->toArray();
                foreach ($images as $image) {
                    if (!$image->getImageFileKey()) {
                        continue;
                    }

                    $url = $image->getImageFileUrl();
                    $ext = pathinfo($url, PATHINFO_EXTENSION);
                    $name = $image->getId() . '.' . $ext;
                    $path = $imagesDir . $name;

                    file_put_contents(
                        $path,
                        file_get_contents($url)
                    );
                    $zip->addFile($path, $fileData['directory'] . '/' . $name);
                }
            } else {
                $fileJson = $exportDir . $fileData['filename'];
                $array = $user->{$fileData['method']}()->toArray();

                file_put_contents(
                    $fileJson,
                    json_encode(array_map(function ($entry) {
                        return $entry->toArray();
                    }, $array), JSON_PRETTY_PRINT)
                );
                $zip->addFile($fileJson, $fileData['filename']);
            }
        }

        $zip->close();

        return $zipFile;
    }
}
