/*jslint browser: true, plusplus: true, vars: true */
/*global console, jQuery*/
/*
 *	@name indexify
 *	Används för att ge index åt listor.
 */
(function ($) {
    "use strict";
	$.fn.indexify = function (options) {

		var settings = $.extend({
			indexTarget      : "",       // element/attribut vi skall indexera om inte $(this)
			date             : false,
			indexDepth       : 1,        // hur många tecken indexet består av (igonerars om date = true)
			prependParent    : false     // om vi ska lägga till indexCharacter före parent-elementet eller direkt före $(this)
		}, options);

		var oldIndexCharacter = "";
		
		return this.each(function (index, val) {
			if ($(this).is(':visible')) {
				/*------------------------------------------
					Bestäm vilken indexCharacter som skall
					användas
				--------------------------------------------*/
				var indexCharacter = $(this).html().trim().substring(0, 1);
				var sortingClass = "alphabeticIndex";

				// Om vi har fått ett annat indexTarget än $(this), använd det som indexCharacter
				if (settings.indexTarget !== "") {

		
					if (typeof $(this).attr(settings.indexTarget) !== 'undefined') {
						indexCharacter = $(this).attr(settings.indexTarget).trim().substring(0, settings.indexDepth);
					
						if (settings.date) {
							indexCharacter = $(this).attr(settings.indexTarget).trim().substring(0, 7); // antar format YYYY-MM-DD HH:mm:ss
							var month = indexCharacter.substring(5, 7);
							var monthNames = [ "januari", "februari", "mars", "april", "maj", "juni", "juli", "augusti", "september", "oktober", "november", "december" ];
                            
                            month = monthNames[month - 1];

                            var year = indexCharacter.substring(0, 4);
                            indexCharacter = month + " " + year;

                            sortingClass = "dateIndex";

						}
					} else {
						console.log('Attributet "' + settings.indexTarget + '" existerar inte för ' + $(this).html()
							+ ", indexar alfabetiskt istället.");
					}

					
				}

				/*------------------------------------------
					Lägg till indexCharacter
				--------------------------------------------*/
				if (oldIndexCharacter !== indexCharacter) {
					var indexCharacterHTML = '<div class="indexCharacter ' + sortingClass + '">' + indexCharacter + '</div>';
					
					if (settings.prependParent) {
						$(this).parent().prepend(indexCharacterHTML);
					} else {
						$(this).prepend(indexCharacterHTML);
					}
					
				}
				oldIndexCharacter = indexCharacter;
			
			}
		});
	};
}(jQuery));