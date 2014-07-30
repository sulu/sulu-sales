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
use Doctrine\ORM\Tools\SchemaTool;

use Sulu\Bundle\ContactBundle\Entity\Account;
use Sulu\Bundle\ContactBundle\Entity\Address;
use Sulu\Bundle\ContactBundle\Entity\AddressType;
use Sulu\Bundle\ContactBundle\Entity\Contact;
use Sulu\Bundle\ContactBundle\Entity\Country;
use Sulu\Bundle\ContactBundle\Entity\Phone;
use Sulu\Bundle\ContactBundle\Entity\PhoneType;
use Sulu\Bundle\ProductBundle\Entity\Product;
use Sulu\Bundle\ProductBundle\Entity\ProductTranslation;
use Sulu\Bundle\ProductBundle\Entity\Status;
use Sulu\Bundle\ProductBundle\Entity\StatusTranslation;
use Sulu\Bundle\ProductBundle\Entity\Type;
use Sulu\Bundle\ProductBundle\Entity\TypeTranslation;

use Sulu\Bundle\Sales\CoreBundle\Entity\Item;
use Sulu\Bundle\Sales\CoreBundle\Entity\ItemStatus;
use Sulu\Bundle\Sales\CoreBundle\Entity\ItemStatusTranslation;
use Sulu\Bundle\Sales\OrderBundle\Entity\Order;
use Sulu\Bundle\Sales\OrderBundle\Entity\OrderAddress;
use Sulu\Bundle\Sales\OrderBundle\Entity\OrderStatus;
use Sulu\Bundle\Sales\OrderBundle\Entity\OrderStatusTranslation;
use Sulu\Bundle\TestBundle\Entity\TestUser;
use Sulu\Bundle\TestBundle\Testing\DatabaseTestCase;
use Symfony\Component\HttpKernel\Client;


class OrderControllerTest extends DatabaseTestCase
{
    /**
     * @var array
     */
    protected static $entities;

    /**
     * @var Client
     */
    private $client;

    /**
     * @var TestUser
     */
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
     * @var Contact
     */
    private $contact;
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
     * @var OrderStatusTranslation
     */
    private $orderStatusTranslation;
    /**
     * @var Item
     */
    private $item;
    /**
     * @var ItemStatus
     */
    private $itemStatus;
    /**
     * @var ItemStatusTranslation
     */
    private $itemStatusTranslation;
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
     * @var Phone
     */
    private $phone;

    public function setUp()
    {
        $this->setUpTestUser();
        $this->setUpClient();
        $this->setUpSchema();
        $this->setUpTestData();
    }

    private function setUpTestUser()
    {
        $this->testUser = new TestUser();
        $this->testUser->setUsername('test');
        $this->testUser->setPassword('test');
        $this->testUser->setLocale('en');
    }

    private function setUpClient()
    {
        $this->client = static::createClient(
            array(),
            array(
                'PHP_AUTH_USER' => $this->testUser->getUsername(),
                'PHP_AUTH_PW' => $this->testUser->getPassword()
            )
        );
    }

