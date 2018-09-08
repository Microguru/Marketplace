define([
    "jquery",
    "jquery/ui"
], function($) {
    "use strict";
    $.widget('jstest.check', {
        _create: function() {
            var yourData = this.options;
            /*this.options contaion all variables which you pass in it for
             use in your javascript code */
            console.log(this.options);
            // here you can write your javascript code as your requirement
        }
    });
    return $.jstest.check;
});