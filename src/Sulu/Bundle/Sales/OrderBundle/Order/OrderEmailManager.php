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

use JMS\Serializer\SerializationContext;
use JMS\Serializer\Serializer;
use Sulu\Bundle\Sales\CoreBundle\Manager\EmailManager;
use Sulu\Bundle\Sales\OrderBundle\Api\ApiOrderInterface;
use Sulu\Component\Contact\Model\ContactInterface;

class OrderEmailManager extends EmailManager
{
    /**
     * @var OrderPdfManager
     */
    protected $pdfManager;

    /**
     * @var Serializer
     */
    protected $serializer;

    /**
     * @var string
     */
    protected $templateCustomerConfirmationPath;

    /**
     * @var string
     */
    protected $templateShopownerConfirmationPath;

    /**
     * @var bool
     */
    protected $sendCustomerEmailConfirmation;

    /**
     * @var bool
     */
    protected $sendShopownerEmailConfirmation;

    /**
     * @var string
     */
    protected $rootPath;

    /**
     * @param \Twig_Environment $twig
     * @param \Swift_Mailer $mailer
     * @param OrderPdfManager $pdfManager
     * @param Serializer $serializer
     * @param string $emailFrom
     * @param string $confirmationRecipientEmailAddress
     * @param string $templateCustomerConfirmationPath
     * @param string $templateShopownerConfirmationPath
     * @param string $templateFooterHtmlPath
     * @param string $templateFooterTxtPath
     * @param bool $sendEmailConfirmationToShopowner
     * @param bool $sendEmailConfirmationToCustomer
     * @param string $rootPath
     */
    public function __construct(
        \Twig_Environment $twig,
        \Swift_Mailer $mailer,
        OrderPdfManager $pdfManager,
        Serializer $serializer,
        $emailFrom,
        $confirmationRecipientEmailAddress,
        $templateCustomerConfirmationPath,
        $templateShopownerConfirmationPath,
        $templateFooterHtmlPath,
        $templateFooterTxtPath,
        $sendEmailConfirmationToShopowner,
        $sendEmailConfirmationToCustomer,
        $rootPath
    ) {
        // Services.
        $this->twig = $twig;
        $this->mailer = $mailer;
        $this->pdfManager = $pdfManager;
        $this->serializer = $serializer;
        // Email addresses.
        $this->emailFrom = $emailFrom;
        $this->confirmationRecipientEmailAddress = $confirmationRecipientEmailAddress;
        // Templates.
        $this->templateCustomerConfirmationPath = $templateCustomerConfirmationPath;
        $this->templateShopownerConfirmationPath = $templateShopownerConfirmationPath;
        $this->templateFooterTxtPath = $templateFooterTxtPath;
        $this->templateFooterHtmlPath = $templateFooterHtmlPath;
        // Define if emails should be sent.
        $this->sendEmailConfirmationToShopowner = $sendEmailConfirmationToShopowner;
        $this->sendEmailConfirmationToCustomer = $sendEmailConfirmationToCustomer;
        // RootPath.
        $this->rootPath = $rootPath;
    }

    /**
     * Sends a confirmation email to the shop-owner.
     *
     * @param null|string $recipient
     * @param ApiOrderInterface $apiOrder
     * @param ContactInterface $customerContact
     *
     * @return bool
     */
    public function sendShopownerConfirmation(
        $recipient,
        ApiOrderInterface $apiOrder,
        ContactInterface $customerContact = null
    ) {
        if (!$this->sendEmailConfirmationToShopowner) {
            return false;
        }

        if (empty($recipient)) {
            // fallback address for shop-owner order confirmations
            $recipient = $this->confirmationRecipientEmailAddress;
        }

        return $this->sendConfirmationEmail(
            $recipient,
            $apiOrder,
            $this->templateShopownerConfirmationPath,
            $customerContact
        );
    }

    /**
     * Sends a confirmation email to the customer.
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
        if (!$this->sendEmailConfirmationToCustomer) {
            return false;
        }

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

        $tmplData = [
            'order' => $apiOrder,
            'contact' => $customerContact,
        ];

        return $this->sendOrderMail($recipient, $templatePath, $tmplData, $apiOrder);
    }

    /**
     * Sends an email.
     *
     * @param string $recipient
     * @param string $templatePath
     * @param array $data
     * @param ApiOrderInterface $apiOrder
     * @param array $blindCopyRecipients Recipients to send bcc
     * @param bool $sendXMLOrder
     *
     * @return bool
     */
    public function sendOrderMail(
        $recipient,
        $templatePath,
        array $data = [],
        ApiOrderInterface $apiOrder = null,
        array $blindCopyRecipients = [],
        $sendXMLOrder = false
    ) {
        $tmplData = array_merge(
            $data,
            [
                'footerTxt' => $this->templateFooterTxtPath,
                'footerHtml' => $this->templateFooterHtmlPath,
            ]
        );

        $template = $this->twig->loadTemplate($templatePath);

        // Merge twig globals so that they also are available in renderBlock.
        $tmplData = $this->twig->mergeGlobals($tmplData);

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

        // Add blind copy recipients.
        foreach ($blindCopyRecipients as $bcc) {
            $message->addBcc($bcc);
        }

        // Add attachments like pdf and xml to email.
        $this->applyOrderAttachments($message, $apiOrder, $sendXMLOrder);

        $failedRecipients = [];
        $this->mailer->send($message, $failedRecipients);

        if (count($failedRecipients) > 0) {
            $this->writeLog('Could not send mail to the following recipients: ' . join(', ', $failedRecipients));

            return false;
        }

        return true;
    }

    /**
     * Function adds attachments to the email message.
     *
     * @param \Swift_Message $message
     * @param ApiOrderInterface $apiOrder
     * @param bool $sendXMLOrder
     */
    protected function applyOrderAttachments(
        \Swift_Message $message,
        ApiOrderInterface $apiOrder = null,
        $sendXMLOrder = false
    ) {
        if ($apiOrder) {
            $pdf = $this->pdfManager->createOrderConfirmation($apiOrder);
            $pdfFileName = $this->pdfManager->getPdfName($apiOrder);
            $attachment = \Swift_Attachment::newInstance()
                ->setFilename($pdfFileName)
                ->setContentType('application/pdf')
                ->setBody($pdf);
            $message->attach($attachment);

            if ($sendXMLOrder) {
                $message->attach($this->createXMLAttachment($apiOrder));
            }
        }
    }

    /**
     * @param ApiOrderInterface $apiOrder
     *
     * @return \Swift_Mime_Attachment
     */
    protected function createXMLAttachment(ApiOrderInterface $apiOrder)
    {
        $xmlFilename = 'PA_OrderConfirmation-' . $apiOrder->getNumber() . '.xml';
        $context = SerializationContext::create()->setGroups(['xmlOrder']);
        $context->setSerializeNull(true);
        $serialized = $this->serializer->serialize($apiOrder, 'xml', $context);

        return \Swift_Attachment::newInstance($serialized, $xmlFilename, 'application/xml');
    }
}
