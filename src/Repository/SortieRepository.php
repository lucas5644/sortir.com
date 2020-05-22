<?php

namespace App\Repository;

use App\Entity\FindSortie;
use App\Entity\Sortie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Sortie|null find($id, $lockMode = null, $lockVersion = null)
 * @method Sortie|null findOneBy(array $criteria, array $orderBy = null)
 * @method Sortie[]    findAll()
 * @method Sortie[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SortieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Sortie::class);
    }


    public function findSortie(FindSortie $filtre)
    {
        $qb = $this->createQueryBuilder('s');

        //Chercher si le champ nom est rempli
        if ($filtre->getNomSortie() != null || $filtre->getNomSortie()) {
            $qb->andWhere('s.nom LIKE :nom')
                ->setParameter('nom', '%' . $filtre->getNomSortie() . '%');
        }

        if ($filtre->getNomCampus() != null || $filtre->getNomSortie()) {
            $qb->join('s.organisateur', 'o')
                ->join('o.campus', 'c')
                ->andWhere('c.id = :campus')
                ->setParameter('campus', $filtre->getNomCampus());
        }

        //requête
        $query = $qb->getQuery();

        dump($query);

        return new Paginator($query);


//        return $this->createQueryBuilder('s')
//            ->andWhere('s.nom LIKE :nom')
//            ->setParameter('nom', '%'.$sortie.'%')
//            ->join('s.organisateur', 'o')
//            ->join('o.campus', 'c')
//            ->andWhere('c.nom LIKE :campus')
//            ->setParameter('campus','%'.$campus.'%')
//            ->getQuery()
//            ->getResult();
    }


//    public function findSortie($sortie, $campus)
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.nom LIKE :nom')
//            ->setParameter('nom', '%'.$sortie.'%')
//            ->join('s.organisateur', 'o')
//            ->join('o.campus', 'c')
//            ->andWhere('c.nom LIKE :campus')
//            ->setParameter('campus','%'.$campus.'%')
//            ->getQuery()
//            ->getResult();
//    }


    // /**
    //  * @return Sortie[] Returns an array of Sortie objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Sortie
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
