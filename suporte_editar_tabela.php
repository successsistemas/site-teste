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
	$editFormAction .= "?" . htmlspecialchars($_SERVER['QUERY_STRING'], ENT_QUOTES, 'UTF-8');
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

// suporte
$colname_suporte = "-1";
if (isset($_GET['id_suporte'])) {
	$colname_suporte = $_GET['id_suporte'];
}
mysql_select_db($database_conexao, $conexao);
$query_suporte = sprintf(
	"
SELECT 
	suporte.*,  
	(SELECT nome FROM usuarios WHERE usuarios.IdUsuario = suporte.id_usuario_responsavel) as usuario_responsavel, 
	(SELECT nome FROM usuarios WHERE usuarios.IdUsuario = suporte.id_usuario_envolvido) as usuario_envolvido, 
	(SELECT COUNT(id_arquivo) FROM suporte_arquivos WHERE suporte_arquivos.id_suporte = suporte.id) as suporte_arquivos_contador, 
	suporte_tipo_atendimento.solicita_suporte_versao AS suporte_tipo_atendimento_solicita_suporte_versao 
FROM 
	suporte 
LEFT JOIN 
	suporte_tipo_atendimento ON suporte.tipo_atendimento = suporte_tipo_atendimento.descricao 
WHERE 
	suporte.id = %s
",
	GetSQLValueString($colname_suporte, "int")
);
$suporte = mysql_query($query_suporte, $conexao) or die(mysql_error());
$row_suporte = mysql_fetch_assoc($suporte);
$totalRows_suporte = mysql_num_rows($suporte);
// fim - suporte

// suporte_contato
mysql_select_db($database_conexao, $conexao);
$query_suporte_contato =  sprintf(
	"
SELECT suporte_contato.*, usuarios.nome AS usuario_responsavel 
FROM suporte_contato 
LEFT JOIN usuarios ON suporte_contato.id_usuario_responsavel = usuarios.IdUsuario 
WHERE id_suporte = %s 
ORDER BY suporte_contato.id DESC",
	GetSQLValueString($row_suporte['id'], "int")
);
$suporte_contato = mysql_query($query_suporte_contato, $conexao) or die(mysql_error());
$row_suporte_contato = mysql_fetch_assoc($suporte_contato);
$totalRows_suporte_contato = mysql_num_rows($suporte_contato);
// fim - suporte_contato

// verifica o tipo_suporte_inloco
$tipo_suporte_inloco = "";
if ($row_suporte['tipo_suporte'] == "c" and $row_suporte['inloco'] == "s") {
	$tipo_suporte_inloco = "cs"; // cliente inloco SIM
} else if ($row_suporte['tipo_suporte'] == "c" and $row_suporte['inloco'] == "n") {
	$tipo_suporte_inloco = "cn"; // cliente inloco NAO
} else if ($row_suporte['tipo_suporte'] == "p") {
	$tipo_suporte_inloco = "p"; // parceiro
} else if ($row_suporte['tipo_suporte'] == "r") {
	$tipo_suporte_inloco = "r"; // reclamacao
}
// verifica o tipo_suporte_inloco

// acessso --------------------------------------------------------------------------------------------------------------------------------------------------------------
$acesso = 0;
if (
	(
		($row_suporte['status_flag'] != "f") and
		(
			($row_suporte['id_usuario_responsavel'] == $row_usuario['IdUsuario']) or
			($row_suporte['id_usuario_envolvido'] == $row_usuario['IdUsuario']) or
			($row_usuario['controle_suporte'] == "Y") or
			($row_usuario['controle_praca'] == "Y" and $row_usuario['praca'] == $row_suporte['praca']) or
			($row_usuario['suporte_operador_parceiro'] == "Y") or
			($row_usuario['praca'] == $row_suporte['praca']))) or (
		($row_suporte['status_flag'] == "f" and $_GET['situacao'] == "editar") and
		(
			($row_suporte['id_usuario_responsavel'] == $row_usuario['IdUsuario']) or
			($row_suporte['id_usuario_envolvido'] == $row_usuario['IdUsuario']) or
			($row_usuario['controle_suporte'] == "Y") or
			($row_usuario['controle_praca'] == "Y" and $row_usuario['praca'] == $row_suporte['praca']) or
			($row_usuario['suporte_operador_parceiro'] == "Y") or
			($row_usuario['tipo_suporte'] == "p" and $row_usuario['suporte_operador_parceiro'] == "Y")))
) {

	$acesso = 1; // autorizado

} else {

	$acesso = 0; // não autorizado

}

if ($acesso == 0) {
	//echo "Acesso não autorizado !";
	echo "<script>window.top.location = 'suporte.php?padrao=sim&" . $suporte_padrao . "';</script>";
	exit;
}
// fim - acesso ---------------------------------------------------------------------------------------------------------------------------------------------------------

// converter entrada de data em portugues para ingles
if (isset($_POST['data_inicio']) and $_POST['data_inicio'] != "") {
	$data_data = substr($_POST['data_inicio'], 0, 10);
	$data_hora = substr($_POST['data_inicio'], 10, 9);
	$_POST['data_inicio'] = implode("-", array_reverse(explode("-", $data_data))) . $data_hora;
} else {
	$_POST['data_inicio'] = "0000-00-00 00:00:00";
}

if (isset($_POST['data_fim']) and $_POST['data_fim'] != "") {
	$data_data = substr($_POST['data_fim'], 0, 10);
	$data_hora = substr($_POST['data_fim'], 10, 9);
	$_POST['data_fim'] = implode("-", array_reverse(explode("-", $data_data))) . $data_hora;
} else {
	$_POST['data_fim'] = "0000-00-00 00:00:00";
}

if (isset($_POST['data_suporte_fim']) and $_POST['data_suporte_fim'] != "") {
	$data_data = substr($_POST['data_suporte_fim'], 0, 10);
	$data_hora = substr($_POST['data_suporte_fim'], 10, 9);
	$_POST['data_suporte_fim'] = implode("-", array_reverse(explode("-", $data_data))) . $data_hora;
} else {
	$_POST['data_suporte_fim'] = "0000-00-00 00:00:00";
}

if (isset($_POST['reclamacao_data_acordada']) and $_POST['reclamacao_data_acordada'] != "") {
	$data_data = substr($_POST['reclamacao_data_acordada'], 0, 10);
	$data_hora = substr($_POST['reclamacao_data_acordada'], 10, 9);
	$_POST['reclamacao_data_acordada'] = implode("-", array_reverse(explode("-", $data_data))) . $data_hora;
} else {
	$_POST['reclamacao_data_acordada'] = "0000-00-00 00:00:00";
}
// fim - converter entrada de data em portugues para ingles - fim

// agenda (para editar/cancelar 'agenda')
$colname_agenda = "-1";
if (isset($_GET['id_suporte'])) {
	$colname_agenda = $_GET['id_suporte'];
}

$colname_agenda2 = "-1";
if (isset($_GET['id_agenda'])) {
	$colname_agenda2 = $_GET['id_agenda'];
}

