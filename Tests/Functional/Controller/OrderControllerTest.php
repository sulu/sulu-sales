<?php
/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\ProductBundle\Tests\Functional\Controller;

use DateTime;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaTool;

use Sulu\Bundle\ContactBundle\Entity\Account;
use Sulu\Bundle\ContactBundle\Entity\Address;
use Sulu\Bundle\ContactBundle\Entity\AddressType;
use Sulu\Bundle\ContactBundle\Entity\Contact;
use Sulu\Bundle\ContactBundle\Entity\ContactTitle;
use Sulu\Bundle\ContactBundle\Entity\Country;
use Sulu\Bundle\ContactBundle\Entity\Phone;
use Sulu\Bundle\ContactBundle\Entity\PhoneType;
use Sulu\Bundle\ContactBundle\Entity\TermsOfDelivery;
use Sulu\Bundle\ContactBundle\Entity\TermsOfPayment;
use Sulu\Bundle\ProductBundle\Entity\Product;
use Sulu\Bundle\ProductBundle\Entity\ProductTranslation;
use Sulu\Bundle\ProductBundle\Entity\Status;
use Sulu\Bundle\ProductBundle\Entity\StatusTranslation;
use Sulu\Bundle\ProductBundle\Entity\Type;
use Sulu\Bundle\ProductBundle\Entity\TypeTranslation;

use Sulu\Bundle\Sales\CoreBundle\Entity\Item;
use Sulu\Bundle\Sales\OrderBundle\Entity\Order;
use Sulu\Bundle\Sales\OrderBundle\Entity\OrderAddress;
use Sulu\Bundle\Sales\OrderBundle\Entity\OrderStatus;
use Sulu\Bundle\Sales\OrderBundle\Entity\OrderStatusTranslation;
use Sulu\Bundle\TestBundle\Entity\TestUser;
use Sulu\Bundle\TestBundle\Testing\SuluTestCase;
use Symfony\Component\HttpKernel\Client;


class OrderControllerTest extends SuluTestCase
{
    private $locale = 'en';

    private $testUser;
    /**
     * @var Account
     */
    private $account;
    /**
     * @var Address
     */
    private $address;
    /**
     * @var Address
     */
    private $address2;
    /**
     * @var Contact
     */
    private $contact;
    /**
     * @var Contact
     */
    private $contact2;
    /**
     * @var Order
     */
    private $order;
    /**
     * @var OrderAddress
     */
    private $orderAddressDelivery;
    /**
     * @var OrderAddress
     */
    private $orderAddressInvoice;
    /**
     * @var OrderStatus
     */
    private $orderStatus;
    /**
     * @var TermsOfDelivery
     */
    private $termsOfDelivery;
    /**
     * @var TermsOfPayment
     */
    private $termsOfPayment;
    /**
     * @var OrderStatusTranslation[]
     */
    private $orderStatusTranslations;
    /**
     * @var Item
     */
    private $item;
    /**
     * @var Product
     */
    private $product;
    /**
     * @var ProductType
     */
    private $productType;
    /**
     * @var ProductTypeTranslation
     */
    private $productTypeTranslation;
    /**
     * @var ProductTranslation
     */
    private $productTranslation;
    /**
     * @var Phone
     */
    private $phone;

    /**
     * @var EntityManager
     */
    protected $em;

    public function setUp()
    {
        parent::setUp();
        $this->em = $this->db('ORM')->getOm();
        $this->purgeDatabase();
        $this->setUpTestData();
    }

