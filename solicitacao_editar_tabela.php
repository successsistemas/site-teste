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

// solicitacao
$colname_solicitacao = "-1";
if (isset($_GET['id_solicitacao'])) {
	$colname_solicitacao = $_GET['id_solicitacao'];
}
mysql_select_db($database_conexao, $conexao);
$query_solicitacao = sprintf("
SELECT 
	solicitacao.*, 
	(SELECT nome FROM usuarios WHERE usuarios.IdUsuario = solicitacao.id_usuario_responsavel) as usuario_responsavel, 
	(SELECT nome FROM usuarios WHERE usuarios.IdUsuario = solicitacao.id_operador) as nome_operador, 
	(SELECT nome FROM usuarios WHERE usuarios.IdUsuario = solicitacao.id_executante) as nome_executante, 
	(SELECT nome FROM usuarios WHERE usuarios.IdUsuario = solicitacao.id_analista_orcamento) as nome_analista_orcamento, 	
	(SELECT nome FROM usuarios WHERE usuarios.IdUsuario = solicitacao.id_testador) as nome_testador
FROM 
	solicitacao 
WHERE 
	id = %s",
GetSQLValueString($colname_solicitacao, "int"));
$solicitacao = mysql_query($query_solicitacao, $conexao) or die(mysql_error());
$row_solicitacao = mysql_fetch_assoc($solicitacao);
$totalRows_solicitacao = mysql_num_rows($solicitacao);
// fim - solicitacao

// acessso --------------------------------------------------------------------------------------------------------------------------------------------------------------
$acesso = 0;
if (
	($row_solicitacao['situacao'] != "solucionada" and $row_solicitacao['situacao'] != "reprovada") and

	(	$row_usuario['controle_solicitacao'] == "Y" or
		($row_usuario['controle_solicitacao'] == "Y" and $row_solicitacao['situacao'] == "criada") or
		($row_usuario['IdUsuario'] == $row_solicitacao['id_usuario_responsavel']) or
		($row_usuario['controle_solicitacao'] == "Y" and $row_usuario['IdUsuario'] == $row_solicitacao['id_operador']) or
		($row_usuario['solicitacao_executante'] == "Y" and $row_usuario['IdUsuario'] == $row_solicitacao['id_executante']) or
		($row_usuario['solicitacao_executante'] == "Y" and $row_usuario['IdUsuario'] == $row_solicitacao['id_analista_orcamento']) or
		($row_usuario['solicitacao_testador'] == "Y" and $row_usuario['IdUsuario'] == $row_solicitacao['id_testador']) or
		($row_usuario['controle_praca'] == "Y" and $row_usuario['praca'] = $row_solicitacao['praca']) or 
		($_GET['acao'] == "Questionar")
	)
) {

	$acesso = 1; // autorizado

	// editar
	if ($_GET['situacao'] == "editar") {

		if (
			$row_usuario['controle_solicitacao'] == "Y" or
			$row_usuario['IdUsuario'] == $row_solicitacao['id_usuario_responsavel'] or
			$row_usuario['IdUsuario'] == $row_solicitacao['id_operador'] or 
			($row_usuario['controle_praca'] == "Y" and $row_solicitacao['praca'] == $row_usuario['praca'])
		) {
			$acesso = 1; // autorizado
		} else {
			$acesso = 0; // não autorizado
		}
	}
	// fim - editar

} else {

	$acesso = 0; // não autorizado

}

if ($acesso == 0) {
	//echo "Acesso não autorizado !";
	echo "<script>window.top.location = 'solicitacao.php?padrao=sim&" . $situacao_padrao . "';</script>";
	exit;
}
// fim - acesso ---------------------------------------------------------------------------------------------------------------------------------------------------------

//region - Acesso ***************************************************************************************************************

// Alterar solicitante
if (($_GET['situacao'] == "editar") and $_GET["acao"] == "Alterar solicitante") {

	if (
		$row_usuario['controle_solicitacao'] == 'Y' or 
		$row_solicitacao['id_operador'] == $row_usuario['IdUsuario'] or 
		($row_usuario['controle_praca'] == "Y" and $row_solicitacao['praca'] == $row_usuario['praca'])
	) { 

	} else {

		header("Location: painel/index.php");
		exit;

	}

}
//end - Alterar solicitante

// Alterar operador
if (($_GET['situacao'] == "editar") and $_GET["acao"] == "Alterar operador") {

	if (
		$row_usuario['controle_solicitacao'] == 'Y' or 
		$row_solicitacao['id_operador'] == $row_usuario['IdUsuario']
	) { 

	} else {

		header("Location: painel/index.php");
		exit;

	}

}
// end - Alterar operador

// Alterar analista de orçamento
if (($_GET['situacao'] == "editar") and $_GET["acao"] == "Alterar analista de orçamento") {

	if (
		$row_usuario['controle_solicitacao'] == 'Y' or 
		$row_solicitacao['id_operador'] == $row_usuario['IdUsuario']
	) { 

	} else {

		header("Location: painel/index.php");
		exit;

	}

}
// end - Alterar analista de orçamento

// Alterar executante
if (($_GET['situacao'] == "editar") and $_GET["acao"] == "Alterar executante") {

	if (
		$row_usuario['controle_solicitacao'] == 'Y' or 
		$row_solicitacao['id_operador'] == $row_usuario['IdUsuario']
	) { 

	} else {

		header("Location: painel/index.php");
		exit;

	}

}
// end - Alterar executante

//endregion - end - Acesso **********************************************************************************************************

// converter entrada de data em portugues para ingles
if (isset($_POST['data'])) {
	$data_data = substr($_POST['data'], 0, 10);
	$data_hora = substr($_POST['data'], 10, 9);
	$_POST['data'] = implode("-", array_reverse(explode("-", $data_data))) . $data_hora;
}
if (isset($_POST['data_inicio'])) {
	$data_data = substr($_POST['data_inicio'], 0, 10);
	$data_hora = substr($_POST['data_inicio'], 10, 9);
	$_POST['data_inicio'] = implode("-", array_reverse(explode("-", $data_data))) . $data_hora;
}
if (isset($_POST['data_executavel'])) {
	$data_data = substr($_POST['data_executavel'], 0, 10);
	$data_hora = substr($_POST['data_executavel'], 10, 9);
	$_POST['data_executavel'] = implode("-", array_reverse(explode("-", $data_data))) . $data_hora;
}
// fim - converter entrada de data em portugues para ingles - fim

// fim - converter entrada de orçamento - fim
if (isset($_POST['orcamento'])) {
	$_POST['orcamento'] = str_replace(',', '.', $_POST['orcamento']);
}
// fim - converter entrada de orçamento - fim

