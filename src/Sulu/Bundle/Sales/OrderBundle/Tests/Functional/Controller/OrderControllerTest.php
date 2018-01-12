<?php
/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\Sales\OrderBundle\Tests\Functional\Controller;

use DateTime;
use Sulu\Bundle\Sales\CoreBundle\Entity\Item;
use Sulu\Bundle\Sales\CoreBundle\Entity\ItemInterface;
use Sulu\Bundle\Sales\OrderBundle\Entity\OrderStatus;
use Sulu\Bundle\Sales\OrderBundle\Tests\OrderTestBase;

class OrderControllerTest extends OrderTestBase
{
    public function testGetById()
    {
        $this->client->request('GET', '/api/orders/' . $this->data->order->getId());
        $response = json_decode($this->client->getResponse()->getContent());

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertEquals('1234', $response->number);
        $this->assertEquals('EUR', $response->currencyCode);
        $this->assertEquals('abcd1234', $response->sessionId);
        $this->assertEquals('cost-centre', $response->costCentre);
        $this->assertEquals(
            (new DateTime('2015-01-01'))->getTimestamp(),
            (new DateTime($response->desiredDeliveryDate))->getTimestamp()
        );
        $this->assertEquals(true, $response->taxfree);
        $this->assertEquals('commission', $response->commission);
        $this->assertEquals('10kg minimum', $response->termsOfDeliveryContent);
        $this->assertEquals('10% off', $response->termsOfPaymentContent);
        $this->assertEquals('tiny internal note', $response->internalNote);

        // Order status
        $this->assertEquals('Created', $response->status->status);
        $this->assertEquals($this->data->orderStatus->getId(), $response->status->id);

        // Contact
        $this->assertEquals($this->data->contact->getId(), $response->customerContact->id);

        // Order Address delivery
        $this->assertEquals($this->data->orderAddressDelivery->getId(), $response->deliveryAddress->id);
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
        // Order Address invoice
        $this->assertEquals($this->data->orderAddressDelivery->getId(), $response->deliveryAddress->id);
        $this->assertEquals('John', $response->deliveryAddress->firstName);

        // TODO: extend item tests
        // Items
        $this->assertEquals(2, count($response->items));
        $item = $response->items[0];
        $this->assertEquals($this->data->item->getId(), $item->id);
        // Item product
        $this->assertEquals($this->data->item->getProduct()->getId(), $item->product->id);
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

        $orderNumbers = [];
        foreach ($items as $item) {
            $orderNumbers[] = $item->number;
        }

        $this->assertContains('1234', $orderNumbers);
        $this->assertContains('12345', $orderNumbers);
    }

    public function testPut()
    {
        $client = $this->createAuthenticatedClient();
        $client->request(
            'PUT',
            '/api/orders/' . $this->data->order->getId(),
            [
                'orderNumber' => 'EvilNumber',
                'customerContact' => [
                    'id' =>  $this->data->contact->getId()
                ],
                'customerAccount' => [
                    'id' =>  $this->data->account->getId()
                ],
                'invoiceAddress' => [
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
                ],
                'deliveryAddress' => [
                    'street' => 'Street',
                    'number' => '2',
                    'city' => 'Utopia',
                    'zip' => '1',
                    'country' => 'Country'
                ],
            ]
        );
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $client->request('GET', '/api/orders/' . $this->data->order->getId());
        $response = json_decode($client->getResponse()->getContent());
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals('EvilNumber', $response->orderNumber);

        $this->checkOrderAddress(
            $response->invoiceAddress,
            $this->data->address,
            $this->data->contact,
            $this->data->account
        );
        $this->checkOrderAddress(
            $response->deliveryAddress,
            $this->data->address2,
            $this->data->contact,
            $this->data->account
        );
    }

