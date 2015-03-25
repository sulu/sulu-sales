<?php

namespace Sulu\Bundle\Sales\CoreBundle\Api;

use JMS\Serializer\Annotation\VirtualProperty;
use Sulu\Bundle\ContactBundle\Entity\Account;
use Sulu\Bundle\ProductBundle\Api\Product;
use Sulu\Bundle\Sales\CoreBundle\Entity\Item as Entity;
use Sulu\Bundle\Sales\CoreBundle\Entity\ItemAttributeEntity;
use Sulu\Bundle\Sales\CoreBundle\Pricing\CalculableBulkPriceItemInterface;
use Sulu\Bundle\Sales\CoreBundle\Pricing\CalculablePriceGroupItemInterface;
use Sulu\Bundle\Sales\CoreBundle\Entity\OrderAddress as OrderAddressEntity;
use Sulu\Bundle\Sales\CoreBundle\Api\OrderAddress;
use Sulu\Component\Rest\ApiWrapper;
use Hateoas\Configuration\Annotation\Relation;
use JMS\Serializer\Annotation\SerializedName;
use JMS\Serializer\Annotation\Groups;
use DateTime;
use Sulu\Component\Security\Authentication\UserInterface;
use Symfony\Component\Intl\NumberFormatter\NumberFormatter;

/**
 * The item class which will be exported to the API
 * @package Sulu\Bundle\Sales\CoreBundle\Api
 */
class Item extends ApiWrapper implements CalculableBulkPriceItemInterface, CalculablePriceGroupItemInterface
{
    /**
     * @param Entity $item The item to wrap
     * @param string $locale The locale of this item
     */
    public function __construct(Entity $item, $locale, $currency = 'EUR')
    {
        $this->entity = $item;
        $this->locale = $locale;
        $this->currency = $currency;
    }

    /**
     * Returns the id of the entity
     * @return int
     * @VirtualProperty
     * @SerializedName("id")
     * @Groups({"cart"})
     */
    public function getId()
    {
        return $this->entity->getId();
    }

    /**
     * @return string
     * @VirtualProperty
     * @SerializedName("name")
     */
    public function getName()
    {
        return $this->entity->getName();
    }

    /**
     * @param $name
     * @return Item
     */
    public function setName($name)
    {
        $this->entity->setName($name);

        return $this;
    }

    /**
     * @return int
     * @VirtualProperty
     * @SerializedName("number")
     */
    public function getNumber()
    {
        return $this->entity->getNumber();
    }

    /**
     * @param $number
     * @return Item
     */
    public function setNumber($number)
    {
        $this->entity->setNumber($number);

        return $this;
    }

    /**
     * @return int
     * @VirtualProperty
     * @SerializedName("created")
     * @return DateTime
     */
    public function getCreated()
    {
        return $this->entity->getCreated();
    }

    /**
     * @param DateTime $created
     * @return Item
     */
    public function setCreated(DateTime $created)
    {
        $this->entity->setCreated($created);

        return $this;
    }

    /**
     * @return int
     * @VirtualProperty
     * @SerializedName("changed")
     * @return DateTime
     */
    public function getChanged()
    {
        return $this->entity->getChanged();
    }

    /**
     * @param DateTime $changed
     * @return Item
     */
    public function setChanged(DateTime $changed)
    {
        $this->entity->setChanged($changed);

        return $this;
    }

    /**
     * Set changer
     * @param UserInterface $changer
     * @return Item
     */
    public function setChanger(UserInterface $changer = null)
    {
        $this->entity->setChanger($changer);

        return $this;
    }

    /**
     * Get changer
     * @return UserInterface
     * @VirtualProperty
     * @SerializedName("changer")
     */
    public function getChanger()
    {
        // just return id
        $changer = $this->entity->getChanger();

        return !$changer ? null : array(
            'id' => $changer->getId()
        );
    }

    /**
     * Set creator
     * @param UserInterface $creator
     * @return Item
     */
    public function setCreator(UserInterface $creator = null)
    {
        $this->entity->setCreator($creator);

        return $this;
    }

    /**
     * Get creator
     * @return UserInterface
     * @VirtualProperty
     * @SerializedName("creator")
     */
    public function getCreator()
    {
        // just return id
        $creator = $this->entity->getCreator();

        return !$creator ? null : array(
            'id' => $creator->getId()
        );
    }

