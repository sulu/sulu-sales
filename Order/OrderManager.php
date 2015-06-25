<?php
/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\Sales\OrderBundle\Order;

use DateTime;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Sulu\Component\Security\Authentication\UserRepositoryInterface;
use Sulu\Component\Persistence\RelationTrait;
use Sulu\Component\Rest\Exception\EntityNotFoundException;
use Sulu\Component\Rest\ListBuilder\Doctrine\FieldDescriptor\DoctrineConcatenationFieldDescriptor;
use Sulu\Component\Rest\ListBuilder\Doctrine\FieldDescriptor\DoctrineFieldDescriptor;
use Sulu\Component\Rest\ListBuilder\Doctrine\FieldDescriptor\DoctrineJoinDescriptor;
use Sulu\Bundle\ContactBundle\Entity\AccountRepository;
use Sulu\Bundle\ContactBundle\Entity\Contact;
use Sulu\Bundle\ProductBundle\Product\ProductManagerInterface;
use Sulu\Bundle\Sales\CoreBundle\Entity\ItemInterface;
use Sulu\Bundle\Sales\CoreBundle\Manager\OrderAddressManager;
use Sulu\Bundle\Sales\CoreBundle\Item\Exception\ItemNotFoundException;
use Sulu\Bundle\Sales\CoreBundle\Item\ItemManager;
use Sulu\Bundle\Sales\CoreBundle\Pricing\GroupedItemsPriceCalculatorInterface;
use Sulu\Bundle\Sales\CoreBundle\Entity\OrderAddress;
use Sulu\Bundle\Sales\OrderBundle\Entity\OrderActivityLog;
use Sulu\Bundle\Sales\OrderBundle\Entity\OrderInterface;
use Sulu\Bundle\Sales\OrderBundle\Entity\OrderRepository;
use Sulu\Bundle\Sales\OrderBundle\Entity\OrderStatus as OrderStatusEntity;
use Sulu\Bundle\Sales\OrderBundle\Entity\OrderStatus;
use Sulu\Bundle\Sales\OrderBundle\Entity\OrderType;
use Sulu\Bundle\Sales\OrderBundle\Order\Exception\MissingOrderAttributeException;
use Sulu\Bundle\Sales\OrderBundle\Order\Exception\OrderDependencyNotFoundException;
use Sulu\Bundle\Sales\OrderBundle\Order\Exception\OrderException;
use Sulu\Bundle\Sales\OrderBundle\Order\Exception\OrderNotFoundException;
use Sulu\Bundle\Sales\OrderBundle\Api\Order;

class OrderManager
{
    use RelationTrait;

    protected static $orderEntityName = 'SuluSalesOrderBundle:Order';
    protected static $contactEntityName = 'SuluContactBundle:Contact';
    protected static $addressEntityName = 'SuluContactBundle:Address';
    protected static $orderStatusEntityName = 'SuluSalesOrderBundle:OrderStatus';
    protected static $orderTypeEntityName = 'SuluSalesOrderBundle:OrderType';
    protected static $orderTypeTranslationEntityName = 'SuluSalesOrderBundle:OrderTypeTranslation';
    protected static $orderAddressEntityName = 'SuluSalesCoreBundle:OrderAddress';
    protected static $orderStatusTranslationEntityName = 'SuluSalesOrderBundle:OrderStatusTranslation';
    protected static $itemEntityName = 'SuluSalesCoreBundle:Item';
    protected static $termsOfDeliveryEntityName = 'SuluContactExtensionBundle:TermsOfDelivery';
    protected static $termsOfPaymentEntityName = 'SuluContactExtensionBundle:TermsOfPayment';

    private $currentLocale;

    /**
     * @var ObjectManager
     */
    private $em;

    /**
     * @var ItemManager
     */
    private $itemManager;

    /**
     * @var OrderRepository
     */
    private $orderRepository;

    /**
     * @var EntityRepository
     */
    private $orderTypeRepository;

    /**
     * @var EntityRepository
     */
    private $orderStatusRepository;

    /**
     * @var UserRepositoryInterface
     */
    private $userRepository;

    /**
     * Describes the fields, which are handled by this controller
     *
     * @var DoctrineFieldDescriptor[]
     */
    private $fieldDescriptors = array();

    /**
     * @var SessionInterface
     */
    protected $session;

    /**
     * @var GroupedItemsPriceCalculatorInterface
     */
    private $priceCalculator;

