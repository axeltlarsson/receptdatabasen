/*
 *	@name searchDatabase
 *	Användning:
 *	sökbox.searchDatabase({ matchTags : false, phpFile : "file.php", resultElements : "#result .recipeTitle" });
 */
/*jslint browser: true, plusplus: true */
/*global jQuery, recipes */
(function ($) {
    "use strict";
    $.fn.searchDatabase = function (options) {
        // Standardinställningar
        var defaults = {
                matchTitle: true,
                matchTags: true,
                matchIngredients: true,
                matchElements: null, // element att matcha titlar mot
                resultElements: null, // element att visa/dölja
                phpFile: null
            },
            // Merga användarinställningar med standardinställningar
            settings = $.extend({}, defaults, options),

            // Skicka ett AJAX-call till PHP-filen och visa resultat
            request = $.ajax({
                // Parametrar för AJAX-requesten
                type: 'POST',
                data: {
                    query: this.val(), // söktermen
                    matchTitle: settings.matchTitle,
                    matchIngredients: settings.matchIngredients,
                    matchTags: settings.matchTags
                },
                url: settings.phpFile,
                dataType: 'json',
                cache: false,

                // Vid lyckad sökning - visa matchade titlar, dölja andra
                success: function (response) {
                    // Dölj alla recept
                    $(settings.resultElements).each(function () {
                        $(this).hide();
                    });
                    // Om inga resultat
                    if ($.isEmptyObject(response)) {

                        // Ta bort eventuella tidigare felmeddelanden
                        $(settings.resultElements).parent().find('span.error').each(function () {
                            $(this).remove();
                        });

                        // Visa felmeddelande
                        $(settings.resultElements).parent().append('<span class="error">Inga recept matchade sökningen.</span>');

                        // Annars - dölj felmeddelande och visa resultat
                    } else {
                        // Dölj felmeddelanden
                        $(settings.resultElements).parent().find('span.error').each(function () {
                            $(this).remove();
                        });

                        // Gå igenom resultat-arrayen
                        $.each(response, function (index, value) {
                            // Visa varje matchad titel
                            $(settings.matchElements + ':contains(' + value + ')').each(function () {
                                $(this).closest(settings.resultElements).show();
                            });
                        });
                    }
                    // "Visa" widener igen [CSS-hack]
                    $("#widener").show().invisible();
                    
                    recipes.updateIndex();
                }
            });
    };
}(jQuery));