<?php
/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\Sales\ShippingBundle\Shipping;

use DateTime;
use Doctrine\Common\Persistence\ObjectManager;
use Sulu\Bundle\Sales\CoreBundle\Entity\Item;
use Sulu\Bundle\Sales\ShippingBundle\Entity\ShippingActivityLog;
use Sulu\Bundle\Sales\ShippingBundle\Entity\ShippingRepository;
use Sulu\Component\Rest\Exception\EntityNotFoundException;
use Sulu\Component\Rest\ListBuilder\Doctrine\FieldDescriptor\DoctrineConcatenationFieldDescriptor;
use Sulu\Component\Rest\ListBuilder\Doctrine\FieldDescriptor\DoctrineFieldDescriptor;
use Sulu\Component\Rest\ListBuilder\Doctrine\FieldDescriptor\DoctrineJoinDescriptor;
use Sulu\Component\Rest\RestHelperInterface;
use Sulu\Component\Security\Authentication\UserRepositoryInterface;
use Sulu\Bundle\Sales\CoreBundle\Item\ItemManager;
use Sulu\Bundle\Sales\OrderBundle\Entity\OrderAddress;
use Sulu\Bundle\Sales\ShippingBundle\Shipping\Exception\MissingShippingAttributeException;
use Sulu\Bundle\Sales\ShippingBundle\Shipping\Exception\ShippingDependencyNotFoundException;
use Sulu\Bundle\Sales\ShippingBundle\Shipping\Exception\ShippingException;
use Sulu\Bundle\Sales\ShippingBundle\Shipping\Exception\ShippingNotFoundException;
use Sulu\Bundle\Sales\ShippingBundle\Entity\Shipping as ShippingEntity;
use Sulu\Bundle\Sales\ShippingBundle\Api\ShippingItem;
use Sulu\Bundle\Sales\ShippingBundle\Entity\ShippingItem as ShippingItemEntity;
use Sulu\Bundle\Sales\ShippingBundle\Entity\ShippingStatus as ShippingStatusEntity;
use Sulu\Bundle\Sales\ShippingBundle\Api\Shipping;

class ShippingManager
{
    protected static $shippingEntityName = 'SuluSalesShippingBundle:Shipping';
    protected static $orderEntityName = 'SuluSalesOrderBundle:Order';
    protected static $orderAddressEntityName = 'SuluSalesOrderBundle:OrderAddress';
    protected static $shippingStatusEntityName = 'SuluSalesShippingBundle:ShippingStatus';
    protected static $shippingStatusTranslationEntityName = 'SuluSalesShippingBundle:ShippingStatusTranslation';

    protected static $itemEntityName = 'SuluSalesCoreBundle:Item';
    protected static $termsOfDeliveryEntityName = 'SuluContactBundle:TermsOfDelivery';

    /**
     * currently used locale
     */
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
     * @var RestHelperInterface
     */
    private $restHelper;

    /**
     * @var UserRepositoryInterface
     */
    private $userRepository;

    /**
     * Describes the fields, which are handled by this controller
     * @var DoctrineFieldDescriptor[]
     */
    private $fieldDescriptors = array();

    /**
     * Describes the order fields, which are handled by this controller
     * @var DoctrineFieldDescriptor[]
     */
    private $orderFieldDescriptors = array();

    /** constructor */
    public function __construct(
        ObjectManager $em,
        UserRepositoryInterface $userRepository,
        ItemManager $itemManager,
        RestHelperInterface $restHelper
    )
    {
        $this->em = $em;
        $this->userRepository = $userRepository;
        $this->itemManager = $itemManager;
        $this->restHelper = $restHelper;
    }

