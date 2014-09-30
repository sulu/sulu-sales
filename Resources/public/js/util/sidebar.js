/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

define(['app-config'], function(AppConfig) {

    'use strict';

    var bindCustomEventsForDetailsSidebar = function() {
            this.sandbox.on('sulu.flow-of-documents.orders.row.clicked', function(data) {
                var routePartials, routeForNavigation;

                if (!!data.route) {
                    this.sandbox.emit('sulu.router.navigate', data.route);

                    // adjusts navigation
                    routePartials = data.route.split('/');
                    routeForNavigation = routePartials[0] + '/' + routePartials[1];
                    this.sandbox.emit('husky.navigation.select-item', routeForNavigation);
                }
            }.bind(this));
        },
        bindCustomEventsForListSidebar = function() {
            // navigate to edit contact
            this.sandbox.on('husky.datagrid.item.click', function(id) {
                // get data for sidebar via controller
                this.sandbox.emit('salesorder.orders.sidebar.getData', {
                    data: id,
                    callback: function(contact, account) {
                        this.sandbox.emit(
                            'sulu.sidebar.set-widget',
                                '/admin/widget-groups/order-info?contact=' + contact + '&account=' + account
                        );
                    }.bind(this)
                });
            }, this);
        },

        bindCustomEvents = function() {
            this.sandbox.dom.off('#sidebar');

            this.sandbox.dom.on('#sidebar', 'click', function(event) {
                var id = this.sandbox.dom.data(event.currentTarget, 'id');
                this.sandbox.emit('sulu.router.navigate', 'contacts/accounts/edit:' + id + '/details');
                this.sandbox.emit('husky.navigation.select-item', 'contacts/accounts');
            }.bind(this), '#sidebar-account');

            this.sandbox.dom.on('#sidebar', 'click', function(event) {
                var id = this.sandbox.dom.data(event.currentTarget, 'id');
                this.sandbox.emit('sulu.router.navigate', 'contacts/contacts/edit:' + id + '/details');
                this.sandbox.emit('husky.navigation.select-item', 'contacts/contacts');
            }.bind(this), '#sidebar-contact');
        };

    return {

        initForDetail: function(sandbox, data) {

            var link = '/admin/widget-groups/order-detail{?params*}',
                url, uriTemplate;

            this.sandbox = sandbox;

            if (!!data.contact && !!data.account && !!data.status) {
                uriTemplate = this.sandbox.uritemplate.parse(link);
                url = uriTemplate.expand({
                    params: {
                        contact: data.contact.id,
                        account: data.account.id,
                        status: data.status.status,
                        locale: AppConfig.getUser().locale,
                        orderDate: data.orderDate,
                        orderNumber: data.number,
                        orderId: data.id
                    }
                });

                this.sandbox.emit('sulu.sidebar.set-widget', url);
                bindCustomEvents.call(this);
                bindCustomEventsForDetailsSidebar.call(this);
            } else {
                this.sandbox.logger.error('required values for sidebar not present!');
            }
        },

        initForList: function(sandbox) {
            this.sandbox = sandbox;
            bindCustomEvents.call(this);
            bindCustomEventsForListSidebar.call(this);
        }
    };
});
