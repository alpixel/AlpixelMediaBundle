<?php

namespace Alpixel\Bundle\MediaBundle\Twig\Extension;

use Symfony\Component\HttpFoundation\RequestStack;
use Doctrine\ORM\EntityManager;

class MediaPreviewExtension extends \Twig_Extension
{
    protected $entityManager;
    protected $requestStack;
    protected $previewIcons;

    public function __construct(RequestStack $requestStack, EntityManager $entityManager, $previewIcons)
    {
        $this->requestStack  = $requestStack->getCurrentRequest();
        $this->entityManager = $entityManager;
        $this->previewIcon   = $previewIcons;
    }

    public function getFilters()
    {
        return array(
            'previewIcon' => new \Twig_Filter_Method($this, 'previewIconFilter', array('is_safe' => array('html')))
        );
    }

    public function previewIconFilter($secretKey = '')
    {
        if($secretKey == '')
            return '';

        $mimeType = $this->getMimeType($secretKey);

        if(preg_match('/^image/', $mimeType) === 0) {
            $icon = $this->getIcon($mimeType);
            $link = $this->generatePath(true, $icon);
            return '<img src="'.$link.'" /><div id="preview-template" style="display: none;"></div>';
        }

        $link = $this->generatePath(false, $secretKey);
        return '<img src="'.$link.'" alt="" data-key="'.$secretKey.'" />';

    }

    protected function getIcon($mimeType)
    {
        $explMime = explode('/', $mimeType);
        $mime     = (isset($explMime[1])) ? $explMime[1] : '';
        return (array_key_exists($mime, $this->previewIcon)) ? $this->previewIcon[$mime] : $this->previewIcon['unknown'];
    }

    protected function generatePath($isIcon, $str)
    {
        if($isIcon === true) {
            return $this->requestStack->getSchemeAndHttpHost().$this->requestStack->getBasePath().'/bundles/media/images/'.$str;
        }

        return $this->requestStack->getSchemeAndHttpHost().$this->requestStack->getBaseUrl().'/media/'.$str.'/admin';
    }

    public function generatePathFromSecretKey($secretKey)
    {
         if($secretKey == '')
            return '';

        $mimeType = $this->getMimeType($secretKey);

        if(preg_match('/^image/', $mimeType) === 0) {
            $icon = $this->getIcon($mimeType);
            return $this->generatePath(true, $icon);
        }

        return $this->generatePath(false, $secretKey);
    }

    protected function getMimeType($secretKey)
    {
        $mediaObject = $this->entityManager->getRepository('MediaBundle:Media')->findOneBySecretKey($secretKey);

        return ($mediaObject !== null) ? $mediaObject->getMime() : null;
    }

    public function getName()
    {
        return 'alpixel_media_twig_media_preview_extension';
    }
}
