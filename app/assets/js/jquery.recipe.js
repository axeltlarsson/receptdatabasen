$(function(){

  /**-----------------------------
                Diverse grejor
  ------------------------------*/
  // Dölj frågetecken hos bilder utan caption
  $('.previewPic:not(:has(figcaption))').addClass('noCaption');

  // Lightbox
  $('.previewPic a').lightBox({maxHeight: 700, maxWidth: 1000});

  // Ersätt saknade bilder med en "missing.png"
  $('img').error(function(){
    $(this).parent().parent('.previewPic').html('<div class="icon">ã</div><span class="missing">Bild saknas.</span>').removeClass('previewPic').addClass('missingPic');
  });

  /**-------------------------------
                Tools
  -------------------------------*/
  // spara titeln
  var     title = $('#title').html();

  $('#deleteRecipe').click(function() {

    // Göm receptet
    $('#recipe_container').fadeOut("fast", function() {
      // Visa konfirmationsdialog
      $('body').prepend('<div id="restoreDeletedRecipe">Vill du verkligen radera receptet?<div id="answer"><a id="ja">Ja</a><a id="nej">Nej</a></div></div>').hide().fadeIn('fast');;
    });
  });

  // Återställ receptet om man ångrar sig genom att trycka nej
  $('body').on('click', '#nej', function() {
    restoreRecipe();
  });

  // Radera receptet om man trycker ja
  $('body').on('click', '#ja', function() {
    deleteRecipe(title);
  });

  // Ändra receptet
  $('#editRecipe').click(function() {
    // redirecta till sidan för att ändra recept
    url = 'newRecipe?title=' + encodeURIComponent(title);
    location.href=url;
  });

  var origNbrOfPersons = "";
  $("#changeNbrOfPersons").on('click', function() {
    $("#resetNbrOfPersons").parent().parent().show();

    origNbrOfPersons = $("#changeNbrOfPersons").text().replace(/[^0-9]/g, '');
    // Spara den ursprungliga ingredienssträngen i en custom tagg
    $('.ingredient li').each(function() {
      var origIngredientString = $(this).html();
      $(this).parent('.ingredient').html('<li data-orig-ingredient-string="' + origIngredientString + '">' + origIngredientString + '</li>');
    });

    $(this).hide();
    $(this).parent().children(".icon").after('<span id="changeNbrOfPersonsInput">För <input name="nbrPersons" value="' + origNbrOfPersons + '" type="number" id="nbrOfPersons"/> <label for="nbrPersons">' + getPersonNoun(origNbrOfPersons) + '</label></span>');
    $("#nbrOfPersons").focus();

  });

  $("#resetNbrOfPersons").on('click', function() {
    $("#changeNbrOfPersons").show();
    $("#changeNbrOfPersonsInput").hide();
    $(this).parent().parent().hide();

    $('.ingredient').each(function() {
      $(this).text($(this).data("original"));
    });
  });

  // Trigga en ändring av ingredienser när antalet personer ändras
  $('body').on('keyup', '#nbrOfPersons', function() {
    changeQuantityOfIngredients();
  });

  /**----------------------------------
                Funktioner
  ------------------------------------*/
  // Funktion för att veta om vi ska använda "person" eller "personer"
  function getPersonNoun(number) {
    return number > 1 ? "personer" : "person";
  }

  // Funktion för att "återställa" receptet
  function restoreRecipe() {
    // Visa receptet igen
    $('#recipe_container').fadeIn('normal');

    // Ta bort dialogrutan
    $("#restoreDeletedRecipe").hide().fadeOut("fast");
  }

  // Funktion för att radera receptet på riktigt
  function deleteRecipe(title) {

    // Visa loading animation
    $('#center').html('<div class="circle"></div><div class="circle1"></div>');

    // Gör AJAX-anrop
    jQuery.ajax({
      type: "POST",
      url: "./assets/deleteRecipe",
      data: 'title='+ title,
      cache: false,
      success: function(response){
        if(response == 1){
          /* det gick bra */
          // redirecta till receptlistan
          location.href = 'recipes';
        }
        else {
          /* det gick dåligt */
          $('#center').html('<div class="error">Det gick inte att radera receptet.</div>');
        }

      }
    });
  }

  // Funktion för att ändra ingrediensernas kvanititet beroende på # personer
  function changeQuantityOfIngredients() {
    // Ta reda på nya # personer
    var newNbrOfPersons = $('#nbrOfPersons').val();

    // Gå igenom alla ingredienser
    // $('.ingredient').each(function(idx, ingredient) {
    $.each($('.ingredient'), function(idx, ingredient) {
      var originalString = $(ingredient).data("original");
      var newString = originalString;

      /*------------------------------------------------
          Ta hand om "½" i olika former
      -------------------------------------------------*/
      // Om ingrediens innehåller "X ½" eller "½"
      var halfRegex = /^½|\d+\s½/.exec(originalString); // matchar först "½" i början sedan "X ½" där X är ett nummer
      if (halfRegex) { // innehåller ngn form av "½"
        // Lägg ihop "X ½" till "X+0.5"
        var nbr = /\d+/.exec(halfRegex); // matchar "X"
        if (nbr) {
          nbr = parseInt(nbr) + 0.5;
          newString = originalString.replace(halfRegex, nbr);
        } else {
          newString = originalString.replace('½', 0.5);
        }

      }

      /*----------------------------------------------
          Omvandla kvantitet
      ------------------------------------------------*/
      // Matchar newString mot regex - bearbetar talet i callbackfunktionen
      var regex = /\d+\.\d*|\d+\,\d*|\d+/g; // matchar tal
      newString = newString.replace(regex, function($0, $1) {

        // Konvertera decimalkomma till decimalpunkt
        newQuantity = $0.replace(',', '.');

        // Beräkna ny kvantitet
        newQuantity = parseFloat(newQuantity) / origNbrOfPersons * newNbrOfPersons;

        // Avrunda fint till 2 decimaler
        return Math.round(newQuantity * 100)/100;
      });

      /*------------------------------------------------
          Uppdatera sidan med de nya siffrorna
      ------------------------------------------------*/
      $(this).html(newString);

      $("label[for='nbrPersons']").text(getPersonNoun(newNbrOfPersons));
    });
  }


});
