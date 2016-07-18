<?php

namespace Sulu\Bundle\Sales\OrderBundle\Entity;

use Sulu\Bundle\Sales\CoreBundle\Entity\ItemInterface;
use Sulu\Bundle\Sales\CoreBundle\Entity\OrderAddressInterface;
use Sulu\Component\Contact\Model\ContactInterface;
use Sulu\Component\Security\Authentication\UserInterface;

interface OrderInterface
{
    /**
     * Set number
     *
     * @param string $number
     *
     * @return OrderInterface
     */
    public function setNumber($number);

    /**
     * Get number
     *
     * @return string
     */
    public function getNumber();

    /**
     * Set orderNumber
     *
     * @param string $orderNumber
     *
     * @return OrderInterface
     */
    public function setOrderNumber($orderNumber);

    /**
     * Get orderNumber
     *
     * @return string
     */
    public function getOrderNumber();

    /**
     * Set currency
     *
     * @param string $currency
     *
     * @return OrderInterface
     */
    public function setCurrencyCode($currency);

    /**
     * Get currency
     *
     * @return string
     */
    public function getCurrencyCode();

    /**
     * Set taxfree
     *
     * @param boolean $taxfree
     *
     * @return OrderInterface
     */
    public function setTaxfree($taxfree);

    /**
     * Get taxfree
     *
     * @return boolean
     */
    public function getTaxfree();

    /**
     * Set costCentre
     *
     * @param string $costCentre
     *
     * @return OrderInterface
     */
    public function setCostCentre($costCentre);

    /**
     * Get costCentre
     *
     * @return string
     */
    public function getCostCentre();

    /**
     * Set commission
     *
     * @param string $commission
     *
     * @return OrderInterface
     */
    public function setCommission($commission);

    /**
     * Get commission
     *
     * @return string
     */
    public function getCommission();

    /**
     * Set customerName
     *
     * @param string $customerName
     *
     * @return OrderInterface
     */
    public function setCustomerName($customerName);

    /**
     * Get customerName
     *
     * @return string
     */
    public function getCustomerName();

    /**
     * Set termsOfDeliveryContent
     *
     * @param string $termsOfDeliveryContent
     *
     * @return OrderInterface
     */
    public function setTermsOfDeliveryContent($termsOfDeliveryContent);

    /**
     * Get termsOfDeliveryContent
     *
     * @return string
     */
    public function getTermsOfDeliveryContent();

    /**
     * Set termsOfPaymentContent
     *
     * @param string $termsOfPaymentContent
     *
     * @return OrderInterface
     */
    public function setTermsOfPaymentContent($termsOfPaymentContent);

    /**
     * Get termsOfPaymentContent
     *
     * @return string
     */
    public function getTermsOfPaymentContent();

    /**
     * Set created
     *
     * @param \DateTime $created
     *
     * @return OrderInterface
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
     * @return OrderInterface
     */
    public function setChanged($changed);

    /**
     * Get changed
     *
     * @return \DateTime
     */
    public function getChanged();

    /**
     * Set desiredDeliveryDate
     *
     * @param \DateTime $desiredDeliveryDate
     *
     * @return OrderInterface
     */
    public function setDesiredDeliveryDate($desiredDeliveryDate);

    /**
     * Get desiredDeliveryDate
     *
     * @return \DateTime
     */
    public function getDesiredDeliveryDate();

    /**
     * Get id
     *
     * @return integer
     */
    public function getId();

    /**
     * Set deliveryAddress
     *
     * @param OrderAddressInterface $deliveryAddress
     *
     * @return OrderInterfaceInterface
     */
    public function setDeliveryAddress(OrderAddressInterface $deliveryAddress = null);

    /**
     * Get deliveryAddress
     *
     * @return OrderInterfaceAddress
     */
    public function getDeliveryAddress();

    /**
     * Set invoiceAddress
     *
     * @param OrderAddressInterface $invoiceAddress
     *
     * @return OrderInterface
     */
    public function setInvoiceAddress(OrderAddressInterface $invoiceAddress = null);

    /**
     * Get invoiceAddress
     *
     * @return OrderInterfaceAddress
     */
    public function getInvoiceAddress();

    /**
     * Set status
     *
     * @param OrderStatusInterface $status
     *
     * @return OrderInterface
     */
    public function setStatus(OrderStatusInterface $status);

    /**
     * Get status
     *
     * @return OrderStatusInterface
     */
    public function getStatus();

    /**
     * Set contact
     *
     * @param ContactInterface $contact
     *
     * @return OrderInterface
     */
    public function setCustomerContact(ContactInterface $contact = null);

    /**
     * Get contact
     *
     * @return ContactInterface
     */
    public function getCustomerContact();

    /**
     * Add items
     *
     * @param ItemInterface $items
     *
     * @return OrderInterface
     */
    public function addItem(ItemInterface $items);

    /**
     * Remove items
     *
     * @param ItemInterface $items
     */
    public function removeItem(ItemInterface $items);

    /**
     * Get items
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getItems();

    /**
     * Set changer
     *
     * @param UserInterface $changer
     *
     * @return OrderInterface
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
     * @return OrderInterface
     */
    public function setCreator(UserInterface $creator = null);

    /**
     * Get creator
     *
     * @return UserInterface
     */
    public function getCreator();

    /**
     * Set totalNetPrice
     *
     * @param float $totalNetPrice
     *
     * @return OrderInterface
     */
    public function setTotalNetPrice($totalNetPrice);

    /**
     * Get totalNetPrice
     *
     * @return float
     */
    public function getTotalNetPrice();

    /**
     * @return float
     */
    public function getTotalRecurringNetPrice();

    /**
     * Set orderDate
     *
     * @param \DateTime $orderDate
     *
     * @return OrderInterface
     */
    public function setOrderDate($orderDate);

    /**
     * Get orderDate
     *
     * @return \DateTime
     */
    public function getOrderDate();

    /**
     * Updates the total net price
     */
    public function updateTotalNetPrice();

    /**
     * Set deliveryCost
     *
     * @param float $deliveryCost
     *
     * @return OrderInterface
     */
    public function setDeliveryCost($deliveryCost);

    /**
     * Get deliveryCost
     *
     * @return float
     */
    public function getDeliveryCost();
}
