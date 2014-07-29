<?php

namespace Sulu\Bundle\Sales\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Exclude;

/**
 * ItemAttribute
 */
class ItemAttribute
{
    /**
     * @var string
     */
    private $attribute;

    /**
     * @var string
     */
    private $value;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \Sulu\Bundle\Sales\CoreBundle\Entity\Item
     * @Exclude
     */
    private $item;


    /**
     * Set attribute
     *
     * @param string $attribute
     * @return ItemAttribute
     */
    public function setAttribute($attribute)
    {
        $this->attribute = $attribute;
    
        return $this;
    }

    /**
     * Get attribute
     *
     * @return string 
     */
    public function getAttribute()
    {
        return $this->attribute;
    }

    /**
     * Set value
     *
     * @param string $value
     * @return ItemAttribute
     */
    public function setValue($value)
    {
        $this->value = $value;
    
        return $this;
    }

    /**
     * Get value
     *
     * @return string 
     */
    public function getValue()
    {
        return $this->value;
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
     * Set item
     *
     * @param \Sulu\Bundle\Sales\CoreBundle\Entity\Item $item
     * @return ItemAttribute
     */
    public function setItem(\Sulu\Bundle\Sales\CoreBundle\Entity\Item $item = null)
    {
        $this->item = $item;
    
        return $this;
    }

    /**
     * Get item
     *
     * @return \Sulu\Bundle\Sales\CoreBundle\Entity\Item 
     */
    public function getItem()
    {
        return $this->item;
    }
}
