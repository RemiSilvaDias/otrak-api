<?php

namespace App\Utils;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

class ApiFetcher {

    public function retrieveData($action, $target, $id)
    {
        $data = null;

        $cache = new FilesystemAdapter(
            $namespace = '',
            $defaultLifetime = 3600
        );

        if ($action == 'search') {
            $endpoint = "https://api.tvmaze.com/search/shows?q=" . $id;
        } else {
            switch ($target) {
                case 'show':
                    $endpoint = "https://api.tvmaze.com/shows/" . $id;
                    break;
                case 'showFull':
                    $endpoint = "https://api.tvmaze.com/shows/" . $id . "?embed=cast";
                    break;
                case 'showComplete':
                    $endpoint = "https://api.tvmaze.com/shows/" . $id . "?embed[]=seasons&embed[]=episodes&embed[]=cast";
                    break;
                case 'season':
                    $endpoint = "https://api.tvmaze.com/shows/" . $id . "/seasons";
                    break;
                case 'firstEpisode':
                    $endpoint = "https://api.tvmaze.com/shows/" . $id . "/episodebynumber?season=1&number=1";
                    break;
                case 'lastEpisode':
                    $endpoint = "https://api.tvmaze.com/episodes/" . $id;
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
        * Verify is the data is in the cache, if not we interrogate the external API
        */
        $data = $cache->getItem('data-' . md5($target . $id));

        if (!$data->isHit()) {
            $header = get_headers($endpoint)[0];

            if (!preg_match('/.*\s2.*/', $header)) {
                $jsonResponse = new JsonResponse(null, 410);

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
