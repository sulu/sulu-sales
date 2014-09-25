<?php

namespace Sulu\Bundle\Sales\OrderBundle\Api;

use JMS\Serializer\Annotation\VirtualProperty;
use Sulu\Bundle\ContactBundle\Entity\Account;
use Sulu\Bundle\ContactBundle\Entity\Contact;
use Sulu\Bundle\ContactBundle\Entity\TermsOfDelivery;
use Sulu\Bundle\ContactBundle\Entity\TermsOfPayment;
use Sulu\Bundle\Sales\CoreBundle\Api\Item;
use Sulu\Bundle\Sales\CoreBundle\Core\SalesDocument;
use Sulu\Bundle\Sales\OrderBundle\Entity\Order as OrderEntity;
use Sulu\Bundle\Sales\OrderBundle\Entity\OrderAddress as OrderAddressEntity;
use Sulu\Component\Rest\ApiWrapper;
use Hateoas\Configuration\Annotation\Relation;
use JMS\Serializer\Annotation\SerializedName;
use Sulu\Component\Security\UserInterface;
use DateTime;

/**
 * The order class which will be exported to the API
 * @package Sulu\Bundle\Sales\OrderBundle\Api
 * @Relation("self", href="expr('/api/admin/orders/' ~ object.getId())")
 */
class Order extends ApiWrapper implements SalesDocument
{
    private $permissions = array();
    private $workflows = array();

    /**
     * @param OrderEntity $order The order to wrap
     * @param string $locale The locale of this order
     */
    public function __construct(OrderEntity $order, $locale)
    {
        $this->entity = $order;
        $this->locale = $locale;
    }

    /**
     * Returns the id of the order entity
     * @return int
     * @VirtualProperty
     * @SerializedName("id")
     */
    public function getId()
    {
        return $this->entity->getId();
    }

    /**
     * @return int
     * @VirtualProperty
     * @SerializedName("number")
     */
    public function getNumber()
    {
        return $this->entity->getNumber();
    }

    /**
     * @param $number
     * @return Order
     */
    public function setNumber($number)
    {
        $this->entity->setNumber($number);
        return $this;
    }

    /**
     * @VirtualProperty
     * @SerializedName("created")
     * @return DateTime
     */
    public function getCreated()
    {
        return $this->entity->getCreated();
    }

    /**
     * @param DateTime $created
     * @return Order
     */
    public function setCreated(DateTime $created)
    {
        $this->entity->setCreated($created);
        return $this;
    }

    /**
     * @VirtualProperty
     * @SerializedName("changed")
     * @return DateTime
     */
    public function getChanged()
    {
        return $this->entity->getChanged();
    }

    /**
     * @param DateTime $changed
     * @return Order
     */
    public function setChanged(DateTime $changed)
    {
        $this->entity->setChanged($changed);
        return $this;
    }

    /**
     * Set sessionId
     *
     * @param string $sessionId
     * @return Order
     */
    public function setSessionId($sessionId)
    {
        $this->entity->setSessionId($sessionId);

        return $this;
    }

    /**
     * Get sessionId
     * @return string
     * @VirtualProperty
     * @SerializedName("changed")
     */
    public function getSessionId()
    {
        return $this->entity->getSessionId();
    }

    /**
     * Set status
     * @param OrderStatus
     * @return Order
     */
    public function setStatus($status)
    {
        $this->entity->setStatus($status);

        return $this;
    }

    /**
     * Set bitmaskStatus
     *
     * @param integer $bitmaskStatus
     * @return Order
     */
    public function setBitmaskStatus($bitmaskStatus)
    {
        $this->entity->setBitmaskStatus($bitmaskStatus);

        return $this;
    }

    /**
     * Get bitmaskStatus
     * @VirtualProperty
     * @SerializedName("bitmaskStatus")
     * @return integer
     */
    public function getBitmaskStatus()
    {
        return $this->entity->getBitmaskStatus();
    }

