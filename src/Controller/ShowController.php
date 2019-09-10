<?php

namespace App\Controller;

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
    /**
     * @Route("/shows/search/{search}", requirements={"search"="\w+"}, methods={"GET"})
     */
    public function searchShows(string $search, Request $request, ShowRepository $showRepository)
    {
        $search = str_replace("+", " ", $search);

        $data = ApiController::retrieveData("search", "show", $search);
        $data = \json_decode($data);

        foreach ($data as $currentResponse) {
            $currentResponse->show->test = 'value';
            // dd($currentResponse->show);
            // dd($currentResponse);
        }

        dd($data);

        $jsonResponse = new JsonResponse($data);
        // return $data;
        return $jsonResponse;
    }
}
