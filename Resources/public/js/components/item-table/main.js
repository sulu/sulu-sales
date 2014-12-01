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
 * @param {Array}  [options.data] Array of data [string, object]
 * @param {Bool}  [options.isEditable] Defines if component is editable
 * @param {Array}  [options.columns] Defines which columns should be shown. Array of strings
 * @param {Bool}  [options.hasNestedItems] this is used, when data array is merged (must be an object
 *        containing an attribute called 'item'
 * @param {Array}  [options.defaultData] can be used to pass extra default parameters to an item
 * @param {Object}  [options.columnCallbacks] if a specific column is clicked (as name) a callback can be defined
 *        by provide key with a function
 * @param {Object}  [options.rowCallback] Is called, when a row is clicked. Passes rowId and rowData
 * @param {Bool}  [options.showSettings] If true, the items settings overlay is displayed on click on a row
 */
define([
    'text!sulusalescore/components/item-table/item.form.html',
    'text!sulusalescore/components/item-table/item.row.html',
    'text!sulusalescore/components/item-table/item.row-head.html',
    'text!sulusalescore/components/item-table/item.overlay.html'
], function(FormTpl, RowTpl, RowHeadTpl, Overlay) {

    'use strict';

    // TODO: implement taxfree

    var defaults = {
            data: [],
            isEditable: true,
            columns: [
                'name',
                'number',
                'settings',
                'quantity',
                'quantityUnit',
                'price',
                'discount',
                'totalPrice'
            ],
            hasNestedItems: false,
            defaultData: {},
            columnCallbacks: {},
            rowCallback: null,
            showSettings: false
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
            discountInput: '.item-discount input',
            globalPriceTableClass: '.global-price-table',
            overallEmptyString: '-'
        },

        selectors = {
            overlayClass: 'item-overlay',
            overlayClassSelector: '.item-overlay'
        },

        /**
         * default values of a item row
         */
        rowDefaults = {
            rowClass: null,
            id: null,
            rowNumber: null,
            address: null,
            addressObject: null,
            description: null,
            rowId: '',
            name: '',
            number: '',
            quantity: '',
            quantityUnit: 'pc',
            price: '',
            discount: null,
            overallPrice: '',
            currency: 'EUR',
            useProductsPrice: false,
            tax: 0,
            supplierName: ''
        },

        templates = {
            priceRow: function(title, value) {
                return [
                    '<tr>',
                    '   <td>',
                    title,
                    '   </td>',
                    '   <td>',
                    value,
                    '   </td>',
                    '</tr>'
                ].join('');
            }
        },

    // event namespace
        eventNamespace = 'sulu.item-table.',

        /**
         * raised when an item is changed
         * @event sulu.item-table.changed
         */
        EVENT_CHANGED = function() {
            return eventNamespace + 'changed';
        },

        /**
         * Sets new default data
         *
         * @param key
         * @param value
         *
         * @event sulu.item-table[.INSTANCENAME].set-default-data
         */
        EVENT_SET_DEFAULT_DATA = function() {
            return getEventName.call(this, 'set-default-data');
        },

        /**
         * returns event name
         * @param suffix
         * @returns {string}
         */
        getEventName = function(suffix) {
            return eventNamespace + this.options.instanceName + '.' + suffix;
        },

        /**
         * data that is shown in header
         */
        getHeaderData = function() {
            return {
                rowClass: 'header',
                name: this.sandbox.translate('salescore.item.product'),
                number: this.sandbox.translate('salescore.item.number'),
                address: this.sandbox.translate('address.delivery'),
                description: this.sandbox.translate('salescore.item.description'),
                quantity: this.sandbox.translate('salescore.item.quantity'),
                quantityUnit: this.sandbox.translate('salescore.item.unit'),
                price: this.sandbox.translate('salescore.item.price'),
                discount: this.sandbox.translate('salescore.item.discount'),
                overallPrice: this.sandbox.translate('salescore.item.overall-value')
            };
        },

        /**
         * bind custom events
         */
        bindCustomEvents = function() {
            this.sandbox.on(EVENT_SET_DEFAULT_DATA.call(this), setDefaultData.bind(this));
        },

        /**
         * bind dom events
         */
        bindDomEvents = function() {

            // add new item
            this.sandbox.dom.on(this.$el, 'click', addNewItemClicked.bind(this), '.add-row');
            // remove row
            this.sandbox.dom.on(this.$el, 'click', removeRowClicked.bind(this), '.remove-row');

            //
            this.sandbox.dom.on(this.$el, 'click', rowClicked.bind(this), '.item-table-row');
            // add new item
            this.sandbox.dom.on(this.$el, 'click', rowCellClicked.bind(this), '.item-table-row td');

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
         * sets default data
         * @param key
         * @param value
         */
        setDefaultData = function(key, value) {
            this.options.defaultData[key] = value;
        },

        /**
         * triggers callback function if set for column
         * @param event
         */
        rowClicked = function(event) {
            // if inupt or link was clicked, do nothing
            if (event.target.tagName.toUpperCase() === 'INPUT' ||
                event.target.tagName.toUpperCase() === 'A' ) {
                return;
            }

            var rowId = this.sandbox.dom.data(event.currentTarget, 'id');
            // call rowCallback
            if (!!this.options.rowCallback) {
                this.options.rowCallback.call(this, rowId, this.items[rowId]);
            }

            // if settings are activated, show them
            if (this.options.showSettings === true || this.options.showSettings === 'true') {
                initSettingsOverlay.call(this, this.items[rowId]);
            }
        },

        /**
         * triggers callback function if set for column
         * @param event
         */
        rowCellClicked = function(event) {
            var name = this.sandbox.dom.data(event.currentTarget, 'name'),
                rowId = this.sandbox.dom.data(this.sandbox.dom.parent(), 'id');
            if (name && this.options.columnCallbacks.hasOwnProperty(name)) {
                this.options.columnCallbacks[name].call(this, event.currentTarget, rowId);
            }
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

            // update rows overall price
            updateOverallPrice.call(this, rowId);

            // update overall price
            updateGlobalPrice.call(this);

            this.sandbox.emit(EVENT_CHANGED.call(this));
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

            // update rows overall price
            updateOverallPrice.call(this, rowId);

            // update overall price
            updateGlobalPrice.call(this);

            this.sandbox.emit(EVENT_CHANGED.call(this));
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

            // update rows overall price
            updateOverallPrice.call(this, rowId);

            // update overall price
            updateGlobalPrice.call(this);

            this.sandbox.emit(EVENT_CHANGED.call(this));
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
         *
         * @param rowId
         */
        updateOverallPrice = function(rowId) {
            var $row = this.$find('#' + rowId),
                item = this.items[rowId],
                $priceCol = this.sandbox.dom.find('.item-overall-price span', $row);

            this.sandbox.dom.html($priceCol, getOverallPriceString.call(this, item));

        },

        /**
         * updates row with global prices
         */
        updateGlobalPrice = function() {
            var tax, item, price, taxPrice,
                $table,
                taxCategory = {},
                netPrice = 0,
                globalPrice = 0;

            for (var i in this.items) {
                item = this.items[i];
                price = parseFloat(getOverallPrice.call(this, item));
                taxPrice = 0;

                if (!!item.tax && item.tax > 0 && item.tax <= 100) {
                    tax = parseFloat(item.tax);
                    taxPrice = (price / 100) * tax;
                    // tax group
                    taxCategory[tax] = !taxCategory[tax] ? taxPrice : taxCategory[tax] + taxPrice;
                }

                // sum up prices
                // net
                netPrice += price;
                // overall
                globalPrice += price + taxPrice;
            }

            // visualize
            $table = this.$find(constants.globalPriceTableClass);
            this.sandbox.dom.html($table, '');

            if (Object.keys(this.items).length > 0) {
                // add net price
                addPriceRow.call(this, $table, this.sandbox.translate('salescore.item.net-price'), getFormatedPriceCurrencyString.call(this, netPrice));

                // add row for every tax group
                for (var i in taxCategory) {
                    addPriceRow.call(this, $table, this.sandbox.translate('salescore.item.vat') + '.(' + i + '%)', getFormatedPriceCurrencyString.call(this, taxCategory[i]));
                }

                addPriceRow.call(this, $table, this.sandbox.translate('salescore.item.overall-price'), getFormatedPriceCurrencyString.call(this, globalPrice));
            }
        },

        addPriceRow = function($table, title, value) {
            var $row = this.sandbox.dom.createElement(templates.priceRow.call(this, title, value));
            this.sandbox.dom.append($table, $row);
        },

        /**
         * returns formated overallPrice + currency as string (based on item)
         * @param item
         * @param mode
         * @returns {string}
         */
        getOverallPriceString = function(item, mode) {
            return getFormatedPriceCurrencyString.call(this,
                getOverallPrice.call(this, item, mode),
                getCurrency.call(this, item));
        },

        /**
         * returns formated overallprice + currency as string (based on value)
         * @param value
         * @param currency
         * @returns {string}
         */
        getFormatedPriceCurrencyString = function(value, currency) {
            currency = !!currency ? currency : rowDefaults.currency;
            return this.sandbox.numberFormat(value, 'n') + ' ' + currency;
        },

        /**
         * returns the overall price
         * @param item
         * @param mode
         * @returns number
         */
        getOverallPrice = function(item, mode) {
            var value = 0;
            if (!mode || mode === 'default') {
                if (!!item.price && !!item.quantity) {

                    // TODO numbers should parsed with globalize #336
                    value = (item.price * item.quantity);

                    // discount
                    if (!!item.discount && item.discount > 0 && item.discount <= 100) {
                        value -= (value / 100) * item.discount;
                    }
                }
            }

            return value;
        },

        /**
         * returns items currency; if not set, default-currency
         * @param item
         * @returns string
         */
        getCurrency = function(item) {
            return !!item.currency ? item.currency : rowDefaults.currency;
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
                        el: this.sandbox.dom.find(constants.productSearchClass, $row),
                        size: '15px'
                    }
                }
            ]);

            // load product details
            this.sandbox.util.load(constants.productUrl + product.id)
                .then(function(response) {
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
            event.stopPropagation();

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

            // remove from data
            removeItemData.call(this, rowId);

            // remove validation
            removeValidationFields.call(this, $row);

            // refresh global price
            updateGlobalPrice.call(this);

            this.sandbox.emit(EVENT_CHANGED.call(this));
        },

        /**
         * creates and returns a new row element
         */
        createItemRow = function(itemData, increaseCount) {
            if (increaseCount !== false) {
                this.rowCount++;
            }

            var rowTpl, $row,
                data = this.sandbox.util.extend({}, rowDefaults, this.options.defaultData, itemData,
                    {
                        isEditable: this.options.isEditable,
                        columns: this.options.columns,
                        rowId: constants.rowIdPrefix + this.rowCount,
                        rowNumber: this.rowCount
                    });

            // handle address
            if (!!data.address && typeof data.address === 'object') {
                data.addressObject = data.address;
                data.address = this.sandbox.sulu.createAddressString(data.address);
            }

            data.overallPrice = getOverallPriceString.call(this, data);
            rowTpl = this.sandbox.util.template(RowTpl, data),
                $row = this.sandbox.dom.createElement(rowTpl);
            return $row;
        },

        /**
         * adds an existing item to the list
         * @param itemData
         */
        addItemRow = function(itemData) {
            var $row, nested;
            // if nested, then define item
            if (this.options.hasNestedItems) {
                nested = itemData;
                // get data and merge with data one level above
                itemData = this.sandbox.util.extend({}, itemData.item, nested);
                delete itemData.item;
            }

            // create row
            $row = createItemRow.call(this, itemData);
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
            this.sandbox.emit(EVENT_CHANGED.call(this));

            return $row;
        },

        /**
         * add validation to row
         * @param $row
         */
        addValidationFields = function($row) {
            if (this.options.columns.indexOf('quantity') > 0) {
                this.sandbox.form.addField(constants.formId, this.sandbox.dom.find(constants.quantityInput, $row));
            }
            if (this.options.columns.indexOf('price') > 0) {
                this.sandbox.form.addField(constants.formId, this.sandbox.dom.find(constants.priceInput, $row));
            }
            if (this.options.columns.indexOf('discount') > 0) {
                this.sandbox.form.addField(constants.formId, this.sandbox.dom.find(constants.discountInput, $row));
            }
        },

        /**
         * remove validation from row
         * @param $row
         */
        removeValidationFields = function($row) {
            if (this.options.columns.indexOf('quantity') > 0) {
                this.sandbox.form.removeField(constants.formId, this.sandbox.dom.find(constants.quantityInput, $row));
            }
            if (this.options.columns.indexOf('price') > 0) {
                this.sandbox.form.removeField(constants.formId, this.sandbox.dom.find(constants.priceInput, $row));
            }
            if (this.options.columns.indexOf('discount') > 0) {
                this.sandbox.form.removeField(constants.formId, this.sandbox.dom.find(constants.discountInput, $row));
            }
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
         * sets an item, based on product
         * @param productData
         * @returns {*}
         */
        setItemByProduct = function(productData) {
            // merge with row defaults
            var itemData = this.sandbox.util.extend({}, rowDefaults, this.options.defaultData,
                {
                    name: productData.name,
                    number: productData.number,
                    description: productData.shortDescription,
                    product: productData,
                    quantityUnit: !!productData.orderUnit ? productData.orderUnit.name : ''
                }
            );

            // get prices
            if (!!productData.prices && productData.prices.length > 0) {
                itemData.price = productData.prices[0].price;
//                for (i = -1, len = productData.price; ++i < len;) {
                // TODO get price with the correct currency https://github.com/massiveart/POOL-ALPIN/issues/337
//            }
            }

            // set supplierName as tooltip, if set
            if (!!productData.supplierName) {
                itemData.supplierName = productData.supplierName;
            }

            return itemData;
        },

        /**
         * Inits the overlay with a specific template
         */
        initSettingsOverlay = function(data) {
            var $overlay, $content;

            data = this.sandbox.util.extend({
                columns: []
            }, data);

            // prevent multiple initialization of the overlay
            this.sandbox.stop(this.sandbox.dom.find(constants.overlayClassSelector, this.$el));
            this.sandbox.dom.remove(this.sandbox.dom.find(constants.overlayClassSelector, this.$el));

            $content = this.sandbox.util.template(Overlay, data);
            $overlay = this.sandbox.dom.createElement('<div class="' + constants.overlayClass + '"></div>');
            this.sandbox.dom.append(this.$el, $overlay);

            this.sandbox.start([
                {
                    name: 'overlay@husky',
                    options: {
                        el: $overlay,
                        title: this.sandbox.translate('test 123'),
                        openOnStart: true,
                        removeOnClose: false,
                        skin: 'wide',
                        data: $content,
                        okCallback: function() {

                        }.bind(this)
                    }
                }
            ]);
        },

        /**
         * renders table head
         */
        renderHeader = function() {
            var rowData = this.sandbox.util.extend({}, rowDefaults, this.options, {header: getHeaderData.call(this)}),
                rowTpl = this.sandbox.util.template(RowHeadTpl, rowData);
            this.sandbox.dom.append(this.$find(constants.listClass), rowTpl);
        },

        /**
         * sets components data-items to current items
         */
        refreshItemsData = function() {
            this.sandbox.dom.data(this.$el, 'items', this.getItems());
        },

        /**
         * initialize husky-validation
         */
        initializeForm = function() {
            this.sandbox.form.create(constants.formId);
        };

    return {
        initialize: function() {
            // load defaults
            this.options = this.sandbox.util.extend({}, defaults, this.options);

            // variables
            this.items = {};
            this.rowCount = 0;
            this.table = null;

            this.isEmpty = this.items.length;

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
                    addText: this.sandbox.translate('salescore.item.add'),
                    isEditable: this.options.isEditable,
                    columns: this.options.columns
                }
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

            // set global price
            updateGlobalPrice.call(this);
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