    /**
     * @var ProductManagerInterface
     */
    private $productManager;

    /**
     * @var OrderFactoryInterface
     */
    private $orderFactory;

    /**
     * @var AccountRepository
     */
    private $accountRepository;

    /**
     * @var OrderAddressManager
     */
    private $orderAddressManager;

    /**
     * @param ObjectManager $em
     * @param OrderRepository $orderRepository
     * @param UserRepositoryInterface $userRepository
     * @param AccountRepository $accountRepository
     * @param ItemManager $itemManager
     * @param EntityRepository $orderStatusRepository
     * @param EntityRepository $orderTypeRepository
     * @param SessionInterface $session
     * @param GroupedItemsPriceCalculatorInterface $priceCalculator
     * @param ProductManagerInterface $productManager
     * @param OrderFactoryInterface $orderFactory
     * @param OrderAddressManager $orderAddressManager
     */
    public function __construct(
        ObjectManager $em,
        OrderRepository $orderRepository,
        UserRepositoryInterface $userRepository,
        AccountRepository $accountRepository,
        ItemManager $itemManager,
        EntityRepository $orderStatusRepository,
        EntityRepository $orderTypeRepository,
        SessionInterface $session,
        GroupedItemsPriceCalculatorInterface $priceCalculator,
        ProductManagerInterface $productManager,
        OrderFactoryInterface $orderFactory,
        OrderAddressManager $orderAddressManager
    ) {
        $this->orderRepository = $orderRepository;
        $this->userRepository = $userRepository;
        $this->accountRepository = $accountRepository;
        $this->em = $em;
        $this->itemManager = $itemManager;
        $this->orderStatusRepository = $orderStatusRepository;
        $this->orderTypeRepository = $orderTypeRepository;
        $this->session = $session;
        $this->priceCalculator = $priceCalculator;
        $this->productManager = $productManager;
        $this->orderFactory = $orderFactory;
        $this->orderAddressManager = $orderAddressManager;
    }

