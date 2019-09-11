<?php

namespace App\Controller;

use App\Repository\ShowRepository;
use App\Repository\SeasonRepository;
use App\Repository\EpisodeRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Encoder\JsonEncode;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ApiController extends AbstractController
{

    public function __construct(AdapterInterface $cache)
    {
        $this->catching = $cache;
    }

     /**
     * @Route("/test/{action}/{target}/{id}", name="test")
     */
    public function retrieveData($action, $target, $id){
    
        
        $data = null ;

        if ($action == 'search'){

            $endpoint = "http://api.tvmaze.com/search/shows?q=".$id;

        }else{

            switch ($target){
                
                case 'show':
                $endpoint = "http://api.tvmaze.com/shows/".$id;
                break;
                case 'showFull':
                $endpoint = "http://api.tvmaze.com/shows/".$id."?embed=cast";
                break;
                case 'season':
                $endpoint = "http://api.tvmaze.com/shows/".$id."/seasons";
                break;
                case 'episode':
                $endpoint = "http://api.tvmaze.com/episodes/".$id;
                break; 
                case 'scheduleEpisodes':
                $endpoint = "http://api.tvmaze.com/schedule?country=US";
                break;
                case 'scheduleAnimeEpisodes':
                $endpoint = "http://api.tvmaze.com/schedule?country=JP&type=animation";
                break;
            }
        }

        /*
        * Vérification de la présence de la donnée dans le cache, mise en cache si non trouvée.
        */
        $data = $this->catching->getItem('data-'.md5($target.$id));

        if (!$data->isHit()){    

            $header = get_headers($endpoint)[0];

            if (!preg_match('/.*\s2.*/', $header)){

                // return $this->render('search/index.html.twig',[
                //     'data' => null
                // ]);

                $jsonResponse = new JsonResponse(null);
                return $jsonResponse;
            }

            $data->set(file_get_contents($endpoint));
    
            $data->expiresAfter(20);
            $this->catching->save($data);
        } 

        $response = $data->get();

        $data = \json_decode($response);

        /*
        * pour les tests, pour afficher sur un twig
        */
        // return $this->render('search/index.html.twig',[
        //     'data' => $data
         
        // ]);

        /*
        * à décommenter à terme pour renvoyer en format Json.
        */
        $jsonResponse = new JsonResponse($data);
        return $jsonResponse;
    }
}
