<?php

namespace App\Repository;

use App\Entity\Disc;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Disc>
 */
class DiscRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Disc::class);
    }

    public function findAllWithPagination($page, $limit)
    {
        $qb = $this->createQueryBuilder('d')
            // permet la pagination définit ses limites
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        $query = $qb->getQuery();
        //->Methode pour empêcher le lazy loading si la requête ci-dessus est insuffisante.
        // $query->setFetchMode(Disc::class, 'songs', \Doctrine\ORM\Mapping\ClassMetadata::FETCH_EAGER);
        return $query->getResult();
    }

    //    /**
    //     * @return Disc[] Returns an array of Disc objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('d')
    //            ->andWhere('d.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('d.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Disc
    //    {
    //        return $this->createQueryBuilder('d')
    //            ->andWhere('d.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
