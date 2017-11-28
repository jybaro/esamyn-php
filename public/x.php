<?php

require_once('../private/config.php');
require_once('../private/bdd.php');
require_once('../private/utils.php');
$result = q("SELECT * FROM esamyn.esa_usuario ");
foreach($result as $r){
    $id = $r['usu_id'];
    $password = md5($r['usu_cedula']);
    q("UPDATE esamyn.esa_usuario SET usu_password='$password' WHERE usu_id=$id");
    echo "<li>{$r[usu_cedula]}</li>";
}
