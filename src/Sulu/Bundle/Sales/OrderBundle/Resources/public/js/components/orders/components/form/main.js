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
    'sulucontact/models/contact',
    'config',
    'widget-groups'
], function(Sidebar, OrderStatus, HeaderUtil, CoreHelper, Account, Contact, Config, WidgetGroups) {

    'use strict';

    var form = '#order-form',

        defaults = {
            currencyCode : Config.get('sulu_sales_core').default_currency
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

        /**
         * The maximum number of contacts that is fetched for fill dropdown.
         */
        MAXIMUM_CONTACTS_TO_FETCH = 999,

        CUSTOMER_TYPE = {
            ORGANIZATION: 1,
            PRIVATE_PERSON: 2
        },

        constants = {
            accountContactsUrl: '/admin/api/accounts/<%= id %>/contacts?flat=true&limit=<%= limit %>',
            accountAddressesUrl: '/admin/api/accounts/<%= id %>/addresses',
            customerAccountId: 'js-customer-account',
            customerContactId: 'js-customer-contact',
            deliveryAddressInstanceName: 'delivery-address',
            billingAddressInstanceName: 'billing-address',
            currencySelectInstanceName: 'currency-select',
            customerTypeSelectInstanceName: 'customer-type-select',
            itemTableInstanceName: 'order-items',
            paymentTermsInstanceName: 'payment-terms',
            deliveryTermsInstanceName: 'delivery-terms',
            contactSelectInstanceName: 'contact-select',
            validateWarningTranslation: 'form.validation-warning',
            translationShippingFailed: 'salescore.shipping-failed',
            autocompleteLimit: 20
        },

        selectors = {
            customerAutocompleteContainer: '#js-customer-autocomplete-container',
            customerAccount: '#' + constants.customerAccountId,
            customerContact: '#' + constants.customerContactId,
            currencySelect: '#js-currency-select',
            deliveryAddress: '#js-' + constants.deliveryAddressInstanceName,
            billingAddress: '#js-' + constants.billingAddressInstanceName,
            itemTable: '#js-order-items',
            taxFree: '#js-tax-free'
        },

        /**
         * Get status of current order.
         */
        getOrderStatusId = function() {
            return (!!this.options.data && !!this.options.data.status) ?
                this.options.data.status.id : null;
        },

        /**
         * Sets specific data to options.
         *
         * @param {String} key Where to set data (this.options[key]).
         * @param {Mixed} optionData Data to set onto options.
         */
        setOptionsData = function(key, optionData) {
            this.options[key] = optionData;
        },

        bindCustomEvents = function() {
            this.sandbox.on(EVENT_SET_OPTIONS_DATA, setOptionsData.bind(this));

            // Auto complete customer account.
            this.sandbox.on('husky.auto-complete.' + this.customerAccountInstanceName + '.initialized', function() {
                if (!this.isEditable) {
                    this.sandbox.dom.attr(this.$find(selectors.customerAccount + ' input'), 'disabled', 'disabled');
                }
                this.dfdAutoCompleteInitialized.resolve();
            }, this);

            this.sandbox.on(
                'husky.auto-complete.' + this.customerAccountInstanceName + '.select',
                accountChangedListener.bind(this)
            );

            this.sandbox.on(
                'husky.auto-complete.' + this.customerAccountInstanceName + '.selection-removed',
                accountChangedListener.bind(this)
            );

            // Auto complete customer contact.
            this.sandbox.on('husky.auto-complete.' + this.customerContactInstanceName + '.initialized', function() {
                if (!this.isEditable) {
                    this.sandbox.dom.attr(this.$find(selectors.customerContact + ' input'), 'disabled', 'disabled');
                }
                this.dfdAutoCompleteInitialized.resolve();
            }, this);

            this.sandbox.on(
                'husky.auto-complete.' + this.customerContactInstanceName + '.select',
                contactChangedListener.bind(this)
            );

            this.sandbox.on(
                'husky.auto-complete.' + this.customerContactInstanceName + '.selection-removed',
                contactChangedListener.bind(this)
            );

            // Contact saved.
            this.sandbox.on('sulu.salesorder.order.saved', function(data) {
                this.options.data = data;
                setSaved.call(this, true);
                this.dfdFormSaved.resolve();
            }, this);

            // Contact save.
            this.sandbox.on('sulu.toolbar.save', function() {
                this.submit();
            }, this);

            // Back to list.
            this.sandbox.on('sulu.header.back', function() {
                this.sandbox.emit('sulu.salesorder.orders.list');
            }, this);

            this.sandbox.on('husky.input.shipping-date.initialized', function() {
                this.dfdShippingDate.resolve();
            }, this);

            this.sandbox.on('husky.input.order-date.initialized', function() {
                this.dfdOrderDate.resolve();
            }, this);

            this.sandbox.on(
                'sulu.editable-data-row.' + constants.deliveryAddressInstanceName + '.initialized',
                function() {
                    this.dfdDeliveryAddressInitialized.resolve();
                }.bind(this)
            );

            this.sandbox.on(
                'sulu.editable-data-row.' + constants.billingAddressInstanceName + '.initialized',
                function() {
                    this.dfdInvoiceAddressInitialized.resolve();
                }.bind(this)
            );

            this.sandbox.on(
                'sulu.editable-data-row.address-view.' + constants.deliveryAddressInstanceName + '.changed',
                function(data) {
                    this.options.data.deliveryAddress = data;
                    setFormData.call(this, this.options.data);
                    changeHandler.call(this);
                }.bind(this)
            );

            this.sandbox.on(
                'sulu.editable-data-row.address-view.' + constants.billingAddressInstanceName + '.changed',
                function(data) {
                    this.options.data.invoiceAddress = data;
                    setFormData.call(this, this.options.data);
                    changeHandler.call(this);
                }.bind(this)
            );

            this.sandbox.on('husky.select.' + constants.currencySelectInstanceName + '.selected.item', function(data) {
                this.sandbox.emit('sulu.item-table.' + constants.itemTableInstanceName + '.change-currency', data);
                this.currency = data;
            }, this);

            this.sandbox.on('sulu.salesorder.set-customer-id', function(customerId) {
                this.customerId = customerId;
            }, this);

            this.sandbox.on('husky.select.' + constants.contactSelectInstanceName + '.initialize', function() {
                this.dfdContactInitialized.resolve();
            }, this);

            this.sandbox.on('husky.select.' + constants.customerTypeSelectInstanceName + '.initialize', function() {
                this.dfdCustomerTypeInitialized.resolve();
            }, this);

            this.sandbox.on(
                'husky.select.' + constants.customerTypeSelectInstanceName + '.selected.item',
                function(customerTypeId) {
                    customerTypeChangedHandler.call(this, parseInt(customerTypeId));
                },
                this
            );
        },

        bindDomEvents = function() {
            this.sandbox.dom.on(
                this.$el,
                'click',
                onTaxfreeClicked.bind(this),
                selectors.taxFree
            );
        },

        /**
         * Event gets called when checkbox is triggered.
         *
         * @param {Object} event
         */
        onTaxfreeClicked = function(event) {
            var taxfree = $(event.currentTarget).is(':checked');
            this.sandbox.emit('sulu.item-table.' + constants.itemTableInstanceName + '.update-price', taxfree);
        },

        /**
         * Set saved method.
         *
         * @param {Boolean} saved Defines if saved state should be shown.
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

        /**
         * @param {Object} data
         */
        initForm = function(data) {
            var formObject = this.sandbox.form.create(form);
            formObject.initialized.then(function() {
                setFormData.call(this, data, true);
                startFormComponents.call(this, data);
            }.bind(this));
        },

        /**
         * @param {Object} data
         */
        setFormData = function(data) {
            // Add collection filters to form.
            this.sandbox.form.setData(form, data).then(function() {
                this.accountId = getAccountId.call(this);
                this.contactId = getContactId.call(this);
            }.bind(this)).fail(function(error) {
                this.sandbox.logger.error("An error occured when setting data!", error);
            }.bind(this));
        },

        /**
         * Start components form components.
         *
         * @param {Object} data
         */
        startFormComponents = function(data) {
            this.sandbox.start(form);

            if (!!data && !data.customerAccount && !!data.customerContact && !!data.customerContact.id) {
                startCustomerContactAutocomplete.call(this, data);

                return;
            }

            startCustomerAccountAutocomplete.call(this, data);
        },

        /**
         * @param {Object} data
         */
        startCustomerAccountAutocomplete = function(data) {
            // Stop other autocomplete element.
            this.sandbox.stop(selectors.customerContact);

            // Create dom element.
            var $element = this.sandbox.dom.createElement('<div id="' + constants.customerAccountId + '" />');
            this.sandbox.dom.append(this.$find(selectors.customerAutocompleteContainer), $element);

            var options = Config.get('sulucontact.components.autocomplete.default.account');
            options.el = selectors.customerAccount;
            options.value = !!data && !!data.customerAccount ? data.customerAccount : '';
            options.instanceName = this.customerAccountInstanceName;
            options.remoteUrl += '&type=' + this.customerId + '&limit=' + constants.autocompleteLimit;
            options.limit = constants.autocompleteLimit;

            // Starts form components.
            this.sandbox.start([
                {
                    name: 'auto-complete@husky',
                    options: options
                }
            ]);

            if (!!data && !!data.customerAccount && !!data.customerAccount.id) {
                initSelectsByAccountId.call(this, data.customerAccount.id, data);
            }
        },

        /**
         * @param {Object} data
         */
        startCustomerContactAutocomplete = function(data) {
            // Stop other autocomplete element.
            this.sandbox.stop(selectors.customerAccount);

            // Create dom element.
            var $element = this.sandbox.dom.createElement('<div id="' + constants.customerContactId + '" />');
            this.sandbox.dom.append(this.$find(selectors.customerAutocompleteContainer), $element);

            var customerContactOptions = Config.get('sulucontact.components.autocomplete.default.contact');
            customerContactOptions.el = selectors.customerContact;
            customerContactOptions.value = !!data && !!data.customerContact ? data.customerContact : '';
            customerContactOptions.instanceName = this.customerContactInstanceName;
            customerContactOptions.limit = constants.autocompleteLimit;

            // Starts form components.
            this.sandbox.start([
                {
                    name: 'auto-complete@husky',
                    options: customerContactOptions
                }
            ]);

            if (!!data && !!data.customerContact && !!data.customerContact.id) {
                initSelectsByContact.call(this, data.customerContact, data);
            }
        },

        /**
         * Returns id of currently set account.
         *
         * @returns {String}
         */
        getAccountId = function() {
            return this.sandbox.dom.attr(selectors.customerAccount, 'data-id');
        },

        /**
         * @returns {String}
         */
        getContactId = function() {
            return this.sandbox.dom.attr(selectors.customerContact, 'data-id');
        },

        /**
         * Init customer type select.
         *
         * @param {Number} customerTypeId
         */
        initCustomerTypeSelect = function(customerTypeId) {
            this.customerTypeId = customerTypeId;

            this.sandbox.data.when(this.dfdCustomerTypeInitialized).then(function() {
                this.sandbox.emit(
                    'husky.select.customer-type-select.update',
                    this.options.customerTypes,
                    [customerTypeId]
                );
            }.bind(this));
        },

        /**
         * Init contact select.
         *
         * @param {Array} data
         * @param {Array} preselectedElements
         */
        initContactSelect = function(data, preselectedElements) {
            preselectedElements = preselectedElements || [];

            this.sandbox.data.when(this.dfdContactInitialized).then(function() {
                this.sandbox.emit('husky.select.contact-select.update', data, preselectedElements);
            }.bind(this));
        },

        /**
         * Init address select.
         *
         * @param {Array} data
         * @param {String} instanceName
         * @param {Array} preselectedElement
         */
        initAddressComponent = function(data, instanceName, preselectedElement) {
            this.sandbox.emit('sulu.editable-data-row.' + instanceName + '.data.update', data, preselectedElement);
            this.$find(selectors.deliveryAddress).removeClass('disabled-button');
            this.$find(selectors.billingAddress).removeClass('disabled-button');
        },

        /**
         * Init delivery and invoice address component.
         *
         * @param {Object} orderData
         * @param {Array} addressData
         */
        initAddressComponents = function(orderData, addressData)
        {
            // When an address is already selected, the selected address should be used
            // otherwise the first delivery / payment address found will be used.
            var preselect = null;
            if (!orderData || !orderData.deliveryAddress) {
                preselect = findAddressWherePropertyIs.call(this, addressData, 'deliveryAddress', true);

                // When no delivery address is found the first address will be used.
                if (!preselect && addressData.length > 0) {
                    preselect = addressData[0];
                }
            } else {
                preselect = orderData.deliveryAddress;
            }
            this.sandbox.data.when(this.dfdDeliveryAddressInitialized).then(function() {
                initAddressComponent.call(this, addressData, constants.deliveryAddressInstanceName, preselect);
                setSettingsOverlayAdresses.call(this, addressData, preselect);
                this.options.data.deliveryAddress = preselect;
            }.bind(this));

            preselect = null;
            if (!orderData || !orderData.invoiceAddress) {
                preselect = findAddressWherePropertyIs.call(this, addressData, 'billingAddress', true);

                // When no invoice address is found the first address will be used.
                if (!preselect && addressData.length > 0) {
                    preselect = addressData[0];
                }
            } else {
                preselect = orderData.invoiceAddress;
            }

            this.sandbox.data.when(this.dfdInvoiceAddressInitialized).then(function() {
                initAddressComponent.call(this, addressData, constants.billingAddressInstanceName, preselect);
                this.options.data.invoiceAddress = preselect;
            }.bind(this));
        },

        /**
         * Set value of editable data-row.
         *
         * @param {String} instanceName
         * @param {Array} value
         */
        setValueOfEditableDataRow = function(instanceName, value) {
            this.sandbox.emit('sulu.editable-data-row.' + instanceName + '.set-value', value);
        },

        /**
         * Set addresses for settings overlay.
         *
         * @param {Array} addresses
         * @param {Array} preselect
         */
        setSettingsOverlayAdresses = function(addresses, preselect) {
            this.sandbox.emit(
                'sulu.item-table.' + constants.itemTableInstanceName + '.set-addresses',
                addresses,
                preselect
            );
        },

        /**
         * Called when account auto-complete is changed.
         *
         * @param {Object} event
         */
        accountChangedListener = function(event) {
            var id = (event && event.id) ? event.id : null;

            // If account has been changed.
            if (id !== this.accountId) {
                this.accountId = id;

                if (id) {
                    // Load contacts of account.
                    initSelectsByAccountId.call(this, id);
                } else {
                    initContactSelect.call(this, []);
                    initAddressComponent.call(this, [], constants.deliveryAddressInstanceName);
                    initAddressComponent.call(this, [], constants.billingAddressInstanceName);

                    setSettingsOverlayAdresses.call(this, []);
                }
            }
        },

        /**
         * Called when contact auto-complete is changed.
         *
         * @param {Object} contact
         */
        contactChangedListener = function(contact) {
            var id = (contact && contact.id) ? contact.id : null;

            // If contact has been changed.
            if (id !== this.contactId) {
                this.contactId = id;

                if (id) {
                    initSelectsByContact.call(this, contact);
                } else {
                    initContactSelect.call(this, []);
                    initAddressComponent.call(this, [], constants.deliveryAddressInstanceName);
                    initAddressComponent.call(this, [], constants.billingAddressInstanceName);

                    setSettingsOverlayAdresses.call(this, []);
                }
            }
        },

        /**
         * @param {Number} customerTypeId
         */
        customerTypeChangedHandler = function(customerTypeId) {
            // If type has changed, load the correct autocomplete element.
            if (this.customerTypeId != customerTypeId) {
                this.customerTypeId = customerTypeId;
                switch (this.customerTypeId) {
                    case CUSTOMER_TYPE.ORGANIZATION:
                        startCustomerAccountAutocomplete.call(this);
                        accountChangedListener.call(this, null);
                        break;
                    case CUSTOMER_TYPE.PRIVATE_PERSON:
                        startCustomerContactAutocomplete.call(this);
                        contactChangedListener.call(this, null);
                        break;
                }
            }
        },

        /**
         * Called when headerbar should be saveable.
         */
        changeHandler = function() {
            setSaved.call(this, false);
        },

        /**
         * @param {Object} contact
         * @param {Object} orderData
         */
        initSelectsByContact = function(contact, orderData) {
            var data = null;
            var preselect = null;

            // Load contact from repository.
            var contactRepository = Contact.findOrCreate({id: contact.id});
            contactRepository.fetch({
                success: function(model) {
                    var contactModel = model.toJSON();

                    if (contactModel.hasOwnProperty('addresses')) {
                        initAddressComponents.call(this, orderData, contactModel.addresses);
                    }
                }.bind(this),
                error: function() {
                    this.sandbox.emit('sulu.labels.warning.show', this.sandbox.translate('error while fetching account'));
                }.bind(this)
            });

            // Load contact select.
            if (!!contact && contact.id) {
                data = [contact];
                preselect = [contact.id];

                initContactSelect.call(this, data, preselect);
            }

            initCustomerTypeSelect.call(this, CUSTOMER_TYPE.PRIVATE_PERSON);
        },

        /**
         * Sets dependent selects based on currently selected account.
         *
         * @param {String} id
         * @param {Object} orderData
         */
        initSelectsByAccountId = function(id, orderData) {
            var data, preselect, account;

            // Load account.
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
                        initAddressComponents.call(this, orderData, account.addresses);
                    }

                }.bind(this),
                error: function() {
                    this.sandbox.emit('sulu.labels.warning.show', this.sandbox.translate('error while fetching account'));
                }.bind(this)
            });

            // Load contacts of an account.
            this.sandbox.util.load(this.sandbox.util.template(
                constants.accountContactsUrl,
                {
                    id: id,
                    limit: MAXIMUM_CONTACTS_TO_FETCH
                }
                ))
                .then(function(response) {
                    data = response._embedded.contacts;
                    preselect = !!orderData && orderData.customerContact ? [orderData.customerContact.id] : null;
                    initContactSelect.call(this, data, preselect);
                }.bind(this))
                .fail(function(textStatus, error) {
                    this.sandbox.logger.error(textStatus, error);
                }.bind(this));

            initCustomerTypeSelect.call(this, CUSTOMER_TYPE.ORGANIZATION);
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
         * Find an address where a specific property with a specific value is set.
         *
         * @param {Object} addresses
         * @param {String} propertyName
         * @param {String} propertyValue
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
            this.customerTypeId = null;

            this.dfdFormSaved = this.sandbox.data.deferred();

            this.dfdAllFieldsInitialized = this.sandbox.data.deferred();
            this.dfdAutoCompleteInitialized = this.sandbox.data.deferred();
            this.dfdShippingDate = this.sandbox.data.deferred();
            this.dfdOrderDate = this.sandbox.data.deferred();

            this.dfdContactInitialized = this.sandbox.data.deferred();
            this.dfdCustomerTypeInitialized = this.sandbox.data.deferred();

            this.dfdInvoiceAddressInitialized = this.sandbox.data.deferred();
            this.dfdDeliveryAddressInitialized = this.sandbox.data.deferred();

            // Define when all fields are initialized.
            this.sandbox.data.when(this.dfdShippingDate, this.dfdOrderDate, this.dfdAutoCompleteInitialized).then(function() {
                this.dfdAllFieldsInitialized.resolve();
            }.bind(this));

            this.orderStatusId = getOrderStatusId.call(this);

            // Set if form is editable.
            this.isEditable = this.orderStatusId <= OrderStatus.IN_CART;

            this.options.data = this.sandbox.util.extend({}, defaults, this.options.data);

            // Current id.
            var id = this.options.data.id ? this.options.data.id : 'new';

            this.customerAccountInstanceName = 'customerAccount' + id;
            this.customerContactInstanceName = 'customerContact' + id;

            // Bind events.
            bindCustomEvents.call(this);
            bindDomEvents.call(this);
            HeaderUtil.initialize.call(this);

            // Set header.
            setSaved.call(this, true);

            // Render form.
            this.render();

            HeaderUtil.setToolbar.call(this, this.options.data);

            // Listen for changes in form.
            this.listenForChange();

            // Initialize sidebar.
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

            // Initialize form.
            initForm.call(this, data);
            this.startItemTableAndCurrencySelect();
        },

        /**
         * Initializes the item-table and the select component.
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
                        el: selectors.itemTable,
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
                        netShippingCosts: this.options.data.netShippingCosts,
                        enableNetShippingCosts: true,
                        netShippingCostsChangedCallback: function(cost) {
                            this.netShippingCosts = cost;
                        }.bind(this)
                    }
                },
                {
                    name: 'select@husky',
                    options: {
                        el: selectors.currencySelect,
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

                // Because the preselected option of the select conflicts with the data mapper
                // the data mapper property is not used and therefore the data.currency property
                // has to be set this way.
                data.currencyCode = !!this.currency ? this.currency : this.options.data.currencyCode;

                data.netShippingCosts = this.netShippingCosts;

                // Only get id, if auto-complete is not empty:
                data.customerAccount = {
                    id: getAccountId.call(this)
                };

                this.sandbox.logger.log('log data', data);
                this.sandbox.emit('sulu.salesorder.order.save', data);
            } else {
                this.sandbox.emit(
                    'sulu.labels.warning.show',
                    this.sandbox.translate(constants.validateWarningTranslation)
                );
                this.dfdFormSaved.reject();
            }
            return this.dfdFormSaved;
        },

        /**
         * Event listens for changes in form.
         */
        listenForChange: function() {
            // Listen for change after TAGS and BIRTHDAY-field have been set.
            this.sandbox.data.when(this.dfdAllFieldsInitialized).then(function() {

                // When input changes.
                this.sandbox.dom.on(form, 'change', changeHandler.bind(this),
                    '.changeListener select, ' +
                    '.changeListener input, ' +
                    '.changeListener .pickdate, ' +
                    '.changeListener .husky-select, ' +
                    '.changeListener textarea');

                // On keyup.
                this.sandbox.dom.on(form, 'keyup', changeHandler.bind(this),
                    '.changeListener select, ' +
                    '.changeListener input, ' +
                    '.changeListener textarea');

                // Change in item-table.
                this.sandbox.on('sulu.item-table.changed', changeHandler.bind(this));
            }.bind(this));

        }
    };
});
