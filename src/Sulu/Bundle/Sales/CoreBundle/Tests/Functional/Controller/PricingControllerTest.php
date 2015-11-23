<?php
/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\Sales\CoreBundle\Tests\Functional\Controller;

use Sulu\Bundle\ProductBundle\Tests\Resources\ProductTestData;
use Sulu\Bundle\Sales\CoreBundle\Tests\Resources\SuluSalesTestCase;

/**
 * Testing Pricing controller.
 */
class PricingControllerTest extends SuluSalesTestCase
{
    protected $locale = 'en';

    /**
     * @var OrderDataSetup
     */
    protected $data;

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var ProductTestData
     */
    protected $productData;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->em = $this->db('ORM')->getOm();
        $this->purgeDatabase();
        $this->setUpTestData();
        $this->em->flush();
        $this->client = $this->createAuthenticatedClient();
    }

    /**
     * Setup test data.
     */
    protected function setUpTestData()
    {
        $this->productData = new ProductTestData($this->container);
    }

    /**
     * Simple test for pricing api.
     */
    public function testSimplePricing()
    {
        $itemData = [
            $this->getItemSampleData(),
        ];

        $client = $this->createAuthenticatedClient();
        $client->request(
            'POST', '/api/pricings', [
                'taxfree' => false,
                'currency' => 'EUR',
                'items' => $itemData
            ]
        );

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $response = json_decode($client->getResponse()->getContent());

        $this->assertEquals($itemData[0]['price'], $response[0]->price);
        $this->assertEquals($itemData[0]['price'] * $itemData[0]['quantity'], $response[0]->totalNetPrice);
    }

    /**
     * Simple test for pricing api.
     */
    public function testMultiplePricings()
    {
        $itemData = [
            $this->getItemSampleData(),
            $this->getItemSampleData(),
            $this->getItemSampleData(),
        ];

        $client = $this->createAuthenticatedClient();
        $client->request(
            'POST', '/api/pricings', [
                'taxfree' => false,
                'currency' => 'EUR',
                'items' => $itemData
            ]
        );

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $response = json_decode($client->getResponse()->getContent());

        foreach ($itemData as $index => $data) {
            $this->assertEquals($itemData[$index]['price'], $response[$index]->price);
            $this->assertEquals(
                $itemData[$index]['price'] * $itemData[$index]['quantity'],
                $response[$index]->totalNetPrice
            );
        }
    }

    /**
     * Returns sample data for item.
     *
     * @return array
     */
    private function getItemSampleData()
    {
        return [
            'id' => 1,
            'name' => 'name',
            'quantity' => 2.0,
            'quantityUnit' => 'pc',
            'useProductsPrice' => false,
            'price' => (float)(rand(1, 999) / 100),
            'discount' => 0,
            'product' => [
                'id' => 1,
            ],
        ];
    }

    /**
     * Returns a random bool.
     *
     * @return bool
     */
    private function getRandomBool()
    {
        return 1 === rand(0,1);
    }
}
