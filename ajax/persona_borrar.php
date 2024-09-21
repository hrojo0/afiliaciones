<?php
$session_lifetime = 3600 * 24 * 4; // 4 days
session_set_cookie_params ($session_lifetime);
session_start();
require '../conexion_db.php';
require '../functions.php';
comprobar_login_ajax();

$incoming_id = $_POST['persona'];

if(!$con){
    $respuesta = 'Error';
} else{
        try{
            $statement = $con->prepare('
            DELETE FROM persona WHERE id_persona = :id_persona');
            $statement->execute(array(
                ':id_persona' => $incoming_id
            ));
            $resultado = $statement->fetchAll();
            $respuesta = "Borrado";
        } catch(Exception $e){
            $respuesta = "Error";
        }
}

echo $respuesta;

?>