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

use Doctrine\ORM\Mapping\ClassMetadata;
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
use Sulu\Bundle\Sales\ShippingBundle\Entity\Shipping;
use Sulu\Bundle\Sales\ShippingBundle\Entity\ShippingItem;
use Sulu\Bundle\Sales\ShippingBundle\Entity\ShippingStatus;
use Sulu\Bundle\Sales\ShippingBundle\Entity\ShippingStatusTranslation;
use Sulu\Bundle\TestBundle\Testing\SuluTestCase;

class ShippingControllerTest extends SuluTestCase
{
    /**
     * @var array
     */
    protected static $entities;

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var ShippingStatus
     */
    protected $statusCreated;

    /**
     * @var ShippingStatus
     */
    protected $statusDeliverNote;

    /**
     * @var ShippingStatus
     */
    protected $statusShipped;

    /**
     * @var ShippingStatus
     */
    protected $statusCancled;

    /**
     * @var string
     */
    private $locale = 'en';

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
     * @var OrderStatusTranslation
     */
    private $orderStatusTranslation;

    /**
     * @var Item
     */
    private $item;

    /**
     * @var Product
     */
    private $product;

    /**
     * @var ProductTranslation
     */
    private $productTranslation;

    /**
     * @var Phone
     */
    private $phone;

    /**
     * @var Shipping
     */
    private $shipping;

    /**
     * @var Shipping
     */
    private $shipping2;

    /**
     * @var ShippingStatus
     */
    private $shippingStatus;

    /**
     * @var ShippingItem
     */
    private $shippingItem;

    /**
     * @var OrderAddress
     */
    private $shippingAddress;

    /**
     * @var OrderAddress
     */
    private $shippingAddress2;

    public function setUp()
    {
        $this->em = $this->db('ORM')->getOm();
        $this->purgeDatabase();
        $this->setUpTestData();
        $this->em->flush();
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
        $this->orderStatus = new OrderStatus();
        $this->orderStatus->setId(1);
        $this->orderStatusTranslation = new OrderStatusTranslation();
        $this->orderStatusTranslation->setName('Created');
        $this->orderStatusTranslation->setLocale($this->locale);
        $this->orderStatusTranslation->setStatus($this->orderStatus);

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
        $this->order->setBitmaskStatus($this->orderStatus->getId());
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

        // shipping
        $metadata = $this->em->getClassMetaData(get_class(new ShippingStatus()));
        $metadata->setIdGeneratorType(ClassMetadata::GENERATOR_TYPE_NONE);

        // created
        $this->statusCreated = new ShippingStatus();
        $this->statusCreated->setId(ShippingStatus::STATUS_CREATED);
        $this->createStatusTranslation($this->statusCreated, 'Created', 'en');
        $this->createStatusTranslation($this->statusCreated, 'Erfasst', 'de');

        // delivery note
        $this->statusDeliverNote = new ShippingStatus();
        $this->statusDeliverNote->setId(ShippingStatus::STATUS_DELIVERY_NOTE);
        $this->createStatusTranslation($this->statusDeliverNote, 'Delivery note created', 'en');
        $this->createStatusTranslation($this->statusDeliverNote, 'Lieferschein erstellt', 'de');

        // shipped
        $this->statusShipped = new ShippingStatus();
        $this->statusShipped->setId(ShippingStatus::STATUS_SHIPPED);
        $this->createStatusTranslation($this->statusShipped, 'Shipped', 'en');
        $this->createStatusTranslation($this->statusShipped, 'Versandt', 'de');

        // canceled
        $this->statusCancled = new ShippingStatus();
        $this->statusCancled->setId(ShippingStatus::STATUS_CANCELED);
        $this->createStatusTranslation($this->statusCancled, 'Canceled', 'en');
        $this->createStatusTranslation($this->statusCancled, 'Storniert', 'de');

        $this->em->persist($this->statusCancled);
        $this->em->persist($this->statusShipped);
        $this->em->persist($this->statusDeliverNote);
        $this->em->persist($this->statusCreated);

        $this->shippingAddress = clone $this->orderAddressDelivery;
        $this->shipping = new Shipping();
        $this->shipping->setNumber('00001');
        $this->shipping->setShippingNumber('432');
        $this->shipping->setStatus($this->statusCreated);
        $this->shipping->setOrder($this->order);
        $this->shipping->setChanged(new DateTime());
        $this->shipping->setCreated(new DateTime());
        $this->shipping->setCommission('shipping-commission');
        $this->shipping->setDeliveryAddress($this->shippingAddress);
        $this->shipping->setExpectedDeliveryDate(new DateTime('2015-01-01'));
        $this->shipping->setHeight(101);
        $this->shipping->setWidth(102);
        $this->shipping->setLength(103);
        $this->shipping->setWeight(10);
        $this->shipping->setNote('simple shipping note');
        $this->shipping->setTermsOfDeliveryContent($this->termsOfDelivery->getTerms());
        $this->shipping->setTermsOfPaymentContent($this->termsOfPayment->getTerms());
        $this->shipping->setTrackingId('abcd1234');
        $this->shipping->setTrackingUrl('http://www.tracking.url?token=abcd1234');
        $this->shipping->setBitmaskStatus($this->statusCreated->getId());

        $this->shipping2 = clone $this->shipping;
        $this->shipping2->setNumber('00002');
        $this->shipping2->setStatus($this->statusCreated);
        $this->shipping2->setBitmaskStatus($this->statusCreated->getId());
        $this->shippingAddress2 = clone $this->shippingAddress;
        $this->shipping2->setDeliveryAddress($this->shippingAddress2);

        $this->shippingItem = new ShippingItem();
        $this->shippingItem->setShipping($this->shipping);
        $this->shippingItem->setItem($this->item);
        $this->shippingItem->setQuantity(1);
        $this->shippingItem->setNote('shipping-item-note');
        $this->shipping->addShippingItem($this->shippingItem);

        // persist
        $this->em->persist($this->account);
        $this->em->persist($title);
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
        $this->em->persist($this->orderStatus);
        $this->em->persist($this->orderAddressDelivery);
        $this->em->persist($this->orderAddressInvoice);
        $this->em->persist($this->orderStatusTranslation);
        $this->em->persist($this->item);
        $this->em->persist($this->product);
        $this->em->persist($this->productTranslation);
        $this->em->persist($productType);
        $this->em->persist($productTypeTranslation);
        $this->em->persist($productStatus);
        $this->em->persist($productStatusTranslation);
        $this->em->persist($this->shipping);
        $this->em->persist($this->shipping2);
        $this->em->persist($this->shippingItem);
        $this->em->persist($this->shippingAddress);
        $this->em->persist($this->shippingAddress2);
    }

