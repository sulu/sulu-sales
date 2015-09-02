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

use DateTime;

abstract class BaseSalesManager
{
    /**
     * sets a date if it's set in data
     * @param $data
     * @param $key
     * @param $currentDate
     * @param callable $setCallback
     */
    protected function setDate($data, $key, $currentDate, callable $setCallback)
    {
        $date = $this->getProperty($data, $key, $currentDate);
        if ($date !== null) {
            if (is_string($date)) {
                $date = new DateTime($data[$key]);
            }
            call_user_func($setCallback, $date);
        }
    }

    /**
     * checks data for attributes
     * @param array $data
     * @param $key
     * @param $isNew
     * @return bool
     * @throws \Exception
     */
    protected function checkDataSet(array $data, $key, $isNew)
    {
        $keyExists = array_key_exists($key, $data);

        if (($isNew && !($keyExists && $data[$key] !== null)) || (!$keyExists || $data[$key] === null)) {
            throw new \Exception(sprintf("Missing attribute %s", $key));
        }

        return $keyExists;
    }

    /**
     * checks if data is set
     * @param $key
     * @param $data
     * @return bool
     */
    protected function checkIfSet($key, $data)
    {
        $keyExists = array_key_exists($key, $data);

        return $keyExists && $data[$key] !== null && $data[$key] !== '';
    }

    /**
     * searches for contact in specified data and calls callback function
     * @param array $data
     * @param $dataKey
     * @param $addCallback
     * @return null|Contact
     * @throws \Exception
     */
    protected function addContactRelation(array $data, $dataKey, $addCallback)
    {
        $contact = null;
        if (array_key_exists($dataKey, $data) && is_array($data[$dataKey]) && array_key_exists('id', $data[$dataKey])) {
            /** @var Contact $contact */
            $contactId = $data[$dataKey]['id'];
            $contact = $this->em->getRepository(static::$contactEntityName)->find($contactId);
            if (!$contact) {
                // TODO: custom exception dependencyNotFound
                throw new \Exception(sprintf("Entity '%s' with id %s not found", static::$contactEntityName, $contactId));
            }
            $addCallback($contact);
        }
        return $contact;
    }

    /**
     * Returns the entry from the data with the given key, or the given default value, if the key does not exist
     * @param array $data
     * @param string $key
     * @param string $default
     * @return mixed
     */
    protected function getProperty(array $data, $key, $default = null)
    {
        return array_key_exists($key, $data) ? $data[$key] : $default;
    }

    /**
     * @param $data
     * @param $entity
     * @return null|object
     * @throws \Exception
     * @internal param Order $order
     */
    protected function setAccount($data, $entity)
    {
        $accountData = $this->getProperty($data, 'account');
        if ($accountData) {
            if (!array_key_exists('id', $accountData)) {
                // TODO: custom exception
                throw new \Exception(sprintf("Missing attribute %s", 'account.id'));
            }
            // TODO: inject repository class
            $account = $this->em->getRepository(static::$accountEntityName)->find($accountData['id']);
            if (!$account) {
                // TODO custom exception: dependencyNotFound
                throw new \Exception(sprintf("Entity '%s' with id %s not found", static::$accountEntityName, $accountData['id']));
            }
            $entity->setAccount($account);
            return $account;
        }
        $entity->setAccount(null);
        return null;
    }
}
