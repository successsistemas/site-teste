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

// verifica $cliente_novo_antigo
$cliente_novo_antigo = "n";
if(@$_GET['novo_antigo']=="a"){
	$cliente_novo_antigo = "a";
}
// fim - verifica $cliente_novo_antigo

// seleciona os EXECUTORES que o usuário atual tem acesso E montagem da query --------------------------------------------
$query_usuarios_geral_tipo_praca_executor = sprintf(" 
SELECT usuarios.praca, geral_tipo_praca_executor.praca, usuarios.IdUsuario, geral_tipo_praca_executor.IdExecutor
FROM usuarios 
INNER JOIN geral_tipo_praca_executor ON  usuarios.praca = geral_tipo_praca_executor.praca 
WHERE usuarios.IdUsuario = ".$row_usuario['IdUsuario']."
");
$usuarios_geral_tipo_praca_executor = mysql_query($query_usuarios_geral_tipo_praca_executor, $conexao) or die(mysql_error());	
$sql_clientes_vendedor17 = ""; 

		// lista os EXECUTORES - em cada EXECUTOR com acesso, é adicionado seu codigo a uma string
		while ($row_usuarios_geral_tipo_praca_executor = mysql_fetch_assoc($usuarios_geral_tipo_praca_executor)){
			$sql_clientes_vendedor17 .= "vendedor17 = '".$row_usuarios_geral_tipo_praca_executor['IdExecutor']."' or ";
		}
		// fim - lista os EXECUTORES - em cada EXECUTOR com acesso, é adicionado seu codigo a uma string

$sql_clientes_vendedor17 = substr($sql_clientes_vendedor17, 0, -4);
// fim - seleciona os EXECUTORES que o usuário atual tem acesso E montagem da query --------------------------------------

// cliente_antigo_listar
//$where_cliente_antigo_listar = "1=1 and da37.status17 <> 'C'";
mysql_select_db($database_conexao, $conexao);
$query_cliente_antigo_listar = sprintf("
SELECT 
da37.codigo17, da37.cliente17, 
da01.nome1 
FROM da37 
INNER JOIN da01 ON da37.cliente17 = da01.codigo1 
WHERE da01.sr_deleted <> 'T' and da37.sr_deleted <> 'T' 
ORDER BY da01.nome1 ASC", 
GetSQLValueString($row_usuario['praca'], "text"));
$cliente_antigo_listar = mysql_query($query_cliente_antigo_listar, $conexao) or die(mysql_error());
$row_cliente_antigo_listar = mysql_fetch_assoc($cliente_antigo_listar);
$totalRows_cliente_antigo_listar = mysql_num_rows($cliente_antigo_listar);
// fim - cliente_antigo_listar

// prospeccao_responsavel - para selectbox
mysql_select_db($database_conexao, $conexao);
$query_prospeccao_responsavel = "SELECT IdUsuario, nome FROM usuarios WHERE status = '1'";
$query_prospeccao_responsavel .= " ORDER BY nome ASC";
$prospeccao_responsavel = mysql_query($query_prospeccao_responsavel, $conexao) or die(mysql_error());
$row_prospeccao_responsavel = mysql_fetch_assoc($prospeccao_responsavel);
$totalRows_prospeccao_responsavel = mysql_num_rows($prospeccao_responsavel);
// fim - prospeccao_responsavel - para selectbox

// tipo ramo_atividade
mysql_select_db($database_conexao, $conexao);
$query_geral_tipo_ramo_atividade = "SELECT * FROM geral_tipo_ramo_atividade ORDER BY titulo ASC";
$geral_tipo_ramo_atividade = mysql_query($query_geral_tipo_ramo_atividade, $conexao) or die(mysql_error());
$row_geral_tipo_ramo_atividade = mysql_fetch_assoc($geral_tipo_ramo_atividade);
$totalRows_geral_tipo_ramo_atividade = mysql_num_rows($geral_tipo_ramo_atividade);
// fim - tipo ramo_atividade

// insert
if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "prospeccao")) {
	
	// usuario_responsavel		
	$colname_usuario_responsavel = "-1";
	if (isset($_POST['prospeccao_responsavel'])) {
		$colname_usuario_responsavel = $_POST['prospeccao_responsavel'];
	}
	mysql_select_db($database_conexao, $conexao);
	$query_usuario_responsavel = sprintf("SELECT IdUsuario, nome, praca FROM usuarios WHERE IdUsuario = %s", GetSQLValueString($colname_usuario_responsavel, "int"));
	$usuario_responsavel = mysql_query($query_usuario_responsavel, $conexao) or die(mysql_error());
	$row_usuario_responsavel = mysql_fetch_assoc($usuario_responsavel);
	$totalRows_usuario_responsavel = mysql_num_rows($usuario_responsavel);
	// fim - usuario_responsavel
	
	// default campos	
	$praca = $row_usuario_responsavel['praca'];

	$status_flag = "a";
	$status = "aguardando agendamento";
	$situacao = "em negociação";

	$id_usuario_responsavel = $row_usuario_responsavel['IdUsuario'];
	
	$encaminhamento_id = '';
	$encaminhamento_data = '';
	$encaminhamento_data_inicio = '';
	
	$observacao = '';
	
	$nome_razao_social = @$_POST['nome_razao_social'];
	$pessoa = @$_POST['pessoa'];
	$fantasia = @$_POST['fantasia'];
	
	$cpf_cnpj = @$_POST['cpf_cnpj'];
	$rg_inscricao = @$_POST['rg_inscricao'];
	$rg_orgao_expeditor = @$_POST['rg_orgao_expeditor'];
	
	$cep = @$_POST['cep'];
	$endereco = @$_POST['endereco'];
	$endereco_numero = @$_POST['endereco_numero'];
	$endereco_complemento = @$_POST['endereco_complemento'];
	$endereco_referencia = @$_POST['endereco_referencia'];
	$bairro = @$_POST['bairro'];
	$cidade = @$_POST['cidade'];
	$uf = @$_POST['uf'];
	
	$telefone = @$_POST['telefone'];
	$celular = @$_POST['celular'];
	$email = @$_POST['email'];
	
	$tipo_cliente = 'n';
	$codigo_empresa = '';
	$contrato = '';
	$contrato_data = '';	
	// fim - default campos
	
	// cliente_antigo
	if($cliente_novo_antigo=="a"){
		
		// cliente_antigo_selecionado
		mysql_select_db($database_conexao, $conexao);
		$query_cliente_antigo_selecionado = sprintf("
		SELECT * 
		FROM da01 
		WHERE 
		codigo1 = %s and da01.sr_deleted <> 'T'", 
		GetSQLValueString($_POST['cliente_antigo'], "text"));
		$cliente_antigo_selecionado = mysql_query($query_cliente_antigo_selecionado, $conexao) or die(mysql_error());
		$row_cliente_antigo_selecionado = mysql_fetch_assoc($cliente_antigo_selecionado);
		$totalRows_cliente_antigo_selecionado = mysql_num_rows($cliente_antigo_selecionado);
		// fim - cliente_antigo_selecionado
		
		// contrato_antigo_selecionado
		mysql_select_db($database_conexao, $conexao);
		$query_contrato_antigo_selecionado = sprintf("
		SELECT 
		codigo17, datcont17, 
		geral_tipo_praca_executor.praca as vendedor17_praca 
		FROM da37 
		INNER JOIN geral_tipo_praca_executor ON da37.vendedor17 = geral_tipo_praca_executor.IdExecutor 
		WHERE 
		cliente17 = %s and da37.sr_deleted <> 'T'", 
		GetSQLValueString($row_cliente_antigo_selecionado['codigo1'], "text"));
		$contrato_antigo_selecionado = mysql_query($query_contrato_antigo_selecionado, $conexao) or die(mysql_error());
		$row_contrato_antigo_selecionado = mysql_fetch_assoc($contrato_antigo_selecionado);
		$totalRows_contrato_antigo_selecionado = mysql_num_rows($contrato_antigo_selecionado);
		// fim - contrato_antigo_selecionado
		
		$praca = $row_contrato_antigo_selecionado['vendedor17_praca'];
		
		$nome_razao_social = $row_cliente_antigo_selecionado['nome1'];
		
		if($row_cliente_antigo_selecionado['pessoa1']=="J"){$pessoa = "j";}
		if($row_cliente_antigo_selecionado['pessoa1']=="F"){$pessoa = "f";}
		
		$fantasia = $row_cliente_antigo_selecionado['fantasia1'];
		
		$cpf_cnpj = $row_cliente_antigo_selecionado['cgc1'];
		$rg_inscricao = $row_cliente_antigo_selecionado['insc1'];
		$rg_orgao_expeditor = $row_cliente_antigo_selecionado['orgao1'];
		
		$cep = $row_cliente_antigo_selecionado['cep1'];
		$endereco = $row_cliente_antigo_selecionado['endereco1'];
		$endereco_numero = '';
		$endereco_complemento = '';
		$endereco_referencia = '';
		$bairro = $row_cliente_antigo_selecionado['bairro1'];
		$cidade = $row_cliente_antigo_selecionado['cidade1'];
		$uf = $row_cliente_antigo_selecionado['uf1'];
		
		$telefone = $row_cliente_antigo_selecionado['telefone1'];
		$celular = $row_cliente_antigo_selecionado['celular1'];
		$email = '';
		
		$tipo_cliente = 'a';
		$codigo_empresa = $row_cliente_antigo_selecionado['codigo1'];
		$contrato = $row_contrato_antigo_selecionado['codigo17'];
		$contrato_data = $row_contrato_antigo_selecionado['datcont17'];
		
		mysql_free_result($cliente_antigo_selecionado);
		mysql_free_result($contrato_antigo_selecionado);

	}
	// fim - cliente_antigo
	
	// Atender
	if($row_usuario['IdUsuario'] == $row_usuario_responsavel['IdUsuario']){

		$status = "aguardando agendamento";
		
	}
	// fim - Atender
	
	// Encaminhar
	if($row_usuario['IdUsuario'] != $row_usuario_responsavel['IdUsuario']){
		
		$status = "encaminhada para usuario responsavel";
		$situacao = "analisada";
		
		$encaminhamento_id = $row_usuario['IdUsuario'];
		$encaminhamento_data = '';
		$encaminhamento_data_inicio = '';

		$observacao = ' - Encaminhado';
		
	}
	// fim - Encaminhar
	
	// campos com checkbox
	$exige_nfe = 0;
	if (isset($_POST['exige_nfe'])) {
		$exige_nfe = 1;
	}
	
	$exige_cupom_fiscal = 0;
	if (isset($_POST['exige_cupom_fiscal'])) {
		$exige_cupom_fiscal = 1;
	}
	
	$exige_nfce = 0;
	if (isset($_POST['exige_nfce'])) {
		$exige_nfce = 1;
	}
	
	$exige_mdfe = 0;
	if (isset($_POST['exige_mdfe'])) {
		$exige_mdfe = 1;
	}
	
	$exige_ctee = 0;
	if (isset($_POST['exige_ctee'])) {
		$exige_ctee = 1;
	}
	
	$exige_efd = 0;
	if (isset($_POST['exige_efd'])) {
		$exige_efd = 1;
	}
	// fim - campos com checkbox

	$sistema_recursos = NULL;
	if(count(@$_POST['sistema_recursos']) > 0){
		$sistema_recursos = implode(',', $_POST['sistema_recursos']);
	}
	
	$podemos_ofertar = NULL;
	if(count(@$_POST['podemos_ofertar']) > 0){
		$podemos_ofertar = implode(',', $_POST['podemos_ofertar']);
	}
		
	$necessidades = NULL;
	if(count(@$_POST['necessidades']) > 0){
		$necessidades = implode(',', $_POST['necessidades']);
	}
	
	// indicador
	$indicador_contador = NULL;
	$indicador_cliente = NULL;
	$indicador_usuario = NULL;
	$indicador_funcionario = NULL;
	$indicador_terceiro = NULL;
	
	if($_POST['indicador']=="co"){
		
		$indicador_contador = $_POST['indicador_contador'];
			
	} else if ($_POST['indicador']=="cl"){
		
		$indicador_cliente = $_POST['indicador_cliente'];				
		
	} else if ($_POST['indicador']=="cs"){
		
		$indicador_usuario = $_POST['indicador_usuario'];

	} else if ($_POST['indicador']=="fu"){
		
		$indicador_funcionario = $_POST['indicador_funcionario'];
		
	} else if ($_POST['indicador']=="te"){
		
		$indicador_terceiro = $_POST['indicador_terceiro'];		
		
	}
	// fim - indicador
	
	// insert - prospeccao
	$insertSQL = sprintf("INSERT INTO prospeccao (data_prospeccao, praca, id_usuario_responsavel, situacao, status, status_flag, tela, nome_razao_social, pessoa, fantasia, cpf_cnpj, rg_inscricao, rg_orgao_expeditor, cep, endereco, endereco_numero, endereco_complemento, endereco_referencia, bairro, cidade, uf, telefone, celular, email, observacao, ativo_passivo, indicador, indicador_contador, indicador_cliente, indicador_usuario, indicador_funcionario, indicador_terceiro, indicado_por, empresa_responsavel, responsavel_por_ti, empresa_contato, enquadramento_fiscal, enquadramento_fiscal_outro, ramo_de_atividade, sistema_possui, id_concorrente, sistema_nivel_utilizacao, sistema_nivel_satisfacao, sistema_nivel_motivo, sistema_recursos, sistema_recursos_success_possui, sistema_recursos_success_nao_possui, empresa_controle_manual, podemos_ofertar, nivel_interesse, necessidades, id_contador, exige_nfe, exige_cupom_fiscal,  exige_nfce, exige_mdfe, exige_ctee, exige_efd, exige_outro, encaminhamento_id, encaminhamento_data, encaminhamento_data_inicio, tipo_cliente, codigo_empresa, contrato, contrato_data) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)",

	GetSQLValueString(date('Y-m-d H:i:s'), "date"),
	GetSQLValueString(@$praca, "text"),
	GetSQLValueString(@$id_usuario_responsavel, "int"),
	GetSQLValueString(@$situacao, "text"),
	GetSQLValueString(@$status, "text"),
	GetSQLValueString(@$status_flag, "text"),
	GetSQLValueString("e", "text"),
	
	GetSQLValueString(@$nome_razao_social, "text"),
	GetSQLValueString(@$pessoa, "text"),
	GetSQLValueString(@$fantasia, "text"),
	
	GetSQLValueString(@$cpf_cnpj, "text"),
	GetSQLValueString(@$rg_inscricao, "text"),
	GetSQLValueString(@$rg_orgao_expeditor, "text"),
	
	GetSQLValueString(@$cep, "text"),
	GetSQLValueString(@$endereco, "text"),
	GetSQLValueString(@$endereco_numero, "text"),
	GetSQLValueString(@$endereco_complemento, "text"),
	GetSQLValueString(@$endereco_referencia, "text"),
	GetSQLValueString(@$bairro, "text"),
	GetSQLValueString(@$cidade, "text"),
	GetSQLValueString(@$uf, "text"),
	
	GetSQLValueString(@$telefone, "text"),
	GetSQLValueString(@$celular, "text"),
	GetSQLValueString(@$email, "text"),

	GetSQLValueString(@$_POST['observacao'], "text"),
	GetSQLValueString(@$_POST['ativo_passivo'], "text"),
	
	GetSQLValueString(@$_POST['indicador'], "text"),
	GetSQLValueString(@$indicador_contador, "text"),
	GetSQLValueString(@$indicador_cliente, "text"),
	GetSQLValueString(@$indicador_usuario, "text"),
	GetSQLValueString(@$indicador_funcionario, "text"),
	GetSQLValueString(@$indicador_terceiro, "text"),
	
	GetSQLValueString(@$_POST['indicado_por'], "text"),
	GetSQLValueString(@$_POST['empresa_responsavel'], "text"),
	GetSQLValueString(@$_POST['responsavel_por_ti'], "text"),
	GetSQLValueString(@$_POST['empresa_contato'], "text"),
	GetSQLValueString(@$_POST['enquadramento_fiscal'], "text"),
	GetSQLValueString(@$_POST['enquadramento_fiscal_outro'], "text"),
	GetSQLValueString(@$_POST['ramo_de_atividade'], "text"),
	GetSQLValueString(@$_POST['sistema_possui'], "text"),
	GetSQLValueString(@$_POST['id_concorrente'], "int"),
	GetSQLValueString(@$_POST['sistema_nivel_utilizacao'], "text"),
	GetSQLValueString(@$_POST['sistema_nivel_satisfacao'], "text"),
	GetSQLValueString(@$_POST['sistema_nivel_motivo'], "text"),
	GetSQLValueString(@$sistema_recursos, "text"),
	GetSQLValueString(@$_POST['sistema_recursos_success_possui'], "text"),
	GetSQLValueString(@$_POST['sistema_recursos_success_nao_possui'], "text"),
	GetSQLValueString(@$_POST['empresa_controle_manual'], "text"),
	GetSQLValueString($podemos_ofertar, "text"),	
	GetSQLValueString(@$_POST['nivel_interesse'], "text"),
	GetSQLValueString(@$necessidades, "text"),
	GetSQLValueString(@$_POST['id_contador'], "text"),
	GetSQLValueString(@$exige_nfe, "int"),
	GetSQLValueString(@$exige_cupom_fiscal, "int"),
	GetSQLValueString(@$exige_nfce, "int"),
	GetSQLValueString(@$exige_mdfe, "int"),
	GetSQLValueString(@$exige_ctee, "int"),
	GetSQLValueString(@$exige_efd, "int"),
	GetSQLValueString(@$_POST['exige_outro'], "text"),
	GetSQLValueString(@$encaminhamento_id, "int"),
	GetSQLValueString(@$encaminhamento_data, "date"),
	GetSQLValueString(@$encaminhamento_data_inicio, "date"),
	
	GetSQLValueString(@$tipo_cliente, "text"),
	GetSQLValueString(@$codigo_empresa, "text"),
	GetSQLValueString(@$contrato, "text"),
	GetSQLValueString(@$contrato_data, "date"));

	mysql_select_db($database_conexao, $conexao);
	$Result_insert = mysql_query($insertSQL, $conexao) or die(mysql_error());
	$idNovo = mysql_insert_id(); // pega o numero do ultimo prospeccao
	// fim - insert - prospeccao
		
	// converter entrada de data em portugues para ingles
	if ( isset($_POST['data_agendamento_inicio']) and $_POST['data_agendamento_inicio'] != "" ) {
		$data_agendamento_inicio_data_inicio = substr($_POST['data_agendamento_inicio'],0,10);
		$data_agendamento_inicio_hora = substr($_POST['data_agendamento_inicio'],10,9);
		$_POST['data_agendamento_inicio'] = implode("-",array_reverse(explode("-",$data_agendamento_inicio_data_inicio))).$data_agendamento_inicio_hora;
	} else {
		$_POST['data_agendamento_inicio'] = "0000-00-00 00:00:00";
	}
	
	
	if ( isset($_POST['data_agendamento']) and $_POST['data_agendamento'] != "" ) {
		$data_agendamento_data = substr($_POST['data_agendamento'],0,10);
		$data_agendamento_hora = substr($_POST['data_agendamento'],10,9);
		$_POST['data_agendamento'] = implode("-",array_reverse(explode("-",$data_agendamento_data))).$data_agendamento_hora;
	} else {
		$_POST['data_agendamento'] = "0000-00-00 00:00:00";
	}
	// fim - converter entrada de data em portugues para ingles - fim

	// se existe agendamento
	if($_POST['data_agendamento_inicio']!="0000-00-00 00:00:00" and $_POST['data_agendamento']!="0000-00-00 00:00:00"){
		
		// insert agenda
		$insertSQL_prospeccao_agenda = sprintf("
		INSERT INTO agenda (id_prospeccao, id_usuario_responsavel, data_inicio, data, data_criacao, status, prospeccao_agenda_tipo, descricao) 
		VALUES (%s, %s, %s, %s, %s, %s, %s, %s)",
		GetSQLValueString($idNovo, "int"),
		GetSQLValueString($row_usuario['IdUsuario'], "int"),
		GetSQLValueString($_POST['data_agendamento_inicio'], "date"),
		GetSQLValueString($_POST['data_agendamento'], "date"),
		GetSQLValueString(date("Y-m-d H:i:s"), "date"),
		GetSQLValueString("a", "text"), 
		GetSQLValueString($_POST['prospeccao_agenda_tipo'], "int"), 
		GetSQLValueString($_POST['descricao_agendamento'], "text"));
		
		mysql_select_db($database_conexao, $conexao);
		$Result_prospeccao_agenda = mysql_query($insertSQL_prospeccao_agenda, $conexao) or die(mysql_error());
		// fim - insert agenda
		
		$id_agenda_novo = mysql_insert_id(); // pega o numero do ultimo prospeccao
		
		if(isset($_POST['prospeccao_agenda_tipo']) and $_POST['prospeccao_agenda_tipo'] == 1) { // Visita
					
			// insert - prospeccao_formulario
			$insertSQL_formulario = sprintf("
			INSERT INTO prospeccao_formulario (id_prospeccao, id_agenda, data, empresa, codigo_empresa, contrato, praca, id_usuario_responsavel, prospeccao_agenda_tipo, status_flag, situacao) 
			VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)",
			GetSQLValueString($idNovo, "int"),		
			GetSQLValueString($id_agenda_novo, "int"),																																																																																																												
			GetSQLValueString(date("Y-m-d H:i:s"), "date"),
			GetSQLValueString($nome_razao_social, "text"),
			GetSQLValueString($codigo_empresa, "text"),
			GetSQLValueString($contrato, "text"),
			GetSQLValueString($praca, "text"),
			GetSQLValueString($row_usuario['IdUsuario'], "int"),
			GetSQLValueString(1, "int"),
			GetSQLValueString('a', "text"),
			GetSQLValueString('autorizado', "text"));
			
			mysql_select_db($database_conexao, $conexao);
			$Result_formulario = mysql_query($insertSQL_formulario, $conexao) or die(mysql_error());
			// fim - insert - prospeccao_formulario
		
		}
		
		// update 'prospeccao'
		$updateSQL_prospeccao = sprintf("UPDATE prospeccao 
										SET quantidade_agendado='1', status = %s 
										WHERE id=%s", 
										GetSQLValueString(@$_POST['agendamento_status'], "text"),
										GetSQLValueString($idNovo, "int"));
		mysql_select_db($database_conexao, $conexao);
		$Result_prospeccao = mysql_query($updateSQL_prospeccao, $conexao) or die(mysql_error());
		// fim - update 'prospeccao'
		
	}
	// fim - se existe agendamento
	
	// insert - prospeccao_descricoes
	$insertSQL2  = sprintf("INSERT INTO prospeccao_descricoes (id_prospeccao, data, tipo_postagem, id_usuario_responsavel) VALUES (%s, %s, %s, %s)",
					   GetSQLValueString($idNovo, "int"),
					   GetSQLValueString(date("Y-m-d H:i:s"), "date"),
					   GetSQLValueString("Nova prospecção".$observacao, "text"),
					   GetSQLValueString($row_usuario['IdUsuario'], "int"));
	$Result2 = mysql_query($insertSQL2, $conexao) or die(mysql_error());
	// fim - insert - prospeccao_descricoes

	// redireciona
	$insertGoTo = "prospeccao_editar.php?id_prospeccao=".$idNovo."&padrao=sim";
	echo sprintf("<meta http-equiv=\"refresh\" content=\"0; url=%s\">", $insertGoTo);
	// fim - redireciona
	exit;
	
}
// fim - insert
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"> 
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">

