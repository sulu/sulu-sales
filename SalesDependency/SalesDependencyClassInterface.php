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
 * which defines which dependency an entity has
 *
 * @package Sulu\Bundle\Sales\CoreBundle\SalesDependency
 */
interface SalesDependencyClassInterface
{
    /**
     * returns name of the dependency class
     * @return string
     */
    public function getName();

    /**
     * returns array of parameters
     * @param $entity
     * @return bool
     */
    public function allowDelete($entity);

    /**
     * returns the identifying name
     * @param $entity
     * @return bool
     */
    public function allowCancel($entity);

    /**
     * returns all documents for the given entityId
     *
     * @param $entity
     * @return array
     */
    public function getDocuments($entity);

    /**
     * returns all possible workflows for the current entity
     *
     * @param $entity
     * @return array
     */
    public function getWorkflows($entity);
}