    /**
     * saves a shipping
     *
     * @param array $data array of data to be set
     * @param $locale locale in which the data should be set
     * @param $userId
     * @param null|int $id
     * @param null|int $statusId
     * @param bool $flush
     * @throws Exception\ShippingNotFoundException
     * @throws Exception\ShippingException
     * @return null|Shipping
     */
    public function save(array $data,
                         $locale,
                         $userId,
                         $id = null,
                         $statusId = null,
                         $flush = true)
    {
        if ($id) {
            $shipping = $this->findByIdAndLocale($id, $locale);

            if (!$shipping) {
                throw new ShippingNotFoundException($id);
            }
        } else {
            $shipping = new Shipping(new ShippingEntity(), $locale);
        }

        // check for data
        $this->checkRequiredData($data, $id === null);

        $user = $this->userRepository->findUserById($userId);

        $shipping->setShippingNumber($this->getProperty($data, 'shippingNumber', $shipping->getShippingNumber()));
        $shipping->setWidth($this->getProperty($data, 'width', $shipping->getWidth()));
        $shipping->setHeight($this->getProperty($data, 'height', $shipping->getHeight()));
        $shipping->setLength($this->getProperty($data, 'length', $shipping->getLength()));
        $shipping->setWeight($this->getProperty($data, 'weight', $shipping->getWeight()));
        $shipping->setCommission($this->getProperty($data, 'commission', $shipping->getCommission()));

        $shipping->setTrackingId($this->getProperty($data, 'trackingId', $shipping->getTrackingId()));
        $shipping->setTrackingUrl($this->getProperty($data, 'trackingUrl', $shipping->getTrackingUrl()));

        $this->setShippingOrder($data, $shipping);

        // TODO: check if empty string overrides note
        $shipping->setNote($this->getProperty($data, 'note', $shipping->getNote()));

        // set expected delivery date
        if (($expectedDeliveryDate = $this->getProperty($data, 'expectedDeliveryDate', $shipping->getExpectedDeliveryDate())) !== null) {
            if (is_string($expectedDeliveryDate)) {
                $expectedDeliveryDate = new DateTime($data['expectedDeliveryDate']);
            }
            $shipping->setExpectedDeliveryDate($expectedDeliveryDate);
        }

        // set terms of delivery
        $shipping->setTermsOfDeliveryContent($this->getProperty($data, 'termsOfDeliveryContent', null));
        // set terms of payment
        $shipping->setTermsOfPaymentContent($this->getProperty($data, 'termsOfPaymentContent', null));

        // create shipping (POST)
        if ($shipping->getId() == null) {
            $shipping->setCreated(new DateTime());
            $shipping->setCreator($user);
            $this->em->persist($shipping->getEntity());

            // set status to created if not defined
            if ($statusId === null) {
                $statusId = ShippingStatusEntity::STATUS_CREATED;
            }

            // create OrderAddress
            $deliveryAddress = new OrderAddress();
            // persist entities
            $this->em->persist($deliveryAddress);
            // assign
            $shipping->setDeliveryAddress($deliveryAddress);
        }
        // set order status
        if ($statusId !== null) {
            $this->convertStatus($shipping, $statusId);
        }

        // set order address
        $deliveryAddress = $shipping->getDeliveryAddress();
        if (($addressData = $this->getProperty($data, 'deliveryAddress'))) {
            $deliveryAddress->setStreet($this->getProperty($addressData, 'street', $deliveryAddress->getStreet()));
            $deliveryAddress->setAddition($this->getProperty($addressData, 'addition', $deliveryAddress->getAddition()));
            $deliveryAddress->setNumber($this->getProperty($addressData, 'number', $deliveryAddress->getNumber()));
            $deliveryAddress->setCity($this->getProperty($addressData, 'city', $deliveryAddress->getCity()));
            $deliveryAddress->setZip($this->getProperty($addressData, 'zip', $deliveryAddress->getZip()));
            $deliveryAddress->setState($this->getProperty($addressData, 'state', $deliveryAddress->getState()));
            $deliveryAddress->setCountry($this->getProperty($addressData, 'country', $deliveryAddress->getCountry()));
            $deliveryAddress->setPostboxNumber($this->getProperty($addressData, 'boxNumber', $deliveryAddress->getPostboxNumber()));
            $deliveryAddress->setPostboxCity($this->getProperty($addressData, 'boxCity', $deliveryAddress->getPostboxCity()));
            $deliveryAddress->setPostboxPostcode($this->getProperty($addressData, 'boxZip', $deliveryAddress->getPostboxPostcode()));

            $deliveryAddress->setTitle($this->getProperty($addressData, 'title', $deliveryAddress->getTitle()));
            $deliveryAddress->setFirstName($this->getProperty($addressData, 'firstName', $deliveryAddress->getFirstName()));
            $deliveryAddress->setLastName($this->getProperty($addressData, 'lastName', $deliveryAddress->getLastName()));
            $deliveryAddress->setAccountName($this->getProperty($addressData, 'accountName', $deliveryAddress->getAccountName()));
            $deliveryAddress->setUid($this->getProperty($addressData, 'uid', $deliveryAddress->getUid()));
            $deliveryAddress->setPhone($this->getProperty($addressData, 'phone', $deliveryAddress->getPhone()));
            $deliveryAddress->setPhoneMobile($this->getProperty($addressData, 'phoneMobile', $deliveryAddress->getPhoneMobile()));
        }

        // handle items
        if (!$this->processItems($data, $shipping, $locale, $userId)) {
            throw new ShippingException('Error while processing items');
        }
        $shipping->setChanged(new DateTime());
        $shipping->setChanger($user);

        if ($flush) {
            $this->em->flush();
        }

        return $shipping;
    }

