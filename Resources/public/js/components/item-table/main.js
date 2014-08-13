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
    'text!sulusalescore/components/item-table/item.row.html',
    'text!sulusalescore/components/item-table/item.row-head.html'
], function(FormTpl, RowTpl, RowHeadTpl) {

    'use strict';

    var defaults = {
            data: []
        },

        constants = {
            formId: '#item-table-form',
            listClass: '.item-table-list',
            productSearchClass: '.product-search',
            rowIdPrefix: 'item-table-row-',
            productsUrl: '/admin/api/products?flat=true&searchFields=number,name&fields=id,name,number',
            productUrl: '/admin/api/products/',
            rowClass: '.item-table-row',
            quantityRowClass: '.item-quantity',
            quantityInput: '.item-quantity input',
            priceRowClass: '.item-price',
            priceInput: '.item-price input',
            discountRowClass: '.item-discount',
            discountInput: '.item-discount input'
        },

        /**
         * default values of a item row
         */
        rowDefaults = {
            rowClass: null,
            id: null,
            rowNumber: null,
            rowId: '',
            name: '',
            number: '',
            quantity: '',
            quantityUnit: 'pc',
            price: '',
            discount: '',
            overallPrice: '',
            currency: 'EUR',
            useProductsPrice: false,
            tax: 0
        },

        eventNamespace = 'sulu.item-table.',

        /**
         * raised when an item is changed
         * @event sulu.item-table.changed
         */
        EVENT_CHANGED =  eventNamespace + 'changed',

        /**
         * data that is shown in header
         */
        getHeaderData = function() {
            return {
                rowClass: 'header',
                name: this.sandbox.translate('salescore.item.product'),
                number: this.sandbox.translate('salescore.item.number'),
                quantity: this.sandbox.translate('salescore.item.quantity'),
                quantityUnit: this.sandbox.translate('salescore.item.unit'),
                price: this.sandbox.translate('salescore.item.price'),
                discount: this.sandbox.translate('salescore.item.discount'),
                overallPrice: this.sandbox.translate('salescore.item.overallValue')
            };
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

            // add new item
            this.sandbox.dom.on(this.$el, 'click', addNewItemClicked.bind(this), '.add-row');
            // remove row
            this.sandbox.dom.on(this.$el, 'click', removeRowClicked.bind(this), '.remove-row');

            this.sandbox.dom.on(this.$el, 'data-changed', function(event) {
                var items = event.items || [];
                rerenderItems.call(this, items);
            }.bind(this));

            // input field listeners
            this.sandbox.dom.on(this.$el, 'change', quantityChangedHandler.bind(this), constants.quantityInput);
            this.sandbox.dom.on(this.$el, 'change', priceChangedHandler.bind(this), constants.priceInput);
            this.sandbox.dom.on(this.$el, 'change', discountChangedHandler.bind(this), constants.discountInput);
        },

        /**
         * triggered when quantity is changed
         * @param event
         */
        quantityChangedHandler = function(event) {
            var rowId = getRowData.call(this, event).id;
            // update quantity
            this.items[rowId].quantity = this.sandbox.dom.val(event.target);
            refreshItemsData.call(this);

            //TODO update rows overall price

            //TODO update overall price

            this.sandbox.emit(EVENT_CHANGED);
        },

        /**
         * triggered when price is changed
         * @param event
         */
        priceChangedHandler = function(event) {
            var rowId = getRowData.call(this, event).id;
            // update price
            this.items[rowId].price = this.sandbox.dom.val(event.target);
            refreshItemsData.call(this);

            //TODO update rows overall price

            //TODO update overall price

            this.sandbox.emit(EVENT_CHANGED);
        },

        /**
         * triggered when discount is changed
         * @param event
         */
        discountChangedHandler = function(event) {
            var rowId = getRowData.call(this, event).id;
            // update discount
            this.items[rowId].discount = this.sandbox.dom.val(event.target);
            refreshItemsData.call(this);

            //TODO update rows overall price

            //TODO update overall price

            this.sandbox.emit(EVENT_CHANGED);
        },

        getRowData = function(event) {
            var $row = this.sandbox.dom.closest(event.target, '.item-table-row'),
                rowId = this.sandbox.dom.attr($row, 'id');
            return {
                row: $row,
                id: rowId
            };
        },

        /**
         * called when a product gets selected in auto-complete
         * @param product
         * @param event
         */
        productSelected = function(product, event) {

            var $row = this.sandbox.dom.closest(event.currentTarget, constants.rowClass),
                rowId = this.sandbox.dom.attr($row, 'id'),
                itemData = {};

            // show loader
            this.sandbox.start([
                {
                    name: 'loader@husky',
                    options: {
                        el: this.sandbox.dom.find(constants.productSearchClass,$row),
                        size: '15px'
                    }
                }
            ]);

            // load product details
            this.sandbox.util.load(constants.productUrl + product.id)
                .then(function(response) {
//                    this.sandbox.dom.stop()
                    // set item to product
                    itemData = setItemByProduct.call(this, response);
                    updateItemRow.call(this, rowId, itemData);
                }.bind(this))
                .fail(function(request, message, error) {
                    this.sandbox.logger.warn(request, message, error);
                }.bind(this));
        },

        /**
         * TODO: move to template when mapper type is implemented
         * initializes the product's auto-complete
         * @param $row
         */
        initProductSearch = function($row) {
            // initialize auto-complete when adding a new Item
            this.sandbox.start([
                {
                    name: 'auto-complete@husky',
                    options: {
                        el: this.sandbox.dom.find(constants.productSearchClass, $row),
                        remoteUrl: constants.productsUrl,
                        resultKey: 'products',
                        getParameter: 'search',
                        value: '',
                        instanceName: 'products',
                        valueKey: 'name',
                        noNewValues: true,
                        selectCallback: productSelected.bind(this)
                    }
                }
            ]);
        },

        /**
         *  DOM-EVENT listener: remove row
         */
        removeRowClicked = function(event) {
            event.preventDefault();

            var $row = this.sandbox.dom.closest(event.currentTarget, '.item-table-row'),
                rowId = this.sandbox.dom.attr($row, 'id');
            removeItemRow.call(this, rowId, $row);
        },

        /**
         * removes item at rowId
         * @param rowId
         */
        removeItemData = function(rowId) {
            // remove from items data
            delete this.items[rowId];

            refreshItemsData.call(this);
        },

        /**
         * adds item to data at index rowId
         * @param rowId
         * @param itemData
         */
        addItemData = function(rowId, itemData) {
            this.items[rowId] = itemData;

            refreshItemsData.call(this);
        },

        /**
         *  DOM-EVENT listener: add a new row
         */
        addNewItemClicked = function(event) {
            event.preventDefault();
            addNewItemRow.call(this);
        },

        /**
         * removes row with
         * @param id
         * @param $row the row element
         */
        removeItemRow = function(rowId, $row) {
            // remove from table
            this.sandbox.dom.remove($row);

            // decrease row counter
            this.rowCount--;

            // remove from data
            removeItemData.call(this, rowId);

            // remove validation
            removeValidtaionFields.call(this, $row);

            this.sandbox.emit(EVENT_CHANGED);
        },

        /**
         * creates and returns a new row element
         */
        createItemRow = function(itemData, increaseCount) {
            if (increaseCount !== false) {
                this.rowCount++;
            }

            var data = this.sandbox.util.extend({}, rowDefaults, itemData, {
                    rowId: constants.rowIdPrefix + this.rowCount,
                    rowNumber: this.rowCount
                }),
                rowTpl = this.sandbox.util.template(RowTpl, data),
                $row = this.sandbox.dom.createElement(rowTpl);
            return $row;
        },

        /**
         * adds an existing item to the list
         * @param itemData
         */
        addItemRow = function(itemData) {
            var $row = createItemRow.call(this, itemData);
            this.sandbox.dom.append(this.$find(constants.listClass), $row);
            return $row;
        },

        /**
         * updates a specific row
         * @param rowId
         */
        updateItemRow = function(rowId, itemData) {
            var $row = createItemRow.call(this, itemData, false);
            this.sandbox.dom.replaceWith(this.$find('#' + rowId), $row);
            // add to data
            addItemData.call(this, rowId, itemData);

            // add to validation
            addValidationFields.call(this, $row);

            // emit data change
            this.sandbox.emit(EVENT_CHANGED);


            return $row;
        },

        addValidationFields = function($row) {
            this.sandbox.form.addField(constants.formId, this.sandbox.dom.find(constants.quantityInput, $row));
            this.sandbox.form.addField(constants.formId, this.sandbox.dom.find(constants.priceInput, $row));
            this.sandbox.form.addField(constants.formId, this.sandbox.dom.find(constants.discountInput, $row));
        },

        removeValidtaionFields = function($row) {
            this.sandbox.form.removeField(constants.formId, this.sandbox.dom.find(constants.quantityInput, $row));
            this.sandbox.form.removeField(constants.formId, this.sandbox.dom.find(constants.priceInput, $row));
            this.sandbox.form.removeField(constants.formId, this.sandbox.dom.find(constants.discountInput, $row));
        },

        /**
         * adds a new item
         */
        addNewItemRow = function() {
            var $row,
                data = {
                    rowClass: 'new'
                };
            $row = addItemRow.call(this, data);
            initProductSearch.call(this, $row);
        },

        /**
         * rerenders component
         */
        rerenderItems = function(items) {
            this.items = [];
            this.sandbox.dom.empty(this.table);
            renderItems.call(this, items);
        },

        /**
         * renders Items
         * @param items
         */
        renderItems = function(items) {
            var i, length, item, $row, rowId;
            for (i = -1, length = items.length; ++i < length;) {
                item = items[i];

                $row = addItemRow.call(this, items[i]);

                // add to items array
                rowId = this.sandbox.dom.attr($row, 'id');
                this.items[rowId] = item;
            }
            // refresh items data attribute
            refreshItemsData.call(this);
        },

        setItemByProduct = function(productData) {
            // merge with row defaults
            return this.sandbox.util.extend({}, rowDefaults,
                {
                    name: productData.name,
                    number: productData.number,
                    product: productData
                }
            );

        },

        /**
         * renders table head
         */
        renderHeader = function() {
            var rowData = this.sandbox.util.extend({}, rowDefaults, getHeaderData.call(this)),
                rowTpl = this.sandbox.util.template(RowHeadTpl, rowData);
            this.sandbox.dom.append(this.$find(constants.listClass), rowTpl);
        },

        refreshItemsData = function() {
            this.sandbox.dom.data(this.$el, 'items', this.getItems());
        },

        initializeForm = function() {
            var form = this.sandbox.form.create(constants.formId);
            form.initialized.then(function() {
                this.sandbox.form.addField(constants.formId, this.$find(constants.quantityInput));
            }.bind(this));

        };

    return {
        initialize: function() {

            // load defaults
            this.options = this.sandbox.util.extend({}, defaults, this.options);

            // variables
            this.items = {};
            this.rowCount = 0;
            this.table = null;

            // if data is not set, check if it's set in elements DATA
            var dataItems = this.sandbox.dom.data(this.$el, 'items');
            if (this.options.data.length === 0 && !!dataItems && dataItems.length > 0) {
                this.options.data = dataItems;
            }

            // render component
            this.render();

            // event listener
            bindCustomEvents.call(this);
            bindDomEvents.call(this);
        },

        render: function() {

            // add translations for template
            var templateData = this.sandbox.util.extend({},
                {
                    addText: this.sandbox.translate('salescore.item.add')
                },
                this.options.data
            );

            // init skeleton
            this.table = this.sandbox.util.template(FormTpl, templateData);
            this.html(this.table);

            // render header
            renderHeader.call(this);

            // render items
            renderItems.call(this, this.options.data);

            // init form
            initializeForm.call(this);
        },

        /**
         * returns current items
         */
        getItems: function() {
            var i,
                items = [];
            // convert this.items to array
            for (i in this.items) {
                items.push(this.items[i]);
            }
            return items;
        }
    };
});
