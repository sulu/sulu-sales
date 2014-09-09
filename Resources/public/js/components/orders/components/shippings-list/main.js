/*
* This file is part of the Sulu CMS.
*
* (c) MASSIVE ART WebServices GmbH
*
* This source file is subject to the MIT license that is bundled
* with this source code in the file LICENSE.
*/

define(function() {

    'use strict';

    var bindCustomEvents = function() {
            // navigate to edit contact
            this.sandbox.on('husky.datagrid.item.click', function(item) {
//                this.sandbox.emit('sulu.sidebar.set-widget', '/admin/widget-groups/contact-info?contact=' + item);
            }, this);

            // delete clicked
            this.sandbox.on('sulu.list-toolbar.delete', function() {
                this.sandbox.emit('husky.datagrid.items.get-selected', function(ids) {
                    this.sandbox.emit('sulu.salesshipping.shipping.delete', ids);
                }.bind(this));
            }, this);

            // add clicked
            this.sandbox.on('sulu.list-toolbar.add', function() {
                this.sandbox.emit('sulu.salesshipping.shipping.new');
            }, this);
        };

    return {
        view: true,

        layout: {
        },

        templates: ['/admin/shipping/template/shipping/list'],

        initialize: function() {
            this.render();
            bindCustomEvents.call(this);
        },

        render: function() {
            var orderId = this.options.data.id;

            this.sandbox.dom.html(this.$el, this.renderTemplate('/admin/shipping/template/shipping/list'));

            // init list-toolbar and datagrid
            this.sandbox.sulu.initListToolbarAndList.call(this, 'shippingsFields', '/admin/api/shippings/fields?context=order',
                {
                    el: this.$find('#list-toolbar-container'),
                    instanceName: 'shippings',
                    inHeader: true,
                    template: 'default'
                },
                {
                    el: this.sandbox.dom.find('#shippings-list', this.$el),
                    url: '/admin/api/shippings?flat=true&orderId=' + orderId,
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
                                        this.sandbox.emit('sulu.salesshipping.shipping.load', id, orderId);
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
