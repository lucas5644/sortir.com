<?php

namespace App\Controller;

use App\Entity\Campus;
use App\Entity\Etat;
use App\Entity\Participant;
use App\Entity\Sortie;
use App\Form\ImportType;
use App\Form\ParticipantType;
use Doctrine\ORM\EntityManagerInterface;
use PhpOffice\PhpSpreadsheet\Reader\Exception;
use PhpOffice\PhpSpreadsheet\Reader\Ods;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Security;

class AdminController extends AbstractController
{

    private $entityManager;
    private $security;

    public function __construct(EntityManagerInterface $entityManager, Security $security)
    {
        $this->entityManager = $entityManager;
        $this->security = $security;
    }

    /**
     * @Route("/admin", name="admin")
     */
    public function index(){
        $users = $this->entityManager->getRepository(Participant::class)->findAll();
        return $this->render("admin/index.html.twig", [
            "users" => $users
        ]);
    }

    /**
     * @Route("/admin/register", name="register")
     * @param Request $request
     * @param EntityManagerInterface $em
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @return RedirectResponse|Response
     */
    public function signInForm(Request $request, EntityManagerInterface $em, UserPasswordEncoderInterface $passwordEncoder)
    {
        $utilisateur = new Participant();
        $userForm = $this->createForm(ParticipantType::class, $utilisateur);

        $userForm->handleRequest($request);
        dump($utilisateur);
        if($userForm->isSubmitted() && $userForm->isValid())
        {
            $password = $passwordEncoder->encodePassword($utilisateur, $utilisateur->getPassword());
            $utilisateur->setPassword($password);
            $utilisateur->setActif(true);
            $em->persist($utilisateur);
            $em->flush();

            $this->addFlash("success", "Vous avez créé l'utilisateur : ".$utilisateur->getPseudo());
            return $this->redirectToRoute('home');
        }

        return $this->render("User/register.html.twig", [
            "userForm" => $userForm->createView(),
            "user" => $utilisateur
        ]);
    }

    /**
     * @Route("/admin/annuler/{id}", name="annuler")
     * @param $id
     */
    public function annulerSortie($id, Request $request){
        $sortie = $this->entityManager->getRepository(Sortie::class)->findOneBy(['id' => $id]);
        $etat = $this->entityManager->getRepository(Etat::class)->findOneBy(['libelle' => "annulée"]);
        if(!is_null($request->get("confirmation"))){
            $raison = $request->get("raison");
            $sortie->setEtat($etat);
            $sortie->setInfosSortie($raison);
            $this->entityManager->persist($sortie);
            $this->entityManager->flush();
            $this->addFlash("success", "La sortie " . $sortie->getNom() . " a bien été annulée !");
            return $this->redirectToRoute('sortie_detail', ['id' => $sortie->getId()]);
        }
        return $this->render("sortie/annulerSortie.html.twig", [
            'sortie' => $sortie
        ]);
    }

    /**
     * @Route("/admin/import", name="import")
     * @param Request $request
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @return RedirectResponse|Response
     * @throws Exception
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function importUsers(Request $request, UserPasswordEncoderInterface $passwordEncoder, EntityManagerInterface $em){

        $fichierFrom = $this->createForm(ImportType::class);
        $fichierFrom->handleRequest($request);
        if($fichierFrom->isSubmitted() && $fichierFrom->isValid()){
            $urlPhotoFile = $fichierFrom->get('fichier')->getData();
            if ($urlPhotoFile) {
                $originalFilename = pathinfo($urlPhotoFile->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$urlPhotoFile->guessExtension();
                try {
                    $urlPhotoFile->move(
                        $this->getParameter('import_csv_directory'),
                        $newFilename

                    );
                    $tableauUsers = $this->getData($newFilename, $passwordEncoder, $em);
                } catch (FileException $e) {
                    $this->addFlash("danger", "Impossible d'enregistrer l'image!!! ");
                    return $this->redirectToRoute('import');
                }
                return $this->render("User/viewUserImport.html.twig", [
                    'users' => $tableauUsers
                ]);
            }

        }

        return $this->render("User/import.html.twig", [
            'fichierFrom' => $fichierFrom->createView()
        ]);
    }

    /**
     * @Route("/admin/desactiver/{id}", name="desactiver")
     * @param $id
     * @param Request $request
     */
    public function desactiverUser($id, Request $request){
        $user = $this->entityManager->getRepository(Participant::class)->findOneBy(['id' => $id]);
        $user->setActif(0);
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        $this->addFlash("success", "Utilisateur désactivé : " . $user->getPseudo());
        return $this->redirectToRoute('admin');
    }

