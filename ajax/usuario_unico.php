<?php
$session_lifetime = 3600 * 24 * 4; // 4 days
session_set_cookie_params ($session_lifetime);
session_start();
require '../conexion_db.php';
require '../functions.php';
comprobar_login_ajax();

$respuesta = [];

$incoming_id = $_POST['usuario'];

$statement = $con->prepare('SELECT id_nivel_usuario FROM usuario WHERE id_usuario = '.$incoming_id.';');
$statement->execute();
$usuario_nivel = $statement->fetch();
$usuario_nivel = $usuario_nivel['id_nivel_usuario'];

switch($usuario_nivel){
    case "1":
    case "2":
        $statement = $con->prepare('SELECT * FROM usuario WHERE id_usuario = '.$incoming_id.';');
        $statement->execute();
        $usuario = $statement->fetch();
        $respuesta = [
            'nombre' => $usuario['nombre'],
            'apellidos' => $usuario['apellidos'],
            'user' => $usuario['user'],
            'pass' => $usuario['pass'],
            'id_nivel_usuario' => $usuario['id_nivel_usuario'],
            'foto' => $usuario['foto'],
        ];
        break;
    case "3":
        $sql = 'SELECT *,m.municipio as mun FROM usuario u INNER JOIN municipio m WHERE u.id_usuario = '.$incoming_id.' AND u.municipio = m.id_municipio;';
        $statement = $con->prepare('SELECT *,m.municipio as mun FROM usuario u INNER JOIN municipio m WHERE u.id_usuario = '.$incoming_id.' AND u.municipio = m.id_municipio;');
        $statement->execute();
        $usuario = $statement->fetch();
        $respuesta = [
            'nombre' => $usuario['nombre'],
            'apellidos' => $usuario['apellidos'],
            'user' => $usuario['user'],
            'pass' => $usuario['pass'],
            'id_nivel_usuario' => $usuario['id_nivel_usuario'],
            'foto' => $usuario['foto'],
            'mun' => $usuario['mun'],
            'id_mun' => $usuario['id_municipio'],
        ];
        break;
    case "4":
        $statement = $con->prepare('SELECT *,m.municipio as mun, m.id_municipio as id_mun FROM usuario u INNER JOIN municipio m INNER JOIN coordinacion_dems c WHERE u.id_usuario = '.$incoming_id.' AND c.id_usuario = '.$incoming_id.' AND u.municipio = m.id_municipio;');
        $statement->execute();
        $usuario = $statement->fetch();
        $respuesta = [
            'nombre' => $usuario['nombre'],
            'apellidos' => $usuario['apellidos'],
            'user' => $usuario['user'],
            'pass' => $usuario['pass'],
            'id_nivel_usuario' => $usuario['id_nivel_usuario'],
            'foto' => $usuario['foto'],
            'mun' => $usuario['mun'],
            'dem' => $usuario['dem'],
            'zona' => $usuario['zona'],
            'dems_resp' => $usuario['demarcaciones'],
            'id_mun' => $usuario['id_mun'],
        ];
        break;
    case "5":
    case "6":
    case "7":
        $statement = $con->prepare('SELECT *,m.municipio as mun FROM usuario u INNER JOIN municipio m WHERE u.id_usuario = '.$incoming_id.' AND u.municipio = m.id_municipio;');
        $statement->execute();
        $usuario = $statement->fetch();
        $respuesta = [
            'nombre' => $usuario['nombre'],
            'apellidos' => $usuario['apellidos'],
            'user' => $usuario['user'],
            'pass' => $usuario['pass'],
            'id_nivel_usuario' => $usuario['id_nivel_usuario'],
            'foto' => $usuario['foto'],
            'mun' => $usuario['mun'],
            'dem' => $usuario['dem'],
            'zona' => $usuario['zona'],
            'seccion' => $usuario['seccion'],
            'id_mun' => $usuario['id_municipio'],
        ];
        break;
}


echo json_encode($respuesta);

?>