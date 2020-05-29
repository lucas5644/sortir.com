<?php

namespace App\Controller;

use App\Entity\Campus;
use App\Form\AddCampusType;
use App\Form\FindCampusType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class CampusController extends AbstractController
{
    private $security;
    private $entityManager;

    public function __construct(EntityManagerInterface $em, Security $security)
    {
        $this->security = $security;
        $this->entityManager = $em;
    }

    /**
     * @Route("campus", name="gestion_campus")
     * @param EntityManagerInterface $em
     * @param Request $request
     * @return Response
     */
    public function gestionCampus(EntityManagerInterface $em, Request $request) : Response
    {
        if (is_null($this->security->getUser())) {
            return $this->redirectToRoute('login');
        }

        //nouvelle instance de Campus
        $findCampus = new Campus();

        //Génération du formulaire
        $findCampusForm = $this->createForm(FindCampusType::class, $findCampus);
        $findCampusForm->handleRequest($request);

        //recherche des tous les campus
        $campusRepo = $this->getDoctrine()->getRepository(Campus::class);
        $listeCampus = $campusRepo->findCampus($findCampus);

        //nouvel objet Campus pour ajouter un campus
        $addCampus = new Campus();

        //création du formulaire pour ajouter un campus
        $addCampusForm = $this->createForm(AddCampusType::class, $addCampus);
        $addCampusForm->handleRequest($request);

        //check si l'ajout est ok
        if ($addCampusForm->isSubmitted() && $addCampusForm->isValid()) {
            $em->persist($addCampus);
            $em->flush();

            //message de confirmation
            $this->addFlash('success', 'Votre campus a bien été ajouté.');
            return $this->redirectToRoute('gestion_campus');
        }

        //Afficher un message d'erreur si aucun résultat
        if ($listeCampus->count() == 0) {
            $this->addFlash('warning', 'Aucun résultat à votre recherche');
        }

        //je renvoie le formulaire sur la page gestion des campus
        return $this->render('campus/gestion-campus.html.twig', [
            'gestionCampus' => $findCampusForm->createView(),
            'listeCampus' => $listeCampus,
            'addCampus' => $addCampusForm->createView(),
        ]);
    }

    /**
     * @Route("/campus/supprimer_campus/{id}", name="supprimer_campus")
     * @param $id
     * @param Request $request
     * @return RedirectResponse
     */
    public function supprimerCampus($id, Request $request) {
        $campus = $this->entityManager->getRepository(Campus::class)->findOneBy(['id' => $id]);
        $this->entityManager->remove($campus);
        $this->entityManager->flush();
        $this->addFlash("success", $campus->getNom(). " a bien été supprimé");
        return $this->redirectToRoute('gestion_campus');
    }
}
