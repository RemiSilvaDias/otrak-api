<?php

namespace App\Controller;

use App\Repository\ShowRepository;
use App\Repository\SeasonRepository;
use App\Repository\EpisodeRepository;
use Symfony\Component\httpsFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\httpsFoundation\JsonResponse;
use Symfony\Component\Serializer\Encoder\JsonEncode;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ApiController extends AbstractController
{

    /**
     * @Route("/test/{action}/{target}/{id}", name="test")
     */
    public function retrieveData($action, $target, $id){
    
        
        $data = null ;

        $cache = new FilesystemAdapter(
            $namespace = '',
            $defaultLifetime = 20
        );

        if ($action == 'search'){

            $endpoint = "https://api.tvmaze.com/search/shows?q=".$id;

        }else{

            switch ($target){
                
                case 'show':
                $endpoint = "https://api.tvmaze.com/shows/".$id;
                break;
                case 'showFull':
                $endpoint = "https://api.tvmaze.com/shows/".$id."?embed=cast";
                break;
                case 'showComplete':
                $endpoint = "https://api.tvmaze.com/shows/".$id."?embed[]=seasons&embed[]=episodes&embed[]=cast";
                break;
                case 'season':
                $endpoint = "https://api.tvmaze.com/shows/".$id."/seasons";
                break;
                case 'firstEpisode':
                $endpoint = "https://api.tvmaze.com/shows/".$id."/episodebynumber?season=1&number=1";
                break; 
                case 'lastEpisode':
                $endpoint = "https://api.tvmaze.com/episodes/".$id;
                break; 
                case 'scheduleEpisodes':
                $endpoint = "https://api.tvmaze.com/schedule?country=US";
                break;
                case 'scheduleAnimeEpisodes':
                $endpoint = "https://api.tvmaze.com/schedule?country=JP&type=animation";
                break;
            }
        }
        
        /*
        * Vérification de la présence de la donnée dans le cache, mise en cache si non trouvée.
        */
        $data = $cache->getItem('data-'.md5($target.$id));

        if (!$data->isHit()){    

            $header = get_headers($endpoint)[0];

            if (!preg_match('/.*\s2.*/', $header)){

                $jsonResponse = new JsonResponse(null);
                return $jsonResponse;
            }

            $data->set(file_get_contents($endpoint));
    
            $data->expiresAfter(3600);
            $cache->save($data);
        } 

        $response = $data->get();

        $data = \json_decode($response);

        return $data;

    }
}
