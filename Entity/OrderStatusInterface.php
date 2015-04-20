<?php

namespace Sulu\Bundle\Sales\OrderBundle\Entity;

interface OrderStatusInterface
{
    /**
     * Get id
     *
     * @return integer 
     */
    public function getId();

    /**
     * @param $id
     * @return $this
     */
    public function setId($id);

    /**
     * Add order
     *
     * @param OrderInterface $order
     * @return OrderStatus
     */
    public function addOrder(OrderInterface $order);

    /**
     * Remove order
     *
     * @param OrderInterface $order
     */
    public function removeOrder(OrderInterface $order);

    /**
     * Get order
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getOrder();

    /**
     * Add translations
     *
     * @param OrderStatusTranslationInterface $translations
     * @return OrderStatus
     */
    public function addTranslation(OrderStatusTranslationInterface $translations);

    /**
     * Remove translations
     *
     * @param OrderStatusTranslationInterface $translations
     */
    public function removeTranslation(OrderStatusTranslationInterface $translations);

    /**
     * Get translations
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getTranslations();
}