    /**
     * Creates a new Order Entity
     *
     * @param array $data The data array, which will be used for setting the orders data
     * @param string $locale Locale
     * @param int $userId Id of the User, which is is saved as creator / changer
     * @param int|null $id If defined, the Order with the given ID will be updated
     * @param int|null $statusId if defined, the status will be set to the given value
     * @param bool $flush Defines if a flush should be performed
     *
     * @throws Exception\OrderNotFoundException
     * @throws Exception\OrderException
     *
     * @return null|Order|\Sulu\Bundle\Sales\OrderBundle\Entity\Order
     */
    public function save(
        array $data,
        $locale,
        $userId = null,
        $id = null,
        $statusId = null,
        $flush = true,
        $patch = true
    ) {
        $isNewOrder = !$id;

        if (!$isNewOrder) {
            $order = $this->findByIdAndLocale($id, $locale);

            if (!$order) {
                throw new OrderNotFoundException($id);
            }
        } else {
            $order = $this->orderFactory->createApiEntity($this->orderFactory->createEntity(), $locale);
            $this->checkRequiredData($data, $id === null);
        }

        $user = $userId ? $this->userRepository->findUserById($userId) : null;

        $order->setOrderNumber(
            $this->getPropertyBasedOnPatch($data, 'orderNumber', $order->getOrderNumber(), $patch)
        );
        $order->setCurrencyCode(
            $this->getPropertyBasedOnPatch($data, 'currencyCode', $order->getCurrencyCode(), $patch)
        );
        $order->setCostCentre(
            $this->getPropertyBasedOnPatch($data, 'costCentre', $order->getCostCentre(), $patch)
        );
        $order->setCommission(
            $this->getPropertyBasedOnPatch($data, 'commission', $order->getCommission(), $patch)
        );
        $order->setTaxfree(
            $this->getPropertyBasedOnPatch($data, 'taxfree', $order->getTaxfree(), $patch)
        );

        // set type of order (if set)
        $this->setOrderType($data, $order);

        $this->setDate(
            $data,
            'desiredDeliveryDate',
            $order->getDesiredDeliveryDate(),
            array($order, 'setDesiredDeliveryDate')
        );
        $this->setDate(
            $data,
            'orderDate',
            $order->getOrderDate(),
            array($order, 'setOrderDate')
        );

        $this->setTermsOfDelivery($data, $order, $patch);
        $this->setTermsOfPayment($data, $order, $patch);

        $account = $this->setCustomerAccount($data, $order, $patch);

        // set session - id
        $sessionId = $this->session->getId();
        $order->setSessionId($sessionId);

        // add contact
        $contact = $this->addContactRelation(
            $data,
            'customerContact',
            function ($contact) use ($order) {
                $order->setCustomerContact($contact);
            }
        );

        // add contact
        $this->addContactRelation(
            $data,
            'responsibleContact',
            function ($contact) use ($order) {
                $order->setResponsibleContact($contact);
            }
        );

        // create order (POST)
        if ($order->getId() == null) {
            $order->setCreated(new DateTime());
            $order->setCreator($user);
            $this->em->persist($order->getEntity());

            // set status to created if not defined
            if ($statusId === null) {
                $statusId = OrderStatus::STATUS_CREATED;
            }

            // create OrderAddress
            $deliveryAddress = new OrderAddress();
            $invoiceAddress = new OrderAddress();
            // persist entities
            $this->em->persist($deliveryAddress);
            $this->em->persist($invoiceAddress);
            // assign to order
            $order->setDeliveryAddress($deliveryAddress);
            $order->setInvoiceAddress($invoiceAddress);
        }

        // set order status
        if ($statusId !== null) {
            $this->convertStatus($order, $statusId);
        }

        // if not new and contact is not set, use old contact
        if (!$isNewOrder && !$contact) {
            $contact = $order->getEntity()->getCustomerContact();
        }
        $contactFullName = null;
        if ($contact) {
            $contactFullName = $contact->getFullName();
        }

        if (isset($data['invoiceAddress'])) {
            // set customer name to account if set, otherwise to contact
            $contactFullName = $this->getContactData($data['invoiceAddress'], $contact)['fullName'];

            // set OrderAddress data
            $this->orderAddressManager->setOrderAddress(
                $order->getEntity()->getInvoiceAddress(),
                $data['invoiceAddress'],
                $contact,
                $account
            );
        }
        if (isset($data['deliveryAddress'])) {
            $this->orderAddressManager->setOrderAddress(
                $order->getEntity()->getDeliveryAddress(),
                $data['deliveryAddress'],
                $contact,
                $account
            );
        }

        // set customer name
        $customerName = $account !== null ? $account->getName() : $contactFullName;
        if ($customerName) {
            $order->setCustomerName($customerName);
        }

        // handle items
        if (!$this->processItems($data, $order, $locale, $userId)) {
            throw new OrderException('Error while processing items');
        }

        $order->setChanged(new DateTime());
        $order->setChanger($user);

        $this->updateApiEntity($order, $locale);

        if ($flush) {
            $this->em->flush();
        }

        return $order;
    }

    /**
     * Function updates the api-entity, like price-calculations
     *
     * @param Order $apiOrder
     */
    public function updateApiEntity(Order $apiOrder, $locale)
    {
        $items = $apiOrder->getItems();

        // perform price calucaltion
        $prices = $supplierItems = null;
        $totalPrice = $this->priceCalculator->calculate($items, $prices, $supplierItems);

        if ($supplierItems) {
            $supplierItems = array_values($supplierItems);
            // update media api entities
            $this->createMediaForSupplierItems($supplierItems, $locale);
            // set grouped items
            $apiOrder->setSupplierItems($supplierItems);
        }

        $this->createMediaForItems($items, $locale);

        // check if any price in cart has changed
        $hasChangedPrices = false;
        foreach ($items as $item) {
            if ($item->getPriceChange()) {
                $hasChangedPrices = true;
                break;
            }
        }
        $apiOrder->setHasChangedPrices($hasChangedPrices);

        // set total price
        $apiOrder->setTotalNetPrice($totalPrice);
    }

    /**
     * Returns contact data as an array. Either by provided address or contact
     *
     * @param array $addressData
     * @param Contact $contact
     *
     * @throws MissingOrderAttributeException
     *
     * @return array
     */
    public function getContactData($addressData, $contact)
    {
        $result = array();
        // if account is set, take account's name
        if ($addressData && isset($addressData['firstName']) && isset($addressData['lastName'])) {
            $result['firstName'] = $addressData['firstName'];
            $result['lastName'] = $addressData['lastName'];
            $result['fullName'] = $result['firstName'] . ' ' . $result['lastName'];
            if (isset($addressData['title'])) {
                $result['title'] = $addressData['title'];
            }
            if (isset($addressData['salutation'])) {
                $result['salutation'] = $addressData['salutation'];
            }
        } else {
            if ($contact) {
                $result['firstName'] = $contact->getFirstName();
                $result['lastName'] = $contact->getLastName();
                $result['fullName'] = $contact->getFullName();
                $result['salutation'] = $contact->getFormOfAddress();
                if ($contact->getTitle() !== null) {
                    $result['title'] = $contact->getTitle()->getTitle();
                }
            } else {
                throw new MissingOrderAttributeException('firstName, lastName or contact');
            }
        }

        return $result;
    }

