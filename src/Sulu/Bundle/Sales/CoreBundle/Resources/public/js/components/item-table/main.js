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
 * @param {Bool}   [options.displayToolbars] Defines if toolbars should be shown, when component is editable.
 *                  If false, no rows can be added or deleted.
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
 * @param {Bool}   [options.showItemCount] Defines if the column which shows the item count should be displayed.
 * @param {Bool}   [options.taxfree] Defines if table should contain taxes
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
            addressKey: 'deliveryAddress',
            allowDuplicatedProducts: true,
            columnCallbacks: {},
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
            data: [],
            defaultData: {},
            displayToolbars: true,
            formId: 'item-table-form',
            hasNestedItems: false,
            isEditable: true,
            rowCallback: null,
            settings: false,
            showItemCount: true,
            urlFilter: {},
            taxfree: false
        },

        urls = {
            products: '/admin/api/products{?filter*}',
            product: '/admin/api/products/',
            pricing: '/admin/api/pricings'
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
         * Raised when item-table is initialized.
         *
         * @event sulu.item-table[.INSTANCENAME].initialized
         */
        EVENT_INITIALIZED = function() {
            return getEventName.call(this, 'initialized');
        },

        /**
         * Raised when an item is changed.
         *
         * @event sulu.item-table.changed
         */
        EVENT_CHANGED = function() {
            return eventNamespace + 'changed';
        },

        /**
         * Sets new default data.
         *
         * @param {String} key
         * @param {mixed} value
         *
         * @event sulu.item-table[.INSTANCENAME].set-default-data
         */
        EVENT_SET_DEFAULT_DATA = function() {
            return getEventName.call(this, 'set-default-data');
        },

        /**
         * Reset all addresses to a certain address.
         *
         * @param {Object} address
         *
         * @event sulu.item-table[.INSTANCENAME].reset-item-addresses
         */
        EVENT_RESET_ITEM_ADDRESSES = function() {
            return getEventName.call(this, 'reset-item-addresses');
        },

        /**
         * Changes the currency and selects related price if available.
         *
         * @event sulu.item-table[.INSTANCENAME].change-currency
         */
        EVENT_CHANGE_CURRENCY = function() {
            return getEventName.call(this, 'change-currency');
        },

        /**
         * Set addresses of overlay select.
         *
         * @param {Array} addresses
         *
         * @event sulu.item-table[.INSTANCENAME].set-addresses
         */
        EVENT_SET_ADRESSES = function() {
            return getEventName.call(this, 'set-addresses');
        },

        /**
         * Event for retreiving item-table data.
         *
         * @param {Function} callback
         *
         * @event sulu.item-table[.INSTANCENAME].get-data
         */
        EVENT_GET_DATA = function() {
            return getEventName.call(this, 'get-data');
        },

        /**
         * Returns event name.
         *
         * @param {String} suffix
         *
         * @returns {String}
         */
        getEventName = function(suffix) {
            return eventNamespace + this.options.instanceName + '.' + suffix;
        },

        /**
         * Data that is shown in header.
         */
        getHeaderData = function() {
            return {
                rowClass: 'header',
                name: this.sandbox.translate('salescore.item.product'),
                number: this.sandbox.translate('salescore.item.number'),
                account: this.sandbox.translate('public.company'),
                customer: this.sandbox.translate('salescore.customer'),
                supplier: this.sandbox.translate('salescore.supplier'),
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
            this.sandbox.on(EVENT_GET_DATA.call(this), getData.bind(this));
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

        getItemRowById = function(rowId) {
            return this.sandbox.dom.find('#' + rowId, this.$list);
        },

        /**
         * Set addresses of settings overlay.
         *
         * @param {Array} addresses
         */
        setAddresses = function(addresses) {
            if (!!this.options.settings) {
                this.options.settings.addresses = addresses;
            }
        },

        /**
         * Set addresses of settings overlay.
         *
         * @param {Function} addresses
         */
        getData= function(callback) {
            if (typeof callback === "function") {
                callback(this.getItems());
            }
        },

        /**
         * Resets addresses of all items to a certain address.
         *
         * @param {Object} address
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
         * Changes currency for item table.
         *
         * @param {String} currency
         */
        changeCurrency = function(currency) {
            var rowIds,
                dfdLoadedProducts,
                dfdLoaderStarted = new this.sandbox.data.deferred();

            this.currency = currency;

            // get all row-Ids
            rowIds = Object.keys(this.items);

            if (!!rowIds && rowIds.length > 0) {
                startLoader.call(this, dfdLoaderStarted);

                dfdLoadedProducts = fetchPricesForRow.call(this, rowIds);

                // go on when loader is fully loaded and product data retrieved
                // dfdLoadedProducts needs to be the first param
                this.sandbox.dom.when(dfdLoadedProducts, dfdLoaderStarted)
                    .done(function(data) {
                        updatePricesForEachProduct.call(this);
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
         * Updates the price and total for every item.
         */
        updatePricesForEachProduct = function() {
            // update price and input value
            for (var rowId in this.items) {
                if (this.items.hasOwnProperty(rowId)) {
                    // set item's input
                    setItemRowPriceInput.call(this, rowId);

                    // update row total price
                    updateOverallPrice.call(this, rowId);
                }
            }
        },

        /**
         * Sets the price input of a row.
         *
         * @param {String} rowId
         */
        setItemRowPriceInput = function(rowId) {
            // update input in dom
            var item = this.items[rowId];
            var $el = this.sandbox.dom.find(constants.priceInput, getItemRowById.call(this, rowId));
            this.sandbox.dom.val($el, item.priceFormatted);
        },

        /**
         * Stops the loader component and shows the list again.
         */
        stopLoader = function() {
            this.sandbox.stop(this.$loader);
            this.sandbox.dom.show(this.$list);
        },

        /**
         * Shows and starts a loader.
         *
         * @param {Object} dfdLoaderStarted
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
         * Creates dom element for loader and appends it to.
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
         * Sets default data.
         *
         * @param {String} key
         * @param {mixed} value
         */
        setDefaultData = function(key, value) {
            this.options.defaultData[key] = value;
        },

        /**
         * Triggers callback function if set for column.
         *
         * @param {Object} event
         */
        rowClicked = function(event) {
            // if input or link was clicked, do nothing
            if (event.target.tagName.toUpperCase() === 'INPUT' ||
                event.target.tagName.toUpperCase() === 'A' ||
                !this.options.isEditable
            ) {
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
         * Triggers callback function if set for column.
         *
         * @param {Object} event
         */
        rowCellClicked = function(event) {
            var name = this.sandbox.dom.data(event.currentTarget, 'name'),
                rowId = this.sandbox.dom.data(this.sandbox.dom.parent(), 'id');
            if (name && this.options.columnCallbacks.hasOwnProperty(name)) {
                this.options.columnCallbacks[name].call(this, event.currentTarget, rowId);
            }
        },

        /**
         * Triggered when quantity is changed.
         *
         * @param {Object} event
         */
        quantityChangedHandler = function(event) {
            var rowId = getRowData.call(this, event).id;
            // update quantity
            this.items[rowId].quantity = this.sandbox.parseFloat(this.sandbox.dom.val(event.target));

            updateItemRowPrices.call(this, rowId);

            this.sandbox.emit(EVENT_CHANGED.call(this));
        },

        /**
         * Triggered when price is changed.
         *
         * @param {Object} event
         */
        priceChangedHandler = function(event) {
            var rowId = getRowData.call(this, event).id;
            // update price
            this.items[rowId].price = this.sandbox.parseFloat(this.sandbox.dom.val(event.target));

            updateItemRowPrices.call(this, rowId);

            this.sandbox.emit(EVENT_CHANGED.call(this));
        },

        /**
         * Triggered when discount is changed.
         *
         * @param {Object} event
         */
        discountChangedHandler = function(event) {
            var rowId = getRowData.call(this, event).id;
            // update discount
            this.items[rowId].discount = this.sandbox.parseFloat(this.sandbox.dom.val(event.target));

            updateItemRowPrices.call(this, rowId);

            this.sandbox.emit(EVENT_CHANGED.call(this));
        },

        /**
         * Calls prices api for a specific row to calculate new prices.
         *
         * @param {String} rowId
         *
         * @returns {Object} Deferred
         */
        updateItemRowPrices = function(rowId) {
            var isLoadedPromise = new this.sandbox.data.deferred();
            // update API
            fetchPricesForRow.call(this, rowId).then(function() {
                // update rows overall price
                updateOverallPrice.call(this, rowId);
                // update global price
                updateGlobalPrice.call(this);
                // update items data in dom
                refreshItemsData.call(this);

                isLoadedPromise.resolve();
            }.bind(this));

            return isLoadedPromise;
        },

        /**
         * Returns an object containing a row's ID and jquery element.
         *
         * @param {Object} event
         *
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
         * Updates overall price.
         *
         * @param {String} rowId
         */
        updateOverallPrice = function(rowId) {
            var $row = this.$find('#' + rowId),
                item = this.items[rowId],
                $priceCol = this.sandbox.dom.find('.item-overall-price span', $row);

            this.sandbox.dom.html($priceCol, getOverallPriceString.call(this, item));

        },

        /**
         * Updates row with global prices.
         */
        updateGlobalPrice = function() {
            var items = this.getItems(), result, $table, i;

            if (!!items && items.length > 0 && !!items[0].price) {

                var totalNetPrice = 0;
                var totalPrice = 0;
                for (var i = -1, len = items.length; ++i < len;) {
                    totalNetPrice += items[i].totalNetPrice;
                    totalPrice += items[i].tax / 100.0 * items[i].totalNetPrice + items[i].totalNetPrice;;
                }

                // visualize
                $table = this.$find(constants.globalPriceTableClass);
                this.sandbox.dom.empty($table);

                if (!!totalNetPrice) {
                    // add net price
                    addPriceRow.call(
                        this,
                        $table,
                        this.sandbox.translate('salescore.item.net-price'),
                        PriceCalcUtil.getFormattedAmountAndUnit(this.sandbox, totalNetPrice, this.currency)
                    );

                    result = PriceCalcUtil.getTotalPricesAndTaxes(this.sandbox, this.items);
                    if (result.taxes) {
                        // add row for every tax group
                        for (i in result.taxes) {
                           addPriceRow.call(
                               this,
                               $table,
                               this.sandbox.translate('salescore.item.vat') + '.(' + i + '%)',
                               PriceCalcUtil.getFormattedAmountAndUnit(this.sandbox, result.taxes[i], this.currency)
                           );
                        }
                    }

                    addPriceRow.call(
                        this,
                        $table,
                        this.sandbox.translate('salescore.item.overall-price'),
                        PriceCalcUtil.getFormattedAmountAndUnit(this.sandbox, totalPrice, this.currency)
                    );
                }

            }
        },

        /**
         * Adds a new price row.
         *
         * @param {Object} $table
         * @param {String} title
         * @param {mixed} value
         */
        addPriceRow = function($table, title, value) {
            var $row = this.sandbox.dom.createElement(templates.priceRow.call(this, title, value));
            this.sandbox.dom.append($table, $row);
        },

        /**
         * Returns formated overallPrice + currency as string (based on item).
         *
         * @param {Object} item
         *
         * @returns {String}
         */
        getOverallPriceString = function(item) {
            setItemDefaults(item);

            return item.totalNetPriceFormatted + ' ' + getCurrency.call(this, item);
        },

        /**
         * Sets defaults for items for proper calculation.
         *
         * @param {Object} item
         */
        setItemDefaults = function(item) {
            item.price = item.price || 0;
            item.priceFormatted = item.priceFormatted || 0;
            item.totalNetPriceFormatted = item.totalNetPriceFormatted || 0;
            item.totalNetPrice = item.totalNetPrice || 0;
            item.discount = item.discount || 0;
            item.quantity = item.quantity || 0;
            item.tax = item.tax || 0;
        },

        /**
         * Returns items currency; if not set, default-currency.
         *
         * @param {Object} item
         *
         * @returns {String}
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
                    $row = updateItemRow.call(this, rowId, itemData);
                    // fetch prices
                    updateItemRowPrices.call(this, rowId).then(function() {
                        // update price input
                        setItemRowPriceInput.call(this, rowId);
                    }.bind(this));
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
         * Fetches price for a specific item.
         *
         * @param {Array} rowId
         */
        fetchPricesForRow = function(rowIds) {
            var isLoadedPromise = new this.sandbox.data.deferred(),
                items = [];

            // if single object convert to array
            if (!this.sandbox.dom.isArray(rowIds)) {
                rowIds = [rowIds];
            }
            // iterate through items
            this.sandbox.util.foreach(rowIds, function(rowId) {
                items.push(this.items[rowId]);
            }.bind(this));

            // load product price
            this.sandbox.util.save(urls.pricing, 'POST' , {
                currency: this.currency,
                taxfree: this.options.taxfree,
                items: items
            }).then(function(response) {
                // map each result of response back to rowIds
                for (var i = -1, len = response.items.length; ++i < len;) {
                    var rowId = rowIds[i];
                    this.sandbox.util.extend(this.items[rowId], response.items[i]);
                }

                isLoadedPromise.resolve();
            }.bind(this))
                .fail(function(request, message, error) {
                    this.sandbox.emit('sulu.labels.warning.show',
                        this.sandbox.translate('salescore.item-table.'),
                        'labels.error',
                        ''
                    );
                    this.sandbox.logger.error(request, message, error);

                    isLoadedPromise.reject();

                }.bind(this));

            return isLoadedPromise;
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
         *  DOM-EVENT listener: remove row.
         *
         *  @param {Object} event
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
         *  DOM-EVENT listener: add a new row.
         *
         *  @param {Object} event
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
                        displayToolbars: this.options.displayToolbars,
                        columns: this.options.columns,
                        rowId: rowId ? rowId : constants.rowIdPrefix + this.rowCount,
                        rowNumber: this.rowCount,
                        showItemCount: this.options.showItemCount
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
         * Adds an existing item to the list.
         *
         * @param {Object} itemData
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
         * Updates a specific row.
         *
         * @param {String} rowId
         * @param {Object} itemData
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
         * Add validation to row.
         *
         * @param {Object} $row
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
         * Remove validation from row.
         *
         * @param {Object} $row
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
         * Adds a new item.
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
         * Rerenders component.
         *
         * @param {Array} items
         */
        rerenderItems = function(items) {
            this.items = {};
            this.sandbox.dom.empty(this.$find(constants.listClass));
            renderItems.call(this, items);
        },

        /**
         * Renders Items.
         *
         * @param {Array} items
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
         * Sets an item, based on product.
         *
         * @param {Object} productData
         *
         * @returns {Object}
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
         * Inits the overlay with a specific template.
         *
         * @param {Object} data
         * @param {Object} settings
         * @param {String} rowId
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

        /**
         * Returns address by id.
         *
         * @param {Number} id
         *
         * @returns {Object}
         */
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
         * Renders table head.
         */
        renderHeader = function() {
            var rowData = this.sandbox.util.extend({}, rowDefaults, this.options, {header: getHeaderData.call(this)}),
                rowTpl = this.sandbox.util.template(RowHeadTpl, rowData);
            this.sandbox.dom.append(this.$find(constants.listClass), rowTpl);
        },

        /**
         * Sets components data-items to current items.
         */
        refreshItemsData = function() {
            this.sandbox.dom.data(this.$el, 'items', this.getItems());
        },

        /**
         * Initialize husky-validation.
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
                    displayToolbars: this.options.displayToolbars,
                    columns: this.options.columns,
                    showItemCount: this.options.showItemCount
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
         * Returns current items.
         *
         * @returns {Array}
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
