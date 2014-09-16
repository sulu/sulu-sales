/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

define([
    'sulusalesorder/model/order',
    'sulusalesorder/util/header'
], function(Order, HeaderUtil) {

    'use strict';

    return {

        initialize: function() {
            this.bindCustomEvents();
            this.bindSidebarEvents();
            this.order = null;

            if (this.options.display === 'list') {
                this.renderList();
            } else if (this.options.display === 'form') {
                this.renderForm().then(function() {
                    HeaderUtil.setHeader.call(this, this.order);
                }.bind(this));
            } else {
                throw 'display type wrong';
            }
        },

        bindCustomEvents: function() {
            // delete order
            this.sandbox.on('sulu.salesorder.order.delete', this.showDeleteWarning.bind(this));

            // conversion events
            this.sandbox.on('sulu.salesorder.order.confirm', this.confirmOrder.bind(this));
            this.sandbox.on('sulu.salesorder.order.edit', this.editOrder.bind(this));

            // save the current package
            this.sandbox.on('sulu.salesorder.order.save', this.saveOrder.bind(this));

            // wait for navigation events
            this.sandbox.on('sulu.salesorder.orders.load', this.loadOrder.bind(this));

            // add new order
            this.sandbox.on('sulu.salesorder.order.new', this.addOrder.bind(this));

            // load list view
            this.sandbox.on('sulu.salesorder.orders.list', this.showOrderList.bind(this));

            this.sandbox.on('sulu.salesorder.shipping.create', this.createOrderShipping.bind(this));

            this.sandbox.on('salesorder.orders.sidebar.getData', this.getDataForOrderSidebar.bind(this));
        },

        /**
         * Gets data to init sidebar with correct params values
         * @param payload
         */
        getDataForOrderSidebar: function(payload){
            if(!!payload.data && !!payload.callback && typeof payload.callback === 'function'){
                var model,
                    order = Order.findOrCreate({id:payload.data});

                order.fetch({
                    success: function(response) {
                        model = response.toJSON();
                        if (!!model.account && !!model.contact) {
                            payload.callback(model.contact.id, model.account.id);
                        } else {
                            this.sandbox.logger.error('received invalid data when initializing sidebar', model);
                        }
                    }.bind(this),
                    error: function() {
                        this.sandbox.logger.error('error while fetching order');
                    }.bind(this)
                });
            } else {
                this.sandbox.logger.error('param for getDataForOrderSidebar has to be an object with a data attribute and a valid callback (attribute)!');
            }
        },

        /**
         * Binds general sidebar events
         */
        bindSidebarEvents: function() {
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
         * confirm an order
         */
        confirmOrder: function() {
            this.convertStatus('confirm');
        },

        /**
         * edit an order, which is already confirmed
         */
        editOrder: function() {
            this.convertStatus('edit');
        },

        /**
         * create a new shipping for an order
         */
        createOrderShipping: function() {
            this.sandbox.emit('sulu.router.navigate', HeaderUtil.getUrl.call(this, this.options.id, 'shippings/add'), true, false);
        },

        /**
         * convert status of an order
         * @param statusString
         */
        convertStatus: function(statusString) {
            // set action
            this.order.set({
                action: statusString
            });

            this.order.save(null, {
                type: 'post',
                success: function(response) {
                    this.sandbox.logger.log('successfully changed status', response);
                    this.loadOrder(this.order.id, true);
                }.bind(this)
            });
        },

        loadOrder: function(id, force) {
            force = (force === true);
            this.sandbox.emit('sulu.router.navigate', HeaderUtil.getUrl.call(this, id, 'details'), true, false, force);
        },

        showDeleteWarning: function(ids) {
            // show dialog
            this.sandbox.emit('sulu.overlay.show-warning',
                'sulu.overlay.be-careful',
                'sulu.overlay.delete-desc',
                null,
                this.delOrderHandler.bind(this, ids)
            );
        },

        // show confirmation and delete account
        delOrderHandler: function(ids) {
            this.sandbox.emit('sulu.header.toolbar.item.loading', 'options-button');

            if (this.sandbox.util.typeOf(ids) === 'array') {
                this.sandbox.util.foreach(ids, function(id) {
                    this.delOrder(id, function() {
                        this.sandbox.emit('husky.datagrid.record.remove', id);
                    }.bind(this), null);
                }.bind(this));
            } else {
                this.delOrder(ids, function() {
                    this.sandbox.emit('sulu.router.navigate', 'sales/orders');
                }.bind(this), null);
            }
        },

        /**
         * deletes an order
         * @param id
         * @param successCallback
         * @param failCallback
         */
        delOrder: function(id, successCallback, failCallback) {

            successCallback = typeof(successCallback) === 'function' ? successCallback : null;
            failCallback = typeof(failCallback) === 'function' ? failCallback : null;

            this.order = Order.findOrCreate({id: id});
            this.order.destroy({
                success: successCallback,
                fail: failCallback
            });
        },

        showOrderList: function() {
            this.sandbox.emit('sulu.router.navigate', 'sales/orders');
        },

        // saves an account
        saveOrder: function(data) {
            this.sandbox.emit('sulu.header.toolbar.item.loading', 'save-button');

            this.order.set(data);
            this.order.save(null, {
                // on success save contacts id
                success: function(response) {
                    var model = response.toJSON();
                    if (!!data.id) {
                        this.sandbox.emit('sulu.salesorder.order.saved', model);
                    } else {
                        this.sandbox.emit('sulu.router.navigate', 'sales/orders/edit:' + model.id + '/overview');
                    }
                }.bind(this),
                error: function() {
                    this.sandbox.logger.log("error while saving profile");
                }.bind(this)
            });
        },

        addOrder: function() {
            this.sandbox.emit('sulu.router.navigate', 'sales/orders/add');
        },

        renderList: function() {
            var $list = this.sandbox.dom.createElement('<div id="orders-list-container"/>');
            this.html($list);
            this.sandbox.start([
                {
                    name: 'orders/components/list@sulusalesorder',
                    options: {
                        el: $list
                    }
                }
            ]);
        },

        renderForm: function() {
            // load data and show form
            this.order = new Order();

            var $form = this.sandbox.dom.createElement('<div id="order-form-container"/>'),
                dfd = this.sandbox.data.deferred();
            this.html($form);

            if (!!this.options.id) {
                this.order = new Order({id: this.options.id});
                //account = this.getModel(this.options.id);
                this.order.fetch({
                    success: function(model) {
                        this.sandbox.start([
                            {name: 'orders/components/form@sulusalesorder', options: { el: $form, data: model.toJSON()}}
                        ]);
                        dfd.resolve();
                    }.bind(this),
                    error: function() {
                        this.sandbox.logger.log("error while fetching order");
                        dfd.reject();
                    }.bind(this)
                });
            } else {
                this.sandbox.start([
                    {name: 'orders/components/form@sulusalesorder', options: { el: $form, data: this.order.toJSON()}}
                ]);
                dfd.resolve();
            }
            return dfd.promise();
        }
    };
});
