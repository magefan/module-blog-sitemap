/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

define([
    'jquery'
], function ($) {
    'use strict';

    return function (data, element) {

        $(element).on('save', function () {
            if ($(this).valid()) {
                $('body').trigger('processStart');
            }
        });
    };
});