    /**
     * @Route("/admin/activer/{id}", name="activer")
     * @param $id
     * @param Request $request
     */
    public function activerUser($id, Request $request){
        $user = $this->entityManager->getRepository(Participant::class)->findOneBy(['id' => $id]);
        $user->setActif(1);
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        $this->addFlash("success", "Utilisateur activé : " . $user->getPseudo());
        return $this->redirectToRoute('admin');
    }

    /**
     * @Route("/admin/supprimer/{id}", name="supprimer")
     * @param $id
     * @param Request $request
     */
    public function supprimerUser($id, Request $request){
        $user = $this->entityManager->getRepository(Participant::class)->findOneBy(['id' => $id]);
        $pseudo = $user->getPseudo();
        $this->entityManager->remove($user);
        $this->entityManager->flush();
        $this->addFlash("success", "Utilisateur supprimé : " . $pseudo);
        return $this->redirectToRoute('admin');
    }


    /**
     * @param String $newFilename
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @param EntityManagerInterface $em
     * @throws Exception Méthode pour retrouver les données du fichier csv.
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    private function getData(String $newFilename, UserPasswordEncoderInterface $passwordEncoder, EntityManagerInterface $em){
        $reader = new Ods();
        $spreadsheet = $reader->load($this->getParameter('kernel.project_dir') . '/public/uploads/imports/'.$newFilename);
        $tableauUsers = array();
        for ($i = 2; $i < 10000; $i++){
            $valueDebut = $spreadsheet->getActiveSheet()->getCellByColumnAndRow(1, $i)->getValue();
            $utilisateur = new Participant();
            if (is_null($valueDebut)){
                $i = 10000;
            }else{
                $campus = $spreadsheet->getActiveSheet()->getCellByColumnAndRow(1, $i)->getValue();
                $pseudo = $spreadsheet->getActiveSheet()->getCellByColumnAndRow(2, $i)->getValue();
                $nom = $spreadsheet->getActiveSheet()->getCellByColumnAndRow(3, $i)->getValue();
                $prenom = $spreadsheet->getActiveSheet()->getCellByColumnAndRow(4, $i)->getValue();
                $telephone = $spreadsheet->getActiveSheet()->getCellByColumnAndRow(5, $i)->getValue();
                $mail = $spreadsheet->getActiveSheet()->getCellByColumnAndRow(6, $i)->getValue();
                $password = $spreadsheet->getActiveSheet()->getCellByColumnAndRow(7, $i)->getValue();
                $administrateur = $spreadsheet->getActiveSheet()->getCellByColumnAndRow(8, $i)->getValue();
                $actif = $spreadsheet->getActiveSheet()->getCellByColumnAndRow(9, $i)->getValue();
                $url_photo = $spreadsheet->getActiveSheet()->getCellByColumnAndRow(10, $i)->getValue();
                $campusById = $this->entityManager->getRepository(Campus::class)->findOneBy(['id' => $campus]);
                $utilisateur->setCampus($campusById);
                $utilisateur->setPseudo($pseudo);
                $utilisateur->setNom($nom);
                $utilisateur->setPrenom($prenom);
                $utilisateur->setTelephone($telephone);
                $utilisateur->setPassword($password);
                $password = $passwordEncoder->encodePassword($utilisateur, $utilisateur->getPassword());
                $utilisateur->setPassword($password);
                $utilisateur->setMail($mail);
                $utilisateur->setAdministrateur($administrateur);
                $utilisateur->setActif($actif);
                $utilisateur->setUrlPhoto($url_photo);
                $em->persist($utilisateur);
                $em->flush();
                $tableauUsers[] = $utilisateur;
            }
        }
        return $tableauUsers;
    }
}
