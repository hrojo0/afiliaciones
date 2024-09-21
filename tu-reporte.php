<?php
$session_lifetime = 3600 * 24 * 4; // 4 days
session_set_cookie_params ($session_lifetime);
session_start();
require 'conexion_db.php';
require 'functions.php';
comprobar_login();
require "includes/session.php";
$_SESSION['usuario_registrado'] = 'no';

/*carga datos de combo para cargar promotores*/
$statement = $con->prepare('SELECT id_municipio, municipio FROM municipio ORDER BY municipio');
$statement->execute();
$municipios = $statement->fetchAll();

//Datos base para paginado dependiendo del nivel de usuario

switch($usr_nivel){
    case "1":
    case "2":
        //$sql_promotores = 'SELECT COUNT(*) FROM persona WHERE cve_elec != "0"';
        $sql_promotores = 'SELECT id_usuario, nombre, apellidos FROM usuario WHERE id_nivel_usuario = "7"';
        break;
    case "3":
        //$sql_promotores = 'SELECT COUNT(*) FROM persona p INNER JOIN municipio m WHERE p.ciudad = m.municipio AND m.id_municipio =  "'.$usr_mun.'" ';
        $sql_promotores = 'SELECT u.id_usuario, u.nombre, u.apellidos FROM usuario u INNER JOIN municipio m ON u.municipio = m.id_municipio WHERE u.id_nivel_usuario = "7" AND m.id_municipio = "'.$usr_mun.'"';
        break;
    case "5":
        //$sql_promotores = 'SELECT COUNT(*) FROM persona p INNER JOIN municipio m WHERE p.ciudad = m.municipio AND m.id_municipio = "'.$usr_mun.'" AND p.demarcacion = "'.$usr_dem.'" AND cve_elec != "0"';
        $sql_promotores = 'SELECT u.id_usuario, u.nombre, u.apellidos FROM usuario u INNER JOIN municipio m ON u.municipio = m.id_municipio WHERE u.id_nivel_usuario = "7" AND m.id_municipio = "'.$usr_mun.'" AND u.dem = "'.$usr_dem.'"';
        break;
    case "6":
        //$sql_promotores = 'SELECT COUNT(*) FROM persona p INNER JOIN municipio m WHERE p.ciudad = m.municipio AND m.id_municipio = "'.$usr_mun.'" AND p.demarcacion = "'.$usr_dem.'" AND zona = "'.$usr_zona.'" AND cve_elec != "0"';
        $sql_promotores = 'SELECT u.id_usuario, u.nombre, u.apellidos FROM usuario u INNER JOIN municipio m ON u.municipio = m.id_municipio WHERE u.id_nivel_usuario = "7" AND m.id_municipio = "'.$usr_mun.'" AND u.zona = "'.$usr_zona.'"';
        break;
    case "4": 
        $statement = $con ->prepare('SELECT demarcaciones FROM coordinacion_dems WHERE id_usuario = "'.$usr_usuario.'"');
        $statement->execute();
        $user_dems = $statement->fetch();
        $user_dems = $user_dems[0];
        $user_dems = substr($user_dems, 0 , -1);
        //$sql_promotores = 'SELECT COUNT(*) FROM persona p INNER JOIN municipio m WHERE p.ciudad = m.municipio AND m.id_municipio =  "'.$usr_mun.'" AND p.seccion IN('.$user_dems.') AND cve_elec != "0"';
        $sql_promotores = 'SELECT u.id_usuario, u.nombre, u.apellidos FROM usuario u INNER JOIN municipio m ON u.municipio = m.id_municipio WHERE u.id_nivel_usuario = "7" AND m.id_municipio = "'.$usr_mun.'" AND u.seccion IN('.$user_dems.')';
        break;
    case "7":
    case "8":
        $sql_promotores = 'SELECT id_usuario, nombre, apellidos FROM usuario WHERE id_usuario = "'.$usr_usuario.'"';
        break;
}

$statement = $con->prepare($sql_promotores);
$statement->execute();
$promotores = $statement->fetchAll();
//print_r(count($promotores));
        
?>

