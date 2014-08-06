<?php
/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\Sales\CoreBundle\Item;

use Doctrine\Common\Persistence\ObjectManager;
use Sulu\Bundle\ContactBundle\Entity\Account;
use Sulu\Bundle\ContactBundle\Entity\Address;
use Sulu\Bundle\ContactBundle\Entity\Contact;
use Sulu\Bundle\ContactBundle\Entity\ContactRepository;
use Sulu\Bundle\ContactBundle\Entity\TermsOfDelivery;
use Sulu\Bundle\Sales\OrderBundle\Entity\OrderAddress;
use Sulu\Bundle\Sales\OrderBundle\Entity\OrderRepository;
use Sulu\Bundle\Sales\OrderBundle\Entity\Order as OrderEntity;
use Sulu\Bundle\Sales\OrderBundle\Order\Exception\MissingOrderAttributeException;
use Sulu\Bundle\Sales\OrderBundle\Order\Exception\OrderDependencyNotFoundException;
use Sulu\Bundle\Sales\OrderBundle\Order\Exception\OrderNotFoundException;
use Sulu\Component\Rest\ListBuilder\Doctrine\FieldDescriptor\DoctrineConcatenationFieldDescriptor;
use Sulu\Component\Rest\ListBuilder\Doctrine\FieldDescriptor\DoctrineFieldDescriptor;
use Sulu\Bundle\Sales\OrderBundle\Api\Order;
use Sulu\Component\Rest\ListBuilder\Doctrine\FieldDescriptor\DoctrineJoinDescriptor;
use Sulu\Component\Security\UserRepositoryInterface;
use DateTime;

class ItemManager
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
     * @var OrderRepository
     */
    private $orderRepository;

    /**
     * @var UserRepositoryInterface
     */
    private $userRepository;

    /**
     * @var ContactRepository
     */
    private $contactRepository;

    /**
     * Describes the fields, which are handled by this controller
     * @var DoctrineFieldDescriptor[]
     */
    private $fieldDescriptors = array();

    public function __construct(
        ObjectManager $em,
        OrderRepository $orderRepository,
        UserRepositoryInterface $userRepository
    )
    {
        $this->orderRepository = $orderRepository;
        $this->userRepository = $userRepository;
        $this->em = $em;
    }

    /**
     * @param array $data
     * @param $locale
     * @param $userId
     * @param null $id
     * @return null|Order|\Sulu\Bundle\Sales\OrderBundle\Entity\Order
     * @throws Exception\OrderNotFoundException
     * @throws Exception\MissingOrderAttributeException
     * @throws Exception\OrderDependencyNotFoundException
     */
    public function save(array $data, $locale, $userId, $id = null)
    {
//        if ($id) {
//            $order = $this->findByIdAndLocale($id, $locale);
//
//            if (!$order) {
//                throw new OrderNotFoundException($id);
//            }
//        } else {
//            $order = new Order(new OrderEntity(), $locale);
//        }
//
//        // check for data
//        $this->checkRequiredData($data, $id === null);
//
//        $user = $this->userRepository->findUserById($userId);
//
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
//        $contact = $this->addContactRelation($data, 'contact', function($contact) use ($order) {
//            $order->setContact($contact);
//        });
//        // add contact
//        $this->addContactRelation($data, 'responsibleContact', function($contact) use ($order) {
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
//        $this->setOrderAddress($order->getDeliveryAddress(), $data['deliveryAddress']['id'], $contact, $account);
//        $this->setOrderAddress($order->getInvoiceAddress(), $data['paymentAddress']['id'], $contact, $account);
//
//        // handle items
//        $this->handleItems($data, $order);
//
//        $order->setChanged(new DateTime());
//        $order->setChanger($user);
//
//        $this->em->flush();
//
//        return $order;
    }

    /**
     * deletes an order
     * @param $id
     * @throws Exception\OrderNotFoundException
     */
    public function delete($id)
    {
//        // TODO: move order to an archive instead of remove it from database
//        $order= $this->orderRepository->findById($id);
//
//        if (!$order) {
//            throw new OrderNotFoundException($id);
//        }
//
//        $this->em->remove($order);
//        $this->em->flush();
    }

    /**
     * Finds an order by id and locale
     * @param $id
     * @param $locale
     * @return null|Order
     */
    public function findByIdAndLocale($id, $locale)
    {
//        $order = $this->orderRepository->findByIdAndLocale($id, $locale);
//
//        if ($order) {
//            return new Order($order, $locale);
//        } else {
//            return null;
//        }
    }

    /**
     * @param $locale
     * @param array $filter
     * @return mixed
     */
    public function findAllByLocale($locale, $filter = array())
    {
//        if (empty($filter)) {
//            $order = $this->orderRepository->findAllByLocale($locale);
//        } else {
//            $order = $this->orderRepository->findByLocaleAndFilter($locale, $filter);
//        }
//
//        array_walk(
//            $order,
//            function (&$order) use ($locale) {
//                $order = new Order($order, $locale);
//            }
//        );
//
//        return $order;
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
}
