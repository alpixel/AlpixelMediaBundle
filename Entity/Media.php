<?php

namespace Alpixel\Bundle\MediaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Media.
 *
 * @ORM\Table(name="media")
 * @ORM\Entity(repositoryClass="Alpixel\Bundle\MediaBundle\Repository\MediaRepository")
 */
class Media
{
    /**
     * @var int
     *
     * @ORM\Column(name="media_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=191, nullable=false)
     */
    protected $name;

    /**
     * @var string
     *
     * @ORM\Column(name="uri", type="string", length=191, nullable=false, unique=true)
     */
    protected $uri;

    /**
     * @var string
     *
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
     * @ORM\Column(name="lifetime", type="datetime", nullable=true)
     */
    protected $lifetime;

    /**
     * @var \DateTime
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="date_created", type="datetime", nullable=false)
     */
    protected $dateCreated;

    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(name="date_updated", type="datetime", nullable=false)
     */
    protected $dateUpdated;

    /**
     * @var string $createdFromIp
     *
     * @Gedmo\IpTraceable(on="create")
     * @ORM\Column(length=45, nullable=true)
     */
    protected $createdFromIp;

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
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Sets the value of id.
     *
     * @param int $id the id
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

    /**
     * Gets the value of dateCreated.
     *
     * @return \DateTime
     */
    public function getDateCreated()
    {
        return $this->dateCreated;
    }

    /**
     * Sets the value of dateCreated.
     *
     * @param \DateTime $dateCreated the date created
     *
     * @return self
     */
    public function setDateCreated(\DateTime $dateCreated)
    {
        $this->dateCreated = $dateCreated;

        return $this;
    }

    /**
     * Gets the value of dateUpdated.
     *
     * @return \DateTime
     */
    public function getDateUpdated()
    {
        return $this->dateUpdated;
    }

    /**
     * Sets the value of dateUpdated.
     *
     * @param \DateTime $dateUpdated the date updated
     *
     * @return self
     */
    public function setDateUpdated(\DateTime $dateUpdated)
    {
        $this->dateUpdated = $dateUpdated;

        return $this;
    }

    /**
     * Gets the value of createdFromIp.
     *
     * @return string $createdFromIp
     */
    public function getCreatedFromIp()
    {
        return $this->createdFromIp;
    }

    /**
     * Sets the value of createdFromIp.
     *
     * @param string $createdFromIp $createdFromIp the created from ip
     *
     * @return self
     */
    public function setCreatedFromIp($createdFromIp)
    {
        $this->createdFromIp = $createdFromIp;

        return $this;
    }
}
