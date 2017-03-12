/*jslint browser: true, plusplus: true */
/*global $, console, jQuery*/
var recipes = {};
$(function () {
  "use strict";
  // Sparar typen av sortering i HTML5 Web Storage
  function saveSortByValue(value) {
    try {
      localStorage.setItem("sortBy", value);
    } catch (ex) {
      console.error(ex.message);
    }
  }

  function sortByNameDescending() {
    $("#titleSort .icon").text('ì '); // Ändra ikonen till fallande "v"
    $("#titleSort .icon").removeClass('inactive');
    $('#dateCreatedSort, #dateUpdatedSort').children('.icon').addClass('inactive');

    $('#recipes tbody tr').datasort({
      sortElement: 'td',
      reverse: true
    });

    saveSortByValue("name descending");
  }

  function sortByNameAscending() {
    $('#titleSort .icon').text('í '); // Ändra ikonen till stigande "^"
    $("#titleSort .icon").removeClass('inactive');
    $('#dateCreatedSort, #dateUpdatedSort').children('.icon').addClass('inactive');

    $('#recipes tbody tr').datasort({
      sortElement: 'td',
      reverse: false
    });

    saveSortByValue("name ascending");
  }

  function sortByDateCreatedDescending() {
    $("#dateCreatedSort .icon").text('ì '); // Ändra ikonen till fallande "v"
    $("#dateCreatedSort .icon").removeClass('inactive');
    $('#titleSort, #dateUpdatedSort').children('.icon').addClass('inactive');

    $('#recipes tbody tr').datasort({
      sortElement: 'td',
      dataType: 'alpha',
      sortAttr: 'data-date-created',
      reverse: true
    });

    saveSortByValue("date created descending");
  }

  function sortByDateCreatedAscending() {
    $("#dateCreatedSort .icon").text('í '); // Ändra ikonen till stigande "^"
    $("#dateCreatedSort .icon").removeClass('inactive');
    $('#titleSort, #dateUpdatedSort').children('.icon').addClass('inactive');

    $('#recipes tbody tr').datasort({
      sortElement: 'td',
      dataType: 'alpha',
      sortAttr: 'data-date-created',
      reverse: false
    });

    saveSortByValue("date created ascending");
  }

  function sortByDateUpdatedDescending() {
    $("#dateUpdatedSort .icon").text('ì '); // Ändra ikonen till fallande "v"
    $("#dateUpdatedSort .icon").removeClass('inactive');
    $('#titleSort, #dateCreatedSort').children('.icon').addClass('inactive');

    $('#recipes tbody tr').datasort({
      sortElement: 'td',
      sortAttr: 'data-date-updated',
      reverse: true
    });

    saveSortByValue("date updated descending");
  }

  function sortByDateUpdatedAscending() {
    $("#dateUpdatedSort .icon").text('í ');// Ändra ikonen till stigande "^"
    $("#dateUpdatedSort .icon").removeClass('inactive');
    $('#titleSort, #dateCreatedSort').children('.icon').addClass('inactive');

    $('#recipes tbody tr').datasort({
      sortElement: 'td',
      sortAttr: 'data-date-updated',
      reverse: false
    });

    saveSortByValue("date updated ascending");
  }

  /**------------------------------
                Sortera receptlistan
        -------------------------------*/
  // När man klickar på "sortera" - visa olika sorteringsmetoder
  $('#tools').on('click', '#sort a', function () {
    $('#titleSort, #dateCreatedSort, #dateUpdatedSort').slideToggle("fast");
  });

  // När man väljer att sortera efter namn
  $('#sortingMethod').on('click', '#titleSort a', function () {
    // Om sorterat efter stigande "^" -> sortera efter fallande
    if ($(this).parent().children('.icon').text() === 'í ') {
      sortByNameDescending();
      // eller tvärtom
    } else {
      sortByNameAscending();
    }

    recipes.updateIndex();
  });

  // När man väljer att sortera efter datum skapat
  $('#sortingMethod').on('click', '#dateCreatedSort a', function () {
    // Visa tooltip innehållandes "data-date-created" på varje recept
    $('.recipeTitle').each(function () {
      var dateCreated = $(this).attr('data-date-created');
      $(this).attr('title', 'Skapades: ' + dateCreated);
    });

    // Om sorterat efter stigande ^ -> sortera efter fallande
    if ($(this).parent().children('.icon').text() === 'í ') {
      sortByDateCreatedDescending();
      // Om sorterat efter fallande -> sortera efter stigande ^
    } else {
      sortByDateCreatedAscending();
    }

    recipes.updateIndex();
  });

  // När man väljer att sortera efter datum uppdaterat
  $('#sortingMethod').on('click', '#dateUpdatedSort a', function () {
    // Visa tooltip innehållandes "data-date-updated" på varje recept
    $('.recipeTitle').each(function () {
      var dateUpdated = $(this).attr('data-date-updated');
      $(this).attr('title', 'Senast uppdaterad: ' + dateUpdated);
    });

    // Om sorterat efter stigande ^ -> sortera efter fallande
    if ($(this).parent().children('.icon').text() === 'í ') {
      sortByDateUpdatedDescending();
      // Om sorterat efter fallande -> sortera efter stigande ^
    } else {
      sortByDateUpdatedAscending();
    }

    recipes.updateIndex();
  });


  /**
   *  Söker i databasen efter recept mha jquery.searchDatabase.js, döljer de recept på sidan som inte matchas
   *  @param e - sökparameter
   *
   */
  function searchDb(e) {
    /** Ta reda på sökparametrar */
    var searchTitle = false,
      searchTags = false,
      searchIngredients = false;

    if ($('#searchTitle').is(":checked")) {
      searchTitle = true;
    }
    if ($('#searchTags').is(":checked")) {
      searchTags = true;
    }
    if ($('#searchIngredients').is(":checked")) {
      searchIngredients = true;
    }

    // Sök endast om någon parameter har angetts
    if (searchIngredients || searchTags || searchTitle) {
      /** Utför sökning */
      e
        .searchDatabase({
          phpFile: './assets/searchDatabase.php',
          matchElements: '.recipeTitle a',
          resultElements: '#recipes tr', // orig: #recipes tr alt: .recipeTitle a
          matchTitle: searchTitle,
          matchTags: searchTags,
          matchIngredients: searchIngredients
        });

      // annars visa alla recept och ta bort felmeddelande
    } else {
      $('#recipes tr').each(function () {
        $(this).show();
      });

      $('#recipes').find('span.error').remove();
    }
  }

  recipes.updateIndex = function () {
    var currentSortingMethod = localStorage.getItem("sortBy") || "name ascending";
    // Ta bort gammalt index
    $('.indexCharacter').remove();

    // Indexera på nytt
    if (/date created/.test(currentSortingMethod)) {
      $(".recipeTitle").indexify({
        date: true,
        indexTarget: 'data-date-created'
      });
    } else if (/date updated/.test(currentSortingMethod)) {
      $(".recipeTitle").indexify({
        date: true,
        indexTarget: 'data-date-updated'
      });
    } else {
      $(".recipeTitle a").indexify({
        prependParent: true
      });
    }
  };


  /**----------------------------------
                Sök och filtrering
        ------------------------------------*/
  // När man skriver i sökboxen
  $('#tools').on('keyup', '#searchBox', function () {
    searchDb($(this));
  });

  // Om sökförfågan har kommit via GET
  if ($('#searchBox').val() !== 0) {
    searchDb($('#searchBox'));
  }

  // Uppdatera sökningen om man klickar i sökalternativ
  $('#searchTitle').change(function () {
    searchDb($('#searchBox'));
  });
  $('#searchTags').change(function () {
    searchDb($('#searchBox'));
  });
  $('#searchIngredients').change(function () {
    searchDb($('#searchBox'));
  });

  /**---------------------------------------
                Taggmoln
        ----------------------------------------*/
  $('#tagCloud').on('click', function () {
    // Navigera till tagCloud.html
    window.location.href = 'tagCloud';
  });

  /**---------------------------------------
                Diverse
        ----------------------------------------*/
  // Initiera sortering utifrån eventuell tidigare lagrad preferens
  var sortingMethod = localStorage.getItem("sortBy") || "name ascending";
  switch (sortingMethod) {
    case "name descending":
      sortByNameDescending();
      break;
    case "date created ascending":
      sortByDateCreatedAscending();
      break;
    case "date created descending":
      sortByDateCreatedDescending();
      break;
    case "date updated ascending":
      sortByDateUpdatedAscending();
      break;
    case "date updated descending":
      sortByDateUpdatedDescending();
      break;
    default:
      sortByNameAscending();
      break;
  }

  // Fokusera på sökrutan från början
  $('#searchBox').focus();
});

/**---------------------------------------
    Hjälpfunktioner för att behålla bredd
    i listan
----------------------------------------*/
jQuery.fn.visible = function () {
  "use strict";
  return this.css('visibility', 'visible');
};

jQuery.fn.invisible = function () {
  "use strict";
  return this.css('visibility', 'hidden');
};

jQuery.fn.visibilityToggle = function () {
  "use strict";
  return this.css('visibility', function (i, visibility) {
    return (visibility === 'visible') ? 'hidden' : 'visible';
  });
};

