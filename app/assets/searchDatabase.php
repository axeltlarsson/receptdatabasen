<?php
/**
 *	@name searchDatabase.php
 *
 */
 
require_once '../assets/includes/global.inc.php';
	
// Ta emot sökterm och inställningar
$query 				= $_POST['query'];
$matchTags 			= $_POST['matchTags'];
$matchIngredients 	= $_POST['matchIngredients'];
$matchTitle			= $_POST['matchTitle'];

// Initiera en array innehållandes eventuella matchningar
$matches = array();

/*-------------------------------------------------
	Koppla upp mot databasen - skaffa ett PDO-objekt
-------------------------------------------------*/
$db = connectToDb();

/*---------------------------
	Hitta matchande titlar
----------------------------*/
if ($matchTitle == "true") {
	try {
		// Förbered SQL-statement
		$matchTitles = $db->prepare('SELECT Title FROM Recipes WHERE Title LIKE :title');
		
		// Sätt till att hämta associativ array
		$matchTitles->setFetchMode(PDO::FETCH_ASSOC);
		
		// Exekvera SQL-statement
		$matchTitles->execute(array(':title' => '%' . $query . '%'));
		
		
		while ($match = $matchTitles->fetch()) {
			array_push($matches, $match['Title']);
		}

	} catch (PDOException $e) {
		logger($e->getMessage());
		return "ERROR";
	}
}
/*---------------------------
	Hitta matchande taggar
----------------------------*/
if ($matchTags == "true") {
	try {
		// Förbered SQL-statement
		$matchTags = $db->prepare('SELECT Tag, F_id FROM Tags WHERE Tag LIKE :tag');
		$findTitleById = $db->prepare('SELECT Title FROM Recipes WHERE P_id=:id');
		
		// Sätt till att hämta associativ array
		$matchTags->setFetchMode(PDO::FETCH_ASSOC);
		
		// Exekvera SQL-statement -> sök efter taggar som matcha sökterm
		$matchTags->execute(array(':tag' => '%' . $query . '%'));
		
		// Gå igenom matchningar av taggar och finn ut titel på receptet
		while ($match = $matchTags->fetch()) {
			
			// Exekvera SQL-statement - hitta recepttitlar
			$findTitleById->execute(array(':id' => $match['F_id']));
			
			// Lagra titel i $matches-arrayen
			while ($titles = $findTitleById->fetch()) {
				array_push($matches, $titles['Title']);
			}
		}

	} catch (PDOException $e) {
		logger($e->getMessage());
		return "ERROR";
	}
}
/*----------------------------------
	Hitta matchande set
-----------------------------------*/
if ($matchIngredients == "true") {
	try {
		// Förbered SQL-statements
		$matchSets = $db->prepare('SELECT SetName, F_id FROM Sets WHERE SetName LIKE :query');
		$findTitleById = $db->prepare('SELECT Title FROM Recipes WHERE P_id=:id');
		
		// Exekvera SQL-statement -> matcha query mot taggar
		$matchSets->execute(array(':query' => '%' . $query . '%'));
		
		// Gå igenom resultat och hitta titel för varje matchning
		while ($sets = $matchSets->fetch()) {
			
			// Exekvera SQL-statement - hitta taggarnas recept
			$findTitleById->execute(array(':id' => $sets['F_id']));
			
			// Lagra titel i $matches-arrayen
			while ($titles = $findTitleById->fetch()) {
				array_push($matches, $titles['Title']);
			}
		}
		
	} catch (PDOException $e) {
		logger($e->getMessage());
		return "ERROR";
	}

}
/*-------------------------------
	Hitta matchande ingredienser
--------------------------------*/
if ($matchIngredients == "true") {	
	try {
		// Förbered SQL-statement
		$matchIngredients = $db->prepare('SELECT Ingredient, F_id FROM Ingredients WHERE Ingredient LIKE :ingredient');
		$findTitleIdBySetId = $db->prepare('SELECT F_id FROM Sets WHERE P_id=:id');
		$findTitleById = $db->prepare('SELECT Title FROM Recipes WHERE P_id=:id');
				
		/*-----------------------------------------------
			Matcha $query mot ingredienser
		--------------------------------------------------*/
		// Lagra matchande ingrediensers "F_id", alltså dess set:s "P_id" i en array
		$setIds = array();
		
		// Exekvera SQL-statement
		$matchIngredients->execute(array(':ingredient' => '%' . $query . '%'));
		
		while ($ingredient = $matchIngredients->fetch()) {
			array_push($setIds, $ingredient['F_id']);
		}

		/*----------------------------------------------------------------
			Lagra dessa set:s "F_id" alltså receptens "P_id" i en array
		-----------------------------------------------------------------*/
		$titleIds = array();
		foreach ($setIds as $setId) {
			// Exekvera SQL-statement
			$findTitleIdBySetId->execute(array(':id' => $setId));
			
			while ($set = $findTitleIdBySetId->fetch()) {
				array_push($titleIds, $set['F_id']);
			}
		}
		
		/*---------------------------------------------------------------
			Mappa recept-"P_id" till deras titlar och lagra i en $matches
		----------------------------------------------------------------*/
		foreach($titleIds as $titleId) {
			// Exekvera SQL
			$findTitleById->execute(array(':id' => $titleId));
			
			// Lagra titel
			while ($recipe = $findTitleById->fetch()) {
				array_push($matches, $recipe['Title']);
			}
		}


	} catch (PDOException $e) {
		logger($e->getMessage());
		return "ERROR";
	}
}

// Ta bort dubletter av titlar
$matches = array_unique($matches);

// Returnera resultat-array
echo json_encode($matches);


?>