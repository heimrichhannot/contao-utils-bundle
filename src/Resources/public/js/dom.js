(function($) {

    $.extend(UtilsBundle, {
        getTextWithoutChildren: function(element, notrim) {
            var result = element.clone().children().remove().end().text();

            if (typeof notrim !== 'undefined' && notrim === true)
                return result;
            else
                return result.trim();
        }
    });

}(jQuery));
