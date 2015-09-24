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
use Sulu\Bundle\AdminBundle\Widgets\WidgetInterface;
use Sulu\Bundle\AdminBundle\Widgets\WidgetParameterException;
use Sulu\Bundle\ContactBundle\Entity\AccountInterface;
use Sulu\Bundle\ContactBundle\Entity\AccountRepository;
use Sulu\Bundle\ContactBundle\Entity\Address;

/**
 * Widgets for displaying multiple Accounts.
 */
class MultipleAccounts implements WidgetInterface
{
    /**
     * Configuration for request keys.
     *
     * @var array
     */
    protected $keys = [
        'ids' => 'accountIds',
        'limit' => 'limit',
        'headline' => 'headline',
        'emptyLabel' => null,
    ];

    /**
     * @var string
     */
    protected $widgetName = 'MultipleAccounts';

    /**
     * @var AccountRepository
     */
    protected $accountRepository;

    /**
     * @param AccountRepository $accountRepository
     */
    function __construct(
        AccountRepository $accountRepository
    ) {
        $this->accountRepository = $accountRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'multiple-accounts';
    }

    /**
     * {@inheritdoc}
     */
    public function getTemplate()
    {
        return 'SuluSalesCoreBundle:Widgets:core.multiple.accounts.html.twig';
    }

    /**
     * {@inheritdoc}
     *
     * @throws WidgetEntityNotFoundException
     * @throws WidgetParameterException
     */
    public function getData($options)
    {
        $data = [];

        if (!empty($options)) {
            $ids = $this->getValue($this->keys['ids'], $options, null);
            $limit = $this->getValue($this->keys['limit'], $options, null);
            $headline = $this->getValue($this->keys['headline'], $options, null);

            // set headline
            if ($headline) {
                $data['headline'] = $headline;
            }

            if ($ids) {
                // Parse accounts
                $accountIds = explode(',', $ids);
                foreach ($accountIds as $index => $id) {
                    // check if limit is exceeded
                    if ($limit && $index >= $limit) {
                        $data['further'] = count($accountIds) - $limit;
                        break;
                    }

                    // fetch and parse account data
                    $account = $this->accountRepository->find($id);
                    if (!$account) {
                        throw new WidgetEntityNotFoundException(
                            'Entity \'Account\' with id ' . $id . ' not found!',
                            $this->widgetName,
                            $id
                        );
                    }
                    $data['accounts'][] = $this->parseAccount($account);
                }
            }

            // set emptylabel
            if (isset($this->keys['emptyLabel'])) {
                $data['emptyLabel'] = $this->keys['emptyLabel'];
            }

            return $data;
        }
    }

    /**
     * Parses the account data.
     *
     * @param AccountInterface $account
     *
     * @return array
     */
    protected
    function parseAccount(
        AccountInterface $account
    ) {
        if ($account) {
            $data = [];
            $data['id'] = $account->getId();
            $data['name'] = $account->getName();
            $data['phone'] = $account->getMainPhone();
            $data['email'] = $account->getMainEmail();
            $data['url'] = $account->getMainUrl();

            // get main contact
            if ($account->getMainContact()) {
                $data['contact'] = $account->getMainContact()->getFullName();
            }

            /* @var Address $accountAddress */
            $accountAddress = $account->getMainAddress();
            if ($accountAddress) {
                $data['address']['street'] = $accountAddress->getStreet();
                $data['address']['number'] = $accountAddress->getNumber();
                $data['address']['zip'] = $accountAddress->getZip();
                $data['address']['city'] = $accountAddress->getCity();
                $data['address']['country'] = $accountAddress->getCountry()->getName();
            }

            return $data;
        } else {
            return null;
        }
    }

    /**
     * Returns value from array. If not set a default value is returned.
     *
     * @param string $key
     * @param array $data
     * @param mixed $default
     *
     * @return mixed
     */
    private
    function getValue(
        $key, $data, $default
    ) {
        if (array_key_exists($key, $data)) {
            return $data[$key];
        }

        return $default;
    }
}
