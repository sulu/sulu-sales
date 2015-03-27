<?php

namespace Sulu\Bundle\Sales\ShippingBundle\Api;

use DateTime;
use JMS\Serializer\Annotation\VirtualProperty;
use Hateoas\Configuration\Annotation\Relation;
use JMS\Serializer\Annotation\SerializedName;
use JMS\Serializer\Annotation\Exclude;
use Sulu\Bundle\Sales\CoreBundle\Core\SalesDocument;
use Sulu\Bundle\Sales\CoreBundle\Entity\OrderAddress;
use Sulu\Component\Rest\ApiWrapper;
use Sulu\Component\Security\Authentication\UserInterface;
use Sulu\Bundle\Sales\CoreBundle\Api\Item;
use Sulu\Bundle\Sales\OrderBundle\Api\Order;
use Sulu\Bundle\Sales\ShippingBundle\Entity\Shipping as ShippingEntity;
use Sulu\Bundle\Sales\ShippingBundle\Entity\ShippingItem as ShippingItemEntity;

/**
 * The Shipping class which will be exported to the API
 *
 * @package Sulu\Bundle\Sales\ShippingBundle\Api
 * @Relation("self", href="expr('/api/admin/shippings/' ~ object.getId())")
 */
class Shipping extends ApiWrapper implements SalesDocument
{
    public static $pdfBaseUrl = '/admin/shipping/pdf/';

    /**
     * @Exclude
     */
    private $shippingItems;

    /**
     * @param ShippingEntity $shipping The shipping to wrap
     * @param string $locale The locale of this shipping
     */
    public function __construct(ShippingEntity $shipping, $locale)
    {
        $this->entity = $shipping;
        $this->locale = $locale;
    }

    /**
     * Set number
     *
     * @param string $number
     * @return Shipping
     */
    public function setNumber($number)
    {
        $this->entity->setNumber($number);

        return $this;
    }

    /**
     * @return string
     * @VirtualProperty
     * @SerializedName("number")
     */
    public function getNumber()
    {
        return $this->entity->getNumber();
    }

    /**
     * Set shippingNumber
     *
     * @param string $shippingNumber
     * @return Shipping
     */
    public function setShippingNumber($shippingNumber)
    {
        $this->entity->setShippingNumber($shippingNumber);

        return $this;
    }

    /**
     * Get shippingNumber
     *
     * @return string
     * @VirtualProperty
     * @SerializedName("shippingNumber")
     */
    public function getShippingNumber()
    {
        return $this->entity->getShippingNumber();
    }

    /**
     * Set customerName
     *
     * @param string $customerName
     * @return Shipping
     */
    public function setCustomerName($customerName)
    {
        $this->entity->setCustomerName($customerName);

        return $this;
    }

    /**
     * Get customerName
     *
     * @return string
     * @VirtualProperty
     * @SerializedName("customerName")
     */
    public function getCustomerName()
    {
        return $this->entity->getCustomerName();
    }

    /**
     * Set termsOfDeliveryContent
     *
     * @param string $termsOfDeliveryContent
     * @return Shipping
     */
    public function setTermsOfDeliveryContent($termsOfDeliveryContent)
    {
        $this->entity->setTermsOfDeliveryContent($termsOfDeliveryContent);

        return $this;
    }

    /**
     * Get termsOfDeliveryContent
     *
     * @return string
     * @VirtualProperty
     * @SerializedName("termsOfDeliveryContent")
     */
    public function getTermsOfDeliveryContent()
    {
        return $this->entity->getTermsOfDeliveryContent();
    }

    /**
     * Set termsOfPaymentContent
     *
     * @param string $termsOfPaymentContent
     * @return Shipping
     */
    public function setTermsOfPaymentContent($termsOfPaymentContent)
    {
        $this->entity->setTermsOfPaymentContent($termsOfPaymentContent);

        return $this;
    }

    /**
     * Get termsOfPaymentContent
     *
     * @return string
     * @VirtualProperty
     * @SerializedName("termsOfPaymentContent")
     */
    public function getTermsOfPaymentContent()
    {
        return $this->entity->getTermsOfPaymentContent();
    }

    /**
     * Set width
     *
     * @param float $width
     * @return Shipping
     */
    public function setWidth($width)
    {
        $this->entity->setWidth($width);

        return $this;
    }

    /**
     * Get width
     *
     * @return float
     * @VirtualProperty
     * @SerializedName("width")
     */
    public function getWidth()
    {
        return $this->entity->getWidth();
    }

