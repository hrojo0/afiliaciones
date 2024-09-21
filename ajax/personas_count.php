<?php
$session_lifetime = 3600 * 24 * 4; // 4 days
session_set_cookie_params ($session_lifetime);
session_start();
require '../conexion_db.php';
require '../functions.php';
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

$incoming_reg = $_POST['registro'];
//$incoming_af = $_POST['afiliacion'];
$incoming_edad = strval($_POST["edad"]);
$incoming_mun = $_POST["municipio"];
$incoming_dem = $_POST['demarc'];
$incoming_zona = $_POST['zona'];
$incoming_sec = $_POST["seccion"];
$incoming_col = $_POST["colonia"];


//echo $incoming_reg.' '.$incoming_af.' '.$incoming_edad.' '.$incoming_mun.' '.$incoming_dem.' '.$incoming_sec.' '.$incoming_col.'---------------- ';

$registro = $demarcacion = $edad = $sexo = $afiliacion = $municipio = $zona = $colonia = $seccion = $nombre = $group = "";
$contador = 1;

$respuesta = [];

require "../includes/switch-sql.php";

//Definición de parametros de la consulta SQL
if($incoming_reg == "1"){
    $registro = ' AND p.cve_elec != "0"';
} else {
    $registro = ' AND p.cve_elec = "0"';
}


//Edad
$incoming_edad = isset($incoming_edad) ? $incoming_edad : "0";
switch($incoming_edad){
    case "0":
        $edad = "";
        $group = $group;
        break;
    case "1":
        $edad = 'AND ((DATEDIFF(CAST(UTC_DATE() AS DATE),p.f_nac) / 365.25) < 18)';
        $group = $group.', edad';
        break;
    case "18":
        $edad = 'AND ((DATEDIFF(CAST(UTC_DATE() AS DATE),p.f_nac) / 365.25) > 18 AND (DATEDIFF(CAST(UTC_DATE() AS DATE),p.f_nac) / 365.25) < 25)';
        $group = $group.', edad';
        break; 
    case "25":
        $edad = 'AND ((DATEDIFF(CAST(UTC_DATE() AS DATE),p.f_nac) / 365.25) > 25 AND (DATEDIFF(CAST(UTC_DATE() AS DATE),p.f_nac) / 365.25) < 35)';
        $group = $group.', edad';
        break; 
    case "35":
        $edad = 'AND ((DATEDIFF(CAST(UTC_DATE() AS DATE),p.f_nac) / 365.25) > 35 AND (DATEDIFF(CAST(UTC_DATE() AS DATE),p.f_nac) / 365.25) < 45)';
        $group = $group.', edad';
        break; 
    case "45":
        $edad = 'AND ((DATEDIFF(CAST(UTC_DATE() AS DATE),p.f_nac) / 365.25) > 45 AND (DATEDIFF(CAST(UTC_DATE() AS DATE),p.f_nac) / 365.25) < 55)';
        $group = $group.', edad';
        break; 
    case "55":
        $edad = 'AND ((DATEDIFF(CAST(UTC_DATE() AS DATE),p.f_nac) / 365.25) > 55 AND (DATEDIFF(CAST(UTC_DATE() AS DATE),p.f_nac) / 365.25) < 65)';
        $group = $group.', edad';
        break; 
    case "65":
        $edad = 'AND ((DATEDIFF(CAST(UTC_DATE() AS DATE),p.f_nac) / 365.25) > 65)';
        $group = $group.', edad';
        break; 
}
//Sexo
$incoming_sexo = isset($incoming_sexo) ? $incoming_sexo : "0";
switch($incoming_sexo){
    case "0":
        $sexo = "";
        $group = $group;
        break;
    case "1":
        $sexo = 'AND p.sexo = "M"';
        $group = $group.', sexo';
        break;
    case "2":
        $sexo = 'AND p.sexo = "F"';
        $group = $group.', sexo';
        break;     
}
//Afiliacion
/*$incoming_afiliacion = isset($incoming_afiliacion) ? $incoming_afiliacion : "0";
switch($incoming_afiliacion){
    case "0":
        $afiliacion = "";
        $group = $group;
        break;
    case "1":
        $afiliacion = 'AND afiliacion = "1"';
        $group = $group.', afiliacion';
        break;
    case "2":
        $afiliacion = 'AND afiliacion = "0"';
        $group = $group.', afiliacion';
        break;     
}*/
//Municipio
$incoming_mun = isset($incoming_mun) ? $incoming_mun : "0";
$municipio = $incoming_mun == 0 || $incoming_mun == "- Municipio -" ? "" : 'AND ciudad = "'.$incoming_mun.'"';
$group = $incoming_mun == 0 || $incoming_mun == "- Municipio -" ? $group : $group.', ciudad';


