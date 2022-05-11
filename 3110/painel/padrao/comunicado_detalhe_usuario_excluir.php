<?php require_once('../padrao_restrito.php'); ?>
<?php require_once('../../Connections/conexao.php'); ?>
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

require_once('../parametros.php');
require_once('../funcao.php');

$janela = NULL;
$janela_url = NULL;
if (isset($_GET['janela'])) {
	$janela = $_GET['janela'];
	if ($janela == "index") {
		$janela_url = "&janela=index";
	}
}

// usuario
$colname_usuario = "-1";
if (isset($_SESSION['MM_Username'])) {
	$colname_usuario = $_SESSION['MM_Username'];
}
mysql_select_db($database_conexao, $conexao);
$query_usuario = sprintf("SELECT * FROM usuarios WHERE usuario = %s", GetSQLValueString($colname_usuario, "text"));
$usuario = mysql_query($query_usuario, $conexao) or die(mysql_error());
$row_usuario = mysql_fetch_assoc($usuario);
$totalRows_usuario = mysql_num_rows($usuario);
// fim - usuario

// comunicado
mysql_select_db($database_conexao, $conexao);
$query_comunicado = sprintf("
SELECT 
	comunicado.*, 
	usuarios.nome AS remetente  
FROM 
	comunicado 
LEFT JOIN 
	usuarios ON usuarios.IdUsuario = comunicado.IdUsuario 
WHERE 
	comunicado.IdComunicado = %s and 
	EXISTS (
		SELECT 
			'x' 
		FROM 
			comunicado_destinatario 
		WHERE 
			comunicado_destinatario.IdUsuario = %s and 
			comunicado_destinatario.IdComunicado = %s
	)
",
GetSQLValueString($_GET['IdComunicado'], "int"),

GetSQLValueString($row_usuario['IdUsuario'], "int"),
GetSQLValueString($_GET['IdComunicado'], "int"));
$comunicado = mysql_query($query_comunicado, $conexao) or die(mysql_error());
$row_comunicado = mysql_fetch_assoc($comunicado);
$totalRows_comunicado = mysql_num_rows($comunicado);
// fim - comunicado

if(
	$row_usuario['administrador_site'] <> "Y" and  
	$row_comunicado['IdUsuario'] <> $row_usuario['IdUsuario'] 
){
	$deleteGoTo = "../padrao/comunicado_detalhe.php?IdComunicado=" . $row_comunicado['IdComunicado'] . $janela_url;
	header(sprintf("Location: %s", $deleteGoTo));
	exit;
}

// delete --------------------------------------------------------------------------------------------------------------------------
if (
	($row_comunicado['IdUsuario'] == $row_usuario['IdUsuario'])
) {

	$colname_IdUsuario = "-1";
	if (isset($_GET['IdUsuario'])) {

		$colname_IdUsuario = $_GET['IdUsuario'];

		// comunicado_destinatario
		mysql_select_db($database_conexao, $conexao);
		$delete_SQL_comunicado_destinatario = sprintf(
			"
		DELETE FROM 
			comunicado_destinatario 
		WHERE 
			IdComunicado=%s and 
			IdUsuario=%s and 
			comunicado_destinatario.responsavel = 0
		",
		GetSQLValueString($_GET['IdComunicado'], "int"),
		GetSQLValueString($colname_IdUsuario, "int"));
		$Result_comunicado_destinatario = mysql_query($delete_SQL_comunicado_destinatario, $conexao) or die(mysql_error());
		// fim - comunicado_destinatario

	}

	$colname_praca = "-1";
	if (isset($_GET['praca'])) {

		$colname_praca = $_GET['praca'];

		// comunicado_destinatario
		mysql_select_db($database_conexao, $conexao);
		$delete_SQL_comunicado_destinatario = sprintf(
			"
		DELETE 
			comunicado_destinatario 
		FROM 
			comunicado_destinatario 
		LEFT JOIN 
			usuarios ON usuarios.IdUsuario = comunicado_destinatario.IdUsuario 
		WHERE 
			IdComunicado=%s and 
			comunicado_destinatario.responsavel = 0 and 
			usuarios.praca=%s and 
			comunicado_destinatario.leu IS NULL 
		",
		GetSQLValueString($_GET['IdComunicado'], "int"),
		GetSQLValueString($colname_praca, "text"));
		$Result_comunicado_destinatario = mysql_query($delete_SQL_comunicado_destinatario, $conexao) or die(mysql_error());
		// fim - comunicado_destinatario

	}

	$deleteGoTo = "../padrao/comunicado_detalhe.php?IdComunicado=" . $row_comunicado['IdComunicado'] . $janela_url;
	header(sprintf("Location: %s", $deleteGoTo));
	exit;

} else {

	$GoTo = "../padrao/comunicado_detalhe.php?IdComunicado=" . $row_comunicado['IdComunicado'] . $janela_url;
	header(sprintf("Location: %s", $GoTo));
	exit;

}
// fim - delete --------------------------------------------------------------------------------------------------------------------

mysql_free_result($usuario);

mysql_free_result($comunicado);
?>