    private function setUpSchema()
    {
        self::$tool = new SchemaTool(self::$em);

        self::$entities = array(
            self::$em->getClassMetadata('Sulu\Bundle\TestBundle\Entity\TestUser'),
            // SalesOrderBundle
            self::$em->getClassMetadata('Sulu\Bundle\Sales\OrderBundle\Entity\Order'),
            self::$em->getClassMetadata('Sulu\Bundle\Sales\OrderBundle\Entity\OrderAddress'),
            self::$em->getClassMetadata('Sulu\Bundle\Sales\OrderBundle\Entity\OrderStatus'),
            self::$em->getClassMetadata('Sulu\Bundle\Sales\OrderBundle\Entity\OrderStatusTranslation'),
            // SalesCoreBundle
            self::$em->getClassMetadata('Sulu\Bundle\Sales\CoreBundle\Entity\Item'),
            self::$em->getClassMetadata('Sulu\Bundle\Sales\CoreBundle\Entity\ItemAttribute'),
            self::$em->getClassMetadata('Sulu\Bundle\Sales\CoreBundle\Entity\ItemStatus'),
            self::$em->getClassMetadata('Sulu\Bundle\Sales\CoreBundle\Entity\ItemStatusTranslation'),
            // ProductBundle
            self::$em->getClassMetadata('Sulu\Bundle\ProductBundle\Entity\Product'),
            self::$em->getClassMetadata('Sulu\Bundle\ProductBundle\Entity\DeliveryStatus'),
            self::$em->getClassMetadata('Sulu\Bundle\ProductBundle\Entity\ProductPrice'),
            self::$em->getClassMetadata('Sulu\Bundle\ProductBundle\Entity\Type'),
            self::$em->getClassMetadata('Sulu\Bundle\ProductBundle\Entity\TypeTranslation'),
            self::$em->getClassMetadata('Sulu\Bundle\ProductBundle\Entity\Status'),
            self::$em->getClassMetadata('Sulu\Bundle\ProductBundle\Entity\StatusTranslation'),
            self::$em->getClassMetadata('Sulu\Bundle\ProductBundle\Entity\AttributeSet'),
            self::$em->getClassMetadata('Sulu\Bundle\ProductBundle\Entity\AttributeSetTranslation'),
            self::$em->getClassMetadata('Sulu\Bundle\ProductBundle\Entity\Attribute'),
            self::$em->getClassMetadata('Sulu\Bundle\ProductBundle\Entity\AttributeTranslation'),
            self::$em->getClassMetadata('Sulu\Bundle\ProductBundle\Entity\ProductTranslation'),
            self::$em->getClassMetadata('Sulu\Bundle\ProductBundle\Entity\ProductAttribute'),
            self::$em->getClassMetadata('Sulu\Bundle\ProductBundle\Entity\Addon'),
            // ContactBundle
            self::$em->getClassMetadata('Sulu\Bundle\ContactBundle\Entity\Account'),
            self::$em->getClassMetadata('Sulu\Bundle\ContactBundle\Entity\AccountContact'),
            self::$em->getClassMetadata('Sulu\Bundle\ContactBundle\Entity\AccountAddress'),
            self::$em->getClassMetadata('Sulu\Bundle\ContactBundle\Entity\AccountCategory'),
            self::$em->getClassMetadata('Sulu\Bundle\ContactBundle\Entity\Activity'),
            self::$em->getClassMetadata('Sulu\Bundle\ContactBundle\Entity\ActivityStatus'),
            self::$em->getClassMetadata('Sulu\Bundle\ContactBundle\Entity\Address'),
            self::$em->getClassMetadata('Sulu\Bundle\ContactBundle\Entity\AddressType'),
            self::$em->getClassMetadata('Sulu\Bundle\ContactBundle\Entity\BankAccount'),
            self::$em->getClassMetadata('Sulu\Bundle\ContactBundle\Entity\Contact'),
            self::$em->getClassMetadata('Sulu\Bundle\ContactBundle\Entity\ContactLocale'),
            self::$em->getClassMetadata('Sulu\Bundle\ContactBundle\Entity\Country'),
            self::$em->getClassMetadata('Sulu\Bundle\ContactBundle\Entity\ContactAddress'),
            self::$em->getClassMetadata('Sulu\Bundle\ContactBundle\Entity\Email'),
            self::$em->getClassMetadata('Sulu\Bundle\ContactBundle\Entity\EmailType'),
            self::$em->getClassMetadata('Sulu\Bundle\ContactBundle\Entity\Note'),
            self::$em->getClassMetadata('Sulu\Bundle\ContactBundle\Entity\Fax'),
            self::$em->getClassMetadata('Sulu\Bundle\ContactBundle\Entity\FaxType'),
            self::$em->getClassMetadata('Sulu\Bundle\ContactBundle\Entity\Phone'),
            self::$em->getClassMetadata('Sulu\Bundle\ContactBundle\Entity\PhoneType'),
            self::$em->getClassMetadata('Sulu\Bundle\ContactBundle\Entity\Url'),
            self::$em->getClassMetadata('Sulu\Bundle\ContactBundle\Entity\UrlType'),
            self::$em->getClassMetadata('Sulu\Bundle\TagBundle\Entity\Tag'),
            self::$em->getClassMetadata('Sulu\Bundle\ContactBundle\Entity\TermsOfPayment'),
            self::$em->getClassMetadata('Sulu\Bundle\ContactBundle\Entity\TermsOfDelivery'),


        );

        self::$tool->dropSchema(self::$entities);
        self::$tool->createSchema(self::$entities);
    }

