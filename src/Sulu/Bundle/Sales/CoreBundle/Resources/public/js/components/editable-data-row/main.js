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
 * @param {String} [options.selectData] data that is shown in select
 * @param {String} [options.view] view name
 * @param {Object} [options.viewOptions] options for the view
 * @param {Object} [options.disabled] disables interaction by default
 * @param {Object} [options.showPlusIcon] shows Plus icon, when value is empty
 */
define([
    'sulusalescore/components/editable-data-row/decorators/address-view',
    'sulusalescore/components/editable-data-row/decorators/simple-view'
], function(AddressView, SimpleView) {

    'use strict';

    var defaults = {
            view: 'address',
            viewOptions: {},
            instanceName: 'undefined',
            disabled: false,
            selectData: null,
            overlayTitle: null,
            showPlusIcon: true
        },

        decorators = {
            address: AddressView,
            simple: SimpleView
        },

        constants = {
            overlayContainerClass: 'edit-data-overlay',
            overlayContainerClassSelector: '.edit-data-overlay',
            rowClassSelector: '.editable-row',
            addClass: 'add-data',

            overlayFormSelector: 'editable-data-overlay-form'
        },

        eventNamespace = 'sulu.editable-data-row.',

        /**
         * Raised when the instance is initialized.
         *
         * @event sulu.editable-data-row.[instanceName].initialized
         */
        EVENT_INITIALIZED = function() {
            return eventNamespace + this.options.instanceName + '.initialized';
        },

        /**
         * Updates data and sets an element.
         *
         * @event sulu.editable-data-row.[instanceName].data.update
         */
        EVENT_DATA_UPDATE = function() {
            return eventNamespace + this.options.instanceName + '.data.update';
        },

        /**
         * Sets value of a data row.
         *
         * @event sulu.editable-data-row.[instanceName].set-value
         * @param value The new value
         */
        EVENT_SET_VALUE = function() {
            return eventNamespace + this.options.instanceName + '.set-value';
        },

        /**
         * Bind custom events.
         */
        bindCustomEvents = function() {
            if (!this.options.disabled) {

                // Sets value.
                this.sandbox.on(EVENT_SET_VALUE.call(this), function(preselected) {
                    this.setSelectedData(preselected);
                    this.overlayView.render();
                }.bind(this));

                // Triggered when new data is available.
                this.sandbox.on(EVENT_DATA_UPDATE.call(this), function(data, preselected) {
                    this.setSelectedData(preselected);

                    this.data = data;
                    this.overlayView.render();
                }.bind(this));

                // Initialize overlay with template, title, callbacks and data.
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

        bindDomEvents = function() {
            this.sandbox.dom.on(this.$el, 'click', function() {
                if (!!this.data) {
                    this.overlayView.openOverlay();
                }
            }.bind(this), '.' + constants.addClass);
        },

        /**
         * Inits the overlay with a specific template.
         */
        initOverlay = function(template, title, okCallback, closeCallback, data) {
            var $overlay, overlayContent, templateData;

            // Prevent multiple initialization of the overlay.
            this.sandbox.stop(this.sandbox.dom.find(constants.overlayContainerClassSelector, this.$el));
            this.sandbox.dom.remove(this.sandbox.dom.find(constants.overlayContainerClassSelector, this.$el));

            $overlay = this.sandbox.dom.createElement('<div class="' + constants.overlayContainerClass + '"></div>');
            this.sandbox.dom.append(this.$el, $overlay);

            // FIXME: because the overlay gets started within the element the validation will also affect the labels in
            // the overlay content.

            templateData = {
                data: data,
                translate: this.sandbox.translate,
                instanceName: this.overlayView.options.instanceName
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
         * Renders the single row with the data according to the fields param or
         * a replacement when no data is given.
         */
        renderPlus = function() {
            var $row;
            if (!this.options.disabled) {
                $row = this.sandbox.dom.createElement(
                        '<i class="fa fa-plus-circle pointer ' + constants.addClass + '"></i>'
                );
                this.sandbox.dom.append(this.$el, $row);
            }
        },

        /**
         * Validates a view.
         *
         * @param view
         */
        isViewValid = function(view) {
            return !!(
                typeof view.initialize === 'function' &&
                typeof view.openOverlay === 'function' &&
                typeof view.render === 'function'
                );
        };

    return {

        initialize: function() {

            // Load defaults.
            this.options = this.sandbox.util.extend({}, defaults, this.options);

            this.selectedData = this.options.value;
            this.data = this.options.selectData;

            // Make a copy of the decorators for each editable-data-row instance.
            // If you directly access the decorators variable the editable-data-row-context
            // in the decorators will be overwritten.
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
            bindDomEvents.call(this);

            if (!!this.selectedData) {
                this.render();
            } else if (!!this.options.showPlusIcon) {
                renderPlus.call(this);
            }

            this.sandbox.emit(EVENT_INITIALIZED.call(this));
        },

        render: function() {
            if (!!this.options.disabled) {
                this.sandbox.dom.addClass(this.$el, 'disabled');
                this.sandbox.dom.addClass(this.$el, 'not-clickable');
            }

            this.overlayView.render();
        },

        setSelectedData: function(data) {
            var plusIcon = this.sandbox.dom.find('.' + constants.addClass, this.$el);
                // check if plus is set
            if (!!data && plusIcon.length !== 0) {
                this.sandbox.dom.remove(plusIcon);
            } else if (!data && plusIcon.length === 0) {
                renderPlus.call(this);
            }

            this.selectedData = data;
            this.$el.data('value', data);
        },

        /**
         * Returns data-element which has a property with a specific value.
         *
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
