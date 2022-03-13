<?php

namespace App\Repository;

use App\Entity\UserBasket;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method UserBasket|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserBasket|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserBasket[]    findAll()
 * @method UserBasket[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserBasketRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserBasket::class);
    }

    // /**
    //  * @return UserBasket[] Returns an array of UserBasket objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('u.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?UserBasket
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
