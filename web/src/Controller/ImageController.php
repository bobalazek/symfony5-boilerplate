<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Intervention\Image\ImageManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class ImageController.
 */
class ImageController extends AbstractController
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var ParameterBagInterface
     */
    private $params;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    public function __construct(
        TranslatorInterface $translator,
        ParameterBagInterface $params,
        EntityManagerInterface $em
    ) {
        $this->translator = $translator;
        $this->params = $params;
        $this->em = $em;
    }

    /**
     * @Route("/image/{entity_type}/{entity_id}.jpg", name="image.view")
     */
    public function view($entity_type, $entity_id)
    {
        $manager = new ImageManager(['driver' => 'imagick']);

        $width = 400;
        $height = 300;

        $availableEntities = [
            'user' => [
                'class' => User::class,
                'imageProperyMethod' => 'getImageFileUrl',
            ],
        ];
        $entityClass = isset($availableEntities[$entity_type])
            ? $availableEntities[$entity_type]['class']
            : null;
        $entity = null;
        $entityFileUrl = null;
        if ($entityClass) {
            $entity = $this->em->getRepository($entityClass)
                ->findOneById($entity_id);
        }
        if ($entity) {
            $method = $availableEntities[$entity_type]['imageProperyMethod'];
            $entityFileUrl = $entity->$method();
        }

        $imageKey = $entity_type . $entity_id;
        $imageLastModified = new \DateTime();

        if (
            !$entityClass ||
            !$entity ||
            !$entityFileUrl
        ) {
            $image = $manager->canvas($width, $height, '#ffffff');

            $image->text('No image found', $width / 2, $height / 2, function ($font) {
                $font->file('../../public/assets/fonts/open-sans/OpenSans-Regular.ttf');
                $font->size(24);
                $font->color('#000000');
                $font->align('center');
                $font->valign('middle');
            });
        } else {
            $image = $manager->make(file_get_contents($entityFileUrl));

            $image->widen($width, function ($constraint) {
                $constraint->upsize();
            });
            $image->heighten($height, function ($constraint) {
                $constraint->upsize();
            });
            $image->resizeCanvas($width, $height);

            $imageLastModified = $entity->getUpdatedAt();
        }

        $imageContent = $image->encode('jpg');

        $response = new Response();

        $dispositionHeader = HeaderUtils::makeDisposition(
            ResponseHeaderBag::DISPOSITION_INLINE,
            'image.jpg'
        );

        $response->headers->set('Content-Disposition', $dispositionHeader);
        $response->headers->set('Content-Type', 'image/jpeg');
        $response->headers->set('Content-Length', strlen($imageContent));

        $response->setContent($imageContent);
        $response->setSharedMaxAge(600);
        $response->setCache([
            'etag' => sha1($imageKey),
            'last_modified' => $imageLastModified,
            'max_age' => 600,
            's_maxage' => 600,
            'private' => true,
        ]);

        return $response;
    }
}
