<?php

namespace App\Form;

use App\Entity\Lieu;
use App\Entity\Sortie;
use App\Entity\Ville;
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
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\DateTime;

class SortieType extends AbstractType
{

    /**
     * Rajoute un Lieu au formulaire en fonction de la ville sélectionnée
     * @param FormInterface $form
     * @param Ville $ville
     */
    private function addLieuField(FormInterface $form, ?Ville $ville)
    {
        $builder = $form->getConfig()->getFormFactory()->createNamedBuilder(
            'lieu',
            EntityType::class,
            null,
            [
                'class' => 'App\Entity\Lieu',
                'placeholder' => $ville ? 'Sélectionnez' : 'Sélectionnez la ville',
                'mapped' => false,
                'auto_initialize' => false,
                'required' => false,
                'choices' => $ville ? $ville->getLieux($ville) : []
            ]
        );
        $builder->addEventListener(
            FormEvents::POST_SUBMIT,
            function (FormEvent $event) {
                $form = $event->getForm();
            }
        );
        $form->add($builder->getForm());
    }


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
                ],
                'required' => false
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
                'label' => "Description de l'évènement : ",
                'attr' => [
                    'class' => 'description-evenement',
                    'placeholder' => 'Veuillez décrire votre évènement'
                ],
                'required' => false
            ])

        ->add('ville', EntityType::class, [
            'class' => 'App\Entity\Ville',
        'placeholder' => 'Sélectionnez',
        'mapped' => false,
        'required' => false,]);

        $builder->get('ville')->addEventListener(
            FormEvents::PRE_SUBMIT,
            function (FormEvent $event)
            {
/*                $ville = $event->getForm()->getData();*/
                $form = $event->getForm();
                $this->addLieuField($form->getParent(), $form->getData());
            }
        );

        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event)
            {
                $data = $event->getData();

                $lieu = $data->getLieu();
                $form = $event->getForm();
                if ($lieu) {
                    $ville = $lieu->getVille();
                    $this->addLieuField($form, $ville);

                    $form->get('ville')->setData($ville);
                    $form->get('lieu')->setData($lieu);
                } else {
                    $this->addLieuField($form, null);
                }
            }
        );

    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
        ]);
    }
}
