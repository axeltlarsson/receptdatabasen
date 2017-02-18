<?php
/*------------------------
	Importera klasser
-------------------------*/
require_once '<REPLACE WITH CORRECT PATH>assets/classes/Recipe.class.php';
require_once '<REPLACE WITH CORRECT PATH>assets/classes/Set.class.php';
require_once '<REPLACE WITH CORRECT PATH>assets/classes/Image.class.php';
require_once '<REPLACE WITH CORRECT PATH>assets/classes/Tag.class.php';
error_reporting(E_ALL);
/*------------------------
	Funktioner
-------------------------*/

/**
 *	Loggningsfunktion - skriver en logfil
 *
 *	@param string $message - meddelandet som skrivs i loggen
 */
function logger($message) 
{
	$logFile = '<REPLACE WITH CORRECT PATH>logs/log.txt';
	$fileHandle = fopen($logFile, 'a');
	$dateString = date("d-m-y H:i:s_");
	fwrite($fileHandle, $dateString.$message."\n");	
}

/**
 *	Koppla upp mot databasen
 *
 *	@return PDO $db - databasobjekt
 */
function connectToDb() {
	/*-------------------------------------------------
		Koppla upp mot databasen
	-------------------------------------------------*/
	$dataBaseName = 'CHANGEME';
	$user = 'CHANGEME';
	$host = 'localhost';
	$password = 'CHANGEME';

	try {	
		// Skapa ett PDO-objekt
		$db = new PDO("mysql:host=$host;dbname=$dataBaseName;charset=utf8", $user, $password);
		$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
		
		// Returnera PDO-objektet
		return $db;

	} catch (PDOException $e) {
		logger($e->getMessage());
	}

}

/**!!! ändra till preperaedLaddar in recept från databasen
 *	
 *	@param string $title - titeln på receptet som skall hämtas
 *
 *	@return Recipe $recipe - ett Recipe-objekt
 */
function loadRecipe($title) 
{
	/*-------------------------------------------------
		Koppla upp mot databasen
	-------------------------------------------------*/
	$db = connectToDb();
	
	try {	
		/*----------------------------------------------------------
			Hämta titel, intro, instructions, nbrOfPersons
		--------------------------------------------------------------*/
		$stmt = $db->prepare("SELECT P_id, Intro, Instructions, NbrOfPersons FROM Recipes WHERE Title=:title");
		$stmt->execute(array(':title' => $title));
		
		# set fetch mode
		$stmt->setFetchMode(PDO::FETCH_ASSOC);
		# lagra informationen
		$recipeArray = $stmt->fetch();
		$recipeId = $recipeArray['P_id'];
		$intro = $recipeArray['Intro'];
		$instructions = $recipeArray['Instructions'];
		$nbrOfPersons = $recipeArray['NbrOfPersons'];
		if($stmt->rowCount() != 1) {
			throw new Exception("global.inc.php->loadRecipe: COULDN'T LOAD RECIPE: $title.");
		}
		
		/*-----------------------------
			Hämta taggar
		-----------------------------*/
		$stmt = $db->prepare("SELECT Tag FROM Tags WHERE F_id=:recipeId");
		$stmt->execute(array(':recipeId' => $recipeId));
		# lagra taggar
		$tags = array();
		while ($tagArray = $stmt->fetch()) {
			$tags[] = $tagArray['Tag'];
		}
		
		/*--------------------------------
			Hämta bilder
		---------------------------------*/
		$stmt = $db->prepare("SELECT Caption, FilePath FROM Gallery WHERE F_id=:recipeId");
		$stmt->execute(array(':recipeId' => $recipeId));
		
		# lagra bilder
		$gallery = array();
		while($galleryArray = $stmt->fetch()) {
			$image = new Image($galleryArray['FilePath'], $galleryArray['Caption']);
			$gallery[] = $image;
		}
		
		/*----------------------------------
			Hämta set och ingredienser
		-----------------------------------*/
		$stmt = $db->prepare("SELECT SetName, P_id FROM Sets WHERE F_id=:recipeId");
		$stmt->execute(array(':recipeId' => $recipeId));
		
		$sets = array();
		
		// Hämta alla set
		while($setsArray = $stmt->fetch()) {
			// Skapa ett nytt set med namn och id från $stmt
			$set = new Set($setsArray['SetName']);
			$setId = $setsArray['P_id'];
			$set->setId($setId);
			
			// Hämta ingredienser tillhörande det set:et
			$stmtIng = $db->query("SELECT Ingredient FROM Ingredients WHERE F_id=$setId");
			$ingredients = array();
			while($ingArray = $stmtIng->fetch()) {
				$ingredients[] = $ingArray['Ingredient'];
			}

			// Lägg till de ingredienserna till set:et
			$set->setIngredients($ingredients);
			
			// Lägg till set:et till arrayen $sets
			$sets[] = $set;
		}
	
	/*-------------------------------------------
		Skapa ett receptobjekt och returnera det
	---------------------------------------------*/
	
		$recipe = new Recipe($title, $intro, $sets, $instructions, $tags, (int) $nbrOfPersons);
		// Lägg till bilderna
		$recipe->addImages($gallery);
		// Lägg till id
		$recipe->setId((int) $recipeId);
		return $recipe;
	
	} catch (PDOException $ex) {
		echo '<div style="border: 1px solid red">An ERROR occurred when loading the recipe!</div>';
		logger($ex->getMessage());
		return false;	
	} catch (Exception $e) {
		echo '<div style="border: 1px solid red">An ERROR occurred when loading the recipe!</div>';
		logger($e->getMessage());
		return false;
	}
}

