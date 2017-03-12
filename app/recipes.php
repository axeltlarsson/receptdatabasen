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
	<link rel="stylesheet" type="text/css" href="./assets/css/recipes.css" />
        <link rel="stylesheet" type="text/css" href="./assets/css/recipe.css" /> <!-- för wide-mode -->
	<!-- Sidscpecifik jQuery -->
	<script defer type="text/javascript" src="./assets/js/jquery.recipes.js"></script>
	<script defer src="./assets/js/jquery.datasort.js"></script>
	<script defer src="./assets/js/jquery.searchDatabase.js"></script>
	<script defer src="./assets/js/jquery.indexify.js"></script>

</head>
	<body>
        <div id="container">
            <div id="list">

            <?php // visar receptlistan
                /*-------------------------------------------------
                    Koppla upp mot databasen
                -------------------------------------------------*/
                $db = connectToDb();

                try {
                    // Förbered SQL-statement
                    $getRecipes = $db->prepare("SELECT Title, DateCreated, DateUpdated FROM Recipes");

                    // Ställ in PDO att hämta associativ array för $getRecipes
                    $getRecipes->setFetchMode(PDO::FETCH_ASSOC);

                    // Exekvera SQL-statement
                    $getRecipes->execute();

                    // Printa ut table
                    echo '<table id="recipes">';
                        echo '<thead>';
                        echo '</thead>';
                        echo '<tbody>';
                            /*---------------------------------------------------
                                Gå igenom varje recept och ladda in som objekt
                            -----------------------------------------------------*/
                            while ($recipe = $getRecipes->fetch()) {
                                $title = $recipe['Title'];
                                $dateCreated = $recipe['DateCreated'];
                                $dateUpdated = $recipe['DateUpdated'];

                                // enkapsulera med klass recipeTitle
                                echo '<tr>';
                                    echo '<td class="recipeTitle" data-date-created="' . $dateCreated . '" data-date-updated="' . $dateUpdated . '">';
                                        // Länk till receptet
                                        echo "<a href='recipe?title=$title'>".  $title . '</a>';
                                    echo '</td>';
                                echo '</tr>';
                            }

                            // #widener används för få bredden på #list alltid till samma även när man har sökt på recept och träffarna har korta titlar
                            echo '<tr id="widener"><td data-date-created="1869-09-01 23:45:01" data-date-updated="1869-09-01 23:45:01">Widener Widener Widener Widener Widener Widener Widener Widener Widener Widener Widener Widener Widener Widener Widener Widener Widener Widener Widener Widener Widener</td></tr>';
                        echo '</tbody>';
                    echo '</table>';



                } catch (PDOException $ex) {
                    echo '<span class="error">Ett fel inträffade.</span>';
                    logger($ex->getMessage());
                }
            ?>
        </div>


            <!-- Lägger till menyn (#sidebar) -->
            <div id="sidebar">
                <?php require './assets/includes/nav.php'; ?>

                <ul id="tools">
                    <li id="search">
                        <span class="icon">\ </span>
                        <?php
                            // Ta reda på antalet recept i databasen
                            $getNbrOfRecipes = $db->prepare("SELECT P_id FROM Recipes");
                            $getNbrOfRecipes->setFetchMode(PDO::FETCH_NUM);
                            $getNbrOfRecipes->execute();
                            $row_count = $getNbrOfRecipes->rowCount();

                            if (isset($_GET['searchQuery'])) {

                                    $title = $_GET['searchQuery'];
                                    echo '<input id="searchBox" placeholder="Sök bland ' . $row_count .  ' recept" value="' . $title . '"/>';
                            } else {
                                echo '<input id="searchBox" placeholder="Sök bland ' . $row_count .  ' recept"/>';
                            }

                        ?>
                    </li>
                    <ul id="advancedSearch">
                        <li title="Sök på receptens namn">
                            <input type="checkbox" id="searchTitle" checked> namn</input>
                        </li>
                        <li title="Sök på receptens taggar">
                            <input type="checkbox" id="searchTags" checked> taggar</input>
                        </li>
                        <li title="Sök på receptens ingredienser">
                            <input type="checkbox" id="searchIngredients" checked> ingredienser</input>
                        </li>
                    </ul>
                    <li id="sort">
                        <span class="icon">D </span><a>Sortera på:</a>
                    </li>
                    <ul id="sortingMethod">
                        <li id="titleSort" style="display: none">
                            <span class="icon inactive">í </span><a>Namn</a>
                        </li>
                         <li id="dateCreatedSort" style="display: none">
                            <span class="icon inactive">í </span><a>Datum skapat</a>
                        </li>
                         <li id="dateUpdatedSort" style="display: none">
                            <span class="icon inactive">í </span><a>Datum uppdaterat</a>
                        </li>
                    </ul>
                    <li>
                        <a style="display: none" id="tagCloud">Taggmoln</a>
                    </li>
                </ul>

                </div>
        </div>

	</body>
<html>
