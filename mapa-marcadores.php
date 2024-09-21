<?php
$session_lifetime = 3600 * 24 * 4; // 4 days
session_set_cookie_params ($session_lifetime);
session_start();
require 'conexion_db.php';
require 'functions.php';
comprobar_login();
require "includes/session.php";
$_SESSION['usuario_registrado'] = 'no';
/*obtener las demarcaciones del coordinador de dems*/
$statement = $con ->prepare('SELECT * FROM coordinacion_dems WHERE id_usuario = "'.$usr_usuario.'"');
$statement->execute();
$found = $statement->fetch();
if($found){
    $statement = $con ->prepare('SELECT demarcaciones FROM coordinacion_dems WHERE id_usuario = "'.$usr_usuario.'"');
    $statement->execute();
    $user_dems = $statement->fetch();
    $user_dems = $user_dems[0];
    $user_dems = substr($user_dems, 0, -1);  
}

/*carga datos de combo municipios y colonia*/
$statement = $con->prepare('SELECT id_municipio, municipio FROM municipio ORDER BY municipio');
$statement->execute();
$municipios = $statement->fetchAll();

?>

<!DOCTYPE html>
<html lang="es-MX">
<head>
    <meta charset="UTF-8">
    <title>Mapa de marcadores</title>
    

    <script src="https://polyfill.io/v3/polyfill.min.js?features=default"></script>
   
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <link rel="stylesheet" type="text/css" href="css/normalize.css">
    <link rel="stylesheet" type="text/css" href="css/base.css">
    <link rel="stylesheet" type="text/css" href="css/form.css">
    <link rel="stylesheet" type="text/css" href="css/mapa-marcadores.css">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Titillium+Web:wght@200;300;400;600;700;900&display=swap" rel="stylesheet">
    
    <script src="https://kit.fontawesome.com/9c52d851d9.js" crossorigin="anonymous"></script>
