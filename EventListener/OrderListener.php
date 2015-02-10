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
use Symfony\Component\HttpKernel\Event\PostResponseEvent;

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

    private function getOrderUpdater()
    {
        return $this->container->get('sulu_sales_order.order_updater');
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

        // TODO: refactor orders and use order interface
        if ($entity instanceof Order) {
            $entityManager = $args->getEntityManager();
            // after saving check if number is set, else set a new one
            if ($entity->getNumber() === null) {
                $entity->setNumber(sprintf('%05d', $entity->getId()));
                $entityManager->flush();
            }
        }

        $this->getOrderUpdater()->scheduleForUpdate($this->getItemId($args));
    }

    public function preUpdate(LifecycleEventArgs $args)
    {
        $this->getOrderUpdater()->scheduleForUpdate($this->getItemId($args));
    }

    public function postDelete(LifecycleEventArgs $args)
    {
        $this->getOrderUpdater()->scheduleForUpdate($this->getItemId($args));
    }

    public function onKernelTerminate(PostResponseEvent $event)
    {
        $requestMethod = $event->getRequest()->getMethod();
        $requestUri = $event->getRequest()->getRequestUri();
        if (strpos($requestUri, '/api/orders') && ($requestMethod == 'PUT' || $requestMethod == 'POST')) {
            $this->getOrderUpdater()->processIds();
        }
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