if (((isset($_POST["MM_update"])) and ($_POST["MM_update"] == "form")) or ((isset($_GET["MM_update"])) and ($_GET["MM_update"] == "form"))) {

	require_once('funcao_formata_data.php');
	require_once('solicitacao_funcao_update.php');
	require_once('solicitacao_funcao_tempo_gasto.php');
	require_once('solicitacao_funcao_devolucao.php');
	require_once('emails.php');

	// funcao - redireciona
	function funcao_solicitacao_redireciona()
	{

		$updateGoTo = "solicitacao_editar.php?id_solicitacao=" . $_GET['id_solicitacao'];
		echo sprintf("<meta http-equiv=\"refresh\" content=\"0; url=%s\">", $updateGoTo);
	}
	// fim - funcao - redireciona

	// limpando o array
	$dados_solicitacao = array();
	$dados_solicitacao_descricao = array();
	// fim - limpando o array

	// interacao **********************************************************************************************************
	$interacao = funcao_solicitacao_interacao($row_solicitacao['id'], @$_GET['interacao']);
	if ($interacao == 1 and @$_GET['interacao'] <> NULL) {
		echo "<script>alert('Foi realizada alguma interação anterior a esta, assim, a ação atual não será gravada. Realize uma nova ação após a atualização da página.');</script>";
		$redirGoTo = "solicitacao_editar.php?id_solicitacao=" . $_GET['id_solicitacao'];
		echo sprintf("<meta http-equiv=\"refresh\" content=\"0; url=%s\">", $redirGoTo);
		exit;
	}
	// fim - interacao ****************************************************************************************************

	//---------------------------------------------------------------------------------------------------------------------------------------------------------------
	// RECEBIDA -----------------------------------------------------------------------------------------------------------------------------------------------------
	//---------------------------------------------------------------------------------------------------------------------------------------------------------------
	// Colocar em análise
	if ((($_GET['situacao'] == "recebida" or $_GET['situacao'] == "aprovada") and $_GET['acao'] == "Colocar em análise") and $_GET["resposta"] == "") {

		$dados_solicitacao = array(
			"interacao" => $row_solicitacao['interacao'] + 1,
			"situacao" => "em análise",
			"status" => "pendente operador",

			"previsao_analise_inicio" => $_POST['data_inicio'],
			"previsao_analise" => $_POST['data'],

			"dt_aprovacao_reprovacao" => "",
			"id_usuario_aprovacao_reprovacao" => "",
			"observacao_aprovacao_reprovacao" => "",

			"previsao_retorno_orcamento_inicio" => "0000-00-00 00:00:00",
			"previsao_retorno_orcamento" => "0000-00-00 00:00:00",

			"previsao_analise_orcamento_inicio" => "0000-00-00 00:00:00",
			"previsao_analise_orcamento" => "0000-00-00 00:00:00",
			"id_analista_orcamento" => "",
			"dt_orcamento" => "",
			"orcamento" => "",
			"prazo_desenvolvimento_orcamento" => "",
			"orcamento_os" => "",

			"previsao_solucao_inicio" => "0000-00-00 00:00:00",
			"previsao_solucao" => "0000-00-00 00:00:00",
			"id_executante" => "",
			"dt_conclusao" => "",

			"previsao_testes_inicio" => "0000-00-00 00:00:00",
			"previsao_testes" => "0000-00-00 00:00:00",
			"id_testador" => "",
			"dt_conclusao_testes" => "",
			"observacao_testes" => "",

			"previsao_validacao_inicio" => "0000-00-00 00:00:00",
			"previsao_validacao" => "0000-00-00 00:00:00",
			"dt_validacao" => "",
			"observacao_validacao" => "",

			"previsao_geral_inicio" => $_POST['data_inicio'],
			"previsao_geral" => $_POST['data'],

			"analista_orcamento_leu" => "",
			"executante_leu" => "",
			"testador_leu" => "",

			"dt_final" => "",
			"observacao_final" => "",

			"previsao_proposta_inicio" => "",
			"previsao_proposta" => "",
			"previsao_proposta_ja_alterada" => "",

			"alterar_previsao_solicitante" => 0,
			"alterar_previsao_operador" => 0,
			"alterar_previsao_executante" => 0,
			"alterar_previsao_testador" => 0,

			"encaminhamento_data_inicio" => "",
			"encaminhamento_data" => "",
			"id_encaminhamento" => "",

			"status_devolucao" => "",
			"devolucao_id_usuario" => "",
			"devolucao_motivo" => "",
			"devolucao_data" => "",
			"status_recusa" => ""
		);
		$dados_solicitacao_descricao = array(
			"id_solicitacao" => $row_solicitacao['id'],
			"id_usuario_responsavel" => $row_usuario['IdUsuario'],
			"descricao" => "Previsão de análise: " . formataDataPTG($_POST['data_inicio']) . " à " . formataDataPTG($_POST['data']) . "<br>" . $_POST['observacao'],
			"data" => date("Y-m-d H:i:s"),
			"tipo_postagem" => "Colocada em análise"
		);

		funcao_solicitacao_update($row_solicitacao['id'], $dados_solicitacao, $dados_solicitacao_descricao);
		email_solicitacao($row_solicitacao['id'], $dados_solicitacao_descricao['tipo_postagem'], $dados_solicitacao_descricao['descricao']);
		$dados_solicitacao = array("interacao" => $row_solicitacao['interacao'] + 1,);
		$dados_solicitacao_descricao = array();
		funcao_solicitacao_redireciona();
		exit;
	}
	// fim - Colocar em análise
	//---------------------------------------------------------------------------------------------------------------------------------------------------------------



	//---------------------------------------------------------------------------------------------------------------------------------------------------------------
	// EM ORÇAMENTO -------------------------------------------------------------------------------------------------------------------------------------------------
	//---------------------------------------------------------------------------------------------------------------------------------------------------------------
	// Aprovar orçamento (sol)
	if (($_GET['situacao'] == "em orçamento" and $_GET['acao'] == "Aprovar") and $_GET["resposta"] == "") {

		$dados_solicitacao = array(
			"interacao" => $row_solicitacao['interacao'] + 1,
			"situacao" => "aprovada",
			"status" => "pendente operador",

			"dt_aprovacao_reprovacao" => date("Y-m-d H:i:s"),
			"id_usuario_aprovacao_reprovacao" => $row_usuario['IdUsuario'],
			"observacao_aprovacao_reprovacao" => $_POST['observacao'],

			"previsao_geral_inicio" => date("Y-m-d H:i:s"),
			"previsao_geral" => date("Y-m-d H:i:s"),

			"previsao_proposta_inicio" => "",
			"previsao_proposta" => "",
			"previsao_proposta_ja_alterada" => "n",

			"alterar_previsao_solicitante" => 0,
			"alterar_previsao_operador" => 0,
			"alterar_previsao_executante" => 0,
			"alterar_previsao_testador" => 0,

			"id_encaminhamento" => "",
			"encaminhamento_data_inicio" => "",
			"encaminhamento_data" => "",

			"status_devolucao" => "",
			"devolucao_id_usuario" => "",
			"devolucao_motivo" => "",
			"devolucao_data" => "",
			"status_recusa" => ""
		);
		$dados_solicitacao_descricao = array(
			"id_solicitacao" => $row_solicitacao['id'],
			"id_usuario_responsavel" => $row_usuario['IdUsuario'],
			"descricao" => $_POST['observacao'],
			"data" => date("Y-m-d H:i:s"),
			"tipo_postagem" => "Orçamento aceito"
		);

		funcao_solicitacao_update($row_solicitacao['id'], $dados_solicitacao, $dados_solicitacao_descricao);
		email_solicitacao($row_solicitacao['id'], $dados_solicitacao_descricao['tipo_postagem'], $dados_solicitacao_descricao['descricao']);
		$dados_solicitacao = array("interacao" => $row_solicitacao['interacao'] + 1,);
		$dados_solicitacao_descricao = array();
		funcao_solicitacao_redireciona();
		exit;
	}
	// fim - Aprovar orçamento (sol)
	//---------------------------------------------------------------------------------------------------------------------------------------------------------------
	// Reprovar orçamento (sol)
	if (($_GET['situacao'] == "em orçamento" and $_GET['acao'] == "Reprovar") and $_GET["resposta"] == "") {

		$sugestao = $_POST['sugestao'];

		// sim
		if ($_POST['sugestao'] == "sim") {

			$dados_solicitacao = array(
				"interacao" => $row_solicitacao['interacao'] + 1,
				"situacao" => "solucionada",
				"status" => "",
				"tipo" => "Sugestão",

				"dt_aprovacao_reprovacao" => date("Y-m-d H:i:s"),
				"id_usuario_aprovacao_reprovacao" => $row_usuario['IdUsuario'],
				"observacao_aprovacao_reprovacao" => $_POST['observacao'],

				"previsao_geral_inicio" => "",
				"previsao_geral" => "",

				"previsao_proposta_inicio" => "",
				"previsao_proposta" => "",

				"status_devolucao" => "",
				"devolucao_id_usuario" => "",
				"devolucao_motivo" => "",
				"devolucao_data" => "",
				"status_recusa" => "",

				"auto_email_status" => "",
				"auto_email_data" => "",
				"auto_email_solicitacao_descricao" => ""
			);
			$dados_solicitacao_descricao = array(
				"id_solicitacao" => $row_solicitacao['id'],
				"id_usuario_responsavel" => $row_usuario['IdUsuario'],
				"descricao" => $_POST['observacao'],
				"data" => date("Y-m-d H:i:s"),
				"tipo_postagem" => "Reprovação de orçamento"
			);
		}
		// fim - sim

		// nao
		if ($_POST['sugestao'] == "nao") {

			$dados_solicitacao = array(
				"interacao" => $row_solicitacao['interacao'] + 1,
				"situacao" => "reprovada",
				"status" => "",

				"observacao_aprovacao_reprovacao" => $_POST['observacao'],
				"dt_aprovacao_reprovacao" => date("Y-m-d H:i:s"),
				"id_usuario_aprovacao_reprovacao" => $row_usuario['IdUsuario'],

				"previsao_geral_inicio" => "",
				"previsao_geral" => "",

				"previsao_proposta_inicio" => "",
				"previsao_proposta" => "",

				"status_devolucao" => "",
				"devolucao_id_usuario" => "",
				"devolucao_motivo" => "",
				"devolucao_data" => "",
				"status_recusa" => "",

				"auto_email_status" => "",
				"auto_email_data" => "",
				"auto_email_solicitacao_descricao" => ""
			);
			$dados_solicitacao_descricao = array(
				"id_solicitacao" => $row_solicitacao['id'],
				"id_usuario_responsavel" => $row_usuario['IdUsuario'],
				"descricao" => $_POST['observacao'],
				"data" => date("Y-m-d H:i:s"),
				"tipo_postagem" => "Reprovação de orçamento"
			);
		}
		// fim - nao

		funcao_solicitacao_update($row_solicitacao['id'], $dados_solicitacao, $dados_solicitacao_descricao);
		email_solicitacao($row_solicitacao['id'], $dados_solicitacao_descricao['tipo_postagem'], $dados_solicitacao_descricao['descricao']);
		$dados_solicitacao = array("interacao" => $row_solicitacao['interacao'] + 1,);
		$dados_solicitacao_descricao = array();
		funcao_solicitacao_redireciona();
		exit;
	}
	// Reprovar orçamento (sol)
	//---------------------------------------------------------------------------------------------------------------------------------------------------------------


	//---------------------------------------------------------------------------------------------------------------------------------------------------------------
	// EM TESTES ----------------------------------------------------------------------------------------------------------------------------------------------------
	//---------------------------------------------------------------------------------------------------------------------------------------------------------------
	// Concluir testes
	if (($_GET['situacao'] == "em testes" and $_GET['acao'] == "Concluir testes") and $_GET["resposta"] == "") {

		$versao_acao = "";
		foreach ($_POST as $versao_acao_campo => $versao_acao_valor) {
			if (strstr($versao_acao_campo, 'versao_acao') and $versao_acao_valor != "") {
				$versao_acao .= $versao_acao_valor . "<br>";
			}
		}

		$dados_solicitacao = array(
			"interacao" => $row_solicitacao['interacao'] + 1,
			"situacao" => "testada",
			"status" => "encaminhada para solicitante",

			"dt_conclusao_testes" => date("Y-m-d H:i:s"),
			"observacao_testes" => $_POST['observacao'],

			"previsao_validacao_inicio" => "0000-00-00 00:00:00",
			"previsao_validacao" => "0000-00-00 00:00:00",
			"dt_validacao" => "",
			"observacao_validacao" => "",

			"previsao_geral_inicio" => date("Y-m-d H:i:s"),
			"previsao_geral" => date("Y-m-d H:i:s"),

			"dt_final" => "",
			"observacao_final" => "",

			"previsao_proposta_inicio" => "",
			"previsao_proposta" => "",
			"previsao_proposta_ja_alterada" => "",

			"encaminhamento_data_inicio" => "",
			"encaminhamento_data" => "",
			"id_encaminhamento" => "",

			"acao" => "Concluir",

			"status_devolucao" => "",
			"devolucao_id_usuario" => "",
			"devolucao_motivo" => "",
			"devolucao_data" => "",
			"status_recusa" => ""
		);

		$dados_solicitacao_descricao = array(
			"id_solicitacao" => $row_solicitacao['id'],
			"id_usuario_responsavel" => $row_usuario['IdUsuario'],
			"descricao" => "Teste concluído - Entrega para solicitante validar<br>" . $_POST['observacao'] . "<br>Versão que foi realizada ação/tratamento: <br>" . $versao_acao,
			"data" => date("Y-m-d H:i:s"),
			"tipo_postagem" => "Conclusão de teste",
			"conclusao_teste" => 1
		);

		funcao_solicitacao_update($row_solicitacao['id'], $dados_solicitacao, $dados_solicitacao_descricao);
		tempo_gasto($row_solicitacao['id'], $row_solicitacao['situacao'], $_GET['acao'], $row_usuario['IdUsuario'], 'testador', $_POST['tempo_gasto']);
		email_solicitacao($row_solicitacao['id'], $dados_solicitacao_descricao['tipo_postagem'], $dados_solicitacao_descricao['descricao']);
		$dados_solicitacao = array("interacao" => $row_solicitacao['interacao'] + 1,);
		$dados_solicitacao_descricao = array();
		funcao_solicitacao_redireciona();
		exit;

	}
	// fim - Concluir testes
	//---------------------------------------------------------------------------------------------------------------------------------------------------------------


	//---------------------------------------------------------------------------------------------------------------------------------------------------------------
	// EM VALIDAÇÃO -------------------------------------------------------------------------------------------------------------------------------------------------
	//---------------------------------------------------------------------------------------------------------------------------------------------------------------
	// Concluir validação
	if (($_GET['situacao'] == "em validação" and $_GET['acao'] == "Concluir validação") and $_GET["resposta"] == "") {

		// se validação é de uma 'Conclusão'

		if ($row_solicitacao['acao'] == "Concluir") {

			$dados_solicitacao = array(
				"interacao" => $row_solicitacao['interacao'] + 1,
				"situacao" => "solucionada",
				"status" => "",

				"dt_validacao" => date("Y-m-d H:i:s"),
				"observacao_validacao" => $_POST['observacao'],

				"previsao_geral_inicio" => "",
				"previsao_geral" => "",

				"dt_final" => date("Y-m-d H:i:s"),
				"observacao_final" => $_POST['observacao'],

				"previsao_proposta_inicio" => "",
				"previsao_proposta" => "",
				"previsao_proposta_ja_alterada" => "",

				"encaminhamento_data_inicio" => "",
				"encaminhamento_data" => "",
				"id_encaminhamento" => "",

				"acao" => "",

				"status_questionamento" => "",
				"status_devolucao" => "",
				"devolucao_id_usuario" => "",
				"devolucao_motivo" => "",
				"devolucao_data" => "",
				"status_recusa" => "",

				"auto_email_status" => "",
				"auto_email_data" => "",
				"auto_email_solicitacao_descricao" => ""
			);
			$dados_solicitacao_descricao = array(
				"id_solicitacao" => $row_solicitacao['id'],
				"id_usuario_responsavel" => $row_usuario['IdUsuario'],
				"descricao" => $_POST['observacao'],
				"data" => date("Y-m-d H:i:s"),
				"tipo_postagem" => "Solucionada"
			);
		}
		// fim - se validação é de uma 'Conclusão'


		// se validação é de uma 'Reprovação'
		if ($row_solicitacao['acao'] == "Reprovar") {

			// deixar como sugestão (sim)
			if ($row_solicitacao['deixar_sugestao'] == "sim") {

				$dados_solicitacao = array(
					"interacao" => $row_solicitacao['interacao'] + 1,
					"situacao" => "solucionada",
					"status" => "",
					"tipo" => "Sugestão",

					"previsao_geral_inicio" => "",
					"previsao_geral" => "",

					"dt_validacao" => date("Y-m-d H:i:s"),
					"observacao_validacao" => $_POST['observacao'],

					"dt_final" => date("Y-m-d H:i:s"),
					"observacao_final" => $_POST['observacao'],

					"previsao_proposta_inicio" => "",
					"previsao_proposta" => "",
					"previsao_proposta_ja_alterada" => "",

					"encaminhamento_data_inicio" => "",
					"encaminhamento_data" => "",
					"id_encaminhamento" => "",

					"acao" => "",

					"status_questionamento" => "",
					"status_devolucao" => "",
					"devolucao_id_usuario" => "",
					"devolucao_motivo" => "",
					"devolucao_data" => "",
					"status_recusa" => "",

					"auto_email_status" => "",
					"auto_email_data" => "",
					"auto_email_solicitacao_descricao" => ""
				);
				$dados_solicitacao_descricao = array(
					"id_solicitacao" => $row_solicitacao['id'],
					"id_usuario_responsavel" => $row_usuario['IdUsuario'],
					"descricao" => $_POST['observacao'],
					"data" => date("Y-m-d H:i:s"),
					"tipo_postagem" => "Reprovação de solicitação"
				);
			}
			// fim - deixar como sugestão (sim)

			// deixar como sugestão (nao)
			if ($row_solicitacao['deixar_sugestao'] == "nao") {

				$dados_solicitacao = array(
					"interacao" => $row_solicitacao['interacao'] + 1,
					"situacao" => "reprovada",
					"status" => "",

					"previsao_geral_inicio" => "",
					"previsao_geral" => "",

					"dt_validacao" => date("Y-m-d H:i:s"),
					"observacao_validacao" => $_POST['observacao'],

					"dt_final" => date("Y-m-d H:i:s"),
					"observacao_final" => $_POST['observacao'],

					"previsao_proposta_inicio" => "",
					"previsao_proposta" => "",
					"previsao_proposta_ja_alterada" => "",

					"encaminhamento_data_inicio" => "",
					"encaminhamento_data" => "",
					"id_encaminhamento" => "",

					"acao" => "",

					"status_questionamento" => "",
					"status_devolucao" => "",
					"devolucao_id_usuario" => "",
					"devolucao_motivo" => "",
					"devolucao_data" => "",
					"status_recusa" => "",

					"auto_email_status" => "",
					"auto_email_data" => "",
					"auto_email_solicitacao_descricao" => ""
				);
				$dados_solicitacao_descricao = array(
					"id_solicitacao" => $row_solicitacao['id'],
					"id_usuario_responsavel" => $row_usuario['IdUsuario'],
					"descricao" => $_POST['observacao'],
					"data" => date("Y-m-d H:i:s"),
					"tipo_postagem" => "Reprovação de solicitação"
				);
			}
			// fim - deixar como sugestão (nao)

		}
		// fim - se validação é de uma 'Reprovação'

		funcao_solicitacao_update($row_solicitacao['id'], $dados_solicitacao, $dados_solicitacao_descricao);
		tempo_gasto($row_solicitacao['id'], $row_solicitacao['situacao'], $_GET['acao'], $row_usuario['IdUsuario'], 'solicitante', $_POST['tempo_gasto']);
		email_solicitacao($row_solicitacao['id'], $dados_solicitacao_descricao['tipo_postagem'], $dados_solicitacao_descricao['descricao']);
		$dados_solicitacao = array("interacao" => $row_solicitacao['interacao'] + 1,);
		$dados_solicitacao_descricao = array();
		funcao_solicitacao_redireciona();
		exit;
	}
	// fim - Concluir validação
	//---------------------------------------------------------------------------------------------------------------------------------------------------------------


	//---------------------------------------------------------------------------------------------------------------------------------------------------------------
	// CONCLUIR EXECUÇÃO --------------------------------------------------------------------------------------------------------------------------------------------
	//---------------------------------------------------------------------------------------------------------------------------------------------------------------
	// em orçamento // em execução
	if (($_GET['situacao'] == "geral" and $_GET['acao'] == "Concluir execução") and $_GET["resposta"] == "") {

		$versao_acao = "";
		foreach ($_POST as $versao_acao_campo => $versao_acao_valor) {
			if (strstr($versao_acao_campo, 'versao_acao') and $versao_acao_valor != "") {
				$versao_acao .= $versao_acao_valor . "<br>";
			}
		}

		if ($row_solicitacao['situacao'] == "em orçamento") {
			$dados_solicitacao = array(
				"interacao" => $row_solicitacao['interacao'] + 1,
				"situacao" => "executada",
				"status" => "encaminhada para testador",

				"dt_aprovacao_reprovacao" => date("Y-m-d H:i:s"),
				"id_usuario_aprovacao_reprovacao" => $row_usuario['IdUsuario'],
				"observacao_aprovacao_reprovacao" => $_POST['observacao'],

				"dt_orcamento" => date("Y-m-d H:i:s"),
				"orcamento" => "0",
				"prazo_desenvolvimento_orcamento" => "0",

				"previsao_solucao_inicio" => date("Y-m-d H:i:s"),
				"previsao_solucao" => date("Y-m-d H:i:s"),
				"id_executante" => $row_usuario['IdUsuario'],
				
				"dt_conclusao" => date("Y-m-d H:i:s"),

				"numero_revisao_svn_estavel" => @$_POST['numero_revisao_svn_estavel'],
				"numero_revisao_svn_desenvolvimento" => @$_POST['numero_revisao_svn_desenvolvimento'],

				"previsao_testes_inicio" => "0000-00-00 00:00:00",
				"previsao_testes" => "0000-00-00 00:00:00",
				"id_testador" => $_POST['testador'],
				"dt_conclusao_testes" => "",
				"observacao_testes" => "",

				"previsao_validacao_inicio" => "0000-00-00 00:00:00",
				"previsao_validacao" => "0000-00-00 00:00:00",
				"dt_validacao" => "",
				"observacao_validacao" => "",

				"previsao_geral_inicio" => date("Y-m-d H:i:s"),
				"previsao_geral" => date("Y-m-d H:i:s"),

				"dt_final" => "",
				"observacao_final" => "",

				"previsao_proposta_inicio" => "",
				"previsao_proposta" => "",
				"previsao_proposta_ja_alterada" => "",

				"testador_leu" => "",

				"encaminhamento_data_inicio" => "",
				"encaminhamento_data" => "",
				"id_encaminhamento" => "",

				"status_devolucao" => "",
				"devolucao_id_usuario" => "",
				"devolucao_motivo" => "",
				"devolucao_data" => "",
				"status_recusa" => ""
			);
		}

		if ($row_solicitacao['situacao'] == "em execução") {
			$dados_solicitacao = array(
				"interacao" => $row_solicitacao['interacao'] + 1,
				"situacao" => "executada",
				"status" => "encaminhada para testador",

				"dt_conclusao" => date("Y-m-d H:i:s"),

				"numero_revisao_svn_estavel" => @$_POST['numero_revisao_svn_estavel'],
				"numero_revisao_svn_desenvolvimento" => @$_POST['numero_revisao_svn_desenvolvimento'],

				"previsao_testes_inicio" => "0000-00-00 00:00:00",
				"previsao_testes" => "0000-00-00 00:00:00",
				"id_testador" => $_POST['testador'],
				"dt_conclusao_testes" => "",
				"observacao_testes" => "",

				"previsao_validacao_inicio" => "0000-00-00 00:00:00",
				"previsao_validacao" => "0000-00-00 00:00:00",
				"dt_validacao" => "",
				"observacao_validacao" => "",

				"previsao_geral_inicio" => date("Y-m-d H:i:s"),
				"previsao_geral" => date("Y-m-d H:i:s"),

				"dt_final" => "",
				"observacao_final" => "",

				"previsao_proposta_inicio" => "",
				"previsao_proposta" => "",
				"previsao_proposta_ja_alterada" => "",

				"testador_leu" => "",

				"encaminhamento_data_inicio" => "",
				"encaminhamento_data" => "",
				"id_encaminhamento" => "",

				"status_devolucao" => "",
				"devolucao_id_usuario" => "",
				"devolucao_motivo" => "",
				"devolucao_data" => "",
				"status_recusa" => ""
			);
		}

		// busca usuario_selecionado
		$colname_usuario_selecionado = "-1";
		if (isset($_POST['testador'])) {
			$colname_usuario_selecionado = $_POST['testador'];
		}
		mysql_select_db($database_conexao, $conexao);
		$query_usuario_selecionado = sprintf("SELECT IdUsuario, nome, praca FROM usuarios WHERE IdUsuario = %s", GetSQLValueString($colname_usuario_selecionado, "int"));
		$usuario_selecionado = mysql_query($query_usuario_selecionado, $conexao) or die(mysql_error());
		$row_usuario_selecionado = mysql_fetch_assoc($usuario_selecionado);
		$totalRows_usuario_selecionado = mysql_num_rows($usuario_selecionado);
		// fim - busca usuario_selecionado

		$dados_solicitacao_descricao = array(
			"id_solicitacao" => $row_solicitacao['id'],
			"id_usuario_responsavel" => $row_usuario['IdUsuario'],
			"descricao" => "Execução concluída - Entrega para testador<br>" . $_POST['observacao'] . "<br>Responsável pelos testes: " . $row_usuario_selecionado['nome'] . "
			<br><br>Versão que foi realizada ação/tratamento: <br>" . $versao_acao.
			"<br>Nº revisão SVN - Estável: ".$_POST['numero_revisao_svn_estavel']."<br>Nº revisão SVN - Desenvolvimento: ".$_POST['numero_revisao_svn_desenvolvimento'],
			"data" => date("Y-m-d H:i:s"),
			"tipo_postagem" => "Conclusão de execução",
			"conclusao_execucao" => 1
		);

		mysql_free_result($usuario_selecionado);

		funcao_solicitacao_update($row_solicitacao['id'], $dados_solicitacao, $dados_solicitacao_descricao);
		tempo_gasto($row_solicitacao['id'], $row_solicitacao['situacao'], $_GET['acao'], $row_usuario['IdUsuario'], 'executante', $_POST['tempo_gasto']);
		email_solicitacao($row_solicitacao['id'], $dados_solicitacao_descricao['tipo_postagem'], $dados_solicitacao_descricao['descricao']);
		$dados_solicitacao = array("interacao" => $row_solicitacao['interacao'] + 1,);
		$dados_solicitacao_descricao = array();
		funcao_solicitacao_redireciona();
		exit;
	}
	//---------------------------------------------------------------------------------------------------------------------------------------------------------------


	//---------------------------------------------------------------------------------------------------------------------------------------------------------------
	// DEVOLVER -----------------------------------------------------------------------------------------------------------------------------------------------------
	//---------------------------------------------------------------------------------------------------------------------------------------------------------------
	if (($_GET['situacao'] == "geral" and $_GET['acao'] == "Devolver") and $_GET["resposta"] == "") {

		$versao_acao = "";
		foreach ($_POST as $versao_acao_campo => $versao_acao_valor) {
			if (strstr($versao_acao_campo, 'versao_acao') and $versao_acao_valor != "") {
				$versao_acao .= $versao_acao_valor . "<br>";
			}
		}

		//region - busca solicitacao_tipo_devolucao_selecionado
		mysql_select_db($database_conexao, $conexao);
		$query_solicitacao_tipo_devolucao_selecionado = sprintf("
		SELECT 
			IdTipoDevolucao, titulo 
		FROM 
			solicitacao_tipo_devolucao 
		WHERE 
			IdTipoDevolucao = %s
		", 
		GetSQLValueString(@$_POST['devolucao_motivo'], "int"));
		$solicitacao_tipo_devolucao_selecionado = mysql_query($query_solicitacao_tipo_devolucao_selecionado, $conexao) or die(mysql_error());
		$row_solicitacao_tipo_devolucao_selecionado = mysql_fetch_assoc($solicitacao_tipo_devolucao_selecionado);
		$totalRows_solicitacao_tipo_devolucao_selecionado = mysql_num_rows($solicitacao_tipo_devolucao_selecionado);

		$solicitacao_tipo_devolucao_selecionado_titulo_atual = $row_solicitacao_tipo_devolucao_selecionado['titulo'];

		mysql_free_result($solicitacao_tipo_devolucao_selecionado);
		//endregion - fim - busca solicitacao_tipo_devolucao_selecionado

		if ($row_solicitacao['status_devolucao'] == "") { // se NÃO foi realizado devolução
			$status_devolucao = $row_solicitacao['status'];
		} else { // se foi realizado devolução
			$status_devolucao = $row_solicitacao['status_devolucao'];
		}

		$devolver_para = $_POST['devolver_para'];

		$dados_solicitacao = array(
			"interacao" => $row_solicitacao['interacao'] + 1,
			"status" => "devolvida para $devolver_para",

			"status_devolucao" => $status_devolucao,
			
			"devolucao_id_usuario" => $row_usuario['IdUsuario'],
			"devolucao_motivo" => $_POST['devolucao_motivo'],
			"devolucao_data" => date('Y-m-d H:i:s')
		);

		$dados_solicitacao_descricao = array(
			"id_solicitacao" => $row_solicitacao['id'],
			"id_usuario_responsavel" => $row_usuario['IdUsuario'],
			"descricao" => $_POST['observacao'] . "<br>Versão que foi realizada ação/tratamento: " . $versao_acao."Motivo da devolução: ".$solicitacao_tipo_devolucao_selecionado_titulo_atual,
			"data" => date("Y-m-d H:i:s"),
			"tipo_postagem" => "Devolução para $devolver_para"
		);

		funcao_solicitacao_update($row_solicitacao['id'], $dados_solicitacao, $dados_solicitacao_descricao);

		if ($row_solicitacao['situacao'] == "em testes") {
			tempo_gasto($row_solicitacao['id'], $row_solicitacao['situacao'], $_GET['acao'], $row_usuario['IdUsuario'], 'testador', $_POST['tempo_gasto']);
		}

		email_solicitacao($row_solicitacao['id'], $dados_solicitacao_descricao['tipo_postagem'], $dados_solicitacao_descricao['descricao']);
		$dados_solicitacao = array("interacao" => $row_solicitacao['interacao'] + 1,);
		$dados_solicitacao_descricao = array();
		funcao_solicitacao_redireciona();
		exit;
	}
	//---------------------------------------------------------------------------------------------------------------------------------------------------------------


	//---------------------------------------------------------------------------------------------------------------------------------------------------------------
	// ENCAMINHAR ---------------------------------------------------------------------------------------------------------------------------------------------
	//---------------------------------------------------------------------------------------------------------------------------------------------------------------
	if ($_GET['acao'] == "Encaminhar" and $_GET["resposta"] == "") {

		$versao_acao = "";
		foreach ($_POST as $versao_acao_campo => $versao_acao_valor) {
			if (strstr($versao_acao_campo, 'versao_acao') and $versao_acao_valor != "") {
				$versao_acao .= $versao_acao_valor . "<br>";
			}
		}

		$responsavel = "";
		$responsavel_id = "";

		if ($_GET['situacao'] == "analisada") { // Encaminhar - Escolher analista de orçamento
			$responsavel = "analista de orçamento";
			$responsavel_id = $_POST['executante'];
			$dados_solicitacao = array(
				"interacao" => $row_solicitacao['interacao'] + 1,
				"status" => "encaminhada para analista",

				"previsao_analise_orcamento_inicio" => "0000-00-00 00:00:00",
				"previsao_analise_orcamento" => "0000-00-00 00:00:00",
				"id_analista_orcamento" => $_POST['executante'],
				"dt_orcamento" => "",
				"orcamento" => "",
				"prazo_desenvolvimento_orcamento" => "",

				"previsao_geral_inicio" => date("Y-m-d H:i:s"),
				"previsao_geral" => date("Y-m-d H:i:s"),

				"analista_orcamento_leu" => "",

				"previsao_proposta_inicio" => "",
				"previsao_proposta" => "",
				"previsao_proposta_ja_alterada" => "",

				"encaminhamento_data_inicio" => "",
				"encaminhamento_data" => "",
				"id_encaminhamento" => "",

				"status_devolucao" => "",
				"devolucao_id_usuario" => "",
				"devolucao_motivo" => "",
				"devolucao_data" => "",
				"status_recusa" => ""
			);
		}

		if ($_GET['situacao'] == "em orçamento") { // Encaminhar (OPE / ANA)
			$responsavel = "analista de orçamento";
			$responsavel_id = $_POST['executante'];
			$dados_solicitacao = array(
				"interacao" => $row_solicitacao['interacao'] + 1,
				"situacao" => "analisada",
				"status" => "encaminhada para analista", // pendente executante

				"previsao_analise_orcamento_inicio" => "0000-00-00 00:00:00",
				"previsao_analise_orcamento" => "0000-00-00 00:00:00",
				"id_analista_orcamento" => $_POST['executante'],
				"dt_orcamento" => "",
				"orcamento" => "",
				"prazo_desenvolvimento_orcamento" => "",

				"previsao_geral_inicio" => date("Y-m-d H:i:s"),
				"previsao_geral" => date("Y-m-d H:i:s"),

				"analista_orcamento_leu" => "",

				"previsao_proposta_inicio" => "",
				"previsao_proposta" => "",
				"previsao_proposta_ja_alterada" => "s",

				"encaminhamento_data_inicio" => $row_solicitacao['previsao_analise_orcamento_inicio'],
				"id_encaminhamento" => $row_solicitacao['id_analista_orcamento'],
				"encaminhamento_data" => $row_solicitacao['previsao_analise_orcamento'],

				"status_devolucao" => "",
				"devolucao_id_usuario" => "",
				"devolucao_motivo" => "",
				"devolucao_data" => "",
				"status_recusa" => ""
			);
		}

		if ($_GET['situacao'] == "aprovada") { // Encaminhar - Escolher executante
			$responsavel = "executante";
			$responsavel_id = $_POST['executante'];

			$orcamento_os = "";
			if ($row_solicitacao['orcamento'] != "") {
				$orcamento_os = $_POST['orcamento_os'];
			}

			$dados_solicitacao = array(
				"interacao" => $row_solicitacao['interacao'] + 1,
				"status" => "encaminhada para executante",

				"orcamento_os" => $orcamento_os,

				"id_executante" => $_POST['executante'],
				"previsao_solucao_inicio" => "0000-00-00 00:00:00",
				"previsao_solucao" => "0000-00-00 00:00:00",
				"dt_conclusao" => "",

				"previsao_geral_inicio" => date("Y-m-d H:i:s"),
				"previsao_geral" => date("Y-m-d H:i:s"),

				"executante_leu" => "",

				"previsao_proposta_inicio" => "",
				"previsao_proposta" => "",
				"previsao_proposta_ja_alterada" => "",

				"encaminhamento_data_inicio" => "",
				"encaminhamento_data" => "",
				"id_encaminhamento" => "",

				"status_devolucao" => "",
				"devolucao_id_usuario" => "",
				"devolucao_motivo" => "",
				"devolucao_data" => "",
				"status_recusa" => ""
			);
		}

		if ($_GET['situacao'] == "em execução") { // Encaminhar (OPE / EXE)
			$responsavel = "executante";
			$responsavel_id = $_POST['executante'];
			$dados_solicitacao = array(
				"interacao" => $row_solicitacao['interacao'] + 1,
				"situacao" => "aprovada",
				"status" => "encaminhada para executante",

				"previsao_solucao_inicio" => "0000-00-00 00:00:00",
				"previsao_solucao" => "0000-00-00 00:00:00",
				"id_executante" => $_POST['executante'],
				"dt_conclusao" => "",

				"previsao_geral_inicio" => date("Y-m-d H:i:s"),
				"previsao_geral" => date("Y-m-d H:i:s"),

				"executante_leu" => "",

				"previsao_proposta_inicio" => "",
				"previsao_proposta" => "",
				"previsao_proposta_ja_alterada" => "s",

				"encaminhamento_data_inicio" => $row_solicitacao['previsao_solucao_inicio'],
				"encaminhamento_data" => $row_solicitacao['previsao_solucao'],
				"id_encaminhamento" => $row_solicitacao['id_executante'],

				"status_devolucao" => "",
				"devolucao_id_usuario" => "",
				"devolucao_motivo" => "",
				"devolucao_data" => "",
				"status_recusa" => ""
			);
		}

		if ($_GET['situacao'] == "executada") { // Encaminhar - Escolher testador
			$responsavel = "testador";
			$responsavel_id = $_POST['testador'];
			$dados_solicitacao = array(
				"interacao" => $row_solicitacao['interacao'] + 1,
				"status" => "encaminhada para testador",

				"id_testador" => $_POST['testador'],
				"previsao_testes_inicio" => "0000-00-00 00:00:00",
				"previsao_testes" => "0000-00-00 00:00:00",
				"dt_conclusao_testes" => "",

				"previsao_geral_inicio" => date("Y-m-d H:i:s"),
				"previsao_geral" => date("Y-m-d H:i:s"),

				"testador_leu" => "",

				"previsao_proposta_inicio" => "",
				"previsao_proposta" => "",
				"previsao_proposta_ja_alterada" => "",

				"encaminhamento_data_inicio" => "",
				"encaminhamento_data" => "",
				"id_encaminhamento" => "",

				"status_devolucao" => "",
				"devolucao_id_usuario" => "",
				"devolucao_motivo" => "",
				"devolucao_data" => "",
				"status_recusa" => ""
			);
		}

		if ($_GET['situacao'] == "em testes") { // Encaminhar (OPE / TES)
			$responsavel = "testador";
			$responsavel_id = $_POST['testador'];
			$dados_solicitacao = array(
				"interacao" => $row_solicitacao['interacao'] + 1,
				"situacao" => "executada",
				"status" => "encaminhada para testador",

				"previsao_testes_inicio" => "0000-00-00 00:00:00",
				"previsao_testes" => "0000-00-00 00:00:00",
				"id_testador" => $_POST['testador'],
				"dt_conclusao_testes" => "",

				"previsao_geral_inicio" => date("Y-m-d H:i:s"),
				"previsao_geral" => date("Y-m-d H:i:s"),

				"testador_leu" => "",

				"previsao_proposta_inicio" => "",
				"previsao_proposta" => "",
				"previsao_proposta_ja_alterada" => "s",

				"encaminhamento_data_inicio" => $row_solicitacao['previsao_testes_inicio'],
				"encaminhamento_data" => $row_solicitacao['previsao_testes'],
				"id_encaminhamento" => $row_solicitacao['testador'],

				"status_devolucao" => "",
				"devolucao_id_usuario" => "",
				"devolucao_motivo" => "",
				"devolucao_data" => "",
				"status_recusa" => ""
			);
		}

		// busca usuario_selecionado
		mysql_select_db($database_conexao, $conexao);
		$query_usuario_selecionado = sprintf("SELECT IdUsuario, nome, praca FROM usuarios WHERE IdUsuario = %s", GetSQLValueString($responsavel_id, "int"));
		$usuario_selecionado = mysql_query($query_usuario_selecionado, $conexao) or die(mysql_error());
		$row_usuario_selecionado = mysql_fetch_assoc($usuario_selecionado);
		$totalRows_usuario_selecionado = mysql_num_rows($usuario_selecionado);
		// fim - busca usuario_selecionado

		$dados_solicitacao_descricao = array(
			"id_solicitacao" => $row_solicitacao['id'],
			"id_usuario_responsavel" => $row_usuario['IdUsuario'],
			"descricao" => "Encaminhada para novo $responsavel<br>Para: " . $row_usuario_selecionado['nome'] . "<br>" . $_POST['observacao'] . "<br>Versão que foi realizada ação/tratamento: <br>" . $versao_acao,
			"data" => date("Y-m-d H:i:s"),
			"tipo_postagem" => "Escolha de novo $responsavel"
		);

		mysql_free_result($usuario_selecionado);

		// insere - tempo gasto	
		tempo_gasto($row_solicitacao['id'], $row_solicitacao['situacao'], $_GET['acao'], $row_usuario['IdUsuario'], $responsavel, $_POST['tempo_gasto']);
		// fim - insere - tempo gasto

		funcao_solicitacao_update($row_solicitacao['id'], $dados_solicitacao, $dados_solicitacao_descricao);

		email_solicitacao($row_solicitacao['id'], $dados_solicitacao_descricao['tipo_postagem'], $dados_solicitacao_descricao['descricao']);
		$dados_solicitacao = array("interacao" => $row_solicitacao['interacao'] + 1,);
		$dados_solicitacao_descricao = array();
		funcao_solicitacao_redireciona();
		exit;
	}
	//---------------------------------------------------------------------------------------------------------------------------------------------------------------


	//---------------------------------------------------------------------------------------------------------------------------------------------------------------
	// ACEITAR ---------------------------------------------------------------------------------------------------------------------------------------
	//---------------------------------------------------------------------------------------------------------------------------------------------------------------
	// aceitar ------------------------------------------------------------------------------------------------------------
	if ($_GET['acao'] == "Aceitar" and $_GET["resposta"] == "" and $row_solicitacao['status_recusa'] != "1") { // aceitar

		$solicitacao_tipo = "Solicitação";

		// criada
		if ($_GET['situacao'] == "criada") {
			$responsavel = "operador";
			$dados_solicitacao = array(
				"interacao" => $row_solicitacao['interacao'] + 1,
				"dt_recebimento" => date("Y-m-d H:i:s"),
				"id_operador" => $row_usuario['IdUsuario'],
				"situacao" => "recebida",
				"status" => "pendente operador",
				"previsao_geral_inicio" => date("Y-m-d H:i:s"),
				"previsao_geral" => date("Y-m-d H:i:s")
			);
		}
		// fim - criada

		// analisada
		if ($_GET['situacao'] == "analisada") {
			$responsavel = "analista de orçamento";
			$dados_solicitacao = array(
				"interacao" => $row_solicitacao['interacao'] + 1,
				"situacao" => "em orçamento",
				"status" => "pendente executante",

				"id_analista_orcamento" => $row_usuario['IdUsuario'],
				"previsao_analise_orcamento_inicio" => $_POST['data_inicio'],
				"previsao_analise_orcamento" => $_POST['data'],

				"previsao_geral_inicio" => $_POST['data_inicio'],
				"previsao_geral" => $_POST['data'],

				"previsao_proposta_inicio" => "",
				"previsao_proposta" => "",
				"previsao_proposta_ja_alterada" => "s",

				"alterar_previsao_solicitante" => 0,
				"alterar_previsao_operador" => 0,
				"alterar_previsao_executante" => 0,
				"alterar_previsao_testador" => 0,

				"id_encaminhamento" => "",
				"encaminhamento_data_inicio" => "",
				"encaminhamento_data" => "",

				"status_devolucao" => "",
				"devolucao_id_usuario" => "",
				"devolucao_motivo" => "",
				"devolucao_data" => "",
				"status_recusa" => ""
			);
		}
		// fim - analisada

		// aprovada
		if ($_GET['situacao'] == "aprovada") {
			$responsavel = "executante";
			$dados_solicitacao = array(
				"interacao" => $row_solicitacao['interacao'] + 1,
				"situacao" => "em execução",
				"status" => "pendente executante",

				"previsao_solucao_inicio" => $_POST['data_inicio'],
				"previsao_solucao" => $_POST['data'],
				"id_executante" => $row_usuario['IdUsuario'],

				"previsao_geral_inicio" => $_POST['data_inicio'],
				"previsao_geral" => $_POST['data'],

				"previsao_proposta_inicio" => "",
				"previsao_proposta" => "",
				"previsao_proposta_ja_alterada" => "s",

				"alterar_previsao_solicitante" => 0,
				"alterar_previsao_operador" => 0,
				"alterar_previsao_executante" => 0,
				"alterar_previsao_testador" => 0,

				"id_encaminhamento" => "",
				"encaminhamento_data_inicio" => "",
				"encaminhamento_data" => "",

				"status_devolucao" => "",
				"devolucao_id_usuario" => "",
				"devolucao_motivo" => "",
				"devolucao_data" => "",
				"status_recusa" => ""
			);
		}
		// fim - aprovada

		// executada
		if ($_GET['situacao'] == "executada") {
			$responsavel = "testador";
			$dados_solicitacao = array(
				"interacao" => $row_solicitacao['interacao'] + 1,
				"situacao" => "em testes",
				"status" => "pendente testador",

				"id_testador" => $row_usuario['IdUsuario'],
				"previsao_testes_inicio" => $_POST['data_inicio'],
				"previsao_testes" => $_POST['data'],
				"dt_conclusao_testes" => "",

				"previsao_geral_inicio" => $_POST['data_inicio'],
				"previsao_geral" => $_POST['data'],

				"previsao_proposta_inicio" => "",
				"previsao_proposta" => "",
				"previsao_proposta_ja_alterada" => "s",

				"alterar_previsao_solicitante" => 0,
				"alterar_previsao_operador" => 0,
				"alterar_previsao_executante" => 0,
				"alterar_previsao_testador" => 0,

				"id_encaminhamento" => "",
				"encaminhamento_data_inicio" => "",
				"encaminhamento_data" => "",

				"status_devolucao" => "",
				"devolucao_id_usuario" => "",
				"devolucao_motivo" => "",
				"devolucao_data" => "",
				"status_recusa" => ""
			);
		}
		// fim - executada

		// testada
		if ($_GET['situacao'] == "testada") {
			$responsavel = "solicitante";
			$dados_solicitacao = array(
				"interacao" => $row_solicitacao['interacao'] + 1,
				"situacao" => "em validação",
				"status" => "pendente solicitante",

				"previsao_validacao_inicio" => date('Y-m-d H:i:s'),
				"previsao_validacao" =>  date('Y-m-d H:i:s'),
				"dt_validacao" => "",
				"observacao_validacao" => "",

				"previsao_geral_inicio" => date('Y-m-d H:i:s'),
				"previsao_geral" =>  date('Y-m-d H:i:s'),

				"previsao_proposta_inicio" => "",
				"previsao_proposta" => "",
				"previsao_proposta_ja_alterada" => "s",

				"alterar_previsao_solicitante" => 0,
				"alterar_previsao_operador" => 0,
				"alterar_previsao_executante" => 0,
				"alterar_previsao_testador" => 0,

				"id_encaminhamento" => "",
				"encaminhamento_data_inicio" => "",
				"encaminhamento_data" => "",

				"acao" => "Concluir",

				"status_devolucao" => "",
				"devolucao_id_usuario" => "",
				"devolucao_motivo" => "",
				"devolucao_data" => "",
				"status_recusa" => ""
			);
		}
		// fim - testada

		// devolução
		if ($row_solicitacao['status_devolucao'] != "") {

			if ($row_solicitacao['status'] == "devolvida para operador" and $row_solicitacao['situacao'] == "em orçamento") { // devolve para analisada
				$dados_solicitacao = array(
					"interacao" => $row_solicitacao['interacao'] + 1,
					"situacao" => "em análise",
					"status" => "pendente operador",

					"previsao_analise_inicio" => $_POST['data_inicio'],
					"previsao_analise" => $_POST['data'],

					"dt_aprovacao_reprovacao" => "",
					"id_usuario_aprovacao_reprovacao" => "",
					"observacao_aprovacao_reprovacao" => "",

					"previsao_retorno_orcamento_inicio" => "0000-00-00 00:00:00",
					"previsao_retorno_orcamento" => "0000-00-00 00:00:00",

					"previsao_analise_orcamento_inicio" => "0000-00-00 00:00:00",
					"previsao_analise_orcamento" => "0000-00-00 00:00:00",
					"id_analista_orcamento" => "",
					"dt_orcamento" => "",
					"orcamento" => "",
					"prazo_desenvolvimento_orcamento" => "",
					"orcamento_os" => "",

					"previsao_solucao_inicio" => "0000-00-00 00:00:00",
					"previsao_solucao" => "0000-00-00 00:00:00",
					"id_executante" => "",
					"dt_conclusao" => "",

					"previsao_testes_inicio" => "0000-00-00 00:00:00",
					"previsao_testes" => "0000-00-00 00:00:00",
					"id_testador" => "",
					"dt_conclusao_testes" => "",
					"observacao_testes" => "",

					"previsao_validacao_inicio" => "0000-00-00 00:00:00",
					"previsao_validacao" => "0000-00-00 00:00:00",
					"dt_validacao" => "",
					"observacao_validacao" => "",

					"previsao_geral_inicio" => $_POST['data_inicio'],
					"previsao_geral" => $_POST['data'],

					"analista_orcamento_leu" => "",
					"executante_leu" => "",
					"testador_leu" => "",

					"dt_final" => "",
					"observacao_final" => "",

					"previsao_proposta_inicio" => "",
					"previsao_proposta" => "",
					"previsao_proposta_ja_alterada" => "",

					"alterar_previsao_solicitante" => 0,
					"alterar_previsao_operador" => 0,
					"alterar_previsao_executante" => 0,
					"alterar_previsao_testador" => 0,

					"encaminhamento_data_inicio" => "",
					"encaminhamento_data" => "",
					"id_encaminhamento" => "",

					"status_devolucao" => "",
					"devolucao_id_usuario" => "",
					"devolucao_motivo" => "",
					"devolucao_data" => "",
					"status_recusa" => ""
				);
			}
			if ($row_solicitacao['status'] == "devolvida para operador" and $row_solicitacao['situacao'] != "em orçamento") { // devolve para aprovada
				$dados_solicitacao = array(
					"interacao" => $row_solicitacao['interacao'] + 1,
					"situacao" => "aprovada",
					"status" => "pendente operador",

					"previsao_solucao_inicio" => "0000-00-00 00:00:00",
					"previsao_solucao" => "0000-00-00 00:00:00",
					"id_executante" => "",
					"dt_conclusao" => "",

					"previsao_testes_inicio" => "0000-00-00 00:00:00",
					"previsao_testes" => "0000-00-00 00:00:00",
					"id_testador" => "",
					"dt_conclusao_testes" => "",
					"observacao_testes" => "",

					"previsao_validacao_inicio" => "0000-00-00 00:00:00",
					"previsao_validacao" => "0000-00-00 00:00:00",
					"dt_validacao" => "",
					"observacao_validacao" => "",

					"previsao_geral_inicio" => date("Y-m-d H:i:s"),
					"previsao_geral" => date("Y-m-d H:i:s"),

					"executante_leu" => "",
					"testador_leu" => "",

					"dt_final" => "",
					"observacao_final" => "",

					"previsao_proposta_inicio" => "",
					"previsao_proposta" => "",
					"previsao_proposta_ja_alterada" => "s",

					"alterar_previsao_solicitante" => 0,
					"alterar_previsao_operador" => 0,
					"alterar_previsao_executante" => 0,
					"alterar_previsao_testador" => 0,

					"encaminhamento_data_inicio" => "",
					"encaminhamento_data" => "",
					"id_encaminhamento" => "",

					"status_devolucao" => "",
					"devolucao_id_usuario" => "",
					"devolucao_motivo" => "",
					"devolucao_data" => "",
					"status_recusa" => ""
				);
			}
			if ($row_solicitacao['status'] == "devolvida para executante") { // devolve para em execução
				$dados_solicitacao = array(
					"interacao" => $row_solicitacao['interacao'] + 1,
					"situacao" => "em execução",
					"status" => "pendente executante",

					"previsao_solucao_inicio" => $_POST['data_inicio'],
					"previsao_solucao" => $_POST['data'],
					"dt_conclusao" => "",

					"previsao_testes_inicio" => "0000-00-00 00:00:00",
					"previsao_testes" => "0000-00-00 00:00:00",
					"id_testador" => "",
					"dt_conclusao_testes" => "",
					"observacao_testes" => "",

					"previsao_validacao_inicio" => "0000-00-00 00:00:00",
					"previsao_validacao" => "0000-00-00 00:00:00",
					"dt_validacao" => "",
					"observacao_validacao" => "",

					"previsao_geral_inicio" => $_POST['data_inicio'],
					"previsao_geral" => $_POST['data'],

					"testador_leu" => "",

					"previsao_proposta_inicio" => "",
					"previsao_proposta" => "",
					"previsao_proposta_ja_alterada" => "s",

					"alterar_previsao_solicitante" => 0,
					"alterar_previsao_operador" => 0,
					"alterar_previsao_executante" => 0,
					"alterar_previsao_testador" => 0,

					"id_encaminhamento" => "",
					"encaminhamento_data_inicio" => "",
					"encaminhamento_data" => "",

					"status_devolucao" => "",
					"devolucao_id_usuario" => "",
					"devolucao_motivo" => "",
					"devolucao_data" => "",
					"status_recusa" => ""
				);
			}
			if ($row_solicitacao['status'] == "devolvida para testador") { // devolve para em testes
				$dados_solicitacao = array(
					"interacao" => $row_solicitacao['interacao'] + 1,
					"situacao" => "em testes",
					"status" => "pendente testador",

					"previsao_testes_inicio" => $_POST['data_inicio'],
					"previsao_testes" => $_POST['data'],
					"dt_conclusao_testes" => "",
					"observacao_testes" => "",

					"previsao_validacao_inicio" => "0000-00-00 00:00:00",
					"previsao_validacao" => "0000-00-00 00:00:00",
					"dt_validacao" => "",
					"observacao_validacao" => "",

					"previsao_geral_inicio" => $_POST['data_inicio'],
					"previsao_geral" => $_POST['data'],

					"previsao_proposta_inicio" => "",
					"previsao_proposta" => "",
					"previsao_proposta_ja_alterada" => "s",

					"alterar_previsao_solicitante" => 0,
					"alterar_previsao_operador" => 0,
					"alterar_previsao_executante" => 0,
					"alterar_previsao_testador" => 0,

					"id_encaminhamento" => "",
					"encaminhamento_data_inicio" => "",
					"encaminhamento_data" => "",

					"status_devolucao" => "",
					"devolucao_id_usuario" => "",
					"devolucao_motivo" => "",
					"devolucao_data" => "",
					"status_recusa" => ""
				);
			}

			$solicitacao_tipo = "Devolução";
			if ($row_solicitacao['status'] == "devolvida para operador") {
				$responsavel = "operador";
			}
			if ($row_solicitacao['status'] == "devolvida para executante") {
				$responsavel = "executante";
			}
			if ($row_solicitacao['status'] == "devolvida para testador") {
				$responsavel = "testador";
			}

		}
		// fim - devolução


		$dados_solicitacao_descricao = array(
			"id_solicitacao" => $row_solicitacao['id'],
			"id_usuario_responsavel" => $row_usuario['IdUsuario'],
			"descricao" => $_POST['observacao'],
			"data" => date("Y-m-d H:i:s"),
			"tipo_postagem" => "$solicitacao_tipo aceita por $responsavel"
		);

		funcao_solicitacao_update($row_solicitacao['id'], $dados_solicitacao, $dados_solicitacao_descricao);

		if($row_solicitacao['status_devolucao'] != ""){
			devolucao($row_solicitacao['id'], $row_solicitacao['situacao'], $row_solicitacao['devolucao_id_usuario'], $responsavel, $row_solicitacao['devolucao_motivo'], "a", $row_solicitacao['devolucao_data']);
		}

		email_solicitacao($row_solicitacao['id'], $dados_solicitacao_descricao['tipo_postagem'], $dados_solicitacao_descricao['descricao']);
		$dados_solicitacao = array("interacao" => $row_solicitacao['interacao'] + 1,);
		$dados_solicitacao_descricao = array();
		funcao_solicitacao_redireciona();
		exit;
	}
	// fim - aceitar -------------------------------------------------------------------------------------------------------

	// aceitar recusa ------------------------------------------------------------------------------------------------------
	if ($_GET['acao'] == "Aceitar" and $_GET["resposta"] == "" and $row_solicitacao['status_recusa'] == "1") { // aceitar recusa

		// analisada
		if ($_GET['situacao'] == "analisada") {
			if ($row_solicitacao['id_encaminhamento'] == "") {
				$dados_solicitacao = array(
					"interacao" => $row_solicitacao['interacao'] + 1,
					"status" => "pendente operador",

					"id_analista_orcamento" => "",

					"previsao_geral_inicio" => date("Y-m-d H:i:s"),
					"previsao_geral" => date("Y-m-d H:i:s"),

					"analista_orcamento_leu" => "",

					"status_recusa" => ""
				);
			} else { // aqui é quando a solicitação vem de um encaminhamento
				$dados_solicitacao = array(
					"interacao" => $row_solicitacao['interacao'] + 1,
					"id_analista_orcamento" => $row_solicitacao['id_encaminhamento'],
					"status" => "pendente executante",
					"previsao_geral_inicio" => $row_solicitacao['encaminhamento_data_inicio'],
					"previsao_geral" => $row_solicitacao['encaminhamento_data'],
					"previsao_analise_orcamento_inicio" => $row_solicitacao['encaminhamento_data_inicio'],
					"previsao_analise_orcamento" => $row_solicitacao['encaminhamento_data'],
					"situacao" => "em orçamento",
					"previsao_proposta_ja_alterada" => "s",
					"id_encaminhamento" => "",
					"encaminhamento_data_inicio" => "",
					"encaminhamento_data" => "",
					"analista_orcamento_leu" => "",
					"status_recusa" => ""
				);
			}
		}
		// fim - analisada

		// aprovada
		if ($_GET['situacao'] == "aprovada") {
			if ($row_solicitacao['id_encaminhamento'] == "") {
				$dados_solicitacao = array(
					"interacao" => $row_solicitacao['interacao'] + 1,
					"id_executante" => "",
					"status" => "pendente operador",
					"previsao_geral_inicio" => date("Y-m-d H:i:s"),
					"previsao_geral" => date("Y-m-d H:i:s"),
					"status_recusa" => ""
				);
			} else { // aqui é quando a solicitação vem de um encaminhamento
				$dados_solicitacao = array(
					"interacao" => $row_solicitacao['interacao'] + 1,
					"id_executante" => $row_solicitacao['id_encaminhamento'],
					"status" => "pendente executante",
					"previsao_geral_inicio" => $row_solicitacao['encaminhamento_data_inicio'],
					"previsao_geral" => $row_solicitacao['encaminhamento_data'],

					"previsao_solucao_inicio" => $row_solicitacao['encaminhamento_data_inicio'],
					"previsao_solucao" => $row_solicitacao['encaminhamento_data'],
					"situacao" => "em execução",
					"previsao_proposta_ja_alterada" => "s",
					"id_encaminhamento" => "",
					"encaminhamento_data" => "",
					"status_recusa" => ""
				);
			}
		}
		// fim - aprovada

		// executada
		if ($_GET['situacao'] == "executada") {
			if ($row_solicitacao['id_encaminhamento'] == "") {
				$dados_solicitacao = array(
					"interacao" => $row_solicitacao['interacao'] + 1,
					"situacao" => "em execução",
					"status" => "pendente executante",

					"dt_conclusao" => "",

					"previsao_testes_inicio" => "0000-00-00 00:00:00",
					"previsao_testes" => "0000-00-00 00:00:00",
					"id_testador" => "",
					"dt_conclusao_testes" => "",
					"observacao_testes" => "",

					"previsao_validacao_inicio" => "0000-00-00 00:00:00",
					"previsao_validacao" => "0000-00-00 00:00:00",
					"dt_validacao" => "",
					"observacao_validacao" => "",

					"previsao_geral_inicio" => $row_solicitacao['previsao_solucao_inicio'],
					"previsao_geral" => $row_solicitacao['previsao_solucao'],

					"testador_leu" => "",

					"previsao_proposta_inicio" => "",
					"previsao_proposta" => "",
					"previsao_proposta_ja_alterada" => "s",

					"id_encaminhamento" => "",
					"encaminhamento_data_inicio" => "",
					"encaminhamento_data" => "",

					"status_devolucao" => "",
					"devolucao_id_usuario" => "",
					"devolucao_motivo" => "",
					"devolucao_data" => "",
					"status_recusa" => ""
				);
			} else { // aqui é quando a solicitação vem de um encaminhamento
				$dados_solicitacao = array(
					"interacao" => $row_solicitacao['interacao'] + 1,
					"situacao" => "em execução",
					"status" => "pendente executante",

					"id_executante" => $row_solicitacao['id_encaminhamento'],
					"previsao_solucao_inicio" => $row_solicitacao['encaminhamento_data_inicio'],
					"previsao_solucao" => $row_solicitacao['encaminhamento_data'],
					"dt_conclusao" => "",

					"previsao_testes_inicio" => "0000-00-00 00:00:00",
					"previsao_testes" => "0000-00-00 00:00:00",
					"id_testador" => "",
					"dt_conclusao_testes" => "",
					"observacao_testes" => "",

					"previsao_validacao_inicio" => "0000-00-00 00:00:00",
					"previsao_validacao" => "0000-00-00 00:00:00",
					"dt_validacao" => "",
					"observacao_validacao" => "",

					"previsao_geral_inicio" => $row_solicitacao['encaminhamento_data_inicio'],
					"previsao_geral" => $row_solicitacao['encaminhamento_data'],

					"testador_leu" => "",

					"previsao_proposta_inicio" => "",
					"previsao_proposta" => "",
					"previsao_proposta_ja_alterada" => "s",

					"id_encaminhamento" => "",
					"encaminhamento_data_inicio" => "",
					"encaminhamento_data" => "",

					"status_devolucao" => "",
					"devolucao_id_usuario" => "",
					"devolucao_motivo" => "",
					"devolucao_data" => "",
					"status_recusa" => ""
				);
			}
		}
		// fim - executada

		// testada ***
		if ($_GET['situacao'] == "testada") {

			$dados_solicitacao = array(
				"interacao" => $row_solicitacao['interacao'] + 1,
				"situacao" => "em testes",
				"status" => "pendente testador",

				"previsao_testes_inicio" => $row_solicitacao['previsao_testes_inicio'],
				"previsao_testes" => $row_solicitacao['previsao_testes'],
				"dt_conclusao_testes" => "",

				"previsao_validacao_inicio" => "0000-00-00 00:00:00",
				"previsao_validacao" => "0000-00-00 00:00:00",
				"dt_validacao" => "",
				"observacao_validacao" => "",

				"previsao_geral_inicio" => $row_solicitacao['previsao_testes_inicio'],
				"previsao_geral" => $row_solicitacao['previsao_testes'],

				"testador_leu" => "",

				"previsao_proposta_inicio" => "",
				"previsao_proposta" => "",
				"previsao_proposta_ja_alterada" => "s",

				"alterar_previsao_solicitante" => 0,
				"alterar_previsao_operador" => 0,
				"alterar_previsao_executante" => 0,
				"alterar_previsao_testador" => 0,

				"id_encaminhamento" => "",
				"encaminhamento_data_inicio" => "",
				"encaminhamento_data" => "",

				"status_devolucao" => "",
				"devolucao_id_usuario" => "",
				"devolucao_motivo" => "",
				"devolucao_data" => "",
				"status_recusa" => ""
			);

		}
		// fim - testada

		$dados_solicitacao_descricao = array(
			"id_solicitacao" => $row_solicitacao['id'],
			"id_usuario_responsavel" => $row_usuario['IdUsuario'],
			"descricao" => "Motivo: " . $_POST['observacao'],
			"data" => date("Y-m-d H:i:s"),
			"tipo_postagem" => "Recusa aceita"
		);

		funcao_solicitacao_update($row_solicitacao['id'], $dados_solicitacao, $dados_solicitacao_descricao);
		email_solicitacao($row_solicitacao['id'], $dados_solicitacao_descricao['tipo_postagem'], $dados_solicitacao_descricao['descricao']);
		$dados_solicitacao = array("interacao" => $row_solicitacao['interacao'] + 1,);
		$dados_solicitacao_descricao = array();
		funcao_solicitacao_redireciona();
		exit;
	}
	// fim - aceitar recusa ------------------------------------------------------------------------------------------------
	//---------------------------------------------------------------------------------------------------------------------------------------------------------------


	//---------------------------------------------------------------------------------------------------------------------------------------------------------------
	// RECUSAR ---------------------------------------------------------------------------------------------------------------------------------------
	//---------------------------------------------------------------------------------------------------------------------------------------------------------------
	// recusar -----------------------------------------------------------------------------------------------------
	// OBS: status_recusa recebe '1' quando o 'analista de orçamento', 'executante' e 'testador' recusam a 'análise', 'execução' e 'testes'
	if ($_GET['acao'] == "Recusar" and $_GET["resposta"] == "" and $row_solicitacao['status_recusa'] != "1") { // recusar

		$solicitacao_tipo = "Solicitação";

		// criada
		if ($_GET['situacao'] == "criada") {

			$responsavel = "operador";
			$acao_tipo = "Solicitação";

			$dados_solicitacao = array(
				"interacao" => $row_solicitacao['interacao'] + 1,
				"situacao" => "reprovada",
				"status" => "",
				"id_operador" => $row_usuario['IdUsuario'],

				"previsao_geral_inicio" => "",
				"previsao_geral" => "",

				"dt_final" => date("Y-m-d H:i:s"),
				"observacao_final" => $_POST['observacao'],

				"previsao_proposta_inicio" => "",
				"previsao_proposta" => "",
				"previsao_proposta_ja_alterada" => "",

				"encaminhamento_data_inicio" => "",
				"encaminhamento_data" => "",
				"id_encaminhamento" => "",

				"status_questionamento" => "",

				"status_devolucao" => "",
				"devolucao_id_usuario" => "",
				"devolucao_motivo" => "",
				"devolucao_data" => "",
				"status_recusa" => "",

				"auto_email_status" => "",
				"auto_email_data" => "",
				"auto_email_solicitacao_descricao" => ""
			);
		}
		// fim - criada

		// analisada
		if ($_GET['situacao'] == "analisada") {
			$responsavel = "analista de orçamento";
			$acao_tipo = "Solicitação";
			if ($row_solicitacao['id_encaminhamento'] == "") {
				$dados_solicitacao = array(
					"interacao" => $row_solicitacao['interacao'] + 1,
					"status" => "pendente operador",
					"status_recusa" => "1"
				);
			} else { // aqui é quando a solicitação vem de um encaminhamento
				$dados_solicitacao = array(
					"interacao" => $row_solicitacao['interacao'] + 1,
					"status" => "pendente operador",
					"status_recusa" => "1"
				);
			}
		}
		// fim - analisada

		// aprovada
		if ($_GET['situacao'] == "aprovada") {
			$responsavel = "executante";
			$acao_tipo = "Solicitação";

			if ($row_solicitacao['id_encaminhamento'] == "") {
				$dados_solicitacao = array(
					"interacao" => $row_solicitacao['interacao'] + 1,
					"status" => "pendente operador", // veio de: encaminhada para executante
					"status_recusa" => "1"
				);
			} else { // aqui é quando a solicitação vem de um encaminhamento
				$dados_solicitacao = array(
					"interacao" => $row_solicitacao['interacao'] + 1,
					"status" => "pendente operador",
					"status_recusa" => "1"
				);
			}
		}
		// fim - aprovada

		// executada
		if ($_GET['situacao'] == "executada") {
			$responsavel = "testador";
			$acao_tipo = "Solicitação";
			if ($row_solicitacao['id_encaminhamento'] == "") { // aqui é quando ela vem após o fim da execução e é recusada pelo testador.
				$dados_solicitacao = array(
					"interacao" => $row_solicitacao['interacao'] + 1,
					"status" => "pendente executante", // veio de: encaminhada para testador
					"status_recusa" => "1"
				);
			} else { // aqui é quando a solicitação vem de um encaminhamento, e o testador que está recebendo recusa
				$dados_solicitacao = array(
					"interacao" => $row_solicitacao['interacao'] + 1,
					"status" => "pendente executante", // veio de: encaminhada para testador
					"status_recusa" => "1"
				);
			}
		}
		// fim - executada

		// testada
		if ($_GET['situacao'] == "testada") {
			$responsavel = "solicitante";
			$acao_tipo = "Solicitação";

			$dados_solicitacao = array(
				"interacao" => $row_solicitacao['interacao'] + 1,
				"status" => "pendente testador", 
				"status_recusa" => "1"
			);

		}
		// fim - testada

		// devolução
		if ($row_solicitacao['status_devolucao'] != "") {
			$dados_solicitacao = array(
				"interacao" => $row_solicitacao['interacao'] + 1,
				"status" => $row_solicitacao['status_devolucao'],
				"status_devolucao" => "",
				"devolucao_id_usuario" => "",
				"devolucao_motivo" => "",
				"devolucao_data" => "",
				"status_recusa" => ""
			);

			$solicitacao_tipo = "Devolução";
			if ($row_solicitacao['status'] == "devolvida para operador") {
				$responsavel = "operador";
			}
			if ($row_solicitacao['status'] == "devolvida para executante") {
				$responsavel = "executante";
			}
			if ($row_solicitacao['status'] == "devolvida para testador") {
				$responsavel = "testador";
			}
		}
		// fim - devolução

		$dados_solicitacao_descricao = array(
			"id_solicitacao" => $row_solicitacao['id'],
			"id_usuario_responsavel" => $row_usuario['IdUsuario'],
			"descricao" => "Motivo: " . $_POST['observacao'],
			"data" => date("Y-m-d H:i:s"),
			"tipo_postagem" => "$solicitacao_tipo recusada por $responsavel"
		);

		funcao_solicitacao_update($row_solicitacao['id'], $dados_solicitacao, $dados_solicitacao_descricao);

		email_solicitacao($row_solicitacao['id'], $dados_solicitacao_descricao['tipo_postagem'], $dados_solicitacao_descricao['descricao']);
		$dados_solicitacao = array("interacao" => $row_solicitacao['interacao'] + 1,);
		$dados_solicitacao_descricao = array();
		funcao_solicitacao_redireciona();
		exit;
	}
	// fim - recusar -----------------------------------------------------------------------------------------------------

	// recusar recusa -----------------------------------------------------------------------------------------------------
	// OBS: status_recusa vazio ' ' quando a recusa 'analista de orçamento', executante e testador é recusada pelo operador
	if ($_GET['acao'] == "Recusar" and $_GET["resposta"] == "" and $row_solicitacao['status_recusa'] == "1") {

		// analisada
		if ($_GET['situacao'] == "analisada") {
			if ($row_solicitacao['id_encaminhamento'] == "") {
				$dados_solicitacao = array(
					"interacao" => $row_solicitacao['interacao'] + 1,
					"status" => "encaminhada para analista",
					"status_recusa" => ""
				);
			} else { // aqui é quando a solicitação vem de um encaminhamento
				$dados_solicitacao = array(
					"interacao" => $row_solicitacao['interacao'] + 1,
					"status" => "pendente executante",
					"status_recusa" => ""
				);
			}
		}
		// fim - analisada

		// aprovada
		if ($_GET['situacao'] == "aprovada") {
			if ($row_solicitacao['id_encaminhamento'] == "") {
				$dados_solicitacao = array(
					"interacao" => $row_solicitacao['interacao'] + 1,
					"status" => "encaminhada para executante",
					"status_recusa" => ""
				);
			} else { // aqui é quando a solicitação vem de um encaminhamento
				$dados_solicitacao = array(
					"interacao" => $row_solicitacao['interacao'] + 1,
					"status" => "pendente executante",
					"status_recusa" => ""
				);
			}
		}
		// fim - aprovada

		// executada
		if ($_GET['situacao'] == "executada") {
			if ($row_solicitacao['id_encaminhamento'] == "") {
				$dados_solicitacao = array(
					"interacao" => $row_solicitacao['interacao'] + 1,
					"status" => "encaminhada para testador",
					"status_recusa" => ""
				);
			} else { // aqui é quando a solicitação vem de um encaminhamento
				$dados_solicitacao = array(
					"interacao" => $row_solicitacao['interacao'] + 1,
					"status" => "encaminhada para testador",
					"status_recusa" => ""
				);
			}
		}
		// fim - executada

		// testada
		if ($_GET['situacao'] == "testada") {
			$dados_solicitacao = array(
				"interacao" => $row_solicitacao['interacao'] + 1,
				"status" => "encaminhada para solicitante",
				"status_recusa" => ""
			);
		}
		// fim - testada

		$dados_solicitacao_descricao = array(
			"id_solicitacao" => $row_solicitacao['id'],
			"id_usuario_responsavel" => $row_usuario['IdUsuario'],
			"descricao" => "Motivo: " . $_POST['observacao'],
			"data" => date("Y-m-d H:i:s"),
			"tipo_postagem" => "Recusa negada"
		);

		funcao_solicitacao_update($row_solicitacao['id'], $dados_solicitacao, $dados_solicitacao_descricao);

		email_solicitacao($row_solicitacao['id'], $dados_solicitacao_descricao['tipo_postagem'], $dados_solicitacao_descricao['descricao']);
		$dados_solicitacao = array("interacao" => $row_solicitacao['interacao'] + 1,);
		$dados_solicitacao_descricao = array();
		funcao_solicitacao_redireciona();
		exit;
	}
	// fim - recusar recusa -----------------------------------------------------------------------------------------------
	//---------------------------------------------------------------------------------------------------------------------------------------------------------------



	//---------------------------------------------------------------------------------------------------------------------------------------------------------------
	// ALTERAR PREVISÃO ---------------------------------------------------------------------------------------------------------------------------------------------
	//---------------------------------------------------------------------------------------------------------------------------------------------------------------
	if (($_GET['situacao'] == "geral" and $_GET['acao'] == "Alterar previsão") and $_GET["resposta"] == "") {

		if ($row_solicitacao['situacao'] == "em validação") {

			// em validação
			$dados_solicitacao = array(
				"interacao" => $row_solicitacao['interacao'] + 1,
				"status" => "pendente solicitante",

				"previsao_validacao_inicio" => $_POST['data_inicio'],
				"previsao_validacao" => $_POST['data'],

				"previsao_geral_inicio" => $_POST['data_inicio'],
				"previsao_geral" => $_POST['data'],

				"previsao_proposta_inicio" => "",
				"previsao_proposta" => "",

				"encaminhamento_data_inicio" => "",
				"encaminhamento_data" => "",
				"id_encaminhamento" => ""
			);
			$tipo_postagem = "Alteração de previsão";
			// fim - em validação

		} else if ($row_solicitacao['situacao'] == "em orçamento" and $row_solicitacao['orcamento'] != "") {

			// em orçamento com o valor já preenchido
			$dados_solicitacao = array(
				"interacao" => $row_solicitacao['interacao'] + 1,
				"status" => "pendente solicitante",

				"previsao_retorno_orcamento_inicio" => $_POST['data_inicio'],
				"previsao_retorno_orcamento" => $_POST['data'],

				"previsao_geral_inicio" => $_POST['data_inicio'],
				"previsao_geral" => $_POST['data'],

				"previsao_proposta_inicio" => "",
				"previsao_proposta" => "",

				"encaminhamento_data_inicio" => "",
				"encaminhamento_data" => "",
				"id_encaminhamento" => ""
			);
			$tipo_postagem = "Alteração de previsão";
			// fim - em orçamento com o valor já preenchido

		} else {

			// outros casos
			$dados_solicitacao = array(
				"interacao" => $row_solicitacao['interacao'] + 1,
				"status" => "pendente solicitante",

				"previsao_proposta_inicio" => $_POST['data_inicio'],
				"previsao_proposta" => $_POST['data'],

				"previsao_geral_inicio" => date('Y-m-d H:i:s'),
				"previsao_geral" =>  date('Y-m-d H:i:s')
			);
			$tipo_postagem = "Solicitação de alteração de previsão";
			// fim - outros casos

		}

		$dados_solicitacao_descricao = array(
			"id_solicitacao" => $row_solicitacao['id'],
			"id_usuario_responsavel" => $row_usuario['IdUsuario'],
			"descricao" => "Solicitação de alteração da previsão para " . formataDataPTG($_POST['data_inicio']) . " à " . formataDataPTG($_POST['data']) . "<br>" . $_POST['observacao'],
			"data" => date("Y-m-d H:i:s"),
			"tipo_postagem" => $tipo_postagem
		);

		funcao_solicitacao_update($row_solicitacao['id'], $dados_solicitacao, $dados_solicitacao_descricao);

		email_solicitacao($row_solicitacao['id'], $dados_solicitacao_descricao['tipo_postagem'], $dados_solicitacao_descricao['descricao']);
		$dados_solicitacao = array("interacao" => $row_solicitacao['interacao'] + 1,);
		$dados_solicitacao_descricao = array();
		funcao_solicitacao_redireciona();
		exit;
	}
	//---------------------------------------------------------------------------------------------------------------------------------------------------------------



	//---------------------------------------------------------------------------------------------------------------------------------------------------------------
	// ALTERAR PREVISÃO - SIM ---------------------------------------------------------------------------------------------------------------------------------------
	//---------------------------------------------------------------------------------------------------------------------------------------------------------------
	if ($_GET['acao'] == "Alterar previsão" and $_GET["resposta"] == "sim") {

		$previsao_tipo = "";

		if ($_GET['situacao'] == "em análise") {
			$previsao_tipo = "Previsão de análise";
			$dados_solicitacao = array(
				"interacao" => $row_solicitacao['interacao'] + 1,
				"status" => "pendente operador",

				"previsao_analise_inicio" => $row_solicitacao['previsao_proposta_inicio'],
				"previsao_analise" => $row_solicitacao['previsao_proposta'],

				"previsao_geral_inicio" => $row_solicitacao['previsao_proposta_inicio'],
				"previsao_geral" => $row_solicitacao['previsao_proposta'],

				"previsao_proposta_inicio" => "",
				"previsao_proposta" => "",
				"previsao_proposta_ja_alterada" => "s",

				"alterar_previsao_operador" => $row_solicitacao['alterar_previsao_operador'] + 1,

				"encaminhamento_data_inicio" => "",
				"encaminhamento_data" => "",
				"id_encaminhamento" => ""
			);
		}
		if ($_GET['situacao'] == "em orçamento") {
			$previsao_tipo = "Previsão de análise de orçamento";
			$dados_solicitacao = array(
				"interacao" => $row_solicitacao['interacao'] + 1,
				"status" => "pendente executante",

				"previsao_analise_orcamento_inicio" => $row_solicitacao['previsao_proposta_inicio'],
				"previsao_analise_orcamento" => $row_solicitacao['previsao_proposta'],

				"previsao_geral_inicio" => $row_solicitacao['previsao_proposta_inicio'],
				"previsao_geral" => $row_solicitacao['previsao_proposta'],

				"previsao_proposta_inicio" => "",
				"previsao_proposta" => "",
				"previsao_proposta_ja_alterada" => "s",

				"alterar_previsao_executante" => $row_solicitacao['alterar_previsao_executante'] + 1,

				"encaminhamento_data_inicio" => "",
				"encaminhamento_data" => "",
				"id_encaminhamento" => ""
			);
		}
		if ($_GET['situacao'] == "em execução") {
			$previsao_tipo = "Previsão de execução";
			$dados_solicitacao = array(
				"interacao" => $row_solicitacao['interacao'] + 1,
				"status" => "pendente executante",

				"previsao_solucao_inicio" => $row_solicitacao['previsao_proposta_inicio'],
				"previsao_solucao" => $row_solicitacao['previsao_proposta'],

				"previsao_geral_inicio" => $row_solicitacao['previsao_proposta_inicio'],
				"previsao_geral" => $row_solicitacao['previsao_proposta'],

				"previsao_proposta_inicio" => "",
				"previsao_proposta" => "",
				"previsao_proposta_ja_alterada" => "s",

				"alterar_previsao_executante" => $row_solicitacao['alterar_previsao_executante'] + 1,

				"encaminhamento_data_inicio" => "",
				"encaminhamento_data" => "",
				"id_encaminhamento" => ""
			);
		}
		if ($_GET['situacao'] == "em testes") {
			$previsao_tipo = "Previsão de teste";
			$dados_solicitacao = array(
				"interacao" => $row_solicitacao['interacao'] + 1,
				"status" => "pendente testador",

				"previsao_testes_inicio" => $row_solicitacao['previsao_proposta_inicio'],
				"previsao_testes" => $row_solicitacao['previsao_proposta'],

				"previsao_geral_inicio" => $row_solicitacao['previsao_proposta_inicio'],
				"previsao_geral" => $row_solicitacao['previsao_proposta'],

				"previsao_proposta_inicio" => "",
				"previsao_proposta" => "",
				"previsao_proposta_ja_alterada" => "s",

				"alterar_previsao_testador" => $row_solicitacao['alterar_previsao_testador'] + 1,

				"encaminhamento_data_inicio" => "",
				"encaminhamento_data" => "",

				"id_encaminhamento" => ""
			);
		}

		$dados_solicitacao_descricao = array(
			"id_solicitacao" => $row_solicitacao['id'],
			"id_usuario_responsavel" => $row_usuario['IdUsuario'],
			"descricao" => "$previsao_tipo alterada para " . formataDataPTG($row_solicitacao['previsao_proposta_inicio']) . " à " . formataDataPTG($row_solicitacao['previsao_proposta']),
			"data" => date("Y-m-d H:i:s"),
			"tipo_postagem" => "Alteração da $previsao_tipo aceita"
		);

		funcao_solicitacao_update($row_solicitacao['id'], $dados_solicitacao, $dados_solicitacao_descricao);

		email_solicitacao($row_solicitacao['id'], $dados_solicitacao_descricao['tipo_postagem'], $dados_solicitacao_descricao['descricao']);
		$dados_solicitacao = array("interacao" => $row_solicitacao['interacao'] + 1,);
		$dados_solicitacao_descricao = array();
		funcao_solicitacao_redireciona();
		exit;
	}
	//---------------------------------------------------------------------------------------------------------------------------------------------------------------



	//---------------------------------------------------------------------------------------------------------------------------------------------------------------
	// ALTERAR PREVISÃO - NAO ---------------------------------------------------------------------------------------------------------------------------------------
	//---------------------------------------------------------------------------------------------------------------------------------------------------------------
	if ($_GET['acao'] == "Alterar previsão" and $_GET["resposta"] == "nao") {

		$previsao_tipo = "";

		if ($_GET['situacao'] == "em análise") {
			$previsao_tipo = "Previsão de análise";
			$dados_solicitacao = array(
				"interacao" => $row_solicitacao['interacao'] + 1,
				"status" => "pendente operador",

				"previsao_geral_inicio" => $row_solicitacao['previsao_analise_inicio'],
				"previsao_geral" => $row_solicitacao['previsao_analise'],

				"previsao_proposta_inicio" => "",
				"previsao_proposta" => "",
				"previsao_proposta_ja_alterada" => "s",

				"encaminhamento_data_inicio" => "",
				"encaminhamento_data" => "",
				"id_encaminhamento" => ""
			);
		}
		if ($_GET['situacao'] == "em orçamento") {
			$previsao_tipo = "Previsão de análise de orçamento";
			$dados_solicitacao = array(
				"interacao" => $row_solicitacao['interacao'] + 1,
				"status" => "pendente executante",

				"previsao_geral_inicio" => $row_solicitacao['previsao_analise_orcamento_inicio'],
				"previsao_geral" => $row_solicitacao['previsao_analise_orcamento'],

				"previsao_proposta_inicio" => "",
				"previsao_proposta" => "",
				"previsao_proposta_ja_alterada" => "s",

				"encaminhamento_data_inicio" => "",
				"encaminhamento_data" => "",
				"id_encaminhamento" => ""
			);
		}
		if ($_GET['situacao'] == "em execução") {
			$previsao_tipo = "Previsão de execução";
			$dados_solicitacao = array(
				"interacao" => $row_solicitacao['interacao'] + 1,
				"status" => "pendente executante",

				"previsao_geral_inicio" => $row_solicitacao['previsao_solucao_inicio'],
				"previsao_geral" => $row_solicitacao['previsao_solucao'],

				"previsao_proposta_inicio" => "",
				"previsao_proposta" => "",
				"previsao_proposta_ja_alterada" => "s",

				"encaminhamento_data_inicio" => "",
				"encaminhamento_data" => "",
				"id_encaminhamento" => ""
			);
		}
		if ($_GET['situacao'] == "em testes") {
			$previsao_tipo = "Previsão de teste";
			$dados_solicitacao = array(
				"interacao" => $row_solicitacao['interacao'] + 1,
				"status" => "pendente testador",

				"previsao_geral_inicio" => $row_solicitacao['previsao_testes_inicio'],
				"previsao_geral" => $row_solicitacao['previsao_testes'],

				"previsao_proposta_inicio" => "",
				"previsao_proposta" => "",
				"previsao_proposta_ja_alterada" => "s",

				"encaminhamento_data_inicio" => "",
				"encaminhamento_data" => "",
				"id_encaminhamento" => ""
			);
		}

		$dados_solicitacao_descricao = array(
			"id_solicitacao" => $row_solicitacao['id'],
			"id_usuario_responsavel" => $row_usuario['IdUsuario'],
			"descricao" => $_POST['observacao'],
			"data" => date("Y-m-d H:i:s"),
			"tipo_postagem" => "Alteração da $previsao_tipo não aceita"
		);

		funcao_solicitacao_update($row_solicitacao['id'], $dados_solicitacao, $dados_solicitacao_descricao);

		email_solicitacao($row_solicitacao['id'], $dados_solicitacao_descricao['tipo_postagem'], $dados_solicitacao_descricao['descricao']);
		$dados_solicitacao = array("interacao" => $row_solicitacao['interacao'] + 1,);
		$dados_solicitacao_descricao = array();
		funcao_solicitacao_redireciona();
		exit;
	}
	//---------------------------------------------------------------------------------------------------------------------------------------------------------------



	//---------------------------------------------------------------------------------------------------------------------------------------------------------------
	// APROVAR ------------------------------------------------------------------------------------------------------------------------------------------------------
	//---------------------------------------------------------------------------------------------------------------------------------------------------------------
	// recebida // em análise // analisada // em orçamento
	if (($_GET['situacao'] == "geral" and $_GET['acao'] == "Aprovar") and $_GET["resposta"] == "") {

		$versao_acao = "";
		foreach ($_POST as $versao_acao_campo => $versao_acao_valor) {
			if (strstr($versao_acao_campo, 'versao_acao') and $versao_acao_valor != "") {
				$versao_acao .= $versao_acao_valor . "<br>";
			}
		}

		$dados_solicitacao = array(
			"interacao" => $row_solicitacao['interacao'] + 1,
			"situacao" => "aprovada",
			"status" => "encaminhada para executante",

			"dt_aprovacao_reprovacao" => date("Y-m-d H:i:s"),
			"id_usuario_aprovacao_reprovacao" => $row_usuario['IdUsuario'],
			"observacao_aprovacao_reprovacao" => $_POST['observacao'],

			"previsao_analise_orcamento_inicio" => "0000-00-00 00:00:00",
			"previsao_analise_orcamento" => "0000-00-00 00:00:00",
			"id_analista_orcamento" => "",
			"dt_orcamento" => "",
			"orcamento" => "",
			"prazo_desenvolvimento_orcamento" => "",

			"previsao_solucao_inicio" => "0000-00-00 00:00:00",
			"previsao_solucao" => "0000-00-00 00:00:00",
			"id_executante" => $_POST['executante'],
			"dt_conclusao" => "",

			"previsao_geral_inicio" => date("Y-m-d H:i:s"),
			"previsao_geral" => date("Y-m-d H:i:s"),

			"executante_leu" => "",
			"analista_orcamento_leu" => "",

			"previsao_proposta_inicio" => "",
			"previsao_proposta" => "",
			"previsao_proposta_ja_alterada" => "",

			"encaminhamento_data_inicio" => "",
			"encaminhamento_data" => "",
			"id_encaminhamento" => "",

			"status_devolucao" => "",
			"devolucao_id_usuario" => "",
			"devolucao_motivo" => "",
			"devolucao_data" => "",
			"status_recusa" => ""
		);

		// busca usuario_selecionado
		$colname_usuario_selecionado = "-1";
		if (isset($_POST['executante'])) {
			$colname_usuario_selecionado = $_POST['executante'];
		}
		mysql_select_db($database_conexao, $conexao);
		$query_usuario_selecionado = sprintf("SELECT IdUsuario, nome, praca FROM usuarios WHERE IdUsuario = %s", GetSQLValueString($colname_usuario_selecionado, "int"));
		$usuario_selecionado = mysql_query($query_usuario_selecionado, $conexao) or die(mysql_error());
		$row_usuario_selecionado = mysql_fetch_assoc($usuario_selecionado);
		$totalRows_usuario_selecionado = mysql_num_rows($usuario_selecionado);
		// fim - busca usuario_selecionado

		$dados_solicitacao_descricao = array(
			"id_solicitacao" => $row_solicitacao['id'],
			"id_usuario_responsavel" => $row_usuario['IdUsuario'],
			"descricao" => "Entrega para executante<br>Executante: " . $row_usuario_selecionado['nome'] . "<br>" . $_POST['observacao'] . "<br>Versão que foi realizada ação/tratamento: <br>" . $versao_acao,
			"data" => date("Y-m-d H:i:s"),
			"tipo_postagem" => "Aprovação"
		);

		mysql_free_result($usuario_selecionado);

		// insere - tempo gasto	
		tempo_gasto($row_solicitacao['id'], $row_solicitacao['situacao'], $_GET['acao'], $row_usuario['IdUsuario'], 'operador', $_POST['tempo_gasto']);
		// fim - insere - tempo gasto

		funcao_solicitacao_update($row_solicitacao['id'], $dados_solicitacao, $dados_solicitacao_descricao);

		email_solicitacao($row_solicitacao['id'], $dados_solicitacao_descricao['tipo_postagem'], $dados_solicitacao_descricao['descricao']);
		$dados_solicitacao = array("interacao" => $row_solicitacao['interacao'] + 1,);
		$dados_solicitacao_descricao = array();
		funcao_solicitacao_redireciona();
		exit;
	}
	//---------------------------------------------------------------------------------------------------------------------------------------------------------------



	//---------------------------------------------------------------------------------------------------------------------------------------------------------------
	// POSTAR ORÇAMENTO ---------------------------------------------------------------------------------------------------------------------------------------------
	//---------------------------------------------------------------------------------------------------------------------------------------------------------------
	// recebida // em análise // aprovada
	if (($_GET['situacao'] == "geral" and $_GET['acao'] == "Postar orçamento") and $_GET["resposta"] == "") {

		$versao_acao = "";
		foreach ($_POST as $versao_acao_campo => $versao_acao_valor) {
			if (strstr($versao_acao_campo, 'versao_acao') and $versao_acao_valor != "") {
				$versao_acao .= $versao_acao_valor . "<br>";
			}
		}

		$dados_solicitacao = array(
			"interacao" => $row_solicitacao['interacao'] + 1,
			"situacao" => "em orçamento",
			"status" => "pendente solicitante",

			"id_analista_orcamento" => $row_usuario['IdUsuario'],
			"dt_orcamento" => date("Y-m-d H:i:s"),
			"orcamento" => $_POST['orcamento'],
			"prazo_desenvolvimento_orcamento" => $_POST['prazo_desenvolvimento_orcamento'],

			"previsao_retorno_orcamento_inicio" => "0000-00-00 00:00:00",
			"previsao_retorno_orcamento" => "0000-00-00 00:00:00",

			"previsao_solucao_inicio" => "0000-00-00 00:00:00",
			"previsao_solucao" => "0000-00-00 00:00:00",
			"id_executante" => "",
			"dt_conclusao" => "",

			"previsao_testes_inicio" => "0000-00-00 00:00:00",
			"previsao_testes" => "0000-00-00 00:00:00",
			"id_testador" => "",
			"dt_conclusao_testes" => "",
			"observacao_testes" => "",

			"previsao_validacao_inicio" => "0000-00-00 00:00:00",
			"previsao_validacao" => "0000-00-00 00:00:00",
			"dt_validacao" => "",
			"observacao_validacao" => "",

			"previsao_geral_inicio" => date("Y-m-d H:i:s"),
			"previsao_geral" => date("Y-m-d H:i:s"),

			"executante_leu" => "",
			"testador_leu" => "",

			"dt_final" => "",
			"observacao_final" => "",

			"previsao_proposta_inicio" => "",
			"previsao_proposta" => "",
			"previsao_proposta_ja_alterada" => "",

			"encaminhamento_data_inicio" => "",
			"encaminhamento_data" => "",
			"id_encaminhamento" => "",

			"status_devolucao" => "",
			"devolucao_id_usuario" => "",
			"devolucao_motivo" => "",
			"devolucao_data" => "",
			"status_recusa" => ""
		);
		$dados_solicitacao_descricao = array(
			"id_solicitacao" => $row_solicitacao['id'],
			"id_usuario_responsavel" => $row_usuario['IdUsuario'],
			"descricao" => "Valor: R$ " . number_format($_POST['orcamento'], 2, ',', '.') . " | Prazo para desenvolvimento após a aprovação do cliente: " . $_POST['prazo_desenvolvimento_orcamento'] . " dias<br>" . $_POST['observacao'] . "<br>Versão que foi realizada ação/tratamento: <br>" . $versao_acao,
			"data" => date("Y-m-d H:i:s"),
			"tipo_postagem" => "Novo orçamento postado"
		);

		// insere - tempo gasto
		tempo_gasto($row_solicitacao['id'], $row_solicitacao['situacao'], $_GET['acao'], $row_usuario['IdUsuario'], 'executante', $_POST['tempo_gasto']);
		// fim - insere - tempo gasto	

		funcao_solicitacao_update($row_solicitacao['id'], $dados_solicitacao, $dados_solicitacao_descricao);

		email_solicitacao($row_solicitacao['id'], $dados_solicitacao_descricao['tipo_postagem'], $dados_solicitacao_descricao['descricao']);
		$dados_solicitacao = array("interacao" => $row_solicitacao['interacao'] + 1,);
		$dados_solicitacao_descricao = array();
		funcao_solicitacao_redireciona();
		exit;
	}
	//---------------------------------------------------------------------------------------------------------------------------------------------------------------



	//---------------------------------------------------------------------------------------------------------------------------------------------------------------
	// SOLICITAR ORÇAMENTO ------------------------------------------------------------------------------------------------------------------------------------------
	//---------------------------------------------------------------------------------------------------------------------------------------------------------------
	if (($_GET['situacao'] == "geral" and $_GET['acao'] == "Solicitar orçamento") and $_GET["resposta"] == "") {

		$versao_acao = "";
		foreach ($_POST as $versao_acao_campo => $versao_acao_valor) {
			if (strstr($versao_acao_campo, 'versao_acao') and $versao_acao_valor != "") {
				$versao_acao .= $versao_acao_valor . "<br>";
			}
		}

		$dados_solicitacao = array(
			"interacao" => $row_solicitacao['interacao'] + 1,
			"situacao" => "analisada",
			"status" => "encaminhada para analista",

			"dt_aprovacao_reprovacao" => "",
			"id_usuario_aprovacao_reprovacao" => "",
			"observacao_aprovacao_reprovacao" => "",

			"previsao_analise_orcamento_inicio" => "0000-00-00 00:00:00",
			"previsao_analise_orcamento" => "0000-00-00 00:00:00",
			"id_analista_orcamento" => $_POST['executante'],
			"dt_orcamento" => "",
			"orcamento" => "",
			"prazo_desenvolvimento_orcamento" => "",
			"orcamento_os" => "",

			"previsao_retorno_orcamento_inicio" => "0000-00-00 00:00:00",
			"previsao_retorno_orcamento" => "0000-00-00 00:00:00",

			"previsao_solucao_inicio" => "0000-00-00 00:00:00",
			"previsao_solucao" => "0000-00-00 00:00:00",
			"id_executante" => "",
			"dt_conclusao" => "",

			"previsao_testes_inicio" => "0000-00-00 00:00:00",
			"previsao_testes" => "0000-00-00 00:00:00",
			"id_testador" => "",
			"dt_conclusao_testes" => "",
			"observacao_testes" => "",

			"previsao_validacao_inicio" => "0000-00-00 00:00:00",
			"previsao_validacao" => "0000-00-00 00:00:00",
			"dt_validacao" => "",
			"observacao_validacao" => "",

			"previsao_geral_inicio" => date("Y-m-d H:i:s"),
			"previsao_geral" => date("Y-m-d H:i:s"),

			"analista_orcamento_leu" => "",
			"executante_leu" => "",
			"testador_leu" => "",

			"dt_final" => "",
			"observacao_final" => "",

			"previsao_proposta_inicio" => "",
			"previsao_proposta" => "",
			"previsao_proposta_ja_alterada" => "",

			"encaminhamento_data_inicio" => "",
			"encaminhamento_data" => "",
			"id_encaminhamento" => "",

			"status_devolucao" => "",
			"devolucao_id_usuario" => "",
			"devolucao_motivo" => "",
			"devolucao_data" => "",
			"status_recusa" => ""
		);

		// busca usuario_selecionado
		$colname_usuario_selecionado = "-1";
		if (isset($_POST['executante'])) {
			$colname_usuario_selecionado = $_POST['executante'];
		}
		mysql_select_db($database_conexao, $conexao);
		$query_usuario_selecionado = sprintf("SELECT IdUsuario, nome, praca FROM usuarios WHERE IdUsuario = %s", GetSQLValueString($colname_usuario_selecionado, "int"));
		$usuario_selecionado = mysql_query($query_usuario_selecionado, $conexao) or die(mysql_error());
		$row_usuario_selecionado = mysql_fetch_assoc($usuario_selecionado);
		$totalRows_usuario_selecionado = mysql_num_rows($usuario_selecionado);
		// fim - busca usuario_selecionado

		$dados_solicitacao_descricao = array(
			"id_solicitacao" => $row_solicitacao['id'],
			"id_usuario_responsavel" => $row_usuario['IdUsuario'],
			"descricao" => "Encaminhada para analista de orçamento<br>Analista: " . $row_usuario_selecionado['nome'] . "<br>" . $_POST['observacao'] . "<br>Versão que foi realizada ação/tratamento: <br>" . $versao_acao,
			"data" => date("Y-m-d H:i:s"),
			"tipo_postagem" => "Escolha do analista de orçamento"
		);

		mysql_free_result($usuario_selecionado);

		// insere - tempo gasto	
		tempo_gasto($row_solicitacao['id'], $row_solicitacao['situacao'], $_GET['acao'], $row_usuario['IdUsuario'], 'operador', $_POST['tempo_gasto']);
		// fim - insere - tempo gasto	

		funcao_solicitacao_update($row_solicitacao['id'], $dados_solicitacao, $dados_solicitacao_descricao);

		email_solicitacao($row_solicitacao['id'], $dados_solicitacao_descricao['tipo_postagem'], $dados_solicitacao_descricao['descricao']);
		$dados_solicitacao = array("interacao" => $row_solicitacao['interacao'] + 1,);
		$dados_solicitacao_descricao = array();
		funcao_solicitacao_redireciona();
		exit;
	}
	//---------------------------------------------------------------------------------------------------------------------------------------------------------------



	//---------------------------------------------------------------------------------------------------------------------------------------------------------------
	// CONCLUIR -----------------------------------------------------------------------------------------------------------------------------------------------------
	//---------------------------------------------------------------------------------------------------------------------------------------------------------------
	// geral
	if (($_GET['situacao'] == "geral" and $_GET['acao'] == "Concluir") and $_GET["resposta"] == "") {

		$versao_acao = "";
		foreach ($_POST as $versao_acao_campo => $versao_acao_valor) {
			if (strstr($versao_acao_campo, 'versao_acao') and $versao_acao_valor != "") {
				$versao_acao .= $versao_acao_valor . "<br>";
			}
		}

		// com validação do solicitante
		if ($_POST['confirmacao_concluir_reprovar'] == "sim") {

			$dados_solicitacao = array(
				"interacao" => $row_solicitacao['interacao'] + 1,
				"acao" => $_GET['acao'],

				"situacao" => "em validação",
				"status" => "pendente solicitante",

				"previsao_validacao_inicio" => date('Y-m-d H:i:s'),
				"previsao_validacao" =>  date('Y-m-d H:i:s'),
				"dt_validacao" => "",
				"observacao_validacao" => "",

				"previsao_geral_inicio" => date('Y-m-d H:i:s'),
				"previsao_geral" =>  date('Y-m-d H:i:s'),

				"previsao_proposta_inicio" => "",
				"previsao_proposta" => "",
				"previsao_proposta_ja_alterada" => "",

				"encaminhamento_data_inicio" => "",
				"encaminhamento_data" => "",
				"id_encaminhamento" => "",

				"status_devolucao" => "",
				"devolucao_id_usuario" => "",
				"devolucao_motivo" => "",
				"devolucao_data" => "",
				"status_recusa" => ""
			);
			$dados_solicitacao_descricao = array(
				"id_solicitacao" => $row_solicitacao['id'],
				"id_usuario_responsavel" => $row_usuario['IdUsuario'],
				"descricao" => "Entrega para solicitante validar conclusão.<br>" . $_POST['observacao'] . "<br>Versão que foi realizada ação/tratamento: <br>" . $versao_acao,
				"data" => date("Y-m-d H:i:s"),
				"tipo_postagem" => "Conclusão de solicitação"
			);
		}
		// fim - com validação do solicitante

		// sem validação do solicitante
		if ($_POST['confirmacao_concluir_reprovar'] == "nao") {

			$dados_solicitacao = array(
				"interacao" => $row_solicitacao['interacao'] + 1,
				"situacao" => "solucionada",
				"status" => "",

				"dt_validacao" => date("Y-m-d H:i:s"),
				"observacao_validacao" => $_POST['observacao'],

				"previsao_geral_inicio" => "",
				"previsao_geral" => "",

				"dt_final" => date("Y-m-d H:i:s"),
				"observacao_final" => $_POST['observacao'],

				"previsao_proposta_inicio" => "",
				"previsao_proposta" => "",
				"previsao_proposta_ja_alterada" => "",

				"encaminhamento_data_inicio" => "",
				"encaminhamento_data" => "",
				"id_encaminhamento" => "",

				"status_questionamento" => "",

				"status_devolucao" => "",
				"devolucao_id_usuario" => "",
				"devolucao_motivo" => "",
				"devolucao_data" => "",
				"status_recusa" => "",

				"auto_email_status" => "",
				"auto_email_data" => "",
				"auto_email_solicitacao_descricao" => ""
			);

			$dados_solicitacao_descricao = array(
				"id_solicitacao" => $row_solicitacao['id'],
				"id_usuario_responsavel" => $row_usuario['IdUsuario'],
				"descricao" => $_POST['observacao'] . "<br>Versão que foi realizada ação/tratamento: <br>" . $versao_acao,
				"data" => date("Y-m-d H:i:s"),
				"tipo_postagem" => "Solucionada"
			);
		}
		// fim - sem validação do solicitante

		// insere - tempo gasto
		tempo_gasto($row_solicitacao['id'], $row_solicitacao['situacao'], $_GET['acao'], $row_usuario['IdUsuario'], 'solicitante', $_POST['tempo_gasto']);
		// fim - insere - tempo gasto

		funcao_solicitacao_update($row_solicitacao['id'], $dados_solicitacao, $dados_solicitacao_descricao);
		email_solicitacao($row_solicitacao['id'], $dados_solicitacao_descricao['tipo_postagem'], $dados_solicitacao_descricao['descricao']);
		$dados_solicitacao = array("interacao" => $row_solicitacao['interacao'] + 1,);
		$dados_solicitacao_descricao = array();
		funcao_solicitacao_redireciona();
		exit;
	}
	//---------------------------------------------------------------------------------------------------------------------------------------------------------------


	//---------------------------------------------------------------------------------------------------------------------------------------------------------------
	// REPROVAR -----------------------------------------------------------------------------------------------------------------------------------------------------
	//---------------------------------------------------------------------------------------------------------------------------------------------------------------
	// geral
	if (($_GET['situacao'] == "geral" and $_GET['acao'] == "Reprovar") and $_GET["resposta"] == "") {

		// com validação do solicitante
		if ($_POST['confirmacao_concluir_reprovar'] == "sim") {

			// sugestao_atual
			if ($_POST['sugestao'] == "sim") {
				$sugestao_atual = "SIM";
			}
			if ($_POST['sugestao'] == "nao") {
				$sugestao_atual = "NÃO";
			}
			// fim - sugestao_atual

			$dados_solicitacao = array(
				"interacao" => $row_solicitacao['interacao'] + 1,
				"situacao" => "em validação",
				"status" => "pendente solicitante",

				"previsao_validacao_inicio" => date('Y-m-d H:i:s'),
				"previsao_validacao" =>  date('Y-m-d H:i:s'),
				"dt_validacao" => "",
				"observacao_validacao" => "",

				"previsao_geral_inicio" => date('Y-m-d H:i:s'),
				"previsao_geral" =>  date('Y-m-d H:i:s'),

				"previsao_proposta_inicio" => "",
				"previsao_proposta" => "",
				"previsao_proposta_ja_alterada" => "",

				"encaminhamento_data_inicio" => "",
				"encaminhamento_data" => "",
				"id_encaminhamento" => "",

				"acao" => $_GET['acao'],
				"deixar_sugestao" => $_POST['sugestao'],

				"status_devolucao" => "",
				"devolucao_id_usuario" => "",
				"devolucao_motivo" => "",
				"devolucao_data" => "",
				"status_recusa" => ""
			);
			$dados_solicitacao_descricao = array(
				"id_solicitacao" => $row_solicitacao['id'],
				"id_usuario_responsavel" => $row_usuario['IdUsuario'],
				"descricao" => "Entrega para solicitante validar reprovação.<br>Deixar esta solicitação como sugestão: " . $sugestao_atual . "<br>" . $_POST['observacao'],
				"data" => date("Y-m-d H:i:s"),
				"tipo_postagem" => "Reprovação de solicitação"
			);
		}
		// fim - com validação do solicitante

		// sem validação do solicitante
		if ($_POST['confirmacao_concluir_reprovar'] == "nao") {

			// deixar como sugestão (sim)
			if ($_POST['sugestao'] == "sim") {

				$dados_solicitacao = array(
					"interacao" => $row_solicitacao['interacao'] + 1,
					"situacao" => "solucionada",
					"status" => "",
					"tipo" => "Sugestão",

					"previsao_geral_inicio" => "",
					"previsao_geral" => "",

					"dt_final" => date("Y-m-d H:i:s"),
					"observacao_final" => $_POST['observacao'],

					"previsao_proposta_inicio" => "",
					"previsao_proposta" => "",
					"previsao_proposta_ja_alterada" => "",

					"encaminhamento_data_inicio" => "",
					"encaminhamento_data" => "",
					"id_encaminhamento" => "",

					"status_questionamento" => "",
					"status_devolucao" => "",
					"devolucao_id_usuario" => "",
					"devolucao_motivo" => "",
					"devolucao_data" => "",
					"status_recusa" => "",

					"auto_email_status" => "",
					"auto_email_data" => "",
					"auto_email_solicitacao_descricao" => ""
				);
				$dados_solicitacao_descricao = array(
					"id_solicitacao" => $row_solicitacao['id'],
					"id_usuario_responsavel" => $row_usuario['IdUsuario'],
					"descricao" => "Esta solicitação entrará na lista de sugestões.<br>" . $_POST['observacao'],
					"data" => date("Y-m-d H:i:s"),
					"tipo_postagem" => "Reprovação de solicitação"
				);
			}
			// fim - deixar como sugestão (sim)

			// deixar como sugestão (nao)
			if ($_POST['sugestao'] == "nao") {

				$dados_solicitacao = array(
					"interacao" => $row_solicitacao['interacao'] + 1,
					"situacao" => "reprovada",
					"status" => "",
					"id_operador" => $row_usuario['IdUsuario'],

					"previsao_geral_inicio" => "",
					"previsao_geral" => "",

					"dt_final" => date("Y-m-d H:i:s"),
					"observacao_final" => $_POST['observacao'],

					"previsao_proposta_inicio" => "",
					"previsao_proposta" => "",
					"previsao_proposta_ja_alterada" => "",

					"encaminhamento_data_inicio" => "",
					"encaminhamento_data" => "",
					"id_encaminhamento" => "",

					"status_questionamento" => "",
					"status_devolucao" => "",
					"devolucao_id_usuario" => "",
					"devolucao_motivo" => "",
					"devolucao_data" => "",
					"status_recusa" => "",

					"auto_email_status" => "",
					"auto_email_data" => "",
					"auto_email_solicitacao_descricao" => ""
				);
				$dados_solicitacao_descricao = array(
					"id_solicitacao" => $row_solicitacao['id'],
					"id_usuario_responsavel" => $row_usuario['IdUsuario'],
					"descricao" => $_POST['observacao'],
					"data" => date("Y-m-d H:i:s"),
					"tipo_postagem" => "Reprovação de solicitação"
				);
			}
			// fim - deixar como sugestão (nao)

		}
		// fim - sem validação do solicitante

		funcao_solicitacao_update($row_solicitacao['id'], $dados_solicitacao, $dados_solicitacao_descricao);

		email_solicitacao($row_solicitacao['id'], $dados_solicitacao_descricao['tipo_postagem'], $dados_solicitacao_descricao['descricao']);
		$dados_solicitacao = array("interacao" => $row_solicitacao['interacao'] + 1,);
		$dados_solicitacao_descricao = array();
		funcao_solicitacao_redireciona();
		exit;
	}
	//---------------------------------------------------------------------------------------------------------------------------------------------------------------



	//---------------------------------------------------------------------------------------------------------------------------------------------------------------
	// ALTERAR GERAL ---------------------------------------------------------------------------------------------------------------------------------------------
	//---------------------------------------------------------------------------------------------------------------------------------------------------------------
	if ($_GET['situacao'] == "editar") {

		// Alterar solicitante
		if (($_GET['situacao'] == "editar") and $_GET["acao"] == "Alterar solicitante") {

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

			$dados_solicitacao = array(
				"interacao" => $row_solicitacao['interacao'] + 1,
				"id_usuario_responsavel" => $row_usuario_selecionado['IdUsuario'],
				"solicitante_leu" => ""
			);
			$dados_solicitacao_descricao = array(
				"id_solicitacao" => $row_solicitacao['id'],
				"id_usuario_responsavel" => $row_usuario['IdUsuario'],
				"descricao" => "Foi alterado o solicitante<br>Solicitante anterior: " . $row_solicitacao['usuario_responsavel'] . " - Novo solicitante: " . $row_usuario_selecionado['nome'] . "<br>" . $_POST['observacao'],
				"data" => date("Y-m-d H:i:s"),
				"tipo_postagem" => "Alteração de solicitante"
			);

			mysql_free_result($usuario_selecionado);

			funcao_solicitacao_update($row_solicitacao['id'], $dados_solicitacao, $dados_solicitacao_descricao);
			email_solicitacao($row_solicitacao['id'], $dados_solicitacao_descricao['tipo_postagem'], $dados_solicitacao_descricao['descricao']);
			$dados_solicitacao = array("interacao" => $row_solicitacao['interacao'] + 1,);
			$dados_solicitacao_descricao = array();
			funcao_solicitacao_redireciona();
			exit;
		}
		// fim - Alterar solicitante
		//---------------------------------------------------------------------------------------------------------------------------------------------------------------
		// Alterar operador
		if (($_GET['situacao'] == "editar") and $_GET["acao"] == "Alterar operador") {

			// busca usuario_selecionado
			$colname_usuario_selecionado = "-1";
			if (isset($_POST['operador'])) {
				$colname_usuario_selecionado = $_POST['operador'];
			}
			mysql_select_db($database_conexao, $conexao);
			$query_usuario_selecionado = sprintf("SELECT IdUsuario, nome, praca FROM usuarios WHERE IdUsuario = %s", GetSQLValueString($colname_usuario_selecionado, "int"));
			$usuario_selecionado = mysql_query($query_usuario_selecionado, $conexao) or die(mysql_error());
			$row_usuario_selecionado = mysql_fetch_assoc($usuario_selecionado);
			$totalRows_usuario_selecionado = mysql_num_rows($usuario_selecionado);
			// fim - busca usuario_selecionado

			$dados_solicitacao = array(
				"interacao" => $row_solicitacao['interacao'] + 1,
				"id_operador" => $row_usuario_selecionado['IdUsuario'],
				"operador_leu" => ""
			);
			$dados_solicitacao_descricao = array(
				"id_solicitacao" => $row_solicitacao['id'],
				"id_usuario_responsavel" => $row_usuario['IdUsuario'],
				"descricao" => "Foi alterado o operador<br>Operador anterior: " . $row_solicitacao['nome_operador'] . " - Novo operador: " . $row_usuario_selecionado['nome'] . "<br>" . $_POST['observacao'],
				"data" => date("Y-m-d H:i:s"),
				"tipo_postagem" => "Alteração de operador"
			);

			mysql_free_result($usuario_selecionado);

			funcao_solicitacao_update($row_solicitacao['id'], $dados_solicitacao, $dados_solicitacao_descricao);
			email_solicitacao($row_solicitacao['id'], $dados_solicitacao_descricao['tipo_postagem'], $dados_solicitacao_descricao['descricao']);
			$dados_solicitacao = array("interacao" => $row_solicitacao['interacao'] + 1,);
			$dados_solicitacao_descricao = array();
			funcao_solicitacao_redireciona();
			exit;
		}
		// fim - Alterar operador
		//---------------------------------------------------------------------------------------------------------------------------------------------------------------
		// Alterar analista de orçamento
		if (($_GET['situacao'] == "editar") and $_GET["acao"] == "Alterar analista de orçamento") {

			// busca usuario_selecionado
			$colname_usuario_selecionado = "-1";
			if (isset($_POST['analista_orcamento'])) {
				$colname_usuario_selecionado = $_POST['analista_orcamento'];
			}
			mysql_select_db($database_conexao, $conexao);
			$query_usuario_selecionado = sprintf("SELECT IdUsuario, nome, praca FROM usuarios WHERE IdUsuario = %s", GetSQLValueString($colname_usuario_selecionado, "int"));
			$usuario_selecionado = mysql_query($query_usuario_selecionado, $conexao) or die(mysql_error());
			$row_usuario_selecionado = mysql_fetch_assoc($usuario_selecionado);
			$totalRows_usuario_selecionado = mysql_num_rows($usuario_selecionado);
			// fim - busca usuario_selecionado

			$dados_solicitacao = array(
				"interacao" => $row_solicitacao['interacao'] + 1,
				"id_analista_orcamento" => $row_usuario_selecionado['IdUsuario'],
				"analista_orcamento_leu" => ""
			);
			$dados_solicitacao_descricao = array(
				"id_solicitacao" => $row_solicitacao['id'],
				"id_usuario_responsavel" => $row_usuario['IdUsuario'],
				"descricao" => "Foi alterado o analista de orçamento<br>Analista de orçamento anterior: " . $row_solicitacao['nome_analista_orcamento'] . " - Novo analista de orçamento: " . $row_usuario_selecionado['nome'] . "<br>" . $_POST['observacao'],
				"data" => date("Y-m-d H:i:s"),
				"tipo_postagem" => "Alteração de analista de orçamento"
			);

			mysql_free_result($usuario_selecionado);

			funcao_solicitacao_update($row_solicitacao['id'], $dados_solicitacao, $dados_solicitacao_descricao);
			email_solicitacao($row_solicitacao['id'], $dados_solicitacao_descricao['tipo_postagem'], $dados_solicitacao_descricao['descricao']);
			$dados_solicitacao = array("interacao" => $row_solicitacao['interacao'] + 1,);
			$dados_solicitacao_descricao = array();
			funcao_solicitacao_redireciona();
			exit;
		}
		// fim - Alterar analista de orçamento
		//---------------------------------------------------------------------------------------------------------------------------------------------------------------
		// Alterar executante
		if (($_GET['situacao'] == "editar") and $_GET["acao"] == "Alterar executante") {

			// busca usuario_selecionado
			$colname_usuario_selecionado = "-1";
			if (isset($_POST['executante'])) {
				$colname_usuario_selecionado = $_POST['executante'];
			}
			mysql_select_db($database_conexao, $conexao);
			$query_usuario_selecionado = sprintf("SELECT IdUsuario, nome, praca FROM usuarios WHERE IdUsuario = %s", GetSQLValueString($colname_usuario_selecionado, "int"));
			$usuario_selecionado = mysql_query($query_usuario_selecionado, $conexao) or die(mysql_error());
			$row_usuario_selecionado = mysql_fetch_assoc($usuario_selecionado);
			$totalRows_usuario_selecionado = mysql_num_rows($usuario_selecionado);
			// fim - busca usuario_selecionado

			$dados_solicitacao = array(
				"interacao" => $row_solicitacao['interacao'] + 1,
				"id_executante" => $row_usuario_selecionado['IdUsuario'],
				"executante_leu" => ""
			);
			$dados_solicitacao_descricao = array(
				"id_solicitacao" => $row_solicitacao['id'],
				"id_usuario_responsavel" => $row_usuario['IdUsuario'],
				"descricao" => "Foi alterado o executante<br>Executante anterior: " . $row_solicitacao['nome_executante'] . " - Novo executante: " . $row_usuario_selecionado['nome'] . "<br>" . $_POST['observacao'],
				"data" => date("Y-m-d H:i:s"),
				"tipo_postagem" => "Alteração de executante"
			);

			mysql_free_result($usuario_selecionado);

			funcao_solicitacao_update($row_solicitacao['id'], $dados_solicitacao, $dados_solicitacao_descricao);
			email_solicitacao($row_solicitacao['id'], $dados_solicitacao_descricao['tipo_postagem'], $dados_solicitacao_descricao['descricao']);
			$dados_solicitacao = array("interacao" => $row_solicitacao['interacao'] + 1,);
			$dados_solicitacao_descricao = array();
			funcao_solicitacao_redireciona();
			exit;
		}
		// fim - Alterar executante
		//---------------------------------------------------------------------------------------------------------------------------------------------------------------
		// Alterar testador
		if (($_GET['situacao'] == "editar") and $_GET["acao"] == "Alterar testador") {

			// busca usuario_selecionado
			$colname_usuario_selecionado = "-1";
			if (isset($_POST['testador'])) {
				$colname_usuario_selecionado = $_POST['testador'];
			}
			mysql_select_db($database_conexao, $conexao);
			$query_usuario_selecionado = sprintf("SELECT IdUsuario, nome, praca FROM usuarios WHERE IdUsuario = %s", GetSQLValueString($colname_usuario_selecionado, "int"));
			$usuario_selecionado = mysql_query($query_usuario_selecionado, $conexao) or die(mysql_error());
			$row_usuario_selecionado = mysql_fetch_assoc($usuario_selecionado);
			$totalRows_usuario_selecionado = mysql_num_rows($usuario_selecionado);
			// fim - busca usuario_selecionado

			$dados_solicitacao = array(
				"interacao" => $row_solicitacao['interacao'] + 1,
				"id_testador" => $row_usuario_selecionado['IdUsuario'],
				"testador_leu" => ""
			);
			$dados_solicitacao_descricao = array(
				"id_solicitacao" => $row_solicitacao['id'],
				"id_usuario_responsavel" => $row_usuario['IdUsuario'],
				"descricao" => "Foi alterado o testador<br>Testador anterior: " . $row_solicitacao['nome_testador'] . " - Novo testador: " . $row_usuario_selecionado['nome'] . "<br>" . $_POST['observacao'],
				"data" => date("Y-m-d H:i:s"),
				"tipo_postagem" => "Alteração de testador"
			);

			mysql_free_result($usuario_selecionado);

			funcao_solicitacao_update($row_solicitacao['id'], $dados_solicitacao, $dados_solicitacao_descricao);
			email_solicitacao($row_solicitacao['id'], $dados_solicitacao_descricao['tipo_postagem'], $dados_solicitacao_descricao['descricao']);
			$dados_solicitacao = array("interacao" => $row_solicitacao['interacao'] + 1,);
			$dados_solicitacao_descricao = array();
			funcao_solicitacao_redireciona();
			exit;
		}
		// fim - Alterar testador
		//---------------------------------------------------------------------------------------------------------------------------------------------------------------
		// Alterar data do executável
		if (($_GET['situacao'] == "editar") and $_GET["acao"] == "Alterar data do executável") {

			$dados_solicitacao = array(
				"interacao" => $row_solicitacao['interacao'] + 1,
				"data_executavel" => $_POST['data_executavel']
			);
			$dados_solicitacao_descricao = array(
				"id_solicitacao" => $row_solicitacao['id'],
				"id_usuario_responsavel" => $row_usuario['IdUsuario'],
				"descricao" => "Foi alterada a data do executável<br>Data do executavel anterior: " . date('d-m-Y', strtotime($row_solicitacao['data_executavel'])) . " - Nova data do executável: " . date('d-m-Y', strtotime($_POST['data_executavel'])) . "<br>" . $_POST['observacao'],
				"data" => date("Y-m-d H:i:s"),
				"tipo_postagem" => "Alteração de data do executável"
			);

			funcao_solicitacao_update($row_solicitacao['id'], $dados_solicitacao, $dados_solicitacao_descricao);

			email_solicitacao($row_solicitacao['id'], $dados_solicitacao_descricao['tipo_postagem'], $dados_solicitacao_descricao['descricao']);
			$dados_solicitacao = array("interacao" => $row_solicitacao['interacao'] + 1,);
			$dados_solicitacao_descricao = array();
			funcao_solicitacao_redireciona();
			exit;
		}
		// fim - Alterar data do executável
		//---------------------------------------------------------------------------------------------------------------------------------------------------------------
		// Alterar hora do executável
		if (($_GET['situacao'] == "editar") and $_GET["acao"] == "Alterar hora do executável") {

			$dados_solicitacao = array(
				"interacao" => $row_solicitacao['interacao'] + 1,
				"hora_executavel" => $_POST['hora_executavel']
			);
			$dados_solicitacao_descricao = array(
				"id_solicitacao" => $row_solicitacao['id'],
				"id_usuario_responsavel" => $row_usuario['IdUsuario'],
				"descricao" => "Foi alterada a hora do executável<br>Hora do executável anterior: " . $row_solicitacao['hora_executavel'] . " - Nova hora do executável: " . $_POST['hora_executavel'] . "<br>" . $_POST['observacao'],
				"data" => date("Y-m-d H:i:s"),
				"tipo_postagem" => "Alteração de hora do executável"
			);

			funcao_solicitacao_update($row_solicitacao['id'], $dados_solicitacao, $dados_solicitacao_descricao);

			email_solicitacao($row_solicitacao['id'], $dados_solicitacao_descricao['tipo_postagem'], $dados_solicitacao_descricao['descricao']);
			$dados_solicitacao = array("interacao" => $row_solicitacao['interacao'] + 1,);
			$dados_solicitacao_descricao = array();
			funcao_solicitacao_redireciona();
			exit;
		}
		// fim - Alterar hora do executável
		//---------------------------------------------------------------------------------------------------------------------------------------------------------------
		// Alterar tipo da solicitação
		if (($_GET['situacao'] == "editar") and $_GET["acao"] == "Alterar tipo da solicitação") {

			if (isset($_POST['implementacao_mensagem_sim_nao'])) {
				$implementacao_mensagem_sim_nao = $_POST['implementacao_mensagem_sim_nao'];
			} else {
				$implementacao_mensagem_sim_nao = "";
			}

			if (isset($_POST['implementacao_nao_justificativa'])) {
				$implementacao_nao_justificativa = $_POST['implementacao_nao_justificativa'];
			} else {
				$implementacao_nao_justificativa = "";
			}

			// gravar a mensagem
			if ($_POST['tipo'] == "Implementação") {
				if ($implementacao_mensagem_sim_nao == "s") {
					$descricao_implementacao = "<br>IMPLEMENTAÇÃO - A solicitação para implementação será realizada na versão desenvolvimento do sistema.";
				} else if ($implementacao_mensagem_sim_nao == "n") {
					$descricao_implementacao = "<br>IMPLEMENTAÇÃO - Solicitado a implementação na versão estável ou versão desejada.";
					$descricao_implementacao .= "<br>Justificativa: " . $implementacao_nao_justificativa;
				} else {
					$descricao_implementacao = "";
				}
			} else {
				$descricao_implementacao = "";
			}
			// fim - gravar a mensagem

			$dados_solicitacao = array(
				"interacao" => $row_solicitacao['interacao'] + 1,
				"tipo" => $_POST['tipo'],
				"implementacao_mensagem_sim_nao" => $implementacao_mensagem_sim_nao,
				"implementacao_nao_justificativa" => $implementacao_nao_justificativa
			);
			$dados_solicitacao_descricao = array(
				"id_solicitacao" => $row_solicitacao['id'],
				"id_usuario_responsavel" => $row_usuario['IdUsuario'],
				"descricao" => "Foi alterado o tipo da solicitação<br>Tipo anterior: " . $row_solicitacao['tipo'] . " - Novo tipo: " . $_POST['tipo'] . "<br>" . $_POST['observacao'] . $descricao_implementacao,
				"data" => date("Y-m-d H:i:s"),
				"tipo_postagem" => "Alteração de tipo de solicitação"
			);

			funcao_solicitacao_update($row_solicitacao['id'], $dados_solicitacao, $dados_solicitacao_descricao);

			email_solicitacao($row_solicitacao['id'], $dados_solicitacao_descricao['tipo_postagem'], $dados_solicitacao_descricao['descricao']);
			$dados_solicitacao = array("interacao" => $row_solicitacao['interacao'] + 1,);
			$dados_solicitacao_descricao = array();
			funcao_solicitacao_redireciona();
			exit;
		}
		// fim - Alterar tipo da solicitação
		//---------------------------------------------------------------------------------------------------------------------------------------------------------------
		// Alterar versão da implementação
		if (($_GET['situacao'] == "editar") and $_GET["acao"] == "Alterar versão da implementação") {

			if (isset($_POST['implementacao_mensagem_sim_nao'])) {
				$implementacao_mensagem_sim_nao = $_POST['implementacao_mensagem_sim_nao'];
			} else {
				$implementacao_mensagem_sim_nao = "";
			}

			if (isset($_POST['implementacao_nao_justificativa'])) {
				$implementacao_nao_justificativa = $_POST['implementacao_nao_justificativa'];
			} else {
				$implementacao_nao_justificativa = "";
			}

			// gravar a mensagem
			if ($implementacao_mensagem_sim_nao == "s") {
				$descricao_implementacao = "<br>IMPLEMENTAÇÃO - A solicitação para implementação será realizada na versão desenvolvimento do sistema.";
			} else if ($implementacao_mensagem_sim_nao == "n") {
				$descricao_implementacao = "<br>IMPLEMENTAÇÃO - Solicitado a implementação na versão estável ou versão desejada.";
				$descricao_implementacao .= "<br>Justificativa: " . $implementacao_nao_justificativa;
			} else {
				$descricao_implementacao = "";
			}
			// fim - gravar a mensagem

			$dados_solicitacao = array(
				"interacao" => $row_solicitacao['interacao'] + 1,
				"implementacao_mensagem_sim_nao" => $implementacao_mensagem_sim_nao,
				"implementacao_nao_justificativa" => $implementacao_nao_justificativa
			);
			$dados_solicitacao_descricao = array(
				"id_solicitacao" => $row_solicitacao['id'],
				"id_usuario_responsavel" => $row_usuario['IdUsuario'],
				"descricao" => "Foi alterado a versão da implementação<br>" . $_POST['observacao'] . $descricao_implementacao,
				"data" => date("Y-m-d H:i:s"),
				"tipo_postagem" => "Alteração de versão da implementação"
			);

			funcao_solicitacao_update($row_solicitacao['id'], $dados_solicitacao, $dados_solicitacao_descricao);

			email_solicitacao($row_solicitacao['id'], $dados_solicitacao_descricao['tipo_postagem'], $dados_solicitacao_descricao['descricao']);
			$dados_solicitacao = array("interacao" => $row_solicitacao['interacao'] + 1,);
			$dados_solicitacao_descricao = array();
			funcao_solicitacao_redireciona();
			exit;
		}
		// fim - Alterar versão da implementação
		//---------------------------------------------------------------------------------------------------------------------------------------------------------------
		// Alterar prioridade
		if (($_GET['situacao'] == "editar") and $_GET["acao"] == "Alterar prioridade") {

			$prioridade_justificativa_texto_anterior = NULL;
			if($row_solicitacao['prioridade_justificativa'] <> NULL){
				$prioridade_justificativa_texto_anterior = " - Justificativa: ".$row_solicitacao['prioridade_justificativa'];
			}
			$prioridade_justificativa_texto_nova = NULL;
			if($_POST['prioridade_justificativa'] <> NULL){
				$prioridade_justificativa_texto_nova = " - Justificativa: ".$_POST['prioridade_justificativa'];
			}

			$dados_solicitacao = array(
				"interacao" => $row_solicitacao['interacao'] + 1,
				"prioridade" => $_POST['prioridade'],
				"prioridade_justificativa" => $_POST['prioridade_justificativa']
			);
			$dados_solicitacao_descricao = array(
				"id_solicitacao" => $row_solicitacao['id'],
				"id_usuario_responsavel" => $row_usuario['IdUsuario'],
				"descricao" => "
				Foi alterada a prioridade<br>
				Orientação anterior: ".$row_solicitacao['prioridade'].$prioridade_justificativa_texto_anterior."<br>
				Nova prioridade: ".$_POST['prioridade'].$prioridade_justificativa_texto_nova."<br>
				".$_POST['observacao'],
				"data" => date("Y-m-d H:i:s"),
				"tipo_postagem" => "Alteração de prioridade"
			);

			funcao_solicitacao_update($row_solicitacao['id'], $dados_solicitacao, $dados_solicitacao_descricao);

			email_solicitacao($row_solicitacao['id'], $dados_solicitacao_descricao['tipo_postagem'], $dados_solicitacao_descricao['descricao']);
			$dados_solicitacao = array("interacao" => $row_solicitacao['interacao'] + 1,);
			$dados_solicitacao_descricao = array();
			funcao_solicitacao_redireciona();
			exit;
		}
		// fim - Alterar prioridade
		//---------------------------------------------------------------------------------------------------------------------------------------------------------------
		// Alterar programa/subprograma
		if (($_GET['situacao'] == "editar") and $_GET["acao"] == "Alterar programa/subprograma") {

			// selecionar o nome do programa escolhido
			$colname_geral_tipo_programa_ins = "-1";
			if (isset($_POST['id_programa'])) {
				$colname_geral_tipo_programa_ins = $_POST['id_programa'];
			}
			mysql_select_db($database_conexao, $conexao);
			$query_geral_tipo_programa_ins = sprintf("SELECT * FROM geral_tipo_programa WHERE id_programa=%s", GetSQLValueString($colname_geral_tipo_programa_ins, "int"));
			$geral_tipo_programa_ins = mysql_query($query_geral_tipo_programa_ins, $conexao) or die(mysql_error());
			$row_geral_tipo_programa_ins = mysql_fetch_assoc($geral_tipo_programa_ins);
			$totalRows_geral_tipo_programa_ins = mysql_num_rows($geral_tipo_programa_ins);
			// fim - selecionar o nome do programa escolhido

			// selecionar o nome do subprograma escolhido
			$colname_geral_tipo_subprograma_ins = "-1";
			if (isset($_POST['id_subprograma'])) {
				$colname_geral_tipo_subprograma_ins = $_POST['id_subprograma'];
			}
			mysql_select_db($database_conexao, $conexao);
			$query_geral_tipo_subprograma_ins = sprintf("SELECT * FROM geral_tipo_subprograma WHERE id_subprograma=%s", GetSQLValueString($colname_geral_tipo_subprograma_ins, "int"));
			$geral_tipo_subprograma_ins = mysql_query($query_geral_tipo_subprograma_ins, $conexao) or die(mysql_error());
			$row_geral_tipo_subprograma_ins = mysql_fetch_assoc($geral_tipo_subprograma_ins);
			$totalRows_geral_tipo_subprograma_ins = mysql_num_rows($geral_tipo_subprograma_ins);
			// fim - selecionar o nome do subprograma escolhido

			$dados_solicitacao = array(
				"interacao" => $row_solicitacao['interacao'] + 1,
				"id_programa" => $_POST['id_programa'],
				"programa" => $row_geral_tipo_programa_ins['programa'],
				"id_subprograma" => $_POST['id_subprograma'],
				"subprograma" => $row_geral_tipo_subprograma_ins['subprograma']
			);
			$dados_solicitacao_descricao = array(
				"id_solicitacao" => $row_solicitacao['id'],
				"id_usuario_responsavel" => $row_usuario['IdUsuario'],
				"descricao" => "Foi alterado o programa/subprograma<br>Programa anterior: " . $row_solicitacao['programa'] . " - Novo programa: " . $row_geral_tipo_programa_ins['programa'] . "<br>Subprograma anterior: " . $row_solicitacao['subprograma'] . " - Novo subprograma: " . $row_geral_tipo_subprograma_ins['subprograma'] . "<br>" . $_POST['observacao'],
				"data" => date("Y-m-d H:i:s"),
				"tipo_postagem" => "Alteração de programa/subprograma"
			);

			mysql_free_result($geral_tipo_programa_ins);
			mysql_free_result($geral_tipo_subprograma_ins);

			funcao_solicitacao_update($row_solicitacao['id'], $dados_solicitacao, $dados_solicitacao_descricao);

			email_solicitacao($row_solicitacao['id'], $dados_solicitacao_descricao['tipo_postagem'], $dados_solicitacao_descricao['descricao']);
			$dados_solicitacao = array("interacao" => $row_solicitacao['interacao'] + 1,);
			$dados_solicitacao_descricao = array();
			funcao_solicitacao_redireciona();
			exit;
		}
		// fim - Alterar programa/subprograma
		//---------------------------------------------------------------------------------------------------------------------------------------------------------------
		// Alterar versão
		if (($_GET['situacao'] == "editar") and $_GET["acao"] == "Alterar versão") {

			$versao = NULL;
			if(count(@$_POST['versao']) > 0){
				$versao = implode(',', $_POST['versao']);
			}

			$dados_solicitacao = array(
				"interacao" => $row_solicitacao['interacao'] + 1,
				"versao" => $versao
			);
			$dados_solicitacao_descricao = array(
				"id_solicitacao" => $row_solicitacao['id'],
				"id_usuario_responsavel" => $row_usuario['IdUsuario'],
				"descricao" => "Foi alterada a versão<br>Versão anterior: " . funcao_consulta_versao_array($row_solicitacao['versao']) . " - Nova versão: " . funcao_consulta_versao_array($versao) . "<br>" . $_POST['observacao'],
				"data" => date("Y-m-d H:i:s"),
				"tipo_postagem" => "Alteração de versão"
			);

			funcao_solicitacao_update($row_solicitacao['id'], $dados_solicitacao, $dados_solicitacao_descricao);

			email_solicitacao($row_solicitacao['id'], $dados_solicitacao_descricao['tipo_postagem'], $dados_solicitacao_descricao['descricao']);
			$dados_solicitacao = array("interacao" => $row_solicitacao['interacao'] + 1,);
			$dados_solicitacao_descricao = array();
			funcao_solicitacao_redireciona();
			exit;
		}
		// fim - Alterar versão
		//---------------------------------------------------------------------------------------------------------------------------------------------------------------
		// Alterar distribuição
		if (($_GET['situacao'] == "editar") and $_GET["acao"] == "Alterar distribuição") {

			$dados_solicitacao = array(
				"interacao" => $row_solicitacao['interacao'] + 1,
				"geral_tipo_distribuicao" => $_POST['distribuicao']
			);
			$dados_solicitacao_descricao = array(
				"id_solicitacao" => $row_solicitacao['id'],
				"id_usuario_responsavel" => $row_usuario['IdUsuario'],
				"descricao" => "Foi alterada a distribuição<br>Distribuição anterior: " . $row_solicitacao['geral_tipo_distribuicao'] . " - Nova distribuição: " . $_POST['distribuicao'] . "<br>" . $_POST['observacao'],
				"data" => date("Y-m-d H:i:s"),
				"tipo_postagem" => "Alteração de distribuição"
			);

			funcao_solicitacao_update($row_solicitacao['id'], $dados_solicitacao, $dados_solicitacao_descricao);

			email_solicitacao($row_solicitacao['id'], $dados_solicitacao_descricao['tipo_postagem'], $dados_solicitacao_descricao['descricao']);
			$dados_solicitacao = array("interacao" => $row_solicitacao['interacao'] + 1,);
			$dados_solicitacao_descricao = array();
			funcao_solicitacao_redireciona();
			exit;
		}
		// fim - Alterar distribuição
		//---------------------------------------------------------------------------------------------------------------------------------------------------------------
		// Alterar campo
		if (($_GET['situacao'] == "editar") and $_GET["acao"] == "Alterar campo") {

			$dados_solicitacao = array(
				"interacao" => $row_solicitacao['interacao'] + 1,
				"campo" => $_POST['campo']
			);
			$dados_solicitacao_descricao = array(
				"id_solicitacao" => $row_solicitacao['id'],
				"id_usuario_responsavel" => $row_usuario['IdUsuario'],
				"descricao" => "Foi alterado o campo<br>Campo anterior: " . $row_solicitacao['campo'] . " - Novo campo: " . $_POST['campo'] . "<br>" . $_POST['observacao'],
				"data" => date("Y-m-d H:i:s"),
				"tipo_postagem" => "Alteração de campo"
			);

			funcao_solicitacao_update($row_solicitacao['id'], $dados_solicitacao, $dados_solicitacao_descricao);

			email_solicitacao($row_solicitacao['id'], $dados_solicitacao_descricao['tipo_postagem'], $dados_solicitacao_descricao['descricao']);
			$dados_solicitacao = array("interacao" => $row_solicitacao['interacao'] + 1,);
			$dados_solicitacao_descricao = array();
			funcao_solicitacao_redireciona();
			exit;
		}
		// fim - Alterar campo
		//---------------------------------------------------------------------------------------------------------------------------------------------------------------
		// Alterar título
		if (($_GET['situacao'] == "editar") and $_GET["acao"] == "Alterar título") {

			$dados_solicitacao = array(
				"interacao" => $row_solicitacao['interacao'] + 1,
				"titulo" => $_POST['titulo']
			);
			$dados_solicitacao_descricao = array(
				"id_solicitacao" => $row_solicitacao['id'],
				"id_usuario_responsavel" => $row_usuario['IdUsuario'],
				"descricao" => "Foi alterado o título<br>Título anterior: " . $row_solicitacao['titulo'] . " - Novo título: " . $_POST['titulo'] . "<br>" . $_POST['observacao'],
				"data" => date("Y-m-d H:i:s"),
				"tipo_postagem" => "Alteração de título"
			);

			funcao_solicitacao_update($row_solicitacao['id'], $dados_solicitacao, $dados_solicitacao_descricao);

			email_solicitacao($row_solicitacao['id'], $dados_solicitacao_descricao['tipo_postagem'], $dados_solicitacao_descricao['descricao']);
			$dados_solicitacao = array("interacao" => $row_solicitacao['interacao'] + 1,);
			$dados_solicitacao_descricao = array();
			funcao_solicitacao_redireciona();
			exit;
		}
		// fim - Alterar título
		//---------------------------------------------------------------------------------------------------------------------------------------------------------------
		// Alterar banco de dados
		if (($_GET['situacao'] == "editar") and $_GET["acao"] == "Alterar banco de dados") {

			$dados_solicitacao = array(
				"interacao" => $row_solicitacao['interacao'] + 1,
				"tipo_bd" => $_POST['banco_de_dados']
			);
			$dados_solicitacao_descricao = array(
				"id_solicitacao" => $row_solicitacao['id'],
				"id_usuario_responsavel" => $row_usuario['IdUsuario'],
				"descricao" => "Foi alterado o banco de dados<br>Banco de dados anterior: " . $row_solicitacao['tipo_bd'] . " - Novo banco de dados: " . $_POST['banco_de_dados'] . "<br>" . $_POST['observacao'],
				"data" => date("Y-m-d H:i:s"),
				"tipo_postagem" => "Alteração de banco de dados"
			);

			funcao_solicitacao_update($row_solicitacao['id'], $dados_solicitacao, $dados_solicitacao_descricao);

			email_solicitacao($row_solicitacao['id'], $dados_solicitacao_descricao['tipo_postagem'], $dados_solicitacao_descricao['descricao']);
			$dados_solicitacao = array("interacao" => $row_solicitacao['interacao'] + 1,);
			$dados_solicitacao_descricao = array();
			funcao_solicitacao_redireciona();
			exit;
		}
		// fim - Alterar banco de dados
		//---------------------------------------------------------------------------------------------------------------------------------------------------------------
		// Alterar ECF
		if (($_GET['situacao'] == "editar") and $_GET["acao"] == "Alterar ECF") {

			$dados_solicitacao = array(
				"interacao" => $row_solicitacao['interacao'] + 1,
				"geral_tipo_ecf" => $_POST['ecf']
			);
			$dados_solicitacao_descricao = array(
				"id_solicitacao" => $row_solicitacao['id'],
				"id_usuario_responsavel" => $row_usuario['IdUsuario'],
				"descricao" => "Foi alterada a ECF<br>ECF anterior: " . $row_solicitacao['geral_tipo_ecf'] . " - Nova ECF: " . $_POST['ecf'] . "<br>" . $_POST['observacao'],
				"data" => date("Y-m-d H:i:s"),
				"tipo_postagem" => "Alteração de ECF"
			);

			funcao_solicitacao_update($row_solicitacao['id'], $dados_solicitacao, $dados_solicitacao_descricao);

			email_solicitacao($row_solicitacao['id'], $dados_solicitacao_descricao['tipo_postagem'], $dados_solicitacao_descricao['descricao']);
			$dados_solicitacao = array("interacao" => $row_solicitacao['interacao'] + 1,);
			$dados_solicitacao_descricao = array();
			funcao_solicitacao_redireciona();
			exit;
		}
		// fim - Alterar ECF
		//---------------------------------------------------------------------------------------------------------------------------------------------------------------
		// Alterar medida tomada
		if (($_GET['situacao'] == "editar") and $_GET["acao"] == "Alterar medida tomada") {

			$dados_solicitacao = array(
				"interacao" => $row_solicitacao['interacao'] + 1,
				"medida_tomada" => $_POST['observacao']
			);
			$dados_solicitacao_descricao = array(
				"id_solicitacao" => $row_solicitacao['id'],
				"id_usuario_responsavel" => $row_usuario['IdUsuario'],
				"descricao" => "Foi alterada a medida tomada<br>Medida tomada anterior: " . $row_solicitacao['medida_tomada'] . "<br>Nova medida tomada: " . $_POST['observacao'],
				"data" => date("Y-m-d H:i:s"),
				"tipo_postagem" => "Alteração de medida tomada"
			);

			funcao_solicitacao_update($row_solicitacao['id'], $dados_solicitacao, $dados_solicitacao_descricao);

			email_solicitacao($row_solicitacao['id'], $dados_solicitacao_descricao['tipo_postagem'], $dados_solicitacao_descricao['descricao']);
			$dados_solicitacao = array("interacao" => $row_solicitacao['interacao'] + 1,);
			$dados_solicitacao_descricao = array();
			funcao_solicitacao_redireciona();
			exit;
		}
		// fim - Alterar medida tomada
		//---------------------------------------------------------------------------------------------------------------------------------------------------------------
		// Alterar anomalia
		if (($_GET['situacao'] == "editar") and $_GET["acao"] == "Alterar anomalia") {

			// anomalia_atual
			mysql_select_db($database_conexao, $conexao);
			$query_anomalia_atual = sprintf("SELECT id, descricao FROM solicitacao_descricoes WHERE id_solicitacao = %s and tipo_postagem = 'Nova Solicitação'", GetSQLValueString($row_solicitacao['id'], "int"));
			$anomalia_atual = mysql_query($query_anomalia_atual, $conexao) or die(mysql_error());
			$row_anomalia_atual = mysql_fetch_assoc($anomalia_atual);
			$totalRows_anomalia_atual = mysql_num_rows($anomalia_atual);
			// fim - anomalia_atual

			$dados_solicitacao = array(
				"interacao" => $row_solicitacao['interacao'] + 1,
				"status" => $row_solicitacao['status']
			);
			$dados_solicitacao_descricao = array(
				"id_solicitacao" => $row_solicitacao['id'],
				"id_usuario_responsavel" => $row_usuario['IdUsuario'],
				"descricao" => "Foi alterada a anomalia<br>Anomalia anterior: " . $row_anomalia_atual['descricao'] . "<br>Nova anomalia: " . $_POST['observacao'],
				"data" => date("Y-m-d H:i:s"),
				"tipo_postagem" => "Alteração de anomalia"
			);

			// atualiza descrição	
			$updateAnomalia = sprintf(
				"UPDATE solicitacao_descricoes SET descricao=%s WHERE id=%s",
				GetSQLValueString($_POST['observacao'], "text"),
				GetSQLValueString($row_anomalia_atual['id'], "int")
			);
			$ResultAnomalia = mysql_query($updateAnomalia, $conexao) or die(mysql_error());
			// fim - atualiza descrição

			mysql_free_result($anomalia_atual);

			funcao_solicitacao_update($row_solicitacao['id'], $dados_solicitacao, $dados_solicitacao_descricao);

			email_solicitacao($row_solicitacao['id'], $dados_solicitacao_descricao['tipo_postagem'], $dados_solicitacao_descricao['descricao']);
			$dados_solicitacao = array("interacao" => $row_solicitacao['interacao'] + 1,);
			$dados_solicitacao_descricao = array();
			funcao_solicitacao_redireciona();
			exit;
		}
		// fim - Anomalia anomalia
		//---------------------------------------------------------------------------------------------------------------------------------------------------------------

	}
	//---------------------------------------------------------------------------------------------------------------------------------------------------------------



	//---------------------------------------------------------------------------------------------------------------------------------------------------------------
	// QUESTIONAMENTO -----------------------------------------------------------------------------------------------------------------------------------------------
	//---------------------------------------------------------------------------------------------------------------------------------------------------------------
	if (($_GET['situacao'] == "") and $_GET["acao"] == "Questionar") {

		$dados_solicitacao = array(
			"interacao" => $row_solicitacao['interacao'] + 1,
			"status_questionamento" => $_POST['questionado']
		);
		$dados_solicitacao_descricao = array(
			"id_solicitacao" => $row_solicitacao['id'],
			"id_usuario_responsavel" => $row_usuario['IdUsuario'],
			"descricao" => $_POST['observacao'],
			"data" => date("Y-m-d H:i:s"),
			"tipo_postagem" => "Questionamento",
			"questionado" => $_POST['questionado']
		);

		funcao_solicitacao_update($row_solicitacao['id'], $dados_solicitacao, $dados_solicitacao_descricao);
		email_solicitacao($row_solicitacao['id'], $dados_solicitacao_descricao['tipo_postagem'], $dados_solicitacao_descricao['descricao']);
		$dados_solicitacao = array("interacao" => $row_solicitacao['interacao'] + 1,);
		$dados_solicitacao_descricao = array();
		funcao_solicitacao_redireciona();
		exit;
	}
	//---------------------------------------------------------------------------------------------------------------------------------------------------------------

	exit;
}

