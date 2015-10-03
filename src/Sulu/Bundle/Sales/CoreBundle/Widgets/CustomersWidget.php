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
class CustomersWidget extends MultipleAccounts
{
    /**
     * {@inheritdoc}
     */
    protected $keys = [
        'ids' => 'customerIds',
        'limit' => 'customerLimit',
        'headline' => 'customerHeadline',
        'emptyLabel' => 'salescore.no-assignment'
    ];
}
