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
    public function create(EntityManagerInterface $em, Request $request)
    {
        $sortie = new Sortie();

        $sortieForm = $this ->createForm(SortieType::class, $sortie);

        $sortieForm->handleRequest($request);
        dump($sortie);
        if ($sortieForm->isSubmitted() && $sortieForm->isValid()){
            $em->persist($sortie);
            $em->flush();

            $this->addFlash("success", "Votre évènement".$sortie->getNom()." a bien été sauvegardé !");
            return $this->redirectToRoute('sortie_detail',['id'=>$sortie->getId()]);
        }

        return $this->render('sortie/createSortie.html.twig', [
            'sortieForm' => $sortieForm -> createView()
        ]);
    }

    /**
     * @Route("/sortie/{id}", name="sortie_detail")
     *     requirements={"id":"\d+"},
     *     methods={"GET"})
     */
    public function detail($id, Request $request)
    {
        $sortieRepo = $this->getDoctrine()->getRepository(Sortie::class);
        $sortie = $sortieRepo->find($id);

        if(empty($sortie)){
            throw $this->createNotFoundException("Oh non... Cet évènement n'existe pas (╥﹏╥)");
        }

        return $this->render('sortie/afficherSortie.html.twig', [
            'sortie' => $sortie
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
