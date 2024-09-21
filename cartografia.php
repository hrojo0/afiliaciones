<?php
$session_lifetime = 3600 * 24 * 4; // 4 days
session_set_cookie_params ($session_lifetime);
session_start();
require 'conexion_db.php';
require 'functions.php';
comprobar_login();
$_SESSION['usuario_registrado'] = 'no';
require "includes/session.php";
/*carga datos de combo municipios y colonia*/
$statement = $con->prepare('SELECT id_municipio, municipio FROM municipio ORDER BY municipio');
$statement->execute();
$municipios = $statement->fetchAll();

?>

<!DOCTYPE html>
<html lang="es-MX">
<head>
    <meta charset="UTF-8">
    <title>Cartografía</title>
    

    <script src="https://polyfill.io/v3/polyfill.min.js?features=default"></script>
   
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <link rel="stylesheet" type="text/css" href="css/normalize.css">
    <link rel="stylesheet" type="text/css" href="css/base.css">
    <link rel="stylesheet" type="text/css" href="css/sidebar-menu.css">
    <link rel="stylesheet" type="text/css" href="css/cartografia.css">
    <link rel="stylesheet" type="text/css" href="css/form.css">
    
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

        <div class="cont" id="cont_menu">
            <!--iframe src="mapa-calor.php" frameborder="0"></iframe-->
            <div class="heatmap_preview"><div id="abrir_heatmap" class="form__button" onclick=""><i class="fa-solid fa-map"></i><p>Mapa de Calor</p></div><div class="heatmap_img"><img src="img/heatmap.jpg" alt=""><div class="shadow"></div></div></div><div class="markermap_preview"><div id="abrir_markermap" class="form__button" onclick=""><i class="fa-solid fa-location-dot"></i><p>Mapa de Marcadores</p></div><div class="markermap_img"><img src="img/markermap.jpg" alt=""><div class="shadow"></div></div></div>
            
        </div>
        
        <div class="cont" id="cont_heatmap">
            <div id="regresar_heatmap" class="form__button back heatmap_preview" onclick=""><i class="fa-solid fa-angle-left"></i><p>Regresar</p></div><iframe src="mapa-calor.php" frameborder="0"></iframe>
        </div>
        
        <div class="cont" id="cont_markermap">
            <div id="regresar_markermap" class="form__button back heatmap_preview" onclick=""><i class="fa-solid fa-angle-left"></i><p>Regresar</p></div><iframe src="mapa-marcadores.php" frameborder="0"></iframe>
        </div>
        
    </div>
    
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.27.0/moment.min.js"></script>
    
    
    

    <script type="text/javascript">
        $('#menu_cartografia').addClass("menu_seleccion");
        
        $('#cont_heatmap').hide();
        $('#abrir_heatmap, #regresar_heatmap').click(function(){
            $('#cont_menu, #cont_heatmap').toggle();
        });
        
        $('#cont_markermap').hide();
        $('#abrir_markermap, #regresar_markermap').click(function(){
            $('#cont_menu, #cont_markermap').toggle();
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

    </script>
    
    <script type="application/javascript" src="js/popup_usuario.js"></script>
</body>
</html>