/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

define([
    'sulusalesorder/util/sidebar',
    'sulusalesorder/util/orderStatus',
    'sulusalesorder/util/header',
    'sulusalescore/util/helper',
    'sulucontact/models/account',
    'config',
    'widget-groups'
], function(Sidebar, OrderStatus, HeaderUtil, CoreHelper, Account, Config, WidgetGroups) {

    'use strict';

    var form = '#order-form',

        // TODO: default-currency needs to be passed from backend
        // https://github.com/sulu-io/SuluSalesOrderBundle/issues/72
        defaults = {
            currencyCode : 'EUR'
        },

        namespace = 'sulu.salesorder.',

        /**
         * Used for setting any parameter to options configuration.
         *
         * @event sulu.salesorder.set-options-data
         *
         * @param {String} key
         * @param {Mixed} value
         */
        EVENT_SET_OPTIONS_DATA = namespace + 'set-options-data',

        constants = {
            accountContactsUrl: '/admin/api/accounts/<%= id %>/contacts?flat=true',
            accountAddressesUrl: '/admin/api/accounts/<%= id %>/addresses',
            accountInputId: '#account-input',
            deliveryAddressInstanceName: 'delivery-address',
            billingAddressInstanceName: 'billing-address',
            currencySelectInstanceName: 'currency-select',
            currencySelectSelector: '#currency-select',
            itemTableInstanceName: 'order-items',
            itemTableSelector: '#order-items',
            paymentTermsInstanceName: 'payment-terms',
            deliveryTermsInstanceName: 'delivery-terms',
            contactSelectId: '#contact-select',
            validateWarningTranslation: 'form.validation-warning',
            translationShippingFailed: 'salescore.shipping-failed',
            autocompleteLimit: 20
        },

        /**
         * get status of current order
         */
        getOrderStatusId = function() {
            return (!!this.options.data && !!this.options.data.status) ?
                this.options.data.status.id : null;
        },

        /**
         * Sets specific data to options.
         *
         * @param {String} key Where to set data (this.options[key])
         * @param {Mixed} optionData Data to set onto options
         */
        setOptionsData = function(key, optionData) {
            this.options[key] = optionData;
        },

        bindCustomEvents = function() {
            this.sandbox.on(EVENT_SET_OPTIONS_DATA, setOptionsData.bind(this));

            this.sandbox.on('husky.auto-complete.' + this.accountInstanceName + '.initialized', function() {
                if (!this.isEditable) {
                    this.sandbox.dom.attr(this.$find(constants.accountInputId + ' input'), 'disabled', 'disabled');
                }
                this.dfdAutoCompleteInitialized.resolve();
            }, this);

            this.sandbox.on('husky.auto-complete.' + this.accountInstanceName + '.selection-removed', accountChangedListener.bind(this));

            // contact saved
            this.sandbox.on('sulu.salesorder.order.saved', function(data) {
                this.options.data = data;
                setSaved.call(this, true);
                this.dfdFormSaved.resolve();
            }, this);

            // contact save
            this.sandbox.on('sulu.toolbar.save', function() {
                this.submit();
            }, this);

            // back to list
            this.sandbox.on('sulu.header.back', function() {
                this.sandbox.emit('sulu.salesorder.orders.list');
            }, this);

            this.sandbox.on('husky.input.shipping-date.initialized', function() {
                this.dfdShippingDate.resolve();
            }, this);

            this.sandbox.on('husky.input.order-date.initialized', function() {
                this.dfdOrderDate.resolve();
            }, this);

            this.sandbox.on('husky.auto-complete.' + this.accountInstanceName + '.select', accountChangedListener.bind(this));

            this.sandbox.on('sulu.editable-data-row.' + constants.deliveryAddressInstanceName + '.initialized', function() {
                this.dfdDeliveryAddressInitialized.resolve();
            }.bind(this));

            this.sandbox.on('sulu.editable-data-row.' + constants.billingAddressInstanceName + '.initialized', function() {
                this.dfdInvoiceAddressInitialized.resolve();
            }.bind(this));

            this.sandbox.on('sulu.editable-data-row.address-view.' + constants.deliveryAddressInstanceName + '.changed', function(data) {
                this.options.data.deliveryAddress = data;
                setFormData.call(this, this.options.data);
                changeHandler.call(this);
            }.bind(this));

            this.sandbox.on('sulu.editable-data-row.address-view.' + constants.billingAddressInstanceName + '.changed', function(data) {
                this.options.data.invoiceAddress = data;
                setFormData.call(this, this.options.data);
                changeHandler.call(this);
            }.bind(this));

            this.sandbox.on('husky.select.' + constants.currencySelectInstanceName + '.selected.item', function(data) {
                this.sandbox.emit('sulu.item-table.' + constants.itemTableInstanceName + '.change-currency', data);
                this.currency = data;
            }, this);

            this.sandbox.on('sulu.salesorder.set-customer-id', function(customerId) {
                this.customerId = customerId;
            }, this);
        },

        bindDomEvents = function() {
            this.sandbox.dom.on(this.$el, 'click', onTaxfreeClicked.bind(this), '#tax-free');
        },

        /**
         * @param {event} gets called when checkbox is triggered
         */
        onTaxfreeClicked = function(event) {
            var taxfree = $(event.currentTarget).is(':checked');
            this.sandbox.emit('sulu.item-table.' + constants.itemTableInstanceName + '.update-price', taxfree);
        },

        /**
         * set saved
         * @param {Bool} saved Defines if saved state should be shown
         */
        setSaved = function(saved) {
            if (saved !== this.saved) {
                if (!!saved) {
                    HeaderUtil.disableSave.call(this);
                } else {
                    HeaderUtil.enableSave.call(this);
                }
            }
            this.saved = saved;
        },

        initForm = function(data) {
            var formObject = this.sandbox.form.create(form);
            formObject.initialized.then(function() {
                setFormData.call(this, data, true);
                // TODO: resolve that form is set
                startFormComponents.call(this, data);
            }.bind(this));
        },

        setFormData = function(data) {
            // add collection filters to form
            this.sandbox.form.setData(form, data).then(function() {
                this.accountId = getAccountId.call(this);
            }.bind(this)).fail(function(error) {
                this.sandbox.logger.error("An error occured when setting data!", error);
            }.bind(this));
        },

        /**
         * start components form components
         * @param data
         */
        startFormComponents = function(data) {
            this.sandbox.start(form);

            if (!!data.customerAccount && !!data.customerAccount.id) {
                initSelectsByAccountId.call(this, data.customerAccount.id, data);
            }

            var options = Config.get('sulucontact.components.autocomplete.default.account');
            options.el = constants.accountInputId;
            options.value = !!data.customerAccount ? data.customerAccount : '';
            options.instanceName = this.accountInstanceName;
            options.remoteUrl += '&type=' + this.customerId + '&limit=' + constants.autocompleteLimit;
            options.limit = constants.autocompleteLimit;

            // starts form components
            this.sandbox.start([
                {
                    name: 'auto-complete@husky',
                    options: options
                }
            ]);
        },

        /**
         * returns id of currently set account
         * @returns string
         */
        getAccountId = function() {
            return this.sandbox.dom.attr(constants.accountInputId, 'data-id');
        },

        /**
         * init contact select
         * @param data
         * @param preselectedElements
         */
        initContactSelect = function(data, preselectedElements) {

            preselectedElements = preselectedElements || [];

            this.sandbox.emit('husky.select.contact-select.update', data, preselectedElements);
        },

        /**
         * init address select
         * @param data
         * @param instanceName
         * @param preselectedElement
         */
        initAddressComponents = function(data, instanceName, preselectedElement) {
            this.sandbox.emit('sulu.editable-data-row.' + instanceName + '.data.update', data, preselectedElement);
            this.$find('#' + constants.deliveryAddressInstanceName).removeClass('disabled-button');
            this.$find('#' + constants.billingAddressInstanceName).removeClass('disabled-button');
        },

        /**
         * set value of editable data-row
         * @param instanceName
         * @param value
         */
        setValueOfEditableDataRow = function(instanceName, value) {
            this.sandbox.emit('sulu.editable-data-row.' + instanceName + '.set-value', value);
        },

        /**
         * set addresses for settings overlay
         * @param addresses
         * @param preselect
         */
        setSettingsOverlayAdresses = function(addresses, preselect) {
            this.sandbox.emit('sulu.item-table.' + constants.itemTableInstanceName + '.set-addresses', addresses, preselect);
        },

        /**
         * called when account auto-complete is changed
         * @param event
         */
        accountChangedListener = function(event) {
            var id = event.id || null;

            // if account has been changed
            if (id !== this.accountId) {
                this.accountId = id;

                if (id) {
                    // load contacts of account
                    initSelectsByAccountId.call(this, id);
                } else {
                    initContactSelect.call(this, []);
                    initAddressComponents.call(this, [], constants.deliveryAddressInstanceName);
                    initAddressComponents.call(this, [], constants.billingAddressInstanceName);

                    setSettingsOverlayAdresses.call(this, []);
                }
            }
        },

        /**
         * called when headerbar should be saveable
         */
        changeHandler = function() {
            setSaved.call(this, false);
        },

        /**
         * sets dependent selects based on currently selected account
         * @param id
         * @param orderData
         */
        initSelectsByAccountId = function(id, orderData) {
            var data, preselect, account, addressData;

            // load account
            account = Account.findOrCreate({id: id});
            account.fetch({
                success: function(model) {
                    account = model.toJSON();

                    var paymentTerms = null,
                        deliveryTerms = null;

                    if (!orderData) {
                        if (account.hasOwnProperty('termsOfDelivery') && !!account.termsOfDelivery) {
                            deliveryTerms = account.termsOfDelivery.terms;
                        }
                        setValueOfEditableDataRow.call(this, constants.deliveryTermsInstanceName, deliveryTerms);

                        if (account.hasOwnProperty('termsOfPayment') && !!account.termsOfPayment) {
                            paymentTerms = account.termsOfPayment.terms;
                        }
                        setValueOfEditableDataRow.call(this, constants.paymentTermsInstanceName, paymentTerms);
                    }

                    if (account.hasOwnProperty('addresses')) {
                        addressData = account.addresses;

                        // when an address is already selected, the selected address should be used
                        // otherwise the first delivery / payment address found will be used
                        preselect = null;
                        if (!orderData || !orderData.deliveryAddress) {
                            preselect = findAddressWherePropertyIs.call(this, addressData, 'deliveryAddress', true);

                            // when no delivery address is found the first address will be used
                            if (!preselect && addressData.length > 0) {
                                preselect = addressData[0];
                            }
                        } else {
                            preselect = orderData.deliveryAddress;
                        }
                        this.sandbox.data.when(this.dfdDeliveryAddressInitialized).then(function() {
                            initAddressComponents.call(this, addressData, constants.deliveryAddressInstanceName, preselect);
                            setSettingsOverlayAdresses.call(this, addressData, preselect);
                            this.options.data.deliveryAddress = preselect;
                        }.bind(this));

                        preselect = null;
                        if (!orderData || !orderData.invoiceAddress) {
                            preselect = findAddressWherePropertyIs.call(this, addressData, 'billingAddress', true);

                            // when no invoice address is found the first address will be used
                            if (!preselect && addressData.length > 0) {
                                preselect = addressData[0];
                            }
                        } else {
                            preselect = orderData.invoiceAddress;
                        }

                        this.sandbox.data.when(this.dfdInvoiceAddressInitialized).then(function() {
                            initAddressComponents.call(this, addressData, constants.billingAddressInstanceName, preselect);
                            this.options.data.invoiceAddress = preselect;
                        }.bind(this));
                    }

                }.bind(this),
                error: function() {
                    // TODO: MESSAGE
                    this.sandbox.emit('sulu.labels.warning.show', this.sandbox.translate('error while fetching account'));
                }.bind(this)
            });

            // load contacts of an account
            this.sandbox.util.load(this.sandbox.util.template(constants.accountContactsUrl, {id: id}))
                .then(function(response) {
                    data = response._embedded.contacts;
                    preselect = !!orderData && orderData.customerContact ? [orderData.customerContact.id] : null;
                    initContactSelect.call(this, data, preselect);
                }.bind(this))
                .fail(function(textStatus, error) {
                    this.sandbox.logger.error(textStatus, error);
                }.bind(this));
        },

        /**
         * Returns currency id for currency code
         */
        getCurrencyIdForCode = function(code, currencies) {
            var currency = [];
            this.sandbox.util.each(currencies, function(key) {
                if (currencies[key].code === code) {
                    currency.push(currencies[key].id);
                    return false;
                }
            }.bind(this));

            return currency;
        },

        /**
         * Find an address where a specific property with a specific value is set
         * @param addresses
         * @param propertyName
         * @param propertyValue
         */
        findAddressWherePropertyIs = function(addresses, propertyName, propertyValue) {
            var address = null;
            if (!!addresses && addresses.length > 0) {
                this.sandbox.util.each(addresses, function(index, adr) {
                    if (adr.hasOwnProperty(propertyName) && adr[propertyName] === propertyValue) {
                        address = adr;
                        return false;
                    }
                }.bind(this));
            }
            return address;
        };

    return {

        view: true,

        layout: {
            content: {
                width: 'fixed'
            },
            sidebar: {
                width: 'max',
                cssClasses: 'sidebar-padding-50'
            }
        },

        templates: ['/admin/order/template/order/form'],

        initialize: function() {
            this.saved = true;
            this.formId = form;
            this.accountId = null;
            this.contactId = null;

            this.dfdFormSaved = this.sandbox.data.deferred();

            this.dfdAllFieldsInitialized = this.sandbox.data.deferred();
            this.dfdAutoCompleteInitialized = this.sandbox.data.deferred();
            this.dfdShippingDate = this.sandbox.data.deferred();
            this.dfdOrderDate = this.sandbox.data.deferred();

            this.dfdInvoiceAddressInitialized = this.sandbox.data.deferred();
            this.dfdDeliveryAddressInitialized = this.sandbox.data.deferred();

            // define when all fields are initialized
            this.sandbox.data.when(this.dfdShippingDate, this.dfdOrderDate, this.dfdAutoCompleteInitialized).then(function() {
                this.dfdAllFieldsInitialized.resolve();
            }.bind(this));

            this.orderStatusId = getOrderStatusId.call(this);

            // set if form is editable
            this.isEditable = this.orderStatusId <= OrderStatus.IN_CART;

            this.options.data = this.sandbox.util.extend({}, defaults, this.options.data);

            // current id
            var id = this.options.data.id ? this.options.data.id : 'new';

            this.accountInstanceName = 'customerAccount' + id;

            // bind events
            bindCustomEvents.call(this);
            bindDomEvents.call(this);
            HeaderUtil.initialize.call(this);

            // set header
            setSaved.call(this, true);

            // render form
            this.render();

            HeaderUtil.setToolbar.call(this, this.options.data);

            // listen for changes in form
            this.listenForChange();

            // initialize sidebar
            if (!!this.options.data && !!this.options.data.id && WidgetGroups.exists('order-detail')) {
                Sidebar.initForDetail(this.sandbox, this.options.data);
            }

        },

        render: function() {
            this.sandbox.dom.html(this.$el, this.renderTemplate(this.templates[0], {
                isEditable: this.isEditable,
                parseDate: CoreHelper.parseDate
            }));

            var data = this.options.data;

            // initialize form
            initForm.call(this, data);
            this.startItemTableAndCurrencySelect();
        },

        /**
         * Initializes the item-table and the select component
         */
        startItemTableAndCurrencySelect: function() {

            this.sandbox.start([
                {
                    name: 'item-table@sulusalescore',
                    options: {
                        instanceName: constants.itemTableInstanceName,
                        isEditable: this.isEditable,
                        remoteUrl: constants.accountUrl,
                        data: this.options.data.items,
                        currency: this.options.data.currencyCode,
                        el: constants.itemTableSelector,
                        enableIndependentItems: true,
                        settings: {
                            columns: [
                                'addresses',
                                'description',
                                'quantity',
                                'single-price',
                                'delivery-date',
                                'cost-center',
                                'discount',
                                'tax-rate'
                            ],
                            taxClasses: this.options.taxClasses,
                            units: this.options.units
                        },
                        taxfree: this.options.data.taxfree,
                        deliveryCost: this.options.data.deliveryCost,
                        enableDeliveryCost: true,
                        deliveryCostChangedCallback: function(cost) {
                            this.deliveryCost = cost;
                        }.bind(this)
                    }
                },
                {
                    name: 'select@husky',
                    options: {
                        el: constants.currencySelectSelector,
                        instanceName: constants.currencySelectInstanceName,
                        disabled: !this.isEditable,
                        emitValues: true,
                        defaultLabel: this.sandbox.translate('dropdown.please-choose'),
                        multipleSelect: false,
                        repeatSelect: false,
                        valueName: 'code',
                        data: this.options.currencies,
                        preSelectedElements: getCurrencyIdForCode.call(this, this.options.data.currencyCode, this.options.currencies)
                    }
                }
            ]);
        },

        submit: function() {
            this.dfdFormSaved = this.sandbox.data.deferred();
            this.sandbox.logger.log('save Model');

            if (this.sandbox.form.validate(form)) {
                var data = this.sandbox.form.getData(form);

                if (data.id === '') {
                    delete data.id;
                }

                // because the preselected option of the select conflicts with the data mapper
                // the data mapper property is not used and therefore the data.currency property
                // has to be set this way
                data.currencyCode = !!this.currency ? this.currency : this.options.data.currencyCode;

                data.deliveryCost = this.deliveryCost;

                // FIXME auto complete in mapper
                // only get id, if auto-complete is not empty:
                data.customerAccount = {
                    id: this.sandbox.dom.attr('#' + this.accountInstanceName, 'data-id')
                };

                this.sandbox.logger.log('log data', data);
                this.sandbox.emit('sulu.salesorder.order.save', data);
            } else {
                this.sandbox.emit('sulu.labels.warning.show', this.sandbox.translate(constants.validateWarningTranslation));
                this.dfdFormSaved.reject();
            }
            return this.dfdFormSaved;
        },

        // event listens for changes in form
        listenForChange: function() {
            // listen for change after TAGS and BIRTHDAY-field have been set
            this.sandbox.data.when(this.dfdAllFieldsInitialized).then(function() {

                // when input changes
                this.sandbox.dom.on(form, 'change', changeHandler.bind(this),
                    '.changeListener select, ' +
                    '.changeListener input, ' +
                    '.changeListener .pickdate, ' +
                    '.changeListener .husky-select, ' +
                    '.changeListener textarea');

                // on keyup
                this.sandbox.dom.on(form, 'keyup', changeHandler.bind(this),
                    '.changeListener select, ' +
                    '.changeListener input, ' +
                    '.changeListener textarea');

                // change in item-table
                this.sandbox.on('sulu.item-table.changed', changeHandler.bind(this));

                // TODO: use this for resetting account
//                    this.sandbox.dom.on(constants.accountInputId+' input', 'changed', accountChangedListener.bind(this));
            }.bind(this));

        }
    };
});