    private function setUpTestData()
    {
        // account
        $this->account = new Account();
         $this->account->setName('Company');
        $this->account->setCreated(new DateTime());
        $this->account->setChanged(new DateTime());
        $this->account->setType(Account::TYPE_BASIC);
        $this->account->setUid('uid-123');
        $this->account->setDisabled(0);

        $this->testUser = new TestUser();
        $this->testUser->setUsername('test');
        $this->testUser->setPassword('test');
        $this->testUser->setLocale('en');

        // country
        $country = new Country();
        $country->setName('Country');
        $country->setCode('co');
        // address type
        $addressType = new AddressType();
        $addressType->setName('Business');
        // address
        $this->address = new Address();
        $this->address->setStreet('Sample-Street');
        $this->address->setNumber('12');
        $this->address->setAddition('Entrance 2');
        $this->address->setCity('Sample-City');
        $this->address->setState('State');
        $this->address->setZip('12345');
        $this->address->setCountry($country);
        $this->address->setPostboxNumber('postboxNumber');
        $this->address->setPostboxPostcode('postboxPostcode');
        $this->address->setPostboxCity('postboxCity');
        $this->address->setAddressType($addressType);
        // address
        $this->address2 = new Address();
        $this->address2->setStreet('Street');
        $this->address2->setNumber('2');
        $this->address2->setCity('Utopia');
        $this->address2->setZip('1');
        $this->address2->setCountry($country);
        $this->address2->setAddressType($addressType);

        // phone
        $phoneType = new PhoneType();
        $phoneType->setName('Business');
        $this->phone = new Phone();
        $this->phone->setPhone('+43 123 / 456 789');
        $this->phone->setPhoneType($phoneType);

        // title
        $title = new ContactTitle();
        $title->setTitle('Dr');
        // contact
        $this->contact = new Contact();
        $this->contact->setFirstName('John');
        $this->contact->setLastName('Doe');
        $this->contact->setTitle($title);
        $this->contact->setCreated(new DateTime());
        $this->contact->setChanged(new DateTime());
        // contact
        $this->contact2 = new Contact();
        $this->contact2->setFirstName('Johanna');
        $this->contact2->setLastName('Dole');
        $this->contact2->setCreated(new DateTime());
        $this->contact2->setChanged(new DateTime());

        // order status
        // created
        $status = new OrderStatus();
        $status->setId(OrderStatus::STATUS_CREATED);
        $this->createStatusTranslation($this->em, $status, 'Created', 'en');
        $this->createStatusTranslation($this->em, $status, 'Erfasst', 'de');
        $this->em->persist($status);
        $this->orderStatus = $status;
        // cart
        $status = new OrderStatus();
        $status->setId(OrderStatus::STATUS_IN_CART);
        $this->createStatusTranslation($this->em, $status, 'In Cart', 'en');
        $this->createStatusTranslation($this->em, $status, 'Im Warenkorb', 'de');
        $this->em->persist($status);
        // confirmed
        $status = new OrderStatus();
        $status->setId(OrderStatus::STATUS_CONFIRMED);
        $this->createStatusTranslation($this->em, $status, 'Order confirmed', 'en');
        $this->createStatusTranslation($this->em, $status, 'Auftragsbestätigung erstellt', 'de');
        $this->em->persist($status);
        // confirmed
        $status = new OrderStatus();
        $status->setId(OrderStatus::STATUS_CLOSED_MANUALLY);
        $this->createStatusTranslation($this->em, $status, 'Order closed', 'en');
        $this->createStatusTranslation($this->em, $status, 'Auftragsbestätigung erstellt', 'de');
        $this->em->persist($status);


        // order address
        $this->orderAddressDelivery = new OrderAddress();
        $this->orderAddressDelivery->setFirstName($this->contact->getFirstName());
        $this->orderAddressDelivery->setLastName($this->contact->getLastName());
        $this->orderAddressDelivery->setTitle($this->contact->getTitle()->getTitle());
        $this->orderAddressDelivery->setStreet($this->address->getStreet());
        $this->orderAddressDelivery->setNumber($this->address->getNumber());
        $this->orderAddressDelivery->setAddition($this->address->getAddition());
        $this->orderAddressDelivery->setCity($this->address->getCity());
        $this->orderAddressDelivery->setZip($this->address->getZip());
        $this->orderAddressDelivery->setState($this->address->getState());
        $this->orderAddressDelivery->setCountry($this->address->getCountry()->getName());
        $this->orderAddressDelivery->setPostboxNumber($this->address->getPostboxNumber());
        $this->orderAddressDelivery->setPostboxPostcode($this->address->getPostboxPostcode());
        $this->orderAddressDelivery->setPostboxCity($this->address->getPostboxCity());
        $this->orderAddressDelivery->setAccountName($this->account->getName());
        $this->orderAddressDelivery->setUid($this->account->getUid());
        $this->orderAddressDelivery->setPhone($this->phone->getPhone());
        $this->orderAddressDelivery->setPhoneMobile('+43 123 / 456');

        // clone address for invoice
        $this->orderAddressInvoice = clone $this->orderAddressDelivery;
        $this->orderAddressInvoice = clone $this->orderAddressDelivery;

        $this->termsOfDelivery = new TermsOfDelivery();
        $this->termsOfDelivery->setTerms('10kg minimum');
        $this->termsOfPayment = new TermsOfPayment();
        $this->termsOfPayment->setTerms('10% off');

        // order
        $this->order = new Order();
        $this->order->setNumber('1234');
        $this->order->setCommission('commission');
        $this->order->setCostCentre('cost-centre');
        $this->order->setCustomerName($this->contact->getFullName());
        $this->order->setCurrency('EUR');
        $this->order->setTermsOfDelivery($this->termsOfDelivery);
        $this->order->setTermsOfDeliveryContent($this->termsOfDelivery->getTerms());
        $this->order->setTermsOfPayment($this->termsOfPayment);
        $this->order->setTermsOfPaymentContent($this->termsOfPayment->getTerms());
        $this->order->setCreated(new DateTime());
        $this->order->setChanged(new DateTime());
        $this->order->setDesiredDeliveryDate(new DateTime('2015-01-01'));
        $this->order->setSessionId('abcd1234');
        $this->order->setTaxfree(true);
        $this->order->setContact($this->contact);
        $this->order->setAccount($this->account);
        $this->order->setStatus($this->orderStatus);
        $this->order->setBitmaskStatus($this->orderStatus->getId());
        $this->order->setDeliveryAddress($this->orderAddressDelivery);
        $this->order->setInvoiceAddress($this->orderAddressInvoice);

        $order2 = clone $this->order;
        $order2->setNumber('12345');
        $order2->setDeliveryAddress(null);
        $order2->setInvoiceAddress(null);

        // product type
        $productType = new Type();
        $productTypeTranslation = new TypeTranslation();
        $productTypeTranslation->setLocale($this->locale);
        $productTypeTranslation->setName('EnglishProductType-1');
        $productTypeTranslation->setType($productType);
        // product status
        $productStatus = new Status();
        $productStatusTranslation = new StatusTranslation();
        $productStatusTranslation->setLocale($this->locale);
        $productStatusTranslation->setName('EnglishProductStatus-1');
        $productStatusTranslation->setStatus($productStatus);
        // product
        $this->product = new Product();
        $this->product->setNumber('ProductNumber-1');
        $this->product->setManufacturer('EnglishManufacturer-1');
        $this->product->setType($productType);
        $this->product->setStatus($productStatus);
        $this->product->setCreated(new DateTime());
        $this->product->setChanged(new DateTime());
        // product translation
        $this->productTranslation = new ProductTranslation();
        $this->productTranslation->setProduct($this->product);
        $this->productTranslation->setLocale($this->locale);
        $this->productTranslation->setName('EnglishProductTranslationName-1');
        $this->productTranslation->setShortDescription('EnglishProductShortDescription-1');
        $this->productTranslation->setLongDescription('EnglishProductLongDescription-1');
        $this->product->addTranslation($this->productTranslation);

        // Item
        $this->item = new Item();
        $this->item->setName('Product1');
        $this->item->setNumber('123');
        $this->item->setQuantity(2);
        $this->item->setQuantityUnit('Pcs');
        $this->item->setUseProductsPrice(true);
        $this->item->setTax(20);
        $this->item->setPrice(125.99);
        $this->item->setDiscount(10);
        $this->item->setDescription('This is a description');
        $this->item->setWeight(15.8);
        $this->item->setWidth(5);
        $this->item->setHeight(6);
        $this->item->setLength(7);
        $this->item->setSupplierName('Supplier');
        $this->item->setCreated(new DateTime());
        $this->item->setChanged(new DateTime());
        $this->item->setProduct($this->product);

        $this->order->addItem($this->item);

        $this->em->persist($this->account);
        $this->em->persist($title);
        $this->em->persist($this->testUser);
        $this->em->persist($country);
        $this->em->persist($this->termsOfPayment);
        $this->em->persist($this->termsOfDelivery);
        $this->em->persist($country);
        $this->em->persist($addressType);
        $this->em->persist($this->address);
        $this->em->persist($this->address2);
        $this->em->persist($phoneType);
        $this->em->persist($this->phone);
        $this->em->persist($this->contact);
        $this->em->persist($this->contact2);
        $this->em->persist($this->order);
        $this->em->persist($order2);
        $this->em->persist($this->orderAddressDelivery);
        $this->em->persist($this->orderAddressInvoice);
        $this->em->persist($this->item);
        $this->em->persist($this->product);
        $this->em->persist($this->productTranslation);
        $this->em->persist($productType);
        $this->em->persist($productTypeTranslation);
        $this->em->persist($productStatus);
        $this->em->persist($productStatusTranslation);
        $this->em->flush();
    }

