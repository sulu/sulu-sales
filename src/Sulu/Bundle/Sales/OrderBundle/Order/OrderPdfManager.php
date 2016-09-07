<?php
/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */
namespace Sulu\Bundle\Sales\OrderBundle\Order;

use Doctrine\Common\Persistence\ObjectManager;
use Massive\Bundle\PdfBundle\Pdf\PdfManager;
use Sulu\Bundle\Sales\OrderBundle\Api\ApiOrderInterface;
use Sulu\Bundle\Sales\OrderBundle\Entity\Order;

class OrderPdfManager
{
    /**
     * @var PdfManager
     */
    protected $pdfManager;

    /**
     * @var string
     */
    protected $templateHeaderPath;

    /**
     * @var string
     */
    protected $templateFooterPath;

    /**
     * @var string
     */
    protected $templateBasePath;

    /**
     * @var string
     */
    protected $templateMacrosPath;

    /**
     * @var string
     */
    protected $templateConfirmationPath;

    /**
     * @var string
     */
    protected $templateDynamicPath;

    /**
     * @var ObjectManager
     */
    protected $entityManager;

    /**
     * @var string
     */
    protected $websiteLocale;

    /**
     * @var string
     */
    protected $namePrefixDynamicOrder;

    /**
     * @var string
     */
    protected $namePrefixConfirmedOrder;

    /**
     * @param ObjectManager $entityManager
     * @param PdfManager $pdfManager
     * @param string $templateConfirmationPath
     * @param string $templateDynamicPath
     * @param string $templateBasePath
     * @param string $templateHeaderPath
     * @param string $templateFooterPath
     * @param string $templateMacrosPath
     * @param string $locale
     * @param string $namePrefixDynamicOrder
     * @param string $namePrefixConfirmedOrder
     */
    public function __construct(
        ObjectManager $entityManager,
        PdfManager $pdfManager,
        $templateConfirmationPath,
        $templateDynamicPath,
        $templateBasePath,
        $templateHeaderPath,
        $templateFooterPath,
        $templateMacrosPath,
        $locale,
        $namePrefixDynamicOrder,
        $namePrefixConfirmedOrder
    ) {
        $this->entityManager = $entityManager;
        $this->pdfManager = $pdfManager;
        $this->templateConfirmationPath = $templateConfirmationPath;
        $this->templateDynamicPath = $templateDynamicPath;
        $this->templateBasePath = $templateBasePath;
        $this->templateHeaderPath = $templateHeaderPath;
        $this->templateFooterPath = $templateFooterPath;
        $this->templateMacrosPath = $templateMacrosPath;
        $this->websiteLocale = $locale;
        $this->namePrefixDynamicOrder = $namePrefixDynamicOrder;
        $this->namePrefixConfirmedOrder = $namePrefixConfirmedOrder;
    }

    /**
     * @param Order $order
     * @param bool $isOrderConfirmation
     *
     * @return string
     */
    public function getPdfName(Order $order, $isOrderConfirmation = true)
    {
        if ($isOrderConfirmation) {
            return $this->namePrefixConfirmedOrder . $order->getNumber() . '.pdf';
        }

        return $this->namePrefixDynamicOrder . $order->getNumber() . '.pdf';
    }

    /**
     * @param ApiOrderInterface $apiOrder
     *
     * @return file
     */
    public function createOrderConfirmation(ApiOrderInterface $apiOrder)
    {
        return $this->createOrderPdfDynamic(
            $apiOrder,
            $this->templateHeaderPath,
            $this->templateConfirmationPath,    // Since this is the action for confirmation pdfs.
            $this->templateFooterPath
        );
    }

    /**
     * Function to create a pdf for a given order using given templates.
     *
     * @param ApiOrderInterface $apiOrder
     * @param string $templateHeaderPath
     * @param string $templateBasePath
     * @param string $templateMainPath
     * @param string $templateFooterPath
     *
     * @return File
     */
    public function createOrderPdfDynamically(
        ApiOrderInterface $apiOrder,
        $templateHeaderPath = null,
        $templateBasePath = null,
        $templateMainPath = null,
        $templateFooterPath = null
    ) {
        $data = $this->getContentForPdf($apiOrder);

        if ($templateHeaderPath === null) {
            $templateHeaderPath = $this->templateHeaderPath;
        }

        $header = $this->pdfManager->renderTemplate(
            $templateHeaderPath,
            []
        );

        if ($templateFooterPath === null) {
            $templateFooterPath = $this->templateFooterPath;
        }

        $footer = $this->pdfManager->renderTemplate(
            $templateFooterPath,
            []
        );

        if ($templateBasePath === null) {
            $templateBasePath = $this->templateConfirmationPath;
        }
        // Change the base template which will be extended to the confirmation template.
        $data['templateDynamicBasePath'] = $templateBasePath;

        if ($templateMainPath === null) {
            $templateMainPath = $this->templateDynamicPath;
        }

        $pdf = $this->pdfManager->convertToPdf(
            $templateMainPath,
            $data,
            false,
            [
                'footer-html' => $footer,
                'header-html' => $header
            ]
        );

        return $pdf;
    }

    /**
     * Function that sets data array for pdf rendering.
     *
     * @param ApiOrderInterface $apiOrder
     *
     * @return array
     */
    protected function getContentForPdf(ApiOrderInterface $apiOrder)
    {
        $order = $apiOrder->getEntity();

        $customerNumber = null;

        if ($order->getCustomerAccount()) {
            $customerNumber = $order->getCustomerAccount()->getNumber();
        } else {
            $customerNumber = sprintf('%05d', $order->getCustomerContact()->getId());
        }

        $data = [
            'recipient' => $order->getDeliveryAddress(),
            'responsibleContact' => $order->getResponsibleContact(),
            'deliveryAddress' => $order->getDeliveryAddress(),
            'customerContact' => $order->getCustomerContact(),
            'billingAddress' => $order->getInvoiceAddress(),
            'order' => $order,
            'customerNumber' => $customerNumber,
            'orderApiEntity' => $apiOrder,
            'itemApiEntities' => $apiOrder->getItems(),
            'templateBasePath' => $this->templateBasePath,
            'templateMacrosPath' => $this->templateMacrosPath,
            'website_locale' => $this->websiteLocale,
        ];

        return $data;
    }
}
