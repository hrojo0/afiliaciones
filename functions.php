<?php
function comprobar_login(){
    if(!isset($_SESSION['user'])){
        header('Location: login.php');
    }
}
function comprobar_login_ajax(){
    if(!isset($_SESSION['user'])){
        header('Location: ../login.php');
    }
}
function comprobar_nivel_ajax(){
    if(!isset($_SESSION['id_nivel_user'])){
        header('Location: ../login.php');
    }
}

function comprobar_nivel(){
    if(isset($_SESSION['id_nivel_user'])){
        if($_SESSION['id_nivel_user'] == 7){
           header('Location: index.php'); 
        }
    }
}



function limpiar_inputs($texto){
    $texto = filter_var($texto, FILTER_SANITIZE_STRING);
    $texto = htmlspecialchars($texto);
    $texto = trim($texto);
    $texto = stripslashes($texto);
    return $texto;
}

function check_save_register($page){
    if($resultado !== false){
        header('Location: '.$page.'.php');
    } else {
        echo "Error";
    }
}

function check_update_user($resultado){
    if($resultado !== false){
        //header('Location: '.$page.'.php');
        echo "200";
    } else {
        echo "Error";
    }
}

function val_car($i){
    $i_val = preg_replace("/[^a-zA-Z\sñÑáéíóúÁÉÍÓÚ]/", "", $i);
    if($i !== $i_val){
        return false;
    }else{return true;}

}

function check_existing_user($user, $con){
        $sql = 'SELECT user FROM usuario';
        $statement = $con->prepare($sql);
        $statement->execute();
        $users = $statement->fetchAll();
        
        $found = 0;
        foreach($users as $usuario_bd){
            
            if($usuario_bd['user'] == $user){
                $found = 1;
                break;
            }
            
        }
    return $found;
}

function encript_pass($pass){
    $pass = hash('sha512', $pass);
    $pass = hash('sha512', $pass);
    $pass = hash('sha512', $pass);
    $pass = hash('sha512', $pass);
    $pass = hash('sha512', $pass);
    $pass = hash('sha512', $pass);
    $pass = hash('sha512', $pass);
    $pass = hash('sha512', $pass);
    $pass = hash('sha512', $pass);
    $pass = hash('sha512', $pass);
    
    return $pass;
}


?>