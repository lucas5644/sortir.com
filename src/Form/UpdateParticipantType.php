<?php

namespace App\Form;

use App\Entity\Participant;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class UpdateParticipantType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('pseudo', TextType::class, [
                'label' => 'Pseudo :',
            ])
            ->add('nom', TextType::class, [
                'label' => 'Nom :',
            ])
            ->add('prenom', TextType::class, [
                'label' => 'Prénom :',
            ])
            ->add('telephone', TextType::class, [
                'label' => 'Téléphone :',
                'required' => false
            ])
            ->add('mail', EmailType::class, [
                'label' => 'Email :',
            ])
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'Les mots de passe doivent correspondre.',
                'options' => ['attr' => ['class' => 'password-field']],
                'required' => false,
                'first_options' => ['label' => 'Nouveau mot de passe :'],
                'second_options' => ['label' => 'Confirmer mot de passe :'],
            ])
            ->add('urlPhoto', FileType::class, [
                'label' => 'Télécharger vers le serveur',
                'mapped' => false,
                'required' => false,
                'row_attr' => ['class'=>'btn btn-light col-12'],
                'constraints' => [
                    new File([
                        'maxSize' => '100M',
                        'mimeTypes' => [
                            'image/png',
                            'image/jpeg',
                        ],
                        'mimeTypesMessage' => 'Veuillez uploader une image valide (extension .jpg/.png/.jpeg)',
                    ])
                ],
            ])
            #TODO L'ajout d'image de profil
            ->add('campus')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Participant::class,
        ]);
    }
}
