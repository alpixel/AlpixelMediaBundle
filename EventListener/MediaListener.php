<?php

namespace Alpixel\Bundle\MediaBundle\EventListener;

use Doctrine\ORM\EntityManager;

class MediaListener
{
    protected $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function onPostSubmit(MediaEvent $event)
    {
        $media = $event->getMedia();

        if ($media !== null) {
            $media->setLifetime(null);
            $this->entityManager->persist($media);
        }
    }
}