    /**
     * delete a shipping
     * @param $id
     * @throws Exception\ShippingNotFoundException
     */
    public function delete($id)
    {
        // TODO: move shipping to an archive instead of remove it from database
        $shipping = $this->getShippingRepository()->findById($id);

        if (!$shipping) {
            throw new ShippingNotFoundException($id);
        }

        $this->em->remove($shipping);
        $this->em->flush();
    }

    /**
     * @param Shipping $shipping
     * @param $statusId
     * @param bool $flush
     * @throws \Sulu\Component\Rest\Exception\EntityNotFoundException
     */
    public function convertStatus(Shipping $shipping, $statusId, $flush = false)
    {
        // get current status
        $currentStatus = null;
        if ($shipping->getStatus()) {
            $currentStatus = $shipping->getStatus()->getEntity();

            // if status has not changed, skip
            if ($currentStatus->getId() === $statusId) {
                return;
            }
        }

        // get desired status
        $statusEntity = $this->em
            ->getRepository(self::$shippingStatusEntityName)
            ->find($statusId);
        if (!$statusEntity) {
            throw new EntityNotFoundException($statusEntity, $statusEntity);
        }

        // ACTIVITY LOG
        $shippingActivity = new ShippingActivityLog();
        $shippingActivity->setShipping($shipping->getEntity());
        if ($currentStatus) {
            $shippingActivity->setStatusFrom($currentStatus);
        }
        $shippingActivity->setStatusTo($statusEntity);
        $shippingActivity->setCreated(new \DateTime());
        $this->em->persist($shippingActivity);

        // BITMASK
        $currentBitmaskStatus = $shipping->getBitmaskStatus();
        // if desired status already is in bitmask, remove current state
        // since this is a step back
        if ($currentBitmaskStatus && $currentBitmaskStatus & $statusEntity->getId()) {
            $shipping->setBitmaskStatus($currentBitmaskStatus & ~$currentStatus->getId());
        } else {
            // else increment bitmask status
            $shipping->setBitmaskStatus($currentBitmaskStatus | $statusEntity->getId());
        }

        // check if status has changed
        if ($statusId === ShippingStatusEntity::STATUS_DELIVERY_NOTE) {
            // TODO: emit event
            $this->convertItemStatus($shipping, $statusId);
        }
        $shipping->setStatus($statusEntity);

        if ($flush === true) {
            $this->em->flush();
        }
    }

    // TODO: this conversion isn't complete yet, there are still a lot of edge
    // cases to go through
    /**
     * converts the status of an item
     * @param Shipping $shipping
     * @param $shippingStatusId
     */
    private function convertItemStatus(Shipping $shipping, $shippingStatusId)
    {
        foreach ($shipping->getItems() as $shippingItem) {
            // get item
            $item = $shippingItem->getItem();

            // set item status based on current shipping status
            switch ($shippingStatusId) {
                // created
                case ShippingStatusEntity::STATUS_CREATED:
                    // TODO: REMOVE PREVIOUS STATE
                    $this->itemManager->addStatus($item, Item::STATUS_CREATED);
                    break;
                // delivery note
                case ShippingStatusEntity::STATUS_DELIVERY_NOTE:
                    if ($this->isPartiallyItem($shippingItem, true)) {
                        $itemStatus = Item::STATUS_SHIPPING_NOTE_PARTIALLY;
                    } else {
                        $itemStatus = Item::STATUS_SHIPPING_NOTE;

                    }
                    $this->itemManager->addStatus($item, $itemStatus);
                    break;
                // shipped
                case ShippingStatusEntity::STATUS_SHIPPED:
                    if ($this->isPartiallyItem($shippingItem, false)) {
                        $itemStatus = Item::STATUS_SHIPPED_PARTIALLY;
                    } else {
                        $itemStatus = Item::STATUS_SHIPPED;

                    }
                    $this->itemManager->addStatus($item, $itemStatus);
                    break;
                case ShippingStatusEntity::STATUS_CANCELED:
                    // TODO: check  if still fully shipped
                    $this->itemManager->removeStatus($item, Item::STATUS_SHIPPED);
                    // TODO: check if partially shipped
                    break;
            }
        }
    }

