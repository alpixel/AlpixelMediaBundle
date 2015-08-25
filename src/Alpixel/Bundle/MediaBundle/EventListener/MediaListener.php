<?php

namespace Alpixel\Bundle\MediaBundle\EventListener;

use Alpixel\Bundle\MediaBundle\EventListener\MediaEvent;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Routing\RouterInterface;

class MediaListener
{
    protected $registry;

    public function __construct(RegistryInterface $registry) {
        $this->registry = $registry;
    }

    public function onPostSubmit(MediaEvent $event)
    {
        $media      = $event->getMedia();

        if($media !== null) {
            $media->setLifetime(null);
            $this->registry->getManager()->persist($media);
        }
    }
}
