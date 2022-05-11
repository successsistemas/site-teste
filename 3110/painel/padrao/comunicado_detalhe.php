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

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
	$editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

require_once('../parametros.php');
require_once('../funcao.php');

$janela = NULL;
if (isset($_GET['janela'])) {
	$janela = $_GET['janela'];
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

// função que limita caracteres
function limita_caracteres($string, $tamanho, $encode = 'UTF-8')
{
	if (strlen($string) > $tamanho) {
		$string = mb_substr($string, 0, $tamanho, $encode);
	}
	return $string;
}
// fim - função que limita caracteres

// update (geral)
if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form")) {

	// upload
	$updateSQL = sprintf(
		"
	UPDATE comunicado_destinatario 
	SET lido = 1, lido_data=%s 
	WHERE IdUsuario=%s and IdComunicado=%s and IdComunicadoHistorico IS NULL",
		GetSQLValueString(date('Y-m-d H:i:s'), "date"),
		GetSQLValueString($row_usuario['IdUsuario'], "int"),
		GetSQLValueString($_POST["IdComunicado"], "int")
	);
	mysql_select_db($database_conexao, $conexao);
	$Result1 = mysql_query($updateSQL, $conexao) or die(mysql_error());
	// fim - upload

	// redireciona
	if ($janela == "index") {
		$updateGoTo = "../index.php";
		echo sprintf("<meta http-equiv=\"refresh\" content=\"0; url=%s\">", $updateGoTo);
		exit;
	} else {
		echo "<script type='text/javascript'>parent.eval('tb_remove()');</script>";
		exit;
	}
	// fim - redireciona

}
// fim - update (geral)

// comunicado
$colname_comunicado = "-1";
if (isset($_GET['IdComunicado'])) {
	$colname_comunicado = $_GET['IdComunicado'];
}
mysql_select_db($database_conexao, $conexao);
$query_comunicado = sprintf(
	"
SELECT 
	comunicado.IdComunicado, comunicado.IdUsuario, comunicado.data_criacao, comunicado.assunto, comunicado.texto, comunicado.data_limite, comunicado.data_reenvio, comunicado.tipo, 
	comunicado.prioridade, comunicado.prioridade_justificativa, 
	usuarios.nome AS remetente, 
	(
		SELECT 
			COUNT(comunicado_destinatario.IdComunicadoDestinatario) 
		FROM 
			comunicado_destinatario 
		WHERE 
			comunicado_destinatario.IdComunicado = comunicado.IdComunicado and 
			IdComunicadoHistorico IS NULL and 
			comunicado_destinatario.responsavel = 0
	) AS comunicado_destinatario_contador, 
	(
		SELECT 
			COUNT(comunicado_destinatario.IdComunicadoDestinatario) 
		FROM 
			comunicado_destinatario 
		WHERE 
			comunicado_destinatario.IdComunicado = comunicado.IdComunicado and 
			comunicado_destinatario.IdComunicadoHistorico IS NULL and 
			comunicado_destinatario.responsavel = 0 and 
			comunicado_destinatario.lido = 1 
	) AS comunicado_destinatario_lido_contador 
FROM 
	comunicado 
LEFT JOIN 
	usuarios ON usuarios.IdUsuario = comunicado.IdUsuario 
WHERE 
	comunicado.IdComunicado = %s and 
	EXISTS (
		SELECT 
			'x' 
		FROM 
			comunicado_destinatario 
		WHERE 
			comunicado_destinatario.IdUsuario = %s and 
			comunicado_destinatario.IdComunicado = %s
	) 
ORDER BY
	comunicado.IdComunicado ASC
",
GetSQLValueString($colname_comunicado, "int"),
GetSQLValueString($row_usuario['IdUsuario'], "int"),
GetSQLValueString($colname_comunicado, "int"));
$comunicado = mysql_query($query_comunicado, $conexao) or die(mysql_error());
$row_comunicado = mysql_fetch_assoc($comunicado);
$totalRows_comunicado = mysql_num_rows($comunicado);
if ($totalRows_comunicado == 0) {
	header("Location: ../index.php");
	exit;
}
// fim - comunicado

