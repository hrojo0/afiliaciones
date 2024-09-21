<?php

// Archivo de conexi車n a la DB
include('conexion_db.php');
/*include('functions.php');
comprobar_login();*/
$session_lifetime = 3600 * 24 * 4; // 4 days
session_set_cookie_params ($session_lifetime);
session_start();
require "includes/session.php";
$_SESSION['usuario_registrado'] = 'no';
// Obtener las direcciones con todos sus datos
/*$result = mysqli_query($con, "SELECT * FROM adulto a INNER JOIN coordenada c on a.id_coordenada = c.id_coordenada;");*/
if($usr_nivel != "8"){
    $incoming_reg = $_POST['registro'];
    $incoming_afi = $_POST['afiliacion'];
} else {
    $incoming_reg = "1";
    $incoming_afi = "1";
}

$incoming_mun = $_POST['municipio'];
$incoming_dem = $_POST['demarc'];
$incoming_zona = $_POST['zona'];
$incoming_sec = $_POST['seccion'];
$incoming_col = $_POST['colonia'];

$registro = $afiliacion = $municipio = $colonia = $demarc = $zona = $seccion = "";







/*obtener las demarcaciones del coordinador de dems*/
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
        $sql_filter = 'SELECT p.nombre, p.ap_pat, p.ap_mat, p.calle_num, p.colonia, p.afiliacion, p.ciudad, p.estado, p.seccion, p.demarcacion, p.lat, p.lng FROM persona p INNER JOIN municipio m ON p.ciudad = m.municipio WHERE 1 ';
        break;
    case "3":
        $sql_filter = 'SELECT p.nombre, p.ap_pat, p.ap_mat, p.calle_num, p.colonia, p.afiliacion, p.ciudad, p.estado, p.seccion, p.demarcacion, p.lat, p.lng FROM persona p INNER JOIN municipio m ON p.ciudad = m.municipio WHERE p.ciudad = m.municipio AND m.id_municipio =  "'.$usr_mun.'" ';
        break;
    case "5":
        $sql_filter = 'SELECT p.nombre, p.ap_pat, p.ap_mat, p.calle_num, p.colonia, p.afiliacion, p.ciudad, p.estado, p.seccion, p.demarcacion, p.lat, p.lng FROM persona p INNER JOIN municipio m ON p.ciudad = m.municipio WHERE p.ciudad = m.municipio AND m.id_municipio = "'.$usr_mun.'" AND p.demarcacion = "'.$usr_dem.'" ';
        break;
    case "6":
        $sql_filter = 'SELECT p.nombre, p.ap_pat, p.ap_mat, p.calle_num, p.colonia, p.afiliacion, p.ciudad, p.estado, p.seccion, p.demarcacion, p.lat, p.lng FROM persona p INNER JOIN municipio m ON p.ciudad = m.municipio WHERE p.ciudad = m.municipio AND m.id_municipio = "'.$usr_mun.'" AND p.demarcacion = "'.$usr_dem.'" AND zona ="'.$usr_zona.'" ';
        break;
    case "4":
        $sql_filter = 'SELECT p.nombre, p.ap_pat, p.ap_mat, p.calle_num, p.colonia, p.afiliacion, p.ciudad, p.estado, p.seccion, p.demarcacion, p.lat, p.lng FROM persona p INNER JOIN municipio m ON p.ciudad = m.municipio WHERE p.ciudad = m.municipio AND m.id_municipio =  "'.$usr_mun.'" AND p.seccion IN ('.$incoming_user_dems.') ';
        break;
    case "7":
    case "8":
        $sql_filter = 'SELECT p.nombre, p.ap_pat, p.ap_mat, p.calle_num, p.colonia, p.afiliacion, p.ciudad, p.estado, p.seccion, p.demarcacion, p.lat, p.lng FROM persona p WHERE p.id_usuario = "'.$usr_usuario.'" ';
        break;
}





//Tipo de registro
$registro = $incoming_reg == "0" ? ' AND (p.cve_elec = "0" OR p.cve_elec = "")'  : 'AND p.cve_elec != "0"';


//Afiliacion
$incoming_afi = isset($incoming_afi) ? $incoming_afi : "2";
switch($incoming_afi){
    case "2":
        $afiliacion = "";
        break;
    case "1":
        $afiliacion = 'AND afiliacion = "1"';
        break;
    case "0":
        $afiliacion = 'AND afiliacion = "0"';
        break;     
}

//Municipio
$incoming_mun = isset($incoming_mun) ? $incoming_mun : "0";
$municipio = $incoming_mun == 0 || $incoming_mun == "- Municipio -" ? "" : 'AND ciudad =  "'.$incoming_mun.'"';

//Colonia
$incoming_col = isset($incoming_col) ? $incoming_col = strtolower($incoming_col) : "0";
$colonia = $incoming_col == 0 || $incoming_col == "- colonia -" ? "" : 'AND LOWER(colonia) LIKE "%'.$incoming_col.'%"';

//Demarcación
$incoming_dem = isset($incoming_dem) ? $incoming_dem : "0";
$demarc = $incoming_dem == 0 || $incoming_dem == "" ? "" : 'AND demarcacion = "'.$incoming_dem.'"';

//Zona
$incoming_zona = isset($incoming_zona) ? $incoming_zona : "0";
$zona = $incoming_zona == 0 || $incoming_zona == "" ? "" : 'AND zona = "'.$incoming_zona.'"';

//Seccion
$incoming_sec = isset($incoming_sec) ? $incoming_sec : "0";
$seccion = $incoming_sec == 0 || $incoming_sec == "" ? "" : 'AND seccion LIKE "%'.$incoming_sec.'%"';

if(!$con){
    $respuesta = ['error' => true];
}
else{
    //$sql = $sql_filter.$municipio.' '.$afiliacion.' '.$colonia.' '.$seccion.';';
    $sql = $sql_filter.$municipio.' '.$afiliacion.' '.$colonia.' '.$seccion.' '.$registro.' '.$demarc.' '.$zona;
    //echo $sql;
    $statement = $con->prepare($sql);
    $statement->execute();
    $personas = $statement->fetchAll();
    $respuesta = [];

    //Agregar datos a mostrar en la ventana de informaci車n de cada marcador
    foreach($personas as $persona){
        $domicilio = $persona['calle_num'].', '.$persona['colonia'].', '.$persona['ciudad'].', '.$persona['estado'];

        $nombre_completo = $persona['nombre'].' '.$persona['ap_pat'].' '.$persona['ap_mat'];

        $afiliado = $persona['afiliacion'] == "1" ? '<p class="infom_si">Afiliado</p>' : '<p class="infom_no">No afiliado</p>';
        /*if($result->num_rows > 0){

        while($row = $result->fetch_assoc()){ */
        $info = [
            0 => '<div class="info_content"><h3>'.$nombre_completo.'</h3><p>'.$domicilio.'</p>'.$afiliado.'</div>'
        ];

        array_push($respuesta, $info);     
    }

}

echo json_encode($respuesta);

?>