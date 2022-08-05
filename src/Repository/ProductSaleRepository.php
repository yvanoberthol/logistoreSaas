<?php

namespace App\Repository;

use App\Entity\ProductSale;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ProductSale|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProductSale|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProductSale[]    findAll()
 * @method ProductSale[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductSaleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProductSale::class);
    }

    public function countByproduct($product,$store=null){
        try {
            $qb = $this->createQueryBuilder('psa')
                ->select('SUM(psa.qty)')
                ->innerJoin('psa.product','p')
                ->innerJoin('psa.sale','s')
                ->where('s.deleted = false')
                ->andWhere('p = :product')
                ->setParameter('product', $product);
            if ($store !== null){
                $qb->innerJoin('s.store','st')
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

    public function countByproducts($store=null): array {
        $qb = $this->createQueryBuilder('psa')
            ->select('p.id','SUM(psa.qty) as qty')
            ->innerJoin('psa.product','p')
            ->innerJoin('psa.sale','s')
            ->where('s.deleted = false');
        if ($store !== null){
            $qb->innerJoin('s.store','st')
                ->andWhere('st = :store')
                ->setParameter('store', $store);
        }
        return $qb->groupBy('p.id')
            ->getQuery()
            ->getScalarResult();
    }

    public function countBySales($store=null): array {
        $qb = $this->createQueryBuilder('psa')
            ->select('psa.id','psa.qty as qty',
                'SUM(pss.qty) as qtySaleStock')
            ->innerJoin('psa.productStockSales','pss');
        if ($store !== null){
            $qb->innerJoin('s.store','st')
                ->andWhere('st = :store')
                ->setParameter('store', $store);
        }
        return $qb->groupBy('psa.id')
            ->having('count(pss.id) = 0')
            ->getQuery()
            ->getResult();
    }

    public function nbByproductAndPeriodDate($start,$end,$product=null,
                                             $employee=null,$store=null)
    {
        $qb = $this->createQueryBuilder('psa')
            ->select( 'p.id','SUM(psa.qty) as qty', 'AVG(psa.unitPrice) as unitPrice',
                'SUM(psa.subtotal) as subtotal')
            ->innerJoin('psa.sale','s')
            ->innerJoin('psa.product','p')
            ->where('s.deleted = false')
            ->andWhere('DATE(s.addDate) >= DATE(:start)')
            ->andWhere('DATE(s.addDate) <= DATE(:end)')
            ->setParameter('start',  $start)
            ->setParameter('end',  $end);

        if ($product !== null){
            $qb->andWhere('p = :product')
                ->setParameter('product', $product);
        }

        if ($employee !== null){
            $qb->innerJoin('s.recorder','r')
                ->andWhere('r = :recorder')
                ->setParameter('recorder', $employee);
        }

        if ($store !== null){
            $qb->innerJoin('s.store','st')
                ->andWhere('st = :store')
                ->setParameter('store', $store);
        }

        return $qb->groupBy('p.id')
            ->orderBy('p.id')
            ->getQuery()->getResult();
    }

    public function nbAdjustedByproductAndPeriodDate($start,$end,$product=null,$store=null)
    {
        $qb = $this->createQueryBuilder('psa')
            ->select( 'p.id','SUM(psa.qty) as qty','SUM(psa.subtotal) as subtotal')
            ->innerJoin('psa.sale','s')
            ->innerJoin('psa.product','p')
            ->where('s.deleted = false')
            ->andWhere('s.adjusted = true')
            ->andWhere('DATE(s.addDate) >= DATE(:start)')
            ->andWhere('DATE(s.addDate) <= DATE(:end)')
            ->setParameter('start',  $start)
            ->setParameter('end',  $end);

        if ($product !== null){
            $qb->andWhere('p = :product')
                ->setParameter('product', $product);
        }

        if ($store !== null){
            $qb->innerJoin('s.store','st')
                ->andWhere('st = :store')
                ->setParameter('store', $store);
        }

        return $qb->groupBy('p.id')
            ->orderBy('p.id')
            ->getQuery()->getResult();
    }

    public function nbByproductAndBeforePeriodDate($start,$product=null,$store=null)
    {
        $qb = $this->createQueryBuilder('psa')
            ->select( 'p.id','SUM(psa.qty) as qty')
            ->innerJoin('psa.product','p')
            ->innerJoin('psa.sale','s')
            ->where('s.deleted = false')
            ->andWhere('DATE(s.addDate) < DATE(:start)')
            ->setParameter('start',  $start);

        if ($product !== null){
            $qb->andWhere('p = :product')
                ->setParameter('product', $product);
        }

        if ($store !== null){
            $qb->innerJoin('s.store','st')
                ->andWhere('st = :store')
                ->setParameter('store', $store);
        }

        return $qb->groupBy('p.id')
            ->orderBy('p.id')
            ->getQuery()->getResult();
    }

    public function findproductSaleByPeriod($start,$end,$product=null,$store=null): ?array
    {

        $qb = $this->createQueryBuilder('psa')
            ->innerJoin('psa.sale','s')
            ->where('TIMESTAMP(s.addDate) >= TIMESTAMP(:start)')
            ->andWhere('s.deleted = false')
            ->andWhere('TIMESTAMP(s.addDate) <= TIMESTAMP(:end)')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->orderBy('s.addDate','DESC');
        if ($product !== null){
            $qb->andWhere('psa.product = :product')
                ->setParameter('product',$product);
        }

        if ($store !== null){
            $qb->innerJoin('s.store','st')
                ->andWhere('st = :store')
                ->setParameter('store', $store);
        }
        return $qb->getQuery()
            ->getResult();
    }

    public function findProductSaleByGroupPeriod($start,$end,$product=null,$store=null): ?array
    {

        $qb = $this->createQueryBuilder('psa')
            ->select( 's.id','DATE(s.addDate) as addDate','psa.unitPrice ','SUM(psa.qty) as qty',
                'SUM(psa.profit) as profit','SUM(psa.subtotal) as subtotal')
            ->innerJoin('psa.sale','s')
            ->where('DATE(s.addDate) >= DATE(:start)')
            ->andWhere('DATE(s.addDate) <= DATE(:end)')
            ->andWhere('s.deleted = false')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->orderBy('s.addDate','DESC');
        if ($product !== null){
            $qb->andWhere('psa.product = :product')
                ->setParameter('product',$product);
        }

        if ($store !== null){
            $qb->innerJoin('s.store','st')
                ->andWhere('st = :store')
                ->setParameter('store', $store);
        }
        return $qb->groupBy('addDate')
            ->getQuery()
            ->getResult();
    }

    public function findProductSaleByGroup($start,$end,$product=null,$employee=null,
                                           $customer=null,$store=null): ?array
    {

        $qb = $this->createQueryBuilder('psa')
            ->select( 'pst.id as id','(SUM(psa.unitPrice)/count(psa)) as sellPrice',
                'SUM(psa.profit) as profit','SUM(pss.qty) as qty')
            ->innerJoin('psa.sale','s')
            ->innerJoin('psa.productStockSales','pss')
            ->innerJoin('pss.productStock','pst')
            ->innerJoin('pst.stock','st')
            ->leftJoin('st.supplier','su')
            ->where('DATE(s.addDate) >= DATE(:start)')
            ->andWhere('DATE(s.addDate) <= DATE(:end)')
            ->andWhere('s.deleted = false')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->orderBy('s.addDate','DESC');
        if ($product !== null){
            $qb->andWhere('psa.product = :product')
                ->setParameter('product',$product);
        }
        if ($employee !== null){
            $qb->andWhere('s.recorder = :employee')
                ->setParameter('employee',$employee);
        }
        if ($customer !== null){
            $qb->andWhere('s.customer = :customer')
                ->setParameter('customer',$customer);
        }
        if ($store !== null){
            $qb->innerJoin('s.store','st')
                ->andWhere('st = :store')
                ->setParameter('store', $store);
        }
        return $qb->groupBy('pst.id')
            ->orderBy('qty','DESC')
            ->getQuery()
            ->getResult();
    }

    public function getByproduct($product,$nbSales = 1,$store=null){
        $qb = $this->createQueryBuilder('psa')
            ->innerJoin('psa.product','p')
            ->innerJoin('psa.sale','s')
            ->where('s.deleted = false')
            ->andWhere('p = :product')
            ->setParameter('product',$product);

        if ($store !== null){
            $qb->innerJoin('s.store','st')
                ->andWhere('st = :store')
                ->setParameter('store', $store);
        }

        $qb->orderBy('psa.id','DESC');

        if ($nbSales > 0){
            $qb->setMaxResults($nbSales);
        }

        return $qb->getQuery()
            ->getResult();
    }

    public function getByproductStock($productStock,$nbSales = 1,$store=null){
        $qb = $this->createQueryBuilder('psa')
            ->innerJoin('psa.product','p')
            ->innerJoin('p.productStocks','pst')
            ->innerJoin('psa.sale','s')
            ->where('s.deleted = false')
            ->andWhere('pst = :productStock')
            ->setParameter('productStock',$productStock);

        if ($store !== null){
            $qb->innerJoin('s.store','st')
                ->andWhere('st = :store')
                ->setParameter('store', $store);
        }

        $qb->orderBy('psa.id','DESC');

        if ($nbSales > 0){
            $qb->setMaxResults($nbSales);
        }

        return $qb->getQuery()
            ->getResult();
    }
}
