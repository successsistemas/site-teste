<?php require('restrito.php'); ?>
<?php
require_once('Connections/conexao.php');
mysql_select_db($database_conexao, $conexao);

$contrato = $_POST['contrato'];

$sql = "SELECT codigo17 FROM da37 WHERE codigo17 = '$contrato' and da37.sr_deleted <> 'T'";
$qr = mysql_query($sql) or die(mysql_error());

if(mysql_num_rows($qr) == 0){
   echo  0;
   
}else{

   echo  1;

}
mysql_free_result($qr);
?>