    /**
     * checks if an item is fully shipped or still open
     * @param $shippingItem
     * @param bool $includeDeliveryNoteStatus
     * @return bool
     */
    private function isPartiallyItem($shippingItem, $includeDeliveryNoteStatus = false)
    {
        $item = $shippingItem->getItem();
        $sumShipped = $this->getSumOfShippedItemsByItemId($item->getId(), $includeDeliveryNoteStatus);
        $orderItem = $shippingItem->getShipping()->getOrder()->getItem($item->getId());
        // if all items are shipped (exclude current item)
        if ($sumShipped == $orderItem->getQuantity() - $shippingItem->getQuantity()) {
            return false;
        }
        return true;
    }

    /**
     * @param $locale
     * @param $context
     * @return \Sulu\Component\Rest\ListBuilder\Doctrine\FieldDescriptor\DoctrineFieldDescriptor[]
     */
    public function getFieldDescriptors($locale, $context = null)
    {
        if ($locale !== $this->currentLocale) {
            $this->initializeFieldDescriptors($locale);
        }
        $descriptors = $this->fieldDescriptors;

        // do not show order number when in order context
        if ($context === 'order') {
            unset($descriptors['orderNumber']);
        }

        return $descriptors;
    }

    /**
     * returns a specific field descriptor by key
     * @param $key
     * @throws Exception\ShippingException
     * @return DoctrineFieldDescriptor
     */
    public function getFieldDescriptor($key)
    {
        if (array_key_exists($key, $this->fieldDescriptors)) {
            return $this->fieldDescriptors[$key];
        } else if (array_key_exists($key, $this->orderFieldDescriptors)) {
            return $this->orderFieldDescriptors[$key];
        } else {
            throw new ShippingException('field descriptor with key ' . $key . ' could not be found');
        }
    }

    /**
     * Finds a shipping by id and locale
     * @param $id
     * @param $locale
     * @return null|Shipping
     */
    public function findByIdAndLocale($id, $locale)
    {
        $shipping = $this->getShippingRepository()->findByIdAndLocale($id, $locale);

        if ($shipping) {
            $apiShippings = new Shipping($shipping, $locale);

            // set currently shipped items (sum of all shippings for that item)
            $shippingCounts = $this->getSumOfShippedItemsByOrderId($shipping->getOrder()->getId());
            foreach ($apiShippings->getItems() as $apiItem) {
                $itemId = $apiItem->getEntity()->getItem()->getId();
                $sum = 0;
                if (array_key_exists($itemId, $shippingCounts)) {
                    $sum = $shippingCounts[$itemId];
                }
                $apiItem->setShippedItems($sum);
            }
            return $apiShippings;
        } else {
            return null;
        }
    }

    public function getSumOfShippedItemsByOrderId($orderId, $includeStatusDeliveryNote = true)
    {
        $result = array();
        $sums = $this->getShippingRepository()->getSumOfShippedItemsByOrderId($orderId, $includeStatusDeliveryNote);
        foreach ($sums as $sum) {
            $result[$sum['items_id']] = intval($sum['shipped']);
        }
        return $result;
    }

    public function getSumOfShippedItemsByItemId($itemId, $includeStatusDeliveryNote = false)
    {
        return $this->getShippingRepository()->getSumOfShippedItemsByItemId($itemId, $includeStatusDeliveryNote);
    }

    /**
     * Returns shippings by order id
     * @param $orderId
     * @param $locale
     * @return array
     */
    public function findByOrderId($orderId, $locale)
    {
        $result = array();
        $items = $this->getShippingRepository()->findByOrderId($orderId, $locale);
        foreach ($items as $item) {
            $result[] = new Shipping($item, $locale);
        }
        return $result;
    }

    /**
     * returns shipping entities by order id
     *
     * @param $orderId
     * @return array|null
     */
    public function findEntitiesByOrderId($orderId)
    {
        return $this->getShippingRepository()->findByOrderId($orderId);
    }