mysql_select_db($database_conexao, $conexao);
$query_agenda = sprintf("
SELECT * 
FROM agenda 
WHERE id_suporte = %s and id_agenda = %s 
ORDER BY data ASC", GetSQLValueString($colname_agenda, "text"), GetSQLValueString($colname_agenda2, "text"));
$agenda = mysql_query($query_agenda, $conexao) or die(mysql_error());
$row_agenda = mysql_fetch_assoc($agenda);
$totalRows_agenda = mysql_num_rows($agenda);
// fim - agenda (para editar/cancelar 'agenda')

// agenda_agendado
mysql_select_db($database_conexao, $conexao);
$query_agenda_agendado = sprintf("
SELECT * 
FROM agenda 
WHERE id_suporte = %s and status = 'a'
ORDER BY data ASC", GetSQLValueString(@$_GET['id_suporte'], "text"));
$agenda_agendado = mysql_query($query_agenda_agendado, $conexao) or die(mysql_error());
$row_agenda_agendado = mysql_fetch_assoc($agenda_agendado);
$totalRows_agenda_agendado = mysql_num_rows($agenda_agendado);
// fim - agenda_agendado

if (((isset($_POST["MM_update"])) and ($_POST["MM_update"] == "form")) or ((isset($_GET["MM_update"])) and ($_GET["MM_update"] == "form"))) {

	require_once('funcao_formata_data.php');
	require_once('suporte_funcao_update.php');
	require_once('suporte_funcao_tempo_gasto.php');

	// interacao **********************************************************************************************************
	$interacao = funcao_suporte_interacao($row_suporte['id'], @$_GET['interacao']);
	if ($interacao == 1 and @$_GET['interacao'] <> NULL) {
		echo "<script>alert('Foi realizada alguma interação anterior a esta, assim, a ação atual não será gravada. Realize uma nova ação após a atualização da página.');</script>";
		$redirGoTo = "suporte_editar.php?id_suporte=" . $_GET['id_suporte'];
		echo sprintf("<meta http-equiv=\"refresh\" content=\"0; url=%s\">", $redirGoTo);
		exit;
	}
	// fim - interacao ****************************************************************************************************

	//---------------------------------------------------------------------------------------------------------------------------------------------------------------
	// Encaminhar
	if ($_GET['acao'] == "Encaminhar" and $_GET["resposta"] == "") {

		// busca usuario_selecionado
		$colname_usuario_selecionado = "-1";
		if (isset($_POST['usuario_responsavel'])) {
			$colname_usuario_selecionado = $_POST['usuario_responsavel'];
		}
		mysql_select_db($database_conexao, $conexao);
		$query_usuario_selecionado = sprintf("SELECT IdUsuario, nome, praca FROM usuarios WHERE IdUsuario = %s", GetSQLValueString($colname_usuario_selecionado, "int"));
		$usuario_selecionado = mysql_query($query_usuario_selecionado, $conexao) or die(mysql_error());
		$row_usuario_selecionado = mysql_fetch_assoc($usuario_selecionado);
		$totalRows_usuario_selecionado = mysql_num_rows($usuario_selecionado);
		// fim - busca usuario_selecionado

		// analisada
		if ($_GET['situacao'] == "analisada") {

			$dados_suporte = array(
				"interacao" => $row_suporte['interacao'] + 1,
				"situacao" => "analisada",
				"status" => "encaminhada para usuario responsavel",

				"id_usuario_responsavel" => $row_usuario_selecionado['IdUsuario'],
				"usuario_responsavel_leu" => "",

				"encaminhamento_id" => $row_usuario['IdUsuario'],
				"encaminhamento_data_inicio" => "",
				"encaminhamento_data" => "",

				"status_devolucao" => "",
				"status_recusa" => ""
			);
		}
		// fim - analisada

		// em execução
		if ($_GET['situacao'] == "em execução") {

			$dados_suporte = array(
				"interacao" => $row_suporte['interacao'] + 1,
				"situacao" => "analisada",
				"status" => "encaminhada para usuario responsavel",

				"id_usuario_responsavel" => $row_usuario_selecionado['IdUsuario'],

				"usuario_responsavel_leu" => "",

				"encaminhamento_id" => $row_suporte['id_usuario_responsavel'],
				"encaminhamento_data_inicio" => "",
				"encaminhamento_data" => "",

				"status_devolucao" => "",
				"status_recusa" => ""
			);
		}
		// fim - em execução

		$dados_suporte_descricao = array(
			"id_suporte" => $row_suporte['id'],
			"id_usuario_responsavel" => $row_usuario['IdUsuario'],
			"descricao" => "Encaminhada para novo responsável<br>Para: " . $row_usuario_selecionado['nome'] . "<br>" . $_POST['observacao'],
			"data" => date("Y-m-d H:i:s"),
			"tipo_postagem" => "Escolha de novo responsável"
		);

		mysql_free_result($usuario_selecionado);

		funcao_suporte_update($row_suporte['id'], $dados_suporte, $dados_suporte_descricao);
		tempo_gasto($row_suporte['id'], $row_suporte['situacao'], $_GET['acao'], $row_usuario['IdUsuario'], '', $_POST['tempo_gasto']);
	}
	// fim - Encaminhar
	//---------------------------------------------------------------------------------------------------------------------------------------------------------------
	// Devolver
	if (($_GET['situacao'] == "editar") and $_GET["acao"] == "Devolver") {

		// parceiro
		if ($tipo_suporte_inloco == "p") {

			$dados_suporte = array(
				"interacao" => $row_suporte['interacao'] + 1,
				"situacao" => "em execução",
				"status" => "devolvida para usuario responsavel",

				"previsao_geral_inicio" => date("Y-m-d H:i:s"),
				"previsao_geral" => date("Y-m-d H:i:s"),

				//"data_suporte_fim" => "0000-00-00 00:00:00",
				//"parecer" => "",

				"encaminhamento_id" => "",
				"encaminhamento_data_inicio" => "",
				"encaminhamento_data" => "",

				"status_devolucao" => "1",
				"status_recusa" => "",

				"solicita_suporte" => "n",
				"solicita_visita" => "n",

				"solicita_solicitacao" => "n",

				"final_situacao" => "",
				"final_parecer" => "",
				"final_status" => "",
				"final_solicita_suporte" => "",
				"final_solicita_visita" => ""
			);
			$dados_suporte_descricao = array(
				"id_suporte" => $row_suporte['id'],
				"id_usuario_responsavel" => $row_usuario['IdUsuario'],
				"descricao" => $_POST['observacao'],
				"data" => date("Y-m-d H:i:s"),
				"tipo_postagem" => "Devolução para usuario responsavel"
			);
			funcao_suporte_update($row_suporte['id'], $dados_suporte, $dados_suporte_descricao);
		}
		// fim - parceiro

		// cliente inloco: sim (cobrança)
		if ($tipo_suporte_inloco == "cs" and $row_suporte['cobranca'] == "s") {

			$dados_suporte = array(
				"interacao" => $row_suporte['interacao'] + 1,
				"situacao" => "em execução",
				"status" => "devolvida para usuario responsavel",

				"previsao_geral_inicio" => date("Y-m-d H:i:s"),
				"previsao_geral" => date("Y-m-d H:i:s"),

				"encaminhamento_id" => "",
				"encaminhamento_data_inicio" => "",
				"encaminhamento_data" => "",

				"status_devolucao" => "1",
				"status_recusa" => "",

				"solicita_suporte" => "n",
				"solicita_visita" => "n",

				"solicita_solicitacao" => "n",

				"data_suporte_fim" => "0000-00-00 00:00:00",
				"parecer" => "",

				"final_situacao" => "",
				"final_parecer" => "",
				"final_status" => "",
				"final_solicita_suporte" => "",
				"final_solicita_visita" => ""
			);
			$dados_suporte_descricao = array(
				"id_suporte" => $row_suporte['id'],
				"id_usuario_responsavel" => $row_usuario['IdUsuario'],
				"descricao" => $_POST['observacao'],
				"data" => date("Y-m-d H:i:s"),
				"tipo_postagem" => "Devolução para usuario responsavel"
			);
			funcao_suporte_update($row_suporte['id'], $dados_suporte, $dados_suporte_descricao);
		}
		// fim - cliente inloco: sim (cobrança)

		// cliente inloco: sim (Extra/Treinamento(n))
		if (
			$tipo_suporte_inloco == "cs" and 
			(
				$row_suporte['tipo_formulario'] == "Extra" or 
				($row_suporte['tipo_formulario'] == "Treinamento" and $row_suporte['creditar'] == "n")
			)
		) {

			$dados_suporte = array(
				"interacao" => $row_suporte['interacao'] + 1,
				"situacao" => "em execução",
				"status" => "devolvida para usuario responsavel",

				"previsao_geral_inicio" => date("Y-m-d H:i:s"),
				"previsao_geral" => date("Y-m-d H:i:s"),

				"encaminhamento_id" => "",
				"encaminhamento_data_inicio" => "",
				"encaminhamento_data" => "",

				"status_devolucao" => "1",
				"status_recusa" => "",

				"solicita_suporte" => "n",
				"solicita_visita" => "n",

				"solicita_solicitacao" => "n",

				"data_suporte_fim" => "0000-00-00 00:00:00",
				"parecer" => "",

				"final_situacao" => "",
				"final_parecer" => "",
				"final_status" => "",
				"final_solicita_suporte" => "",
				"final_solicita_visita" => ""
			);
			$dados_suporte_descricao = array(
				"id_suporte" => $row_suporte['id'],
				"id_usuario_responsavel" => $row_usuario['IdUsuario'],
				"descricao" => $_POST['observacao'],
				"data" => date("Y-m-d H:i:s"),
				"tipo_postagem" => "Devolução para usuario responsavel"
			);
			funcao_suporte_update($row_suporte['id'], $dados_suporte, $dados_suporte_descricao);

		}
		// fim - cliente inloco: sim (Extra/Treinamento(n))

		// reclamacao
		if ($tipo_suporte_inloco == "r") {

			$dados_suporte = array(
				"interacao" => $row_suporte['interacao'] + 1,
				"situacao" => "em execução",
				"status" => "pendente usuario responsavel",

				"acao" => "",

				"data_inicio" => date("Y-m-d H:i:s"),
				"data_fim" => date("Y-m-d H:i:s"),

				"previsao_geral_inicio" => date("Y-m-d H:i:s"),
				"previsao_geral" => date("Y-m-d H:i:s"),

				"encaminhamento_id" => "",
				"encaminhamento_data_inicio" => "",
				"encaminhamento_data" => "",

				"status_devolucao" => "",
				"status_recusa" => "",

				"solicita_suporte" => "n",
				"solicita_visita" => "n",
				"solicita_solicitacao" => "n",

				"data_suporte_fim" => "0000-00-00 00:00:00",

				"final_situacao" => "",
				"final_parecer" => "",
				"final_status" => "",
				"final_solicita_suporte" => "",
				"final_solicita_visita" => ""
			);
			$dados_suporte_descricao = array(
				"id_suporte" => $row_suporte['id'],
				"id_usuario_responsavel" => $row_usuario['IdUsuario'],
				"descricao" => $_POST['observacao'],
				"data" => date("Y-m-d H:i:s"),
				"tipo_postagem" => "Devolução para usuario responsavel"
			);
			funcao_suporte_update($row_suporte['id'], $dados_suporte, $dados_suporte_descricao);
		}
		// fim - reclamacao

	}
	// fim - Devolver
	//---------------------------------------------------------------------------------------------------------------------------------------------------------------



	//---------------------------------------------------------------------------------------------------------------------------------------------------------------
	// ACEITAR ---------------------------------------------------------------------------------------------------------------------------------------
	//---------------------------------------------------------------------------------------------------------------------------------------------------------------
	// aceitar ------------------------------------------------------------------------------------------------------------
	if ($_GET['acao'] == "Aceitar" and $_GET["resposta"] == "" and $row_suporte['status_recusa'] != "1") { // aceitar

		// analisada
		if ($_GET['situacao'] == "analisada" and $row_suporte['status_devolucao'] == "") {

			$dados_suporte = array(
				"interacao" => $row_suporte['interacao'] + 1,
				"situacao" => "em execução",
				"status" => "pendente usuario responsavel",

				"acao" => "",

				"previsao_geral_inicio" => "",
				"previsao_geral" => "",

				"encaminhamento_id" => "",
				"encaminhamento_data_inicio" => "",
				"encaminhamento_data" => "",

				"status_devolucao" => "",
				"status_recusa" => ""
			);
			$dados_suporte_descricao = array(
				"id_suporte" => $row_suporte['id'],
				"id_usuario_responsavel" => $row_usuario['IdUsuario'],
				"descricao" => $_POST['observacao'],
				"data" => date("Y-m-d H:i:s"),
				"tipo_postagem" => "Suporte aceito por usuário responsável"
			);

			funcao_suporte_update($row_suporte['id'], $dados_suporte, $dados_suporte_descricao);

			// update 'agenda'
			$updateSQL_suporte_agenda = sprintf(
				"
											   UPDATE agenda 
											   SET id_usuario_responsavel=%s 
											   WHERE id_suporte=%s and status='a'",
				GetSQLValueString($row_usuario['IdUsuario'], "int"),

				GetSQLValueString($row_suporte['id'], "int")
			);

			mysql_select_db($database_conexao, $conexao);
			$Result_suporte_agenda = mysql_query($updateSQL_suporte_agenda, $conexao) or die(mysql_error());
			// fim - update 'agenda'

		}
		// fim - analisada

		// devolucao
		if ($row_suporte['status_devolucao'] == "1") {

			// parceiro
			if ($tipo_suporte_inloco == "p") {

				$dados_suporte = array(
					"interacao" => $row_suporte['interacao'] + 1,
					"situacao" => "em execução",
					"status" => "pendente usuario responsavel",

					"acao" => "",

					"previsao_geral_inicio" => "",
					"previsao_geral" => "",

					"data_suporte_fim" => "0000-00-00 00:00:00",
					"parecer" => "",

					"encaminhamento_id" => "",
					"encaminhamento_data_inicio" => "",
					"encaminhamento_data" => "",

					"status_devolucao" => "",
					"status_recusa" => ""
				);
				$dados_suporte_descricao = array(
					"id_suporte" => $row_suporte['id'],
					"id_usuario_responsavel" => $row_usuario['IdUsuario'],
					"descricao" => $_POST['observacao'],
					"data" => date("Y-m-d H:i:s"),
					"tipo_postagem" => "Devolução aceita por usuário responsável"
				);

				funcao_suporte_update($row_suporte['id'], $dados_suporte, $dados_suporte_descricao);
			}
			// fim - parceiro

			// cliente inloco: sim - cobrançca
			if ($tipo_suporte_inloco == "cs" and $row_suporte['cobranca'] == "s") {

				$dados_suporte = array(
					"interacao" => $row_suporte['interacao'] + 1,
					"situacao" => "em execução",
					"status" => "pendente usuario responsavel",

					"acao" => "",

					"previsao_geral_inicio" => "",
					"previsao_geral" => "",

					"encaminhamento_id" => "",
					"encaminhamento_data_inicio" => "",
					"encaminhamento_data" => "",

					"status_devolucao" => "",
					"status_recusa" => ""
				);
				$dados_suporte_descricao = array(
					"id_suporte" => $row_suporte['id'],
					"id_usuario_responsavel" => $row_usuario['IdUsuario'],
					"descricao" => $_POST['observacao'],
					"data" => date("Y-m-d H:i:s"),
					"tipo_postagem" => "Devolução aceita por usuário responsável"
				);

				funcao_suporte_update($row_suporte['id'], $dados_suporte, $dados_suporte_descricao);
			}
			// fim - cliente inloco: sim - cobrançca

			// cliente inloco: sim - Extra/Treinamento(n)
			if (
				$tipo_suporte_inloco == "cs" and 
				(
					$row_suporte['tipo_formulario'] == "Extra" or 
					($row_suporte['tipo_formulario'] == "Treinamento" and $row_suporte['creditar'] == "n")
				)
			) {

				$dados_suporte = array(
					"interacao" => $row_suporte['interacao'] + 1,
					"situacao" => "em execução",
					"status" => "pendente usuario responsavel",

					"acao" => "",

					"previsao_geral_inicio" => "",
					"previsao_geral" => "",

					"encaminhamento_id" => "",
					"encaminhamento_data_inicio" => "",
					"encaminhamento_data" => "",

					"status_devolucao" => "",
					"status_recusa" => ""
				);
				$dados_suporte_descricao = array(
					"id_suporte" => $row_suporte['id'],
					"id_usuario_responsavel" => $row_usuario['IdUsuario'],
					"descricao" => $_POST['observacao'],
					"data" => date("Y-m-d H:i:s"),
					"tipo_postagem" => "Devolução aceita por usuário responsável"
				);

				funcao_suporte_update($row_suporte['id'], $dados_suporte, $dados_suporte_descricao);
			}
			// fim - cliente inloco: sim - Extra/Treinamento(n)

		}
		// fim - devolucao

	}
	// fim - aceitar -------------------------------------------------------------------------------------------------------

	// aceitar recusa ------------------------------------------------------------------------------------------------------
	if ($_GET['acao'] == "Aceitar" and $_GET["resposta"] == "" and $row_suporte['status_recusa'] == "1") {

		// analisada
		if ($_GET['situacao'] == "analisada") {

			$dados_suporte = array(
				"interacao" => $row_suporte['interacao'] + 1,
				"situacao" => "em execução",
				"status" => "pendente usuario responsavel",

				"id_usuario_responsavel" => $row_suporte['encaminhamento_id'],

				"encaminhamento_id" => "",
				"encaminhamento_data_inicio" => "",
				"encaminhamento_data" => "",

				"status_devolucao" => "",
				"status_recusa" => ""
			);
			$dados_suporte_descricao = array(
				"id_suporte" => $row_suporte['id'],
				"id_usuario_responsavel" => $row_usuario['IdUsuario'],
				"descricao" => $_POST['observacao'],
				"data" => date("Y-m-d H:i:s"),
				"tipo_postagem" => "Recusa aceita"
			);

			funcao_suporte_update($row_suporte['id'], $dados_suporte, $dados_suporte_descricao);
		}
		// fim - analisada

	}
	// fim - aceitar recusa ------------------------------------------------------------------------------------------------
	//---------------------------------------------------------------------------------------------------------------------------------------------------------------


	//---------------------------------------------------------------------------------------------------------------------------------------------------------------
	// RECUSAR ---------------------------------------------------------------------------------------------------------------------------------------
	//---------------------------------------------------------------------------------------------------------------------------------------------------------------
	// recusar ------------------------------------------------------------------------------------------------------------
	if ($_GET['acao'] == "Recusar" and $_GET["resposta"] == "" and $row_suporte['status_recusa'] != "1") {

		// analisada
		if ($_GET['situacao'] == "analisada" and $row_suporte['status_devolucao'] == "") {

			$dados_suporte = array(
				"interacao" => $row_suporte['interacao'] + 1,
				"status" => "pendente usuario responsavel",
				"status_devolucao" => "",
				"status_recusa" => "1"
			);
			$dados_suporte_descricao = array(
				"id_suporte" => $row_suporte['id'],
				"id_usuario_responsavel" => $row_usuario['IdUsuario'],
				"descricao" => $_POST['observacao'],
				"data" => date("Y-m-d H:i:s"),
				"tipo_postagem" => "Suporte recusado por usuário responsável"
			);

			funcao_suporte_update($row_suporte['id'], $dados_suporte, $dados_suporte_descricao);
		}
		// fim - analisada	

		// devolucao
		if ($row_suporte['status_devolucao'] == "1") {

			// parceiro
			if ($tipo_suporte_inloco == "p") {

				$dados_suporte = array(
					"interacao" => $row_suporte['interacao'] + 1,
					"situacao" => "em validação",
					"status" => "pendente usuario envolvido",

					"encaminhamento_id" => "",
					"encaminhamento_data_inicio" => "",
					"encaminhamento_data" => "",

					"solicita_suporte" => "n",
					"solicita_visita" => "n",

					"status_devolucao" => "",
					"status_recusa" => ""

				);
				$dados_suporte_descricao = array(
					"id_suporte" => $row_suporte['id'],
					"id_usuario_responsavel" => $row_usuario['IdUsuario'],
					"descricao" => $_POST['observacao'],
					"data" => date("Y-m-d H:i:s"),
					"tipo_postagem" => "Devolução recusada por usuário responsável"
				);

				funcao_suporte_update($row_suporte['id'], $dados_suporte, $dados_suporte_descricao);
			}
			// fim - parceiro

			// cliente inloco: sim - cobrançca
			if ($tipo_suporte_inloco == "cs" and $row_suporte['cobranca'] == "s") {

				$dados_suporte = array(
					"interacao" => $row_suporte['interacao'] + 1,
					"situacao" => "em validação",
					"status" => "pendente controlador de suporte",

					"encaminhamento_id" => "",
					"encaminhamento_data_inicio" => "",
					"encaminhamento_data" => "",

					"solicita_suporte" => "n",
					"solicita_visita" => "n",

					"status_devolucao" => "",
					"status_recusa" => ""

				);
				$dados_suporte_descricao = array(
					"id_suporte" => $row_suporte['id'],
					"id_usuario_responsavel" => $row_usuario['IdUsuario'],
					"descricao" => $_POST['observacao'],
					"data" => date("Y-m-d H:i:s"),
					"tipo_postagem" => "Devolução recusada por usuário responsável"
				);

				funcao_suporte_update($row_suporte['id'], $dados_suporte, $dados_suporte_descricao);
			}
			// fim - cliente inloco: sim - cobrançca

			// cliente inloco: sim - Extra/Treinamento(n)
			if (
				$tipo_suporte_inloco == "cs" and 
				(
					$row_suporte['tipo_formulario'] == "Extra" or 
					($row_suporte['tipo_formulario'] == "Treinamento" and $row_suporte['creditar'] == "n")
				)
			) {

				$dados_suporte = array(
					"interacao" => $row_suporte['interacao'] + 1,
					"situacao" => "em validação",
					"status" => "pendente controlador de suporte",

					"encaminhamento_id" => "",
					"encaminhamento_data_inicio" => "",
					"encaminhamento_data" => "",

					"solicita_suporte" => "n",
					"solicita_visita" => "n",

					"status_devolucao" => "",
					"status_recusa" => ""

				);
				$dados_suporte_descricao = array(
					"id_suporte" => $row_suporte['id'],
					"id_usuario_responsavel" => $row_usuario['IdUsuario'],
					"descricao" => $_POST['observacao'],
					"data" => date("Y-m-d H:i:s"),
					"tipo_postagem" => "Devolução recusada por usuário responsável"
				);

				funcao_suporte_update($row_suporte['id'], $dados_suporte, $dados_suporte_descricao);
			}
			// fim - cliente inloco: sim - Extra/Treinamento(n)

		}
		// fim - devolucao

	}
	// fim - recusar ------------------------------------------------------------------------------------------------------

	// recusar recusa -----------------------------------------------------------------------------------------------------
	if ($_GET['acao'] == "Recusar" and $_GET["resposta"] == "" and $row_suporte['status_recusa'] == "1") {

		// analisada
		if ($_GET['situacao'] == "analisada") {

			$dados_suporte = array(
				"interacao" => $row_suporte['interacao'] + 1,
				"status" => "encaminhada para usuario responsavel",
				"status_devolucao" => "",
				"status_recusa" => ""
			);
			$dados_suporte_descricao = array(
				"id_suporte" => $row_suporte['id'],
				"id_usuario_responsavel" => $row_usuario['IdUsuario'],
				"descricao" => $_POST['observacao'],
				"data" => date("Y-m-d H:i:s"),
				"tipo_postagem" => "Recusa negada"
			);

			funcao_suporte_update($row_suporte['id'], $dados_suporte, $dados_suporte_descricao);
		}
		// fim - analisada		

	}
	// fim - recusar recusa -----------------------------------------------------------------------------------------------
	//---------------------------------------------------------------------------------------------------------------------------------------------------------------


	//---------------------------------------------------------------------------------------------------------------------------------------------------------------
	// Encerrar
	if (($_GET['situacao'] == "editar") and $_GET["acao"] == "Encerrar") {

		if ($tipo_suporte_inloco == "cs" or $tipo_suporte_inloco == "cn" or $tipo_suporte_inloco == "p") {

			// com validação (parceiro)
			if (@$_POST['confirmacao_encerrar_cancelar'] == "sim") {

				// parceiro - com validação do usuario_envolvido
				if ($tipo_suporte_inloco == "p") {

					$dados_suporte = array(
						"interacao" => $row_suporte['interacao'] + 1,
						"situacao" => "em validação",
						"status" => "pendente usuario envolvido",

						"acao" => $_GET['acao'],

						"previsao_geral_inicio" => date("Y-m-d H:i:s"),
						"previsao_geral" => date("Y-m-d H:i:s"),

						"data_suporte_fim" => $_POST['data_suporte_fim'],
						"parecer" => $_POST['suporte_tipo_parecer'],

						"cobranca_recebimento"  => @$_POST['confirmacao_recebimento'],
						"cobranca_recebimento_justificativa"  => @$_POST['cobranca_recebimento_justificativa'],
						"cobranca_documento_vinculado"  => @$_POST['cobranca_documento_vinculado'],

						"solicita_suporte" => "n",
						"solicita_visita" => "n",

						"solicita_solicitacao" => "n",

						"final_situacao" => "",
						"final_status" => "",
						"final_parecer" => "",
						"final_solicita_suporte" => "",
						"final_solicita_visita" => ""
					);
					$dados_suporte_descricao = array(
						"id_suporte" => $row_suporte['id'],
						"id_usuario_responsavel" => $row_usuario['IdUsuario'],
						"descricao" => "Entrega para parceiro validar encerramento.<br>" . $_POST['observacao'],
						"data" => date("Y-m-d H:i:s"),
						"tipo_postagem" => "Encerramento de suporte"
					);
				}
				// fim - parceiro - com validação do usuario_envolvido

				// cobrança - com validação do controlador de suporte
				if ($row_suporte['cobranca'] == "s") {

					$dados_suporte = array(
						"interacao" => $row_suporte['interacao'] + 1,
						"situacao" => "em validação",
						"status" => "pendente controlador de suporte",

						"acao" => $_GET['acao'],

						"encaminhamento_id" => "",
						"encaminhamento_data_inicio" => "",
						"encaminhamento_data" => "",

						"status_devolucao" => "",
						"status_recusa" => "",

						"solicita_suporte" => "n",
						"solicita_visita" => "n",

						"solicita_solicitacao" => "n",

						"previsao_geral_inicio" => date("Y-m-d H:i:s"),
						"previsao_geral" => date("Y-m-d H:i:s"),

						"parecer" => $_POST['suporte_tipo_parecer'],
						"cobranca_recebimento"  => @$_POST['confirmacao_recebimento'],
						"cobranca_recebimento_justificativa"  => @$_POST['cobranca_recebimento_justificativa'],
						"cobranca_documento_vinculado"  => @$_POST['cobranca_documento_vinculado']
					);
					$dados_suporte_descricao = array(
						"id_suporte" => $row_suporte['id'],
						"id_usuario_responsavel" => $row_usuario['IdUsuario'],
						"descricao" => "Entrega para controlador de suporte validar encerramento.<br>" . $_POST['observacao'],
						"data" => date("Y-m-d H:i:s"),
						"tipo_postagem" => "Encerramento de suporte"
					);

				}
				// fim - cobrançca - com validação do controlador de suporte

				// Extra/Treinamento(n) - com validação do controlador de suporte
				if (
					$tipo_suporte_inloco == "cs" and 
					(
						$row_suporte['tipo_formulario'] == "Extra" or 
						($row_suporte['tipo_formulario'] == "Treinamento" and $row_suporte['creditar'] == "n")
					)
				) {

					$dados_suporte = array(
						"interacao" => $row_suporte['interacao'] + 1,
						"situacao" => "em validação",
						"status" => "pendente controlador de suporte",

						"acao" => $_GET['acao'],

						"encaminhamento_id" => "",
						"encaminhamento_data_inicio" => "",
						"encaminhamento_data" => "",

						"status_devolucao" => "",
						"status_recusa" => "",

						"solicita_suporte" => "n",
						"solicita_visita" => "n",

						"solicita_solicitacao" => "n",

						"previsao_geral_inicio" => date("Y-m-d H:i:s"),
						"previsao_geral" => date("Y-m-d H:i:s"),

						"data_suporte_fim" => $_POST['data_suporte_fim'],
						"parecer" => $_POST['suporte_tipo_parecer']
					);
					$dados_suporte_descricao = array(
						"id_suporte" => $row_suporte['id'],
						"id_usuario_responsavel" => $row_usuario['IdUsuario'],
						"descricao" => "Entrega para controlador de suporte validar encerramento.<br>" . $_POST['observacao'],
						"data" => date("Y-m-d H:i:s"),
						"tipo_postagem" => "Encerramento de suporte"
					);

				}
				// fim - Extra/Treinamento(n) - com validação do controlador de suporte

				if ($row_suporte['tipo_formulario'] == "Treinamento" and $row_suporte['creditar'] == "n"){

					$dados_suporte = array(
						"interacao" => $row_suporte['interacao'] + 1,
						"situacao" => "em validação",
						"status" => "pendente controlador de suporte",

						"acao" => $_GET['acao'],

						"encaminhamento_id" => "",
						"encaminhamento_data_inicio" => "",
						"encaminhamento_data" => "",

						"status_devolucao" => "",
						"status_recusa" => "",

						"solicita_suporte" => "n",
						"solicita_visita" => "n",

						"solicita_solicitacao" => "n",

						"previsao_geral_inicio" => date("Y-m-d H:i:s"),
						"previsao_geral" => date("Y-m-d H:i:s"),

						"data_suporte_fim" => $_POST['data_suporte_fim'],
						"parecer" => $_POST['suporte_tipo_parecer']
					);
					$dados_suporte_descricao = array(
						"id_suporte" => $row_suporte['id'],
						"id_usuario_responsavel" => $row_usuario['IdUsuario'],
						"descricao" => "Entrega para controlador de suporte validar encerramento.<br>" . $_POST['observacao'],
						"data" => date("Y-m-d H:i:s"),
						"tipo_postagem" => "Encerramento de suporte"
					);

				}

			}
			// fim - com validação (parceiro)

			// sem validação
			else {

				$dados_suporte = array(
					"interacao" => $row_suporte['interacao'] + 1,
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

					"solicita_solicitacao" => "n",

					"previsao_geral_inicio" => "",
					"previsao_geral" => "",

					"data_suporte_fim" => $_POST['data_suporte_fim'],

					"parecer" => $_POST['suporte_tipo_parecer'],
					"cobranca_recebimento"  => @$_POST['confirmacao_recebimento'],
					"cobranca_recebimento_justificativa"  => @$_POST['cobranca_recebimento_justificativa'],
					"cobranca_documento_vinculado"  => @$_POST['cobranca_documento_vinculado'],

					"final_situacao" => $row_suporte['situacao'],
					"final_status" => $row_suporte['status'],
					"final_parecer" => $row_suporte['parecer'],
					"final_solicita_suporte" => $row_suporte['solicita_suporte'],
					"final_solicita_visita" => $row_suporte['solicita_visita'], 

					"atendimento" => NULL, 
					"atendimento_cliente" => NULL, 
					"atendimento_IdUsuario" => NULL, 
					"atendimento_data" => NULL, 
					"atendimento_local" => NULL, 
					"atendimento_previsao" => NULL, 
					"atendimento_texto" => NULL
				);
				$dados_suporte_descricao = array(
					"id_suporte" => $row_suporte['id'],
					"id_usuario_responsavel" => $row_usuario['IdUsuario'],
					"descricao" => "Encerramento de suporte<br>" . $_POST['observacao'],
					"data" => date("Y-m-d H:i:s"),
					"tipo_postagem" => "Encerrado"
				);

				//region - atualiza o formulário em anexo caso exista
				$query_select_cancelar_formulario = sprintf("
				SELECT 
					IdFormulario  
				FROM 
					suporte_formulario 
				WHERE 
					id_suporte = %s", 
				GetSQLValueString($row_suporte['id'], "int"));
				$select_cancelar_formulario = mysql_query($query_select_cancelar_formulario, $conexao) or die(mysql_error());
				$row_select_cancelar_formulario = mysql_fetch_assoc($select_cancelar_formulario);
				$totalRows_select_cancelar_formulario = mysql_num_rows($select_cancelar_formulario);

				if ($totalRows_select_cancelar_formulario > 0) {

					$updateSQL_formulario = sprintf("
					UPDATE 
						suporte_formulario 
					SET 
						status_flag = 'a', 
						situacao = 'encerrado' 
					
					WHERE 
						id_suporte = %s
					", GetSQLValueString($row_suporte['id'], "int"));
					mysql_select_db($database_conexao, $conexao);
					$Result_updateSQL_formulario = mysql_query($updateSQL_formulario, $conexao) or die(mysql_error());

				}
				mysql_free_result($select_cancelar_formulario);
				//endregion - fim - atualiza o formulário em anexo caso exista

				//region - update 'agenda'
				$updateSQL_suporte_agenda = sprintf(
				"
				UPDATE agenda 
				SET status=%s 
				WHERE id_suporte=%s and status='a'",
				GetSQLValueString("f", "text"),

				GetSQLValueString($row_suporte['id'], "int")
				);

				mysql_select_db($database_conexao, $conexao);
				$Result_suporte_agenda = mysql_query($updateSQL_suporte_agenda, $conexao) or die(mysql_error());
				//endregion - fim - update 'agenda'

			}
			// fim - sem validação

		}

		if ($tipo_suporte_inloco == "r") {

			$dados_suporte = array(
				"interacao" => $row_suporte['interacao'] + 1,
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

				"data_suporte_fim" => date("Y-m-d H:i:s"),

				"final_situacao" => $row_suporte['situacao'],
				"final_status" => $row_suporte['status'],
				"final_solicita_suporte" => $row_suporte['solicita_suporte'],
				"final_solicita_visita" => $row_suporte['solicita_visita'], 

				"atendimento" => NULL, 
				"atendimento_cliente" => NULL, 
				"atendimento_IdUsuario" => NULL, 
				"atendimento_data" => NULL, 
				"atendimento_local" => NULL, 
				"atendimento_previsao" => NULL, 
				"atendimento_texto" => NULL
			);
			$dados_suporte_descricao = array(
				"id_suporte" => $row_suporte['id'],
				"id_usuario_responsavel" => $row_usuario['IdUsuario'],
				"descricao" => "Encerramento de suporte<br>" . $_POST['observacao'],
				"data" => date("Y-m-d H:i:s"),
				"tipo_postagem" => "Encerrado"
			);
		}

		funcao_suporte_update($row_suporte['id'], $dados_suporte, $dados_suporte_descricao);
		tempo_gasto($row_suporte['id'], $row_suporte['situacao'], $_GET['acao'], $row_usuario['IdUsuario'], '', $_POST['tempo_gasto']);
	}
	// fim - Encerrar
	//---------------------------------------------------------------------------------------------------------------------------------------------------------------
	// Fechar suporte sem gerar solicitação (p)
	if (($_GET['situacao'] == "editar") and $_GET["acao"] == "Fechar suporte sem gerar solicitação" and $tipo_suporte_inloco == "p") {

		$dados_suporte = array(
			"interacao" => $row_suporte['interacao'] + 1,
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

			"previsao_geral_inicio" => date("Y-m-d H:i:s"),
			"previsao_geral" => date("Y-m-d H:i:s"),

			"data_suporte_fim" => date("Y-m-d H:i:s"),

			"final_situacao" => $row_suporte['situacao'],
			"final_status" => $row_suporte['status'],
			"final_parecer" => $row_suporte['parecer'],
			"final_solicita_suporte" => $row_suporte['solicita_suporte'],
			"final_solicita_visita" => $row_suporte['solicita_visita'],

			"avaliacao_atendimento" => @$_POST['avaliacao_atendimento'],
			"avaliacao_atendimento_justificativa" => @$_POST['avaliacao_atendimento_justificativa'],

			"solucionado" => $_POST['solucionado'],
			"solucionado_nao" => @$_POST['solucionado_nao'], 

			"atendimento" => NULL, 
			"atendimento_cliente" => NULL, 
			"atendimento_IdUsuario" => NULL, 
			"atendimento_data" => NULL, 
			"atendimento_local" => NULL, 
			"atendimento_previsao" => NULL, 
			"atendimento_texto" => NULL
		);
		$dados_suporte_descricao = array(
			"id_suporte" => $row_suporte['id'],
			"id_usuario_responsavel" => $row_usuario['IdUsuario'],
			"descricao" => $_POST['observacao'],
			"data" => date("Y-m-d H:i:s"),
			"tipo_postagem" => "Encerrado sem gerar solicitação"
		);
		funcao_suporte_update($row_suporte['id'], $dados_suporte, $dados_suporte_descricao);
		tempo_gasto($row_suporte['id'], $row_suporte['situacao'], $_GET['acao'], $row_usuario['IdUsuario'], '', $_POST['tempo_gasto']);
	}
	// fim - Fechar suporte sem gerar solicitação (p)
	//---------------------------------------------------------------------------------------------------------------------------------------------------------------
	// Cancelar
	if (($_GET['situacao'] == "editar") and $_GET["acao"] == "Cancelar") {

		// com validação do 'parceiro' (p)
		if (@$_POST['confirmacao_encerrar_cancelar'] == "sim") {

			$dados_suporte = array(
				"interacao" => $row_suporte['interacao'] + 1,
				"situacao" => "em validação",
				"status" => "pendente usuario envolvido",

				"encaminhamento_id" => "",
				"encaminhamento_data_inicio" => "",
				"encaminhamento_data" => "",

				"status_devolucao" => "",
				"status_recusa" => "",

				"solicita_suporte" => "n",
				"solicita_visita" => "n",

				"solicita_solicitacao" => "n",

				"acao" => $_GET['acao'],

				"previsao_geral_inicio" => date("Y-m-d H:i:s"),
				"previsao_geral" => date("Y-m-d H:i:s"),

				"data_suporte_fim" => $_POST['data_suporte_fim'],
				"parecer" => $_POST['suporte_tipo_parecer'],

				"cobranca_recebimento"  => @$_POST['confirmacao_recebimento'],
				"cobranca_recebimento_justificativa"  => @$_POST['cobranca_recebimento_justificativa'],
				"cobranca_documento_vinculado"  => @$_POST['cobranca_documento_vinculado'],

				"final_situacao" => "",
				"final_status" => "",
				"final_parecer" => "",
				"final_solicita_suporte" => "",
				"final_solicita_visita" => ""

			);
			$dados_suporte_descricao = array(
				"id_suporte" => $row_suporte['id'],
				"id_usuario_responsavel" => $row_usuario['IdUsuario'],
				"descricao" => "Entrega para parceiro validar cancelamento.<br>" . $_POST['observacao'],
				"data" => date("Y-m-d H:i:s"),
				"tipo_postagem" => "Cancelamento de suporte"
			);
			funcao_suporte_update($row_suporte['id'], $dados_suporte, $dados_suporte_descricao);
			tempo_gasto($row_suporte['id'], $row_suporte['situacao'], $_GET['acao'], $row_usuario['IdUsuario'], '', $_POST['tempo_gasto']);
		}
		// fim - com validação do 'parceiro' (p)

		// sem validação do 'parceiro'
		else {

			$dados_suporte = array(
				"interacao" => $row_suporte['interacao'] + 1,
				"situacao" => "cancelada",
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

				"solicita_solicitacao" => "n",

				"previsao_geral_inicio" => "",
				"previsao_geral" => "",

				"data_suporte_fim" => $_POST['data_suporte_fim'],
				"parecer" => $_POST['suporte_tipo_parecer'],

				"final_situacao" => $row_suporte['situacao'],
				"final_status" => $row_suporte['status'],
				"final_parecer" => $row_suporte['parecer'],
				"final_solicita_suporte" => $row_suporte['solicita_suporte'],
				"final_solicita_visita" => $row_suporte['solicita_visita'],

				"credito" => "", 

				"atendimento" => NULL, 
				"atendimento_cliente" => NULL, 
				"atendimento_IdUsuario" => NULL, 
				"atendimento_data" => NULL, 
				"atendimento_local" => NULL, 
				"atendimento_previsao" => NULL, 
				"atendimento_texto" => NULL
			);
			$dados_suporte_descricao = array(
				"id_suporte" => $row_suporte['id'],
				"id_usuario_responsavel" => $row_usuario['IdUsuario'],
				"descricao" => "Cancelamento de suporte<br>" . $_POST['observacao'],
				"data" => date("Y-m-d H:i:s"),
				"tipo_postagem" => "Cancelado"
			);
			funcao_suporte_update($row_suporte['id'], $dados_suporte, $dados_suporte_descricao);
			tempo_gasto($row_suporte['id'], $row_suporte['situacao'], $_GET['acao'], $row_usuario['IdUsuario'], '', $_POST['tempo_gasto']);

			// (cs)
			if ($tipo_suporte_inloco == "cs") {

				// busca o 'suporte_formulario'
				$query_select_cancelar_formulario = sprintf("
			SELECT IdFormulario, tipo_visita, tipo_formulario, creditar, data, visita_bonus  
			FROM suporte_formulario 
			WHERE id_suporte = %s", GetSQLValueString($row_suporte['id'], "int"));
				$select_cancelar_formulario = mysql_query($query_select_cancelar_formulario, $conexao) or die(mysql_error());
				$row_select_cancelar_formulario = mysql_fetch_assoc($select_cancelar_formulario);
				$totalRows_select_cancelar_formulario = mysql_num_rows($select_cancelar_formulario);
				// fim - busca o 'suporte_formulario'

				// se existe 'suporte_formulario'
				if ($totalRows_select_cancelar_formulario > 0) {

					// cancela suporte_formulario
					$updateSQL_formulario = sprintf("
				UPDATE suporte_formulario 
				SET 
				status_flag = 'c', 
				credito = NULL, 
				situacao = 'cancelado' 
				
				WHERE id_suporte = %s", GetSQLValueString($row_suporte['id'], "int"));
					mysql_select_db($database_conexao, $conexao);
					$Result_updateSQL_formulario = mysql_query($updateSQL_formulario, $conexao) or die(mysql_error());
					// fim - cancela suporte_formulario

					// credito
					if (
						($row_select_cancelar_formulario['tipo_visita'] == "Mensal" or
							$row_select_cancelar_formulario['tipo_visita'] == "Trimestral")
						and
						($row_select_cancelar_formulario['tipo_formulario'] == "Manutencao" or
							// $row_select_cancelar_formulario['tipo_formulario']=="Cobranca" or 
							($row_select_cancelar_formulario['tipo_formulario'] == "Treinamento" and $row_select_cancelar_formulario['creditar'] == "s") or
							($row_select_cancelar_formulario['tipo_formulario'] == "Treinamento" and $row_select_cancelar_formulario['creditar'] == "s")) and
						$row_select_cancelar_formulario['visita_bonus'] <> "s"
					) {

						// busca o 'geral_credito'
						mysql_select_db($database_conexao, $conexao);
						$query_geral_credito_atual = sprintf(
							"
					SELECT IdCredito, data_criacao  
					FROM geral_credito 
					WHERE contrato = %s and status = 1 and data_utilizacao IS NOT NULL
					ORDER BY data_criacao DESC LIMIT 1",
							GetSQLValueString($row_suporte['contrato'], "text")
						);
						$geral_credito_atual = mysql_query($query_geral_credito_atual, $conexao) or die(mysql_error());
						$row_geral_credito_atual = mysql_fetch_assoc($geral_credito_atual);
						$totalRows_geral_credito_atual = mysql_num_rows($geral_credito_atual);
						// fim - busca o 'geral_credito'

						// se crédito existe, então atualiza
						if ($totalRows_geral_credito_atual > 0) {

							// update - geral_credito
							$updateSQL_credito = sprintf(
								"
											 UPDATE geral_credito SET data_utilizacao = NULL, data_cancelamento = %s WHERE IdCredito = %s",
								GetSQLValueString(date('Y-m-d H:i:s'), "date"),
								GetSQLValueString($row_geral_credito_atual['IdCredito'], "int")
							);
							mysql_select_db($database_conexao, $conexao);
							$Result_credito = mysql_query($updateSQL_credito, $conexao) or die(mysql_error());
							// fim - update - geral_credito

						}
						// fim - se crédito existe, então atualiza

						// se crédito NÃO existe, então insere 
						else {

							$tipo_visita_atual = "";
							if ($row_select_cancelar_formulario['tipo_visita'] == "Mensal") {
								$tipo_visita_atual = 3;
							}
							if ($row_select_cancelar_formulario['tipo_visita'] == "Trimestral") {
								$tipo_visita_atual = 4;
							}

							// insert - geral_credito
							$insertSQL_credito = sprintf(
								"
													 INSERT INTO geral_credito (contrato, tipo_visita, data_criacao, status) VALUES (%s, %s, %s, %s)",
								GetSQLValueString($row_suporte['contrato'], "text"),
								GetSQLValueString($tipo_visita_atual, "text"),
								GetSQLValueString($row_select_cancelar_formulario['data'], "date"),
								GetSQLValueString('1', "int")
							);
							mysql_select_db($database_conexao, $conexao);
							$Result_credito = mysql_query($insertSQL_credito, $conexao) or die(mysql_error());
							// fim - insert - geral_credito

						}
						// fim - se crédito NÃO existe, então insere
					}
					// fim - credito

				}
				// fim - se existe 'suporte_formulario'

				mysql_free_result($select_cancelar_formulario);
			}
			// fim - (cs)

			// update 'agenda'
			$updateSQL_suporte_agenda = sprintf(
				"
											   UPDATE agenda 
											   SET status=%s 
											   WHERE id_suporte=%s and status='a'",
				GetSQLValueString("c", "text"),

				GetSQLValueString($row_suporte['id'], "int")
			);

			mysql_select_db($database_conexao, $conexao);
			$Result_suporte_agenda = mysql_query($updateSQL_suporte_agenda, $conexao) or die(mysql_error());
			// fim - update 'agenda'

		}
		// fim - sem validação do 'parceiro'

	}
	// fim - Cancelar
	//---------------------------------------------------------------------------------------------------------------------------------------------------------------
	// Bloquear (cs)
	if (($_GET['situacao'] == "editar") and $_GET["acao"] == "Bloquear" and $tipo_suporte_inloco == "cs") {

		$dados_suporte = array(
			"interacao" => $row_suporte['interacao'] + 1,
			"status" => "pendente controlador de suporte",
			"status_flag" => "b"
		);
		$dados_suporte_descricao = array(
			"id_suporte" => $row_suporte['id'],
			"id_usuario_responsavel" => $row_usuario['IdUsuario'],
			"descricao" => $_POST['observacao'],
			"data" => date("Y-m-d H:i:s"),
			"tipo_postagem" => "Bloqueio de suporte"
		);
		funcao_suporte_update($row_suporte['id'], $dados_suporte, $dados_suporte_descricao);

		// Bloqueia o formulário em anexo caso exista
		$query_select_cancelar_formulario = sprintf("
					 SELECT IdFormulario  
					 FROM suporte_formulario 
					 WHERE id_suporte = %s", GetSQLValueString($row_suporte['id'], "int"));
		$select_cancelar_formulario = mysql_query($query_select_cancelar_formulario, $conexao) or die(mysql_error());
		$row_select_cancelar_formulario = mysql_fetch_assoc($select_cancelar_formulario);
		$totalRows_select_cancelar_formulario = mysql_num_rows($select_cancelar_formulario);

		if ($totalRows_select_cancelar_formulario > 0) {

			$updateSQL_formulario = sprintf("
										UPDATE suporte_formulario 
										SET 
										status_flag = 'b', 
										situacao = 'bloqueado' 
										
										WHERE id_suporte = %s", GetSQLValueString($row_suporte['id'], "int"));
			mysql_select_db($database_conexao, $conexao);
			$Result_updateSQL_formulario = mysql_query($updateSQL_formulario, $conexao) or die(mysql_error());
		}
		mysql_free_result($select_cancelar_formulario);
		// fim - Bloqueia o formulário em anexo caso exista
	}
	// fim - Bloquear (cs)
	//---------------------------------------------------------------------------------------------------------------------------------------------------------------
	// Desbloquear (cs)
	if (($_GET['situacao'] == "editar") and $_GET["acao"] == "Desbloquear" and $tipo_suporte_inloco == "cs") {

		$dados_suporte = array(
			"interacao" => $row_suporte['interacao'] + 1,
			// "situacao" => 'em execução',
			"status" => "pendente usuario responsavel",
			"status_flag" => "a",
			"ordem_servico" => $_POST['ordem_servico']
		);
		$dados_suporte_descricao = array(
			"id_suporte" => $row_suporte['id'],
			"id_usuario_responsavel" => $row_usuario['IdUsuario'],
			"descricao" => $_POST['observacao'] . "<br>OS: " . $_POST['ordem_servico'],
			"data" => date("Y-m-d H:i:s"),
			"tipo_postagem" => "Desbloqueio de suporte"
		);
		funcao_suporte_update($row_suporte['id'], $dados_suporte, $dados_suporte_descricao);

		// desbloqueia o formulário em anexo caso exista
		$query_select_cancelar_formulario = sprintf("
					 SELECT IdFormulario  
					 FROM suporte_formulario 
					 WHERE id_suporte = %s", GetSQLValueString($row_suporte['id'], "int"));
		$select_cancelar_formulario = mysql_query($query_select_cancelar_formulario, $conexao) or die(mysql_error());
		$row_select_cancelar_formulario = mysql_fetch_assoc($select_cancelar_formulario);
		$totalRows_select_cancelar_formulario = mysql_num_rows($select_cancelar_formulario);

		if ($totalRows_select_cancelar_formulario > 0) {

			$updateSQL_formulario = sprintf("
										UPDATE suporte_formulario 
										SET 
										status_flag = 'a', 
										situacao = 'autorizado' 
										
										WHERE id_suporte = %s", GetSQLValueString($row_suporte['id'], "int"));
			mysql_select_db($database_conexao, $conexao);
			$Result_updateSQL_formulario = mysql_query($updateSQL_formulario, $conexao) or die(mysql_error());
		}
		mysql_free_result($select_cancelar_formulario);
		// fim - desbloqueia o formulário em anexo caso exista

	}
	// fim - Desbloquear (cs)
	//---------------------------------------------------------------------------------------------------------------------------------------------------------------
	// Concluir execução
	if (($_GET['situacao'] == "editar") and $_GET["acao"] == "Concluir execução") {

		if ($tipo_suporte_inloco == "r") {

			$dados_suporte = array(
				"interacao" => $row_suporte['interacao'] + 1,
				"situacao" => "em validação",
				"status" => "pendente usuario responsavel",

				"acao" => $_GET['acao'],

				"data_inicio" => date("Y-m-d H:i:s"),
				"data_fim" => date("Y-m-d H:i:s"),

				"previsao_geral_inicio" => date("Y-m-d H:i:s"),
				"previsao_geral" => date("Y-m-d H:i:s"),

				"solicita_suporte" => "n",
				"solicita_visita" => "n",

				"solicita_solicitacao" => "n",

				"final_situacao" => "",
				"final_status" => "",
				"final_parecer" => "",
				"final_solicita_suporte" => "",
				"final_solicita_visita" => ""
			);
			$dados_suporte_descricao = array(
				"id_suporte" => $row_suporte['id'],
				"id_usuario_responsavel" => $row_usuario['IdUsuario'],
				"descricao" => "Enviada para validação.<br>" . $_POST['observacao'],
				"data" => date("Y-m-d H:i:s"),
				"tipo_postagem" => "Conclusão de execução"
			);

			funcao_suporte_update($row_suporte['id'], $dados_suporte, $dados_suporte_descricao);
			tempo_gasto($row_suporte['id'], $row_suporte['situacao'], $_GET['acao'], $row_usuario['IdUsuario'], '', $_POST['tempo_gasto']);
		}
	}
	// fim - Concluir execução
	//---------------------------------------------------------------------------------------------------------------------------------------------------------------
	// Concluir validação
	if (($_GET['situacao'] == "editar") and $_GET["acao"] == "Concluir validação") {

		// parceiro
		if ($tipo_suporte_inloco == "p") {

			$tipo_postagem_label = "Encerrado";
			$descricao_label = "Encerramento de suporte<br>" . $_POST['observacao'];
			if(@$_POST['solucionado'] == "n"){
				$tipo_postagem_label = "Encerrado sem Resolução";
				$descricao_label = $descricao_label."<br>".$_POST['solucionado_nao'];
			}

			// se validação é de um 'Encerramento'
			if ($row_suporte['acao'] == "Encerrar") {

				$dados_suporte = array(
					"interacao" => $row_suporte['interacao'] + 1,
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

					"data_suporte_fim" => date("Y-m-d H:i:s"),

					"final_situacao" => $row_suporte['situacao'],
					"final_status" => $row_suporte['status'],
					"final_parecer" => $row_suporte['parecer'],
					"final_solicita_suporte" => $row_suporte['solicita_suporte'],
					"final_solicita_visita" => $row_suporte['solicita_visita'],

					"avaliacao_atendimento" => $_POST['avaliacao_atendimento'],
					"avaliacao_atendimento_justificativa" => $_POST['avaliacao_atendimento_justificativa'],

					"solucionado" => @$_POST['solucionado'],
					"solucionado_nao" => @$_POST['solucionado_nao'], 

					"atendimento" => NULL, 
					"atendimento_cliente" => NULL, 
					"atendimento_IdUsuario" => NULL, 
					"atendimento_data" => NULL, 
					"atendimento_local" => NULL, 
					"atendimento_previsao" => NULL, 
					"atendimento_texto" => NULL
				);
				$dados_suporte_descricao = array(
					"id_suporte" => $row_suporte['id'],
					"id_usuario_responsavel" => $row_usuario['IdUsuario'],
					"descricao" => $descricao_label,
					"data" => date("Y-m-d H:i:s"),
					"tipo_postagem" => $tipo_postagem_label
				);
				funcao_suporte_update($row_suporte['id'], $dados_suporte, $dados_suporte_descricao);
				tempo_gasto($row_suporte['id'], $row_suporte['situacao'], $_GET['acao'], $row_usuario['IdUsuario'], '', $_POST['tempo_gasto']);
			}
			// fim - se validação é de um 'Encerramento'

			// se validação é de um 'Cancelamento'
			if ($row_suporte['acao'] == "Cancelar") {

				$dados_suporte = array(
					"interacao" => $row_suporte['interacao'] + 1,
					"situacao" => "cancelada",
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

					"data_suporte_fim" => date("Y-m-d H:i:s"),

					"final_situacao" => $row_suporte['situacao'],
					"final_status" => $row_suporte['status'],
					"final_parecer" => $row_suporte['parecer'],
					"final_solicita_suporte" => $row_suporte['solicita_suporte'],
					"final_solicita_visita" => $row_suporte['solicita_visita'],

					"avaliacao_atendimento" => $_POST['avaliacao_atendimento'],
					"avaliacao_atendimento_justificativa" => $_POST['avaliacao_atendimento_justificativa'],

					"solucionado" => @$_POST['solucionado'],
					"solucionado_nao" => @$_POST['solucionado_nao'], 

					"atendimento" => NULL, 
					"atendimento_cliente" => NULL, 
					"atendimento_IdUsuario" => NULL, 
					"atendimento_data" => NULL, 
					"atendimento_local" => NULL, 
					"atendimento_previsao" => NULL, 
					"atendimento_texto" => NULL
				);
				$dados_suporte_descricao = array(
					"id_suporte" => $row_suporte['id'],
					"id_usuario_responsavel" => $row_usuario['IdUsuario'],
					"descricao" => "Cancelamento de suporte<br>" . $_POST['observacao'],
					"data" => date("Y-m-d H:i:s"),
					"tipo_postagem" => "Cancelado"
				);
				funcao_suporte_update($row_suporte['id'], $dados_suporte, $dados_suporte_descricao);
				tempo_gasto($row_suporte['id'], $row_suporte['situacao'], $_GET['acao'], $row_usuario['IdUsuario'], '', $_POST['tempo_gasto']);

				// cancela o formulário em anexo caso exista
				$query_select_cancelar_formulario = sprintf("
							 SELECT IdFormulario  
							 FROM suporte_formulario 
							 WHERE id_suporte = %s", GetSQLValueString($row_suporte['id'], "int"));
				$select_cancelar_formulario = mysql_query($query_select_cancelar_formulario, $conexao) or die(mysql_error());
				$row_select_cancelar_formulario = mysql_fetch_assoc($select_cancelar_formulario);
				$totalRows_select_cancelar_formulario = mysql_num_rows($select_cancelar_formulario);

				if ($totalRows_select_cancelar_formulario > 0) {

					$updateSQL_formulario = sprintf("
												UPDATE suporte_formulario 
												SET 
												status_flag = 'c', 
												situacao = 'cancelado' 
												
												WHERE id_suporte = %s", GetSQLValueString($row_suporte['id'], "int"));
					mysql_select_db($database_conexao, $conexao);
					$Result_updateSQL_formulario = mysql_query($updateSQL_formulario, $conexao) or die(mysql_error());
				}
				mysql_free_result($select_cancelar_formulario);
				// fim - cancela o formulário em anexo caso exista

			}
			// fim - se validação é de um 'Cancelamento'

		}
		// fim - parceiro

		// cliente inloco: sim (cobrança)
		if ($tipo_suporte_inloco == "cs" and $row_suporte['cobranca'] == "s") {

			$dados_suporte = array(
				"interacao" => $row_suporte['interacao'] + 1,
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

				"data_suporte_fim" => date("Y-m-d H:i:s"),

				"final_situacao" => $row_suporte['situacao'],
				"final_status" => $row_suporte['status'],
				"final_parecer" => $row_suporte['parecer'],
				"final_solicita_suporte" => $row_suporte['solicita_suporte'],
				"final_solicita_visita" => $row_suporte['solicita_visita'], 

				"atendimento" => NULL, 
				"atendimento_cliente" => NULL, 
				"atendimento_IdUsuario" => NULL, 
				"atendimento_data" => NULL, 
				"atendimento_local" => NULL, 
				"atendimento_previsao" => NULL, 
				"atendimento_texto" => NULL
			);

			$dados_suporte_descricao = array(
				"id_suporte" => $row_suporte['id'],
				"id_usuario_responsavel" => $row_usuario['IdUsuario'],
				"descricao" => "Encerramento de suporte<br>" . $_POST['observacao'],
				"data" => date("Y-m-d H:i:s"),
				"tipo_postagem" => "Encerrado"
			);
			funcao_suporte_update($row_suporte['id'], $dados_suporte, $dados_suporte_descricao);
			tempo_gasto($row_suporte['id'], $row_suporte['situacao'], $_GET['acao'], $row_usuario['IdUsuario'], '', $_POST['tempo_gasto']);

			// atualiza o formulário em anexo caso exista
			$query_select_cancelar_formulario = sprintf("
						 SELECT IdFormulario  
						 FROM suporte_formulario 
						 WHERE id_suporte = %s", GetSQLValueString($row_suporte['id'], "int"));
			$select_cancelar_formulario = mysql_query($query_select_cancelar_formulario, $conexao) or die(mysql_error());
			$row_select_cancelar_formulario = mysql_fetch_assoc($select_cancelar_formulario);
			$totalRows_select_cancelar_formulario = mysql_num_rows($select_cancelar_formulario);

			if ($totalRows_select_cancelar_formulario > 0) {

				$updateSQL_formulario = sprintf("
											UPDATE suporte_formulario 
											SET 
											status_flag = 'a', 
											situacao = 'encerrado' 
											
											WHERE id_suporte = %s", GetSQLValueString($row_suporte['id'], "int"));
				mysql_select_db($database_conexao, $conexao);
				$Result_updateSQL_formulario = mysql_query($updateSQL_formulario, $conexao) or die(mysql_error());
			}
			mysql_free_result($select_cancelar_formulario);
			// fim - atualiza o formulário em anexo caso exista

			// update 'agenda'
			$updateSQL_suporte_agenda = sprintf(
				"
											   UPDATE agenda 
											   SET status=%s 
											   WHERE id_suporte=%s and status='a'",
				GetSQLValueString("f", "text"),

				GetSQLValueString($row_suporte['id'], "int")
			);

			mysql_select_db($database_conexao, $conexao);
			$Result_suporte_agenda = mysql_query($updateSQL_suporte_agenda, $conexao) or die(mysql_error());
			// fim - update 'agenda'

		}
		// fim - cliente inloco: sim (cobrança)

		// cliente inloco: sim (Extra/Treinamento(n))
		if (
			$tipo_suporte_inloco == "cs" and 
			(
				$row_suporte['tipo_formulario'] == "Extra" or 
				($row_suporte['tipo_formulario'] == "Treinamento" and $row_suporte['creditar'] == "n")
			)
		) {

			$dados_suporte = array(
				"interacao" => $row_suporte['interacao'] + 1,
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

				"solicita_solicitacao" => "n",

				"previsao_geral_inicio" => "",
				"previsao_geral" => "",

				"data_suporte_fim" => date("Y-m-d H:i:s"),

				"final_situacao" => $row_suporte['situacao'],
				"final_status" => $row_suporte['status'],
				"final_parecer" => $row_suporte['parecer'],
				"final_solicita_suporte" => $row_suporte['solicita_suporte'],
				"final_solicita_visita" => $row_suporte['solicita_visita'], 

				"atendimento" => NULL, 
				"atendimento_cliente" => NULL, 
				"atendimento_IdUsuario" => NULL, 
				"atendimento_data" => NULL, 
				"atendimento_local" => NULL, 
				"atendimento_previsao" => NULL, 
				"atendimento_texto" => NULL
			);
			
			$dados_suporte_descricao = array(
				"id_suporte" => $row_suporte['id'],
				"id_usuario_responsavel" => $row_usuario['IdUsuario'],
				"descricao" => "Encerramento de suporte<br>" . $_POST['observacao'],
				"data" => date("Y-m-d H:i:s"),
				"tipo_postagem" => "Encerrado"
			);
			funcao_suporte_update($row_suporte['id'], $dados_suporte, $dados_suporte_descricao);
			tempo_gasto($row_suporte['id'], $row_suporte['situacao'], $_GET['acao'], $row_usuario['IdUsuario'], '', $_POST['tempo_gasto']);

			// atualiza o formulário em anexo caso exista
			$query_select_cancelar_formulario = sprintf("
			SELECT 
				IdFormulario  
			FROM 
				suporte_formulario 
			WHERE 
				id_suporte = %s
			", GetSQLValueString($row_suporte['id'], "int"));
			$select_cancelar_formulario = mysql_query($query_select_cancelar_formulario, $conexao) or die(mysql_error());
			$row_select_cancelar_formulario = mysql_fetch_assoc($select_cancelar_formulario);
			$totalRows_select_cancelar_formulario = mysql_num_rows($select_cancelar_formulario);

			if ($totalRows_select_cancelar_formulario > 0) {

				$updateSQL_formulario = sprintf("
				UPDATE 
					suporte_formulario 
				SET 
					status_flag = 'a', 
					situacao = 'encerrado' 
				WHERE 
					id_suporte = %s
				", 
				GetSQLValueString($row_suporte['id'], "int"));
				mysql_select_db($database_conexao, $conexao);
				$Result_updateSQL_formulario = mysql_query($updateSQL_formulario, $conexao) or die(mysql_error());

			}
			mysql_free_result($select_cancelar_formulario);
			// fim - atualiza o formulário em anexo caso exista

			// update 'agenda'
			$updateSQL_suporte_agenda = sprintf("
			UPDATE 
				agenda 
			SET 
				status=%s 
			WHERE 
				id_suporte=%s and status='a'
			",
			GetSQLValueString("f", "text"),
			GetSQLValueString($row_suporte['id'], "int"));

			mysql_select_db($database_conexao, $conexao);
			$Result_suporte_agenda = mysql_query($updateSQL_suporte_agenda, $conexao) or die(mysql_error());
			// fim - update 'agenda'

		}
		// fim - cliente inloco: sim (Extra/Treinamento(n))

	}
	// fim - Concluir validação
	//---------------------------------------------------------------------------------------------------------------------------------------------------------------
	// Estornar
	if (
		($_GET['situacao'] == "editar" and $_GET["acao"] == "Estornar") and
		(
			(
				($row_usuario['controle_suporte'] == "Y") or
				($row_suporte['praca'] == $row_usuario['praca'] and $row_usuario['controle_praca'] == "Y")) or ($row_usuario['controle_suporte'] == "Y"))
	) {

		$dados_suporte = array(
			"interacao" => $row_suporte['interacao'] + 1,
			"situacao" => "em execução",
			"status" => "pendente usuario responsavel",
			"status_flag" => "a",

			"data_conclusao" => "", 

			"encaminhamento_id" => "",
			"encaminhamento_data_inicio" => "",
			"encaminhamento_data" => "",

			"status_devolucao" => "",
			"status_recusa" => "",

			"solicita_suporte" => "n",
			"solicita_visita" => "n",

			"previsao_geral_inicio" => "",
			"previsao_geral" => "",

			"data_suporte_fim" => "0000-00-00 00:00:00",
			"parecer" => $row_suporte['final_parecer'],

			"final_situacao" => "",
			"final_parecer" => "",
			"final_status" => "",
			"final_solicita_suporte" => "",
			"final_solicita_visita" => "",

			"estorno" => "s",
			"estorno_justificativa" => $_POST['observacao']
		);
		$dados_suporte_descricao = array(
			"id_suporte" => $row_suporte['id'],
			"id_usuario_responsavel" => $row_usuario['IdUsuario'],
			"descricao" => "Estorno de suporte<br>" . $_POST['observacao'],
			"data" => date("Y-m-d H:i:s"),
			"tipo_postagem" => "Estornado"
		);
		funcao_suporte_update($row_suporte['id'], $dados_suporte, $dados_suporte_descricao);

		// (cs)
		if ($tipo_suporte_inloco == "cs") {

			// busca o 'suporte_formulario'
			$query_select_cancelar_formulario = sprintf("
		SELECT IdFormulario, tipo_visita, tipo_formulario, creditar, data, situacao 
		FROM suporte_formulario 
		WHERE id_suporte = %s", GetSQLValueString($row_suporte['id'], "int"));
			$select_cancelar_formulario = mysql_query($query_select_cancelar_formulario, $conexao) or die(mysql_error());
			$row_select_cancelar_formulario = mysql_fetch_assoc($select_cancelar_formulario);
			$totalRows_select_cancelar_formulario = mysql_num_rows($select_cancelar_formulario);
			// fim - busca o 'suporte_formulario'

			// se existe 'suporte_formulario'
			if ($totalRows_select_cancelar_formulario > 0) {

				// autoriza suporte_formulario
				$updateSQL_formulario = sprintf(
					"
			UPDATE suporte_formulario 
			SET 
			status_flag = 'a', 
			credito = NULL, 
			situacao = 'autorizado' 			
			WHERE id_suporte = %s",
					GetSQLValueString($row_suporte['id'], "int")
				);
				mysql_select_db($database_conexao, $conexao);
				$Result_updateSQL_formulario = mysql_query($updateSQL_formulario, $conexao) or die(mysql_error());
				// fim - autoriza suporte_formulario

				// credito
				if (
					($row_select_cancelar_formulario['tipo_visita'] == "Mensal" or
						$row_select_cancelar_formulario['tipo_visita'] == "Trimestral")
					and
					($row_select_cancelar_formulario['tipo_formulario'] == "Manutencao" or
						// $row_select_cancelar_formulario['tipo_formulario']=="Cobranca" or 
						($row_select_cancelar_formulario['tipo_formulario'] == "Treinamento" and $row_select_cancelar_formulario['creditar'] == "s") or
						($row_select_cancelar_formulario['tipo_formulario'] == "Reclamacao" and $row_select_cancelar_formulario['creditar'] == "s"))
				) {

					// se vem de 'encerrado' ---------------------------------------------------------------
					if ($row_select_cancelar_formulario['situacao'] == "encerrado") {

						// busca o 'geral_credito' (procura credito já utilizado e ativo)
						mysql_select_db($database_conexao, $conexao);
						$query_geral_credito_atual = sprintf(
							"
					SELECT IdCredito, data_criacao  
					FROM geral_credito 
					WHERE contrato = %s and status = 1 and data_utilizacao IS NOT NULL
					ORDER BY data_criacao DESC LIMIT 1",
							GetSQLValueString($row_suporte['contrato'], "text")
						);
						$geral_credito_atual = mysql_query($query_geral_credito_atual, $conexao) or die(mysql_error());
						$row_geral_credito_atual = mysql_fetch_assoc($geral_credito_atual);
						$totalRows_geral_credito_atual = mysql_num_rows($geral_credito_atual);
						// fim - busca o 'geral_credito' (procura credito já utilizado e ativo)

						// se crédito NÃO existe, então insere um 'crédito' já utilizado
						if ($totalRows_geral_credito_atual == 0) {

							$tipo_visita_atual = "";
							if ($row_select_cancelar_formulario['tipo_visita'] == "Mensal") {
								$tipo_visita_atual = 3;
							}
							if ($row_select_cancelar_formulario['tipo_visita'] == "Trimestral") {
								$tipo_visita_atual = 4;
							}

							// insert - geral_credito
							$insertSQL_credito = sprintf(
								"
													 INSERT INTO geral_credito (contrato, tipo_visita, data_criacao, data_utilizacao, status) VALUES (%s, %s, %s, %s, %s)",
								GetSQLValueString($row_suporte['contrato'], "text"),
								GetSQLValueString($tipo_visita_atual, "text"),
								GetSQLValueString($row_select_cancelar_formulario['data'], "date"),
								GetSQLValueString($row_select_cancelar_formulario['data'], "date"),
								GetSQLValueString('1', "int")
							);
							mysql_select_db($database_conexao, $conexao);
							$Result_credito = mysql_query($insertSQL_credito, $conexao) or die(mysql_error());
							// fim - insert - geral_credito

						}
						// fim - se crédito NÃO existe, então insere um 'crédito' já utilizado

					}
					// fim - se vem de 'encerrado' ---------------------------------------------------------


					// se vem de 'cancelado' ---------------------------------------------------------------
					if ($row_select_cancelar_formulario['situacao'] == "cancelado") {

						// busca o 'geral_credito' (procura credito NÃO utilizado e ativo)
						mysql_select_db($database_conexao, $conexao);
						$query_geral_credito_atual = sprintf(
							"
					SELECT IdCredito, data_criacao  
					FROM geral_credito 
					WHERE contrato = %s and status = 1 and data_utilizacao IS NULL
					ORDER BY data_criacao ASC LIMIT 1",
							GetSQLValueString($row_suporte['contrato'], "text")
						);
						$geral_credito_atual = mysql_query($query_geral_credito_atual, $conexao) or die(mysql_error());
						$row_geral_credito_atual = mysql_fetch_assoc($geral_credito_atual);
						$totalRows_geral_credito_atual = mysql_num_rows($geral_credito_atual);
						// fim - busca o 'geral_credito' (procura credito NÃO utilizado e ativo)

						// se crédito existe, então atualiza para utilizado
						if ($totalRows_geral_credito_atual > 0) {

							// update - geral_credito
							$updateSQL_credito = sprintf(
								"
											 UPDATE geral_credito SET data_utilizacao = %s WHERE IdCredito = %s",
								GetSQLValueString($row_select_cancelar_formulario['data'], "date"),
								GetSQLValueString($row_geral_credito_atual['IdCredito'], "int")
							);
							mysql_select_db($database_conexao, $conexao);
							$Result_credito = mysql_query($updateSQL_credito, $conexao) or die(mysql_error());
							// fim - update - geral_credito

						}
						// fim - se crédito existe, então atualiza para utilizado

						// se crédito NÃO existe, então insere um 'crédito' já utilizado
						else {

							$tipo_visita_atual = "";
							if ($row_select_cancelar_formulario['tipo_visita'] == "Mensal") {
								$tipo_visita_atual = 3;
							}
							if ($row_select_cancelar_formulario['tipo_visita'] == "Trimestral") {
								$tipo_visita_atual = 4;
							}

							// insert - geral_credito
							$insertSQL_credito = sprintf(
								"
													 INSERT INTO geral_credito (contrato, tipo_visita, data_criacao, data_utilizacao, status) VALUES (%s, %s, %s, %s, %s)",
								GetSQLValueString($row_suporte['contrato'], "text"),
								GetSQLValueString($tipo_visita_atual, "text"),
								GetSQLValueString($row_select_cancelar_formulario['data'], "date"),
								GetSQLValueString($row_select_cancelar_formulario['data'], "date"),
								GetSQLValueString('1', "int")
							);
							mysql_select_db($database_conexao, $conexao);
							$Result_credito = mysql_query($insertSQL_credito, $conexao) or die(mysql_error());
							// fim - insert - geral_credito

						}
						// fim - se crédito NÃO existe, então insere um 'crédito' já utilizado

					}
					// fim - se vem de 'cancelado' ---------------------------------------------------------

				}
				// fim - credito

			}
			// fim - se existe 'suporte_formulario'

			mysql_free_result($select_cancelar_formulario);
		}
		// fim - (cs)

		// update 'agenda'
		$updateSQL_suporte_agenda = sprintf(
			"
	UPDATE agenda 
	SET status=%s 
	WHERE id_suporte=%s and (status='f' or status='c')",
			GetSQLValueString("a", "text"),

			GetSQLValueString($row_suporte['id'], "int")
		);

		mysql_select_db($database_conexao, $conexao);
		$Result_suporte_agenda = mysql_query($updateSQL_suporte_agenda, $conexao) or die(mysql_error());
		// fim - update 'agenda'

	}
	// fim - Estornar
	//---------------------------------------------------------------------------------------------------------------------------------------------------------------
	// Encaminhar para solicitação (p)
	if (($_GET['situacao'] == "editar") and $_GET["acao"] == "Encaminhar para solicitação" and $tipo_suporte_inloco == "p") {

		$dados_suporte = array(
			"interacao" => $row_suporte['interacao'] + 1,
			"situacao" => "encaminhado para solicitação",
			"status" => "pendente usuario envolvido",

			"acao" => "",

			"previsao_geral_inicio" => date("Y-m-d H:i:s"),
			"previsao_geral" => date("Y-m-d H:i:s"),

			"encaminhamento_id" => "",
			"encaminhamento_data_inicio" => "",
			"encaminhamento_data" => "",

			"status_devolucao" => "",
			"status_recusa" => "",

			"solicita_suporte" => "n",
			"solicita_visita" => "n",

			"solicita_solicitacao" => "s",

			"data_suporte_fim" => $_POST['data_suporte_fim'],
			"parecer" => $_POST['suporte_tipo_parecer']
		);
		$dados_suporte_descricao = array(
			"id_suporte" => $row_suporte['id'],
			"id_usuario_responsavel" => $row_usuario['IdUsuario'],
			"descricao" => $_POST['observacao'],
			"data" => date("Y-m-d H:i:s"),
			"tipo_postagem" => "Encaminhado para solicitação"
		);
		funcao_suporte_update($row_suporte['id'], $dados_suporte, $dados_suporte_descricao);
		tempo_gasto($row_suporte['id'], $row_suporte['situacao'], $_GET['acao'], $row_usuario['IdUsuario'], '', $_POST['tempo_gasto']);
	}
	// fim - Encaminhar para solicitação (p)
	//---------------------------------------------------------------------------------------------------------------------------------------------------------------
	// Solicitar suporte (cn)
	if (($_GET['situacao'] == "editar") and $_GET["acao"] == "Solicitar suporte" and $tipo_suporte_inloco == "cn") {

		
		if (
			$_POST['anomalia_simulada'] == "n" and 
			(isset($_POST['anomalia_simulada_afirmacao']) and $_POST['anomalia_simulada_afirmacao'] == "n")
			) {

			$dados_suporte = array(
				"interacao" => $row_suporte['interacao'] + 1, 

				"anomalia_simulada" => $_POST['anomalia_simulada'],
				"anomalia_simulada_afirmacao" => $_POST['anomalia_simulada_afirmacao']
			);
			$dados_suporte_descricao = array(
				"id_suporte" => $row_suporte['id'],
				"id_usuario_responsavel" => $row_usuario['IdUsuario'],
				"descricao" => $_POST['observacao'],
				"data" => date("Y-m-d H:i:s"),
				"tipo_postagem" => "Solicitação de suporte não efetivada"
			);
			
			funcao_suporte_update($row_suporte['id'], $dados_suporte, $dados_suporte_descricao);

		} else {

			$versao = NULL;
			$versao_texto = NULL;
			if (count(@$_POST['versao']) > 0) {
				$versao = implode(',', $_POST['versao']);
				$versao_texto = "<br><br>Versões: " . funcao_consulta_versao_array($versao);
			}

			$dados_suporte = array(
				"interacao" => $row_suporte['interacao'] + 1,
				"situacao" => "solicitado suporte",
				"status" => "pendente controlador de suporte",

				"versao" => $versao,

				"previsao_geral_inicio" => date("Y-m-d H:i:s"),
				"previsao_geral" => date("Y-m-d H:i:s"),

				"encaminhamento_id" => "",
				"encaminhamento_data_inicio" => "",
				"encaminhamento_data" => "",

				"status_devolucao" => "",
				"status_recusa" => "",

				"solicita_suporte" => "s",
				"solicita_visita" => "n",

				"data_solicita_suporte" => date("Y-m-d H:i:s"),
				"data_solicita_suporte_aceita_recusa" => "", 

				"anomalia_simulada" => $_POST['anomalia_simulada'],
				"anomalia_simulada_afirmacao" => ""
			);
			$dados_suporte_descricao = array(
				"id_suporte" => $row_suporte['id'],
				"id_usuario_responsavel" => $row_usuario['IdUsuario'],
				"descricao" => $_POST['observacao'] . $versao_texto,
				"data" => date("Y-m-d H:i:s"),
				"tipo_postagem" => "Solicitação de suporte"
			);
			
			funcao_suporte_update($row_suporte['id'], $dados_suporte, $dados_suporte_descricao);

		}

	}
	// fim - Solicitar suporte (cn)
	//---------------------------------------------------------------------------------------------------------------------------------------------------------------
	// Solicitar suporte - sim (cn)
	if ($_GET['acao'] == "Solicitar suporte" and $_GET["resposta"] == "sim" and $tipo_suporte_inloco == "cn") {

		$dados_suporte = array(
			"interacao" => $row_suporte['interacao'] + 1,
			"situacao" => "em execução",
			"status" => "pendente usuario responsavel",

			"previsao_geral_inicio" => "",
			"previsao_geral" => "",

			"encaminhamento_id" => "",
			"encaminhamento_data_inicio" => "",
			"encaminhamento_data" => "",

			"status_devolucao" => "",
			"status_recusa" => "",

			"solicita_suporte" => "n",
			"solicita_visita" => "n",

			"tipo_suporte" => "p",
			"inloco" => "n",

			"id_usuario_responsavel" => $row_usuario['IdUsuario'],
			"usuario_responsavel_leu" => date("Y-m-d H:i:s"),

			"id_usuario_envolvido" => $row_suporte['id_usuario_responsavel'],
			"usuario_envolvido_leu" => $row_suporte['usuario_responsavel_leu'],

			"data_solicita_suporte_aceita_recusa" => date("Y-m-d H:i:s")
		);

		$dados_suporte_descricao = array(
			"id_suporte" => $row_suporte['id'],
			"id_usuario_responsavel" => $row_usuario['IdUsuario'],
			"descricao" => $_POST['observacao'],
			"data" => date("Y-m-d H:i:s"),
			"tipo_postagem" => "Suporte aceito"
		);
		funcao_suporte_update($row_suporte['id'], $dados_suporte, $dados_suporte_descricao);
	}
	// fim - Solicitar suporte - sim (cn)
	//---------------------------------------------------------------------------------------------------------------------------------------------------------------
	// Solicitar suporte - nao (cn)
	if ($_GET['acao'] == "Solicitar suporte" and $_GET["resposta"] == "nao" and $tipo_suporte_inloco == "cn") {

		$dados_suporte = array(
			"interacao" => $row_suporte['interacao'] + 1,
			"situacao" => "em execução",
			"status" => "pendente usuario responsavel",

			"versao" => NULL,

			"previsao_geral_inicio" => "",
			"previsao_geral" => "",

			"encaminhamento_id" => "",
			"encaminhamento_data_inicio" => "",
			"encaminhamento_data" => "",

			"status_devolucao" => "",
			"status_recusa" => "",

			"solicita_suporte" => "n",
			"solicita_visita" => "n",

			"data_solicita_suporte_aceita_recusa" => date("Y-m-d H:i:s")
		);
		$dados_suporte_descricao = array(
			"id_suporte" => $row_suporte['id'],
			"id_usuario_responsavel" => $row_usuario['IdUsuario'],
			"descricao" => $_POST['observacao'],
			"data" => date("Y-m-d H:i:s"),
			"tipo_postagem" => "Suporte recusado"
		);
		funcao_suporte_update($row_suporte['id'], $dados_suporte, $dados_suporte_descricao);
	}
	// fim - Solicitar suporte - nao (cn)
	//---------------------------------------------------------------------------------------------------------------------------------------------------------------
	// Contato
	if (($_GET['situacao'] == "editar") and $_GET["acao"] == "Contato") {

		// insert - suporte_contato
		mysql_select_db($database_conexao, $conexao);
		$insertSQL_suporte_contato  = sprintf(
			"
	INSERT INTO suporte_contato (id_suporte, id_usuario_responsavel, data, responsavel, telefone, descricao) 
	VALUES (%s, %s, %s, %s, %s, %s)",
			GetSQLValueString($row_suporte['id'], "int"),
			GetSQLValueString($row_usuario['IdUsuario'], "int"),
			GetSQLValueString(date("Y-m-d H:i:s"), "date"),
			GetSQLValueString($_POST['contato_responsavel'], "text"),
			GetSQLValueString($_POST['contato_telefone'], "text"),
			GetSQLValueString($_POST['observacao'], "text")
		);

		mysql_select_db($database_conexao, $conexao);
		$Result_suporte_contato = mysql_query($insertSQL_suporte_contato, $conexao) or die(mysql_error());
		$id_suporte_contato = mysql_insert_id();
		// fim - insert - suporte_contato

		// suporte_contato (total)
		mysql_select_db($database_conexao, $conexao);
		$query_suporte_contato_total = sprintf("
	SELECT id  
	FROM suporte_contato 
	WHERE id_suporte = %s 
	ORDER BY id ASC", GetSQLValueString($row_suporte['id'], "int"));
		$suporte_contato_total = mysql_query($query_suporte_contato_total, $conexao) or die(mysql_error());
		$row_suporte_contato_total = mysql_fetch_assoc($suporte_contato_total);
		$totalRows_suporte_contato_total = mysql_num_rows($suporte_contato_total);
		mysql_free_result($suporte_contato_total);
		// fim - suporte_contato (total)

		$dados_suporte = array(
			"interacao" => $row_suporte['interacao'] + 1,
			"contato" => $totalRows_suporte_contato_total
		);
		$dados_suporte_descricao = array(
			"id_suporte" => $row_suporte['id'],
			"id_usuario_responsavel" => $row_usuario['IdUsuario'],
			"descricao" => $_POST['observacao'],
			"data" => date("Y-m-d H:i:s"),
			"tipo_postagem" => "Novo contato"
		);
		funcao_suporte_update($row_suporte['id'], $dados_suporte, $dados_suporte_descricao);
	}
	// fim - Contato
	//---------------------------------------------------------------------------------------------------------------------------------------------------------------
	// Liberar anexos (cs)
	if (($_GET['situacao'] == "editar") and $_GET["acao"] == "Liberar anexos" and $tipo_suporte_inloco == "cs") {

		$dados_suporte = array(
			"interacao" => $row_suporte['interacao'] + 1,
			"prazo_anexo_liberar" => 's'
		);
		$dados_suporte_descricao = array(
			"id_suporte" => $row_suporte['id'],
			"id_usuario_responsavel" => $row_usuario['IdUsuario'],
			"descricao" => $_POST['observacao'],
			"data" => date("Y-m-d H:i:s"),
			"tipo_postagem" => "Liberar anexos"
		);
		funcao_suporte_update($row_suporte['id'], $dados_suporte, $dados_suporte_descricao);
	}
	// fim - Liberar anexos (cs)
	//---------------------------------------------------------------------------------------------------------------------------------------------------------------
	// Bloquear anexos (cs)
	if (($_GET['situacao'] == "editar") and $_GET["acao"] == "Bloquear anexos" and $tipo_suporte_inloco == "cs") {

		$dados_suporte = array(
			"interacao" => $row_suporte['interacao'] + 1,
			"prazo_anexo_liberar" => 'n'
		);
		$dados_suporte_descricao = array(
			"id_suporte" => $row_suporte['id'],
			"id_usuario_responsavel" => $row_usuario['IdUsuario'],
			"descricao" => $_POST['observacao'],
			"data" => date("Y-m-d H:i:s"),
			"tipo_postagem" => "Bloquear anexos"
		);
		funcao_suporte_update($row_suporte['id'], $dados_suporte, $dados_suporte_descricao);
	}
	// fim - Bloquear anexos (cs)
	//---------------------------------------------------------------------------------------------------------------------------------------------------------------
	// Reagendar (cs)
	if (($_GET['situacao'] == "editar") and $_GET["acao"] == "Reagendar" and $tipo_suporte_inloco == "cs") {

		// busca usuario_responsavel selecionado
		$colname_usuario_selecionado = "-1";
		if (isset($_POST['usuario_responsavel'])) {
			$colname_usuario_selecionado = $_POST['usuario_responsavel'];
		}
		mysql_select_db($database_conexao, $conexao);
		$query_usuario_selecionado = sprintf("SELECT IdUsuario, nome, praca FROM usuarios WHERE IdUsuario = %s", GetSQLValueString($colname_usuario_selecionado, "int"));
		$usuario_selecionado = mysql_query($query_usuario_selecionado, $conexao) or die(mysql_error());
		$row_usuario_selecionado = mysql_fetch_assoc($usuario_selecionado);
		$totalRows_usuario_selecionado = mysql_num_rows($usuario_selecionado);
		// fim - busca usuario_responsavel selecionado

		$dados_suporte = array(
			"interacao" => $row_suporte['interacao'] + 1,
			"status" => $row_suporte['status'],

			"id_usuario_responsavel" => $row_usuario_selecionado['IdUsuario'],
			"usuario_responsavel_leu" => "",

			"data_inicio" => $_POST['data_inicio'],
			"data_fim" => $_POST['data_fim'],

			"previsao_geral_inicio" => "",
			"previsao_geral" => "",

			"encaminhamento_id" => "",
			"encaminhamento_data_inicio" => "",
			"encaminhamento_data" => "",

			"status_devolucao" => "",
			"status_recusa" => "",

			"reagendamento" => $row_suporte['reagendamento'] + 1,
			"reagendamento_solicitante" => $_POST['reagendamento_solicitante'], 

			"atendimento" => NULL, 
			"atendimento_cliente" => NULL, 
			"atendimento_IdUsuario" => NULL, 
			"atendimento_data" => NULL, 
			"atendimento_local" => NULL, 
			"atendimento_previsao" => NULL, 
			"atendimento_texto" => NULL
		);
		$dados_suporte_descricao = array(
			"id_suporte" => $row_suporte['id'],
			"id_usuario_responsavel" => $row_usuario['IdUsuario'],
			"descricao" => "
			Solicitante do reagendamento: " . $_POST['reagendamento_solicitante'] . "<br>" .
				"Usuário responsável anterior: " . $row_suporte['usuario_responsavel'] . " - Novo usuário responsável: " . $row_usuario_selecionado['nome'] . "<br>" .
				"Data início anterior: " . formataDataPTG($row_suporte['data_inicio']) . " - Nova data início: " . formataDataPTG($_POST['data_inicio']) . "<br>" .
				"Data fim anterior: " . formataDataPTG($row_suporte['data_fim']) . " - Nova data fim: " . formataDataPTG($_POST['data_fim']) . "<br>" . $_POST['observacao'],
			"data" => date("Y-m-d H:i:s"),
			"tipo_postagem" => "Reagendamento de suporte in-loco"
		);

		funcao_suporte_update($row_suporte['id'], $dados_suporte, $dados_suporte_descricao);

		// update 'agenda'
		$updateSQL_suporte_agenda = sprintf(
			"
										   UPDATE agenda 
										   SET id_usuario_responsavel=%s, data_inicio=%s, data=%s, descricao=%s, status=%s 
										   WHERE id_suporte=%s and id_agenda=%s",
			GetSQLValueString($row_usuario_selecionado['IdUsuario'], "int"),
			GetSQLValueString($_POST['data_inicio'], "date"),
			GetSQLValueString($_POST['data_fim'], "date"),
			GetSQLValueString($_POST['descricao_agendamento'], "text"),
			GetSQLValueString("a", "text"),

			GetSQLValueString($row_suporte['id'], "int"),
			GetSQLValueString($row_agenda['id_agenda'], "int")
		);

		mysql_select_db($database_conexao, $conexao);
		$Result_suporte_agenda = mysql_query($updateSQL_suporte_agenda, $conexao) or die(mysql_error());
		// fim - update 'agenda'

		mysql_free_result($usuario_selecionado);
	}
	// fim - Reagendar (cs)
	//---------------------------------------------------------------------------------------------------------------------------------------------------------------
	// Solicitar visita (cn)
	if (($_GET['situacao'] == "editar") and $_GET["acao"] == "Solicitar visita" and ($tipo_suporte_inloco == "cn")) {

		if ($tipo_suporte_inloco == "cn") {

			$dados_suporte = array(
				"interacao" => $row_suporte['interacao'] + 1,
				"situacao" => "solicitado visita",
				"status" => "pendente usuario responsavel",

				"previsao_geral_inicio" => date("Y-m-d H:i:s"),
				"previsao_geral" => date("Y-m-d H:i:s"),

				"encaminhamento_id" => "",
				"encaminhamento_data_inicio" => "",
				"encaminhamento_data" => "",

				"status_devolucao" => "",
				"status_recusa" => "",

				"solicita_suporte" => "n",
				"solicita_visita" => "s"
			);
			$dados_suporte_descricao = array(
				"id_suporte" => $row_suporte['id'],
				"id_usuario_responsavel" => $row_usuario['IdUsuario'],
				"descricao" => $_POST['observacao'],
				"data" => date("Y-m-d H:i:s"),
				"tipo_postagem" => "Solicitação de visita"
			);
		}

		funcao_suporte_update($row_suporte['id'], $dados_suporte, $dados_suporte_descricao);
	}
	// fim - Solicitar visita (cn)
	//---------------------------------------------------------------------------------------------------------------------------------------------------------------
	// Cancelar solicitação de visita (cn)
	if (($_GET['situacao'] == "editar") and $_GET["acao"] == "Cancelar solicitação de visita" and ($tipo_suporte_inloco == "cn")) {

		$dados_suporte = array(
			"interacao" => $row_suporte['interacao'] + 1,
			"situacao" => "em execução",
			"status" => "pendente usuario responsavel",

			"encaminhamento_id" => "",
			"encaminhamento_data_inicio" => "",
			"encaminhamento_data" => "",

			"status_devolucao" => "",
			"status_recusa" => "",

			"solicita_suporte" => "n",
			"solicita_visita" => "n"
		);
		$dados_suporte_descricao = array(
			"id_suporte" => $row_suporte['id'],
			"id_usuario_responsavel" => $row_usuario['IdUsuario'],
			"descricao" => $_POST['observacao'],
			"data" => date("Y-m-d H:i:s"),
			"tipo_postagem" => "Cancelamento de solicitação de visita"
		);
		funcao_suporte_update($row_suporte['id'], $dados_suporte, $dados_suporte_descricao);
	}
	// fim - Cancelar solicitação de visita (cn)
	//---------------------------------------------------------------------------------------------------------------------------------------------------------------
	// Agendar visita (cn)
	if (($_GET['situacao'] == "editar") and $_GET["acao"] == "Agendar visita" and $tipo_suporte_inloco == "cn") {

		$dados_suporte = array(
			"interacao" => $row_suporte['interacao'] + 1,
			"situacao" => "criada",
			"status" => "pendente usuario responsavel",
			"status_flag" => "a",

			"data_conclusao" => "", 

			"encaminhamento_id" => "",
			"encaminhamento_data_inicio" => "",
			"encaminhamento_data" => "",

			"status_devolucao" => "",
			"status_recusa" => "",

			"solicita_suporte" => "n",
			"solicita_visita" => "s",

			"titulo" => "Agendamento de visita - " . $row_suporte['titulo'],
			"titulo_anterior" => $row_suporte['titulo'],

			"tipo_suporte" => "c",
			"inloco" => "s",

			"data_inicio" => date('Y-m-d H:i:s'),
			"data_fim" => date('Y-m-d H:i:s'),

			"data_suporte_fim" => "0000-00-00 00:00:00",

			"cobranca" => "n",

			"tela" => "g"
		);
		$dados_suporte_descricao = array(
			"id_suporte" => $row_suporte['id'],
			"id_usuario_responsavel" => $row_usuario['IdUsuario'],
			"descricao" => $_POST['observacao'],
			"data" => date("Y-m-d H:i:s"),
			"tipo_postagem" => "Agendamento de visita"
		);
		funcao_suporte_update($row_suporte['id'], $dados_suporte, $dados_suporte_descricao);
	}
	// fim - Agendar visita (cn)
	//---------------------------------------------------------------------------------------------------------------------------------------------------------------
	// Alterar previsão (r)
	if (($_GET['situacao'] == "editar") and $_GET["acao"] == "Alterar previsão") {


		$dados_suporte = array(
			"interacao" => $row_suporte['interacao'] + 1,
			"data_inicio" => $_POST['data_inicio'],
			"data_fim" => $_POST['data_fim'],

			"previsao_geral_inicio" => $_POST['data_inicio'],
			"previsao_geral" => $_POST['data_fim']
		);
		$dados_suporte_descricao = array(
			"id_suporte" => $row_suporte['id'],
			"id_usuario_responsavel" => $row_usuario['IdUsuario'],
			"descricao" => "Foi alterada a previsão<br>Previsão anterior: " . formataDataPTG($row_suporte['data_inicio']) . " à " . formataDataPTG($row_suporte['data_fim']) . "<br>Nova previsão: " . formataDataPTG($_POST['data_inicio']) . " à " . formataDataPTG($_POST['data_fim']) . "<br>" . $_POST['observacao'],
			"data" => date("Y-m-d H:i:s"),
			"tipo_postagem" => "Alteração de previsão"

		);
		funcao_suporte_update($row_suporte['id'], $dados_suporte, $dados_suporte_descricao);
	}
	// fim - Alterar previsão (r)
	//---------------------------------------------------------------------------------------------------------------------------------------------------------------


	//---------------------------------------------------------------------------------------------------------------------------------------------------------------
	// Alterar ---
	//---------------------------------------------------------------------------------------------------------------------------------------------------------------
	// Alterar título
	if (($_GET['situacao'] == "editar") and $_GET["acao"] == "Alterar título") {


		$dados_suporte = array(
			"interacao" => $row_suporte['interacao'] + 1,
			"titulo" => $_POST['titulo']
		);
		$dados_suporte_descricao = array(
			"id_suporte" => $row_suporte['id'],
			"id_usuario_responsavel" => $row_usuario['IdUsuario'],
			"descricao" => "Foi alterado o título<br>Título anterior: " . $row_suporte['titulo'] . " - Novo título: " . $_POST['titulo'] . "<br>" . $_POST['observacao'],
			"data" => date("Y-m-d H:i:s"),
			"tipo_postagem" => "Alteração de título"
		);
		funcao_suporte_update($row_suporte['id'], $dados_suporte, $dados_suporte_descricao);
	}
	// fim - Alterar título
	//---------------------------------------------------------------------------------------------------------------------------------------------------------------
	// Alterar solicitante
	if (($_GET['situacao'] == "editar") and $_GET["acao"] == "Alterar solicitante") {


		$dados_suporte = array(
			"interacao" => $row_suporte['interacao'] + 1,
			"solicitante" => $_POST['solicitante']
		);
		$dados_suporte_descricao = array(
			"id_suporte" => $row_suporte['id'],
			"id_usuario_responsavel" => $row_usuario['IdUsuario'],
			"descricao" => "Foi alterado o solicitante<br>Solicitante anterior: " . $row_suporte['solicitante'] . " - Novo solicitante: " . $_POST['solicitante'] . "<br>" . $_POST['observacao'],
			"data" => date("Y-m-d H:i:s"),
			"tipo_postagem" => "Alteração de solicitante"
		);
		funcao_suporte_update($row_suporte['id'], $dados_suporte, $dados_suporte_descricao);
	}
	// fim - Alterar solicitante
	//---------------------------------------------------------------------------------------------------------------------------------------------------------------
	// Alterar envolvido(s) na reclamação
	if (($_GET['situacao'] == "editar") and $_GET["acao"] == "Alterar envolvido(s) na reclamação") {


		$dados_suporte = array(
			"interacao" => $row_suporte['interacao'] + 1,
			"envolvido_reclamacao" => $_POST['envolvido_reclamacao']
		);
		$dados_suporte_descricao = array(
			"id_suporte" => $row_suporte['id'],
			"id_usuario_responsavel" => $row_usuario['IdUsuario'],
			"descricao" => "Foi alterado o(s) envolvido(s) na reclamação<br>Envolvido(s) na reclamação anterior(es): " . $row_suporte['envolvido_reclamacao'] . " - Novo(s) envolvido(s) na reclamação: " . $_POST['envolvido_reclamacao'] . "<br>" . $_POST['observacao'],
			"data" => date("Y-m-d H:i:s"),
			"tipo_postagem" => "Alteração de envolvido(s) na reclamação"
		);
		funcao_suporte_update($row_suporte['id'], $dados_suporte, $dados_suporte_descricao);
	}
	// fim - Alterar envolvido(s) na reclamação
	//---------------------------------------------------------------------------------------------------------------------------------------------------------------
	// Alterar usuário responsável
	if (($_GET['situacao'] == "editar") and $_GET["acao"] == "Alterar usuário responsável") {

		// busca usuario_responsavel selecionado
		$colname_usuario_selecionado = "-1";
		if (isset($_POST['usuario_responsavel'])) {
			$colname_usuario_selecionado = $_POST['usuario_responsavel'];
		}
		mysql_select_db($database_conexao, $conexao);
		$query_usuario_selecionado = sprintf("SELECT IdUsuario, nome, praca FROM usuarios WHERE IdUsuario = %s", GetSQLValueString($colname_usuario_selecionado, "int"));
		$usuario_selecionado = mysql_query($query_usuario_selecionado, $conexao) or die(mysql_error());
		$row_usuario_selecionado = mysql_fetch_assoc($usuario_selecionado);
		$totalRows_usuario_selecionado = mysql_num_rows($usuario_selecionado);
		// fim - busca usuario_responsavel selecionado

		$dados_suporte = array(
			"interacao" => $row_suporte['interacao'] + 1,
			"id_usuario_responsavel" => $row_usuario_selecionado['IdUsuario'],
			"usuario_responsavel_leu" => ""
		);
		$dados_suporte_descricao = array(
			"id_suporte" => $row_suporte['id'],
			"id_usuario_responsavel" => $row_usuario['IdUsuario'],
			"descricao" => "Foi alterado o usuário responsável<br>Anterior: " . $row_suporte['usuario_responsavel'] . " - Novo: " . $row_usuario_selecionado['nome'] . "<br>" . $_POST['observacao'],
			"data" => date("Y-m-d H:i:s"),
			"tipo_postagem" => "Alteração de usuário responsável"
		);

		mysql_free_result($usuario_selecionado);

		funcao_suporte_update($row_suporte['id'], $dados_suporte, $dados_suporte_descricao);
	}
	// fim - Alterar usuário responsável
	//---------------------------------------------------------------------------------------------------------------------------------------------------------------
	// Alterar usuário envolvido
	if (($_GET['situacao'] == "editar") and $_GET["acao"] == "Alterar usuário envolvido") {

		// busca usuario_envolvido selecionado
		$colname_usuario_selecionado = "-1";
		if (isset($_POST['usuario_envolvido'])) {
			$colname_usuario_selecionado = $_POST['usuario_envolvido'];
		}
		mysql_select_db($database_conexao, $conexao);
		$query_usuario_selecionado = sprintf("SELECT IdUsuario, nome, praca FROM usuarios WHERE IdUsuario = %s", GetSQLValueString($colname_usuario_selecionado, "text"));
		$usuario_selecionado = mysql_query($query_usuario_selecionado, $conexao) or die(mysql_error());
		$row_usuario_selecionado = mysql_fetch_assoc($usuario_selecionado);
		$totalRows_usuario_selecionado = mysql_num_rows($usuario_selecionado);
		// fim - busca usuario_envolvido selecionado

		$dados_suporte = array(
			"interacao" => $row_suporte['interacao'] + 1,
			"id_usuario_envolvido" => $row_usuario_selecionado['IdUsuario'],
			"usuario_envolvido_leu" => ""
		);
		$dados_suporte_descricao = array(
			"id_suporte" => $row_suporte['id'],
			"id_usuario_responsavel" => $row_usuario['IdUsuario'],
			"descricao" => "Foi alterado o usuário envolvido<br>Anterior: " . $row_suporte['usuario_envolvido'] . " - Novo: " . $row_usuario_selecionado['nome'] . "<br>" . $_POST['observacao'],
			"data" => date("Y-m-d H:i:s"),
			"tipo_postagem" => "Alteração de usuário envolvido"
		);

		mysql_free_result($usuario_selecionado);

		funcao_suporte_update($row_suporte['id'], $dados_suporte, $dados_suporte_descricao);
	}
	// fim - Alterar usuário envolvido
	//---------------------------------------------------------------------------------------------------------------------------------------------------------------
	// Alterar anomalia
	if (($_GET['situacao'] == "editar") and $_GET["acao"] == "Alterar anomalia") {
		$dados_suporte = array(
			"interacao" => $row_suporte['interacao'] + 1,
			"anomalia" => $_POST['anomalia']
		);
		$dados_suporte_descricao = array(
			"id_suporte" => $row_suporte['id'],
			"id_usuario_responsavel" => $row_usuario['IdUsuario'],
			"descricao" => "Foi alterada a anomalia<br>Anomalia anterior: " . $row_suporte['anomalia'] . " - Nova anomalia: " . $_POST['anomalia'] . "<br>" . $_POST['observacao'],
			"data" => date("Y-m-d H:i:s"),
			"tipo_postagem" => "Alteração de anomalia"
		);
		funcao_suporte_update($row_suporte['id'], $dados_suporte, $dados_suporte_descricao);
	}
	// fim - Alterar anomalia
	//---------------------------------------------------------------------------------------------------------------------------------------------------------------
	// Alterar reclamação
	if (($_GET['situacao'] == "editar") and $_GET["acao"] == "Alterar reclamação") {
		$dados_suporte = array(
			"interacao" => $row_suporte['interacao'] + 1,
			"reclamacao" => $_POST['reclamacao']
		);
		$dados_suporte_descricao = array(
			"id_suporte" => $row_suporte['id'],
			"id_usuario_responsavel" => $row_usuario['IdUsuario'],
			"descricao" => "Foi alterada a reclamação<br>Reclamação anterior: " . $row_suporte['reclamacao'] . "<br>Nova reclamação: " . $_POST['reclamacao'] . "<br>" . $_POST['observacao'],
			"data" => date("Y-m-d H:i:s"),
			"tipo_postagem" => "Alteração de reclamação"
		);
		funcao_suporte_update($row_suporte['id'], $dados_suporte, $dados_suporte_descricao);
	}
	// fim - Alterar reclamação
	//---------------------------------------------------------------------------------------------------------------------------------------------------------------
	// Alterar questionamento inicial
	if (($_GET['situacao'] == "editar") and $_GET["acao"] == "Alterar questionamento inicial") {
		$dados_suporte = array(
			"interacao" => $row_suporte['interacao'] + 1,
			"reclamacao_questionamento" => $_POST['reclamacao_questionamento']
		);
		$dados_suporte_descricao = array(
			"id_suporte" => $row_suporte['id'],
			"id_usuario_responsavel" => $row_usuario['IdUsuario'],
			"descricao" => "Foi alterado o questionamento inicial<br>Questionamento inicial anterior: " . $row_suporte['reclamacao_questionamento'] . "<br>Novo questionamento inicial: " . $_POST['reclamacao_questionamento'] . "<br>" . $_POST['observacao'],
			"data" => date("Y-m-d H:i:s"),
			"tipo_postagem" => "Alteração de questionamento inicial"
		);
		funcao_suporte_update($row_suporte['id'], $dados_suporte, $dados_suporte_descricao);
	}
	// fim - Alterar questionamento inicial
	//---------------------------------------------------------------------------------------------------------------------------------------------------------------
	// Alterar telefone reclamante
	if (($_GET['situacao'] == "editar") and $_GET["acao"] == "Alterar telefone reclamante") {


		$dados_suporte = array(
			"interacao" => $row_suporte['interacao'] + 1,
			"reclamacao_telefone" => $_POST['reclamacao_telefone']
		);
		$dados_suporte_descricao = array(
			"id_suporte" => $row_suporte['id'],
			"id_usuario_responsavel" => $row_usuario['IdUsuario'],
			"descricao" => "Foi alterado o telefone reclamante<br>Telefone anterior: " . $row_suporte['reclamacao_telefone'] . " - Novo telefone reclamante: " . $_POST['reclamacao_telefone'] . "<br>" . $_POST['observacao'],
			"data" => date("Y-m-d H:i:s"),
			"tipo_postagem" => "Alteração de telefone de contato reclamante"
		);
		funcao_suporte_update($row_suporte['id'], $dados_suporte, $dados_suporte_descricao);
	}
	// fim - Alterar telefone reclamante
	//---------------------------------------------------------------------------------------------------------------------------------------------------------------
	// Alterar reclamante
	if (($_GET['situacao'] == "editar") and $_GET["acao"] == "Alterar reclamante") {


		$dados_suporte = array(
			"interacao" => $row_suporte['interacao'] + 1,
			"reclamacao_responsavel" => $_POST['reclamacao_responsavel']
		);
		$dados_suporte_descricao = array(
			"id_suporte" => $row_suporte['id'],
			"id_usuario_responsavel" => $row_usuario['IdUsuario'],
			"descricao" => "Foi alterado o reclamante<br>Reclamante anterior: " . $row_suporte['reclamacao_responsavel'] . " - Novo reclamante: " . $_POST['reclamacao_responsavel'] . "<br>" . $_POST['observacao'],
			"data" => date("Y-m-d H:i:s"),
			"tipo_postagem" => "Alteração de reclamante"
		);
		funcao_suporte_update($row_suporte['id'], $dados_suporte, $dados_suporte_descricao);
	}
	// fim - Alterar reclamante
	//---------------------------------------------------------------------------------------------------------------------------------------------------------------
	// Alterar data acordada
	if (($_GET['situacao'] == "editar") and $_GET["acao"] == "Alterar data acordada") {


		$dados_suporte = array(
			"interacao" => $row_suporte['interacao'] + 1,
			"reclamacao_data_acordada" => $_POST['reclamacao_data_acordada']
		);
		$dados_suporte_descricao = array(
			"id_suporte" => $row_suporte['id'],
			"id_usuario_responsavel" => $row_usuario['IdUsuario'],
			"descricao" => "Foi alterada a data acordada<br>Data anterior: " . formataDataPTG($row_suporte['reclamacao_data_acordada']) . " - Nova data acordada: " . formataDataPTG($_POST['reclamacao_data_acordada']) . "<br>" . $_POST['observacao'],
			"data" => date("Y-m-d H:i:s"),
			"tipo_postagem" => "Alteração de data acordada"
		);
		funcao_suporte_update($row_suporte['id'], $dados_suporte, $dados_suporte_descricao);
	}
	// fim - Alterar data acordada
	//---------------------------------------------------------------------------------------------------------------------------------------------------------------
	// Alterar percepção
	if (($_GET['situacao'] == "editar") and $_GET["acao"] == "Alterar percepção") {
		$dados_suporte = array(
			"interacao" => $row_suporte['interacao'] + 1,
			"reclamacao_percepcao" => $_POST['suporte_tipo_percepcao']
		);
		$dados_suporte_descricao = array(
			"id_suporte" => $row_suporte['id'],
			"id_usuario_responsavel" => $row_usuario['IdUsuario'],
			"descricao" => "Foi alterada a percepção<br>Percepção anterior: " . $row_suporte['reclamacao_percepcao'] . " - Nova percepção: " . $_POST['suporte_tipo_percepcao'] . "<br>" . $_POST['observacao'],
			"data" => date("Y-m-d H:i:s"),
			"tipo_postagem" => "Alteração de percepção"
		);
		funcao_suporte_update($row_suporte['id'], $dados_suporte, $dados_suporte_descricao);
	}
	// fim - Alterar percepção
	//---------------------------------------------------------------------------------------------------------------------------------------------------------------
	// Alterar orientação
	if (($_GET['situacao'] == "editar") and $_GET["acao"] == "Alterar orientação") {
		$dados_suporte = array(
			"interacao" => $row_suporte['interacao'] + 1,
			"orientacao" => $_POST['orientacao']
		);
		$dados_suporte_descricao = array(
			"id_suporte" => $row_suporte['id'],
			"id_usuario_responsavel" => $row_usuario['IdUsuario'],
			"descricao" => "Foi alterada a orientação<br>Orientação anterior: " . $row_suporte['orientacao'] . " - Nova orientação: " . $_POST['orientacao'] . "<br>" . $_POST['observacao'],
			"data" => date("Y-m-d H:i:s"),
			"tipo_postagem" => "Alteração de orientação"
		);
		funcao_suporte_update($row_suporte['id'], $dados_suporte, $dados_suporte_descricao);
	}
	// fim - Alterar orientação
	//---------------------------------------------------------------------------------------------------------------------------------------------------------------
	// Alterar observação
	if (($_GET['situacao'] == "editar") and $_GET["acao"] == "Alterar observação") {
		$dados_suporte = array(
			"interacao" => $row_suporte['interacao'] + 1,
			"observacao" => $_POST['observacao']
		);
		$dados_suporte_descricao = array(
			"id_suporte" => $row_suporte['id'],
			"id_usuario_responsavel" => $row_usuario['IdUsuario'],
			"descricao" => "Foi alterada a observação<br>Orientação anterior: " . $row_suporte['observacao'] . " - Nova observação: " . $_POST['observacao'] . "<br>" . $_POST['observacao'],
			"data" => date("Y-m-d H:i:s"),
			"tipo_postagem" => "Alteração de observação"
		);
		funcao_suporte_update($row_suporte['id'], $dados_suporte, $dados_suporte_descricao);
	}
	// fim - Alterar observação
	//---------------------------------------------------------------------------------------------------------------------------------------------------------------
	// Alterar prioridade
	if (($_GET['situacao'] == "editar") and $_GET["acao"] == "Alterar prioridade") {

		$prioridade_justificativa_texto_anterior = NULL;
		if ($row_suporte['prioridade_justificativa'] <> NULL) {
			$prioridade_justificativa_texto_anterior = " - Justificativa: " . $row_suporte['prioridade_justificativa'];
		}
		$prioridade_justificativa_texto_nova = NULL;
		if ($_POST['prioridade_justificativa'] <> NULL) {
			$prioridade_justificativa_texto_nova = " - Justificativa: " . $_POST['prioridade_justificativa'];
		}

		$dados_suporte = array(
			"interacao" => $row_suporte['interacao'] + 1,
			"prioridade" => $_POST['suporte_tipo_prioridade'],
			"prioridade_justificativa" => $_POST['prioridade_justificativa']
		);
		$dados_suporte_descricao = array(
			"id_suporte" => $row_suporte['id'],
			"id_usuario_responsavel" => $row_usuario['IdUsuario'],
			"descricao" => "
			Foi alterada a prioridade<br>
			Orientação anterior: " . $row_suporte['prioridade'] . $prioridade_justificativa_texto_anterior . "<br>
			Nova prioridade: " . $_POST['suporte_tipo_prioridade'] . $prioridade_justificativa_texto_nova . "<br>
			" . $_POST['observacao'],
			"data" => date("Y-m-d H:i:s"),
			"tipo_postagem" => "Alteração de prioridade"
		);

		funcao_suporte_update($row_suporte['id'], $dados_suporte, $dados_suporte_descricao);
	}
	// fim - Alterar prioridade
	//---------------------------------------------------------------------------------------------------------------------------------------------------------------
	// Alterar recomendação
	if (($_GET['situacao'] == "editar") and $_GET["acao"] == "Alterar recomendação") {


		$dados_suporte = array(
			"interacao" => $row_suporte['interacao'] + 1,
			"recomendacao" => $_POST['suporte_tipo_recomendacao']
		);
		$dados_suporte_descricao = array(
			"id_suporte" => $row_suporte['id'],
			"id_usuario_responsavel" => $row_usuario['IdUsuario'],
			"descricao" => "Foi alterado a recomendação<br>Recomendação anterior: " . $row_suporte['recomendacao'] . " - Nova Recomendação: " . $_POST['suporte_tipo_recomendacao'] . "<br>" . $_POST['observacao'],
			"data" => date("Y-m-d H:i:s"),
			"tipo_postagem" => "Alteração de recomendação"
		);
		funcao_suporte_update($row_suporte['id'], $dados_suporte, $dados_suporte_descricao);
	}
	// fim - Alterar recomendação
	//---------------------------------------------------------------------------------------------------------------------------------------------------------------
	// Alterar tipo de atendimento
	if (($_GET['situacao'] == "editar") and $_GET["acao"] == "Alterar tipo de atendimento") {
		$dados_suporte = array(
			"interacao" => $row_suporte['interacao'] + 1,
			"tipo_atendimento" => $_POST['suporte_tipo_atendimento']
		);
		$dados_suporte_descricao = array(
			"id_suporte" => $row_suporte['id'],
			"id_usuario_responsavel" => $row_usuario['IdUsuario'],
			"descricao" => "Foi alterado o tipo de atendimento<br>Tipo anterior: " . $row_suporte['tipo_atendimento'] . " - Novo tipo: " . $_POST['suporte_tipo_atendimento'] . "<br>" . $_POST['observacao'],
			"data" => date("Y-m-d H:i:s"),
			"tipo_postagem" => "Alteração de tipo de atendimento"
		);
		funcao_suporte_update($row_suporte['id'], $dados_suporte, $dados_suporte_descricao);
	}
	// fim - Alterar tipo de atendimento
	//---------------------------------------------------------------------------------------------------------------------------------------------------------------
	// Alterar módulo
	if (($_GET['situacao'] == "editar") and $_GET["acao"] == "Alterar módulo") {
		$dados_suporte = array(
			"interacao" => $row_suporte['interacao'] + 1,
			"modulo" => $_POST['geral_tipo_modulo']
		);
		$dados_suporte_descricao = array(
			"id_suporte" => $row_suporte['id'],
			"id_usuario_responsavel" => $row_usuario['IdUsuario'],
			"descricao" => "Foi alterado o módulo<br>Módulo anterior: " . $row_suporte['modulo'] . " - Novo módulo: " . $_POST['geral_tipo_modulo'] . "<br>" . $_POST['observacao'],
			"data" => date("Y-m-d H:i:s"),
			"tipo_postagem" => "Alteração de módulo"
		);
		funcao_suporte_update($row_suporte['id'], $dados_suporte, $dados_suporte_descricao);
	}
	// fim - Alterar módulo
	//---------------------------------------------------------------------------------------------------------------------------------------------------------------
	// fim - Alterar ---
	//---------------------------------------------------------------------------------------------------------------------------------------------------------------

	//---------------------------------------------------------------------------------------------------------------------------------------------------------------
	// QUESTIONAMENTO -----------------------------------------------------------------------------------------------------------------------------------------------
	//---------------------------------------------------------------------------------------------------------------------------------------------------------------
	if (($_GET['situacao'] == "") and $_GET["acao"] == "Questionar") {

		$dados_suporte = array(
			"interacao" => $row_suporte['interacao'] + 1,
			"status_questionamento" => $_POST['questionado']
		);
		$dados_suporte_descricao = array(
			"id_suporte" => $row_suporte['id'],
			"id_usuario_responsavel" => $row_usuario['IdUsuario'],
			"descricao" => $_POST['observacao'],
			"data" => date("Y-m-d H:i:s"),
			"tipo_postagem" => "Questionamento",
			"questionado" => $_POST['questionado']
		);
		funcao_suporte_update($row_suporte['id'], $dados_suporte, $dados_suporte_descricao);
	}
	// fim - Questionar
	//---------------------------------------------------------------------------------------------------------------------------------------------------------------


	//---------------------------------------------------------------------------------------------------------------------------------------------------------------
	// ATENDIMENTO -----------------------------------------------------------------------------------------------------------------------------------------------
	//---------------------------------------------------------------------------------------------------------------------------------------------------------------
	// Iniciar atendimento
	if ($_GET["acao"] == "Iniciar atendimento") {

		$observacao_label = NULL;
		if(@$_POST['observacao'] <> NULL){
			$observacao_label = "<br>Observações: " . $_POST['observacao'];
		}

		$dados_suporte = array(
			"interacao" => $row_suporte['interacao'] + 1, 
			"atendimento" => "IniAte", 
			"atendimento_cliente" => $_POST['atendimento_cliente'], 
			"atendimento_IdUsuario" => $row_usuario['IdUsuario'], 
			"atendimento_data" => date("Y-m-d H:i:s")
		);
		$dados_suporte_descricao = array(
			"id_suporte" => $row_suporte['id'],
			"id_usuario_responsavel" => $row_usuario['IdUsuario'],
			"descricao" => "Pessoa/Cliente: " . $_POST['atendimento_cliente'] . $observacao_label,
			"data" => date("Y-m-d H:i:s"),
			"tipo_postagem" => "Iniciar atendimento"
		);
		funcao_suporte_update($row_suporte['id'], $dados_suporte, $dados_suporte_descricao);

	}
	// fim - Iniciar atendimento
	//---------------------------------------------------------------------------------------------------------------------------------------------------------------
	// Finalizar atendimento
	if ($_GET["acao"] == "Finalizar atendimento") {

		$observacao_label = NULL;
		if(@$_POST['observacao'] <> NULL){
			$observacao_label = "<br>Observações: " . $_POST['observacao'];
		}

		$dados_suporte = array(
			"interacao" => $row_suporte['interacao'] + 1, 
			"atendimento" => $_POST['atendimento_status'], 
			"atendimento_cliente" => $_POST['atendimento_cliente'], 
			"atendimento_texto" => $_POST['atendimento_texto'], 
			"atendimento_IdUsuario" => $row_usuario['IdUsuario'], 
			"atendimento_data" => date("Y-m-d H:i:s")
		);
		$dados_suporte_descricao = array(
			"id_suporte" => $row_suporte['id'],
			"id_usuario_responsavel" => $row_usuario['IdUsuario'],
			"descricao" => "Pessoa/Cliente: " . $_POST['atendimento_cliente'] . "<br>Status: " . $_POST['atendimento_status'] . "<br>Detalhes: " . $_POST['atendimento_texto'] . $observacao_label,
			"data" => date("Y-m-d H:i:s"),
			"tipo_postagem" => "Finalizar atendimento"
		);
		funcao_suporte_update($row_suporte['id'], $dados_suporte, $dados_suporte_descricao);

	}
	// fim - Finalizar atendimento
	//---------------------------------------------------------------------------------------------------------------------------------------------------------------
	// Cancelar atendimento
	if ($_GET["acao"] == "Cancelar atendimento") {

		$atendimento_local_label = NULL;
		if($_POST['atendimento_local'] == "c"){
			$atendimento_local_label = "Ao chegar ao cliente";
		} else if($_POST['atendimento_local'] == "a"){
			$atendimento_local_label = "Antecipado";
		} else if($_POST['atendimento_local'] == "d"){
			$atendimento_local_label = "Durante o atendimento";
		}

		$observacao_label = NULL;
		if(@$_POST['observacao'] <> NULL){
			$observacao_label = "<br>Observações: " . $_POST['observacao'];
		}

		$dados_suporte = array(
			"interacao" => $row_suporte['interacao'] + 1, 
			"atendimento" => "SolCan", 
			"atendimento_cliente" => $_POST['atendimento_cliente'], 
			"atendimento_IdUsuario" => $row_usuario['IdUsuario'], 
			"atendimento_data" => date("Y-m-d H:i:s")
		);
		$dados_suporte_descricao = array(
			"id_suporte" => $row_suporte['id'],
			"id_usuario_responsavel" => $row_usuario['IdUsuario'],
			"descricao" => "Pessoa/Cliente: " . $_POST['atendimento_cliente'] . "<br>Local: " . $atendimento_local_label . "<br>Motivo: " . $_POST['atendimento_texto'] . $observacao_label,
			"data" => date("Y-m-d H:i:s"),
			"tipo_postagem" => "Cancelar atendimento"
		);
		funcao_suporte_update($row_suporte['id'], $dados_suporte, $dados_suporte_descricao);

	}
	// fim - Cancelar atendimento
	//---------------------------------------------------------------------------------------------------------------------------------------------------------------
	// Reagendar atendimento
	if ($_GET["acao"] == "Reagendar atendimento") {

		if (isset($_POST['atendimento_previsao']) and $_POST['atendimento_previsao'] != "") {
			$data_data = substr($_POST['atendimento_previsao'], 0, 10);
			$data_hora = substr($_POST['atendimento_previsao'], 10, 9);
			$_POST['atendimento_previsao'] = implode("-", array_reverse(explode("-", $data_data))) . $data_hora;
		} else {
			$_POST['atendimento_previsao'] = NULL;
		}
		
		$observacao_label = NULL;
		if(@$_POST['observacao'] <> NULL){
			$observacao_label = "<br>Observações: " . $_POST['observacao'];
		}

		$dados_suporte = array(
			"interacao" => $row_suporte['interacao'] + 1, 

			"atendimento" => "SolRea", 
			"atendimento_cliente" => $_POST['atendimento_cliente'], 
			"atendimento_previsao" => $_POST['atendimento_previsao'], 
			"atendimento_texto" => $_POST['atendimento_texto'], 
			"atendimento_IdUsuario" => $row_usuario['IdUsuario'], 
			"atendimento_data" => date("Y-m-d H:i:s")
		);
		$dados_suporte_descricao = array(
			"id_suporte" => $row_suporte['id'],
			"id_usuario_responsavel" => $row_usuario['IdUsuario'],
			"descricao" => "Pessoa/Cliente: " . $_POST['atendimento_cliente'] . "<br>Previsão: " . formataDataPTG($_POST['atendimento_previsao']) . "<br>Motivo: " . $_POST['atendimento_texto'] . $observacao_label,
			"data" => date("Y-m-d H:i:s"),
			"tipo_postagem" => "Reagendar atendimento"
		);
		funcao_suporte_update($row_suporte['id'], $dados_suporte, $dados_suporte_descricao);

	}
	// fim - Reagendar atendimento
	//---------------------------------------------------------------------------------------------------------------------------------------------------------------
	//---------------------------------------------------------------------------------------------------------------------------------------------------------------

	// limpando o array
	$dados_suporte = array();
	$dados_suporte_descricao = array();
	// fim - limpando o array

	// redireciona
	$updateGoTo = "suporte_editar.php?id_suporte=" . $_GET['id_suporte'];
	echo sprintf("<meta http-equiv=\"refresh\" content=\"0; url=%s\">", $updateGoTo);
	// fim - redireciona

	exit;
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title><? echo $_GET['acao']; ?> (<?php echo $row_suporte['id']; ?>)</title>

	<link rel="stylesheet" href="css/suporte.css" type="text/css" media="screen" />

	<script type="text/javascript" src="js/jquery.js"></script>

	<link rel="stylesheet" href="css/thickbox.css" type="text/css" media="screen" />
	<script type="text/javascript" src="js/thickbox.js"></script>

	<script type="text/javascript" src="funcoes.js"></script>

	<script src="js/jquery.metadata.js" type="text/javascript"></script>
	<script type="text/javascript" src="js/jquery.validate.js"></script>

	<script type="text/javascript" src="js/jquery.numeric.js"></script>

	<script type="text/javascript" src="js/jquery.maskedinput-1.2.2.min.js"></script>
	<script src="js/jquery.price_format.1.3.js" type="text/javascript"></script>
	<script type="text/javascript" src="js/jquery.alphanumeric.pack.js"></script>

	<link type="text/css" href="css/jquery-ui.css" rel="stylesheet" />
	<link type="text/css" href="css/jquery-ui-timepicker-addon.css" rel="stylesheet" />

	<script type="text/javascript" src="js/jquery-ui.js"></script>
	<script type="text/javascript" src="js/jquery-ui-timepicker-addon.js"></script>

	<script type="text/javascript" src="js/date.format.js"></script>

	<script type="text/javascript" src="js/funcoes_data.js"></script>

	<script type="text/javascript" src="js/funcao_js_valida_cpf_cnpj.js"></script>

	<style>
		/* erro de validação */
		label.error {
			color: red;
			display: none;
		}

		/* fim - erro de validação */

		/* calendário */
		.ui-timepicker-div .ui-widget-header {
			margin-bottom: 8px;
		}

		.ui-timepicker-div dl {
			text-align: left;
		}

		.ui-timepicker-div dl dt {
			height: 25px;
			margin-bottom: -25px;
		}

		.ui-timepicker-div dl dd {
			margin: 0 10px 10px 65px;
		}

		.ui-timepicker-div td {
			font-size: 90%;
		}

		.ui-tpicker-grid-label {
			background: none;
			border: none;
			margin: 0;
			padding: 0;
		}

		.ui-timepicker-rtl {
			direction: rtl;
		}

		.ui-timepicker-rtl dl {
			text-align: right;
		}

		.ui-timepicker-rtl dl dd {
			margin: 0 65px 10px 10px;
		}

		/* fim - calendário */

		.ui-datepicker-trigger {
			margin-left: 5px;
			vertical-align: top;
		}

		.ui-dialog {
			font-size: 12px;
		}
	</style>

	<script type="text/javascript">
		window.history.forward(1); // Desabilita a função de voltar do Browser

		// validar diferença entre datas
		jQuery.validator.addMethod("dateRange", function() {

			var is_valid = true;
			var data_inicio = $("#data_inicio").val();
			var data_fim = $("#data_fim").val();

			if (data_inicio.length != 16) {
				if (data_inicio.length != 19) {
					is_valid = false;
				}
			}
			if (data_fim.length != 16) {
				if (data_fim.length != 19) {
					is_valid = false;
				}
			}

			if (data_inicio != "" && data_fim != "") {

				// quebra data inicial
				var quebraDI = data_inicio.split("-");
				var diaDI = quebraDI[0];
				var mesDI = quebraDI[1];
				var anoDI = quebraDI[2].substr(0, 4);
				var time_inicial = quebraDI[2].substr(5, 8);
				var quebraTimeDI = time_inicial.split(":");
				var horaDI = quebraTimeDI[0];
				var minutoDI = quebraTimeDI[1];
				var segundoDI = quebraTimeDI[2];
				if (quebraTimeDI[2] == null) {
					var segundoDI = '00';
				} else {
					var segundoDI = quebraTimeDI[2];
				}

				// quebra data final
				var quebraDF = data_fim.split("-");
				var diaDF = quebraDF[0];
				var mesDF = quebraDF[1];
				var anoDF = quebraDF[2].substr(0, 4);
				var time_final = quebraDF[2].substr(5, 8);
				var quebraTimeDF = time_final.split(":");
				var horaDF = quebraTimeDF[0];
				var minutoDF = quebraTimeDF[1];
				var segundoDF = quebraTimeDF[2];
				if (quebraTimeDF[2] == null) {
					var segundoDF = '00';
				} else {
					var segundoDF = quebraTimeDF[2];
				}

				var date1 = anoDI + "-" + mesDI + "-" + diaDI + " " + horaDI + ":" + minutoDI + ":" + segundoDI;
				var date2 = anoDF + "-" + mesDF + "-" + diaDF + " " + horaDF + ":" + minutoDF + ":" + segundoDF;

				is_valid = date1 < date2;

			}

			return (is_valid);

		}, " Data final deve ser maior que a data inicial");
		// validar diferença entre datas

		// validar_comprovante_pagamento
		$.validator.addMethod("validar_comprovante_pagamento", function(value, element) {

			var comprovante_pagamento_retorno = 0;

			$.ajax({
				async: false,
				type: "POST",
				url: "suporte_consulta_comprovante_pagamento.php",
				data: {
					id_suporte: <?php echo $row_suporte['id']; ?>
				},
				success: function(data) {
					$('#comprovante_pagamento').val(data);
				},
				complete: function(data) {
					comprovante_pagamento_retorno = $('#comprovante_pagamento').val();
				}
			});

			if (comprovante_pagamento_retorno == 1) {
				return true;
			} else {
				return false;
			}

		});
		// fim - validar_comprovante_pagamento

		$.metadata.setType("attr", "validate");
		$(document).ready(function() {

			// tab/enter	
			textboxes = $("input, select, textarea");
			$("input, select").keypress(function(e) {

				var tecla = (e.keyCode ? e.keyCode : e.which);
				if (tecla == 13 || tecla == 9) {

					// corrige problema - quando a agenda está aberta, o tab/enter fica com o FOCUS na hora_inicio			
					if ($("#TB_window").length) { // verifica se o tb_show está sendo exibido
						$("#data_inicio").focus();
						event.preventDefault();

					} else {

						// ação do tab/enter
						currentBoxNumber = textboxes.index(this);
						if (textboxes[currentBoxNumber + 1] != null) {
							nextBox = textboxes[currentBoxNumber + 1]
							nextBox.focus();
							event.preventDefault();
						}
						// fim - ação do tab/enter				

					}
					// fim - corrige problema

				}

			});
			// fim - tab/enter	

			// caixa_comprovante_pagamento
			$('#caixa_comprovante_pagamento').hide();
			$("input[name='confirmacao_recebimento']").change(function() { // ao mudar o valor do select

				$("input[name='confirmacao_recebimento']:checked").each(function() {
					var confirmacao_recebimento_atual = $(this).val(); // lê o valor selecionado

					// sim
					if (confirmacao_recebimento_atual == "s") {
						$('#caixa_comprovante_pagamento').show();
					}
					// fim - sim

					// nao
					if (confirmacao_recebimento_atual == "n") {
						$('#caixa_comprovante_pagamento').hide();
					}
					// fim - nao

				});

			});
			// fim - caixa_comprovante_pagamento

			// Click no botão Botão: Salvar ---------------------------------------------------------------
			$('#button').click(function() {

				<? if (
					($_GET['situacao'] == "editar") and
					($_GET["acao"] == "Agendamento" or
						$_GET["acao"] == "Finalizar agendamento" or
						$_GET["acao"] == "Reagendar")
				) { ?>

					// consulta automática - agenda
					if ($("input[name=data_inicio]").val() != '' && $("input[name=data_fim]").val() != '' && $("select[name=usuario_responsavel]").val() != '') {

						// post
						$.post("agenda_consulta.php", {
							data_inicio: $("input[name=data_inicio]").val(),
							data_fim: $("input[name=data_fim]").val(),
							id_usuario_responsavel: $("select[name=usuario_responsavel]").find("option:selected").attr("title"),
							id_agenda: <? if ($totalRows_agenda > 0) {
											echo $row_agenda['id_agenda'];
										} else {
											echo 0;
										} ?>
						}, function(data) {

							if (data == 0) {
								$('#form').submit();
							}
							if (data == 1) {
								alert("Data informada inválida. Existem agendamentos para o usuário atual durante período informado.");
								$('#data_fim').val('');
								$('#agendamento_tempo').val('');
								return false;
							}

						});
						// fim - post

					} else {

						$('#form').submit();

					}
					// fim - consulta automática - agenda

				<? } else { ?>

					$('#form').submit();

				<? } ?>

			});
			// fim - Click no botão Botão: Salvar ---------------------------------------------------------

			//region - suporte_tipo_prioridade ***************************************************************************
			<? if ($row_suporte['prioridade'] == "Alta") { ?>
				$("div[id=prioridade_justificativa_caixa]").show();
			<? } else { ?>
				$("div[id=prioridade_justificativa_caixa]").hide();
			<? } ?>

			$("select[id='suporte_tipo_prioridade']").change(function() { // ao mudar o valor do select 'suporte_tipo_prioridade'
				$("select[id='suporte_tipo_prioridade'] option:selected").each(function() {
					suporte_tipo_prioridade_atual = $(this).val();

					// se suporte_tipo_prioridade é: Alta
					if (suporte_tipo_prioridade_atual == "Alta") {

						$("div[id=prioridade_justificativa_caixa]").show();

						$("textarea[id='prioridade_justificativa']").val('');

					}
					// fim - se suporte_tipo_prioridade é: Alta

					// se não
					else {

						$("div[id=prioridade_justificativa_caixa]").hide();

						$("textarea[id='prioridade_justificativa']").val('');

					}
					// fim - se não

				});
			})
			//endregion - fim - suporte_tipo_prioridade ******************************************************************

			// validação
			$("#form").validate({
				rules: {
					<? if (
						($_GET['situacao'] == "editar" and $_GET['acao'] == "Alterar prioridade")
					) { ?>
						observacao: {
							required: function(element) {
								return $("select[id='suporte_tipo_prioridade'] option:selected").val() != "Alta";
							},
							minlength: 10
						},
					<? } else if (
						($_GET['situacao'] == "editar" and $_GET['acao'] == "Concluir validação")
					) { ?>
						observacao: {
							required: function(element) {
								return $("select[id='solucionado'] option:selected").val() != "n";
							},
							minlength: 10
						},
					<? } else if (
						($_GET['acao'] == "Encaminhar") or
						($_GET['acao'] == "Recusar") or
						($_GET['acao'] == "Solicitar suporte" and $_GET['resposta'] == "nao") or
						($_GET['situacao'] == "geral") or
						($_GET['acao'] == "Questionar") or
						($_GET['situacao'] == "editar")
					) { ?>
						observacao: {
							required: true,
							minlength: 10
						},
					<? } ?>
					data_inicio: {
						required: true
					},
					agendamento_tempo: {
						required: true
					},
					data_fim: {
						required: true,
						dateRange: true
					},
					data_suporte_fim: {
						required: true
					},
					titulo: "required",
					cobranca_recebimento_justificativa: {
						required: {
							depends: function() {
								return $('input[name=confirmacao_recebimento]:checked').val() == 'n';
							}
						}
					},
					usuario_responsavel: "required",
					usuario_envolvido: "required",
					ordem_servico: "required",
					solicitante: "required",
					envolvido_reclamacao: "required",
					geral_tipo_modulo: "required",
					suporte_tipo_atendimento: "required",
					suporte_tipo_prioridade: "required",
					prioridade_justificativa: {
						required: function(element) {
							return $("select[id='suporte_tipo_prioridade'] option:selected").val() == "Alta";
						},
						minlength: 30
					},
					anomalia: {
						required: true,
						minlength: 10
					},
					orientacao: "required",
					suporte_tipo_parecer: "required",
					suporte_tipo_recomendacao: "required",
					confirmacao_recebimento: {
						required: true,
						validar_comprovante_pagamento: {
							depends: function() {
								return $('input[name=confirmacao_recebimento]:checked').val() == 's';
							}
						}
					},
					avaliacao_atendimento_justificativa: {
						required: {
							depends: function() {
								return (
									$('input[name=avaliacao_atendimento]:checked').val() == 'Ruim' ||
									$('input[name=avaliacao_atendimento]:checked').val() == 'Péssimo'
								);
							}
						}
					},
					solucionado: "required",
					solucionado_nao: {
						required: function(element) {
							return $("select[name='solucionado']").val() == "n";
						}, 
						minlength: 10
					},
					anomalia_simulada_afirmacao: {
						required: function(element) {
							return $("select[name='anomalia_simulada']").val() == "n";
						}
					},
					inloco: "required",
					tipo_formulario: "required",
					reagendamento_solicitante: "required",
					contato_responsavel: "required",
					contato_telefone: "required",
					'versao[]': "required",
					atendimento_cliente: "required",
					atendimento_local: "required",
					atendimento_previsao: "required",
					atendimento_status: "required",
					atendimento_texto: {
						required: true,
						minlength: 10
					},
					tempo_gasto: "required"
				},
				messages: {
					observacao: "Informe a observação com no mínimo 10 caracteres",
					data_inicio: " Informe uma data inicial",
					agendamento_tempo: " Selecione o tempo de agendamento",
					data_fim: {
						required: " Informe uma data final"
					},
					data_suporte_fim: {
						required: " Informe uma data final do suporte"
					},
					titulo: " Informe um título",
					cobranca_recebimento_justificativa: " Informe a justificativa",
					usuario_responsavel: " Selecione o usuário responsável",
					usuario_envolvido: " Selecione o usuário envolvido",
					ordem_servico: " Informe a ordem de serviço vinculada",
					solicitante: " Informe um solicitante",
					envolvido_reclamacao: " Informe o(s) envolvido(s) na reclamação",
					geral_tipo_modulo: " Informe um módulo",
					suporte_tipo_atendimento: " Informe um tipo de atendimento",
					suporte_tipo_prioridade: " Informe a prioridade",
					prioridade_justificativa: "Informe a justificativa da prioridade Alta com no mínimo 30 caracteres",
					anomalia: "Informe a anomalia com no mínimo 10 caracteres",
					orientacao: "Informe a orientação",
					suporte_tipo_parecer: " Selecione o parecer",
					suporte_tipo_recomendacao: " Selecione a recomendação",
					confirmacao_recebimento: {
						required: " Selecione uma das opções",
						validar_comprovante_pagamento: "Necessário anexar comprovante de pagamento"
					},
					avaliacao_atendimento_justificativa: " Informe a justificativa",
					solucionado: " Informe se foi solucionado",
					solucionado_nao: " Informe a justificativa com no mínimo 10 caracteres",
					anomalia_simulada_afirmacao: " Selecione uma das opções",
					inloco: " Não informado local de atendimento",
					tipo_formulario: " Não informado tipo de formulário",
					reagendamento_solicitante: " Informe o solicitante do reagendamento",
					contato_responsavel: " Informe o contato",
					contato_telefone: " Informe o telefone",
					'versao[]': " Informe a versão",
					atendimento_cliente: " Informe o cliente",
					atendimento_local: " Informe o local",
					atendimento_previsao: " Informe a previsão",
					atendimento_status: "Selecione o status",
					atendimento_texto: " Informe o texto com no mínimo 10 caracteres",
					tempo_gasto: " Informe o tempo gasto"
				},
				onkeyup: false,
				submitHandler: function(form) {
					form.submit();
				},
				errorPlacement: function(error, element) {
					if (element.is(":radio")) {
						error.prependTo(element.parent().next());
					} else {
						error.insertAfter(element);
					}
				}
			});
			// fim - validação

			// mascara
			$('#data_inicio').mask('99-99-9999 99:99', {
				placeholder: " "
			});
			$('#data_fim').mask('99-99-9999 99:99', {
				placeholder: " "
			});

			$('#data_suporte_fim').mask('99-99-9999 99:99', {
				placeholder: " "
			});

			$('#reclamacao_data_acordada').mask('99-99-9999 99:99', {
				placeholder: " "
			});

			$('#prazo_desenvolvimento_orcamento').numeric();

			$('#ordem_servico').numeric();

			$('#orcamento').priceFormat({
				prefix: '',
				centsSeparator: ',',
				thousandsSeparator: ''
			});
			// fim - mascara

			// abrir agenda
			$('#ver_agenda').click(function() {

				var usuario_responsavel = $("select[name=usuario_responsavel]").val();
				data_atual = $('#data_inicio').val();

				tb_show("Agenda", "agenda_popup.php?usuario_atual=" + usuario_responsavel + "&data_atual=" + data_atual + "&height=<? echo $suporte_editar_tabela_height - 100; ?>&width=<? echo $suporte_editar_tabela_width - 40; ?>&placeValuesBeforeTB_=savedValues&TB_iframe=true", "");
				return false;

			});
			// fim - abrir agenda

			//region - calendário -------------------------------------------------------------

			<? if ($tipo_suporte_inloco == "cs" or $tipo_suporte_inloco == "cn" or $tipo_suporte_inloco == "p") { ?>

				// data_inicio
				var data_inicio = $('#data_inicio');
				data_inicio.datetimepicker({
					showOn: "button",
					buttonImage: "css/images/calendar.gif",
					buttonImageOnly: true,
					showSecond: false,
					minDateTime: new Date(<?php echo time() * 1000 ?>),
					inline: true,
					dateFormat: 'dd-mm-yy',
					timeFormat: 'HH:mm',
					dayNames: [
						'Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado', 'Domingo'
					],
					dayNamesMin: [
						'D', 'S', 'T', 'Q', 'Q', 'S', 'S', 'D'
					],
					dayNamesShort: [
						'Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb', 'Dom'
					],
					monthNames: [
						'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro',
						'Outubro', 'Novembro', 'Dezembro'
					],
					monthNamesShort: [
						'Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set',
						'Out', 'Nov', 'Dez'
					],
					nextText: 'Próximo',
					prevText: 'Anterior',
					closeText: "Fechar",
					currentText: "Agora",
					timeOnlyTitle: 'Escolha a hora',
					timeText: 'Horário',
					hourText: 'Hora',
					minuteText: 'Minuto',
					secondText: 'Segundo',
					beforeShow: function(selectedDateTime) {

						$('#data_fim').val('');
						$('#agendamento_tempo').val('');

					},
					onChangeMonthYear: function(selectedDateTime) {
						$('#data_fim').val('');
						$('#agendamento_tempo').val('');
					},
					onClose: function(selectedDateTime) {

						if (selectedDateTime == "  -  -       :  " || selectedDateTime == "") {
							$('#data_fim').val('');
							$('#agendamento_tempo').val('');
						}

					},
					onSelect: function(selectedDateTime) {
						$('#data_fim').val('');
						$('#agendamento_tempo').val('');
					}
				});
				// fim - data_inicio

			<? } ?>

			<? if ($tipo_suporte_inloco == "r") { ?>

				$('#data_fim').attr("disabled", true);

				// data_inicio
				$('#data_inicio').datetimepicker({
					showSecond: false,
					minDateTime: new Date(<?php echo time() * 1000 ?>),
					inline: true,
					dateFormat: 'dd-mm-yy',
					timeFormat: 'HH:mm',
					dayNames: [
						'Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado', 'Domingo'
					],
					dayNamesMin: [
						'D', 'S', 'T', 'Q', 'Q', 'S', 'S', 'D'
					],
					dayNamesShort: [
						'Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb', 'Dom'
					],
					monthNames: [
						'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro',
						'Outubro', 'Novembro', 'Dezembro'
					],
					monthNamesShort: [
						'Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set',
						'Out', 'Nov', 'Dez'
					],
					nextText: 'Próximo',
					prevText: 'Anterior',
					closeText: "Fechar",
					currentText: "Agora",
					timeOnlyTitle: 'Escolha a hora',
					timeText: 'Horário',
					hourText: 'Hora',
					minuteText: 'Minuto',
					secondText: 'Segundo',
					minDate: new Date(<?php echo time() * 1000 ?>),
					onChangeMonthYear: function(selectedDateTime) {

						$('#data_fim').val('');

					},
					onSelect: function(selectedDateTime) {

						$('#data_fim').val('');

					},
					onClose: function(selectedDateTime) {

						if (selectedDateTime == '') {
							$('#data_fim').val('');
							$('#data_fim').attr("disabled", true);
						} else {
							$('#data_fim').attr("disabled", false);
						}

					}
				});
				// fim - data_inicio

				// data_fim
				$('#data_fim').datetimepicker({
					showSecond: false,
					inline: true,
					dateFormat: 'dd-mm-yy',
					timeFormat: 'HH:mm',
					dayNames: [
						'Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado', 'Domingo'
					],
					dayNamesMin: [
						'D', 'S', 'T', 'Q', 'Q', 'S', 'S', 'D'
					],
					dayNamesShort: [
						'Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb', 'Dom'
					],
					monthNames: [
						'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro',
						'Outubro', 'Novembro', 'Dezembro'
					],
					monthNamesShort: [
						'Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set',
						'Out', 'Nov', 'Dez'
					],
					nextText: 'Próximo',
					prevText: 'Anterior',
					closeText: "Fechar",
					currentText: "Agora",
					timeOnlyTitle: 'Escolha a hora',
					timeText: 'Horário',
					hourText: 'Hora',
					minuteText: 'Minuto',
					secondText: 'Segundo',
					minDate: new Date(<?php echo time() * 1000 ?>),
					beforeShow: function(selectedDateTime) {

						var start = $('#data_inicio').datetimepicker('getDate');
						$('#data_fim').datetimepicker('option', 'minDate', new Date(start.getTime()));

					}
				});
				// fim - data_fim

			<? } ?>

			// data_suporte_fim
			var data_suporte_fim = $('#data_suporte_fim');
			data_suporte_fim.datetimepicker({
				showOn: "button",
				buttonImage: "css/images/calendar.gif",
				buttonImageOnly: true,
				showSecond: false,
				inline: true,
				dateFormat: 'dd-mm-yy',
				timeFormat: 'HH:mm',
				dayNames: [
					'Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado', 'Domingo'
				],
				dayNamesMin: [
					'D', 'S', 'T', 'Q', 'Q', 'S', 'S', 'D'
				],
				dayNamesShort: [
					'Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb', 'Dom'
				],
				monthNames: [
					'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro',
					'Outubro', 'Novembro', 'Dezembro'
				],
				monthNamesShort: [
					'Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set',
					'Out', 'Nov', 'Dez'
				],
				nextText: 'Próximo',
				prevText: 'Anterior',
				closeText: "Fechar",
				currentText: "Agora",
				timeOnlyTitle: 'Escolha a hora',
				timeText: 'Horário',
				hourText: 'Hora',
				minuteText: 'Minuto',
				secondText: 'Segundo'
			});
			// fim - data_suporte_fim

			//endregion - fim - calendario -------------------------------------------------------

			// data_inicio - verifica se é uma data válida/agenda auto
			$('#data_inicio').blur(function() {

				var campo = $(this);

				<? if ($tipo_suporte_inloco == "cs" or $tipo_suporte_inloco == "cn" or $tipo_suporte_inloco == "p") { ?>

					// erro
					var erro = funcao_verifica_data_hora_valida(campo, <?php echo time() * 1000 ?>) // chamada da função (retorna 0/1)		
					if (erro == 1) {

						alert("Data inválida");
						$('#data_inicio').val('');
						$('#agendamento_tempo').val('');
						$('#data_fim').val('');
						setTimeout(function() {
							campo.focus();
						}, 100);
						return false;

					}
					// fim - erro

					// agenda auto
					else if ($(this).val().length == 16) {

						$('#agendamento_tempo').val('');
						$('#data_fim').val('');

						var usuario_responsavel = $("select[name=usuario_responsavel]").val();
						data_atual = $('#data_inicio').val();

						tb_show("Agenda", "agenda_popup.php?usuario_atual=" + usuario_responsavel + "&data_atual=" + data_atual + "&height=<? echo $suporte_editar_tabela_height - 100; ?>&width=<? echo $suporte_editar_tabela_width - 40; ?>&placeValuesBeforeTB_=savedValues&TB_iframe=true", "");
						return false;

					}
					// fim - agenda auto

				<? } else if ($tipo_suporte_inloco == "r") { ?>

					// erro
					var erro = funcao_verifica_data_hora_valida(campo, <?php echo time() * 1000 ?>) // chamada da função (retorna 0/1)		
					if (erro == 1) {

						alert("Data inválida");
						$('#data_inicio').val('');
						$('#data_fim').val('');
						setTimeout(function() {
							campo.focus();
						}, 100);
						return false;

					}
					// fim - erro

				<? } ?>

			});
			// fim - data_inicio - verifica se é uma data válida/agenda auto

			// data_fim - verifica se é uma data válida
			$('#data_fim').blur(function() {

				var campo = $(this);

				<? if ($tipo_suporte_inloco == "r") { ?>

					// erro
					var erro = funcao_verifica_data_hora_valida(campo, <?php echo time() * 1000 ?>) // chamada da função (retorna 0/1)		
					if (erro == 1) {

						alert("Data inválida");
						$('#data_fim').val('');
						setTimeout(function() {
							campo.focus();
						}, 100);
						return false;

					}
					// fim - erro

				<? } ?>

			});
			// fim - data_fim - verifica se é uma data válida

			// atendimento_previsao **********************************************************************************************
			$('#atendimento_previsao').mask('99-99-9999 99:99', {
				placeholder: " "
			});

			$('#atendimento_previsao').datetimepicker({

				showOn: "button",
				buttonImage: "css/images/calendar.gif",
				buttonImageOnly: true,

				showSecond: false,
				inline: true,
				dateFormat: 'dd-mm-yy',
				timeFormat: 'HH:mm',
				dayNames: [
					'Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado', 'Domingo'
				],
				dayNamesMin: [
					'D', 'S', 'T', 'Q', 'Q', 'S', 'S', 'D'
				],
				dayNamesShort: [
					'Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb', 'Dom'
				],
				monthNames: [
					'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro',
					'Outubro', 'Novembro', 'Dezembro'
				],
				monthNamesShort: [
					'Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set',
					'Out', 'Nov', 'Dez'
				],
				nextText: 'Próximo',
				prevText: 'Anterior',
				closeText: "Fechar",
				currentText: "Agora",
				timeOnlyTitle: 'Escolha a hora',
				timeText: 'Horário',
				hourText: 'Hora',
				minuteText: 'Minuto',
				secondText: 'Segundo',
				minDate: new Date(<?php echo time() * 1000 ?>)
			});

			// verifica se é uma data válida
			$('#atendimento_previsao').blur(function() {

				var campo = $(this);

				// erro
				var erro = funcao_verifica_data_hora_valida(campo, <?php echo time() * 1000 ?>) // chamada da função (retorna 0/1)		
				if (erro == 1) {

					alert("Data inválida");
					$('#atendimento_previsao').val('');
					setTimeout(function() {
						campo.focus();
					}, 100);
					return false;

				}
				// fim - erro

			});
			// fim - verifica se é uma data válida
			// fim - atendimento_previsao *****************************************************************************************

			// reclamacao_data_acordada - verifica se é uma data válida
			$('#reclamacao_data_acordada').blur(function() {

				var campo = $(this);

				<? if ($tipo_suporte_inloco == "r") { ?>

					// erro
					var erro = funcao_verifica_data_hora_valida(campo, <?php echo time() * 1000 ?>) // chamada da função (retorna 0/1)		
					if (erro == 1) {

						alert("Data inválida");
						$('#reclamacao_data_acordada').val('');
						setTimeout(function() {
							campo.focus();
						}, 100);
						return false;

					}
					// fim - erro

				<? } ?>

			});
			// fim - reclamacao_data_acordada - verifica se é uma data válida

			// agendamento_tempo
			$("select[name=agendamento_tempo]").change(function() {

				var agendamento_tempo = $(this).val();

				var data_inicio = $("#data_inicio").val();
				var quebraDI = data_inicio.split("-");
				var diaDI = quebraDI[0];
				var mesDI = quebraDI[1];
				var anoDI = quebraDI[2].substr(0, 4);
				var time_inicial = quebraDI[2].substr(5, 8);
				var quebraTimeDI = time_inicial.split(":");
				var horaDI = quebraTimeDI[0];
				var minutoDI = quebraTimeDI[1];

				// current date
				var date = new Date(anoDI, mesDI - 1, diaDI, horaDI, minutoDI, 0);

				// future date
				var new_date = new Date(date);

				var minutes = parseInt($("#agendamento_tempo").val());

				// Add the minutes to current date to arrive at the new date
				new_date.setMinutes(date.getMinutes() + minutes);

				date1 = new_date.format('dd-mm-yyyy HH:MM'); // date.format.js

				$("#data_fim").val(date1);

			});
			// fim - agendamento_tempo

			// solucionado
			$('#div_solucionado_nao').hide();
			$("textarea[name='solucionado_nao']").val('');

			$("select[name='solucionado']").change(function() { // ao mudar o valor do select

				var solucionado_atual = $(this).val(); // lê o valor selecionado

				if(solucionado_atual == 'n'){
					$('#div_solucionado_nao').show();
					$("textarea[name='solucionado_nao']").val('');
				} else {
					$('#div_solucionado_nao').hide();
					$("textarea[name='solucionado_nao']").val('');
				}

			});
			// fim - solucionado

			// anomalia_simulada
			$('#div_anomalia_simulada_nao').hide();
			$('#div_anomalia_simulada_afirmacao').hide();
			$("select[name='anomalia_simulada_afirmacao'] option:first").attr('selected','selected');

			$("select[name='anomalia_simulada']").change(function() { // ao mudar o valor do select

				var anomalia_simulada_atual = $(this).val(); // lê o valor selecionado

				if(anomalia_simulada_atual == 's' || anomalia_simulada_atual == 'a'){
					$('#div_anomalia_simulada_nao').hide();
					$('#div_anomalia_simulada_afirmacao').hide();
					$("select[name='anomalia_simulada_afirmacao'] option:first").attr('selected','selected');
				} else {
					$('#div_anomalia_simulada_nao').show();
					$('#div_anomalia_simulada_afirmacao').show();
					$("select[name='anomalia_simulada_afirmacao'] option:first").attr('selected','selected');
				}

			});
			// fim - anomalia_simulada

			// tempo gasto
			<?php
			$tg_dias_restantes = 0;
			$tg_horas_restantes = 23;
			$tg_minutos_restantes = 59;

			if (isset($row_suporte['previsao_geral_inicio'])) {
				$tempo_gasto_data = $row_suporte['previsao_geral_inicio'];
			} else {
				$tempo_gasto_data = $row_suporte['data_suporte'];
			}

			if ($tempo_gasto_data != NULL) {

				#Calculamos a contagem regressiva
				$previsao_geral_inicio_diferenca = strtotime(date('Y-m-d H:i:s')) - strtotime($tempo_gasto_data);

				// valida a quantidade de dias, deixando livre minutos e segundos pois o usuário tem a opção de escolhar dias completos ou horas ou minutos.
				$tg_dias_restantes = floor($previsao_geral_inicio_diferenca / 60 / 60 / 24);

				if ($tg_dias_restantes < 1) { // se é menor que 1 dia, então valida somente horas e minutos.

					$tg_horas_restantes = floor(($previsao_geral_inicio_diferenca - ($tg_dias_restantes * 60 * 60 * 24)) / 60 / 60);

					if ($tg_horas_restantes < 1) { // se é menor que 1 hora, então valida somente minutos.

						$tg_minutos_restantes = floor(($previsao_geral_inicio_diferenca - ($tg_dias_restantes * 60 * 60 * 24) - ($tg_horas_restantes * 60 * 60)) / 60);
					}
				}
			}
			?>

			$('#tempo_gasto').timepicker({
				showSecond: true,
				timeFormat: 'HH:mm:ss',
				timeText: 'Tempo',
				timeOnlyTitle: 'Informe o tempo',

				hourText: 'Dias',
				hourMax: <? echo $tg_dias_restantes; ?>,

				minuteText: 'Horas',
				minuteMax: <? echo $tg_horas_restantes; ?>,

				secondText: 'Minutos',
				secondMax: <? echo $tg_minutos_restantes; ?>,

				closeText: "Fechar",
				currentText: ''
			});
			$('#tempo_gasto').click(function() {
				$('.ui-datepicker-current').css('display', 'none'); // oculta o 'currentText'
			});
			// tempo gasto

		});
	</script>
</head>

<body>

	<div class="div_solicitacao_linhas">
		<table cellspacing="0" cellpadding="0" width="100%">
			<tr>
				<td style="text-align:left">
					Suporte número: <?php echo $row_suporte['id']; ?>
				</td>

				<td style="text-align: right">
					&lt;&lt; <a href="suporte_editar.php?id_suporte=<?php echo $_GET['id_suporte']; ?>" target="_top">Voltar</a>
				</td>
			</tr>
		</table>
	</div>

	<div class="div_solicitacao_linhas3">
		<table cellspacing="0" cellpadding="0">
			<tr>
				<td style="text-align: left">
					<strong><? echo $_GET['acao']; ?> </strong>
					<? if ($_GET['resposta'] == "sim" or $_GET['resposta'] == "nao") {
						echo " (" . $_GET['resposta'] . ") ";
					} ?>
					<? if (isset($_GET['id_agenda']) != "") {
						echo " (" . $_GET['id_agenda'] . ") ";
					} ?>
				</td>
			</tr>
		</table>
	</div>

	<div class="div_solicitacao_linhas3">
		<table cellspacing="0" cellpadding="0">
			<tr>
				<td style="text-align: left">
					Título: <?php echo $row_suporte['titulo']; ?>
				</td>
			</tr>
		</table>
	</div>

	<? if (
		$_GET['situacao'] == "editar" and
		($_GET['acao'] == "Encerrar" or
			$_GET['acao'] == "Encaminhar para solicitação") and
		$tipo_suporte_inloco == "cs" and
		$row_suporte['suporte_arquivos_contador'] == 0
	) {
	?>
		<div class="div_solicitacao_linhas4">
			<table cellspacing="0" cellpadding="0">
				<tr>
					<td style="text-align: left">
						<span style="color: #C00; font-weight: bold;"><? echo $row_parametros['suporte_encerrar_mensagem_arquivos']; ?></span>
					</td>
				</tr>
			</table>
		</div>
	<? } ?>

	<!-- Agendamento atual -->
	<? if (
		$_GET['situacao'] == "editar" and

		$totalRows_agenda_agendado > 0 and
		(
			($_GET["acao"] == "Agendamento" or $_GET["acao"] == "Finalizar agendamento") or
			$_GET['acao'] == "Reagendar")
	) { ?>

		<div class="div_solicitacao_linhas4">
			<table cellspacing="0" cellpadding="0" width="100%">
				<tr>
					<td style="text-align: left">
						<span class="label_solicitacao">Agendamento atual: </span>
						<? echo date('d-m-Y H:i:s', strtotime($row_agenda_agendado['data_inicio'])); ?> à <? echo date('d-m-Y H:i:s', strtotime($row_agenda_agendado['data'])); ?>
						<div style="margin-top: 5px;">
							OBS: <? echo $row_agenda_agendado['descricao']; ?>
						</div>
					</td>
				</tr>
			</table>
		</div>

	<? } ?>
	<!-- fim - Agendamento atual -->

	<!-- Alterar previsão -->
	<? if (
		$_GET['situacao'] == "editar" and
		($_GET['acao'] == "Alterar previsão")
	) { ?>
		<div class="div_solicitacao_linhas3">
			<table cellspacing="0" cellpadding="0" width="100%">
				<tr>
					<td style="text-align: left">
						<span class="label_solicitacao">
							Previsão atual:
							<?
							// inicio
							if (isset($row_suporte['previsao_geral_inicio'])) {
								echo date('d-m-Y  H:i', strtotime($row_suporte['previsao_geral_inicio']));
							}
							?>
							à
							<?
							// fim
							if (isset($row_suporte['previsao_geral'])) {
								echo date('d-m-Y  H:i', strtotime($row_suporte['previsao_geral']));
							}
							?>
						</span>
					</td>
				</tr>
			</table>
		</div>
	<? } ?>
	<!-- fim - Alterar previsão -->

	<!-- Contato -->
	<? if (
		$_GET['situacao'] == "editar" and
		$_GET['acao'] == "Contato" and
		$totalRows_suporte_contato > 0
	) { ?>

		<div class="div_solicitacao_linhas4">
			<table cellspacing="0" cellpadding="0" width="100%">
				<tr>
					<td style="text-align: left">
						<span class="label_solicitacao">Contato: </span>
						<br>
						<table width="100%" border="0" cellspacing="0" cellpadding="0" style="margin-top: 5px;">
							<thead>
								<tr bgcolor="#F1F1F1">
									<th width="15%" style="padding: 5px;" align="left">Data</th>
									<th width="15%" style="padding: 5px;" align="left">Responsável</th>
									<th width="15%" style="padding: 5px;" align="left">Contato</th>
									<th width="15%" style="padding: 5px;" align="left">Telefone</th>
									<th style="padding: 5px;" align="left">Observação</th>
								</tr>
							</thead>

							<tbody>
								<? $contador_suporte_contato = 0; ?>
								<? do { ?>
									<? $contador_suporte_contato = $contador_suporte_contato + 1; ?>
									<tr>

										<td style="padding: 5px;" align="left">
											<span style=" color: <? if (($contador_suporte_contato % 2) == 0) {
																		echo "#F00";
																	} else {
																		echo "#000";
																	} ?>;">
												<? if ($row_suporte_contato['data'] != "") {
													echo date('d-m-Y H:i', strtotime($row_suporte_contato['data']));
												} ?>
											</span>
										</td>

										<td style="padding: 5px;" align="left">
											<span style=" color: <? if (($contador_suporte_contato % 2) == 0) {
																		echo "#F00";
																	} else {
																		echo "#000";
																	} ?>;">
												<? echo $row_suporte_contato['usuario_responsavel']; ?>
											</span>
										</td>

										<td style="padding: 5px;" align="left">
											<span style=" color: <? if (($contador_suporte_contato % 2) == 0) {
																		echo "#F00";
																	} else {
																		echo "#000";
																	} ?>;">
												<? echo $row_suporte_contato['responsavel']; ?>
											</span>
										</td>

										<td style="padding: 5px;" align="left">
											<span style=" color: <? if (($contador_suporte_contato % 2) == 0) {
																		echo "#F00";
																	} else {
																		echo "#000";
																	} ?>;">
												<? echo $row_suporte_contato['telefone']; ?>
											</span>
										</td>

										<td style="padding: 5px;" align="left">
											<span style=" color: <? if (($contador_suporte_contato % 2) == 0) {
																		echo "#F00";
																	} else {
																		echo "#000";
																	} ?>;">
												<? echo $row_suporte_contato['descricao']; ?>
											</span>
										</td>

									</tr>
								<?php } while ($row_suporte_contato = mysql_fetch_assoc($suporte_contato)); ?>
							</tbody>
						</table>
					</td>
				</tr>
			</table>
		</div>

	<? } ?>
	<!-- fim - Contato -->

	<div class="div_solicitacao_linhas2">
		<table cellspacing="0" cellpadding="0">
			<tr>
				<td style="text-align: left">
					<form id="form" name="form" method="POST" action="<?php echo $editFormAction; ?>" class="cmxform" target="_top">

						<!-- titulo -->
						<? if (
							($_GET['situacao'] == "editar" and $_GET['acao'] == "Alterar título")
						) { ?>
							<div style="padding-bottom: 10px;">
								<div class="label_solicitacao2">Título*:</div>
								<input type="text" name="titulo" id="titulo" value="<? echo $row_suporte['titulo']; ?>" style="width:760px" />
							</div>
						<? } ?>
						<!-- fim - titulo -->


						<!-- solicitante -->
						<? if (
							$_GET['situacao'] == "editar" and
							($_GET['acao'] == "Alterar solicitante")
						) { ?>
							<div style="padding-bottom: 10px;">
								<div class="label_solicitacao2">Solicitante*:</div>
								<input type="text" name="solicitante" id="solicitante" value="<? echo $row_suporte['solicitante']; ?>" style="width: 320px" />
							</div>
						<? } ?>
						<!-- fim - solicitante -->

						<!-- envolvido_reclamacao -->
						<? if (
							$_GET['situacao'] == "editar" and
							($_GET['acao'] == "Alterar envolvido(s) na reclamação")
						) { ?>
							<div style="padding-bottom: 10px;">
								<div class="label_solicitacao2">Envolvido(s) na reclamação*:</div>
								<input type="text" name="envolvido_reclamacao" id="envolvido_reclamacao" value="<? echo $row_suporte['envolvido_reclamacao']; ?>" style="width: 320px" />
							</div>
						<? } ?>
						<!-- fim - envolvido_reclamacao -->

						<!-- reagendamento_solicitante -->
						<? if (
							$_GET['situacao'] == "editar" and
							($_GET['acao'] == "Reagendar")
						) { ?>
							<div style="padding-bottom: 10px;">
								<div class="label_solicitacao2">Solicitante do reagendamento*:</div>
								<input type="text" name="reagendamento_solicitante" id="reagendamento_solicitante" style="width: 320px" />
							</div>
						<? } ?>
						<!-- fim - reagendamento_solicitante -->


						<!-- usuario_responsavel -->
						<?
						if (
							($_GET['situacao'] == "editar" and
								($_GET['acao'] == "Alterar usuário responsável" or $_GET['acao'] == "Reagendar"))

							or

							(
								($_GET['situacao'] == "analisada" or $_GET['situacao'] == "em execução") and
								$_GET['acao'] == "Encaminhar")
						) { ?>
							<div style="padding-bottom: 10px;">
								<?
								mysql_select_db($database_conexao, $conexao);

								$filtro_suporte_responsavel = "1=1";

								// se existe filtragem por praça ou não
								if ($tipo_suporte_inloco == "p" and ($row_usuario['controle_suporte'] == "Y" or $row_usuario['suporte_operador_parceiro'] == "Y")) {
									$filtro_suporte_responsavel .= " and (controle_suporte = 'Y' or suporte_operador_parceiro = 'Y')";
								} else if ($tipo_suporte_inloco == "r") {
									$filtro_suporte_responsavel .= " and praca = '" . $row_suporte['praca'] . "'";
								} else if ($tipo_suporte_inloco == "cs") {
									$filtro_suporte_responsavel .= " and praca = '" . $row_suporte['praca'] . "'";
								} else if ($tipo_suporte_inloco == "cn" and ($row_usuario['controle_suporte'] == "Y" or $row_usuario['suporte_operador_parceiro'] == "Y")) {
									$filtro_suporte_responsavel .= " and (praca = '" . $row_suporte['praca'] . "' or (controle_suporte = 'Y' or suporte_operador_parceiro = 'Y'))";
								} else if ($tipo_suporte_inloco == "cn") {
									$filtro_suporte_responsavel .= " and praca = '" . $row_suporte['praca'] . "'";
								} else {
									$filtro_suporte_responsavel .= " and 1=2";
								}
								// fim - se existe filtragem por praça ou não

								$query_usuario_responsavel = "
								SELECT 
									IdUsuario, nome, praca 
								FROM 
									usuarios 
								WHERE 
									status = 1 and 
									$filtro_suporte_responsavel 
								ORDER BY 
									praca, nome ASC
								";
								$usuario_responsavel = mysql_query($query_usuario_responsavel, $conexao) or die(mysql_error());
								$row_usuario_responsavel = mysql_fetch_assoc($usuario_responsavel);
								$totalRows_usuario_responsavel = mysql_num_rows($usuario_responsavel);
								?>
								<div class="label_solicitacao2">Usuário responsável*:</div>
								<select name="usuario_responsavel" id="usuario_responsavel">
									<option value="">Escolha o usuário responsável ...</option>
									<?php
									do {
									?>
										<option title="<?php echo $row_usuario_responsavel['IdUsuario'] ?>" value="<?php echo $row_usuario_responsavel['IdUsuario'] ?>"><?php echo $row_usuario_responsavel['nome'] ?> [<?php echo $row_usuario_responsavel['praca'] ?>]</option>

									<?php
									} while ($row_usuario_responsavel = mysql_fetch_assoc($usuario_responsavel));
									$rows = mysql_num_rows($usuario_responsavel);
									if ($rows > 0) {
										mysql_data_seek($usuario_responsavel, 0);
										$row_usuario_responsavel = mysql_fetch_assoc($usuario_responsavel);
									}
									?>
								</select>
								<? mysql_free_result($usuario_responsavel); ?>
							</div>
						<? } ?>
						<!-- fim - usuario_responsavel -->


						<!-- usuario_envolvido -->
						<? if (
							($_GET['situacao'] == "editar" and $_GET['acao'] == "Alterar usuário envolvido")
						) { ?>
							<div style="padding-bottom: 10px;">
								<?				
								mysql_select_db($database_conexao, $conexao);

								$filtro_suporte_envolvido = "1=1";

								// se existe filtragem por praça ou não 
								if ($tipo_suporte_inloco == "p" and $row_usuario['controle_praca'] == "Y" and $row_suporte['praca'] == $row_usuario['praca']) {
									$filtro_suporte_envolvido .= " and praca = '" . $row_suporte['praca'] . "' ";
								} else if ($tipo_suporte_inloco == "p") {
									$filtro_suporte_envolvido .= " and IdUsuario != ".$row_usuario['IdUsuario']." ";
								} else if ($tipo_suporte_inloco == "cs" or $tipo_suporte_inloco == "cn") {
									$filtro_suporte_envolvido .= " and praca = '" . $row_suporte['praca'] . "' ";
								} else {
									$filtro_suporte_envolvido .= " and 1=2";
								}
								// fim - se existe filtragem por praça ou não

								$query_usuario_envolvido = "
								SELECT 
									IdUsuario, nome, praca 
								FROM 
									usuarios 
								WHERE 
									status = 1 and 
									IdUsuario != '".$row_suporte['id_usuario_envolvido']."' and 
									$filtro_suporte_envolvido 
								ORDER BY 
									praca, nome ASC
								";

								$usuario_envolvido = mysql_query($query_usuario_envolvido, $conexao) or die(mysql_error());
								$row_usuario_envolvido = mysql_fetch_assoc($usuario_envolvido);
								$totalRows_usuario_envolvido = mysql_num_rows($usuario_envolvido);
								?>
								<div class="label_solicitacao2">Usuário envolvido*:</div>
								<select name="usuario_envolvido" id="usuario_envolvido">
									<option value="">Escolha o usuário envolvido ...</option>
									<?php
									do {
									?>
										<option value="<?php echo $row_usuario_envolvido['IdUsuario'] ?>"><?php echo $row_usuario_envolvido['nome'] ?> [<?php echo $row_usuario_envolvido['praca'] ?>]</option>
									<?php
									} while ($row_usuario_envolvido = mysql_fetch_assoc($usuario_envolvido));
									?>
								</select>
								<? mysql_free_result($usuario_envolvido); ?>
							</div>
						<? } ?>
						<!-- fim - usuario_envolvido -->


						<!-- ordem_servico -->
						<? if (
							$_GET['situacao'] == "editar" and
							($_GET['acao'] == "Desbloquear")
						) { ?>
							<div style="padding-bottom: 10px;">
								<div class="label_solicitacao2">Ordem de Serviço*:</div>
								<input type="text" name="ordem_servico" id="ordem_servico" value="<? echo $row_suporte['ordem_servico']; ?>" style="width: 320px" />
							</div>
						<? } ?>
						<!-- fim - ordem_servico -->


						<!-- geral_tipo_modulo -->
						<? if (
							($_GET['situacao'] == "editar" and $_GET['acao'] == "Alterar módulo")
						) { ?>
							<div style="padding-bottom: 10px;">
								<div class="label_solicitacao2">Módulo*:</div>
								<?php
								mysql_select_db($database_conexao, $conexao);
								$query_geral_tipo_modulo = "SELECT * FROM geral_tipo_modulo ORDER BY descricao ASC";
								$geral_tipo_modulo = mysql_query($query_geral_tipo_modulo, $conexao) or die(mysql_error());
								$row_geral_tipo_modulo = mysql_fetch_assoc($geral_tipo_modulo);
								$totalRows_geral_tipo_modulo = mysql_num_rows($geral_tipo_modulo);
								?>
								<select name="geral_tipo_modulo" id="geral_tipo_modulo" style="width: 400px;">
									<option value="" <?php if (!(strcmp("", $row_suporte['modulo']))) {
															echo "selected=\"selected\"";
														} ?>>Selecione ...</option>
									<?php
									do {
									?>
										<option value="<?php echo $row_geral_tipo_modulo['descricao'] ?>" <?php if (!(strcmp($row_geral_tipo_modulo['descricao'], $row_suporte['modulo']))) {
																												echo "selected=\"selected\"";
																											} ?>>
											<?php echo $row_geral_tipo_modulo['descricao'] ?>
										</option>
									<?php
									} while ($row_geral_tipo_modulo = mysql_fetch_assoc($geral_tipo_modulo));
									$rows = mysql_num_rows($geral_tipo_modulo);
									if ($rows > 0) {
										mysql_data_seek($geral_tipo_modulo, 0);
										$row_geral_tipo_modulo = mysql_fetch_assoc($geral_tipo_modulo);
									}
									?>
								</select>
								<? mysql_free_result($geral_tipo_modulo); ?>
							</div>
						<? } ?>
						<!-- fim - geral_tipo_modulo -->


						<!-- suporte_tipo_atendimento -->
						<? if (
							($_GET['situacao'] == "editar" and $_GET['acao'] == "Alterar tipo de atendimento")
						) { ?>
							<div style="padding-bottom: 10px;">
								<div class="label_solicitacao2">Tipo de atendimento*:</div>
								<?php
								mysql_select_db($database_conexao, $conexao);
								$query_suporte_tipo_atendimento = "SELECT * FROM suporte_tipo_atendimento ORDER BY descricao ASC";
								$suporte_tipo_atendimento = mysql_query($query_suporte_tipo_atendimento, $conexao) or die(mysql_error());
								$row_suporte_tipo_atendimento = mysql_fetch_assoc($suporte_tipo_atendimento);
								$totalRows_suporte_tipo_atendimento = mysql_num_rows($suporte_tipo_atendimento);
								?>
								<select name="suporte_tipo_atendimento" id="suporte_tipo_atendimento" style="width: 400px;">
									<option value="" <?php if (!(strcmp("", $row_suporte['tipo_atendimento']))) {
															echo "selected=\"selected\"";
														} ?>>Selecione ...</option>
									<?php
									do {
									?>
										<option value="<?php echo $row_suporte_tipo_atendimento['descricao'] ?>" <?php if (!(strcmp($row_suporte_tipo_atendimento['descricao'], $row_suporte['tipo_atendimento']))) {
																													echo "selected=\"selected\"";
																												} ?>>
											<?php echo $row_suporte_tipo_atendimento['descricao'] ?>
										</option>
									<?php
									} while ($row_suporte_tipo_atendimento = mysql_fetch_assoc($suporte_tipo_atendimento));
									$rows = mysql_num_rows($suporte_tipo_atendimento);
									if ($rows > 0) {
										mysql_data_seek($suporte_tipo_atendimento, 0);
										$row_suporte_tipo_atendimento = mysql_fetch_assoc($suporte_tipo_atendimento);
									}
									?>
								</select>
								<? mysql_free_result($suporte_tipo_atendimento); ?>
							</div>
						<? } ?>
						<!-- fim - suporte_tipo_atendimento -->


						<!-- suporte_tipo_prioridade -->
						<? if (
							($_GET['situacao'] == "editar" and $_GET['acao'] == "Alterar prioridade")
						) { ?>
							<div style="padding-bottom: 10px;">
								<div class="label_solicitacao2">Prioridade*:</div>
								<?php
								mysql_select_db($database_conexao, $conexao);
								$query_suporte_tipo_prioridade = "SELECT * FROM suporte_tipo_prioridade ORDER BY titulo ASC";
								$suporte_tipo_prioridade = mysql_query($query_suporte_tipo_prioridade, $conexao) or die(mysql_error());
								$row_suporte_tipo_prioridade = mysql_fetch_assoc($suporte_tipo_prioridade);
								$totalRows_suporte_tipo_prioridade = mysql_num_rows($suporte_tipo_prioridade);
								?>
								<select name="suporte_tipo_prioridade" id="suporte_tipo_prioridade" style="width: 200px;">
									<option value="" <?php if (!(strcmp("", $row_suporte['prioridade']))) {
															echo "selected=\"selected\"";
														} ?>>Selecione ...</option>
									<?php
									do {
									?>
										<option value="<?php echo $row_suporte_tipo_prioridade['titulo'] ?>" <?php if (!(strcmp($row_suporte_tipo_prioridade['titulo'], $row_suporte['prioridade']))) {
																												echo "selected=\"selected\"";
																											} ?>>
											<?php echo $row_suporte_tipo_prioridade['titulo'] ?>
										</option>
									<?php
									} while ($row_suporte_tipo_prioridade = mysql_fetch_assoc($suporte_tipo_prioridade));
									$rows = mysql_num_rows($suporte_tipo_prioridade);
									if ($rows > 0) {
										mysql_data_seek($suporte_tipo_prioridade, 0);
										$row_suporte_tipo_prioridade = mysql_fetch_assoc($suporte_tipo_prioridade);
									}
									?>
								</select>
								<? mysql_free_result($suporte_tipo_prioridade); ?>
							</div>

							<div style="padding-bottom: 10px;" id="prioridade_justificativa_caixa">
								<div class="label_solicitacao2">Justificativa da prioridade*:</div>
								<textarea name="prioridade_justificativa" id="prioridade_justificativa" style="width:760px; height: 80px;" /><? echo $row_suporte['prioridade_justificativa']; ?></textarea>
							</div>
						<? } ?>

						<!-- fim - suporte_tipo_prioridade -->


						<!-- questionamento -->
						<? if (
							($_GET['acao'] == "Questionar")
						) { ?>
							<div style="padding-bottom: 10px;">
								<style type="text/css">
									.block {
										display: block;
									}

									form.cmxform label.error {
										display: none;
									}
								</style>
								Selecione para quem será direcionada a mensagem: <br>
								<fieldset style="border: 1px solid #CCCCCC; padding: 5px;">

									<? if ($row_suporte['situacao'] != "solucionada" and $row_suporte['situacao'] != "cancelada" and $row_suporte['situacao'] != "criada") { ?>

										<? $status_usuario_questionamento = 0; ?>

										<!-- usuario_responsavel -->
										<? if (
											$row_suporte['id_usuario_responsavel'] != "" and
											$row_suporte['id_usuario_responsavel'] != $row_usuario['IdUsuario']
										) { ?>
											<? $status_usuario_questionamento = 1; ?>
											<input name="questionado" id="questionado" type="radio" value="usuário responsável" validate="required:true"> usuário responsável
										<? } ?>
										<!-- usuario_responsavel -->


										<!-- usuario_envolvido -->
										<? if (
											$row_suporte['id_usuario_envolvido'] != "" and
											$row_suporte['id_usuario_envolvido'] != $row_usuario['IdUsuario']
										) { ?>
											<? $status_usuario_questionamento = 1; ?>
											<input name="questionado" id="questionado" type="radio" value="usuário envolvido" validate="required:true"> usuário envolvido
										<? } ?>
										<!-- usuario_envolvido -->

										<? if ($row_suporte['status_questionamento'] == "" and $status_usuario_questionamento == 0) { ?>
											<input name="questionado" id="questionado" type="radio" value="" checked="checked" validate="required:true"> <em>comentar</em>
										<? } ?>

										<? if ($row_suporte['status_questionamento'] != "") { ?>
											<input name="questionado" id="questionado" type="radio" value="" validate="required:true"> <em>responder questionamento</em>
										<? } ?>

									<? } else if (
										($row_suporte['id_usuario_responsavel'] == $row_usuario['IdUsuario']) or
										($row_suporte['id_usuario_envolvido'] == $row_usuario['IdUsuario']) or
										($row_usuario['controle_suporte'] == "Y") or
										($row_usuario['controle_praca'] == "Y" and $row_usuario['praca'] = $row_suporte['praca']) or
										($row_usuario['suporte_operador_parceiro'] == "Y")
									) { ?>

										<input name="questionado" id="questionado" type="radio" value="" checked="checked" validate="required:true"> <em>comentar</em>

									<? } ?>

								</fieldset>
								<label for="questionado" class="error">Selecione quem será questionado</label>
							</div>
						<? } ?>
						<!-- fim - questionamento -->


						<!-- anomalia -->
						<? if (
							($_GET['situacao'] == "editar" and $_GET['acao'] == "Alterar anomalia")
						) { ?>
							<div style="padding-bottom: 10px;">
								<div class="label_solicitacao2">Anomalia*:</div>
								<textarea name="anomalia" id="anomalia" style="width:760px; height: 80px;" /><? echo $row_suporte['anomalia']; ?></textarea>
							</div>
						<? } ?>
						<!-- fim - anomalia -->


						<!-- reclamacao -->
						<? if (
							($_GET['situacao'] == "editar" and $_GET['acao'] == "Alterar reclamação")
						) { ?>
							<div style="padding-bottom: 10px;">
								<div class="label_solicitacao2">Reclamação*:</div>
								<textarea name="reclamacao" id="reclamacao" style="width:760px; height: 80px;" /><? echo $row_suporte['reclamacao']; ?></textarea>
							</div>
						<? } ?>
						<!-- fim - reclamacao -->


						<!-- reclamacao_questionamento -->
						<? if (
							($_GET['situacao'] == "editar" and $_GET['acao'] == "Alterar questionamento inicial")
						) { ?>
							<div style="padding-bottom: 10px;">
								<div class="label_solicitacao2">Questionamento inicial*:</div>
								<textarea name="reclamacao_questionamento" id="reclamacao_questionamento" style="width:760px; height: 80px;" /><? echo $row_suporte['reclamacao_questionamento']; ?></textarea>
							</div>
						<? } ?>
						<!-- fim - reclamacao_questionamento -->


						<!-- suporte_tipo_percepcao (reclamação) -->
						<? if (
							($_GET['situacao'] == "editar" and $_GET['acao'] == "Alterar percepção")
						) { ?>
							<div style="padding-bottom: 10px;">
								<div class="label_solicitacao2">Percepção*:</div>
								<?php
								mysql_select_db($database_conexao, $conexao);
								$query_suporte_tipo_percepcao = "SELECT * FROM suporte_tipo_percepcao ORDER BY titulo ASC";
								$suporte_tipo_percepcao = mysql_query($query_suporte_tipo_percepcao, $conexao) or die(mysql_error());
								$row_suporte_tipo_percepcao = mysql_fetch_assoc($suporte_tipo_percepcao);
								$totalRows_suporte_tipo_percepcao = mysql_num_rows($suporte_tipo_percepcao);
								?>
								<select name="suporte_tipo_percepcao" id="suporte_tipo_percepcao" style="width: 200px;">
									<option value="" <?php if (!(strcmp("", $row_suporte['reclamacao_percepcao']))) {
															echo "selected=\"selected\"";
														} ?>>Selecione ...</option>
									<?php
									do {
									?>
										<option value="<?php echo $row_suporte_tipo_percepcao['titulo'] ?>" <?php if (!(strcmp($row_suporte_tipo_percepcao['titulo'], $row_suporte['reclamacao_percepcao']))) {
																												echo "selected=\"selected\"";
																											} ?>>
											<?php echo $row_suporte_tipo_percepcao['titulo'] ?>
										</option>
									<?php
									} while ($row_suporte_tipo_percepcao = mysql_fetch_assoc($suporte_tipo_percepcao));
									$rows = mysql_num_rows($suporte_tipo_percepcao);
									if ($rows > 0) {
										mysql_data_seek($suporte_tipo_percepcao, 0);
										$row_suporte_tipo_percepcao = mysql_fetch_assoc($suporte_tipo_percepcao);
									}
									?>
								</select>
								<? mysql_free_result($suporte_tipo_percepcao); ?>
							</div>
						<? } ?>
						<!-- fim - suporte_tipo_percepcao (reclamação) -->


						<!-- reclamacao_telefone -->
						<? if (
							($_GET['situacao'] == "editar" and $_GET['acao'] == "Alterar telefone reclamante")
						) { ?>
							<div style="padding-bottom: 10px;">
								<div class="label_solicitacao2">Telefone de contato reclamante*:</div>
								<input type="text" name="reclamacao_telefone" id="reclamacao_telefone" value="<? echo $row_suporte['reclamacao_telefone']; ?>" style="width:150px" maxlength="13" />
							</div>
						<? } ?>
						<!-- fim - reclamacao_telefone -->

						<!-- reclamacao_responsavel -->
						<? if (
							($_GET['situacao'] == "editar" and $_GET['acao'] == "Alterar reclamante")
						) { ?>
							<div style="padding-bottom: 10px;">
								<div class="label_solicitacao2">Reclamante*:</div>
								<input type="text" name="reclamacao_responsavel" id="reclamacao_responsavel" value="<? echo $row_suporte['reclamacao_responsavel']; ?>" style="width:760px" />
							</div>
						<? } ?>
						<!-- fim - reclamacao_responsavel -->

						<!-- reclamacao_data_acordada -->
						<? if (
							($_GET['situacao'] == "editar" and $_GET['acao'] == "Alterar data acordada")
						) { ?>
							<div style="padding-bottom: 10px;">
								<div class="label_solicitacao2">Data acordada*:</div>
								<input type="text" name="reclamacao_data_acordada" id="reclamacao_data_acordada" value="<? echo date('d-m-Y H:i', strtotime($row_suporte['reclamacao_data_acordada'])); ?>" style="width:150px" />
							</div>
						<? } ?>
						<!-- fim - reclamacao_data_acordada --


						<!-- orientacao -->
						<? if (
							($_GET['situacao'] == "editar" and $_GET['acao'] == "Alterar orientação")
						) { ?>
							<div style="padding-bottom: 10px;">
								<div class="label_solicitacao2">Orientação*:</div>
								<textarea name="orientacao" id="orientacao" style="width:760px; height: 80px;" /><? echo $row_suporte['orientacao']; ?></textarea>
							</div>
						<? } ?>
						<!-- fim - orientacao -->


						<!-- data_inicio -->
						<? if (
							$_GET['situacao'] == "editar" and
							($_GET['acao'] == "Reagendar" or
								$_GET['acao'] == "Alterar previsão")
						) { ?>
							<div style="padding-bottom: 10px;">
								<div class="label_solicitacao2">Data início:</div>
								<input name="data_inicio" type="text" id="data_inicio" style="width: 150px;" />
								<? if ($_GET['acao'] == "Reagendar") { ?>
									<br>
									<a href="#" id="ver_agenda" name="ver_agenda" style="color: #03C; font-weight: bold; padding-left: 5px;">Ver agenda</a>
								<? } ?>
							</div>
						<? } ?>
						<!-- fim - data_inicio -->


						<!-- agendamento_tempo/data_fim -->
						<? if (
							$_GET['situacao'] == "editar" and
							($_GET['acao'] == "Reagendar" or
								$_GET['acao'] == "Alterar previsão")
						) { ?>

							<? if ($_GET['acao'] == "Reagendar") { ?>
								<!-- agendamento_tempo -->
								<div style="padding-bottom: 10px;" id="div_agendamento_tempo">
									<div class="label_solicitacao2">Tempo*: </div>
									<select name="agendamento_tempo" id="agendamento_tempo" style="width: 175px;">
										<option value="">Escolha...</option>
										<option value="<? echo $mm = 15; ?>"><? echo sprintf("%02dh %02dm", floor($mm / 60), $mm % 60); ?></option>
										<option value="<? echo $mm = 30; ?>"><? echo sprintf("%02dh %02dm", floor($mm / 60), $mm % 60); ?></option>
										<option value="<? echo $mm = 45; ?>"><? echo sprintf("%02dh %02dm", floor($mm / 60), $mm % 60); ?></option>
										<option value="<? echo $mm = 60; ?>"><? echo sprintf("%02dh %02dm", floor($mm / 60), $mm % 60); ?></option>
										<option value="<? echo $mm = 120; ?>"><? echo sprintf("%02dh %02dm", floor($mm / 60), $mm % 60); ?></option>
										<option value="<? echo $mm = 180; ?>"><? echo sprintf("%02dh %02dm", floor($mm / 60), $mm % 60); ?></option>
										<option value="<? echo $mm = 240; ?>"><? echo sprintf("%02dh %02dm", floor($mm / 60), $mm % 60); ?></option>
										<option value="<? echo $mm = 300; ?>"><? echo sprintf("%02dh %02dm", floor($mm / 60), $mm % 60); ?></option>
									</select>
								</div>
								<!-- fim - agendamento_tempo -->
							<? } ?>

							<!-- data_fim -->
							<div style="padding-bottom: 10px;">
								<div class="label_solicitacao2">Data fim:</div>
								<input name="data_fim" type="text" id="data_fim" style="width: 150px;" />
							</div>
							<!-- fim - data_fim -->

						<? } ?>
						<!-- fim - agendamento_tempo/data_fim -->


						<!-- descricao_agendamento -->
						<? if (
							$_GET['situacao'] == "editar" and
							($_GET['acao'] == "Reagendar")
						) { ?>
							<div style="padding-bottom: 10px;" id="div_descricao_agendamento">
								<div class="label_solicitacao2">Descrição (agendamento):</div>

								<textarea name="descricao_agendamento" id="descricao_agendamento" style="width: 760px; height: 60px" /><? if ($_GET['acao'] == "Reagendar") {
																																			echo $row_agenda['descricao'];
																																		} ?></textarea>

							</div>
						<? } ?>
						<!-- fim - descricao_agendamento -->


						<!-- data_suporte_fim -->
						<? if (
							$_GET['situacao'] == "editar" and
							($_GET['acao'] == "Encerrar" or
								$_GET['acao'] == "Cancelar" or
								$_GET['acao'] == "Encaminhar para solicitação") and

							($tipo_suporte_inloco == "cs" or $tipo_suporte_inloco == "cn" or $tipo_suporte_inloco == "p")
						) { ?>
							<div style="padding-bottom: 10px;">
								<div class="label_solicitacao2">Data término:</div>
								<input name="data_suporte_fim" type="text" id="data_suporte_fim" style="width: 150px;" />
							</div>
						<? } ?>
						<!-- fim - data_suporte_fim -->


						<!-- parecer -->
						<? if (
							$_GET['situacao'] == "editar" and
							($_GET['acao'] == "Encerrar" or
								$_GET['acao'] == "Cancelar" or
								$_GET['acao'] == "Encaminhar para solicitação") and

							($tipo_suporte_inloco == "cs" or $tipo_suporte_inloco == "cn" or $tipo_suporte_inloco == "p")
						) { ?>
							<div style="padding-bottom: 10px;">
								<?
								// suporte_tipo_parecer
								mysql_select_db($database_conexao, $conexao);
								$query_tipo = "SELECT * FROM suporte_tipo_parecer ORDER BY IdTipoParecer ASC";
								$tipo = mysql_query($query_tipo, $conexao) or die(mysql_error());
								$row_tipo = mysql_fetch_assoc($tipo);
								$totalRows_tipo = mysql_num_rows($tipo);
								// fim - suporte_tipo_parecer
								?>
								<div class="label_solicitacao2">Parecer:</div>
								<select name="suporte_tipo_parecer" id="suporte_tipo_parecer">
									<?php
									do {
									?>
										<option value="<?php echo $row_tipo['descricao'] ?>"><?php echo $row_tipo['descricao'] ?></option>
									<?php
									} while ($row_tipo = mysql_fetch_assoc($tipo));
									$rows = mysql_num_rows($tipo);
									if ($rows > 0) {
										mysql_data_seek($tipo, 0);
										$row_tipo = mysql_fetch_assoc($tipo);
									}
									?>
								</select>
								<? mysql_free_result($tipo); ?>
							</div>
						<? } ?>
						<!-- fim - parecer -->


						<!-- confirmacao_recebimento -->
						<? if (
							$row_suporte['cobranca'] == "s" and $_GET['situacao'] == "editar" and
							($_GET['acao'] == "Encerrar")
						) { ?>
							<div style="padding-bottom: 10px;">
								<div class="label_solicitacao2">Confirma recebimento no auxílio cobrança?</div>
								<fieldset id="confirmacao_recebimento" style="border: 1px solid #CCCCCC; padding: 5px;">
									<input name="confirmacao_recebimento" id="confirmacao_recebimento_sim" type="radio" value="s" />
									<label for="confirmacao_recebimento_sim">sim</label>
									<input name="confirmacao_recebimento" id="confirmacao_recebimento_nao" type="radio" value="n" />
									<label for="confirmacao_recebimento_não">não</label>
								</fieldset>
								<div id="radio_teste"></div>
							</div>

							<!-- comprovante_pagamento -->
							<div style="padding-bottom: 10px;" id="caixa_comprovante_pagamento">
								<div class="label_solicitacao2">Comprovante de Pagamento: </div>
								<input type="hidden" id="comprovante_pagamento" name="comprovante_pagamento" />
								<iframe src="suporte_editar_upload.php?id_suporte=<?php echo $row_suporte['id']; ?>&voltar=s&situacao=&acao=Arquivos em anexo&resposta=&editar_tabela=s" width="760" height="200" frameborder="0" style="border: 1px solid #CCC; margin-top: 5px; margin-bottom: 5px;"></iframe>
							</div>
							<!-- comprovante_pagamento -->

							<div style="padding-bottom: 10px;">
								<div class="label_solicitacao2">Justificativa: </div>
								<textarea name="cobranca_recebimento_justificativa" id="cobranca_recebimento_justificativa" style="width:760px; height: 50px;" /><? echo $row_suporte['cobranca_recebimento_justificativa']; ?></textarea>
							</div>

							<div style="padding-bottom: 10px;">
								<div class="label_solicitacao2">Documento vinculado (cobrança): </div>
								<input type="text" name="cobranca_documento_vinculado" id="cobranca_documento_vinculado" style="width: 300px;" maxlength="50" value="<? echo $row_suporte['cobranca_documento_vinculado']; ?>" />
							</div>

						<? } ?>
						<!-- fim - confirmacao_recebimento -->


						<!-- suporte_tipo_recomendacao -->
						<? if (
							($_GET['situacao'] == "editar" and $_GET['acao'] == "Alterar recomendação")
						) { ?>
							<div style="padding-bottom: 10px;">
								<div class="label_solicitacao2">Recomendação*:</div>
								<?php
								mysql_select_db($database_conexao, $conexao);
								$query_suporte_tipo_recomendacao = "SELECT * FROM suporte_tipo_recomendacao ORDER BY IdTipoRecomendacao ASC";
								$suporte_tipo_recomendacao = mysql_query($query_suporte_tipo_recomendacao, $conexao) or die(mysql_error());
								$row_suporte_tipo_recomendacao = mysql_fetch_assoc($suporte_tipo_recomendacao);
								$totalRows_suporte_tipo_recomendacao = mysql_num_rows($suporte_tipo_recomendacao);
								?>
								<select name="suporte_tipo_recomendacao" id="suporte_tipo_recomendacao" style="width: 400px;">
									<option value="" <?php if (!(strcmp("", $row_suporte['recomendacao']))) {
															echo "selected=\"selected\"";
														} ?>>Selecione ...</option>
									<?php
									do {
									?>
										<option value="<?php echo $row_suporte_tipo_recomendacao['titulo'] ?>" <?php if (!(strcmp($row_suporte_tipo_recomendacao['titulo'], $row_suporte['recomendacao']))) {
																													echo "selected=\"selected\"";
																												} ?>>
											<?php echo $row_suporte_tipo_recomendacao['titulo'] ?>
										</option>
									<?php
									} while ($row_suporte_tipo_recomendacao = mysql_fetch_assoc($suporte_tipo_recomendacao));
									$rows = mysql_num_rows($suporte_tipo_recomendacao);
									if ($rows > 0) {
										mysql_data_seek($suporte_tipo_recomendacao, 0);
										$row_suporte_tipo_recomendacao = mysql_fetch_assoc($suporte_tipo_recomendacao);
									}
									?>
								</select>
								<? mysql_free_result($suporte_tipo_recomendacao); ?>
							</div>
						<? } ?>
						<!-- fim - suporte_tipo_recomendacao -->


						<!-- in-loco -->
						<? if (
							$_GET['situacao'] == "editar" and
							($_GET['acao'] == "Encaminhar para atendimento in-loco")
						) { ?>
							<input name="inloco" type="hidden" value="s">
						<? } ?>
						<!-- fim - in-loco -->


						<!-- tipo_formulario -->
						<? if (
							$_GET['situacao'] == "editar" and
							($_GET['acao'] == "Gerar formulário de visita")
						) { ?>
							<input name="tipo_formulario" type="hidden" value="<? echo $_GET['resposta']; ?>">
						<? } ?>
						<!-- fim - tipo_formulario -->


						<!-- confirmacao_encerrar_cancelar -->
						<? if (
							$row_suporte['tipo_suporte'] == "p" and
							(
								($_GET['situacao'] == "editar" and $_GET['acao'] == "Encerrar") or
								($_GET['situacao'] == "editar" and $_GET['acao'] == "Cancelar"))
						) { ?>
							<style type="text/css">
								.block {
									display: block;
								}

								form.cmxform label.error {
									display: none;
								}
							</style>
							<div style="padding-bottom: 10px;">
								Solicitar validação do parceiro?<br>
								<fieldset style="border: 1px solid #CCCCCC; padding: 5px;">
									<input name="confirmacao_encerrar_cancelar" id="confirmacao_encerrar_cancelar" type="radio" value="sim" validate="required:true" checked="checked"> sim
									<input name="confirmacao_encerrar_cancelar" id="confirmacao_encerrar_cancelar" type="radio" value="nao" validate="required:true" disabled="disabled"> não
								</fieldset>
								<label for="confirmacao_encerrar_cancelar" class="error">Selecione uma das opções</label>
							</div>
						<? } else if (
							($row_suporte['tipo_suporte'] == "c" and $row_suporte['inloco'] == "s") and
							($row_suporte['cobranca'] == "s" or $row_suporte['tipo_formulario'] == "Extra" or ($row_suporte['tipo_formulario'] == "Treinamento" and $row_suporte['creditar'] == "n")) and
							($_GET['situacao'] == "editar" and $_GET['acao'] == "Encerrar")
						) { ?>
							<input type="hidden" name="confirmacao_encerrar_cancelar" id="confirmacao_encerrar_cancelar" value="sim">
						<? } else { ?>
							<input type="hidden" name="confirmacao_encerrar_cancelar" id="confirmacao_encerrar_cancelar" value="nao">
						<? } ?>
						<!-- fim - confirmacao_encerrar_cancelar -->

						<!-- Avaliar atendimento -->
						<?
						if (
							$_GET['situacao'] == "editar" and
							$row_suporte['tipo_suporte'] == "p" and
							$row_suporte['id_usuario_envolvido'] == $row_usuario['IdUsuario'] and
							(
								($row_suporte['situacao'] == "em validação" and $_GET['acao'] == "Concluir validação") or
								($row_suporte['solicita_solicitacao'] == "s" and $_GET['acao'] == "Fechar suporte sem gerar solicitação"))
						) { ?>
							<style type="text/css">
								.block {
									display: block;
								}

								form.cmxform label.error {
									display: none;
								}
							</style>
							<div style="padding-bottom: 10px;">
								<div class="label_solicitacao2">Por favor, avalie esse atendimento:</div>
								<fieldset style="border: 1px solid #CCCCCC; padding: 5px;">
									<input name="avaliacao_atendimento" id="avaliacao_atendimento" type="radio" value="Excelente" validate="required:true" <? if ($row_suporte['avaliacao_atendimento'] == "Excelente") {
																																								echo "checked";
																																							} ?>> Excelente
									<input name="avaliacao_atendimento" id="avaliacao_atendimento" type="radio" value="Bom" validate="required:true" <? if ($row_suporte['avaliacao_atendimento'] == "Bom") {
																																							echo "checked";
																																						} ?>> Bom
									<input name="avaliacao_atendimento" id="avaliacao_atendimento" type="radio" value="Regular" validate="required:true" <? if ($row_suporte['avaliacao_atendimento'] == "Regular") {
																																								echo "checked";
																																							} ?>> Regular
									<input name="avaliacao_atendimento" id="avaliacao_atendimento" type="radio" value="Ruim" validate="required:true" <? if ($row_suporte['avaliacao_atendimento'] == "Ruim") {
																																							echo "checked";
																																						} ?>> Ruim
									<input name="avaliacao_atendimento" id="avaliacao_atendimento" type="radio" value="Péssimo" validate="required:true" <? if ($row_suporte['avaliacao_atendimento'] == "Péssimo") {
																																								echo "checked";
																																							} ?>> Péssimo
								</fieldset>
								<label for="avaliacao_atendimento" class="error">Selecione uma das opções</label>
							</div>

							<div style="padding-bottom: 10px;">
								<div class="label_solicitacao2">Solucionado: </div>
									<select name="solucionado" id="solucionado" style="width: 175px;">
										<option value="">...</option>
										<option value="s">Sim</option>
										<option value="n">Não</option>
									</select>
								</div>
							</div>

							<div style="padding-bottom: 10px;" id="div_solucionado_nao">
								<div class="label_solicitacao2">Justificativa para o encerramento sem solução do problema:</div>
									<textarea name="solucionado_nao" id="solucionado_nao" style="width: 760px; height: 50px" /></textarea>
								</div>
							</div>

						<? } ?>
						<!-- fim - Avaliar atendimento -->


						<!-- contato_responsavel -->
						<? if (
							($_GET['situacao'] == "editar" and
								$_GET["acao"] == "Contato")
						) { ?>
							<div style="padding-bottom: 10px;">
								<div class="label_solicitacao2">Contato*:</div>
								<input type="text" name="contato_responsavel" id="contato_responsavel" style="width:290px" />
							</div>
						<? } ?>
						<!-- fim - contato_responsavel -->


						<!-- contato_telefone -->
						<? if (
							($_GET['situacao'] == "editar" and
								$_GET["acao"] == "Contato")
						) { ?>
							<div style="padding-bottom: 10px;">
								<div class="label_solicitacao2">Telefone*:</div>
								<input type="text" name="contato_telefone" id="contato_telefone" style="width:150px" maxlength="13" />
							</div>
						<? } ?>
						<!-- fim - contato_telefone -->


						<!-- versao -->
						<? if (
							($_GET['situacao'] == "editar" and $_GET['acao'] == "Solicitar suporte") and
							$row_suporte['suporte_tipo_atendimento_solicita_suporte_versao'] == 1
						) { ?>
							<div style="padding-top: 5px; padding-bottom: 15px;">
								<?
								mysql_select_db($database_conexao, $conexao);
								$query_versao = "SELECT * FROM geral_tipo_versao WHERE status = 1 ORDER BY titulo ASC";
								$versao = mysql_query($query_versao, $conexao) or die(mysql_error());
								$row_versao = mysql_fetch_assoc($versao);
								$totalRows_versao = mysql_num_rows($versao);
								?>
								Versão:
								<fieldset>
									<?php do { ?>

										<input name="versao[]" id="versao" type="checkbox" class="checkbox" value="<? echo $row_versao['IdTipoVersao']; ?>" <? if (in_array($row_versao['IdTipoVersao'], explode(',', $row_suporte['versao']))) {  ?>checked="checked" <? } ?> />
										<? echo $row_versao['titulo']; ?><br>

									<?php } while ($row_versao = mysql_fetch_assoc($versao)); ?>
								</fieldset>

								<label for="versao[]" class="error">Selecione pelo menos uma das versões acima</label>
								<? mysql_free_result($versao); ?>
							</div>
						<? } ?>
						<!-- fim - versao -->

						<? if (
							($_GET['situacao'] == "editar" and $_GET['acao'] == "Solicitar suporte")
						) { ?>

							<div style="padding-bottom: 10px;" id="div_anomalia_simulada">
								<div class="label_solicitacao2">Anomalia simulada em testes: </div>
									<select name="anomalia_simulada" id="anomalia_simulada" style="width: 175px;">
										<option value="s">Sim</option>
										<option value="n">Não</option>
										<option value="a">Não se aplica</option>
									</select>
								</div>
							</div>

							<div style="padding-bottom: 10px;" id="div_anomalia_simulada_nao">
								<div class="label_solicitacao2">Orientações a ser verificadas:</div>
								<? echo $row_parametros['suporte_solicita_orientacoes']; ?>
								</div>
							</div>

							<div style="padding-bottom: 10px;" id="div_anomalia_simulada_afirmacao">
								<div class="label_solicitacao2">Afirmo que foram seguidas todas as orientações: </div>
									<select name="anomalia_simulada_afirmacao" id="anomalia_simulada_afirmacao" style="width: 175px;">
										<option value="">...</option>
										<option value="s">Sim</option>
										<option value="n">Não</option>
									</select>
								</div>
							</div>

						<? } ?>

						<!-- atendimento_status -->
						<? if (
							$_GET["acao"] == "Finalizar atendimento"
						) { ?>
							<div style="padding-bottom: 10px;">
								<div class="label_solicitacao2">Status do atendimento:</div>
								<select name="atendimento_status" id="atendimento_status" style="width: 175px;">
									<option value="">...</option>
									<option value="FinAte">Concluído</option>
									<option value="PenAte">Pendente</option>
								</select>
							</div>
						<? } ?>
						<!-- fim - atendimento_status -->

						<!-- atendimento_cliente -->
						<? if (
							$_GET["acao"] == "Iniciar atendimento" or 
							$_GET["acao"] == "Finalizar atendimento" or 
							$_GET["acao"] == "Cancelar atendimento" or 
							$_GET["acao"] == "Reagendar atendimento"
						) { ?>
							<div style="padding-bottom: 10px;">
								<div class="label_solicitacao2">Pessoa/Cliente:</div>
								<input type="text" name="atendimento_cliente" id="atendimento_cliente" style="width:400px" maxlength="180" />
							</div>
						<? } ?>
						<!-- fim - atendimento_cliente -->

						<!-- atendimento_local -->
						<? if (
							$_GET["acao"] == "Cancelar atendimento"
						) { ?>
							<div style="padding-bottom: 10px;">
								<div class="label_solicitacao2">Local do cancelamento:</div>
								<select name="atendimento_local" id="atendimento_local" style="width: 175px;">
									<option value="">...</option>
									<option value="c">Ao chegar ao cliente</option>
									<option value="a">Antecipado</option>
									<option value="d">Durante o atendimento</option>
								</select>
							</div>
						<? } ?>
						<!-- fim - atendimento_local -->

						<!-- atendimento_previsao -->
						<? if (
							$_GET["acao"] == "Reagendar atendimento"
						) { ?>
							<div style="padding-bottom: 10px;">
								<div class="label_solicitacao2">Previsão de nova data e horário:</div>
								<input name="atendimento_previsao" type="text" id="atendimento_previsao" style="width: 150px;" />
							</div>
						<? } ?>
						<!-- fim - atendimento_previsao -->

						<!-- atendimento_texto -->
						<? if (
							$_GET["acao"] == "Cancelar atendimento" or 
							$_GET["acao"] == "Reagendar atendimento" or 
							$_GET["acao"] == "Finalizar atendimento"
						) { ?>
							<div style="padding-bottom: 10px;">
								<div class="label_solicitacao2">
								<? if($_GET["acao"] == "Cancelar atendimento" or $_GET["acao"] == "Reagendar atendimento"){ ?>
									Motivo do evento
								<? } else if($_GET["acao"] == "Finalizar atendimento"){ ?>
									Detalhes do atendimento
								<? } ?>:
								</div>
								<textarea name="atendimento_texto" id="atendimento_texto" style="width: 760px; height: 90px" /></textarea>
							</div>
						<? } ?>
						<!-- fim - atendimento_texto -->

						<!-- tempo gasto -->
						<? if (
							($_GET['acao'] == "Encaminhar") or
							($_GET['situacao'] == "editar" and $_GET['acao'] == "Encerrar") or
							($_GET['situacao'] == "editar" and $_GET['acao'] == "Cancelar") or
							($_GET['situacao'] == "editar" and $_GET["acao"] == "Fechar suporte sem gerar solicitação" and $tipo_suporte_inloco == "p") or
							($_GET['situacao'] == "editar" and $_GET["acao"] == "Concluir execução" and $tipo_suporte_inloco == "r") or
							($_GET['situacao'] == "editar" and $_GET["acao"] == "Concluir validação" and $tipo_suporte_inloco == "p") or
							($_GET['situacao'] == "editar" and $_GET["acao"] == "Encaminhar para solicitação" and $tipo_suporte_inloco == "p")
						) { ?>
							<div style="padding-bottom: 10px;">
								Tempo gasto (dias - horas - minutos):<br>
								<input name="tempo_gasto" type="text" id="tempo_gasto" size="30" />
							</div>
						<? } ?>
						<!-- fim - tempo gasto -->

						<!-- Observação -->
						<div style="padding-bottom: 10px;">
							<div class="label_solicitacao2">Observações:</div>

							<? if (
								($_GET['situacao'] == "editar" and $_GET['acao'] == "Alterar observação")
							) { ?>
								<textarea name="observacao" id="observacao" style="width: 760px; height: 90px" /><? echo str_replace("<br />", "", $row_suporte['observacao']); ?></textarea>
							<? } else { ?>
								<textarea name="observacao" id="observacao" style="width: 760px; height: 90px" /></textarea>
							<? } ?>
						</div>
						<!-- fim - Observação -->


						<!-- Botões -->
						<div>
							<input type="hidden" id="id_solicitacao" name="id_solicitacao" value="<?php echo $row_suporte['id']; ?>" />
							<input type="hidden" id="MM_update" name="MM_update" value="form" />
							<input type="button" name="button" id="button" value="Salvar" class="botao_geral2" style="width: 70px" />

							<!-- Registrar reclamação ========================================================================================================================================= -->
							<? if (
								($row_suporte['status_flag'] != "f" and ($tipo_suporte_inloco == "cs" or $tipo_suporte_inloco == "cn" or $tipo_suporte_inloco == "p"))
							) { ?>
								<a href="suporte_gerar.php?tipo_suporte=r&inloco=n&cobranca=n&cliente=<? echo $row_suporte['codigo_empresa']; ?>6&contrato=<? echo $row_suporte['contrato']; ?>&reclamacao_suporte=<? echo $row_suporte['id']; ?>" target="_blank" id="botao_geral2">Registrar reclamação</a>

							<? } ?>
							<!-- fim - Registrar reclamação =================================================================================================================================== -->

						</div>
						<!-- fim - Botões -->

					</form>
				</td>
			</tr>
		</table>
	</div>

</body>

</html>
<?php
mysql_free_result($suporte);
mysql_free_result($suporte_contato);
mysql_free_result($usuario);
mysql_free_result($agenda);
mysql_free_result($agenda_agendado);
?>