    /**
     * Set height
     *
     * @param float $height
     * @return Shipping
     */
    public function setHeight($height)
    {
        $this->entity->setHeight($height);

        return $this;
    }

    /**
     * Get height
     *
     * @return float
     * @VirtualProperty
     * @SerializedName("height")
     */
    public function getHeight()
    {
        return $this->entity->getHeight();
    }

    /**
     * Set length
     *
     * @param float $length
     * @return Shipping
     *
     */
    public function setLength($length)
    {
        $this->entity->setLength($length);

        return $this;
    }

    /**
     * Get length
     *
     * @return float
     * @VirtualProperty
     * @SerializedName("length")
     */
    public function getLength()
    {
        return $this->entity->getLength();
    }

    /**
     * Set weight
     *
     * @param float $weight
     * @return Shipping
     */
    public function setWeight($weight)
    {
        $this->entity->setWeight($weight);

        return $this;
    }

    /**
     * Get weight
     *
     * @return float
     * @VirtualProperty
     * @SerializedName("weight")
     */
    public function getWeight()
    {
        return $this->entity->getWeight();
    }

    /**
     * Set trackingId
     *
     * @param string $trackingId
     * @return Shipping
     */
    public function setTrackingId($trackingId)
    {
        $this->entity->setTrackingId($trackingId);

        return $this;
    }

    /**
     * Get trackingId
     *
     * @return string
     * @VirtualProperty
     * @SerializedName("trackingId")
     */
    public function getTrackingId()
    {
        return $this->entity->getTrackingId();
    }

    /**
     * Set trackingUrl
     *
     * @param string $trackingUrl
     * @return Shipping
     */
    public function setTrackingUrl($trackingUrl)
    {
        $this->entity->setTrackingUrl($trackingUrl);

        return $this;
    }

    /**
     * Get trackingUrl
     *
     * @return string
     * @VirtualProperty
     * @SerializedName("trackingUrl")
     */
    public function getTrackingUrl()
    {
        return $this->entity->getTrackingUrl();
    }

    /**
     * Set commission
     *
     * @param string $commission
     * @return Shipping
     */
    public function setCommission($commission)
    {
        $this->entity->setCommission($commission);

        return $this;
    }

    /**
     * Get commission
     *
     * @return string
     * @VirtualProperty
     * @SerializedName("commission")
     */
    public function getCommission()
    {
        return $this->entity->getCommission();
    }

    /**
     * Set note
     *
     * @param string $note
     * @return Shipping
     */
    public function setNote($note)
    {
        $this->entity->setNote($note);

        return $this;
    }

