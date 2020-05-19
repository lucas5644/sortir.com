<?php

namespace App\Controller;

use App\Entity\Sortie;
use App\Form\SortieType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class SortieController extends AbstractController
{
    /**
     * @Route("/sortie/createSortie", name="sortie")
     */
    public function sortie(EntityManagerInterface $em)
    {
        $sortie = new Sortie();
        $sortieForm = $this ->createForm(SortieType::class, $sortie);

        return $this->render('sortie/createSortie.html.twig', [
            'sortieForm' => $sortieForm -> createView()
        ]);
    }
}
