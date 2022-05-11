<?php 
if (!isset($_SESSION)) {
	session_start();
}

require_once('../Connections/conexao.php');
require_once('../parametros.php');

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

// usuario
$colname_usuario = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_usuario = $_SESSION['MM_Username'];
}
mysql_select_db($database_conexao, $conexao);
$query_usuario = sprintf("SELECT IdUsuario, primeiro_acesso, praca, aniversario, telefone FROM usuarios WHERE usuario = %s", GetSQLValueString($colname_usuario, "text"));
$usuario = mysql_query($query_usuario, $conexao) or die(mysql_error());
$row_usuario = mysql_fetch_assoc($usuario);
$totalRows_usuario = mysql_num_rows($usuario);
// fim - usuario

$acesso = 0;

// geral_tipo_praca (status)
if($praca_status == 1){

	$acesso = 1;

} else {

	$acesso = 0;
	header ("location: ../relatorio_fechamento.php");
	exit;
	
}
// fim - geral_tipo_praca (status)

// primeiro_acesso
if($row_usuario['primeiro_acesso'] == 0){

	$acesso = 1;
	$_SESSION['MM_primeiro_acesso_controle'] = 0;

} else if($row_usuario['primeiro_acesso'] == 1){

	$acesso = 0;
	$_SESSION['MM_primeiro_acesso_controle'] = 1;
	header ("location: padrao/primeiro_acesso.php");
	exit;
	
}
// fim - primeiro acesso

// dados (aniversario/telefone)
if($row_usuario['aniversario'] == NULL or $row_usuario['telefone'] == NULL){

	$acesso = 0;
	header ("location: padrao/dados.php");
	exit;

} else {

	$acesso = 1;
		
}
// fim - dados (aniversario/telefone)

// acesso ***************************************************************
if($acesso == 0){
	
	header ("location: ../index.php");
	
} else if($acesso == 1){
	
	$acao = NULL;
	if(isset($_GET['acao']) and $_GET['acao']=='sucesso'){
		$acao = "?acao=".$_GET['acao'];
	}

	header ("location: padrao/index.php".$acao);
	
}
// fim - acesso *********************************************************

mysql_free_result($usuario);
?>