<?php if($_SERVER["REMOTE_ADDR"] != "189.38.95.36"){ die("Acesso nao Autorizado"); } // restringe acesso somente ao IP do site ?>
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

require_once('funcao_dia_util.php');

// parametros
mysql_select_db($database_conexao, $conexao);
$query_parametros = "SELECT * FROM parametros";
$parametros = mysql_query($query_parametros, $conexao) or die(mysql_error());
$row_parametros = mysql_fetch_assoc($parametros);
$totalRows_parametros = mysql_num_rows($parametros);
// fim - parametros

// comunicado
mysql_select_db($database_conexao, $conexao);
$query_comunicado = sprintf("
							 SELECT * 
							 FROM comunicado 
							 WHERE 
							 tipo = 'c' 							 
							 ORDER BY data_criacao ASC");
$comunicado = mysql_query($query_comunicado, $conexao) or die(mysql_error());
$row_comunicado = mysql_fetch_assoc($comunicado);
$totalRows_comunicado = mysql_num_rows($comunicado);
// fim - comunicado

$prazo_encerramento_comunicado_dias = $row_parametros['prazo_excluir_comunicado'];
$prazo_encerramento_comunicado_segundos = $prazo_encerramento_comunicado_dias * 86400;
$data_atual_segundos = strtotime("now");
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title></title>
</head>

<body style="font-family:Verdana, Geneva, sans-serif; font-size: 12px; margin: 0px;">
<? if($totalRows_comunicado > 0 and $prazo_encerramento_comunicado_dias > 0){ ?>   
<?php do { ?>
<div style="border: 1px solid #CCC; margin: 5px; padding: 5px;">

	<!-- dados -->
	<? $previsao_geral_segundos = strtotime($row_comunicado['data_criacao']); ?>
   
    <? $previsao_limite = proximoDiaUtil(date('Y-m-d H:i:s', $previsao_geral_segundos + $prazo_encerramento_comunicado_segundos)); ?>
    <? $previsao_limite_segundos = strtotime($previsao_limite); ?>
    
	<? $previsao_geral_passados_dias = (($data_atual_segundos - $previsao_limite_segundos) + $prazo_encerramento_comunicado_segundos) / 86400; ?>

    Número do comunicado: <?php echo $id = $row_comunicado['IdComunicado']; ?>
	<br>
	Criação: <? echo date('d-m-Y  H:i:s', $previsao_geral_segundos); ?>
    <br>
	Aberto a: <? echo $previsao_geral_passados_dias; ?> dia(s)
	<br>
    Exclusão: <? echo date('d-m-Y  H:i:s', $previsao_limite_segundos); ?>
	<br>
	<!-- fim - dados -->
	
    
    <!-- dentro do prazo -->
    <? if ($previsao_geral_passados_dias < $prazo_encerramento_comunicado_dias){ ?>

		<!-- faltam 2 ou menos dias -->
		<? if($prazo_encerramento_comunicado_dias > 0 and $previsao_geral_passados_dias >= $prazo_encerramento_comunicado_dias-2){ ?>
            
            <div style="color: green;">Dentro do prazo - faltam 2 ou menos dias</div>
            
        <? } ?>
        <!-- fim - faltam 2 ou menos dias -->
    
    <? } ?>
    <!-- fim - dentro do prazo -->
    
    
    <!-- fora do prazo -->
    <? if ($previsao_geral_passados_dias >= $prazo_encerramento_comunicado_dias){ ?>
    
    	<div style="color: red;">Fora do prazo</div>
        <?
		// comunicado
		mysql_select_db($database_conexao, $conexao);	
		$delete_SQL_comunicado = sprintf("
		DELETE FROM comunicado 
		WHERE IdComunicado=%s", 
		GetSQLValueString($row_comunicado['IdComunicado'], "int"));
		$Result_comunicado = mysql_query($delete_SQL_comunicado, $conexao) or die(mysql_error());
		// fim - comunicado
		
		// comunicado_destinatario
		mysql_select_db($database_conexao, $conexao);	
		$delete_SQL_comunicado_destinatario = sprintf("
		DELETE FROM comunicado_destinatario 
		WHERE IdComunicado=%s", 
		GetSQLValueString($row_comunicado['IdComunicado'], "int"));
		$Result_comunicado_destinatario = mysql_query($delete_SQL_comunicado_destinatario, $conexao) or die(mysql_error());
		// fim - comunicado_destinatario
		
		// comunicado_historico
		mysql_select_db($database_conexao, $conexao);	
		$delete_SQL_comunicado_historico = sprintf("
		DELETE FROM comunicado_historico 
		WHERE IdComunicado=%s", 
		GetSQLValueString($row_comunicado['IdComunicado'], "int"));
		$Result_comunicado_historico = mysql_query($delete_SQL_comunicado_historico, $conexao) or die(mysql_error());
		// fim - comunicado_historico

		// comunicado_anexo (arquivos)
		mysql_select_db($database_conexao, $conexao);
		$query_comunicado_anexo = sprintf("
		SELECT comunicado_anexo.*  
		FROM comunicado_anexo 
		WHERE comunicado_anexo.IdComunicado = %s 
		ORDER BY comunicado_anexo.IdComunicadoAnexo DESC", 
		GetSQLValueString($row_comunicado['IdComunicado'], "int"));
		$comunicado_anexo = mysql_query($query_comunicado_anexo, $conexao) or die(mysql_error());
		$row_comunicado_anexo = mysql_fetch_assoc($comunicado_anexo);
		$totalRows_comunicado_anexo = mysql_num_rows($comunicado_anexo);
		
		if($totalRows_comunicado_anexo > 0){ 
			do {
				$arquivo_atual = "arquivos/comunicado/".$row_comunicado_anexo['arquivo'];
				if(file_exists($arquivo_atual)){
					unlink($arquivo_atual);
				}
			} while ($row_comunicado_anexo = mysql_fetch_assoc($comunicado_anexo));
		}
		mysql_free_result($comunicado_anexo);
		// fim - comunicado_anexo (arquivos)
		
		// comunicado_anexo
		mysql_select_db($database_conexao, $conexao);	
		$delete_SQL_comunicado_anexo = sprintf("
		DELETE FROM comunicado_anexo 
		WHERE IdComunicado=%s", 
		GetSQLValueString($row_comunicado['IdComunicado'], "int"));
		$Result_comunicado_anexo = mysql_query($delete_SQL_comunicado_anexo, $conexao) or die(mysql_error());
		// fim - comunicado_anexo
		?>
        
    <? } ?>
    <!-- fim - fora do prazo -->
    
</div>        
<?php } while ($row_comunicado = mysql_fetch_assoc($comunicado)); ?>
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
mysql_free_result($comunicado);
mysql_free_result($parametros);
?>