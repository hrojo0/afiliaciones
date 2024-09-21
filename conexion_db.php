<?php 
require 'config.php'; 

try{
        $con = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
        return $con;
    }catch (PDOException $e){
        return false;
    }

?>