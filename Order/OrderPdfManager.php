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

use Massive\Bundle\PdfBundle\Pdf\PdfManager;
use Sulu\Bundle\Sales\OrderBundle\Api\ApiOrderInterface;

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
     * @param PdfManager $pdfManager
     * @param string $templateBasePath
     * @param string $templateHeaderPath
     * @param string $templateFooterPath
     * @param string $templateMacrosPath
     */
    public function __construct(
        PdfManager $pdfManager,
        $templateBasePath,
        $templateHeaderPath,
        $templateFooterPath,
        $templateMacrosPath
    ) {
        $this->pdfManager = $pdfManager;
        $this->templateHeaderPath = $templateHeaderPath;
        $this->templateFooterPath = $templateFooterPath;
        $this->templateBasePath = $templateBasePath;
        $this->templateMacrosPath = $templateMacrosPath;
    }

    /**
     * @param ApiOrderInterface $order
     *
     * @return string
     */
    public function getPdfName($order)
    {
        $pdfName = 'PA_OrderConfirmation-' . $order->getNumber() . '.pdf';

        return $pdfName;
    }

    /**
     * @param ApiOrderInterface $apiOrder
     *
     * @return file
     */
    public function createOrderConfirmation(ApiOrderInterface $apiOrder)
    {
        $order = $apiOrder->getEntity();

        $data = array(
            'recipient' => $order->getDeliveryAddress(),
            'responsibleContact' => $order->getResponsibleContact(),
            'deliveryAddress' => $order->getInvoiceAddress(),
            'order' => $order,
            'orderApiEntity' => $apiOrder,
            'itemApiEntities' => $apiOrder->getItems(),
            'templateBasePath' => $this->templateBasePath,
            'templateMacrosPath' => $this->templateMacrosPath,
        );

        $header = $this->pdfManager->renderTemplate(
            $this->templateHeaderPath,
            array()
        );

        $footer = $this->pdfManager->renderTemplate(
            $this->templateFooterPath,
            array()
        );

        $pdf = $this->pdfManager->convertToPdf(
            'SuluSalesOrderBundle:Template:order.confirmation.pdf.html.twig',
            $data,
            false,
            array(
                'footer-html' => $footer,
                'header-html' => $header
            )
        );

        return $pdf;
    }
}
