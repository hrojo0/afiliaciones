<?php
$session_lifetime = 3600 * 24 * 4; // 4 days
session_set_cookie_params ($session_lifetime);
session_start();
require '../conexion_db.php';
require '../functions.php';
comprobar_login_ajax();
require "../includes/session.php";
require "../includes/switch-sql.php";


//DECLARACIÓN DE VARIABLES Y CASTEO DE POST
$incoming_edad = strval($_POST["edad"]);
$incoming_demarcacion = strval($_POST["demarc"]);
$incoming_zona = strval($_POST["zona"]);
$incoming_seccion = strval($_POST["seccion"]);
$incoming_col = strval($_POST["colonia"]);

$edad = $municipio = $demarcacion = $zona = $seccion = $colonia = "";
//echo $incoming_edad.' | '.$incoming_demarc.' | '.$incoming_zona.' | '.$incoming_seccion.' | '.$incoming_colonia;

//Definición de parametros de la consulta SQL
//Edad
$incoming_edad = isset($incoming_edad) ? $incoming_edad : "0";
switch($incoming_edad){
    case "0":
        $edad = "";
        break;
    case "1":
        $edad = 'AND ((DATEDIFF(CAST(UTC_DATE() AS DATE),f_nac) / 365.25) < 18)';
        break;
    case "18":
        $edad = 'AND ((DATEDIFF(CAST(UTC_DATE() AS DATE),f_nac) / 365.25) > 18 AND (DATEDIFF(CAST(UTC_DATE() AS DATE),f_nac) / 365.25) < 25)';
        break; 
    case "25":
        $edad = 'AND ((DATEDIFF(CAST(UTC_DATE() AS DATE),f_nac) / 365.25) > 25 AND (DATEDIFF(CAST(UTC_DATE() AS DATE),f_nac) / 365.25) < 35)';
        break; 
    case "35":
        $edad = 'AND ((DATEDIFF(CAST(UTC_DATE() AS DATE),f_nac) / 365.25) > 35 AND (DATEDIFF(CAST(UTC_DATE() AS DATE),f_nac) / 365.25) < 45)';
        break; 
    case "45":
        $edad = 'AND ((DATEDIFF(CAST(UTC_DATE() AS DATE),f_nac) / 365.25) > 45 AND (DATEDIFF(CAST(UTC_DATE() AS DATE),f_nac) / 365.25) < 55)';
        break; 
    case "55":
        $edad = 'AND ((DATEDIFF(CAST(UTC_DATE() AS DATE),f_nac) / 365.25) > 55 AND (DATEDIFF(CAST(UTC_DATE() AS DATE),f_nac) / 365.25) < 65)';
        break; 
    case "65":
        $edad = 'AND ((DATEDIFF(CAST(UTC_DATE() AS DATE),f_nac) / 365.25) > 65)';
        break; 
}

//Demarcación
$incoming_demarcacion = isset($incoming_demarcacion) ? $incoming_demarcacion : "0";
$demarcacion = $incoming_demarcacion == 0 || $incoming_demarcacion == "- Demarcación -" ? "" : 'AND demarcacion = "'.$incoming_demarcacion.'"';

//Zona
$incoming_zona = isset($incoming_zona) ? $incoming_zona : "0";
$zona = $incoming_zona == 0 || $incoming_zona == "- Zona -" ? "" : 'AND zona = "'.$incoming_zona.'"';

//Seccion
$incoming_seccion = isset($incoming_seccion) ? $incoming_seccion : "0";
$seccion = $incoming_seccion == 0 || $incoming_seccion == "" ? "" : 'AND seccion = "'.$incoming_seccion.'"';

//Colonia
$incoming_col = isset($incoming_col) ? $incoming_col = strtolower($incoming_col) : "0";
$colonia = $incoming_col == 0 || $incoming_col == "- colonia -" ? "" : 'AND LOWER(colonia) LIKE "%'.$incoming_col.'%"';



//sql para obtener datos dependiendo el filtro y nivel de usuario, edad la calcula con deciles ej 31.895
$condiciones = ' '.$edad.' '.$demarcacion.' '.$zona.' '.$seccion.' '.$colonia;

