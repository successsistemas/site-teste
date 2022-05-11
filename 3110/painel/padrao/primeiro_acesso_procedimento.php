<?php require_once('../padrao_restrito.php'); ?>
<?php require_once('../../Connections/conexao.php'); ?>
<?php

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

require_once('../parametros.php');

// usuarios
$colname_usuario = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_usuario = $_SESSION['MM_Username'];
}
mysql_select_db($database_conexao, $conexao);
$query_usuario = sprintf("SELECT * FROM usuarios WHERE usuario = %s", GetSQLValueString($colname_usuario, "text"));
$usuario = mysql_query($query_usuario, $conexao) or die(mysql_error());
$row_usuario = mysql_fetch_assoc($usuario);
$totalRows_usuario = mysql_num_rows($usuario);
// fim - usuarios

// geral_procedimento_site
$colname_geral_procedimento_site = "-1";
if (isset($_GET['IdProcedimentoSite'])) {
  $colname_geral_procedimento_site = $_GET['IdProcedimentoSite'];
}
mysql_select_db($database_conexao, $conexao);
$query_geral_procedimento_site = sprintf("SELECT * FROM geral_procedimento_site WHERE IdProcedimentoSite = %s", GetSQLValueString($colname_geral_procedimento_site, "int"));
$geral_procedimento_site = mysql_query($query_geral_procedimento_site, $conexao) or die(mysql_error());
$row_geral_procedimento_site = mysql_fetch_assoc($geral_procedimento_site);
$totalRows_geral_procedimento_site = mysql_num_rows($geral_procedimento_site);
// fim - geral_procedimento_site

// geral_procedimento_site_ant
mysql_select_db($database_conexao, $conexao);
$query_geral_procedimento_site_ant = sprintf("
											SELECT IdProcedimentoSite 
											FROM geral_procedimento_site 
											WHERE IdProcedimentoSite < %s 
											ORDER BY IdProcedimentoSite DESC
											LIMIT 1", 
											GetSQLValueString($row_geral_procedimento_site['IdProcedimentoSite'], "int"));
$geral_procedimento_site_ant = mysql_query($query_geral_procedimento_site_ant, $conexao) or die(mysql_error());
$row_geral_procedimento_site_ant = mysql_fetch_assoc($geral_procedimento_site_ant);
$totalRows_geral_procedimento_site_ant = mysql_num_rows($geral_procedimento_site_ant);
// fim - geral_procedimento_site_ant

// geral_procedimento_site_prox
mysql_select_db($database_conexao, $conexao);
$query_geral_procedimento_site_prox = sprintf("
											SELECT IdProcedimentoSite 
											FROM geral_procedimento_site 
											WHERE IdProcedimentoSite > %s 
											ORDER BY IdProcedimentoSite ASC 
											LIMIT 1", 
											GetSQLValueString($row_geral_procedimento_site['IdProcedimentoSite'], "int"));
$geral_procedimento_site_prox = mysql_query($query_geral_procedimento_site_prox, $conexao) or die(mysql_error());
$row_geral_procedimento_site_prox = mysql_fetch_assoc($geral_procedimento_site_prox);
$totalRows_geral_procedimento_site_prox = mysql_num_rows($geral_procedimento_site_prox);
// fim - geral_procedimento_site_prox

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

<link rel="stylesheet" href="../../css/suporte.css" type="text/css" />
<title>Procedimento</title>
</head>

<body>

<div class="div_solicitacao_linhas">
<table cellspacing="0" cellpadding="0" width="100%">
	<tr>
		<td style="text-align:left">
		Procedimentos do site
		</td>

		<td style="text-align: right">
		&lt;&lt; <a href="primeiro_acesso.php" target="_top">Voltar</a>
        </td>
	</tr>
</table>
</div>

<div class="div_solicitacao_linhas4">
<table cellspacing="0" cellpadding="0" width="100%">
	<tr>
		<td style="text-align: center">
            <table width="100%" border="0" cellspacing="0" cellpadding="0">
            <tr>
                <td width="50%" align="left" style="font-size: 14px; font-weight: bold;">
                <? if($totalRows_geral_procedimento_site_ant == 1){ ?>
                <a href="primeiro_acesso_procedimento.php?IdProcedimentoSite=<? echo $row_geral_procedimento_site_ant['IdProcedimentoSite']; ?>" style="color: #000;">&lt;&lt; anterior</a>
                <? } else { ?>
                &nbsp;
                <? } ?>
                </td>
                
                <td width="50%" align="right" style="font-size: 14px; font-weight: bold;">
                <? if($totalRows_geral_procedimento_site_prox == 1){ ?>
                <a href="primeiro_acesso_procedimento.php?IdProcedimentoSite=<? echo $row_geral_procedimento_site_prox['IdProcedimentoSite']; ?>" style="color: #000;">próximo &gt;&gt;</a>
                <? } else { ?>
                &nbsp;
                <? } ?>
                </td>
            </tr>
            </table>
        </td>
	</tr>
</table>
</div>

<div class="div_solicitacao_linhas3">
<table cellspacing="0" cellpadding="0" width="100%">
	<tr>
		<td style="text-align:left">
		<span class="label_solicitacao">Título: </span>
		<?php echo $row_geral_procedimento_site['titulo']; ?>      
        </td>
	</tr>
</table>
</div>


<div class="div_solicitacao_linhas4">
<table cellspacing="0" cellpadding="0" width="100%">
	<tr>
		<td style="text-align:left">
		<?php echo $row_geral_procedimento_site['descricao']; ?>  
        </td>
	</tr>
</table>
</div>


</body>
</html>
<?php
mysql_free_result($usuario);
mysql_free_result($geral_procedimento_site);
mysql_free_result($geral_procedimento_site_ant);
mysql_free_result($geral_procedimento_site_prox);
?>