// leu
$updateSQL_leu = sprintf(
	"
UPDATE comunicado_destinatario 
SET comunicado_destinatario.leu=%s 
WHERE 
comunicado_destinatario.IdComunicado=%s and 
comunicado_destinatario.IdUsuario=%s and 
IdComunicadoHistorico IS NULL
",
	GetSQLValueString(date("Y-m-d H:i:s"), "date"),

	GetSQLValueString($row_comunicado['IdComunicado'], "int"),
	GetSQLValueString($row_usuario['IdUsuario'], "int")
);

mysql_select_db($database_conexao, $conexao);
$Result_leu = mysql_query($updateSQL_leu, $conexao) or die(mysql_error());
// fim - leu

// destinatario_listar
mysql_select_db($database_conexao, $conexao);
$query_destinatario_listar = "
SELECT * 
FROM usuarios 
WHERE status = 1 
ORDER BY praca ASC, nome ASC 
";
$destinatario_listar = mysql_query($query_destinatario_listar, $conexao) or die(mysql_error());
$row_destinatario_listar = mysql_fetch_assoc($destinatario_listar);
$totalRows_destinatario_listar = mysql_num_rows($destinatario_listar);
// fim - destinatario_listar
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title></title>

	<link rel="stylesheet" href="../../css/suporte.css" type="text/css" />
	<link rel="stylesheet" href="../../css/suporte_imprimir.css" type="text/css" media="print" />
	<link rel="stylesheet" href="../../css/thickbox.css" type="text/css" media="screen" />

	<script type="text/javascript" src="../../js/jquery.js"></script>
	<script type="text/javascript" src="../../js/thickbox.js"></script>
	<script type="text/javascript" src="../../funcoes.js"></script>

	<!--[if !IE]> -->
	<style>
		body {
			overflow-y: scroll;
			/* se não é IE, então mostra a scroll vertical */
		}

		.destinatario_listar_nome {
			cursor: pointer;
		}

		.popup-overlay {
			visibility: hidden;
			position: absolute;
			background: #ffffff;
			border: 3px solid #666666;
			width: 50%;
			height: 50%;
			left: 25%;
			top: 25%;
		}

		.popup-overlay.active {
			visibility: visible;
			text-align: center;
		}

		.popup-content {
			visibility: hidden;
			padding: 5px;
		}

		.popup-content.active {
			visibility: visible;
		}
	</style>
	<!-- <![endif]-->

	<script type="text/javascript">
		function confirmaSubmit() {
			var agree = confirm("Deseja realmente remover este usuário?");
			if (agree)
				return true;
			else
				return false;
		}

		function confirmaSubmitPraca() {
			var agree = confirm("Deseja realmente remover TODOS usuários da PRAÇA?");
			if (agree)
				return true;
			else
				return false;
		}

		$(document).ready(function() {

			$(".texto a[href^='http'], .texto a[href^='https'], .texto_responder a[href^='http'], .texto_responder a[href^='https']").attr('target', '_blank');

			$(".open").on("click", function() {
				var IdUsuario_atual = $(this).attr('id');
				var nome_atual = $(this).html();
				$(".popup-content > h2").html(nome_atual);
				$(".popup-overlay, .popup-content").addClass("active");
			});

			$(".close, .popup-overlay").on("click", function() {
				$(".popup-overlay, .popup-content").removeClass("active");
			});

		});
	</script>
</head>

<div class="popup-overlay">
	<div class="popup-content">
		<div class="div_solicitacao_linhas" id="cabecalho_solicitacoes" style="cursor: pointer">
			<table cellspacing="0" cellpadding="0" width="100%">
				<tr>
					<td style="text-align:left">
						Usuário: xxx
					</td>

					<td style="text-align: right">
						<a href="#" class="close">Fechar [X]</a>
					</td>
				</tr>
			</table>
		</div>
	</div>
</div>

