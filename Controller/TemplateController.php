<?php

namespace Sulu\Bundle\Sales\ShippingBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sulu\Component\Rest\RestController;
use Hateoas\Representation\CollectionRepresentation;

class TemplateController extends RestController
{
    protected static $termsOfDeliveryEntityName = 'SuluContactExtensionBundle:TermsOfDelivery';
    protected static $termsOfPaymentEntityName = 'SuluContactExtensionBundle:TermsOfPayment';

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
            'SuluSalesShippingBundle:Template:shipping.form.html.twig',
            array(
                'termsOfDelivery' => $this->getTermsArray(static::$termsOfDeliveryEntityName),
                'termsOfPayment' => $this->getTermsArray(static::$termsOfPaymentEntityName)
            )
        );
    }

    /**
     * returns Terms Of Payment / Delivery
     * @param $entityName
     * @return array
     */
    public function getTermsArray($entityName)
    {
        $terms = $this->getDoctrine()->getRepository($entityName)->findAll();
        $termsArray = [];

        foreach ($terms as $term) {
            $termsArray[] = array(
                'id' => $term->getId(),
                'name' => $term->getTerms()
            );
        }
        return $termsArray;
    }
}
