<?php

namespace Sulu\Bundle\Sales\OrderBundle\Entity;

class Order extends AbstractOrder
{
    /**
     * @var string
     */
    protected $sessionId;

    /**
     * @var OrderType
     */
    protected $type;

    /**
     * @var integer
     */
    protected $bitmaskStatus;

    /**
     * Set sessionId
     *
     * @param string $sessionId
     *
     * @return Order
     */
    public function setSessionId($sessionId)
    {
        $this->sessionId = $sessionId;

        return $this;
    }

    /**
     * Get sessionId
     *
     * @return string
     */
    public function getSessionId()
    {
        return $this->sessionId;
    }

    /**
     * Set type
     *
     * @param OrderType $type
     *
     * @return Order
     */
    public function setType(OrderType $type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return OrderType
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * {@inheritDoc}
     */
    public function setBitmaskStatus($bitmaskStatus)
    {
        $this->bitmaskStatus = $bitmaskStatus;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getBitmaskStatus()
    {
        return $this->bitmaskStatus;
    }
}
