<?php

namespace Alpixel\Bundle\MediaBundle\Services;

use Alpixel\Bundle\MediaBundle\Entity\Media;
use Alpixel\Bundle\MediaBundle\Exception\InvalidMimeTypeException;
use Cocur\Slugify\Slugify;
use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\File\Exception\UploadException;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Routing\Generator\UrlGenerator;

/**
 * @author Benjamin HUBERT <benjamin@alpixel.fr>
 */
class MediaManager
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $entityManager;
    protected $uploadDir;
    protected $uploadConfiguration;

    use ContainerAwareTrait;

    const SIZE_OF_KIBIOCTET = 1024;
    const OCTET_IN_KO = 1;
    const OCTET_IN_MO = 2;
    const OCTET_IN_GO = 3;
    const OCTET_IN_TO = 4;
    const OCTET_IN_PO = 5;

    public function __construct(EntityManager $entityManager, $uploadDir, $uploadConfiguration)
    {
        $this->entityManager = $entityManager;
        if (substr($uploadDir, -1) !== '/') {
            $uploadDir = $uploadDir.'/';
        }
        $this->uploadDir = $uploadDir;
        $this->uploadConfiguration = $uploadConfiguration;
    }


    /**
     * @param \Symfony\Component\HttpFoundation\File\File $file
     * @param string $dest_folder
     * @param \DateTime|null $lifetime
     * @param String $uploadConfiguration
     * @return \Alpixel\Bundle\MediaBundle\Entity\Media
     */
    public function upload(File $file, $dest_folder = '', \DateTime $lifetime = null, $uploadConfiguration = null)
    {
        if ($file instanceof UploadedFile) {
            if ($file->getError() !== null && $file->getError() !== 0) {
                throw new UploadException($file->getErrorMessage());
            }
        }

        //preparing dir name
        $dest_folder = date('Ymd').'/'.date('G').'/'.$dest_folder;

        //checking mimetypes
        if ($uploadConfiguration !== null) {
            $allowedMimeTypes = $this->uploadConfiguration[$uploadConfiguration]['allowed_mimetypes'];

            $mimeTypePassed = false;
            foreach ($allowedMimeTypes as $mimeType) {
                if (preg_match('@'.$mimeType.'@', $file->getMimeType())) {
                    $mimeTypePassed = true;
                }
            }

            if (!$mimeTypePassed) {
                throw new InvalidMimeTypeException(
                    'Only following filetypes are allowed : '.implode(', ', $allowedMimeTypes)
                );
            }
        }

        $fs = new Filesystem();
        if (!$fs->exists($this->uploadDir.$dest_folder)) {
            $fs->mkdir($this->uploadDir.$dest_folder);
        }

        $em = $this->entityManager;
        $media = new Media();
        $media->setMime($file->getMimeType());

        // If there's one, we try to generate a new name
        $extension = $file->getExtension();

        // Sanitizing the filename
        $slugify = new Slugify();
        if ($file instanceof UploadedFile) {
            if (empty($extension)) {
                $extension = $file->getClientOriginalExtension();
                if (empty($extension)) {
                    $extension = $file->guessClientExtension();
                }
            }
            $filename = $slugify->slugify(basename($file->getClientOriginalName(), $extension)).'.'.$extension;
        } else {
            if (empty($extension)) {
                $extension = $file->guessClientExtension();
            }
            $filename = $slugify->slugify(basename($file->getFilename(), $extension)).'.'.$extension;
        }

        // A media can have a lifetime and will be deleted with the cleanup function
        if (!empty($lifetime)) {
            $media->setLifetime($lifetime);
        }

        // Checking for a media with the same name
        $mediaExists = $this->entityManager->getRepository('AlpixelMediaBundle:Media')->findOneByUri(
            $dest_folder.$filename
        );
        $mediaExists = (count($mediaExists) > 0);
        if ($mediaExists === false) {
            $mediaExists = $fs->exists($this->uploadDir.$dest_folder.$filename);
        }

        if ($mediaExists === true) {
            $filename = basename($filename, '.'.$extension);
            $i = 1;
            do {
                $media->setName($filename.'-'.$i++.'.'.$extension);
                $media->setUri($dest_folder.$media->getName());
                $mediaExists = $this->entityManager->getRepository('AlpixelMediaBundle:Media')->findOneByUri(
                    $media->getUri()
                );
                $mediaExists = (count($mediaExists) > 0);
                if ($mediaExists === false) {
                    $mediaExists = $fs->exists($this->uploadDir.$dest_folder.$filename);
                }
            } while ($mediaExists === true);
        } else {
            $media->setName($filename);
            $media->setUri($dest_folder.$media->getName());
        }

        $file->move($this->uploadDir.$dest_folder, $media->getName());
        chmod($this->uploadDir.$dest_folder.$media->getName(), 0664);

        // Getting the salt defined in parameters.yml
        $secret = $this->container->getParameter('secret');
        $media->setSecretKey(hash('sha256', $secret.$media->getName().$media->getUri()));

        $em->persist($media);
        $em->flush();

        return $media;
    }

    /**
     * @return int
     */
    public function cleanup()
    {
        $expired = 0;

        $mediaRepo = $this->entityManager->getRepository('AlpixelMediaBundle:Media');

        //Cleanup expired files
        $medias = $mediaRepo->findExpiredMedias();
        $expired += count($medias);
        foreach ($medias as $media) {
            $this->delete($media);
        }

        //Cleanup files without any database record
        $fs = new Filesystem();
        $finder = new Finder();
        $files = $finder->in($this->uploadDir)->exclude('filters')->files();

        foreach ($files as $file) {
            $path = $file->getRelativePathname();
            $media = $mediaRepo->findOneByUri($path);
            if ($media === null) {
                try {
                    $fs->remove($file);
                    $expired++;
                } catch (IOException $e) {
                }
            }
        }

        return $expired;
    }

    public function delete(Media $media, $flush = true)
    {
        $em = $this->entityManager;
        $fs = new Filesystem();

        $file_path = $this->uploadDir.$media->getUri();

        try {
            $file = new File($file_path);
            if ($file->isFile() && $file->isWritable()) {
                $fs->remove($file_path);
            }
        } catch (FileNotFoundException $e) {
        }

        $em->remove($media);
        if ($flush) {
            $em->flush();
        }
    }

    public function getUploadDir($filter = null)
    {
        if (!empty($filter)) {
            return $this->uploadDir.'filters/'.$filter.'/';
        }

        return $this->uploadDir;
    }

    public function getWebPath(Media $media)
    {
        $request = $this->container->get('request');
        $dir = $request->getSchemeAndHttpHost().$request->getBaseUrl().'/';

        return $dir.$media->getUri();
    }

    public function getAbsolutePath(Media $media, $filter = null)
    {
        $imgSrc = $this->uploadDir;
        if (!empty($filter)) {
            return $imgSrc.'filters/'.$filter.'/'.$media->getUri();
        } else {
            return $imgSrc.$media->getUri();
        }
    }

    public function generateUrl(Media $media, $options)
    {
        $defaultOptions = [
            'public'   => true,
            'action'   => 'show',
            'filter'   => null,
            'absolute' => false,
        ];

        $options = array_merge($defaultOptions, $options);
        $params = [];

        $routeName = 'media_';
        if ($options['action'] === 'download') {
            $routeName .= 'download_';
        } else {
            $routeName .= 'show_';
        }

        if ($options['public'] === true) {
            $routeName .= 'public';
            $params['id'] = $media->getId();
            $params['name'] = $media->getName();
        } else {
            $routeName .= 'private';
            $params['secretKey'] = $media->getSecretKey();
        }

        if ($options['filter'] !== null) {
            if ($options['public'] === true) {
                $routeName .= '_filters';
            }
            $params['filter'] = $options['filter'];
        }

        $container = $this->container;
        $router = $container->get('router');

        if ($options['absolute']) {
            $referenceType = UrlGenerator::ABSOLUTE_URL;
        } else {
            $referenceType = UrlGenerator::ABSOLUTE_PATH;
        }

        return $router->generate($routeName, $params, $referenceType);
    }

    public function findFromSecret($secret)
    {
        return $this->entityManager->getRepository('AlpixelMediaBundle:Media')->findOneBySecretKey($secret);
    }

    public function convertOctetIn($size, $convert)
    {
        if ($convert > 0) {
            $size = ($size / self::SIZE_OF_KIBIOCTET) * 1;

            return $this->convertOctetIn($size, $convert - 1);
        }

        return $size;
    }
}
