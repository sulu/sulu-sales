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
use \Sulu\Bundle\Sales\OrderBundle\Api\Order as ApiOrder;

class OrderPdfManager
{
    /**
     * @var PdfManager
     */
    protected $pdfManager;

    /**
     * @param PdfManager $pdfManager
     */
    public function __construct(PdfManager $pdfManager)
    {
        $this->pdfManager = $pdfManager;
    }

    /**
     * @param $order
     * @return string
     */
    public function getPdfName($order)
    {
        $pdfName = 'PA_OrderConfirmation-' . $order->getNumber() . '.pdf';

        return $pdfName;
    }

    /**
     * @param ApiOrder $apiOrder
     * @return file
     */
    public function createOrderConfirmation(ApiOrder $apiOrder)
    {
        $order = $apiOrder->getEntity();

        $data = array(
            'recipient' => $order->getDeliveryAddress(),
            'responsibleContact' => $order->getResponsibleContact(),
            'deliveryAddress' => $order->getInvoiceAddress(),
            'order' => $order,
            'orderApiEntity' => $apiOrder,
            'itemApiEntities' => $apiOrder->getItems(),
        );

        $header = $this->pdfManager->renderTemplate(
            'SuluSalesCoreBundle:Default:pdf-base-header.html.twig',
            array()
        );

        $footer = $this->pdfManager->renderTemplate(
            'SuluSalesCoreBundle:Default:pdf-base-footer.html.twig',
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
