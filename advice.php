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

$title = "Возможно вы захотите перекусить этими продуктами:";
$product_data = "<table><tr><th>Название продукта</th><th>Индекс насыщения</th></tr>";
$favourite_present = false;

while ($row = mysqli_fetch_assoc($selected_food_result)) {
    if (checkIfFavourite($conn, $row['category'], $user_id)) {
        $favourite_present = true;
    }
    $product_data .= "<tr><td>" . $row['name'] . "</td><td>" . $row['satiety_index'] . "</td></tr>";
}

if (!$favourite_present) {
    // Добавляем любимый продукт, так как он отсутствует в списке
    $favourite_product_query = "SELECT * FROM food WHERE category = (SELECT favourite FROM likes WHERE user_id = $user_id) LIMIT 1";
    $favourite_product_result = mysqli_query($conn, $favourite_product_query);
    $favourite_product = mysqli_fetch_assoc($favourite_product_result);
    $product_data .= "<tr><td>" . $favourite_product['name'] . "</td><td>" . $favourite_product['satiety_index'] . "</td></tr>";
}

$product_data .= "</table>";

$content = $product_data;
require("template.php");

function checkIfFavourite($conn, $category_id, $user_id) {
    $check_favourite_query = "SELECT * FROM likes WHERE user_id = $user_id AND favourite = $category_id";
    $check_favourite_result = mysqli_query($conn, $check_favourite_query);
    return mysqli_num_rows($check_favourite_result) > 0;
}
?>
