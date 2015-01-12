<?php

namespace Sulu\Bundle\Sales\OrderBundle\Api;

use JMS\Serializer\Annotation\VirtualProperty;
use Sulu\Bundle\Sales\OrderBundle\Entity\OrderType as Entity;
use Sulu\Bundle\Sales\OrderBundle\Entity\OrderTypeTranslation;
use Sulu\Component\Rest\ApiWrapper;
use JMS\Serializer\Annotation\SerializedName;

/**
 * Defines the type of an order
 * @package Sulu\Bundle\Sales\OrderBundle\Api
 */
class OrderType extends ApiWrapper
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
     * Returns the type
     * @return string
     * @VirtualProperty
     * @SerializedName("type")
     */
    public function getType() {
        return $this->getTranslation($this->locale)->getName();
    }

    /**
     * Returns the translation for the given locale
     * @param string $locale
     * @return OrderTypeTranslation
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