<!DOCTYPE html>
<html lang="es-MX">
<head>
    <meta charset="UTF-8">
    <title>Personas registradas</title>
    

    <script src="https://polyfill.io/v3/polyfill.min.js?features=default"></script>
   
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <link rel="stylesheet" type="text/css" href="css/normalize.css">
    <link rel="stylesheet" type="text/css" href="css/base.css">
    <link rel="stylesheet" type="text/css" href="css/form.css">
    <link rel="stylesheet" type="text/css" href="css/sidebar-menu.css">
    <link href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/v/dt/jqc-1.12.4/jszip-3.10.1/dt-2.0.1/b-3.0.0/b-colvis-3.0.0/b-html5-3.0.0/datatables.min.css" rel="stylesheet">
    
    
    <link rel="stylesheet" type="text/css" href="css/tu-reporte.css">
    
    

    
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
            <h2 class="form__title" id="form__title">Afinidad de Personas al Proyecto</h2>  
            <div class="form__div div__nombre" id="buscador__principal">
                <!--input type="text" id="buscador" placeholder="Buscar opciones"-->
                
                <input type="text" placeholder=" " id="nombre" name="nombre" class="form__input filter" readony/>
                <input type="text" placeholder=" " id="promotor_" name="promotor_" class="form__input filter" value="id" style="display: none" readonly/>
                <label class="full-field form__label label__nombre">Nombre Promotor</label>
                
                
                <!--select id="combo" size="2" style="display: none;">
    <?php
    //foreach ($promotores as $promotor) {
        //echo "<option value='".$promotor["id_usuario"]."'>".$promotor["nombre"].' '.$promotor['apellidos']."</option>";
    //}
    ?>
