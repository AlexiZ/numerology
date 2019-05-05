<?php

namespace SiteBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', EmailType::class, [
                'required' => true,
                'label' => 'Votre email',
                'attr' => [
                    'placeholder' => 'Email'
                ],
            ])
            ->add('body', TextareaType::class, [
                'required' => true,
                'label' => false,
                'attr' => [
                    'rows' => 5,
                    'placeholder' => 'Votre message',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([]);
    }

    public function getBlockPrefix()
    {
        return 'site_bundle_contact';
    }
}