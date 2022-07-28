<?php

namespace App\Repository;

use App\Entity\Attendance;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Attendance|null find($id, $lockMode = null, $lockVersion = null)
 * @method Attendance|null findOneBy(array $criteria, array $orderBy = null)
 * @method Attendance[]    findAll()
 * @method Attendance[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AttendanceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Attendance::class);
    }

    public function findByMonthYear($month,$year,$user=null,$store = null)
    {
        $qb = $this->createQueryBuilder('a')
            ->where('MONTH(a.date) = :month')
            ->andWhere('YEAR(a.date) = :year')
            ->setParameter('month', $month)
            ->setParameter('year', $year);

         if($user !== null){
             $qb->andWhere('a.user = :user')->setParameter('user', $user);
         }

        if ($store !== null){
            $qb->innerJoin('a.store','s')
                ->andWhere('s = :store')
                ->setParameter('store', $store);
        }

          return  $qb->orderBy('a.date', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function findByDateAndEmployee($date,$user=null,$store = null)
    {
        $qb = $this->createQueryBuilder('a')
            ->where('DATE(a.date) = DATE(:date)')
            ->setParameter('date', $date);

        if($user !== null){
            $qb->andWhere('a.user = :user')->setParameter('user', $user);
        }

        if ($store !== null){
            $qb->innerJoin('a.store','s')
                ->andWhere('s = :store')
                ->setParameter('store', $store);
        }

        return  $qb->getQuery()->getOneOrNullResult();
    }
}
