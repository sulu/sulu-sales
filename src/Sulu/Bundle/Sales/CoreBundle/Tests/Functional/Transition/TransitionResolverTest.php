<?php

namespace Sulu\Bundle\Sales\CoreBundle\Tests\Functional\Transition;

use Doctrine\ORM\EntityManager;
use Sulu\Bundle\Sales\CoreBundle\Entity\OrderAddress;
use Sulu\Bundle\Sales\CoreBundle\Entity\Transition;
use Sulu\Bundle\Sales\CoreBundle\Tests\Resources\SuluSalesTestCase;
use Sulu\Bundle\Sales\CoreBundle\Transition\DependencyManager;
use Sulu\Bundle\Sales\CoreBundle\Transition\TransitionResolver;

class TransitionResolverTest extends SuluSalesTestCase
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var OrderAddress[]
     */
    private $orderAdresses = [];

    /**
     * @var DependencyManager
     */
    private $dependencyManager;

    /**
     * @var TransitionResolver
     */
    private $transitionResolver;

    protected function setUp()
    {
        parent::setUp();
        $this->purgeDatabase();

        $this->em = $this->getContainer()->get('doctrine.orm.default_entity_manager');
        $this->dependencyManager = $this->getContainer()->get('sulu_sales_core.dependency_manager');

        $this->dependencyManager->addMapping(OrderAddress::class, ['icon' => 'fa_icon_bug', 'alias' => 'orderAddress']);
        $this->transitionResolver = $this->getContainer()->get('sulu_sales_core.transition_resolver');
        $this->createFixtures();
    }

    /**
     * Create fixtures.
     */
    protected function createFixtures()
    {
        for ($i = 0; $i < 5; ++$i) {
            $address = new OrderAddress();
            $address->setFirstName('firstname ' . $i);
            $address->setLastName('lastname');
            $address->setStreet('street');
            $address->setCity('city');
            $address->setZip('1234');
            $address->setCountry('country');

            $this->em->persist($address);

            $this->orderAdresses[$i] = $address;
        }
        $this->em->flush();

        $transition = new Transition();
        $transition->setSourceId($this->orderAdresses[0]->getId());
        $transition->setSource('orderAddress');
        $transition->setDestinationId($this->orderAdresses[1]->getId());
        $transition->setDestination('orderAddress');

        $this->em->persist($transition);

        $transition = new Transition();
        $transition->setSourceId($this->orderAdresses[1]->getId());
        $transition->setSource('orderAddress');
        $transition->setDestinationId($this->orderAdresses[2]->getId());
        $transition->setDestination('orderAddress');

        $this->em->persist($transition);

        $transition = new Transition();
        $transition->setSourceId($this->orderAdresses[2]->getId());
        $transition->setSource('orderAddress');
        $transition->setDestinationId($this->orderAdresses[3]->getId());
        $transition->setDestination('orderAddress');

        $this->em->persist($transition);

        $transition = new Transition();
        $transition->setSourceId($this->orderAdresses[3]->getId());
        $transition->setSource('orderAddress');
        $transition->setDestinationId($this->orderAdresses[4]->getId());
        $transition->setDestination('orderAddress');

        $this->em->persist($transition);

        $this->em->flush();
    }

    /**
     * Test resolve transitions.
     *
     * @throws \Doctrine\ORM\EntityNotFoundException
     */
    public function testResolveTransitions()
    {
        $transitions = $this->transitionResolver->getTransitions('orderAddress', 3);

        $this->assertNotNull($transitions['current'], 'Current transition not found');
        $this->assertCount(2, $transitions['previous'], 'Previous transition count does not match');
        $this->assertCount(2, $transitions['following'], 'Following transition count does not match');

        $total = count($transitions['current']) + count($transitions['following']) + count($transitions['previous']);
        $this->assertEquals(5, $total, 'Count does not match');
    }

    /**
     * Test resolve transitions with first transition.
     * This should only give the source of the first transition.
     *
     * @throws \Doctrine\ORM\EntityNotFoundException
     */
    public function testResolveMinPreviousTransitions()
    {
        $transitions = $this->transitionResolver->getTransitions('orderAddress', 2);

        $this->assertNotNull($transitions['current'], 'Current transition not found');
        $this->assertCount(1, $transitions['previous'], 'Previous transition count does not match');
        $this->assertCount(3, $transitions['following'], 'Following transition count does not match');

        $total = count($transitions['current']) + count($transitions['following']) + count($transitions['previous']);
        $this->assertEquals(5, $total, 'Count does not match');
    }

    /**
     * Test resolve transitions without following transition.
     *
     * @throws \Doctrine\ORM\EntityNotFoundException
     */
    public function testResolveNoFollowingTransitions()
    {
        $transitions = $this->transitionResolver->getTransitions('orderAddress', 5);

        $this->assertNotNull($transitions['current'], 'Current transition not found');
        $this->assertCount(4, $transitions['previous'], 'Previous transition count does not match');
        $this->assertCount(0, $transitions['following'], 'Following transition count does not match');

        $total = count($transitions['current']) + count($transitions['following']) + count($transitions['previous']);
        $this->assertEquals(5, $total, 'Count does not match');
    }
}
