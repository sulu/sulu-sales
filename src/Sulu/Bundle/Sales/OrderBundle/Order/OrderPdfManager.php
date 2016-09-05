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
    protected $templateUnsubmittedPath;

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
    protected $namePrefixUnsubmittedOrder;

    /**
     * @var string
     */
    protected $namePrefixConfirmedOrder;

    /**
     * @param ObjectManager $entityManager
     * @param PdfManager $pdfManager
     * @param string $templateConfirmationPath
     * @param string $templateUnsubmittedPath
     * @param string $templateBasePath
     * @param string $templateHeaderPath
     * @param string $templateFooterPath
     * @param string $templateMacrosPath
     * @param string $locale
     * @param string $namePrefixUnsubmittedOrder
     * @param string $namePrefixConfirmedOrder
     */
    public function __construct(
        ObjectManager $entityManager,
        PdfManager $pdfManager,
        $templateConfirmationPath,
        $templateUnsubmittedPath,
        $templateBasePath,
        $templateHeaderPath,
        $templateFooterPath,
        $templateMacrosPath,
        $locale,
        $namePrefixUnsubmittedOrder,
        $namePrefixConfirmedOrder
    ) {
        $this->entityManager = $entityManager;
        $this->pdfManager = $pdfManager;
        $this->templateConfirmationPath = $templateConfirmationPath;
        $this->templateUnsubmittedPath = $templateUnsubmittedPath;
        $this->templateBasePath = $templateBasePath;
        $this->templateHeaderPath = $templateHeaderPath;
        $this->templateFooterPath = $templateFooterPath;
        $this->templateMacrosPath = $templateMacrosPath;
        $this->websiteLocale = $locale;
        $this->namePrefixUnsubmittedOrder = $namePrefixUnsubmittedOrder;
        $this->namePrefixConfirmedOrder = $namePrefixConfirmedOrder;
    }

    /**
     * @param Order $order
     *
     * @return string
     */
    public function getPdfName(Order $order, $isOrderSubmitted = true)
    {
        if ($isOrderSubmitted) {
            return $this->namePrefixConfirmedOrder . $order->getNumber() . '.pdf';
        }

        return $this->namePrefixUnsubmittedOrder . $order->getNumber() . '.pdf';
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
     * @param string $template
     *
     * @return file
     */
    public function createOrderPdfDynamic(
        ApiOrderInterface $apiOrder,
        $templateHeaderPath = null,
        $templateBasePath = null,
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
            $templateBasePath = $this->templateUnsubmittedPath;
        }

        $pdf = $this->pdfManager->convertToPdf(
            $templateBasePath,
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
