<?php 
require_once('padrao_restrito.php');

session_start();

session_destroy();

session_write_close(); 

header('location: ../index.php');
?>