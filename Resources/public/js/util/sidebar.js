/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

define(['app-config'], function(AppConfig) {

    'use strict';

    var bindCustomEvents = function() {
        this.sandbox.on('sulu.flow-of-documents.orders.row.clicked', function(data) {
            this.sandbox.emit('sulu.router.navigate', data.route);
        }.bind(this));
    };

    return {

        init: function(sandbox, data) {

            var link = '/admin/widget-groups/order-detail{?params*}',
                url, uriTemplate;

            this.sandbox = sandbox;

            if (!!data.contact && !!data.account && !!data.status) {
                uriTemplate = this.sandbox.uritemplate.parse(link);
                url = uriTemplate.expand({
                    params: {
                        contact: data.contact.id,
                        account: data.account.id,
                        status: data.status.status,
                        locale: AppConfig.getUser().locale,
                        orderDate: data.orderDate,
                        orderNumber: data.number,
                        orderId: data.id
                    }
                });

                this.sandbox.emit('sulu.sidebar.set-widget', url);
                bindCustomEvents.call(this);
            } else {
                this.sandbox.logger.error('required values for sidebar not present!');
            }
        }
    };
});
