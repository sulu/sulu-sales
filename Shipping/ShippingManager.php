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

use Doctrine\Common\Persistence\ObjectManager;
use Sulu\Bundle\Sales\CoreBundle\Item\ItemManager;
use Sulu\Bundle\Sales\ShippingBundle\Shipping\Exception\MissingShippingAttributeException;
use Sulu\Component\Rest\ListBuilder\Doctrine\FieldDescriptor\DoctrineConcatenationFieldDescriptor;
use Sulu\Component\Rest\ListBuilder\Doctrine\FieldDescriptor\DoctrineFieldDescriptor;
use Sulu\Component\Rest\ListBuilder\Doctrine\FieldDescriptor\DoctrineJoinDescriptor;
use Sulu\Component\Rest\RestHelperInterface;

use Sulu\Bundle\Sales\ShippingBundle\Entity\Shipping as ShippingEntity;
use Sulu\Bundle\Sales\ShippingBundle\Api\Shipping;
use DateTime;

class ShippingManager
{
    protected static $shippingEntityName = 'SuluSalesShippingBundle:Shipping';
    protected static $orderEntityName = 'SuluSalesOrderBundle:Order';
    protected static $userEntityName = 'SuluSecurityBundle:User';
    protected static $orderAddressEntityName = 'SuluSalesOrderBundle:OrderAddress';
    protected static $shippingStatusEntityName = 'SuluSalesShippingBundle:ShippingStatus';
    protected static $shippingStatusTranslationEntityName = 'SuluSalesShippingBundle:ShippingStatusTranslation';

//    protected static $contactEntityName = 'SuluContactBundle:Contact';
//    protected static $addressEntityName = 'SuluContactBundle:Address';
//    protected static $accountEntityName = 'SuluContactBundle:Account';
//    protected static $itemEntityName = 'SuluSalesCoreBundle:Item';
//    protected static $termsOfDeliveryEntityName = 'SuluContactBundle:TermsOfDelivery';
//    protected static $termsOfPaymentEntityName = 'SuluContactBundle:TermsOfPayment';

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
     * Describes the fields, which are handled by this controller
     * @var DoctrineFieldDescriptor[]
     */
    private $fieldDescriptors = array();

    public function __construct(
        ObjectManager $em,
        ItemManager $itemManager,
        RestHelperInterface $restHelper
    )
    {
        $this->em = $em;
        $this->itemManager = $itemManager;
        $this->restHelper = $restHelper;
    }

    /**
     * @param array $data
     * @param $locale
     * @param $userId
     * @param null $id
     * @return null|Order|\Sulu\Bundle\Sales\ShippingBundle\Entity\Shipping
     */
    public function save(array $data, $locale, $userId, $id = null)
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

        $user = $this->em->getRepository(self::$userEntityName)->findUserById($userId);

//        $order->setOrderNumber($this->getProperty($data, 'orderNumber', $order->getOrderNumber()));
//        $order->setCurrency($this->getProperty($data, 'currency', $order->getCurrency()));
//        $order->setCostCentre($this->getProperty($data, 'costCentre', $order->getCostCentre()));
//        $order->setCommission($this->getProperty($data, 'commission', $order->getCommission()));
//        $order->setTaxfree($this->getProperty($data, 'taxfree', $order->getTaxfree()));
//
//        if (($desiredDeliveryDate = $this->getProperty($data, 'desiredDeliveryDate', $order->getDesiredDeliveryDate())) !== null) {
//            if (is_string($desiredDeliveryDate)) {
//                $desiredDeliveryDate = new DateTime($data['desiredDeliveryDate']);
//            }
//            $order->setDesiredDeliveryDate($desiredDeliveryDate);
//        }
//
//        $this->setTermsOfDelivery($data, $order);
//        $this->setTermsOfPayment($data, $order);
//
//        $account = $this->setAccount($data, $order);
//
//        // TODO: check sessionID
////        $order->setSessionId($this->getProperty($data, 'number', $order->getNumber()));
//
//        // add contact
//        $contact = $this->addContactRelation($data, 'contact', function ($contact) use ($order){
//            $order->setContact($contact);
//        });
//        // add contact
//        $this->addContactRelation($data, 'responsibleContact', function ($contact) use ($order){
//            $order->setResponsibleContact($contact);
//        });
//
//        // create order (POST)
//        if ($order->getId() == null) {
//            $order->setCreated(new DateTime());
//            $order->setCreator($user);
//            $this->em->persist($order->getEntity());
//
//            // TODO: determine orders status
//            // FIXME: currently the status with id=1 is taken
//            $status = $this->em->getRepository(self::$orderStatusEntityName)->find(1);
//            $order->setStatus($status);
//
//            // create OrderAddress
//            $deliveryAddress = new OrderAddress();
//            $invoiceAddress = new OrderAddress();
//            // persist entities
//            $this->em->persist($deliveryAddress);
//            $this->em->persist($invoiceAddress);
//            // assign to order
//            $order->setDeliveryAddress($deliveryAddress);
//            $order->setInvoiceAddress($invoiceAddress);
//        }
//
//        // set customer name to account if set, otherwise to contact
//        $customerName = $account !== null ? $account->getName() : $contact->getFullName();
//        $order->setCustomerName($customerName);
//
//        // set OrderAddress data
//        $this->setOrderAddress($order->getDeliveryAddress(), $data['deliveryAddress'], $contact, $account);
//        $this->setOrderAddress($order->getInvoiceAddress(), $data['invoiceAddress'], $contact, $account);
//
//        // handle items
//        if (!$this->processItems($data, $order, $locale, $userId)) {
//            throw new OrderException('Error while processing items');
//        }
//
//        $order->setChanged(new DateTime());
//        $order->setChanger($user);
//
//        $this->em->flush();
//
//        return $order;
    }
