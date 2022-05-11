<? session_start(); ?>
<?php require_once('restrito.php'); ?>
<?php require_once('Connections/conexao.php'); ?>
<?php
if (!function_exists("GetSQLValueString")) {
	require_once('funcao_converte_caracter.php');

	function GetSQLValueString($theValue, $theType, $theDefinedValue = "", $theNotDefinedValue = "")
	{
		$theValue = get_magic_quotes_gpc() ? stripslashes($theValue) : $theValue;

		$theValue = function_exists("mysql_real_escape_string") ? mysql_real_escape_string($theValue) : mysql_escape_string($theValue);

		switch ($theType) {
			case "text":
				//$theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
				$theValue = ($theValue != "") ? "'" . funcao_converte_caracter($theValue) . "'" : "NULL";
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

require_once('parametros.php');
require_once('suporte_funcao_update.php');

// usuário logado via SESSION
$colname_usuario = "-1";
if (isset($_SESSION['MM_Username'])) {
	$colname_usuario = $_SESSION['MM_Username'];
}
mysql_select_db($database_conexao, $conexao);
$query_usuario = sprintf("SELECT * FROM usuarios WHERE usuario = %s", GetSQLValueString($colname_usuario, "text"));
$usuario = mysql_query($query_usuario, $conexao) or die(mysql_error());
$row_usuario = mysql_fetch_assoc($usuario);
$totalRows_usuario = mysql_num_rows($usuario);
// fim - usuário logado via SESSION

// puxa o codigo da empresa
$colname_codigo_empresa = "-1";
if (isset($_GET["codigo_empresa"])) {
	$colname_codigo_empresa = $_GET["codigo_empresa"];
}
// fim - puxa o codigo da empresa

// puxa o número do contrato/manutenção
$colname_contrato = "-1";
if (isset($_GET["contrato"])) {
	$colname_contrato = $_GET["contrato"];
}
// fim - puxa o número do contrato/manutenção

// empresa_dados
mysql_select_db($database_conexao, $conexao);
$query_empresa_dados = sprintf("SELECT * FROM da01 WHERE codigo1 = %s and da01.sr_deleted <> 'T'", GetSQLValueString($colname_codigo_empresa, "text"));
$empresa_dados = mysql_query($query_empresa_dados, $conexao) or die(mysql_error());
$row_empresa_dados = mysql_fetch_assoc($empresa_dados);
$totalRows_empresa_dados = mysql_num_rows($empresa_dados);
// fim - empresa_dados

// empresa_dados_comp
mysql_select_db($database_conexao, $conexao);
$query_empresa_dados_comp = sprintf("SELECT * FROM dc01 WHERE codigo1 = %s", GetSQLValueString($colname_codigo_empresa, "text"));
$empresa_dados_comp = mysql_query($query_empresa_dados_comp, $conexao) or die(mysql_error());
$row_empresa_dados_comp = mysql_fetch_assoc($empresa_dados_comp);
$totalRows_empresa_dados_comp = mysql_num_rows($empresa_dados_comp);
// fim - empresa_dados_comp

// manutenção
mysql_select_db($database_conexao, $conexao);
$query_manutencao_dados = sprintf("
SELECT * FROM da37 WHERE da37.cliente17 = %s AND da37.codigo17 = %s and da37.sr_deleted <> 'T'", GetSQLValueString($colname_codigo_empresa, "text"), GetSQLValueString($colname_contrato, "text"));
$manutencao_dados = mysql_query($query_manutencao_dados, $conexao) or die(mysql_error());
$row_manutencao_dados = mysql_fetch_assoc($manutencao_dados);
$totalRows_manutencao_dados = mysql_num_rows($manutencao_dados);
// fim - manutenção

// suporte formulário
mysql_select_db($database_conexao, $conexao);
$query_suporte_formulario = sprintf("
SELECT * 
FROM suporte_formulario 
WHERE IdFormulario = %s", GetSQLValueString($_GET['IdFormulario'], "int"));
$suporte_formulario = mysql_query($query_suporte_formulario, $conexao) or die(mysql_error());
$row_suporte_formulario = mysql_fetch_assoc($suporte_formulario);
$totalRows_suporte_formulario = mysql_num_rows($suporte_formulario);
// fim - suporte formulário

// suporte
mysql_select_db($database_conexao, $conexao);
$query_suporte = sprintf("
SELECT *,  
(SELECT nome FROM usuarios WHERE usuarios.IdUsuario = suporte.id_usuario_responsavel) as usuario_responsavel, 
(SELECT nome FROM usuarios WHERE usuarios.IdUsuario = suporte.id_usuario_envolvido) as usuario_envolvido 
FROM suporte 
WHERE id = %s", GetSQLValueString($row_suporte_formulario['id_suporte'], "int"));
$suporte = mysql_query($query_suporte, $conexao) or die(mysql_error());
$row_suporte = mysql_fetch_assoc($suporte);
$totalRows_suporte = mysql_num_rows($suporte);
// fim - suporte

// modcon
mysql_select_db($database_conexao, $conexao);
$query_modcon = sprintf(
	"
								  SELECT * FROM modcon 
								  WHERE modcon.contrato = %s AND modcon.codcli = %s",
	GetSQLValueString($colname_contrato, "text"),
	GetSQLValueString($colname_codigo_empresa, "text")
);
$modcon = mysql_query($query_modcon, $conexao) or die(mysql_error());
$row_modcon = mysql_fetch_assoc($modcon);
$totalRows_modcon = mysql_num_rows($modcon);
// fim - modcon

// solicitacao_ultimos (abertos)
mysql_select_db($database_conexao, $conexao);
$query_solicitacao_ultimos = sprintf(
	"
								 SELECT id 
								 FROM solicitacao 
								 WHERE 
								 solicitacao.contrato = %s and 
								 solicitacao.situacao <> 'solucionada' and solicitacao.situacao <> 'reprovada' and solicitacao.situacao <> 'cancelada'
								 
								 ORDER BY solicitacao.id ASC",
	GetSQLValueString($colname_contrato, "text")
);
$solicitacao_ultimos = mysql_query($query_solicitacao_ultimos, $conexao) or die(mysql_error());
$row_solicitacao_ultimos = mysql_fetch_assoc($solicitacao_ultimos);
$totalRows_solicitacao_ultimos = mysql_num_rows($solicitacao_ultimos);
// fim - solicitacao_ultimos (abertos)

// suporte_ultimos LIMIT 3
mysql_select_db($database_conexao, $conexao);
$query_suporte_ultimos = sprintf(
	"
								 SELECT suporte.id, suporte.data_suporte, suporte.titulo, suporte.inloco, suporte.situacao, suporte.anomalia, 
								 suporte_formulario.IdFormulario, suporte_formulario.tipo_formulario 
								 FROM suporte 
								 LEFT JOIN suporte_formulario ON suporte.id_formulario = suporte_formulario.IdFormulario
								 WHERE suporte.codigo_empresa = %s and suporte.tipo_suporte = 'c' and suporte.id <> %s 
								 ORDER BY suporte.id DESC LIMIT 3",
	GetSQLValueString($colname_codigo_empresa, "text"),
	GetSQLValueString($row_suporte['id'], "int")
);
$suporte_ultimos = mysql_query($query_suporte_ultimos, $conexao) or die(mysql_error());
$row_suporte_ultimos = mysql_fetch_assoc($suporte_ultimos);
$totalRows_suporte_ultimos = mysql_num_rows($suporte_ultimos);
// fim - suporte_ultimos LIMIT 3

// reclamacao
mysql_select_db($database_conexao, $conexao);
$query_reclamacao = sprintf("
SELECT * 
FROM suporte 
WHERE id = %s", GetSQLValueString($row_suporte['reclamacao_vinculo'], "int"));
$reclamacao = mysql_query($query_reclamacao, $conexao) or die(mysql_error());
$row_reclamacao = mysql_fetch_assoc($reclamacao);
$totalRows_reclamacao = mysql_num_rows($reclamacao);
// fim - reclamacao
?>
<html>

<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title></title>
	<style>
		@media screen and (-webkit-min-device-pixel-ratio:0) {
			html {
				margin-left: 1px;
				/* 1px margin on Google Chrome (conserta bug) */
			}
		}

		@page {
			size: landscape;
		}

		table.bordasimples {
			border-collapse: collapse;
			font-size: 11px;
		}

		table.bordasimples tr td {
			border: 1px solid #000;
			font-family: Verdana, Geneva, sans-serif;
			padding-left: 3px;
			padding-right: 3px;
			padding-top: 1px;
			padding-bottom: 1px;
			vertical-align: top;
			line-height: 1.1em;
		}

		.titulo_dados_atendimento {
			font-size: 10px;
		}

		.titulo_formulario {
			font-family: Verdana, Geneva, sans-serif;
			font-size: 11px;
			padding-bottom: 2px;
			line-height: 1.2em;
		}
	</style>
</head>

<body>

	<table width="100%" height="20" border="0" cellspacing="0" cellpadding="0">
		<tr>
			<td width="60%" class="titulo_formulario">
				<strong>

					<? if ($row_suporte_formulario['tipo_formulario'] == "Manutencao") { ?>
						FICHA DE ATENDIMENTO A CLIENTE OPTANTE POR MANUTENÇÃO
					<? } ?>

					<? if ($row_suporte_formulario['tipo_formulario'] == "Cobranca") { ?>
						SOLICITAÇÃO DE ATENDIMENTO EM SISTEMAS SUCCESS - AUXÍLIO COBRANÇA
					<? } ?>

					<? if ($row_suporte_formulario['tipo_formulario'] == "Extra") { ?>
						SOLICITAÇÃO DE ATENDIMENTO EM SISTEMAS SUCCESS - EXTRA
					<? } ?>

					<? if ($row_suporte_formulario['tipo_formulario'] == "Treinamento") { ?>
						SOLICITAÇÃO DE TREINAMENTO EM SISTEMAS SUCCESS
					<? } ?>

					<? if ($row_suporte_formulario['tipo_formulario'] == "Reclamacao") { ?>
						ATENDIMENTO PARA ANÁLISE DE RECLAMAÇÃO
					<? } ?>
				</strong>

				<? if ($row_suporte['visita_bonus'] == "s") { ?>
					- <span style="font-weight: bold; font-size: 14px;">VISITA CORTESIA</span>
				<? } ?>

				<br>
				Suporte nº <strong><? echo $row_suporte_formulario['id_suporte']; ?></strong> | Form. nº <strong><? echo $row_suporte_formulario['IdFormulario']; ?></strong> |
				Criado em: <strong><? echo date('d-m-Y  H:i:s', strtotime($row_suporte_formulario['data'])); ?></strong>
			</td>
			<td width="20%" class="titulo_formulario" align="left">


				Situação:
				<strong>
					<? if ($row_suporte_formulario['situacao'] == "autorizado") { ?>Autorizado<? } ?>
					<? if ($row_suporte_formulario['situacao'] == "bloqueado") { ?>Bloqueado<? } ?>
					<? if ($row_suporte_formulario['situacao'] == "cancelado") { ?>Cancelado<? } ?>
				</strong>
				<br>
				Representante: <strong><? echo $row_suporte['praca']; ?></strong>
			</td>
			<td width="20%" class="titulo_formulario" align="right">
				Página 1
			</td>
		</tr>
	</table>


	<table width="100%" border="0" cellspacing="0" cellpadding="0">
		<tr>

			<td width="50%" align="left" valign="top">

				<table width="100%" height="20" border="0" cellspacing="0" cellpadding="0" class="bordasimples">
					<tr>
						<td align="center" bgcolor="#F1F1F1" style="font-weight:bold">Dados do Cliente/Empresa</td>
					</tr>
				</table>

				<table width="100%" height="30" border="0" cellspacing="0" cellpadding="0" class="bordasimples">
					<tr>
						<td>
							Razão social:
							<div><?php echo utf8_encode($row_empresa_dados['nome1']); ?></div>
						</td>
						<td width="25%">
							Contrato:
							<div><?php echo $row_manutencao_dados['codigo17']; ?></div>
						</td>
					</tr>
				</table>

				<table width="100%" height="30" border="0" cellspacing="0" cellpadding="0" class="bordasimples">
					<tr>
						<td width="60%">
							Nome fantasia:
							<div><?php echo utf8_encode($row_empresa_dados['fantasia1']); ?></div>
						</td>

						<td>
							Responsável:
							<div><?php echo $row_empresa_dados['contato1']; ?></div>
						</td>
					</tr>
				</table>

				<table width="100%" height="45" border="0" cellspacing="0" cellpadding="0" class="bordasimples">
					<tr>
						<td>
							Endereço:
							<div><?php echo utf8_encode($row_empresa_dados['endereco1']); ?></div>
						</td>
						<td width="25%">
							Telefone:
							<div><?php echo $row_empresa_dados['telefone1']; ?></div>
						</td>
					</tr>
				</table>

				<table width="100%" height="30" border="0" cellspacing="0" cellpadding="0" class="bordasimples">
					<tr>
						<td>
							Bairro:
							<div><?php echo $row_empresa_dados['bairro1']; ?></div>
						</td>
						<td>
							Cidade:
							<div><?php echo $row_empresa_dados['cidade1']; ?></div>
						</td>
						<td>
							Estado:
							<div><?php echo $row_empresa_dados['uf1']; ?></div>
						</td>
					</tr>
				</table>

				<table width="100%" height="30" border="0" cellspacing="0" cellpadding="0" class="bordasimples">
					<tr>
						<td>
							CEP:
							<div><?php echo $row_empresa_dados['cep1']; ?></div>
						</td>
						<td>
							E-mail:
							<div><?php echo $row_empresa_dados_comp['email1']; ?></div>
						</td>
					</tr>
				</table>

				<table width="100%" height="30" border="0" cellspacing="0" cellpadding="0" class="bordasimples">
					<tr>
						<td width="50%">
							CPF / CNPJ:
							<div><?php echo shellDescriptografa($row_empresa_dados['cgc1']); ?></div>
						</td>
						<td>
							ID / INSC. EST.:
							<div><?php echo shellDescriptografa($row_empresa_dados['insc1']); ?></div>
						</td>
					</tr>
				</table>

			</td>

			<td width="50%" align="right" valign="top">

				<table width="100%" height="20" border="0" cellspacing="0" cellpadding="0" class="bordasimples">
					<tr>
						<td align="center" bgcolor="#F1F1F1" style="font-weight:bold">Dados técnicos</td>
					</tr>
				</table>

				<table width="100%" height="30" border="0" cellspacing="0" cellpadding="0" class="bordasimples">
					<tr>
						<td width="25%">
							Créditos:
							<div><? echo funcao_suporte_credito($row_suporte['contrato']); ?></div>
						</td>

						<td width="25%">
							Visita bônus:
							<div><? if ($row_suporte_formulario['visita_bonus'] == "s") {
										echo "Sim";
									} else {
										echo "Não";
									}; ?></div>

						</td>

						<td width="25%">
							<div>(<?php if ($row_manutencao_dados['espmod17'] == "O") {
										echo "X";
									} else {
										echo " ";
									} ?>) Oficce</div>
							<div>(<?php if ($row_manutencao_dados['espmod17'] == "B") {
										echo "X";
									} else {
										echo " ";
									} ?>) Standard</div>
						</td>

						<td width="25%">
							<div>(<?php if ($row_manutencao_dados['versao17'] == "1") {
										echo "X";
									} else {
										echo " ";
									} ?>) DOS</div>
							<div>(<?php if ($row_manutencao_dados['versao17'] == "2") {
										echo "X";
									} else {
										echo " ";
									} ?>) Windows</div>
						</td>

					</tr>
				</table>

				<table width="100%" height="30" border="0" cellspacing="0" cellpadding="0" class="bordasimples">
					<tr>
						<td>
							Módulos:
							<div>
								(<? if ($row_modcon['modest'] != NULL) {
										echo "X";
									} else {
										echo " ";
									} ?>) Estoque
								(<? if ($row_modcon['modfin'] != NULL) {
										echo "X";
									} else {
										echo " ";
									} ?>) Financeiro
								(<? if ($row_modcon['modser'] != NULL) {
										echo "X";
									} else {
										echo " ";
									} ?>) Serviço
								(<? if ($row_modcon['modoti'] != NULL) {
										echo "X";
									} else {
										echo " ";
									} ?>) Ótica
								(<? if ($row_modcon['modpdv'] != NULL) {
										echo "X";
									} else {
										echo " ";
									} ?>) PDV
								(<? if ($row_modcon['modpve'] != NULL) {
										echo "X";
									} else {
										echo " ";
									} ?>) PVE
								(<? if ($row_modcon['modben'] != NULL) {
										echo "X";
									} else {
										echo " ";
									} ?>) Bens
							</div>
						</td>
					</tr>
				</table>

				<table width="100%" border="0" height="45" cellspacing="0" cellpadding="0" class="bordasimples">
					<tr>
						<td>
							Ferramentas adicionais:
							<br>
							(<? if ($row_modcon['ferlot'] != NULL) {
									echo "X";
								} else {
									echo " ";
								} ?>) Lote bancário
							(<? if ($row_modcon['fernfe'] != NULL) {
									echo "X";
								} else {
									echo " ";
								} ?>) NFE
							(<? if ($row_modcon['ferefd'] != NULL) {
									echo "X";
								} else {
									echo " ";
								} ?>) EFD
							(<? if ($row_modcon['ferrelcon'] != NULL) {
									echo "X";
								} else {
									echo " ";
								} ?>) Relatórios Consultoria
							(<? if ($row_modcon['fermes'] != NULL) {
									echo "X";
								} else {
									echo " ";
								} ?>) Controle Mesa
							<br>
							(<? if ($row_modcon['ferbin'] != NULL) {
									echo "X";
								} else {
									echo " ";
								} ?>) Bina
							(<? if ($row_modcon['ferfid'] != NULL) {
									echo "X";
								} else {
									echo " ";
								} ?>) Cartão Fidelidade

							(<? if ($row_modcon['fertdi'] != NULL) {
									echo "X";
								} else {
									echo " ";
								} ?>) Tef Discado
							(<? if ($row_modcon['fertdd'] != NULL) {
									echo "X";
								} else {
									echo " ";
								} ?>) Tef Dedicado
							(<? if ($row_modcon['fertpy'] != NULL) {
									echo "X";
								} else {
									echo " ";
								} ?>) Tef Pay&Go
						</td>
					</tr>
				</table>

				<table width="100%" height="30" border="0" cellspacing="0" cellpadding="0" class="bordasimples">
					<tr>
						<td width="50%">
							Qtde de Computadores: <strong><? echo $row_modcon['qtdter'] - 0; ?></strong>
							<br>
							Qtde de Terminais: <strong><? echo $row_modcon['qtdter'] - 1; ?></strong>
						</td>

						<td>
							Qtde PDV/ECF: <strong><? echo $row_modcon['qtdpdv'] - 0; ?></strong>
							<br>
							Qtde PVE/PALM: <strong><? echo $row_modcon['qtdpve'] - 0; ?></strong>
						</td>
					</tr>
				</table>

				<table width="100%" height="30" border="0" cellspacing="0" cellpadding="0" class="bordasimples">
					<tr>
						<td>
							Solicitação Pendente: (n° Protocolo):
							<br>
							<? do { ?>

								<strong><? echo $row_solicitacao_ultimos['id']; ?></strong>
								&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
							<?php } while ($row_solicitacao_ultimos = mysql_fetch_assoc($solicitacao_ultimos)); ?>
						</td>
					</tr>
				</table>


				<table width="100%" height="30" border="0" cellspacing="0" cellpadding="0" class="bordasimples">
					<tr>
						<td>
							Agendamento:
							<br>
							<? echo date('d-m-Y  H:i', strtotime($row_suporte['data_inicio'])); ?> à <? echo date('d-m-Y  H:i', strtotime($row_suporte['data_fim'])); ?>
						</td>

						<td>
							Solicitante:
							<br>
							<? echo $row_suporte['solicitante']; ?>
						</td>

						<td>
							Técnico:
							<br>
							<? echo $row_suporte['usuario_responsavel']; ?>
						</td>
					</tr>
				</table>

			</td>

		</tr>
	</table>

	<? if ($row_reclamacao['reclamacao_solicitacao']) { ?>

		<table width="100%" border="0" cellspacing="0" cellpadding="0" class="bordasimples">
			<tr>
				<td align="left" height="18">
					<!-- reclamacao_solicitacao -->
					<?
					// reclamacao_solicitacao
					mysql_select_db($database_conexao, $conexao);
					$query_reclamacao_solicitacao = sprintf("
        SELECT titulo 
        FROM solicitacao 
        WHERE id = %s", GetSQLValueString($row_reclamacao['reclamacao_solicitacao'], "int"));
					$reclamacao_solicitacao = mysql_query($query_reclamacao_solicitacao, $conexao) or die(mysql_error());
					$row_reclamacao_solicitacao = mysql_fetch_assoc($reclamacao_solicitacao);
					$totalRows_reclamacao_solicitacao = mysql_num_rows($reclamacao_solicitacao);
					// fim - reclamacao_solicitacao
					?>

					Origem da reclamação: Solicitação nº <? echo $row_reclamacao['reclamacao_solicitacao']; ?> - <? echo $row_reclamacao_solicitacao['titulo']; ?>

					<? mysql_free_result($reclamacao_solicitacao); ?>
					<!-- fim - reclamacao_solicitacao -->
				</td>
			</tr>
		</table>

	<? } else if ($row_reclamacao['reclamacao_suporte']) { ?>

		<table width="100%" border="0" cellspacing="0" cellpadding="0" class="bordasimples">
			<tr>
				<td align="left" height="18">
					<!-- reclamacao_suporte -->
					<?
					// reclamacao_suporte
					mysql_select_db($database_conexao, $conexao);
					$query_reclamacao_suporte = sprintf("
        SELECT titulo 
        FROM suporte 
        WHERE id = %s", GetSQLValueString($row_reclamacao['reclamacao_suporte'], "int"));
					$reclamacao_suporte = mysql_query($query_reclamacao_suporte, $conexao) or die(mysql_error());
					$row_reclamacao_suporte = mysql_fetch_assoc($reclamacao_suporte);
					$totalRows_reclamacao_suporte = mysql_num_rows($reclamacao_suporte);
					// fim - reclamacao_suporte
					?>

					Origem da reclamação: Suporte nº <? echo $row_reclamacao['reclamacao_suporte']; ?> - <? echo $row_reclamacao_suporte['titulo']; ?>

					<? mysql_free_result($reclamacao_suporte); ?>
					<!-- fim - reclamacao_suporte -->
				</td>
			</tr>
		</table>

	<? } else if ($row_reclamacao['reclamacao_prospeccao']) { ?>

		<table width="100%" border="0" cellspacing="0" cellpadding="0" class="bordasimples">
			<tr>
				<td align="left" height="18">
					Origem da reclamação: Prospecção nº <? echo $row_reclamacao['reclamacao_prospeccao']; ?>
				</td>
			</tr>
		</table>

	<? } else if ($row_reclamacao['reclamacao_venda']) { ?>

		<table width="100%" border="0" cellspacing="0" cellpadding="0" class="bordasimples">
			<tr>
				<td align="left" height="18">
					Origem da reclamação: Venda nº <? echo $row_reclamacao['reclamacao_venda']; ?>
				</td>
			</tr>
		</table>

	<? } ?>

	<? if ($row_suporte_formulario['tipo_formulario'] == "Reclamacao" and $totalRows_reclamacao > 0) { ?>
		<table width="100%" border="0" cellspacing="0" cellpadding="0" class="bordasimples">
			<tr>
				<td align="left" valign="middle" width="70%" height="18">Envolvido(s) na reclamação: <? echo $row_suporte['envolvido_reclamacao']; ?></td>
				<td align="left" valign="middle" width="30%" height="18">Reclamante: <? echo $row_reclamacao['reclamacao_responsavel']; ?></td>
			</tr>
		</table>

		<table cellspacing=0 cellpadding=0 width="100%" class="bordasimples">
			<tr>
				<td align="left" valign="top" class="titulo_dados_atendimento"><strong>Reclamação <? echo $row_reclamacao['id']; ?></strong></td>
			</tr>
			<tr>
				<td height="70" align="left" valign="top"><? echo $row_reclamacao['reclamacao']; ?></td>
			</tr>
		</table>

		<table cellspacing=0 cellpadding=0 width="100%" class="bordasimples">
			<tr>
				<td align="left" valign="top" class="titulo_dados_atendimento"><strong>Anomalia</strong></td>
			</tr>
			<tr>
				<td height="35" align="left" valign="top"><? echo $row_suporte['anomalia']; ?></td>
			</tr>
			<tr>
				<td align="left" valign="middle"><strong>Diagnóstico</strong>:</td>
			</tr>
			<tr>
				<td align="center" valign="top">&nbsp;</td>
			</tr>
			<tr>
				<td align="center" valign="top">&nbsp;</td>
			</tr>
			<tr>
				<td align="center" valign="top">&nbsp;</td>
			</tr>
			<tr>
				<td align="center" valign="top">&nbsp;</td>
			</tr>
			<tr>
				<td align="center" valign="top">&nbsp;</td>
			</tr>
			<tr>
				<td align="center" valign="top">&nbsp;</td>
			</tr>
			<tr>
				<td align="center" valign="top">&nbsp;</td>
			</tr>
			<tr>
				<td align="center" valign="top">&nbsp;</td>
			</tr>
			<tr>
				<td align="center" valign="top">&nbsp;</td>
			</tr>
			<tr>
				<td align="center" valign="top">&nbsp;</td>
			</tr>
			<tr>
				<td align="center" valign="top">&nbsp;</td>
			</tr>
			<tr>
				<td align="center" valign="top">&nbsp;</td>
			</tr>
			<tr>
				<td align="center" valign="top">&nbsp;</td>
			</tr>
			<tr>
				<td align="center" valign="top">&nbsp;</td>
			</tr>
			<tr>
				<td align="center" valign="top">&nbsp;</td>
			</tr>
			<tr>
				<td align="center" valign="top">&nbsp;</td>
			</tr>
			<tr>
				<td align="center" valign="top">&nbsp;</td>
			</tr>
			<tr>
				<td align="center" valign="top">&nbsp;</td>
			</tr>
			<tr>
				<td align="center" valign="top">&nbsp;</td>
			</tr>
		</table>

		<div style="page-break-after: always"></div>
		<table cellspacing=0 cellpadding=0 width="100%">
			<tr>
				<td>&nbsp;</td>
			</tr>
		</table>

		<table width="100%" height="20" border="0" cellspacing="0" cellpadding="0">
			<tr>
				<td width="90%" class="titulo_formulario">
					<strong>

						<? if ($row_suporte_formulario['tipo_formulario'] == "Manutencao") { ?>
							FICHA DE ATENDIMENTO A CLIENTE OPTANTE POR MANUTENÇÃO
						<? } ?>

						<? if ($row_suporte_formulario['tipo_formulario'] == "Cobranca") { ?>
							SOLICITAÇÃO DE ATENDIMENTO EM SISTEMAS SUCCESS - AUXÍLIO COBRANÇA
						<? } ?>

						<? if ($row_suporte_formulario['tipo_formulario'] == "Extra") { ?>
							SOLICITAÇÃO DE ATENDIMENTO EM SISTEMAS SUCCESS - EXTRA
						<? } ?>

						<? if ($row_suporte_formulario['tipo_formulario'] == "Treinamento") { ?>
							SOLICITAÇÃO DE TREINAMENTO EM SISTEMAS SUCCESS
						<? } ?>

						<? if ($row_suporte_formulario['tipo_formulario'] == "Reclamacao") { ?>
							ATENDIMENTO PARA ANÁLISE DE RECLAMAÇÃO
						<? } ?>

					</strong>
					- Suporte nº <strong><? echo $row_suporte_formulario['id_suporte']; ?></strong> | Form. nº <strong><? echo $row_suporte_formulario['IdFormulario']; ?></strong> |
					Criado em: <strong><? echo date('d-m-Y  H:i:s', strtotime($row_suporte_formulario['data'])); ?></strong>
				</td>
				<td width="0%" class="titulo_formulario" align="right">
					Página 2
				</td>
			</tr>
		</table>

		<table cellspacing=0 cellpadding=0 width="100%" class="bordasimples">
			<tr>
				<td colspan="3" align="left" valign="middle"><strong>Parecer do atendente</strong>:</td>
			</tr>
			<tr>
				<td colspan="3" align="center" valign="top">&nbsp;</td>
			</tr>
			<tr>
				<td colspan="3" align="center" valign="top">&nbsp;</td>
			</tr>
			<tr>
				<td colspan="3" align="center" valign="top">&nbsp;</td>
			</tr>
			<tr>
				<td colspan="3" align="center" valign="top">&nbsp;</td>
			</tr>
			<tr>
				<td colspan="3" align="center" valign="top">&nbsp;</td>
			</tr>
			<tr>
				<td colspan="3" align="center" valign="top">&nbsp;</td>
			</tr>
			<tr>
				<td colspan="3" align="center" valign="top">&nbsp;</td>
			</tr>
			<tr>
				<td colspan="3" align="center" valign="top">&nbsp;</td>
			</tr>
			<tr>
				<td colspan="3" align="center" valign="top">&nbsp;</td>
			</tr>
			<tr>
				<td colspan="3" align="left" valign="middle"><strong>Parecer do cliente</strong>:</td>
			</tr>
			<tr>
				<td colspan="3" align="center" valign="top">&nbsp;</td>
			</tr>
			<tr>
				<td colspan="3" align="center" valign="top">&nbsp;</td>
			</tr>
			<tr>
				<td colspan="3" align="center" valign="top">&nbsp;</td>
			</tr>
			<tr>
				<td colspan="3" align="center" valign="top">&nbsp;</td>
			</tr>
			<tr>
				<td colspan="3" align="center" valign="top">&nbsp;</td>
			</tr>
			<tr>
				<td colspan="3" align="center" valign="top">&nbsp;</td>
			</tr>
			<tr>
				<td colspan="3" align="center" valign="top">&nbsp;</td>
			</tr>
			<tr>
				<td colspan="3" align="center" valign="top">&nbsp;</td>
			</tr>
			<tr>
				<td align="center" valign="top" width="60%"><strong>Situação</strong></td>
				<td align="center" valign="top" width="20%"><strong>Data acordada para retorno</strong></td>
				<td align="center" valign="top" width="20%"><strong>Assinatura do cliente</strong></td>
			</tr>
			<tr>
				<td align="center" valign="top" height="25">&nbsp;</td>
				<td align="center" valign="top">&nbsp;</td>
				<td align="center" valign="top">&nbsp;</td>
			</tr>
		</table>

		<table cellspacing=0 cellpadding=0 width="100%">
			<tr>
				<td height="10"></td>
			</tr>
		</table>

		<table width="100%" border="0" cellspacing="0" cellpadding="0" class="bordasimples">
			<tr>
				<td colspan="6" align="left"><strong>Últimos atendimentos</strong>:</td>
			</tr>
			<tr>
				<td class="titulo_dados_atendimento" style="text-align: left; font-size: 9px" width="40"><strong>Sup. nº</strong></td>
				<td class="titulo_dados_atendimento" style="text-align: left; font-size: 9px" width="40"><strong>In-loco</strong></td>
				<td class="titulo_dados_atendimento" style="text-align: left; font-size: 9px" width="70"><strong>Data</strong></td>
				<td class="titulo_dados_atendimento" style="text-align: left; font-size: 9px"><strong>Anomalia</strong></td>
				<td class="titulo_dados_atendimento" style="text-align: left; font-size: 9px" width="70"><strong>Tipo de Form.</strong></td>
				<td class="titulo_dados_atendimento" style="text-align: left; font-size: 9px" width="50"><strong>Situação</strong></td>
			</tr>
			<? if ($totalRows_suporte_ultimos > 0) { ?>
				<? do { ?>
					<tr>
						<td class="titulo_dados_atendimento" style="text-align: left; font-size: 9px"><? echo $row_suporte_ultimos['id']; ?></td>
						<td class="titulo_dados_atendimento" style="text-align: left; font-size: 9px"><?php if ($row_suporte_ultimos['inloco'] == "s") {
																											echo "Sim";
																										}
																										if ($row_suporte_ultimos['inloco'] == "n") {
																											echo "Não";
																										} ?></td>
						<td class="titulo_dados_atendimento" style="text-align: left; font-size: 9px"><? echo date('d-m-Y', strtotime($row_suporte_ultimos['data_suporte'])); ?></td>
						<td class="titulo_dados_atendimento" style="text-align: left; font-size: 9px"><?php $texto = $row_suporte_ultimos['anomalia'];
																										$texto_limite = 38;
																										if (strlen($texto) > $texto_limite) {
																											$texto = substr($texto, 0, $texto_limite) . '...';
																										}
																										echo $texto; ?></td>
						<td class="titulo_dados_atendimento" style="text-align: left; font-size: 9px"><? echo $row_suporte_ultimos['tipo_formulario']; ?></td>
						<td class="titulo_dados_atendimento" style="text-align: left; font-size: 9px"><? echo $row_suporte_ultimos['situacao']; ?></td>
					</tr>
				<?php } while ($row_suporte_ultimos = mysql_fetch_assoc($suporte_ultimos)); ?>
			<? } ?>
		</table>

		<table cellspacing=0 cellpadding=0 width="100%">
			<tr>
				<td height="10"></td>
			</tr>
		</table>

		<table cellspacing=0 cellpadding=0 width="100%" class="bordasimples">
			<tr>
				<td colspan="3" align="left" valign="middle"><strong>Contato pós atendimento</strong>:</td>
			</tr>
			<tr>
				<td width="20%" height="25" align="left" valign="top">Data:</td>
				<td width="30%" align="left" valign="top">Responsável:</td>
				<td width="50%" align="left" valign="top">Contato na empresa:</td>
			</tr>
			<tr>
				<td colspan="3" align="center" valign="top">&nbsp;</td>
			</tr>
			<tr>
				<td colspan="3" align="center" valign="top">&nbsp;</td>
			</tr>
			<tr>
				<td colspan="3" align="center" valign="top">&nbsp;</td>
			</tr>
			<tr>
				<td colspan="3" align="center" valign="top">&nbsp;</td>
			</tr>
			<tr>
				<td colspan="3" align="center" valign="top">&nbsp;</td>
			</tr>
			<tr>
				<td colspan="3" align="center" valign="top">&nbsp;</td>
			</tr>
			<tr>
				<td colspan="3" align="center" valign="top">&nbsp;</td>
			</tr>
			<tr>
				<td colspan="3" align="center" valign="top">&nbsp;</td>
			</tr>
			<tr>
				<td colspan="3" align="left" valign="middle"><strong>Parecer</strong>:</td>
			</tr>
			<tr>
				<td colspan="3" align="center" valign="top">&nbsp;</td>
			</tr>
			<tr>
				<td colspan="3" align="center" valign="top">&nbsp;</td>
			</tr>
			<tr>
				<td colspan="3" align="center" valign="top">&nbsp;</td>
			</tr>
		</table>

		<table cellspacing=0 cellpadding=0 width="100%" class="bordasimples">
			<tr>
				<td align="center" valign="top" width="80%"><strong>Situação</strong></td>
				<td width="80%" align="center" valign="top"><strong>Assinatura do responsável</strong></td>
			</tr>
			<tr>
				<td align="center" valign="top" width="40%" height="25">&nbsp;</td>
				<td align="center" valign="top">&nbsp;</td>
			</tr>
		</table>

	<? } else { ?>

		<table cellspacing=0 cellpadding=0 width="100%" class="bordasimples">
			<tr>
				<td width="10%" align="center" valign="top" class="titulo_dados_atendimento"><strong>Data</strong></td>
				<td width="40%" align="center" valign="top" class="titulo_dados_atendimento"><strong>Anomalia</strong></td>
				<td width="7%" align="center" valign="top" class="titulo_dados_atendimento"><strong>Hora Inicial</strong></td>
				<td width="7%" align="center" valign="top" class="titulo_dados_atendimento"><strong>Hora Final</strong></td>
				<td width="7%" align="center" valign="top" class="titulo_dados_atendimento"><strong>Duração</strong></td>
				<td width="7%" align="center" valign="top" class="titulo_dados_atendimento"><strong>Versão</strong></td>
				<td width="8%" align="center" valign="top" class="titulo_dados_atendimento"><strong>Data Versão</strong></td>
				<td width="14%" align="center" valign="top" class="titulo_dados_atendimento"><strong>Situação</strong></td>
			</tr>
			<tr>
				<td sty height="35" align="center" valign="top">&nbsp;</td>
				<td align="center" valign="top" class="titulo_dados_atendimento" style="text-align: left;"><? echo $row_suporte['anomalia']; ?></td>
				<td align="center" valign="top">&nbsp;</td>
				<td align="center" valign="top">&nbsp;</td>
				<td align="center" valign="top">&nbsp;</td>
				<td align="center" valign="top">&nbsp;</td>
				<td align="center" valign="top">&nbsp;</td>
				<td align="center" valign="top">&nbsp;</td>
			</tr>
			<tr>
				<td colspan="8" align="left" valign="middle"><strong>Diagnóstico</strong>:</td>
			</tr>
			<tr>
				<td colspan="8" align="center" valign="top">&nbsp;</td>
			</tr>
			<tr>
				<td colspan="8" align="center" valign="top">&nbsp;</td>
			</tr>
			<tr>
				<td colspan="8" align="center" valign="top">&nbsp;</td>
			</tr>
			<tr>
				<td colspan="8" align="center" valign="top">&nbsp;</td>
			</tr>
			<tr>
				<td colspan="8" align="center" valign="top">&nbsp;</td>
			</tr>
			<tr>
				<td colspan="8" align="center" valign="top">&nbsp;</td>
			</tr>
			<tr>
				<td colspan="8" align="center" valign="top">&nbsp;</td>
			</tr>
			<tr>
				<td colspan="8" align="center" valign="top">&nbsp;</td>
			</tr>
			<tr>
				<td colspan="8" align="center" valign="top">&nbsp;</td>
			</tr>
			<tr>
				<td colspan="8" align="center" valign="top">&nbsp;</td>
			</tr>
			<tr>
				<td colspan="8" align="center" valign="top">&nbsp;</td>
			</tr>
			<tr>
				<td colspan="8" align="center" valign="top" style="text-align: left">Orientou o cliente sobre o backup diário: Sim ( ) Não ( )</td>
			</tr>
			<tr>
				<td colspan="7" align="center" valign="top" style="text-align: left"><strong>Parecer do Cliente</strong>: </td>
				<td align="center" valign="top" class="titulo_dados_atendimento"><strong>Visto Cliente</strong></td>
			</tr>
			<tr>
				<td colspan="7" align="center" valign="top" style="text-align: left">&nbsp;</td>
				<td rowspan="2" align="center" valign="top" class="titulo_dados_atendimento">&nbsp;</td>
			</tr>
			<tr>
				<td colspan="7" align="center" valign="top" style="text-align: left">&nbsp;</td>
			</tr>
		</table>

		<table cellspacing=0 cellpadding=0 width=100% style="font-family:Verdana, Geneva, sans-serif; font-size: 9px">
			<tr>
				<td width="38%" align="center" valign="top" style="text-align: left; font-size: 9px; padding-right: 10px; line-height: 1.2em;">


					<!-- Manutencao -->
					<? if ($row_suporte_formulario['tipo_formulario'] == "Manutencao") { ?>

						<strong>
							Se passar algum parâmetro para que o cliente modifique não deixe de documentar<br>
							Procedimento de Atualização Simplificado (Leia sempre o procedimento completo):<br>
						</strong>
						- Faça o backup antes de executar qualquer atividade;<br>
						- Verifique com o cliente a situação atual, documentando qualquer anomalia;<br>
						- Verifique com o usuário a situação encontrada, documentando qualquer anomalia;<br>
						- Verifique em cada estação por possíveis arquivos de dados do sistema e remova;<br>
						- Verifique a situação e documente, sobre cada equipamento (Funcionamento, Travamento, Qualidade, Conservação e Compatibilidade), estabilidade de energia, tais como uso de no-break´s em cada terminal comum, exceto emulado, no servidor, testando seu funcionamento, removendo o plug da tomada;<br>
						- Faça cópia dos executáveis em uma sub-pasta do servidor;<br>
						- Faça a atualização dos arquivos utilizando o arquivo de atualização;<br>
						- Execute a interface e entre normalmente;<br>
						- Pela interface acesse o programa utilitário;<br>
						- Atualize o modulo no menu lançamento\modulo, conforme a instalação feita para o cliente;

					<? } ?>
					<!-- fim - Manutencao -->


					<!-- Cobranca -->
					<? if ($row_suporte_formulario['tipo_formulario'] == "Cobranca") { ?>

						Observação: Visita para auxílio em cobrança.

					<? } ?>
					<!-- fim - Cobranca -->


					<!-- Extra -->
					<? if ($row_suporte_formulario['tipo_formulario'] == "Extra") { ?>

						<div style="font-size: 14px; font-weight: bold; margin-top: 3px;">

							Valor atendimento extra / hora:

							<? if (
								($row_manutencao_dados['status17'] == "D" or $row_manutencao_dados['status17'] == "P") and
								$row_empresa_dados['status1'] == "0" and
								$row_empresa_dados['flag1'] == "0"
							) { ?>

								R$ <? echo number_format($row_parametros['valor_formulario_extra_com_manutencao'], 2, ',', '.'); ?>
							<? } else { ?>
								R$ <? echo number_format($row_parametros['valor_formulario_extra_sem_manutencao'], 2, ',', '.'); ?>
							<? } ?>
							
							<br><br>
							Total: R$ <? echo number_format($row_suporte_formulario['valor'], 2, ',', '.'); ?>
						</div>

						<br><br>

						Observação: Para atendimentos em contrato de manutenção limitado ou fora da área de atuação, será de direito da Success Sistemas &amp; Informática Ltda, executar todas as condições existentes no contrato.<br>
						O Cliente acima identificado e abaixo assinado autoriza a cobrança bancária dos serviços acima solicitados e respectivos valores, assumindo a responsabilidade pelo pagamento, não indeferindo por dúvidas ou disposições dos serviços, que neste caso caberá a Success Sistemas as devidas correções, desde que coerente e em total conformidade com o serviço solicitado e descrito acima.

					<? } ?>
					<!-- fim - Extra -->


					<!-- Treinamento -->
					<? if ($row_suporte_formulario['tipo_formulario'] == "Treinamento") { ?>

						<strong>Prezado Cliente/Usuário</strong>,
						<br>
						será muito importante, para a Success Sistemas e com certeza para você também o perfeito entendimento sobre o processo de nosso treinamento, tais como:
						<br>
						1) O Treinamento é voltado ao administrador/proprietário/gerente responsável pela estrutura organizacional e informática, podendo os usuários participar também do treinamento, mas será sempre enfocado ao usuário que irá implantar e disseminar o uso do sistema na sua empresa;
						<br>
						2) Os Usuários envolvidos no treinamento devem ter domínio sobre o processo (atividade) que exerce, sendo o treinamento, apenas como forma de ensinar aos usuários a utilizar o programa para executar a mesma tarefa do cotidiano;
						<br>
						3) O treinamento varia em relação ao tempo de acordo com o nível de conhecimento e percepção dos usuários;
						<br>
						4) É muito comum um profissional ter dúvidas sobre o assunto, mas ele será responsável por levantar todas as duvidas possíveis durante o treinamento;
						<br>
						5) A Success dispõe de consultoria técnica de informática para acompanhar e ou implantar o sistema em sua empresa, porém é objeto de análise e os custos serão levantados isoladamente do valor dos softwares e ou treinamento;

					<? } ?>
					<!-- fim - Treinamento -->


				</td>

				<td align="center" valign="top" style="text-align: left; font-size: 9px; line-height: 1.2em;">


					<!-- Manutencao -->
					<? if ($row_suporte_formulario['tipo_formulario'] == "Manutencao") { ?>

						- Atualize os parâmetros do sistema e pdv em caso de terminal, verificando fechamentos pendentes nos terminais de PDV;<br>
						- Atualize as áreas de acesso de cada usuário existente, confirmando com o responsável a <br>
						permissão de acesso;<br>
						- Atualize as estruturas de dados conforme a instalação feita para o cliente;<br>
						- Atualize os terminais copiando os executáveis do servidor e acesse o programa utilitário e atualize os arquivos de consulta;<br>
						- Verifique as alterações no arquivo texto de instalação passando o cliente conforme o ramo de atividade (DETALHADAMENTE E DOCUMENTANDO);<br>
						- Verifique o tamanho das tabelas, execução das viradas mensal, calculo geral e tamanho do registro de log´s;<br>
						- Documente neste formulário todas as situações necessárias, para verificação futura;<br>
						- Solicite a assinatura do cliente e o responsável;<br>
						Obs: Enviar este formulário mensalmente para a Success Sistemas até o 5º dia útil do mês seguinte.

					<? } ?>
					<!-- fim - Manutencao -->


					<!-- Cobranca -->
					<? if ($row_suporte_formulario['tipo_formulario'] == "Cobranca") { ?>


					<? } ?>
					<!-- fim - Cobranca -->


					<!-- Extra -->
					<? if ($row_suporte_formulario['tipo_formulario'] == "Extra") { ?>



					<? } ?>
					<!-- fim - Extra -->


					<!-- Treinamento -->
					<? if ($row_suporte_formulario['tipo_formulario'] == "Treinamento") { ?>

						6) Somente assine os tópicos abaixo, após o perfeito entendimento, não aceitaremos reclamações sobre o treinamento, quando a reclamação não for coerente com este formulário.
						<br><br>
						7) Informe o nome do responsável pelo treinamento: ___________________________________________
						<br><br>


						<? if ($row_suporte_formulario['creditar'] == "n") { ?>
							<span style="font-size: 14px; font-weight: bold;">
								Valor atendimento treinamento / hora:
								<? if (
									($row_manutencao_dados['status17'] == "D" or $row_manutencao_dados['status17'] == "P") and
									$row_empresa_dados['status1'] == "0" and
									$row_empresa_dados['flag1'] == "0"
								) { ?>
									R$ <? echo number_format($row_parametros['valor_formulario_treinamento_com_manutencao'], 2, ',', '.'); ?>
									<br><br>
									Total: R$ <? 
									echo number_format($row_parametros['valor_formulario_treinamento_com_manutencao'] * ceil((strtotime($row_suporte['data_fim']) - strtotime($row_suporte['data_inicio'])) /60/60), 2, ',', '.');
									?>
								<? } else { ?>
									R$ <? echo number_format($row_parametros['valor_formulario_treinamento_sem_manutencao'], 2, ',', '.'); ?>
									<br><br>
									Total: R$ <? echo number_format($row_parametros['valor_formulario_treinamento_sem_manutencao'] * ceil((strtotime($row_suporte['data_fim']) - strtotime($row_suporte['data_inicio'])) /60/60), 2, ',', '.'); ?>
								<? } ?>
							</span>
							<br><br>
						<? } ?>

					<? } ?>
					<!-- fim - Treinamento -->


					<div style="font-weight: bold; margin-top: 5px;">Últimos atendimentos:</div>
					<table width="100%" border="0" cellspacing="0" cellpadding="0" class="bordasimples">
						<tr>
							<td class="titulo_dados_atendimento" style="text-align: left; font-size: 9px" width="40"><strong>Sup. nº</strong></td>
							<td class="titulo_dados_atendimento" style="text-align: left; font-size: 9px" width="40"><strong>In-loco</strong></td>
							<td class="titulo_dados_atendimento" style="text-align: left; font-size: 9px" width="70"><strong>Data</strong></td>
							<td class="titulo_dados_atendimento" style="text-align: left; font-size: 9px"><strong>Anomalia</strong></td>
							<td class="titulo_dados_atendimento" style="text-align: left; font-size: 9px" width="70"><strong>Tipo de Form.</strong></td>
							<td class="titulo_dados_atendimento" style="text-align: left; font-size: 9px" width="50"><strong>Situação</strong></td>
						</tr>
						<? if ($totalRows_suporte_ultimos > 0) { ?>
							<? do { ?>
								<tr>
									<td class="titulo_dados_atendimento" style="text-align: left; font-size: 9px"><? echo $row_suporte_ultimos['id']; ?></td>
									<td class="titulo_dados_atendimento" style="text-align: left; font-size: 9px"><?php if ($row_suporte_ultimos['inloco'] == "s") {
																														echo "Sim";
																													}
																													if ($row_suporte_ultimos['inloco'] == "n") {
																														echo "Não";
																													} ?></td>
									<td class="titulo_dados_atendimento" style="text-align: left; font-size: 9px"><? echo date('d-m-Y', strtotime($row_suporte_ultimos['data_suporte'])); ?></td>
									<td class="titulo_dados_atendimento" style="text-align: left; font-size: 9px"><?php $texto = $row_suporte_ultimos['anomalia'];
																													$texto_limite = 38;
																													if (strlen($texto) > $texto_limite) {
																														$texto = substr($texto, 0, $texto_limite) . '...';
																													}
																													echo $texto; ?></td>
									<td class="titulo_dados_atendimento" style="text-align: left; font-size: 9px"><? echo $row_suporte_ultimos['tipo_formulario']; ?></td>
									<td class="titulo_dados_atendimento" style="text-align: left; font-size: 9px"><? echo $row_suporte_ultimos['situacao']; ?></td>
								</tr>
							<?php } while ($row_suporte_ultimos = mysql_fetch_assoc($suporte_ultimos)); ?>
						<? } ?>
					</table>



				</td>
			</tr>
		</table>

	<? } ?>
</body>

</html>
<?php
mysql_free_result($usuario);
mysql_free_result($empresa_dados);
mysql_free_result($empresa_dados_comp);
mysql_free_result($manutencao_dados);
mysql_free_result($modcon);
mysql_free_result($suporte_formulario);
mysql_free_result($suporte);
mysql_free_result($solicitacao_ultimos);
mysql_free_result($suporte_ultimos);
mysql_free_result($reclamacao);
?>