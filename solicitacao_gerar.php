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
				case "text_sem_funcao_converte_caracter":
					$theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
					//$theValue = ($theValue != "") ? "'" . funcao_converte_caracter($theValue) . "'" : "NULL";
					break;
		}
		return $theValue;
	}
}

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
	$editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

require_once('parametros.php');

//region - usuário logado via SESSION
$colname_usuario = "-1";
if (isset($_SESSION['MM_Username'])) {
	$colname_usuario = $_SESSION['MM_Username'];
}
mysql_select_db($database_conexao, $conexao);
$query_usuario = sprintf("
SELECT * 
FROM usuarios 
WHERE usuario = %s", GetSQLValueString($colname_usuario, "text"));
$usuario = mysql_query($query_usuario, $conexao) or die(mysql_error());
$row_usuario = mysql_fetch_assoc($usuario);
$totalRows_usuario = mysql_num_rows($usuario);
//endregion - fim - usuário logado via SESSION

//region - SELECT - suporte
$colname_suporte = "-1";
if (isset($_GET['numero_protocolo'])) {
	$colname_suporte = $_GET['numero_protocolo'];
}
mysql_select_db($database_conexao, $conexao);
$query_suporte = sprintf("
SELECT 
	suporte.*,  
	(SELECT nome FROM usuarios WHERE usuarios.IdUsuario = suporte.id_usuario_responsavel) as usuario_responsavel 
FROM 
	suporte 
WHERE 
	suporte.id = %s and 
	suporte.solicita_solicitacao='s' and 
	suporte.status_flag='a' and 
	suporte.id_solicitacao IS NULL
", 
GetSQLValueString($colname_suporte, "int"));
$suporte = mysql_query($query_suporte, $conexao) or die(mysql_error());
$row_suporte = mysql_fetch_assoc($suporte);
$totalRows_suporte = mysql_num_rows($suporte);
//endregion - fim - SELECT - suporte

//region - SELECT - solicitacao_desmembrada
$colname_solicitacao_desmembrada = "-1";
if (isset($_GET['solicitacao_desmembrada'])) {
	$colname_solicitacao_desmembrada = $_GET['solicitacao_desmembrada'];
}
mysql_select_db($database_conexao, $conexao);
$query_solicitacao_desmembrada = sprintf("
SELECT 
	solicitacao.*,  
	(SELECT nome FROM usuarios WHERE usuarios.IdUsuario = solicitacao.id_usuario_responsavel) as usuario_responsavel, 
	(SELECT nome FROM usuarios WHERE usuarios.IdUsuario = solicitacao.id_operador) as usuario_operador 
FROM 
	solicitacao 
WHERE 
	solicitacao.id = %s and 
	solicitacao.situacao <> 'solucionada' and 
	solicitacao.situacao <> 'reprovada'
", 
GetSQLValueString($colname_solicitacao_desmembrada, "int"));
$solicitacao_desmembrada = mysql_query($query_solicitacao_desmembrada, $conexao) or die(mysql_error());
$row_solicitacao_desmembrada = mysql_fetch_assoc($solicitacao_desmembrada);
$totalRows_solicitacao_desmembrada = mysql_num_rows($solicitacao_desmembrada);
//endregion - fim - SELECT - solicitacao_desmembrada

//region - acessso --------------------------------------------------------------------------------------------------------------------------------------------------------------
$acesso = 0;
if (
	($totalRows_suporte == 1) and
	(
		($row_suporte['id_operador'] == $row_usuario['IdUsuario']) or
		($row_suporte['id_usuario_envolvido'] == $row_usuario['IdUsuario']) or
		($row_suporte['id_usuario_responsavel'] == $row_usuario['IdUsuario']) or
		($row_usuario['controle_praca'] == "Y" and $row_usuario['praca'] = $row_suporte['praca']))
) {

	$acesso = 1; // autorizado

} else if (
	($totalRows_solicitacao_desmembrada == 1) and
	(
		$row_usuario['controle_solicitacao']=='Y' or 
		$row_usuario['solicitacao_testador']=='Y'
	)
) {

	$acesso = 1; // autorizado

} else {

	$acesso = 0; // não autorizado

}

if ($acesso == 0) {
	//echo "Acesso não autorizado !";
	echo "<script>window.top.location = 'solicitacao.php?padrao=sim&" . $situacao_padrao . "';</script>";
	exit;
}
//endregion - fim - acesso ---------------------------------------------------------------------------------------------------------------------------------------------------------


//region - INSERT - solicitação
if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "solicitacao")) {

	if($totalRows_suporte > 0){

		$id_atual = $row_suporte['id'];
		$id_atual_titulo = "N. Protocolo:";
		$id_atual_campo = "protocolo_suporte";

		$empresa = $row_suporte['empresa'];
		$codigo_empresa = $row_suporte['codigo_empresa'];
		$contrato = $row_suporte['contrato'];
		$praca = $row_suporte['praca'];

	} else if($totalRows_solicitacao_desmembrada > 0){

		$id_atual = $row_solicitacao_desmembrada['id'];
		$id_atual_titulo = "Solicitação Desmembrada:";
		$id_atual_campo = "solicitacao_desmembrada";

		$empresa = @$_POST['empresa'];
		$codigo_empresa = @$_POST['codigo_empresa'];
		$contrato = @$_POST['contrato'];
		$praca = @$_POST['praca'];

	}

	$_POST['data_executavel'] = implode("-", array_reverse(explode("-", $_POST['data_executavel']))); // converter data formato brasileiro para americano (mysql)

	//region - seleciona o nome do programa escolhido
	$colname_geral_tipo_programa_ins = "-1";
	if (isset($_POST['id_programa'])) {
		$colname_geral_tipo_programa_ins = $_POST['id_programa'];
	}
	mysql_select_db($database_conexao, $conexao);
	$query_geral_tipo_programa_ins = sprintf("SELECT * FROM geral_tipo_programa WHERE id_programa=%s", GetSQLValueString($colname_geral_tipo_programa_ins, "int"));
	$geral_tipo_programa_ins = mysql_query($query_geral_tipo_programa_ins, $conexao) or die(mysql_error());
	$row_geral_tipo_programa_ins = mysql_fetch_assoc($geral_tipo_programa_ins);
	$totalRows_geral_tipo_programa_ins = mysql_num_rows($geral_tipo_programa_ins);
	//endregion - fim - seleciona o nome do programa escolhido

	//region - seleciona o nome do subprograma escolhido
	$colname_geral_tipo_subprograma_ins = "-1";
	if (isset($_POST['id_subprograma'])) {
		$colname_geral_tipo_subprograma_ins = $_POST['id_subprograma'];
	}
	mysql_select_db($database_conexao, $conexao);
	$query_geral_tipo_subprograma_ins = sprintf("SELECT * FROM geral_tipo_subprograma WHERE id_subprograma=%s", GetSQLValueString($colname_geral_tipo_subprograma_ins, "int"));
	$geral_tipo_subprograma_ins = mysql_query($query_geral_tipo_subprograma_ins, $conexao) or die(mysql_error());
	$row_geral_tipo_subprograma_ins = mysql_fetch_assoc($geral_tipo_subprograma_ins);
	$totalRows_geral_tipo_subprograma_ins = mysql_num_rows($geral_tipo_subprograma_ins);
	//endregion - fim - seleciona o nome do subprograma escolhido

	//region - se for IMPLEMENTAÇÃO
	$implementacao_mensagem_sim_nao = "";
	$implementacao_nao_justificativa = "";
	if ($_POST['tipo'] == "Implementação") {

		// A solicitação para implementação será realizada na versão desenvolvimento do sistema
		if (isset($_POST['implementacao_mensagem_sim_nao'])) {
			$implementacao_mensagem_sim_nao = $_POST['implementacao_mensagem_sim_nao'];
		}
		// fim - A solicitação para implementação será realizada na versão desenvolvimento do sistema

		// Preencha abaixo a justificativa para implementação na versão estável ou versão desejada. A justificativa será passível de análise
		if (isset($_POST['implementacao_nao_justificativa'])) {
			$implementacao_nao_justificativa = $_POST['implementacao_nao_justificativa'];
		}
		// fim - Preencha abaixo a justificativa para implementação na versão estável ou versão desejada. A justificativa será passível de análise

		// gravar a mensagem
		if ($implementacao_mensagem_sim_nao == "s") {
			$descricao_implementacao = "<br><br>IMPLEMENTAÇÃO - A solicitação para implementação será realizada na versão desenvolvimento do sistema.";
		} else if ($implementacao_mensagem_sim_nao == "n") {
			$descricao_implementacao = "<br><br>IMPLEMENTAÇÃO - Solicitado a implementação na versão estável ou versão desejada.";
			$descricao_implementacao .= "<br>Justificativa: " . $implementacao_nao_justificativa;
		} else {
			$descricao_implementacao = "";
		}
		// fim - gravar a mensagem

	} else {

		$descricao_implementacao = "";
	}
	//endregion - fim - se for IMPLEMENTAÇÃO


	$prioridade_justificativa = NULL;
	if (isset($_POST['prioridade_justificativa'])) {
		$prioridade_justificativa = $_POST['prioridade_justificativa'];
	}

	$versao = NULL;
	if (count(@$_POST['versao']) > 0) {
		$versao = implode(',', $_POST['versao']);
	}

	//region - insert - solicitacao
	$insertSQL = sprintf(
	"INSERT INTO solicitacao (
		titulo, dt_solicitacao, empresa, codigo_empresa, contrato, praca, 
		
		id_usuario_responsavel, 
		
		id_programa, programa, id_subprograma, subprograma, 
		
		tipo, implementacao_mensagem_sim_nao, implementacao_nao_justificativa, 
		
		versao, geral_tipo_distribuicao, tipo_bd, geral_tipo_ecf, situacao, prioridade, prioridade_justificativa, campo, data_executavel, hora_executavel, medida_tomada, 

		".$id_atual_campo.", 

		status, previsao_geral_inicio, previsao_geral, solicitante_leu
	) VALUES (
		%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s
	)",

	GetSQLValueString($_POST['titulo'], "text"),
	GetSQLValueString(date("Y-m-d H:i:s"), "date"),
	GetSQLValueString($empresa, "text"),
	GetSQLValueString($codigo_empresa, "text"),
	GetSQLValueString($contrato, "text"),
	GetSQLValueString($praca, "text"),

	GetSQLValueString($row_usuario['IdUsuario'], "int"), // id_usuario_responsavel

	GetSQLValueString($_POST['id_programa'], "int"),
	GetSQLValueString($row_geral_tipo_programa_ins['programa'], "text"),
	GetSQLValueString($_POST['id_subprograma'], "int"),
	GetSQLValueString($row_geral_tipo_subprograma_ins['subprograma'], "text"),

	GetSQLValueString($_POST['tipo'], "text"),
	GetSQLValueString($implementacao_mensagem_sim_nao, "text"),
	GetSQLValueString($implementacao_nao_justificativa, "text"),

	GetSQLValueString($versao, "text"),
	GetSQLValueString($_POST['geral_tipo_distribuicao'], "text"),
	GetSQLValueString($_POST['tipo_bd'], "text"),
	GetSQLValueString($_POST['geral_tipo_ecf'], "text"),
	GetSQLValueString("criada", "text"), // situação
	GetSQLValueString($_POST['prioridade'], "text"),
	GetSQLValueString($_POST['prioridade_justificativa'], "text"),
	GetSQLValueString($_POST['campo'], "text"),
	GetSQLValueString($_POST['data_executavel'], "date"),
	GetSQLValueString($_POST['hora_executavel'], "text"),
	GetSQLValueString(nl2br($_POST['medida_tomada']), "text"),

	GetSQLValueString($id_atual, "int"), // numero_protocolo // solicitacao_desmembrada

	GetSQLValueString("pendente operador", "text"), // status
	GetSQLValueString(date("Y-m-d H:i:s"), "date"), // previsao_geral_inicio
	GetSQLValueString(date("Y-m-d H:i:s"), "date"), // previsao_geral
	GetSQLValueString(date("Y-m-d H:i:s"), "date") // solicitante_leu
	); 

	mysql_select_db($database_conexao, $conexao);
	$Result1 = mysql_query($insertSQL, $conexao) or die(mysql_error());
	mysql_free_result($geral_tipo_programa_ins);
	mysql_free_result($geral_tipo_subprograma_ins);
	
	$IdSolicitacaoNova = mysql_insert_id(); // pega o numero da ultima solicitação

	//endregion - fim - insert - solicitacao

	$descricao = $_POST['descricao'] . $descricao_implementacao; // soliciticação + implementação

	//region - insert - solicitacao_descricoes
	$insertSQL_solicitacao_descricoes  = sprintf(
		"INSERT INTO solicitacao_descricoes (
			id_solicitacao, descricao, data, tipo_postagem, id_usuario_responsavel
		) VALUES (
			%s, %s, %s, %s, %s
		)",
		
		GetSQLValueString($IdSolicitacaoNova, "int"),
		GetSQLValueString($descricao, "text"),
		GetSQLValueString(date("Y-m-d H:i:s"), "date"),
		GetSQLValueString("Nova Solicitação", "text"),
		GetSQLValueString($row_usuario['IdUsuario'], "int")
	);
	$Result_solicitacao_descricoes = mysql_query($insertSQL_solicitacao_descricoes, $conexao) or die(mysql_error());
	//endregion - fim - insert - solicitacao_descricoes

	if($totalRows_suporte > 0){

		//region - update - suporte (adiciona o num da solicitacao no suporte)
		$updateSQL_suporte_solicitacao  = sprintf("
		UPDATE 
			suporte 
		SET 
			id_solicitacao = $IdSolicitacaoNova, 
			status='', 
			situacao='solucionada',
			status_flag='f', 
			avaliacao_atendimento = %s,
			avaliacao_atendimento_justificativa = %s 
		WHERE 
			id = %s",
			GetSQLValueString($_POST['avaliacao_atendimento'], "text"),
			GetSQLValueString($_POST['avaliacao_atendimento_justificativa'], "text"),
			GetSQLValueString($row_suporte['id'], "int"));
		mysql_select_db($database_conexao, $conexao);
		$Result_suporte_solicitacao = mysql_query($updateSQL_suporte_solicitacao, $conexao) or die(mysql_error());
		//endregion - fim - update - suporte (adiciona o num da solicitacao no suporte)

	} else if($totalRows_solicitacao_desmembrada > 0){

		$descricao_solicitacao_desmembrada_link = '<a href="solicitacao_editar.php?id_solicitacao='.$IdSolicitacaoNova.'" target="_blank">'.$IdSolicitacaoNova.'</a>';
		$descricao_solicitacao_desmembrada = '<br>Solicitação gerada: '.$descricao_solicitacao_desmembrada_link;

		//region - insert - solicitacao_descricoes
		$insertSQL_solicitacao_descricoes  = sprintf(
		"INSERT INTO solicitacao_descricoes (
			id_solicitacao, descricao, data, tipo_postagem, id_usuario_responsavel
		) VALUES (
			%s, %s, %s, %s, %s
		)",
		GetSQLValueString($id_atual, "int"),
		GetSQLValueString($descricao_solicitacao_desmembrada, "text_sem_funcao_converte_caracter"),
		GetSQLValueString(date("Y-m-d H:i:s"), "date"),
		GetSQLValueString("Desmembramento de Solicitação", "text"),
		GetSQLValueString($row_usuario['IdUsuario'], "int")
		);

		$Result_solicitacao_descricoes = mysql_query($insertSQL_solicitacao_descricoes, $conexao) or die(mysql_error());
		//endregion - fim - insert - solicitacao_descricoes

	}

	//region - função que envia e-mail
	$tipo_postagem = "Aviso de nova solicitação de suporte - $IdSolicitacaoNova - " . $_POST['titulo'] . "";
	$descricao = "
	Link: <a href='http://success.inf.br/solicitacao_editar.php?id_solicitacao=$IdSolicitacaoNova&padrao=sim'>Clique aqui para acessar a solicitação</a>
	
	Título: " . $_POST['titulo'] . "
	N. Solicitação: " . $IdSolicitacaoNova . " 
	Tipo: " . $_POST['tipo'] . "
	" . $id_atual_titulo . " " . $row_suporte['id'] . " 
	Prioridade: " . $_POST['prioridade'] . " 
	
	Data de solicitacão: " . date("d-m-Y H:i:s") . " 
	Empresa: " . $empresa . "
	Contrato: " . $contrato . "
	Responsável: " . $usuario_responsavel . " 
	Praça: " . $praca . " 
	
	Versão: " . $_POST['versao'] . "
	Distribuição: " . $_POST['geral_tipo_distribuicao'] . " 
	
	Programa: " . $row_geral_tipo_programa_ins['programa'] . " 
	Subprograma: " . $row_geral_tipo_subprograma_ins['subprograma'] . " 
	Campo: " . $_POST['campo'] . " 
	Executável: " . date('d-m-Y', strtotime($_POST['data_executavel'])) . " - " . $_POST['hora_executavel'] . "
	
	Tipo de banco de dados: " . $_POST['tipo_bd'] . " 
	Tipo de ECF: " . $_POST['geral_tipo_ecf'] . " 
	
	Medida tomada: " . $_POST['medida_tomada'] . "
	
	Descrição da anomalia: " . $descricao . "";

	require_once('emails.php');
	email_solicitacao($IdSolicitacaoNova, $tipo_postagem, $descricao);
	//endregion - fim - função que envia e-mail

	//region - redireciona
	$insertGoTo = "solicitacao_editar.php?id_solicitacao=" . $IdSolicitacaoNova . "&padrao=sim";
	if (isset($_SERVER['QUERY_STRING'])) {
		$insertGoTo .= (strpos($insertGoTo, '?')) ? "&" : "?";
		$insertGoTo .= $_SERVER['QUERY_STRING'];
	}
	echo sprintf("<meta http-equiv=\"refresh\" content=\"0; url=%s\">", $insertGoTo);
	//endregion - fim - redireciona

	exit;
}
//endregion - fim - INSERT - solicitação