    /**
     * Deletes an order
     *
     * @param int $id
     *
     * @throws Exception\OrderNotFoundException
     */
    public function delete($id)
    {
        // TODO: move order to an archive instead of removing it from database
        $order = $this->orderRepository->findById($id);

        if (!$order) {
            throw new OrderNotFoundException($id);
        }

        $this->em->remove($order);
        $this->em->flush();
    }

    /**
     * Returns OrderType entity with given id
     *
     * @param int $typeId
     *
     * @throws EntityNotFoundException
     *
     * @return null|OrderType
     */
    public function getOrderTypeEntityById($typeId)
    {
        // get desired status
        $typeEntity = $this->orderTypeRepository->find($typeId);
        if (!$typeEntity) {
            throw new EntityNotFoundException($typeEntity, $typeId);
        }

        return $typeEntity;
    }

    /**
     * Converts the status of an order
     *
     * @param ApiOrderInterface|OrderInterface $order
     * @param int $statusId
     * @param bool $flush
     * @param bool $persist
     *
     * @throws \Sulu\Component\Rest\Exception\EntityNotFoundException
     */
    public function convertStatus($order, $statusId, $flush = false, $persist = true)
    {
        if ($order instanceof Order) {
            $order = $order->getEntity();
        }

        // get current status
        $currentStatus = $order->getStatus();
        if ($currentStatus) {
            if ($currentStatus instanceof \Massive\Bundle\Purchase\OrderBundle\Api\OrderStatus) {
                $currentStatus = $order->getStatus()->getEntity();
            }

            // if status has not changed, skip
            if ($currentStatus->getId() === $statusId) {
                return;
            }
        }

        // get desired status
        $statusEntity = $this->em
            ->getRepository(self::$orderStatusEntityName)
            ->find($statusId);
        if (!$statusEntity) {
            throw new EntityNotFoundException(self::$orderStatusEntityName, $statusId);
        }

        // ACTIVITY LOG
        $orderActivity = new OrderActivityLog();
        $orderActivity->setOrder($order);
        if ($currentStatus) {
            $orderActivity->setStatusFrom($currentStatus);
        }
        $orderActivity->setStatusTo($statusEntity);
        $orderActivity->setCreated(new \DateTime());
        if ($persist) {
            $this->em->persist($orderActivity);
        }

        // BITMASK
        $currentBitmaskStatus = $order->getBitmaskStatus();
        // if desired status already is in bitmask, remove current state
        // since this is a step back
        if ($currentBitmaskStatus && $currentBitmaskStatus & $statusEntity->getId()) {
            $order->setBitmaskStatus($currentBitmaskStatus & ~$currentStatus->getId());
        } else {
            // else increment bitmask status
            $order->setBitmaskStatus($currentBitmaskStatus | $statusEntity->getId());
        }

        // check if status has changed
        if ($statusId === OrderStatusEntity::STATUS_CREATED) {
            // TODO: re-edit - do some business logic
        }
        $order->setStatus($statusEntity);

        if ($flush === true) {
            $this->em->flush();
        }
    }

    /**
     * Finds a status by id
     *
     * @param $statusId
     *
     * @throws \Sulu\Component\Rest\Exception\EntityNotFoundException
     *
     * @return OrderStatus
     */
    public function findOrderStatusById($statusId)
    {
        try {
            return $this->em
                ->getRepository(self::$orderStatusEntityName)
                ->find($statusId);
        } catch (NoResultException $nre) {
            throw new EntityNotFoundException(self::$orderStatusEntityName, $statusId);
        }
    }

    /**
     * Find order entity by id
     *
     * @param $id
     *
     * @throws \Sulu\Component\Rest\Exception\EntityNotFoundException
     *
     * @return OrderInterface
     */
    public function findOrderEntityById($id)
    {
        try {
            return $this->em
                ->getRepository(static::$orderEntityName)
                ->find($id);
        } catch (NoResultException $nre) {
            throw new EntityNotFoundException(static::$orderEntityName, $id);
        }
    }

