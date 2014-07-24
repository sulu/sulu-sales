<?php

namespace Sulu\Bundle\Sales\OrderBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * OrderStatus
 */
class OrderStatus
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $order;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $translations;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->order = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Add order
     *
     * @param \Sulu\Bundle\Sales\OrderBundle\Entity\Order $order
     * @return OrderStatus
     */
    public function addOrder(\Sulu\Bundle\Sales\OrderBundle\Entity\Order $order)
    {
        $this->order[] = $order;
    
        return $this;
    }

    /**
     * Remove order
     *
     * @param \Sulu\Bundle\Sales\OrderBundle\Entity\Order $order
     */
    public function removeOrder(\Sulu\Bundle\Sales\OrderBundle\Entity\Order $order)
    {
        $this->order->removeElement($order);
    }

    /**
     * Get order
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * Add translations
     *
     * @param \Sulu\Bundle\Sales\OrderBundle\Entity\OrderStatusTranslation $translations
     * @return OrderStatus
     */
    public function addTranslation(\Sulu\Bundle\Sales\OrderBundle\Entity\OrderStatusTranslation $translations)
    {
        $this->translations[] = $translations;
    
        return $this;
    }

    /**
     * Remove translations
     *
     * @param \Sulu\Bundle\Sales\OrderBundle\Entity\OrderStatusTranslation $translations
     */
    public function removeTranslation(\Sulu\Bundle\Sales\OrderBundle\Entity\OrderStatusTranslation $translations)
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
