<?php

namespace App\Form\Type;

use App\Entity\User;
use App\Manager\AvatarManager;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class SettingsImageType.
 */
class SettingsImageType extends AbstractType
{
    /**
     * @var ParameterBagInterface
     */
    private $params;

    /**
     * @var AvatarManager
     */
    private $avatarManager;

    public function __construct(ParameterBagInterface $params, AvatarManager $avatarManager)
    {
        $this->params = $params;
        $this->avatarManager = $avatarManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $avatarImages = [];
        foreach ($this->avatarManager->getFiles() as $file) {
            $filename = $file->getRelativePathname();
            $avatarImages[$filename] = $filename;
        }

        $builder
            ->add('imageFile', FileType::class, [
                'required' => false,
                'label' => 'Profile picture',
            ])
            ->add('avatarImage', ChoiceType::class, [
                'required' => false,
                'label' => 'Avatar',
                'choices' => $avatarImages,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection' => true,
            'data_class' => User::class,
            'validation_groups' => ['settings.image'],
        ]);
    }
}