//region - tipo de solicitações
mysql_select_db($database_conexao, $conexao);
$query_solicitacao_tipo_solicitacao = "SELECT * FROM solicitacao_tipo_solicitacao ORDER BY titulo ASC";
$solicitacao_tipo_solicitacao = mysql_query($query_solicitacao_tipo_solicitacao, $conexao) or die(mysql_error());
$row_solicitacao_tipo_solicitacao = mysql_fetch_assoc($solicitacao_tipo_solicitacao);
$totalRows_solicitacao_tipo_solicitacao = mysql_num_rows($solicitacao_tipo_solicitacao);
//endregion - fim - tipo de solicitações

//region - tipo versões
mysql_select_db($database_conexao, $conexao);
$query_geral_tipo_versao = "SELECT * FROM geral_tipo_versao WHERE status = 1 ORDER BY titulo ASC";
$geral_tipo_versao = mysql_query($query_geral_tipo_versao, $conexao) or die(mysql_error());
$row_geral_tipo_versao = mysql_fetch_assoc($geral_tipo_versao);
$totalRows_geral_tipo_versao = mysql_num_rows($geral_tipo_versao);
//endregion - fim - tipo versões

//region - tipo distribuições
mysql_select_db($database_conexao, $conexao);
$query_geral_tipo_distribuicao = "SELECT * FROM geral_tipo_distribuicao ORDER BY titulo ASC";
$geral_tipo_distribuicao = mysql_query($query_geral_tipo_distribuicao, $conexao) or die(mysql_error());
$row_geral_tipo_distribuicao = mysql_fetch_assoc($geral_tipo_distribuicao);
$totalRows_geral_tipo_distribuicao = mysql_num_rows($geral_tipo_distribuicao);
//endregion - fim - tipo distribuições

