<?php require_once('Connections/conexao.php'); ?>
<?php
// careega tNG classes
require_once('includes/tng/tNG.inc.php');

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

$colname_site_link = "-1";
if (isset($_GET['IdLink'])) {
  $colname_site_link = $_GET['IdLink'];
}
mysql_select_db($database_conexao, $conexao);
$query_site_link = sprintf("SELECT * FROM site_link WHERE IdLink = %s", GetSQLValueString($colname_site_link, "int"));
$site_link = mysql_query($query_site_link, $conexao) or die(mysql_error());
$row_site_link = mysql_fetch_assoc($site_link);
$totalRows_site_link = mysql_num_rows($site_link);
// fim - usuário logado via SESSION

?>
<?

$colname_site_link_foto = "-1";
if (isset($_GET['IdLink'])) {
  $colname_site_link_foto = $_GET['IdLink'];
}
mysql_select_db($database_conexao, $conexao);
$query_site_link_foto = sprintf("SELECT * FROM site_link_foto WHERE IdLink = %s ORDER BY IdLinkFoto DESC", GetSQLValueString($colname_site_link_foto, "int"));
$site_link_foto = mysql_query($query_site_link_foto, $conexao) or die(mysql_error());
$row_site_link_foto = mysql_fetch_assoc($site_link_foto);
$totalRows_site_link_foto = mysql_num_rows($site_link_foto);

$total = mysql_num_rows($site_link_foto);
$colunas = "3"; 

// Show Dynamic Thumbnail
$objDynamicThumb_foto = new tNG_DynamicThumbnail("", "KT_thumbnail1");
$objDynamicThumb_foto->setFolder("imagens/site_link_foto/");
$objDynamicThumb_foto->setRenameRule("{site_link_foto.foto}");
$objDynamicThumb_foto->setResize(200, 0, true);
$objDynamicThumb_foto->setWatermark(false);
$objDynamicThumb_foto->setPopupSize(800, 600, false);
$objDynamicThumb_foto->setPopupNavigation(false);
$objDynamicThumb_foto->setPopupWatermark(false);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"> 
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<script src="js/jquery.js"></script>

<script type="text/javascript" src="js/thickbox.js"></script>
<link rel="stylesheet" href="css/thickbox.css" type="text/css" media="screen" />

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

<td class="menu"><?php include('menu.php'); ?>
</td>

<td class="conteudo">    
<!-- conteúdo da página - início -->
<div class="conteudo_div">

  	<!-- caminho/voltar/título -->
    <div class="voltar"><a href="javascript:history.go(-1);">Voltar</a></div>
    <div class="caminho">
		<a href="index.php">Página Inicial</a> >> <strong><?php echo $row_site_link['titulo']; ?></strong>
    </div> 
    <div class="titulos"><?php echo $row_site_link['titulo']; ?></div>
  	<!-- caminho/voltar/título -  fim -->


	<? if ( $row_site_link_foto['IdLinkFoto'] != "" ) { ?> 
    <div align="center" style="padding-bottom:10px;">
        <table border="0" align="center" cellpadding="2" cellspacing="5">
        <?php
        if ($total>0) { 
        for($i=0; $i<$total; $i++) { 
        if (($i%$colunas)==0) { 
        ?>
        <tr valign="top">
        <? } ?>
        <td width="25%" valign="top" style="text-align:center;">
        
        <div style="border:1px solid #CCCCCC; padding: 5px 5px 5px 5px; height:auto;">
        <div>
        
        <a href="imagens/site_link_foto/<? echo $row_site_link_foto['foto']; ?>" class="thickbox">
        <img src="<?php echo $objDynamicThumb_foto->Execute(); ?>" border="0" />
        </a>
        </div>
        </div>
        
        </td>
        <?
        $numero_registros_por_linha = $i % $colunas;
        $numero_da_coluna = $colunas-1;
        if( $numero_registros_por_linha == $numero_da_coluna ) { ?>
        </tr>
        <? } ?>
        <? $row_site_link_foto = mysql_fetch_assoc($site_link_foto); ?>
        <? }} ?>
        </table>
    </div>
    <? } ?>
    
    <?php echo $row_site_link['texto']; ?>

</div>
<!-- conteúdo da página - fim -->
</td>
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
mysql_free_result($site_link);
mysql_free_result($site_link_foto);
?>
