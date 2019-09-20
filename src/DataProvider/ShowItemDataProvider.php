<?php

namespace App\DataProvider;

use App\Entity\Show;
use App\Controller\ApiController;
use App\Repository\ShowRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use ApiPlatform\Core\DataProvider\ItemDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use ApiPlatform\Core\Exception\ResourceClassNotSupportedException;

final class ShowItemDataProvider implements ItemDataProviderInterface, RestrictedDataProviderInterface
{
    public const STATUS_IN_DEVELOPMENT = 0;
    public const STATUS_RUNNING = 1;
    public const STATUS_ENDED = 2;

    /**
     * @var ShowRepository
     */
    private $repository;

    public function __construct(ShowRepository $repository)
    {
        $this->repository = $repository;
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return Show::class === $resourceClass;
    }

    public function getItem(string $resourceClass, $id, string $operationName = null, array $context = [])
    {
        $show = $this->repository->findOneBy(['id_tvmaze' => $id]);

        if ($show === null) {
            $showApi = ApiController::retrieveData('get', 'showComplete', $id);

            $name = $showApi->name;

            $summary = '';
            if (!is_null($showApi->summary)) $summary = $showApi->summary;

            $status = 0;
            if (!is_null($showApi->status)) {
                $status = self::STATUS_ENDED;

                switch ($showApi->status) {
                    case 'In Development':
                        $status = self::STATUS_IN_DEVELOPMENT;
                        break;

                    case 'Running':
                        $status = self::STATUS_RUNNING;
                        break;
                }
            }

            $poster = '';
            if (!is_null($showApi->image)) $poster = $showApi->image->original;

            $type = '';
            if (!is_null($showApi->type)) $type = $showApi->type;

            $genre = [];
            if (!is_null($showApi->genres)) {

                foreach ($showApi->genres as $currentGenre) {
                    $genre = self::array_push_assoc($genre, 'name', $currentGenre);
                }
            }

            $website = '';
            if (!is_null($showApi->officialSite)) $website = $showApi->officialSite;

            $rating = 0;
            if (!is_null($showApi->rating)) $rating = $showApi->rating->average;

            $language = '';
            if (!is_null($showApi->language)) $language = $showApi->language;

            $runtime = 0;
            if (!is_null($showApi->runtime)) $runtime = $showApi->runtime;

            $idTvDb = null;
            if (!is_null($showApi->externals->thetvdb)) $idTvDb = $showApi->externals->thetvdb;

            $premiered = null;
            if (!is_null($showApi->premiered)) $idTvDb = $showApi->premiered;

            $network = null;
            if (!is_null($showApi->network)) $network = $showApi->network->name;
            else if (is_null($showApi->network) && !is_null($showApi->webChannel)) $network = $showApi->webChannel->name;

            $nbSeasons = 0;
            $nbEpisodes = 0;

            $seasons = [];

            if (sizeof($showApi->_embedded->seasons) > 0) {
                $nbSeasons += \sizeof($showApi->_embedded->seasons);
                $nbEpisodes += \sizeof($showApi->_embedded->episodes);

                foreach ($showApi->_embedded->seasons as $season) {
                    $episodes = [];

                    $seasonPoster = '';
                    if (!is_null($season->image)) $poster = $season->image->original;

                    $episodes = [];
                    $episodesCount = 0;

                    foreach($showApi->_embedded->episodes as $episode) {
                        if ($episode->season == $season->number) {
                            $episodesCount++;

                            $episodeImage = '';
                            if (!is_null($episode->image)) $poster = $episode->image->original;

                            $episodes[] = [
                                'name' => $episode->name,
                                'number' => $episode->number,
                                'summary' => $episode->summary,
                                'airstamp' => $episode->airstamp,
                                'image' => $episodeImage,
                            ];
                        }
                    }

                    $seasons[] = [
                        'number' => $season->number,
                        'poster' => $seasonPoster,
                        'episodeCount' => $season->episodeOrder,
                        'premiereDate' => $season->premiereDate,
                        'endDate' => $season->endDate,
                        'episodes' => $episodes,
                    ];
                }
            }

            $cast = null;
            if (!is_null($showApi->_embedded->cast)) $cast = $showApi->_embedded->cast;

            $show = new JsonResponse([
                'name' => $name,
                'summary' => $summary,
                'type' => $type,
                'genre' => $genre,
                'status' => $status,
                'premiered' => $premiered,
                'poster' => $poster,
                'website' => $website,
                'network' => $network,
                'rating' => $rating,
                'language' => $language,
                'runtime' => $runtime,
                'id_tvmaze' => $showApi->id,
                'id_tvdb' => $idTvDb,
                'api_update' => $showApi->updated,
                'nbSeasons' => $nbSeasons,
                'nbEpisodes' => $nbEpisodes,
                'seasons' => $seasons,
                'cast' => $cast,
            ]);

            return $show;
        }

        return $show;
    }

    public function array_push_assoc($array, $key, $value){
        $array[$key] = $value;

        return $array;
    }
}
