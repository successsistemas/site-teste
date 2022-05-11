<?php require_once('restrito.php'); ?>
<?php require_once('Connections/conexao.php'); ?>
<?php
if (!function_exists("GetSQLValueString")) {
	function GetSQLValueString($theValue, $theType, $theDefinedValue = "", $theNotDefinedValue = "")
	{
		$theValue = get_magic_quotes_gpc() ? stripslashes($theValue) : $theValue;

		$theValue = function_exists("mysql_real_escape_string") ? mysql_real_escape_string($theValue) : mysql_escape_string($theValue);

		switch ($theType) {
			case "text":
				$theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
				break;
			case "long":
			case "int":
				$theValue = ($theValue != "") ? intval($theValue) : "NULL";
				break;
			case "double":
				$theValue = ($theValue != "") ? "'" . doubleval($theValue) . "'" : "NULL";
				break;
			case "date":
				$theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
				break;
			case "defined":
				$theValue = ($theValue != "") ? $theDefinedValue : $theNotDefinedValue;
				break;
		}
		return $theValue;
	}
}

require_once('parametros.php');

// verifica se existe o $_GET['id'] (id da solicitação)
$id_atual = "-1";
if (isset($_POST["id"])) {
	$id_atual = $_POST["id"];
}
// fim - verifica se existe o $_GET['id'] (id da solicitação)

// $id_atual = 'CF000035'; // para testes

// cliente_selecionado
mysql_select_db($database_conexao, $conexao);
$query_cliente_selecionado = sprintf("
SELECT 
	pessoa1, nome1, fantasia1, cgc1, insc1, cep1, endereco1, bairro1, cidade1, uf1, telefone1, celular1 
FROM 
	da01 
WHERE 
	codigo1 = %s and da01.sr_deleted <> 'T'
", 
GetSQLValueString($id_atual, "text"));
$cliente_selecionado = mysql_query($query_cliente_selecionado, $conexao) or die(mysql_error());
$row_cliente_selecionado = mysql_fetch_assoc($cliente_selecionado);
$totalRows_cliente_selecionado = mysql_num_rows($cliente_selecionado);
// fim - cliente_selecionado
?>

<?
$return_data = array(
	'pessoa1' => utf8_encode($row_cliente_selecionado['pessoa1']),
	'nome1' => utf8_encode($row_cliente_selecionado['nome1']),
	'fantasia1' => utf8_encode($row_cliente_selecionado['fantasia1']),
	'cgc1' => shellDescriptografa($row_cliente_selecionado['cgc1']), 
	'insc1' => shellDescriptografa($row_cliente_selecionado['insc1']), 
	'cep1' => utf8_encode($row_cliente_selecionado['cep1']),
	'endereco1' => utf8_encode($row_cliente_selecionado['endereco1']),
	'bairro1' => utf8_encode($row_cliente_selecionado['bairro1']),
	'cidade1' => utf8_encode($row_cliente_selecionado['cidade1']),
	'uf1' => utf8_encode($row_cliente_selecionado['uf1']),
	'telefone1' => utf8_encode($row_cliente_selecionado['telefone1']),
	'celular1' => utf8_encode($row_cliente_selecionado['celular1'])
);
echo json_encode($return_data);
?>

<?php mysql_free_result($cliente_selecionado); ?>