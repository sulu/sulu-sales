<?php
/*
 * This file is part of the Sulu.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\Sales\CoreBundle\Transition\TransitionData;

/**
 * Transition Data for an item
 */
class Item
{
    /**
     * @var float
     */
    private $price;

    /**
     * @var float
     */
    private $quantity;

    /**
     * @var string
     */
    private $quantityUnit;

    /**
     * @var Product
     */
    private $product;

    /**
     * @var Account
     */
    private $supplier;

    /**
     * @var Account
     */
    private $customer;

    /**
     * @return float
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param float $price
     */
    public function setPrice($price)
    {
        $this->price = $price;
    }

    /**
     * @return float
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * @param float $quantity
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;
    }

    /**
     * @return string
     */
    public function getQuantityUnit()
    {
        return $this->quantityUnit;
    }

    /**
     * @param string $quantityUnit
     */
    public function setQuantityUnit($quantityUnit)
    {
        $this->quantityUnit = $quantityUnit;
    }

    /**
     * @return Product
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * @param Product $product
     */
    public function setProduct($product)
    {
        $this->product = $product;
    }

    /**
     * @return Account
     */
    public function getSupplier()
    {
        return $this->supplier;
    }

    /**
     * @param Account $supplier
     */
    public function setSupplier($supplier)
    {
        $this->supplier = $supplier;
    }

    /**
     * @return Account
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * @param Account $customer
     */
    public function setCustomer($customer)
    {
        $this->customer = $customer;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'quantity' => $this->quantity,
            'quantityUnit' => $this->quantityUnit,
            'price' => $this->price,
            'product' => $this->product->toArray(),
            'supplier' => $this->supplier->toArray(),
            'customer' => $this->customer->toArray(),
        ];
    }
}
