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
 * @param {String} [options.view] view name
 * @param {Object} [options.viewOptions] options for the view
 * @param {Object} [options.disabled] disables interaction by default
 */
define(['sulusalescore/components/editable-data-row/decorators/address-view'], function(AddressView) {

    'use strict';

    var defaults = {
            view: 'address',
            viewOptions: {},
            instanceName: 'undefined',
            disabled: false
        },

        decorators = {
            address: AddressView
        },

        constants = {
            overlayContainerClass: 'edit-data-overlay',
            overlayContainerClassSelector: '.edit-data-overlay',

            overlayFormSelector: 'editable-data-overlay-form'
        },

        eventNamespace = 'sulu.editable-data-row.',

        /**
         * raised when the instance is initialized
         * @event sulu.editable-data-row.[instanceName].initialized
         */
        EVENT_INITIALIZED = function() {
            this.sandbox.emit(eventNamespace + this.options.instanceName + '.initialized');
        },

        /**
         * bind custom events
         */
        bindCustomEvents = function() {
            if (!this.options.disabled) {
                // triggered when new data is available
                this.sandbox.on('sulu.editable-data-row.' + this.options.instanceName + '.data.update', function(data, preselected) {
                    if (!!preselected) {
                        this.selectedData = preselected;
                    }

                    this.data = data;
                    this.overlayView.render();
                }.bind(this));

                // initialize overlay with template, title, callbacks and data
                this.sandbox.on('sulu.editable-data-row.' + this.options.instanceName + '.overlay.initialize',
                    function(data) {
                        if (!data.overlayTemplate) {
                            this.sandbox.logger.error('No template for overlay defined!');
                        }

                        if ((typeof data.okCallback === 'function' || !data.okCallback) &&
                            (typeof data.closeCallback === 'function' || !data.closeCallback)) {
                            initOverlay.call(this,
                                data.overlayTemplate,
                                data.overlayTitle,
                                data.overlayOkCallback,
                                data.overlayCloseCallback,
                                data.overlayData
                            );
                        } else {
                            this.sandbox.logger.error('Editable-Data-Row: Invalid callbacks for overlay!');
                        }
                    }.bind(this)
                );
            }
        },

        /**
         * Inits the overlay with a specific template
         */
        initOverlay = function(template, title, okCallback, closeCallback, data) {
            var $overlay, overlayContent, templateData;

            // prevent multiple initialization of the overlay
            this.sandbox.stop(this.sandbox.dom.find(constants.overlayContainerClassSelector, this.$el));

            $overlay = this.sandbox.dom.createElement('<div class="' + constants.overlayContainerClass + '"></div>');
            this.sandbox.dom.append(this.$el, $overlay);

            templateData = {
                data: data,
                translate: this.sandbox.translate
            };

            overlayContent = this.sandbox.util.template(template, templateData);

            this.sandbox.start([
                {
                    name: 'overlay@husky',
                    options: {
                        el: $overlay,
                        title: this.sandbox.translate(title),
                        openOnStart: true,
                        removeOnClose: false,
                        skin: 'wide',
                        instanceName: this.options.instanceName,
                        data: overlayContent,
                        okCallback: !!okCallback ? okCallback.bind(this) : null,
                        closeCallback: !!closeCallback ? closeCallback.bind(this) : null
                    }
                }
            ]);
        },

        /**
         * Validates a view
         * @param view
         */
        isViewValid = function(view) {
            return !!(typeof view.initialize === 'function' &&
                typeof view.render === 'function');
        };

    return {

        initialize: function() {

            // load defaults
            this.options = this.sandbox.util.extend({}, defaults, this.options);

            this.selectedData = this.options.value;
            this.data = null;

            // make a copy of the decorators for each editable-data-row instance
            // if you directly access the decorators variable the editable-data-row-context in the decorators will be overwritten
            this.decorators = this.sandbox.util.extend(true, {}, decorators);
            this.viewId = this.options.view;
            this.overlayView = this.decorators[this.viewId];

            if (!!this.overlayView && !!isViewValid.call(this, this.overlayView)) {
                this.options.viewOptions.instanceName = this.options.instanceName;
                this.overlayView.initialize(this, this.options.viewOptions);
            } else {
                this.sandbox.logger.error("Editable-Data-Row: View is not valid!");
                return;
            }

            bindCustomEvents.call(this);

            if (!!this.selectedData) {
                this.render();
            }

            EVENT_INITIALIZED.call(this);
        },

        render: function() {
            if(!!this.options.disabled){
                this.sandbox.dom.addClass(this.$el, 'disabled');
            }

            this.overlayView.render();
        },

        /**
         * returns data-element which has a property with a specific value
         * @param propertyName
         * @param propertyValue
         */
        getDataByPropertyAndValue: function(propertyName, propertyValue) {
            var data = null;

            this.sandbox.util.each(this.context.data, function(index, el) {
                if (el[propertyName].toString() === propertyValue.toString()) {
                    data = el;
                    return false;
                }
            }.bind(this));

            return data;
        }
    };
});
