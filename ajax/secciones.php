<?php
//error_reporting(0);
//header ('Content-type: text/html; charset=utf-8');
$session_lifetime = 3600 * 24 * 4; // 4 days
session_set_cookie_params ($session_lifetime);
session_start();
require '../conexion_db.php';
require '../functions.php';
require '../includes/session.php';
//require '../includes/swtich-sql.php';
comprobar_login_ajax();

$incoming_mun = $_POST['mun'];
$incoming_dem = $_POST['dem'];
$incoming_zona = $_POST['zona'];

if(!$con){
    $respuesta = ['error' => true];
} else {
    
    
    
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
}

   switch($usr_nivel){
        case "1":
        case "2":
        case "3":
        case "5":
        case "6":
        case "8":
            $sql = 'SELECT DISTINCT d.secc FROM dem_secc d INNER JOIN municipio m WHERE d.id_municipio = m.id_municipio AND m.municipio ="'.$incoming_mun.'" AND d.dem = "'.$incoming_dem.'";';
            break;
        case "4":
            $sql = 'SELECT demarcaciones FROM coordinacion_dems WHERE id_usuario = "'.$usr_usuario.'";';
            break;
        case "7":
           $sql = 'SELECT cd.demarcaciones AS secc
                    FROM coordinacion_dems cd
                    JOIN usuario u1 ON cd.id_usuario = u1.id_usuario
                    JOIN usuario u2 ON u1.dem = u2.dem AND u1.zona = u2.zona
                    WHERE u1.id_nivel_usuario = 4
                    AND u2.id_nivel_usuario = 7
                    AND u2.id_usuario = "'.$usr_usuario.'"
                    AND u2.zona = "'.$incoming_zona.'"';
            break;
    }
    
    
    //echo $sql;
    
    
    
    
    
    
    
    //LISTA DE SECCIONES
    //$sql = 'SELECT DISTINCT d.secc FROM dem_secc d INNER JOIN municipio m WHERE d.id_municipio = m.id_municipio AND m.municipio ="'.$incoming_mun.'" AND d.dem = "'.$incoming_dem.'";';
    $statement = $con->prepare($sql);
    $statement->execute();
    $secciones = $statement->fetchAll();
    
    $respuesta = [];

    
    switch($usr_nivel){
        case "1":
        case "2":
            break;
        case "3":
            break;
        case "5":
            break;
        case "6":
            break;
        case "4":
            if(!empty($secciones)){
                $secciones = explode(",",$secciones[0]['demarcaciones']);
                array_walk($secciones, function(&$value) {
                    $value = ['secc' => $value];
                });
                array_pop($secciones);
            }
            break;
        case "7":
            if(!empty($secciones)){
                $secciones = explode(",",$secciones[0]['secc']);
                //array_pop($secciones);
                array_walk($secciones, function(&$value) {
                    $value = ['secc' => $value];
                });
                array_pop($secciones);
            }
            break;
    }
    
    if(!empty($secciones)){
        foreach($secciones as $seccion){
            $secc = [
                'secc' => $seccion['secc'],
            ];
            array_push($respuesta, $secc);
        }
    }
}

echo json_encode($respuesta);
