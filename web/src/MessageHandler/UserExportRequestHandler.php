<?php

namespace App\MessageHandler;

use App\Entity\User;
use App\Entity\UserExport;
use App\Message\UserExportRequest;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Vich\UploaderBundle\Templating\Helper\UploaderHelper;

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
     * @var UploaderHelper
     */
    private $uploaderHelper;

    public function __construct(
        EntityManagerInterface $em,
        UploaderHelper $uploaderHelper
    ) {
        $this->em = $em;
        $this->uploaderHelper = $uploaderHelper;
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

        // Persist & save so we can use and forward the ID when creating the zip
        $this->em->persist($userExport);
        $this->em->flush();

        try {
            $zipFilePath = $this->_saveZip($userExport);
            $file = new UploadedFile(
                $zipFilePath,
                $userExport->getId() . '.zip',
                null,
                null,
                true
            );

            $userExport
                ->setStatus(UserExport::STATUS_COMPLETED)
                ->setFile($file)
                ->setCompletedAt(new \DateTime())
                ->setExpiresAt((new \DateTime())->modify('+30 days'))
            ;
        } catch (\Exception $e) {
            $userExport
                ->setStatus(UserExport::STATUS_FAILED)
                ->setFailedMessage($e->getMessage())
                ->setFailedAt(new \DateTime())
            ;
        }

        $this->em->persist($userExport);
        $this->em->flush();

        // TODO: remove the archive and contents in the tmp file once it's uploaded
        // Can't do it in this request, as we would then remove the zip file,
        // because the upload happens later in the listerner!
    }

    public function _saveZip(UserExport $userExport): string
    {
        $filesystem = new Filesystem();

        $tmpDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR;
        $exportsDir = $tmpDir . 'exports' . DIRECTORY_SEPARATOR;
        $exportsContentsDir = $exportsDir . 'contents' . DIRECTORY_SEPARATOR;
        $exportsArchivesDir = $exportsDir . 'archives' . DIRECTORY_SEPARATOR;
        $exportDir = $exportsContentsDir . $userExport->getId() . DIRECTORY_SEPARATOR;

        $filesystem->mkdir($exportsContentsDir);
        $filesystem->mkdir($exportsArchivesDir);
        $filesystem->mkdir($exportDir);

        $user = $userExport->getUser();

        $zipFile = $exportsArchivesDir . $userExport->getId() . '.zip';
        $zip = new \ZipArchive();

        $zipResponse = $zip->open($zipFile, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
        if ($zipResponse !== true) {
            throw new \Exception('Could not create .zip file. Error: ' . $zipResponse);
        }

        // User
        $userJson = $exportDir . 'user.json';
        file_put_contents(
            $userJson,
            json_encode($user->toArray(), JSON_PRETTY_PRINT)
        );
        $zip->addFile($userJson, 'user.json');

        // User - avatar
        $userImageUrl = $this->uploaderHelper->asset($user, 'imageFile');
        if ($userImageUrl) {
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
                'filename' => 'user_devices.json',
                'method' => 'getUserDevices',
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

        $zip->close();

        return $zipFile;
    }
}
