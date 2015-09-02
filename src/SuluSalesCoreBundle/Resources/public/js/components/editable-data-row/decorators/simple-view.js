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
 * @param {String} [options.overlayTemplate] template for the overlay
 * @param {String} [options.overlayTitle] translation key for the overlay title
 * @param {String} [options.dataFormat] structure which defines blocks with used fields and their delimiter - example below:
 * @param {String} [options.defaultProperty] property which is used to decide default displayed data
 * @param {String} [options.propertyName] which property is taken from select
 *
 * Templates: The template for the overlay form has to use the editable-data-overlay-form class
 */
define([
    'text!sulusalescore/components/editable-data-row/templates/simple.form.html'
], function(SimpleTemplate) {

    'use strict';

    var defaults = {
            instanceName: 'undefined',
            fields: null,
            defaultProperty: null,
            overlayTemplate: null,
            overlayTitle: 'public.edit-entries',
            selectSelector: '#data-select',
            propertyName: 'name'
        },

        constants = {
            rowClass: 'editable-row',
            rowClassSelector: '.editable-row',
            formSelector: '.editable-data-overlay-form',
            eventNamespace: 'sulu.editable-data-row.simple-view.'
        },

        templates = {
            rowTemplate: function(value, disabled) {
                if (!!disabled) {
                    return ['<span class="block ',
                        constants.rowClass ,
                        ' disabled">',
                        value,
                        '</span>'].join('');
                }
                return ['<span class="block pointer ', constants.rowClass , '">', value, '</span>'].join('');
            }
        },

        /**
         * raised when an item is changed
         * @event sulu.editable-data-row.simple-view.instanceName.initialized
         */
        EVENT_INITIALIZED = function() {
            this.sandbox.emit(constants.eventNamespace + this.options.instanceName + '.initialized');
        },

        /**
         * raised when something is changed - should be used by subcomponents
         * @event sulu.editable-data-row.[instanceName].changed
         * @param data
         */
        CHANGED_EVENT = function(data) {
            this.sandbox.emit(constants.eventNamespace + this.options.instanceName + '.changed', data);
        },

        /**
         * bind custom events
         */
        bindCustomEvents = function() {
            if (!this.context.options.disabled) {
                // trigger init of form when overlay is open
                this.sandbox.on('husky.overlay.' + this.context.options.instanceName + '.opened', function() {

                    // TODO: Handle empty values - not decided yet - issue #131
                    // currently values will not be overwritten when no address is available

                    if (!this.openedDialog) {
                        startForm.call(this);
                    }
                    this.openedDialog = false;
                }.bind(this));

                this.sandbox.on('husky.overlay.' + this.context.options.instanceName + '.initialized', function() {
                    this.openedDialog = false;
                }.bind(this));

                // set data on form from selected address
                this.sandbox.on('husky.select.' + this.options.instanceName + '.select.selected.item', function(id) {
                    var data = this.context.getDataByPropertyAndValue.call(this, 'id', id);
                    setFormData.call(this, data);
                }.bind(this));
            }
        },

        /**
         * @var ids - array of ids to delete
         * @var callback - callback function returns true or false if data got deleted
         */
        showInformationDialog = function() {
            // hide overlay
            this.sandbox.emit('husky.overlay.' + this.context.options.instanceName + '.close');

            // show dialog
            this.sandbox.emit('sulu.overlay.show-warning',
                'salesorder.orders.addressOverlayWarningTitle',
                'salesorder.orders.addressOverlayWarningMessage',
                handleDialogClick.bind(this, false),
                handleDialogClick.bind(this, true)
            );

            this.openedDialog = true;
        },

        /**
         * Handles closing of dialog
         * @param accepted
         */
        handleDialogClick = function(accepted) {
            if (!accepted) {
                this.sandbox.emit('husky.overlay.' + this.context.options.instanceName + '.open');
            } else {
                var data = this.sandbox.form.getData(this.formObject, true),
                    newData = data;
                // merge changed address data with old data
                if (typeof data === 'object') {
                    newData = this.sandbox.util.extend({}, this.context.selectedData, data);
                    data = newData[this.options.propertyName];
                }
                this.context.setSelectedData(data);

                CHANGED_EVENT.call(this, this.context.selectedData);
                renderRow.call(this, this.context.selectedData);
            }
        },

        /**
         * starts form
         */
        startForm = function() {
            var $form = this.sandbox.dom.find(constants.formSelector, this.context.$el);

            this.sandbox.start(this.sandbox.dom.find('.editable-data-overlay-container', this.$el));

            this.formObject = this.sandbox.form.create($form);

            this.formObject.initialized.then(function() {
                if (!!this.context.selectedData) {
                    setFormData.call(this, this.context.selectedData);
                }
            }.bind(this));
        },

        /**
         * sets data on form
         * @param data
         */
        setFormData = function(data) {
            this.sandbox.form.setData(this.formObject, data).fail(function(error) {
                this.sandbox.logger.error("An error occured when setting data!", error);
            }.bind(this));
        },

        openOverlay = function() {
            this.sandbox.emit(
                    'sulu.editable-data-row.' + this.options.instanceName + '.overlay.initialize',
                {
                    overlayTemplate: SimpleTemplate,
                    overlayTitle: this.options.overlayTitle,
                    overlayOkCallback: showInformationDialog.bind(this),
                    overlayCloseCallback: null,
                    overlayData: this.context.data
                }
            );
        },

        /**
         * bind dom events
         */
        bindDomEvents = function() {
            if (!this.context.options.disabled) {
                // click handler to trigger overlay
                this.sandbox.dom.on(this.context.$el, 'click', openOverlay.bind(this), constants.rowClassSelector);

                // TODO remove it, when when replaced by custom select
                // this is temporarily replacement for the custom select
                this.sandbox.dom.on(this.context.$el, 'change', function(event) {
                    var id = this.sandbox.dom.val(event.currentTarget),
                        data = this.context.getDataByPropertyAndValue.call(this, 'id', id);
                    setFormData.call(this, data);
                }.bind(this), this.options.selectSelector);
            }
        },

        /**
         * Renders the single row with the data according to the fields param or
         * a replacement when no data is given
         */
        renderRow = function(data) {
            var $row,
                $oldRow;

            // remove old row when rendering is triggered not for the first time
            $oldRow = this.sandbox.dom.find(constants.rowClassSelector, this.context.$el);
            this.sandbox.dom.remove($oldRow);

            if (!!data) {
                $row = this.sandbox.dom.createElement(
                    templates.rowTemplate(data, this.context.options.disabled)
                );
                this.sandbox.dom.append(this.context.$el, $row);
            }
        };

    return {

        initialize: function(context, options) {

            this.context = context;
            this.sandbox = this.context.sandbox;
            this.options = this.sandbox.util.extend({}, defaults, options);
            this.openedDialog = false; // used to determine if form is accessed via dialog or not

            bindCustomEvents.call(this);
            bindDomEvents.call(this);

            EVENT_INITIALIZED.call(this);
        },

        render: function() {
            renderRow.call(this, this.context.selectedData);
        },

        openOverlay: function() {
            openOverlay.call(this);
        }
    };
});
