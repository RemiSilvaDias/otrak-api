<?php

namespace App\Controller;

use App\Controller\ApiController;
use App\Entity\Episode;
use App\Repository\ShowRepository;
use App\Repository\SeasonRepository;
use App\Repository\EpisodeRepository;
use App\Repository\FollowingRepository;
use App\Utils\ApiFetcher;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/api")
 */
class ShowController extends AbstractController
{
    public const STATUS_IN_DEVELOPMENT = 0;
    public const STATUS_RUNNING = 1;
    public const STATUS_ENDED = 2;

    public const TRACKING_WATCHING = 0;
    public const TRACKING_COMPLETED = 1;
    public const TRACKING_SEE_NEXT = 2;
    public const TRACKING_UPCOMING = 3;
    public const TRACKING_STOPPED = 4;

    /**
     * Search a show in the external API and return the relevant data to the front
     * 
     * @Route("/shows/search/{search}", methods={"GET"})
     */
    public function searchShows(string $search, ApiFetcher $apiFetcher)
    {
        $shows = [];
        $search = str_replace("+", " ", $search);
        
        $data = $apiFetcher->retrieveData("search", "show", $search);

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
            if (!is_null($response->show->image)) {
                $poster = \str_replace('http://', 'https://', $response->show->image->original);
            }

            $type = '';
            if (!is_null($response->show->type)) $type = $response->show->type;

            $genre = null;
            if (!is_null($response->show->genres)) $genre = $response->show->genres;

            $rating = 0;
            if (!is_null($response->show->rating) && !is_null($response->show->rating->average)) $rating = $response->show->rating->average;

            $language = '';
            if (!is_null($response->show->language)) $language = $response->show->language;

            $runtime = 0;
            if (!is_null($response->show->runtime)) $runtime = $response->show->runtime;

            $latestEpisodeNumber = 0;
            $latestEpisodeSeason = 0;

            if (isset($response->show->_links->previousepisode)) {
                $lastEpisodeId = 0;
                \preg_match('/(\d+)$/', $response->show->_links->previousepisode->href, $lastEpisodeId, PREG_OFFSET_CAPTURE);
                $lastEpisode = $apiFetcher->retrieveData("get", "lastEpisode", $lastEpisodeId[0][0]);

                $latestEpisodeNumber = $lastEpisode->number;
                $latestEpisodeSeason = $lastEpisode->season;
            }

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
                'latestEpisodeNumber' => $latestEpisodeNumber,
                'latestEpisodeSeason' => $latestEpisodeSeason,
            );
        }

        $jsonResponse = new JsonResponse($shows, 200);
        
        return $jsonResponse;
    }

    /**
     * Give the list of the episodes aired the last 24h. If the user is connected, only display the shows he's tracking when available
     * 
     * @Route("/shows/aired", methods={"GET"})
     */
    public function aired(ShowRepository $showRepository, FollowingRepository $followingRepository, ApiFetcher $apiFetcher)
    {
        $episodesApi = [];
        $episodes = [];
        $tracked = false;

        $series = $apiFetcher->retrieveData("get", "scheduleEpisodes", 'scheduleUS');
        $animes = $apiFetcher->retrieveData("get", "scheduleAnimeEpisodes", 'scheduleJP');

        foreach ($series as $serie) {
            $serie->tracked = $tracked;
            $episodesApi[] = $serie;
        }

        foreach ($animes as $anime) {
            $anime->tracked = $tracked;
            $episodesApi[] = $anime;
        }

        \usort($episodesApi, function($item1, $item2) {
            return $item1->airstamp <=> $item2->airstamp;
        });

        $episodesApiBackup = $episodesApi;

        $user = $this->getUser();

        if (!is_null($user)) {
            $followingListUser = $followingRepository->findBy(['user' => $user], ['id' => 'DESC']);
            $tracked = true;

            foreach ($episodesApi as $key => $value) {
                $found = false;
                
                foreach($followingListUser as $following) {
                    if ($found === false && !is_null($following->getEpisode()) && $following->getTvShow()->getIdTvmaze() == $value->show->id && ($following->getSeason()->getNumber() == $value->season && $following->getEpisode()->getNumber() == $value->number - 1)) {
                        $value->tracked = $tracked;
                        $found = !$found;
                    }
                }

                if ($found === false) unset($episodesApi[$key]);
            }
        }

        if (empty($episodesApi)) $episodesApi = $episodesApiBackup;

        foreach ($episodesApi as $response) {
            $showDb = null;
            $showDb = $showRepository->findOneBy(['id_tvmaze' => $response->show->id]);

            $status = self::STATUS_ENDED;

            switch ($response->show->status) {
                case 'In Development':
                    $status = self::STATUS_IN_DEVELOPMENT;
                    break;
                
                case 'Running':
                    $status = self::STATUS_RUNNING;
                    break;
            }

            $id = null;
            $type = '';
            $genre = [];
            $rating = 0;
            $language = '';

            if (!is_null($showDb)) {
                $id = $showDb->getId();
                $type = $showDb->getType()->getName();
                $genre = $showDb->getGenre();
                if (!is_null($showDb->getRating())) $rating = $showDb->getRating();
                $language = $showDb->getLanguage();
            }

            $poster = '';
            if (!is_null($response->show->image)) {
                $poster = \str_replace('http://', 'https://', $response->show->image->original);
            }

            if ($type == '' && !is_null($type = $response->show->type)) $type = $response->show->type;

            if (sizeof($genre) == 0 && !is_null($response->show->genres)) {
                foreach ($response->show->genres as $currentGenre) {
                    $genre[] = ['name' => $currentGenre];
                }
            }

            if (isset($response->show->rating->average)) $rating = $response->show->rating->average;
            if ($language == '' && !is_null($response->show->language)) $language = $response->show->language;

            $episodes[] = array(
                'show_id' => $id,
                'show_id_tvmaze' => $response->show->id,
                'show_name' => $response->show->name,
                'show_status' => $status,
                'Show_type' => $type,
                'show_genre' => $genre,
                'show_rating' => $rating,
                'show_language' => $language,
                'name' => $response->name,
                'season' => $response->season,
                'number' => $response->number,
                'poster' => $poster,
                'id_tvmaze' => $response->id,
                'airstamp' => $response->airstamp,
                'tracked' => $response->tracked,
            );
        }

        $jsonResponse = new JsonResponse($episodes, 200);
        
        return $jsonResponse;
    }

    /**
     * Give the list of the next episode to watch of the tracked shows
     * 
     * @Route("/shows/next", methods={"GET"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function next(ShowRepository $showRepository, EpisodeRepository $episodeRepository, SeasonRepository $seasonRepository, FollowingRepository $followingRepository)
    {
        $nextEpisodes = [];
        $episodes = [];
        
        $user = $this->getUser();

        $followings = $followingRepository->findBy(['user' => $user], ['episode' => 'DESC']);

        $lastShowIndex = 0;
        $onTrack = false;

        foreach ($followings as $following) {
            $showFollowing = $followingRepository->findOneBy(['user' => $user, 'tvShow' => $following->getTvShow(), 'season' => null, 'episode' => null]);

            if ($showFollowing->getStatus() == self::TRACKING_WATCHING && $following->getStatus() == self::TRACKING_COMPLETED && !is_null($following->getEpisode()) && $lastShowIndex != $following->getTvShow()->getIdTvmaze()) {
                $nextEpisode = new Episode();
                if ($following->getEpisode()->getNumber() != $following->getSeason()->getEpisodes()->count()) $nextEpisode = $episodeRepository->findOneBy(['season' => $following->getEpisode()->getSeason(), 'number' => $following->getEpisode()->getNumber() + 1]);
                else {
                    $nextSeason = $seasonRepository->findOneBy(['tvShow' => $following->getTvShow(), 'number' => $following->getSeason()->getNumber() + 1]);
                    if ($nextSeason->getEpisodes()->first()->getAirstamp() < new \DateTime()) $nextEpisode = $nextSeason->getEpisodes()->first();
                    else $nextEpisode = null;
                }

                if ($following->getTvShow()->getIdTvmaze() != $lastShowIndex) $onTrack = false;

                if(!is_null($nextEpisode) && $nextEpisode->getSeason()->getTvShow() != $following->getTvShow()) {
                    $nextSeason = $seasonRepository->findOneBy(['tvShow' => $following->getTvShow(), 'number' => $following->getSeason()->getNumber() + 1]);

                    if (!is_null($nextSeason) && !is_bool($nextSeason)) {
                        $nextEpisode = $nextSeason->getEpisodes()->first();
                    }
                }
                
                $currentDatetime = new \DateTime();

                if ((!is_null($nextEpisode) && !is_bool($nextEpisode))) {
                    if ($nextEpisode->getAirstamp() > $currentDatetime) $onTrack = true;
                    
                    if ($onTrack === false) $episodes[] = $nextEpisode;

                    $lastShowIndex = $following->getTvShow()->getIdTvmaze();
                }
            }
        }

        foreach ($episodes as $response) {
            if (!is_bool($response)) {
                $nextEpisodes[] = array(
                    'show_id' => $response->getSeason()->getTvShow()->getId(),
                    'show_id_tvmaze' => $response->getSeason()->getTvShow()->getIdTvmaze(),
                    'show_name' => $response->getSeason()->getTvShow()->getName(),
                    'show_status' => $response->getSeason()->getTvShow()->getStatus(),
                    'Show_type' => $response->getSeason()->getTvShow()->getType()->getName(),
                    'show_genre' => $response->getSeason()->getTvShow()->getGenre(),
                    'show_rating' => $response->getSeason()->getTvShow()->getRating(),
                    'show_language' => $response->getSeason()->getTvShow()->getLanguage(),
                    'name' => $response->getName(),
                    'season' => $response->getSeason()->getNumber(),
                    'number' => $response->getNumber(),
                    'poster' => $response->getSeason()->getTvShow()->getPoster(),
                    'id_tvmaze' => $response->getId(),
                    'airstamp' => $response->getAirstamp(),
                );
            }
        }

        $jsonResponse = new JsonResponse($nextEpisodes, 200);
        
        return $jsonResponse;
    }
}
