<?php

namespace Sulu\Bundle\Sales\OrderBundle\Controller;

use Sulu\Bundle\ContactExtensionBundle\Entity\Account;
use Sulu\Bundle\Sales\CoreBundle\Manager\CustomerTypeManager;
use Sulu\Bundle\Sales\CoreBundle\Traits\ItemTableTrait;
use Sulu\Bundle\Sales\OrderBundle\Api\OrderStatus;
use Sulu\Component\Rest\RestController;
use Symfony\Component\HttpFoundation\Response;

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
            [
                'systemUser' => $this->getSystemUserArray(),
                'systemUserId' => $this->getUser()->getContact()->getId(),
                'termsOfPayment' => $this->getTermsArray(self::$termsOfPaymentEntityName),
                'termsOfDelivery' => $this->getTermsArray(self::$termsOfDeliveryEntityName),
                'orderStatus' => $this->getOrderStatus(),
                'currencies' => $this->getCurrencies($locale),
                'customerId' => Account::TYPE_CUSTOMER,
                'taxClasses' => $this->getTaxClasses($locale),
                'units' => $this->getProductUnits($locale),
                'customerTypes' => $this->getCustomerTypesManager()->retrieveAllAsArray($locale),
                'customerTypeDefault' => $this->getCustomerTypesManager()->retrieveDefault($locale)
            ]
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
            $contacts[] = [
                'id' => $contact->getId(),
                'fullName' => $contact->getFullName()
            ];
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
            $termsArray[] = [
                'id' => $term->getId(),
                'name' => $term->getTerms()
            ];
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
            $statusArray[] = [
                'id' => $status->getId(),
                'status' => $status->getStatus()
            ];
        }

        return $statusArray;
    }

    /**
     * @return CustomerTypeManager
     */
    private function getCustomerTypesManager()
    {
        return $this->get('sulu_sales_core.customer_types_manager');
    }
}
