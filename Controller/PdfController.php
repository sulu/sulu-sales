<?php
/*
 * This file is part of the Sulu CMF.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\Sales\OrderBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Sulu\Component\Rest\RestController;
use Sulu\Bundle\Sales\OrderBundle\Order\Exception\OrderNotFoundException;

class PdfController extends RestController
{

    /**
     * @return OrderManager
     */
    private function getOrderManager()
    {
        return $this->get('sulu_sales_order.order_manager');
    }

    /**
     * @return PdfMananger
     */
    private function getPdfManager()
    {
        return $this->get('massive_pdf.pdf_manager');
    }

    /**
     * returns a baseURL for the current host
     * @param request
     * @return string
     */
    public function getBaseUrl($request)
    {
        return $request->getScheme() . '://' . $request->getHost();
    }

    /**
     * Finds a order object by a given id from the url
     * and returns a rendered pdf in a download window
     * @param Request $request
     * @param $id
     * @return Response
     */
    public function orderConfirmationAction(Request $request, $id)
    {
        $locale = $this->getLocale($request);

        try {
            $order = $this->getOrderManager()->findByIdAndLocale($id, $locale)->getEntity();
        } catch(OrderNotFoundException $exc) {
            throw new OrderNotFoundException($id);
        }

        $data = array(
            'baseUrl' => $this->getBaseUrl($request),
            'recipient' => $order->getInvoiceAddress(),
            'responsibleContact' => $order->getResponsibleContact(),
            'deliveryAddress' => $order->getInvoiceAddress(),
            'items' => $order->getItems(),
            'order' => $order
        );

        $footer = $this->getPdfManager()->renderTemplate(
            'PoolAlpinBaseBundle:Default:pdf-base-footer.html.twig',
            array(
                'baseUrl' => $this->getBaseUrl($request),
            )
        );

        $pdf = $this->getPdfManager()->convertToPdf(
            'SuluSalesOrderBundle:Template:order.confirmation.pdf.html.twig',
            $data,
            false,
            array('footer-html' => $footer)
        );

        return new Response(
            $pdf,
            200,
            array(
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="file.pdf"'
            )
        );
    }
}
