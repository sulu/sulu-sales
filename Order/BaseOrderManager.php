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
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;
use Sulu\Bundle\ContactBundle\Entity\Account;
use Sulu\Bundle\ContactBundle\Entity\Address;
use Sulu\Bundle\ContactBundle\Entity\Contact;
use Sulu\Bundle\ContactBundle\Entity\TermsOfDelivery;
use Sulu\Bundle\Sales\CoreBundle\Item\ItemManager;
use Sulu\Bundle\Sales\OrderBundle\Entity\OrderAddress;
use Sulu\Bundle\Sales\OrderBundle\Entity\OrderRepository;
use Sulu\Bundle\Sales\OrderBundle\Entity\Order as OrderEntity;
use Sulu\Bundle\Sales\OrderBundle\Entity\OrderType;
use Sulu\Bundle\Sales\OrderBundle\Order\Exception\MissingOrderAttributeException;
use Sulu\Bundle\Sales\OrderBundle\Order\Exception\OrderDependencyNotFoundException;
use Sulu\Bundle\Sales\OrderBundle\Order\Exception\OrderException;
use Sulu\Component\Rest\Exception\EntityNotFoundException;
use Sulu\Component\Rest\ListBuilder\Doctrine\FieldDescriptor\DoctrineConcatenationFieldDescriptor;
use Sulu\Component\Rest\ListBuilder\Doctrine\FieldDescriptor\DoctrineFieldDescriptor;
use Sulu\Bundle\Sales\OrderBundle\Api\Order;
use Sulu\Component\Rest\ListBuilder\Doctrine\FieldDescriptor\DoctrineJoinDescriptor;
use Sulu\Component\Security\Authentication\UserRepositoryInterface;
use DateTime;
use Sulu\Component\Persistence\RelationTrait;

abstract class BaseOrderManager
{
    use RelationTrait;

    protected static $orderEntityName = 'SuluSalesOrderBundle:Order';
    protected static $contactEntityName = 'SuluContactBundle:Contact';
    protected static $addressEntityName = 'SuluContactBundle:Address';
    protected static $accountEntityName = 'SuluContactBundle:Account';
    protected static $orderStatusEntityName = 'SuluSalesOrderBundle:OrderStatus';
    protected static $orderTypeEntityName = 'SuluSalesOrderBundle:OrderType';
    protected static $orderTypeTranslationEntityName = 'SuluSalesOrderBundle:OrderTypeTranslation';
    protected static $orderAddressEntityName = 'SuluSalesOrderBundle:OrderAddress';
    protected static $orderStatusTranslationEntityName = 'SuluSalesOrderBundle:OrderStatusTranslation';
    protected static $itemEntityName = 'SuluSalesCoreBundle:Item';
    protected static $termsOfDeliveryEntityName = 'SuluContactBundle:TermsOfDelivery';
    protected static $termsOfPaymentEntityName = 'SuluContactBundle:TermsOfPayment';

    /**
     * @var string
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
     * @var DoctrineFieldDescriptor[]
     */
    private $fieldDescriptors = array();

    public function getOrderTypeEntityById($typeId) {
        // get desired status
        $typeEntity= $this->orderTypeRepository->find($typeId);
        if (!$typeEntity) {
            throw new EntityNotFoundException($typeEntity, $typeId);
        }
        return $typeEntity;
    }

