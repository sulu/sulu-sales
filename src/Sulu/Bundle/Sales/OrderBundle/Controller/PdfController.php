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
        return $this->get('sulu_sales_order.order_pdf_manager');
    }

    /**
     * Finds an order object by a given id from the url and returns a rendered pdf in a download window.
     *
     * @param Request $request
     * @param int $id
     *
     * @throws OrderNotFoundException
     *
     * @return Response
     */
    public function orderConfirmationAction(Request $request, $id)
    {
        $locale = $this->getLocale($request);

        $this->get('translator')->setLocale($locale);

        try {
            $orderApiEntity = $this->getOrderManager()->findByIdAndLocale($id, $locale);
            $order = $orderApiEntity->getEntity();
        } catch (OrderNotFoundException $exc) {
            throw new OrderNotFoundException($id);
        }

        $pdf = $this->getPdfManager()->createOrderConfirmation($orderApiEntity);

        // Get pdf name with function parameter isSubmitted = true.
        $pdfName = $this->getPdfManager()->getPdfName($order, true);
        $responseType = $this->container->getParameter('sulu_sales_order.pdf_response_type');

        return new Response(
            $pdf,
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => sprintf('%s; filename="%s"', $responseType, $pdfName)
            ]
        );
    }

    /**
     * Finds an order object by a given id from the url and renders a configurable template.
     * Then returns it as pdf in a new tab.
     *
     * @param Request $request
     * @param int $id
     *
     * @throws OrderNotFoundException
     *
     * @return Response
     */
    public function orderPdfAction(Request $request, $id)
    {
        $locale = $request->getLocale();

        $this->get('translator')->setLocale($locale);

        $orderRepository = $this->get('sulu_sales_order.order_repository');

        $orderApiEntity = $this->getOrderManager()->findByIdAndLocale($id, $locale);
        $order = $orderApiEntity->getEntity();

        // Get pdf file from manager. Function parameter fallbacks are sufficient.
        $pdf = $this->getPdfManager()->createOrderPdfDynamically($orderApiEntity);

        // Get the filename with parameter: isSubmitted = false since this is the unsubmitted case.
        $pdfName = $this->getPdfManager()->getPdfName($order, false);

        // Get the response type defined in the parameters.yml.
        $responseType = $this->container->getParameter('sulu_sales_order.pdf_response_type');

        return new Response(
            $pdf,
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => sprintf('%s; filename="%s"', $responseType, $pdfName)
            ]
        );
    }
}
