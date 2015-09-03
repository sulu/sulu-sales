<?php

namespace Sulu\Bundle\Sales\ShippingBundle\Entity;

use Sulu\Bundle\Sales\CoreBundle\Entity\OrderAddress;
use Sulu\Component\Security\Authentication\UserInterface;

class Shipping
{
    /**
     * @var string
     */
    private $number;

    /**
     * @var string
     */
    private $shippingNumber;

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
     * @var float
     */
    private $width;

    /**
     * @var float
     */
    private $height;

    /**
     * @var float
     */
    private $length;

    /**
     * @var float
     */
    private $weight;

    /**
     * @var string
     */
    private $trackingId;

    /**
     * @var string
     */
    private $trackingUrl;

    /**
     * @var string
     */
    private $commission;

    /**
     * @var string
     */
    private $note;

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
    private $expectedDeliveryDate;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var OrderAddress
     */
    private $deliveryAddress;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $shippingItems;

    /**
     * @var \Sulu\Bundle\Sales\ShippingBundle\Entity\ShippingStatus
     */
    private $status;

    /**
     * @var \Sulu\Bundle\Sales\OrderBundle\Entity\Order
     */
    private $order;

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
     * Constructor
     */
    public function __construct()
    {
        $this->shippingItems = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Set number
     *
     * @param string $number
     * @return Shipping
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
     * Set shippingNumber
     *
     * @param string $shippingNumber
     * @return Shipping
     */
    public function setShippingNumber($shippingNumber)
    {
        $this->shippingNumber = $shippingNumber;
    
        return $this;
    }

    /**
     * Get shippingNumber
     *
     * @return string 
     */
    public function getShippingNumber()
    {
        return $this->shippingNumber;
    }

    /**
     * Set customerName
     *
     * @param string $customerName
     * @return Shipping
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
     * @return Shipping
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
     * Set width
     *
     * @param float $width
     * @return Shipping
     */
    public function setWidth($width)
    {
        $this->width = $width;
    
        return $this;
    }

    /**
     * Get width
     *
     * @return float 
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * Set height
     *
     * @param float $height
     * @return Shipping
     */
    public function setHeight($height)
    {
        $this->height = $height;
    
        return $this;
    }

    /**
     * Get height
     *
     * @return float 
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * Set length
     *
     * @param float $length
     * @return Shipping
     */
    public function setLength($length)
    {
        $this->length = $length;
    
        return $this;
    }

    /**
     * Get length
     *
     * @return float 
     */
    public function getLength()
    {
        return $this->length;
    }

    /**
     * Set weight
     *
     * @param float $weight
     * @return Shipping
     */
    public function setWeight($weight)
    {
        $this->weight = $weight;
    
        return $this;
    }

    /**
     * Get weight
     *
     * @return float 
     */
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     * Set trackingId
     *
     * @param string $trackingId
     * @return Shipping
     */
    public function setTrackingId($trackingId)
    {
        $this->trackingId = $trackingId;
    
        return $this;
    }

    /**
     * Get trackingId
     *
     * @return string 
     */
    public function getTrackingId()
    {
        return $this->trackingId;
    }

    /**
     * Set trackingUrl
     *
     * @param string $trackingUrl
     * @return Shipping
     */
    public function setTrackingUrl($trackingUrl)
    {
        $this->trackingUrl = $trackingUrl;
    
        return $this;
    }

    /**
     * Get trackingUrl
     *
     * @return string 
     */
    public function getTrackingUrl()
    {
        return $this->trackingUrl;
    }

    /**
     * Set commission
     *
     * @param string $commission
     * @return Shipping
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
     * Set note
     *
     * @param string $note
     * @return Shipping
     */
    public function setNote($note)
    {
        $this->note = $note;
    
        return $this;
    }

    /**
     * Get note
     *
     * @return string 
     */
    public function getNote()
    {
        return $this->note;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     * @return Shipping
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
     * @return Shipping
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
     * Set expectedDeliveryDate
     *
     * @param \DateTime $expectedDeliveryDate
     * @return Shipping
     */
    public function setExpectedDeliveryDate($expectedDeliveryDate)
    {
        $this->expectedDeliveryDate = $expectedDeliveryDate;
    
        return $this;
    }

    /**
     * Get expectedDeliveryDate
     *
     * @return \DateTime 
     */
    public function getExpectedDeliveryDate()
    {
        return $this->expectedDeliveryDate;
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
     * @param OrderAddress $deliveryAddress
     * @return Shipping
     */
    public function setDeliveryAddress(OrderAddress $deliveryAddress = null)
    {
        $this->deliveryAddress = $deliveryAddress;
    
        return $this;
    }

    /**
     * Get deliveryAddress
     *
     * @return OrderAddress
     */
    public function getDeliveryAddress()
    {
        return $this->deliveryAddress;
    }

    /**
     * Add shippingItems
     *
     * @param \Sulu\Bundle\Sales\ShippingBundle\Entity\ShippingItem $shippingItems
     * @return Shipping
     */
    public function addShippingItem(\Sulu\Bundle\Sales\ShippingBundle\Entity\ShippingItem $shippingItems)
    {
        $this->shippingItems[] = $shippingItems;
    
        return $this;
    }

    /**
     * Remove shippingItems
     *
     * @param \Sulu\Bundle\Sales\ShippingBundle\Entity\ShippingItem $shippingItems
     */
    public function removeShippingItem(\Sulu\Bundle\Sales\ShippingBundle\Entity\ShippingItem $shippingItems)
    {
        $this->shippingItems->removeElement($shippingItems);
    }

    /**
     * Get shippingItems
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getShippingItems()
    {
        return $this->shippingItems;
    }

    /**
     * Set status
     *
     * @param \Sulu\Bundle\Sales\ShippingBundle\Entity\ShippingStatus $status
     * @return Shipping
     */
    public function setStatus(\Sulu\Bundle\Sales\ShippingBundle\Entity\ShippingStatus $status)
    {
        $this->status = $status;
    
        return $this;
    }

    /**
     * Get status
     *
     * @return \Sulu\Bundle\Sales\ShippingBundle\Entity\ShippingStatus 
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set order
     *
     * @param \Sulu\Bundle\Sales\OrderBundle\Entity\Order $order
     * @return Shipping
     */
    public function setOrder(\Sulu\Bundle\Sales\OrderBundle\Entity\Order $order = null)
    {
        $this->order = $order;
    
        return $this;
    }

    /**
     * Get order
     *
     * @return \Sulu\Bundle\Sales\OrderBundle\Entity\Order 
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * Set changer
     *
     * @param UserInterface $changer
     * @return Shipping
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
     * @return Shipping
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
     * @return Shipping
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
     * Set termsOfPaymentContent
     *
     * @param string $termsOfPaymentContent
     * @return Shipping
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
}
