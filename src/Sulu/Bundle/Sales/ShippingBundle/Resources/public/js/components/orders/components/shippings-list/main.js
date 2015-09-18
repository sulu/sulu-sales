/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

define([
    'app-config',
    'sulusalesorder/util/sidebar',
    'sulusalesorder/util/orderStatus',
    'sulusalesorder/util/header',
    'widget-groups'
], function(AppConfig, Sidebar, OrderStatus, OrderHeaderUtil, WidgetGroups) {

    'use strict';

    var bindCustomEvents = function() {
            // delete clicked
            this.sandbox.on('sulu.list-toolbar.delete', function() {
                this.sandbox.emit('husky.datagrid.items.get-selected', function(ids) {
                    this.sandbox.emit('sulu.salesshipping.shipping.delete', ids);
                }.bind(this));
            }, this);

            // add clicked
            this.sandbox.on('sulu.list-toolbar.add', function() {
                this.sandbox.emit('sulu.salesshipping.shipping.new', this.orderId);
            }, this);

            // back to list
            this.sandbox.on('sulu.header.back', function() {
                this.sandbox.emit('sulu.salesshipping.orders.list');
            }, this);

            //// checkbox clicked
            //this.sandbox.on('husky.datagrid.number.selections', function(number) {
            //    var postfix = number > 0 ? 'enable' : 'disable';
            //    this.sandbox.emit('sulu.header.toolbar.shippings.item.' + postfix, 'delete', false);
            //}, this);
        },

        getListToolbarTemplate = function() {
            return [
                {
                    id: 'add',
                    icon: 'plus-circle',
                    class: 'highlight-white',
                    disabled: this.options.data.status.id < OrderStatus.CONFIRMED,
                    position: 1,
                    title: this.sandbox.translate('sulu.list-toolbar.add'),
                    callback: function() {
                        this.sandbox.emit('sulu.list-toolbar.add');
                    }.bind(this)
                },
                {
                    id: 'deleteSelected',
                    icon: 'trash-o',
                    position: 2,
                    title: this.sandbox.translate('sulu.list-toolbar.delete'),
                    callback: function() {
                        this.sandbox.emit('sulu.list-toolbar.delete');
                    }.bind(this)
                },
                {
                    id: 'settings',
                    icon: 'gear',
                    dropdownItems: [
                        {
                            type: 'columnOptions'
                        }
                    ]
                }
            ];
        },

        datagridAction = function(id) {
            this.sandbox.emit('sulu.salesshipping.shipping.load', id)
        };

    return {
        view: true,

        layout: {
            sidebar: {
                width: 'fixed',
                cssClasses: 'sidebar-padding-50'
            }
        },

        templates: ['/admin/shipping/template/shipping/list'],

        initialize: function() {
            this.orderId = null;

            this.render();
            bindCustomEvents.call(this);

            // TODO: all order events must accessible globally
            // therefore first a service must be implemented
            // for handling all toolbar events, before this line can be uncommented
            //OrderHeaderUtil.setToolbar.call(this, this.options.data);
            this.sandbox.emit('sulu.header.set-toolbar', {buttons: {}});

            if (!!this.options.data && !!this.options.data.id && WidgetGroups.exists('shipping-detail')) {
                Sidebar.initForDetail(this.sandbox, this.options.data);
            }
        },

        render: function() {
            this.orderId = this.options.data.id;

            this.sandbox.dom.html(this.$el, this.renderTemplate('/admin/shipping/template/shipping/list'));

            // init list-toolbar and datagrid
            this.sandbox.sulu.initListToolbarAndList.call(this, 'orderShippingFields', '/admin/api/shippings/fields?context=order',
                {
                    el: this.$find('#list-toolbar-container'),
                    instanceName: 'shippings',
                    inHeader: true,
                    template: getListToolbarTemplate.call(this)
                },
                {
                    el: this.sandbox.dom.find('#shippings-list', this.$el),
                    url: '/admin/api/shippings?flat=true&orderId=' + this.orderId,
                    searchInstanceName: 'shippings',
                    searchFields: ['number', 'account', 'contact'],
                    resultKey: 'shippings',
                    actionCallback: datagridAction.bind(this)
                }
            );
        }
    };
});
