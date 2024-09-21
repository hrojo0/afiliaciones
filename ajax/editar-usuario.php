<?php
$session_lifetime = 3600 * 24 * 4; // 4 days
session_set_cookie_params ($session_lifetime);
session_start();
require '../conexion_db.php';
require '../functions.php';
//comprobar_nivel_ajax();
comprobar_login_ajax();
//VARIABLES UPDATE USUARIO
$nombre = limpiar_inputs($_POST['nombre']);
$apellidos = limpiar_inputs($_POST['apellidos']);
$user = limpiar_inputs($_POST['user']);


$id_nivel_usuario = $_POST['nivel_usuario'];
$id_usuario = $foto = $old_pass = "";


//variables control -> id inicial 3
$e_nom = $e_aps = $e_user = $e_old_pass = $e_new_pass = $e_new_pass_check = $e_dems_resp = $e_zonas = 3;


//checar que no esten vacios los input -> id error 1
$e_nom = $nombre == "" ? 1 : 0;
$e_aps = $apellidos == "" ? 1 : 0;
$e_user = ord($user) == 0 ? 1 : 0;



//checar input solo texto -> id error 4
if($e_nom == 0){$e_nom = !val_car($nombre) ? 4 : 0;}
if($e_aps == 0){$e_aps = !val_car($apellidos) ? 4 : 0;}


