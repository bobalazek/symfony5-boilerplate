<?php

namespace App\Manager;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Finder\Finder;

/**
 * Class AvatarManager.
 */
class AvatarManager
{
    /**
     * @var ParameterBagInterface
     */
    private $params;

    public function __construct(ParameterBagInterface $params)
    {
        $this->params = $params;
    }

    public function getFiles()
    {
        $files = [];
        $projectDir = $this->params->get('kernel.project_dir');
        $finder = new Finder();
        $finder->files()->in($projectDir . '/public/assets/images/avatars');

        foreach ($finder as $file) {
            $files[] = $file;
        }

        return $files;
    }
}
