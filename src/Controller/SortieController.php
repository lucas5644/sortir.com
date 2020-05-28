<?php

namespace App\Controller;

use App\Entity\Etat;
use App\Entity\Inscription;
use App\Entity\Lieu;
use App\Entity\Participant;
use App\Entity\Sortie;
use App\Entity\Ville;
use App\Form\InscriptionType;
use App\Form\SortieType;
use App\Form\UpdateSortieType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class SortieController extends AbstractController
{

    private $entityManager;
    private $security;

    public function __construct(EntityManagerInterface $entityManager,Security $security)
    {
        $this->entityManager = $entityManager;
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



        $sortieForm = $this->createForm(SortieType::class, $sortie);

        $sortieForm->handleRequest($request);


        if ($sortieForm->isSubmitted() && $sortieForm->isValid()) {
            if ($sortieForm->getClickedButton() && 'creerEtPublier' === $sortieForm->getClickedButton()->getName()) {
                $etat = $this->getDoctrine()->getManager()->getRepository(Etat::class)->findOneBy(["libelle"=>"ouverte"]);
                $sortie->setEtat($etat);
            }else{
                $etat = $this->getDoctrine()->getManager()->getRepository(Etat::class)->findOneBy(["libelle"=>"créée"]);
                $sortie->setEtat($etat);
            }
            $em->persist($sortie);
            $em->flush();



            if ($sortieForm->getClickedButton() && 'creerEtPublier' === $sortieForm->getClickedButton()->getName()) {
                $this->addFlash("success", "Votre évènement" . $sortie->getNom() . " a bien été sauvegardé  et publier !");
                return $this->redirectToRoute('sortie_detail', ['id' => $sortie->getId()]);
            }else{
                $this->addFlash("success", "Votre évènement" . $sortie->getNom() . " a bien été sauvegardé !");
                return $this->redirectToRoute('sortie_modifier', ['id' => $sortie->getId()]);
            }

        }

        return $this->render('sortie/createSortie.html.twig', [
            'sortieForm' => $sortieForm->createView()
        ]);
    }

    /**
     * @Route("/sortie/modifier?{id}", name="sortie_modifier")
     * requirements={"id":"\d+"},
     */
    public function modifierSortie($id,  Request $request){
        $sortie = $this->entityManager->getRepository(Sortie::class)->findOneBy(['id' => $id]);
        $utilisateurConnecte = $this->security->getUser();
        dump($sortie);
        dump($utilisateurConnecte);
        if($sortie->getOrganisateur()->getId() === $utilisateurConnecte->getId()){
            $sortieModifForm = $this->createForm(UpdateSortieType::class, $sortie);
            $sortieModifForm->handleRequest($request);
            if($sortieModifForm->isSubmitted() && $sortieModifForm->isValid())
            {
                if ($sortieModifForm->getClickedButton() && 'creerEtPublier' === $sortieModifForm->getClickedButton()->getName()) {
                    $etat = $this->getDoctrine()->getManager()->getRepository(Etat::class)->findOneBy(["libelle"=>"ouverte"]);
                    $sortie->setEtat($etat);
                }else{
                    $etat = $this->getDoctrine()->getManager()->getRepository(Etat::class)->findOneBy(["libelle"=>"créée"]);
                    $sortie->setEtat($etat);
                }

                $this->entityManager->persist($sortie);
                $this->entityManager->flush();

                if ($sortieModifForm->getClickedButton() && 'creerEtPublier' === $sortieModifForm->getClickedButton()->getName()) {
                    $this->addFlash("success", "Votre évènement" . $sortie->getNom() . " a bien été sauvegardé  et publier !");
                    return $this->redirectToRoute('sortie_detail', ['id' => $sortie->getId()]);
                }else{
                    $this->addFlash("success", "Votre évènement" . $sortie->getNom() . " a bien été sauvegardé !");
                    return $this->redirectToRoute('sortie_modifier', ['id' => $sortie->getId()]);
                }
            }
        }
        return $this->render('sortie/modifierSortie.html.twig', [
            'sortieModifForm' => $sortieModifForm->createView(),
            'sortie' => $sortie
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

/*        dump(count($sortie->getInscriptions()));
        dump($sortie->getNbInscriptionMax());*/

        if($dateNow > $dateFinSortie){
            $this->addFlash('danger', 'Impossible d\'accéder à cet évènement, car il est archivé !');
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

    /**
     * @Route("/sortie/annuler/{id}", name="annuler_sortie")
     * @param $id
     */
    public function annulerSortie($id, Request $request){
        $sortie = $this->entityManager->getRepository(Sortie::class)->findOneBy(['id' => $id]);
        $etat = $this->entityManager->getRepository(Etat::class)->findOneBy(['libelle' => "annulée"]);
        if(!is_null($request->get("confirmation"))){
            $raison = $request->get("raison");
            $sortie->setEtat($etat);
            $sortie->setInfosSortie($raison);
            $this->entityManager->persist($sortie);
            $this->entityManager->flush();
            $this->addFlash("success", "Sortie annulée : " . $sortie->getNom());
            return $this->redirectToRoute('sortie_detail', ['id' => $sortie->getId()]);
        }
        return $this->render("sortie/annulerSortie.html.twig", [
            'sortie' => $sortie
        ]);
    }
}
