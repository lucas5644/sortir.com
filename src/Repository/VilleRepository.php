<?php

namespace App\Repository;

use App\Entity\Ville;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Ville|null find($id, $lockMode = null, $lockVersion = null)
 * @method Ville|null findOneBy(array $criteria, array $orderBy = null)
 * @method Ville[]    findAll()
 * @method Ville[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VilleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Ville::class);
    }

    public function findVille(Ville $filtre)
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
