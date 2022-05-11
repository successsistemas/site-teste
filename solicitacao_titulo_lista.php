<?php require('restrito.php'); ?>
<?php
require_once "Connections/conexao.php";

mysql_select_db($database_conexao, $conexao);

$q = strtolower($_GET["q"]);
if (!$q) return;

$sql = "select DISTINCT titulo as titulo FROM solicitacao where titulo LIKE '%$q%'";
$rsd = mysql_query($sql);
while($rs = mysql_fetch_array($rsd)) {
	$cname = $rs['titulo'];
	echo "$cname\n";
}
?>