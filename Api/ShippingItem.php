<?php

namespace Sulu\Bundle\Sales\ShippingBundle\Api;

use JMS\Serializer\Annotation\VirtualProperty;
use JMS\Serializer\Annotation\SerializedName;
use JMS\Serializer\Annotation\Exclude;
use Sulu\Bundle\Sales\CoreBundle\Api\Item as ApiItem;
use Sulu\Bundle\Sales\CoreBundle\Entity\Item;
use Sulu\Component\Rest\ApiWrapper;
use Sulu\Bundle\Sales\ShippingBundle\Entity\ShippingItem as ShippingItemEntity;
use Sulu\Bundle\ProductBundle\Product\ProductFactory;
use Sulu\Bundle\Sales\ShippingBundle\Api\Shipping as ApiShipping;

/**
 * Describes an item of a shipping
 * @package Sulu\Bundle\Sales\ShippingBundle\Api
 */
class ShippingItem extends ApiWrapper
{
    /**
     * quantity that has already been shipped in other shippings
     */
    private $shippedItems;

    /**
     * @param ShippingItemEntity $entity
     * @param string $locale
     */
    public function __construct(ShippingItemEntity $entity, $locale)
    {
        $this->entity = $entity;
        $this->locale = $locale;
    }

    /**
     * Returns the id
     *
     * @VirtualProperty
     * @SerializedName("id")
     *
     * @return int
     */
    public function getId()
    {
        return $this->entity->getId();
    }

    /**
     * Set quantity
     *
     * @param float $quantity
     *
     * @return ShippingItem
     */
    public function setQuantity($quantity)
    {
        $this->entity->setQuantity($quantity);

        return $this;
    }

    /**
     * Get quantity
     *
     * @VirtualProperty
     * @SerializedName("quantity")
     *
     * @return float
     */
    public function getQuantity()
    {
        return $this->entity->getQuantity();
    }

    /**
     * Set note
     *
     * @param string $note
     *
     * @return ShippingItem
     */
    public function setNote($note)
    {
        $this->entity->setNote($note);

        return $this;
    }

    /**
     * Get note
     *
     * @VirtualProperty
     * @SerializedName("note")
     *
     * @return string
     */
    public function getNote()
    {
        return $this->entity->getNote();
    }

    /**
     * Set shipping
     *
     * @param Shipping $shipping
     *
     * @return ShippingItem
     */
    public function setShipping(Shipping $shipping)
    {
        $this->entity->setShipping($shipping);

        return $this;
    }

    /**
     * Get Shipping
     *
     * @return ApiShipping
     */
    public function getShipping()
    {
        return new ApiShipping($this->entity->getShipping(), $this->locale);
    }

    /**
     * Set item
     *
     * @return ShippingItem
     *
     * @param Item $item
     */
    public function setItem(Item $item)
    {
        $this->entity->setItem($item);

        return $this;
    }

    /**
     * Get item
     *
     * @VirtualProperty
     * @SerializedName("item")
     *
     * @return Item
     */
    public function getItem()
    {
        $productFactory = new ProductFactory();

        return new ApiItem($this->entity->getItem(), $this->locale, $productFactory);
    }

    /**
     * Set number if ShippedItems
     *
     * @param int $numShippedItems
     *
     * @return Shipping
     */
    public function setShippedItems($numShippedItems)
    {
        $this->shippedItems = $numShippedItems;

        return $this;
    }

    /**
     * Returns nuber of shipped items
     *
     * @VirtualProperty
     * @SerializedName("shippedItems")
     *
     * @return int
     */
    public function getShippedItems()
    {
        return $this->shippedItems;
    }
}
