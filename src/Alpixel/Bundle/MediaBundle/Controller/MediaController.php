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
     * @Route("/media/upload", name="upload")
     *
     * @Method({"POST"})
     */
    public function uploadAction()
    {
        $returnData    = array();

        if ($this->get('request')->get('lifetime') !== null) {
            $lifetime = new \DateTime($this->get('request')->get('lifetime'));
        }

        if (empty($lifetime) || $lifetime == new \DateTime('now')) {
            $lifetime = new \DateTime("+6 hours");
        }

        $mediaPreview = $this->container->get('twig.extension.media_preview_extension');
        foreach ($this->get('request')->files as $files) {
            if(!is_array($files))
              $files = array($files);
            foreach($files as $file) {
              $media   = $this->get('media')->upload($file, $this->get('request')->get('folder'), $lifetime);
              $path    = $mediaPreview->generatePathFromSecretKey($media->getSecretKey());
              $returnData[] = array(
                  'id'   => $media->getSecretKey(),
                  'path' => $path,
                  'name' => $media->getName(),
              );
            }
        }

        return new JsonResponse($returnData);
    }

    /**
     * @Route("/media/download/{secretKey}", name="media_download")
     *
     * @Method({"GET"})
     */
    public function downloadMediaAction(Media $media)
    {
        $response = new Response();
        $response->setContent(file_get_contents($this->get('media')->getAbsolutePath($media)));
        $response->headers->set('Content-Type', 'application/force-download');
        $response->headers->set('Content-disposition', 'filename='.$media->getName());

        return $response;
    }

    /**
     * @Route("/media/delete/{secretKey}", name="media_delete")
     *
     * @Method({"POST"})
     */
    public function deleteMediaAction(Media $media)
    {
        $this->get('media')->delete($media);

        return new Response();
    }

    /**
     * @Route("/media/{secretKey}/{filter}", name="media_show")
     *
     * @Method({"GET"})
     */
    public function showMediaAction(Media $media, $filter = null)
    {
      $response = new Response();

      //Checking if it is an image or not
      $src     = $this->get('media')->getAbsolutePath($media);
      $isImage = @getimagesize($src);

      if($isImage) {
        $response->headers->set('Content-disposition', 'inline;filename='.$media->getName());
        if (!empty($filter) && $isImage) {

          $src           = $this->get('media')->getAbsolutePath($media, $filter);
          $dataManager   = $this->get('liip_imagine.data.manager');    // the data manager service
          $filterManager = $this->get('liip_imagine.filter.manager');// the filter manager service

          $uploadDir = $this->get('media')->getUploadDir($filter);

          if (!is_file($src)) {
            $fs = new Filesystem();
            if (!$fs->exists($uploadDir.$media->getFolder())) {
              $fs->mkdir($uploadDir.$media->getFolder());
            }

            $path         = 'upload/'.$media->getUri();
            $image        = $dataManager->find($filter, $path);                    // find the image and determine its type
            $responseData = $filterManager->applyFilter($image, $filter);         // run the filter
            $data         = $responseData->getContent();                              // get the image from the response
            file_put_contents($uploadDir.$media->getUri(), $data);
          } else {
            $data = file_get_contents($src);
          }

        } else {
          $src  = $this->get('media')->getAbsolutePath($media);
          $data = file_get_contents($src);
        }
      } else {
        $data = file_get_contents($src);
        $response->headers->set('Content-disposition', 'attachment;filename='.basename($media->getUri()));
      }

      $date = new \DateTime();
      $date->modify('+1 month');
      $response->setExpires($date);
      $response->setPublic();
      $response->isNotModified($this->get('request'));
      $response->setContent($data);
      $response->setETag(md5($data));

      $response->headers->set('Content-Type', $media->getMime());

      return $response;
    }

}
