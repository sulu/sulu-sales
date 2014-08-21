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

use Doctrine\Common\Persistence\ObjectManager;
use Sulu\Bundle\ContactBundle\Entity\Account;
use Sulu\Bundle\ContactBundle\Entity\Address;
use Sulu\Bundle\ContactBundle\Entity\Contact;
use Sulu\Bundle\ContactBundle\Entity\ContactRepository;
use Sulu\Bundle\ContactBundle\Entity\TermsOfDelivery;
use Sulu\Bundle\Sales\CoreBundle\Item\ItemManager;
use Sulu\Bundle\Sales\OrderBundle\Entity\OrderAddress;
use Sulu\Bundle\Sales\OrderBundle\Entity\OrderRepository;
use Sulu\Bundle\Sales\OrderBundle\Entity\Order as OrderEntity;
use Sulu\Bundle\Sales\OrderBundle\Entity\OrderStatus;
use Sulu\Bundle\Sales\OrderBundle\Order\Exception\MissingOrderAttributeException;
use Sulu\Bundle\Sales\OrderBundle\Order\Exception\OrderDependencyNotFoundException;
use Sulu\Bundle\Sales\OrderBundle\Order\Exception\OrderException;
use Sulu\Bundle\Sales\OrderBundle\Order\Exception\OrderNotFoundException;
use Sulu\Component\Rest\ListBuilder\Doctrine\FieldDescriptor\DoctrineConcatenationFieldDescriptor;
use Sulu\Component\Rest\ListBuilder\Doctrine\FieldDescriptor\DoctrineFieldDescriptor;
use Sulu\Bundle\Sales\OrderBundle\Api\Order;
use Sulu\Component\Rest\ListBuilder\Doctrine\FieldDescriptor\DoctrineJoinDescriptor;
use Sulu\Component\Rest\RestHelperInterface;
use Sulu\Component\Security\UserRepositoryInterface;
use DateTime;

