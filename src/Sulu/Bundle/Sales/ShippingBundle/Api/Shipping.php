<?php

namespace Sulu\Bundle\Sales\ShippingBundle\Api;

use DateTime;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\VirtualProperty;
use Hateoas\Configuration\Annotation\Relation;
use JMS\Serializer\Annotation\SerializedName;
use JMS\Serializer\Annotation\Exclude;
use Sulu\Bundle\PricingBundle\Pricing\PriceFormatter;
use Sulu\Component\Rest\ApiWrapper;
use Sulu\Component\Security\Authentication\UserInterface;
use Sulu\Bundle\ProductBundle\Product\ProductFactory;
use Sulu\Bundle\Sales\CoreBundle\Core\SalesDocument;
use Sulu\Bundle\Sales\CoreBundle\Entity\OrderAddress;
use Sulu\Bundle\Sales\CoreBundle\Item\ItemFactoryInterface;
use Sulu\Bundle\Sales\CoreBundle\Item\ItemFactory;
use Sulu\Bundle\Sales\OrderBundle\Api\Order as ApiOrder;
use Sulu\Bundle\Sales\OrderBundle\Entity\Order;
use Sulu\Bundle\Sales\ShippingBundle\Entity\Shipping as ShippingEntity;
use Sulu\Bundle\Sales\ShippingBundle\Entity\ShippingItem as ShippingItemEntity;
use Sulu\Bundle\Sales\ShippingBundle\Entity\ShippingStatus;
use Sulu\Bundle\Sales\ShippingBundle\Api\ShippingStatus as ApiShippingStatus;

/**
 * The Shipping class which will be exported to the API
 *
 * @package Sulu\Bundle\Sales\ShippingBundle\Api
 * @Relation("self", href="expr('/api/admin/shippings/' ~ object.getId())")
 * @ExclusionPolicy("all")
 */
class Shipping extends ApiWrapper implements SalesDocument
{
    public static $pdfBaseUrl = '/admin/shipping/pdf/';

    /**
     * @Exclude
     */
    private $shippingItems;

    /**
     * @var ItemFactoryInterface
     */
    private $itemFactory;

    /**
     * @var PriceFormatter
     */
    private $priceFormatter;

    /**
     * @param ShippingEntity $shipping The shipping to wrap
     * @param string $locale The locale of this shipping
     * @param ItemFactoryInterface $itemFactory
     * @param PriceFormatter $priceFormatter
     */
    public function __construct(
        ShippingEntity $shipping,
        $locale,
        ItemFactoryInterface $itemFactory,
        PriceFormatter $priceFormatter
    ) {
        $this->entity = $shipping;
        $this->locale = $locale;
        $this->itemFactory = $itemFactory;
        $this->priceFormatter = $priceFormatter;
    }

    /**
     * Set number
     *
     * @param string $number
     *
     * @return Shipping
     */
    public function setNumber($number)
    {
        $this->entity->setNumber($number);

        return $this;
    }

    /**
     * @VirtualProperty
     * @SerializedName("number")
     *
     * @return string
     */
    public function getNumber()
    {
        return $this->entity->getNumber();
    }

    /**
     * Set shippingNumber
     *
     * @param string $shippingNumber
     *
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
     * @VirtualProperty
     * @SerializedName("shippingNumber")
     *
     * @return string
     */
    public function getShippingNumber()
    {
        return $this->entity->getShippingNumber();
    }

    /**
     * Set customerName
     *
     * @param string $customerName
     *
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
     * @VirtualProperty
     * @SerializedName("customerName")
     *
     * @return string
     */
    public function getCustomerName()
    {
        return $this->entity->getCustomerName();
    }

    /**
     * Set termsOfDeliveryContent
     *
     * @param string $termsOfDeliveryContent
     *
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
     * @VirtualProperty
     * @SerializedName("termsOfDeliveryContent")
     *
     * @return string
     */
    public function getTermsOfDeliveryContent()
    {
        return $this->entity->getTermsOfDeliveryContent();
    }

    /**
     * Set termsOfPaymentContent
     *
     * @param string $termsOfPaymentContent
     *
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
     * @VirtualProperty
     * @SerializedName("termsOfPaymentContent")
     *
     * @return string
     */
    public function getTermsOfPaymentContent()
    {
        return $this->entity->getTermsOfPaymentContent();
    }

    /**
     * Set width
     *
     * @param float $width
     *
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
     * @VirtualProperty
     * @SerializedName("width")
     *
     * @return float
     */
    public function getWidth()
    {
        return $this->entity->getWidth();
    }

    /**
     * Set height
     *
     * @param float $height
     *
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
     * @VirtualProperty
     * @SerializedName("height")
     *
     * @return float
     */
    public function getHeight()
    {
        return $this->entity->getHeight();
    }

    /**
     * Set length
     *
     * @param float $length
     *
     * @return Shipping
     */
    public function setLength($length)
    {
        $this->entity->setLength($length);

        return $this;
    }

    /**
     * Get length
     *
     * @VirtualProperty
     * @SerializedName("length")
     *
     * @return float
     */
    public function getLength()
    {
        return $this->entity->getLength();
    }

    /**
     * Set weight
     *
     * @param float $weight
     *
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
     * @VirtualProperty
     * @SerializedName("weight")
     *
     * @return float
     */
    public function getWeight()
    {
        return $this->entity->getWeight();
    }

