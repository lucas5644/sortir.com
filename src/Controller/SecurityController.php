<?php

namespace App\Controller;


use App\Entity\Participant;
use App\Form\ForgottenPasswordType;
use App\Form\ResetPasswordType;
use App\Repository\ParticipantRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Swift_Mailer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    /**
     * @Route("/login", name="login")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
             return $this->redirectToRoute('home');
        }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    /**
     * @Route("/logout", name="logout")
     */
    public function logout()
    {
        throw new \LogicException("Oops... (×﹏×) il semblerait qu'il y ait un petit problème. Rien de grave cependant ! 
        Nous te conseillons de fermer la fenêtre directement ! (⌒_⌒;) ");
    }

    /**
     * @Route("/oubli-pass", name="app_forgotten_password")
     * @param Request $request
     * @param Swift_Mailer $mailer
     * @param TokenGeneratorInterface $tokenGenerator
     * @param EntityManagerInterface $em
     * @return Response
     */
    public function oubliPass(Request $request, Swift_Mailer $mailer, TokenGeneratorInterface $tokenGenerator, EntityManagerInterface $em): Response
    {
        $utilisateur = new Participant();
        // On initialise le formulaire
        $form = $this->createForm(ForgottenPasswordType::class);

        // On traite le formulaire
        $form->handleRequest($request);

        // Si le formulaire est valide
        if ($form->isSubmitted() && $form->isValid()) {
            // On récupère les données
            $donnees = $form->getData();

            // On cherche un utilisateur ayant cet e-mail

            $utilisateur= $em->getRepository(Participant::class)->findOneBy(['mail'=>$donnees['mail']]);

            // Si l'utilisateur n'existe pas
            if ($utilisateur === null) {
                // On envoie une alerte disant que l'adresse e-mail est inconnue
                $this->addFlash('danger', 'Cette adresse e-mail est inconnue');

                // On retourne sur la page de connexion
                return $this->redirectToRoute('login');
            }else{
                dump($donnees['mail']);
                dump($utilisateur);
                //return $this->redirectToRoute('app_forgotten_password');
            }

            // On génère un token
            $token = $tokenGenerator->generateToken();

            // On essaie d'écrire le token en base de données
            try{
                $utilisateur->setResetToken($token);
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($utilisateur);
                $entityManager->flush();
            } catch (\Exception $e) {
                $this->addFlash('warning', $e->getMessage());
                return $this->redirectToRoute('login');
            }

            // On génère l'URL de réinitialisation de mot de passe
            $url = $this->generateUrl('app_reset_password', array('token' => $token), UrlGeneratorInterface::ABSOLUTE_URL);

            // On génère l'e-mail
            $message = (new \Swift_Message('Mot de passe oublié'))
                ->setFrom('votre@adresse.fr')
                ->setTo($utilisateur->getMail())
                ->setBody(
                    "Bonjour,<br><br>Une demande de réinitialisation de mot de passe a été effectuée pour le site sortir.com. Veuillez cliquer sur le lien suivant : " . $url,
                    'text/html'
                )
            ;

            // On envoie l'e-mail
            $mailer->send($message);

            // On crée le message flash de confirmation
            $this->addFlash('message', 'E-mail de réinitialisation du mot de passe envoyé !');

            // On redirige vers la page de login
            return $this->redirectToRoute('login');

        }

        // On envoie le formulaire à la vue
        return $this->render('security/forgotten_password.html.twig',['emailForm' => $form->createView()]);
    }

    /**
     * @Route("/reset_pass/{token}", name="app_reset_password")
     * @param Request $request
     * @param string $token
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @param EntityManagerInterface $em
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function resetPassword(Request $request, string $token, UserPasswordEncoderInterface $passwordEncoder, EntityManagerInterface $em)
    {
        $utilisateur = new Participant();
        $form = $this->createForm(ResetPasswordType::class);

        //$resPassForm->handleRequest($request);
        // On cherche un utilisateur avec le token donné
        $utilisateur = $this->getDoctrine()->getRepository(Participant::class)->findOneBy(['reset_token' => $token]);

        // Si l'utilisateur n'existe pas
        if ($utilisateur === null) {
            // On affiche une erreur
            $this->addFlash('danger', 'Token Inconnu');
            return $this->redirectToRoute('login');
        }

        // Si le formulaire est envoyé en méthode post
        if ($form->isSubmitted() && $form->isValid()) {

            // On supprime le token
            $utilisateur->setResetToken();

            // On chiffre le mot de passe
            $utilisateur->setPassword($passwordEncoder->encodePassword($utilisateur, $request->request->get('password')));
            dump($utilisateur);
            // On stock
            $em = $this->getDoctrine()->getManager();
            $em->persist($utilisateur);
            $em->flush();

            // On crée le message flash
            $this->addFlash('message', 'Mot de passe mis à jour');

            // On redirige vers la page de connexion
            return $this->redirectToRoute('login');
        }else {
            // Si on n'a pas reçu les données, on affiche le formulaire
            return $this->render('security/reset_password.html.twig', [
                'token' => $token,
                'resPassForm' => $form->createView()
            ]);
        }
    }
}
