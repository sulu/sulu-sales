<?php
/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Sulu\Bundle\Sales\ShippingBundle\Entity\ShippingStatus;
use Sulu\Bundle\Sales\ShippingBundle\Entity\ShippingStatusTranslation;

class LoadShippingStatus extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        // force id = 1
        $metadata = $manager->getClassMetaData(get_class(new ShippingStatus()));
        $metadata->setIdGeneratorType(\Doctrine\ORM\Mapping\ClassMetadata::GENERATOR_TYPE_NONE);

        // created
        $status = new ShippingStatus();
        $status->setId(ShippingStatus::STATUS_CREATED);
        $this->createStatusTranslation($manager, $status, 'Created', 'en');
        $this->createStatusTranslation($manager, $status, 'Erfasst', 'de');
        $manager->persist($status);

        $status = new ShippingStatus();
        $status->setId(ShippingStatus::STATUS_DELIVERY_NOTE);
        $this->createStatusTranslation($manager, $status, 'Delivery note created', 'en');
        $this->createStatusTranslation($manager, $status, 'Lieferschein erstellt', 'de');
        $manager->persist($status);

        $status = new ShippingStatus();
        $status->setId(ShippingStatus::STATUS_SHIPPED);
        $this->createStatusTranslation($manager, $status, 'Shipped', 'en');
        $this->createStatusTranslation($manager, $status, 'Versandt', 'de');
        $manager->persist($status);

        $manager->flush();
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 1;
    }

    private function createStatusTranslation($manager, $status, $translation, $locale) {
        $statusTranslation = new ShippingStatusTranslation();
        $statusTranslation->setName($translation);
        $statusTranslation->setLocale($locale);
        $statusTranslation->setStatus($status);
        $manager->persist($statusTranslation);
        return $statusTranslation;
    }
}