    /**
     * Get sessionId
     * @return OrderStatus
     * @VirtualProperty
     * @SerializedName("changed")
     */
    public function getStatus()
    {
        if ($this->entity && $this->entity->getStatus()) {
            return new OrderStatus($this->entity->getStatus(), $this->locale);
        } else {
            return null;
        }
    }

    /**
     * Set currency
     *
     * @param string $currency
     * @return Order
     */
    public function setCurrency($currency)
    {
        $this->entity->setCurrency($currency);

        return $this;
    }

    /**
     * Get currency
     * @VirtualProperty
     * @SerializedName("currency")
     * @return string
     */
    public function getCurrency()
    {
        return $this->entity->getCurrency();
    }

    /**
     * @param string $customerName
     * @return Order
     */
    public function setCustomerName($customerName)
    {
        $this->entity->setCustomerName($customerName);

        return $this;
    }

    /**
     * @VirtualProperty
     * @SerializedName("customerName")
     * @return string
     */
    public function getCustomerName()
    {
        return $this->entity->getCustomerName();
    }

    /**
     * Set termsOfDelivery
     *
     * @param TermsOfDelivery $termsOfDelivery
     * @return Order
     */
    public function setTermsOfDelivery($termsOfDelivery)
    {
        $this->entity->setTermsOfDelivery($termsOfDelivery);

        return $this;
    }

    /**
     * Get termsOfDelivery
     *
     * @return TermsOfDelivery
     * @VirtualProperty
     * @SerializedName("termsOfDelivery")
     */
    public function getTermsOfDelivery()
    {
        if ($terms = $this->entity->getTermsOfDelivery()) {
            return array(
                'id' => $terms->getId(),
                'terms' => $terms->getTerms()
            );
        }

        return null;
    }

    /**
     * Set termsOfPayment
     *
     * @param TermsOfPayment $termsOfPayment
     * @return Order
     */
    public function setTermsOfPayment($termsOfPayment)
    {
        $this->entity->setTermsOfPayment($termsOfPayment);

        return $this;
    }

    /**
     * Get termsOfPayment
     * @return TermsOfPayment
     * @VirtualProperty
     * @SerializedName("termsOfPayment")
     */
    public function getTermsOfPayment()
    {
        if ($terms = $this->entity->getTermsOfPayment()) {
            return array(
                'id' => $terms->getId(),
                'terms' => $terms->getTerms()
            );
        }

        return null;
    }

    /**
     * Set termsOfPayment
     *
     * @param string $termsOfPayment
     * @return Order
     */
    public function setTermsOfPaymentContent($termsOfPayment)
    {
        $this->entity->setTermsOfPaymentContent($termsOfPayment);

        return $this;
    }

    /**
     * Get termsOfPayment
     *
     * @return string
     * @VirtualProperty
     * @SerializedName("termsOfPaymentContent")
     */
    public function getTermsOfPaymentContent()
    {
        return $this->entity->getTermsOfPaymentContent();
    }

    /**
     * Set termsOfDelivery
     *
     * @param string $termsOfDelivery
     * @return Order
     */
    public function setTermsOfDeliveryContent($termsOfDelivery)
    {
        $this->entity->setTermsOfDeliveryContent($termsOfDelivery);

        return $this;
    }

    /**
     * Get termsOfDelivery
     *
     * @return string
     * @VirtualProperty
     * @SerializedName("termsOfDeliveryContent")
     */
    public function getTermsOfDeliveryContent()
    {
        return $this->entity->getTermsOfDeliveryContent();
    }

    /**
     * Set costCentre
     *
     * @param string $costCentre
     * @return Order
     */
    public function setCostCentre($costCentre)
    {
        $this->entity->setCostCentre($costCentre);

        return $this;
    }

    /**
     * Get costCentre
     *
     * @return string
     * @VirtualProperty
     * @SerializedName("costCentre")
     */
    public function getCostCentre()
    {
        return $this->entity->getCostCentre();
    }

