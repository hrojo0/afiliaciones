<?php
$session_lifetime = 3600 * 24 * 4; // 4 days
session_set_cookie_params ($session_lifetime);
session_start();
require '../conexion_db.php';
require '../functions.php';
comprobar_login_ajax();
require "../includes/session.php";

$flag = $_POST['flag'];

//VARIABLES PARA PAGINADO
$pagina = $_POST['pagina'];
$limit = $_POST['limit'];
$flag = $_POST['flag'];
$start_from = ($pagina-1) * $limit; 
$cont = 0;

$flag_cve = $flag == 0 ? ' AND p.cve_elec = "0" ' : ' AND p.cve_elec != "0" ';

//DECLARACIÓN DE VARIABLES Y CASTEO DE POST
$row_persona = "";
$incoming_edad = strval($_POST["edad"]);
$incoming_sexo = strval($_POST["sexo"]);
$incoming_demarcacion = strval($_POST["demarcacion"]);
//$incoming_zona = strval($_POST["zona"]);

$incoming_zona = isset($_POST["zona"]) ? strval($_POST["zona"]) : '0';

$incoming_afiliacion = strval($_POST["afiliacion"]);
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
$incoming_afiliacion = isset($incoming_afiliacion) ? $incoming_afiliacion : "0";
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
        $sql_personas_filter = 'SELECT p.id_persona,  p.cve_elec, p.nombre, p.ap_pat, p.ap_mat, DATEDIFF(CAST(UTC_DATE() AS DATE), p.f_nac) / 365.25 AS edad, p.sexo, p.celular, p.telefono, p.afiliacion, p.calle_num, p.colonia, p.cp, p.seccion, p.ciudad, p.estado, p.lat, p.lng FROM persona  p WHERE 1 '.$flag_cve;
        
        break;
    case "3":
        $sql_personas_filter = 'SELECT p.id_persona, p.cve_elec, p.nombre, p.ap_pat, p.ap_mat, DATEDIFF(CAST(UTC_DATE() AS DATE),p.f_nac) / 365.25 AS edad, p.sexo, p.celular, p.telefono, p.afiliacion, p.calle_num, p.colonia, p.cp, p.seccion, p.ciudad, p.estado, p.lat, p.lng FROM persona p INNER JOIN municipio m WHERE p.ciudad = m.municipio AND m.id_municipio = "'.$usr_mun.'" '.$flag_cve;
        break;
    case "5":        
        $sql_personas_filter = 'SELECT p.id_persona,  p.cve_elec, p.nombre, p.ap_pat, p.ap_mat, DATEDIFF(CAST(UTC_DATE() AS DATE),p.f_nac) / 365.25 AS edad, p.sexo, p.celular, p.telefono, p.afiliacion, p.calle_num, p.colonia, p.cp, p.seccion, p.ciudad, p.estado, p.lat, p.lng FROM persona p INNER JOIN municipio m WHERE p.ciudad = m.municipio AND m.id_municipio = "'.$usr_mun.'" AND p.demarcacion = "'.$usr_dem.'" '.$flag_cve;
        break;
    case "6":
        $sql_personas_filter = 'SELECT p.id_persona,  p.cve_elec, p.nombre, p.ap_pat, p.ap_mat, DATEDIFF(CAST(UTC_DATE() AS DATE),p.f_nac) / 365.25 AS edad, p.sexo, p.celular, p.telefono, p.afiliacion, p.calle_num, p.colonia, p.cp, p.seccion, p.ciudad, p.estado, p.lat, p.lng FROM persona p INNER JOIN municipio m WHERE p.ciudad = m.municipio AND m.id_municipio = "'.$usr_mun.'" AND p.demarcacion = "'.$usr_dem.'" AND p.zona ="'.$usr_zona.'" '.$flag_cve;
        break;
    case "4":
        $statement = $con ->prepare('SELECT demarcaciones FROM coordinacion_dems WHERE id_usuario = "'.$usr_usuario.'"');
        $statement->execute();
        $user_dems = $statement->fetch();
        $user_dems = $user_dems[0];
        $user_dems = substr($user_dems, 0 , -1);
        $sql_personas_filter = 'SELECT p.id_persona,  p.cve_elec, p.nombre, p.ap_pat, p.ap_mat, DATEDIFF(CAST(UTC_DATE() AS DATE),p.f_nac) / 365.25 AS edad, p.sexo, p.celular, p.telefono, p.afiliacion, p.calle_num, p.colonia, p.cp, p.seccion, p.ciudad, p.estado, p.lat, p.lng FROM persona p INNER JOIN municipio m WHERE p.ciudad = m.municipio AND m.id_municipio = "'.$usr_mun.'" AND p.seccion IN ('.$user_dems.') '.$flag_cve;
        break;
    case "7":
        $sql_personas_filter = 'SELECT id_persona,  cve_elec, nombre, ap_pat, ap_mat, DATEDIFF(CAST(UTC_DATE() AS DATE),f_nac) / 365.25 AS edad, sexo, celular, telefono, afiliacion, calle_num, colonia, cp, seccion, ciudad, estado, lat, lng FROM persona p WHERE id_usuario = "'.$usr_usuario.'" '.$flag_cve;
        break;
}
$statement = $con->prepare($sql_personas_filter.$nombre.' '.$edad.' '.$sexo.' '.$afiliacion.' '.$municipio.' '.$demarcacion.' '.$zona.' '.$colonia.' '.$seccion.' LIMIT '.$start_from.', '.$limit.';');
$statement->execute();
$personas = $statement->fetchAll();

