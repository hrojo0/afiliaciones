<?php
$session_lifetime = 3600 * 24 * 4; // 4 days
session_set_cookie_params ($session_lifetime);
session_start();
require 'conexion_db.php';
require 'functions.php';
comprobar_login();

//print_r($_SESSION['usuario_registrado']);
require "includes/session.php";
//$_SESSION['usuario_registrado'] = 'no';

// Define el tiempo de expiración de la cookie (por ejemplo, 30 días)
$tiempo_expiracion = time() + (4 * 24 * 60 * 60); // 4 días en segundos

// Define el nombre y el valor de la cookie
$nombre_cookie = "sesion_usuario";
$valor_cookie = "activo";

// Establece la cookie con el tiempo de expiración
setcookie($nombre_cookie, $valor_cookie, $tiempo_expiracion, '/'); // '/' indica que la cookie está disponible en todo el dominio






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

//echo $usr_usuario.' | '.$usr_nombre.' | '.$usr_apellidos.' | '.$usr_user.' | '.$usr_foto.' | '.$usr_nivel.' | '.$usr_mun.' | '.$usr_dem.' | '.$usr_zona;

if(!$con){
    $respuesta = ['error' => true];
} else{

    require "includes/switch-sql.php";
    
    /*carga datos de combo municipios y colonia*/
    $statement = $con->prepare('SELECT m.id_municipio, m.municipio FROM municipio m ORDER BY m.municipio');
    $statement->execute();
    $municipios = $statement->fetchAll();

    $statement = $con->prepare('SELECT cp.id_cp_colonia, cp.colonia FROM cp_colonia cp ORDER BY cp.colonia;');
    $statement->execute();
    $colonias = $statement->fetchAll();

    //TOTAL DE REGISTROS
    $statement = $con->prepare('SELECT COUNT(*) as cantidad from persona p'.$extra_sql.';');
    $statement->execute();
    $total_registros = $statement->fetch();
    
    //TOTAL DE REGISTROS
    $dq="";
    if($usr_nivel == "8"){
        $statement = $con->prepare('SELECT COUNT(*) as cantidad from persona p'.$extra_sql.' AND p.cve_elec != "0"'); 
    } else{
        $statement = $con->prepare('SELECT COUNT(*) as cantidad from persona p'.$extra_sql.' AND p.cve_elec = "0"');
    }
    
    $statement->execute();
    $total_aspirantes = $statement->fetch(); 
    
    //AFILIADOS
    $statement = $con->prepare('SELECT p.afiliacion, COUNT(*) as cantidad from persona p'.$extra_sql.' AND p.afiliacion = "1"'); //GROUP BY p.afiliacion;
    $statement->execute();
    $total_afiliados = $statement->fetchAll();
    
    
    //AFILIADOS POR SEXO
    $statement = $con->prepare('SELECT p.afiliacion, SUM(CASE WHEN p.sexo = "M" THEN 1 END) AS hombres, SUM(CASE WHEN p.sexo = "F" THEN 1 END) AS mujeres FROM persona p '.$extra_sql.' AND p.afiliacion = 1 GROUP BY  p.afiliacion = 1;');
    $statement->execute();
    $total_afiliados_sexo = $statement->fetchAll(); 
    //print_r($total_afiliados_sexo);
    
    //ASPIRANTES POR SEXO
    $statement = $con->prepare('SELECT SUM(CASE WHEN p.sexo = "M" THEN 1 END) AS hombres, SUM(CASE WHEN p.sexo = "F" THEN 1 END) AS mujeres FROM persona p '.$extra_sql.' AND p.cve_elec = "0"');
    $statement->execute();
    $total_aspirantes_sexo = $statement->fetchAll(); 
    //print_r($total_aspirantes_sexo);
    
    //EDAD AFILIADOS
    $statement = $con->prepare('SELECT floor(DATEDIFF(CAST(UTC_DATE() AS DATE), p.f_nac) / 365.25) AS edad, COUNT(*) AS cantidad FROM persona p'.$extra_sql.' AND p.afiliacion = "1" GROUP BY edad ORDER BY cantidad DESC;');
    $statement->execute();
    $total_edad_afiliados = $statement->fetchAll(); 
    
    //COLONIAS TOP
    $statement = $con->prepare('SELECT p.ciudad, p.estado, p.colonia, COUNT(*) as cantidad from persona p'.$extra_sql.' AND p.afiliacion = "1" GROUP BY p.colonia ORDER BY cantidad DESC LIMIT 10;');
    $statement->execute();
    $total_cols_top = $statement->fetchAll(); 
    
    //COLONIAS LOWER
    $statement = $con->prepare('SELECT p.ciudad, p.estado, p.colonia, COUNT(*) as cantidad from persona p'.$extra_sql.' AND p.afiliacion = "1" GROUP BY p.colonia ORDER BY cantidad ASC LIMIT 10;');
    $statement->execute();
    $total_cols_lower = $statement->fetchAll(); 
    
    //SECCIONES TOP
    $statement = $con->prepare('SELECT p.seccion, COUNT(*) as cantidad from persona p'.$extra_sql.' AND p.afiliacion = "1" GROUP BY p.seccion ORDER BY cantidad DESC LIMIT 10;');
    $statement->execute();
    $total_seccs_top = $statement->fetchAll(); 
    
    //SECCIONES LOWER
    $statement = $con->prepare('SELECT p.seccion, COUNT(*) as cantidad from persona p'.$extra_sql.' AND p.afiliacion = "1" GROUP BY p.seccion ORDER BY cantidad ASC LIMIT 10;');
    $statement->execute();
    $total_seccs_lower = $statement->fetchAll(); 
    
    //PERSONAS POR RANGO DE EDAD
    //MENOS DE 18 AÑOS
    $statement = $con->prepare('SELECT COUNT(*) AS cantidad FROM persona p'.$extra_sql.' AND (DATEDIFF(CAST(UTC_DATE() AS DATE), p.f_nac) / 365.25) < 18 AND p.afiliacion = "1";');
    $statement->execute();
    $total_menores = $statement->fetch(); 
    
    //18 a 24
    $statement = $con->prepare('SELECT COUNT(*) AS cantidad FROM persona p'.$extra_sql.' AND (DATEDIFF(CAST(UTC_DATE() AS DATE), p.f_nac) / 365.25) > 18 AND (DATEDIFF(CAST(UTC_DATE() AS DATE), p.f_nac) / 365.25) < 25 AND p.afiliacion = "1";');
    $statement->execute();
    $total_18a24 = $statement->fetch(); 


    //25 a 34
    $statement = $con->prepare('SELECT COUNT(*) AS cantidad FROM persona p'.$extra_sql.' AND (DATEDIFF(CAST(UTC_DATE() AS DATE), p.f_nac) / 365.25) > 25 AND (DATEDIFF(CAST(UTC_DATE() AS DATE), p.f_nac) / 365.25) < 34 AND p.afiliacion = "1";');
    $statement->execute();
    $total_25a34 = $statement->fetch(); 


    //35 a 44
    $statement = $con->prepare('SELECT COUNT(*) AS cantidad FROM persona p'.$extra_sql.' AND (DATEDIFF(CAST(UTC_DATE() AS DATE), p.f_nac) / 365.25) > 35 AND (DATEDIFF(CAST(UTC_DATE() AS DATE), p.f_nac) / 365.25) < 44 AND p.afiliacion = "1";');
    $statement->execute();
    $total_35a44 = $statement->fetch(); 


    //45 a 54
    $statement = $con->prepare('SELECT COUNT(*) AS cantidad FROM persona p'.$extra_sql.' AND (DATEDIFF(CAST(UTC_DATE() AS DATE), p.f_nac) / 365.25) > 45 AND (DATEDIFF(CAST(UTC_DATE() AS DATE), p.f_nac) / 365.25) < 54 AND p.afiliacion = "1";');
    $statement->execute(); 
    $total_45a54 = $statement->fetch(); 


    //55 a 64
    $statement = $con->prepare('SELECT COUNT(*) AS cantidad FROM persona p'.$extra_sql.' AND (DATEDIFF(CAST(UTC_DATE() AS DATE), p.f_nac) / 365.25) > 55 AND (DATEDIFF(CAST(UTC_DATE() AS DATE), p.f_nac) / 365.25) < 64 AND p.afiliacion = "1";');
    $statement->execute(); 
    $total_55a64 = $statement->fetch(); 


    //65 o mas
    $statement = $con->prepare('SELECT COUNT(*) AS cantidad FROM persona p'.$extra_sql.' AND (DATEDIFF(CAST(UTC_DATE() AS DATE), p.f_nac) / 365.25) > 65 AND p.afiliacion = "1";');
    $statement->execute(); 
    $total_65mas = $statement->fetch(); 
    
    //array con datos de edades
    $total_edades = [
        ['rango' => 'Menor de 18', 'cantidad' => $total_menores[0]],
        ['rango' => 'De 18 a 24', 'cantidad' => $total_18a24[0]],
        ['rango' => 'De 25 a 34', 'cantidad' => $total_25a34[0]],
        ['rango' => 'De 34 a 44', 'cantidad' => $total_35a44[0]],
        ['rango' => 'De 45 a 54', 'cantidad' => $total_45a54[0]],
        ['rango' => 'De 55 a 64', 'cantidad' => $total_55a64[0]],
        ['rango' => '65 o más', 'cantidad' => $total_65mas[0]]
    ];
    
    //BUSCADOR DE COLONIAS
    $statement = $con->prepare('SELECT p.colonia, p.ciudad, p.estado, p.seccion from persona p'.$extra_sql.' AND p.seccion LIKE "%746%" GROUP BY p.colonia;');
    $statement->execute();
    $cols_secc = $statement->fetchAll(); 
}
?>

