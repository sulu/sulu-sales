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
     * @var string
     */
    protected $internalNote;

    /**
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
     * @return string
     */
    public function getSessionId()
    {
        return $this->sessionId;
    }

    /**
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

    /**
     * @param string $note
     *
     * @return Inquiry
     */
    public function setInternalNote($note)
    {
        $this->internalNote = $note;

        return $this;
    }

    /**
     * @return string
     */
    public function getInternalNote()
    {
        return $this->internalNote;
    }
}
