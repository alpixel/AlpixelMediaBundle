<?php

namespace Alpixel\Bundle\MediaBundle\Twig\Extension;

use Alpixel\Bundle\MediaBundle\Entity\Media;
use Alpixel\Bundle\MediaBundle\Services\MediaManager;

class MediaExtension extends \Twig_Extension
{
    protected $mediaManager;

    public function __construct(MediaManager $mm)
    {
        $this->mediaManager = $mm;
    }

    public function getName()
    {
        return 'secretImage';
    }

    public function getFilters()
    {
        return [
            'secret_image' => new \Twig_Filter_Method($this, 'generateSecretImage', []),
        ];
    }

    public function generateSecretImage(Media $media, $field)
    {
    }
}
