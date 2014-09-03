<?php

namespace Sulu\Bundle\Sales\ShippingBundle\Api;

use JMS\Serializer\Annotation\VirtualProperty;
use Sulu\Component\Rest\ApiWrapper;
use JMS\Serializer\Annotation\SerializedName;
use Sulu\Bundle\Sales\ShippingBundle\Entity\ShippingStatus as ShippingStatusEntity;

/**
 * Defines the status of an shipping
 * @package Sulu\Bundle\Sales\ShippingBundle\Api
 */
class ShippingStatus extends ApiWrapper
{
    /**
     * @param ShippingStatusEntity $entity
     * @param string $locale
     */
    public function __construct(ShippingStatusEntity $entity, $locale) {
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
     * @return ShippingStatusTranslation
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
