<?php

namespace App\Controller;

use App\Entity\Inscription;
use App\Entity\Sortie;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class InscriptionController extends AbstractController
{
    /*
     * @Route("/inscription", name="inscription")
     *
    public function index()
    {
        return $this->render('inscription/index.html.twig', [
            'controller_name' => 'InscriptionController',
        ]);
    }
    */


    /**
     * @param $id
     * @Route("/inscription/delete/{id}", name="inscription")
     */
    public function remove($id, EntityManagerInterface $em){
        $inscriptionRepo = $this->getDoctrine()->getRepository(Inscription::class);
        $inscription = $inscriptionRepo->findOneBy(['id' => $id]);
        $idSortie = $inscription->getSortie()->getId();

        $em->remove($inscription);
        $em->flush();

        $this->addFlash("success","Votre inscription a bien été supprimée !");
        return $this->redirectToRoute('sortie_detail',['id'=>$idSortie]);
    }
}
