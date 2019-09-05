<?php

namespace App\Repository;

use App\Entity\Show;
use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method Show|null find($id, $lockMode = null, $lockVersion = null)
 * @method Show|null findOneBy(array $criteria, array $orderBy = null)
 * @method Show[]    findAll()
 * @method Show[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ShowRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Show::class);

    }
    /*
    Fonction de recherche. Appel à l'API tvmaze sur le endpoint search avec en paramètre l'input de la recherche.
    */
    public function searchShow($query){

        $json = file_get_contents("http://api.tvmaze.com/search/shows?q=".$query);

        return $json;
    }

    /*
    Fonction d'affichage d'un show. Appel à l'API tvmaze sur le endpoint shows avec en paramètre l'id du show à afficher.
    */
    public function showShow(){

        /*
        Création du cache.
        */
        $cache = new FilesystemAdapter(

            $namespace = '',
            $defaultLifetime = 20
        );
        
        /*
        Récupère si il existe l'item show.id et le créé si il n'existe pas.
        */
        $episodeInfo = $cache->getItem('show');
        
        /*
        Récupère si il existe l'item show.id et le créé si il n'existe pas.
        */
        if (!$episodeInfo->isHit()){
            
            $episodeInfo->set(file_get_contents("http://api.tvmaze.com/shows"));
            $cache->save($episodeInfo);
            $response = $episodeInfo->get();

            return $response;

        } else {

            // $json = file_get_contents("http://api.tvmaze.com/shows/".$showId);
            $response = $episodeInfo->get();

            return $response;

        }
    }
}
