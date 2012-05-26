<?php

namespace Rts\Bundle\AppMonBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Rts\Bundle\AppMonBundle\Entity\Server
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Rts\Bundle\AppMonBundle\Entity\ServerRepository")
 */
class Server
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string $hostname
     *
     * @ORM\Column(name="hostname", type="string", length=255)
     */
    private $hostname;

    /**
     * @var string $ip_address
     *
     * @ORM\Column(name="ip_address", type="string", length=255)
     */
    private $ip_address;

    /**
     * @var string $description
     *
     * @ORM\Column(name="description", type="string", length=255, nullable=true)
     */
    private $description;

    /**
     * @var array $tags
     *
     * @ORM\Column(name="tags", type="array", nullable=true)
     */
    private $tags;


    /**
     * @var array|\Doctrine\Common\Collections\ArrayCollection
     * @ORM\OneToMany(targetEntity="App", mappedBy="server")
     */
    private $apps;

    /**
     * constructor
     *
     */
    public function __construct()
    {
        $this->apps = new ArrayCollection();
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set hostname
     *
     * @param string $hostname
     */
    public function setHostname($hostname)
    {
        $this->hostname = $hostname;
    }

    /**
     * Get hostname
     *
     * @return string
     */
    public function getHostname()
    {
        return $this->hostname;
    }

    /**
     * Set ip_address
     *
     * @param string $ipAddress
     */
    public function setIpAddress($ipAddress)
    {
        $this->ip_address = $ipAddress;
    }

    /**
     * Get ip_address
     *
     * @return string
     */
    public function getIpAddress()
    {
        return $this->ip_address;
    }

    /**
     * Set description
     *
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set tags
     *
     * @param array $tags
     */
    public function setTags($tags)
    {
        $this->tags = $tags;
    }

    /**
     * Get tags
     *
     * @return array
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * @param array $apps
     */
    public function setApps($apps)
    {
        $this->apps = $apps;
    }

    /**
     * @return array|\Doctrine\Common\Collections\ArrayCollection
     */
    public function getApps()
    {
        return $this->apps;
    }

    /**
     * Add apps
     *
     * @param Rts\Bundle\AppMonBundle\Entity\App $apps
     */
    public function addApp(\Rts\Bundle\AppMonBundle\Entity\App $apps)
    {
        $this->apps[] = $apps;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $description = $this->getDescription();

        $returnstring = $this->getIpAddress();
        if (!empty($description)) {
            $returnstring = sprintf('%s - %s', $description, $this->getIpAddress());
        }

        return (string) $returnstring;
    }

}