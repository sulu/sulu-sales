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

use DateTime;
use Sulu\Bundle\ContactBundle\Entity\AccountInterface;
use Sulu\Bundle\ContactExtensionBundle\Entity\TermsOfDelivery;
use Sulu\Bundle\ContactExtensionBundle\Entity\TermsOfPayment;
use Sulu\Bundle\Sales\CoreBundle\Entity\ItemInterface;
use Sulu\Bundle\Sales\CoreBundle\Entity\OrderAddress as OrderAddressEntity;
use Sulu\Bundle\Sales\CoreBundle\Entity\OrderAddressInterface;
use Sulu\Component\Contact\Model\ContactInterface;
use Sulu\Component\Security\Authentication\UserInterface;

interface ApiOrderInterface
{
    /**
     * Returns the id of the order entity.
     *
     * @return int
     */
    public function getId();

    /**
     * @return string
     */
    public function getNumber();

    /**
     * @param string $number
     *
     * @return self
     */
    public function setNumber($number);

    /**
     * @return DateTime
     */
    public function getCreated();

    /**
     * @param DateTime $created
     *
     * @return self
     */
    public function setCreated(DateTime $created);

    /**
     * @return DateTime
     */
    public function getChanged();

    /**
     * @param DateTime $changed
     *
     * @return self
     */
    public function setChanged(DateTime $changed);

    /**
     * @param OrderStatus $status
     *
     * @return self
     */
    public function setStatus($status);

    /**
     * @return OrderStatus
     */
    public function getStatus();

    /**
     * @param string $currency
     *
     * @return self
     */
    public function setCurrencyCode($currency);

    /**
     * @return string
     */
    public function getCurrencyCode();

    /**
     * @param string $customerName
     *
     * @return self
     */
    public function setCustomerName($customerName);

    /**
     * @return string
     */
    public function getCustomerName();

    /**
     * @param TermsOfDelivery $termsOfDelivery
     *
     * @return self
     */
    public function setTermsOfDelivery(TermsOfDelivery $termsOfDelivery);

    /**
     * @return TermsOfDelivery
     */
    public function getTermsOfDelivery();

    /**
     * @param TermsOfPayment $termsOfPayment
     *
     * @return self
     */
    public function setTermsOfPayment(TermsOfPayment $termsOfPayment);

    /**
     * @return TermsOfPayment
     */
    public function getTermsOfPayment();

    /**
     * @param string $termsOfPayment
     *
     * @return self
     */
    public function setTermsOfPaymentContent($termsOfPayment);

    /**
     * @return string
     */
    public function getTermsOfPaymentContent();

    /**
     * @param string $termsOfDelivery
     *
     * @return self
     */
    public function setTermsOfDeliveryContent($termsOfDelivery);

    /**
     * @return string
     */
    public function getTermsOfDeliveryContent();

    /**
     * @param float $netShippingCosts
     *
     * @return self
     */
    public function setNetShippingCosts($netShippingCosts);

    /**
     * @return float
     */
    public function getNetShippingCosts();

    /**
     * @param string $costCentre
     *
     * @return self
     */
    public function setCostCentre($costCentre);

    /**
     * @return string
     */
    public function getCostCentre();

    /**
     * @param string $commission
     *
     * @return self
     */
    public function setCommission($commission);

    /**
     * @return string
     */
    public function getCommission();

    /**
     * @param \DateTime $desiredDeliveryDate
     *
     * @return self
     */
    public function setDesiredDeliveryDate($desiredDeliveryDate);

    /**
     * @return \DateTime
     */
    public function getDesiredDeliveryDate();

    /**
     * @param boolean $taxfree
     *
     * @return self
     */
    public function setTaxfree($taxfree);

    /**
     * @return boolean
     */
    public function getTaxfree();

    /**
     * @param AccountInterface|null $account
     *
     * @return self
     */
    public function setCustomerAccount(AccountInterface $account = null);

    /**
     * @return AccountInterface
     */
    public function getCustomerAccount();

    /**
     * @param ContactInterface|null $contact
     *
     * @return self
     */
    public function setCustomerContact(ContactInterface $contact = null);

    /**
     * @return ContactInterface
     */
    public function getCustomerContact();

    /**
     * @param ContactInterface|null $responsibleContact
     *
     * @return self
     */
    public function setResponsibleContact(ContactInterface $responsibleContact = null);

    /**
     * @return ContactInterface
     */
    public function getResponsibleContact();

    /**
     * @param ItemInterface $item
     *
     * @return self
     */
    public function addItem(ItemInterface $item);

    /**
     * @param ItemInterface $item
     *
     * @return self
     */
    public function removeItem(ItemInterface $item);

    /**
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getItems();

    /**
     * @param string $id
     *
     * @return mixed
     */
    public function getItem($id);

    /**
     * @param UserInterface|null $changer
     *
     * @return self
     */
    public function setChanger(UserInterface $changer = null);

    /**
     * @return UserInterface
     */
    public function getChanger();

    /**
     * @param UserInterface|null $creator
     *
     * @return self
     */
    public function setCreator(UserInterface $creator = null);

    /**
     * @return UserInterface
     */
    public function getCreator();

    /**
     * @param OrderAddressInterface|null $deliveryAddress
     *
     * @return self
     */
    public function setDeliveryAddress(OrderAddressInterface $deliveryAddress = null);

    /**
     * @return OrderAddressEntity
     */
    public function getDeliveryAddress();

    /**
     * @param OrderAddressInterface|null $invoiceAddress
     *
     * @return self
     */
    public function setInvoiceAddress(OrderAddressInterface $invoiceAddress = null);

    /**
     * @return OrderAddressEntity
     */
    public function getInvoiceAddress();

    /**
     * @param $number
     *
     * @return self
     */
    public function setOrderNumber($number);

    /**
     * @return string
     */
    public function getOrderNumber();

    /**
     * @param float $totalNetPrice
     *
     * @return self
     */
    public function setTotalNetPrice($totalNetPrice);

    /**
     * @return float
     */
    public function getTotalNetPrice();

    /**
     * @param string $locale|null
     *
     * @return string
     */
    public function getTotalNetPriceFormatted($locale = null);

    /**
     * @param float $totalRecurringNetPrice
     *
     * @return self
     */
    public function setTotalRecurringNetPrice($totalRecurringNetPrice);

    /**
     * @return float
     */
    public function getTotalRecurringNetPrice();

    /**
     * @param string $locale|null
     *
     * @return string
     */
    public function getTotalRecurringNetPriceFormatted($locale = null);

    /**
     * @param string $locale|null
     *
     * @return string
     */
    public function getNetShippingCostsFormatted($locale = null);

    /**
     * @param DateTime
     *
     * @return self
     */
    public function setOrderDate($orderDate);

    /**
     * @return DateTime
     */
    public function getOrderDate();
}
