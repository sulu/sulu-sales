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

/**
 * Interface SalesDocument should be implemented by all documents which will be displayed in the table-widget in the
 * sidebar. It will offer methods to retrieve basic information about the documents
 */
interface SalesDocument {

    /**
     * Returns the data needed for the sales document widget as array
     * @return array
     */
    public function getSalesDocumentData();
}
