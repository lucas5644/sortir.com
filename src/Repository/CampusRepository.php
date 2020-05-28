<?php

namespace App\Repository;

use App\Entity\Campus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Campus|null find($id, $lockMode = null, $lockVersion = null)
 * @method Campus|null findOneBy(array $criteria, array $orderBy = null)
 * @method Campus[]    findAll()
 * @method Campus[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CampusRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Campus::class);
    }

    public function findCampus(Campus $filtre)
    {
        $qb = $this->createQueryBuilder('v');

        //si le champ nom est rempli
        if ($filtre->getNom() != null || $filtre->getNom()) {
            $qb->andWhere('v.nom LIKE :nom')
                ->setParameter('nom', '%'.$filtre->getNom().'%');
        }

        //requête sur la table des villes
        $query = $qb->getQuery();

        return new Paginator($query);

    }

}