<link rel="stylesheet" href="css/suporte.css" type="text/css" media="screen" />


<script type="text/javascript" src="js/jquery.js"></script>

<script type="text/javascript" src="js/jquery.maskedinput-1.2.2.min.js"></script> 
<script type="text/javascript" src="js/jquery.alphanumeric.pack.js"></script> 
<script type="text/javascript" src="js/jquery.rsv.js"></script> 

<link rel="stylesheet" href="css/thickbox.css" type="text/css" media="screen" />
<script type="text/javascript" src="js/thickbox.js"></script>

<link type="text/css" href="css/jquery-ui.css" rel="stylesheet" />	
<link type="text/css" href="css/jquery-ui-timepicker-addon.css" rel="stylesheet" />	

<script type="text/javascript" src="js/jquery-ui.js"></script>
<script type="text/javascript" src="js/jquery-ui-timepicker-addon.js"></script>

<script type="text/javascript" src="js/date.format.js"></script>

<script type="text/javascript" src="js/funcoes_data.js"></script>

<script type="text/javascript" src="js/funcao_js_valida_cpf_cnpj.js"></script>

<style>
/* calendário */
.ui-timepicker-div .ui-widget-header { margin-bottom: 8px; }
.ui-timepicker-div dl { text-align: left; }
.ui-timepicker-div dl dt { height: 25px; margin-bottom: -25px; }
.ui-timepicker-div dl dd { margin: 0 10px 10px 65px; }
.ui-timepicker-div td { font-size: 90%; }
.ui-tpicker-grid-label { background: none; border: none; margin: 0; padding: 0; }
/* fim - calendário */

