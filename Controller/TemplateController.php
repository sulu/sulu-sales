<?php

namespace Sulu\Bundle\Sales\OrderBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sulu\Component\Rest\RestController;
use Hateoas\Representation\CollectionRepresentation;

class TemplateController extends RestController
{

    /**
     * Returns Template for list
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function orderListAction()
    {
        return $this->render(
            'SuluSalesOrderBundle:Template:order.list.html.twig'
        );
    }


}
