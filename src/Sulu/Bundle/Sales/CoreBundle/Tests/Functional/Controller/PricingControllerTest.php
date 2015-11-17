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

use Sulu\Bundle\TestBundle\Testing\SuluTestCase;

/**
 * Testing Pricing controller.
 */
class PricingControllerTest extends SuluTestCase
{
    protected $locale = 'en';

    protected static $orderStatusEntityName = 'SuluSalesOrderBundle=>OrderStatus';

    /**
     * @var OrderDataSetup
     */
    protected $data;

    /**
     * @var EntityManager
     */
    protected $em;

    public function setUp()
    {
        $this->em = $this->db('ORM')->getOm();
        $this->purgeDatabase();
        $this->setUpTestData();
        $this->client = $this->createAuthenticatedClient();
        $this->em->flush();
    }

    protected function setUpTestData()
    {
    }

    public function testSimplePricing()
    {
        $client = $this->createAuthenticatedClient();
        $client->request(
            'POST', '/api/pricings', [
                'taxfree' => false,
                'currency' => 'EUR',
                'items' => $this->getItemSampleData()
            ]
        );

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $response = json_decode($client->getResponse()->getContent());

        $this->assertEquals($this->data->order->getId(), $response->id);
    }

    private function getItemSampleData()
    {
        return [
            'id' => 1,
            'name' => 'name',
            'quantity' => 2.0,
            'quantityUnit' => 'pc',
            'useProductsPrice' => false,
            'price' => 7.75,
            'discount' => 0,
            'product' => [
                'id' => 1,
            ],
        ];
    }
}
