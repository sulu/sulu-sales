<?php

namespace Sulu\Bundle\Sales\OrderBundle\Api;

use JMS\Serializer\Annotation\VirtualProperty;
use Sulu\Bundle\Sales\OrderBundle\Entity\OrderStatus as Entity;
use Sulu\Component\Rest\ApiWrapper;
use JMS\Serializer\Annotation\SerializedName;

/**
 * Defines the status of an order
 * @package Sulu\Bundle\Sales\OrderBundle\Api
 */
class OrderStatus extends ApiWrapper
{
    /**
     * @param Entity $entity
     * @param string $locale
     */
    public function __construct(Entity $entity, $locale) {
        $this->entity = $entity;
        $this->locale = $locale;
    }

    /**
     * Returns the id
     * @return int
     * @VirtualProperty
     * @SerializedName("id")
     */
    public function getId()
    {
        return $this->entity->getId();
    }

    /**
     * Returns the status
     * @return int
     * @VirtualProperty
     * @SerializedName("id")
     */
    public function getStatus() {
        return $this->entity->getTranslation($this->locale)->getTranslation()->getName;
    }
}