//checa si el post que recibe es de un usuario existente o un usuario nuevo
//Usuario nuevo
if(!isset($_POST['usuario'])){  
    $e_new_pass = ord($_POST['new_pass_add']) == 0 ? 1 : 0;
    //error zona dependiendo el usuario por agregar
    if($id_nivel_usuario == "5"){
        $e_zonas = ord($_POST['zonas_add']) == 0 ? 1 : 0;
    } elseif($id_nivel_usuario == "4" || $id_nivel_usuario == "6" || $id_nivel_usuario == "7") {
        $e_zonas = $_POST['zona_add'] == 0 ? 10 : 0;
    }
    if($id_nivel_usuario == "3" || $id_nivel_usuario == "2" || $id_nivel_usuario == "1"){
        $e_zonas = 0;
    }
    
    //contraseña
    $new_pass = limpiar_inputs($_POST['new_pass_add']);
    $new_pass_check = limpiar_inputs($_POST['pass_check_add']);
    if($e_new_pass == 0){
        if(strlen($new_pass) < 8){
           $e_new_pass = 2 ;
        } else {
            $new_pass = encript_pass($new_pass);
            $new_pass_check = encript_pass($new_pass_check);
            //$new_pass = encript_pass(limpiar_inputs($_POST['new_pass_add']));
            //$new_pass_check = encript_pass(limpiar_inputs($_POST['pass_check_add']));        
        }
        
    } else{
        $new_pass = $new_pass_check = "";
    }
    
    //checar longitud mínima nueva contraseña -> id error 2
    //$e_new_pass = (strlen($new_pass) < 8) ? 2 : 0;
    
    switch($id_nivel_usuario){
        case "1":
        case "2":
            $municipio_new = $dem_new = $zona_new = $seccion_new = $e_dems_resp = 0;
            break;
            
        case "3":
            $municipio_new = isset($_POST['municipio_add']) ? $_POST['municipio_add'] : 0;
            $dem_new = $zona_new = $seccion_new = $e_dems_resp = 0;
            break;
            
        case "4":
            $municipio_new = isset($_POST['municipio_add']) ? $_POST['municipio_add'] : 0;
            $dem_new = $zona_new = $seccion_new = 0;
            $e_dems_resp = ord($_POST['demarcaciones_responsable_add']) == 0 ? 9 : 0; 
            break;
            
        case "5":
            $municipio_new = isset($_POST['municipio_add']) ? $_POST['municipio_add'] : 0;
            $dem_new = isset($_POST['demarcacion_add']) ? $_POST['demarcacion_add'] : 0;
            $e_dems_resp = $seccion_new = 0;
            $zona_new = isset($_POST['zonas_add']) ? $_POST['zonas_add'] : 0;
            break;
            
        case "6":
            $municipio_new = isset($_POST['municipio_add']) ? $_POST['municipio_add'] : 0;
            $dem_new = isset($_POST['demarcacion_add']) ? $_POST['demarcacion_add'] : 0;
            $zona_new = isset($_POST['zona_add']) ? $_POST['zona_add'] : 0;
            $e_dems_resp = 0;
            break;
        case "7":
            $municipio_new = isset($_POST['municipio_add']) ? $_POST['municipio_add'] : 0;
            $dem_new = isset($_POST['demarcacion_add']) ? $_POST['demarcacion_add'] : 0;
            $zona_new = isset($_POST['zona_add']) ? $_POST['zona_add'] : 0;
            $seccion_new = isset($_POST['seccion_add']) ? $_POST['seccion_add'] : 0;
            $e_dems_resp = 0;
            break;
    }
    
    
    $foto = $_FILES['file'];
    $archivo_destino = $carpeta_destino = "";
    
    //$municipio_new = isset($_POST['municipio']) ? $_POST['municipio'] : 0;
   // $dem_new = isset($_POST['demarcacion']) ? $_POST['demarcacion'] : 0;
   // $zona_new = isset($_POST['zona']) ? $_POST['zona'] : 0;
    
    //si existe foto la sube al servidor, no existe foto carga imagen default
    if($foto['name'] != ""){
        $carpeta_destino = 'img/users/';
        $archivo_destino = $carpeta_destino.$foto['name'];
        $carpeta_destino = '../'.$carpeta_destino;
        //move_uploaded_file($foto['tmp_name'], $carpeta_destino.$foto['name']);
        
        
    } else {
        $archivo_destino = "0";
    }
    
    //checa si el usuario existe o si es menor que 6 caracteres
    $e_user = check_existing_user($user, $con) == 1 ? 8 : ((strlen($user) < 6) ? 7 : 0);
    
    if($e_new_pass == 0){
        //checar coincidencia de nuevas contraseñas -> id error 6
        $e_new_pass_check = $new_pass != $new_pass_check ? 6 : 0;

    }

    if($e_nom == 0 && $e_aps == 0 && $e_user == 0 && $e_new_pass == 0 && $e_new_pass_check == 0 && $e_dems_resp == 0 && $e_zonas == 0){
        //NO HAY ERRORES EN FORMULARIO
        $sql = 'INSERT INTO usuario (id_usuario, nombre, apellidos, user, pass, foto, id_nivel_usuario, municipio, dem, zona, seccion) VALUES (NULL, "'.$nombre.'", "'.$apellidos.'", "'.$user.'", "'.$new_pass.'", "'.$archivo_destino.'", '.$id_nivel_usuario.', '.$municipio_new.', '.$dem_new.', '.$zona_new.', '.$seccion_new.')';   
        
        $statement = $con->prepare($sql);
        $statement->execute();
        $resultado = $statement->fetchAll();
        if($archivo_destino != "0"){
            move_uploaded_file($foto['tmp_name'], $carpeta_destino.$foto['name']);
        }
        
        //agregar sql para coordinacion_dems, usuario a agregar id_nivel
        if($id_nivel_usuario == 4){
            
            $statement = $con->prepare("SELECT * FROM usuario ORDER BY id_usuario DESC LIMIT 1");
            $statement->execute();
            $resultado_last_user = $statement->fetch();
            
            $dems_resp = $_POST['demarcaciones_responsable_add'];
            $sql_coord_dems = 'INSERT INTO coordinacion_dems (id_coordinacion_dems, demarcaciones, id_usuario, id_municipio) VALUES (NULL, "'.$dems_resp.'", '.$resultado_last_user['id_usuario'].', '.$municipio_new.')'; 
        
            $statement = $con->prepare($sql_coord_dems);
            $statement->execute();
        }
        
        check_update_user($resultado);
        
    } else {
        $respuesta = [
            'e_nom' => $e_nom,
            'e_aps' => $e_aps,
            'e_user' => $e_user,
            'e_new_pass' => $e_new_pass,
            'e_new_pass_check' => $e_new_pass_check,
            'e_dems_resp' => $e_dems_resp,
            'e_zonas' => $e_zonas,
        ];
        
        echo json_encode($respuesta);

    }
    
    
}

/**********************************************************************/
/**********************************************************************/
/************************* FIN NUEVO USUARIO **************************/
/**********************************************************************/
/**********************************************************************/

//usuario existente
//CODIGO PARA EDITAR USUARIO