    /**
     * Find order for item with id
     *
     * @param int $id
     *
     * @throws \Sulu\Component\Rest\Exception\EntityNotFoundException
     *
     * @return OrderInterface
     */
    public function findOrderEntityForItemWithId($id, $returnMultipleResults = false)
    {
        try {
            return $this->em
                ->getRepository(static::$orderEntityName)
                ->findOrderForItemWithId($id, $returnMultipleResults);
        } catch (NoResultException $nre) {
            throw new EntityNotFoundException(static::$itemEntity, $id);
        }
    }

    /**
     * @param string $locale
     *
     * @return \Sulu\Component\Rest\ListBuilder\Doctrine\FieldDescriptor\DoctrineFieldDescriptor[]
     */
    public function getFieldDescriptors($locale)
    {
        if ($locale !== $this->currentLocale) {
            $this->initializeFieldDescriptors($locale);
        }

        return $this->fieldDescriptors;
    }

    /**
     * returns a specific field descriptor by key
     *
     * @param string $key
     *
     * @return DoctrineFieldDescriptor
     */
    public function getFieldDescriptor($key)
    {
        return $this->fieldDescriptors[$key];
    }

    /**
     * Finds an order by id and locale
     *
     * @param int $id
     * @param string $locale
     *
     * @return null|Order
     */
    public function findByIdAndLocale($id, $locale)
    {
        $order = $this->orderRepository->findByIdAndLocale($id, $locale);

        if ($order) {
            $order = $this->orderFactory->createApiEntity($order, $locale);
            $this->updateApiEntity($order, $locale);

            return $order;
        } else {
            return null;
        }
    }

    /**
     * @param $locale
     * @param array $filter
     *
     * @return OrderInterface[]
     */
    public function findAllByLocale($locale, $filter = array())
    {
        if (empty($filter)) {
            $order = $this->orderRepository->findAllByLocale($locale);
        } else {
            $order = $this->orderRepository->findByLocaleAndFilter($locale, $filter);
        }

        if ($order) {
            array_walk(
                $order,
                function (&$order) use ($locale) {
                    $order = $this->orderFactory->createApiEntity($order, $locale);
                    $this->updateApiEntity($order, $locale);
                }
            );
        }

        return $order;
    }

    /**
     * @param array $itemData
     * @param string $locale
     * @param int $userId
     * @param Order $order
     *
     * @return mixed
     */
    public function addItem($itemData, $locale, $userId, $order)
    {
        $item = $this->itemManager->save($itemData, $locale, $userId);

        return $order->addItem($item->getEntity());
    }

    /**
     * @param ItemInterface $item
     * @param array $itemData
     * @param string $locale
     * @param int $userId
     *
     * @return null|\Sulu\Bundle\Sales\CoreBundle\Api\Item
     */
    public function updateItem(ItemInterface $item, $itemData, $locale, $userId)
    {
        return $this->itemManager->save($itemData, $locale, $userId, $item);
    }

    /**
     * @param ItemInterface $item
     * @param OrderInterface $order
     * @param bool $deleteEntity
     */
    public function removeItem(ItemInterface $item, OrderInterface $order, $deleteEntity = true)
    {
        // remove from order
        $order->removeItem($item);
        if ($deleteEntity) {
            // delete item
            $this->em->remove($item);
        }
    }

    /**
     * Get order item by id and checks if item belongs to the order
     *
     * @param int $itemId
     * @param OrderInterface $order
     * @param bool $hasMultiple Returns if multiple orders exist for the item
     *
     * @throws EntityNotFoundException
     * @throws ItemNotFoundException
     * @throws OrderException
     *
     * @return null|\Sulu\Bundle\Sales\CoreBundle\Api\Item
     */
    public function getOrderItemById($itemId, OrderInterface $order, &$hasMultiple = false)
    {
        $item = $this->itemManager->findEntityById($itemId);
        if (!$item) {
            throw new ItemNotFoundException($itemId);
        }

        $match = false;
        $orders = $this->findOrderEntityForItemWithId($itemId, true);
        if ($orders) {
            if (count($orders > 1)) {
                $hasMultiple = true;
            }
            foreach ($orders as $itemOrders) {
                if ($order === $itemOrders) {
                    $match = true;
                }
            }
        }
        if (!$match) {
            throw new OrderException('User not owner of order');
        }

        return $item;
    }

