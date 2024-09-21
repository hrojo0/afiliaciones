<?php
//error_reporting(0);
if($usr_nivel == "4"){
    $statement = $con ->prepare('SELECT * FROM coordinacion_dems WHERE id_usuario = "'.$usr_usuario.'"');
    $statement->execute();
    $found = $statement->fetch();
    /*if($found)*/if(isset($_POST['user_dems'])){
        $incoming_user_dems = $_POST['user_dems'];
    } else{
        $statement = $con ->prepare('SELECT demarcaciones FROM coordinacion_dems WHERE id_usuario = "'.$usr_usuario.'"');
        $statement->execute();
        $user_dems = $statement->fetch();
        $user_dems = $user_dems[0];

        $incoming_user_dems = substr($user_dems, 0, -1);

    }
}

   switch($usr_nivel){
        case "1":
        case "2":
            $extra_sql = ' WHERE 1 ';
            break;
        case "3":
        
            $extra_sql = ' INNER JOIN municipio m ON m.municipio = p.ciudad WHERE p.ciudad = m.municipio AND m.id_municipio =  "'.$usr_mun.'" ';
            break;
        case "5":
            $extra_sql = ' INNER JOIN municipio m ON m.municipio = p.ciudad WHERE p.ciudad = m.municipio AND m.id_municipio = "'.$usr_mun.'" AND p.demarcacion = "'.$usr_dem.'" ';
            break;
        case "6":
            $extra_sql = ' INNER JOIN municipio m ON m.municipio = p.ciudad WHERE p.ciudad = m.municipio AND m.id_municipio = "'.$usr_mun.'" AND p.demarcacion = "'.$usr_dem.'" AND zona = "'.$usr_zona.'" ';
            break;
        case "4":
            $extra_sql = ' INNER JOIN municipio m ON m.municipio = p.ciudad WHERE p.ciudad = m.municipio AND m.id_municipio =  "'.$usr_mun.'" AND p.seccion IN ('.$incoming_user_dems.') ';
            break;
        case "7":
        case "8":
            $extra_sql = ' WHERE p.id_usuario = "'.$usr_usuario.'" ';
            break;
    }

?>