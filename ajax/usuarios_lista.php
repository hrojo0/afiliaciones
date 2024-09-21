<?php
$session_lifetime = 3600 * 24 * 4; // 4 days
session_set_cookie_params ($session_lifetime);
session_start();
require '../conexion_db.php';
require '../functions.php';
comprobar_login_ajax();
/*$usr_usuario = $_SESSION['usuario'];
$usr_nombre = $_SESSION['nombre'];
$usr_apellidos = $_SESSION['apellidos'];
$usr_user = $_SESSION['user'];
$usr_foto = $_SESSION['foto'];
$usr_nivel = strval($_SESSION['id_nivel_user']);
$usr_mun = strval($_SESSION['municipio']);
$usr_dem = strval($_SESSION['demarcacion']);
$usr_zona = strval($_SESSION['zona']);*/
require "../includes/session.php";
//VARIABLES PARA PAGINADO
$pagina = $_POST['pagina'];
$limit = $_POST['limit'];
$start_from = ($pagina-1) * $limit; 


//DECLARACIÓN DE VARIABLES Y CASTEO DE POST
$usuarios = $id_nivel = $extra_html = $sql_extra = "";
$incoming_id = strval($_POST["id"]);

//obtener municipio de usuario
if($usr_nivel == 4){
    $sql_dems = 'SELECT demarcaciones FROM coordinacion_dems WHERE id_usuario = "'.$usr_usuario.'"';
    $statement = $con->prepare($sql_dems);
    $statement->execute();
    $dems_usr_resp = $statement->fetch();
    $dems_usr_resp = substr($dems_usr_resp['demarcaciones'],0,-1);
}

//nivel usuario para bd
//echo $incoming_id.$usr_nivel;
switch($incoming_id){
    case "admin":
        $id_nivel = "1";
        $extra_html = "";
        break;
    case "super_user":
        $id_nivel = "2";
        //$extra_html = "";
        break;
    case "supervisor":
        $id_nivel = "3";
        break; 
    
    case "resp_dem":
        $id_nivel = "5";
        switch($usr_nivel){
            case "3":
                $sql_extra = ' AND municipio = "'.$usr_mun.'" ';
                break;
            case "4":
                $sql_extra = ' AND seccion IN('.$dems_usr_resp.')';
                break;
        }
        break; 
    case "resp_zona":
        $id_nivel = "6";
        switch($usr_nivel){
            case "3":
                $sql_extra = ' AND municipio = "'.$usr_mun.'" ';
                break;
            case "4":
                $sql_extra = ' AND seccion IN('.$dems_usr_resp.') AND municipio = "'.$usr_mun.'" ';
                break;
            case "5":
            $sql_extra = ' AND dem = "'.$usr_dem.'" AND municipio = "'.$usr_mun.'" ';
                break;
        }
        break; 
        
    case "coord_dem":
        $id_nivel = "4";
        switch($usr_nivel){
            case "3":
                $sql_extra = ' AND municipio = "'.$usr_mun.'" ';
                break;
            case "5":
                $sql_extra = ' AND dem = "'.$usr_dem.'" AND municipio = "'.$usr_mun.'" ';
                break;
            case "6":
                $sql_extra = ' AND dem = "'.$usr_dem.'" AND municipio = "'.$usr_mun.'" AND zona = "'.$usr_zona.'" ';
                break;
        }
        break; 
    case "promo":
        $id_nivel = "7";
        switch($usr_nivel){
            case "3":
                $sql_extra = ' AND municipio = "'.$usr_mun.'" ';
                break;
            case "4":
                $sql_extra = ' And seccion IN('.$dems_usr_resp.') AND municipio = "'.$usr_mun.'" AND zona ="'.$usr_zona.'" ';
                break;
            case "5":
                $sql_extra = ' AND dem = "'.$usr_dem.'" AND municipio = "'.$usr_mun.'" ';
                break;
            case "6":
                $sql_extra = ' AND dem = "'.$usr_dem.'" AND municipio = "'.$usr_mun.'" AND zona = "'.$usr_zona.'" ';
                break;
        }
        break; 
}

//sql para obtener datos
$sql = 'SELECT * FROM usuario u INNER JOIN nivel_usuario n WHERE u.id_nivel_usuario = n.id_nivel_usuario AND u.id_nivel_usuario = '.$id_nivel.' '.$sql_extra.' LIMIT '.$start_from.', '.$limit.';';

$statement = $con->prepare($sql);
$statement->execute();
$usuarios_nivel_filtro = $statement->fetchAll();

$statement = $con->prepare('SELECT COUNT(*) FROM usuario u INNER JOIN nivel_usuario n WHERE u.id_nivel_usuario = n.id_nivel_usuario AND u.id_nivel_usuario = '.$id_nivel.' '.$sql_extra);
$statement->execute();
$total_usuarios = $statement->fetch();


