<?php

namespace Sulu\Bundle\Sales\CoreBundle\Api;

use DateTime;
use Sulu\Component\Security\Authentication\UserInterface;

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
     * return ApiItemInterface
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
     * return ApiItemInterface
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
     * return ApiItemInterface
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
     * return ApiItemInterface
     */
    public function setUseProductsPrice($useProductsPrice);

    /**
     * Get useProductsPrice
     *
     * @return boolean
     */
    public function getUseProductsPrice();

    /**
     * Set tax
     *
     * @param float $tax
     *
     * return ApiItemInterface
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
     * @return ApiItemInterface
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
     * @return ApiItemInterface
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
     * @return ApiItemInterface
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
     * @return ApiItemInterface
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
     * @return ApiItemInterface
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
     * @return ApiItemInterface
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
     * @return ApiItemInterface
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
     * @return ApiItemInterface
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
     * @return ApiItemInterface
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
     * @param $product
     *
     * @return ApiItemInterface
     */
    public function setProduct($product = null);

    /**
     * Get product
     *
     * @return \Sulu\Bundle\ProductBundle\Entity\Product
     */
    public function getProduct();

    /**
     * Set changer
     *
     * @param UserInterface $changer
     *
     * @return ApiItemInterface
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
     * @return ApiItemInterface
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
     * @return ApiItemInterface
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
     * @return ApiItemInterface
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
     * @return ApiItemInterface
     */
    public function setDeliveryDate(DateTime $deliveryDate);

    /**
     * @return string
     */
    public function getCostCentre();

    /**
     * @param string $costCentre
     *
     * @return ApiItemInterface
     */
    public function setCostCentre($costCentre);
}
