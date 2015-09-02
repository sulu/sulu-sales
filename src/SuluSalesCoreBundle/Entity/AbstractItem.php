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

use Sulu\Bundle\ContactBundle\Entity\AccountInterface;

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
     * @var OrderAddressInterface
     */
    protected $deliveryAddress;

    /**
     * @var AccountInterface
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
     *
     * @param string $supplierName
     *
     * @return Item
     */
    public function setSupplierName($supplierName)
    {
        $this->supplierName = $supplierName;

        return $this;
    }

    /**
     * Get supplierName
     *
     * @return string
     */
    public function getSupplierName()
    {
        return $this->supplierName;
    }

    /**
     * Add attribute
     *
     * @param ItemAttributeInterface $attribute
     *
     * @return Item
     */
    public function addAttribute(ItemAttributeInterface $attribute)
    {
        $this->attributes[] = $attribute;

        return $this;
    }

    /**
     * Remove attribute
     *
     * @param ItemAttributeInterface $attribute
     */
    public function removeAttribute(ItemAttributeInterface $attribute)
    {
        $this->attributes->removeElement($attribute);
    }

    /**
     * Get attributes
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * Set supplier
     *
     * @param AccountInterface $supplier
     *
     * @return Item
     */
    public function setSupplier(AccountInterface $supplier = null)
    {
        $this->supplier = $supplier;

        return $this;
    }

    /**
     * Get supplier
     *
     * @return AccountInterface
     */
    public function getSupplier()
    {
        return $this->supplier;
    }

    /**
     * Set deliveryAddress
     *
     * @param OrderAddressInterface $deliveryAddress
     *
     * @return Item
     */
    public function setDeliveryAddress(OrderAddressInterface $deliveryAddress = null)
    {
        $this->deliveryAddress = $deliveryAddress;

        return $this;
    }

    /**
     * Get deliveryAddress
     *
     * @return OrderAddressInterface $deliveryAddress
     */
    public function getDeliveryAddress()
    {
        return $this->deliveryAddress;
    }
}
