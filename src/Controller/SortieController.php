<?php

namespace App\Controller;

use App\Entity\Etat;
use App\Entity\Inscription;
use App\Entity\Lieu;
use App\Entity\Sortie;
use App\Entity\Ville;
use App\Form\InscriptionType;
use App\Form\SortieType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class SortieController extends AbstractController
{

    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    /**
     * @Route("/sortie/createSortie", name="sortie")
     */
    public function create(EntityManagerInterface $em, Request $request)
    {

        $sortie = new Sortie();

        $organisateur = $this->getUser();
        $sortie->setOrganisateur($organisateur);

        $etat = $this->getDoctrine()->getManager()->getRepository(Etat::class)->findOneBy(["libelle"=>"créée"]);
        $sortie->setEtat($etat);

        $sortieForm = $this->createForm(SortieType::class, $sortie);

        $sortieForm->handleRequest($request);


        if ($sortieForm->isSubmitted() && $sortieForm->isValid()) {
            $em->persist($sortie);
            $em->flush();


            $this->addFlash("success", "Votre évènement" . $sortie->getNom() . " a bien été sauvegardé !");
            return $this->redirectToRoute('sortie_detail', ['id' => $sortie->getId()]);
        }

        return $this->render('sortie/createSortie.html.twig', [
            'sortieForm' => $sortieForm->createView()
        ]);
    }

    /**
     * @Route("/sortie/publierSortie", name="sortie_publier")
     */
    public function publierSortie(EntityManagerInterface $em, Request $request)
    {

        $sortie = new Sortie();

        $organisateur = $this->getUser();
        $sortie->setOrganisateur($organisateur);

        $etat = $this->getDoctrine()->getManager()->getRepository(Etat::class)->findOneBy(["libelle"=>"publiée"]);
        $sortie->setEtat($etat);

        $sortieForm = $this->createForm(SortieType::class, $sortie);

        $sortieForm->handleRequest($request);


        if ($sortieForm->isSubmitted() && $sortieForm->isValid()) {
            $em->persist($sortie);
            $em->flush();


            $this->addFlash("success", "Votre évènement" . $sortie->getNom() . " a bien été sauvegardé !");
            return $this->redirectToRoute('sortie_detail', ['id' => $sortie->getId()]);
        }

        return $this->render('sortie/createSortie.html.twig', [
            'sortieForm' => $sortieForm->createView()
        ]);
    }


    /**
     * @Route("/sortie/lieux", name="sortie_lieux")
     * @param Request $request
     * @return JsonResponse
     */
    public function lieuSelonVille(Request $request)
    {
        $em =$this->getDoctrine()->getManager();
        $lieuxRepo = $em->getRepository("App:Lieu");

        $lieux = $lieuxRepo->createQueryBuilder("q")
            ->where("q.ville = :villeid")
            ->setParameter("villeid",$request->query->get("villeid"))
            ->getQuery()
            ->getResult();

        $responseArray = array();

        foreach ($lieux as $lieu){
            $responseArray[] = array(
                "id" => $lieu->getId(),
                "nom" => $lieu->getNom()
            );
        }
        return new JsonResponse($responseArray);
    }

    /**
     * @Route("/sortie/lieu", name="sortie_coor")
     * @param Request $request
     * @return JsonResponse
     */
    public function CoorSelonLieu(Request $request)
    {
        $em =$this->getDoctrine()->getManager();
        $lieuxRepo = $em->getRepository(Lieu::class);

        $lieu = $lieuxRepo->createQueryBuilder("q")
            ->where("q.id = :lieuid")
            ->setParameter("lieuid",$request->query->get("lieuid"))
            ->getQuery()
            ->getOneOrNullResult();

/*        $responseArray = array();*/


            $responseArray = array(
                "id" => $lieu->getId(),
                "nom" => $lieu->getNom(),
                "rue" => $lieu->getRue(),
                "latitude" => $lieu->getLatitude(),
                "longitude" => $lieu->getLongitude()
            );

        return new JsonResponse($responseArray);
    }


    /**
     * @Route("/sortie/{id}", name="sortie_detail")
     *     requirements={"id":"\d+"},
     *     methods={"GET"})
     */
    public function detail($id, Request $request, EntityManagerInterface $em)
    {
        $sortieRepo = $this->getDoctrine()->getRepository(Sortie::class);
        $sortie = $sortieRepo->find($id);
        $dateFinSortie = $sortie->getDateHeureDebut();

        date_add($dateFinSortie, date_interval_create_from_date_string("".$sortie->getDuree()." minutes"));
        date_add($dateFinSortie, date_interval_create_from_date_string("1 month"));
        $dateNow = new \DateTime();

        if($dateNow > $dateFinSortie){
            $this->addFlash('danger', 'Impossible d\'accéder à cet évènement, car il est vieux de plus de 1 mois!');
            return $this->redirectToRoute('home');
        }

        $idUser = $this->security->getUser()->getId();
        $idInscription = null;
        $user = null;

        foreach ($sortie->getInscriptions() as $ins) {
            if ($ins->getParticipant()->getId() == $idUser) {
                $idInscription = $ins->getId();
                $user = $ins->getParticipant();
            }
        }

        if(empty($sortie)){
            throw $this->createNotFoundException("Oh non... Cet évènement n'existe pas (╥﹏╥)");
        }

        return $this->render('sortie/afficherSortie.html.twig', [
            'sortie' => $sortie,
            'utilisateurIns' => $user,
            'idInscription' => $idInscription,
        ]);
    }


    /**
     * @Route("/sortie/delete/{id}", name="sortie_delete", requirements={"id": "\d+"})
     */
    public function delete($id, EntityManagerInterface $em)
    {
        $sortieRepo = $this->getDoctrine()->getRepository(Sortie::class);
        $sortie = $sortieRepo->find($id);

        $em->remove($sortie);
        $em->flush();

        $this->addFlash("success","Votre évènement a bien été supprimé !");
        return $this->redirectToRoute('/');
    }

    /**
     * @Route("/sortie/publier/{id}", name="publier", requirements={"id": "\d+"})
     */
    public function publier($id , EntityManagerInterface $em){
        $sortieRepo = $this->getDoctrine()->getRepository(Sortie::class);
        $sortie = $sortieRepo->find($id);
        $etatRepo = $this->getDoctrine()->getRepository(Etat::class);
        $etat = $etatRepo->findOneBy(['libelle' => 'ouverte']);

        $sortie->setEtat($etat);

        $em->persist($sortie);
        $em->flush();
        $this->addFlash("success","Votre évènement a bien été publié !");
        return $this->redirectToRoute('home');
    }
}
