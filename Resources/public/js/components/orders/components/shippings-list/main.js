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
    'widget-groups'
], function(AppConfig, Sidebar, OrderStatus, WidgetGroups) {

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
                    id: 'delete',
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
                    items: [
                        {
                            type: 'columnOptions'
                        }
                    ]
                }
            ];
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
                    searchFields: ['fullName'],
                    resultKey: 'shippings',
                    viewOptions: {
                        table: {
                            icons: [
                                {
                                    icon: 'pencil',
                                    column: 'number',
                                    align: 'left',
                                    callback: function(id) {
                                        this.sandbox.emit('sulu.salesshipping.shipping.load', id, this.orderId);
                                    }.bind(this)
                                }
                            ],
                            highlightSelected: true,
                            fullWidth: false
                        }
                    }
                }
            );
        }
    };
});
