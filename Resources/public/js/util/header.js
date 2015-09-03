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
     * sets header title and breadCrumb according to order and additions
     * @param order
     * @param options
     */
    var setHeadlinesAndBreadCrumb = function(order, options) {
        var title, breadcrumb, hasOptions,
            titleAddition = null,
            breadcrumbAddition = null,
            orderEvent = null;

        title = this.sandbox.translate('salesorder.order');
        breadcrumb = [
            {title: 'navigation.sales'},
            {title: 'salesorder.orders.title', event: 'sulu.salesorder.orders.list'}
        ];

        // parse options
        hasOptions = typeof options === 'object';
        if (hasOptions && options.hasOwnProperty('titleAddition')) {
            titleAddition = options.titleAddition;
        }
        if (hasOptions && options.hasOwnProperty('breadcrumbAddition')) {
            breadcrumbAddition = options.breadcrumbAddition;
        }
        if (hasOptions && options.hasOwnProperty('breadcrumbOrderEvent')) {
            orderEvent = options.breadcrumbOrderEvent;
        }

        // set title based on order
        if (!!order && !!order.number) {
            title += ' #' + order.number;
            breadcrumb.push({title: '#' + order.number, event: orderEvent});
        }
        // title addition
        if (!!titleAddition) {
            title += ' ' + titleAddition;
        }
        // breadcrumb addition
        if (!!breadcrumbAddition) {
            breadcrumb = breadcrumb.concat(breadcrumbAddition);
        }

        this.sandbox.emit('sulu.header.set-title', title);
        this.sandbox.emit('sulu.header.set-breadcrumb', breadcrumb);
    };

    return {

        /**
         * sets header data: breadcrumb, headline for an order
         * @param {Object} order Backbone-Entity
         * @param {Object} options configuration object for options
         * @param {String} [options.titleAddition] adds an extra text to the title
         * @param {Array} [options.breadcrumbAddition] adds extra items to breadcrumb
         * @param {String} [options.breadcrumbOrderEvent] event thats added to breadcrumb of the current order
         */
        setHeader: function(order, options) {
            // parse to json
            order = order.toJSON();
            // sets headline and breadcrumb
            setHeadlinesAndBreadCrumb.call(this, order, options);
        },

        /**
         * will create the url string for an order
         * @param [id] if defined, 'edit:id' will be added to the url string
         * @param [postfix] adds an url postfix
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
