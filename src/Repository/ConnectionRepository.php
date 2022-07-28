<?php

namespace App\Repository;

use App\Entity\Connection;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Connection|null find($id, $lockMode = null, $lockVersion = null)
 * @method Connection|null findOneBy(array $criteria, array $orderBy = null)
 * @method Connection[]    findAll()
 * @method Connection[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ConnectionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Connection::class);
    }

    public function findLastConnection($user,$store=null): ?Connection
    {
        $q = $this->createQueryBuilder('c')
            ->innerJoin('c.user', 'u')
            ->where('u = :user')
            ->setParameter('user', $user)
            ->orderBy('c.id', 'DESC')
            ->setMaxResults(1);
        if ($store !== null){
            $q->innerJoin('n.store','st')
                ->andWhere('st = :store')
                ->setParameter('store', $store);
        }
        return $q->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function findByPeriodAndUser($start,$end,$user=null,$store=null): array
    {
         $q = $this->createQueryBuilder('c')
             ->where('DATE(c.addDate) >= :start')
             ->andWhere('DATE(c.addDate) <= :end')
             ->setParameter('start',  $start)
             ->setParameter('end',  $end);

         if ($user !== null){
             $q->innerJoin('c.user', 'u')
             ->andWhere('u = :user')
             ->setParameter('user', $user);
         }

        if ($store !== null){
            $q->innerJoin('n.store','st')
                ->andWhere('st = :store')
                ->setParameter('store', $store);
        }

        return $q->orderBy('c.addDate', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
