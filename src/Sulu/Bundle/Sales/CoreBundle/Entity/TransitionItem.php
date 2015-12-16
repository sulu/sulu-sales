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
     * @var ItemInterface
     */
    protected $item;

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
     * @return ItemInterface
     */
    public function getItem()
    {
        return $this->item;
    }

    /**
     * @param ItemInterface $item
     *
     * @return self
     */
    public function setItem($item)
    {
        $this->item = $item;

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
