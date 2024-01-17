<?php
require("db_connect.php");

if(!empty($_POST)){
    $login = $_POST["login"];
    $password = $_POST["password"];

    $result = mysqli_query($conn, "SELECT * FROM users WHERE login='$login' AND password='$password'");

    if($result && mysqli_num_rows($result) > 0){
        session_start();
        $_SESSION["user"] = mysqli_fetch_assoc($result);
        header("Location: user.php");
    } else {
        echo "Неправильный логин или пароль.";
    }
}

$title = "Авторизация";
$content = "
<form method='POST'>
    <div>
        <label>Логин</label>
        <input type='text' name='login' required>
    </div>
    
    <div>
        <label>Пароль</label>
        <input type='password' name='password' required>
    </div>
    
    <div style='text-align: center;'>
    <button type='submit'>Войти</button>
</div>

</form>
";

require("template.php");
?>
