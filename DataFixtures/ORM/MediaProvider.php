<?php

namespace Alpixel\Bundle\MediaBundle\DataFixtures\ORM;

use Alpixel\Bundle\MediaBundle\Services\MediaManager;
use Faker\Provider\Base as BaseProvider;
use Symfony\Component\HttpFoundation\File\File;

class MediaProvider extends BaseProvider
{
    protected $mediaManager;

    public function __construct(MediaManager $mediaManager)
    {
        $this->mediaManager = $mediaManager;
    }

    public function randomMedia($width = null, $height = null, $type = 'color')
    {
        do {
            $file = $this->downloadMedia($this->generateUrl($width, $height, $type));
        } while (!preg_match('@^image/@', $file->getMimeType()));

        $media = $this->mediaManager->upload($file);

        return $media;
    }

    protected function generateUrl($width = null, $height = null, $type = 'color')
    {
        $url = 'http://loremflickr.com/';

        if ($type !== 'color') {
            $url .= 'g/';
        }

        if ($width === null && $height !== null) {
            $width = round($height * 4 / 3);
        } elseif ($width !== null && $height === null) {
            $height = round($width * 3 / 4);
        } else {
            $aWidth = [800, 1200, 1600];
            $width = array_rand($aValues, 1);
            $width = $aValues[$width];
            $height = round($width * 3 / 4);
        }

        $url .= $width.'/'.$height;

        $category = ['abstract', 'city', 'nature', 'moutains'];
        $url .= '/'.$category[array_rand($category, 1)].'/';

        return $url;
    }

    protected function downloadMedia($url)
    {
        $filepath = sys_get_temp_dir().'/tmp.jpg';
        $ch = curl_init($url);
        $fp = fopen($filepath, 'wb');
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_exec($ch);
        curl_close($ch);
        fclose($fp);

        return new File($filepath, 'random');
    }
}
