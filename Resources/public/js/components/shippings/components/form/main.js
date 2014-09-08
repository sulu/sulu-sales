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

    var form = '#shipping-form',

        constants = {
            accountId: '#account',
            contactId: '#contact',
            accountAddressesUrl: '/admin/api/accounts/<%= id %>/addresses',
            deliveryAddressInstanceName: 'delivery-address'
//            accountContactsUrl: '/admin/api/accounts/<%= id %>/contacts?flat=true',
//            accountUrl: '/admin/api/accounts?searchFields=name&flat=true&fields=id,name',
//            accountInputId: '#account-input',
//            paymentAddressInstanceName: 'invoice-address',
//            contactSelectId: '#contact-select',
//            itemTableId: '#order-items'
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
                        title: this.sandbox.translate('salesshipping.shippings.confirm'),
                        callback: confirmClickedHandler.bind(this)
                    },
                    edit: {
                        title: this.sandbox.translate('salesshipping.shippings.edit'),
                        callback: editClickedHandler.bind(this)
                    },
                    divider: {
                        divider: true
                    }
                };

            // if shipping is not new
            if (this.options.data.id) {
                // define workflow based on status
                // TODO: get statuses from backend
                if (this.shippingStatusId === 1) {
                    workflow.items.push(workflowItems.confirm);
                } else if (this.shippingStatusId === 3) {
                    workflow.items.push(workflowItems.edit);
                }

                // more than in cart
                if (this.shippingStatusId > 2) {
                    workflow.items.push(workflowItems.divider);
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
         * confirm a shipping, checks for unsaved data and shows a warning
         */
        confirmClickedHandler = function() {
            checkForUnsavedData.call(this, function() {
                this.sandbox.emit('sulu.salesshipping.shipping.confirm');
            });
        },

        /**
         * edit an order, checks for unsaved data and shows a warning
         */
        editClickedHandler = function() {
            checkForUnsavedData.call(this, function() {
                this.sandbox.emit('sulu.salesshipping.shipping.edit');
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
         * get status id of current shipping
         */
        getShippingStatusId = function() {
            return (!!this.options.data && !!this.options.data.status) ?
                this.options.data.status.id : null;
        },

        bindCustomEvents = function() {

            // delete shipping
            this.sandbox.on('sulu.header.toolbar.delete', function() {
                this.sandbox.emit('sulu.salesshipping.shipping.delete', this.options.data.id);
            }, this);

            // shipping saved
            this.sandbox.on('sulu.salesshipping.shipping.saved', function(data) {
                this.options.data = data;
                setSaved.call(this, true);
            }, this);

            // save
            this.sandbox.on('sulu.header.toolbar.save', function() {
                this.submit();
            }, this);

            // back to list
            this.sandbox.on('sulu.header.back', function() {
                this.sandbox.emit('sulu.salesshipping.shippings.list');
            }, this);

            // TODO expected deliverydate
            this.sandbox.on('husky.input.expected-delivery-date.initialized', function() {
                this.dfdExpectedDeliveryDate.resolve();
            }, this);

            this.sandbox.on('sulu.editable-data-row.delivery-address.initialized', function() {
                this.dfdDeliveryAddressInitialized.resolve();
            }.bind(this));

            this.sandbox.on('sulu.editable-data-row.address-view.delivery-address.changed', function(data) {
                this.options.data.deliveryAddress = data;
                setFormData.call(this, this.options.data);
                changeHandler.call(this);
            }.bind(this));
        },

        /**
         * Sets the title to the username
         * default title as fallback
         */
        setHeaderTitle = function() {
            var title = this.sandbox.translate('salesshipping.shipping'),
                breadcrumb = [
                    {title: 'navigation.sales'},
                    {title: 'salesshipping.shippings.title', event: 'salesshipping.shipping.list'}
                ];

            if (!!this.options.data && !!this.options.data.number) {
                title += ' #' + this.options.data.number;
                breadcrumb.push({title: '#' + this.options.data.number});
            }

            this.sandbox.emit('sulu.header.set-title', title);
            this.sandbox.emit('sulu.header.set-breadcrumb', breadcrumb);
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
                if (data.hasOwnProperty('deliveryAddress')) {
                    // set account
                    this.sandbox.dom.html(
                        this.$find(constants.accountId),
                        data.deliveryAddress.accountName
                    );
                    // set contact
                    this.sandbox.dom.html(
                        this.$find(constants.contactId),
                        data.deliveryAddress.firstName + ' ' + data.deliveryAddress.lastName
                    );
                }
            }.bind(this)).fail(function(error) {
                this.sandbox.logger.error("An error occured when setting data!", error);
            }.bind(this));
        },

        setDeliveryAddress = function(shippingData) {
            // load addresses of account
            this.sandbox.util.load(this.sandbox.util.template(constants.accountAddressesUrl, {id: shippingData.order.account.id}))
                .then(function(response) {
                    // is already refactored in another branch
                    var addressesData = response._embedded.addresses,
                        data = null;

                    // if no deliveryAddress is set, try to get it from existing data
                    // when an address is already selected, the selected address should be used
                    // otherwise the first delivery / payment address found will be used
                    if (!shippingData || !shippingData.deliveryAddress) {
                        // check if order is assigned (then a deliveryAddress must be defined
                        if (shippingData && shippingData.hasOwnProperty('order')) {
                            data = shippingData.order.deliveryAddress;
                        // when no delivery address is found the first address will be used
                        } else if(!data && addressesData.length > 0) {
                            data = addressesData[0];
                        }
                    } else {
                        data = shippingData.deliveryAddress;
                    }

                    this.sandbox.data.when(this.dfdDeliveryAddressInitialized).then(function() {
                        this.sandbox.emit('sulu.editable-data-row.' + constants.deliveryAddressInstanceName + '.data.update', addressesData, data);
                        this.options.data.deliveryAddress = data;
                        setFormData.call(this, this.options.data);
                    }.bind(this));


                }.bind(this))
                .fail(function(textStatus, error) {
                    this.sandbox.logger.error(textStatus, error);
                }.bind(this));
        },

        /**
         * start components form components
         * @param data
         */
        startFormComponents = function(data) {
            this.sandbox.start(form);
            setDeliveryAddress.call(this, data);
        },

        /**
         * called when headerbar should be saveable
         */
        changeHandler = function() {
            setSaved.call(this, false);
        };

    return {

        view: true,

        layout: {
//                sidebar: {
//                    width: 'fixed',
//                    cssClasses: 'sidebar-padding-50'
//                }
        },

        templates: ['/admin/shipping/template/shipping/form'],

        initialize: function() {
            this.saved = true;

            this.dfdAllFieldsInitialized = this.sandbox.data.deferred();
            this.dfdExpectedDeliveryDate = this.sandbox.data.deferred();
            this.dfdInvoiceAddressInitialized = this.sandbox.data.deferred();
            this.dfdDeliveryAddressInitialized = this.sandbox.data.deferred();

            // define when all fields are initialized
            this.sandbox.data.when(this.dfdExpectedDeliveryDate).then(function() {
                this.dfdAllFieldsInitialized.resolve();
            }.bind(this));


            this.shippingStatusId = getShippingStatusId.call(this);
            // set if form is editable
            this.isEditable = this.shippingStatusId <= 2;

            // bind events
            bindCustomEvents.call(this);

            // set header
            setHeaderTitle.call(this);
            setHeaderToolbar.call(this);
            setSaved.call(this, true);

            // render form
            this.render();

            // listen for changes in form
            this.listenForChange();

            // initialize sidebar
//                if (!!this.options.data && !!this.options.data.id) {
//                    this.initSidebar(
//                        '/admin/widget-groups/contact-detail?contact=',
//                        this.options.data.id
//                    );
//                }
        },

        initSidebar: function(url, id) {
            this.sandbox.emit('sulu.sidebar.set-widget', url + id);
        },

        render: function() {
            var data = this.options.data;

            this.sandbox.dom.html(this.$el, this.renderTemplate(this.templates[0], this.sandbox.util.extend({}, data, {isEditable: this.isEditable})));

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

                this.sandbox.logger.log('log data', data);
                this.sandbox.emit('sulu.salesshipping.shipping.save', data);
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

//                // listen on
                this.sandbox.on('husky.select.delivery-terms.selected.item', changeHandler.bind(this));
            }.bind(this));

        }
    };
});