//sql para obtener el número de páginas dependiendo del filtro y nivel de usuario
switch($usr_nivel){
    case "1":
    case "2":
        $sql_num_paginas = 'SELECT COUNT(*) FROM persona p WHERE 1 '.$flag_cve;
        break;
    case "3":
        $sql_num_paginas = 'SELECT COUNT(*) FROM persona p INNER JOIN municipio m WHERE p.ciudad = m.municipio AND m.id_municipio =  "'.$usr_mun.'" '.$flag_cve;
        break;
    case "5":
        $sql_num_paginas = 'SELECT COUNT(*) FROM persona p INNER JOIN municipio m WHERE p.ciudad = m.municipio AND m.id_municipio = "'.$usr_mun.'" AND p.demarcacion = "'.$usr_dem.'" '.$flag_cve;
        break;
    case "6":
        $sql_num_paginas = 'SELECT COUNT(*) FROM persona p INNER JOIN municipio m WHERE p.ciudad = m.municipio AND m.id_municipio = "'.$usr_mun.'" AND p.demarcacion = "'.$usr_dem.'" '.$flag_cve;
        break;
    case "4":
        $statement = $con ->prepare('SELECT demarcaciones FROM coordinacion_dems WHERE id_usuario = "'.$usr_usuario.'"');
        $statement->execute();
        $user_dems = $statement->fetch();
        $user_dems = $user_dems[0];
        $user_dems = substr($user_dems, 0 , -1);
        $sql_num_paginas = 'SELECT COUNT(*) FROM persona p INNER JOIN municipio m WHERE p.ciudad = m.municipio AND m.id_municipio =  "'.$usr_mun.'" AND p.seccion IN('.$user_dems.') '.$flag_cve;
        break;
    case "7":
        $sql_num_paginas = 'SELECT COUNT(*) FROM persona p WHERE id_usuario = "'.$usr_usuario.'" '.$flag_cve;
        break;
}
$statement = $con->prepare($sql_num_paginas.$nombre.''.$edad.' '.$sexo.' '.$afiliacion.' '.$municipio.' '.$colonia.' '.$seccion.' '.$flag_cve);
$statement->execute();
$total_personas_filtro = $statement->fetch();


$contador = $start_from + 1;