    /**
     * Creates correct media-api for supplier-items array
     *
     * @param array $items
     * @param string $locale
     */
    protected function createMediaForSupplierItems($items, $locale)
    {
        foreach ($items as $item) {
            if (isset($item['items']) && count($item['items'] > 0)) {
                $this->createMediaForItems($item['items'], $locale);
            }
        }
    }

    /**
     * Creates correct media-api for items array
     *
     * @param array $items
     * @param string $locale
     */
    protected function createMediaForItems($items, $locale)
    {
        foreach ($items as $item) {
            $product = $item->getProduct();
            if ($product) {
                $this->productManager->createProductMedia($product, $locale);
                $item->setProduct($product);
            }
        }
    }

    /**
     * Returns the entry from the data with the given key, or the given default value, if the key does not exist
     *
     * @param array $data
     * @param string $key
     * @param string $default
     *
     * @return mixed
     */
    protected function getProperty(array $data, $key, $default = null)
    {
        return array_key_exists($key, $data) ? $data[$key] : $default;
    }

    /**
     * Gets Property of data array. If PUT set to null
     *
     * @param array $data
     * @param string $key
     * @param mixed $default
     * @param bool $patch
     *
     * @return mixed
     */
    protected function getPropertyBasedOnPatch($data, $key, $default, $patch)
    {
        if (!$patch) {
            $default = null;
        }
        return $this->getProperty($data, $key, $default);
    }

    /**
     * Check if necessary data is set
     *
     * @param array $data
     * @param bool $isNew
     */
    private function checkRequiredData($data, $isNew)
    {
        $this->checkDataSet($data, 'deliveryAddress', $isNew);
        $this->checkDataSet($data, 'invoiceAddress', $isNew);
    }

    /**
     * Sets a date if it's set in data
     *
     * @param array $data
     * @param string $key
     * @param DateTime $currentDate
     * @param callable $setCallback
     */
    private function setDate($data, $key, $currentDate, callable $setCallback)
    {
        if (($date = $this->getProperty($data, $key, $currentDate)) !== null) {
            if (is_string($date)) {
                $date = new DateTime($data[$key]);
            }
            call_user_func($setCallback, $date);
        }
    }

    /**
     * Sets OrderType on an order
     *
     * @param array $data
     * @param Order $order
     *
     * @throws EntityNotFoundException
     * @throws OrderException
     */
    private function setOrderType($data, $order)
    {
        // get OrderType
        $type = $this->getProperty($data, 'type', null);
        if (!is_null($type)) {
            if (is_array($type) && isset($type['id'])) {
                // if provided as array
                $typeId = $type['id'];
            } elseif (is_numeric($type)) {
                // if is numeric
                $typeId = $type;
            } else {
                throw new OrderException('No typeid given');
            }
        } else {
            // default type is manual
            $typeId = OrderType::MANUAL;
        }
        // get entity
        $orderType = $this->getOrderTypeEntityById($typeId);
        if (!$orderType) {
            throw new EntityNotFoundException(static::$orderTypeEntityName, $typeId);
        }

        // set order type
        $order->setType($orderType);
    }

