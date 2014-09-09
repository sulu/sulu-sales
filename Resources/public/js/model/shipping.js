/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

define([
    'mvc/relationalmodel',
    'mvc/hasone',
    'sulusalesorder/model/order'
], function(RelationalModel, HasOne, Order) {

    'use strict';
    
    return RelationalModel({
        urlRoot: '/admin/api/shippings',
        defaults: function() {
            return {
                id: null,
                number: ''

            };
        }, relations: [
            {
                type: HasOne,
                key: 'order',
                relatedModel: Order
            }
        ]
    });
});