//Demarcación
$incoming_dem = isset($incoming_dem) ? $incoming_dem : "0";
$demarcacion = $incoming_dem == 0 || $incoming_dem == "- Demarcación -" ? "" : 'AND p.demarcacion = "'.$incoming_dem.'"';
$group = $incoming_dem == 0 || $incoming_dem == "- Demarcación -" ? $group : $group.', demarcacion';

//Zona
$incoming_zona = isset($incoming_zona) ? $incoming_zona : "0";
$zona = $incoming_zona == 0 || $incoming_zona == "- Zona -" || empty($incoming_zona) ? "" : 'AND p.zona = "'.$incoming_zona.'"';
$group = $incoming_zona == 0 || $incoming_zona == "- Zona -" ? $group : $group.', zona';

//Seccion
$incoming_sec = isset($incoming_sec) ? $incoming_sec : "0";
$seccion = $incoming_sec == 0 || $incoming_sec == "- Sección -" ? "" : 'AND p.seccion = "'.$incoming_sec.'"';
$group = $incoming_sec == 0 || $incoming_sec == "- Sección -" ? $group : $group.', seccion';


//Colonia
$incoming_col = isset($incoming_col) ? $incoming_col = strtolower($incoming_col) : "0";
$colonia = $incoming_col == 0 || $incoming_col == "- colonia -" ? "" : 'AND LOWER(p.colonia) LIKE "%'.$incoming_col.'%"';


//Seccion
/*$incoming_sec = isset($incoming_sec) ? $incoming_sec : "0";
$seccion = $incoming_sec == 0 || $incoming_sec == "" ? "" : 'AND p.seccion LIKE "%'.$incoming_sec.'%"';
$group = $incoming_sec == 0 || $incoming_sec == "" ? $group : $group.', p.seccion';*/
//sql para obtener datos, edad la calcula con deciles ej 31.895

/*
SQL MUESTRA PARA OBTENER CIFRAS DE DATOS. Ej. obtiene contador de personas acorde a edad, sexo y ciudad; obtener el promedio de edad con ciclo for se multiplica la columna edad * cantidad y se almacena en una variable para despues dividir entre el total de count que se obtiene con el ciclo for
|
SELECT *, COUNT(*) AS cantidad FROM (SELECT DATEDIFF(CAST(UTC_DATE() AS DATE),f_nac) / 365.25 AS edad, sexo, celular, telefono, afiliacion, colonia, cp, seccion, ciudad, estado FROM persona WHERE 1 AND ((DATEDIFF(CAST(UTC_DATE() AS DATE),f_nac) / 365.25) < 18) AND sexo = "M" AND LOWER(ciudad) LIKE "%Tepic%") AS subconsulta GROUP BY telefono, edad, sexo, ciudad;
*/

//MODIFICAR SQL PARA QUE SEA DINAMICO

$sql = 'SELECT *, COUNT(*) AS cantidad, SUM(edad) AS total_edad, SUM(CASE WHEN sexo = "M" THEN 1 ELSE 0 END) AS total_hombres, SUM(CASE WHEN sexo = "F" THEN 1 ELSE 0 END) AS total_mujeres, SUM(CASE WHEN whatsapp != 0 THEN 1 ELSE 0 END) AS total_con_telefono FROM (SELECT DATEDIFF(CAST(UTC_DATE() AS DATE), p.f_nac) / 365.25 AS edad, p.sexo, p.demarcacion, p.zona, p.whatsapp, p.afiliacion, p.colonia, p.cp, p.seccion, p.ciudad, p.estado FROM persona p '.$extra_sql.' '.$registro.' '.$edad.' '.$sexo.' '.$municipio.' '.$demarcacion.' '.$zona.' '.$colonia.' '.$seccion.') AS subconsulta GROUP BY afiliacion, sexo'.$group.';';


$statement = $con->prepare($sql);
$statement->execute();
$personas = $statement->fetchAll();

foreach($personas as $persona){    
    $temp = [
        'edad' => $persona['edad'],
        'sexo' => $persona['sexo'],
        'afiliacion' => $persona['afiliacion'],
        'colonia' => $persona['colonia'],
        'cp' => $persona['cp'],
        'seccion' => $persona['seccion'],
        'ciudad' => $persona['ciudad'],
        'estado' => $persona['estado'],
        'cantidad' => $persona['cantidad'],
        'total_edad' => $persona['total_edad'],
        'total_hombres' => $persona['total_hombres'],
        'total_mujeres' => $persona['total_mujeres'],
        'total_con_telefono' => $persona['total_con_telefono'],
    ];
    array_push($respuesta, $temp);
}

echo json_encode($respuesta);

?>