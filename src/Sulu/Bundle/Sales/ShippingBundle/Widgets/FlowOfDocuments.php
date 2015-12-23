<?php
/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\Sales\ShippingBundle\Widgets;

use DateTime;
use Sulu\Bundle\AdminBundle\Widgets\WidgetException;
use Sulu\Bundle\AdminBundle\Widgets\WidgetParameterException;
use Sulu\Bundle\Sales\CoreBundle\Widgets\FlowOfDocuments as FlowOfDocumentsBase;
use Sulu\Bundle\Sales\ShippingBundle\Api\Shipping;

class FlowOfDocuments extends FlowOfDocumentsBase
{
    protected $routes;

    protected $widgetName = 'ShippingFlowOfDocuments';

    public function __construct(array $routes)
    {
        $this->routes = $routes;
    }

    /**
     * return name of widget
     *
     * @return string
     */
    public function getName()
    {
        return 'shipping-flow-of-documents';
    }

    /**
     * returns data to render template
     *
     * @param array $options
     *
     * @throws WidgetException
     * @return array
     */
    public function getData($options)
    {
        $this->checkRequiredParameters($options);

        $this->getOrderData($options);
        $this->getShipppingData($options);
        parent::orderDataByDate(false);

        return parent::serializeData();
    }

    /**
     * Retrieves order data
     *
     * @param $options
     *
     * @throws \Sulu\Bundle\AdminBundle\Widgets\WidgetParameterException
     */
    protected function getOrderData($options)
    {
        parent::addEntry(
            $options['orderId'],
            $options['orderNumber'],
            'fa-shopping-cart',
            new DateTime($options['orderDate']),
            parent::getRoute($options['orderId'], 'order', 'details'),
            parent::getRoute($options['orderId'], 'order', 'pdf'),
            'salesorder.order'
        );
    }

    /**
     * Retrieves order data
     *
     * @param $options
     *
     * @throws \Sulu\Bundle\AdminBundle\Widgets\WidgetParameterException
     */
    protected function getShipppingData($options)
    {
        parent::addEntry(
            $options['id'],
            $options['number'],
            'fa-truck',
            new DateTime($options['date']),
            parent::getRoute($options['id'], 'shipping', 'details'),
            null,
            'salesshipping.shipping'
        );
    }

    /**
     * @param $options
     *
     * @throws WidgetException
     * @throws WidgetParameterException
     *
     * @return bool
     */
    protected function checkRequiredParameters($options)
    {
        if (empty($options)) {
            throw new WidgetException('No params found!', $this->getName());
        }

        $requiredParameters = ['orderNumber', 'orderDate', 'orderId', 'locale', 'id', 'date', 'number'];

        // check if all required params are set
        foreach ($requiredParameters as $parameter) {
            if (empty($options[$parameter])) {
                throw new WidgetParameterException(
                    'Required parameter ' . $parameter . ' not found or invalid!',
                    $this->widgetName,
                    $parameter
                );
            }
        }
        return true;
    }
}
