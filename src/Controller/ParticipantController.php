<?php


namespace App\Controller;


use App\Entity\Participant;
use App\Form\ParticipantType;
use App\Form\UpdateParticipantType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Security;

class ParticipantController extends AbstractController
{

    private $entityManager;
    private $security;

    public function __construct(EntityManagerInterface $entityManager, Security $security)
    {
        $this->entityManager = $entityManager;
        $this->security = $security;
    }

    /**
     * @Route("/register", name="register")
     * @param Request $request
     * @param EntityManagerInterface $em
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function signInForm(Request $request, EntityManagerInterface $em, UserPasswordEncoderInterface $passwordEncoder)
    {
        $utilisateur = new Participant();
        $userForm = $this->createForm(ParticipantType::class, $utilisateur);

        $userForm->handleRequest($request);
        dump($utilisateur);
        if($userForm->isSubmitted() && $userForm->isValid())
        {
            $password = $passwordEncoder->encodePassword($utilisateur, $utilisateur->getPassword());
            $utilisateur->setPassword($password);
            $utilisateur->setAdministrateur(false);
            $utilisateur->setActif(true);
            $em->persist($utilisateur);
            $em->flush();
            $this->addFlash("success", "Vous êtes inscrit!!! Bienvenue ".$utilisateur->getPseudo());
            return $this->redirectToRoute('home');
        }

        return $this->render("User/register.html.twig", [
           "userForm" => $userForm->createView()
        ]);
    }

    /**
     * @Route("profil/{pseudo}", name="profile")
     */
    public function afficherProfil($pseudo, Request $request, EntityManagerInterface $em, UserPasswordEncoderInterface $passwordEncoder){
        $user = $this->entityManager->getRepository(Participant::class)->findOneBy(['pseudo' => $pseudo]);
        $userC = $this->security->getUser();
        $oldPassword = $userC->getPassword();

        if($userC ==! $user){
            return $this->render("User/profil.html.twig", [
                "user" => $user
            ]);
        }else{
            $userForm = $this->createForm(UpdateParticipantType::class, $user);

            $userForm->handleRequest($request);
            dump($user);
            if($userForm->isSubmitted() && $userForm->isValid())
            {
                if(strlen(trim($user->getPassword())) ==! 0){
                    $password = $passwordEncoder->encodePassword($user, $user->getPassword());
                    $user->setPassword($password);
                }else{
                    $user->setPassword($oldPassword);
                }
                $em->persist($user);
                $em->flush();
                return $this->redirectToRoute('home');
            }
            return $this->render("User/monProfil.html.twig", [
                "user" => $user,
                "userForm" => $userForm->createView()
            ]);
        }


    }
}