<?php

// Archivo de conexión a la DB
//include('conexion_db.php');
/*include('functions.php');
comprobar_login();*/

// Obtener las direcciones con todos sus datos
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

//$incoming_user_dems = isset($_POST['user_dems']) ? "2,3,4" : "1";

switch($usr_nivel){
    case "1":
    case "2":
        $sql = 'SELECT p.nombre, p.ap_pat, p.ap_mat, p.calle_num, p.colonia, p.afiliacion, p.ciudad, p.estado, p.seccion, p.demarcacion, p.lat, p.lng FROM persona p INNER JOIN municipio m ON p.ciudad = m.municipio WHERE 1 AND p.cve_elec != "0"';
        break;
    case "3":
        $sql = 'SELECT p.nombre, p.ap_pat, p.ap_mat, p.calle_num, p.colonia, p.afiliacion, p.ciudad, p.estado, p.seccion, p.demarcacion, p.lat, p.lng FROM persona p INNER JOIN municipio m ON p.ciudad = m.municipio WHERE p.ciudad = m.municipio AND m.id_municipio =  "'.$usr_mun.'" AND p.cve_elec != "0"';
        break;
    case "5":
        $sql = 'SELECT p.nombre, p.ap_pat, p.ap_mat, p.calle_num, p.colonia, p.afiliacion, p.ciudad, p.estado, p.seccion, p.demarcacion, p.lat, p.lng FROM persona p INNER JOIN municipio m ON p.ciudad = m.municipio WHERE p.ciudad = m.municipio AND m.id_municipio = "'.$usr_mun.'" AND p.demarcacion = "'.$usr_dem.' AND p.cve_elec != "0"';
        break;
    case "6":
        $sql = 'SELECT p.nombre, p.ap_pat, p.ap_mat, p.calle_num, p.colonia, p.afiliacion, p.ciudad, p.estado, p.seccion, p.demarcacion, p.lat, p.lng FROM persona p INNER JOIN municipio m ON p.ciudad = m.municipio WHERE p.ciudad = m.municipio AND m.id_municipio = "'.$usr_mun.'" AND p.demarcacion = "'.$usr_dem.'" AND zona = "'.$usr_zona.'" AND p.cve_elec != "0"';
        break;
    case "4":
        $sql = 'SELECT p.nombre, p.ap_pat, p.ap_mat, p.calle_num, p.colonia, p.afiliacion, p.ciudad, p.estado, p.seccion, p.demarcacion, p.lat, p.lng FROM persona p INNER JOIN municipio m ON p.ciudad = m.municipio WHERE p.ciudad = m.municipio AND m.id_municipio =  "'.$usr_mun.'" AND p.seccion IN ('.$incoming_user_dems.') AND p.cve_elec != "0"';
        break;
    case "7":
    case "8":
        $sql = 'SELECT p.nombre, p.ap_pat, p.ap_mat, p.calle_num, p.colonia, p.afiliacion, p.ciudad, p.estado, p.seccion, p.demarcacion, p.lat, p.lng FROM persona p WHERE p.id_usuario = "'.$usr_usuario.'" AND p.cve_elec != "0"';
        break;
}
//echo $sql;
$statement = $con->prepare($sql);
  $statement->execute();
  $personas = $statement->fetchAll();

//Agregar datos a mostrar en la ventana de información de cada marcador
foreach($personas as $persona){
    $domicilio = $persona['calle_num'].', '.$persona['colonia'].', '.$persona['ciudad'].', '.$persona['estado'];
    
    $nombre_completo = $persona['nombre'].' '.$persona['ap_pat'].' '.$persona['ap_mat'];
    
    $afiliado = $persona['afiliacion'] == "1" ? '<p class="infom_si">Afiliado</p>' : '<p class="infom_no">No afiliado</p>';
    /*if($result->num_rows > 0){
    
    while($row = $result->fetch_assoc()){ */?>
    
    ['<div class="info_content">' + '<h3><?php echo $nombre_completo; ?></h3>' + '<p><?php echo $domicilio; ?></p>' + '<?php echo $afiliado; ?>' + '</div>'], 
 
    <?php }



?>