<?php

namespace Sulu\Bundle\Sales\CoreBundle\Entity;

use Doctrine\Common\Collections\Collection;

interface TransitionInterface
{
    /**
     * @return int
     */
    public function getId();

    /**
     * @var string
     */
    public function getSource();

    /**
     * @var int
     */
    public function getSourceId();

    /**
     * @var string
     */
    public function getDestination();

    /**
     * @var int;
     */
    public function getDestinationId();

    /**
     * @var Collection
     */
    public function getItems();

    /**
     * @param TransitionItem $item
     *
     * @return self
     */
    public function addItem($item);
}
