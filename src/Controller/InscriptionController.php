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

        $inscription = new Inscription();

        $sortie = $this->getDoctrine()->getRepository(Sortie::class)->find($idSortie);

        if($this->date > $sortie->getDateLimiteInscription()){
            $this->addFlash("success", "Petit malin, t'as cru t'étais plus fort que moi?");
            return $this->redirectToRoute('sortie_detail',['id'=>$sortie->getId()]);
        }
        $inscription->setDateInscription($this->date);
        $inscription->setParticipant($this->security->getUser());
        $inscription->setSortie($sortie);

        $em->persist($inscription);
        $em->flush();

        $this->addFlash("success", "Votre inscription a bien été sauvegardée");
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

        if($inscription->getParticipant() != $this->security->getUser()){
            $this->addFlash("success", "Attention, on ne supprime pas les inscriptions des autres!!!");
            return $this->redirectToRoute('sortie_detail',['id'=>$idSortie]);
        }

        if($inscription->getSortie()->getEtat()->getId() > 3){
            $this->addFlash("success", "Tu veux vraiment supprimer ton inscription alors que l'évènement est déjà passé?");
            return $this->redirectToRoute('sortie_detail',['id'=>$idSortie]);
        }

        $em->remove($inscription);
        $em->flush();

        $this->addFlash("success","Votre inscription a bien été supprimée !");
        return $this->redirectToRoute('sortie_detail',['id'=>$idSortie]);
    }
}
