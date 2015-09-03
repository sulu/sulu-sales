<?php

namespace Sulu\Bundle\Sales\ShippingBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ShippingStatus
 */
class ShippingStatus
{

    const STATUS_CREATED = 1;
    const STATUS_DELIVERY_NOTE = 2;
    const STATUS_SHIPPED= 4;
    const STATUS_CANCELED= 8;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $shipping;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $translations;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->shipping = new \Doctrine\Common\Collections\ArrayCollection();
        $this->translations = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @param $id
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Add shipping
     *
     * @param \Sulu\Bundle\Sales\ShippingBundle\Entity\Shipping $shipping
     * @return ShippingStatus
     */
    public function addShipping(\Sulu\Bundle\Sales\ShippingBundle\Entity\Shipping $shipping)
    {
        $this->shipping[] = $shipping;
    
        return $this;
    }

    /**
     * Remove shipping
     *
     * @param \Sulu\Bundle\Sales\ShippingBundle\Entity\Shipping $shipping
     */
    public function removeShipping(\Sulu\Bundle\Sales\ShippingBundle\Entity\Shipping $shipping)
    {
        $this->shipping->removeElement($shipping);
    }

    /**
     * Get shipping
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getShipping()
    {
        return $this->shipping;
    }

    /**
     * Add translations
     *
     * @param \Sulu\Bundle\Sales\ShippingBundle\Entity\ShippingStatusTranslation $translations
     * @return ShippingStatus
     */
    public function addTranslation(\Sulu\Bundle\Sales\ShippingBundle\Entity\ShippingStatusTranslation $translations)
    {
        $this->translations[] = $translations;
    
        return $this;
    }

    /**
     * Remove translations
     *
     * @param \Sulu\Bundle\Sales\ShippingBundle\Entity\ShippingStatusTranslation $translations
     */
    public function removeTranslation(\Sulu\Bundle\Sales\ShippingBundle\Entity\ShippingStatusTranslation $translations)
    {
        $this->translations->removeElement($translations);
    }

    /**
     * Get translations
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getTranslations()
    {
        return $this->translations;
    }
}