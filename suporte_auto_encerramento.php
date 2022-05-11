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
require_once('funcao_dia_util.php');

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
							 SELECT previsao_geral, id, situacao, status, parecer, solicita_suporte, solicita_visita 
							 FROM suporte 
							 WHERE 
							 (tipo_suporte = 'c' or tipo_suporte = 'p') and 
							 situacao = 'em validação' and 
							 status='pendente usuario envolvido'
							 
							 ORDER BY previsao_geral ASC");
$suporte = mysql_query($query_suporte, $conexao) or die(mysql_error());
$row_suporte = mysql_fetch_assoc($suporte);
$totalRows_suporte = mysql_num_rows($suporte);
// fim - suporte

$prazo_encerramento_suporte_dias = $row_parametros['prazo_encerramento_suporte'];
$prazo_encerramento_suporte_segundos = $prazo_encerramento_suporte_dias * 86400;
$data_atual_segundos = strtotime("now");
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title></title>
</head>

<body style="font-family:Verdana, Geneva, sans-serif; font-size: 12px; margin: 0px;">
<? if($totalRows_suporte > 0 and $prazo_encerramento_suporte_dias > "0"){ ?>   
<?php do { ?>
<div style="border: 1px solid #CCC; margin: 5px; padding: 5px;">

	<!-- dados -->
	<? $previsao_geral_segundos = strtotime($row_suporte['previsao_geral']); ?>
   
    <? $previsao_limite = proximoDiaUtil(date('Y-m-d H:i:s', $previsao_geral_segundos + $prazo_encerramento_suporte_segundos)); ?>
    <? $previsao_limite_segundos = strtotime($previsao_limite); ?>
    
	<? $previsao_geral_passados_dias = (($data_atual_segundos - $previsao_limite_segundos) + $prazo_encerramento_suporte_segundos) / 86400; ?>

    Número do suporte: <?php echo $id = $row_suporte['id'];; ?>
	<br>
	Previsão geral: <? echo date('d-m-Y  H:i:s', $previsao_geral_segundos); ?>
	<br>
    Previsão limite: <? echo date('d-m-Y  H:i:s', $previsao_limite_segundos); ?>
    <br>
	Dias passados: <? echo $previsao_geral_passados_dias; ?>
	<br>
	<!-- fim - dados -->
	
    
    <!-- dentro do prazo -->
    <? if ($previsao_geral_passados_dias < $prazo_encerramento_suporte_dias){ ?>

		<!-- faltam 2 ou menos dias -->
		<? if($prazo_encerramento_suporte_dias > 0 and $previsao_geral_passados_dias >= $prazo_encerramento_suporte_dias-2){ ?>
            
            <div style="color: green;">Dentro do prazo - faltam 2 ou menos dias</div>
            
			<?
			// atualiza solicitação
			$dados_suporte = array(
					"situacao" => "em validação"
			);	
			$dados_suporte_descricao = array(
					"id_suporte" => $id,
					"id_usuario_responsavel" => "",
					"descricao" => $row_parametros['encerramento_suporte_msg_dentro_prazo'],
					"data" => date("Y-m-d H:i:s"),
					"tipo_postagem" => "Aviso de validação automática próxima"
			);	
			funcao_suporte_update($id, $dados_suporte, $dados_suporte_descricao);
			// fim - atualiza solicitação
			
			// função que envia e-mail
			email_suporte($id, $dados_suporte_descricao['tipo_postagem'], $dados_suporte_descricao['descricao']);
			// fim - função que envia e-mail
			?>
            
        <? } ?>
        <!-- fim - faltam 2 ou menos dias -->
    
    <? } ?>
    <!-- fim - dentro do prazo -->
    
    
    <!-- fora do prazo -->
    <? if ($previsao_geral_passados_dias >= $prazo_encerramento_suporte_dias){ ?>
    
    	<div style="color: red;">Fora do prazo</div>
    	
        <?
		// atualiza solicitação
		$dados_suporte = array(
				"situacao" => "solucionada",
				"status" => "",
				"status_flag" => "f",

				"data_conclusao" => date("Y-m-d H:i:s"), 
				
				"acao" => "",
				
				"encaminhamento_id" => "",
				"encaminhamento_data_inicio" => "",
				"encaminhamento_data" => "",
				
				"status_devolucao" => "",
				"status_recusa" => "",
				
				"solicita_suporte" => "n",
				"solicita_visita" => "n",

				"previsao_geral_inicio" => "",
				"previsao_geral" => "",
				
				"data_fim" => date("Y-m-d H:i:s"),
				
				"encerramento_automatico" => 1,
				"encerramento_automatico_data" => date('Y-m-d H:i:s'),
				
				"final_situacao" => $row_suporte['situacao'],
				"final_status" => $row_suporte['status'],
				"final_parecer" => $row_suporte['parecer'],
				"final_solicita_suporte" => $row_suporte['solicita_suporte'],
				"final_solicita_visita" => $row_suporte['solicita_visita']
		);	
		$dados_suporte_descricao = array(
				"id_suporte" => $id,
				"id_usuario_responsavel" => "",
				"descricao" => $row_parametros['encerramento_suporte_msg_fora_prazo'],
				"data" => date("Y-m-d H:i:s"),
				"tipo_postagem" => "Validação automática do suporte"
		);	
		funcao_suporte_update($id, $dados_suporte, $dados_suporte_descricao);
		// fim - atualiza solicitação

		// função que envia e-mail
		email_suporte($id, $dados_suporte_descricao['tipo_postagem'], $dados_suporte_descricao['descricao']);
		// fim - função que envia e-mail
		
    	?>
        
    <? } ?>
    <!-- fim - fora do prazo -->
    
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