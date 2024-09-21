<?php
$session_lifetime = 3600 * 24 * 4; // 4 days
session_set_cookie_params ($session_lifetime);
session_start();
require '../conexion_db.php';
require '../functions.php';
comprobar_login_ajax();
require "../includes/session.php";

$filtro = $_POST['filtro'];

//VARIABLES PARA PAGINADO
$flag = $_POST['flag'];

$cont = 0;

$flag_cve = $flag == 0 ? ' AND (p.cve_elec = "0" OR p.cve_elec = "" OR u.id_nivel_usuario IS NULL)' : ' AND (p.cve_elec != "0" OR u.id_nivel_usuario IS NULL)';


//DECLARACIÓN DE VARIABLES Y CASTEO DE POST
$row_persona = "";
$incoming_edad = strval($_POST["edad"]);
$incoming_sexo = strval($_POST["sexo"]);
$incoming_demarcacion = strval($_POST["demarcacion"]);
//$incoming_zona = strval($_POST["zona"]);

$incoming_zona = isset($_POST["zona"]) ? strval($_POST["zona"]) : '0';

$incoming_afiliacion = isset($_POST["afiliacion"]) ? strval($_POST["afiliacion"]) : "0";
$incoming_mun = $_POST["municipio"];
$incoming_col = $_POST["colonia"];
$incoming_sec = $_POST["seccion"];
$incoming_nom = $_POST["nombre"];
$edad = $sexo = $afiliacion = $municipio = $colonia = $seccion = $nombre = "";
$contador = 1;
$nombre_array = [];
//Definición de parametros de la consulta SQL
//Nombre
$nombre_compuesto = explode(" ", $incoming_nom);
$incoming_nom = isset($incoming_nom) ? $incoming_nom : "0";
$nombre_compuesto = $incoming_nom == "0" || $incoming_nom == "" ? 0 : explode(" ", $incoming_nom);
if($nombre_compuesto != "0"){
    foreach($nombre_compuesto as $parte_nombre){
        $nombre = $nombre.'AND (nombre LIKE "%'.$parte_nombre.'%" OR ap_pat LIKE "%'.$parte_nombre.'%" OR ap_mat LIKE "%'.$parte_nombre.'%") ';
    }
} else {
    $nombre = "";
}

$nombre_compuesto = $incoming_nom == 0 || $incoming_nom == "" ? "" : 'AND nombre LIKE "%'.$incoming_nom.'%"';

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
//Sexo
$incoming_sexo = isset($incoming_sexo) ? $incoming_sexo : "0";
switch($incoming_sexo){
    case "0":
        $sexo = "";
        break;
    case "1":
        $sexo = 'AND sexo = "M"';
        break;
    case "2":
        $sexo = 'AND sexo = "F"';
        break;     
}
//Afiliacion
switch($incoming_afiliacion){
    case "0":
        $afiliacion = "";
        break;
    case "1":
        $afiliacion = 'AND 	afiliacion = "1"';
        break;
    case "2":
        $afiliacion = 'AND afiliacion = "0"';
        break;     
}
//Municipio
$incoming_mun = isset($incoming_mun) ? $incoming_mun : "0";
$municipio = $incoming_mun == 0 || $incoming_mun == "- Municipio -" ? "" : 'AND ciudad = "'.$incoming_mun.'"';

//Demarcación
$incoming_demarcacion = isset($incoming_demarcacion) ? $incoming_demarcacion : "0";
$demarcacion = $incoming_demarcacion == 0 || $incoming_demarcacion == "- Demarcación -" ? "" : 'AND demarcacion = "'.$incoming_demarcacion.'"';

//Zona
$incoming_zona = isset($incoming_zona) ? $incoming_zona : "0";
$zona = $incoming_zona == 0 || $incoming_zona == "- Zona -" ? "" : 'AND zona = "'.$incoming_zona.'"';

//Colonia
$incoming_col = isset($incoming_col) ? $incoming_col = strtolower($incoming_col) : "0";
$colonia = $incoming_col == 0 || $incoming_col == "- colonia -" ? "" : 'AND LOWER(colonia) LIKE "%'.$incoming_col.'%"';

