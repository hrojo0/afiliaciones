<?php
$session_lifetime = 3600 * 24 * 4; // 4 days
session_set_cookie_params ($session_lifetime);
session_start();
require 'conexion_db.php';
require 'functions.php';
comprobar_login();
require "includes/session.php";
//$_SESSION['usuario_registrado'] = 'no';

/*obtener las demarcaciones del coordinador de dems*/
$statement = $con ->prepare('SELECT * FROM coordinacion_dems WHERE id_usuario = "'.$usr_usuario.'"');
$statement->execute();
$found = $statement->fetch();
if($found){
    $statement = $con ->prepare('SELECT demarcaciones FROM coordinacion_dems WHERE id_usuario = "'.$usr_usuario.'"');
    $statement->execute();
    $user_dems = $statement->fetch();
    $user_dems = $user_dems[0];
}



/*
*/

/*carga datos de combo municipios y colonia*/
$statement = $con->prepare('SELECT id_municipio, municipio FROM municipio ORDER BY municipio');
$statement->execute();
$municipios = $statement->fetchAll();

?>

<!DOCTYPE html>
<html lang="es-MX">
<head>
    <meta charset="UTF-8">
    <title>Mapa de calor</title>
    

    <script src="https://polyfill.io/v3/polyfill.min.js?features=default"></script>
   
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <link rel="stylesheet" type="text/css" href="css/normalize.css">
    <link rel="stylesheet" type="text/css" href="css/base.css">
    <link rel="stylesheet" type="text/css" href="css/form.css">
    <link rel="stylesheet" type="text/css" href="css/heatmap.css">
    <link rel="stylesheet" type="text/css" href="css/mapa-calor.css">
    
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
                            <option value="<?php echo $municipio['id_municipio']; ?>"><?php echo utf8_encode($municipio['municipio']) ?></option>   
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
               <div id="floating-panel">
                
                    <div class="button__div" id="change-radius"><p>Cambiar área de influencia</p> </div>
                    <div class="button__div" id="change-opacity"><p>Cambiar opacidad de calor</p></div>
                
            
            
            </div>
                <div id="map"></div>        
            </div>
        </div>
        
    </div>
    
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.27.0/moment.min.js"></script>
    
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.3/jquery.min.js"></script>
    
    <script src="https://maps.googleapis.com/maps/api/js?key=API_KEY&callback=initMap&libraries=visualization&v=weekly&loading=async" defer></script>

    <script type="text/javascript">
        $('#menu_cartografia').addClass("menu_seleccion");
        bandera_mun = 0;
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
        
        
        /*****JS PARA HEATMAP*****/
        let map, heatmap;

        function heatmapF(map){
            
            heatmap = new google.maps.visualization.HeatmapLayer({
            data: getPoints("1"),
            map: map,
            });
            
        }
        
        coordinates = getPoints("0");
        if(coordinates.length === 0){
            $('#registro').val(0);
            $('#div__afiliacion').hide();
            coordinates = getPoints("1");    
        }
  /*      
        if(coordinates.length === 0){
            $('#registro').val(0)
            $("#div__afiliacion").hide();
            getPoints("1");
            heatmap.setData(getPoints("1"));
        }
*/
        function initMap() {
            
            /*
            var bounds = new google.maps.LatLngBounds();
            for (var i = 0; i < coordinates.length; i++) {
                bounds.extend(new google.maps.LatLng(coordinates[i].lat, coordinates[i].lng));
            }
            map = new google.maps.Map(document.getElementById("map"), {
            
                center: bounds.getCenter(),
                zoom: 10,
            //center: { lat: 21.475318, lng: -104.8783707 },
            });
            map.fitBounds(bounds);*/
          /*  if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function(position) {
                  var latitude = position.coords.latitude;
                  var longitude = position.coords.longitude;

                  // Create a LatLng object
                  var myLatLng = new google.maps.LatLng(latitude, longitude);

                  // Set the map center to user's location
                  map.setCenter(myLatLng);


                }, function() {
                  // Handle geolocation error
                  console.log('Error: The Geolocation service failed.');
                });
          } else {
            // Browser doesn't support geolocation
            console.log('Error: Your browser doesn\'t support geolocation.');
          }*/
          

  map = new google.maps.Map(document.getElementById("map"), {
    center: { lat: 21.5100274, lng: -104.8783707 },
    zoom: 12,
  });
            
            //heatmapF(map);
            heatmap = new google.maps.visualization.HeatmapLayer({
            data: getPoints("1"),
            map: map,
            });
            document.getElementById("change-opacity").addEventListener("click", changeOpacity);
            document.getElementById("change-radius").addEventListener("click", changeRadius);
            
           
           //opciones(map);
        }
        
        /*function opciones(map){
           
            map.setCenter({lat: 21.4806399, lng: -104.8804897});
            console.log(map.getCenter().lat()); 
            console.log(map.getZoom()); 
        }*/
        
        function changeRadius() {
            heatmap.set("radius", heatmap.get("radius") ? null : 20);
        }

        function changeOpacity() {
            heatmap.set("opacity", heatmap.get("opacity") ? null : 0.2);
        }

        /*****JS PARA OBTENER COORDENADAS DE BD*****/
        function getPoints(bandera_centro) {
            colonia = bandera_mun === 1 ? "0" : $('#colonia option:selected').text();
            <?php if(isset($user_dems)):?>
                params = {
                    registro: $('#registro').val(),
                    afiliacion: $('#afiliacion').val(),
                    municipio: $('#municipio option:selected').text(),
                    colonia: colonia,
                    seccion: $('#seccion').val(),
                    demarc: $('#demarc').val(),
                    user_dems: '<?php echo $user_dems;?>',
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
            
            coords = coordinates = points = [];
            var json = "";
            $.ajax({
                type: 'POST',
                url: 'ajax/coordenadas.php', 
                data: params,
                //dataType: "json",
                async: false,
                success: function(data){
                    
                    if(data === ""){
                    } else {
                        json = jQuery.parseJSON(data);
                        for (var i=0; i < json.length; i++) {
                            if(bandera_centro != "0"){
                            coords = json[i];
                            points.push(new google.maps.LatLng(coords.lat, coords.lng));
                            }
                            else {
                                coordinates.push(json[i]);
                            }
                            
                        }
                    }
                }
            });
            if(bandera_centro != "0"){
            return points;} else{ return coordinates; }
        }
        
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
        
        /*Banderas para saber si cambio municipio o colonia*/
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
            getPoints("1");
            heatmap.setData(getPoints("1"));
        });
        $('#seccion').on('keyup', function(){
            getPoints("1");
            heatmap.setData(getPoints("1"));
        });
        
        /*Resetea los campos de filtro y reinicia la tabla de personas*/
        $("#filter__reload").click(function() {
            $("#afiliacion").val('2');
            $("#municipio").val('0');
            $("#seccion").val('0');
            $("#zona").html('<option value="0">- Zona -</option>');
            $("#colonia").html('<option value="0">- Colonia -</option>');
            getPoints("1");
            heatmap.setData(getPoints("1"));
        });

    </script>
</body>
</html>