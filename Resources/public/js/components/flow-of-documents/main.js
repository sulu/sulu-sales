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
 * @param {Array} [options.columDefinition] defines the data displayed in the columns
 * @param {String} [options.columDefinition.property] used property of the data
 * @param {String} [options.columDefinition.type] type of display [icon, number, date]
 * @param {String} [options.columDefinition.prefix] prefix for number type
 * @param {String} [options.columDefinition.prefixProperty] prefix for the number type which displays the type as text
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
            row: function() {
                return '<tr class="pointer"></tr>';
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
            var $row = this.sandbox.dom.createElement(templates.row.call(this));
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
                    if (typeof data[definition.property] === 'object') {
                        value = data[definition.property].date;
                    } else {
                        value = data[definition.property];
                    }
                    value = (this.sandbox.date.format(new Date(value))).split(' ')[0];
                    $td = this.sandbox.dom.createElement('<td>' + value + '</td>');
                    break;
                default:
                    value = '';
                    this.sandbox.logger.error('flow-of-documents: Undefined row type!');
                    break;
            }

            this.sandbox.dom.append($row, $td);
        },

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

        getCssClassForValue = function(value) {
            switch (value) {
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
        },

        bindCustomEvents = function() {
        },

        bindDomEvents = function() {
        };

    return {

        initialize: function() {

            var $table;

            // load defaults
            this.options = this.sandbox.util.extend({}, defaults, this.options);

            bindCustomEvents.call(this);
            bindDomEvents.call(this);
            this.render();

            this.sandbox.emit(EVENT_INITIALIZED.call(this));
        },

        render: function() {
            renderHeader.call(this);
            renderTable.call(this);
        }
    };
});