    private function createStatusTranslation($status, $translation, $locale)
    {
        $statusTranslation = new ShippingStatusTranslation();
        $statusTranslation->setName($translation);
        $statusTranslation->setLocale($locale);
        $statusTranslation->setStatus($status);
        $this->em->persist($statusTranslation);
        return $statusTranslation;
    }

    public function testGetById()
    {
        $client = $this->createAuthenticatedClient();
        $client->request('GET', '/api/shippings/' . $this->shipping->getId());
        $response = json_decode($client->getResponse()->getContent());

        // shipping
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals('00001', $response->number);
        $this->assertEquals('432', $response->shippingNumber);
        $this->assertEquals('shipping-commission', $response->commission);
        $this->assertEquals(101, $response->height);
        $this->assertEquals(102, $response->width);
        $this->assertEquals(103, $response->length);
        $this->assertEquals(10, $response->weight);
        $this->assertEquals('abcd1234', $response->trackingId);
        $this->assertEquals('http://www.tracking.url?token=abcd1234', $response->trackingUrl);
        $this->assertEquals('simple shipping note', $response->note);
        $this->assertEquals(
            (new DateTime('2015-01-01'))->getTimestamp(),
            (new DateTime($response->expectedDeliveryDate))->getTimestamp()
        );

        // shipping status
        $this->assertEquals($this->shipping->getStatus()->getId(), $response->status->id);

        // order
        $this->assertEquals($this->order->getId(), $response->order->id);

        // shipping item
        $this->assertEquals(1, count($response->items));
        $item = $response->items[0];
        $this->assertEquals($this->shippingItem->getId(), $item->id);
        $this->assertEquals(1, $item->quantity);
        $this->assertEquals('shipping-item-note', $item->note);

        // order address
        $this->assertEquals($this->shippingAddress->getId(), $response->deliveryAddress->id);
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

        // terms
        $this->assertEquals($this->termsOfDelivery->getTerms(), $response->termsOfDeliveryContent);
        $this->assertEquals($this->termsOfPayment->getTerms(), $response->termsOfPaymentContent);

    }

    public function testGetAll()
    {
        $client = $this->createAuthenticatedClient();
        $client->request('GET', '/api/shippings');
        $response = json_decode($client->getResponse()->getContent());
        $items = $response->_embedded->shippings;

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals(2, count($items));

        $item = $items[0];
        $this->assertEquals('00001', $item->number);

        $item = $items[1];
        $this->assertEquals('00002', $item->number);
    }

