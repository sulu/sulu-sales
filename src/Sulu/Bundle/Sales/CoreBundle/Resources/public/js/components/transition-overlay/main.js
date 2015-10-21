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
 * @param {Object}   [options] Configuration object
 * @param {String}   [options.columns] Columns to be shown in item-table
 * @param {Array}    [options.customerData] Data that is shown in the customer select
 * @param {Array}    [options.customerItemData] Array of data containing customer information and it's items
 * @param {Function} [options.okCallback] Callback function when transition should be made
 * @param {Array}    [options.transitionData] Data that is shown in transition select
 */
define([
    'text!sulusalescore/components/transition-overlay/transition-overlay.html'
], function(Template) {

    'use strict';

    var defaults = {
            columns: [
                'quantity',
                'quantityUnit',
                'name',
                'account',
                'address'
            ],
            customerData: [],
            customerItemData: [],
            okCallback: null,
            transitionData: []
        },

        constants = {
            MAX_CUSTOMER_LENGTH: 20
        },

        classes = {
            overlayContainerClass: 'transition-overlay-inner-container',
        },

        selectors = {
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
         * Renders the transition overlay.
         *
         * @param {Object} data
         */
        initOverlay = function(data) {
            var $overlay, overlayContent, templateData;

            // prevent multiple initialization of the overlay
            this.sandbox.stop(this.sandbox.dom.find('.' + classes.overlayContainerClass, this.$el));
            this.sandbox.dom.remove(this.sandbox.dom.find('.' + classes.overlayContainerClass, this.$el));

            $overlay = this.sandbox.dom.createElement('<div class="' + classes.overlayContainerClass + '"></div>');
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
                        skin: 'wide',
                        okCallback: submitTransition.bind(this),
                        okDefaultText: 'test test'
                        //slides: [
                        //    {
                        //        buttons: [
                        //            {
                        //                type: 'ok',
                        //                text: 'blah schmafu'
                        //            }
                        //        ]
                        //    }
                        //]
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

                // add account to each item
                this.sandbox.util.foreach(customer.items, function(item) {
                    item.account = {
                        name: cropString(customer.name, constants.MAX_CUSTOMER_LENGTH),
                        id: customer.id
                    }
                });

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
        cropString = function(string, maxLength) {
            var result = string;
            if (string.length > maxLength + 2) {
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
                    columns: this.options.columns,
                    data: filterCustomerDataById.call(this, customerId),
                    displayToolbars: false,
                    el: selectors.itemsTable,
                    hasNestedItems: true,
                    instanceName: 'transition',
                    showPrice: false,
                    showItemCount: false
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
                        data: transitionData,
                        el: selectors.transitionSelect,
                        instanceName: 'transition',
                        preSelectedElements: [transitionData[0].name],
                        style: 'action big'
                    }
                },
                {
                    name: 'select@husky',
                    options: {
                        data: customerData,
                        defaultLabel: this.sandbox.translate('salescore.all-customers'),
                        deselectCallback: rerenderItemTable.bind(this),
                        deselectField: this.sandbox.translate('salescore.all-customers'),
                        el: selectors.customerSelect,
                        instanceName: 'customer',
                        selectCallback: rerenderItemTable.bind(this)
                    }
                }
            ]);

            // start item-table
            startItemsTable.call(this);
        },

        /**
         * Submit data from transition overlay.
         */
        submitTransition = function() {
            var transitionKey,
                itemsData;

            // gather data
            this.sandbox.emit('husky.select.transition.get-checked', function(selection) {
                transitionKey = selection[0];
            });
            this.sandbox.emit('sulu.item-table.transition.get-data', function(items) {
                itemsData = items;
            });

            // return data
            if (!!this.options.okCallback) {
                this.options.okCallback({
                    transition: transitionKey,
                    items: itemsData
                });
            }
        };

    return {
        initialize: function() {
            this.customerId = null;

            this.options = this.sandbox.util.extend({}, defaults, this.options);

            this.render();

            this.sandbox.emit(EVENT_INITIALIZED.call(this));
        },

        render: function() {
            initOverlay.call(this);
        }
    };
});