</head>
<body>
   
    
    <div class="todo">
        <div class="cont">
            <div class="filtros">
               <?php if($usr_nivel != "8"):?>
                <div class="form__div">
                    <div class="combo combo-registro">
                        <select class="form__input filter__registro filter" id="registro" name="registro">
                          <option value="1">Afiliados/No afiliados</option>
                          <option value="0">Aspirantes</option>   
                        </select>
                        <label class="form__label" for="registro">Registro</label>
                    </div>
                </div>
                
                
                <div class="form__div" id="div__afiliacion">
                    <div class="combo combo-afiliacion">
                        <select class="form__input filter__afiliacion filter" id="afiliacion" name="afiliacion">
                          <option value="2">- Afiliado -</option>
                          <option value="1">Si</option>
                          <option value="0">No</option>   
                        </select>
                        <label class="form__label" for="afiliacion">Afiliado</label>
                    </div>
                </div>
                <?php endif;?>
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
                    <div class="combo combo-demarc">
                        <select class="form__input demarc filter" id="demarc" name="demarc">
                            <option value="0">- Demarcación -</option>
                        </select>
                        <label class="form__label sexo_afiliacion" for="demarc">Demarcación</label>

                    </div>
                </div>
                  
                  <div class="form__div">
                    <div class="combo combo-demarc">
                        <select class="form__input demarc filter" id="zona" name="zona">
                            <option value="0">- Zona -</option>
                        </select>
                        <label class="form__label sexo_afiliacion" for="demarc">Zona</label>

                    </div>
                </div>
                   
                <div class="form__div">
                    <div class="combo combo-seccion">
                        <select class="form__input seccion filter" id="seccion" name="seccion">
                            <option value="0">- Sección -</option>
                        </select>
                        <label class="form__label sexo_afiliacion" for="seccion">Sección</label>

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
 
                
                <div id="filter__reload" class="form__button">
                    <i class="fa-solid fa-rotate-left"></i>
                </div>
            </div>
            
            
            
            <div class="cont_map">
                <div id="mapa"></div>        
            </div>
        </div>
        
    </div>
    
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.27.0/moment.min.js"></script>
    
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.3/jquery.min.js"></script>
    
    <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=API_KEY&callback=initMap" defer></script>

    <script type="text/javascript">
         
        $('#registro').on('change', function(){
            
            flag = $('#registro').find(":selected").val();
            if(flag == 0){
                $("#afiliacion").val('2');
                $("#div__afiliacion").hide();
            }
            if(flag == 1){
                $("#afiliacion").val('2');
                $("#div__afiliacion").show();
            }
        });
        
        $('#municipio').on('change', function(){
            $('#demarc').html('<option value="0">- Demarcación -</option>');
            $('#seccion').html('<option value="0">- Sección -</option>');
            dems_temp = "";
            $.post('ajax/demarcaciones.php', {mun:$('#municipio option:selected').text()}, function(data){
                json = jQuery.parseJSON(data);
                for(i = 0; i< json.length; i++){
                    $('#demarc').append($('<option>', {
                        value: json[i]['dem'],
                        text: json[i]['dem']
                    }));
                    dems_temp = dems_temp + json[i]['dem'] + ",";
                }
                $('#demarc').val(dems_temp);
                $("#demarc").val('0');
            });
        });
        
        $('#zona').on('change', function(){
            $('#seccion').html('<option value="0">- Sección -</option>');
            seccs_temp = "";
            $.post("ajax/secciones.php", {mun: $('#municipio option:selected').text(), dem: $('#demarc option:selected').val(), zona: $('#zona option:selected').val() }, function(data){
                
                json = jQuery.parseJSON(data);
                for(i = 0; i< json.length; i++){
                    $('#seccion').append($('<option>', {
                        value: json[i]['secc'],
                        text: json[i]['secc']
                    }));
                    seccs_temp = seccs_temp + json[i]['secc'] + ",";
                }
                $('#seccion').val(seccs_temp);
                $('#seccion').val('0');
            });
        });
        
        /*Carga zonas de la demarcacion seleccionada*/
        $('#demarc').on('change', function(){
            $("#zona").html(""); 
            $.post("ajax/zonas.php", {mun: $('#municipio option:selected').val(), dem: $('#demarc option:selected').val() }, function(data){                
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
        
        
        var map, bounds, mostrarMarcadores, lat, lng, icono_1, icono_0, marker;
        var markers = [];
        var defaultLocation = { lat: 21.5089346, lng: -104.9028577 };
        <?php if(isset($user_dems)):?>
        user_demarcaciones = '<?php echo $user_dems; ?>'
        <?php endif;?>
        function initMap() {
            icono_1 = {
                url: 'img/marcadores-1.png',//'http://maps.google.com/mapfiles/ms/icons/green-dot.png', // URL del ícono
                scaledSize: new google.maps.Size(32, 40), // Tamaño del ícono
                origin: new google.maps.Point(0, 0), // Origen del ícono
                anchor: new google.maps.Point(16, 40) // Punto de anclaje del ícono
            };
            icono_0 = {
                url: 'img/marcadores-0.png',//'http://maps.google.com/mapfiles/ms/icons/green-dot.png', // URL del ícono
                scaledSize: new google.maps.Size(32, 40), // Tamaño del ícono
                origin: new google.maps.Point(0, 0), // Origen del ícono
                anchor: new google.maps.Point(16, 40) // Punto de anclaje del ícono
            };
            bounds = new google.maps.LatLngBounds();
            var mapOptions = {
                mapTypeId: 'roadmap',
                center: defaultLocation,
                zoom: 12,
            };

            map = new google.maps.Map(document.getElementById('mapa'), {
              mapOptions
            });

            map.setTilt(50);

            
            // Crear múltiples marcadores desde la Base de Datos 
            var marcadores = [
                <?php include('marcadores.php'); ?>
            ];
            //console.log(marcadores);
            // Creamos la ventana de información para cada Marcador
            var ventanaInfo = [
                <?php include('info_marcadores.php'); ?>
                
            ];
            
            markermapF(map, bounds, marcadores, ventanaInfo);

 
      }
        function markermapF(map, bounds, marcadores, ventanaInfo){
            deleteMarkers();
            markers = [];

            // Creamos la ventana de información con los marcadores 
            mostrarMarcadores = new google.maps.InfoWindow(), marcadores;
            
            // Colocamos los marcadores en el Mapa de Google 
            for (i = 0; i < marcadores.length; i++) {
                //console.log(marcadores[i]);
                let icono = '';
                if(marcadores[i][3] == 1){
                   icono = icono_1;
                } else {
                   icono = icono_0;
                }
                var position = new google.maps.LatLng(marcadores[i][1], marcadores[i][2]);
                bounds.extend(position);
                marker = new google.maps.Marker({
                  position: position,
                  map: map,
                  title: marcadores[i][0],
                    icon: icono
                });

                // Colocamos la ventana de información a cada Marcador del Mapa de Google 
                google.maps.event.addListener(marker, 'click', (function(marker, i) {
                    return function() {
                        mostrarMarcadores.setContent(ventanaInfo[i][0]);
                        mostrarMarcadores.open(map, marker);
                    }
                })(marker, i));

                // Centramos el Mapa de Google para que todos los marcadores se puedan ver 
                map.fitBounds(bounds);
                
                //se agrega marcardor a array markers
                markers.push(marker);
                //se agregar los marcadores al mapra para despues ser reiniciados
                markers[i].setMap(map);
            }

                    
            
            // Aplicamos el evento 'bounds_changed' que detecta cambios en la ventana del Mapa de Google, también le configramos un zoom de 14 
            /*var boundsListener = google.maps.event.addListener((map), 'bounds_changed', function(event) {
              this.setZoom(14);
              google.maps.event.removeListener(boundsListener);
            });
           
            
            //navigator.geolocation.getCurrentPosition(success);
            /*
            if(navigator.geolocation){
                navigator.geolocation.getCurrentPosition(function(pos){
                    lat = pos.coords.latitude;
                    lng = pos.coords.longitude;
                    console.log(lat);
                    console.log(lng);
                    map.setCenter({lat: lat, lng: lng});
                    
                });
            }*/
                                                     
        // Obtener la ubicación actual del usuario si la geolocalización está disponible
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function(position) {
                    var userLocation = {
                        lat: position.coords.latitude,
                        lng: position.coords.longitude
                    };
                    // Centrar el mapa en la ubicación del usuario
                    map.setCenter(userLocation);
                    map.setZoom(13);
                }, function() {
                    // En caso de error al obtener la ubicación actual, mantener la ubicación predeterminada
                    handleLocationError(true, map.getCenter());
                });
            } else {
                // Si la geolocalización no está disponible en el navegador, mantener la ubicación predeterminada
                handleLocationError(false, map.getCenter());
            }

            
        }
        // Función para manejar errores de ubicación
function handleLocationError(browserHasGeolocation, defaultLocation) {
    var errorMessage = browserHasGeolocation ?
        'Error: No se pudo obtener la ubicación del usuario.' :
        'Error: Tu navegador no soporta la geolocalización.';
    //console.log(errorMessage);
    map.setCenter({ lat: 21.5089346, lng: -104.9028577 });
    map.setZoom(12);
        
    
}
        function success(pos) {
          crd = pos.coords;
          //console.log(crd);
          //console.log('Your current position is:');
          //console.log(`Latitude : ${crd.latitude}`);
          //console.log(`Longitude: ${crd.longitude}`);
          //console.log(`More or less ${crd.accuracy} meters.`);

            return crd;
        }
        function setMapOnAll(map) {
            for (let i = 0; i < markers.length; i++) {
                markers[i].setMap(map);
                
            }
        }
        function hideMarkers() {
            setMapOnAll(null);
        }
        function deleteMarkers() {
            hideMarkers();
            markers = [];
        }
        function getMarcadores(ajaxurl){
            var respuesta_marcadores;
            
            colonia = bandera_mun === 1 ? "0" : $('#colonia option:selected').text();
            <?php if(isset($user_dems)):?>
                params = {
                    registro: $('#registro').val(),
                    afiliacion: $('#afiliacion').val(),
                    municipio: $('#municipio option:selected').text(),
                    colonia: colonia,
                    seccion: $('#seccion').val(),
                    demarc: $('#demarc').val(),
                    user_dems: user_demarcaciones,
                    zona: $('#zona').val(),
                };
            <?php else:?>
                params = {
                    registro: $('#registro').val(),
                    afiliacion: $('#afiliacion').val(),
                    municipio: $('#municipio option:selected').text(),
                    colonia: colonia,
                    seccion: $('#seccion').val(),
                    demarc: $('#demarc').val(),
                    zona: $('#zona').val(),
                };
            <?php endif;?>

            coords = points = [];
            var json="";
            
            $.ajax({
                type: 'POST',
                url: ajaxurl, 
                data: params,
                async: false,
                success: function(data){
                    if(data === ""){
                        //console.log("VACIO");
                    } else {
                        
                        json = jQuery.parseJSON(data);
                        
                        for (var i=0; i < json.length; i++) {
                            coords.push(json[i]);
                        }
                    }
                }
            });
            
            return coords;
        }
        
        
        /*Carga tabla de personas de acuerdo con los filtros aplicados*/
        $('.filter').on('change', function(){
            marcadores = getMarcadores('marcadores_filter.php');
            ventanaInfo = getMarcadores('info_marcadores_filter.php');
            markermapF(map, bounds, marcadores, ventanaInfo);
            
        });
        
        $("#filter__reload").click(function() {
            $("#afiliacion").val('2');
            $("#municipio").val('0');
            $('#demarc').html('<option value="0">- Demarcación -</option>');
            $('#seccion').html('<option value="0">- Sección -</option>');
            $("#seccion").val('0');
            $("#demarc").val('0');
            $("#colonia").html('<option value="0">- Colonia -</option>');
            $("#zona").html('<option value="0">- Zona -</option>');
            marcadores = getMarcadores('marcadores_filter.php');
            
            ventanaInfo = getMarcadores('info_marcadores_filter.php');
            markermapF(map, bounds, marcadores, ventanaInfo);
        });
        
        
        /***************JS PARA CARGAR COLONIAS DEPENDIENDO DE MUNICIPIO*****************/
       $("#municipio").on('change', function () {
            municipio = $('#municipio option:selected').text();
            $.post("ajax/colonias_filter.php", { municipio: municipio }, function(data){
                if(data === ""){
                    $("#colonia").eq(0).html('<option value="0">- Colonia -</option>');

                } else {
                    $("#colonia").eq(0).html(data);
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
        
        

    </script>
</body>
</html>