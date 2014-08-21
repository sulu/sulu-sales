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
 * @param {String} [options.dataFormat] structure wich defines blocks with used fields and their delimitor - example below:
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
            defaultProperty: null
        },

        constants = {
            rowClass: 'editable-row',
            rowClassSelector: 'editable-row'
        },

        templates = {

            rowTemplate: function(value) {
                return ['<span class="block pointer ', constants.rowClass ,'">',value,'</span>'].join('');
            },

            rowElementTemplate: function(value) {
                return ['<span>',value,'</span>'].join('');
            }
        },

        eventNamespace = 'sulu.editable-data-row.',

        /**
         * raised when an item is changed
         * @event sulu.editable-data-row.instanceName.initialized
         */
        EVENT_INITIALIZED = function(){
            this.sandbox.emit(eventNamespace + this.options.instanceName + '.initialized');
        },

        /**
         * bind custom events
         */
        bindCustomEvents = function() {

            this.sandbox.on('sulu.editable-data-row.'+this.options.instanceName+'.data.update', function(data, preselected){
                // TODO update select
            }.bind(this));
        },

        /**
         * bind dom events
         */
        bindDomEvents = function() {

            // click handler to trigger overlay
            this.sandbox.dom.on(this.$el, 'click', function(){
                this.sandbox.logger.warn("clicked editable row!");
            }.bind(this), constants.rowClassSelector);
        },

        /**
         * Renders the single row with the data according to the fields param or a replacement when no data is given
         */
        renderRow = function(data) {
            var $row,
                $block;

            if (!!data) {
                $row = this.sandbox.dom.createElement(templates.rowTemplate());
                this.sandbox.util.each(this.dataFormat.blocks, function(index, block) {
                    $block = processBlock.call(this, block, data);
                    if(!!$block) {
                        this.sandbox.dom.append($row, $block);
                    }
                }.bind(this));
            }
            this.sandbox.dom.append(this.$el, $row);
        },

        /**
         * Processes a block which contains an array of fields and can have his own delimiter
         * @param block
         * @returns {*}
         */
        processBlock = function(block, data) {
            var $block, $field;
            if (!!block && this.sandbox.util.typeOf(block) === 'object' && block.hasOwnProperty('fields')) {

                $block = this.sandbox.dom.createElement(templates.rowElementTemplate(''));

                this.sandbox.util.each(block.fields, function(index, field) {
                    $field = processField.call(this, field, data);
                    if (!!$field) {
                        this.sandbox.dom.append($block, $field);
                    }
                }.bind(this));

                if(block.hasOwnProperty('delimiter')){
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
        processField = function(field, data){
            var $field, fieldText;

            if (!!field && this.sandbox.util.typeOf(field) === 'object' && field.hasOwnProperty('name')) {
                fieldText = data[field.name];
                if (!!field.hasOwnProperty('delimiter')) {
                    fieldText += field.delimiter;
                }
                $field = this.sandbox.dom.createElement(templates.rowElementTemplate(fieldText));
            }
            return $field;
        };


    return {

        initialize: function() {

            // load defaults
            this.options = this.sandbox.util.extend({}, defaults, this.options);

            this.selectedData = this.options.value;
            this.data = null;
            this.dataFormat = this.options.dataFormat;
            this.defaultProperty = this.options.defaultProperty;

            if(!this.dataFormat && this.sandbox.util.typeOf(this.dataFormat) !== 'object'){
                this.sandbox.logger.error('Value of this.fields in editable-data-row '+this.instanceName+' is no object!');
                return;
            }

            // render component
            this.render();

            // event listener
            bindCustomEvents.call(this);
            bindDomEvents.call(this);

            EVENT_INITIALIZED.call(this);

            // TODO

//            1. Laden der Daten via Datamapper
//            2. Darstellen des Einzeilers
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
