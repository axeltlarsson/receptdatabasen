var autoImport = (function() {
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
    ingredients: function(data) {
      return { 'Ingredienser': selectionsToArray('li[itemprop=recipeIngredient]', data) };
    },
    nbr_persons: recipeYield.bind(null, 'h3[itemprop=recipeYield]'),
    image: function(data) {
      return $(data).find('a.image-caption-wrap').attr('href');
    },
    tags: selectionsToArray.bind(null, '.tags.tag-label a')
  }

  extractors['www.ica.se'] = {
    title: textFromElem.bind(null, 'h1.recipepage__headline'),
    intro: textFromElem.bind(null, 'p.recipe-preamble'),
    instructions: (data) => paragraphsToMarkdown($(data).find('#recipe-howto ol li')),
    ingredients: function(data) {
      var sets = {};
      $(data).find('.ingredients--dynamic ul').each(function (idx) {
        var setHeading = $(this).prev('strong').text().trim() || 'Ingredienser';
        console.log("setHeading", setHeading);
        var ingredients = [];
        $(this).children('li').each(function (idx) {
          ingredients.push($(this).text().trim());
        });
        sets[setHeading] = ingredients;
      });
      return sets;
    },
    nbr_persons: function(data) {
      return $(data).find('.servings-picker').data('current-portions');
    },
    tags: selectionsToArray.bind(null, '.related-recipe-tags__container a'),
    image: imageFromBackground.bind(null, '.hero__image__background')
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
        var sectionAsMd = `## ${heading}\n${bodyAsMd}\n`;
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
    },
    nbr_persons: recipeYield.bind(null, '.recipe-servings'),
    tags: selectionsToArray.bind(null, '.recipe-tags a'),
    image: imageFromBackground.bind(null, '.featured-image-background')
  }

  function imageFromBackground(selector, data) {
      var urlStr =  $(data).find(selector).css('background-image');
      var url = urlStr.replace(/url\("(.+)"\)/, '$1');
      return url;
  }

  function recipeYield(selector, data) {
      var nbrMaybe = $(data).find(selector).text().match(/\d+/);
      if (nbrMaybe.length) {
        return nbrMaybe[0];
      } else {
        return undefined;
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


  return {
    extractors: extractors
  };
})();

