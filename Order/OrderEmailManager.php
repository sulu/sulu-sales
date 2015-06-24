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

use Sulu\Bundle\Sales\OrderBundle\Api\ApiOrderInterface;

class OrderEmailManager
{
    /**
     * @var \Twig_Environment
     */
    protected $twig;

    /**
     * @var OrderPdfManager
     */
    protected $pdfManager;

    /**
     * @var \Swift_Mailer
     */
    protected $mailer;

    /**
     * @var string
     */
    protected $templateFooterHtmlPath;

    /**
     * @var string
     */
    protected $templateFooterTxtPath;

    /**
     * @var string
     */
    protected $templateCustomerConfirmationPath;

    /**
     * @var string
     */
    protected $templateShopOwnerConfirmationPath;

    /**
     * @param \Twig_Environment $twig
     * @param \Swift_Mailer $mailer
     * @param OrderPdfManager $pdfManager
     * @param string $emailFrom
     * @param string $emailConfirmationTo
     * @param string $templateCustomerConfirmationPath
     * @param string $templateShopOwnerConfirmationPath
     * @param string $templateFooterHtmlPath
     * @param string $templateFooterTxtPath
     */
    public function __construct(
        \Twig_Environment $twig,
        \Swift_Mailer $mailer,
        OrderPdfManager $pdfManager,
        $emailFrom,
        $emailConfirmationTo,
        $templateCustomerConfirmationPath,
        $templateShopOwnerConfirmationPath,
        $templateFooterHtmlPath,
        $templateFooterTxtPath
    ) {
        // services
        $this->twig = $twig;
        $this->mailer = $mailer;
        $this->pdfManager = $pdfManager;
        // email addresses
        $this->emailFrom = $emailFrom;
        $this->emailConfirmationTo = $emailConfirmationTo;
        // templates
        $this->templateCustomerConfirmationPath = $templateCustomerConfirmationPath;
        $this->templateShopOwnerConfirmationPath = $templateShopOwnerConfirmationPath;
        $this->templateFooterTxtPath = $templateFooterTxtPath;
        $this->templateFooterHtmlPath = $templateFooterHtmlPath;
    }

    /**
     * Sends a confirmation email to the shop-owner
     *
     * @param string $recipient
     * @param ApiOrderInterface $apiOrder
     * @param Contact $customerContact
     */
    public function sendShopOwnerConfirmation($recipient, ApiOrderInterface $apiOrder, Contact $customerContact = null)
    {
        $this->sendConfirmationEmail($recipient, $apiOrder, $this->templateShopOwnerConfirmationPath, $customerContact);
    }

    /**
     * Sends a confirmation email to the customer
     *
     * @param string $recipient
     * @param ApiOrderInterface $apiOrder
     * @param Contact $customerContact
     */
    public function sendCustomerConfirmation($recipient, ApiOrderInterface $apiOrder, Contact $customerContact = null)
    {
        $this->sendConfirmationEmail($recipient, $apiOrder, $this->templateCustomerConfirmationPath, $customerContact);
    }

    /**
     * @param string $recipient The email-address of the customer
     * @param ApiOrderInterface $apiOrder
     * @param string $templatePath Template to render
     * @param Contact|null $customerContact
     *
     * @return bool
     */
    public function sendConfirmationEmail(
        $recipient,
        ApiOrderInterface $apiOrder,
        $templatePath,
        Contact $customerContact = null
    ) {
        if (empty($recipient)) {
            return false;
        }

        $tmplData = array(
            'order' => $apiOrder,
            'contact' => $customerContact,
            'footerTxt' => $this->templateFooterTxtPath,
            'footerHtml' => $this->templateFooterHtmlPath,
        );

        $template = $this->twig->loadTemplate($templatePath);
        $subject = $template->renderBlock('subject', $tmplData);

        $emailBodyText = $template->renderBlock('body_text', $tmplData);
        $emailBodyHtml = $template->renderBlock('body_html', $tmplData);

        $pdf = $this->pdfManager->createOrderConfirmation($apiOrder);
        $pdfFileName = $this->pdfManager->getPdfName($apiOrder);

        if ($recipient) {
            // now send mail
            $attachment = \Swift_Attachment::newInstance()
                ->setFilename($pdfFileName)
                ->setContentType('application/pdf')
                ->setBody($pdf);

            /** @var \Swift_Message $message */
            $message = \Swift_Message::newInstance()
                ->setSubject($subject)
                ->setFrom($this->emailFrom)
                ->setTo($recipient)
                ->setBody($emailBodyText, 'text/plain')
                ->addPart($emailBodyHtml, 'text/html')
                ->attach($attachment);

            return $this->mailer->send($message);
        }

        return false;
    }
}
