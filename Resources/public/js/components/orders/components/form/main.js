/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

define([], function() {

    'use strict';

    var form = '#order-form',

        constants = {
            accountContactsUrl: '/admin/api/accounts/<%= id %>/contacts?flat=true',
            accountAddressesUrl: '/admin/api/accounts/<%= id %>/addresses',
            accountUrl: '/admin/api/accounts?searchFields=name&flat=true&fields=id,name',
            accountInputId: '#account-input',
            deliveryAddressInstanceName: 'delivery-address',
            paymentAddressInstanceName: 'invoice-address',
            contactSelectId: '#contact-select'
        },

        /**
         * set header toolbar based on current order status
         */
        setHeaderToolbar = function() {

            var toolbarItems = [
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
                settings = {
                    icon: 'gear',
                    iconSize: 'large',
                    group: 'left',
                    id: 'options-button',
                    position: 30,
                    items: [
                        {
                            title: this.sandbox.translate('toolbar.delete'),
                            callback: function() {
                                this.sandbox.emit('sulu.header.toolbar.delete');
                            }.bind(this)
                        }
                    ]
                },
                workflow = {
                    icon: 'hand-o-right',
                    iconSize: 'large',
                    group: 'left',
                    id: 'workflow',
                    position: 40,
                    items: []
                },
                workflowItems = {
                    confirm: {
                        title: this.sandbox.translate('salesorder.orders.confirm'),
                        callback: confirmOrder.bind(this)
                    },
                    edit: {
                        title: this.sandbox.translate('salesorder.orders.edit'),
                        callback: editOrder.bind(this)
                    },
                    shipping: {
                        title: this.sandbox.translate('salesorder.orders.shipping.create'),
                        callback: createShipping.bind(this)
                    },
                    divider: {
                        divider: true
                    }
                };

            // show settings template is order already saved
            if (this.options.data.id) {

                // define workflow based on orderStatus
                // TODO: get statuses from backend
                if (this.orderStatusId === 1) {
                    workflow.items.push(workflowItems.confirm);
                } else if (this.orderStatusId === 3) {
                    workflow.items.push(workflowItems.edit);
                }

                // more than in cart
                if (this.orderStatusId > 2) {
                    workflow.items.push(workflowItems.divider);
                    workflow.items.push(workflowItems.shipping);
                }

                // add settings items
                if (settings.items.length > 0) {
                    toolbarItems.push(settings);
                }
                // add workflow items
                if (workflow.items.length > 0) {
                    toolbarItems.push(workflow);
                }
            }
            // show toolbar
            this.sandbox.emit('sulu.header.set-toolbar', {
                template: toolbarItems
            });
        },

        /**
         * confirm an order, checks for unsaved data and shows a warning
         */
        confirmOrder = function() {
            checkForUnsavedData.call(this, function() {
                this.sandbox.emit('sulu.salesorder.order.confirm');
            });
        },

        /**
         * edit an order, checks for unsaved data and shows a warning
         */
        editOrder = function() {
            checkForUnsavedData.call(this, function() {
                this.sandbox.emit('sulu.salesorder.order.edit');
            });
        },

        /**
         * create a shipping, checks for unsaved data and shows a warning
         */
        createShipping = function() {
            checkForUnsavedData.call(this, function() {
                this.sandbox.emit('sulu.salesorder.shipping.create');
            });
        },

        /**
         * checks for unsaved data. if unsaved, a dialog is shown, else immediately proceed
         * @param callback - called when no unsaved data, or warning was confirmed
         */
        checkForUnsavedData = function(callback) {
            if (typeof callback !== 'function') {
                return;
            }

            // check if unsaved data
            if (!this.saved) {
                // show dialog
                this.sandbox.emit('sulu.overlay.show-warning',
                    'sulu.overlay.be-careful',
                    'sulu.overlay.unsaved-changes-confirm',
                    null,
                    callback.bind(this)
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

            this.sandbox.on('sulu.salesorder.set-order-status', setOrderStatuses.bind(this));

            // delete contact
            this.sandbox.on('sulu.header.toolbar.delete', function() {
                this.sandbox.emit('sulu.salesorder.order.delete', this.options.data.id);
            }, this);

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
            }, this);

            // contact save
            this.sandbox.on('sulu.header.toolbar.save', function() {
                this.submit();
            }, this);

            // back to list
            this.sandbox.on('sulu.header.back', function() {
                this.sandbox.emit('sulu.salesorder.orders.list');
            }, this);

            // TODO desired deliverydate
            this.sandbox.on('husky.input.desired-delivery-date.initialized', function() {
                this.dfdDesiredDeliveryDate.resolve();
            }, this);

            this.sandbox.on('husky.auto-complete.' + this.accountInstanceName + '.select', accountChangedListener.bind(this));

            this.sandbox.on('sulu.editable-data-row.delivery-address.initialized', function() {
                this.dfdDeliveryAddressInitialized.resolve();
            }.bind(this));

            this.sandbox.on('sulu.editable-data-row.invoice-address.initialized', function() {
                this.dfdInvoiceAddressInitialized.resolve();
            }.bind(this));

            this.sandbox.on('sulu.editable-data-row.address-view.delivery-address.changed', function(data) {
                this.options.data.deliveryAddress = data;
                setFormData.call(this, this.options.data);
                changeHandler.call(this);
            }.bind(this));

            this.sandbox.on('sulu.editable-data-row.address-view.invoice-address.changed', function(data) {
                this.options.data.invoiceAddress = data;
                setFormData.call(this, this.options.data);
                changeHandler.call(this);
            }.bind(this));
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

            // TODO: init desired delivery date
            this.dfdDesiredDeliveryDate.resolve();

            // TODO init address and contacts
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
                    initAddressComponents.call(this, [], constants.paymentAddressInstanceName);
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
            var data, preselect;
            this.sandbox.util.load(this.sandbox.util.template(constants.accountContactsUrl, {id: id}))
                .then(function(response) {
                    data = response._embedded.contacts;
                    preselect = !!orderData && orderData.contact ? [orderData.contact.id] : null;
                    initContactSelect.call(this, data, preselect);
                }.bind(this))
                .fail(function(textStatus, error) {
                    this.sandbox.logger.error(textStatus, error);
                }.bind(this));

            // load addresses of account
            this.sandbox.util.load(this.sandbox.util.template(constants.accountAddressesUrl, {id: id}))
                .then(function(response) {

                    // is already refactored in another branch
                    data = response._embedded.addresses;
                    preselect = null;

                    // when an address is already selected, the selected address should be used
                    // otherwise the first delivery / payment address found will be used
                    if (!orderData || !orderData.deliveryAddress) {
                        preselect = findAddressWherePropertyIs.call(this, data, 'deliveryAddress', true);

                        // when no delivery address is found the first address will be used
                        if(!preselect && data.length > 0) {
                            preselect = data[0];
                        }
                    }
                    this.sandbox.data.when(this.dfdDeliveryAddressInitialized).then(function() {
                        initAddressComponents.call(this, data, constants.deliveryAddressInstanceName, preselect);
                        this.options.data.deliveryAddress = preselect;
                        setFormData.call(this, this.options.data);
                    }.bind(this));

                    preselect = null;

                    if (!orderData || !orderData.invoiceAddress) {
                        preselect = findAddressWherePropertyIs.call(this, data, 'billingAddress', true);

                        // when no invoice address is found the first address will be used
                        if(!preselect && data.length > 0) {
                            preselect = data[0];
                        }
                    }

                    this.sandbox.data.when(this.dfdInvoiceAddressInitialized).then(function() {
                        initAddressComponents.call(this, data, constants.paymentAddressInstanceName, preselect);
                        this.options.data.invoiceAddress = preselect;
                        setFormData.call(this, this.options.data);
                    }.bind(this));

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

            this.dfdAllFieldsInitialized = this.sandbox.data.deferred();
            this.dfdAutoCompleteInitialized = this.sandbox.data.deferred();
            this.dfdDesiredDeliveryDate = this.sandbox.data.deferred();

            this.dfdInvoiceAddressInitialized = this.sandbox.data.deferred();
            this.dfdDeliveryAddressInitialized = this.sandbox.data.deferred();

            // define when all fields are initialized
            this.sandbox.data.when(this.dfdDesiredDeliveryDate, this.dfdAutoCompleteInitialized).then(function() {
                this.dfdAllFieldsInitialized.resolve();
            }.bind(this));

            this.orderStatusId = getOrderStatusId.call(this);

            // set if form is editable
            this.isEditable = this.orderStatusId <= 2;

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
            if (!!this.options.data && !!this.options.data.id) {
                this.initSidebar(
                    '/admin/widget-groups/order-detail',
                    this.options.data.contact.id,
                    this.options.data.account.id
                );
            }
        },

        initSidebar: function(url, contactId, accountId) {
            if(!!contactId && !!accountId){
                url += '?contact='+contactId+'&account='+accountId;
                this.sandbox.emit('sulu.sidebar.set-widget', url);
            } else {
                this.sandbox.logger.error('invalid values for account and contact ids!');
            }
        },

        render: function() {

            this.sandbox.dom.html(this.$el, this.renderTemplate(this.templates[0], {isEditable: this.isEditable}));

            var data = this.options.data;

            // initialize form
            initForm.call(this, data);
        },

        submit: function() {
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
            }
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

                // listen on
                this.sandbox.on('husky.select.contact-select.selected.item', changeHandler.bind(this));
                this.sandbox.on('husky.select.responsible-contact-select.selected.item', changeHandler.bind(this));
                this.sandbox.on('husky.select.delivery-terms.selected.item', changeHandler.bind(this));
                this.sandbox.on('husky.select.payment-terms.selected.item', changeHandler.bind(this));

                // TODO: use this for resetting account
//                    this.sandbox.dom.on(constants.accountInputId+' input', 'changed', accountChangedListener.bind(this));
            }.bind(this));

        }
    };
});