$sql = '
SELECT
    (SELECT COUNT(*) FROM persona p '.$extra_sql.' AND (DATEDIFF(CAST(UTC_DATE() AS DATE), p.f_nac) / 365.25) < 18 AND p.afiliacion = "1" '.$condiciones.') AS menores,
    (SELECT COUNT(*) FROM persona p '.$extra_sql.' AND (DATEDIFF(CAST(UTC_DATE() AS DATE), p.f_nac) / 365.25) > 18 AND (DATEDIFF(CAST(UTC_DATE() AS DATE), p.f_nac) / 365.25) < 25 AND p.afiliacion = "1" '.$condiciones.') AS 18a24,
    (SELECT COUNT(*) FROM persona p '.$extra_sql.' AND (DATEDIFF(CAST(UTC_DATE() AS DATE), p.f_nac) / 365.25) >= 25 AND (DATEDIFF(CAST(UTC_DATE() AS DATE), p.f_nac) / 365.25) < 35 AND p.afiliacion = "1" '.$condiciones.') AS 25a34,
    (SELECT COUNT(*) FROM persona p '.$extra_sql.' AND (DATEDIFF(CAST(UTC_DATE() AS DATE), p.f_nac) / 365.25) >= 35 AND (DATEDIFF(CAST(UTC_DATE() AS DATE), p.f_nac) / 365.25) < 45 AND p.afiliacion = "1" '.$condiciones.') AS 35a44,
    (SELECT COUNT(*) FROM persona p '.$extra_sql.' AND (DATEDIFF(CAST(UTC_DATE() AS DATE), p.f_nac) / 365.25) >= 45 AND (DATEDIFF(CAST(UTC_DATE() AS DATE), p.f_nac) / 365.25) < 55 AND p.afiliacion = "1" '.$condiciones.') AS 45a54,
    (SELECT COUNT(*) FROM persona p '.$extra_sql.' AND (DATEDIFF(CAST(UTC_DATE() AS DATE), p.f_nac) / 365.25) >= 55 AND (DATEDIFF(CAST(UTC_DATE() AS DATE), p.f_nac) / 365.25) < 65 AND p.afiliacion = "1" '.$condiciones.') AS 55a64,
    (SELECT COUNT(*) FROM persona p '.$extra_sql.' AND (DATEDIFF(CAST(UTC_DATE() AS DATE), p.f_nac) / 365.25) >= 65 AND p.afiliacion = "1" '.$condiciones.') AS 65mas;
';
$statement = $con->prepare($sql);
$statement->execute(); 
$afiliados_rango_edad = $statement->fetch(); 

//array con datos de edades
$total_edades = [
    ['rango' => 'Menor de 18', 'cantidad' => $afiliados_rango_edad['menores']],
    ['rango' => 'De 18 a 24', 'cantidad' => $afiliados_rango_edad['18a24']],
    ['rango' => 'De 25 a 34', 'cantidad' => $afiliados_rango_edad['25a34']],
    ['rango' => 'De 34 a 44', 'cantidad' => $afiliados_rango_edad['35a44']],
    ['rango' => 'De 45 a 54', 'cantidad' => $afiliados_rango_edad['45a54']],
    ['rango' => 'De 55 a 64', 'cantidad' => $afiliados_rango_edad['55a64']],
    ['rango' => '65 o más', 'cantidad' => $afiliados_rango_edad['65mas']]
];

//COLONIAS TOP
$statement = $con->prepare('SELECT p.ciudad, p.estado, p.colonia, COUNT(*) as cantidad from persona p'.$extra_sql.' AND p.afiliacion = "1" '.$condiciones.' GROUP BY p.colonia ORDER BY cantidad DESC LIMIT 10;');
$statement->execute();
$total_cols_top = $statement->fetchAll(); 

//COLONIAS LOWER
$statement = $con->prepare('SELECT p.ciudad, p.estado, p.colonia, COUNT(*) as cantidad from persona p'.$extra_sql.' AND p.afiliacion = "1" '.$condiciones.' GROUP BY p.colonia ORDER BY cantidad ASC LIMIT 10;');
$statement->execute();
$total_cols_lower = $statement->fetchAll(); 

//SECCIONES TOP
$statement = $con->prepare('SELECT p.seccion, COUNT(*) as cantidad from persona p'.$extra_sql.' AND p.afiliacion = "1" '.$condiciones.' GROUP BY p.seccion ORDER BY cantidad DESC LIMIT 10;');
$statement->execute();
$total_seccs_top = $statement->fetchAll(); 

//SECCIONES LOWER
$statement = $con->prepare('SELECT p.seccion, COUNT(*) as cantidad from persona p'.$extra_sql.' AND p.afiliacion = "1" '.$condiciones.' GROUP BY p.seccion ORDER BY cantidad ASC LIMIT 10;');
$statement->execute();
$total_seccs_lower = $statement->fetchAll(); 

$data = [
    'total edades' => $total_edades,
    'total colonias top' => $total_cols_top,
    'total colonias lower' => $total_cols_lower,
    'total secciones top' => $total_seccs_top,
    'total secciones lower' => $total_seccs_lower,
];
/*********************************************************/
//CHECAR SQL PARA EL FILTRO!!!!!!
//echo $sql_filter;
/********************************************************/


echo json_encode($data);;




?>