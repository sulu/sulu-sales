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
     * Sets header title and breadCrumb according to order and additions.
     *
     * @param {Object} order
     * @param {Object} options
     */
    var setHeadline = function(order, options) {
        var title, hasOptions,
            titleAddition = null,
            orderEvent = null;

        title = this.sandbox.translate('salesorder.order');

        // parse options
        hasOptions = typeof options === 'object';
        if (hasOptions && options.hasOwnProperty('titleAddition')) {
            titleAddition = options.titleAddition;
        }

        // set title based on order
        if (!!order && !!order.number) {
            title += ' #' + order.number;
        }
        // title addition
        if (!!titleAddition) {
            title += ' ' + titleAddition;
        }

        this.sandbox.emit('sulu.header.set-title', title);
    };

    return {
        /**
         * Sets header data: breadcrumb, headline for an order.
         *
         * @param {Object} order Backbone-Entity
         * @param {Object} options configuration object for options
         * @param {String} [options.titleAddition] adds an extra text to the title
         */
        setHeader: function(order, options) {
            // parse to json
            order = order.toJSON();
            // sets headline and breadcrumb
            setHeadline.call(this, order, options);
        },

        /**
         * Will create the url string for an order.
         *
         * @param {Number} [id] if defined, 'edit:id' will be added to the url string
         * @param {String} [postfix] adds an url postfix
         *
         * @returns {string}
         */
        getUrl: function(id, postfix) {
            var url = 'sales/orders';
            if (!!id) {
                url += '/edit:' + id;
            }
            if (!!postfix) {
                url += '/' + postfix;
            }

            return url;
        }
    };
});
