/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

/**
 * @class editable-data-row@sulusalescore
 * @constructor
 *
 * @param {Object} [options] Configuration object
 * @param {String} [options.instanceName] instance name of the component and its subcomponents
 */
define([], function() {

    'use strict';

    var defaults = {
            instanceName: 'undefined'
        },

        constants = {
        },

        eventNamespace = 'sulu.editable-data-row.',

//        /**
//         * raised when an item is changed
//         * @event sulu.item-table.changed
//         */
//        EVENT_CHANGED = eventNamespace + 'changed',

        /**
         * bind custom events
         */
        bindCustomEvents = function() {

        },

        /**
         * bind dom events
         */
        bindDomEvents = function() {

        };


    return {

        initialize: function() {

            // load defaults
            this.options = this.sandbox.util.extend({}, defaults, this.options);

            // render component
            this.render();

            // event listener
            bindCustomEvents.call(this);
            bindDomEvents.call(this);

            this.sandbox.logger.log("initialized " + this.options.instanceName);
        },

        render: function() {

        }
    };
});
