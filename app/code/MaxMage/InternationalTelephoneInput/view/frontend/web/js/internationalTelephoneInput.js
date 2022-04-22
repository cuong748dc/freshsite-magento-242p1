define([
    'jquery',
    'intlTelInput'
], function ($) {
    var initIntl = function (config, node) {
        console.log(config);
        console.log(node);
        $(node).intlTelInput(config);
    };
    return initIntl;
});
