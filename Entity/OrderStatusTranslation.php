<?php

namespace Sulu\Bundle\Sales\OrderBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * OrderStatusTranslation
 */
class OrderStatusTranslation
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $locale;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \Sulu\Bundle\Sales\OrderBundle\Entity\OrderStatus
     */
    private $status;


    /**
     * Set name
     *
     * @param string $name
     * @return OrderStatusTranslation
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
     * Set locale
     *
     * @param string $locale
     * @return OrderStatusTranslation
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
    
        return $this;
    }

    /**
     * Get locale
     *
     * @return string 
     */
    public function getLocale()
    {
        return $this->locale;
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
     * Set status
     *
     * @param \Sulu\Bundle\Sales\OrderBundle\Entity\OrderStatus $status
     * @return OrderStatusTranslation
     */
    public function setStatus(\Sulu\Bundle\Sales\OrderBundle\Entity\OrderStatus $status = null)
    {
        $this->status = $status;
    
        return $this;
    }

    /**
     * Get status
     *
     * @return \Sulu\Bundle\Sales\OrderBundle\Entity\OrderStatus 
     */
    public function getStatus()
    {
        return $this->status;
    }
}