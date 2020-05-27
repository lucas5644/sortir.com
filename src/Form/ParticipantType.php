<?php

namespace App\Form;

use App\Entity\Participant;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ParticipantType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('pseudo', TextType::class, [
                'label' => 'Pseudo',
                'required' => true,
            ])
            ->add('prenom', TextType::class, [
                 'label' => 'Prénom : ',
                 'required' => true,
            ])

            ->add('nom', TextType::class, [
                'label' => 'Nom : ',
                'required' => true,
            ])

            ->add('telephone', TextType::class, [
                'label' => 'Téléphone : ',
                'required' => false
            ])

            ->add('mail', EmailType::class, [
                'label' => 'Email : ',
                'required' => true,
            ])

            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'first_options' => ['label' => 'Nouveau mot de passe'],
                'invalid_message' => 'Les mots de passe doivent correspondre.',
                'options' => ['attr' => ['class' => 'password-field']],
                'required' => true,
                'second_options' => ['label' => 'Confirmation'],
            ])

            ->add('administrateur', CheckboxType::class, [
                'label' => 'Administrateur ?',
                'required' => false,
            ])

            ->add('actif')

            ->add('urlPhoto')

            ->add('campus');
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Participant::class,
        ]);
    }
}
