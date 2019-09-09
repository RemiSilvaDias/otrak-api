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

    // /**
    //  * @Route("/show/{id}", name="show_show")
    //  */
    // public function show(ShowRepository $showRepository, $id)
    // {
    //     // $data = $this->catching->toCache("http://api.tvmaze.com/shows/".$showId, $showId);
        
    //     // return $data;
        
    //     $response = $showRepository->showShow($id);

    //     // return $response;

    //     return $this->render('search/index.html.twig', [
    //         'controller_name' => 'SearchController',
    //         'response' => $response,
    //     ]);
    // }

     /**
     * @Route("/test/{action}/{target}/{id}", name="test")
     *
     */
    public function retrieveData($action, $target, $id){

        // $data = $this->catching->toCache($endpoint, $target.$id);

        if ($action == 'search'){

            // $showRepository = new ShowRepository();
            $response = ShowRepository::searchShow($id);
            // $endpoint = "http://api.tvmaze.com/search/shows?q=".$id;
            return new Response ($response);
            
        }elseif($action == 'get' && $target == 'show'){
            
            $response = ShowRepository::showShow($id);
            
            return new Response ($response);
            
        }elseif($action == 'get' && $target == 'season'){
            
            $response = SeasonRepository::showSeason($id);
            
            return new Response ($response);
            
        }elseif($action == 'get' && $target == 'episode'){
            
            $response = EpisodeRepository::showEpisode($id);
            
            dd($response);
            
            return new JsonResponse ($response);

        }elseif($action == 'get' && $target == 'scheduleEpisodes'){

            $response = EpisodeRepository::scheduleEpisode();

            return new Response ($response);

        }elseif($action == 'get' && $target == 'scheduleAnimeEpisodes'){

            $response = EpisodeRepository::scheduleAnimeEpisode();

            return new Response ($response);

        }else{

            return new Response (null);
        }
    }
}
