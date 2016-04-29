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
use Sulu\Bundle\Sales\OrderBundle\Entity\OrderType;

class LoadOrderTypes extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        // force id = 1
        $metadata = $manager->getClassMetaData(get_class(new OrderType()));
        $metadata->setIdGeneratorType(\Doctrine\ORM\Mapping\ClassMetadata::GENERATOR_TYPE_NONE);

        // manual
        $type = new OrderType();
        $type->setId(OrderType::MANUAL);
        $this->createTypeTranslation($manager, $type, 'Manual', 'en');
        $this->createTypeTranslation($manager, $type, 'Manuell', 'de');
        $this->createTypeTranslation($manager, $type, 'Manuell', 'de_CH');
        $manager->persist($type);

        // shop
        $type = new OrderType();
        $type->setId(OrderType::SHOP);
        $this->createTypeTranslation($manager, $type, 'Shop order', 'en');
        $this->createTypeTranslation($manager, $type, 'Shopbestellung', 'de');
        $this->createTypeTranslation($manager, $type, 'Shopbestellung', 'de_CH');
        $manager->persist($type);

        // anonymous
        $type = new OrderType();
        $type->setId(OrderType::ANONYMOUS);
        $this->createTypeTranslation($manager, $type, 'Anonymous order', 'en');
        $this->createTypeTranslation($manager, $type, 'Anonyme Bestellung', 'de');
        $this->createTypeTranslation($manager, $type, 'Anonyme Bestellung', 'de_CH');
        $manager->persist($type);

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
    private function createTypeTranslation($manager, $type, $translation, $locale) {
        $typeTranslation = new \Sulu\Bundle\Sales\OrderBundle\Entity\OrderTypeTranslation();
        $typeTranslation->setName($translation);
        $typeTranslation->setLocale($locale);
        $typeTranslation->setType($type);
        $manager->persist($typeTranslation);
        return $typeTranslation;
    }
}