<!DOCTYPE html>
<html lang="es-MX">
<head>
    <meta charset="UTF-8">
    <title>Index</title>
    

    <script src="https://polyfill.io/v3/polyfill.min.js?features=default"></script>
   
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- INICIALIZAR PYTHON -->
    <!--link rel="stylesheet" href="https://pyscript.net/alpha/pyscript.css" />
    <script defer src="https://pyscript.net/alpha/pyscript.js"></script-->
    
    <link rel="stylesheet" type="text/css" href="css/normalize.css">
    <link rel="stylesheet" type="text/css" href="css/base.css">
    <link rel="stylesheet" type="text/css" href="css/form.css">
    <link rel="stylesheet" type="text/css" href="css/sidebar-menu.css">
    <link rel="stylesheet" type="text/css" href="css/histograma.css">
    <link rel="stylesheet" type="text/css" href="css/index.css">
    
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
          <div class="titulo"><p>Reporte general</p></div>
           <div class="info_general">
               <div class="personas_registradas gen">
                  <p class="subtitle">Personas registradas en plataforma</p>
                   <div class="ajuste_info_general">
                       <div class="total"><p><?php echo $total_registros[0]-$total_aspirantes['cantidad']; ?></p></div>
                       <div class="txt"><p>Personas registradas</p><p>en la plataforma</p></div>
                   </div>
                   
                   <div class="nexos"><p>de las cuales</p></div>
                   
                   <div class="ajuste_info_general">
                       <div class="total "><p><?php echo $total_afiliados[0]['cantidad']; ?></p></div>
                       <div class="txt"><p>Personas están</p><p>afiliadas a GP</p></div>
                   </div>
                   
                   <div class="ajuste_info_general" id="por_afi">
                       <div class="total "><p><?php 
                           $tot_temp = $total_registros[0] == 0 ? 1: $total_registros[0];  
                           
                           echo round(($total_afiliados[0]['cantidad']*100)/($tot_temp-$total_aspirantes['cantidad']),2); 
                           
                           ?>%</p></div>
                       <div class="txt"><p>Personas</p><p>afiliadas</p></div>
                   </div>
               </div>
               
               <!-- AFILIADOS POR SEXO -->
               <div class="afi_sexo gen">
                  <p class="subtitle">Afiliados por sexo</p>
                   <div class="afiliados margen first">
                       <div class="ajuste_info_general">
                           <div class="total afi"><p><?php
                               $total_afi_sex = empty($total_afiliados_sexo) || $total_afiliados_sexo[0]['hombres'] == "" ? 0 : $total_afiliados_sexo[0]['hombres'];
                               $tot_temp = $total_registros[0] == 0 ? 0: $total_afi_sex;
                               echo $tot_temp;
                            ?></p></div>
                           <div class="txt"><p>Hombres</p><p>afiliados</p></div>
                       </div>

                       <div class="ajuste_info_general">
                           <div class="total afi mujeres"><p><?php 
                               $total_afi_sex = empty($total_afiliados_sexo) ? 0: $total_afiliados_sexo[0]['hombres'];

                               $total_afi = $total_afiliados[0]['afiliacion'] == 0 ? 1: $total_afiliados[0]['cantidad'];


                               $tot_temp = $total_registros[0] == 0 ? 1: $total_afi;
                               $tot_tmp_hombre = $total_registros[0] == 0 ? 0: $total_afi_sex;


                               echo round(($tot_tmp_hombre*100)/$tot_temp,2); 
                            ?>%</p></div>
                           <div class="txt"><p>Hombres</p><p>afiliados</p></div>
                       </div>
                   </div>

                   <div class="afiliados margen">
                   <div class="ajuste_info_general">
                       <div class="total afi mujeres"><p><?php
                           $total_afi_sex = empty($total_afiliados_sexo) || $total_afiliados_sexo[0]['mujeres'] == "" ? 0: $total_afiliados_sexo[0]['mujeres'];
                           $tot_temp = $total_registros[0] == 0 ? 0: $total_afi_sex;
                           echo $tot_temp; 
                        ?></p></div>
                       <div class="txt"><p>Mujeres</p><p>afiliadas</p></div>
                   </div>
                   
                   <div class="ajuste_info_general">
                       <div class="total afi mujeres"><p><?php 
                           $total_afi_sex = empty($total_afiliados_sexo) ? 0: $total_afiliados_sexo[0]['mujeres'];
                           
                           $total_afi = $total_afiliados[0]['afiliacion'] == 0 ? 1: $total_afiliados[0]['cantidad'];
                           
                           $tot_temp = $total_registros[0] == 0 ? 1: $total_afi;
                           $tot_tmp_mujer = $total_registros[0] == 0 ? 0: $total_afi_sex;

                           echo round(($tot_tmp_mujer*100)/$tot_temp,2);
                        ?>%</p></div>
                       <div class="txt"><p>Mujeres</p><p>afiliadas</p></div>
                   </div>
               </div>
               </div>
               
               <!--    EDADES DE LOS AFILIADOS -->
               <div class="afi_edades gen">
                  <p class="subtitle">Edades de los afiliados</p>
                   <div class="personas_registradas margen first">
                       <div class="ajuste_info_general">
                          <div class="total"><p><?php 
                           $sum_edad_afi = 0;
                           $total_afi = 0;
                           foreach($total_edad_afiliados as $edad_afiliados){
                               $sum_edad_afi = $sum_edad_afi + $edad_afiliados['edad'] * $edad_afiliados['cantidad'];
                               $total_afi = $total_afi + $edad_afiliados['cantidad'];
                           }
                           $total_afi = $total_afi == 0 ? 1 : $total_afi;
                           $prom_edad_afi = $sum_edad_afi / $total_afi;
                           echo round($prom_edad_afi, 0).' años';
                        ?></p></div>
                            <div class="txt"><p>Edad promedio</p><p>de personas afiliadas</p></div>
                       </div>

                   </div>

                   <div class="personas_registradas margen">
                       <div class="ajuste_info_general">
                          <div class="total"><p><?php 
                             $tot_edad_afi = empty($total_edad_afiliados) ? 0: $total_edad_afiliados[0]['edad'];



                           $tot_temp = $total_registros[0] == 0 ? 0: $tot_edad_afi;
                           echo $tot_temp;
                        ?> años</p></div>
                           <div class="txt"><p>Edad más frecuente</p><p>de personas afiliadas</p></div>
                       </div>

                   </div>
               </div>
               
               <!-- ASPIRANTES POR SEXO -->
               <div class="afi_sexo gen">
                  <p class="subtitle">Aspirantes por sexo</p>
                   <div class="afiliados margen first">
                       <div class="ajuste_info_general">
                           <div class="total afi"><p><?php
                               $total_asp_sex = empty($total_aspirantes_sexo) || $total_aspirantes_sexo[0]['hombres'] == "" ? 0 : $total_aspirantes_sexo[0]['hombres'];
                               $tot_temp = $total_aspirantes[0] == 0 ? 0: $total_asp_sex;
                               echo $tot_temp;
                            ?></p></div>
                           <div class="txt"><p>Hombres</p><p>aspirantes</p></div>
                       </div>

                       <div class="ajuste_info_general">
                           <div class="total afi mujeres"><p><?php 
                               $total_asp_sex = empty($total_aspirantes_sexo) || $total_aspirantes_sexo[0]['hombres'] == "" ? 0: $total_aspirantes_sexo[0]['hombres'];

                               $total_asp = empty($total_aspirantes) || $total_aspirantes['cantidad'] == 0 ? 1: $total_aspirantes['cantidad'];

                               echo round(($total_asp_sex*100)/$total_asp,2); 
                            ?>%</p></div>
                           <div class="txt"><p>Hombres</p><p>aspirantes</p></div>
                       </div>
                   </div>
                   
                   
                   <div class="afiliados margen">
                       <div class="ajuste_info_general">
                           <div class="total afi"><p><?php
                               $total_asp_sex = empty($total_aspirantes_sexo) || $total_aspirantes_sexo[0]['mujeres'] == "" ? 0 : $total_aspirantes_sexo[0]['mujeres'];
                               $tot_temp = $total_aspirantes[0] == 0 ? 0: $total_asp_sex;
                               echo $tot_temp;
                            ?></p></div>
                           <div class="txt"><p>Mujeres</p><p>aspirantes</p></div>
                       </div>

                       <div class="ajuste_info_general">
                           <div class="total afi mujeres"><p><?php 
                               $total_asp_sex = empty($total_aspirantes_sexo) || $total_aspirantes_sexo[0]['mujeres'] == "" ? 0: $total_aspirantes_sexo[0]['mujeres'];

                               $total_asp = empty($total_aspirantes) || $total_aspirantes['cantidad'] == 0 ? 1: $total_aspirantes['cantidad'];

                               echo round(($total_asp_sex*100)/$total_asp,2); 
                            ?>%</p></div>
                           <div class="txt"><p>Mujeres</p><p>aspirantes</p></div>
                       </div>
                   </div>
                  
               </div>
           </div>
           
           <div class="info_general">
               
               
           </div>
           
           <!-- GRÁFICAS GENERALES Y BUSCADOR DE COLONIAS -->
            <div class="container">
                <div class="inner">
                    <div class="graficas">
                     
                     <!-- EDADES -->
                        <div class="histo">
                            <h2>Afiliados por rango de edad</h2>
                            <?php $contador = 1; foreach($total_edades as $total_edad) :?>
                            <div class="four histo-rate">
                                <span class="histo-star"><?php echo $total_edad['rango']; ?>            </span>
                                <span class="bar-block">
                                    <span id="age-<?php echo $contador++;?>" class="bar">
                                        <p class="cantidad"><?php echo $total_edad['cantidad']; ?>personas</p>
                                        <p class="porcentaje"><?php
                                            
                                            $tot_temp = $total_registros[0] == 0 || empty($total_registros) ? 1: ($total_afiliados[0]['cantidad'] == 0 ? 1: $total_afiliados[0]['cantidad']);
                                            
                                            
                                            $tot_temp_edad = $total_registros[0] == 0 ? 0: $total_edad['cantidad'];
                                            
                                            //echo $tot_temp;
                                            echo number_format((float)($tot_temp_edad*100)/$tot_temp, 2, '.', '');?>%</p>
                                    </span> 
                                </span>
                            </div> 
                            <?php endforeach;?>
                        </div>
                        
                      <!-- COLONIAS TOP -->
                        <div class="histo">
                            <h2>Colonias con mayor afiliación</h2>
                            <?php $contador = 1; foreach($total_cols_top as $col_top) :?>
                            <div class="four histo-rate">
                                <a href="https://www.google.com/maps/place/<?php echo $col_top['colonia'].'; '.$col_top['ciudad'].', '.$col_top['estado']; ?>" target="_blank"><span class="link_col histo-star"><?php echo $col_top['colonia'].'; '.$col_top['ciudad'].', '.$col_top['estado']; ?>            </span></a>
                                <span class="bar-block">
                                    <span id="colT-<?php echo $contador++;?>" class="bar">
                                        <p class="cantidad"><?php echo $col_top['cantidad']; ?>personas</p>
                                        <p class="porcentaje"><?php echo number_format((float)($col_top['cantidad']*100)/$total_afiliados[0]['cantidad'], 2, '.', '');?>%</p>
                                    </span> 
                                </span>
                            </div> 
                            <?php endforeach;?>
                        </div>
                        
                        <!-- COLONIAS LOWER -->
                        <div class="histo">
                            <h2>Colonias con menor afiliación</h2>
                            <?php $contador = 1; foreach($total_cols_lower as $col_lower) :?>
                            <div class="four histo-rate">
                                <a href="https://www.google.com/maps/place/<?php echo $col_lower['colonia'].'; '.$col_lower['ciudad'].', '.$col_lower['estado']; ?>" target="_blank"><span class="link_col histo-star"><?php echo $col_lower['colonia'].'; '.$col_lower['ciudad'].', '.$col_lower['estado']; ?>            </span></a>
                                <span class="bar-block">
                                    <span id="colL-<?php echo $contador++;?>" class="bar">
                                        <p class="cantidad"><?php echo $col_lower['cantidad']; ?>personas</p>
                                        <p class="porcentaje"><?php echo number_format((float)($col_lower['cantidad']*100)/$total_afiliados[0]['cantidad'], 2, '.', '');?>%</p>
                                    </span> 
                                </span>
                            </div> 
                            <?php endforeach;?>
                        </div>
                        
                        <!-- SECCIONES TOP -->
                        <div class="histo">
                            <h2>Secciones con mayor afiliación</h2>
                            <?php $contador = 1; foreach($total_seccs_top as $secc_top) :?>
                            <div class="four histo-rate">
                                <span class="histo-star"><?php echo $secc_top['seccion']; ?>            </span>
                                <span class="bar-block">
                                    <span id="seccT-<?php echo $contador++;?>" class="bar">
                                        <p class="cantidad"><?php echo $secc_top['cantidad']; ?>personas</p>
                                        <p class="porcentaje"><?php echo number_format((float)($secc_top['cantidad']*100)/$total_afiliados[0]['cantidad'], 2, '.', '');?>%</p>
                                    </span> 
                                </span>
                            </div> 
                            <?php endforeach;?>
                        </div>
                        
                        <!-- SECCIONES LOWER -->
                        <div class="histo">
                            <h2>Secciones con menor afiliación</h2>
                            <?php $contador = 1; foreach($total_seccs_lower as $secc_lower) :?>
                            <div class="four histo-rate">
                                <span class="histo-star"><?php echo $secc_lower['seccion']; ?>            </span>
                                <span class="bar-block">
                                    <span id="seccL-<?php echo $contador++;?>" class="bar">
                                        <p class="cantidad"><?php echo $secc_lower['cantidad']; ?>personas</p>
                                        <p class="porcentaje"><?php echo number_format((float)($secc_lower['cantidad']*100)/$total_afiliados[0]['cantidad'], 2, '.', '');?>%</p>
                                    </span> 
                                </span>
                            </div> 
                            <?php endforeach;?>
                        </div>
                        
                        <div class="buscador_seccion">
                           <h2>buscador de colonias registradas por sección</h2>
                            <div class="form__div div__seccion ">

                                <input maxlength="3" type="number" placeholder=" " id="seccion" name="seccion" class="form__input filter"/>
                                <label class="full-field form__label label__seccion">Sección</label>

                                <div class="colonias_seccion">
                                    <p id="colonia_title"></p>
                                    <div id="colonias"></div>
                                </div>
                            </div>
                        </div>
                        
                   
                   
                    </div>
                </div>
                
                
            </div>
           
           <!-- REPORTE PERSONAS DE ACUERDO A LOS FILTROS APLICADOS -->
           <div class="info_combos">
               <div class="titulo_datos_filtrados">
                   <h2>Reporte de personas acorde a los siguientes filtros</h2>
               </div>
               <div class="control">
                    <div class="form__div">
                        <div class="combo combo-registro">
                            <select class="form__input filter__registro filter" id="registro" name="registro">
                              <option value="1">Afiliados/No afiliados</option>
                              <option value="0">Aspirantes</option>   
                            </select>
                            <label class="form__label" for="registro">Registro</label>
                        </div>
                    </div>
                    
                    <!--div class="form__div" id="div__afiliacion">
                        <div class="combo combo-afiliacion">
                            <select class="form__input filter__afiliacion filter" id="afiliacion" name="afiliacion">
                              <option value="2">- Afiliado -</option>
                              <option value="1">Si</option>
                              <option value="0">No</option>   
                            </select>
                            <label class="form__label" for="afiliacion">Afiliado</label>
                        </div>
                    </div-->
                    
                    <div class="form__div ">
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
                            <label class="form__label label__seccion" for="edad">Edad</label>
                        </div>
                    </div>

                    <div class="form__div ">
                        <div class="combo combo-afiliacion">
                            <select class="form__input filter_municipio filter" id="municipio" name="municipio">
                                <option value="0">- Municipio -</option>
                                <?php foreach($municipios as $municipio):?>
                                <option value="<?php echo $municipio['id_municipio']; ?>"><?php echo $municipio['municipio'] ?></option>   
                                <?php endforeach;?>
                            </select>
                            <label class="form__label label__seccion" for="municipio">Municipio</label>
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
                        <div class="combo combo-seccion">
                            <select class="form__input seccion filter" id="zona" name="zona">
                                <option value="0">- Zona -</option>
                            </select>
                            <label class="form__label sexo_afiliacion" for="seccion">Zona</label>

                        </div>
                    </div>
                   
                    <div class="form__div">
                        <div class="combo combo-seccion">
                            <select class="form__input seccion filter" id="seccion_filter" name="seccion">
                                <option value="0">- Sección -</option>
                            </select>
                            <label class="form__label sexo_afiliacion" for="seccion">Sección</label>

                        </div>
                    </div>
                

                    <div class="form__div ">
                        <div class="combo combo-promotor">
                            <select class="form__input filter_colonia filter" id="colonia" name="colonia">
                                <option value="0">- Colonia -</option>
                            </select>
                            <label class="form__label label__seccion" for="">Colonia</label>
                        </div>
                    </div>

                    <!--div class="form__div div__seccion div__control">
                        <input maxlength="3" type="number" placeholder=" " id="seccion_filter" name="seccion" class="form__input filter"/>
                        <label class="full-field form__label label__seccion">Sección</label>
                    </div-->

                    <div id="filter__reload" class="form__button">
                        <i class="fa-solid fa-rotate-left"></i>
                    </div>
                </div>
                
                
                
                <!-- RERPORTE PERSONAS DATOS FILTRADOS -->
               <div class="datos_filtrados" id="datos_filtrados">
                  <div id="datos_filtrados_no_data" style="display: none">
                      <div class="filter__error">
                          <i class="fa-solid fa-triangle-exclamation"></i><p>No se han encontrado registros con los filtros solicitados</p></div>
                  </div>
                  
                  <div id="resultado_datos_filtrados">
                      <div class="afi_sexo gen">
                          <p class="subtitle">Personas</p>
                           <div class="afiliados margen first">
                               <div class="ajuste_info_general">
                                   <div class="total afi" id="filter__cant__personas"></div>
                                   <div class="txt"><p>Personas</p>registradas</div>
                               </div>
                           </div>
                           
                           <div class="nexos" id="nexos"><p>de las cuales</p></div>
                   
                           <div class="ajuste_info_general" id="filter__info__total__personas">
                               <div class="total " id="filter__total__afi"></div>
                               <div class="txt"><p>Personas están</p><p>afiliadas a GP</p></div>
                           </div>

                           <div class="ajuste_info_general" id="filter__info__total__personas__porc">
                               <div class="total " id="filter__total__afi__porc"></div>
                               <div class="txt"><p>Personas</p><p>afiliadas</p></div>
                           </div>
                       </div> 

                      <div class="afi_sexo gen">
                          <p class="subtitle">Edad promedio</p>
                           <div class="afiliados margen first">
                               <div class="ajuste_info_general">
                                   <div class="total afi" id="filter__edad__prom"></div>
                                   <div class="txt"><p>Edad</p><p>promedio</p></div>
                               </div>
                           </div>
                       </div>


                      <div class="afi_sexo gen">
                          <p class="subtitle"><span id="filter__type"></span> por sexo</p>
                           <div class="afiliados margen first">
                               <div class="ajuste_info_general">
                                   <div class="total afi" id="filter__hombres"></div>
                                   <div class="txt"><p>Hombres</p><p id="filter__type__hombres"></p></div>
                               </div>

                               <div class="ajuste_info_general">
                                   <div class="total afi mujeres" id="filter__hombres__porc"></div>
                                   <div class="txt"><p>Hombres</p><p id="filter__type__hombres__porc"></p></div>
                               </div>
                           </div>


                           <div class="afiliados margen">
                               <div class="ajuste_info_general">
                                   <div class="total afi" id="filter__mujeres"></div>
                                   <div class="txt"><p>Mujeres</p><p id="filter__type__mujeres"></p></div>
                               </div>

                               <div class="ajuste_info_general">
                                   <div class="total afi mujeres" id="filter__mujeres__porc"></div>
                                   <div class="txt"><p>Mujeres</p><p id="filter__type__mujeres__porc"></p></div>
                               </div>
                           </div>

                       </div>
                  </div>
                  
    
               </div>
           </div>
           
           
           
            
            
            <!--PYTHON-->
            <!--py-script>
                print('<h1>Hello World</h1>')
            </py-script-->
        </div>
    </div>
    
    
    
    <script type="text/javascript">
    $(document).ready(function() {
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
        
        $('#demarc').on('change', function(){
            $('#seccion_filter').html('<option value="0">- Sección -</option>');
            seccs_temp = "";
            $.post("ajax/secciones.php", {mun: $('#municipio option:selected').text(), dem: $('#demarc option:selected').val() }, function(data){
                
                json = jQuery.parseJSON(data);
                for(i = 0; i< json.length; i++){
                    $('#seccion_filter').append($('<option>', {
                        value: json[i]['secc'],
                        text: json[i]['secc']
                    }));
                    seccs_temp = seccs_temp + json[i]['secc'] + ",";
                }
                $('#seccion_filter').val(seccs_temp);
                $('#seccion_filter').val('0');
            });
        });
        
        
        $('#menu_inicio').addClass("menu_seleccion");
        $('.bar span').hide();
        //COLONIAS TOP
        contador = 1;
        <?php $tot_temp = $total_registros[0] == 0 ? 1: $total_registros[0]; ?>
        <?php foreach($total_cols_top as $col_top):?>
            id = '#colT-'+contador++;
            $(id).animate({
             width: '<?php echo (($col_top['cantidad']*100)/$tot_temp)*2 ?>%'}, 1000);
        <?php endforeach; ?>
        
        //COLONIAS LOWER
        contador = 1;
        <?php foreach($total_cols_lower as $col_lower):?>
            id = '#colL-'+contador++;
            $(id).animate({
             width: '<?php echo (($col_lower['cantidad']*100)/$tot_temp)*2 ?>%'}, 1000);
        <?php endforeach; ?>
        
        //SECCIONES TOP
        contador = 1;
        <?php foreach($total_seccs_top as $secc_top):?>
            id = '#seccT-'+contador++;
            $(id).animate({
             width: '<?php echo (($secc_top['cantidad']*100)/$tot_temp)*2 ?>%'}, 1000);
        <?php endforeach; ?>
        
        //SECCIONES LOWER
        contador = 1;
        <?php foreach($total_seccs_lower as $secc_lower):?>
            id = '#seccL-'+contador++;
            $(id).animate({
             width: '<?php echo (($secc_lower['cantidad']*100)/$tot_temp)*2 ?>%'}, 1000);
        <?php endforeach; ?>
        
        //GRAFICA RANGO DE EDAD
        contador = 1;
        <?php foreach($total_edades as $total_edad):?>
            id = '#age-'+contador++;
            $(id).animate({
             width: '<?php echo (($total_edad['cantidad']*100)/$tot_temp)*2 ?>%'}, 1000);
        <?php endforeach; ?>
        
        setTimeout(function() {
        $('.bar span').fadeIn('slow');
        }, 1000);
        
        /******* PENDIENTE DE HACERLO COMO AJAX ******/
        $('#seccion').on('keyup', function(){
            cargaColonias();
        });
        
        
        function cargaColonias(){
            seccion = $('#seccion').val();
            title = $('#colonia_title').text("Colonias");
            $.post("ajax/colonias_por_seccion.php", { seccion: seccion }, function(data){
                if(data === ""){
                    $("#colonias").html('<i class="fa-solid fa-triangle-exclamation"></i><p class="error_seccion">No se han encontrado colonias con la sección especificada</p>');
                } else {
                    $("#colonias").html(data);
                }
            });	

        }
        
        
        /*Carga colonias correspondientes cuando se elige municipio*/
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
                        $("#zona").append('<option value="'+i+'">'+i+'</option>');
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
            cargaFiltros();
            //alert();
        });
        
        /*$('#seccion_filter').on('keyup', function(){
            cargaFiltros();
        });*/
        
        function filter_type(){
            if($('#registro option:selected').val() == 1){
                $('#filter__type').text('Personas');
                $('#filter__type__hombres, #filter__type__hombres__porc').text('afiliados');
                $('#filter__type__mujeres, #filter__type__mujeres__porc').text('afiliadas');
            } else {
                $('#filter__type').text('Aspirantes');
                $('#filter__type__hombres, #filter__type__hombres__porc').text('aspirantes');
                $('#filter__type__mujeres, #filter__type__mujeres__porc').text('aspirantes');
            }
        }
        filter_type();
        $('#registro').on('change', function(){
            filter_type();
        });
        
        function cargaFiltros(){
            registro = $('#registro').val();
            afiliacion = $('#afiliacion').val();
            edad = $('#edad').val();
            sexo = $('#sexo').val();
            //afiliacion = $('#afiliacion').val();
            municipio = $('#municipio option:selected').text();
            colonia = bandera_mun === 1 ? "0" : $('#colonia option:selected').text();
            seccion = $('#seccion_filter').val();
            demarc = $('#demarc').val();
            zona = $('#zona').val();
            
            <?php if(isset($user_dems)):?>user_dems = '<?php echo $user_dems;?>';
            <?php else:?>user_dems = "";
            <?php endif;?>
            
             
            $.post("ajax/personas_count.php", { registro: registro, afiliacion:afiliacion, demarc: demarc, zona: zona, edad: edad, sexo: sexo, municipio: municipio, colonia: colonia, seccion: seccion }, function(data){
                
                json = jQuery.parseJSON(data);
                if(json.length === 0){
                    $("#datos_filtrados_no_data").show();
                    $("#resultado_datos_filtrados").hide();
                } else {
                    $("#resultado_datos_filtrados").show();
                    $("#datos_filtrados_no_data").hide();
                    sum_edad = prom_edad = afi_hombres = afi_mujeres = reg_telefono = reg_total = prc_telefono = reg_total = afi_total = total_hombres = total_mujeres = 0;
                    if(registro == "1"){
                        $("#nexos").show();
                        $("#filter__info__total__personas").show();
                        $("#filter__info__total__personas__porc").show();
                        for(i = 0; i< json.length; i++){
                            reg_total += parseInt(json[i]['cantidad']);

                            sum_edad += parseFloat(json[i]['total_edad']);

                            if(json[i]['total_con_telefono'] != '0'){
                                reg_telefono += parseInt(json[i]['cantidad']);
                            }

                            total_hombres += parseInt(json[i]['total_hombres']);
                            total_mujeres += parseInt(json[i]['total_mujeres']);

                            afi_total += parseInt(json[i]['cantidad'] * json[i]['afiliacion']);
                            afi_hombres += parseInt(json[i]['total_hombres'] * json[i]['afiliacion']);
                            afi_mujeres += parseInt(json[i]['total_mujeres'] * json[i]['afiliacion']);

                        }
                        prom_edad = (sum_edad/reg_total) | 0;
                        prc_telefono = ((reg_telefono*100)/reg_total).toFixed(0);
                        //afi_total = afi_hombres + afi_mujeres;
                        prc_hombres = ((afi_hombres*100)/(afi_hombres+afi_mujeres)).toFixed(0);        
                        prc_mujeres = ((afi_mujeres*100)/(afi_hombres+afi_mujeres)).toFixed(0);
                        prc_afi_total = ((afi_total*100)/(reg_total)).toFixed(0);
                    } else {
                        $("#nexos").hide();
                        $("#filter__info__total__personas").hide();
                        $("#filter__info__total__personas__porc").hide();
                        for(i = 0; i< json.length; i++){
                            reg_total += parseInt(json[i]['cantidad']);
                            sum_edad += parseFloat(json[i]['total_edad']); 
                            
                            total_hombres += parseInt(json[i]['total_hombres']);
                            total_mujeres += parseInt(json[i]['total_mujeres']);
                            
                            afi_total += parseInt(json[i]['cantidad']);
                            afi_hombres += parseInt(json[i]['total_hombres']);
                            afi_mujeres += parseInt(json[i]['total_mujeres']);

                        }                        
                        
                        prom_edad = (sum_edad/reg_total) | 0;
                        
                        prc_hombres = ((afi_hombres*100)/(afi_hombres+afi_mujeres)).toFixed(0);        
                        prc_mujeres = ((afi_mujeres*100)/(afi_hombres+afi_mujeres)).toFixed(0);
                        prc_afi_total = ((afi_total*100)/(reg_total)).toFixed(0);
                        
                    }
                    
                    if(afi_hombres+afi_mujeres == 0){prc_hombres = prc_mujeres = "0";}
                    
                    $('#filter__total__afi').html('<p>'+afi_total+'</p>');
                    $('#filter__total__afi__porc').html('<p>'+prc_afi_total+'%</p>');
                    $('#filter__edad__prom').html('<p>'+prom_edad+' años</p>');
                    $('#filter__cant__personas').html('<p>'+reg_total+'</p>');
                    $('#filter__hombres').html('<p>'+afi_hombres+'</p>');
                    $('#filter__hombres__porc').html('<p>'+prc_hombres+'%</p>');
                    $('#filter__mujeres').html('<p>'+afi_mujeres+'</p>');
                    $('#filter__mujeres__porc').html('<p>'+prc_mujeres+'%</p>');

                }
            });	
            
        }
        
        cargaFiltros();
        
        $('#filter__reload').on('click', function(){
            $('#registro').val(1);
            $('#edad').val(0);
            $('#municipio').val(0);
            $('#demarc').html('<option value="0">- Demarcación -</option>');
            $('#zona').html('<option value="0">- Zona -</option>');
            $('#seccion_filter').html('<option value="0">- Sección -</option>');
            $('#colonia').html('<option value="0">- Colonia -</option>');
            cargaFiltros();
        });
        
        $(window).resize(function() {
          // This will execute whenever the window is resized
          wheight = $(window).height(); // New height
          wwidth = $(window).width(); // New width
            
            if(wwidth < 900){
               
            }
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