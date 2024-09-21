<?php
//error_reporting(0);
//header ('Content-type: text/html; charset=utf-8');
$session_lifetime = 3600 * 24 * 4; // 4 days
session_set_cookie_params ($session_lifetime);
session_start();
require '../conexion_db.php';
require '../functions.php';
comprobar_login();
comprobar_login_ajax();
    
$incoming_mun = $_POST['mun'];

if(!$con){
    $respuesta = ['error' => true];
} else {
    
    //LISTA DE LOCALIDADES
    
    //$statement = $con->prepare('SELECT d.id_dem_secc, d.dem, d.secc, d.id_municipio, m.municipio FROM dem_secc d INNER JOIN municipio m WHERE d.id_municipio = m.id_municipio;');
    $statement = $con->prepare('SELECT DISTINCT d.dem FROM dem_secc d inner join municipio m where d.id_municipio = m.id_municipio AND m.municipio ="'.$incoming_mun.'";');
    $statement->execute();
    $demarcaciones = $statement->fetchAll();
    
    $respuesta = [];

    foreach($demarcaciones as $demarcacion){
        $dem = [
            'dem' => $demarcacion['dem'],
        ];
        array_push($respuesta, $dem);
    }
}

echo json_encode($respuesta);
