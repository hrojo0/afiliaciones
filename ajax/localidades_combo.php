<?php
//error_reporting(0);
//header ('Content-type: text/html; charset=utf-8');
$session_lifetime = 3600 * 24 * 4; // 4 days
session_set_cookie_params ($session_lifetime);
session_start();
require '../conexion_db.php';
require '../functions.php';
comprobar_login();
//comprobar_login_ajax();

$incoming_cp = $_POST['cp'];

if(!$con){
    $respuesta = ['error' => true];
} else {
    
    //LISTA DE LOCALIDADES
    $sql = 'SELECT * FROM localidad WHERE id_municipio = "18" AND cp = "'.$incoming_cp.'" ORDER BY localidad ASC';
    //echo $sql;
    $statement = $con->prepare($sql);
    $statement->execute();
    $localidades = $statement->fetchAll();
    
    $respuesta = [];

    foreach($localidades as $localidad){
        $loc = [
            'id_localidad' => $localidad['id_localidad'],
            'localidad' => $localidad['localidad'],
            'cp' => $localidad['cp'],
            'id_municipio' => $localidad['id_municipio']
        ];
        array_push($respuesta, $loc);
    }
}
//print_r($respuesta);
echo json_encode($respuesta);
