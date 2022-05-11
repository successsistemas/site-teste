<?
if ($_SERVER["HTTP_X_CRON_AUTH"] != "3d5a01b606de5e79d6bcdaac8db316a0") {
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

require_once('solicitacao_funcao_update.php');
require_once('emails.php');

// parametros
mysql_select_db($database_conexao, $conexao);
$query_parametros = "SELECT * FROM parametros";
$parametros = mysql_query($query_parametros, $conexao) or die(mysql_error());
$row_parametros = mysql_fetch_assoc($parametros);
$totalRows_parametros = mysql_num_rows($parametros);
// fim - parametros

// solicitacao
mysql_select_db($database_conexao, $conexao);
$query_solicitacao = sprintf("
							 SELECT previsao_geral, id 
							 FROM solicitacao 
							 WHERE 
							 situacao = 'em validação' and 
							 status='pendente solicitante' and
							 (status_questionamento IS NULL or status_questionamento = 'solicitante') 
							 
							 ORDER BY previsao_geral ASC");
$solicitacao = mysql_query($query_solicitacao, $conexao) or die(mysql_error());
$row_solicitacao = mysql_fetch_assoc($solicitacao);
$totalRows_solicitacao = mysql_num_rows($solicitacao);
// fim - solicitacao

$prazo_encerramento_solicitacao_dias = $row_parametros['prazo_encerramento_solicitacao'];
$prazo_encerramento_solicitacao_segundos = $prazo_encerramento_solicitacao_dias * 86400;
$data_atual_segundos = strtotime("now");
?>
<html>

<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title></title>
</head>

<body style="font-family:Verdana, Geneva, sans-serif; font-size: 12px; margin: 0px;">
	<? if ($totalRows_solicitacao > 0 and $prazo_encerramento_solicitacao_dias > "0") { ?>
		<?php do { ?>
			<div style="border: 1px solid #CCC; margin: 5px; padding: 5px;">

				<!-- dados -->
				<? $previsao_geral_segundos = strtotime($row_solicitacao['previsao_geral']); ?>
				<? $previsao_limite_segundos = $previsao_geral_segundos + $prazo_encerramento_solicitacao_segundos; ?>

				<? $previsao_geral_passados_dias = (($data_atual_segundos - $previsao_limite_segundos) + $prazo_encerramento_solicitacao_segundos) / 86400; ?>

				Número da solicitação: <?php echo $id = $row_solicitacao['id'];; ?>
				<br>
				Previsão geral: <? echo date('d-m-Y  H:i:s', $previsao_geral_segundos); ?>
				<br>
				Previsão limite: <? echo date('d-m-Y  H:i:s', $previsao_limite_segundos); ?>
				<br>
				Dias passados: <? echo $previsao_geral_passados_dias; ?>
				<br>
				<!-- fim - dados -->


				<!-- dentro do prazo -->
				<? if ($previsao_geral_passados_dias < $prazo_encerramento_solicitacao_dias) { ?>

					<!-- faltam 2 ou menos dias -->
					<? if ($prazo_encerramento_solicitacao_dias > 0 and $previsao_geral_passados_dias >= $prazo_encerramento_solicitacao_dias - 2) { ?>

						<div style="color: green;">Dentro do prazo - faltam 2 ou menos dias</div>

						<?
						// atualiza solicitação
						$dados_solicitacao = array(
							"situacao" => "em validação"
						);
						$dados_solicitacao_descricao = array(
							"id_solicitacao" => $id,
							"id_usuario_responsavel" => "",
							"descricao" => $row_parametros['encerramento_solicitacao_msg_dentro_prazo'],
							"data" => date("Y-m-d H:i:s"),
							"tipo_postagem" => "Aviso de validação automática próxima"
						);
						funcao_solicitacao_update($id, $dados_solicitacao, $dados_solicitacao_descricao);
						// fim - atualiza solicitação

						// função que envia e-mail
						email_solicitacao($id, $dados_solicitacao_descricao['tipo_postagem'], $dados_solicitacao_descricao['descricao']);
						// fim - função que envia e-mail
						?>

					<? } ?>
					<!-- fim - faltam 2 ou menos dias -->

				<? } ?>
				<!-- fim - dentro do prazo -->


				<!-- fora do prazo -->
				<? if ($previsao_geral_passados_dias >= $prazo_encerramento_solicitacao_dias) { ?>

					<div style="color: red;">Fora do prazo</div>

					<?
					// atualiza solicitação
					$dados_solicitacao = array(
						"situacao" => "solucionada",
						"status" => "",

						"previsao_geral_inicio" => "",
						"previsao_geral" => "",

						"dt_final" => date("Y-m-d H:i:s"),
						"observacao_final" => $row_parametros['encerramento_solicitacao_msg_fora_prazo'],

						"encerramento_automatico" => 1,
						"encerramento_automatico_data" => date('Y-m-d H:i:s'),

						"previsao_proposta_inicio" => "",
						"previsao_proposta" => "",
						"previsao_proposta_ja_alterada" => "",

						"id_encaminhamento" => "",
						"encaminhamento_data_inicio" => "",
						"encaminhamento_data" => "",

						"status_devolucao" => "",
						"status_questionamento" => "",
						"status_recusa" => ""
					);
					$dados_solicitacao_descricao = array(
						"id_solicitacao" => $id,
						"id_usuario_responsavel" => "",
						"descricao" => $row_parametros['encerramento_solicitacao_msg_fora_prazo'],
						"data" => date("Y-m-d H:i:s"),
						"tipo_postagem" => "Validação automática da solicitação"
					);
					funcao_solicitacao_update($id, $dados_solicitacao, $dados_solicitacao_descricao);
					// fim - atualiza solicitação

					// função que envia e-mail
					email_solicitacao($id, $dados_solicitacao_descricao['tipo_postagem'], $dados_solicitacao_descricao['descricao']);
					// fim - função que envia e-mail
					?>

				<? } ?>
				<!-- fim - fora do prazo -->

			</div>
		<?php } while ($row_solicitacao = mysql_fetch_assoc($solicitacao)); ?>
	<? } ?>

	<?
	// insert - auto *****************************************************************
	$insertSQL_auto = sprintf(
		"
INSERT INTO auto (titulo, data, ip) 
VALUES (%s, %s, %s)",
		GetSQLValueString(basename($_SERVER['PHP_SELF']), "text"),
		GetSQLValueString(date('Y-m-d H:i:s'), "date"),
		GetSQLValueString(basename($_SERVER["REMOTE_ADDR"]), "text")
	);
	mysql_select_db($database_conexao, $conexao);
	$Result_auto = mysql_query($insertSQL_auto, $conexao) or die(mysql_error());
	// fim - insert - auto ***********************************************************
	?>
</body>

</html>
<?php
mysql_free_result($solicitacao);
mysql_free_result($parametros);
?>