    /**
     * Get note
     *
     * @return string
     * @VirtualProperty
     * @SerializedName("note")
     */
    public function getNote()
    {
        return $this->entity->getNote();
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     * @return Shipping
     */
    public function setCreated($created)
    {
        $this->entity->setCreated($created);

        return $this;
    }

    /**
     * Get created
     *
     * @return \DateTime
     * @VirtualProperty
     * @SerializedName("created")
     */
    public function getCreated()
    {
        return $this->entity->getCreated();
    }

    /**
     * Set changed
     *
     * @param \DateTime $changed
     * @return Shipping
     */
    public function setChanged($changed)
    {
        $this->entity->setChanged($changed);

        return $this;
    }

    /**
     * Get changed
     *
     * @return \DateTime
     * @VirtualProperty
     * @SerializedName("changed")
     */
    public function getChanged()
    {
        return $this->entity->getChanged();
    }

    /**
     * Set expectedDeliveryDate
     *
     * @param \DateTime $expectedDeliveryDate
     * @return Shipping
     */
    public function setExpectedDeliveryDate($expectedDeliveryDate)
    {
        $this->entity->setExpectedDeliveryDate($expectedDeliveryDate);

        return $this;
    }

    /**
     * Get expectedDeliveryDate
     *
     * @return \DateTime
     * @VirtualProperty
     * @SerializedName("expectedDeliveryDate")
     */
    public function getExpectedDeliveryDate()
    {
        return $this->entity->getExpectedDeliveryDate();
    }

    /**
     * Get id
     *
     * @return integer
     * @VirtualProperty
     * @SerializedName("id")
     */
    public function getId()
    {
        return $this->entity->getId();
    }

    /**
     * Set deliveryAddress
     *
     * @param OrderAddress $deliveryAddress
     * @return Shipping
     */
    public function setDeliveryAddress(OrderAddress $deliveryAddress = null)
    {
        $this->entity->setDeliveryAddress($deliveryAddress);

        return $this;
    }

    /**
     * Get deliveryAddress
     *
     * @return OrderAddress
     * @VirtualProperty
     * @SerializedName("deliveryAddress")
     */
    public function getDeliveryAddress()
    {
        return $this->entity->getDeliveryAddress();
    }

    /**
     * Add shippingItems
     *
     * @param \Sulu\Bundle\Sales\ShippingBundle\Entity\ShippingItem $shippingItems
     * @return Shipping
     */
    public function addShippingItem(ShippingItemEntity $shippingItems)
    {
        $this->entity->addShippingItem($shippingItems);

        return $this;
    }

    /**
     * Remove shippingItems
     *
     * @param \Sulu\Bundle\Sales\ShippingBundle\Entity\ShippingItem $shippingItems
     */
    public function removeShippingItem(ShippingItemEntity $shippingItems)
    {
        $this->entity->removeShippingItem($shippingItems);
    }

    /**
     * Get shippingItems
     *
     * @return Array
     * @VirtualProperty
     * @SerializedName("items")
     */
    public function getItems()
    {
        if (!$this->shippingItems) {
            $this->shippingItems = array();
            foreach ($this->entity->getShippingItems() as $shippingItem) {
                $this->shippingItems[] = new ShippingItem($shippingItem, $this->locale);
            }
        }

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
        $this->entity->setStatus($status);

        return $this;
    }

    /**
     * Get status
     *
     * @return \Sulu\Bundle\Sales\ShippingBundle\Entity\ShippingStatus|null
     * @VirtualProperty
     * @SerializedName("status")
     */
    public function getStatus()
    {
        if (!$this->entity->getStatus()) {
            return null;
        }

        return new ShippingStatus($this->entity->getStatus(), $this->locale);
    }

    /**
     * Set bitmaskStatus
     *
     * @param integer $bitmaskStatus
     * @return Shipping
     */
    public function setBitmaskStatus($bitmaskStatus)
    {
        $this->entity->setBitmaskStatus($bitmaskStatus);

        return $this;
    }

    /**
     * Get bitmaskStatus
     *
     * @VirtualProperty
     * @SerializedName("bitmaskStatus")
     * @return integer
     */
    public function getBitmaskStatus()
    {
        return $this->entity->getBitmaskStatus();
    }

    /**
     * Set order
     *
     * @param \Sulu\Bundle\Sales\OrderBundle\Entity\Order $order
     * @return Shipping
     */
    public function setOrder(\Sulu\Bundle\Sales\OrderBundle\Entity\Order $order = null)
    {
        $this->entity->setOrder($order);

        return $this;
    }

    /**
     * Get order
     *
     * @return \Sulu\Bundle\Sales\OrderBundle\Entity\Order
     * @VirtualProperty
     * @SerializedName("order")
     */
    public function getOrder()
    {
        return new Order($this->entity->getOrder(), $this->locale);
    }

    /**
     * Set changer
     *
     * @param UserInterface $changer
     * @return Shipping
     */
    public function setChanger(UserInterface $changer = null)
    {
        $this->entity->setChanger($changer);

        return $this;
    }

    /**
     * Get changer
     *
     * @return UserInterface
     */
    public function getChanger()
    {
        // TODO
//        return $this->entity->changer;
    }

    /**
     * Set creator
     *
     * @param UserInterface $creator
     * @return Shipping
     */
    public function setCreator(UserInterface $creator = null)
    {
        $this->entity->setCreator($creator);

        return $this;
    }

    /**
     * Get creator
     *
     * @return UserInterface
     */
    public function getCreator()
    {
        // TODO
//        return $this->entity->creator;
    }

    /**
     * returns the entities locale
     *
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * Returns the data needed for the sales document widget as array
     *
     * @return array
     */
    public function getSalesDocumentData()
    {
        return array(
            'number' => $this->getNumber(),
            'type' => 'shipping',
            'date' => $this->getExpectedDeliveryDate(),
            'id' => $this->getId(),
            'pdfBaseUrl' => $this->getPdfBaseUrl()
        );
    }

    /**
     * Returns url for generating the documents pdf
     * @return string
     */
    public function getPdfBaseUrl()
    {
        return self::$pdfBaseUrl;
    }

}
