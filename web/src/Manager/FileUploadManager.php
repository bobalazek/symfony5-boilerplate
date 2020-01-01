<?php

namespace App\Manager;

use Aws\S3\S3Client;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * Class FileUploadManager.
 */
class FileUploadManager
{
    /**
     * @var ParameterBagInterface
     */
    private $params;

    /**
     * @var array
     */
    private $s3Params;

    /**
     * @var S3Client
     */
    private $s3Client;

    public function __construct(ParameterBagInterface $params)
    {
        $this->params = $params;

        $this->s3Params = $this->params->get('app.aws.s3');
        $this->s3Client = new S3Client([
            'version' => $this->s3Params['version'],
            'region' => $this->s3Params['region'],
            'credentials' => $this->params->get('app.aws'),
        ]);
    }

    public function upload($file, $fileName, $prefix = '')
    {
        $options = [
            'ContentType' => $file->getMimeType(),
            'ContentLength' => $file->getSize(),
        ];

        $key = $prefix . $fileName;

        $result = $this->s3Client->upload(
            $this->s3Params['bucket'],
            $key,
            file_get_contents($file->getPathname()),
            'public-read',
            ['params' => $options]
        );

        return [
            'key' => $key,
            'url' => $result->get('ObjectURL'),
            'version' => $result->get('VersionId'),
        ];
    }

    public function delete($key)
    {
        $result = $this->s3Client->deleteObject([
            'Bucket' => $this->s3Params['bucket'],
            'Key' => $key,
        ]);

        return [
            'version' => $result->get('VersionId'),
        ];
    }
}