foreach($usuarios_nivel_filtro as $user_level){
    $foto = $user_level['foto'] == "0" ? 'img/user.png' : $user_level['foto'];
    $nombre = $user_level['nombre'].' '.$user_level['apellidos'];
    
    //html extra
    switch($id_nivel){
        case "1":
            $extra_html = "";
            break;
        case "2":
            $extra_html = "";
            break;
        case "3":
            $statement = $con->prepare('SELECT municipio FROM municipio WHERE id_municipio = "'.$user_level['municipio'].'"');
            $statement->execute();
            $dems_resp_user = $statement->fetch();
            $extra_html = '<div class="datos_persona"><label for="info_mun">Municipio</label><p class="info" id="info_mun">'.$dems_resp_user['municipio'].'</p></div>';
            break; 
        case "4":
            
            $statement = $con->prepare('SELECT m.municipio, c.demarcaciones, u.dem, u.zona FROM municipio m INNER JOIN coordinacion_dems c INNER JOIN usuario u WHERE m.id_municipio = "'.$user_level['municipio'].'" AND c.id_usuario = "'.$user_level['id_usuario'].'" AND c.id_usuario = u.id_usuario');
            
            $statement->execute();
            $dems_resp_user = $statement->fetch();
            $extra_html = '<div class="datos_persona"><label for="info_mun">Municipio</label><p class="info" id="info_mun">'.$dems_resp_user['municipio'].'</p></div><div class="datos_persona" style="width:6rem"><label for="info_dems_resp">Demarcación</label><p class="info" id="info_dems_resp">'.$dems_resp_user['dem'].'</p></div><div class="datos_persona" style="width:3.2rem"><label for="info_dems_resp">Zona</label><p class="info" id="info_dems_resp">'.$dems_resp_user['zona'].'</p></div><div class="datos_persona"><label for="info_dems_resp">Secciones</label><p class="info" id="info_dems_resp">'.substr($dems_resp_user['demarcaciones'],0,-1).'</p></div>';
            break; 
        case "5":
            $statement = $con->prepare('SELECT municipio FROM municipio WHERE id_municipio = "'.$user_level['municipio'].'"');
            $statement->execute();
            $dems_resp_user = $statement->fetch();
            
            $extra_html = '<div class="datos_persona"><label for="info_mun">Municipio</label><p class="info" id="info_mun">'.$dems_resp_user['municipio'].'</p></div><div class="datos_persona" style="width:6rem"><label for="info_dem">Demarcación</label><p class="info" id="info_dem">'.$user_level['dem'].'</p></div><div class="datos_persona" ><label for="info_zona">Zonas</label><p class="info" id="info_zona">'.$user_level['zona'].'</p></div>';
            break; 
        case "6":
            $statement = $con->prepare('SELECT municipio FROM municipio WHERE id_municipio = "'.$user_level['municipio'].'"');
            $statement->execute();
            $dems_resp_user = $statement->fetch();
            
            $extra_html = '<div class="datos_persona"><label for="info_mun">Municipio</label><p class="info" id="info_mun">'.$dems_resp_user['municipio'].'</p></div><div class="datos_persona"><label for="info_dem">Demarcación</label><p class="info" id="info_dem">'.$user_level['dem'].'</p></div><div class="datos_persona" style="margin-right: 1rem"><label for="info_zona">Zona</label><p class="info" id="info_zona">'.$user_level['zona'].'</p></div>';
            break; 
        case "7":
            $statement = $con->prepare('SELECT municipio FROM municipio WHERE id_municipio = "'.$user_level['municipio'].'"');
            $statement->execute();
            $dems_resp_user = $statement->fetch();
            
            $extra_html = '<div class="datos_persona"><label for="info_mun">Municipio</label><p class="info" id="info_mun">'.$dems_resp_user['municipio'].'</p></div><div class="datos_persona"><label for="info_dem">Demarcación</label><p class="info" id="info_dem">'.$user_level['dem'].'</p></div><div class="datos_persona" style="margin-right: 1rem"><label for="info_zona">Zona</label><p class="info" id="info_zona">'.$user_level['zona'].'</p></div><div class="datos_persona"><label for="info_zona">Sección</label><p class="info" id="info_zona">'.$user_level['seccion'].'</p></div>';
            break; 
    }

    $usuarios = $usuarios.'
    <div id="'.$user_level['id_usuario'].'" class="user">
        <div class="flex_user">
            <div class="foto">
                <img src="'.$foto.'" alt="">
            </div>
            <div class="user_info">
                <div class="datos_persona">
                    <label for="info_nombre">Nombre</label>
                    <p class="info" id="info_nombre">'.$nombre.'</p>
                </div>

                <div class="datos_persona">
                    <label for="info_usuario">Usuario</label>
                    <p class="info" id="info_usuario">'.$user_level['user'].'</p>
                </div>

                <div class="datos_persona info_nivel">
                    <label for="info_nivel">Nivel de usuario</label>
                    <p class="info" id="info_nivel">'.$user_level['nivel_usuario'].'</p>
                </div>
                '.$extra_html.'
            </div>
        </div>
    </div>
    ';
}

echo $usuarios.'<p id="total_usuarios_filtro" style="display: none">'.$total_usuarios[0].'</p>'.'<p id="pagina_activa" style="display: none">'.$pagina.'</p>'.'<p id="usuario_activo" style="display: none">'.$incoming_id.'</p>';


?>