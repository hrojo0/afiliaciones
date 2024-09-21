<?php
$session_lifetime = 3600 * 24 * 4; // 4 days
session_set_cookie_params ($session_lifetime);
session_start();
require 'conexion_db.php';
require 'functions.php';
comprobar_login();
require "includes/session.php";
$_SESSION['usuario_registrado'] = 'no';
/*carga datos de combo municipios y colonia*/
$statement = $con->prepare('SELECT id_municipio, municipio FROM municipio ORDER BY municipio');
$statement->execute();
$municipios = $statement->fetchAll();

$statement = $con->prepare('SELECT id_cp_colonia, colonia FROM cp_colonia ORDER BY colonia;');
$statement->execute();
$colonias = $statement->fetchAll();

//Datos base para paginado dependiendo del nivel de usuario
switch($usr_nivel){
    case "1":
    case "2":
        $sql_paginas = 'SELECT COUNT(*) FROM persona WHERE cve_elec = "0"';
        break;
    case "3":
        $sql_paginas = 'SELECT COUNT(*) FROM persona p INNER JOIN municipio m WHERE p.ciudad = m.municipio AND m.id_municipio =  "'.$usr_mun.'" AND cve_elec = "0"';
        break;
    case "5":
        $sql_paginas = 'SELECT COUNT(*) FROM persona p INNER JOIN municipio m WHERE p.ciudad = m.municipio AND m.id_municipio = "'.$usr_mun.'" AND p.demarcacion = "'.$usr_dem.'" AND cve_elec = "0"';
        break;
    case "6":
        $sql_paginas = 'SELECT COUNT(*) FROM persona p INNER JOIN municipio m WHERE p.ciudad = m.municipio AND m.id_municipio = "'.$usr_mun.'" AND p.demarcacion = "'.$usr_dem.'" AND zona = "'.$usr_zona.'" AND cve_elec = "0"';
        break;
    case "4": 
        $statement = $con ->prepare('SELECT demarcaciones FROM coordinacion_dems WHERE id_usuario = "'.$usr_usuario.'"');
        $statement->execute();
        $user_dems = $statement->fetch();
        $user_dems = $user_dems[0];
        $user_dems = substr($user_dems, 0 , -1);
        $sql_paginas = 'SELECT COUNT(*) FROM persona p INNER JOIN municipio m WHERE p.ciudad = m.municipio AND m.id_municipio =  "'.$usr_mun.'" AND p.seccion IN('.$user_dems.') AND cve_elec = "0"';
        break;
    case "7":   
        $sql_paginas = 'SELECT COUNT(*) FROM persona WHERE id_usuario = "'.$usr_usuario.'" AND cve_elec = "0"';
        break;
}
$statement = $con->prepare($sql_paginas);
$statement->execute();
$total_registros = $statement->fetch();
$limit = 50;
$total_paginas = ceil($total_registros[0] / $limit);
?>

