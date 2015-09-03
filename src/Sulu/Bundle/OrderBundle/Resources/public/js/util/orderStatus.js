/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

define([], function() {

    'use strict';

    /**
     * define possible ORDER STATES
     */
    return {
        CREATED: 1,
        IN_CART: 2,
        CONFIRMED: 4,
        CLOSED_MANUALLY: 8,
        CANCELED: 16,
        COMPLETED: 32
    };
});
