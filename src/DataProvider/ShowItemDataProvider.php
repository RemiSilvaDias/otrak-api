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
            $showApi = ApiController::retrieveData('get', 'showFull', $id);

            $name = $showApi->name;

            $summary = '';
            if (!is_null($showApi->summary)) $summary = $showApi->summary;

            $status = 0;
            if (!is_null($showApi->status)) $status = $showApi->status;

            $poster = '';
            if (!is_null($showApi->image)) $poster = $showApi->image->original;

            $type = '';
            if (!is_null($showApi->type)) $type = $showApi->type;

            $genre = null;
            if (!is_null($showApi->genres)) $genre = $showApi->genres;

            $website = '';
            if (!is_null($showApi->officialSite)) $website = $showApi->officialSite;

            $rating = null;
            if (!is_null($showApi->rating->average)) $rating = $showApi->rating->average;

            $language = '';
            if (!is_null($showApi->language)) $language = $showApi->language;

            $runtime = 0;
            if (!is_null($showApi->runtime)) $runtime = $showApi->runtime;

            $idTvDb = null;
            if (!is_null($showApi->externals->thetvdb)) $idTvDb = $showApi->externals->thetvdb;

            $cast = null;
            if (!is_null($showApi->_embedded->cast)) $cast = $showApi->_embedded->cast;

            $show = new JsonResponse([
                'name' => $name,
                'summary' => $summary,
                'type' => $type,
                'genre' => $genre,
                'status' => $status,
                'premiered' => $showApi->premiered,
                'poster' => $poster,
                'website' => $website,
                'rating' => $rating,
                'language' => $language,
                'runtime' => $runtime,
                'id_tvmaze' => $showApi->id,
                'id_tvdb' => $idTvDb,
                'api_update' => $showApi->updated,
                'cast' => $cast,
            ]);

            return $show;
        }

        return $show;
    }
}
