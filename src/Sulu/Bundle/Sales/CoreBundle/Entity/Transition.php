<?php

namespace Sulu\Bundle\Sales\CoreBundle\Entity;

use Doctrine\Common\Collections\Collection;

class Transition implements TransitionInterface
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $source;

    /**
     * @var int
     */
    protected $sourceId;

    /**
     * @var string
     */
    protected $destination;

    /**
     * @var int;
     */
    protected $destinationId;

    /**
     * @var Collection
     */
    protected $items;

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
     * @return string
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * @param string $source
     *
     * @return self
     */
    public function setSource($source)
    {
        $this->source = $source;

        return $this;
    }

    /**
     * @return int
     */
    public function getSourceId()
    {
        return $this->sourceId;
    }

    /**
     * @param int $sourceId
     *
     * @return self
     */
    public function setSourceId($sourceId)
    {
        $this->sourceId = $sourceId;

        return $this;
    }

    /**
     * @return string
     */
    public function getDestination()
    {
        return $this->destination;
    }

    /**
     * @param string $destination
     *
     * @return self
     */
    public function setDestination($destination)
    {
        $this->destination = $destination;

        return $this;
    }

    /**
     * @return int
     */
    public function getDestinationId()
    {
        return $this->destinationId;
    }

    /**
     * @param int $destinationId
     *
     * @return self
     */
    public function setDestinationId($destinationId)
    {
        $this->destinationId = $destinationId;

        return $this;
    }

    /**
     * @return Collection
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @param Collection $items
     *
     * @return self
     */
    public function setItems($items)
    {
        $this->items = $items;

        return $this;
    }

    /**
     * @param $item
     *
     * @return self
     */
    public function addItem($item)
    {
        $this->items->add($item);

        return $this;
    }
}
