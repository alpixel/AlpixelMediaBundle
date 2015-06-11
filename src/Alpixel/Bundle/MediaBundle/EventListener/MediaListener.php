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
        $event      = $event->getData();
        $formParent = $event->getForm()->getParent();
        $data       = $event->getData();

        if($formParent !== null && $data !== null)
         {
            $manager    = $this->registry->getManager();
            $repository = $manager->getRepository('Alpixel\Bundle\MediaBundle\Entity\Media');
            $media      = $repository->findOneBySecretKey($data);

            if($media !== null) {
                $media->setLifetime(null);
                $event->setData($media);
            }
        }
    }
}