else{
    
    
    
    
    $flag_pass = limpiar_inputs($_POST['flag_pass']);
    
    $id_usuario = $_POST['usuario'];
    $sql = 'SELECT pass, foto FROM usuario WHERE id_usuario = '.$id_usuario;
    $statement = $con->prepare($sql);
    $statement->execute();
    $foto_sql = $statement->fetch();
    
    if($flag_pass == "1"){   
        
        $e_new_pass = ord($_POST['new_pass']) == 0 ? 1 : 0;
        //contraseña
        $new_pass = limpiar_inputs($_POST['new_pass']);
        $new_pass_check = limpiar_inputs($_POST['pass_check']); 

        //checar contrasña vacia -> id error 1
        $e_new_pass = $e_new_pass == "" ? 1 : 0;
        //cehcar contraseña actual -> id error 5
        $old_pass = encript_pass(limpiar_inputs($_POST['old_pass']));
        $e_old_pass = $old_pass != $foto_sql['pass'] ? 5 : 0;
        //checar nuevas contraseñas min_length y match
        if($e_old_pass == 0){
            //checar longitud mínima nueva contraseña -> id error 2
            $e_new_pass = (strlen($new_pass) < 8) ? 2 : 0;
            if($e_new_pass == 0){
                //checar coincidencia de nuevas contraseñas -> id error 6
                $e_new_pass_check = $new_pass != $new_pass_check ? 6 : 0;

            }
        }
        $new_pass = encript_pass($new_pass);
    } else {
        $new_pass = $foto_sql['pass'];
    }
    
    if(isset($_FILES['file']) && $_FILES['file']['name'] != ''){
        $foto = $_FILES['file']['name'];
        $archivo_destino = $carpeta_destino = "";
        $carpeta_destino = 'img/users/';
        $archivo_destino = $carpeta_destino.$foto;
        $carpeta_destino = '../'.$carpeta_destino;
    }
    else {
        
        if(!isset($_POST['foto'])){
            if($foto_sql['foto'] == "0" || $foto_sql['foto'] == 'img/user.png' || (isset($_FILES['file']) && $_FILES['file']['name'] == '')){
                $foto = "0";
            } else {
                $foto = $foto_sql['foto'];     
            }
        } else {
            
                $foto = $_POST['foto'];
                $foto = $foto == "" ? "0" : $foto;
        }
    }
    
    
    if(!(isset($_POST['flag_user_mod']))){
        //CHECAR SI USUARIO EXISTE
        $sql = 'SELECT id_usuario, user, COUNT(*) AS cantidad FROM usuario WHERE user = "'.$user.'";';
        
        $statement = $con->prepare($sql);
        $statement->execute();
        $user_found = $statement->fetch();


        if($user_found['id_usuario'] == $id_usuario && $user_found['user'] == $user){
            $e_user = 0;

        } else{
            //Checar si nuevo usuario existe al editar uno existente con function custom que retorna variable $found, 0 = no existe usuario, 1 = usuario existe en bd -> id error 8
            $e_user = check_existing_user($user, $con) == 1 ? 8 : ((strlen($user) < 6) ? 7 : 0);

        }
    }
        
        
    //Si no hay cambio de contraseña no aplican los errores de contraseña
    if($flag_pass == "0"){
        $e_old_pass = $e_new_pass = $e_new_pass_check = 0;
    }
    //if($flag_pass == "1"){$new_pass = encript_pass($new_pass);}
    

    //CARGA SQL PARA ACTUALIZAR DATOS DEL USUARIO
    $sql = 'UPDATE usuario SET nombre = "'.$nombre.'", apellidos = "'.$apellidos.'", user = "'.$user.'", pass = "'.$new_pass.'", foto = :foto, id_nivel_usuario = "'.$id_nivel_usuario.'" WHERE id_usuario = "'.$id_usuario.'"';
    
    switch($id_nivel_usuario){
        case "1":
        case "2":
            $e_dems_resp = 0;
            break;
        case "3":
            if(!(isset($_POST['flag_user_mod']))){
                $mun = $_POST['municipio_change'];
            
                $sql = 'UPDATE usuario SET nombre = "'.$nombre.'", apellidos = "'.$apellidos.'", user = "'.$user.'", pass = "'.$new_pass.'", foto = :foto, id_nivel_usuario = "'.$id_nivel_usuario.'", municipio = "'.$mun.'", dem = "0", zona = "0", seccion = "0" WHERE id_usuario = "'.$id_usuario.'"';    
            }
            
            $e_dems_resp = 0;
            
            break;
        case "4":
            if(!(isset($_POST['flag_user_mod']))){
                $mun = $_POST['municipio_change'];
                $dem = $_POST['demarcacion_change'];
                $zona = $_POST['zona_change'];
                
                $dems_responsable = $_POST['demarcaciones_responsable_change'];

                $sql = 'UPDATE usuario SET nombre = "'.$nombre.'", apellidos = "'.$apellidos.'", user = "'.$user.'", pass = "'.$new_pass.'", foto = :foto, id_nivel_usuario = "'.$id_nivel_usuario.'", municipio = "'.$mun.'", dem = "'.$dem.'", zona = "'.$zona.'", seccion = "0" WHERE id_usuario = "'.$id_usuario.'"';

                $e_dems_resp = ord($_POST['demarcaciones_responsable_change']) == 0 ? 9 : 0; 
            } else{ 
                $e_dems_resp = 0;
            }
                
            break;
        case "5":
            if(!(isset($_POST['flag_user_mod']))){
                $mun = $_POST['municipio_change'];
                $dem = $_POST['demarcacion_change'];
                $zonas = $_POST['zonas_change'];

                $sql = 'UPDATE usuario SET nombre = "'.$nombre.'", apellidos = "'.$apellidos.'", user = "'.$user.'", pass = "'.$new_pass.'", foto = :foto, id_nivel_usuario = "'.$id_nivel_usuario.'", municipio = "'.$mun.'", dem = "'.$dem.'", zona = "'.$zonas.'", seccion = "0" WHERE id_usuario = "'.$id_usuario.'"';
            }
            $e_dems_resp = 0;
            break;
        case "6":
            if(!(isset($_POST['flag_user_mod']))){
                $mun = $_POST['municipio_change'];
                $dem = $_POST['demarcacion_change'];
                $zona = $_POST['zona_change'];

                $sql = 'UPDATE usuario SET nombre = "'.$nombre.'", apellidos = "'.$apellidos.'", user = "'.$user.'", pass = "'.$new_pass.'", foto = :foto, id_nivel_usuario = "'.$id_nivel_usuario.'", municipio = "'.$mun.'", dem = "'.$dem.'", zona = "'.$zona.'" WHERE id_usuario = "'.$id_usuario.'"';
            }
            $e_dems_resp = 0;
            break;
        case "7":
            if(!(isset($_POST['flag_user_mod']))){
                $mun = $_POST['municipio_change'];
                $dem = $_POST['demarcacion_change'];
                $zona = $_POST['zona_change'];
                //$seccion = $_POST['seccion_change'];
                $seccion = isset($_POST['seccion_change']) ? $_POST['seccion_change'] : 0;

                $sql = 'UPDATE usuario SET nombre = "'.$nombre.'", apellidos = "'.$apellidos.'", user = "'.$user.'", pass = "'.$new_pass.'", foto = :foto, id_nivel_usuario = "'.$id_nivel_usuario.'", municipio = "'.$mun.'", dem = "'.$dem.'", zona = "'.$zona.'", seccion = "'.$seccion.'" WHERE id_usuario = "'.$id_usuario.'"';
            }
            $e_dems_resp = 0;
            break;
    }
    
    
    if($e_nom == 0 && $e_aps == 0 && $e_user == 0 && $e_old_pass == 0 && $e_new_pass == 0 &&  $e_new_pass_check == 0 && $e_dems_resp == 0){
        //if($flag_pass == "1"){$new_pass = encript_pass($new_pass);}      
        //NO HAY ERRORES EN FORMULARIO
        //echo $sql;
     
        if(isset($_FILES['file']) && $_FILES['file']['name'] != ''){
            $foto = $_FILES['file']['name'];
            
            $archivo_destino = $carpeta_destino = "";
            
            $carpeta_destino = 'img/users/';
            
            $archivo_destino = $carpeta_destino.$foto;
            
            $carpeta_destino = '../'.$carpeta_destino;
            
            move_uploaded_file($_FILES['file']['tmp_name'], $carpeta_destino.$foto);
            
            $foto = $archivo_destino;
            
        }else{
            $statement = $con->prepare('SELECT foto FROM usuario WHERE id_usuario = '.$id_usuario.';');
            $statement->execute();
            $foto = $statement->fetchAll();
            $foto = $foto[0]['foto'];
        } 
        
        $statement = $con->prepare($sql);
        $statement->execute(array(':foto' => $foto));
        
        if( $id_nivel_usuario == "4" && !(isset($_POST['flag_user_mod'])) ){
            $sql_dems = 'UPDATE coordinacion_dems SET demarcaciones = "'.$dems_responsable.'", id_municipio = "'.$mun.'" WHERE id_usuario = "'.$id_usuario.'"';
            $stmnt = $con->prepare($sql_dems);
            $stmnt -> execute();
        }        
        
        $resultado = $statement->fetchAll();
        
        check_update_user($resultado);
        
    } else {
        $respuesta = [
            'e_nom' => $e_nom,
            'e_aps' => $e_aps,
            'e_user' => $e_user,
            'e_old_pass' => $e_old_pass,
            'e_new_pass' => $e_new_pass,
            'e_new_pass_check' => $e_new_pass_check,
            'e_dems_resp' => $e_dems_resp,
            'e_zonas' => $e_zonas,
        ];
        echo json_encode($respuesta);

    }
}




?>