.cpf_cnpj_erro{
	margin-top: 5px;
	color: red;
	text-align: center;
}
</style>
<script type="text/javascript">
function myOnComplete(){return true;}

// função customizada (data_inicial_final_menor)
function data_inicial_final_menor()
{
	var data_inicio = document.getElementById("data_agendamento_inicio").value;
	var data_fim = document.getElementById("data_agendamento").value;	
	var is_valid = false;

	if(data_inicio != "" && data_fim != ""){
		var quebraDI=data_inicio.split("-");
		var diaDI = quebraDI[0];
		var mesDI = quebraDI[1];
		var anoDI = quebraDI[2].substr(0,4);
		var time_inicial = quebraDI[2].substr(5,8);
		var quebraTimeDI=time_inicial.split(":");
		var horaDI = quebraTimeDI[0];
		var minutoDI = quebraTimeDI[1];
		var segundoDI = quebraTimeDI[2];
		var date1 = anoDI+"-"+mesDI+"-"+diaDI+" "+horaDI+":"+minutoDI+":"+segundoDI;
		
		var quebraDF=data_fim.split("-");
		var diaDF = quebraDF[0];
		var mesDF = quebraDF[1];
		var anoDF = quebraDF[2].substr(0,4);
		var time_final = quebraDF[2].substr(5,8);
		var quebraTimeDF=time_final.split(":");
		var horaDF = quebraTimeDF[0];
		var minutoDF = quebraTimeDF[1];
		var segundoDF = quebraTimeDF[2];
		var date2 = anoDF+"-"+mesDF+"-"+diaDF+" "+horaDF+":"+minutoDF+":"+segundoDF;	
		
		if (date1 < date2){
			is_valid = true;
		}
		
		if (!is_valid)
		{
			var field = document.getElementById("data_agendamento_inicio");
			return [[field, "Data inicial maior que data final."]];
		}		
	}

	return true;
}
// fim - função customizada (data_inicial_final_menor)

