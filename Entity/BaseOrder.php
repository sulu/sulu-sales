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

use Sulu\Bundle\ContactBundle\Entity\Contact;
use Sulu\Bundle\Sales\CoreBundle\Entity\ItemInterface;
use Sulu\Bundle\Sales\CoreBundle\Entity\OrderAddressInterface;
use Sulu\Component\Security\Authentication\UserInterface;

abstract class BaseOrder implements OrderInterface
{
    /**
     * @var string
     */
    private $number;

    /**
     * @var int
     */
    private $orderNumber;

    /**
     * @var string
     */
    private $currency;

    /**
     * @var boolean
     */
    private $taxfree;

    /**
     * @var string
     */
    private $costCentre;

    /**
     * @var string
     */
    private $commission;

    /**
     * @var string
     */
    private $customerName;

    /**
     * @var \DateTime
     */
    private $created;

    /**
     * @var \DateTime
     */
    private $changed;

    /**
     * @var \DateTime
     */
    private $desiredDeliveryDate;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var OrderAddressInterface
     */
    private $deliveryAddress;

    /**
     * @var OrderAddressInterface
     */
    private $invoiceAddress;

    /**
     * @var OrderStatusInterface
     */
    private $status;

    /**
     * @var Contact
     */
    private $contact;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $items;

    /**
     * @var UserInterface
     */
    private $changer;

    /**
     * @var UserInterface
     */
    private $creator;

    /**
     * @var integer
     */
    private $bitmaskStatus;

    /**
     * @var float
     */
    private $totalNetPrice;

    /**
     * @var \DateTime
     */
    private $orderDate;

    /**
     * @var float
     */
    private $deliveryCost;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->items = new \Doctrine\Common\Collections\ArrayCollection();
    }

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
    public function setCurrency($currency)
    {
        $this->currency = $currency;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getCurrency()
    {
        return $this->currency;
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
    public function setDeliveryAddress(OrderAddressInterface $deliveryAddress = null)
    {
        $this->deliveryAddress = $deliveryAddress;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getDeliveryAddress()
    {
        return $this->deliveryAddress;
    }

    /**
     * {@inheritDoc}
     */
    public function setInvoiceAddress(OrderAddressInterface $invoiceAddress = null)
    {
        $this->invoiceAddress = $invoiceAddress;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getInvoiceAddress()
    {
        return $this->invoiceAddress;
    }

    /**
     * {@inheritDoc}
     */
    public function setStatus(OrderStatusInterface $status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * {@inheritDoc}
     */
    public function setContact(Contact $contact = null)
    {
        $this->contact = $contact;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getContact()
    {
        return $this->contact;
    }

    /**
     * {@inheritDoc}
     */
    public function addItem(ItemInterface $items)
    {
        $this->items[] = $items;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function removeItem(ItemInterface $items)
    {
        $this->items->removeElement($items);
    }

    /**
     * {@inheritDoc}
     */
    public function getItems()
    {
        return $this->items;
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
    public function setBitmaskStatus($bitmaskStatus)
    {
        $this->bitmaskStatus = $bitmaskStatus;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getBitmaskStatus()
    {
        return $this->bitmaskStatus;
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
    public function setDeliveryCost($deliveryCost)
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
}