foreach($personas as $persona){
    if($flag == 1){
        //Obtiene la edad entera sin puntos decimales -> ej 31.895 = 31
        $edad_c = explode(".", $persona['edad']);
        $edad = $edad_c[0];    

        $sexo = $persona['sexo'] == 'M' ? 'Masculino' : 'Femenino';

        $telefono = $persona['telefono'] == 0 || $persona['telefono'] == '' ? '<td class="t_sin_telefono">No tiene</td>' : '
        <td class="t_telefono"><a href="tel:'.$persona['telefono'].'">'.$persona['telefono'].'</a></td>';

        $apellidos = $persona['ap_pat'].' '.$persona['ap_mat'];

        $afiliacion = $persona['afiliacion'] == 1 ? 'Si' : 'No';

        $calle_num = '
        <a href = "https://maps.google.com/?q='.$persona['lat'].','.$persona['lng'].'" target="_blank">
            '.$persona['calle_num'].'
        </a>';



        $row_persona = $row_persona.'
        <tr class="row__person" id="'.$persona['id_persona'].'">
            <td class="t_contador">'.$contador++.'</td>
            <td class="t_nombre t_capi">'.$persona['nombre'].'</td>
            <td class="t_apellidos t_capi">'.$apellidos.'</td>
            <td class="t_edad">'.$edad.'</td>
            <td class="t_sexo t_capi">'.$sexo.'</td>
            <td class="t_celular"><a href="tel:'.$persona['celular'].'">'.$persona['celular'].'</a></td>
            '.$telefono.'
            <td class="t_afiliacion">'.$afiliacion.'</td>
            <td class="t_calle">'.$calle_num.'</td>
            <td class="t_colonia">'.$persona['colonia'].'</td>
            <td class="t_seccion">'.$persona['seccion'].'</td>
            <td class="t_ciudad">'.$persona['ciudad'].'</td>
            <td class="t_estado">'.$persona['estado'].'</td>
        </tr>';
        $cont += 1;
    }
    if($flag == 0){
        //Obtiene la edad entera sin puntos decimales -> ej 31.895 = 31
        $edad_c = explode(".", $persona['edad']);
        $edad = $edad_c[0];    

        $sexo = $persona['sexo'] == 'M' ? 'Masculino' : 'Femenino';

        $telefono = $persona['telefono'] == 0 || $persona['telefono'] == '' ? '<td class="t_sin_telefono">No tiene</td>' : '
        <td class="t_telefono"><a href="tel:'.$persona['telefono'].'">'.$persona['telefono'].'</a></td>';

        $apellidos = $persona['ap_pat'].' '.$persona['ap_mat'];

        $afiliacion = $persona['afiliacion'] == 1 ? 'Si' : 'No';

        $calle_num = '
        <a href = "https://maps.google.com/?q='.$persona['lat'].','.$persona['lng'].'" target="_blank">
            '.$persona['calle_num'].'
        </a>';



        $row_persona = $row_persona.'
        <tr class="row__person" id="'.$persona['id_persona'].'">
            <td class="t_contador">'.$contador++.'</td>
            <td class="t_nombre t_capi">'.$persona['nombre'].'</td>
            <td class="t_apellidos t_capi">'.$apellidos.'</td>
            <td class="t_edad">'.$edad.'</td>
            <td class="t_sexo t_capi">'.$sexo.'</td>
            <td class="t_celular"><a href="tel:'.$persona['celular'].'">'.$persona['celular'].'</a></td>
            '.$telefono.'
            <td class="t_afiliacion">'.$afiliacion.'</td>
            <td class="t_calle">'.$calle_num.'</td>
            <td class="t_colonia">'.$persona['colonia'].'</td>
            <td class="t_seccion">'.$persona['seccion'].'</td>
            <td class="t_ciudad">'.$persona['ciudad'].'</td>
            <td class="t_estado">'.$persona['estado'].'</td>
        </tr>';
        $cont += 1;
    }
}

$statement = $con->prepare('SELECT id_municipio FROM municipio WHERE municipio = "'.$incoming_mun.'";');
$statement->execute();
$id_mun = $statement->fetch();

//echo $incoming_mun;
$tot_personas_filtro = $total_personas_filtro[0] != 0 ? $total_personas_filtro[0] : 0;

echo $row_persona.'<p id="total_personas_filtro" style="display: none">'.$tot_personas_filtro.'</p>'.'<p id="pagina_activa" style="display: none">'.$pagina.'</p>';

//echo $row_persona.'<p id="total_personas_filtro" style="display: none">'.$tot_personas_filtro.'</p>'.'<p id="pagina_activa" style="display: none">'.$pagina.'</p>';


?>