    private function setUpTestData()
    {
//        // account
//        $this->account = new Account();
//        $this->account->setName('Company');
//        $this->account->setCreated(new DateTime());
//        $this->account->setChanged(new DateTime());
//        $this->account->setType(Account::TYPE_BASIC);
//        $this->account->setDisabled(0);

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
        $this->address->setPostboxNumber('Box2');
        $this->address->setAddressType($addressType);

        // phone
        $phoneType = new PhoneType();
        $phoneType->setName('Business');
        $this->phone = new Phone();
        $this->phone->setPhone('+43 123 / 456 789');
        $this->phone->setPhoneType($phoneType);

        // contact
        $this->contact = new Contact();
        $this->contact->setFirstName('John');
        $this->contact->setLastName('Doe');
        $this->contact->setTitle('Dr');
        $this->contact->setCreated(new DateTime());
        $this->contact->setChanged(new DateTime());

        // order status
        $this->orderStatus = new OrderStatus();
        $this->orderStatusTranslation = new OrderStatusTranslation();
        $this->orderStatusTranslation->setName('English-Order-Status-1');
        $this->orderStatusTranslation->setLocale('en');
        $this->orderStatusTranslation->setStatus($this->orderStatus);

        // order address
        $this->orderAddressDelivery = new OrderAddress();
        $this->orderAddressDelivery->setFirstName($this->contact->getFirstName());
        $this->orderAddressDelivery->setLastName($this->contact->getLastName());
        $this->orderAddressDelivery->setTitle($this->contact->getTitle());
        $this->orderAddressDelivery->setStreet($this->address->getStreet());
        $this->orderAddressDelivery->setNumber($this->address->getNumber());
        $this->orderAddressDelivery->setAddition($this->address->getAddition());
        $this->orderAddressDelivery->setCity($this->address->getCity());
        $this->orderAddressDelivery->setZip($this->address->getZip());
        $this->orderAddressDelivery->setState($this->address->getState());
        $this->orderAddressDelivery->setCountry($this->address->getCountry()->getName());
        $this->orderAddressDelivery->setBox($this->address->getPostboxNumber());
//        $this->orderAddressDelivery->setAccountName($this->account->getName());
//        $this->orderAddressDelivery->setUid($this->account->getUid());
        $this->orderAddressDelivery->setPhone($this->phone->getPhone());
        $this->orderAddressDelivery->setPhoneMobile('+43 123 / 456');
        $this->orderAddressDelivery->setAddress($this->address);
        // clone address for invoice
        $this->orderAddressInvoice = clone $this->orderAddressDelivery;

        // order
        $this->order = new Order();
        $this->order->setNumber('1234');
        $this->order->setCommission('commission');
        $this->order->setCostCentre('cost-centre');
        $this->order->setCurrency('EUR');
        $this->order->setTermsOfDelivery('10kg minimum');
        $this->order->setTermsOfPayment('10% off');
        $this->order->setCreated(new DateTime());
        $this->order->setChanged(new DateTime());
        $this->order->setDesiredDeliveryDate(new DateTime('2015-01-01'));
        $this->order->setSessionId('abcd1234');
        $this->order->setTaxfree(true);
        $this->order->setContact($this->contact);
        $this->order->setAccount($this->account);
        $this->order->setStatus($this->orderStatus);
        $this->order->setDeliveryAddress($this->orderAddressDelivery);
        $this->order->setInvoiceAddress($this->orderAddressInvoice);

        $order2 = clone $this->order;
        $order2->setNumber('12345');
        $order2->setDeliveryAddress(null);
        $order2->setInvoiceAddress(null);

        // product type
        $productType = new Type();
        $productTypeTranslation = new TypeTranslation();
        $productTypeTranslation->setLocale('en');
        $productTypeTranslation->setName('EnglishProductType-1');
        $productTypeTranslation->setType($productType);
        // product status
        $productStatus = new Status();
        $productStatusTranslation = new StatusTranslation();
        $productStatusTranslation->setLocale('en');
        $productStatusTranslation->setName('EnglishProductStatus-1');
        $productStatusTranslation->setStatus($productStatus);
        // product
        $this->product = new Product();
        $this->product->setCode('EnglishProductCode-1');
        $this->product->setNumber('ProductNumber-1');
        $this->product->setManufacturer('EnglishManufacturer-1');
        $this->product->setType($productType);
        $this->product->setStatus($productStatus);
        $this->product->setCreated(new DateTime());
        $this->product->setChanged(new DateTime());
        // product translation
        $productTranslation = new ProductTranslation();
        $productTranslation->setProduct($this->product);
        $productTranslation->setLocale('en');
        $productTranslation->setName('EnglishProductTranslationName-1');
        $productTranslation->setShortDescription('EnglishProductShortDescription-1');
        $productTranslation->setLongDescription('EnglishProductLongDescription-1');
        $this->product->addTranslation($productTranslation);

        // Item Status
        $this->itemStatus = new ItemStatus();
        $this->itemStatusTranslation = new ItemStatusTranslation();
        $this->itemStatusTranslation->setName('English-Item-Status-1');
        $this->itemStatusTranslation->setLocale('en');
        $this->itemStatusTranslation->setStatus($this->itemStatus);
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
        $this->item->setStatus($this->itemStatus);

        $this->order->addItem($this->item);

//        self::$em->persist($this->account);
        self::$em->persist($country);
        self::$em->persist($addressType);
        self::$em->persist($this->address);
        self::$em->persist($phoneType);
        self::$em->persist($this->phone);
        self::$em->persist($this->contact);
        self::$em->persist($this->order);
        self::$em->persist($order2);
        self::$em->persist($this->orderStatus);
        self::$em->persist($this->orderAddressDelivery);
        self::$em->persist($this->orderAddressInvoice);
        self::$em->persist($this->orderStatusTranslation);
        self::$em->persist($this->item);
        self::$em->persist($this->itemStatus);
        self::$em->persist($this->itemStatusTranslation);
        self::$em->persist($this->product);
        self::$em->persist($productTranslation);
        self::$em->persist($productType);
        self::$em->persist($productTypeTranslation);
        self::$em->persist($productStatus);
        self::$em->persist($productStatusTranslation);
        self::$em->flush();
    }

