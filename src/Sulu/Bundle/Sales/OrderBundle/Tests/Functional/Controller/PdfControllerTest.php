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

class PdfControllerTest extends OrderTestBase
{
    public function testGetPdf()
    {
        $request = $this->client->request('GET', '/de/_pdf/order/' . $this->data->order->getId());

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertEquals('application/pdf', $this->client->getResponse()->headers->get('Content-Type'));
        $this->assertNotNull($this->client->getResponse()->getContent());

    }
}
