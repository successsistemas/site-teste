<?php require('restrito.php'); ?>
<?php
require_once('Connections/conexao.php');

require_once('parametros.php');

mysql_select_db($database_conexao, $conexao);

$cpf_cnpj = shellCriptografa($_POST['cpf_cnpj']);

$sql = "SELECT cgc1, nome1 FROM da01 WHERE cgc1 = '$cpf_cnpj' and da01.sr_deleted <> 'T'";
$qr = mysql_query($sql) or die(mysql_error());
$row_qr = mysql_fetch_assoc($qr);

$postData = array(
	"teste" => $sql,
	"retorno" => mysql_num_rows($qr),
	"cgc1" => shellDescriptografa($row_qr['cgc1']),
	"nome1" => $row_qr['nome1']
);

echo json_encode($postData);

mysql_free_result($qr);
?>