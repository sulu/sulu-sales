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
            contactSelectId: '#contact-select'
        },

        setHeaderToolbar = function() {
            this.sandbox.emit('sulu.header.set-toolbar', {
                template: 'default'
            });
        },

        bindCustomEvents = function() {
            // delete contact
            this.sandbox.on('sulu.header.toolbar.delete', function() {
                this.sandbox.emit('sulu.salesorder.order.delete', this.options.data.id);
            }, this);

            this.sandbox.on('husky.auto-complete.' + this.accountInstanceName + '.initialized', function() {
                this.dfdAutoCompleteInitialized.resolve();
            }, this);

            // contact saved
            this.sandbox.on('sulu.salesorder.order.saved', function(data) {
                this.options.data = data;
                this.setHeaderBar(true);
//                this.initContactData();
//                this.setFormData(data);
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

            if (!!this.options.data && !!this.options.data.id) {
                title += ' #' + this.options.data.id;
                breadcrumb.push({title: '#' + this.options.data.id});
            }

            this.sandbox.emit('sulu.header.set-title', title);
            this.sandbox.emit('sulu.header.set-breadcrumb', breadcrumb);
        },

        initForm = function(data) {
            var formObject = this.sandbox.form.create(form);
            formObject.initialized.then(function() {
                setFormData.call(this, data, true);
            }.bind(this));
        },

        setFormData = function(data) {
            // add collection filters to form
            this.sandbox.form.setData(form, data).then(function() {

                this.accountId = getAccountId.call(this);

                // TODO: resolve that form is set
                startFormComponents.call(this, data);
            }.bind(this)).fail(function(error) {
                this.sandbox.logger.error("An error occured when setting data!", error);
            }.bind(this));
        },

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

        getAccountId = function() {
            return this.sandbox.dom.attr(constants.accountInputId, 'data-id');
        },

        initContactSelect = function(data, preselectedElements) {

            preselectedElements = preselectedElements || [];

            this.sandbox.emit('husky.select.contact-select.update', data, preselectedElements);
        },

        initAddressSelect = function(data, selectInstanceName, preselectedElements) {

            preselectedElements = preselectedElements || [];

            this.sandbox.emit('husky.select.' + selectInstanceName + '.update', data, preselectedElements);
        },

        accountChangedListener = function(event) {
            var id = event.id;

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
    return (function() {
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

                setHeaderToolbar.call(this);
                this.listenForChange();

//                if (!!this.options.data && !!this.options.data.id) {
//                    this.initSidebar(
//                        '/admin/widget-groups/contact-detail?contact=',
//                        this.options.data.id
//                    );
//                }

                this.setHeaderBar(true);
            },

            initSidebar: function(url, id) {
                this.sandbox.emit('sulu.sidebar.set-widget', url + id);
            },

            render: function() {
                this.sandbox.dom.html(this.$el, this.renderTemplate(this.templates[0]));

                var data = this.options.data,
                    id = data.id ? data.id : 'new';

                this.contactInstanceName = 'customerContact' + id;
                this.accountInstanceName = 'customerAccount' + id;

                // initialize form
                initForm.call(this, data);

                // bind events
                bindCustomEvents.call(this);
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

//            /**
//             * Register events for editable drop downs
//             * @param instanceName
//             */
//            initializeDropDownListender: function(instanceName) {
//                var instance = 'husky.select.' + instanceName;
//                this.sandbox.on(instance + '.selected.item', function(id) {
//                    if (id > 0) {
//                        this.setHeaderBar(false);
//                    }
//                }.bind(this));
//                this.sandbox.on(instance + '.deselected.item', function() {
//                    this.setHeaderBar(false);
//                }.bind(this));
//            },

            // event listens for changes in form
            listenForChange: function() {
                // listen for change after TAGS and BIRTHDAY-field have been set
                this.sandbox.data.when(this.dfdAllFieldsInitialized).then(function() {

                    this.sandbox.dom.on(form, 'change', function() {
                            this.setHeaderBar(false);
                        }.bind(this),
                            '.changeListener select, ' +
                            '.changeListener input, ' +
                            '.changeListener textarea');

                    this.sandbox.dom.on(form, 'keyup', function() {
                            this.setHeaderBar(false);
                        }.bind(this),
                            '.changeListener select, ' +
                            '.changeListener input, ' +
                            '.changeListener textarea');

                    // TODO: use this for resetting account
//                    this.sandbox.dom.on(constants.accountInputId+' input', 'changed', accountChangedListener.bind(this));
                }.bind(this));

            }
        };
    })();
});
