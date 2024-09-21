<?php
$session_lifetime = 3600 * 24 * 4; // 4 days
session_set_cookie_params ($session_lifetime);
session_start();
require '../conexion_db.php';
require '../functions.php';
//comprobar_nivel_ajax();
comprobar_login_ajax();
//variables sesion para mostrar registros asociados al perfil loggeado
require "../includes/session.php";


//VARIABLES PARA PAGINADO
$flag = $_POST['flag'];


$row_persona = "";
$contador = 1;


//$flag_cve = $flag == 0 ? ' AND (p.cve_elec = "0" OR p.cve_elec = "")' : ' AND p.cve_elec != "0"';
$flag_cve = $flag == 0 ? ' AND (p.cve_elec = "0" OR p.cve_elec = "" OR u.id_nivel_usuario IS NULL)' : ' AND (p.cve_elec != "0" OR u.id_nivel_usuario IS NULL)';

switch($usr_nivel){
    case "1":
    case "2":
        /*$sql_personas = 'SELECT p.id_persona, p.cve_elec, p.nombre, p.ap_pat, p.ap_mat, p.f_nac, p.sexo, p.celular, p.whatsapp, p.afiliacion, p.calle_num, p.colonia, p.cp, p.ciudad, p.estado, p.demarcacion, p.seccion, p.lat, p.lng, p.id_usuario FROM persona p WHERE 1'.$flag_cve.';';*/
        
        $sql_personas =
        
        'SELECT * FROM (
           SELECT m.id_municipio, p.id_persona, p.cve_elec, p.nombre, p.ap_pat, p.ap_mat, p.f_nac, p.sexo, p.celular, p.whatsapp, p.afiliacion, p.calle_num, p.colonia, p.cp, p.ciudad, p.estado, p.demarcacion, p.seccion, p.lat, p.lng, p.id_usuario, u.id_nivel_usuario 
            FROM persona p 
            INNER JOIN municipio m ON p.ciudad = m.municipio 
            LEFT JOIN usuario u ON p.id_usuario = u.id_usuario 
            WHERE p.ciudad = m.municipio '.$flag_cve.'
            UNION ALL
            SELECT m.id_municipio, p.id_persona, p.cve_elec, p.nombre, p.ap_pat, p.ap_mat, p.f_nac, p.sexo, p.celular, p.whatsapp, p.afiliacion, p.calle_num, p.colonia, p.cp, p.ciudad, p.estado, p.demarcacion, p.seccion, p.lat, p.lng, p.id_usuario, u.id_nivel_usuario 
            FROM persona p 
            INNER JOIN municipio m ON p.ciudad = m.municipio 
            INNER JOIN usuario u ON p.id_usuario = u.id_usuario 
            WHERE p.ciudad = m.municipio  AND u.id_nivel_usuario = "8"
        ) AS subquery
            GROUP BY cve_elec';
        
        break;
    case "3":
        /*$sql_personas = 'SELECT m.id_municipio, p.id_persona, p.cve_elec, p.nombre, p.ap_pat, p.ap_mat, p.f_nac, p.sexo, p.celular, p.whatsapp, p.afiliacion, p.calle_num, p.colonia, p.cp, p.ciudad, p.estado, p.demarcacion, p.seccion, p.lat, p.lng, p.id_usuario FROM persona p INNER JOIN municipio m ON p.ciudad = m.municipio LEFT JOIN usuario u ON p.id_usuario = u.id_usuario WHERE p.ciudad = m.municipio AND m.id_municipio = "'.$usr_mun.'" '.$flag_cve.';';*/
        
        $sql_personas =
       'SELECT * FROM (
           SELECT m.id_municipio, p.id_persona, p.cve_elec, p.nombre, p.ap_pat, p.ap_mat, p.f_nac, p.sexo, p.celular, p.whatsapp, p.afiliacion, p.calle_num, p.colonia, p.cp, p.ciudad, p.estado, p.demarcacion, p.seccion, p.lat, p.lng, p.id_usuario, u.id_nivel_usuario 
            FROM persona p 
            INNER JOIN municipio m ON p.ciudad = m.municipio 
            LEFT JOIN usuario u ON p.id_usuario = u.id_usuario 
            WHERE p.ciudad = m.municipio AND m.id_municipio = "'.$usr_mun.'" '.$flag_cve.'
            UNION ALL
            SELECT m.id_municipio, p.id_persona, p.cve_elec, p.nombre, p.ap_pat, p.ap_mat, p.f_nac, p.sexo, p.celular, p.whatsapp, p.afiliacion, p.calle_num, p.colonia, p.cp, p.ciudad, p.estado, p.demarcacion, p.seccion, p.lat, p.lng, p.id_usuario, u.id_nivel_usuario 
            FROM persona p 
            INNER JOIN municipio m ON p.ciudad = m.municipio 
            INNER JOIN usuario u ON p.id_usuario = u.id_usuario 
            WHERE p.ciudad = m.municipio AND m.id_municipio = "'.$usr_mun.'" AND u.id_nivel_usuario = "8"
        ) AS subquery
            GROUP BY cve_elec';
        
        break;
    case "5":        
        /*  $sql_personas = 'SELECT p.id_persona, p.cve_elec, p.nombre, p.ap_pat, p.ap_mat, p.f_nac, p.sexo, p.celular, p.whatsapp, p.afiliacion, p.calle_num, p.colonia, p.cp, p.ciudad, p.estado, p.demarcacion, p.seccion, p.lat, p.lng, p.id_usuario, p.demarcacion FROM persona p INNER JOIN municipio m ON p.ciudad = m.municipio WHERE p.ciudad = m.municipio AND m.id_municipio = "'.$usr_mun.'" AND p.demarcacion = "'.$usr_dem.'" '.$flag_cve.';';*/
        
        $sql_personas = 
        'SELECT * FROM (
            SELECT m.id_municipio, p.id_persona, p.cve_elec, p.nombre, p.ap_pat, p.ap_mat, p.f_nac, p.sexo, p.celular, p.whatsapp, p.afiliacion, p.calle_num, p.colonia, p.cp, p.ciudad, p.estado, p.demarcacion, p.seccion, p.lat, p.lng, p.id_usuario, u.id_nivel_usuario
            FROM persona p 
            INNER JOIN municipio m ON p.ciudad = m.municipio 
            LEFT JOIN usuario u ON p.id_usuario = u.id_usuario 
            WHERE p.ciudad = m.municipio AND m.id_municipio = "'.$usr_mun.'" AND p.demarcacion = "'.$usr_dem.'" '.$flag_cve.'
            UNION ALL
            SELECT m.id_municipio, p.id_persona, p.cve_elec, p.nombre, p.ap_pat, p.ap_mat, p.f_nac, p.sexo, p.celular, p.whatsapp, p.afiliacion, p.calle_num, p.colonia, p.cp, p.ciudad, p.estado, p.demarcacion, p.seccion, p.lat, p.lng, p.id_usuario, u.id_nivel_usuario 
            FROM persona p 
            INNER JOIN municipio m ON p.ciudad = m.municipio 
            INNER JOIN usuario u ON p.id_usuario = u.id_usuario 
            WHERE p.ciudad = m.municipio AND p.demarcacion = "'.$usr_dem.'" AND u.id_nivel_usuario = "8"
        ) AS subquery
            GROUP BY cve_elec';
        break;
    case "6":
        /*$sql_personas = 'SELECT p.id_persona, p.cve_elec, p.nombre, p.ap_pat, p.ap_mat, p.f_nac, p.sexo, p.celular, p.whatsapp, p.afiliacion, p.calle_num, p.colonia, p.cp, p.ciudad, p.estado, p.demarcacion, p.seccion, p.lat, p.lng, p.id_usuario FROM persona p INNER JOIN municipio m ON p.ciudad = m.municipio WHERE p.ciudad = m.municipio AND m.id_municipio = "'.$usr_mun.'" AND p.demarcacion = "'.$usr_dem.'" AND p.zona = "'.$usr_zona.'" '.$flag_cve.';';*/
        
        $sql_personas = 
        'SELECT * FROM (
            SELECT m.id_municipio, p.id_persona, p.cve_elec, p.nombre, p.ap_pat, p.ap_mat, p.f_nac, p.sexo, p.celular, p.whatsapp, p.afiliacion, p.calle_num, p.colonia, p.cp, p.ciudad, p.estado, p.demarcacion, p.seccion, p.lat, p.lng, p.id_usuario, u.id_nivel_usuario
            FROM persona p 
            INNER JOIN municipio m ON p.ciudad = m.municipio
            INNER JOIN usuario u ON p.id_usuario = u.id_usuario 
            WHERE p.ciudad = m.municipio AND m.id_municipio = "'.$usr_mun.'" AND p.demarcacion = "'.$usr_dem.'" AND p.zona = "'.$usr_zona.'" '.$flag_cve.'
            UNION ALL
            SELECT m.id_municipio, p.id_persona, p.cve_elec, p.nombre, p.ap_pat, p.ap_mat, p.f_nac, p.sexo, p.celular, p.whatsapp, p.afiliacion, p.calle_num, p.colonia, p.cp, p.ciudad, p.estado, p.demarcacion, p.seccion, p.lat, p.lng, p.id_usuario, u.id_nivel_usuario 
            FROM persona p 
            INNER JOIN municipio m ON p.ciudad = m.municipio 
            INNER JOIN usuario u ON p.id_usuario = u.id_usuario 
            WHERE p.ciudad = m.municipio AND p.demarcacion = "'.$usr_dem.'" AND p.zona = "'.$usr_zona.'"  AND u.id_nivel_usuario = "8"
        ) AS subquery
        GROUP BY cve_elec';
        
        
        break;
    case "4":
        $statement = $con ->prepare('SELECT demarcaciones FROM coordinacion_dems WHERE id_usuario = "'.$usr_usuario.'"');
        $statement->execute();
        $user_dems = $statement->fetch();
        $user_dems = $user_dems[0];
        $user_dems = substr($user_dems, 0 , -1);
        /* $sql_personas = 'SELECT p.id_persona, p.cve_elec, p.nombre, p.ap_pat, p.ap_mat, p.f_nac, p.sexo, p.celular, p.whatsapp, p.afiliacion, p.calle_num, p.colonia, p.cp, p.ciudad, p.estado, p.demarcacion, p.seccion, p.lat, p.lng, p.id_usuario FROM persona p INNER JOIN municipio m ON p.ciudad = m.municipio WHERE p.ciudad = m.municipio AND m.id_municipio = "'.$usr_mun.'" AND p.zona = "'.$usr_zona.'" AND p.seccion IN ('.$user_dems.') '.$flag_cve.';';*/
        
        $sql_personas = 
           ' SELECT * FROM (
                SELECT m.id_municipio, p.id_persona, p.cve_elec, p.nombre, p.ap_pat, p.ap_mat, p.f_nac, p.sexo, p.celular, p.whatsapp, p.afiliacion, p.calle_num, p.colonia, p.cp, p.ciudad, p.estado, p.demarcacion, p.seccion, p.lat, p.lng, p.id_usuario, u.id_nivel_usuario 
                FROM persona p 
                INNER JOIN municipio m ON p.ciudad = m.municipio 
                INNER JOIN usuario u ON p.id_usuario = u.id_usuario 
                WHERE p.ciudad = m.municipio AND m.id_municipio = "'.$usr_mun.'" AND p.zona = "'.$usr_zona.'" AND p.seccion IN ('.$user_dems.') '.$flag_cve.'
                UNION ALL
                SELECT m.id_municipio, p.id_persona, p.cve_elec, p.nombre, p.ap_pat, p.ap_mat, p.f_nac, p.sexo, p.celular, p.whatsapp, p.afiliacion, p.calle_num, p.colonia, p.cp, p.ciudad, p.estado, p.demarcacion, p.seccion, p.lat, p.lng, p.id_usuario, u.id_nivel_usuario 
                FROM persona p 
                INNER JOIN municipio m ON p.ciudad = m.municipio 
                INNER JOIN usuario u ON p.id_usuario = u.id_usuario 
                WHERE p.ciudad = m.municipio AND p.demarcacion = "'.$usr_dem.'" AND p.zona = "'.$usr_zona.'" AND u.id_nivel_usuario = "8"
            ) AS subquery
            GROUP BY cve_elec;';

        
        
        
        break;
    case "7":
        $statement = $con -> prepare('SELECT m.municipio FROM municipio m WHERE m.id_municipio = '.$usr_mun);
        $statement -> execute();
        $temp_mun = $statement -> fetch();
        
        
       $sql_personas = 
           'SELECT m.id_municipio, p.id_persona, p.cve_elec, p.nombre, p.ap_pat, p.ap_mat, p.f_nac, p.sexo, p.celular, p.whatsapp, p.afiliacion, p.calle_num, p.colonia, p.cp, p.ciudad, p.estado, p.demarcacion, p.seccion, p.lat, p.lng, p.id_usuario, u.id_nivel_usuario 
           FROM persona p
           INNER JOIN municipio m ON p.ciudad = m.municipio
           INNER JOIN usuario u ON p.id_usuario = u.id_usuario 
           WHERE p.id_usuario = "'.$usr_usuario.'" AND p.ciudad = "'.$temp_mun['municipio'].'" AND p.demarcacion = "'.$usr_dem.'" AND p.zona = "'.$usr_zona.'" '.$flag_cve.'
           UNION ALL
           SELECT m.id_municipio, p.id_persona, p.cve_elec, p.nombre, p.ap_pat, p.ap_mat, p.f_nac, p.sexo, p.celular, p.whatsapp, p.afiliacion, p.calle_num, p.colonia, p.cp, p.ciudad, p.estado, p.demarcacion, p.seccion, p.lat, p.lng, p.id_usuario, u.id_nivel_usuario
           FROM persona p
           INNER JOIN municipio m ON p.ciudad = m.municipio
           INNER JOIN usuario u ON p.id_usuario = u.id_usuario 
           WHERE p.ciudad = m.municipio AND p.demarcacion = "'.$usr_dem.'" AND p.zona = "'.$usr_zona.'"  '.$flag_cve.' AND u.id_nivel_usuario = "8";';
        
        
        
        break;
    case "8":
        $sql_personas = 'SELECT m.id_municipio, p.id_persona, p.cve_elec, p.nombre, p.ap_pat, p.ap_mat, p.f_nac, p.sexo, p.celular, p.whatsapp, p.afiliacion, p.calle_num, p.colonia, p.cp, p.ciudad, p.estado, p.demarcacion, p.seccion, p.lat, p.lng, p.id_usuario FROM persona p INNER JOIN municipio m ON p.ciudad = m.municipio WHERE p.ciudad = m.municipio AND p.id_usuario = "'.$usr_usuario.'" ;';
        
        break;
}

