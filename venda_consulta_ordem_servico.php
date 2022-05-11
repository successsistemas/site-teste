<?php require('restrito.php'); ?>
<?php
require_once('Connections/conexao.php');
mysql_select_db($database_conexao, $conexao);

$id_venda = $_POST['id_venda'];
$ordem_servico = $_POST['ordem_servico'];

$sql = "SELECT id, ordem_servico FROM venda WHERE id<>$id_venda and ordem_servico = '$ordem_servico'";
$qr = mysql_query($sql) or die(mysql_error());
$row_qr = mysql_fetch_assoc($qr);

$postData = array(
        "retorno" => mysql_num_rows($qr),
        "id_venda" => $row_qr['id']
);

echo json_encode($postData);

mysql_free_result($qr);
?>