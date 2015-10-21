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
     * @var Account
     */
    protected $customer;

    /**
     * @var Account
     */
    protected $supplier;

    /**
     * @var float
     */
    protected $deliveryCost;

    /**
     * @var Contact
     */
    protected $responsiblePerson;

    /**
     * @var array
     */
    protected $items;

    public function __construct()
    {
        $this->items = [];
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
     * @return Contact
     */
    public function getResponsiblePerson()
    {
        return $this->responsiblePerson;
    }

    /**
     * @param Contact $responsiblePerson
     */
    public function setResponsiblePerson($responsiblePerson)
    {
        $this->responsiblePerson = $responsiblePerson;
    }

    /**
     * @return array
     */
    public function getItems()
    {
        return $this->items;
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
    public function setResponsiblePersonByData($data)
    {
        $supplier = new Contact($this->getProperty('id', $data), $this->getProperty('fullName', $data));
        $this->supplier = $supplier;
    }

    /**
     * @param array $data
     */
    public function setItemsByData($data)
    {
        foreach($data as $itemData) {
            $item = new Item();

            $item->setPrice($this->getProperty('price', $itemData));
            $item->setQuantity($this->getProperty('quantity', $itemData));
            $item->setQuantityUnit($this->getProperty('quantityUnit', $itemData));

            if ($itemData['product']) {
                $customer = new Product(
                    $this->getProperty('id', $itemData['product'])
                );
                $item->setCustomer($customer);
            }
            if ($itemData['customer']) {
                $customer = new Account(
                    $this->getProperty('id', $itemData['customer']),
                    $this->getProperty('name', $itemData['customer'])
                );
                $item->setCustomer($customer);
            }
            if ($itemData['supplier']) {
                $customer = new Account(
                    $this->getProperty('id', $itemData['supplier']),
                    $this->getProperty('name', $itemData['supplier'])
                );
                $item->setSupplier($customer);
            }
        }
    }

    /**
     * @param array $data
     */
    public function setResponsibleContactByData($data)
    {
        $contact = new Contact(
            $data['responsibleContact']['id'],
            $data['responsibleContact']['fullName']
        );
        $this->setResponsiblePerson($contact);
    }

    /**
     * @return array
     */
    public function itemsToArray()
    {
        $result = [];

        foreach ($this->items as $item) {
            $result[] = $item->toArray();
        }

        return $result;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'title' => $this->title,
            'description' => $this->description,
            'commission' => $this->commission,
            'costCentre' => $this->costCentre,
            'deliveryCost' => $this->deliveryCost,
            'items' => $this->,
        ];
    }

    protected function getProperty($key, $data, $default = null)
    {
        if (isset($key['data'])) {
            return $data[$key];
        }

        return $default;
    }
}
