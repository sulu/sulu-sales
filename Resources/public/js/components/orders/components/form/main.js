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
    'sulusalescore/util/helper',
    'sulucontact/model/account'
], function(Sidebar, OrderStatus, CoreHelper, Account) {

    'use strict';

    var form = '#order-form',

        constants = {
            accountContactsUrl: '/admin/api/accounts/<%= id %>/contacts?flat=true',
            accountAddressesUrl: '/admin/api/accounts/<%= id %>/addresses',
            accountUrl: '/admin/api/accounts?searchFields=name&flat=true&fields=id,name',
            accountInputId: '#account-input',
            deliveryAddressInstanceName: 'delivery-address',
            billingAddressInstanceName: 'billing-address',
            currencySelectInstanceName: 'currency-select',
            itemTableInstanceName: 'order-items',
            paymentTermsInstanceName: 'payment-terms',
            deliveryTermsInstanceName: 'delivery-terms',
            contactSelectId: '#contact-select',
            validateWarningTranslation: 'form.validation-warning',
            translationConversionFailed: 'salescore.conversion-failed',
            translationShippingFailed: 'salescore.shipping-failed'
        },

        /**
         * set header toolbar based on current order status
         */
        setHeaderToolbar = function() {

            var i, len,
                workflow,
                currentSection = null,
                data = this.options.data,
                toolbarItems = [
                    {
                        id: 'save-button',
                        icon: 'floppy-o',
                        iconSize: 'large',
                        class: 'highlight',
                        position: 1,
                        group: 'left',
                        disabled: true,
                        callback: function() {
                            this.sandbox.emit('sulu.header.toolbar.save');
                        }.bind(this)
                    }
                ],
                workflowDropdown = {
                    icon: 'hand-o-right',
                    iconSize: 'large',
                    group: 'left',
                    id: 'workflow',
                    position: 40,
                    items: []
                },
                divider = {
                    divider: true
                };

            // show settings template is order already saved
            if (this.options.data.id) {
                // add workflows provided by api
                for (i = -1, len = data.workflows.length; ++i < len;) {
                    workflow = data.workflows[i];

                    // if new section, add divider
                    if (workflowDropdown.items.length === 0) {
                        currentSection = workflow.section;
                    } else if (!!currentSection &&
                        currentSection !== workflow.section) {
                        workflowDropdown.items.push(divider);
                        currentSection = workflow.section;
                    }
                    // add workflow item
                    workflowDropdown.items.push({
                        title: this.sandbox.translate(workflow.title),
                        callback: createWorkflowCallback.bind(this, workflow)
                    });
                }

                // add workflow items
                if (workflowDropdown.items.length > 0) {
                    toolbarItems.push(workflowDropdown);
                }
            }
            // show toolbar
            this.sandbox.emit('sulu.header.set-toolbar', {
                template: toolbarItems
            });
        },

        /**
         * creates a callback for a workflow
         * @param workflow
         */
        createWorkflowCallback = function(workflow) {
            // if event is defined, call event
            if (workflow.hasOwnProperty('event') && !!workflow.event) {
                var params = workflow.parameters || null;
                this.sandbox.emit(workflow.event, params);
            }
            // else if route, check for unsaved data before routing
            else if (workflow.hasOwnProperty('route') && !!workflow.route) {
                checkForUnsavedData.call(this, function() {
                        this.sandbox.emit('sulu.router.navigate', workflow.route);
                    }.bind(this),
                    showErrorLabel.bind(this, '')
                );
            }
            // otherwise, log error
            else {
                this.sandbox.logger.log('no route or event provided for workflow with title ' + workflow.title);
            }
        },

        /**
         * confirm an order, checks for unsaved data and shows a warning
         */
        confirmOrder = function() {
            checkForUnsavedData.call(this, function() {
                    this.sandbox.emit('sulu.salesorder.order.confirm');
                },
                showErrorLabel.bind(this, constants.translationConversionFailed)
            );
        },

        /**
         * edit an order, checks for unsaved data and shows a warning
         */
        editOrder = function() {
            checkForUnsavedData.call(this, function() {
                    this.sandbox.emit('sulu.salesorder.order.edit');
                },
                showErrorLabel.bind(this, constants.translationConversionFailed)
            );
        },

        showErrorLabel = function(translationKey) {
            this.sandbox.emit('sulu.labels.error.show',
                this.sandbox.translate(translationKey));
        },

        /**
         * checks for unsaved data. if unsaved, a dialog is shown, else immediately proceed
         * @param callback - called when no unsaved data, or warning was confirmed
         * @param errorCallback - if submission fails
         */
        checkForUnsavedData = function(callback, errorCallback) {
            if (typeof callback !== 'function') {
                return;
            }

            // check if unsaved data
            if (!this.saved) {
                // show dialog
                this.sandbox.emit('sulu.overlay.show-warning',
                    'sulu.overlay.be-careful',
                    'sulu.overlay.save-unsaved-changes-confirm',
                    null,
                    function() {
                        this.submit().then(
                            callback.bind(this),
                            errorCallback.bind(this)
                        );
                    }.bind(this)
                );
            }
            // otherwise proceed
            else {
                callback.call(this);
            }
        },

        /**
         * get status of current order
         */
        getOrderStatusId = function() {
            return (!!this.options.data && !!this.options.data.status) ?
                this.options.data.status.id : null;
        },

        /**
         * set order statuses
         * @param statuses
         */
        setOrderStatuses = function(statuses) {
            this.options.orderStatuses = statuses;
        },

        bindCustomEvents = function() {
            // status change events
            this.sandbox.on('sulu.salesorder.order.edit.clicked', editOrder.bind(this));
            this.sandbox.on('sulu.salesorder.order.confirm.clicked', confirmOrder.bind(this));
            this.sandbox.on('sulu.salesorder.set-order-status', setOrderStatuses.bind(this));

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
            this.sandbox.on('sulu.header.toolbar.save', function() {
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
            }, this);
        },

        /**
         * set saved
         * @param {Bool} saved Defines if saved state should be shown
         */
        setSaved = function(saved) {
            if (saved !== this.saved) {
                var type = (!!this.options.data && !!this.options.data.id) ? 'edit' : 'add';
                this.sandbox.emit('sulu.header.toolbar.state.change', type, saved, true);
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

            if (!!data.account && !!data.account.id) {
                initSelectsByAccountId.call(this, data.account.id, data);
            }

            // starts form components
            this.sandbox.start([
                {
                    name: 'auto-complete@husky',
                    options: {
                        el: constants.accountInputId,
                        remoteUrl: constants.accountUrl,
                        resultKey: 'accounts',
                        getParameter: 'search',
                        value: !!data.account ? data.account : '',
                        instanceName: this.accountInstanceName,
                        valueKey: 'name',
                        noNewValues: true
                    }
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
                            this.options.data.deliveryAddress = preselect;
//                            setFormData.call(this, this.options.data);
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
//                            setFormData.call(this, this.options.data);
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
                    preselect = !!orderData && orderData.contact ? [orderData.contact.id] : null;
                    initContactSelect.call(this, data, preselect);
                }.bind(this))
                .fail(function(textStatus, error) {
                    this.sandbox.logger.error(textStatus, error);
                }.bind(this));
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
            sidebar: {
                width: 'fixed',
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

            // current id
            var id = this.options.data.id ? this.options.data.id : 'new';

            this.accountInstanceName = 'customerAccount' + id;

            // bind events
            bindCustomEvents.call(this);

            // set header
            setHeaderToolbar.call(this);
            setSaved.call(this, true);

            // render form
            this.render();

            // listen for changes in form
            this.listenForChange();

            // initialize sidebar
            Sidebar.initForDetail(this.sandbox, this.options.data);

        },

        render: function() {

            this.sandbox.dom.html(this.$el, this.renderTemplate(this.templates[0], {
                isEditable: this.isEditable,
                parseDate: CoreHelper.parseDate
            }));

            var data = this.options.data;

            // initialize form
            initForm.call(this, data);
        },

        submit: function() {
            this.dfdFormSaved = this.sandbox.data.deferred();
            this.sandbox.logger.log('save Model');

            if (this.sandbox.form.validate(form)) {
                var data = this.sandbox.form.getData(form);

                if (data.id === '') {
                    delete data.id;
                }

                // FIXME auto complete in mapper
                // only get id, if auto-complete is not empty:
                data.account = {
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
