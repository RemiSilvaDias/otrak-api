<?php

namespace App\Repository;

use App\Entity\Episode;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method Episode|null find($id, $lockMode = null, $lockVersion = null)
 * @method Episode|null findOneBy(array $criteria, array $orderBy = null)
 * @method Episode[]    findAll()
 * @method Episode[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EpisodeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Episode::class);
    }

    public function findEpisodeBySeason($season, $episodeNumber)
    {
        return $this->createQueryBuilder('e')
            ->where('e.season = :season')
            ->andWhere('e.number = :num')
            ->setParameters(['season' => $season, 'num' => $episodeNumber])
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
