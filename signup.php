<?php
require("db_connect.php");
require("session.php");
require("category_selector.php");

if (!empty($_POST)) {
    $name = $_POST["name"];
    $login = $_POST["login"];
    $password = $_POST["password"];
    $sugar_level = $_POST["sugar_level"];
    $favourite = $_POST["favourite"];
    $unloved = $_POST["unloved"];

    $result = mysqli_query($conn, "SELECT * FROM users WHERE login=\"" . $login . "\"");

    if (mysqli_num_rows($result) == 0) {
        mysqli_query($conn, "INSERT INTO users (name, login, password) VALUES (\"" . $name . "\", \"" . $login . "\", \"" . $password . "\")");
        $user_id = mysqli_insert_id($conn);

        if (!empty($sugar_level)) {
            mysqli_query($conn, "INSERT INTO current_sugar (user_id, sugar_level, time) VALUES ($user_id, $sugar_level, NOW())");
        }
        else {
            mysqli_query($conn, "INSERT INTO current_sugar (user_id, sugar_level, time) VALUES ($user_id, 0, NOW())");
        }

        if (!empty($favourite) || !empty($unloved)) {
            mysqli_query($conn, "INSERT INTO likes (user_id, favourite, unloved) VALUES ($user_id, $favourite, $unloved)");
        }
        else {
            mysqli_query($conn, "INSERT INTO likes (user_id, favourite, unloved) VALUES ($user_id, '1', '6')"); 
        }

        header("Location: allfood.php?user_id=" . $user_id);
    } else {
        echo "Пользователь с таким логином уже существует";
    }
}

$title = "Регистрация";

$content = "
    <form method=\"POST\">
        <div>
            <label style='display: flex; padding: 0 0 0 660px;'>Как к вам обращаться?</label>
            <input type=\"text\" name=\"name\" required>
        </div>
        
        <div>
            <label style='display: flex; padding: 0 0 0 660px;'>Логин</label>
            <input type=\"text\" name=\"login\" required>
        </div>
        
        <div>
            <label style='display: flex; padding: 0 0 0 660px;'>Пароль</label>
            <input type=\"password\" name=\"password\" required>
        </div>
        
        <div>
            <label style='display: flex; padding: 0 0 0 660px;'>Уровень сахара</label>
            <input type=\"number\" name=\"sugar_level\" step=\"0.1\">
        </div>
        
        <div>
            <label style='display: flex; padding: 0 0 0 660px;'>Любимая категория</label>
            <select name=\"favourite\">
                <option value=\"\">Не выбрано</option>
                " . getCategoryOptions($conn) . "
            </select>
        </div>
        
        <div>
            <label style='display: flex; padding: 0 0 0 660px;'>Нелюбимая категория</label>
            <select name=\"unloved\">
                <option value=\"\">Не выбрано</option>
                " . getCategoryOptions($conn) . "
            </select>
        </div>

        <div>
            <button type=\"submit\">Регистрация</button>
        </div>
    </form>
";

require("template.php");
?>