    private function createStatusTranslation($manager, $status, $translation, $locale) {
        $statusTranslation = new \Sulu\Bundle\Sales\OrderBundle\Entity\OrderStatusTranslation();
        $statusTranslation->setName($translation);
        $statusTranslation->setLocale($locale);
        $statusTranslation->setStatus($status);
        $manager->persist($statusTranslation);
        return $statusTranslation;
    }

    public function tearDown()
    {
        parent::tearDown();
        //self::$tool->dropSchema(self::$entities);
    }

    public function testGetById()
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', '/api/orders/' . $this->order->getId());
        $response = json_decode($client->getResponse()->getContent());

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals('1234', $response->number);
        $this->assertEquals('EUR', $response->currency);
        $this->assertEquals('abcd1234', $response->sessionId);
        $this->assertEquals('cost-centre', $response->costCentre);
        $this->assertEquals((new DateTime('2015-01-01'))->getTimestamp(), (new DateTime($response->desiredDeliveryDate))->getTimestamp());
        $this->assertEquals(true, $response->taxfree);
        $this->assertEquals('commission', $response->commission);
        $this->assertEquals('10kg minimum', $response->termsOfDeliveryContent);
        $this->assertEquals('10% off', $response->termsOfPaymentContent);
        // order status
        $this->assertEquals('Created', $response->status->status);
        $this->assertEquals($this->orderStatus->getId(), $response->status->id);
        // contact
        $this->assertEquals($this->contact->getId(), $response->contact->id);
        // order Address delivery
        $this->assertEquals($this->orderAddressDelivery->getId(), $response->deliveryAddress->id);
        $this->assertEquals('John', $response->deliveryAddress->firstName);
        $this->assertEquals('Doe', $response->deliveryAddress->lastName);
        $this->assertEquals('Company', $response->deliveryAddress->accountName);
        $this->assertEquals('Dr', $response->deliveryAddress->title);
        $this->assertEquals('Sample-Street', $response->deliveryAddress->street);
        $this->assertEquals('Entrance 2', $response->deliveryAddress->addition);
        $this->assertEquals('12', $response->deliveryAddress->number);
        $this->assertEquals('Sample-City', $response->deliveryAddress->city);
        $this->assertEquals('12345', $response->deliveryAddress->zip);
        $this->assertEquals('State', $response->deliveryAddress->state);
        $this->assertEquals('Country', $response->deliveryAddress->country);
        $this->assertEquals('postboxPostcode', $response->deliveryAddress->postboxPostcode);
        $this->assertEquals('postboxNumber', $response->deliveryAddress->postboxNumber);
        $this->assertEquals('postboxCity', $response->deliveryAddress->postboxCity);
        $this->assertEquals('uid-123', $response->deliveryAddress->uid);
        $this->assertEquals('+43 123 / 456 789', $response->deliveryAddress->phone);
        $this->assertEquals('+43 123 / 456', $response->deliveryAddress->phoneMobile);
        // order Address invoice
        $this->assertEquals($this->orderAddressDelivery->getId(), $response->deliveryAddress->id);
        $this->assertEquals('John', $response->deliveryAddress->firstName);

