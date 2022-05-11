<?php require_once('../padrao_restrito.php'); ?>
<?php require_once('../../Connections/conexao.php'); ?>
<?
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

$IdComunicado = @$_POST['IdComunicado'];
$IdUsuario = @$_POST['IdUsuario'];

$query_comunicado_destinatario = sprintf("
SELECT IdComunicadoDestinatario, lido 
FROM comunicado_destinatario
WHERE IdUsuario=%s and IdComunicado=%s and IdComunicadoHistorico IS NULL", 
GetSQLValueString($IdUsuario, "int"),
GetSQLValueString($IdComunicado, "int"));
mysql_select_db($database_conexao, $conexao);
$comunicado_destinatario = mysql_query($query_comunicado_destinatario, $conexao) or die(mysql_error());
$row_comunicado_destinatario = mysql_fetch_assoc($comunicado_destinatario);
$totalRows_comunicado_destinatario = mysql_num_rows($comunicado_destinatario);

$lido = 0;
$lido_data = NULL;
if($row_comunicado_destinatario['lido'] == 0){
	$lido = 1;
	$lido_data = date('Y-m-d H:i:s');
}
$update_SQL_comunicado_destinatario = sprintf("
UPDATE comunicado_destinatario 
SET lido=%s, lido_data=%s 
WHERE IdComunicadoDestinatario=%s", 
GetSQLValueString($lido, "int"), 
GetSQLValueString($lido_data, "date"), 
GetSQLValueString($row_comunicado_destinatario['IdComunicadoDestinatario'], "int"));
mysql_select_db($database_conexao, $conexao);
$Result_update_comunicado_destinatario = mysql_query($update_SQL_comunicado_destinatario, $conexao) or die(mysql_error());

mysql_free_result($comunicado_destinatario);
?>