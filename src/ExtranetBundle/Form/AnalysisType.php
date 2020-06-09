<?php

namespace ExtranetBundle\Form;

use ExtranetBundle\Entity\Analysis;
use ExtranetBundle\Services\Geocoding;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatorInterface;

class AnalysisType extends AbstractType
{
    /**
     * @var Geocoding
     */
    private $geocoding;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(Geocoding $geocoding, TranslatorInterface $translator)
    {
        $this->geocoding = $geocoding;
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('birthName', TextType::class, [
                'label' => 'Votre nom de famille de naissance',
                'required' => true,
                'attr' => [
                    'placeholder' => 'Votre nom de famille de naissance',
                ],
            ])
            ->add('useName', TextType::class, [
                'label' => 'Votre nom de famille d\'usage (si différent)',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Votre nom de famille d\'usage',
                ],
            ])
            ->add('firstname', TextType::class, [
                'label' => 'Votre premier prénom',
                'required' => true,
                'attr' => [
                    'placeholder' => 'Votre premier prénom',
                ],
            ])
            ->add('otherFirstnames', CollectionType::class, [
                'label' => 'Vos autres prénoms',
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
            ->add('pseudos', CollectionType::class, [
                'label' => 'Vos surnoms d\'usage',
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
            ->add('birthDate', TextType::class, [
                'label' => 'Vos date et heure de naissance',
                'required' => true,
                'attr' => [
                    'readonly' => 'true',
                ],
            ])
            ->add('birthPlace', TextType::class, [
                'label' => 'Votre lieu de naissance',
                'required' => true,
                'attr' => [
                    'placeholder' => 'Votre lieu de naissance',
                    'class' => 'locationGuesser',
                ],
            ])
            ->add('birthPlaceCoordinates', HiddenType::class, [
                'mapped' => false,
            ])
            ->add('email', EmailType::class, [
                'label' => 'Votre email (facultatif)',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Votre email (facultatif)',
                ],
            ])
            ->add('referrer', HiddenType::class, [
                'data' => $_SERVER['HTTP_REFERER'],
            ])
        ;

        $builder->addEventListener(FormEvents::POST_SET_DATA, function(FormEvent $event) {
            /** @var Analysis $data */
            $data = $event->getData();
            $birthPlaceCoordinatesStringed = $data->getBirthPlaceCoordinates() ? implode(',', array_merge([
                $this->geocoding->DMStoDEC($data->getBirthPlaceCoordinates()['lat']),
                $this->geocoding->DMStoDEC($data->getBirthPlaceCoordinates()['lng'])
            ])) : '';
            /** @var \DateTime $birthDate */
            $birthDate = $data->getBirthDate();

            /** @var Form $form */
            $form = $event->getForm();
            $form->get('birthPlaceCoordinates')->setData($birthPlaceCoordinatesStringed);
            $form->get('birthDate')->setData($birthDate ? $birthDate->format('d/m/Y H:i') : null);
        });

        $builder->addEventListener(FormEvents::SUBMIT, function(FormEvent $event) use ($options) {
            /** @var Form $form */
            $form = $event->getForm();
            $birthPlaceCoordinatesStringed = $form->get('birthPlaceCoordinates')->getData();

            // Prevent case when autocomplete has not been used
            if ($birthPlaceCoordinatesStringed == "0,0") {
                $form->addError(new FormError($options['translator']->trans('analysis.form.error.birthPlace')));
            } else {
                /** @var Analysis $data */
                $data = $event->getData();
                $birthPlaceCoordinatesArray = explode(',', $birthPlaceCoordinatesStringed);
                $birthPlaceCoordinates = [
                    'lat' => $this->geocoding->DECtoDMS($birthPlaceCoordinatesArray[0], true),
                    'lng' => $this->geocoding->DECtoDMS($birthPlaceCoordinatesArray[1], true),
                ];
                $data->setBirthPlaceCoordinates($birthPlaceCoordinates);
            }

            // Prevent case when birthdate isn't what it should be
            $birthDate = \DateTime::createFromFormat('d/m/Y H:i', $form->get('birthDate')->getData());
            if (!is_a($birthDate, 'DateTime')) {
                $form->addError(new FormError($options['translator']->trans('analysis.form.error.birthDate')));
            } else {
                $data->setBirthDate($birthDate);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Analysis::class,
            'translator' => $this->translator,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'app_bundle_numerologie';
    }
}
