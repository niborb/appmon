<?php

namespace Rts\Bundle\AppMonBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Rts\Bundle\AppMonBundle\Entity\AppCategory
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Rts\Bundle\AppMonBundle\Entity\AppCategoryRepository")
 */
class AppCategory
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
     * @var string $name
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var array|\Doctrine\Common\Collections\ArrayCollection
     * @ORM\OneToMany(targetEntity="App", mappedBy="category")
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
     * Set name
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getName();
    }

    /**
     * @param array|\Doctrine\Common\Collections\ArrayCollection $apps
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
}