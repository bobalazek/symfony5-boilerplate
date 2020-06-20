<?php

namespace App\Form\Type;

use App\Entity\User;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\LocaleType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Intl\Locales;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class SettingsType.
 */
class SettingsType extends AbstractType
{
    /**
     * @var ParameterBagInterface
     */
    private $params;

    public function __construct(ParameterBagInterface $params)
    {
        $this->params = $params;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $locales = $this->params->get('app.locales');
        $localeChoices = Locales::getNames();
        foreach ($localeChoices as $locale => $localeChoice) {
            if (!in_array($locale, $locales)) {
                unset($localeChoices[$locale]);
            }
        }

        $builder
            ->add('name')
            ->add('username')
            ->add('email')
            ->add('locale', LocaleType::class, [
                'choice_loader' => null,
                'choices' => array_flip($localeChoices),
            ])
            ->add('bio', TextareaType::class, [
                'required' => false,
            ])
            ->add('city', TextType::class, [
                'required' => false,
            ])
            ->add('countryCode', CountryType::class, [
                'required' => false,
                'label' => 'Country',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection' => true,
            'data_class' => User::class,
            'validation_groups' => ['settings'],
        ]);
    }
}
