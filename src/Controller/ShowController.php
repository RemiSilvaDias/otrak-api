<?php

namespace App\Controller;

use App\Entity\Show;
use App\Controller\ApiController;
use App\Repository\ShowRepository;
use App\Repository\SeasonRepository;
use App\Repository\EpisodeRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/api")
 */
class ShowController extends AbstractController
{
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
            $status = 0;

            $poster = '';
            if (!is_null($response->show->image)) $poster = $response->show->image->original;

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
    public function aired()
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
            $poster = '';
            if (!is_null($response->show->image)) $poster = $response->show->image->original;

            $episodes[] = array(
                'show_name' => $response->show->name,
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
