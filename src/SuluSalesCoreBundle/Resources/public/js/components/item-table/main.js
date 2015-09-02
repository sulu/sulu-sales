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
 * @param {Bool}   [options.isEditable] Defines if component is editable
 * @param {Array}  [options.columns] Defines which columns should be shown. Array of strings
 * @param {Bool}   [options.hasNestedItems] this is used, when data array is merged (must be an object
 *                 containing an attribute called 'item'
 * @param {Array}  [options.defaultData] can be used to pass extra default parameters to an item
 * @param {Object} [options.columnCallbacks] if a specific column is clicked (as name) a callback can be defined
 *                 by provide key with a function
 * @param {Object} [options.rowCallback] Is called, when a row is clicked. Passes rowId and rowData
 * @param {Object} [options.settings] Configuration Object for displaying Options overlay
 * @param {Object} [options.urlFilter] Object containing key value pairs to extend the url
 * @param {String} [options.addressKey] Defines how to access address value over api
 * @param {Bool}   [options.allowDuplicatedProducts] Defines if a product can be added multiple times to items list
 */
define([
    'text!sulusalescore/components/item-table/item.form.html',
    'text!sulusalescore/components/item-table/item.row.html',
    'text!sulusalescore/components/item-table/item.row-head.html',
    'text!sulusalescore/components/item-table/item.overlay.html',
    'config',
    'suluproduct/util/price-calculation-util'
], function(FormTpl, RowTpl, RowHeadTpl, Overlay, Config, PriceCalcUtil) {

    'use strict';

    // TODO: implement taxfree
    // TODO: order-address handling: set contact-data as well

    var defaults = {
            urlFilter: {},
            formId: 'item-table-form',
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
            settings: false,
            addressKey: 'deliveryAddress',
            allowDuplicatedProducts: true
        },

        urls = {
            products: '/admin/api/products{?filter*}',
            product: '/admin/api/products/'
        },

        constants = {
            listClass: '.item-table-list',
            formSelector: '.item-table-list-form',
            productSearchClass: '.product-search',
            rowIdPrefix: 'item-table-row-',
            rowClass: '.item-table-row',
            quantityRowClass: '.item-quantity',
            quantityInput: '.item-quantity input',
            priceRowClass: '.item-price',
            priceInput: '.item-price input',
            discountRowClass: '.item-discount',
            discountInput: '.item-discount input',
            globalPriceTableClass: '.global-price-table',
            overallEmptyString: '-',
            loaderSelector: '.item-table-loader',
            loaderClass: 'item-table-loader',
            overlayClassSelector: '.settings-overlay',
            autocompleteLimit: 20
        },
        
        translations = {
            defaultAddress: 'salescore.use-main-delivery-address'
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
            description: '',
            rowId: '',
            name: '',
            number: '',
            quantity: '',
            quantityUnit: '',
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
            },
            loader: function(classes) {
                return '<div style="display:hidden" class="grid-row ' + classes + '"></div>';
            }
        },

        // event namespace
        eventNamespace = 'sulu.item-table.',

        /**
         * raised when item-table is initialized
         * @event sulu.item-table[.INSTANCENAME].initialized
         */
        EVENT_INITIALIZED = function() {
            return getEventName.call(this, 'initialized');
        },

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
         * TODO: Reset all addresses to a certain address
         *
         * @param {object} address
         *
         * @event sulu.item-table[.INSTANCENAME].reset-item-addresses
         */
        EVENT_RESET_ITEM_ADDRESSES = function() {
            return getEventName.call(this, 'reset-item-addresses');
        },

        /**
         * Changes the currency and selects related price if available
         * @event sulu.item-table[.INSTANCENAME].change-currency
         */
        EVENT_CHANGE_CURRENCY = function() {
            return getEventName.call(this, 'change-currency');
        },

        /**
         * Set addresses of overlay select
         *
         * @param addresses
         *
         * @event sulu.item-table[.INSTANCENAME].set-addresses
         */
        EVENT_SET_ADRESSES = function() {
            return getEventName.call(this, 'set-addresses');
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
            this.sandbox.on(EVENT_CHANGE_CURRENCY.call(this), changeCurrency.bind(this));
            this.sandbox.on(EVENT_SET_ADRESSES.call(this), setAddresses.bind(this));
            this.sandbox.on(EVENT_RESET_ITEM_ADDRESSES.call(this), resetItemAddresses.bind(this));
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
         * Set addresses of settings overlay
         * @param addresses
         */
        setAddresses = function(addresses) {
            if (!!this.options.settings) {
                this.options.settings.addresses = addresses;
            }
        },

        /**
         * Resets addresses of all items to a certain address
         * @param address
         */
        resetItemAddresses = function(address) {
            var i, item;
            
            // reset default address
            setDefaultData.call(this, 'address', address);
            
            // reset addresses of all items
            for (i in this.items) {
                if (this.items.hasOwnProperty(i)) {
                    item = this.items[i];
                    item.address = address;
                }
            }
        },

        /**
         * Changes currency for item table
         * @param currency
         */
        changeCurrency = function(currency) {
            var ids,
                dfdLoadedProducts,
                dfdLoaderStarted = new this.sandbox.data.deferred();

            this.currency = currency;
            ids = getAllProductIds.call(this, this.items);

            if (!!ids && ids.length > 0) {
                startLoader.call(this, dfdLoaderStarted);
                dfdLoadedProducts = fetchProductData.call(this, ids);

                // go on when loader is fully loaded and product data retrieved
                // dfdLoadedProducts needs to be the first param
                this.sandbox.dom.when(dfdLoadedProducts, dfdLoaderStarted)
                    .done(function(data) {
                        updatePricesForEachProduct.call(this, data);
                        updateGlobalPrice.call(this);
                        stopLoader.call(this);
                    }.bind(this))
                    .fail(function(jqXHR, textStatus, error) {
                        this.sandbox.emit('sulu.labels.error.show',
                            this.sandbox.translate('salescore.item-table.error.changing-currency'),
                            'labels.error',
                            ''
                        );
                        this.sandbox.logger.error(jqXHR, textStatus, error);
                    }.bind(this)
                );
            }
        },

        /**
         * Updates the price for every product and the total
         * @param data
         */
        updatePricesForEachProduct = function(data) {
            var $el,
                item,
                prop,
                prices = getArrayForProductPrices.call(this, data._embedded.products);

            // update price and input value
            for (prop in this.items) {
                if (this.items.hasOwnProperty(prop)) {
                    item = this.items[prop];

                    // get price
                    if (!!prices[item.product.id] && !!prices[item.product.id][this.currency]) {
                        item.price = prices[item.product.id][this.currency];
                    } else {
                        item.price = 0;
                    }

                    // update input in dom
                    $el = this.sandbox.dom.find(constants.priceInput, this.sandbox.dom.find('#' + prop, this.$list));
                    this.sandbox.dom.val($el, this.sandbox.numberFormat(item.price, 'n'));

                    // update row total price
                    updateOverallPrice.call(this, prop);
                }
            }
        },

        /**
         * Returns an associative array of productIds and prices
         * @param products
         * @returns Array associative array of productsIds/prices
         */
        getArrayForProductPrices = function(products) {
            var data = {};
            this.sandbox.util.foreach(products, function(value) {
                data[value.id] = {};
                this.sandbox.util.foreach(value.prices, function(price) {
                    data[value.id][price.currency.code] = price.price || 0;
                }.bind(this));
            }.bind(this));

            return data;
        },

        /**
         * Loads product data
         * @param ids
         */
        fetchProductData = function(ids) {
            var url = this.sandbox.uritemplate.parse(urls.products).expand({
                filter: {
                    'ids': ids.join(',')
                }
            });
            return this.sandbox.util.load(url);
        },

        /**
         * Stops the loader component and shows the list again
         */
        stopLoader = function() {
            this.sandbox.stop(this.$loader);
            this.sandbox.dom.show(this.$list);
        },

        /**
         * Shows and starts a loader
         * @param dfdLoaderStarted
         */
        startLoader = function(dfdLoaderStarted) {
            prepareDomForLoader.call(this);
            this.sandbox.start([
                    {
                        name: 'loader@husky',
                        options: {
                            el: this.$loader,
                            size: '40px',
                            hidden: false
                        }
                    }
                ]
            ).done(function() {
                    dfdLoaderStarted.resolve();
                }.bind(this));
        },

        /**
         * Creats dom element for loader and appends it to
         */
        prepareDomForLoader = function() {
            var height = this.sandbox.dom.height(this.$el);

            this.$loader = this.sandbox.dom.createElement(templates.loader.call(this, constants.loaderClass));
            this.$list = this.sandbox.dom.find(constants.formSelector, this.$el);

            this.sandbox.dom.append(this.$el, this.$loader);
            this.sandbox.dom.height(this.$loader, height);
            this.sandbox.dom.hide(this.$list);
            this.sandbox.dom.show(this.$loader);
        },

        /**
         * Retrievs all product ids from all products currently in the table
         * @param items
         * @returns {Array} of ids
         */
        getAllProductIds = function(items) {
            var ids = [], el;
            for (el in items) {
                if (!!items[el].hasOwnProperty('product')) {
                    ids.push(items[el].product.id);
                }
            }
            return ids;
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
            // if input or link was clicked, do nothing
            if (event.target.tagName.toUpperCase() === 'INPUT' ||
                event.target.tagName.toUpperCase() === 'A' ||
                !this.options.isEditable) {
                return;
            }

            var rowId = this.sandbox.dom.attr(event.currentTarget, 'id'),
                dataId = this.sandbox.dom.data(event.currentTarget, 'id');
            // call rowCallback
            if (!!this.options.rowCallback) {
                this.options.rowCallback.call(this, rowId, this.items[rowId]);
            }

            // if settings are activated, show them
            if (!!this.options.settings && this.options.settings !== 'false' && (!!dataId || dataId === 0)) {
                initSettingsOverlay.call(this, this.items[rowId], this.options.settings, rowId);
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
            this.items[rowId].quantity = this.sandbox.parseFloat(this.sandbox.dom.val(event.target));
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
            this.items[rowId].price = this.sandbox.parseFloat(this.sandbox.dom.val(event.target));
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
            this.items[rowId].discount = this.sandbox.parseFloat(this.sandbox.dom.val(event.target));
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
            var items = this.getItems(), result, $table, i;

            if (!!items && items.length > 0 && !!items[0].price) {
                result = PriceCalcUtil.getTotalPricesAndTaxes(this.sandbox, this.items);

                // visualize
                $table = this.$find(constants.globalPriceTableClass);
                this.sandbox.dom.empty($table);

                if (!!result) {
                    // add net price
                    addPriceRow.call(
                        this,
                        $table,
                        this.sandbox.translate('salescore.item.net-price'),
                        PriceCalcUtil.getFormattedAmountAndUnit(this.sandbox, result.netPrice, this.currency)
                    );

                    // add row for every tax group
                    for (i in result.taxes) {
                        addPriceRow.call(
                            this,
                            $table,
                            this.sandbox.translate('salescore.item.vat') + '.(' + i + '%)',
                            PriceCalcUtil.getFormattedAmountAndUnit(this.sandbox, result.taxes[i], this.currency)
                        );
                    }

                    addPriceRow.call(
                        this,
                        $table,
                        this.sandbox.translate('salescore.item.overall-price'),
                        PriceCalcUtil.getFormattedAmountAndUnit(this.sandbox, result.grossPrice, this.currency)
                    );
                }

            }
        },

        addPriceRow = function($table, title, value) {
            var $row = this.sandbox.dom.createElement(templates.priceRow.call(this, title, value));
            this.sandbox.dom.append($table, $row);
        },

        /**
         * returns formated overallPrice + currency as string (based on item)
         * @param item
         * @returns {string}
         */
        getOverallPriceString = function(item) {
            setItemDefaults(item);
            return PriceCalcUtil.getTotalPrice(
                this.sandbox,
                item.price,
                getCurrency.call(this, item),
                item.discount,
                item.quantity,
                item.tax,
                true
            );
        },

        /**
         * Sets defaults for items for proper calculation
         * @param item
         */
        setItemDefaults = function(item) {
            item.price = item.price || 0;
            item.discount = item.discount || 0;
            item.quantity = item.quantity || 0;
            item.tax = item.tax || 0;
        },

        /**
         * returns items currency; if not set, default-currency
         * @param item
         * @returns string
         */
        getCurrency = function(item) {
            return !!item.currency ? item.currency : this.currency;
        },

        /**
         * Called when a product gets selected in auto-complete.
         *
         * @param {Object} product
         * @param {Object} event
         */
        productSelected = function(product, event) {

            var $row = this.sandbox.dom.closest(event.currentTarget, constants.rowClass),
                rowId = this.sandbox.dom.attr($row, 'id'),
                itemData = {};

            if (productIsForbiddenDuplicate.call(this, product.id)) {
                this.sandbox.emit('sulu.labels.warning.show',
                    this.sandbox.translate('salescore.item-table.warning.product-already-added'),
                    'labels.warning',
                    ''
                );
                return;
            }

            this.addedProductIds.push(product.id);

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
            this.sandbox.util.load(urls.product + product.id)
                .then(function(response) {
                    // set item to product
                    itemData = setItemByProduct.call(this, response);
                    updateItemRow.call(this, rowId, itemData);
                }.bind(this))
                .fail(function(request, message, error) {
                        this.sandbox.emit('sulu.labels.error.show',
                            this.sandbox.translate('salescore.item-table.error.loading-product'),
                            'labels.error',
                            ''
                        );
                        this.sandbox.logger.error(request, message, error);
                }.bind(this));
        },

        /**
         * Checks if a product is not an unallowed duplicate.
         *
         * @param {String|Int} productId
         *
         * @returns {Bool}
         */
        productIsForbiddenDuplicate = function(productId) {
            if (!this.options.allowDuplicatedProducts && this.addedProductIds.indexOf(productId) !== -1) {
                return true;
            }

            return false;
        },

        /**
         * Initializes the product's auto-complete.
         * TODO: move to template when mapper type is implemented
         *
         * @param {Object} $row
         */
        initProductSearch = function($row) {
            var options = Config.get('suluproduct.components.autocomplete.default');
            var remoteUrl = options.remoteUrl + '{&filter*}{&limit*}';
            options.remoteUrl = this.sandbox.uritemplate.parse(remoteUrl).expand({
                filter: this.options.urlFilter,
                limit: constants.autocompleteLimit
            });
            options.el = this.sandbox.dom.find(constants.productSearchClass, $row);
            options.selectCallback = productSelected.bind(this);
            options.limit = constants.autocompleteLimit;
            options.instanceName += this.rowCount;

            // initialize auto-complete when adding a new Item
            this.sandbox.start([
                {
                    name: 'auto-complete@husky',
                    options: options
                }
            ]).then(function() {
                if (!!this.$lastAddedRow) {
                    var $input = this.sandbox.dom.find('input', this.$lastAddedRow)[0];
                    this.sandbox.dom.focus($input);
                    this.$lastAddedRow = null;
                }
            }.bind(this));
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
         * Removes item at rowId.
         *
         * @param {String} rowId
         */
        removeItemData = function(rowId) {
            // remove from items data
            delete this.items[rowId];

            refreshItemsData.call(this);
        },

        /**
         * Adds item to data at index rowId.
         *
         * @param {String} rowId
         * @param {Object} itemData
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
         * Removes an item-row.
         *
         * @param {String} rowId
         * @param {Object} $row the row element
         */
        removeItemRow = function(rowId, $row) {
            // remove from table
            this.sandbox.dom.remove($row);

            // remove product id
            if (!!this.items[rowId] && !!this.items[rowId].product) {
                var index = this.addedProductIds.indexOf(this.items[rowId].product.id);
                this.addedProductIds.splice(index,1);
            }

            // remove from data
            removeItemData.call(this, rowId);

            // remove validation
            removeValidationFields.call(this, $row);

            // refresh global price
            updateGlobalPrice.call(this);

            this.sandbox.emit(EVENT_CHANGED.call(this));
        },

        /**
         * Creates and returns a new row element.
         *
         * @param {Object} itemData
         * @param {String} rowId
         *
         * @returns {Object}
         */
        createItemRow = function(itemData, rowId) {
            if (!rowId) {
                this.rowCount++;
            }

            var rowTpl, $row,
                data = this.sandbox.util.extend({}, rowDefaults, this.options.defaultData, itemData,
                    {
                        isEditable: this.options.isEditable,
                        columns: this.options.columns,
                        rowId: rowId ? rowId : constants.rowIdPrefix + this.rowCount,
                        rowNumber: this.rowCount
                    });

            // handle address
            if (!!data.address && typeof data.address === 'object') {
                data.addressObject = data.address;
                data.address = this.sandbox.sulu.createAddressString(data.address);
            }

            data.currency = this.currency;
            data.overallPrice = this.sandbox.numberFormat(getOverallPriceString.call(this, data));

            // format numbers for cultural differences
            data.discount = this.sandbox.numberFormat(data.discount, 'n');
            data.price = this.sandbox.numberFormat(data.price, 'n');
            data.quantity = this.sandbox.numberFormat(data.quantity, 'n');

            rowTpl = this.sandbox.util.template(RowTpl, data);
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
            this.$lastAddedRow = $row;
            this.sandbox.dom.append(this.$find(constants.listClass), $row);
            return $row;
        },

        /**
         * updates a specific row
         * @param rowId
         * @param itemData
         */
        updateItemRow = function(rowId, itemData) {
            var $row = createItemRow.call(this, itemData, rowId);
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
                this.sandbox.form.addField(this.selectorFormId, this.sandbox.dom.find(constants.quantityInput, $row));
            }
            if (this.options.columns.indexOf('price') > 0) {
                this.sandbox.form.addField(this.selectorFormId, this.sandbox.dom.find(constants.priceInput, $row));
            }
            if (this.options.columns.indexOf('discount') > 0) {
                this.sandbox.form.addField(this.selectorFormId, this.sandbox.dom.find(constants.discountInput, $row));
            }
        },

        /**
         * remove validation from row
         * @param $row
         */
        removeValidationFields = function($row) {
            if (this.options.columns.indexOf('quantity') > 0) {
                this.sandbox.form.removeField(this.selectorFormId, this.sandbox.dom.find(constants.quantityInput, $row));
            }
            if (this.options.columns.indexOf('price') > 0) {
                this.sandbox.form.removeField(this.selectorFormId, this.sandbox.dom.find(constants.priceInput, $row));
            }
            if (this.options.columns.indexOf('discount') > 0) {
                this.sandbox.form.removeField(this.selectorFormId, this.sandbox.dom.find(constants.discountInput, $row));
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
            var i, len,
                itemData = this.sandbox.util.extend({}, rowDefaults, this.options.defaultData,
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
                for (i = -1, len = productData.prices.length; ++i < len;) {
                    if (productData.prices[i].currency.code === this.currency) {
                        itemData.price = productData.prices[i].price;
                    }
                }
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
        initSettingsOverlay = function(data, settings, rowId) {
            var $overlay, $content, title, subTitle,
                defaultAddressLabel = this.sandbox.translate(translations.defaultAddress);

            settings = this.sandbox.util.extend({
                columns: [],
                addresses: []
            }, settings);

            if (!!data[this.options.addressKey]) {
                defaultAddressLabel = this.sandbox.sulu.createAddressString(data[this.options.addressKey]);
            }

            data = this.sandbox.util.extend({
                settings: settings,
                defaultAddressLabel: defaultAddressLabel,
                createAddressString: this.sandbox.sulu.createAddressString,
                translate: this.sandbox.translate,
                deliveryDate: null,
                costCenter: null,
                discount: null,
                numberFormat: this.sandbox.numberFormat
            }, data);
            
            if (!data.hasOwnProperty(this.options.addressKey) || !data[this.options.addressKey]) {
                data[this.options.addressKey] = {id: null};
            }

            // prevent multiple initialization of the overlay
            this.sandbox.stop(this.sandbox.dom.find(constants.overlayClassSelector, this.$el));
            this.sandbox.dom.remove(this.sandbox.dom.find(constants.overlayClassSelector, this.$el));

            $content = this.sandbox.util.template(Overlay, data);
            $overlay = this.sandbox.dom.createElement('<div class="' + constants.overlayClass + '"></div>');
            this.sandbox.dom.append(this.$el, $overlay);

            title = data.name;
            subTitle = '#' + data.number;
            if (data.supplierName && data.supplierName !== '') {
                subTitle += '<br/>' + data.supplierName;
            }

            this.sandbox.start([
                {
                    name: 'overlay@husky',
                    options: {
                        el: $overlay,
                        title: title,
                        subTitle: subTitle,
                        instanceName: 'settings',
                        openOnStart: true,
                        removeOnClose: false,
                        skin: 'wide',
                        data: $content,
                        okCallback: function() {
                            var deliveryAddress = this.sandbox.dom.val(constants.overlayClassSelector + ' *[data-mapper-property="deliveryAddress"]'),
                                deliveryDate = this.sandbox.dom.val(constants.overlayClassSelector + ' *[data-mapper-property="deliveryDate"] input'),
                                costCenter = this.sandbox.dom.val(constants.overlayClassSelector + ' *[data-mapper-property="costCenter"]');

                            this.items[rowId].description = this.sandbox.dom.val(constants.overlayClassSelector + ' *[data-mapper-property="description"]');
                            this.items[rowId].quantity = this.sandbox.parseFloat(
                                this.sandbox.dom.val(constants.overlayClassSelector + ' *[data-mapper-property="quantity"]')
                            );
                            this.items[rowId].price = this.sandbox.parseFloat( 
                                this.sandbox.dom.val(constants.overlayClassSelector + ' *[data-mapper-property="price"]')
                            );
                            this.items[rowId].discount = this.sandbox.parseFloat( 
                                this.sandbox.dom.val(constants.overlayClassSelector + ' *[data-mapper-property="discount"]')
                            );

                            // set address
                            if (deliveryAddress !== '-1') {
                                // TODO: set whole order-address (contact-data as well)
                                this.items[rowId][this.options.addressKey] = getAddressById.call(this, deliveryAddress);
                                //delete this.items[rowId][this.options.addressKey].id; // delete reference to contact-address
                            }
                            this.items[rowId].deliveryDate = deliveryDate !== '' ? deliveryDate : null;
                            this.items[rowId].costCenter = costCenter !== '' ? costCenter : null;

                            updateItemRow.call(this, rowId, this.items[rowId]);
                            updateGlobalPrice.call(this, rowId);
                            refreshItemsData.call(this);
                        }.bind(this)
                    }
                },
                {
                    name: 'input@husky',
                    options: {
                        el: '#delivery-date',
                        skin: 'date',
                        instanceName: 'settings-delivery-date',
                        inputId: 'settings-delivery-date'
                    }
                }
            ]);
        },
        
        getAddressById = function(id) {
            var i,len,
                addresses = this.options.settings.addresses;
            for (i = -1, len = addresses.length; ++i < len;) {
                if (addresses[i].id.toString() === id.toString()) {
                    return addresses[i];
                }
            }
            return null;
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
            this.sandbox.form.create(this.selectorFormId);
        };

    return {
        initialize: function() {
            // load defaults
            this.options = this.sandbox.util.extend({}, defaults, this.options);

            this.selectorFormId = '#'+ this.options.formId;

            // variables
            this.items = {};
            this.rowCount = 0;
            this.addedProductIds = [];
            this.table = null;
            this.currency = this.options.currency || rowDefaults.currency;

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
                    formId: this.options.formId,
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

            this.sandbox.emit(EVENT_INITIALIZED.call(this));
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