//---------------------------------------------------------------------------------------------------------------------------------------------------------------
// Alteração de previsãO QTDE/PRAZO -----------------------------------------------------------------------------------------------------------------------------
//---------------------------------------------------------------------------------------------------------------------------------------------------------------
$alteracao_previsao_prazo = 0;
$alterar_previsao_status = 0;
$alterar_previsao_envolvido = "";

// operador =======================================================================================================================================
if (($row_usuario['controle_solicitacao'] == 'Y' and $row_solicitacao['id_operador'] == $row_usuario['IdUsuario']) and
	($row_solicitacao['situacao'] == "em análise")
) {
	$alterar_previsao_status = 1;
	$alterar_previsao_envolvido = "operador";
}
// fim - operador =================================================================================================================================

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
		($row_solicitacao['situacao'] == "em orçamento" and  $row_solicitacao['orcamento'] != "") or
		$row_solicitacao['situacao'] == "em validação")
) {
	$alterar_previsao_status = 1;
	$alterar_previsao_envolvido = "solicitante";
}
// fim - solicitante ==============================================================================================================================

if ($alterar_previsao_status == 1) {

	if ($row_solicitacao['prioridade'] == "Alta") {
		$alteracao_previsao_prioridade = "alta";
	}
	if ($row_solicitacao['prioridade'] == "Média") {
		$alteracao_previsao_prioridade = "media";
	}
	if ($row_solicitacao['prioridade'] == "Baixa") {
		$alteracao_previsao_prioridade = "baixa";
	}

	$alterar_previsao = "alterar_previsao_" . $alterar_previsao_envolvido; // pega na tabela 'solicitacao' o campo atual
	$alteracao_previsao_prazo = "alteracao_previsao_prazo_" . $alterar_previsao_envolvido . "_" . $alteracao_previsao_prioridade; // pega na tabela 'param.' o campo atual

}
//---------------------------------------------------------------------------------------------------------------------------------------------------------------
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title></title>

	<link rel="stylesheet" href="css/suporte.css" type="text/css" media="screen" />


	<script type="text/javascript" src="js/jquery.js"></script>

	<script type="text/javascript" src="funcoes.js"></script>

	<script src="js/jquery.metadata.js" type="text/javascript"></script>
	<script type="text/javascript" src="js/jquery.validate.js"></script>

	<script type="text/javascript" src="js/jquery.maskedinput-1.2.2.min.js"></script>
	<script src="js/jquery.price_format.1.3.js" type="text/javascript"></script>
	<script type="text/javascript" src="js/jquery.alphanumeric.pack.js"></script>

	<link type="text/css" href="css/jquery-ui.css" rel="stylesheet" />
	<link type="text/css" href="css/jquery-ui-timepicker-addon.css" rel="stylesheet" />

	<script type="text/javascript" src="js/jquery-ui.js"></script>
	<script type="text/javascript" src="js/jquery-ui-timepicker-addon.js"></script>

	<style>
		/* erro de validação */
		form.cmxform label.error,
		label.error {
			color: red;
		}

		div.error,
		label.error {
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

		<? if (
			($_GET['situacao'] == "geral" and $_GET['acao'] == "Concluir execução") or
			($_GET['situacao'] == "em testes" and $_GET['acao'] == "Concluir testes") or
			($_GET['situacao'] == "em validação" and $_GET['acao'] == "Concluir validação") or
			($_GET['situacao'] == "geral" and $_GET['acao'] == "Aprovar") or
			($_GET['situacao'] == "geral" and $_GET['acao'] == "Solicitar orçamento") or
			($_GET['situacao'] == "geral" and $_GET['acao'] == "Postar orçamento") or
			($_GET['situacao'] == "geral" and $_GET['acao'] == "Concluir")
		) { ?>.ui-datepicker-current {
			display: none;
		}

		<? } ?>
		/* fim - calendário */
	</style>

	<script type="text/javascript">
		window.history.forward(1); // Desabilita a função de voltar do Browser

		// validar diferença entre datas
		jQuery.validator.addMethod("dateRange", function() {

			// quebra data inicial
			var data_inicio = $("#data_inicio").val();
			var quebraDI = data_inicio.split("-");
			var diaDI = quebraDI[0];
			var mesDI = quebraDI[1];
			var anoDI = quebraDI[2].substr(0, 4);
			var time_inicial = quebraDI[2].substr(5, 8);
			var quebraTimeDI = time_inicial.split(":");
			var horaDI = quebraTimeDI[0];
			var minutoDI = quebraTimeDI[1];
			var segundoDI = quebraTimeDI[2];

			// quebra data final
			var data_final = $("#data").val();
			var quebraDF = data_final.split("-");
			var diaDF = quebraDF[0];
			var mesDF = quebraDF[1];
			var anoDF = quebraDF[2].substr(0, 4);
			var time_final = quebraDF[2].substr(5, 8);
			var quebraTimeDF = time_final.split(":");
			var horaDF = quebraTimeDF[0];
			var minutoDF = quebraTimeDF[1];
			var segundoDF = quebraTimeDF[2];

			var date1 = anoDI + "-" + mesDI + "-" + diaDI + " " + horaDI + ":" + minutoDI + ":" + segundoDI;
			var date2 = anoDF + "-" + mesDF + "-" + diaDF + " " + horaDF + ":" + minutoDF + ":" + segundoDF;

			return (date1 < date2);
		}, " Data final deve ser maior que a data inicial.");
		// validar diferença entre datas

		$.metadata.setType("attr", "validate");
		$(document).ready(function() {

			// tipo (Implementação)	
			<? if (
				($_GET['situacao'] == "editar" and $_GET['acao'] == "Alterar tipo da solicitação") and
				$row_solicitacao['id_usuario_responsavel'] == $row_usuario['IdUsuario']
			) { ?>
				$("input[id='implementacao_mensagem_sim_nao']").attr("checked", ""); // limpa 'radio'
				$("textarea[id='implementacao_nao_justificativa']").val(''); // limpa 'justificativa'

				$('input[id="implementacao_mensagem_sim_nao"]').attr('disabled', 'disabled');
				$("textarea[id='implementacao_nao_justificativa']").attr('disabled', 'disabled');

				$("div[id=implementacao_mensagem]").hide(); // oculta 'radio'
				$("div[id=implementacao_nao]").hide(); // oculta 'justificativa'

				$("select[id='tipo']").change(function() { // ao mudar o valor do select 'tipo'
					$("select[id='tipo'] option:selected").each(function() {

						tipo_atual = $(this).text();

						// se tipo é: Implementação
						if (tipo_atual == "Implementação") {

							$("div[id=implementacao_mensagem]").show();
							$('input[id="implementacao_mensagem_sim_nao"]').removeAttr('disabled');

							// implementacao_nao ---------------------------------------------------------------
							$("input[id='implementacao_mensagem_sim_nao']").change(function() {

								// se 'não concordo'
								if ($("input[id='implementacao_mensagem_sim_nao']:checked").val() == 'n') {

									$("div[id=implementacao_nao]").show();
									$("textarea[id='implementacao_nao_justificativa']").removeAttr('disabled');
								}
								// fim - se 'não concordo'

								// senão ...
								else {

									$("textarea[id='implementacao_nao_justificativa']").val('');
									$("textarea[id='implementacao_nao_justificativa']").attr('disabled', 'disabled');
									$("div[id=implementacao_nao]").hide();

								}
								// fim - senão ...
							});
							// fim - implementacao_nao ---------------------------------------------------------

						}
						// fim - se tipo é: Implementação

						// se não é Implementação
						else {

							$("input[id='implementacao_mensagem_sim_nao']").attr("checked", "");
							$("textarea[id='implementacao_nao_justificativa']").val('');

							$('input[id="implementacao_mensagem_sim_nao"]').attr('disabled', 'disabled');
							$("textarea[id='implementacao_nao_justificativa']").attr('disabled', 'disabled');

							$("div[id=implementacao_mensagem]").hide();
							$("div[id=implementacao_nao]").hide();

						}
						// fim - se não é Implementação

					});
				})
			<? } ?>
			// fim - tipo (Implementação)


			// implementacao_mensagem_sim_nao (Implementação)	
			<? if (
				($_GET['situacao'] == "editar" and $_GET['acao'] == "Alterar versão da implementação") and
				$row_solicitacao['id_usuario_responsavel'] == $row_usuario['IdUsuario']
			) { ?>
				if ($("input[id='implementacao_mensagem_sim_nao']:checked").val() == 'n') {

					$("div[id=implementacao_nao]").show();
					$("textarea[id='implementacao_nao_justificativa']").removeAttr('disabled');

				} else {

					/* $("textarea[id='implementacao_nao_justificativa']").val(''); */
					$("textarea[id='implementacao_nao_justificativa']").attr('disabled', 'disabled');
					$("div[id=implementacao_nao]").hide();

				}

				$("input[id='implementacao_mensagem_sim_nao']").change(function() {

					// se 'não concordo'
					if ($("input[id='implementacao_mensagem_sim_nao']:checked").val() == 'n') {

						$("div[id=implementacao_nao]").show();
						$("textarea[id='implementacao_nao_justificativa']").removeAttr('disabled');
					}
					// fim - se 'não concordo'

					// senão ...
					else {

						/* $("textarea[id='implementacao_nao_justificativa']").val(''); */
						$("textarea[id='implementacao_nao_justificativa']").attr('disabled', 'disabled');
						$("div[id=implementacao_nao]").hide();

					}
					// fim - senão ...

				});
			<? } ?>
			// fim - implementacao_mensagem_sim_nao (Implementação)

			$('#data').attr("disabled", true);

			// calendário -------------------------------------------------------------

			// data_inicio
			$('#data_inicio').datetimepicker({
				data_atual_juliano: '<?php echo time() * 1000 ?>',
				showSecond: true,
				inline: true,
				dateFormat: 'dd-mm-yy',
				timeFormat: 'HH:mm:ss',
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

					$('#data').val('');

				},
				onSelect: function(selectedDateTime) {

					$('#data').val('');

				},
				onClose: function(selectedDateTime) {

					if (selectedDateTime == '') {
						$('#data').val('');
						$('#data').attr("disabled", true);
					} else {
						$('#data').attr("disabled", false);
					}

				}

			});
			// fim - data_inicio

			// data
			$('#data').datetimepicker({
				data_atual_juliano: '<?php echo time() * 1000 ?>',
				showSecond: true,
				inline: true,
				dateFormat: 'dd-mm-yy',
				timeFormat: 'HH:mm:ss',
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
					$('#data').datetimepicker('option', 'minDate', new Date(start.getTime()));

					<? if ($_GET['situacao'] == "geral" and $_GET['acao'] == "Alterar previsão") { ?>

						// data limite			
						var data_inicio_ingles = $("#data_inicio").val();
						var quebraDI = data_inicio_ingles.split("-");
						var diaDI = quebraDI[0];
						var mesDI = quebraDI[1];
						var anoDI = quebraDI[2].substr(0, 4);
						var time_inicial = quebraDI[2].substr(5, 8);
						var quebraTimeDI = time_inicial.split(":");
						var horaDI = quebraTimeDI[0];
						var minutoDI = quebraTimeDI[1];
						var segundoDI = quebraTimeDI[2];
						var data_inicio_ingles = anoDI + "-" + mesDI + "-" + diaDI + " " + horaDI + ":" + minutoDI + ":" + segundoDI;

						var data_nova = new Date((somar_dias_uteis(data_inicio_ingles, <? echo $row_parametros[$alteracao_previsao_prazo]; ?>))).getTime();
						$('#data').datetimepicker('option', 'maxDate', new Date(data_nova));
						// fim - data limite

					<? } ?>

				}

			});
			// fim - data

			// fim - calendário -------------------------------------------------------

			//region - prioridade ***************************************************************************
			<? if($row_solicitacao['prioridade'] == "Alta"){ ?>
				$("div[id=prioridade_justificativa_caixa]").show();
			<? } else { ?>
				$("div[id=prioridade_justificativa_caixa]").hide();
			<? } ?>
			
			$("select[id='prioridade']").change(function () { // ao mudar o valor do select 'prioridade'
				$("select[id='prioridade'] option:selected").each(function () {
					prioridade_atual = $(this).val();
					
					// se prioridade é: Alta
					if( prioridade_atual=="Alta" ){
						
						$("div[id=prioridade_justificativa_caixa]").show();

						$("textarea[id='prioridade_justificativa']").val('');
						
					} 
					// fim - se prioridade é: Alta
					
					// se não
					else {
						
						$("div[id=prioridade_justificativa_caixa]").hide();

						$("textarea[id='prioridade_justificativa']").val('');

					}
					// fim - se não
					
				});
			})
			//endregion - fim - prioridade ******************************************************************


			// validação
			$("#form").validate({
				rules: {
					<? if (
						($_GET['acao'] == "Devolver" and $_GET['resposta'] == "") or
						($_GET['acao'] == "Recusar" and $_GET['resposta'] == "") or 
						($_GET['situacao']=="editar" and $_GET['acao'] != "Alterar prioridade")
					) { ?>
					observacao: {
						required: true, 
						minlength: 10
					},
					<? } else if (
						($_GET['situacao']=="editar" and $_GET['acao'] == "Alterar prioridade")
					) { ?>
					observacao: {
						required: function(element){
							return $("select[id='prioridade'] option:selected").val() != "Alta";
						},
						minlength: 10
					},
					<? } ?>
					data_inicio: {
						dateTimeBR: true,
						required: true
					},
					data: {
						required: true,
						dateTimeBR: true,
						dateRange: true
					},
					executante: "required",
					testador: "required",
					operador: "required",
					usuario_responsavel: "required",
					<? if (
						($_GET['acao'] == "Devolver" and $_GET['resposta'] == "") or
						($_GET['situacao'] == "geral" and $_GET['acao'] == "Concluir execução") or
						(($_GET['situacao'] == "em testes" and $_GET['acao'] == "Concluir testes") and $_GET["resposta"] == "") or
						(($_GET['situacao'] == "geral" and $_GET['acao'] == "Aprovar") and $_GET["resposta"] == "")
					) { ?>
						versao_acao: "required",
					<? } ?>
					previsao_entrega: "required",
					orcamento: "required",
					<? if ($row_solicitacao['orcamento'] > 0) { ?>
						orcamento_os: {
							required: true,
							minlength: 10,
							min: 1
						},
					<? } ?>
					prazo_desenvolvimento_orcamento: "required",
					data_executavel: "required",
					hora_executavel: "required",
					tipo: "required",
					implementacao_mensagem_sim_nao: "required",
					implementacao_nao_justificativa: "required",
					prioridade: "required",
					prioridade_justificativa: {
						required: function(element){
							return $("select[id='prioridade'] option:selected").val() == "Alta";
						},
						minlength: 30
					},
					id_programa: "required",
					id_subprograma: "required",
					'versao[]': "required",
					distribuicao: "required",
					banco_de_dados: "required",
					ecf: "required",
					campo: "required",
					titulo: "required",
					numero_revisao_svn_estavel: {
						required: function(element) {
							return $("#numero_revisao_svn_desenvolvimento").val() == '';
						}
					},
					numero_revisao_svn_desenvolvimento: {
						required: function(element) {
							return $("#numero_revisao_svn_estavel").val() == '';
						}
					},
					devolucao_motivo: "required",
					tempo_gasto: "required"
				},
				messages: {
					<? if (
						($_GET['acao'] == "Devolver" and $_GET['resposta'] == "") or
						($_GET['acao'] == "Recusar" and $_GET['resposta'] == "") or 
						($_GET['situacao']=="editar" and $_GET['acao'] != "Alterar prioridade")
					) { ?>
					observacao: "Informe a observação com no mínimo 10 caracteres",
					<? } else if (
						($_GET['situacao']=="editar" and $_GET['acao'] == "Alterar prioridade")
					) { ?>
					observacao: "Informe a observação com no mínimo 10 caracteres",
					<? } ?>
					data_inicio: {
						required: " Informe uma data inicial",
						dateTimeBR: " informe uma data inicial válida"
					},
					data: {
						required: " Informe uma data final",
						dateTimeBR: " informe uma data final válida"
					},
					executante: " Informe o executante",
					testador: " Informe o testador",
					operador: " Informe o operador",
					usuario_responsavel: " Informe o solicitante",
					<? if (
						($_GET['acao'] == "Devolver" and $_GET['resposta'] == "") or
						($_GET['situacao'] == "geral" and $_GET['acao'] == "Concluir execução") or
						(($_GET['situacao'] == "em testes" and $_GET['acao'] == "Concluir testes") and $_GET["resposta"] == "") or
						(($_GET['situacao'] == "geral" and $_GET['acao'] == "Aprovar") and $_GET["resposta"] == "")
					) { ?>
						versao_acao: " Informe a versão",
					<? } ?>
					previsao_entrega: " Informe a previsão de entrega",
					orcamento: " Informe o valor do orçamento",
					<? if ($row_solicitacao['orcamento'] > 0) { ?>
						orcamento_os: " Informe o número da ordem de serviço com 10 dígitos",
					<? } ?>
					prazo_desenvolvimento_orcamento: " Informe o prazo em",
					data_executavel: " Informe a data do executável",
					hora_executavel: " Informe a hora do executável",
					tipo: " Informe o tipo",
					implementacao_mensagem_sim_nao: " Escolha uma das alternativas",
					implementacao_nao_justificativa: " Informe a justificativa",
					tipo: " Informe o tipo",
					prioridade: " Informe a prioridade",
					prioridade_justificativa:  "Informe a justificativa da prioridade Alta com no mínimo 30 caracteres",
					id_programa: " Informe o programa",
					id_subprograma: " Informe o subprograma",
					'versao[]': " Informe a versão",
					distribuicao: " Informe a distribuição",
					banco_de_dados: " Informe o banco de dados",
					ecf: " Informe a ECF",
					campo: " Informe o campo",
					titulo: " Informe o título",
					numero_revisao_svn_estavel: " Informe um número",
					numero_revisao_svn_desenvolvimento: " Informe um número",
					devolucao_motivo: " Informe o motivo",
					tempo_gasto: " Informe o tempo gasto"
				},
				onkeyup: false,
				submitHandler: function(form) { // evita que o formulário seja enviado várias vezes, desabilitando após o primeiro envio (conta somente quando não existe a validação).
					$("#botao_salvar").attr("disabled", "disabled");
					$(form).ajaxSubmit();
					return false;
				}
			});
			// fim - validação


			// mascara - inicio
			$('#data_executavel').mask('99-99-9999', {
				placeholder: " "
			});
			$('#hora_executavel').mask('99:99:99', {
				placeholder: " "
			});

			$('#tempo_gasto').mask('99:99:99', {
				placeholder: " "
			});

			$('#prazo_desenvolvimento_orcamento').numeric();

			$('#orcamento').priceFormat({
				prefix: '',
				centsSeparator: ',',
				thousandsSeparator: ''
			});

			$("#orcamento_os").numeric();
			// fim - mascara


			// geral_tipo_programa
			$("select[name=id_programa]").change(function() {
				$("select[name=id_subprograma]").html('<option value="0">Carregando...</option>');

				$.post("solicitacao_tipo_subprograma.php", {
						id_programa: $(this).val()
					},
					function(valor) {
						$("select[name=id_subprograma]").html(valor);
					}
				)

			})
			// fim - geral_tipo_programa

			// tempo gasto
			<?php
			$tg_dias_restantes = 0;
			$tg_horas_restantes = 23;
			$tg_minutos_restantes = 59;

			if ($row_solicitacao['previsao_geral_inicio'] != NULL) {

				#Calculamos a contagem regressiva
				$previsao_geral_inicio_diferenca = strtotime(date('Y-m-d H:i:s')) - strtotime($row_solicitacao['previsao_geral_inicio']);

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

			$("select[id='tipo'] option[value='Dúvida']").remove();

		});
	</script>
