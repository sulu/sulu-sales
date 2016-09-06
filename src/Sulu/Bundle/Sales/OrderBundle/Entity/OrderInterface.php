<?php

namespace Sulu\Bundle\Sales\OrderBundle\Entity;

use Sulu\Bundle\Sales\CoreBundle\Entity\ItemInterface;
use Sulu\Bundle\Sales\CoreBundle\Entity\OrderAddressInterface;
use Sulu\Component\Contact\Model\ContactInterface;
use Sulu\Component\Security\Authentication\UserInterface;

interface OrderInterface
{
    /**
     * @param string $number
     *
     * @return OrderInterface
     */
    public function setNumber($number);

    /**
     * @return string
     */
    public function getNumber();

    /**
     * @param string $orderNumber
     *
     * @return OrderInterface
     */
    public function setOrderNumber($orderNumber);

    /**
     * @return string
     */
    public function getOrderNumber();

    /**
     * @param string $currency
     *
     * @return OrderInterface
     */
    public function setCurrencyCode($currency);

    /**
     * @return string
     */
    public function getCurrencyCode();

    /**
     * @param boolean $taxfree
     *
     * @return OrderInterface
     */
    public function setTaxfree($taxfree);

    /**
     * @return boolean
     */
    public function getTaxfree();

    /**
     * @param string $costCentre
     *
     * @return OrderInterface
     */
    public function setCostCentre($costCentre);

    /**
     * @return string
     */
    public function getCostCentre();

    /**
     * @param string $commission
     *
     * @return OrderInterface
     */
    public function setCommission($commission);

    /**
     * @return string
     */
    public function getCommission();

    /**
     * @param string $customerName
     *
     * @return OrderInterface
     */
    public function setCustomerName($customerName);

    /**
     * @return string
     */
    public function getCustomerName();

    /**
     * @param string $termsOfDeliveryContent
     *
     * @return OrderInterface
     */
    public function setTermsOfDeliveryContent($termsOfDeliveryContent);

    /**
     * @return string
     */
    public function getTermsOfDeliveryContent();

    /**
     * @param string $termsOfPaymentContent
     *
     * @return OrderInterface
     */
    public function setTermsOfPaymentContent($termsOfPaymentContent);

    /**
     * @return string
     */
    public function getTermsOfPaymentContent();

    /**
     * @param \DateTime $created
     *
     * @return OrderInterface
     */
    public function setCreated($created);

    /**
     * @return \DateTime
     */
    public function getCreated();

    /**
     * @param \DateTime $changed
     *
     * @return OrderInterface
     */
    public function setChanged($changed);

    /**
     * @return \DateTime
     */
    public function getChanged();

    /**
     * @param \DateTime $desiredDeliveryDate
     *
     * @return OrderInterface
     */
    public function setDesiredDeliveryDate($desiredDeliveryDate);

    /**
     * @return \DateTime
     */
    public function getDesiredDeliveryDate();

    /**
     * @return integer
     */
    public function getId();

    /**
     * @param OrderAddressInterface $deliveryAddress
     *
     * @return OrderAddressInterface
     */
    public function setDeliveryAddress(OrderAddressInterface $deliveryAddress = null);

    /**
     * @return OrderAddressInterface
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
     * @return OrderAddressInterface
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
     * @return OrderStatusInterface
     */
    public function getStatus();

    /**
     * @param ContactInterface $contact
     *
     * @return OrderInterface
     */
    public function setCustomerContact(ContactInterface $contact = null);

    /**
     * @return ContactInterface
     */
    public function getCustomerContact();

    /**
     * @param ItemInterface $items
     *
     * @return OrderInterface
     */
    public function addItem(ItemInterface $items);

    /**
     * @param ItemInterface $items
     */
    public function removeItem(ItemInterface $items);

    /**
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getItems();

    /**
     * @param UserInterface $changer
     *
     * @return OrderInterface
     */
    public function setChanger(UserInterface $changer = null);

    /**
     *
     * @return UserInterface
     */
    public function getChanger();

    /**
     * @param UserInterface $creator
     *
     * @return OrderInterface
     */
    public function setCreator(UserInterface $creator = null);

    /**
     * @return UserInterface
     */
    public function getCreator();

    /**
     * @param float $totalPrice
     *
     * @return OrderInterface
     */
    public function setTotalPrice($totalPrice);

    /**
     * @return float
     */
    public function getTotalPrice();

    /**
     * @param float $totalNetPrice
     *
     * @return OrderInterface
     */
    public function setTotalNetPrice($totalNetPrice);

    /**
     * @return float
     */
    public function getTotalNetPrice();

    /**
     * @param float $totalRecurringNetPrice
     *
     * @return OrderInterface
     */
    public function setTotalRecurringNetPrice($totalRecurringNetPrice);

    /**
     * @return float
     */
    public function getTotalRecurringNetPrice();

    /**
     * @param float $totalRecurringPrice
     *
     * @return OrderInterface
     */
    public function setTotalRecurringPrice($totalRecurringPrice);

    /**
     * @return float
     */
    public function getTotalRecurringPrice();

    /**
     * @param \DateTime $orderDate
     *
     * @return OrderInterface
     */
    public function setOrderDate($orderDate);

    /**
     * @return \DateTime
     */
    public function getOrderDate();

    /**
    * Updates the total net price.
    */
    public function updateTotalNetPrice();

    /**
     * @param float $shippingCosts
     *
     * @return OrderInterface
     */
    public function setShippingCosts($shippingCosts);

    /**
     * @return float
     */
    public function getShippingCosts();

    /**
     * @param float $netShippingCosts
     *
     * @return OrderInterface
     */
    public function setNetShippingCosts($netShippingCosts);

    /**
     * @return float
     */
    public function getNetShippingCosts();
}
