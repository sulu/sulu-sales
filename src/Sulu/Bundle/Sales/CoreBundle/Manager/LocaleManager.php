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

use Sulu\Component\Security\Authentication\UserInterface;

class LocaleManager
{
    /**
     * @var array
     */
    private $fallbackLocale;

    /**
     * @param string $fallbackLocale
     */
    public function __construct($fallbackLocale)
    {
        $this->fallbackLocale = $fallbackLocale;
    }

    /**
     * Function returns the locale that should be used by default.
     * If request-locale is set, then use this one.
     * Else check if the user has a locale, then return this.
     * Else return the fallback locale of Sulu.
     *
     * @param UserInterface $user
     * @param null|string $requestLocale
     *
     * @return string
     */
    public function retrieveLocale(UserInterface $user, $requestLocale = null)
    {
        // Use request locale if defined.
        if ($requestLocale && is_string($requestLocale)) {
            return $requestLocale;
        }

        if ($user->getLocale()) {
            return $user->getLocale();
        }

        return $this->fallbackLocale;
    }
}
