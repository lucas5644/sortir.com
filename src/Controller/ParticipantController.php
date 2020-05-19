<?php


namespace App\Controller;


use App\Entity\Participant;
use App\Form\ParticipantType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class ParticipantController extends AbstractController
{
    /**
     * @Route("/signIn", name="signIn")
     */
    public function signInForm()
    {
        $utilisateur = new Participant();
        $productForm = $this->createForm(ParticipantType::class, $utilisateur);

        return $this->render("User/signIn.html.twig", [
           "productForm" => $productForm->createView()
        ]);
    }
}