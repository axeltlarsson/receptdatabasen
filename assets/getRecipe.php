<?php 
$title = $_GET['title'];
/*---------------------------------------
    Ladda in receptet från databasen
----------------------------------------*/
require_once 'includes/global.inc.php';
if(!$recipe = loadRecipe($title)) {
    // om det gick snett
    header("HTTP/1.0 404 Not Found");
    header("Location: 404");
    return;
}
/*---------------------------------
    Visa receptet
----------------------------------*/
// Titel
echo '<div id="title">' . $recipe->getTitle() . '</div>';

// Inledning
echo '<div id="intro">' . $recipe->getIntro() . '</div>';

    // Ingredienser
    echo '<div id="ingredientsDiv">';
    foreach($recipe->getSets() as $set) {
        echo '<div class="set"><div class="setHeading">' . $set->getName() . '</div>';
        foreach($set->getIngredients() as $ingredient) {
            echo '<ul>';
                echo '<div class="ingredient"><li>' . $ingredient . '</li></div>';
            echo '</ul>';
        }
        echo '</div>';
    }
echo '</div>';

// Instruktioner
echo '<div id="instructionsDiv">';
    echo '<h3>Instruktioner</h3>';
    echo '<div id="instructions">' . $recipe->getInstructions() . '</div>';
echo '</div>';

// Taggar
echo '<div id="tagsDiv">';
    foreach($recipe->getTags() as $tag) {
        echo '<span class="icon">k</span>';
        echo '<span class="tag">' . $tag . '</span>';
    }

echo '</div>';

?>