    /**
     * Set commission
     *
     * @param string $commission
     * @return Order
     */
    public function setCommission($commission)
    {
        $this->entity->setCommission($commission);

        return $this;
    }

    /**
     * Get commission
     *
     * @return string
     * @VirtualProperty
     * @SerializedName("commission")
     */
    public function getCommission()
    {
        return $this->entity->getCommission();
    }

    /**
     * Set desiredDeliveryDate
     *
     * @param \DateTime $desiredDeliveryDate
     * @return Order
     */
    public function setDesiredDeliveryDate($desiredDeliveryDate)
    {
        $this->entity->setDesiredDeliveryDate($desiredDeliveryDate);

        return $this;
    }

    /**
     * Get desiredDeliveryDate
     *
     * @return \DateTime
     * @VirtualProperty
     * @SerializedName("desiredDeliveryDate")
     */
    public function getDesiredDeliveryDate()
    {
        return $this->entity->getDesiredDeliveryDate();
    }

    /**
     * Set taxfree
     *
     * @param boolean $taxfree
     * @return Order
     */
    public function setTaxfree($taxfree)
    {
        $this->entity->setTaxfree($taxfree);

        return $this;
    }

    /**
     * Get taxfree
     *
     * @return boolean
     * @VirtualProperty
     * @SerializedName("taxfree")
     */
    public function getTaxfree()
    {
        return $this->entity->getTaxfree();
    }

    /**
     * Set account
     *
     * @param Account $account
     * @return Order
     */
    public function setAccount(Account $account = null)
    {
        $this->entity->setAccount($account);

        return $this;
    }

    /**
     * Get account
     *
     * @return Account
     * @VirtualProperty
     * @SerializedName("account")
     */
    public function getAccount()
    {
        if ($account = $this->entity->getAccount()) {
            return array(
                'id' => $account->getId(),
                'name' => $account->getName()
            );
        }

        return null;
    }

    /**
     * Set contact
     *
     * @param Contact $contact
     * @return Order
     */
    public function setContact(Contact $contact = null)
    {
        $this->entity->setContact($contact);

        return $this;
    }

    /**
     * Get contact
     *
     * @return Contact
     * @VirtualProperty
     * @SerializedName("contact")
     */
    public function getContact()
    {
        if ($contact = $this->entity->getContact()) {
            return array(
                'id' => $contact->getId(),
                'fullName' => $contact->getFullName()
            );
        }

        return null;
    }

    /**
     * Set responsibleContact
     *
     * @param Contact $responsibleContact
     * @return Order
     */
    public function setResponsibleContact(Contact $responsibleContact = null)
    {
        $this->entity->setResponsibleContact($responsibleContact);

        return $this;
    }

    /**
     * Get responsibleContact
     *
     * @return Contact
     * @VirtualProperty
     * @SerializedName("responsibleContact")
     */
    public function getResponsibleContact()
    {
        if ($contact = $this->entity->getResponsibleContact()) {
            return array(
                'id' => $contact->getId(),
                'fullName' => $contact->getFullName()
            );
        }

        return null;
    }

    /**
     * Add item
     *
     * @param \Sulu\Bundle\Sales\CoreBundle\Entity\Item $item
     * @return Order
     */
    public function addItem(\Sulu\Bundle\Sales\CoreBundle\Entity\Item $item)
    {
        $this->entity->addItem($item);

        return $this;
    }

    /**
     * Remove item
     *
     * @param \Sulu\Bundle\Sales\CoreBundle\Entity\Item $item
     */
    public function removeItem(\Sulu\Bundle\Sales\CoreBundle\Entity\Item $item)
    {
        $this->entity->removeItem($item);
    }

    /**
     * Get items
     *
     * @return \Doctrine\Common\Collections\Collection
     *
     * @VirtualProperty
     * @SerializedName("items")
     */
    public function getItems()
    {
        $items = array();
        foreach ($this->entity->getItems() as $item) {
            $items[] = new Item($item, $this->locale);
        }
        return $items;
    }

