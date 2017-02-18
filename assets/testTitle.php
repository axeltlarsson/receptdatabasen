<?php
require_once '../assets/includes/global.inc.php';
	
// Ta emot titel
$title = $_POST['title'];

// Kolla titeln - returnera false (0) eller true (1)
if(checkTitleExists($title)) {
	echo 1; // true
} else {
	echo 0; // false
}
?>