//region - tipo banco de dados
mysql_select_db($database_conexao, $conexao);
$query_geral_tipo_banco_de_dados = "SELECT * FROM geral_tipo_banco_de_dados ORDER BY titulo ASC";
$geral_tipo_banco_de_dados = mysql_query($query_geral_tipo_banco_de_dados, $conexao) or die(mysql_error());
$row_geral_tipo_banco_de_dados = mysql_fetch_assoc($geral_tipo_banco_de_dados);
$totalRows_geral_tipo_banco_de_dados = mysql_num_rows($geral_tipo_banco_de_dados);
//endregion - fim - tipo banco de dados

//region - tipo ecfs
mysql_select_db($database_conexao, $conexao);
$query_geral_tipo_ecf = "SELECT * FROM geral_tipo_ecf ORDER BY titulo ASC";
$geral_tipo_ecf = mysql_query($query_geral_tipo_ecf, $conexao) or die(mysql_error());
$row_geral_tipo_ecf = mysql_fetch_assoc($geral_tipo_ecf);
$totalRows_geral_tipo_ecf = mysql_num_rows($geral_tipo_ecf);
//endregion - fim - tipo ecfs

//region - geral_tipo_programa
mysql_select_db($database_conexao, $conexao);
$query_geral_tipo_programa = "SELECT * FROM geral_tipo_programa ORDER BY programa ASC";
$geral_tipo_programa = mysql_query($query_geral_tipo_programa, $conexao) or die(mysql_error());
$row_geral_tipo_programa = mysql_fetch_assoc($geral_tipo_programa);
$totalRows_geral_tipo_programa = mysql_num_rows($geral_tipo_programa);
//endregion - fim - geral_tipo_programa

