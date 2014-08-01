/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

require.config({
    paths: {
        sulusalesorder: '../../sulusalesorder/js'
    }
});

define({

    name: "SuluSalesOrderBundle",

    initialize: function(app) {

        'use strict';

        var sandbox = app.sandbox;

        app.components.addSource('sulusalesorder', '/bundles/sulusalesorder/js/components');

        // list all orders
        sandbox.mvc.routes.push({
            route: 'sales/orders',
            callback: function() {
                this.html('<div data-aura-component="orders@sulusalesorder" data-aura-display="list"/>');
            }
        });

        // show form for createing a new order
        sandbox.mvc.routes.push({
            route: 'sales/orders/add',
            callback: function() {
                this.html(
                    '<div data-aura-component="orders/components/content@sulusalesorder" data-aura-display="content" data-aura-content="form" />'
                );
            }
        });

        // show form for editing an order
        sandbox.mvc.routes.push({
            route: 'sales/orders/edit::id/:content',
            callback: function(id, content) {
                this.html(
                    '<div data-aura-component="orders/components/content@sulusalesorder" data-aura-display="content" data-aura-content="' + content + '" data-aura-id="' + id + '"/>'
                );
            }
        });
    }
});
