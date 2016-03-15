<?php

namespace Alpixel\Bundle\MediaBundle\Controller;

use Alpixel\Bundle\MediaBundle\Entity\Media;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class MediaController extends Controller
{
    
    /**
     * @Route("/media/upload/wysiwyg", name="upload_wysiwyg")
     *
     * @Method({"POST"})
     */
    public function uploadFilesWysiwygAction()
    {
        foreach ($this->get('request')->files as $file) {
            $media = $this->get('media')->upload($file, $this->get('request')->get('folder'), null);
        }
        
        $file_uploaded = $this->get('media')->getSecretPath($media);

        return $this->render('AlpixelMediaBundle:admin:blocks/upload_wysiwyg.html.twig', array(
            'file_uploaded' => $file_uploaded,
        ));
    }
    
    /**
     * @Route("/media/upload", name="upload")
     *
     * @Method({"POST"})
     */
    public function uploadAction()
    {
        $returnData = [];

        if ($this->get('request')->get('lifetime') !== null) {
            $lifetime = new \DateTime($this->get('request')->get('lifetime'));
        }

        if (empty($lifetime) || $lifetime == new \DateTime('now')) {
            $lifetime = new \DateTime('+6 hours');
        }

        $mediaPreview = $this->container->get('twig.extension.media_preview_extension');
        foreach ($this->get('request')->files as $files) {
            if (!is_array($files)) {
                $files = [$files];
            }
            foreach ($files as $file) {
                $media = $this->get('alpixel_media.manager')->upload($file, $this->get('request')->get('folder'), $lifetime);
                $path = $mediaPreview->generatePathFromSecretKey($media->getSecretKey());
                $returnData[] = [
                  'id'   => $media->getSecretKey(),
                  'path' => $path,
                  'name' => $media->getName(),
              ];
            }
        }

        return new JsonResponse($returnData);
    }

    /**
     * @Route("/media/download/{id}-{name}", name="media_download_public")
     * @Route("/media/download/{filter}/{id}-{name}", name="media_download_public_filters")
     * @Route("/media/download/{secretKey}/{filter}", name="media_download_private")
     *
     * @Method({"GET"})
     */
    public function downloadMediaAction(Media $media)
    {
        $response = new Response();
        $response->setContent(file_get_contents($this->get('alpixel_media.manager')->getAbsolutePath($media)));
        $response->headers->set('Content-Type', 'application/force-download');
        $response->headers->set('Content-disposition', 'filename='.$media->getName());

        return $response;
    }

    /**
     * @Route("/media/{id}-{name}", name="media_show_public")
     * @Route("/media/{filter}/{id}-{name}", name="media_show_public_filters")
     * @Route("/media/{secretKey}/{filter}", name="media_show_private")
     *
     * @Method({"GET"})
     */
    public function showMediaAction(Media $media, $filter = null)
    {
        $response = new Response();
        $lastModified = new \DateTime('now');

        //Checking if it is an image or not
        $src = $this->get('alpixel_media.manager')->getAbsolutePath($media);
        $isImage = @getimagesize($src);

        if ($isImage) {
            $response->headers->set('Content-disposition', 'inline;filename='.$media->getName());
            if (!empty($filter) && $isImage) {
                $src = $this->get('alpixel_media.manager')->getAbsolutePath($media, $filter);
                $dataManager = $this->get('liip_imagine.data.manager'); // the data manager service
                $filterManager = $this->get('liip_imagine.filter.manager'); // the filter manager service
                $uploadDir = $this->get('alpixel_media.manager')->getUploadDir($filter);

                if (!is_file($src)) {
                    $fs = new Filesystem();
                    if (!$fs->exists($uploadDir.$media->getFolder())) {
                        $fs->mkdir($uploadDir.$media->getFolder());
                    }

                    $path = 'upload/'.$media->getUri();

                     // find the image and determine its type
                    $image = $dataManager->find($filter, $path);

                    // run the filter
                    $responseData = $filterManager->applyFilter($image, $filter);
                    $data = $responseData->getContent();
                    file_put_contents($uploadDir.$media->getUri(), $data);
                } else {
                    $data = file_get_contents($src);
                    $lastModified->setTimestamp(filemtime($src));
                }
            } else {
                $src = $this->get('alpixel_media.manager')->getAbsolutePath($media);
                $lastModified->setTimestamp(filemtime($src));
                $data = file_get_contents($src);
            }
        } else {
            $lastModified->setTimestamp(filemtime($src));
            $data = file_get_contents($src);
            $response->headers->set('Content-disposition', 'attachment;filename='.basename($media->getUri()));
        }

        $response->setLastModified($lastModified);
        $response->setPublic();
        $response->headers->set('Content-Type', $media->getMime());

        if ($response->isNotModified($this->get('request'))) {
            return $response;
        }

        $response->setContent($data);

        return $response;
    }
}