//region - tipo de prioridades
mysql_select_db($database_conexao, $conexao);
$query_solicitacao_tipo_prioridade = "SELECT * FROM solicitacao_tipo_prioridade ORDER BY titulo ASC";
$solicitacao_tipo_prioridade = mysql_query($query_solicitacao_tipo_prioridade, $conexao) or die(mysql_error());
$row_solicitacao_tipo_prioridade = mysql_fetch_assoc($solicitacao_tipo_prioridade);
$totalRows_solicitacao_tipo_prioridade = mysql_num_rows($solicitacao_tipo_prioridade);
//endregion - fim - tipo de prioridades

//region - cliente_antigo_listar
mysql_select_db($database_conexao, $conexao);
$query_cliente_antigo_listar = "
SELECT 
	da37.codigo17, da37.cliente17, 
	da01.nome1, 
	geral_tipo_praca_executor.praca as praca 
FROM 
	da37 
LEFT JOIN 
	da01 ON da37.cliente17 = da01.codigo1 
LEFT JOIN 
    geral_tipo_praca_executor ON geral_tipo_praca_executor.IdExecutor = da37.vendedor17
WHERE 
	da01.sr_deleted <> 'T' and da37.sr_deleted <> 'T' 
ORDER BY 
	da01.nome1 ASC
";
$cliente_antigo_listar = mysql_query($query_cliente_antigo_listar, $conexao) or die(mysql_error());
$row_cliente_antigo_listar = mysql_fetch_assoc($cliente_antigo_listar);
$totalRows_cliente_antigo_listar = mysql_num_rows($cliente_antigo_listar);
//endregion - fim - cliente_antigo_listar
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">

<link rel="stylesheet" href="css/suporte.css" type="text/css" media="screen" />


<script type="text/javascript" src="js/jquery.js"></script>

<script type='text/javascript' src='js/jquery.autocomplete.js'></script>
<link rel="stylesheet" type="text/css" href="css/jquery.autocomplete.css" />

<script type="text/javascript" src="js/jquery.maskedinput-1.2.2.min.js"></script>

<script type="text/javascript" src="js/jquery.rsv.js"></script>

<script type="text/javascript">
function myOnComplete() {
	return true;
}

$(document).ready(function() {

	<? if($totalRows_solicitacao_desmembrada > 0){ ?>
		$("select[name=id_programa] option[value='<? echo $row_solicitacao_desmembrada['id_programa']; ?>']").attr("selected", "selected");

		$.post("solicitacao_tipo_subprograma.php", {
				id_programa: '<? echo $row_solicitacao_desmembrada['id_programa']; ?>'
			},
			function(valor) {
				$("select[name=id_subprograma]").html(valor);
				$("select[name=id_subprograma] option[value='<? echo $row_solicitacao_desmembrada['id_subprograma']; ?>']").attr("selected", "selected");
			}
		);

	<? } ?>

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
	// geral_tipo_programa - fim

	// mascara
	$('#data_executavel').mask('99-99-9999', {
		placeholder: " "
	});
	$('#hora_executavel').mask('99:99:99', {
		placeholder: " "
	});
	// mascara - fim

	$("select[id='tipo'] option[value='Dúvida']").remove();

	var rules = [];

	//region tipo (Implementação) *******************************************************************
	var tipo_abrir_tela = $("select[id='tipo'] option:selected").val();

	if(tipo_abrir_tela == "Implementação"){

		$("input[id='implementacao_mensagem_sim_nao']").attr("checked", false); // limpa 'radio'
		$("textarea[id='implementacao_nao_justificativa']").val(''); // limpa 'justificativa'

		$("div[id=implementacao_mensagem]").show(); //
		$("div[id=implementacao_nao]").hide(); // oculta 'justificativa'

		rules.push("required,implementacao_mensagem_sim_nao,Informe se concorda ou não com a implementação na versão em desenvolvimento."); // adiciona a regra

	} else {

		$("input[id='implementacao_mensagem_sim_nao']").attr("checked", false); // limpa 'radio'
		$("textarea[id='implementacao_nao_justificativa']").val(''); // limpa 'justificativa'

		$("div[id=implementacao_mensagem]").hide(); // oculta 'radio'
		$("div[id=implementacao_nao]").hide(); // oculta 'justificativa'

	}

	$("select[id='tipo']").change(function() { // ao mudar o valor do select 'tipo'

		$("select[id='tipo'] option:selected").each(function() {

			var tipo_atual = $(this).val();

			// se tipo é: Implementação
			if (tipo_atual == "Implementação") {

				$("div[id=implementacao_mensagem]").show();

				//region - remove regra (implementacao_mensagem_sim_nao)
				var regra_implementacao_mensagem_sim_nao = "required,implementacao_mensagem_sim_nao,Informe se concorda ou não com a implementação na versão em desenvolvimento.";
				var regra = $.inArray(regra_implementacao_mensagem_sim_nao, rules); // verifica se regra existe (caso não, retorna -1)
				if (regra > -1) {
					for (i = regra; i < rules.length - 1; i++) {
						rules[i] = rules[i + 1];
					}
					rules.pop();
					regra = "";
				}
				//endregion - fim - remove regra (implementacao_mensagem_sim_nao)

				rules.push("required,implementacao_mensagem_sim_nao,Informe se concorda ou não com a implementação na versão em desenvolvimento."); // adiciona a regra

				//region - implementacao_nao ---------------------------------------------------------------
				$("input[id='implementacao_mensagem_sim_nao']").change(function() {

					// se 'não concordo'
					if ($("input[id='implementacao_mensagem_sim_nao']:checked").val() == 'n') {

						$("div[id=implementacao_nao]").show();

						// remove regra (implementacao_nao_justificativa)
						var regra_implementacao_nao_justificativa = "required,implementacao_nao_justificativa,Informe a justificativa para implementação na versão estável ou versão desejada.";
						var regra = $.inArray(regra_implementacao_nao_justificativa, rules); // verifica se regra existe (caso não, retorna -1)
						if (regra > -1) {
							for (i = regra; i < rules.length - 1; i++) {
								rules[i] = rules[i + 1];
							}
							rules.pop();
							regra = "";
						}
						// fim - remove regra (implementacao_nao_justificativa)
						rules.push("required,implementacao_nao_justificativa,Informe a justificativa para implementação na versão estável ou versão desejada."); // adc

					}
					// fim - se 'não concordo'

					// senão ...
					else {

						$("textarea[id='implementacao_nao_justificativa']").val('');
						$("div[id=implementacao_nao]").hide();

						// remove regra (implementacao_nao_justificativa)
						var regra_implementacao_nao_justificativa = "required,implementacao_nao_justificativa,Informe a justificativa para implementação na versão estável ou versão desejada.";
						var regra = $.inArray(regra_implementacao_nao_justificativa, rules); // verifica se regra existe (caso não, retorna -1)
						if (regra > -1) {
							for (i = regra; i < rules.length - 1; i++) {
								rules[i] = rules[i + 1];
							}
							rules.pop();
							regra = "";
						}
						// fim - remove regra (implementacao_nao_justificativa)

					}
					// fim - senão ...
				});
				//endregion - fim - implementacao_nao ---------------------------------------------------------

			}
			// fim - se tipo é: Implementação

			// se não é Implementação
			else {

				$("input[id='implementacao_mensagem_sim_nao']").attr("checked", false);
				$("textarea[id='implementacao_nao_justificativa']").val('');

				$("div[id=implementacao_mensagem]").hide();
				$("div[id=implementacao_nao]").hide();

				// remove regra (implementacao_mensagem_sim_nao)
				var regra_implementacao_mensagem_sim_nao = "required,implementacao_mensagem_sim_nao,Informe se concorda ou não com a implementação na versão em desenvolvimento.";
				var regra = $.inArray(regra_implementacao_mensagem_sim_nao, rules); // verifica se regra existe (caso não, retorna -1)
				if (regra > -1) {
					for (i = regra; i < rules.length - 1; i++) {
						rules[i] = rules[i + 1];
					}
					rules.pop();
					regra = "";
				}
				// fim - remove regra (implementacao_mensagem_sim_nao)

				// remove regra (implementacao_nao_justificativa)
				var regra_implementacao_nao_justificativa = "required,implementacao_nao_justificativa,Informe a justificativa para implementação na versão estável ou versão desejada.";
				var regra = $.inArray(regra_implementacao_nao_justificativa, rules); // verifica se regra existe (caso não, retorna -1)
				if (regra > -1) {
					for (i = regra; i < rules.length - 1; i++) {
						rules[i] = rules[i + 1];
					}
					rules.pop();
					regra = "";
				}
				// fim - remove regra (implementacao_nao_justificativa)

			}
			// fim - se não é Implementação

		});

	})
	//endregion - fim - tipo (Implementação) ********************************************************

	//region - prioridade ***************************************************************************
	$("table[id=prioridade_justificativa_caixa]").hide();

	$("textarea[id='implementacao_nao_justificativa']").val(''); // limpa 'justificativa'

	$("select[id='prioridade']").change(function() { // ao mudar o valor do select 'prioridade'
		$("select[id='prioridade'] option:selected").each(function() {
			prioridade_atual = $(this).val();

			// se prioridade é: Alta
			if (prioridade_atual == "Alta") {

				$("table[id=prioridade_justificativa_caixa]").show();

				$("textarea[id='prioridade_justificativa']").val('');

				// remove regra (prioridade_justificativa)
				var regra_prioridade_justificativa = "length>=30,prioridade_justificativa,Informe a justificativa da prioridade Alta com no mínimo 30 caracteres.";
				var regra = $.inArray(regra_prioridade_justificativa, rules); // verifica se regra existe (caso não, retorna -1)
				if (regra > -1) {
					for (i = regra; i < rules.length - 1; i++) {
						rules[i] = rules[i + 1];
					}
					rules.pop();
					regra = "";
				}
				// fim - remove regra (prioridade_justificativa)

				rules.push("length>=30,prioridade_justificativa,Informe a justificativa da prioridade Alta com no mínimo 30 caracteres."); // adiciona a regra

			}
			// fim - se prioridade é: Alta

			// se não
			else {

				$("table[id=prioridade_justificativa_caixa]").hide();

				$("textarea[id='prioridade_justificativa']").val('');

				// remove regra (prioridade_justificativa)
				var regra_prioridade_justificativa = "length>=30,prioridade_justificativa,Informe a justificativa da prioridade Alta com no mínimo 30 caracteres.";
				var regra = $.inArray(regra_prioridade_justificativa, rules); // verifica se regra existe (caso não, retorna -1)
				if (regra > -1) {
					for (i = regra; i < rules.length - 1; i++) {
						rules[i] = rules[i + 1];
					}
					rules.pop();
					regra = "";
				}
				// fim - remove regra (prioridade_justificativa)

			}
			// fim - se não

		});
	})
	//endregion - fim - prioridade ******************************************************************

	//region - validação
	<? if($totalRows_suporte > 0){ ?>
	rules.push("required,avaliacao_atendimento,Informe a avaliação do atendimento.");
	rules.push("if:avaliacao_atendimento=Ruim,required,avaliacao_atendimento_justificativa,Informe a justificativa da avaliação do atendimento");
	rules.push("if:avaliacao_atendimento=Péssimo,required,avaliacao_atendimento_justificativa,Informe a justificativa da avaliação do atendimento");
	<? } ?>

	rules.push("required,titulo,Informe o título.");
	rules.push("required,tipo,Informe o tipo.");
	rules.push("required,versao,Informe a versão.");
	rules.push("required,geral_tipo_distribuicao,Informe a distribuição.");
	rules.push("required,id_programa,Informe o programa.");
	rules.push("required,id_subprograma,Informe o subprograma.");
	rules.push("required,campo,Informe o campo.");
	rules.push("required,data_executavel,Informe a data do programa executável.");
	rules.push("required,hora_executavel,Informe a hora do programa executável.");
	rules.push("required,prioridade,Informe a prioridade.");
	rules.push("length>50,descricao,Informe a descrição com no mínimo 50 caracteres.");

	$("#solicitacao").RSV({
		onCompleteHandler: myOnComplete,
		rules: rules
	});
	//endregion - fim - validação

	<? if($totalRows_solicitacao_desmembrada > 0){ ?>
		// cliente_antigo
		$("select[name='cliente_antigo']").change(function () { // ao mudar o valor do select
			
			var empresa_atual = $(this).find(":selected").attr('data-empresa');
			var codigo_empresa_atual = $(this).find(":selected").attr('data-codigo_empresa');
			var contrato_atual = $(this).find(":selected").val();
			var praca_atual = $(this).find(":selected").attr('data-praca');

			$('#empresa').val(empresa_atual); 
			$('#codigo_empresa').val(codigo_empresa_atual); 
			$('#contrato').val(contrato_atual); 
			$('#praca').val(praca_atual);

		});
		// fim - cliente_antigo
	<? } ?>

});
</script>

