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

use Doctrine\ORM\EntityManager;
use Sulu\Bundle\AdminBundle\Widgets\WidgetEntityNotFoundException;
use Sulu\Bundle\AdminBundle\Widgets\WidgetException;
use Sulu\Bundle\AdminBundle\Widgets\WidgetInterface;
use Sulu\Bundle\AdminBundle\Widgets\WidgetParameterException;
use Sulu\Component\Contact\Model\ContactInterface;
use Sulu\Component\Contact\Model\ContactRepositoryInterface;

/**
 * SimpleContact widget.
 *
 * Used for displaying a contact in the sidebar.
 */
class SimpleContact implements WidgetInterface
{
    /**
     * @var EntityManager
     */
    protected $em;

    protected $widgetName = 'SimpleContact';

    /**
     * @var ContactRepositoryInterface
     */
    private $contactRepository;

    /**
     * @param EntityManager $em
     * @param ContactRepositoryInterface $contactRepository
     */
    public function __construct(
        EntityManager $em,
        ContactRepositoryInterface $contactRepository
    ) {
        $this->em = $em;
        $this->contactRepository = $contactRepository;
    }

    /**
     * Return name of widget.
     *
     * @return string
     */
    public function getName()
    {
        return 'simple-contact';
    }

    /**
     * Returns template name of widget.
     *
     * @return string
     */
    public function getTemplate()
    {
        return 'SuluSalesCoreBundle:Widgets:core.contact.html.twig';
    }

    /**
     * Returns data to render template.
     *
     * @param array $options
     * @throws WidgetException
     *
     * @return array
     */
    public function getData($options)
    {
        if (!empty($options) &&
            array_key_exists('contact', $options) &&
            !empty($options['contact'])
        ) {
            $id = $options['contact'];
            $contact = $this->contactRepository->find($id);

            if (!$contact) {
                throw new WidgetEntityNotFoundException(
                    'Entity ' . $this->contactRepository->getClassName() . ' with id ' . $id . ' not found!',
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
     * Returns the data needed for the account list-sidebar.
     *
     * @param ContactInterface $contact
     *
     * @return array
     */
    protected function parseMainContact(ContactInterface $contact)
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