/** 
 *	Funktion för att kolla om titel redan finns i databasen
 *
 *	@param string $title - titeln som skall matchas
 *
 *	@return boolean - true om titel existerar, false annars
 */
function checkTitleExists($title) 
{
	/*-------------------------------------------------
		Koppla upp mot databasen
	-------------------------------------------------*/
	$db = connectToDb();
	
	try {	
		// Förbered SQL-statement
		$stmt = $db->prepare('SELECT * FROM Recipes WHERE Title=:title');
		
		// Bind variabler och exekvera SQL
		$stmt->execute(array(':title' => $title));
		
		// Kolla om något recept matchade $title
		if($stmt->rowCount() == 1) { // en match => receptet finns redan
			return true;
		} else { // receptet finns ej
			return false;
		}
		
	} catch (PDOException $ex) {
		echo '<span class="error">Ett fel inträffade.</span>';
		logger($ex->getMessage());
	}
}

/**
 *	Funktion för att radera ett recept från databasen
 *
 *	@param string $title - titeln på recept som skall raderas
 *
 */
function deleteRecipe($title)
{
	// Försök ladda in receptet
	if(!$recipe = loadRecipe($title)) {
		// om det gick snett
		return false;
	// Försök radera receptet
	} else if($recipe->deleteFromDb()) {
		return true;	// det gick bra
	} else {
		return false; 	// det gick dåligt
	}

}

/**
 * 	Funktion för att returnera en lista med alla taggar som finns i databasen.
 *	
 *	@return $tagList - en array med Tag:s inkluderandes antal recept som varje tagg tillhör
 */
function getTagList() {
	/*-------------------------------------------------
		Koppla upp mot databasen
	-------------------------------------------------*/
	$db = connectToDb();
	
	try {	
		/*-----------------------------
			Hämta taggar
		-----------------------------*/
		$stmt = $db->prepare("SELECT * FROM Tags ORDER BY Tag");
		$stmt->execute();
		# lagra taggar
		$tags = array();
		$oldTagName = "";
		$oldTag = new Tag("");
		while ($tagArray = $stmt->fetch()) {
			// hämta nuvarande tag
			$tagName = trim($tagArray['Tag']);
			$tag = new Tag($tagName);
			if (strcasecmp($oldTagName, $tagName) == 0)  { // samma tagg (case insensitive), men använd i annat recept
				$oldTag->incrementNbrOfRecipes();	
			} else { // ny tagg
				$tags[] = $tag;
				$oldTag = $tag;
			}

			$oldTagName = $tagName;
			
		}

		$resultArray[] = array();
		
		foreach ($tags as $tag) {
			$resultArray[$tag->getName()] = $tag->getNbrOfRecipes(); 
		}
		return $resultArray;

	} catch (PDOException $ex) {
		echo '<span class="error">Ett fel inträffade.</span>';
		logger($ex->getMessage());
	}
}

/**
 *	Resize-funktion - genererar resizade, mer lätthanterliga bilder
 *
 *	@param $src string - filsökväg till bild
 *	@param $desired_width int - önskad bredd på resizad bild
 *
 *	@return $data - virtuell bilddata
 */
function resizeImage($src, $desired_width)
{
	// Fixa till $src	
	$src = 'uploaded_images/' . basename($src);

	// Ta reda på filändelse
	$ext = substr(strrchr($src,'.'),1);

	// Kolla att det är en ok filändelse
	$allowed_exts = array("gif", "jpeg", "jpg", "png");
	if (!in_array($ext, $allowed_exts)) {
		throw new Exception("global.inc.php->resizeImage: IMAGE EXTENSION NOT ALLOWED: $ext");
	}
    
    // Öppna bilden beroende på filändelse
    switch ($ext) {
        case "gif" :
            // Öpnna filen
            $source_image = @imagecreatefromgif($src);
        break;
        case "png" :
            // Öppna filen
            $source_image = @imagecreatefrompng($src);
        break;
        case "jpg" :
            // Öppna filen
            $source_image = @imagecreatefromjpeg($src);
        break;
        case "jpeg" :
            // Öppna filen
            $source_image = @imageCreateFromJpeg($src);
		break;	
	}
	
	// Kontrollera att det gick att öppna
    if(!$source_image)
    {
        echo "global.inc.php->resizeImage: COULDN'T OPEN IMAGE: $source_image";
        throw new Exception("global.inc.php->resizeImage: COULDN'T OPEN IMAGE: $source_image");
    }
	
	// Ta reda på bredd och höjd
	$width = imagesx($source_image);
	$height = imagesy($source_image);
   
	// Finn önskad höjd med rätt förhållande gentemot specificerad önskad bredd
	$desired_height = floor($height * ($desired_width / $width));
    // Skapa en ny, virtuell bild
	$virtual_image = imagecreatetruecolor($desired_width, $desired_height);
	
	// Kopiera källbilden i de nya dimensionerna
	imagecopyresampled($virtual_image, $source_image, 0, 0, 0, 0, $desired_width, $desired_height, $width, $height);
	
	// Fånga streamen
	ob_start();
	switch ($ext) {
		case "gif" :
			imagegif($virtual_image);
		break;
		case "png" :
			imagepng($virtual_image);
		break;
		case "jpg" :
			imagejpeg($virtual_image);
		break;
		case "jpeg" :
			imagejpeg($virtual_image);
		break;	
	}
	// Spara stremen
	$data = ob_get_clean(); 
	
	// Frigör minne
	imagedestroy($virtual_image);
	
	// Returnera den genererade thumbnailen
	return $data;	
}
?>
