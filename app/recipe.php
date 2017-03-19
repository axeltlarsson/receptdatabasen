<?php
/**
 *      Visar ett recept - inladdat från databasen
 *
 *      @name recipe.php
 *
 */

// Ta emot receptets titel som skall visas
$title = $_GET['title'];

require_once 'assets/includes/global.inc.php';

?>
<!DOCTYPE HTML>
<html>
<head>
        <!-- Sidspecifik titel -->
        <title><?php echo $title; ?></title>

        <?php
                require_once 'assets/includes/head.inc.php';
        ?>
        <!-- Sidspecifik CSS -->
        <link rel="stylesheet" type="text/css" href="./assets/css/recipe.css" />
        <link rel="stylesheet" type="text/css" href="./assets/css/lightbox-0.5.css" />
        <link rel="stylesheet" type="text/css" href="./assets/css/loadingAnimations.css" />
        <!-- Sidspecifik jQuery -->
        <script type="text/javascript" src="./assets/js/jquery.recipe.js"></script>
        <script src="./assets/js/jquery.lightbox-0.5.js"></script>


</head>
<body>
    <div id="recipe_center">
        <div id="recipe_container">
        <!-- Dela upp sidan i två kolumner -->
        <main>
            <?php
                /*---------------------------------------
                    Ladda in receptet från databasen
                ----------------------------------------*/
                use League\CommonMark\CommonMarkConverter;
                $markdown = new CommonMarkConverter();

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
                echo '<div id="intro">' . $markdown->convertToHtml($recipe->getIntro()) . '</div>';

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
                    echo '<div id="instructions">' . $markdown->convertToHtml($recipe->getInstructions()) . '</div>';
                echo '</div>';

                // Taggar
                echo '<div id="tagsDiv">';
                    foreach($recipe->getTags() as $tag) {
                        echo '<span class="icon">k</span>';
                        echo '<span class="tag">' . $tag . '</span>';
                    }

                echo '</div>';
            ?>
        </main>
        <div id="recipe_right">
                <!-- Lägger till menyn (#sidebar) -->
                <div id="sidebar">
                        <?php require './assets/includes/nav.php'; ?>

            <ul id="tools">
                <li><span class="icon">ç</span><a id="deleteRecipe">Radera recept</a></li>
                <li><span class="icon">U</span><a id="editRecipe">Ändra recept</a></li>
                <li>
                    <span class="icon">á</span><a id="changeNbrOfPersons">För <?php echo $recipe->getNbrOfPersons() . " " . $recipe->getNoun(); ?></a>
                    <ul style="display: none">
                        <li><span class="icon">z</span><a id="resetNbrOfPersons">Återställ</a></li>
                    </ul>
                </li>
                <li><span class="icon">Å</span><a id="addToShoppinglist">Lägg till i inköpslista</a></li>
            </ul>

                </div>
                <div id="gallery">
                        <?php
                /*-----------------------
                    Ladda in bilderna
                ------------------------*/
                foreach ($recipe->getGallery() as $image) {
                    if ($image instanceof Image) {
                        // Ta reda på variabler
                        $path = $image->getPath();
                        $caption = $image->getCaption();

                        echo '<div class="previewPic">';        // css markup
                        // lightbox
                        echo '<a href="' . $path . '" title="' . $caption . '">';
                            // Generera en thumbnail i mindre storlek
                            try {
                                $thumbnail = resizeImage($path, 700);
                            } catch (Exception $e) {
                                logger($e->getMessage());
                                echo $e->getMessage();
                            }
                            // This is retarded
                            echo '<img src="data:image/jpeg;base64,' . base64_encode($thumbnail) . '" >';
                            if ($caption != "") { // om det finns caption
                                echo '<figcaption>' . $caption .'</figcaption>';        // caption
                            }
                        echo '</a></div>';
                    }
                }
                        ?>
                </div>
        </div>
        </div>
    </div>
</body>
<html>
