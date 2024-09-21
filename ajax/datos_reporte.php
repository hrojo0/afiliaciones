<?php
//error_reporting(0);
//header ('Content-type: text/html; charset=utf-8');
$session_lifetime = 3600 * 24 * 4; // 4 days
session_set_cookie_params ($session_lifetime);
session_start();
require '../conexion_db.php';
require '../functions.php';
//comprobar_login();
/*$usr_nombre = $_SESSION['nombre'];
$usr_apellidos = $_SESSION['apellidos'];
$usr_user = $_SESSION['user'];
$usr_foto = $_SESSION['foto'];
$usr_nivel = $_SESSION['id_nivel_user'];*/
comprobar_login_ajax();
require "../includes/session.php";

if(!$con){
    $respuesta = ['error' => true];
} else {
    $statement = $con->prepare('SELECT colonia, afiliacion, ciudad, seccion, lat, lng FROM persona WHERE 1  '.$municipio.' '.$afiliacion.' '.$colonia.' '.$seccion.';');
        $statement->execute();
        $coordenadas = $statement->fetchAll();
    $respuesta = [];

    foreach($coordenadas as $coordenada){
        $coord = [
            'afiliacion' => $coordenada['afiliacion'],
            'colonia' => $coordenada['colonia'],
            'ciudad' => $coordenada['ciudad'],
            'seccion' => $coordenada['seccion'],
            'lat' => $coordenada['lat'],
            'lng' => $coordenada['lng']
        ];
        array_push($respuesta, $coord);
    }
}

echo json_encode($respuesta);
