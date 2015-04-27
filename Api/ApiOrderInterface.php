<?php
/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\Sales\OrderBundle\Api;

use Sulu\Bundle\ContactBundle\Entity\TermsOfPayment;
use Sulu\Bundle\ContactBundle\Entity\TermsOfDelivery;
use Sulu\Bundle\ContactBundle\Entity\Contact;
use Sulu\Bundle\ContactBundle\Entity\Account;
use Sulu\Component\Security\Authentication\UserInterface;
use Sulu\Bundle\Sales\CoreBundle\Entity\OrderAddress as OrderAddressEntity;
use Sulu\Bundle\Sales\CoreBundle\Entity\OrderAddressInterface;
use Sulu\Bundle\Sales\CoreBundle\Entity\ItemInterface;
use DateTime;

interface ApiOrderInterface
{
    /**
     * Returns the id of the order entity

     * @return int
     */
    public function getId();

    /**
     *
     * @return int
     */
    public function getNumber();

    /**
     * @param $number
     *
     * @return Order
     */
    public function setNumber($number);

    /**
     * @return DateTime
     */
    public function getCreated();

    /**
     * @param DateTime $created
     *
     * @return Order
     */
    public function setCreated(DateTime $created);

    /**
     * @return DateTime
     */
    public function getChanged();

    /**
     * @param DateTime $changed
     *
     * @return Order
     */
    public function setChanged(DateTime $changed);

    /**
     * Set sessionId
     *
     * @param string $sessionId
     *
     * @return Order
     */
    public function setSessionId($sessionId);

    /**
     * Get sessionId
     *
     * @return string
     */
    public function getSessionId();

    /**
     * Set status
     *
     * @param OrderStatus
     *
     * @return Order
     */
    public function setStatus($status);

    /**
     * Set bitmaskStatus
     *
     * @param integer $bitmaskStatus
     *
     * @return Order
     */
    public function setBitmaskStatus($bitmaskStatus);

    /**
     * Get bitmaskStatus
     *
     * @return integer
     */
    public function getBitmaskStatus();

    /**
     * Get order status
     *
     * @return OrderStatus
     */
    public function getStatus();

    /**
     * Set type
     *
     * @param OrderType
     *
     * @return Order
     */
    public function setType($type);

    /**
     * Get order tpye
     *
     * @return OrderType
     */
    public function getType();

    /**
     * Set currency-code
     *
     * @param string $currency
     *
     * @return Order
     */
    public function setCurrencyCode($currency);

    /**
     * Get currency-code
     *
     * @return string
     */
    public function getCurrencyCode();

    /**
     * @param string $customerName
     *
     * @return Order
     */
    public function setCustomerName($customerName);

    /**
     * @return string
     */
    public function getCustomerName();

    /**
     * Set termsOfDelivery
     *
     * @param TermsOfDelivery $termsOfDelivery
     *
     * @return Order
     */
    public function setTermsOfDelivery($termsOfDelivery);

    /**
     * Get termsOfDelivery
     *
     * @return TermsOfDelivery
     */
    public function getTermsOfDelivery();

    /**
     * Set termsOfPayment
     *
     * @param TermsOfPayment $termsOfPayment
     *
     * @return Order
     */
    public function setTermsOfPayment($termsOfPayment);

    /**
     * Get termsOfPayment
     *
     * @return TermsOfPayment
     */
    public function getTermsOfPayment();

    /**
     * Set termsOfPayment
     *
     * @param string $termsOfPayment
     *
     * @return Order
     */
    public function setTermsOfPaymentContent($termsOfPayment);

    /**
     * Get termsOfPayment
     *
     * @return string
     */
    public function getTermsOfPaymentContent();

    /**
     * Set termsOfDelivery
     *
     * @param string $termsOfDelivery
     *
     * @return Order
     */
    public function setTermsOfDeliveryContent($termsOfDelivery);

    /**
     * Get termsOfDelivery
     *
     * @return string
     */
    public function getTermsOfDeliveryContent();

    /**
     * @param float $deliveryCost
     *
     * @return Order
     */
    public function setDeliveryCost($deliveryCost);