class OrderManager
{
    protected static $orderEntityName = 'SuluSalesOrderBundle:Order';
    protected static $contactEntityName = 'SuluContactBundle:Contact';
    protected static $addressEntityName = 'SuluContactBundle:Address';
    protected static $accountEntityName = 'SuluContactBundle:Account';
    protected static $orderStatusEntityName = 'SuluSalesOrderBundle:OrderStatus';
    protected static $orderAddressEntityName = 'SuluSalesOrderBundle:OrderAddress';
    protected static $orderStatusTranslationEntityName = 'SuluSalesOrderBundle:OrderStatusTranslation';
    protected static $itemEntityName = 'SuluSalesCoreBundle:Item';
    protected static $termsOfDeliveryEntityName = 'SuluContactBundle:TermsOfDelivery';
    protected static $termsOfPaymentEntityName = 'SuluContactBundle:TermsOfPayment';

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
     * @var UserRepositoryInterface
     */
    private $userRepository;


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
        OrderRepository $orderRepository,
        UserRepositoryInterface $userRepository,
        ItemManager $itemManager,
        RestHelperInterface $restHelper
    )
    {
        $this->orderRepository = $orderRepository;
        $this->userRepository = $userRepository;
        $this->em = $em;
        $this->itemManager = $itemManager;
        $this->restHelper = $restHelper;
    }

    /**
     * @param array $data
     * @param $locale
     * @param $userId
     * @param null $id
     * @throws Exception\OrderNotFoundException
     * @throws Exception\OrderException
     * @return null|Order|\Sulu\Bundle\Sales\OrderBundle\Entity\Order
     */
    public function save(array $data, $locale, $userId, $id = null)
    {
        if ($id) {
            $order = $this->findByIdAndLocale($id, $locale);

            if (!$order) {
                throw new OrderNotFoundException($id);
            }
        } else {
            $order = new Order(new OrderEntity(), $locale);
        }

        // check for data
        $this->checkRequiredData($data, $id === null);

        $user = $this->userRepository->findUserById($userId);

        $order->setOrderNumber($this->getProperty($data, 'orderNumber', $order->getOrderNumber()));
        $order->setCurrency($this->getProperty($data, 'currency', $order->getCurrency()));
        $order->setCostCentre($this->getProperty($data, 'costCentre', $order->getCostCentre()));
        $order->setCommission($this->getProperty($data, 'commission', $order->getCommission()));
        $order->setTaxfree($this->getProperty($data, 'taxfree', $order->getTaxfree()));

        if (($desiredDeliveryDate = $this->getProperty($data, 'desiredDeliveryDate', $order->getDesiredDeliveryDate())) !== null) {
            if (is_string($desiredDeliveryDate)) {
                $desiredDeliveryDate = new DateTime($data['desiredDeliveryDate']);
            }
            $order->setDesiredDeliveryDate($desiredDeliveryDate);
        }

        $this->setTermsOfDelivery($data, $order);
        $this->setTermsOfPayment($data, $order);

        $account = $this->setAccount($data, $order);

        // TODO: check sessionID
//        $order->setSessionId($this->getProperty($data, 'number', $order->getNumber()));

        // add contact
        $contact = $this->addContactRelation($data, 'contact', function ($contact) use ($order){
            $order->setContact($contact);
        });
        // add contact
        $this->addContactRelation($data, 'responsibleContact', function ($contact) use ($order){
            $order->setResponsibleContact($contact);
        });

        // create order (POST)
        if ($order->getId() == null) {
            $order->setCreated(new DateTime());
            $order->setCreator($user);
            $this->em->persist($order->getEntity());

            // TODO: determine orders status
            // FIXME: currently the status with id=1 is taken
            $status = $this->em->getRepository(self::$orderStatusEntityName)->find(1);
            $order->setStatus($status);

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

        // set customer name to account if set, otherwise to contact
        $customerName = $account !== null ? $account->getName() : $contact->getFullName();
        $order->setCustomerName($customerName);

        // set OrderAddress data
        $this->setOrderAddress($order->getDeliveryAddress(), $data['deliveryAddress']['id'], $contact, $account);
        $this->setOrderAddress($order->getInvoiceAddress(), $data['paymentAddress']['id'], $contact, $account);

        // handle items
        if (!$this->processItems($data, $order, $locale, $userId)) {
            throw new OrderException('Error while processing items');
        }

        $order->setChanged(new DateTime());
        $order->setChanger($user);

        $this->em->flush();

        return $order;
    }

    /**
     * deletes an order
     * @param $id
     * @throws Exception\OrderNotFoundException
     */
    public function delete($id)
    {
        // TODO: move order to an archive instead of remove it from database
        $order = $this->orderRepository->findById($id);

        if (!$order) {
            throw new OrderNotFoundException($id);
        }

        $this->em->remove($order);
        $this->em->flush();
    }

    public function convertStatus(Order $order, $statusId, $flush = false) {

        // get current status
        $currentStatus = $order->getStatus()->getEntity();

        // get desired status
        $statusEntity = $this->em
            ->getRepository(self::$orderStatusEntityName)
            ->find($statusId);
        if (!$statusEntity) {
            throw new EntityNotFoundException($statusEntity, $statusEntity);
        }

        // check if status has changed
        if($currentStatus->getId() !== $statusId) {
            $order->setStatus($statusEntity);
        }
    }

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
     * Finds an order by id and locale
     * @param $id
     * @param $locale
     * @return null|Order
     */
    public function findByIdAndLocale($id, $locale)
    {
        $order = $this->orderRepository->findByIdAndLocale($id, $locale);

        if ($order) {
            return new Order($order, $locale);
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
            $order = $this->orderRepository->findAllByLocale($locale);
        } else {
            $order = $this->orderRepository->findByLocaleAndFilter($locale, $filter);
        }

        array_walk(
            $order,
            function (&$order) use ($locale){
                $order = new Order($order, $locale);
            }
        );

        return $order;
    }

    /**
     * initializes field descriptors
     */
    private function initializeFieldDescriptors($locale)
    {
        $this->fieldDescriptors['id'] = new DoctrineFieldDescriptor('id', 'id', self::$orderEntityName, 'public.id', array(), true);
        $this->fieldDescriptors['number'] = new DoctrineFieldDescriptor('number', 'number', self::$orderEntityName, 'salesorder.orders.number', array(), false, true);

        // TODO: get customer from order-address

        $contactJoin = array(
            self::$orderAddressEntityName => new DoctrineJoinDescriptor(
                    self::$orderAddressEntityName,
                    self::$orderEntityName . '.invoiceAddress'
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
            self::$orderStatusTranslationEntityName,
            'salesorder.orders.status',
            array(
                self::$orderStatusEntityName => new DoctrineJoinDescriptor(
                        self::$orderStatusEntityName,
                        self::$orderEntityName . '.status'
                    ),
                self::$orderStatusTranslationEntityName => new DoctrineJoinDescriptor(
                        self::$orderStatusTranslationEntityName,
                        self::$orderStatusEntityName . '.translations',
                        self::$orderStatusTranslationEntityName . ".locale = '" . $locale . "'"
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
        // check if contact and status are set
        $this->checkDataSet($data, 'contact', $isNew) && $this->checkDataSet($data['contact'], 'id', $isNew);
        $this->checkDataSet($data, 'deliveryAddress', $isNew) && $this->checkDataSet($data['deliveryAddress'], 'id', $isNew);
        $this->checkDataSet($data, 'paymentAddress', $isNew) && $this->checkDataSet($data['paymentAddress'], 'id', $isNew);
    }

    /**
     * checks data for attributes
     * @param array $data
     * @param $key
     * @param $isNew
     * @return bool
     * @throws Exception\MissingOrderAttributeException
     */
    private function checkDataSet(array $data, $key, $isNew)
    {
        $keyExists = array_key_exists($key, $data);

        if (($isNew && !($keyExists && $data[$key] !== null)) || (!$keyExists || $data[$key] === null)) {
            throw new MissingOrderAttributeException($key);
        }

        return $keyExists;
    }

    private function checkIfSet($key, $data)
    {
        $keyExists = array_key_exists($key, $data);

        return $keyExists && $data[$key] !== null && $data[$key] !== '';
    }

    /**
     * searches for contact in specified data and calls callback function
     * @param array $data
     * @param $dataKey
     * @param $addCallback
     * @return Contact
     * @throws Exception\OrderDependencyNotFoundException
     */
    private function addContactRelation(array $data, $dataKey, $addCallback)
    {
        if (array_key_exists($dataKey, $data) && array_key_exists('id', $data[$dataKey])) {
            $contactId = $data[$dataKey]['id'];
            /** @var Contact $contact */
            $contact = $this->em->getRepository(self::$contactEntityName)->find($contactId);
            if (!$contact) {
                throw new OrderDependencyNotFoundException(self::$contactEntityName, $contactId);
            }
            $addCallback($contact);
            return $contact;
        }
    }

    /**
     * @param OrderAddress $orderAddress
     * @param $addressId
     * @param Contact $contact
     * @param Account $account
     * @throws OrderDependencyNotFoundException
     */
    private function setOrderAddress(OrderAddress $orderAddress, $addressId, Contact $contact, Account $account = null)
    {
        // check if address with id can be found
        // add contact data
        $orderAddress->setFirstName($contact->getFirstName());
        $orderAddress->setLastName($contact->getLastName());
        if ($contact->getTitle() !== null) {
            $orderAddress->setTitle($contact->getTitle()->getTitle());
        }

        // add account data
        if ($account) {
            $orderAddress->setAccountName($account->getName());
            $orderAddress->setUid($account->getUid());
        } else {
            $orderAddress->setAccountName(null);
            $orderAddress->setUid(null);
        }

        // TODO: add phone

        /** @var Address $address */
        $address = $this->em->getRepository(self::$addressEntityName)->find($addressId);
        if (!$address) {
            throw new OrderDependencyNotFoundException(self::$addressEntityName, $addressId);
        }
        $this->copyAddressToOrderAddress($orderAddress, $address);
    }

    /**
     * copies address data to order address
     * @param OrderAddress $orderAddress
     * @param Address $address
     */
    private function copyAddressToOrderAddress(OrderAddress &$orderAddress, Address $address)
    {
        $orderAddress->setAddress($address);
        $orderAddress->setStreet($address->getStreet());
        $orderAddress->setNumber($address->getNumber());
        $orderAddress->setAddition($address->getAddition());
        $orderAddress->setCity($address->getCity());
        $orderAddress->setZip($address->getZip());
        $orderAddress->setState($address->getState());
        $orderAddress->setCountry($address->getCountry()->getName());
        // TODO: check whats really needed at postbox address
//        $orderAddress->setBox(sprintf('%s %s %s', $address->getPostboxNumber(), $address->getPostboxPostcode(), $address->getPostboxCity()));
        $orderAddress->setBox($address->getPostboxNumber());
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
        return array_key_exists($key, $data) ? $data[$key] : $default;
    }

    /**
     * @param $data
     * @param Order $order
     * @return null|object
     * @throws Exception\MissingOrderAttributeException
     * @throws Exception\OrderDependencyNotFoundException
     */
    private function setTermsOfDelivery($data, Order $order)
    {
        // terms of delivery
        $termsOfDeliveryData = $this->getProperty($data, 'termsOfDelivery');
        if ($termsOfDeliveryData) {
            if (!array_key_exists('id', $termsOfDeliveryData)) {
                throw new MissingOrderAttributeException('termsOfDelivery.id');
            }
            // TODO: inject repository class
            $terms = $this->em->getRepository(self::$termsOfDeliveryEntityName)->find($termsOfDeliveryData['id']);
            if (!$terms) {
                throw new OrderDependencyNotFoundException(self::$termsOfDeliveryEntityName, $termsOfDeliveryData['id']);
            }
            $order->setTermsOfDelivery($terms);
            $order->setTermsOfDeliveryContent($terms->getTerms());
            return $terms;
        } else {
            $order->setTermsOfDelivery(null);
            $order->setTermsOfDeliveryContent(null);
        }
        return null;
    }

    /**
     * @param $data
     * @param Order $order
     * @return null|object
     * @throws Exception\MissingOrderAttributeException
     * @throws Exception\OrderDependencyNotFoundException
     */
    private function setTermsOfPayment($data, Order $order)
    {
        // terms of delivery
        $termsOfPaymentData = $this->getProperty($data, 'termsOfPayment');
        if ($termsOfPaymentData) {
            if (!array_key_exists('id', $termsOfPaymentData)) {
                throw new MissingOrderAttributeException('termsOfPayment.id');
            }
            // TODO: inject repository class
            $terms = $this->em->getRepository(self::$termsOfPaymentEntityName)->find($termsOfPaymentData['id']);
            if (!$terms) {
                throw new OrderDependencyNotFoundException(self::$termsOfPaymentEntityName, $termsOfPaymentData['id']);
            }
            $order->setTermsOfPayment($terms);
            $order->setTermsOfPaymentContent($terms->getTerms());
            return $terms;
        } else {
            $order->setTermsOfPayment(null);
            $order->setTermsOfPaymentContent(null);
        }
        return null;
    }

    /**
     * @param $data
     * @param Order $order
     * @return null|object
     * @throws Exception\MissingOrderAttributeException
     * @throws Exception\OrderDependencyNotFoundException
     */
    private function setAccount($data, Order $order)
    {
        $accountData = $this->getProperty($data, 'account');
        if ($accountData) {
            if (!array_key_exists('id', $accountData)) {
                throw new MissingOrderAttributeException('account.id');
            }
            // TODO: inject repository class
            $account = $this->em->getRepository(self::$accountEntityName)->find($accountData['id']);
            if (!$account) {
                throw new OrderDependencyNotFoundException(self::$accountEntityName, $accountData['id']);
            }
            $order->setAccount($account);
            return $account;
        } else {
            $order->setAccount(null);
        }
        return null;
    }

    private function processItems($data, Order $order, $locale, $userId)
    {
        $result = true;
        try {
            if ($this->checkIfSet('items', $data)) {
                // items has to be an array
                if (!is_array($data['items'])) {
                    throw new MissingOrderAttributeException('items arrray');
                }

                $items = $data['items'];

                $get = function ($item){
                    return $item->getId();
                };

                $delete = function ($item) use ($order){
                    $entity = $item->getEntity();
                    // remove from order
                    $order->removeItem($entity);
                    // delete item
                    $this->em->remove($entity);
                };

                $update = function ($item, $matchedEntry) use ($locale, $userId, $order){
                    $itemEntity = $this->itemManager->save($matchedEntry, $locale, $userId, $item);
                    return $itemEntity ? true : false;
                };

                $add = function ($itemData) use ($locale, $userId, $order){
                    $item = $this->itemManager->save($itemData, $locale, $userId);
                    return $order->addItem($item->getEntity());
                };

                $result = $this->restHelper->processSubEntities($order->getItems(), $items, $get, $add, $update, $delete);

            }
        } catch (Exception $e) {
            throw new OrderException('Error while creating items: ' . $e->getMessage());
        }
        return $result;
    }
}