$(document).ready(function() {
						   		
	// pega o primeiro campo habilitado
	setTimeout(function() {$('#form :input:visible:enabled:first').focus();}, 100);
	// fim - pega o primeiro campo habilitado
	
	// cliente_novo_antigo (habilita/desabilita)
	<? if($cliente_novo_antigo=="n"){ ?>
	
		$('select[id="cliente_antigo"]').attr('disabled', true);
		
	<? } ?>

	<? if($cliente_novo_antigo=="a"){ ?>
	
		$('input[id="nome_razao_social"]').attr('disabled', true);
		$('input[id="pessoa"]').attr('disabled', true);
		$('input[id="fantasia"]').attr('disabled', true);
		$('input[id="cpf_cnpj"]').attr('disabled', true);
		$('input[id="rg_inscricao"]').attr('disabled', true);
		$('input[id="rg_orgao_expeditor"]').attr('disabled', true);
		
		$('input[id="cep"]').attr('disabled', true);
		$('input[id="endereco"]').attr('disabled', true);
		$('input[id="endereco_numero"]').attr('disabled', true);
		$('input[id="endereco_complemento"]').attr('disabled', true);
		$('input[id="endereco_referencia"]').attr('disabled', true);
		$('input[id="bairro"]').attr('disabled', true);
		$('input[id="cidade"]').attr('disabled', true);
		$('select[id="uf"]').attr('disabled', true);
		$('select[id="cidade"]').attr('disabled', true);
		
		$('input[id="telefone"]').attr('disabled', true);
		$('input[id="celular"]').attr('disabled', true);
		$('input[id="email"]').attr('disabled', true);
		
		$('input[id="sistema_possui"]').attr('disabled', true);
	
	<? } ?>
	// cliente_novo_antigo (habilita/desabilita)

	// calendário -------------------------------------------------------------
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
		'Domingo','Segunda','Terça','Quarta','Quinta','Sexta','Sábado','Domingo'
		],
		dayNamesMin: [
		'D','S','T','Q','Q','S','S','D'
		],
		dayNamesShort: [
		'Dom','Seg','Ter','Qua','Qui','Sex','Sáb','Dom'
		],
		monthNames: [
		'Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro',
		'Outubro','Novembro','Dezembro'
		],
		monthNamesShort: [
		'Jan','Fev','Mar','Abr','Mai','Jun','Jul','Ago','Set',
		'Out','Nov','Dez'
		],
		nextText: 'Próximo',
		prevText: 'Anterior',
		closeText:"Fechar",
		currentText: "Agora",
		timeOnlyTitle: 'Escolha a hora',
		timeText: 'Horário',
		hourText: 'Hora',
		minuteText: 'Minuto',
		secondText: 'Segundo',
		beforeShow: function (selectedDateTime){
			
			$('#data_agendamento').val('');
			$('#agendamento_tempo').val('');
			
		},
		onChangeMonthYear: function(selectedDateTime) {
			$('#data_agendamento').val('');
			$('#agendamento_tempo').val('');
		},
		onClose: function(selectedDateTime){

			if(selectedDateTime=="  -  -       :  " || selectedDateTime==""){
				$('#data_agendamento').val('');
				$('#agendamento_tempo').val('');
			}
			
		},
		onSelect: function (selectedDateTime){
			$('#data_agendamento').val('');
			$('#agendamento_tempo').val('');
		}
		
	});
	
	// baixa_contrato_data
	var baixa_contrato_data = $('#baixa_contrato_data');
	baixa_contrato_data.datepicker({ 					
		showOn: "button",
		buttonImage: "css/images/calendar.gif",
		buttonImageOnly: true,									
		minDate: -20,
		maxDate: 0,
		inline: true,
		dateFormat: 'dd-mm-yy',
		dayNames: [
		'Domingo','Segunda','Terça','Quarta','Quinta','Sexta','Sábado','Domingo'
		],
		dayNamesMin: [
		'D','S','T','Q','Q','S','S','D'
		],
		dayNamesShort: [
		'Dom','Seg','Ter','Qua','Qui','Sex','Sáb','Dom'
		],
		monthNames: [
		'Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro',
		'Outubro','Novembro','Dezembro'
		],
		monthNamesShort: [
		'Jan','Fev','Mar','Abr','Mai','Jun','Jul','Ago','Set',
		'Out','Nov','Dez'
		],
		nextText: 'Próximo',
		prevText: 'Anterior',
		closeText:"Fechar",
		currentText: "Agora",
		timeOnlyTitle: 'Escolha a hora',
		timeText: 'Horário',
		hourText: 'Hora',
		minuteText: 'Minuto',
		secondText: 'Segundo'	
	});
	// fim - baixa_contrato_data
	// fim - calendario -------------------------------------------------------
	
	// agendamento_tempo
	$("select[name=agendamento_tempo]").change(function(){
														
		var agendamento_tempo = $(this).val();

		var data_inicio = $("#data_agendamento_inicio").val();
		var quebraDI=data_inicio.split("-");
		var diaDI = quebraDI[0];
		var mesDI = quebraDI[1];
		var anoDI = quebraDI[2].substr(0,4);
		var time_inicial = quebraDI[2].substr(5,8);
		var quebraTimeDI=time_inicial.split(":");
		var horaDI = quebraTimeDI[0];
		var minutoDI = quebraTimeDI[1];
		
		// current date
		var date = new Date(anoDI, mesDI-1, diaDI, horaDI, minutoDI, 0);
		
		// future date
		var new_date = new Date (date);
		
		var minutes = parseInt($("#agendamento_tempo").val());
		
		// Add the minutes to current date to arrive at the new date
		new_date.setMinutes ( date.getMinutes() + minutes );

		date1 = new_date.format('dd-mm-yyyy HH:MM'); // date.format.js
		
		$("#data_agendamento").val(date1);
		
	});
	// fim - agendamento_tempo
	
	// cpf_cnpj
	$("input[name=cpf_cnpj]").blur(function() {

		$('#cpf_cnpj_erro').hide();
		
		var cpf_cnpj_campo = $(this);
		var cpf_cnpj = $(this).val();
			
		// remove pontuações
		cpf_cnpj = cpf_cnpj.replace('.','');
		cpf_cnpj = cpf_cnpj.replace('.','');
		cpf_cnpj = cpf_cnpj.replace('-','');
		cpf_cnpj = cpf_cnpj.replace('/','');
		// fim - remove pontuações

		var consulta_cpf_cnpj = 0;
		
		if (cpf_cnpj.length == 11) { // utilizar validação do CPF
		
			var retorno = validaCPF(cpf_cnpj);
			if(retorno==false){
				$('#cpf_cnpj_erro').html("CPF Inválido!");
				$('#cpf_cnpj_erro').show();
				setTimeout(function(){ cpf_cnpj_campo.focus()}, 50);
				return false;
			} else {
				var consulta_cpf_cnpj = 1;
			}
			
		} else if (cpf_cnpj.length == 14) { // utilizar a validação do CNPJ
		
			var retorno = validaCNPJ(cpf_cnpj);
			if(retorno==false){
				$('#cpf_cnpj_erro').html("CNPJ Inválido!");
				$('#cpf_cnpj_erro').show();
				setTimeout(function(){ cpf_cnpj_campo.focus()}, 50);
				return false;
			} else {
				var consulta_cpf_cnpj = 1;
			}
			
		} else if (cpf_cnpj.length > 0) { // retorna tamanho inválido
			$('#cpf_cnpj_erro').html('Tamanho inválido');
			$('#cpf_cnpj_erro').show();
			setTimeout(function() {cpf_cnpj_campo.focus();}, 50);
			return false;
		}

		if(consulta_cpf_cnpj == 1){

			$.post("consulta_cpf_cnpj.php",{ 
				cpf_cnpj: cpf_cnpj
			},		
			function(retorno){

				if(retorno.contador > 0){
					$('#cpf_cnpj_erro').html('CPF/CNPJ já cadastrado');
					$('#cpf_cnpj_erro').show();
					setTimeout(function() {cpf_cnpj_campo.focus();}, 50);
					return false;
				}

			}, 'json');

		}
		
	});
	// fim - cpf_cnpj
	
	// campos obrigatórios - coloca o asterisco*	
	$("label > #req").hide();
	
	<? if($cliente_novo_antigo=="a"){ ?>
		$("label[id=label_prospeccao_responsavel] > #req").show();
		$("label[id=label_cliente_antigo] > #req").show();
		$("label[id=label_ativo_passivo] > #req").show();
		
		$("label[id=label_empresa_responsavel] > #req").show();
		$("label[id=label_responsavel_por_ti] > #req").show();
		$("label[id=label_empresa_contato] > #req").show();
		$("label[id=label_ramo_de_atividade] > #req").show();
		$("label[id=label_id_contador] > #req").show();
		$("label[id=label_observacao] > #req").show();
	<? } ?>

	<? if($cliente_novo_antigo=="n"){ ?>
		$("label[id=label_prospeccao_responsavel] > #req").show();
		$("label[id=label_ativo_passivo] > #req").show();
		
		$("label[id=label_nome_razao_social] > #req").show();
		
		$("label[id=label_cep] > #req").show();
		$("label[id=label_endereco] > #req").show();
		$("label[id=label_endereco_numero] > #req").show();
		$("label[id=label_bairro] > #req").show();
		$("label[id=label_cidade] > #req").show();
		$("label[id=label_uf] > #req").show();
		$("label[id=label_cidade] > #req").show();
		
		$("label[id=label_telefone] > #req").show();
		
		$("label[id=label_empresa_responsavel] > #req").show();
		$("label[id=label_responsavel_por_ti] > #req").show();
		$("label[id=label_empresa_contato] > #req").show();
		$("label[id=label_ramo_de_atividade] > #req").show();
		$("label[id=label_id_contador] > #req").show();
		$("label[id=label_observacao] > #req").show();
	<? } ?>
	// fim - campos obrigatórios - coloca o asterisco*
	
    // Click no botão Botão: Salvar ---------------------------------------------------------------
	$('#button').click(function() {

        // consulta automática - agenda
        if($("input[name=data_agendamento_inicio]").val() != '' && $("input[name=data_agendamento]").val() != ''  && $("select[name=prospeccao_responsavel]").val() != '') {
            
            // post
            $.post("agenda_consulta.php", {
                   data_inicio: $("input[name=data_agendamento_inicio]").val(), 
                   data_fim: $("input[name=data_agendamento]").val(),
                   id_usuario_responsavel: $("select[name=prospeccao_responsavel]").val(),
                   id_agenda: 0
                   }, function(data) {

                        if(data == 0){
                            $('#form').submit();
                        }
                        if(data == 1){
                            alert("Data informada inválida. Existem agendamentos para o usuário atual durante período informado.");
                            $('#data_agendamento').val('');
                            $('#agendamento_tempo').val('');
                            return false;
                        }

                   }
            );
            // fim - post
        
        } else {
            
            $('#form').submit();
            
        }
        // fim - consulta automática - agenda

	});
	// fim - Click no botão Botão: Salvar ---------------------------------------------------------
	
	// validação
	var rules = [];	
	
	<? if($cliente_novo_antigo=="a"){ ?>
		rules.push("required,prospeccao_responsavel,Informe o responsável pela prospecção.");
		rules.push("required,cliente_antigo,Selecione o cliente.");
		rules.push("required,ativo_passivo,Informe o tipo de prospecção.");
		
		rules.push("required,empresa_responsavel,Informe o responsável pela empresa.");
		rules.push("required,responsavel_por_ti,Informe o responsável por TI.");
		rules.push("required,empresa_contato,Informe o contato na empresa.");
		rules.push("required,ramo_de_atividade,Informe o ramo de atividade.");
		
		rules.push("function, data_inicial_final_menor");
		
		rules.push("length>=10,observacao,Informe a observação com no mínimo 10 caracteres.");
	<? } ?>

	<? if($cliente_novo_antigo=="n"){ ?>
		rules.push("required,prospeccao_responsavel,Informe o responsável pela prospecção.");
		rules.push("required,ativo_passivo,Informe o tipo de prospecção.");
		
		rules.push("required,pessoa,Informe o tipo de pessoa (física/jurídica).");
		rules.push("length>1,nome_razao_social,Informe o nome/razão social.");
		
		rules.push("length>=8,cep,Informe o cep.");
		rules.push("length>=5,endereco,Informe o endereço (mínimo 5 caracteres).");
		rules.push("length>=1,endereco_numero,Informe o número do endereço.");
		rules.push("length>1,bairro,Informe o bairro.");
		rules.push("length>1,uf,Informe o estado.");
		rules.push("length>1,cidade,Informe a cidade.");
		
		rules.push("length>=10,telefone,Informe o telefone com DDD (mínimo 10 caracteres).");
		/* rules.push("required,email,Informe o e-mail."); */
		
		rules.push("required,empresa_responsavel,Informe o responsável pela empresa.");
		rules.push("required,responsavel_por_ti,Informe o responsável por TI.");
		rules.push("required,empresa_contato,Informe o contato na empresa.");
		rules.push("required,ramo_de_atividade,Informe o ramo de atividade.");
		
		rules.push("function, data_inicial_final_menor");
		
		rules.push("length>=10,observacao,Informe a observação com no mínimo 10 caracteres.");
	<? } ?>

	$("#form").RSV({
			onCompleteHandler: myOnComplete,
			rules: rules
	});			
	// fim - validação
	
	// mascara
	$('#cep').mask('99999-999',{placeholder:" "});
	$('#data_agendamento_inicio').mask('99-99-9999 99:99',{placeholder:" "});
	
	$("input[name=cpf_cnpj]").numeric();
	$("input[name=rg_inscricao]").alphanumeric();
	// mascara - fim
	
	// tab/enter													 
	textboxes = $("input[type='text']:visible:enabled, input[type='submit']:visible:enabled, input[type='radio']:visible:enabled, select:visible:enabled, textarea:visible:enabled");	
	$(textboxes).keypress (checkForEnter);
	function checkForEnter (event) {
		
		if (event.keyCode == 9 || event.keyCode == 13) {
			
			// corrige problema - quando a agenda está aberta, o tab/enter fica com o FOCUS na hora_inicio
			if ( $("#TB_window").length ) { // verifica se o tb_show está sendo exibido
				setTimeout(function() {$('#observacao').focus();}, 100);
				event.preventDefault();
			} else {
				// ação do tab/enter
				currentBoxNumber = textboxes.index(this);	
				if (textboxes[currentBoxNumber + 1] != null) {
					nextBox = textboxes[currentBoxNumber + 1]
					setTimeout(function() {nextBox.focus();}, 100);
					event.preventDefault();
				}
				// fim - ação do tab/enter				
			}
			// fim - corrige problema
		}
	}
	// fim - tab/enter
	
    // abrir agenda
	$('#ver_agenda').click(function() {		
		id_usuario_envolvido = $("select[id='prospeccao_responsavel']").val();
		data_agendamento_inicio = $('#data_agendamento_inicio').val(); // pega a data inicial
		
		tb_show("Agenda","agenda_popup.php?id_usuario_atual="+id_usuario_envolvido+"&data_atual="+data_agendamento_inicio+"&height=<? echo $prospeccao_editar_tabela_height; ?>&width=<? echo $prospeccao_editar_tabela_width; ?>&placeValuesBeforeTB_=savedValues&TB_iframe=true","");		
		return false;
	});
	// fim - abrir agenda
	
	// cliente_antigo
	$("select[name='cliente_antigo']").change(function () { // ao mudar o valor do select

		var cliente_antigo_atual = $(this).val(); // lê o valor selecionado		
		$.post("prospeccao_consulta_cliente.php", 
			  {id:cliente_antigo_atual},
			  function(valor){

				  if( valor.pessoa1 == "F"){
					  $('input:radio[id="pessoa"]:nth(0)').attr('checked', true);
				  }
				  if( valor.pessoa1 == "J"){
					  $('input:radio[id="pessoa"]:nth(1)').attr('checked', true);
				  }
				  
				  $('#nome_razao_social').val(valor.nome1); 
				  $('#fantasia').val(valor.fantasia1);
				  $('#cpf_cnpj').val(valor.cgc1);
				  $('#rg_inscricao').val(valor.insc1);
				  $('#cep').val(valor.cep1);
				  $('#endereco').val(valor.endereco1);
				  $('#bairro').val(valor.bairro1);
				  $('#cidade').val(valor.cidade1);
				  $('#estado').val(valor.uf1);
				  $('#telefone').val(valor.telefone1);
				  $('#celular').val(valor.celular1);

			  }, "json"
		);

	});
	// fim - cliente_antigo
	
	// sistema_recursos_success_possui	
	$('textarea[id="sistema_recursos_success_nao_possui"]').attr('disabled', true);
	$('textarea[id="sistema_recursos_success_nao_possui"]').val('');
	
	$("select[id=sistema_recursos_success_possui]").change(function(){
		
		var sistema_recursos_success_possui_atual = $(this).val(); // lê o valor selecionado
		if(sistema_recursos_success_possui_atual == '' || sistema_recursos_success_possui_atual == 't'){
			$('textarea[id="sistema_recursos_success_nao_possui"]').attr('disabled', true);
			$('textarea[id="sistema_recursos_success_nao_possui"]').val('');
		} else if(sistema_recursos_success_possui_atual == 'p'){
			$('textarea[id="sistema_recursos_success_nao_possui"]').attr('disabled', false);
		}
		
	});
	// fim - sistema_recursos_success_possui
	
	// contador_uf
	$("select[id=contador_uf]").change(function(){
		
		$("select[id=contador_cidade]").html('<option value="">...</option>');
		$("select[id=id_contador]").html('<option value="">...</option>');
		
		$.post("consulta_cidade.php", 
			{
			uf: $(this).val(), 
			cidade: ''
			},
			function(valor){
				$("select[id=contador_cidade]").html(valor);
			}
		);

	})
	// contador_uf - fim
		
	// contador_cidade
	$("select[id=contador_cidade]").change(function(){
		
		$("select[id=id_contador]").html('<option value="">...</option>');
		
		var contador_uf_atual = $("select[id=id_contador]").val();
		var contador_cidade_atual = $(this).val();

		// post
		$.post("prospeccao_contador_consultar.php", {
			   uf_atual: contador_uf_atual,
			   cidade_atual: contador_cidade_atual
			   }, function(valor) {
				  $("select[id=id_contador]").html(valor);
			   }
		);
		// fim - post
				
	});
	// fim - contador_cidade
	
	// id_concorrente
	$("select[id=id_concorrente]").click(function() {
		
		var id_concorrente_atual = $(this).val();
		
		// post
		$.post("prospeccao_concorrente_consultar.php", {
			   id_concorrente_atual: id_concorrente_atual
			   }, function(valor) {
				   
				  $("select[id=id_concorrente]").html(valor);
			   }
		);
		// fim - post
				
	});
	// fim - id_concorrente

	// indicador
	$('#label_indicador2').hide();
	$('#label_indicador2').text('');
	$('select[id="indicador_contador"]').attr('disabled', true);
	$('select[id="indicador_contador"]').hide();
	$('select[id="indicador_cliente"]').attr('disabled', true);
	$('select[id="indicador_cliente"]').hide();
	$('select[id="indicador_usuario"]').attr('disabled', true);	
	$('select[id="indicador_usuario"]').hide();
	$('input[id="indicador_funcionario"]').attr('disabled', true);	
	$('input[id="indicador_funcionario"]').hide();
	$('input[id="indicador_terceiro"]').attr('disabled', true);
	$('input[id="indicador_terceiro"]').hide();

	$("select[id=indicador]").change(function(){
		
		var indicador_atual = $(this).val();
		
		if(indicador_atual == "co"){ // contador
		
			$('#label_indicador2').show();
			$('#label_indicador2').text('Contador (indicador)');
			$('select[id="indicador_contador"]').attr('disabled', false);
			$('select[id="indicador_contador"]').show();
			$('select[id="indicador_cliente"]').attr('disabled', true);
			$('select[id="indicador_cliente"]').hide();
			$('select[id="indicador_usuario"]').attr('disabled', true);	
			$('select[id="indicador_usuario"]').hide();
			$('input[id="indicador_funcionario"]').attr('disabled', true);	
			$('input[id="indicador_funcionario"]').hide();
			$('input[id="indicador_terceiro"]').attr('disabled', true);
			$('input[id="indicador_terceiro"]').hide();		
			
		} else if(indicador_atual == "cl"){ // cliente
		
			$('#label_indicador2').show();
			$('#label_indicador2').text('Cliente (indicador)');
			$('select[id="indicador_contador"]').attr('disabled', true);
			$('select[id="indicador_contador"]').hide();
			$('select[id="indicador_cliente"]').attr('disabled', false);
			$('select[id="indicador_cliente"]').show();
			$('select[id="indicador_usuario"]').attr('disabled', true);	
			$('select[id="indicador_usuario"]').hide();
			$('input[id="indicador_funcionario"]').attr('disabled', true);	
			$('input[id="indicador_funcionario"]').hide();
			$('input[id="indicador_terceiro"]').attr('disabled', true);
			$('input[id="indicador_terceiro"]').hide();			
			
		} else if(indicador_atual == "cs"){ // usuario/colaborador
		
			$('#label_indicador2').show();
			$('#label_indicador2').text('Colaborador (indicador)');
			$('select[id="indicador_contador"]').attr('disabled', true);
			$('select[id="indicador_contador"]').hide();
			$('select[id="indicador_cliente"]').attr('disabled', true);
			$('select[id="indicador_cliente"]').hide();
			$('select[id="indicador_usuario"]').attr('disabled', false);	
			$('select[id="indicador_usuario"]').show();
			$('input[id="indicador_funcionario"]').attr('disabled', true);	
			$('input[id="indicador_funcionario"]').hide();
			$('input[id="indicador_terceiro"]').attr('disabled', true);
			$('input[id="indicador_terceiro"]').hide();		
			
		} else if(indicador_atual == "fu"){ // funcionario
		
			$('#label_indicador2').show();
			$('#label_indicador2').text('Funcionário (indicador)');
			$('select[id="indicador_contador"]').attr('disabled', true);
			$('select[id="indicador_contador"]').hide();
			$('select[id="indicador_cliente"]').attr('disabled', true);
			$('select[id="indicador_cliente"]').hide();
			$('select[id="indicador_usuario"]').attr('disabled', true);	
			$('select[id="indicador_usuario"]').hide();
			$('input[id="indicador_funcionario"]').attr('disabled', false);	
			$('input[id="indicador_funcionario"]').show();
			$('input[id="indicador_terceiro"]').attr('disabled', true);
			$('input[id="indicador_terceiro"]').hide();		
			
		} else if(indicador_atual == "te"){ // terceiro
		
			$('#label_indicador2').show();
			$('#label_indicador2').text('Terceiros (indicador)');
			$('select[id="indicador_contador"]').attr('disabled', true);
			$('select[id="indicador_contador"]').hide();
			$('select[id="indicador_cliente"]').attr('disabled', true);
			$('select[id="indicador_cliente"]').hide();
			$('select[id="indicador_usuario"]').attr('disabled', true);	
			$('select[id="indicador_usuario"]').hide();
			$('input[id="indicador_funcionario"]').attr('disabled', true);	
			$('input[id="indicador_funcionario"]').hide();
			$('input[id="indicador_terceiro"]').attr('disabled', false);
			$('input[id="indicador_terceiro"]').show();
			
		} else {
		
			$('#label_indicador2').hide();
			$('#label_indicador2').text('');
			$('select[id="indicador_contador"]').attr('disabled', true);
			$('select[id="indicador_contador"]').hide();
			$('select[id="indicador_cliente"]').attr('disabled', true);
			$('select[id="indicador_cliente"]').hide();
			$('select[id="indicador_usuario"]').attr('disabled', true);	
			$('select[id="indicador_usuario"]').hide();
			$('input[id="indicador_funcionario"]').attr('disabled', true);	
			$('input[id="indicador_funcionario"]').hide();
			$('input[id="indicador_terceiro"]').attr('disabled', true);
			$('input[id="indicador_terceiro"]').hide();		
			
		}
				
	});
	// fim - indicador
	
	// sistema_possui
	$('select[id="id_concorrente"]').attr('disabled', true);
	$('select[id="sistema_nivel_satisfacao"]').attr('disabled', true);
	$('select[id="sistema_nivel_utilizacao"]').attr('disabled', true);
	$('textarea[id="sistema_nivel_motivo"]').attr('disabled', true);
	
	$('#label_id_concorrente').hide();
	$('#id_concorrente').hide();
	$('.div_sistema_possui').hide();

	$("input[id=sistema_possui]").change(function(){
		
		var sistema_possui = $(this).val();
		
		if(sistema_possui == "s"){
		
			$('select[id="id_concorrente"]').attr('disabled', false);
			$('select[id="sistema_nivel_satisfacao"]').attr('disabled', false);
			$('select[id="sistema_nivel_utilizacao"]').attr('disabled', false);
			$('textarea[id="sistema_nivel_motivo"]').attr('disabled', false);
			
			$('#label_id_concorrente').show();
			$('#id_concorrente').show();
			$('.div_sistema_possui').show();

		} else {
			
			$('select[id="id_concorrente"]').attr('disabled', true);
			$('select[id="sistema_nivel_satisfacao"]').attr('disabled', true);
			$('select[id="sistema_nivel_utilizacao"]').attr('disabled', true);
			$('textarea[id="sistema_nivel_motivo"]').attr('disabled', true);
			
			$('#label_id_concorrente').hide();
			$('#id_concorrente').hide();
			$('.div_sistema_possui').hide();

		}
				
	});
	// fim - sistema_possui

	// empresa_controle_manual
	$('input[id="podemos_ofertar"]').attr('disabled', true);
	
	$('.div_empresa_controle_manual').hide();
	
	$("input[id=empresa_controle_manual]").change(function(){
		
		var empresa_controle_manual = $(this).val();
		
		if(empresa_controle_manual == "s"){
		
			$('input[id="podemos_ofertar"]').attr('disabled', false);
			
			$('.div_empresa_controle_manual').show();

		} else {
			
			$('input[id="podemos_ofertar"]').attr('disabled', true);
			
			$('.div_empresa_controle_manual').hide();

		}
				
	});
	// fim - empresa_controle_manual	
	
	<? if($cliente_novo_antigo == "n"){ ?>
	// uf
	$("select[id=uf]").change(function(){
		$("select[id=cidade]").html('<option value="0">Carregando...</option>');
		
		$.post("consulta_cidade.php", 
			{
			uf: $(this).val(), 
			cidade: ''
			},
			function(valor){
				$("select[id=cidade]").html(valor);
			}
		);

	})
	// uf - fim	
	<? } ?>
	
});
</script>

