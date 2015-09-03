<?php

namespace Sulu\Bundle\Sales\CoreBundle\Entity;

use Sulu\Bundle\ProductBundle\Entity\ProductInterface;
use Sulu\Component\Security\Authentication\UserInterface;

/**
 * Item
 */
interface ItemInterface
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
     * @return Item
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
     * @return Item
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
     * @return Item
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
     * @return Item
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
     * @return Item
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
     * @return Item
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
     * @return Item
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
     * @return Item
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
     * @return Item
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
     * @return Item
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
     * @return Item
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
     * @return Item
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
     * @param \DateTime $created
     *
     * @return Item
     */
    public function setCreated($created);

    /**
     * Get created
     *
     * @return \DateTime
     */
    public function getCreated();

    /**
     * Set changed
     *
     * @param \DateTime $changed
     *
     * @return Item
     */
    public function setChanged($changed);

    /**
     * Get changed
     *
     * @return \DateTime
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
     * @return Item
     */
    public function setProduct(ProductInterface $product = null);

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
     * @return Item
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
     * @return Item
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
     * @return Item
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
     * @return Item
     */
    public function setTotalNetPrice($totalNetPrice);

    /**
     * Get totalNetPrice
     *
     * @return float
     */
    public function getTotalNetPrice();

    /**
     * Set deliveryDate
     *
     * @param \DateTime $deliveryDate
     *
     * @return Item
     */
    public function setDeliveryDate($deliveryDate);

    /**
     * Get deliveryDate
     *
     * @return \DateTime
     */
    public function getDeliveryDate();
}
