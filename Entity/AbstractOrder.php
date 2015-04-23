<?php

namespace Sulu\Bundle\Sales\OrderBundle\Entity;

use Sulu\Bundle\ContactBundle\Entity\Account;
use Sulu\Bundle\ContactBundle\Entity\Contact;
use Sulu\Bundle\ContactBundle\Entity\TermsOfDelivery;
use Sulu\Bundle\ContactBundle\Entity\TermsOfPayment;

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
    public function setCustomerAccount(Account $account = null)
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
}
