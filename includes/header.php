<?php 
$statement = $con->prepare('SELECT nivel_usuario FROM nivel_usuario WHERE id_nivel_usuario = '.$usr_nivel);
$statement->execute();
$user_nivel = $statement->fetch();


$statement = $con->prepare('SELECT user, nombre, apellidos, foto FROM usuario WHERE id_usuario = '.$usr_usuario);
$statement->execute();
$usuario_bd = $statement->fetch();

$usuario_foto = $usuario_bd['foto'];
$usuario_user = $usuario_bd['user'];
$usuario_nombre = $usuario_bd['nombre'];
$usuario_apellidos = $usuario_bd['apellidos'];


?>
<div class="header_user">   
   <div class="flex_header_user" id="<?php echo $usr_nivel; ?>">
       <div class="header_user_info" id="<?php echo $usuario_user; ?>">
           <div class="header_datos_persona" id="<?php echo $usr_usuario;?>">
               <div class="logout_"><span id="logout" >Cerrar Sesión</span></div>
               <p class="info" id="header_nombre"> <?php echo $usuario_nombre.' '.$usuario_apellidos; ?></p>
           </div>
            <div class="header_datos_persona header_info_nivel">
               <div class="ghost_lvl" id="<?php echo $usr_nivel;?>"></div>
                <p class="info" id="header_nivel"><?php echo $user_nivel[0]; 
                    $statement = $con->prepare('SELECT municipio FROM municipio WHERE id_municipio = "'.$usr_mun.'"');
                    $statement->execute();
                    $municipio_header = $statement->fetch();
                    switch($usr_nivel){
                        case "1":
                        case "2":
                            break;
                        case "3":
                            $statement = $con ->prepare('SELECT demarcaciones FROM coordinacion_dems WHERE id_usuario = "'.$usr_usuario.'"');
                            $statement->execute();
                            echo ', Municipio de '.$municipio_header[0];
                            break;
                        case "4":
                            $statement = $con ->prepare('SELECT demarcaciones FROM coordinacion_dems WHERE id_usuario = "'.$usr_usuario.'"');
                            $statement->execute();
                            $user_dems = $statement->fetch();
                            $user_dems = $user_dems[0];
                            echo ' '.substr($user_dems, 0, -1).', Zona '.$usr_zona.', Demarcación '.$usr_dem.', Municipio de '.$municipio_header[0];
                            break;
                        case "5":
                            echo ' '.$usr_dem.', Municipio de '.$municipio_header[0];
                            break;
                        case "6":
                            echo ' '.$usr_zona.', Demarcación '.$usr_dem.', Municipio de '.$municipio_header[0];
                            break;
                        case "7":
                            echo ' '.$municipio_header[0].', Demarcación '.$usr_dem.', Zona '.$usr_zona;
                            break;
                    }
                    ?></p>
            </div>
        </div>
        <div class="header_foto" id="foto_perfil">
           <img id="img_perfil" src="<?php $foto = $usuario_foto == "0" ? 'img/user.png' : $usuario_foto; echo $foto;?>" alt="">
       </div>
    </div>
</div>


<script type="text/javascript">
    $('#logout').click(function(){
      window.location = 'logout.php';
    });
</script>