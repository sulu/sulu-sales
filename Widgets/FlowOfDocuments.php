<?php
/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\Sales\OrderBundle\Widgets;

use DateTime;
use Sulu\Bundle\AdminBundle\Widgets\WidgetException;
use Sulu\Bundle\AdminBundle\Widgets\WidgetParameterException;
use Sulu\Bundle\Sales\CoreBundle\Core\SalesDocument;
use Sulu\Bundle\Sales\CoreBundle\Widgets\FlowOfDocuments as FlowOfDocumentsBase;
use Sulu\Bundle\Sales\ShippingBundle\Shipping\ShippingManager;

class FlowOfDocuments extends FlowOfDocumentsBase
{

    protected $widgetName = 'OrderFlowOfDocuments';

    function __construct(ShippingManager $shippingManager)
    {
        $this->shippingManager = $shippingManager;
    }

    /**
     * return name of widget
     *
     * @return string
     */
    public function getName()
    {
        return 'order-flow-of-documents';
    }

    /**
     * returns data to render template
     *
     * @param array $options
     * @throws WidgetException
     * @return array
     */
    public function getData($options)
    {
        if ($this->checkRequiredParameters($options)) {
            $this->getOrderData($options);
            $this->getShippingData($options);
            parent::orderDataByDate();
        } else {
            throw new WidgetException('No params found!', $this->getName());
        }
    }

    /**
     * Retrieves order data
     *
     * @param $options
     * @throws \Sulu\Bundle\AdminBundle\Widgets\WidgetParameterException
     */
    protected function getOrderData($options)
    {
        parent::addEntry(
            array(
                'id' => $options['orderId'],
                'number' => $options['orderNumber'],
                'type' => 'order',
                'date' => new DateTime($options['orderDate'])
            )
        );
    }

    /**
     * Retrieves shipping data for a specific order
     *
     * @param $options
     * @throws \Sulu\Bundle\AdminBundle\Widgets\WidgetParameterException
     */
    protected function getShippingData($options)
    {
        $shippings = $this->shippingManager->findByOrderId($options['orderId'], $options['locale']);
        if (!empty($shippings)) {
            /* @var SalesDocument $shipping */
            foreach ($shippings as $shipping) {
                parent::addEntry($shipping->toArray());
            }
        }
    }

    /**
     * @param $options
     * @return bool
     * @throws \Sulu\Bundle\AdminBundle\Widgets\WidgetParameterException
     */
    protected function checkRequiredParameters($options)
    {
        $attribute = "";
        if (!empty($options)) {

            if (empty($options['orderNumber'])) {
                $attribute = 'orderNumber';
            }

            if (empty($options['orderDate'])) {
                $attribute = 'orderDate';
            }

            if (empty($options['orderId'])) {
                $attribute = 'orderId';
            }

            if (empty($options['locale'])) {
                $attribute = 'locale';
            }

            if (empty($attribute)) {
                return true;
            }

        } else {
            return false;
        }

        throw new WidgetParameterException(
            'Required parameter ' . $attribute . ' not found or invalid!',
            $this->widgetName,
            $attribute
        );

    }
}
