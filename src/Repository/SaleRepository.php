<?php

namespace App\Repository;

use App\Entity\Sale;
use App\Util\RoleConstant;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Sale|null find($id, $lockMode = null, $lockVersion = null)
 * @method Sale|null findOneBy(array $criteria, array $orderBy = null)
 * @method Sale[]    findAll()
 * @method Sale[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SaleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Sale::class);
    }

    /**
     * @param $value
     * @param null $store
     * @return mixed
     * @throws NonUniqueResultException
     */
    public function findByIdOrNumInvoice($value,$store=null)
    {
        $q = $this->createQueryBuilder('s');
        if ($store !== null){
            $q->innerJoin('s.store','st')
                ->andWhere('st = :store')
                ->setParameter('store', $store);
        }
        return $q
            ->where('s.id = :value')
            ->orWhere('s.numInvoice = :value')
            ->setParameter('value', $value)
            ->orderBy('s.addDate', 'ASC')
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findByUser($value,$store=null)
    {
        $q = $this->createQueryBuilder('s');
        if ($store !== null){
            $q->innerJoin('s.store','st')
                ->andWhere('st = :store')
                ->setParameter('store', $store);
        }
        return $q
            ->leftJoin('s.recorder','u')
            ->where('s.deleted = false')
            ->andWhere('u = :user')
            ->setParameter('user', $value)
            ->orderBy('s.addDate', 'ASC')
            ->getQuery()
            ->getResult()
            ;
    }

    public function findByCustomer($value,$store=null)
    {
        $q = $this->createQueryBuilder('s');
        if ($store !== null){
            $q->innerJoin('s.store','st')
                ->andWhere('st = :store')
                ->setParameter('store', $store);
        }
        return $q
            ->leftJoin('s.customer','c')
            ->where('s.deleted = false')
            ->andWhere('c = :customer')
            ->setParameter('customer', $value)
            ->orderBy('s.addDate', 'ASC')
            ->getQuery()
            ->getResult()
            ;
    }

    public function findSaleByPeriod($start,$end,$product=null,
                                            $deleted=false,$adjusted=false,$store=null): ?array
    {

        $qb = $this->createQueryBuilder('s')
            ->where('TIMESTAMP(s.addDate) >= TIMESTAMP(:start)')
            ->andWhere('s.deleted = :deleted')
            ->andWhere('TIMESTAMP(s.addDate) <= TIMESTAMP(:end)')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->setParameter('deleted', $deleted);

        if ($adjusted){
            $qb->andWhere('s.adjusted = :adjusted')
                ->setParameter('adjusted', $adjusted);
        }

        if ($product !== null){
            $qb->andWhere('s.product = :product')
                ->setParameter('product',$product);
        }

        if ($store !== null){
            $qb->innerJoin('s.store','st')
                ->andWhere('st = :store')
                ->setParameter('store', $store);
        }
        return $qb->orderBy('s.addDate','DESC')->getQuery()
            ->getResult();
    }

    public function findSaleByMethodPeriod($start,$end,$paymentMethod=null,
                                           $recorder=null,$store=null): ?array
    {

        $qb = $this->createQueryBuilder('s')
            ->where('TIMESTAMP(s.addDate) >= TIMESTAMP(:start)')
            ->andWhere('s.deleted = false')
            ->andWhere('TIMESTAMP(s.addDate) <= TIMESTAMP(:end)')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->orderBy('s.addDate','DESC');
        if ($paymentMethod !== null){
            $qb->andWhere('s.paymentMethod = :paymentMethod')
                ->setParameter('paymentMethod',$paymentMethod);
        }

        if ($recorder!== null){
            $qb->andWhere('s.recorder = :recorder')
                ->setParameter('recorder',$recorder);
        }

        if ($store !== null){
            $qb->innerJoin('s.store','st')
                ->andWhere('st = :store')
                ->setParameter('store', $store);
        }
        return $qb->getQuery()
            ->getResult();
    }

    public function findByPeriodUser($start,$end,$value,$adjusted=false,$store=null): array
    {
        $qb = $this->createQueryBuilder('s')
            ->innerJoin('s.recorder','u')
            ->where('s.deleted = false')
            ->andWhere('DATE(s.addDate) >= DATE(:start)')
            ->andWhere('DATE(s.addDate) <= DATE(:end)')
            ->andWhere('u = :user')
            ->setParameter('start',  $start)
            ->setParameter('end',  $end)
            ->setParameter('user', $value);

        if ($adjusted){
            $qb->andWhere('s.adjusted = :adjusted')
                ->setParameter('adjusted', $adjusted);
        }

        if ($store !== null){
            $qb->innerJoin('s.store','st')
                ->andWhere('st = :store')
                ->setParameter('store', $store);
        }

        return $qb
            ->orderBy('s.addDate', 'DESC')
            ->getQuery()
            ->getResult()
            ;
    }

    public function groupByDateUser($user,$store=null): array
    {
        $qb = $this->createQueryBuilder('s')
            ->select('DATE(s.addDate) as date','count(s) as nbSales',
                'sum(s.amount) as amount', 'sum(s.profit) as profit')
            ->where('s.deleted = false');
            if ($user !== null){
                $qb->innerJoin('s.recorder','u')
                    ->andWhere('u = :user')
                    ->setParameter('user', $user);
            }
        if ($store !== null){
            $qb->innerJoin('s.store','st')
                ->andWhere('st = :store')
                ->setParameter('store', $store);
        }
        return $qb
            ->groupBy('date')
            ->orderBy('date')
            ->getQuery()->getArrayResult();
    }

    public function groupByYearUser($user,$store=null): array
    {
        $qb = $this->createQueryBuilder('s')
            ->select('count(s) as nbSales', 'sum(s.amount) as amount',
                'sum(s.profit) as profit','YEAR(s.addDate) as year')
            ->innerJoin('s.recorder','u')
            ->where('s.deleted = false')
            ->andWhere('u = :user')
            ->setParameter('user', $user);
        if ($store !== null){
            $qb->innerJoin('s.store','st')
                ->andWhere('st = :store')
                ->setParameter('store', $store);
        }
        return $qb->groupBy('year')->orderBy('year')
            ->getQuery()->getArrayResult();
    }

    public function groupByPeriodDate($start,$end,$user=null,$store=null): array
    {
        $qb = $this->createQueryBuilder('s')
            ->select('DATE(s.addDate) as date', 'count(s) as nbSales',
                'sum(s.amount) as amount','sum(s.profit) as profit')
            ->where('s.deleted = false')
            ->andWhere('DATE(s.addDate) >= :start')
            ->andWhere('DATE(s.addDate) <= :end')
            ->setParameter('start',  $start)
            ->setParameter('end',  $end);

        if ($user !== null){
            $qb->innerJoin('s.recorder','u')
                ->innerJoin('u.roles','r')
                ->andWhere('u = :user')
                ->andWhere("r.rank < 2")
                ->setParameter('user', $user);
        }

        if ($store !== null){
            $qb->innerJoin('s.store','st')
                ->andWhere('st = :store')
                ->setParameter('store', $store);
        }

        return $qb->groupBy('date')->getQuery()->getResult();
    }

    public function getSaleByMonth($month,$year,$store=null): ?array
    {
        $qb = $this->createQueryBuilder('s')
            ->select('DATE(s.addDate) as date','count(s) as nbSales',
                'sum(s.amount) as amount')
            ->where('s.deleted = false')
            ->andWhere('MONTH(s.addDate) = :month')
            ->andWhere('YEAR(s.addDate) = :year')
            ->setParameter('month', $month)
            ->setParameter('year', $year);
        if ($store !== null){
            $qb->innerJoin('s.store','st')
                ->andWhere('st = :store')
                ->setParameter('store', $store);
        }
        return $qb->groupBy('date')
            ->getQuery()
            ->getArrayResult();
    }

    public function getSaleByYear($year,$user= null,$store=null): ?array
    {
         $qb = $this->createQueryBuilder('s')
            ->select('DATE(s.addDate) as date','count(s) as nbSales',
                'sum(s.amount) as amount')
            ->where('s.deleted = false')
            ->andWhere('YEAR(s.addDate) = :year')
            ->setParameter('year', $year);
            if ($user !== null){
                $qb->innerJoin('s.recorder','u')
                    ->andWhere('u = :user')
                    ->setParameter('user', $user);
            }
        if ($store !== null){
            $qb->innerJoin('s.store','st')
                ->andWhere('st = :store')
                ->setParameter('store', $store);
        }
           return $qb->groupBy('date')->orderBy('date')
            ->getQuery()->getArrayResult();
    }

    public function groupByDateProduct($product,$store=null): array
    {
        $qb = $this->createQueryBuilder('s')
            ->select('DATE(s.addDate) as date',
                'count(s) as nbSales', 'sum(vm.qty) as qtySold',
                'sum(vm.qty)/count(s) as rate',
                'sum(vm.profit) as profits')
            ->innerJoin('s.productSales','vm')
            ->innerJoin('vm.product','m')
            ->where('s.deleted = false')
            ->andWhere('m = :product')
            ->setParameter('product', $product);
        if ($store !== null){
            $qb->innerJoin('s.store','st')
                ->andWhere('st = :store')
                ->setParameter('store', $store);
        }
        return $qb->groupBy('date')->orderBy('date')
            ->getQuery()->getArrayResult();
    }

    public function groupByYearProfit($store=null): array
    {
        $qb = $this->createQueryBuilder('s')
            ->select('YEAR(s.addDate) as year','sum(s.profit) as profit')
            ->where('s.deleted = false');
        if ($store !== null){
            $qb->innerJoin('s.store','st')
                ->andWhere('st = :store')
                ->setParameter('store', $store);
        }
        return $qb->groupBy('year')->orderBy('year')
            ->getQuery()->getArrayResult();
    }

    public function groupByYearProduct($product,$store=null): array
    {
        $qb = $this->createQueryBuilder('s')
            ->select('count(s) as nbSales', 'sum(vm.qty) as qtySold',
                'sum(vm.qty)/count(s) as rate',
                'sum(vm.profit) as profits','YEAR(s.addDate) as year')
            ->innerJoin('s.productSales','vm')
            ->innerJoin('vm.product','m')
            ->where('s.deleted = false')
            ->andWhere('m = :product')
            ->setParameter('product', $product);
        if ($store !== null){
            $qb->innerJoin('s.store','st')
                ->andWhere('st = :store')
                ->setParameter('store', $store);
        }
        return $qb->groupBy('year')->orderBy('year')
            ->getQuery()->getArrayResult();
    }

    public function countAll($user = null,$store=null)
    {
        try {
            $qb = $this->createQueryBuilder('s')
                ->select('count(s)')
                ->where('s.deleted = false');
                if ($user !== null){
                    $qb->innerJoin('s.recorder','u')
                        ->andWhere('u = :user')
                        ->setParameter('user', $user);
                }
            if ($store !== null){
                $qb->innerJoin('s.store','st')
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

    public function countByPaymentPeriod($paymentMethod,$store=null){
        try {
            $qb = $this->createQueryBuilder('s')
                ->select('COUNT(s)')
                ->innerJoin('s.paymentMethod','p')
                ->where('s.deleted = false')
                ->andWhere('p = :paymentMethod')
                ->setParameter('paymentMethod', $paymentMethod);

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

    public function getDiscountOnPeriod($start,$end,$customer=null,$employee=null
        ,$store=null){
        try {
            $qb = $this->createQueryBuilder('s')
                ->select('SUM(s.discount) as discount')
                ->where('s.deleted = false')
                ->andWhere('DATE(s.addDate) >= :start')
                ->andWhere('DATE(s.addDate) <= :end')
                ->setParameter('start',  $start)
                ->setParameter('end',  $end);

                if ($customer !== null){
                    $qb->innerJoin('s.customer','c')
                        ->andWhere('c = :customer')
                        ->setParameter('customer', $customer);
                }

            if ($employee !== null){
                $qb->innerJoin('s.recorder','e')
                    ->andWhere('e = :employee')
                    ->setParameter('employee', $employee);
            }
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

    public function findByPeriodCustomer($start,$end,$customer
        ,$employee=null,$store = null)
    {
        $qb = $this->createQueryBuilder('s')
            ->where('s.deleted = false')
            ->andWhere('DATE(s.addDate) >= DATE(:start)')
            ->andWhere('DATE(s.addDate) <= DATE(:end)')
            ->setParameter('start',  $start)
            ->setParameter('end',  $end);

        if ($customer !== null){
            $qb->innerJoin('s.customer','c')
                ->andWhere('c = :customer')
                ->setParameter('customer', $customer);
        }

        if ($employee !== null){
            $qb->innerJoin('s.recorder','e')
                ->andWhere('e = :employee')
                ->setParameter('employee', $employee);
        }

        if ($store !== null){
            $qb->innerJoin('s.store','st')
                ->andWhere('st = :store')
                ->setParameter('store', $store);
        }

        return $qb->orderBy('s.addDate', 'DESC')
            ->getQuery()
            ->getResult()
            ;
    }

    public function groupByDateCustomer($customer,$store = null): array
    {
        $qb = $this->createQueryBuilder('s')
            ->select('DATE(s.addDate) as date','count(s) as nbSales',
                'sum(s.amount) as amount', 'sum(s.profit) as profit')
            ->where('s.deleted = false');
        if ($customer !== null){
            $qb->innerJoin('s.customer','c')
                ->andWhere('c = :customer')
                ->setParameter('customer', $customer);
        }
        if ($store !== null){
            $qb->innerJoin('s.store','st')
                ->andWhere('st = :store')
                ->setParameter('store', $store);
        }
        return $qb
            ->groupBy('date')
            ->orderBy('date')
            ->getQuery()->getArrayResult();
    }

    public function groupByYearCustomer($customer,$store = null): array
    {
        $qb = $this->createQueryBuilder('s')
            ->select('count(s) as nbSales', 'sum(s.amount) as amount',
                'sum(s.profit) as profit','YEAR(s.addDate) as year')
            ->innerJoin('s.customer','c')
            ->where('s.deleted = false')
            ->andWhere('c = :customer')
            ->setParameter('customer', $customer);

        if ($store !== null){
            $qb->innerJoin('s.store','st')
                ->andWhere('st = :store')
                ->setParameter('store', $store);
        }
        return $qb->groupBy('year')->orderBy('year')
            ->getQuery()->getArrayResult();
    }

    public function groupByPeriodDateCustomer($start,$end,$customer,$store = null): array
    {
        $qb = $this->createQueryBuilder('s')
            ->select('DATE(s.addDate) as date', 'count(s) as nbSales',
                'sum(s.amount) as amount')
            ->where('s.deleted = false')
            ->andWhere('DATE(s.addDate) >= :start')
            ->andWhere('DATE(s.addDate) <= :end')
            ->setParameter('start',  $start)
            ->setParameter('end',  $end);

        if ($customer !== null){
            $qb->innerJoin('s.customer','c')
                ->andWhere('c = :customer')
                ->setParameter('customer', $customer);
        }

        if ($store !== null){
            $qb->innerJoin('s.store','st')
                ->andWhere('st = :store')
                ->setParameter('store', $store);
        }

        return $qb->groupBy('date')->getQuery()->getResult();
    }

    public function getSaleByYearCustomer($year,$customer= null,$store = null): ?array
    {
        $qb = $this->createQueryBuilder('s')
            ->select('DATE(s.addDate) as date','count(s) as nbSales',
                'sum(s.amount) as amount')
            ->where('s.deleted = false')
            ->andWhere('YEAR(s.addDate) = :year')
            ->setParameter('year', $year);
        if ($customer !== null){
            $qb->innerJoin('s.customer','c')
                ->andWhere('c = :customer')
                ->setParameter('customer', $customer);
        }

        if ($store !== null){
            $qb->innerJoin('s.store','st')
                ->andWhere('st = :store')
                ->setParameter('store', $store);
        }
        return $qb->groupBy('date')->orderBy('date')
            ->getQuery()->getArrayResult();
    }

    public function countAllByCustomer($customer = null,$store = null)
    {
        try {
            $qb = $this->createQueryBuilder('s')
                ->select('count(s)')
                ->where('s.deleted = false');
            if ($customer !== null){
                $qb->innerJoin('s.customer','c')
                    ->andWhere('c = :customer')
                    ->setParameter('customer', $customer);
            }

            if ($store !== null){
                $qb->innerJoin('s.store','st')
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
}
