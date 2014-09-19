<?php

namespace Sulu\Bundle\Sales\ShippingBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ShippingItem
 */
class ShippingItem
{
    /**
     * @var float
     */
    private $quantity;

    /**
     * @var string
     */
    private $note;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \Sulu\Bundle\Sales\ShippingBundle\Entity\Shipping
     */
    private $shipping;

    /**
     * @var \Sulu\Bundle\Sales\CoreBundle\Entity\Item
     */
    private $item;


    /**
     * Set quantity
     *
     * @param float $quantity
     * @return ShippingItem
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;
    
        return $this;
    }

    /**
     * Get quantity
     *
     * @return float 
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * Set note
     *
     * @param string $note
     * @return ShippingItem
     */
    public function setNote($note)
    {
        $this->note = $note;
    
        return $this;
    }

    /**
     * Get note
     *
     * @return string 
     */
    public function getNote()
    {
        return $this->note;
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
     * Set shipping
     *
     * @param \Sulu\Bundle\Sales\ShippingBundle\Entity\Shipping $shipping
     * @return ShippingItem
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

    /**
     * Set item
     *
     * @param \Sulu\Bundle\Sales\CoreBundle\Entity\Item $item
     * @return ShippingItem
     */
    public function setItem(\Sulu\Bundle\Sales\CoreBundle\Entity\Item $item)
    {
        $this->item = $item;
    
        return $this;
    }

    /**
     * Get item
     *
     * @return \Sulu\Bundle\Sales\CoreBundle\Entity\Item 
     */
    public function getItem()
    {
        return $this->item;
    }
}