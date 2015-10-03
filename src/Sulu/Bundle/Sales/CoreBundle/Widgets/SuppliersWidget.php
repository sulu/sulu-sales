<?php
/*
  * This file is part of the Sulu CMS.
  *
  * (c) MASSIVE ART WebServices GmbH
  *
  * This source file is subject to the MIT license that is bundled
  * with this source code in the file LICENSE.
  */

namespace Sulu\Bundle\Sales\CoreBundle\Widgets;

/**
 * Widgets for displaying multiple customers.
 */
class SuppliersWidget extends MultipleAccounts
{
    /**
     * {@inheritdoc}
     */
    protected $keys = [
        'ids' => 'supplierIds',
        'limit' => 'supplierLimit',
        'headline' => 'supplierHeadline',
        'emptyLabel' => 'salescore.no-assignment',
    ];
}
