<?php

namespace Sulu\Bundle\Sales\CoreBundle\Service;

use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\DependencyInjection\Container;

class SalesConfigParameterService
{
    /**
     * @var Container $container
     */
    private $container;

    /**
     * @var array $shopowner
     */
    private $shopowner;

    /**
     * SalesConfigParameterService constructor.
     *
     * @param Container $container
     * @param array $shopowner
     */
    public function __construct(Container $container, $shopowner)
    {
        $this->container = $container;
        $this->shopowner = $shopowner;
    }

    /**
     * Returns the value of the given parameter from the shopowner config data.
     *
     * @param string $parameterName
     *
     * @return mixed
     */
    public function getShopownerParameter($parameterName)
    {
        if (!array_key_exists($parameterName, $this->shopowner)) {
            throw new Exception("Parameter doesn't exists");
        }

        return $this->shopowner[$parameterName];
    }
}
