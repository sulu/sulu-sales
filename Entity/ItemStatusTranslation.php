<?php

namespace Sulu\Bundle\Sales\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ItemStatusTranslation
 */
class ItemStatusTranslation
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $locale;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \Sulu\Bundle\Sales\CoreBundle\Entity\ItemStatus
     */
    private $status;


    /**
     * Set name
     *
     * @param string $name
     * @return ItemStatusTranslation
     */
    public function setName($name)
    {
        $this->name = $name;
    
        return $this;
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
     * Set locale
     *
     * @param string $locale
     * @return ItemStatusTranslation
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
    
        return $this;
    }

    /**
     * Get locale
     *
     * @return string 
     */
    public function getLocale()
    {
        return $this->locale;
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
     * Set status
     *
     * @param \Sulu\Bundle\Sales\CoreBundle\Entity\ItemStatus $status
     * @return ItemStatusTranslation
     */
    public function setStatus(\Sulu\Bundle\Sales\CoreBundle\Entity\ItemStatus $status = null)
    {
        $this->status = $status;
    
        return $this;
    }

    /**
     * Get status
     *
     * @return \Sulu\Bundle\Sales\CoreBundle\Entity\ItemStatus 
     */
    public function getStatus()
    {
        return $this->status;
    }
}
