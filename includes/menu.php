<div class="ajuste-fixed" id="ajuste-fixed">
   <div class="side-menu" id="side-menu">
    <div class="menu-items" id="menu-items">
        <ul class="nav">
            <a href="index.php"><li id="menu_inicio"><i class="fas fa-home"></i> Inicio</li></a>
            <a href="cartografia.php"><li id="menu_cartografia"><i class="fa-solid fa-map"></i> Cartografía</li></a>
            <a href="afinidad.php"><li id="menu_afinidad"><i class="fa-solid fa-handshake"></i> Afinidad</li></a>
            <?php if($usr_nivel != "8"):?>
            <a href="aspirantes.php"><li id="menu_aspirantes"><i class="fa-solid fa-id-badge"></i> Aspirantes</li></a>
            <?php endif;?>
            
            <?php if($usr_nivel != "8"):?>
            <a href="registro-ine.php"><li id="menu_registro"><i class="fa-solid fa-id-card"></i> Registro/INE</li></a>            
            <?php else:?>
            <a href="registro-ine.php"><li id="menu_registro"><i class="fa-solid fa-id-card"></i> Registro</li></a>            
            <?php endif;?>

            <?php if($usr_nivel != "7" && $usr_nivel != "8"): ?>
            <a href="usuarios.php"><li id="menu_usuarios"><i class="fa-solid fa-users"></i> Usuarios</li></a>
            <?php endif;?>
            
            <?php $specifiedDate = new DateTime('2024-03-01'); $currentDate = new DateTime(); if ($currentDate > $specifiedDate):?>
            <a href="tu-reporte.php"><li id="menu_tu_reporte"><i class="fa-solid fa-list"></i> Tu Reporte</li></a>
            <?php endif;?>
        </ul>
    </div>
</div>
</div>