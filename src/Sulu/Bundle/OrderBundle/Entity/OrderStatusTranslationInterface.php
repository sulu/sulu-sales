<?php

namespace Sulu\Bundle\Sales\OrderBundle\Entity;

interface OrderStatusTranslationInterface
{
    /**
     * Set name
     *
     * @param string $name
     *
     * @return OrderStatusTranslation
     */
    public function setName($name);

    /**
     * Get name
     *
     * @return string
     */
    public function getName();

    /**
     * Set locale
     *
     * @param string $locale
     *
     * @return OrderStatusTranslation
     */
    public function setLocale($locale);

    /**
     * Get locale
     *
     * @return string
     */
    public function getLocale();

    /**
     * Get id
     *
     * @return integer
     */
    public function getId();

    /**
     * Set status
     *
     * @param OrderStatusInterface $status
     *
     * @return OrderStatusTranslation
     */
    public function setStatus(OrderStatusInterface $status = null);

    /**
     * Get status
     *
     * @return OrderStatusInterface
     */
    public function getStatus();
}