//Seccion
$incoming_sec = isset($incoming_sec) ? $incoming_sec : "0";
$seccion = /*$incoming_sec == 0 ||*/ $incoming_sec == "" ? "" : 'AND seccion LIKE "%'.$incoming_sec.'%"';

//sql para obtener datos dependiendo el filtro y nivel de usuario, edad la calcula con deciles ej 31.895
switch($usr_nivel){
    case "1":
    case "2":
        /*$sql_personas_filter = 'SELECT p.id_persona,  p.cve_elec, p.nombre, p.ap_pat, p.ap_mat, DATEDIFF(CAST(UTC_DATE() AS DATE), p.f_nac) / 365.25 AS edad, p.sexo, p.celular, p.whatsapp, p.afiliacion, p.calle_num, p.colonia, p.cp, p.seccion, p.ciudad, p.estado, p.lat, p.lng, p.demarcacion FROM persona  p WHERE 1 '.$flag_cve;*/
        
        $sql_personas_filter =
            'SELECT m.id_municipio, p.id_persona, p.cve_elec, p.nombre, p.ap_pat, p.ap_mat, DATEDIFF(CAST(UTC_DATE() AS DATE),p.f_nac) / 365.25 AS edad, p.sexo, p.celular, p.whatsapp, p.afiliacion, p.calle_num, p.colonia, p.cp, p.ciudad, p.estado, p.demarcacion, p.seccion, p.lat, p.lng, p.id_usuario, u.id_nivel_usuario 
            FROM persona p 
            INNER JOIN municipio m ON p.ciudad = m.municipio 
            LEFT JOIN usuario u ON p.id_usuario = u.id_usuario 
            WHERE p.ciudad = m.municipio '.$flag_cve;
        break;
    case "3":
        /*$sql_personas_filter = 'SELECT p.id_persona, p.cve_elec, p.nombre, p.ap_pat, p.ap_mat, DATEDIFF(CAST(UTC_DATE() AS DATE),p.f_nac) / 365.25 AS edad, p.sexo, p.celular, p.whatsapp, p.afiliacion, p.calle_num, p.colonia, p.cp, p.seccion, p.ciudad, p.estado, p.lat, p.lng, p.demarcacion FROM persona p INNER JOIN municipio m WHERE p.ciudad = m.municipio AND m.id_municipio = "'.$usr_mun.'" '.$flag_cve;*/
        
        $sql_personas_filter = 
            'SELECT m.id_municipio, p.id_persona, p.cve_elec, p.nombre, p.ap_pat, p.ap_mat, DATEDIFF(CAST(UTC_DATE() AS DATE),p.f_nac) / 365.25 AS edad, p.sexo, p.celular, p.whatsapp, p.afiliacion, p.calle_num, p.colonia, p.cp, p.ciudad, p.estado, p.demarcacion, p.seccion, p.lat, p.lng, p.id_usuario, u.id_nivel_usuario 
            FROM persona p 
            INNER JOIN municipio m ON p.ciudad = m.municipio 
            LEFT JOIN usuario u ON p.id_usuario = u.id_usuario 
            WHERE p.ciudad = m.municipio AND m.id_municipio = "'.$usr_mun.'" '.$flag_cve;
        break;
    case "5":        
        /*$sql_personas_filter = 'SELECT p.id_persona,  p.cve_elec, p.nombre, p.ap_pat, p.ap_mat, DATEDIFF(CAST(UTC_DATE() AS DATE),p.f_nac) / 365.25 AS edad, p.sexo, p.celular, p.whatsapp, p.afiliacion, p.calle_num, p.colonia, p.cp, p.seccion, p.ciudad, p.estado, p.lat, p.lng, p.demarcacion FROM persona p INNER JOIN municipio m WHERE p.ciudad = m.municipio AND m.id_municipio = "'.$usr_mun.'" AND p.demarcacion = "'.$usr_dem.'" '.$flag_cve;*/
        
        $sql_personas_filter = 
            'SELECT m.id_municipio, p.id_persona, p.cve_elec, p.nombre, p.ap_pat, p.ap_mat, DATEDIFF(CAST(UTC_DATE() AS DATE),p.f_nac) / 365.25 AS edad, p.sexo, p.celular, p.whatsapp, p.afiliacion, p.calle_num, p.colonia, p.cp, p.ciudad, p.estado, p.demarcacion, p.seccion, p.lat, p.lng, p.id_usuario, u.id_nivel_usuario
            FROM persona p 
            INNER JOIN municipio m ON p.ciudad = m.municipio 
            LEFT JOIN usuario u ON p.id_usuario = u.id_usuario 
            WHERE p.ciudad = m.municipio AND m.id_municipio = "'.$usr_mun.'" AND p.demarcacion = "'.$usr_dem.'" '.$flag_cve;
        break;
    case "6":
        /*$sql_personas_filter = 'SELECT p.id_persona,  p.cve_elec, p.nombre, p.ap_pat, p.ap_mat, DATEDIFF(CAST(UTC_DATE() AS DATE),p.f_nac) / 365.25 AS edad, p.sexo, p.celular, p.whatsapp, p.afiliacion, p.calle_num, p.colonia, p.cp, p.seccion, p.ciudad, p.estado, p.lat, p.lng, p.demarcacion FROM persona p INNER JOIN municipio m WHERE p.ciudad = m.municipio AND m.id_municipio = "'.$usr_mun.'" AND p.demarcacion = "'.$usr_dem.'" AND p.zona ="'.$usr_zona.'" '.$flag_cve;*/
        
        $sql_personas_filter = 
            'SELECT m.id_municipio, p.id_persona, p.cve_elec, p.nombre, p.ap_pat, p.ap_mat, DATEDIFF(CAST(UTC_DATE() AS DATE),p.f_nac) / 365.25 AS edad, p.sexo, p.celular, p.whatsapp, p.afiliacion, p.calle_num, p.colonia, p.cp, p.ciudad, p.estado, p.demarcacion, p.seccion, p.lat, p.lng, p.id_usuario, u.id_nivel_usuario
            FROM persona p 
            INNER JOIN municipio m ON p.ciudad = m.municipio
            INNER JOIN usuario u ON p.id_usuario = u.id_usuario 
            WHERE p.ciudad = m.municipio AND m.id_municipio = "'.$usr_mun.'" AND p.demarcacion = "'.$usr_dem.'" AND p.zona = "'.$usr_zona.'" '.$flag_cve;
        break;
    case "4":
        $statement = $con ->prepare('SELECT demarcaciones FROM coordinacion_dems WHERE id_usuario = "'.$usr_usuario.'"');
        $statement->execute();
        $user_dems = $statement->fetch();
        $user_dems = $user_dems[0];
        $user_dems = substr($user_dems, 0 , -1);
        /*$sql_personas_filter = 'SELECT p.id_persona,  p.cve_elec, p.nombre, p.ap_pat, p.ap_mat, DATEDIFF(CAST(UTC_DATE() AS DATE),p.f_nac) / 365.25 AS edad, p.sexo, p.celular, p.whatsapp, p.afiliacion, p.calle_num, p.colonia, p.cp, p.seccion, p.ciudad, p.estado, p.lat, p.lng, p.demarcacion FROM persona p INNER JOIN municipio m WHERE p.ciudad = m.municipio AND m.id_municipio = "'.$usr_mun.'" AND p.seccion IN ('.$user_dems.') '.$flag_cve;*/
        
        $sql_personas_filter = 
            'SELECT m.id_municipio, p.id_persona, p.cve_elec, p.nombre, p.ap_pat, p.ap_mat, DATEDIFF(CAST(UTC_DATE() AS DATE),p.f_nac) / 365.25 AS edad, p.sexo, p.celular, p.whatsapp, p.afiliacion, p.calle_num, p.colonia, p.cp, p.ciudad, p.estado, p.demarcacion, p.seccion, p.lat, p.lng, p.id_usuario, u.id_nivel_usuario 
                FROM persona p 
                INNER JOIN municipio m ON p.ciudad = m.municipio 
                INNER JOIN usuario u ON p.id_usuario = u.id_usuario 
                WHERE p.ciudad = m.municipio AND m.id_municipio = "'.$usr_mun.'" AND p.zona = "'.$usr_zona.'" AND p.seccion IN ('.$user_dems.') '.$flag_cve;
        

        
        break;
    case "7":
        $statement = $con -> prepare('SELECT m.municipio FROM municipio m WHERE m.id_municipio = '.$usr_mun);
        $statement -> execute();
        $temp_mun = $statement -> fetch();
        
        
       /* $sql_personas_filter = 'SELECT id_persona,  cve_elec, nombre, ap_pat, ap_mat, DATEDIFF(CAST(UTC_DATE() AS DATE),f_nac) / 365.25 AS edad, sexo, celular, whatsapp, afiliacion, calle_num, colonia, cp, seccion, ciudad, estado, lat, lng, demarcacion FROM persona p WHERE id_usuario = "'.$usr_usuario.'" '.$flag_cve;*/
        
        $sql_personas_filter = 
           'SELECT m.id_municipio, p.id_persona, p.cve_elec, p.nombre, p.ap_pat, p.ap_mat, DATEDIFF(CAST(UTC_DATE() AS DATE),f_nac) / 365.25 AS edad, p.sexo, p.celular, p.whatsapp, p.afiliacion, p.calle_num, p.colonia, p.cp, p.ciudad, p.estado, p.demarcacion, p.seccion, p.lat, p.lng, p.id_usuario, u.id_nivel_usuario 
           FROM persona p
           INNER JOIN municipio m ON p.ciudad = m.municipio
           INNER JOIN usuario u ON p.id_usuario = u.id_usuario 
           WHERE p.id_usuario = "'.$usr_usuario.'" AND p.ciudad = "'.$temp_mun['municipio'].'" AND p.demarcacion = "'.$usr_dem.'" AND p.zona = "'.$usr_zona.'" '.$flag_cve;
        
        
        
        break;
        
        case "8":
        $sql_personas_filter = 'SELECT m.id_municipio, p.id_persona, p.cve_elec, p.nombre, p.ap_pat, p.ap_mat, DATEDIFF(CAST(UTC_DATE() AS DATE),f_nac) / 365.25 AS edad, p.sexo, p.celular, p.whatsapp, p.afiliacion, p.calle_num, p.colonia, p.cp, p.ciudad, p.estado, p.demarcacion, p.seccion, p.lat, p.lng, p.id_usuario FROM persona p INNER JOIN municipio m ON p.ciudad = m.municipio WHERE p.ciudad = m.municipio AND p.id_usuario = "'.$usr_usuario.'"';
        
        break;
}
$sql_personas_filter_2 =
    'SELECT m.id_municipio, p.id_persona, p.cve_elec, p.nombre, p.ap_pat, p.ap_mat, DATEDIFF(CAST(UTC_DATE() AS DATE),f_nac) / 365.25 AS edad, p.sexo, p.celular, p.whatsapp, p.afiliacion, p.calle_num, p.colonia, p.cp, p.ciudad, p.estado, p.demarcacion, p.seccion, p.lat, p.lng, p.id_usuario, u.id_nivel_usuario
    FROM persona p
    INNER JOIN municipio m ON p.ciudad = m.municipio
    INNER JOIN usuario u ON p.id_usuario = u.id_usuario 
    WHERE p.ciudad = m.municipio AND p.demarcacion = "'.$usr_dem.'" AND p.zona = "'.$usr_zona.'" AND u.id_nivel_usuario = "8"';
