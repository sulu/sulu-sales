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

use Doctrine\ORM\EntityManager;
use Sulu\Bundle\AdminBundle\Widgets\WidgetException;
use Sulu\Bundle\AdminBundle\Widgets\WidgetInterface;
use Sulu\Bundle\ContactBundle\Entity\Contact;
use Sulu\Bundle\ContactBundle\Entity\Address;
use Sulu\Bundle\AdminBundle\Widgets\WidgetParameterException;
use Sulu\Bundle\AdminBundle\Widgets\WidgetEntityNotFoundException;

/**
 * Shippingdetails widget
 *
 * @package Sulu\Bundle\Sales\ShippingBundle\Widgets
 */
class ShippingDetails implements WidgetInterface
{
    protected $em;

    protected $widgetName = 'ShippingDetails';

    /**
     * return name of widget
     *
     * @return string
     */
    public function getName()
    {
        return 'shipping-details';
    }

    /**
     * returns template name of widget
     *
     * @return string
     */
    public function getTemplate()
    {
        return 'SuluSalesShippingBundle:Widgets:shipping.details.html.twig';
    }

    /**
     * returns data to render template
     *
     * @param array $options
     * @throws \Sulu\Bundle\AdminBundle\Widgets\WidgetParameterException
     * @throws \Sulu\Bundle\AdminBundle\Widgets\WidgetEntityNotFoundException
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

            if(array_key_exists('date', $options) &&
                !empty($options['date'])){
                $data['date'] = $options['date'];
            }

            return $data;
        } else {
            throw new WidgetParameterException(
                'Required parameter status not found or empty!',
                $this->widgetName,
                'status'
            );
        }
    }
}
