<?php require_once 'assets/includes/global.inc.php'; ?>
<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="refresh" content="0; url=/recipes" />
    <?php require_once 'assets/includes/head.inc.php'; ?>
</head>

<body>
    <div id="header"></div>
    <div id="container">
        <!-- Dela upp sidan i tre kolumner -->
        <div id="left">
            <p>Här kommer det kanske att finnas en random bild från ett random recept.
            </p>
        </div>
        <div id="center">
            <h1>Välkommen till receptdatabasen!</h1>
        </div>
        <div id="right">
        <!-- Lägger till menyn (#sidebar) -->
        <div id="sidebar">
            <?php require './assets/includes/nav.php'; ?>
            <table id="tools">
                <tr>
                    <td>
                        <div class="icon"></div>
                    </td>
                    <td>
                        <a href="#"></a>
                    </td>
                </tr>
            </table>
        </div>
        <div id="gallery">Här kommer kanske random litet galleri.</div>
    </div>
    </div>
    <div id="footer"></div>
</body>

</html>