    /**
     * Initializes field descriptors
     *
     * @param string $locale
     */
    private function initializeFieldDescriptors($locale)
    {
        $this->fieldDescriptors['id'] = new DoctrineFieldDescriptor(
            'id',
            'id',
            static::$orderEntityName,
            'public.id',
            array(),
            true
        );
        $this->fieldDescriptors['number'] = new DoctrineFieldDescriptor(
            'number',
            'number',
            static::$orderEntityName,
            'salesorder.orders.number',
            array(),
            false,
            true
        );

        // TODO: get customer from order-address

        $contactJoin = array(
            static::$orderAddressEntityName => new DoctrineJoinDescriptor(
                static::$orderAddressEntityName,
                static::$orderEntityName . '.invoiceAddress'
            )
        );

        $this->fieldDescriptors['account'] = new DoctrineConcatenationFieldDescriptor(
            array(
                new DoctrineFieldDescriptor(
                    'accountName',
                    'account',
                    static::$orderAddressEntityName,
                    'contact.contacts.contact',
                    $contactJoin
                )
            ),
            'account',
            'salesorder.orders.account',
            ' ',
            false,
            false,
            '',
            '',
            '160px'
        );

        $this->fieldDescriptors['contact'] = new DoctrineConcatenationFieldDescriptor(
            array(
                new DoctrineFieldDescriptor(
                    'firstName',
                    'contact',
                    static::$orderAddressEntityName,
                    'contact.contacts.contact',
                    $contactJoin
                ),
                new DoctrineFieldDescriptor(
                    'lastName',
                    'contact',
                    static::$orderAddressEntityName,
                    'contact.contacts.contact',
                    $contactJoin
                )
            ),
            'contact',
            'salesorder.orders.contact',
            ' ',
            false,
            false,
            '',
            '',
            '160px',
            false
        );

        $this->fieldDescriptors['status'] = new DoctrineFieldDescriptor(
            'name',
            'status',
            static::$orderStatusTranslationEntityName,
            'salesorder.orders.status',
            array(
                static::$orderStatusEntityName => new DoctrineJoinDescriptor(
                    static::$orderStatusEntityName,
                    static::$orderEntityName . '.status'
                ),
                static::$orderStatusTranslationEntityName => new DoctrineJoinDescriptor(
                    static::$orderStatusTranslationEntityName,
                    static::$orderStatusEntityName . '.translations',
                    static::$orderStatusTranslationEntityName . ".locale = '" . $locale . "'"
                )
            )
        );

        $this->fieldDescriptors['type'] = new DoctrineFieldDescriptor(
            'name',
            'type',
            static::$orderTypeTranslationEntityName,
            'salesorder.orders.type',
            array(
                static::$orderTypeEntityName => new DoctrineJoinDescriptor(
                    static::$orderTypeEntityName,
                    static::$orderEntityName . '.type'
                ),
                static::$orderTypeTranslationEntityName => new DoctrineJoinDescriptor(
                    static::$orderTypeTranslationEntityName,
                    static::$orderTypeEntityName . '.translations',
                    static::$orderTypeTranslationEntityName . ".locale = '" . $locale . "'"
                )
            )
        );
    }


    /**
     * Checks data for attributes
     *
     * @param array $data
     * @param string $key
     * @param bool $isNew
     *
     * @throws Exception\MissingOrderAttributeException
     *
     * @return bool
     */
    private function checkDataSet(array $data, $key, $isNew)
    {
        $keyExists = array_key_exists($key, $data);

        if (($isNew && !($keyExists && $data[$key] !== null)) || (!$keyExists || $data[$key] === null)) {
            throw new MissingOrderAttributeException($key);
        }

        return $keyExists;
    }

    /**
     * Checks if data is set
     *
     * @param string $key
     * @param array $data
     *
     * @return bool
     */
    private function checkIfSet($key, $data)
    {
        $keyExists = array_key_exists($key, $data);

        return $keyExists && $data[$key] !== null && $data[$key] !== '';
    }

    /**
     * Searches for contact in specified data and calls callback function
     *
     * @param array $data
     * @param string $key
     * @param callable $addCallback
     *
     * @throws Exception\MissingOrderAttributeException
     * @throws Exception\OrderDependencyNotFoundException
     *
     * @return Contact|null
     */
    private function addContactRelation(array $data, $key, $addCallback)
    {
        $contact = null;
        if (array_key_exists($key, $data) &&
            is_array($data[$key]) &&
            array_key_exists('id', $data[$key])
        ) {
            /** @var Contact $contact */
            $contactId = $data[$key]['id'];
            $contact = $this->em->getRepository(static::$contactEntityName)->find($contactId);
            if (!$contact) {
                throw new OrderDependencyNotFoundException(static::$contactEntityName, $contactId);
            }
            $addCallback($contact);
        }

        return $contact;
    }

    /**
     * @param array $data
     * @param Order $order
     * @param bool $patch
     *
     * @throws MissingOrderAttributeException
     * @throws OrderDependencyNotFoundException
     *
     * @return null|object
     */
    private function setTermsOfDelivery($data, Order $order, $patch = false)
    {
        $terms = null;
        // terms of delivery
        $termsOfDeliveryData = $this->getProperty($data, 'termsOfDelivery');
        $termsOfDeliveryContentData = $this->getProperty($data, 'termsOfDeliveryContent');
        if ($termsOfDeliveryData) {
            if (!array_key_exists('id', $termsOfDeliveryData)) {
                throw new MissingOrderAttributeException('termsOfDelivery.id');
            }
            // TODO: inject repository class
            $terms = $this->em->getRepository(static::$termsOfDeliveryEntityName)->find($termsOfDeliveryData['id']);
            if (!$terms) {
                throw new OrderDependencyNotFoundException(
                    static::$termsOfDeliveryEntityName,
                    $termsOfDeliveryData['id']
                );
            }
            $order->setTermsOfDelivery($terms);
            $order->setTermsOfDeliveryContent($terms->getTerms());
        } elseif (!$patch) {
            $order->setTermsOfDelivery(null);
            $order->setTermsOfDeliveryContent(null);
        }
        // set content data
        if ($termsOfDeliveryContentData) {
            $order->setTermsOfDeliveryContent($termsOfDeliveryContentData);
        }

        return $terms;
    }

