<?php


namespace App\Controller;


use App\Entity\Participant;
use App\Form\ParticipantType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ParticipantController extends AbstractController
{
    /**
     * @Route("/signIn", name="signIn")
     */
    public function signInForm(Request $request, EntityManagerInterface $em)
    {
        $utilisateur = new Participant();
        $userForm = $this->createForm(ParticipantType::class, $utilisateur);

        $userForm->handleRequest($request);
        dump($utilisateur);
        if($userForm->isSubmitted() && $userForm->isValid())
        {
            $utilisateur->setAdministrateur(false);
            $utilisateur->setActif(true);
            $em->persist($utilisateur);
            return $this->redirectToRoute('home.html.twig', ['id'=>$utilisateur->getId()]);
        }

        return $this->render("User/signIn.html.twig", [
           "userForm" => $userForm->createView()
        ]);
    }
}