    /**
     * @param float
     * @return Item
     */
    public function setQuantity($quantity)
    {
        $this->entity->setQuantity($quantity);

        return $this;
    }

    /**
     * @return float
     * @VirtualProperty
     * @SerializedName("quantity")
     * @Groups({"cart"})
     */
    public function getQuantity()
    {
        return $this->entity->getQuantity();
    }

    /**
     * @param string
     * @return Item
     */
    public function setQuantityUnit($quantityUnit)
    {
        $this->entity->setQuantityUnit($quantityUnit);

        return $this;
    }

    /**
     * @return string
     * @VirtualProperty
     * @SerializedName("quantityUnit")
     */
    public function getQuantityUnit()
    {
        return $this->entity->getQuantityUnit();
    }

    /**
     * @param bool
     * @return Item
     */
    public function setUseProductsPrice($useProductsPrice)
    {
        $this->entity->setUseProductsPrice($useProductsPrice);

        return $this;
    }

    /**
     * @return bool
     * @VirtualProperty
     * @SerializedName("useProductsPrice")
     */
    public function getUseProductsPrice()
    {
        return $this->entity->getUseProductsPrice();
    }

    /**
     * @param float
     * @return Item
     */
    public function setTax($tax)
    {
        $this->entity->setTax($tax);

        return $this;
    }

    /**
     * @return float
     * @VirtualProperty
     * @SerializedName("tax")
     */
    public function getTax()
    {
        return $this->entity->getTax();
    }

    /**
     * @param float
     * @return Item
     */
    public function setPrice($value)
    {
        $this->entity->setPrice($value);

        return $this;
    }

    /**
     * @return float
     * @VirtualProperty
     * @SerializedName("price")
     * @Groups({"cart"})
     */
    public function getPrice()
    {
        return $this->entity->getPrice();
    }

    /**
     * @VirtualProperty
     * @SerializedName("priceFormatted")
     * @Groups({"cart"})
     * @return string
     */
    public function getPriceFormatted($locale = null)
    {
        $formatter = $this->getFormatter($locale);

        return $formatter->format((float)$this->entity->getPrice());
    }

    /**
     * @VirtualProperty
     * @SerializedName("totalNetPriceFormatted")
     * @Groups({"cart"})
     * @return string
     */
    public function getTotalNetPriceFormatted($locale = null)
    {
        $formatter = $this->getFormatter($locale);

        return $formatter->format((float)$this->entity->getTotalNetPrice());
    }

    /**
     * get total net price of an item
     *
     * @VirtualProperty
     * @SerializedName("totalNetPrice")
     * @Groups({"cart"})
     */
    public function getTotalNetPrice()
    {
        return $this->entity->getTotalNetPrice();
    }

    /**
     * set total net price of an item
     *
     * @param $price
     * @return Item
     */
    public function setTotalNetPrice($price)
    {
        $this->entity->setTotalNetPrice($price);
        
        return $this;
    }

    /**
     * @param float
     * @return Item
     */
    public function setDiscount($value)
    {
        $this->entity->setDiscount($value);

        return $this;
    }

    /**
     * @return float
     * @VirtualProperty
     * @SerializedName("discount")
     */
    public function getDiscount()
    {
        return $this->entity->getDiscount();
    }

    /**
     * @param string
     * @return Item
     */
    public function setDescription($value)
    {
        $this->entity->setDescription($value);

        return $this;
    }

    /**
     * @return string
     * @VirtualProperty
     * @SerializedName("description")
     */
    public function getDescription()
    {
        return $this->entity->getDescription();
    }

    /**
     * @param float
     * @return Item
     */
    public function setWeight($value)
    {
        $this->entity->setWeight($value);

        return $this;
    }

    /**
     * @return float
     * @VirtualProperty
     * @SerializedName("weight")
     */
    public function getWeight()
    {
        return $this->entity->getWeight();
    }

    /**
     * @param float
     * @return Item
     */
    public function setWidth($value)
    {
        $this->entity->setWidth($value);

        return $this;
    }

    /**
     * @return float
     * @VirtualProperty
     * @SerializedName("width")
     */
    public function getWidth()
    {
        return $this->entity->getWidth();
    }

    /**
     * @param float
     * @return Item
     */
    public function setHeight($value)
    {
        $this->entity->setHeight($value);

        return $this;
    }

    /**
     * @return float
     * @VirtualProperty
     * @SerializedName("height")
     */
    public function getHeight()
    {
        return $this->entity->getHeight();
    }