    public function tearDown()
    {
        parent::tearDown();
        self::$tool->dropSchema(self::$entities);
    }

    public function testGetById()
    {
        $this->client->request('GET', '/api/orders/1');
        $response = json_decode($this->client->getResponse()->getContent());

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertEquals('1234', $response->number);
        $this->assertEquals('EUR', $response->currency);
        $this->assertEquals('abcd1234', $response->sessionId);
        $this->assertEquals('cost-centre', $response->costCentre);
        $this->assertEquals((new DateTime('2015-01-01'))->getTimestamp(), (new DateTime($response->desiredDeliveryDate))->getTimestamp());
        $this->assertEquals(true, $response->taxfree);
        $this->assertEquals('commission', $response->commission);
        $this->assertEquals('10kg minimum', $response->termsOfDelivery);
        $this->assertEquals('10% off', $response->termsOfPayment);
        // order status
        $this->assertEquals('English-Order-Status-1', $response->status->status);
        $this->assertEquals($this->orderStatus->getId(), $response->status->id);
        // contact
        $this->assertEquals($this->contact->getId(), $response->contact->id);
        $this->assertEquals($this->contact->getFirstName(), $response->contact->firstName);
        $this->assertEquals($this->contact->getLastName(), $response->contact->lastName);
        // order Address delivery
        $this->assertEquals($this->orderAddressDelivery->getId(), $response->deliveryAddress->id);
        $this->assertEquals('John', $response->deliveryAddress->firstName);
        $this->assertEquals('Doe', $response->deliveryAddress->lastName);
        $this->assertEquals('Account1', $response->deliveryAddress->accountName);
        $this->assertEquals('Dr', $response->deliveryAddress->title);
        $this->assertEquals('Sample-Street', $response->deliveryAddress->street);
        $this->assertEquals('Entrance 2', $response->deliveryAddress->addition);
        $this->assertEquals('12', $response->deliveryAddress->number);
        $this->assertEquals('Sample-City', $response->deliveryAddress->city);
        $this->assertEquals('12345', $response->deliveryAddress->zip);
        $this->assertEquals('State', $response->deliveryAddress->state);
        $this->assertEquals('Country', $response->deliveryAddress->country);
        $this->assertEquals('Box2', $response->deliveryAddress->box);
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
        // item status
        $this->assertEquals($this->item->getStatus()->getId(), $item->status->id);
        $this->assertEquals('English-Item-Status-1', $item->status->status);
        // item product
        $this->assertEquals($this->item->getProduct()->getId(), $item->product->id);
    }

