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

use Doctrine\ORM\EntityManager;
use Sulu\Bundle\AdminBundle\Widgets\WidgetInterface;
use Sulu\Bundle\AdminBundle\Widgets\WidgetParameterException;

/**
 * Orderdetails widget
 *
 * @package Sulu\Bundle\Sales\OrderBundle\Widgets
 */
class OrderDetails implements WidgetInterface
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var string
     */
    protected $widgetName = 'OrderDetails';

    /**
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * Return name of widget
     *
     * @return string
     */
    public function getName()
    {
        return 'order-details';
    }

    /**
     * Returns template name of widget
     *
     * @return string
     */
    public function getTemplate()
    {
        return 'SuluSalesOrderBundle:Widgets:order.details.html.twig';
    }

    /**
     * Returns data to render template
     *
     * @param array $options
     *
     * @throws \Sulu\Bundle\AdminBundle\Widgets\WidgetParameterException
     *
     * @return array
     */
    public function getData($options)
    {
        if (!empty($options) &&
            array_key_exists('status', $options) &&
            !empty($options['status'])
        ) {
            $data = [];
            $data['status'] = $options['status'];

            return $data;
        }

        throw new WidgetParameterException(
            'Required parameter status not found or empty!',
            $this->widgetName,
            'status'
        );
    }
}