    /**
     * @param float
     * @return Item
     */
    public function setLength($value)
    {
        $this->entity->setWeight($value);

        return $this;
    }

    /**
     * @return float
     * @VirtualProperty
     * @SerializedName("length")
     */
    public function getLenght()
    {
        return $this->entity->getLength();
    }

    /**
     * @param int
     * @return Item
     */
    public function setBitmaskStatus($status)
    {
        $this->entity->setBitmaskStatus($status);

        return $this;
    }

    /**
     * @return int
     * @VirtualProperty
     * @SerializedName("bitmaskStatus")
     */
    public function getBitmaskStatus()
    {
        return $this->entity->getBitmaskStatus();
    }

    /**
     * @param Account
     * @return Item
     */
    public function setSupplier($value)
    {
        $this->entity->setSupplier($value);

        return $this;
    }

    /**
     * @return Account
     */
    public function getSupplier()
    {
        return $this->entity->getSupplier();
    }

    /**
     * @param string
     * @return Item
     */
    public function setSupplierName($value)
    {
        $this->entity->setSupplierName($value);

        return $this;
    }

    /**
     * @return float
     * @VirtualProperty
     * @SerializedName("supplierName")
     */
    public function getSupplierName()
    {
        return $this->entity->getSupplierName();
    }

    /**
     * @param ItemAttributeEntity $value
     * @return $this
     */
    public function addAttribute(ItemAttributeEntity $value)
    {
        $this->entity->addAttribute($value);

        return $this;
    }

    /**
     * @return ItemAttribute
     * @VirtualProperty
     * @SerializedName("attributes")
     */
    public function getAttributes()
    {
        return $this->entity->getAttributes();
    }

    /**
     * @param $product
     * @return Item
     */
    public function setProduct($product)
    {
        $this->entity->setProduct($product);

        return $this;
    }

    /**
     * @return Product
     * @VirtualProperty
     * @SerializedName("product")
     * @Groups({"cart"})
     */
    public function getProduct()
    {
        $product = $this->getEntity()->getProduct();
        if ($product) {
            return new Product($product, $this->locale);
        }
        return null;
    }

    /**
     * Set deliveryAddress
     * @param OrderAddressEntity $deliveryAddress
     * @return Item
     */
    public function setDeliveryAddress(OrderAddressEntity $deliveryAddress = null)
    {
        $this->entity->setDeliveryAddress($deliveryAddress);
        return $this;
    }

    /**
     * Get deliveryAddress
     * @return OrderAddress $deliveryAddress
     * @VirtualProperty
     * @SerializedName("deliveryAddress")
     * @Groups({"cart"})
     */
    public function getDeliveryAddress()
    {
        $address = $this->entity->getDeliveryAddress();
        if ($address) {
            return new OrderAddress($address);
        }
        return null;
    }

    /**
     * @param $locale
     * @return Formatter
     */
    private function getFormatter($locale)
    {
        $sysLocale = $locale ? $locale : 'de-AT';
        $formatter = new \NumberFormatter($sysLocale, \NumberFormatter::DECIMAL);
        $formatter->setAttribute(\NumberFormatter::MIN_FRACTION_DIGITS, 2);
        $formatter->setAttribute(\NumberFormatter::DECIMAL_ALWAYS_SHOWN, 1);
        return $formatter;
    }

    /**
     * {@inheritDoc}
     * TODO: default-price EUR?
     */
    public function getCalcProduct()
    {
        return $this->getProduct()->getEntity();
    }

    /**
     * {@inheritDoc}
     */
    public function getCalcQuantity()
    {
        return $this->getQuantity();
    }

    /**
     * {@inheritDoc}
     */
    public function getCalcPriceGroup()
    {
        $supplier = $this->getSupplier();
        if ($supplier) {
            return $supplier->getId();
        }

        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function getCalcDiscount()
    {
        return $this->getDiscount();
    }

    /**
     * {@inheritDoc}
     */
    public function getCalcCurrencyCode()
    {
        return $this->currency;
    }

    /**
     * {@inheritDoc}
     */
    public function getCalcPriceGroupContent()
    {
        $supplier = $this->getSupplier();
        if ($supplier) {
            return array(
                'id' => $supplier->getId(),
                'name' => $supplier->getName(),
            );
        }
        return null;
    }
}
