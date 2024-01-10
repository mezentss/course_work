<?php
require("session.php");
require("db_connect.php");
require("facts.php");

if (isset($_GET["user_id"])) {
    $_SESSION["user"] = ["id" => $_GET["user_id"]];
    $session_user = $_SESSION["user"];
}

$result = mysqli_query($conn, "SELECT * FROM categories");
$title = "Вся еда";
$content = "<h2>Категории</h2>";

$content .= "<div style='display: flex;'>
                <div style='flex: 1;'>";
if(!$result || mysqli_num_rows($result) == 0){
    $content .= "В базе данных нет категорий.";
} else {
    while($category = mysqli_fetch_assoc($result)){
        $content .= "<h3><a href='category.php?id={$category['id']}'>{$category['name']}</a></h3>";
    }
}

$randomFact = get_random_fact();

$content .= "</div>
            <div style='flex: 1;'>
                <h2>Для вас!</h2>
                <p>$randomFact</p>
            </div>
        </div>";


require("template.php");
?>
