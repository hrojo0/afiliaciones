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
$promotor = $_POST['promotor'];


$row_persona = "";
$contador = 1;


//$flag_cve = $flag == 0 ? ' AND (p.cve_elec = "0" OR p.cve_elec = "")' : ' AND p.cve_elec != "0"';
$flag_cve = $flag == 0 ? ' AND (p.cve_elec = "0" OR p.cve_elec = "" OR u.id_nivel_usuario IS NULL)' : ' AND (p.cve_elec != "0" OR u.id_nivel_usuario IS NULL)';



$sql_personas = 'SELECT m.id_municipio, p.id_persona, p.cve_elec, p.nombre, p.ap_pat, p.ap_mat, p.f_nac, p.sexo, p.celular, p.whatsapp, p.afiliacion, p.calle_num, p.colonia, p.cp, p.ciudad, p.estado, p.demarcacion, p.seccion, p.lat, p.lng, p.id_usuario FROM persona p INNER JOIN municipio m ON p.ciudad = m.municipio WHERE p.ciudad = m.municipio AND p.id_usuario = "'.$promotor.'" ';

$statement = $con->prepare($sql_personas);
$statement->execute();
$personas = $statement->fetchAll();

print_r($personas);

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
        
        $data[] = array($persona['id_persona'],$persona['nombre'], $apellidos, $edad, $sexo, $celular, $whatsapp, $afiliacion, $calle_num, $persona['colonia'], $persona['demarcacion'], $persona['seccion'], $persona['ciudad']);
    }
    $json = json_encode(array("data" => $data));
}


//echo '<tbody>'.$row_persona.'</t  body>';

//echo ($json);

$myfile = fopen("../data/arrays_reporte.txt", "w") or die("Unable to open file!");
$txt = $json;
fwrite($myfile, $txt);
fclose($myfile);

?>