<?php
/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\Sales\OrderBundle\EventListener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Sulu\Bundle\Sales\OrderBundle\Entity\Order;
use Sulu\Bundle\Sales\CoreBundle\Entity\Item;
use Sulu\Bundle\Sales\OrderBundle\Order\OrderManager;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Class AccountListener
 * @package Sulu\Bundle\Sales\OrderBundle\EventListener
 */
class OrderListener implements EventSubscriberInterface
{
    /**
     * @var OrderManager
     */
    private $container;

    public function __construct($container)
    {
        // WORKAROUND - bad style, but injecting just the OrderManager
        // causes a circular dependency.
        $this->container = $container;
    }

    private function getOrderManager()
    {
        return $this->container->get('sulu_sales_order.order_manager');
    }

    /**
     * Register to kernel events
     */
    public static function getSubscribedEvents()
    {
        $events[KernelEvents::TERMINATE][] = array('onKernelTerminate', 200);
        return $events;
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        if ($entity instanceof Order) {
            $entityManager = $args->getEntityManager();
            // after saving check if number is set, else set a new one
            if ($entity->getNumber() === null) {
                $entity->setNumber(sprintf('%05d', $entity->getId()));
                $entityManager->flush();
            }
        }

        $this->getOrderManager()->scheduleForUpdate($this->getItemId($args));
    }

    public function preUpdate(LifecycleEventArgs $args)
    {
        $this->getOrderManager()->scheduleForUpdate($this->getItemId($args));
    }

    public function postDelete(LifecycleEventArgs $args)
    {
        $this->getOrderManager()->scheduleForUpdate($this->getItemId($args));
    }

    public function onKernelTerminate()
    {
        $this->getOrderManager()->processIds();
    }

    /**
     * Returns the id of an Item
     *
     * @param null|string $args
     */
    private function getItemId($args)
    {
        $entity = $args->getEntity();
        if ($entity instanceof Item) {
            return $entity->getId();
        }
        return null;
    }
}
