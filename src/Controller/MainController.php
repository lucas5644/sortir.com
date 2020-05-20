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
        $sortie = new Sortie();
        $sortieRepo = $this->getDoctrine()->getRepository(Sortie::class);
        $sorties = $sortieRepo->findAll();

        //récupération de la liste des campus
        $campusRepo = $this->getDoctrine()->getRepository(Campus::class);
        $campus = $campusRepo->findAll();
        $findSortieForm = $this->createForm(SearchSortieType::class, $sortie);
        $findSortieForm->handleRequest($request);
        if ($findSortieForm->isSubmitted() && $findSortieForm->isValid()) {
            if ($sortie->getNom() == null) {
                $sortie->setNom('');
            }
//            if ($sortie->getOrganisateur()->getCampus()->getNom() == null) {
//                $sortie->getOrganisateur()->getCampus()->setNom('');
//            }

            $sorties = $sortieRepo->findSortie($sortie->getNom(), $sortie->getOrganisateur()->getCampus()->getNom());
            return $this->render('main/search-sortie.html.twig', [
                "sorties" => $sorties
            ]);
        }
        return $this->render('main/index.html.twig', [
            "findSortieForm" => $findSortieForm->createView(),
            "sorties"=>$sorties, "campus" => $campus
        ]);
    }


}
