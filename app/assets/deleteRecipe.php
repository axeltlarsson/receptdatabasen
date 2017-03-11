<?php

require_once '../assets/includes/global.inc.php';
	
// Ta emot titel
$title = $_POST['title'];

// Radera receptet - returnera ev. felkod
if(deleteRecipe($title)) {
	echo 1; // true	- bra
} else {
	echo 0; // false - dåligt
}

?>