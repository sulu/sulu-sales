<?php

namespace Sulu\Bundle\Sales\ShippingBundle\Api;

use DateTime;
use Hateoas\Configuration\Annotation\Relation;
use JMS\Serializer\Annotation\Exclude;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\SerializedName;
use JMS\Serializer\Annotation\VirtualProperty;
use Sulu\Bundle\PricingBundle\Pricing\PriceFormatter;
use Sulu\Bundle\Sales\CoreBundle\Core\SalesDocument;
use Sulu\Bundle\Sales\CoreBundle\Entity\OrderAddress;
use Sulu\Bundle\Sales\CoreBundle\Item\ItemFactoryInterface;
use Sulu\Bundle\Sales\OrderBundle\Api\Order as ApiOrder;
use Sulu\Bundle\Sales\OrderBundle\Entity\Order;
use Sulu\Bundle\Sales\ShippingBundle\Api\ShippingStatus as ApiShippingStatus;
use Sulu\Bundle\Sales\ShippingBundle\Entity\Shipping as ShippingEntity;
use Sulu\Bundle\Sales\ShippingBundle\Entity\ShippingItem as ShippingItemEntity;
use Sulu\Bundle\Sales\ShippingBundle\Entity\ShippingStatus;
use Sulu\Component\Rest\ApiWrapper;
use Sulu\Component\Security\Authentication\UserInterface;

/**
 * The Shipping class which will be exported to the API
 *
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
     * @param string $number
     *
     * @return self
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
     * @param string $shippingNumber
     *
     * @return self
     */
    public function setShippingNumber($shippingNumber)
    {
        $this->entity->setShippingNumber($shippingNumber);

        return $this;
    }

    /**
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
     * @param string $customerName
     *
     * @return self
     */
    public function setCustomerName($customerName)
    {
        $this->entity->setCustomerName($customerName);

        return $this;
    }

    /**
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
     * @param string $termsOfDeliveryContent
     *
     * @return self
     */
    public function setTermsOfDeliveryContent($termsOfDeliveryContent)
    {
        $this->entity->setTermsOfDeliveryContent($termsOfDeliveryContent);

        return $this;
    }

    /**
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
     * @param string $termsOfPaymentContent
     *
     * @return self
     */
    public function setTermsOfPaymentContent($termsOfPaymentContent)
    {
        $this->entity->setTermsOfPaymentContent($termsOfPaymentContent);

        return $this;
    }

    /**
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
     * @param float $width
     *
     * @return self
     */
    public function setWidth($width)
    {
        $this->entity->setWidth($width);

        return $this;
    }

    /**
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
     * @param float $height
     *
     * @return self
     */
    public function setHeight($height)
    {
        $this->entity->setHeight($height);

        return $this;
    }

    /**
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
     * @param float $length
     *
     * @return self
     */
    public function setLength($length)
    {
        $this->entity->setLength($length);

        return $this;
    }

    /**
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
     * @param float $weight
     *
     * @return self
     */
    public function setWeight($weight)
    {
        $this->entity->setWeight($weight);

        return $this;
    }

    /**
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
     * @param string $trackingId
     *
     * @return self
     */
    public function setTrackingId($trackingId)
    {
        $this->entity->setTrackingId($trackingId);

        return $this;
    }

    /**
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
     * @param string $trackingUrl
     *
     * @return self
     */
    public function setTrackingUrl($trackingUrl)
    {
        $this->entity->setTrackingUrl($trackingUrl);

        return $this;
    }

    /**
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
     * @param string $commission
     *
     * @return self
     */
    public function setCommission($commission)
    {
        $this->entity->setCommission($commission);

        return $this;
    }

    /**
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
     * @param string $note
     *
     * @return self
     */
    public function setNote($note)
    {
        $this->entity->setNote($note);

        return $this;
    }

    /**
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
     * @param \DateTime $created
     *
     * @return self
     */
    public function setCreated($created)
    {
        $this->entity->setCreated($created);

        return $this;
    }

    /**
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
     * @param \DateTime $changed
     *
     * @return self
     */
    public function setChanged($changed)
    {
        $this->entity->setChanged($changed);

        return $this;
    }

    /**
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
     * @param \DateTime $expectedDeliveryDate
     *
     * @return self
     */
    public function setExpectedDeliveryDate($expectedDeliveryDate)
    {
        $this->entity->setExpectedDeliveryDate($expectedDeliveryDate);

        return $this;
    }

    /**
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
     * @VirtualProperty
     * @SerializedName("id")
     *
     * @return int
     */
    public function getId()
    {
        return $this->entity->getId();
    }

    /**
     * @param OrderAddress $deliveryAddress
     *
     * @return self
     */
    public function setDeliveryAddress(OrderAddress $deliveryAddress = null)
    {
        $this->entity->setDeliveryAddress($deliveryAddress);

        return $this;
    }

    /**
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
     * @param ShippingItemEntity $shippingItems
     *
     * @return self
     */
    public function addShippingItem(ShippingItemEntity $shippingItems)
    {
        $this->entity->addShippingItem($shippingItems);

        return $this;
    }

    /**
     * @param ShippingItemEntity $shippingItems
     */
    public function removeShippingItem(ShippingItemEntity $shippingItems)
    {
        $this->entity->removeShippingItem($shippingItems);
    }

    /**
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
     * @param ShippingStatus $status
     *
     * @return self
     */
    public function setStatus(ShippingStatus $status)
    {
        $this->entity->setStatus($status);

        return $this;
    }

    /**
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
     * @param int $bitmaskStatus
     *
     * @return self
     */
    public function setBitmaskStatus($bitmaskStatus)
    {
        $this->entity->setBitmaskStatus($bitmaskStatus);

        return $this;
    }

    /**
     * @VirtualProperty
     * @SerializedName("bitmaskStatus")
     *
     * @return int
     */
    public function getBitmaskStatus()
    {
        return $this->entity->getBitmaskStatus();
    }

    /**
     * @param Order $order
     *
     * @return self
     */
    public function setOrder(Order $order = null)
    {
        $this->entity->setOrder($order);

        return $this;
    }

    /**
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
     * @param UserInterface $changer
     *
     * @return self
     */
    public function setChanger(UserInterface $changer = null)
    {
        $this->entity->setChanger($changer);

        return $this;
    }

    /**
     * @return UserInterface
     */
    public function getChanger()
    {
        return $this->entity->getChanger();
    }

    /**
     * @param UserInterface $creator
     *
     * @return self
     */
    public function setCreator(UserInterface $creator = null)
    {
        $this->entity->setCreator($creator);

        return $this;
    }

    /**
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
     * @return self
     */
    public function setInternalNote($note)
    {
        $this->entity->setInternalNote($note);

        return $this;
    }


    /**
     * Returns the entities locale.
     *
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * Returns the data needed for the sales document widget as array.
     *
     * @return array
     */
    public function getSalesDocumentData()
    {
        return [
            'number' => $this->getNumber(),
            'type' => 'shipping',
            'icon' => 'fa-truck',
            'date' => $this->getExpectedDeliveryDate(),
            'id' => $this->getId(),
            'pdfBaseUrl' => null,
            'translationKey' => 'salescore.shipping',
        ];
    }

    /**
     * Returns url for generating the documents pdf.
     *
     * @return string
     */
    public function getPdfBaseUrl()
    {
        return self::$pdfBaseUrl;
    }
}
