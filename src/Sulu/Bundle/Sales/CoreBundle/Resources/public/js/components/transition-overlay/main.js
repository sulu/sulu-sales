/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

/**
 * @class transition-overlay@sulusalescore
 * @constructor
 *
 * @param {Object} [options] Configuration object
 * @param {String} [options.selectData] data that is shown in select TODO:
 */
define([
    'text!sulusalescore/components/transition-overlay/transition-overlay.html'
], function(Template) {

    'use strict';

    var defaults = {
            customerData: [],
            transitionData: [],
            itemUrl: null
        },

        selectors = {
            overlayContainerClass: 'transition-overlay-container',
            transitionSelect: '.js-transition-select',
            customerSelect: '.js-customer-select'
        },

        eventNamespace = 'sulu.transition-overlay.',

        /**
         * Raised when the instance is initialized.
         *
         * @event sulu.transition-overlay.[instanceName].initialized
         */
        EVENT_INITIALIZED = function() {
            return getEventName.call(this, 'initialized');
        },

        /**
         * Returns event name.
         *
         * @param suffix
         *
         * @returns {String}
         */
        getEventName = function(suffix) {
            var eventName = eventNamespace;
            if (!!this.options.instanceName) {
                eventName += '.' + this.options.instanceName;
            }

            return eventName + '.' + suffix;
        },

        /**
         * bind custom events
         */
        bindCustomEvents = function() {

        },

        bindDomEvents = function() {

        },

        /**
         * Renders the transition overlay.
         *
         * @param {Object} data
         */
        initOverlay = function(data) {
            var $overlay, overlayContent, templateData;

            //// prevent multiple initialization of the overlay
            //this.sandbox.stop(this.sandbox.dom.find('.'+selectors.overlayContainerClass, this.$el));
            //this.sandbox.dom.remove(this.sandbox.dom.find('.'+selectors.overlayContainerClass, this.$el));

            $overlay = this.sandbox.dom.createElement('<div class="' + selectors.overlayContainerClass + '"></div>');
            this.sandbox.dom.append(this.$el, $overlay);

            templateData = {
                data: data
            };

            overlayContent = this.sandbox.util.template(Template, templateData);

            // create overlay with data
            this.sandbox.start([
                {
                    name: 'overlay@husky',
                    options: {
                        el: $overlay,
                        container: this.$el,
                        displayHeader: false,
                        openOnStart: true,
                        removeOnClose: true,
                        cssClass: 'transition-overlay-container',
                        instanceName: 'transition',
                        data: overlayContent,
                        skin: 'wide'
                        //,
                        //okCallback:
                    }
                }
            ]);

            this.sandbox.once('husky.overlay.transition.opened', initOverlayComponents.bind(this));
        },

        /**
         * Parses an object into an array of items to be displayed in a select.
         *
         * @param {Object} data
         *
         * @returns {Array}
         */
        parseObjectDataForSelect = function(data) {
            var result = [];

            for (var i in data) {
                result.push({
                    id: i,
                    name: this.sandbox.translate(data[i])
                });
            }

            return result;
        },

        /**
         * Initializes all the components which are displayed in the overlay.
         */
        initOverlayComponents = function() {
            var transitionData = parseObjectDataForSelect.call(this, this.options.transitionData);

            this.sandbox.start([
                {
                    name: 'select@husky',
                    options: {
                        el: selectors.transitionSelect,
                        data: transitionData,
                        style: 'action big',
                        preselectedElements: [transitionData[0]]
                    }

                }
            ]);

            this.sandbox.start([
                {
                    name: 'select@husky',
                    options: {
                        el: selectors.customerSelect,
                        data: ,
                    }

                }
            ]);
        };

    return {
        initialize: function() {
            this.options = this.sandbox.util.extend({}, defaults, this.options);

            bindCustomEvents.call(this);
            bindDomEvents.call(this);

            this.render();

            this.sandbox.emit(EVENT_INITIALIZED.call(this));
        },

        render: function() {
            initOverlay.call(this);
        }
    };
});
