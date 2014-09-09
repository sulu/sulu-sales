/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

define([
    'sulusalesshipping/model/shipping',
    'sulusalesorder/model/order',
    'sulusalesorder/util/header'
], function(Shipping, Order, OrderHeaderUtil) {

    'use strict';

    return {

        initialize: function() {
            this.bindCustomEvents();
            this.bindSidebarEvents();
            this.shipping = null;
            this.order = null;

            if (this.options.display === 'list') {
                this.renderList();
            } else if (this.options.display === 'form') {
                this.renderForm().then(function() {
//                    {
//                        breadcrumbAddition : [
//                            {title: 'salesshipping.shippings.title', event: 'salesshipping.order.shipping.list'},
//                        ],
//                            breadcrumbOrderEvent: 'salesshipping.order.load'
//                    }
                }.bind(this));
            } else if (this.options.display === 'orderList') {
                this.renderOrderList().then(function() {
                    OrderHeaderUtil.setHeader.call(this, this.order);
                }.bind(this));
            } else {
                throw 'display type wrong';
            }
        },

        bindCustomEvents: function() {
            // delete order
            this.sandbox.on('sulu.salesshipping.shipping.delete', this.showDeleteWarning.bind(this));

            // conversion events
            this.sandbox.on('sulu.salesshipping.shipping.confirm', this.confirmAction.bind(this));
            this.sandbox.on('sulu.salesshipping.shipping.edit', this.editAction.bind(this));

            // save the current package
            this.sandbox.on('sulu.salesshipping.shipping.save', this.saveAction.bind(this));

            // wait for navigation events
            this.sandbox.on('sulu.salesshipping.shipping.load', this.loadAction.bind(this));

            // add new order
            this.sandbox.on('sulu.salesshipping.shipping.new', this.addAction.bind(this));

            // load list view
            this.sandbox.on('sulu.salesshipping.shippings.list', this.listAction.bind(this));
        },

        /**
         * Binds general sidebar events
         */
        bindSidebarEvents: function() {
            // TODO: uncomment after sidebar is implemented
            // bind sidebar
//            this.sandbox.dom.off('#sidebar');
//
//            this.sandbox.dom.on('#sidebar', 'click', function(event) {
//                var id = this.sandbox.dom.data(event.currentTarget,'id');
//                this.sandbox.emit('sulu.contacts.accounts.load', id);
//            }.bind(this), '#sidebar-accounts-list');
//
//            this.sandbox.dom.on('#sidebar', 'click', function(event) {
//                var id = this.sandbox.dom.data(event.currentTarget,'id');
//                this.sandbox.emit('sulu.router.navigate', 'contacts/contacts/edit:' + id + '/details');
//                this.sandbox.emit('husky.navigation.select-item','contacts/contacts');
//            }.bind(this), '#main-contact');
        },

        /**
         * confirm a shipping
         */
        confirmAction: function() {
            this.convertStatus('confirm');
        },

        /**
         * edit a shipping, which is already confirmed
         */
        editAction: function() {
            this.convertStatus('edit');
        },

        /**
         * convert status of a shipping
         * @param statusString
         */
        convertStatus: function(statusString) {
            // set action
            this.shipping.set({
                action: statusString
            });

            this.shipping.save(null, {
                type: 'post',
                success: function(response) {
                    this.sandbox.logger.log('successfully changed status', response);
                    this.loadAction(this.shipping.id, true);
                }.bind(this)
            });
        },

        loadAction: function(id, orderId, force) {
            force = (force === true);
            this.sandbox.emit('sulu.router.navigate', 'sales/shippings/edit:' + id + '/details', true, false, force);
        },

        showDeleteWarning: function(ids){
            // show dialog
            this.sandbox.emit('sulu.overlay.show-warning',
                'sulu.overlay.be-careful',
                'sulu.overlay.delete-desc',
                null,
                this.delShippingHandler.bind(this, ids)
            );
        },

        // show confirmation and delete
        delShippingHandler: function(ids) {
            this.sandbox.emit('sulu.header.toolbar.item.loading', 'options-button');

            if (this.sandbox.util.typeOf(ids) === 'array') {
                this.sandbox.util.foreach(ids, function(id) {
                    this.deleteAction(id, function() {
                        this.sandbox.emit('husky.datagrid.record.remove', id);
                    }.bind(this), null);
                }.bind(this));
            } else {
                this.deleteAction(ids, function() {
                    this.sandbox.emit('sulu.router.navigate', 'sales/shippings');
                }.bind(this), null);
            }
        },

        /**
         * deletes a shipping
         * @param id
         * @param successCallback
         * @param failCallback
         */
        deleteAction: function(id, successCallback, failCallback){

            successCallback = typeof(successCallback) === 'function' ? successCallback : null;
            failCallback = typeof(failCallback) === 'function' ? failCallback : null;

            this.shipping = Shipping.findOrCreate({id: id});
            this.shipping.destroy({
                success: successCallback,
                fail: failCallback
            });
        },

        listAction: function() {
            this.sandbox.emit('sulu.router.navigate', 'sales/shippings');
        },

        // save action
        saveAction: function(data) {
            this.sandbox.emit('sulu.header.toolbar.item.loading', 'save-button');

            this.shipping.set(data);
            this.shipping.save(null, {
                // on success save contacts id
                success: function(response) {
                    var model = response.toJSON();
                    if (!!data.id) {
                        this.sandbox.emit('sulu.salesshipping.shipping.saved', model);
                    } else {
                        this.sandbox.emit('sulu.router.navigate', 'sales/shippings/edit:' + model.id + '/overview');
                    }
                }.bind(this),
                error: function() {
                    this.sandbox.logger.log("error while saving profile");
                }.bind(this)
            });
        },

        addAction: function() {
            this.sandbox.emit('sulu.router.navigate', 'sales/shippings/add');
        },

        renderList: function() {
            var $list = this.sandbox.dom.createElement('<div id="shippings-list-container"/>');
            this.html($list);
            this.sandbox.start([
                {
                    name: 'shippings/components/list@sulusalesshipping',
                    options: {
                        el: $list
                    }
                }
            ]);
        },

        renderOrderList: function() {
            var dfd = this.sandbox.data.deferred();
            if (!!this.options.id) {
                this.order = Order.findOrCreate({id: this.options.id});

                this.order.fetch({
                    success: function(model) {
                        var $list = this.sandbox.dom.createElement('<div id="order-shippings-list-container"/>');
                        this.html($list);
                        this.sandbox.start([
                            {
                                name: 'orders/components/shippings-list@sulusalesshipping',
                                options: {
                                    el: $list,
                                    data: this.order
                                }
                            }
                        ]);
                        dfd.resolve();
                    }.bind(this),
                    error: function() {
                        this.sandbox.logger.log("error while fetching order");
                        dfd.reject();
                    }.bind(this)
                });
            }
            return dfd.promise();
        },

        renderForm: function() {
            // load data and show form
            this.shipping = new Shipping();

            var dfd = this.sandbox.data.deferred();

            // shipping exists
            if (!!this.options.id) {
                this.shipping = new Shipping({id: this.options.id});
                this.shipping.fetch({
                    success: this.startFormCallback.bind(this, dfd),
                    error: this.errorCallback.bind(this, dfd)
                });

            // order id is set
            } else if (!!this.options.orderId) {
                this.order = Order.findOrCreate({id: this.options.orderId});

                this.order.fetch({
                    success: function(order) {
                        this.order = order;
                        this.shipping.set({order: order});
                        this.startFormCallback(dfd, this.shipping);
                    }.bind(this),
                    error: this.errorCallback.bind(this, dfd)
                });
            }
            return dfd.promise();
        },

        startFormCallback: function(dfd, shipping) {
            var $form = this.sandbox.dom.createElement('<div id="shipping-form-container"/>')
            this.html($form);

            this.sandbox.start([
                {name: 'shippings/components/form@sulusalesshipping', options: { el: $form, data: shipping.toJSON()}}
            ]);
            dfd.resolve();
        },

        errorCallback: function(dfd) {
            this.sandbox.logger.log("error while fetching shipping");
            dfd.reject();
        }
    };
});
