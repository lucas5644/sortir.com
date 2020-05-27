<?php

namespace App\Controller;

use App\Entity\Ville;
use App\Form\AddVilleType;
use App\Form\FindVilleType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class VilleController extends AbstractController
{
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    /**
     * @Route("villes", name="gestion_villes")
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
        $listeVilles = $villeRepo->findAll();

        $addVille = new Ville();

        $addVilleForm = $this->createForm(AddVilleType::class, $addVille);
        $addVilleForm->handleRequest($request);

        if ($addVilleForm->isSubmitted() && $addVilleForm->isValid()) {

            $em->persist($addVille);
            $em->flush();

            $this->addFlash("success", "Votre ville " . $addVille->getNom() ." ". $addVille->getCodePostal() . " a bien été ajoutée !");
            return $this->redirectToRoute('gestion_villes');

        }
        return $this->render('ville/gestion-villes.html.twig', [
            'gestionVilles' => $findVilleForm->createView(),
            'listeVilles' => $listeVilles,
            'addVille' => $addVilleForm->createView()
        ]);
    }

}
