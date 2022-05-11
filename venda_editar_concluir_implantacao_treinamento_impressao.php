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

// venda_conclusao_implantacao_treinamento_atualiza
$query_venda_conclusao_implantacao_treinamento_atualiza = sprintf("
										UPDATE venda
										SET conclusao_implantacao_treinamento = 1, conclusao_implantacao_treinamento_data = %s 
										WHERE id = %s", 
										GetSQLValueString(date('Y-m-d H:i:s'), "date"),
										GetSQLValueString($id_venda, "int"));
mysql_select_db($database_conexao, $conexao);
$Result_venda_conclusao_implantacao_treinamento_atualiza = mysql_query($query_venda_conclusao_implantacao_treinamento_atualiza, $conexao) or die(mysql_error());
// fim - venda_conclusao_implantacao_treinamento_atualiza
?>