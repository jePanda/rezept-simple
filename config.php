<?php
$server = "localhost";
$user = "root";
$pwd = "";
$db = "rezept";

try {
    $con = new PDO('mysql:host='.$server.';dbname='.$db.';charset=utf8', $user, $pwd);
    $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


} catch(Exception $e)
{
    echo 'Fehler '.$e->getCode().': '.$e->getMessage();
}