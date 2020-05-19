<?php

namespace App\Form;

use App\Entity\Sortie;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SortieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom de l\'évènement',
                'attr' => [
                    'class' => 'nom-evenement',
                    'placeholder' => 'Nom'
                ]
            ])
            ->add('dateHeureDebut', DateTimeType::class, [
                'label' => 'Date et Heure de début',
                'attr' => [
                    'class' => 'date-debut'
                ]
            ])
            ->add('duree', IntegerType::class, [
                'label' => 'Durée de l\'évènement',
                'attr' => [
                    'class' => 'duree-evenement'
                ]
            ])
            ->add('dateLimiteInscription', DateTimeType::class, [
                'label' => 'Date limite d\'inscription',
                'attr' => [
                    'class' => 'date-limite-inscription'
                ]
            ])
            ->add('nbInscriptionMax', IntegerType::class, [
                'label' => 'Nombre d\'inscrits maximum',
                'attr' => [
                    'class' => 'nb-inscrits-max'
                ]
            ])
            ->add('infosSortie', TextareaType::class, [
                'label' => 'Description de l\'évènement',
                'attr' => [
                    'class' => 'description-evenement',
                    'placeholder' => 'Veuillez décrire votre évènement'
                ]
            ] )
            ->add('organisateur', TextType::class, [
                'label' => 'Organisateur',
                'attr' => [
                    'class' => 'organisateur'
                ]
            ] )
            ->add('lieu', TextType::class, [
                'label' => 'Lieu de l\'évènement',
                'attr' => [
                    'class' => 'lieu-evenement'
                ]

            ])
            ->add('etat', TextType::class, [
                'label' => 'Etat',
                'attr' => [
                    'class' => 'etat-evenement'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
        ]);
    }
}
