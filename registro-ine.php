<?php
//error_reporting(0);
$session_lifetime = 3600 * 24 * 4; // 4 days
session_set_cookie_params ($session_lifetime);
session_start();
require 'conexion_db.php';
require 'functions.php';
comprobar_login();

require "includes/session.php";

$usuario_registrado='no';

if($_SESSION['usuario_registrado'] == 'no'){
    $usuario_registrado = 'no';
} else {
    $usuario_registrado = 'registrado';
}
//LISTA DE PROMOTORES
switch($usr_nivel){
    case "1":   
    case "2":
        $sql_promotores = 'SELECT id_usuario, nombre, apellidos, id_nivel_usuario FROM usuario WHERE id_nivel_usuario = "7" ORDER BY nombre ASC';
        break;
    case "3":
    
        $sql_promotores = 'SELECT id_usuario, nombre, apellidos, id_nivel_usuario FROM usuario WHERE municipio = "'.$usr_mun.'" AND id_nivel_usuario = "7" ORDER BY nombre ASC';
        break;
    
    case "5":
        $sql_promotores = 'SELECT id_usuario, nombre, apellidos, id_nivel_usuario FROM usuario WHERE municipio = "'.$usr_mun.'" AND dem = "'.$usr_dem.'" AND id_nivel_usuario = "7" ORDER BY nombre ASC';
        break;
    case "6":
        $sql_promotores = 'SELECT id_usuario, nombre, apellidos, id_nivel_usuario FROM usuario WHERE municipio = "'.$usr_mun.'" AND dem = "'.$usr_dem.'" AND zona = "'.$usr_zona.'" AND id_nivel_usuario = "7" ORDER BY nombre ASC';
        break;
    case "4":       
        $statement = $con ->prepare('SELECT demarcaciones FROM coordinacion_dems WHERE id_usuario = "'.$usr_usuario.'"');
        $statement->execute();
        $user_dems = $statement->fetch();
        $user_dems = $user_dems[0];
        $user_dems = substr($user_dems, 0 , -1);
        $sql_promotores = 'SELECT id_usuario, nombre, apellidos, id_nivel_usuario FROM usuario WHERE municipio = "'.$usr_mun.'" AND id_nivel_usuario = "7" AND seccion IN ('.$user_dems.')  ORDER BY nombre ASC';
        break;
    case "7":
    case "8":
        $sql_promotores = 'SELECT id_usuario, nombre, apellidos, id_nivel_usuario FROM usuario WHERE user = "'.$usr_user.'"';
        break;
}
$statement = $con->prepare($sql_promotores);
$statement->execute();
$promotores = $statement->fetchAll();


//Carga de demarcaciones primera vez
switch($usr_nivel){
    case "4":
        $statement = $con->prepare('SELECT m.id_municipio, c.demarcaciones FROM municipio m INNER JOIN coordinacion_dems c WHERE m.id_municipio = c.id_municipio AND c.id_usuario ="'.$usr_usuario.'";');
        $statement->execute();
        $coord_dems = $statement->fetch();

        $temp_coord_dems = explode(",",$coord_dems['demarcaciones']);
        array_pop($temp_coord_dems);
        break;
}

//variables control errores -> id inicial 3
$e_Cve = $e_Nom = $e_Apat = $e_Amat = $e_Nac = $e_Curp = $e_Fb = $e_Cel = $e_Dom = $e_Secc = $e_Dem = $e_Ubicacion = $e_Nom_asp = $e_Apat_asp = $e_Amat_asp = $e_Nac_asp = $e_Cel_asp = $e_Zona = $e_Wp_asp = $e_Wp = $e_Cve_doble = $e_CP = $e_NombreCompleto = 3;

