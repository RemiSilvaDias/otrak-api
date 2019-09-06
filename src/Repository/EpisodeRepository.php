<?php

namespace App\Repository;

use App\Entity\Episode;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

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

    // /**
    //  * @return Episode[] Returns an array of Episode objects
    //  */
    /*
    Fonction d'affichage d'un épisode. Appel à l'API tvmaze sur le endpoint episodes avec en paramètre l'id de l'épisode à afficher.
    */
    public function showEpisode($episodeId){

        $json = file_get_contents("http://api.tvmaze.com/episodes/".$episodeId);

        return $json;
    }

}
