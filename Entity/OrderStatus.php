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
    private $orderStatusTranslations;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->order = new \Doctrine\Common\Collections\ArrayCollection();
        $this->orderStatusTranslations = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Add orderStatusTranslations
     *
     * @param \Sulu\Bundle\Sales\OrderBundle\Entity\OrderStatusTranslations $orderStatusTranslations
     * @return OrderStatus
     */
    public function addOrderStatusTranslation(\Sulu\Bundle\Sales\OrderBundle\Entity\OrderStatusTranslations $orderStatusTranslations)
    {
        $this->orderStatusTranslations[] = $orderStatusTranslations;
    
        return $this;
    }

    /**
     * Remove orderStatusTranslations
     *
     * @param \Sulu\Bundle\Sales\OrderBundle\Entity\OrderStatusTranslations $orderStatusTranslations
     */
    public function removeOrderStatusTranslation(\Sulu\Bundle\Sales\OrderBundle\Entity\OrderStatusTranslations $orderStatusTranslations)
    {
        $this->orderStatusTranslations->removeElement($orderStatusTranslations);
    }

    /**
     * Get orderStatusTranslations
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getOrderStatusTranslations()
    {
        return $this->orderStatusTranslations;
    }
}
