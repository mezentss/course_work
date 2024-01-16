<?php
require("session.php");
require("db_connect.php");
require("category_selector.php");
require("facts.php");

$user_id = $session_user['id'];
$sugarLevelInfo = [];

$user_query = "SELECT * FROM users WHERE id = {$user_id}";
$user_result = mysqli_query($conn, $user_query);
$user = mysqli_fetch_assoc($user_result);

$sugar_query = "SELECT * FROM current_sugar WHERE user_id = {$user_id} ORDER BY time DESC LIMIT 1";
$sugar_result = mysqli_query($conn, $sugar_query);
$sugar = mysqli_fetch_assoc($sugar_result);

$likes_fv_query = "SELECT * FROM likes JOIN categories ON likes.favourite = categories.id WHERE user_id = {$user_id}";
$likes_fv_result = mysqli_query($conn, $likes_fv_query);
$likes_fv = mysqli_fetch_assoc($likes_fv_result);

$likes_un_query = "SELECT * FROM likes JOIN categories ON likes.unloved = categories.id WHERE user_id = {$user_id}";
$likes_un_result = mysqli_query($conn, $likes_un_query);
$likes_un = mysqli_fetch_assoc($likes_un_result);

$sugarLevels = [
    "low" => [
        "image" => "images\low_sugar_image.png",
        "text" => "Ваш уровень сахара низкий!",
    ],
    "medium" => [
        "image" => "images\medium_sugar_image.png",
        "text" => "Ваш уровень сахара в пределах нормы!",
    ],
    "high" => [
        "image" => "images\high_sugar_image.png",
        "text" => "Ваш уровень сахара высокий!",
    ]
];

if($sugar['sugar_level'] < 5.4) {
    $sugarLevelInfo = $sugarLevels['low'];
} elseif ($sugar['sugar_level'] >= 5.4 && $sugar['sugar_level'] <= 8.4) {
    $sugarLevelInfo = $sugarLevels['medium'];
} else {
    $sugarLevelInfo = $sugarLevels['high'];
}

if(isset($_POST['update_profile'])) {
    $name = $_POST["name"];
    $login = $_POST["login"];
    $password = $_POST["password"];


    $update_user_query = "UPDATE users SET name = '$name', login = '$login', password = '$password' WHERE id = $user_id";
    mysqli_query($conn, $update_user_query);
    header("Location: user.php");
} elseif(isset($_POST['update_sugar_level'])) {
    $new_sugar_level = $_POST["sugar_level"];
    $insert_sugar_query = "UPDATE current_sugar SET sugar_level = '$new_sugar_level', time = NOW() WHERE user_id = $user_id";
    mysqli_query($conn, $insert_sugar_query);
    
    if($new_sugar_level < 5.4) {
        $result = mysqli_query($conn, "CALL dbscan(0.8, 15)");
    } elseif ($new_sugar_level >= 5.4 && $new_sugar_level <= 8.4) {
        $result = mysqli_query($conn, "CALL dbscan(0.5, 10)");
    }
     else {
        $result = mysqli_query($conn, "CALL dbscan(0.02, 10)");
    }
    header("Location: advice.php?user_id=$user_id&new_sugar_level=$new_sugar_level");
}

if(isset($_POST['update_likes'])) {
    $favourite_category = $_POST['favourite_category'];
    $unloved_category = $_POST['unloved_category'];

    $update_likes_query = "UPDATE likes SET favourite = $favourite_category, unloved = $unloved_category WHERE user_id = $user_id";
    mysqli_query($conn, $update_likes_query);
    header("Location: user.php");
}

$randomFact = get_random_fact(); 

$title = "Личный кабинет";

$content = '
<div style=" display: flex; text-align: center;">
    <div style="padding: 0 150px 0 100px; display: flex; ">
        <form method="POST">
            <h2>Личные данные</h2>
            <div style="padding: 0 20px;">
                <label>Пользователь</label>
                <input type="text" name="name" value="'.$user['name'].'" required>
            </div>
            <div>
            <label>Логин</label>
            <input type="text" login="login" value="'.$user['login'].'" required>
            </div>
            <div>
                <label>Пароль</label>
                <input type="password" name="password" required>
            </div>
            <div>
                <button type="submit" name="update_profile">Сохранить</button>
            </div>
        </form>
        <form method="POST">
            <h2 style="padding: 0 20px;">Категории продуктов</h2>
            <div>
                <label>Любимая категория:</label>
                <select name="favourite_category">
                    <option value= ""> '.$likes_fv['name']. '</option>
                    ' . getCategoryOptions($conn) . '
                </select>
            </div>
            <div>
                <label>Нелюбимая категория:</label>
                <select name="unloved_category">
                    <option value= ""> '.$likes_un['name']. '</option>
                    ' . getCategoryOptions($conn) . '
                </select>
            </div>
            <div>
                <button type="submit" name="update_likes">Сохранить</button>
            </div>
        </form>
    </div>
<div>
<form method="POST">
<h2 style="padding: 0 20px;">Уровень сахара</h2>
<div style="display: flex;">
    <div style="display: block;">
        <label>Нынешее значение:</label>
        <input type="number" name="sugar_level" value='.$sugar['sugar_level'].' step="0.1" required>
        <button type="submit" name="update_sugar_level">Сохранить</button>
    </div>    
    <div style="display: block; padding: 0 0 0 150px">
        <img src="' . $sugarLevelInfo['image'] . '" alt="Уровень сахара" style="max-width: 200px;">
        <p>' . $sugarLevelInfo['text'] . '</p>
    </div>
</div>
<h2>Интересный факт:</h2>
<div style="width: 600px;">
<p>' .$randomFact. '</p>
</div>
</form>
</div>

</form>';

require("template.php");
?>