    /**
     * finds a status by id
     * @param $statusId
     * @return object
     * @throws \Sulu\Component\Rest\Exception\EntityNotFoundException
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
     * find order entity by id
     * @param $id
     * @throws \Sulu\Component\Rest\Exception\EntityNotFoundException
     * @internal param $statusId
     * @return OrderEntity
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
     * find order for item with id
     * @param $id
     * @throws \Sulu\Component\Rest\Exception\EntityNotFoundException
     * @internal param $statusId
     * @return OrderEntity
     */
    public function findOrderEntityForItemWithId($id)
    {
        try {
            return $this->em
                ->getRepository(static::$orderEntityName)
                ->findOrderForItemWithId($id);
        } catch (NoResultException $nre) {
            throw new EntityNotFoundException(static::$itemEntity, $id);
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

        if ($order) {
            array_walk(
                $order,
                function (&$order) use ($locale) {
                    $order = new Order($order, $locale);
                }
            );
        }

        return $order;
    }

    /**
     * sets a date if it's set in data
     *
     * @param $data
     * @param $key
     * @param $currentDate
     * @param callable $setCallback
     */
    protected function setDate($data, $key, $currentDate, callable $setCallback)
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
     * @param $data
     * @param $order
     * @throws EntityNotFoundException
     * @throws OrderException
     */
    protected function setOrderType($data, $order)
    {
        // get OrderType
        $type = $this->getProperty($data, 'type', null);
        if (!is_null($type)) {
            if (is_array($type) && isset($type['id'])) {
                // if provided as array
                $typeId = $type['id'];
            } else if(is_numeric($type)) {
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
     * initializes field descriptors
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
    }

    /**
     * check if necessary data is set
     * @param $data
     * @param $isNew
     */
    protected function checkRequiredData($data, $isNew)
    {
        // TODO: implement
    }

    /**
     * checks data for attributes
     * @param array $data
     * @param $key
     * @param $isNew
     * @return bool
     * @throws Exception\MissingOrderAttributeException
     */
    protected function checkDataSet(array $data, $key, $isNew)
    {
        $keyExists = array_key_exists($key, $data);

        if (($isNew && !($keyExists && $data[$key] !== null)) || (!$keyExists || $data[$key] === null)) {
            throw new MissingOrderAttributeException($key);
        }

        return $keyExists;
    }

    /**
     * checks if data is set
     * @param $key
     * @param $data
     * @return bool
     */
    protected function checkIfSet($key, $data)
    {
        $keyExists = array_key_exists($key, $data);

        return $keyExists && $data[$key] !== null && $data[$key] !== '';
    }

    /**
     * searches for contact in specified data and calls callback function
     * @param array $data
     * @param $dataKey
     * @param $addCallback
     * @throws Exception\MissingOrderAttributeException
     * @throws Exception\OrderDependencyNotFoundException
     * @return Contact|null
     */
    protected function addContactRelation(array $data, $dataKey, $addCallback)
    {
        $contact = null;
        if (array_key_exists($dataKey, $data) && is_array($data[$dataKey]) && array_key_exists('id', $data[$dataKey])) {
            /** @var Contact $contact */
            $contactId = $data[$dataKey]['id'];
            $contact = $this->em->getRepository(static::$contactEntityName)->find($contactId);
            if (!$contact) {
                throw new OrderDependencyNotFoundException(static::$contactEntityName, $contactId);
            }
            $addCallback($contact);
        }
        return $contact;
    }

    /**
     * @param OrderAddress $orderAddress
     * @param $addressData
     * @param Contact $contact
     * @param Account|null $account
     * @throws OrderDependencyNotFoundException
     */
    protected function setOrderAddress(OrderAddress $orderAddress, $addressData, $contact = null, $account = null)
    {
        // check if address with id can be found

        $contactData = $this->getContactData($addressData, $contact);
        // add contact data
        $orderAddress->setFirstName($contactData['firstName']);
        $orderAddress->setLastName($contactData['lastName']);
        if (isset($contactData['title'])) {
            $orderAddress->setTitle($contactData['title']);
        }
        if (isset($contactData['salutation'])) {
            $orderAddress->setSalutation($contactData['salutation']);
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

        $this->setAddressDataForOrder($orderAddress, $addressData);
    }

    /**
     * copies address data to order address
     * @param OrderAddress $orderAddress
     * @param $addressData
     */
    protected function setAddressDataForOrder(OrderAddress &$orderAddress, $addressData)
    {
        $orderAddress->setStreet($this->getProperty($addressData, 'street', ''));
        $orderAddress->setNumber($this->getProperty($addressData, 'number', ''));
        $orderAddress->setAddition($this->getProperty($addressData, 'addition', ''));
        $orderAddress->setCity($this->getProperty($addressData, 'city', ''));
        $orderAddress->setZip($this->getProperty($addressData, 'zip', ''));
        $orderAddress->setState($this->getProperty($addressData, 'state', ''));
        $orderAddress->setCountry($this->getProperty($addressData, 'country', ''));
        $orderAddress->setEmail($this->getProperty($addressData, 'email', ''));
        $orderAddress->setPhone($this->getProperty($addressData, 'phone', ''));

        $orderAddress->setPostboxCity($this->getProperty($addressData, 'postboxCity', ''));
        $orderAddress->setPostboxPostcode($this->getProperty($addressData, 'postboxPostcode', ''));
        $orderAddress->setPostboxNumber($this->getProperty($addressData, 'postboxNumber', ''));
    }

    /**
     * Returns the entry from the data with the given key, or the given default value, if the key does not exist
     * @param array $data
     * @param string $key
     * @param string $default
     * @return mixed
     */
    protected function getProperty(array $data, $key, $default = null)
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
    protected function setTermsOfDelivery($data, Order $order)
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
        } else {
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
     * @param $data
     * @param Order $order
     * @return null|object
     * @throws Exception\MissingOrderAttributeException
     * @throws Exception\OrderDependencyNotFoundException
     */
    protected function setTermsOfPayment($data, Order $order)
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
                throw new OrderDependencyNotFoundException(static::$termsOfPaymentEntityName, $termsOfPaymentData['id']);
            }
            $order->setTermsOfPayment($terms);
            $order->setTermsOfPaymentContent($terms->getTerms());

        } else {
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
     * @param $data
     * @param Order $order
     * @return null|object
     * @throws Exception\MissingOrderAttributeException
     * @throws Exception\OrderDependencyNotFoundException
     */
    protected function setAccount($data, Order $order)
    {
        $accountData = $this->getProperty($data, 'account');
        if ($accountData) {
            if (!array_key_exists('id', $accountData)) {
                throw new MissingOrderAttributeException('account.id');
            }
            // TODO: inject repository class
            $account = $this->em->getRepository(static::$accountEntityName)->find($accountData['id']);
            if (!$account) {
                throw new OrderDependencyNotFoundException(static::$accountEntityName, $accountData['id']);
            }
            $order->setAccount($account);
            return $account;
        } else {
            $order->setAccount(null);
        }
        return null;
    }
}
