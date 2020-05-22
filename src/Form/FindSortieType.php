<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\FindSortie;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FindSortieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nomSortie', TextType::class, [
                'required' => false,
                'label' => 'Le nom de la sortie contient : '
            ])
//            ->add('nomCampus', EntityType::class, [
//                'label' => 'Choisir le campus : ',
//                'required' => false,
//                'choice_label' => 'nom',
//                'class' => Campus::class,
//                'placeholder' => 'Choisir un campus',
//                'query_builder' => function (EntityRepository $er) {
//                    return $er->createQueryBuilder('o')
//                        ->groupBy('o.nom');
//                },
//            ])

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
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => FindSortie::class,
            'method' => 'get',
            'csrf-protection' => false
        ]);
    }

    public function getBlockPrefix()
    {
        return '';
    }
}
