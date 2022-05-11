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

mysql_select_db($database_conexao, $conexao);
$query_site_evento = "SELECT * FROM site_evento ORDER BY `data`, `hora` ASC";
$site_evento = mysql_query($query_site_evento, $conexao) or die(mysql_error());
$row_site_evento = mysql_fetch_assoc($site_evento);
$totalRows_site_evento = mysql_num_rows($site_evento);

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"> 
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="content-language" content="pt-BR" />
<meta name="language" content="pt-BR" />
<meta name="robots" content="noindex,nofollow" />
<meta name="author" content="Juliano Martins - (34) 8805 3396" />
<meta name="copyright" content=" Copyright © Success Sistemas - Todos os direitos reservados." />
<title>Área do Parceiro - Success Sistemas</title>
<link href="../../css/guia_painel.css" rel="stylesheet" type="text/css">
<script src="../../js/jquery.js"></script>
</head>
<body>

<div class="cabecalho"><? require_once('../padrao_cabecalho.php'); ?></div>

<!-- corpo -->
<div class="corpo">
	<div class="texto"> 
        <table width="100%" border="0" cellspacing="0" cellpadding="0">
            <tr>
            
                <td class="padrao_esquerda"><? require_once('../padrao_esquerda.php'); ?></td>
                                
                <td class="padrao_centro">                
                
                <!-- titulo -->
                <div class="titulo">
                <table width="100%" border="0" cellspacing="0" cellpadding="0">
                    <tr>
                        <td align="left">Eventos</td>
                        <td align="right"><a href="javascript:history.go(-1);"><img src="../../imagens/botao_voltar.jpg" border="0" /></a></td>
                    </tr>
                </table>
                </div>
                <div class="caminho"><a href="../index.php">Página inicial</a> &gt;&gt; Eventos</div>
                <!-- fim - titulo -->
                
                <div class="conteudo">
                
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
		
		<img src="../../imagens/site_evento/<?php echo $row_site_evento['imagem']; ?>" border="0" />
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
                
                 
                </td>
                
            </tr>
        </table>
  	</div>
</div>
<!-- fim - corpo -->

<div class="rodape"><? require_once('../padrao_rodape.php'); ?></div>

</body>
</html>
<?php
mysql_free_result($usuario);
mysql_free_result($site_evento);
?>
