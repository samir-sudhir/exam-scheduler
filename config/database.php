<?php
$host = 'localhost'; // Change this to your DB host
$db = 'a_4'; // Change this to your DB name
$user = 'root'; // DB user
$pass = ''; // DB password

$mysqli = new mysqli($host, $user, $pass, $db);

if ($mysqli->connect_error) {
    die('Connection Failed: ' . $mysqli->connect_error);
}

?>
