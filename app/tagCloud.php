<?php
	require_once 'assets/includes/global.inc.php';
?>
<!DOCTYPE HTML>
<html>
<head>
	<?php
		require_once 'assets/includes/head.inc.php';
	?>
	<!-- Sidspecifik CSS -->
	<link rel="stylesheet" type="text/css" href="./assets/css/tagCloud.css" />
	<!-- Sidscpecifik jQuery -->
	<script type="text/javascript" src="./assets/js/jquery.tagCloud.js"></script>


</head>
	<body>
		<div id="header"></div>
		
	<!-- Dela upp sidan i tre kolumner -->
		<div id="center">	
			<ul id="tagList"></ul>
		<!--
			<?php

			// Testar Tag.class.php
			$tag = new Tag("testTag ballz");
			echo "Name: " . $tag->getName();
			echo "<br/>NbrOfIds: " . $tag->getNbrOfRecipes();
			$tag->incrementNbrOfRecipes();
			echo "<br/>NbrOfIds(+1): " . $tag->getNbrOfRecipes();

			$tags = array();
			$dessert = new Tag("Efterrätt");
			$tags[] = $tag;
			$tags[] = $dessert;
			$dessert->incrementNbrOfRecipes();
			$dessert->incrementNbrOfRecipes();
			$dessert->incrementNbrOfRecipes();
			$dessert->incrementNbrOfRecipes();
			$dessert->incrementNbrOfRecipes();

			foreach ($tags as $arrayTag) {
				echo "<br/>" . $arrayTag->getName() . " " . $arrayTag->getNbrOfRecipes();
			}
			?> -->
		</div>
		<!-- <div id="left">
		</div> -->
		<div id="right">
			<!-- Lägger till menyn (#sidebar) -->
			<div id="sidebar">
				<?php require './assets/includes/nav.php'; ?> 
				<table id="tools">					
					
				</table>
			</div>
		</div>
		
		<div id="footer"></div>	
		
	</body>
<html>