<?php

namespace Alpixel\Bundle\MediaBundle\EventListener;

use Alpixel\Bundle\MediaBundle\Entity\Media;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\Form\FormEvent;

class MediaEvent extends Event
{
    protected $media;

    const POST_SUBMIT = 'alpixel.media.post_submit';

    public function __construct(Media $media)
    {
        $this->media = $media;
    }


    /**
     * Gets the value of media.
     *
     * @return mixed
     */
    public function getMedia()
    {
        return $this->media;
    }
}
