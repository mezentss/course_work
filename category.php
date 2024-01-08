<?php
require("session.php");
require("db_connect.php");

$result = mysqli_query($conn, "SELECT * FROM categories WHERE id = {$_GET['id']}");

if(!$result || mysqli_num_rows($result) == 0){
    echo "В базе данных нет категории с таким id.";
    exit;
}

$category = mysqli_fetch_assoc($result);
$title = "Категория: " . $category['name'];
$content = "<h2>{$category['name']}</h2>";

$food_result = mysqli_query($conn, "SELECT * FROM food WHERE category = {$category['id']}");
if(!$food_result || mysqli_num_rows($food_result) == 0){
    $content .= "<p>К сожалению, в этой категории нет продуктов.</p>";
} else {
    $content .= "<ul>";
    while($food = mysqli_fetch_assoc($food_result)){
        $content .= "<li>{$food['name']} - {$food['satiety_index']}</li>";
    }
    $content .= "</ul>";
}

require("template.php");
?>