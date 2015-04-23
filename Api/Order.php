<?php

namespace Sulu\Bundle\Sales\OrderBundle\Api;

use Sulu\Bundle\ContactBundle\Entity\Account;
use Sulu\Bundle\ContactBundle\Entity\Contact;
use Sulu\Bundle\ContactExtensionBundle\Entity\TermsOfDelivery;
use Sulu\Bundle\ContactBundle\Entity\TermsOfPayment;
use Sulu\Bundle\Sales\CoreBundle\Api\Item;
use Sulu\Bundle\Sales\CoreBundle\Core\SalesDocument;
use Sulu\Bundle\Sales\CoreBundle\Entity\ItemInterface;
use Sulu\Bundle\Sales\CoreBundle\Entity\OrderAddress as OrderAddressEntity;
use Sulu\Bundle\Sales\CoreBundle\Api\OrderAddress;
use Sulu\Bundle\Sales\OrderBundle\Entity\Order as OrderEntity;
use Sulu\Component\Rest\ApiWrapper;
use Hateoas\Configuration\Annotation\Relation;
use JMS\Serializer\Annotation\VirtualProperty;
use JMS\Serializer\Annotation\SerializedName;
use JMS\Serializer\Annotation\Groups;
use Sulu\Component\Security\Authentication\UserInterface;
use DateTime;

/**
 * The order class which will be exported to the API
 *
 * @package Sulu\Bundle\Sales\OrderBundle\Api
 * @Relation("self", href="expr('/api/admin/orders/' ~ object.getId())")
 */
class Order extends ApiWrapper implements SalesDocument
{
    /**
     * Define permissions for front-end
     *
     * @var array
     */
    private $permissions = array();

    /**
     * Define workflows for front-end
     *
     * @var array
     */
    private $workflows = array();

    /**
     * Groups items by suppliers
     *
     * @var array
     */
    private $groupedItems = array();

    /**
     * Defines if changes of items have been changed since last view
     *
     * @var bool
     */
    private $hasChangedPrices = false;

    /**
     * Cache for items
     *
     * @var array
     */
    private $cacheItems;

    /**
     * Indicated if items have been changed
     *
     * @var bool
     */
    private $itemsChanged = false;

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
     *
     * @VirtualProperty
     * @SerializedName("id")
     *
     * @return int
     */
    public function getId()
    {
        return $this->entity->getId();
    }

    /**
     * @VirtualProperty
     * @SerializedName("number")
     *
     * @return int
     */
    public function getNumber()
    {
        return $this->entity->getNumber();
    }

    /**
     * @param $number
     *
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
     * @Groups({"cart"})
     *
     * @return DateTime
     */
    public function getCreated()
    {
        return $this->entity->getCreated();
    }

    /**
     * @param DateTime $created
     *
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
     *
     * @return DateTime
     */
    public function getChanged()
    {
        return $this->entity->getChanged();
    }

    /**
     * @param DateTime $changed
     *
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
     *
     * @return Order
     */
    public function setSessionId($sessionId)
    {
        $this->entity->setSessionId($sessionId);

        return $this;
    }

    /**
     * Get sessionId
     *
     * @VirtualProperty
     * @SerializedName("changed")
     *
     * @return string
     */
    public function getSessionId()
    {
        return $this->entity->getSessionId();
    }

    /**
     * Set status
     *
     * @param OrderStatus
     *
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
     *
     * @return Order
     */
    public function setBitmaskStatus($bitmaskStatus)
    {
        $this->entity->setBitmaskStatus($bitmaskStatus);

        return $this;
    }

    /**
     * Get bitmaskStatus
     *
     * @VirtualProperty
     * @SerializedName("bitmaskStatus")
     *
     * @return integer
     */
    public function getBitmaskStatus()
    {
        return $this->entity->getBitmaskStatus();
    }

    /**
     * Get order status
     *
     * @VirtualProperty
     * @SerializedName("status")
     *
     * @return OrderStatus
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
     * Set type
     *
     * @param OrderType
     *
     * @return Order
     */
    public function setType($type)
    {
        $this->entity->setType($type);

        return $this;
    }

    /**
     * Get order tpye
     *
     * @VirtualProperty
     * @SerializedName("type")
     *
     * @return OrderType
     */
    public function getType()
    {
        if ($this->entity && $this->entity->getType()) {
            return new OrderType($this->entity->getType(), $this->locale);
        } else {
            return null;
        }
    }

    /**
     * Set currency-code
     *
     * @param string $currency
     *
     * @return Order
     */
    public function setCurrencyCode($currency)
    {
        $this->entity->setCurrencyCode($currency);

        return $this;
    }

    /**
     * Get currency-code
     *
     * @VirtualProperty
     * @SerializedName("currencyCode")
     * @Groups({"cart"})
     *
     * @return string
     */
    public function getCurrencyCode()
    {
        return $this->entity->getCurrencyCode();
    }

