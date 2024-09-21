<?php
$session_lifetime = 3600 * 24 * 4; // 4 days
session_set_cookie_params ($session_lifetime);
session_start();
require '../conexion_db.php';
require '../functions.php';
comprobar_login_ajax();

$respuesta = [];

$incoming_id = $_POST['persona'];

$statement = $con->prepare('SELECT id_usuario FROM persona WHERE id_persona = '.$incoming_id);
$statement->execute();
$tmp_usuario = $statement->fetch();

if($tmp_usuario['id_usuario'] == NULL){
    $sql = 'SELECT *, p.nombre AS personNombre FROM persona p WHERE id_persona = '.$incoming_id.';';
} else {
    $sql = 'SELECT *, u.nombre AS userNombre, p.nombre AS personNombre FROM persona p INNER JOIN usuario u ON p.id_usuario = u.id_usuario WHERE id_persona = '.$incoming_id.';';
}

$statement = $con->prepare($sql);
$statement->execute();
$persona = $statement->fetch();

//echo $sql;
$nombre = $persona['personNombre'].' '.$persona['ap_pat'].' '.$persona['ap_mat'];

$celuar = '<a href="tel:'.$persona['celular'].'">'.$persona['celular'].'</a>';

$whatsapp = $persona['whatsapp'] == 0 || $persona['whatsapp'] == '' ? 'No tiene' : '
    <a target="_blank" href="https://wa.me/52'.$persona['celular'].'"><i class="fa-brands fa-whatsapp" style="font-weight:900"></i></a>';




$f_nac = $persona['f_nac'];
$hoy = date("Y-m-d");
$dif = date_diff(date_create($f_nac), date_create($hoy));
$edad = $dif->format('%y');

$sexo = $persona['sexo'] == 'M' ? 'Masculino' : 'Femenino';



$afiliacion = $persona['afiliacion'] == 1 ? 'Sí' : 'No';

$domicilio = $persona['num_int'] == 0 ? 
    
    '
    <a href = "https://maps.google.com/?q='.$persona['lat'].','.$persona['lng'].'" target="_blank">
        '.$persona['calle_num'].', '.$persona['colonia'].', '.$persona['cp'].'; '.$persona['ciudad'].', '.$persona['estado'].'
    </a>': 

    '
    <a href = "https://maps.google.com/?q='.$persona['lat'].','.$persona['lng'].'" target="_blank">
        '.$persona['calle_num'].' '.$persona['num_int'].', '.$persona['colonia'].', '.$persona['cp'].'; '.$persona['ciudad'].', '.$persona['estado'].'
    </a>';

if($tmp_usuario['id_usuario'] == NULL){
    $promotor = 'Pendiente por asignar';
} else{
    $promotor = $persona['userNombre'].' '.$persona['apellidos'];
}

if($persona['cve_elec'] == '0'){
    $cve_elec = 'sin captura';
    $curp = 'sin captura';
    
} else {
    $cve_elec = $persona['cve_elec'];
    $curp = $persona['curp'];
    
}

$fb = $persona['fb'] == '0' ? 'No tiene' : $persona['fb'];

$respuesta = [
    'cve_elec' => $cve_elec,
    'nombre' => $nombre,
    'edad' => $edad,
    'sexo' => $sexo,
    'curp' => $curp,
    'fb' => $fb,
    'celular' => $celuar,
    'whatsapp' => $whatsapp,
    'afiliacion' => $afiliacion,
    'domicilio' => $domicilio,
    'demarcacion' => $persona['demarcacion'],
    'seccion' => $persona[22],
    'promotor' => $promotor,
    'lat' => $persona['lat'],
    'lng' => $persona['lng'],
    'id_promotor' => $persona['id_nivel_usuario']
    
    
];
//print_r($persona);
echo json_encode($respuesta);

?>