<?php
    session_start();
    unset($session_user);
    session_destroy();
    header("Location: signup.php");
?>
