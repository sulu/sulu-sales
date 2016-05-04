<?php
/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\Sales\OrderBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Sulu\Bundle\Sales\OrderBundle\Entity\OrderStatus;

class LoadOrderStatus extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        // force id = 1
        $metadata = $manager->getClassMetaData(get_class(new OrderStatus()));
        $metadata->setIdGeneratorType(\Doctrine\ORM\Mapping\ClassMetadata::GENERATOR_TYPE_NONE);

        // created
        $status = new OrderStatus();
        $status->setId(OrderStatus::STATUS_CREATED);
        $this->createStatusTranslation($manager, $status, 'Created', 'en');
        $this->createStatusTranslation($manager, $status, 'Erfasst', 'de');
        $this->createStatusTranslation($manager, $status, 'Erfasst', 'de_ch');
        $manager->persist($status);

        // cart
        $status = new OrderStatus();
        $status->setId(OrderStatus::STATUS_IN_CART);
        $this->createStatusTranslation($manager, $status, 'In Cart', 'en');
        $this->createStatusTranslation($manager, $status, 'Im Warenkorb', 'de');
        $this->createStatusTranslation($manager, $status, 'Im Warenkorb', 'de_ch');
        $manager->persist($status);

        // confirmed
        $status = new OrderStatus();
        $status->setId(OrderStatus::STATUS_CONFIRMED);
        $this->createStatusTranslation($manager, $status, 'Order confirmed', 'en');
        $this->createStatusTranslation($manager, $status, 'Bestätigt', 'de');
        $this->createStatusTranslation($manager, $status, 'Bestätigt', 'de_ch');
        $manager->persist($status);

        // confirmed
        $status = new OrderStatus();
        $status->setId(OrderStatus::STATUS_CLOSED_MANUALLY);
        $this->createStatusTranslation($manager, $status, 'Manually closed', 'en');
        $this->createStatusTranslation($manager, $status, 'Manuell geschlossen', 'de');
        $this->createStatusTranslation($manager, $status, 'Manuell geschlossen', 'de_ch');
        $manager->persist($status);

        // canceled
        $status = new OrderStatus();
        $status->setId(OrderStatus::STATUS_CANCELED);
        $this->createStatusTranslation($manager, $status, 'Canceled', 'en');
        $this->createStatusTranslation($manager, $status, 'Abgebrochen', 'de');
        $this->createStatusTranslation($manager, $status, 'Abgebrochen', 'de_ch');
        $manager->persist($status);

        // canceled
        $status = new OrderStatus();
        $status->setId(OrderStatus::STATUS_COMPLETED);
        $this->createStatusTranslation($manager, $status, 'Completed', 'en');
        $this->createStatusTranslation($manager, $status, 'Abgeschlossen', 'de');
        $this->createStatusTranslation($manager, $status, 'Abgeschlossen', 'de_ch');
        $manager->persist($status);

        // canceled
        $status = new OrderStatus();
        $status->setId(OrderStatus::STATUS_CART_PENDING);
        $this->createStatusTranslation($manager, $status, 'Cart order pending', 'en');
        $this->createStatusTranslation($manager, $status, 'Warenkorb in Bearbeitung', 'de');
        $this->createStatusTranslation($manager, $status, 'Warenkorb in Bearbeitung', 'de_ch');
        $manager->persist($status);

        $manager->flush();
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 2;
    }

    /**  */
    private function createStatusTranslation($manager, $status, $translation, $locale) {
        $statusTranslation = new \Sulu\Bundle\Sales\OrderBundle\Entity\OrderStatusTranslation();
        $statusTranslation->setName($translation);
        $statusTranslation->setLocale($locale);
        $statusTranslation->setStatus($status);
        $manager->persist($statusTranslation);
        return $statusTranslation;
    }
}