<title>Nova prospecção</title>
</head>

<body>
<form id="form" name="form" method="POST" action="<?php echo $editFormAction; ?>">

<input type="hidden" name="MM_insert" value="prospeccao" />

<div class="div_solicitacao_linhas">
<table cellspacing="0" cellpadding="0" width="100%">
	<tr>
		<td style="text-align:left">
		Controle de prospecção para
        <?php 
		if($cliente_novo_antigo=="n"){echo "novo cliente";}
		if($cliente_novo_antigo=="a"){echo "cliente antigo";} 
		?> 
		</td>

		<td style="text-align: right">
		&lt;&lt; <a href="prospeccao.php?padrao=sim&<? echo $prospeccao_padrao; ?>">Voltar</a> | 
		Usuário logado: <? echo $row_usuario['nome']; ?> |
		<a href="painel/padrao_sair.php">Sair</a>
        </td>
	</tr>
</table>
</div>

<div class="div_solicitacao_linhas3">
<table cellspacing="0" cellpadding="0" width="945">
	<tr>
		<td style="text-align:left" width="365">
		<span class="label_solicitacao"><label id="label_prospeccao_responsavel">Representante Comercial<span id="req">*</span>: </label></span>
        <select name="prospeccao_responsavel" id="prospeccao_responsavel" style="width: 320px;">
        <option value="">Selecione ...</option>
        <?php
        do {  
        ?>
        <option value="<?php echo $row_prospeccao_responsavel['IdUsuario']?>"
		<?php
		// caso tenha o usuário já definido
		if($row_usuario['IdUsuario'] != ""){
	        if (!(strcmp($row_prospeccao_responsavel['IdUsuario'], $row_usuario['IdUsuario']))) {echo "selected=\"selected\"";}
		}
		// caso tenha o usuário já definido		
		?>>
		<?php echo utf8_encode($row_prospeccao_responsavel['nome']); ?>
        </option>
        <?php
        } while ($row_prospeccao_responsavel = mysql_fetch_assoc($prospeccao_responsavel));
        $rows = mysql_num_rows($prospeccao_responsavel);
        if($rows > 0) {
        mysql_data_seek($prospeccao_responsavel, 0);
        $row_prospeccao_responsavel = mysql_fetch_assoc($prospeccao_responsavel);
        }
        ?>
        </select>
        </td>
        
		<td style="text-align: right">
        <span class="label_solicitacao"><label id="label_cliente_antigo">Cliente (<? echo $totalRows_cliente_antigo_listar; ?>):<span id="req">*</span></label></span>
        <br>
        <select name="cliente_antigo" id="cliente_antigo" style="width: 320px;">
        <option value="">Selecione ...</option>
        <?php
        do {  
        ?>
        <option value="<?php echo $row_cliente_antigo_listar['cliente17']?>"><?php echo utf8_encode($row_cliente_antigo_listar['nome1']); ?></option>
        <?php
        } while ($row_cliente_antigo_listar = mysql_fetch_assoc($cliente_antigo_listar));
        $rows = mysql_num_rows($cliente_antigo_listar);
        if($rows > 0) {
        mysql_data_seek($cliente_antigo_listar, 0);
        $row_cliente_antigo_listar = mysql_fetch_assoc($cliente_antigo_listar);
        }
        ?>
        </select>
        </td>
	</tr>
</table>
</div>


<div class="div_solicitacao_linhas4">
<table cellspacing="0" cellpadding="0" width="945">
	<tr>
		<td style="text-align:left" width="350">
		<span class="label_solicitacao"><label id="label_nome_razao_social">Nome/Razão Social:<span id="req">*</span></label></span>
		<br>
		<input type="text" name="nome_razao_social" id="nome_razao_social" style="width: 300px;" value="">
        </td>
        
        <td style="text-align: left"  width="350">
		<span class="label_solicitacao"><label id="label_fantasia">Nome Fantasia<span id="req">*</span>:</label></span>
		<br>
        <input type="text" name="fantasia" id="fantasia" style="width: 300px;" value="">
        </td>
		
		<td style="text-align: right">
		<span class="label_solicitacao"><label id="label_ramo_de_atividade">Ramo de Atividade:<span id="req">*</span></label></span>
		<br>
        <select name="ramo_de_atividade" id="ramo_de_atividade" style="width: 250px;">
        <option value="">Escolha ...</option>
        <?php
        do {  
        ?>
        <option value="<?php echo $row_geral_tipo_ramo_atividade['titulo']?>"><?php echo $row_geral_tipo_ramo_atividade['titulo']?></option>
        <?php
        } while ($row_geral_tipo_ramo_atividade = mysql_fetch_assoc($geral_tipo_ramo_atividade));
        $rows = mysql_num_rows($geral_tipo_ramo_atividade);
        if($rows > 0) {
        mysql_data_seek($geral_tipo_ramo_atividade, 0);
        $row_geral_tipo_ramo_atividade = mysql_fetch_assoc($geral_tipo_ramo_atividade);
        }
        ?>
        </select>
		</td>
	</tr>
