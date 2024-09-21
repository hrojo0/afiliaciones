<?php
//error_reporting(0);
//header ('Content-type: text/html; charset=utf-8');
$session_lifetime = 3600 * 24 * 4; // 4 days
session_set_cookie_params ($session_lifetime);
session_start();
require '../conexion_db.php';
require '../functions.php';
comprobar_login_ajax();

$incoming_cp = $_POST['cp'];
//$incoming_dem = $_POST['dem'];

if(!$con){
    $respuesta = ['error' => true];
} else {    
    
    //LISTA DE LOCALIDADES
    
    //$statement = $con->prepare('SELECT d.id_dem_secc, d.dem, d.secc, d.id_municipio, m.municipio FROM dem_secc d INNER JOIN municipio m WHERE d.id_municipio = m.id_municipio;');
    $sql_colonias_cp = "SELECT colonia FROM `cp_colonia` WHERE cp = '".$incoming_cp."' ORDER BY colonia ASC" ;
    
    $statement = $con->prepare($sql_colonias_cp);
    $statement->execute();
    $colonias_cp = $statement->fetchAll();
    
    $respuesta = [];

    foreach($colonias_cp as $colonia_cp){
        $cols_cp = [
            'colonia' => $colonia_cp['colonia'],
        ];
        array_push($respuesta, $cols_cp);
    }
}

echo json_encode($respuesta);
