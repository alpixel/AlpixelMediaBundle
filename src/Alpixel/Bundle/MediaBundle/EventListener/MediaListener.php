<?php

namespace Alpixel\Bundle\MediaBundle\EventListener;

use Alpixel\Bundle\MediaBundle\EventListener\MediaEvent;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Routing\RouterInterface;

class MediaListener
{
    protected $entityManager;

    public function __construct(EntityManager $entityManager) {
        $this->entityManager = $entityManager;
    }

    public function onPostSubmit(MediaEvent $event)
    {
        $media = $event->getMedia();

        if($media !== null) {
            $media->setLifetime(null);
            $this->entityManager->persist($media);
        }
    }
}
