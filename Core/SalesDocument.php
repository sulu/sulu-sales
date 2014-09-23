<?php
/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\Sales\CoreBundle\Core;

use DateTime;

/**
 * Interface SalesDocument should be implemented by all documents which will be displayed in the table-widget in the
 * sidebar. It will offer methods to retrieve basic information about the documents
 */
interface SalesDocument {

    /**
     * Returns the identification number of the document
     * @return String
     */
    public function getNumber();

    /**
     * Returns the date of the document
     * @return DateTime
     */
    public function getDate();

    /**
     * Returns the type of the document
     * @return String
     */
    public function getType();

    /**
     * Returns object as array
     * @return []
     */
    public function toArray();

    /**
     * Returns the id
     * @return mixed
     */
    public function getId();
}
