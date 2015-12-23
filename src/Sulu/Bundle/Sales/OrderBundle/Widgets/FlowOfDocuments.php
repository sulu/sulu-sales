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
            'fa-shopping-cart',
            new DateTime($options['orderDate']),
            parent::getRoute($options['orderId'], 'order', 'details'),
            parent::getRoute($options['orderId'], 'order', 'pdf'),
            'salesorder.order'
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
                    $this->getProperty($data, 'id'),
                    $this->getProperty($data, 'number'),
                    $this->getProperty($data, 'icon'),
                    $this->getProperty($data, 'date'),
                    parent::getRoute($data['id'], $data['type'], 'details'),
                    $this->getProperty($data, 'pdfBaseUrl'),
                    $this->getProperty($data, 'translationKey')
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

    /**
     * Returns value from array if key exists, else returns default
     *
     * @param array $array
     * @param string $key
     * @param mixed $default
     *
     * @return mixed
     */
    public function getProperty($array, $key, $default = null)
    {
        if (isset($array[$key])) {
            return $array[$key];
        }

        return $default;
    }
}
