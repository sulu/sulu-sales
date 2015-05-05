<?php

namespace Sulu\Bundle\Sales\OrderBundle\Entity;

use Sulu\Bundle\ContactBundle\Entity\AccountInterface;
use Sulu\Bundle\ContactBundle\Entity\Contact;
use Sulu\Bundle\Sales\CoreBundle\Entity\ItemInterface;
use Sulu\Bundle\Sales\CoreBundle\Entity\OrderAddressInterface;
use Sulu\Bundle\ContactExtensionBundle\Entity\TermsOfDelivery;
use Sulu\Bundle\ContactExtensionBundle\Entity\TermsOfPayment;

abstract class AbstractOrder extends BaseOrder
{
    /**
     * @var string
     */
    protected $termsOfDeliveryContent;

    /**
     * @var string
     */
    protected $termsOfPaymentContent;

    /**
     * @var TermsOfDelivery
     */
    protected $termsOfDelivery;

    /**
     * @var TermsOfPayment
     */
    protected $termsOfPayment;

    /**
     * @var Account
     */
    protected $customerAccount;

    /**
     * @var Contact
     */
    protected $responsibleContact;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $items;

    /**
     * @var OrderStatusInterface
     */
    protected $status;

    /**
     * @var OrderAddressInterface
     */
    protected $deliveryAddress;

    /**
     * @var OrderAddressInterface
     */
    protected $invoiceAddress;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->items = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set termsOfDeliveryContent
     *
     * @param string $termsOfDeliveryContent
     *
     * @return Order
     */
    public function setTermsOfDeliveryContent($termsOfDeliveryContent)
    {
        $this->termsOfDeliveryContent = $termsOfDeliveryContent;

        return $this;
    }

    /**
     * Get termsOfDeliveryContent
     *
     * @return string
     */
    public function getTermsOfDeliveryContent()
    {
        return $this->termsOfDeliveryContent;
    }

    /**
     * Set termsOfPaymentContent
     *
     * @param string $termsOfPaymentContent
     *
     * @return Order
     */
    public function setTermsOfPaymentContent($termsOfPaymentContent)
    {
        $this->termsOfPaymentContent = $termsOfPaymentContent;

        return $this;
    }

    /**
     * Get termsOfPaymentContent
     *
     * @return string
     */
    public function getTermsOfPaymentContent()
    {
        return $this->termsOfPaymentContent;
    }

    /**
     * Set termsOfDelivery
     *
     * @param TermsOfDelivery $termsOfDelivery
     *
     * @return Order
     */
    public function setTermsOfDelivery(TermsOfDelivery $termsOfDelivery = null)
    {
        $this->termsOfDelivery = $termsOfDelivery;

        return $this;
    }

    /**
     * Get termsOfDelivery
     *
     * @return TermsOfDelivery
     */
    public function getTermsOfDelivery()
    {
        return $this->termsOfDelivery;
    }

    /**
     * Set termsOfPayment
     *
     * @param TermsOfPayment $termsOfPayment
     *
     * @return Order
     */
    public function setTermsOfPayment(TermsOfPayment $termsOfPayment = null)
    {
        $this->termsOfPayment = $termsOfPayment;

        return $this;
    }

    /**
     * Get termsOfPayment
     *
     * @return TermsOfPayment
     */
    public function getTermsOfPayment()
    {
        return $this->termsOfPayment;
    }

    /**
     * Set account
     *
     * @param Account $account
     *
     * @return Order
     */
    public function setCustomerAccount(AccountInterface $account = null)
    {
        $this->customerAccount = $account;

        return $this;
    }

    /**
     * Get account
     *
     * @return Account
     */
    public function getCustomerAccount()
    {
        return $this->customerAccount;
    }

    /**
     * Set responsibleContact
     *
     * @param Contact $responsibleContact
     *
     * @return Order
     */
    public function setResponsibleContact(Contact $responsibleContact = null)
    {
        $this->responsibleContact = $responsibleContact;

        return $this;
    }

    /**
     * Get responsibleContact
     *
     * @return Contact
     */
    public function getResponsibleContact()
    {
        return $this->responsibleContact;
    }

    /**
     * {@inheritDoc}
     */
    public function addItem(ItemInterface $items)
    {
        $this->items[] = $items;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function removeItem(ItemInterface $items)
    {
        $this->items->removeElement($items);
    }

    /**
     * {@inheritDoc}
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * {@inheritDoc}
     */
    public function setStatus(OrderStatusInterface $status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * {@inheritDoc}
     */
    public function setDeliveryAddress(OrderAddressInterface $deliveryAddress = null)
    {
        $this->deliveryAddress = $deliveryAddress;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getDeliveryAddress()
    {
        return $this->deliveryAddress;
    }

    /**
     * {@inheritDoc}
     */
    public function setInvoiceAddress(OrderAddressInterface $invoiceAddress = null)
    {
        $this->invoiceAddress = $invoiceAddress;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getInvoiceAddress()
    {
        return $this->invoiceAddress;
    }
}
