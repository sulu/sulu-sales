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
 */
define(['sulusalescore/components/editable-data-row/decorators/address-view'], function(AddressView) {

    'use strict';

    var defaults = {
            view: 'address',
            viewOptions: {},
            instanceName: 'undefined'
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

                if (!!preselected) {
                    this.selectedData = preselected;
                }

                this.data = data;
                this.view.render();

            }.bind(this));

            this.sandbox.on('sulu.editable-data-row.' + this.options.instanceName + '.overlay.initialize',
                function(data) {

                    if(!data.overlayTemplate){
                        this.sandbox.logger.error('No template for overlay defined!');
                    }

                    if((typeof data.okCallback === 'function' || !data.okCallback) &&
                        (typeof data.closeCallback === 'function' || !data.closeCallback)){
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
        },

        /**
         * Inits the overlay with a specific template
         */
        initOverlay = function(template, title, okCallback, closeCallback, data){
            var $overlay, overlayContent, templateData;

            $overlay = this.sandbox.dom.createElement('<div class="'+constants.overlayContainerClass+'"></div>');
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
                        removeOnClose: true,
                        skin: 'wide',
                        instanceName: this.options.instanceName,
                        data: overlayContent,
                        okCallback: !!okCallback ? okCallback.bind(this) : null,
                        closeCallback: !!closeCallback ? closeCallback.bind(this): null
                    }
                }
            ]);
        },

        /**
         * Validates a view
         * @param view
         */
        isViewValid = function(view) {
            if(typeof view.initialize === 'function' &&
                typeof view.render === 'function') {
                return true;
            }
            return false;
        };

    return {

        initialize: function() {

            // load defaults
            this.options = this.sandbox.util.extend({}, defaults, this.options);

            this.selectedData = this.options.value;
            this.data = null;

            // make a copy of the decorators for each editable-data-row instance
            // if you directly access the decorators variable the datagrid-context in the decorators will be overwritten
            this.decorators = this.sandbox.util.extend(true, {}, decorators);
            this.viewId = this.options.view;
            this.view = this.decorators[this.viewId];

            if(!!this.view && !!isViewValid.call(this, this.view)){
                this.options.viewOptions.instanceName = this.options.instanceName;
                this.view.initialize(this, this.options.viewOptions);
            } else {
                this.sandbox.logger.error("Editable-Data-Row: View is not valid!");
                return;
            }

            // event listener
            bindCustomEvents.call(this);

            if(!!this.selectedData){
                this.render();
            }

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

        render: function(){
            this.view.render();
        }
    };
});
