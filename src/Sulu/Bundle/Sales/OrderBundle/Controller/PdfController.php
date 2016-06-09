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
     * Finds a order object by a given id from the url
     * and returns a rendered pdf in a download window
     *
     * @param Request $request
     * @param $id
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

        $pdfName = $this->getPdfManager()->getPdfName($order);

        return new Response(
            $pdf,
            200,
            array(
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $pdfName . '"'
            )
        );
    }
}
