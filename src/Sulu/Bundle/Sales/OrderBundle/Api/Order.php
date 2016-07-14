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
use Hateoas\Configuration\Annotation\Relation;
use JMS\Serializer\Annotation\VirtualProperty;
use JMS\Serializer\Annotation\SerializedName;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\Exclude;
use Sulu\Bundle\ContactBundle\Entity\AccountInterface;
use Sulu\Bundle\ContactExtensionBundle\Entity\TermsOfDelivery;
use Sulu\Bundle\ContactExtensionBundle\Entity\TermsOfPayment;
use Sulu\Bundle\Sales\CoreBundle\Api\OrderAddress;
use Sulu\Bundle\Sales\CoreBundle\Core\SalesDocument;
use Sulu\Bundle\Sales\CoreBundle\Entity\ItemInterface;
use Sulu\Bundle\Sales\CoreBundle\Entity\OrderAddressInterface;
use Sulu\Bundle\Sales\CoreBundle\Item\ItemFactoryInterface;
use Sulu\Bundle\PricingBundle\Pricing\PriceFormatter;
use Sulu\Bundle\Sales\OrderBundle\Api\OrderStatus as ApiOrderStatus;
use Sulu\Bundle\Sales\OrderBundle\Entity\OrderInterface;
use Sulu\Bundle\Sales\OrderBundle\Entity\OrderStatus;
use Sulu\Component\Contact\Model\ContactInterface;
use Sulu\Component\Rest\ApiWrapper;
use Sulu\Component\Security\Authentication\UserInterface;

/**
 * The order class which will be exported to the API
 *
 * @Relation("self", href="expr('/api/admin/orders/' ~ object.getId())")
 */
class Order extends ApiWrapper implements SalesDocument, ApiOrderInterface
{
    /**
     * Define if deletion is allowed.
     *
     * @var array
     */
    private $allowDelete = array();

    /**
     * Cache for items.
     *
     * @Exclude
     *
     * @var array
     */
    private $cacheItems;

    /**
     * Defines the status code of the cart.
     *
     * @var null|array
     */
    private $cartStatusCodes = null;

    /**
     * Groups items by suppliers.
     *
     * @Exclude
     *
     * @var array
     */
    private $groupedItems = array();

    /**
     * Defines if changes of items have been changed since last view.
     *
     * @var bool
     */
    private $hasChangedPrices = false;

    /**
     * @Exclude
     *
     * @var ItemFactoryInterface
     */
    private $itemFactory;

    /**
     * Indicated if items have been changed.
     *
     * @var bool
     */
    private $itemsChanged = false;

    /**
     * Define permissions for front-end.
     *
     * @var array
     */
    private $permissions = array();

    /**
     * Define workflows for front-end.
     *
     * @var array
     */
    private $workflows = array();

    /**
     * @var PriceFormatter
     */
    private $priceFormatter;

    /**
     * @param OrderInterface $order The order to wrap
     * @param string $locale The locale of this order
     * @param ItemFactoryInterface $itemFactory
     * @param PriceFormatter $priceFormatter
     */
    public function __construct(OrderInterface $order, $locale, $itemFactory, PriceFormatter $priceFormatter)
    {
        $this->entity = $order;
        $this->locale = $locale;
        $this->itemFactory = $itemFactory;
        $this->priceFormatter = $priceFormatter;
    }

    /**
     * Returns the id of the order entity.
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
     * @Groups({"Default","cart"})
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
     * @Groups({"Default","cart"})
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
     * @VirtualProperty
     * @SerializedName("sessionId")
     *
     * @return string
     */
    public function getSessionId()
    {
        return $this->entity->getSessionId();
    }

    /**
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
     * @return ApiOrderStatus|null
     */
    public function getStatus()
    {
        if ($this->entity && $this->entity->getStatus()) {
            return new ApiOrderStatus($this->entity->getStatus(), $this->locale);
        } else {
            return null;
        }
    }

    /**
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
     * @VirtualProperty
     * @SerializedName("currencyCode")
     * @Groups({"Default","cart"})
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
     * @Groups({"Default","cart"})
     *
     * @return float
     */
    public function getDeliveryCost()
    {
        return $this->entity->getDeliveryCost();
    }

    /**
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
     * @VirtualProperty
     * @SerializedName("costCentre")
     * @Groups({"Default","cart"})
     *
     * @return string
     */
    public function getCostCentre()
    {
        return $this->entity->getCostCentre();
    }

    /**
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
     * @VirtualProperty
     * @SerializedName("commission")
     * @Groups({"Default","cart"})
     *
     * @return string
     */
    public function getCommission()
    {
        return $this->entity->getCommission();
    }

    /**
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
     * @VirtualProperty
     * @SerializedName("desiredDeliveryDate")
     * @Groups({"Default","cart"})
     *
     * @return \DateTime
     */
    public function getDesiredDeliveryDate()
    {
        return $this->entity->getDesiredDeliveryDate();
    }

    /**
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
     * @param AccountInterface $account
     *
     * @return Order
     */
    public function setCustomerAccount(AccountInterface $account = null)
    {
        $this->entity->setCustomerAccount($account);

        return $this;
    }

    /**
     * @VirtualProperty
     * @SerializedName("customerAccount")
     *
     * @return AccountInterface
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
     * @param ContactInterface $contact
     *
     * @return Order
     */
    public function setCustomerContact(ContactInterface $contact = null)
    {
        $this->entity->setCustomerContact($contact);

        return $this;
    }

    /**
     * @VirtualProperty
     * @SerializedName("customerContact")
     *
     * @return ContactInterface
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
     * @param ContactInterface $responsibleContact
     *
     * @return Order
     */
    public function setResponsibleContact(ContactInterface $responsibleContact = null)
    {
        $this->entity->setResponsibleContact($responsibleContact);

        return $this;
    }

