<?php
/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\Sales\OrderBundle\Entity;

use Sulu\Component\Contact\Model\ContactInterface;
use Sulu\Component\Security\Authentication\UserInterface;

abstract class BaseOrder implements OrderInterface
{
    /**
     * @var string
     */
    protected $number;

    /**
     * @var int
     */
    protected $orderNumber;

    /**
     * @var string
     */
    protected $currencyCode;

    /**
     * @var boolean
     */
    protected $taxfree = false;

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
    protected $customerName;

    /**
     * @var \DateTime
     */
    protected $created;

    /**
     * @var \DateTime
     */
    protected $changed;

    /**
     * @var \DateTime
     */
    protected $desiredDeliveryDate;

    /**
     * @var integer
     */
    protected $id;

    /**
     * @var ContactInterface
     */
    protected $customerContact;

    /**
     * @var UserInterface
     */
    protected $changer;

    /**
     * @var UserInterface
     */
    protected $creator;

    /**
     * @var float
     */
    protected $totalPrice;

    /**
     * @var float
     */
    protected $totalNetPrice;

    /**
     * @var float
     */
    protected $totalRecurringPrice;

    /**
     * @var float
     */
    protected $totalRecurringNetPrice;

    /**
     * @var \DateTime
     */
    protected $orderDate;

    /**
     * @var float
     */
    protected $netDeliveryCost = 0;

    /**
     * @var float
     */
    protected $deliveryCost = 0;

    /**
     * {@inheritDoc}
     */
    public function setNumber($number)
    {
        $this->number = $number;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * {@inheritDoc}
     */
    public function setOrderNumber($orderNumber)
    {
        $this->orderNumber = $orderNumber;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getOrderNumber()
    {
        return $this->orderNumber;
    }

    /**
     * {@inheritDoc}
     */
    public function setCurrencyCode($currencyCode)
    {
        $this->currencyCode = $currencyCode;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getCurrencyCode()
    {
        return $this->currencyCode;
    }

    /**
     * {@inheritDoc}
     */
    public function setTaxfree($taxfree)
    {
        $this->taxfree = $taxfree;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getTaxfree()
    {
        return $this->taxfree;
    }

    /**
     * {@inheritDoc}
     */
    public function setCostCentre($costCentre)
    {
        $this->costCentre = $costCentre;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getCostCentre()
    {
        return $this->costCentre;
    }

    /**
     * {@inheritDoc}
     */
    public function setCommission($commission)
    {
        $this->commission = $commission;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getCommission()
    {
        return $this->commission;
    }

    /**
     * {@inheritDoc}
     */
    public function setCustomerName($customerName)
    {
        $this->customerName = $customerName;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getCustomerName()
    {
        return $this->customerName;
    }

    /**
     * {@inheritDoc}
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * {@inheritDoc}
     */
    public function setChanged($changed)
    {
        $this->changed = $changed;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getChanged()
    {
        return $this->changed;
    }

    /**
     * {@inheritDoc}
     */
    public function setDesiredDeliveryDate($desiredDeliveryDate)
    {
        $this->desiredDeliveryDate = $desiredDeliveryDate;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getDesiredDeliveryDate()
    {
        return $this->desiredDeliveryDate;
    }

    /**
     * {@inheritDoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritDoc}
     */
    public function setCustomerContact(ContactInterface $contact = null)
    {
        $this->customerContact = $contact;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getCustomerContact()
    {
        return $this->customerContact;
    }

    /**
     * {@inheritDoc}
     */
    public function setChanger(UserInterface $changer = null)
    {
        $this->changer = $changer;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getChanger()
    {
        return $this->changer;
    }

    /**
     * {@inheritDoc}
     */
    public function setCreator(UserInterface $creator = null)
    {
        $this->creator = $creator;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getCreator()
    {
        return $this->creator;
    }

    /**
     * {@inheritDoc}
     */
    public function setTotalNetPrice($totalNetPrice)
    {
        $this->totalNetPrice = $totalNetPrice;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getTotalNetPrice()
    {
        return $this->totalNetPrice;
    }

    /**
     * {@inheritDoc}
     */
    public function setTotalPrice($totalPrice)
    {
        $this->totalPrice = $totalPrice;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getTotalPrice()
    {
        return $this->totalNetPrice;
    }

    /**
     * {@inheritDoc}
     */
    public function setOrderDate($orderDate)
    {
        $this->orderDate = $orderDate;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getOrderDate()
    {
        return $this->orderDate;
    }

    /**
     * FIXME: this function does not really belong here
     *
     * Updates the total net price
     */
    public function updateTotalNetPrice()
    {
        if (!$this->getItems()) {
            return;
        }

        $sum = 0;
        foreach ($this->getItems() as $item) {
            $sum += $item->getTotalNetPrice();
        }
        $this->setTotalNetPrice($sum);
    }

    /**
     * {@inheritDoc}
     */
    public function setDeliveryCost($deliveryCost = 0)
    {
        $this->deliveryCost = $deliveryCost;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getDeliveryCost()
    {
        return $this->deliveryCost;
    }

    /**
     * {@inheritDoc}
     */
    public function setNetDeliveryCost($netDeliveryCost = 0)
    {
        $this->netDeliveryCost = $netDeliveryCost;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getNetDeliveryCost()
    {
        return $this->deliveryCost;
    }

    /**
     * {@inheritDoc}
     */
    public function getTotalRecurringNetPrice()
    {
        return $this->totalRecurringNetPrice;
    }

    /**
     * {@inheritDoc}
     */
    public function setTotalRecurringNetPrice($totalRecurringNetPrice)
    {
        $this->totalRecurringNetPrice = $totalRecurringNetPrice;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getTotalRecurringPrice()
    {
        return $this->totalRecurringPrice;
    }

    /**
     * {@inheritDoc}
     */
    public function setTotalRecurringPrice($totalRecurringPrice)
    {
        $this->totalRecurringPrice = $totalRecurringPrice;

        return $this;
    }
}