    /**
     * Set trackingId
     *
     * @param string $trackingId
     *
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
     * @VirtualProperty
     * @SerializedName("trackingId")
     *
     * @return string
     */
    public function getTrackingId()
    {
        return $this->entity->getTrackingId();
    }

    /**
     * Set trackingUrl
     *
     * @param string $trackingUrl
     *
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
     * @VirtualProperty
     * @SerializedName("trackingUrl")
     *
     * @return string
     */
    public function getTrackingUrl()
    {
        return $this->entity->getTrackingUrl();
    }

    /**
     * Set commission
     *
     * @param string $commission
     *
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
     * @VirtualProperty
     * @SerializedName("commission")
     *
     * @return string
     */
    public function getCommission()
    {
        return $this->entity->getCommission();
    }

    /**
     * Set note
     *
     * @param string $note
     *
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
     * @VirtualProperty
     * @SerializedName("note")
     *
     * @return string
     */
    public function getNote()
    {
        return $this->entity->getNote();
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     *
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
     * @VirtualProperty
     * @SerializedName("created")
     *
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->entity->getCreated();
    }

    /**
     * Set changed
     *
     * @param \DateTime $changed
     *
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
     * @VirtualProperty
     * @SerializedName("changed")
     *
     * @return \DateTime
     */
    public function getChanged()
    {
        return $this->entity->getChanged();
    }

    /**
     * Set expectedDeliveryDate
     *
     * @param \DateTime $expectedDeliveryDate
     *
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
     * @VirtualProperty
     * @SerializedName("expectedDeliveryDate")
     *
     * @return \DateTime
     */
    public function getExpectedDeliveryDate()
    {
        return $this->entity->getExpectedDeliveryDate();
    }

    /**
     * Get id
     *
     * @VirtualProperty
     * @SerializedName("id")
     *
     * @return integer
     */
    public function getId()
    {
        return $this->entity->getId();
    }

    /**
     * Set deliveryAddress
     *
     * @param OrderAddress $deliveryAddress
     *
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
     * @VirtualProperty
     * @SerializedName("deliveryAddress")
     *
     * @return OrderAddress
     */
    public function getDeliveryAddress()
    {
        return $this->entity->getDeliveryAddress();
    }

    /**
     * Add shippingItems
     *
     * @param ShippingItemEntity $shippingItems
     *
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
     * @param ShippingItemEntity $shippingItems
     */
    public function removeShippingItem(ShippingItemEntity $shippingItems)
    {
        $this->entity->removeShippingItem($shippingItems);
    }

    /**
     * Get shippingItems
     *
     * @VirtualProperty
     * @SerializedName("items")
     *
     * @return array
     */
    public function getItems()
    {
        if (!$this->shippingItems) {
            $this->shippingItems = array();
            foreach ($this->entity->getShippingItems() as $shippingItem) {
                $this->shippingItems[] = new ShippingItem(
                    $shippingItem,
                    $this->locale,
                    $this->itemFactory,
                    $this->priceFormatter
                );
            }
        }

        return $this->shippingItems;
    }

    /**
     * Set status
     *
     * @param ShippingStatus $status
     * @return Shipping
     */
    public function setStatus(ShippingStatus $status)
    {
        $this->entity->setStatus($status);

        return $this;
    }

    /**
     * Get status
     *
     * @VirtualProperty
     * @SerializedName("status")
     *
     * @return ShippingStatus|null
     */
    public function getStatus()
    {
        if (!$this->entity->getStatus()) {
            return null;
        }

        return new ApiShippingStatus($this->entity->getStatus(), $this->locale);
    }

    /**
     * Set bitmaskStatus
     *
     * @param integer $bitmaskStatus
     *
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
     *
     * @return integer
     */
    public function getBitmaskStatus()
    {
        return $this->entity->getBitmaskStatus();
    }

    /**
     * Set order
     *
     * @param Order $order
     *
     * @return Shipping
     */
    public function setOrder(Order $order = null)
    {
        $this->entity->setOrder($order);

        return $this;
    }

    /**
     * Get order
     *
     * @VirtualProperty
     * @SerializedName("order")
     *
     * @return ApiOrder
     */
    public function getOrder()
    {
        return new ApiOrder($this->entity->getOrder(), $this->locale, $this->itemFactory, $this->priceFormatter);
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
        return $this->entity->getChanger();
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
        return $this->entity->getCreator();
    }

    /**
     * @VirtualProperty
     * @SerializedName("internalNote")
     *
     * @return string
     */
    public function getInternalNote()
    {
        return $this->entity->getInternalNote();
    }

    /**
     * @param string $note
     *
     * @return Inquiry
     */
    public function setInternalNote($note)
    {
        $this->entity->setInternalNote($note);

        return $this;
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
            'icon' => 'fa-truck',
            'date' => $this->getExpectedDeliveryDate(),
            'id' => $this->getId(),
            'pdfBaseUrl' => null,
            'translationKey' => 'salescore.shipping',
        );
    }

    /**
     * Returns url for generating the documents pdf
     *
     * @return string
     */
    public function getPdfBaseUrl()
    {
        return self::$pdfBaseUrl;
    }
}
