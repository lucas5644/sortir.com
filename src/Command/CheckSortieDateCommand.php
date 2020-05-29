<?php

namespace App\Command;

use App\Entity\Etat;
use App\Entity\Sortie;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CheckSortieDateCommand extends Command
{

    protected static $defaultName = 'app:check-date';
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Check toutes les sorties, leur état, leur date, et change leur état')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $dateNow = new \DateTime();
        $sorties = $this->getSorties();
        $etatRepo = $this->entityManager->getRepository(Etat::class);
        $io->note(sprintf('La date d\'aujourd\'hui est le %s', $dateNow->format('Y-m-d H:i:s')));
        foreach ($sorties as $sortie){

            //Ici on récupère l'état actuel de la sortie
            $etat = $etatRepo->findOneBy(['libelle' => $sortie->getEtat()->getLibelle()]);

            $output->writeln('=============');
            $io->note(sprintf('Le nom de la sortie est : %s', $sortie->getNom()));
            $io->note(sprintf('La date de fin d\'inscription est le : %s', $sortie->getDateLimiteInscription()->format('Y-m-d H:i:s')));
            $io->note(sprintf('L\'état est à : %s', $etat->getLibelle()));

            //On fait en sorte de créer une date qui se situe à 1 mois après la fin de l'évènement (en comptant la durée si elle existe)
            $dateFinSortie = new \DateTime($sortie->getDateHeureDebut()->format('Y-m-d H:i:s'));
            date_add($dateFinSortie,  date_interval_create_from_date_string("".$sortie->getDuree()." minutes"));
            $dateArchivage = new \DateTime($dateFinSortie->format('Y-m-d H:i:s'));
            date_add($dateArchivage,  date_interval_create_from_date_string("1 month"));


            if($etat->getLibelle() == 'ouverte' && $dateNow > $sortie->getDateLimiteInscription()){
                $io->note(sprintf('Il faut que la sortie passe en clôturée'));
                $this->changerEtat($sortie, 'clôturée', $this->entityManager);
                $io->note(sprintf('Nouvel état de la sortie : %s', $sortie->getEtat()->getLibelle()));
            }elseif ($etat->getLibelle() == 'clôturée' && $dateNow > $sortie->getDateHeureDebut() && $dateNow < $dateFinSortie){
                $io->note(sprintf('Il faut que la sortie passe en activité en cours'));
                $this->changerEtat($sortie, 'activité en cours', $this->entityManager);
                $io->note(sprintf('Nouvel état de la sortie : %s', $sortie->getEtat()->getLibelle()));
            }elseif ($etat->getLibelle() == 'activité en cours' && $dateNow > $dateFinSortie){
                $io->note(sprintf('Il faut que la sortie passe en passée'));
                $this->changerEtat($sortie, 'passée', $this->entityManager);
                $io->note(sprintf('Nouvel état de la sortie : %s', $sortie->getEtat()->getLibelle()));
            }elseif (($etat->getLibelle() == 'passée' || $etat->getLibelle() == 'annulée') && $dateNow > $dateArchivage){
                $io->note(sprintf('Il faut que la sortie passe en archivée'));
                $this->changerEtat($sortie, 'archivée', $this->entityManager);
                $io->note(sprintf('Nouvel état de la sortie : %s', $sortie->getEtat()->getLibelle()));
            }elseif (($etat->getLibelle() != 'passée' || $etat->getLibelle() != 'annulée') && $dateNow > $dateArchivage){
                $io->note(sprintf('Il faut que la sortie passe en archivée'));
                $this->changerEtat($sortie, 'archivée', $this->entityManager);
                $io->note(sprintf('Nouvel état de la sortie : %s', $sortie->getEtat()->getLibelle()));
            }



        }

        $io->success('Fin de la commande, à bientôt.');
        return 0;
    }

    private function getSorties(){
        $sorties = $this->entityManager->getRepository(Sortie::class)->findAll();//PAS DE FINDALL()!!!
        return $sorties;
    }

    private function changerEtat(Sortie $sortie, string $etatString, EntityManagerInterface $em){
        $etatRepo = $this->entityManager->getRepository(Etat::class);
        $etatNouveau = $etatRepo->findOneBy(['libelle' => $etatString]);
        $sortie->setEtat($etatNouveau);
        $em->persist($sortie);
        $em->flush();//A METTRE EN DEHORS DE LA METHODE
    }
}
