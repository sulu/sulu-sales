<?php

namespace Sulu\Bundle\Sales\ShippingBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ShippingActivityLog
 */
class ShippingActivityLog
{
    /**
     * @var \DateTime
     */
    private $created;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \Sulu\Bundle\Sales\ShippingBundle\Entity\ShippingStatus
     */
    private $statusFrom;

    /**
     * @var \Sulu\Bundle\Sales\ShippingBundle\Entity\ShippingStatus
     */
    private $statusTo;

    /**
     * @var \Sulu\Bundle\Sales\ShippingBundle\Entity\Shipping
     */
    private $shipping;


    /**
     * Set created
     *
     * @param \DateTime $created
     * @return ShippingActivityLog
     */
    public function setCreated($created)
    {
        $this->created = $created;
    
        return $this;
    }

    /**
     * Get created
     *
     * @return \DateTime 
     */
    public function getCreated()
    {
        return $this->created;
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
     * Set statusFrom
     *
     * @param \Sulu\Bundle\Sales\ShippingBundle\Entity\ShippingStatus $statusFrom
     * @return ShippingActivityLog
     */
    public function setStatusFrom(\Sulu\Bundle\Sales\ShippingBundle\Entity\ShippingStatus $statusFrom = null)
    {
        $this->statusFrom = $statusFrom;
    
        return $this;
    }

    /**
     * Get statusFrom
     *
     * @return \Sulu\Bundle\Sales\ShippingBundle\Entity\ShippingStatus 
     */
    public function getStatusFrom()
    {
        return $this->statusFrom;
    }

    /**
     * Set statusTo
     *
     * @param \Sulu\Bundle\Sales\ShippingBundle\Entity\ShippingStatus $statusTo
     * @return ShippingActivityLog
     */
    public function setStatusTo(\Sulu\Bundle\Sales\ShippingBundle\Entity\ShippingStatus $statusTo)
    {
        $this->statusTo = $statusTo;
    
        return $this;
    }

    /**
     * Get statusTo
     *
     * @return \Sulu\Bundle\Sales\ShippingBundle\Entity\ShippingStatus 
     */
    public function getStatusTo()
    {
        return $this->statusTo;
    }

    /**
     * Set shipping
     *
     * @param \Sulu\Bundle\Sales\ShippingBundle\Entity\Shipping $shipping
     * @return ShippingActivityLog
     */
    public function setShipping(\Sulu\Bundle\Sales\ShippingBundle\Entity\Shipping $shipping)
    {
        $this->shipping = $shipping;
    
        return $this;
    }

    /**
     * Get shipping
     *
     * @return \Sulu\Bundle\Sales\ShippingBundle\Entity\Shipping 
     */
    public function getShipping()
    {
        return $this->shipping;
    }
}