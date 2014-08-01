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
//                this.renderForm().then(function() {
//                    AccountsUtilHeader.setHeader.call(this, this.account, this.options.accountType);
//                }.bind(this));
            } else {
                throw 'display type wrong';
            }
        },

        bindCustomEvents: function() {
            // delete order
            this.sandbox.on('sulu.contacts.account.delete', this.del.bind(this));
//
//            // save the current package
//            this.sandbox.on('sulu.contacts.accounts.save', this.save.bind(this));
//
//            // wait for navigation events
//            this.sandbox.on('sulu.contacts.accounts.load', this.load.bind(this));
        },

        /**
         * Binds general sidebar events
         */
        bindSidebarEvents: function(){
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

        // show confirmation and delete account
        del: function() {

                this.sandbox.emit('sulu.header.toolbar.item.loading', 'options-button');
                this.order.destroy({
                    data: {removeContacts: !!removeContacts},
                    processData: true,
                    success: function() {
                        this.sandbox.emit('sulu.router.navigate', 'contacts/accounts');
                    }.bind(this)
                });
        },

        // saves an account
        save: function(data) {
//            this.sandbox.emit('sulu.header.toolbar.item.loading', 'save-button');
//
//            this.account.set(data);
//            this.account.save(null, {
//                // on success save contacts id
//                success: function(response) {
//                    var model = response.toJSON();
//                    if (!!data.id) {
//                        this.sandbox.emit('sulu.contacts.accounts.saved', model);
//                    } else {
//                        this.sandbox.emit('sulu.router.navigate', 'contacts/accounts/edit:' + model.id + '/details');
//                    }
//                }.bind(this),
//                error: function() {
//                    this.sandbox.logger.log("error while saving profile");
//                }.bind(this)
//            });
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
            this.order = new Account();

            var accTypeId,
                $form = this.sandbox.dom.createElement('<div id="accounts-form-container"/>'),
                dfd = this.sandbox.data.deferred();
            this.html($form);

            if (!!this.options.id) {
                this.account = new Account({id: this.options.id});
                //account = this.getModel(this.options.id);
                this.account.fetch({
                    success: function(model) {
                        this.sandbox.start([
                            {name: 'accounts/components/form@sulucontact', options: { el: $form, data: model.toJSON()}}
                        ]);
                        dfd.resolve();
                    }.bind(this),
                    error: function() {
                        this.sandbox.logger.log("error while fetching contact");
                        dfd.reject();
                    }.bind(this)
                });
            } else {
                accTypeId = AccountsUtilHeader.getAccountTypeIdByTypeName.call(this, this.options.accountType);
                this.account.set({type: accTypeId});
                this.sandbox.start([
                    {name: 'accounts/components/form@sulucontact', options: { el: $form, data: this.account.toJSON()}}
                ]);
                dfd.resolve();
            }
            return dfd.promise();
        },

        showDeleteConfirmation: function(ids, callbackFunction) {
            if (ids.length === 0) {
                return;
            } else if (ids.length === 1) {
                // if only one account was selected - get related sub-companies and contacts (and show the first 3 ones)
                this.confirmSingleDeleteDialog(ids[0], callbackFunction);
            } else {
                // if multiple accounts were selected, get related sub-companies and show simplified message
                this.confirmMultipleDeleteDialog(ids, callbackFunction);
            }
        },

        confirmSingleDeleteDialog: function(id, callbackFunction) {
            var url = '/admin/api/accounts/' + id + '/deleteinfo';

            this.sandbox.util.ajax({
                headers: {
                    'Content-Type': 'application/json'
                },

                context: this,
                type: 'GET',
                url: url,

                success: function(response) {
                    this.showConfirmSingleDeleteDialog(response, id, callbackFunction);
                }.bind(this),

                error: function(jqXHR, textStatus, errorThrown) {
                    this.sandbox.logger.error("error during get request: " + textStatus, errorThrown);
                }.bind(this)
            });
        },
    };
});
