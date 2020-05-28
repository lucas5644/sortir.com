<?php

namespace App\Controller;

use App\Entity\Etat;
use App\Entity\Inscription;
use App\Entity\Sortie;
use App\Form\InscriptionType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class InscriptionController extends AbstractController
{

    private $security;
    private $date;

    public function __construct(Security $security)
    {
        $this->security = $security;
        $this->date = new \DateTime();
    }

    /**
     * @Route("/inscription/{idSortie}", name="inscription")
     * @param $idSortie
     * @param Request $request
     * @param EntityManagerInterface $em
     * @return RedirectResponse
     */
    public function index($idSortie, Request $request, EntityManagerInterface $em)
    {
        $utilisateurConnecte = $this->security->getUser();

        if (!$utilisateurConnecte->getActif()){
            $this->addFlash("danger", "Vous ne pouvez pas vous inscrire !");
            return $this->redirectToRoute('sortie_detail',['id'=>$idSortie]);
        }

        $inscription = new Inscription();

        $sortie = $this->getDoctrine()->getRepository(Sortie::class)->find($idSortie);

        if($this->date > $sortie->getDateLimiteInscription()){
            $this->addFlash("danger", "Petit malin, t'as cru que t'étais plus fort que moi ?");
            return $this->redirectToRoute('sortie_detail',['id'=>$sortie->getId()]);
        }
        if(count($sortie->getInscriptions()) === $sortie->getNbInscriptionMax()){
            $this->addFlash("danger", "Impossible de s'inscrire sur cette sortie, le nombre de place maximum est atteint !");
            return $this->redirectToRoute('sortie_detail',['id'=>$sortie->getId()]);
        }
        $inscription->setDateInscription($this->date);
        $inscription->setParticipant($this->security->getUser());
        $inscription->setSortie($sortie);

        $em->persist($inscription);
        $em->flush();

        if(count($sortie->getInscriptions()) === $sortie->getNbInscriptionMax() - 1){
            $etat = $this->getDoctrine()->getManager()->getRepository(Etat::class)->findOneBy(["libelle"=>"clôturée"]);
            $this->addFlash("danger","Vous vous êtes inscrit, vous êtes le dernier !");
            $sortie->setEtat($etat);
            $em->persist($sortie);
            $em->flush();
        }

        $this->addFlash("success", "Votre inscription a bien été sauvegardée !");
        return $this->redirectToRoute('sortie_detail',['id'=>$sortie->getId()]);
    }


    /**
     * @param $id
     * @param EntityManagerInterface $em
     * @return RedirectResponse
     * @Route("/inscription/delete/{id}", name="inscriptionDelete")
     */
    public function remove($id, EntityManagerInterface $em){

        $inscriptionRepo = $this->getDoctrine()->getRepository(Inscription::class);
        $inscription = $inscriptionRepo->findOneBy(['id' => $id]);
        $idSortie = $inscription->getSortie()->getId();
        $sortie = $this->getDoctrine()->getRepository(Sortie::class)->find($idSortie);

        $utilisateurConnecte = $this->security->getUser();

        if (!$utilisateurConnecte->getActif()){
            $this->addFlash("danger", "Vous ne pouvez pas supprimer les inscriptions !");
            return $this->redirectToRoute('sortie_detail',['id'=>$idSortie]);
        }

        if($inscription->getParticipant() != $this->security->getUser()){
            $this->addFlash("success", "Attention, on ne supprime pas les inscriptions des autres !");
            return $this->redirectToRoute('sortie_detail',['id'=>$idSortie]);
        }

        $nbMax = $inscription->getSortie()->getNbInscriptionMax();
        $etatString = $inscription->getSortie()->getEtat()->getLibelle();

        if($etatString === "activité en cours" || $etatString === "passée" || $etatString === "annulée" || $etatString === "archivée"){
            $this->addFlash("success", "Tu veux vraiment supprimer ton inscription alors que l'évènement est déjà passé ?");
            return $this->redirectToRoute('sortie_detail',['id'=>$idSortie]);
        }

        $em->remove($inscription);
        $em->flush();

        if($etatString === "clôturée"){
            $etat = $this->getDoctrine()->getManager()->getRepository(Etat::class)->findOneBy(["libelle"=>"ouverte"]);
            $this->addFlash("success","Vous vous êtes désisté, il y a de nouveau la possibilité de s'inscrire !");
            $sortie->setEtat($etat);
            $em->persist($sortie);
            $em->flush();
        }

        $this->addFlash("success","Votre inscription a bien été supprimée !");
        return $this->redirectToRoute('sortie_detail',['id'=>$idSortie]);
    }
}