</select-->

            </div>
            
            <div class="cont_img" id="cont_img">
                <div class="img_ingresa">
                    <img src="img/ingresa-promotor.png" alt="">
                </div>
            </div>
            
            
            <table id="myTable" class="display" style="width:100%">

                <thead>
                    <tr>
                        <th style="text-align: center; display:noe">ID</th>
                        <th style="text-align: center">Nombre</th>
                        <th style="text-align: center">Apellidos</th>
                        <th style="text-align: center">Edad</th>
                        <th style="text-align: center">Sexo</th>
                        <th style="text-align: center">Teléfono</th>
                        <th style="text-align: center">Whatsapp</th>
                        <th style="text-align: center">Afiliado</th>
                        <th style="text-align: center">Calle y numero</th>
                        <th style="text-align: center">Colonia</th>
                        <th style="text-align: center">Demarcación</th>
                        <th style="text-align: center">Seccion</th>
                        <th style="text-align: center">Municipio</th>
                    </tr>
                </thead>
            </table>

           
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
        var isNombreFocused = false;
        var noCoincidencias = true;
        $('#menu_tu_reporte').addClass("menu_seleccion");
        
        $('#myTable').hide();
        
        var promotoresCargados = false;
        
        $('#nombre').on('focus', function(){
            $('#buscador__cont').show();
            if (!promotoresCargados) {
                $('#buscador__principal').append($('<div>', {
                    id: 'buscador__cont',
                }));

                $('#buscador__cont').append($('<div>', {
                    id: 'promotores__cont',
                }));

                $('#promotores__cont').append($('<ul>', {
                    id: 'lista__promotores',
                }));

                <?php $tmp_promotores="";
                foreach ($promotores as $promotor) {
                    $tmp_promotores .= '<li id="'.$promotor['id_usuario'].'" class="elemento_lista_promotores form__input">'.$promotor['nombre'].' '.$promotor['apellidos'].'</li>';
                }
                ?>

                $('#lista__promotores').html('<?php echo $tmp_promotores;?>');

                promotoresCargados = true;
                isNombreFocused = true;
    
            }
        }).on('input', function(){
            $('#sin_promotores').remove();
            var searchText = $(this).val().trim().toLowerCase();
            noCoincidencias = true;
            
            $('#lista__promotores li').each(function(){
                var promotorName = $(this).text().toLowerCase();

                if (promotorName.includes(searchText)) {
                    $(this).show();
                    noCoincidencias = false;
                } else {
                    $(this).hide();
                }
            });
            
            if (noCoincidencias) {
                console.log('No se encontraron coincidencias.');
                $('#lista__promotores').append($('<li>', {
                    id: 'sin_promotores',
                    text: 'No se encontraron resultados',
                    class: 'elemento_lista_promotores form__input'
                }));
            } else {
                
            }
        });
        
        $(document).on('click', '.elemento_lista_promotores', function() {
            $('#promotor_').val($(this).attr('id'));
            $('#nombre').val($(this).text()); 
            cargaPersonas();
            
            var searchText = $('#nombre').val().trim().toLowerCase();

            $('#lista__promotores li').each(function(){
                var promotorName = $(this).text().toLowerCase();

                if (promotorName.includes(searchText)) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        });
        
        
        $('#nombre').on('blur', function() {
            setTimeout(function() {
                isNombreFocused = false;
                $('#buscador__cont').hide();
            }, 200);
        });


        
        
        /*Carga tabla con datos de personas registradas en plataforma*/
        function cargaPersonas(){
            $('#cont_img').remove();
            $('#myTable').fadeIn();
            $.post('ajax/personas_reporte.php',{flag: 1, promotor: $('#promotor_').val() }, function(data){
                if ($.fn.DataTable.isDataTable('#myTable')) {
            $('#myTable').DataTable().destroy();
        }
            let table = new DataTable('#myTable', {
                language: {url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json',},
                ajax: 'data/arrays_reporte.txt',
                rowId: '0',
                "createdRow": function( row, data, dataIndex ) {
                    $(row).addClass( 'row__person' );
                    if(data[data.length - 1] == "8"){
                        $(row).addClass( 'promovido__especial' );   
                    }
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
            });
            
        });
        }	
        
        <?php if($usr_nivel == "7<" || $usr_nivel == "8"):?>
        //Loggeado como promotor, carga datos automáticamente
            $('#cont_img').remove();
            $('#nombre').val('<?php echo $usr_nombre.' '.$usr_apellidos;?>');
            $('#promotor_').val('<?php echo $usr_usuario;?>');
            cargaPersonas();
        <?php endif;?>
        //cargaPersonas();
        
        
        
      
        
        /*Carga tabla de personas de acuerdo con los filtros aplicados*/
        $('.filter').on('change', function(){
            //cargaFiltrosMejor(1);
        });
        
        $('#nombre').on('keyup', function(){
            //cargaFiltrosMejor(1);
        });
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        function cargaFiltrosMejor(filtro){   
            edad = $('#edad').val();
            sexo = $('#sexo').val();
            afiliacion = $('#afiliacion').val();
            municipio = $('#municipio option:selected').text();
            demarcacion = $('#demarcacion option:selected').val();
            zona = $('#zona option:selected').val();
            colonia = bandera_mun === 1 ? "0" : $('#colonia option:selected').text();
            seccion = $('#seccion').val();
            nombre = $('#nombre').val();
            
             
            $.post("ajax/personas_filter_temp.php", { flag: 1, filtro: filtro, nombre: nombre, edad: edad, sexo: sexo, afiliacion: afiliacion, municipio: municipio, demarcacion: demarcacion, zona: zona, colonia: colonia, seccion: seccion }, function(data){
                var table = $('#myTable').DataTable();
                table.clear().draw();
                json = jQuery.parseJSON(data);
                for(i = 0; i< json.length; i++){
                    
                    var rowData = [json[i]['id_persona'], json[i]['nombre'], json[i]['apellidos'], json[i]['edad'], json[i]['sexo'], json[i]['celular'], json[i]['whatsapp'], json[i]['afiliacion'], json[i]['calle_num'], json[i]['colonia'], json[i]['demarcacion'], json[i]['seccion'], json[i]['ciudad']];
    
                    var newRow = table.row.add(rowData).draw().node();

                    if (json[i]['usuario'] == "8") {
                        $(newRow).addClass('promovido__especial');
                    }
                }
                
            });	
            
        }
        

    
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
                cargaFiltrosMejor(1);
        });
      
       
        
      
        
        
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