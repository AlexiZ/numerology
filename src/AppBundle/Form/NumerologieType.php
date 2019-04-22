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
                'label' => 'Nom de famille de naissance',
                'required' => true,
                'attr' => [
                    'placeholder' => 'Nom de famille de naissance',
                ],
            ])
            ->add('useName', TextType::class, [
                'label' => 'Nom de famille d\'usage (si différent de naissance)',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Nom de famille d\'usage',
                ],
            ])
            ->add('pseudos', CollectionType::class, [
                'label' => 'Pseudonymes',
                'required' => false,
                'entry_type' => TextType::class,
                'entry_options' => [
                    'attr' => [
                        'class' => 'form-control',
                    ],
                ],
                'allow_add' => true,
                'allow_delete' => true,
                'delete_empty' => true,
            ])

            ->add('firstname', TextType::class, [
                'label' => 'Premier prénom',
                'required' => true,
                'attr' => [
                    'placeholder' => 'Premier prénom',
                ],
            ])
            ->add('otherFirstnames', CollectionType::class, [
                'label' => 'Autres prénoms',
                'required' => false,
                'entry_type' => TextType::class,
                'entry_options' => [
                    'attr' => [
                        'class' => 'form-control',
                    ],
                ],
                'allow_add' => true,
                'allow_delete' => true,
            ])

            ->add('birthDate', DateTimeType::class, [
                'label' => 'Date et heure de naissance (heure universelle)',
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('birthPlace', TextType::class, [
                'label' => 'Lieu de naissance',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Lieu de naissance',
                ],
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
