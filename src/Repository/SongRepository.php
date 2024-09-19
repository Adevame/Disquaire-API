<?php

namespace App\Repository;

use App\Entity\Song;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Song>
 */
class SongRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Song::class);
    }

    public function findAllWithPagination($page, $limit)
    {
        $qb = $this->createQueryBuilder('s')
            // permet de sélectionner les données rechcherchée ainsi que permettre la pagination
            ->select('s.title', 's.duration', 's.genre', 'singer.fullName AS singerName', 'singer.id AS singerId', 'disc.discName AS discName', 'disc.id AS discId')
            // jointure avec les entités Singer et Disc pour afficher les informations des artistes et des disques associés
            ->leftJoin('s.singer', 'singer')
            ->leftJoin('s.disc', 'disc')
            // définit les limites pour la pagination
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        $query = $qb->getQuery();
        //->Methode pour empêcher le lazy loading si la requête ci-dessus est insuffisante.
        // $query->setFetchMode(Song::class, 'singer', 'disc', \Doctrine\ORM\Mapping\ClassMetadata::FETCH_EAGER);  
        return $query->getResult();
    }

    //    /**
    //     * @return Song[] Returns an array of Song objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('s.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Song
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
