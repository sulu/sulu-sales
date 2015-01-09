<?php

namespace Sulu\Bundle\Sales\OrderBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * OrderActivityLog
 */
class OrderActivityLog
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
     * @var \Sulu\Bundle\Sales\OrderBundle\Entity\OrderStatus
     */
    private $statusFrom;

    /**
     * @var \Sulu\Bundle\Sales\OrderBundle\Entity\OrderStatus
     */
    private $statusTo;

    /**
     * @var \Sulu\Bundle\Sales\OrderBundle\Entity\Order
     */
    private $order;


    /**
     * Set created
     *
     * @param \DateTime $created
     * @return OrderActivityLog
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
     * @param \Sulu\Bundle\Sales\OrderBundle\Entity\OrderStatus $statusFrom
     * @return OrderActivityLog
     */
    public function setStatusFrom(\Sulu\Bundle\Sales\OrderBundle\Entity\OrderStatus $statusFrom)
    {
        $this->statusFrom = $statusFrom;
    
        return $this;
    }

    /**
     * Get statusFrom
     *
     * @return \Sulu\Bundle\Sales\OrderBundle\Entity\OrderStatus 
     */
    public function getStatusFrom()
    {
        return $this->statusFrom;
    }

    /**
     * Set statusTo
     *
     * @param \Sulu\Bundle\Sales\OrderBundle\Entity\OrderStatus $statusTo
     * @return OrderActivityLog
     */
    public function setStatusTo(\Sulu\Bundle\Sales\OrderBundle\Entity\OrderStatus $statusTo)
    {
        $this->statusTo = $statusTo;
    
        return $this;
    }

    /**
     * Get statusTo
     *
     * @return \Sulu\Bundle\Sales\OrderBundle\Entity\OrderStatus 
     */
    public function getStatusTo()
    {
        return $this->statusTo;
    }

    /**
     * Set order
     *
     * @param \Sulu\Bundle\Sales\OrderBundle\Entity\Order $order
     * @return OrderActivityLog
     */
    public function setOrder(\Sulu\Bundle\Sales\OrderBundle\Entity\Order $order)
    {
        $this->order = $order;
    
        return $this;
    }

    /**
     * Get order
     *
     * @return \Sulu\Bundle\Sales\OrderBundle\Entity\Order 
     */
    public function getOrder()
    {
        return $this->order;
    }
}
