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

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
	$editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING'], ENT_QUOTES, 'UTF-8');
}

require_once('parametros.php');
require_once('funcao_consulta_versao_array.php');

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

if ($praca_status == 0) {
	header("Location: painel/index.php");
	exit;
}

// solicitacao_editar (recordset) - seleciona a solicitação atual
$colname_solicitacao_editar = "-1";
if (isset($_GET['id_solicitacao'])) {
	$colname_solicitacao_editar = $_GET['id_solicitacao'];
}
mysql_select_db($database_conexao, $conexao);
$query_solicitacao_editar = sprintf(
	"
SELECT 
	id, id_usuario_responsavel, situacao, praca, dt_recebimento, id_usuario_responsavel, id_operador, id_executante, id_testador, id_analista_orcamento, id_encaminhamento, 
	(SELECT nome FROM usuarios WHERE usuarios.IdUsuario = solicitacao.id_usuario_responsavel) as usuario_responsavel, 
	(SELECT nome FROM usuarios WHERE usuarios.IdUsuario = solicitacao.id_operador) as nome_operador, 
	(SELECT nome FROM usuarios WHERE usuarios.IdUsuario = solicitacao.id_executante) as nome_executante, 
	(SELECT nome FROM usuarios WHERE usuarios.IdUsuario = solicitacao.id_analista_orcamento) as nome_analista_orcamento, 
	(SELECT nome FROM usuarios WHERE usuarios.IdUsuario = solicitacao.id_testador) as nome_testador 
FROM 
	solicitacao 
WHERE 
	id = %s
",
	GetSQLValueString($colname_solicitacao_editar, "int")
);
$solicitacao_editar = mysql_query($query_solicitacao_editar, $conexao) or die(mysql_error());
$row_solicitacao_editar = mysql_fetch_assoc($solicitacao_editar);
$totalRows_solicitacao_editar = mysql_num_rows($solicitacao_editar);
// fim - solicitacao_ditar (recordset) - seleciona a solicitação atual

// caso não tenho solicitacao, volta para listagem ********************************
if ($totalRows_solicitacao_editar < 1) {
	$site_link_redireciona = "solicitacao.php?padrao=sim&" . $situacao_padrao;
	echo sprintf("<meta http-equiv=\"refresh\" content=\"0; url=%s\">", $site_link_redireciona);
	exit;
}
// fim - caso não tenho solicitacao, volta para listagem **************************

// se solicitação é diferente de 'solucionada' e 'reprovada'
if ($row_solicitacao_editar['situacao'] != "solucionada" and $row_solicitacao_editar['situacao'] != "reprovada") {

	// insert - LEU --------------------------------------------------
	// se é solicitante
	if ($row_solicitacao_editar['id_usuario_responsavel'] == $row_usuario['IdUsuario']) {
		$updateSQL_leu = sprintf(
			"UPDATE solicitacao SET solicitante_leu=%s WHERE id=%s",
			GetSQLValueString(date("Y-m-d H:i:s"), "date"),
			GetSQLValueString($row_solicitacao_editar['id'], "int")
		);

		mysql_select_db($database_conexao, $conexao);
		$Result_leu = mysql_query($updateSQL_leu, $conexao) or die(mysql_error());
	}
	// fim - se é solicitante

	// se é operador
	if ($row_usuario['controle_solicitacao'] == "Y" and $row_solicitacao_editar['id_operador'] == $row_usuario['IdUsuario']) {
		$updateSQL_leu = sprintf(
			"UPDATE solicitacao SET operador_leu=%s WHERE id=%s",
			GetSQLValueString(date("Y-m-d H:i:s"), "date"),
			GetSQLValueString($row_solicitacao_editar['id'], "int")
		);

		mysql_select_db($database_conexao, $conexao);
		$Result_leu = mysql_query($updateSQL_leu, $conexao) or die(mysql_error());
	}
	// fim - se é operador

	// se é analista orcamento
	if ($row_usuario['solicitacao_executante'] == "Y" and $row_solicitacao_editar['id_analista_orcamento'] == $row_usuario['IdUsuario']) {
		$updateSQL_leu = sprintf(
			"UPDATE solicitacao SET analista_orcamento_leu=%s WHERE id=%s",
			GetSQLValueString(date("Y-m-d H:i:s"), "date"),
			GetSQLValueString($row_solicitacao_editar['id'], "int")
		);

		mysql_select_db($database_conexao, $conexao);
		$Result_leu = mysql_query($updateSQL_leu, $conexao) or die(mysql_error());
	}
	// fim - se é analista orcamento

	// se é executante
	if ($row_usuario['solicitacao_executante'] == "Y" and $row_solicitacao_editar['id_executante'] == $row_usuario['IdUsuario']) {
		$updateSQL_leu = sprintf(
			"UPDATE solicitacao SET executante_leu=%s WHERE id=%s",
			GetSQLValueString(date("Y-m-d H:i:s"), "date"),
			GetSQLValueString($row_solicitacao_editar['id'], "int")
		);

		mysql_select_db($database_conexao, $conexao);
		$Result_leu = mysql_query($updateSQL_leu, $conexao) or die(mysql_error());
	}
	// fim - se é executante

	// se é testador
	if ($row_usuario['solicitacao_testador'] == "Y" and $row_solicitacao_editar['id_testador'] == $row_usuario['IdUsuario']) {
		$updateSQL_leu = sprintf(
			"UPDATE solicitacao SET testador_leu=%s WHERE id=%s",
			GetSQLValueString(date("Y-m-d H:i:s"), "date"),
			GetSQLValueString($row_solicitacao_editar['id'], "int")
		);

		mysql_select_db($database_conexao, $conexao);
		$Result_leu = mysql_query($updateSQL_leu, $conexao) or die(mysql_error());
	}
	// fim - se é testador	
	// fim - insert - LEU --------------------------------------------

}
// fim - se solicitação é diferente de 'solucionada' e 'reprovada'

mysql_free_result($solicitacao_editar);

// ----------------------------------------------------------------------------------------------------------------------------------------------------------------

// SELECT - solicitacao
$colname_solicitacao = "-1";
if (isset($_GET['id_solicitacao'])) {
	$colname_solicitacao = $_GET['id_solicitacao'];
}
mysql_select_db($database_conexao, $conexao);
$query_solicitacao = sprintf(
	"
SELECT solicitacao.*, 
(SELECT nome FROM usuarios WHERE usuarios.IdUsuario = solicitacao.id_usuario_responsavel) as usuario_responsavel, 
(SELECT nome FROM usuarios WHERE usuarios.IdUsuario = solicitacao.id_operador) as nome_operador, 
(SELECT nome FROM usuarios WHERE usuarios.IdUsuario = solicitacao.id_executante) as nome_executante, 
(SELECT nome FROM usuarios WHERE usuarios.IdUsuario = solicitacao.id_analista_orcamento) as nome_analista_orcamento, 
(SELECT nome FROM usuarios WHERE usuarios.IdUsuario = solicitacao.id_testador) as nome_testador 
FROM solicitacao
 WHERE solicitacao.id = %s",
	GetSQLValueString($colname_solicitacao, "int")
);
$solicitacao = mysql_query($query_solicitacao, $conexao) or die(mysql_error());
$row_solicitacao = mysql_fetch_assoc($solicitacao);
$totalRows_solicitacao = mysql_num_rows($solicitacao);
// fim - SELECT - solicitacao

// programa (recordset)
$colname_geral_tipo_programa = "-1";
if (isset($row_solicitacao['id_programa'])) {
	$colname_geral_tipo_programa = $row_solicitacao['id_programa'];
}
mysql_select_db($database_conexao, $conexao);
$query_geral_tipo_programa = sprintf("SELECT * FROM geral_tipo_programa WHERE id_programa = %s", GetSQLValueString($colname_geral_tipo_programa, "int"));
$geral_tipo_programa = mysql_query($query_geral_tipo_programa, $conexao) or die(mysql_error());
$row_geral_tipo_programa = mysql_fetch_assoc($geral_tipo_programa);
$totalRows_geral_tipo_programa = mysql_num_rows($geral_tipo_programa);
// fim - programa (recordset)

// subprograma (recordset)
$colname_geral_tipo_subprograma = "-1";
if (isset($row_solicitacao['id_subprograma'])) {
	$colname_geral_tipo_subprograma = $row_solicitacao['id_subprograma'];
}
mysql_select_db($database_conexao, $conexao);
$query_geral_tipo_subprograma = sprintf("SELECT * FROM geral_tipo_subprograma WHERE id_subprograma = %s", GetSQLValueString($colname_geral_tipo_subprograma, "int"));
$geral_tipo_subprograma = mysql_query($query_geral_tipo_subprograma, $conexao) or die(mysql_error());
$row_geral_tipo_subprograma = mysql_fetch_assoc($geral_tipo_subprograma);
$totalRows_geral_tipo_subprograma = mysql_num_rows($geral_tipo_subprograma);
// fim - subprograma (recordset)

// solicitacao_tipo_parecer (recordset)
mysql_select_db($database_conexao, $conexao);
$query_solicitacao_tipo_parecer = "SELECT * FROM solicitacao_tipo_parecer ORDER BY titulo ASC";
$solicitacao_tipo_parecer = mysql_query($query_solicitacao_tipo_parecer, $conexao) or die(mysql_error());
$row_solicitacao_tipo_parecer = mysql_fetch_assoc($solicitacao_tipo_parecer);
$totalRows_solicitacao_tipo_parecer = mysql_num_rows($solicitacao_tipo_parecer);
// fim - solicitacao_tipo_parecer (recordset)

// descricao
$colname_descricao = "-1";
if (isset($_GET['id_solicitacao'])) {
	$colname_descricao = $_GET['id_solicitacao'];
}
mysql_select_db($database_conexao, $conexao);
$query_descricao = sprintf(
	"
SELECT *, 
(SELECT nome FROM usuarios WHERE usuarios.IdUsuario = solicitacao_descricoes.id_usuario_responsavel) as usuario_responsavel 
FROM solicitacao_descricoes 
WHERE id_solicitacao = %s 
ORDER BY id DESC",
	GetSQLValueString($colname_descricao, "text")
);
$descricao = mysql_query($query_descricao, $conexao) or die(mysql_error());
$row_descricao = mysql_fetch_assoc($descricao);
$totalRows_descricao = mysql_num_rows($descricao);
// fim - descricao

// arquivos_anexos
$colname_arquivos_anexos = "-1";
if (isset($_GET['id_solicitacao'])) {
	$colname_arquivos_anexos = $_GET['id_solicitacao'];
}
mysql_select_db($database_conexao, $conexao);
$query_arquivos_anexos = sprintf("SELECT id_arquivo FROM solicitacao_arquivos WHERE id_solicitacao = %s", GetSQLValueString($colname_arquivos_anexos, "int"));
$arquivos_anexos = mysql_query($query_arquivos_anexos, $conexao) or die(mysql_error());
$row_arquivos_anexos = mysql_fetch_assoc($arquivos_anexos);
$totalRows_arquivos_anexos = mysql_num_rows($arquivos_anexos);
// fim - arquivos_anexos

// solicitacao_devolucao
$colname_solicitacao_devolucao = "-1";
if (isset($_GET['id_solicitacao'])) {
	$colname_solicitacao_devolucao = $_GET['id_solicitacao'];
}
mysql_select_db($database_conexao, $conexao);
$query_solicitacao_devolucao = sprintf(
	"
SELECT 
	solicitacao_devolucao.motivo, 
	solicitacao_tipo_devolucao.titulo AS solicitacao_tipo_devolucao_titulo, 
	COUNT(solicitacao_devolucao.id_solicitacao_devolucao) AS retorno 
FROM 
	solicitacao_devolucao 
LEFT JOIN
	solicitacao_tipo_devolucao ON solicitacao_tipo_devolucao.IdTipoDevolucao = solicitacao_devolucao.motivo 
WHERE 
	id_solicitacao = %s 

GROUP BY
	motivo 
ORDER BY 
	motivo ASC
",
	GetSQLValueString($colname_solicitacao_devolucao, "int")
);
$solicitacao_devolucao = mysql_query($query_solicitacao_devolucao, $conexao) or die(mysql_error());
$row_solicitacao_devolucao = mysql_fetch_assoc($solicitacao_devolucao);
$totalRows_solicitacao_devolucao = mysql_num_rows($solicitacao_devolucao);
// fim - solicitacao_devolucao

