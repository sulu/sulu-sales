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
    'mvc/hasmany',
    'mvc/hasone',
//    'sulucontact/model/bankAccount'
], function(RelationalModel, HasMany, HasOne) {

    'use strict';
    
    return RelationalModel({
        urlRoot: '/admin/api/orders',
        defaults: function() {
            return {
                id: null,
                number: ''

            };
        }, relations: [
//            {
//                type: HasMany,
//                key: 'emails',
//                relatedModel: Email
//            },
        ]
    });
});
