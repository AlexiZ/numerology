<?php

namespace AppBundle\Form;

use AppBundle\Entity\Numerologie;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
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
            ->add('noms', CollectionType::class, [
                'label' => 'Noms de famille (naissance, adoption, mariage)',
                'required' => true,
                'entry_type' => TextType::class,
                'allow_add' => true,
            ])
            ->add('prenoms', CollectionType::class, [
                'label' => 'Prénoms',
                'required' => true,
                'entry_type' => TextType::class,
                'allow_add' => true,
            ])
            ->add('pseudo', TextType::class, [
                'label' => 'Pseudonymes',
                'required' => false,
            ])

            ->add('dateNaissance', DateType::class, [
                'label' => 'Date et heure de naissance (heure universelle)',
                'widget' => 'single_text',
                'required' => true,
            ])
            ->add('lieuNaissance', TextType::class, [
                'label' => 'Lieu de naissance',
                'required' => true,
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Enregistrer',
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