<!DOCTYPE html>
<html lang="es-MX">
<head>
    <meta charset="UTF-8">
    <title>Aspirantes al proyecto</title>
    

    <script src="https://polyfill.io/v3/polyfill.min.js?features=default"></script>
   
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <link rel="stylesheet" type="text/css" href="css/normalize.css">
    <link rel="stylesheet" type="text/css" href="css/base.css">
    <link rel="stylesheet" type="text/css" href="css/form.css">
    <link rel="stylesheet" type="text/css" href="css/sidebar-menu.css">
    <link href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/v/dt/jqc-1.12.4/jszip-3.10.1/dt-2.0.1/b-3.0.0/b-colvis-3.0.0/b-html5-3.0.0/datatables.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="css/registro-ine.css">
    
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
            <h2 class="form__title" id="form__title">Aspirantes al Proyecto</h2>  
            <div class="form__div div__nombre">
                <input type="text" placeholder=" " id="nombre" name="nombre" class="form__input filter"/>
                <label class="full-field form__label label__nombre">Nombre Completo</label>
            </div>
            <div class="control">
                
                <div class="form__div">
                    <div class="combo combo-afiliacion">
                        <select class="form__input filter_age filter" id="edad" name="edad">
                            <option value="0">- Edad -</option>
                            <option value="1">Menor de edad</option>
                            <option value="18">De 18 a 24</option>
                            <option value="25">De 25 a 34</option>
                            <option value="35">De 35 a 44</option>
                            <option value="45">De 45 a 54</option>
                            <option value="55">De 55 a 64</option>
                            <option value="65">65 o más</option>
                        </select>
                        <label class="form__label" for="edad">Edad</label>
                    </div>
                </div>
                
                <div class="form__div">
                    <div class="combo combo-afiliacion">
                        <select class="form__input filter__sex filter" id="sexo" name="sexo">
                          <option value="0">- Sexo -</option>
                          <option value="1">Masculino</option>
                          <option value="2">Femenino</option>   
                        </select>
                        <label class="form__label" for="sexo">Sexo</label>
                    </div>
                </div>
                
                <div class="form__div">
                    <div class="combo combo-afiliacion">
                        <select class="form__input filter__afiliacion filter" id="afiliacion" name="afiliacion">
                          <option value="0">- Afiliado -</option>
                          <option value="1">Si</option>
                          <option value="2">No</option>   
                        </select>
                        <label class="form__label" for="afiliacion">Afiliado</label>
                    </div>
                </div>
                
                <div class="form__div">
                    <div class="combo combo-afiliacion">
                        <select class="form__input filter_municipio filter" id="municipio" name="municipio">
                            <option value="0">- Municipio -</option>
                            <?php foreach($municipios as $municipio):?>
                            <option value="<?php echo $municipio['id_municipio']; ?>"><?php echo $municipio['municipio'] ?></option>   
                            <?php endforeach;?>
                        </select>
                        <label class="form__label" for="municipio">Municipio</label>
                    </div>
                </div>
                
                <div class="form__div">
                    <div class="combo combo-afiliacion">
                        <select class="form__input filter_municipio filter" id="demarcacion" name="demarcacion">
                            <option value="0">- Demarcación -</option>
                        </select>
                        <label class="form__label" for="municipio">Demarcación</label>
                    </div>
                </div>
                
                <div class="form__div">
                    <div class="combo combo-afiliacion">
                        <select class="form__input filter_municipio filter" id="zona" name="zona">
                            <option value="0">- Zona -</option>
                        </select>
                        <label class="form__label" for="municipio">Zona</label>
                    </div>
                </div>
                
                <div class="form__div">
                    <div class="combo combo-promotor">
                        <select class="form__input filter_colonia filter" id="colonia" name="colonia">
                            <option value="0">- Colonia -</option>
                        </select>
                        <label class="form__label" for="">Colonia</label>
                    </div>
                </div>
                
                <div class="form__div div__seccion">
                    <input maxlength="3" type="number" placeholder=" " id="seccion" name="seccion" class="form__input filter"/>
                    <label class="full-field form__label label__seccion">Sección</label>
                </div>
                
                <div id="filter__reload" class="form__button">
                    <i class="fa-solid fa-rotate-left"></i>
                </div>
            </div>
        
            <div class="notificacion_borrado" id="notificacion_borrado">
                
                
            </div>
            
            <!--div class="table__div" id="tabla_personas">

            </div--> 
            
            <table id="myTable" class="display" style="width:100%">
        <thead>
            <tr>
                <th style="text-align: center; display:noe">ID</th>
                <th style="text-align: center">Nombre</th>
                <th style="text-align: center">Apellidos</th>
                <th style="text-align: center">Edad</th>
                <th style="text-align: center">Sexo</th>
                <th style="text-align: center">Celular</th>
                <th style="text-align: center">Whatsapp</th>
                <th style="text-align: center">Calle y numero</th>
                <th style="text-align: center">Colonia</th>
                <th style="text-align: center">Demarcación</th>
                <th style="text-align: center">Seccion</th>
                <th style="text-align: center">Municipio</th>
            </tr>
        </thead>
    </table>
            
           
            <div class="info_persona" id="info_persona">
                <div class="cont_info">
                    <i class="fa-solid fa-circle-xmark" id="close_info"></i>
                    <form class="register-form" id="register-form" name="editarRegistro" action="editar-registro.php" method="POST" enctype="multipart/form-data">
                    <div id="datos_persona">
                        <h2 id="info_nombre_persona">Nombre persona</h2>
                        <input style="display: none" type="text" id="input_persona" name="persona" readonly />
                        <input value="0" style="display: none" type="text" id="input_tipo" name="tipo" readonly />
                        
                        <div class="datos_persona">
                            <label for="info_edad">Edad</label>
                            <p class="info" id="info_edad"></p>
                        </div>
                        
                        <div class="datos_persona">
                            <label for="info_sexo">Sexo</label>
                            <p class="info" id="info_sexo"></p>
                        </div>
                        
                        <div class="datos_persona">
                            <label for="info_curp">Celular</label>
                            <p class="info" id="info_cel"></p>
                        </div>
                        
                        <div class="datos_persona">
                            <label for="info_curp">Whatsapp</label>
                            <p class="info" id="info_tel"></p>
                        </div>
                        
                        <div class="datos_persona">
                            <label for="info_curp">Afiliación</label>
                            <p class="info" id="info_afiliacion"></p>
                        </div>
                        
                        <div class="datos_persona">
                            <label for="info_curp">Domicilio</label>
                            <p class="info" id="info_domicilio"></p>
                        </div>
                        
                        <div class="datos_persona">
                            <label for="info_curp">Demarcación</label>
                            <p class="info" id="info_demarcacion"></p>
                        </div>
                        
                        <div class="datos_persona">
                            <label for="info_curp">Sección</label>
                            <p class="info" id="info_seccion"></p>
                        </div>
                        
                        <div class="datos_persona">
                            <label for="info_curp">Promotor</label>
                            <p class="info" id="info_promotor"></p>
                        </div>
                        
                        
                        
                    </div>
                    
                    <div class="btns_persona">
                        <div id="editar_persona" class="form__button" onClick="editarRegistro.submit();">
                            <i class="fa-solid fa-pen-to-square"></i>
                            <p class="submit-txt">Editar Registro</p>
                        </div>
                        
                        
                        <div id="delete_persona" class="form__button">
                            <div id="delete_default">
                                <i class="fa-solid fa-trash"></i>
                                <p class="submit-txt">Borrar Registro</p>
                            </div>
                            
                            <div id="delete_confirmar">
                                <p>Confirmar</p>
                                <i class="fa-sharp fa-solid fa-square-xmark" id="cancelar_borrar"></i>
                                <i class="fa-sharp fa-solid fa-square-check" id="confirmar_borrar"></i>
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
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/v/dt/jqc-1.12.4/jszip-3.10.1/dt-2.0.1/b-3.0.0/b-colvis-3.0.0/b-html5-3.0.0/datatables.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.0.0/js/buttons.print.min.js"></script>
    
    <script type="text/javascript">
    $(document).ready(function(){
        
        function cargaPersonas(){
            $.post('ajax/personas_temp.php',{flag: 0 }, function(data){
                //console.log(data);
                let table = new DataTable('#myTable', {
                    language: {url: '//cdn.datatables.net/plug-ins/2.0.3/i18n/es-ES.json',},
                    ajax: 'data/arrays.txt',
                    rowId: '0',
                "createdRow": function( row, data, dataIndex ) {
                    $(row).addClass( 'row__person' );
                },
                fixedHeader: true,
                responsive: true,
                pageLength: 25,
                columnDefs:[{target:0,visible:false,searchable:false}],
                buttons: ['copy', 'excel', 'print'],
                layout: {
                    topEnd: {
                        buttons: ['copy', 'excel', 'print'],
                    }
                },
                }).on('click', 'tbody tr', function () {
                    //let data = table.row(this).data();
                    showInfoPersona(table.row(this));
                });;

            });
        }
        cargaPersonas();
        
        $('#menu_aspirantes').addClass("menu_seleccion");
        
        var pagina;
        $('.pagina').click(function(){
            pagina = this.id;
            cargaFiltros(pagina);
        });
        
        /*Carga tabla con datos de personas registradas en plataforma*/
       /* function cargaPersonas(){
            $.post("ajax/personas.php", {flag: 0, pagina: 1, limit: <?php echo $limit; ?>, } , function(data){
                if(data === "" || data == "<tbody></tbody>"){
                    $("#tabla_personas").html('<div class="filter__error"><i class="fa-solid fa-triangle-exclamation"></i><p>No se han encontrado registros</p></div>');
                    $('#pagination').remove();
                } else {
                    data = '<table class="table"><thead class="table__head"><tr><th scope= "col">No.</th><th scope= "col">Nombre</th><th scope= "col">Apellidos</th><th scope= "col">Edad</th><th scope= "col">Sexo</th><th scope= "col">Celular</th><th scope= "col">Telefono fijo</th>><th scope= "col">Calle y número</th><th scope= "col">Colonia</th><th scope= "col">Sección</th><th scope= "col">Municipio</th><th scope= "col">Estado</th></tr></thead>' + data

                    $("#tabla_personas").html(data);
                    
                    $('.row__person').click(function(){                        
                        showInfoPersona(this);
                    });
                }
            });	
        }	
        cargaPersonas();*/
        
        /*Carga colonias y demarcaciones correspondientes cuando se elige municipio*/
        $("#municipio").on('change', function () {
            municipio = $('#municipio option:selected').text();
            $.post("ajax/colonias_filter.php", { municipio: municipio }, function(data){
                if(data === ""){
                    $("#colonia").eq(0).html('<option value="0">- Colonia -</option>');

                } else {
                    $("#colonia").eq(0).html(data);
                }
            });	
            
            $('#demarcacion').html('<option value="0">- Demarcación -</option>');
            $.post("ajax/demarcaciones.php", { mun: $('#municipio option:selected').text()}, function(data){
                
                json = jQuery.parseJSON(data);
                for(i = 0; i< json.length; i++){
                    $('#demarcacion').append($('<option>', {
                        value: json[i]['dem'],
                        text: json[i]['dem']
                    }));
                }
                
            });
        });

        /*Carga zonas de la demarcacion seleccionada*/
        $('#demarcacion').on('change', function(){
            $("#zona").html(""); 
            $.post("ajax/zonas.php", {mun: $('#municipio option:selected').val(), dem: $('#demarcacion option:selected').val() }, function(data){                
                if(data == "x" || data == "0"){
                    if($("#demarc option:selected").val() == '0'){
                        $("#zona").append('<option value="0">- Zona -</option>');   
                    } else{
                    $("#zona").append('<option value="0">Sin zonas asignadas</option>');}
                } else {
                    zonas = parseInt(data);
                    $("#zona").append('<option value="0">- Zona -</option>');
                    for(i = 1; i <= zonas; i++){
                        <?php if($usr_nivel == "7" || $usr_nivel == "4" || $usr_nivel == "6"):?>
                            if(i == <?php echo $usr_zona;?>){
                                $("#zona").append('<option value="'+i+'">'+i+'</option>');
                            }
                        <?php else:?>
                            $("#zona").append('<option value="'+i+'">'+i+'</option>');
                        <?php endif;?>
                        
                        
                        
                        
                    }
                }
            });
        });

        /*Banderas para saber si cambio municipio o colonia*/
        bandera_mun = 0;
        $('#municipio').on('change', function(){
            bandera_mun = 1;
            return bandera_mun;
        });
        $('#colonia').on('change', function(){
            bandera_mun = 0;
            return bandera_mun;
        });
        
        /*Carga tabla de personas de acuerdo con los filtros aplicados*/
        $('.filter').on('change', function(){
            //cargaFiltros(1);
            cargaFiltrosMejor(0);
        });
        
        $('#seccion').on('keyup', function(){
            //cargaFiltros(1);
            cargaFiltrosMejor(0);
        });
        $('#nombre').on('keyup', function(){
            //cargaFiltros(1);
            cargaFiltrosMejor(0);
        });
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        function cargaFiltrosMejor(filtro){   
            edad = $('#edad').val();
            sexo = $('#sexo').val();
            municipio = $('#municipio option:selected').text();
            demarcacion = $('#demarcacion option:selected').val();
            zona = $('#zona option:selected').val();
            colonia = bandera_mun === 1 ? "0" : $('#colonia option:selected').text();
            seccion = $('#seccion').val();
            nombre = $('#nombre').val();
            
             
            $.post("ajax/personas_filter_temp.php", { flag: 0, filtro: filtro, nombre: nombre, edad: edad, sexo: sexo, municipio: municipio, demarcacion: demarcacion, zona: zona, colonia: colonia, seccion: seccion }, function(data){
                let table = $('#myTable').DataTable();
                table.clear().draw();
                json = jQuery.parseJSON(data);
                
                for(i = 0; i< json.length; i++){
                    table.row.add([json[i]['id_persona'],json[i]['nombre'],json[i]['apellidos'],json[i]['edad'],json[i]['sexo'],json[i]['celular'],json[i]['whatsapp'],json[i]['calle_num'],json[i]['colonia'],json[i]['demarcacion'],json[i]['seccion'],json[i]['ciudad']]).draw();
                    
                }
            
            });	
            
        }
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        /*
        function cargaFiltros(page){   
            edad = $('#edad').val();
            sexo = $('#sexo').val();
            afiliacion = $('#afiliacion').val();
            municipio = $('#municipio option:selected').text();
            demarcacion = $('#demarcacion option:selected').val();
            zona = $('#zona option:selected').val();
            colonia = bandera_mun === 1 ? "0" : $('#colonia option:selected').text();
            seccion = $('#seccion').val();
            nombre = $('#nombre').val();
             
            $.post("ajax/personas_filter.php", { flag: 0, pagina: page ,nombre: nombre, edad: edad, sexo: sexo, afiliacion: afiliacion, municipio: municipio, demarcacion: demarcacion, zona: zona, colonia: colonia, seccion: seccion, limit: <?php //echo $limit; ?> }, function(data){
              /*  if(data === ""){
                    $("#tabla_personas").html('<div class="filter__error"><i class="fa-solid fa-triangle-exclamation"></i><p>No se han encontrado registros con los filtros solicitados</p></div>');
                } else {
                    data = '<table class="table"><thead class="table__head"><tr><th scope= "col">No.</th><th scope= "col">Nombre</th><th scope= "col">Apellidos</th><th scope= "col">Edad</th><th scope= "col">Sexo</th><th scope= "col">Celular</th><th scope= "col">Telefono fijo</th><th scope= "col">Calle y número</th><th scope= "col">Colonia</th><th scope= "col">Sección</th><th scope= "col">Municipio</th><th scope= "col">Estado</th></tr></thead>' + data

                    $("#tabla_personas").html(data);
                    
                    personas_filtro = parseInt($('#total_personas_filtro').text());
                    pagina_activa = parseInt($('#pagina_activa').text());
                    paginas = "";
                    total_paginas_filtro = Math.ceil(personas_filtro / <?php //echo $limit; ?>);
                    for(i = 1; i <= total_paginas_filtro; i++){
                        
                        if(i == pagina_activa){
                            paginas = paginas + '<li class="active pagina" id="' + i + '">' + i + '</li>';
                        } else {
                            paginas = paginas + '<li class="pagina" id="' + i + '">' + i + '</li>';
                        }
                    }
                    
                    $('#pagination').html(paginas);
                    
                    $('.row__person').click(function(){                        
                        showInfoPersona(this);
                    });
                    
                    $('.pagina').click(function(){
                        pagina = this.id;
                        cargaFiltros(pagina);
                    });
                    
                    if(personas_filtro == 0){
                        $("#tabla_personas").html('<div class="filter__error"><i class="fa-solid fa-triangle-exclamation"></i><p>No se han encontrado registros con los filtros solicitados</p></div>');
                    }
               // }
            });
        }*/
    
        /*Resetea los campos de filtro y reinicia la tabla de personas*/
        $("#filter__reload").click(function() {
            $("#edad").val('0');
            $("#sexo").val('0');
            $("#afiliacion").val('0');
            $("#municipio").val('0');
            $("#demarcacion").html('<option value="0">- Demarcación -</option>');
            $("#zona").html('<option value="0">- Zona -</option>');
            $("#seccion").val('');
            $("#colonia").html('<option value="0">- Colonia -</option>');
            //cargaPersonas();
            //cargaFiltros(1);
            cargaFiltrosMejor(0);
        });
        
        $('#info_persona').hide();
        /*Cerrar popup con info de la persona*/
        $('#close_info').click(function(){
            
            if(!$('#delete_default').is(':visible')){
                $('#delete_default').show();
                $('#delete_confirmar').hide();
            }
            $('#info_persona').fadeOut(500);
        });

        //Mostrar registro completo de cada persona
        var id;
        function showInfoPersona(row){
            /*if(!$('#delete_default').is(':visible')){
                $('#delete_default, #delete_confirmar').toggle(300);
            }*/
            $('#info_persona').fadeIn(500);
            datos = [];
            $.post("ajax/persona_unico.php", { persona: row.id }, function(data){
                if(data === ""){
                    $("#datos_persona").html('error');
                } else {
                    
                    json = jQuery.parseJSON(data);
                    id = row.id;
                    $("#info_nombre_persona").html("- "+json['nombre']+" -  ");
                    $('#input_persona').val(id);
                    
                    $("#info_edad").html(json['edad']);
                    $("#info_sexo").html(json['sexo']);
                    $("#info_cve").html(json['cve_elec']);
                    $("#info_curp").html(json['curp']);
                    $("#info_rfc").html(json['rfc']);
                    $("#info_cel").html(json['celular']);
                    $("#info_tel").html(json['whatsapp']);
                    $("#info_afiliacion").html(json['afiliacion']);
                    $("#info_domicilio").html(json['domicilio']);
                    $("#info_demarcacion").html(json['demarcacion']);
                    $("#info_seccion").html(json['seccion']);
                    $("#info_promotor").html(json['promotor']);  
                }
            });	
        }
        
        //Confirmación para borrar registro
        $('#delete_confirmar').hide();
        $('#delete_default, #cancelar_borrar').click(function(){
            $('#delete_default, #delete_confirmar').toggle(300);
        });
        
        $('#confirmar_borrar').click(function(){
            borrarRegistro();
        });
        
        function borrarRegistro(){
            
            $.post("ajax/persona_borrar.php", {persona: id}, function(data){
                
                if(data == "Error"){
                    $('#notificacion_borrado').html('<div class="borrado_error"><i class="fa-solid fa-triangle-exclamation"></i><p>Ups! Ocurrió un problema</p></div>');
                    $('#info_persona').fadeOut(500);
                    $('#notificacion_borrado').delay(3000).fadeOut(400);
                } else{
                    $("#info_nombre_persona").html("");
                    $("#info_edad").html("");
                    $("#info_sexo").html("");
                    $("#info_cve").html("");
                    $("#info_curp").html("");
                    $("#info_rfc").html("");
                    $("#info_cel").html("");
                    $("#info_tel").html("");
                    $("#info_afiliacion").html("");
                    $("#info_domicilio").html("");
                    $("#info_demarcacion").html("");
                    $("#info_seccion").html("");
                    $("#info_promotor").html(""); 
                    
                    cargaPersonas();
                    $('#notificacion_borrado').html('<div class="borrado_success"><i class="fa-solid fa-trash"></i><p>Registro eliminado satisfactoriamente</p></div>')
                    $('#info_persona').fadeOut(500);
                    $('#notificacion_borrado').delay(3000).fadeOut(400);
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
            
    });
        
    
        
        
    
    </script>
    
    <script type="application/javascript" src="js/popup_usuario.js"></script>
</body>
</html>












