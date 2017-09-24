$(document).ready(function(){
  /**------------------------------
                Valideringsfunktioner
        -------------------------------*/
  // Funktion som kontrollerar om en titel är tillgänglig
  function titleCheck(editMode) {
    var title = $('#title').val();
    // Kolla så att titeln inte är tom
    if (title == "") {	// inte ok
      // ta bort fel
      $('#titleDiv .titleError').remove();
      // visa fel
      $('#titleDiv').append('<div class="titleError"><span class="icon">!</span>Detta fält måste fyllas i.<span class="titleError-arrow-border"></span><span class="titleError-arrow"></span></div>');
      // lägg in ett fel i errorArray
      setError("titleError", "true");
      // bryt ut ur funktionen
      return false;
    } else {	// ok
      // ta bort fel
      $('#titleDiv .titleError').remove();
      // "ta bort" felet ur errorArray
      setError("titleError", "false");
    }
    // Kolla titel mot "testTitle.php"
    if (!editMode) {
      jQuery.ajax({
        type: "POST",
        url: "./assets/testTitle",
        data: 'title='+ title,
        cache: false,
        success: function(response){
          if(response == 1){	// titel finns
            // ta bort fel
            $('#titleDiv .titleError').remove();
            // visa fel
            $('#titleDiv').append('<div class="titleError"><span class="icon">!</span>Receptet existerar redan.<span class="titleError-arrow-border"></span><span class="titleError-arrow"></span></div>');
            // lägg in ett fel i errorArray
            setError("titleError", "true");
          } else {	// titel tillgänglig
            // ta bort fel
            $('#titleDiv .titleError').remove();
            // "ta bort" felet ur errorArray
            setError("titleError", "false");
          }

        }
      });
    }
  }

  // Funktion som kollar att nbrPersons är ifylld
  function nbrPersonsCheck() {
    var nbrPersons = $('#nbrPersons').val();
    if (nbrPersons <= 0) {	// inte ok
      // ta bort fel
      $('#nbrPersonsDiv .nbrError').remove();
      // fokusera detta fält
      $('#nbrPersons').focus();
      // visa fel
      $('#nbrPersonsDiv').append('<div class="nbrError"><span class="icon">!</span>Fyll i ett antal.<span class="nbrError-arrow-border"></span><span class="nbrError-arrow"></span></div>');
      // lägg in ett fel i errorArray
      setError("nbrPersonsError", "true");

    } else { // ok
      // ta bort fel
      $('#nbrPersonsDiv .nbrError').remove();
      // "ta bort" felet ur errorArray
      setError("nbrPersonsError", "false");
    }
  }

  // Funktion för att kolla att instruktioner är ifyllda
  function instructionsCheck() {
    var val = $('#instructions').val();
    if (val == "") { // inte ok
      // ta bort fel
      $('#instructionsDiv .instructionsError').remove();
      // visa fel
      $('#instructions').parent('#instructionsDiv').append('<div class="instructionsError"><span class="icon">!</span>Detta fält måste fyllas i.<span class="instructionsError-arrow-border"></span><span class="instructionsError-arrow"></span></div>');
      // lägg till fel till errorArray
      setError("instructionsError", "true");
    } else {	// ok
      // ta bort fel
      $('#instructionsDiv .instructionsError').remove();
      // "ta bort" felet ur errorArray
      setError("instructionsError", "false");
    }
  }

  // Funktion för att kolla att set-namn är ifyllda
  function setHeadingCheck(heading) {
    if (heading.val() == "") {	// inte ok
      // ta bort fel
      heading.siblings('.setError').remove();
      // visa fel
      heading.siblings('.icon').after('<div class="setError"><span class="icon">!</span>Detta fält måste fyllas i.<span class="setError-arrow-border"></span><span class="setError-arrow"></span></div>');
      // "lägg till" fel i errorArray
      setError("setHeadingError", "true");
    } else { // ok
      // ta bort fel
      heading.siblings('.setError').remove();
      // ta bort fel ur errorArray
      setError("setHeadingError", "false");
    }
  }

  /**------------------------------------
                Funktion som hanterar alla fel
                - aktiverar "spara recept" bara
                om det inte finns några fel alls
                i errorArray
        --------------------------------------*/
  var errorArray = {
    titleError : 'false',
    nbrPersonsError : 'true',
    instructionsError : 'true',
    setHeadingError : 'false'
  };
  function setError(error, type) {
    // Kolla att error existerar
    if (error in errorArray && (type == "false" || type == "true")) {
      errorArray[error] = type;
    }

    // aktivera "spara recept"
    $('#saveRecipe a').attr('id', 'active');
    $('#saveRecipe a').attr('title', '');

    // Kolla statusen på errorArray
    $.each(errorArray, function(index, value) {
      if (value == "true") { // det finns ett fel
        // inaktivera "spara recept"
        $('#saveRecipe a').attr('id', 'inactive');
        $('#saveRecipe a').attr('title', 'Du måste fylla i formen korrekt innan du kan spara.');
      }
    });
  }

  // Funktion som tar bort alla tomma ingredienser och taggar så att det inte sparas tomma strängar
  function removeEmptyInputs() {

    // Hämta alla tomma textinputs
    var emptyTextBoxes = $('#center input:text[value=""]');

    // Radera dem
    emptyTextBoxes.each(function() {
      var removeIcons = $(this).siblings('a.icon');
      $(this).remove();
      removeIcons.remove();
    });
  }


  /**----------------------------------------------------
                Trigga valideringsfunktioner vid rätt tillfällen
        -------------------------------------------------------*/
  // När man skriver in en titel - kolla hela tiden mot databasen (såvida inte vi är i edit mode)
  var recipeId = $('#recipeId').html();
  var editMode = false;
  if (recipeId > 0) {	// redan existerande recept med ett databas id = editMode
    editMode = true;
    // Keyup i titel
    $('body').on('keyup', '#title',  function() {
      titleCheck(true); // kolla ej titel mot databas (den finns ju såklart redan)
    });
    // Change i titel
    $('body').on('change', '#title',  function() {
      titleCheck(false); // kolla mot databas
    });
  } else {	// ett nytt recept - !editMode
    $('body').on('keyup', '#title',  function() {
      titleCheck(false); // vanligt
    });
  }

  // Visa fel för de set-namn som ej är ifyllda
  $('body').on('keyup', '.setHeading',  function() {
    setHeadingCheck($(this));
  });

  $('body').on('blur', '.setHeading',  function() {
    setHeadingCheck($(this));
  });

  // Kolla så att några ingredienser är ifyllda

  // Kolla så att instruktioner är ifyllda
  $('#instructions').keyup(function() {
    instructionsCheck();
  });

  // Kolla så att antalet personer är korrekt inskrivet
  $('#nbrPersons').keyup(nbrPersonsCheck);

  /**-----------------------------------------------
                När man klickar på "Spara recept"
        --------------------------------------------------*/
  var recipeTitle = "";
  $('#saveRecipe a').click(function() {
    // kolla fält igen
    nbrPersonsCheck();
    instructionsCheck();

    // Kolla titeln igen, beroende på editMode
    if (recipeId > 0) {	// redan existerande recept med ett databas id = editMode
      $('body').on('keyup', '#title',  function() {
        titleCheck(true);
      });
    } else {	// ett nytt recept - !editMode
      $('body').on('keyup', '#title',  function() {
        titleCheck(false);
      });
    }

    // om valideringen ovan lyckats:
    if($('#saveRecipe a').attr('id') == "active") {
      //	removeEmptyInputs();	// ta bort tomma ingredienser, taggar



      /*------------------------------
                                Lägg in bilderna i en array
                        --------------------------------*/
      var galleryArray = new Array();
      $('.preview img').each(function() {
        var base64 = $(this).attr('src');	// base64-strängen
        var filePath = $(this).attr('data-file-path');
        var imgCaption = $(this).parent('.imageHolder').parent('.preview').children('.imgcaption').attr('value');
        if (typeof filePath != 'undefined' && filePath != '' && filePath.length) { // om filePath existerar - lägg till i array
          var imageArray = {filePathString : filePath, captionString : imgCaption};
        } else {	// annars kör utan filePath
          var imageArray = {base64String : base64, captionString : imgCaption};
        }

        galleryArray.push(imageArray);
      });

      galleryArray = JSON.stringify(galleryArray);
      //console.log(galleryArray);

      /*--------------------------------
                                Lägg in resten i en array
                        --------------------------------*/
      // Ta först reda på titeln
      var recipe = $('#recipeInput').serializeArray();

      $.each(recipe, function() {
        var name = this.name;  // the name property in the current iteration
        var value = this.value;  // the value property in the current iteration
        if (name == "title") {
          recipeTitle = this.value;
          return false;
        }

      });

      // Gör en array av all input från formen
      var recipe = $('form').serializeArray();

      // Lägg in id:et i arrayen om det finns
      if (recipeId > 0 ) {
        recipe.push({"name" : "id", "value" : recipeId });
      }

      // för varje entry i recipe om editMode
      if (editMode) {
        $.each(recipe, function(key, name) {
          var name = this.name;
          var value = this.value;
          if (name == "setHeading") {	// hitta varje setHeading
            var setHeading = value;
            var setId = $('#ingredientsDiv input:text[value="' + setHeading + '"]').parent().attr('data-set-id');	// ta reda på setId
            // lägg till setId till recipe-arrayen, precis efter varje "setHeading"
            var pos = key + 1;	// pos precis efter "setHeading"
            recipe.splice(pos, 0, {"name" : "setId", "value" : setId}); // lägg till setId precis efter "setHeading
          }
        });
      }
      // Lägg sedan in i en JSON-sträng
      recipe = JSON.stringify(recipe);

      /*----------------------------
                                Ladda upp till servern
                        ----------------------------*/
      uploadToServer(recipe, galleryArray);

    } // slut på "om validering lyckas"

  });

  /**-------------------------------------
                Funktion för att ladda upp till
                servern via ajax och en php-fil
        ---------------------------------------*/
  function uploadToServer(recipe, galleryArray) {
    var formData = new FormData();
    var phpUploadFile = './assets/upload';
    formData.append('recipe', recipe);
    formData.append('gallery', galleryArray);
    // Skicka till servern via ajax och php-script
    $.ajax({
      url: phpUploadFile,
      data: formData,
      processData: false,
      contentType: false,
      type: 'POST',
      xhr: function() {
        myXhr = $.ajaxSettings.xhr();
        if(myXhr.upload) { // check if upload property exists
          myXhr.upload.addEventListener('progress', progressHandler, false);
        }
        return myXhr;
      },
      success: function(response){
        if (response == 1) { // false - ngt gick fel
          alert("Receptet kunde inte sparas korrekt");
          // Visa inputs igen
          $('#tools').fadeIn('slow');
          $('#gallery').fadeIn('slow');
          $('#left').fadeIn('slow');
          $('#previewDiv').fadeIn('slow');
          $('#sidebar').fadeIn('slow');

        } else if (response == 0) {	// true - ok
          // ta bort bilder från disk
          removeImagesFromDisk();
          // redirecta
          url = 'recipe?title=' + encodeURIComponent(recipeTitle);
          location.href=url;

        } else { // ngt blev fuckat for real
          alert("Something fucked up, real hard...");
          // Visa inputs igen
          $('#tools').fadeIn('slow');
          $('#gallery').fadeIn('slow');
          $('#left').fadeIn('slow');
          $('#previewDiv').fadeIn('slow');
          $('#sidebar').fadeIn('slow');
        }
      },
      error: function(jqXHR, msg, ex) {
        console.log(jqXHR);
        console.log(msg);
        console.log(ex);
        alert(ex);
      }
    });
    // Hantera progressbar
    function progressHandler(e) {
      if(e.lengthComputable) {
        // Ta bort uppladningselement
        $('#tools').fadeOut('slow', function() {$(this).hide()});
        $('#gallery').fadeOut('slow', function() {$(this).hide()});
        $('#left').fadeOut('slow', function() {$(this).hide()});
        $('#previewDiv').fadeOut('slow', function() {$(this).hide()});
        $('#sidebar').fadeOut('slow', function() {$(this).hide()});

        // Visa progress
        $('#center').html('<p style="text-align: center">Receptet sparas i databasen...');
        $('#center').append('<div class="progressHolder"><div class="progress"></div>');
        var progressHolderWidth = $('.progressHolder').width();
        $('.progress').width(e.loaded/e.total * progressHolderWidth);
        // för trad. progress: $('progress').attr({value:e.loaded, max:e.total});
      }
    }
  }
  /*-------------------------------
                Ta bort borttagna bilder
                från disk
        --------------------------------*/
  function removeImagesFromDisk() {
    $('.removedFilePath').each(function() {
      var removedFilePath = $(this).attr('data-removed-file-path');
      jQuery.ajax({
        type: "POST",
        url: "./assets/deleteImage",
        data: 'path='+ removedFilePath,
        async: false,
        cache: false,
        success: function(response){
          if(response == 0){	// ok

          } else {	// problem

          }
        }
      });

    });
  }

});
