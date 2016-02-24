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
        return 'alpixel_media';
    }

    public function getFilters()
    {
        return [
            'media_url' => new \Twig_Filter_Method($this, 'generateMediaUrl', []),
        ];
    }

    public function generateMediaUrl(Media $media, $options = array())
    {
        return $this->mediaManager->generateUrl($media, $options);
    }
}
