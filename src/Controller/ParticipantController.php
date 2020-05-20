<?php


namespace App\Controller;


use App\Entity\Participant;
use App\Form\ParticipantType;
use App\Form\UpdateParticipantType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
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
     * @Route("/register", name="register")
     * @param Request $request
     * @param EntityManagerInterface $em
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
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
            $utilisateur->setAdministrateur(false);
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
                if(is_null($user->getUrlPhoto())){
                    $user->setUrlPhoto($oldURL);
                }else{
                    /** @var UploadedFile $brochureFile */
                    $urlPhotoFile = $userForm->get('urlPhoto')->getData();
                    if ($urlPhotoFile) {
                        $originalFilename = pathinfo($urlPhotoFile->getClientOriginalName(), PATHINFO_FILENAME);
                        // this is needed to safely include the file name as part of the URL
                        $safeFilename = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $originalFilename);
                        $newFilename = $safeFilename.'-'.uniqid().'.'.$urlPhotoFile->guessExtension();

                        // Move the file to the directory where brochures are stored
                        try {
                            $urlPhotoFile->move(
                                $this->getParameter('photo_profil_directory'),
                                $newFilename
                            );
                        } catch (FileException $e) {
                            // ... handle exception if something happens during file upload
                        }

                        // updates the 'brochureFilename' property to store the PDF file name
                        // instead of its contents
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