<title>Nova solicitação</title>
</head>

<body>

<form action="<?php echo $editFormAction; ?>" method="POST" name="solicitacao" id="solicitacao">

	<div class="div_solicitacao_linhas">
		<table cellspacing="0" cellpadding="0" width="100%">
			<tr>

				<td style="text-align:left">
					Nova solicitação 
					<? if($totalRows_suporte > 0){ ?>de protocolo número: <? echo $_GET['numero_protocolo']; ?><? } ?>
					<? if($totalRows_solicitacao_desmembrada > 0){ ?>desmembrada de nº <? echo $_GET['solicitacao_desmembrada']; ?><? } ?>
				</td>

				<td style="text-align: right">
					&lt;&lt; <a href="solicitacao.php?padrao=sim&<? echo $situacao_padrao; ?>">Voltar</a> |
					Usuário logado: <? echo $row_usuario['nome']; ?> |
					<a href="painel/padrao_sair.php">Sair</a>
				</td>

			</tr>
		</table>
	</div>

	<? if($totalRows_suporte > 0){ ?>

		<div class="div_solicitacao_linhas2">
			<table cellspacing="0" cellpadding="0" width="100%">
				<tr>
					<td style="text-align:left">
						<span class="label_solicitacao">Empresa: </span>
						<?php echo $row_suporte['empresa']; ?> |
						<span class="label_solicitacao">Contrato: </span> 
						<?php echo $row_suporte['contrato']; ?> |
						<span class="label_solicitacao">Praça: </span>
						<?php echo $row_suporte['praca']; ?>
					</td>
				</tr>
			</table>
		</div>

	<? } else if($totalRows_solicitacao_desmembrada > 0){ ?>

		<div class="div_solicitacao_linhas2">
			<table cellspacing="0" cellpadding="0" width="100%">
				<tr>

					<td style="text-align:left" width="467">
						<span class="label_solicitacao"><label id="label_cliente_antigo">Empresa (<? echo $totalRows_cliente_antigo_listar; ?>):<span id="req">*</span></label></span>
						<br>
						<select name="cliente_antigo" id="cliente_antigo" style="width: 420px;">
							<?php do { ?>
								<option value="<?php echo $row_cliente_antigo_listar['codigo17']; ?>" 
								<? if ($row_cliente_antigo_listar['codigo17'] == $row_solicitacao_desmembrada['contrato']) { echo "selected=\"selected\""; } ?> 
								data-empresa="<?php echo utf8_encode($row_cliente_antigo_listar['nome1']); ?>" 
								data-codigo_empresa="<?php echo $row_cliente_antigo_listar['cliente17']; ?>" 
								data-praca="<?php echo utf8_encode($row_cliente_antigo_listar['praca']); ?>" >
								<?php echo utf8_encode($row_cliente_antigo_listar['nome1']); ?>
								</option>
							<?php } while ($row_cliente_antigo_listar = mysql_fetch_assoc($cliente_antigo_listar)); ?>
						</select>
						<input type="hidden" name="empresa" id="empresa" value="<?php echo $row_solicitacao_desmembrada['empresa']; ?>" readonly />
						<input type="hidden" name="codigo_empresa" id="codigo_empresa" value="<?php echo $row_solicitacao_desmembrada['codigo_empresa']; ?>" readonly />
					</td>

					<td style="text-align:left" width="200">
						<span class="label_solicitacao">Contrato:</span>
						<br>
						<input type="text" name="contrato" id="contrato" style="width: 150px;" value="<?php echo $row_solicitacao_desmembrada['contrato']; ?>" readonly />
					</td>

					<td style="text-align:left">
						<span class="label_solicitacao">Praça:</span>
						<br>
						<input type="text" name="praca" id="praca" style="width: 250px;" value="<?php echo $row_solicitacao_desmembrada['praca']; ?>" readonly />
					</td>

				</tr>
			</table>
		</div>

	<? } ?>


	<? if($totalRows_suporte > 0){ ?>
	<!-- Avaliação do atendimento do suporte -->
	<div class="div_solicitacao_linhas3">
		<table cellspacing="0" cellpadding="0" width="100%">
			<tr>

				<td style="text-align: left">
					<span class="label_solicitacao">Por favor, avalie o atendimento no suporte <? echo $_GET['numero_protocolo']; ?>: </span>
					<br>
					<fieldset>
						<input name="avaliacao_atendimento" id="avaliacao_atendimento" type="radio" value="Excelente" validate="required:true"> Excelente
						<input name="avaliacao_atendimento" id="avaliacao_atendimento" type="radio" value="Bom" validate="required:true"> Bom
						<input name="avaliacao_atendimento" id="avaliacao_atendimento" type="radio" value="Regular" validate="required:true"> Regular
						<input name="avaliacao_atendimento" id="avaliacao_atendimento" type="radio" value="Ruim" validate="required:true"> Ruim
						<input name="avaliacao_atendimento" id="avaliacao_atendimento" type="radio" value="Péssimo" validate="required:true"> Péssimo
					</fieldset>
				</td>

			</tr>
		</table>
	</div>

	<div class="div_solicitacao_linhas2">
		<table cellspacing="0" cellpadding="0" width="100%">
			<tr>

				<td style="text-align: left">
					<span class="label_solicitacao">Justificativa da avaliação do atendimento no suporte <? echo $_GET['numero_protocolo']; ?>: </span>
					<br>
					<textarea name="avaliacao_atendimento_justificativa" id="avaliacao_atendimento_justificativa" style="margin-top: 2px; width: 945px; height: 40px;"></textarea>
				</td>

			</tr>
		</table>
	</div>
	<!-- fim - Avaliação do atendimento do suporte -->
	<? } ?>

	<div class="div_solicitacao_linhas3">
		<table cellspacing="0" cellpadding="0" width="100%">
			<tr>
				<td style="text-align:left">
					<span class="label_solicitacao">Título*: </span>
					<input name="titulo" type="text" id="titulo" size="115">
				</td>

				<td style="text-align:right">
					<span class="label_solicitacao">Tipo de solicitação*: </span>
					<? 
					$tipo_atual = NULL;
					if($totalRows_solicitacao_desmembrada > 0){ 
						$tipo_atual = $row_solicitacao_desmembrada['tipo']; 
					}
					?>
					<select name="tipo" id="tipo">
						<option value="">Escolha ...</option>
						<?php
						do {
						?>
							<option 
							value="<?php echo $row_solicitacao_tipo_solicitacao['titulo'] ?>" 
							<? if($row_solicitacao_tipo_solicitacao['titulo'] == $tipo_atual) { echo "selected=\"selected\""; } ?>>
								<?php echo $row_solicitacao_tipo_solicitacao['titulo'] ?>
							</option>
						<?php
						} while ($row_solicitacao_tipo_solicitacao = mysql_fetch_assoc($solicitacao_tipo_solicitacao));
						$rows = mysql_num_rows($solicitacao_tipo_solicitacao);
						if ($rows > 0) {
							mysql_data_seek($solicitacao_tipo_solicitacao, 0);
							$row_solicitacao_tipo_solicitacao = mysql_fetch_assoc($solicitacao_tipo_solicitacao);
						}
						?>
					</select>
				</td>

			</tr>
		</table>
	</div>

	<!-- Implementação -->
	<div id="implementacao_mensagem" style="border: 2px solid #06C; padding: 5px; margin-bottom: 5px;">
		<span class="label_solicitacao">
			A solicitação para implementação será realizada na versão desenvolvimento do sistema.
		</span>
		<br>
		<fieldset id="implementacao_mensagem_sim_nao">
			<input id="implementacao_mensagem_sim_nao" name="implementacao_mensagem_sim_nao" type="radio" value="s" /> concordo
			<input id="implementacao_mensagem_sim_nao" name="implementacao_mensagem_sim_nao" type="radio" value="n" /> não concordo
		</fieldset>
	</div>

	<div id="implementacao_nao" style="border: 2px solid #06C; padding: 5px; margin-bottom: 5px;">
		<span class="label_solicitacao">
			Preencha abaixo a justificativa para implementação na versão estável ou versão desejada. A justificativa será passível de análise.
		</span>
		<br>
		<textarea name="implementacao_nao_justificativa" id="implementacao_nao_justificativa" style="margin-top: 2px; width: 945px; height: 70px;"></textarea>
	</div>
	<!-- Implementação -->

	<div class="div_solicitacao_linhas2">
		<table cellspacing="0" cellpadding="0" width="100%">
			<tr>
				<td style="text-align: left">
					<span class="label_solicitacao">Versão*: </span>

					<fieldset>
						<?php do { ?>

							<? if($totalRows_suporte > 0){ ?>

								<input name="versao[]" id="versao" type="checkbox" class="checkbox" 
								value="<? echo $row_geral_tipo_versao['IdTipoVersao']; ?>" 
								<? if (in_array($row_geral_tipo_versao['IdTipoVersao'], explode(',', $row_suporte['versao']))) {  ?>checked="checked" <? } ?> />
								<? echo $row_geral_tipo_versao['titulo']; ?>

							<? } else if($totalRows_solicitacao_desmembrada > 0){ ?>

								<input name="versao[]" id="versao" type="checkbox" class="checkbox" 
								value="<? echo $row_geral_tipo_versao['IdTipoVersao']; ?>" 
								<? if (in_array($row_geral_tipo_versao['IdTipoVersao'], explode(',', $row_solicitacao_desmembrada['versao']))) {  ?>checked="checked" <? } ?> />
								<? echo $row_geral_tipo_versao['titulo']; ?>

							<? } ?>

						<?php } while ($row_geral_tipo_versao = mysql_fetch_assoc($geral_tipo_versao)); ?>
					</fieldset>

				</td>

				<td style="text-align:right">
					<span class="label_solicitacao">Distribuição*: </span>
					<? 
					$geral_tipo_distribuicao_atual = NULL;
					if($totalRows_solicitacao_desmembrada > 0){ 
						$geral_tipo_distribuicao_atual = $row_solicitacao_desmembrada['geral_tipo_distribuicao']; 
					}
					?>
					<select name="geral_tipo_distribuicao" id="geral_tipo_distribuicao" style="margin-top: 2px;">
						<option value="">Escolha ...</option>
						<?php do { ?>
							<option 
							value="<?php echo $row_geral_tipo_distribuicao['titulo'] ?>" 
							<? if($row_geral_tipo_distribuicao['titulo'] == $geral_tipo_distribuicao_atual) { echo "selected=\"selected\""; } ?>>
								<?php echo $row_geral_tipo_distribuicao['titulo'] ?>
							</option>
						<?php
						} while ($row_geral_tipo_distribuicao = mysql_fetch_assoc($geral_tipo_distribuicao));
						$rows = mysql_num_rows($geral_tipo_distribuicao);
						if ($rows > 0) {
							mysql_data_seek($geral_tipo_distribuicao, 0);
							$row_geral_tipo_distribuicao = mysql_fetch_assoc($geral_tipo_distribuicao);
						}
						?>
					</select>
				</td>

			</tr>
		</table>
	</div>

	<div class="div_solicitacao_linhas3">
		<table cellspacing="0" cellpadding="0" width="100%">
			<tr>

				<td style="text-align: left" width="310px">
					<span class="label_solicitacao">Programa*: </span><br>
					<select name="id_programa">
						<option value="">Escolha ...</option>
						<?php do { ?>
							<option value="<?php echo $row_geral_tipo_programa['id_programa'] ?>" 
							<?php if ((isset($_GET['id_programa'])) and (!(strcmp($row_geral_tipo_programa['id_programa'], $_GET['id_programa'])))) { echo "selected=\"selected\""; } ?>>
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
				</td>

				<td style="text-align: left">
					<span class="label_solicitacao">Subprograma*: </span><br>
					<select name="id_subprograma">
						<option value="">Escolha um programa primeiro ... </option>
					</select>
				</td>

				<td style="text-align: right;">
					<span class="label_solicitacao">Campo*: </span>
					<? 
					$campo_atual = NULL;
					if($totalRows_solicitacao_desmembrada > 0){ 
						$campo_atual = $row_solicitacao_desmembrada['campo']; 
					}
					?>
					<input name="campo" type="text" id="campo" size="30" style="margin-top: 2px;" value="<? if($campo_atual <> NULL) { echo $campo_atual; } ?>">
				</td>

			</tr>
		</table>
	</div>

	<div class="div_solicitacao_linhas2">
		<table cellspacing="0" cellpadding="0" width="100%">
			<tr>

				<td width="250" style="text-align: left;">
					<span class="label_solicitacao">Data do executável*: </span>
					<? 
					$data_executavel_atual = NULL;
					if($totalRows_solicitacao_desmembrada > 0){ 
						$data_executavel_atual = $row_solicitacao_desmembrada['data_executavel']; 
					}
					?>
					<input name="data_executavel" type="text" id="data_executavel" size="15" value="<? if($data_executavel_atual <> NULL) { echo date('d-m-Y', strtotime($data_executavel_atual)); } ?>">
				</td>

				<td style="text-align: left;">
					<span class="label_solicitacao">Hora do executável*: </span>
					<? 
					$hora_executavel_atual = NULL;
					if($totalRows_solicitacao_desmembrada > 0){ 
						$hora_executavel_atual = $row_solicitacao_desmembrada['hora_executavel']; 
					}
					?>
					<input name="hora_executavel" type="text" id="hora_executavel" size="10" value="<? if($hora_executavel_atual <> NULL) { echo $hora_executavel_atual; } ?>">
				</td>

				<td style="text-align: right" width="300">
					<span class="label_solicitacao">Banco de dados: </span>
					<select name="tipo_bd" id="tipo_bd">
						<option value="">Escolha ...</option>
						<?php
						do {
						?>
							<option value="<?php echo $row_geral_tipo_banco_de_dados['titulo'] ?>"><?php echo $row_geral_tipo_banco_de_dados['titulo'] ?></option>
						<?php
						} while ($row_geral_tipo_banco_de_dados = mysql_fetch_assoc($geral_tipo_banco_de_dados));
						$rows = mysql_num_rows($geral_tipo_banco_de_dados);
						if ($rows > 0) {
							mysql_data_seek($geral_tipo_banco_de_dados, 0);
							$row_geral_tipo_banco_de_dados = mysql_fetch_assoc($geral_tipo_banco_de_dados);
						}
						?>
					</select>
				</td>

				<td style="text-align: right" width="250">
					<span class="label_solicitacao">Tipo da ECF: </span>
					<select name="geral_tipo_ecf" id="geral_tipo_ecf">
						<option value="">Escolha ...</option>
						<?php
						do {
						?>
							<option value="<?php echo $row_geral_tipo_ecf['titulo'] ?>"><?php echo $row_geral_tipo_ecf['titulo'] ?></option>
						<?php
						} while ($row_geral_tipo_ecf = mysql_fetch_assoc($geral_tipo_ecf));
						$rows = mysql_num_rows($geral_tipo_ecf);
						if ($rows > 0) {
							mysql_data_seek($geral_tipo_ecf, 0);
							$row_geral_tipo_ecf = mysql_fetch_assoc($geral_tipo_ecf);
						}
						?>
					</select>
				</td>

			</tr>
		</table>
	</div>

	<div class="div_solicitacao_linhas3">
		<table cellspacing="0" cellpadding="0" width="100%">
			<tr>

				<td style="text-align: left">
					<span class="label_solicitacao">Prioridade*: </span>
					<select name="prioridade" id="prioridade">
						<option value="">Escolha ...</option>
						<?php
						do {
						?>
							<option value="<?php echo $row_solicitacao_tipo_prioridade['titulo'] ?>"><?php echo $row_solicitacao_tipo_prioridade['titulo'] ?></option>
						<?php
						} while ($row_solicitacao_tipo_prioridade = mysql_fetch_assoc($solicitacao_tipo_prioridade));
						$rows = mysql_num_rows($solicitacao_tipo_prioridade);
						if ($rows > 0) {
							mysql_data_seek($solicitacao_tipo_prioridade, 0);
							$row_solicitacao_tipo_prioridade = mysql_fetch_assoc($solicitacao_tipo_prioridade);
						}
						?>
					</select>
				</td>

			</tr>
		</table>

		<table cellspacing="0" cellpadding="0" width="100%" style="margin-top:  10px;" id="prioridade_justificativa_caixa">
			<tr>

				<td style="text-align: left">
					<span class="label_solicitacao">Justificativa da prioridade: </span>
					<br>
					<textarea name="prioridade_justificativa" id="prioridade_justificativa" style="margin-top: 2px; width: 945px; height: 40px;"></textarea>
				</td>

			</tr>
		</table>
	</div>

	<div class="div_solicitacao_linhas3">
		<table cellspacing="0" cellpadding="0" width="100%">
			<tr>

				<td style="text-align: left">
					<span class="label_solicitacao">Medida tomada: </span>
					<br>
					<textarea name="medida_tomada" id="medida_tomada" style="margin-top: 2px; width: 945px; height: 70px;" class="textarea_medida"></textarea>
				</td>

			</tr>
		</table>
	</div>

	<? if($totalRows_suporte > 0){ ?>
	<div class="div_solicitacao_linhas4">
		<table cellspacing="0" cellpadding="0" width="100%">
			<tr>

				<td style="text-align: left">
					<!-- dados do suporte -->
					<?php if ($row_suporte['id'] != "") { ?>
						<div style="padding-top: 5px; padding-bottom: 5px;">
							<span class="label_solicitacao">Suporte vinculado: </span><?php echo $row_suporte['id']; ?> - <?php echo $row_suporte['titulo']; ?>
						</div>
					<? } ?>

					<?php if ($row_suporte['anomalia'] != "") { ?>
						<div style="padding-top: 5px; padding-bottom: 5px;">
							<span class="label_solicitacao">Anomalia: </span><?php echo $row_suporte['anomalia']; ?>
						</div>
					<? } ?>

					<?php if ($row_suporte['id_usuario_responsavel'] != "") { ?>
						<div style="padding-top: 5px; padding-bottom: 5px;">
							<span class="label_solicitacao">Responsável suporte/agendamento: </span><?php echo $row_suporte['usuario_responsavel']; ?>
						</div>
					<? } ?>

					<?php if ($row_suporte['data_inicio'] != "" and $row_suporte['data_fim'] != "") { ?>
						<div style="padding-top: 5px; padding-bottom: 5px;">
							<span class="label_solicitacao">Data/hora inicio: </span><? echo date('d-m-Y  H:i:s', strtotime($row_suporte['data_inicio'])); ?>
							<?php if ($row_suporte['data_fim'] != "" and $row_suporte['data_fim'] != "0000-00-00 00:00:00") { ?>
								| <span class="label_solicitacao">Data/hora fim: </span><? echo date('d-m-Y  H:i:s', strtotime($row_suporte['data_fim'])); ?>
							<? } ?>
						</div>
					<? } ?>

					<?php if ($row_suporte['orientacao'] != "") { ?>
						<div style="padding-top: 5px; padding-bottom: 5px;">
							<span class="label_solicitacao">Orientação: </span><?php echo $row_suporte['orientacao']; ?>
						</div>
					<? } ?>

					<?php if ($row_suporte['observacao'] != "") { ?>
						<div style="padding-top: 5px; padding-bottom: 5px;">
							<span class="label_solicitacao">Observação: </span><?php echo $row_suporte['observacao']; ?>
						</div>
					<? } ?>
					<!-- fim - dados do suporte -->
				</td>

			</tr>
		</table>
	</div>
	<? } ?>

	<? if($totalRows_solicitacao_desmembrada > 0){ ?>
	<div class="div_solicitacao_linhas4">
		<table cellspacing="0" cellpadding="0" width="100%">
			<tr>

				<td style="text-align: left">
					<!-- dados da solicitacao_desmembrada -->
					<?php if ($row_solicitacao_desmembrada['id'] != "") { ?>
						<div style="padding-top: 5px; padding-bottom: 5px;">
							<span class="label_solicitacao">Solicitação desmembrada: </span><?php echo $row_solicitacao_desmembrada['id']; ?> - <?php echo $row_solicitacao_desmembrada['titulo']; ?>
						</div>
					<? } ?>

					<?php if ($row_solicitacao_desmembrada['id_usuario_responsavel'] != "") { ?>
						<div style="padding-top: 5px; padding-bottom: 5px;">
							<span class="label_solicitacao">Responsável: </span><?php echo $row_solicitacao_desmembrada['usuario_responsavel']; ?>
						</div>
					<? } ?>

					<?php if ($row_solicitacao_desmembrada['id_operador'] != "") { ?>
						<div style="padding-top: 5px; padding-bottom: 5px;">
							<span class="label_solicitacao">Operador: </span><?php echo $row_solicitacao_desmembrada['usuario_operador']; ?>
						</div>
					<? } ?>

					<?php if ($row_solicitacao_desmembrada['dt_solicitacao'] != "") { ?>
						<div style="padding-top: 5px; padding-bottom: 5px;">
							<span class="label_solicitacao">Criação: </span><? echo date('d-m-Y  H:i:s', strtotime($row_solicitacao_desmembrada['dt_solicitacao'])); ?>
						</div>
					<? } ?>

					<?php if ($row_solicitacao_desmembrada['observacao'] != "") { ?>
						<div style="padding-top: 5px; padding-bottom: 5px;">
							<span class="label_solicitacao">Observação: </span><?php echo $row_solicitacao_desmembrada['observacao']; ?>
						</div>
					<? } ?>
					<!-- fim - dados da solicitacao_desmembrada -->
				</td>

			</tr>
		</table>
	</div>
	<? } ?>

	<div class="div_solicitacao_linhas2">
		<table cellspacing="0" cellpadding="0" width="100%">
			<tr>

				<td style="text-align: left">
					<span class="label_solicitacao">Digite sua solicitação (descrição)*: </span>
					<div style="padding-top: 3px;">
						<textarea name="descricao" id="descricao" style="margin-top: 2px; width: 945px; height: 180px;"></textarea>
					</div>
				</td>

			</tr>
		</table>
	</div>

	<div class="div_solicitacao_linhas3">
		<table cellspacing="0" cellpadding="0" width="100%">
			<tr>
				<td width="200" style=" padding-top: 3px; padding-bottom: 3px; text-align: left; ">
					<input type="submit" name="button" id="button" value="Gravar dados" class="botao_geral" style="width: 150px;">
					<input type="reset" name="button" id="button" value="Limpar dados" class="botao_geral" style="width: 150px">

					<input type="hidden" name="MM_insert" value="solicitacao" />

				</td>

			</tr>
		</table>
	</div>

</form>
<div class="div_solicitacao_linhas2">*campos com preenchimento obrigatório</div>

<script type="text/javascript">
	$().ready(function() {
		// autocompletar
		$("#titulo").autocomplete("solicitacao_titulo_lista.php", {
			width: 550,
			matchContains: true,
			//mustMatch: true,
			//minChars: 0,
			//multiple: true,
			//highlight: false,
			//multipleSeparator: ",",
			selectFirst: false
		});
		// fim - autocompletar
	});
</script>

</body>

</html>
<?php
mysql_free_result($solicitacao_tipo_solicitacao);
mysql_free_result($geral_tipo_versao);
mysql_free_result($geral_tipo_distribuicao);
mysql_free_result($geral_tipo_banco_de_dados);
mysql_free_result($geral_tipo_ecf);
mysql_free_result($geral_tipo_programa);
mysql_free_result($solicitacao_tipo_prioridade);
mysql_free_result($usuario);
mysql_free_result($suporte);
mysql_free_result($solicitacao_desmembrada);
mysql_free_result($cliente_antigo_listar);
?>