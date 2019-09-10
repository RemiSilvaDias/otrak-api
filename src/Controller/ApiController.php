<?php

namespace App\Controller;

use App\Utils\Cache;
use App\Repository\ShowRepository;
use App\Repository\SeasonRepository;
use App\Repository\EpisodeRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Encoder\JsonEncode;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ApiController extends AbstractController
{

    public function __construct(Cache $cache)
    {
        $this->catching = $cache;
    }

     /**
     * @Route("/test/{action}/{target}/{id}", name="test")
     */
    public function retrieveData($action, $target, $id){
    
        $data = null ;

        if ($action == 'search'){

            $data = file_get_contents("http://api.tvmaze.com/search/shows?q=".$id);

        }else{

            switch ($target){
                
                case 'show':
                $data = file_get_contents("http://api.tvmaze.com/shows/".$id);
                break;
                case 'season':
                $data = file_get_contents("http://api.tvmaze.com/shows/".$id."/seasons");
                break;
                case 'episode':
                $data = file_get_contents("http://api.tvmaze.com/episodes/".$id);
                break; 
                case 'scheduleEpisodes':
                $data = file_get_contents("http://api.tvmaze.com/schedule?country=US");
                break;
                case 'scheduleAnimeEpisodes':
                $data = file_get_contents("http://api.tvmaze.com/schedule?country=JP&type=animation");
                break;
            }
        }
        
        $data = \json_decode($data);
        dd($data);
        return $data;
        
        // $jsonResponse = new JsonResponse($data);
        // return $jsonResponse;
    }
}
