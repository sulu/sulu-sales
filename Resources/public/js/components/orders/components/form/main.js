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
            accountAddressesUrl: '/admin/api/accounts/<%= id %>/addresses?flat=true',
            accountUrl: '/admin/api/accounts?searchFields=name&flat=true&fields=id,name',
            accountInputId: '#account-input',
            deliveryAddressInstanceName: 'delivery-address-select',
            paymentAddressInstanceName: 'payment-address-select',
            contactSelectId: '#contact-select',
            itemTableId: '#order-items'
        },

        /**
         * set header toolbar based on current order status
         * @param editable
         */
        setHeaderToolbar = function() {

            var orderStatus,
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
                    icon: 'hand-o-left',
                    iconSize: 'large',
                    group: 'left',
                    id: 'workflow',
                    position: 40,
                    items: []
                };

            // show settings template is order already saved
            if (this.options.data.id) {

                orderStatus = getOrderStatus.call(this);

                // if order has a status add extra information
                // TODO: show menu based on current status
                if (orderStatus !== 2) {
                    workflow.items.push(
                        {
                            title: this.sandbox.translate('salesorder.order.confirm'),
                            callback: confirmOrder.bind(this)
                        }
                    );
                }

                toolbarItems.push(settings);
                toolbarItems.push(workflow);
            }
            // show toolbar
            this.sandbox.emit('sulu.header.set-toolbar', {
                template: toolbarItems
            });
        },

        /**
         * c
         * @param force - do not check for unsaved data
         */
        confirmOrder = function(force) {

            // check if unsaved data
            if (!this.saved && !force) {
                // show dialog
                this.sandbox.emit('sulu.overlay.show-warning',
                    'sulu.overlay.be-careful',
                    'sulu.overlay.unsaved-changes',
                    null,
                    function() {
                        confirmOrder.call(this, true);
                    }.bind(this)
                );
                return;
            }

            // check if status is at least created

            this.sandbox.emit('sulu.salesorder.order.confirm');
        },

        /**
         * get status of current order
         * @returns
         */
        getOrderStatus = function() {
            return (!!this.options.data && !!this.options.data.status) ?
                this.options.data.status : null;
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
                this.dfdAutoCompleteInitialized.resolve();
            }, this);

            this.sandbox.on('husky.auto-complete.' + this.accountInstanceName + '.selection-removed', accountChangedListener.bind(this));

            // contact saved
            this.sandbox.on('sulu.salesorder.order.saved', function(data) {
                this.options.data = data;
                this.setHeaderBar(true);
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
        },

        /**
         * Sets the title to the username
         * default title as fallback
         */
        setTitle = function() {
            var title = this.sandbox.translate('salesorder.order'),
                breadcrumb = [
                    {title: 'navigation.sales'},
                    {title: 'salesorder.orders.title', event: 'sulu.salesorder.orders.list'}
                ];

            if (!!this.options.data && !!this.options.data.number) {
                title += ' #' + this.options.data.number;
                breadcrumb.push({title: '#' + this.options.data.number});
            }

            this.sandbox.emit('sulu.header.set-title', title);
            this.sandbox.emit('sulu.header.set-breadcrumb', breadcrumb);
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
         * @param selectInstanceName
         * @param preselectedElements
         */
        initAddressSelect = function(data, selectInstanceName, preselectedElements) {

            preselectedElements = preselectedElements || [];

            this.sandbox.emit('husky.select.' + selectInstanceName + '.update', data, preselectedElements);
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
                    initAddressSelect.call(this, [], constants.deliveryAddressInstanceName);
                    initAddressSelect.call(this, [], constants.paymentAddressInstanceName);
                }
            }
        },

        /**
         * called when headerbar should be saveable
         * @param event
         */
        changeHandler = function(event) {
            this.setHeaderBar(false);
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
                    data = response._embedded.addresses;
                    preselect = !!orderData && orderData.deliveryAddress ? [orderData.deliveryAddress.address.id] : null;
                    initAddressSelect.call(this, data, constants.deliveryAddressInstanceName, preselect);
                    preselect = !!orderData && orderData.invoiceAddress ? [orderData.invoiceAddress.address.id] : null;
                    initAddressSelect.call(this, data, constants.paymentAddressInstanceName, preselect);
                }.bind(this))
                .fail(function(textStatus, error) {
                    this.sandbox.logger.error(textStatus, error);
                }.bind(this));
        };
    return {

        view: true,

        layout: {
//                sidebar: {
//                    width: 'fixed',
//                    cssClasses: 'sidebar-padding-50'
//                }
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

            // define when all fields are initialized
            this.sandbox.data.when(this.dfdDesiredDeliveryDate, this.dfdAutoCompleteInitialized).then(function() {
                this.dfdAllFieldsInitialized.resolve();
            }.bind(this));

            setTitle.call(this);

            this.render();

            setHeaderToolbar.call(this, getOrderStatus.call(this));
            this.listenForChange();

//                if (!!this.options.data && !!this.options.data.id) {
//                    this.initSidebar(
//                        '/admin/widget-groups/contact-detail?contact=',
//                        this.options.data.id
//                    );
//                }

            this.setHeaderBar(true);

            // bind events
            bindCustomEvents.call(this);
        },

        initSidebar: function(url, id) {
            this.sandbox.emit('sulu.sidebar.set-widget', url + id);
        },

        render: function() {

            this.sandbox.dom.html(this.$el, this.renderTemplate(this.templates[0]));

            var data = this.options.data,
                id = data.id ? data.id : 'new';

            this.accountInstanceName = 'customerAccount' + id;

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

        // @var Bool saved - defines if saved state should be shown
        setHeaderBar: function(saved) {
            if (saved !== this.saved) {
                var type = (!!this.options.data && !!this.options.data.id) ? 'edit' : 'add';
                this.sandbox.emit('sulu.header.toolbar.state.change', type, saved, true);
            }
            this.saved = saved;
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
