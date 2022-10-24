<?php

namespace App\Repository;

use App\Entity\Place;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Place>
 *
 * @method Place|null find($id, $lockMode = null, $lockVersion = null)
 * @method Place|null findOneBy(array $criteria, array $orderBy = null)
 * @method Place[]    findAll()
 * @method Place[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PlaceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Place::class);
    }

    public function save(Place $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Place $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return Place[] Returns an array of Place objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('p.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Place
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }

/**
 * Retourne les lieux actif paginés par $page a la limite $limit
 * @param int $page
 * @param int $limit de lieux par page
 * @return array
 */

    public function findWithPagination($page, $limit){
        $qb = $this->createQueryBuilder('place');
        $qb->setMaxResults($limit);
        $qb->setFirstResult(($page - 1) * $limit);
        $qb->where('place.status = \'on\'');
        return $qb->getQuery()->getResult();
    }

/**
 * Retourne les lieux ordoner par rate
 * @return array
 */
    public function orderByRate(){
        $qb = $this->createQueryBuilder('place')
        ->orderBy('place.placeRate', 'DESC');
        return $qb->getQuery()->getResult();
    }

    // public function findPlacesByStatus($status){
    //     $qb = $this->createQueryBuilder('place')
    //     ->andwhere('place.status = :status')->setParameter('status', $status);
    //     return $qb->getQuery()->getResult();
    // }
}
