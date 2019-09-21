<?php

namespace App\Serializer;

use App\Entity\Show;
use App\Controller\ApiController;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

final class ApiNormalizer implements NormalizerInterface, DenormalizerInterface, SerializerAwareInterface
{
    private $decorated;

    public function __construct(NormalizerInterface $decorated)
    {
        if (!$decorated instanceof DenormalizerInterface) {
            throw new \InvalidArgumentException(sprintf('The decorated normalizer must implement the %s.', DenormalizerInterface::class));
        }

        $this->decorated = $decorated;
    }

    public function supportsNormalization($data, $format = null)
    {
        return $this->decorated->supportsNormalization($data, $format);
    }

    public function normalize($object, $format = null, array $context = [])
    {
        $data = $this->decorated->normalize($object, $format, $context);

        if($object instanceof Show) {
            if (is_array($data)) {
                if ($context['operation_type'] != 'subresource') {
                    $showApi = ApiController::retrieveData('get', 'showFull', $object->getIdTvmaze());

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
