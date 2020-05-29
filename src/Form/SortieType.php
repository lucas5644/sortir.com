<?php

namespace App\Form;

use App\Entity\Lieu;
use App\Entity\Sortie;
use App\Entity\Ville;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
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
    private $em;

    /**
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
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
                'label' => false,
                'time_widget' => 'single_text',
                'date_widget' => 'single_text',
                'data' => new \DateTime("now"),
                'format' => 'yyy-MM-dd',
                'attr' => [
                    'class' => 'date-debut',

                ]
            ])

            ->add('duree', IntegerType::class, [
                'label' => 'Durée de l\'évènement : ',
                'attr' => [
                    'class' => 'duree-evenement',
                    'placeholder' => 'En minutes'
                ],
                'required' => false
            ])

            ->add('dateLimiteInscription', DateTimeType::class, [
                'label' => false,
                'time_widget' => 'single_text',
                'date_widget' => 'single_text',
                'data' => new \DateTime("now"),
                'format' => 'yyy-MM-dd',
                'attr' => [
                    'class' => 'date-limite-inscription'
                ]
            ])

            ->add('nbInscriptionMax', IntegerType::class, [
                'label' => 'Nombre de places maximum : ',
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
            ->add('creer', SubmitType::class, [
                'attr' => ['class' => 'btn btn-info']

            ])
            ->add('creerEtPublier', SubmitType::class, [
                'attr' => ['class' => 'btn btn-info']
            ]);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, array($this, 'onPreSetData'));
        $builder->addEventListener(FormEvents::PRE_SUBMIT, array($this, 'onPreSubmit'));
    }

    protected function addElements(FormInterface $form, Ville $ville = null)
    {
        $form->add('ville', EntityType::class, [
            'class' => 'App\Entity\Ville',
            'placeholder' => 'Sélectionnez',
            'mapped' => false,
            'data' => $ville,
            'required' => true
        ]);

        $lieux = array();

        if($ville){

            $repoLieu = $this->em->getRepository(Lieu::class);

            $lieux = $repoLieu->createQueryBuilder('q')
                ->where("q.ville = :villeid")
                ->setParameter("villeid", $ville->getId())
                ->getQuery()
                ->getResult();
            }

            $form->add('lieu', EntityType::class, array(
                'class' => 'App:Lieu',
                'placeholder' => 'Sélectionnez la ville avant',
                'mapped' => true,
                'auto_initialize' => false,
                'required' => true,
                'choices' => $lieux
            ));

    }

    function onPreSubmit(FormEvent $event)
    {
        $form = $event->getForm();
        $data = $event->getData();

        $ville = $this->em->getRepository(Ville::class)->find($data['ville']);
        $lieu = $this->em->getRepository(Lieu::class)->find($data['lieu']);

        $this->addElements($form,$ville);
    }

    function onPreSetData(FormEvent $event) {
        $sortie = $event->getData();
        $form = $event->getForm();

        $ville = null;

        $this->addElements($form, $ville);
    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'sortie';
    }
}
