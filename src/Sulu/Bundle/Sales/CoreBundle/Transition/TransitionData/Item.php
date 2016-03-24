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
     * @var bool
     */
    private $useProductsPrice;

    /**
     * @var Product
     */
    private $product;

    /**
     * @var Account
     */
    private $supplierAccount;

    /**
     * @var Account
     */
    private $customerAccount;

    /**
     * @var array
     */
    private $address;

    /**
     * @var string
     */
    private $deliveryDate;

    /**
     * @var float
     */
    private $deliveryCost;

    /**
     * @var string
     */
    private $note;

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
    public function getSupplierAccount()
    {
        return $this->supplierAccount;
    }

    /**
     * @param Account $supplier
     */
    public function setSupplierAccount($supplier)
    {
        $this->supplierAccount = $supplier;
    }

    /**
     * @return Account
     */
    public function getCustomerAccount()
    {
        return $this->customerAccount;
    }

    /**
     * @param Account $customer
     */
    public function setCustomerAccount($customer)
    {
        $this->customerAccount = $customer;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $data = [
            'address' => $this->address,
            'customerAccount' => $this->createDataArray($this->customerAccount),
            'price' => $this->price,
            'product' => $this->createDataArray($this->product),
            'supplierAccount' => $this->createDataArray($this->supplierAccount),
            'useProductsPrice' => $this->useProductsPrice,
            'quantity' => $this->quantity,
            'quantityUnit' => $this->quantityUnit,
            'deliveryDate' => $this->deliveryDate,
            'deliveryCost' => $this->deliveryCost,
            'note' => $this->note,
        ];

        return array_filter($data, [$this, 'checkNotNull']);
    }

    /**
     * @return array
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @param array $address
     */
    public function setAddress($address)
    {
        $this->address = $address;
    }

    /**
     * @return bool
     */
    public function getUseProductsPrice()
    {
        return $this->useProductsPrice;
    }

    /**
     * @param bool $useProductsPrice
     */
    public function setUseProductsPrice($useProductsPrice)
    {
        $this->useProductsPrice = $useProductsPrice;
    }

    /**
     * @return string
     */
    public function getDeliveryDate()
    {
        return $this->deliveryDate;
    }

    /**
     * @param string $deliveryDate
     */
    public function setDeliveryDate($deliveryDate)
    {
        $this->deliveryDate = $deliveryDate;
    }

    /**
     * @return string
     */
    public function getNote()
    {
        return $this->note;
    }

    /**
     * @param string $note
     */
    public function setNote($note)
    {
        $this->note = $note;
    }

    /**
     * @return float
     */
    public function getDeliveryCost()
    {
        return $this->deliveryCost;
    }

    /**
     * @param float $deliveryCost
     */
    public function setDeliveryCost($deliveryCost)
    {
        $this->deliveryCost = $deliveryCost;
    }

    /**
     * Calls to Array on a certain object, if method exists.
     *
     * @param Object $object
     *
     * @return null|array
     */
    private function createDataArray($object)
    {
        if ($object && method_exists($object, 'toArray')) {
            return $object->toArray();
        }

        return null;
    }

    /**
     * @param string $entry
     *
     * @return bool
     */
    private function checkNotNull($entry)
    {
        return !is_null($entry);
    }
}
