<?php

namespace App\Form;

use App\Entity\UserTfaMethod;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class SettingsUserTfaMethodType.
 */
class SettingsUserTfaMethodType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (!$options['hide_enabled_field']) {
            $builder
                ->add('enabled', ChoiceType::class, [
                    'label' => false,
                    'choices' => [
                        'Enabled' => true,
                        'Disabled' => false,
                    ],
                ])
            ;
        }

        if ($options['show_code_field']) {
            $builder
                ->add('code', TextType::class, [
                    'label' => 'Code',
                    'required' => true,
                ])
            ;
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection' => true,
            'data_class' => UserTfaMethod::class,
            'validation_groups' => ['settings.tfa'],
            'hide_enabled_field' => false,
            'show_code_field' => false,
        ]);
    }
}
