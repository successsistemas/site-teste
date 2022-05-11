<?
if($_SERVER["HTTP_X_CRON_AUTH"] != "3d5a01b606de5e79d6bcdaac8db316a0"){
	die("Acesso nao Autorizado");
}
?>
<?php require_once('Connections/conexao.php'); ?>
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

require_once('suporte_funcao_update.php');
require_once('emails.php');

// parametros
mysql_select_db($database_conexao, $conexao);
$query_parametros = "SELECT * FROM parametros";
$parametros = mysql_query($query_parametros, $conexao) or die(mysql_error());
$row_parametros = mysql_fetch_assoc($parametros);
$totalRows_parametros = mysql_num_rows($parametros);
// fim - parametros

// suporte
mysql_select_db($database_conexao, $conexao);
$query_suporte = sprintf("
							 SELECT id, situacao, status, data_suporte  
							 FROM suporte 
							 WHERE 
							 situacao = 'criada' and status_flag = 'a' and tela = 'g' and solicita_visita <> 's' 
							 
							 ORDER BY data_suporte DESC");
$suporte = mysql_query($query_suporte, $conexao) or die(mysql_error());
$row_suporte = mysql_fetch_assoc($suporte);
$totalRows_suporte = mysql_num_rows($suporte);
// fim - suporte

$prazo_excluir_suporte_horas = 3;
$prazo_excluir_suporte_segundos = $prazo_excluir_suporte_horas * 60 * 60;

$data_atual_segundos = strtotime("now");

$data_limite_segundos = $data_atual_segundos - $prazo_excluir_suporte_segundos;
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title></title>
</head>

<body style="font-family:Verdana, Geneva, sans-serif; font-size: 12px; margin: 0px;">


<div style="border: 1px solid #CCC; margin: 5px; padding: 5px;">
Data Atual: <? echo $data_atual = date('d-m-Y  H:i:s', $data_atual_segundos); ?>
<br>
Data Limite: <? echo $data_limite = date('d-m-Y  H:i:s', $data_limite_segundos); ?>
</div>

<? if($totalRows_suporte > 0){ ?>

	<?php do { ?>
    <div style="border: 1px solid #CCC; margin: 5px; padding: 5px;">
    
		<? $data_suporte_segundos = strtotime($row_suporte['data_suporte']); ?>

    
        <!-- dados -->
        Número do suporte: <?php echo $id = $row_suporte['id'];; ?>
        <br>
        Situação: <?php echo $row_suporte['situacao'];; ?>
        <br>
        Data Suporte: <? echo date('d-m-Y  H:i:s', strtotime($row_suporte['data_suporte'])); ?> 
		<br>
        <!-- fim - dados -->

		
        <!-- dentro do prazo -->
        <? if ($data_suporte_segundos >= $data_limite_segundos){ ?>
        
            <span style="color: green;">Dentro do prazo</span>
        
        <? } ?>
        <!-- dentro do prazo -->
        
        
        <!-- fora do prazo -->
        <? if ($data_suporte_segundos < $data_limite_segundos){ ?>
    
            <span style="color: red">Fora do prazo - EXCLUIR</span>
            <br>
            <?
            // DELETE - caso não tenha inserido dados --------------------------------------------------------------------
            $deleteSQL = sprintf("DELETE FROM suporte WHERE id = %s and situacao = 'criada' and status_flag = 'a' and tela = 'g'", GetSQLValueString($row_suporte['id'], "int"));
            mysql_select_db($database_conexao, $conexao);
            $Result_delete = mysql_query($deleteSQL, $conexao) or die(mysql_error());
            // DELETE - caso não tenha inserido dados --------------------------------------------------------------------
            ?>
        
        <? } ?>
        <!-- fora do prazo -->
        
    </div>        
    <?php } while ($row_suporte = mysql_fetch_assoc($suporte)); ?>

<? } ?>

<?
// insert - auto *****************************************************************
$insertSQL_auto = sprintf("
INSERT INTO auto (titulo, data, ip) 
VALUES (%s, %s, %s)", 
GetSQLValueString(basename($_SERVER['PHP_SELF']), "text"), 
GetSQLValueString(date('Y-m-d H:i:s'), "date"),
GetSQLValueString(basename($_SERVER["REMOTE_ADDR"]), "text"));
mysql_select_db($database_conexao, $conexao);
$Result_auto = mysql_query($insertSQL_auto, $conexao) or die(mysql_error());
// fim - insert - auto ***********************************************************
?>
</body>
</html>
<?php
mysql_free_result($suporte);
mysql_free_result($parametros);
?>