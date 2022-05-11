<?php
error_reporting (E_ALL & ~ E_NOTICE & ~ E_DEPRECATED);

# FileName="Connection_php_mysql.htm"
# Type="MYSQL"
# HTTP="true"
$hostname_conexao = "mysql.success.inf.br";
$database_conexao = "success07";
$username_conexao = "success07";
$password_conexao = "vacapreta123";
$conexao = mysql_pconnect($hostname_conexao, $username_conexao, $password_conexao) or trigger_error(mysql_error(),E_USER_ERROR); 
?>