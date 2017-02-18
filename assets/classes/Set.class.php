<?php
/**	Klass som hanterar set.
 *	Set innehåller i sin tur ett antal ingredienser, det är ett sätt att
 *	hantera ingredienser och dela in dem i underrubriker.
 *	
 *	@author Axel Larsson <axl.larsson@gmail.com>
 *
 */
class Set
{
	/**
	 *	Privata variabler
	 *
	 *	@var array _ingredients - innehåller en lista med ingredienser
	 *	@var string _name - innehåller namnet på set:et
	 *	@var int _id - innehåller set:ets databasid
	 */
	 private $_ingredients,
		$_name,
		$_id;
	 
	/**
	 *	Konstruktör - tar emot namnet på set:et som argument
	 */
	public function __construct($name) 
	{
		$this->_name = $name;
	}
	
	/**
	 *	Simpel get-funktion - returnerar namnet på set:et
	 *	@return string - namnet på set:et
	 */
	public function getName() 
	{
		return $this->_name;
	}
	
	/**
	 *	Returnerar ingredienserna som set:et innehåller i form av en array
	 *	
	 *	@return array - alla ingredienser som tillhör set:et
	 */
	public function getIngredients() 
	{
		return $this->_ingredients;
	}
	
	/**
	 *	Returnerar databasid
	 *
	 *	@return int $_id - databasid för set:et
	 */
	public function getId()
	{
		return $this->_id;
	}
	
	/**
	 *	Simpel set-funktion som ändrar namnet på set:et
	 *
	 *	@param string $newName - det nya namnet
	 */
	public function setName($newName) 
	{
		$this->_name = $newName;
	}
	
	/**
	 *	Sätter ingrediens-listan - tar emot
	 *	
	 *	@param string/array $ingredients - en ingrediens eller en lista innehållandes ingredienser
	 */
	public function setIngredients($ingredients) 
	{
		if(is_array($ingredients)) { // om array
			$this->_ingredients = $ingredients;	
		} else { // annars skapa en array och lägg in strängen
			$this->_ingredients = array($ingredients);
		}
	}
	
	/**
	 *	Lägger till ingredienser en till set:et
	 *
	 *	@param string $ingredient - en ingrediens
	 */
	public function addIngredient($ingredient) {
		// Kolla att vi har fått rätt antal argument
		if (func_num_args() != 1) {
			throw new Exception('Set.class.php->addIngredient: WRONG NUMBER OF ARGUMENTS');
		}
		// Om $_ingredients inte är en array (bara en string)
		if (!is_array($this->_ingredients)) {
			// gör om den till en array
			$thisIngredient = $this->_ingredients; 
			$this->_ingredient = array($thisIngredient);
		}
		
		// Kolla så att $ingredient är en sträng
		if (is_string($ingredient)) {	// om det är det
			$this->_ingredients[] = $ingredient; // lägg till den	
		} else {
			throw new Exception('Set.class.php->addIngredient: PARAMETER $ingredient IS NOT A STRING.');	// annars - kasta ett undantag
		}
	}
	/**
	 *	Sätter set:ets databasid
	 *
	 *	@param int $id - set:ets databasid
	 */
	public function setId($id)
	{
		$this->_id = $id;
	}		
}