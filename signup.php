<?php
require("db_connect.php");
require("session.php");

if(!empty($_POST)){
    $name = $_POST["name"];
    $login = $_POST["login"];
    $password = $_POST["password"];
    $sugar_level = $_POST["sugar_level"];
    $favourite = $_POST["favourite"];
    $unloved = $_POST["unloved"];

    $result = mysqli_query($conn, "SELECT * FROM users WHERE login=\"".$login."\"");

    if(mysqli_num_rows($result) == 0){
        mysqli_query($conn, "INSERT INTO users (name, login, password) VALUES (\"".$name."\", \"".$login."\", \"".$password."\")");
        $user_id = mysqli_insert_id($conn); // Получаем user_id после вставки новой записи
        mysqli_query($conn, "INSERT INTO current_sugar (user_id, sugar_level, time) VALUES ($user_id, $sugar_level, NOW())");

        mysqli_query($conn, "INSERT INTO likes (user_id, favourite, unloved) VALUES ($user_id, $favourite, $unloved)");
        header("Location: allfood.php?user_id=".$user_id);
    } else {
        echo "Пользователь с таким логином уже существует";
    }
}

$title = "Регистрация";
$content = "
    <form method=\"POST\">
        <div>
            <label>ФИО</label>
            <input type=\"text\" name=\"name\" required>
        </div>
        
        <div>
            <label>Логин</label>
            <input type=\"text\" name=\"login\" required>
        </div>
        
        <div>
            <label>Пароль</label>
            <input type=\"password\" name=\"password\" required>
        </div>
        
        <div>
            <label>Уровень сахара</label>
            <input type=\"number\" name=\"sugar_level\" step=\"0.1\">
        </div>
        
        <div>
            <label>Выберите любимую категорию</label>
            <select name=\"favourite\">
                <option value=\"2\">Мясо</option>
                <option value=\"3\">Рыба и морепродукты</option>
                <!-- Другие категории -->
            </select>
        </div>
        
        <div>
            <label>Выберите нелюбимую категорию</label>
            <select name=\"unloved\">
                <option value=\"4\">Овощи и зелень</option>
                <option value=\"5\">Фрукты и ягоды</option>
                <!-- Другие категории -->
            </select>
        </div>

        <div>
            <button type=\"submit\">Регистрация</button>
        </div>
    </form>
";

require("template.php");
?>
