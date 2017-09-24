<?php
	require_once 'assets/includes/global.inc.php';
	

?>
<!DOCTYPE html>
<html>
	<head>
	<?php
		require_once 'assets/includes/head.inc.php';
		
		// initiera variabler
		$instructions = '';
		$intro = '';
		$title = '';
		$nbrOfPersons = '';
		$tags = array();
		$recipeId = '';
		/*---------------------------------------------
			Om denna sida kallas från "ändra recept"
		----------------------------------------------*/
		if (isset($_GET['title'])){
			
			$title = $_GET['title'];
			
			/*---------------------------------------
				Ladda in receptet från databasen
			----------------------------------------*/
			if(!$recipe = loadRecipe($title)) {
				// om det gick snett
				echo "<span class='error'>Kunde inte hitta receptet:</span> $title";
				return;
			}
			
			$intro = $recipe->getIntro();
			$instructions = $recipe->getInstructions();
			$nbrOfPersons = $recipe->getNbrOfPersons();
			$tags = $recipe->getTags();
			$sets = $recipe->getSets();
			$recipeId = $recipe->getId();
			$gallery = $recipe->getGallery();
		}
	?>
	
		<!-- Sidspecifik CSS -->
		<link rel="stylesheet" type="text/css" href="./assets/css/newRecipe.css" />
		<!-- Sidscpecifik jQuery -->

		<script src="./assets/js/jquery.auto-import.js"></script>
		<script src="./assets/js/jquery.new_recipe.js"></script>
		<script src="./assets/js/jquery.gallery.js"></script>
		<script src="./assets/js/jquery.validation.js"></script>
        <script src='./assets/js/jquery.autosize.js'></script>
		
		<script>
			$(document).ready(function(){
				// Om edit-mode är true
				if (<?php if(isset($_GET['title'])) { echo 1; } else { echo 0;} ?> == 1) {				
					/**-----------------------------
						Taggarna
					-----------------------------*/
					// fyll på en JS-array med taggarna från PHP-recept-objektet
					var tagsArray = new Array();
					<?php
						foreach ($tags as $tag) {
							?> tag = '<?php echo "$tag"; ?>';
							tagsArray.push(tag);
							<?php
						}
					?>
					/*------------------------------------------
						Lägg till rätt antal tagg-inputboxar
					-------------------------------------------*/
					$.each(tagsArray, function(key, value) {
						$('#tagsDiv input:text[value=""]').val(value);	// lägg till tagg i tomma inputsTaggar
						$('#addTag a').trigger('click');	// lägg till ny tagg-box
					});
					// Ta bort den extra sista tomma taggen
					$('#tagsDiv input:text[value=""]').parent('.tags').remove();
					
					/**-----------------------------
						Set och ingredienser
					------------------------------*/
					// fyll på en JS-array med set:en från PHP-recept-objektet
					var setsArray = new Array();
					<?php
						if (isset($sets)) {
							foreach ($sets as $set) {	// gå igenom alla set:en
								?>	set = new Array();	// skall lagras i denna JS-array
									setName = "<?php echo $set->getName(); ?>"; // ta reda på nuvarande set:s namn
									setId = "<?php echo $set->getId(); ?>"; // ta reda på nuvarande set:s databasid
									set.push(setName);
									set.push(setId);
									ingredientsArray = new Array();
								<?php 
									foreach ($set->getIngredients() as $ingredient) {	// gå igenom alla ingredienser för PHP-set-objekten
										?>	ingredient = "<?php echo $ingredient; ?>";	
											ingredientsArray.push(ingredient);		// lagra dem i ett JS-ingredients-objekt
										<?php
									}
								?>	set.push(ingredientsArray);	// lagra JS-ingredientsArray i JS-set-objektet
								setsArray.push(set); <?php	// lagra JS-set i JS-setsArray
							}
						}
					?>
					
					/*----------------------------------
						Lägg till set
					----------------------------------*/
					// Ta först bort alla set
					$('#ingredientsDiv .set').remove();
					
					/*-------------------------------
						Gå igenom alla set
					-------------------------------*/
					$.each(setsArray, function(key, value) { // för varje set
						var set = value;
						var setName = set[0];
						// lägg till ett tomt set
						$('#addSet a').trigger('click'); // har en tom setHeading
						// lägg till setId till en custom data-tagg
						var setId = set[1];
						$('#ingredientsDiv input:text[value=""].setHeading').parent('.set').attr('data-set-id', setId);
						/*--------------------------------
							Fyll på set:et med ingredienser
						---------------------------------*/
						var setIngredients = set[2];	// array med ingredienserna för detta set
						$.each(setIngredients, function(key, value) {				// för varje ingrediens i detta set
							$('#ingredientsDiv input:text[value=""].setHeading')	// hitta detta set med tom setHeading
								.parent('.set')										// gå upp en nivå till set-klassen
								.children('.ingredients')							// gå ner till ingrediensklasserna
								.children('input:text[value=""]')					// hitta en tom ingrediens-box
								.val(value);										// fyll på med nuvaranda ingrediens
							
							// Lägg till ingrediensbox en ny tom ingrediens-box
							$('#addIngredient a').trigger('click');
							
						});
						// Ta bort överflödig och tom ingrediensbox
						$('#ingredientsDiv input:text[value=""].setHeading').parent('.set')	// hit
							.children('.ingredients')
							.children('input:text[value=""]')
							.parent('.ingredients')
							.remove();
							
						// ändra namnet på set:et det till setName (alltså det set utan rubrik)
						$('#ingredientsDiv input:text[value=""].setHeading').val(setName)
							.trigger('keyup');	// trigga validering						
					});
					/**---------------------------
						Galleriet
					-----------------------------*/
					<?php if (isset($gallery)) 
					{ 
					?>
						var galleryArray = new Array();
						<?php 
						if (isset($gallery)) {
							foreach ($gallery as $image) {
								if ($image instanceof Image) {	// kolla så att det är en bild
									?>
										var image = new Array();	// skapa en JS-bild
										image.push("<?php echo $image->getPath(); ?>");	// lägg till filsökväg
										image.push("<?php echo $image->getCaption(); ?>");	// lägg till bildtext om det finns
										image.push('<?php echo $image->getBase64(); ?>'); // lägg till själva bilden som en base64										
										galleryArray.push(image); // lägg till JS-bilden till JS-galleriet	
									<?php
								}
							}	
						}
						?>
						/* Konvertera filsökvägarna till base64 */
						$.each(galleryArray, function(key, value) {
							if (value != null && value != "undefined") {
								var filePath = value[0];
								var caption = value[1];
								var base64 = value[2];
								// Markup för en bild
								$('#previewDiv').append('<div class="preview"><span class="imageHolder"><img src="' + base64 + '" data-file-path="' + filePath + '"></span><span class="remover"><a style="display: none; " class="icon">ç</a></span><input type="text" value="' + caption + '" class="imgcaption" placeholder="Skriv en bildtext här."/></div>');	
							}
						});
					<?php
					} ?>
					
					// Aktivera "spara recept"
					$('#saveRecipe a').attr('id', 'active');
					
				} // slut på if-sats (edit-mode)
			});
		</script>
		
		
	</head>
	<body>
        <div id="container_center">
		  <div id="container">
            <main>
                <form id="recipeInput">
                    <div>
                      <input placeholder="Importera recept från URL" type="text" name="auto-import" id="auto-import" />
                    </div>
                    <div id="titleDiv">
                        <input placeholder="Titel" type="text" name="title" id="title" value="<?php echo $title; ?>"/>
                    </div>
                    <div id="introDiv">
                        <textarea placeholder="Inledning" name="intro" id="intro"><?php echo $intro; ?></textarea>
                    </div>
                    <div id="ingredientsDiv">
                        <!-- This is where the magic will happen -->
                    </div>
                    <div id="instructionsDiv">
                        <label for="instructions">Instruktioner</label>
                        <textarea placeholder="Instruktioner" name="instructions" id="instructions"><?php echo $instructions; ?></textarea>
                    </div>
                    <div id="nbrPersonsDiv">
                        <label for="nbrPersons">För </label>
                        <input name="nbrPersons" type="number" id="nbrPersons" value="<?php echo $nbrOfPersons; ?>"/>
                        <label for="nbrPersons"> personer</label>
                    </div>
                    <div id="tagsDiv">
                        <!-- Here, some more magic will occurr -->
                    </div>
                </form>
            </main>

            <div id="right">
                <!-- Lägger till menyn (#sidebar) -->
                <div id="sidebar">
                    <?php require './assets/includes/nav.php'; ?> 
                    <ul id="tools">
                        <li id="addIngredient">
                            <span class="icon">{ </span><a>Lägg till ingrediens</a>
                        </li>
                        <li id="addSet">
                            <span class="icon">b </span><a>Lägg till set</a>
                        </li>
                        <li id="addTag">
                            <span class="icon">l </span><a>Lägg till tagg</a>
                        </li>
                        <li id="saveRecipe">
                            <span class="icon">å </span><a id="inactive" title="Du måste fylla i formen korrekt innan du kan spara.">Spara recept</a>
                        </li>
                    </ul>

                </div>
                <div id="gallery">
                    <input id="uploadbtn" class="uploadbtn icon" type="button" value="A"/>
                    <input id="upload" type="file" multiple />
                </div>
                <div id="previewDiv"></div>
        </div>
        </div>
        </div>
        <div id="footer"><div style="display: none" id="recipeId"><?php echo $recipeId; ?></div></div>
	</body>
<html>
