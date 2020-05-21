<?php

namespace App\Form;

use App\Entity\Lieu;
use App\Entity\Sortie;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\DateTime;

class SortieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom de l\'évènement : ',
                'attr' => [
                    'class' => 'nom-evenement',
                    'placeholder' => 'Nom'
                ]
            ])
            ->add('dateHeureDebut', DateTimeType::class, [
                'label' => 'Date et Heure de l\'évènement : ',
                'time_widget' => 'single_text',
                'date_widget' => 'single_text',
                'data' => new \DateTime("now"),
                'format' => 'yyy-MM-dd',
                'attr' => [
                    'class' => 'date-debut'
                ]
            ])

            ->add('duree', IntegerType::class, [
                'label' => 'Durée de l\'évènement ( en minutes ): ',
                'attr' => [
                    'class' => 'duree-evenement'
                ]
            ])
            ->add('dateLimiteInscription', DateTimeType::class, [
                'label' => 'Date limite d\'inscription : ',
                'time_widget' => 'single_text',
                'date_widget' => 'single_text',
                'data' => new \DateTime("now"),
                'format' => 'yyy-MM-dd',
                'attr' => [
                    'class' => 'date-limite-inscription'
                ]
            ])
            ->add('nbInscriptionMax', IntegerType::class, [
                'label' => 'Nombre de places : ',
                'attr' => [
                    'class' => 'nb-inscrits-max'
                ]
            ])
            ->add('infosSortie', TextareaType::class, [
                'label' => 'Description de l\'évènement : ',
                'attr' => [
                    'class' => 'description-evenement',
                    'placeholder' => 'Veuillez décrire votre évènement'
                ]
            ] )
            ->add('organisateur', TextType::class, [
                'label' => 'Organisateur : ',
                'attr' => [
                    'class' => 'organisateur'
                ]
            ] )

/*            ->add('lieu', ChoiceType::class, [
                'label' => "Lieu de l'évènement : ",
                'attr' => [
                    'class' => 'lieu-evenement'
                ]

            ])*/

            /*->add('lieu',EntityType::class,[
                'class'=>'App\Entity\Lieu',
                'placeholder' => 'Selectionnez',
                'mapped' => false,
    ])*/

            ->add('ville',EntityType::class,[
                'class'=>'App\Entity\Ville',
                'placeholder' => 'Selectionnez',
                'mapped' => false,
            ])

            ->add('etat', TextType::class, [
                'label' => 'Etat : ',
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
