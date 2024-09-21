<?php
header('Content-Type: text/html; charset=UTF-8');
$session_lifetime = 3600 * 24 * 4; // 4 days
session_set_cookie_params ($session_lifetime);
session_start();

if(!empty($_SESSION)){
    header('Location:index.php');
}

$wrong_user = 0;
if($_SERVER['REQUEST_METHOD'] == 'POST'){
    
    
    require 'conexion_db.php';
    require 'functions.php';
    $user = filter_var($_POST['user'], FILTER_SANITIZE_STRING);
    $pass = encript_pass($_POST['pass']);
    
    $statement = $con->prepare('
        SELECT * FROM usuario WHERE user = :user');
        $statement->execute(array(':user' => $user));
        $resultado = $statement->fetch();
    
    if($resultado){
        if($resultado['user'] == $user && $resultado['pass'] == $pass){
            $_SESSION['usuario'] = $resultado['id_usuario'];
            $_SESSION['nombre'] = $resultado['nombre'];
            $_SESSION['apellidos'] = $resultado['apellidos'];
            $_SESSION['user'] = $resultado['user'];
            $_SESSION['foto'] = $resultado['foto'];
            $_SESSION['id_nivel_user'] = $resultado['id_nivel_usuario'];
            $_SESSION['municipio'] = $resultado['municipio'];
            $_SESSION['demarcacion'] = $resultado['dem'];
            $_SESSION['zona'] = $resultado['zona'];
            $_SESSION['seccion'] = $resultado['seccion'];
            $_SESSION['usuario_registrado'] = 'no';
            header('Location: index.php');
        }else {
            $wrong_user = 1;
        }
    }
}

?>

<!DOCTYPE html>
<html lang="es-MX">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Iniciar Sesi&oacute;n</title>
   
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    
    <link rel="stylesheet" type="text/css" href="css/normalize.css">
    <link rel="stylesheet" type="text/css" href="css/login.css">
    <link rel="stylesheet" type="text/css" href="css/base.css">
    <link rel="stylesheet" type="text/css" href="css/form.css">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Titillium+Web:wght@200;300;400;600;700;900&display=swap" rel="stylesheet">
    
    <script src="https://kit.fontawesome.com/9c52d851d9.js" crossorigin="anonymous"></script>
</head>
<body>
   
<div class="cont">
    <div class="form-login" onkeypress="return checkSubmit(event)">
        <form class="login-form" id="login-form" name="login" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
            <div class="form__icon">
                <p id="title_login">Con GP</p>
            </div>
            <div class="form__div">
                <input type="text" class="form__input" placeholder=" " name="user">
                <label for="" class="form__label">Usuario</label>
            </div> 
            
            <div class="form__div">
                <input type="password" class="form__input" placeholder=" " name="pass">
                <label for="" class="form__label">Contrase&ntilde;a</label>
            </div> 
            
            <?php if($wrong_user == 1):?>
            <div class="form__error">
                <i class="fa-solid fa-triangle-exclamation"></i> <p class="error post">Usuario y/o contraseña incorrectos</p>
            </div>
            <?php endif;?>
            
            <div class="form__button" onClick="login.submit();">
                <p class="submit-txt">Iniciar Sesi&oacute;n</p>
            </div>
       </form>
   </div>
</div>
   
   
    <script type="text/javascript">
        function checkSubmit(e) {
           if(e && e.keyCode == 13) {
              login.submit();
           }
        }
    </script>
    
</body>
</html>