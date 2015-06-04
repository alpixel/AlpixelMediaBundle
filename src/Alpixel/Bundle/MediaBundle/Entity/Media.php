<?php

namespace Alpixel\Bundle\MediaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * Media.
 *
 * @ORM\Table(name="media")
 * @ORM\Entity(repositoryClass="Alpixel\Bundle\MediaBundle\Repository\MediaRepository")
 * @Serializer\ExclusionPolicy("all")
 */
class Media
{
    /**
     * @var integer
     *
     * @ORM\Column(name="media_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     */
    protected $name;

    /**
     * @var string
     *
     * @ORM\Column(name="uri", type="string", length=255, nullable=false, unique=true)
     */
    protected $uri;

    /**
     * @var string
     *
     * @Serializer\Expose
     * @ORM\Column(name="secret_key", type="string", length=64, nullable=false, unique=true)
     */
    protected $secretKey;

    /**
     * @var string
     *
     * @ORM\Column(name="mime", type="string", length=100, nullable=true)
     */
    protected $mime;

    /**
     * @var string
     *
     * @ORM\Column(name="lifetime", type="datetime", length=255, nullable=true)
     */
    protected $lifetime;

    public function __construct()
    {
    }

    public function getFolder()
    {
        return str_replace(basename($this->uri), '', $this->uri);
    }

    /**
     * Gets the value of id.
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Sets the value of id.
     *
     * @param integer $id the id
     *
     * @return self
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Gets the value of name.
     *
     * @return string $name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Sets the value of name.
     *
     * @param string $name $name the name
     *
     * @return self
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Gets the value of uri.
     *
     * @return string $name
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * Sets the value of uri.
     *
     * @param string $name $uri the uri
     *
     * @return self
     */
    public function setUri($uri)
    {
        $this->uri = $uri;

        return $this;
    }

    /**
     * Gets the value of secretKey.
     *
     * @return string $name
     */
    public function getSecretKey()
    {
        return $this->secretKey;
    }

    /**
     * Sets the value of secretKey.
     *
     * @param string $name $secretKey the secret key
     *
     * @return self
     */
    public function setSecretKey($secretKey)
    {
        $this->secretKey = $secretKey;

        return $this;
    }

    /**
     * Gets the value of mime.
     *
     * @return string $name
     */
    public function getMime()
    {
        return $this->mime;
    }

    /**
     * Sets the value of mime.
     *
     * @param string $name $mime the mime
     *
     * @return self
     */
    public function setMime($mime)
    {
        $this->mime = $mime;

        return $this;
    }

    /**
     * Gets the value of lifetime.
     *
     * @return string $label
     */
    public function getLifetime()
    {
        return $this->lifetime;
    }

    /**
     * Sets the value of lifetime.
     *
     * @param string $label $lifetime the lifetime
     *
     * @return self
     */
    public function setLifetime($lifetime)
    {
        $this->lifetime = $lifetime;

        return $this;
    }
}
