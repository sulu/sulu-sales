<?php

namespace Sulu\Bundle\Sales\OrderBundle\Api;

use JMS\Serializer\Annotation\VirtualProperty;
use Sulu\Bundle\Sales\OrderBundle\Entity\OrderAddress as Entity;

use JMS\Serializer\Annotation\Groups;
use Sulu\Component\Rest\ApiWrapper;
use JMS\Serializer\Annotation\SerializedName;

/**
 * Defines the type of an order
 * @package Sulu\Bundle\Sales\OrderBundle\Api
 */
class OrderAddress extends ApiWrapper
{
    /**
     * @param Entity $entity
     */
    public function __construct(Entity $entity) {
        $this->entity = $entity;
    }

    /**
     * Returns the id
     * @return int
     * @VirtualProperty
     * @SerializedName("id")
     * @Groups({"cart"})d
     */
    public function getId()
    {
        return $this->entity->getId();
    }


}
