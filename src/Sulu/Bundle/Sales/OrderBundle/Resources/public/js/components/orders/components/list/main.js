/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

define(['sulusalesorder/util/sidebar'], function(Sidebar) {

    'use strict';

    var constants = {
            datagridInstanceName: 'orders'
        },
        bindCustomEvents = function() {
            // add clicked
            this.sandbox.on('sulu.toolbar.add', function() {
                this.sandbox.emit('sulu.salesorder.order.new');
            }, this);
        },

        // list-toolbar template
        getListToolbarTemplate = function() {
            return this.sandbox.sulu.buttons.get({
                settings: {
                    options: {
                        dropdownItems: [
                            {
                                type: 'columnOptions'
                            }
                        ]
                    }
                }
            });
        },

        datagridAction = function(id) {
            this.sandbox.emit('sulu.salesorder.orders.load', id);
        },

        datagridClick = function(id) {
            this.sandbox.emit('sulu.salesorder.orders.sidebar.load', id);
        };

    return {
        view: true,

        layout: {
            content: {
                width: 'max'
            },
            sidebar: {
                width: 'fixed',
                cssClasses: 'sidebar-padding-50'
            }
        },

        header: {
            title: 'salesorder.orders.title',
            noBack: true,

            toolbar: {
                buttons: {
                    add: {},
                    export: {
                        options: {
                            urlParameter: {
                                flat: true
                            },
                            url: '/admin/api/orders.csv'
                        }
                    }
                }
            }
        },

        templates: ['/admin/order/template/order/list'],

        initialize: function() {
            this.render();
            bindCustomEvents.call(this);
        },

        render: function() {
            this.sandbox.dom.html(this.$el, this.renderTemplate('/admin/order/template/order/list'));

            // init list-toolbar and datagrid
            this.sandbox.sulu.initListToolbarAndList.call(this, 'ordersFields', '/admin/api/orders/fields',
                {
                    el: this.$find('#list-toolbar-container'),
                    instanceName: 'orders',
                    groups: [
                        {
                            id: 1,
                            align: 'left'
                        },
                        {
                            id: 2,
                            align: 'right'
                        }
                    ],
                    template: getListToolbarTemplate.call(this)
                },
                {
                    el: this.sandbox.dom.find('#orders-list', this.$el),
                    url: '/admin/api/orders?flat=true',
                    searchInstanceName: 'orders',
                    searchFields: ['number', 'account', 'contact'],
                    resultKey: 'orders',
                    instanceName: constants.datagridInstanceName,
                    actionCallback: datagridAction.bind(this),
                    clickCallback: datagridClick.bind(this)

                },
                'orders',
                '#orders-list-info'
            );
            Sidebar.initForList(this.sandbox);
        }
    };
});
