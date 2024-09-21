<?php
$session_lifetime = 3600 * 24 * 4; // 4 days
session_set_cookie_params ($session_lifetime);
session_start();
require 'conexion_db.php';
require 'functions.php';
comprobar_login();
comprobar_nivel();
require "includes/session.php";
$_SESSION['usuario_registrado'] = 'no';

$statement = $con->prepare('SELECT COUNT(*) FROM usuario');
$statement->execute();
$total_registros = $statement->fetch();
$limit = 20;
$total_paginas = ceil($total_registros[0] / $limit);

$statement = $con->prepare('SELECT * FROM usuario');
$statement->execute();
$usuarios = $statement->fetchAll();

//SWITCH PARA RELLENAR DATOS DE COMBOS EXTRA
switch($usr_nivel){
    case "1":
    case "2":
        $extra_sql_mun = 'SELECT * FROM municipio';
        $extra_sql_dem = 'SELECT DISTINCT dem FROM dem_secc WHERE id_municipio = "18"';
        break;
    case "3":
        $extra_sql_mun = 'SELECT * FROM municipio WHERE id_municipio = "'.$usr_mun.'"';
        $extra_sql_dem = 'SELECT DISTINCT dem FROM dem_secc WHERE id_municipio = "'.$usr_mun.'"';
        break;
    case "4":
        //municipios
        $extra_sql_mun = 'SELECT * FROM municipio WHERE id_municipio = "'.$usr_mun.'"';
        //demarcaciones
        $extra_sql_dem = 'SELECT DISTINCT dem FROM dem_secc WHERE id_municipio = "'.$usr_mun.'"';
        //$extra_sql = ' INNER JOIN municipio m WHERE p.ciudad = m.municipio AND m.id_municipio =  "'.$usr_mun.'" AND p.demarcacion IN ('.$incoming_user_dems.') ';
        break;
    case "5":
    case "6":
    case "7":
        //municipios
        $extra_sql_mun = 'SELECT * FROM municipio WHERE id_municipio = "'.$usr_mun.'"';
        //demarcaciones
        $extra_sql_dem = 'SELECT DISTINCT dem FROM dem_secc WHERE id_municipio = "'.$usr_mun.'"';
        //$extra_sql = ' INNER JOIN municipio m WHERE p.ciudad = m.municipio AND m.id_municipio =  "'.$usr_mun.'" AND p.demarcacion IN ('.$incoming_user_dems.') ';
        break;
}
//municipios
$statement = $con->prepare($extra_sql_mun);
$statement->execute();
$municipios = $statement->fetchAll();
$append_option = "";
foreach($municipios as $municipio){
    if($municipio['id_municipio'] == '18'){
        $append_option = $append_option.'<option value="'.$municipio['id_municipio'].'" selected>'.$municipio['municipio'].'</option>';    
    } else{
        $append_option = $append_option.'<option value="'.$municipio['id_municipio'].'">'.$municipio['municipio'].'</option>';
    }
}

//demarcaciones mun Acaponeta
$statement = $con->prepare($extra_sql_dem);
$statement->execute();
$dems_aca = $statement->fetchAll();
$append_option_dems = $append_checkbox = "";
foreach($dems_aca as $dem_aca){
    $append_option_dems = $append_option_dems.'<option value="'.$dem_aca['dem'].'">'.$dem_aca['dem'].'</option>';
}



?>

