<?php

namespace App\EventListener;

use App\Entity\Show;
use App\Utils\Slugger;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;


class Slugifier {

    private $slugger;

    public function __construct(Slugger $slugger)
    {
        $this->slugger = $slugger;
    }
    
    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();

        if (!$entity instanceof Show) {
            return;
        }

        $slug = $this->slugger->slugify($entity->getName());

        $entity->setSlug($slug);
        
    }

    public function preUpdate(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();

        if (!$entity instanceof Show) {
            return;
        }

        $slug = $this->slugger->slugify($entity->getName());

        $entity->setSlug($slug);
        
    }

}
