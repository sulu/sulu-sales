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
        sulusalescore: '../../sulusalescore/js',
        'type/editableDataRow': '../../sulusalescore/js/components/editable-data-row/editable-data-row-type',
        'type/itemTable': '../../sulusalescore/js/components/item-table/item-table-type',
        'extensions/sulu-buttons-salescore': '../../sulusalescore/js/extensions/sulu-buttons'
    }
});

define([
    'extensions/sulu-buttons-salescore'
], function(SalesButtons) {

    return {
        name: "SuluSalesCoreBundle",

        initialize: function(app) {

            'use strict';

            var sandbox = app.sandbox;

            sandbox.sulu.buttons.push(SalesButtons.getButtons());

            app.components.addSource('sulusalescore', '/bundles/sulusalescore/js/components');

        }
    }
});
