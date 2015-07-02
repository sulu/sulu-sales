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
use Sulu\Component\Contact\Model\ContactInterface;

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
     * @param string $confirmationRecipientEmailAddress
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
        $confirmationRecipientEmailAddress,
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
        $this->confirmationRecipientEmailAddress = $confirmationRecipientEmailAddress;
        // templates
        $this->templateCustomerConfirmationPath = $templateCustomerConfirmationPath;
        $this->templateShopOwnerConfirmationPath = $templateShopOwnerConfirmationPath;
        $this->templateFooterTxtPath = $templateFooterTxtPath;
        $this->templateFooterHtmlPath = $templateFooterHtmlPath;
    }

    /**
     * Sends a confirmation email to the shop-owner
     *
     * @param null|string $recipient
     * @param ApiOrderInterface $apiOrder
     * @param ContactInterface $customerContact
     *
     * @return bool
     */
    public function sendShopOwnerConfirmation(
        $recipient,
        ApiOrderInterface $apiOrder,
        ContactInterface $customerContact = null
    ) {
        if (empty($recipient)) {
            // fallback address for shop-owner order confirmations
            $recipient = $this->confirmationRecipientEmailAddress;
        }

        return $this->sendConfirmationEmail(
            $recipient,
            $apiOrder,
            $this->templateShopOwnerConfirmationPath,
            $customerContact
        );
    }

    /**
     * Sends a confirmation email to the customer
     *
     * @param string $recipient
     * @param ApiOrderInterface $apiOrder
     * @param ContactInterface $customerContact
     *
     * @return bool
     */
    public function sendCustomerConfirmation(
        $recipient,
        ApiOrderInterface $apiOrder,
        ContactInterface $customerContact = null
    ) {
        return $this->sendConfirmationEmail(
            $recipient,
            $apiOrder,
            $this->templateCustomerConfirmationPath,
            $customerContact
        );
    }

    /**
     * @param string $recipient The email-address of the customer
     * @param ApiOrderInterface $apiOrder
     * @param string $templatePath Template to render
     * @param ContactInterface|null $customerContact
     *
     * @return bool
     */
    public function sendConfirmationEmail(
        $recipient,
        ApiOrderInterface $apiOrder,
        $templatePath,
        ContactInterface $customerContact = null
    ) {
        if (empty($recipient)) {
            $this->writeLog('No recipient specified.');

            return false;
        }

        $tmplData = array(
            'order' => $apiOrder,
            'contact' => $customerContact,
        );

        return $this->sendMail($recipient, $templatePath, $tmplData, $apiOrder);
    }

    /**
     * Sends an email
     *
     * @param string $recipient
     * @param string $templatePath
     * @param array $data
     * @param ApiOrderInterface $apiOrder
     *
     * @return bool
     */
    public function sendMail(
        $recipient,
        $templatePath,
        $data = array(),
        ApiOrderInterface $apiOrder = null
    ) {
        $tmplData = array_merge(
            $data,
            array(
                'footerTxt' => $this->templateFooterTxtPath,
                'footerHtml' => $this->templateFooterHtmlPath,
            )
        );

        $template = $this->twig->loadTemplate($templatePath);
        $subject = $template->renderBlock('subject', $tmplData);

        $emailBodyText = $template->renderBlock('body_text', $tmplData);
        $emailBodyHtml = $template->renderBlock('body_html', $tmplData);

        /** @var \Swift_Message $message */
        $message = \Swift_Message::newInstance()
            ->setSubject($subject)
            ->setFrom($this->emailFrom)
            ->setTo($recipient)
            ->setBody($emailBodyText, 'text/plain')
            ->addPart($emailBodyHtml, 'text/html');

        // add pdf if order is supplied
        if ($apiOrder) {
            $pdf = $this->pdfManager->createOrderConfirmation($apiOrder);
            $pdfFileName = $this->pdfManager->getPdfName($apiOrder);
            // now send mail
            $attachment = \Swift_Attachment::newInstance()
                ->setFilename($pdfFileName)
                ->setContentType('application/pdf')
                ->setBody($pdf);
            $message->attach($attachment);
        }

        $failedRecipients = array();
        $this->mailer->send($message, $failedRecipients);

        if (count($failedRecipients) > 0) {
            $this->writeLog('Could not send mail to the following recipients: ' . join(', ', $failedRecipients));

            return false;
        }

        return true;
    }

    /**
     * Writes a new line to mail error log
     *
     * @param string $message
     */
    private function writeLog($message)
    {
        $fileName = 'app/logs/mail/error.log';

        $log = sprintf("[%s]: %s\n", date_format(new \DateTime(), 'Y-m-d H:i:s'), $message);
        file_put_contents($fileName, $log, FILE_APPEND);
    }
}
