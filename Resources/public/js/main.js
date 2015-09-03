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
        sulusalesshipping: '../../sulusalesshipping/js',
        'util/shippingStatus': '../../sulusalesshipping/js/util/shippingStatus'
    }
});

define({

    name: "SuluSalesShippingBundle",

    initialize: function(app) {

        'use strict';

        var sandbox = app.sandbox;

        app.components.addSource('sulusalesshipping', '/bundles/sulusalesshipping/js/components');

        // list all shippings
        sandbox.mvc.routes.push({
            route: 'sales/shippings',
            callback: function() {
                this.html('<div data-aura-component="shippings@sulusalesshipping" data-aura-display="list"/>');
            }
        });

        // show form for creating a new shipping
        sandbox.mvc.routes.push({
            route: 'sales/shippings/add',
            callback: function() {
                this.html(
                    '<div data-aura-component="shippings/components/content@sulusalesshipping" data-aura-display="content" data-aura-content="form" />'
                );
            }
        });

        // show form for editing a shipping
        sandbox.mvc.routes.push({
            route: 'sales/shippings/edit::id/:content',
            callback: function(id, content) {
                this.html(
                    '<div data-aura-component="shippings/components/content@sulusalesshipping" data-aura-display="content" data-aura-content="' + content + '" data-aura-id="' + id + '"/>'
                );
            }
        });

        /** orders */

        // show form for creating a new shipping
        sandbox.mvc.routes.push({
            route: 'sales/orders/edit::id/shippings/add',
            callback: function(id) {
                this.html(
                    '<div data-aura-component="shippings/components/content@sulusalesshipping" data-aura-display="content" data-aura-content="form" data-aura-order-id="' + id + '"/>'
                );
            }
        });
    }
});
