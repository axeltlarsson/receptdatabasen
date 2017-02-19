<?php
/**	Klass som hanterar recept.
 *
 *	@author Axel Larsson <axl.larsson@gmail.com>
 *
 */
class Recipe
{
	/**
	 *	Privata variabler hos klassen
	 *
	 *	@var string _title - namnet på receptet
	 *	@var string _intro - inledning av receptet
	 *	@var array _sets - array av Set-objekt
	 *	@var string _instructions - instruktioner av receptet
	 *	@var array _tags - array av taggar (strängar)
	 *	@var array _gallery	- array av Image-objekt - första objektet är dock undefined
	 *	@var int _nbrOfPersons - antal personer
	 *	@var int _id - ett identifikationsnummer i databasen
	 *	?@var date _dateCreated - datum då receptet skapades (sätts automatiskt av MySql vid första save)
	 *	?@var data _dateUpdated - datum då det senast uppdaterades	(sätts automatiskt varje update av MySql)
	 */
	 private $_title,
		$_intro,
		$_sets,
		$_instructions,
		$_gallery,
		$_tags,
		$_nbrOfPersons,
		$_id;

	/**
	 *	Konstruktör
	 *
	 *	@param string $title - titeln på receptet
	 *	@param string $intro - inledning på receptet
	 *	@param array Set $sets - innehåller en lista med Set-objekt
	 *							som i sin tur innehåller ingredienser
	 *	@param string $instructions - beskrivning på hur hur receptet tillagas
	 *	@param array Image - innehåller en lista med Image-objekt
	 *	@param array/string $tags - en lista med taggar eller en tagg
	 *	@return void
	 */
	public function __construct($title, $intro, $sets, $instructions, $tags, $nbrOfPersons)
	{
		// Kolla att vi har fått rätt antal argument
		if (func_num_args() != 6) {
			throw new Exception('Recipe.class.php->__construct: WRONG NUMBER OF ARGUMENTS');
		}

		// Validera $title
		$this->_title = self::validateString($title);

		// Validera $intro
		$this->_intro = self::validateString($intro);

		// Validera $sets
		$this->_sets = self::validateSets($sets);

		// Validera $instructions
		$this->_instructions = self::validateString($instructions);

		// Validera $tags
		$this->_tags = self::validateTags($tags);

		// Validera $nbrOfPersons
		if(is_int($nbrOfPersons)) {
			$this->_nbrOfPersons = $nbrOfPersons;
		} else { // om det inte är ett tal
			throw new Exception('Recipe.class.php->__construct: PARAMETER $nbrOfPersons IS NOT A VALID INTEGER.');
		}
	}

	/**
	 *	Lägger till Image-objekt (bilder med caption) till galleriet
	 *
	 *	@param Image/array with Image:s $images - tar emot Image-objekt för sig eller i array
	 */
	public function addImages($images)
	{
		// Kolla att vi har fått rätt antal argument
		if (func_num_args() != 1) {
			throw new Exception('Recipe.class.php->addImages: WRONG NUMBER OF ARGUMENTS');
		}
		// Om $_gallery inte är en array (bara en Image)
		if (!is_array($this->_gallery)) {
			// gör om den till en array
			$image = $this->_gallery;
			$this->_gallery = array($image);
		}
		// Validera $images
		if (is_array($images)) { // om det är en array
			foreach($images as $image) {	// kolla varje objekt
				if($image instanceof Image) {	// om det är ett Image-objekt
					$this->_gallery[] = $image;	// lägg in det i $_gallery
				} else {	// om det inte är ett Image-objekt
					throw new Exception('Recipe.class.php->addImages: PARAMETER $images IS NOT A VALID IMAGE ARRAY.');
				}
			}
		} else { // det är inte en array
			if($images instanceOf Image) { // om det är ett Image-objekt
				$this->_gallery[] = $images;
			} else {	// det är inte ett Image-objekt
				throw new Exception('Recipe.class.php->addImages: PARAMETER $images IS NOT AN IMAGE.');
			}
		}
	}

	/**
	 *	Lägg till Set - ett i taget eller i en array
	 *
	 *	@param array/objekt - innehåller antingen array av Set-objekt
	 *	eller ett Set-objekt
	 */
	public function addSets($sets)
	{
		// Kolla att vi har fått rätt antal argument
		if (func_num_args() != 1) {
			throw new Exception('Recipe.class.php->addSets: WRONG NUMBER OF ARGUMENTS');
		}
		// Validera
		self::validateSets($sets);
		// Om $_sets inte är en array
		if (!is_array($this->_sets)) {
			// gör om den till en array
			$set = $this->_sets;
			$this->_sets = array($set);
		}
		// Lägg in
		if(is_array($sets)) {	// det är en array
			foreach($sets as $set) {
				$this->_sets[] = $set;
			}
		} else {	// ett Set-objekt
			$this->_sets[] = $sets;
		}

	}

