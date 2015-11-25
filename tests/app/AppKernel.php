<?php

/*
 * This file is part of the Sulu.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

use Symfony\Component\Config\Loader\LoaderInterface;
use Sulu\Bundle\TestBundle\Kernel\SuluTestKernel;

class AppKernel extends SuluTestKernel
{
    public function registerBundles()
    {
        $bundles = parent::registerBundles();

        return $bundles;
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        parent::registerContainerConfiguration($loader);

        $loader->load(__DIR__ . '/config/config.yml');
    }
}
