<?php

namespace App\Form;

use App\Entity\Inscription;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InscriptionType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('dateInscription', TextType::class, array(
                'required' => false,
                'empty_data' => null,
                'attr' => array(
                    'placeholder' => 'mm/dd/yyyy'
                )))
            ->add('sortie', TextType::class, [
                'label' => 'Sortie : ',
                'attr' => [
                    'class' => 'Sortie'
                ]
            ] )
            ->add('participant', TextType::class, [
                'label' => 'Organisateur : ',
                'attr' => [
                    'class' => 'organisateur'
                ]
            ] )
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Inscription::class,
        ]);
    }
}