    /**
     * @return float
     */
    public function getDeliveryCost();

    /**
     * Set costCentre
     *
     * @param string $costCentre
     *
     * @return Order
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
     * @return Order
     */
    public function setCommission($commission);

    /**
     * Get commission
     *
     * @return string
     */
    public function getCommission();

    /**
     * Set desiredDeliveryDate
     *
     * @param \DateTime $desiredDeliveryDate
     *
     * @return Order
     */
    public function setDesiredDeliveryDate($desiredDeliveryDate);

    /**
     * Get desiredDeliveryDate
     *
     * @return \DateTime
     */
    public function getDesiredDeliveryDate();

    /**
     * Set taxfree
     *
     * @param boolean $taxfree
     *
     * @return Order
     */
    public function setTaxfree($taxfree);

    /**
     * Get taxfree
     *
     * @return boolean
     */
    public function getTaxfree();

    /**
     * Set account
     *
     * @param Account $account
     *
     * @return Order
     */
    public function setCustomerAccount(Account $account = null);

    /**
     * Get account
     *
     * @return Account
     */
    public function getCustomerAccount();

    /**
     * Set contact
     *
     * @param Contact $contact
     *
     * @return Order
     */
    public function setCustomerContact(Contact $contact = null);

    /**
     * Get contact
     *
     * @return Contact
     */
    public function getCustomerContact();

    /**
     * Set responsibleContact
     *
     * @param Contact $responsibleContact
     *
     * @return Order
     */
    public function setResponsibleContact(Contact $responsibleContact = null);

    /**
     * Get responsibleContact
     *
     * @return Contact
     */
    public function getResponsibleContact();

    /**
     * Add item
     *
     * @param ItemInterface $item
     *
     * @return Order
     */
    public function addItem(ItemInterface $item);

    /**
     * Remove item
     *
     * @param ItemInterface $item
     *
     * @return Order
     */
    public function removeItem(ItemInterface $item);

    /**
     * Get items
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getItems();

    /**
     * Get items ordered by suppliers
     *
     * @return array
     */
    public function getSupplierItems();

    /**
     * Set supplier items
     *
     * @param $supplierItems
     *
     * @return $this
     */
    public function setSupplierItems($supplierItems);

    /**
     * Get item entity by id
     *
     * @param $id
     *
     * @return mixed
     */
    public function getItem($id);

    /**
     * Set changer
     *
     * @param UserInterface $changer
     *
     * @return Order
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
     * @return Order
     */
    public function setCreator(UserInterface $creator = null);

    /**
     * Get creator
     *
     * @return UserInterface
     */
    public function getCreator();

    /**
     * Set deliveryAddress
     *
     * @param OrderAddressInterface $deliveryAddress
     *
     * @return Order
     */
    public function setDeliveryAddress(OrderAddressInterface $deliveryAddress = null);

    /**
     * Get deliveryAddress
     *
     * @return OrderAddressEntity
     */
    public function getDeliveryAddress();

    /**
     * Set invoiceAddress
     *
     * @param OrderAddressInterface $invoiceAddress
     *
     * @return Order
     */
    public function setInvoiceAddress(OrderAddressInterface $invoiceAddress = null);

    /**
     * Get invoiceAddress
     *
     * @return OrderAddressEntity
     */
    public function getInvoiceAddress();

    /**
     * @param $number
     *
     * @return Order
     */
    public function setOrderNumber($number);

    /**
     * @return string
     */
    public function getOrderNumber();

    /**
     * @param $totalNetPrice
     *
     * @return $this
     */
    public function setTotalNetPrice($totalNetPrice);

    /**
     * @return float
     */
    public function getTotalNetPrice();

    /**
     * @return string
     */
    public function getTotalNetPriceFormatted($locale = null);

    /**
     * @return string
     */
    public function getDeliveryCostFormatted($locale = null);

    /**
     * @param DateTime
     *
     * @return Order
     */
    public function setOrderDate($orderDate);

    /**
     * @return DateTime
     */
    public function getOrderDate();

    /**
     * Returns url for generating the documents pdf
     *
     * @return string
     */
    public function getPdfBaseUrl();
}
