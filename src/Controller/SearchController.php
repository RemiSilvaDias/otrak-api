<?php

namespace App\Controller;

use App\Repository\ShowRepository;
use App\Repository\SeasonRepository;
use App\Repository\EpisodeRepository;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class SearchController extends AbstractController
{
    /**
     * @Route("/search/{id}", name="search")
     */
    public function index(ShowRepository $showRepository, $id)
    {

        $response = $showRepository->searchShow($id);

        return $this->render('search/index.html.twig', [
            'controller_name' => 'SearchController',
            'response' => $response,
        ]);
    }

    /**
     * @Route("/show/{id}", name="show_show")
     */
    public function show(ShowRepository $showRepository, $id)
    {

        $response = $showRepository->showShow($id);

        return $this->render('search/index.html.twig', [
            'controller_name' => 'SearchController',
            'response' => $response,
        ]);
    }

    /**
     * @Route("/show/{id}/season", name="season_show")
     */
    public function season(SeasonRepository $seasonRepository, $id)
    {

        $response = $seasonRepository->showSeason($id);

        return $this->render('search/index.html.twig', [
            'controller_name' => 'SearchController',
            'response' => $response,
        ]);
    }

    /**
     * @Route("/episode/{id}", name="episode_show")
     */
    public function showEpisode(EpisodeRepository $episodeRepository, $id)
    {

        $response = $episodeRepository->showEpisode($id);

        return $this->render('search/index.html.twig', [
            'controller_name' => 'SearchController',
            'response' => $response,
        ]);
    }
}
