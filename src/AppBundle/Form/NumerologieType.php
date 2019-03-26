<?php

namespace AppBundle\Form;

use AppBundle\Entity\Numerologie;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NumerologieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('birthName', TextType::class, [
                'label' => 'Nom de naissance',
                'required' => true,
            ])
            ->add('useName', TextType::class, [
                'label' => 'Nom d\'usage (si différent du nom d\'usage)',
                'required' => false,
            ])
            ->add('firstname', TextType::class, [
                'label' => 'Premier prénom',
                'required' => true,
            ])
            ->add('otherFirstnames', CollectionType::class, [
                'label' => 'Autres prénoms',
                'required' => true,
                'entry_type' => TextType::class,
                'allow_add' => true,
                'allow_delete' => true,
            ])

            ->add('birthDate', DateTimeType::class, [
                'label' => 'Date et heure de naissance (heure universelle)',
                'widget' => 'single_text',
                'required' => true,
            ])
            ->add('birthPlace', TextType::class, [
                'label' => 'Lieu de naissance',
                'required' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Numerologie::class,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'app_bundle_numerologie';
    }
}
