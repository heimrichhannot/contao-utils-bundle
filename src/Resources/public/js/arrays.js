(function($) {

    $.extend(UtilsBundle, {
        removeFromArray: function(value, array) {
            return $.grep(array, function(currentValue) {
                return currentValue !== value;
            });
        },
    });

}(jQuery));
