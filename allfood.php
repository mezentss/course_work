<?php
require("session.php");
require("db_connect.php");
if (isset($_GET["user_id"])) {
    $_SESSION["user"] = ["id" => $_GET["user_id"]];
    $session_user = $_SESSION["user"];
}

$result = mysqli_query($conn, "SELECT * FROM categories");
$title = "Вся еда";
$content = "<h2>Категории</h2>";

if(!$result || mysqli_num_rows($result) == 0){
    $content .= "В базе данных нет категорий.";
} else {
    while($category = mysqli_fetch_assoc($result)){
        $content .= "<h3><a href='category.php?id={$category['id']}'>{$category['name']}</a></h3>";
    }
}

require("template.php");
?>
