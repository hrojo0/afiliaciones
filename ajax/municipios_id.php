<?php
$session_lifetime = 3600 * 24 * 4; // 4 days
session_set_cookie_params ($session_lifetime);
session_start();
require '../conexion_db.php';
require '../functions.php';
comprobar_login_ajax();
comprobar_nivel_ajax();

$municipios_cp_options = "";
$incoming_mun = $_POST["municipio"];
$municipio = "";

$statement = $con->prepare('SELECT id_municipio, municipio FROM municipio WHERE municipio = "'.$incoming_mun.'"');
$statement->execute();
$municipio_id = $statement->fetch();

echo $municipio_id['id_municipio'];
?>