    /**
     * @VirtualProperty
     * @SerializedName("responsibleContact")
     *
     * @return ContactInterface
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
     * @VirtualProperty
     * @SerializedName("items")
     * @Groups({"Default","cart"})
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
                $this->cacheItems[] = $this->itemFactory->createApiEntity($item, $this->locale, $this->getCurrencyCode());
            }
        }

        return $this->cacheItems;
    }

    /**
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
     * @param $supplierItems
     *
     * @return self
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
     * @return UserInterface
     */
    public function getChanger()
    {
        return $this->entity->getChanger();
    }

    /**
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
     * @return UserInterface
     */
    public function getCreator()
    {
        return $this->entity->getCreator();
    }

    /**
     * @param OrderAddressInterface $deliveryAddress
     *
     * @return Order
     */
    public function setDeliveryAddress(OrderAddressInterface $deliveryAddress = null)
    {
        $this->entity->setDeliveryAddress($deliveryAddress);

        return $this;
    }

    /**
     * @VirtualProperty
     * @SerializedName("deliveryAddress")
     * @Groups({"Default","cart"})
     *
     * @return OrderAddressInterface
     */
    public function getDeliveryAddress()
    {
        if ($address = $this->entity->getDeliveryAddress()) {
            return new OrderAddress($address);
        }

        return null;
    }

    /**
     * @param OrderAddressInterface $invoiceAddress
     *
     * @return Order
     */
    public function setInvoiceAddress(OrderAddressInterface $invoiceAddress = null)
    {
        $this->entity->setInvoiceAddress($invoiceAddress);

        return $this;
    }

    /**
     * @VirtualProperty
     * @SerializedName("invoiceAddress")
     * @Groups({"Default","cart"})
     *
     * @return OrderAddressInterface
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
     * @param float $totalNetPrice
     *
     * @return self
     */
    public function setTotalNetPrice($totalNetPrice)
    {
        $this->entity->setTotalNetPrice($totalNetPrice);

        return $this;
    }

    /**
     * @VirtualProperty
     * @SerializedName("totalNetPrice")
     * @Groups({"Default","cart"})
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
     * @Groups({"Default","cart"})
     *
     * @param string $locale
     *
     * @return string
     */
    public function getTotalNetPriceFormatted($locale = null)
    {
        return $this->priceFormatter->format((float)$this->entity->getTotalNetPrice(), null, $locale);
    }

    /**
     * @VirtualProperty
     * @SerializedName("deliveryCostFormatted")
     * @Groups({"Default","cart"})
     *
     * @param string $locale
     *
     * @return string
     */
    public function getDeliveryCostFormatted($locale = null)
    {
        return $this->priceFormatter->format((float)$this->entity->getDeliveryCost(), null, $locale);
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
     * @Groups({"Default","cart"})
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
            'icon' => 'fa-shopping-cart',
            'id' => $this->getId(),
            'pdfBaseUrl' => $this->getPdfBaseUrl(),
            'translationkey' => 'salesorder.order'
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
        return $this->workflows;
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
     * @VirtualProperty
     * @SerializedName("hasChangedPrices")
     * @Groups({"Default","cart"})
     *
     * @return bool
     */
    public function hasChangedPrices()
    {
        return $this->hasChangedPrices;
    }

    /**
     * @param $hasChangedPrices
     *
     * @return Order
     */
    public function setHasChangedPrices($hasChangedPrices)
    {
        $this->hasChangedPrices = $hasChangedPrices;

        return $this;
    }

    /**
     * Get status codes of cart
     *
     * @VirtualProperty
     * @SerializedName("cartErrorCodes")
     * @Groups({"Default","cart"})
     *
     * @return array
     */
    public function getCartErrorCodes()
    {
        return $this->cartStatusCodes;
    }

    /**
     * Adds a status code to cart
     *
     * @param int
     *
     * @return Order
     */
    public function addCartErrorCode($statusCode)
    {
        $this->cartStatusCodes[] = $statusCode;

        return $this;
    }

    /**
     * Returns if cart is in cart_pending state
     *
     * @VirtualProperty
     * @SerializedName("isPending")
     * @Groups({"Default","cart"})
     *
     * @return bool
     */
    public function isPending()
    {
        return $this->getStatus()->getId() === OrderStatus::STATUS_CART_PENDING;
    }

    /**
     * Set if Deletion is allowed.
     *
     * @param bool $allow
     */
    public function setAllowDelete($allow)
    {
        $this->allowDelete = $allow;
    }

    /**
     * Return if deletion is allowed.
     *
     * @VirtualProperty
     * @SerializedName("allowDelete")
     * @Groups({"fullOrder"})
     *
     * @return bool
     */
    public function allowDelete()
    {
        return $this->allowDelete;
    }

    /**
     * @VirtualProperty
     * @SerializedName("internalNote")
     *
     * @return string
     */
    public function getInternalNote()
    {
        return $this->entity->getInternalNote();
    }

    /**
     * @param string $note
     *
     * @return self
     */
    public function setInternalNote($note)
    {
        $this->entity->setInternalNote($note);

        return $this;
    }

    /**
     * @param float $totalRecurringNetPrice
     *
     * @return self
     */
    public function setTotalRecurringNetPrice($totalRecurringNetPrice)
    {
        $this->entity->setTotalRecurringNetPrice($totalRecurringNetPrice);

        return $this;
    }

    /**
     * @VirtualProperty
     * @SerializedName("totalRecurringNetPrice")
     * @Groups({"Default","cart"})
     *
     * @return float
     */
    public function getTotalRecurringNetPrice()
    {
        return $this->entity->getTotalRecurringNetPrice();
    }
}
