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
use Sulu\Bundle\Sales\CoreBundle\Entity\ItemStatus;
use Sulu\Bundle\Sales\CoreBundle\Entity\ItemStatusTranslation;

class LoadItemStatus extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        // force id = 1
        $metadata = $manager->getClassMetaData(get_class(new ItemStatus()));
        $metadata->setIdGeneratorType(\Doctrine\ORM\Mapping\ClassMetadata::GENERATOR_TYPE_NONE);

        // created
        $status = new ItemStatus();
        $status->setId(1);
        $this->createStatusTranslation($manager, $status, 'Created', 'en');
        $this->createStatusTranslation($manager, $status, 'Erstellt', 'de');
        $manager->persist($status);
        $manager->flush();
    }

    /**
     * @param $manager
     * @param $status
     * @param $translation
     * @param $locale
     * @return \Sulu\Bundle\Sales\CoreBundle\Entity\ItemStatusTranslation
     */
    private function createStatusTranslation($manager, $status, $translation, $locale) {
        $statusTranslation = new ItemStatusTranslation();
        $statusTranslation->setName($translation);
        $statusTranslation->setLocale($locale);
        $statusTranslation->setStatus($status);
        $manager->persist($statusTranslation);
        return $statusTranslation;
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 1;
    }
}
