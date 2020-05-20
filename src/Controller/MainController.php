<?php

namespace App\Controller;

use App\Entity\Campus;
use App\Entity\Sortie;
use App\Form\SearchSortieType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function index(Request $request, EntityManagerInterface $em)
    {
        //Nouvelle instance Sortie
        $sortie = new Sortie();

        //récupération de la liste des campus
        $campusRepo = $this->getDoctrine()->getRepository(Campus::class);
        $campus = $campusRepo->findAll();

        $sortieForm = $this->createForm(SearchSortieType::class, $sortie);
        $sortieForm->handleRequest($request);
        if ($sortieForm->isSubmitted() && $sortieForm->isValid()) {
            $em->persist($sortie);
            $em->flush();
            return $this->redirectToRoute('home');
        }
        return $this->render('main/index.html.twig', [
            "sortieForm" => $sortieForm->createView(), "campus" => $campus
        ]);
    }

    /**
     * @Route("/search", name="search")
     */
    public function search(Request $request, EntityManagerInterface $em)
    {

    }
}
