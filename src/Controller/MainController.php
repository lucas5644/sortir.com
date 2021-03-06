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
     * @param Request $request
     * @param SortieRepository $sortieRepository
     * @return Response
     */
    public function index(Request $request, SortieRepository $sortieRepository): Response
    {
        //check si l'utilisateur est bien connecté
        if (is_null($this->security->getUser())) {
            return $this->redirectToRoute('login');
        }

        //nouvelle instance de FindSortie
        $findSortie = new FindSortie();

        //création du formulaire avec l'instance
        $findMesSortiesForm = $this->createForm(FindSortieType::class, $findSortie);
        //récupération des données du formulaire
        $findMesSortiesForm->handleRequest($request);

        //check si les deux dates sont saisies
        if ($findSortie->getDateDebut() == '' && $findSortie->getDateFin() != '' ||
            $findSortie->getDateDebut() != '' && $findSortie->getDateFin() == '') {
            $this->addFlash('warning', 'Les deux dates doivent être saisies');
        }

        //recherche de toutes les sorties pour affichage dans tableau
        $sortieRepo = $this->getDoctrine()->getRepository(Sortie::class);

        //récupération des données avec la requête FindSortie
        $sorties = $sortieRepo->findSortie($findSortie);

        //l'utilisateur courant est-il inscrit
        $userConnected = $this->security->getUser()->getId();
        $inscriptionRepo = $this->getDoctrine()->getRepository(Inscription::class);
        $inscrit = $inscriptionRepo->findBy(array('participant' => $userConnected));
        //retourne true ou false
        $estInscrit = $findMesSortiesForm->getData()->getMesInscriptions();

        //injection de l'id des sorties auxquelles l'utilisateur est inscrit
        $numeroSortieInscrit = array();
        foreach ($inscrit as $i) {
            array_push($numeroSortieInscrit, $i->getSortie()->getId());
        }

        //dans un tableau, injection de l'id de toutes le sorties
        $numeroSorties = array();
        $allSorties = $sortieRepo->findAll();
        foreach ($allSorties as $s) {
            array_push($numeroSorties, $s->getId());
        }

        //Dans un tableau, je récupère le numéro des sorties auxquelles je ne suis pas inscrit
        $numeroSortiePasInscrit = array_diff($numeroSorties, $numeroSortieInscrit);

        //Afficher un message d'erreur si aucun résultat
        if ($sorties->count() == 0) {
            $this->addFlash('warning', 'Aucun résultat à votre recherche');
        }

        //renvoyer le formulaire à ma page et mon filtre
        return $this->render('main/index.html.twig', [
            'findMesSortiesForm' => $findMesSortiesForm->createView(),
            "sorties" => $sorties,
            'numeroSortieInscrit'=>$numeroSortieInscrit,
            'estInscrit' => $estInscrit,
            'numeroSortiePasInscrit' => $numeroSortiePasInscrit
        ]);
    }
}
