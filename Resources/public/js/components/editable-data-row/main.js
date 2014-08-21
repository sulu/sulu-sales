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
 * @param {String} [options.overlayTemplate] template for the overlay
 * @param {String} [options.overlayTitle] translation key for the overlay title
 * @param {String} [options.dataFormat] structure which defines blocks with used fields and their delimiter - example below:
 *
 *{
 *   "blocks":[
 *      {
 *         "fields":[
 *               {
 *                  "name":"street",
 *                  "delimiter":" "
 *               },
 *               {
 *                  "name":"number"
 *               }
 *         ],
 *         "delimiter":", "
 *      },
 *      {
 *         "fields":[
 *               {
 *                  "name":"zip",
 *                  "delimiter":""
 *               },
 *               {
 *                  "name":"city"
 *               }
 *         ],
 *         "delimiter":", "
 *      }
 *   ]
 *}
 *
 * @param {String} [options.defaultProperty] property which is used to decide default displayed data
 */
define([], function() {

    'use strict';

    var defaults = {
            instanceName: 'undefined',
            fields: null,
            defaultProperty: null,
            overlayTemplate: null,
            overlayTitle: ''

        },

        constants = {
            rowClass: 'editable-row',
            rowClassSelector: '.editable-row',

            overlayContainerClass: 'edit-data-overlay',
            overlayContainerClassSelector: '.edit-data-overlay'
        },

        templates = {

            rowTemplate: function(value) {
                return ['<span class="block pointer ', constants.rowClass , '">', value, '</span>'].join('');
            },

            rowElementTemplate: function(value) {
                return ['<span>', value, '</span>'].join('');
            }
        },

        eventNamespace = 'sulu.editable-data-row.',

        /**
         * raised when an item is changed
         * @event sulu.editable-data-row.instanceName.initialized
         */
        EVENT_INITIALIZED = function() {
            this.sandbox.emit(eventNamespace + this.options.instanceName + '.initialized');
        },

        /**
         * bind custom events
         */
        bindCustomEvents = function() {

            // triggered when new data is available
            this.sandbox.on('sulu.editable-data-row.' + this.options.instanceName + '.data.update', function(data, preselected) {
                this.selectedData = preselected;
                this.data = data;

                if (!!preselected) {
                    renderRow.call(this, preselected);
                }
            }.bind(this));
        },

        /**
         * bind dom events
         */
        bindDomEvents = function() {

            // click handler to trigger overlay
            this.sandbox.dom.on(this.$el, 'click', function() {
                initOverlay.call(this);
            }.bind(this), constants.rowClassSelector);
        },

        /**
         * Renders the single row with the data according to the fields param or a replacement when no data is given
         */
        renderRow = function(data) {
            var $row,
                $oldRow,
                $block;

            // remove old row when rendering is triggered not for the first time
            $oldRow = this.sandbox.dom.find(constants.rowClassSelector, this.$el);
            this.sandbox.dom.remove($oldRow);

            if (!!data) {
                $row = this.sandbox.dom.createElement(templates.rowTemplate());
                this.sandbox.util.each(this.dataFormat.blocks, function(index, block) {
                    $block = processBlock.call(this, block, data);
                    if (!!$block) {
                        this.sandbox.dom.append($row, $block);
                    }
                }.bind(this));
            }
            this.sandbox.dom.append(this.$el, $row);
        },

        /**
         * Processes a block which contains an array of fields and can have his own delimiter
         * @param block
         * @param data
         * @returns {*}
         */
        processBlock = function(block, data) {
            var $block, $field, addedField = false;
            if (!!block && this.sandbox.util.typeOf(block) === 'object' && block.hasOwnProperty('fields')) {

                $block = this.sandbox.dom.createElement(templates.rowElementTemplate(''));

                this.sandbox.util.each(block.fields, function(index, field) {
                    $field = processField.call(this, field, data);
                    if (!!$field) {
                        this.sandbox.dom.append($block, $field);
                        addedField = true;
                    }
                }.bind(this));

                if (block.hasOwnProperty('delimiter') && !!addedField) {
                    this.sandbox.dom.append($block, block.delimiter);
                }
            }
            return $block;
        },

        /**
         * Processes a field of the structure passed to define the structure of the displayed data
         * @param field
         * @param data
         * @returns {*}
         */
        processField = function(field, data) {
            var $field, fieldText;

            if (!!field && this.sandbox.util.typeOf(field) === 'object' &&
                field.hasOwnProperty('name') && !!data[field.name] || data[field.name] === 0
                ) {
                fieldText = data[field.name];
                if (!!field.hasOwnProperty('delimiter')) {
                    fieldText += field.delimiter;
                }
                $field = this.sandbox.dom.createElement(templates.rowElementTemplate(fieldText));
            }
            return $field;
        },

        /**
         * Inits the overlay with a specific template
         */
        initOverlay = function(){

            var $overlay, overlayContent;

            // stop overlay if its still running
            this.sandbox.stop(this.sandbox.dom.find(this.$el, constants.overlayContainerClassSelector));

            $overlay = this.sandbox.dom.createElement('<div class="'+constants.overlayContainerClass+'"></div>');
            this.sandbox.dom.append(this.$el, $overlay);

            overlayContent = this.sandbox.util.template(this.options.overlayTemplate, this.data);

            this.sandbox.start([
                {
                    name: 'overlay@husky',
                    options: {
                        el: $overlay,
                        title: this.sandbox.translate(this.options.overlayTitle),
                        openOnStart: true,
                        removeOnClose: true,
                        skin: 'wide',
                        instanceName: 'editable-data-overlay',
                        data: overlayContent,
                        okCallback: '',
                        closeCallback: ''
                    }
                }
            ]);
        };

    return {

        initialize: function() {

            // load defaults
            this.options = this.sandbox.util.extend({}, defaults, this.options);

            this.selectedData = this.options.value;
            this.data = null;
            this.dataFormat = this.options.dataFormat;

            if (!this.dataFormat && this.sandbox.util.typeOf(this.dataFormat) !== 'object') {
                this.sandbox.logger.error('Value of this.fields in editable-data-row ' + this.instanceName + ' is no object!');
                return;
            }

            // render only when selectedData is set (e.g. via datamapper)
            if (!!this.selectedData) {
                this.render();
            }

            // event listener
            bindCustomEvents.call(this);
            bindDomEvents.call(this);

            EVENT_INITIALIZED.call(this);

            // TODO

//            3. Klick-Event zum Öffnen des Overlays
//            4. Darstellen des übergebenen Formulars
//            5. Aktuell ausgewählter Eintrag wird in Formular eingetragen
//            6. Beim Wechseln wird Formular aktualisiert
//            7. Formularfelder können überschrieben werden
//            8. Werte in Formularfeldern sind Rückgabewert
//
//            - Daten werden via Event aktualisiert
//            - Warten für Select-Data via Event
//            - Ausnahme wenn kein Event mit Daten geworfen

        },

        render: function() {
            renderRow.call(this, this.selectedData);
        }
    };
});
