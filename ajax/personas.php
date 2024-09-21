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
$pagina = $_POST['pagina'];
$limit = $_POST['limit'];
$flag = $_POST['flag'];
$start_from = ($pagina-1) * $limit;  

$row_persona = "";
$contador = 1;


$flag_cve = $flag == 0 ? ' AND p.cve_elec = "0" ' : ' AND p.cve_elec != "0" ';

switch($usr_nivel){
    case "1":
    case "2":
        $sql_personas = 'SELECT p.id_persona, p.cve_elec, p.nombre, p.ap_pat, p.ap_mat, p.f_nac, p.sexo, p.celular, p.whatsapp, p.afiliacion, p.calle_num, p.colonia, p.cp, p.ciudad, p.estado, p.demarcacion, p.seccion, p.lat, p.lng, p.id_usuario FROM persona p WHERE 1'.$flag_cve.' LIMIT '.$start_from.', '.$limit.';';
        break;
    case "3":
        $sql_personas = 'SELECT m.id_municipio, p.id_persona, p.cve_elec, p.nombre, p.ap_pat, p.ap_mat, p.f_nac, p.sexo, p.celular, p.whatsapp, p.afiliacion, p.calle_num, p.colonia, p.cp, p.ciudad, p.estado, p.demarcacion, p.seccion, p.lat, p.lng, p.id_usuario FROM persona p INNER JOIN municipio m ON p.ciudad = m.municipio WHERE p.ciudad = m.municipio AND m.id_municipio = "'.$usr_mun.'" '.$flag_cve.' LIMIT '.$start_from.', '.$limit.';';
        break;
    case "5":        
        $sql_personas = 'SELECT p.id_persona, p.cve_elec, p.nombre, p.ap_pat, p.ap_mat, p.f_nac, p.sexo, p.celular, p.whatsapp, p.afiliacion, p.calle_num, p.colonia, p.cp, p.ciudad, p.estado, p.demarcacion, p.seccion, p.lat, p.lng, p.id_usuario FROM persona p INNER JOIN municipio m ON p.ciudad = m.municipio WHERE p.ciudad = m.municipio AND m.id_municipio = "'.$usr_mun.'" AND p.demarcacion = "'.$usr_dem.'" '.$flag_cve.' LIMIT '.$start_from.', '.$limit.';';
        break;
    case "6":
        $sql_personas = 'SELECT p.id_persona, p.cve_elec, p.nombre, p.ap_pat, p.ap_mat, p.f_nac, p.sexo, p.celular, p.whatsapp, p.afiliacion, p.calle_num, p.colonia, p.cp, p.ciudad, p.estado, p.demarcacion, p.seccion, p.lat, p.lng, p.id_usuario FROM persona p INNER JOIN municipio m ON p.ciudad = m.municipio WHERE p.ciudad = m.municipio AND m.id_municipio = "'.$usr_mun.'" AND p.demarcacion = "'.$usr_dem.'" AND p.zona = "'.$usr_zona.'" '.$flag_cve.' LIMIT '.$start_from.', '.$limit.';';
        break;
    case "4":
        $statement = $con ->prepare('SELECT demarcaciones FROM coordinacion_dems WHERE id_usuario = "'.$usr_usuario.'"');
        $statement->execute();
        $user_dems = $statement->fetch();
        $user_dems = $user_dems[0];
        $user_dems = substr($user_dems, 0 , -1);
        $sql_personas = 'SELECT p.id_persona, p.cve_elec, p.nombre, p.ap_pat, p.ap_mat, p.f_nac, p.sexo, p.celular, p.whatsapp, p.afiliacion, p.calle_num, p.colonia, p.cp, p.ciudad, p.estado, p.demarcacion, p.seccion, p.lat, p.lng, p.id_usuario FROM persona p INNER JOIN municipio m ON p.ciudad = m.municipio WHERE p.ciudad = m.municipio AND m.id_municipio = "'.$usr_mun.'" AND p.seccion IN ('.$user_dems.') '.$flag_cve.' LIMIT '.$start_from.', '.$limit.';';
        break;
    case "7":
        $statement = $con -> prepare('SELECT m.municipio FROM municipio m WHERE m.id_municipio = '.$usr_mun);
        $statement -> execute();
        $temp_mun = $statement -> fetch();
        
        $sql_personas = 'SELECT p.id_persona, p.cve_elec, p.nombre, p.ap_pat, p.ap_mat, p.f_nac, p.sexo, p.celular, p.whatsapp, p.afiliacion, p.calle_num, p.colonia, p.cp, p.ciudad, p.estado, p.demarcacion, p.seccion, p.lat, p.lng, p.id_usuario FROM persona p WHERE p.id_usuario = "'.$usr_usuario.'" AND p.ciudad = "'.$temp_mun['municipio'].'" AND p.demarcacion = "'.$usr_dem.'" AND p.seccion = "'.$usr_seccion.'" '.$flag_cve.' LIMIT '.$start_from.', '.$limit.';';
        break;
}

