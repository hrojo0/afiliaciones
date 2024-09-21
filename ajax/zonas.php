<?php
$session_lifetime = 3600 * 24 * 4; // 4 days
session_set_cookie_params ($session_lifetime);
session_start();
require '../conexion_db.php';
require '../functions.php';
comprobar_login_ajax();

$colonias_cp_options = "";
//$incoming_mun = $_POST["mun"];
$incoming_mun = isset($_POST['mun']) ? $_POST["mun"] : '0';
$incoming_municipio = isset($_POST['municipio']) ? $_POST["municipio"] : '0';
$incoming_dem = $_POST["dem"];




if($incoming_mun != '0'){
    $sql = 'SELECT zona FROM usuario WHERE id_nivel_usuario = "5" AND municipio = "'.$incoming_mun.'" AND dem ="'.$incoming_dem.'"';
    
}

if($incoming_municipio != '0'){
    $sq = 'SELECT id_municipio FROM municipio WHERE municipio = "'.$incoming_municipio.'"';
    $statement = $con->prepare($sq);
    $statement->execute();
    $zonas = $statement->fetch();
    
    $sql = 'SELECT zona FROM usuario WHERE id_nivel_usuario = "5" AND municipio = "'.$zonas[0].'" AND dem ="'.$incoming_dem.'"';
}


$statement = $con->prepare($sql);
$statement->execute();
$zonas = $statement->fetch();
$rows = $statement->rowCount();
if($rows == 0){
    $zonas = [0 => "x"];
}


echo $zonas[0];
?>