    public function testGetAllFlat()
    {
        $client = $this->createAuthenticatedClient();
        $client->request('GET', '/api/shippings?flat=true');
        $response = json_decode($client->getResponse()->getContent());
        $items = $response->_embedded->shippings;

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals(2, count($items));

        $item = $items[0];
        $this->assertEquals('00001', $item->number);

        $item = $items[1];
        $this->assertEquals('00002', $item->number);
    }

    public function testPut()
    {
        $data = array(
            'shippingNumber' => 'sh01',
            'order' => array(
                'id' => $this->order->getId()
            ),
            'deliveryAddress' => array(
                'firstName' => 'Jane',
                'lastName' => 'Bloggs',
                'accountName' => 'Sample-Company',
                'uid' => 'uid123',
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
            'items' => array()
        );
        $client = $this->createAuthenticatedClient();
        $client->request(
            'PUT',
            '/api/shippings/' . $this->shipping->getId(),
            $data
        );
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $client->request('GET', '/api/shippings/' . $this->shipping->getId());
        $response = json_decode($client->getResponse()->getContent());
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals('sh01', $response->shippingNumber);
        $this->assertEquals($this->order->getId(), $response->order->id);

        $this->compareDataWithAddress($data['deliveryAddress'], $response->deliveryAddress);
    }

    public function testPutNotExisting()
    {
        $client = $this->createAuthenticatedClient();
        $client->request('PUT', '/api/shippings/666', array('number' => '123'));
        $response = json_decode($client->getResponse()->getContent());

        $this->assertEquals(404, $client->getResponse()->getStatusCode());

        $this->assertEquals(
            'Entity with the type "SuluSalesShippingBundle:Shipping" and the id "666" not found.',
            $response->message
        );
    }

    public function testPost()
    {
        $data = array(
            'shippingNumber' => 'sh00003',
            'deliveryAddress' => array(
                'firstName' => 'Jane',
                'lastName' => 'Bloggs',
                'accountName' => 'Sample-Company',
                'uid' => 'uid123',
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
            'order' => array(
                'id' => $this->order->getId()
            ),
            'termsOfDeliveryContent' => $this->termsOfDelivery->getTerms(),
            'termsOfPaymentContent' => $this->termsOfPayment->getTerms(),
            'items' => array()
        );

        $client = $this->createAuthenticatedClient();
        $client->request('POST', '/api/shippings', $data);
        $response = json_decode($client->getResponse()->getContent());
        $this->assertEquals('sh00003', $response->shippingNumber);

        $client->request('GET', '/api/shippings/' . $response->id);
        $response = json_decode($client->getResponse()->getContent());
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $this->assertEquals('sh00003', $response->shippingNumber);
        $this->assertEquals(ShippingStatus::STATUS_CREATED, $response->status->id);

        $this->compareDataWithAddress($data['deliveryAddress'], $response->deliveryAddress);
    }

    /**
     * asserts equality if object's attribute exist
     */
    private function assertEqualsArrayClass($firstValue, $secondObject, $value)
    {
        if ($firstValue !== null && array_key_exists($value, $firstValue)) {
            $this->assertEquals($firstValue[$value], $secondObject->$value);
        }
    }

    /**
     * @param $response
     * @param $data
     */
    private function compareDataWithAddress($data, $response)
    {
        $this->assertEqualsArrayClass($data, $response, 'firstName');
        $this->assertEqualsArrayClass($data, $response, 'lastName');
        $this->assertEqualsArrayClass($data, $response, 'accountName');
        $this->assertEqualsArrayClass($data, $response, 'title');
        $this->assertEqualsArrayClass($data, $response, 'addition');
        $this->assertEqualsArrayClass($data, $response, 'number');
        $this->assertEqualsArrayClass($data, $response, 'city');
        $this->assertEqualsArrayClass($data, $response, 'zip');
        $this->assertEqualsArrayClass($data, $response, 'state');
        $this->assertEqualsArrayClass($data, $response, 'country');
//        $this->assertEqualsArrayClass($data, $response, 'postboxPostcode');
//        $this->assertEqualsArrayClass($data, $response, 'postboxNumber');
//        $this->assertEqualsArrayClass($data, $response, 'postboxCity');
        $this->assertEqualsArrayClass($data, $response, 'uid');
        $this->assertEqualsArrayClass($data, $response, 'phone');
        $this->assertEqualsArrayClass($data, $response, 'phoneMobile');
    }
}
