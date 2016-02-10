<?php

namespace Sulu\Bundle\Sales\CoreBundle\Entity;

use Sulu\Bundle\ProductBundle\Entity\ProductInterface;
use Sulu\Component\Security\Authentication\UserInterface;

interface ItemInterface
{
    /**
     * Set name
     *
     * @param string $name
     *
     * @return ItemInterface
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
     * @return ItemInterface
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
     * @return ItemInterface
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
     * @return ItemInterface
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
     * @return ItemInterface
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
     * @return ItemInterface
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
     * @return ItemInterface
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
     * @return ItemInterface
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
     * @return ItemInterface
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
     * @return ItemInterface
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
     * @return ItemInterface
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
     * @return ItemInterface
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
     * @return ItemInterface
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
     * @return ItemInterface
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
     * @return ItemInterface
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
     * @return ItemInterface
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
     * @return ItemInterface
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
     * @return ItemInterface
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
     * @return ItemInterface
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
     * @return ItemInterface
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
     * @return ItemInterface
     */
    public function setDeliveryDate($deliveryDate);

    /**
     * Get deliveryDate
     *
     * @return \DateTime
     */
    public function getDeliveryDate();

    /**
     * @param string $costCentre
     *
     * @return ItemInterface
     */
    public function setCostCentre($costCentre);

    /**
     * @return string
     */
    public function getCostCentre();


}
