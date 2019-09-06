<?php

namespace App\Repository;

use App\Utils\Cache;
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
    public function __construct(ManagerRegistry $registry, Cache $cache)
    {
        parent::__construct($registry, Episode::class);
        $this->catching = $cache;
    }

    // /**
    //  * @return Episode[] Returns an array of Episode objects
    //  */
    /*
    Fonction d'affichage d'un épisode. Appel à l'API tvmaze sur le endpoint episodes avec en paramètre l'id de l'épisode à afficher.
    */
    public function showEpisode($episodeId){

        $data = $this->catching->toCache("http://api.tvmaze.com/episodes/".$episodeId, $episodeId);
        
        return $data;
    
    }

}
