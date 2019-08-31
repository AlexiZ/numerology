<?php

namespace ExtranetBundle\Form;

use ExtranetBundle\Entity\Analysis;
use ExtranetBundle\Services\Geocoding;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AnalysisType extends AbstractType
{
    /**
     * @var Geocoding
     */
    private $geocoding;

    public function __construct(Geocoding $geocoding)
    {
        $this->geocoding = $geocoding;
    }

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
                'label' => 'Nom de famille d\'usage (si différent)',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Nom de famille d\'usage',
                ],
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
            ->add('birthDate', TextType::class, [
                'label' => 'Date et heure de naissance',
                'required' => true,
            ])
            ->add('birthPlace', TextType::class, [
                'label' => 'Lieu de naissance',
                'required' => true,
                'attr' => [
                    'placeholder' => 'Lieu de naissance',
                    'class' => 'locationGuesser',
                ],
            ])
            ->add('birthPlaceCoordinates', HiddenType::class, [
                'mapped' => false,
            ])
        ;

        $builder->addEventListener(FormEvents::POST_SET_DATA, function(FormEvent $event) {
            /** @var Analysis $data */
            $data = $event->getData();
            $birthPlaceCoordinatesStringed = implode(',', array_merge([
                $this->geocoding->DMStoDEC($data->getBirthPlaceCoordinates()['lat']),
                $this->geocoding->DMStoDEC($data->getBirthPlaceCoordinates()['lng'])
            ]));
            /** @var \DateTime $birthDate */
            $birthDate = $data->getBirthDate();

            /** @var Form $form */
            $form = $event->getForm();
            $form->get('birthPlaceCoordinates')->setData($birthPlaceCoordinatesStringed);
            $form->get('birthDate')->setData($birthDate ? $birthDate->format('d/m/Y H:i') : null);
        });

        $builder->addEventListener(FormEvents::SUBMIT, function(FormEvent $event) {
            /** @var Form $form */
            $form = $event->getForm();
            $birthPlaceCoordinatesStringed = $form->get('birthPlaceCoordinates')->getData();

            // Prevent case when autocomplete has not been used
            if ($birthPlaceCoordinatesStringed == "0,0") {
                $form->addError(new FormError('Vous devez saisir un lieu de naissance dans le champ "lieu de naissance" puis sélectionner une proposition dans la liste déroulante qui apparaît pendant votre saisie.'));
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
                $form->addError(new FormError('La date de naissance saisie n\'est pas correcte, cliquez dans le champ pour utiliser le sélecteur de date et d\'heure. Vous pouvez également inscrire la date et l\'heure à la main sur le modèle suivante : "JJ/MM/AAAA HH:MM" (jour, mois, année, heure puis minutes).'));
            } else {
                $data->setBirthDate($birthDate);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Analysis::class,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'app_bundle_numerologie';
    }
}