//
//    /**
//     * deletes an order
//     * @param $id
//     * @throws Exception\OrderNotFoundException
//     */
//    public function delete($id)
//    {
//        // TODO: move order to an archive instead of remove it from database
//        $order = $this->orderRepository->findById($id);
//
//        if (!$order) {
//            throw new OrderNotFoundException($id);
//        }
//
//        $this->em->remove($order);
//        $this->em->flush();
//    }
//
//    public function convertStatus(Order $order, $statusId, $flush = false) {
//
//        // get current status
//        $currentStatus = $order->getStatus()->getEntity();
//
//        // get desired status
//        $statusEntity = $this->em
//            ->getRepository(self::$orderStatusEntityName)
//            ->find($statusId);
//        if (!$statusEntity) {
//            throw new EntityNotFoundException($statusEntity, $statusEntity);
//        }
//
//        // check if status has changed
//        if($currentStatus->getId() !== $statusId) {
//            if ($statusId === OrderStatusEntity::STATUS_CREATED) {
//                // TODO: re-edit - do some business logic
//            }
//
//            $order->setStatus($statusEntity);
//        }
//    }
//
    /**
     * @param $locale
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
     * @param $key
     * @return DoctrineFieldDescriptor
     */
    public function getFieldDescriptor($key)
    {
        return $this->fieldDescriptors[$key];
    }

    /**
     * Finds a shipping by id and locale
     * @param $id
     * @param $locale
     * @return null|Shipping
     */
    public function findByIdAndLocale($id, $locale)
    {
        $shipping = $this->em->getRepository(self::$shippingEntityName)->findByIdAndLocale($id, $locale);

        if ($shipping) {
            return new Shipping($shipping, $locale);
        } else {
            return null;
        }
    }

    /**
     * @param $locale
     * @param array $filter
     * @return mixed
     */
    public function findAllByLocale($locale, $filter = array())
    {
        if (empty($filter)) {
            $shipping = $this->em->getRepository(self::$shippingEntityName)->findAllByLocale($locale);
        } else {
            $shipping = $this->em->getRepository(self::$shippingEntityName)->findByLocaleAndFilter($locale, $filter);
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
        $this->fieldDescriptors['id'] = new DoctrineFieldDescriptor('id', 'id', self::$shippingEntityName, 'public.id', array(), true);
        $this->fieldDescriptors['number'] = new DoctrineFieldDescriptor('number', 'number', self::$shippingEntityName, 'salesorder.orders.number', array(), false, true);

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
            'salesorder.orders.contact',
            ' ',
            false,
            false,
            '',
            '',
            '160px'
        );

        $this->fieldDescriptors['status'] = new DoctrineFieldDescriptor(
            'name',
            'status',
            self::$shippingStatusTranslationEntityName,
            'salesorder.orders.status',
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
    }

    /**
     * check if necessary data is set
     * @param $data
     * @param $isNew
     */
    private function checkRequiredData($data, $isNew)
    {
        $this->checkDataSet($data, 'order', $isNew) && $this->checkDataSet($data['order'], 'id', $isNew);
        $this->checkDataSet($data, 'contact', $isNew) && $this->checkDataSet($data['contact'], 'id', $isNew);
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

//    private function checkIfSet($key, $data)
//    {
//        $keyExists = array_key_exists($key, $data);
//
//        return $keyExists && $data[$key] !== null && $data[$key] !== '';
//    }


    /**
     * Returns the entry from the data with the given key, or the given default value, if the key does not exist
     * @param array $data
     * @param string $key
     * @param string $default
     * @return mixed
     */
    private function getProperty(array $data, $key, $default = null)
    {
        return array_key_exists($key, $data) ? $data[$key] : $default;
    }

//    /**
//     * @param $data
//     * @param Order $order
//     * @return null|object
//     * @throws Exception\MissingOrderAttributeException
//     * @throws Exception\OrderDependencyNotFoundException
//     */
//    private function setTermsOfDelivery($data, Order $order)
//    {
//        // terms of delivery
//        $termsOfDeliveryData = $this->getProperty($data, 'termsOfDelivery');
//        if ($termsOfDeliveryData) {
//            if (!array_key_exists('id', $termsOfDeliveryData)) {
//                throw new MissingOrderAttributeException('termsOfDelivery.id');
//            }
//            // TODO: inject repository class
//            $terms = $this->em->getRepository(self::$termsOfDeliveryEntityName)->find($termsOfDeliveryData['id']);
//            if (!$terms) {
//                throw new OrderDependencyNotFoundException(self::$termsOfDeliveryEntityName, $termsOfDeliveryData['id']);
//            }
//            $order->setTermsOfDelivery($terms);
//            $order->setTermsOfDeliveryContent($terms->getTerms());
//            return $terms;
//        } else {
//            $order->setTermsOfDelivery(null);
//            $order->setTermsOfDeliveryContent(null);
//        }
//        return null;
//    }
//
//    /**
//     * @param $data
//     * @param Order $order
//     * @return null|object
//     * @throws Exception\MissingOrderAttributeException
//     * @throws Exception\OrderDependencyNotFoundException
//     */
//    private function setAccount($data, Order $order)
//    {
//        $accountData = $this->getProperty($data, 'account');
//        if ($accountData) {
//            if (!array_key_exists('id', $accountData)) {
//                throw new MissingOrderAttributeException('account.id');
//            }
//            // TODO: inject repository class
//            $account = $this->em->getRepository(self::$accountEntityName)->find($accountData['id']);
//            if (!$account) {
//                throw new OrderDependencyNotFoundException(self::$accountEntityName, $accountData['id']);
//            }
//            $order->setAccount($account);
//            return $account;
//        } else {
//            $order->setAccount(null);
//        }
//        return null;
//    }

//    private function processItems($data, Order $order, $locale, $userId)
//    {
//        $result = true;
//        try {
//            if ($this->checkIfSet('items', $data)) {
//                // items has to be an array
//                if (!is_array($data['items'])) {
//                    throw new MissingOrderAttributeException('items arrray');
//                }
//
//                $items = $data['items'];
//
//                $get = function ($item){
//                    return $item->getId();
//                };
//
//                $delete = function ($item) use ($order){
//                    $entity = $item->getEntity();
//                    // remove from order
//                    $order->removeItem($entity);
//                    // delete item
//                    $this->em->remove($entity);
//                };
//
//                $update = function ($item, $matchedEntry) use ($locale, $userId, $order){
//                    $itemEntity = $this->itemManager->save($matchedEntry, $locale, $userId, $item);
//                    return $itemEntity ? true : false;
//                };
//
//                $add = function ($itemData) use ($locale, $userId, $order){
//                    $item = $this->itemManager->save($itemData, $locale, $userId);
//                    return $order->addItem($item->getEntity());
//                };
//
//                $result = $this->restHelper->processSubEntities($order->getItems(), $items, $get, $add, $update, $delete);
//
//            }
//        } catch (Exception $e) {
//            throw new OrderException('Error while creating items: ' . $e->getMessage());
//        }
//        return $result;
//    }
}
