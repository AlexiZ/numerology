<?php

namespace ExtranetBundle\Form;

use ExtranetBundle\Entity\Number;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NumberType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        foreach (Number::getProperties() as $property) {
            $builder
                ->add($property, HiddenType::class, [
                    'label' => $property,
                    'required' => false,
                ])
            ;
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Number::class,
        ]);
    }
}
