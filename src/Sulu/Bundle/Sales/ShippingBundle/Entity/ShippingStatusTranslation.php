<?php

namespace Sulu\Bundle\Sales\ShippingBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ShippingStatusTranslation
 */
class ShippingStatusTranslation
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
     * @var \Sulu\Bundle\Sales\ShippingBundle\Entity\ShippingStatus
     */
    private $status;


    /**
     * Set name
     *
     * @param string $name
     * @return ShippingStatusTranslation
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
     * @return ShippingStatusTranslation
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
     * @param \Sulu\Bundle\Sales\ShippingBundle\Entity\ShippingStatus $status
     * @return ShippingStatusTranslation
     */
    public function setStatus(\Sulu\Bundle\Sales\ShippingBundle\Entity\ShippingStatus $status)
    {
        $this->status = $status;
    
        return $this;
    }

    /**
     * Get status
     *
     * @return \Sulu\Bundle\Sales\ShippingBundle\Entity\ShippingStatus 
     */
    public function getStatus()
    {
        return $this->status;
    }
}