    /**
     * @param string $customerName
     *
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
     *
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
     *
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
     * @VirtualProperty
     * @SerializedName("termsOfDelivery")
     *
     * @return TermsOfDelivery
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
     *
     * @return Order
     */
    public function setTermsOfPayment($termsOfPayment)
    {
        $this->entity->setTermsOfPayment($termsOfPayment);

        return $this;
    }

    /**
     * Get termsOfPayment
     *
     * @VirtualProperty
     * @SerializedName("termsOfPayment")
     *
     * @return TermsOfPayment
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
     *
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
     * @VirtualProperty
     * @SerializedName("termsOfPaymentContent")
     *
     * @return string
     */
    public function getTermsOfPaymentContent()
    {
        return $this->entity->getTermsOfPaymentContent();
    }

    /**
     * Set termsOfDelivery
     *
     * @param string $termsOfDelivery
     *
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
     * @VirtualProperty
     * @SerializedName("termsOfDeliveryContent")
     *
     * @return string
     */
    public function getTermsOfDeliveryContent()
    {
        return $this->entity->getTermsOfDeliveryContent();
    }

    /**
     * @param float $deliveryCost
     *
     * @return Order
     */
    public function setDeliveryCost($deliveryCost)
    {
        $this->entity->setDeliveryCost($deliveryCost);

        return $this;
    }

    /**
     * @VirtualProperty
     * @SerializedName("deliveryCost")
     * @Groups({"cart"})
     *
     * @return float
     */
    public function getDeliveryCost()
    {
        return $this->entity->getDeliveryCost();
    }

    /**
     * Set costCentre
     *
     * @param string $costCentre
     *
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
     * @VirtualProperty
     * @SerializedName("costCentre")
     * @Groups({"cart"})
     *
     * @return string
     */
    public function getCostCentre()
    {
        return $this->entity->getCostCentre();
    }

    /**
     * Set commission
     *
     * @param string $commission
     *
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
     * @VirtualProperty
     * @SerializedName("commission")
     * @Groups({"cart"})
     *
     * @return string
     */
    public function getCommission()
    {
        return $this->entity->getCommission();
    }

    /**
     * Set desiredDeliveryDate
     *
     * @param \DateTime $desiredDeliveryDate
     *
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
     * @VirtualProperty
     * @SerializedName("desiredDeliveryDate")
     * @Groups({"cart"})
     *
     * @return \DateTime
     */
    public function getDesiredDeliveryDate()
    {
        return $this->entity->getDesiredDeliveryDate();
    }

    /**
     * Set taxfree
     *
     * @param boolean $taxfree
     *
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
     * @VirtualProperty
     * @SerializedName("taxfree")
     *
     * @return boolean
     */
    public function getTaxfree()
    {
        return $this->entity->getTaxfree();
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
        $this->entity->setCustomerAccount($account);

        return $this;
    }

