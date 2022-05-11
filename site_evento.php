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

mysql_select_db($database_conexao, $conexao);
$query_site_evento = "SELECT * FROM site_evento ORDER BY `data`, `hora` ASC";
$site_evento = mysql_query($query_site_evento, $conexao) or die(mysql_error());
$row_site_evento = mysql_fetch_assoc($site_evento);
$totalRows_site_evento = mysql_num_rows($site_evento);

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
		<a href="index.php">Página Inicial</a> >> <strong>Eventos</strong>
    </div>
    <div class="titulos">Eventos</div>
  	<!-- caminho/voltar/título -  fim -->

	<? $contador_site_evento = 0; ?>
	<?php do { ?>
	<? $contador_site_evento = $contador_site_evento + 1; ?>
            
        <?php if($row_site_evento['titulo'] != ""){ ?>
            <div style="margin-top: 10px; font-size: 16px; font-weight: bold; color: #C00;">
            <?php echo $row_site_evento['titulo']; ?>
            </div>
        <? } ?>
       
        <?php if($row_site_evento['data'] != ""){ ?>
            <div style="margin-top: 5px;">
            Data: <strong><? echo date('d-m-Y', strtotime($row_site_evento['data'])); ?> <? echo date('H:i', strtotime($row_site_evento['hora'])); ?></strong>
            </div>
        <? } ?>
        
        <?php if($row_site_evento['imagem'] != ""){ ?>
            <div style="margin-top: 10px; text-align: center;">
            
            <img src="imagens/site_evento/<?php echo $row_site_evento['imagem']; ?>" border="0" />
            </div>
        <? } ?>
        
        <?php if($row_site_evento['texto'] != ""){ ?>
            <div style="margin-top: 10px;">
            <?php echo $row_site_evento['texto']; ?>
            </div>
        <? } ?>

        <? if($totalRows_site_evento > 1 and ($totalRows_site_evento != $contador_site_evento) ){ ?>
        	<div style="margin-top: 20px; margin-bottom: 20px; height: 2px; background-color:#DDD;"></div>
        <? } ?>

    <?php } while ($row_site_evento = mysql_fetch_assoc($site_evento)); ?>
    
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
mysql_free_result($site_evento);
?>