</table>
</div>

<div class="div_solicitacao_linhas3">
<table cellspacing="0" cellpadding="0" width="945">
	<tr>        
		<td style="text-align: left" width="180">
		<span class="label_solicitacao"><label id="label_pessoa">Pessoa:<span id="req">*</span></label></span>
        <input type="radio" name="pessoa" id="pessoa" value="f"> Física
        <input type="radio" name="pessoa" id="pessoa" value="j"> Jurídica
        </td>
		
		<td style="text-align: left" width="260">
		<span class="label_solicitacao"><label id="label_cpf_cnpj">CPF/CNPJ:<span id="req">*</span></label></span>
		<input type="text" name="cpf_cnpj" id="cpf_cnpj" style="width: 180px;" value="" maxlength="14">
		<div class="cpf_cnpj_erro" id="cpf_cnpj_erro"></div>
        </td>

		<td style="text-align:left" width="290">
		<span class="label_solicitacao"><label id="label_rg_inscricao">RG/Inscrição estadual<span id="req">*</span>:</label></span>
        <input type="text" name="rg_inscricao" id="rg_inscricao" style="width: 150px;" value="">
        </td>
                
        <td style="text-align: right">
		<span class="label_solicitacao"><label id="label_rg_orgao_expeditor">Orgão Expedidor<span id="req">*</span>:</label></span>
        <input type="text" name="rg_orgao_expeditor" id="rg_orgao_expeditor" style="width: 100px;" value="">
        </td>
	</tr>
</table>
</div>

<div class="div_solicitacao_linhas4">
<table cellspacing="0" cellpadding="0" width="945">
	<tr>
        <td style="text-align: left" width="400">
		<span class="label_solicitacao"><label id="label_endereco">Endereço<span id="req">*</span>:</label></span>
        <input type="text" name="endereco" id="endereco" style="width: 330px;" value="">
        </td>
                
        <td style="text-align: left" width="110">
		<span class="label_solicitacao"><label id="label_endereco_numero">n°<span id="req">*</span>:</label></span>
        <input type="text" name="endereco_numero" id="endereco_numero" style="width: 80px;" value="">
        </td>
        
        <td style="text-align: left" width="130">
		<span class="label_solicitacao"><label id="label_endereco_complemento">Comp.<span id="req">*</span>:</label></span>
        <input type="text" name="endereco_complemento" id="endereco_complemento" style="width: 80px;" value="">
        </td>
		
		<td style="text-align: right">
		<span class="label_solicitacao"><label id="label_endereco_referencia">Ponto de referência:<span id="req">*</span></label></span>
		<input type="text" name="endereco_referencia" id="endereco_referencia" style="width: 160px;" value="">
        </td>

	</tr>
</table>
</div>

<div class="div_solicitacao_linhas3">
<table cellspacing="0" cellpadding="0" width="945">
	<tr>
	
		<td style="text-align:left" width="280">
		<span class="label_solicitacao"><label id="label_bairro">Bairro:<span id="req">*</span></label></span>
		<input type="text" name="bairro" id="bairro" style="width: 230px;" value="">
        </td>

		<td style="text-align:left" width="200">
		<span class="label_solicitacao"><label id="label_cep">CEP:<span id="req">*</span></label></span>
		<input type="text" name="cep" id="cep" style="width: 150px;" value="">
        </td>
		
		<td style="text-align:right">
		<span class="label_solicitacao"><label id="label_cidade">Cidade/UF:<span id="req">*</span></label></span>
		
		<select name="uf" id="uf">
		<option value="">Selecione...</option>				
		<option value="AC">AC</option>
		<option value="AL">AL</option>
		<option value="AP">AP</option>
		<option value="AM">AM</option>
		<option value="BA">BA</option>
		<option value="CE">CE</option>
		<option value="DF">DF</option>
		<option value="ES">ES</option>
		<option value="GO">GO</option>
		<option value="MA">MA</option>
		<option value="MT">MT</option>
		<option value="MS">MS</option>
		<option value="MG">MG</option>
		<option value="PA">PA</option>
		<option value="PB">PB</option>
		<option value="PR">PR</option>
		<option value="PE">PE</option>
		<option value="PI">PI</option>
		<option value="RJ">RJ</option>
		<option value="RN">RN</option>
		<option value="RS">RS</option>
		<option value="RO">RO</option>
		<option value="RR">RR</option>
		<option value="SC">SC</option>
		<option value="SP">SP</option>
		<option value="SE">SE</option>
		<option value="TO">TO</option>
		</select>
				
		<select name="cidade" id="cidade" style="width: 200px;">
		<option value="">Selecione primeiro o estado...</option>
		</select>
        </td>
                
	</tr>
</table>
</div>

<div class="div_solicitacao_linhas4">
<table cellspacing="0" cellpadding="0" width="945">
	<tr>
        
        <td style="text-align: left" width="300">
		<span class="label_solicitacao"><label id="label_telefone">Telefone<span id="req">*</span>:</label></span>
        <input type="text" name="telefone" id="telefone" style="width: 200px;" value="">
        </td>
        
		<td style="text-align: right" width="250">
		<span class="label_solicitacao"><label id="label_celular">Celular:<span id="req">*</span></label></span>
		<input type="text" name="celular" id="celular" style="width: 200px;" value="">
        </td>
		
		<td style="text-align: right">
		<span class="label_solicitacao"><label id="label_email">E-mail:<span id="req">*</span></label></span>
		<input type="text" name="email" id="email" style="width: 300px;" value="">
        </td>
	</tr>
</table>
</div>

<div class="div_solicitacao_linhas3">
<table cellspacing="0" cellpadding="0" width="945">
	<tr>   
		<td style="text-align:left" width="360">
		<span class="label_solicitacao"><label id="label_empresa_responsavel">Responsável pela empresa:<span id="req">*</span></label></span>
		<input type="text" name="empresa_responsavel" id="empresa_responsavel" style="width: 180px;" value="">
        </td>
		   
		<td style="text-align:left" width="330">
		<span class="label_solicitacao"><label id="label_responsavel_por_ti">Responsável por TI:<span id="req">*</span></label></span>
		<input type="text" name="responsavel_por_ti" id="responsavel_por_ti" style="width: 180px;" value="">
        </td>
		
		<td style="text-align:right">
		<span class="label_solicitacao"><label id="label_empresa_contato">Contato:<span id="req">*</span></label></span>
		<input type="text" name="empresa_contato" id="empresa_contato" style="width: 180px;" value="">
        </td>
</table>
</div>

<div class="div_solicitacao_linhas4">
<table cellspacing="0" cellpadding="0" width="945">
	<tr>
		<td style="text-align:left" width="350">
        <span class="label_solicitacao"><label id="label_ativo_passivo">Tipo de prospect:<span id="req">*</span></label></span>
		<input type="radio" name="ativo_passivo" id="ativo_passivo" value="a"> Ativo 
        <input name="ativo_passivo" type="radio" id="ativo_passivo" value="p" checked="checked"> Passivo 
        </td>
        
	</tr>
</table>
</div>

<div class="div_solicitacao_linhas3">
<table cellspacing="0" cellpadding="0" width="945">
	<tr>
		<td style="text-align:left" width="250">
		<span class="label_solicitacao"><label id="label_indicador">Indicador:<span id="req">*</span></label></span>
		<br>
        <select name="indicador" id="indicador" style="width: 200px;">
        <option value=""> ...</option>
        <option value="co">Contador</option>
		<option value="cl">Cliente</option>
		<option value="cs">Colaborador Success</option>
		<option value="fu">Funcionário</option>
		<option value="te">Terceiros</option>
        </select>
        </td>
		
		<td  width="420" style="text-align: left">
		<span class="label_solicitacao"><label id="label_indicador2"></label></span>
		<br>
		<!-- indicador_contador -->
		<?
		// indicador_contador - para selectbox
		mysql_select_db($database_conexao, $conexao);
		$query_indicador_contador = "SELECT prospeccao_contador.razao, prospeccao_contador.cidade FROM prospeccao_contador ORDER BY prospeccao_contador.razao ASC";
		$indicador_contador = mysql_query($query_indicador_contador, $conexao) or die(mysql_error());
		$row_indicador_contador = mysql_fetch_assoc($indicador_contador);
		$totalRows_indicador_contador = mysql_num_rows($indicador_contador);
		// fim - indicador_contador - para selectbox
		?>
        <select name="indicador_contador" id="indicador_contador" style="width: 400px;">
        <option value="">...</option>
        <?php
        do {  
        ?>
        <option value="<?php echo $row_indicador_contador['razao']?>">
		[<?php echo $row_indicador_contador['cidade']; ?>] <?php echo $row_indicador_contador['razao']; ?>
        </option>
        <?php
        } while ($row_indicador_contador = mysql_fetch_assoc($indicador_contador));
        $rows = mysql_num_rows($indicador_contador);
        if($rows > 0) {
        mysql_data_seek($indicador_contador, 0);
        $row_indicador_contador = mysql_fetch_assoc($indicador_contador);
        }
        ?>
        </select>
		<? mysql_free_result($indicador_contador); ?>
		<!-- fim - indicador_contador -->

		<!-- indicador_cliente -->
		<?
		// indicador_cliente - para selectbox
		mysql_select_db($database_conexao, $conexao);
		$query_indicador_cliente = sprintf("
		SELECT 
		da37.codigo17, da37.cliente17, 
		da01.nome1 
		FROM da37 
		INNER JOIN da01 ON da37.cliente17 = da01.codigo1 
		WHERE da01.sr_deleted <> 'T' and da37.sr_deleted <> 'T' 
		ORDER BY da01.nome1 ASC", 
		GetSQLValueString($row_usuario['praca'], "text"));
		$indicador_cliente = mysql_query($query_indicador_cliente, $conexao) or die(mysql_error());
		$row_indicador_cliente = mysql_fetch_assoc($indicador_cliente);
		$totalRows_indicador_cliente = mysql_num_rows($indicador_cliente);
		// fim - indicador_cliente - para selectbox
		?>
		<select name="indicador_cliente" id="indicador_cliente" style="width: 400px;">
		<option value="">...</option>
		<?php
		do {  
		?>
		<option value="<?php echo $row_indicador_cliente['nome1']?>">
		<?php echo $row_indicador_cliente['nome1']; ?>
		</option>
		<?php
		} while ($row_indicador_cliente = mysql_fetch_assoc($indicador_cliente));
		$rows = mysql_num_rows($indicador_cliente);
		if($rows > 0) {
		mysql_data_seek($indicador_cliente, 0);
		$row_indicador_cliente = mysql_fetch_assoc($indicador_cliente);
		}
		?>
		</select>
		<? mysql_free_result($indicador_cliente); ?>
		<!-- fim - indicador_cliente -->
		
		<!-- indicador_usuario -->
		<?
		// indicador_usuario - para selectbox
		mysql_select_db($database_conexao, $conexao);
		$query_indicador_usuario = "
		SELECT usuarios.IdUsuario, usuarios.nome 
		FROM usuarios
		WHERE usuarios.status = 1 
		ORDER BY usuarios.nome ASC";
		$indicador_usuario = mysql_query($query_indicador_usuario, $conexao) or die(mysql_error());
		$row_indicador_usuario = mysql_fetch_assoc($indicador_usuario);
		$totalRows_indicador_usuario = mysql_num_rows($indicador_usuario);
		// fim - indicador_usuario - para selectbox
		?>
		<select name="indicador_usuario" id="indicador_usuario" style="width: 400px;">
		<option value="">...</option>
		<?php
		do {  
		?>
		<option value="<?php echo $row_indicador_usuario['nome']?>">
		<?php echo $row_indicador_usuario['nome']; ?>
		</option>
		<?php
		} while ($row_indicador_usuario = mysql_fetch_assoc($indicador_usuario));
		$rows = mysql_num_rows($indicador_usuario);
		if($rows > 0) {
		mysql_data_seek($indicador_usuario, 0);
		$row_indicador_usuario = mysql_fetch_assoc($indicador_usuario);
		}
		?>
		</select>
		<? mysql_free_result($indicador_usuario); ?>
		<!-- fim - indicador_usuario -->
		
		<input type="text" name="indicador_funcionario" id="indicador_funcionario" style="width: 300px;" value="">
		
		<input type="text" name="indicador_terceiro" id="indicador_terceiro" style="width: 300px;" value="">
        </td>
		
        <td style="text-align: right">
		<span class="label_solicitacao"><label id="label_indicado_por">Nome do indicador:<span id="req">*</span></label></span>
		<br>
		<input type="text" name="indicado_por" id="indicado_por" style="width: 250px;" value="">
		<td>
		
	</tr>
