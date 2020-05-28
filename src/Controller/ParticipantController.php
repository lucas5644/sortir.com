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
use Symfony\Component\HttpFoundation\File\Exception\FileException;
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
     * @Route("profil/{pseudo}", name="profile")
     */
    public function afficherProfil($pseudo, Request $request, EntityManagerInterface $em, UserPasswordEncoderInterface $passwordEncoder){
        $user = $this->entityManager->getRepository(Participant::class)->findOneBy(['pseudo' => $pseudo]);
        $userC = $this->security->getUser();
        $oldPassword = $userC->getPassword();
        $oldURL = $userC->getUrlPhoto();
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
                            $this->addFlash("success", "Impossible d'enregistrer l'image ".$user->getPseudo());
                            return $this->redirectToRoute('profile', ['pseudo', $user->getPseudo()]);
                        }
                        $user->setUrlPhoto($newFilename);
                    }
                }
                $em->persist($user);
                $em->flush();
                $pseudo = $request->get('pseudo');
                dump($pseudo);
                $this->addFlash("success",  $userC->getPseudo().", vos modifications ont été prises en compte.");
                return $this->redirectToRoute('profile', ['pseudo', $userC->getPseudo()]);
            }
            return $this->render("User/monProfil.html.twig", [
                "user" => $user,
                "userForm" => $userForm->createView()
            ]);
        }
    }
}

