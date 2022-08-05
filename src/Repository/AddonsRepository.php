<?php

namespace App\Repository;

use App\Entity\Addons;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Addons|null find($id, $lockMode = null, $lockVersion = null)
 * @method Addons|null findOneBy(array $criteria, array $orderBy = null)
 * @method Addons[]    findAll()
 * @method Addons[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AddonsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Addons::class);
    }
}
