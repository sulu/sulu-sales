/*
* This file is part of the Sulu CMS.
*
* (c) MASSIVE ART WebServices GmbH
*
* This source file is subject to the MIT license that is bundled
* with this source code in the file LICENSE.
*/

define([
    'sulusalesshipping/util/shippingStatus',
    'sulusalesshipping/util/sidebar'
], function(ShippingStatus, Sidebar) {

    'use strict';

    var form = '#shipping-form',

        defaults = {
            shippedOrderItems: null
        },

        constants = {
            accountId: '#account',
            contactId: '#contact',
            accountAddressesUrl: '/admin/api/accounts/<%= id %>/addresses',
            deliveryAddressInstanceName: 'delivery-address',
            validateQuantityWarningTranslation: 'salesshipping.shipping.validation.quantityError',
            validateWarningTranslation: 'salesshipping.shipping.validation.error'
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
                workflowDropdown = {
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
                    ship: {
                        title: this.sandbox.translate('salesshipping.shippings.ship'),
                        callback: shipClickedHandler.bind(this)
                    },
                    cancel: {
                        title: this.sandbox.translate('salesshipping.shippings.cancel'),
                        callback: cancelClickedHandler.bind(this)
                    },
                    divider: {
                        divider: true
                    }
                };

            // if shipping is not new
            if (this.options.data.id) {
                // define workflow based on status
                if (this.shippingStatusId === ShippingStatus.CREATED) {
                    workflowDropdown.items.push(workflowItems.confirm);
                } else if (this.shippingStatusId === ShippingStatus.DELIVERY_NOTE) {
                    workflowDropdown.items.push(workflowItems.edit);
                    workflowDropdown.items.push(workflowItems.cancel);
                    workflowDropdown.items.push(workflowItems.divider);
                    workflowDropdown.items.push(workflowItems.ship);
                } else if (this.shippingStatusId === ShippingStatus.SHIPPED) {
                    workflowDropdown.items.push(workflowItems.cancel);
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
         * ship an order, checks for unsaved data and shows a warning
         */
        shipClickedHandler = function() {
            checkForUnsavedData.call(this, function() {
                this.sandbox.emit('sulu.salesshipping.shipping.ship');
            });
        },

        /**
         * cancels an order
         */
        cancelClickedHandler = function() {
            checkForUnsavedData.call(this, function() {
                this.sandbox.emit('sulu.salesshipping.shipping.cancel');
            });
        },

        /**
         * checks for unsaved data. if unsaved, a dialog is shown, else immediately proceed
         * @param callback - called when no unsaved data, or warning was confirmed
         */
        checkForUnsavedData = function(callback) {
            if (typeof callback !== 'function') {
                throw 'checkForUnsavedData: callback is not a function';
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

            this.sandbox.on('husky.toolbar.header.initialized', function() {
                // if new shipping, enable save button
                setSaved.call(this, !this.isNew);
            }.bind(this));

            // back to list
            this.sandbox.on('sulu.header.back', function() {
                this.sandbox.emit('sulu.salesshipping.shippings.list');
                this.sandbox.emit('husky.navigation.select-item','sales/shippings');
            }, this);

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
                    {title: 'salesshipping.shippings.title', event: 'salesshipping.shippings.list'}
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
                // set customer and contact based on orders delivery address
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
            this.sandbox.util.load(this.sandbox.util.template(constants.accountAddressesUrl, {id: shippingData.order.customerAccount.id}))
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
                        this.dfdAddressSet.resolve();
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
            sidebar: {
                width: 'fixed',
                cssClasses: 'sidebar-padding-50'
            }
        },

        templates: ['/admin/shipping/template/shipping/form'],

        initialize: function() {

            this.options = this.sandbox.util.extend({}, defaults, this.options);

            this.saved = true;
            this.isNew = !this.options.data.id;

            this.dfdAllFieldsInitialized = this.sandbox.data.deferred();
            this.dfdExpectedDeliveryDate = this.sandbox.data.deferred();
            this.dfdDesiredDeliveryDate = this.sandbox.data.deferred();
            this.dfdAddressSet = this.sandbox.data.deferred();
            this.dfdDeliveryAddressInitialized = this.sandbox.data.deferred();

            // define when all fields are initialized
            this.sandbox.data.when(this.dfdExpectedDeliveryDate, this.dfdAddressSet).then(function() {
                this.dfdAllFieldsInitialized.resolve();
            }.bind(this));


            this.shippingStatusId = getShippingStatusId.call(this);

            // set if form is editable
            this.isEditable = (!this.shippingStatusId || this.shippingStatusId === ShippingStatus.CREATED);

            // bind events
            bindCustomEvents.call(this);

            // set header
            setHeaderTitle.call(this);
            setHeaderToolbar.call(this);

            // render form
            this.render();

            // listen for changes in form
            this.listenForChange();

            // initialize sidebar
            Sidebar.initForDetail(this.sandbox, this.options.data);

        },

        render: function() {
            var data = this.options.data,
                templateData = this.sandbox.util.extend({}, {
                    isEditable: this.isEditable,
                    isNew: this.isNew,
                    shippedOrderItems: this.options.shippedOrderItems
                }, data);

            this.sandbox.dom.html(this.$el, this.renderTemplate(this.templates[0], templateData));

            // initialize form
            initForm.call(this, data);
        },

        submit: function() {
            var data = this.sandbox.form.getData(form),
                quantityIsZero = this.isQuantityZero(data);

            if (this.sandbox.form.validate(form) && !quantityIsZero) {

                if (data.id === '') {
                    delete data.id;
                }
                this.sandbox.logger.log('saving shipping', data);
                this.sandbox.emit('sulu.salesshipping.shipping.save', data);
            } else {
                if (!!quantityIsZero) {
                    this.sandbox.emit('sulu.labels.warning.show', this.sandbox.translate(constants.validateQuantityWarningTranslation));
                } else {
                    this.sandbox.emit('sulu.labels.warning.show', this.sandbox.translate(constants.validateWarningTranslation));
                }
            }
        },

        /**
         * checks if total quantity of the shippment is bigger than zero
         * @param data
         * @returns {boolean}
         */
        isQuantityZero: function(data) {
            var result = true;
            this.sandbox.util.foreach(data.items, function(item) {
                if (parseInt(item.quantity, 10) > 0) {
                    result = false;
                    return false;
                }
            }.bind(this));

            return result;
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
            }.bind(this));
        }
    };
});
