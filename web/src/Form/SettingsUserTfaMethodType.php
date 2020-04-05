<?php

namespace App\Form;

use App\Entity\UserTfaMethod;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class SettingsUserTfaMethodType.
 */
class SettingsUserTfaMethodType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('enabled', CheckboxType::class, [
                'label' => 'Enabled',
                'required' => false,
            ])
            // TODO
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => UserTfaMethod::class,
            'validation_groups' => ['settings.tfa'],
        ]);
    }
}