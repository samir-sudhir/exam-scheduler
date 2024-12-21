<?php
    include_once('../e_v/config/database.php');

    session_start();
    session_unset();
    session_destroy();

    header('Location: ../e_v/sign_in.php');
    exit();
?>
