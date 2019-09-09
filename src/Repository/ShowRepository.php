<?php

namespace App\Repository;

use App\Entity\Show;
use App\Utils\Cache;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\JsonResponse;
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
    public function searchShow($search){

        $data = file_get_contents("http://api.tvmaze.com/search/shows?q=".$search);

        return $data;
        
    }

    /*
    Fonction d'affichage d'un show. Appel à l'API tvmaze sur le endpoint shows avec en paramètre l'id du show à afficher.
    */
    public function showShow($showId){

        // $response = new Response();

        // $code = $response->getStatusCode("http://api.tvmaze.com/shows/".$showId);
    
        // dump($response);

        // if (!$code = 200){

        // return null;

        // }else{

        //     $json = file_get_contents("http://api.tvmaze.com/shows/".$showId);

        //     return $json;
        // }

        $data = file_get_contents("http://api.tvmaze.com/shows/".$showId);

        return $data;
    }
}
