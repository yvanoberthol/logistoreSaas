<?php

namespace App\Repository;

use App\Entity\Loss;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Loss|null find($id, $lockMode = null, $lockVersion = null)
 * @method Loss|null findOneBy(array $criteria, array $orderBy = null)
 * @method Loss[]    findAll()
 * @method Loss[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LossRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Loss::class);
    }

    public function countByproduct($product,$store=null){
        try {
            $qb = $this->createQueryBuilder('l')
                ->select('SUM(l.qty)')
                ->innerJoin('l.productStock','st')
                ->innerJoin('st.product','p')
                ->where('p = :product')
                ->setParameter('product', $product);
            if ($store !== null){
                $qb->innerJoin('l.store','st')
                    ->andWhere('st = :store')
                    ->setParameter('store', $store);
            }
            return $qb->getQuery()
                ->getSingleScalarResult();
        } catch (NoResultException $e) {
            return 0;
        } catch (NonUniqueResultException $e) {
            return 0;
        }
    }

    public function countByproductStock($productStock,$store=null){
        try {
            $qb = $this->createQueryBuilder('l')
                ->select('SUM(l.qty)')
                ->innerJoin('l.productStock','pst')
                ->where('pst = :productStock')
                ->setParameter('productStock', $productStock);
            if ($store !== null){
                $qb->innerJoin('l.store','st')
                    ->andWhere('st = :store')
                    ->setParameter('store', $store);
            }
            return $qb->getQuery()
                ->getSingleScalarResult();
        } catch (NoResultException $e) {
            return 0;
        } catch (NonUniqueResultException $e) {
            return 0;
        }
    }

    public function countByproducts($store=null){
        $qb = $this->createQueryBuilder('l')
            ->select('p.id','SUM(l.qty) as qty')
            ->innerJoin('l.productStock','pst')
            ->innerJoin('pst.product','p');
        if ($store !== null){
            $qb->innerJoin('l.store','st')
                ->andWhere('st = :store')
                ->setParameter('store', $store);
        }
        return $qb->groupBy('p.id')
            ->getQuery()
            ->getScalarResult();
}

    public function findLossByMonth($month,$year,$store=null): ?array
    {
        $qb = $this->createQueryBuilder('l')
            ->where('MONTH(l.addDate) = :month')
            ->andWhere('YEAR(l.addDate) = :year')
            ->setParameter('month', $month)
            ->setParameter('year', $year);
        if ($store !== null){
            $qb->innerJoin('l.store','st')
                ->andWhere('st = :store')
                ->setParameter('store', $store);
        }
        return $qb->orderBy('l.addDate','DESC')
            ->getQuery()
            ->getResult();
    }

    public function nbByproductAndPeriodDate($start,$end,$product=null,$store=null)
    {
        $qb = $this->createQueryBuilder('l')
            ->select( 'p.id','SUM(l.qty) as qty')
            ->innerJoin('l.productStock','pst')
            ->innerJoin('pst.product','p')
            ->where('DATE(l.addDate) >= DATE(:start)')
            ->andWhere('DATE(l.addDate) <= DATE(:end)')
            ->setParameter('start',  $start)
            ->setParameter('end',  $end);

        if ($product !== null){
            $qb->andWhere('p = :product')
                ->setParameter('product', $product);
        }
        if ($store !== null){
            $qb->innerJoin('l.store','st')
                ->andWhere('st = :store')
                ->setParameter('store', $store);
        }

        return $qb->groupBy('p.id')
            ->orderBy('p.id')->getQuery()->getResult();
    }

    public function nbByproductAndBeforePeriodDate($start,$product=null,$store=null)
    {
        $qb = $this->createQueryBuilder('l')
            ->select( 'p.id','SUM(l.qty) as qty')
            ->innerJoin('l.productStock','pst')
            ->innerJoin('pst.product','p')
            ->where('DATE(l.addDate) < DATE(:start)')
            ->setParameter('start',  $start);

        if ($product !== null){
            $qb->andWhere('p = :product')
                ->setParameter('product', $product);
        }

        if ($store !== null){
            $qb->innerJoin('l.store','st')
                ->andWhere('st = :store')
                ->setParameter('store', $store);
        }

        return $qb->groupBy('p.id')
            ->orderBy('p.id')->getQuery()->getResult();
    }

    public function countAll($store=null)
    {
        try {
            $qb = $this->createQueryBuilder('l')
                ->select('count(l)');
            if ($store !== null){
                $qb->innerJoin('l.store','st')
                    ->andWhere('st = :store')
                    ->setParameter('store', $store);
            }
            return $qb->getQuery()->getSingleScalarResult();
        } catch (NoResultException $e) {
            return 0;
        } catch (NonUniqueResultException $e) {
            return 0;
        }
    }

    public function findLossByPeriod($start, $end,$product=null,$store=null)
    {
        $qb = $this->createQueryBuilder('l')
            ->where('DATE(l.addDate) >= DATE(:start)')
            ->andWhere('DATE(l.addDate) <= DATE(:end)')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->orderBy('l.addDate','DESC');

        if ($product !== null){
            $qb->innerJoin('l.productStock','pst')
                ->innerJoin('pst.product','p')
                ->andWhere('p = :product')
                ->setParameter('product',$product);
        }

        if ($store !== null){
            $qb->innerJoin('l.store','st')
                ->andWhere('st = :store')
                ->setParameter('store', $store);
        }

        return $qb->getQuery()
            ->getResult();
    }
}
