/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

/**
 * @class item-table@sulusalescore
 * @constructor
 *
 * @param {Object} [options] Configuration object
 * @param {Array}  [options.data] array of data [string, object]
 */
define([
    'text!sulusalescore/components/item-table/item.form.html',
    'text!sulusalescore/components/item-table/item.row.html'
], function(FormTpl, RowTpl) {

    'use strict';

    var defaults = {
            data: [],
        },

        constants = {
            listClass: '.item-table-list',
            productSearchClass: '.product-search',
            productUrl: '/admin/api/products?flat=true&searchFields=number&fields=id,number'
        },

        rowDefaults = {
            rowClass: null,
            id: null,
            rowNumber: '',
            name: '',
            number: '',
            quantity: '',
            quantityUnit: '',
            price: '',
            discount: '',
            overallPrice: '',
            currency: 'EUR'
        },

        getHeaderData = function() {
            return {
                rowClass: 'header',
                name: this.sandbox.translate('Artikel'),
                number: this.sandbox.translate('Art.Nr'),
                quantity: this.sandbox.translate('Menge'),
                quantityUnit: this.sandbox.translate('Einheit'),
                price: this.sandbox.translate('Preis'),
                discount: this.sandbox.translate('Rabatt(%)'),
                overallPrice: this.sandbox.translate('Positionswert')
            };
        },

        bindCustomEvents = function() {

        },

        bindDomEvents = function() {
            this.sandbox.dom.on(this.$el, 'click', function(event) {
                event.preventDefault();
                addNewItem.call(this);
            }.bind(this), '.add-row');
        },

        productSelected = function(product) {
            // TODO: show fixed row in component

        },

        initProductSearch = function($row) {
            // initialize auto-complete when adding a new Item
            this.sandbox.start([
                {
                    name: 'auto-complete@husky',
                    options: {
                        el: this.sandbox.dom.find(constants.productSearchClass, $row),
                        remoteUrl: constants.productUrl,
                        resultKey: 'products',
                        getParameter: 'search',
                        value: '',
                        instanceName: 'products',
                        valueKey: 'number',
                        noNewValues: true,
                        selectCallback: productSelected.bind(this)
                    }
                }
            ]);
        },

        /**
         * adds an existing item to the list
         * @param item
         */
        addItem = function(item) {

            var data = this.sandbox.util.extend({}, rowDefaults, item, {
                    rowNumber: ++this.rowCount
                }),
                rowTpl = this.sandbox.util.template(RowTpl, data),
                $row = this.sandbox.dom.createElement(rowTpl);
            this.sandbox.dom.append(this.$find(constants.listClass), $row);
            return $row;
        },

        /**
         * adds a new item
         */
        addNewItem = function() {
            var $row,
                data =  {
                    rowClass: 'new'
                };
            $row = addItem.call(this, data);
            initProductSearch.call(this, $row);
        },

        /**
         * render header line
         * @param items
         */
        renderItems = function(items) {
            var i, length, item;
            for (i = -1, length = items.length; ++i < length;) {
                item = items[i];

                this.items[item.id] = item;

                addItem.call(this, items[i]);
            }
        },

        renderHeader = function() {
            var rowData = this.sandbox.util.extend({}, rowDefaults, getHeaderData.call(this)),
                rowTpl = this.sandbox.util.template(RowTpl, rowData);
            this.sandbox.dom.append(this.$find(constants.listClass), rowTpl);
        };

    return {
        initialize: function() {

            // load defaults
            this.options = this.sandbox.util.extend({}, defaults, this.options);

            // variables
            this.items = {};
            this.rowCount = 0;

            // render component
            this.render();

            // event listener
            bindCustomEvents.call(this);
            bindDomEvents.call(this);
        },

        render: function() {
            // init skeleton
            var formTpl = this.sandbox.util.template(FormTpl, this.options.data);
            this.html(formTpl);

            // render header
            renderHeader.call(this);

            // render items
            renderItems.call(this, this.options.data);
        }

    };
});
