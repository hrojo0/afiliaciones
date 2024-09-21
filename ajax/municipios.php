<?php
$session_lifetime = 3600 * 24 * 4; // 4 days
session_set_cookie_params ($session_lifetime);
session_start();
require '../conexion_db.php';
require '../functions.php';
comprobar_login_ajax();
comprobar_nivel_ajax();


$municipios_cp_options = "";
$incoming_cp = $_POST["codigo_postal"];
$municipio = "";

$statement = $con->prepare('SELECT id_cp_colonia, cp, municipio FROM cp_colonia ORDER BY municipio;');
$statement->execute();
$municipios_cp = $statement->fetchAll();

foreach($municipios_cp as $municipio_cp){
    if($incoming_cp == $municipio_cp['cp']){
        if($municipio == "" || $municipio != $municipio_cp['municipio']){
            $municipio = $municipio_cp['municipio'];
            $municipios_cp_options = $municipios_cp_options.'<option value="'.$municipio.'">'.$municipio.'</option>';
        } 
        
            
        
    }
}

echo $municipios_cp_options;
?>