	/**
	 *	Lägger till taggar till ett recept
	 *
	 *	@param string/array $tags - innehåller antingen array med taggar eller en tagg
	 */
	public function addTags($tags)
	{
		// Kolla att vi har fått rätt antal argument
		if (func_num_args() != 1) {
			throw new Exception('Recipe.class.php->addTags: WRONG NUMBER OF ARGUMENTS');
		}
		// Validera taggen/taggarna
		self::validateTags($tags);

		// Om $_tags inte är en array (bara en tagg)
		if (!is_array($this->_tags)) {
			// gör om den till en array
			$tag = $this->_tags;
			$this->_tags = array($tag);
		}

		if (is_array($tags)) {	// om det är en array av taggar
			foreach($tags as $tag) {	// lägg till varje tagg i arrayen
				$this->_tags[] = $tag;
			}
		} else {	// annars lägg till taggen till arrayen
			$this->_tags[] = $tags;
		}
	}

	/**
	 *	Sparar receptet till databasen
	 *
	 *	@return boolean - true om det gick bra att spara, false annars
	 */
	public function saveToDb()
	{
		// Variabler till att koppla upp mot databasen
    $dataBaseName = $_ENV["SQL_DB"];
    $user = $_ENV["SQL_USER"];
    $host = $_ENV["SQL_HOST"];
    $password = $_ENV["SQL_PASSWORD"];

		// För loggning
		$editMode = false;
		try {	// Skapa ett PDO-objekt
			$db = new PDO("mysql:host=$host;dbname=$dataBaseName;charset=utf8", $user, $password);
			$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

			/*------------------------------------------
				Om receptet redan finns
			-------------------------------------------*/
			if(isset($this->_id)) {
				$editMode = true;
				/*--------------------------------------------
					Modifiera entry för receptet i `Recipes`
				----------------------------------------------*/
				// Förbered SQL-statement
				$stmt = $db->prepare('UPDATE Recipes SET Title=:title, Intro=:intro, Instructions=:instructions, NbrOfPersons=:nbrOfPersons WHERE P_id=:p_id');
				$updateRecipe = $db->prepare('UPDATE Recipes SET DateUpdated=CURRENT_TIMESTAMP() WHERE P_id=:p_id');
				// Bind variabler och exekvera SQL
				$stmt->execute(array(':title' => $this->_title,
									 ':intro' => $this->_intro,
									 ':instructions' => $this->_instructions,
									 ':nbrOfPersons' => $this->_nbrOfPersons,
									 ':p_id' => $this->_id));
				$updateRecipe->execute(array(':p_id' => $this->_id));

				/*---------------------------------------
					Ta bort de gamla taggarna
				----------------------------------------*/
				// Förbered SQL-statement
				$deleteTags = $db->prepare('DELETE FROM Tags WHERE F_id=:f_id');

				// Bind variabler och exekvera SQL
				$deleteTags->execute(array(':f_id' => $this->_id));

				/*---------------------------------------
					Ta bort bilderna ur galleriet
				----------------------------------------*/
				// Förbered SQL-statement
				$deleteGallery = $db->prepare('DELETE FROM Gallery WHERE F_id=:f_id');

				// Bind variabler och exekvera SQL
				$deleteGallery->execute(array(':f_id' => $this->_id));

				/*---------------------------------------
					Ta bort ingredienser och set
				----------------------------------------*/
				// Förbered SQL-statement
				$getSetIds = $db->prepare('SELECT P_id FROM Sets WHERE F_id=:f_id');
				$deleteIngredients = $db->prepare('DELETE FROM Ingredients WHERE F_id=:f_id');
				$deleteSets = $db->prepare('DELETE FROM Sets WHERE F_id=:f_id');

				// Ta reda på alla gamla set-id
				$getSetIds->execute(array(':f_id' => $this->_id));

				// För varje set i receptet -> radera dess ingredienser
				foreach($setIds = $getSetIds->fetchAll() as $setId) {
					$deleteIngredients->execute(array('f_id' => $setId[0]));
				}

				// Bind varibler (receptId) och exekvera SQL - radera set:en
				$deleteSets->execute(array(':f_id' => $this->_id));

			} else {
				/*-----------------------------------------
					Gör en entry för receptet i `Recipes`
				------------------------------------------*/
				// Förbered SQL-statement
				$stmt = $db->prepare("INSERT INTO Recipes(Title, Intro, Instructions, NbrOfPersons) VALUES(:title, :intro, :instructions, :nbrOfPersons)");

				// Bind variabler och exekvera SQL
				$stmt->execute(array(':title' => $this->_title,
									 ':intro' => $this->_intro,
									 ':instructions' => $this->_instructions,
									 ':nbrOfPersons' => $this->_nbrOfPersons));
				// För att se om det gick bra
				if($stmt->rowCount() != 1){
					throw new Exception("Recipe.class.php->saveToDb: SOMETHING WENT WRONG WHEN SAVING THE RECIPE TO THE DATABASE.");
				}

				// Hämta receptets databasid
				$this->_id = $db->lastInsertId();
			}

			/*---------------------------------------
				Lägg in taggar
			----------------------------------------*/
			if(isset($this->_tags)) {
				// Förbered SQL-statement
				$stmt = $db->prepare("INSERT INTO Tags VALUES(:tag, :f_id)");

				// Initiera variabler
				$tag = '';

				// Bind variabler
				$stmt->bindParam(':tag', $tag, PDO::PARAM_STR);	// binder :tag till $tag
				$stmt->bindParam(':f_id', $this->_id, PDO::PARAM_STR);	// binder :f_id till $_id

				// Exekvera SQL med varje $tag från $_tags-arrayen
				foreach($this->_tags as $tag) { // Gå igenom $_tags och lägg till taggar en efter en
					$stmt->execute();
				}
			}
			/*--------------------------------------------
				Lägg till bilder till receptet
			----------------------------------------------*/
			// Om det finns bilder
			if(isset($this->_gallery)) {
				// Förbered SQL-statement
				$stmt = $db->prepare("INSERT INTO Gallery VALUES(:caption, :filePath, :f_id)");

				// Initiera varibler
				$caption = "";
				$filePath = "";

				// Bind varibler
				$stmt->bindParam(':caption', $caption, PDO::PARAM_STR);	// binder :caption till $caption
				$stmt->bindParam(':filePath', $filePath, PDO::PARAM_STR); // binder :filePath till $filePath
				$stmt->bindParam(':f_id', $this->_id, PDO::PARAM_STR); // binder :f_id till $_id

				// Exekvera SQL med varje $caption, $filePath från $_gallery-arrayen
				foreach($this->_gallery as $image) {
					if ($image instanceof Image) {	// undviker den första tomma entry:n i _gallery
						$caption = $image->getCaption();
						$filePath = $image->getPath();
						$stmt->execute();
					}
				}
			}

			/*--------------------------------------
				Lägg till set till receptet
			---------------------------------------*/
			// Förbered SQL-statement
			$stmt = $db->prepare("INSERT INTO Sets(SetName, F_id) VALUES(:setName, :f_id)");

			// Initiera varibler
			$setName = "";

			// Bind varibler
			$stmt->bindParam(':setName', $setName, PDO::PARAM_STR); // binder :setName till $setName
			$stmt->bindParam(':f_id', $this->_id, PDO::PARAM_STR); // binder :f_id till $_id

			// Exekvera SQL med varje $setName från $_sets-arrayen
			foreach($this->_sets as $set) {
				$setName = $set->getName();
				$stmt->execute();

				// Hämta och lagra set:ets databasid
				$set->setId($db->lastInsertId());

				/*-----------------------------------------
					Lägg till ingredienser till set:et
				--------------------------------------------*/
				// Förbered SQL-statement
				$stmtIng = $db->prepare("INSERT INTO Ingredients VALUES(:ingredient, :f_id)");

				// Initiera variabler
				$ingredient = "";
                $ingredientId = $set->getId();

				// Bind variabler
				$stmtIng->bindParam(':ingredient', $ingredient, PDO::PARAM_STR); // binder :ingredient till $ingredient
				$stmtIng->bindParam(':f_id', $ingredientId, PDO::PARAM_STR); // binder :f_id till set:ets id

				// Kolla så att set:et innehåller ingredienser - att det är en array
				if (!is_array($set->getIngredients())) {
					throw new Exception("Recipe.class.php->saveToDb: SET DOES NOT CONTAIN ANY INGREDIENTS.");
				}
				// Exekvera SQL med varje $ingredient från $set
				foreach ($set->getIngredients() as $ingredient) {
					$stmtIng->execute();
				}
			}
		}
		catch (PDOException $ex) {
			echo '<span class="error">An ERROR occurred!<br /></span>';
			logger("Recipe.class.php->saveToDb: " . $ex->getMessage());
			return false;
		}
	}

