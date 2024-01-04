<?php
require("session.php");
require("db_connect.php");

$user_id = $session_user['id'];

$user_query = "SELECT * FROM users WHERE id = {$user_id}";
$user_result = mysqli_query($conn, $user_query);
$user = mysqli_fetch_assoc($user_result);

$sugar_query = "SELECT * FROM current_sugar WHERE user_id = {$user_id} ORDER BY time DESC LIMIT 1";
$sugar_result = mysqli_query($conn, $sugar_query);
$sugar = mysqli_fetch_assoc($sugar_result);

if(isset($_POST['update_profile'])) {
    $name = $_POST["name"];
    $password = $_POST["password"];

    $update_user_query = "UPDATE users SET name = '$name', password = '$password' WHERE id = $user_id";
    mysqli_query($conn, $update_user_query);
    header("Location: user.php");
} elseif(isset($_POST['update_sugar_level'])) {
    $new_sugar_level = $_POST["sugar_level"];
    $insert_sugar_query = "UPDATE current_sugar SET sugar_level = '$new_sugar_level', time = NOW() WHERE user_id = $user_id";
    mysqli_query($conn, $insert_sugar_query);

    if($new_sugar_level < 5.4) {
        echo "Ваш уровень сахара низкий. Рекомендуется обратиться к врачу.";
        $result = mysqli_query($conn, "CALL dbscan(0.2, 2)");
    } elseif ($new_sugar_level >= 5.4 && $new_sugar_level <= 8.4) {
        echo "Ваш уровень сахара находится в норме. Рекомендуется поддерживать правильное питание.";
        $result = mysqli_query($conn, "CALL dbscan(0.02, 5)");
    } else {
        echo "Ваш уровень сахара высокий. Рекомендуется обратиться к врачу.";
        $result = mysqli_query($conn, "CALL dbscan(1, 7)");
    }
    header("Location: advice.php?user_id=$user_id&new_sugar_level=$new_sugar_level");
}

if(isset($_POST['update_likes'])) {
    $favourite_category = $_POST['favourite_category'];
    $unloved_category = $_POST['unloved_category'];

    $update_likes_query = "UPDATE likes SET favourite = $favourite_category, unloved = $unloved_category WHERE user_id = $user_id";
    mysqli_query($conn, $update_likes_query);
}

function getCategoryName($conn, $categoryId) {
    $category_query = "SELECT name FROM categories WHERE id = $categoryId";
    $category_result = mysqli_query($conn, $category_query);
    $category = mysqli_fetch_assoc($category_result);
    return $category['name'];
}

$title = "Личный кабинет";
$content = "
<h2>Личный кабинет</h2>

<!-- Форма для обновления профиля -->
<form method=\"POST\">
    <h3>Обновить информацию пользователя</h3>
    <div>
        <label>ФИО</label>
        <input type=\"text\" name=\"name\" value=\"{$user['name']}\" required>
    </div>
    
    <div>
        <label>Пароль</label>
        <input type=\"password\" name=\"password\" required>
    </div>
    
    <div>
        <button type=\"submit\" name=\"update_profile\">Сохранить</button>
    </div>
</form>

<form method=\"POST\">
    <h3>Обновить уровень сахара</h3>
    <div>
        <label for=\"sugar_level\">Новый уровень сахара</label>
        <input type=\"number\" name=\"sugar_level\" step=\"0.1\" required>
    </div>
    
    <div>
        <button type=\"submit\" name=\"update_sugar_level\">Сохранить</button>
    </div>
</form>
";

$content .= '
<form method="POST">
    <h3>Выберите любимые и нелюбимые категории продуктов</h3>
    <div>
        <label>Любимая категория:</label>
        <select name="favourite_category">
            <option value="">Выберите категорию</option>';
            $categories_query = "SELECT * FROM categories";
            $categories_result = mysqli_query($conn, $categories_query);
            while ($category = mysqli_fetch_assoc($categories_result)) {
                $content .= "<option value=\"{$category['id']}\">{$category['name']}</option>";
            }
    $content .= '</select>
    </div>
    <div>
        <label>Нелюбимая категория:</label>
        <select name="unloved_category">
            <option value="">Выберите категорию</option>';
            mysqli_data_seek($categories_result, 0);
            while ($category = mysqli_fetch_assoc($categories_result)) {
                $content .= "<option value=\"{$category['id']}\">{$category['name']}</option>";
            }
    $content .= '</select>
    </div>
    <div>
        <button type="submit" name="update_likes">Сохранить предпочтения</button>
    </div>
</form>';

require("template.php");
?>