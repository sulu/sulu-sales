<?php

namespace Sulu\Bundle\Sales\OrderBundle\Entity;

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
     * @var string
     */
    private $sessionId;

    /**
     * @var string
     */
    private $currency;

    /**
     * @var string
     */
    private $termsOfDelivery;

    /**
     * @var string
     */
    private $termsOfPayment;

    /**
     * @var string
     */
    private $costCentre;

    /**
     * @var string
     */
    private $commission;

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
     * @var boolean
     */
    private $taxfree;

    /**
     * @var integer
     */
    private $id;

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
     * Set termsOfDelivery
     *
     * @param string $termsOfDelivery
     * @return Order
     */
    public function setTermsOfDelivery($termsOfDelivery)
    {
        $this->termsOfDelivery = $termsOfDelivery;
    
        return $this;
    }

    /**
     * Get termsOfDelivery
     *
     * @return string 
     */
    public function getTermsOfDelivery()
    {
        return $this->termsOfDelivery;
    }

    /**
     * Set termsOfPayment
     *
     * @param string $termsOfPayment
     * @return Order
     */
    public function setTermsOfPayment($termsOfPayment)
    {
        $this->termsOfPayment = $termsOfPayment;
    
        return $this;
    }

    /**
     * Get termsOfPayment
     *
     * @return string 
     */
    public function getTermsOfPayment()
    {
        return $this->termsOfPayment;
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
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set status
     *
     * @param \Sulu\Bundle\Sales\OrderBundle\Entity\OrderStatus $status
     * @return Order
     */
    public function setStatus(\Sulu\Bundle\Sales\OrderBundle\Entity\OrderStatus $status = null)
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
     * @param UserInterface $changer
     * @return $this
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
     * @var \Sulu\Bundle\Sales\OrderBundle\Entity\OrderAddress
     */
    private $deliveryAddress;

    /**
     * @var \Sulu\Bundle\Sales\OrderBundle\Entity\OrderAddress
     */
    private $invoiceAddress;


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
}
