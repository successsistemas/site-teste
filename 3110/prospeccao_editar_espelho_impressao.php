<?php 
require_once('restrito.php');
require_once('Connections/conexao.php');

if (!function_exists("GetSQLValueString")) {
function GetSQLValueString($theValue, $theType, $theDefinedValue = "", $theNotDefinedValue = "") 
{
  if (PHP_VERSION < 6) {
    $theValue = get_magic_quotes_gpc() ? stripslashes($theValue) : $theValue;
  }

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
      $theValue = ($theValue != "") ? doubleval($theValue) : "NULL";
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

// entrada
$id_venda = "-1";
if (isset($_POST['id_venda'])) {
  $id_venda = $_POST['id_venda'];
}
// fim - entrada

// venda_espelho_atualiza
$query_venda_espelho_atualiza = sprintf("
										UPDATE venda
										SET espelho = 1 
										WHERE id = %s", 
										GetSQLValueString($id_venda, "int"));
mysql_select_db($database_conexao, $conexao);
$Result_venda_espelho_atualiza = mysql_query($query_venda_espelho_atualiza, $conexao) or die(mysql_error());
// fim - venda_espelho_atualiza
?>