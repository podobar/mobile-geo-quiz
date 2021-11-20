<?php

namespace App\Repository;

use App\Entity\UserAnswer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method UserAnswer|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserAnswer|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserAnswer[]    findAll()
 * @method UserAnswer[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserAnswerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserAnswer::class);
    }

    public function findRanking() {
        $query = $this->createQueryBuilder('ua');

        return $query
            ->select('u.username, COUNT(u.id) AS points')
            ->innerJoin('ua.user', 'u')
            ->andWhere('u.roles LIKE \'%ROLE_PLAYER%\'')
            ->andHaving('points > 0')
            ->orderBy('points', 'DESC')
            ->groupBy('u.username')
            ->getQuery()
            ->getResult()
        ;
    }

    public function findUserPoints(int $userId) {
        $query = $this->createQueryBuilder('ua');

        return $query
            ->select('COUNT(ua.user) AS points')
            ->innerJoin('ua.user', 'u')
            ->andWhere('u.id = :userId')
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    // /**
    //  * @return UserAnswer[] Returns an array of UserAnswer objects
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
    public function findOneBySomeField($value): ?UserAnswer
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