// tempo_gasto
$colname_tempo_gasto = "-1";
if (isset($_GET['id_solicitacao'])) {
	$colname_tempo_gasto = $_GET['id_solicitacao'];
}
mysql_select_db($database_conexao, $conexao);
$query_tempo_gasto = sprintf("
SELECT id_solicitacao_tempo_gasto 
FROM solicitacao_tempo_gasto 
WHERE id_solicitacao = %s 
ORDER BY id_solicitacao_tempo_gasto DESC", GetSQLValueString($colname_tempo_gasto, "int"));
$tempo_gasto = mysql_query($query_tempo_gasto, $conexao) or die(mysql_error());
$row_tempo_gasto = mysql_fetch_assoc($tempo_gasto);
$totalRows_tempo_gasto = mysql_num_rows($tempo_gasto);
// fim - tempo_gasto

// SELECT - suporte
mysql_select_db($database_conexao, $conexao);
$query_suporte = sprintf(
	"
SELECT 
	suporte.*, 
	(SELECT nome FROM usuarios WHERE usuarios.IdUsuario = suporte.id_usuario_responsavel) as usuario_responsavel 
FROM 
	suporte 
WHERE 
	suporte.id = %s
",
	GetSQLValueString($row_solicitacao['protocolo_suporte'], "int")
);
$suporte = mysql_query($query_suporte, $conexao) or die(mysql_error());
$row_suporte = mysql_fetch_assoc($suporte);
$totalRows_suporte = mysql_num_rows($suporte);
// fim - SELECT - suporte

// SELECT - solicitacao_desmembrada
mysql_select_db($database_conexao, $conexao);
$query_solicitacao_desmembrada = sprintf(
	"
SELECT 
	solicitacao.*, 
	(SELECT nome FROM usuarios WHERE usuarios.IdUsuario = solicitacao.id_usuario_responsavel) as usuario_responsavel, 
	(SELECT nome FROM usuarios WHERE usuarios.IdUsuario = solicitacao.id_operador) as usuario_operador
FROM 
	solicitacao 
WHERE 
	solicitacao.id = %s
",
	GetSQLValueString($row_solicitacao['solicitacao_desmembrada'], "int")
);
$solicitacao_desmembrada = mysql_query($query_solicitacao_desmembrada, $conexao) or die(mysql_error());
$row_solicitacao_desmembrada = mysql_fetch_assoc($solicitacao_desmembrada);
$totalRows_solicitacao_desmembrada = mysql_num_rows($solicitacao_desmembrada);
// fim - SELECT - solicitacao_desmembrada

// reclamacao_solicitacao
mysql_select_db($database_conexao, $conexao);
$query_reclamacao_solicitacao = sprintf("
SELECT id, data_suporte, situacao, titulo 
FROM suporte 
WHERE reclamacao_solicitacao = %s 
ORDER BY id ASC", GetSQLValueString($row_solicitacao['id'], "text"));
$reclamacao_solicitacao = mysql_query($query_reclamacao_solicitacao, $conexao) or die(mysql_error());
$row_reclamacao_solicitacao = mysql_fetch_assoc($reclamacao_solicitacao);
$totalRows_reclamacao_solicitacao = mysql_num_rows($reclamacao_solicitacao);
// fim - reclamacao_solicitacao

// reclamacao_consulta
mysql_select_db($database_conexao, $conexao);
$query_reclamacao_consulta = sprintf(
	"
SELECT id, empresa, situacao, status_flag     
FROM suporte 
WHERE contrato = %s and tipo_suporte = 'r' and 
((status_flag = 'a') or (status_flag = 'f' and DATE_ADD(data_fim,INTERVAL " . $row_parametros['suporte_reclamacao_mensagem_inicial_dias'] . " DAY) >= now()))
",
	GetSQLValueString($row_solicitacao['contrato'], "text")
);
$reclamacao_consulta = mysql_query($query_reclamacao_consulta, $conexao) or die(mysql_error());
$row_reclamacao_consulta = mysql_fetch_assoc($reclamacao_consulta);
$totalRows_reclamacao_consulta = mysql_num_rows($reclamacao_consulta);

if ($totalRows_reclamacao_consulta > 0) {

	$reclamacao_consulta_status = 0;
	$reclamacao_consulta_mensagem_aberta = NULL;
	$reclamacao_consulta_mensagem_fechada = NULL;
	do {

		if ($row_reclamacao_consulta['status_flag'] == "f") {
			$reclamacao_consulta_mensagem_fechada .= 'Reclamação: ' . $row_reclamacao_consulta['id'] . ' - Situação: ' . $row_reclamacao_consulta['situacao'] . '\n';
		} else {
			$reclamacao_consulta_status = 1;
			$reclamacao_consulta_mensagem_aberta .= 'Reclamação: ' . $row_reclamacao_consulta['id'] . ' - Situação: ' . $row_reclamacao_consulta['situacao'] . '\n';
		}
	} while ($row_reclamacao_consulta = mysql_fetch_assoc($reclamacao_consulta));

	$reclamacao_consulta_mensagem_corpo = NULL;
	if ($reclamacao_consulta_status == 0) {
		$reclamacao_consulta_mensagem = 'ATENÇÃO:\nRECLAMAÇÃO REGISTRADA RECENTEMENTE\nCliente: ' . utf8_encode($row_solicitacao['empresa']) . '\n' . $reclamacao_consulta_mensagem_fechada;
		$reclamacao_consulta_mensagem_corpo = 'ATENÇÃO: CLIENTE COM RECLAMAÇÃO REGISTRADA RECENTEMENTE';
	} else if ($reclamacao_consulta_status == 1) {
		$reclamacao_consulta_mensagem = 'ATENÇÃO:\nRECLAMAÇÃO EM ANDAMENTO\nCliente: ' . utf8_encode($row_solicitacao['empresa']) . '\n' . $reclamacao_consulta_mensagem_aberta;
		$reclamacao_consulta_mensagem_corpo = 'ATENÇÃO: CLIENTE COM RECLAMAÇÃO EM ANDAMENTO';
	}
}
// fim - reclamacao_consulta
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">

	<link rel="stylesheet" href="css/suporte.css" type="text/css" />
	<link rel="stylesheet" href="css/suporte_imprimir.css" type="text/css" media="print" />

	<script type="text/javascript" src="js/jquery.js"></script>

	<script type="text/javascript" src="js/thickbox.js"></script>
	<link rel="stylesheet" href="css/thickbox.css" type="text/css" media="screen" />

	<script type="text/javascript">
		window.history.forward(1); // Desabilita a função de voltar do Browser

		$(document).ready(function() {

			// botao_sim (sem janela modal)
			var botao_atual = $('a[name="botao_sim"]');
			botao_atual.click(function() {
				window.open(this, '_top');
				botao_atual.removeAttr('href');
			});
			// fim - botao_sim (sem janela modal)

		});
	</script>
	<title>Solicitação n° <? echo $row_solicitacao['id']; ?></title>
</head>

<body>

	<div class="div_solicitacao_linhas">
		<table cellspacing="0" cellpadding="0" width="100%">
			<tr>
				<td style="text-align:left">
					Solicitação n° <? echo $row_solicitacao['id']; ?>
				</td>

				<td style="text-align: right">
					&lt;&lt; <a href="solicitacao.php?padrao=sim&<? echo $situacao_padrao; ?>">Voltar</a> |
					Usuário logado: <? echo $row_usuario['nome']; ?> |
					<a href="painel/padrao_sair.php">Sair</a>
				</td>
			</tr>
		</table>
	</div>

	<div class="div_solicitacao_linhas2">
		<table cellspacing="0" cellpadding="0" width="100%">
			<tr>
				<td style="text-align:left">
					<span class="label_solicitacao">Empresa:</span> <?php echo utf8_encode($row_solicitacao['empresa']); ?> |
					<span class="label_solicitacao">Contrato:</span> <?php echo $row_solicitacao['contrato']; ?> |
					<span class="label_solicitacao">Praça:</span> <?php echo $row_solicitacao['praca']; ?>
				</td>

				<td style="text-align: right">
					<span class="label_solicitacao">Versão: </span><?php echo funcao_consulta_versao_array($row_solicitacao['versao']); ?>
					<? if (
						(
							($row_usuario['controle_solicitacao'] == "Y" or $row_solicitacao['id_operador'] == $row_usuario['IdUsuario']) // se é operador da solicitação
							or
							($row_solicitacao['id_usuario_responsavel'] == $row_usuario['IdUsuario'] and $row_solicitacao['situacao'] == "criada") // se é solicitante da solicitação
						) and
						($row_solicitacao['situacao'] != "reprovada" and $row_solicitacao['situacao'] != "solucionada")
					) {
					?>
						<a href="solicitacao_editar_tabela.php?id_solicitacao=<? echo $row_solicitacao['id']; ?>&interacao=<? echo $row_solicitacao['interacao']; ?>&situacao=editar&acao=Alterar versão&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $solicitacao_editar_tabela_height; ?>&width=<? echo $solicitacao_editar_tabela_width; ?>&modal=true" class="thickbox">
							<img src="imagens/editar.png" id="botao_editar" align="absbottom" border="0" title="Alterar versão">
						</a>
					<? } ?> |
					<span class="label_solicitacao">Distribuição: </span><?php echo $row_solicitacao['geral_tipo_distribuicao']; ?>
					<? if (
						(
							($row_usuario['controle_solicitacao'] == "Y" or $row_solicitacao['id_operador'] == $row_usuario['IdUsuario']) // se é operador da solicitação
							or
							($row_solicitacao['id_usuario_responsavel'] == $row_usuario['IdUsuario'] and $row_solicitacao['situacao'] == "criada") // se é solicitante da solicitação
						) and
						($row_solicitacao['situacao'] != "reprovada" and $row_solicitacao['situacao'] != "solucionada")
					) {
					?>
						<a href="solicitacao_editar_tabela.php?id_solicitacao=<? echo $row_solicitacao['id']; ?>&interacao=<? echo $row_solicitacao['interacao']; ?>&situacao=editar&acao=Alterar distribuição&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $solicitacao_editar_tabela_height; ?>&width=<? echo $solicitacao_editar_tabela_width; ?>&modal=true" class="thickbox">
							<img src="imagens/editar.png" id="botao_editar" align="absbottom" border="0" title="Alterar distribuição">
						</a>
					<? } ?>
				</td>
			</tr>
		</table>
	</div>

	<div class="div_solicitacao_linhas3">
		<table cellspacing="0" cellpadding="0" width="100%">
			<tr>
				<td style="text-align: left">
					<span class="label_solicitacao">Título: </span><?php echo $row_solicitacao['titulo']; ?>
					<? if (
						(
							($row_usuario['controle_solicitacao'] == "Y" or $row_solicitacao['id_operador'] == $row_usuario['IdUsuario']) // se é operador da solicitação
							or
							($row_solicitacao['id_usuario_responsavel'] == $row_usuario['IdUsuario'] and $row_solicitacao['situacao'] == "criada") // se é solicitante da solicitação
						) and
						($row_solicitacao['situacao'] != "reprovada" and $row_solicitacao['situacao'] != "solucionada")
					) {
					?>
						<a href="solicitacao_editar_tabela.php?id_solicitacao=<? echo $row_solicitacao['id']; ?>&interacao=<? echo $row_solicitacao['interacao']; ?>&situacao=editar&acao=Alterar título&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $solicitacao_editar_tabela_height; ?>&width=<? echo $solicitacao_editar_tabela_width; ?>&modal=true" class="thickbox">
							<img src="imagens/editar.png" id="botao_editar" align="absbottom" border="0" title="Alterar título">
						</a>
					<? } ?>
				</td>

				<td style="text-align: right" width=" 280">
					<span class="label_solicitacao">Criação: </span><? echo date('d-m-Y  H:i', strtotime($row_solicitacao['dt_solicitacao'])); ?>
					<br>

					<? if ($totalRows_suporte > 0 and $row_solicitacao['protocolo_suporte'] <> NULL) { ?>
						<span class="label_solicitacao">Núm. Controle Suporte:</span> <strong><?php echo $row_solicitacao['protocolo_suporte']; ?></strong> -
						<a href="suporte_editar.php?id_suporte=<?php echo $row_solicitacao['protocolo_suporte']; ?>&padrao=sim" target="_blank" style="text-decoration: none; color: #000; font-weight: bold;">
							Acessar
						</a>
					<? } ?>

					<? if ($totalRows_solicitacao_desmembrada > 0 and $row_solicitacao['solicitacao_desmembrada'] <> NULL) { ?>
						<span class="label_solicitacao">Num. Solicitação Desmembrada:</span> <strong><?php echo $row_solicitacao['solicitacao_desmembrada']; ?></strong> -
						<a href="solicitacao_editar.php?id_solicitacao=<?php echo $row_solicitacao['solicitacao_desmembrada']; ?>&padrao=sim" target="_blank" style="text-decoration: none; color: #000; font-weight: bold;">
							Acessar
						</a>
					<? } ?>

				</td>
			</tr>
		</table>
	</div>

	<? if ($totalRows_reclamacao_consulta > 0) { ?>
		<div class="div_solicitacao_linhas4" style="color: red;">
			<? echo $reclamacao_consulta_mensagem_corpo; ?>
		</div>
	<? } ?>

	<div class="div_solicitacao_linhas2">
		<table cellspacing="0" cellpadding="0" width="100%">
			<tr>
				<td style="text-align:left">
					<span class="label_solicitacao">Programa: </span><?php echo $row_geral_tipo_programa['programa']; ?> |
					<span class="label_solicitacao">Subprograma: </span><?php echo $row_geral_tipo_subprograma['subprograma']; ?>
					<? if (
						(
							($row_usuario['controle_solicitacao'] == "Y" or $row_solicitacao['id_operador'] == $row_usuario['IdUsuario']) // se é operador da solicitação
							or
							($row_solicitacao['id_usuario_responsavel'] == $row_usuario['IdUsuario'] and $row_solicitacao['situacao'] == "criada") // se é solicitante da solicitação
						) and
						($row_solicitacao['situacao'] != "reprovada" and $row_solicitacao['situacao'] != "solucionada")
					) {
					?>

						<a href="solicitacao_editar_tabela.php?id_solicitacao=<? echo $row_solicitacao['id']; ?>&interacao=<? echo $row_solicitacao['interacao']; ?>&situacao=editar&acao=Alterar programa/subprograma&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $solicitacao_editar_tabela_height; ?>&width=<? echo $solicitacao_editar_tabela_width; ?>&modal=true" class="thickbox">

							<img src="imagens/editar.png" id="botao_editar" align="absbottom" border="0" title="Alterar programa/subprograma">
						</a>
					<? } ?>
				</td>

				<td style="text-align: right">
					<span class="label_solicitacao">Campo: </span><?php echo $row_solicitacao['campo']; ?>
					<? if (
						(
							($row_usuario['controle_solicitacao'] == "Y" or $row_solicitacao['id_operador'] == $row_usuario['IdUsuario']) // se é operador da solicitação
							or
							($row_solicitacao['id_usuario_responsavel'] == $row_usuario['IdUsuario'] and $row_solicitacao['situacao'] == "criada") // se é solicitante da solicitação
						) and
						($row_solicitacao['situacao'] != "reprovada" and $row_solicitacao['situacao'] != "solucionada")
					) {
					?>

						<a href="solicitacao_editar_tabela.php?id_solicitacao=<? echo $row_solicitacao['id']; ?>&interacao=<? echo $row_solicitacao['interacao']; ?>&situacao=editar&acao=Alterar campo&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $solicitacao_editar_tabela_height; ?>&width=<? echo $solicitacao_editar_tabela_width; ?>&modal=true" class="thickbox">

							<img src="imagens/editar.png" id="botao_editar" align="absbottom" border="0" title="Alterar campo">
						</a>
					<? } ?>
				</td>

			</tr>
		</table>
	</div>

	<div class="div_solicitacao_linhas3">
		<table cellspacing="0" cellpadding="0" width="100%">
			<tr>

				<td style="text-align: left;">
					<span class="label_solicitacao">Data executável: </span><?php echo implode("-", array_reverse(explode("-", $row_solicitacao['data_executavel']))); ?>
					<? if (
						(
							($row_usuario['controle_solicitacao'] == "Y" or $row_solicitacao['id_operador'] == $row_usuario['IdUsuario']) // se é operador da solicitação
							or
							($row_solicitacao['id_usuario_responsavel'] == $row_usuario['IdUsuario'] and $row_solicitacao['situacao'] == "criada") // se é solicitante da solicitação
						) and
						($row_solicitacao['situacao'] != "reprovada" and $row_solicitacao['situacao'] != "solucionada")
					) {
					?>

						<a href="solicitacao_editar_tabela.php?id_solicitacao=<? echo $row_solicitacao['id']; ?>&interacao=<? echo $row_solicitacao['interacao']; ?>&situacao=editar&acao=Alterar data do executável&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $solicitacao_editar_tabela_height; ?>&width=<? echo $solicitacao_editar_tabela_width; ?>&modal=true" class="thickbox">

							<img src="imagens/editar.png" id="botao_editar" align="absbottom" border="0" title="Alterar data do executável">
						</a>
					<? } ?> |
					<span class="label_solicitacao">Hora executável: </span><?php echo $row_solicitacao['hora_executavel']; ?>
					<? if (
						(
							($row_usuario['controle_solicitacao'] == "Y" or $row_solicitacao['id_operador'] == $row_usuario['IdUsuario']) // se é operador da solicitação
							or
							($row_solicitacao['id_usuario_responsavel'] == $row_usuario['IdUsuario'] and $row_solicitacao['situacao'] == "criada") // se é solicitante da solicitação
						) and
						($row_solicitacao['situacao'] != "reprovada" and $row_solicitacao['situacao'] != "solucionada")
					) {
					?>

						<a href="solicitacao_editar_tabela.php?id_solicitacao=<? echo $row_solicitacao['id']; ?>&interacao=<? echo $row_solicitacao['interacao']; ?>&situacao=editar&acao=Alterar hora do executável&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $solicitacao_editar_tabela_height; ?>&width=<? echo $solicitacao_editar_tabela_width; ?>&modal=true" class="thickbox">

							<img src="imagens/editar.png" id="botao_editar" align="absbottom" border="0" title="Alterar hora do executável">
						</a>
					<? } ?>
				</td>

				<td style="text-align: right">
					<span class="label_solicitacao">Banco de dados:</span> <?php echo $row_solicitacao['tipo_bd']; ?>
					<? if (
						(
							($row_usuario['controle_solicitacao'] == "Y" or $row_solicitacao['id_operador'] == $row_usuario['IdUsuario']) // se é operador da solicitação
							or
							($row_solicitacao['id_usuario_responsavel'] == $row_usuario['IdUsuario'] and $row_solicitacao['situacao'] == "criada") // se é solicitante da solicitação
						) and
						($row_solicitacao['situacao'] != "reprovada" and $row_solicitacao['situacao'] != "solucionada")
					) {
					?>

						<a href="solicitacao_editar_tabela.php?id_solicitacao=<? echo $row_solicitacao['id']; ?>&interacao=<? echo $row_solicitacao['interacao']; ?>&situacao=editar&acao=Alterar banco de dados&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $solicitacao_editar_tabela_height; ?>&width=<? echo $solicitacao_editar_tabela_width; ?>&modal=true" class="thickbox">

							<img src="imagens/editar.png" id="botao_editar" align="absbottom" border="0" title="Alterar banco de dados">
						</a>
					<? } ?> |
					<span class="label_solicitacao">ECF:</span> <?php echo $row_solicitacao['geral_tipo_ecf']; ?>
					<? if (
						(
							($row_usuario['controle_solicitacao'] == "Y" or $row_solicitacao['id_operador'] == $row_usuario['IdUsuario']) // se é operador da solicitação
							or
							($row_solicitacao['id_usuario_responsavel'] == $row_usuario['IdUsuario'] and $row_solicitacao['situacao'] == "criada") // se é solicitante da solicitação
						) and
						($row_solicitacao['situacao'] != "reprovada" and $row_solicitacao['situacao'] != "solucionada")
					) {
					?>

						<a href="solicitacao_editar_tabela.php?id_solicitacao=<? echo $row_solicitacao['id']; ?>&interacao=<? echo $row_solicitacao['interacao']; ?>&situacao=editar&acao=Alterar ECF&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $solicitacao_editar_tabela_height; ?>&width=<? echo $solicitacao_editar_tabela_width; ?>&modal=true" class="thickbox">

							<img src="imagens/editar.png" id="botao_editar" align="absbottom" border="0" title="Alterar ECF">
						</a>
					<? } ?>
				</td>

			</tr>
		</table>
	</div>

	<div class="div_solicitacao_linhas2">
		<table cellspacing="0" cellpadding="0" width="100%">
			<tr>

				<td style="text-align: left">
					<span class="label_solicitacao">Tipo: </span><?php echo $row_solicitacao['tipo']; ?>

					<? if (
						(
							($row_usuario['controle_solicitacao'] == "Y" or $row_solicitacao['id_operador'] == $row_usuario['IdUsuario']) // se é operador da solicitação
							or
							($row_solicitacao['id_usuario_responsavel'] == $row_usuario['IdUsuario'] and $row_solicitacao['situacao'] == "criada") // se é solicitante da solicitação
						) and
						($row_solicitacao['situacao'] != "reprovada" and $row_solicitacao['situacao'] != "solucionada")
					) {
					?>

						<a href="solicitacao_editar_tabela.php?id_solicitacao=<? echo $row_solicitacao['id']; ?>&interacao=<? echo $row_solicitacao['interacao']; ?>&situacao=editar&acao=Alterar tipo da solicitação&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $solicitacao_editar_tabela_height; ?>&width=<? echo $solicitacao_editar_tabela_width; ?>&modal=true" class="thickbox">

							<img src="imagens/editar.png" id="botao_editar" align="absbottom" border="0" title="Alterar tipo da solicitação">
						</a>
					<? } ?> |

					<span class="label_solicitacao">Prioridade: </span><?php echo $row_solicitacao['prioridade']; ?>
					<? if (
						(
							($row_usuario['controle_solicitacao'] == "Y" or $row_solicitacao['id_operador'] == $row_usuario['IdUsuario']) // se é operador da solicitação
							or
							($row_solicitacao['id_usuario_responsavel'] == $row_usuario['IdUsuario'] and $row_solicitacao['situacao'] == "criada") // se é solicitante da solicitação
						) and
						($row_solicitacao['situacao'] != "reprovada" and $row_solicitacao['situacao'] != "solucionada")
					) {
					?>

						<a href="solicitacao_editar_tabela.php?id_solicitacao=<? echo $row_solicitacao['id']; ?>&interacao=<? echo $row_solicitacao['interacao']; ?>&situacao=editar&acao=Alterar prioridade&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $solicitacao_editar_tabela_height; ?>&width=<? echo $solicitacao_editar_tabela_width; ?>&modal=true" class="thickbox">

							<img src="imagens/editar.png" id="botao_editar" align="absbottom" border="0" title="Alterar prioridade">
						</a>
					<? } ?>

				</td>

				<td style="text-align: right">
					<span class="label_solicitacao">Situação: </span><?php echo $row_solicitacao['situacao']; ?> |
					<span class="label_solicitacao">Status: </span><?php echo $row_solicitacao['status']; ?> |
					<span class="label_solicitacao">Questionamento para: </span><?php echo $row_solicitacao['status_questionamento']; ?>
				</td>

			</tr>
		</table>
	</div>

	<?php if ($row_solicitacao['prioridade_justificativa'] <> NULL) { ?>
		<div class="div_solicitacao_linhas3">
			<table cellspacing="0" cellpadding="0" width="100%">
				<tr>
					<td><span class="label_solicitacao">Justificativa da Prioridade Alta: </span><?php echo $row_solicitacao['prioridade_justificativa']; ?></td>
				</tr>
			</table>
		</div>
	<? } ?>

	<div class="div_solicitacao_linhas3">
		<table cellspacing="0" cellpadding="0" width="100%">
			<tr>
				<td style="padding-top: 5px; padding-bottom: 5px;">
					<!-- andamento -->
					<span class="label_solicitacao">Andamento: </span>
					criada
					<? if ($row_solicitacao['dt_recebimento'] != "") {
						echo " >> recebida";
					} ?>
					<? if ($row_solicitacao['situacao'] == "em análise") {
						echo " >> em análise";
					} ?>
					<? if ($row_solicitacao['situacao'] != "em análise" and $row_solicitacao['situacao'] != "recebida" and $row_solicitacao['situacao'] != "criada") {
						echo " >> analisada";
					} ?>
					<? if ($row_solicitacao['situacao'] == "em orçamento") {
						echo " >> em orçamento";
					} ?>
					<? if ($row_solicitacao['situacao'] != "em orçamento" and $row_solicitacao['dt_orcamento'] != "") {
						echo " >> orçada";
					} ?>
					<? if ($row_solicitacao['dt_aprovacao_reprovacao'] != "" and $row_solicitacao['situacao'] != "em orçamento") {
						echo " >> aprovada";
					} ?>
					<? if ($row_solicitacao['situacao'] == "em execução") {
						echo " >> em execução";
					} ?>
					<? if ($row_solicitacao['situacao'] != "em execução" and $row_solicitacao['dt_conclusao'] != "") {
						echo " >> executada";
					} ?>
					<? if ($row_solicitacao['situacao'] == "em testes") {
						echo " >> em testes";
					} ?>
					<? if ($row_solicitacao['situacao'] != "em testes" and $row_solicitacao['dt_conclusao_testes'] != "") {
						echo " >> testada";
					} ?>
					<? if ($row_solicitacao['situacao'] == "em validação") {
						echo " >> em validação";
					} ?>
					<? if ($row_solicitacao['situacao'] == "reprovada") {
						echo " >> reprovada";
					} ?>
					<? if ($row_solicitacao['situacao'] == "solucionada") {
						echo " >> solucionada";
					} ?>
					<!-- fim - andamento -->

					<div style="text-align:right; float: right">
						<!-- previsões -->
						<? require_once('funcao_formata_data.php'); ?>

						<? if ($row_solicitacao['situacao'] == "em análise" and $row_solicitacao['previsao_analise'] != "0000-00-00 00:00:00") { ?>
							<span class="label_solicitacao">Previsão da análise de solicitação: </span>
							<? echo formataDataPTG($row_solicitacao['previsao_analise_inicio']); ?> à <? echo formataDataPTG($row_solicitacao['previsao_analise']); ?>
							<? if (strtotime($row_solicitacao['previsao_analise_inicio']) < strtotime(date("Y-m-d H:i:s"))) { ?>
								| <span class="label_solicitacao">Atraso: </span>
								<?
								// dias_atraso ...
								$data_ini = strtotime($row_solicitacao['previsao_analise_inicio']);
								$data_final = strtotime(date("Y-m-d H:i:s"));

								$nDias   = ($data_final - $data_ini) / (3600 * 24);  // dias
								$nHoras = (($data_final - $data_ini) % (3600 * 24)) / 3600; // horas
								$nMinutos = ((($data_final - $data_ini) % (3600 * 24)) % 3600) / 60; // minutos

								echo sprintf('%02dd %02dh %02dm', $nDias, $nHoras, $nMinutos);
								// fim - dias_atraso ...
								?>
							<? } ?>
						<? } ?>

						<? if ($row_solicitacao['situacao'] == "em orçamento" and $row_solicitacao['previsao_analise_orcamento'] != "0000-00-00 00:00:00" and $row_solicitacao['orcamento'] == "") { ?>
							<span class="label_solicitacao">Previsão da análise de orçamento: </span>
							<? echo formataDataPTG($row_solicitacao['previsao_analise_orcamento_inicio']); ?> à <? echo formataDataPTG($row_solicitacao['previsao_analise_orcamento']); ?>
							<? if (strtotime($row_solicitacao['previsao_analise_orcamento_inicio']) < strtotime(date("Y-m-d H:i:s"))) { ?>
								| <span class="label_solicitacao">Atraso: </span>
								<?
								// dias_atraso ...
								$data_ini = strtotime($row_solicitacao['previsao_analise_orcamento_inicio']);
								$data_final = strtotime(date("Y-m-d H:i:s"));

								$nDias   = ($data_final - $data_ini) / (3600 * 24);  // dias
								$nHoras = (($data_final - $data_ini) % (3600 * 24)) / 3600; // horas
								$nMinutos = ((($data_final - $data_ini) % (3600 * 24)) % 3600) / 60; // minutos

								echo sprintf('%02dd %02dh %02dm', $nDias, $nHoras, $nMinutos);
								// fim - dias_atraso ...
								?>
							<? } ?>
						<? } ?>

						<? if ($row_solicitacao['situacao'] == "em orçamento" and $row_solicitacao['previsao_retorno_orcamento'] != "0000-00-00 00:00:00") { ?>
							<span class="label_solicitacao">Previsão de retorno sobre orçamento: </span>
							<? echo formataDataPTG($row_solicitacao['previsao_retorno_orcamento_inicio']); ?> à <? echo formataDataPTG($row_solicitacao['previsao_retorno_orcamento']); ?>
							<? if (strtotime($row_solicitacao['previsao_retorno_orcamento_inicio']) < strtotime(date("Y-m-d H:i:s"))) { ?>
								| <span class="label_solicitacao">Atraso: </span>
								<?
								// dias_atraso ...
								$data_ini = strtotime($row_solicitacao['previsao_retorno_orcamento_inicio']);
								$data_final = strtotime(date("Y-m-d H:i:s"));

								$nDias   = ($data_final - $data_ini) / (3600 * 24);  // dias
								$nHoras = (($data_final - $data_ini) % (3600 * 24)) / 3600; // horas
								$nMinutos = ((($data_final - $data_ini) % (3600 * 24)) % 3600) / 60; // minutos

								echo sprintf('%02dd %02dh %02dm', $nDias, $nHoras, $nMinutos);
								// fim - dias_atraso ...
								?>
							<? } ?>
						<? } ?>

						<? if ($row_solicitacao['situacao'] == "em execução" and $row_solicitacao['previsao_solucao'] != "0000-00-00 00:00:00") { ?>
							<span class="label_solicitacao">Previsão de execução: </span>
							<? echo formataDataPTG($row_solicitacao['previsao_geral_inicio']); ?> à <? echo formataDataPTG($row_solicitacao['previsao_geral']); ?><br>
							<? if (strtotime($row_solicitacao['previsao_geral_inicio']) < strtotime(date("Y-m-d H:i:s"))) { ?>
								| <span class="label_solicitacao">Atraso: </span>
								<?
								// dias_atraso ...
								$data_ini = strtotime($row_solicitacao['previsao_geral_inicio']);
								$data_final = strtotime(date("Y-m-d H:i:s"));

								$nDias   = ($data_final - $data_ini) / (3600 * 24);  // dias
								$nHoras = (($data_final - $data_ini) % (3600 * 24)) / 3600; // horas
								$nMinutos = ((($data_final - $data_ini) % (3600 * 24)) % 3600) / 60; // minutos

								echo sprintf('%02dd %02dh %02dm', $nDias, $nHoras, $nMinutos);
								// fim - dias_atraso ...
								?>
							<? } ?>

							<? echo formataDataPTG($row_solicitacao['previsao_solucao_inicio']); ?> à <? echo formataDataPTG($row_solicitacao['previsao_solucao']); ?><br>
							| <span class="label_solicitacao">Previsão geral: </span>
						<? } ?>

						<? if ($row_solicitacao['situacao'] == "em testes" and $row_solicitacao['previsao_testes'] != "0000-00-00 00:00:00") { ?>
							<span class="label_solicitacao">Previsão de testes: </span>
							<? echo formataDataPTG($row_solicitacao['previsao_testes_inicio']); ?> à <? echo formataDataPTG($row_solicitacao['previsao_testes']); ?>
							<? if (strtotime($row_solicitacao['previsao_testes_inicio']) < strtotime(date("Y-m-d H:i:s"))) { ?>
								| <span class="label_solicitacao">Atraso: </span>
								<?
								// dias_atraso ...
								$data_ini = strtotime($row_solicitacao['previsao_testes_inicio']);
								$data_final = strtotime(date("Y-m-d H:i:s"));

								$nDias   = ($data_final - $data_ini) / (3600 * 24);  // dias
								$nHoras = (($data_final - $data_ini) % (3600 * 24)) / 3600; // horas
								$nMinutos = ((($data_final - $data_ini) % (3600 * 24)) % 3600) / 60; // minutos

								echo sprintf('%02dd %02dh %02dm', $nDias, $nHoras, $nMinutos);
								// fim - dias_atraso ...
								?>
							<? } ?>
						<? } ?>

						<? if ($row_solicitacao['situacao'] == "em validação" and $row_solicitacao['previsao_validacao'] != "0000-00-00 00:00:00") { ?>
							<span class="label_solicitacao">Previsão de validação: </span>
							<? echo formataDataPTG($row_solicitacao['previsao_validacao_inicio']); ?> à <? echo formataDataPTG($row_solicitacao['previsao_validacao']); ?>
							<? if (strtotime($row_solicitacao['previsao_validacao_inicio']) < strtotime(date("Y-m-d H:i:s"))) { ?>
								| <span class="label_solicitacao">Atraso: </span>
								<?
								// dias_atraso ...
								$data_ini = strtotime($row_solicitacao['previsao_validacao_inicio']);
								$data_final = strtotime(date("Y-m-d H:i:s"));

								$nDias   = ($data_final - $data_ini) / (3600 * 24);  // dias
								$nHoras = (($data_final - $data_ini) % (3600 * 24)) / 3600; // horas
								$nMinutos = ((($data_final - $data_ini) % (3600 * 24)) % 3600) / 60; // minutos

								echo sprintf('%02dd %02dh %02dm', $nDias, $nHoras, $nMinutos);
								// fim - dias_atraso ...
								?>
							<? } ?>
						<? } ?>
						<!-- fim - previsões -->
					</div>

				</td>
			</tr>
		</table>
	</div>

	<? if ($totalRows_solicitacao_devolucao > 0) { ?>
		<div class="div_solicitacao_linhas3">
			<table cellspacing="0" cellpadding="0" width="100%">
				<tr>

					<td style="text-align: left;">

						<span class="label_solicitacao">Devolução por: </span>

						<? $contador_solicitacao_devolucao = 0; ?>
						<? do { ?>
							<? $contador_solicitacao_devolucao++; ?>
							<? echo $row_solicitacao_devolucao['solicitacao_tipo_devolucao_titulo']; ?>: <strong><? echo $row_solicitacao_devolucao['retorno']; ?></strong>
							<? if ($totalRows_solicitacao_devolucao <> $contador_solicitacao_devolucao) { ?> | <? } ?>

						<? } while ($row_solicitacao_devolucao = mysql_fetch_assoc($solicitacao_devolucao)); ?>

					</td>
				</tr>
			</table>
		</div>
	<? } ?>

	<!-- Botões ====================================================================================================================================================== -->
	<? if ($row_solicitacao['situacao'] != "solucionada" and $row_solicitacao['situacao'] != "reprovada") { ?>
		<div class="div_solicitacao_linhas4" id="botoes">
			<table cellspacing="0" cellpadding="0" width="100%">
				<tr>

					<td align="left">

						<!-- recebida -->
						<? if ($row_solicitacao['situacao'] == "recebida" or $row_solicitacao['situacao'] == "aprovada") { ?>

							<?
							if (
								$row_usuario['controle_solicitacao'] == 'Y' or
								$row_solicitacao['id_operador'] == $row_usuario['IdUsuario']
							) {
							?>

								<a href="solicitacao_editar_tabela.php?id_solicitacao=<? echo $row_solicitacao['id']; ?>&interacao=<? echo $row_solicitacao['interacao']; ?>&situacao=recebida&acao=Colocar em análise&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $solicitacao_editar_tabela_height; ?>&width=<? echo $solicitacao_editar_tabela_width; ?>&modal=true" class="thickbox" id="botao_geral2">Colocar em análise</a>

							<? } ?>

						<? } ?>
						<!-- fim - recebida -->


						<!-- Concluir execução -->
						<?
						if (
							// executante =======================================================================================================================================
							($row_usuario['solicitacao_executante'] == "Y" and $row_solicitacao['id_executante'] == $row_usuario['IdUsuario'])
							// fim -executante =================================================================================================================================
							or
							// analista de orçamento ==========================================================================================================================
							($row_solicitacao['id_analista_orcamento'] == $row_usuario['IdUsuario'])
							// fim - analista de orçamento ====================================================================================================================
						) { ?>

							<?
							if (
								$row_solicitacao['situacao'] == "em orçamento" and (($row_solicitacao['orcamento'] == 0 or ($row_solicitacao['orcamento'] > 0 and $row_solicitacao['orcamento_os'] <> NULL))) or
								$row_solicitacao['situacao'] == "em execução"
							) { ?>

								<a href="solicitacao_editar_tabela.php?id_solicitacao=<? echo $row_solicitacao['id']; ?>&interacao=<? echo $row_solicitacao['interacao']; ?>&situacao=geral&acao=Concluir execução&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $solicitacao_editar_tabela_height; ?>&width=<? echo $solicitacao_editar_tabela_width; ?>&modal=true" class="thickbox" id="botao_geral2">Concluir execução</a>

							<? } ?>

						<? } ?>
						<!-- fim - Concluir execução -->


						<!-- em testes -->
						<? if ($row_solicitacao['situacao'] == "em testes") { ?>

							<? if ($row_usuario['solicitacao_testador'] == "Y" and $row_solicitacao['id_testador'] == $row_usuario['IdUsuario']) { // testador 
							?>

								<a href="solicitacao_editar_tabela.php?id_solicitacao=<? echo $row_solicitacao['id']; ?>&interacao=<? echo $row_solicitacao['interacao']; ?>&situacao=em testes&acao=Concluir testes&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $solicitacao_editar_tabela_height; ?>&width=<? echo $solicitacao_editar_tabela_width; ?>&modal=true" class="thickbox" id="botao_geral2">Concluir testes</a>

							<? } // fim - testador 
							?>

						<? } ?>
						<!-- fim - em testes -->


						<!-- em validação -->
						<? if ($row_solicitacao['situacao'] == "em validação") { ?>

							<? if ($row_solicitacao['id_usuario_responsavel'] == $row_usuario['IdUsuario']) { // solicitante 
							?>

								<a href="solicitacao_editar_tabela.php?id_solicitacao=<? echo $row_solicitacao['id']; ?>&interacao=<? echo $row_solicitacao['interacao']; ?>&situacao=em validação&acao=Concluir validação&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $solicitacao_editar_tabela_height; ?>&width=<? echo $solicitacao_editar_tabela_width; ?>&modal=true" class="thickbox" id="botao_geral2">Concluir validação</a>

							<? } // fim - solicitante 
							?>

						<? } ?>
						<!-- fim - em validação -->


						<!-- Devolver ===================================================================================================================================== -->
						<?
						if (
							// controle_solicitacao / operador =======================================================================================================================================
							(($row_usuario['controle_solicitacao'] == 'Y' or $row_solicitacao['id_operador'] == $row_usuario['IdUsuario']) and
								($row_solicitacao['situacao'] == "em orçamento" or
									$row_solicitacao['situacao'] == "em execução" or
									$row_solicitacao['situacao'] == "em testes" or
									$row_solicitacao['situacao'] == "em validação"))
							// fim - controle_solicitacao / operador =================================================================================================================================
							or
							// analista de orçamento ==========================================================================================================================
							(($row_usuario['solicitacao_executante'] == 'Y' and $row_solicitacao['id_analista_orcamento'] == $row_usuario['IdUsuario']) and
								($row_solicitacao['situacao'] == "em orçamento"))
							// fim - analista de orçamento ====================================================================================================================
							or
							// executante =====================================================================================================================================
							(($row_usuario['solicitacao_executante'] == 'Y' and $row_solicitacao['id_executante'] == $row_usuario['IdUsuario']) and ($row_solicitacao['situacao'] == "em execução"))
							// fim - executante ===============================================================================================================================
							or
							// testador =======================================================================================================================================
							(($row_usuario['solicitacao_testador'] == 'Y' and $row_solicitacao['id_testador'] == $row_usuario['IdUsuario']) and ($row_solicitacao['situacao'] == "em testes"))
							// fim - testador =================================================================================================================================
							or
							// solicitante ====================================================================================================================================
							(($row_solicitacao['id_usuario_responsavel'] == $row_usuario['IdUsuario']) and ($row_solicitacao['situacao'] == "em validação"))
							// fim - solicitante ==============================================================================================================================
						) { ?>

							<a href="solicitacao_editar_tabela.php?id_solicitacao=<? echo $row_solicitacao['id']; ?>&interacao=<? echo $row_solicitacao['interacao']; ?>&situacao=geral&acao=Devolver&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $solicitacao_editar_tabela_height; ?>&width=<? echo $solicitacao_editar_tabela_width; ?>&modal=true" class="thickbox" id="botao_geral2">Devolver</a>

						<? } ?>
						<!-- fim - Devolver =============================================================================================================================== -->


						<!-- Encaminhar =================================================================================================================================== -->
						<?
						if (
							// controle_solicitacao / operador =======================================================================================================================================
							(($row_usuario['controle_solicitacao'] == 'Y' or $row_solicitacao['id_operador'] == $row_usuario['IdUsuario']) and
								($row_solicitacao['situacao'] == "analisada" or
									$row_solicitacao['situacao'] == "em orçamento" or
									$row_solicitacao['situacao'] == "aprovada" or
									$row_solicitacao['situacao'] == "em execução" or
									$row_solicitacao['situacao'] == "executada" or
									$row_solicitacao['situacao'] == "em testes"))
							// fim - controle_solicitacao / operador =================================================================================================================================
							or
							// analista de orçamento ==========================================================================================================================
							(($row_usuario['solicitacao_executante'] == 'Y' and $row_solicitacao['id_analista_orcamento'] == $row_usuario['IdUsuario']) and
								($row_solicitacao['situacao'] == "analisada" or
									$row_solicitacao['situacao'] == "em orçamento"))
							// fim - analista de orçamento ====================================================================================================================
							or
							// executante =====================================================================================================================================
							(($row_usuario['solicitacao_executante'] == 'Y' and $row_solicitacao['id_executante'] == $row_usuario['IdUsuario']) and (
								($row_solicitacao['situacao'] == "aprovada" and $row_solicitacao['status_recusa'] != "1") or
								$row_solicitacao['situacao'] == "em execução" or
								$row_solicitacao['situacao'] == "executada"))
							// fim - executante ===============================================================================================================================
							or
							// testador =======================================================================================================================================
							(($row_usuario['solicitacao_testador'] == 'Y' and $row_solicitacao['id_testador'] == $row_usuario['IdUsuario']) and ($row_solicitacao['situacao'] == "em testes"))
							// fim - testador =================================================================================================================================
						) { ?>

							<a href="solicitacao_editar_tabela.php?id_solicitacao=<? echo $row_solicitacao['id']; ?>&interacao=<? echo $row_solicitacao['interacao']; ?>&situacao=<? echo $row_solicitacao['situacao']; ?>&acao=Encaminhar&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $solicitacao_editar_tabela_height; ?>&width=<? echo $solicitacao_editar_tabela_width; ?>&modal=true" class="thickbox" id="botao_geral2">Encaminhar</a>

							<a href="painel.php" target="_blank" id="botao_geral2">Painel</a>

						<? } ?>
						<!-- fim - Encaminhar ============================================================================================================================= -->


						<!-- Alterar previsão ============================================================================================================================= -->
						<?
						$alterar_previsao_status = 0;
						$alterar_previsao_envolvido = "";

						// controle_solicitacao / operador =======================================================================================================================================
						if (($row_usuario['controle_solicitacao'] == 'Y' or $row_solicitacao['id_operador'] == $row_usuario['IdUsuario']) and
							($row_solicitacao['situacao'] == "em análise")
						) {
							$alterar_previsao_status = 1;
							$alterar_previsao_envolvido = "operador";
						}
						// fim - controle_solicitacao / operador =================================================================================================================================

						// analista de orçamento ==========================================================================================================================
						if (($row_usuario['solicitacao_executante'] == 'Y' and $row_solicitacao['id_analista_orcamento'] == $row_usuario['IdUsuario']) and
							($row_solicitacao['situacao'] == "em orçamento" and
								$row_solicitacao['orcamento'] == "")
						) {
							$alterar_previsao_status = 1;
							$alterar_previsao_envolvido = "executante";
						}
						// fim - analista de orçamento ====================================================================================================================

						// executante =====================================================================================================================================
						if (($row_usuario['solicitacao_executante'] == 'Y' and $row_solicitacao['id_executante'] == $row_usuario['IdUsuario']) and ($row_solicitacao['situacao'] == "em execução")
						) {
							$alterar_previsao_status = 1;
							$alterar_previsao_envolvido = "executante";
						}
						// fim - executante ===============================================================================================================================

						// testador =======================================================================================================================================
						if (($row_usuario['solicitacao_testador'] == 'Y' and $row_solicitacao['id_testador'] == $row_usuario['IdUsuario']) and ($row_solicitacao['situacao'] == "em testes")
						) {
							$alterar_previsao_status = 1;
							$alterar_previsao_envolvido = "testador";
						}
						// fim - testador =================================================================================================================================

						// solicitante ====================================================================================================================================
						if (($row_solicitacao['id_usuario_responsavel'] == $row_usuario['IdUsuario']) and (
								($row_solicitacao['situacao'] == "em orçamento" and  $row_solicitacao['orcamento'] != ""))
						) {
							$alterar_previsao_status = 1;
							$alterar_previsao_envolvido = "solicitante";
						}
						// fim - solicitante ==============================================================================================================================
						?>

						<? if ($alterar_previsao_status == 1) { ?>

							<? if ($row_solicitacao['prioridade'] == "Alta") {
								$alteracao_previsao_prioridade = "alta";
							} ?>
							<? if ($row_solicitacao['prioridade'] == "Média") {
								$alteracao_previsao_prioridade = "media";
							} ?>
							<? if ($row_solicitacao['prioridade'] == "Baixa") {
								$alteracao_previsao_prioridade = "baixa";
							} ?>

							<? $alterar_previsao = "alterar_previsao_" . $alterar_previsao_envolvido; // pega na tabela 'solicitacao' o campo atual 
							?>
							<? $alteracao_previsao_qtde = "alteracao_previsao_qtde_" . $alterar_previsao_envolvido . "_" . $alteracao_previsao_prioridade; // pega na tabela 'parametros' o campo atual 
							?>

							<? if (
								($row_solicitacao[$alterar_previsao] < $row_parametros[$alteracao_previsao_qtde]) or // se está dentro da 'quantidade'
								($row_parametros[$alteracao_previsao_qtde] == "")
							) { // se campo está desativado (vazio) na tabela de parametros 
							?>

								<a href="solicitacao_editar_tabela.php?id_solicitacao=<? echo $row_solicitacao['id']; ?>&interacao=<? echo $row_solicitacao['interacao']; ?>&situacao=geral&acao=Alterar previsão&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $solicitacao_editar_tabela_height; ?>&width=<? echo $solicitacao_editar_tabela_width; ?>&modal=true" class="thickbox" id="botao_geral2">
									Alterar previsão
									<? if ($row_parametros[$alteracao_previsao_qtde] != "") { ?>
										(<? echo $row_solicitacao[$alterar_previsao]; ?>)
									<? } ?>
								</a>

							<? } else { // se estourou a quantidade, desabilita o botão 
							?>

								<div id="botao_geral_desativado">Alterar previsão (<? echo $row_solicitacao[$alterar_previsao]; ?>)</div>

							<? } ?>

						<? } ?>
						<!-- fim - Alterar previsão ======================================================================================================================= -->


						<!-- Aprovar ====================================================================================================================================== -->
						<?
						if (
							$row_usuario['controle_solicitacao'] == 'Y' or
							$row_solicitacao['id_operador'] == $row_usuario['IdUsuario']
						) {
						?>

							<? if (
								$row_solicitacao['situacao'] == "recebida" or
								$row_solicitacao['situacao'] == "em análise" or
								$row_solicitacao['situacao'] == "analisada" or
								$row_solicitacao['situacao'] == "em orçamento"
							) { ?>

								<a href="solicitacao_editar_tabela.php?id_solicitacao=<? echo $row_solicitacao['id']; ?>&interacao=<? echo $row_solicitacao['interacao']; ?>&situacao=geral&acao=Aprovar&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $solicitacao_editar_tabela_height; ?>&width=<? echo $solicitacao_editar_tabela_width; ?>&modal=true" class="thickbox" id="botao_geral2">Aprovar</a>

							<? } ?>
						<? } ?>
						<!-- fim - Aprovar ================================================================================================================================ -->


						<!-- Postar orçamento ============================================================================================================================= -->
						<?
						if (
							// analista de orçamento ==========================================================================================================================
							(($row_usuario['solicitacao_executante'] == 'Y' and $row_solicitacao['id_analista_orcamento'] == $row_usuario['IdUsuario']) and
								($row_solicitacao['situacao'] == "em orçamento" or
									$row_solicitacao['situacao'] == "analisada"))
							// fim - analista de orçamento ====================================================================================================================
							or
							// executante =====================================================================================================================================
							(($row_usuario['solicitacao_executante'] == 'Y' and $row_solicitacao['id_executante'] == $row_usuario['IdUsuario']) and ($row_solicitacao['situacao'] == "em execução"))
							// fim - executante ===============================================================================================================================
						) { ?>

							<a href="solicitacao_editar_tabela.php?id_solicitacao=<? echo $row_solicitacao['id']; ?>&interacao=<? echo $row_solicitacao['interacao']; ?>&situacao=geral&acao=Postar orçamento&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $solicitacao_editar_tabela_height; ?>&width=<? echo $solicitacao_editar_tabela_width; ?>&modal=true" class="thickbox" id="botao_geral2">Postar orçamento</a>

						<? } ?>
						<!-- fim - Postar orçamento ======================================================================================================================= -->


						<!-- Solicitar orçamento ========================================================================================================================== -->
						<?
						if (
							$row_usuario['controle_solicitacao'] == 'Y' or
							$row_solicitacao['id_operador'] == $row_usuario['IdUsuario']
						) {
						?>

							<a href="solicitacao_editar_tabela.php?id_solicitacao=<? echo $row_solicitacao['id']; ?>&interacao=<? echo $row_solicitacao['interacao']; ?>&situacao=geral&acao=Solicitar orçamento&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $solicitacao_editar_tabela_height; ?>&width=<? echo $solicitacao_editar_tabela_width; ?>&modal=true" class="thickbox" id="botao_geral2">Solicitar orçamento</a>

						<? } ?>
						<!-- fim - Solicitar orçamento ==================================================================================================================== -->


						<!-- Concluir/Reprovar ============================================================================================================================ -->
						<?
						if (
							$row_usuario['controle_solicitacao'] == 'Y' or
							$row_solicitacao['id_operador'] == $row_usuario['IdUsuario']
						) {
						?>

							<a href="solicitacao_editar_tabela.php?id_solicitacao=<? echo $row_solicitacao['id']; ?>&interacao=<? echo $row_solicitacao['interacao']; ?>&situacao=geral&acao=Concluir&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $solicitacao_editar_tabela_height; ?>&width=<? echo $solicitacao_editar_tabela_width; ?>&modal=true" class="thickbox" id="botao_geral2">Concluir</a>

							<a href="solicitacao_editar_tabela.php?id_solicitacao=<? echo $row_solicitacao['id']; ?>&interacao=<? echo $row_solicitacao['interacao']; ?>&situacao=geral&acao=Reprovar&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $solicitacao_editar_tabela_height; ?>&width=<? echo $solicitacao_editar_tabela_width; ?>&modal=true" class="thickbox" id="botao_geral2">Reprovar</a>

						<? } // fim administrador 
						?>
						<!-- fim - Concluir/Reprovar ====================================================================================================================== -->

						<!-- Desmembrar =================================================================================================================================== -->
						<? if (
							$row_usuario['controle_solicitacao'] == 'Y' or
							$row_solicitacao['id_testador'] == $row_usuario['IdUsuario']
						) { ?>

							<? if ($row_solicitacao['situacao'] != "criada") { ?>

								<a href="solicitacao_gerar.php?solicitacao_desmembrada=<? echo $row_solicitacao['id']; ?>" id="botao_geral2" style="width: 90px;">Desmembrar</a>

							<? } ?>

						<? } ?>
						<!-- fim - Desmembrar ============================================================================================================================= -->


						<!-- Questionar =================================================================================================================================== -->
						<? if ($row_solicitacao['situacao'] != "criada") { ?>

							<a href="solicitacao_editar_tabela.php?id_solicitacao=<? echo $row_solicitacao['id']; ?>&interacao=<? echo $row_solicitacao['interacao']; ?>&situacao=&acao=Questionar&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $solicitacao_editar_tabela_height; ?>&width=<? echo $solicitacao_editar_tabela_width; ?>&modal=true" class="thickbox" id="botao_geral2">Questionar</a>

						<? } ?>
						<!-- fim - Questionar ============================================================================================================================= -->

						<!-- anexos -->
						<a href="solicitacao_editar_upload.php?id_solicitacao=<? echo $row_solicitacao['id']; ?>&situacao=&acao=Arquivos em  anexo&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $solicitacao_editar_tabela_height; ?>&width=<? echo $solicitacao_editar_tabela_width; ?>&modal=true" class="thickbox" id="botao_geral2">Arquivos em anexo (<? echo $totalRows_arquivos_anexos; ?>)</a>
						<!-- fim - anexos -->

						<!-- Registrar reclamação ========================================================================================================================================= -->
						<a href="suporte_gerar.php?tipo_suporte=r&inloco=n&cobranca=n&cliente=<? echo $row_solicitacao['codigo_empresa']; ?>6&contrato=<? echo $row_solicitacao['contrato']; ?>&reclamacao_solicitacao=<? echo $row_solicitacao['id']; ?>" id="botao_geral2">Registrar reclamação</a>
						<!-- fim - Registrar reclamação =================================================================================================================================== -->
					</td>

					<td align="right" style="color:#F00; font-weight:bold;">

						<!-- Alterar previsão SIM-NAO ===================================================================================================================== -->
						<?
						if (
							// proposta de alteração para o solicitante =======================================================================================================
							(($row_solicitacao['previsao_proposta'] != "" and $row_solicitacao['id_usuario_responsavel'] == $row_usuario['IdUsuario']) and
								($row_solicitacao['situacao'] == "em análise" or
									$row_solicitacao['situacao'] == "em orçamento" or
									$row_solicitacao['situacao'] == "em execução" or
									$row_solicitacao['situacao'] == "em testes"))
							// fim - proposta de alteração para o solicitante =================================================================================================
						) { ?>

							Alterar previsão ?
							<br>
							<div style="float:right">
								<a href="solicitacao_editar_tabela.php?id_solicitacao=<? echo $row_solicitacao['id']; ?>&interacao=<? echo $row_solicitacao['interacao']; ?>&situacao=<? echo $row_solicitacao['situacao']; ?>&acao=Alterar previsão&resposta=sim&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $solicitacao_editar_tabela_height; ?>&width=<? echo $solicitacao_editar_tabela_width; ?>&modal=true&MM_update=form" id="botao_geral2" name="botao_sim">Sim</a>

								<a href="solicitacao_editar_tabela.php?id_solicitacao=<? echo $row_solicitacao['id']; ?>&interacao=<? echo $row_solicitacao['interacao']; ?>&situacao=<? echo $row_solicitacao['situacao']; ?>&acao=Alterar previsão&resposta=nao&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $solicitacao_editar_tabela_height; ?>&width=<? echo $solicitacao_editar_tabela_width; ?>&modal=true" class="thickbox" id="botao_geral2">Não</a>
							</div>
						<? } ?>
						<!-- fim - Alterar previsão SIM-NAO =============================================================================================================== -->

						<!-- Aceitar/Recusar ============================================================================================================================== -->
						<?
						if (
							(

								// criada ----------------------------------------------------------------------------
								$row_solicitacao['situacao'] == "criada" and ($row_usuario['controle_solicitacao'] == "Y")
								// fim - criada ----------------------------------------------------------------------------

							) or (

								// analisada ----------------------------------------------------------------------------
								$row_solicitacao['situacao'] == "analisada" and (

									(
										($row_usuario['solicitacao_executante'] == "Y" and $row_solicitacao['id_analista_orcamento'] == $row_usuario['IdUsuario']) // analista de orçamento logado
										and ($row_solicitacao['status_recusa'] != "1" and
											$row_solicitacao['previsao_analise_orcamento'] == "0000-00-00 00:00:00" and $row_solicitacao['id_analista_orcamento'] != "" // solicitado analista
										)) or (

										($row_usuario['controle_solicitacao'] == "Y" or $row_solicitacao['id_operador'] == $row_usuario['IdUsuario']) and
										$row_solicitacao['status_recusa'] == "1"))
								// fim - analisada ----------------------------------------------------------------------------

							) or (

								// aprovada ------------------------------------------------------------------------------
								$row_solicitacao['situacao'] == "aprovada" and (
									(
										($row_usuario['solicitacao_executante'] == "Y" and $row_solicitacao['id_executante'] == $row_usuario['IdUsuario']) // executante logado
										and ($row_solicitacao['status_recusa'] != "1" and
											$row_solicitacao['previsao_solucao'] == "0000-00-00 00:00:00" and $row_solicitacao['id_executante'] != "" // solicitado executante
										)) or (
										($row_usuario['controle_solicitacao'] == "Y" or $row_solicitacao['id_operador'] == $row_usuario['IdUsuario']) and
										$row_solicitacao['status_recusa'] == "1"))
								// fim - aprovada ------------------------------------------------------------------------------	

							) or (

								// executada -----------------------------------------------------------------------------	
								$row_solicitacao['situacao'] == "executada" and (
									(
										($row_usuario['solicitacao_testador'] == "Y" and $row_solicitacao['id_testador'] == $row_usuario['IdUsuario']) // testador logado
										and ($row_solicitacao['status_recusa'] != "1" and
											$row_solicitacao['previsao_testes'] == "0000-00-00 00:00:00" and $row_solicitacao['id_testador'] != "" // solicitado teste
										)) or (

										($row_usuario['solicitacao_executante'] == "Y" and $row_solicitacao['id_executante'] == $row_usuario['IdUsuario']) and  // executante logado
										$row_solicitacao['status_recusa'] == "1"))
								// fim - executada -----------------------------------------------------------------------------		

							) or (

								($row_solicitacao['status'] == "devolvida para operador" and  ($row_usuario['controle_solicitacao'] == 'Y' or $row_solicitacao['id_operador'] == $row_usuario['IdUsuario'])) or // devolução para operador
								($row_solicitacao['status'] == "devolvida para executante" and $row_solicitacao['id_executante'] == $row_usuario['IdUsuario']) or // devolução para executante
								($row_solicitacao['status'] == "devolvida para testador" and $row_solicitacao['id_testador'] == $row_usuario['IdUsuario']) // devolução para testador
							)
						) {
						?>

							<div style="float:right; margin-left: 5px;">
								<a href="solicitacao_editar_tabela.php?id_solicitacao=<? echo $row_solicitacao['id']; ?>&interacao=<? echo $row_solicitacao['interacao']; ?>&situacao=<? echo $row_solicitacao['situacao']; ?>&acao=Aceitar&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $solicitacao_editar_tabela_height; ?>&width=<? echo $solicitacao_editar_tabela_width; ?>&modal=true" class="thickbox" id="botao_geral2">Aceitar</a>

								<a href="solicitacao_editar_tabela.php?id_solicitacao=<? echo $row_solicitacao['id']; ?>&interacao=<? echo $row_solicitacao['interacao']; ?>&situacao=<? echo $row_solicitacao['situacao']; ?>&acao=Recusar&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $solicitacao_editar_tabela_height; ?>&width=<? echo $solicitacao_editar_tabela_width; ?>&modal=true" class="thickbox" id="botao_geral2">Recusar</a>
							</div>

						<? } ?>
						<!-- fim - Aceitar/Recusar ======================================================================================================================== -->

						<!-- em orçamento -->
						<? if ($row_solicitacao['situacao'] == "em orçamento") { ?>

							<? if ($row_solicitacao['dt_orcamento'] != "") { ?>

								Aguardando aprovação/reprovação do solicitante

								<? if ($row_solicitacao['id_usuario_responsavel'] == $row_usuario['IdUsuario']) { ?>

									- Aprovar/Reprovar orçamento?
									<br>
									<div style="float:right">
										<a href="solicitacao_editar_tabela.php?id_solicitacao=<? echo $row_solicitacao['id']; ?>&interacao=<? echo $row_solicitacao['interacao']; ?>&situacao=em orçamento&acao=Aprovar&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $solicitacao_editar_tabela_height; ?>&width=<? echo $solicitacao_editar_tabela_width; ?>&modal=true" class="thickbox" id="botao_geral2">Aprovar</a>

										<a href="solicitacao_editar_tabela.php?id_solicitacao=<? echo $row_solicitacao['id']; ?>&interacao=<? echo $row_solicitacao['interacao']; ?>&situacao=em orçamento&acao=Reprovar&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $solicitacao_editar_tabela_height; ?>&width=<? echo $solicitacao_editar_tabela_width; ?>&modal=true" class="thickbox" id="botao_geral2">Reprovar</a>
									</div>

								<? } ?>

							<? } ?>

						<? } ?>
						<!-- fim - em orçamento -->

						<!-- Mensagens ==================================================================================================================================== -->
						<? if ($row_solicitacao['situacao'] == "criada") { ?>

							Aguardando aceitação do operador

						<? } ?>

						<? if ($row_solicitacao['situacao'] == "analisada") { ?>
							<? if (
								$row_solicitacao['previsao_analise_orcamento'] == "0000-00-00 00:00:00" and
								$row_solicitacao['id_analista_orcamento'] != "" and
								$row_solicitacao['status_recusa'] != "1"
							) { ?>

								Aguardando aceitação do analista de orçamento

							<? } ?>
						<? } ?>

						<? if ($row_solicitacao['situacao'] == "aprovada") { ?>
							<? if (
								$row_solicitacao['previsao_solucao'] == "0000-00-00 00:00:00" and
								$row_solicitacao['id_executante'] != "" and
								$row_solicitacao['status_recusa'] != "1"
							) { ?>

								Aguardando aceitação do executante

							<? } ?>
						<? } ?>

						<? if ($row_solicitacao['situacao'] == "executada") { ?>
							<? if (
								$row_solicitacao['previsao_testes'] == "0000-00-00 00:00:00" and
								$row_solicitacao['id_testador'] != "" and
								$row_solicitacao['status_recusa'] != "1"
							) { ?>

								Aguardando aceitação do testador

							<? } ?>
						<? } ?>

						<?
						if (
							$row_solicitacao['status'] == "devolvida para operador" or
							$row_solicitacao['status'] == "devolvida para executante" or
							$row_solicitacao['status'] == "devolvida para testador"
						) { ?>

							Aguardando aceitação de devolução

						<? } ?>

						<?
						if ($row_solicitacao['status_recusa'] == "1") { ?>
							Aguardando aceitação de recusa
						<? } ?>
						<!-- fim - Mensagens ============================================================================================================================== -->

					</td>

				</tr>
			</table>
		</div>
	<? } ?>

	<? if ($row_solicitacao['situacao'] == "solucionada" or $row_solicitacao['situacao'] == "reprovada"){ ?>
		<div class="div_solicitacao_linhas4">
			<table cellspacing="0" cellpadding="0" width="100%">
				<tr>

					<td align="left">

						<!-- Questionar =================================================================================================================================== -->
						<a href="solicitacao_editar_tabela.php?id_solicitacao=<? echo $row_solicitacao['id']; ?>&interacao=<? echo $row_solicitacao['interacao']; ?>&situacao=&acao=Questionar&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $solicitacao_editar_tabela_height; ?>&width=<? echo $solicitacao_editar_tabela_width; ?>&modal=true" class="thickbox" id="botao_geral2">Questionar</a>
						<!-- fim - Questionar ============================================================================================================================= -->

						<!-- anexos -->
						<a href="solicitacao_editar_upload.php?id_solicitacao=<? echo $row_solicitacao['id']; ?>&situacao=&acao=Arquivos em  anexo&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $solicitacao_editar_tabela_height; ?>&width=<? echo $solicitacao_editar_tabela_width; ?>&modal=true"  class="thickbox" id="botao_geral2">Arquivos em anexo (<? echo $totalRows_arquivos_anexos; ?>)</a>
						<!-- fim - anexos -->

					</td>

				</tr>
			</table>
		</div>
	<? } ?>
	<!-- fim - Botões ================================================================================================================================================ -->

	<!-- Implementação -->
	<? if ($row_solicitacao['tipo'] == "Implementação") { ?>
		<div id="implementacao_mensagem" style="border: 2px solid #06C; padding: 5px; margin-bottom: 5px;">

			<? if ($row_solicitacao['implementacao_mensagem_sim_nao'] == "s" or $row_solicitacao['implementacao_mensagem_sim_nao'] == "") { ?>
				<span class="label_solicitacao">A solicitação para implementação será realizada na versão desenvolvimento do sistema.</span>
			<? } ?>
			<? if ($row_solicitacao['implementacao_mensagem_sim_nao'] == "n") { ?>
				<span class="label_solicitacao">Solicitado a implementação na versão estável ou versão desejada.</span>
			<? } ?>

			<? if (
				($row_solicitacao['id_usuario_responsavel'] == $row_usuario['IdUsuario']) and // se é solicitante da solicitação
				($row_solicitacao['situacao'] != "reprovada" and $row_solicitacao['situacao'] != "solucionada")
			) {
			?>
				<a href="solicitacao_editar_tabela.php?id_solicitacao=<? echo $row_solicitacao['id']; ?>&interacao=<? echo $row_solicitacao['interacao']; ?>&situacao=editar&acao=Alterar versão da implementação&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $solicitacao_editar_tabela_height; ?>&width=<? echo $solicitacao_editar_tabela_width; ?>&modal=true" class="thickbox">
					<img src="imagens/editar.png" id="botao_editar" align="absbottom" border="0" title="Alterar versão da implementação">
				</a>
			<? } ?>

		</div>
	<? } ?>
	<!-- Implementação -->

	<!-- reclamacao_solicitacao -->
	<? if ($totalRows_reclamacao_solicitacao > 0) { ?>
		<div class="div_solicitacao_linhas4">
			<table cellspacing="0" cellpadding="0" width="100%">
				<tr>
					<td style="text-align: left">Reclamações vinculadas: </span>
						<!-- tabela -->
						<table width="100%" border="0" cellspacing="0" cellpadding="0" style="padding: 5px;">
							<tr bgcolor="#F1F1F1">
								<td style="padding: 5px;" width="70"><strong>Número</strong></td>
								<td style="padding: 5px;" width="180"><strong>Data</strong></td>
								<td style="padding: 5px;" width="100"><strong>Status</strong></td>
								<td style="padding: 5px;" width="300"><strong>Título</strong></td>
								<td style="padding: 5px;"><strong>Ações</strong></td>
							</tr>

							<? $contador_reclamacao_solicitacao = 0; ?>

							<?php do { ?>
								<tr bgcolor="<? if (($contador_reclamacao_solicitacao % 2) == 1) {
													echo "#F1F1F1";
												} else {
													echo "#FFFFFF";
												} ?>">
									<td style="padding: 5px;"><?php echo $row_reclamacao_solicitacao['id']; ?></td>
									<td style="padding: 5px;"><? echo date('d-m-Y  H:i', strtotime($row_reclamacao_solicitacao['data_suporte'])); ?></td>
									<td style="padding: 5px;"><?php echo $row_reclamacao_solicitacao['situacao']; ?></td>
									<td style="padding: 5px;"><?php echo $row_reclamacao_solicitacao['titulo']; ?></td>
									<td style="padding: 5px;"><a href="suporte_editar.php?id_suporte=<? echo $row_reclamacao_solicitacao['id']; ?>&padrao=sim" target="_blank" id="botao_geral2" style="width: 70px;">Abrir</a></td>
								</tr>
								<? $contador_reclamacao_solicitacao = $contador_reclamacao_solicitacao + 1; ?>
							<?php } while ($row_reclamacao_solicitacao = mysql_fetch_assoc($reclamacao_solicitacao)); ?>

						</table>
						<!-- fim - tabela -->
					</td>
				</tr>
			</table>
		</div>
	<? } ?>
	<!-- fim - reclamacao_solicitacao -->

	<div class="div_solicitacao_linhas3">
		<table cellspacing="0" cellpadding="0" width="100%">
			<tr>

				<td width="200" valign="top">

					<? if ($row_solicitacao['situacao'] != "solucionada" and $row_solicitacao['situacao'] != "reprovada") { ?>

						<!-- solicitante leu em -->
						<? echo $row_solicitacao['usuario_responsavel']; ?>
						<!-- alterar solicitante -->
						<? if ($row_usuario['controle_solicitacao'] == 'Y' or $row_solicitacao['id_operador'] == $row_usuario['IdUsuario']) { ?>
							<a href="solicitacao_editar_tabela.php?id_solicitacao=<? echo $row_solicitacao['id']; ?>&interacao=<? echo $row_solicitacao['interacao']; ?>&situacao=editar&acao=Alterar solicitante&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $solicitacao_editar_tabela_height; ?>&width=<? echo $solicitacao_editar_tabela_width; ?>&modal=true" class="thickbox">
								<img src="imagens/editar.png" id="botao_editar" align="absbottom" border="0" title="Alterar solicitante">
							</a>
						<? } ?>
						<!-- fim - alterar solicitante -->

						<br>
						<span class="label_solicitacao">Solicitante leu em:</span>
						<br>
						<? if ($row_solicitacao['solicitante_leu'] != "") {
							echo date('d-m-Y - H:i:s', strtotime($row_solicitacao['solicitante_leu']));
						} else {
							echo "não leu";
						} ?>
						<!-- fim - solicitante leu em -->

						<!-- operador leu em -->
						<? if ($row_solicitacao['id_operador'] != "") { ?>
							<br><br>
							<? echo $row_solicitacao['nome_operador']; ?>
							<!-- alterar operador -->
							<? if ($row_usuario['controle_solicitacao'] == 'Y' or $row_usuario['controle_solicitacao'] == 'Y' or $row_solicitacao['id_operador'] == $row_usuario['IdUsuario']) { ?>
								<a href="solicitacao_editar_tabela.php?id_solicitacao=<? echo $row_solicitacao['id']; ?>&interacao=<? echo $row_solicitacao['interacao']; ?>&situacao=editar&acao=Alterar operador&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $solicitacao_editar_tabela_height; ?>&width=<? echo $solicitacao_editar_tabela_width; ?>&modal=true" class="thickbox">
									<img src="imagens/editar.png" id="botao_editar" align="absbottom" border="0" title="Alterar operador">
								</a>
							<? } ?>
							<!-- fim - alterar operador -->
							<br>
							<span class="label_solicitacao">Operador leu em:</span>
							<br>
							<? if ($row_solicitacao['operador_leu'] != "") {
								echo date('d-m-Y - H:i:s', strtotime($row_solicitacao['operador_leu']));
							} else {
								echo "não leu";
							} ?>
						<? } ?>
						<!-- fim - operador leu em -->

						<!-- analista de orçamento leu em -->
						<? if ($row_solicitacao['id_analista_orcamento'] != "") { ?>
							<br><br>
							<? echo $row_solicitacao['nome_analista_orcamento']; ?>
							<!-- alterar analista_orcamento -->
							<? if ($row_usuario['controle_solicitacao'] == 'Y' or $row_solicitacao['id_operador'] == $row_usuario['IdUsuario']) { ?>
								<a href="solicitacao_editar_tabela.php?id_solicitacao=<? echo $row_solicitacao['id']; ?>&interacao=<? echo $row_solicitacao['interacao']; ?>&situacao=editar&acao=Alterar analista de orçamento&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $solicitacao_editar_tabela_height; ?>&width=<? echo $solicitacao_editar_tabela_width; ?>&modal=true" class="thickbox">
									<img src="imagens/editar.png" id="botao_editar" align="absbottom" border="0" title="Alterar analista de orçamento">
								</a>
							<? } ?>
							<!-- fim - alterar analista_orcamento -->
							<br>
							<span class="label_solicitacao">Analista de orçamento leu em:</span>
							<br>
							<? if ($row_solicitacao['analista_orcamento_leu'] != "") {
								echo date('d-m-Y - H:i:s', strtotime($row_solicitacao['analista_orcamento_leu']));
							} else {
								echo "não leu";
							} ?>
						<? } ?>
						<!-- fim - analista de orçamento leu em -->

						<!-- executante leu em -->
						<? if ($row_solicitacao['id_executante'] != "") { ?>
							<br><br>
							<? echo $row_solicitacao['nome_executante']; ?>
							<!-- alterar executante -->
							<? if ($row_usuario['controle_solicitacao'] == 'Y' or $row_solicitacao['id_operador'] == $row_usuario['IdUsuario']) { ?>
								<a href="solicitacao_editar_tabela.php?id_solicitacao=<? echo $row_solicitacao['id']; ?>&interacao=<? echo $row_solicitacao['interacao']; ?>&situacao=editar&acao=Alterar executante&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $solicitacao_editar_tabela_height; ?>&width=<? echo $solicitacao_editar_tabela_width; ?>&modal=true" class="thickbox">
									<img src="imagens/editar.png" id="botao_editar" align="absbottom" border="0" title="Alterar executante">
								</a>
							<? } ?>
							<!-- fim - alterar executante -->
							<br>
							<span class="label_solicitacao">Executante leu em:</span>
							<br>
							<? if ($row_solicitacao['executante_leu'] != "") {
								echo date('d-m-Y - H:i:s', strtotime($row_solicitacao['executante_leu']));
							} else {
								echo "não leu";
							} ?>
						<? } ?>
						<!-- fim - executante leu em -->

						<!-- executante leu em -->
						<? if ($row_solicitacao['id_testador'] != "") { ?>
							<br><br>
							<? echo $row_solicitacao['nome_testador']; ?>
							<br>
							<span class="label_solicitacao">Testador leu em:</span>
							<br>
							<? if ($row_solicitacao['testador_leu'] != "") {
								echo date('d-m-Y - H:i:s', strtotime($row_solicitacao['testador_leu']));
							} else {
								echo "não leu";
							} ?>
						<? } ?>
						<!-- fim - executante leu em -->

						<!-- duração -->
						<br><br>
						<span class="label_solicitacao">Duração:</span>
						<br>
						<?
						$data_ini = strtotime($row_solicitacao['dt_solicitacao']);
						$data_final = strtotime(date("Y-m-d H:i:s"));

						$nDias   = ($data_final - $data_ini) / (3600 * 24);  // dias
						$nHoras = (($data_final - $data_ini) % (3600 * 24)) / 3600; // horas
						$nMinutos = ((($data_final - $data_ini) % (3600 * 24)) % 3600) / 60; // minutos

						echo sprintf('%02dd %02dh %02dm', $nDias, $nHoras, $nMinutos);
						?>
						<!-- fim - duração -->

					<? } ?>

					<? if ($row_solicitacao['situacao'] == "solucionada" or $row_solicitacao['situacao'] == "reprovada") { ?>

						<? if ($row_solicitacao['solicitante_leu'] != "") { ?>
							<!-- solicitante leu em -->
							<? echo $row_solicitacao['usuario_responsavel']; ?>
							<br>
							<span class="label_solicitacao">Solicitante</span>
							<!-- fim - solicitante leu em -->
						<? } ?>

						<? if ($row_solicitacao['operador_leu'] != "") { ?>
							<!-- operador leu em -->
							<br><br>
							<? echo $row_solicitacao['nome_operador']; ?>
							<br>
							<span class="label_solicitacao">Operador</span>
							<!-- fim - operador leu em -->
						<? } ?>

						<? if ($row_solicitacao['executante_leu'] != "") { ?>
							<!-- executante leu em -->
							<br><br>
							<? echo $row_solicitacao['nome_executante']; ?>
							<br>
							<span class="label_solicitacao">Executante</span>
							<!-- fim - executante leu em -->
						<? } ?>

						<? if ($row_solicitacao['testador_leu'] != "") { ?>
							<!-- testador leu em -->
							<br><br>
							<? echo $row_solicitacao['nome_testador']; ?>
							<br>
							<span class="label_solicitacao">Testador</span>
							<!-- fim - testador leu em -->
						<? } ?>

					<? } ?>

					<!-- infos de orcamento -->
					<? if ($row_solicitacao['orcamento'] != "") { ?>
						<br><br>
						<span class="label_solicitacao">Orçamento atual:</span>
						<br>
						Valor: R$ <? echo number_format($row_solicitacao['orcamento'], 2, ',', '.'); ?>
						<br>
						Prazo desenvol.: <? echo $row_solicitacao['prazo_desenvolvimento_orcamento']; ?> dias
						<br>
						<? if ($row_solicitacao['orcamento_os'] != "") { ?>Núm. da OS: <? echo $row_solicitacao['orcamento_os']; ?><? } ?>
					<? } ?>
					<!-- fim - infos de orcamento -->

					<? if (
						($row_solicitacao['situacao'] == "em análise" and $row_solicitacao['previsao_analise'] <> $row_solicitacao['previsao_geral'] and $row_solicitacao['previsao_analise'] != "0000-00-00 00:00:00") or

						($row_solicitacao['situacao'] == "em orçamento" and $row_solicitacao['previsao_analise_orcamento'] <> $row_solicitacao['previsao_geral'] and $row_solicitacao['previsao_analise_orcamento'] != "0000-00-00 00:00:00" and $row_solicitacao['orcamento'] == "") or

						($row_solicitacao['situacao'] == "em execução" and $row_solicitacao['previsao_solucao'] <> $row_solicitacao['previsao_geral'] and $row_solicitacao['previsao_solucao'] != "0000-00-00 00:00:00") or

						($row_solicitacao['situacao'] == "em testes" and $row_solicitacao['previsao_testes'] <> $row_solicitacao['previsao_geral'] and $row_solicitacao['previsao_testes'] != "0000-00-00 00:00:00")
					) { ?>
						<br><br>
						<font color=red>
							Foi solicitado alteração da data de previsão da solicitação.
						</font>
					<? } ?>

				</td>
				<td style="padding: 0px;">

					<div class="div_descricao">

						<!-- Descrição -->
						<?php do { ?>

							<strong>
								<?
								// descricao_usuario (nome)
								$colname_descricao_usuario = "-1";
								if (isset($row_descricao['IdUsuario'])) {
									$colname_descricao_usuario = $row_descricao['IdUsuario'];
								}
								mysql_select_db($database_conexao, $conexao);
								$query_descricao_usuario = sprintf("SELECT nome FROM usuarios WHERE IdUsuario = %s", GetSQLValueString($colname_descricao_usuario, "int"));
								$descricao_usuario = mysql_query($query_descricao_usuario, $conexao) or die(mysql_error());
								$row_descricao_usuario = mysql_fetch_assoc($descricao_usuario);
								$totalRows_descricao_usuario = mysql_num_rows($descricao_usuario);

								echo $row_descricao_usuario['nome'];

								mysql_free_result($descricao_usuario);
								// fim - descricao_usuario (nome)
								?>

								<?php if ($row_descricao['usuario_responsavel'] != "") {
									echo $row_descricao['usuario_responsavel'];
								} else {
									echo "Sistema";
								} // PARA SOLICITACOES ANTIGAS - responsável do post 
								?>
								| <? echo date('d-m-Y | H:i:s', strtotime($row_descricao['data'])); ?> |
								<?php echo $row_descricao['tipo_postagem']; ?>
								<br>
							</strong>

							<?php if ($row_descricao['questionado'] != "") { ?>
								Para: <strong><?php echo $row_descricao['questionado']; ?></strong>
								<br>
							<? } ?>

							<? if ($row_descricao['tipo_postagem'] <> "Nova Solicitação") { ?>
								<?php echo $row_descricao['descricao']; ?>
								<div style=" width: 100%; height: 1px; background-color: #CCCCCC; margin-top: 10px; margin-bottom: 10px; padding-right:0px; padding-left: 0px; margin-left: 0px; margin-right: 0px;"></div>
							<? } else { ?>
								<div style=" width: 100%; height: 1px; background-color: #4297D7; margin-top: 10px; margin-bottom: 10px; padding-right:0px; padding-left: 0px; margin-left: 0px; margin-right: 0px;"></div>
							<? } ?>

						<?php } while ($row_descricao = mysql_fetch_assoc($descricao)); ?>
						<!-- fim - Descrição -->

						<!-- Dados da solicitação -->
						<div>
							<strong>Dados da solicitação: </strong>
							<br><br>
							<strong>Anomalia</strong>:
							<?
							// anomalia_atual
							mysql_select_db($database_conexao, $conexao);
							$query_anomalia_atual = sprintf("SELECT descricao FROM solicitacao_descricoes WHERE id_solicitacao = %s and tipo_postagem = 'Nova Solicitação'", GetSQLValueString($row_solicitacao['id'], "int"));
							$anomalia_atual = mysql_query($query_anomalia_atual, $conexao) or die(mysql_error());
							$row_anomalia_atual = mysql_fetch_assoc($anomalia_atual);
							$totalRows_anomalia_atual = mysql_num_rows($anomalia_atual);
							// fim - anomalia_atual
							echo $row_anomalia_atual['descricao'];
							mysql_free_result($anomalia_atual);
							?>

							<br><br>
							<strong>Medida tomada</strong>: <?php echo $row_solicitacao['medida_tomada']; ?>

						</div>
						<!-- fim - Dados da solicitação -->


						<? if ($totalRows_suporte > 0 and $row_solicitacao['protocolo_suporte'] <> NULL) { ?>

							<div style=" width: 100%; height: 1px; background-color: #CCCCCC; margin-top: 10px; margin-bottom: 10px; padding-right:0px; padding-left: 0px; margin-left: 0px; margin-right: 0px;"></div>

							<!-- Dados do suporte vinculado -->
							<div>
								<strong>Dados do suporte vinculado: </strong>
								<br><br>
								<strong>Suporte</strong>: <?php echo $row_suporte['id']; ?> - <?php echo $row_suporte['titulo']; ?><br>
								<?php if ($row_suporte['usuario_responsavel'] != "") { ?>
									<strong>Responsável</strong>: <?php echo $row_suporte['usuario_responsavel']; ?><br>
								<? } ?>
								<?php if ($row_suporte['anomalia'] != "") { ?>
									<strong>Anomalia</strong>: <?php echo $row_suporte['anomalia']; ?><br>
								<? } ?>
								<?php if ($row_suporte['data_inicio'] != "" and $row_suporte['data_inicio'] != "0000-00-00 00:00:00") { ?>
									<strong>Data/hora inicio</strong>: <? echo date('d-m-Y  H:i:s', strtotime($row_suporte['data_inicio'])); ?>
								<? } ?>
								<?php if ($row_suporte['data_fim'] != "" and $row_suporte['data_fim'] != "0000-00-00 00:00:00") { ?>
									| <strong>Data/hora fim</strong>: <? echo date('d-m-Y  H:i:s', strtotime($row_suporte['data_fim'])); ?>
								<? } ?>
								<br>
								<?php if ($row_suporte['orientacao'] != "") { ?>
									<strong>Orientação</strong>: <?php echo $row_suporte['orientacao']; ?><br>
								<? } ?>
								<?php if ($row_suporte['observacao'] != "") { ?>
									<strong>Observação</strong>: <?php echo $row_suporte['observacao']; ?><br>
								<? } ?>

							</div>
							<!-- fim - Dados do suporte vinculado -->

						<? } ?>

						<? if ($totalRows_solicitacao_desmembrada > 0 and $row_solicitacao['solicitacao_desmembrada'] <> NULL) { ?>

							<div style=" width: 100%; height: 1px; background-color: #CCCCCC; margin-top: 10px; margin-bottom: 10px; padding-right:0px; padding-left: 0px; margin-left: 0px; margin-right: 0px;"></div>

							<!-- Dados da solicitacao_desmembrada -->
							<div>
								<strong>Dados da solicitação desmembrada: </strong>
								<br><br>
								<strong>Solicitação</strong>: <?php echo $row_solicitacao_desmembrada['id']; ?> - <?php echo $row_solicitacao_desmembrada['titulo']; ?><br>
								<?php if ($row_solicitacao_desmembrada['usuario_responsavel'] != "") { ?>
									<strong>Responsável</strong>: <?php echo $row_solicitacao_desmembrada['usuario_responsavel']; ?><br>
								<? } ?>
								<?php if ($row_solicitacao_desmembrada['usuario_operador'] != "") { ?>
									<strong>Operador</strong>: <?php echo $row_solicitacao_desmembrada['usuario_operador']; ?><br>
								<? } ?>
								<?php if ($row_solicitacao_desmembrada['dt_solicitacao'] != "" and $row_solicitacao_desmembrada['dt_solicitacao'] != "0000-00-00 00:00:00") { ?>
									<strong>Criação</strong>: <? echo date('d-m-Y  H:i:s', strtotime($row_solicitacao_desmembrada['dt_solicitacao'])); ?>
								<? } ?>
								<br>
							</div>
							<!-- fim - Dados da solicitacao_desmembrada -->

						<? } ?>

					</div>

				</td>

			</tr>
		</table>
	</div>

	<? if ($totalRows_arquivos_anexos > 0) { ?>
		<div class="div_solicitacao_linhas4">
			<table cellspacing="0" cellpadding="0" width="100%">
				<tr>
					<td style="text-align: left; color: #F00; font-weight: bold;">
						Existe(m) arquivo(s) em anexo.
					</td>
				</tr>
			</table>
		</div>
	<? } ?>

	<div class="div_solicitacao_linhas3" id="botoes">
		<table cellspacing="0" cellpadding="0" width="100%">
			<tr>
				<td style="text-align: left; ">

					<!-- Tempo gasto -->
					<? if ($totalRows_tempo_gasto > 0) { ?>
						<a href="solicitacao_editar_tempo_gasto.php?id_solicitacao=<? echo $row_solicitacao['id']; ?>&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $solicitacao_editar_tabela_height; ?>&width=<? echo $solicitacao_editar_tabela_width; ?>&modal=true" class="thickbox" id="botao_geral">Tempo gasto</a>
					<? } ?>
					<!-- fim - Tempo gasto -->

					<!-- Imprimir -->
					<a href="solicitacao_imprimir.php?id_solicitacao=<? echo $row_solicitacao['id']; ?>" target="_blank" id="botao_geral">Imprimir</a>
					<!-- fim - Imprimir -->

					<!-- Alterar anomalia -->
					<? if (
						(
							($row_usuario['controle_solicitacao'] == "Y" or $row_solicitacao['id_operador'] == $row_usuario['IdUsuario']) // se é operador da solicitação
							or
							($row_solicitacao['id_usuario_responsavel'] == $row_usuario['IdUsuario'] and $row_solicitacao['situacao'] == "criada") // se é solicitante da solicitação
						) and
						($row_solicitacao['situacao'] != "reprovada" and $row_solicitacao['situacao'] != "solucionada")
					) {
					?>
						<a href="solicitacao_editar_tabela.php?id_solicitacao=<? echo $row_solicitacao['id']; ?>&interacao=<? echo $row_solicitacao['interacao']; ?>&situacao=editar&acao=Alterar anomalia&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $solicitacao_editar_tabela_height; ?>&width=<? echo $solicitacao_editar_tabela_width; ?>&modal=true" class="thickbox" id="botao_geral" style="width: 150px;">Alterar anomalia</a>
					<? } ?>
					<!-- fim - Alterar anomalia -->

					<!-- Alterar medida tomada -->
					<? if (
						(
							($row_usuario['controle_solicitacao'] == "Y" or $row_solicitacao['id_operador'] == $row_usuario['IdUsuario']) // se é operador da solicitação
							or
							($row_solicitacao['id_usuario_responsavel'] == $row_usuario['IdUsuario'] and $row_solicitacao['situacao'] == "criada") // se é solicitante da solicitação
						) and
						($row_solicitacao['situacao'] != "reprovada" and $row_solicitacao['situacao'] != "solucionada")
					) {
					?>
						<a href="solicitacao_editar_tabela.php?id_solicitacao=<? echo $row_solicitacao['id']; ?>&interacao=<? echo $row_solicitacao['interacao']; ?>&situacao=editar&acao=Alterar medida tomada&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $solicitacao_editar_tabela_height; ?>&width=<? echo $solicitacao_editar_tabela_width; ?>&modal=true" class="thickbox" id="botao_geral" style="width: 150px;">Alterar medida tomada</a>
					<? } ?>
					<!-- fim - Alterar medida tomada -->

				</td>
			</tr>
		</table>
	</div>

</body>

</html>

<!-- reclamacao_consulta -->
<? if (isset($_GET['padrao']) && ($_GET['padrao'] == "sim")) { ?>
	<? if ($totalRows_reclamacao_consulta > 0) { ?>

		<script>
			alert('<? echo $reclamacao_consulta_mensagem; ?>');
		</script>

	<? } ?>
<? } ?>
<!-- fim - reclamacao_consulta -->

<?php
mysql_free_result($geral_tipo_programa);
mysql_free_result($geral_tipo_subprograma);
mysql_free_result($descricao);
mysql_free_result($arquivos_anexos);
mysql_free_result($solicitacao_devolucao);
mysql_free_result($tempo_gasto);
mysql_free_result($solicitacao_tipo_parecer);
mysql_free_result($solicitacao);
mysql_free_result($usuario);
mysql_free_result($suporte);
mysql_free_result($solicitacao_desmembrada);
mysql_free_result($reclamacao_solicitacao);
mysql_free_result($reclamacao_consulta);
?>