<?php

namespace Sulu\Bundle\Sales\OrderBundle\Api;

use JMS\Serializer\Annotation\VirtualProperty;
use Sulu\Bundle\Sales\OrderBundle\Entity\OrderStatus as Entity;
use Sulu\Bundle\Sales\OrderBundle\Entity\OrderStatusTranslation;
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
     * @return string
     * @VirtualProperty
     * @SerializedName("status")
     */
    public function getStatus() {
        return $this->getTranslation($this->locale)->getName();
    }

    /**
     * Returns the translation for the given locale
     * @param string $locale
     * @return OrderStatusTranslation
     */
    public function getTranslation($locale)
    {
        $translation = null;
        foreach ($this->entity->getTranslations() as $translationData) {
            if ($translationData->getLocale() == $locale) {
                $translation = $translationData;
                break;
            }
        }

        return $translation;
    }
}
