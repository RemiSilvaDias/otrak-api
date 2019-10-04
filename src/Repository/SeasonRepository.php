<?php

namespace App\Repository;

use App\Entity\Season;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method Season|null find($id, $lockMode = null, $lockVersion = null)
 * @method Season|null findOneBy(array $criteria, array $orderBy = null)
 * @method Season[]    findAll()
 * @method Season[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SeasonRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Season::class);
    }

    public function findSeasonByShow($show, $seasonNumber)
    {
        return $this->createQueryBuilder('s')
            ->where('s.tvShow = :show')
            ->andWhere('s.number = :num')
            ->setParameters(['show' => $show, 'num' => $seasonNumber])
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
