<?php require_once('restrito.php'); ?>
<?php require_once('Connections/conexao.php'); ?>
<?php require_once('parametros.php'); ?>
<?php


$cpf_cnpj = shellCriptografa(@$_POST['cpf_cnpj']);

// da01_listar
mysql_select_db($database_conexao, $conexao);
$query_da01_listar = "
SELECT 
	da01.cgc1, 
	da37.codigo17 
FROM 
	da01 
LEFT JOIN 
	da37 ON da37.cliente17 = da01.codigo1 and da37.sr_deleted <> 'T'
WHERE 
	da01.cgc1 = '$cpf_cnpj' and 
	da01.sr_deleted <> 'T' 
LIMIT 1
";
$da01_listar = mysql_query($query_da01_listar, $conexao) or die(mysql_error());
$row_da01_listar = mysql_fetch_assoc($da01_listar);
$totalRows_da01_listar = mysql_num_rows($da01_listar);
// fim - da01_listar
?>

<?
$retorno = array(
	'contador' => $totalRows_da01_listar, 
	'contrato' => $row_da01_listar['codigo17']
);

return print json_encode($retorno);
?>

<? mysql_free_result($da01_listar); ?>