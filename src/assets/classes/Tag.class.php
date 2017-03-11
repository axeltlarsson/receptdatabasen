<?php
class Tag {
	private $_name,
			$_nbrOfRecipes;

	public function __construct($name) {
		$this->_name = $name;
		$this->_nbrOfRecipes = 1;
	}

	public function incrementNbrOfRecipes() {
		$this->_nbrOfRecipes++;
	}

	public function getName() {
		return $this->_name;
	}

	public function getNbrOfRecipes() {
		return $this->_nbrOfRecipes;
	}
}
?>