    /**
     * @param array $data
     * @param Order $order
     * @param bool $patch
     *
     * @throws MissingOrderAttributeException
     * @throws OrderDependencyNotFoundException
     *
     * @return null|object
     */
    private function setTermsOfPayment($data, Order $order, $patch = false)
    {
        $terms = null;
        // terms of delivery
        $termsOfPaymentData = $this->getProperty($data, 'termsOfPayment');
        $termsOfPaymentContentData = $this->getProperty($data, 'termsOfPaymentContent');
        if ($termsOfPaymentData) {
            if (!array_key_exists('id', $termsOfPaymentData)) {
                throw new MissingOrderAttributeException('termsOfPayment.id');
            }
            // TODO: inject repository class
            $terms = $this->em->getRepository(static::$termsOfPaymentEntityName)->find($termsOfPaymentData['id']);
            if (!$terms) {
                throw new OrderDependencyNotFoundException(
                    static::$termsOfPaymentEntityName,
                    $termsOfPaymentData['id']
                );
            }
            $order->setTermsOfPayment($terms);
            $order->setTermsOfPaymentContent($terms->getTerms());
        } elseif (!$patch) {
            $order->setTermsOfPayment(null);
            $order->setTermsOfPaymentContent(null);
        }
        // set content data
        if ($termsOfPaymentContentData) {
            $order->setTermsOfPaymentContent($termsOfPaymentContentData);
        }

        return $terms;
    }

    /**
     * Sets the customer account of an order
     *
     * @param array $data
     * @param Order $order
     * @param bool $patch
     *
     * @return null|object
     * @throws MissingOrderAttributeException
     * @throws OrderDependencyNotFoundException
     */
    private function setCustomerAccount($data, Order $order, $patch = false)
    {
        $accountData = $this->getProperty($data, 'customerAccount');
        if ($accountData) {
            if (!array_key_exists('id', $accountData)) {
                throw new MissingOrderAttributeException('account.id');
            }
            $account = $this->accountRepository->find($accountData['id']);
            if (!$account) {
                throw new OrderDependencyNotFoundException('Account', $accountData['id']);
            }
            $order->setCustomerAccount($account);

            return $account;
        } elseif (!$patch) {
            $order->setCustomerAccount(null);
        }

        return null;
    }

    /**
     * Processes items defined in an order and creates item entities
     *
     * @param array $data
     * @param Order $order
     * @param string $locale
     * @param int $userId
     *
     * @throws Exception\OrderException
     *
     * @return bool
     */
    private function processItems($data, Order $order, $locale, $userId = null)
    {
        $result = true;
        try {
            if ($this->checkIfSet('items', $data)) {
                // items has to be an array
                if (!is_array($data['items'])) {
                    throw new MissingOrderAttributeException('items array');
                }

                $items = $data['items'];

                $get = function ($item) {
                    return $item->getId();
                };

                $delete = function ($item) use ($order) {
                    $this->removeItem($item->getEntity(), $order->getEntity());
                };

                $update = function ($item, $matchedEntry) use ($locale, $userId, $order) {
                    $item = $item->getEntity();
                    $itemEntity = $this->updateItem($item, $matchedEntry, $locale, $userId);

                    return $itemEntity ? true : false;
                };

                $add = function ($itemData) use ($locale, $userId, $order) {
                    return $this->addItem($itemData, $locale, $userId, $order);
                };

                $result = $this->processSubEntities(
                    $order->getItems(),
                    $items,
                    $get,
                    $add,
                    $update,
                    $delete
                );
            }
        } catch (\Exception $e) {
            throw new OrderException('Error while creating items: ' . $e->getMessage());
        }

        return $result;
    }
}
