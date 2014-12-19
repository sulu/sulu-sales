<?php

namespace Sulu\Bundle\Sales\OrderBundle\Entity;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Mapping as ORM;
use Sulu\Component\Security\UserInterface;

/**
 * Order
 */
class Order
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
     * @var int
     */
    private $type;

    /**
     * @var string
     */
    private $sessionId;

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
     * @var string
     */
    private $termsOfDeliveryContent;

    /**
     * @var string
     */
    private $termsOfPaymentContent;

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
     * @var \Sulu\Bundle\Sales\OrderBundle\Entity\OrderAddress
     */
    private $deliveryAddress;

    /**
     * @var \Sulu\Bundle\Sales\OrderBundle\Entity\OrderAddress
     */
    private $invoiceAddress;

    /**
     * @var \Sulu\Bundle\ContactBundle\Entity\TermsOfDelivery
     */
    private $termsOfDelivery;

    /**
     * @var \Sulu\Bundle\ContactBundle\Entity\TermsOfPayment
     */
    private $termsOfPayment;

    /**
     * @var \Sulu\Bundle\Sales\OrderBundle\Entity\OrderStatus
     */
    private $status;

    /**
     * @var \Sulu\Bundle\ContactBundle\Entity\Account
     */
    private $account;

    /**
     * @var \Sulu\Bundle\ContactBundle\Entity\Contact
     */
    private $contact;

    /**
     * @var \Sulu\Bundle\ContactBundle\Entity\Contact
     */
    private $responsibleContact;

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
     * Constructor
     */
    public function __construct()
    {
        $this->items = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set number
     *
     * @param string $number
     * @return Order
     */
    public function setNumber($number)
    {
        $this->number = $number;

        return $this;
    }

    /**
     * Get number
     *
     * @return string
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * Set orderNumber
     *
     * @param string $orderNumber
     * @return Order
     */
    public function setOrderNumber($orderNumber)
    {
        $this->orderNumber = $orderNumber;

        return $this;
    }

    /**
     * Get orderNumber
     *
     * @return string
     */
    public function getOrderNumber()
    {
        return $this->orderNumber;
    }

    /**
     * Set type
     *
     * @param integer $type
     * @return Order
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return integer
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set sessionId
     *
     * @param string $sessionId
     * @return Order
     */
    public function setSessionId($sessionId)
    {
        $this->sessionId = $sessionId;

        return $this;
    }

    /**
     * Get sessionId
     *
     * @return string
     */
    public function getSessionId()
    {
        return $this->sessionId;
    }

    /**
     * Set currency
     *
     * @param string $currency
     * @return Order
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;

        return $this;
    }

    /**
     * Get currency
     *
     * @return string
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * Set taxfree
     *
     * @param boolean $taxfree
     * @return Order
     */
    public function setTaxfree($taxfree)
    {
        $this->taxfree = $taxfree;

        return $this;
    }

    /**
     * Get taxfree
     *
     * @return boolean
     */
    public function getTaxfree()
    {
        return $this->taxfree;
    }

    /**
     * Set costCentre
     *
     * @param string $costCentre
     * @return Order
     */
    public function setCostCentre($costCentre)
    {
        $this->costCentre = $costCentre;

        return $this;
    }

    /**
     * Get costCentre
     *
     * @return string
     */
    public function getCostCentre()
    {
        return $this->costCentre;
    }

    /**
     * Set commission
     *
     * @param string $commission
     * @return Order
     */
    public function setCommission($commission)
    {
        $this->commission = $commission;

        return $this;
    }

    /**
     * Get commission
     *
     * @return string
     */
    public function getCommission()
    {
        return $this->commission;
    }

    /**
     * Set customerName
     *
     * @param string $customerName
     * @return Order
     */
    public function setCustomerName($customerName)
    {
        $this->customerName = $customerName;

        return $this;
    }

    /**
     * Get customerName
     *
     * @return string
     */
    public function getCustomerName()
    {
        return $this->customerName;
    }

    /**
     * Set termsOfDeliveryContent
     *
     * @param string $termsOfDeliveryContent
     * @return Order
     */
    public function setTermsOfDeliveryContent($termsOfDeliveryContent)
    {
        $this->termsOfDeliveryContent = $termsOfDeliveryContent;

        return $this;
    }

    /**
     * Get termsOfDeliveryContent
     *
     * @return string
     */
    public function getTermsOfDeliveryContent()
    {
        return $this->termsOfDeliveryContent;
    }

    /**
     * Set termsOfPaymentContent
     *
     * @param string $termsOfPaymentContent
     * @return Order
     */
    public function setTermsOfPaymentContent($termsOfPaymentContent)
    {
        $this->termsOfPaymentContent = $termsOfPaymentContent;

        return $this;
    }

    /**
     * Get termsOfPaymentContent
     *
     * @return string
     */
    public function getTermsOfPaymentContent()
    {
        return $this->termsOfPaymentContent;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     * @return Order
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created
     *
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set changed
     *
     * @param \DateTime $changed
     * @return Order
     */
    public function setChanged($changed)
    {
        $this->changed = $changed;

        return $this;
    }

    /**
     * Get changed
     *
     * @return \DateTime
     */
    public function getChanged()
    {
        return $this->changed;
    }

    /**
     * Set desiredDeliveryDate
     *
     * @param \DateTime $desiredDeliveryDate
     * @return Order
     */
    public function setDesiredDeliveryDate($desiredDeliveryDate)
    {
        $this->desiredDeliveryDate = $desiredDeliveryDate;

        return $this;
    }

    /**
     * Get desiredDeliveryDate
     *
     * @return \DateTime
     */
    public function getDesiredDeliveryDate()
    {
        return $this->desiredDeliveryDate;
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set deliveryAddress
     *
     * @param \Sulu\Bundle\Sales\OrderBundle\Entity\OrderAddress $deliveryAddress
     * @return Order
     */
    public function setDeliveryAddress(\Sulu\Bundle\Sales\OrderBundle\Entity\OrderAddress $deliveryAddress = null)
    {
        $this->deliveryAddress = $deliveryAddress;

        return $this;
    }

    /**
     * Get deliveryAddress
     *
     * @return \Sulu\Bundle\Sales\OrderBundle\Entity\OrderAddress
     */
    public function getDeliveryAddress()
    {
        return $this->deliveryAddress;
    }

    /**
     * Set invoiceAddress
     *
     * @param \Sulu\Bundle\Sales\OrderBundle\Entity\OrderAddress $invoiceAddress
     * @return Order
     */
    public function setInvoiceAddress(\Sulu\Bundle\Sales\OrderBundle\Entity\OrderAddress $invoiceAddress = null)
    {
        $this->invoiceAddress = $invoiceAddress;

        return $this;
    }

    /**
     * Get invoiceAddress
     *
     * @return \Sulu\Bundle\Sales\OrderBundle\Entity\OrderAddress
     */
    public function getInvoiceAddress()
    {
        return $this->invoiceAddress;
    }

    /**
     * Set termsOfDelivery
     *
     * @param \Sulu\Bundle\ContactBundle\Entity\TermsOfDelivery $termsOfDelivery
     * @return Order
     */
    public function setTermsOfDelivery(\Sulu\Bundle\ContactBundle\Entity\TermsOfDelivery $termsOfDelivery = null)
    {
        $this->termsOfDelivery = $termsOfDelivery;

        return $this;
    }

    /**
     * Get termsOfDelivery
     *
     * @return \Sulu\Bundle\ContactBundle\Entity\TermsOfDelivery
     */
    public function getTermsOfDelivery()
    {
        return $this->termsOfDelivery;
    }

    /**
     * Set termsOfPayment
     *
     * @param \Sulu\Bundle\ContactBundle\Entity\TermsOfPayment $termsOfPayment
     * @return Order
     */
    public function setTermsOfPayment(\Sulu\Bundle\ContactBundle\Entity\TermsOfPayment $termsOfPayment = null)
    {
        $this->termsOfPayment = $termsOfPayment;

        return $this;
    }

    /**
     * Get termsOfPayment
     *
     * @return \Sulu\Bundle\ContactBundle\Entity\TermsOfPayment
     */
    public function getTermsOfPayment()
    {
        return $this->termsOfPayment;
    }

    /**
     * Set status
     *
     * @param \Sulu\Bundle\Sales\OrderBundle\Entity\OrderStatus $status
     * @return Order
     */
    public function setStatus(\Sulu\Bundle\Sales\OrderBundle\Entity\OrderStatus $status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return \Sulu\Bundle\Sales\OrderBundle\Entity\OrderStatus
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set account
     *
     * @param \Sulu\Bundle\ContactBundle\Entity\Account $account
     * @return Order
     */
    public function setAccount(\Sulu\Bundle\ContactBundle\Entity\Account $account = null)
    {
        $this->account = $account;

        return $this;
    }

    /**
     * Get account
     *
     * @return \Sulu\Bundle\ContactBundle\Entity\Account
     */
    public function getAccount()
    {
        return $this->account;
    }

    /**
     * Set contact
     *
     * @param \Sulu\Bundle\ContactBundle\Entity\Contact $contact
     * @return Order
     */
    public function setContact(\Sulu\Bundle\ContactBundle\Entity\Contact $contact = null)
    {
        $this->contact = $contact;

        return $this;
    }

    /**
     * Get contact
     *
     * @return \Sulu\Bundle\ContactBundle\Entity\Contact
     */
    public function getContact()
    {
        return $this->contact;
    }

    /**
     * Set responsibleContact
     *
     * @param \Sulu\Bundle\ContactBundle\Entity\Contact $responsibleContact
     * @return Order
     */
    public function setResponsibleContact(\Sulu\Bundle\ContactBundle\Entity\Contact $responsibleContact = null)
    {
        $this->responsibleContact = $responsibleContact;

        return $this;
    }

    /**
     * Get responsibleContact
     *
     * @return \Sulu\Bundle\ContactBundle\Entity\Contact
     */
    public function getResponsibleContact()
    {
        return $this->responsibleContact;
    }

    /**
     * Add items
     *
     * @param \Sulu\Bundle\Sales\CoreBundle\Entity\Item $items
     * @return Order
     */
    public function addItem(\Sulu\Bundle\Sales\CoreBundle\Entity\Item $items)
    {
        $this->items[] = $items;

        return $this;
    }

    /**
     * Remove items
     *
     * @param \Sulu\Bundle\Sales\CoreBundle\Entity\Item $items
     */
    public function removeItem(\Sulu\Bundle\Sales\CoreBundle\Entity\Item $items)
    {
        $this->items->removeElement($items);
    }

    /**
     * Get items
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * Set changer
     *
     * @param UserInterface $changer
     * @return Order
     */
    public function setChanger(UserInterface $changer = null)
    {
        $this->changer = $changer;

        return $this;
    }

    /**
     * Get changer
     *
     * @return UserInterface
     */
    public function getChanger()
    {
        return $this->changer;
    }

    /**
     * Set creator
     *
     * @param UserInterface $creator
     * @return Order
     */
    public function setCreator(UserInterface $creator = null)
    {
        $this->creator = $creator;

        return $this;
    }

    /**
     * Get creator
     *
     * @return UserInterface
     */
    public function getCreator()
    {
        return $this->creator;
    }

    /**
     * Set bitmaskStatus
     *
     * @param integer $bitmaskStatus
     * @return Order
     */
    public function setBitmaskStatus($bitmaskStatus)
    {
        $this->bitmaskStatus = $bitmaskStatus;

        return $this;
    }

    /**
     * Get bitmaskStatus
     *
     * @return integer
     */
    public function getBitmaskStatus()
    {
        return $this->bitmaskStatus;
    }

    /**
     * Set totalNetPrice
     *
     * @param float $totalNetPrice
     * @return Order
     */
    private function setTotalNetPrice($totalNetPrice)
    {
        $this->totalNetPrice = $totalNetPrice;

        return $this;
    }

    /**
     * Get totalNetPrice
     *
     * @return float
     */
    public function getTotalNetPrice()
    {
        return $this->totalNetPrice;
    }

    /**
     * Set orderDate
     *
     * @param \DateTime $orderDate
     * @return Order
     */
    public function setOrderDate($orderDate)
    {
        $this->orderDate = $orderDate;

        return $this;
    }

    /**
     * Get orderDate
     *
     * @return \DateTime
     */
    public function getOrderDate()
    {
        return $this->orderDate;
    }

    /**
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
}