$statement = $con->prepare($sql_personas);
$statement->execute();
$personas = $statement->fetchAll();

foreach($personas as $persona){
        
    
    if($flag == 1){
        $whatsapp = $persona['whatsapp'] == 0 || $persona['whatsapp'] == '' ? '<td class="t_sin_whatsapp">No tiene</td>' : '
        <td class="t_whatsapp"><a target="_blank" href="https://wa.me/52'.$persona['celular'].'"><i class="fa-brands fa-whatsapp" style="font-weight:900"></i></a></td>';

        $apellidos = $persona['ap_pat'].' '.$persona['ap_mat'];

        $sexo = $persona['sexo'] == 'M' ? 'Masculino' : 'Femenino';

        $f_nac = $persona['f_nac'];
        $hoy = date("Y-m-d");
        $dif = date_diff(date_create($f_nac), date_create($hoy));
        $edad = $dif->format('%y');

        $afiliacion = $persona['afiliacion'] == 1 ? 'Sí' : 'No';

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
            '.$whatsapp.'
            <td class="t_afiliacion">'.$afiliacion.'</td>
            <td class="t_calle">'.$calle_num.'</td>
            <td class="t_colonia">'.$persona['colonia'].'</td>
            <td class="t_seccion">'.$persona['seccion'].'</td>
            <td class="t_ciudad">'.$persona['ciudad'].'</td>
            <td class="t_estado">'.$persona['estado'].'</td>
        </tr>';
    }
    
    if($flag == 0){
        
        $whatsapp = $persona['whatsapp'] == 0 || $persona['whatsapp'] == '' ? '<td class="t_sin_whatsapp">No tiene</td>' : '
        <td class="t_whatsapp"><a target="_blank" href="https://wa.me/52'.$persona['celular'].'"><i class="fa-brands fa-whatsapp" style="font-weight:900"></i></a></td>';

        $apellidos = $persona['ap_pat'].' '.$persona['ap_mat'];

        $sexo = $persona['sexo'] == 'M' ? 'Masculino' : 'Femenino';

        $f_nac = $persona['f_nac'];
        $hoy = date("Y-m-d");
        $dif = date_diff(date_create($f_nac), date_create($hoy));
        $edad = $dif->format('%y');

        $afiliacion = $persona['afiliacion'] == 1 ? 'Sí' : 'No';

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
            '.$whatsapp.'
            
            <td class="t_calle">'.$calle_num.'</td>
            <td class="t_colonia">'.$persona['colonia'].'</td>
            <td class="t_seccion">'.$persona['seccion'].'</td>
            <td class="t_ciudad">'.$persona['ciudad'].'</td>
            <td class="t_estado">'.$persona['estado'].'</td>
        </tr>';
    }
}

echo '<tbody>'.$row_persona.'</tbody>';

?>