    public function testPutNotExisting()
    {
        $client = $this->createAuthenticatedClient();
        $client->request(
            'PUT',
            '/api/orders/666',
            ['number' => '123']
        );
        $response = json_decode($client->getResponse()->getContent());

        $this->assertEquals(404, $client->getResponse()->getStatusCode());

        $this->assertEquals(
            'Entity with the type "SuluSalesOrderBundle:Order" and the id "666" not found.',
            $response->message
        );
    }

    public function testPost()
    {
        $data = [
            'orderNumber' => 'NUMBER:0815',
            'supplierName' => $this->data->account->getName(),
            'customerAccount' => [
                'id' => $this->data->account->getId()
            ],
            'customerContact' => [
                'id' => $this->data->contact->getId()
            ],
            'invoiceAddress' => [
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
            ],
            'deliveryAddress' => [
                'street' => 'Street',
                'number' => '2',
                'city' => 'Utopia',
                'zip' => '1',
                'country' => 'Country'
            ],
            'termsOfDelivery' => [
                'id' => $this->data->termsOfDelivery->getId()
            ],
            'termsOfPayment' => [
                'id' => $this->data->termsOfPayment->getId()
            ],
        ];
        $client = $this->createAuthenticatedClient();

        $client->request('POST', '/api/orders', $data);
        $response = json_decode($client->getResponse()->getContent());

        $client->request('GET', '/api/orders/' . $response->id);
        $response = json_decode($client->getResponse()->getContent());
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $this->assertEquals('NUMBER:0815', $response->orderNumber);
        $this->assertEquals('Created', $response->status->status);

        $this->checkOrderAddress(
            $response->invoiceAddress,
            $this->data->address,
            $this->data->contact,
            $this->data->account
        );
        $this->checkOrderAddress(
            $response->deliveryAddress,
            $this->data->address2,
            $this->data->contact,
            $this->data->account
        );
    }

    public function testPostItems()
    {
        $data = [
            'orderNumber' => 'NUMBER:0815',
            'supplierName' => $this->data->account->getName(),
            'customerAccount' => [
                'id' => $this->data->account->getId()
            ],
            'orderType' => [
              'id' => $this->data->orderTypeManual->getId()
            ],
            'customerContact' => [
                'id' => $this->data->contact->getId()
            ],
            'invoiceAddress' => [
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
            ],
            'deliveryAddress' => [
                'street' => 'Street',
                'number' => '2',
                'city' => 'Utopia',
                'zip' => '1',
                'country' => 'Country'
            ],
            'items' => [
                [
                    'name' => $this->data->productTranslation->getName(),
                    'number' => $this->data->product->getNumber(),
                    'quantity' => 2,
                    'quantityUnit' => 'pc',
                    'price' => 1000,
                    'discount' => 10,
                    'tax' => 20,
                    'description' => $this->data->productTranslation->getLongDescription(),
                    'useProductsPrice' => false,
                    'weight' => 12,
                    'width' => 13,
                    'height' => 14,
                    'length' => 15,
                    'supplierName' => 'supplier',
                    'isRecurringPrice' => true,
                    'product' => [
                        'id' => $this->data->product->getId()
                    ]
                ],
                [
                    'name' => $this->data->productTranslation->getName(),
                    'number' => $this->data->addon->getAddon()->getNumber(),
                    'quantity' => 2,
                    'quantityUnit' => 'pc',
                    'price' => 1000,
                    'discount' => 10,
                    'tax' => 20,
                    'description' => $this->data->productTranslation->getLongDescription(),
                    'useProductsPrice' => false,
                    'weight' => 12,
                    'width' => 13,
                    'height' => 14,
                    'length' => 15,
                    'supplierName' => 'supplier',
                    'isRecurringPrice' => true,
                    'type' => Item::TYPE_ADDON,
                    'product' => [
                        'id' => $this->data->addon->getAddon()->getId()
                    ]
                ],
            ]
        ];
        $client = $this->createAuthenticatedClient();

        $client->request('POST', '/api/orders', $data);
        $response = json_decode($client->getResponse()->getContent());
        $this->assertEquals(200, $client->getResponse()->getStatusCode(), $client->getResponse());

        $client->request('GET', '/api/orders/' . $response->id);
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $this->assertTrue($response->items[0]->isRecurringPrice);
        $this->assertEquals(Item::TYPE_ADDON, $response->items[1]->type);
        $this->assertEquals($response->items[0]->id, $response->items[1]->parentId);
    }

