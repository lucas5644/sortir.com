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

        if (is_null($this->security->getUser())) {
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

        //récupération des données avec la requête FindSortie
        $sorties = $sortieRepo->findSortie($findSortie);

        //l'utilisateur courant est-il inscrit
        $userConnected = $this->security->getUser()->getId();
        $inscriptionRepo = $this->getDoctrine()->getRepository(Inscription::class);
        $inscrit = $inscriptionRepo->findBy(array('participant' => $userConnected));

        $numeroSortie = array();

        foreach ($inscrit as $i) {
            array_push($numeroSortie, $i->getSortie()->getId());
        }

        dump($inscrit, $numeroSortie);

        //Afficher un message d'erreur si aucun résultat
        if ($sorties->count() == 0) {
            $this->addFlash('warning', 'Aucun résultat à votre recherche');
        }

        //renvoyer le formulaire à ma page et mon filtre
        return $this->render('main/index.html.twig', [
            'findMesSortiesForm' => $findMesSortiesForm->createView(),
            "sorties" => $sorties,
            'numeroSortie'=>$numeroSortie
        ]);
    }

}
