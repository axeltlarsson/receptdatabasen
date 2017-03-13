/*--------------------------------
        jQuery
----------------------------------*/
$(function(){
  /**--------------------------------
                Variabler
        ----------------------------------*/
  var dropbox = $('#gallery'),
    previewDiv = $('#previewDiv'),
    template = '<div class="preview">'+
    '<span class="imageHolder">'+
    '<img />'+
    '</span>'+
    '<span class="remover">'+
    '<a style="display: none" class="icon">ç</a>'+
    '</span>'+
    '<input type="text" class="imgcaption" placeholder="Skriv en bildtext här." />'+
    '</div>',
    uploadBtn = $('#uploadbtn'),
    defaultUploadBtn = $('#upload'),
    totalFileSize = 0,
    maxAllowedTotalFileSize = 100*1024*1024; // 100 MB

  /**---------------------------------
                Event handlers
        -----------------------------------*/
  // Dragover
  dropbox.on('dragover', function() {
    // Ändra ikonen
    uploadBtn.attr('value', '{');
    return false;
  });

  // Dragleave
  dropbox.on('dragleave', function() {
    // Ändra tillbaka ikonen
    uploadBtn.attr('value', 'A');
    return false;
  });

  // Fånga upp droppade filer
  dropbox.on('drop', function(e) {
    // Förhindra standardbeteende
    e.stopPropagation();
    e.preventDefault();
    uploadBtn.attr('value', 'A');

    // retrieve uploaded files data
    var files = e.originalEvent.dataTransfer.files;
    processFiles(files);

    return false;
  });

  // När man valt filer via file select-dialogen
  uploadBtn.on('click', function(e) {
    // trigger default file upload button
    defaultUploadBtn.click();
  });

  defaultUploadBtn.on('change', function() {
    //retrieve selected uploaded files data
    var files = $(this)[0].files;
    processFiles(files);

    return false;
  });

  // Visa "ta bort"-ikon när man hovrar bilder
  $('body').on('hover', '.preview img,a', function() {
    $(this).parent().parent().children('.remover').children('a').show();
    $(this).parent().parent().children('.imageHolder').children('img').addClass('shadowed');
  });

  // Dölj "ta bort"-ikon när man inte hovrar bilder
  $('body').on('mouseleave', '.preview img,a', function() {
    $(this).parent().parent().children('.imageHolder').children('img').removeClass('shadowed');
    $(this).parent().parent().children('.remover').children('a').hide();
  });

  // Ta bort bilden när man klickar på "ta bort"-ikonen
  $('body').on('click', '.remover a', function() {
    // Om filen redan finns sparad på disk (har en data-file-path)
    var filePath = $(this).parent().parent().children('.imageHolder').children('img').attr('data-file-path');
    if (typeof filePath != 'undefined' && filePath != '' && filePath.length) { // filePath finns
      // Lagra filePath som en "removed-file-path" i previewDiven
      $('#previewDiv').append('<span class="removedFilePath" data-removed-file-path="' + filePath + '"></span>');
    }

    // Ta bort från klientsidan
    $(this).parent().parent().fadeOut('normal', function(){ // dubbla parent för att ta bort hela .preview
      $(this).remove();
    });
  });

  /*--------------------------------
                Funktion för att processera
                listan med filer som valts
        ----------------------------------*/
  function processFiles(files) {
    if(files && typeof FileReader !== "undefined") {
      // process each files only if browser is supported
      for(var i=0; i<files.length; i++) {
        // räkna storlek
        totalFileSize = totalFileSize + files[i].size;
        // om total storlek är för mycket för POST-request
        if (totalFileSize > maxAllowedTotalFileSize) {
          alert ("Filerna är för stora. Max tillåtna storlek per uppladdning är " + maxAllowedTotalFileSize/1024/1024 + " MB.");
          return false;
        }
        createPreview(files[i]);
      }
    } else {
      alert("Your browswer sucks; it doesn't support the HTML5 FileReader API.");
    }
  }

  /*--------------------------------
                Funktion för att skapa previews
        ----------------------------------*/
  function createPreview(file){

    var preview = $(template),
      image = $('img', preview);

    var reader = new FileReader();

    image.width = 100;
    image.height = 100;

    reader.onload = function(e){

      // e.target.result holds the DataURL which
      // can be used as a source of the image:

      image.attr('src',e.target.result);
    };

    // Reading the file as a DataURL. When finished,
    // this will trigger the onload function above:
    reader.readAsDataURL(file);

    // Appenda preview
    preview.appendTo(previewDiv);

    // Associating a preview container
    // with the file, using jQuery's $.data():

    $.data(file,preview);
  }
});
