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
 * @param {String} [options.noDataSelectedLabel] translation key when no data is available
 * @param {String} [options.fields] structure wich defines blocks with used fields and their delimitor - example below:
 *
 *{
 *   "blocks":[
 *      {
 *         "fields":[
 *            [
 *               {
 *                  "name":"street",
 *                  "delimitor":" "
 *               },
 *               {
 *                  "name":"number"
 *               }
 *            ]
 *         ],
 *         "delimitor":", "
 *      },
 *      {
 *         "fields":[
 *            [
 *               {
 *                  "name":"zip",
 *                  "delimitor":""
 *               },
 *               {
 *                  "name":"city"
 *               }
 *            ]
 *         ],
 *         "delimitor":", "
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
            noDataSelectedLabel: 'salesorder.orders.addAddress',
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
        renderRow = function(data){
            var $row,
                noSelectionTranslation,
                $element;

            if(!!data){

                $row  = this.sandbox.dom.createElement(templates.rowTemplate());
            this.sandbox.util.each(this.fields, function(index, field){
                if(!!field && !!data.hasOwnProperty(field) && data[field] !== ''){
                    $element = this.sandbox.dom.createElement(templates.rowElementTemplate(data[field]+ '&nbsp;'));
                    this.sandbox.dom.append($row, $element);
                }
            }.bind(this));
            } else {
                noSelectionTranslation = this.sandbox.translate(this.options.noDataSelectedLabel);
                $row = this.sandbox.dom.createElement(templates.rowTemplate(noSelectionTranslation));
            }

            this.sandbox.dom.append(this.$el, $row);
        },

        findDataToDisplay = function(){
            var data;

//            if(!!this.defaultProperty && this.)

            return data;
        };


    return {

        initialize: function() {

            // load defaults
            this.options = this.sandbox.util.extend({}, defaults, this.options);

            this.selectedData = this.options.value;
            this.data = null;
            this.fields = this.options.fields.split(',');
            this.defaultProperty = this.options.defaultProperty;

            if(!this.fields && this.sandbox.util.typeOf(this.fields) !== 'array'){
                this.sandbox.logger.error('Value of this.fields in editable-data-row '+this.instanceName+' is no array!');
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
