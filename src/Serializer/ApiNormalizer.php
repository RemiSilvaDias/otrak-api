<?php

namespace App\Serializer;

use App\Entity\Show;
use App\Utils\ApiFetcher;
use App\Entity\Following;
use App\Repository\FollowingRepository;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

final class ApiNormalizer implements NormalizerInterface, DenormalizerInterface, SerializerAwareInterface
{
    private $decorated;
    private $apiFetcher;
    private $followingRepository;

    public function __construct(NormalizerInterface $decorated, ApiFetcher $apiFetcher, FollowingRepository $followingRepository)
    {
        if (!$decorated instanceof DenormalizerInterface) {
            throw new \InvalidArgumentException(sprintf('The decorated normalizer must implement the %s.', DenormalizerInterface::class));
        }

        $this->decorated = $decorated;
        $this->apiFetcher = $apiFetcher;
        $this->followingRepository = $followingRepository;
    }

    public function supportsNormalization($data, $format = null)
    {
        return $this->decorated->supportsNormalization($data, $format);
    }

    public function normalize($object, $format = null, array $context = [])
    {
        $data = $this->decorated->normalize($object, $format, $context);

        /* Add the latest episode follow in the show information on the follow reponse */
        if ($object instanceof Following) {
            if (isset($context['subresource_operation_name']) && $context['subresource_operation_name'] == 'api_users_followings_get_subresource' && (is_null($object->getSeason()) && is_null($object->getEpisode()))) {
                $latestFollow = $this->followingRepository->findOneBy(['user' => $object->getUser(), 'tvShow' => $object->getTvShow()], ['id' => 'DESC']);
                $latestFollowSeason = 0;
                $latestFollowEpisode = 0;
                
                if (null !== $latestFollow->getEpisode()) {
                    $latestFollowSeason = $latestFollow->getEpisode()->getSeason()->getNumber();
                    $latestFollowEpisode = $latestFollow->getEpisode()->getNumber();
                }

                $data['latestFollowSeason'] = $latestFollowSeason;
                $data['latestFollowEpisode'] = $latestFollowEpisode;
                $data['idTvmaze'] = $object->getTvShow()->getIdTvmaze();
            } else if (!is_null($object->getSeason()) && !is_null($object->getEpisode())) {
                $showFollow = $this->followingRepository->findOneBy(['user' => $object->getUser(), 'tvShow' => $object->getTvShow(), 'season' => null, 'episode' => null]);

                $showFollowId = $showFollow->getId();

                $data['showFollowId'] = $showFollowId;
            }
        }

        /* Return the cast to the response when getting the detail of a specific show
        Return the number of seasons and episodes of a specific show */
        if ($object instanceof Show) {
            if (is_array($data)) {
                if ($context['operation_type'] != 'subresource') {
                    $showApi = $this->apiFetcher->retrieveData('get', 'showFull', $object->getIdTvmaze());

                    $cast = null;
                    
                    if (!is_null($showApi->_embedded->cast)) $cast = $showApi->_embedded->cast;
                    $data['cast'] = $cast;
                }

                $nbSeasons = 0;
                $nbEpisodes = 0;

                if ($object->getSeasons()->count() > 0) {
                    foreach($object->getSeasons() as $season) {
                        $nbSeasons += 1;
                        $nbEpisodes += $season->getEpisodes()->count();
                    }
                }

                $data['nbSeasons'] = $nbSeasons;
                $data['nbEpisodes'] = $nbEpisodes;
            }
        }

        return $data;
    }

    public function supportsDenormalization($data, $type, $format = null)
    {
        return $this->decorated->supportsDenormalization($data, $type, $format);
    }

    public function denormalize($data, $class, $format = null, array $context = [])
    {
        return $this->decorated->denormalize($data, $class, $format, $context);
    }

    public function setSerializer(SerializerInterface $serializer)
    {
        if($this->decorated instanceof SerializerAwareInterface) {
            $this->decorated->setSerializer($serializer);
        }
    }
}
