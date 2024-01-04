<!DOCTYPE html>
<html>
    <head>
        <title><?= $title ?></title>
        <meta charset="utf-8" />
        <link rel="stylesheet" href="style.css" />
    </head>
    <body>
        <?php require("header.php"); ?>
        <div id="content">
            <h1><?= $title ?></h1>
            <div>
                <?= $content ?>
            </div>
        </div>
        <?php require("footer.php"); ?>
    </body>
</html>
