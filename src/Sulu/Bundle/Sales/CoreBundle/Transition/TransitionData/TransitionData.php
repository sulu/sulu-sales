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
 * Main transition data. Holds all the important information.
 */
class TransitionData
{
    /**
     * @var string
     */
    protected $title;

    /**
     * @var string
     */
    protected $number;

    /**
     * @var string
     */
    protected $description;

    /**
     * @var string
     */
    protected $costCentre;

    /**
     * @var string
     */
    protected $commission;

    /**
     * @var string
     */
    protected $internalNote;

    /**
     * @var float
     */
    protected $netShippingCosts;

    /**
     * @var Contact
     */
    protected $responsibleContact;

    /**
     * @var array
     */
    protected $items;

    /**
     * @var array
     */
    protected $customerItems;

    /**
     * @var string
     */
    protected $currencyCode;

    /**
     * @var array
     */
    protected $customerSupplierItems;

    /**
     * TransitionData constructor.
     */
    public function __construct()
    {
        $this->items = [];
        $this->customerItems = [];
        $this->customerSupplierItems = [];
    }

    /**
     * @return string
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * @param string $number
     */
    public function setNumber($number)
    {
        $this->number = $number;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function getCostCentre()
    {
        return $this->costCentre;
    }

    /**
     * @param string $costCentre
     */
    public function setCostCentre($costCentre)
    {
        $this->costCentre = $costCentre;
    }

    /**
     * @return string
     */
    public function getCommission()
    {
        return $this->commission;
    }

    /**
     * @param string $commission
     */
    public function setCommission($commission)
    {
        $this->commission = $commission;
    }

    /**
     * @return string
     */
    public function getInternalNote()
    {
        return $this->internalNote;
    }

    /**
     * @param string $internalNote
     */
    public function setInternalNote($internalNote)
    {
        $this->internalNote = $internalNote;
    }

    /**
     * @return float
     */
    public function getNetShippingCosts()
    {
        return $this->netShippingCosts;
    }

    /**
     * @param float $netShippingCosts
     */
    public function setNetShippingCosts($netShippingCosts)
    {
        $this->netShippingCosts = $netShippingCosts;
    }

    /**
     * @return Contact
     */
    public function getResponsibleContact()
    {
        return $this->responsibleContact;
    }

    /**
     * @param Contact $responsibleContact
     */
    public function setresponsibleContact($responsibleContact)
    {
        $this->responsibleContact = $responsibleContact;
    }

    /**
     * @return array
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @return array
     */
    public function getCustomerItems()
    {
        return $this->customerItems;
    }

    /**
     * @return array
     */
    public function getCustomerSupplierItems()
    {
        return $this->customerSupplierItems;
    }

    /**
     * @param Item[] $items
     */
    public function setItems($items)
    {
        if ($items && count($items) > 0) {
            $this->items = $items;
        }
    }

    /**
     * @param Item $item
     */
    public function addItem(Item $item)
    {
        $this->items = $item;
    }

    /**
     * @param $data
     */
    public function setResponsibleContactByData($data)
    {
        $this->responsibleContact = new Contact(
            $this->getProperty('id', $data),
            $this->getProperty('fullName', $data)
        );
    }

    /**
     * @param array $data
     */
    public function setItemsByData($data)
    {
        foreach ($data as $itemData) {
            $item = new Item();

            if (isset($itemData['item'])) {
                $itemData = array_merge($itemData, $itemData['item']);
            }

            // stop if quantity is smaller than 0
            if ($this->getProperty('quantity', $itemData, 0) < 1) {
                continue;
            }

            $item->setPrice($this->getProperty('price', $itemData, 0));
            $item->setQuantity($this->getProperty('quantity', $itemData));
            $item->setQuantityUnit($this->getProperty('quantityUnit', $itemData));
            $item->setAddress($this->getProperty('address', $itemData));
            $item->setUseProductsPrice($this->getProperty('useProductsPrice', $itemData));
            $item->setDeliveryDate($this->getProperty('deliveryDate', $itemData));
            $item->setNetShippingCosts($this->getProperty('netShippingCosts', $itemData));

            if (isset($itemData['product'])) {
                $product = new Product(
                    $this->getProperty('id', $itemData['product'])
                );
                $item->setProduct($product);
            }

            // set account to customer
            if (isset($itemData['account']) && !isset($itemData['customer'])) {
                $itemData['customer'] = $itemData['account'];
            }

            if (isset($itemData['customer'])) {
                $customer = new Account(
                    $this->getProperty('id', $itemData['customer']),
                    $this->getProperty('name', $itemData['customer'])
                );
                $item->setCustomerAccount($customer);
            }
            if (isset($itemData['supplier'])) {
                $customer = new Account(
                    $this->getProperty('id', $itemData['supplier']),
                    $this->getProperty('name', $itemData['supplier'])
                );
                $item->setSupplierAccount($customer);
            }

            // add to items array
            $this->items[] = $item;

            // create ordered item arrays
            $customerId = 0;
            $supplierId = 0;
            if ($item->getCustomerAccount()) {
                $customerId = $item->getCustomerAccount()->getId();
            }
            if ($item->getSupplierAccount()) {
                $supplierId = $item->getSupplierAccount()->getId();
            }
            $this->customerItems[$customerId][] = $item;
            $this->customerSupplierItems[$customerId]['supplierItems'][$supplierId][] = $item;
        }
    }

    /**
     * Adds extra customer data to customerSupplierItems.
     *
     * @param array $accountPurchases
     */
    public function addAccountPurchases($accountPurchases)
    {
        if (!$accountPurchases) {
            return;
        }

        foreach ($accountPurchases as $accountPurchase) {
            $accountId = $accountPurchase['account']['id'];

            if (!array_key_exists($accountId, $this->customerSupplierItems)) {
                continue;
            }

            $customerData = [];
            if (isset($accountPurchase['avisoContact'])) {
                $customerData['avisoContact'] = $accountPurchase['avisoContact'];
            }
            if (isset($accountPurchase['contact'])) {
                $customerData['contact'] = $accountPurchase['contact'];
            }

            // Add customer data
            $this->customerSupplierItems[$accountId] = $this->customerSupplierItems[$accountId] + $customerData;
        }
    }

    /**
     * @return array
     */
    public function itemsToArray()
    {
        $result = [];

        foreach ($this->items as $item) {
            $result[] = $this->createDataArray($item);
        }

        return $result;
    }

    /**
     * Converts TransitionData to array.
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'title' => $this->title,
            'description' => $this->description,
            'commission' => $this->commission,
            'costCentre' => $this->costCentre,
            'currencyCode' => $this->currencyCode,
            'netShippingCosts' => $this->netShippingCosts,
            'internalNote' => $this->getInternalNote(),
            'responsibleContact' => $this->createDataArray($this->responsibleContact),
            'items' => $this->itemsToArray(),
        ];
    }

    /**
     * @return string
     */
    public function getCurrencyCode()
    {
        return $this->currencyCode;
    }

    /**
     * @param string $currencyCode
     */
    public function setCurrencyCode($currencyCode)
    {
        $this->currencyCode = $currencyCode;
    }

    /**
     * Get property of an array.
     *
     * @param string $key
     * @param array $data
     * @param mixed|null $default
     *
     * @return mixed
     */
    protected function getProperty($key, $data, $default = null)
    {
        if (isset($data[$key])) {
            return $data[$key];
        }

        return $default;
    }

    /**
     * Calls toArray function on a certain object, if method exists.
     *
     * @param mixed $object
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
}
