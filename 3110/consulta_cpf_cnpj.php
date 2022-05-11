<?php require_once('restrito.php'); ?>
<?php require_once('Connections/conexao.php'); ?>
<?php
$cpf_cnpj = @$_POST['cpf_cnpj'];

// da01_listar
mysql_select_db($database_conexao, $conexao);
$query_da01_listar = "
SELECT 
	cgc1 
FROM 
	da01 
WHERE 
	cgc1 = '$cpf_cnpj'
";
$da01_listar = mysql_query($query_da01_listar, $conexao) or die(mysql_error());
$row_da01_listar = mysql_fetch_assoc($da01_listar);
$totalRows_da01_listar = mysql_num_rows($da01_listar);
// fim - da01_listar
?>

<?
$retorno = array(
	'contador' => $totalRows_da01_listar
);

return print json_encode($retorno);
?>

<? mysql_free_result($da01_listar); ?>