//Variable control vacios POST
$cve_exists = $nom_exists = $ap_exists = $am_exists = $curp_exists = $fb_exists = $cel_exists = $dom_exists = $form_id_exists = $nom_exists_asp = $ap_exists_asp = $am_exists_asp = $cel_exists_asp = $CP_exists = false;

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    
    $_SESSION['usuario_registrado'] = 'no';
    $form_id_exists = true;
    $form_id = $_POST['form_id'];
    if($form_id == 1){
        $_SESSION['usuario_registrado'] = 'no';
        $wp_exists = false;
       //definicion de variables
        
        $cve_elec = isset($_POST['cve_elec']) ? strtoupper(limpiar_inputs($_POST['cve_elec'])) : "0";
        
        $nombre = ucwords(limpiar_inputs($_POST['nombre']));
        $ap_pat = ucwords(limpiar_inputs($_POST['ap_pat']));
        $ap_mat = ucwords(limpiar_inputs($_POST['ap_mat']));
        $f_nac = limpiar_inputs($_POST['f_nac']);
        $f_nac = date($f_nac);
        $sexo = limpiar_inputs($_POST['sexo']);
        //$curp = strtoupper((limpiar_inputs($_POST['curp'])));
        $curp = isset($_POST['curp']) ? strtoupper((limpiar_inputs($_POST['curp']))) : "0";
        
        //$fb = limpiar_inputs($_POST['fb']);
        $fb = isset($_POST['fb']) ? limpiar_inputs($_POST['fb']) : "0";
        $celular = limpiar_inputs($_POST['celular']);
        
        
        $whatsapp = isset($_POST['whatsapp']);
        $whatsapp = $whatsapp == NULL ? "error_wp_empty" : $_POST['whatsapp'];
        //$afiliacion = limpiar_inputs($_POST['afiliacion']);
        $afiliacion = isset($_POST['afiliacion']) ? limpiar_inputs($_POST['afiliacion']) : "1";
        
        $calle_num = limpiar_inputs($_POST['calle_num']);
        $num_int = limpiar_inputs($_POST['num_int']);
        $colonia = limpiar_inputs($_POST['colonia']);
        if(isset($_POST['cols_cp'])){
            $cols_cp = limpiar_inputs($_POST['cols_cp']);
        }
        $cp = limpiar_inputs($_POST['cp']);
        $localidad = limpiar_inputs($_POST['localidad']);
        $ciudad = limpiar_inputs($_POST['ciudad']);
        $estado = limpiar_inputs($_POST['estado']);
        $pais = limpiar_inputs($_POST['pais']);
        $demarc = limpiar_inputs($_POST['demarc']);
        $dems = limpiar_inputs($_POST['dems']);
        $seccion = limpiar_inputs($_POST['secc']);
        $seccs = limpiar_inputs($_POST['seccs']);
        $zona = limpiar_inputs($_POST['zona']);
        $zonas = limpiar_inputs($_POST['zonas']);
        $proms = ($_POST['promotores']);
        $lat = limpiar_inputs($_POST['lat']);
        $lng = limpiar_inputs($_POST['lng']);
        $id_usuario = $_POST['promotor'];

        $dems = explode(",",$dems);
        $seccs = explode(",",$seccs);
        $zonas = explode(",",$zonas);
        array_pop($dems);
        array_pop($seccs);
        array_pop($zonas);

        //checar que no esten vacios los input -> identificador error 1
        $e_Cve = ord($cve_elec) == 0 ? 1 : 0;
        $e_Nom = $nombre === '' ? 1 : 0;
        $e_Apat = $ap_pat == '' ? 1 : 0;
        $e_Amat = $ap_mat == '' ? 1 : 0;
        $e_Nac = $f_nac == '' ? 1 : 0;
        $e_Curp = ord($curp) == 0 ? 1 : 0;
        //$e_Fb = ord($fb) == 0 ? 1 : 0;
        $e_Fb = 0;
        $e_Cel = ord($celular) == 0 ? 1 : 0;
        $e_Dom = ord($calle_num) == 0 ? 1 : 0;

        //if(ord($calle_num) == 0){$e_Dom = 1;} else {$e_Dom = 0;}

        //checa datos mínimos de los input correspondientes -> identificador error 2
        //$e_Cve = (strlen($cve_elec) !== 18) ? 2 : 0;
        $cve_elec_pattern = '/^[A-Z]{6}[0-9]{8}[HM][0-9]{3}$/';
        $cve_elec_correcta = preg_match($cve_elec_pattern, $cve_elec) ? 1 : 0;
        $e_Cve = ($cve_elec_correcta == 0) ? 2 : 0; 
        
        //$e_Curp = (strlen($curp) !== 18) ? 2 : 0;
        $curp_pattern = '/^[A-Z]{4}[0-9]{6}[A-Z]{6}[A-Z0-9]{2}$/';
        $curp_correcto = preg_match($curp_pattern, $curp) ? 1 : 0;
        $e_Curp = ($curp_correcto == 0) ? 2 : 0;
        
        //$fb_correcto = (strlen($fb) >= 10 && strlen($fb) <= 13) ? 1 : 0;
        /*$fb_pattern = '/^[A-Z]{4}[0-9]{6}$/';
        $fb_pattern_h = '/^[A-Z]{4}[0-9]{6}[A-Z0-9]{3}$/';
        $fb_correcto = preg_match($fb_pattern, $fb) || preg_match($fb_pattern_h, $fb) ? 1 : 0;
        $e_Fb = ($fb_correcto === 0) ? 2 : 0;*/
        
        $e_Cel = (strlen($celular) !== 10) ? 2 : 0;
        $e_Dom = (ord($calle_num) == 0 || ord($localidad) == 0) ? 2 : 0;

        //Checar inputs que solo contengan texto error 4
        if($e_Nom == 0){$e_Nom = (ord($nombre) !== 0 && !val_car($nombre)) ? 4 : 0;}
        if($e_Apat == 0){$e_Apat = !val_car($ap_pat) ? 4 : 0;}
        if($e_Apat == 0){$e_Amat = !val_car($ap_mat) ? 4 : 0;}

        //Checar combos sin valor, value 0 -> error 5
        $e_Secc = $seccion == '0' ? 5 : 0;
        $e_Dem = $demarc == '0' ? 5 : 0;
        $e_Zona = $zona == '0' ? 5 : 0;
        

        //Checar si la persona pertenece al municipio asignado -> identificador error 6
        switch($usr_nivel){
            case "1":
            case "2":
                //acepta todo
                break;
            case "3":
            case "4":
            case "5":
            case "6":
            case "7":
                if($ciudad != ""){
                $statement = $con->prepare('SELECT id_municipio FROM municipio WHERE municipio = "'.$ciudad.'"');
                $statement->execute();
                $id_mun = $statement->fetch();
                $e_Ubicacion = $usr_mun != $id_mun[0] ? 6 : 0;        }
                break;
        }


        //Checar que el radio button se haya seleccionado, value "vacio" -> error 7
        $e_Wp = $whatsapp == 'error_wp_empty' ? 7 : 0;
        
        
        //Checar que la clave de elector sea única -> error 8
        
        if($e_Cve == 0){
            $statement = $con->prepare('
            SELECT cve_elec FROM persona WHERE cve_elec = "'.$cve_elec.'"'
            );
            $statement->execute();
            $resultado = $statement->fetch();
            $e_Cve = $resultado != false ? 8 : 0;
            
        }
        
        //Checar que el rfc tenga al menos 5 dígitos -> error 9
        $e_CP = strlen($cp) != 5 ? 9 : 0;
        
        //Checar que el nombre completo no se encuentre en la base de datos -> error 10
        $statement = $con->prepare('
        SELECT COUNT(*) FROM persona WHERE nombre = "'.$nombre.'" AND ap_pat ="'.$ap_pat.'" AND ap_mat = "'.$ap_mat.'"'
        );
        $statement->execute();
        $resultado = $statement->fetch();
        $e_NombreCompleto = $resultado[0] != 0 ? 10 : 0;
        
        
        //echo ' Cve'.$e_Cve.' Nom'.$e_Nom.' apPat'.$e_Apat.' apMat'.$e_Amat.' Nac'.$e_Nac.' CURP'.$e_Curp.' fb'.$e_Fb.' cel'.$e_Cel.' dom'.$e_Dom.' secc'.$e_Secc;
        //si todo esta bien las variables de error pasan a 0 y se guarda el registro
        
        if($e_Cve == 0 && $e_Nom == 0 && $e_Apat == 0 && $e_Amat == 0 && $e_Nac == 0 && $e_Curp == 0 && $e_Fb == 0 && $e_Cel == 0 && $e_Dom == 0 && $e_Secc == 0 && $e_Dem == 0 && $e_Wp == 0 && $e_NombreCompleto == 0){
            //echo $celular;
            $fb = ord($fb) == 0 ? "0" : $fb;
            
            if($num_int == ''){
                $num_int = 0;
            }
            /*if($telefono == ''){
                $telefono = 0;
            }*/
            
            if($id_usuario == 0 || ord($fb) == 0){
                $id_usuario = NULL;
            }
            
            //echo ' Cve'.$cve_elec.' Nom'.$nombre.' apPat'.$ap_pat.' apMat'.$ap_mat.' Nac'.$f_nac.' sexo'.$sexo.' CURP'.$curp.' fb'.$fb.' cel'.$celular.' telefono'.$telefono.' afiliacion'.$afiliacion.' calle_num'.$calle_num.' num_int'.$num_int.' colonia'.$colonia.' cp'.$cp.' localidad'.$localidad.' ciudad'.$ciudad.' estado'.$estado.' pais'.$pais.' demarc'.$demarc.' seccion'.$seccion.' lat'.$lat.' lng'.$lng.' id_usuario'.$id_usuario;
            $statement = $con->prepare('
            INSERT INTO persona (id_persona, cve_elec, nombre, ap_pat, ap_mat, f_nac, sexo, curp, fb, celular, whatsapp, afiliacion, calle_num, num_int, colonia, cp, localidad, ciudad, estado, pais, demarcacion, zona, seccion, lat, lng, id_usuario) VALUES (NULL, :cve_elec, :nombre, :ap_pat, :ap_mat, :f_nac, :sexo, :curp, :fb, :celular, :whatsapp, :afiliacion, :calle_num, :num_int, :colonia, :cp, :localidad, :ciudad, :estado, :pais, :demarcacion, :zona, :seccion, :lat, :lng, :id_usuario)');
            $statement->execute(array(
                ':cve_elec' => $cve_elec,
                ':nombre' => $nombre,
                ':ap_pat' => $ap_pat,
                ':ap_mat' => $ap_mat,
                ':f_nac' => $f_nac,
                ':sexo' => $sexo,
                ':curp' => $curp,
                ':fb' => $fb,
                ':celular' => $celular,
                ':whatsapp' => $whatsapp,
                ':afiliacion' => $afiliacion,
                ':calle_num' => $calle_num,
                ':num_int' => $num_int,
                ':colonia' => $colonia,
                ':cp' => $cp,
                ':localidad' => $localidad,
                ':ciudad' => $ciudad,
                ':estado' => $estado,
                ':pais' => $pais,
                ':demarcacion' => $demarc,
                ':seccion' => $seccion,
                ':zona' => $zona,
                ':lat' => $lat,
                ':lng' => $lng,
                ':id_usuario' => $id_usuario
            ));
            $resultado = $statement->fetchAll(); 
            
            $_SESSION['usuario_registrado'] = 'registrado';
            
            check_save_register("registro-ine");

        }
    }

    if($form_id == 2){
        $_SESSION['usuario_registrado'] = 'no';
        $wp_exists_asp = false;
        $nombre_asp = ucwords(limpiar_inputs($_POST['nombre_asp']));
        $ap_pat_asp = ucwords(limpiar_inputs($_POST['ap_pat_asp']));
        $ap_mat_asp = ucwords(limpiar_inputs($_POST['ap_mat_asp']));
        $f_nac_asp = limpiar_inputs($_POST['f_nac_asp']);
        $f_nac_asp = date($f_nac_asp);
        $sexo_asp = limpiar_inputs($_POST['sexo_asp']);
        $fb_asp = limpiar_inputs($_POST['fb_asp']);
        $celular_asp = limpiar_inputs($_POST['celular_asp']);
        $whatsapp_asp = isset($_POST['whatsapp_asp']);
        $whatsapp_asp = $whatsapp_asp == NULL ? "error_wp_vacio" : $_POST['whatsapp_asp'];       
        
        $calle_num = limpiar_inputs($_POST['calle_num']);
        $num_int = limpiar_inputs($_POST['num_int']);
        $colonia = limpiar_inputs($_POST['colonia']);
        $cp = limpiar_inputs($_POST['cp']);
        $localidad = limpiar_inputs($_POST['localidad']);
        $ciudad = limpiar_inputs($_POST['ciudad']);
        $estado = limpiar_inputs($_POST['estado']);
        $pais = limpiar_inputs($_POST['pais']);
        $demarc = limpiar_inputs($_POST['demarc']);
        $dems = limpiar_inputs($_POST['dems']);
        $seccion = limpiar_inputs($_POST['secc']);
        $seccs = limpiar_inputs($_POST['seccs']);
        $zona = limpiar_inputs($_POST['zona']);
        $zonas = limpiar_inputs($_POST['zonas']);
        $proms = ($_POST['promotores']);
        $lat = limpiar_inputs($_POST['lat']);
        $lng = limpiar_inputs($_POST['lng']);
        $id_usuario = $_POST['promotor'];


        $dems = explode(",",$dems);
        $seccs = explode(",",$seccs);
        $zonas = explode(",",$zonas);
        array_pop($dems);
        array_pop($seccs);
        array_pop($zonas);

        //checar que no esten vacios los input -> identificador error 1
        $e_Nom_asp = $nombre_asp === '' ? 1 : 0;
        $e_Apat_asp = $ap_pat_asp == '' ? 1 : 0;
        $e_Amat_asp = $ap_mat_asp == '' ? 1 : 0;
        $e_Nac_asp = $f_nac_asp == '' ? 1 : 0;
        $e_Cel_asp = ord($celular_asp) == 0 ? 1 : 0;
        $e_Dom = ord($calle_num) == 0 ? 1 : 0;
        
        //checa datos mínimos de los input correspondientes -> identificador error 2
        $e_Cel_asp = (strlen($celular_asp) !== 10) ? 2 : 0;
        $e_Dom = (ord($calle_num) == 0 || ord($localidad) == 0) ? 2 : 0;
        
        //Checar inputs que solo contengan texto error 4
        if($e_Nom_asp == 0){$e_Nom_asp = (ord($nombre_asp) !== 0 && !val_car($nombre_asp)) ? 4 : 0;}
        if($e_Apat_asp == 0){$e_Apat_asp = !val_car($ap_pat_asp) ? 4 : 0;}
        if($e_Apat_asp == 0){$e_Amat_asp = !val_car($ap_mat_asp) ? 4 : 0;}

        //Checar combos sin valor, value 0 -> error 5
        $e_Secc = $seccion == '0' ? 5 : 0;
        $e_Dem = $demarc == '0' ? 5 : 0;
        $e_Zona = $zona == '0' ? 5 : 0;
        
        //Checar si la persona pertenece al municipio asignado -> identificador error 6
        switch($usr_nivel){
            case "1":
            case "2":
                //acepta todo
                break;
            case "3":
            case "4":
            case "5":
            case "6":
            case "7":
                
                if(ord($ciudad) != 0){
                    $statement = $con->prepare('SELECT id_municipio FROM municipio WHERE municipio = "'.$ciudad.'"');
                $statement->execute();
                $id_mun = $statement->fetch();
                $e_Ubicacion = $usr_mun != $id_mun[0] ? 6 : 0; 
                }
                       
                break;
        }
        
        //Checar que el radio button se haya seleccionado, value "vacio" -> error 7
        $e_Wp_asp = $whatsapp_asp == 'error_wp_vacio' ? 7 : 0;
        
        //Checar que el nombre completo no se encuentre en la base de datos -> error 10
        $statement = $con->prepare('
        SELECT COUNT(*) FROM persona WHERE nombre = "'.$nombre.'" AND ap_pat ="'.$ap_pat.'" AND ap_mat = "'.$ap_mat.'"'
        );
        $statement->execute();
        $resultado = $statement->fetch();
        $e_NombreCompleto = $resultado[0] != 0 ? 10 : 0;
        
        if($e_Nom_asp == 0 && $e_Apat_asp == 0 && $e_Amat_asp == 0 && $e_Nac_asp == 0 && $e_Cel_asp == 0 && $e_Dom == 0 && $e_Secc == 0 && $e_Dem == 0 && $e_Zona == 0 && $e_Wp_asp == 0 ){
            
            if($num_int == ''){
                $num_int = 0;
            }
            if($id_usuario == 0){
                $id_usuario = NULL;
            }
            
            $statement = $con->prepare('
            INSERT INTO persona (id_persona, cve_elec, nombre, ap_pat, ap_mat, f_nac, sexo, curp, fb, celular, whatsapp, afiliacion, calle_num, num_int, colonia, cp, localidad, ciudad, estado, pais, demarcacion, zona, seccion, lat, lng, id_usuario) VALUES (NULL, :cve_elec, :nombre, :ap_pat, :ap_mat, :f_nac, :sexo, :curp, :fb, :celular, :whatsapp, :afiliacion, :calle_num, :num_int, :colonia, :cp, :localidad, :ciudad, :estado, :pais, :demarcacion, :zona, :seccion, :lat, :lng, :id_usuario)');
            $statement->execute(array(
                ':cve_elec' => 0,
                ':nombre' => $nombre_asp,
                ':ap_pat' => $ap_pat_asp,
                ':ap_mat' => $ap_mat_asp,
                ':f_nac' => $f_nac_asp,
                ':sexo' => $sexo_asp,
                ':curp' => 0,
                ':fb' => 0,
                ':celular' => $celular_asp,
                ':whatsapp' => $whatsapp,
                ':afiliacion' => 0,
                ':calle_num' => $calle_num,
                ':num_int' => $num_int,
                ':colonia' => $colonia,
                ':cp' => $cp,
                ':localidad' => $localidad,
                ':ciudad' => $ciudad,
                ':estado' => $estado,
                ':pais' => $pais,
                ':demarcacion' => $demarc,
                ':zona' => $zona,
                ':seccion' => $seccion,
                ':lat' => $lat,
                ':lng' => $lng,
                ':id_usuario' => $id_usuario
            ));
            $resultado = $statement->fetchAll(); 

            $_SESSION['usuario_registrado'] = 'registrado';
            
            check_save_register("aspirantes");
        }

    }
    if($form_id == 0){
        
    }
    

}

?>

<!DOCTYPE html>
<html lang="es-MX">
<head>
    <meta charset="UTF-8">
    <title>Registro/INE</title>
    

    <script src="https://polyfill.io/v3/polyfill.min.js?features=default"></script>
   
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <link rel="stylesheet" type="text/css" href="css/normalize.css">
    <link rel="stylesheet" type="text/css" href="css/base.css">
    <link rel="stylesheet" type="text/css" href="css/sidebar-menu.css">
    <link rel="stylesheet" type="text/css" href="css/form.css">
    <link rel="stylesheet" type="text/css" href="css/afinidad.css">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Titillium+Web:wght@200;300;400;600;700;900&display=swap" rel="stylesheet">
    
    <script src="https://kit.fontawesome.com/9c52d851d9.js" crossorigin="anonymous"></script>
    
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.3/jquery.min.js"></script>
    
</head>
<body>
   <header>
       <?php require "includes/header.php"; ?>
       
       <?php require "includes/popup_editar_usuario.php"; ?>
   </header>
   
   <div class="hamb" id="hamb"><p>.</p><p>.</p><p>.</p></div>
   <div class="close_hamb" id="close_hamb"><p>.</p><p>.</p></div>
     
    <div class="todo">
       <?php require "includes/menu.php"; ?>

        <div class="cont">
            <div class="form-register-cont">
        
                <form class="register-form" id="register-form" name="guardarRegistro" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST" enctype="multipart/form-data">
                    
                    <?php if($usr_nivel != "8"):?>
                        <h2 class="form__title" id="form__title">Registro de Personas</h2>  
                    <?php else:?>
                        <h2 class="form__title" id="form__title">Registro de Familiares</h2>  
                    <?php endif;?>
                       
                       
                       
                    <div class="form__div form_id__">
                            <input id="form_id" type="text" class="form__input" placeholder=" " name="form_id" value="<?php if(isset($form_id)){ echo $form_id; $form_id_exists = true;} else{echo '0';}?>" readonly style="display:none">
                            
                           

                        </div>
                       
                       

                    <div class="opciones_registro">
                        <div class="btns_opt_reg">
                            <div class="form__button opt_reg" id="reg_afiliado">

                                <p id="form0" class="submit-txt">Registrar persona afiliada</p>
                            </div>

                            <div class="form__button opt_reg" id="reg_aspirante">

                                <p id="form1" class="submit-txt">Registrar aspirante</p>
                            </div>
                        </div>
                        <?php if($form_id_exists && $form_id == 0):?>
                            <div class="form__error">
                                <i class="fa-solid fa-triangle-exclamation"></i> <p class="error post">Seleccione un formulario</p>
                            </div>
                            <?php endif;?>
                        
                    </div>  
                    
                    <div class="info_afinidad" id="info_afinidad">
                        <!--input name="form_id" type="text" value="1" readonly style="display:none"/-->
                        <h3 class="form__title title_type_form" id="form__title">Datos de afiliado</h3>  
                        
                        <div class="form__div cve_elec" id="cve_elec_cont">
                            <input type="text" class="form__input <?php if($e_Cve !== 0):?> error_input<?php endif;?>" placeholder=" " name="cve_elec" id="cve_elec" value="<?php if(isset($cve_elec)){ echo $cve_elec; $cve_exists = true;}?>">
                            <label for="" class="form__label">Clave de Elector*</label>
                            <?php if($cve_exists && ord($cve_elec) == 0):?>
                            <div class="form__error">
                                <i class="fa-solid fa-triangle-exclamation"></i> <p class="error post">Ingrese la clave de elector</p>
                            </div>
                            <?php endif;?>
                            <?php if($e_Cve == 2 && strlen($cve_elec) >= 1):?>
                            <div class="form__error">
                                <i class="fa-solid fa-triangle-exclamation"></i> <p class="error post">Ingrese una clave de elector váilda</p>
                            </div>
                            <?php endif;?>
                            <?php if($e_Cve == 8):?>
                            <div class="form__error">
                                <i class="fa-solid fa-triangle-exclamation"></i> <p class="error post">Clave de elector ya registrada</p>
                            </div>
                            <?php endif;?>
                        </div>

                        <div class="form__div nombre">
                            <input id="nombre" type="text" class="form__input fb_keyup" placeholder=" " name="nombre" value="<?php if(isset($nombre)){ echo $nombre; $nom_exists = true;}?>">
                            <label for="" class="form__label">Nombre(s)*</label>
                            <?php if($e_Nom == 1 || ($nom_exists && ord($nombre) == 0)):?>
                            <div class="form__error">
                                <i class="fa-solid fa-triangle-exclamation"></i> <p class="error post">Ingrese el nombre(s)</p>
                            </div>
                            <?php endif;?>

                            <?php if($e_Nom == 4):?>
                            <div class="form__error">
                                <i class="fa-solid fa-triangle-exclamation"></i> <p class="error post">Ingrese un nombre válido</p>
                            </div>
                            <?php endif;?>
                            <?php if($e_NombreCompleto == 10):?>
                            <div class="form__error" style="width: 100rem;">
                                <i class="fa-solid fa-triangle-exclamation"></i> <p class="error post"> El nombre <?php echo $nombre.' '.$ap_pat.' '.$ap_mat;?> ya se encuentra registrado</p>
                            </div>
                            <?php endif;?>
                        </div>

                        <div class="form__div ap_pat">
                            <input id="ap_pat" type="text" class="form__input fb_keyup" placeholder=" " name="ap_pat" value="<?php if(isset($ap_pat)){ echo $ap_pat; $ap_exists = true;} ?>">
                            <label for="" class="form__label">Apellido Paterno*</label>
                            <?php if($e_Apat == 1 || ($ap_exists && ord($ap_pat) == 0)):?>
                            <div class="form__error">
                                <i class="fa-solid fa-triangle-exclamation"></i> <p class="error post">Ingrese el apellido paterno</p>
                            </div>
                            <?php endif;?>

                            <?php if($e_Apat == 4):?>
                            <div class="form__error">
                                <i class="fa-solid fa-triangle-exclamation"></i> <p class="error post">Ingrese un apellido válido</p>
                            </div>
                            <?php endif;?>
                        </div>

                        <div class="form__div ap_mat">
                            <input id="ap_mat" type="text" class="form__input fb_keyup" placeholder=" " name="ap_mat" value="<?php if(isset($ap_mat)){ echo $ap_mat; $am_exists = true;} ?>">
                            <label for="" class="form__label">Apellido Materno*</label>
                            <?php if($e_Amat == 1 || ($am_exists && ord($ap_mat) == 0)):?>
                            <div class="form__error">
                                <i class="fa-solid fa-triangle-exclamation"></i> <p class="error post">Ingrese el apellido materno</p>
                            </div>
                            <?php endif;?>
                            <?php if($e_Amat == 4):?>
                            <div class="form__error">
                                <i class="fa-solid fa-triangle-exclamation"></i> <p class="error post">Ingrese un apellido válido</p>
                            </div>
                            <?php endif;?>
                        </div>

                        <div class="form__div f_nac">
                            <input type="date" class="form__input" placeholder=" " name="f_nac" onfocus="this.showPicker()" id="f_nac" value="<?php if(isset($f_nac)) echo $f_nac; ?>" min="" max="">
                            <label for="" class="form__label">Fecha de Nacimiento*</label>
                            <?php if($e_Nac == 1):?>
                            <div class="form__error">
                                <i class="fa-solid fa-triangle-exclamation"></i> <p class="error post">Ingrese fecha de nacimiento</p>
                            </div>
                            <?php endif;?>
                        </div>


                        <div class="form__div sexo">
                            <div class="combo combo-sexo">
                                <select class="form__input sexo" id="sexo" name="sexo">
                                  <option value="M" <?php if(isset($sexo) && $sexo == 'M'):?>' selected="selected"'<?php endif;?> >Masculino</option>
                                  <option value="F" <?php if(isset($sexo) && $sexo == 'F'):?>' selected="selected"'<?php endif;?> >Femenino</option>   
                                </select>
                                <label class="form__label sexo_label" for="sexo">Sexo</label>
                            </div>
                        </div>

                        <div class="form__div curp" id="curp_cont">
                            <input type="text" class="form__input" placeholder=" " name="curp" id="curp" value="<?php if(isset($curp)){ echo $curp; $curp_exists = true;}?>">
                            <label for="" class="form__label">CURP*</label>
                            <?php if($curp_exists && ord($curp) == 0):?>
                            <div class="form__error">
                                <i class="fa-solid fa-triangle-exclamation"></i> <p class="error post">Ingrese el CURP</p>
                            </div>
                            <?php endif;?>
                            <?php if($e_Curp == 2 && strlen($curp) >= 1):?>
                            <div class="form__error">
                                <i class="fa-solid fa-triangle-exclamation"></i> <p class="error post">Ingrese un CURP válido</p>
                            </div>
                            <?php endif;?>
                        </div>

                        <div class="form__div fb" id="fb_cont">
                            <input id="fb" type="text" class="form__input" placeholder=" " name="fb" value="<?php if(isset($fb)){ echo $fb; $fb_exists = true;}?>">
                            <label for="" class="form__label">Facebook</label>
                            
                            
                        </div>

                        <div class="form__div fb celular">
                            <input type="number" class="form__input" placeholder=" " name="celular" value="<?php if(isset($celular)){ echo $celular; $cel_exists = true;}?>">
                            <label for="" class="form__label">Teléfono*</label>
                            <?php if($cel_exists && ord($celular) == 0):?>
                            <div class="form__error">
                                <i class="fa-solid fa-triangle-exclamation"></i> <p class="error post">Ingrese un número de teléfono</p>
                            </div>
                            <?php endif;?>
                            <?php if($e_Cel == 2 && strlen($celular) >= 1):?>
                            <div class="form__error">
                                <i class="fa-solid fa-triangle-exclamation"></i> <p class="error post">Ingrese un número de teléfono válido</p>
                            </div>
                            <?php endif;?>
                        </div>

                        <!--div class="form__div afiliacion">
                            <input type="number" class="form__input" placeholder=" " name="telefono" value="<?php //if(isset($telefono)) echo $telefono; ?>">
                            <label for="" class="form__label">Teléfono fijo</label>
                        </div-->

                       <div class="form__div ">
                            <div class="combo combo-afiliacion">
                                <div class="form__input">
                                    <input class="radio__btn" type="radio" id="whatsapp_si" name="whatsapp" value="1" <?php if(isset($whatsapp) && $whatsapp == '1'):?>checked<?php $wp_exists = true; endif;?>>
                                    <label class="radio__btn" for="whatsapp_si">Si</label>
                                    
                                    <input class="radio__btn" type="radio" id="whatsapp_no" name="whatsapp" value="0" <?php if(isset($whatsapp) && $whatsapp == '0'):?>checked<?php $wp_exists = true; endif;?>>
                                    <label class="radio__btn" for="whatsapp_no">No</label>
                                </div>
                                <label class="form__label sexo_afiliacion" for="whatsapp">Whatsapp</label>
                                
                                   
                                <?php if($e_Wp == 7):?>
                                <div class="form__error">
                                    <i class="fa-solid fa-triangle-exclamation"></i> <p class="error post">Seleccione una opción</p>
                                </div>
                                <?php endif;?>
                                
                            </div>
                        </div>
                       
                        <div class="form__div" id="afiliacion_cont">
                            <div class="combo combo-afiliacion">
                                <select class="form__input afiliacion" id="afiliacion" name="afiliacion">
                                  <option value="1" <?php if(isset($afiliacion) && $afiliacion == '1'):?>' selected="selected"'<?php endif;?>>Si</option>
                                  <option value="0" <?php if(isset($afiliacion) && $afiliacion == '0'):?>' selected="selected"'<?php endif;?>>No</option>   
                                </select>
                                <label class="form__label sexo_afiliacion" for="afiliacion">Afiliación</label>
                            </div>
                        </div>
                    
                    </div>
                    
                    
                    
                    <div class="info_aspirante" id="info_aspirante">
                       
                       <h3 class="form__title title_type_form" id="form__title">Datos de aspirante</h3>  
                       
                        <div class="form__div nombre">
                            <input id="nombre_asp" type="text" class="form__input fb_keyup" placeholder=" " name="nombre_asp" value="<?php if(isset($nombre_asp)){ echo $nombre_asp; $nom_exists_asp = true;}?>">
                            <label for="" class="form__label">Nombre(s)*</label>
                            <?php if($e_Nom_asp == 1 || ($nom_exists_asp && ord($nombre_asp) == 0)):?>
                            <div class="form__error">
                                <i class="fa-solid fa-triangle-exclamation"></i> <p class="error post">Ingrese el nombre(s)</p>
                            </div>
                            <?php endif;?>

                            <?php if($e_Nom_asp == 4):?>
                            <div class="form__error">
                                <i class="fa-solid fa-triangle-exclamation"></i> <p class="error post">Ingrese un nombre válido</p>
                            </div>
                            <?php endif;?>
                        </div>

                        <div class="form__div ap_pat">
                            <input id="ap_pat_asp" type="text" class="form__input fb_keyup" placeholder=" " name="ap_pat_asp" value="<?php if(isset($ap_pat_asp)){ echo $ap_pat_asp; $ap_exists_asp = true;} ?>">
                            <label for="" class="form__label">Apellido Paterno*</label>
                            <?php if($e_Apat_asp == 1 || ($ap_exists_asp && ord($ap_pat_asp) == 0)):?>
                            <div class="form__error">
                                <i class="fa-solid fa-triangle-exclamation"></i> <p class="error post">Ingrese el apellido paterno</p>
                            </div>
                            <?php endif;?>

                            <?php if($e_Apat_asp == 4):?>
                            <div class="form__error">
                                <i class="fa-solid fa-triangle-exclamation"></i> <p class="error post">Ingrese un apellido válido</p>
                            </div>
                            <?php endif;?>
                        </div>

                        <div class="form__div ap_mat">
                            <input id="ap_mat_asp" type="text" class="form__input fb_keyup" placeholder=" " name="ap_mat_asp" value="<?php if(isset($ap_mat_asp)){ echo $ap_mat_asp; $am_exists_asp = true;} ?>">
                            <label for="" class="form__label">Apellido Materno*</label>
                            <?php if($e_Amat_asp == 1 || ($am_exists_asp && ord($ap_mat_asp) == 0)):?>
                            <div class="form__error">
                                <i class="fa-solid fa-triangle-exclamation"></i> <p class="error post">Ingrese el apellido materno</p>
                            </div>
                            <?php endif;?>
                            <?php if($e_Amat_asp == 4):?>
                            <div class="form__error">
                                <i class="fa-solid fa-triangle-exclamation"></i> <p class="error post">Ingrese un apellido válido</p>
                            </div>
                            <?php endif;?>
                        </div>

                        <div class="form__div f_nac">
                            <input type="date" class="form__input" placeholder=" " name="f_nac_asp" onfocus="this.showPicker()" id="f_nac_asp" value="<?php if(isset($f_nac_asp)) echo $f_nac_asp; ?>" min="" max="">
                            <label for="" class="form__label">Fecha de Nacimiento*</label>
                            <?php if($e_Nac_asp == 1):?>
                            <div class="form__error">
                                <i class="fa-solid fa-triangle-exclamation"></i> <p class="error post">Ingrese fecha de nacimiento</p>
                            </div>
                            <?php endif;?>
                        </div>


                        <div class="form__div sexo">
                            <div class="combo combo-sexo">
                                <select class="form__input sexo" id="sexo_asp" name="sexo_asp">
                                  <option value="M" <?php if(isset($sexo_asp) && $sexo_asp == 'M'):?>' selected="selected"'<?php endif;?> >Masculino</option>
                                  <option value="F" <?php if(isset($sexo_asp) && $sexo_asp == 'F'):?>' selected="selected"'<?php endif;?> >Femenino</option>   
                                </select>
                                <label class="form__label sexo_label" for="sexo">Sexo</label>
                            </div>
                        </div>
                        
                        
                        <div class="form__div fb">
                            <input id="fb_asp" type="text" class="form__input" placeholder=" " name="fb_asp" value="<?php if(isset($fb_asp)){ echo $fb_asp;}?>">
                            <label for="" class="form__label">Facebook</label>
                            
                        </div>


                        <div class="form__div fb celular">
                            <input type="number" class="form__input" placeholder=" " name="celular_asp" value="<?php if(isset($celular_asp)){ echo $celular_asp; $cel_exists_asp = true;}?>">
                            <label for="" class="form__label">Teléfono*</label>
                            <?php if($cel_exists_asp && ord($celular_asp) == 0):?>
                            <div class="form__error">
                                <i class="fa-solid fa-triangle-exclamation"></i> <p class="error post">Ingrese un número de teléfono</p>
                            </div>
                            <?php endif;?>
                            <?php if($e_Cel_asp == 2 && strlen($celular_asp) >= 1):?>
                            <div class="form__error">
                                <i class="fa-solid fa-triangle-exclamation"></i> <p class="error post">Ingrese un número de teléfono válido</p>
                            </div>
                            <?php endif;?>
                        </div>
                        
                        <div class="form__div ">
                            <div class="combo combo-afiliacion">
                                <div class="form__input">
                                    <input class="radio__btn" type="radio" id="whatsapp_si_asp" name="whatsapp_asp" value="1" <?php if(isset($whatsapp_asp) && $whatsapp_asp == '1'):?>checked<?php $wp_exists_asp = true; endif;?>>
                                    <label class="radio__btn" for="whatsapp_si_asp">Si</label>
                                    
                                    <input class="radio__btn" type="radio" id="whatsapp_no_asp" name="whatsapp_asp" value="0" <?php if(isset($whatsapp_asp) && $whatsapp_asp == '0'):?>checked<?php $wp_exists_asp = true; endif;?>>
                                    <label class="radio__btn" for="whatsapp_no_asp">No</label>
                                </div>
                                <label class="form__label sexo_afiliacion" for="whatsapp">Whatsapp</label>
                                
                                <?php if($e_Wp_asp == 7):?>
                                    <div class="form__error">
                                        <i class="fa-solid fa-triangle-exclamation"></i> <p class="error post">Seleccione una opción</p>
                                    </div>
                                    <?php endif;?>
                                
                            </div>
                        </div>
                   
                    
                    </div>
                    
                    
                    
                    <div id="contenido_domicilio">
                        
                        <div class="form__div ">
                            <div class="combo combo-afiliacion">
                                <div class="form__input">
                                   <label id="chk_rural_lbl" class="radio__btn container" for="chk_rural" title="Si el domicilio no aparece en las sugerencias habilita esta casilla e ingresalo manualmente. Una vez posicionado el marcador en el mapa se podra arrastrar a la posición deseada">¿Domicilio inexistente?
                                    <input class="check__btn" type="checkbox" id="chk_rural" name="chk_rural" value="1" title="Si el domicilio no aparece en las sugerencias habilita esta casilla e ingresalo manualmente. Una vez posicionado el marcador en el mapa se podra arrastrar a la posición deseada">
                                    <span id="span_chk_rural" class="checkmark"></span>
                                   
                                   </label> 
                                   <img id="help_domicilio" src="img/help.png" title="Si el domicilio no aparece en las sugerencias habilita esta casilla e ingresalo manualmente. Una vez posicionado el marcador en el mapa se podra arrastrar a la posición deseada" alt="Documentación" class="icon ic_b_help">
                                </div>
                                
                                
                                
                                <!--label for="dem_612_add" id="label_dem_612_add" class="container">612
                                
                                <input type="checkbox" id="dem_612_add" name="dems_resp_t_add" class="dems_resp_t_add" value="612">
                                <span id="span_dem_612_add" class="checkmark"></span>
                                
                                
                                </label-->
                                
                                
                                
                                
                                <?php //if($e_Wp_asp == 7):?>
                                    <!--div class="form__error">
                                        <i class="fa-solid fa-triangle-exclamation"></i> <p class="error post">Seleccione una opción</p>
                                    </div-->
                                    <?php //endif;?>
                                
                            </div>
                        </div>
                       
                       
                        <div class="form__div">
                            <input placeholder=" " id="domicilio" name="calle_num" required autocomplete="off" class="form__input" value="<?php if(isset($calle_num)){ echo $calle_num; $dom_exists = true;}?>"/>
                            <label class="full-field form__label">Domicilio (Calle y número)*</label>
                            <?php if($dom_exists && ord($calle_num) == 0):?>
                            <div class="form__error">
                                <i class="fa-solid fa-triangle-exclamation"></i> <p class="error post">Ingrese el domicilio</p>
                            </div>
                            <?php elseif($e_Ubicacion == 6):?>
                            <div class="form__error">
                                <i class="fa-solid fa-triangle-exclamation"></i> <p class="error post">El domicilio no pertenece al municipio asignado al usuario</p>
                            </div>

                            <?php endif;?>
                            <?php if($e_Dom == 2 && strlen($calle_num) >= 1):?>
                            <div class="form__error">
                                <i class="fa-solid fa-triangle-exclamation"></i> <p class="error post">Ingrese un domicilio válido</p>
                            </div>
                            <?php endif;?>


                        </div>

                        <div class="form__div"> 
                            <input placeholder=" " id="num_int" name="num_int" class="form__input" value="<?php if(isset($num_int)) echo $num_int; ?>"/>
                            <label class="full-field form__label">Número interior</label>
                        </div>

                        <div class="form__div" id="form__colonia">
                            <input placeholder=" " id="colonia" name="colonia" required class="form__input read" readonly value="<?php if(isset($colonia)) echo $colonia; ?>"/>
                            <label class="full-field form__label">Colonia</label>
                        </div>

                        <div class="form__div" id="form__div__cp">
                            <input type="number" placeholder=" " id="cp" name="cp" required class="form__input read" readonly value="<?php if(isset($cp)) echo $cp; ?>"/>
                            <label id="form__label__cp" class="slim-field-right form__label" for="postal_code">Código Postal</label>
                           
                            <?php if($e_CP == 9):?>
                            <div class="form__error" id="form__error__cp">
                                <i class="fa-solid fa-triangle-exclamation"></i> <p class="error post" id="error_p">C.P. inválido</p>
                            </div>
                            <?php endif;?>
                            
                        </div>

                        <div class="form__div" id="form__localidad">
                            <input placeholder=" " id="localidad" name="localidad" required class="form__input read" readonly value="<?php if(isset($localidad)) echo $localidad; ?>"/>
                            <label class="full-field form__label">Localidad</label>
                        </div>

                        <div class="form__div">
                            <input placeholder=" " id="ciudad" name="ciudad" required class="form__input read" readonly value="<?php if(isset($ciudad)) echo $ciudad; ?>"/>
                            <label class="full-field form__label">Municipio</label>
                        </div>

                        <div class="form__div">
                            <input placeholder=" " id="estado" name="estado" required class="form__input read" readonly value="<?php if(isset($estado)) echo $estado; ?>"/>
                            <label class="slim-field-left form__label">Estado</label>
                        </div>

                        <div class="form__div">
                            <input placeholder=" " id="pais" name="pais" required class="form__input read" readonly value="<?php if(isset($pais)) echo $pais; ?>"/>
                            <label class="full-field form__label">País</label>
                        </div>  

                        <input type="text" name="dems" id="dems" placheholder=" " value="<?php 

                            switch($usr_nivel){
                                case "1":
                                case "2":
                                case "3":
                                    if(isset($demarc)){ 
                                        foreach($dems as $key => $n){
                                            echo $n.','; 
                                        } 
                                    }
                                    break;
                                

                                    // OJO AQUIIII!!!
                                    //////////////////////
                                    //////////////////////
                                    
                                case "4":
                                    /*foreach($temp_coord_dems as $key => $n){
                                        echo $n.','; 
                                    } 
                                    break;*/
                                case "5":
                                case "6":
                                case "7":
                                    echo $usr_dem.',';
                                    break;
                            }

                        ?>" style="display:none" readonly/>

                        <div class="form__div">
                            <div class="combo combo-demarc">
                                <select class="form__input demarc" id="demarc" name="demarc">



                                    <?php if(isset($dems)) :?>
                                       <option value="0" <?php if(isset($demarc) && $demarc == '0'):?>' selected="selected"'<?php endif;?>>- Demarcación -</option>
                                        <?php foreach($dems as $key => $n):?>

                                            <option value="<?php echo $n ?>"      <?php if(isset($demarc) && $demarc == $n):?>' selected="selected"'<?php endif;?>         ><?php echo $n ?></option>
                                        <?php endforeach; ?>

                                    <?php else: ?>
                                    <option value="0" <?php if(isset($demarc) && $demarc == '0'):?>' selected="selected"'<?php endif;?>>- Demarcación -</option>

                                    <?php endif;?>

                                </select>
                                <label class="form__label sexo_afiliacion" for="afiliacion">Demarcación*</label>

                                <?php if($e_Dem == 5):?>
                                <div class="form__error">
                                    <i class="fa-solid fa-triangle-exclamation"></i> <p class="error post">Ingrese la demarcación</p>
                                </div>
                                <?php endif;?>

                            </div>
                        </div>
                           
                           
                    
                           
                           
                           <div class="form__div">
                           
                          
                        </div>
                        
                        
                        
                        
                        
                        
                        
                        
                        <input type="text" name="zonas" id="zonas" placheholder=" " value="<?php if(isset($zonas)){ foreach($zonas as $key => $n){ echo $n.','; } } ?>" style="display:none" readonly/>
                        
                        <div class="form__div">
                            <div class="combo combo-seccs">
                                <select class="form__input secc" id="zona" name="zona">


                                    <?php if(isset($seccs)) :?>
                                        <option value="0"  <?php if(isset($seccion) && $seccion == '0'):?>' selected="selected"'<?php endif;?>>- Zona -</option> 

                                        <?php foreach($zonas as $key => $n):?>
                                           <?php if($n != 0):?>
                                            <option value="<?php echo $n ?>"    <?php if(isset($zona) && $zona == $n):?>' selected="selected"'<?php endif;?>    ><?php echo $n ?></option>
                                            <?php endif;?>
                                        <?php endforeach; ?>

                                    <?php else: ?>
                                    <option value="0"  <?php if(isset($zona) && $zona == '0'):?>' selected="selected"'<?php endif;?>>- Zona -</option> 

                                    <?php endif;?>

                                </select>

                                <label class="form__label sexo_afiliacion" for="afiliacion">Zona*</label>

                                <?php if($e_Zona == 5):?>
                                <div class="form__error">
                                    <i class="fa-solid fa-triangle-exclamation"></i> <p class="error post" id="error_zona">Ingrese la zona</p>
                                </div>
                                <?php endif;?>

                            </div>
                        </div>
                        
                        
                        
                        
                        

                        <input type="text" name="seccs" id="seccs" placheholder=" " value="<?php if(isset($seccs)){ foreach($seccs as $key => $n){ echo $n.','; } } ?>" style="display:none" readonly/>

                        <div class="form__div">
                            <div class="combo combo-seccs">
                                <select class="form__input secc" id="secc" name="secc">


                                    <?php if(isset($seccs)) :?>
                                        <option value="0"  <?php if(isset($seccion) && $seccion == '0'):?>' selected="selected"'<?php endif;?>>- Sección -</option> 

                                        <?php foreach($seccs as $key => $n):?>
                                            <option value="<?php echo $n ?>"    <?php if(isset($seccion) && $seccion == $n):?>' selected="selected"'<?php endif;?>    ><?php echo $n ?></option>
                                        <?php endforeach; ?>

                                    <?php else: ?>
                                    <option value="0"  <?php if(isset($seccion) && $seccion == '0'):?>' selected="selected"'<?php endif;?>>- Sección -</option> 

                                    <?php endif;?>

                                </select>

                                <label class="form__label sexo_afiliacion" for="afiliacion">Sección*</label>

                                <?php if($e_Secc == 5):?>
                                <div class="form__error">
                                    <i class="fa-solid fa-triangle-exclamation"></i> <p class="error post">Ingrese la sección</p>
                                </div>
                                <?php endif;?>

                            </div>
                        </div>


                       
                       
                       
                        <div class="form__div promotor__" style="width: 100%">
                       <input type="text" name="promotores" id="promotores" placheholder=" "  value='<?php if(isset($proms)){ echo $proms; } else{} //echo '<option value="0">- Promotor -</option>';} ?>' style="display:none;" readonly />
                       
                       
                        <div class="combo combo-promotor">
                           
                            <select class="form__input promotor" id="promotor" name="promotor">
                                <?php if($usr_nivel == 1 || $usr_nivel == 2 || $usr_nivel == 3 ||  $usr_nivel == 4):?>
                                    <option value="0" >
                                        - Promotor -
                                    </option> 
                               
                                <?php else:?>
                                   <option value="0">- Promotor -</option>
                                   
                                    <?php if($usr_nivel != 8):?>
                                   
                                    <?php foreach($promotores as $promotor):?>
                                        <option value="<?php echo $promotor['id_usuario']; ?>" 
                                        <?php if(isset($id_usuario) && $id_usuario == $promotor['id_usuario']):?> selected="selected"<?php endif;?>>
                                            <?php echo $promotor['nombre'].' '.$promotor['apellidos'] ?>
                                        </option>   
                                    <?php endforeach;?>
                                    
                                    <?php else:?>
                                        <?php 
                                        $statement = $con ->prepare('SELECT id_usuario, nombre, apellidos FROM usuario WHERE id_usuario = "'.$usr_usuario.'"');
                                        $statement->execute();
                                        $prom_especial = $statement->fetch();
                                        ?>
                                        
                                        <option value="<?php echo $prom_especial['id_usuario']; ?>" 
                                        <?php if(isset($id_usuario) && $id_usuario == $prom_especial['id_usuario']):?> selected="selected"<?php endif;?>>
                                            <?php echo $prom_especial['nombre'].' '.$prom_especial['apellidos'] ?>
                                        </option>
                                        
                                    <?php endif;?>
                               <?php endif;?>
                            </select>
                            
                            <label class="form__label colonia_label" for="">Promotor</label>
                        
                        </div>
                        
                    </div>
                    
                    </div> 
                    
                    <div class="form__div form__div__searchMap">
                        <!-- mapa -->
                        <div id="map"></div>
                        <!-- info marker -->
                        <div id="infowindow-content">
                            <span id="place-name" class="title"></span><br />
                            <span id="place-address"></span>
                        </div>
                        <!-- Coordenadas -->
                        <input type="text" id="place-lat" class="form__input" placeholder=" " name="lat" style="display:none" readonly value="<?php if(isset($lat)) echo $lat; ?>">
                        <input id="place-lng" type="text" class="form__input" placeholder=" " name="lng" style="display:none" readonly value="<?php if(isset($lng)) echo $lng; ?>">  
                    </div> 
                    
                    
                    
                    
                   
                    
                    
                    <div class="campos">
                        <p>*Campos obligatorios</p>
                    </div>
                    
                    <div class="form__button" onClick="guardarRegistro.submit();">
                        <i class="fas fa-save"></i>
                        <p class="submit-txt">Guardar Registro</p>
                    </div>
                </form> 

                
            </div>
        </div>
    </div>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.27.0/moment.min.js"></script>
    
    
        
    <script src="https://maps.googleapis.com/maps/api/js?key=API_KEY&libraries=places&callback=initAutocomplete&v=weekly&loading=async" defer></script>
    
    
    
    <!-- FUNCIONES Y VALIDACIONES DE FORMULARIO -->
    <script type="text/javascript">
    $(document).ready(function(){
        $('#cve_elec').css("text-transform", "none");
        let flag_ciudad_ajuste = 1;
        
        <?php if($_SERVER['REQUEST_METHOD'] == 'POST'): ?>
            <?php if(isset($proms) && $proms != ""):?>
            $('#promotor').html('<?php echo $proms; ?>');
            $('#promotor').val(<?php echo $id_usuario; ?>);
            <?php else:?>
            $('#promotor').html('<option value="0">- Promotor -</option>');
            <?php endif;?>
        
        <?php endif;?>
            
        
        
        $('#menu_registro').addClass("menu_seleccion");
        
        /*prepara el formato para rfc de la fecha de nacimiento*/
        cve_elec_input = document.querySelector("#cve_elec");
        cve_elec_input.focus();
      /*  function formatoFechaRFC(input){
            f_formato = input.val();
            f_formato = moment(input.val(),'YYYY/MM/DD').format('YYMMDD');
            return f_formato;
        }*/
        /*agrega primeros 4 caracteres a rfc*/
      /*  $('.rfc_keyup').on('keyup',function(){
            nombre = $('#nombre').val();
            ap_pat = $('#ap_pat').val();
            ap_mat = $('#ap_mat').val();
            rfc = "";

            if(ap_pat.length == 1){
                rfc = (ap_pat + ap_mat + nombre).toUpperCase();
            } else if (ap_pat.length >= 2){
                ap_pat = ap_pat.substring(0, 2);
                rfc = (ap_pat + ap_mat + nombre).toUpperCase();
            }
            if(ap_mat.length != 0){
                ap_mat = ap_mat.substring(0, 1);
                rfc = (ap_pat + ap_mat + nombre).toUpperCase();
            }
            if(nombre.length != 0){
                nombre = nombre.substring(0, 1);
                rfc = (ap_pat + ap_mat + nombre).toUpperCase();
            }
                
            if($('#f_nac').val() === ""){
                $('#rfc').val(rfc);
                $('#curp').val(rfc);
            } else{
                formatoFechaRFC($('#f_nac')); 
                $('#rfc').val(rfc+f_formato);
                $('#curp').val(rfc+f_formato);
            }
            
        });       */ 
        
        /*comprueba si la fecha de nacimiento se borró*/
        function dateIsValid(date) {
          return (
            Object.prototype.toString.call(date) === '[object Date]' && !isNaN(date)
          );
        }
        
        /*limita fecha de nacimiento a mayor de 15 años*/
        var fechaActual = new Date();
        // Calcular la fecha al 31 de diciembre de hace 15 años
        fechaHace15Anios = new Date(fechaActual.getFullYear() - 15, 11, 31);
        fechaMinima = '1910-12-31';
        // Formatear la fecha hace 15 años para establecerla como valor máximo del campo input
        fechaMaxima = fechaHace15Anios.toISOString().split('T')[0];
        // Establecer los valores máximo y mínimo
        $("#f_nac").add($("#f_nac_asp")).attr({  
           "max" : fechaMaxima,  
           "min" : fechaMinima  
        });  
        
        /*agrega fecha de nacimiento en input rfc*/
        /*$('#f_nac').on('change', function () {
            fecha_valida = dateIsValid(new Date($('#f_nac').val()));
            if($('#f_nac').val() === "" || fecha_valida === false){
                rfc = $('#rfc').val();
                rfc = rfc.substring(0, rfc.length-6);
                $('#rfc').val(rfc);
                $('#curp').val(rfc);
            } else {
                formatoFechaRFC($('#f_nac'));
                rfc = $('#rfc').val();
                if(rfc.length > 4){
                   rfc = rfc.substring(0, rfc.length-6);
                    $('#rfc').val(rfc + f_formato);
                    $('#curp').val(rfc + f_formato);
                }
                $('#rfc').val(rfc + f_formato);
                $('#curp').val(rfc + f_formato);
            }
            
        });*/
        
        /*Cargar sección a partir de demarcación del municipio*/
        $('#demarc').on('change', function () {
            $('#secc').html('<option value="0">- Sección -</option>');
            //$('#promotor').html('<option value="0">- Promotor1 -</option>');
            promotores_carga();
            function cargaZonas(){
                $("#zona").html(""); 
                zonas_temp = '';
                $.ajax({
                    type: 'POST',
                    url: 'ajax/zonas.php', 
                    data: {municipio: $("#ciudad").val(),
                            dem: $("#demarc option:selected").val() /*$("#demarcacion"+id_adicional+" option:selected").text()*/},
                    success: function(data){
                        if(data == "x" || data == "0"){
                            if($("#demarc option:selected").val() == '0'){
                                $("#zona").append('<option value="0">- Zona -</option>');   
                            } else{
                            $("#zona").append('<option value="0">Sin zonas asignadas</option>');}
                            $('#zonas').val('0,');
                        } else {
                            zonas = parseInt(data);
                            $("#zona").append('<option value="0">- Zona -</option>');
                            for(i = 1; i <= zonas; i++){
                                <?php if($usr_nivel == "7" || $usr_nivel == "4" || $usr_nivel == "6"):?>
                                    if(i == <?php echo $usr_zona;?>){
                                        $("#zona").append('<option value="'+i+'">'+i+'</option>');
                                        zonas_temp = zonas_temp + i + ',';       
                                    }
                                <?php else:?>
                                    $("#zona").append('<option value="'+i+'">'+i+'</option>');
                                        zonas_temp = zonas_temp + i + ',';  
                                <?php endif;?>
                                
                            }
                            $('#zonas').val(zonas_temp);
                        }
                    }
                });   
                
            }
            
            cargaZonas();
            
        });
        
        $('#zona').on('change', function(){
            $('#secc').html('<option value="0">- Sección -</option>');
            //$('#promotor').html('<option value="0">- Promotor2 -</option>');
            promotores_carga();
            seccs_temp = "";
            m = $('#ciudad').val();
            d = $('#demarc option:selected').val();
            z = $('#zona option:selected').val();
            $.post("ajax/secciones.php", {mun: m, dem: d, zona: z }, function(data){
                json = jQuery.parseJSON(data);
                for(i = 0; i< json.length; i++){
                    $('#secc').append($('<option>', {
                        value: json[i]['secc'],
                        text: json[i]['secc']
                    }));
                    seccs_temp = seccs_temp + json[i]['secc'] + ",";
                }
                $('#seccs').val(seccs_temp);
            });
        });
        
        /*Cargar promotores de la seccion seleccionada*/
        //$('#secc').on('change', function(){
        function promotores_carga(){
            
            
            
            <?php if($usr_nivel == 1 || $usr_nivel == 2 || $usr_nivel == 3 || $usr_nivel == 5):?>
                
                $('#promotor').html('<option value="0">- Promotor -</option>');
            
                promotores_temp = '<option value="0">- Promotor -</option>';
            
                //checar variables que se enviam deben ser numeros en mun y dem
                <?php if($usr_mun == 0):?>
            
            
            
                    municipio = $('#ciudad').val();
                    mun_prom = $.post("ajax/municipios_id.php", {municipio: municipio}, function(data){
                        mun_prom = data;
                        dem_prom = $('#demarc').val();
                        secc_prom = $('#secc').val();
                        zona_prom = <?php
                            if($usr_nivel == '5'){
                                echo '0';
                            } else {
                                echo $usr_zona;
                            }
                            
                            ?>;//a1
                        $.post("ajax/promotores.php", {mun: mun_prom, dem: dem_prom, zona: zona_prom}, function(data){
                            
                            json = jQuery.parseJSON(data);
                            for(i = 0; i< json.length; i++){
                                $('#promotor').append($('<option>', {
                                    value: json[i]['id_usuario'],
                                    text: json[i]['nombre']
                                }));
                                promotores_temp = promotores_temp + '<option value="'+json[i]['id_usuario']+'">'+json[i]['nombre']+'</option>';
                                
                            }
                            $('#promotores').val(promotores_temp);
                        });
                    });
            
            
            
                <?php else:?>
            
            
            
                    mun_prom = <?php echo $usr_mun?>;
                    dem_prom = $('#demarc').val();
                    secc_prom = $('#secc').val();
                    
                    if($('#zona').val() == '0'){
                        zona_prom = <?php
                            if($usr_nivel == '5'){
                                echo '0';
                            } else {
                                echo $usr_zona;
                            }
                            
                            ?>;
                    } else {
                        zona_prom = $('#zona').val();
                    }
            
                    $.post("ajax/promotores.php", {mun: mun_prom, dem: dem_prom, zona: zona_prom }, function(data){
                        
                        json = jQuery.parseJSON(data);
                        for(i = 0; i< json.length; i++){
                            $('#promotor').append($('<option>', {
                                value: json[i]['id_usuario'],
                                text: json[i]['nombre']
                            }));
                            promotores_temp = promotores_temp + '<option value="'+json[i]['id_usuario']+'">'+json[i]['nombre']+'</option>';
                        }
                        $('#promotores').val(promotores_temp);
                    });
            
            
            
                <?php endif;?>

            <?php else:?>
                <?php if($usr_nivel == 7 || $usr_nivel == 8):?>
                    
                <?php else:?>
                    $('#promotor').empty();
                    promotores_temp = '<option value="0">- Promotor4 -</option>';
                    mun_prom = <?php echo $usr_mun?>;
                    dem_prom = $('#demarc').val();
                    secc_prom = $('#secc').val();
                    zona_prom = <?php echo $usr_zona;?>;//a3

                    $.post("ajax/promotores.php", {mun: mun_prom, dem: dem_prom, zona: zona_prom }, function(data){
                        json = jQuery.parseJSON(data);

                        $('#promotor').append($('<option>', {
                                value: '0',
                                text: '- Promotor -'
                            }));
                        for(i = 0; i< json.length; i++){
                            $('#promotor').append($('<option>', {
                                value: json[i]['id_usuario'],
                                text: json[i]['nombre']
                            }));
                            promotores_temp = promotores_temp + '<option value="'+json[i]['id_usuario']+'">'+json[i]['nombre']+'</option>';
                        }
                        $('#promotores').val(promotores_temp);
                    });
                <?php endif;?>
            <?php endif;?>
            
            
        }//);
        
        
        $('#promotor').on('change', function(){
            cargaPromotores();
        });
        
        function cargaPromotores(){
            $('#promotores').val('');
            promotores_para_post = $('#promotores').val();
            $('#promotor option').each(function() {
                // Agrega el HTML de la opción a la variable string
                promotores_para_post += $(this)[0].outerHTML.replace(/\s+/g, ' ').trim();;
            });
            $('#promotores').val(promotores_para_post);
        }
        cargaPromotores();
        
        /*toggle campos de registro de personas entre afiliado y aspirante*/
        form_id = $('#form_id').val();
        if(form_id == 0){//ocultar forms primera carga de pagina
            $('#info_afinidad').hide();
            $('#info_aspirante').hide();
            $('#contenido_domicilio').hide();
            
            
        }
        if(form_id == 1){//mostrar form aspirante
            
            $('#form_id').val("1");
            
            $('#info_afinidad').show();
            $('#reg_aspirante').show();
            $('#contenido_domicilio').show();
            
            $('#reg_afiliado').hide();
            $('#info_aspirante').hide();
        }
        if(form_id == 2){
            $('#form_id').val("2");
            
            $('#info_aspirante').show();
            $('#reg_afiliado').show();
           $('#contenido_domicilio').show();
         
            $('#reg_aspirante').hide();
            $('#info_afinidad').hide();
            
        }

        /*resetea seccion al cambiar la direccion*/
        $('#domicilio').on('change', function(){
            $('#secc').html('<option value="0">- Sección -</option>');
            //$('#promotor').html('<option value="0">- PromotorM -</option>');
            //cargaPromotores();
            $('#zona').html('<option value="0">- Zona -</option>');
            $('#seccs').val('');
            $('#zonas').val('');
        });
        
        /*botones para elegir el tipo de formulario*/
        <?php if($usr_nivel != 8):?>
            <?php if($_SERVER['REQUEST_METHOD'] == 'POST' && $form_id != 0):?>
            $('#reg_afiliado').add($('#reg_aspirante')).css('padding','.75rem 1rem');
            $('#reg_afiliado').add($('#reg_aspirante')).css('font-size','1.3rem');
            $('#reg_afiliado').add($('#reg_aspirante')).css('margin-bottom', '0');
            $('#form0').html('Cambiar a registro de afiliado');
            <?php endif;?>

            <?php if($_SERVER['REQUEST_METHOD'] == 'POST' && $form_id != 0):?>

            $('#form1').html('Cambiar a registro de aspirante');
            <?php endif;?>

            $('#reg_afiliado').on('click', function(){
                $('#form1').html('Cambiar a registro de aspirante');
                $('#reg_aspirante').css('padding','.75rem 1rem');
                $('#reg_aspirante').css('font-size','1.3rem');
                $('.opciones_registro').css('margin-bottom', '0');

                $('#form_id').val("1");

                $('#info_afinidad').show();
                $('#reg_aspirante').fadeIn();
                $('#contenido_domicilio').show();

                $('#reg_afiliado').hide();
                $('#info_aspirante').hide();




            });

            $('#reg_aspirante').on('click', function(){
                $('#form0').html('Cambiar a registro de afiliado');
                $('#reg_afiliado').css('padding','.75rem 1rem');
                $('#reg_afiliado').css('font-size','1.3rem');
                $('.opciones_registro').css('margin-bottom', '0');

                $('#form_id').val("2");

                $('#info_aspirante').show();
                $('#reg_afiliado').fadeIn();
                $('#contenido_domicilio').show();

                $('#reg_aspirante').hide();
                $('#info_afinidad').hide();


            });
        <?php else:?>
            $('#info_afinidad').show();
            $('#contenido_domicilio').show();
            $('#info_aspirante').remove();
            $('#cve_elec_cont').remove();        
            $('#curp_cont').remove();        
            $('#fb_cont').remove();        
            $('#afiliacion_cont').remove();        
            $('.opciones_registro').remove();
            $('#form_id').val("1");
        <?php endif;?>
        ///////////////////////////////////////////////////
        
        if('<?php echo $usuario_registrado;?>' != 'no'){
            $('#register-form').prepend('<div class="register_success" id="register_success"><i class="fa-solid fa-check"></i><p>Registro guardado exitosamente</p></div>');
            

            //$('#register_success').delay(1500).fadeOut(1000);
        }
        
        
        
        
        
        
        
        
        /* MENU RESPONSIVE*/
        $('#hamb').on('click', function(){
           //alert(); 
            $('#ajuste-fixed').fadeIn();
            //$('#close_hamb').css('display', 'flex');
            $('#close_hamb').fadeIn();
            $('#close_hamb').css('display', 'flex');
        });
        
        $('#close_hamb').on('click', function(){
           //alert(); 
            $('#ajuste-fixed').fadeOut();
            $('#close_hamb').fadeOut();
        });
        
        
        
    });
    </script>
    
    
    <!-- AUTOCOMPLETAR CAMPOS A PARTIR DE DIRECCIÓN -->
    <script src="js/autocompleteAddress.js"></script>
    
    <script type="text/javascript">
        /*ABRIR OPCIONES PARA DOMICILIO RURAL*/
        $('#chk_rural').click(function() {
            if($('#chk_rural').prop('checked')) {
                $('#chk_rural_lbl').css("color", "#8B1232");
                $('#chk_rural_lbl').css("font-weight", "600");
                cp = $('#cp');
                cp.prop("readonly", false);
                $('#form__label__cp').text('Código Postal*');
                cp.off('keyup').keyup(function(){
                    
                    $('#form__error__cp').remove();
                    if(cp.val().length != 5){
                        
                        $('#form__div__cp').append($('<div>', {
                            class: 'form__error',
                            id: 'form__error__cp'
                        }));
                        
                        $('#form__error__cp').append($('<i>', {
                            class: 'fa-solid fa-triangle-exclamation'
                        }));
                        $('#form__error__cp').append($('<p>', {
                            class: 'error post',
                            id: 'error_p'
                        }));
                        $('#error_p').html('C.P. inválido');
                        
                    } else {
                        //CARGA COMBO COLONIAS A PARTIR DE CP
                        $('#form__colonia').html('<input type="text" name="cols_cp" id="cols_cp" placheholder=" " value="" style="display:none" readonly/><select class="form__input demarc" id="colonia_" name="colonia"></select><label class="form__label sexo_afiliacion" for="colonia_" id="colonia_lbl_">Colonia</label>');
                        
                        $.post("ajax/colonias_cp.php", { cp:cp.val() }, function(data){
                            
                            cols_cp_temp = '';
                            json = jQuery.parseJSON(data);
                            if(json.length == 0){
                               $('#colonia_').append($('<option>', {
                                    value: '0',
                                    text: '- Error en C.P. -'
                                }));
                            }
                            for(i = 0; i< json.length; i++){
                                $('#colonia_').append($('<option>', {
                                    value: json[i]['colonia'],
                                    text: json[i]['colonia']
                                }));
                                cols_cp_temp = cols_cp_temp + json[i]['colonia'] + ',';
                            }

                            $('#cols_cp').val(cols_cp_temp);
                        });
                        
                        //CARGA LOCALIDADES A PARTIR DE CP
                        $('#form__localidad').html('<input type="text" name="locs_cp" id="locs_cp" placheholder=" " value="" style="display:none" readonly/><select class="form__input localid" id="localidad_" name="localidad"></select><label class="form__label sexo_afiliacion" for="localidad_" id="localidad_lbl_">Localidad</label>');
                        
                        $.post("ajax/localidades_combo.php", { cp:cp.val() }, function(data){
                            
                            locs_cp_temp = '';
                            if(data == '[]'){
                                
                                
                                $('#form__localidad').html('<input placeholder=" " id="localidad" name="localidad" required="" class="form__input read" readonly="" value="Tepic"><label class="full-field form__label">Localidad</label>')
                                $('#ciudad').val('Tep');
                                $('#estado').val('Nayarit');
                                $('#pais').val('México');
                                
                                
                                $('#demarc').html('<option value="0">- Demarcación -</option>');
                                
                                $('#secc').html('<option value="0">- Sección -</option>');
                                dems_temp = "";
                                dems_val = $('#dems').val();
                                $.post("ajax/demarcaciones.php", {mun: $('#ciudad').val() }, function(data){
                                    
                                    json = jQuery.parseJSON(data);
                                        for(i = 0; i< json.length; i++){
                                            $('#demarc').append($('<option>', {
                                                value: json[i]['dem'],
                                                text: json[i]['dem']
                                            }));
                                            dems_temp = dems_temp + json[i]['dem'] + ",";
                                        }
                                        $('#dems').val(dems_temp);
                                });
                                
                            } else {
                            json = jQuery.parseJSON(data);}
                            for(i = 0; i< json.length; i++){
                                $('#localidad_').append($('<option>', {
                                    value: json[i]['localidad'],
                                    text: json[i]['localidad']
                                }));
                                locs_cp_temp = locs_cp_temp + json[i]['localidad'] + ',';
                            }

                            $('#locs_cp').val(locs_cp_temp);
                            $('#ciudad').val('Tepic');
                                $('#estado').val('Nayarit');
                                $('#pais').val('México');
                                
                                
                                
                                $('#secc').html('<option value="0">- Sección -</option>');
                                dems_temp = "";
                                dems_val = $('#dems').val();
                                $('#demarc').html('<option value="0">- Demarcación -</option>');
                                
                                //if(flag_ciudad_ajuste != 0){
                                $.post("ajax/demarcaciones.php", {mun: $('#ciudad').val() }, function(data){
                                    
                                    json = jQuery.parseJSON(data);
                                        for(i = 0; i< json.length; i++){
                                            $('#demarc').append($('<option>', {
                                                value: json[i]['dem'],
                                                text: json[i]['dem']
                                            }));
                                            dems_temp = dems_temp + json[i]['dem'] + ",";
                                        }
                                        $('#dems').val(dems_temp);
                                });//}
                        });
                        
                    }
                });
                
                /*if(con_colonia == 0){
                    console.log('sin colonia');
                }*/
                
                
            } else {
                $('#chk_rural_lbl').css("color", "#000000");
                $('#chk_rural_lbl').css("font-weight", "400");
                $('#form__label__cp').text('Código Postal');
                
                $('#cols_cp').remove();
                $('#colonia_').remove();
                $('#colonia_lbl_').remove();
                $('#colonia_').find('option').remove();
                $('#form__colonia').html('<input placeholder=" " id="colonia" name="colonia" required="" class="form__input read" readonly="" value=""><label class="full-field form__label">Colonia</label>');
                $('#cp').attr("readonly", true);
                
                $('#form__localidad').html('<input placeholder=" " id="localidad" name="localidad" required="" class="form__input read" readonly="" value=""><label class="full-field form__label">Localidad</label>');
            }
        });
    </script>
    
    
    <script type="application/javascript" src="js/popup_usuario.js"></script>

</body>
</html>