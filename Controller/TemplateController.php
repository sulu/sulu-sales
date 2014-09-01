<?php

namespace Sulu\Bundle\Sales\ShippingBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sulu\Component\Rest\RestController;
use Hateoas\Representation\CollectionRepresentation;

class TemplateController extends RestController
{

    /**
     * Returns Template for list
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function shippingListAction()
    {
        return $this->render(
            'SuluSalesShippingBundle:Template:shipping.list.html.twig'
        );
    }

    /**
     * Returns Template for list
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function shippingFormAction()
    {
        return $this->render(
            'SuluSalesShippingBundle:Template:shipping.form.html.twig'
        );
    }
}
