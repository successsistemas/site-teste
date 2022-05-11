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

$currentPage = $_SERVER["PHP_SELF"];

// usuário logado via SESSION
$colname_usuario = "-1";
if (isset($_SESSION['IdUsuario'])) {
  $colname_usuario = $_SESSION['IdUsuario'];
}
mysql_select_db($database_conexao, $conexao);
$query_usuario = sprintf("SELECT * FROM usuarios WHERE IdUsuario = %s", GetSQLValueString($colname_usuario, "int"));
$usuario = mysql_query($query_usuario, $conexao) or die(mysql_error());
$row_usuario = mysql_fetch_assoc($usuario);
$totalRows_usuario = mysql_num_rows($usuario);
// fim - usuário logado via SESSION

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"> 
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<script src="js/jquery.js"></script>

<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>Success Sistemas</title>
<link href="css/guia.css" rel="stylesheet" type="text/css">
</head>

<body>
<?php include('topo.php'); ?>
<table class="tabela_geral" cellpadding="0" cellspacing="0">

<tr>
	<td class="tabela_geral_acima"></td>
</tr>

<tr>
	<td class="tabela_geral_centro">

<!-- menu/conteúdo - início -->
<table width="100%" border="0" cellspacing="0" cellpadding="0">
<tr>
<td class="menu"><?php include('menu.php'); ?></td>
<td class="conteudo">
    
<!-- conteúdo da página - início -->
<div class="conteudo_div">

  	<!-- caminho/voltar/título -->

    <div class="voltar"><a href="javascript:history.go(-1);">Voltar</a></div>
    <div class="caminho">
		<a href="index.php">Página Inicial</a> >> <strong>Seja um parceiro</strong></div>

    <div class="titulos">Seja um parceiro</div>
  	<p align="justify">
  	  <!-- caminho/voltar/título -  fim -->
  	  
<?
$emaildestino = "juliano@clicaraxa.com";
$msg = "
\n Nome: $nome 
\n E-mail: $email 
\n Cidade: $cidade 
\n Estado: $estado 
\n Telefone: $telefone
\n Outras informações: $outras
\n";
mail($emaildestino, $assunto, $msg, "from: $email,");
?>
  	  
Mensagem enviado com sucesso. Em breve retornaremos o seu contato. Obrigado.</p>
</div>
<!-- conteúdo da página - fim --></td>
</tr>
</table>
<!-- menu/conteúdo - fim -->

	</td>
</tr>

<tr>
	<td class="tabela_geral_abaixo"></td>
</tr>

</table>
<?php include('creditos.php'); ?>
</body>

</html>
<?php
mysql_free_result($usuario);
?>
