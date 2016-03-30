<?php

namespace Alpixel\Bundle\MediaBundle\Services;

use Alpixel\Bundle\MediaBundle\Entity\Media;
use Alpixel\Bundle\MediaBundle\Exception\InvalidMimeTypeException;
use Cocur\Slugify\Slugify;
use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Routing\Generator\UrlGenerator;

class MediaManager
{
    protected $entityManager;
    protected $uploadDir;
    protected $allowedMimetypes;

    use ContainerAwareTrait;

    const SIZE_OF_KIBIOCTET = 1024;
    const OCTET_IN_KO = 1;
    const OCTET_IN_MO = 2;
    const OCTET_IN_GO = 3;
    const OCTET_IN_TO = 4;
    const OCTET_IN_PO = 5;

    public function __construct(EntityManager $entityManager, $uploadDir, $allowedMimetypes)
    {
        $this->entityManager = $entityManager;
        if (substr($uploadDir, -1) !== '/') {
            $uploadDir = $uploadDir.'/';
        }
        $this->uploadDir = $uploadDir;
        $this->allowedMimetypes = $allowedMimetypes;
    }

    /**
     * $current_uri String actual uri of the file
     * $dest_folder String future uri of the file starting from web/upload folder
     * $lifetime DateTime lifetime of the file. If time goes over this limit, the file will be deleted.
     **/
    public function upload(File $file, $dest_folder = '', \DateTime $lifetime = null)
    {
        //preparing dir name
        $dest_folder = date('Ymd').'/'.date('G').'/'.$dest_folder;

        //checking mimetypes
        $mimeTypePassed = false;
        foreach ($this->allowedMimetypes as $mimeType) {
            if (preg_match('@'.$mimeType.'@', $file->getMimeType())) {
                $mimeTypePassed = true;
            }
        }

        if (!$mimeTypePassed) {
            throw new InvalidMimeTypeException('Only following filetypes are allowed : '.implode(', ', $this->allowedMimetypes));
        }

        $fs = new Filesystem();
        if (!$fs->exists($this->uploadDir.$dest_folder)) {
            $fs->mkdir($this->uploadDir.$dest_folder);
        }

        $em = $this->entityManager;
        $media = new Media();
        $media->setMime($file->getMimeType());

        // Sanitizing the filename
        $slugify = new Slugify();
        if ($file instanceof UploadedFile) {
            $filename = $slugify->slugify($file->getClientOriginalName());
        } else {
            $filename = $slugify->slugify($file->getFilename());
        }

        // A media can have a lifetime and will be deleted with the cleanup function
        if (!empty($lifetime)) {
            $media->setLifetime($lifetime);
        }

        // Checking for a media with the same name
        $mediaExists = $this->entityManager->getRepository('AlpixelMediaBundle:Media')->findOneByUri($dest_folder.$filename);
        if (count($mediaExists) === 0) {
            $mediaExists = $fs->exists($this->uploadDir.$dest_folder.$filename);
        }

        // If there's one, we try to generate a new name
        $extension = $file->getExtension();
        if (empty($extension)) {
            $extension = $file->guessExtension();
        }

        if (count($mediaExists) > 0) {
            $filename = basename($filename, '.'.$extension);

            $i = 1;
            do {
                $media->setName($filename.'-'.$i++.'.'.$extension);
                $media->setUri($dest_folder.$media->getName());
                $mediaExists = $this->entityManager->getRepository('AlpixelMediaBundle:Media')->findOneByUri($media->getUri());
            } while (count($mediaExists) > 0);
        } else {
            $media->setName($filename.'.'.$extension);
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

    public function cleanup()
    {
        $medias = $this->entityManager->getRepository('AlpixelMediaBundle:Media')->findExpiredMedias();
        foreach ($medias as $media) {
            $this->delete($media);
        }
    }

    public function delete(Media $media)
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
        } catch (IOException $e) {
        }

        $em->remove($media);
        $em->flush();
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

    public function setAllowedMimeTypes(array $type)
    {
        if ($type !== null) {
            $this->allowedMimetypes = $type;
        }

        return $this;
    }

    public function getAllowedMimeTypes()
    {
        return $this->allowedMimetypes;
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
