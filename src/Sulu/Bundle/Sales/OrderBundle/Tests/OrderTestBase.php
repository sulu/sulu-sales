<?php
/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\Sales\OrderBundle\Tests;

use Doctrine\ORM\EntityManager;
use Sulu\Bundle\ContactBundle\Entity\AccountInterface;
use Sulu\Bundle\ContactBundle\Entity\Address;
use Sulu\Bundle\Sales\CoreBundle\Entity\OrderAddressInterface;
use Sulu\Bundle\Sales\CoreBundle\Item\ItemFactoryInterface;
use Sulu\Bundle\TestBundle\Testing\SuluTestCase;
use Sulu\Component\Contact\Model\ContactInterface;
use Sulu\Component\Contact\Model\ContactRepositoryInterface;
use Symfony\Component\HttpKernel\Client;

class OrderTestBase extends SuluTestCase
{
    protected $locale = 'en';

    protected static $orderStatusEntityName = 'SuluSalesOrderBundle:OrderStatus';

    /**
     * @var OrderDataSetup
     */
    protected $data;
    
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var Client
     */
    protected $client;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->em = $this->getEntityManager();
        $this->purgeDatabase();
        $this->setUpTestData();
        $this->client = $this->createAuthenticatedClient();
        $this->em->flush();
    }

    /**
     * Setup order test data.
     */
    protected function setUpTestData()
    {
        $this->data = new OrderDataSetup($this->em, $this->getItemFactory(), $this->getContactRepository());
    }

    /**
     * Compares an order-address response with its origin entities.
     *
     * @param OrderAddressInterface $orderAddress
     * @param Address $address
     * @param ContactInterface $contact
     * @param AccountInterface $account
     */
    protected function checkOrderAddress(
        $orderAddress,
        Address $address,
        ContactInterface $contact,
        AccountInterface $account = null
    ) {
        // Contact
        $this->assertEquals($contact->getFirstName(), $orderAddress->firstName);
        $this->assertEquals($contact->getLastName(), $orderAddress->lastName);
        if ($contact->getTitle() !== null) {
            $this->assertEquals($contact->getTitle()->getTitle(), $orderAddress->title);
        }

        // Address
        $this->assertEqualsIfExists($address->getStreet(), $orderAddress, 'street');
        $this->assertEqualsIfExists($address->getAddition(), $orderAddress, 'addition');
        $this->assertEqualsIfExists($address->getNumber(), $orderAddress, 'number');
        $this->assertEqualsIfExists($address->getCity(), $orderAddress, 'city');
        $this->assertEqualsIfExists($address->getZip(), $orderAddress, 'zip');
        $this->assertEqualsIfExists($address->getCountry()->getName(), $orderAddress, 'country');
        $this->assertEqualsIfExists($address->getPostboxNumber(), $orderAddress, 'postboxNumber');
        $this->assertEqualsIfExists($address->getPostboxCity(), $orderAddress, 'postboxCity');
        $this->assertEqualsIfExists($address->getPostboxPostcode(), $orderAddress, 'postboxPostcode');

        // Account
        if ($account) {
            $this->assertEqualsIfExists($account->getName(), $orderAddress, 'accountName');
            $this->assertEqualsIfExists($account->getUid(), $orderAddress, 'uid');
        }
    }

    /**
     * Asserts equality if object's attribute exist.
     *
     * @param mixed $firstValue
     * @param \stdClass $secondObject
     * @param string $value
     */
    protected function assertEqualsIfExists($firstValue, $secondObject, $value)
    {
        if ($firstValue !== null) {
            $this->assertEquals($firstValue, $secondObject->$value);
        }
    }

    /**
     * @return ItemFactoryInterface
     */
    protected function getItemFactory()
    {
        return $this->getContainer()->get('sulu_sales_core.item_factory');
    }

    /**
     * @return ContactRepositoryInterface
     */
    private function getContactRepository()
    {
        return $this->getContainer()->get('sulu.repository.contact');
    }
}