$condiciones = $nombre.' '.$edad.' '.$sexo.' '.$afiliacion.' '.$municipio.' '.$demarcacion.' '.$zona.' '.$colonia.' '.$seccion;

if($usr_nivel == "8"){
    $sql_final = $sql_personas_filter.$condiciones;
} else {
    $sql_final = $sql_personas_filter.$condiciones.' UNION ALL '.$sql_personas_filter_2.$condiciones;
}

$statement = $con->prepare('SELECT * FROM ('.$sql_final.') AS subquery GROUP BY cve_elec');
$statement->execute();
$personas = $statement->fetchAll();

$datos = [];
if($flag == 1){
    $data = array();
    foreach ($personas as $persona) {
        $whatsapp = $persona['whatsapp'] == 0 || $persona['whatsapp'] == '' ? '<td class="t_sin_whatsapp">No tiene</td>' : '
        <td class="t_whatsapp"><a target="_blank" href="https://wa.me/52'.$persona['celular'].'"><i class="fa-brands fa-whatsapp" style="font-weight:900"></i></a></td>';
        
        $celular = $persona['celular'] == 0 || $persona['celular'] == '' ? '<td class="t_sin_telefono">No tiene</td>' : '
        <td class="t_telefono"><a href="tel:'.$persona['celular'].'">'.$persona['celular'].'</a></td>';

        $apellidos = $persona['ap_pat'].' '.$persona['ap_mat'];

        $sexo = $persona['sexo'] == 'M' ? 'Masculino' : 'Femenino';

        $edad = floor($persona['edad']);

        $afiliacion = $persona['afiliacion'] == 1 ? 'Sí' : 'No';

        $calle_num = '
        <a class="td_red" href = "https://maps.google.com/?q='.$persona['lat'].','.$persona['lng'].'" target="_blank">
            '.$persona['calle_num'].'
        </a>';
        if($usr_nivel == "8"){
    $tmp = [
            'id_persona' => $persona['id_persona'],
            'nombre' => $persona['nombre'], 
            'apellidos' => $apellidos, 
            'edad' => $edad, 
            'sexo' =>$sexo, 
            'celular' => $celular, 
            'whatsapp' => $whatsapp, 
            'afiliacion' => $afiliacion, 
            'calle_num' => $calle_num, 
            'colonia' => $persona['colonia'], 
            'demarcacion' => $persona['demarcacion'], 
            'seccion' => $persona['seccion'], 
            'ciudad' => $persona['ciudad'],
        ];
} else {
    $tmp = [
            'id_persona' => $persona['id_persona'],
            'nombre' => $persona['nombre'], 
            'apellidos' => $apellidos, 
            'edad' => $edad, 
            'sexo' =>$sexo, 
            'celular' => $celular, 
            'whatsapp' => $whatsapp, 
            'afiliacion' => $afiliacion, 
            'calle_num' => $calle_num, 
            'colonia' => $persona['colonia'], 
            'demarcacion' => $persona['demarcacion'], 
            'seccion' => $persona['seccion'], 
            'ciudad' => $persona['ciudad'],
            'usuario' => $persona['id_nivel_usuario']
        ];
}
        
        array_push($datos, $tmp);
    }
    $json = json_encode($datos);
}

