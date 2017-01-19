<?php

/*
 * This file is part of Sulu.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\Sales\OrderBundle\Controller;

use Sulu\Bundle\Sales\CoreBundle\Manager\LocaleManager;
use Sulu\Bundle\Sales\OrderBundle\Order\Exception\OrderNotFoundException;
use Sulu\Bundle\Sales\OrderBundle\Order\OrderManager;
use Sulu\Bundle\Sales\OrderBundle\Order\OrderPdfManager;
use Sulu\Component\Rest\RestController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PdfController extends RestController
{
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
        $locale = $this->getLocaleManager()->retrieveLocale($this->getUser(), $request->get('locale'));

        $this->get('translator')->setLocale($locale);

        try {
            $orderApiEntity = $this->getOrderManager()->findByIdAndLocale($id, $locale);
            $order = $orderApiEntity->getEntity();
        } catch (OrderNotFoundException $exc) {
            throw new OrderNotFoundException($id);
        }

        $pdf = $this->getOrderPdfManager()->createOrderConfirmation($orderApiEntity);

        // Get pdf name with function parameter isSubmitted = true.
        $pdfName = $this->getOrderPdfManager()->getPdfName($order, true);
        $responseType = $this->container->getParameter('sulu_sales_order.pdf_response_type');

        return new Response(
            $pdf,
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => sprintf('%s; filename="%s"', $responseType, $pdfName),
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

        $orderApiEntity = $this->getOrderManager()->findByIdAndLocale($id, $locale);
        $order = $orderApiEntity->getEntity();

        // Get pdf file from manager. Function parameter fallbacks are sufficient.
        $pdf = $this->getOrderPdfManager()->createOrderPdfDynamically($orderApiEntity);

        // Get the filename with parameter: isSubmitted = false since this is the unsubmitted case.
        $pdfName = $this->getOrderPdfManager()->getPdfName($order, false);

        // Get the response type defined in the parameters.yml.
        $responseType = $this->container->getParameter('sulu_sales_order.pdf_response_type');

        return new Response(
            $pdf,
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => sprintf('%s; filename="%s"', $responseType, $pdfName),
            ]
        );
    }

    /**
     * @return OrderManager
     */
    protected function getOrderManager()
    {
        return $this->get('sulu_sales_order.order_manager');
    }

    /**
     * @return OrderPdfManager
     */
    protected function getOrderPdfManager()
    {
        return $this->get('sulu_sales_order.order_pdf_manager');
    }

    /**
     * @return LocaleManager
     */
    protected function getLocaleManager()
    {
        return $this->get('sulu_sales_core.locale_manager');
    }
}
