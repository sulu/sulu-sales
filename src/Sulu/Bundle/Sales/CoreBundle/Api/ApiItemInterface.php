<?php

namespace Sulu\Bundle\Sales\CoreBundle\Api;

use DateTime;
use JMS\Serializer\Annotation\SerializedName;
use JMS\Serializer\Annotation\VirtualProperty;
use JMS\Serializer\Annotation\Groups;
use Sulu\Bundle\ProductBundle\Entity\Addon;
use Sulu\Bundle\ProductBundle\Entity\ProductInterface;
use Sulu\Component\Security\Authentication\UserInterface;
use Sulu\Bundle\Sales\CoreBundle\Entity\OrderAddressInterface;

interface ApiItemInterface
{
    /**
     * Set name
     *
     * @param string $name
     *
     * @return Item
     */
    public function setName($name);

    /**
     * Get name
     *
     * @return string
     */
    public function getName();

    /**
     * Set number
     *
     * @param string $number
     *
     * @return self
     */
    public function setNumber($number);

    /**
     * Get number
     *
     * @return string
     */
    public function getNumber();

    /**
     * Set quantity
     *
     * @param float $quantity
     *
     * @return self
     */
    public function setQuantity($quantity);

    /**
     * Get quantity
     *
     * @return float
     */
    public function getQuantity();

    /**
     * Set quantityUnit
     *
     * @param string $quantityUnit
     *
     * @return self
     */
    public function setQuantityUnit($quantityUnit);

    /**
     * Get quantityUnit
     *
     * @return string
     */
    public function getQuantityUnit();

    /**
     * Set useProductsPrice
     *
     * @param boolean $useProductsPrice
     *
     * @return self
     */
    public function setUseProductsPrice($useProductsPrice);

    /**
     * Get useProductsPrice
     *
     * @return bool
     */
    public function getUseProductsPrice();

    /**
     * Set tax
     *
     * @param float $tax
     *
     * @return self
     */
    public function setTax($tax);

    /**
     * Get tax
     *
     * @return float
     */
    public function getTax();

    /**
     * Set price
     *
     * @param float $price
     *
     * @return self
     */
    public function setPrice($price);

    /**
     * Get price
     *
     * @return float
     */
    public function getPrice();

    /**
     * Set discount
     *
     * @param float $discount
     *
     * @return self
     */
    public function setDiscount($discount);

    /**
     * Get discount
     *
     * @return float
     */
    public function getDiscount();

    /**
     * Set description
     *
     * @param string $description
     *
     * @return self
     */
    public function setDescription($description);

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription();

    /**
     * Set weight
     *
     * @param float $weight
     *
     * @return self
     */
    public function setWeight($weight);

    /**
     * Get weight
     *
     * @return float
     */
    public function getWeight();

    /**
     * Set width
     *
     * @param float $width
     *
     * @return self
     */
    public function setWidth($width);

    /**
     * Get width
     *
     * @return float
     */
    public function getWidth();

    /**
     * Set height
     *
     * @param float $height
     *
     * @return self
     */
    public function setHeight($height);

    /**
     * Get height
     *
     * @return float
     */
    public function getHeight();

    /**
     * Set length
     *
     * @param float $length
     *
     * @return self
     */
    public function setLength($length);

    /**
     * Get length
     *
     * @return float
     */
    public function getLength();

    /**
     * Set created
     *
     * @param DateTime $created
     *
     * @return self
     */
    public function setCreated(DateTime $created);

    /**
     * Get created
     *
     * @return DateTime
     */
    public function getCreated();

    /**
     * Set changed
     *
     * @param DateTime $changed
     *
     * @return self
     */
    public function setChanged(DateTime $changed);

    /**
     * Get changed
     *
     * @return DateTime
     */
    public function getChanged();

    /**
     * Get id
     *
     * @return integer
     */
    public function getId();

    /**
     * Set product
     *
     * @param ProductInterface $product
     *
     * @return self
     */
    public function setProduct($product);

    /**
     * Get product
     *
     * @return ProductInterface
     */
    public function getProduct();

    /**
     * Set changer
     *
     * @param UserInterface $changer
     *
     * @return self
     */
    public function setChanger(UserInterface $changer = null);

    /**
     * Get changer
     *
     * @return UserInterface
     */
    public function getChanger();

    /**
     * Set creator
     *
     * @param UserInterface $creator
     *
     * @return self
     */
    public function setCreator(UserInterface $creator = null);

    /**
     * Get creator
     *
     * @return UserInterface
     */
    public function getCreator();

    /**
     * Set bitmaskStatus
     *
     * @param integer $bitmaskStatus
     *
     * @return self
     */
    public function setBitmaskStatus($bitmaskStatus);

    /**
     * Get bitmaskStatus
     *
     * @return integer
     */
    public function getBitmaskStatus();

    /**
     * Set totalNetPrice
     *
     * @param float $totalNetPrice
     *
     * @return self
     */
    public function setTotalNetPrice($totalNetPrice);

    /**
     * Get totalNetPrice
     *
     * @return float
     */
    public function getTotalNetPrice();

    /**
     * Get delivery date
     *
     * @return DateTime
     */
    public function getDeliveryDate();

    /**
     * Set delivery date
     *
     * @param DateTime $deliveryDate
     *
     * @return self
     */
    public function setDeliveryDate(DateTime $deliveryDate);

    /**
     * @return OrderAddressInterface|null
     */
    public function getDeliveryAddress();

    /**
     * @param OrderAddressInterface|null $deliveryAddress
     *
     * @return self
     */
    public function setDeliveryAddress(OrderAddressInterface $deliveryAddress = null);

    /**
     * @return string
     */
    public function getCostCentre();

    /**
     * @param string $costCentre
     *
     * @return self
     */
    public function setCostCentre($costCentre);

    /**
     * @param bool $isRecurringPrice
     *
     * @return bool
     */
    public function setIsRecurringPrice($isRecurringPrice);

    /**
     * @return string
     */
    public function isRecurringPrice();

    /**
     * @return int
     */
    public function getType();

    /**
     * @param int $type
     *
     * @return self
     */
    public function setType($type);

    /**
     * @return Addon
     */
    public function getAddon();

    /**
     * @param Addon $addon
     *
     * @return self
     */
    public function setAddon(Addon $addon = null);

    /**
     * @return ApiItemInterface
     */
    public function getParent();

    /**
     * @param ApiItemInterface $parent
     *
     * @return self
     */
    public function setParent(ApiItemInterface $parent = null);
}
