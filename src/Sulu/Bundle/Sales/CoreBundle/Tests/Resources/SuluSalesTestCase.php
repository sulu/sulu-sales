<?php

namespace Sulu\Bundle\Sales\CoreBundle\Tests\Resources;

use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\ProxyReferenceRepository;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;
use Sulu\Bundle\TestBundle\Testing\SuluTestCase;

/**
 * Testing Pricing controller.
 */
class SuluSalesTestCase extends SuluTestCase
{
    /**
     * Purge the Doctrine ORM database
     */
    protected function purgeDatabase()
    {
        /** @var EntityManager $em */
        $em = $this->db('ORM')->getOm();

        try {
            $connection = $em->getConnection();

            if ($connection->getDriver() instanceof \Doctrine\DBAL\Driver\PDOMySql\Driver) {
                $connection->executeUpdate("SET foreign_key_checks = 0;");
            }

            $purger = new ORMPurger();
            $purger->setPurgeMode(ORMPurger::PURGE_MODE_TRUNCATE);
            $executor = new ORMExecutor($em, $purger);
            $referenceRepository = new ProxyReferenceRepository($em);
            $executor->setReferenceRepository($referenceRepository);

            $executor->purge();
        } catch (\Exception $ex) {
            throw new RuntimeException(
                sprintf(
                    'Could not purge database! Have you initialized it? Run: ' . PHP_EOL .
                    'app/console doctrine:database:create --env=test ' . PHP_EOL .
                    'app/console doctrine:schema:update --force --env=test'
                )
            );
        }

        if ($connection->getDriver() instanceof \Doctrine\DBAL\Driver\PDOMySql\Driver) {
            $em->getConnection()->executeUpdate("SET foreign_key_checks = 1;");
        }
    }
}