<!DOCTYPE html>
<html lang="es-MX">
<head>
    <meta charset="UTF-8">
    <title>Usuarios</title>      
    
    <script src="https://polyfill.io/v3/polyfill.min.js?features=default"></script>
   
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <link rel="stylesheet" type="text/css" href="css/normalize.css">
    <link rel="stylesheet" type="text/css" href="css/base.css">
    <link rel="stylesheet" type="text/css" href="css/sidebar-menu.css">
    <link rel="stylesheet" type="text/css" href="css/form.css">
    <link rel="stylesheet" type="text/css" href="css/usuarios.css">
    
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
          <?php if(!( $usr_nivel == 4 || $usr_nivel == 5 || $usr_nivel == 6)): ?>
           <div class="agregar_usuario" id="agregar_usuario"> 
                <div class="cont_agregar">
                <div id="panel_add" class="panel_add">
                <i class="fa-solid fa-circle-xmark" id="close_info_add"></i>
                <form class="agregarUsuario" id="agregarUsuario" name="agregarUsuario" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST" enctype="multipart/form-data">
                   <div class="ajuste_adduser">
                       <div class="row" id="row">
                            <!--div class="cont_preview"-->
                                <div class="file-preview" id="file-preview">
                                    <img src="" alt="" id="file_pr">
                                </div>
                            <!--/div-->
                            <label id="foto-label" for="file-input">
                               <div class="over_label">
                                <i class="fa-solid fa-image"></i>
                                </div>
                            </label>
                            <input id="file-input" type="file" name="file" accept="image/*" style="display:none">

                       </div>


                        <div id="datos_usuario">
                            
                            
                                
                            
                            <div class="form__div" id="form__div__nombre_add">
                                <input type="text" class="form__input" placeholder=" " name="nombre" id="nombre_add" value="">
                                <label for="nombre_add" class="form__label">Nombre*</label>
                            </div>

                            <div class="form__div" id="form__div__aps_add">
                                <input type="text" class="form__input" placeholder=" " name="apellidos" id="aps_add" value="">
                                <label for="aps_add" class="form__label">Apellidos*</label>
                            </div>

                            <div class="form__div" id="form__div__user_add">
                                <input type="text" class="form__input" placeholder=" " name="user" id="userp_add" value="">
                                <label for="userp_add" class="form__label">Usuario*</label>
                            </div>


                            <div class="form__div" id="form__div__newpass_add">
                                <input type="password" class="form__input" placeholder=" " name="new_pass_add" id="pass_add" value="" >
                                <label for="pass_add" class="form__label">Contraseña*</label>
                            </div>

                            <div class="form__div" id="form__div__passcheck_add">
                                <input type="password" class="form__input" placeholder=" " name="pass_check_add" id="pass_check_add" value="">
                                <label for="pass_check_add" class="form__label">Confirmar contraseña*</label>
                            </div>      
                            
                            <div class="form__div">
                                <div class="combo combo-afiliacion_add">
                                    <select class="form__input filter__nivel filter " id="nivel_add" name="nivel_usuario">
                                     <?php if($usr_nivel == "1"):?>
                                      <option id="admin_add_combo" value="1">Administrador</option>
                                      <option id="superuser_add_combo" value="2">Super usuario</option>   
                                      <?php endif;?>
                                      <?php if($usr_nivel == "1" || $usr_nivel == "2"):?>
                                      <option id="supervisor_add_combo" value="3">Supervisor</option>   
                                      <?php endif;?>
                                      <?php if($usr_nivel == "1" || $usr_nivel == "2" || $usr_nivel == "3"):?>
                                      <option id="resp_dem_add_combo" value="5">Coordinador de demarcación</option>   
                                      <option id="resp_zona_add_combo" value="6">Coordinador de zona</option>   
                                      <option id="coord_dem_add_combo" value="4">Coordinador de secciones</option>  
                                      <?php endif;?>
                                      <option id="promo_add_combo" value="7">Promotor</option>   
                                      
                                      
                                      <?php if($usr_nivel == "1" || $usr_nivel == "2" || $usr_nivel == "3"):?>
                                      <option id="promo_add_combo" value="8">Promotor Especial</option>
                                      <?php endif;?>
                                      
                                    </select>
                                    <label class="form__label" for="nivel_add">Nivel de usuario</label>
                                </div>
                            </div>

                        </div>
                    </div>
                    <div class="btns_persona">
                        
                        <button id="submit_add" type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Agregar usuario
                        </button>
                        
                        <div id="delete_persona" class="form__button">
                            <div id="cancel_add">
                                 <i class="fa-solid fa-xmark"></i>

                                <p class="submit-txt">Cancelar</p>
                            </div>
                            
                        </div>                        
                        
                    </div>
                    
                </form>
           </div>
               </div>
            </div>
            <?php endif;?>
            
            <h2 class="form__title" id="form__title">Usuarios</h2>
            
            <?php if(!( $usr_nivel == 4 || $usr_nivel == 5 || $usr_nivel == 6)): ?>
            
            <div id="add_user" class="form__button add_user active">
                <i class="fa-solid fa-user-plus"></i>
            </div>
            <?php endif;?>
                
            <!--CONTROL-->
            <div class="tipos_usuarios">
                
                <?php if($usr_nivel == "1"):?>
                <div id="admin" class="form__button user_type active">
                    <p>Administradores</p>
                </div>
                <?php endif;?>
                
                <?php if($usr_nivel == "1"):?>
                <div id="super_user" class="form__button user_type">
                    <p>Super Usuarios</p>
                </div>
                <?php endif;?>
                
                <?php if($usr_nivel == "1" || $usr_nivel == "2"):?>
                <div id="supervisor" class="form__button user_type">
                    <p>Supervisores</p>
                </div>
                <?php endif;?>
                
                <?php if($usr_nivel == "1" || $usr_nivel == "2" || $usr_nivel == "3"):?>
                <div id="resp_dem" class="form__button user_type">
                    <p>Coordinadores de Demarcación</p>
                </div>
                <?php endif;?>
                
                <?php if($usr_nivel == "1" || $usr_nivel == "2" || $usr_nivel == "3" || $usr_nivel == "5"):?>
                <div id="resp_zona" class="form__button user_type">
                    <p>Coordinadores de Zona</p>
                </div>
                <?php endif;?>
                
                <?php if($usr_nivel == "1" || $usr_nivel == "2" || $usr_nivel == "3" || $usr_nivel == "6" || $usr_nivel == "5"):?>
                <div id="coord_dem" class="form__button user_type">
                    <p>Coordinadores de Sección</p>
                </div>
                <?php endif;?>
                
                <div id="promo" class="form__button user_type">
                    <p>Promotores</p>
                </div>
                
            </div>
            
            <!--LISTA DE USUARIOS-->
            <div class="usuarios" id="usuarios">
                
            </div>
            
            
            <!--PAGINADO-->
            <div class="paginado" align="center">
                <ul class="pagination text-center" id="pagination">
               
                </ul>
            </div>       
            
            <div class="info_persona" id="info_persona">
                <div class="cont_info">
                    <i class="fa-solid fa-circle-xmark" id="close_info"></i>
                    <form class="editarUsuario" id="editarUsuario" name="editarUsuario" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST" enctype="multipart/form-data">
                    <input type="text" class="form__input" placeholder=" " name="usuario" id="id_editar" readonly style="display:none">
                    <div id="datos_persona">
                        <div class="form__div" id="form__div__nombre">
                            <input type="text" class="form__input" placeholder=" " name="nombre" id="nombre" value="">
                            <label for="nombre" class="form__label">Nombre*</label>
                        </div>
                        
                        <div class="form__div" id="form__div__aps">
                            <input type="text" class="form__input" placeholder=" " name="apellidos" id="aps" value="">
                            <label for="aps" class="form__label">Apellidos*</label>
                        </div>
                        
                        <div class="form__div" id="form__div__user">
                            <input type="text" class="form__input" placeholder=" " name="user" id="userp" value="">
                            <label for="userp" class="form__label">Usuario*</label>
                        </div>
                        
                        <div id="change_pass" class="">
                            <p>Cambiar contraseña</p>
                            
                        </div>
                        
                        
                        
                        <input style="display: none" type="number" class="form__input" placeholder=" " name="flag_pass" id="pass_flag" value="0" readonly>
                        
                        <div class="form__div" id="form__div__oldpass">
                            <input type="password" class="form__input" placeholder=" " name="old_pass" id="old_pass" value="">
                            <label for="old_pass" class="form__label">Contraseña actual*</label>
                        </div>
                        
                        <div class="form__div" id="form__div__newpass">
                            <input type="password" class="form__input" placeholder=" " name="new_pass" id="new_pass" value="">
                            <label for="new_pass" class="form__label">Nueva contraseña*</label>
                        </div>
                        
                        <div class="form__div" id="form__div__newpasscheck">
                            <input type="password" class="form__input" placeholder=" " name="pass_check" id="pass_check" value="">
                            <label for="pass_check" class="form__label">Confirmar nueva contraseña*</label>
                        </div>
                        
                        <div class="form__div">
                            <div class="combo combo-afiliacion">
                                <select class="form__input filter__nivel filter" id="nivel" name="nivel_usuario">
                                  <option value="1">Administrador</option>
                                  <option value="2">Super usuario</option>   
                                  <option value="3">Supervisor</option>   
                                  <option value="5">Coordinador de Demarcación </option>   
                                  <option value="6">Coordinador de zona</option>   
                                  <option value="4">Coordinador de secciones</option>   
                                  <option value="7">Promotor</option>   
                                </select>
                                <label class="form__label" for="nivel">Nivel de usuario</label>
                            </div>
                        </div>
                        
                        
                    </div>
                    
                    <div class="btns_persona">
                        
                        <button id="submit" type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Guardar cambios
                        </button>
                        
                        <div id="delete_persona" class="form__button">
                            <div id="delete_default">
                                 <i class="fa-solid fa-trash"></i>

                                <p class="submit-txt">Borrar usuario</p>
                            </div>
                            
                            <div id="delete_confirmar">
                                <p>Confirmar </p>
                                <i class="fa-sharp fa-solid fa-square-xmark" id="cancelar_borrar"></i>
                                <i class="fa-sharp fa-solid fa-square-check" id="confirmar_borrar"></i>
                            </div>
                            
                        </div>
                        
                        <div id="cancel_persona" class="form__button">
                            <div id="cancel_default">
                                 <i class="fa-sharp fa-solid fa-xmark"></i>

                                <p class="submit-txt">Cancelar</p>
                            </div>
                            
                        </div>
                        
                        
                        
                        
                        
                        
                    </div>
                    
                    </form>
                    
                </div>
            </div>
            
        </div>
    </div>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.27.0/moment.min.js"></script>
    
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.3/jquery.min.js"></script>
    
    <script type="text/javascript">
        //SCRIPT AGREGAR USUARIO
        //cargar thumbnail de foto
        $('#file-input').change(function(e) {
            if(document.getElementById("file-input").files.length != 0 ){
            if(typeof FileReader == "undefined") return true;

            var elem = $(this);
            var files = e.target.files;

            for (var i = 0, f; f = files[i]; i++) {
                if (f.type.match('image.*')) {
                    var reader = new FileReader();
                    reader.onload = (function(theFile) {
                        return function(e) {
                            var image = e.target.result;
                            $('#file_pr').attr('src',image);
                            $('#row').attr('style','background:none');
                            $('#foto-label').html("");
                        };
                    })(f);
                    reader.readAsDataURL(f);
                }
            }} else {
                $('#file_pr').attr('src','');
                $('#row').attr('style','background:#c9c9c9');
                $('#foto-label').html('<div class="over_label"><i class="fa-solid fa-image"></i></div>');
            }
        });
        
        //marcar errores en los input
        function marcaErrores(tipo_error, divPadre, text){

            if(tipo_error == 1){

                divPadre.append('<div class="form__error e_vacios" id="error__vacio"><i class="fa-solid fa-triangle-exclamation"></i> <p class="error post">Ingrese '+text+'</p></div>')
            }
            if(tipo_error == 2){

                divPadre.append('<div class="form__error pass_minima" id="error__minimo"><i class="fa-solid fa-triangle-exclamation"></i> <p class="error post">La '+text+' debe contener mínimo 8 caracteres</p></div>')
            }
            if(tipo_error == 4){

                divPadre.append('<div class="form__error solo_texto" id="error__valido"><i class="fa-solid fa-triangle-exclamation"></i> <p class="error post">Ingrese '+text+' válidos</p></div>')
            }
            if(tipo_error == 5){

                divPadre.append('<div class="form__error pass_antigua" id="error__old"><i class="fa-solid fa-triangle-exclamation"></i> <p class="error post">La '+text+' actual no es correcta</p></div>')
            }
            if(tipo_error == 6){

                divPadre.append('<div class="form__error pass_antigua" id="error__match"><i class="fa-solid fa-triangle-exclamation"></i> <p class="error post">Las '+text+'s no coinciden</p></div>')
            }
            if(tipo_error == 7){

                divPadre.append('<div class="form__error user_minimo" id="error__minuser"><i class="fa-solid fa-triangle-exclamation"></i> <p class="error post">Mínimo 6 caracteres</p></div>')
            }
            if(tipo_error == 8){

                divPadre.append('<div class="form__error user_repetido" id="error__minuser"><i class="fa-solid fa-triangle-exclamation"></i> <p class="error post">El usuario ya existe</p></div>')
            }
            if(tipo_error == 9){

                divPadre.append('<div class="form__error user_repetido" id="error__minuser"><i class="fa-solid fa-triangle-exclamation"></i> <p class="error post">Ingrese al menos una sección</p></div>')
            }
            if(tipo_error == 10){
               divPadre.append('<div class="form__error user_repetido" id="error__minuser"><i class="fa-solid fa-triangle-exclamation"></i> <p class="error post">Sin zonas asignadas. Primero asigne zonas desde el Coordinador de demarcación</p></div>') 
            }


        }
        
        //agregar mun/dem/secc acorde al nivel de usuario
        nivel_usuario_nuevo = $('#nivel_add option:selected').val();
        function remove_options(id_adicional){
            $('#combo_municipio'+id_adicional).remove();
            $('#combo_demarcacion'+id_adicional).remove();
            $('#combo_zona'+id_adicional).remove();
            $('#input_zonas'+id_adicional).remove();
            $('#label__resp__dems'+id_adicional).remove();
            $('#cont_dems_resp'+id_adicional).remove();
            $('#demarcaciones_responsable'+id_adicional).remove();
            $('#combo_seccion'+id_adicional).remove();
        }
        
        function add_user_options(divPadre, nivel_usuario_nuevo, id_adicional){
            append_option = "";
            id_dem = 1;
            dem_tmp = "1";
            secc_tmp = "0";
            function cargaZonas(){
                $("#zona"+id_adicional).html("");        
                $.ajax({
                    type: 'POST',
                    url: 'ajax/zonas.php', 
                    data: {mun: $("#municipio"+id_adicional+" option:selected").val(),
                            dem: dem_tmp/*$("#demarcacion"+id_adicional+" option:selected").text()*/},
                    async: false,
                    success: function(data){
                        if(data == "x" || data == "0"){
                            $("#zona"+id_adicional).append('<option value="0">Sin zonas asignadas</option>');
                            secc_tmp = "0";
                        } else {
                            zonas = parseInt(data);
                            for(i = 1; i <= zonas; i++){
                                $("#zona"+id_adicional).append('<option value="'+i+'">'+i+'</option>');
                                secc_tmp = "1";
                            }
                        }
                    }
                });   
                
            }
            function cargaSecciones(){
                $.post("ajax/secciones.php", {mun: $("#municipio"+id_adicional+" option:selected").text(), dem: $("#demarcacion"+id_adicional+" option:selected").text(), zona: $("#zona"+id_adicional+" option:selected").text()}, function(data){
                     valor = $("#zona"+id_adicional+" option:selected").val();
                     if(valor == "0"){
                         $('#seccion'+id_adicional).html('<option value="0">Sin zonas asignadas</option>');

                     } else {
                         $('#seccion'+id_adicional).html('');
                         json = jQuery.parseJSON(data);
                         for(i = 0; i < json.length; i++){
                             $('#seccion'+id_adicional).append($('<option>', {
                                 value: json[i]['secc'],
                                 text: json[i]['secc']
                             }));
                         }
                     }

                }); 
            }
            
            function cargaMun(){
                divPadre.append('<div class="form__div" id="combo_municipio'+id_adicional+'"><div class="combo combo-municipio'+id_adicional+'"><select class="form__input municipio_agregar" id="municipio'+id_adicional+'" name="municipio'+id_adicional+'">'+append_option+'</select><label class="form__label" for="municipio'+id_adicional+'">Municipio asignado</label></div></div>');
            }
            
            function cargaDem(){
                divPadre.append('<div class="form__div" id="combo_demarcacion'+id_adicional+'"><div class="combo combo-dems'+id_adicional+'"><select class="form__input demarcacion_agregar" id="demarcacion'+id_adicional+'" name="demarcacion'+id_adicional+'">'+append_option_dems_b+'</select><label class="form__label" for="demarcacion'+id_adicional+'">Demarcación asignada</label></div></div>');
            }
            
            function cargaZona(){
                divPadre.append('<div class="form__div" id="combo_zona'+id_adicional+'"><div class="combo combo-zona'+id_adicional+'"><select class="form__input zona_agregar" id="zona'+id_adicional+'" name="zona'+id_adicional+'"></select><label class="form__label" for="zona'+id_adicional+'">Zona asignada</label></div></div>');
                
                
            }
            //nivel_usuario_nuevo = $('#nivel_add option:selected').val();
            switch(nivel_usuario_nuevo){
                case "1":
                case "2":
                    remove_options("_add");
                    remove_options("_change");
                    break;
                case "3":
                case "8":    
                    
                    remove_options("_add");
                    remove_options("_change");
                    append_option = '<?php echo $append_option; ?>';
                    
                    cargaMun();
                    
                    break;
                case "4":
                    remove_options("_add");
                    remove_options("_change");
                    append_option = '<?php echo $append_option; ?>';
                    <?php
                    
                    ?>
                    
                    append_option = '<?php echo $append_option; ?>';
                    append_option_dems_b = '<?php echo $append_option_dems; ?>';
                    
                    append_checkbox = '<?php echo $append_checkbox; ?>';
                    
                    //carga combo boxes
                    cargaMun();
                    cargaDem();
                    cargaZona();
                    
                    //carga checklist seccion
                    divPadre.append('<label class="label__resp__dems" id="label__resp__dems'+id_adicional+'">Secciones por asignar</label><div class="cont_dems_responsable'+id_adicional+'" id="cont_dems_resp'+id_adicional+'"></div>');
                    
                    
                   
                    
                    cargaZonas();
                    $('#municipio'+id_adicional).on('change', function () {
                        
                        $.post("ajax/demarcaciones.php", {mun: $("#municipio"+id_adicional+" option:selected").text()}, function(data){
                            $('#demarcacion'+id_adicional).html('');
                            json = jQuery.parseJSON(data);
                            for(i = 0; i < json.length; i++){
                                $('#demarcacion'+id_adicional).append($('<option>', {
                                    value: json[i]['dem'],
                                    text: json[i]['dem']
                                }));
                            }
                        });  
                        dem_tmp = "1";
                        cargaZonas();
                        
                        
                        
                        load_dems_from_mun();   
                    });
                    
                    $('#demarcacion'+id_adicional).on('change', function(){
                        dem_tmp = $("#demarcacion"+id_adicional+" option:selected").text();
                        cargaZonas();
                        load_dems_from_mun();
                    });
                    
                    divPadre.append('<input type="text" class="form__input" placeholder=" " name="demarcaciones_responsable'+id_adicional+'" id="demarcaciones_responsable'+id_adicional+'" value="" readonly style="display:none">'); 
                    
                    function load_dems_from_mun(){

                        $('#demarcaciones_responsable'+id_adicional).val("");
                        
                        if(secc_tmp == "1"){
                        $.post("ajax/secciones.php", {mun: $("#municipio"+id_adicional+" option:selected").text(), dem: $("#demarcacion"+id_adicional+" option:selected").text(), zona: $("#zona"+id_adicional+" option:selected").text()}, function(data){
                            console.log(data);
                            $('#cont_dems_resp'+id_adicional).html('');
                            json = jQuery.parseJSON(data);
                            for(i = 0; i < json.length; i++){
                                $('#cont_dems_resp'+id_adicional).append($('<label>', {
                                    for: "dem_"+json[i]['secc']+id_adicional,
                                    id: "label_dem_"+json[i]['secc']+id_adicional,
                                    text:json[i]['secc'],
                                    class: "container",
                                    
                                }));
                                
                                
                                $('#label_dem_'+json[i]['secc']+id_adicional).append($('<input>', {
                                    type: "checkbox",
                                    id: "dem_"+json[i]['secc']+id_adicional,
                                    name: "dems_resp_t"+id_adicional,
                                    class: "dems_resp_t"+id_adicional,
                                    value: json[i]['secc'],
                                }));
                                
                                $('#label_dem_'+json[i]['secc']+id_adicional).append($('<span>', {
                                    id: "span_dem_"+json[i]['secc']+id_adicional,
                                    class: "checkmark",
                                }));
                                
                            }
                            
                            $('.dems_resp_t'+id_adicional).on('change', function(){
                                dems_resp_temp="";
                                $("input[name='dems_resp_t"+id_adicional+"']:checked").each(function() {
                                    dems_resp_temp += this.value+",";
                                });
                                $('#demarcaciones_responsable'+id_adicional).val(dems_resp_temp);
                            });
                        }); } else {
                            $('#cont_dems_resp'+id_adicional).html('<p>Sin zonas asignadas</p>');
                        }
                    }
                    
                    
                    load_dems_from_mun();
                    
                    break;
                case "5":
                    append_option = '<?php echo $append_option; ?>';
                    append_option_dems_b = '<?php echo $append_option_dems; ?>';
                    
                    remove_options("_add");
                    remove_options("_change");
                    
                    //carga combo boxes
                    cargaMun();
                    cargaDem();
                    
                    //carga input numérico para establecer zonas de la dem
                    divPadre.append('<div class="form__div" id="input_zonas'+id_adicional+'"><div class="combo combo-dems'+id_adicional+'"><input type="number" class="form__input demarcacion_agregar" id="zonas'+id_adicional+'" name="zonas'+id_adicional+'"><label class="form__label" for="zonas'+id_adicional+'">Zonas en la demarcación</label></div></div>');
                    
                    $('#municipio'+id_adicional).on('change', function () {
                        $.post("ajax/demarcaciones.php", {mun: $("#municipio"+id_adicional+" option:selected").text()}, function(data){
                            $('#demarcacion'+id_adicional).html('');
                            json = jQuery.parseJSON(data);
                            for(i = 0; i < json.length; i++){
                                $('#demarcacion'+id_adicional).append($('<option>', {
                                    value: json[i]['dem'],
                                    text: json[i]['dem']
                                }));
                            }
                        });   
                        
                    });
                    
                    break;
                case "6":
                    append_option = '<?php echo $append_option; ?>';
                    append_option_dems_b = '<?php echo $append_option_dems; ?>';
                    
                    remove_options("_add");
                    remove_options("_change");
                    
                    //carga combo boxes
                    cargaMun();
                    cargaDem();
                    cargaZona();
                    
                    //carga zonas asignadas a la demarcacion del combo
                    cargaZonas();
                    
                    $('#municipio'+id_adicional).on('change', function () {
                        $.post("ajax/demarcaciones.php", {mun: $("#municipio"+id_adicional+" option:selected").text()}, function(data){
                            $('#demarcacion'+id_adicional).html('');
                            json = jQuery.parseJSON(data);
                            for(i = 0; i < json.length; i++){
                                $('#demarcacion'+id_adicional).append($('<option>', {
                                    value: json[i]['dem'],
                                    text: json[i]['dem']
                                }));
                            }
                        });    
                        dem_tmp = "1";
                        cargaZonas();
                        
                    });
                    
                    $('#demarcacion'+id_adicional).on('change', function(){
                        dem_tmp = $("#demarcacion"+id_adicional+" option:selected").text();
                        cargaZonas();
                    });
                    
                    break;
                case "7":
                
                    append_option = '<?php echo $append_option; ?>';
                    append_option_dems_b = '<?php echo $append_option_dems; ?>';
                    
                    remove_options("_add");
                    remove_options("_change");
                    
                    //carga combo boxes
                    cargaMun();
                    cargaDem();
                    cargaZona();
                    
                    //carga combo seccion
                    /*divPadre.append('<div class="form__div" id="combo_seccion'+id_adicional+'"><div class="combo combo-seccion'+id_adicional+'"><select class="form__input seccion_agregar" id="seccion'+id_adicional+'" name="seccion'+id_adicional+'"></select><label class="form__label" for="seccion'+id_adicional+'">Sección asignada</label></div></div>');*/
                    
                    //carga zonas y secciones asignadas a la demarcacion del combo
                    cargaZonas();
                    cargaSecciones();
                    
                    $('#municipio'+id_adicional).on('change', function () {
                        $.post("ajax/demarcaciones.php", {mun: $("#municipio"+id_adicional+" option:selected").text()}, function(data){
                            $('#demarcacion'+id_adicional).html('');
                            json = jQuery.parseJSON(data);
                            for(i = 0; i < json.length; i++){
                                $('#demarcacion'+id_adicional).append($('<option>', {
                                    value: json[i]['dem'],
                                    text: json[i]['dem']
                                }));
                            }
                        });    
                        dem_tmp = "1";
                        cargaZonas();
                        cargaSecciones();
                    });
                    
                    //CREAR ZONAS DINÁMICAS Y SECCIONES QUE SEA UN COMBO PARA SOLO ELEGIR UNA
                    $('#demarcacion'+id_adicional).on('change', function(){
                        dem_tmp = $("#demarcacion"+id_adicional+" option:selected").text();
                        cargaZonas();
                        cargaSecciones();
                    });
                    
                    
                    break;
                default:
                    remove_options("_add");
                    remove_options("_change");
            }
        }
        
        add_user_options($('#datos_usuario'), $('#nivel_add option:selected').val(), "_add");
        //add_user_options($('#datos_persona'), $('#nivel option:selected').val(), "_change");
        
        $('#nivel_add').on('change', function(){
            
            add_user_options($('#datos_usuario'), $('#nivel_add option:selected').val(), "_add");
            
        });
        
        $('#nivel').on('change', function(){
            add_user_options($('#datos_persona'), $('#nivel option:selected').val(), "_change");
        });
        
        
        
    </script>
    <script type="text/javascript">
        
        
        $('#menu_usuarios').addClass("menu_seleccion");
        
        $('.user_type').click(function(){
            cargaUsuarios(1, this.id);
        });
        
        <?php
        switch($usr_nivel){
            case "1":
                $nivel_menu = 'admin';
                break;
            case "2":
                $nivel_menu = 'supervisor';
                break;
            case "3":
                $nivel_menu = 'resp_dem';
                break;
            case "4":
                $nivel_menu = 'promo';
                break;  
            case "5":
                $nivel_menu = 'resp_zona';
                break;
            case "6":
                $nivel_menu = 'coord_dem';
                break;
        }
        ?>
        op_inicial = "<?php echo $nivel_menu;?>";
        cargaUsuarios(1, op_inicial);
        function cargaUsuarios(page, id){
            $('#form__div__oldpass').hide();
            $('#form__div__newpass').hide();
            $('#form__div__newpasscheck').hide();
            
            $.post("ajax/usuarios_lista.php",{ pagina: page, id: id, limit: <?php echo $limit; ?> }, function(data){
                $('#usuarios').html(data);
                //Resalta el boton de los usuarios visualizados
                $('.user_type').removeClass("active");
                if($('#usuario_activo').text() == "admin"){$('#admin').addClass("active");}
                if($('#usuario_activo').text() == "super_user"){$('#super_user').addClass("active");}
                if($('#usuario_activo').text() == "supervisor"){$('#supervisor').addClass("active");}
                if($('#usuario_activo').text() == "coord_dem"){$('#coord_dem').addClass("active");}
                if($('#usuario_activo').text() == "resp_dem"){$('#resp_dem').addClass("active");}
                if($('#usuario_activo').text() == "resp_zona"){$('#resp_zona').addClass("active");}
                if($('#usuario_activo').text() == "promo"){$('#promo').addClass("active");}
                
                //paginado dinamico
                personas_filtro = parseInt($('#total_usuarios_filtro').text());
                pagina_activa = parseInt($('#pagina_activa').text());
                paginas = "";
                total_paginas_filtro = Math.ceil(personas_filtro / <?php echo $limit; ?>);
                for(i = 1; i <= total_paginas_filtro; i++){

                    if(i == pagina_activa){
                        paginas = paginas + '<li class="active pagina" id="' + i + '">' + i + '</li>';
                    } else {
                        paginas = paginas + '<li class="pagina" id="' + i + '">' + i + '</li>';
                    }
                }
                
                if(personas_filtro == 0){
                   $('#usuarios').html('<div class="filter__error"><i class="fa-solid fa-triangle-exclamation"></i><p>No se han encontrado usuarios</p></div>');
                }
                
                $('#pagination').html(paginas);
                
                
                $('.pagina').click(function(){
                    pagina = this.id;
                    id = $('.active')[1].id;
                    //console.log(id);
                    cargaUsuarios(pagina, id);
                });
                
                //Carga datos en el popup de usuario
                <?php if(!( $usr_nivel == 4 || $usr_nivel == 5 || $usr_nivel == 6)): ?>
                $('.user').click(function(){
                    $('.borrado_success').remove();
                    $('.form__success').remove();
                    $('#info_persona').fadeIn(300);
                    
                    $('#id_editar').val(this.id);
                    
                    $.post("ajax/usuario_unico.php",{ usuario: this.id}, function(data){
                        if(data == ""){
                            
                        }else{
                            <?php
                            $t_sql = 'SELECT municipio, dem, zona, seccion FROM usuario WHERE id_nivel_usuario = 5';
                            $statement = $con->prepare($t_sql);
                            $statement->execute();
                            $usrs_dems = $statement->fetchAll();
                            $resp = [];
                            foreach($usrs_dems as $usr_dem_x){
                                $t1 = [
                                    "mun" => $usr_dem_x['municipio'],
                                    "dem" => $usr_dem_x['dem'],
                                    "zona" => $usr_dem_x['zona'],
                                    "seccion" => $usr_dem_x['seccion']
                                ];
                                array_push($resp, $t1);
                            }

                            ?>
                            json = jQuery.parseJSON(data);
                            $('#nombre').val(json['nombre']);
                            $('#aps').val(json['apellidos']);
                            $('#userp').val(json['user']);
                            //$('#old_pass').val(json['pass']);
                            $('#nivel').val(json['id_nivel_usuario']);
                            switch($('#nivel option:selected').val()){
                                case "3":
                                case "8":
                                    add_user_options($('#datos_persona'), $('#nivel option:selected').val(), "_change");
                                    $('#municipio_change').val(json['id_mun']);
                                    break;
                                case "4":
                                    add_user_options($('#datos_persona'), $('#nivel option:selected').val(), "_change");
                                    $('#municipio_change').val(json['id_mun']);
                                    
                                    
                                    

                                    data_t = '<?php echo json_encode($resp)?>';
                                    json_t = jQuery.parseJSON(data_t);
                                    $('#zona_change').html('');
                                    for(i = 0; i < json_t.length; i++){
                                        if(json['id_mun'] == json_t[i]['mun'] && json['dem'] == json_t[i]['dem']){
                                            for(j = 1; j <= json_t[i]['zona']; j++){
                                                $('#zona_change').append($('<option>', {
                                                    value: j,
                                                    text: j
                                                }));
                                            }
                                        }
                                    }
 
                                    $('#demarcacion_change').val(json['dem']);
                                    $('#zona_change').val(json['zona']);
                                    $('#demarcaciones_responsable_change').val(json['dems_resp']);
                                    array_temp = json['dems_resp'].split(",");
                                    array_temp.pop();
                                    
                                    /****************************************************************/
                                    /* codigo para cargar demarcaciones existentes al editar usuario*/
                                    /****************************************************************/
                                    $.post("ajax/secciones.php", {mun: $("#municipio_change option:selected").text(), dem: $("#demarcacion_change option:selected").text(), zona: $("#zona"+id_adicional+" option:selected").text()}, function(data){
                                        
                                        $('#cont_dems_resp_change').html('');
                                        json = jQuery.parseJSON(data);
                                        
                                        for(i = 0; i < json.length; i++){
                                            $('#cont_dems_resp_change').append($('<label>', {
                                                for: "dem_"+json[i]['secc']+'_change',
                                                id: "label_dem_"+json[i]['secc']+'_change',
                                                text:json[i]['secc'],
                                                class: "container",
                                                
                                            }));

                                            $('#label_dem_'+json[i]['secc']+'_change').append($('<input>', {
                                                type: "checkbox",
                                                id: "dem_"+json[i]['secc']+'_change',
                                                name: "dems_resp_t"+'_change',
                                                class: "dems_resp_t"+'_change',
                                                value: json[i]['secc'],
                                            }));

                                            $('#label_dem_'+json[i]['secc']+'_change').append($('<span>', {
                                                id: "span_dem_"+json[i]['secc']+'_change',
                                                class: "checkmark",
                                            }));
                                            
                                            
                                        }
                                        
                                        $('.dems_resp_t_change').each(function(){
                                           for(i = 0; i < array_temp.length; i++){
                                               if(this.value == array_temp[i]){
                                                   $('#dem_'+this.value+'_change').prop('checked', true);
                                                   //$('.dems_resp_t_change')[i].checked = true;
                                                   //$('.dems_resp_t_change')[this.value-1].checked = true;
                                               }
                                           } 
                                        });

                                        $('.dems_resp_t_change').on('change', function(){
                                            dems_resp_temp="";
                                            $("input[name='dems_resp_t_change']:checked").each(function() {
                                                dems_resp_temp += this.value+",";
                                            });
                                            $('#demarcaciones_responsable_change').val(dems_resp_temp);
                                        });
                                    }); 
                                    /****************************************************************/
                                    /****************************************************************/
                                    /****************************************************************/
                                    
                                    
                                    break;
                                case "5":
                                    add_user_options($('#datos_persona'), $('#nivel option:selected').val(), "_change");
                                    
                                    data_t = '<?php echo json_encode($resp)?>';
                                    json_t = jQuery.parseJSON(data_t);
                                    $('#zona_change').html('');
                                    for(i = 0; i < json_t.length; i++){
                                        if(json['id_mun'] == json_t[i]['mun'] && json['dem'] == json_t[i]['dem']){
                                            for(j = 1; j <= json_t[i]['zona']; j++){
                                                $('#zona_change').append($('<option>', {
                                                    value: j,
                                                    text: j
                                                }));
                                            }
                                        }
                                    }
 
                                    $('#municipio_change').val(json['id_mun']);
                                    $('#demarcacion_change').val(json['dem']);
                                    $('#zonas_change').val(json['zona']);
                                    $('#demarcaciones_responsable_change').val(json['dems_resp']);
                                    break;
                                case "6":
                                    add_user_options($('#datos_persona'), $('#nivel option:selected').val(), "_change");

                                    data_t = '<?php echo json_encode($resp)?>';
                                    json_t = jQuery.parseJSON(data_t);
                                    $('#zona_change').html('');
                                    for(i = 0; i < json_t.length; i++){
                                        if(json['id_mun'] == json_t[i]['mun'] && json['dem'] == json_t[i]['dem']){
                                            for(j = 1; j <= json_t[i]['zona']; j++){
                                                $('#zona_change').append($('<option>', {
                                                    value: j,
                                                    text: j
                                                }));
                                            }
                                        }
                                    }
 
                                    $('#municipio_change').val(json['id_mun']);
                                    $('#demarcacion_change').val(json['dem']);
                                    $('#zona_change').val(json['zona']);
                                    $('#demarcaciones_responsable_change').val(json['dems_resp']);
                                    
                                    break;
                                case "7":
                                    add_user_options($('#datos_persona'), $('#nivel option:selected').val(), "_change");
                                    
                                    data_t = '<?php echo json_encode($resp)?>';
                                    json_t = jQuery.parseJSON(data_t);
                                    $('#zona_change').html('');
                                    for(i = 0; i < json_t.length; i++){
                                        if(json['id_mun'] == json_t[i]['mun'] && json['dem'] == json_t[i]['dem']){
                                            for(j = 1; j <= json_t[i]['zona']; j++){
                                                $('#zona_change').append($('<option>', {
                                                    value: j,
                                                    text: j
                                                }));
                                            }
                                        }
                                    }
 
                                    $('#municipio_change').val(json['id_mun']);
                                    $('#demarcacion_change').val(json['dem']);
                                    $('#zona_change').val(json['zona']);
                                    
                                    $.post("ajax/secciones.php", {mun: $("#municipio_change option:selected").text(), dem: $("#demarcacion_change option:selected").text(), zona: $("#zona"+id_adicional+" option:selected").text()}, function(data){
                     valor = $("#zona_change option:selected").val();
                     if(valor == "0"){
                         $('#seccion_change').html('<option value="0">Sin zonas asignadas</option>');

                     } else {
                         $('#seccion_change').html('');
                         json = jQuery.parseJSON(data);
                         for(i = 0; i < json.length; i++){
                             $('#seccion_change').append($('<option>', {
                                 value: json[i]['secc'],
                                 text: json[i]['secc']
                             }));
                         }
                     }

                });
                                    $('#seccion_change').val(json['seccion']);
                                    
                                    
                                    break;
                            }
                        }
                    });
                    
                });
                <?php endif;?>
                
                //cierra popup
                $('#close_info').add('#cancel_persona').click(function(){
                    $('#info_persona').fadeOut(300);
                    $('#pass_flag').val("0");
                    $('#form__div__oldpass').fadeOut(300);
                    $('#form__div__newpass').fadeOut(300);
                    $('#form__div__newpasscheck').fadeOut(300);
                    $('#change_pass').show();  
                    $('#old_pass').val("");
                    $('#new_pass').val("");
                    $('#pass_check').val("");
                    if(!$('#delete_default').is(':visible')){
                        $('#delete_default, #delete_confirmar').toggle(300);
                    }
                    $('.form__error').remove();

                });
                
                
                //toggle cambiar pass
                $('#change_pass').click(function(){
                    $('#change_pass').hide();
                    $('#pass_flag').val("1");
                    $('#form__div__oldpass').fadeIn(200);
                    $('#form__div__newpass').fadeIn(200);
                    $('#form__div__newpasscheck').fadeIn(200);
                });

               
                
            });
        }
           
                   
        //FORM EDITAR USUARIO
        $("#editarUsuario").submit(function(e) {
            e.preventDefault();

            usuario = $('#id_editar').val();
            nombre = $('#nombre').val();
            apellidos = $('#aps').val();
            user = $('#userp').val();
            old_pass = $('#old_pass').val();
            new_pass = $('#new_pass').val();
            pass_check = $('#pass_check').val();
            nivel_usuario = $('#nivel').val();
            flag_pass = $('#pass_flag').val();
                
            //var formData = new FormData(document.getElementById("editarUsuario"));
            formData = $("#editarUsuario").serialize();

                //{usuario: usuario, nombre: nombre, apellidos: apellidos, user: user, flag_pass: flag_pass, old_pass: old_pass, new_pass: new_pass, pass_check: pass_check, nivel_usuario: nivel_usuario}
                
            $.post("ajax/editar-usuario.php", formData, function(data){
                if(data != "200"){
                    $('#success_update').remove();
                    respuesta = jQuery.parseJSON(data);
                    $('.form__error').remove();
                    marcaErrores(respuesta['e_nom'], $('#form__div__nombre'), ' nombre(s)');
                    marcaErrores(respuesta['e_aps'], $('#form__div__aps'), 'apellidos');
                    marcaErrores(respuesta['e_user'], $('#form__div__user'), 'usuario');
                    marcaErrores(respuesta['e_new_pass'], $('#form__div__newpass'), 'contraseña');
                    marcaErrores(respuesta['e_old_pass'], $('#form__div__oldpass'), 'contraseña');
                    marcaErrores(respuesta['e_new_pass_check'], $('#form__div__newpasscheck'), 'contraseña');
                    marcaErrores(respuesta['e_dems_resp'], $('#datos_persona'), 'contraseña');
                    marcaErrores(respuesta['e_zonas'], $('#input_zonas_add'), 'zonas por asignar');
                    marcaErrores(respuesta['e_zonas'], $('#combo_zona_add'), '');
                    
                } else {
                    $('#error_update').remove();
                    $('#datos_persona').append('<div class="form__success respuesta_popup" id="success_update"><i class="fa-sharp fa-solid fa-thumbs-up success"></i> <p class="success post"> Datos actualizados</p></div>');
                    $('#success_update').hide();
                    $('#success_update').fadeIn(300);
                    $('#info_persona').delay(1500).fadeOut(300);
                    cargaUsuarios(1,$('.active')[1].id);
                    $('#change_pass').show(); 
                    $('.form__error').remove();
                }
            });

        });        
        
        //FORM AGREGAR USUARIO
        $('#agregarUsuario').submit(function(e){
            e.preventDefault();
            nombre = $('#nombre_add').val();
            apellidos = $('#aps_add').val();
            user = $('#userp_add').val();
            new_pass = $('#pass_add').val();
            pass_check = $('#pass_check_add').val();
            nivel_usuario = $('#nivel_add').val();
            //nivel_usuario_nuevo = $('#nivel_add option:selected').val();

            var formData = new FormData(document.getElementById("agregarUsuario"));

            $.ajax({
                url: "ajax/editar-usuario.php",
                type: "post",
                dataType: "html",
                data: formData,
                cache: false,
                contentType: false,
                processData: false
            }).done(function(data){
                if(data != "200"){
                    //console.log(data);
                    respuesta = jQuery.parseJSON(data);
                    $('.form__error').remove();
                    marcaErrores(respuesta['e_nom'], $('#form__div__nombre_add'), ' nombre(s)');
                    marcaErrores(respuesta['e_aps'], $('#form__div__aps_add'), 'apellidos');
                    marcaErrores(respuesta['e_user'], $('#form__div__user_add'), ' usuario');
                    marcaErrores(respuesta['e_new_pass'], $('#form__div__newpass_add'), 'contraseña');
                    marcaErrores(respuesta['e_new_pass_check'], $('#form__div__passcheck_add'), 'contraseña');
                    marcaErrores(respuesta['e_dems_resp'], $('#datos_usuario'), 'contraseña');
                    marcaErrores(respuesta['e_zonas'], $('#input_zonas_add'), 'zonas por asignar');
                    marcaErrores(respuesta['e_zonas'], $('#combo_zona_add'), '');

                } else {
                    //$('#error_update').remove();
                    
                    
                    $('#datos_usuario').append('<div class="form__success respuesta_popup" id="success_add"><i class="fa-sharp fa-solid fa-thumbs-up success"></i> <p class="success post">Usuario agregado</p></div>');
                    $('.form__input').val("");
                    $('#nivel_add').val(1);
                    $('.form__error').remove();
                    $('#file_pr').attr('src','');
                    $('#row').attr('style','background:#c9c9c9');
                    $('#foto-label').html('<div class="over_label"><i class="fa-solid fa-image"></i></div>');

                    $('#success_add').hide();
                    $('#success_add').fadeIn(300);

                    $('#agregar_usuario').delay(1500).fadeOut(300);
                    cargaUsuarios(1,$('.active')[1].id);

                }

            });

        });           
        
            
            
        //Confirmación para borrar registro
        $('#delete_confirmar').hide();
        $('#delete_default, #cancelar_borrar').click(function(){
            $('#delete_default, #delete_confirmar').toggle(300);
        });
        
        ///////////////////////////////////////////////////////////////////////   
        ///////////////////////////////////////////////////////////////////////   
        ///////////////////////////////////////////////////////////////////////   
        ///////////////////////////////////////////////////////////////////////   
        ///////////////////////////////////////////////////////////////////////   
        ///////////////////////////////////////////////////////////////////////   
        ///////////////////////////////////////////////////////////////////////   
        $('#info_persona').hide();
            
        $('#agregar_usuario').hide();
        $('#add_user').click(function(){
            $('.form__success').remove();
            $('#nivel_add option:eq(0)').prop('selected', true);
            $('#agregar_usuario').fadeIn(300);
            add_user_options($('#datos_usuario'), $('#nivel_add option:selected').val(), "_add");
            
        });
        $('#close_info_add, #cancel_add').click(function(){
            $('#agregar_usuario').fadeOut(300);
            $('#success_add').hide();
            $('.form__input').val("");
            $('#nivel_add').val(1);
            $('.form__error').remove();
            $('#file_pr').attr('src','');
            $('#row').attr('style','background:#c9c9c9');
            $('#foto-label').html('<div class="over_label"><i class="fa-solid fa-image"></i></div>');
            remove_options();
        });
        
        
            
        $('#confirmar_borrar').click(function(){
            borrarRegistro($('#id_editar').val(),$('.active')[1].id);
        });

        function borrarRegistro(usuario, tipo_usuario){

            $.post("ajax/usuario_borrar.php", {usuario: usuario}, function(data){

                if(data == "Error"){
                    $('#datos_persona').append('<div class="borrado_error borrado respuesta_popup"><i class="fa-solid fa-triangle-exclamation"></i><p>Ups! Ocurrió un problema</p></div>');
                    //$('#info_persona').fadeOut(500);
                    //$('#notificacion_borrado').delay(3000).fadeOut(400);
                } else{
                    $('#datos_persona').append('<div class="borrado_success borrado respuesta_popup" id="success_delete"><i class="fa-solid fa-trash"></i><p>Usuario eliminado</p></div>');
                    $('#success_delete').hide();
                    $('#success_delete').fadeIn(300);

                    $('#info_persona').delay(1500).fadeOut(300);

                    cargaUsuarios(1,tipo_usuario);

                }
            });
        }
            
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
       
    </script>
    
    <script type="application/javascript" src="js/popup_usuario.js"></script>
    
    
</body>
</html>