if($flag == 0){
    $data = array();
    foreach ($personas as $persona) {
        if($persona['cve_elec'] == "0" || $persona['cve_elec'] == ""){
        $whatsapp = $persona['whatsapp'] == 0 || $persona['whatsapp'] == '' ? '<td class="t_sin_whatsapp">No tiene</td>' : '
        <td class="t_whatsapp"><a target="_blank" href="https://wa.me/52'.$persona['celular'].'"><i class="fa-brands fa-whatsapp" style="font-weight:900"></i></a></td>';
        
        $celular = $persona['celular'] == 0 || $persona['celular'] == '' ? '<td class="t_sin_telefono">No tiene</td>' : '
        <td class="t_telefono"><a href="tel:'.$persona['celular'].'">'.$persona['celular'].'</a></td>';

        $apellidos = $persona['ap_pat'].' '.$persona['ap_mat'];

        $sexo = $persona['sexo'] == 'M' ? 'Masculino' : 'Femenino';

        $edad = floor($persona['edad']);

        $afiliacion = $persona['afiliacion'] == 1 ? 'Sí' : 'No';

        $calle_num = '
        <a class="td_red" href = "https://maps.google.com/?q='.$persona['lat'].','.$persona['lng'].'" target="_blank">
            '.$persona['calle_num'].'
        </a>';

        $tmp = [
            'id_persona' => $persona['id_persona'],
            'nombre' => $persona['nombre'], 
            'apellidos' => $apellidos, 
            'edad' => $edad, 
            'sexo' =>$sexo, 
            'celular' => $celular, 
            'whatsapp' => $whatsapp, 
            'calle_num' => $calle_num, 
            'colonia' => $persona['colonia'], 
            'demarcacion' => $persona['demarcacion'], 
            'seccion' => $persona['seccion'], 
            'ciudad' => $persona['ciudad']
        ];
        array_push($datos, $tmp);}
    }
    $json = json_encode($datos);
}


echo $json;



?>