<?php

namespace App\Controller;

use App\Entity\Ville;
use App\Form\AddVilleType;
use App\Form\FindVilleType;
use App\Form\UpdateVilleType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class VilleController extends AbstractController
{
    private $security;
    private $entityManager;


    public function __construct(EntityManagerInterface $em, Security $security)
    {
        $this->security = $security;
        $this->entityManager = $em;
    }

    /**
     * @Route("villes", name="gestion_villes")
     * @param EntityManagerInterface $em
     * @param Request $request
     * @return Response
     */
    public function gestionVilles(EntityManagerInterface $em, Request $request) : Response
    {
        if (is_null($this->security->getUser())) {
            return $this->redirectToRoute('login');
        }

        //nouvelle instance de Ville
        $findVille = new Ville();

        //génération du formulaire
        $findVilleForm = $this->createForm(FindVilleType::class, $findVille);
        $findVilleForm->handleRequest($request);

        //recherche des toutes les villes
        $villeRepo  = $this->getDoctrine()->getRepository(Ville::class);
        $listeVilles = $villeRepo->findVille($findVille);

        $addVille = new Ville();

        $addVilleForm = $this->createForm(AddVilleType::class, $addVille);
        $addVilleForm->handleRequest($request);

        if ($addVilleForm->isSubmitted() && $addVilleForm->isValid()) {
            $em->persist($addVille);
            $em->flush();

            $this->addFlash("success", "Votre ville " . $addVille->getNom() ." ". $addVille->getCodePostal() . " a bien été ajoutée !");
            return $this->redirectToRoute('gestion_villes');
        }

//        $updatedVille = new Ville();
//
//        //$updatedVille = $this->entityManager->getRepository(Ville::class)->findOneBy(['id' => $id]);
//        $updatedVilleForm = $this->createForm(UpdateVilleType::class, $updatedVille);
//        $updatedVilleForm->handleRequest($request);
//
//        if ($updatedVilleForm->isSubmitted() && $updatedVilleForm->isValid()) {
//
//
//            $em->persist($updatedVille);
//            $em->flush();
//
//            $this->addFlash("success", "Votre ville " . $updatedVille->getNom() ." ". $updatedVille->getCodePostal() . " a bien été modifiée !");
//            return $this->redirectToRoute('gestion_villes');
//
//        }

        //Afficher un message d'erreur si aucun résultat
        if ($listeVilles->count() == 0) {
            $this->addFlash('warning', 'Aucun résultat à votre recherche');
        }


        //je renvoie le formulaire sur la page de gestion des sorties
        return $this->render('ville/gestion-villes.html.twig', [
            'gestionVilles' => $findVilleForm->createView(),
            'listeVilles' => $listeVilles,
            'addVille' => $addVilleForm->createView(),
            //'updatedVille' => $updatedVilleForm->createView()

        ]);
    }

    /**
     * @Route("/villes/supprimer_Ville/{id}", name="supprimer_Ville")
     * @param $id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function supprimerVille($id, Request $request)
    {
        $ville = $this->entityManager->getRepository(Ville::class)->findOneBy(['id' => $id]);
        $nom = $ville->getNom();
        $this->entityManager->remove($ville);
        $this->entityManager->flush();
        $this->addFlash("success", $ville->getNom()." a bien été supprimée !");
        return $this->redirectToRoute('gestion_villes');
    }

}
