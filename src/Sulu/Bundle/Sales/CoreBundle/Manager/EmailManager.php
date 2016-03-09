<?php
/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */
namespace Sulu\Bundle\Sales\CoreBundle\Manager;

use Sulu\Bundle\ContactBundle\Entity\AccountInterface;
use Sulu\Component\Contact\Model\ContactInterface;
use Sulu\Component\Security\Authentication\UserInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class EmailManager
{
    /**
     * @var \Twig_Environment
     */
    protected $twig;

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
    protected $rootPath;

    /**
     * @param \Twig_Environment $twig
     * @param \Swift_Mailer $mailer
     * @param string $emailFrom
     * @param string $templateFooterHtmlPath
     * @param string $templateFooterTxtPath
     * @param string $rootPath
     */
    public function __construct(
        \Twig_Environment $twig,
        \Swift_Mailer $mailer,
        $emailFrom,
        $templateFooterHtmlPath,
        $templateFooterTxtPath,
        $rootPath
    ) {
        // services
        $this->twig = $twig;
        $this->mailer = $mailer;

        // email addresses
        $this->emailFrom = $emailFrom;

        // templates
        $this->templateFooterTxtPath = $templateFooterTxtPath;
        $this->templateFooterHtmlPath = $templateFooterHtmlPath;

        $this->rootPath = $rootPath;
    }

    /**
     * Sends an email
     *
     * @param string $recipient
     * @param string $templatePath Path to the twig template
     * @param array $data
     * @param array $blindCopyRecipients Recipients to send bcc
     *
     * @return bool
     */
    public function sendMail(
        $recipient,
        $templatePath,
        $data = array(),
        $blindCopyRecipients = array()
    ) {
        $tmplData = array_merge(
            $data,
            array(
                'footerTxt' => $this->templateFooterTxtPath,
                'footerHtml' => $this->templateFooterHtmlPath,
            )
        );

        // load template from twig
        $template = $this->twig->loadTemplate($templatePath);
        // get subject from block
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
        
        // add blind copy recipients
        foreach ($blindCopyRecipients as $bcc) {
            $message->addBcc($bcc);
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
     * Get Email address of a user; fallback to contact / account if not defined
     *
     * @param UserInterface $user
     * @param bool $useFallback
     *
     * @return null|string
     */
    public function getEmailAddressOfUser(UserInterface $user, $useFallback = true)
    {
        // take users email address
        $userEmail = $user->getEmail();
        if ($userEmail) {
            return $userEmail;
        }

        // fallback: get contacts / accounts main-email
        $contact = $user->getContact();
        if ($useFallback && $contact) {
            return $this->getEmailAddressOfContact($contact);
        }

        return null;
    }

    /**
     * Gets email address of a contact
     *
     * @param ContactInterface $contact
     * @param bool $useFallback
     *
     * @return string|null
     */
    public function getEmailAddressOfContact(ContactInterface $contact, $useFallback = true)
    {
        // take contacts main-email
        $contactMainEmail = $contact->getMainEmail();
        if ($contact && $contactMainEmail) {
            return $contactMainEmail;
        }

        // fallback take contact's main-account main-email
        $account = $contact->getMainAccount();
        if ($useFallback && $account) {
            return $this->getEmailAddressOfAccount($account);
        }

        return null;
    }

    /**
     * Get Email-Address of account
     *
     * @param AccountInterface $account
     *
     * @return string|null
     */
    public function getEmailAddressOfAccount(AccountInterface $account)
    {
        $accountMainEmail = $account->getMainEmail();
        if ($accountMainEmail) {
            return $accountMainEmail;
        }

        return null;
    }

    /**
     * Writes a new line to mail error log
     *
     * @param string $message
     */
    protected function writeLog($message)
    {
        $fileName = $this->rootPath . '/logs/mail/error.log';

        $log = sprintf("[%s]: %s\n", date_format(new \DateTime(), 'Y-m-d H:i:s'), $message);
        file_put_contents($fileName, $log, FILE_APPEND);
    }
}
