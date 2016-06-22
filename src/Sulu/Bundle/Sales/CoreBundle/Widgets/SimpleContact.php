<?php
/*
  * This file is part of the Sulu CMS.
  *
  * (c) MASSIVE ART WebServices GmbH
  *
  * This source file is subject to the MIT license that is bundled
  * with this source code in the file LICENSE.
  */

namespace Sulu\Bundle\Sales\CoreBundle\Widgets;

use Sulu\Bundle\AdminBundle\Widgets\WidgetInterface;
use Sulu\Bundle\AdminBundle\Widgets\WidgetException;
use Sulu\Bundle\AdminBundle\Widgets\WidgetParameterException;
use Sulu\Bundle\AdminBundle\Widgets\WidgetEntityNotFoundException;
use Doctrine\ORM\EntityManager;
use Sulu\Bundle\ContactBundle\Entity\Account;

/**
 * SimpleContact widget
 *
 * @package Sulu\Bundle\Sales\CoreBundle\Widgets
 */
class SimpleContact implements WidgetInterface
{
    protected $em;

    protected $widgetName = 'SimpleContact';
    protected $contactEntityName = 'SuluContactExtensionBundle:Contact';

    function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * return name of widget
     *
     * @return string
     */
    public function getName()
    {
        return 'simple-contact';
    }

    /**
     * returns template name of widget
     *
     * @return string
     */
    public function getTemplate()
    {
        return 'SuluSalesCoreBundle:Widgets:core.contact.html.twig';
    }

    /**
     * returns data to render template
     *
     * @param array $options
     * @throws WidgetException
     * @return array
     */
    public function getData($options)
    {
        if (!empty($options) &&
            array_key_exists('contact', $options) &&
            !empty($options['contact'])
        ) {
            $id = $options['contact'];
            $contact = $this->em->getRepository(
                $this->contactEntityName
            )->find($id);

            if (!$contact) {
                throw new WidgetEntityNotFoundException(
                    'Entity ' . $this->contactEntityName . ' with id ' . $id . ' not found!',
                    $this->widgetName,
                    $id
                );
            }
            return $this->parseMainContact($contact);
        } else {
            throw new WidgetParameterException(
                'Required parameter account not found or empty!',
                $this->widgetName,
                'account'
            );
        }
    }

    /**
     * Returns the data needed for the account list-sidebar
     *
     * @param Contact $contact
     * @return array
     */
    protected function parseMainContact(Contact $contact)
    {
        if ($contact) {
            $data = [];
            $data['id'] = $contact->getId();
            $data['fullName'] = $contact->getFullName();
            $data['phone'] = $contact->getMainPhone();
            $data['email'] = $contact->getMainEmail();
            return $data;
        } else {
            return null;
        }
    }
}
