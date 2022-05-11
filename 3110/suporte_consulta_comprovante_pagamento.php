<?php require('restrito.php'); ?>
<?php
require_once('Connections/conexao.php');
mysql_select_db($database_conexao, $conexao);

$id_suporte = $_POST['id_suporte'];

$sql = "SELECT * FROM suporte_arquivos WHERE id_suporte = '$id_suporte' and comprovante_pagamento = 's'";
$qr = mysql_query($sql) or die(mysql_error());

if(mysql_num_rows($qr) == 0){
	
   echo 0;
   
}else{
	
	echo 1;

}
mysql_free_result($qr);
?>