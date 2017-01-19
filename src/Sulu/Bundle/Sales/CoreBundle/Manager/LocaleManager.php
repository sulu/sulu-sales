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

/**
 * Locale manager for retrieving the correct locale from the request.
 */
class LocaleManager
{
    /**
     * @var string
     */
    protected $fallbackLocale;

    /**
     * @param string $fallbackLocale
     */
    function __construct($fallbackLocale)
    {
        $this->fallbackLocale = $fallbackLocale;
    }

    /**
     * Function returns the locale that should be used by default.
     * If request-locale is set, then use this one.
     * Else return the locale of the user.
     *
     * @param null|UserInterface $user
     * @param null|string $requestLocale
     *
     * @return string
     */
    public function retrieveLocale(UserInterface $user = null, $requestLocale = null)
    {
        // Use request locale if defined.
        if ($requestLocale && is_string($requestLocale)) {
            return $requestLocale;
        }

        if ($user && $user->getLocale()) {
            return $user->getLocale();
        }

        return $this->fallbackLocale;
    }
}