    public function testGetAll()
    {
        $this->client->request('GET', '/api/orders');
        $response = json_decode($this->client->getResponse()->getContent());
        $items = $response->_embedded->orders;

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertEquals(2, count($items));

        // TODO: extend test
        $item = $items[0];
        $this->assertEquals('1234', $item->number);

        $item = $items[1];
        $this->assertEquals('12345', $item->number);
    }

    public function testGetAllFlat()
    {
        $this->client->request('GET', '/api/orders?flat=true');
        $response = json_decode($this->client->getResponse()->getContent());
        $items = $response->_embedded->orders;

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertEquals(2, count($items));

        // TODO: extend test
        $item = $items[0];
        $this->assertEquals('1234', $item->number);

        $item = $items[1];
        $this->assertEquals('12345', $item->number);
    }

    public function testPut()
    {
        $this->client->request(
            'PUT',
            '/api/orders/1',
            array(
                'number' => 'EvilNumber',
                'supplierName' => 'Mr.Supplier',
                'contact' => array(
                    'id' => 1
                ),
                'invoiceAddress' => 1,
                'deliveryAddress' => 1,

            )
        );
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $this->client->request('GET', '/api/order/1');
        $response = json_decode($this->client->getResponse()->getContent());
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertEquals('EnglishProductTranslationNameNew-1', $response->name);
        $this->assertEquals('EvilCode', $response->code);
        $this->assertEquals('EvilNumber', $response->number);
        $this->assertEquals('EvilKnievel', $response->manufacturer);
    }

    public function testPutNotExisting()
    {
//        $this->client->request('PUT', '/api/products/666', array('code' => 'MissingProduct'));
//        $response = json_decode($this->client->getResponse()->getContent());
//
//        $this->assertEquals(404, $this->client->getResponse()->getStatusCode());
//
//        $this->assertEquals(
//            'Entity with the type "SuluProductBundle:Product" and the id "666" not found.',
//            $response->message
//        );
    }

    public function testPutMissingNumber()
    {
    }
    public function testPost($testParent = false)
    {
//        $data = array(
//            'code' => 'CODE:0815',
//            'name' => 'English Product',
//            'shortDescription' => 'This is an english product.',
//            'longDescription' => 'Indeed, it\'s a real english product.',
//            'number' => 'NUMBER:0815',
//            'manufacturer' => $this->product1->getManufacturer(),
//            'manufacturerCountry' => array(
//                'id' => $this->product1->getManufacturerCountry()
//            ),
//            'cost' => 666.66,
//            'priceInfo' => 'Preis Info',
//            'status' => array(
//                'id' => $this->productStatus1->getId()
//            ),
//            'type' => array(
//                'id' => $this->type1->getId()
//            ),
//            'attributeSet' => array(
//                'id' => $this->attributeSet1->getId()
//            )
//        );
//
//        if ($testParent) {
//            $data['parent']['id'] = $this->product2->getId();
//        }
//
//        $this->client->request('POST', '/api/products', $data);
//        $response = json_decode($this->client->getResponse()->getContent());
//
//        $this->client->request('GET', '/api/products/' . $response->id);
//        $response = json_decode($this->client->getResponse()->getContent());
//        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
//
//        $this->assertEquals('English Product', $response->name);
//        $this->assertEquals('This is an english product.', $response->shortDescription);
//        $this->assertEquals('Indeed, it\'s a real english product.', $response->longDescription);
//
//        $this->assertEquals('CODE:0815', $response->code);
//        $this->assertEquals('NUMBER:0815', $response->number);
//        $this->assertEquals(666.66, $response->cost);
//        $this->assertEquals('Preis Info', $response->priceInfo);
//        $this->assertEquals($this->product1->getManufacturer(), $response->manufacturer);
//
//        $this->assertEquals('EnglishProductStatus-1', $response->status->name);
//
//        $this->assertEquals('EnglishProductType-1', $response->type->name);
//
//        $this->assertEquals($this->attributeSet1->getId(), $response->attributeSet->id);
//        $this->assertEquals('EnglishTemplate-1', $response->attributeSet->name);
//
//        if ($testParent) {
//            $this->assertEquals($this->product2->getId(), $response->parent->id);
//        }
    }

    public function testDeleteById()
    {
//        $this->client->request('DELETE', '/api/products/1');
//        $this->assertEquals('204', $this->client->getResponse()->getStatusCode());
//
//        $this->client->request('GET', '/api/products/1');
//        $this->assertEquals('404', $this->client->getResponse()->getStatusCode());
    }
}
