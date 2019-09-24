<?php

namespace App\DataPersister;

use App\Entity\Following;
use App\Repository\FollowingRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use ApiPlatform\Core\DataPersister\DataPersisterInterface;

final class FollowingDataPersister implements DataPersisterInterface
{
    private $em;
    private $followingRepository;

    public function __construct(EntityManagerInterface $em, FollowingRepository $followingRepository) {
        $this->em = $em;
        $this->followingRepository = $followingRepository;
    }

    public function supports($data): bool
    {
        return $data instanceof Following;
    }
    
    public function persist($data)
    {
        $this->em->persist($data);
        $this->em->flush();

        return $data;
    }

    public function remove($data)
    {
        if (is_null($data->getSeason()) && is_null($data->getEpisode())) {
            $episodesShowFollowed = $this->followingRepository->findBy(['user' => $data->getUser(), 'tvShow' => $data->getTvShow()]);

            foreach ($episodesShowFollowed as $episode) {
                $this->em->remove($episode);
            }

            $this->remove($data);
            $this->em->flush();
        } else {
            $this->em->remove($data);
            $this->em->flush();
        }
    }
}
