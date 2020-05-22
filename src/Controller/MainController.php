<?php

namespace App\Controller;

use App\Entity\FindSortie;
use App\Entity\Inscription;
use App\Entity\Sortie;
use App\Form\FindSortieType;
use App\Repository\SortieRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class MainController extends AbstractController
{

    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    /**
     * @Route("/", name="home")
     */
    public function index(Request $request, SortieRepository $sortieRepository): Response
    {

        if(is_null($this->security->getUser())){
            return $this->redirectToRoute('login');
        }

        //nouvelle instance de FindSortie
        $findSortie = new FindSortie();

        //création du formulaire
        $findMesSortiesForm = $this->createForm(FindSortieType::class, $findSortie);
        //récupération des données du formulaire
        $findMesSortiesForm->handleRequest($request);

        if ($findSortie->getDateDebut() == '' && $findSortie->getDateFin() != '' ||
            $findSortie->getDateDebut() != '' && $findSortie->getDateFin() == '') {
            $this->addFlash('warning', 'Les deux dates doivent être saisies');
        }

        //recherche de toutes les sorties pour affichage dan tableau
        $sortieRepo = $this->getDoctrine()->getRepository(Sortie::class);

        //requête avec l'instance FindSortie qui a récupéré les données du formulaire
        $sorties = $sortieRepo->findSortie($findSortie);

//        if (cou) {
//            $this->addFlash('warning', 'Aucun résultat ne correspond à votre recherche');
//        }


        dump($findSortie);

        //renvoyer le formulaire à ma page et mon filtre
        return $this->render('main/index.html.twig', [
            'findMesSortiesForm' => $findMesSortiesForm->createView(),
            "sorties" => $sorties,
        ]);
    }


//    public function index(Request $request, EntityManagerInterface $em)
//    {
//        $sortie = new Sortie();
//        $sortieRepo = $this->getDoctrine()->getRepository(Sortie::class);
//        $sorties = $sortieRepo->findAll();
//
//        //récupération de la liste des campus
//        $campusRepo = $this->getDoctrine()->getRepository(Campus::class);
//        $campus = $campusRepo->findAll();
//        $findSortieForm = $this->createForm(SearchSortieType::class, $sortie);
//        $findSortieForm->handleRequest($request);
//        if ($findSortieForm->isSubmitted() && $findSortieForm->isValid()) {
//            if ($sortie->getNom() == null) {
//                $sortie->setNom('');
//            }
//
//            $nomCampus = $request->get('campus');
//            $dateDebut = $request->get('dateDebut');
//            $dateFin = $request->get('dateFin');
//            $mesSorties = $request->get('mesSorties');
//            $mesInscriptions = $request->get('mesInscriptions');
//            $pasEncoreInscrit = $request->get('pasEncoreInscrit');
//            $sortiesPassees = $request->get('sortiesPassees');
//
//            dump($sortie->getNom(), $nomCampus, $dateDebut, $dateFin, $mesSorties, $mesInscriptions, $pasEncoreInscrit, $sortiesPassees);
//
//            $sorties = $sortieRepo->findSortie($sortie->getNom(), $nomCampus);
//            return $this->render('main/search-sortie.html.twig', [
//                "sorties" => $sorties
//            ]);
//        }
//        return $this->render('main/index.html.twig', [
//            "findSortieForm" => $findSortieForm->createView(),
//            "sorties"=>$sorties, "campus" => $campus
//        ]);
//    }


}
