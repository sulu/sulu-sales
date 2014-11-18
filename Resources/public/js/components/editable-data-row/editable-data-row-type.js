/*
 * This file is part of the Husky Validation.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 *
 */

define([
    'type/default'
], function(Default) {

    'use strict';

    return function($el, options) {
        var defaults = {
                required: false
            },

            typeInterface = {
                setValue: function(data) {
                    if (!!data) {
                        this.$el.data({
                            'value': data
                        }).trigger('data-changed');
                    } else {
                        this.$el.data({
                            'value': ''
                        }).trigger('data-changed');
                    }
                },

                getValue: function() {
                    return this.$el.data('value');
                },

                needsValidation: function() {
                    return false;
                },

                validate: function() {
                    return true;
                }
            };

        return new Default($el, defaults, options, 'editable-data-row', typeInterface);
    };
});