</table>
</div>

<div class="div_solicitacao_linhas4">
<table cellspacing="0" cellpadding="0" width="945">
	<tr>
		<td style="text-align:left">
		
		<span class="label_solicitacao"><label>Necessidades/Interesses do cliente:</label></span>


		<?
		// geral_tipo_modulo_categoria_listar
		mysql_select_db($database_conexao, $conexao);
		$query_geral_tipo_modulo_categoria_listar = "
		SELECT * 
		FROM geral_tipo_modulo_categoria 
		WHERE IdTipoModuloCategoria <> 2 and IdTipoModuloCategoria <> 7 
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
				WHERE IdTipoModuloCategoria = ".$row_geral_tipo_modulo_categoria_listar['IdTipoModuloCategoria']."
				ORDER BY geral_tipo_modulo.IdTipoModuloCategoria ASC, geral_tipo_modulo.ordem ASC, geral_tipo_modulo.IdTipoModulo ASC";
				$geral_tipo_modulo_listar = mysql_query($query_geral_tipo_modulo_listar, $conexao) or die(mysql_error());
				$row_geral_tipo_modulo_listar = mysql_fetch_assoc($geral_tipo_modulo_listar);
				$totalRows_geral_tipo_modulo_listar = mysql_num_rows($geral_tipo_modulo_listar);
				// fim - geral_tipo_modulo_listar
				?>
				
					<? do { ?>
					
						<input  name="necessidades[]" id="necessidades" type="checkbox" class="checkbox" value="<? echo $row_geral_tipo_modulo_listar['IdTipoModulo']; ?>"/>
						<? echo $row_geral_tipo_modulo_listar['descricao']; ?>
						
					<? } while ($row_geral_tipo_modulo_listar = mysql_fetch_assoc($geral_tipo_modulo_listar)); ?>
					
				<? mysql_free_result($geral_tipo_modulo_listar); ?>
				
			</div>
			
		<? } while ($row_geral_tipo_modulo_categoria_listar = mysql_fetch_assoc($geral_tipo_modulo_categoria_listar)); ?>
		</fieldset>
		<? mysql_free_result($geral_tipo_modulo_categoria_listar); ?>
	   
		<label for="necessidades[]" class="error">Selecione pelo menos um dos módulos acima</label>

        </td>

	</tr>
</table>
</div>

<div class="div_solicitacao_linhas3">
<table cellspacing="0" cellpadding="0" width="945">
	<tr>        
        <td style="text-align: left" width="220">
		<span class="label_solicitacao"><label id="label_sistema_possui">Possui sistema?:<span id="req">*</span></label></span>
        <input type="radio" name="sistema_possui" id="sistema_possui" value="n" checked="checked"> Não
        <input type="radio" name="sistema_possui" id="sistema_possui" value="s"> Sim
        </td>
        
		<td style="text-align: right">
		<span class="label_solicitacao"><label id="label_id_concorrente">Concorrente: <span id="req">*</span></label></span>
		<?
		// concorrente - para selectbox
		mysql_select_db($database_conexao, $conexao);
		$query_concorrente = "SELECT * FROM prospeccao_concorrente ORDER BY prospeccao_concorrente.nome ASC";
		$concorrente = mysql_query($query_concorrente, $conexao) or die(mysql_error());
		$row_concorrente = mysql_fetch_assoc($concorrente);
		$totalRows_concorrente = mysql_num_rows($concorrente);
		// fim - concorrente - para selectbox
		?>
		<select name="id_concorrente" id="id_concorrente" style="width: 350px;">
		<option value="">...</option>
		<?php
		do {  
		?>
		<option value="<?php echo $row_concorrente['id']?>">
		<?php echo $row_concorrente['nome']; ?> 
		</option>
		<?php
		} while ($row_concorrente = mysql_fetch_assoc($concorrente));
		$rows = mysql_num_rows($concorrente);
		if($rows > 0) {
		mysql_data_seek($concorrente, 0);
		$row_concorrente = mysql_fetch_assoc($concorrente);
		}
		?>
		</select>
		<? mysql_free_result($concorrente); ?>
        </td>

	</tr>
</table>
</div>

<div class="div_solicitacao_linhas4 div_sistema_possui">
<table cellspacing="0" cellpadding="0" width="945">
	<tr>        
        <td style="text-align: left">
		<span class="label_solicitacao"><label id="label_sistema_nivel_satisfacao">Nível de satisfação:<span id="req">*</span></label></span>
        <select name="sistema_nivel_satisfacao" id="sistema_nivel_satisfacao" style="width: 120px;">
        <option value=""> ...</option>
        <option value="a">Alto</option>
		<option value="m">Médio</option>
		<option value="b">Baixo</option>
		<option value="i">Insatisfeito</option>
        </select>
        </td>
		

		<td style="text-align: right">
		<span class="label_solicitacao"><label id="label_sistema_nivel_utilizacao">Nível de utilização:<span id="req">*</span></label></span>
        <select name="sistema_nivel_utilizacao" id="sistema_nivel_utilizacao" style="width: 120px;">
        <option value=""> ...</option>
        <option value="a">Alto</option>
		<option value="m">Médio</option>
		<option value="b">Baixo</option>
		<option value="n">Não implantado</option>
        </select>
        </td>
        
	</tr>
</table>
</div>

<div class="div_solicitacao_linhas3 div_sistema_possui">
<table cellspacing="0" cellpadding="0" width="945">
	<tr>        
		<td style="text-align: left">
		<span class="label_solicitacao"><label id="label_sistema_nivel_motivo">Motivo da satisfação/insatisfação:<span id="req">*</span></label></span>
        <br>
		<textarea name="sistema_nivel_motivo" id="sistema_nivel_motivo" style="margin-top: 2px; width: 945px; height: 100px;"></textarea>
        </td>
	</tr>
</table>
</div>

<div class="div_solicitacao_linhas4 div_sistema_possui">
<table cellspacing="0" cellpadding="0" width="945">
	<tr>        
        <td style="text-align: left">
		<span class="label_solicitacao"><label id="label_sistema_recursos">Quais recursos o cliente utiliza?:<span id="req">*</span></label></span>
		
		<!-- sistema_recursos -->
		<fieldset>
							
			<?
			// geral_tipo_modulo_listar
			mysql_select_db($database_conexao, $conexao);
			$query_geral_tipo_modulo_listar = "
			SELECT 
			geral_tipo_modulo.IdTipoModulo, geral_tipo_modulo.IdTipoModuloCategoria, geral_tipo_modulo.descricao 
			FROM geral_tipo_modulo 
			WHERE IdTipoModuloCategoria = 1
			ORDER BY geral_tipo_modulo.IdTipoModuloCategoria ASC, geral_tipo_modulo.ordem ASC, geral_tipo_modulo.IdTipoModulo ASC";
			$geral_tipo_modulo_listar = mysql_query($query_geral_tipo_modulo_listar, $conexao) or die(mysql_error());
			$row_geral_tipo_modulo_listar = mysql_fetch_assoc($geral_tipo_modulo_listar);
			$totalRows_geral_tipo_modulo_listar = mysql_num_rows($geral_tipo_modulo_listar);
			// fim - geral_tipo_modulo_listar
			?>
			
				<? do { ?>
				
					<input  name="sistema_recursos[]" id="sistema_recursos" type="checkbox" class="checkbox" value="<? echo $row_geral_tipo_modulo_listar['IdTipoModulo']; ?>" />
					<? echo $row_geral_tipo_modulo_listar['descricao']; ?>
					
				<? } while ($row_geral_tipo_modulo_listar = mysql_fetch_assoc($geral_tipo_modulo_listar)); ?>
				
			<? mysql_free_result($geral_tipo_modulo_listar); ?>

		</fieldset>
		<!-- fim - sistema_recursos -->
		<td>
	</tr>
</table>
</div>

<div class="div_solicitacao_linhas3 div_sistema_possui">
<table cellspacing="0" cellpadding="0" width="945">
	<tr>        
        
		<td style="text-align: left">
		<span class="label_solicitacao"><label id="label_sistema_recursos_success_possui">O Success tem os recursos que o cliente utiliza?:<span id="req">*</span></label></span>
		<br>
        <select name="sistema_recursos_success_possui" id="sistema_recursos_success_possui" style="width: 120px;">
        <option value=""> ...</option>
        <option value="p">Parcialmente</option>
		<option value="t">Totalmente</option>
        </select>
        </td>
	</tr>
</table>
</div>

<div class="div_solicitacao_linhas4 div_sistema_possui">
<table cellspacing="0" cellpadding="0" width="945">
	<tr>        
        
		<td style="text-align: left">
		<span class="label_solicitacao"><label id="label_sistema_recursos_success_nao_possui">Recursos que o cliente utiliza e o Success não tem:<span id="req">*</span></label></span>
        <br>
		<textarea name="sistema_recursos_success_nao_possui" id="sistema_recursos_success_nao_possui" style="margin-top: 2px; width: 945px; height: 100px;"></textarea>
        </td>
	</tr>
</table>
</div>

<div class="div_solicitacao_linhas3">
<table cellspacing="0" cellpadding="0" width="945">
	<tr>        
        <td style="text-align: left" width="450">
		<span class="label_solicitacao"><label id="label_empresa_controle_manual">Empresa faz algum controle manual?:<span id="req">*</span></label></span>
        <input type="radio" name="empresa_controle_manual" id="empresa_controle_manual" value="n" checked="checked"> Não
        <input type="radio" name="empresa_controle_manual" id="empresa_controle_manual" value="s"> Sim
        </td>
	</tr>
</table>
</div>

<div class="div_solicitacao_linhas4 div_empresa_controle_manual">
<table cellspacing="0" cellpadding="0" width="945">
	<tr>        
        <td style="text-align: left">
		<span class="label_solicitacao"><label id="label_podemos_ofertar">O que podemos ofertar para automatizar o processo manual?:<span id="req">*</span></label></span>
		
		
		<!-- podemos_ofertar -->

		<?
		// geral_tipo_modulo_categoria_listar
		mysql_select_db($database_conexao, $conexao);
		$query_geral_tipo_modulo_categoria_listar = "
		SELECT * 
		FROM geral_tipo_modulo_categoria 
		WHERE IdTipoModuloCategoria <> 2 and IdTipoModuloCategoria <> 7 
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
				WHERE IdTipoModuloCategoria = ".$row_geral_tipo_modulo_categoria_listar['IdTipoModuloCategoria']."
				ORDER BY geral_tipo_modulo.IdTipoModuloCategoria ASC, geral_tipo_modulo.ordem ASC, geral_tipo_modulo.IdTipoModulo ASC";
				$geral_tipo_modulo_listar = mysql_query($query_geral_tipo_modulo_listar, $conexao) or die(mysql_error());
				$row_geral_tipo_modulo_listar = mysql_fetch_assoc($geral_tipo_modulo_listar);
				$totalRows_geral_tipo_modulo_listar = mysql_num_rows($geral_tipo_modulo_listar);
				// fim - geral_tipo_modulo_listar
				?>
				
					<? do { ?>
					
						<input  name="podemos_ofertar[]" id="podemos_ofertar" type="checkbox" class="checkbox" value="<? echo $row_geral_tipo_modulo_listar['IdTipoModulo']; ?>"/>
						<? echo $row_geral_tipo_modulo_listar['descricao']; ?>
						
					<? } while ($row_geral_tipo_modulo_listar = mysql_fetch_assoc($geral_tipo_modulo_listar)); ?>
					
				<? mysql_free_result($geral_tipo_modulo_listar); ?>
				
			</div>
			
		<? } while ($row_geral_tipo_modulo_categoria_listar = mysql_fetch_assoc($geral_tipo_modulo_categoria_listar)); ?>
		</fieldset>
		<? mysql_free_result($geral_tipo_modulo_categoria_listar); ?>
		<!-- fim - podemos_ofertar -->
		<td>
	</tr>
