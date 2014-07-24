<?php

namespace Sulu\Bundle\Sales\OrderBundle\Api;

use JMS\Serializer\Annotation\VirtualProperty;
use Sulu\Bundle\Sales\OrderBundle\Entity\Order as Entity;

/**
 * The order class which will be exported to the API
 * @package Sulu\Bundle\Sales\OrderBundle\Api
 * @Relation("self", href="expr('/api/admin/orders/' ~ object.getId())")
 */
class Order extends ApiWrapper
{
    /**
     * @param Entity $order The order to wrap
     * @param string $locale The locale of this order
     */
    public function __construct(Entity $order, $locale) {
        $this->entity = $order;
        $this->locale = $locale;
    }

    /**
     * Returns the id of the order
     * @return int
     * @VirtualProperty
     * @SerializedName("id")
     */
    public function getId()
    {
        return $this->entity->getId();
    }

    /**
     * Returns the id of the order
     * @return int
     * @VirtualProperty
     * @SerializedName("number")
     */
    public function getNumber()
    {
        return $this->entity->getNumber();
    }

    /**
     * sets number of order
     * @param $number
     * @return Entity
     */
    public function setNumber($number)
    {
        return $this->entity->setNumber($number);
    }
}
