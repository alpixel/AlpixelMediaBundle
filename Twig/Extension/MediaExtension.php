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

    public function getFunctions()
    {
        return [];
    }

    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('media_url', [$this, 'generateMediaUrl']),
        ];
    }

    public function generateMediaUrl(Media $media, $options = [])
    {
        return $this->mediaManager->generateUrl($media, $options);
    }

}
