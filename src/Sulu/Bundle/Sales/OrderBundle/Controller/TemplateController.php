<?php

namespace Sulu\Bundle\Sales\OrderBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Sulu\Component\Rest\RestController;
use Sulu\Bundle\ContactExtensionBundle\Entity\Account;
use Sulu\Bundle\Sales\CoreBundle\Traits\ItemTableTrait;
use Sulu\Bundle\Sales\OrderBundle\Api\OrderStatus;

class TemplateController extends RestController
{
    use ItemTableTrait;

    protected static $termsOfPaymentEntityName = 'SuluContactExtensionBundle:TermsOfPayment';
    protected static $termsOfDeliveryEntityName = 'SuluContactExtensionBundle:TermsOfDelivery';
    protected static $orderStatusEntityName = 'SuluSalesOrderBundle:OrderStatus';

    /**
     * Returns Template for list.
     *
     * @return Response
     */
    public function orderListAction()
    {
        return $this->render(
            'SuluSalesOrderBundle:Template:order.list.html.twig'
        );
    }

    /**
     * Returns Template for list.
     *
     * @return Response
     */
    public function orderFormAction()
    {
        $locale = $this->getUser()->getLocale();

        return $this->render(
            'SuluSalesOrderBundle:Template:order.form.html.twig',
            array(
                'systemUser' => $this->getSystemUserArray(),
                'systemUserId' => $this->getUser()->getContact()->getId(),
                'termsOfPayment' => $this->getTermsArray(self::$termsOfPaymentEntityName),
                'termsOfDelivery' => $this->getTermsArray(self::$termsOfDeliveryEntityName),
                'orderStatus' => $this->getOrderStatus(),
                'currencies' => $this->getCurrencies($locale),
                'customerId' => Account::TYPE_CUSTOMER,
                'taxClasses' => $this->getTaxClasses($locale),
                'units' => $this->getProductUnits($locale),
            )
        );
    }

    /**
     * Returns all sulu system users.
     *
     * @return array
     */
    public function getSystemUserArray()
    {
        $repo = $this->get('sulu_security.user_repository');
        $users = $repo->findUserBySystem($this->container->getParameter('sulu_security.system'));
        $contacts = [];

        foreach ($users as $user) {
            $contact = $user->getContact();
            $contacts[] = array(
                'id' => $contact->getId(),
                'fullName' => $contact->getFullName()
            );
        }
        return $contacts;
    }

    /**
     * Returns Terms Of Payment / Delivery.
     *
     * @param string $entityName
     *
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

    /**
     * Returns array of order statuses.
     *
     * @return array
     */
    public function getOrderStatus()
    {
        $statuses = $this->getDoctrine()->getRepository(self::$orderStatusEntityName)->findAll();
        $locale = $this->getUser()->getLocale();
        $statusArray = [];

        foreach ($statuses as $statusEntity) {
            $status = new OrderStatus($statusEntity, $locale);
            $statusArray[] = array(
                'id' => $status->getId(),
                'status' => $status->getStatus()
            );
        }
        return $statusArray;
    }
}
