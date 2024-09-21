<?php
$session_lifetime = 3600 * 24 * 4; // 4 days
session_set_cookie_params ($session_lifetime);
session_start();
require '../conexion_db.php';
require '../functions.php';
//comprobar_nivel_ajax();
comprobar_login_ajax();
/*$usr_usuario = $_SESSION['usuario'];
$usr_nombre = $_SESSION['nombre'];
$usr_apellidos = $_SESSION['apellidos'];
$usr_user = $_SESSION['user'];
$usr_foto = $_SESSION['foto'];
$usr_nivel = strval($_SESSION['id_nivel_user']);
$usr_mun = strval($_SESSION['municipio']);
$usr_dem = strval($_SESSION['demarcacion']);
$usr_zona = strval($_SESSION['zona']);*/
require "../includes/session.php";

//DECLARACIÓN DE VARIABLES Y CASTEO DE POST
$row_persona = "";

$incoming_sec = $_POST["seccion"];
$edad = $sexo = $afiliacion = $municipio = $colonia = $seccion = "";
$contador = 1;
//Definición de parametros de la consulta SQL

//Seccion
try{
    $incoming_sec = isset($incoming_sec) ? $incoming_sec : "0";

    require "../includes/switch-sql.php";

    $seccion = $incoming_sec == 0 || $incoming_sec == "" ? "" : 'seccion LIKE "%'.$incoming_sec.'%" GROUP BY colonia';

    //sql para obtener datos, edad la calcula con deciles ej 31.895
    $statement = $con->prepare('SELECT p.colonia, p.ciudad, p.estado, p.seccion from persona p '.$extra_sql.' AND  '.$seccion.';');
    $statement->execute();
    $colonias = $statement->fetchAll();
    foreach($colonias as $colonia){
        //Obtiene la edad entera sin puntos decimales -> ej 31.895 = 31    
        $row_persona = $row_persona.'<a href="https://www.google.com/maps/place/'.$colonia['colonia'].',+'.$colonia['ciudad'].',+'.$colonia['estado'].'" target="_blank"><p class="colonia"> '.$colonia['colonia'].'<span class="ciudad">, '.$colonia['ciudad'].' | </span></p></a>';
    }
} catch(Exception $e){
    $row_persona = '<i class="fa-solid fa-triangle-exclamation"></i><p class="error_seccion">Ingrese una sección</p>';
}

    
echo $row_persona;

?>