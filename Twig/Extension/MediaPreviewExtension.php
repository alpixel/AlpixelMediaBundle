<?php

namespace Alpixel\Bundle\MediaBundle\Twig\Extension;

use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\RequestStack;

class MediaPreviewExtension extends \Twig_Extension
{
    protected $entityManager;
    protected $request;
    protected $previewIcons;

    public function __construct(RequestStack $requestStack, EntityManager $entityManager, $previewIcons)
    {
        $this->request = $requestStack->getCurrentRequest();
        $this->entityManager = $entityManager;
        $this->previewIcon = $previewIcons;
    }

    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('previewIcon', [$this, 'previewIconFilter'], [
                'is_safe'           => ['html'],
                'needs_environment' => true,
                ]
            ),
        ];
    }

    public function previewIconFilter(\Twig_Environment $twig, $secretKey = '')
    {
        if ($secretKey == '') {
            return '';
        }

        $mimeType = $this->getMimeType($secretKey);
        $icon = '';
        $link = '';

        if (preg_match('/^image/', $mimeType) === 0) {
            $icon = $this->getIcon($mimeType);
            $link = $this->generatePath(true, $icon);
        } else {
            $link = $this->generatePath(false, $secretKey);
        }

        return $twig->render('AlpixelMediaBundle:Form:blocks/show_icon.html.twig', [
            'link'      => $link,
            'icon'      => $icon,
            'secretKey' => $secretKey,
        ]);
    }

    protected function getIcon($mimeType)
    {
        $explMime = explode('/', $mimeType);
        $mime = (isset($explMime[1])) ? $explMime[1] : '';

        return (array_key_exists($mime, $this->previewIcon)) ? $this->previewIcon[$mime] : $this->previewIcon['unknown'];
    }

    protected function generatePath($isIcon, $str)
    {
        if ($isIcon === true) {
            return $this->request->getSchemeAndHttpHost().$this->request->getBasePath().'/bundles/alpixelmedia/images/'.$str;
        }

        return $this->request->getSchemeAndHttpHost().$this->request->getBaseUrl().'/media/'.$str.'/admin';
    }

    public function generatePathFromSecretKey($secretKey)
    {
        if ($secretKey == '') {
            return '';
        }

        $mimeType = $this->getMimeType($secretKey);

        if (preg_match('/^image/', $mimeType) === 0) {
            $icon = $this->getIcon($mimeType);

            return $this->generatePath(true, $icon);
        }

        return $this->generatePath(false, $secretKey);
    }

    protected function getMimeType($secretKey)
    {
        $mediaObject = $this->entityManager->getRepository('AlpixelMediaBundle:Media')->findOneBySecretKey($secretKey);

        return ($mediaObject !== null) ? $mediaObject->getMime() : null;
    }

    public function getName()
    {
        return 'alpixel_media_twig_media_preview_extension';
    }
}
