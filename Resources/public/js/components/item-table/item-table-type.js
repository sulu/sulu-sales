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
    'type/default',
    'form/util'
], function(Default) {

    'use strict';

    return function($el, options) {
        var defaults = {
                id: 'id',
                label: 'value',
                required: false
            },

            typeInterface = {
                setValue: function(data) {
                    if (data === undefined || data === '' || data === null) {
                        return;
                    }

                    if (typeof data === 'object') {
                        App.dom.data(this.$el, 'items', data);
//                        App.dom.trigger(this.$el, 'data-changed', data);
                    }
                },

                getValue: function() {
                    return App.dom.data(this.$el, 'items');
                },

                needsValidation: function() {
                    var val = this.getValue();
                    return !!val;
                },

                validate: function() {
                    return App.form.validate('#item-table-form');
                }
            };

        return new Default($el, defaults, options, 'item-table', typeInterface);
    };
});
