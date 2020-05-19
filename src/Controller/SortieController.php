<?php

namespace App\Controller;

use App\Entity\Sortie;
use App\Form\SortieType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class SortieController extends AbstractController
{
    /**
     * @Route("/sortie/createSortie", name="sortie")
     */
    public function sortie(EntityManagerInterface $em, Request $request)
    {
        $sortie = new Sortie();
        $sortieForm = $this ->createForm(SortieType::class, $sortie);

        $sortieForm->handleRequest($request);
        dump($sortie);
        if ($sortieForm->handleRequest($request) && $sortieForm->isValid()){
            $em->persist($sortie);
            $em->flush();
            $this->addFlash("success", "Votre évènement".$sortie->getNom()." a bien été sauvegardé !");
        }

        return $this->render('sortie/createSortie.html.twig', [
            'sortieForm' => $sortieForm -> createView()
        ]);
    }
}