        // TODO: extend item tests
        // items
        $this->assertEquals(1, count($response->items));
        $item = $response->items[0];
        $this->assertEquals($this->item->getId(), $item->id);
        // item product
        $this->assertEquals($this->item->getProduct()->getId(), $item->product->id);
    }

    public function testGetAll()
    {
        $client = $this->createAuthenticatedClient();
        $client->request('GET', '/api/orders');
        $response = json_decode($client->getResponse()->getContent());
        $items = $response->_embedded->orders;

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals(2, count($items));

        // TODO: extend test
        $item = $items[0];
        $this->assertEquals('1234', $item->number);

        $item = $items[1];
        $this->assertEquals('12345', $item->number);
    }

    public function testGetAllFlat()
    {
        $client = $this->createAuthenticatedClient();
        $client->request('GET', '/api/orders?flat=true');
        $response = json_decode($client->getResponse()->getContent());
        $items = $response->_embedded->orders;

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals(2, count($items));

        // TODO: extend test
        $item = $items[0];
        $this->assertEquals('1234', $item->number);

        $item = $items[1];
        $this->assertEquals('12345', $item->number);
    }

    public function testPut()
    {
        $client = $this->createAuthenticatedClient();
        $client->request(
            'PUT',
            '/api/orders/' . $this->order->getId(),
            array(
                'orderNumber' => 'EvilNumber',
                'contact' => array(
                    'id' =>  $this->contact->getId()
                ),
                'account' => array(
                    'id' =>  $this->account->getId()
                ),
                'invoiceAddress' => array(
                    'street' => 'Sample-Street',
                    'number' => '12',
                    'addition' => 'Entrance 2',
                    'city' => 'Sample-City',
                    'state' => 'State',
                    'zip' => '12345',
                    'country' => 'Country',
                    'postboxNumber' => 'postboxNumber',
                    'postboxCity' => 'postboxCity',
                    'postboxPostcode' => 'postboxPostcode'
                ),
                'deliveryAddress' => array(
                    'street' => 'Street',
                    'number' => '2',
                    'city' => 'Utopia',
                    'zip' => '1',
                    'country' => 'Country'
                ),
            )
        );
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $client->request('GET', '/api/orders/' . $this->order->getId());
        $response = json_decode($client->getResponse()->getContent());
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals('EvilNumber', $response->orderNumber);

        $this->checkOrderAddress($response->invoiceAddress, $this->address, $this->contact2, $this->account);
        $this->checkOrderAddress($response->deliveryAddress, $this->address2, $this->contact2, $this->account);

    }

    public function testPutNotExisting()
    {
        $client = $this->createAuthenticatedClient();
        $client->request('PUT', '/api/orders/666', array('number' => '123'));
        $response = json_decode($client->getResponse()->getContent());

        $this->assertEquals(404, $client->getResponse()->getStatusCode());

        $this->assertEquals(
            'Entity with the type "SuluSalesOrderBundle:Order" and the id "666" not found.',
            $response->message
        );
    }

    public function testPost()
    {
        $data = array(
            'orderNumber' => 'NUMBER:0815',
            'supplierName' => $this->account->getName(),
            'account' => array(
                'id' => $this->account->getId()
            ),
            'contact' => array(
                'id' => $this->contact->getId()
            ),
            'invoiceAddress' => array(
                'street' => 'Sample-Street',
                'number' => '12',
                'addition' => 'Entrance 2',
                'city' => 'Sample-City',
                'state' => 'State',
                'zip' => '12345',
                'country' => 'Country',
                'postboxNumber' => 'postboxNumber',
                'postboxCity' => 'postboxCity',
                'postboxPostcode' => 'postboxPostcode'
            ),
            'deliveryAddress' => array(
                'street' => 'Street',
                'number' => '2',
                'city' => 'Utopia',
                'zip' => '1',
                'country' => 'Country'
            ),
            'termsOfDelivery' => array(
                'id' => $this->termsOfDelivery->getId()
            ),
            'termsOfPayment' => array(
                'id' => $this->termsOfPayment->getId()
            ),
        );
        $client = $this->createAuthenticatedClient();

        $client->request('POST', '/api/orders', $data);
        $response = json_decode($client->getResponse()->getContent());

        $client->request('GET', '/api/orders/' . $response->id);
        $response = json_decode($client->getResponse()->getContent());
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $this->assertEquals('NUMBER:0815', $response->orderNumber);
        $this->assertEquals('Created', $response->status->status);

        $this->checkOrderAddress($response->invoiceAddress, $this->address, $this->contact, $this->account);
        $this->checkOrderAddress($response->deliveryAddress, $this->address2, $this->contact, $this->account);
    }

    public function testPostItems()
    {
        $data = array(
            'orderNumber' => 'NUMBER:0815',
            'supplierName' => $this->account->getName(),
            'account' => array(
                'id' => $this->account->getId()
            ),
            'contact' => array(
                'id' => $this->contact->getId()
            ),
            'invoiceAddress' => array(
                'street' => 'Sample-Street',
                'number' => '12',
                'addition' => 'Entrance 2',
                'city' => 'Sample-City',
                'state' => 'State',
                'zip' => '12345',
                'country' => 'Country',
                'postboxNumber' => 'postboxNumber',
                'postboxCity' => 'postboxCity',
                'postboxPostcode' => 'postboxPostcode'
            ),
            'deliveryAddress' => array(
                'street' => 'Street',
                'number' => '2',
                'city' => 'Utopia',
                'zip' => '1',
                'country' => 'Country'
            ),
            'items' => array(
                array(
                    'name' => $this->productTranslation->getName(),
                    'number' => $this->product->getNumber(),
                    'quantity' => 2,
                    'quantityUnit' => 'pc',
                    'price' => 1000,
                    'discount' => 10,
                    'tax' => 20,
                    'description' => $this->productTranslation->getLongDescription(),
                    'useProductsPrice' => false,
                    'weight' => 12,
                    'width' => 13,
                    'height' => 14,
                    'length' => 15,
                    'supplierName' => 'supplier',
                    'product' => array(
                        'id' => $this->product->getId()
                    )
                )
            )
        );
        $client = $this->createAuthenticatedClient();

        $client->request('POST', '/api/orders', $data);
        $response = json_decode($client->getResponse()->getContent());

        $client->request('GET', '/api/orders/' . $response->id);
        $response = json_decode($client->getResponse()->getContent());
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testStatusChange()
    {
        $client = $this->createAuthenticatedClient();
        $client->request('POST', '/api/orders/' . $this->order->getId(), array(
            'action' => 'confirm'
        ));
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $response = json_decode($client->getResponse()->getContent());
        $this->assertEquals(1, $response->id);
        $this->assertEquals(OrderStatus::STATUS_CREATED | OrderStatus::STATUS_CONFIRMED, $response->bitmaskStatus);
        $this->assertEquals(OrderStatus::STATUS_CONFIRMED, $response->status->id);

        $client->request('POST', '/api/orders/' . $this->order->getId(), array(
            'action' => 'edit'
        ));
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $response = json_decode($client->getResponse()->getContent());
        $this->assertEquals(1, $response->id);
        $this->assertEquals(OrderStatus::STATUS_CREATED & ~OrderStatus::STATUS_CONFIRMED, $response->bitmaskStatus);
        $this->assertEquals(OrderStatus::STATUS_CREATED, $response->status->id);
    }

    public function testDeleteById()
    {
        $client = $this->createAuthenticatedClient();
        $client->request('DELETE', '/api/orders/' . $this->order->getId());
        $this->assertEquals('204', $client->getResponse()->getStatusCode());

        $client->request('GET', '/api/orders/' . $this->order->getId());
        $this->assertEquals('404', $client->getResponse()->getStatusCode());
    }

    /**
     * compares an order-address response with its origin entities
     */
    private function checkOrderAddress($orderAddress, Address $address, Contact $contact, Account $account = null) {
        // contact
        $this->assertEquals($contact->getFirstName(), $orderAddress->firstName);
        $this->assertEquals($contact->getLastName(), $orderAddress->lastName);
        if ($contact->getTitle() !== null) {
            $this->assertEquals($contact->getTitle()->getTitle(), $orderAddress->title);
        }

        // address
        $this->assertEqualsIfExists($address->getStreet(), $orderAddress, 'street');
        $this->assertEqualsIfExists($address->getAddition(), $orderAddress, 'addition');
        $this->assertEqualsIfExists($address->getNumber(), $orderAddress, 'number');
        $this->assertEqualsIfExists($address->getCity(), $orderAddress, 'city');
        $this->assertEqualsIfExists($address->getZip(), $orderAddress, 'zip');
        $this->assertEqualsIfExists($address->getCountry()->getName(), $orderAddress, 'country');
        $this->assertEqualsIfExists($address->getPostboxNumber(), $orderAddress, 'postboxNumber');
        $this->assertEqualsIfExists($address->getPostboxCity(), $orderAddress, 'postboxCity');
        $this->assertEqualsIfExists($address->getPostboxPostcode(), $orderAddress, 'postboxPostcode');

        // account
        if ($account) {
            $this->assertEqualsIfExists($account->getName(), $orderAddress, 'accountName');
            $this->assertEqualsIfExists($account->getUid(), $orderAddress, 'uid');
        }

        // TODO: check phone
//        $this->assertEquals('+43 123 / 456 789', $orderAddress->phone);
//        $this->assertEquals('+43 123 / 456', $orderAddress->phoneMobile);
    }

    /**
     * asserts equality if object's attribute exist
     */
    private function assertEqualsIfExists($firstValue, $secondObject, $value) {
        if ($firstValue !== null) {
            $this->assertEquals($firstValue, $secondObject->$value);
        }
    }
}
