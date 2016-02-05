<?php

namespace Sulu\Bundle\Sales\OrderBundle\Controller;

use Sulu\Bundle\ProductBundle\Entity\TaxClass;
use Symfony\Component\HttpFoundation\Response;
use Sulu\Bundle\ContactExtensionBundle\Entity\Account;
use Sulu\Bundle\ProductBundle\Api\Currency;
use Sulu\Bundle\Sales\OrderBundle\Api\OrderStatus;
use Sulu\Component\Rest\RestController;

class TemplateController extends RestController
{
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
        $users = $repo->getUserInSystem();
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

    /**
     * Returns all currencies.
     *
     * @param string $locale
     *
     * @return array
     */
    private function getCurrencies($locale)
    {
        /** @var Currency[] $currencies */
        $currencies = $this->get('sulu_product.currency_manager')->findAll($locale);

        $currencyValues = array();

        foreach ($currencies as $currency) {
            $currencyValues[] = array(
                'id' => $currency->getId(),
                'name' => $currency->getName(),
                'code' => $currency->getCode()
            );
        }

        return $currencyValues;
    }

    /**
     * Returns all tax classes.
     *
     * @param string $locale
     *
     * @return array
     */
    private function getTaxClasses($locale)
    {
        /** @var TaxClass[] $taxClasses */
        $taxClasses = $this->get('sulu_product.tax_class_repository')->findAll();

        $result = [];

        foreach ($taxClasses as $taxClass) {
            $result[] = [
                'id' => $taxClass->getId(),
                'name' => $taxClass->getTranslation($locale),
            ];
        }

        return $result;
    }

    /**
     * Returns all product units.
     *
     * @param string $locale
     *
     * @return array
     */
    private function getProductUnits($locale)
    {
        /** @var TaxClass[] $taxClasses */
        $productUnits = $this->get('sulu_product.unit_repository')->findAll();

        $result = [];

        foreach ($productUnits as $productUnit) {
            $result[] = [
                'id' => $productUnit->getId(),
                'name' => $productUnit->getTranslation($locale),
            ];
        }

        return $result;
    }
}