	/**
	 *	Tar bort receptet ur databasen
	 *
	 *	@return boolean - true om det gick bra att radera, false annars
	 */
	public function deleteFromDb()
	{

		// Variabler till att koppla upp mot databasen
		$dataBaseName = '';
		$user = '';
		$host = '';
		$password = '';

		try {
			// Kolla att receptet har ett id
			if(!isset($this->_id)) {
				throw new Exception("Recipe.class.php->deleteFromDb: RECIPE CANNOT BE DELETED: IT HAS NO DATABASE ID!");
			}

			// Skapa ett PDO-objekt
			$db = new PDO("mysql:host=$host;dbname=$dataBaseName;charset=utf8", $user, $password);
			$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

			/*---------------------------------------
				Ta bort taggar
			----------------------------------------*/
			// Förbered SQL-statement
			$deleteTags = $db->prepare('DELETE FROM Tags WHERE F_id=:f_id');

			// Bind variabler och exekvera SQL
			$deleteTags->execute(array(':f_id' => $this->_id));

			/*---------------------------------------
				Ta bort bilderna ur galleriet
			----------------------------------------*/
			// Förbered SQL-statement
			$deleteGallery = $db->prepare('DELETE FROM Gallery WHERE F_id=:f_id');

			// Bind variabler och exekvera SQL
			$deleteGallery->execute(array(':f_id' => $this->_id));

			/*---------------------------------------
				Ta bort ingredienser och set
			----------------------------------------*/
			// Förbered SQL-statement
			$deleteIngredients = $db->prepare('DELETE FROM Ingredients WHERE F_id=:f_id');
			$deleteSets = $db->prepare('DELETE FROM Sets WHERE F_id=:f_id');

			// För varje set i $_sets -> radera dess ingredienser
			foreach($this->_sets as $set) {
				$setId = $set->getId();
				$deleteIngredients->execute(array('f_id' => $setId));
			}

			// Bind varibler (receptId) och exekvera SQL - radera set:en
			$deleteSets->execute(array(':f_id' => $this->_id));

			/*--------------------------------------
				Ta bort entry från `Recipes`
			---------------------------------------*/
			// Förbered SQL-statement
			$deleteRecipe = $db->prepare('DELETE FROM Recipes WHERE P_id=:p_id');

			// Bind varibler och exekvera SQL
			$deleteRecipe->execute(array(':p_id' => $this->_id));

		} catch (PDOException $ex) {
			echo '<span class="error">An ERROR occurred!</span>';
			logger($ex->getMessage());
			return false;
		} catch (Exception $e) {
			echo '<span class="error">An ERROR occurred!</span>';
			logger($e->getMessage());
			return false;
		}
		return true; // det gick bra att radera
	}