    /**
     * returns number of shippings by order id
     * @param $orderId
     * @param array $statusIds
     * @param string $comparator
     * @return int|mixed
     */
    public function countByOrderId($orderId, $statusIds = array(), $comparator = "=")
    {
        return $this->getShippingRepository()->countByOrderId($orderId, $statusIds, $comparator);
    }

    /**
     * @param $locale
     * @param array $filter
     * @return mixed
     */
    public function findAllByLocale($locale, $filter = array())
    {
        if (empty($filter)) {
            $shipping = $this->getShippingRepository()->findAllByLocale($locale);
        } else {
            $shipping = $this->getShippingRepository()->findByLocaleAndFilter($locale, $filter);
        }

        array_walk(
            $shipping,
            function (&$shipping) use ($locale){
                $shipping = new Shipping($shipping, $locale);
            }
        );

        return $shipping;
    }

    /**
     * initializes field descriptors
     */
    private function initializeFieldDescriptors($locale)
    {
        $this->initializeOrderFieldDescriptors($locale);

        $this->fieldDescriptors['id'] = new DoctrineFieldDescriptor('id', 'id', self::$shippingEntityName, 'public.id', array(), true);
        $this->fieldDescriptors['number'] = new DoctrineFieldDescriptor('number', 'number', self::$shippingEntityName, 'salesshipping.shippings.number', array(), false, true);

        $contactJoin = array(
            self::$orderAddressEntityName => new DoctrineJoinDescriptor(
                    self::$orderAddressEntityName,
                    self::$shippingEntityName . '.deliveryAddress'
                )
        );

        $this->fieldDescriptors['account'] = new DoctrineConcatenationFieldDescriptor(
            array(
                new DoctrineFieldDescriptor(
                    'accountName',
                    'account',
                    self::$orderAddressEntityName,
                    'contact.contacts.contact',
                    $contactJoin
                )
            ),
            'account',
            'salescore.account',
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
                    self::$orderAddressEntityName,
                    'contact.contacts.contact',
                    $contactJoin
                ),
                new DoctrineFieldDescriptor(
                    'lastName',
                    'contact',
                    self::$orderAddressEntityName,
                    'contact.contacts.contact',
                    $contactJoin
                )
            ),
            'contact',
            'salescore.contact',
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
            self::$shippingStatusTranslationEntityName,
            'salescore.status',
            array(
                self::$shippingStatusEntityName => new DoctrineJoinDescriptor(
                        self::$shippingStatusEntityName,
                        self::$shippingEntityName . '.status'
                    ),
                self::$shippingStatusTranslationEntityName => new DoctrineJoinDescriptor(
                        self::$shippingStatusTranslationEntityName,
                        self::$shippingStatusEntityName . '.translations',
                        self::$shippingStatusTranslationEntityName . ".locale = '" . $locale . "'"
                    )
            )
        );

        $this->fieldDescriptors['orderNumber'] = $this->orderFieldDescriptors['orderNumber'];

    }

    private function initializeOrderFieldDescriptors()
    {
        $orderJoin = array(
            self::$orderEntityName => new DoctrineJoinDescriptor(
                    self::$orderEntityName,
                    self::$shippingEntityName . '.order'
                )
        );

        $this->orderFieldDescriptors['orderNumber'] = new DoctrineFieldDescriptor(
            'number',
            'orderNumber',
            self::$orderEntityName,
            'salesorder.orders.number',
            $orderJoin
        );
        $this->orderFieldDescriptors['orderId'] = new DoctrineFieldDescriptor(
            'id',
            'orderId',
            self::$orderEntityName,
            'public.id',
            $orderJoin
        );
    }

    /**
     * check if necessary data is set
     * @param $data
     * @param $isNew
     */
    private function checkRequiredData($data, $isNew)
    {
        $this->checkDataSet($data, 'order', $isNew) && $this->checkDataSet($data['order'], 'id', $isNew);
        $this->checkDataSet($data, 'deliveryAddress', $isNew);
    }

    /**
     * checks data for attributes
     * @param array $data
     * @param $key
     * @param $isNew
     * @return bool
     * @throws Exception\MissingShippingAttributeException
     */
    private function checkDataSet(array $data, $key, $isNew)
    {
        $keyExists = array_key_exists($key, $data);

        if (($isNew && !($keyExists && $data[$key] !== null)) || (!$keyExists || $data[$key] === null)) {
            throw new MissingShippingAttributeException($key);
        }

        return $keyExists;
    }

    /**
     * Returns the entry from the data with the given key, or the given default value, if the key does not exist
     * @param array $data
     * @param string $key
     * @param string $default
     * @return mixed
     */
    private function getProperty(array $data, $key, $default = null)
    {
        return array_key_exists($key, $data) && $data[$key] !== null ? $data[$key] : $default;
    }

    /**
     * @param $data
     * @param Shipping $shipping
     * @throws Exception\ShippingDependencyNotFoundException
     * @throws Exception\MissingShippingAttributeException
     * @return null|object
     */
    private function setShippingOrder($data, Shipping $shipping)
    {
        $orderData = $this->getProperty($data, 'order');
        if ($orderData) {
            if (!array_key_exists('id', $orderData)) {
                throw new MissingShippingAttributeException('order.id');
            }
            $order = $this->em->getRepository(self::$orderEntityName)->find($orderData['id']);
            if (!$order) {
                throw new ShippingDependencyNotFoundException(self::$orderEntityName, $orderData['id']);
            }
            $shipping->setOrder($order);
            return $order;
        } else {
            $shipping->setOrder(null);
        }
        return null;
    }

    /**
     * process shipping items
     * @param $data
     * @param Shipping $shipping
     * @param $locale
     * @param $userId
     * @return bool
     * @throws OrderException
     * @throws Exception\MissingShippingAttributeException
     */
    private function processItems($data, Shipping $shipping, $locale, $userId)
    {
        $result = true;
        try {
            if ($this->checkDataSet($data, 'items', false)) {
                // items has to be an array
                if (!is_array($data['items'])) {
                    throw new MissingShippingAttributeException('items array');
                }

                $items = $data['items'];

                $get = function ($item){
                    return $item->getId();
                };

                $delete = function ($item) use ($shipping){
                    $entity = $item->getEntity();
                    // remove from order
                    $shipping->removeShippingItem($entity);
                    // delete item
                    $this->em->remove($entity);
                };

                $update = function ($item, $matchedEntry) use ($locale){
                    $shippingItem = $this->saveShippingItem($matchedEntry, $item->getEntity());

                    return $shippingItem ? true : false;
                };

                $add = function ($itemData) use ($locale, $shipping){
                    $shippingItem = $this->saveShippingItem($itemData);
                    $shippingItem->setShipping($shipping->getEntity());
                    return $shipping->addShippingItem($shippingItem, null, $locale);
                };

                $result = $this->restHelper->processSubEntities($shipping->getItems(), $items, $get, $add, $update, $delete);
            }
        } catch (Exception $e) {
            throw new OrderException('Error while creating items: ' . $e->getMessage());
        }
        return $result;
    }

    /**
     * @param $data
     * @param null $shippingItem
     * @return null|ShippingItemEntity
     */
    private function saveShippingItem($data, $shippingItem = null)
    {
        // check if shipping is set
        if (!$shippingItem) {
            $shippingItem = $this->createShippingItemEntity($data);
        }

        $shippingItem->setNote($this->getProperty($data, 'note', $shippingItem->getNote()));
        $shippingItem->setQuantity($this->getProperty($data, 'quantity', 0));

        return $shippingItem;
    }

    /**
     * creates a ShippingItem Entity by a given (shipping-)data array
     * @param $data
     * @return ShippingItemEntity
     * @throws Exception\ShippingDependencyNotFoundException
     * @throws Exception\MissingShippingAttributeException
     */
    private function createShippingItemEntity($data)
    {
        // check if necessary item data is set
        if (array_key_exists('item', $data) && array_key_exists('id', $data['item'])) {
            $itemId = $data['item']['id'];
            $item = $this->itemManager->findEntityById($itemId);
            if (!$item) {
                throw new ShippingDependencyNotFoundException('SuluSalesCoreBundle:Items', $itemId);
            }

            $shippingItem = new ShippingItemEntity();
            $shippingItem->setItem($item);
            $this->em->persist($shippingItem);
        } else {
            throw new MissingShippingAttributeException('ShippingItems.item.id');
        }

        return $shippingItem;
    }

    /**
     * @return ShippingRepository
     */
    private function getShippingRepository()
    {
        return $this->em->getRepository(self::$shippingEntityName);
    }
}
