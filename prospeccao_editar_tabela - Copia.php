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

if($praca_status == 0){ header("Location: painel/index.php"); exit; } 

// prospeccao
$colname_prospeccao = "-1";
if (isset($_GET['id_prospeccao'])) {
  $colname_prospeccao = $_GET['id_prospeccao'];
}
mysql_select_db($database_conexao, $conexao);
$query_prospeccao = sprintf("
SELECT *, 
(SELECT nome FROM usuarios WHERE usuarios.IdUsuario = prospeccao.id_usuario_responsavel) as usuario_responsavel 
FROM prospeccao 
WHERE id = %s", 
GetSQLValueString($colname_prospeccao, "int"));
$prospeccao = mysql_query($query_prospeccao, $conexao) or die(mysql_error());
$row_prospeccao = mysql_fetch_assoc($prospeccao);
$totalRows_prospeccao = mysql_num_rows($prospeccao);
// fim - prospeccao

// SELECT - prospeccao_concorrente
mysql_select_db($database_conexao, $conexao);
$query_prospeccao_concorrente = sprintf("
SELECT prospeccao_concorrente.* 
FROM prospeccao_concorrente 
WHERE prospeccao_concorrente.id = %s", GetSQLValueString($row_prospeccao['id_concorrente'], "int"));
$prospeccao_concorrente = mysql_query($query_prospeccao_concorrente, $conexao) or die(mysql_error());
$row_prospeccao_concorrente = mysql_fetch_assoc($prospeccao_concorrente);
$totalRows_prospeccao_concorrente = mysql_num_rows($prospeccao_concorrente);
// fim - SELECT - prospeccao_concorrente

// SELECT - prospeccao_contador
mysql_select_db($database_conexao, $conexao);
$query_prospeccao_contador = sprintf("
SELECT prospeccao_contador.* 
FROM prospeccao_contador 
WHERE prospeccao_contador.id = %s", GetSQLValueString($row_prospeccao['id_contador'], "int"));
$prospeccao_contador = mysql_query($query_prospeccao_contador, $conexao) or die(mysql_error());
$row_prospeccao_contador = mysql_fetch_assoc($prospeccao_contador);
$totalRows_prospeccao_contador = mysql_num_rows($prospeccao_contador);
// fim - SELECT - prospeccao_contador

// acessso --------------------------------------------------------------------------------------------------------------------------------------------------------------
$acesso = 0;
if(
	(
		($row_prospeccao['status_flag'] != "f") and 
		(
			(
			 ($row_usuario['nivel_prospeccao'] == 1 and $row_usuario['praca'] == $row_prospeccao['praca']) or 
			 ($row_usuario['nivel_prospeccao'] == 2 and $row_usuario['praca'] == $row_prospeccao['praca']) or 
			 ($row_usuario['nivel_prospeccao'] == 3 and $row_usuario['praca'] == $row_prospeccao['praca']) or 
			 ($row_usuario['controle_prospeccao'] == "Y") or 
			 $row_prospeccao['id_usuario_responsavel'] == $row_usuario['IdUsuario'] or 
			 $row_usuario['praca'] == $row_prospeccao['praca']
			 )
		)
	)or(
		($row_prospeccao['status_flag'] == "f" and $_GET['situacao']=="editar") and 
		(
			(
			 ($row_usuario['nivel_prospeccao'] == 1 and $row_usuario['praca'] == $row_prospeccao['praca']) or 
			 ($row_usuario['nivel_prospeccao'] == 2 and $row_usuario['praca'] == $row_prospeccao['praca']) or 
			 ($row_usuario['nivel_prospeccao'] == 3 and $row_usuario['praca'] == $row_prospeccao['praca']) or 
			 $row_usuario['controle_prospeccao'] == "Y" or 
			 $row_prospeccao['id_usuario_responsavel'] == $row_usuario['IdUsuario'] or 
			 $row_usuario['praca'] == $row_prospeccao['praca']
			 )
		)
	)
){

	$acesso = 1; // autorizado

}  else {
	
	$acesso = 0; // não autorizado
	
}

if($acesso==0){
	//echo "Acesso não autorizado !";
	echo "<script>window.top.location = 'prospeccao.php?padrao=sim&".$prospeccao_padrao."';</script>";
	exit;
}
// fim - acesso ---------------------------------------------------------------------------------------------------------------------------------------------------------

// converter entrada de data em portugues para ingles
if ( isset($_POST['data_agendamento_inicio']) and $_POST['data_agendamento_inicio'] != "" ) {
	$data_agendamento_inicio_data_agendamento_inicio = substr($_POST['data_agendamento_inicio'],0,10);
	$data_agendamento_inicio_hora = substr($_POST['data_agendamento_inicio'],10,9);
	$_POST['data_agendamento_inicio'] = implode("-",array_reverse(explode("-",$data_agendamento_inicio_data_agendamento_inicio))).$data_agendamento_inicio_hora;
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

// agenda (para editar/cancelar 'agenda')
$colname_agenda = "-1";
if (isset($_GET['id_prospeccao'])) {
  $colname_agenda = $_GET['id_prospeccao'];
}

$colname_agenda2 = "-1";
if (isset($_GET['id_agenda'])) {
  $colname_agenda2 = $_GET['id_agenda'];
}

mysql_select_db($database_conexao, $conexao);
$query_agenda = sprintf("
SELECT * 
FROM agenda 
WHERE id_prospeccao = %s and id_agenda = %s 
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
WHERE id_prospeccao = %s and status = 'a'
ORDER BY data ASC", GetSQLValueString(@$_GET['id_prospeccao'], "text"));
$agenda_agendado = mysql_query($query_agenda_agendado, $conexao) or die(mysql_error());
$row_agenda_agendado = mysql_fetch_assoc($agenda_agendado);
$totalRows_agenda_agendado = mysql_num_rows($agenda_agendado);
// fim - agenda_agendado

// prospeccao_contato
mysql_select_db($database_conexao, $conexao);
$query_prospeccao_contato =  sprintf("
								 SELECT prospeccao_contato.*, usuarios.nome AS usuarios_nome 
								 FROM prospeccao_contato 
								 LEFT JOIN usuarios ON prospeccao_contato.id_usuario_responsavel = usuarios.IdUsuario 
								 WHERE prospeccao_contato.id_prospeccao = %s 
								 ORDER BY prospeccao_contato.id DESC", 
								 GetSQLValueString($row_prospeccao['id'], "int"));
$prospeccao_contato = mysql_query($query_prospeccao_contato, $conexao) or die(mysql_error());
$row_prospeccao_contato = mysql_fetch_assoc($prospeccao_contato);
$totalRows_prospeccao_contato = mysql_num_rows($prospeccao_contato);
// fim - prospeccao_contato

// $botao_encerrar_status (ativa/desativa: Motivo: venda)
$botao_encerrar_status = 0;
if(
   $row_prospeccao['nome_razao_social']!="" and 
   $row_prospeccao['cpf_cnpj']!="" and 
   $row_prospeccao['rg_inscricao']!="" and 
   $row_prospeccao['cep']!="" and
   $row_prospeccao['endereco']!="" and 
   //$row_prospeccao['endereco_numero']!="" and
   $row_prospeccao['bairro']!="" and 
   $row_prospeccao['cidade']!="" and 
   $row_prospeccao['uf']!="" and 
   $row_prospeccao['telefone']!=""
){
	$botao_encerrar_status = 1;
}
// fim - $botao_encerrar_status (ativa/desativa: Motivo: venda)


if (((isset($_POST["MM_update"])) and ($_POST["MM_update"] == "form")) or ((isset($_GET["MM_update"])) and ($_GET["MM_update"] == "form"))) {

require_once('funcao_formata_data.php');
require_once('prospeccao_funcao_update.php');
require_once('funcao_consulta_modulo_array.php');

// interacao **********************************************************************************************************
$interacao = funcao_prospeccao_interacao($row_prospeccao['id'], @$_GET['interacao']);
if($interacao == 1 and @$_GET['interacao'] <> NULL){
	echo "<script>alert('Foi realizada alguma interação anterior a esta, assim, a ação atual não será gravada. Realize uma nova ação após a atualização da página.');</script>";
	$redirGoTo = "prospeccao_editar.php?id_prospeccao=".$_GET['id_prospeccao'];
	echo sprintf("<meta http-equiv=\"refresh\" content=\"0; url=%s\">", $redirGoTo);
	exit;
}
// fim - interacao ****************************************************************************************************

//---------------------------------------------------------------------------------------------------------------------------------------------------------------
// Encaminhar
if($_GET['acao']=="Encaminhar" and $_GET["resposta"] == ""){
	
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
	if($_GET['situacao']=="analisada"){ 
	
		$dados_prospeccao = array("interacao" => $row_prospeccao['interacao'] + 1,
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
	
	// em negociação
	if($_GET['situacao']=="em negociação"){
	
		$dados_prospeccao = array("interacao" => $row_prospeccao['interacao'] + 1,
				"situacao" => "analisada",
				"status" => "encaminhada para usuario responsavel",
				
				"id_usuario_responsavel" => $responsavel_id,			
				"usuario_responsavel_leu" => "",
	
				"encaminhamento_id" => $row_prospeccao['id_usuario_responsavel'],
				"encaminhamento_data_inicio" => "",
				"encaminhamento_data" => "",
				
				"status_devolucao" => "",
				"status_recusa" => ""
		);	
		
	}
	// fim - em negociação
	
	$dados_prospeccao_descricao = array(
			"id_prospeccao" => $row_prospeccao['id'],
			"id_usuario_responsavel" => $row_usuario['IdUsuario'],				
			"descricao" => "Encaminhada para novo responsável<br>Para: ".$responsavel_nome."<br>".$_POST['observacao'],
			"data" => date("Y-m-d H:i:s"),
			"tipo_postagem" => "Escolha de novo responsável"
	);
		
	mysql_free_result($usuario_selecionado);
	
	funcao_prospeccao_update($row_prospeccao['id'], $dados_prospeccao, $dados_prospeccao_descricao);
	
}
// fim - Encaminhar
//---------------------------------------------------------------------------------------------------------------------------------------------------------------

//---------------------------------------------------------------------------------------------------------------------------------------------------------------
// ACEITAR ---------------------------------------------------------------------------------------------------------------------------------------
//---------------------------------------------------------------------------------------------------------------------------------------------------------------
// aceitar ------------------------------------------------------------------------------------------------------------
if($_GET['acao']=="Aceitar" and $_GET["resposta"] == "" and $row_prospeccao['status_recusa']!="1"){ // aceitar

	// analisada
	if($_GET['situacao']=="analisada" and $row_prospeccao['status_devolucao']==""){
		
		$dados_prospeccao = array("interacao" => $row_prospeccao['interacao'] + 1,
				"situacao" => "em negociação",
				"status" => "pendente usuario responsavel",

				"encaminhamento_id" => "",
				"encaminhamento_data_inicio" => "",
				"encaminhamento_data" => "",
				
				"status_devolucao" => "",
				"status_recusa" => ""
		);	
		$dados_prospeccao_descricao = array(
				"id_prospeccao" => $row_prospeccao['id'],
				"id_usuario_responsavel" => $row_usuario['IdUsuario'],				
				"descricao" => $_POST['observacao'],
				"data" => date("Y-m-d H:i:s"),
				"tipo_postagem" => "Prospecção aceita por usuário responsável"
		);
		
		funcao_prospeccao_update($row_prospeccao['id'], $dados_prospeccao, $dados_prospeccao_descricao);

		// update 'agenda'
		$updateSQL_prospeccao_agenda = sprintf("
		UPDATE agenda 
		SET id_usuario_responsavel=%s 
		WHERE id_prospeccao=%s and status='a'", 
		GetSQLValueString($row_usuario['IdUsuario'], "int"),
		
		GetSQLValueString($row_prospeccao['id'], "int"));
		
		mysql_select_db($database_conexao, $conexao);
		$Result_prospeccao_agenda = mysql_query($updateSQL_prospeccao_agenda, $conexao) or die(mysql_error());
		// fim - update 'agenda'
	
	}
	// fim - analisada

}
// fim - aceitar -------------------------------------------------------------------------------------------------------

// aceitar recusa ------------------------------------------------------------------------------------------------------
if($_GET['acao']=="Aceitar" and $_GET["resposta"] == "" and $row_prospeccao['status_recusa']=="1"){

	// analisada
	if($_GET['situacao']=="analisada"){
		
		$dados_prospeccao = array("interacao" => $row_prospeccao['interacao'] + 1,
				"situacao" => "em negociação",
				"status" => "pendente usuario responsavel",
								
				"id_usuario_responsavel" => $row_prospeccao['encaminhamento_id'],
				
				"encaminhamento_id" => "",
				"encaminhamento_data_inicio" => "",
				"encaminhamento_data" => "",
				
				"status_devolucao" => "",
				"status_recusa" => ""
		);	
		$dados_prospeccao_descricao = array(
				"id_prospeccao" => $row_prospeccao['id'],
				"id_usuario_responsavel" => $row_usuario['IdUsuario'],				
				"descricao" => $_POST['observacao'],
				"data" => date("Y-m-d H:i:s"),
				"tipo_postagem" => "Recusa aceita"
		);
		
		funcao_prospeccao_update($row_prospeccao['id'], $dados_prospeccao, $dados_prospeccao_descricao);
		
	}
	// fim - analisada
	
}
// fim - aceitar recusa ------------------------------------------------------------------------------------------------
//---------------------------------------------------------------------------------------------------------------------------------------------------------------


//---------------------------------------------------------------------------------------------------------------------------------------------------------------
// RECUSAR ---------------------------------------------------------------------------------------------------------------------------------------
//---------------------------------------------------------------------------------------------------------------------------------------------------------------
// recusar ------------------------------------------------------------------------------------------------------------
if($_GET['acao']=="Recusar" and $_GET["resposta"] == "" and $row_prospeccao['status_recusa']!="1"){

	// analisada
	if($_GET['situacao']=="analisada" and $row_prospeccao['status_devolucao']==""){
			
		$dados_prospeccao = array("interacao" => $row_prospeccao['interacao'] + 1,
				"status" => "pendente usuario responsavel",
				"status_devolucao" => "",
				"status_recusa" => "1"
		);	
		$dados_prospeccao_descricao = array(
				"id_prospeccao" => $row_prospeccao['id'],
				"id_usuario_responsavel" => $row_usuario['IdUsuario'],				
				"descricao" => $_POST['observacao'],
				"data" => date("Y-m-d H:i:s"),
				"tipo_postagem" => "Prospecção recusada por usuário responsável"
		);
		
		funcao_prospeccao_update($row_prospeccao['id'], $dados_prospeccao, $dados_prospeccao_descricao);
		
	}
	// fim - analisada	
	
}
// fim - recusar ------------------------------------------------------------------------------------------------------

// recusar recusa -----------------------------------------------------------------------------------------------------
if($_GET['acao']=="Recusar" and $_GET["resposta"] == "" and $row_prospeccao['status_recusa']=="1"){
	
	// analisada
	if($_GET['situacao']=="analisada"){
			
		$dados_prospeccao = array("interacao" => $row_prospeccao['interacao'] + 1,
				"status" => "encaminhada para usuario responsavel",
				"status_devolucao" => "",
				"status_recusa" => ""
		);	
		$dados_prospeccao_descricao = array(
				"id_prospeccao" => $row_prospeccao['id'],
				"id_usuario_responsavel" => $row_usuario['IdUsuario'],				
				"descricao" => $_POST['observacao'],
				"data" => date("Y-m-d H:i:s"),
				"tipo_postagem" => "Recusa negada"
		);
		
		funcao_prospeccao_update($row_prospeccao['id'], $dados_prospeccao, $dados_prospeccao_descricao);
		
	}
	// fim - analisada		
	
}
// fim - recusar recusa -----------------------------------------------------------------------------------------------
//---------------------------------------------------------------------------------------------------------------------------------------------------------------


// Encerrar
if(($_GET['situacao']=="editar") and $_GET["acao"] == "Encerrar"){
	
	// se é uma venda
	if(@$_POST['baixa_tipo']=="v"){
		
		// converter entrada de data em portugues para ingles		
		if ( isset($_POST['baixa_contrato_data']) and $_POST['baixa_contrato_data'] != "" ) {
			$data_agendamento_data = substr($_POST['baixa_contrato_data'],0,10);
			$_POST['baixa_contrato_data'] = implode("-",array_reverse(explode("-",$data_agendamento_data)));
		} else {
			$_POST['baixa_contrato_data'] = "0000-00-00 00:00:00";
		}
		// fim - converter entrada de data em portugues para ingles
		
		// converter entrada de valor_venda
		if ( isset($_POST['valor_venda']) ) {
			$_POST['valor_venda'] = str_replace(',','.',$_POST['valor_venda']);
		}
		// fim - converter entrada de valor_venda
		
		// converter entrada de valor_treinamento
		if ( isset($_POST['valor_treinamento']) ) {
			$_POST['valor_treinamento'] = str_replace(',','.',$_POST['valor_treinamento']);
		}
		// fim - converter entrada de valor_treinamento
		
		// cliente antigo
		if($row_prospeccao['tipo_cliente']=="a"){
			
			$colname_contrato = $_POST['baixa_contrato'];
					
			// manutencao_dados
			mysql_select_db($database_conexao, $conexao);
			$query_manutencao_dados = sprintf("
			SELECT 
			geral_tipo_praca_executor.praca, 
			da37.codigo17, da37.cliente17, da37.status17, da37.obs17, da37.tpocont17, da37.visita17, da37.optacuv17, da37.versao17, da37.espmod17, versao17, espmod17, 
			geral_tipo_contrato.descricao as tpocont17_descricao,
			geral_tipo_visita.descricao as visita17_descricao
			
			FROM da37 
			INNER JOIN geral_tipo_praca_executor ON da37.vendedor17 = geral_tipo_praca_executor.IdExecutor
			INNER JOIN geral_tipo_contrato ON da37.tpocont17 = geral_tipo_contrato.IdTipoContrato
			INNER JOIN geral_tipo_visita ON da37.visita17 = geral_tipo_visita.IdTipoVisita
			
			WHERE da37.codigo17 = %s and da37.sr_deleted <> 'T'", 
			GetSQLValueString($colname_contrato, "text"));
			$manutencao_dados = mysql_query($query_manutencao_dados, $conexao) or die(mysql_error());
			$row_manutencao_dados = mysql_fetch_assoc($manutencao_dados);
			$totalRows_manutencao_dados = mysql_num_rows($manutencao_dados);
			// fim - manutencao_dados
			
			// empresa_dados
			mysql_select_db($database_conexao, $conexao);
			$query_empresa_dados = sprintf("
			SELECT codigo1, nome1, endereco1, bairro1, cidade1, uf1, telefone1, comercio1, cep1, ultcompra1, atraso1, status1, flag1
			FROM da01 
			WHERE codigo1 = %s and da01.sr_deleted <> 'T'", GetSQLValueString($row_manutencao_dados['cliente17'], "text"));
			$empresa_dados = mysql_query($query_empresa_dados, $conexao) or die(mysql_error());
			$row_empresa_dados = mysql_fetch_assoc($empresa_dados);
			$totalRows_empresa_dados = mysql_num_rows($empresa_dados);
			// fim - empresa_dados
			
			$empresa_nome = $row_empresa_dados['nome1'];
			$empresa_codigo = $row_empresa_dados['codigo1'];
			$contrato_praca = $row_manutencao_dados['praca'];
			
			mysql_free_result($manutencao_dados);
			mysql_free_result($empresa_dados);
			
		}
		// fim - cliente antigo
		
		// cliente novo
		if($row_prospeccao['tipo_cliente']=="n"){
			
			$colname_contrato = str_pad($row_parametros['ultimo_contrato']+1, 6, "0", STR_PAD_LEFT);
					 
			// insert - contrato
			$insertSQL_contrato = sprintf("INSERT INTO contrato (codigo17, datcont17) VALUES (%s, %s)",
		
			   GetSQLValueString($colname_contrato, "text"),
			   GetSQLValueString($_POST['baixa_contrato_data'], "date"));
		
			mysql_select_db($database_conexao, $conexao);
			$Result_contrato = mysql_query($insertSQL_contrato, $conexao) or die(mysql_error());
			// fim - insert - contrato
			
			// update 'parametros_contrato'
			$updateSQL_parametros_contrato = sprintf("UPDATE parametros
													 SET ultimo_contrato=%s", 
													 GetSQLValueString($colname_contrato, "text"));
			mysql_select_db($database_conexao, $conexao);
			$Result_parametros_contrato = mysql_query($updateSQL_parametros_contrato, $conexao) or die(mysql_error());
			// fim - update 'parametros_contrato'
			
			$empresa_nome = $row_prospeccao['nome_razao_social'];
			$empresa_codigo = '';
			$contrato_praca = $row_prospeccao['praca'];
			
		}
		// fim - cliente novo

		$implantacao_tempo = 0;
		if (isset($_POST['implantacao_tempo_adicional']) and $_POST['implantacao_tempo_adicional'] > 0) {
			$implantacao_tempo = $_POST['implantacao_tempo_adicional'];
		}
		
		// geral_tipo_modulo_atual (implantacao_tempo)
		$geral_tipo_modulo_atual_array = NULL;
		if (isset($_POST['venda_modulos'])) {
			foreach($_POST["venda_modulos"] as $venda_modulos){
				
				// geral_tipo_modulo_atual
				mysql_select_db($database_conexao, $conexao);
				$query_geral_tipo_modulo_atual = sprintf("
				SELECT * FROM geral_tipo_modulo 
				WHERE IdTipoModulo = %s 
				ORDER BY IdTipoModulo ASC
				", 
				GetSQLValueString($venda_modulos, "int"));
				$geral_tipo_modulo_atual = mysql_query($query_geral_tipo_modulo_atual, $conexao) or die(mysql_error());
				$row_geral_tipo_modulo_atual = mysql_fetch_assoc($geral_tipo_modulo_atual);
				$totalRows_geral_tipo_modulo_atual = mysql_num_rows($geral_tipo_modulo_atual);
				
				$geral_tipo_modulo_atual_array[] = array('IdTipoModulo' => $row_geral_tipo_modulo_atual['IdTipoModulo'], 'descricao' => $row_geral_tipo_modulo_atual['descricao']);
				$implantacao_tempo = $implantacao_tempo + $row_geral_tipo_modulo_atual['implantacao_tempo'];
				
				mysql_free_result($geral_tipo_modulo_atual);
				// fim - geral_tipo_modulo_atual
					
			}
		}
		// fim - geral_tipo_modulo_atual (implantacao_tempo)

		// insert - venda
		$insertSQL = sprintf("INSERT INTO venda (id_prospeccao, data_venda, data_inicio, empresa, codigo_empresa, praca, contrato, id_usuario_responsavel, status, situacao, tela, previsao_geral_inicio, previsao_geral, treinamento_tempo, implantacao_tempo, data_contrato, valor_venda, valor_treinamento) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)",
	
		   GetSQLValueString($row_prospeccao['id'], "int"),
		   GetSQLValueString(date('Y-m-d H:i:s'), "date"),
		   GetSQLValueString(date('Y-m-d H:i:s'), "date"),   
		   GetSQLValueString($empresa_nome, "text"),
		   GetSQLValueString($empresa_codigo, "text"),
		   GetSQLValueString($contrato_praca, "text"),
		   GetSQLValueString($colname_contrato, "text"),
		   GetSQLValueString($row_usuario['IdUsuario'], "int"),
		   GetSQLValueString("pendente usuario responsavel", "text"),
		   GetSQLValueString("documentação pendente", "text"),
		   GetSQLValueString("e", "text"),
		   GetSQLValueString(date('Y-m-d H:i:s'), "date"),
		   GetSQLValueString(date('Y-m-d H:i:s'), "date"), 
		   GetSQLValueString(@$_POST['treinamento_tempo'], "int"),
		   GetSQLValueString($implantacao_tempo, "int"),
		   GetSQLValueString($_POST['baixa_contrato_data'], "date"),
		   GetSQLValueString($_POST['valor_venda'], "double"),
		   GetSQLValueString($_POST['valor_treinamento'], "double"));
	
		mysql_select_db($database_conexao, $conexao);
		$Result1 = mysql_query($insertSQL, $conexao) or die(mysql_error());
		$IdVendaNova = mysql_insert_id();
		// fim - insert - venda
		
		// insert - venda_descricoes
		$insertSQL2  = sprintf("INSERT INTO venda_descricoes (id_venda, data, tipo_postagem, id_usuario_responsavel) VALUES (%s, %s, %s, %s)",
						   GetSQLValueString($IdVendaNova, "int"),
						   GetSQLValueString(date("Y-m-d H:i:s"), "date"),
						   GetSQLValueString("Nova venda", "text"),
						   GetSQLValueString($row_usuario['IdUsuario'], "int"));
		$Result2 = mysql_query($insertSQL2, $conexao) or die(mysql_error());
		// fim - insert - venda_descricoes
		
		// insert - venda_modulos
		if (isset($geral_tipo_modulo_atual_array)) {
			foreach($geral_tipo_modulo_atual_array as $venda_modulos){
				$insertSQL_modulo = sprintf("INSERT INTO venda_modulos (id_prospeccao, id_venda, id_modulo, modulo, data_criacao, contrato) VALUES (%s, %s, %s, %s, %s, %s)",
					
						   GetSQLValueString($row_prospeccao['id'], "int"),
						   GetSQLValueString($IdVendaNova, "text"),
						   GetSQLValueString($venda_modulos['IdTipoModulo'], "int"),
						   GetSQLValueString($venda_modulos['descricao'], "text"),
						   GetSQLValueString(date('Y-m-d H:i:s'), "date"),
						   GetSQLValueString($colname_contrato, "text"));
					
				mysql_select_db($database_conexao, $conexao);
				$Result_modulo = mysql_query($insertSQL_modulo, $conexao) or die(mysql_error());

			}
		}
		// fim - insert - venda_modulos

		$dados_prospeccao = array("interacao" => $row_prospeccao['interacao'] + 1,							  
				"situacao" => "venda realizada",
				"status" => "",
				"status_flag" => "f",
				
				"quantidade_agendado" => 0,
								
				"encaminhamento_id" => "",
				"encaminhamento_data_inicio" => "",
				"encaminhamento_data" => "",
				
				"solicita_agendamento" => "n",
				
				"status_devolucao" => "",
				"status_recusa" => "",
				
				"data_prospeccao_fim" => date('Y-m-d H:i:s'),
				
				"baixa_tipo" => @$_POST['baixa_tipo'],
				"baixa_contrato" => $colname_contrato,
				"baixa_id_venda" => $IdVendaNova,
				
				"parecer" => $_POST['observacao'],
	
				"final_situacao" => $row_prospeccao['situacao'],
				"final_status" => $row_prospeccao['status']
		);	
		$dados_prospeccao_descricao = array(
				"id_prospeccao" => $row_prospeccao['id'],
				"id_usuario_responsavel" => $row_usuario['IdUsuario'],
				"descricao" => "Encerramento de prospecção<br>".$_POST['observacao'],
				"data" => date("Y-m-d H:i:s"),
				"tipo_postagem" => "Encerrado por venda"
		);
		
		funcao_prospeccao_update($row_prospeccao['id'], $dados_prospeccao, $dados_prospeccao_descricao);

		// update 'agenda'
		$updateSQL_prospeccao_agenda = sprintf("
		UPDATE agenda 
		SET status=%s 
		WHERE id_prospeccao=%s and status='a'", 
		GetSQLValueString("f", "text"), 
		
		GetSQLValueString($row_prospeccao['id'], "int"));
		
		mysql_select_db($database_conexao, $conexao);
		$Result_prospeccao_agenda = mysql_query($updateSQL_prospeccao_agenda, $conexao) or die(mysql_error());
		// fim - update 'agenda'
		
		// update 'prospeccao_formulario'
		$updateSQL_prospeccao_prospeccao_formulario = sprintf("
		UPDATE prospeccao_formulario 
		SET situacao='encerrado' 
		WHERE id_prospeccao=%s and status_flag = 'a'", 												 
		GetSQLValueString($row_prospeccao['id'], "int"));
	
		mysql_select_db($database_conexao, $conexao);
		$Result_prospeccao_prospeccao_formulario = mysql_query($updateSQL_prospeccao_prospeccao_formulario, $conexao) or die(mysql_error());
		// fim - update 'prospeccao_formulario'

		// redireciona
		$updateGoTo = "prospeccao_editar_espelho.php?id_prospeccao=".$row_prospeccao['id']."&id_venda=".$IdVendaNova;
		echo sprintf("<meta http-equiv=\"refresh\" content=\"0; url=%s\">", $updateGoTo);
		exit;
		// fim - redireciona
		
	}
	// fim - se é uma venda	
	
	// se é uma perda
	if(@$_POST['baixa_tipo']=="p"){
		
		$dados_prospeccao = array("interacao" => $row_prospeccao['interacao'] + 1,							  
				"situacao" => "venda perdida",
				"status" => "",
				"status_flag" => "f",
				
				"quantidade_agendado" => 0,
								
				"encaminhamento_id" => "",
				"encaminhamento_data_inicio" => "",
				"encaminhamento_data" => "",
				
				"solicita_agendamento" => "n",
				
				"status_devolucao" => "",
				"status_recusa" => "",
				
				"data_prospeccao_fim" => date('Y-m-d H:i:s'),
				
				"baixa_tipo" => @$_POST['baixa_tipo'],
				"baixa_perda_motivo" => $_POST['baixa_perda_motivo'],
				"baixa_perda_data" => date('Y-m-d H:i:s'),
				"baixa_perda_recurso" => @$_POST['baixa_perda_recurso'],
				"baixa_perda_recurso_solicitacao_existe" => @$_POST['baixa_perda_recurso_solicitacao_existe'],
				"baixa_perda_recurso_solicitacao_verificada" => @$_POST['baixa_perda_recurso_solicitacao_verificada'],
				"baixa_perda_recurso_solicitacao_sugestao" => @$_POST['baixa_perda_recurso_solicitacao_sugestao'],
				"baixa_perda_concorrencia_programa" => @$_POST['baixa_perda_concorrencia_programa'],
				"baixa_perda_concorrencia_fator" => @$_POST['baixa_perda_concorrencia_fator'],
				
				"parecer" => $_POST['observacao'],
	
				"final_situacao" => $row_prospeccao['situacao'],
				"final_status" => $row_prospeccao['status']
		);	
		$dados_prospeccao_descricao = array(
				"id_prospeccao" => $row_prospeccao['id'],
				"id_usuario_responsavel" => $row_usuario['IdUsuario'],
				"descricao" => "Encerramento de prospecção<br>".$_POST['observacao'],
				"data" => date("Y-m-d H:i:s"),
				"tipo_postagem" => "Encerrado por perda"
		);
		
		funcao_prospeccao_update($row_prospeccao['id'], $dados_prospeccao, $dados_prospeccao_descricao);
			
		
		// delete 'prospeccao_perda_participacao'
		$deleteSQL_prospeccao_perda_participacao = sprintf("
		DELETE FROM prospeccao_perda_participacao 
		WHERE id_prospeccao=%s", 
		GetSQLValueString($row_prospeccao['id'], "int"));
		
		mysql_select_db($database_conexao, $conexao);
		$Result_delete_prospeccao_perda_participacao = mysql_query($deleteSQL_prospeccao_perda_participacao, $conexao) or die(mysql_error());
		// fim - delete 'prospeccao_perda_participacao'
		
		// perguntas fechadas
		foreach($_POST['opcao'] as $pergunta_key => $pergunta_array){
			
			foreach($pergunta_array as $opcao_atual){
				
				// insert
				$insertSQL_prospeccao_perda_participacao = sprintf("
				INSERT INTO prospeccao_perda_participacao 
				(id_prospeccao, IdProspeccaoPerdaPergunta, IdProspeccaoPerdaResposta, campo_texto, data) 
				VALUES (%s, %s, %s, %s, %s)", 
				GetSQLValueString($row_prospeccao['id'], "int"),
				GetSQLValueString($pergunta_key, "int"), 
				GetSQLValueString($opcao_atual, "int"), 
				GetSQLValueString(NULL, "text"), 
				GetSQLValueString(date('Y-m-d H:i:s'), "date"));	
				mysql_select_db($database_conexao, $conexao);
				$Result_insert_prospeccao_perda_participacao = mysql_query($insertSQL_prospeccao_perda_participacao, $conexao) or die(mysql_error());
				// fim - insert

			}

			
		}
		// fim - perguntas fechadas

		// perguntas abertas
		foreach($_POST['campo_texto'] as $pergunta_key => $pergunta_atual){
			
			// insert
			$insertSQL_prospeccao_perda_participacao = sprintf("
			INSERT INTO prospeccao_perda_participacao 
			(id_prospeccao, IdProspeccaoPerdaPergunta, campo_texto, data) 
			VALUES (%s, %s, %s, %s)", 
			GetSQLValueString($row_prospeccao['id'], "int"),
			GetSQLValueString($pergunta_key, "int"), 
			GetSQLValueString($pergunta_atual, "text"), 
			GetSQLValueString(date('Y-m-d H:i:s'), "date"));	
			mysql_select_db($database_conexao, $conexao);
			$Result_insert_prospeccao_perda_participacao = mysql_query($insertSQL_prospeccao_perda_participacao, $conexao) or die(mysql_error());
			// fim - insert
			
		}
		// fim - perguntas abertas
		
		
		// update 'agenda'
		$updateSQL_prospeccao_agenda = sprintf("
		UPDATE agenda 
		SET status=%s 
		WHERE id_prospeccao=%s and status='a'", 
		GetSQLValueString("f", "text"), 
		
		GetSQLValueString($row_prospeccao['id'], "int"));
		
		mysql_select_db($database_conexao, $conexao);
		$Result_prospeccao_agenda = mysql_query($updateSQL_prospeccao_agenda, $conexao) or die(mysql_error());
		// fim - update 'agenda'
		
		// update 'prospeccao_formulario'
		$updateSQL_prospeccao_prospeccao_formulario = sprintf("
		UPDATE prospeccao_formulario 
		SET situacao='encerrado' 
		WHERE id_prospeccao=%s and status_flag = 'a'", 												 
		GetSQLValueString($row_prospeccao['id'], "int"));
	
		mysql_select_db($database_conexao, $conexao);
		$Result_prospeccao_prospeccao_formulario = mysql_query($updateSQL_prospeccao_prospeccao_formulario, $conexao) or die(mysql_error());
		// fim - update 'prospeccao_formulario'
		
	}
	// fim - se é uma perda
	
	// se é vazio
	if(@$_POST['baixa_tipo']==""){

		$dados_prospeccao = array("interacao" => $row_prospeccao['interacao'] + 1,
				"praca" => $row_prospeccao['praca']
		);	
		$dados_prospeccao_descricao = array(
				"id_prospeccao" => $row_prospeccao['id'],
				"id_usuario_responsavel" => $row_usuario['IdUsuario'],
				"descricao" => $_POST['observacao'],
				"data" => date("Y-m-d H:i:s"),
				"tipo_postagem" => "Tentativa de encerramento"
		);
		funcao_prospeccao_update($row_prospeccao['id'], $dados_prospeccao, $dados_prospeccao_descricao);

	}
	// fim - se é vazio

}
// fim - Encerrar
//---------------------------------------------------------------------------------------------------------------------------------------------------------------
// Cancelar
if(($_GET['situacao']=="editar") and $_GET["acao"] == "Cancelar"){
	
	$dados_prospeccao = array("interacao" => $row_prospeccao['interacao'] + 1,
			"situacao" => "cancelada",
			"status" => "",
			"status_flag" => "f",
			
			"quantidade_agendado" => 0,
			
			"encaminhamento_id" => "",
			"encaminhamento_data_inicio" => "",
			"encaminhamento_data" => "",
			
			"solicita_agendamento" => "n",
			
			"status_devolucao" => "",
			"status_recusa" => "",
			
			"data_prospeccao_fim" => date('Y-m-d H:i:s'),

			"final_situacao" => $row_prospeccao['situacao'],
			"final_status" => $row_prospeccao['status']
	);	
	$dados_prospeccao_descricao = array(
			"id_prospeccao" => $row_prospeccao['id'],
			"id_usuario_responsavel" => $row_usuario['IdUsuario'],
			"descricao" => "Cancelamento de prospecção<br>".$_POST['observacao'],
			"data" => date("Y-m-d H:i:s"),
			"tipo_postagem" => "Cancelado"
	);
	
	funcao_prospeccao_update($row_prospeccao['id'], $dados_prospeccao, $dados_prospeccao_descricao);
	
	// update 'agenda'
	$updateSQL_prospeccao_agenda = sprintf("
										   UPDATE agenda 
										   SET status=%s 
										   WHERE id_prospeccao=%s and status='a'", 
										   GetSQLValueString("c", "text"), 
										   
										   GetSQLValueString($row_prospeccao['id'], "int"));
	
	mysql_select_db($database_conexao, $conexao);
	$Result_prospeccao_agenda = mysql_query($updateSQL_prospeccao_agenda, $conexao) or die(mysql_error());
	// fim - update 'agenda'
	
	// update 'prospeccao_formulario'
	$updateSQL_prospeccao_prospeccao_formulario = sprintf("
	UPDATE prospeccao_formulario 
	SET status_flag='c', situacao='cancelado' 
	WHERE id_prospeccao=%s", 												 
	GetSQLValueString($row_prospeccao['id'], "int"));

	mysql_select_db($database_conexao, $conexao);
	$Result_prospeccao_prospeccao_formulario = mysql_query($updateSQL_prospeccao_prospeccao_formulario, $conexao) or die(mysql_error());
	// fim - update 'prospeccao_formulario'
	
}
// fim - Cancelar
//---------------------------------------------------------------------------------------------------------------------------------------------------------------
// Estornar
if(($_GET['situacao']=="editar") and $_GET["acao"] == "Estornar"){
	
	$dados_prospeccao = array("interacao" => $row_prospeccao['interacao'] + 1,	
	
				"situacao" => $row_prospeccao['final_situacao'],
				"status" => $row_prospeccao['final_satus'],
				"status_flag" => "a",
				
				"quantidade_agendado" => 0,
								
				"encaminhamento_id" => "",
				"encaminhamento_data_inicio" => "",
				"encaminhamento_data" => "",
				
				"solicita_agendamento" => "n",
				
				"status_devolucao" => "",
				"status_recusa" => "",
				
				"data_prospeccao_fim" => "",
				
				"baixa_tipo" => "",
				"baixa_perda_motivo" => "",
				"baixa_perda_data" => "",
				"baixa_perda_recurso" => "",
				"baixa_perda_recurso_solicitacao_existe" => "",
				"baixa_perda_recurso_solicitacao_verificada" => "",
				"baixa_perda_recurso_solicitacao_sugestao" => "",
				"baixa_perda_concorrencia_programa" => "",
				"baixa_perda_concorrencia_fator" => "",
				
				"parecer" => "",
	
				"final_situacao" => "",
				"final_status" => ""
				
	);	
	$dados_prospeccao_descricao = array(
			"id_prospeccao" => $row_prospeccao['id'],
			"id_usuario_responsavel" => $row_usuario['IdUsuario'],
			"descricao" => "Estorno de prospecção<br>".$_POST['observacao'],
			"data" => date("Y-m-d H:i:s"),
			"tipo_postagem" => "Estornado"
	);
	
	funcao_prospeccao_update($row_prospeccao['id'], $dados_prospeccao, $dados_prospeccao_descricao);
		
}
// fim - Estornar
//---------------------------------------------------------------------------------------------------------------------------------------------------------------
// Agendamento / Finalizar agendamento
if(($_GET['situacao']=="editar") and ($_GET["acao"] == "Agendamento" or $_GET["acao"] == "Finalizar agendamento")){
	
	// caso exista agendamento em aberto ---------------------
	if($row_agenda_agendado > 0){
		
		$dados_prospeccao = array(
		"interacao" => $row_prospeccao['interacao'] + 1,
	
		"nivel_interesse" => $_POST['nivel_interesse'],
		"proposta_valor" => $_POST['proposta_valor'],
		"proposta_recursos" => $_POST['proposta_recursos'],
		"proposta_validade" => $_POST['proposta_validade'],

		"quantidade_agendado" => '0', 
		"situacao" => 'em negociação',
		"status" => 'aguardando agendamento',
		"status_flag" => "a"
	
		);	
		$dados_prospeccao_descricao = array(
				"id_prospeccao" => $row_prospeccao['id'],
				"id_usuario_responsavel" => $row_usuario['IdUsuario'],				
				"descricao" => "Código agendamento: ".$row_agenda['id_agenda']."<br>Data Inicial do agendamento: ".date('d-m-Y  H:i:s', strtotime($row_agenda['data_inicio']))."<br>Data Final do agendamento: ".date('d-m-Y  H:i:s', strtotime($row_agenda['data']))."<br>Descrição: ".$row_agenda['descricao']."<br><br>",			
				"data" => date("Y-m-d H:i:s"),
				"tipo_postagem" => "Agendamento finalizado"
		);
		funcao_prospeccao_update($row_prospeccao['id'], $dados_prospeccao, $dados_prospeccao_descricao);

		if ( isset($_POST['data_atendimento']) and $_POST['data_atendimento'] != "" ) {
			$data_atendimento_data = substr($_POST['data_atendimento'],0,10);
			$_POST['data_atendimento'] = implode("-",array_reverse(explode("-",$data_atendimento_data)));
		} else {
			$_POST['data_atendimento'] = "0000-00-00";
		}

		// update 'agenda'
		$updateSQL_prospeccao_agenda = sprintf("
		UPDATE agenda 
		SET status='f', 
		prospeccao_id_usuario_responsavel_final=%s, 
		prospeccao_data_atendimento=%s, 
		prospeccao_hora_atendimento_inicio=%s, 
		prospeccao_hora_atendimento_fim=%s, 
		prospeccao_tempo_gasto=%s, 
		prospeccao_receptor=%s 
		
		WHERE id_prospeccao=%s and status = 'a'", 
		GetSQLValueString($_POST['agenda_id_usuario_responsavel_final'], "int"), 
		GetSQLValueString($_POST['data_atendimento'], "text"),
		GetSQLValueString($_POST['hora_atendimento_inicio'], "text"),
		GetSQLValueString($_POST['hora_atendimento_fim'], "text"),
		GetSQLValueString($_POST['atendimento_tempo_gasto'], "text"),
		GetSQLValueString($_POST['atendimento_receptor'], "text"),
												 
		GetSQLValueString($row_prospeccao['id'], "int"));

		mysql_select_db($database_conexao, $conexao);
		$Result_prospeccao_agenda = mysql_query($updateSQL_prospeccao_agenda, $conexao) or die(mysql_error());
		// fim - update 'agenda'
	
	}
	// fim - caso exista agendamento em aberto ---------------
		
	// update 'prospeccao_formulario'
	$updateSQL_prospeccao_prospeccao_formulario = sprintf("
	UPDATE prospeccao_formulario 
	SET situacao='encerrado' 
	WHERE id_prospeccao=%s and status_flag = 'a'", 												 
	GetSQLValueString($row_prospeccao['id'], "int"));

	mysql_select_db($database_conexao, $conexao);
	$Result_prospeccao_prospeccao_formulario = mysql_query($updateSQL_prospeccao_prospeccao_formulario, $conexao) or die(mysql_error());
	// fim - update 'prospeccao_formulario'
	
	// acao_agenda: 'a' (Novo Agendamento)
	if(@$_POST['acao_agenda']=="a"){
		
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
		
		$dados_prospeccao = array("interacao" => $row_prospeccao['interacao'] + 1,
				"situacao" => 'em negociação',
				"status" => $_POST['status'],
				"status_flag" => "a",
				
				"id_usuario_responsavel" => $row_usuario_selecionado['IdUsuario'],
				"usuario_responsavel_leu" => "",
			
				"solicita_agendamento" => "n",
				"quantidade_agendado" => '1'
		);	
		$dados_prospeccao_descricao = array(
				"id_prospeccao" => $row_prospeccao['id'],
				"id_usuario_responsavel" => $row_usuario['IdUsuario'],				
				"descricao" => "Data Inicial do agendamento: ".date('d-m-Y  H:i:s', strtotime($_POST['data_agendamento_inicio']))."<br>Data Final do agendamento: ".date('d-m-Y  H:i:s', strtotime($_POST['data_agendamento']))."<br>Descrição: ".$_POST['descricao_agendamento']."<br><br>".$_POST['observacao'],
				"data" => date("Y-m-d H:i:s"),
				"tipo_postagem" => "Agendamento"
		);
		funcao_prospeccao_update($row_prospeccao['id'], $dados_prospeccao, $dados_prospeccao_descricao);
		
		mysql_free_result($usuario_selecionado);
		
		// insert 'agenda'
		$insertSQL_prospeccao_agenda = sprintf("
		INSERT INTO agenda (id_prospeccao, id_usuario_responsavel, data_inicio, data, data_criacao, status, prospeccao_agenda_tipo, descricao) 
		VALUES (%s, %s, %s, %s, %s, %s, %s, %s)",
		GetSQLValueString($row_prospeccao['id'], "int"),
		GetSQLValueString($row_usuario_selecionado['IdUsuario'], "int"),
		GetSQLValueString($_POST['data_agendamento_inicio'], "date"),
		GetSQLValueString($_POST['data_agendamento'], "date"),
		GetSQLValueString(date("Y-m-d H:i:s"), "date"),
		GetSQLValueString("a", "text"), 
		GetSQLValueString($_POST['prospeccao_agenda_tipo'], "text"), 
		GetSQLValueString($_POST['descricao_agendamento'], "text"));
		
		mysql_select_db($database_conexao, $conexao);
		$Result_prospeccao_agenda = mysql_query($insertSQL_prospeccao_agenda, $conexao) or die(mysql_error());
		// fim - insert 'agenda'
		
		$id_agenda_novo = mysql_insert_id(); // pega o numero do ultimo prospeccao
		
		if(isset($_POST['prospeccao_agenda_tipo']) and $_POST['prospeccao_agenda_tipo'] == 1) { // Visita
					
			// insert - prospeccao_formulario
			$insertSQL_formulario = sprintf("
			INSERT INTO prospeccao_formulario (id_prospeccao, id_agenda, data, empresa, codigo_empresa, contrato, praca, id_usuario_responsavel, prospeccao_agenda_tipo, status_flag, situacao) 
			VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)",
			GetSQLValueString($row_prospeccao['id'], "int"),	
			GetSQLValueString($id_agenda_novo, "int"), 																																																																																																											
			GetSQLValueString(date("Y-m-d H:i:s"), "date"),
			GetSQLValueString($row_prospeccao['nome_razao_social'], "text"),
			GetSQLValueString($row_prospeccao['codigo_empresa'], "text"),
			GetSQLValueString($row_prospeccao['contrato'], "text"),
			GetSQLValueString($row_usuario['praca'], "text"),
			GetSQLValueString($row_usuario['IdUsuario'], "int"),
			GetSQLValueString(1, "int"),
			GetSQLValueString('a', "text"),
			GetSQLValueString('autorizado', "text"));
			
			mysql_select_db($database_conexao, $conexao);
			$Result_formulario = mysql_query($insertSQL_formulario, $conexao) or die(mysql_error());
			// fim - insert - prospeccao_formulario
		
		}
		
	}
	// fim - acao_agenda: 'a' (Novo Agendamento)
	
	// acao_agenda: 's' (Solicitar agendamento)
	if(@$_POST['acao_agenda']=="s"){
		
		$dados_prospeccao = array("interacao" => $row_prospeccao['interacao'] + 1,
			"situacao" => "solicitado agendamento",
			"status" => "aguardando agendamento",
			
			"previsao_geral_inicio" => date("Y-m-d H:i:s"),
			"previsao_geral" => date("Y-m-d H:i:s"),
			
			"encaminhamento_id" => "",
			"encaminhamento_data_inicio" => "",
			"encaminhamento_data" => "",
			
			"status_devolucao" => "",
			"status_recusa" => "",
			
			"solicita_agendamento" => "s",
			
			"quantidade_agendado" => '0'
		);	
		$dados_prospeccao_descricao = array(
			"id_prospeccao" => $row_prospeccao['id'],
			"id_usuario_responsavel" => $row_usuario['IdUsuario'],
			"descricao" => $_POST['observacao'],
			"data" => date("Y-m-d H:i:s"),
			"tipo_postagem" => "Solicitação de agendamento"
		);
		funcao_prospeccao_update($row_prospeccao['id'], $dados_prospeccao, $dados_prospeccao_descricao);
	
	}
	// fim - acao_agenda: 's' (Solicitar agendamento)
	
}
// fim - Agendamento / Finalizar agendamento
//---------------------------------------------------------------------------------------------------------------------------------------------------------------
// Reagendar
if(($_GET['situacao']=="editar") and $_GET["acao"] == "Reagendar"){
	
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

	$dados_prospeccao = array("interacao" => $row_prospeccao['interacao'] + 1,
			"status" => $_POST['status'],
			"status_flag" => "a",
			
			"id_usuario_responsavel" => $row_usuario_selecionado['IdUsuario'],
			"usuario_responsavel_leu" => ""
	);	
	$dados_prospeccao_descricao = array(
			"id_prospeccao" => $row_prospeccao['id'],
			"id_usuario_responsavel" => $row_usuario['IdUsuario'],				
			"descricao" => "Código agendamento: ".$row_agenda['id_agenda']."<br>Data Inicial do agendamento: ".date('d-m-Y  H:i:s', strtotime($_POST['data_agendamento_inicio']))."<br>Data Final do agendamento: ".date('d-m-Y  H:i:s', strtotime($_POST['data_agendamento']))."<br>Descrição: ".$_POST['descricao_agendamento']."<br><br>".$_POST['observacao'],			
			"data" => date("Y-m-d H:i:s"),
			"tipo_postagem" => "Agendamento alterado"
	);
	funcao_prospeccao_update($row_prospeccao['id'], $dados_prospeccao, $dados_prospeccao_descricao);
		
	// update 'agenda'
	$updateSQL_prospeccao_agenda = sprintf("
	UPDATE agenda 
	SET id_usuario_responsavel=%s, data_inicio=%s, data=%s, descricao=%s, status=%s 
	WHERE id_prospeccao=%s and id_agenda=%s", 
	GetSQLValueString($row_usuario_selecionado['IdUsuario'], "int"),
	GetSQLValueString($_POST['data_agendamento_inicio'], "date"),
	GetSQLValueString($_POST['data_agendamento'], "date"),  
	GetSQLValueString($_POST['descricao_agendamento'], "text"), 
	GetSQLValueString("a", "text"), 
	
	GetSQLValueString($row_prospeccao['id'], "int"), 
	GetSQLValueString($row_agenda['id_agenda'], "int"));
	
	mysql_select_db($database_conexao, $conexao);
	$Result_prospeccao_agenda = mysql_query($updateSQL_prospeccao_agenda, $conexao) or die(mysql_error());
	// fim - update 'agenda'

	mysql_free_result($usuario_selecionado);
	
}
// fim - Reagendar
//---------------------------------------------------------------------------------------------------------------------------------------------------------------
// Solicitar agendamento
if(($_GET['situacao']=="editar") and $_GET["acao"] == "Solicitar agendamento"){

	$dados_prospeccao = array("interacao" => $row_prospeccao['interacao'] + 1,
			"situacao" => "solicitado agendamento",
			"status" => "aguardando agendamento",
			
			"previsao_geral_inicio" => date("Y-m-d H:i:s"),
			"previsao_geral" => date("Y-m-d H:i:s"),
			
			"encaminhamento_id" => "",
			"encaminhamento_data_inicio" => "",
			"encaminhamento_data" => "",
			
			"status_devolucao" => "",
			"status_recusa" => "",
			
			"solicita_agendamento" => "s",
			
			"quantidade_agendado" => '0'
	);	
	$dados_prospeccao_descricao = array(
			"id_prospeccao" => $row_prospeccao['id'],
			"id_usuario_responsavel" => $row_usuario['IdUsuario'],
			"descricao" => $_POST['observacao'],
			"data" => date("Y-m-d H:i:s"),
			"tipo_postagem" => "Solicitação de agendamento"
	);
	funcao_prospeccao_update($row_prospeccao['id'], $dados_prospeccao, $dados_prospeccao_descricao);

	// caso exista agendamento em aberto
	if($row_agenda_agendado > 0){
		
		// update 'agenda'
		$updateSQL_prospeccao_agenda = sprintf("UPDATE agenda 
											   SET status='f'
											   WHERE id_prospeccao=%s and status = 'a'",												 
											   GetSQLValueString($row_prospeccao['id'], "int"));
		
		mysql_select_db($database_conexao, $conexao);
		$Result_prospeccao_agenda = mysql_query($updateSQL_prospeccao_agenda, $conexao) or die(mysql_error());
		// fim - update 'agenda'
	
	}
	// fim - caso exista agendamento em aberto
	
	// update 'prospeccao_formulario'
	$updateSQL_prospeccao_prospeccao_formulario = sprintf("
	UPDATE prospeccao_formulario 
	SET situacao='encerrado' 
	WHERE id_prospeccao=%s and status_flag = 'a'", 												 
	GetSQLValueString($row_prospeccao['id'], "int"));

	mysql_select_db($database_conexao, $conexao);
	$Result_prospeccao_prospeccao_formulario = mysql_query($updateSQL_prospeccao_prospeccao_formulario, $conexao) or die(mysql_error());
	// fim - update 'prospeccao_formulario'
		
}
// fim - Solicitar agendamento
//---------------------------------------------------------------------------------------------------------------------------------------------------------------
// Cancelar solicitação de agendamento
if(($_GET['situacao']=="editar") and $_GET["acao"] == "Cancelar solicitação de agendamento"){

	$dados_prospeccao = array("interacao" => $row_prospeccao['interacao'] + 1,
			"situacao" => "em negociação",
			"status" => "aguardando agendamento",
			
			"previsao_geral_inicio" => "",
			"previsao_geral" => "",
			
			"encaminhamento_id" => "",
			"encaminhamento_data_inicio" => "",
			"encaminhamento_data" => "",
			
			"solicita_agendamento" => "n",
			
			"quantidade_agendado" => '0',
			
			"status_devolucao" => "",
			"status_recusa" => ""

	);	
	$dados_prospeccao_descricao = array(
			"id_prospeccao" => $row_prospeccao['id'],
			"id_usuario_responsavel" => $row_usuario['IdUsuario'],
			"descricao" => $_POST['observacao'],
			"data" => date("Y-m-d H:i:s"),
			"tipo_postagem" => "Cancelamento de solicitação de agendamento"
	);
	funcao_prospeccao_update($row_prospeccao['id'], $dados_prospeccao, $dados_prospeccao_descricao);

}
// fim - Cancelar solicitação de agendamento
//---------------------------------------------------------------------------------------------------------------------------------------------------------------
// Contato
if(($_GET['situacao']=="editar") and $_GET["acao"] == "Contato"){

	// insert prospeccao_contato
	mysql_select_db($database_conexao, $conexao);
	$insertSQL_prospeccao_contato = sprintf("
	INSERT INTO prospeccao_contato (id_prospeccao, id_usuario_responsavel, data, responsavel, telefone, descricao) 
	VALUES (%s, %s, %s, %s, %s, %s)",
	GetSQLValueString($row_prospeccao['id'], "int"),
	GetSQLValueString($row_usuario['IdUsuario'], "int"),
	GetSQLValueString(date("Y-m-d H:i:s"), "date"),
	GetSQLValueString($_POST['contato_responsavel'], "text"),
	GetSQLValueString($_POST['contato_telefone'], "text"),
	GetSQLValueString($_POST['observacao'], "text"));
	
	mysql_select_db($database_conexao, $conexao);
	$Result_prospeccao_contato = mysql_query($insertSQL_prospeccao_contato, $conexao) or die(mysql_error());
	$id_prospeccao_contato = mysql_insert_id();
	// fim - insert prospeccao_contato
		
	$dados_prospeccao = array("interacao" => $row_prospeccao['interacao'] + 1,							  
			"status_flag" => $row_prospeccao['status_flag']
	);	
	$dados_prospeccao_descricao = array(
			"id_prospeccao" => $row_prospeccao['id'],
			"id_usuario_responsavel" => $row_usuario['IdUsuario'],
			"descricao" => "Contato: ".$_POST['contato_responsavel']."<br>".$_POST['observacao'],
			"data" => date("Y-m-d H:i:s"),
			"tipo_postagem" => "Novo contato"
	);
	
	funcao_prospeccao_update($row_prospeccao['id'], $dados_prospeccao, $dados_prospeccao_descricao);

}
// fim - Contato
//---------------------------------------------------------------------------------------------------------------------------------------------------------------
// Questionar
if(($_GET['situacao']=="editar") and $_GET["acao"] == "Questionar"){

	$dados_prospeccao = array("interacao" => $row_prospeccao['interacao'] + 1,
			"praca" => $row_prospeccao['praca']
	);	
	$dados_prospeccao_descricao = array(
			"id_prospeccao" => $row_prospeccao['id'],
			"id_usuario_responsavel" => $row_usuario['IdUsuario'],
			"descricao" => $_POST['observacao'],
			"data" => date("Y-m-d H:i:s"),
			"tipo_postagem" => "Questionamento"
	);
	funcao_prospeccao_update($row_prospeccao['id'], $dados_prospeccao, $dados_prospeccao_descricao);

}
// fim - Questionar
//---------------------------------------------------------------------------------------------------------------------------------------------------------------

//---------------------------------------------------------------------------------------------------------------------------------------------------------------
// ATENDIMENTO -----------------------------------------------------------------------------------------------------------------------------------------------
//---------------------------------------------------------------------------------------------------------------------------------------------------------------
// Iniciar atendimento
if ($_GET["acao"] == "Iniciar atendimento") {

	//print_r($_POST);
	//exit;

	$dados_prospeccao = array(
		"interacao" => $row_prospeccao['interacao'] + 1, 
		"atendimento" => "IniAte", 
		"atendimento_cliente" => $_POST['atendimento_cliente'], 
		"atendimento_IdUsuario" => $row_usuario['IdUsuario'], 
		"atendimento_data" => date("Y-m-d H:i:s")
	);
	$dados_prospeccao_descricao = array(
		"id_prospeccao" => $row_prospeccao['id'],
		"id_usuario_responsavel" => $row_usuario['IdUsuario'],
		"descricao" => "Pessoa/Cliente: " . $_POST['atendimento_cliente'] . "<br>" . $_POST['observacao'],
		"data" => date("Y-m-d H:i:s"),
		"tipo_postagem" => "Iniciar atendimento"
	);
	funcao_prospeccao_update($row_prospeccao['id'], $dados_prospeccao, $dados_prospeccao_descricao);

}
// fim - Iniciar atendimento
//---------------------------------------------------------------------------------------------------------------------------------------------------------------
// Finalizar atendimento
if ($_GET["acao"] == "Finalizar atendimento") {

	$dados_prospeccao = array(
		"interacao" => $row_prospeccao['interacao'] + 1, 
		"atendimento" => $_POST['atendimento_status'], 
		"atendimento_cliente" => $_POST['atendimento_cliente'], 
		"atendimento_texto" => $_POST['atendimento_texto'], 
		"atendimento_IdUsuario" => $row_usuario['IdUsuario'], 
		"atendimento_data" => date("Y-m-d H:i:s")
	);
	$dados_prospeccao_descricao = array(
		"id_prospeccao" => $row_prospeccao['id'],
		"id_usuario_responsavel" => $row_usuario['IdUsuario'],
		"descricao" => "Pessoa/Cliente: " . $_POST['atendimento_cliente'] . "<br>Status: " . $_POST['atendimento_status'] . "<br>Detalhes: " . $_POST['atendimento_texto'] . "<br>" . $_POST['observacao'],
		"data" => date("Y-m-d H:i:s"),
		"tipo_postagem" => "Finalizar atendimento"
	);
	funcao_prospeccao_update($row_prospeccao['id'], $dados_prospeccao, $dados_prospeccao_descricao);

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

	$dados_prospeccao = array(
		"interacao" => $row_prospeccao['interacao'] + 1, 
		"atendimento" => "SolCan", 
		"atendimento_cliente" => $_POST['atendimento_cliente'], 
		"atendimento_IdUsuario" => $row_usuario['IdUsuario'], 
		"atendimento_data" => date("Y-m-d H:i:s")
	);
	$dados_prospeccao_descricao = array(
		"id_prospeccao" => $row_prospeccao['id'],
		"id_usuario_responsavel" => $row_usuario['IdUsuario'],
		"descricao" => "Pessoa/Cliente: " . $_POST['atendimento_cliente'] . "<br>Local: " . $atendimento_local_label . "<br>Motivo: " . $_POST['atendimento_texto'] . "<br>" . $_POST['observacao'],
		"data" => date("Y-m-d H:i:s"),
		"tipo_postagem" => "Cancelar atendimento"
	);
	funcao_prospeccao_update($row_prospeccao['id'], $dados_prospeccao, $dados_prospeccao_descricao);

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
	
	$dados_prospeccao = array(
		"interacao" => $row_prospeccao['interacao'] + 1, 

		"atendimento" => "SolRea", 
		"atendimento_cliente" => $_POST['atendimento_cliente'], 
		"atendimento_previsao" => $_POST['atendimento_previsao'], 
		"atendimento_texto" => $_POST['atendimento_texto'], 
		"atendimento_IdUsuario" => $row_usuario['IdUsuario'], 
		"atendimento_data" => date("Y-m-d H:i:s")
	);
	$dados_prospeccao_descricao = array(
		"id_prospeccao" => $row_prospeccao['id'],
		"id_usuario_responsavel" => $row_usuario['IdUsuario'],
		"descricao" => "Pessoa/Cliente: " . $_POST['atendimento_cliente'] . "<br>Previsão: " . formataDataPTG($_POST['atendimento_previsao']) . "<br>Motivo: " . $_POST['atendimento_texto'] . "<br>" . $_POST['observacao'],
		"data" => date("Y-m-d H:i:s"),
		"tipo_postagem" => "Reagendar atendimento"
	);
	funcao_prospeccao_update($row_prospeccao['id'], $dados_prospeccao, $dados_prospeccao_descricao);

}
// fim - Reagendar atendimento
//---------------------------------------------------------------------------------------------------------------------------------------------------------------
//---------------------------------------------------------------------------------------------------------------------------------------------------------------

// Alterar
//---------------------------------------------------------------------------------------------------------------------------------------------------------------
// Alterar usuário responsável
if(($_GET['situacao']=="editar") and $_GET["acao"] == "Alterar usuário responsável"){

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

	$dados_prospeccao = array("interacao" => $row_prospeccao['interacao'] + 1,
			"id_usuario_responsavel" => $row_usuario_selecionado['IdUsuario'],
			"usuario_responsavel_leu" => ""
	);	
	$dados_prospeccao_descricao = array(
			"id_prospeccao" => $row_prospeccao['id'],
			"id_usuario_responsavel" => $row_usuario['IdUsuario'],
			"descricao" => "Foi alterado o usuário responsável<br>Anterior: ".$row_prospeccao['usuario_responsavel']." - Novo: ".$row_usuario_selecionado['nome']."<br>".$_POST['observacao'],
			"data" => date("Y-m-d H:i:s"),
			"tipo_postagem" => "Alteração de usuário responsável"
	);
	
	funcao_prospeccao_update($row_prospeccao['id'], $dados_prospeccao, $dados_prospeccao_descricao);

	// update 'agenda'
	$updateSQL_prospeccao_agenda = sprintf("
										   UPDATE agenda 
										   SET id_usuario_responsavel=%s 
										   WHERE id_prospeccao=%s and status='a'", 
										   GetSQLValueString($row_usuario_selecionado['IdUsuario'], "int"),
										   
										   GetSQLValueString($row_prospeccao['id'], "int"));
	
	mysql_select_db($database_conexao, $conexao);
	$Result_prospeccao_agenda = mysql_query($updateSQL_prospeccao_agenda, $conexao) or die(mysql_error());
	// fim - update 'agenda'
	
	mysql_free_result($usuario_selecionado);

}
// fim - Alterar usuário responsável
//---------------------------------------------------------------------------------------------------------------------------------------------------------------
// Alterar tipo de prospect
if(($_GET['situacao']=="editar") and $_GET["acao"] == "Alterar tipo de prospect"){

	if($row_prospeccao['ativo_passivo']=='a'){$ativo_passivo_anterior = "ativo";}
	if($row_prospeccao['ativo_passivo']=='p'){$ativo_passivo_anterior = "passivo";}

	if($_POST['ativo_passivo']=='a'){$ativo_passivo_novo = "ativo";}
	if($_POST['ativo_passivo']=='p'){$ativo_passivo_novo = "passivo";}
	
	$dados_prospeccao = array("interacao" => $row_prospeccao['interacao'] + 1,
			"ativo_passivo" => $_POST['ativo_passivo']
	);	
	$dados_prospeccao_descricao = array(
			"id_prospeccao" => $row_prospeccao['id'],
			"id_usuario_responsavel" => $row_usuario['IdUsuario'],
			"descricao" => "Foi alterado o Tipo de prospect<br>Tipo de prospect anterior: ".$ativo_passivo_anterior." - Novo Tipo de prospect: ".$ativo_passivo_novo."<br>".$_POST['observacao'],
			"data" => date("Y-m-d H:i:s"),
			"tipo_postagem" => "Alteração de tipo de prospect"
	);
	funcao_prospeccao_update($row_prospeccao['id'], $dados_prospeccao, $dados_prospeccao_descricao);
}
// fim - Alterar tipo de prospect
//---------------------------------------------------------------------------------------------------------------------------------------------------------------
// Alterar indicado por
if(($_GET['situacao']=="editar") and $_GET["acao"] == "Alterar indicado por"){

	
	$dados_prospeccao = array("interacao" => $row_prospeccao['interacao'] + 1,
			"indicado_por" => $_POST['indicado_por']
	);	
	$dados_prospeccao_descricao = array(
			"id_prospeccao" => $row_prospeccao['id'],
			"id_usuario_responsavel" => $row_usuario['IdUsuario'],
			"descricao" => "Foi alterado o Nome do indicador<br>Nome do indicador anterior: ".$row_prospeccao['indicado_por']." - Novo Nome do indicador: ".$_POST['indicado_por']."<br>".$_POST['observacao'],
			"data" => date("Y-m-d H:i:s"),
			"tipo_postagem" => "Alteração de indicado por"
	);
	funcao_prospeccao_update($row_prospeccao['id'], $dados_prospeccao, $dados_prospeccao_descricao);

}
// fim - Alterar indicado por
//---------------------------------------------------------------------------------------------------------------------------------------------------------------
// Alterar cliente
if(($_GET['situacao']=="editar") and $_GET["acao"] == "Alterar cliente"){

	
	$dados_prospeccao = array("interacao" => $row_prospeccao['interacao'] + 1,
			"nome_razao_social" => $_POST['nome_razao_social']
	);	
	$dados_prospeccao_descricao = array(
			"id_prospeccao" => $row_prospeccao['id'],
			"id_usuario_responsavel" => $row_usuario['IdUsuario'],
			"descricao" => "Foi alterado o cliente<br>Cliente anterior: ".$row_prospeccao['nome_razao_social']." - Novo cliente: ".$_POST['nome_razao_social']."<br>".$_POST['observacao'],
			"data" => date("Y-m-d H:i:s"),
			"tipo_postagem" => "Alteração de cliente"
	);
	funcao_prospeccao_update($row_prospeccao['id'], $dados_prospeccao, $dados_prospeccao_descricao);

}
// fim - Alterar cliente
//---------------------------------------------------------------------------------------------------------------------------------------------------------------
// Alterar nome fantasia
if(($_GET['situacao']=="editar") and $_GET["acao"] == "Alterar nome fantasia"){

	
	$dados_prospeccao = array("interacao" => $row_prospeccao['interacao'] + 1,
			"fantasia" => $_POST['fantasia']
	);	
	$dados_prospeccao_descricao = array(
			"id_prospeccao" => $row_prospeccao['id'],
			"id_usuario_responsavel" => $row_usuario['IdUsuario'],
			"descricao" => "Foi alterado o Nome fantasia<br>Nome fantasia anterior: ".$row_prospeccao['fantasia']." - Novo Nome fantasia: ".$_POST['fantasia']."<br>".$_POST['observacao'],
			"data" => date("Y-m-d H:i:s"),
			"tipo_postagem" => "Alteração de nome fantasia"
	);
	funcao_prospeccao_update($row_prospeccao['id'], $dados_prospeccao, $dados_prospeccao_descricao);

}
// fim - Alterar nome fantasia
//---------------------------------------------------------------------------------------------------------------------------------------------------------------
// Alterar cpf/cnpj
if(($_GET['situacao']=="editar") and $_GET["acao"] == "Alterar cpf/cnpj"){

	
	$dados_prospeccao = array("interacao" => $row_prospeccao['interacao'] + 1,
			"cpf_cnpj" => $_POST['cpf_cnpj']
	);	
	$dados_prospeccao_descricao = array(
			"id_prospeccao" => $row_prospeccao['id'],
			"id_usuario_responsavel" => $row_usuario['IdUsuario'],
			"descricao" => "Foi alterado o CPF/CNPJ<br>CPF/CNPJ anterior: ".$row_prospeccao['cpf_cnpj']." - Novo CPF/CNPJ: ".$_POST['cpf_cnpj']."<br>".$_POST['observacao'],
			"data" => date("Y-m-d H:i:s"),
			"tipo_postagem" => "Alteração de cpf/cnpj"
	);
	funcao_prospeccao_update($row_prospeccao['id'], $dados_prospeccao, $dados_prospeccao_descricao);

}
// fim - Alterar cpf/cnpj
//---------------------------------------------------------------------------------------------------------------------------------------------------------------
// Alterar rg/inscrição estadual
if(($_GET['situacao']=="editar") and $_GET["acao"] == "Alterar rg/inscrição estadual"){

	
	$dados_prospeccao = array("interacao" => $row_prospeccao['interacao'] + 1,
			"rg_inscricao" => $_POST['rg_inscricao']
	);	
	$dados_prospeccao_descricao = array(
			"id_prospeccao" => $row_prospeccao['id'],
			"id_usuario_responsavel" => $row_usuario['IdUsuario'],
			"descricao" => "Foi alterado o RG/Inscrição Estadual<br>RG/Inscrição Estadual anterior: ".$row_prospeccao['rg_inscricao']." - Novo RG/Inscrição Estadual: ".$_POST['rg_inscricao']."<br>".$_POST['observacao'],
			"data" => date("Y-m-d H:i:s"),
			"tipo_postagem" => "Alteração de rg/inscrição estadual"
	);
	funcao_prospeccao_update($row_prospeccao['id'], $dados_prospeccao, $dados_prospeccao_descricao);

}
// fim - Alterar rg/inscrição estadual
//---------------------------------------------------------------------------------------------------------------------------------------------------------------
// Alterar endereço
if(($_GET['situacao']=="editar") and $_GET["acao"] == "Alterar endereço"){

	
	$dados_prospeccao = array("interacao" => $row_prospeccao['interacao'] + 1,
			"cep" => $_POST['cep'],
			"endereco" => $_POST['endereco'],
			"endereco_numero" => $_POST['endereco_numero'],
			"endereco_complemento" => $_POST['endereco_complemento'],
			"bairro" => $_POST['bairro'],
			"cidade" => $_POST['cidade'],
			"uf" => $_POST['uf']
			
	);	
	$dados_prospeccao_descricao = array(
			"id_prospeccao" => $row_prospeccao['id'],
			"id_usuario_responsavel" => $row_usuario['IdUsuario'],
			"descricao" => "Foi alterado a Localização<br>Localização anterior: "." CEP: ".$row_prospeccao['cep']." Endereço: ".$row_prospeccao['endereco']." - N°: ".$row_prospeccao['endereco_numero']." - Complemento: ".$row_prospeccao['endereco_complemento']." - Bairro: ".$row_prospeccao['bairro']." - Cidade: ".$row_prospeccao['cidade']." - Estado: ".$row_prospeccao['uf']."<br>Nova Localização: "." CEP: ".$_POST['cep']." - Endereço: ".$_POST['endereco']." - N°: ".$_POST['endereco_numero']." - Complemento: ".$_POST['endereco_complemento']." - Bairro: ".$_POST['bairro']." - Cidade: ".$_POST['cidade']." - Estado: ".$_POST['uf']."<br>".$_POST['observacao'],
			"data" => date("Y-m-d H:i:s"),
			"tipo_postagem" => "Alteração de localização"
	);
	funcao_prospeccao_update($row_prospeccao['id'], $dados_prospeccao, $dados_prospeccao_descricao);
	
}
// fim - Alterar endereço
//---------------------------------------------------------------------------------------------------------------------------------------------------------------
// Alterar telefone
if(($_GET['situacao']=="editar") and $_GET["acao"] == "Alterar telefone"){

	
	$dados_prospeccao = array("interacao" => $row_prospeccao['interacao'] + 1,
			"telefone" => $_POST['telefone']
	);	
	$dados_prospeccao_descricao = array(
			"id_prospeccao" => $row_prospeccao['id'],
			"id_usuario_responsavel" => $row_usuario['IdUsuario'],
			"descricao" => "Foi alterado o Telefone<br>Telefone anterior: ".$row_prospeccao['telefone']." - Novo Telefone: ".$_POST['telefone']."<br>".$_POST['observacao'],
			"data" => date("Y-m-d H:i:s"),
			"tipo_postagem" => "Alteração de telefone"
	);
	funcao_prospeccao_update($row_prospeccao['id'], $dados_prospeccao, $dados_prospeccao_descricao);

}
// fim - Alterar telefone
//---------------------------------------------------------------------------------------------------------------------------------------------------------------
// Alterar celular
if(($_GET['situacao']=="editar") and $_GET["acao"] == "Alterar celular"){

	
	$dados_prospeccao = array("interacao" => $row_prospeccao['interacao'] + 1,
			"celular" => $_POST['celular']
	);	
	$dados_prospeccao_descricao = array(
			"id_prospeccao" => $row_prospeccao['id'],
			"id_usuario_responsavel" => $row_usuario['IdUsuario'],
			"descricao" => "Foi alterado o Celular<br>Celular anterior: ".$row_prospeccao['celular']." - Novo Celular: ".$_POST['celular']."<br>".$_POST['observacao'],
			"data" => date("Y-m-d H:i:s"),
			"tipo_postagem" => "Alteração de celular"
	);
	funcao_prospeccao_update($row_prospeccao['id'], $dados_prospeccao, $dados_prospeccao_descricao);

}
// fim - Alterar celular
//---------------------------------------------------------------------------------------------------------------------------------------------------------------
// Alterar reponsável por T.I.
if(($_GET['situacao']=="editar") and $_GET["acao"] == "Alterar responsável por T.I."){

	
	$dados_prospeccao = array("interacao" => $row_prospeccao['interacao'] + 1,
			"responsavel_por_ti" => $_POST['responsavel_por_ti']
	);	
	$dados_prospeccao_descricao = array(
			"id_prospeccao" => $row_prospeccao['id'],
			"id_usuario_responsavel" => $row_usuario['IdUsuario'],
			"descricao" => "Foi alterado o Responsável por T.I.<br>Responsável por T.I. anterior: ".$row_prospeccao['responsavel_por_ti']." - Novo Responsável por T.I.: ".$_POST['responsavel_por_ti']."<br>".$_POST['observacao'],
			"data" => date("Y-m-d H:i:s"),
			"tipo_postagem" => "Alteração de reponsável por T.I."
	);
	funcao_prospeccao_update($row_prospeccao['id'], $dados_prospeccao, $dados_prospeccao_descricao);

}
// fim - Alterar reponsável por T.I.
//---------------------------------------------------------------------------------------------------------------------------------------------------------------
// Alterar Ramo de atividade
if(($_GET['situacao']=="editar") and $_GET["acao"] == "Alterar Ramo de atividade"){
	
	$dados_prospeccao = array("interacao" => $row_prospeccao['interacao'] + 1,
			"ramo_de_atividade" => $_POST['ramo_de_atividade']
	);	
	$dados_prospeccao_descricao = array(
			"id_prospeccao" => $row_prospeccao['id'],
			"id_usuario_responsavel" => $row_usuario['IdUsuario'],
			"descricao" => "Foi alterado o Ramo de atividade<br>Ramo de atividade anterior: ".$row_prospeccao['ramo_de_atividade']." - Novo Ramo de atividade: ".$_POST['ramo_de_atividade']."<br>".$_POST['observacao'],
			"data" => date("Y-m-d H:i:s"),
			"tipo_postagem" => "Alteração de Ramo de atividade"
	);
	funcao_prospeccao_update($row_prospeccao['id'], $dados_prospeccao, $dados_prospeccao_descricao);

}
// fim - Alterar Ramo de atividade
//---------------------------------------------------------------------------------------------------------------------------------------------------------------
// Alterar enquadramento fiscal
if(($_GET['situacao']=="editar") and $_GET["acao"] == "Alterar enquadramento fiscal"){

	if($row_prospeccao['enquadramento_fiscal']==""){
		$enquadramento_fiscal_anterior = $row_prospeccao['enquadramento_fiscal_outro'];
	} else {
		$enquadramento_fiscal_anterior = $row_prospeccao['enquadramento_fiscal'];
	}
	
	if($_POST['enquadramento_fiscal']==""){
		$enquadramento_fiscal_novo = $_POST['enquadramento_fiscal_outro'];
	} else {
		$enquadramento_fiscal_novo = $_POST['enquadramento_fiscal'];
	}
	
	$dados_prospeccao = array("interacao" => $row_prospeccao['interacao'] + 1,
			"enquadramento_fiscal" => $_POST['enquadramento_fiscal'],
			"enquadramento_fiscal_outro" => $_POST['enquadramento_fiscal_outro']
	);	
	$dados_prospeccao_descricao = array(
			"id_prospeccao" => $row_prospeccao['id'],
			"id_usuario_responsavel" => $row_usuario['IdUsuario'],
			"descricao" => "Foi alterado o Enquadramento fiscal<br>Enquadramento fiscal anterior: ".$enquadramento_fiscal_anterior." - Novo Enquadramento fiscal: ".$enquadramento_fiscal_novo."<br>".$_POST['observacao'],
			"data" => date("Y-m-d H:i:s"),
			"tipo_postagem" => "Alteração de enquadramento fiscal"
	);
	funcao_prospeccao_update($row_prospeccao['id'], $dados_prospeccao, $dados_prospeccao_descricao);

}
// fim - Alterar enquadramento fiscal
//---------------------------------------------------------------------------------------------------------------------------------------------------------------
// Alterar informações fiscais
if(($_GET['situacao']=="editar") and $_GET["acao"] == "Alterar informações fiscais"){

	$exige_cupom_fiscal = 0;
	if (isset($_POST['exige_cupom_fiscal'])) {
		$exige_cupom_fiscal = 1;
	}
	if($row_prospeccao['exige_cupom_fiscal']=='1'){$exige_cupom_fiscal_anterior = "sim";}
	if($row_prospeccao['exige_cupom_fiscal']=='0'){$exige_cupom_fiscal_anterior = "não";}
	if($exige_cupom_fiscal=='1'){$exige_cupom_fiscal_novo = "sim";}
	if($exige_cupom_fiscal=='0'){$exige_cupom_fiscal_novo = "não";}


	$exige_nfe = 0;
	if (isset($_POST['exige_nfe'])) {
		$exige_nfe = 1;
	}
	if($row_prospeccao['exige_nfe']=='1'){$exige_nfe_anterior = "sim";}
	if($row_prospeccao['exige_nfe']=='0'){$exige_nfe_anterior = "não";}
	if($exige_nfe=='1'){$exige_nfe_novo = "sim";}
	if($exige_nfe=='0'){$exige_nfe_novo = "não";}

	
	$exige_nfce = 0;
	if (isset($_POST['exige_nfce'])) {
		$exige_nfce = 1;
	}
	if($row_prospeccao['exige_nfce']=='1'){$exige_nfce_anterior = "sim";}
	if($row_prospeccao['exige_nfce']=='0'){$exige_nfce_anterior = "não";}
	if($exige_nfce=='1'){$exige_nfce_novo = "sim";}
	if($exige_nfce=='0'){$exige_nfce_novo = "não";}


	$exige_mdfe = 0;
	if (isset($_POST['exige_mdfe'])) {
		$exige_mdfe = 1;
	}
	if($row_prospeccao['exige_mdfe']=='1'){$exige_mdfe_anterior = "sim";}
	if($row_prospeccao['exige_mdfe']=='0'){$exige_mdfe_anterior = "não";}
	if($exige_mdfe=='1'){$exige_mdfe_novo = "sim";}
	if($exige_mdfe=='0'){$exige_mdfe_novo = "não";}


	$exige_ctee = 0;
	if (isset($_POST['exige_ctee'])) {
		$exige_ctee = 1;
	}
	if($row_prospeccao['exige_ctee']=='1'){$exige_ctee_anterior = "sim";}
	if($row_prospeccao['exige_ctee']=='0'){$exige_ctee_anterior = "não";}
	if($exige_ctee=='1'){$exige_ctee_novo = "sim";}
	if($exige_ctee=='0'){$exige_ctee_novo = "não";}


	$exige_efd = 0;
	if (isset($_POST['exige_efd'])) {
		$exige_efd = 1;
	}
	if($row_prospeccao['exige_efd']=='1'){$exige_efd_anterior = "sim";}
	if($row_prospeccao['exige_efd']=='0'){$exige_efd_anterior = "não";}
	if($exige_efd=='1'){$exige_efd_novo = "sim";}
	if($exige_efd=='0'){$exige_efd_novo = "não";}

	$dados_prospeccao = array("interacao" => $row_prospeccao['interacao'] + 1,
			"exige_cupom_fiscal" => $exige_cupom_fiscal,
			"exige_nfe" => $exige_nfe,
			"exige_nfce" => $exige_nfce,
			"exige_mdfe" => $exige_mdfe,
			"exige_ctee" => $exige_ctee,
			"exige_efd" => $exige_efd,
			"exige_outro" => $_POST['exige_outro']
	);	
	$dados_prospeccao_descricao = array(
			"id_prospeccao" => $row_prospeccao['id'],
			"id_usuario_responsavel" => $row_usuario['IdUsuario'],
			"descricao" => "
			Foi alterado as informações fiscais<br>
			Informações anteriores: "."
			Exige Cupom Fiscal: ".$exige_cupom_fiscal_anterior." - 
			Exige NFE: ".$exige_nfe_anterior." - 
			Exige NFCE: ".$exige_nfce_anterior." - 
			Exige MDFE: ".$exige_mdfe_anterior." - 
			Exige CTEE: ".$exige_ctee_anterior." - 
			Exige EFD: ".$exige_efd_anterior." - 
			Exige outros: ".$row_prospeccao['exige_outro']."<br>
			
			Novas informações: "."
			Exige Cupom Fiscal: ".$exige_cupom_fiscal_novo." - 
			Exige NFE: ".$exige_nfe_novo." - 
			Exige NFCE: ".$exige_nfce_novo." - 
			Exige MDFE: ".$exige_mdfe_novo." - 
			Exige CTEE: ".$exige_ctee_novo." - 
			Exige EFD: ".$exige_efd_novo." - 
			Exige outros: ".$_POST['exige_outro']."
			<br>".$_POST['observacao'],
			"data" => date("Y-m-d H:i:s"),
			"tipo_postagem" => "Alteração de informações fiscais"
	);
	funcao_prospeccao_update($row_prospeccao['id'], $dados_prospeccao, $dados_prospeccao_descricao);

}
// fim - Alterar informações fiscais
//---------------------------------------------------------------------------------------------------------------------------------------------------------------
// Alterar contador
if(($_GET['situacao']=="editar") and $_GET["acao"] == "Alterar contador"){

	// busca prospeccao_contador_selecionado
	$colname_prospeccao_contador_selecionado = "-1";
	if (isset($_POST['id_contador'])) {
	  $colname_prospeccao_contador_selecionado = $_POST['id_contador'];
	}
	mysql_select_db($database_conexao, $conexao);
	$query_prospeccao_contador_selecionado = sprintf("SELECT id, razao FROM prospeccao_contador WHERE id = %s", GetSQLValueString($colname_prospeccao_contador_selecionado, "int"));
	$prospeccao_contador_selecionado = mysql_query($query_prospeccao_contador_selecionado, $conexao) or die(mysql_error());
	$row_prospeccao_contador_selecionado = mysql_fetch_assoc($prospeccao_contador_selecionado);
	$totalRows_prospeccao_contador_selecionado = mysql_num_rows($prospeccao_contador_selecionado);
	// fim - busca prospeccao_contador_selecionado

	$dados_prospeccao = array("interacao" => $row_prospeccao['interacao'] + 1,
			"id_contador" => $_POST['id_contador']			
	);	
	$dados_prospeccao_descricao = array(
			"id_prospeccao" => $row_prospeccao['id'],
			"id_usuario_responsavel" => $row_usuario['IdUsuario'],
			"descricao" => "Foi alterado a Contabilidade<br>Contador anterior: ".$row_prospeccao_contador['razao']."<br>Novo Contador: ".$row_prospeccao_contador_selecionado['razao']."<br>".$_POST['observacao'],
			"data" => date("Y-m-d H:i:s"),
			"tipo_postagem" => "Alteração de contador"
	);
	funcao_prospeccao_update($row_prospeccao['id'], $dados_prospeccao, $dados_prospeccao_descricao);
	
	mysql_free_result($prospeccao_contador_selecionado);
	
}
// fim - Alterar contador
//---------------------------------------------------------------------------------------------------------------------------------------------------------------
//---------------------------------------------------------------------------------------------------------------------------------------------------------------
// Alterar concorrente
if(($_GET['situacao']=="editar") and $_GET["acao"] == "Alterar concorrente"){

	// busca prospeccao_concorrente_selecionado
	$colname_prospeccao_concorrente_selecionado = "-1";
	if (isset($_POST['id_concorrente'])) {
	  $colname_prospeccao_concorrente_selecionado = $_POST['id_concorrente'];
	}
	mysql_select_db($database_conexao, $conexao);
	$query_prospeccao_concorrente_selecionado = sprintf("SELECT id, nome FROM prospeccao_concorrente WHERE id = %s", GetSQLValueString($colname_prospeccao_concorrente_selecionado, "int"));
	$prospeccao_concorrente_selecionado = mysql_query($query_prospeccao_concorrente_selecionado, $conexao) or die(mysql_error());
	$row_prospeccao_concorrente_selecionado = mysql_fetch_assoc($prospeccao_concorrente_selecionado);
	$totalRows_prospeccao_concorrente_selecionado = mysql_num_rows($prospeccao_concorrente_selecionado);
	// fim - busca prospeccao_concorrente_selecionado

	$dados_prospeccao = array("interacao" => $row_prospeccao['interacao'] + 1,
			"id_concorrente" => $_POST['id_concorrente']			
	);	
	$dados_prospeccao_descricao = array(
			"id_prospeccao" => $row_prospeccao['id'],
			"id_usuario_responsavel" => $row_usuario['IdUsuario'],
			"descricao" => "Foi alterado o Concorrente<br>Concorrente anterior: ".$row_prospeccao_concorrente['nome']."<br>Novo Concorrente: ".$row_prospeccao_concorrente_selecionado['nome']."<br>".$_POST['observacao'],
			"data" => date("Y-m-d H:i:s"),
			"tipo_postagem" => "Alteração de concorrente"
	);
	funcao_prospeccao_update($row_prospeccao['id'], $dados_prospeccao, $dados_prospeccao_descricao);
	
	mysql_free_result($prospeccao_concorrente_selecionado);
	
}
// fim - Alterar concorrente
//---------------------------------------------------------------------------------------------------------------------------------------------------------------
// Alterar Possui sistema
if(($_GET['situacao']=="editar") and $_GET["acao"] == "Alterar Possui sistema"){

	if($row_prospeccao['sistema_possui']=='n'){$sistema_possui_anterior = "Não";}
	if($row_prospeccao['sistema_possui']=='s'){$sistema_possui_anterior = "Sim";}

	if($_POST['sistema_possui']=='n'){$sistema_possui_novo = "Não";}
	if($_POST['sistema_possui']=='s'){$sistema_possui_novo = "Sim";}
	
	$dados_prospeccao = array("interacao" => $row_prospeccao['interacao'] + 1,
			"sistema_possui" => $_POST['sistema_possui']
	);	
	$dados_prospeccao_descricao = array(
			"id_prospeccao" => $row_prospeccao['id'],
			"id_usuario_responsavel" => $row_usuario['IdUsuario'],
			"descricao" => "Foi alterado o Possui sistema<br>Possui sistema anterior: ".$sistema_possui_anterior." - Novo Possui sistema: ".$sistema_possui_novo."<br>".$_POST['observacao'],
			"data" => date("Y-m-d H:i:s"),
			"tipo_postagem" => "Alteração de Possui sistema"
	);
	funcao_prospeccao_update($row_prospeccao['id'], $dados_prospeccao, $dados_prospeccao_descricao);
}
// fim - Alterar Possui sistema
//---------------------------------------------------------------------------------------------------------------------------------------------------------------
// Alterar Nível de utilização
if(($_GET['situacao']=="editar") and $_GET["acao"] == "Alterar Nível de utilização"){

	if($row_prospeccao['sistema_nivel_utilizacao']=='a'){$sistema_nivel_utilizacao_anterior = "Alto";}
	if($row_prospeccao['sistema_nivel_utilizacao']=='m'){$sistema_nivel_utilizacao_anterior = "Médio";}
	if($row_prospeccao['sistema_nivel_utilizacao']=='b'){$sistema_nivel_utilizacao_anterior = "Baixo";}
	if($row_prospeccao['sistema_nivel_utilizacao']=='n'){$sistema_nivel_utilizacao_anterior = "Não implantado";}

	if($_POST['sistema_nivel_utilizacao']=='a'){$sistema_nivel_utilizacao_novo = "Alto";}
	if($_POST['sistema_nivel_utilizacao']=='m'){$sistema_nivel_utilizacao_novo = "Médio";}
	if($_POST['sistema_nivel_utilizacao']=='b'){$sistema_nivel_utilizacao_novo = "Baixo";}
	if($_POST['sistema_nivel_utilizacao']=='n'){$sistema_nivel_utilizacao_novo = "Não implantado";}
	
	$dados_prospeccao = array("interacao" => $row_prospeccao['interacao'] + 1,
			"sistema_nivel_utilizacao" => $_POST['sistema_nivel_utilizacao']
	);	
	$dados_prospeccao_descricao = array(
			"id_prospeccao" => $row_prospeccao['id'],
			"id_usuario_responsavel" => $row_usuario['IdUsuario'],
			"descricao" => "Foi alterado o Nível de utilização<br>Nível de utilização anterior: ".$sistema_nivel_utilizacao_anterior." - Novo Nível de utilização: ".$sistema_nivel_utilizacao_novo."<br>".$_POST['observacao'],
			"data" => date("Y-m-d H:i:s"),
			"tipo_postagem" => "Alteração de Nível de utilização"
	);
	funcao_prospeccao_update($row_prospeccao['id'], $dados_prospeccao, $dados_prospeccao_descricao);
}
// fim - Alterar Nível de utilização
//---------------------------------------------------------------------------------------------------------------------------------------------------------------
// Alterar Nível de satisfação
if(($_GET['situacao']=="editar") and $_GET["acao"] == "Alterar Nível de satisfação"){

	if($row_prospeccao['sistema_nivel_satisfacao']=='a'){$sistema_nivel_satisfacao_anterior = "Alto";}
	if($row_prospeccao['sistema_nivel_satisfacao']=='m'){$sistema_nivel_satisfacao_anterior = "Médio";}
	if($row_prospeccao['sistema_nivel_satisfacao']=='b'){$sistema_nivel_satisfacao_anterior = "Baixo";}
	if($row_prospeccao['sistema_nivel_satisfacao']=='i'){$sistema_nivel_satisfacao_anterior = "Insatisfeito";}

	if($_POST['sistema_nivel_satisfacao']=='a'){$sistema_nivel_satisfacao_novo = "Alto";}
	if($_POST['sistema_nivel_satisfacao']=='m'){$sistema_nivel_satisfacao_novo = "Médio";}
	if($_POST['sistema_nivel_satisfacao']=='b'){$sistema_nivel_satisfacao_novo = "Baixo";}
	if($_POST['sistema_nivel_satisfacao']=='i'){$sistema_nivel_satisfacao_novo = "Insatisfeito";}
	
	$dados_prospeccao = array("interacao" => $row_prospeccao['interacao'] + 1,
			"sistema_nivel_satisfacao" => $_POST['sistema_nivel_satisfacao']
	);	
	$dados_prospeccao_descricao = array(
			"id_prospeccao" => $row_prospeccao['id'],
			"id_usuario_responsavel" => $row_usuario['IdUsuario'],
			"descricao" => "Foi alterado o Nível de satisfação<br>Nível de satisfação anterior: ".$sistema_nivel_satisfacao_anterior." - Novo Nível de satisfação: ".$sistema_nivel_satisfacao_novo."<br>".$_POST['observacao'],
			"data" => date("Y-m-d H:i:s"),
			"tipo_postagem" => "Alteração de Nível de satisfação"
	);
	funcao_prospeccao_update($row_prospeccao['id'], $dados_prospeccao, $dados_prospeccao_descricao);
}
// fim - Alterar Nível de satisfação
//---------------------------------------------------------------------------------------------------------------------------------------------------------------
// Alterar Motivo da Satisfação/Insatisfação
if(($_GET['situacao']=="editar") and $_GET["acao"] == "Alterar Motivo da Satisfação/Insatisfação"){

	
	$dados_prospeccao = array("interacao" => $row_prospeccao['interacao'] + 1,
			"sistema_nivel_motivo" => $_POST['sistema_nivel_motivo']
	);	
	$dados_prospeccao_descricao = array(
			"id_prospeccao" => $row_prospeccao['id'],
			"id_usuario_responsavel" => $row_usuario['IdUsuario'],
			"descricao" => "Foi alterado o Motivo da Satisfação/Insatisfação<br>
			Motivo da Satisfação/Insatisfação anterior: ".$row_prospeccao['sistema_nivel_motivo']." - 
			Novo Motivo da Satisfação/Insatisfação: ".$_POST['sistema_nivel_motivo']."<br>
			".$_POST['observacao'],
			"data" => date("Y-m-d H:i:s"),
			"tipo_postagem" => "Alteração de Motivo da Satisfação/Insatisfação"
	);
	funcao_prospeccao_update($row_prospeccao['id'], $dados_prospeccao, $dados_prospeccao_descricao);

}
// fim - Alterar Motivo da Satisfação/Insatisfação
//---------------------------------------------------------------------------------------------------------------------------------------------------------------
// Alterar Empresa faz algum controle manual?
if(($_GET['situacao']=="editar") and $_GET["acao"] == "Alterar Empresa faz algum controle manual?"){

	if($row_prospeccao['empresa_controle_manual']=='n'){$empresa_controle_manual_anterior = "Não";}
	if($row_prospeccao['empresa_controle_manual']=='s'){$empresa_controle_manual_anterior = "Sim";}

	if($_POST['empresa_controle_manual']=='n'){$empresa_controle_manual_novo = "Não";}
	if($_POST['empresa_controle_manual']=='s'){$empresa_controle_manual_novo = "Sim";}
	
	$dados_prospeccao = array("interacao" => $row_prospeccao['interacao'] + 1,
			"empresa_controle_manual" => $_POST['empresa_controle_manual']
	);	
	$dados_prospeccao_descricao = array(
			"id_prospeccao" => $row_prospeccao['id'],
			"id_usuario_responsavel" => $row_usuario['IdUsuario'],
			"descricao" => "Foi alterado o Empresa faz algum controle manual?<br>Empresa faz algum controle manual? anterior: ".$empresa_controle_manual_anterior." - Novo Empresa faz algum controle manual?: ".$empresa_controle_manual_novo."<br>".$_POST['observacao'],
			"data" => date("Y-m-d H:i:s"),
			"tipo_postagem" => "Alteração de Empresa faz algum controle manual?"
	);
	funcao_prospeccao_update($row_prospeccao['id'], $dados_prospeccao, $dados_prospeccao_descricao);
}
// fim - Alterar Empresa faz algum controle manual?
//---------------------------------------------------------------------------------------------------------------------------------------------------------------
// Alterar Necessidades/Interesses do cliente
if(($_GET['situacao']=="editar") and $_GET["acao"] == "Alterar Necessidades/Interesses do cliente"){

	$necessidades = NULL;
	if(count(@$_POST['necessidades']) > 0){
		$necessidades = implode(',', $_POST['necessidades']);
	}
	
	$dados_prospeccao = array("interacao" => $row_prospeccao['interacao'] + 1,
			"necessidades" => $necessidades
	);	
	$dados_prospeccao_descricao = array(
			"id_prospeccao" => $row_prospeccao['id'],
			"id_usuario_responsavel" => $row_usuario['IdUsuario'],
			"descricao" => "Foram alteradas as Necessidades/Interesses do cliente<br>
			Anterior: ".funcao_consulta_modulo_array($row_prospeccao['necessidades'])." - 
			Novo: ".funcao_consulta_modulo_array($necessidades)."<br>
			".$_POST['observacao'],
			"data" => date("Y-m-d H:i:s"),
			"tipo_postagem" => "Alteração de Necessidades/Interesses do cliente"
	);
	funcao_prospeccao_update($row_prospeccao['id'], $dados_prospeccao, $dados_prospeccao_descricao);

	
}
// fim - Alterar Necessidades/Interesses do cliente
//---------------------------------------------------------------------------------------------------------------------------------------------------------------
// Alterar O que podemos ofertar para automatizar o processo manual
if(($_GET['situacao']=="editar") and $_GET["acao"] == "Alterar O que podemos ofertar para automatizar o processo manual"){

	$podemos_ofertar = NULL;
	if(count(@$_POST['podemos_ofertar']) > 0){
		$podemos_ofertar = implode(',', $_POST['podemos_ofertar']);
	}
	
	$dados_prospeccao = array("interacao" => $row_prospeccao['interacao'] + 1,
			"podemos_ofertar" => $podemos_ofertar
	);	
	$dados_prospeccao_descricao = array(
			"id_prospeccao" => $row_prospeccao['id'],
			"id_usuario_responsavel" => $row_usuario['IdUsuario'],
			"descricao" => "Foi alterado O que podemos ofertar para automatizar o processo manual<br>
			Anterior: ".funcao_consulta_modulo_array($row_prospeccao['podemos_ofertar'])." - 
			Novo: ".funcao_consulta_modulo_array($podemos_ofertar)."<br>
			".$_POST['observacao'],
			"data" => date("Y-m-d H:i:s"),
			"tipo_postagem" => "Alteração de O que podemos ofertar para automatizar o processo manual"
	);
	funcao_prospeccao_update($row_prospeccao['id'], $dados_prospeccao, $dados_prospeccao_descricao);

	
}
// fim - Alterar O que podemos ofertar para automatizar o processo manual
//---------------------------------------------------------------------------------------------------------------------------------------------------------------
// Alterar Quais recursos o cliente utiliza?
if(($_GET['situacao']=="editar") and $_GET["acao"] == "Alterar Quais recursos o cliente utiliza?"){

	$podemos_ofertar = NULL;
	if(count(@$_POST['sistema_recursos']) > 0){
		$sistema_recursos = implode(',', $_POST['sistema_recursos']);
	}
	
	$dados_prospeccao = array("interacao" => $row_prospeccao['interacao'] + 1,
			"sistema_recursos" => $sistema_recursos
	);	
	$dados_prospeccao_descricao = array(
			"id_prospeccao" => $row_prospeccao['id'],
			"id_usuario_responsavel" => $row_usuario['IdUsuario'],
			"descricao" => "Foi alterado Quais recursos o cliente utiliza?<br>
			Anterior: ".funcao_consulta_modulo_array($row_prospeccao['sistema_recursos'])." - 
			Novo: ".funcao_consulta_modulo_array($sistema_recursos)."<br>
			".$_POST['observacao'],
			"data" => date("Y-m-d H:i:s"),
			"tipo_postagem" => "Alteração de Quais recursos o cliente utiliza?"
	);
	funcao_prospeccao_update($row_prospeccao['id'], $dados_prospeccao, $dados_prospeccao_descricao);

	
}
// fim - Alterar Quais recursos o cliente utiliza?
//---------------------------------------------------------------------------------------------------------------------------------------------------------------
// Alterar O Success tem os recursos que o cliente utiliza?
if(($_GET['situacao']=="editar") and $_GET["acao"] == "Alterar O Success tem os recursos que o cliente utiliza?"){

	if($row_prospeccao['sistema_recursos_success_possui']=='t'){$sistema_recursos_success_possui_anterior = "Totalmente";}
	if($row_prospeccao['sistema_recursos_success_possui']=='p'){$sistema_recursos_success_possui_anterior = "Parcialmente";}

	if($_POST['sistema_recursos_success_possui']=='t'){$sistema_recursos_success_possui_novo = "Totalmente";}
	if($_POST['sistema_recursos_success_possui']=='p'){$sistema_recursos_success_possui_novo = "Parcialmente";}
	
	$dados_prospeccao = array("interacao" => $row_prospeccao['interacao'] + 1,
			"sistema_recursos_success_possui" => $_POST['sistema_recursos_success_possui']
	);	
	$dados_prospeccao_descricao = array(
			"id_prospeccao" => $row_prospeccao['id'],
			"id_usuario_responsavel" => $row_usuario['IdUsuario'],
			"descricao" => "Foi alterado o O Success tem os recursos que o cliente utiliza?<br>O Success tem os recursos que o cliente utiliza? anterior: ".$sistema_recursos_success_possui_anterior." - Novo O Success tem os recursos que o cliente utiliza?: ".$sistema_recursos_success_possui_novo."<br>".$_POST['observacao'],
			"data" => date("Y-m-d H:i:s"),
			"tipo_postagem" => "Alteração de O Success tem os recursos que o cliente utiliza?"
	);
	funcao_prospeccao_update($row_prospeccao['id'], $dados_prospeccao, $dados_prospeccao_descricao);
}
// fim - Alterar O Success tem os recursos que o cliente utiliza?
//---------------------------------------------------------------------------------------------------------------------------------------------------------------
// Alterar Recursos que o cliente utiliza e o Success não tem
if(($_GET['situacao']=="editar") and $_GET["acao"] == "Alterar Recursos que o cliente utiliza e o Success não tem"){

	$dados_prospeccao = array("interacao" => $row_prospeccao['interacao'] + 1,
			"sistema_recursos_success_nao_possui" => $_POST['sistema_recursos_success_nao_possui']
	);	
	$dados_prospeccao_descricao = array(
			"id_prospeccao" => $row_prospeccao['id'],
			"id_usuario_responsavel" => $row_usuario['IdUsuario'],
			"descricao" => "Foi alterado o Recursos que o cliente utiliza e o Success não tem<br>
			Recursos que o cliente utiliza e o Success não tem anterior: ".$row_prospeccao['sistema_recursos_success_nao_possui']." - 
			Novo Recursos que o cliente utiliza e o Success não tem: ".$_POST['sistema_recursos_success_nao_possui']."<br>
			".$_POST['observacao'],
			"data" => date("Y-m-d H:i:s"),
			"tipo_postagem" => "Alteração de Recursos que o cliente utiliza e o Success não tem"
	);
	funcao_prospeccao_update($row_prospeccao['id'], $dados_prospeccao, $dados_prospeccao_descricao);

}
// fim - Alterar Recursos que o cliente utiliza e o Success não tem
//---------------------------------------------------------------------------------------------------------------------------------------------------------------
// Alterar status
if(($_GET['situacao']=="editar") and $_GET["acao"] == "Alterar status"){

	
	$dados_prospeccao = array("interacao" => $row_prospeccao['interacao'] + 1,
			"status" => $_POST['status']
	);	
	$dados_prospeccao_descricao = array(
			"id_prospeccao" => $row_prospeccao['id'],
			"id_usuario_responsavel" => $row_usuario['IdUsuario'],
			"descricao" => "Foi alterado o status<br>Status anterior: ".$row_prospeccao['status']." - Novo Status: ".$_POST['status']."<br>".$_POST['observacao'],
			"data" => date("Y-m-d H:i:s"),
			"tipo_postagem" => "Alteração de status"
	);
	funcao_prospeccao_update($row_prospeccao['id'], $dados_prospeccao, $dados_prospeccao_descricao);

}
// fim - Alterar status
//---------------------------------------------------------------------------------------------------------------------------------------------------------------
// Alterar observação
if(($_GET['situacao']=="editar") and $_GET["acao"] == "Alterar observação"){

	
	$dados_prospeccao = array("interacao" => $row_prospeccao['interacao'] + 1,
			"observacao" => $_POST['observacao']
	);	
	$dados_prospeccao_descricao = array(
			"id_prospeccao" => $row_prospeccao['id'],
			"id_usuario_responsavel" => $row_usuario['IdUsuario'],
			"descricao" => "Foi alterado o Observação<br>Observação anterior: ".$row_prospeccao['observacao']." - Novo Observação: ".$_POST['observacao']."<br>".$_POST['observacao'],
			"data" => date("Y-m-d H:i:s"),
			"tipo_postagem" => "Alteração de observação"
	);
	funcao_prospeccao_update($row_prospeccao['id'], $dados_prospeccao, $dados_prospeccao_descricao);

}
// fim - Alterar observação
//---------------------------------------------------------------------------------------------------------------------------------------------------------------
// fim - Alterar

// limpando o array
$dados_prospeccao = array();
$dados_prospeccao_descricao = array();
// fim - limpando o array

// redireciona
$updateGoTo = "prospeccao_editar.php?id_prospeccao=".$_GET['id_prospeccao'];
echo sprintf("<meta http-equiv=\"refresh\" content=\"0; url=%s\">", $updateGoTo);
// fim - redireciona

exit;
}

$prospeccao_nivel_interesse_reagendamento = NULL;
if($row_prospeccao['nivel_interesse'] == 'n'){
	$prospeccao_nivel_interesse_reagendamento = $row_parametros['prospeccao_nivel_interesse_reagendamento_baixo'];
} else if($row_prospeccao['nivel_interesse'] == 'b'){
	$prospeccao_nivel_interesse_reagendamento = $row_parametros['prospeccao_nivel_interesse_reagendamento_baixo'];
} else if($row_prospeccao['nivel_interesse'] == 'm'){
	$prospeccao_nivel_interesse_reagendamento = $row_parametros['prospeccao_nivel_interesse_reagendamento_medio'];
} else if($row_prospeccao['nivel_interesse'] == 'a'){ 
	$prospeccao_nivel_interesse_reagendamento = $row_parametros['prospeccao_nivel_interesse_reagendamento_alto'];
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><? echo $_GET['acao']; ?> (<?php echo $row_prospeccao['id']; ?>)</title>

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
label.error { color: red; display: none; }	
/* fim - erro de validação */

/* calendário */
.ui-timepicker-div .ui-widget-header { margin-bottom: 8px; }
.ui-timepicker-div dl { text-align: left; }
.ui-timepicker-div dl dt { height: 25px; margin-bottom: -25px; }
.ui-timepicker-div dl dd { margin: 0 10px 10px 65px; }
.ui-timepicker-div td { font-size: 90%; }
.ui-tpicker-grid-label { background: none; border: none; margin: 0; padding: 0; }

.ui-timepicker-rtl{ direction: rtl; }
.ui-timepicker-rtl dl { text-align: right; }
.ui-timepicker-rtl dl dd { margin: 0 65px 10px 10px; }
/* fim - calendário */

.ui-datepicker-trigger {
margin-left : 5px;
vertical-align : top;
}

.ui-dialog{
	font-size: 12px;
}
</style>
<script type="text/javascript">
window.history.forward(1); // Desabilita a função de voltar do Browser

// validar diferença entre datas
jQuery.validator.addMethod("dateRange", function() {

		var is_valid = true;
		var data_inicio = $("#data_agendamento_inicio").val();
		var data_fim = $("#data_agendamento").val();
												 
		if(data_inicio.length != 16){if(data_inicio.length != 19){is_valid = false;}}
		if(data_fim.length != 16){if(data_fim.length != 19){is_valid = false;}}

		if(data_inicio != "" && data_fim != ""){
			
			// quebra data inicial
			var quebraDI=data_inicio.split("-");
			var diaDI = quebraDI[0];
			var mesDI = quebraDI[1];
			var anoDI = quebraDI[2].substr(0,4);
			var time_inicial = quebraDI[2].substr(5,8);
			var quebraTimeDI=time_inicial.split(":");
			var horaDI = quebraTimeDI[0];
			var minutoDI = quebraTimeDI[1];
			var segundoDI = quebraTimeDI[2];
			if(quebraTimeDI[2]==null){
				var segundoDI = '00';
			} else {
				var segundoDI = quebraTimeDI[2];
			}
		
			// quebra data final
			var quebraDF=data_fim.split("-");
			var diaDF = quebraDF[0];
			var mesDF = quebraDF[1];
			var anoDF = quebraDF[2].substr(0,4);
			var time_final = quebraDF[2].substr(5,8);
			var quebraTimeDF=time_final.split(":");
			var horaDF = quebraTimeDF[0];
			var minutoDF = quebraTimeDF[1];
			var segundoDF = quebraTimeDF[2];
			if(quebraTimeDF[2]==null){
				var segundoDF = '00';
			} else {
				var segundoDF = quebraTimeDF[2];
			}
	
			var date1 = anoDI+"-"+mesDI+"-"+diaDI+" "+horaDI+":"+minutoDI+":"+segundoDI;
			var date2 = anoDF+"-"+mesDF+"-"+diaDF+" "+horaDF+":"+minutoDF+":"+segundoDF;
			
			is_valid = date1 < date2;
			
		}
		
		return (is_valid);
		
}, " Data final deve ser maior que a data inicial");
// validar diferença entre datas

$.metadata.setType("attr", "validate");
$(document).ready(function() {

	// tab/enter	
	textboxes = $("input, select, textarea");
	$("input, select").keypress(function(e){

		var tecla = (e.keyCode?e.keyCode:e.which);
		if(tecla == 13 || tecla == 9){
			
			// corrige problema - quando a agenda está aberta, o tab/enter fica com o FOCUS na hora_inicio			
			if ( $("#TB_window").length ) { // verifica se o tb_show está sendo exibido
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
	
	// acao_agenda
	<? if($_GET['situacao']=="editar" and 
				(
				$_GET["acao"] == "Agendamento" or 
				$_GET["acao"] == "Finalizar agendamento"
				)
	){ ?>
	$('input[id="data_agendamento_inicio"]').attr('disabled', true);
	$('select[id="agendamento_tempo"]').attr('disabled', true);
	$('select[id="prospeccao_agenda_tipo"]').attr('disabled', true);
	$('input[id="data_agendamento"]').attr('disabled', true);
	$('textarea[id="descricao_agendamento"]').attr('disabled', true);
	$('select[id="status"]').attr('disabled', true);
	$('select[id="usuario_responsavel"]').attr('disabled', true);
	
	$('#div_data_agendamento_inicio').hide();
	$('#div_agendamento_tempo').hide();
	$('#div_prospeccao_agenda_tipo').hide();
	$('#div_data_agendamento').hide();
	$('#div_descricao_agendamento').hide();
	$('#div_status').hide();
	$('#div_usuario_responsavel').hide();
				
	$("input[id='acao_agenda']").change(function () { // ao mudar o valor do select
		
		$("input[id='acao_agenda']:checked").each(function () {
														   
			var acao_agenda_atual = $(this).val(); // lê o valor selecionado

			if( acao_agenda_atual=="a" ){
				
				$('input[id="data_agendamento_inicio"]').attr('disabled', false);
				$('select[id="agendamento_tempo"]').attr('disabled', false);
				$('select[id="prospeccao_agenda_tipo"]').attr('disabled', false);
				$('input[id="data_agendamento"]').attr('disabled', false);
				$('textarea[id="descricao_agendamento"]').attr('disabled', false);
				$('select[id="status"]').attr('disabled', false);
				$('select[id="usuario_responsavel"]').attr('disabled', false);
				
				$('#div_data_agendamento_inicio').show();
				$('#div_agendamento_tempo').show();
				$('#div_prospeccao_agenda_tipo').show();
				$('#div_data_agendamento').show();
				$('#div_descricao_agendamento').show();
				$('#div_status').show();
				$('#div_usuario_responsavel').show();
				
			}
			
			if( acao_agenda_atual=="s" ){
				
				$('input[id="data_agendamento_inicio"]').attr('disabled', true);
				$('select[id="agendamento_tempo"]').attr('disabled', true);
				$('select[id="prospeccao_agenda_tipo"]').attr('disabled', true);
				$('input[id="data_agendamento"]').attr('disabled', true);
				$('textarea[id="descricao_agendamento"]').attr('disabled', true);
				$('select[id="status"]').attr('disabled', true);
				$('select[id="usuario_responsavel"]').attr('disabled', true);
				
				$('#div_data_agendamento_inicio').hide();
				$('#div_agendamento_tempo').hide();
				$('#div_prospeccao_agenda_tipo').hide();
				$('#div_data_agendamento').hide();
				$('#div_descricao_agendamento').hide();
				$('#div_status').hide();
				$('#div_usuario_responsavel').hide();
				
			}
			
		});
		
	});
	<? } ?>
	// fim - acao_agenda
	
	// baixa_tipo
	<? if($_GET['situacao']=="editar" and 
				(
				$_GET["acao"] == "Encerrar"
				)
	){ ?>
	
	<? if($botao_encerrar_status == 0){ ?>

	jQuery('input[id="baixa_tipo"]:radio[value="v"]').attr('disabled', true);
	
	<? } ?>
	
	<?
	if(
	($row_usuario['nivel_prospeccao'] == 2 and $row_usuario['praca'] == $row_prospeccao['praca']) or 
	($row_usuario['nivel_prospeccao'] == 3 and $row_usuario['praca'] == $row_prospeccao['praca'])
	){
	?>
	
	jQuery('input[id="baixa_tipo"]:radio[value="p"]').attr('disabled', true);
	
	<? } ?>
	
	
	$('input[id="baixa_contrato"]').attr('disabled', true);
	$('input[id="baixa_contrato_data"]').attr('disabled', true);
	$('input[id="venda_modulos"]').attr('disabled', true);

	$('select[id="implantacao_tempo_adicional"]').attr('disabled', true);
	$('select[id="treinamento_tempo"]').attr('disabled', true);

	$('input[id="valor_venda"]').attr('disabled', true);
	$('input[id="valor_treinamento"]').attr('disabled', true);
	
	$('input[id="baixa_perda_motivo"]').attr('disabled', true);
	
	$('input[id="baixa_perda_recurso"]').attr('disabled', true);
	$('input[id="baixa_perda_recurso_solicitacao_existe"]').attr('disabled', true);
	$('input[id="baixa_perda_recurso_solicitacao_verificada"]').attr('disabled', true);
	$('input[id="baixa_perda_recurso_solicitacao_sugestao"]').attr('disabled', true);
    
	$('select[id="baixa_perda_concorrencia_programa"]').attr('disabled', true);
	$('input[id="baixa_perda_concorrencia_fator"]').attr('disabled', true);
	
	$('#div_baixa_contrato').hide();
	$('#div_baixa_contrato_data').hide();
	$('#div_venda_modulos').hide();

	$('#div_implantacao_tempo_adicional').hide();
	$('#div_treinamento_tempo').hide();

	$('#div_valor_venda').hide();
	$('#div_valor_treinamento').hide();
	
	$('#div_baixa_perda_motivo').hide();
	
	$('#div_baixa_perda_recurso').hide();
	$('#div_baixa_perda_recurso_solicitacao_existe').hide();
	$('#div_baixa_perda_recurso_solicitacao_verificada').hide();
	$('#div_baixa_perda_recurso_solicitacao_sugestao').hide();
    
	$('#div_baixa_perda_concorrencia_programa').hide();
	$('#div_baixa_perda_concorrencia_fator').hide();
	
	
	$('#div_perda_questionario').hide();
	
	
	$("input[name='baixa_tipo']").change(function () { // ao mudar o valor do select
												 
		$("input[name='baixa_tipo']:checked").each(function () {									   
			var baixa_tipo_atual = $(this).val(); // lê o valor selecionado

			if(baixa_tipo_atual=="v"){

				$('input[id="baixa_contrato"]').attr('disabled', false);
				$('input[id="baixa_contrato_data"]').attr('disabled', false);
				$('input[id="venda_modulos"]').attr('disabled', false);

				$('select[id="implantacao_tempo_adicional"]').attr('disabled', true);
				$('select[id="treinamento_tempo"]').attr('disabled', true);

				$('input[id="valor_venda"]').attr('disabled', false);
				$('input[id="valor_treinamento"]').attr('disabled', false);
				
				$('input[id="baixa_perda_motivo"]').attr('disabled', true);
				
				$('input[id="baixa_perda_recurso"]').attr('disabled', true);
				$('input[id="baixa_perda_recurso_solicitacao_existe"]').attr('disabled', true);
				$('input[id="baixa_perda_recurso_solicitacao_verificada"]').attr('disabled', true);
				$('input[id="baixa_perda_recurso_solicitacao_sugestao"]').attr('disabled', true);
				
				$('select[id="baixa_perda_concorrencia_programa"]').attr('disabled', true);
				$('input[id="baixa_perda_concorrencia_fator"]').attr('disabled', true);
				
				
				
				$('#div_baixa_contrato').show();
				$('#div_baixa_contrato_data').show();
				$('#div_venda_modulos').show();

				$('#div_implantacao_tempo_adicional').show();
				$('#div_treinamento_tempo').show();

				$('#div_valor_venda').show();
				$('#div_valor_treinamento').show();
				
				$('#div_baixa_perda_motivo').hide();
				
				$('#div_baixa_perda_recurso').hide();
				$('#div_baixa_perda_recurso_solicitacao_existe').hide();
				$('#div_baixa_perda_recurso_solicitacao_verificada').hide();
				$('#div_baixa_perda_recurso_solicitacao_sugestao').hide();
				
				$('#div_baixa_perda_concorrencia_programa').hide();
				
				
				$('#div_perda_questionario').hide();

			}
			
			if(baixa_tipo_atual=="p"){

				$('input[id="baixa_contrato"]').attr('disabled', true);
				$('input[id="baixa_contrato_data"]').attr('disabled', true);
				$('input[id="venda_modulos"]').attr('disabled', true);

				$('select[id="implantacao_tempo_adicional"]').attr('disabled', true);
				$('select[id="treinamento_tempo"]').attr('disabled', true);

				$('input[id="valor_venda"]').attr('disabled', true);
				$('input[id="valor_treinamento"]').attr('disabled', true);
				
				$('input[id="baixa_perda_motivo"]').attr('disabled', false);

				$('input[id="baixa_perda_recurso"]').attr('disabled', true);
				$('input[id="baixa_perda_recurso_solicitacao_existe"]').attr('disabled', true);
				$('input[id="baixa_perda_recurso_solicitacao_verificada"]').attr('disabled', true);
				$('input[id="baixa_perda_recurso_solicitacao_sugestao"]').attr('disabled', true);
				
				$('select[id="baixa_perda_concorrencia_programa"]').attr('disabled', true);
				$('input[id="baixa_perda_concorrencia_fator"]').attr('disabled', true);
				
				
				
				$('#div_baixa_contrato').hide();
				$('#div_baixa_contrato_data').hide();
				$('#div_venda_modulos').hide();

				$('#div_implantacao_tempo_adicional').hide();
				$('#div_treinamento_tempo').hide();

				$('#div_valor_venda').hide();
				$('#div_valor_treinamento').hide();
				
				$('#div_baixa_perda_motivo').show();
				
				$('#div_baixa_perda_recurso').hide();
				$('#div_baixa_perda_recurso_solicitacao_existe').hide();
				$('#div_baixa_perda_recurso_solicitacao_verificada').hide();
				$('#div_baixa_perda_recurso_solicitacao_sugestao').hide();
				
				$('#div_baixa_perda_concorrencia_programa').hide();
				$('#div_baixa_perda_concorrencia_fator').hide();
				
				
				$('#div_perda_questionario').show();
				
			}
		});
		
	});
	<? } ?>
	// baixa_tipo
	
	// baixa_perda_motivo
	<? if($_GET['situacao']=="editar" and 
				(
				$_GET["acao"] == "Encerrar"
				)
	){ ?>	
	$("input[name='baixa_perda_motivo']").change(function () { // ao mudar o valor do select
												 
		$("input[name='baixa_perda_motivo']:checked").each(function () {									   
			var baixa_perda_motivo_atual = $(this).val(); // lê o valor selecionado

			if(baixa_perda_motivo_atual=="falta de recurso"){

				$('input[id="baixa_perda_recurso"]').attr('disabled', false);
				$('input[id="baixa_perda_recurso_solicitacao_existe"]').attr('disabled', false);
				$('input[id="baixa_perda_recurso_solicitacao_verificada"]').attr('disabled', false);
				$('input[id="baixa_perda_recurso_solicitacao_sugestao"]').attr('disabled', false);
				
				$('select[id="baixa_perda_concorrencia_programa"]').attr('disabled', true);
				$('input[id="baixa_perda_concorrencia_fator"]').attr('disabled', true);
				
				$('#div_baixa_perda_recurso').show();
				$('#div_baixa_perda_recurso_solicitacao_existe').show();
				$('#div_baixa_perda_recurso_solicitacao_verificada').show();
				$('#div_baixa_perda_recurso_solicitacao_sugestao').show();
				
				$('#div_baixa_perda_concorrencia_programa').hide();
				$('#div_baixa_perda_concorrencia_fator').hide();

			}
			
			if(baixa_perda_motivo_atual=="concorrência"){

				$('input[id="baixa_perda_recurso"]').attr('disabled', true);
				$('input[id="baixa_perda_recurso_solicitacao_existe"]').attr('disabled', true);
				$('input[id="baixa_perda_recurso_solicitacao_verificada"]').attr('disabled', true);
				$('input[id="baixa_perda_recurso_solicitacao_sugestao"]').attr('disabled', true);
				
				$('select[id="baixa_perda_concorrencia_programa"]').attr('disabled', false);
				$('input[id="baixa_perda_concorrencia_fator"]').attr('disabled', false);
				
				$('#div_baixa_perda_recurso').hide();
				$('#div_baixa_perda_recurso_solicitacao_existe').hide();
				$('#div_baixa_perda_recurso_solicitacao_verificada').hide();
				$('#div_baixa_perda_recurso_solicitacao_sugestao').hide();
				
				$('#div_baixa_perda_concorrencia_programa').show();
				$('#div_baixa_perda_concorrencia_fator').show();
				
			}
			
			if(baixa_perda_motivo_atual=="encerramento de atividade"){

				$('input[id="baixa_perda_recurso"]').attr('disabled', true);
				$('input[id="baixa_perda_recurso_solicitacao_existe"]').attr('disabled', true);
				$('input[id="baixa_perda_recurso_solicitacao_verificada"]').attr('disabled', true);
				$('input[id="baixa_perda_recurso_solicitacao_sugestao"]').attr('disabled', true);
				
				$('select[id="baixa_perda_concorrencia_programa"]').attr('disabled', true);
				$('input[id="baixa_perda_concorrencia_fator"]').attr('disabled', true);
				
				$('#div_baixa_perda_recurso').hide();
				$('#div_baixa_perda_recurso_solicitacao_existe').hide();
				$('#div_baixa_perda_recurso_solicitacao_verificada').hide();
				$('#div_baixa_perda_recurso_solicitacao_sugestao').hide();
				
				$('#div_baixa_perda_concorrencia_programa').hide();
				$('#div_baixa_perda_concorrencia_fator').hide();
				
			}
			
			if(baixa_perda_motivo_atual=="outros motivos"){

				$('input[id="baixa_perda_recurso"]').attr('disabled', true);
				$('input[id="baixa_perda_recurso_solicitacao_existe"]').attr('disabled', true);
				$('input[id="baixa_perda_recurso_solicitacao_verificada"]').attr('disabled', true);
				$('input[id="baixa_perda_recurso_solicitacao_sugestao"]').attr('disabled', true);
				
				$('select[id="baixa_perda_concorrencia_programa"]').attr('disabled', true);
				$('input[id="baixa_perda_concorrencia_fator"]').attr('disabled', true);
				
				$('#div_baixa_perda_recurso').hide();
				$('#div_baixa_perda_recurso_solicitacao_existe').hide();
				$('#div_baixa_perda_recurso_solicitacao_verificada').hide();
				$('#div_baixa_perda_recurso_solicitacao_sugestao').hide();
				
				$('#div_baixa_perda_concorrencia_programa').hide();
				$('#div_baixa_perda_concorrencia_fator').hide();
				
			}
			
		});
		
	});
	<? } ?>
	// baixa_perda_motivo
	
	// Click no botão Botão: Salvar ---------------------------------------------------------------
	$('#button').click(function() {

		<? if(
			  ($_GET['situacao']=="editar") and 
					(
					 $_GET["acao"] == "Agendamento" or
					 $_GET["acao"] == "Finalizar agendamento" or 
					 $_GET["acao"] == "Reagendar"
					 )
		){ ?>

			// consulta automática - prospeccao_agenda
			if($("input[name=data_agendamento_inicio]").val() != '' && $("input[name=data_agendamento]").val() != ''  && $("select[name=usuario_responsavel]").val() != '') {
				
				// post
				$.post("agenda_consulta.php", {
					   data_inicio: $("input[name=data_agendamento_inicio]").val(), 
					   data_fim: $("input[name=data_agendamento]").val(),
					   id_usuario_responsavel: $("select[name=usuario_responsavel]").find("option:selected").attr("title"),
					   id_agenda: <? if($totalRows_agenda>0){echo $row_agenda['id_agenda'];}else{echo 0;} ?>
					   }, function(data) {
	
							if(data == 0){
								$('#form').submit();
							}
							if(data == 1){
								alert("Data informada inválida. Existem agendamentos para o usuário atual durante período informado.");
								$('#data_agendamento').val('');
								$('#agendamento_tempo').val('');
								$('#prospeccao_agenda_tipo').val('');
								return false;
							}
							
					   }
				);
				// fim - post
			
			} else {
				
				$('#form').submit();
				
			}
			// fim - consulta automática - prospeccao_agenda

		<? } else { ?>
		
			$('#form').submit();
		
		<? } ?>

	});
	// fim - Click no botão Botão: Salvar ---------------------------------------------------------
	
	// validação
	$("#form").validate({
		rules: {
			<? if(
			(	
			$_GET['acao']=="Encaminhar" or 
			$_GET['acao']=="Recusar" or 
			$_GET['acao']=="Encerrar" or 
			$_GET['acao']=="Cancelar" or 
			$_GET["acao"] == "Agendamento" or 
			$_GET["acao"] == "Finalizar agendamento" or 
			$_GET["acao"] == "Reagendar" or 
			$_GET["acao"] == "Solicitar agendamento" or 
			$_GET["acao"] == "Cancelar solicitação de agendamento" or 
			$_GET['acao']=="Questionar"
			)
			){ ?>
			observacao: { 
				required: {
					depends: function() {return $('input[name=acao_agenda]:checked').val() != 'a';}
				},
				minlength: 10
			},
			<? } ?>
			
			<? if(
			(
			$_GET["acao"] == "Finalizar agendamento"
			)
			){ ?>
			data_atendimento:  "required",
			hora_atendimento_inicio:  "required",
			hora_atendimento_fim:  "required",
			atendimento_receptor:  "required",
			
			nivel_interesse:  "required",
			proposta_recursos: { 
				required: {
					depends: function() {return parseFloat($('input[name=proposta_valor]').val().replace(',','.')) > 0; }
				}
			},
			proposta_validade: { 
				required: {
					depends: function() {return parseFloat($('input[name=proposta_valor]').val().replace(',','.')) > 0; }
				}
			},
			<? } ?>
			
			acao_agenda:  "required",
			prospeccao_responsavel_cancelado:  "required",
			data_agendamento_inicio: {required: true},
			agendamento_tempo: {required: true},
			prospeccao_agenda_tipo: {required: true},
			data_agendamento: {required: true, dateRange: true},
			ativo_passivo:  "required",
			nome_razao_social:  "required",
			cpf_cnpj:  "required",
			rg_inscricao:  "required",
			ramo_de_atividade:  "required",
			
			cep:  "required",
			endereco: { 
				required: true,
				minlength: 5
			},
			//endereco_numero:  "required",
			bairro:  "required",
			cidade:  "required",
			uf:  "required",
			
			telefone: { 
				required: true,
				minlength: 10
			},
			
			baixa_tipo:  "required",
			baixa_contrato_data: { 
				required: {depends: function() {return $('input[name=baixa_tipo]:checked').val() == 'v';}}
			},
			valor_venda:  "required",
			valor_treinamento:  "required",

			//implantacao_tempo_adicional: {required: true, min: 1},
			treinamento_tempo: {required: true, min: 1},

			"venda_modulos[]":  {
              required: true,
              minlength: 1
          	},
			
			baixa_perda_motivo:  "required",
			
			baixa_perda_recurso:  "required",
			baixa_perda_recurso_solicitacao_existe:  "required",
			baixa_perda_recurso_solicitacao_verificada:  "required",
			baixa_perda_recurso_solicitacao_sugestao:  "required",
			baixa_perda_concorrencia_programa:  "required",
			baixa_perda_concorrencia_fator:  "required",
			
			usuario_responsavel:  "required",
			
			<? if(
			($_GET['situacao']=="editar" and ($_GET["acao"] == "Agendamento" or $_GET["acao"] == "Finalizar agendamento"))
			){ ?>
			descricao_agendamento: {required: true, minlength: 10},
			<? } ?>
			
			contato_responsavel: "required",
			contato_telefone: "required",
			
			status: "required", 

			atendimento_cliente: "required",
			atendimento_local: "required",
			atendimento_previsao: "required",
			atendimento_status: "required",
			atendimento_texto: {
				required: true,
				minlength: 10
			}
		},
		messages: {
			<? if(
			(	
			$_GET['acao']=="Encaminhar" or 
			$_GET['acao']=="Recusar" or 
			$_GET['acao']=="Encerrar" or 
			$_GET['acao']=="Cancelar" or 
			$_GET["acao"] == "Agendamento" or 
			$_GET["acao"] == "Finalizar agendamento" or 
			$_GET["acao"] == "Reagendar" or 
			$_GET["acao"] == "Solicitar agendamento" or 
			$_GET["acao"] == "Cancelar solicitação de agendamento" or 
			$_GET['acao']=="Questionar"
			)
			){ ?>
			observacao:  "Informe a observação com no mínimo 10 caracteres", 
			<? } ?>

			<? if(
			(
			$_GET["acao"] == "Finalizar agendamento"
			)
			){ ?>
			data_atendimento:  " Informe a data",
			hora_atendimento_inicio:  " Informe a hora inicial",
			hora_atendimento_fim:  " Informe a hora final",
			atendimento_receptor:  " Informe o receptor",
			
			nivel_interesse: " Selecione o nível de interesse",
			proposta_recursos:  " Informe os recursos",
			proposta_validade:  " Informe a validade",
			<? } ?>
						
			acao_agenda:  " Selecione uma das opções",
			prospeccao_responsavel_cancelado: " Informe o responsável pelo cancelamento",
			data_agendamento_inicio: " Informe uma data inicial",
			agendamento_tempo: " Selecione o tempo de agendamento",
			prospeccao_agenda_tipo: " Selecione o tipo de agendamento",
			data_agendamento: {required: " Informe uma data final"},
			ativo_passivo:  " Informe o tipo de prospect",
			nome_razao_social:  " Informe o cliente",
			cpf_cnpj:  " Informe o CPF/CNPJ",
			rg_inscricao:  " Informe o RG/Inscrição",
			ramo_de_atividade:  " Informe o ramo de atividade",
			
			cep:  " Informe o cep",
			endereco:  " Informe o endereço (mínimo 5 caracteres)",
			//endereco_numero:  " Informe o número",
			bairro:  " Informe o bairro",
			cidade:  " Informe a cidade",
			uf:  " Informe o estado",
			
			telefone:  " Informe o telefone com DDD (mínimo 10 caracteres)",
			
			baixa_tipo:  " Informe o motivo da baixa",
			baixa_contrato_data:  " Informe a data do contrato",
			valor_venda:  " Informe o valor",
			valor_treinamento:  " Informe o valor",

			//implantacao_tempo_adicional:  " Selecione o tempo",
			treinamento_tempo:  " Selecione o tempo",

			"venda_modulos[]":  " Selecione pelo menos um dos módulos acima",
			
			baixa_perda_motivo:  " Informe o motivo da perda",
			
			baixa_perda_recurso:  " Informe o recurso",
			baixa_perda_recurso_solicitacao_existe:  " Selecione uma das opções",
			baixa_perda_recurso_solicitacao_verificada:  " Selecione uma das opções",
			baixa_perda_recurso_solicitacao_sugestao:  " Selecione uma das opções",
			baixa_perda_concorrencia_programa:  " Informe o programa",
			baixa_perda_concorrencia_fator:  " Informe o fator",
			
			usuario_responsavel:  " Informe o usuário responsável",
			
			<? if(
			($_GET['situacao']=="editar" and ($_GET["acao"] == "Agendamento" or $_GET["acao"] == "Finalizar agendamento"))
			){ ?>
			descricao_agendamento:  "Informe a descrição com no mínimo 10 caracteres", 
			<? } ?>

			contato_responsavel: " Informe o contato",
			contato_telefone: " Informe o telefone",
						
			status: " Informe o status", 

			atendimento_cliente: " Informe o cliente",
			atendimento_local: " Informe o local",
			atendimento_previsao: " Informe a previsão",
			atendimento_status: "Selecione o status",
			atendimento_texto: " Informe o texto com no mínimo 10 caracteres"
		},
		onkeyup: false,
		submitHandler: function(form) {
			
			<? if($_GET['situacao']=="editar" and $_GET['acao']=="Encerrar"){ ?>
			
				var baixa_tipo_atual = $("input[name='baixa_tipo']:checked").val();
				
				if(baixa_tipo_atual=="v"){

					<? if($row_prospeccao['tipo_cliente']=="a"){ ?>
					
						var r = confirm('Tem certeza que essa prospecção é para cliente “Antigo"?');
						if(r==false){
							alert('Antes de prosseguir consulte o administrador da praça');
							return false;
						}
						if(r==true){
							var r2 = confirm('O encerramento dessa prospecção gerará uma Venda e o processo não poderá ser revertido. Confirma o encerramento da prospecção?');
							if(r2==false){
								return false;
							}
							if(r2==true){
								form.submit();
							}
						}
						
					<? } ?>
					
					<? if($row_prospeccao['tipo_cliente']=="n" and $row_prospeccao['cpf_cnpj']!=""){ ?>

						// post
						$.post("prospeccao_consulta_cpf_cnpj.php", 
							  {cpf_cnpj: <? echo $row_prospeccao['cpf_cnpj']; ?>},
							  function(valor){
								 
								 if(valor.retorno == 0){
									 
									 form.submit();
									 
								 } else {
									 
									var r = confirm('O CNPJ '+valor.cgc1+' já está cadastrado no sistema para a razão social '+valor.nome1+'.\n Tem certeza que essa prospecção é para cliente “Novo"?');
									if(r==false){
										alert('Antes de prosseguir consulte o administrador da praça.');
										return false;
									}
									if(r==true){
										var r2 = confirm('Confirma que o cliente está adquirindo outra licença para o mesmo CNPJ?');
										if(r2==false){
											return false;
										}
										if(r2==true){
											var r3 = confirm('Atenção: O encerramento dessa prospecção gerará um novo contrato de manutenção para o cliente. Em caso de cliente antigo com manutenção ativa, o mesmo passará a ter mais de um contrato de manutenção ativo para o mesmo CNPJ.');
											if(r3==false){
												return false;
											}
											if(r3==true){
												var r4 = confirm('O encerramento dessa prospecção gerará uma Venda e o processo não poderá ser revertido. Confirma o encerramento da prospecção?');
												if(r4==false){
													return false;
												}
												if(r4==true){
													form.submit();
												}
											}
										}
									}									 
												 
								 }
								 
							  }, "json"
						)
						// fim - post
						
					<? } ?>
					
				} else {
					form.submit();
				}
			
			<? } else { ?>
			
				form.submit();
			
			<? } ?>

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
	
	$("#baixa_contrato").numeric();
	
	// mascara
	$('#data_atendimento').mask('99-99-9999',{placeholder:" "});
	$('#hora_atendimento_inicio').mask('99:99',{placeholder:" "});
	$('#hora_atendimento_fim').mask('99:99',{placeholder:" "});
			
	$('#data_agendamento_inicio').mask('99-99-9999 99:99',{placeholder:" "});
	$('#data_agendamento').mask('99-99-9999 99:99',{placeholder:" "});
	$('#baixa_contrato_data').mask('99-99-9999',{placeholder:" "});
	$('#cep').mask('99999-999',{placeholder:" "});
	
	$('#atendimento_tempo_gasto').mask('99:99:99',{placeholder:" "});
	
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
	
	$('#proposta_valor').priceFormat({
		prefix: '',
		centsSeparator: ',',
		thousandsSeparator: ''
	});
	// fim - mascara

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

	// atendimento_tempo_gasto
	<?php
	$tg_dias_restantes = 0;
	$tg_horas_restantes = 23;
	$tg_minutos_restantes = 59;
	
	if($row_agenda_agendado['data_inicio']!=NULL){
		
		#Calculamos a contagem regressiva
		$previsao_geral_inicio_diferenca = strtotime(date('Y-m-d H:i:s')) - strtotime($row_agenda_agendado['data_inicio']);
		
		// valida a quantidade de dias, deixando livre minutos e segundos pois o usuário tem a opção de escolhar dias completos ou horas ou minutos.
		$tg_dias_restantes = floor ($previsao_geral_inicio_diferenca / 60 / 60 / 24);
		
		if($tg_dias_restantes < 1){ // se é menor que 1 dia, então valida somente horas e minutos.
			
			$tg_horas_restantes = floor (($previsao_geral_inicio_diferenca - ($tg_dias_restantes * 60 * 60 * 24)) / 60 / 60);
			
				if($tg_horas_restantes < 1){ // se é menor que 1 hora, então valida somente minutos.
					
					$tg_minutos_restantes = floor (($previsao_geral_inicio_diferenca - ($tg_dias_restantes * 60 * 60 * 24) - ($tg_horas_restantes * 60 * 60)) / 60);
					
				}

		}
		
	}
	?>
	
	$('#atendimento_tempo_gasto').timepicker({
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

		closeText:"Fechar",
		currentText: ''
	});
	$('#atendimento_tempo_gasto').click(function(){
		$('.ui-datepicker-current').css('display','none'); // oculta o 'currentText'
	});
	// atendimento_atendimento_tempo_gasto
	
	// cpf_cnpj
	$("input[name=cpf_cnpj]").numeric();
	$("input[name=cpf_cnpj]").blur(function() {
		
		var cpf_cnpj_campo = $(this);
		var cpf_cnpj = $(this).val();
			
		// remove pontuações
		cpf_cnpj = cpf_cnpj.replace('.','');
		cpf_cnpj = cpf_cnpj.replace('.','');
		cpf_cnpj = cpf_cnpj.replace('-','');
		cpf_cnpj = cpf_cnpj.replace('/','');
		// fim - remove pontuações
		
		if (cpf_cnpj.length == 11) { // utilizar validação do CPF
		
			var retorno = validaCPF(cpf_cnpj);
			if(retorno==false){
				alert("CPF Inválido!");
				setTimeout(function(){ cpf_cnpj_campo.focus()}, 50);
				return false;
			}
			
		} else if (cpf_cnpj.length == 14) { // utilizar a validação do CNPJ
		
			var retorno = validaCNPJ(cpf_cnpj);
			if(retorno==false){
				alert("CNPJ Inválido!");
				setTimeout(function(){ cpf_cnpj_campo.focus()}, 50);
				return false;
			}
			
		} else if (cpf_cnpj.length > 0) { // retorna tamanho inválido
			alert('Tamanho inválido');
			setTimeout(function() {cpf_cnpj_campo.focus();}, 50);
			return false;
		}
		
	});
	// fim - cpf_cnpj
	
    // abrir agenda
	$('#ver_agenda').click(function() {		
		
		var usuario_responsavel = $("select[name=usuario_responsavel]").val();
		data_atual = $('#data_agendamento_inicio').val();

		tb_show("Agenda","agenda_popup.php?usuario_atual="+usuario_responsavel+"&data_atual="+data_atual+"&height=<? echo $prospeccao_editar_tabela_height-100; ?>&width=<? echo $prospeccao_editar_tabela_width-40; ?>&placeValuesBeforeTB_=savedValues&TB_iframe=true","");		
		return false;
		
	});
	// fim - abrir agenda
			
	// calendário -------------------------------------------------------------
	// data_agendamento_inicio
	var data_agendamento_inicio = $('#data_agendamento_inicio');
	
	data_agendamento_inicio.datetimepicker({
							   
		showOn: "button",
		buttonImage: "css/images/calendar.gif",
		buttonImageOnly: true,									
		showSecond: false,
		minDateTime: new Date(<?php echo time() * 1000 ?>),
		<? if($prospeccao_nivel_interesse_reagendamento <> NULL){ ?>
		maxDateTime: new Date(<?php echo strtotime("+$prospeccao_nivel_interesse_reagendamento days", strtotime(date('Y-m-d 23:59:59'))) * 1000 ?>),
		<? } ?>
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
			$('#prospeccao_agenda_tipo').val('');
			
		},
		onChangeMonthYear: function(selectedDateTime) {
			$('#data_agendamento').val('');
			$('#agendamento_tempo').val('');
			$('#prospeccao_agenda_tipo').val('');
		},
		onClose: function(selectedDateTime){

			if(selectedDateTime=="  -  -       :  " || selectedDateTime==""){
				$('#data_agendamento').val('');
				$('#agendamento_tempo').val('');
				$('#prospeccao_agenda_tipo').val('');
			}
			
		},
		onSelect: function (selectedDateTime){
			$('#data_agendamento').val('');
			$('#agendamento_tempo').val('');
			$('#prospeccao_agenda_tipo').val('');
		}
		
	});
	// fim - data_agendamento_inicio
	
	// baixa_contrato_data
	var baixa_contrato_data = $('#baixa_contrato_data');
	baixa_contrato_data.datepicker({ 					
		showOn: "button",
		buttonImage: "css/images/calendar.gif",
		buttonImageOnly: true,									
		minDate: -<? echo $row_parametros['prospeccao_tempo_retroativo_data_contrato']; ?>,
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
	
	// data_agendamento_inicio - verifica se é uma data válida/agenda auto
    $('#data_agendamento_inicio').blur(function(){

		var campo = $(this);
			
		// erro
		var erro = funcao_verifica_data_hora_valida(campo, <?php echo time() * 1000 ?>) // chamada da função (retorna 0/1)		
		if(erro==1){
			
			alert("Data inválida");
			$('#data_agendamento_inicio').val('');
			$('#agendamento_tempo').val('');
			$('#prospeccao_agenda_tipo').val('');
			$('#data_agendamento').val('');
			setTimeout(function() {campo.focus();}, 100);
			return false;
			
		}
		// fim - erro
		
		// agenda auto
		else if($(this).val().length == 16) {
			
			$('#agendamento_tempo').val('');
			$('#prospeccao_agenda_tipo').val('');
			$('#data_agendamento').val('');
					
			var usuario_responsavel = $("select[name=usuario_responsavel]").val();
			data_atual = $('#data_agendamento_inicio').val();
	
			tb_show("Agenda","agenda_popup.php?usuario_atual="+usuario_responsavel+"&data_atual="+data_atual+"&height=<? echo $prospeccao_editar_tabela_height-100; ?>&width=<? echo $prospeccao_editar_tabela_width-40; ?>&placeValuesBeforeTB_=savedValues&TB_iframe=true","");		
			return false;
	
		}
		// fim - agenda auto
		
    });
	// fim - data_agendamento_inicio - verifica se é uma data válida/agenda auto

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
		var new_date = new Date ( date );
		
		var minutes = parseInt($("#agendamento_tempo").val());
		
		// Add the minutes to current date to arrive at the new date
		new_date.setMinutes ( date.getMinutes() + minutes );

		date1 = new_date.format('dd-mm-yyyy HH:MM'); // date.format.js
		
		$("#data_agendamento").val(date1);
		
	});
	// fim - agendamento_tempo
	
	// baixa_contrato
	$("#baixa_contrato").change(function(){
		
		var baixa_tipo_atual = $("input[name='baixa_tipo']:checked").val();
		var baixa_contrato_atual = $("#baixa_contrato").val();
		
		if(baixa_tipo_atual != '' && baixa_contrato_atual != ''){
			
			// post
			$.post("prospeccao_consulta_contrato.php", 
				  {contrato:baixa_contrato_atual},
				  function(valor){
					 
					 if(valor == 0){
						 
						 alert('Contrato não localizado. Por favor verifique.');
						 $("#baixa_contrato").val('')
						 return false;
						 
					 }
					 
				  }
			)
			// fim - post
		
		}
		
		return false;
		
	});
	// fim - baixa_contrato
	
	var baixa_contrato_data_erro = 0;
	
	// baixa_contrato_data
    $('#baixa_contrato_data').blur(function(){

		var campo = $(this);
			
		baixa_contrato_data_erro = funcao_verifica_data_valida(campo) // chamada da função (retorna 0/1)
		
		// confere data com XX dias ou menos
		if(baixa_contrato_data_erro==0 && campo.val().length == 10){
			
			// data_entrada
			value = campo.val();
			var quebraDE = value.split("-");
			
			var diaDE = quebraDE[0];
			var mesDE = quebraDE[1];
			var anoDE = quebraDE[2].substr(0,4);
			
			var data_entrada = anoDE+'/'+mesDE+'/'+diaDE+ ' 23:59:59';

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
			
			if(hojeDE.getTime() < hojeDA.getTime() || hojeDE.getTime() > hoje.getTime()){baixa_contrato_data_erro = 1;}
			
		}
		// fim - confere data com XX dias ou menos
		
		// baixa_contrato_data_erro
		if(baixa_contrato_data_erro==1){
			
			alert("Data inválida");
			$('#baixa_contrato_data').val('');
			setTimeout(function() {campo.focus();}, 100);
			return false;
			event.preventDefault();
			
		} else {
			
			baixa_contrato_data_erro = 0;
			
		}
		// fim - baixa_contrato_data_erro
		
    });
	// fim - baixa_contrato_data
	
	// valor_venda
	$('select[id="implantacao_tempo_adicional"]').attr('disabled', true);
	$('select[id="implantacao_tempo_adicional"]').val('');
	
	$("input[name=valor_venda]").blur(function() {
													 
		if(baixa_contrato_data_erro == 0){
		
			var valor_atual = $(this).val();
			
			if(valor_atual == "0,00"){
			
				var r = confirm("Confirma que a venda não tem valor?");
				
				if (r==true){
					
					$('select[id="implantacao_tempo_adicional"]').attr('disabled', true);
					$('select[id="implantacao_tempo_adicional"]').val('');
					
				} else {
	
					$('select[id="implantacao_tempo_adicional"]').attr('disabled', true);
					$('select[id="implantacao_tempo_adicional"]').val('');
					setTimeout(function(){ valor_venda.focus()}, 50);
					return false;
					
				}
				
			} else {
				
				$('select[id="implantacao_tempo_adicional"]').attr('disabled', false);
				
			}
		
		}
		
	});
	// fim - valor_venda
	
	// valor_treinamento
	$('select[id="treinamento_tempo"]').attr('disabled', true);
	$('select[id="treinamento_tempo"]').val('');
	
	$("input[name=valor_treinamento]").blur(function() {
													 
		if(baixa_contrato_data_erro == 0){
		
			var valor_atual = $(this).val();
			
			if(valor_atual == "0,00"){
			
				var r = confirm("Confirma que o treinamento não tem valor?");
				
				if (r==true){
					
					$('select[id="treinamento_tempo"]').attr('disabled', true);
					$('select[id="treinamento_tempo"]').val('');
					
				} else {
	
					$('select[id="treinamento_tempo"]').attr('disabled', true);
					$('select[id="treinamento_tempo"]').val('');
					setTimeout(function(){ valor_treinamento.focus()}, 50);
					return false;
					
				}
				
			} else {
				
				$('select[id="treinamento_tempo"]').attr('disabled', false);
				
			}
		
		}
		
	});
	// fim - valor_treinamento

});
</script>
</head>
<body>

<div class="div_solicitacao_linhas">
<table cellspacing="0" cellpadding="0" width="100%">
	<tr>
		<td style="text-align:left">
		Prospecção número: <?php echo $row_prospeccao['id']; ?>
		</td>

		<td style="text-align: right">
		&lt;&lt; <a href="prospeccao_editar.php?id_prospeccao=<?php echo $_GET['id_prospeccao']; ?>" target="_top">Voltar</a>
        </td>
	</tr>
</table>
</div>


<div class="div_solicitacao_linhas3">
<table cellspacing="0" cellpadding="0">
	<tr>
		<td style="text-align: left">
		<strong><? echo $_GET['acao']; ?> </strong> 
        <? if($_GET['resposta']=="sim" or $_GET['resposta']=="nao"){echo " (".$_GET['resposta'].") ";} ?>
        <? if(isset($_GET['id_agenda'])!=""){echo " (".$_GET['id_agenda'].") ";} ?>
		</td>
	</tr>
</table>
</div>

<div class="div_solicitacao_linhas3">
<table cellspacing="0" cellpadding="0">
	<tr>
		<td style="text-align: left">
		Cliente: <?php echo  utf8_encode($row_prospeccao['nome_razao_social']); ?>
		</td>
	</tr>
</table>
</div>

<!-- Encerrar (mensagem) -->
<? if($_GET['situacao']=="editar" and 
			(
			$_GET["acao"] == "Encerrar"
			) and 
			$row_prospeccao['status_flag'] == 'a' and $botao_encerrar_status == 0
){ ?>
<div class="div_solicitacao_linhas4">
<table cellspacing="0" cellpadding="0" width="100%">
  <tr>
    <td style="text-align: left">
	<span style="font-weight: bold; color: #F00;">
    O encerramento por 'Venda' somente será liberado após preenchimento correto dos campos: 
    Razão social
    •	CPF/CNPJ
    •	RG/Inscrição Estadual
    •	CEP
    •	Endereço
    •	Bairro
    •	Cidade
    •	UF
    •	Telefone.
    </span>
    </td>
  </tr>
</table>
</div>
<? } ?>
<!-- fim - Encerrar (mensagem) -->

<!-- Agendamento atual -->
<? if($_GET['situacao']=="editar" and
			
	$totalRows_agenda_agendado > 0 and 
    (
    ($_GET["acao"] == "Agendamento" or $_GET["acao"] == "Finalizar agendamento") or 
    $_GET['acao']=="Reagendar"
    )
){ ?>

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

<!-- Contato -->
<? if(
	  $_GET['situacao']=="editar" and 
	  $_GET['acao']=="Contato" and 
	  $totalRows_prospeccao_contato > 0
){ ?>

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
            <? $contador_prospeccao_contato = 0; ?>
            <? do{ ?>
            <? $contador_prospeccao_contato = $contador_prospeccao_contato + 1; ?>
            <tr>
            
                <td style="padding: 5px;" align="left">
                <span style=" color: <? if(($contador_prospeccao_contato%2)==0){echo "#F00";}else{echo "#000";} ?>;">
                    <? if($row_prospeccao_contato['data']!=""){echo date('d-m-Y H:i', strtotime($row_prospeccao_contato['data']));} ?>
                </span>
                </td>
                
                <td style="padding: 5px;" align="left">
                  <span style=" color: <? if(($contador_prospeccao_contato%2)==0){echo "#F00";}else{echo "#000";} ?>;">
                    <? echo $row_prospeccao_contato['usuarios_nome']; ?>
                    </span>
                </td>

                <td style="padding: 5px;" align="left">
                  <span style=" color: <? if(($contador_prospeccao_contato%2)==0){echo "#F00";}else{echo "#000";} ?>;">
                    <? echo $row_prospeccao_contato['responsavel']; ?>
                    </span>
                </td>
                
                <td style="padding: 5px;" align="left">
                  <span style=" color: <? if(($contador_prospeccao_contato%2)==0){echo "#F00";}else{echo "#000";} ?>;">
                    <? echo $row_prospeccao_contato['telefone']; ?>
                    </span>
                </td>
    
                <td style="padding: 5px;" align="left">
                <span style=" color: <? if(($contador_prospeccao_contato%2)==0){echo "#F00";}else{echo "#000";} ?>;">
                    <? echo $row_prospeccao_contato['descricao']; ?>
                </span>
                </td>
            
            </tr>
            <?php } while ($row_prospeccao_contato = mysql_fetch_assoc($prospeccao_contato)); ?>
            </tbody>
            </table>
        </td>
      </tr>
    </table>
    </div>
    
<? } ?>  
<!-- fim - Contato -->

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

<div class="div_solicitacao_linhas2">
<table cellspacing="0" cellpadding="0">
	<tr>
		<td style="text-align: left">
        <form id="form" name="form" method="POST" action="<?php echo $editFormAction; ?>" class="cmxform" target="_top">

		<!-- contato_responsavel -->
		<? if(
		($_GET['situacao']=="editar" and 
				$_GET["acao"] == "Contato"
		)
        ){ ?>
            <div style="padding-bottom: 10px;">
                <div class="label_solicitacao2">Contato*:</div>
                <input type="text" name="contato_responsavel" id="contato_responsavel" style="width:290px" />
            </div>
		<? } ?>
		<!-- fim - contato_responsavel -->
        

		<!-- contato_telefone -->
		<? if(
		($_GET['situacao']=="editar" and 
				$_GET["acao"] == "Contato"
		)
        ){ ?>
            <div style="padding-bottom: 10px;">
                <div class="label_solicitacao2">Telefone*:</div>
                <input type="text" name="contato_telefone" id="contato_telefone" style="width:150px" maxlength="13" />
            </div>
		<? } ?>
		<!-- fim - contato_telefone -->


		<!-- Finalizar agendamento -->
		<? if($_GET['situacao']=="editar" and 
					(
					($_GET["acao"] == "Finalizar agendamento")
					)
		){ ?>
		<div class="label_solicitacao2">Finalizar agendamento:</div>
		<div class="div_solicitacao_linhas4">
		 		
			<!-- agenda_usuario_responsavel -->
			<div style="padding-bottom: 10px;" id="div_agenda_usuario_responsavel">
				<?
				// agenda_usuario_responsavel
				mysql_select_db($database_conexao, $conexao);
				
				$query_agenda_usuario_responsavel = sprintf("
				SELECT IdUsuario, nome, praca 
				FROM usuarios 
				WHERE status = 1 and praca = %s 
				ORDER BY praca, nome ASC", 
				GetSQLValueString($row_prospeccao['praca'], "text"));
				
				$agenda_usuario_responsavel = mysql_query($query_agenda_usuario_responsavel, $conexao) or die(mysql_error());
				$row_agenda_usuario_responsavel = mysql_fetch_assoc($agenda_usuario_responsavel);
				$totalRows_agenda_usuario_responsavel = mysql_num_rows($agenda_usuario_responsavel);
				// fim - agenda_usuario_responsavel
				?>
				<div class="label_solicitacao2">Usuário responsável*:</div>
				<select name="agenda_id_usuario_responsavel_final" id="agenda_id_usuario_responsavel_final">
				<option value="">Escolha o usuário responsável ...</option>
				<?php
				do {  
				?>
				<option title="<?php echo $row_agenda_usuario_responsavel['IdUsuario']?>" value="<?php echo $row_agenda_usuario_responsavel['IdUsuario']?>"><?php echo $row_agenda_usuario_responsavel['nome']?> [<?php echo $row_agenda_usuario_responsavel['praca']?>]</option>
				
				<?php
				} while ($row_agenda_usuario_responsavel = mysql_fetch_assoc($agenda_usuario_responsavel));
				$rows = mysql_num_rows($agenda_usuario_responsavel);
				if($rows > 0) {
				mysql_data_seek($agenda_usuario_responsavel, 0);
				$row_agenda_usuario_responsavel = mysql_fetch_assoc($agenda_usuario_responsavel);
				}
				?>
				</select>
				<? mysql_free_result($agenda_usuario_responsavel); ?>
			</div>
			<!-- fim - agenda_usuario_responsavel -->
			
			
			<!-- data_atendimento -->
			<? if($_GET['situacao']=="editar" and 
						(
						($_GET["acao"] == "Finalizar agendamento")
						)
			){ ?>
				<div style="padding-bottom: 10px;" id="div_data_atendimento">
					<div class="label_solicitacao2">Data do atendimento*:</div>
					<input name="data_atendimento" type="text" id="data_atendimento" style="width: 170px;" maxlength="10" />
				</div>
			<? } ?>
			<!-- fim - data_atendimento -->
			
			
			<!-- hora_atendimento_inicio -->
			<? if($_GET['situacao']=="editar" and 
						(
						($_GET["acao"] == "Finalizar agendamento")
						)
			){ ?>
				<div style="padding-bottom: 10px;" id="div_hora_atendimento_inicio">
					<div class="label_solicitacao2">hora do atendimento (inicial)*:</div>
					<input name="hora_atendimento_inicio" type="text" id="hora_atendimento_inicio" style="width: 100px;" maxlength="5" />
				</div>
			<? } ?>
			<!-- fim - hora_atendimento_inicio -->
			
			
			<!-- hora_atendimento_fim -->
			<? if($_GET['situacao']=="editar" and 
						(
						($_GET["acao"] == "Finalizar agendamento")
						)
			){ ?>
				<div style="padding-bottom: 10px;" id="div_hora_atendimento_fim">
					<div class="label_solicitacao2">hora do atendimento (final)*:</div>
					<input name="hora_atendimento_fim" type="text" id="hora_atendimento_fim" style="width: 100px;" maxlength="5" />
				</div>
			<? } ?>
			<!-- fim - hora_atendimento_fim -->
			
			
			<!-- atendimento_tempo_gasto -->
			<? if($_GET['situacao']=="editar" and 
						(
						($_GET["acao"] == "Finalizar agendamento")
						)
			){ ?>
				<div style="padding-bottom: 10px;" id="div_atendimento_tempo_gasto">
					<div class="label_solicitacao2">Tempo gasto (dias - horas - minutos)*:</div>
					<input name="atendimento_tempo_gasto" type="text" id="atendimento_tempo_gasto" style="width: 100px;" maxlength="10" readonly="readonly" />
				</div>
			<? } ?>
			<!-- fim - atendimento_tempo_gasto -->
			
			
			<!-- atendimento_receptor -->
			<? if($_GET['situacao']=="editar" and 
						(
						($_GET["acao"] == "Finalizar agendamento")
						)
			){ ?>
				<div style="padding-bottom: 10px;" id="div_atendimento_receptor">
					<div class="label_solicitacao2">Prospectado/Contactado*:</div>
					<input name="atendimento_receptor" type="text" id="atendimento_receptor" style="width: 300px;" maxlength="100" />
				</div>
			<? } ?>
			<!-- fim - atendimento_receptor -->

		</div>
		<? } ?>
		<!-- fim - Finalizar agendamento -->
		
		
		<!-- Atualizar dados da prospecção -->
		<? if($_GET['situacao']=="editar" and 
					(
					($_GET["acao"] == "Finalizar agendamento")
					)
		){ ?>
		<div class="label_solicitacao2">Atualizar dados da prospecção:</div>
		<div class="div_solicitacao_linhas4">
		 					
			
			<!-- nivel_interesse -->
			<? if($_GET['situacao']=="editar" and 
						(
						($_GET["acao"] == "Finalizar agendamento")
						)
			){ ?>
				<div style="padding-bottom: 10px;" id="div_data_atendimento">
					<div class="label_solicitacao2">Nível de interesse*:</div>
					<select name="nivel_interesse" id="nivel_interesse" style="width: 120px;">					
						<option value="" <?php if (!(strcmp("", $row_prospeccao['nivel_interesse']))) {echo "selected=\"selected\"";} ?>>...</option>
						<option value="a" <?php if (!(strcmp("a", $row_prospeccao['nivel_interesse']))) {echo "selected=\"selected\"";} ?>>Alto</option>
						<option value="m" <?php if (!(strcmp("m", $row_prospeccao['nivel_interesse']))) {echo "selected=\"selected\"";} ?>>Médio</option>
						<option value="b" <?php if (!(strcmp("b", $row_prospeccao['nivel_interesse']))) {echo "selected=\"selected\"";} ?>>Baixo</option>
						<option value="n" <?php if (!(strcmp("n", $row_prospeccao['nivel_interesse']))) {echo "selected=\"selected\"";} ?>>Nenhum</option>
					</select>
				</div>
			<? } ?>
			<!-- fim - nivel_interesse -->
			
			
			<!-- proposta_valor -->
			<? if($_GET['situacao']=="editar" and 
						(
						($_GET["acao"] == "Finalizar agendamento")
						)
			){ ?>
				<div style="padding-bottom: 10px;" id="div_proposta_valor">
					<div class="label_solicitacao2">Valor da Proposta/Orçamento:</div>
					<input name="proposta_valor" type="text" id="proposta_valor" style="width: 170px;" maxlength="19" value="<? echo $row_prospeccao['proposta_valor']; ?>" />
				</div>
			<? } ?>
			<!-- fim - proposta_valor -->
			
			
			<!-- proposta_recursos -->
			<? if($_GET['situacao']=="editar" and 
						(
						($_GET["acao"] == "Finalizar agendamento")
						)
			){ ?>
				<div style="padding-bottom: 10px;" id="div_proposta_recursos">
					<div class="label_solicitacao2">Recurso da Proposta/Orçamento:</div>
					<input name="proposta_recursos" type="text" id="proposta_recursos" style="width: 750px;" maxlength="100" value="<? echo $row_prospeccao['proposta_recursos']; ?>" />
				</div>
			<? } ?>
			<!-- fim - proposta_recursos -->
			
			
			<!-- proposta_validade -->
			<? if($_GET['situacao']=="editar" and 
						(
						($_GET["acao"] == "Finalizar agendamento")
						)
			){ ?>
				<div style="padding-bottom: 10px;" id="div_proposta_validade">
					<div class="label_solicitacao2">Validade da Proposta/Orçamento:</div>
					<input name="proposta_validade" type="text" id="proposta_validade" style="width: 170px;" maxlength="10" value="<? echo $row_prospeccao['proposta_validade']; ?>" />
				</div>
			<? } ?>
			<!-- fim - proposta_validade -->

		</div>
		<? } ?>
		<!-- fim - Atualizar dados da prospecção -->
		
		
		<!-- acao_agenda -->
		<? if($_GET['situacao']=="editar" and 
					(
					($_GET["acao"] == "Agendamento" or $_GET["acao"] == "Finalizar agendamento")
					)
		){ ?>
            <div style="padding-bottom: 10px;" id="div_acao_agenda">
                <div class="label_solicitacao2">Agendar/Solicitar novo agendamento:</div>
                <fieldset style="border: 1px solid #CCCCCC; padding: 5px;">
                    <input type="radio" name="acao_agenda" id="acao_agenda" value="a"> Novo agendamento
                    <input type="radio" name="acao_agenda" id="acao_agenda" value="s"> Solicitar agendamento
                </fieldset>
                <label for="acao_agenda" class="error">Selecione uma das opções</label>
            </div>
		<? } ?>
		<!-- acao_agenda -->
        
        
		<!-- usuario_responsavel -->
		<? 
		if(
		   (
			$_GET['situacao']=="editar" and 
					(
					$_GET['acao']=="Alterar usuário responsável" or 
					$_GET["acao"] == "Agendamento" or 
					$_GET["acao"] == "Finalizar agendamento" or 
					$_GET['acao']=="Reagendar"
					)
			)
		   
		   or 
		   
		   (
			($_GET['situacao']=="analisada" or $_GET['situacao']=="em negociação") and 
					$_GET['acao']=="Encaminhar"
			)
		){ ?>
        	<div style="padding-bottom: 10px;" id="div_usuario_responsavel">
				<?
				// usuario_responsavel
                mysql_select_db($database_conexao, $conexao);
				
                $query_usuario_responsavel = sprintf("
													 SELECT IdUsuario, nome, praca FROM usuarios WHERE status = 1 and praca = %s ORDER BY praca, nome ASC", 
													 GetSQLValueString($row_prospeccao['praca'], "text"));
				
                $usuario_responsavel = mysql_query($query_usuario_responsavel, $conexao) or die(mysql_error());
                $row_usuario_responsavel = mysql_fetch_assoc($usuario_responsavel);
                $totalRows_usuario_responsavel = mysql_num_rows($usuario_responsavel);
				// fim - usuario_responsavel
                ?>
                <div class="label_solicitacao2">Usuário responsável*:</div>
                <select name="usuario_responsavel" id="usuario_responsavel">
                <option value="">Escolha o usuário responsável ...</option>
                <?php
                do {  
                ?>
                <option title="<?php echo $row_usuario_responsavel['IdUsuario']?>" value="<?php echo $row_usuario_responsavel['IdUsuario']?>"><?php echo $row_usuario_responsavel['nome']?> [<?php echo $row_usuario_responsavel['praca']?>]</option>
                
                <?php
                } while ($row_usuario_responsavel = mysql_fetch_assoc($usuario_responsavel));
                $rows = mysql_num_rows($usuario_responsavel);
                if($rows > 0) {
                mysql_data_seek($usuario_responsavel, 0);
                $row_usuario_responsavel = mysql_fetch_assoc($usuario_responsavel);
                }
                ?>
                </select>
                <? mysql_free_result($usuario_responsavel); ?>
			</div>
		<? } ?>
		<!-- fim - usuario_responsavel -->
                

		<!-- data_agendamento inicio -->
		<? if($_GET['situacao']=="editar" and 
					(
					$_GET["acao"] == "Agendamento" or 
					$_GET["acao"] == "Finalizar agendamento" or
					$_GET['acao']=="Reagendar"
					)
		){ ?>
            <div style="padding-bottom: 10px;" id="div_data_agendamento_inicio">
                <div class="label_solicitacao2">Data Inicial (agendamento)*:</div>
                <input name="data_agendamento_inicio" type="text" id="data_agendamento_inicio" style="width: 170px;" maxlength="19" value="<? 
				if($_GET['acao']=="Reagendar"){ 
					echo "";
				} else
				if($_GET['acao']=="Agendamento" or $_GET['acao']=="Finalizar agendamento"){ 
					echo "";
				} else { 
					echo date('d-m-Y H:i:s'); 
				} ?>" />
                <br>
                <a href="#" id="ver_agenda" name="ver_agenda" style="color: #03C; font-weight: bold; padding-left: 5px;">Ver agenda</a>
            </div>
		<? } ?>
		<!-- fim - data_agendamento inicio -->
        
                 
		<!-- agendamento_tempo/data_agendamento -->
		<? if($_GET['situacao']=="editar" and 
					(
					$_GET["acao"] == "Agendamento" or 
					$_GET["acao"] == "Finalizar agendamento" or
					$_GET['acao']=="Reagendar"
					)
		){ ?>

			<!-- agendamento_tempo -->
            <div style="padding-bottom: 10px;" id="div_agendamento_tempo">
                <div class="label_solicitacao2">Tempo*: </div>
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
            </div>
			<!-- fim - agendamento_tempo -->

			<!-- data_agendamento -->
            <div style="padding-bottom: 10px;" id="div_data_agendamento">
                <div class="label_solicitacao2">Data Final (agendamento)*:</div>
                <input name="data_agendamento" type="text" id="data_agendamento" style="width: 170px;" readonly="readonly" value="<? 
				if($_GET['acao']=="Reagendar"){ 
					echo "";
				} else
				if($_GET['acao']=="Agendamento" or $_GET['acao']=="Finalizar agendamento"){ 
					echo "";
				} else { 
					echo date('d-m-Y H:i:s'); 
				} ?>" />
            </div>
			<!-- fim - data_agendamento -->
                        
		<? } ?>
		<!-- fim - agendamento_tempo/data_agendamento -->
		
		<!-- prospeccao_agenda_tipo -->
		<? if($_GET['situacao']=="editar" and 
					(
					$_GET["acao"] == "Agendamento" or 
					$_GET["acao"] == "Finalizar agendamento"
					)
		){ ?>
        	<div style="padding-bottom: 10px;" id="div_prospeccao_agenda_tipo">
				<?
				// prospeccao_agenda_tipo
                mysql_select_db($database_conexao, $conexao);
                $query_prospeccao_agenda_tipo = "SELECT prospeccao_agenda_tipo.* FROM prospeccao_agenda_tipo ORDER BY id ASC";
                $prospeccao_agenda_tipo = mysql_query($query_prospeccao_agenda_tipo, $conexao) or die(mysql_error());
                $row_prospeccao_agenda_tipo = mysql_fetch_assoc($prospeccao_agenda_tipo);
                $totalRows_prospeccao_agenda_tipo = mysql_num_rows($prospeccao_agenda_tipo);
				// fim - prospeccao_agenda_tipo
                ?>
                <div class="label_solicitacao2">Tipo de Agendamento*:</div>
                <select name="prospeccao_agenda_tipo" id="prospeccao_agenda_tipo">
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
			</div>
		<? } ?>
		<!-- fim - prospeccao_agenda_tipo -->       
        
		<!-- descricao_agendamento -->
		<? if($_GET['situacao']=="editar" and 
					(
					$_GET["acao"] == "Agendamento" or 
					$_GET["acao"] == "Finalizar agendamento" or
					$_GET['acao']=="Reagendar"
					)
		){ ?>
            <div style="padding-bottom: 10px;" id="div_descricao_agendamento">
                <div class="label_solicitacao2">Descrição (agendamento):</div>
                
                <textarea name="descricao_agendamento" id="descricao_agendamento" style="width: 760px; height: 60px" /><? if($_GET['acao']=="Reagendar"){ echo $row_agenda['descricao']; } ?></textarea>

            </div>
		<? } ?>
		<!-- fim - descricao_agendamento -->


		<!-- status -->
		<? if($_GET['situacao']=="editar" and 
					(
					$_GET['acao']=="Alterar status" or 
					$_GET['acao']=="Agendamento" or
					$_GET['acao']=="Finalizar agendamento" or
					$_GET['acao']=="Reagendar"
					)
		){ ?>
            <div style="padding-bottom: 10px;" id="div_status">
            <div class="label_solicitacao2">Status*:</div>
            
            <select name="status" id="status" style="width: 400px;">
            	<option value="">Selecione ...</option>
                <option value="aguardando atendente">aguardando atendente</option>
                <option value="aguardando retorno do cliente">aguardando retorno do cliente</option>
            </select>
            
            </div>
		<? } ?>
		<!-- fim - status -->
        
        
		<!-- ativo_passivo -->
		<? if(
		($_GET['situacao']=="editar" and $_GET['acao']=="Alterar tipo de prospect")
        ){ ?>
            <div style="padding-bottom: 10px;">
                <div class="label_solicitacao2">Tipo de prospect:</div>
                <fieldset style="border: 1px solid #CCCCCC; padding: 5px;">
                    <input type="radio" name="ativo_passivo" id="ativo_passivo" value="a" <?php if (!(strcmp($row_prospeccao['ativo_passivo'], 'a'))) {echo "checked";} ?>> Ativo 
                    <input type="radio" name="ativo_passivo" id="ativo_passivo" value="p" <?php if (!(strcmp($row_prospeccao['ativo_passivo'], 'p'))) {echo "checked";} ?>> Passivo 
                </fieldset>
                <label for="ativo_passivo" class="error">Selecione uma das opções</label>
            </div>
		<? } ?>
		<!-- ativo_passivo -->
        
        
		<!-- indicado_por -->
		<? if(
		($_GET['situacao']=="editar" and $_GET['acao']=="Alterar indicado por")
        ){ ?>
            <div style="padding-bottom: 10px;">
                <div class="label_solicitacao2">Nome do indicador*:</div>
                <input type="text" name="indicado_por" id="indicado_por" value="<? echo $row_prospeccao['indicado_por']; ?>" style="width:760px" />
            </div>
		<? } ?>
		<!-- fim - indicado_por -->
       
        
		<!-- nome_razao_social -->
		<? if(
		($_GET['situacao']=="editar" and $_GET['acao']=="Alterar cliente")
        ){ ?>
            <div style="padding-bottom: 10px;">
                <div class="label_solicitacao2">Cliente (nome/razão social)*:</div>
                <input type="text" name="nome_razao_social" id="nome_razao_social" value="<? echo $row_prospeccao['nome_razao_social']; ?>" style="width:760px" />
            </div>
		<? } ?>
		<!-- fim - nome_razao_social -->


		<!-- nome_fantasia -->
		<? if(
		($_GET['situacao']=="editar" and $_GET['acao']=="Alterar nome fantasia")
        ){ ?>
            <div style="padding-bottom: 10px;">
                <div class="label_solicitacao2">Nome fantasia*:</div>
                <input type="text" name="fantasia" id="fantasia" value="<? echo $row_prospeccao['fantasia']; ?>" style="width:760px" />
            </div>
		<? } ?>
		<!-- fim - nome_fantasia -->


		<!-- cpf_cnpj -->
		<? if(
		($_GET['situacao']=="editar" and $_GET['acao']=="Alterar cpf/cnpj")
        ){ ?>
            <div style="padding-bottom: 10px;">
                <div class="label_solicitacao2">CPF/CNPJ*:</div>
                <input type="text" name="cpf_cnpj" id="cpf_cnpj" value="<? echo $row_prospeccao['cpf_cnpj']; ?>" style="width:760px" maxlength="14" />
            </div>
		<? } ?>
		<!-- fim - cpf_cnpj -->


		<!-- rg_inscricao -->
		<? if(
		($_GET['situacao']=="editar" and $_GET['acao']=="Alterar rg/inscrição estadual")
        ){ ?>
            <div style="padding-bottom: 10px;">
                <div class="label_solicitacao2">RG/Inscrição Estadual*:</div>
                <input type="text" name="rg_inscricao" id="rg_inscricao" value="<? echo $row_prospeccao['rg_inscricao']; ?>" style="width:760px" />
            </div>
		<? } ?>
		<!-- fim - rg_inscricao -->


		<!-- localizacao -->
		<? if(
		($_GET['situacao']=="editar" and $_GET['acao']=="Alterar endereço")
        ){ ?>
            <div style="padding-bottom: 10px;">
            
                <div class="label_solicitacao2">CEP*:</div>
                <input type="text" name="cep" id="cep" value="<? echo $row_prospeccao['cep']; ?>" style="width:300px" />
                
                
                <div class="label_solicitacao2">Endereço*:</div>
                <input type="text" name="endereco" id="endereco" value="<? echo $row_prospeccao['endereco']; ?>" style="width:760px" />
                <br>
                
                <table width="100%" border="0" cellspacing="0" cellpadding="0">
                  <tr>
                    <td width="220">
                    <div class="label_solicitacao2">Número:</div>
                    <input type="text" name="endereco_numero" id="endereco_numero" value="<? echo $row_prospeccao['endereco_numero']; ?>" style="width:200px" />
                    </td>
                    
                    <td>
                    <div class="label_solicitacao2">Complemento:</div>
                    <input type="text" name="endereco_complemento" id="endereco_complemento" value="<? echo $row_prospeccao['endereco_complemento']; ?>" style="width:200px" />
                    </td>
                  </tr>
                </table>

                <div class="label_solicitacao2">Bairro*:</div>
                <input type="text" name="bairro" id="bairro" value="<? echo $row_prospeccao['bairro']; ?>" style="width:300px" />
                <br>
                
                <table width="100%" border="0" cellspacing="0" cellpadding="0">
                  <tr>
                    <td width="320">
                    <div class="label_solicitacao2">Cidade*:</div>
                    <input type="text" name="cidade" id="cidade" value="<? echo $row_prospeccao['cidade']; ?>" style="width:300px" />
                    <br>
                    </td>
                    
                    <td>
                    <div class="label_solicitacao2">Estado*:</div>
                    <select name="uf" id="uf" style="width: 100px;">
                        <option value="" <?php if (!(strcmp("", $row_prospeccao['uf']))) {echo "selected=\"selected\"";} ?>>Escolha...</option>
                        <option value="AC" <?php if (!(strcmp("AC", $row_prospeccao['uf']))) {echo "selected=\"selected\"";} ?>>AC</option>
                        <option value="AL" <?php if (!(strcmp("AL", $row_prospeccao['uf']))) {echo "selected=\"selected\"";} ?>>AL</option>
                        <option value="AM" <?php if (!(strcmp("AM", $row_prospeccao['uf']))) {echo "selected=\"selected\"";} ?>>AM</option>
                        <option value="AP" <?php if (!(strcmp("AP", $row_prospeccao['uf']))) {echo "selected=\"selected\"";} ?>>AP</option>
                        <option value="BA" <?php if (!(strcmp("BA", $row_prospeccao['uf']))) {echo "selected=\"selected\"";} ?>>BA</option>
                        <option value="CE" <?php if (!(strcmp("CE", $row_prospeccao['uf']))) {echo "selected=\"selected\"";} ?>>CE</option>
                        <option value="DF" <?php if (!(strcmp("DF", $row_prospeccao['uf']))) {echo "selected=\"selected\"";} ?>>DF</option>
                        <option value="ES" <?php if (!(strcmp("ES", $row_prospeccao['uf']))) {echo "selected=\"selected\"";} ?>>ES</option>
                        <option value="GO" <?php if (!(strcmp("GO", $row_prospeccao['uf']))) {echo "selected=\"selected\"";} ?>>GO</option>
                        <option value="MA" <?php if (!(strcmp("MA", $row_prospeccao['uf']))) {echo "selected=\"selected\"";} ?>>MA</option>
                        <option value="MG" <?php if (!(strcmp("MG", $row_prospeccao['uf']))) {echo "selected=\"selected\"";} ?>>MG</option>
                        <option value="MS" <?php if (!(strcmp("MS", $row_prospeccao['uf']))) {echo "selected=\"selected\"";} ?>>MS</option>
                        <option value="MT" <?php if (!(strcmp("MT", $row_prospeccao['uf']))) {echo "selected=\"selected\"";} ?>>MT</option>
                        <option value="PA" <?php if (!(strcmp("PA", $row_prospeccao['uf']))) {echo "selected=\"selected\"";} ?>>PA</option>
                        <option value="PB" <?php if (!(strcmp("PB", $row_prospeccao['uf']))) {echo "selected=\"selected\"";} ?>>PB</option>
                        <option value="PE" <?php if (!(strcmp("PE", $row_prospeccao['uf']))) {echo "selected=\"selected\"";} ?>>PE</option>
                        <option value="PI" <?php if (!(strcmp("PI", $row_prospeccao['uf']))) {echo "selected=\"selected\"";} ?>>PI</option>
                        <option value="PR" <?php if (!(strcmp("PR", $row_prospeccao['uf']))) {echo "selected=\"selected\"";} ?>>PR</option>
                        <option value="RJ" <?php if (!(strcmp("RJ", $row_prospeccao['uf']))) {echo "selected=\"selected\"";} ?>>RJ</option>
                        <option value="RN" <?php if (!(strcmp("RN", $row_prospeccao['uf']))) {echo "selected=\"selected\"";} ?>>RN</option>
                        <option value="RO" <?php if (!(strcmp("RO", $row_prospeccao['uf']))) {echo "selected=\"selected\"";} ?>>RO</option>
                        <option value="RR" <?php if (!(strcmp("RR", $row_prospeccao['uf']))) {echo "selected=\"selected\"";} ?>>RR</option>
                        <option value="RS" <?php if (!(strcmp("RS", $row_prospeccao['uf']))) {echo "selected=\"selected\"";} ?>>RS</option>
                        <option value="SC" <?php if (!(strcmp("SC", $row_prospeccao['uf']))) {echo "selected=\"selected\"";} ?>>SC</option>
                        <option value="SE" <?php if (!(strcmp("SE", $row_prospeccao['uf']))) {echo "selected=\"selected\"";} ?>>SE</option>
                        <option value="SP" <?php if (!(strcmp("SP", $row_prospeccao['uf']))) {echo "selected=\"selected\"";} ?>>SP</option>
                        <option value="TO" <?php if (!(strcmp("TO", $row_prospeccao['uf']))) {echo "selected=\"selected\"";} ?>>TO</option>
                    </select>
                    <br>
                    </td>
                  </tr>
                </table>

            </div>
		<? } ?>
		<!-- fim - localizacao -->
        
        
		<!-- telefone -->
		<? if(
		($_GET['situacao']=="editar" and $_GET['acao']=="Alterar telefone")
        ){ ?>
            <div style="padding-bottom: 10px;">
                <div class="label_solicitacao2">Telefone*:</div>
                <input type="text" name="telefone" id="telefone" value="<? echo $row_prospeccao['telefone']; ?>" style="width:760px" />
            </div>
		<? } ?>
		<!-- fim - telefone -->
       
        
		<!-- celular -->
		<? if(
		($_GET['situacao']=="editar" and $_GET['acao']=="Alterar celular")
        ){ ?>
            <div style="padding-bottom: 10px;">
                <div class="label_solicitacao2">Celular*:</div>
                <input type="text" name="celular" id="celular" value="<? echo $row_prospeccao['celular']; ?>" style="width:760px" />
            </div>
		<? } ?>
		<!-- fim - celular -->        
        
        
		<!-- responsavel_por_ti -->
		<? if(
		($_GET['situacao']=="editar" and $_GET['acao']=="Alterar responsável por T.I.")
        ){ ?>
            <div style="padding-bottom: 10px;">
                <div class="label_solicitacao2">Responsável por T.I.*:</div>
                <input type="text" name="responsavel_por_ti" id="responsavel_por_ti" value="<? echo $row_prospeccao['responsavel_por_ti']; ?>" style="width:760px" />
            </div>
		<? } ?>
		<!-- fim - responsavel_por_ti -->
        
        
		<!-- ramo_de_atividade -->
		<? if(
		($_GET['situacao']=="editar" and $_GET['acao']=="Alterar Ramo de atividade")
        ){ ?>
            <div style="padding-bottom: 10px;">
              <div class="label_solicitacao2">Ramo de atividade*:</div>
			<?php
            mysql_select_db($database_conexao, $conexao);
            $query_geral_tipo_ramo_atividade = "SELECT * FROM geral_tipo_ramo_atividade ORDER BY titulo ASC";
            $geral_tipo_ramo_atividade = mysql_query($query_geral_tipo_ramo_atividade, $conexao) or die(mysql_error());
            $row_geral_tipo_ramo_atividade = mysql_fetch_assoc($geral_tipo_ramo_atividade);
            $totalRows_geral_tipo_ramo_atividade = mysql_num_rows($geral_tipo_ramo_atividade);
            ?>
            <select name="ramo_de_atividade" id="ramo_de_atividade" style="width: 400px;">
            <option value="" <?php if (!(strcmp("", $row_prospeccao['ramo_de_atividade']))) {echo "selected=\"selected\"";} ?>>Escolha ...</option>
            <?php
            do {  
            ?>
            <option value="<?php echo $row_geral_tipo_ramo_atividade['titulo']?>"
			<?php if (!(strcmp($row_geral_tipo_ramo_atividade['titulo'], $row_prospeccao['ramo_de_atividade']))) {echo "selected=\"selected\"";} ?>>
			<?php echo $row_geral_tipo_ramo_atividade['titulo']?>
            </option>
            <?php
            } while ($row_geral_tipo_ramo_atividade = mysql_fetch_assoc($geral_tipo_ramo_atividade));
            $rows = mysql_num_rows($geral_tipo_ramo_atividade);
            if($rows > 0) {
            mysql_data_seek($geral_tipo_ramo_atividade, 0);
            $row_geral_tipo_ramo_atividade = mysql_fetch_assoc($geral_tipo_ramo_atividade);
            }
            ?>
            </select>
            <? mysql_free_result($geral_tipo_ramo_atividade); ?>
            </div>
		<? } ?>
		<!-- fim - ramo_de_atividade -->
        
        
		<!-- enquadramento_fiscal -->
		<? if(
		($_GET['situacao']=="editar" and $_GET['acao']=="Alterar enquadramento fiscal")
        ){ ?>
            <div style="padding-bottom: 10px;">
                <div class="label_solicitacao2">Enquadramento Fiscal:</div>
                <fieldset style="border: 1px solid #CCCCCC; padding: 5px;">
                    <input type="radio" name="enquadramento_fiscal" id="enquadramento_fiscal" value="Super Simples" <?php if (!(strcmp($row_prospeccao['enquadramento_fiscal'], 'Super Simples'))) {echo "checked";} ?>> Super Simples 
                    <input type="radio" name="enquadramento_fiscal" id="enquadramento_fiscal" value="Débito e Crédito" <?php if (!(strcmp($row_prospeccao['enquadramento_fiscal'], 'Débito e Crédito'))) {echo "checked";} ?>> Débito e Crédito 
                    <input type="radio" name="enquadramento_fiscal" id="enquadramento_fiscal" value="" <?php if (!(strcmp($row_prospeccao['enquadramento_fiscal'], ''))) {echo "checked";} ?>> Outro 
                    <input type="text" name="enquadramento_fiscal_outro" id="enquadramento_fiscal_outro" value="<? echo $row_prospeccao['enquadramento_fiscal_outro']; ?>" style="width:300px" />
                </fieldset>
                <label for="enquadramento_fiscal" class="error">Selecione uma das opções</label>               

            </div>
		<? } ?>
		<!-- enquadramento_fiscal -->
        
        
		<!-- informações fiscais -->
		<? if(
		($_GET['situacao']=="editar" and $_GET['acao']=="Alterar informações fiscais")
        ){ ?>
            <div style="padding-bottom: 10px;">
            
                <div class="label_solicitacao2" style="margin-bottom: 20px;">
                					
					<input type="checkbox" name="exige_cupom_fiscal" id="exige_cupom_fiscal" value="1" <?php if (!(strcmp($row_prospeccao['exige_cupom_fiscal'], '1'))) {echo "checked";} ?> /> Cupom Fiscal
					<br>
					<input type="checkbox" name="exige_nfe" id="exige_nfe" value="1" <?php if (!(strcmp($row_prospeccao['exige_nfe'], '1'))) {echo "checked";} ?> /> NFe
					<br>
					<input type="checkbox" name="exige_nfce" id="exige_nfce" value="1" <?php if (!(strcmp($row_prospeccao['exige_nfce'], '1'))) {echo "checked";} ?> /> NFCe
					<br>
					<input type="checkbox" name="exige_mdfe" id="exige_mdfe" value="1" <?php if (!(strcmp($row_prospeccao['exige_mdfe'], '1'))) {echo "checked";} ?> /> MDFe
					<br>
					<input type="checkbox" name="exige_ctee" id="exige_ctee" value="1" <?php if (!(strcmp($row_prospeccao['exige_ctee'], '1'))) {echo "checked";} ?> /> CTE-e
					<br>
					<input type="checkbox" name="exige_efd" id="exige_efd" value="1" <?php if (!(strcmp($row_prospeccao['exige_efd'], '1'))) {echo "checked";} ?> /> EFD
                
				</div>

                <div class="label_solicitacao2">Exige Outros:</div>
                <input type="text" name="exige_outro" id="exige_outro" value="<? echo $row_prospeccao['exige_outro']; ?>" style="width:760px" />

            </div>
		<? } ?>
		<!-- fim - informações fiscais -->


		<!-- contador -->
		<? if(
		($_GET['situacao']=="editar" and $_GET['acao']=="Alterar contador")
        ){ ?>
            <div style="padding-bottom: 10px;">
                <div class="label_solicitacao2">Contabilidade*:</div>                
				<?
				// contador - para selectbox
				mysql_select_db($database_conexao, $conexao);
				$query_contador = "SELECT prospeccao_contador.* FROM prospeccao_contador ORDER BY prospeccao_contador.razao ASC";
				$contador = mysql_query($query_contador, $conexao) or die(mysql_error());
				$row_contador = mysql_fetch_assoc($contador);
				$totalRows_contador = mysql_num_rows($contador);
				// fim - contador - para selectbox
				?>
				<select name="id_contador" id="id_contador" style="width: 760px;">
				<option value="">...</option>
				<?php
				do {  
				?>
				<option value="<?php echo $row_contador['id']?>">
				[<?php echo $row_contador['cidade']; ?>] <?php echo $row_contador['razao']; ?>
				</option>
				<?php
				} while ($row_contador = mysql_fetch_assoc($contador));
				$rows = mysql_num_rows($contador);
				if($rows > 0) {
				mysql_data_seek($contador, 0);
				$row_contador = mysql_fetch_assoc($contador);
				}
				?>
				</select>

            </div>
		<? } ?>
		<!-- fim - contador -->


		<!-- concorrente -->
		<? if(
		($_GET['situacao']=="editar" and $_GET['acao']=="Alterar concorrente")
        ){ ?>
            <div style="padding-bottom: 10px;">
                <div class="label_solicitacao2">Contabilidade*:</div>                
				<?
				// concorrente - para selectbox
				mysql_select_db($database_conexao, $conexao);
				$query_concorrente = "SELECT prospeccao_concorrente.* FROM prospeccao_concorrente ORDER BY prospeccao_concorrente.nome ASC";
				$concorrente = mysql_query($query_concorrente, $conexao) or die(mysql_error());
				$row_concorrente = mysql_fetch_assoc($concorrente);
				$totalRows_concorrente = mysql_num_rows($concorrente);
				// fim - concorrente - para selectbox
				?>
				<select name="id_concorrente" id="id_concorrente" style="width: 760px;">
				<option value="">...</option>
				<?php
				do {  
				?>
				<option value="<?php echo $row_concorrente['id']?>">
				<?php echo $row_concorrente['nome']; ?> [<?php echo $row_concorrente['empresa']; ?>]
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

            </div>
		<? } ?>
		<!-- fim - concorrente -->


		<!-- Empresa faz algum controle manual? -->
		<? if(
		($_GET['situacao']=="editar" and $_GET['acao']=="Alterar Empresa faz algum controle manual?")
        ){ ?>
            <div style="padding-bottom: 10px;" id="div_empresa_controle_manual">
            <div class="label_solicitacao2">Empresa faz algum controle manual?*:</div>
			<fieldset style="border: 1px solid #CCCCCC; padding: 5px;">
				<input type="radio" name="empresa_controle_manual" id="empresa_controle_manual" value="n" <?php if (!(strcmp($row_prospeccao['empresa_controle_manual'], 'n'))) {echo "checked";} ?>> Não 
				<input type="radio" name="empresa_controle_manual" id="empresa_controle_manual" value="s" <?php if (!(strcmp($row_prospeccao['empresa_controle_manual'], 's'))) {echo "checked";} ?>> Sim 
			</fieldset>
			<label for="empresa_controle_manual" class="error">Selecione uma das opções</label>
            
            </div>
		<? } ?>
		<!-- fim - Empresa faz algum controle manual? -->
		
		
		<!-- Alterar Necessidades/Interesses do cliente -->
		<? if(
		($_GET['situacao']=="editar" and $_GET['acao']=="Alterar Necessidades/Interesses do cliente")
        ){ ?>
            <div style="padding-bottom: 10px;" id="div_empresa_controle_manual">
            <div class="label_solicitacao2">Necessidades/Interesses do cliente*:</div>	
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
						
							<input  name="necessidades[]" id="necessidades" type="checkbox" class="checkbox" value="<? echo $row_geral_tipo_modulo_listar['IdTipoModulo']; ?>" 
							<? if (in_array($row_geral_tipo_modulo_listar['IdTipoModulo'], explode(',', $row_prospeccao['necessidades']))) {  ?>checked="checked"<? } ?> />
							<? echo $row_geral_tipo_modulo_listar['descricao']; ?>
							
						<? } while ($row_geral_tipo_modulo_listar = mysql_fetch_assoc($geral_tipo_modulo_listar)); ?>
						
					<? mysql_free_result($geral_tipo_modulo_listar); ?>
					
				</div>
				
			<? } while ($row_geral_tipo_modulo_categoria_listar = mysql_fetch_assoc($geral_tipo_modulo_categoria_listar)); ?>
			</fieldset>
			<? mysql_free_result($geral_tipo_modulo_categoria_listar); ?>
		   
			<label for="necessidades[]" class="error">Selecione pelo menos um dos módulos acima</label>
		
            
            </div>
		<? } ?>
		<!-- fim - Alterar Necessidades/Interesses do cliente -->
				

		<!-- Alterar O que podemos ofertar para automatizar o processo manual -->
		<? if(
		($_GET['situacao']=="editar" and $_GET['acao']=="Alterar O que podemos ofertar para automatizar o processo manual")
        ){ ?>
            <div style="padding-bottom: 10px;" id="div_empresa_controle_manual">
            <div class="label_solicitacao2">Alterar O que podemos ofertar para automatizar o processo manual*:</div>	
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
						
							<input  name="podemos_ofertar[]" id="podemos_ofertar" type="checkbox" class="checkbox" value="<? echo $row_geral_tipo_modulo_listar['IdTipoModulo']; ?>" 
							<? if (in_array($row_geral_tipo_modulo_listar['IdTipoModulo'], explode(',', $row_prospeccao['podemos_ofertar']))) {  ?>checked="checked"<? } ?> />
							<? echo $row_geral_tipo_modulo_listar['descricao']; ?>
							
						<? } while ($row_geral_tipo_modulo_listar = mysql_fetch_assoc($geral_tipo_modulo_listar)); ?>
						
					<? mysql_free_result($geral_tipo_modulo_listar); ?>
					
				</div>
				
			<? } while ($row_geral_tipo_modulo_categoria_listar = mysql_fetch_assoc($geral_tipo_modulo_categoria_listar)); ?>
			</fieldset>
			<? mysql_free_result($geral_tipo_modulo_categoria_listar); ?>
		   
			<label for="podemos_ofertar[]" class="error">Selecione pelo menos um dos módulos acima</label>
		
            
            </div>
		<? } ?>
		<!-- fim - Alterar O que podemos ofertar para automatizar o processo manual -->


		<!-- Alterar Quais recursos o cliente utiliza? -->
		<? if(
		($_GET['situacao']=="editar" and $_GET['acao']=="Alterar Quais recursos o cliente utiliza?")
        ){ ?>
            <div style="padding-bottom: 10px;" id="div_empresa_controle_manual">
            <div class="label_solicitacao2">Alterar Quais recursos o cliente utiliza?*:</div>	
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
						
							<input  name="sistema_recursos[]" id="sistema_recursos" type="checkbox" class="checkbox" value="<? echo $row_geral_tipo_modulo_listar['IdTipoModulo']; ?>" 
							<? if (in_array($row_geral_tipo_modulo_listar['IdTipoModulo'], explode(',', $row_prospeccao['sistema_recursos']))) {  ?>checked="checked"<? } ?> />
							<? echo $row_geral_tipo_modulo_listar['descricao']; ?>
							
						<? } while ($row_geral_tipo_modulo_listar = mysql_fetch_assoc($geral_tipo_modulo_listar)); ?>
						
					<? mysql_free_result($geral_tipo_modulo_listar); ?>
					
				</div>
				
			<? } while ($row_geral_tipo_modulo_categoria_listar = mysql_fetch_assoc($geral_tipo_modulo_categoria_listar)); ?>
			</fieldset>
			<? mysql_free_result($geral_tipo_modulo_categoria_listar); ?>
		   
			<label for="sistema_recursos[]" class="error">Selecione pelo menos um dos módulos acima</label>
		
            
            </div>
		<? } ?>
		<!-- fim - Alterar Quais recursos o cliente utiliza? -->


		<!-- sistema_possui -->
		<? if(
		($_GET['situacao']=="editar" and $_GET['acao']=="Alterar Possui sistema")
        ){ ?>
            <div style="padding-bottom: 10px;" id="div_sistema_possui">
            <div class="label_solicitacao2">Possui sistema*:</div>
			<fieldset style="border: 1px solid #CCCCCC; padding: 5px;">
				<input type="radio" name="sistema_possui" id="sistema_possui" value="n" <?php if (!(strcmp($row_prospeccao['sistema_possui'], 'n'))) {echo "checked";} ?>> Não 
				<input type="radio" name="sistema_possui" id="sistema_possui" value="s" <?php if (!(strcmp($row_prospeccao['sistema_possui'], 's'))) {echo "checked";} ?>> Sim 
			</fieldset>
			<label for="sistema_possui" class="error">Selecione uma das opções</label>
            
            </div>
		<? } ?>
		<!-- fim - sistema_possui -->
		
			
		<!-- sistema_nivel_utilizacao -->
		<? if(
		($_GET['situacao']=="editar" and $_GET['acao']=="Alterar Nível de utilização")
        ){ ?>
            <div style="padding-bottom: 10px;" id="div_sistema_nivel_utilizacao">
            <div class="label_solicitacao2">Nível de utilização*:</div>
            <select name="sistema_nivel_utilizacao" id="sistema_nivel_utilizacao" style="width: 400px;">
            	<option value="">...</option>
				<option value="a" <?php if (!(strcmp("a", $row_prospeccao['sistema_nivel_utilizacao']))) {echo "selected=\"selected\"";} ?>>Alto</option>
				<option value="m" <?php if (!(strcmp("m", $row_prospeccao['sistema_nivel_utilizacao']))) {echo "selected=\"selected\"";} ?>>Médio</option>
				<option value="b" <?php if (!(strcmp("b", $row_prospeccao['sistema_nivel_utilizacao']))) {echo "selected=\"selected\"";} ?>>Baixo</option>
				<option value="n" <?php if (!(strcmp("n", $row_prospeccao['sistema_nivel_utilizacao']))) {echo "selected=\"selected\"";} ?>>Não implantado</option>
            </select>
            
            </div>
		<? } ?>
		<!-- fim - sistema_nivel_utilizacao -->


		<!-- sistema_nivel_satisfacao -->
		<? if(
		($_GET['situacao']=="editar" and $_GET['acao']=="Alterar Nível de satisfação")
        ){ ?>
            <div style="padding-bottom: 10px;" id="div_sistema_nivel_satisfacao">
            <div class="label_solicitacao2">Nível de satisfação*:</div>
            <select name="sistema_nivel_satisfacao" id="sistema_nivel_satisfacao" style="width: 400px;">
            	<option value="">...</option>
				<option value="a" <?php if (!(strcmp("a", $row_prospeccao['sistema_nivel_satisfacao']))) {echo "selected=\"selected\"";} ?>>Alto</option>
				<option value="m" <?php if (!(strcmp("m", $row_prospeccao['sistema_nivel_satisfacao']))) {echo "selected=\"selected\"";} ?>>Médio</option>
				<option value="b" <?php if (!(strcmp("b", $row_prospeccao['sistema_nivel_satisfacao']))) {echo "selected=\"selected\"";} ?>>Baixo</option>
				<option value="i" <?php if (!(strcmp("i", $row_prospeccao['sistema_nivel_satisfacao']))) {echo "selected=\"selected\"";} ?>>Insatisfeito</option>
            </select>
            
            </div>
		<? } ?>
		<!-- fim - sistema_nivel_satisfacao -->
							

		<!-- sistema_nivel_motivo -->            
		<? if(
		($_GET['situacao']=="editar" and $_GET['acao']=="Alterar Motivo da Satisfação/Insatisfação")
        ){ ?>
            <div style="padding-bottom: 10px;" id="div_sistema_nivel_motivo">
            <div class="label_solicitacao2">Motivo da Satisfação/Insatisfação*:</div>
            
            <textarea name="sistema_nivel_motivo" id="sistema_nivel_motivo" style="width: 760px; height: 90px" /><? echo str_replace("<br />", "", $row_prospeccao['sistema_nivel_motivo']); ?></textarea>
            </div>
        <? } ?>
		<!-- fim - sistema_nivel_motivo -->


		<!-- empresa_controle_manual -->
		<? if(
		($_GET['situacao']=="editar" and $_GET['acao']=="Empresa faz algum controle manual?")
        ){ ?>
            <div style="padding-bottom: 10px;" id="div_empresa_controle_manual">
            <div class="label_solicitacao2">Empresa faz algum controle manual?*:</div>
			<fieldset style="border: 1px solid #CCCCCC; padding: 5px;">
				<input type="radio" name="empresa_controle_manual" id="empresa_controle_manual" value="n" <?php if (!(strcmp($row_prospeccao['empresa_controle_manual'], 'n'))) {echo "checked";} ?>> Não 
				<input type="radio" name="empresa_controle_manual" id="empresa_controle_manual" value="s" <?php if (!(strcmp($row_prospeccao['empresa_controle_manual'], 's'))) {echo "checked";} ?>> Sim 
			</fieldset>
			<label for="empresa_controle_manual" class="error">Selecione uma das opções</label>
            
            </div>
		<? } ?>
		<!-- fim - empresa_controle_manual -->
		
		
		<!-- sistema_recursos_success_possui -->
		<? if(
		($_GET['situacao']=="editar" and $_GET['acao']=="Alterar O Success tem os recursos que o cliente utiliza?")
        ){ ?>
            <div style="padding-bottom: 10px;" id="div_sistema_recursos_success_possui">
            <div class="label_solicitacao2">O Success tem os recursos que o cliente utiliza?*:</div>
            <select name="sistema_recursos_success_possui" id="sistema_recursos_success_possui" style="width: 400px;">
            	<option value="">...</option>
				<option value="t" <?php if (!(strcmp("t", $row_prospeccao['sistema_recursos_success_possui']))) {echo "selected=\"selected\"";} ?>>Totalmente</option>
				<option value="p" <?php if (!(strcmp("p", $row_prospeccao['sistema_recursos_success_possui']))) {echo "selected=\"selected\"";} ?>>Parcialmente</option>
            </select>
            
            </div>
		<? } ?>
		<!-- fim - sistema_recursos_success_possui -->
		

		<!-- sistema_recursos_success_nao_possui -->            
		<? if(
		($_GET['situacao']=="editar" and $_GET['acao']=="Alterar Recursos que o cliente utiliza e o Success não tem")
        ){ ?>
            <div style="padding-bottom: 10px;" id="div_sistema_recursos_success_nao_possui">
            <div class="label_solicitacao2">Recursos que o cliente utiliza e o Success não tem*:</div>
            
            <textarea name="sistema_recursos_success_nao_possui" id="sistema_recursos_success_nao_possui" style="width: 760px; height: 90px" /><? echo str_replace("<br />", "", $row_prospeccao['sistema_recursos_success_nao_possui']); ?></textarea>
            </div>
        <? } ?>
		<!-- fim - sistema_recursos_success_nao_possui -->

				
		<!-- baixa (Encerrar) -->
		<? if(
		($_GET['situacao']=="editar" and $_GET['acao']=="Encerrar")
        ){ ?>
        
        	<!-- baixa_tipo -->
			<div style="padding-bottom: 10px;">
                <div class="label_solicitacao2">Motivo*:</div>

                <fieldset style="border: 1px solid #CCCCCC; padding: 5px;">
                    <input type="radio" name="baixa_tipo" id="baixa_tipo" value="v" <?php if (!(strcmp($row_prospeccao['baixa_tipo'], 'v'))) {echo "checked";} ?>> Venda 
                    <input type="radio" name="baixa_tipo" id="baixa_tipo" value="p" <?php if (!(strcmp($row_prospeccao['baixa_tipo'], 'p'))) {echo "checked";} ?>> Perda 
                </fieldset>
                <label for="baixa_tipo" class="error">Selecione uma das opções</label>
			</div>
            <!-- baixa_tipo -->
            
            
     		<!-- baixa_contrato -->
			<div style="padding-bottom: 10px;" id="div_baixa_contrato">
                <div class="label_solicitacao2">Contrato*:</div>
                <? if($row_prospeccao['tipo_cliente']=="a"){ ?>
                	<input type="hidden" name="baixa_contrato" id="baixa_contrato" value="<? echo $row_prospeccao['contrato']; ?>" />
                    <? echo $row_prospeccao['contrato']; ?>
                <? } else { ?>
                	Novo
                	<input type="hidden" name="baixa_contrato" id="baixa_contrato" value="" />
                <? } ?>
				
				<div style="font-weight: bold; color: #F00; margin-top: 8px;">
				Tempo limite para envio de documentação a Matriz: <? echo $row_parametros['venda_prazo_envio_documentacao_dias']; ?> Dias
				</div>

			</div>
            <!-- baixa_contrato -->
            
            
      		<!-- baixa_contrato_data -->
            <div style="padding-bottom: 10px;" id="div_baixa_contrato_data">
                <div class="label_solicitacao2">Data do Contrato*:</div>
                <input name="baixa_contrato_data" type="text" id="baixa_contrato_data" style="width: 200px;" value="" />
			</div>
            <!-- fim - baixa_contrato_data -->
			
            
      		<!-- valor_venda -->
            <div style="padding-bottom: 10px;" id="div_valor_venda">
                <div class="label_solicitacao2">Valor da venda do software*:</div>
                R$ <input type="text" name="valor_venda" id="valor_venda" value="0,00" />
			</div>
            <!-- fim - valor_venda -->
            
            
      		<!-- valor_treinamento -->
            <div style="padding-bottom: 10px;" id="div_valor_treinamento">
                <div class="label_solicitacao2">Valor da venda do treinamento*:</div>
                R$ <input type="text" name="valor_treinamento" id="valor_treinamento" value="0,00" />
			</div>
            <!-- fim - valor_treinamento -->
            
            
      		<!-- venda_modulos -->
            <div style="padding-bottom: 10px;" id="div_venda_modulos">
                <div class="label_solicitacao2">Módulos:</div>
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
                        WHERE IdTipoModuloCategoria = ".$row_geral_tipo_modulo_categoria_listar['IdTipoModuloCategoria']."
                        ORDER BY geral_tipo_modulo.IdTipoModuloCategoria ASC, geral_tipo_modulo.ordem ASC, geral_tipo_modulo.IdTipoModulo ASC";
                        $geral_tipo_modulo_listar = mysql_query($query_geral_tipo_modulo_listar, $conexao) or die(mysql_error());
                        $row_geral_tipo_modulo_listar = mysql_fetch_assoc($geral_tipo_modulo_listar);
                        $totalRows_geral_tipo_modulo_listar = mysql_num_rows($geral_tipo_modulo_listar);
                        // fim - geral_tipo_modulo_listar
                        ?>
                        
							<? do { ?>
                            
								<input  name="venda_modulos[]" id="venda_modulos" type="checkbox" class="checkbox" value="<? echo $row_geral_tipo_modulo_listar['IdTipoModulo']; ?>" <? if(($row_geral_tipo_modulo_listar['IdTipoModulo']== 20 or $row_geral_tipo_modulo_listar['IdTipoModulo']== 7) and $row_prospeccao['tipo_cliente']=="n"){ ?>checked onclick="return false;" onkeydown="return false;"<? } ?> />
								<? echo $row_geral_tipo_modulo_listar['descricao']; ?>
                                
                        	<? } while ($row_geral_tipo_modulo_listar = mysql_fetch_assoc($geral_tipo_modulo_listar)); ?>
                            
                        <? mysql_free_result($geral_tipo_modulo_listar); ?>
                        
                    </div>
                    
                <? } while ($row_geral_tipo_modulo_categoria_listar = mysql_fetch_assoc($geral_tipo_modulo_categoria_listar)); ?>
                </fieldset>
                <? mysql_free_result($geral_tipo_modulo_categoria_listar); ?>
               
                <label for="venda_modulos[]" class="error">Selecione pelo menos um dos módulos acima</label>

            </div>
            <!-- fim - venda_modulos -->
            
     		<!-- implantacao_tempo_adicional -->
			 <div style="padding-bottom: 10px;" id="div_implantacao_tempo_adicional">
                <div class="label_solicitacao2">Hora/implantação vendida*:</div>
				<select name="implantacao_tempo_adicional" id="implantacao_tempo_adicional" style="width: 200px;">
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
			<!-- fim - implantacao_tempo_adicional -->	
			
     		<!-- treinamento_tempo -->
            <div style="padding-bottom: 10px;" id="div_treinamento_tempo">
                <div class="label_solicitacao2">Qtde de tempo para treinamento*:</div>
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
			<!-- fim - treinamento_tempo -->	           

      		<!-- baixa_perda_motivo -->
            <div style="padding-bottom: 10px;" id="div_baixa_perda_motivo">
                <div class="label_solicitacao2">Motivo da perda*:</div>
                <fieldset style="border: 1px solid #CCCCCC; padding: 5px;">
                    <input type="radio" name="baixa_perda_motivo" id="baixa_perda_motivo" value="falta de recurso"> falta de recurso
                    <input type="radio" name="baixa_perda_motivo" id="baixa_perda_motivo" value="concorrência"> concorrência
					<input type="radio" name="baixa_perda_motivo" id="baixa_perda_motivo" value="encerramento de atividade"> encerramento de atividade
					<input type="radio" name="baixa_perda_motivo" id="baixa_perda_motivo" value="outros motivos"> outros motivos
                </fieldset>
                <label for="baixa_perda_motivo" class="error">Selecione uma das opções</label>
			</div>
            <!-- fim - baixa_perda_motivo -->
            
      		<!-- baixa_perda_recurso -->
            <div style="padding-bottom: 10px;" id="div_baixa_perda_recurso">
                <div class="label_solicitacao2">Recurso*:</div>
                <input type="text" name="baixa_perda_recurso" id="baixa_perda_recurso" style="width: 600px;" />
			</div>
            <!-- fim - baixa_perda_recurso -->
            
      		<!-- baixa_perda_recurso_solicitacao_existe -->
            <div style="padding-bottom: 10px;" id="div_baixa_perda_recurso_solicitacao_existe">
                <div class="label_solicitacao2">Já foi verificada a existência de solicitação de implementação ligada ao recurso?*:</div>
                <fieldset style="border: 1px solid #CCCCCC; padding: 5px;">
                <input type="radio" name="baixa_perda_recurso_solicitacao_existe" id="baixa_perda_recurso_solicitacao_existe" value="s" 
                <?php if (!(strcmp($row_prospeccao['baixa_perda_recurso_solicitacao_existe'], 's'))) {echo "checked";} ?>> Sim 
                <input type="radio" name="baixa_perda_recurso_solicitacao_existe" id="baixa_perda_recurso_solicitacao_existe" value="n" 
                <?php if (!(strcmp($row_prospeccao['baixa_perda_recurso_solicitacao_existe'], 'n'))) {echo "checked";} ?>> Não 
                </fieldset>
                <label for="baixa_perda_recurso_solicitacao_existe" class="error">Selecione uma das opções</label>
			</div>
            <!-- fim - baixa_perda_recurso_solicitacao_existe -->
            
            
      		<!-- baixa_perda_recurso_solicitacao_verificada -->
            <div style="padding-bottom: 10px;" id="div_baixa_perda_recurso_solicitacao_verificada">
                <div class="label_solicitacao2">Existe viabilidade de criar uma solicitação de implementação do recurso?*</div>
                <fieldset style="border: 1px solid #CCCCCC; padding: 5px;">
                <input type="radio" name="baixa_perda_recurso_solicitacao_verificada" id="baixa_perda_recurso_solicitacao_verificada" value="s" 
                <?php if (!(strcmp($row_prospeccao['baixa_perda_recurso_solicitacao_verificada'], 's'))) {echo "checked";} ?>> Sim 
                <input type="radio" name="baixa_perda_recurso_solicitacao_verificada" id="baixa_perda_recurso_solicitacao_verificada" value="n" 
                <?php if (!(strcmp($row_prospeccao['baixa_perda_recurso_solicitacao_verificada'], 'n'))) {echo "checked";} ?>> Não 
                </fieldset>
                <label for="baixa_perda_recurso_solicitacao_verificada" class="error">Selecione uma das opções</label>
			</div>
            <!-- fim - baixa_perda_recurso_solicitacao_verificada -->
            
            
      		<!-- baixa_perda_recurso_solicitacao_sugestao -->
            <div style="padding-bottom: 10px;" id="div_baixa_perda_recurso_solicitacao_sugestao">
                <div class="label_solicitacao2">Foi criada uma solicitação de sugestão para implementação do recurso?*</div>
                <fieldset style="border: 1px solid #CCCCCC; padding: 5px;">
                <input type="radio" name="baixa_perda_recurso_solicitacao_sugestao" id="baixa_perda_recurso_solicitacao_sugestao" value="s" 
                <?php if (!(strcmp($row_prospeccao['baixa_perda_recurso_solicitacao_sugestao'], 's'))) {echo "checked";} ?>> Sim 
                <input type="radio" name="baixa_perda_recurso_solicitacao_sugestao" id="baixa_perda_recurso_solicitacao_sugestao" value="n" 
                <?php if (!(strcmp($row_prospeccao['baixa_perda_recurso_solicitacao_sugestao'], 'n'))) {echo "checked";} ?>> Não 
                </fieldset>
                <label for="baixa_perda_recurso_solicitacao_sugestao" class="error">Selecione uma das opções</label>
			</div>
            <!-- fim - baixa_perda_recurso_solicitacao_sugestao -->
            
            
      		<!-- baixa_perda_concorrencia_programa -->
            <div style="padding-bottom: 10px;" id="div_baixa_perda_concorrencia_programa">
                <div class="label_solicitacao2">Concorrente*:</div>
				<?
				// concorrente - para selectbox
				mysql_select_db($database_conexao, $conexao);
				$query_concorrente = "SELECT * FROM prospeccao_concorrente ORDER BY prospeccao_concorrente.nome ASC";
				$concorrente = mysql_query($query_concorrente, $conexao) or die(mysql_error());
				$row_concorrente = mysql_fetch_assoc($concorrente);
				$totalRows_concorrente = mysql_num_rows($concorrente);
				// fim - concorrente - para selectbox
				?>
				<select name="baixa_perda_concorrencia_programa" id="baixa_perda_concorrencia_programa" style="width: 300px;">
				<option value="">...</option>
				<?php
				do {  
				?>
				<option value="<?php echo $row_concorrente['id']?>">
				<?php echo $row_concorrente['nome']; ?> [<?php echo $row_concorrente['empresa']; ?>]
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
			</div>
            <!-- fim - baixa_perda_concorrencia_programa -->
            
            
      		<!-- baixa_perda_concorrencia_fator -->
            <div style="padding-bottom: 10px;" id="div_baixa_perda_concorrencia_fator">
                <div class="label_solicitacao2">Fator determinante na escolha do cliente*:</div>
                <input type="text" name="baixa_perda_concorrencia_fator" id="baixa_perda_concorrencia_fator" style="width: 600px;" />
			</div>
            <!-- fim - baixa_perda_concorrencia_fator -->
			
			
			
			<!-- Questionário ********************************************************* -->
			<?
			// prospeccao_perda_pergunta
			mysql_select_db($database_conexao, $conexao);
			$query_prospeccao_perda_pergunta = "SELECT * FROM prospeccao_perda_pergunta ORDER BY IdProspeccaoPerdaPergunta ASC";
			$prospeccao_perda_pergunta = mysql_query($query_prospeccao_perda_pergunta, $conexao) or die(mysql_error());
			$row_prospeccao_perda_pergunta = mysql_fetch_assoc($prospeccao_perda_pergunta);
			$totalRows_prospeccao_perda_pergunta = mysql_num_rows($prospeccao_perda_pergunta);
			// fim - prospeccao_perda_pergunta
			?>
						
            <div style="padding-bottom: 10px;" id="div_perda_questionario">
                <div class="label_solicitacao2">Questionário*:</div>
					<div class="div_solicitacao_linhas4">
					<table border="0" cellspacing="0" cellpadding="0" width="100%">
						<tr>
							<td style="text-align: left">
							<? if($totalRows_prospeccao_perda_pergunta > 0){ ?>
							<? $contador_pergunta = 0; ?>
							
							<?php do { ?>
								<? $contador_pergunta = $contador_pergunta + 1; ?>
								
								<div style="margin-top: 10px; margin-bottom: 10px; text-align: justify">
								
									<!-- pergunta -->
									<div style="font-weight: bold; padding-bottom: 5px; padding-top: 5px;">
									<? echo $contador_pergunta; ?>) <span style="color: #000;"><?php echo $row_prospeccao_perda_pergunta['descricao']; ?></span>  -      
									</div>
									<!-- fim - pergunta -->
									

									<!-- resposta ------------------------------------------------------------------------------------------------------------------------------------------->
									<?
									// prospeccao_perda_resposta
									$colname_prospeccao_perda_resposta = "-1";
									if (isset($row_prospeccao_perda_pergunta['IdProspeccaoPerdaPergunta'])) {
										$colname_prospeccao_perda_resposta = $row_prospeccao_perda_pergunta['IdProspeccaoPerdaPergunta'];
									}
									mysql_select_db($database_conexao, $conexao);
									$query_prospeccao_perda_resposta = sprintf("
																		 SELECT * 
																		 FROM prospeccao_perda_resposta 
																		 WHERE IdProspeccaoPerdaPergunta = %s 
																		 ORDER BY IdProspeccaoPerdaResposta ASC", 
																		 GetSQLValueString($colname_prospeccao_perda_resposta, "text"));
									$prospeccao_perda_resposta = mysql_query($query_prospeccao_perda_resposta, $conexao) or die(mysql_error());
									$row_prospeccao_perda_resposta = mysql_fetch_assoc($prospeccao_perda_resposta);
									$totalRows_prospeccao_perda_resposta = mysql_num_rows($prospeccao_perda_resposta);
									// fim - prospeccao_perda_resposta             
									?>
					
									<!-- questão fechada -->
									<? if($totalRows_prospeccao_perda_resposta > "0"){ ?>
									<div style="padding: 5px;">
									
									<?php do { ?>
									
										<div style="padding-bottom: 5px; padding-top: 5px;">
											<? if($row_prospeccao_perda_pergunta['tipo']=="r"){ // uma alternativa ?> 
												<input type="radio" id="opcao" name="opcao[<? echo $row_prospeccao_perda_pergunta['IdProspeccaoPerdaPergunta']; ?>][]" value="<?php echo $row_prospeccao_perda_resposta['IdProspeccaoPerdaResposta']; ?>" 
												style="border: 0px; padding: 0px; margin: 0px;" /> 
											<? } ?>
											
											<? if($row_prospeccao_perda_pergunta['tipo']=="c"){ // várias alternativas ?> 
												<input type="checkbox" id="opcao" name="opcao[<? echo $row_prospeccao_perda_pergunta['IdProspeccaoPerdaPergunta']; ?>][]" value="<?php echo $row_prospeccao_perda_resposta['IdProspeccaoPerdaResposta']; ?>" 
												style="border: 0px; padding: 0px; margin: 0px;" /> 
											<? } ?> 
																   
											<?php echo $row_prospeccao_perda_resposta['descricao']; ?>
										</div>
					
									<?php } while ($row_prospeccao_perda_resposta = mysql_fetch_assoc($prospeccao_perda_resposta)); ?>
									
									</div>
									<? } ?>                
									<!-- fim - questão fechada -->
				
									<? mysql_free_result($prospeccao_perda_resposta); ?>
					
									<!-- questão aberta  -->
									<?php if($row_prospeccao_perda_pergunta['campo_texto'] =="s"){ ?>
									<div style="padding: 5px;">
									
										<?php if($row_prospeccao_perda_pergunta['campo_texto_label'] !=""){ ?>
											<div style="margin-top: 10px; margin-bottom: 5px; font-weight: bold;">
												<?php echo $row_prospeccao_perda_pergunta['campo_texto_label']; ?>
											</div>
										<? } ?>
				
										<div style="text-align: left;">
											<label class="error" for="campo_texto">Insira algum contéudo no campo para continuar<br></label>
											<textarea id="campo_texto" name="campo_texto[<? echo $row_prospeccao_perda_pergunta['IdProspeccaoPerdaPergunta']; ?>]" style="width: 610px; height: 50px; margin-top: 5px; margin-bottom: 5px; padding: 5px; border: 1px solid #CCC;"></textarea>
										</div>
									
									</div>
									<? } ?>    
									<!-- fim - questão aberta -->	   
									<!-- fim - resposta ------------------------------------------------------------------------------------------------------------------------------------->
									
								</div>
								
								<hr style="border: 1px solid #CCC;">
								
							<?php } while ($row_prospeccao_perda_pergunta = mysql_fetch_assoc($prospeccao_perda_pergunta)); ?>
							
							<? } else { ?>
							Nenhuma pergunta disponível.
							<? } ?>
							</td>
						</tr>
					</table>
					</div>
			</div>

			<? mysql_free_result($prospeccao_perda_pergunta); ?>
			<!-- fim - Questionário ********************************************************* -->
                        
		<? } ?>
		<!-- fim - baixa (Encerrar) -->
        
        
		<!-- Observação -->
		<div style="padding-bottom: 10px;">
		<? if(
		($_GET['situacao']=="editar" and $_GET['acao']=="Encerrar")
        ){ ?>
            <div class="label_solicitacao2">Parecer/Justificativa:</div>
        <? } else { ?>
        	<div class="label_solicitacao2">Observações:</div>
        <? } ?>
            
		<? if(
		($_GET['situacao']=="editar" and $_GET['acao']=="Alterar observação")
        ){ ?>
            <textarea name="observacao" id="observacao" style="width: 760px; height: 90px" /><? echo str_replace("<br />", "", $row_prospeccao['observacao']); ?></textarea>
        <? } else { ?>
        	<textarea name="observacao" id="observacao" style="width: 760px; height: 90px" /></textarea>
        <? } ?>
		</div>
		<!-- fim - Observação -->


		<!-- Botões -->
        <div>
            <input name="id_solicitacao" type="hidden" value="<?php echo $row_prospeccao['id']; ?>" />
            <input type="hidden" name="MM_update" value="form" />
            <input type="button" name="button" id="button" value="Salvar" class="botao_geral2" style="width: 70px" />
            
            <!-- Registrar reclamação ========================================================================================================================================= -->
            <? if( 
				$row_prospeccao['situacao']!="venda perdida" and $row_prospeccao['situacao']!="venda realizada" and $row_prospeccao['situacao']!="cancelada" and 
                $row_prospeccao['tipo_cliente'] == "a"
            ){ ?>
            
                <a href="suporte_gerar.php?tipo_suporte=r&inloco=n&cobranca=n&cliente=<? echo $row_prospeccao['codigo_empresa']; ?>6&contrato=<? echo $row_prospeccao['contrato']; ?>&reclamacao_prospeccao=<? echo $row_prospeccao['id']; ?>" target="_blank" id="botao_geral2">Registrar reclamação</a>
                
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
mysql_free_result($prospeccao);
mysql_free_result($prospeccao_concorrente);
mysql_free_result($prospeccao_contador);
mysql_free_result($usuario);
mysql_free_result($agenda);
mysql_free_result($agenda_agendado);
mysql_free_result($prospeccao_contato);
?>