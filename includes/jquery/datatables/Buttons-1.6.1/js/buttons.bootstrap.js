/*******************************************************************************
 * -------------------------------------------------------+
 * | PHP-Fusion Content Management System
 * | Copyright (C) PHP-Fusion Inc
 * | https://www.phpfusion.com/
 * +--------------------------------------------------------+
 * | Filename:
 * | Author:
 * +--------------------------------------------------------+
 * | This program is released as free software under the
 * | Affero GPL license. You can redistribute it and/or
 * | modify it under the terms of this license which you
 * | can read by viewing the included agpl.txt or online
 * | at www.gnu.org/licenses/agpl.html. Removal of this
 * | copyright header is strictly prohibited without
 * | written permission from the original author(s).
 * +--------------------------------------------------------
 ******************************************************************************/

/*! Bootstrap integration for DataTables' Buttons
 * ©2016 SpryMedia Ltd - datatables.net/license
 */

(function (factory) {
    if (typeof define === 'function' && define.amd) {
        // AMD
        define(['jquery', 'datatables.net-bs', 'datatables.net-buttons'], function ($) {
            return factory($, window, document);
        });
    } else if (typeof exports === 'object') {
        // CommonJS
        module.exports = function (root, $) {
            if (!root) {
                root = window;
            }

            if (!$ || !$.fn.dataTable) {
                $ = require('datatables.net-bs')(root, $).$;
            }

            if (!$.fn.dataTable.Buttons) {
                require('datatables.net-buttons')(root, $);
            }

            return factory($, root, root.document);
        };
    } else {
        // Browser
        factory(jQuery, window, document);
    }
}(function ($, window, document, undefined) {
    'use strict';
    var DataTable = $.fn.dataTable;


    $.extend(true, DataTable.Buttons.defaults, {
        dom: {
            container: {
                className: 'dt-buttons btn-group'
            },
            button: {
                className: 'btn btn-default'
            },
            collection: {
                tag: 'ul',
                className: 'dropdown-menu',
                button: {
                    tag: 'li',
                    className: 'dt-button',
                    active: 'active',
                    disabled: 'disabled'
                },
                buttonLiner: {
                    tag: 'a',
                    className: ''
                }
            }
        }
    });

    DataTable.ext.buttons.collection.text = function (dt) {
        return dt.i18n('buttons.collection', 'Collection <span class="caret"/>');
    };


    return DataTable.Buttons;
}));