    /**
     * Set changer
     *
     * @param UserInterface $changer
     * @return Order
     */
    public function setChanger(UserInterface $changer = null)
    {
        $this->entity->setChanger($changer);

        return $this;
    }

    /**
     * Get changer
     *
     * @return UserInterface
     */
    public function getChanger()
    {
        return $this->entity->getChanger();
    }

    /**
     * Set creator
     *
     * @param UserInterface $creator
     * @return Order
     */
    public function setCreator(UserInterface $creator = null)
    {
        $this->entity->setCreator($creator);

        return $this;
    }

    /**
     * Get creator
     *
     * @return UserInterface
     */
    public function getCreator()
    {
        return $this->entity->getCreator();
    }

    /**
     * Set deliveryAddress
     *
     * @param OrderAddressEntity $deliveryAddress
     * @return Order
     */
    public function setDeliveryAddress(OrderAddressEntity $deliveryAddress = null)
    {
        $this->entity->setDeliveryAddress($deliveryAddress);

        return $this;
    }

    /**
     * Get deliveryAddress
     *
     * @return OrderAddressEntity
     * @VirtualProperty
     * @SerializedName("deliveryAddress")
     */
    public function getDeliveryAddress()
    {
        return $this->entity->getDeliveryAddress();
    }

    /**
     * Set invoiceAddress
     *
     * @param OrderAddressEntity $invoiceAddress
     * @return Order
     */
    public function setInvoiceAddress(OrderAddressEntity $invoiceAddress = null)
    {
        $this->entity->setInvoiceAddress($invoiceAddress);

        return $this;
    }

    /**
     * Get invoiceAddress
     *
     * @return OrderAddressEntity
     * @VirtualProperty
     * @SerializedName("invoiceAddress")
     */
    public function getInvoiceAddress()
    {
        return $this->entity->getInvoiceAddress();
    }

    /**
     * @param $number
     * @return Order
     */
    public function setOrderNumber($number)
    {
        $this->entity->setOrderNumber($number);

        return $this;
    }

    /**
     * @return string
     * @VirtualProperty
     * @SerializedName("orderNumber")
     */
    public function getOrderNumber()
    {
        return $this->entity->getOrderNumber();
    }

    /**
     * @param $totalPrice
     * @return Order
     */
    public function setTotalPrice($totalPrice)
    {
        $this->entity->setTotalPrice($totalPrice);

        return $this;
    }

    /**
     * @return float
     * @VirtualProperty
     * @SerializedName("totalPrice")
     */
    public function getTotalPrice()
    {
        return $this->entity->getTotalPrice();
    }

    /**
     * @param DateTime
     * @return Order
     */
    public function setOrderDate($orderDate)
    {
        $this->entity->setOrderDate($orderDate);

        return $this;
    }

    /**
     * @return DateTime
     * @VirtualProperty
     * @SerializedName("orderDate")
     */
    public function getOrderDate()
    {
        return $this->entity->getOrderDate();
    }

    /**
     * Returns the data needed for the sales document widget as array
     *
     * @return array
     */
    public function getSalesDocumentData()
    {
        return array(
            'number' => $this->getNumber(),
            'data' => $this->getOrderDate(),
            'type' => 'order',
            'id' => $this->getId()
        );
    }

    /**
     * @param array
     * @return Order
     */
    public function setPermissions(array $permissions)
    {
        $this->permissions = $permissions;

        return $this;
    }

    /**
     * @return array
     * @VirtualProperty
     * @SerializedName("permissions")
     */
    public function getPermissions()
    {
        return $this->permissions;
    }

    /**
     * @param array
     * @return Order
     */
    public function setWorkflows(array $workflows)
    {
        $this->workflows = $workflows;

        return $this;
    }

    /**
     * @return array
     * @VirtualProperty
     * @SerializedName("workflows")
     */
    public function getWorkflows()
    {
        return $this->$workflows;
    }
}
