<?php
$session_lifetime = 3600 * 24 * 4; // 4 days
session_set_cookie_params ($session_lifetime);
session_start();
require '../conexion_db.php';
require '../functions.php';
comprobar_nivel_ajax();
comprobar_login_ajax();

$colonias_options = '<option value="0">- Colonia -</option>';
$incoming_mun = $_POST["municipio"];
//$incoming_mun = "Acaponeta";
if($incoming_mun != "- Municipio -"){
    $statement = $con->prepare('SELECT id_cp_colonia, colonia FROM cp_colonia WHERE municipio = :municipio ORDER BY colonia ;');
    $statement->execute(array(
        ':municipio' => $incoming_mun
    ));
    $colonias = $statement->fetchAll();

    foreach($colonias as $colonia){
        $colonias_options = $colonias_options.'<option value="'.$colonia['id_cp_colonia'].'">'.$colonia['colonia'].'</option>';
    }

    echo $colonias_options;
} else {/*
    $statement = $con->prepare('SELECT id_cp_colonia, colonia FROM cp_colonia ORDER BY colonia ;');
    $statement->execute();
    $colonias = $statement->fetchAll();

    foreach($colonias as $colonia){
        $colonias_options = $colonias_options.'<option value="'.$colonia['id_cp_colonia'].'">'.$colonia['colonia'].'</option>';
    }*/

    echo '<option value="0">- Colonia -</option>';
}
?>