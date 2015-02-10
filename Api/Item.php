<?php

namespace Sulu\Bundle\Sales\CoreBundle\Api;

use JMS\Serializer\Annotation\VirtualProperty;
use Sulu\Bundle\ContactBundle\Entity\Account;
use Sulu\Bundle\ProductBundle\Api\Product;
use Sulu\Bundle\Sales\CoreBundle\Entity\Item as Entity;
use Sulu\Bundle\Sales\CoreBundle\Entity\ItemAttribute;
use Sulu\Component\Rest\ApiWrapper;
use Hateoas\Configuration\Annotation\Relation;
use JMS\Serializer\Annotation\SerializedName;
use DateTime;
use Sulu\Component\Security\UserInterface;
use Symfony\Component\Intl\NumberFormatter\NumberFormatter;

/**
 * The item class which will be exported to the API
 * @package Sulu\Bundle\Sales\CoreBundle\Api
 */
class Item extends ApiWrapper
{
    /**
     * @param Entity $item The item to wrap
     * @param string $locale The locale of this item
     */
    public function __construct(Entity $item, $locale)
    {
        $this->entity = $item;
        $this->locale = $locale;
    }

    /**
     * Returns the id of the entity
     * @return int
     * @VirtualProperty
     * @SerializedName("id")
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
     *
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
     *
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
     *
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
     *
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
     */
    public function getPrice()
    {
        return $this->entity->getPrice();
    }

    /**
     * @VirtualProperty
     * @SerializedName("priceFormatted")
     *
     * @return string
     */
    public function getPriceFormatted($locale=null)
    {
        $formatter = $this->getFormatter($locale);
        return $formatter->format((float)$this->entity->getPrice());
    }

    /**
     * @VirtualProperty
     * @SerializedName("totalNetPriceFormatted")
     *
     * @return string
     */
    public function getTotalNetPriceFormatted($locale=null)
    {
        $formatter = $this->getFormatter($locale);
        return $formatter->format((float)$this->entity->getTotalNetPrice());
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
     * @param ItemAttribute $value
     * @return $this
     */
    public function addAttribute(ItemAttribute $value)
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
     */
    public function getProduct()
    {
        if ($this->getEntity()->getProduct()) {
            return array(
                'id' => $this->entity->getProduct()->getId()
            );
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
        return new \NumberFormatter($sysLocale, \NumberFormatter::CURRENCY);
    }
}
