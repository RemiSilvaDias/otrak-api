<?php

namespace App\DataProvider;

use App\Entity\Episode;
use App\Controller\ApiController;
use App\Repository\EpisodeRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use ApiPlatform\Core\DataProvider\ItemDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use ApiPlatform\Core\Exception\ResourceClassNotSupportedException;

final class EpisodeItemDataProvider implements ItemDataProviderInterface, RestrictedDataProviderInterface
{
    /**
     * @var episodeRepository
     */
    private $repository;

    public function __construct(EpisodeRepository $repository)
    {
        $this->repository = $repository;
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return Episode::class === $resourceClass;
    }

    public function getItem(string $resourceClass, $id, string $operationName = null, array $context = [])
    {
        $episode = $this->repository->find($id);

        if ($episode === null) {
            $episodeApi = ApiController::retrieveData('get', 'episode', $id);

            $episode = new JsonResponse([
                'number' => $episodeApi->number,
                'status' => 0,
                'summary' => $episodeApi->summary,
                'runtime' => $episodeApi->runtime,
                'airstamp' => $episodeApi->airstamp,
                'image' => $episodeApi->image,
            ]);

            return $episode;
        }

        return $episode;
    }
}