    public function testPostItemsWithoutProductReference()
    {
        $data = [
            'supplierName' => $this->data->account->getName(),
            'customerAccount' => [
                'id' => $this->data->account->getId()
            ],
            'customerContact' => [
                'id' => $this->data->contact->getId()
            ],
            'invoiceAddress' => [
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
            ],
            'deliveryAddress' => [
                'street' => 'Street',
                'number' => '2',
                'city' => 'Utopia',
                'zip' => '1',
                'country' => 'Country'
            ],
            'items' => [
                [
                    'name' => $this->data->productTranslation->getName(),
                    'number' => $this->data->product->getNumber(),
                    'quantity' => 2,
                    'quantityUnit' => 'pc',
                    'price' => 1000,
                    'discount' => 10,
                    'tax' => 20,
                    'type' => Item::TYPE_CUSTOM,
                    'description' => $this->data->productTranslation->getLongDescription(),
                ]
            ]
        ];
        $client = $this->createAuthenticatedClient();

        $client->request('POST', '/api/orders', $data);
        $response = json_decode($client->getResponse()->getContent());
        $this->assertEquals(200, $client->getResponse()->getStatusCode(), $client->getResponse());

        $client->request('GET', '/api/orders/' . $response->id);
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $response = json_decode($client->getResponse()->getContent(), true);
        $itemData = $data['items'][0];
        $item = $response['items'][0];

        $this->assertEquals($itemData['name'], $item['name'], 'item name');
        $this->assertEquals($itemData['description'], $item['description'], 'item description');
        $this->assertEquals($itemData['number'], $item['number'], 'item number');
        $this->assertEquals($itemData['quantity'], $item['quantity'], 'item quantity');
        $this->assertEquals($itemData['tax'], $item['tax'], 'item tax');
        $this->assertEquals($itemData['price'], $item['price'], 'item price');
        $this->assertEquals($itemData['discount'], $item['discount'], 'item discount');
    }

    public function testStatusChange()
    {
        $client = $this->createAuthenticatedClient();
        $client->request(
            'POST',
            '/api/orders/' . $this->data->order->getId(),
            [
                'action' => 'confirm'
            ]
        );
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $response = json_decode($client->getResponse()->getContent());
        $this->assertEquals($this->data->order->getId(), $response->id);
        $this->assertEquals(OrderStatus::STATUS_CREATED | OrderStatus::STATUS_CONFIRMED, $response->bitmaskStatus);
        $this->assertEquals(OrderStatus::STATUS_CONFIRMED, $response->status->id);

        $client->request(
            'POST',
            '/api/orders/' . $this->data->order->getId(),
            [
                'action' => 'edit'
            ]
        );

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $response = json_decode($client->getResponse()->getContent());
        $this->assertEquals($this->data->order->getId(), $response->id);
        $this->assertEquals(OrderStatus::STATUS_CREATED & ~OrderStatus::STATUS_CONFIRMED, $response->bitmaskStatus);
        $this->assertEquals(OrderStatus::STATUS_CREATED, $response->status->id);
    }

    public function testDeleteById()
    {
        $client = $this->createAuthenticatedClient();
        $client->request('DELETE', '/api/orders/' . $this->data->order->getId());
        $this->assertEquals('204', $client->getResponse()->getStatusCode());

        $client->request('GET', '/api/orders/' . $this->data->order->getId());
        $this->assertEquals('404', $client->getResponse()->getStatusCode());
    }
}
