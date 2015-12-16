<?php

namespace Sulu\Bundle\Sales\CoreBundle\Entity;

class TransitionItem
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var Transition
     */
    protected $transition;

    /**
     * @var int
     */
    protected $itemId;

    /**
     * @var string
     */
    protected $itemClass;

    /**
     * @var int
     */
    protected $itemCount;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return self
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return Transition
     */
    public function getTransition()
    {
        return $this->transition;
    }

    /**
     * @param Transition $transition
     *
     * @return self
     */
    public function setTransition($transition)
    {
        $this->transition = $transition;

        return $this;
    }

    /**
     * @return int
     */
    public function getItemId()
    {
        return $this->itemId;
    }

    /**
     * @param int $itemId
     *
     * @return self
     */
    public function setItemId($itemId)
    {
        $this->itemId = $itemId;

        return $this;
    }

    /**
     * @return string
     */
    public function getItemClass()
    {
        return $this->itemClass;
    }

    /**
     * @param string $itemClass
     *
     * @return self
     */
    public function setItemClass($itemClass)
    {
        $this->itemClass = $itemClass;

        return $this;
    }

    /**
     * @return int
     */
    public function getItemCount()
    {
        return $this->itemCount;
    }

    /**
     * @param int $itemCount
     *
     * @return self
     */
    public function setItemCount($itemCount)
    {
        $this->itemCount = $itemCount;

        return $this;
    }
}
