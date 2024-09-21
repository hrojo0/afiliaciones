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

if(!$con){
    $respuesta = ['error' => true];
} else {
    
    //LISTA DE LOCALIDADES
    $statement = $con->prepare('SELECT l.id_localidad, l.localidad, m.municipio FROM localidad l INNER JOIN municipio m WHERE l.id_municipio = m.id_municipio;');
    $statement->execute();
    $localidades = $statement->fetchAll();
    
    $respuesta = [];

    foreach($localidades as $localidad){
        $loc = [
            'id_localidad' => $localidad['id_localidad'],
            'localidad' => $localidad['localidad'],
            'municipio' => $localidad['municipio']
        ];
        array_push($respuesta, $loc);
    }
}

echo json_encode($respuesta);
