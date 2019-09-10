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
        $show = $this->repository->find($id);

        if ($show === null) {
            $showApi = ApiController::retrieveData('get', 'showFull', $id);

            $show = new JsonResponse([
                'name' => $showApi->name,
                'summary' => $showApi->summary,
                'status' => 0,
                'poster' => $showApi->image->original,
                'website' => $showApi->officialSite,
                'rating' => $showApi->rating->average,
                'language' => $showApi->language,
                'runtime' => $showApi->runtime,
                'id_tvmaze' => $showApi->id,
                'id_tvdb' => $showApi->externals->thetvdb,
                'api_update' => $showApi->updated,
                'cast' => $showApi->_embedded->cast,
            ]);

            return $show;
        }

        return $show;
    }
}