</table>
</div>

<div class="div_solicitacao_linhas3">
<table cellspacing="0" cellpadding="0" width="945">
	<tr>        
        <td style="text-align: left" width="450">
		<span class="label_solicitacao"><label id="label_nivel_interesse">Nível de interesse:<span id="req">*</span></label></span>
        <select name="nivel_interesse" id="nivel_interesse" style="width: 120px;">
        <option value=""> ...</option>
        <option value="a">Alto</option>
		<option value="m">Médio</option>
		<option value="b">Baixo</option>
		<option value="n">Nenhum</option>
        </select>
        </td>
	</tr>
</table>
</div>

<div class="div_solicitacao_linhas4">
<table cellspacing="0" cellpadding="0" width="945">
	<tr>        
		<td style="text-align: left" width="350">
		
		<span class="label_solicitacao"><label id="label_id_contador">Contabilidade: <span id="req">*</span></label></span>
		
		<select name="contador_uf" id="contador_uf">
		<option value="">Estado...</option>				
		<option value="AC">AC</option>
		<option value="AL">AL</option>
		<option value="AP">AP</option>
		<option value="AM">AM</option>
		<option value="BA">BA</option>
		<option value="CE">CE</option>
		<option value="DF">DF</option>
		<option value="ES">ES</option>
		<option value="GO">GO</option>
		<option value="MA">MA</option>
		<option value="MT">MT</option>
		<option value="MS">MS</option>
		<option value="MG">MG</option>
		<option value="PA">PA</option>
		<option value="PB">PB</option>
		<option value="PR">PR</option>
		<option value="PE">PE</option>
		<option value="PI">PI</option>
		<option value="RJ">RJ</option>
		<option value="RN">RN</option>
		<option value="RS">RS</option>
		<option value="RO">RO</option>
		<option value="RR">RR</option>
		<option value="SC">SC</option>
		<option value="SP">SP</option>
		<option value="SE">SE</option>
		<option value="TO">TO</option>
		</select>
				
		<select name="contador_cidade" id="contador_cidade" style="width: 200px;">
		<option value="">Selecione primeiro o estado...</option>
		</select>
		
        <select name="id_contador" id="id_contador" style="width: 450px;">
        <option value="">...</option>
        </select>
        </td>
       
	</tr>
</table>
</div>

<div class="div_solicitacao_linhas3">
<table cellspacing="0" cellpadding="0" width="945">
	<tr>
		<td style="text-align:left">
        <span class="label_solicitacao"><label id="label_enquadramento_fiscal">Enquadramento Fiscal:<span id="req">*</span></label></span>
		<input name="enquadramento_fiscal" type="radio" id="enquadramento_fiscal" value="Super Simples" checked="checked"> Super Simples 
        <input type="radio" name="enquadramento_fiscal" id="enquadramento_fiscal" value="Débito e Crédito"> Débito e Crédito 
        <input type="radio" name="enquadramento_fiscal" id="enquadramento_fiscal" value=""> Outro 
        
        <input type="text" name="enquadramento_fiscal_outro" id="enquadramento_fiscal_outro" style="width:300px" />
        </td>
	</tr>
</table>
</div>

<div class="div_solicitacao_linhas4">
<table cellspacing="0" cellpadding="0" width="945">
	<tr>
		<td style="text-align:left" width="545">
		<span class="label_solicitacao"><label>Obrigações fiscais exigidas:</label></span>
        <input type="checkbox" name="exige_cupom_fiscal" id="exige_cupom_fiscal" value="1" /> Cupom Fiscal
        <input type="checkbox" name="exige_nfe" id="exige_nfe" value="1" /> NFe
		<input type="checkbox" name="exige_nfce" id="exige_nfce" value="1" /> NFCe
		<input type="checkbox" name="exige_mdfe" id="exige_mdfe" value="1" /> MDFe
		<input type="checkbox" name="exige_ctee" id="exige_ctee" value="1" /> CTE-e
		<input type="checkbox" name="exige_efd" id="exige_efd" value="1" /> EFD
        </td>
        
        <td style="text-align: right">
		<span class="label_solicitacao"><label id="label_exige_outro">Outras<span id="req">*</span>:</label></span>
        <input type="text" name="exige_outro" id="exige_outro" style="width: 250px;" value="">
        </td>

	</tr>
</table>
</div>

<div class="div_solicitacao_linhas2">
<table cellspacing="0" cellpadding="0" width="100%">
  <tr>
    <td style="text-align: left">
	<span class="label_solicitacao"><label id="label_observacao">Observação:<span id="req">*</span></label></span>
	<br>
	<textarea name="observacao" id="observacao" style="margin-top: 2px; width: 945px; height: 100px;"></textarea>
	</td>
  </tr>
</table>
</div>

<div class="div_solicitacao_linhas3">
	<div style="font-weight: bold; margin-bottom: 10px;">Agendamento</div>
    
    <table cellspacing="0" cellpadding="0" width="945">
        <tr>
            <td style="text-align:left" width="290">
            <span class="label_solicitacao"><label id="label_data_agendamento_inicio">Data inicio:<span id="req">*</span></label></span>
            <br>
            <input name="data_agendamento_inicio" type="text" id="data_agendamento_inicio" style="width: 150px;">
            <a href="#" id="ver_agenda" name="ver_agenda" style="color: #03C; font-weight: bold; padding-left: 5px;">Ver agenda</a>
            </td>
            
            <td style="text-align: left" width="200">
            <span class="label_solicitacao"><label id="label_agendamento_tempo">Tempo:<span id="req">*</span></label></span>
            <br>
            <select name="agendamento_tempo" id="agendamento_tempo" style="width: 175px;">
                <option value="">Escolha...</option>
                <option value="<? echo $mm = 15; ?>"><? echo sprintf("%02dh %02dm", floor($mm/60), $mm%60); ?></option>
                <option value="<? echo $mm = 30; ?>"><? echo sprintf("%02dh %02dm", floor($mm/60), $mm%60); ?></option>
                <option value="<? echo $mm = 45; ?>"><? echo sprintf("%02dh %02dm", floor($mm/60), $mm%60); ?></option>
                <option value="<? echo $mm = 60; ?>"><? echo sprintf("%02dh %02dm", floor($mm/60), $mm%60); ?></option>
                <option value="<? echo $mm = 120; ?>"><? echo sprintf("%02dh %02dm", floor($mm/60), $mm%60); ?></option>
                <option value="<? echo $mm = 180; ?>"><? echo sprintf("%02dh %02dm", floor($mm/60), $mm%60); ?></option>
                <option value="<? echo $mm = 240; ?>"><? echo sprintf("%02dh %02dm", floor($mm/60), $mm%60); ?></option>
                <option value="<? echo $mm = 300; ?>"><? echo sprintf("%02dh %02dm", floor($mm/60), $mm%60); ?></option>
            </select>
    		</td>
            
            <td align="left" width="170">
            <span class="label_solicitacao"><label id="label_data_agendamento">Data fim:<span id="req">*</span></label></span>
            <br>
            <input name="data_agendamento" type="text" id="data_agendamento" readonly="readonly" style="width: 150px;">
            </td>

            <td align="right">
            <span class="label_solicitacao"><label id="label_prospeccao_agenda_tipo">Tipo:<span id="req">*</span></label></span>
            <br>
			
			<?
			// prospeccao_agenda_tipo
			mysql_select_db($database_conexao, $conexao);
			$query_prospeccao_agenda_tipo = "SELECT prospeccao_agenda_tipo.* FROM prospeccao_agenda_tipo ORDER BY id ASC";
			$prospeccao_agenda_tipo = mysql_query($query_prospeccao_agenda_tipo, $conexao) or die(mysql_error());
			$row_prospeccao_agenda_tipo = mysql_fetch_assoc($prospeccao_agenda_tipo);
			$totalRows_prospeccao_agenda_tipo = mysql_num_rows($prospeccao_agenda_tipo);
			// fim - prospeccao_agenda_tipo
			?>
			<select name="prospeccao_agenda_tipo" id="prospeccao_agenda_tipo" style="width: 130px;">
			<option value="">...</option>
			<?php
			do {  
			?>
			<option title="<?php echo $row_prospeccao_agenda_tipo['id']?>" value="<?php echo $row_prospeccao_agenda_tipo['id']?>"><?php echo $row_prospeccao_agenda_tipo['titulo']?></option>
			
			<?php
			} while ($row_prospeccao_agenda_tipo = mysql_fetch_assoc($prospeccao_agenda_tipo));
			$rows = mysql_num_rows($prospeccao_agenda_tipo);
			if($rows > 0) {
			mysql_data_seek($prospeccao_agenda_tipo, 0);
			$row_prospeccao_agenda_tipo = mysql_fetch_assoc($prospeccao_agenda_tipo);
			}
			?>
			</select>
			<? mysql_free_result($prospeccao_agenda_tipo); ?>
    		</td>
			
            <td align="right">
            <span class="label_solicitacao"><label id="label_agendamento_status">Status:<span id="req">*</span></label></span>
            <br>
            <select name="agendamento_status" id="agendamento_status" style="width: 130px;">
                <option value="aguardando atendente">aguardando atendente</option>
                <option value="aguardando retorno do cliente">aguardando retorno do cliente</option>
            </select>
    		</td>
            
        </tr>
    </table>

        
    <table cellspacing="0" cellpadding="0" width="945" style="margin-top: 5px;">
        <tr>
            <td style="text-align:left">
            <span class="label_solicitacao"><label id="label_agenda_observacao">Descrição:<span id="req">*</span></label></span>
            <br>
            <textarea name="descricao_agendamento" id="descricao_agendamento" style="margin-top: 2px; width: 945px; height: 100px;"></textarea>
            </td>    
        </tr>
    </table>
</div>


<div class="div_solicitacao_linhas3">
<table cellspacing="0" cellpadding="0" width="100%">
  <tr>

    <td>
    
    <input type="button" name="button" id="button" value="Gravar dados" class="botao_geral" style="width: 150px">
    <input type="reset" name="button" id="button" value="Limpar dados" class="botao_geral" style="width: 150px"> 
	
	<a href="painel/prospeccao/tabela_contador.php?no_new=1" target="_blank" class="botao_geral" style="width: 150px;">Contadores</a>
	
	<a href="painel/prospeccao/tabela_concorrente.php?no_new=1" target="_blank" class="botao_geral" style="width: 150px;">Concorrentes</a>
    
	<input type="hidden" name="MM_update" value="prospeccao" />
    <input name="id_prospeccao" type="hidden" value="" />
	</td>

  </tr>
</table>
</div>

</form>

<div class="div_solicitacao_linhas2">*campos com preenchimento obrigatório</div>

</body>
</html>
<?php
mysql_free_result($usuario);
mysql_free_result($geral_tipo_ramo_atividade);
mysql_free_result($prospeccao_responsavel);
mysql_free_result($cliente_antigo_listar);
mysql_free_result($usuarios_geral_tipo_praca_executor);
?>