    /**
     * Get account
     *
     * @VirtualProperty
     * @SerializedName("customerAccount")
     *
     * @return Account
     */
    public function getCustomerAccount()
    {
        if ($account = $this->entity->getCustomerAccount()) {
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
     *
     * @return Order
     */
    public function setCustomerContact(Contact $contact = null)
    {
        $this->entity->setCustomerContact($contact);

        return $this;
    }

    /**
     * Get contact
     *
     * @VirtualProperty
     * @SerializedName("customerContact")
     *
     * @return Contact
     */
    public function getCustomerContact()
    {
        $contact = $this->entity->getCustomerContact();
        if ($contact) {
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
     *
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
     * @VirtualProperty
     * @SerializedName("responsibleContact")
     *
     * @return Contact
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
     * @param ItemInterface $item
     *
     * @return Order
     */
    public function addItem(ItemInterface $item)
    {
        $this->itemsChanged = true;
        $this->entity->addItem($item);

        return $this;
    }

    /**
     * Remove item
     *
     * @param ItemInterface $item
     *
     * @return Order
     */
    public function removeItem(ItemInterface $item)
    {
        $this->itemsChanged = true;
        $this->entity->removeItem($item);

        return $this;
    }

    /**
     * Get items
     *
     * @VirtualProperty
     * @SerializedName("items")
     * @Groups({"cart"})
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getItems()
    {
        if (!$this->itemsChanged && $this->cacheItems && count($this->cacheItems) === count($this->entity->getItems())) {
            return $this->cacheItems;
        } else {
            $this->itemsChanged = false;
            $this->cacheItems = array();
            foreach ($this->entity->getItems() as $item) {
                $this->cacheItems[] = new Item($item, $this->locale, $this->getCurrencyCode());
            }
        }

        return $this->cacheItems;
    }

    /**
     * Get items ordered by suppliers
     *
     * @VirtualProperty
     * @SerializedName("supplierItems")
     * @Groups({"cartExtended"})
     *
     * @return array
     */
    public function getSupplierItems()
    {
        return $this->groupedItems;
    }

    /**
     * Set supplier items
     *
     * @param $supplierItems
     *
     * @return $this
     */
    public function setSupplierItems($supplierItems)
    {
        $this->groupedItems = $supplierItems;

        return $this;
    }

    /**
     * Get item entity by id
     *
     * @param $id
     *
     * @return mixed
     */
    public function getItem($id)
    {
        foreach ($this->entity->getItems() as $item) {
            if ($item->getId() === $id) {
                return $item;
            }
        }

        return null;
    }

    /**
     * Set changer
     *
     * @param UserInterface $changer
     *
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
     *
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
     *
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
     * @VirtualProperty
     * @SerializedName("deliveryAddress")
     * @Groups({"cart"})
     *
     * @return OrderAddressEntity
     */
    public function getDeliveryAddress()
    {
        if ($address = $this->entity->getDeliveryAddress()) {
            return new OrderAddress($address);
        }

        return null;
    }

    /**
     * Set invoiceAddress
     *
     * @param OrderAddressEntity $invoiceAddress
     *
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
     * @VirtualProperty
     * @SerializedName("invoiceAddress")
     * @Groups({"cart"})
     *
     * @return OrderAddressEntity
     */
    public function getInvoiceAddress()
    {
        if ($address = $this->entity->getInvoiceAddress()) {
            return new OrderAddress($address);
        }
    }

    /**
     * @param $number
     *
     * @return Order
     */
    public function setOrderNumber($number)
    {
        $this->entity->setOrderNumber($number);

        return $this;
    }

    /**
     * @VirtualProperty
     * @SerializedName("orderNumber")
     *
     * @return string
     */
    public function getOrderNumber()
    {
        return $this->entity->getOrderNumber();
    }

    /**
     * @param $totalNetPrice
     *
     * @return $this
     */
    public function setTotalNetPrice($totalNetPrice)
    {
        $this->entity->setTotalNetPrice($totalNetPrice);

        return $this;
    }

    /**
     * @VirtualProperty
     * @SerializedName("totalNetPrice")
     * @Groups({"cart"})
     *
     * @return float
     */
    public function getTotalNetPrice()
    {
        return $this->entity->getTotalNetPrice();
    }

    /**
     * @VirtualProperty
     * @SerializedName("totalNetPriceFormatted")
     * @Groups({"cart"})
     *
     * @return string
     */
    public function getTotalNetPriceFormatted($locale = null)
    {
        $formatter = $this->getFormatter($locale);

        return $formatter->format((float)$this->entity->getTotalNetPrice());
    }

    /**
     * @VirtualProperty
     * @SerializedName("deliveryCostFormatted")
     * @Groups({"cart"})
     *
     * @return string
     */
    public function getDeliveryCostFormatted($locale = null)
    {
        $formatter = $this->getFormatter($locale);

        return $formatter->format((float)$this->entity->getDeliveryCost());
    }

    /**
     * @param DateTime
     *
     * @return Order
     */
    public function setOrderDate($orderDate)
    {
        $this->entity->setOrderDate($orderDate);

        return $this;
    }

    /**
     * @VirtualProperty
     * @SerializedName("orderDate")
     * @Groups({"cart"})
     *
     * @return DateTime
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
            'id' => $this->getId(),
            'pdfBaseUrl' => $this->getPdfBaseUrl(),
        );
    }

    /**
     * @param array
     *
     * @return Order
     */
    public function setPermissions(array $permissions)
    {
        $this->permissions = $permissions;

        return $this;
    }

    /**
     * @VirtualProperty
     * @SerializedName("permissions")
     *
     * @return array
     */
    public function getPermissions()
    {
        return $this->permissions;
    }

    /**
     * @param array
     *
     * @return Order
     */
    public function setWorkflows(array $workflows)
    {
        $this->workflows = $workflows;

        return $this;
    }

    /**
     * @VirtualProperty
     * @SerializedName("workflows")
     *
     * @return array
     */
    public function getWorkflows()
    {
        return $this->$workflows;
    }

    /**
     * Returns url for generating the documents pdf
     *
     * @return string
     */
    public function getPdfBaseUrl()
    {
        return self::$pdfBaseUrl;
    }

    /**
     * @param $locale
     *
     * @return Formatter
     */
    private function getFormatter($locale)
    {
        $sysLocale = $locale ? $locale : 'de-AT';
        $formatter = new \NumberFormatter($sysLocale, \NumberFormatter::DECIMAL);
        $formatter->setAttribute(\NumberFormatter::MIN_FRACTION_DIGITS, 2);
        $formatter->setAttribute(\NumberFormatter::DECIMAL_ALWAYS_SHOWN, 1);

        return $formatter;
    }

    /**
     * @VirtualProperty
     * @SerializedName("hasChangedPrices")
     * @Groups({"cart"})
     *
     * @return bool
     */
    public function hasChangedPrices()
    {
        return $this->hasChangedPrices;
    }

    /**
     * Set changed prices
     *
     * @param $hasChangedPrices
     *
     * @return $this
     */
    public function setHasChangedPrices($hasChangedPrices)
    {
        $this->hasChangedPrices = $hasChangedPrices;

        return $this;
    }
}
