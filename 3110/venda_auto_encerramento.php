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

require_once('venda_funcao_update.php');
require_once('emails.php');
require_once('funcao_dia_util.php');

// parametros
mysql_select_db($database_conexao, $conexao);
$query_parametros = "SELECT * FROM parametros";
$parametros = mysql_query($query_parametros, $conexao) or die(mysql_error());
$row_parametros = mysql_fetch_assoc($parametros);
$totalRows_parametros = mysql_num_rows($parametros);
// fim - parametros

// venda
mysql_select_db($database_conexao, $conexao);
$query_venda = sprintf("
SELECT 
venda.id, venda.status, venda.situacao, venda.data_venda, venda.id_usuario_responsavel, 
venda.empresa, venda.quantidade_agendado_implantacao, venda.quantidade_agendado_implantacao, venda.status_flag, 
venda.id_prospeccao, venda.contrato, venda.praca, venda.data_inicio, venda.data_fim, venda.observacao, venda.dilacao_prazo, 
(SELECT nome FROM usuarios WHERE venda.id_usuario_responsavel = usuarios.IdUsuario) AS usuario_responsavel 

FROM venda 
WHERE venda.status_flag = 'a' 
ORDER BY venda.id ASC");
$venda = mysql_query($query_venda, $conexao) or die(mysql_error());
$row_venda = mysql_fetch_assoc($venda);
$totalRows_venda = mysql_num_rows($venda);
// fim - venda

$venda_prazo_encerramento_mensagem = $row_parametros['venda_prazo_encerramento_mensagem'];
$venda_validade_dias = $row_parametros['venda_validade_dias'];
$venda_validade_dias_segundos = $venda_validade_dias * 86400;
$data_atual_segundos = strtotime("now");
?>
<html>

<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title></title>
</head>

<body style="font-family:Verdana, Geneva, sans-serif; font-size: 12px; margin: 0px;">
	<? if ($totalRows_venda > 0 and $venda_validade_dias > "0") { ?>
		<?php do { ?>
			<div style="border: 1px solid #CCC; margin: 5px; padding: 5px;">

				<!-- dados -->
				<? $data_venda_segundos = strtotime($row_venda['data_venda']); ?>
				<? $dilacao_prazo_segundos = $row_venda['dilacao_prazo'] * 86400; ?>

				<? $validade = date('Y-m-d 23:59:59', $data_venda_segundos + $venda_validade_dias_segundos + $dilacao_prazo_segundos); ?>
				<? $validade_segundos = strtotime($validade); ?>

				<? $data_venda_passados_dias = (($data_atual_segundos - $validade_segundos) + $venda_validade_dias_segundos) / 86400; ?>

				Número da venda: <?php echo $id = $row_venda['id'];; ?>
				<br>
				Data da venda: <? echo date('d-m-Y  H:i:s', $data_venda_segundos); ?>
				<br>
				Validade: <? echo date('d-m-Y  H:i:s', $validade_segundos); ?>
				<br>
				Dias passados: <? echo $data_venda_passados_dias; ?>
				<br>
				<!-- fim - dados -->


				<!-- dentro do prazo -->
				<? if ($data_venda_passados_dias < $venda_validade_dias) { ?>

					<!-- faltam XX ou menos dias -->
					<? if ($venda_validade_dias > 0 and $data_venda_passados_dias >= $venda_validade_dias - $venda_prazo_encerramento_mensagem) { ?>

						<div style="color: green;">Dentro do prazo - faltam <? echo $venda_prazo_encerramento_mensagem; ?> ou menos dias</div>

						<?
						// atualiza solicitação
						$dados_venda = array(
							"status_flag" => "a"
						);
						$dados_venda_descricao = array(
							"id_venda" => $id,
							"id_usuario_responsavel" => "",
							"descricao" => $row_parametros['venda_encerramento_msg_dentro_prazo'],
							"data" => date("Y-m-d H:i:s"),
							"tipo_postagem" => "Aviso de encerramento automático próximo"
						);
						funcao_venda_update($id, $dados_venda, $dados_venda_descricao);
						// fim - atualiza solicitação

						// função que envia e-mail
						email_venda($id, $dados_venda_descricao['tipo_postagem'], $dados_venda_descricao['descricao']);
						// fim - função que envia e-mail
						?>

					<? } ?>
					<!-- fim - faltam XX ou menos dias -->

				<? } ?>
				<!-- fim - dentro do prazo -->


				<!-- fora do prazo -->
				<? if ($data_venda_passados_dias >= $venda_validade_dias) { ?>

					<div style="color: red;">Fora do prazo</div>

					<?
					// atualiza solicitação
					$dados_venda = array(
						"situacao" => "solucionada",
						"status" => "",
						"status_flag" => "f",

						"acao" => "",

						"previsao_geral_inicio" => "",
						"previsao_geral" => "",

						"encaminhamento_id" => "",
						"encaminhamento_data_inicio" => "",
						"encaminhamento_data" => "",

						"status_devolucao" => "",
						"status_recusa" => "",

						"data_fim" => date('Y-m-d H:i:s'),

						"encerramento_automatico" => 1,
						"encerramento_automatico_data" => date('Y-m-d H:i:s'),

						"final_situacao" => $row_venda['situacao'],
						"final_status" => $row_venda['status']
					);
					$dados_venda_descricao = array(
						"id_venda" => $id,
						"id_usuario_responsavel" => "",
						"descricao" => $row_parametros['venda_encerramento_msg_fora_prazo'],
						"data" => date("Y-m-d H:i:s"),
						"tipo_postagem" => "Encerramento automático da venda"
					);
					funcao_venda_update($id, $dados_venda, $dados_venda_descricao);
					// fim - atualiza solicitação

					// update 'agenda'
					$updateSQL_venda_agenda = sprintf(
					"
					UPDATE agenda 
					SET status=%s 
					WHERE (id_venda_treinamento=%s or id_venda_implantacao=%s) and status='a'",
					GetSQLValueString("f", "text"),

					GetSQLValueString($row_venda['id'], "int"),
					GetSQLValueString($row_venda['id'], "int")
					);

					mysql_select_db($database_conexao, $conexao);
					$Result_venda_agenda = mysql_query($updateSQL_venda_agenda, $conexao) or die(mysql_error());
					// fim - update 'agenda'

					// função que envia e-mail
					email_venda($id, $dados_venda_descricao['tipo_postagem'], $dados_venda_descricao['descricao']);
					// fim - função que envia e-mail

					?>

				<? } ?>
				<!-- fim - fora do prazo -->

			</div>
		<?php } while ($row_venda = mysql_fetch_assoc($venda)); ?>
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
mysql_free_result($venda);
mysql_free_result($parametros);
?>