/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

/**
 * @class editable-data-row@sulusalescore
 * @constructor
 *
 * @param {Object} [options] Configuration object
 * @param {String} [options.instanceName] instance name of the component and its subcomponents
 * @param {String} [options.view] view name
 * @param {Object} [options.viewOptions] options for the view
 * @param {String} [options.overlayTemplate] template for the overlay
 * @param {String} [options.overlayTitle] translation key for the overlay title
 * @param {String} [options.dataFormat] structure which defines blocks with used fields and their delimiter - example below:
 * @param {String} [options.defaultProperty] property which is used to decide default displayed data
 *
 * Templates: The template for the overlay form has to use the editable-data-overlay-form class
 */
define(['text!sulusalescore/components/editable-data-row/templates/address.form.html'], function(AddressTemplate) {

    'use strict';

    var defaults = {
            instanceName: 'undefined',
            fields: null,
            defaultProperty: null,
            overlayTemplate: null,
            overlayTitle: '',
            selectSelector: 'address-select'
        },

        constants = {
            rowClass: 'editable-row',
            rowClassSelector: '.editable-row',

            overlayTitle: 'salesorder.orders.addressOverlayTitle'
        },

        templates = {
            rowTemplate: function(value) {
                return ['<span class="block pointer ', constants.rowClass , '">', value, '</span>'].join('');
            }
        },

        eventNamespace = 'sulu.editable-data-row.addres-view.',

        /**
         * raised when an item is changed
         * @event sulu.editable-data-row.instanceName.initialized
         */
        EVENT_INITIALIZED = function() {
            this.sandbox.emit(eventNamespace + this.options.instanceName + '.initialized');
        },

        /**
         * bind custom events
         */
        bindCustomEvents = function() {

        },

        /**
         * bind dom events
         */
        bindDomEvents = function() {
            // click handler to trigger overlay
            this.sandbox.dom.on(this.context.$el, 'click', function() {
                this.sandbox.emit(
                        'sulu.editable-data-row.' + this.options.instanceName + '.overlay.initialize',
                    {
                        overlayTemplate: AddressTemplate,
                        overlayTitle: constants.overlayTitle,
                        overlayOkCallback: null,
                        overlayCloseCallback: null
                    }
                );
            }.bind(this), constants.rowClassSelector);
        },

        /**
         * Flattens an address object or an array of addresses
         * @param data
         * @returns {*}
         */
        flattenAddresses = function(data) {

            if(!!data && this.sandbox.util.typeOf(data) === 'array'){
                this.sandbox.util.foreach(data, function(address){
                    if (!!address && !!address.country && !!address.country.name) {
                        address.country = address.country.name;
                    }

                    address.fullAddress = getAddressString.call(this, address);

                }.bind(this));
            } else {
                if (!!data && !!data.country && !!data.country.name) {
                    data.country = data.country.name;
                }

                data.fullAddress = getAddressString.call(this, data);
            }
            return data;
        },

        /**
         * Renders the single row with the data according to the fields param or a replacement when no data is given
         */
        renderRow = function(data) {
            var $row,
                $oldRow,
                address;

            // remove old row when rendering is triggered not for the first time
            $oldRow = this.sandbox.dom.find(constants.rowClassSelector, this.context.$el);
            this.sandbox.dom.remove($oldRow);

            if (!!data) {
                address = flattenAddresses.call(this, data);
                $row = this.sandbox.dom.createElement(templates.rowTemplate(address.fullAddress));
                this.sandbox.dom.append(this.context.$el, $row);
            }
        },

        /**
         * Returns the address according to the defined format
         * @param data
         * @returns {*}
         */
        getAddressString = function(address){
            var str = address.street;

            str+= !!str.length && !!address.number ? ' '+address.number : address.number;
            str+= !!str.length && !!address.zip ? ', '+address.zip : '';
            str+= !!str.length && !!address.city ? ' '+address.city : address.city;
            str+= !!str.length && !!address.country ? ', '+address.country : address.country;

            return str;
        };

    return {

        initialize: function(context, options) {


//            data-aura-overlay-title="salesorder.orders.addressOverlayTitle"
//            data-aura-select-selector="#address-select"

            this.context = context;
            this.sandbox = this.context.sandbox;
            this.options = this.sandbox.util.extend({}, defaults, options);

            bindCustomEvents.call(this);
            bindDomEvents.call(this);

            EVENT_INITIALIZED.call(this);
        },

        render: function(){
            renderRow.call(this, this.context.selectedData);
        }


    };
});