</head>

<body>

	<div class="div_solicitacao_linhas">
		<table cellspacing="0" cellpadding="0" width="100%">
			<tr>
				<td style="text-align:left">
					Solicitação número: <?php echo $row_solicitacao['id']; ?>
				</td>

				<td style="text-align: right">
					&lt;&lt; <a href="solicitacao_editar.php?id_solicitacao=<?php echo $_GET['id_solicitacao']; ?>" target="_top">Voltar</a>
				</td>
			</tr>
		</table>
	</div>

	<div class="div_solicitacao_linhas3">
		<table cellspacing="0" cellpadding="0">
			<tr>
				<td style="text-align: left">
					<strong><? echo $_GET['acao']; ?></strong>
					<? if ($_GET['resposta'] == "sim" or $_GET['resposta'] == "nao") {
						echo " (" . $_GET['resposta'] . ") ";
					} ?>
				</td>
			</tr>
		</table>
	</div>

	<div class="div_solicitacao_linhas3">
		<table cellspacing="0" cellpadding="0">
			<tr>
				<td style="text-align: left">
					Título: <?php echo $row_solicitacao['titulo']; ?>
				</td>
			</tr>
		</table>
	</div>


	<!-- orçamento existente -->
	<? if ($row_solicitacao['orcamento'] != "" and (isset($_GET['acao']) and ($_GET['acao'] == "Solicitar orçamento" or $_GET['acao'] == "Postar orçamento"))) { ?>
		<div class="div_solicitacao_linhas3">
			<table cellspacing="0" cellpadding="0" width="100%">
				<tr>
					<td style="text-align: left">
						<span class="label_solicitacao" style="color:#F00">
							Esta solicitação já possui um orçamento. Caso seja solicitado/postado um novo, o antigo será excluído.
						</span>
					</td>
				</tr>
			</table>
		</div>
	<? } ?>
	<!-- fim - orçamento existente -->


	<!-- Alterar previsão -->
	<? if (
		(
			($_GET['situacao'] == "geral" and $_GET['acao'] == "Alterar previsão")) and ($_GET['resposta'] != "nao")
	) { ?>
		<div class="div_solicitacao_linhas3">
			<table cellspacing="0" cellpadding="0" width="100%">
				<tr>
					<td style="text-align: left">
						<span class="label_solicitacao">
							Previsão atual:
							<?
							// inicio
							if (isset($row_solicitacao['previsao_geral_inicio'])) {
								echo date('d-m-Y  H:i', strtotime($row_solicitacao['previsao_geral_inicio']));
							}
							?>
							à
							<?
							// fim
							if (isset($row_solicitacao['previsao_geral'])) {
								echo date('d-m-Y  H:i', strtotime($row_solicitacao['previsao_geral']));
							}
							?>
						</span>
					</td>
				</tr>
			</table>
		</div>
	<? } ?>
	<!-- fim - Alterar previsão -->


	<div class="div_solicitacao_linhas2">
		<table cellspacing="0" cellpadding="0" width="100%">
			<tr>
				<td style="text-align: left">
					<form id="form" name="form" method="POST" action="<?php echo $editFormAction; ?>" class="cmxform" target="_top">

						<!-- data -->
						<? if (
							(
								($_GET['situacao'] == "recebida" and $_GET['acao'] == "Colocar em análise") or
								($_GET['situacao'] == "geral" and $_GET['acao'] == "Alterar previsão") or
								($_GET['situacao'] == "analisada" and $_GET['acao'] == "Aceitar" and $row_solicitacao['status_recusa'] != "1") or
								($_GET['situacao'] == "aprovada" and $_GET['acao'] == "Aceitar" and $row_solicitacao['status_recusa'] != "1") or
								($_GET['situacao'] == "executada" and $_GET['acao'] == "Aceitar" and $row_solicitacao['status_recusa'] != "1") or 
								($_GET['situacao'] == "em validação" and $_GET['acao'] == "Aceitar" and $row_solicitacao['status_recusa'] != "1" and $row_solicitacao['status'] != "devolvida para operador") or

								($_GET['situacao'] == "em orçamento" and $_GET['acao'] == "Aceitar" and $row_solicitacao['status_devolucao'] != "" and $row_solicitacao['status_recusa'] != "1" and $row_solicitacao['status'] != "devolvida para operador") or
								($_GET['situacao'] == "em testes" and $_GET['acao'] == "Aceitar" and $row_solicitacao['status_devolucao'] != "" and $row_solicitacao['status_recusa'] != "1" and $row_solicitacao['status'] != "devolvida para operador") or
								($_GET['situacao'] == "em validação" and $_GET['acao'] == "Aceitar" and $row_solicitacao['status_devolucao'] != "" and $row_solicitacao['status_recusa'] != "1" and $row_solicitacao['status'] != "devolvida para operador")) and ($_GET['resposta'] != "nao")
						) { ?>

							<div style="padding-top: 10px;">
								Data inicio:<br>
								<input name="data_inicio" type="text" id="data_inicio" size="30" autocomplete="off" />
							</div>

							<div style="padding-top: 10px;">
								Data fim:<br>
								<input name="data" type="text" id="data" size="30" autocomplete="off" />
							</div>

						<? } ?>
						<!-- fim - data -->


						<!-- Executante -->
						<? if (
							($_GET['situacao'] == "analisada" and $_GET['acao'] == "Escolher analista de orçamento") or
							($_GET['situacao'] == "aprovada" and ($_GET['acao'] == "Escolher executante" or $_GET['acao'] == "Encaminhar")) or
							($_GET['situacao'] == "editar" and $_GET['acao'] == "Alterar analista de orçamento") or
							($_GET['situacao'] == "editar" and $_GET['acao'] == "Alterar executante") or
							($_GET['situacao'] == "em execução" and $_GET['acao'] == "Encaminhar") or
							($_GET['situacao'] == "em orçamento" and $_GET['acao'] == "Encaminhar") or
							($_GET['situacao'] == "analisada" and $_GET['acao'] == "Encaminhar") or
							($_GET['situacao'] == "geral" and $_GET['acao'] == "Aprovar") or
							($_GET['situacao'] == "geral" and $_GET['acao'] == "Solicitar orçamento")
						) { ?>
							<div style="padding-top: 10px;">
								<?
								mysql_select_db($database_conexao, $conexao);
								$query_executante = "
								SELECT 
									IdUsuario, nome 
								FROM 
									usuarios 
								WHERE 
									status = 1 and 
									solicitacao_executante = 'Y' 
								ORDER BY 
									nome ASC
								";
								$executante = mysql_query($query_executante, $conexao) or die(mysql_error());
								$row_executante = mysql_fetch_assoc($executante);
								$totalRows_executante = mysql_num_rows($executante);
								?>
								Executante:<br>
								<select name="executante" id="executante">
									<option value="">Escolha o executante ...</option>
									<?php
									do {
									?>
										<option value="<?php echo $row_executante['IdUsuario'] ?>"><?php echo $row_executante['nome'] ?></option>
									<?php
									} while ($row_executante = mysql_fetch_assoc($executante));
									$rows = mysql_num_rows($executante);
									if ($rows > 0) {
										mysql_data_seek($executante, 0);
										$row_executante = mysql_fetch_assoc($executante);
									}
									?>
								</select>
								<? mysql_free_result($executante); ?>
							</div>
						<? } ?>
						<!-- fim - Executante -->


						<!-- Testador -->
						<? if (
							($_GET['situacao'] == "geral" and $_GET['acao'] == "Concluir execução") or
							($_GET['situacao'] == "executada" and ($_GET['acao'] == "Escolher testador" or $_GET['acao'] == "Encaminhar")) or
							($_GET['situacao'] == "editar" and $_GET['acao'] == "Alterar testador") or
							($_GET['situacao'] == "em testes" and $_GET['acao'] == "Encaminhar")
						) { ?>
							<div style="padding-top: 10px;">
								<?
								mysql_select_db($database_conexao, $conexao);
								$query_testador = "
								SELECT 
									IdUsuario, nome 
								FROM 
									usuarios 
								WHERE 
									status = 1 and 
									solicitacao_testador = 'Y' 
								ORDER BY 
									nome ASC
								";
								$testador = mysql_query($query_testador, $conexao) or die(mysql_error());
								$row_testador = mysql_fetch_assoc($testador);
								$totalRows_testador = mysql_num_rows($testador);
								?>
								Testador:<br>
								<select name="testador" id="testador">
									<option value="">Escolha o testador ...</option>
									<?php
									do {
									?>
										<option value="<?php echo $row_testador['IdUsuario'] ?>"><?php echo $row_testador['nome'] ?></option>
									<?php
									} while ($row_testador = mysql_fetch_assoc($testador));
									$rows = mysql_num_rows($testador);
									if ($rows > 0) {
										mysql_data_seek($testador, 0);
										$row_testador = mysql_fetch_assoc($testador);
									}
									?>
								</select>
								<? mysql_free_result($testador); ?>
							</div>
						<? } ?>
						<!-- fim - Testador -->


						<!-- Operador -->
						<? if (
							(
								$row_usuario['controle_solicitacao'] == 'Y' or 
								$row_solicitacao['id_operador'] == $row_usuario['IdUsuario']
							) and 
							($_GET['situacao'] == "editar" and $_GET['acao'] == "Alterar operador")
						) { ?>
							<div style="padding-top: 10px;">
								<?
								mysql_select_db($database_conexao, $conexao);
								$query_operador = "
								SELECT 
									IdUsuario, nome 
								FROM 
									usuarios 
								WHERE 
									status = 1 and 
									controle_solicitacao = 'Y' 
								ORDER BY 
									nome ASC
								";
								$operador = mysql_query($query_operador, $conexao) or die(mysql_error());
								$row_operador = mysql_fetch_assoc($operador);
								$totalRows_operador = mysql_num_rows($operador);
								?>
								Operador:<br>
								<select name="operador" id="operador">
									<option value="">Escolha o operador ...</option>
									<?php
									do {
									?>
										<option value="<?php echo $row_operador['IdUsuario'] ?>"><?php echo $row_operador['nome'] ?></option>
									<?php
									} while ($row_operador = mysql_fetch_assoc($operador));
									$rows = mysql_num_rows($operador);
									if ($rows > 0) {
										mysql_data_seek($operador, 0);
										$row_operador = mysql_fetch_assoc($operador);
									}
									?>
								</select>
								<? mysql_free_result($operador); ?>
							</div>
						<? } ?>
						<!-- fim - Operador -->


						<!-- Solicitante -->
						<? if (
							($_GET['situacao'] == "editar" and $_GET['acao'] == "Alterar solicitante")
						) { ?>
							<div style="padding-top: 10px;">
								<?

								$filtro_solicitacao_solicitante = "1=1";

								if ($row_usuario['controle_praca'] == "Y" and $row_solicitacao['praca'] == $row_usuario['praca']) {
									$filtro_solicitacao_solicitante .= " and (praca = '".$row_solicitacao['praca']."' and IdUsuario <> ".$row_solicitacao['id_usuario_responsavel']." ) ";
								}

								mysql_select_db($database_conexao, $conexao);
								$query_solicitante = "
								SELECT 
									IdUsuario, nome, praca 
								FROM 
									usuarios 
								WHERE 
									status = 1 and 
									$filtro_solicitacao_solicitante 
								ORDER BY 
									praca, nome ASC
								";
								$solicitante = mysql_query($query_solicitante, $conexao) or die(mysql_error());
								$row_solicitante = mysql_fetch_assoc($solicitante);
								$totalRows_solicitante = mysql_num_rows($solicitante);
								?>
								solicitante:<br>
								<select name="usuario_responsavel" id="usuario_responsavel">
									<option value="">Escolha o solicitante ...</option>
									<?php
									do {
									?>
										<option value="<?php echo $row_solicitante['IdUsuario'] ?>"><?php echo $row_solicitante['nome'] ?> [<?php echo $row_solicitante['praca'] ?>]</option>
									<?php
									} while ($row_solicitante = mysql_fetch_assoc($solicitante));
									$rows = mysql_num_rows($solicitante);
									if ($rows > 0) {
										mysql_data_seek($solicitante, 0);
										$row_solicitante = mysql_fetch_assoc($solicitante);
									}
									?>
								</select>
								<? mysql_free_result($solicitante); ?>
							</div>
						<? } ?>
						<!-- fim - Solicitante -->


						<!-- Versão que foi realizado tratamento -->
						<? if (
							($_GET['situacao'] == "geral" and $_GET['acao'] == "Concluir execução") or
							(($_GET['situacao'] == "em testes" and $_GET['acao'] == "Concluir testes") and $_GET["resposta"] == "") or
							(($_GET['situacao'] == "geral" and $_GET['acao'] == "Devolver") and $_GET["resposta"] == "") or
							($_GET['acao'] == "Encaminhar" and $_GET["resposta"] == "") or
							(($_GET['situacao'] == "geral" and $_GET['acao'] == "Aprovar") and $_GET["resposta"] == "") or
							(($_GET['situacao'] == "geral" and $_GET['acao'] == "Postar orçamento") and $_GET["resposta"] == "") or
							(($_GET['situacao'] == "geral" and $_GET['acao'] == "Solicitar orçamento") and $_GET["resposta"] == "") or
							(($_GET['situacao'] == "geral" and $_GET['acao'] == "Concluir") and $_GET["resposta"] == "")
						) { ?>
							<?
							mysql_select_db($database_conexao, $conexao);
							$query_versao_acao = "SELECT * FROM geral_tipo_versao WHERE status = 1 ORDER BY titulo ASC";
							$versao_acao = mysql_query($query_versao_acao, $conexao) or die(mysql_error());
							$row_versao_acao = mysql_fetch_assoc($versao_acao);
							$totalRows_versao_acao = mysql_num_rows($versao_acao);
							?>
							<script type="text/javascript">
								// adiciona-remove versao_acao input
								$(function() {
									var linha_div = $('#linha_div');
									var i = $('#linha_div p').size() + 1;

									$('#addScnt').live('click', function() {


										$('<p><label for="versao_acao_label"><label for="versao_acao_label"><select name="versao_acao_' + i + '" id="versao_acao_' + i + '"><option value="">Escolha ...</option><?php
										do {
										?><option value="<?php echo $row_versao_acao['titulo'] ?>"><?php echo $row_versao_acao['titulo'] ?></option><?php
										} while ($row_versao_acao = mysql_fetch_assoc($versao_acao));
										$rows = mysql_num_rows($versao_acao);
										if ($rows > 0) {
										mysql_data_seek($versao_acao, 0);
										$row_versao_acao = mysql_fetch_assoc($versao_acao);
										}
										?></select></label></label> <a href="#" id="remScnt">Remover</a></p>').appendTo(linha_div);
										i++;
										return false;
									});

									$('#remScnt').live('click', function() {
										if (i > 1) {
											$(this).parents('p').remove();
											i++;
										}
										return false;
									});
								});
								// fim - adiciona-remove versao_acao input
							</script>
							<div style="padding-top: 10px;">
								Versão que foi realizada ação/tratamento: <br>
								<a href="#" id="addScnt">Adicionar mais versões</a>
								<br>
								<div id="linha_div">
										<label for="versao_acao_label">
											<select name="versao_acao" id="versao_acao">
												<option value="">Escolha ...</option>
												<?php
												do {
												?>
													<option value="<?php echo $row_versao_acao['titulo'] ?>"><?php echo $row_versao_acao['titulo'] ?></option>
												<?php
												} while ($row_versao_acao = mysql_fetch_assoc($versao_acao));
												$rows = mysql_num_rows($versao_acao);
												if ($rows > 0) {
													mysql_data_seek($versao_acao, 0);
													$row_versao_acao = mysql_fetch_assoc($versao_acao);
												}
												?>
											</select>
										</label>
								</div>
							</div>
							<? mysql_free_result($versao_acao); ?>
						<? } ?>
						<!-- Versão que foi realizado tratamento -->


						<!-- Valor -->
						<? if (
							($_GET['situacao'] == "geral" and $_GET['acao'] == "Postar orçamento")
						) { ?>
							<div style="padding-top: 10px;">
								Orçamento (Valor):<br>
								R$ <input type="text" name="orcamento" id="orcamento" />
							</div>
						<? } ?>
						<!-- fim - Valor -->


						<!-- Prazo para desenvolvimento do orçamento -->
						<? if (
							($_GET['situacao'] == "geral" and $_GET['acao'] == "Postar orçamento")
						) { ?>
							<div style="padding-top: 10px;">
								Prazo para desenvolvimento após a aprovação do cliente:<br>
								<input type="text" name="prazo_desenvolvimento_orcamento" id="prazo_desenvolvimento_orcamento" /> dias
							</div>
						<? } ?>
						<!-- fim - Prazo para desenvolvimento do orçamento -->


						<!-- Orçamento OS -->
						<? if (
							($_GET['situacao'] == "aprovada" and $_GET['acao'] == "Encaminhar" and $row_solicitacao['orcamento'] != "")
						) { ?>
							<div style="padding-top: 10px;">
								Número da OS:<br>
								<input type="text" name="orcamento_os" id="orcamento_os" value="<? echo $row_solicitacao['orcamento_os']; ?>" style="width: 150px" maxlength="10" />
							</div>
						<? } ?>
						<!-- fim - Orçamento OS -->


						<!-- titulo -->
						<? if (
							($_GET['situacao'] == "editar" and $_GET['acao'] == "Alterar título")
						) { ?>
							<div style="padding-top: 10px;">
								Título:<br>
								<input name="titulo" type="text" id="titulo" size="100" value="<? echo $row_solicitacao['titulo']; ?>" />
							</div>
						<? } ?>
						<!-- fim - titulo -->


						<!-- data executável -->
						<? if (
							($_GET['situacao'] == "editar" and $_GET['acao'] == "Alterar data do executável")
						) { ?>
							<div style="padding-top: 10px;">
								Data do executável:<br>
								<input name="data_executavel" type="text" id="data_executavel" size="30" />
							</div>
						<? } ?>
						<!-- fim - data executável -->


						<!-- hora executável -->
						<? if (
							($_GET['situacao'] == "editar" and $_GET['acao'] == "Alterar hora do executável")
						) { ?>
							<div style="padding-top: 10px;">
								Hora do executável:<br>
								<input name="hora_executavel" type="text" id="hora_executavel" size="20" />
							</div>
						<? } ?>
						<!-- fim - hora executável -->


						<!-- tipo -->
						<? if (
							($_GET['situacao'] == "editar" and $_GET['acao'] == "Alterar tipo da solicitação")
						) { ?>
							<div style="padding-top: 10px;">
								<?
								mysql_select_db($database_conexao, $conexao);
								$query_tipo = "SELECT * FROM solicitacao_tipo_solicitacao ORDER BY titulo ASC";
								$tipo = mysql_query($query_tipo, $conexao) or die(mysql_error());
								$row_tipo = mysql_fetch_assoc($tipo);
								$totalRows_tipo = mysql_num_rows($tipo);
								?>
								Tipo:<br>
								<select name="tipo" id="tipo">
									<option value="">Escolha o tipo ...</option>
									<?php
									do {
									?>
										<? if ($row_tipo['titulo'] != $row_solicitacao['tipo']) { ?>
											<option value="<?php echo $row_tipo['titulo'] ?>"><?php echo $row_tipo['titulo'] ?></option>
										<? } ?>
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

							<!-- Implementação -->
							<? if ($row_solicitacao['id_usuario_responsavel'] == $row_usuario['IdUsuario']) { ?>
								<div id="implementacao_mensagem" style="padding-top: 10px;">
									A solicitação para implementação será realizada na versão desenvolvimento do sistema:
									<br>
									<fieldset id="implementacao_mensagem_sim_nao">
										<input id="implementacao_mensagem_sim_nao" name="implementacao_mensagem_sim_nao" type="radio" value="s" /> concordo
										<input id="implementacao_mensagem_sim_nao" name="implementacao_mensagem_sim_nao" type="radio" value="n" /> não concordo
									</fieldset>
									<label for="implementacao_mensagem_sim_nao" class="error">Escolha uma das alternativas</label>
								</div>

								<div id="implementacao_nao" style="padding-top: 10px;">
									Preencha abaixo a justificativa para implementação na versão estável ou versão desejada. A justificativa será passível de análise:
									<br>
									<textarea name="implementacao_nao_justificativa" id="implementacao_nao_justificativa" style="width: 760px; height: 90px; margin-bottom: 5px;"></textarea>
								</div>
							<? } ?>
							<!-- Implementação -->

						<? } ?>
						<!-- fim - tipo -->


						<!-- implementacao_mensagem_sim_nao -->
						<? if (
							($_GET['situacao'] == "editar" and $_GET['acao'] == "Alterar versão da implementação")
						) { ?>
							<!-- Implementação -->
							<? if ($row_solicitacao['id_usuario_responsavel'] == $row_usuario['IdUsuario']) { ?>
								<div id="implementacao_mensagem" style="padding-top: 10px;">
									A solicitação para implementação será realizada na versão desenvolvimento do sistema:
									<br>
									<fieldset id="implementacao_mensagem_sim_nao" name="implementacao_mensagem_sim_nao">
										<input <?php if (!(strcmp($row_solicitacao['implementacao_mensagem_sim_nao'], "s"))) {
													echo "checked=\"checked\"";
												} ?> id="implementacao_mensagem_sim_nao" name="implementacao_mensagem_sim_nao" type="radio" value="s" /> concordo
										<input <?php if (!(strcmp($row_solicitacao['implementacao_mensagem_sim_nao'], "n"))) {
													echo "checked=\"checked\"";
												} ?> id="implementacao_mensagem_sim_nao" name="implementacao_mensagem_sim_nao" type="radio" value="n" /> não concordo
									</fieldset>
									<label for="implementacao_mensagem_sim_nao" class="error">Escolha uma das alternativas</label>
								</div>

								<div id="implementacao_nao" style="padding-top: 10px;">
									Preencha abaixo a justificativa para implementação na versão estável ou versão desejada. A justificativa será passível de análise:
									<br>
									<textarea name="implementacao_nao_justificativa" id="implementacao_nao_justificativa" style="width: 760px; height: 90px; margin-bottom: 5px;"><?php echo $row_solicitacao['implementacao_nao_justificativa']; ?></textarea>
								</div>
							<? } ?>
							<!-- Implementação -->

						<? } ?>
						<!-- fim - implementacao_mensagem_sim_nao -->


						<!-- prioridade -->
						<? if (
							($_GET['situacao'] == "editar" and $_GET['acao'] == "Alterar prioridade")
						) { ?>

							<div style="padding-top: 10px;">
								<?
								mysql_select_db($database_conexao, $conexao);
								$query_prioridade = "SELECT * FROM solicitacao_tipo_prioridade ORDER BY titulo ASC";
								$prioridade = mysql_query($query_prioridade, $conexao) or die(mysql_error());
								$row_prioridade = mysql_fetch_assoc($prioridade);
								$totalRows_prioridade = mysql_num_rows($prioridade);
								?>
								Prioridade:<br>
								<select name="prioridade" id="prioridade">
									<option value="">Escolha o prioridade ...</option>
									<?php
									do {
									?>
										<option value="<?php echo $row_prioridade['titulo'] ?>"><?php echo $row_prioridade['titulo'] ?></option>
									<?php
									} while ($row_prioridade = mysql_fetch_assoc($prioridade));
									$rows = mysql_num_rows($prioridade);
									if ($rows > 0) {
										mysql_data_seek($prioridade, 0);
										$row_prioridade = mysql_fetch_assoc($prioridade);
									}
									?>
								</select>
								<? mysql_free_result($prioridade); ?>
							</div>

							<div style="padding-top: 10px;" id="prioridade_justificativa_caixa">
								<div class="label_solicitacao2">Justificativa da prioridade*:</div>
								<textarea name="prioridade_justificativa" id="prioridade_justificativa" style="width:760px; height: 80px;" /><? echo $row_suporte['prioridade_justificativa']; ?></textarea>
							</div>

						<? } ?>
						<!-- fim - prioridade -->


						<!-- programa/subprograma -->
						<? if (
							($_GET['situacao'] == "editar" and $_GET['acao'] == "Alterar programa/subprograma")
						) { ?>
							<div style="padding-top: 10px;">
								<?
								mysql_select_db($database_conexao, $conexao);
								$query_geral_tipo_programa = "SELECT * FROM geral_tipo_programa ORDER BY programa ASC";
								$geral_tipo_programa = mysql_query($query_geral_tipo_programa, $conexao) or die(mysql_error());
								$row_geral_tipo_programa = mysql_fetch_assoc($geral_tipo_programa);
								$totalRows_geral_tipo_programa = mysql_num_rows($geral_tipo_programa);
								?>
								Programa:<br>
								<select name="id_programa">
									<option value="">
										Escolha ...
									</option>
									<?php do { ?>
										<option value="<?php echo $row_geral_tipo_programa['id_programa'] ?>" <?php if ((isset($_GET['id_programa'])) and (!(strcmp($row_geral_tipo_programa['id_programa'], $_GET['id_programa'])))) {
																													echo "selected=\"selected\"";
																												} ?>>
											<?php echo $row_geral_tipo_programa['programa'] ?>
										</option>
									<?php
									} while ($row_geral_tipo_programa = mysql_fetch_assoc($geral_tipo_programa));
									$rows = mysql_num_rows($geral_tipo_programa);
									if ($rows > 0) {
										mysql_data_seek($geral_tipo_programa, 0);
										$row_geral_tipo_programa = mysql_fetch_assoc($geral_tipo_programa);
									}
									?>
								</select>

								<br><br>
								Subprograma:<br>
								<select name="id_subprograma">
									<option value="">Escolha um programa primeiro ... </option>
								</select>

								<? mysql_free_result($geral_tipo_programa); ?>
							</div>
						<? } ?>
						<!-- fim - programa/subprograma -->


						<!-- versao -->
						<? if (
							($_GET['situacao'] == "editar" and $_GET['acao'] == "Alterar versão")
						) { ?>
							<div style="padding-top: 10px;">
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
									
									<input  name="versao[]" id="versao" type="checkbox" class="checkbox" 
									value="<? echo $row_versao['IdTipoVersao']; ?>" 
									<? if (in_array($row_versao['IdTipoVersao'], explode(',', $row_solicitacao['versao']))) {  ?>checked="checked"<? } ?>
									/> 
									<? echo $row_versao['titulo']; ?><br>

								<?php } while ($row_versao = mysql_fetch_assoc($versao)); ?>
								</fieldset>

								<label for="versao[]" class="error">Selecione pelo menos uma das versões acima</label>
								<? mysql_free_result($versao); ?>
							</div>
						<? } ?>
						<!-- fim - versao -->


						<!-- distribuicao -->
						<? if (
							($_GET['situacao'] == "editar" and $_GET['acao'] == "Alterar distribuição")
						) { ?>
							<div style="padding-top: 10px;">
								<?
								mysql_select_db($database_conexao, $conexao);
								$query_distribuicao = "SELECT * FROM geral_tipo_distribuicao ORDER BY titulo ASC";
								$distribuicao = mysql_query($query_distribuicao, $conexao) or die(mysql_error());
								$row_distribuicao = mysql_fetch_assoc($distribuicao);
								$totalRows_distribuicao = mysql_num_rows($distribuicao);
								?>
								Distribuição:<br>
								<select name="distribuicao" id="distribuicao">
									<option value="">Escolha o distribuicao ...</option>
									<?php
									do {
									?>
										<option value="<?php echo $row_distribuicao['titulo'] ?>"><?php echo $row_distribuicao['titulo'] ?></option>
									<?php
									} while ($row_distribuicao = mysql_fetch_assoc($distribuicao));
									$rows = mysql_num_rows($distribuicao);
									if ($rows > 0) {
										mysql_data_seek($distribuicao, 0);
										$row_distribuicao = mysql_fetch_assoc($distribuicao);
									}
									?>
								</select>
								<? mysql_free_result($distribuicao); ?>
							</div>
						<? } ?>
						<!-- fim - distribuicao -->


						<!-- banco_de_dados -->
						<? if (
							($_GET['situacao'] == "editar" and $_GET['acao'] == "Alterar banco de dados")
						) { ?>
							<div style="padding-top: 10px;">
								<?
								mysql_select_db($database_conexao, $conexao);
								$query_banco_de_dados = "SELECT * FROM geral_tipo_banco_de_dados ORDER BY titulo ASC";
								$banco_de_dados = mysql_query($query_banco_de_dados, $conexao) or die(mysql_error());
								$row_banco_de_dados = mysql_fetch_assoc($banco_de_dados);
								$totalRows_banco_de_dados = mysql_num_rows($banco_de_dados);
								?>
								Banco de dados:<br>
								<select name="banco_de_dados" id="banco_de_dados">
									<option value="">Escolha o banco de dados ...</option>
									<?php
									do {
									?>
										<option value="<?php echo $row_banco_de_dados['titulo'] ?>"><?php echo $row_banco_de_dados['titulo'] ?></option>
									<?php
									} while ($row_banco_de_dados = mysql_fetch_assoc($banco_de_dados));
									$rows = mysql_num_rows($banco_de_dados);
									if ($rows > 0) {
										mysql_data_seek($banco_de_dados, 0);
										$row_banco_de_dados = mysql_fetch_assoc($banco_de_dados);
									}
									?>
								</select>
								<? mysql_free_result($banco_de_dados); ?>
							</div>
						<? } ?>
						<!-- fim - banco_de_dados -->


						<!-- ecf -->
						<? if (
							($_GET['situacao'] == "editar" and $_GET['acao'] == "Alterar ECF")
						) { ?>
							<div style="padding-top: 10px;">
								<?
								mysql_select_db($database_conexao, $conexao);
								$query_ecf = "SELECT * FROM geral_tipo_ecf ORDER BY titulo ASC";
								$ecf = mysql_query($query_ecf, $conexao) or die(mysql_error());
								$row_ecf = mysql_fetch_assoc($ecf);
								$totalRows_ecf = mysql_num_rows($ecf);
								?>
								ECF:<br>
								<select name="ecf" id="ecf">
									<option value="">Escolha a ECF...</option>
									<?php
									do {
									?>
										<option value="<?php echo $row_ecf['titulo'] ?>"><?php echo $row_ecf['titulo'] ?></option>
									<?php
									} while ($row_ecf = mysql_fetch_assoc($ecf));
									$rows = mysql_num_rows($ecf);
									if ($rows > 0) {
										mysql_data_seek($ecf, 0);
										$row_ecf = mysql_fetch_assoc($ecf);
									}
									?>
								</select>
								<? mysql_free_result($ecf); ?>
							</div>
						<? } ?>
						<!-- fim - ecf -->

						<!-- campo -->
						<? if (
							($_GET['situacao'] == "editar" and $_GET['acao'] == "Alterar campo")
						) { ?>
							<div style="padding-top: 10px;">
								Campo:<br>
								<input name="campo" type="text" id="campo" size="40" />
							</div>
						<? } ?>
						<!-- fim - campo -->

						<!-- numero_revisao_svn_estavel -->
						<? if (($_GET['situacao'] == "geral" and $_GET['acao'] == "Concluir execução") and $_GET["resposta"] == "") { ?>
							<div style="padding-top: 10px;">
								Nº revisão SVN - Estável:<br>
								<input type="text" name="numero_revisao_svn_estavel" id="numero_revisao_svn_estavel" size="40" />
							</div>
						<? } ?>
						<!-- fim - numero_revisao_svn_estavel -->

						<!-- numero_revisao_svn_desenvolvimento -->
						<? if (($_GET['situacao'] == "geral" and $_GET['acao'] == "Concluir execução") and $_GET["resposta"] == "") { ?>
							<div style="padding-top: 10px;">
								Nº revisão SVN - Desenvolvimento:<br>
								<input type="text" name="numero_revisao_svn_desenvolvimento" id="numero_revisao_svn_desenvolvimento" size="40" />
							</div>
						<? } ?>
						<!-- fim - numero_revisao_svn_desenvolvimento -->

						<!-- questionamento -->
						<? if (
							($_GET['acao'] == "Questionar")
						) { ?>
							<div style="padding-top: 10px;">
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

									<? if ($row_solicitacao['situacao'] != "solucionada" or $row_solicitacao['situacao'] == "reprovada") { ?>


										<!-- solicitacante -->
										<? if ($row_solicitacao['id_usuario_responsavel'] != $row_usuario['IdUsuario']) { ?>
											<input name="questionado" id="questionado" type="radio" value="solicitante" validate="required:true"> solicitante
										<? } ?>
										<!-- fim - solicitacante -->


										<!-- operador -->
										<? if ($row_solicitacao['id_operador'] != $row_usuario['IdUsuario']) { ?>
											<input name="questionado" id="questionado" type="radio" value="operador" validate="required:true"> operador
										<? } ?>
										<!-- fim - operador -->


										<!-- analista de orçamento -->
										<? if (
											$row_solicitacao['id_analista_orcamento'] != "" and
											($row_solicitacao['dt_orcamento'] == "" or $row_solicitacao['situacao'] == "em orçamento") and
											$row_solicitacao['id_analista_orcamento'] != $row_usuario['IdUsuario']
										) { ?>
											<input name="questionado" id="questionado" type="radio" value="analista de orçamento" validate="required:true"> analista de orçamento
										<? } ?>
										<!-- fim - analista de orçamento -->


										<!-- executante -->
										<? if (
											$row_solicitacao['id_executante'] != "" and
											($row_solicitacao['dt_conclusao'] == "" or $row_solicitacao['situacao'] == "executada") and
											$row_solicitacao['id_executante'] != $row_usuario['IdUsuario']
										) { ?>
											<input name="questionado" id="questionado" type="radio" value="executante" validate="required:true"> executante
										<? } ?>
										<!-- fim - executante -->


										<!-- testador -->
										<? if (
											$row_solicitacao['id_testador'] != "" and
											$row_solicitacao['dt_conclusao_testes'] == "" and
											$row_solicitacao['id_testador'] != $row_usuario['IdUsuario']
										) { ?>
											<input name="questionado" id="questionado" type="radio" value="testador" validate="required:true"> testador
										<? } ?>
										<!-- fim - testador -->


										<? if ($row_solicitacao['status_questionamento'] != "") { ?>
											<input name="questionado" id="questionado" type="radio" value="" validate="required:true"> <em>responder questionamento</em>
										<? } ?>

									<? } else if ($row_solicitacao['id_operador'] == $row_usuario['IdUsuario']) { ?>

										<input name="questionado" id="questionado" type="radio" value="" checked="checked" validate="required:true"> <em>comentar</em>

									<? } ?>
								</fieldset>
								<label for="questionado" class="error">Selecione quem será questionado</label>
							</div>
						<? } ?>
						<!-- fim - questionamento -->


						<!-- Sugestão -->
						<? if (
							($_GET['situacao'] == "geral" and $_GET['acao'] == "Reprovar") or
							($_GET['situacao'] == "em orçamento" and $_GET['acao'] == "Reprovar")
						) { ?>
							<style type="text/css">
								.block {
									display: block;
								}

								form.cmxform label.error {
									display: none;
								}
							</style>
							<div style="padding-top: 10px;">
								Deixar esta solicitação como sugestão?<br>
								<fieldset style="border: 1px solid #CCCCCC; padding: 5px;">
									<input name="sugestao" id="sugestao" type="radio" value="sim" validate="required:true"> sim
									<input name="sugestao" id="sugestao" type="radio" value="nao" validate="required:true"> não
								</fieldset>
								<label for="sugestao" class="error">Selecione uma das opções</label>
							</div>
						<? } ?>
						<!-- fim - Sugestão -->


						<!-- Concluir/Reprovar -->
						<? if (
							($_GET['situacao'] == "geral" and $_GET['acao'] == "Concluir") or
							($_GET['situacao'] == "geral" and $_GET['acao'] == "Reprovar")
						) { ?>
							<style type="text/css">
								.block {
									display: block;
								}

								form.cmxform label.error {
									display: none;
								}
							</style>
							<div style="padding-top: 10px;">
								Solicitar validação do solicitante?<br>
								<fieldset style="border: 1px solid #CCCCCC; padding: 5px;">
									<input name="confirmacao_concluir_reprovar" id="confirmacao_concluir_reprovar" type="radio" value="sim" validate="required:true" checked="checked"> sim
									<input name="confirmacao_concluir_reprovar" id="confirmacao_concluir_reprovar" type="radio" value="nao" validate="required:true" disabled="disabled"> não
								</fieldset>
								<label for="confirmacao_concluir_reprovar" class="error">Selecione uma das opções</label>
							</div>
						<? } ?>
						<!-- Concluir/Reprovar -->


						<!-- Devolver para -->
						<? if ($_GET['acao'] == "Devolver" and $_GET['resposta'] == "") { ?>
							<style type="text/css">
								.block {
									display: block;
								}
								form.cmxform label.error {
									display: none;
								}
							</style>
							<div style="padding-top: 10px;">
								Devolver para:<br>
								<fieldset style="border: 1px solid #CCCCCC; padding: 5px;">

									<? $devolver_ativo = "n"; // irá verificar se existe execução/teste, se não existe então marca o operador para devolução da solicitação. ?>

									<?
									if ($row_solicitacao['dt_conclusao'] != "") {
										$devolver_ativo = "s";
									}
									if ($row_solicitacao['dt_conclusao_testes'] != "") {
										$devolver_ativo = "s";
									}
									?>

									<? 
									if (
										($row_solicitacao['dt_recebimento'] != "" or $row_solicitacao['dt_aprovacao_reprovacao'] != "") and 
										$row_solicitacao['dt_conclusao'] == "" and 
										$row_solicitacao['dt_conclusao_testes'] == "" 
									) { 
									?>
									<input name="devolver_para" id="devolver_para" type="radio" value="operador" validate="required:true" <? if ($devolver_ativo == "n") { echo "checked"; } ?>>
									operador
									<? } ?>

									<? if ($row_solicitacao['dt_conclusao'] != "") { ?>
										<input name="devolver_para" id="devolver_para" type="radio" value="executante" validate="required:true"> executante
									<? } ?>

									<? 
									if (
										$row_solicitacao['dt_conclusao_testes'] != "" or 
										($row_solicitacao['dt_conclusao_testes'] == "" and $row_solicitacao['situacao'] == "em validação") 
										) { 
									?>
										<input name="devolver_para" id="devolver_para" type="radio" value="testador" validate="required:true"> testador
									<? } ?>

								</fieldset>
								<label for="devolver_para" class="error">Selecione uma das opções</label>
							</div>
						<? } ?>
						<!-- fim - Devolver para -->

						<!-- devolucao_motivo -->
						<? if ($_GET['acao'] == "Devolver" and $_GET['resposta'] == "") { ?>

							<div style="padding-top: 10px;">
								<?
								mysql_select_db($database_conexao, $conexao);
								$query_devolucao = "SELECT * FROM solicitacao_tipo_devolucao ORDER BY titulo ASC";
								$devolucao = mysql_query($query_devolucao, $conexao) or die(mysql_error());
								$row_devolucao = mysql_fetch_assoc($devolucao);
								$totalRows_devolucao = mysql_num_rows($devolucao);
								?>
								Motivo da devolução:<br>
								<select name="devolucao_motivo" id="devolucao_motivo">
									<option value="">Escolha...</option>
									<?php do { ?>
										<option value="<?php echo $row_devolucao['IdTipoDevolucao'] ?>"><?php echo $row_devolucao['titulo'] ?></option>
									<?php } while ($row_devolucao = mysql_fetch_assoc($devolucao)); ?>
								</select>
								<? mysql_free_result($devolucao); ?>
							</div>

						<? } ?>
						<!-- fim - devolucao_motivo -->

						<!-- tempo gasto -->
						<? if (
							($_GET['situacao'] == "geral" and $_GET['acao'] == "Concluir execução") or
							($_GET['situacao'] == "em testes" and $_GET['acao'] == "Concluir testes") or
							($_GET['situacao'] == "em validação" and $_GET['acao'] == "Concluir validação") or
							($_GET['situacao'] == "geral" and $_GET['acao'] == "Aprovar") or
							($_GET['situacao'] == "geral" and $_GET['acao'] == "Solicitar orçamento") or
							($_GET['situacao'] == "geral" and $_GET['acao'] == "Postar orçamento") or
							($_GET['situacao'] == "geral" and $_GET['acao'] == "Concluir") or

							($row_solicitacao['situacao'] == "em testes" and $_GET['situacao'] == "geral" and $_GET['acao'] == "Devolver") or 

							$_GET['acao'] == "Encaminhar"
						) { ?>
							<div style="padding-top: 10px;">
								Tempo gasto (dias - horas - minutos):<br>
								<input name="tempo_gasto" type="text" id="tempo_gasto" size="30" />
							</div>
						<? } ?>
						<!-- fim - tempo gasto -->

						<!-- Observação -->
						<? if (
							($_GET['situacao'] == "criada") or
							($_GET['situacao'] == "recebida") or
							($_GET['situacao'] == "em análise") or
							($_GET['situacao'] == "analisada") or
							($_GET['situacao'] == "em orçamento") or
							($_GET['situacao'] == "aprovada") or
							($_GET['situacao'] == "em execução") or
							($_GET['situacao'] == "executada") or
							($_GET['situacao'] == "em testes") or
							($_GET['situacao'] == "testada") or
							($_GET['situacao'] == "em validação") or
							($_GET['situacao'] == "geral") or
							($_GET['situacao'] == "editar") or
							($_GET['acao'] == "Questionar")
						) { ?>
							<div style="padding-top: 10px;">
								<?
								if ($_GET['acao'] == "Alterar medida tomada") {
									echo "Medida tomada: ";
								} else if ($_GET['acao'] == "Alterar anomalia") {
									echo "Descrição: ";
								} else {
									echo "Observações: ";
								}
								?>
								<br>
								<textarea name="observacao" id="observacao" style="width: 760px; height: 90px; margin-bottom: 5px;" /><?
																																		if ($_GET['acao'] == "Alterar medida tomada") {
																																			echo str_replace("<br />", "", $row_solicitacao['medida_tomada']);
																																		}
																																		if ($_GET['acao'] == "Alterar anomalia") {
																																			// anomalia
																																			$colname_anomalia = "-1";
																																			if (isset($_GET['id_solicitacao'])) {
																																				$colname_anomalia = $_GET['id_solicitacao'];
																																			}
																																			mysql_select_db($database_conexao, $conexao);
																																			$query_anomalia = sprintf("SELECT descricao FROM solicitacao_descricoes WHERE id_solicitacao = %s and tipo_postagem='Nova Solicitação' ORDER BY id ASC", GetSQLValueString($colname_anomalia, "int"));
																																			$anomalia = mysql_query($query_anomalia, $conexao) or die(mysql_error());
																																			$row_anomalia = mysql_fetch_assoc($anomalia);
																																			$totalRows_anomalia = mysql_num_rows($anomalia);
																																			echo str_replace("<br />", "", $row_anomalia['descricao']);
																																			mysql_free_result($anomalia);
																																			// fim - anomalia
																																		}
																																		?></textarea>
							</div>
						<? } ?>
						<!-- fim - Observação -->


						<!-- Botões -->
						<div style="padding-top: 10px;">

							<input name="id_solicitacao" type="hidden" value="<?php echo $row_solicitacao['id']; ?>" />
							<input type="hidden" name="MM_update" value="form" />
							<input type="submit" name="botao_salvar" id="botao_salvar" value="Salvar" class="botao_geral2" style="width: 70px" />

							<!-- Registrar reclamação ========================================================================================================================================= -->
							<? if ($row_solicitacao['situacao'] != "solucionada" and $row_solicitacao['situacao'] != "reprovada") { ?>
								<a href="suporte_gerar.php?tipo_suporte=r&inloco=n&cobranca=n&cliente=<? echo $row_solicitacao['codigo_empresa']; ?>6&contrato=<? echo $row_solicitacao['contrato']; ?>&reclamacao_solicitacao=<? echo $row_solicitacao['id']; ?>" target="_blank" id="botao_geral2">Registrar reclamação</a>
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
mysql_free_result($solicitacao);
mysql_free_result($usuario);
?>