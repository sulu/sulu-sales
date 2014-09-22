<?php

namespace Sulu\Bundle\Sales\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Sulu\Component\Security\UserInterface;

/**
 * Item
 */
class Item
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $number;

    /**
     * @var float
     */
    private $quantity;

    /**
     * @var string
     */
    private $quantityUnit;

    /**
     * @var boolean
     */
    private $useProductsPrice;

    /**
     * @var float
     */
    private $tax;

    /**
     * @var float
     */
    private $price;

    /**
     * @var float
     */
    private $discount;

    /**
     * @var string
     */
    private $description;

    /**
     * @var float
     */
    private $weight;

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
     * @var string
     */
    private $supplierName;

    /**
     * @var \DateTime
     */
    private $created;

    /**
     * @var \DateTime
     */
    private $changed;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $attributes;

    /**
     * @var \Sulu\Bundle\Sales\CoreBundle\Entity\ItemStatus
     */
    private $status;

    /**
     * @var \Sulu\Bundle\ProductBundle\Entity\Product
     */
    private $product;

    /**
     * @var UserInterface
     */
    private $changer;

    /**
     * @var UserInterface
     */
    private $creator;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->attributes = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Set name
     *
     * @param string $name
     * @return Item
     */
    public function setName($name)
    {
        $this->name = $name;
    
        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set number
     *
     * @param string $number
     * @return Item
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
     * Set quantity
     *
     * @param float $quantity
     * @return Item
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;
    
        return $this;
    }

    /**
     * Get quantity
     *
     * @return float 
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * Set quantityUnit
     *
     * @param string $quantityUnit
     * @return Item
     */
    public function setQuantityUnit($quantityUnit)
    {
        $this->quantityUnit = $quantityUnit;
    
        return $this;
    }

    /**
     * Get quantityUnit
     *
     * @return string 
     */
    public function getQuantityUnit()
    {
        return $this->quantityUnit;
    }

    /**
     * Set useProductsPrice
     *
     * @param boolean $useProductsPrice
     * @return Item
     */
    public function setUseProductsPrice($useProductsPrice)
    {
        $this->useProductsPrice = $useProductsPrice;
    
        return $this;
    }

    /**
     * Get useProductsPrice
     *
     * @return boolean 
     */
    public function getUseProductsPrice()
    {
        return $this->useProductsPrice;
    }

    /**
     * Set tax
     *
     * @param float $tax
     * @return Item
     */
    public function setTax($tax)
    {
        $this->tax = $tax;
    
        return $this;
    }

    /**
     * Get tax
     *
     * @return float 
     */
    public function getTax()
    {
        return $this->tax;
    }

    /**
     * Set price
     *
     * @param float $price
     * @return Item
     */
    public function setPrice($price)
    {
        $this->price = $price;
    
        return $this;
    }

    /**
     * Get price
     *
     * @return float 
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Set discount
     *
     * @param float $discount
     * @return Item
     */
    public function setDiscount($discount)
    {
        $this->discount = $discount;
    
        return $this;
    }

    /**
     * Get discount
     *
     * @return float 
     */
    public function getDiscount()
    {
        return $this->discount;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return Item
     */
    public function setDescription($description)
    {
        $this->description = $description;
    
        return $this;
    }

    /**
     * Get description
     *
     * @return string 
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set weight
     *
     * @param float $weight
     * @return Item
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
     * Set width
     *
     * @param float $width
     * @return Item
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
     * @return Item
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
     * @return Item
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
     * Set supplierName
     *
     * @param string $supplierName
     * @return Item
     */
    public function setSupplierName($supplierName)
    {
        $this->supplierName = $supplierName;
    
        return $this;
    }

    /**
     * Get supplierName
     *
     * @return string 
     */
    public function getSupplierName()
    {
        return $this->supplierName;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     * @return Item
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
     * @return Item
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
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Add attributes
     *
     * @param \Sulu\Bundle\Sales\CoreBundle\Entity\ItemAttribute $attributes
     * @return Item
     */
    public function addAttribute(\Sulu\Bundle\Sales\CoreBundle\Entity\ItemAttribute $attributes)
    {
        $this->attributes[] = $attributes;
    
        return $this;
    }

    /**
     * Remove attributes
     *
     * @param \Sulu\Bundle\Sales\CoreBundle\Entity\ItemAttribute $attributes
     */
    public function removeAttribute(\Sulu\Bundle\Sales\CoreBundle\Entity\ItemAttribute $attributes)
    {
        $this->attributes->removeElement($attributes);
    }

    /**
     * Get attributes
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * Set status
     *
     * @param \Sulu\Bundle\Sales\CoreBundle\Entity\ItemStatus $status
     * @return Item
     */
    public function setStatus(\Sulu\Bundle\Sales\CoreBundle\Entity\ItemStatus $status = null)
    {
        $this->status = $status;
    
        return $this;
    }

    /**
     * Get status
     *
     * @return \Sulu\Bundle\Sales\CoreBundle\Entity\ItemStatus 
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set product
     *
     * @param \Sulu\Bundle\ProductBundle\Entity\Product $product
     * @return Item
     */
    public function setProduct(\Sulu\Bundle\ProductBundle\Entity\Product $product = null)
    {
        $this->product = $product;
    
        return $this;
    }

    /**
     * Get product
     *
     * @return \Sulu\Bundle\ProductBundle\Entity\Product 
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * Set changer
     *
     * @param UserInterface $changer
     * @return Item
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
     * @return Item
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
     * @var \Sulu\Bundle\ContactBundle\Entity\Account
     */
    private $supplier;


    /**
     * Set supplier
     *
     * @param \Sulu\Bundle\ContactBundle\Entity\Account $supplier
     * @return Item
     */
    public function setSupplier(\Sulu\Bundle\ContactBundle\Entity\Account $supplier = null)
    {
        $this->supplier = $supplier;
    
        return $this;
    }

    /**
     * Get supplier
     *
     * @return \Sulu\Bundle\ContactBundle\Entity\Account 
     */
    public function getSupplier()
    {
        return $this->supplier;
    }
}