/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

define([], function() {

    'use strict';

    var translations = {
            conversionFailed: 'salescore.conversion-failed'
        },

    /**
     * Set header toolbar based on current order status.
     */
        setHeaderToolbar = function(order) {
            var i, len,
                workflow,
                currentSection = null,
                toolbarItems = {
                    save: {},
                    delete: {
                        options: {
                            callback: function() {
                                this.sandbox.emit('sulu.salesorder.order.delete');
                            }.bind(this),
                            disabled: true
                        }
                    }
                },
                workflowDropdown = {
                    icon: 'hand-o-right',
                    iconSize: 'large',
                    group: 'left',
                    title: 'workflows.title',
                    id: 'workflow',
                    dropdownItems: []
                },
                divider = {
                    divider: true
                };

            // show settings template is order already saved
            if (order.id) {
                if (order.allowDelete) {
                    toolbarItems.delete.options.disabled = false;
                }

                // add workflows provided by api
                for (i = -1, len = order.workflows.length; ++i < len;) {
                    workflow = order.workflows[i];

                    // if new section, add divider
                    if (workflowDropdown.dropdownItems.length === 0) {
                        currentSection = workflow.section;
                    } else if (!!currentSection &&
                        currentSection !== workflow.section) {
                        workflowDropdown.dropdownItems.push(divider);
                        currentSection = workflow.section;
                    }
                    // add workflow item
                    workflowDropdown.dropdownItems.push({
                        title: this.sandbox.translate(workflow.title),
                        callback: createWorkflowCallback.bind(this, workflow),
                        disabled: workflow.disabled
                    });
                }

                // add workflow items
                if (workflowDropdown.dropdownItems.length > 0) {
                    toolbarItems.workflows = {options: workflowDropdown};
                }
            }

            this.sandbox.emit('sulu.header.set-toolbar', {
                buttons: toolbarItems
            });
        },

        /**
         * Creates a callback for a workflow.
         *
         * @param {Object} workflow
         */
        createWorkflowCallback = function(workflow) {
            // if event is defined, call event
            if (workflow.hasOwnProperty('event') && !!workflow.event) {
                var params = workflow.parameters || null;
                this.sandbox.emit(workflow.event, params);
            }
            // else if route, check for unsaved data before routing
            else if (workflow.hasOwnProperty('route') && !!workflow.route) {
                checkForUnsavedData.call(this, function() {
                        this.sandbox.emit('sulu.router.navigate', workflow.route);
                    }.bind(this),
                    showErrorLabel.bind(this, '')
                );
            }
            // otherwise, log error
            else {
                this.sandbox.logger.log('no route or event provided for workflow with title ' + workflow.title);
            }
        },

        /**
         * Confirm an order, checks for unsaved data and shows a warning.
         */
        confirmOrder = function() {
            checkForUnsavedData.call(this, function() {
                    this.sandbox.emit('sulu.salesorder.order.confirm');
                },
                showErrorLabel.bind(this, translations.conversionFailed)
            );
        },

        /**
         * Edit an order, checks for unsaved data and shows a warning.
         */
        editOrder = function() {
            checkForUnsavedData.call(this, function() {
                    this.sandbox.emit('sulu.salesorder.order.edit');
                },
                showErrorLabel.bind(this, translations.conversionFailed)
            );
        },

        /**
         * Shows an error Label.
         *
         * @param {String} translationKey
         */
        showErrorLabel = function(translationKey) {
            this.sandbox.emit('sulu.labels.error.show',
                this.sandbox.translate(translationKey));
        },

        /**
         * Checks for unsaved data. if unsaved, a dialog is shown, else immediately proceed.
         *
         * @param {Function} callback - called when no unsaved data, or warning was confirmed
         * @param {Function} errorCallback - if submission fails
         */
        checkForUnsavedData = function(callback, errorCallback) {
            if (typeof callback !== 'function') {
                return;
            }

            // check if unsaved data
            if (!this.saved) {
                // show dialog
                this.sandbox.emit('sulu.overlay.show-warning',
                    'sulu.overlay.be-careful',
                    'sulu.overlay.save-unsaved-changes-confirm',
                    null,
                    function() {
                        this.submit().then(
                            callback.bind(this),
                            errorCallback.bind(this)
                        );
                    }.bind(this)
                );
            }
            // otherwise proceed
            else {
                callback.call(this);
            }
        },

        /**
         * Bind workflow events.
         */
        bindWorkflowEvents = function() {
            // status change events
            this.sandbox.on('sulu.salesorder.order.edit.clicked', editOrder.bind(this));
            this.sandbox.on('sulu.salesorder.order.confirm.clicked', confirmOrder.bind(this));
        },

        /**
         * Sets header title and breadCrumb according to order and additions.
         *
         * @param {Object} order
         * @param {Object} options
         */
        setHeadline = function(order, options) {
            var title, hasOptions,
                titleAddition = null,
                orderEvent = null;

            title = this.sandbox.translate('salesorder.order');

            // parse options
            hasOptions = typeof options === 'object';
            if (hasOptions && options.hasOwnProperty('titleAddition')) {
                titleAddition = options.titleAddition;
            }

            // set title based on order
            if (!!order && !!order.number) {
                title += ' #' + order.number;
            }
            // title addition
            if (!!titleAddition) {
                title += ' ' + titleAddition;
            }

            this.sandbox.emit('sulu.header.set-title', title);
        };

    return {

        initialize: function() {
            bindWorkflowEvents.call(this);
        },

        /**
         * Sets header data: breadcrumb, headline for an order.
         *
         * @param {Object} order Backbone-Entity
         * @param {Object} options configuration object for options
         * @param {String} [options.titleAddition] adds an extra text to the title
         */
        setHeader: function(order, options) {
            // parse to json
            order = order.toJSON();
            // sets headline and breadcrumb
            setHeadline.call(this, order, options);
        },

        /**
         * Set Header Buttons.
         * Attention: needs this context.
         *
         * @param {Object} order
         */
        setToolbar: function(order) {
            setHeaderToolbar.call(this, order)
        },

        /**
         * Checks for unsaved data. if unsaved, a dialog is shown, else immediately proceed.
         *
         * @param callback - called when no unsaved data, or warning was confirmed
         * @param errorCallback - if submission fails
         */
        checkForUnsavedData: function(callback, errorCallback) {
            checkForUnsavedData.call(this, callback, errorCallback);
        },

        /**
         * Will create the url string for an order.
         *
         * @param {Number} [id] if defined, 'edit:id' will be added to the url string
         * @param {String} [postfix] adds an url postfix
         *
         * @returns {string}
         */
        getUrl: function(id, postfix) {
            var url = 'sales/orders';
            if (!!id) {
                url += '/edit:' + id;
            }
            if (!!postfix) {
                url += '/' + postfix;
            }

            return url;
        },

        /**
         * Enables the save-button
         */
        enableSave: function() {
            this.sandbox.emit('sulu.header.toolbar.item.enable', 'save', false);
        },

        /**
         * Disables the save-button
         */
        disableSave: function() {
            this.sandbox.emit('sulu.header.toolbar.item.disable', 'save', false);
        },

        /**
         * Sets the save-button in loading-state
         */
        loadingSave: function() {
            this.sandbox.emit('sulu.header.toolbar.item.loading', 'save');
        }
    };
});