	/**
	 *	Intern funktion som validerar Set:s
	 *	Kastar ett fel om valideringen misslyckas.
	 *
	 *	@param Set/array with Set:s $sets - ett Set/en eller flera Set:s i en array
	 *
	 *	@return @param $sets - returnerar parametern om inga fel påträffades
	 */
	private function validateSets($sets)
	{
		if (is_array($sets)) {	// om det är en array
			foreach($sets as $set) {
				if(!$set instanceof Set) {	// om det inte är ett objekt av typen Set
					throw new Exception('Recipe.class.php->validateSets: PARAMETER $sets IS NOT A VALID SET ARRAY.');
				}
			}

		} else {	// inte en array
			if(!$sets instanceof Set) {
				throw new Exception('Recipe.class.php->validateSets: PARAMETER $sets IS NOT A VALID SET.');
			}
		}
		return $sets;
	}

	/**
	 *	Intern funktion som validerar taggar
	 *	Kastar ett fel om det inte går att validera taggarna.
	 *
	 *	@param string/array with strings $tags - taggar i en sträng eller i en array
	 *
	 *	@return @param $tags - returnerar parametern om inga fel påträffades
	 */
	private function validateTags($tags)
	{
		if (is_array($tags)) {	// om det är en array
			foreach($tags as $tag) {	// gå igenom varje enhet i arrayen
				if(!is_string($tag)) {	// kolla så att det är en tagg
					throw new Exception('Recipe.class.php->validateTags: PARAMETER $tags IS NOT A VALID TAG ARRAY.');	// innehåller ogiltiga strängar
				}
			}
		} else {	// inte en array
			if(!is_string($tags)) {
				throw new Exception('Recipe.class.php->validateTags: PARAMETER $tags IS NOT A STRING');	// ej sträng
			}
		}
		return $tags;
	}

