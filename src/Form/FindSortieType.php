<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\FindSortie;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\RadioType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FindSortieType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nomSortie', TextType::class, [
                'required' => false,
                'label' => 'Le nom contient : '
            ])
            ->add('nomCampus', EntityType::class, [
                'label' => 'Choisir le campus : ',
                'required' => false,
                'choice_label' => 'nom',
                'class' => Campus::class,
                'placeholder' => 'Choisir un campus',
            ])

            ->add('dateDebut', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Entre le :',
                'required' => false,
            ])

            ->add('dateFin', DateType::class, [
                'widget' => 'single_text',
                'label' => '... et le :',
                'required' => false,
            ])
            ->add('mesSorties', CheckboxType::class, [
                'label' => '...dont je suis l\'organisateur/trice',
                'required' => false
            ])
            ->add('sortiesPassees', CheckboxType::class, [
                'label' => '...passées',
                'required' => false
            ])
            ->add('mesInscriptions', ChoiceType::class, [
                'choices' => [
                    'Toutes les sorties' => null,
                    'Les sorties auxquelles je suis inscrit' => true,
                    'les sorties auxquelles je ne suis pas inscrit' => false,
                ],
                'expanded' => false,
                'label' => false,
            ])
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => FindSortie::class,
            'method' => 'get',
            'csrf-protection' => false
        ]);
    }

    /**
     * @return string|null
     */
    public function getBlockPrefix()
    {
        return '';
    }
}
