/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

define([
    'sulusalesorder/model/order'
], function(Order) {

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
//                    AccountsUtilHeader.setHeader.call(this, this.account, this.options.accountType);
                }.bind(this));
            } else {
                throw 'display type wrong';
            }
        },

        bindCustomEvents: function() {
            // delete order
            this.sandbox.on('sulu.salesorder.order.delete', this.delOrder.bind(this));

            // save the current package
            this.sandbox.on('sulu.salesorder.order.save', this.saveOrder.bind(this));

            // wait for navigation events
            this.sandbox.on('sulu.salesorder.orders.load', this.loadOrder.bind(this));

            // add new order
            this.sandbox.on('sulu.salesorder.order.new', this.addOrder.bind(this));

            // load list view
            this.sandbox.on('sulu.salesorder.orders.list', this.showOrderList.bind(this));
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

        loadOrder: function(id) {
            this.sandbox.emit('sulu.router.navigate', 'sales/orders/edit:' + id + '/details');
        },

        // show confirmation and delete account
        delOrder: function() {
                this.sandbox.emit('sulu.header.toolbar.item.loading', 'options-button');

            // show dialog
            this.sandbox.emit('sulu.overlay.show-warning',
                'sulu.overlay.be-careful',
                'sulu.overlay.delete-desc',
                null,
                function() {
                    this.order.destroy({
                        success: function() {
                            this.sandbox.emit('sulu.router.navigate', 'sales/order');
                        }.bind(this)
                    });
                }
            );
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

        addOrder: function(data) {
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
