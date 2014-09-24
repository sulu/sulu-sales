<?php
/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\Sales\CoreBundle\SalesDependency;

/**
 * Interface of Sales Dependencies
 * which defines which dependencies and permissions an entity has
 *
 * @package Sulu\Bundle\Sales\CoreBundle\SalesDependency
 */
abstract class AbstractSalesDependency
{
    protected $dependencyClasses;

    /**
     * constructor
     */
    public function __construct()
    {
        $this->dependencyClasses = array();
    }

    public function addDependencyClass(SalesDependencyClassInterface $dependency)
    {
        $this->dependencyClasses[] = $dependency;
    }
}
