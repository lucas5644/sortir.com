<?php


namespace App\Controller;


use App\Entity\Participant;
use App\Form\ParticipantType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class ParticipantController extends AbstractController
{
    /**
     * @Route("/signIn", name="signIn")
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

        return $this->render("User/signIn.html.twig", [
           "userForm" => $userForm->createView()
        ]);
    }
}