<?php
//error_reporting(0);
//header ('Content-type: text/html; charset=utf-8');
$session_lifetime = 3600 * 24 * 4; // 4 days
session_set_cookie_params ($session_lifetime);
session_start();
require '../conexion_db.php';
require '../functions.php';
comprobar_login_ajax();

$incoming_mun = $_POST['mun'];
$incoming_dem = $_POST['dem'];
//$incoming_secc = $_POST['secc'];
$incoming_zona = $_POST['zona'];
$zona_sql = ' ';
//echo $incoming_mun;
if($incoming_mun != '0'){
    //echo '1324567uiy5t43r';
    $zona_sql = ' zona = "'.$incoming_zona.'" ';
} else{
    $zona_sql = ' 1 ';
}

if($incoming_zona == "0"){
    $zona_sql = ' 1 ';
}
/**/
$mun_pattern = '/^[0-9]{2}$/';
$incoming_mun = preg_match($mun_pattern, $incoming_mun) ? $incoming_mun : 0;

if($incoming_mun == 0){
    $statement = $con->prepare('SELECT id_municipio FROM municipio WHERE municipio = "'.$_POST['mun'].'"');
    $statement->execute();
    $mun_temp = $statement->fetch();
    $incoming_mun = $mun_temp['id_municipio'];
}
/**/


if(!$con){
    $respuesta = ['error' => true];
} else {
    
    //LISTA DE PROMOTORES
    //$sql = 'SELECT id_usuario, nombre, apellidos, id_nivel_usuario FROM usuario WHERE seccion = "'.$incoming_secc.'"'.$zona_sql.'AND dem = "'.$incoming_dem.'" AND municipio ="'.$incoming_mun.'" AND id_nivel_usuario = "7" ORDER BY nombre ASC';
    
    
    $sql = 'SELECT id_usuario, nombre, apellidos, id_nivel_usuario FROM usuario WHERE '.$zona_sql.'AND dem = "'.$incoming_dem.'" AND municipio ="'.$incoming_mun.'" AND id_nivel_usuario = "7" ORDER BY nombre ASC';
    
    
    $statement = $con->prepare($sql);
    $statement->execute();
    $promotores = $statement->fetchAll();
    
    $respuesta = [];

    foreach($promotores as $promotor){
        $promo = [
            'id_usuario' => $promotor['id_usuario'],
            'nombre' => $promotor['nombre'].' '.$promotor['apellidos'],
            'apellidos' => $promotor['apellidos'],
        ];
        array_push($respuesta, $promo);
    }
}
//echo $sql;
echo json_encode($respuesta);
