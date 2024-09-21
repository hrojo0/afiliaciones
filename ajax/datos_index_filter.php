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
$incoming_colonia = strval($_POST["colonia"]);

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
$incoming_colonia = isset($incoming_colonia) ? strtolower($incoming_colonia) : "0";
$colonia = $incoming_colonia == 0 || $incoming_colonia == "- colonia -" ? "" : 'AND LOWER(colonia) LIKE "%'.$incoming_colonia.'%"';

//sql para obtener datos dependiendo el filtro y nivel de usuario, edad la calcula con deciles ej 31.895
$condiciones = ' '.$edad.' '.$demarcacion.' '.$zona.' '.$seccion.' '.$colonia;

$sql_filter ='
SELECT
    #DATOS GENERALES
   (SELECT COUNT(*) as cantidad from persona p'.$extra_sql.' '.$condiciones.') AS total,
   (SELECT COUNT(*) as cantidad from persona p'.$extra_sql.' AND p.afiliacion = "1"'.$condiciones.') AS total_afiliados,
   (SELECT SUM(CASE WHEN p.sexo = "M" THEN 1 END) AS hombres FROM persona p '.$extra_sql.' AND p.afiliacion = 1 '.$condiciones.' GROUP BY  p.afiliacion ) AS hombres,
   (SELECT SUM(CASE WHEN p.sexo = "F" THEN 1 END) AS mujeres FROM persona p '.$extra_sql.' AND p.afiliacion = 1 '.$condiciones.' GROUP BY  p.afiliacion ) AS mujeres,
   (SELECT edad FROM (SELECT floor(DATEDIFF(CAST(UTC_DATE() AS DATE), p.f_nac) / 365.25) AS edad, COUNT(*) AS cantidad FROM persona p '.$extra_sql.' AND p.afiliacion = "1" '.$condiciones.' GROUP BY edad ORDER BY cantidad DESC LIMIT 1) AS temp_sql) AS edad_moda,
   (SELECT floor(AVG(floor(DATEDIFF(CAST(UTC_DATE() AS DATE), p.f_nac) / 365.25))) AS edad FROM persona p '.$extra_sql.' AND p.afiliacion = "1" AND p.sexo = "M"'.$condiciones.') AS edad_promedio_hombres,
   (SELECT floor(AVG(floor(DATEDIFF(CAST(UTC_DATE() AS DATE), p.f_nac) / 365.25))) AS edad FROM persona p '.$extra_sql.' AND p.afiliacion = "1" AND p.sexo = "F"'.$condiciones.') AS edad_promedio_mujeres,
   
   #RANGO DE EDAD
   (SELECT COUNT(*) FROM persona p '.$extra_sql.' AND (DATEDIFF(CAST(UTC_DATE() AS DATE), p.f_nac) / 365.25) < 18 AND p.afiliacion = "1" AND p.sexo = "M" '.$condiciones.') AS menores_hombres,
    (SELECT COUNT(*) FROM persona p '.$extra_sql.' AND (DATEDIFF(CAST(UTC_DATE() AS DATE), p.f_nac) / 365.25) < 18 AND p.afiliacion = "1" AND p.sexo = "F" '.$condiciones.') AS menores_mujeres,
    (SELECT COUNT(*) FROM persona p '.$extra_sql.' AND (DATEDIFF(CAST(UTC_DATE() AS DATE), p.f_nac) / 365.25) > 18 AND (DATEDIFF(CAST(UTC_DATE() AS DATE), p.f_nac) / 365.25) < 25 AND p.afiliacion = "1" AND p.sexo = "M" '.$condiciones.') AS 18a24_hombres,
    (SELECT COUNT(*) FROM persona p '.$extra_sql.' AND (DATEDIFF(CAST(UTC_DATE() AS DATE), p.f_nac) / 365.25) > 18 AND (DATEDIFF(CAST(UTC_DATE() AS DATE), p.f_nac) / 365.25) < 25 AND p.afiliacion = "1" AND p.sexo = "F" '.$condiciones.') AS 18a24_mujeres,
    (SELECT COUNT(*) FROM persona p '.$extra_sql.' AND (DATEDIFF(CAST(UTC_DATE() AS DATE), p.f_nac) / 365.25) >= 25 AND (DATEDIFF(CAST(UTC_DATE() AS DATE), p.f_nac) / 365.25) < 35 AND p.afiliacion = "1" AND p.sexo = "M" '.$condiciones.') AS 25a34_hombres,
    (SELECT COUNT(*) FROM persona p '.$extra_sql.' AND (DATEDIFF(CAST(UTC_DATE() AS DATE), p.f_nac) / 365.25) >= 25 AND (DATEDIFF(CAST(UTC_DATE() AS DATE), p.f_nac) / 365.25) < 35 AND p.afiliacion = "1" AND p.sexo = "F" '.$condiciones.') AS 25a34_mujeres,
    (SELECT COUNT(*) FROM persona p '.$extra_sql.' AND (DATEDIFF(CAST(UTC_DATE() AS DATE), p.f_nac) / 365.25) >= 35 AND (DATEDIFF(CAST(UTC_DATE() AS DATE), p.f_nac) / 365.25) < 45 AND p.afiliacion = "1" AND p.sexo = "M" '.$condiciones.') AS 35a44_hombres,
    (SELECT COUNT(*) FROM persona p '.$extra_sql.' AND (DATEDIFF(CAST(UTC_DATE() AS DATE), p.f_nac) / 365.25) >= 35 AND (DATEDIFF(CAST(UTC_DATE() AS DATE), p.f_nac) / 365.25) < 45 AND p.afiliacion = "1" AND p.sexo = "F" '.$condiciones.') AS 35a44_mujeres,
    (SELECT COUNT(*) FROM persona p '.$extra_sql.' AND (DATEDIFF(CAST(UTC_DATE() AS DATE), p.f_nac) / 365.25) >= 45 AND (DATEDIFF(CAST(UTC_DATE() AS DATE), p.f_nac) / 365.25) < 55 AND p.afiliacion = "1" AND p.sexo = "M" '.$condiciones.') AS 45a54_hombres,
    (SELECT COUNT(*) FROM persona p '.$extra_sql.' AND (DATEDIFF(CAST(UTC_DATE() AS DATE), p.f_nac) / 365.25) >= 45 AND (DATEDIFF(CAST(UTC_DATE() AS DATE), p.f_nac) / 365.25) < 55 AND p.afiliacion = "1" AND p.sexo = "F" '.$condiciones.') AS 45a54_mujeres,
    (SELECT COUNT(*) FROM persona p '.$extra_sql.' AND (DATEDIFF(CAST(UTC_DATE() AS DATE), p.f_nac) / 365.25) >= 55 AND (DATEDIFF(CAST(UTC_DATE() AS DATE), p.f_nac) / 365.25) < 65 AND p.afiliacion = "1" AND p.sexo = "M" '.$condiciones.') AS 55a64_hombres,
    (SELECT COUNT(*) FROM persona p '.$extra_sql.' AND (DATEDIFF(CAST(UTC_DATE() AS DATE), p.f_nac) / 365.25) >= 55 AND (DATEDIFF(CAST(UTC_DATE() AS DATE), p.f_nac) / 365.25) < 65 AND p.afiliacion = "1" AND p.sexo = "F" '.$condiciones.') AS 55a64_mujeres,
    (SELECT COUNT(*) FROM persona p '.$extra_sql.' AND (DATEDIFF(CAST(UTC_DATE() AS DATE), p.f_nac) / 365.25) >= 65 AND p.afiliacion = "1" AND p.sexo = "M" '.$condiciones.') AS 65mas_hombres,
    (SELECT COUNT(*) FROM persona p '.$extra_sql.' AND (DATEDIFF(CAST(UTC_DATE() AS DATE), p.f_nac) / 365.25) >= 65 AND p.afiliacion = "1" AND p.sexo = "F" '.$condiciones.') AS 65mas_mujeres,
    
    #SECCION
    (SELECT seccion FROM (SELECT COUNT(p.seccion) AS seccion_h, p.seccion FROM persona p '.$extra_sql.' AND p.afiliacion = "1" AND p.sexo = "M" '.$condiciones.' GROUP BY p.seccion ORDER BY COUNT(p.seccion) DESC LIMIT 1) AS sh2) AS seccion_mas_hombres,
    (SELECT seccion_h FROM (SELECT COUNT(p.seccion) AS seccion_h, p.seccion FROM persona p '.$extra_sql.' AND p.afiliacion = "1" AND p.sexo = "M" '.$condiciones.' GROUP BY p.seccion ORDER BY COUNT(p.seccion) DESC LIMIT 1) AS sh1) AS seccion_mas_hombres_cantidad,
    (SELECT seccion FROM (SELECT COUNT(p.seccion) AS seccion_m, p.seccion FROM persona p '.$extra_sql.' AND p.afiliacion = "1" AND p.sexo = "F" '.$condiciones.' GROUP BY p.seccion ORDER BY COUNT(p.seccion) DESC LIMIT 1) AS sm2) AS seccion_mas_mujeres,
    (SELECT seccion_m FROM (SELECT COUNT(p.seccion) AS seccion_m, p.seccion FROM persona p '.$extra_sql.' AND p.afiliacion = "1" AND p.sexo = "F" '.$condiciones.' GROUP BY p.seccion ORDER BY COUNT(p.seccion) DESC LIMIT 1) AS sm1) AS seccion_mas_mujeres_cantidad
';

/*********************************************************/
//CHECAR SQL PARA EL FILTRO!!!!!!
//echo $sql_filter;
/********************************************************/

//$statement = $con->prepare('SELECT * FROM ('.$sql_final.') AS subquery GROUP BY cve_elec');
$statement = $con->prepare($sql_filter);
$statement->execute();
$data = $statement->fetch();

echo json_encode($data);;



?>