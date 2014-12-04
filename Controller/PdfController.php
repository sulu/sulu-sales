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

class PdfController extends Controller
{
    private function getPdfManager()
    {
        return $this->get('massive_pdf.pdf_manager');
    }

    public function orderConfirmationAction() {
        $pdf = $this->getPdfManager()->convertToPdf(
            'SuluSalesOrderBundle:Template:order.confirmation.pdf.html.twig',
            array(),
            false
        );

        return new Response($pdf);
    }

}