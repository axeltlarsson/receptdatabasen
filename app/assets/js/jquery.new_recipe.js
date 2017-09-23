/*jslint browser: true, plusplus: true */
/*global $, console, jQuery*/
$(document).ready(function () {
  "use strict";

  $('nav ul li#new_recipe').hide();
  // Auto resize textareas
  $("textarea").autosize();

  /**----------------------------------------------------------------
                "Lägg till set", event handlers för att ta bort etc
        -----------------------------------------------------------------*/
  function addSet() {
    // Lägger till ett set till ingredientsDiv
    $('#ingredientsDiv').append('<div class="set"><input name="setHeading" class="setHeading" type="text" /><a tabindex="-1" class="icon">ç</a></div>');
    $('.set:last').hide().fadeIn('normal'); // animera ingången
    $('.set').removeAttr('id'); // avmarkera alla set
    $('.set:last').attr('id', 'selectedSet'); // markera det nyligen tillagda
    $('#addIngredient').trigger('click'); // Lägg till en ingrediens
    $('.set:last input:first').focus(); // sätt fokus i det nyligen skapade set:et

    // Event handler - markera set som klickas
    $('.set').each(function () {
      $(this).click(function () {
        // Avmarkera alla set
        $('.set').removeAttr('id');
        // Markera detta set
        $(this).attr('id', 'selectedSet');
      });
    });

    // Event handler - ta bort set som klickas
    $('.set a').each(function () {
      $(this).click(function () {
        $(this).parent('.set').fadeOut('normal', function () {
          $(this).remove();
        });
      });
    });

    // Event handler - temporärt markera set som hovras
    $('.set').each(function () {
      $(this).hover(function () {
        $(this).addClass('hoveredSet');
      }, function () {
        // när hovring är klar
        $(this).removeClass('hoveredSet');
      });
    });
  }

  // Bind event handler
  $('#addSet').click(function () { addSet(); });


  /**----------------------------------------------------------------
                "Lägg till ingrediens", event handlers för att ta bort etc.
        -----------------------------------------------------------------*/
  function addIngredient() {
    // Lägger till i markerat set och binder event handlers
    $('<div class="ingredients"><input name="ingredient" type="text" /><a tabindex="-1" class="icon">ç</a></div>').appendTo('#selectedSet').hide().fadeIn('normal').on('keydown', function (key) {
      // Event handler: om sista ingrediens ej tom, i set och användare trycker 'tab' -> lägg automatiskt till ny ingrediens
      var keyCode = key.keyCode || key.which;
      if (keyCode === 9 && $(this).is(":last-child")) {
        if ($(this).children('input[name="ingredient"]').val() !== "") {
          key.preventDefault();
          addIngredient();
          $("#selectedSet:last input").focus();
        } else {
          // Vi har förmodligen lagt till en ingrediens i onödan - ta bort
          $("#selectedSet .ingredients:last").fadeOut('fast', function () {
            $(this).remove();
          });
        }
      }
    });

    $('#selectedSet:last input').focus(); // sätt fokus i den nyligen skapade ingrediensen

    // Event handler till ingrediens-remove-knapparna (blir egentligen trippla för första ingredienser...)
    $('.ingredients a ').each(function () {
      $(this).click(function () {
        $(this).parent('.ingredients ').fadeOut('normal ', function () {
          $(this).remove();
        });
      });
    });
  }

  // Bind event handler - klick på "Lägg till ingrediens"
  $('#addIngredient').click(function () { addIngredient(); });

  /**----------------------------------------------------------------
                "Lägg till taggar", event handlers för att ta bort etc.
  -----------------------------------------------------------------*/
  function addTag() {
    // Om rubrik inte finnns, lägg till
    if (!$('#tagsDiv .icon').length) {
      $('#tagsDiv').append('<div class="icon">l</div>');
    }

    // Lägg till tag till tagsDiv
    $('<div class="tags"><input name="tag" type="text" /><a tabindex="-1" class="icon">ç</a></div>').appendTo('#tagsDiv');
    $('.tags:last input').focus(); // sätt fokus i den nyligen skapade taggen

    // Event handler till tag-remove-knapparna
    $('.tags a').each(function () {
      $(this).click(function () {
        $(this).parent('.tags').fadeOut('normal', function () {
          $(this).remove();
        });
      });
    });
  }

  // Bind event handler
  $('#addTag').click(function () { addTag(); });


  /**----------------------------------------------------------------
        Auto import recipes from external sites
  -----------------------------------------------------------------*/

  // `extractors` contain one extractor object for each site supported,
  // each extractor object defines functions to retrieve:
  // - title: String
  // - intro: String
  // - instructions: String (md formatted as numbered list of paragraphs)
  // - ingredients: Array[String]
  // - recipe yield (nbr of persons): Int
  // - image: image url
  var extractors = {}
  extractors['receptfavoriter.se'] = {
    title: textFromElem.bind(null, '.header-1[itemprop=name]'),
    intro: textFromElem.bind(null, '.recipe-description .legible'),
    instructions: (data) => paragraphsToMarkdown($(data).find('li[itemprop=itemListElement]')),
    ingredients: selectionsToArray.bind(null, 'li[itemprop=recipeIngredient]'),
    nbr_persons: function(data) {
      var nbrMaybe = $(data).find('h3[itemprop=recipeYield]').text().match(/\d+/);
      if (nbrMaybe.length) {
        return nbrMaybe[0];
      } else {
        return undefined;
      }
    },
    image: function(data) {
      return $(data).find('a.image-caption-wrap').attr('href');
    },
    tags: selectionsToArray.bind(null, '.tags.tag-label a')
  }
  extractors['www.ica.se'] = {
    title: textFromElem.bind(null, 'h1.recipepage__headline'),
    intro: textFromElem.bind(null, 'p.recipe-preamble'),
    instructions: (data) => paragraphsToMarkdown($(data).find('#recipe-howto ol li')),
    ingredients: selectionsToArray.bind(null, 'li span.ingredient'),
    nbr_persons: function(data) {
      return $(data).find('.servings-picker').data('current-portions');
    },
    tags: selectionsToArray.bind(null, '.related-recipe-tags__container a'),
    image: function(data) {
      var urlStr =  $(data).find('.hero__image__background').css('background-image');
      var url = urlStr.replace(/url\("(.+)"\)/, '$1');
      return url;
    }
  }
  extractors['alltommat.se'] = {
    title: function(data) {
      return $(data).find('h1.recipe-title')[0].firstChild.data.trim();
    },
    intro: textFromElem.bind(null, '.article-content-inner > p:nth-child(1)'),
    instructions: function(data) {
      var sections = $(data).find('span[itemprop=recipeInstructions] ol');
      var md = [];
      sections.each(function(idx) {
        var body = $(this).children('li');
        var heading = $(this).prev('h2').text();
        var bodyAsMd = paragraphsToMarkdown(body, data);
        var sectionAsMd = `## ${heading}\n${bodyAsMd}`;
        md.push(sectionAsMd);
      });
      return md.join('\n');
    },
    ingredients: function(data) {
      var sets = {}
      $(data).find('table.ingredients-list').each(function (idx) {
        var heading = $(this).children('caption[itemprop=name]').text().trim() || 'Ingredienser';
        var ingredients = [];
        $(this).children('tbody').children('tr').each(function (idx) {
          var quantity = $(this).children('td').text().trim();
          var ingredient = $(this).children('th[itemprop=recipeIngredient]').text().trim();
          ingredients.push(`${quantity} ${ingredient}`);
        });
        sets[heading] = ingredients;
      });
      return sets;
    }
  }

  function textFromElem(selector, data) {
    return $(data).find(selector).text().trim();
  }

  function selectionsToArray(selector, data) {
    var items = [];
    $(data).find(selector).each(function (idx) {
      var item = $(this).text().trim();
      if (item)
        items.push(item);
    });
    return items;
  }

  function paragraphsToMarkdown(paragraphs) {
    var md = [];
    paragraphs.each(function (idx) {
      var p = $(this).text().trim();
      var markdownP = `${idx + 1}. ${p}`;
      md.push(markdownP);
    });
    return md.join('\n');
  }

  $('#auto-import').on('blur', function (e) {
    var url = e.target.value;
    if (!url)
      return false;

    var site = new URL(url).hostname;
    var extractor = extractors[site];
    if (!extractor) {
      console.error("The site `" + site + "` is not supported");
      return false;
    }

    $.get('/reverse_proxy', { site: url }, function (data) {
      // Remove all src directives to prevent unnecessay requests for external
      // resources, such as images
      var re = /src="(.+?)"/g;
      var data = data.replace(re, '$href=""');

      var title = extractor['title'](data);
      console.log(`title: ${title}`);
      $('#title').val(title);

      var intro = extractor['intro'](data);
      console.log(`intro: ${intro}`);
      $('#intro').val(intro);

      var instructions = extractor['instructions'](data);
      console.log(`instructions:\n${instructions}`);
      $('#instructions').val(instructions);

      // TODO: support "sets"
      var ingredients = extractor['ingredients'](data);
      console.log("ingredients", ingredients);
      $('.ingredients').remove();
      ingredients.forEach(function (ingredient) {
        addIngredient();
        $('input[name=ingredient]:last').val(ingredient);
      });
      $('.ingredients:last').remove();

      var nbrPersons = extractor['nbr_persons'](data);
      console.log(`nbrPersons: ${nbrPersons}`);
      $('#nbrPersons').val(nbrPersons);

      var tags = extractor['tags'](data);
      console.log("tags", tags);
      $('.tags:last').remove();
      tags.forEach(function (tag) {
        addTag();
        $('input[name=tag]:last').val(tag);
      });

      var imageURL = extractor['image'](data);

      // Converts URL pointing to image to a DataURI
      function getDataURI(url, callback) {
        var image = new Image();

        image.onload = function() {
          var canvas = document.createElement('canvas');
          canvas.width = this.naturalWidth;
          canvas.height = this.naturalHeight;

          canvas.getContext('2d').drawImage(this, 0, 0);
          callback(canvas.toDataURL());
        };

        image.src = url;
      }

      getDataURI(`/reverse_proxy?site=${imageURL}`, function(dataURI) {
        var previewImg = `<div class="preview">
        <span class="imageHolder"><img src="${dataURI}"/></span>
        <span class="remover"><a style="display: none" class="icon">ç</a></span>
        <input type="text" class="imgcaption" placeholder="Skriv en bildtext här." />
        </div>`

        $('#previewDiv').append(previewImg);
      });



    });

  });

  /**----------------------------------------------------------------
                Allmänt - ladda in grejor, fokusera etc. när sidan laddats
        -----------------------------------------------------------------*/
  // Lägg till en tag när sidan laddats
  addTag();
  // Lägg till ett set när sidan laddas
  addSet();
  // Fyll i det första set:ets namn
  $('.set:first input[type=text]:first').attr('value', 'Ingredienser');
  // Lägg till tre ingredienser efteråt (en läggs till automatiskt)
  addIngredient();
  addIngredient();
  $('#title').focus();
});
