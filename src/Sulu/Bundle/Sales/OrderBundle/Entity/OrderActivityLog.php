<?php

namespace Sulu\Bundle\Sales\OrderBundle\Entity;

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
     * @var OrderStatusInterface
     */
    private $statusFrom;

    /**
     * @var OrderStatusInterface
     */
    private $statusTo;

    /**
     * @var OrderInterface
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
     * @param OrderStatusInterface $statusFrom
     * @return OrderActivityLog
     */
    public function setStatusFrom(OrderStatusInterface $statusFrom)
    {
        $this->statusFrom = $statusFrom;

        return $this;
    }

    /**
     * Get statusFrom
     *
     * @return OrderStatusInterface
     */
    public function getStatusFrom()
    {
        return $this->statusFrom;
    }

    /**
     * Set statusTo
     *
     * @param OrderStatusInterface $statusTo
     * @return OrderActivityLog
     */
    public function setStatusTo(OrderStatusInterface $statusTo)
    {
        $this->statusTo = $statusTo;

        return $this;
    }

    /**
     * Get statusTo
     *
     * @return OrderStatusInterface
     */
    public function getStatusTo()
    {
        return $this->statusTo;
    }

    /**
     * Set order
     *
     * @param OrderInterface $order
     * @return OrderActivityLog
     */
    public function setOrder(OrderInterface $order)
    {
        $this->order = $order;

        return $this;
    }

    /**
     * Get order
     *
     * @return OrderInterface
     */
    public function getOrder()
    {
        return $this->order;
    }
}
