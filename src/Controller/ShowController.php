<?php

namespace App\Controller;

use App\Entity\Show;
use App\Controller\ApiController;
use App\Repository\ShowRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/api")
 */
class ShowController extends AbstractController
{
    public const STATUS_IN_DEVELOPMENT = 0;
    public const STATUS_RUNNING = 1;
    public const STATUS_ENDED = 2;

    /**
     * @Route("/shows/search/{search}", methods={"GET"})
     */
    public function searchShows(string $search, Request $request, ShowRepository $showRepository)
    {
        $shows = [];
        $search = str_replace("+", " ", $search);
        
        $data = ApiController::retrieveData("search", "show", $search);

        foreach ($data as $response) {
            $name = $response->show->name;

            $status = self::STATUS_ENDED;

            switch ($response->show->status) {
                case 'In Development':
                    $status = self::STATUS_IN_DEVELOPMENT;
                    break;
                
                case 'Running':
                    $status = self::STATUS_RUNNING;
                    break;
            }

            $poster = '';
            if (!is_null($response->show->image)) $poster = $response->show->image->original;

            $type = '';
            if (!is_null($response->show->type)) $type = $response->show->type;

            $genre = null;
            if (!is_null($response->show->genres)) $genre = $response->show->genres;

            $rating = null;
            if (!is_null($response->show->rating) && !is_null($response->show->rating->average)) $rating = $response->show->rating->average;

            $language = '';
            if (!is_null($response->show->language)) $language = $response->show->language;

            $runtime = 0;
            if (!is_null($response->show->runtime)) $runtime = $response->show->runtime;

            $shows[] = array(
                'name' => $name,
                'status' => $status,
                'poster' => $poster,
                'type' => $type,
                'genre' => $genre,
                'rating' => $rating,
                'language' => $language,
                'runtime' => $runtime,
                'id_tvmaze' => $response->show->id,
                'premiered' => $response->show->premiered,
            );
        }

        $jsonResponse = new JsonResponse($shows);
        
        return $jsonResponse;
    }

    /**
     * @Route("/shows/aired", methods={"GET"})
     */
    public function aired(ShowRepository $showRepository)
    {
        $episodesApi = [];
        $episodes = [];

        $series = ApiController::retrieveData("get", "scheduleEpisodes", 'scheduleUS');
        $animes = ApiController::retrieveData("get", "scheduleAnimeEpisodes", 'scheduleJP');

        foreach ($series as $serie) {
            $episodesApi[] = $serie;
        }

        foreach ($animes as $anime) {
            $episodesApi[] = $anime;
        }

        \usort($episodesApi, function($item1, $item2) {
            return $item2->airstamp <=> $item1->airstamp;
        });

        foreach ($episodesApi as $response) {
            $showDb = null;
            $showDb = $showRepository->findOneBy(['id_tvmaze', $reponse->show->id]);

            $status = self::STATUS_ENDED;

            switch ($response->show->status) {
                case 'In Development':
                    $status = self::STATUS_IN_DEVELOPMENT;
                    break;
                
                case 'Running':
                    $status = self::STATUS_RUNNING;
                    break;
            }

            $type = '';
            $genre = null;

            if (!is_null($showDb)) {
                $type = $showDb->getType();
                $genre = $showDb->getGenre();
            }

            $poster = '';
            if (!is_null($response->show->image)) $poster = $response->show->image->original;

            if ($type == '' && !is_null($type = $response->show->type)) $type = $response->show->type;

            if (is_null($genre) && !is_null($response->show->genres)) $genre = $response->show->genres;

            $episodes[] = array(
                'show_name' => $response->show->name,
                'show_status' => $status,
                'Show_type' => $type,
                'show_genre' => $genres,
                'name' => $response->name,
                'season' => $response->season,
                'number' => $response->number,
                'poster' => $poster,
                'show_id_tvmaze' => $response->show->id,
                'id_tvmaze' => $response->id,
                'airstamp' => $response->airstamp,
            );
        }

        $jsonResponse = new JsonResponse($episodes);
        
        return $jsonResponse;
    }
}
