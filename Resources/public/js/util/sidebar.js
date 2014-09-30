/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

define(['app-config', 'sulusalesshipping/model/shipping'], function(AppConfig, Shipping) {

    'use strict';

    /**
     * Binds events for the sidebar in the details view
     */
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

        /**
         * Binds events used by the sidebar in the list view
         */
        bindCustomEventsForListSidebar = function() {
            // navigate to edit contact
            this.sandbox.on('husky.datagrid.item.click', function(id) {
                // get data for sidebar via controller
                getDataForListSidebar.call(this, {
                    data: id,
                    callback: function(contact, account) {
                        this.sandbox.emit(
                            'sulu.sidebar.set-widget',
                                '/admin/widget-groups/shipping-info?contact=' + contact + '&account=' + account
                        );
                    }.bind(this)
                });
            }, this);
        },

        /**
         * Binds dom events for sidebar
         */
        bindDomEvents = function() {
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
        },

        /**
         * Gets data to init list sidebar with correct params values
         * @param payload
         */
        getDataForListSidebar = function(payload) {
            if (!!payload && !!payload.data && !!payload.callback && typeof payload.callback === 'function') {
                var model,
                    shipping = Shipping.findOrCreate({id: payload.data});

                shipping.fetch({
                    success: function(response) {
                        model = response.toJSON();
                        if (!!model.order.account && !!model.order.contact) {
                            payload.callback(model.order.contact.id, model.order.account.id);
                        } else {
                            this.sandbox.logger.error('received invalid data when initializing sidebar', model);
                        }
                    }.bind(this),
                    error: function() {
                        this.sandbox.logger.error('error while fetching order');
                    }.bind(this)
                });
            } else {
                this.sandbox.logger.error('param for getDataForListSidebar has to be an object with a data attribute and a valid callback (attribute)!');
            }
        };

    return {

        /**
         * Inits sidebar for details view
         * @param sandbox
         * @param data
         */
        initForDetail: function(sandbox, data) {

            var link = '/admin/widget-groups/shipping-detail{?params*}',
                url, uriTemplate;

            this.sandbox = sandbox;

            if (!!data.order.contact && !!data.order.account && !!data.status) {
                uriTemplate = this.sandbox.uritemplate.parse(link);
                url = uriTemplate.expand({
                    params: {
                        id: data.id,
                        number: data.number,
                        contact: data.order.contact.id,
                        account: data.order.account.id,
                        status: data.status.status,
                        locale: AppConfig.getUser().locale,
                        date: data.expectedDeliveryDate,
                        orderDate: data.order.orderDate,
                        orderNumber: data.order.number,
                        orderId: data.order.id
                    }
                });

                this.sandbox.emit('sulu.sidebar.set-widget', url);
                bindDomEvents.call(this);
                bindCustomEventsForDetailsSidebar.call(this);
            } else {
                this.sandbox.logger.error('required values for sidebar not present!');
            }
        },

        /**
         * Inits sidebar for the list view
         * @param sandbox
         */
        initForList: function(sandbox) {
            this.sandbox = sandbox;
            bindDomEvents.call(this);
            bindCustomEventsForListSidebar.call(this);
        }
    };
});
