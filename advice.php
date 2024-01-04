<?php
require("session.php");
require("db_connect.php");
$user_id = ($session_user)['id'];

$sugar_level_query = "SELECT sugar_level FROM current_sugar WHERE user_id = $user_id";
$sugar_level_result = mysqli_query($conn, $sugar_level_query);
$sugar_level = mysqli_fetch_assoc($sugar_level_result)['sugar_level'];

$selected_cluster_id_query = "SELECT cluster_id FROM food ORDER BY satiety_index DESC LIMIT 1";
if ($sugar_level < 5.4) {
    $selected_cluster_id_query = "SELECT cluster_id FROM food ORDER BY satiety_index DESC LIMIT 1";
} elseif ($sugar_level > 8.4) {
    $selected_cluster_id_query = "SELECT cluster_id FROM food ORDER BY satiety_index ASC LIMIT 1";
}

$selected_cluster_id_result = mysqli_query($conn, $selected_cluster_id_query);
$selected_cluster_id = mysqli_fetch_assoc($selected_cluster_id_result)['cluster_id'];

$selected_food_query = "SELECT * FROM food WHERE cluster_id = $selected_cluster_id";
$selected_food_result = mysqli_query($conn, $selected_food_query);

$product_data = "";

while ($row = mysqli_fetch_assoc($selected_food_result)) {
    $product_data .= "Name: " . $row['name'] . ", Satiety Index: " . $row['satiety_index'] . "<br>";
}

$title = "Recommended Products";
$content = $product_data;
require("template.php");
?>
