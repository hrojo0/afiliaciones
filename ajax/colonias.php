<?php
$session_lifetime = 3600 * 24 * 4; // 4 days
session_set_cookie_params ($session_lifetime);
session_start();
require '../conexion_db.php';
require '../functions.php';
//comprobar_nivel_ajax();
comprobar_login_ajax();

$colonias_cp_options = "";
$incoming_cp = $_POST["codigo_postal"];

$statement = $con->prepare('SELECT id_cp_colonia, cp, colonia FROM cp_colonia ORDER BY colonia;');
$statement->execute();
$colonias_cp = $statement->fetchAll();

foreach($colonias_cp as $colonia_cp){
    if($incoming_cp == $colonia_cp['cp']){
        $colonias_cp_options = $colonias_cp_options.'<option value="'.$colonia_cp['id_cp_colonia'].'">'.$colonia_cp['colonia'].'</option>';
    }
}

echo $colonias_cp_options;
?>