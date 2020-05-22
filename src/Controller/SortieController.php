<?php

namespace App\Controller;

use App\Entity\Inscription;
use App\Entity\Lieu;
use App\Entity\Sortie;
use App\Entity\Ville;
use App\Form\InscriptionType;
use App\Form\SortieType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class SortieController extends AbstractController
{

    private $security;
    private $date;

    public function __construct(Security $security)
    {
        $this->security = $security;
        $this->date = new \DateTime();
    }

    /**
     * @Route("/sortie/createSortie", name="sortie")
     */
    public function create(EntityManagerInterface $em, Request $request)
    {
        $sortie = new Sortie();
        $lieuRepo = $this->getDoctrine()->getRepository(Lieu::class);



        /*foreach ($lieuE as $l) {
            $villeRepo = $this->getDoctrine()->getRepository(Ville::class);
            $villeE = $villeRepo->findOneBy(['id'=>$l->getVille()->getId()]);
            $l->setVille($villeE);
        }*/

        $sortieForm = $this->createForm(SortieType::class, $sortie);

        $sortieForm->handleRequest($request);

        $organisateur = $this->getUser();
        $sortie->setOrganisateur($organisateur);

        /*$nom = $request->request->get('lieuE');
        $lieu = $this->getDoctrine()->getManager()->getRepository(Lieu::class)->findOneBy(['nom'=>$nom]);
        $sortie->setLieu($lieu);*/

        $etat = $this->getDoctrine()->getManager()->getRepository('App:Etat')->find(1);
        $sortie->setEtat($etat);


        dump($sortie);
        if ($sortieForm->isSubmitted() && $sortieForm->isValid()){
            $em->persist($sortie);
            $em->flush();

            $this->addFlash("success", "Votre évènement".$sortie->getNom()." a bien été sauvegardé !");
            return $this->redirectToRoute('sortie_detail',['id'=>$sortie->getId()]);
        }

        return $this->render('sortie/createSortie.html.twig', [
            //'lieuE'=> $lieuE,
            'sortieForm' => $sortieForm -> createView()
        ]);
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


        $idUser = $this->security->getUser()->getId();
        $idInscription = null;
        $user = null;

        foreach ($sortie->getInscriptions() as $ins){
            if($ins->getParticipant()->getId() == $idUser){
                $idInscription = $ins->getId();
                dump($idInscription);
                $user = $ins->getParticipant();
                dump($user);
            }
        }

        $inscription = new Inscription();
        $inscriptionForm = $this->createForm(InscriptionType::class, $inscription);

        dump($inscription);
        dump($sortie);

        $inscriptionForm->handleRequest($request);

        if($inscriptionForm->isSubmitted() && $inscriptionForm->isValid()){

            $inscription->setDateInscription($this->date);
            $inscription->setParticipant($this->security->getUser());
            $inscription->setSortie($sortie);
            dump($inscription);
            $em->persist($inscription);
            $em->flush();

            $this->addFlash("success", "Votre inscription a bien été sauvegardée");
            return $this->redirectToRoute('sortie_detail',['id'=>$sortie->getId()]);
        }

        if(empty($sortie)){
            throw $this->createNotFoundException("Oh non... Cet évènement n'existe pas (╥﹏╥)");
        }

        return $this->render('sortie/afficherSortie.html.twig', [
            'sortie' => $sortie,
            'utilisateurIns' => $user,
            'idInscription' => $idInscription,
            'inscriptionForm' => $inscriptionForm -> createView()
        ]);
    }

    /**
     * @Route("/sortie/delete/{id}", name="sortie_delete", requirements={"id": "\d+"})
     */
    public function delete($id, EntityManagerInterface $em)
    {
        $sortieRepo = $this->getDoctrine()->getRepository(Sortie::class);
        $sortie = $sortieRepo->find{$id};

        $em->remove($sortie);
        $em->flush();

        $this->addFlash("success","Votre évènement a bien été supprimé !");
        return $this->redirectToRoute('/');
    }
}
