<?php

namespace Sulu\Bundle\Sales\ShippingBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Sulu\Bundle\Sales\CoreBundle\Entity\ItemInterface;

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
     * @var Shipping
     */
    private $shipping;

    /**
     * @var ItemInterface
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
     * @param Shipping $shipping
     * @return ShippingItem
     */
    public function setShipping(Shipping $shipping)
    {
        $this->shipping = $shipping;
    
        return $this;
    }

    /**
     * Get shipping
     *
     * @return Shipping
     */
    public function getShipping()
    {
        return $this->shipping;
    }

    /**
     * Set item
     *
     * @param ItemInterface $item
     * @return ShippingItem
     */
    public function setItem(ItemInterface $item)
    {
        $this->item = $item;
    
        return $this;
    }

    /**
     * Get item
     *
     * @return ItemInterface
     */
    public function getItem()
    {
        return $this->item;
    }
}