<body>

	<div class="div_solicitacao_linhas" id="cabecalho_solicitacoes" style="cursor: pointer">
		<table cellspacing="0" cellpadding="0" width="100%">
			<tr>
				<td style="text-align:left">
					Comunicado número: <?php echo $row_comunicado['IdComunicado']; ?> 
				</td>

				<td style="text-align: right">

					<? //if($janela == "index"){ echo "../padrao/index.php"; } else { echo "../comunicado/listar.php" } 
					?>
					<a onclick="parent.eval('tb_remove()')">Fechar [X]</a>
				</td>
			</tr>
		</table>
	</div>

	<? if (strtotime(date('Y-m-d 23:59:59')) > strtotime($row_comunicado['data_limite'])) { ?>
		<div class="div_solicitacao_linhas3">
			<table cellspacing="0" cellpadding="0">
				<tr>
					<td style="text-align: left; font-size: 14px; color: red;">
						Tempo de Resposta Expirado em: <? echo date('d-m-Y', strtotime($row_comunicado['data_limite'])); ?>
					</td>
				</tr>
			</table>
		</div>
	<? } ?>

	<div class="div_solicitacao_linhas3">
		<table width="100%" cellspacing="0" cellpadding="0">
			<tr>
				<td style="text-align: left; font-weight: bold; font-size: 14px;">
					<div>Título: <? echo $row_comunicado['assunto']; ?></div>
					<div style="font-size: 12px; font-weight: normal;">Remetente: <? echo $row_comunicado['remetente']; ?></div>
				</td>
				<td style="text-align: right" width="160">
					<div>
						<? echo date('d/m/Y H:i', strtotime($row_comunicado['data_criacao'])); ?><? if ($row_comunicado['data_reenvio'] <> NULL) {
																										echo "<br> Reenvio: ";
																										echo date('d/m/Y H:i', strtotime($row_comunicado['data_reenvio']));
																									} ?>
					</div>
					<div>Prioridade: <? echo $row_comunicado['prioridade']; ?></div>
				</td>
			</tr>
		</table>
	</div>

	<?php if ($row_comunicado['prioridade_justificativa'] <> NULL) { ?>
		<div class="div_solicitacao_linhas3">
			<table cellspacing="0" cellpadding="0" width="100%">
				<tr>
					<td><span class="label_solicitacao">Justificativa da Prioridade Alta: </span><?php echo $row_comunicado['prioridade_justificativa']; ?></td>
				</tr>
			</table>
		</div>
	<? } ?>

	<div style="margin-top: 10px; border: 1px solid #DDD; padding: 10px;">

		<div class="texto"><? echo $row_comunicado['texto']; ?></div>

		<hr style="border: 1px solid #DDD;" />
		<div style="font-weight: bold;">Histórico:</div>

		<!-- comunicado_historico_listar -->
		<?
		// comunicado_historico_listar
		mysql_select_db($database_conexao, $conexao);
		$query_comunicado_historico_listar = sprintf(
		"
		SELECT comunicado_historico.*, 
		usuarios.nome AS usuarios_nome 
		FROM comunicado_historico 
		LEFT JOIN usuarios ON comunicado_historico.IdUsuario = usuarios.IdUsuario 
		WHERE comunicado_historico.IdComunicado = %s 
		ORDER BY comunicado_historico.IdComunicadoHistorico ASC",
		GetSQLValueString($row_comunicado['IdComunicado'], "int")
		);
		$comunicado_historico_listar = mysql_query($query_comunicado_historico_listar, $conexao) or die(mysql_error());
		$row_comunicado_historico_listar = mysql_fetch_assoc($comunicado_historico_listar);
		$totalRows_comunicado_historico_listar = mysql_num_rows($comunicado_historico_listar);
		// fim - comunicado_historico_listar
		?>
		<? if ($totalRows_comunicado_historico_listar > 0) { ?>
			<?php do { ?>

				<div class="texto_responder">
					<hr style="border: 1px solid #DDD;" />
					<span style="font-weight: bold;"><? echo $row_comunicado_historico_listar['usuarios_nome']; ?>
						[<? echo date('d/m/Y H:i', strtotime($row_comunicado_historico_listar['data_criacao'])); ?>]:
					</span>
					<? echo $row_comunicado_historico_listar['texto']; ?>
				</div>

			<?php } while ($row_comunicado_historico_listar = mysql_fetch_assoc($comunicado_historico_listar)); ?>
		<? } ?>
		<? mysql_free_result($comunicado_historico_listar); ?>
		<!-- fim - comunicado_historico_listar -->

		<hr style="border: 1px solid #DDD;" />
		<div style="font-weight: bold;">Anexo(s):</div>

		<!-- comunicado_anexo_listar -->
		<?
		// comunicado_anexo_listar
		mysql_select_db($database_conexao, $conexao);
		$query_comunicado_anexo_listar = sprintf(
			"
    SELECT comunicado_anexo.*, 
    usuarios.nome AS usuarios_nome 
    FROM comunicado_anexo 
    LEFT JOIN usuarios ON comunicado_anexo.IdUsuario = usuarios.IdUsuario 
    WHERE comunicado_anexo.IdComunicado = %s 
    ORDER BY comunicado_anexo.IdComunicadoAnexo DESC",
			GetSQLValueString($row_comunicado['IdComunicado'], "int")
		);
		$comunicado_anexo_listar = mysql_query($query_comunicado_anexo_listar, $conexao) or die(mysql_error());
		$row_comunicado_anexo_listar = mysql_fetch_assoc($comunicado_anexo_listar);
		$totalRows_comunicado_anexo_listar = mysql_num_rows($comunicado_anexo_listar);
		// fim - comunicado_anexo_listar
		?>
		<? if ($totalRows_comunicado_anexo_listar > 0) { ?>
			<?php do { ?>

				<div>
					<a href="../../arquivos/comunicado/<?php echo $row_comunicado_anexo_listar['arquivo']; ?>" target="_blank">
						<span><? echo $row_comunicado_anexo_listar['usuarios_nome']; ?>
							[<? echo date('d/m/Y H:i', strtotime($row_comunicado_anexo_listar['data_criacao'])); ?>]:
						</span>
						<? echo $row_comunicado_anexo_listar['arquivo']; ?>
					</a>
				</div>

			<?php } while ($row_comunicado_anexo_listar = mysql_fetch_assoc($comunicado_anexo_listar)); ?>
		<? } ?>
		<? mysql_free_result($comunicado_anexo_listar); ?>
		<!-- fim - comunicado_anexo_listar -->

		<hr style="border: 1px solid #DDD;" />

		<div style="padding: 5px 0 0 0; margin-top: 10px;" id="botoes">
			<form id="form" name="form" method="POST" action="<?php echo $editFormAction; ?>" class="cmxform" <? if ($janela == "index") { ?>target="_top" <? } ?>>
				<table width="100%" border="0" cellspacing="0" cellpadding="0">
					<tr>

						<td valign="middle">
							<input type="hidden" name="MM_update" value="form" />
							<input type="hidden" id="IdComunicado" name="IdComunicado" value="<? echo $row_comunicado['IdComunicado']; ?>" />

							<input type="submit" name="Marcar como lido" value="Marcar como lido" class="botao_geral2" />

							<? if (strtotime($row_comunicado['data_limite']) >= strtotime(date('Y-m-d H:i:s'))) { ?>
								<a href="comunicado_responder.php?IdComunicado=<? echo $row_comunicado['IdComunicado']; ?><? if ($janela == "index") { ?>&janela=index<? } ?>" id="botao_geral2">Responder</a>

								<a href="comunicado_anexar.php?IdComunicado=<? echo $row_comunicado['IdComunicado']; ?><? if ($janela == "index") { ?>&janela=index<? } ?>" id="botao_geral2">Arquivos em anexo</a>
							<? } ?>

							<? if ($row_usuario['controle_comunicado'] == "Y") { ?>
								<a href="comunicado_reenviar.php?IdComunicado=<? echo $row_comunicado['IdComunicado']; ?><? if ($janela == "index") { ?>&janela=index<? } ?>" id="botao_geral2">Reenviar</a>
							<? } ?>
							<a href="#" id="botao_geral2" onclick="print()">Imprimir</a>

						</td>

					</tr>
				</table>
			</form>
			<hr style="border: 1px solid #DDD;" />
		</div>

		<div id="comunicado_destinatarios">

			<div style="margin-bottom: 5px;">Destinatário(s):<br></div>

			<fieldset style="border: 0; padding: 0;">
				<div style="clear: both;"></div>
				<? $praca_atual = NULL; ?>
				<? do { ?>
					<?
					// destinatario_consultar
					mysql_select_db($database_conexao, $conexao);
					$query_destinatario_consultar = sprintf("
					SELECT 
						COUNT(IdComunicadoDestinatario) as retorno, data_criacao, lido_data, responsavel, leu 
					FROM 
						comunicado_destinatario 
					WHERE 
						IdComunicado = %s and 
						IdComunicadoHistorico IS NULL and 
						IdUsuario = %s 
					",
					GetSQLValueString($row_comunicado['IdComunicado'], "int"),
					GetSQLValueString($row_destinatario_listar['IdUsuario'], "int"));
					$destinatario_consultar = mysql_query($query_destinatario_consultar, $conexao) or die(mysql_error());
					$row_destinatario_consultar = mysql_fetch_assoc($destinatario_consultar);
					$totalRows_destinatario_consultar = mysql_num_rows($destinatario_consultar);
					// fim - destinatario_consultar
					?>

					<!-- praca atual -->
					<? if ($praca_atual != $row_destinatario_listar['praca']) { ?>
						<div style="clear: both;"></div>
						<div style="padding: 5px; background-color: #DDD; font-weight: bold;">
							<table cellspacing="0" cellpadding="0" width="100%">
								<tr>
									<td style="text-align:left" width="50%">
										<? echo $row_destinatario_listar['praca']; ?>
									</td>

									<td style="text-align: right" width="50%">
										<?
										if (
											$row_destinatario_consultar['retorno'] > 0 and
											$row_comunicado['IdUsuario'] == $row_usuario['IdUsuario'] and 
											$row_destinatario_consultar['responsavel'] == 0 and 
											$row_comunicado['comunicado_destinatario_lido_contador'] == 0
										) {
										?>
											<a href="comunicado_detalhe_usuario_excluir.php?IdComunicado=<?php echo $row_comunicado['IdComunicado']; ?>&praca=<?php echo $row_destinatario_listar['praca']; ?>" onClick="return confirmaSubmitPraca()" target="_self">
												<span style="font-weight: bold; color: #FF0004;" title="Remover">X</span>
											</a>
										<? } ?>
									</td>
								</tr>
							</table>
						</div>
					<? } ?>
					<!-- fim - praca atual -->

					<div style="width: 300px; float: left; border: 0px solid #000; line-height: 1.2em; margin-bottom: 5px; min-height: 20px;">

						<input type="checkbox" id="destinatario" name="destinatario[]" value="<? echo $row_destinatario_listar['IdUsuario']; ?>" <? if ($row_destinatario_consultar['retorno'] > 0) { ?>checked="checked" <? } ?> disabled="disabled" title="<? echo $row_destinatario_listar['praca']; ?>">

						<!--<span class="destinatario_listar_nomex openx" id=""> -->

						<?
						if (
							$row_destinatario_consultar['retorno'] > 0 and 
							$row_comunicado['IdUsuario'] == $row_usuario['IdUsuario'] and 
							$row_destinatario_consultar['responsavel'] == 0 and 
							$row_destinatario_consultar['leu'] == NULL and 
							$row_comunicado['comunicado_destinatario_lido_contador'] == 0 
						) {
						?>
							<a href="comunicado_detalhe_usuario_excluir.php?IdComunicado=<?php echo $row_comunicado['IdComunicado']; ?>&IdUsuario=<?php echo $row_destinatario_listar['IdUsuario']; ?>" onClick="return confirmaSubmit()" target="_self">
								<span style="font-weight: bold; color: #FF0004;" title="Remover">X</span>
							</a>
						<? } ?>

						<? if ($row_destinatario_consultar['retorno'] > 0) { ?>
							<span title="<?
											if ($row_destinatario_consultar['data_criacao'] <> NULL) {
												echo "Enviado em: " . date('d-m-Y H:i:s', strtotime($row_destinatario_consultar['data_criacao']));
											}

											if ($row_destinatario_consultar['leu'] <> NULL) {
												echo " / Visualizado em: " . date('d-m-Y H:i:s', strtotime($row_destinatario_consultar['leu']));
											}
											?>">
								<? echo $row_destinatario_listar['nome']; ?>
							</span>
						<? } else { ?>
							<? echo $row_destinatario_listar['nome']; ?>
						<? } ?>

						<? if ($row_destinatario_consultar['lido_data'] <> NULL) { ?>
							<br>
							Lido em: <? echo date('d-m-Y H:i', strtotime($row_destinatario_consultar['lido_data'])); ?>
						<? } ?>
					</div>

					<? $praca_atual = $row_destinatario_listar['praca']; ?>
					<? mysql_free_result($destinatario_consultar); ?>
				<?php } while ($row_destinatario_listar = mysql_fetch_assoc($destinatario_listar)); ?>
			</fieldset>

		</div>

		<div style="clear: both;"></div>

	</div>

</body>

</html>
<?php
mysql_free_result($usuario);
mysql_free_result($comunicado);
?>