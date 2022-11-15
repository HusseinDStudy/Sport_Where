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

    /**
     * Retourne les lieux paginés par $page a la limite $limit
     * @param int $page
     * @param int $limit de lieux par page
     * @return array
     */
    public function findWithPagination($page, $limit){
        $qb = $this->createQueryBuilder('place');
        $qb->setMaxResults($limit);
        $qb->setFirstResult(($page - 1) * $limit);
        return $qb->getQuery()->getResult();
    }

    /**
 * Retourne les lieux actif paginés par $page a la limite $limit ordonné de manière decroissante et filtré par departement $dept
 * @param int $page
 * @param int $limit de lieux par page
 * @param string $dept filtre de departement
 * @return array
 */
    public function findAllCustom($page, $limit, $dept){
        $qb = $this->createQueryBuilder('place');
        $qb->setMaxResults($limit);
        $qb->setFirstResult(($page - 1) * $limit);
        $qb->where('place.status = \'on\'');
        $qb->andWhere('place.dept = :dept')->setParameter('dept', $dept);
        $qb->orderBy('place.placeRate', 'DESC');
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

    /**
     * Retourne les lieux sportif selon son département
     * @param int $departement
     * @return array
     */
    public function getPlacesbyDept(string $dept){
        $qb = $this->createQueryBuilder('place')
            ->where('place.dept = :dept')
            ->setParameter('dept', $dept);
        return $qb->getQuery()->getResult();
    }
}
