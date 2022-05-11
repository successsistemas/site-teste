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

// venda
$colname_venda = "-1";
if (isset($_GET['id_venda'])) {
	$colname_venda = $_GET['id_venda'];
}
mysql_select_db($database_conexao, $conexao);
$query_venda = sprintf(
	"
SELECT venda.*, 
(SELECT nome FROM usuarios WHERE venda.id_usuario_responsavel = usuarios.IdUsuario) AS usuario_responsavel
FROM venda 
WHERE venda.id = %s",
	GetSQLValueString($colname_venda, "int")
);
$venda = mysql_query($query_venda, $conexao) or die(mysql_error());
$row_venda = mysql_fetch_assoc($venda);
$totalRows_venda = mysql_num_rows($venda);
// fim - venda

// acessso --------------------------------------------------------------------------------------------------------------------------------------------------------------
$acesso = 0;

if (
	(
		($row_venda['status_flag'] != "f") and
		(
			(
				($row_usuario['nivel_venda'] == 1 and $row_usuario['praca'] == $row_venda['praca']) or
				($row_usuario['nivel_venda'] == 2 and $row_usuario['praca'] == $row_venda['praca']) or
				($row_usuario['nivel_venda'] == 3 and $row_usuario['praca'] == $row_venda['praca']) or
				($row_usuario['controle_praca'] == "Y" and $row_usuario['praca'] == $row_venda['praca']) or
				$row_venda['id_usuario_responsavel'] == $row_usuario['IdUsuario'] or
				$row_usuario['praca'] == $row_venda['praca'] or
				$row_usuario['praca'] == 'MATRIZ'))) or (
		($row_venda['status_flag'] == "f" and $_GET['situacao'] == "editar") and
		(
			(
				($row_usuario['nivel_venda'] == 1 and $row_usuario['praca'] == $row_venda['praca']) or
				($row_usuario['nivel_venda'] == 2 and $row_usuario['praca'] == $row_venda['praca']) or
				($row_usuario['nivel_venda'] == 3 and $row_usuario['praca'] == $row_venda['praca']) or
				$row_usuario['controle_venda'] == "Y" or
				($row_usuario['controle_praca'] == "Y" and $row_usuario['praca'] == $row_venda['praca']) or
				$row_venda['id_usuario_responsavel'] == $row_usuario['IdUsuario'] or
				$row_usuario['praca'] == $row_venda['praca'])))
) {

	$acesso = 1; // autorizado

} else {

	$acesso = 0; // não autorizado

}

if ($acesso == 0) {
	//echo "Acesso não autorizado !";
	echo "<script>window.top.location = 'venda.php?padrao=sim&" . $venda_padrao . "';</script>";
	exit;
}
// fim - acesso ---------------------------------------------------------------------------------------------------------------------------------------------------------

// converter entrada de data em portugues para ingles
if (isset($_POST['data_agendamento_inicio']) and $_POST['data_agendamento_inicio'] != "") {
	$data_agendamento_inicio_data_inicio = substr($_POST['data_agendamento_inicio'], 0, 10);
	$data_agendamento_inicio_hora = substr($_POST['data_agendamento_inicio'], 10, 9);
	$_POST['data_agendamento_inicio'] = implode("-", array_reverse(explode("-", $data_agendamento_inicio_data_inicio))) . $data_agendamento_inicio_hora;
} else {
	$_POST['data_agendamento_inicio'] = "0000-00-00 00:00:00";
}

if (isset($_POST['data_agendamento']) and $_POST['data_agendamento'] != "") {
	$data_agendamento_data = substr($_POST['data_agendamento'], 0, 10);
	$data_agendamento_hora = substr($_POST['data_agendamento'], 10, 9);
	$_POST['data_agendamento'] = implode("-", array_reverse(explode("-", $data_agendamento_data))) . $data_agendamento_hora;
} else {
	$_POST['data_agendamento'] = "0000-00-00 00:00:00";
}

if (isset($_POST['data_contrato']) and $_POST['data_contrato'] != "") {
	$data_contrato_data = substr($_POST['data_contrato'], 0, 10);
	$_POST['data_contrato'] = implode("-", array_reverse(explode("-", $data_contrato_data)));
} else {
	$_POST['data_contrato'] = "0000-00-00";
}
// fim - converter entrada de data em portugues para ingles - fim

// agenda_tipo
$agenda_tipo = @$_GET['agenda_tipo'];
if ($agenda_tipo == "treinamento") {

	$agenda_tipo = "treinamento";
	$agenda_tipo_titulo = "treinamento";
} else if ($agenda_tipo == "implantacao") {

	$agenda_tipo = "implantacao";
	$agenda_tipo_titulo = "implantação";
} else {

	$agenda_tipo = "treinamento";
	$agenda_tipo_titulo = "treinamento";
}
// fim - agenda_tipo

// agenda_$agenda_tipo - para editar/cancelar
$colname_agenda = "-1";
if (isset($_GET['id_venda'])) {
	$colname_agenda = $_GET['id_venda'];
}

$colname_agenda2 = "-1";
if (isset($_GET['id_agenda'])) {
	$colname_agenda2 = $_GET['id_agenda'];
}

