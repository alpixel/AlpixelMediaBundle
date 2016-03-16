<?php

namespace Alpixel\Bundle\MediaBundle\DataFixtures\ORM;

use Nelmio\Alice\ProcessorInterface;

class MediaProcessor implements ProcessorInterface
{
    public function __construct()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function preProcess($object)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function postProcess($object)
    {
    }
}
