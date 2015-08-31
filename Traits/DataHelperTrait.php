<?php

/*
 * This file is part of the Sulu.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\Sales\CoreBundle\Traits;

/**
 * Helper Trait for get and set data
 */
trait DataHelperTrait
{
    /**
     * Returns the entry from the data with the given key,
     * or the given default value, if the key does not exist.
     *
     * @param array $data
     * @param string $key
     * @param string $default
     *
     * @return mixed
     */
    private function getProperty(array $data, $key, $default = null)
    {
        return array_key_exists($key, $data) ? $data[$key] : $default;
    }

    /**
     * Sets a date if it's set in data.
     *
     * @param array $data
     * @param string $key
     * @param string|\DateTime $defaultDate
     * @param callable $setCallback
     */
    private function setDate($data, $key, $defaultDate, callable $setCallback)
    {
        if (($date = $this->getProperty($data, $key, $defaultDate)) !== null) {
            // set to null if empty
            if (empty($date)) {
                $date = null;
            }
            if (is_string($date)) {
                $date = new \DateTime($date);
            }
            // call the given set date function
            call_user_func($setCallback, $date);
        }
    }

    /**
     * Returns the boolean value of a value
     *
     * @param sting $value
     *
     * @return bool
     */
    private function getBoolValue($value)
    {
        if (strtolower($value) === "true" || $value === true || $value === 1) {
            return true;
        }

        return false;
    }
}
