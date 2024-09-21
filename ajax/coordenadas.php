<?php
//error_reporting(0);
//header ('Content-type: text/html; charset=utf-8');
$session_lifetime = 3600 * 24 * 4; // 4 days
session_set_cookie_params ($session_lifetime);
session_start();
require '../conexion_db.php';
require '../functions.php';
comprobar_login_ajax();
require "../includes/session.php";

if($usr_nivel == "4"){
    $statement = $con ->prepare('SELECT * FROM coordinacion_dems WHERE id_usuario = "'.$usr_usuario.'"');
    $statement->execute();
    $found = $statement->fetch();
    /*if($found)*/if(isset($_POST['user_dems'])){
        $incoming_user_dems = $_POST['user_dems'];
    } else{
        $statement = $con ->prepare('SELECT demarcaciones FROM coordinacion_dems WHERE id_usuario = "'.$usr_usuario.'"');
        $statement->execute();
        $user_dems = $statement->fetch();
        $user_dems = $user_dems[0];

        $incoming_user_dems = substr($user_dems, 0, -1);

    }
} else {
    $incoming_user_dems = isset($_POST['user_dems']) ? $_POST['user_dems'] : "";
}



if($usr_nivel != "8"){
    $incoming_reg = $_POST['registro'];
    $incoming_af = $_POST['afiliacion'];
} else {
    $incoming_reg = "1";
    $incoming_af = "1";
}

$incoming_mun = $_POST['municipio'];
$incoming_dem = $_POST['demarc'];
$incoming_zona = $_POST['zona'];
$incoming_sec = $_POST['seccion'];
$incoming_col = $_POST['colonia'];


switch($usr_nivel){
    case "1":
    case "2":
        $sql_paginas = 'SELECT p.colonia, p.afiliacion, p.ciudad, p.seccion, p.demarcacion, p.lat, p.lng FROM persona p INNER JOIN municipio m WHERE 1 ';
        break;
    case "3":
        $sql_paginas = 'SELECT p.colonia, p.afiliacion, p.ciudad, p.seccion, p.demarcacion, p.lat, p.lng FROM persona p INNER JOIN municipio m WHERE p.ciudad = m.municipio AND m.id_municipio =  "'.$usr_mun.'" ';
        break;
    case "5":
        $sql_paginas = 'SELECT p.colonia, p.afiliacion, p.ciudad, p.seccion, p.demarcacion, p.lat, p.lng FROM persona p INNER JOIN municipio m WHERE p.ciudad = m.municipio AND m.id_municipio = "'.$usr_mun.'" AND p.demarcacion = "'.$usr_dem.'" ';
        break;
    case "6":
        $sql_paginas = 'SELECT p.colonia, p.afiliacion, p.ciudad, p.seccion, p.demarcacion, p.lat, p.lng FROM persona p INNER JOIN municipio m WHERE p.ciudad = m.municipio AND m.id_municipio = "'.$usr_mun.'" AND p.demarcacion = "'.$usr_dem.'" AND zona = "'.$usr_zona.'" ';
        break;
    case "4":
        $sql_paginas = 'SELECT p.colonia, p.afiliacion, p.ciudad, p.seccion, p.demarcacion, p.lat, p.lng FROM persona p INNER JOIN municipio m WHERE p.ciudad = m.municipio AND m.id_municipio =  "'.$usr_mun.'" AND p.seccion IN ('.substr($incoming_user_dems,0,-1).') ';
        break;
    case "7":
    case "8":
        $sql_paginas = 'SELECT p.colonia, p.afiliacion, p.ciudad, p.seccion, p.demarcacion, p.lat, p.lng FROM persona p WHERE p.id_usuario = "'.$usr_usuario.'" ';
        break;
}

//Tipo de registro
$registro = $incoming_reg == "0" ? ' AND (p.cve_elec = "0" OR p.cve_elec = "")'  : 'AND p.cve_elec != "0"';

//Afiliacion
$incoming_af = isset($incoming_af) ? $incoming_af : "2";
$afiliacion = $incoming_af == 2 || $incoming_af == "- Afiliado -" ? "" : 'AND p.afiliacion =  "'.$incoming_af.'"';

//Municipio
$incoming_mun = isset($incoming_mun) ? $incoming_mun : "0";
$municipio = $incoming_mun == 0 || $incoming_mun == "- Municipio -" ? "" : 'AND p.ciudad =  "'.$incoming_mun.'"';

//Colonia
$incoming_col = isset($incoming_col) ? $incoming_col = strtolower($incoming_col) : "0";
$colonia = $incoming_col == 0 || $incoming_col == "- colonia -" ? "" : 'AND LOWER(p.colonia) LIKE "%'.$incoming_col.'%"';

//Demarcación
$incoming_dem = isset($incoming_dem) ? $incoming_dem : "0";
$demarc = $incoming_dem == 0 || $incoming_dem == "" ? "" : 'AND demarcacion = "'.$incoming_dem.'"';

//Zona
$incoming_zona = isset($incoming_zona) ? $incoming_zona : "0";
$zona = $incoming_zona == 0 || $incoming_zona == "" ? "" : 'AND zona = "'.$incoming_zona.'"';

//Seccion
$incoming_sec = isset($incoming_sec) ? $incoming_sec : "0";
$seccion = $incoming_sec == 0 || $incoming_sec == "" ? "" : 'AND seccion = "'.$incoming_sec.'"';

if(!$con){
    $respuesta = ['error' => true];
} else {
    $statement = $con->prepare($sql_paginas.$municipio.' '.$afiliacion.' '.$colonia.' '.$seccion.' '.$registro.' '.$demarc.' '.$zona);
    
    //echo $sql_paginas.$municipio.' '.$afiliacion.' '.$colonia.' '.$seccion.' '.$registro.' '.$demarc.' '.$zona;
    
        $statement->execute();
        $coordenadas = $statement->fetchAll();
    $respuesta = [];

    foreach($coordenadas as $coordenada){
        $coord = [
            'afiliacion' => $coordenada['afiliacion'],
            'colonia' => $coordenada['colonia'],
            'ciudad' => $coordenada['ciudad'],
            'seccion' => $coordenada['seccion'],
            'demarcacion' => $coordenada['demarcacion'],
            'lat' => $coordenada['lat'],
            'lng' => $coordenada['lng']
        ];
        array_push($respuesta, $coord);
    }
}

echo json_encode($respuesta);
