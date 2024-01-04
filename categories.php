<?php
require("session.php");
require("connectdb.php");

$result = mysqli_query($conn, "SELECT * FROM categories");
$title = "Категории";
$content = "<h2>Категории</h2>";

if(!$result || mysqli_num_rows($result) == 0){
    $content .= "В базе данных нет категорий.";
} else {
    $content .= "<ul>";
    while($category = mysqli_fetch_assoc($result)){
        $content .= "<li><a href=\"category.php?id={$category['id']}\">{$category['name']}</a></li>";
    }
    $content .= "</ul>";
}

require("template.php");
?>
