<?php
$session_lifetime = 3600 * 24 * 4; // 4 days
session_set_cookie_params ($session_lifetime);
session_start();
require '../conexion_db.php';
require '../functions.php';
comprobar_nivel_ajax();
comprobar_login_ajax();

$colonias_options = '<option value="0">- Colonia -</option>';
$incoming_mun = $_POST["municipio"];
$incoming_dem = $_POST["dem"];
$incoming_sec = $_POST["seccion"];

$extra_sql_dem = $incoming_dem == 0 ? '' : ' AND ds.dem = "'.$incoming_dem.'" ' ;
$extra_sql_sec = $incoming_sec == 0 ? '' : ' AND ds.sec = "'.$incoming_sec.'" ' ;

$sql = '
SELECT cp.id_cp_colonia, cp.cp, cp.colonia, cp.municipio, m.id_municipio, ds.dem, ds.secc, ds.listado
	FROM cp_colonia cp
	INNER JOIN municipio m ON cp.municipio = m.municipio 
    INNER JOIN dem_secc ds ON m.id_municipio = ds.id_municipio 
    
    WHERE cp.municipio = m.municipio 
    AND m.id_municipio = "18" 
    '.$extra_sql_dem.'
    '.$extra_sql_sec.'
    GROUP BY cp.colonia
';

echo $sql;

$statement = $con->prepare($sql);
$statement->execute();
$colonias = $statement->fetchAll();

foreach($colonias as $colonia){
    $colonias_options = $colonias_options.'<option value="'.$colonia['id_cp_colonia'].'">'.$colonia['colonia'].'</option>';
}

echo $colonias_options;

?>