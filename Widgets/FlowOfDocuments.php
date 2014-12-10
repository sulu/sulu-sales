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
use Sulu\Bundle\Sales\CoreBundle\SalesDependency\SalesDependencyClassInterface;
use Sulu\Bundle\Sales\CoreBundle\Widgets\FlowOfDocuments as FlowOfDocumentsBase;
use Sulu\Bundle\Sales\OrderBundle\Api\Order;

class FlowOfDocuments extends FlowOfDocumentsBase
{

    /**
     * DependencyManager
     * @var SalesDependencyClassInterface
     */
    protected $dependencyManager;

    protected $routes;

    protected $widgetName = 'OrderFlowOfDocuments';

    function __construct(SalesDependencyClassInterface $dependencyManager, array $routes)
    {
        $this->dependencyManager = $dependencyManager;
        $this->routes = $routes;
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
            $this->fetchDocumentData($options);
            parent::orderDataByDate(false);

            return parent::serializeData();
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
            $options['orderId'],
            $options['orderNumber'],
            'order',
            new DateTime($options['orderDate']),
            parent::getRoute($options['orderId'], 'order', 'details'),
            Order::$pdfBaseUrl
        );
    }

    /**
     * Retrieves document data for a specific order and adds it to the entries
     *
     * @param $options
     * @throws \Sulu\Bundle\AdminBundle\Widgets\WidgetParameterException
     */
    protected function fetchDocumentData($options)
    {
        $documents = $this->dependencyManager->getDocuments($options['orderId'], $options['locale']);
        if (!empty($documents)) {
            /* @var SalesDocument $document */
            foreach ($documents as $document) {
                $data = $document->getSalesDocumentData();
                parent::addEntry(
                    $data['id'],
                    $data['number'],
                    $data['type'],
                    $data['date'],
                    parent::getRoute($data['id'], $data['type'], 'details'),
                    $data['pdfBaseUrl']
                );
            }
        }
    }

    /**
     * @param $options
     * @return bool
     * @throws \Sulu\Bundle\AdminBundle\Widgets\WidgetParameterException
     */
    function checkRequiredParameters($options)
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
