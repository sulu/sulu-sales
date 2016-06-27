<?php
/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\Sales\ShippingBundle\Tests\Functional\Controller;

use DateTime;
use Doctrine\ORM\EntityManager;
use Sulu\Bundle\ContactExtensionBundle\Entity\TermsOfDelivery;
use Sulu\Bundle\ContactExtensionBundle\Entity\TermsOfPayment;
use Sulu\Bundle\Sales\CoreBundle\Entity\OrderAddress;
use Sulu\Bundle\Sales\OrderBundle\Tests\OrderDataSetup;
use Sulu\Bundle\Sales\ShippingBundle\DataFixtures\ORM\LoadShippingStatus;
use Sulu\Bundle\Sales\ShippingBundle\Entity\Shipping;
use Sulu\Bundle\Sales\ShippingBundle\Entity\ShippingItem;
use Sulu\Bundle\Sales\ShippingBundle\Entity\ShippingStatus;
use Sulu\Bundle\TestBundle\Testing\SuluTestCase;
use Sulu\Component\Contact\Model\ContactRepositoryInterface;

class ShippingControllerTest extends SuluTestCase
{
    /**
     * @var array
     */
    protected $data;

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
     * @var Shipping
     */
    private $shipping;

    /**
     * @var Shipping
     */
    private $shipping2;

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
        $this->em = $this->getEntityManager();
        $this->purgeDatabase();
        $this->setUpTestData();
        $this->em->flush();
    }
    /**
     * @return ItemFactoryInterface
     */
    protected function getItemFactory()
    {
        return $this->getContainer()->get('sulu_sales_core.item_factory');
    }

    private function setUpTestData()
    {
        // initialize order data
        $this->data = new OrderDataSetup($this->em, $this->getItemFactory(), $this->getContactRepository());

        // load order-statuses
        $statusFixtures = new LoadShippingStatus();
        $shippingStatuses = $statusFixtures->load($this->em);
        $this->statusCreated = $shippingStatuses[ShippingStatus::STATUS_CREATED];

        $this->shippingAddress = clone $this->data->orderAddressDelivery;
        $this->shipping = new Shipping();
        $this->shipping->setNumber('00001');
        $this->shipping->setShippingNumber('432');
        $this->shipping->setStatus($this->statusCreated);
        $this->shipping->setOrder($this->data->order);
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
        $this->shipping->setTermsOfDeliveryContent($this->data->termsOfDelivery->getTerms());
        $this->shipping->setTermsOfPaymentContent($this->data->termsOfPayment->getTerms());
        $this->shipping->setTrackingId('abcd1234');
        $this->shipping->setTrackingUrl('http://www.tracking.url?token=abcd1234');
        $this->shipping->setBitmaskStatus($this->statusCreated->getId());
        $this->shipping->setInternalNote('Tiny internal note');

        $this->shipping2 = clone $this->shipping;
        $this->shipping2->setNumber('00002');
        $this->shipping2->setStatus($this->statusCreated);
        $this->shipping2->setBitmaskStatus($this->statusCreated->getId());
        $this->shippingAddress2 = clone $this->shippingAddress;
        $this->shipping2->setDeliveryAddress($this->shippingAddress2);

        $this->shippingItem = new ShippingItem();
        $this->shippingItem->setShipping($this->shipping);
        $this->shippingItem->setItem($this->data->item);
        $this->shippingItem->setQuantity(1);
        $this->shippingItem->setNote('shipping-item-note');
        $this->shipping->addShippingItem($this->shippingItem);

        // persist
        $this->em->persist($this->shipping);
        $this->em->persist($this->shipping2);
        $this->em->persist($this->shippingItem);
        $this->em->persist($this->shippingAddress);
        $this->em->persist($this->shippingAddress2);
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
        $this->assertEquals('Tiny internal note', $response->internalNote);

        // shipping status
        $this->assertEquals($this->shipping->getStatus()->getId(), $response->status->id);

        // order
        $this->assertEquals($this->data->order->getId(), $response->order->id);

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
        $this->assertEquals($this->data->termsOfDelivery->getTerms(), $response->termsOfDeliveryContent);
        $this->assertEquals($this->data->termsOfPayment->getTerms(), $response->termsOfPaymentContent);

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
                'id' => $this->data->order->getId()
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
        $this->assertEquals($this->data->order->getId(), $response->order->id);

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
                'id' => $this->data->order->getId()
            ),
            'termsOfDeliveryContent' => $this->data->termsOfDelivery->getTerms(),
            'termsOfPaymentContent' => $this->data->termsOfPayment->getTerms(),
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
     * Asserts equality if object's attribute exist.
     */
    private function assertEqualsArrayClass($firstValue, $secondObject, $value)
    {
        if ($firstValue !== null && array_key_exists($value, $firstValue)) {
            $this->assertEquals($firstValue[$value], $secondObject->$value);
        }
    }

    /**
     * @param \stdClass $response
     * @param array $data
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

    /**
     * @return ContactRepositoryInterface
     */
    private function getContactRepository()
    {
        return $this->getContainer()->get('sulu.repository.contact');
    }
}
