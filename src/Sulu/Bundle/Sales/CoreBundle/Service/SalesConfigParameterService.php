<?php
/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\Sales\CoreBundle\Service;

use Sulu\Bundle\Sales\CoreBundle\Exceptions\SalesConfigParameterNotFoundException;

class SalesConfigParameterService
{
    /**
     * @var array $shopowner
     */
    private $shopowner;

    /**
     * SalesConfigParameterService constructor.
     *
     * @param array $shopowner
     */
    public function __construct($shopowner)
    {
        $this->shopowner = $shopowner;
    }

    /**
     * Returns the value of the given parameter from the shopowner config data.
     *
     * @param string $parameterName
     *
     * @throws SalesConfigParameterNotFoundException
     *
     * @return string
     */
    public function getShopownerParameter($parameterName)
    {
        if (!array_key_exists($parameterName, $this->shopowner)) {
            throw new SalesConfigParameterNotFoundException();
        }

        return $this->shopowner[$parameterName];
    }
}
