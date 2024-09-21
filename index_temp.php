<?php
$session_lifetime = 3600 * 24 * 4; // 4 days
setlocale(LC_TIME, 'es_ES.UTF-8', 'Spanish_Spain.1252'); //set hora local
session_set_cookie_params ($session_lifetime);
session_start();
require 'conexion_db.php';
require 'functions.php';
comprobar_login();

require "includes/session.php";
//$_SESSION['usuario_registrado'] = 'no';










// Obtener fecha actual
date_default_timezone_set('America/Mazatlan');
$fecha_actual = date('Y-m-d');
$dia_semana = ucfirst(strftime('%A', strtotime($fecha_actual)));
$dia_mes = strftime('%e', strtotime($fecha_actual));
$mes =  ucfirst(strftime('%B', strtotime($fecha_actual))); 
$hoy_es = "$dia_semana $dia_mes de $mes";
$hoy_es = utf8_encode($hoy_es);

//días faltantes
$fecha_objetivo = strtotime('2024-06-02');

$diferencia_segundos = $fecha_objetivo - strtotime($fecha_actual);
$diferencia_dias = floor($diferencia_segundos / (60 * 60 * 24));

/*******************************************************************************************/

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
    
    $statement = $con->prepare('SELECT id_cp_colonia, colonia FROM cp_colonia WHERE municipio = "Tepic" ORDER BY colonia ASC');
    $statement->execute();
    $colonias = $statement->fetchAll();


    //TOTAL DE REGISTROS*****************************************************************************
    $statement = $con->prepare('SELECT COUNT(*) as cantidad from persona p'.$extra_sql.';');
    $statement->execute();
    $personas_total = $statement->fetch();
    
    //AFILIADOS**************************************************************************************
    $sql ='
    SELECT
       (SELECT COUNT(*) as cantidad from persona p'.$extra_sql.' AND p.afiliacion = "1") AS total,
       (SELECT SUM(CASE WHEN p.sexo = "M" THEN 1 END) AS hombres FROM persona p '.$extra_sql.' AND p.afiliacion = 1 GROUP BY  p.afiliacion) AS hombres,
       (SELECT SUM(CASE WHEN p.sexo = "F" THEN 1 END) AS mujeres FROM persona p '.$extra_sql.' AND p.afiliacion = 1 GROUP BY  p.afiliacion) AS mujeres,
       (SELECT edad FROM (SELECT floor(DATEDIFF(CAST(UTC_DATE() AS DATE), p.f_nac) / 365.25) AS edad, COUNT(*) AS cantidad FROM persona p '.$extra_sql.' AND p.afiliacion = "1" GROUP BY edad ORDER BY cantidad DESC LIMIT 1) AS temp_sql) AS edad_moda,
       (SELECT floor(AVG(floor(DATEDIFF(CAST(UTC_DATE() AS DATE), p.f_nac) / 365.25))) AS edad FROM persona p '.$extra_sql.' AND p.afiliacion = "1" AND p.sexo = "M") AS edad_promedio_hombres,
       (SELECT floor(AVG(floor(DATEDIFF(CAST(UTC_DATE() AS DATE), p.f_nac) / 365.25))) AS edad FROM persona p '.$extra_sql.' AND p.afiliacion = "1" AND p.sexo = "F") AS edad_promedio_mujeres
    
    ';
    $statement = $con->prepare($sql);
    $statement->execute();
    $afiliados = $statement->fetch();
    
    
    //TOTAL DE ASPIRANTES***********************************************************************************
    $sql = '
    SELECT
        (SELECT COALESCE(COUNT(*), 0) AS cantidad FROM persona p '.$extra_sql.' AND p.cve_elec = "0") AS total,
        (SELECT COALESCE(SUM(CASE WHEN p.sexo = "M" THEN 1 END), 0) AS hombres FROM persona p '.$extra_sql.' AND p.cve_elec = "0") AS hombres,
        (SELECT COALESCE(SUM(CASE WHEN p.sexo = "F" THEN 1 END), 0) AS mujeres FROM persona p '.$extra_sql.' AND p.cve_elec = "0") AS mujeres;
    ';
    $statement = $con->prepare($sql);
    $statement->execute();
    $aspirantes = $statement->fetch(); 
    
    //RANGO EDAD DE AFILIADOS*****************************************************************************
    $sql = '
    SELECT
        (SELECT COUNT(*) FROM persona p '.$extra_sql.' AND (DATEDIFF(CAST(UTC_DATE() AS DATE), p.f_nac) / 365.25) < 18 AND p.afiliacion = "1" AND p.sexo = "M") AS menores_hombres,
        (SELECT COUNT(*) FROM persona p '.$extra_sql.' AND (DATEDIFF(CAST(UTC_DATE() AS DATE), p.f_nac) / 365.25) < 18 AND p.afiliacion = "1" AND p.sexo = "F") AS menores_mujeres,
        (SELECT COUNT(*) FROM persona p '.$extra_sql.' AND (DATEDIFF(CAST(UTC_DATE() AS DATE), p.f_nac) / 365.25) > 18 AND (DATEDIFF(CAST(UTC_DATE() AS DATE), p.f_nac) / 365.25) < 25 AND p.afiliacion = "1" AND p.sexo = "M") AS 18a24_hombres,
        (SELECT COUNT(*) FROM persona p '.$extra_sql.' AND (DATEDIFF(CAST(UTC_DATE() AS DATE), p.f_nac) / 365.25) > 18 AND (DATEDIFF(CAST(UTC_DATE() AS DATE), p.f_nac) / 365.25) < 25 AND p.afiliacion = "1" AND p.sexo = "F") AS 18a24_mujeres,
        (SELECT COUNT(*) FROM persona p '.$extra_sql.' AND (DATEDIFF(CAST(UTC_DATE() AS DATE), p.f_nac) / 365.25) >= 25 AND (DATEDIFF(CAST(UTC_DATE() AS DATE), p.f_nac) / 365.25) < 35 AND p.afiliacion = "1" AND p.sexo = "M") AS 25a34_hombres,
        (SELECT COUNT(*) FROM persona p '.$extra_sql.' AND (DATEDIFF(CAST(UTC_DATE() AS DATE), p.f_nac) / 365.25) >= 25 AND (DATEDIFF(CAST(UTC_DATE() AS DATE), p.f_nac) / 365.25) < 35 AND p.afiliacion = "1" AND p.sexo = "F") AS 25a34_mujeres,
        (SELECT COUNT(*) FROM persona p '.$extra_sql.' AND (DATEDIFF(CAST(UTC_DATE() AS DATE), p.f_nac) / 365.25) >= 35 AND (DATEDIFF(CAST(UTC_DATE() AS DATE), p.f_nac) / 365.25) < 45 AND p.afiliacion = "1" AND p.sexo = "M") AS 35a44_hombres,
        (SELECT COUNT(*) FROM persona p '.$extra_sql.' AND (DATEDIFF(CAST(UTC_DATE() AS DATE), p.f_nac) / 365.25) >= 35 AND (DATEDIFF(CAST(UTC_DATE() AS DATE), p.f_nac) / 365.25) < 45 AND p.afiliacion = "1" AND p.sexo = "F") AS 35a44_mujeres,
        (SELECT COUNT(*) FROM persona p '.$extra_sql.' AND (DATEDIFF(CAST(UTC_DATE() AS DATE), p.f_nac) / 365.25) >= 45 AND (DATEDIFF(CAST(UTC_DATE() AS DATE), p.f_nac) / 365.25) < 55 AND p.afiliacion = "1" AND p.sexo = "M") AS 45a54_hombres,
        (SELECT COUNT(*) FROM persona p '.$extra_sql.' AND (DATEDIFF(CAST(UTC_DATE() AS DATE), p.f_nac) / 365.25) >= 45 AND (DATEDIFF(CAST(UTC_DATE() AS DATE), p.f_nac) / 365.25) < 55 AND p.afiliacion = "1" AND p.sexo = "F") AS 45a54_mujeres,
        (SELECT COUNT(*) FROM persona p '.$extra_sql.' AND (DATEDIFF(CAST(UTC_DATE() AS DATE), p.f_nac) / 365.25) >= 55 AND (DATEDIFF(CAST(UTC_DATE() AS DATE), p.f_nac) / 365.25) < 65 AND p.afiliacion = "1" AND p.sexo = "M") AS 55a64_hombres,
        (SELECT COUNT(*) FROM persona p '.$extra_sql.' AND (DATEDIFF(CAST(UTC_DATE() AS DATE), p.f_nac) / 365.25) >= 55 AND (DATEDIFF(CAST(UTC_DATE() AS DATE), p.f_nac) / 365.25) < 65 AND p.afiliacion = "1" AND p.sexo = "F") AS 55a64_mujeres,
        (SELECT COUNT(*) FROM persona p '.$extra_sql.' AND (DATEDIFF(CAST(UTC_DATE() AS DATE), p.f_nac) / 365.25) >= 65 AND p.afiliacion = "1" AND p.sexo = "M") AS 65mas_hombres,
        (SELECT COUNT(*) FROM persona p '.$extra_sql.' AND (DATEDIFF(CAST(UTC_DATE() AS DATE), p.f_nac) / 365.25) >= 65 AND p.afiliacion = "1" AND p.sexo = "F") AS 65mas_mujeres;
    ';
    
    $statement = $con->prepare($sql);
    $statement->execute();
    $afiliados_rango_edad_por_sexo = $statement->fetch(); 
    
    //array con datos de edades por sexo
    $rango_edades_hombres = [
        ['rango' => 'Menor de 18', 'cantidad' => $afiliados_rango_edad_por_sexo['menores_hombres']],
        ['rango' => 'De 18 a 24', 'cantidad' => $afiliados_rango_edad_por_sexo['18a24_hombres']],
        ['rango' => 'De 25 a 34', 'cantidad' => $afiliados_rango_edad_por_sexo['25a34_hombres']],
        ['rango' => 'De 34 a 44', 'cantidad' => $afiliados_rango_edad_por_sexo['35a44_hombres']],
        ['rango' => 'De 45 a 54', 'cantidad' => $afiliados_rango_edad_por_sexo['45a54_hombres']],
        ['rango' => 'De 55 a 64', 'cantidad' => $afiliados_rango_edad_por_sexo['55a64_hombres']],
        ['rango' => '65 o más', 'cantidad' => $afiliados_rango_edad_por_sexo['65mas_hombres']]
    ];
    
    $rango_edades_mujeres = [
        ['rango' => 'Menor de 18', 'cantidad' => $afiliados_rango_edad_por_sexo['menores_mujeres']],
        ['rango' => 'De 18 a 24', 'cantidad' => $afiliados_rango_edad_por_sexo['18a24_mujeres']],
        ['rango' => 'De 25 a 34', 'cantidad' => $afiliados_rango_edad_por_sexo['25a34_mujeres']],
        ['rango' => 'De 34 a 44', 'cantidad' => $afiliados_rango_edad_por_sexo['35a44_mujeres']],
        ['rango' => 'De 45 a 54', 'cantidad' => $afiliados_rango_edad_por_sexo['45a54_mujeres']],
        ['rango' => 'De 55 a 64', 'cantidad' => $afiliados_rango_edad_por_sexo['55a64_mujeres']],
        ['rango' => '65 o más', 'cantidad' => $afiliados_rango_edad_por_sexo['65mas_mujeres']]
    ];
    
    
    //SECCIONES CON MAYOR NUMERO DE HOMBRES Y MUJERES*************************************************************
    $sql = '
    SELECT
        (SELECT seccion FROM (SELECT COUNT(p.seccion) AS seccion_h, p.seccion FROM persona p '.$extra_sql.' AND p.afiliacion = "1" AND p.sexo = "M" GROUP BY p.seccion ORDER BY COUNT(p.seccion) DESC LIMIT 1) AS sh2) AS seccion_mas_hombres,
        (SELECT seccion_h FROM (SELECT COUNT(p.seccion) AS seccion_h, p.seccion FROM persona p '.$extra_sql.' AND p.afiliacion = "1" AND p.sexo = "M" GROUP BY p.seccion ORDER BY COUNT(p.seccion) DESC LIMIT 1) AS sh1) AS seccion_mas_hombres_cantidad,
        (SELECT seccion FROM (SELECT COUNT(p.seccion) AS seccion_m, p.seccion FROM persona p '.$extra_sql.' AND p.afiliacion = "1" AND p.sexo = "F" GROUP BY p.seccion ORDER BY COUNT(p.seccion) DESC LIMIT 1) AS sm2) AS seccion_mas_mujeres,
        (SELECT seccion_m FROM (SELECT COUNT(p.seccion) AS seccion_m, p.seccion FROM persona p '.$extra_sql.' AND p.afiliacion = "1" AND p.sexo = "F" GROUP BY p.seccion ORDER BY COUNT(p.seccion) DESC LIMIT 1) AS sm1) AS seccion_mas_mujeres_cantidad;
    ';
    
    $statement = $con->prepare($sql);
    $statement->execute();
    $seccion_mayor_afiliados_sexo = $statement->fetch();
    
    
    /* INFO PARA GRÁFICAS ***********************************************************************/
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
    
    //PERSONAS POR RANGO DE EDAD***************************************************************
    $sql = '
    SELECT
        (SELECT COUNT(*) FROM persona p '.$extra_sql.' AND (DATEDIFF(CAST(UTC_DATE() AS DATE), p.f_nac) / 365.25) < 18 AND p.afiliacion = "1") AS menores,
        (SELECT COUNT(*) FROM persona p '.$extra_sql.' AND (DATEDIFF(CAST(UTC_DATE() AS DATE), p.f_nac) / 365.25) > 18 AND (DATEDIFF(CAST(UTC_DATE() AS DATE), p.f_nac) / 365.25) < 25 AND p.afiliacion = "1") AS 18a24,
        (SELECT COUNT(*) FROM persona p '.$extra_sql.' AND (DATEDIFF(CAST(UTC_DATE() AS DATE), p.f_nac) / 365.25) >= 25 AND (DATEDIFF(CAST(UTC_DATE() AS DATE), p.f_nac) / 365.25) < 35 AND p.afiliacion = "1") AS 25a34,
        (SELECT COUNT(*) FROM persona p '.$extra_sql.' AND (DATEDIFF(CAST(UTC_DATE() AS DATE), p.f_nac) / 365.25) >= 35 AND (DATEDIFF(CAST(UTC_DATE() AS DATE), p.f_nac) / 365.25) < 45 AND p.afiliacion = "1") AS 35a44,
        (SELECT COUNT(*) FROM persona p '.$extra_sql.' AND (DATEDIFF(CAST(UTC_DATE() AS DATE), p.f_nac) / 365.25) >= 45 AND (DATEDIFF(CAST(UTC_DATE() AS DATE), p.f_nac) / 365.25) < 55 AND p.afiliacion = "1") AS 45a54,
        (SELECT COUNT(*) FROM persona p '.$extra_sql.' AND (DATEDIFF(CAST(UTC_DATE() AS DATE), p.f_nac) / 365.25) >= 55 AND (DATEDIFF(CAST(UTC_DATE() AS DATE), p.f_nac) / 365.25) < 65 AND p.afiliacion = "1") AS 55a64,
        (SELECT COUNT(*) FROM persona p '.$extra_sql.' AND (DATEDIFF(CAST(UTC_DATE() AS DATE), p.f_nac) / 365.25) >= 65 AND p.afiliacion = "1") AS 65mas;
    ';
    $statement = $con->prepare($sql);
    $statement->execute(); 
    $afiliados_rango_edad = $statement->fetch(); 
    
    //array con datos de edades
    $total_edades = [
        ['rango' => 'Menor de 18', 'cantidad' => $afiliados_rango_edad['menores']],
        ['rango' => 'De 18 a 24', 'cantidad' => $afiliados_rango_edad['18a24']],
        ['rango' => 'De 25 a 34', 'cantidad' => $afiliados_rango_edad['25a34']],
        ['rango' => 'De 34 a 44', 'cantidad' => $afiliados_rango_edad['35a44']],
        ['rango' => 'De 45 a 54', 'cantidad' => $afiliados_rango_edad['45a54']],
        ['rango' => 'De 55 a 64', 'cantidad' => $afiliados_rango_edad['55a64']],
        ['rango' => '65 o más', 'cantidad' => $afiliados_rango_edad['65mas']]
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
    <title>Sonreímos Juntos</title>
    

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
    <link rel="stylesheet" type="text/css" href="css/index_temp.css">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Titillium+Web:wght@200;300;400;600;700;900&display=swap" rel="stylesheet">
    
    <script src="https://kit.fontawesome.com/9c52d851d9.js" crossorigin="anonymous"></script>
    
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.3/jquery.min.js"></script>
    
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
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
          <div class="titulo">
              <p>Hola <?php echo $usr_nombre;?></p>
              <!--span>Faltan <?php echo $diferencia_dias;?> días para seguir sonriendo</span-->
          </div>
          <div class="fecha"><i class="fa-solid fa-caret-right"></i><p><?php echo $hoy_es;?></p></div>
           
           <div class="info_combos">
               <div class="control">
                    
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

                    <!--div class="form__div ">
                        <div class="combo combo-afiliacion">
                            <select class="form__input filter_municipio filter" id="municipio" name="municipio">
                                <option value="0">- Municipio -</option>
                                <?php //foreach($municipios as $municipio):?>
                                <option value="<?php //echo $municipio['id_municipio']; ?>"><?php //echo $municipio['municipio'] ?></option>   
                                <?php //endforeach;?>
                            </select>
                            <label class="form__label label__seccion" for="municipio">Municipio</label>
                        </div>
                    </div-->
                    
                    <div class="form__div">
                        <div class="combo combo-demarc">
                            <select class="form__input demarc filter" id="demarc" name="demarc">
                                <option value="0">- Demarcación -</option>
                            </select>
                            <label class="form__label label__seccion sexo_afiliacion" for="demarc">Demarcación</label>

                        </div>
                    </div>

                   <div class="form__div">
                        <div class="combo combo-seccion">
                            <select class="form__input seccion filter" id="zona" name="zona">
                                <option value="0">- Zona -</option>
                            </select>
                            <label class="form__label label__seccion sexo_afiliacion" for="seccion">Zona</label>

                        </div>
                    </div>
                   
                    <div class="form__div">
                        <div class="combo combo-seccion">
                            <select class="form__input seccion filter" id="seccion_filter" name="seccion">
                                <option value="0">- Sección -</option>
                            </select>
                            <label class="form__label label__seccion sexo_afiliacion" for="seccion">Sección</label>

                        </div>
                    </div>
                

                    <div class="form__div ">
                        <div class="combo combo-promotor">
                            <select class="form__input filter_colonia filter" id="colonia" name="colonia">
                                <option value="0">- Colonia -</option>
                                <?php foreach($colonias as $colonia):?>
                                <option value="<?php echo $colonia['id_cp_colonia']; ?>"><?php echo $colonia['colonia'] ?></option>   
                                <?php endforeach;?>
                            </select>
                            <label class="form__label label__seccion label__seccion" for="">Colonia</label>
                        </div>
                    </div>

                    <div id="filter__reload" class="form__button">
                        <i class="fa-solid fa-rotate-left"></i>
                    </div>
                </div>
                
            </div>
                
           
           
           <div class="wrapper">
              
              <div class="personas__registradas wrapper__box">
                 <div class="wrapper__box__content">
                      <div class="icono__default icono"><i class="fa-solid fa-users"></i></div>
                      <div class="info">
                          <p id="data__total" class="dato__small"><?php echo $personas_total['cantidad']; ?> personas registradas</p>
                          <p id="data__total__afiliados" class="dato__relevante"><?php echo $afiliados['total']; ?> seguirán sonriendo</p>
                          <p id="data__total__afiliados__prc" class="dato__numero">Representan el <?php echo number_format(($afiliados['total'] * 100) / $personas_total['cantidad'], 1, '.',''); ?>% de los registros</p>
                      </div>
                  </div>
              </div>
              
              <div class="afiliados__sexo wrapper__box">
                 <div class="wrapper__box__title">
                     <p class="box__title">Personas afiliadas</p>
                 </div>
                 <div class="wrapper__box__content">
                      <div class="afiliados__mujeres inside__info inside__first">
                          <div class="icono__small icono"><i class="fa-solid fa-person-dress"></i></div>
                          <div class="afiliados__mujeres__datos">
                              <p id="data__mujeres" class="dato__relevante"><?php echo $afiliados['mujeres']; ?> Mujeres</p>
                              <p id="data__mujeres__prc" class="dato__numero"><?php echo number_format(($afiliados['mujeres'] * 100) / $afiliados['total'], 1, '.', ''); ?>%</p>
                          </div>
                      </div>
                      <div class="afiliados__hombres inside__info">
                          <div class="icono__small icono"><i class="fa-solid fa-person"></i></div>
                          <div class="afiliados__hombres__datos">
                              <p id="data__hombres" class="dato__relevante"><?php echo $afiliados['hombres']; ?> Hombres</p>
                              <p id="data__hombres__prc" class="dato__numero"><?php echo number_format(($afiliados['hombres'] * 100) / $afiliados['total'], 1, '.', ''); ?>%</p>
                          </div>
                      </div> 
                  </div> 
              </div>
              
              <div class="afiliados__edad wrapper__box">
                 <div class="wrapper__box__title">
                     <p class="box__title">Edad promedio</p>
                 </div>
                  <div class="wrapper__box__content">
                      <div class="afiliados__edad__mujeres inside__info inside__first">
                          <div class="icono__small icono"><i class="fa-solid fa-person-dress"></i></div>
                          <div class="afiliados__hombres__datos">
                              <p id="data__edadprom__mujeres" class="dato__relevante"><?php $afiliados['edad_promedio_mujeres'] = $afiliados['edad_promedio_mujeres'] == '' ? 'N/A' : $afiliados['edad_promedio_mujeres']; echo $afiliados['edad_promedio_mujeres']; ?> años</p>
                          </div>
                      </div>
                      <div class="afiliados__edad__hombres inside__info">
                          <div class="icono__small icono"><i class="fa-solid fa-person"></i></div>
                          <div class="afiliados__hombres__datos">
                              <p id="data__edadprom__hombres" class="dato__relevante"><?php $afiliados['edad_promedio_hombres'] = $afiliados['edad_promedio_hombres'] == '' ? 'N/A' : $afiliados['edad_promedio_hombres']; echo $afiliados['edad_promedio_hombres']; ?> años</p>
                          </div>
                      </div>
                  </div>
              </div>
              
              <div class="afiliados__edad__rango__sexo wrapper__box">
                 <div class="wrapper__box__title">
                     <p class="box__title">Rango de edad con más afiliadas/os</p>
                 </div>
                  <div class="wrapper__box__content">
                      <div class="afiliados__edad__mujeres inside__info inside__first">
                          <div class="icono__small icono"><i class="fa-solid fa-person-dress"></i></div>
                          <div class="afiliados__mujeres__datos">
                              <?php
                              $indice_max = array_search(max(array_column($rango_edades_mujeres, 'cantidad')), array_column($rango_edades_mujeres, 'cantidad'));
                              $array_max = $rango_edades_mujeres[$indice_max]; 
                              ?>

                              <p id="data__maxedad__mujeres" class="dato__relevante"><?php echo ($array_max['rango']); ?> años</p>
                              <p id="data__maxedad__mujeres__cantidad" class="dato__numero"><?php echo $array_max['cantidad']; ?> mujeres</p>
                          </div>
                      </div> 
                     <div class="afiliados__edad__hombres inside__info">
                          <div class="icono__small icono"><i class="fa-solid fa-person"></i></div>
                          <div class="afiliados__hombres__datos">
                              <?php
                              $indice_max = array_search(max(array_column($rango_edades_hombres, 'cantidad')), array_column($rango_edades_hombres, 'cantidad'));
                              $array_max = $rango_edades_hombres[$indice_max]; 
                              ?>

                              <p id="data__maxedad__hombres" class="dato__relevante"><?php echo ($array_max['rango']); ?> años</p>
                              <p id="data__maxedad__hombres__cantidad" class="dato__numero"><?php echo $array_max['cantidad']; ?> hombres</p>
                          </div>
                      </div>
                  </div>
              </div>
              
              <div class="afiliados__seccion__sexo wrapper__box">
                 <div class="wrapper__box__title">
                     <p class="box__title">Sección con más afiliadas/os</p>
                 </div>
                  <div class="wrapper__box__content">
                      <div class="afiliados__seccion__mujeres inside__info inside__first">
                          <div class="icono__small icono"><i class="fa-solid fa-person-dress"></i></div>
                          <div class="afiliados__hombres__datos">
                              
                              <p id="data__maxseccion__mujeres" class="dato__relevante"><?php $seccion_mayor_afiliados_sexo['seccion_mas_mujeres'] = $seccion_mayor_afiliados_sexo['seccion_mas_mujeres'] == '' ? 'N/A' : $seccion_mayor_afiliados_sexo['seccion_mas_mujeres']; echo $seccion_mayor_afiliados_sexo['seccion_mas_mujeres']; ?></p>
                              
                              <p id="data__maxseccion__mujeres__cantidad" class="dato__numero"><?php $seccion_mayor_afiliados_sexo['seccion_mas_mujeres_cantidad'] = $seccion_mayor_afiliados_sexo['seccion_mas_mujeres_cantidad'] == '' ? '0' : $seccion_mayor_afiliados_sexo['seccion_mas_mujeres_cantidad']; echo $seccion_mayor_afiliados_sexo['seccion_mas_mujeres_cantidad']; ?> mujeres</p>
                          </div>
                      </div>
                      <div class="afiliados__seccion__hombres inside__info">
                          <div class="icono__small icono"><i class="fa-solid fa-person"></i></div>
                          <div class="afiliados__hombres__datos">
                             
                              <p id="data__maxseccion__hombres" class="dato__relevante"><?php $seccion_mayor_afiliados_sexo['seccion_mas_hombres'] = $seccion_mayor_afiliados_sexo['seccion_mas_hombres'] == '' ? 'N/A' : $seccion_mayor_afiliados_sexo['seccion_mas_hombres']; echo $seccion_mayor_afiliados_sexo['seccion_mas_hombres']; ?></p>
                              
                              <p id="data__maxseccion__hombres__cantidad" class="dato__numero"><?php $seccion_mayor_afiliados_sexo['seccion_mas_hombres_cantidad'] = $seccion_mayor_afiliados_sexo['seccion_mas_hombres_cantidad'] == '' ? '0' : $seccion_mayor_afiliados_sexo['seccion_mas_hombres_cantidad']; echo $seccion_mayor_afiliados_sexo['seccion_mas_hombres_cantidad']; ?> hombres</p>
                          </div>
                      </div>
                  </div>
              </div>
              
              
              
              
              
              <div class="graph__rango__edad graph__">
                 <div id="graph__rango__edad__cont" class="chart__" style="height: 400px;">
                    <canvas id="graph__rango__edad" style="height: 100%; width:100%"></canvas>
                 </div>
                
              </div>
              
               <div class="graph__colonias__mas__afiliacion graph__">
                   <div id="graph__colonias__mas__afiliacion__cont" class="chart__" style="height: 400px;">
                       <canvas id="graph__colonias__mas__afiliacion" style="height: 100%; width:100%"></canvas>
                   </div>
               </div>
               
               
               <div class="graph__colonias__menor__afiliacion graph__">
                   <div id="graph__colonias__menor__afiliacion__cont" class="chart__" style="height: 400px;">
                      <canvas id="graph__colonias__menor__afiliacion" style="height: 100%; width:100%"></canvas>
                   </div>
               </div>
               
               
               <div class="graph__secciones__mas__afiliacion graph__">
                   <div id="graph__secciones__mas__afiliacion__cont" class="chart__" style="height: 400px;">
                        <canvas id="graph__secciones__mas__afiliacion" style="height: 100%; width:100%"></canvas>
                   </div>
               </div>
               
               
               <div class="graph__secciones__menor__afiliacion graph__">
                   <div id="graph__secciones__menor__afiliacion__cont" class="chart__" style="height: 400px;">
                        <canvas id="graph__secciones__menor__afiliacion" style="height: 100%; width:100%"></canvas>
                   </div>
               </div>          
               
               
           </div>
           
           <div class="contenedor_buscador">
               <div class="buscador_seccion">
                   <h2>buscador de colonias registradas por sección</h2>
                    <div class="form__div div__seccion seccion_buscador">

                        <input maxlength="3" type="number" placeholder=" " id="seccion" name="seccion" class="form__input filter"/>
                        <label class="full-field form__label label__seccions">Sección</label>

                        <div class="colonias_seccion">
                            <p id="colonia_title"></p>
                            <div id="colonias"></div>
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
    
    <!-- CARGA DE GRÁFICAS -->
     <script type="text/javascript">
         var ctx__rango__edad = document.getElementById('graph__rango__edad').getContext('2d');
         var ctx__colonias__mas__afiliacion = document.getElementById('graph__colonias__mas__afiliacion').getContext('2d');
         var ctx__colonias__menor__afiliacion = document.getElementById('graph__colonias__menor__afiliacion').getContext('2d');
         var ctx__secciones__mas__afiliacion = document.getElementById('graph__secciones__mas__afiliacion').getContext('2d');
         var ctx__secciones__menor__afiliacion = document.getElementById('graph__secciones__menor__afiliacion').getContext('2d');

        let labels__rango__edad = [];
        let values__rango__edad = [];
        <?php foreach($total_edades as $total_edad):?>
            labels__rango__edad.push('<?php echo $total_edad['rango'] ?>');
            values__rango__edad.push(<?php echo $total_edad['cantidad'] ?>);
        <?php endforeach;?>
         

        let labels__colonias__mas__afiliacion = [];
        let values__colonias__mas__afiliacion = [];
        <?php foreach($total_cols_top as $colonia_top):?>
            labels__colonias__mas__afiliacion.push('<?php echo $colonia_top['colonia'] ?>');
            values__colonias__mas__afiliacion.push('<?php echo $colonia_top['cantidad'] ?>');
        <?php endforeach;?>
         
         
        let labels__colonias__menor__afiliacion = [];
        let values__colonias__menor__afiliacion = [];
        <?php foreach($total_cols_lower as $colonia_lower):?>
            labels__colonias__menor__afiliacion.push('<?php echo $colonia_lower['colonia'] ?>');
            values__colonias__menor__afiliacion.push('<?php echo $colonia_lower['cantidad'] ?>');
        <?php endforeach;?>
        
         
        let labels__secciones__mas__afiliacion = [];
        let values__secciones__mas__afiliacion = [];
        <?php foreach($total_seccs_top as $seccion_top):?>
            labels__secciones__mas__afiliacion.push('<?php echo $seccion_top['seccion'] ?>');
            values__secciones__mas__afiliacion.push('<?php echo $seccion_top['cantidad'] ?>');
        <?php endforeach;?>
         
         
        let labels__secciones__menor__afiliacion = [];
        let values__secciones__menor__afiliacion = [];
        <?php foreach($total_seccs_lower as $seccion_lower):?>
            labels__secciones__menor__afiliacion.push('<?php echo $seccion_lower['seccion'] ?>');
            values__secciones__menor__afiliacion.push('<?php echo $seccion_lower['cantidad'] ?>');
        <?php endforeach;?>
         
        let data__rango__edad;
        let data__colonias__mas__afiliacion;
        let data__colonias__menor__afiliacion;
        let data__secciones__mas__afiliacion;
        let data__secciones__menor__afiliacion;
         function cargaDataGraficas(
                labels__rango__edad, values__rango__edad,
                labels__colonias__mas__afiliacion, values__colonias__mas__afiliacion,
                labels__colonias__menor__afiliacion, values__colonias__menor__afiliacion,
                labels__secciones__mas__afiliacion, values__secciones__mas__afiliacion,
                labels__secciones__menor__afiliacion, values__secciones__menor__afiliacion
                ){
              data__rango__edad = {
                labels: labels__rango__edad,
                datasets: [{
                    label: '',
                    data: values__rango__edad,
                    fill: false,
                    backgroundColor: [
                        '#F4354533',
                        '#FA890133',
                        '#FAD71733',
                        '#00BA7133',
                        '#00C2DE33',
                        '#00418D33',
                        '#5F287933'
                    ],
                    borderColor: [
                        '#F4354580',
                        '#FA890180',
                        '#FAD717F0',
                        '#00BA71D0',
                        '#00C2DED0',
                        '#00418D80',
                        '#5F287980'
                    ],
                    borderWidth: 1,
                }]
            };   
                    
             data__colonias__mas__afiliacion = {
            labels: labels__colonias__mas__afiliacion,
            datasets: [{
                label: '',
                data: values__colonias__mas__afiliacion,
                fill: false,
                backgroundColor: [
                    '#0D755C63',
                    '#05815F63',
                    '#54A05B83',
                    '#89BE3A63',
                    '#A2AB2D63',
                    '#C8B60263',
                    '#DDC50263',
                    '#EDE00073',
                    '#FFDA0263',
                    '#FFD935A3'
                ],
                borderColor: [
                    '#0D755C80',
                    '#05815F80',
                    '#54A05BF0',
                    '#89BE3AD0',
                    '#A2AB2DD0',
                    '#C8B60280',
                    '#DDC50280',
                    '#EDE000D0',
                    '#FFDA0280',
                    '#FFD93580'
                ],
                borderWidth: 1,
            }]
        };
          
        data__colonias__menor__afiliacion = {
            labels: labels__colonias__menor__afiliacion,
            datasets: [{
                label: '',
                data: values__colonias__menor__afiliacion,
                fill: false,
                backgroundColor: [
                    '#FF000053',
                    '#FF1A0043',
                    '#FF330043',
                    '#FF4C0043',
                    '#FF650043',
                    '#FF7D0043',
                    '#FF950063',
                    '#FFAD0073',
                    '#FFC30073',
                    '#FFEE0073'
                ],
                borderColor: [
                    '#FF000080',
                    '#FF1A0080',
                    '#FF330070',
                    '#FF4C0070',
                    '#FF650070',
                    '#FF7D0080',
                    '#FF950080',
                    '#FFAD00D0',
                    '#FFC30080',
                    '#FFEE0080'
                ],
                borderWidth: 1,
            }]
        };  
         
        data__secciones__mas__afiliacion = {
            labels: labels__secciones__mas__afiliacion,
            datasets: [{
                label: '',
                data: values__secciones__mas__afiliacion,
                fill: false,
                backgroundColor: [
                    '#0D755C63',
                    '#05815F63',
                    '#54A05B83',
                    '#89BE3A63',
                    '#A2AB2D63',
                    '#C8B60263',
                    '#DDC50263',
                    '#EDE00073',
                    '#FFDA0263',
                    '#FFD935A3'
                ],
                borderColor: [
                    '#0D755C80',
                    '#05815F80',
                    '#54A05BF0',
                    '#89BE3AD0',
                    '#A2AB2DD0',
                    '#C8B60280',
                    '#DDC50280',
                    '#EDE000D0',
                    '#FFDA0280',
                    '#FFD93580'
                ],
                borderWidth: 1,
            }]
        };
         
        data__secciones__menor__afiliacion = {
            labels: labels__secciones__menor__afiliacion,
            datasets: [{
                label: '',
                data: values__secciones__menor__afiliacion,
                fill: false,
                backgroundColor: [
                    '#FF000053',
                    '#FF1A0043',
                    '#FF330043',
                    '#FF4C0043',
                    '#FF650043',
                    '#FF7D0043',
                    '#FF950063',
                    '#FFAD0073',
                    '#FFC30073',
                    '#FFEE0073'
                ],
                borderColor: [
                    '#FF000080',
                    '#FF1A0080',
                    '#FF330070',
                    '#FF4C0070',
                    '#FF650070',
                    '#FF7D0080',
                    '#FF950080',
                    '#FFAD00D0',
                    '#FFC30080',
                    '#FFEE0080'
                ],
                borderWidth: 1,
            }]
        };
         }
         
         cargaDataGraficas( labels__rango__edad, values__rango__edad,
                labels__colonias__mas__afiliacion, values__colonias__mas__afiliacion,
                labels__colonias__menor__afiliacion, values__colonias__menor__afiliacion,
                labels__secciones__mas__afiliacion, values__secciones__mas__afiliacion,
                labels__secciones__menor__afiliacion, values__secciones__menor__afiliacion);
        
         
       
         
         let color__rango__edad = [
            '#C41C30',
            '#CC6612',
            '#CC6612',
            '#098952',
            '#00418D',
            '#00418D',
            '#50206D'
        ] 
         
         let color__colonias__creciente = [
            '#0A5944',
            '#0A5944',
            '#0A5944',
            '#096633',
            '#0D841B',
            '#55770A',
            '#55770A',
            '#916B0D',
            '#916B0D',
            '#916B0D'
        ]
         
         let color__colonias__decreciente = [
            '#A80C0C',
            '#A80C0C',
            '#A80C0C',
            '#A80C0C',
            '#A8260C',
            '#A8260C',
            '#A84B0C',
            '#A84B0C',
            '#A84B0C',
            '#A84B0C'
        ]
         
        var myChart;

        function createChart(ctx, data, labels, color, title) {
            myChart = new Chart(ctx, {
                type: 'bar',
                data: data,
                options: {
                    indexAxis: 'y',
                    scales: {
                        x: {},
                        y: {
                            beginAtZero: true,
                            grid: {
                                drawOnChartArea: false
                            },
                            ticks: {
                                mirror: true,
                                color: 'black',
                                z: '9999',
                                callback: function(value, index, values) {
                                    let p = '';
                                    if(data.datasets[0].data[index] == 1){ p = ' persona';} else { p = ' personas';}
                                    return labels[index] + ' | ' + data.datasets[0].data[index] + p;
                                },
                                font:{
                                    family: 'Titillium Web',
                                    size: 14,
                                    weight: 'bold'
                                },
                                color: color
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        title: {
                            display: true,
                            text: title,
                            font:{
                                family: 'Titillium Web',
                                size: 14,
                                weight: 'bold',
                                lineHeight: 1.5
                            },
                            color: '#8B1232'
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    var dataset = context.dataset.data;
                                    var total = dataset.reduce((acc, val) => acc + val);
                                    var currentValue = dataset[context.dataIndex];
                                    var percentage = Math.floor(((currentValue / total) * 100) + 0.5);
                                    return currentValue + ' personas (' + percentage + '%)';
                                }
                            }
                        }
                    },//fin plugins
                }
            });
            
        }

        createChart(ctx__rango__edad, data__rango__edad, labels__rango__edad, color__rango__edad, 'Rango de edad');
         
        createChart(ctx__colonias__mas__afiliacion, data__colonias__mas__afiliacion, labels__colonias__mas__afiliacion, color__colonias__creciente, 'Colonias con mayor afiliación');
        createChart(ctx__colonias__menor__afiliacion, data__colonias__menor__afiliacion, labels__colonias__menor__afiliacion, color__colonias__decreciente, 'Colonias con menor afiliación');
         
        createChart(ctx__secciones__mas__afiliacion, data__secciones__mas__afiliacion, labels__secciones__mas__afiliacion, color__colonias__creciente, 'Secciones con mayor afiliación');
        createChart(ctx__secciones__menor__afiliacion, data__secciones__menor__afiliacion, labels__secciones__menor__afiliacion, color__colonias__decreciente, 'Secciones con menor afiliación');
    </script>
    
    <script type="text/javascript">
    $(document).ready(function() {
        bandera_mun = 0;
        

        $('.bar span').hide();

        $(window).resize(function() {
          // This will execute whenever the window is resized
          wheight = $(window).height(); // New height
          wwidth = $(window).width(); // New width
            
            if(wwidth < 900){
               
            }
        });
        
        /* CARGA COMBOS DE FILTROS */
        $('#edad').on('change', function(){
            cargaFiltros();
        });
        /* Carga demarcaciones 1ra vez */
        function cargaDemarcaciones(){
            $.post('ajax/demarcaciones.php', {mun: 'Tepic'}, function(data){
                
                json = jQuery.parseJSON(data);
                for(i = 0; i< json.length; i++){
                    $('#demarc').append($('<option>', {
                        value: json[i]['dem'],
                        text: json[i]['dem']
                    }));
                    
                }
                
            });
        }
        cargaDemarcaciones();
        
        // Carga zonas cuando cambia la demarcacion
        $('#demarc').on('change', function(){
            $('#zona').html('<option value="0">- Zona -</option>');
            $('#seccion_filter').html('<option value="0">- Sección -</option>');
            $.post("ajax/zonas.php", {mun: '18', dem: $('#demarc option:selected').val() }, function(data){
                if(data == "x" || data == "0"){
                    if($("#demarc option:selected").val() == '0'){
                        $("#zona").html('<option value="0">- Zona -</option>');   
                    } else{
                        $("#zona").html('<option value="0">Sin zonas asignadas</option>');
                    }

                } else {
                    zonas = parseInt(data);
                    for(i = 1; i <= zonas; i++){
                        $("#zona").append('<option value="'+i+'">'+i+'</option>');

                    }
                }
                   
            });
            cargaFiltros();
        });
        
        // Carga Secciones cuando cambia la zona
        $('#zona').on('change', function(){
            $('#seccion_filter').html('<option value="0">- Sección -</option>');
            d = $('#demarc option:selected').val();
            z = $('#zona option:selected').val();
            $.post("ajax/secciones.php", {mun: 'Tepic', dem: d, zona: z }, function(data){
                json = jQuery.parseJSON(data);
                for(i = 0; i< json.length; i++){
                    $("#seccion_filter").append('<option value="'+json[i]['secc']+'">'+json[i]['secc']+'</option>');
                }
            });
            cargaFiltros();
        });
        
        $('#seccion_filter').on('change', function(){
            cargaFiltros();
        });
        
        $('#colonia').on('change', function(){
            cargaFiltros();
        });
        
        
        /* FILTROS DE INFROMACIÓN */
        function cargaFiltros(){
            edad = $('#edad').val();
            demarc = $('#demarc').val();
            zona = $('#zona').val();
            seccion = $('#seccion_filter').val();
            colonia = $('#colonia option:selected').text();
            
            //Datos sencillos
            $.post('ajax/datos_index_filter.php', {edad: edad, demarc: demarc, zona: zona, seccion: seccion, colonia: colonia}, function(data){
                
                json = jQuery.parseJSON(data);
                
                var rango_mujeres = [
                    { indice: "Menor de edad", valor: json['menores_mujeres'] },
                    { indice: "18 a 24 años", valor: json['18a24_mujeres'] },
                    { indice: "25 a 34 años", valor: json['25a34_mujeres'] },
                    { indice: "35 a 44 años", valor: json['35a44_mujeres'] },
                    { indice: "45 a 54 años", valor: json['45a54_mujeres'] },
                    { indice: "55 a 64 años", valor: json['55a64_mujeres'] },
                    { indice: "65 o más años", valor: json['65mas_mujeres'] }
                ];

                var resultado_rangos_mujeres = {};
                rango_mujeres.forEach(function(item) {
                    var nuevoIndice = item['indice'];
                    resultado_rangos_mujeres[nuevoIndice] = item['valor'];
                });
                var rango_hombres = [
                    { indice: "Menor de edad", valor: json['menores_hombres'] },
                    { indice: "18 a 24 años", valor: json['18a24_hombres'] },
                    { indice: "25 a 34 años", valor: json['25a34_hombres'] },
                    { indice: "35 a 44 años", valor: json['35a44_hombres'] },
                    { indice: "45 a 54 años", valor: json['45a54_hombres'] },
                    { indice: "55 a 64 años", valor: json['55a64_hombres'] },
                    { indice: "65 o más años", valor: json['65mas_hombres'] }
                ];

                var resultado_rangos_hombres = {};
                rango_hombres.forEach(function(item) {
                    var nuevoIndice = item['indice'];
                    resultado_rangos_hombres[nuevoIndice] = item['valor'];
                });
                
                var maxedad_mujeres;
                var valorMayor_rangoMujeres = -Infinity;
                var mayor_edadm_zero = 0;
                
                for (var indice in resultado_rangos_mujeres) {
                    if (resultado_rangos_mujeres[indice] > valorMayor_rangoMujeres) {
                        valorMayor_rangoMujeres = resultado_rangos_mujeres[indice];
                        maxedad_mujeres = indice;
                    }
                    mayor_edadm_zero += resultado_rangos_mujeres[indice];
                }

                
                var maxedad_hombres;
                var valorMayor_rangoHombres = -Infinity;
                var mayor_edadh_zero = 0;

                for (var indice in resultado_rangos_hombres) {
                    if (resultado_rangos_hombres[indice] > valorMayor_rangoHombres) {
                        valorMayor_rangoHombres = resultado_rangos_hombres[indice];
                        maxedad_hombres = indice;
                    }
                    mayor_edadh_zero += resultado_rangos_hombres[indice];
                }

                function nullToZero(dato){
                    if(dato == null) dato = 0;
                    return dato;
                }
                
                $('#data__total').html(json['total'] + ' personas registradas');
                $('#data__total__afiliados').html(json['total_afiliados'] + ' seguirán sonriendo');
                prc_afiliados = (json['total_afiliados'] * 100) / json['total'];
                $('#data__total__afiliados__prc').html('Representan el ' + parseFloat(prc_afiliados.toFixed(1)) + '% de los registros');
                
                $('#data__mujeres').html(nullToZero(json['mujeres']) + ' Mujeres');
                prc_mujeres = (json['mujeres'] * 100) / json['total_afiliados'];
                $('#data__mujeres__prc').html(parseFloat(prc_mujeres.toFixed(1)) + '%');
                $('#data__hombres').html(nullToZero(json['hombres']) + ' Hombres');
                prc_hombres = (json['hombres'] * 100) / json['total_afiliados'];
                $('#data__hombres__prc').html(parseFloat(prc_hombres.toFixed(1)) + '%');
                
                
                $('#data__edadprom__mujeres').html(nullToZero(json['edad_promedio_mujeres']) + ' años');
                $('#data__edadprom__hombres').html(nullToZero(json['edad_promedio_hombres']) + ' años');
                
                if(mayor_edadm_zero == 0) maxedad_mujeres = 'N/A';
                $('#data__maxedad__mujeres').html(maxedad_mujeres);
                $('#data__maxedad__mujeres__cantidad').html(valorMayor_rangoMujeres + ' mujeres');
                if(mayor_edadh_zero == 0) maxedad_hombres = 'N/A';
                $('#data__maxedad__hombres').html(maxedad_hombres);
                $('#data__maxedad__hombres__cantidad').html(valorMayor_rangoHombres + ' hombres');
                
                $('#data__maxseccion__mujeres').html(json['seccion_mas_mujeres']);
                $('#data__maxseccion__mujeres__cantidad').html(nullToZero(json['seccion_mas_mujeres_cantidad']) + ' mujeres');                
                $('#data__maxseccion__hombres').html(json['seccion_mas_hombres']);
                $('#data__maxseccion__hombres__cantidad').html(nullToZero(json['seccion_mas_hombres_cantidad']) + ' hombres');
                
                
            });
            
            
            //Datos graficas
            $.post('ajax/datos_index_filter_graficas.php', {edad: edad, demarc: demarc, zona: zona, seccion: seccion, colonia: colonia}, function(data){
                json = jQuery.parseJSON(data);
                
                total_colonias_lower = json['total colonias lower'];
                total_colonias_top = json['total colonias top'];
                total_secciones_lower = json['total secciones lower'];
                total_secciones_top = json['total secciones top'];
                total_edades = json['total edades'];
                
                function filtraLabelsValues(labels, values, array, array_label, array_value){
                    for(i = 0; i< array.length; i++){
                        labels.push(array[i][array_label]);
                        values.push(array[i][array_value]);
                    }    
                }
                
                function filtraGraph(canvas, div, canvas_id, data, labels, color, titulo){
                    canvas.remove();
                    div.html('<canvas id="'+canvas_id+'" style="height: 100%; width:100%"></canvas>');
                    ctx = document.getElementById(canvas_id).getContext('2d');
                    createChart(ctx, data, labels, color, titulo);
                }
                
                
                //reiniciar labels y values
                labels__rango__edad = [];
                values__rango__edad = [];
                
                labels__colonias__mas__afiliacion = [];
                values__colonias__mas__afiliacion = [];
                
                labels__colonias__menor__afiliacion = [];
                values__colonias__menor__afiliacion = [];
                
                labels__secciones__mas__afiliacion = [];
                values__secciones__mas__afiliacion = [];
                
                labels__secciones__menor__afiliacion = [];
                values__secciones__menor__afiliacion = [];
                
                
                
                filtraLabelsValues(labels__rango__edad, values__rango__edad, total_edades, 'rango', 'cantidad');
                
                filtraLabelsValues(labels__colonias__mas__afiliacion, values__colonias__mas__afiliacion, total_colonias_top, 'colonia', 'cantidad');
                
                filtraLabelsValues(labels__colonias__menor__afiliacion, values__colonias__menor__afiliacion, total_colonias_lower, 'colonia', 'cantidad');
                
                filtraLabelsValues(labels__secciones__mas__afiliacion, values__secciones__mas__afiliacion, total_secciones_top, 'seccion', 'cantidad');
                
                filtraLabelsValues(labels__secciones__menor__afiliacion, values__secciones__menor__afiliacion, total_secciones_lower, 'seccion', 'cantidad');
                
                
                
                cargaDataGraficas ( labels__rango__edad, values__rango__edad,
                labels__colonias__mas__afiliacion, values__colonias__mas__afiliacion,
                labels__colonias__menor__afiliacion, values__colonias__menor__afiliacion,
                labels__secciones__mas__afiliacion, values__secciones__mas__afiliacion,
                labels__secciones__menor__afiliacion, values__secciones__menor__afiliacion);
                
                
                
                
                
                filtraGraph(
                    $('#graph__rango__edad'),
                    $('#graph__rango__edad__cont'),
                    'graph__rango__edad',
                    data__rango__edad,
                    labels__rango__edad,
                    color__rango__edad,
                    'Rango de edad');
                //filtro colonias top
                filtraGraph(
                    $('#graph__colonias__mas__afiliacion'),
                    $('#graph__colonias__mas__afiliacion__cont'),
                    'graph__colonias__mas__afiliacion',
                    data__colonias__mas__afiliacion,
                    labels__colonias__mas__afiliacion,
                    color__colonias__creciente,
                    'Colonias con mayor afiliación');
                
                
                //filtro colonias lower
                filtraGraph(
                    $('#graph__colonias__menor__afiliacion'),
                    $('#graph__colonias__menor__afiliacion__cont'),
                    'graph__colonias__menor__afiliacion',
                    data__colonias__menor__afiliacion,
                    labels__colonias__menor__afiliacion,
                    color__colonias__decreciente,
                    'Colonias con menor afiliación');
                
                //filtro secciones top
                filtraGraph(
                    $('#graph__secciones__mas__afiliacion'),
                    $('#graph__secciones__mas__afiliacion__cont'),
                    'graph__secciones__mas__afiliacion',
                    data__secciones__mas__afiliacion,
                    labels__secciones__mas__afiliacion,
                    color__colonias__creciente,
                    'Secciones con mayor afiliación');
                
                
                //filtro secciones lower
                filtraGraph(
                    $('#graph__secciones__menor__afiliacion'),
                    $('#graph__secciones__menor__afiliacion__cont'),
                    'graph__secciones__menor__afiliacion',
                    data__secciones__menor__afiliacion,
                    labels__secciones__menor__afiliacion,
                    color__colonias__decreciente,
                    'Secciones con menor afiliación');
            });
            
            
        }        
        
        
        $('#filter__reload').on('click', function(){
            $('#edad').val(0);
            $('#demarc').val(0);
            $('#zona').html('<option value="0">- Zona -</option>');
            $('#seccion_filter').html('<option value="0">- Sección -</option>');
            $('#colonia').val(0);
            cargaFiltros();
        });
        /***************************************************************/
        
        
        /* BUSCADOR COLONIAS */
        $('#seccion').on('keyup', function(){
            cargaColonias();
        });
        
        
        function cargaColonias(){
            seccion = $('#seccion').val();
            $.post("ajax/colonias_por_seccion.php", { seccion: seccion }, function(data){
                if(data === ""){
                    $("#colonias").html('<i class="fa-solid fa-triangle-exclamation"></i><p class="error_seccion">No se han encontrado colonias con la sección especificada</p>');
                } else {
                    $("#colonias").html(data);
                }
            });	

        }
        /*************************************************************/
        
        $('#hamb').on('click', function(){
            $('#ajuste-fixed').fadeIn();
            //$('#close_hamb').css('display', 'flex');
            $('#close_hamb').fadeIn();
            $('#close_hamb').css('display', 'flex');
        });
        
        $('#close_hamb').on('click', function(){
            $('#ajuste-fixed').fadeOut();
            $('#close_hamb').fadeOut();
        });

    });
    </script>
    
    <script type="application/javascript" src="js/popup_usuario.js"></script>
    
    
</body>
</html>