<?php require('restrito.php'); ?>
<?php
require_once('Connections/conexao.php');
mysql_select_db($database_conexao, $conexao);

$id_programa = $_POST['id_programa'];

$sql = "SELECT * FROM geral_tipo_subprograma WHERE id_programa = '$id_programa' ORDER BY subprograma ASC";
$qr = mysql_query($sql) or die(mysql_error());

if(mysql_num_rows($qr) == 0){
   echo  '<option value="">'.htmlentities('Nenhum subprograma encontrado').'</option>';
   
}else{

   echo  '<option value="">'.htmlentities('Escolha ... ').'</option>';

   while($ln = mysql_fetch_assoc($qr)){
      echo '<option value="'.$ln['id_subprograma'].'">'.$ln['subprograma'].'</option>';
   }
}
?>