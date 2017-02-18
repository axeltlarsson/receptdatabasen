<?php 
require_once '../assets/includes/global.inc.php';
	
// Ta emot filsökväg för Image
$path = $_POST['path'];
try {
	// Om filsökväg angetts
	if (isset($path)) {
		// tar reda på absoluta filsökvägen
		$path = realpath($path); 
		
		// ta reda på basename -> typ 508e790d58104.jpg
		$baseName = basename($path);
		
		// Kolla att filen är läsbar och existerar
		if (is_readable($path)) {
			// radera den från disk
			unlink('<REPLACE ME WITH CORRECT PATH>uploaded_images/'.$baseName);
		} else { // filen är ej läsbar
			throw new Exception("deleteImage.php: filen är ej läsbar och går ej att radera.");
		}
		
	}
} catch (Exception $e) {
	echo 1; // fel
	logger($e->getMessage());
	exit;
}
logger("Raderade: " . $baseName);
echo 0; // lycka
?>
