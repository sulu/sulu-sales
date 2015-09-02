<?php
/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\Sales\CoreBundle\Item\Exception;

class ItemNotFoundException extends ItemException
{
    /**
     * The name of the object not found
     * @var string
     */
    private $entityName;

    /**
     * The id of the object not found
     * @var integer
     */
    private $id;

    public function __construct($id)
    {
        $this->entityName = 'SuluSalesCoreBundle:Item';
        $this->id = $id;
        parent::__construct('The item with the id "' . $this->id . '" was not found.', 0);
    }

    /**
     * Returns the name of the entityname of the dependency not found
     * @return string
     */
    public function getEntityName()
    {
        return $this->entityName;
    }

    /**
     * Returns the id of the object not found
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
}
