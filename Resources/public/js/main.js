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

        // list all contacts
        sandbox.mvc.routes.push({
            route: 'sales/orders',
            callback: function() {
                console.log("ASDF",'added new route');
                this.html('<div data-aura-component="orders@sulusalesorder" data-aura-display="list"/>');
            }
        });
    }
});
