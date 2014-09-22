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
 * @param {Bool}  [options.isEditable] defines if component is editable
 * @param {Bool}  [options.canAddRemove] defines if remove and add rows are processed
 * @param {Bool}  [options.showFinishedItems] defines if a completely processed item should be shown
 * @param {Object} [options.headerTranslations] Translations to set to template
 * @param {String}  [options.processedQuantityValueName] name of data-field  that shows the processed quantity
 * @param {Object}  [options.processedItems] key value pair of sum items that already have been processed
 *                                           (f.e. count of already shipped items of an order)
 */
define([
    'text!sulusalescore/components/item-quantity-table/item.form.html',
    'text!sulusalescore/components/item-quantity-table/item.row.html',
    'text!sulusalescore/components/item-quantity-table/item.row-head.html'
], function(FormTpl, RowTpl, RowHeadTpl) {

    'use strict';

    var defaults = {
            data: [],
            itemsData: [],
            processedQuantityValueName: 'shippedItems',
            processedItems: null,
            canAddRemove: false,
            showFinishedItems: true,
            isEditable: true,
            isNew: false,
            headerTranslations: {}
        },

        constants = {
            formId: '#item-table-form',
            listClass: '.item-table-list',
            productSearchClass: '.product-search',
            rowIdPrefix: 'item-table-row-',
            rowClass: '.item-table-row',
            quantityRowClass: '.item-quantity',
            quantityInput: '.quantity-input input'
        },

        /**
         * default values of a item row
         */
        rowDefaults = {
            rowClass: null,
            rowNumber: null,
            rowId: '',
            id: null,
            item: {
                id: null,
                name: '',
                number: '',
                quantity: '',
                quantityUnit: 'pc',
                price: '',
                discount: null,
                overallPrice: '',
                currency: 'EUR',
                useProductsPrice: false,
                tax: 0
            },
            processedQuantity: null,
            quantity: null
        },

    // event namespace
        eventNamespace = 'sulu.item-table.',

        /**
         * raised when an item is changed
         * @event sulu.item-table.changed
         */
        EVENT_CHANGED = eventNamespace + 'changed',

        /**
         * data that is shown in header
         * is merged with this options.translations
         */
        getHeaderData = function() {
            var i, translations,
                translated = {},
                defaultTranslations = {
                    rowClass: 'header',
                    name: this.sandbox.translate('salescore.item.product'),
                    number: this.sandbox.translate('salescore.item.number'),
                    quantity: this.sandbox.translate('salescore.item.quantity'),
                    quantityInput: this.sandbox.translate('salescore.item.quantity'),
                    quantityUnit: this.sandbox.translate('salescore.item.unit'),
                    price: this.sandbox.translate('salescore.item.price'),
                    discount: this.sandbox.translate('salescore.item.discount'),
                    overallPrice: this.sandbox.translate('salescore.item.overall-value'),
                    processedQuantity: this.sandbox.translate('salescore.item.processed-quantity')
                };
            // translate passed translations
            for (i in this.options.headerTranslations) {
                translated[i] = this.sandbox.translate(this.options.headerTranslations[i]);
            }
            // merge default translations with given one's
            translations = this.sandbox.util.extend({}, defaultTranslations, translated);

            return translations;
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

            this.sandbox.emit(EVENT_CHANGED);
        },

        /**
         * returns an object containing a row's ID and jquery element
         * @param event
         * @returns {{row: *, id: *}}
         */
        getRowData = function(event) {
            var $row = this.sandbox.dom.closest(event.target, '.item-table-row'),
                rowId = this.sandbox.dom.attr($row, 'id');
            return {
                row: $row,
                id: rowId
            };
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
            var data, rowTpl, id,
                processed = 0;

            if (increaseCount !== false) {
                this.rowCount++;
            }

            // get sum of already processed items
            if (!!this.options.processedItems) {
                id = itemData.item.id;
                processed = this.options.processedItems[id] || 0;
            } else if (itemData.hasOwnProperty(this.options.processedQuantityValueName)) {
                processed = itemData[this.options.processedQuantityValueName];
            }
            // do not show completely processed items (if they are not processed in this entity)
            if (!this.options.showFinishedItems && itemData.quantity === 0) {
                if (itemData.quantity - processed < 1) {
                    return;
                }
            }

            data = this.sandbox.util.extend({}, rowDefaults, itemData, {
                rowId: constants.rowIdPrefix + this.rowCount,
                rowNumber: this.rowCount,
                isEditable: this.options.isEditable,
                isNew: this.options.isNew,
                canAddRemove: this.options.canAddRemove,
                processedQuantity: processed,
                numberFormat: this.sandbox.numberFormat
            });
            // calculate max quantity
            data.maxQuantity = (parseFloat(data.item.quantity) - parseFloat(processed)).toFixed(1);
            // parse to int
            data.maxQuantity = (data.maxQuantity % 1 === 0) ? parseInt(data.maxQuantity) : data.maxQuantity;
            // correct number format
            data.formattedMaxQuantity = this.sandbox.numberFormat(data.maxQuantity,'d');

            rowTpl = this.sandbox.util.template(RowTpl, data);
            // create row and return it
            return this.sandbox.dom.createElement(rowTpl);
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

            // add item to data
            addItemData.call(this, rowId, itemData);

            // add to validation
            addValidationFields.call(this, $row);

            // emit data change
            this.sandbox.emit(EVENT_CHANGED);

            return $row;
        },

        /**
         * add validation to row
         * @param $row
         */
        addValidationFields = function($row) {
            this.sandbox.form.addField(constants.formId, this.sandbox.dom.find(constants.quantityInput, $row));
            this.sandbox.form.addField(constants.formId, this.sandbox.dom.find(constants.priceInput, $row));
            this.sandbox.form.addField(constants.formId, this.sandbox.dom.find(constants.discountInput, $row));
        },

        /**
         * remove validation from row
         * @param $row
         */
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
            this.items = {};
            this.sandbox.dom.empty(this.$find(constants.listClass));
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

        /**
         * renders table head
         */
        renderHeader = function() {
            var rowData = this.sandbox.util.extend({
                    canAddRemove: this.options.canAddRemove
                }, rowDefaults, getHeaderData.call(this)),
                rowTpl = this.sandbox.util.template(RowHeadTpl, rowData);
            this.sandbox.dom.append(this.$find(constants.listClass), rowTpl);
        },

        /**
         * sets components data-items to current items
         */
        refreshItemsData = function() {
            this.sandbox.dom.data(this.$el, 'items', this.getItems());
        },

        getProcessedByItemId = function(id) {
            var i, item;
            for (i in this.options.data) {
                item = this.options.data[i].item;
                if (item.id === id) {
                    return this.options.data[i];
                }
            }
            return null;
        },

        /**
         * redefines this.options.data
         * creates a new processed item for every item defined in this.options.dataItems
         * and assigns items to it
         *
         * @returns {Array}
         */
        assignItemsToProcessed = function() {
            // add items to shipping items
            var i, processed, item,
                dataProcessed = [];

            // for every item find a processed item or create a new one
            for (i in this.options.itemsData) {
                item = this.options.itemsData[i];
                processed = getProcessedByItemId.call(this, item.id);
                // if no processed item was found, create a new one
                if (!processed) {
                    processed = this.sandbox.util.extend({}, rowDefaults, {item: item});
                }
                dataProcessed.push(processed);
            }

            this.options.data = dataProcessed;
            return dataProcessed;
        },

        /**
         * initialize husky-validation
         */
        initializeForm = function() {
            this.sandbox.form.create(constants.formId);
        };

    return {
        initialize: function() {
            var dataItems, dataProcessed, i, processed, item;
            // load defaults
            this.options = this.sandbox.util.extend({}, defaults, this.options);

            // variables
            this.items = {};
            this.rowCount = 0;
            this.table = null;

            this.isEmpty = this.items.length

            // if data is not set, check if it's set in elements DATA
            dataItems = this.sandbox.dom.data(this.$el, 'items');
            if (this.options.data.length === 0 && !!dataItems && dataItems.length > 0) {
                this.options.data = dataItems;
            }

            // completes data
            assignItemsToProcessed.call(this);

            // render component
            this.render();

            // event listener
            bindCustomEvents.call(this);
            bindDomEvents.call(this);

            refreshItemsData.call(this);
        },

        render: function() {

            // add translations for template
            var templateData = this.sandbox.util.extend({},
                {
                    addText: this.sandbox.translate('salescore.item.add'),
                    isEditable: this.options.isEditable,
                    canAddRemove: this.options.canAddRemove
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
