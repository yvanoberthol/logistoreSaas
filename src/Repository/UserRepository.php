<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements UserLoaderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function findByStore($store=null){

        $q = $this->createQueryBuilder('u');

        if ($store !== null){
            $q->innerJoin('u.store','s')
                ->andWhere('s = :store')
                ->setParameter('store', $store);
        }
        return $q->getQuery()->getResult();

    }

    public function findByNameOrEmail($value,$store=null){
        try {

            $q = $this->createQueryBuilder('u')
                ->where('u.name = :value')
                ->orWhere('u.email = :value')
                ->setParameter('value', $value);

            if ($store !== null){
                $q->innerJoin('u.store','s')
                    ->andWhere('s = :store')
                    ->setParameter('store', $store);
            }
            return $q->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            return null;
        }
    }

    public function findUserByRole($value,$store=null)
    {
        $q = $this->createQueryBuilder('u');

        if ($store !== null){
            $q->innerJoin('u.store','s')
                ->andWhere('s = :store')
                ->setParameter('store', $store);
        }
        return $q
            ->innerJoin('u.rules','r')
            ->where('r.name = :value')
            ->setParameter('value', $value)
            ->getQuery()->getResult();

    }

    public function QbUserByRole($value,$store=null)
    {

        $q = $this->createQueryBuilder('u')
            ->innerJoin('u.rules','r')
            ->where('r.name = :value')
            ->setParameter('value', $value);

        if ($store !== null){
            $q->innerJoin('u.store','s')
                ->andWhere('s = :store')
                ->setParameter('store', $store);
        }

        return $q;

    }

    public function findEmployees($store=null)
    {
        $q = $this->createQueryBuilder('u')
            ->leftJoin('u.rules','r')
            ->where("r is null")
            ->orWhere("r.name != 'ROLE_ADMIN'");

        if ($store !== null){
            $q->innerJoin('u.store','s')
                ->andWhere('s = :store')
                ->setParameter('store', $store);
        }


        return $q->getQuery()->getResult();
    }

    public function findWithRole($store=null)
    {
        $q = $this->createQueryBuilder('u')
            ->innerJoin('u.rules','r')
            ->where("r is not null");

        if ($store !== null){
            $q->innerJoin('u.store','s')
                ->andWhere('s = :store')
                ->setParameter('store', $store);
        }

        return $q->getQuery()->getResult();
    }

    public function getSaleByMonth($month,$year,$store=null): ?array {
        $q = $this->createQueryBuilder('u')
            ->select('u','count(s) as nbSales','sum(s.amount) as amountSold')
            ->leftJoin('u.sales','s')
            ->where('MONTH(s.addDate) = :month')
            ->andWhere('YEAR(s.addDate) = :year')
            ->setParameter('month', $month)
            ->setParameter('year', $year);
        if ($store !== null){
            $q->innerJoin('s.store','sto')
                ->innerJoin('u.store','st')
                ->andWhere('st = :store')
                ->andWhere('sto = :store')
                ->setParameter('store', $store);
        }
        return $q->groupBy('u.id')
            ->getQuery()->getArrayResult();
    }

    public function getSaleByYear($year,$store=null): ?array {
        $q = $this->createQueryBuilder('u')
            ->select('u','sum(s.amount) as amountSold','count(s) as nbSales')
            ->leftJoin('u.sales','s')
            ->where('YEAR(s.addDate) = :year')
            ->setParameter('year', $year);
        if ($store !== null){
            $q->innerJoin('s.store','sto')
                ->innerJoin('u.store','st')
                ->andWhere('st = :store')
                ->andWhere('sto = :store')
                ->setParameter('store', $store);
        }
        return $q->groupBy('u.id')
            ->getQuery()->getArrayResult();
    }

    public function findByMonthYearReport($month,$year,$store=null)
    {
        $qb = $this->createQueryBuilder('e')
            ->select('e','sp.salary as salary',
                'SUM(sp.amount) as amount',
                '(sp.salary - SUM(sp.amount)) as amountRemaining')
            ->leftJoin('e.rules','r')
            ->leftJoin('e.salaryPayments','sp',Join::WITH,
                'sp.year = :year and sp.month = :month')
            ->setParameter('year', $year)
            ->setParameter('month', $month)
            ->where("r is null")
            ->orWhere("r.name != 'ROLE_ADMIN'");

        if ($store !== null){
            $qb->innerJoin('sp.store','sto')
                ->innerJoin('u.store','st')
                ->andWhere('st = :store')
                ->andWhere('sto = :store')
                ->setParameter('store', $store);
        }
        return $qb
            ->groupBy('e.id')
            ->having('COUNT(sp) >= 0')
            ->getQuery()
            ->getResult()
            ;
    }

    public function getByPeriodDate($start,$end,$store=null): array
    {
        $qb = $this->createQueryBuilder('u')
            ->select('u.id','u.name','COUNT(s) as nbSales','SUM(s.profit) as profit',
                'SUM(s.amount) as amount')
            ->leftJoin('u.sales','s',Join::WITH,
            '(DATE(s.addDate) >= DATE(:start) and s.deleted = false 
            and DATE(s.addDate) <= DATE(:end))')
            ->innerJoin('u.rules','r')
            ->where("r is not null")
            ->andWhere("r.name != 'ROLE_ADMIN'")
            ->setParameter('start',  $start)
            ->setParameter('end',  $end);

        if ($store !== null){
            $qb->innerJoin('s.store','sto')
                ->innerJoin('u.store','st')
                ->andWhere('st = :store')
                ->andWhere('sto = :store')
                ->setParameter('store', $store);
        }

        return $qb->groupBy('u.id')
            ->having('COUNT(s) >= 0')
            ->orderBy('nbSales','DESC')
            //->orderBy('amount','DESC')
            ->getQuery()->getResult();
    }

    /**
     * @param string $username
     * @return void
     *
     * @deprecated since Symfony 5.3, use loadUserByIdentifier() instead
     */
    public function loadUserByUsername(string $username)
    {
        try {
            return $this->createQueryBuilder('u')
                ->where('u.name = :value')
                ->orWhere('u.email = :value')
                ->setParameter('value', $username)
                ->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            return null;
        }
    }

    /**
     * @param string $username
     * @return void
     *
     */
    public function loadUserByIdentifier(string $username)
    {
        try {
            return $this->createQueryBuilder('u')
                ->where('u.name = :value')
                ->orWhere('u.email = :value')
                ->setParameter('value', $username)
                ->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            return null;
        }
    }

    public function __call($name, $arguments)
    {
        // TODO: Implement @method null loadUserByIdentifier(string $identifier)
    }
}
