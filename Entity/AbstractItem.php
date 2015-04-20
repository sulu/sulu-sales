<?php
/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\Sales\CoreBundle\Entity;

/**
 * Item
 */
class AbstractItem extends BaseItem implements ItemInterface
{
    /**
     * @var string
     */
    protected $supplierName;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $attributes;

    /**
     * @var OrderAddress
     */
    protected $deliveryAddress;

    /**
     * @var \Sulu\Bundle\ContactBundle\Entity\Account
     */
    protected $supplier;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->attributes = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set supplierName
     * @param string $supplierName
     * @return Item
     */
    public function setSupplierName($supplierName)
    {
        $this->supplierName = $supplierName;

        return $this;
    }

    /**
     * Get supplierName
     * @return string
     */
    public function getSupplierName()
    {
        return $this->supplierName;
    }

    /**
     * Add attributes
     * @param \Sulu\Bundle\Sales\CoreBundle\Entity\ItemAttribute $attributes
     * @return Item
     */
    public function addAttribute(\Sulu\Bundle\Sales\CoreBundle\Entity\ItemAttribute $attributes)
    {
        $this->attributes[] = $attributes;

        return $this;
    }

    /**
     * Remove attributes
     * @param \Sulu\Bundle\Sales\CoreBundle\Entity\ItemAttribute $attributes
     */
    public function removeAttribute(\Sulu\Bundle\Sales\CoreBundle\Entity\ItemAttribute $attributes)
    {
        $this->attributes->removeElement($attributes);
    }

    /**
     * Get attributes
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * Set supplier
     * @param \Sulu\Bundle\ContactBundle\Entity\Account $supplier
     * @return Item
     */
    public function setSupplier(\Sulu\Bundle\ContactBundle\Entity\Account $supplier = null)
    {
        $this->supplier = $supplier;

        return $this;
    }

    /**
     * Get supplier
     * @return \Sulu\Bundle\ContactBundle\Entity\Account
     */
    public function getSupplier()
    {
        return $this->supplier;
    }

    /**
     * Set deliveryAddress
     * @param OrderAddress $deliveryAddress
     * @return Item
     */
    public function setDeliveryAddress(OrderAddress $deliveryAddress = null)
    {
        $this->deliveryAddress = $deliveryAddress;

        return $this;
    }

    /**
     * Get deliveryAddress
     * @return OrderAddress $deliveryAddress
     */
    public function getDeliveryAddress()
    {
        return $this->deliveryAddress;
    }
}
