<?php

namespace App\DataProvider;

use App\Entity\Season;
use App\Controller\ApiController;
use App\Repository\SeasonRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use ApiPlatform\Core\DataProvider\ItemDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use ApiPlatform\Core\Exception\ResourceClassNotSupportedException;

final class SeasonItemDataProvider implements ItemDataProviderInterface, RestrictedDataProviderInterface
{
    /**
     * @var seasonRepository
     */
    private $repository;

    public function __construct(SeasonRepository $repository)
    {
        $this->repository = $repository;
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return Season::class === $resourceClass;
    }

    public function getItem(string $resourceClass, $id, string $operationName = null, array $context = [])
    {
        $season = $this->repository->find($id);

        if ($season === null) {
            $seasonApi = ApiController::retrieveData('get', 'season', $id);

            $season = new JsonResponse([
                'number' => $seasonApi->number,
                'status' => 0,
                'poster' => $seasonApi->image->original,
                'episodeCount' => $seasonApi->episodeCount,
                'premiereDate' => $seasonApi->premiereDate,
                'endDate' => $seasonApi->endDate,
                'tvShow' => $seasonApi->runtime,
            ]);

            return $season;
        }

        return $season;
    }
}
