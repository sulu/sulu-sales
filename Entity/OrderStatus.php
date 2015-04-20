<?php

namespace Sulu\Bundle\Sales\OrderBundle\Entity;

class OrderStatus
{
    const STATUS_CREATED = 1;

    const STATUS_IN_CART = 2;

    const STATUS_CONFIRMED = 4;

    const STATUS_CLOSED_MANUALLY = 8;

    const STATUS_CANCELED = 16;

    const STATUS_COMPLETED = 32;

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
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param $id
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;;

        return $this;
    }

    /**
     * Add order
     *
     * @param OrderInterface $order
     * @return OrderStatus
     */
    public function addOrder(OrderInterface $order)
    {
        $this->order[] = $order;

        return $this;
    }

    /**
     * Remove order
     *
     * @param OrderInterface $order
     */
    public function removeOrder(OrderInterface $order)
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
     * @param OrderStatusTranslationInterface $translations
     * @return OrderStatus
     */
    public function addTranslation(OrderStatusTranslationInterface $translations)
    {
        $this->translations[] = $translations;

        return $this;
    }

    /**
     * Remove translations
     *
     * @param OrderStatusTranslationInterface $translations
     */
    public function removeTranslation(OrderStatusTranslationInterface $translations)
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