$statement = $con->prepare($sql_personas);
echo $sql_personas;
$statement->execute();
$personas = $statement->fetchAll();

if($flag == 1){
    $data = array();
    foreach ($personas as $persona) {
        $whatsapp = $persona['whatsapp'] == 0 || $persona['whatsapp'] == '' ? '<td class="t_sin_whatsapp">No tiene</td>' : '
        <td class="t_whatsapp"><a target="_blank" href="https://wa.me/52'.$persona['celular'].'"><i class="fa-brands fa-whatsapp" style="font-weight:900"></i></a></td>';
        
        $celular = $persona['celular'] == 0 || $persona['celular'] == '' ? '<td class="t_sin_telefono">No tiene</td>' : '
        <td class="t_telefono"><a href="tel:'.$persona['celular'].'">'.$persona['celular'].'</a></td>';

        $apellidos = $persona['ap_pat'].' '.$persona['ap_mat'];

        $sexo = $persona['sexo'] == 'M' ? 'Masculino' : 'Femenino';

        $f_nac = $persona['f_nac'];
        $hoy = date("Y-m-d");
        $dif = date_diff(date_create($f_nac), date_create($hoy));
        $edad = $dif->format('%y');

        $afiliacion = $persona['afiliacion'] == 1 ? 'Sí' : 'No';

        $calle_num = '
        <a class="td_red" href = "https://maps.google.com/?q='.$persona['lat'].','.$persona['lng'].'" target="_blank">
            '.$persona['calle_num'].'
        </a>';
        
        $data[] = array($persona['id_persona'],$persona['nombre'], $apellidos, $edad, $sexo, $celular, $whatsapp, $afiliacion, $calle_num, $persona['colonia'], $persona['demarcacion'], $persona['seccion'], $persona['ciudad'], $persona['id_nivel_usuario']);
    }
    $json = json_encode(array("data" => $data));
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

        $f_nac = $persona['f_nac'];
        $hoy = date("Y-m-d");
        $dif = date_diff(date_create($f_nac), date_create($hoy));
        $edad = $dif->format('%y');

        $afiliacion = $persona['afiliacion'] == 1 ? 'Sí' : 'No';

        $calle_num = '
        <a class="td_red" href = "https://maps.google.com/?q='.$persona['lat'].','.$persona['lng'].'" target="_blank">
            '.$persona['calle_num'].'
        </a>';
        
        $data[] = array($persona['id_persona'],$persona['nombre'], $apellidos, $edad, $sexo, $celular, $whatsapp, $calle_num, $persona['colonia'], $persona['demarcacion'], $persona['seccion'], $persona['ciudad']);}
    }
    $json = json_encode(array("data" => $data));
    
}


//echo '<tbody>'.$row_persona.'</tbody>';

//echo ($json);

$myfile = fopen("../data/arrays.txt", "w") or die("Unable to open file!");
$txt = $json;
fwrite($myfile, $txt);
fclose($myfile);

?>