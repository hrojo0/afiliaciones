<?php
$session_lifetime = 3600 * 24 * 4; // 4 days
session_set_cookie_params ($session_lifetime);
session_start();
require '../conexion_db.php';
require '../functions.php';
comprobar_login_ajax();

$incoming_id = $_POST['usuario'];

if(!$con){
    $respuesta = 'Error';
} else{
        try{
            //CODIGO PARA BORRAR NIVEL USUARIO 4
            
            $statement = $con->prepare('SELECT id_nivel_usuario FROM usuario WHERE id_usuario = :id_usuario');
            $statement->execute(array(
                ':id_usuario' => $incoming_id
            ));
            $resultado = $statement->fetch();
            
            
            if($resultado['id_nivel_usuario'] == 4){
                echo 'user resp dems';
                
                
                $statement = $con->prepare('
                DELETE FROM coordinacion_dems WHERE id_usuario = :id_usuario');
                $statement->execute(array(
                    ':id_usuario' => $incoming_id
                ));
                
                
                $statement = $con->prepare('
                DELETE FROM usuario WHERE id_usuario = :id_usuario');
                $statement->execute(array(
                    ':id_usuario' => $incoming_id
                ));
                $resultado = $statement->fetchAll();
                $respuesta = "Borrado";  
                
            } else {
                echo 'otro usuario';
                $statement = $con->prepare('
                DELETE FROM usuario WHERE id_usuario = :id_usuario');
                $statement->execute(array(
                    ':id_usuario' => $incoming_id
                ));
                $resultado = $statement->fetchAll();
                $respuesta = "Borrado";
                
            }
            
            
            
            
            
            
            
            
            
        } catch(Exception $e){
            $respuesta = "Error";
        }
}

echo $respuesta;

?>