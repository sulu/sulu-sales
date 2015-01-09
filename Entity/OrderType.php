<?php

namespace Sulu\Bundle\Sales\OrderBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * OrderType
 */
class OrderType
{
    const MANUAL = 0;
    const SHOP = 1;
    const ANONYMOUS = 2;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $order;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $translations;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->order = new \Doctrine\Common\Collections\ArrayCollection();
        $this->translations = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * @param $id
     * @return OrderType
     */
    public function setId($id)
    {
        $this->id = $id;;
        return $this;
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
     * Add order
     *
     * @param \Sulu\Bundle\Sales\OrderBundle\Entity\Order $order
     * @return OrderType
     */
    public function addOrder(\Sulu\Bundle\Sales\OrderBundle\Entity\Order $order)
    {
        $this->order[] = $order;

        return $this;
    }

    /**
     * Remove order
     *
     * @param \Sulu\Bundle\Sales\OrderBundle\Entity\Order $order
     */
    public function removeOrder(\Sulu\Bundle\Sales\OrderBundle\Entity\Order $order)
    {
        $this->order->removeElement($order);
    }

    /**
     * Get order
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * Add translations
     *
     * @param \Sulu\Bundle\Sales\OrderBundle\Entity\OrderTypeTranslation $translations
     * @return OrderType
     */
    public function addTranslation(\Sulu\Bundle\Sales\OrderBundle\Entity\OrderTypeTranslation $translations)
    {
        $this->translations[] = $translations;

        return $this;
    }

    /**
     * Remove translations
     *
     * @param \Sulu\Bundle\Sales\OrderBundle\Entity\OrderTypeTranslation $translations
     */
    public function removeTranslation(\Sulu\Bundle\Sales\OrderBundle\Entity\OrderTypeTranslation $translations)
    {
        $this->translations->removeElement($translations);
    }

    /**
     * Get translations
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getTranslations()
    {
        return $this->translations;
    }
}
