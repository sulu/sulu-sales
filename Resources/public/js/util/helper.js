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

    return {
        /**
         * simple function for parsing a date to a format.
         * @param date
         * @param format only dd, mm and yyyy are currently supported
         * @returns {string}
         */
        parseDate: function(date, format) {
            date = date || new Date();
            format = format || 'mm/dd/yyyy';
            var dd = date.getDate(),
                mm = date.getMonth() + 1, //January is 0!
                yyyy = date.getFullYear(),
                dateString = format;

            if (dd < 10) {
                dd = '0' + dd;
            }

            if (mm < 10) {
                mm = '0' + mm;
            }

            // replace format by real values
            dateString = dateString.replace('mm',mm);
            dateString = dateString.replace('dd',dd);
            dateString = dateString.replace('yyyy',yyyy);

            return dateString;
        }
    };
});