	/**
	 *	Intern funktion som validerar strängar
	 *	Kastar ett fel om det inte är en sträng
	 *
	 *	@param string $string - strängen som skall valideras
	 *
	 *	@return @param $string - returnerar strängen om inga fel hittats
	 */
	private function validateString($string)
	{
		// Kolla att $string är en sträng
		if (!is_string($string)) {
			throw new Exception('Recipe.class.php->validateString: PARAMETER IS NOT A STRING.');
		} else {
			return $string;
		}
	}

	/**
	 *	Returnerar titel
	 *
	 *	@return string - innehållandes titeln
	 */
	public function getTitle()
	{
		return $this->_title;
	}

	/**
	 *	Returnerar intro
	 *
	 *	@return string - innehållandes inledningen
	 */
	public function getIntro()
	{
		return $this->_intro;
	}

	/**
	 *	Returnerar sets
	 *
	 *	@return array - innehållandes sets
	 */
	public function getSets()
	{
		return $this->_sets;
	}

	/**
	 *	Returnerar instruktionerna
	 *
	 *	@return string - innehållandes instruktioner
	 */
	public function getInstructions()
	{
		return $this->_instructions;
	}

	/**
	 *	Returnerar taggar
	 *
	 *	@return array - innehållandes taggar
	 */
	public function getTags()
	{
		return $this->_tags;
	}

	/**
	 *	Returnera galleriet
	 *
	 *	@return Image/array with Image:s - returnerar antingen en array av Image-objekt eller ett Image-objekt
	 */
	public function getGallery()
	{
		return $this->_gallery;
	}

	/**
	 *	Returnerar antalet personer
	 *
	 *	@return int $_nbrOfPersons - antalet personer
	 */
	public function getNbrOfPersons()
	{
		return $this->_nbrOfPersons;
	}

    /**
     *  Returnerar rätt böjning av substantivet person(er)
     *  beroende på om getNbrOfPersons() är plural/singular.
     *
     *  @return string "person" eller "personer"
     */
    public function getNoun()
    {
        return $this->_nbrOfPersons > 1 ? "personer" : "person";
    }


	/**
	 *	Returnerar databasid om det finns
	 *
	 *	@return int $_id - eller false om det inte existerar
	 */
	public function getId()
	{
		if(isset($this->_id)) {
			return $this->_id;
		} else {
			return false;
		}
	}

	/**
	 *	Ändrar titel
	 *
	 *	@param string $newTitle - den nya titeln
	 */
	public function setTitle($newTitle)
	{
		$this->_title = self::validateString($newTitle);
	}

	/**
	 *	Ändrar inledning
	 *
	 *	@param string $newIntro - den nya inledningen
	 */
	public function setIntro($newIntro)
	{
		$this->_intro = self::validateString($newIntro);
	}

	/**
	 *	Sätter instruktionerna
	 *
	 *	@param string $newInstructions - innehåller de instruktionerna
	 */
	public function setInstructions($newInstructions)
	{
		$this->_instructions = self::validateString($newInstructions);
	}

	/**
	 *	Sätter $_id
	 *
	 *	@param int $id - innehåller receptets databasid
	 */
	public function setId($setId)
	{
		// Kolla att vi har fått rätt antal argument
		if (func_num_args() != 1) {
			throw new Exception('Recipe.class.php->setId: WRONG NUMBER OF ARGUMENTS');
		}

		// Lagra databasid om det är en int
		if (is_int($setId)) {
			$this->_id = $setId;
		} else {
			throw new Exception('Recipe.class.php->setId: PARAMETER $setId IS NOT A NUMBER.');
		}
	}

	/** !!!! FUngerar ej med endast ett set eller en tag som ej ligger i en array !!!
	 *	Printar receptet, denna funktion kommer förmodligen att kasseras
	 *
	 *	@return string - en sträng innehållandes receptet i html format
	 */
	public function printRecipe()
	{
		$recipe = "";
		$recipe .= '<h3>' . $this->_title . '</h3><br />';
		$recipe .= '<i>' . $this->_intro . '</i><br />';
		foreach($this->_sets as $set) {
			$recipe .= '<h4>' . $set->getName() . '</h4><br />';
			foreach ($set->getIngredients() as $ingredient) {
				$recipe .= $ingredient . '<br />';
			}
		}
		$recipe .= '<h4>Instruktioner</h4>' . $this->_instructions . '<br />';
		$recipe .= '<i><h4>Taggar:</h4> ';
		foreach($this->_tags as $tag) {
			$recipe .= $tag . ' ';
		}
		$recipe .= '</i>';
		$recipe .= '# ' . $this->_nbrOfPersons;
		return $recipe;
	}

}


?>
