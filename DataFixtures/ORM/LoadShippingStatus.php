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

class LoadShippingStatus extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
//        // force id = 1
//        $metadata = $manager->getClassMetaData(get_class(new ShippingStatus()));
//        $metadata->setIdGeneratorType(\Doctrine\ORM\Mapping\ClassMetadata::GENERATOR_TYPE_NONE);
//
//        // created
//        $status = new ShippingStatus();
//        $status->setId(1);
//        $this->createStatusTranslation($manager, $status, 'Created', 'en');
//        $this->createStatusTranslation($manager, $status, 'Erstellt', 'de');
//        $manager->persist($status);
//
//
//
//        $manager->flush();
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 2;
    }

    private function createStatusTranslation($manager, $status, $translation, $locale) {
//        $statusTranslation = new \Sulu\Bundle\Sales\OrderBundle\Entity\ShippingStatusTranslation();
//        $statusTranslation->setName($translation);
//        $statusTranslation->setLocale($locale);
//        $statusTranslation->setStatus($status);
//        $manager->persist($statusTranslation);
//        return $statusTranslation;
    }
}
