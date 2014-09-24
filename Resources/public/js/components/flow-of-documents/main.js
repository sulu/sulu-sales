/*
 * This file is part of the Sulu CMS.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

/**
 * @class flow-of-documents@sulusalescore
 * @constructor
 *
 * @param {Object} [options] Configuration object
 * @param {String} [options.instanceName] instance name of the component and its subcomponents
 * @param {String} [options.header] translation key for the header on top of the table
 * @param {Array} [options.columnDefinition] defines the data displayed in the columns
 * @param {String} [options.columnDefinition.property] used property of the data
 * @param {String} [options.columnDefinition.type] type of display [icon, number, date]
 * @param {String} [options.columnDefinition.prefix] prefix for number type
 * @param {String} [options.columnDefinition.prefixProperty] prefix for the number type which displays the type as text
 *
 */
define([], function() {

    'use strict';

    var defaults = {
            instanceName: 'undefined',
            header: 'sales.core.flow-of-documents',
            columnDefinition: [
                {
                    property: 'type',
                    type: 'icon'
                },
                {
                    property: 'number',
                    type: 'number',
                    prefix: '#',
                    prefixProperty: 'type'
                },
                {
                    property: 'date',
                    type: 'date'
                }
            ]
        },

        templates = {
            header: function(title) {
                return '<p class="' + constants.titleClass + ' m-left-10">' + title + '</p>';
            },
            table: function() {
                return '<table class="' + constants.tableClass + '"></table>';
            },
            row: function(id, route) {
                return '<tr data-id="' + id + '" data-route="' + route + '" class="pointer"></tr>';
            }
        },

        constants = {
            titleClass: 'sidebar-table-title',
            tableClass: 'sidebar-table'
        },

        eventNamespace = 'sulu.flow-of-documents.',

        /**
         * raised when the instance is initialized
         * @event sulu.editable-data-row.[instanceName].initialized
         */
        EVENT_INITIALIZED = function() {
            return eventNamespace + this.options.instanceName + '.initialized';
        },

        /**
         * raised when a row is clicked
         * @event sulu.editable-data-row.[instanceName].initialized
         */
        EVENT_CLICKED_ROW = function() {
            return eventNamespace + this.options.instanceName + '.row.clicked';
        },

        /**
         * Renders header for table
         */
        renderHeader = function() {
            var title = this.sandbox.translate(this.options.header),
                $header = this.sandbox.dom.createElement(templates.header.call(this, title));
            this.sandbox.dom.append(this.$el, $header);
        },

        /**
         * Renders table
         */
        renderTable = function() {
            this.$table = this.sandbox.dom.createElement(templates.table.call(this));
            renderRows.call(this);
            this.sandbox.dom.append(this.$el, this.$table);
        },

        renderRows = function() {
            this.sandbox.util.foreach(this.options.data, function(data) {
                renderRow.call(this, data);
            }.bind(this));
        },

        renderRow = function(data) {
            var $row = this.sandbox.dom.createElement(templates.row.call(this, data.id, data.route));
            this.sandbox.util.foreach(this.options.columnDefinition, function(definition) {
                renderCell.call(this, data, $row, definition);
            }.bind(this));
            this.sandbox.dom.append(this.$table, $row);
        },

        /**
         * Renders content of a cell
         * @param data
         * @param $row
         * @param definition
         */
        renderCell = function(data, $row, definition) {
            var value,
                cssClass,
                prefix = '',
                $td;

            // TODO refactor when more abstraction is needed and more time available

            switch (definition.type) {
                case 'icon':
                    value = data[definition.property];
                    cssClass = getCssClassForValue.call(this, value);
                    $td = this.sandbox.dom.createElement('<td class="icon-cell"><span class="fa ' + cssClass + ' icon"></span></td>');
                    break;
                case 'number':
                    if (!!data[definition.prefixProperty]) {
                        prefix = getPrefixForType.call(this, data[definition.prefixProperty]) + ' ';
                    }
                    if (!!definition.prefix) {
                        prefix += definition.prefix;
                    }
                    value = data[definition.property];
                    $td = this.sandbox.dom.createElement('<td>' + prefix + value + '</td>');
                    break;
                case 'date':
                    // format date and strip time
                    value = (this.sandbox.date.format(data[definition.property])).split(' ')[0];
                    $td = this.sandbox.dom.createElement('<td>' + value + '</td>');
                    break;
                default:
                    value = '';
                    this.sandbox.logger.error('flow-of-documents: Undefined row type!');
                    break;
            }

            this.sandbox.dom.append($row, $td);
        },

        /**
         * Returns a translated key for a type
         * @param type
         * @returns {String}
         */
        getPrefixForType = function(type) {
            switch (type) {
                case 'order':
                    return this.sandbox.translate('salescore.order');
                case 'shipping':
                    return this.sandbox.translate('salescore.shipping');
                case 'invoice':
                    return this.sandbox.translate('salescore.invoice');
                default:
                    this.sandbox.logger.warn('flow-of-documents: No prefix for type found!');
                    return '';
            }
        },

        bindDomEvents = function() {
            this.sandbox.dom.on(this.$el, 'click', function(event) {
                var $tr = this.sandbox.dom.createElement(event.currentTarget),
                    id = this.sandbox.dom.data($tr, 'id'),
                    route = this.sandbox.dom.data($tr, 'route');

                this.sandbox.emit(EVENT_CLICKED_ROW.call(this),{id: id, route: route});

            }.bind(this), 'tr');
        },

        /**
         * Returns css icon class for a type
         * @param type
         * @returns {String}
         */
        getCssClassForValue = function(type) {
            switch (type) {
                case 'order':
                    return 'fa-shopping-cart';
                case 'shipping':
                    return 'fa-truck';
                case 'invoice':
                    return 'fa-money';
                default:
                    this.sandbox.logger.warn('flow-of-documents: No icon-definition found!');
                    return '';
            }
        };

    return {

        initialize: function() {

            this.$table = null;

            // load defaults
            this.options = this.sandbox.util.extend({}, defaults, this.options);

            this.render();
            bindDomEvents.call(this);
            this.sandbox.emit(EVENT_INITIALIZED.call(this));
        },

        render: function() {
            renderHeader.call(this);
            renderTable.call(this);
        }
    };
});
