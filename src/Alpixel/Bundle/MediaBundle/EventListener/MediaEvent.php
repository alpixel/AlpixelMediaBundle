<?php

namespace Alpixel\Bundle\MediaBundle\EventListener;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\Form\FormEvent;

class MediaEvent extends Event
{
    protected $form;

    const POST_SUBMIT = 'alpixel.media.post_submit';

    public function __construct(FormEvent $form)
    {
        $this->form = $form;
    }

    public function getData()
    {
        return $this->form;
    }
}
