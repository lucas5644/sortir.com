<?php


namespace App\Controller;


use App\Entity\Campus;
use App\Entity\Participant;
use App\Form\ImportType;
use App\Form\ParticipantType;
use App\Form\UpdateParticipantType;
use Doctrine\ORM\EntityManagerInterface;
use PhpOffice\PhpSpreadsheet\Reader\Csv;
use PhpOffice\PhpSpreadsheet\Reader\Exception;
use PhpOffice\PhpSpreadsheet\Reader\IReadFilter;
use PhpOffice\PhpSpreadsheet\Reader\Ods;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Security;

class ParticipantController extends AbstractController
{

    private $entityManager;
    private $security;

    public function __construct(EntityManagerInterface $entityManager, Security $security)
    {
        $this->entityManager = $entityManager;
        $this->security = $security;
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
            $this->addFlash("success", "Vous êtes inscrit!!! Bienvenue ".$utilisateur->getPseudo());
            return $this->redirectToRoute('home');
        }

        return $this->render("User/register.html.twig", [
           "userForm" => $userForm->createView()
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
                    $this->getData($newFilename, $passwordEncoder, $em);
                } catch (FileException $e) {
                    $this->addFlash("danger", "Impossible d'enregistrer l'image!!! ");
                    return $this->redirectToRoute('import');
                }
            }
        }

        return $this->render("User/import.html.twig", [
            'fichierFrom' => $fichierFrom->createView()
        ]);
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
            }
        }
    }

    /**
     * @Route("profil/{pseudo}", name="profile")
     */
    public function afficherProfil($pseudo, Request $request, EntityManagerInterface $em, UserPasswordEncoderInterface $passwordEncoder){
        $user = $this->entityManager->getRepository(Participant::class)->findOneBy(['pseudo' => $pseudo]);
        $userC = $this->security->getUser();
        $oldPassword = $userC->getPassword();
        $oldURL = $userC->getUrlPhoto();
        dump($userC->getUsername());
        dump($user->getUsername());
        if(!hash_equals($userC->getUsername(), $user->getUsername())){
            return $this->render("User/profil.html.twig", [
                "user" => $user
            ]);
        }else{
            $userForm = $this->createForm(UpdateParticipantType::class, $user);

            $userForm->handleRequest($request);
            dump($user);
            if($userForm->isSubmitted() && $userForm->isValid())
            {

                if(strlen(trim($user->getPassword())) ==! 0){
                    $password = $passwordEncoder->encodePassword($user, $user->getPassword());
                    $user->setPassword($password);
                }else{
                    $user->setPassword($oldPassword);
                }
                if(is_null($user->getUrlPhoto()) && !is_null($oldURL)){
                    $user->setUrlPhoto($oldURL);
                }else{
                    $urlPhotoFile = $userForm->get('urlPhoto')->getData();
                    if ($urlPhotoFile) {
                        $originalFilename = pathinfo($urlPhotoFile->getClientOriginalName(), PATHINFO_FILENAME);
                        // this is needed to safely include the file name as part of the URL
                        $safeFilename = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $originalFilename);
                        $newFilename = $safeFilename.'-'.uniqid().'.'.$urlPhotoFile->guessExtension();

                        try {
                            $urlPhotoFile->move(
                                $this->getParameter('photo_profil_directory'),
                                $newFilename
                            );
                        } catch (FileException $e) {
                            $this->addFlash("success", "Impossible d'enregistrer l'image!!! ".$user->getPseudo());
                            return $this->redirectToRoute('profile', $user->getPseudo());
                        }
                        $user->setUrlPhoto($newFilename);
                    }
                }
                $em->persist($user);
                $em->flush();
                $this->addFlash("success", "Modifications prise en compte!!! ".$user->getPseudo());
                return $this->redirectToRoute('home');
            }
            return $this->render("User/monProfil.html.twig", [
                "user" => $user,
                "userForm" => $userForm->createView()
            ]);
        }


    }
}

