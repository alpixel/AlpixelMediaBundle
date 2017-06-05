<?php

namespace Alpixel\Bundle\MediaBundle\DataFixtures\ORM;

use Alpixel\Bundle\MediaBundle\Services\MediaManager;
use Faker\Provider\Base as BaseProvider;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\File\File;

class MediaProvider extends BaseProvider
{
    protected $mediaManager;

    public function __construct(MediaManager $mediaManager)
    {
        $this->mediaManager = $mediaManager;
    }

    /** @deprecated */
    public function randomMedia($width = null, $height = null, $type = 'color')
    {
        return $this->randomImage($width, $height, $type);
    }

    public function randomImage($width = null, $height = null, $type = 'color')
    {
        do {
            $dimensions = $this->fetchDimensions($width, $height);
            $file = $this->fetchFromCache($dimensions['w'] . '-' . $dimensions['h']);
            if ($file === null) {
                $file = $this->downloadMedia($this->generateUrl($dimensions, $type), 'jpg');
                $this->storeInCache($dimensions['w'] . '-' . $dimensions['h'], $file);
            }
        } while (!preg_match('@^image/@', $file->getMimeType()));

        $media = $this->mediaManager->upload($file);

        return $media;
    }

    public function randomFile($fileType)
    {
        $file = $this->fetchFromCache("file-" . $fileType);
        if ($file === null) {
            switch ($fileType) {
                case "pdf":
                default:
                    $file = $this->downloadMedia("https://symfony.com/pdf/Symfony_book_master.pdf", $fileType);
                    break;
            }
        }
        $this->storeInCache("file-" . $fileType, $file);
        $media = $this->mediaManager->upload($file);

        return $media;
    }

    protected function fetchDimensions($width = null, $height = null)
    {
        if ($width === null && $height !== null) {
            $width = round($height * 4 / 3);
        } elseif ($width !== null && $height === null) {
            $height = round($width * 3 / 4);
        } else {
            $aWidth = [800, 1200, 1600];
            $width = array_rand($aWidth, 1);
            $width = $aWidth[$width];
            $height = round($width * 3 / 4);
        }

        return ['w' => $width, 'h' => $height];
    }

    protected function generateUrl($dimensions, $type = 'color')
    {
        $url = 'http://loremflickr.com/';

        if ($type !== 'color') {
            $url .= 'g/';
        }

        $url .= $dimensions['w'] . '/' . $dimensions['h'];

        $category = ['abstract', 'city', 'nature', 'moutains'];
        $url .= '/' . $category[array_rand($category, 1)] . '/';

        return $url;
    }

    protected function downloadMedia($url, $ext)
    {
        $filepath = sys_get_temp_dir() . '/' . uniqid() . '.' . $ext;
        $ch = curl_init($url);
        $fp = fopen($filepath, 'wb');
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_exec($ch);
        curl_close($ch);
        fclose($fp);

        return new File($filepath, 'random');
    }

    protected function fetchFromCache($key)
    {
        $fs = new Filesystem();
        $cacheDir = $_SERVER['HOME'] . '/.symfony/media';
        if (!$fs->exists($cacheDir)) {
            $fs->mkdir($cacheDir, 0777);
        } else {
            $cacheDir .= '/' . $key;
            if (!$fs->exists($cacheDir)) {
                $fs->mkdir($cacheDir, 0777);
            } else {
                $finder = new Finder();
                $files = $finder->in($cacheDir . '/')->files();
                if (strrpos($key, "file-") !== false || $files->count() === 3) {
                    $iterator = $finder->getIterator();
                    $iterator->rewind();
                    for ($i = 0; $i < rand(0, 2); $i++) {
                        $iterator->next();
                    }
                    $file = new File($iterator->current());
                    $fs->copy($file->getRealPath(), sys_get_temp_dir() . '/' . $file->getFilename());

                    return new File(sys_get_temp_dir() . '/' . $file->getFilename());
                }
            }
        }
    }

    protected function storeInCache($key, File $file)
    {
        $fs = new Filesystem();
        $cacheDir = $_SERVER['HOME'] . '/.symfony/media/' . $key;
        if (!$fs->exists($cacheDir)) {
            $fs->mkdir($cacheDir, 0777);
        }
        $fs->copy($file->getRealPath(), $cacheDir . '/' . $file->getFilename());
    }
}
