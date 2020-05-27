<?php

namespace App\Repository;

use App\Entity\FindSortie;
use App\Entity\Inscription;
use App\Entity\Sortie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Security;

/**
 * @method Sortie|null find($id, $lockMode = null, $lockVersion = null)
 * @method Sortie|null findOneBy(array $criteria, array $orderBy = null)
 * @method Sortie[]    findAll()
 * @method Sortie[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SortieRepository extends ServiceEntityRepository
{
    private $security;

    public function __construct(ManagerRegistry $registry, Security $security)
    {
        parent::__construct($registry, Sortie::class);
        $this->security = $security;
    }


    public function findSortie(FindSortie $filtre)
    {
        $qb = $this->createQueryBuilder('s');

        //Si le champ nom est rempli...
        if ($filtre->getNomSortie() != null || $filtre->getNomSortie()) {
            $qb->andWhere('s.nom LIKE :nom')
                ->setParameter('nom', '%' . $filtre->getNomSortie() . '%');
        }
        //Si le champ campus est choisi
        if ($filtre->getNomCampus() != '' || $filtre->getNomSortie()) {
            $qb->join('s.organisateur', 'o')
                ->join('o.campus', 'c')
                ->andWhere('c.nom LIKE :campus')
                ->setParameter('campus', '%' . $filtre->getNomCampus() . '%');
        }
//        // si les dates sont saisies
//        if (($filtre->getDateDebut() != null || $filtre->getDateDebut())
//            || ($filtre->getDateFin() != null || $filtre->getDateFin())) {
//            $qb->andWhere('s.dateHeureDebut > :dateDebut AND s.dateHeureDebut < :dateFin')
//                ->setParameter('dateFin', $filtre->getDateFin())
//                ->setParameter('dateDebut', $filtre->getDateDebut());
//        }

        //si je suis organisateur
        if ($filtre->getMesSorties() == true) {
            //je recherche l'id du user connecté
            $userId = $this->security->getUser()->getId();

            //recherche des sorties que j'organise
            $qb->andWhere('s.organisateur = :userId')
                ->setParameter('userId', $userId);
        }

        dump($filtre->getMesInscriptions());

        // si je suis inscrit
        if ($filtre->getMesInscriptions() === true) {
            //je recherche l'id du user connecté
            $userId = $this->security->getUser()->getId();

            //recherche des sorties où je suis inscrit
            $qb->join('s.inscriptions', 'i')
                ->andWhere('i.participant = :userId')
                ->setParameter('userId', $userId)
                ->groupBy('s.id');
        }

        //si je ne suis pas inscrit
//        if ($filtre->getMesInscriptions() === false) {
//            $qb->andWhere('s.id != :idSortie')
//                ->andWhere('s.id != :idSortie2')
//                ->setParameter('idSortie', $filtreInscription->)
//        }
        if ($filtre->getMesInscriptions() === false) {
            //je recherche l'id du user connecté
            $userId = $this->security->getUser()->getId();

            //recherche des sorties où je ne suis pas inscrit
            $qb->join('s.inscriptions', 'i')
                ->andWhere('i.participant != :userId')
                ->setParameter('userId', $userId)
                ->groupBy('i.sortie');
        }

        //si la sortie est passée
        if ($filtre->getSortiesPassees() == true) {
            //recherche des sorties passées
            $qb->andWhere('s.dateHeureDebut < CURRENT_DATE()');
        }

        dump($qb->getQuery());

        //requête sur la table des sorties
        $query = $qb->getQuery();

        return new Paginator($query);

    }
}