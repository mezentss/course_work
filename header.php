<div id="header">
<div class="logo-container">
        <img src="images\logo.png" alt="Логотип" class="logo">
    </div>
    <ul id="menu">
        <li><a href="allfood.php">Категории</a></li>
        <?php if(isset($pageid) && $session_user['id'] == $page["user_id"]):?>
            <li><a href="create_update.php?id=<?=$pageid?>">Редактировать</a></li>
        <?php endif;?>
        <?php if(isset($session_user['id'])) :?>
            <li><a href="user.php">Кабинет</a></li>
            <li><a href="exit.php">Выход</a></li>
        <?php else: ?>
            <li><a href="auth.php">Вход</a></li>
            <li><a href="signup.php">Регистрация</a></li>
        <?php endif;?>
    </ul>
</div>
