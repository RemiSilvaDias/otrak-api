<?php

namespace App\Repository;

use App\Entity\Season;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

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
 
    /*
    Fonction d'affichage d'un épisode. Appel à l'API tvmaze sur le endpoint seasons avec en paramètre l'id de l'épisode à afficher.
    */
    public function showSeason($showId){

        $json = file_get_contents("http://api.tvmaze.com/shows/".$showId."/seasons");

        return $json;
    }
}
