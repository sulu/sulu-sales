/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

/**
 * @class transition-overlay@sulusalescore
 * @constructor
 *
 * @param {Object} [options] Configuration object
 * @param {String} [options.selectData] data that is shown in select TODO:
 */
define([
    'text!sulusalescore/components/transition-overlay/transition-overlay.html'
], function(Template) {

    'use strict';

    var defaults = {
            customerData: [],
            transitionData: [],
            customerItemData: [],
            itemUrl: null
        },

        constants = {
            MAX_CUSTOMER_LENGTH: 20
        },

        selectors = {
            overlayContainerClass: 'transition-overlay-inner-container',
            transitionSelect: '#transition-select',
            customerSelect: '#customer-select',
            itemsTable: '#item-table'
        },

        eventNamespace = 'sulu.transition-overlay.',

        /**
         * Raised when the instance is initialized.
         *
         * @event sulu.transition-overlay.[instanceName].initialized
         */
        EVENT_INITIALIZED = function() {
            return getEventName.call(this, 'initialized');
        },

        /**
         * Returns event name.
         *
         * @param suffix
         *
         * @returns {String}
         */
        getEventName = function(suffix) {
            var eventName = eventNamespace;
            if (!!this.options.instanceName) {
                eventName += '.' + this.options.instanceName;
            }

            return eventName + '.' + suffix;
        },

        /**
         * bind custom events
         */
        bindCustomEvents = function() {

        },

        bindDomEvents = function() {

        },

        /**
         * Renders the transition overlay.
         *
         * @param {Object} data
         */
        initOverlay = function(data) {
            var $overlay, overlayContent, templateData;

            //// prevent multiple initialization of the overlay
            //this.sandbox.stop(this.sandbox.dom.find('.'+selectors.overlayContainerClass, this.$el));
            //this.sandbox.dom.remove(this.sandbox.dom.find('.'+selectors.overlayContainerClass, this.$el));

            $overlay = this.sandbox.dom.createElement('<div class="' + selectors.overlayContainerClass + '"></div>');
            this.sandbox.dom.append(this.$el, $overlay);

            templateData = {
                data: data
            };

            overlayContent = this.sandbox.util.template(Template, templateData);

            // create overlay with data
            this.sandbox.start([
                {
                    name: 'overlay@husky',
                    options: {
                        el: $overlay,
                        container: this.$el,
                        displayHeader: false,
                        openOnStart: true,
                        removeOnClose: true,
                        cssClass: 'transition-overlay-container',
                        instanceName: 'transition',
                        data: overlayContent,
                        skin: 'wide'
                        //,
                        //okCallback:
                    }
                }
            ]);

            this.sandbox.once('husky.overlay.transition.opened', initOverlayComponents.bind(this));
        },

        /**
         * Parses an object into an array of items to be displayed in a select.
         *
         * @param {Object} data
         *
         * @returns {Array}
         */
        parseObjectDataForSelect = function(data) {
            var result = [];

            for (var i in data) {
                result.push({
                    id: i,
                    name: this.sandbox.translate(data[i])
                });
            }

            return result;
        },

        /**
         * Returns items-array by filtering through customerItemData.
         *
         * @param {Number} customerId
         *
         * @returns {Array}
         */
        filterCustomerDataById = function(customerId) {
            var data = this.options.customerItemData,
                customer,
                items = [];
            for (var i = -1, len = data.length; ++i < len;) {
                customer = data[i];

                // filter by customer
                if (!!customerId && customerId != customer.id) {
                    continue;
                }

                // add to items
                items = items.concat(customer.items);

                if (!!customerId) {
                    break;
                }
            }

            return items;
        },

        /**
         * Shortens a String
         *
         * @param {String} String
         * @param {Number} maxLength
         */
        shortenString = function(string, maxLength) {
            var result = string;
            if (string.length > maxLength) {
                result = string.substring(0, maxLength) + '..';
            }

            return result;
        },

        /**
         * Starts item-table component with data of specified customer.
         *
         * @param {Number} customerId
         */
        startItemsTable = function(customerId) {
            this.sandbox.start([{
                name: 'item-table@sulusalescore',
                options: {
                    el: selectors.itemsTable,
                    showPrice: false,
                    displayToolbars: false,
                    hasNestedItems: true,
                    columns: [
                        'quantity',
                        'quantityUnit',
                        'name',
                        'address'
                    ],
                    data: filterCustomerDataById.call(this, customerId)
                }
            }]);
        },

        /**
         * Rerenders Item table.
         *
         * @param {Number} customerId
         */
        rerenderItemTable = function(customerId) {
            this.customerId = customerId;
            startItemsTable.call(this, customerId);
        },

        /**
         * Initializes all the components which are displayed in the overlay.
         */
        initOverlayComponents = function() {
            var transitionData = parseObjectDataForSelect.call(this, this.options.transitionData),
                customerData = this.options.customerData;

            // display selects with transitions and customers
            this.sandbox.start([
                {
                    name: 'select@husky',
                    options: {
                        el: selectors.transitionSelect,
                        data: transitionData,
                        style: 'action big',
                        preSelectedElements: [transitionData[0].name]
                    }

                },
                {
                    name: 'select@husky',
                    options: {
                        el: selectors.customerSelect,
                        data: customerData,
                        defaultLabel: this.sandbox.translate('public.all'),
                        deselectField: this.sandbox.translate('public.all'),
                        selectCallback: rerenderItemTable.bind(this),
                        deselectCallback: rerenderItemTable.bind(this)
                    },
                }
            ]);

            // start item-table
            startItemsTable.call(this);
        };

    return {
        initialize: function() {
            this.customerId = null;

            this.options = this.sandbox.util.extend({}, defaults, this.options);

            bindCustomEvents.call(this);
            bindDomEvents.call(this);

            this.render();

            this.sandbox.emit(EVENT_INITIALIZED.call(this));
        },

        render: function() {
            initOverlay.call(this);
        }
    };
});
