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
use Sulu\Bundle\AdminBundle\Widgets\WidgetParameterException;
use Sulu\Bundle\AdminBundle\Widgets\WidgetEntityNotFoundException;
use Doctrine\ORM\EntityManager;
use Sulu\Bundle\ContactBundle\Entity\AccountInterface;
use Sulu\Bundle\ContactBundle\Entity\AccountRepository;
use Sulu\Bundle\ContactBundle\Entity\Address;

/**
 * SimpleAccount widget
 *
 * @package Sulu\Bundle\Sales\CoreBundle\Widgets
 */
class MultipleAccounts implements WidgetInterface
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var string
     */
    protected $widgetName = 'MultipleAccounts';

    /**
     * @var AccountRepository
     */
    protected $accountRepository;

    /**
     * @param EntityManager $em
     * @param AccountRepository $accountRepository
     */
    function __construct(EntityManager $em, AccountRepository $accountRepository)
    {
        $this->em = $em;
        $this->accountRepository = $accountRepository;
    }

    /**
     * Return name of widget
     *
     * @return string
     */
    public function getName()
    {
        return 'multiple-accounts';
    }

    /**
     * Returns template name of widget
     *
     * @return string
     */
    public function getTemplate()
    {
        return 'SuluSalesCoreBundle:Widgets:core.multiple.accounts.html.twig';
    }

    /**
     * Returns data to render template
     *
     * @param array $options
     *
     * @throws WidgetEntityNotFoundException
     * @throws WidgetParameterException
     *
     * @return array
     */
    public function getData($options)
    {
        if (!empty($options) &&
            array_key_exists('accountIds', $options) &&
            !empty($options['accountIds'])
        ) {
            $data = array();

            $accountIds = explode(',', $options['accountIds']);
            foreach ($accountIds as $id) {
                $account = $this->accountRepository->find($id);
                if (!$account) {
                    throw new WidgetEntityNotFoundException(
                        'Entity \'Account\' with id ' . $id . ' not found!',
                        $this->widgetName,
                        $id
                    );
                }
                $data[] = $this->parseAccount($account);
            }

            return $data;
        } else {
            throw new WidgetParameterException(
                'Required parameter contact not found or empty!',
                $this->widgetName,
                'account'
            );
        }
    }

    /**
     * Parses the account data
     *
     * @param AccountInterface $account
     *
     * @return array
     */
    protected function parseAccount(AccountInterface $account)
    {
        if ($account) {
            $data = [];
            $data['id'] = $account->getId();
            $data['name'] = $account->getName();
            $data['phone'] = $account->getMainPhone();
            $data['email'] = $account->getMainEmail();
            $data['url'] = $account->getMainUrl();
            // get main contact
            if ($mainContact = $account->getMainContact()) {
                $data['contact'] = $account->getMainContact()->getFullName();
            }

            /* @var Address $accountAddress */
            $accountAddress = $account->getMainAddress();

            if ($accountAddress) {
                $data['address']['street'] = $accountAddress->getStreet();
                $data['address']['number'] = $accountAddress->getNumber();
                $data['address']['zip'] = $accountAddress->getZip();
                $data['address']['city'] = $accountAddress->getCity();
                $data['address']['country'] = $accountAddress->getCountry(
                )->getName();
            }

            return $data;
        } else {
            return null;
        }
    }
}