mysql_select_db($database_conexao, $conexao);
$query_agenda = sprintf("
SELECT * 
FROM agenda 
WHERE id_venda_$agenda_tipo = %s and id_agenda = %s 
ORDER BY data ASC", GetSQLValueString($colname_agenda, "text"), GetSQLValueString($colname_agenda2, "text"));
$agenda = mysql_query($query_agenda, $conexao) or die(mysql_error());
$row_agenda = mysql_fetch_assoc($agenda);
$totalRows_agenda = mysql_num_rows($agenda);
// fim - agenda - para editar/cancelar

// agenda_contador (calculo) ------------------------------------------------------------------
$contador_segundo = $row_venda[$agenda_tipo . '_tempo'] * 60;
$contador_segundo_finalizado = 0;
$contador_segundo_agendado = 0;
$contador_segundo_cancelado = 0;
$contador_segundo_restante = $contador_segundo;

// agenda_contador
$colname_agenda_contador = "-1";
if (isset($_GET['id_venda'])) {
	$colname_agenda_contador = $_GET['id_venda'];
}
mysql_select_db($database_conexao, $conexao);
$query_agenda_contador = sprintf("
SELECT data_inicio, data, status  
FROM agenda 
WHERE id_venda_$agenda_tipo = %s 
ORDER BY data ASC", GetSQLValueString($colname_agenda_contador, "text"));
$agenda_contador = mysql_query($query_agenda_contador, $conexao) or die(mysql_error());
$row_agenda_contador = mysql_fetch_assoc($agenda_contador);
$totalRows_agenda_contador = mysql_num_rows($agenda_contador);
// fim - agenda_contador

if ($totalRows_agenda_contador > 0) {

	do {

		$data_ini = strtotime($row_agenda_contador['data_inicio']);
		$data_final = strtotime($row_agenda_contador['data']);

		if ($row_agenda_contador['status'] == "f") {
			$contador_segundo_finalizado = (strtotime($row_agenda_contador['data']) - strtotime($row_agenda_contador['data_inicio'])) + $contador_segundo_finalizado;
		}

		if ($row_agenda_contador['status'] == "a") {
			$contador_segundo_agendado = (strtotime($row_agenda_contador['data']) - strtotime($row_agenda_contador['data_inicio'])) + $contador_segundo_agendado;
		}

		if ($row_agenda_contador['status'] == "c") {
			$contador_segundo_cancelado = (strtotime($row_agenda_contador['data']) - strtotime($row_agenda_contador['data_inicio'])) + $contador_segundo_cancelado;
		}

		$contador_segundo_restante = $contador_segundo - ($contador_segundo_finalizado + $contador_segundo_agendado);
	} while ($row_agenda_contador = mysql_fetch_assoc($agenda_contador));
}
mysql_free_result($agenda_contador);
// fim - agenda_contador (calculo) -----------------------------------------------------------

// venda_validade
mysql_select_db($database_conexao, $conexao);
$query_venda_validade =  sprintf(
	"
								 SELECT venda_validade.*, 
								 (SELECT nome FROM usuarios WHERE venda_validade.id_usuario_responsavel_solicitacao = usuarios.IdUsuario) as usuarios_nome_responsavel_solicitacao, 
								 (SELECT nome FROM usuarios WHERE venda_validade.id_usuario_responsavel_aceite_recusa = usuarios.IdUsuario) as usuarios_nome_responsavel_aceite_recusa  
								 FROM venda_validade 
								 WHERE venda_validade.id_venda = %s 
								 ORDER BY venda_validade.data_atual DESC",
	GetSQLValueString($row_venda['id'], "int")
);
$venda_validade = mysql_query($query_venda_validade, $conexao) or die(mysql_error());
$row_venda_validade = mysql_fetch_assoc($venda_validade);
$totalRows_venda_validade = mysql_num_rows($venda_validade);
// fim - venda_validade

// venda_contato
mysql_select_db($database_conexao, $conexao);
$query_venda_contato =  sprintf(
	"
								 SELECT venda_contato.*, usuarios.nome AS usuarios_nome 
								 FROM venda_contato 
								 LEFT JOIN usuarios ON venda_contato.id_usuario_responsavel = usuarios.IdUsuario 
								 WHERE venda_contato.id_venda = %s 
								 ORDER BY venda_contato.id DESC",
	GetSQLValueString($row_venda['id'], "int")
);
$venda_contato = mysql_query($query_venda_contato, $conexao) or die(mysql_error());
$row_venda_contato = mysql_fetch_assoc($venda_contato);
$totalRows_venda_contato = mysql_num_rows($venda_contato);
// fim - venda_contato

// prospeccao
mysql_select_db($database_conexao, $conexao);
$query_prospeccao = sprintf("SELECT id, tipo_cliente FROM prospeccao WHERE id = %s", GetSQLValueString($row_venda['id_prospeccao'], "int"));
$prospeccao = mysql_query($query_prospeccao, $conexao) or die(mysql_error());
$row_prospeccao = mysql_fetch_assoc($prospeccao);
$totalRows_prospeccao = mysql_num_rows($prospeccao);
// fim - prospeccao

if (((isset($_POST["MM_update"])) and ($_POST["MM_update"] == "form")) or ((isset($_GET["MM_update"])) and ($_GET["MM_update"] == "form"))) {

	require_once('funcao_formata_data.php');
	require_once('venda_funcao_update.php');

	// interacao **********************************************************************************************************
	$interacao = funcao_venda_interacao($row_venda['id'], @$_GET['interacao']);
	if ($interacao == 1 and @$_GET['interacao'] <> NULL) {
		echo "<script>alert('Foi realizada alguma interação anterior a esta, assim, a ação atual não será gravada. Realize uma nova ação após a atualização da página.');</script>";
		$redirGoTo = "venda_editar.php?id_venda=" . $_GET['id_venda'];
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

		$responsavel_id = $row_usuario_selecionado['IdUsuario'];
		$responsavel_nome = $row_usuario_selecionado['nome'];

		// analisada
		if ($_GET['situacao'] == "analisada") {

			$dados_venda = array(
				"interacao" => $row_venda['interacao'] + 1,
				"situacao" => "analisada",
				"status" => "encaminhada para usuario responsavel",

				"id_usuario_responsavel" => $responsavel_id,
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

			$dados_venda = array(
				"interacao" => $row_venda['interacao'] + 1,
				"situacao" => "analisada",
				"status" => "encaminhada para usuario responsavel",

				"id_usuario_responsavel" => $responsavel_id,
				"usuario_responsavel_leu" => "",

				"encaminhamento_id" => $row_venda['id_usuario_responsavel'],
				"encaminhamento_data_inicio" => "",
				"encaminhamento_data" => "",

				"status_devolucao" => "",
				"status_recusa" => ""
			);
		}
		// fim - em execução

		$dados_venda_descricao = array(
			"id_venda" => $row_venda['id'],
			"id_usuario_responsavel" => $row_usuario['IdUsuario'],
			"descricao" => "Encaminhada para novo responsável<br>Para: " . $responsavel_nome . "<br>" . $_POST['observacao'],
			"data" => date("Y-m-d H:i:s"),
			"tipo_postagem" => "Escolha de novo responsável"
		);

		mysql_free_result($usuario_selecionado);

		funcao_venda_update($row_venda['id'], $dados_venda, $dados_venda_descricao);
	}
	// fim - Encaminhar
	//---------------------------------------------------------------------------------------------------------------------------------------------------------------

	//---------------------------------------------------------------------------------------------------------------------------------------------------------------
	// ACEITAR ---------------------------------------------------------------------------------------------------------------------------------------
	//---------------------------------------------------------------------------------------------------------------------------------------------------------------
	// aceitar ------------------------------------------------------------------------------------------------------------
	if ($_GET['acao'] == "Aceitar" and $_GET["resposta"] == "" and $row_venda['status_recusa'] != "1") { // aceitar

		// analisada
		if ($_GET['situacao'] == "analisada" and $row_venda['status_devolucao'] == "") {

			$dados_venda = array(
				"interacao" => $row_venda['interacao'] + 1,
				"situacao" => "em execução",
				"status" => "pendente usuario responsavel",

				"encaminhamento_id" => "",
				"encaminhamento_data_inicio" => "",
				"encaminhamento_data" => "",

				"status_devolucao" => "",
				"status_recusa" => ""
			);
			$dados_venda_descricao = array(
				"id_venda" => $row_venda['id'],
				"id_usuario_responsavel" => $row_usuario['IdUsuario'],
				"descricao" => $_POST['observacao'],
				"data" => date("Y-m-d H:i:s"),
				"tipo_postagem" => "Venda aceita por usuário responsável"
			);

			funcao_venda_update($row_venda['id'], $dados_venda, $dados_venda_descricao);
		}
		// fim - analisada

	}
	// fim - aceitar -------------------------------------------------------------------------------------------------------

	// aceitar recusa ------------------------------------------------------------------------------------------------------
	if ($_GET['acao'] == "Aceitar" and $_GET["resposta"] == "" and $row_venda['status_recusa'] == "1") {

		// analisada
		if ($_GET['situacao'] == "analisada") {

			$dados_venda = array(
				"interacao" => $row_venda['interacao'] + 1,
				"situacao" => "em execução",
				"status" => "pendente usuario responsavel",

				"id_usuario_responsavel" => $row_venda['encaminhamento_id'],

				"encaminhamento_id" => "",
				"encaminhamento_data_inicio" => "",
				"encaminhamento_data" => "",

				"status_devolucao" => "",
				"status_recusa" => ""
			);
			$dados_venda_descricao = array(
				"id_venda" => $row_venda['id'],
				"id_usuario_responsavel" => $row_usuario['IdUsuario'],
				"descricao" => $_POST['observacao'],
				"data" => date("Y-m-d H:i:s"),
				"tipo_postagem" => "Recusa aceita"
			);

			funcao_venda_update($row_venda['id'], $dados_venda, $dados_venda_descricao);
		}
		// fim - analisada

	}
	// fim - aceitar recusa ------------------------------------------------------------------------------------------------
	//---------------------------------------------------------------------------------------------------------------------------------------------------------------


	//---------------------------------------------------------------------------------------------------------------------------------------------------------------
	// RECUSAR ---------------------------------------------------------------------------------------------------------------------------------------
	//---------------------------------------------------------------------------------------------------------------------------------------------------------------
	// recusar ------------------------------------------------------------------------------------------------------------
	if ($_GET['acao'] == "Recusar" and $_GET["resposta"] == "" and $row_venda['status_recusa'] != "1") {

		// analisada
		if ($_GET['situacao'] == "analisada" and $row_venda['status_devolucao'] == "") {

			$dados_venda = array(
				"interacao" => $row_venda['interacao'] + 1,
				"status" => "pendente usuario responsavel",
				"status_devolucao" => "",
				"status_recusa" => "1"
			);
			$dados_venda_descricao = array(
				"id_venda" => $row_venda['id'],
				"id_usuario_responsavel" => $row_usuario['IdUsuario'],
				"descricao" => $_POST['observacao'],
				"data" => date("Y-m-d H:i:s"),
				"tipo_postagem" => "Venda recusada por usuário responsável"
			);

			funcao_venda_update($row_venda['id'], $dados_venda, $dados_venda_descricao);
		}
		// fim - analisada	

	}
	// fim - recusar ------------------------------------------------------------------------------------------------------

	// recusar recusa -----------------------------------------------------------------------------------------------------
	if ($_GET['acao'] == "Recusar" and $_GET["resposta"] == "" and $row_venda['status_recusa'] == "1") {

		// analisada
		if ($_GET['situacao'] == "analisada") {

			$dados_venda = array(
				"interacao" => $row_venda['interacao'] + 1,
				"status" => "encaminhada para usuario responsavel",
				"status_devolucao" => "",
				"status_recusa" => ""
			);
			$dados_venda_descricao = array(
				"id_venda" => $row_venda['id'],
				"id_usuario_responsavel" => $row_usuario['IdUsuario'],
				"descricao" => $_POST['observacao'],
				"data" => date("Y-m-d H:i:s"),
				"tipo_postagem" => "Recusa negada"
			);

			funcao_venda_update($row_venda['id'], $dados_venda, $dados_venda_descricao);
		}
		// fim - analisada		

	}
	// fim - recusar recusa -----------------------------------------------------------------------------------------------
	//---------------------------------------------------------------------------------------------------------------------------------------------------------------


	// Encerrar
	if (($_GET['situacao'] == "editar") and $_GET["acao"] == "Encerrar") {

		$dados_venda = array(
			"interacao" => $row_venda['interacao'] + 1,
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

			"final_situacao" => $row_venda['situacao'],
			"final_status" => $row_venda['status']
		);
		$dados_venda_descricao = array(
			"id_venda" => $row_venda['id'],
			"id_usuario_responsavel" => $row_usuario['IdUsuario'],
			"descricao" => "Encerramento de venda<br>" . $_POST['observacao'],
			"data" => date("Y-m-d H:i:s"),
			"tipo_postagem" => "Encerrado"
		);

		funcao_venda_update($row_venda['id'], $dados_venda, $dados_venda_descricao);

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

	}
	// fim - Encerrar
	//---------------------------------------------------------------------------------------------------------------------------------------------------------------
	// Cancelar
	if (($_GET['situacao'] == "editar") and $_GET["acao"] == "Cancelar") {

		$dados_venda = array(
			"interacao" => $row_venda['interacao'] + 1,
			"situacao" => "cancelada",
			"status" => "",
			"status_flag" => "f",

			"acao" => "",

			"encaminhamento_id" => "",
			"encaminhamento_data_inicio" => "",
			"encaminhamento_data" => "",

			"status_devolucao" => "",
			"status_recusa" => "",

			"data_fim" => date('Y-m-d H:i:s'),

			"final_situacao" => $row_venda['situacao'],
			"final_status" => $row_venda['status']
		);
		$dados_venda_descricao = array(
			"id_venda" => $row_venda['id'],
			"id_usuario_responsavel" => $row_usuario['IdUsuario'],
			"descricao" => "Cancelamento de venda<br>" . $_POST['observacao'],
			"data" => date("Y-m-d H:i:s"),
			"tipo_postagem" => "Cancelado"
		);

		funcao_venda_update($row_venda['id'], $dados_venda, $dados_venda_descricao);

		// update 'agenda'
		$updateSQL_venda_agenda = sprintf(
			"
	UPDATE agenda 
	SET status=%s 
	WHERE (id_venda_treinamento=%s or id_venda_implantacao=%s) and status='a'",
			GetSQLValueString("c", "text"),

			GetSQLValueString($row_venda['id'], "int"),
			GetSQLValueString($row_venda['id'], "int")
		);

		mysql_select_db($database_conexao, $conexao);
		$Result_venda_agenda = mysql_query($updateSQL_venda_agenda, $conexao) or die(mysql_error());
		// fim - update 'agenda'

	}
	// fim - Cancelar
	//---------------------------------------------------------------------------------------------------------------------------------------------------------------
	// Estornar
	if (
		($_GET['situacao'] == "editar" and $_GET["acao"] == "Estornar") and
		($row_usuario['controle_venda'] == "Y")
	) {

		$dados_venda = array(
			"interacao" => $row_venda['interacao'] + 1,
			"situacao" => $row_venda['final_situacao'],
			"status" => $row_venda['final_status'],
			"status_flag" => "a",

			"acao" => "",

			"encaminhamento_id" => "",
			"encaminhamento_data_inicio" => "",
			"encaminhamento_data" => "",

			"status_devolucao" => "",
			"status_recusa" => "",

			"data_fim" => "0000-00-00 00:00:00",

			"final_situacao" => "",
			"final_status" => "",

			"estorno" => "s",
			"estorno_justificativa" => $_POST['observacao']
		);
		$dados_venda_descricao = array(
			"id_venda" => $row_venda['id'],
			"id_usuario_responsavel" => $row_usuario['IdUsuario'],
			"descricao" => "Estorno de venda<br>" . $_POST['observacao'],
			"data" => date("Y-m-d H:i:s"),
			"tipo_postagem" => "Estornado"
		);

		funcao_venda_update($row_venda['id'], $dados_venda, $dados_venda_descricao);
	}
	// fim - Estornar
	//---------------------------------------------------------------------------------------------------------------------------------------------------------------
	// Dilação de prazo
	if (($_GET['situacao'] == "editar") and $_GET["acao"] == "Dilação de prazo") {

		// não existe solicitação de dilação do prazo
		if ($row_venda['dilacao_prazo_proposto'] == 0) {

			$venda_validade_dias = $row_parametros['venda_validade_dias'] + $row_venda['dilacao_prazo'];
			$validade = date('d-m-Y 23:59:59', strtotime("+$venda_validade_dias days", strtotime($row_venda['data_venda'])));

			$dilacao_prazo_proposto = $_POST['dilacao_prazo'];
			$dilacao_prazo_soma = $row_venda['dilacao_prazo'] + $dilacao_prazo_proposto;

			// insert venda_validade
			$insertSQL_venda_validade = sprintf(
				"
											   INSERT INTO venda_validade (id_venda, id_usuario_responsavel, data_atual, prazo, data_validade, motivo, status, id_usuario_responsavel_solicitacao) 
											   VALUES (%s, %s, %s, %s, %s, %s, %s, %s)",
				GetSQLValueString($row_venda['id'], "int"),
				GetSQLValueString($row_usuario['IdUsuario'], "int"),
				GetSQLValueString(date("Y-m-d H:i:s"), "date"),
				GetSQLValueString($dilacao_prazo_proposto, "date"),
				GetSQLValueString(date('Y-m-d H:i:s', strtotime("+$dilacao_prazo_proposto days", strtotime($validade))), "date"),

				GetSQLValueString($_POST['observacao'], "text"),
				GetSQLValueString("0", "text"),
				GetSQLValueString($row_usuario['IdUsuario'], "int")
			);

			mysql_select_db($database_conexao, $conexao);
			$Result_venda_validade = mysql_query($insertSQL_venda_validade, $conexao) or die(mysql_error());
			$id_venda_validade = mysql_insert_id();
			// fim - insert venda_validade

			$dados_venda = array(
				"interacao" => $row_venda['interacao'] + 1,
				"status_flag" => "a",

				"dilacao_id_atual" => $id_venda_validade,
				"dilacao_prazo_quantidade" => $row_venda['dilacao_prazo_quantidade'] + 1,
				"dilacao_prazo_proposto" => $dilacao_prazo_proposto,
				"dilacao_motivo" => $_POST['observacao']
			);
			$dados_venda_descricao = array(
				"id_venda" => $row_venda['id'],
				"id_usuario_responsavel" => $row_usuario['IdUsuario'],
				"descricao" => "Solicitação de dilação da validade da venda em " . $_POST['dilacao_prazo'] . " dias<br>" . $_POST['observacao'],
				"data" => date("Y-m-d H:i:s"),
				"tipo_postagem" => "Solicitação de alteração de validade"
			);

			funcao_venda_update($row_venda['id'], $dados_venda, $dados_venda_descricao);
		}
		// fim - não existe solicitação de dilação do prazo

		// existe solicitação de dilação do prazo
		if ($row_venda['dilacao_prazo_proposto'] > 0 and $row_usuario['controle_venda'] == "Y") {

			$venda_validade_dias = $row_parametros['venda_validade_dias'] + $row_venda['dilacao_prazo'];
			$validade = date('d-m-Y  23:59:59', strtotime("+$venda_validade_dias days", strtotime($row_venda['data_venda'])));

			$dilacao_prazo_proposto = $row_venda['dilacao_prazo_proposto'];
			$dilacao_prazo_soma = $row_venda['dilacao_prazo'] + $dilacao_prazo_proposto;

			// sim
			if (@$_POST['dilacao_prazo_sim_nao'] == "s") {

				// update 'venda_validade'
				$updateSQL_venda_validade = sprintf(
					"
											  UPDATE venda_validade 
											  SET status=1, id_usuario_responsavel_aceite_recusa=%s, data_aceite_recusa=%s, observacao=%s  
											  WHERE id=%s",
					GetSQLValueString($row_usuario['IdUsuario'], "int"),
					GetSQLValueString(date('Y-m-d H:i:s'), "text"),
					GetSQLValueString($_POST['observacao'], "text"),
					GetSQLValueString($row_venda['dilacao_id_atual'], "int")
				);

				mysql_select_db($database_conexao, $conexao);
				$Result_venda_validade = mysql_query($updateSQL_venda_validade, $conexao) or die(mysql_error());
				// fim - update 'venda_validade'

				$dados_venda = array(
					"interacao" => $row_venda['interacao'] + 1,
					"status_flag" => "a",

					"dilacao_id_atual" => "",
					"dilacao_prazo_proposto" => 0,
					"dilacao_prazo" => $dilacao_prazo_soma
				);
				$dados_venda_descricao = array(
					"id_venda" => $row_venda['id'],
					"id_usuario_responsavel" => $row_usuario['IdUsuario'],
					"descricao" => "Dilação da validade da venda alterada de " . $row_venda['dilacao_prazo'] . " dias para " . $dilacao_prazo_soma . " dias",
					"data" => date("Y-m-d H:i:s"),
					"tipo_postagem" => "Alteração de validade aceita"
				);

				funcao_venda_update($row_venda['id'], $dados_venda, $dados_venda_descricao);
			}
			// fim - sim

			// não
			if (@$_POST['dilacao_prazo_sim_nao'] == "n") {

				// update 'venda_validade'
				$updateSQL_venda_validade = sprintf(
					"
											  UPDATE venda_validade 
											  SET status=0, id_usuario_responsavel_aceite_recusa=%s, data_aceite_recusa=%s, observacao=%s  
											  WHERE id=%s",
					GetSQLValueString($row_usuario['IdUsuario'], "int"),
					GetSQLValueString(date('Y-m-d H:i:s'), "text"),
					GetSQLValueString($_POST['observacao'], "text"),
					GetSQLValueString($row_venda['dilacao_id_atual'], "int")
				);

				mysql_select_db($database_conexao, $conexao);
				$Result_venda_validade = mysql_query($updateSQL_venda_validade, $conexao) or die(mysql_error());
				// fim - update 'venda_validade'

				$dados_venda = array(
					"interacao" => $row_venda['interacao'] + 1,
					"status_flag" => "a",

					"dilacao_id_atual" => "",
					"dilacao_prazo_proposto" => 0
				);
				$dados_venda_descricao = array(
					"id_venda" => $row_venda['id'],
					"id_usuario_responsavel" => $row_usuario['IdUsuario'],
					"descricao" => $_POST['observacao'],
					"data" => date("Y-m-d H:i:s"),
					"tipo_postagem" => "Alteração de validade não aceita"
				);

				funcao_venda_update($row_venda['id'], $dados_venda, $dados_venda_descricao);
			}
			// fim - não

		}
		// fim - existe solicitação de dilação do prazo

	}
	// fim - Dilação de prazo
	//---------------------------------------------------------------------------------------------------------------------------------------------------------------
	// Contato
	if (($_GET['situacao'] == "editar") and $_GET["acao"] == "Contato") {

		// insert venda_contato
		mysql_select_db($database_conexao, $conexao);
		$insertSQL_venda_contato = sprintf(
			"
	INSERT INTO venda_contato (id_venda, id_usuario_responsavel, data, responsavel, telefone, descricao) 
	VALUES (%s, %s, %s, %s, %s, %s)",
			GetSQLValueString($row_venda['id'], "int"),
			GetSQLValueString($row_usuario['IdUsuario'], "int"),
			GetSQLValueString(date("Y-m-d H:i:s"), "date"),
			GetSQLValueString($_POST['contato_responsavel'], "text"),
			GetSQLValueString($_POST['contato_telefone'], "text"),
			GetSQLValueString($_POST['observacao'], "text")
		);

		mysql_select_db($database_conexao, $conexao);
		$Result_venda_contato = mysql_query($insertSQL_venda_contato, $conexao) or die(mysql_error());
		$id_venda_contato = mysql_insert_id();
		// fim - insert venda_contato

		$dados_venda = array(
			"interacao" => $row_venda['interacao'] + 1,
			"status_flag" => $row_venda['status_flag']
		);
		$dados_venda_descricao = array(
			"id_venda" => $row_venda['id'],
			"id_usuario_responsavel" => $row_usuario['IdUsuario'],
			"descricao" => "Contato: " . $_POST['contato_responsavel'] . "<br>" . $_POST['observacao'],
			"data" => date("Y-m-d H:i:s"),
			"tipo_postagem" => "Novo contato"
		);

		funcao_venda_update($row_venda['id'], $dados_venda, $dados_venda_descricao);
	}
	// fim - Contato
	//---------------------------------------------------------------------------------------------------------------------------------------------------------------
	// Agendamento de $agenda_tipo_titulo
	if (($_GET['situacao'] == "editar") and $_GET["acao"] == "Agendamento de $agenda_tipo_titulo") {

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

		$dados_venda = array(
			"interacao" => $row_venda['interacao'] + 1,
			"quantidade_agendado_$agenda_tipo" => $row_venda['quantidade_agendado_' . $agenda_tipo] + 1,
			"status_flag" => "a"
		);
		$dados_venda_descricao = array(
			"id_venda" => $row_venda['id'],
			"id_usuario_responsavel" => $row_usuario['IdUsuario'],
			"descricao" => "Data Inicial do agendamento de $agenda_tipo_titulo: " . date('d-m-Y  H:i:s', strtotime($_POST['data_agendamento_inicio'])) . "<br>Data Final do agendamento de $agenda_tipo_titulo: " . date('d-m-Y  H:i:s', strtotime($_POST['data_agendamento'])) . "<br>Descrição: " . $_POST['descricao_agendamento'] . "<br><br>" . $_POST['observacao'],
			"data" => date("Y-m-d H:i:s"),
			"tipo_postagem" => "Agendamento de $agenda_tipo_titulo"
		);

		funcao_venda_update($row_venda['id'], $dados_venda, $dados_venda_descricao);

		// insert agenda
		$insertSQL_venda_agenda = sprintf(
			"
										   INSERT INTO agenda (id_venda_$agenda_tipo, id_usuario_responsavel, data_inicio, data, data_criacao, status, descricao, venda_solicitante) 
										   VALUES (%s, %s, %s, %s, %s, %s, %s, %s)",
			GetSQLValueString($row_venda['id'], "int"),
			GetSQLValueString($row_usuario_selecionado['IdUsuario'], "int"),
			GetSQLValueString($_POST['data_agendamento_inicio'], "date"),
			GetSQLValueString($_POST['data_agendamento'], "date"),
			GetSQLValueString(date("Y-m-d H:i:s"), "date"),
			GetSQLValueString("a", "text"),
			GetSQLValueString($_POST['descricao_agendamento'], "text"),
			GetSQLValueString($_POST['venda_solicitante'], "text")
		);

		mysql_select_db($database_conexao, $conexao);
		$Result_venda_agenda = mysql_query($insertSQL_venda_agenda, $conexao) or die(mysql_error());
		// fim - insert agenda

		mysql_free_result($usuario_selecionado);
	}
	// fim - Agendamento de $agenda_tipo_titulo
	//---------------------------------------------------------------------------------------------------------------------------------------------------------------
	// Reagendar $agenda_tipo_titulo
	if (($_GET['situacao'] == "editar") and $_GET["acao"] == "Reagendar $agenda_tipo_titulo") {

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

		$dados_venda = array(
			"interacao" => $row_venda['interacao'] + 1,
			"status_flag" => "a"
		);
		$dados_venda_descricao = array(
			"id_venda" => $row_venda['id'],
			"id_usuario_responsavel" => $row_usuario['IdUsuario'],
			"descricao" => "Código agendamento de $agenda_tipo_titulo: " . $row_agenda['id_agenda'] . "<br>Data Inicial do agendamento de $agenda_tipo_titulo: " . date('d-m-Y  H:i:s', strtotime($_POST['data_agendamento_inicio'])) . "<br>Data Final do agendamento de $agenda_tipo_titulo: " . date('d-m-Y  H:i:s', strtotime($_POST['data_agendamento'])) . "<br>Descrição: " . $_POST['descricao_agendamento'] . "<br><br>" . $_POST['observacao'],
			"data" => date("Y-m-d H:i:s"),
			"tipo_postagem" => "Agendamento de $agenda_tipo_titulo alterado"
		);
		funcao_venda_update($row_venda['id'], $dados_venda, $dados_venda_descricao);

		// update agenda
		$updateSQL_venda_agenda = sprintf(
			"
										   UPDATE agenda 
										   SET id_usuario_responsavel=%s, data_inicio=%s, data=%s, descricao=%s, status=%s, venda_solicitante=%s  
										   WHERE id_venda_$agenda_tipo = %s and id_agenda = %s",
			GetSQLValueString($row_usuario_selecionado['IdUsuario'], "int"),
			GetSQLValueString($_POST['data_agendamento_inicio'], "date"),
			GetSQLValueString($_POST['data_agendamento'], "date"),
			GetSQLValueString($_POST['descricao_agendamento'], "text"),
			GetSQLValueString("a", "text"),
			GetSQLValueString($_POST['venda_solicitante'], "text"),

			GetSQLValueString($row_venda['id'], "int"),
			GetSQLValueString($row_agenda['id_agenda'], "int")
		);

		mysql_select_db($database_conexao, $conexao);
		$Result_venda_agenda = mysql_query($updateSQL_venda_agenda, $conexao) or die(mysql_error());
		// fim - update agenda

		mysql_free_result($usuario_selecionado);
	}
	// fim - Reagendar $agenda_tipo_titulo
	//---------------------------------------------------------------------------------------------------------------------------------------------------------------
	// Finalizar agendamento de $agenda_tipo_titulo
	if (($_GET['situacao'] == "editar") and $_GET["acao"] == "Finalizar agendamento de $agenda_tipo_titulo") {

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

		$dados_venda = array(
			"interacao" => $row_venda['interacao'] + 1,
			"quantidade_agendado_$agenda_tipo" => $row_venda['quantidade_agendado_' . $agenda_tipo] - 1,
			"status_flag" => "a"

		);
		$dados_venda_descricao = array(
			"id_venda" => $row_venda['id'],
			"id_usuario_responsavel" => $row_usuario['IdUsuario'],
			"descricao" => "Código agendamento de $agenda_tipo_titulo: " . $row_agenda['id_agenda'] . "<br>Data Inicial do agendamento de $agenda_tipo_titulo: " . date('d-m-Y  H:i:s', strtotime($row_agenda['data_inicio'])) . "<br>Data Final do agendamento de $agenda_tipo_titulo: " . date('d-m-Y  H:i:s', strtotime($row_agenda['data'])) . "<br>Descrição: " . $row_agenda['descricao'] . "<br><br>" . $_POST['observacao'],
			"data" => date("Y-m-d H:i:s"),
			"tipo_postagem" => "Agendamento de $agenda_tipo_titulo finalizado"
		);
		funcao_venda_update($row_venda['id'], $dados_venda, $dados_venda_descricao);

		// update agenda
		$updateSQL_venda_agenda = sprintf(
			"
										   UPDATE agenda 
										   SET id_usuario_responsavel=%s, status=%s, venda_receptor=%s  
										   WHERE id_venda_$agenda_tipo = %s and id_agenda = %s",
			GetSQLValueString($row_usuario_selecionado['IdUsuario'], "int"),

			GetSQLValueString("f", "text"),
			GetSQLValueString($_POST['venda_receptor'], "text"),

			GetSQLValueString($row_venda['id'], "int"),
			GetSQLValueString($row_agenda['id_agenda'], "int")
		);

		mysql_select_db($database_conexao, $conexao);
		$Result_venda_agenda = mysql_query($updateSQL_venda_agenda, $conexao) or die(mysql_error());
		// fim - update agenda

		mysql_free_result($usuario_selecionado);
	}
	// fim - Finalizar agendamento de $agenda_tipo_titulo
	//---------------------------------------------------------------------------------------------------------------------------------------------------------------
	// Cancelar agendamento de $agenda_tipo_titulo
	if (($_GET['situacao'] == "editar") and $_GET["acao"] == "Cancelar agendamento de $agenda_tipo_titulo") {

		$dados_venda = array(
			"interacao" => $row_venda['interacao'] + 1,
			"quantidade_agendado_$agenda_tipo" => $row_venda['quantidade_agendado_' . $agenda_tipo] - 1,
			"status_flag" => "a"

		);
		$dados_venda_descricao = array(
			"id_venda" => $row_venda['id'],
			"id_usuario_responsavel" => $row_usuario['IdUsuario'],
			"descricao" => "Código agendamento de $agenda_tipo_titulo: " . $row_agenda['id_agenda'] . "<br>Data Inicial do agendamento de $agenda_tipo_titulo: " . date('d-m-Y  H:i:s', strtotime($row_agenda['data_inicio'])) . "<br>Data Final do agendamento de $agenda_tipo_titulo: " . date('d-m-Y  H:i:s', strtotime($row_agenda['data'])) . "<br>Descrição: " . $row_agenda['descricao'] . "<br><br>" . $_POST['observacao'],
			"data" => date("Y-m-d H:i:s"),
			"tipo_postagem" => "Agendamento de $agenda_tipo_titulo cancelado"
		);
		funcao_venda_update($row_venda['id'], $dados_venda, $dados_venda_descricao);

		// update agenda
		$updateSQL_venda_agenda = sprintf(
			"
										   UPDATE agenda 
										   SET status=%s, venda_responsavel_cancelado=%s 
										   WHERE id_venda_$agenda_tipo = %s and id_agenda = %s",
			GetSQLValueString("c", "text"),
			GetSQLValueString($_POST['venda_responsavel_cancelado'], "text"),

			GetSQLValueString($row_venda['id'], "int"),
			GetSQLValueString($row_agenda['id_agenda'], "int")
		);

		mysql_select_db($database_conexao, $conexao);
		$Result_venda_agenda = mysql_query($updateSQL_venda_agenda, $conexao) or die(mysql_error());
		// fim - update agenda

	}
	// fim - Cancelar agendamento de $agenda_tipo_titulo
	//---------------------------------------------------------------------------------------------------------------------------------------------------------------
	// Validar venda
	if (($_GET['situacao'] == "editar") and $_GET["acao"] == "Validar venda") {

		$dados_venda = array(
			"interacao" => $row_venda['interacao'] + 1,
			"situacao" => "em execução",
			"validacao_venda_data" => date("Y-m-d H:i:s"),
			"validacao_venda_IdUsuario" => $row_usuario['IdUsuario']
		);
		$dados_venda_descricao = array(
			"id_venda" => $row_venda['id'],
			"id_usuario_responsavel" => $row_usuario['IdUsuario'],
			"descricao" => $_POST['observacao'],
			"data" => date("Y-m-d H:i:s"),
			"tipo_postagem" => "Validar venda"
		);
		funcao_venda_update($row_venda['id'], $dados_venda, $dados_venda_descricao);
	}
	// fim - Validar venda
	//---------------------------------------------------------------------------------------------------------------------------------------------------------------
	// Questionar
	if (($_GET['situacao'] == "editar") and $_GET["acao"] == "Questionar") {

		$dados_venda = array(
			"interacao" => $row_venda['interacao'] + 1,
			"praca" => $row_venda['praca']
		);
		$dados_venda_descricao = array(
			"id_venda" => $row_venda['id'],
			"id_usuario_responsavel" => $row_usuario['IdUsuario'],
			"descricao" => $_POST['observacao'],
			"data" => date("Y-m-d H:i:s"),
			"tipo_postagem" => "Questionamento"
		);
		funcao_venda_update($row_venda['id'], $dados_venda, $dados_venda_descricao);
	}
	// fim - Questionar
	//---------------------------------------------------------------------------------------------------------------------------------------------------------------


	// Alterar
	//---------------------------------------------------------------------------------------------------------------------------------------------------------------
	// Alterar usuário responsável
	if (($_GET['situacao'] == "editar") and $_GET["acao"] == "Alterar usuário responsável") {

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

		$dados_venda = array(
			"interacao" => $row_venda['interacao'] + 1,
			"id_usuario_responsavel" => $row_usuario_selecionado['IdUsuario'],
			"usuario_responsavel_leu" => ""
		);
		$dados_venda_descricao = array(
			"id_venda" => $row_venda['id'],
			"id_usuario_responsavel" => $row_usuario['IdUsuario'],
			"descricao" => "Foi alterado o usuário responsável<br>Anterior: " . $row_venda['usuario_responsavel'] . " - Novo: " . $row_usuario_selecionado['nome'] . "<br>" . $_POST['observacao'],
			"data" => date("Y-m-d H:i:s"),
			"tipo_postagem" => "Alteração de usuário responsável"
		);

		mysql_free_result($usuario_selecionado);

		funcao_venda_update($row_venda['id'], $dados_venda, $dados_venda_descricao);
	}
	// fim - Alterar usuário responsável
	//---------------------------------------------------------------------------------------------------------------------------------------------------------------
	// Alterar observação
	if (($_GET['situacao'] == "editar") and $_GET["acao"] == "Alterar observação") {


		$dados_venda = array(
			"interacao" => $row_venda['interacao'] + 1,
			"observacao" => $_POST['observacao']
		);
		$dados_venda_descricao = array(
			"id_venda" => $row_venda['id'],
			"id_usuario_responsavel" => $row_usuario['IdUsuario'],
			"descricao" => "Foi alterado o Observação<br>Observação anterior: " . $row_venda['observacao'] . " - Novo Observação: " . $_POST['observacao'] . "<br>" . $_POST['observacao'],
			"data" => date("Y-m-d H:i:s"),
			"tipo_postagem" => "Alteração de observação"
		);
		funcao_venda_update($row_venda['id'], $dados_venda, $dados_venda_descricao);
	}
	// fim - Alterar observação
	//---------------------------------------------------------------------------------------------------------------------------------------------------------------
	// Alterar valor da venda do software
	if (($_GET['situacao'] == "editar") and $_GET["acao"] == "Alterar valor da venda do software") {

		// converter entrada de valor_venda
		if (isset($_POST['valor_venda'])) {
			$_POST['valor_venda'] = str_replace(',', '.', $_POST['valor_venda']);
		}
		// fim - converter entrada de valor_venda

		$dados_venda = array(
			"interacao" => $row_venda['interacao'] + 1,
			"valor_venda" => $_POST['valor_venda'],
			"observacao" => $_POST['observacao']
		);
		$dados_venda_descricao = array(
			"id_venda" => $row_venda['id'],
			"id_usuario_responsavel" => $row_usuario['IdUsuario'],
			"descricao" => "Foi alterado o Valor de venda do software<br>Valor anterior: R$ " . number_format($row_venda['valor_venda'], 2, ',', '.') . " - Novo Valor: R$ " . number_format($_POST['valor_venda'], 2, ',', '.') . "<br>" . $_POST['observacao'],
			"data" => date("Y-m-d H:i:s"),
			"tipo_postagem" => "Alteração de valor da venda do software"
		);
		funcao_venda_update($row_venda['id'], $dados_venda, $dados_venda_descricao);
	}
	// fim - Alterar valor da venda do software
	//---------------------------------------------------------------------------------------------------------------------------------------------------------------
	// Alterar valor da venda do treinamento
	if (($_GET['situacao'] == "editar") and $_GET["acao"] == "Alterar valor da venda do treinamento") {

		// converter entrada de valor_treinamento
		if (isset($_POST['valor_treinamento'])) {
			$_POST['valor_treinamento'] = str_replace(',', '.', $_POST['valor_treinamento']);
		}
		// fim - converter entrada de valor_treinamento

		$dados_venda = array(
			"interacao" => $row_venda['interacao'] + 1,
			"valor_treinamento" => $_POST['valor_treinamento'],
			"observacao" => $_POST['observacao']
		);
		$dados_venda_descricao = array(
			"id_venda" => $row_venda['id'],
			"id_usuario_responsavel" => $row_usuario['IdUsuario'],
			"descricao" => "Foi alterado o Valor de venda do treinamento<br>Valor anterior: R$ " . number_format($row_venda['valor_treinamento'], 2, ',', '.') . " - Novo Valor: R$ " . number_format($_POST['valor_treinamento'], 2, ',', '.') . "<br>" . $_POST['observacao'],
			"data" => date("Y-m-d H:i:s"),
			"tipo_postagem" => "Alteração de valor da venda do treinamento"
		);
		funcao_venda_update($row_venda['id'], $dados_venda, $dados_venda_descricao);
	}
	// fim - Alterar valor da venda do treinamento
	//---------------------------------------------------------------------------------------------------------------------------------------------------------------
	// Alterar qtde de tempo para treinamento
	if (($_GET['situacao'] == "editar") and $_GET["acao"] == "Alterar qtde de tempo para treinamento") {

		$treinamento_tempo_anterior_tHoras = ($row_venda['treinamento_tempo'] * 60) / 3600;
		$treinamento_tempo_anterior_tMinutos = ($row_venda['treinamento_tempo'] * 60) % 3600 / 60;
		$treinamento_tempo_anterior = sprintf('%02dh %02dm', $treinamento_tempo_anterior_tHoras, $treinamento_tempo_anterior_tMinutos);

		$treinamento_tempo_novo_tHoras = ($_POST['treinamento_tempo'] * 60) / 3600;
		$treinamento_tempo_novo_tMinutos = ($_POST['treinamento_tempo'] * 60) % 3600 / 60;
		$treinamento_tempo_novo = sprintf('%02dh %02dm', $treinamento_tempo_novo_tHoras, $treinamento_tempo_novo_tMinutos);

		$dados_venda = array(
			"interacao" => $row_venda['interacao'] + 1,
			"treinamento_tempo" => $_POST['treinamento_tempo'],
			"observacao" => $_POST['observacao']
		);
		$dados_venda_descricao = array(
			"id_venda" => $row_venda['id'],
			"id_usuario_responsavel" => $row_usuario['IdUsuario'],
			"descricao" => "Foi alterada a qtde de tempo para treinamento<br>Anterior: " . $treinamento_tempo_anterior . " - Novo: " . $treinamento_tempo_novo . "<br>" . $_POST['observacao'],
			"data" => date("Y-m-d H:i:s"),
			"tipo_postagem" => "Alteração de qtde de tempo para treinamento"
		);
		funcao_venda_update($row_venda['id'], $dados_venda, $dados_venda_descricao);
	}
	// fim - Alterar qtde de tempo para treinamento
	//---------------------------------------------------------------------------------------------------------------------------------------------------------------
	// Alterar qtde de tempo para implantação
	if (($_GET['situacao'] == "editar") and $_GET["acao"] == "Alterar qtde de tempo para implantação") {

		$implantacao_tempo_anterior_tHoras = ($row_venda['implantacao_tempo'] * 60) / 3600;
		$implantacao_tempo_anterior_tMinutos = ($row_venda['implantacao_tempo'] * 60) % 3600 / 60;
		$implantacao_tempo_anterior = sprintf('%02dh %02dm', $implantacao_tempo_anterior_tHoras, $implantacao_tempo_anterior_tMinutos);

		$implantacao_tempo_novo_tHoras = ($_POST['implantacao_tempo'] * 60) / 3600;
		$implantacao_tempo_novo_tMinutos = ($_POST['implantacao_tempo'] * 60) % 3600 / 60;
		$implantacao_tempo_novo = sprintf('%02dh %02dm', $implantacao_tempo_novo_tHoras, $implantacao_tempo_novo_tMinutos);

		$dados_venda = array(
			"interacao" => $row_venda['interacao'] + 1,
			"implantacao_tempo" => $_POST['implantacao_tempo'],
			"observacao" => $_POST['observacao']
		);
		$dados_venda_descricao = array(
			"id_venda" => $row_venda['id'],
			"id_usuario_responsavel" => $row_usuario['IdUsuario'],
			"descricao" => "Foi alterada a qtde de tempo para implantação<br>Anterior: " . $implantacao_tempo_anterior . " - Novo: " . $implantacao_tempo_novo . "<br>" . $_POST['observacao'],
			"data" => date("Y-m-d H:i:s"),
			"tipo_postagem" => "Alteração de qtde de tempo para implantação"
		);
		funcao_venda_update($row_venda['id'], $dados_venda, $dados_venda_descricao);
	}
	// fim - Alterar qtde de tempo para implantação
	//---------------------------------------------------------------------------------------------------------------------------------------------------------------
	// Alterar ordem de serviço
	if (($_GET['situacao'] == "editar") and $_GET["acao"] == "Alterar ordem de serviço") {


		$dados_venda = array(
			"interacao" => $row_venda['interacao'] + 1,
			"ordem_servico" => $_POST['ordem_servico']
		);
		$dados_venda_descricao = array(
			"id_venda" => $row_venda['id'],
			"id_usuario_responsavel" => $row_usuario['IdUsuario'],
			"descricao" => "Foi alterado a ordem de serviço.<br>Ordem de serviço anterior: " . $row_venda['ordem_servico'] . " - Nova ordem de serviço: " . $_POST['ordem_servico'] . "<br>" . $_POST['observacao'],
			"data" => date("Y-m-d H:i:s"),
			"tipo_postagem" => "Alteração de ordem de serviço"
		);
		funcao_venda_update($row_venda['id'], $dados_venda, $dados_venda_descricao);
	}
	// fim - Alterar ordem de serviço
	//---------------------------------------------------------------------------------------------------------------------------------------------------------------
	// Alterar módulos
	if (($_GET['situacao'] == "editar") and $_GET["acao"] == "Alterar módulos") {

		// modulos_antigo
		mysql_select_db($database_conexao, $conexao);
		$query_venda_modulos = sprintf(
			"
		SELECT geral_tipo_modulo.descricao AS modulo, geral_tipo_modulo.IdTipoModulo AS id_modulo 
		FROM venda_modulos 
		LEFT JOIN geral_tipo_modulo ON venda_modulos.id_modulo = geral_tipo_modulo.IdTipoModulo 
		WHERE venda_modulos.id_venda = %s 
		ORDER BY id ASC",
			GetSQLValueString($row_venda['id'], "int")
		);
		$venda_modulos = mysql_query($query_venda_modulos, $conexao) or die(mysql_error());
		$row_venda_modulos = mysql_fetch_assoc($venda_modulos);
		$totalRows_venda_modulos = mysql_num_rows($venda_modulos);

		$contador_venda_modulos_antigo = 0;
		$modulos_antigo = NULL;
		do {

			$contador_venda_modulos_antigo = $contador_venda_modulos_antigo + 1;

			$modulos_antigo .= $row_venda_modulos['modulo'];
			if ($contador_venda_modulos_antigo < $totalRows_venda_modulos) {
				$modulos_antigo .= ", ";
			}
		} while ($row_venda_modulos = mysql_fetch_assoc($venda_modulos));
		// fim - modulos_antigo

		// $geral_tipo_modulo_atual_array
		$geral_tipo_modulo_atual_array = NULL;
		if (isset($_POST['venda_modulos'])) {
			foreach ($_POST["venda_modulos"] as $venda_modulos) {

				// geral_tipo_modulo_atual
				mysql_select_db($database_conexao, $conexao);
				$query_geral_tipo_modulo_atual = sprintf(
					"
													 SELECT * FROM geral_tipo_modulo 
													 WHERE IdTipoModulo = %s 
													 ORDER BY IdTipoModulo ASC",
					GetSQLValueString($venda_modulos, "int")
				);
				$geral_tipo_modulo_atual = mysql_query($query_geral_tipo_modulo_atual, $conexao) or die(mysql_error());
				$row_geral_tipo_modulo_atual = mysql_fetch_assoc($geral_tipo_modulo_atual);
				$totalRows_geral_tipo_modulo_atual = mysql_num_rows($geral_tipo_modulo_atual);

				$geral_tipo_modulo_atual_array[] = array('IdTipoModulo' => $row_geral_tipo_modulo_atual['IdTipoModulo'], 'descricao' => $row_geral_tipo_modulo_atual['descricao']);

				mysql_free_result($geral_tipo_modulo_atual);
				// fim - geral_tipo_modulo_atual

			}
		}
		// fim - $geral_tipo_modulo_atual_array

		// delete - venda_modulos
		$deleteSQL_modulo_delete = sprintf("DELETE FROM venda_modulos WHERE id_venda=%s", GetSQLValueString($row_venda['id'], "int"));

		mysql_select_db($database_conexao, $conexao);
		$Result_modulo_delete = mysql_query($deleteSQL_modulo_delete, $conexao) or die(mysql_error());
		// fim - delete - venda_modulos

		// insert - venda_modulos
		$contador_venda_modulos_novo = 0;
		$modulos_novo = NULL;
		if (isset($geral_tipo_modulo_atual_array)) {
			foreach ($geral_tipo_modulo_atual_array as $venda_modulos) {

				$contador_venda_modulos_novo = $contador_venda_modulos_novo + 1;
				$modulos_novo .= $venda_modulos['descricao'];
				if ($contador_venda_modulos_novo < count($_POST['venda_modulos'])) {
					$modulos_novo .= ", ";
				}

				$insertSQL_modulo_insert = sprintf(
					"INSERT INTO venda_modulos (id_prospeccao, id_venda, id_modulo, modulo, data_criacao, contrato) VALUES (%s, %s, %s, %s, %s, %s)",

					GetSQLValueString($row_venda['id_prospeccao'], "int"),
					GetSQLValueString($row_venda['id'], "int"),
					GetSQLValueString($venda_modulos['IdTipoModulo'], "int"),
					GetSQLValueString($venda_modulos['descricao'], "text"),
					GetSQLValueString(date('Y-m-d H:i:s'), "date"),
					GetSQLValueString($row_venda["contrato"], "text")
				);

				mysql_select_db($database_conexao, $conexao);
				$Result_modulo_insert = mysql_query($insertSQL_modulo_insert, $conexao) or die(mysql_error());
			}
		}
		// fim - insert - venda_modulos

		$dados_venda = array(
			"interacao" => $row_venda['interacao'] + 1,
			"status_flag" => $row_venda['status_flag']
		);
		$dados_venda_descricao = array(
			"id_venda" => $row_venda['id'],
			"id_usuario_responsavel" => $row_usuario['IdUsuario'],
			"descricao" => "Foram alterados os módulos<br>Anterior: " . $modulos_antigo . "<br>Novo: " . $modulos_novo . "<br>" . $_POST['observacao'],
			"data" => date("Y-m-d H:i:s"),
			"data" => date("Y-m-d H:i:s"),
			"tipo_postagem" => "Alteração de módulos"
		);
		funcao_venda_update($row_venda['id'], $dados_venda, $dados_venda_descricao);
	}
	// fim - Alterar módulos
	//---------------------------------------------------------------------------------------------------------------------------------------------------------------
	// Alterar data do contrato
	if (($_GET['situacao'] == "editar") and $_GET["acao"] == "Alterar data do contrato") {

		$data_contrato_atual = date('d-m-Y', strtotime($row_venda['data_contrato']));
		$data_contrato_novo = date('d-m-Y', strtotime($_POST['data_contrato']));

		// update 'contrato'
		$updateSQL_contrato = sprintf(
			"
									  UPDATE contrato 
									  SET datcont17 = %s 
									  WHERE codigo17 = %s",
			GetSQLValueString($_POST['data_contrato'], "date"),
			GetSQLValueString($row_venda['contrato'], "text")
		);

		mysql_select_db($database_conexao, $conexao);
		$Result_contrato = mysql_query($updateSQL_contrato, $conexao) or die(mysql_error());
		// fim - update 'contrato'

		$dados_venda = array(
			"interacao" => $row_venda['interacao'] + 1,
			"data_contrato" => $_POST['data_contrato']
		);
		$dados_venda_descricao = array(
			"id_venda" => $row_venda['id'],
			"id_usuario_responsavel" => $row_usuario['IdUsuario'],
			"descricao" => "Foi alterada a data do contrato.<br>Data do contrato anterior: " . $data_contrato_atual . " - Nova data do contrato: " . $data_contrato_novo . "<br>" . $_POST['observacao'],
			"data" => date("Y-m-d H:i:s"),
			"tipo_postagem" => "Alteração de data do contrato"
		);
		funcao_venda_update($row_venda['id'], $dados_venda, $dados_venda_descricao);
	}
	// fim - Alterar data do contrato
	//---------------------------------------------------------------------------------------------------------------------------------------------------------------
	// fim - Alterar

	// limpando o array
	$dados_venda = array();
	$dados_venda_descricao = array();
	// fim - limpando o array

	// redireciona
	$updateGoTo = "venda_editar.php?id_venda=" . $_GET['id_venda'];
	echo sprintf("<meta http-equiv=\"refresh\" content=\"0; url=%s\">", $updateGoTo);
	// fim - redireciona

	exit;
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><? echo $_GET['acao']; ?> (<?php echo $row_venda['id']; ?>)</title>

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

	/* fim - calendário */
</style>

<script type="text/javascript">
window.history.forward(1); // Desabilita a função de voltar do Browser

// validar diferença entre datas
jQuery.validator.addMethod("dateRange", function() {

	var is_valid = true;
	var data_inicio = $("#data_agendamento_inicio").val();
	var data_fim = $("#data_agendamento").val();

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

$.metadata.setType("attr", "validate");
$(document).ready(function() {

	// tab/enter	
	textboxes = $("input, select, textarea");
	$("input, select").keypress(function(e) {

		var tecla = (e.keyCode ? e.keyCode : e.which);
		if (tecla == 13 || tecla == 9) {

			// corrige problema - quando a agenda está aberta, o tab/enter fica com o FOCUS na hora_inicio			
			if ($("#TB_window").length) { // verifica se o tb_show está sendo exibido
				$("#data_agendamento_inicio").focus();
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

	// consulta automática - agenda
	<? if (
		($_GET['situacao'] == "editar") and
		($_GET["acao"] == "Agendamento de $agenda_tipo_titulo" or
			$_GET["acao"] == "Reagendar $agenda_tipo_titulo" or

			$_GET["acao"] == "Agendamento de implantação" or
			$_GET["acao"] == "Reagendar implantação")
	) { ?>
		$('#button').click(function() {

			if ($("input[name=data_agendamento_inicio]").val() != '' && $("input[name=data_agendamento]").val() != '' && $("select[name=usuario_responsavel]").val() != '') {

				// post
				$.post("agenda_consulta.php", {
					data_inicio: $("input[name=data_agendamento_inicio]").val(),
					data_fim: $("input[name=data_agendamento]").val(),
					usuario_responsavel: $("select[name=usuario_responsavel]").find("option:selected").attr("title"),
					id_agenda: <? if ($totalRows_agenda > 0) {
									echo $row_agenda['id_agenda'];
								} else {
									echo 0;
								} ?>
				}, function(data) {

					//alert(data);
					//return false;

					if (data == 0) {
						$('#form').submit();
					}
					if (data == 1) {
						alert("Data informada inválida. Existem agendamentos para o usuário atual durante período informado.");
						$('#data_agendamento').val('');
						$('#agendamento_tempo').val('');
						return false;
					}

				});
				// fim - post

			} else {
				$('#form').submit();
			}

		});
	<? } else { ?>
		$('#button').click(function() {

			$('#form').submit();

		});
	<? } ?>
	// fim - consulta automática - agenda

	$('#valor_venda').priceFormat({
		prefix: '',
		centsSeparator: ',',
		thousandsSeparator: ''
	});

	$('#valor_treinamento').priceFormat({
		prefix: '',
		centsSeparator: ',',
		thousandsSeparator: ''
	});

	// validação
	$("#form").validate({
		rules: {
			<? if (
				($_GET['acao'] == "Encaminhar" or
					$_GET['acao'] == "Recusar" or
					$_GET['acao'] == "Encerrar" or
					$_GET['acao'] == "Cancelar" or
					$_GET["acao"] == "Finalizar agendamento de $agenda_tipo_titulo" or
					$_GET["acao"] == "Cancelar agendamento de $agenda_tipo_titulo" or
					$_GET['acao'] == "Questionar" or
					$_GET['acao'] == "Validar venda" or
					$_GET['acao'] == "Dilação de prazo" or
					$_GET['acao'] == "Contato" or
					($_GET['situacao'] == "editar" and $_GET['acao'] == "Alterar módulos")) or 
					$_GET["acao"] == "Alterar valor da venda do software" or 
					$_GET["acao"] == "Alterar valor da venda do treinamento"
			) { ?>
				observacao: {
					required: true,
					minlength: 10
				},
			<? } ?>

			dilacao_prazo_sim_nao: "required",
			nome_razao_social: "required",
			ramo_de_atividade: "required",

			agendamento_tempo: "required",
			venda_responsavel_cancelado: "required",
			ordem_servico: {
				required: true,
				minlength: 10,
				min: 1
			},
			dilacao_prazo: {
				required: true,
				minlength: 1,
				min: 1,
				max: 90
			},
			"venda_modulos[]": {
				required: true,
				minlength: 1
			},
			data_agendamento_inicio: {
				required: true
			},
			data_agendamento: {
				required: true
			},
			descricao_agendamento: {
				required: true,
				minlength: 10
			},
			status_agendamento: "required",

			venda_receptor: "required",

			contato_responsavel: "required",
			contato_telefone: "required",

			baixa_tipo: "required",
			baixa_contrato: "required",

			data_contrato: "required",

			valor_venda: "required",
			valor_treinamento: "required",

			treinamento_tempo: {required: true, min: 1},
			implantacao_tempo: {required: true, min: 1},

			usuario_responsavel: "required",
			venda_solicitante: "required"
		},
		messages: {
			<? if (
				($_GET['acao'] == "Encaminhar" or
					$_GET['acao'] == "Recusar" or
					$_GET['acao'] == "Encerrar" or
					$_GET['acao'] == "Cancelar" or
					$_GET["acao"] == "Finalizar agendamento de $agenda_tipo_titulo" or
					$_GET["acao"] == "Cancelar agendamento de $agenda_tipo_titulo" or
					$_GET['acao'] == "Questionar" or
					$_GET['acao'] == "Validar venda" or
					$_GET['acao'] == "Dilação de prazo" or
					$_GET['acao'] == "Contato" or
					($_GET['situacao'] == "editar" and $_GET['acao'] == "Alterar módulos")) or 
					$_GET["acao"] == "Alterar valor da venda do software" or 
					$_GET["acao"] == "Alterar valor da venda do treinamento"
			) { ?>
				observacao: " <br>Informe a observação com no mínimo 10 caracteres",
			<? } ?>

			dilacao_prazo_sim_nao: " Informe o tipo de prospect",
			nome_razao_social: " Informe o cliente",
			ramo_de_atividade: " Informe o ramo de atividade",

			agendamento_tempo: " Selecione o tempo de agendamento",
			venda_responsavel_cancelado: " Informe o responsável pelo cancelamento",
			ordem_servico: " Informe o número da ordem de serviço com 10 dígitos",
			dilacao_prazo: " Informe a quantidade de dias a estender (entre 1 e 90 dias)",
			"venda_modulos[]": " Selecione pelo menos um dos módulos acima",
			data_agendamento_inicio: " Informe uma data inicial",
			data_agendamento: {
				required: " Informe uma data final"
			},
			descricao_agendamento: " <br>Informe a descrição com no mínimo 10 caracteres",
			status_agendamento: " Informe o status do agendamento",

			venda_receptor: " Informe o receptor",

			contato_responsavel: " Informe o contato",
			contato_telefone: " Informe o telefone",

			baixa_tipo: " Informe o motivo da baixa",
			baixa_contrato: " Informe o número do contrato",

			data_contrato: " Informe a data do contrato",

			valor_venda: " Informe o valor",
			valor_treinamento: " Informe o valor",

			treinamento_tempo: " Informe a quantidade",
			implantacao_tempo: " Informe a quantidade",

			usuario_responsavel: " Informe o usuário responsável",
			venda_solicitante: " Informe o solicitante"
		},
		onkeyup: false,
		submitHandler: function(form) {

			<? if ($_GET['situacao'] == "editar" and $_GET['acao'] == "Alterar ordem de serviço") { ?>

				// post
				$.post("venda_consulta_ordem_servico.php", {
						id_venda: <?php echo $row_venda['id']; ?>,
						ordem_servico: $('#ordem_servico').val()
					},
					function(valor) {

						if (valor.retorno == 0) {

							form.submit();

						} else {

							alert("Ordem de serviço já existe para a Venda n° " + valor.id_venda + ". Por favor verifique.");
							return false;
						}

					}, "json"
				)
				// fim - post

			<? } else { ?>

				form.submit();

			<? } ?>

		}
	});

	$("#baixa_contrato").numeric();
	$("#ordem_servico").numeric();
	// fim - validação

	// mascara
	$('#data_agendamento_inicio').mask('99-99-9999 99:99', {
		placeholder: " "
	});
	$('#cep').mask('99999-999', {
		placeholder: " "
	});
	$('#telefone').mask('(99) 9999-9999', {
		placeholder: " "
	});
	$('#celular').mask('(99) 9999-9999', {
		placeholder: " "
	});
	$('#contador_telefone').mask('(99) 9999-9999', {
		placeholder: " "
	});
	$('#data_contrato').mask('99-99-9999', {
		placeholder: " "
	});
	// fim - mascara

	// abrir agenda (agenda)
	$('#ver_agenda').click(function() {

		var usuario_responsavel = $("select[name=usuario_responsavel]").val();
		data_atual = $('#data_agendamento_inicio').val();

		tb_show("Agenda", "agenda_popup.php?usuario_atual=" + usuario_responsavel + "&data_atual=" + data_atual + "&height=<? echo $venda_editar_tabela_height - 100; ?>&width=<? echo $venda_editar_tabela_width - 40; ?>&placeValuesBeforeTB_=savedValues&TB_iframe=true", "");
		return false;

	});
	// fim - abrir agenda (agenda)

	// calendário -------------------------------------------------------------
	// data_agenda_inicio
	var data_agendamento_inicio = $('#data_agendamento_inicio');

	data_agendamento_inicio.datetimepicker({

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

			$('#data_agendamento').val('');
			$('#agendamento_tempo').val('');

		},
		onChangeMonthYear: function(selectedDateTime) {
			$('#data_agendamento').val('');
			$('#agendamento_tempo').val('');
		},
		onClose: function(selectedDateTime) {

			if (selectedDateTime == "  -  -       :  " || selectedDateTime == "") {
				$('#data_agendamento').val('');
				$('#agendamento_tempo').val('');
			}

		},
		onSelect: function(selectedDateTime) {
			$('#data_agendamento').val('');
			$('#agendamento_tempo').val('');
		}

	});
	// fim - data_agenda_inicio

	// data_contrato
	var data_contrato = $('#data_contrato');
	data_contrato.datepicker({
		showOn: "button",
		buttonImage: "css/images/calendar.gif",
		buttonImageOnly: true,
		minDate: -<? echo $row_parametros['prospeccao_tempo_retroativo_data_contrato']; ?>,
		maxDate: 0,
		inline: true,
		dateFormat: 'dd-mm-yy',
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
	// fim - data_contrato
	// fim - calendário -------------------------------------------------------

	// data_contrato
	$('#data_contrato').blur(function() {

		var campo = $(this);

		data_contrato_erro = funcao_verifica_data_valida(campo) // chamada da função (retorna 0/1)

		// confere data com XX dias ou menos
		if (data_contrato_erro == 0 && campo.val().length == 10) {

			// data_entrada
			value = campo.val();
			var quebraDE = value.split("-");

			var diaDE = quebraDE[0];
			var mesDE = quebraDE[1];
			var anoDE = quebraDE[2].substr(0, 4);

			var data_entrada = anoDE + '/' + mesDE + '/' + diaDE + ' 23:59:59';

			var hojeDE = new Date(data_entrada);

			hojeDE.setHours(23, 59, 59, 59);
			// fim - data_entrada

			// data_atual
			var hoje = new Date(<?php echo time() * 1000 ?>);
			hoje.setHours(23, 59, 59, 59);
			// fim - data_atual

			// data_anterior
			var hojeDA = new Date(<?php echo time() * 1000 ?>);
			hojeDA.setHours(23, 59, 59, 59);
			var diaDA = hojeDA.getDate();
			hojeDA.setDate(diaDA - <? echo $row_parametros['prospeccao_tempo_retroativo_data_contrato']; ?>);
			// fim - data_anterior

			if (hojeDE.getTime() < hojeDA.getTime() || hojeDE.getTime() > hoje.getTime()) {
				data_contrato_erro = 1;
			}

		}
		// fim - confere data com XX dias ou menos

		// data_contrato_erro
		if (data_contrato_erro == 1) {

			alert("Data inválida");
			$('#data_contrato').val('');
			setTimeout(function() {
				campo.focus();
			}, 100);
			return false;
			event.preventDefault();

		} else {

			data_contrato_erro = 0;

		}
		// fim - data_contrato_erro

	});
	// fim - data_contrato

	// verifica se é uma data válida/agenda auto
	$('#data_agendamento_inicio').blur(function() {

		var campo = $(this);

		// erro
		var erro = funcao_verifica_data_hora_valida(campo, <?php echo time() * 1000 ?>) // chamada da função (retorna 0/1)
		if (erro == 1) {

			alert("Data inválida");
			$('#data_agendamento_inicio').val('');
			$('#agendamento_tempo').val('');
			$('#data_agendamento').val('');
			setTimeout(function() {
				campo.focus();
			}, 100);
			return false;

		}
		// fim - erro

		// agenda auto
		else if ($(this).val().length == 16) {

			$('#agendamento_tempo').val('');
			$('#data_agendamento').val('');

			var usuario_responsavel = $("select[name=usuario_responsavel]").val();
			data_atual = $('#data_agendamento_inicio').val();

			tb_show("Agenda", "agenda_popup.php?usuario_atual=" + usuario_responsavel + "&data_atual=" + data_atual + "&height=<? echo $venda_editar_tabela_height - 100; ?>&width=<? echo $venda_editar_tabela_width - 40; ?>&placeValuesBeforeTB_=savedValues&TB_iframe=true", "");
			return false;

		}
		// fim - agenda auto

	});
	// fim - verifica se é uma data válida/agenda auto

	// agendamento_tempo
	$("select[name=agendamento_tempo]").change(function() {

		var agendamento_tempo = $(this).val();

		var data_inicio = $("#data_agendamento_inicio").val();
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

		$("#data_agendamento").val(date1);

	});
	// fim - agendamento_tempo

});
</script>
</head>

<body>

	<div class="div_solicitacao_linhas">
		<table cellspacing="0" cellpadding="0" width="100%">
			<tr>
				<td style="text-align:left">
					Venda número: <?php echo $row_venda['id']; ?>
				</td>

				<td style="text-align: right">
					&lt;&lt; <a href="venda_editar.php?id_venda=<?php echo $_GET['id_venda']; ?>" target="_top">Voltar</a>
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
					Cliente: <?php echo $row_venda['empresa']; ?>
				</td>
			</tr>
		</table>
	</div>

	<!-- agendamento -->
	<? if (
		$_GET['situacao'] == "editar" and
		($_GET['acao'] == "Agendamento de $agenda_tipo_titulo" or
			$_GET['acao'] == "Reagendar $agenda_tipo_titulo" or

			$_GET['acao'] == "Agendamento de implantação" or
			$_GET['acao'] == "Reagendar implantação")
	) { ?>

		<div class="div_solicitacao_linhas4">
			<table cellspacing="0" cellpadding="0" width="100%">
				<tr>
					<td style="text-align: left">

						<!-- Adquirido/Disponibilizado -->
						<span class="label_solicitacao">Adquirido/Disponibilizado: </span>
						<?
						$tHoras = $contador_segundo / 3600;
						$tMinutos = $contador_segundo % 3600 / 60;

						echo sprintf('%02dh %02dm', $tHoras, $tMinutos);
						?> |
						<!-- fim - Adquirido/Disponibilizado -->


						<!-- Agendado -->
						<span class="label_solicitacao">Agendado: </span>
						<?
						$tHoras = $contador_segundo_agendado / 3600;
						$tMinutos = $contador_segundo_agendado % 3600 / 60;

						echo sprintf('%02dh %02dm', $tHoras, $tMinutos);
						?> |
						<!-- fim - Agendado -->


						<!-- Finalizado -->
						<span class="label_solicitacao">Finalizado: </span>
						<?
						$tHoras = $contador_segundo_finalizado / 3600;
						$tMinutos = $contador_segundo_finalizado % 3600 / 60;

						echo sprintf('%02dh %02dm', $tHoras, $tMinutos);
						?> |
						<!-- fim - Finalizado -->


						<!-- Cancelado -->
						<span class="label_solicitacao">Cancelado: </span>
						<?
						$tHoras = $contador_segundo_cancelado / 3600;
						$tMinutos = $contador_segundo_cancelado % 3600 / 60;

						echo sprintf('%02dh %02dm', $tHoras, $tMinutos);
						?> |
						<!-- fim - Cancelado -->


						<!-- Restante -->
						<span class="label_solicitacao">Restante: </span>
						<?
						$tHoras = $contador_segundo_restante / 3600;
						$tMinutos = $contador_segundo_restante % 3600 / 60;

						echo sprintf('%02dh %02dm', $tHoras, $tMinutos);
						?>
						<!-- fim - Restante -->

					</td>
				</tr>
			</table>
		</div>

	<? } ?>
	<!-- fim - agendamento -->

	<!-- Agendamento atual -->
	<? if (
		$_GET['situacao'] == "editar" and
		($_GET['acao'] == "Reagendar $agenda_tipo_titulo" or
			$_GET['acao'] == "Finalizar agendamento de $agenda_tipo_titulo" or
			$_GET['acao'] == "Cancelar agendamento de $agenda_tipo_titulo")
	) { ?>

		<div class="div_solicitacao_linhas4">
			<table cellspacing="0" cellpadding="0" width="100%">
				<tr>
					<td style="text-align: left">
						<span class="label_solicitacao">Agendamento atual: </span>
						<? echo date('d-m-Y H:i:s', strtotime($row_agenda['data_inicio'])); ?> à <? echo date('d-m-Y H:i:s', strtotime($row_agenda['data'])); ?>
						<div style="margin-top: 5px;">
							OBS: <? echo $row_agenda['descricao']; ?>
						</div>
					</td>
				</tr>
			</table>
		</div>

	<? } ?>
	<!-- fim - Agendamento atual -->

	<!-- Dilação de prazo -->
	<? if (
		$_GET['situacao'] == "editar" and
		$_GET['acao'] == "Dilação de prazo" and
		$totalRows_venda_validade > 0
	) { ?>

		<div class="div_solicitacao_linhas4">
			<table cellspacing="0" cellpadding="0" width="100%">
				<tr>
					<td style="text-align: left">
						<span class="label_solicitacao">Dilação de prazo: </span>
						<br>
						<table width="100%" border="0" cellspacing="0" cellpadding="0" style="margin-top: 5px;">

							<thead>
								<tr bgcolor="#F1F1F1">
									<th width="20%" style="padding: 5px;" align="left">Data</th>
									<th width="10%" style="padding: 5px;" align="left">Solicitante</th>
									<th width="15%" style="padding: 5px;" align="left">Qtde dias solicitados</th>
									<th style="padding: 5px;" align="left">Motivo</th>
									<th width="10%" style="padding: 5px;" align="left">Autorizado</th>
									<th width="10%" style="padding: 5px;" align="left">Responsável</th>
									<th width="20%" style="padding: 5px;" align="left">Observação</th>
								</tr>
							</thead>

							<? do { ?>
								<tr>

									<td style="padding: 5px;" align="left">
										<span style=" color: <? if ($row_venda_validade['id'] == $row_venda['dilacao_id_atual']) {
																	echo "#F00";
																} else {
																	echo "#000";
																} ?>;">
											<? if ($row_venda_validade['data_atual'] != "") {
												echo date('d-m-Y H:i', strtotime($row_venda_validade['data_atual']));
											} ?>
										</span>
									</td>

									<td style="padding: 5px;" align="left">
										<span style=" color: <? if ($row_venda_validade['id'] == $row_venda['dilacao_id_atual']) {
																	echo "#F00";
																} else {
																	echo "#000";
																} ?>;">
											<? echo $row_venda_validade['usuarios_nome_responsavel_solicitacao']; ?>
										</span>
									</td>

									<td style="padding: 5px;" align="left">
										<span style=" color: <? if ($row_venda_validade['id'] == $row_venda['dilacao_id_atual']) {
																	echo "#F00";
																} else {
																	echo "#000";
																} ?>;">
											<? echo $row_venda_validade['prazo']; ?>
										</span>
									</td>

									<td style="padding: 5px;" align="left">
										<span style=" color: <? if ($row_venda_validade['id'] == $row_venda['dilacao_id_atual']) {
																	echo "#F00";
																} else {
																	echo "#000";
																} ?>;">
											<? echo $row_venda_validade['motivo']; ?>
										</span>
									</td>

									<td style="padding: 5px;" align="left">
										<span style=" color: <? if ($row_venda_validade['id'] == $row_venda['dilacao_id_atual']) {
																	echo "#F00";
																} else {
																	echo "#000";
																} ?>;">
											<? if ($row_venda_validade['status'] == 1) {
												echo "Sim";
											} ?>
											<? if ($row_venda_validade['status'] == 0) {
												echo "Não";
											} ?>
										</span>
									</td>

									<td style="padding: 5px;" align="left">
										<span style=" color: <? if ($row_venda_validade['id'] == $row_venda['dilacao_id_atual']) {
																	echo "#F00";
																} else {
																	echo "#000";
																} ?>;">
											<? echo $row_venda_validade['usuarios_nome_responsavel_aceite_recusa']; ?>
										</span>
									</td>

									<td style="padding: 5px;" align="left">
										<span style=" color: <? if ($row_venda_validade['id'] == $row_venda['dilacao_id_atual']) {
																	echo "#F00";
																} else {
																	echo "#000";
																} ?>;">
											<? echo $row_venda_validade['observacao']; ?>
										</span>
									</td>

								</tr>
							<?php } while ($row_venda_validade = mysql_fetch_assoc($venda_validade)); ?>

						</table>
					</td>
				</tr>
			</table>
		</div>

	<? } ?>
	<!-- fim - Dilação de prazo -->

	<!-- Contato -->
	<? if (
		$_GET['situacao'] == "editar" and
		$_GET['acao'] == "Contato" and
		$totalRows_venda_contato > 0
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
								<? $contador_venda_contato = 0; ?>
								<? do { ?>
									<? $contador_venda_contato = $contador_venda_contato + 1; ?>
									<tr>

										<td style="padding: 5px;" align="left">
											<span style=" color: <? if (($contador_venda_contato % 2) == 0) {
																		echo "#F00";
																	} else {
																		echo "#000";
																	} ?>;">
												<? if ($row_venda_contato['data'] != "") {
													echo date('d-m-Y H:i', strtotime($row_venda_contato['data']));
												} ?>
											</span>
										</td>

										<td style="padding: 5px;" align="left">
											<span style=" color: <? if (($contador_venda_contato % 2) == 0) {
																		echo "#F00";
																	} else {
																		echo "#000";
																	} ?>;">
												<? echo $row_venda_contato['usuarios_nome']; ?>
											</span>
										</td>

										<td style="padding: 5px;" align="left">
											<span style=" color: <? if (($contador_venda_contato % 2) == 0) {
																		echo "#F00";
																	} else {
																		echo "#000";
																	} ?>;">
												<? echo $row_venda_contato['responsavel']; ?>
											</span>
										</td>

										<td style="padding: 5px;" align="left">
											<span style=" color: <? if (($contador_venda_contato % 2) == 0) {
																		echo "#F00";
																	} else {
																		echo "#000";
																	} ?>;">
												<? echo $row_venda_contato['telefone']; ?>
											</span>
										</td>

										<td style="padding: 5px;" align="left">
											<span style=" color: <? if (($contador_venda_contato % 2) == 0) {
																		echo "#F00";
																	} else {
																		echo "#000";
																	} ?>;">
												<? echo $row_venda_contato['descricao']; ?>
											</span>
										</td>

									</tr>
								<?php } while ($row_venda_contato = mysql_fetch_assoc($venda_contato)); ?>
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

						<!-- usuario_responsavel -->
						<?
						if (
							($_GET['situacao'] == "editar" and
								($_GET['acao'] == "Alterar usuário responsável" or
									$_GET["acao"] == "Agendamento de $agenda_tipo_titulo" or
									$_GET["acao"] == "Finalizar agendamento de $agenda_tipo_titulo" or
									$_GET['acao'] == "Reagendar $agenda_tipo_titulo"))

							or

							(
								($_GET['situacao'] == "analisada" or $_GET['situacao'] == "em execução") and
								$_GET['acao'] == "Encaminhar")
						) { ?>
							<div style="padding-bottom: 10px;" id="div_usuario_responsavel">
								<?
								// usuario_responsavel
								mysql_select_db($database_conexao, $conexao);

								$query_usuario_responsavel = sprintf(
									"
													 SELECT IdUsuario, nome, praca FROM usuarios WHERE status = 1 and praca = %s ORDER BY praca, nome ASC",
									GetSQLValueString($row_venda['praca'], "text")
								);

								$usuario_responsavel = mysql_query($query_usuario_responsavel, $conexao) or die(mysql_error());
								$row_usuario_responsavel = mysql_fetch_assoc($usuario_responsavel);
								$totalRows_usuario_responsavel = mysql_num_rows($usuario_responsavel);
								// fim - usuario_responsavel
								?>
								<div class="label_solicitacao2">Usuário responsável*:</div>
								<select name="usuario_responsavel" id="iusuario_responsavel">
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


						<!-- venda_solicitante -->
						<? if (
							($_GET['situacao'] == "editar" and
								$_GET["acao"] == "Agendamento de $agenda_tipo_titulo" or
								$_GET['acao'] == "Reagendar $agenda_tipo_titulo")
						) { ?>
							<div style="padding-bottom: 10px;">
								<div class="label_solicitacao2">Solicitante*:</div>
								<input type="text" name="venda_solicitante" id="venda_solicitante" value="<? echo $row_agenda['venda_solicitante']; ?>" style="width:290px" />
							</div>
						<? } ?>
						<!-- fim - venda_solicitante -->


						<!-- venda_receptor -->
						<? if (
							($_GET['situacao'] == "editar" and
								($_GET['acao'] == "Finalizar agendamento de $agenda_tipo_titulo"))
						) { ?>
							<div style="padding-bottom: 10px;">
								<div class="label_solicitacao2">Receptor*:</div>
								<input type="text" name="venda_receptor" id="venda_receptor" value="<? echo $row_agenda['venda_receptor']; ?>" style="width:290px" />
							</div>
						<? } ?>
						<!-- fim - venda_receptor -->


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


						<!-- valor_venda -->
						<? if (
							($_GET['situacao'] == "editar" and
								$_GET["acao"] == "Alterar valor da venda do software")
						) { ?>
							<div style="padding-bottom: 10px;">
								<div class="label_solicitacao2">Valor da venda do software*:</div>
								<input type="text" name="valor_venda" id="valor_venda" style="width:290px" value="<? echo $row_venda['valor_venda']; ?>" />
							</div>
						<? } ?>
						<!-- fim - valor_venda -->


						<!-- valor_treinamento -->
						<? if (
							($_GET['situacao'] == "editar" and
								$_GET["acao"] == "Alterar valor da venda do treinamento")
						) { ?>
							<div style="padding-bottom: 10px;">
								<div class="label_solicitacao2">Valor da venda do treinamento*:</div>
								<input type="text" name="valor_treinamento" id="valor_treinamento" style="width:290px" value="<? echo $row_venda['valor_treinamento']; ?>" />
							</div>
						<? } ?>
						<!-- fim - valor_treinamento -->


						<!-- treinamento_tempo -->
						<? if (
							($_GET['situacao'] == "editar" and
								$_GET["acao"] == "Alterar qtde de tempo para treinamento")
						) { ?>
							<div style="padding-bottom: 10px;">
								<div class="label_solicitacao2">Alterar qtde de tempo para treinamento*:</div>
								<select name="treinamento_tempo" id="treinamento_tempo" style="width: 200px;">
									<option value="0">Escolha...</option>

									<option value="30">30 minutos</option>
									<option value="60">1 hora</option>
									<option value="90">1 hora e 30 minutos</option>
									<option value="120">2 horas</option>
									<option value="150">2 horas e 30 minutos</option>
									<option value="180">3 horas</option>
									<option value="210">3 horas 30 minutos</option>
									<option value="240">4 horas</option>
									<option value="270">4 horas e 30 minutos</option>
									<option value="300">5 horas</option>
									<option value="330">5 horas e 30 minutos</option>
									<option value="360">6 horas</option>
									<option value="390">6 horas e 30 minutos</option>
									<option value="420">7 horas</option>
									<option value="450">7 horas e 30 minutos</option>
									<option value="480">8 horas</option>
									<option value="510">8 horas 30 minutos</option>
									<option value="540">9 horas</option>
									<option value="570">9 horas e 30 minutos</option>
									<option value="600">10 horas</option>

									<option value="630">10 horas e 30 minutos</option>
									<option value="660">11 horas</option>
									<option value="690">11 horas e 30 minutos</option>
									<option value="720">12 horas</option>
									<option value="750">12 horas e 30 minutos</option>
									<option value="780">13 horas</option>
									<option value="810">13 horas 30 minutos</option>
									<option value="840">14 horas</option>
									<option value="870">14 horas e 30 minutos</option>
									<option value="900">15 horas</option>
									<option value="930">15 horas e 30 minutos</option>
									<option value="960">16 horas</option>
									<option value="990">16 horas e 30 minutos</option>
									<option value="1020">17 horas</option>
									<option value="1050">17 horas e 30 minutos</option>
									<option value="1080">18 horas</option>
									<option value="1110">18 horas 30 minutos</option>
									<option value="1140">19 horas</option>
									<option value="1170">19 horas e 30 minutos</option>
									<option value="1200">20 horas</option>

									<option value="1230">20 horas e 30 minutos</option>
									<option value="1260">21 horas</option>
									<option value="1290">21 horas e 30 minutos</option>
									<option value="1320">22 horas</option>
									<option value="1350">22 horas e 30 minutos</option>
									<option value="1380">23 horas</option>
									<option value="1410">23 horas 30 minutos</option>
									<option value="1440">24 horas</option>
									<option value="1470">24 horas e 30 minutos</option>
									<option value="1500">25 horas</option>
									<option value="1530">25 horas e 30 minutos</option>
									<option value="1560">26 horas</option>
									<option value="1590">26 horas e 30 minutos</option>
									<option value="1620">27 horas</option>
									<option value="1650">27 horas e 30 minutos</option>
									<option value="1680">28 horas</option>
									<option value="1710">28 horas 30 minutos</option>
									<option value="1740">29 horas</option>
									<option value="1770">29 horas e 30 minutos</option>
									<option value="1800">30 horas</option>

									<option value="1230">30 horas e 30 minutos</option>
									<option value="1260">31 horas</option>
									<option value="1290">31 horas e 30 minutos</option>
									<option value="1320">32 horas</option>
									<option value="1350">32 horas e 30 minutos</option>
									<option value="1380">33 horas</option>
									<option value="1410">33 horas 30 minutos</option>
									<option value="1440">34 horas</option>
									<option value="1470">34 horas e 30 minutos</option>
									<option value="1500">35 horas</option>
									<option value="1530">35 horas e 30 minutos</option>
									<option value="1560">36 horas</option>
									<option value="1590">36 horas e 30 minutos</option>
									<option value="1620">37 horas</option>
									<option value="1650">37 horas e 30 minutos</option>
									<option value="1680">38 horas</option>
									<option value="1710">38 horas 30 minutos</option>
									<option value="1740">39 horas</option>
									<option value="1770">39 horas e 30 minutos</option>
									<option value="1800">40 horas</option>

									<option value="1830">40 horas e 30 minutos</option>
									<option value="1860">41 horas</option>
									<option value="1890">41 horas e 30 minutos</option>
									<option value="1920">42 horas</option>
									<option value="1950">42 horas e 30 minutos</option>
									<option value="1980">43 horas</option>
									<option value="2010">43 horas 30 minutos</option>
									<option value="2040">44 horas</option>
									<option value="2070">44 horas e 30 minutos</option>
									<option value="2100">45 horas</option>
									<option value="2130">45 horas e 30 minutos</option>
									<option value="2160">46 horas</option>
									<option value="2190">46 horas e 30 minutos</option>
									<option value="2220">47 horas</option>
									<option value="2250">47 horas e 30 minutos</option>
									<option value="2280">48 horas</option>
									<option value="2310">48 horas 30 minutos</option>
									<option value="2340">49 horas</option>
									<option value="2370">49 horas e 30 minutos</option>
									<option value="2400">50 horas</option>
								</select>
							</div>
						<? } ?>
						<!-- fim - treinamento_tempo -->

						<!-- implantacao_tempo -->
						<? if (
							($_GET['situacao'] == "editar" and
								$_GET["acao"] == "Alterar qtde de tempo para implantação")
						) { ?>
							<div style="padding-bottom: 10px;">
								<div class="label_solicitacao2">Alterar qtde de tempo para implantação*:</div>
								<select name="implantacao_tempo" id="implantacao_tempo" style="width: 200px;">
									<option value="0">Escolha...</option>

									<option value="30">30 minutos</option>
									<option value="60">1 hora</option>
									<option value="90">1 hora e 30 minutos</option>
									<option value="120">2 horas</option>
									<option value="150">2 horas e 30 minutos</option>
									<option value="180">3 horas</option>
									<option value="210">3 horas 30 minutos</option>
									<option value="240">4 horas</option>
									<option value="270">4 horas e 30 minutos</option>
									<option value="300">5 horas</option>
									<option value="330">5 horas e 30 minutos</option>
									<option value="360">6 horas</option>
									<option value="390">6 horas e 30 minutos</option>
									<option value="420">7 horas</option>
									<option value="450">7 horas e 30 minutos</option>
									<option value="480">8 horas</option>
									<option value="510">8 horas 30 minutos</option>
									<option value="540">9 horas</option>
									<option value="570">9 horas e 30 minutos</option>
									<option value="600">10 horas</option>

									<option value="630">10 horas e 30 minutos</option>
									<option value="660">11 horas</option>
									<option value="690">11 horas e 30 minutos</option>
									<option value="720">12 horas</option>
									<option value="750">12 horas e 30 minutos</option>
									<option value="780">13 horas</option>
									<option value="810">13 horas 30 minutos</option>
									<option value="840">14 horas</option>
									<option value="870">14 horas e 30 minutos</option>
									<option value="900">15 horas</option>
									<option value="930">15 horas e 30 minutos</option>
									<option value="960">16 horas</option>
									<option value="990">16 horas e 30 minutos</option>
									<option value="1020">17 horas</option>
									<option value="1050">17 horas e 30 minutos</option>
									<option value="1080">18 horas</option>
									<option value="1110">18 horas 30 minutos</option>
									<option value="1140">19 horas</option>
									<option value="1170">19 horas e 30 minutos</option>
									<option value="1200">20 horas</option>

									<option value="1230">20 horas e 30 minutos</option>
									<option value="1260">21 horas</option>
									<option value="1290">21 horas e 30 minutos</option>
									<option value="1320">22 horas</option>
									<option value="1350">22 horas e 30 minutos</option>
									<option value="1380">23 horas</option>
									<option value="1410">23 horas 30 minutos</option>
									<option value="1440">24 horas</option>
									<option value="1470">24 horas e 30 minutos</option>
									<option value="1500">25 horas</option>
									<option value="1530">25 horas e 30 minutos</option>
									<option value="1560">26 horas</option>
									<option value="1590">26 horas e 30 minutos</option>
									<option value="1620">27 horas</option>
									<option value="1650">27 horas e 30 minutos</option>
									<option value="1680">28 horas</option>
									<option value="1710">28 horas 30 minutos</option>
									<option value="1740">29 horas</option>
									<option value="1770">29 horas e 30 minutos</option>
									<option value="1800">30 horas</option>

									<option value="1230">30 horas e 30 minutos</option>
									<option value="1260">31 horas</option>
									<option value="1290">31 horas e 30 minutos</option>
									<option value="1320">32 horas</option>
									<option value="1350">32 horas e 30 minutos</option>
									<option value="1380">33 horas</option>
									<option value="1410">33 horas 30 minutos</option>
									<option value="1440">34 horas</option>
									<option value="1470">34 horas e 30 minutos</option>
									<option value="1500">35 horas</option>
									<option value="1530">35 horas e 30 minutos</option>
									<option value="1560">36 horas</option>
									<option value="1590">36 horas e 30 minutos</option>
									<option value="1620">37 horas</option>
									<option value="1650">37 horas e 30 minutos</option>
									<option value="1680">38 horas</option>
									<option value="1710">38 horas 30 minutos</option>
									<option value="1740">39 horas</option>
									<option value="1770">39 horas e 30 minutos</option>
									<option value="1800">40 horas</option>

									<option value="1830">40 horas e 30 minutos</option>
									<option value="1860">41 horas</option>
									<option value="1890">41 horas e 30 minutos</option>
									<option value="1920">42 horas</option>
									<option value="1950">42 horas e 30 minutos</option>
									<option value="1980">43 horas</option>
									<option value="2010">43 horas 30 minutos</option>
									<option value="2040">44 horas</option>
									<option value="2070">44 horas e 30 minutos</option>
									<option value="2100">45 horas</option>
									<option value="2130">45 horas e 30 minutos</option>
									<option value="2160">46 horas</option>
									<option value="2190">46 horas e 30 minutos</option>
									<option value="2220">47 horas</option>
									<option value="2250">47 horas e 30 minutos</option>
									<option value="2280">48 horas</option>
									<option value="2310">48 horas 30 minutos</option>
									<option value="2340">49 horas</option>
									<option value="2370">49 horas e 30 minutos</option>
									<option value="2400">50 horas</option>
								</select>
							</div>
						<? } ?>
						<!-- fim - implantacao_tempo -->


						<!-- data_agendamento_inicio -->
						<? if (
							$_GET['situacao'] == "editar" and
							($_GET['acao'] == "Agendamento de $agenda_tipo_titulo" or
								$_GET['acao'] == "Reagendar $agenda_tipo_titulo")
						) { ?>
							<div style="padding-bottom: 10px;">
								<div class="label_solicitacao2">Data Inicial (agendamento)*:</div>
								<input name="data_agendamento_inicio" type="text" id="data_agendamento_inicio" style="width: 170px;" maxlength="19" value="<?
																																							if ($_GET['acao'] == "Reagendar $agenda_tipo_titulo") {
																																								echo "";
																																							} else
				if ($_GET['acao'] == "Agendamento de $agenda_tipo_titulo" or $_GET['acao'] == "Finalizar agendamento de $agenda_tipo_titulo") {
																																								echo "";
																																							} else {
																																								echo date('d-m-Y H:i:s');
																																							} ?>" />
								<br>
								<a href="#" id="ver_agenda" name="ver_agenda" style="color: #03C; font-weight: bold; padding-left: 5px;">Ver agenda</a>
							</div>
						<? } ?>
						<!-- fim - data_agendamento_inicio -->


						<!-- agendamento_tempo/data_agendamento -->
						<? if (
							$_GET['situacao'] == "editar" and
							($_GET['acao'] == "Agendamento de $agenda_tipo_titulo" or
								$_GET['acao'] == "Reagendar $agenda_tipo_titulo")
						) { ?>

							<!-- agendamento_tempo -->
							<div style="padding-bottom: 10px;">
								<div class="label_solicitacao2">Tempo*:</div>
								<?
								if ($_GET['acao'] == "Agendamento de $agenda_tipo_titulo") {
									$tempo_total = $contador_segundo_restante / 60;
								}
								if ($_GET['acao'] == "Reagendar $agenda_tipo_titulo") {
									$tempo_total = ($contador_segundo_restante + (strtotime($row_agenda['data']) - strtotime($row_agenda['data_inicio']))) / 60;
								}
								?>
								<select name="agendamento_tempo" id="agendamento_tempo" style="width: 175px;">
									<option value="">Escolha...</option>
									<? $tempo = 0; ?>
									<? for ($i = 1; $i <= $tempo_total / 30; $i++) { ?>

										<? $tempo = $tempo + 30; ?>
										<option value="<? echo $tempo; ?>"><? $mm = $tempo;
																			echo sprintf("%02dh %02dm", floor($mm / 60), $mm % 60); ?></option>

									<? } ?>
								</select>
							</div>
							<!-- fim - agendamento_tempo -->

							<!-- data_agendamento -->
							<div style="padding-bottom: 10px;">
								<div class="label_solicitacao2">Data Final (agendamento)*:</div>
								<input name="data_agendamento" type="text" id="data_agendamento" style="width: 170px;" readonly="readonly" value="<?
																																					if ($_GET['acao'] == "Reagendar $agenda_tipo_titulo") {
																																						echo "";
																																					} else
				if ($_GET['acao'] == "Agendamento de $agenda_tipo_titulo" or $_GET['acao'] == "Finalizar agendamento de $agenda_tipo_titulo") {
																																						echo "";
																																					} else {
																																						echo date('d-m-Y H:i:s');
																																					} ?>" />
							</div>
							<!-- fim - data_agendamento -->

						<? } ?>
						<!-- fim - agendamento_tempo/data_agendamento -->


						<!-- descricao_agendamento -->
						<? if (
							$_GET['situacao'] == "editar" and
							($_GET['acao'] == "Agendamento de $agenda_tipo_titulo" or
								$_GET['acao'] == "Reagendar $agenda_tipo_titulo")
						) { ?>
							<div style="padding-bottom: 10px;">
								<div class="label_solicitacao2">Descrição (agendamento)*:</div>

								<textarea name="descricao_agendamento" id="descricao_agendamento" style="width: 760px; height: 60px" /><? echo $row_agenda['descricao']; ?></textarea>
							</div>
						<? } else if (
							$_GET['situacao'] == "editar" and
							($_GET['acao'] == "Finalizar agendamento de $agenda_tipo_titulo" or
								$_GET['acao'] == "Cancelar agendamento de $agenda_tipo_titulo")
						) { ?>
							<div style="padding-bottom: 10px;">
								<div class="label_solicitacao2">Descrição (agendamento):</div>
								<? echo $row_agenda['descricao']; ?>
							</div>
						<? } ?>
						<!-- fim - descricao_agendamento -->


						<!-- venda_responsavel_cancelado -->
						<? if (
							($_GET['situacao'] == "editar" and
								($_GET['acao'] == "Cancelar agendamento de $agenda_tipo_titulo"))
						) { ?>
							<div style="padding-bottom: 10px;">
								<div class="label_solicitacao2">Responsável pelo cancelamento (agendamento)*:</div>
								<input type="text" name="venda_responsavel_cancelado" id="venda_responsavel_cancelado" value="<? echo $row_agenda['venda_responsavel_cancelado']; ?>" style="width:760px" />
							</div>
						<? } ?>
						<!-- fim - venda_responsavel_cancelado -->


						<!-- ordem_servico -->
						<? if (
							($_GET['situacao'] == "editar" and
								($_GET['acao'] == "Alterar ordem de serviço"))
						) { ?>
							<div style="padding-bottom: 10px;">
								<div class="label_solicitacao2">Ordem de serviço*:</div>
								<input type="text" name="ordem_servico" id="ordem_servico" value="<? echo $row_venda['ordem_servico']; ?>" style="width:300px" maxlength="10" />
							</div>
						<? } ?>
						<!-- fim - ordem_servico -->


						<!-- dilacao_prazo -->
						<? if (
							($_GET['situacao'] == "editar" and $_GET['acao'] == "Dilação de prazo" and $row_venda['dilacao_prazo_proposto'] == 0)
						) { ?>
							<div style="padding-bottom: 10px;">
								<div class="label_solicitacao2">Quantidade de dias a estender*:</div>
								<input type="text" name="dilacao_prazo" id="dilacao_prazo" value="" style="width:100px" maxlength="2" />
							</div>
						<? } ?>

						<? if (
							($_GET['situacao'] == "editar" and $_GET['acao'] == "Dilação de prazo" and $row_venda['dilacao_prazo_proposto'] > 0 and $row_usuario['controle_venda'] == "Y")
						) { ?>
							<div style="padding-bottom: 10px;">
								<div class="label_solicitacao2">Conceder dilação de prazo*:</div>
								<fieldset style="border: 1px solid #CCCCCC; padding: 5px;">
									<input type="radio" name="dilacao_prazo_sim_nao" id="dilacao_prazo_sim_nao" value="s" /> Sim
									<input type="radio" name="dilacao_prazo_sim_nao" id="dilacao_prazo_sim_nao" value="n" /> Não
								</fieldset>
								<label for="dilacao_prazo_sim_nao" class="error">Selecione uma das opções</label>
							</div>
						<? } ?>
						<!-- fim - dilacao_prazo -->


						<!-- venda_modulos -->
						<? if (
							($_GET['situacao'] == "editar" and
								($_GET['acao'] == "Alterar módulos"))
						) { ?>

							<div style="padding-bottom: 10px;">

								<div class="label_solicitacao2">Módulos*:</div>
								<?
								// geral_tipo_modulo_categoria_listar
								mysql_select_db($database_conexao, $conexao);
								$query_geral_tipo_modulo_categoria_listar = "
				SELECT * 
				FROM geral_tipo_modulo_categoria 
				WHERE IdTipoModuloCategoria <> 7 
				ORDER BY geral_tipo_modulo_categoria.IdTipoModuloCategoria ASC, geral_tipo_modulo_categoria.ordem ASC";
								$geral_tipo_modulo_categoria_listar = mysql_query($query_geral_tipo_modulo_categoria_listar, $conexao) or die(mysql_error());
								$row_geral_tipo_modulo_categoria_listar = mysql_fetch_assoc($geral_tipo_modulo_categoria_listar);
								$totalRows_geral_tipo_modulo_categoria_listar = mysql_num_rows($geral_tipo_modulo_categoria_listar);
								// fim - geral_tipo_modulo_categoria_listar
								?>
								<fieldset>
									<? do { ?>

										<div style="border: 1px solid #DDD; padding: 3px; margin-top: 10px;">

											<div style="margin-bottom: 5px; font-weight: bold;"><? echo $row_geral_tipo_modulo_categoria_listar['descricao']; ?>:</div>

											<?
											// geral_tipo_modulo_listar
											mysql_select_db($database_conexao, $conexao);
											$query_geral_tipo_modulo_listar = "
                        SELECT 
                        geral_tipo_modulo.IdTipoModulo, geral_tipo_modulo.IdTipoModuloCategoria, geral_tipo_modulo.descricao 
                        FROM geral_tipo_modulo 
                        WHERE IdTipoModuloCategoria = " . $row_geral_tipo_modulo_categoria_listar['IdTipoModuloCategoria'] . "
                        ORDER BY geral_tipo_modulo.IdTipoModuloCategoria ASC, geral_tipo_modulo.ordem ASC, geral_tipo_modulo.IdTipoModulo ASC";
											$geral_tipo_modulo_listar = mysql_query($query_geral_tipo_modulo_listar, $conexao) or die(mysql_error());
											$row_geral_tipo_modulo_listar = mysql_fetch_assoc($geral_tipo_modulo_listar);
											$totalRows_geral_tipo_modulo_listar = mysql_num_rows($geral_tipo_modulo_listar);
											// fim - geral_tipo_modulo_listar
											?>

											<? do { ?>

												<?
												// venda_modulos
												mysql_select_db($database_conexao, $conexao);
												$query_venda_modulos = sprintf(
													"
								SELECT geral_tipo_modulo.descricao AS modulo 
								FROM venda_modulos 
								LEFT JOIN geral_tipo_modulo ON venda_modulos.id_modulo = geral_tipo_modulo.IdTipoModulo 
								WHERE venda_modulos.id_venda = %s",
													GetSQLValueString($row_venda['id'], "int")
												);
												$venda_modulos = mysql_query($query_venda_modulos, $conexao) or die(mysql_error());
												$row_venda_modulos = mysql_fetch_assoc($venda_modulos);
												$totalRows_venda_modulos = mysql_num_rows($venda_modulos);
												// fim - venda_modulos

												$venda_modulos_checked = 'n';
												do {

													if ($row_geral_tipo_modulo_listar['descricao'] == $row_venda_modulos['modulo']) {
														$venda_modulos_checked = 's';
													}
												} while ($row_venda_modulos = mysql_fetch_assoc($venda_modulos));
												?>

												<input name="venda_modulos[]" id="venda_modulos" type="checkbox" class="checkbox" value="<? echo $row_geral_tipo_modulo_listar['IdTipoModulo']; ?>" <?
																																																	if ($venda_modulos_checked == 's' or (($row_geral_tipo_modulo_listar['IdTipoModulo'] == 20 or $row_geral_tipo_modulo_listar['IdTipoModulo'] == 7) and $row_prospeccao['tipo_cliente'] == "n")) { ?>checked="checked" <? } ?> <? if (($row_geral_tipo_modulo_listar['IdTipoModulo'] == 20 or $row_geral_tipo_modulo_listar['IdTipoModulo'] == 7) and $row_prospeccao['tipo_cliente'] == "n") { ?>onclick="return false;" onkeydown="return false;" <? } ?> />

												<? echo $row_geral_tipo_modulo_listar['descricao']; ?>

												<? mysql_free_result($venda_modulos); ?>

											<? } while ($row_geral_tipo_modulo_listar = mysql_fetch_assoc($geral_tipo_modulo_listar)); ?>

											<? mysql_free_result($geral_tipo_modulo_listar); ?>

										</div>

									<? } while ($row_geral_tipo_modulo_categoria_listar = mysql_fetch_assoc($geral_tipo_modulo_categoria_listar)); ?>
								</fieldset>
								<? mysql_free_result($geral_tipo_modulo_categoria_listar); ?>

								<label for="venda_modulos[]" class="error">Selecione pelo menos um dos módulos acima</label>

							</div>
						<? } ?>
						<!-- fim - venda_modulos -->


						<!-- data_contrato -->
						<? if (
							($_GET['situacao'] == "editar" and
								($_GET['acao'] == "Alterar data do contrato"))
						) { ?>

							<div style="padding-bottom: 10px;">
								<div class="label_solicitacao2">Data do contrato*:</div>
								<input name="data_contrato" type="text" id="data_contrato" style="width: 170px;" />
							</div>

						<? } ?>
						<!-- fim - data_contrato -->


						<!-- Observação -->
						<div style="padding-bottom: 10px;">
							<? if (
								($_GET['acao'] == "Cancelar" and $_GET['resposta'] == "") or
								($_GET['acao'] == "Cancelar agendamento de $agenda_tipo_titulo" and $_GET['resposta'] == "") or
								($_GET['situacao'] == "editar" and $_GET['acao'] == "Dilação de prazo" and $row_venda['dilacao_prazo_proposto'] == 0) or
								($_GET['acao'] == "Alterar módulos") or
								($_GET['acao'] == "Alterar data do contrato")
							) { ?>
								<div class="label_solicitacao2">Motivo:</div>
							<? } else { ?>
								<div class="label_solicitacao2">Observações:</div>
							<? } ?>

							<? if (
								($_GET['situacao'] == "editar" and $_GET['acao'] == "Alterar observação")
							) { ?>
								<textarea name="observacao" id="observacao" style="width: 760px; height: 80px" /><? echo $row_venda['observacao']; ?></textarea>
							<? } else { ?>
								<textarea name="observacao" id="observacao" style="width: 760px; height: 80px" /></textarea>
							<? } ?>
						</div>
						<!-- fim - Observação -->


						<!-- Botões -->
						<div>
							<input name="id_solicitacao" type="hidden" value="<?php echo $row_venda['id']; ?>" />
							<input type="hidden" name="MM_update" value="form" />
							<input type="button" name="button" id="button" value="Salvar" class="botao_geral2" style="width: 70px" />

							<!-- Registrar reclamação ========================================================================================================================================= -->
							<? if (
								$row_venda['situacao'] != "criada" and $row_venda['situacao'] != "solucionada" and $row_venda['situacao'] != "cancelada" and
								$row_venda['contrato'] != "" and $row_venda['codigo_empresa'] != "" // contrato existe na tabela 'DA37s9'
							) {  ?>

								<a href="suporte_gerar.php?tipo_suporte=r&inloco=n&cobranca=n&cliente=<? echo $row_venda['codigo_empresa']; ?>6&contrato=<? echo $row_venda['contrato']; ?>&reclamacao_venda=<? echo $row_venda['id']; ?>" target="_blank" id="botao_geral2">Registrar reclamação</a>

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
mysql_free_result($venda);
mysql_free_result($usuario);
mysql_free_result($agenda);
mysql_free_result($venda_validade);
mysql_free_result($venda_contato);
mysql_free_result($prospeccao);
?>