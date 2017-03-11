<?php
/**
 *	Laddar upp recept till databasen
 * 
 *	
 *
 */
require_once '../assets/includes/global.inc.php';

/*-------------------------------
	Ta emot de två arrayerna 
	och lagra dem i variabler
---------------------------------*/

// Kolla så att arrayerna finns
if (!isset($_POST['gallery'])) { 
	logger("upload.php: " . '$_POST-arrayen gallery är inte satt. Laddar ej upp.');
	echo 1;
	exit;
}

if (!isset($_POST['recipe'])) {
	logger("upload.php: " . '$_POST-arrayen recipe är inte satt. Laddar ej upp.');
	echo 1;
	exit;
}


// Lagra dem i variabler
$recipeArray = json_decode($_POST['recipe'], true);
$galleryArray = json_decode($_POST['gallery'], true);

try {
	/*--------------------------------
		Mappa innehåll till variabler
	---------------------------------*/
	$sets = Array();
	$tags = Array();
	// Utvinn data ur $recipeArray
	foreach ($recipeArray as $recipeEntry) {
		// Kolla att variabler är satta
		if (!isset($recipeEntry['value']) || !isset($recipeEntry['name'])){
			continue; // hoppa över detta entry
		}
		$name = $recipeEntry['name'];
		$value = $recipeEntry['value'];
		if ($name == "title") {
			$title = $value;
		} else if ($name == "intro") {
			$intro = $value;
		} else if ($name == "instructions") {
			$instructions = $value;
		} else if ($name == "nbrPersons") {
			$nbrOfPersons = $value;
		} else if ($name == "tag") {
			$tags[] = $value;
		} else if ($name == "setHeading") {
			$set = new Set($value);	// nytt set
			$sets[] = $set;	// lägg till det i sets-arrayen
		} else if ($name == "setId") {
			$set->setId($value);	// lägg till setId för nuvarande set		
		} else if ($name == "ingredient") {
			$set->addIngredient($value);	// lägg till ingrediensen till nuvarande set
		} else if ($name == "id") {
			$id = $value;
		}
	}
	
	// Skapa Image-objekt och spara i array från $galleryArray
	$images = array();

	foreach ($galleryArray as $galleryEntry) {
		$caption = $galleryEntry['captionString'];
		// om det är en ny bild - använda base64 - Image skapar då en ny filsöväg
		if (isset($galleryEntry['base64String'])) {
			$base64 = $galleryEntry['base64String'];
			$image = new Image($base64, $caption);
		// annars om det är en gammal bild - återanvänd filsökvägen
		} else if (isset($galleryEntry['filePathString'])) {
			$filePath = $galleryEntry['filePathString'];
			$image = new Image($filePath, $caption);
		}
		$images[] = $image;
	}
	
	// Gör ett Recipe-objekt och lagra bilder
	$recipe = new Recipe($title, $intro, $sets, $instructions, $tags, (int) $nbrOfPersons);
	$recipe->addImages($images);
	
	// Om det fanns ett id - lagra det
	if(isset($id)) {
		$recipe->setId((int) $id);
	}

	// Spara receptet till databasen
	$recipe->saveToDb();

} catch (Exception $e) {
	echo 1; // false
	logger("upload.php: " + $e->getMessage());
	exit;
}

// Allt gick väl
echo 0;	// true
?>