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

// verifica se existe o $id_suporte
$id_suporte = "-1";
if (isset($_GET["id_suporte"])) { 
	$id_suporte = $_GET["id_suporte"];
}
// fim - verifica se existe o $id_suporte

// select - suporte
mysql_select_db($database_conexao, $conexao);
$query_suporte = sprintf("
SELECT suporte.* 
FROM suporte 
WHERE suporte.id = %s", GetSQLValueString($id_suporte, "int"));
$suporte = mysql_query($query_suporte, $conexao) or die(mysql_error());
$row_suporte = mysql_fetch_assoc($suporte);
$totalRows_suporte = mysql_num_rows($suporte);
// fim - select - suporte

if($row_suporte['tela']=="e"){echo sprintf("<meta http-equiv=\"refresh\" content=\"0; url=%s\">", "suporte_editar.php?id_suporte=".$id_suporte."&padrao=sim"); exit;}

// $colname_contrato
$colname_contrato = "-1";
if($totalRows_suporte==0){
	$colname_contrato = $_GET["contrato"];
} else {
	$colname_contrato = $row_suporte['contrato'];	
}
// fim - $colname_contrato

// manutencao_dados
mysql_select_db($database_conexao, $conexao);
$query_manutencao_dados = sprintf("
SELECT 
geral_tipo_praca_executor.praca, 
da37.codigo17, da37.cliente17, da37.status17, da37.obs17, da37.tpocont17, da37.visita17, da37.optacuv17, da37.versao17, da37.espmod17, versao17, espmod17, 
geral_tipo_contrato.descricao as tpocont17_descricao,
geral_tipo_visita.descricao as visita17_descricao

FROM da37 
LEFT JOIN geral_tipo_praca_executor ON da37.vendedor17 = geral_tipo_praca_executor.IdExecutor
LEFT JOIN geral_tipo_contrato ON da37.tpocont17 = geral_tipo_contrato.IdTipoContrato
LEFT JOIN geral_tipo_visita ON da37.visita17 = geral_tipo_visita.IdTipoVisita

WHERE da37.codigo17 = %s and da37.sr_deleted <> 'T'", 
GetSQLValueString($colname_contrato, "text"));
$manutencao_dados = mysql_query($query_manutencao_dados, $conexao) or die(mysql_error());
$row_manutencao_dados = mysql_fetch_assoc($manutencao_dados);
$totalRows_manutencao_dados = mysql_num_rows($manutencao_dados);
// fim - manutencao_dados

// empresa_dados
mysql_select_db($database_conexao, $conexao);
$query_empresa_dados = sprintf("
SELECT 
codigo1, nome1, fantasia1, contato1, endereco1, bairro1, cidade1, uf1, telefone1, comercio1, cep1, ultcompra1, atraso1, status1, flag1, tipo1 
FROM da01 
WHERE codigo1 = %s and da01.sr_deleted <> 'T'", GetSQLValueString($row_manutencao_dados['cliente17'], "text"));
$empresa_dados = mysql_query($query_empresa_dados, $conexao) or die(mysql_error());
$row_empresa_dados = mysql_fetch_assoc($empresa_dados);
$totalRows_empresa_dados = mysql_num_rows($empresa_dados);
// fim - empresa_dados

// select - suporte_formulario_bonus_ultimo
mysql_select_db($database_conexao, $conexao);
$query_suporte_formulario_bonus_ultimo = sprintf("
SELECT suporte_formulario.data 
FROM suporte_formulario 
WHERE suporte_formulario.contrato = %s and suporte_formulario.status_flag = 'a' and suporte_formulario.visita_bonus='s'
ORDER BY suporte_formulario.IdFormulario DESC 
LIMIT 1 
", 
GetSQLValueString($colname_contrato, "text"));
$suporte_formulario_bonus_ultimo = mysql_query($query_suporte_formulario_bonus_ultimo, $conexao) or die(mysql_error());
$row_suporte_formulario_bonus_ultimo = mysql_fetch_assoc($suporte_formulario_bonus_ultimo);
$totalRows_suporte_formulario_bonus_ultimo = mysql_num_rows($suporte_formulario_bonus_ultimo);
// fim - select - suporte_formulario_bonus_ultimo

// modcon
mysql_select_db($database_conexao, $conexao);
$query_modcon = sprintf("
								  SELECT * FROM modcon 
								  WHERE modcon.contrato = %s AND modcon.codcli = %s", 
								  GetSQLValueString($colname_contrato, "text"), 
								  GetSQLValueString($row_manutencao_dados['cliente17'], "text"));
$modcon = mysql_query($query_modcon, $conexao) or die(mysql_error());
$row_modcon = mysql_fetch_assoc($modcon);
$totalRows_modcon = mysql_num_rows($modcon);
// fim - modcon

//------------------------------------------------------------------------------------------------------------------------------------------------------------------
// INSERT - se NÃO EXISTE o $id_suporte MAS existe campos para o novo
if ( $id_suporte == "-1" and isset($row_usuario['nome']) and isset($_GET['tipo_suporte']) and isset($_GET['inloco']) ) {
			
	// acessso --------------------------------------------------------------------------------------------------------------------------------------------------------------
	$acesso = 0;
	if (
			(
			($row_usuario['praca'] == $row_manutencao_dados['praca']) or 
			
			($row_usuario['controle_suporte'] == "Y") or  
			($row_usuario['suporte_operador_parceiro'] == "Y") or 
			($row_usuario['controle_praca'] == "Y" and $row_usuario['praca'] = $row_manutencao_dados['praca'])
			)
		) {
	
		$acesso = 1; // autorizado
	
	}  else {
		
		$acesso = 0; // não autorizado
		
	}
	
	if($acesso==0){
		//echo "Acesso não autorizado !";
		echo "<script>window.top.location = 'suporte.php?padrao=sim&".$suporte_padrao."';</script>";
		exit;
	}
	// fim - acesso ---------------------------------------------------------------------------------------------------------------------------------------------------------

	// verifica tipo_suporte E inloco	
	$id_usuario_envolvido = "";
	$titulo = "";
	$status = "";
	$situacao = "";
	$cobranca = "";
		
	if($_GET['tipo_suporte']=="c" and $_GET['inloco']=="s" and $_GET['cobranca']=="n"){ // cliente - inloco SIM - cobranca NAO
	
		$id_usuario_envolvido = "";
		
		$titulo = "Agendamento de visita";
		
		$status = "pendente usuario responsavel";
		$situacao = "criada";
		
		$cobranca = "n";
		
	} 
	else if($_GET['tipo_suporte']=="c" and $_GET['inloco']=="s" and $_GET['cobranca']=="s"){ // cliente - inloco SIM - cobranca SIM
	
		$id_usuario_envolvido = "";
		
		$titulo = "Agendamento de visita";
		
		$status = "pendente usuario responsavel";
		$situacao = "criada";
		
		$cobranca = "s";
		
	} 
	else if($_GET['tipo_suporte']=="c" and $_GET['inloco']=="n"){ // cliente - inloco NAO
	
		$id_usuario_envolvido = "";
		
		$titulo = "";
		
		$status = "pendente usuario responsavel";
		$situacao = "criada";
		
		$cobranca = "n";
		
	} 
	else if($_GET['tipo_suporte']=="p"){ // parceiro
	
		$id_usuario_envolvido = $row_usuario['IdUsuario'];
		
		$titulo = "";
		
		$status = "pendente usuario responsavel";
		$situacao = "criada";
		
		$cobranca = "n";
		
	} else if($_GET['tipo_suporte']=="r"){ // reclamacao
	
		$id_usuario_envolvido = "";
		
		$titulo = "Reclamação";
		
		$status = "pendente usuario responsavel";
		$situacao = "criada";
		
		$cobranca = "n";
		
	}
	// fim - verifica tipo_suporte E inloco
	
	// reclamacao_vinculo ***********************************
	$reclamacao_vinculo = NULL; 
	if (isset($_GET["reclamacao_vinculo"])) { 
		$reclamacao_vinculo = $_GET["reclamacao_vinculo"];
	}
	// fim - reclamacao_vinculo *****************************
	
	// reclamacao_solicitacao
	$reclamacao_solicitacao = NULL; 
	if (isset($_GET["reclamacao_solicitacao"])) { 
		$reclamacao_solicitacao = $_GET["reclamacao_solicitacao"];
	}
	// fim - reclamacao_solicitacao
	
	// reclamacao_suporte
	$reclamacao_suporte = NULL; 
	if (isset($_GET["reclamacao_suporte"])) { 
		$reclamacao_suporte = $_GET["reclamacao_suporte"];
	}
	// fim - reclamacao_suporte
	
	// reclamacao_prospeccao
	$reclamacao_prospeccao = NULL; 
	if (isset($_GET["reclamacao_prospeccao"])) { 
		$reclamacao_prospeccao = $_GET["reclamacao_prospeccao"];
	}
	// fim - reclamacao_prospeccao
	
	// reclamacao_venda
	$reclamacao_venda = NULL; 
	if (isset($_GET["reclamacao_venda"])) { 
		$reclamacao_venda = $_GET["reclamacao_venda"];
	}
	// fim - reclamacao_venda
	
	// insert - suporte
	$insertSQL = sprintf("INSERT INTO suporte (titulo, tipo_suporte, inloco, data_suporte, data_inicio, data_fim, empresa, codigo_empresa, praca, contrato, id_usuario_envolvido, id_usuario_responsavel, status, cobranca, situacao, reclamacao_vinculo, reclamacao_solicitacao, reclamacao_suporte, reclamacao_prospeccao, reclamacao_venda) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)",

	   GetSQLValueString($titulo, "text"),
	   GetSQLValueString($_GET['tipo_suporte'], "text"),
	   GetSQLValueString($_GET['inloco'], "text"),
	   GetSQLValueString(date('Y-m-d H:i:s'), "date"),
	   GetSQLValueString(date('Y-m-d H:i:s'), "date"),
	   GetSQLValueString("0000-00-00 00:00:00", "date"), 
	   GetSQLValueString($row_empresa_dados['nome1'], "text"),
	   GetSQLValueString($row_empresa_dados['codigo1'], "text"),
	   GetSQLValueString($row_manutencao_dados['praca'], "text"),
	   GetSQLValueString($row_manutencao_dados['codigo17'], "text"),
	   GetSQLValueString($id_usuario_envolvido, "int"),
	   GetSQLValueString($row_usuario['IdUsuario'], "int"),
	   GetSQLValueString($status, "text"),
	   GetSQLValueString($cobranca, "text"),
	   GetSQLValueString($situacao, "text"),
	   GetSQLValueString($reclamacao_vinculo, "int"),
	   GetSQLValueString($reclamacao_solicitacao, "int"),
	   GetSQLValueString($reclamacao_suporte, "int"),
	   GetSQLValueString($reclamacao_prospeccao, "int"),
	   GetSQLValueString($reclamacao_venda, "int"));

	mysql_select_db($database_conexao, $conexao);
	$Result1 = mysql_query($insertSQL, $conexao) or die(mysql_error());
	$IdSuporteNovo = mysql_insert_id();
	// fim - insert - suporte

	// redireciona
	$insertGoTo = "suporte_gerar.php?id_suporte=".$IdSuporteNovo;
	echo sprintf("<meta http-equiv=\"refresh\" content=\"0; url=%s\">", $insertGoTo);
	// fim - redireciona
	
	exit;	
} 
// fim - INSERT - se NÃO EXISTE o $id_suporte MAS existe campos para o novo
// -----------------------------------------------------------------------------------------------------------------------------------------------------------------
// REDIRECIONA - se NÃO EXISTE o $id_suporte
else if ($id_suporte == "-1") { 

	echo "Nenhum suporte disponível..."; 
	echo sprintf("<meta http-equiv=\"refresh\" content=\"0; url=%s\">", "suporte.php"); 
	exit;
	
}
// fim - REDIRECIONA - se NÃO EXISTE o $id_suporte
// --------------------------------------------------------------------------------------------------------------------------------------------------------------------
// SELECT/UPDATE se EXISTE o $id_suporte, mostra os dados do suporte
else if ($id_suporte != "-1") {	

	// acessso --------------------------------------------------------------------------------------------------------------------------------------------------------------
	$acesso = 0;
	if (
		($row_suporte['status_flag'] != "f") and 
			(
			($row_usuario['controle_suporte'] == "Y" or $row_usuario['suporte_operador_parceiro'] == "Y") or  
			($row_suporte['id_usuario_envolvido'] == $row_usuario['IdUsuario']) or 
			($row_suporte['id_usuario_responsavel'] == $row_usuario['IdUsuario']) or 
			
			($row_suporte['praca'] == $row_usuario['praca'] and $row_suporte['situacao'] == 'criada') or 
			
			($row_usuario['controle_praca'] == "Y" and $row_usuario['praca'] = $row_suporte['praca'])
			)
		) {
	
		$acesso = 1; // autorizado
	
	}  else {
		
		$acesso = 0; // não autorizado
		
	}
	
	if($acesso==0){
		echo sprintf("<meta http-equiv=\"refresh\" content=\"0; url=%s\">", "suporte.php?padrao=sim&".$suporte_padrao);
		exit;
	}
	// fim - acesso ---------------------------------------------------------------------------------------------------------------------------------------------------------
	
	// verifica o tipo_suporte_inloco
	$tipo_suporte_inloco = "";
	if($row_suporte['tipo_suporte']=="c" and $row_suporte['inloco']=="s"){
		$tipo_suporte_inloco = "cs"; // cliente inloco SIM
	} else if($row_suporte['tipo_suporte']=="c" and $row_suporte['inloco']=="n"){
		$tipo_suporte_inloco = "cn"; // cliente inloco NAO
	} else if($row_suporte['tipo_suporte']=="p"){
		$tipo_suporte_inloco = "p"; // parceiro
	} else if($row_suporte['tipo_suporte']=="r"){
		$tipo_suporte_inloco = "r"; // reclamacao
	} 
	// verifica o tipo_suporte_inloco

	// cobranca / tipo_formulario
	$cobranca = "n";
	$tipo_formulario = NULL; // Manutencao / Extra / Cobranca / Treinamento / Reclamacao
	if($tipo_suporte_inloco == "cs" and $row_suporte['cobranca'] == "s"){
			$cobranca = "s";
			$tipo_formulario = "Cobranca";
	} else if($tipo_suporte_inloco == "cs"){
		$tipo_formulario = @$_POST['suporte_tipo_formulario'];
	}
	// fim - cobranca / tipo_formulario
	
	// reclamacao_vinculo
	$reclamacao_vinculo = "n";
	if($tipo_suporte_inloco == "cs" and $row_suporte['reclamacao_vinculo'] > 0){
		$reclamacao_vinculo = "s";
	}
	// fim - reclamacao_vinculo
	
	// função consulta créditos (contrato)
	$creditos = NULL;
	$creditos = funcao_suporte_credito($row_suporte['contrato']);
	// fim - função consulta créditos (contrato)

	// para selectbox ******************************************************************************************
	
	$usuario_atual = $row_usuario['IdUsuario'];
	
	// suporte_responsavel para selectbox
	// verifica e existe $_GET['IdUsuario']
	$IdUsuario = "-1"; 
	if (isset($_GET["IdUsuario"])) { 
		$IdUsuario = $_GET["IdUsuario"];
	}
	if (isset($_GET["IdUsuario"]) and $_GET["IdUsuario"]=="") {$IdUsuario = "-1";}
	// fim - verifica e existe $_GET['IdUsuario']
	
	mysql_select_db($database_conexao, $conexao);
	if($IdUsuario != -1){
		$query_suporte_responsavel = "SELECT IdUsuario, nome, praca FROM usuarios WHERE IdUsuario = $IdUsuario and status = '1' ";
	} else {
		$query_suporte_responsavel = "SELECT IdUsuario, nome, praca FROM usuarios WHERE status = '1' ";
	}

	// se existe filtragem por praça ou não
	if($tipo_suporte_inloco == "p" and ($row_usuario['controle_suporte'] == "Y" or $row_usuario['suporte_operador_parceiro'] == "Y")){
		$query_suporte_responsavel .= " and (controle_suporte = 'Y' or suporte_operador_parceiro = 'Y')";
	} else if($tipo_suporte_inloco == "r"){
		$query_suporte_responsavel .= " and praca = '".$row_suporte['praca']."'";
	} else if($tipo_suporte_inloco == "cs"){
		$query_suporte_responsavel .= " and praca = '".$row_suporte['praca']."'";
	} else if($tipo_suporte_inloco == "cn" and ($row_usuario['controle_suporte'] == "Y" or $row_usuario['suporte_operador_parceiro'] == "Y")){
		$query_suporte_responsavel .= " and (praca = '".$row_suporte['praca']."' or (controle_suporte = 'Y' or suporte_operador_parceiro = 'Y'))";
	} else if($tipo_suporte_inloco == "cn"){
		$query_suporte_responsavel .= " and praca = '".$row_suporte['praca']."'";

	} else {
		$query_suporte_responsavel .= " and 1=2";
	}
	// fim - se existe filtragem por praça ou não
	
	$query_suporte_responsavel .= " ORDER BY nome ASC";
	
	$suporte_responsavel = mysql_query($query_suporte_responsavel, $conexao) or die(mysql_error());
	$row_suporte_responsavel = mysql_fetch_assoc($suporte_responsavel);
	$totalRows_suporte_responsavel = mysql_num_rows($suporte_responsavel);
	// fim - suporte_responsavel para selectbox
	
	// suporte_envolvido para selectbox
	// verifica e existe $_GET['IdUsuario']
	$IdUsuario = "-1"; 
	if (isset($_GET["IdUsuario"])) { 
		$IdUsuario = $_GET["IdUsuario"];
	}
	if (isset($_GET["IdUsuario"]) and $_GET["IdUsuario"]=="") {$IdUsuario = "-1";}
	// fim - verifica e existe $_GET['IdUsuario']
	
	mysql_select_db($database_conexao, $conexao);
	if($IdUsuario != -1){
		$query_suporte_envolvido = "SELECT IdUsuario, nome FROM usuarios WHERE IdUsuario = $IdUsuario and status = '1' ";
	} else {
		$query_suporte_envolvido = "SELECT IdUsuario, nome FROM usuarios WHERE status = '1' ";
	}

	// se existe filtragem por praça ou não
	if( ($tipo_suporte_inloco == "cs" or $tipo_suporte_inloco == "cn") and $row_usuario['controle_suporte'] != "Y" ){
		
		$query_suporte_envolvido .= " and praca = '".$row_usuario['praca']."'";		
		
	}
	// fim - se existe filtragem por praça ou não
	
	$query_suporte_envolvido .= " and IdUsuario != $usuario_atual ORDER BY nome ASC";
	
	$suporte_envolvido = mysql_query($query_suporte_envolvido, $conexao) or die(mysql_error());
	$row_suporte_envolvido = mysql_fetch_assoc($suporte_envolvido);
	$totalRows_suporte_envolvido = mysql_num_rows($suporte_envolvido);
	// fim - suporte_envolvido para selectbox

	// geral_tipo_modulo para selectbox
	mysql_select_db($database_conexao, $conexao);
	$query_geral_tipo_modulo = "SELECT * FROM geral_tipo_modulo ORDER BY IdTipoModulo ASC";
	$geral_tipo_modulo = mysql_query($query_geral_tipo_modulo, $conexao) or die(mysql_error());
	$row_geral_tipo_modulo = mysql_fetch_assoc($geral_tipo_modulo);
	$totalRows_geral_tipo_modulo = mysql_num_rows($geral_tipo_modulo);
	// fim - geral_tipo_modulo para selectbox
	
	// suporte_tipo_atendimento para selectbox
	mysql_select_db($database_conexao, $conexao);
	$query_suporte_tipo_atendimento = "SELECT * FROM suporte_tipo_atendimento ORDER BY IdTipoAtendimento ASC";
	$suporte_tipo_atendimento = mysql_query($query_suporte_tipo_atendimento, $conexao) or die(mysql_error());
	$row_suporte_tipo_atendimento = mysql_fetch_assoc($suporte_tipo_atendimento);
	$totalRows_suporte_tipo_atendimento = mysql_num_rows($suporte_tipo_atendimento);
	// fim - suporte_tipo_atendimento para selectbox
		
	// suporte_tipo_recomendacao para selectbox
	mysql_select_db($database_conexao, $conexao);
	$query_suporte_tipo_recomendacao = "SELECT * FROM suporte_tipo_recomendacao ORDER BY IdTipoRecomendacao ASC";
	$suporte_tipo_recomendacao = mysql_query($query_suporte_tipo_recomendacao, $conexao) or die(mysql_error());
	$row_suporte_tipo_recomendacao = mysql_fetch_assoc($suporte_tipo_recomendacao);
	$totalRows_suporte_tipo_recomendacao = mysql_num_rows($suporte_tipo_recomendacao);
	// fim - suporte_tipo_recomendacao para selectbox
	
	// tipo de prioridades
	mysql_select_db($database_conexao, $conexao);
	$query_suporte_tipo_prioridade = "SELECT * FROM suporte_tipo_prioridade ORDER BY titulo ASC";
	$suporte_tipo_prioridade = mysql_query($query_suporte_tipo_prioridade, $conexao) or die(mysql_error());
	$row_suporte_tipo_prioridade = mysql_fetch_assoc($suporte_tipo_prioridade);
	$totalRows_suporte_tipo_prioridade = mysql_num_rows($suporte_tipo_prioridade);
	// fim - tipo de prioridades

	// fim - para selectbox ******************************************************************************************
	
	// UPDATE ----------------------------------------------------------------------------------------------------
	if (((isset($_POST["MM_update"])) and ($_POST["MM_update"] == "suporte")) or ((isset($_GET["MM_update"])) and ($_GET["MM_update"] == "suporte"))) {
		
			$data_atual = date('Y-m-d H:i:s');
					
			// converter entrada de data em portugues para ingles
			if ( isset($_POST['data_inicio']) and $_POST['data_inicio'] != "" ) {
				$data_data = substr($_POST['data_inicio'],0,10);
				$data_hora = substr($_POST['data_inicio'],10,9);
				$_POST['data_inicio'] = implode("-",array_reverse(explode("-",$data_data))).$data_hora;
			} else {
				$_POST['data_inicio'] = "0000-00-00 00:00:00";
			}
			
			if ( isset($_POST['data_fim']) and $_POST['data_fim'] != "" ) {
				$data_data = substr($_POST['data_fim'],0,10);
				$data_hora = substr($_POST['data_fim'],10,9);
				$_POST['data_fim'] = implode("-",array_reverse(explode("-",$data_data))).$data_hora;
			} else {
				$_POST['data_fim'] = "0000-00-00 00:00:00";
			}
			
			if ( isset($_POST['reclamacao_data_acordada']) and $_POST['reclamacao_data_acordada'] != "" ) {
				$data_data = substr($_POST['reclamacao_data_acordada'],0,10);
				$data_hora = substr($_POST['reclamacao_data_acordada'],10,9);
				$_POST['reclamacao_data_acordada'] = implode("-",array_reverse(explode("-",$data_data))).$data_hora;
			} else {
				$_POST['reclamacao_data_acordada'] = "0000-00-00 00:00:00";
			}
			// fim - converter entrada de data em portugues para ingles - fim
			
			// usuario_responsavel		
			$colname_usuario_responsavel = "-1";
			if (isset($_POST['suporte_responsavel'])) {
				$colname_usuario_responsavel = $_POST['suporte_responsavel'];
			}
			mysql_select_db($database_conexao, $conexao);
			$query_usuario_responsavel = sprintf("SELECT IdUsuario, nome FROM usuarios WHERE IdUsuario = %s", GetSQLValueString($colname_usuario_responsavel, "int"));
			$usuario_responsavel = mysql_query($query_usuario_responsavel, $conexao) or die(mysql_error());
			$row_usuario_responsavel = mysql_fetch_assoc($usuario_responsavel);
			$totalRows_usuario_responsavel = mysql_num_rows($usuario_responsavel);
			// fim - usuario_responsavel
										
			// usuario_envolvido		
			$colname_usuario_envolvido = "-1";
			if (isset($_POST['suporte_envolvido'])) {
				$colname_usuario_envolvido = $_POST['suporte_envolvido'];
			}
			mysql_select_db($database_conexao, $conexao);
			$query_usuario_envolvido = sprintf("SELECT IdUsuario, nome FROM usuarios WHERE IdUsuario = %s", GetSQLValueString($colname_usuario_envolvido, "int"));
			$usuario_envolvido = mysql_query($query_usuario_envolvido, $conexao) or die(mysql_error());
			$row_usuario_envolvido = mysql_fetch_assoc($usuario_envolvido);
			$totalRows_usuario_envolvido = mysql_num_rows($usuario_envolvido);
			// fim - usuario_envolvido

			// default campos
			$status_flag = "a";
			$status = "";
			$situacao = "em execução";

			$id_usuario_responsavel = $row_usuario_responsavel['IdUsuario'];
			$usuario_responsavel = $row_usuario_responsavel['nome'];
			
			$encaminhamento_id = '';
			$encaminhamento_data = '';
			$encaminhamento_data_inicio = '';
			
			$solicita_solicitacao = "n";
			
			$observacao = '';
			
			$valor = NULL;
			
			$visita_bonus = 'n';
			// fim - default campos
			
			// Atender
			if($row_usuario['IdUsuario'] == $row_usuario_responsavel['IdUsuario']){

				$status = "pendente usuario responsavel";				
				
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
			
			// para 'cliente' in-loco: 'sim' ------------------------------------------------------------------------------------------------------------------------
			if($tipo_suporte_inloco == "cs"){
				
				# formulario ****************************************************************************************

				$status_flag_formulario = "a";
				$situacao_formulario = "autorizado";
				
				$suporte_formulario_abater_credito = NULL;
				$credito = NULL;

				if($tipo_formulario == "Manutencao"){
					
					$suporte_formulario_abater_credito = "s";
					
				}
				
				if($tipo_formulario == "Cobranca"){
					
					$suporte_formulario_abater_credito = "n";
					
				}
				
				if($tipo_formulario == "Extra"){
					
					$suporte_formulario_abater_credito = "n";
					
					$status_flag = "b"; // bloqueado
					$status_flag_formulario = "b"; // bloqueado
					$situacao_formulario = "bloqueado";
					
				}
				
				if($tipo_formulario == "Treinamento"){
					
					$suporte_formulario_abater_credito = @$_POST['suporte_formulario_abater_credito'];
					
					if($suporte_formulario_abater_credito == "s"){
						$status_flag = "a"; // desbloqueado
						$status_flag_formulario = "a"; // desbloqueado
						$situacao_formulario = "autorizado";						
					}else{
						$status_flag = "b"; // bloqueado
						$status_flag_formulario = "b"; // bloqueado
						$situacao_formulario = "bloqueado";
					}
					
				}
				
				if($tipo_formulario == "Reclamacao"){
					
					$suporte_formulario_abater_credito = @$_POST['suporte_formulario_abater_credito'];
					
					if($suporte_formulario_abater_credito == "s"){
						$status_flag = "a"; // desbloqueado
						$status_flag_formulario = "a"; // desbloqueado
						$situacao_formulario = "autorizado";						
					}else{
						$status_flag = "b"; // bloqueado
						$status_flag_formulario = "b"; // bloqueado
						$situacao_formulario = "bloqueado";
					}
					
				}
				
				// credito ----------------------------------------------------------------------------------------------------------				
				if($row_manutencao_dados['visita17']=="3" or $row_manutencao_dados['visita17']=="4"){
					
					$contrato_tipo_visita = $row_manutencao_dados['visita17'];
					
					if(
					   $tipo_formulario == "Manutencao" or 
					   ($tipo_formulario == "Treinamento" and $suporte_formulario_abater_credito == "s") or 
					   ($tipo_formulario == "Reclamacao" and $suporte_formulario_abater_credito == "s")
					){
						
						// geral_credito_atual (busca o crédito a ser utilizado)
						mysql_select_db($database_conexao, $conexao);
						$query_geral_credito_atual = sprintf("
						SELECT IdCredito, data_criacao  
						FROM geral_credito 
						WHERE contrato = %s and status = 1 and data_utilizacao IS NULL
						ORDER BY data_criacao ASC LIMIT 1", 
						GetSQLValueString($row_suporte['contrato'], "text"));
						$geral_credito_atual = mysql_query($query_geral_credito_atual, $conexao) or die(mysql_error());
						$row_geral_credito_atual = mysql_fetch_assoc($geral_credito_atual);
						$totalRows_geral_credito_atual = mysql_num_rows($geral_credito_atual);
						// fim - geral_credito_atual (busca o crédito a ser utilizado)

						// se 'credito' é ZERO
						if($totalRows_geral_credito_atual == 0 and $creditos == 0){
							
							// geral_credito_ultimo (busca o ultimo 'credito' gerado 'ativo') - MENSAL e TRIMESTRAL
							mysql_select_db($database_conexao, $conexao);
							$query_geral_credito_ultimo = sprintf("
							SELECT IdCredito, data_criacao 
							FROM geral_credito 
							WHERE contrato = %s and status = 1
							ORDER BY data_criacao DESC LIMIT 1", 
							GetSQLValueString($row_manutencao_dados['codigo17'], "int"));
							$geral_credito_ultimo = mysql_query($query_geral_credito_ultimo, $conexao) or die(mysql_error());
							$row_geral_credito_ultimo = mysql_fetch_assoc($geral_credito_ultimo);
							$totalRows_geral_credito_ultimo = mysql_num_rows($geral_credito_ultimo);
							// fim - geral_credito_ultimo (busca o ultimo 'credito' gerado 'ativo') - MENSAL e TRIMESTRAL
							
							// aqui ocorria a ação do antigo recurso de adiantamento de visita, porém agora não existe mais (não existem mais créditos negativos ou adiantamento)

							mysql_free_result($geral_credito_ultimo);
							
						} else 
						// fim - se 'credito' é ZERO
						
						// senão...
						if($totalRows_geral_credito_atual > 0){
							
							// update - geral_credito
							$updateSQL = sprintf("
												 UPDATE geral_credito SET data_utilizacao = %s WHERE IdCredito = %s",
												 GetSQLValueString($data_atual, "date"),
												 GetSQLValueString($row_geral_credito_atual['IdCredito'], "int"));
							mysql_select_db($database_conexao, $conexao);
							$Result = mysql_query($updateSQL, $conexao) or die(mysql_error());
							// fim - geral_credito
							
							$credito = $row_geral_credito_atual['IdCredito'];
							
						}
						// fim - senão...

						mysql_free_result($geral_credito_atual);
						
					}
				}
				// fim - credito ----------------------------------------------------------------------------------------------------
				
				// $visita_bonus
				if($creditos == 0){
					$visita_bonus = $_POST['visita_bonus'];
				}
				// fim - $visita_bonus

				// valor
				if($tipo_formulario == "Extra"){

					$tempo_extra = ceil((strtotime($_POST['data_fim']) - strtotime($_POST['data_inicio'])) /60/60);

					if(
						($row_manutencao_dados['status17'] == "D" or $row_manutencao_dados['status17'] == "P") and 
						$row_empresa_dados['status1'] == "0" and 
						$row_empresa_dados['flag1'] == "0"
				  	){ 
						$valor = $tempo_extra*$row_parametros['valor_formulario_extra_com_manutencao'];
					} else {
						$valor = $tempo_extra*$row_parametros['valor_formulario_extra_sem_manutencao'];
					}

				}
				// fim - valor

				// insert - suporte_formulario
				$insertSQL_formulario = sprintf("
				INSERT INTO suporte_formulario (id_suporte, data, empresa, codigo_empresa, contrato, praca, id_usuario_responsavel, tipo_visita,  tipo_formulario, status_flag, situacao, creditar, credito, visita_bonus, valor) 
				VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)",
				
					   GetSQLValueString($row_suporte['id'], "int"),																																																																																																												
					   GetSQLValueString($data_atual, "date"),
					   GetSQLValueString($row_suporte['empresa'], "text"),
					   GetSQLValueString($row_suporte['codigo_empresa'], "text"),
					   GetSQLValueString($row_suporte['contrato'], "text"),
					   GetSQLValueString($row_suporte['praca'], "text"),
					   GetSQLValueString($row_usuario['IdUsuario'], "int"),
					   GetSQLValueString($row_manutencao_dados['visita17_descricao'], "text"),
					   GetSQLValueString($tipo_formulario, "text"),
					   GetSQLValueString($status_flag_formulario, "text"),
					   GetSQLValueString($situacao_formulario, "text"),
					   GetSQLValueString($suporte_formulario_abater_credito, "text"), 
					   GetSQLValueString($credito, "int"), 
					   GetSQLValueString($visita_bonus, "text"),
					   GetSQLValueString($valor, "text"));
			
				mysql_select_db($database_conexao, $conexao);
				$Result_formulario = mysql_query($insertSQL_formulario, $conexao) or die(mysql_error());
				// fim - insert - suporte_formulario
				
				# fim - formulario ****************************************************************************************
				
				$id_formulario_atual = mysql_insert_id($conexao); // pega o último formulario gerado
							
				$dados_suporte = array(
						"titulo" => $_POST['titulo'],
						
						"id_usuario_responsavel" => $id_usuario_responsavel,
						
						"encaminhamento_id" => $encaminhamento_id,
						"encaminhamento_data" => $encaminhamento_data,
						"encaminhamento_data_inicio" => $encaminhamento_data_inicio,
						
						"id_usuario_envolvido" => '',
						
						"data_inicio" => $_POST['data_inicio'],
						"data_fim" => $_POST['data_fim'],
						
						"status" => $status,
						"status_flag" => $status_flag,
						"situacao" => $situacao,
											
						"solicitante" => $_POST['solicitante'],
						"envolvido_reclamacao" => $_POST['envolvido_reclamacao'],
						"modulo" => "",
						"tipo_atendimento" => "",
						"prioridade" => $_POST['suporte_tipo_prioridade'],
						"anomalia" => $_POST['anomalia'],
						"orientacao" => "",
						"parecer" => "",
						"recomendacao" => "",
						"observacao" => $_POST['observacao'],
						
						"solicita_visita" => "n",
						"solicita_suporte" => "n",
						
						"tela" => "e",
						"id_formulario" => $id_formulario_atual,
						"tipo_formulario" => $tipo_formulario,
						
						"creditar" => $suporte_formulario_abater_credito, 
						"credito" => $credito,
						
						"visita_bonus" => $visita_bonus
				);
				
				// insert 'agenda'
				$insertSQL_suporte_agenda = sprintf("
				INSERT INTO agenda (id_suporte, id_usuario_responsavel, data_inicio, data, data_criacao, status, descricao) 
				VALUES (%s, %s, %s, %s, %s, %s, %s)",
				GetSQLValueString($row_suporte['id'], "int"),
				GetSQLValueString($id_usuario_responsavel, "int"),
				GetSQLValueString($_POST['data_inicio'], "date"),
				GetSQLValueString($_POST['data_fim'], "date"),
				GetSQLValueString(date("Y-m-d H:i:s"), "date"),
				GetSQLValueString("a", "text"), 
				GetSQLValueString("Agendamento de visita", "text"));
				
				mysql_select_db($database_conexao, $conexao);
				$Result_suporte_agenda = mysql_query($insertSQL_suporte_agenda, $conexao) or die(mysql_error());
				// fim - insert 'agenda'
				
			}
			// fim - para 'cliente' in-loco: 'sim' ------------------------------------------------------------------------------------------------------------------

			// para 'cliente' in-loco: 'nao' ------------------------------------------------------------------------------------------------------------------------
			if($tipo_suporte_inloco == "cn"){

				$dados_suporte = array(
						"titulo" => $_POST['titulo'],
						
						"id_usuario_responsavel" => $id_usuario_responsavel,

						"encaminhamento_id" => $encaminhamento_id,
						"encaminhamento_data" => $encaminhamento_data,
						"encaminhamento_data_inicio" => $encaminhamento_data_inicio,
						
						"id_usuario_envolvido" => '',
						
						"data_inicio" => $_POST['data_inicio'],
						"data_fim" => $_POST['data_fim'],
						
						"status" => $status,
						"status_flag" => $status_flag,
						"situacao" => $situacao,

						"solicitante" => $_POST['solicitante'],
						"envolvido_reclamacao" => $_POST['envolvido_reclamacao'],
						"modulo" => $_POST['geral_tipo_modulo'],
						"tipo_atendimento" => $_POST['suporte_tipo_atendimento'],
						"prioridade" => $_POST['suporte_tipo_prioridade'],
						"anomalia" => $_POST['anomalia'],
						"orientacao" => $_POST['orientacao'],
						"recomendacao" => "",
						"observacao" => $_POST['observacao'],
						
						"solicita_visita" => "n",
						"solicita_suporte" => "n",
						
						"tela" => "e",
						"id_formulario" => ""		
				);
				
			}
			// fim - para 'cliente' in-loco: 'nao' ------------------------------------------------------------------------------------------------------------------
			
			// para 'parceiro' --------------------------------------------------------------------------------------------------------------------------------------
			if($tipo_suporte_inloco == "p"){
								
				$dados_suporte = array(				   
						"status" => $status,
						"status_flag" => $status_flag,
						"situacao" => $situacao,
						
						"id_usuario_responsavel" => $id_usuario_responsavel,

						"id_usuario_envolvido" => $row_usuario_envolvido['IdUsuario'],
						
						"encaminhamento_id" => $encaminhamento_id,
						"encaminhamento_data" => $encaminhamento_data,
						"encaminhamento_data_inicio" => $encaminhamento_data_inicio,

						"titulo" => $_POST['titulo'],
						
						"data_inicio" => $_POST['data_inicio'],
						"data_fim" => $_POST['data_fim'],
						
						"solicitante" => $_POST['solicitante'],
						"modulo" => $_POST['geral_tipo_modulo'],
						"tipo_atendimento" => $_POST['suporte_tipo_atendimento'],
						"prioridade" => $_POST['suporte_tipo_prioridade'],
						"anomalia" => $_POST['anomalia'],
						"orientacao" => $_POST['orientacao'],
						"recomendacao" => $_POST['suporte_tipo_recomendacao'],
						"observacao" => $_POST['observacao'],
						
						"solicita_solicitacao" => $solicita_solicitacao,
						
						"solicita_visita" => "n",
						"solicita_suporte" => "n",
						
						"tela" => "e",
						"id_formulario" => ""
				);
				
			}
			// fim - para 'parceiro' --------------------------------------------------------------------------------------------------------------------------------
			
			// para 'reclamacao' ------------------------------------------------------------------------------------------------------------------------
			if($tipo_suporte_inloco == "r"){

				$dados_suporte = array(
						"titulo" => $_POST['titulo'],
						
						"id_usuario_responsavel" => $id_usuario_responsavel,

						"encaminhamento_id" => $encaminhamento_id,
						"encaminhamento_data" => $encaminhamento_data,
						"encaminhamento_data_inicio" => $encaminhamento_data_inicio,
						
						"id_usuario_envolvido" => '',
						
						"data_inicio" => date('Y-m-d H:i:s'),
						"data_fim" => date('Y-m-d H:i:s'),
						
						"previsao_geral_inicio" => date('Y-m-d H:i:s'),
						"previsao_geral" => date('Y-m-d H:i:s'),
						
						"status" => $status,
						"status_flag" => $status_flag,
						"situacao" => $situacao,

						"solicitante" => '',
						"modulo" => '',
						"tipo_atendimento" => '',
						"prioridade" => 'Alta',
						"anomalia" => '',
						"orientacao" => '',
						"recomendacao" => '',
						"observacao" => $_POST['observacao'],
						
						"solicita_visita" => "n",
						"solicita_suporte" => "n",
						
						"tela" => "e",
						"id_formulario" => '',
						"reclamacao" => $_POST['reclamacao'],
						"reclamacao_questionamento" => $_POST['reclamacao_questionamento'],
						"reclamacao_percepcao" => $_POST['reclamacao_percepcao'],
						"reclamacao_data_acordada" => $_POST['reclamacao_data_acordada'],
						"reclamacao_responsavel" => $_POST['reclamacao_responsavel'],
						"reclamacao_telefone" => $_POST['reclamacao_telefone']		
				);
				
				// descricao_reclamacao
				$descricao_reclamacao_status = 0;
				if($row_suporte['reclamacao_solicitacao'] > 0){
					$descricao_reclamacao_status = 1;
					$descricao_reclamacao_modulo = 'solicitacao';
				} else if($row_suporte['reclamacao_suporte'] > 0){
					$descricao_reclamacao_status = 1;
					$descricao_reclamacao_modulo = 'suporte';
				} else if($row_suporte['reclamacao_prospeccao'] > 0){
					$descricao_reclamacao_status = 1;
					$descricao_reclamacao_modulo = 'prospeccao';
				} else if($row_suporte['reclamacao_venda'] > 0){
					$descricao_reclamacao_status = 1;
					$descricao_reclamacao_modulo = 'venda';
				}

				if($descricao_reclamacao_status == 1){
					
					$descricao_reclamacao = 'Foi gerada uma reclamação vinculada de número: '.$row_suporte['id'];
					
					// insert - descricao (nova reclamação)
					$insertSQL_descricao_reclamacao = sprintf("
												 INSERT INTO ".$descricao_reclamacao_modulo."_descricoes 
												 (id_".$descricao_reclamacao_modulo.", descricao, id_usuario_responsavel, data, tipo_postagem) 
												 VALUES (%s, %s, %s, %s, %s)", 
												 GetSQLValueString($row_suporte['reclamacao_'.$descricao_reclamacao_modulo], "int"), 
												 GetSQLValueString($descricao_reclamacao, "text"), 
												 GetSQLValueString($row_usuario['IdUsuario'], "int"),
												 GetSQLValueString(date('Y-m-d H:i:s'), "date"), 
												 GetSQLValueString('Nova reclamação vinculada', "text"));
					mysql_select_db($database_conexao, $conexao);
					$Result_descricao_reclamacao = mysql_query($insertSQL_descricao_reclamacao, $conexao) or die(mysql_error());
					// fim - insert - descricao (nova reclamação)				
					
				}
				// fim - descricao_reclamacao
				
			}
			// fim - para 'reclamacao' ------------------------------------------------------------------------------------------------------------------
			
			// lê dados para a descrição
			$inloco = "";
			$tipo_suporte = "";			
			if($tipo_suporte_inloco == "cs"){
				$tipo_suporte = "cliente";
				$inloco = "sim";
			} else if($tipo_suporte_inloco == "cn"){ 
				$tipo_suporte = "cliente";
				$inloco = "não";
			} else if($tipo_suporte_inloco == "p"){
				$tipo_suporte = "parceiro";
				$inloco = "não";
			} else if($tipo_suporte_inloco == "r"){
				$tipo_suporte = "reclamação";
				$inloco = "não";
			}
			// fim - lê dados para a descrição
			
			// descrição
			$dados_suporte_descricao = array(
					"id_suporte" => $row_suporte['id'],
					"id_usuario_responsavel" => $row_usuario['IdUsuario'],
					"descricao" => "Tipo de suporte: ".$tipo_suporte." | In-loco: ".$inloco." | Situação: ".$situacao." | Status: ".$status,
					"data" => date("Y-m-d H:i:s"),
					"tipo_postagem" => "Novo suporte".$observacao
			);
			// fim - descrição

			funcao_suporte_update($row_suporte['id'], $dados_suporte, $dados_suporte_descricao);
			mysql_free_result($usuario_envolvido);
			
			// redireciona
			echo sprintf("<meta http-equiv=\"refresh\" content=\"0; url=%s\">", "suporte_editar.php?id_suporte=".$row_suporte['id']."&padrao=sim"); 
			// fim - redireciona
			exit;
			
	}
	// fim - UPDATE ----------------------------------------------------------------------------------------------
	
	// DELETE (Excluir) - caso não tenha inserido dados --------------------------------------------------------------------
	if ( isset($_GET["acao"]) and $_GET["acao"] == "excluir" and $row_suporte['tela']=="g" ) {
		
		$deleteSQL = sprintf("DELETE FROM suporte WHERE id = %s", GetSQLValueString($row_suporte['id'], "int"));
		mysql_select_db($database_conexao, $conexao);
		$Result_delete = mysql_query($deleteSQL, $conexao) or die(mysql_error());
		
		// redireciona
		echo sprintf("<meta http-equiv=\"refresh\" content=\"0; url=%s\">", "suporte.php?padrao=sim&".$suporte_padrao."");
		// fim - redireciona
		exit;
		
	}
	// fim - DELETE (Excluir) - caso não tenha inserido dados --------------------------------------------------------------------

	// UPDATE (Cancelar) - caso não tenha inserido dados --------------------------------------------------------------------
	if ( isset($_GET["acao"]) and $_GET["acao"] == "cancelar" and $row_suporte['tela']=="g" ) {		

		$dados_suporte = array(		
			"tipo_suporte" => "c",
			"inloco" => "n",
			"titulo" => $row_suporte['titulo_anterior'], 
			"titulo_anterior" => "",
			
			"id_usuario_envolvido" => '',
			
			"data_inicio" => date('Y-m-d H:i:s'),
			"data_fim" => "0000-00-00 00:00:00",
			"data_suporte_fim" => "0000-00-00 00:00:00",
			
			"status" => "pendente usuario responsavel",
			"status_flag" => "a",
			"situacao" => "em execução",
			
			"solicita_visita" => "n",
			"solicita_suporte" => "n",
			
			"status_devolucao" => "",
			"status_recusa" => "", 
			"cobranca" => "n",
			
			"tela" => "e",
			"id_formulario" => ""			
		);
		
		$dados_suporte_descricao = array(
			"id_suporte" => $row_suporte['id'],
			"id_usuario_responsavel" => $row_usuario['IdUsuario'],
			"descricao" => "",
			"data" => date("Y-m-d H:i:s"),
			"tipo_postagem" => "Cancelamento de agendamento de visita"
		);
		
		funcao_suporte_update($row_suporte['id'], $dados_suporte, $dados_suporte_descricao);

		// redireciona
		echo sprintf("<meta http-equiv=\"refresh\" content=\"0; url=%s\">", "suporte_editar.php?id_suporte=".$row_suporte['id']."&padrao=sim");
		// fim - redireciona
		exit;
		
	}
	// fim - UPDATE (Cancelar) - caso não tenha inserido dados --------------------------------------------------------------------

}
// fim - SELECT/UPDATE se EXISTE o $id_suporte, mostra os dados do suporte
//-------------------------------------------------------------------------------------------------------------------------------------------------------------------
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"> 
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">

<link rel="stylesheet" href="css/suporte.css" type="text/css" media="screen" />


<script type="text/javascript" src="js/jquery.js"></script>

<script type="text/javascript" src="funcoes.js"></script>

<script type="text/javascript" src="js/jquery.maskedinput-1.2.2.min.js"></script> 
<script type="text/javascript" src="js/jquery.rsv.js"></script> 

<link rel="stylesheet" href="css/thickbox.css" type="text/css" media="screen" />
<script type="text/javascript" src="js/thickbox.js"></script>

<link type="text/css" href="css/jquery-ui.css" rel="stylesheet" />	
<link type="text/css" href="css/jquery-ui-timepicker-addon.css" rel="stylesheet" />	

<script type="text/javascript" src="js/jquery-ui.js"></script>
<script type="text/javascript" src="js/jquery-ui-timepicker-addon.js"></script>

<script type="text/javascript" src="js/date.format.js"></script>
<script type="text/javascript" src="js/funcoes_data.js"></script>

<style>
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
</style>
<script type="text/javascript">
function myOnComplete()
{
	return true;
}

// validar diferença entre datas (funcao)
function data_inicial_final_menor(){
	
		var is_valid = true;
		var data_inicio = $("#data_inicio").val();
		var data_fim = $("#data_fim").val();

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
			// fim - quebra data inicial
			
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
			// fim - quebra data final
			
			var date1 = anoDI+"-"+mesDI+"-"+diaDI+" "+horaDI+":"+minutoDI+":"+segundoDI;
			var date2 = anoDF+"-"+mesDF+"-"+diaDF+" "+horaDF+":"+minutoDF+":"+segundoDF;
			
			is_valid = date1 < date2;

			if (!is_valid){
				var field = document.getElementById("data_inicio");
				return [[field, "Data final deve ser maior que a data inicial"]];
			}

		}

		return true;
}
// validar diferença entre datas (funcao)

$(document).ready(function() {
						   
	// tab/enter	
	textboxes = $("input, select, textarea");
	$("input, select").keypress(function(e){

		var tecla = (e.keyCode?e.keyCode:e.which);
		if(tecla == 13 || tecla == 9){
			
			// corrige problema - quando a agenda está aberta, o tab/enter fica com o FOCUS na hora_inicio			
			if ( $("#TB_window").length ) { // verifica se o tb_show está sendo exibido
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
	
	var rules = [];
	$("label > #req").hide();

	// campos desabilitados ------------------------------------------------------------------------------------------------------
	<? if($tipo_suporte_inloco == "cs"){ ?>
	
		$('input[id="titulo"]').attr('readonly', 'disabled');				
		$('select[id="geral_tipo_modulo"]').attr('disabled', true);
		$('select[id="suporte_tipo_atendimento"]').attr('disabled', true);
		$('textarea[id="orientacao"]').attr('disabled', true);
		$('select[id="suporte_tipo_recomendacao"]').attr('disabled', true);
		
		jQuery('input[id="suporte_tipo_formulario"]:radio[value="Cobranca"]').attr('disabled', true);
		jQuery('input[id="suporte_tipo_formulario"]:radio[value="Reclamacao"]').attr('disabled', true);
		
		jQuery('input[id="suporte_formulario_abater_credito"]').attr('disabled', true);
		jQuery("label[id=label_visita_bonus] > #req").hide(); // esconde asterisco*
		
		// tipo de visita/creditos (Manutencao)
		<? if(
		($row_manutencao_dados['visita17'] == 1 or $row_manutencao_dados['visita17'] == 5) or 
		(($row_manutencao_dados['visita17'] == 3 or $row_manutencao_dados['visita17'] == 4) and $creditos <= 0)
		){ ?>
			jQuery('input[id="suporte_tipo_formulario"]:radio[value="Manutencao"]').attr('disabled', true);
		<? } ?>
		// fim - tipo de visita/creditos (Manutencao)

		<? if(
		$creditos==0 and 
		$row_empresa_dados['tipo1'] == 'O' and 
		($row_manutencao_dados['visita17'] == "3" or $row_manutencao_dados['visita17'] == "4") and 
		((strtotime(date('Y-m', strtotime($row_suporte_formulario_bonus_ultimo['data'])))) <> (strtotime(date('Y-m')))) // ultimo bonus diferente do mês atual
		){
		?>
			jQuery('input[id="visita_bonus"]').attr('disabled', false);
			jQuery("label[id=label_visita_bonus] > #req").show(); // mostra asterisco*
			jQuery('input:radio[id="visita_bonus"]:nth(0)').attr('checked', true);
	
			jQuery('input[id="suporte_tipo_formulario"]:radio[value="Manutencao"]').attr('disabled', false);
			jQuery('input[id="suporte_tipo_formulario"]:radio[value="Treinamento"]').attr('disabled', false);
			jQuery('input[id="suporte_tipo_formulario"]:radio[value="Extra"]').attr('disabled', true);
			
			jQuery('input[id="suporte_formulario_abater_credito"]').attr('disabled', true);
			jQuery("label[id=label_suporte_formulario_abater_credito] > #req").hide(); // esconde asterisco*

		<? } else { ?>
			jQuery('input[id="visita_bonus"]').attr('disabled', true);
			jQuery("label[id=label_visita_bonus] > #req").hide(); // esconde asterisco*
		<? } ?>
		
		// 'cobranca'
		<? if($cobranca == "s"){ ?>
			jQuery('input[id="suporte_tipo_formulario"]:radio[value="Manutencao"]').attr('disabled', true);
			jQuery('input[id="suporte_tipo_formulario"]:radio[value="Extra"]').attr('disabled', true);
			jQuery('input[id="suporte_tipo_formulario"]:radio[value="Treinamento"]').attr('disabled', true);
			jQuery('input[id="suporte_tipo_formulario"]:radio[value="Reclamacao"]').attr('disabled', true);
			
			jQuery('input[id="suporte_tipo_formulario"]:radio[value="Cobranca"]').attr('disabled', false); // ativa formulario de cobrança
			jQuery('input[id="suporte_tipo_formulario"]:radio[value="Cobranca"]').attr('checked', 'true'); // seleciona formulario de cobrança
		<? } ?>
		// fim - 'cobranca'
		
		// 'reclamacao_vinculo'
		<? if($reclamacao_vinculo == "s"){ ?>
			jQuery('input[id="suporte_tipo_formulario"]:radio[value="Manutencao"]').attr('disabled', true);
			jQuery('input[id="suporte_tipo_formulario"]:radio[value="Extra"]').attr('disabled', true);
			jQuery('input[id="suporte_tipo_formulario"]:radio[value="Treinamento"]').attr('disabled', true);
			jQuery('input[id="suporte_tipo_formulario"]:radio[value="Cobranca"]').attr('disabled', true);
			
			jQuery('input[id="suporte_tipo_formulario"]:radio[value="Reclamacao"]').attr('disabled', false); // ativa formulario de reclamacao_vinculo
			jQuery('input[id="suporte_tipo_formulario"]:radio[value="Reclamacao"]').attr('checked', 'true'); // seleciona formulario de reclamacao_vinculo

			jQuery('input[id="suporte_formulario_abater_credito"]').attr('disabled', false);
			jQuery("label[id=label_suporte_formulario_abater_credito] > #req").show(); // mostra asterisco*
			
			// regras de validação - insere as regras
			$.each(rules, function() { // lê a regra atual
				// verifica se existe
				if (this == "required,suporte_formulario_abater_credito,Informe se irá ou não abater créditos."){
						rules.pop("required,suporte_formulario_abater_credito,Informe se irá ou não abater créditos."); // se existe, então exclui regra
				}
				// fim - verifica se existe
			});
			rules.push("required,suporte_formulario_abater_credito,Informe se irá ou não abater créditos.");		
			// fim - regras de validação - insere as regras
			
			// suporte_formulario_abater_credito
			<? if(
			($row_manutencao_dados['visita17'] == 1 or $row_manutencao_dados['visita17'] == 5) or 
			(($row_manutencao_dados['visita17'] == 3 or $row_manutencao_dados['visita17'] == 4) and $creditos <= 0) or 
			($row_empresa_dados['status1'] == 1 or $row_empresa_dados['flag1'] == 1)
			){ ?>
				jQuery('input:radio[id="suporte_formulario_abater_credito"]:nth(0)').attr('disabled', true);
				jQuery('input:radio[id="suporte_formulario_abater_credito"]:nth(1)').attr('checked', true);
			<? } ?>
			// fim - suporte_formulario_abater_credito

		<? } ?>
		// fim - 'reclamacao_vinculo'
		
	<? } else if($tipo_suporte_inloco == "cn"){ ?>
	
		$('select[id="suporte_tipo_recomendacao"]').attr('disabled', true);

	<? } else if($tipo_suporte_inloco == "p"){ ?>

	<? } else if($tipo_suporte_inloco == "r"){ ?>
	
		$('input[id="titulo"]').attr('readonly', 'disabled');
		
		$('input[id="solicitante"]').attr('disabled', true);
		$('select[id="geral_tipo_modulo"]').attr('disabled', true);
		$('select[id="suporte_tipo_atendimento"]').attr('disabled', true);
		$('textarea[id="orientacao"]').attr('disabled', true);
		$('select[id="suporte_tipo_recomendacao"]').attr('disabled', true);
		$('select[id="suporte_tipo_prioridade"]').attr('disabled', true);

	<? } ?>
	// fim - campos desabilitados ------------------------------------------------------------------------------------------------
		
	// pega o primeiro campo habilitado
	setTimeout(function() {$('#form :input:visible:enabled:first').focus();}, 100);
	// fim - pega o primeiro campo habilitado

	// campos obrigatórios - coloca o asterisco*	
	<? if($tipo_suporte_inloco == "cs"){ ?>
	
		$("label[id=label_suporte_tipo_formulario] > #req").show();
		$("label[id=label_suporte_responsavel] > #req").show();
		$("label[id=label_data_inicio] > #req").show();
		$("label[id=label_data_fim] > #req").show();
		$("label[id=label_agendamento_tempo] > #req").show();
		$("label[id=label_solicitante] > #req").show();
		<? if($row_suporte['reclamacao_vinculo'] > 0){ ?>
		$("label[id=label_envolvido_reclamacao] > #req").show();
		<? } ?>
		$("label[id=label_suporte_tipo_prioridade] > #req").show();
		$("label[id=label_suporte_tipo_prioridade] > #req").show();
		$("label[id=label_anomalia] > #req").show();
				
	<? } else if($tipo_suporte_inloco == "cn"){ ?>
	
		$("label[id=label_titulo] > #req").show();
		$("label[id=label_suporte_responsavel] > #req").show();
		$("label[id=label_data_inicio] > #req").show();
		$("label[id=label_solicitante] > #req").show();
		<? if($row_suporte['reclamacao_vinculo'] > 0){ ?>
		$("label[id=label_envolvido_reclamacao] > #req").show();
		<? } ?>
		$("label[id=label_geral_tipo_modulo] > #req").show();
		$("label[id=label_suporte_tipo_atendimento] > #req").show();
		$("label[id=label_suporte_tipo_prioridade] > #req").show();
		$("label[id=label_anomalia] > #req").show();
		$("label[id=label_observacao] > #req").show();
		
	<? } else if($tipo_suporte_inloco == "p"){ ?>
	
		$("label[id=label_titulo] > #req").show();
		$("label[id=label_suporte_responsavel] > #req").show();
		$("label[id=label_suporte_envolvido] > #req").show();
		$("label[id=label_data_inicio] > #req").show();
		$("label[id=label_geral_tipo_modulo] > #req").show();	
		$("label[id=label_suporte_tipo_atendimento] > #req").show();
		$("label[id=label_suporte_tipo_prioridade] > #req").show();
		$("label[id=label_suporte_tipo_recomendacao] > #req").show();
		$("label[id=label_anomalia] > #req").show();
		$("label[id=label_observacao] > #req").show();

	<? } else if($tipo_suporte_inloco == "r"){ ?>
	
		$("label[id=label_titulo] > #req").show();
		$("label[id=label_suporte_responsavel] > #req").show();
		$("label[id=label_reclamacao] > #req").show();
		$("label[id=label_reclamacao_questionamento] > #req").show();
		$("label[id=label_reclamacao_percepcao] > #req").show();
		$("label[id=label_reclamacao_data_acordada] > #req").show();
		$("label[id=label_reclamacao_responsavel] > #req").show();
		$("label[id=label_reclamacao_telefone] > #req").show();
		
	<? } ?>
	// fim - campos obrigatórios - coloca o asterisco*
	
    // Click no botão Botão: Salvar ---------------------------------------------------------------
	$('#button').click(function() {

		<? if($tipo_suporte_inloco == "cs"){ ?>

			// consulta automática - agenda
			if($("input[name=data_inicio]").val() != '' && $("input[name=data_fim]").val() != ''  && $("select[name=suporte_responsavel]").val() != '') {
				
				// post
				$.post("agenda_consulta.php", {
					   data_inicio: $("input[name=data_inicio]").val(), 
					   data_fim: $("input[name=data_fim]").val(),
					   id_usuario_responsavel: $("select[name=suporte_responsavel]").val(),
					   id_agenda: 0
					   }, function(data) {

							if(data == 0){
								$('#form').submit();
							}
							if(data == 1){
								alert("Data informada inválida. Existem agendamentos para o usuário atual durante período informado.");
								$('#data_fim').val('');
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

		<? } else { ?>
		
			$('#form').submit();
		
		<? } ?>

	});
	// fim - Click no botão Botão: Salvar ---------------------------------------------------------

	// suporte_tipo_formulario
	$("input[id='suporte_tipo_formulario']").change(function () { // ao mudar o valor do select
		$("input[id='suporte_tipo_formulario']:checked").each(function () {
			
			var suporte_tipo_formulario_atual = $(this).val(); // lê o valor selecionado
			
			// se é: Treinamento
			if( suporte_tipo_formulario_atual=="Treinamento" ){
				
				jQuery('input[id="suporte_formulario_abater_credito"]').attr('disabled', false);
				jQuery("label[id=label_suporte_formulario_abater_credito] > #req").show(); // mostra asterisco*
				jQuery('input[id="titulo"]').val('Agendamento de treinamento');
				
				// regras de validação - insere as regras
				$.each(rules, function() { // lê a regra atual
					// verifica se existe
					if (this == "required,suporte_formulario_abater_credito,Informe se irá ou não abater créditos."){
							rules.pop("required,suporte_formulario_abater_credito,Informe se irá ou não abater créditos."); // se existe, então exclui regra
					}
					// fim - verifica se existe
				});
				rules.push("required,suporte_formulario_abater_credito,Informe se irá ou não abater créditos.");		
				// fim - regras de validação - insere as regras
				
				// suporte_formulario_abater_credito
				<? if(
				($row_manutencao_dados['visita17'] == 1 or $row_manutencao_dados['visita17'] == 5) or 
				(($row_manutencao_dados['visita17'] == 3 or $row_manutencao_dados['visita17'] == 4) and $creditos <= 0)
				){ ?>
					jQuery('input:radio[id="suporte_formulario_abater_credito"]:nth(0)').attr('disabled', true);
					jQuery('input:radio[id="suporte_formulario_abater_credito"]:nth(1)').attr('checked', true);
				<? } ?>
				// fim - suporte_formulario_abater_credito

			}
			// fim - se é: Treinamento

			// se não ...
			else {
				
				jQuery('input[id="suporte_formulario_abater_credito"]').attr('disabled', true);
				jQuery("label[id=label_suporte_formulario_abater_credito] > #req").hide(); // esconde asterisco*
				jQuery('input[id="titulo"]').val('Agendamento de visita');
				
				// regras de validação - remove as regras					
				$.each(rules, function() {
					// verifica se existe data/hora fim
					if (this == "required,suporte_formulario_abater_credito,Informe se irá ou não abater créditos."){
							rules.pop("required,suporte_formulario_abater_credito,Informe se irá ou não abater créditos."); // se existe, então exclui regra
					}
					// fim - verifica se existe data/hora fim
				});
				// fim - regras de validação - remove as regras
				
				jQuery('input:radio[id="suporte_formulario_abater_credito"]:nth(1)').attr('checked', false);
							
			}
			// fim - se não ...	
			
		});
	});
	// fim - suporte_tipo_formulario
		
	// visita_bonus
	$("input[id='visita_bonus']").change(function () { // ao mudar o valor do select
		$("input[id='visita_bonus']:checked").each(function () {
			
			jQuery('input[id="suporte_tipo_formulario"]').attr('checked', false);
			
			jQuery('input[id="suporte_formulario_abater_credito"]').attr('disabled', true);
			jQuery('input[id="suporte_formulario_abater_credito"]').attr('checked', false);
			jQuery("label[id=label_suporte_formulario_abater_credito] > #req").hide(); // mostra asterisco*
			
			var visita_bonus_atual = $(this).val(); // lê o valor selecionado
			
			// sim
			if( visita_bonus_atual == "s" ){
				jQuery('input[id="suporte_tipo_formulario"]:radio[value="Manutencao"]').attr('disabled', false);
				jQuery('input[id="suporte_tipo_formulario"]:radio[value="Extra"]').attr('disabled', true);		
				jQuery('input[id="suporte_tipo_formulario"]:radio[value="Treinamento"]').attr('disabled', false);

			}
			// fim - sim

			// se não ...
			else {
				jQuery('input[id="suporte_tipo_formulario"]:radio[value="Manutencao"]').attr('disabled', true);
				jQuery('input[id="suporte_tipo_formulario"]:radio[value="Extra"]').attr('disabled', false);		
				jQuery('input[id="suporte_tipo_formulario"]:radio[value="Treinamento"]').attr('disabled', true);
				
				//jQuery('input[id="suporte_formulario_abater_credito"]').attr('disabled', true);
			}
			// fim - se não ...	
			
		});
	});
	// fim - visita_bonus

	// validação
	<? if($tipo_suporte_inloco == "cs"){ ?>
	
		rules.push("required,suporte_tipo_formulario,Informe o tipo de formulário.");
		rules.push("required,suporte_responsavel,Informe o responsavel.");
		rules.push("required,data_inicio,Informe a data de início.");
		rules.push("required,agendamento_tempo,Selecione o tempo de agendamento.");
		rules.push("required,data_fim,Informe a data final.");
		rules.push("function, data_inicial_final_menor");
		rules.push("length>=1,solicitante,Informe o solicitante.");
		<? if($row_suporte['reclamacao_vinculo'] > 0){ ?>
		rules.push("length>=1,envolvido_reclamacao,Informe o(s) envolvido(s) na reclamação.");
		<? } ?>
		rules.push("required,suporte_tipo_prioridade,Informe a prioridade.");
		rules.push("length>=10,anomalia,Informe a anomalia com no mínimo 10 caracteres.");
		
	<? } else if($tipo_suporte_inloco == "cn"){ ?>
	
		rules.push("length>=1,titulo,Informe o título.");
		rules.push("required,suporte_responsavel,Informe o responsavel.");
		rules.push("required,data_inicio,Informe a data de início.");
		rules.push("function, data_inicial_final_menor");		
		rules.push("length>=1,solicitante,Informe o solicitante.");
		<? if($row_suporte['reclamacao_vinculo'] > 0){ ?>
		rules.push("length>=1,envolvido_reclamacao,Informe o(s) envolvido(s) na reclamação.");
		<? } ?>
		rules.push("required,geral_tipo_modulo,Informe o módulo.");
		rules.push("required,suporte_tipo_atendimento,Informe o tipo de atendimento.");
		rules.push("required,suporte_tipo_prioridade,Informe a prioridade.");
		rules.push("length>=10,anomalia,Informe a anomalia com no mínimo 10 caracteres.");
		rules.push("length>=10,observacao,Informe a observação com no mínimo 10 caracteres.");
		
	<? } else if($tipo_suporte_inloco == "p"){ ?>
	
		rules.push("length>1,titulo,Informe o título.");
		rules.push("required,suporte_responsavel,Informe o responsavel.");
		rules.push("required,suporte_envolvido,Informe o envolvido.");
		rules.push("required,data_inicio,Informe a data de início.");
		rules.push("function, data_inicial_final_menor");		
		rules.push("required,geral_tipo_modulo,Informe o módulo.");
		rules.push("required,suporte_tipo_atendimento,Informe o tipo de atendimento.");
		rules.push("required,suporte_tipo_prioridade,Informe a prioridade.");
		rules.push("length>=10,anomalia,Informe a anomalia com no mínimo 10 caracteres.");
		rules.push("required,suporte_tipo_recomendacao,Informe a recomendação.");
		rules.push("length>=10,observacao,Informe a observação com no mínimo 10 caracteres.");
		
	<? } else if($tipo_suporte_inloco == "r"){ ?>
	
		rules.push("length>=1,titulo,Informe o título.");
		rules.push("required,suporte_responsavel,Informe o responsavel.");
		rules.push("length>=10,reclamacao,Informe a reclamação com no mínimo 10 caracteres.");
		rules.push("length>=10,reclamacao_questionamento,Informe questionamento inicial da Success com no mínimo 10 caracteres.");
		rules.push("required,reclamacao_percepcao,Informe a percepção.");
		rules.push("required,reclamacao_data_acordada,Informe data acordada p/ resposta.");
		rules.push("required,reclamacao_responsavel,Informe o reclamante.");
		rules.push("required,reclamacao_telefone,Informe o telefone de contato direto com o reclamante.");
		
	<? } ?>

	$("#form").RSV({
			onCompleteHandler: myOnComplete,
			rules: rules
	});			
	// fim - validação
	
	// mascara
	$('#data_inicio').mask('99-99-9999 99:99',{placeholder:" "});
	$('#data_fim').mask('99-99-9999 99:99',{placeholder:" "});
	$('#reclamacao_data_acordada').mask('99-99-9999 99:99',{placeholder:" "});
	// mascara - fim
	
    // abrir agenda
	<? if($tipo_suporte_inloco == "cs"){ ?>
	$('#ver_agenda').click(function() {		
		
		var id_usuario_responsavel = $("select[id='suporte_responsavel']").val();
		data_atual = $('#data_inicio').val();

		tb_show("Agenda","agenda_popup.php?id_usuario_atual="+id_usuario_responsavel+"&data_atual="+data_atual+"&height=<? echo $suporte_editar_tabela_height-100; ?>&width=<? echo $suporte_editar_tabela_width-40; ?>&placeValuesBeforeTB_=savedValues&TB_iframe=true","");
		return false;
		
	});
	<? } ?>
	// fim - abrir agenda

	// calendario
	<? if($tipo_suporte_inloco == "cs" or $tipo_suporte_inloco == "cn" or $tipo_suporte_inloco == "p"){ ?>
	var data_inicio = $('#data_inicio');
	var data_fim = $('#data_fim');
	
	data_inicio.datetimepicker({ 	
		data_atual_juliano: '<?php echo time() * 1000 ?>',
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
			
			data_fim.val('');
			data_inicio.datetimepicker('option', 'minDate', new Date(<?php echo time() * 1000 ?>) ); // para 'data'
			data_inicio.datetimepicker('option', 'minDateTime', new Date(<?php echo time() * 1000 ?>) ); // para 'hora'
			
		},
		onSelect: function () {
			document.all ? $(this).get(0).fireEvent("onchange") : $(this).change();
			this.focus();
		},
		onClose: function (dateText, inst) {
			if (!document.all){
				this.select();
			}
		}
		
	});
	<? } ?>
	
	<? if($tipo_suporte_inloco == "cn" or $tipo_suporte_inloco == "p"){ ?>
	data_fim.datetimepicker({ 
							
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
			
			var teste_data_inicio = data_inicio.datetimepicker('getDate');
			var teste_data_fim = data_fim.datetimepicker('getDate');
			
			if(teste_data_inicio != null){
				data_fim.datetimepicker('option', 'minDate', data_inicio.datetimepicker('getDate') ); // para 'data'
				data_fim.datetimepicker('option', 'minDateTime', data_inicio.datetimepicker('getDate') ); // para 'hora'
			}else{
				data_fim.datetimepicker('option', 'minDate', new Date(<?php echo time() * 1000 ?>) ); // para 'data'
				data_fim.datetimepicker('option', 'minDateTime', new Date(<?php echo time() * 1000 ?>) ); // para 'hora'
			}
			
		}
		
	});
	<? } ?>
	// fim - calendario
	
	// verifica se é uma data válida/agenda auto
    $('#data_inicio').blur(function(){
									
		var campo = $(this);
		var erro = funcao_verifica_data_hora_valida(campo, <?php echo time() * 1000 ?>) // chamada da função (retorna 0/1)
		
		// erro	
		if(erro==1){
			
			alert("Data inválida");
			$('#data_inicio').val('');
			$('#data_fim').val('');
			setTimeout(function() {campo.focus();}, 100);
			return false;
			
		}
		// fim - erro

		// agenda auto
		<? if($tipo_suporte_inloco == "cs"){ ?>
		else if($(this).val().length == 16) {
			
			var id_usuario_responsavel = $("select[id='suporte_responsavel']").val();
			data_atual = $('#data_inicio').val();
	
			tb_show("Agenda","agenda_popup.php?id_usuario_atual="+id_usuario_responsavel+"&data_atual="+data_atual+"&height=<? echo $suporte_editar_tabela_height-100; ?>&width=<? echo $suporte_editar_tabela_width-40; ?>&placeValuesBeforeTB_=savedValues&TB_iframe=true","");
			return false;
			
		}
		<? } ?>
		// fim - agenda auto
		
    });
	
    $('#data_fim').blur(function(){

		var campo = $(this);
		var erro = funcao_verifica_data_hora_valida(campo, <?php echo time() * 1000 ?>) // chamada da função (retorna 0/1)
		
		// erro	
		if(erro==1){
			
			alert("Data inválida");
			campo.val('');
			setTimeout(function() {campo.focus();}, 100);
			return false;
			
		}
		// fim - erro
		
    });

    $('#reclamacao_data_acordada').blur(function(){

		var campo = $(this);
		var erro = funcao_verifica_data_hora_valida(campo, <?php echo time() * 1000 ?>) // chamada da função (retorna 0/1)
		
		// erro	
		if(erro==1){
			
			alert("Data inválida");
			campo.val('');
			setTimeout(function() {campo.focus();}, 100);
			return false;
			
		}
		// fim - erro
		
    });
	// fim - verifica se é uma data válida/agenda auto
	
	// agendamento_tempo
	<? if($tipo_suporte_inloco == "cs"){ ?>
	$("select[name=agendamento_tempo]").change(function(){
														
		var agendamento_tempo = $(this).val();

		var data_inicio = $("#data_inicio").val();
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
		
		$("#data_fim").val(date1);
		
	});
	<? } ?>
	// fim - agendamento_tempo
	
	$("input:disabled, textarea:disabled, select:disabled").addClass("campo_desabilitado");
	
});

// confirma excluir
function confirmaExcluir(){
	return confirm("Esta acão irá excluir o suporte atual. Confirma?");
}
// fim - confirma excluir	

// confirma cancelar
function confirmaCancelar(){
	return confirm("Esta acão irá cancelar o suporte atual. Confirma?");
}
// fim - confirma cancelar

// Máscara para campo data dd-mm-aaaa hh:mm:ss (não está utilizando)
function DataHora(evento, objeto){
	var keypress=(window.event)?event.keyCode:evento.which;
	campo = eval (objeto);
	if (campo.value == '00-00-0000 00:00:00')
	{
		campo.value=""
	}

	caracteres = '0123456789';
	separacao1 = '-';
	separacao2 = ' ';
	separacao3 = ':';
	conjunto1 = 2;
	conjunto2 = 5;
	conjunto3 = 10;
	conjunto4 = 13;
	conjunto5 = 16;
	if ((caracteres.search(String.fromCharCode (keypress))!=-1) && campo.value.length < (16))
	{
		if (campo.value.length == conjunto1 )
		campo.value = campo.value + separacao1;
		else if (campo.value.length == conjunto2)
		campo.value = campo.value + separacao1;
		else if (campo.value.length == conjunto3)
		campo.value = campo.value + separacao2;
		else if (campo.value.length == conjunto4)
		campo.value = campo.value + separacao3;
		else if (campo.value.length == conjunto5)
		campo.value = campo.value + separacao3;
	}
	else
		event.returnValue = false;
}
// fim - Máscara para campo data dd-mm-aaaa hh:mm:ss (não está utilizando)
</script>


<title>Novo suporte n° <? echo $row_suporte['id']; ?></title>
</head>

<body>
<form id="form" name="form" method="POST" action="<?php echo $editFormAction; ?>">
<input name="id_suporte" type="hidden" value="<?php echo $row_suporte['id']; ?>" />

<div class="<? if($tipo_suporte_inloco == "r"){ ?>div_solicitacao_linhas_laranja<? } else { ?>div_solicitacao_linhas<? } ?>">
<table cellspacing="0" cellpadding="0" width="100%">
	<tr>
		<td style="text-align:left">
        <?
		if($tipo_suporte_inloco == "cs"){
			echo "Suporte ao cliente (in-loco: Sim)";
		} else if($tipo_suporte_inloco == "cn"){ 
			echo "Suporte ao cliente (in-loco: Não)";
		} else if($tipo_suporte_inloco == "p"){
			echo "Suporte ao parceiro";
		} else if($tipo_suporte_inloco == "r"){
			echo "Reclamação";
		}
		?>
        - n° <? echo $row_suporte['id']; ?>
		</td>

		<td style="text-align: right">
		&lt;&lt; <a href="suporte.php?padrao=sim&<? echo $suporte_padrao; ?>">Voltar</a> | 
		Usuário logado: <? echo $row_usuario['nome']; ?> |
		<a href="painel/padrao_sair.php">Sair</a>
        </td>
	</tr>
</table>
</div>

<div class="div_solicitacao_linhas2">
<table cellspacing="0" cellpadding="0" width="945">
	<tr>
		<td style="text-align:left">       
        <?php echo utf8_encode($row_empresa_dados['nome1']); ?> |
        <span class="label_solicitacao">Fantasia:</span> <?php echo utf8_encode($row_empresa_dados['fantasia1']); ?> |
        <span class="label_solicitacao">Fone:</span> <?php echo $row_empresa_dados['telefone1']; ?> <?php if($row_empresa_dados['comercio1'] > 0){ ?> | <?php echo $row_empresa_dados['comercio1']; ?> | <? } ?>
        </td>
        
		<td style="text-align:right">
        <span class="label_solicitacao">Representante legal:</span> <?php echo $row_empresa_dados['contato1']; ?>
        </td>
	</tr>
</table>
</div>

<div class="div_solicitacao_linhas3">
<table cellspacing="0" cellpadding="0" width="945">
	<tr>
		<td style="text-align:left">
		<span class="label_solicitacao">Localização: </span>
		<? echo utf8_encode($row_empresa_dados['endereco1']); ?> - <?php echo utf8_encode($row_empresa_dados['bairro1']); ?> - 
        CEP: <?php echo $row_empresa_dados['cep1']; ?> | <?php echo utf8_encode($row_empresa_dados['cidade1']); ?> - <?php echo $row_empresa_dados['uf1']; ?>        
        </td>
	</tr>
</table>
</div>

<div class="div_solicitacao_linhas4">
<table cellspacing="0" cellpadding="0" width="945">
	<tr>
		<td style="text-align:left">
		<span class="label_solicitacao">Obs. sobre o cliente: </span>
		<?php echo utf8_encode($row_manutencao_dados['obs17']); ?>
        </td>
	</tr>
</table>
</div>

<div class="div_solicitacao_linhas3">
<table cellspacing="0" cellpadding="0" width="945">
	<tr>
		<td style="text-align:left">
        <span class="label_solicitacao">Contrato:</span> <?php echo $row_manutencao_dados['codigo17']; ?> | 
        <span class="label_solicitacao">Tipo do contrato:</span> <?php echo $row_manutencao_dados['tpocont17_descricao']; ?> | 
	    <span class="label_solicitacao">Créditos de visitas:</span> <?php echo $creditos; ?> | 

        <span class="label_solicitacao">Direito a visita bônus: </span>
		<? if(
		$creditos==0 and 
		$row_empresa_dados['tipo1'] == 'O' and 
		($row_manutencao_dados['visita17'] == "3" or $row_manutencao_dados['visita17'] == "4") and 
		((strtotime(date('Y-m', strtotime($row_suporte_formulario_bonus_ultimo['data'])))) <> (strtotime(date('Y-m')))) // ultimo bonus diferente do mês atual
		){ ?>Sim<? } else { ?>Não<? } ?>
        </td>
        
		<td style="text-align:right">        
        <span class="label_solicitacao">Versão:</span> 
        <?php if($row_manutencao_dados['espmod17']=="B"){ echo "Standard";} ?>
        <?php if($row_manutencao_dados['espmod17']=="O"){ echo "Office";} ?>
        </td>
	</tr>
</table>
</div>

<div class="div_solicitacao_linhas4">
<table cellspacing="0" cellpadding="0" width="945">
	<tr>
		<td style="text-align:left" width="450">
        <span class="label_solicitacao">Plano de Manutenção:</span> <?php echo $row_manutencao_dados['visita17_descricao']; ?> | 
        <span class="label_solicitacao">Optante por acumulo de manutenção:</span> <?php if($row_manutencao_dados['optacuv17']=="N"){echo "Não";} if($row_manutencao_dados['optacuv17']=="S"){echo "Sim";} ?>
        </td>
        
		<td style="text-align:right">
        <span class="label_solicitacao">Total dias em atraso:</span> <?php echo $row_empresa_dados['atraso1']; ?> 
        </td>
	</tr>
</table>
</div>

<div class="div_solicitacao_linhas3">
<table cellspacing="0" cellpadding="0" width="945">
	<tr>
		<td style="text-align:left">
        <span class="label_solicitacao">Módulos:</span> 
        <? if($row_modcon['modest']!=NULL){echo "(X) Estoque ";} ?>  
        <? if($row_modcon['modfin']!=NULL){echo "(X) Financeiro ";} ?>
        <? if($row_modcon['modser']!=NULL){echo "(X) Serviço ";} ?>
        <? if($row_modcon['modoti']!=NULL){echo "(X) Ótica ";} ?>
        <? if($row_modcon['modpdv']!=NULL){echo "(X) PDV ";} ?>
        <? if($row_modcon['modpve']!=NULL){echo "(X) PVE ";} ?>
        <? if($row_modcon['modben']!=NULL){echo "(X) Bens ";} ?>
        </td>

		<td style="text-align:right">
        <span class="label_solicitacao">Ferramentas adicionais:</span> 
        <? if($row_modcon['ferlot']!=NULL){echo "(X) Lote bancário ";} ?>
        <? if($row_modcon['fernfe']!=NULL){echo "(X) NFE ";} ?>
        <? if($row_modcon['ferefd']!=NULL){echo "(X) EFD ";} ?>
        <? if($row_modcon['ferrelcon']!=NULL){echo "(X) Relatórios Consultoria ";} ?>
        <? if($row_modcon['fermes']!=NULL){echo "(X) Controle Mesa ";} ?>

        <? if($row_modcon['ferbin']!=NULL){echo "(X) Bina ";} ?>
        <? if($row_modcon['ferfid']!=NULL){echo "(X) Cartão Fidelidade ";} ?>
        
        <? if($row_modcon['fertdi']!=NULL){echo "(X) Tef Discado ";} ?>
        <? if($row_modcon['fertdd']!=NULL){echo "(X) Tef Dedicado ";} ?>
        <? if($row_modcon['fertpy']!=NULL){echo "(X) Tef Pay&Go ";} ?>
        </td>
	</tr>
</table>
</div>


<? if($tipo_suporte_inloco == "cs"){ ?>
<div class="div_solicitacao_linhas4">
<table cellspacing="0" cellpadding="0" width="945">
	<tr>
		<td style="text-align:left" width="600">
        <span class="label_solicitacao"><label id="label_suporte_tipo_formulario">Escolha o tipo do formulário:<span id="req">*</span></label></span>
        <br>
		<input type="radio" name="suporte_tipo_formulario" id="suporte_tipo_formulario" value="Manutencao"> Manutenção 
        <input type="radio" name="suporte_tipo_formulario" id="suporte_tipo_formulario" value="Extra"> Extra
        <input type="radio" name="suporte_tipo_formulario" id="suporte_tipo_formulario" value="Treinamento"> Treinamento
        <input type="radio" name="suporte_tipo_formulario" id="suporte_tipo_formulario" value="Cobranca"> Cobrança
        <input type="radio" name="suporte_tipo_formulario" id="suporte_tipo_formulario" value="Reclamacao"> Reclamação
        </td>
        
		<td style="text-align:right">
        <span class="label_solicitacao"><label id="label_visita_bonus">Visita bônus:<span id="req">*</span></label></span>
		<input type="radio" name="visita_bonus" id="visita_bonus" value="s"> Sim 
        <input type="radio" name="visita_bonus" id="visita_bonus" value="n"> Não
        
        <br>
        
        <span class="label_solicitacao"><label id="label_suporte_formulario_abater_credito">Abater nos créditos:<span id="req">*</span></label></span>
		<input type="radio" name="suporte_formulario_abater_credito" id="suporte_formulario_abater_credito" value="s"> Sim 
        <input type="radio" name="suporte_formulario_abater_credito" id="suporte_formulario_abater_credito" value="n"> Não
        </td>
	</tr>
</table>
</div>
<? } ?>

<div class="div_solicitacao_linhas3">
<table cellspacing="0" cellpadding="0" width="945">
	<tr>
		<td style="text-align:left" width="525">
		<span class="label_solicitacao"><label id="label_titulo">Título:<span id="req">*</span></label></span>
		<input name="titulo" type="text" id="titulo" style="width: 470px;" value="<?php echo $row_suporte['titulo']; ?>">
        </td>
        
        <td style="text-align: right">        
		<span class="label_solicitacao"><label id="label_suporte_responsavel">Responsável<span id="req">*</span>:</label></span>
        <select name="suporte_responsavel" id="suporte_responsavel" style="width: 320px;">
        <option value="">Selecione ...</option>
        <?php
        do {  
        ?>
        <option value="<?php echo $row_suporte_responsavel['IdUsuario']?>">
		<?php echo utf8_encode($row_suporte_responsavel['nome']); ?> [<?php echo $row_suporte_responsavel['praca']?>]
        </option>
        <?php
        } while ($row_suporte_responsavel = mysql_fetch_assoc($suporte_responsavel));
        $rows = mysql_num_rows($suporte_responsavel);
        if($rows > 0) {
        mysql_data_seek($suporte_responsavel, 0);
        $row_suporte_responsavel = mysql_fetch_assoc($suporte_responsavel);
        }
        ?>
        </select>

		<? if($tipo_suporte_inloco == "p"){ ?>
        <br><br>
		<span class="label_solicitacao"><label id="label_suporte_envolvido">Envolvido<span id="req">*</span>:</label></span>
        <select name="suporte_envolvido" id="suporte_envolvido" style="width: 320px;">
        <option value="">Selecione ...</option>
        <?php
        do {  
        ?>
        <option value="<?php echo $row_suporte_envolvido['IdUsuario']?>"
		<?php
		// caso tenha o usuário já definido
		if($row_suporte['id_usuario_envolvido'] != ""){
	        if (!(strcmp($row_suporte_envolvido['IdUsuario'], $row_suporte['id_usuario_envolvido']))) {echo "selected=\"selected\"";}
		}
		// caso tenha o usuário já definido		
		?>>
		<?php echo utf8_encode($row_suporte_envolvido['nome']); ?>
        </option>
        <?php
        } while ($row_suporte_envolvido = mysql_fetch_assoc($suporte_envolvido));
        $rows = mysql_num_rows($suporte_envolvido);
        if($rows > 0) {
        mysql_data_seek($suporte_envolvido, 0);
        $row_suporte_envolvido = mysql_fetch_assoc($suporte_envolvido);
        }
        ?>
        </select>
        <? } ?>
        </td>

	</tr>
</table>
</div>

<? if($tipo_suporte_inloco == "cs" or $tipo_suporte_inloco == "cn" or $tipo_suporte_inloco == "p"){ ?>
<div class="div_solicitacao_linhas4">
<table cellspacing="0" cellpadding="0" width="945">
	<tr>
		<td style="text-align:left" width="350">
		<span class="label_solicitacao"><label id="label_data_inicio">Data inicio:<span id="req">*</span></label></span>
        <input name="data_inicio" type="text" id="data_inicio" style="width: 150px;" maxlength="16"> 
        <? if($tipo_suporte_inloco == "cs"){ ?>
        <a href="#" id="ver_agenda" name="ver_agenda" style="color: #03C; font-weight: bold; padding-left: 5px;">Ver agenda</a>
        <? } ?>
        </td>
        
        <? if($tipo_suporte_inloco == "cs"){ ?>
        <td width="350">
        <span class="label_solicitacao"><label id="label_agendamento_tempo">Tempo:<span id="req">*</span></label></span>
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
			<option value="<? echo $mm = 360; ?>"><? echo sprintf("%02dh %02dm", floor($mm/60), $mm%60); ?></option>
			<option value="<? echo $mm = 420; ?>"><? echo sprintf("%02dh %02dm", floor($mm/60), $mm%60); ?></option>
			<option value="<? echo $mm = 480; ?>"><? echo sprintf("%02dh %02dm", floor($mm/60), $mm%60); ?></option>
			<option value="<? echo $mm = 540; ?>"><? echo sprintf("%02dh %02dm", floor($mm/60), $mm%60); ?></option>
			<option value="<? echo $mm = 600; ?>"><? echo sprintf("%02dh %02dm", floor($mm/60), $mm%60); ?></option>
        </select>
        </td>
		<? } ?>
        
		<td align="right">        
		<span class="label_solicitacao"><label id="label_data_fim">Data fim:<span id="req">*</span></label></span>
        <input name="data_fim" type="text" id="data_fim" style="width: 150px;" maxlength="16" <? if($tipo_suporte_inloco == "cs"){ ?>readonly="readonly"<? } ?>>
        </td>

	</tr>
</table>
</div>
<? } ?>

<? if($tipo_suporte_inloco == "cs" or $tipo_suporte_inloco == "cn" or $tipo_suporte_inloco == "p"){ ?>
<div class="div_solicitacao_linhas3">
<table cellspacing="0" cellpadding="0" width="945">
	<tr>

		<td style="text-align:left" width="400">
		<span class="label_solicitacao"><label id="label_solicitante">Solicitante:<span id="req">*</span></label></span>
		<input name="solicitante" type="text" id="solicitante" style="width: 310px;" value="<? echo $row_suporte['solicitante']; ?>">
		</td>
        
    	<td style="text-align:left" width="170">
		<span class="label_solicitacao"><label id="label_geral_tipo_modulo">Módulo:<span id="req">*</span></label></span>
        <select name="geral_tipo_modulo" id="geral_tipo_modulo" style="width: 110px;">
        <option value="">Selecione ...</option>
        <?php
        do {  
        ?>
        <option value="<?php echo $row_geral_tipo_modulo['descricao']?>"><?php echo $row_geral_tipo_modulo['descricao']?></option>
        <?php
        } while ($row_geral_tipo_modulo = mysql_fetch_assoc($geral_tipo_modulo));
        $rows = mysql_num_rows($geral_tipo_modulo);
        if($rows > 0) {
        mysql_data_seek($geral_tipo_modulo, 0);
        $row_geral_tipo_modulo = mysql_fetch_assoc($geral_tipo_modulo);
        }
        ?>
        </select>
        </td>
        
    	<td style="text-align: right">    
		<span class="label_solicitacao"><label id="label_suporte_tipo_atendimento">Tipo de atendimento:<span id="req">*</span></label></span>
        <select name="suporte_tipo_atendimento" id="suporte_tipo_atendimento" style="width: 225px;">
        <option value="">Selecione ...</option>
        <?php
        do {  
        ?>
        <option value="<?php echo $row_suporte_tipo_atendimento['descricao']?>"><?php echo $row_suporte_tipo_atendimento['descricao']?></option>
        <?php
        } while ($row_suporte_tipo_atendimento = mysql_fetch_assoc($suporte_tipo_atendimento));
        $rows = mysql_num_rows($suporte_tipo_atendimento);
        if($rows > 0) {
        mysql_data_seek($suporte_tipo_atendimento, 0);
        $row_suporte_tipo_atendimento = mysql_fetch_assoc($suporte_tipo_atendimento);
        }
        ?>
        </select>
		</td>        
	</tr>
</table>
</div>
<? } ?>

<? if($tipo_suporte_inloco == "cs" or $tipo_suporte_inloco == "cn"){ ?>
	<? if($row_suporte['reclamacao_vinculo'] > 0){ ?>
    
    <div class="div_solicitacao_linhas4">
    <table cellspacing="0" cellpadding="0" width="945">
        <tr>
    
            <td style="text-align:left">
            <span class="label_solicitacao"><label id="label_envolvido_reclamacao">Envolvido(s) na reclamação:<span id="req">*</span></label></span>
            <input name="envolvido_reclamacao" type="text" id="envolvido_reclamacao" style="width: 310px;" value="<? echo $row_suporte['envolvido_reclamacao']; ?>">
            </td>
            
            <td style="text-align:left">&nbsp;</td>
        </tr>
    </table>
    </div>
    
    <? } else { ?>
		<input name="envolvido_reclamacao" id="envolvido_reclamacao" type="hidden" value="">
	<? } ?>
<? } ?>

<? if($tipo_suporte_inloco == "cs" or $tipo_suporte_inloco == "cn" or $tipo_suporte_inloco == "p"){ ?>
<div class="div_solicitacao_linhas2">
<table cellspacing="0" cellpadding="0" width="100%">
  <tr>
    <td style="text-align: left">
	<span class="label_solicitacao"><label id="label_anomalia">Anomalia:<span id="req">*</span></label></span>
	<br>
	<textarea name="anomalia" id="anomalia" style="margin-top: 2px; width: 945px; height: 70px;"></textarea>
	</td>
  </tr>
</table>
</div>
<? } ?>

<? if($tipo_suporte_inloco == "cs" or $tipo_suporte_inloco == "cn" or $tipo_suporte_inloco == "p"){ ?>
<div class="div_solicitacao_linhas2">
<table cellspacing="0" cellpadding="0" width="100%">
  <tr>
    <td style="text-align: left">
	<span class="label_solicitacao"><label id="label_orientacao">Orientação:<span id="req">*</span></label></span>
	<br>
	<textarea name="orientacao" id="orientacao" style="margin-top: 2px; width: 945px; height: 70px;"></textarea>
	</td>
  </tr>
</table>
</div>
<? } ?>

<? if($tipo_suporte_inloco == "r"){ ?>
<div class="div_solicitacao_linhas3">
<table cellspacing="0" cellpadding="0" width="945">
	<tr>
        
    	<td style="text-align:left;" width="465">
		<span class="label_solicitacao"><label id="label_reclamacao_responsavel">Reclamante:<span id="req">*</span></label></span>
		<input name="reclamacao_responsavel" type="text" id="reclamacao_responsavel" style="width: 280px;" value="<? echo $row_suporte['reclamacao_responsavel']; ?>">
        </td>
        
    	<td style="text-align: right;">    
		<span class="label_solicitacao"><label id="label_reclamacao_telefone">Telefone de contato direto com o reclamante:<span id="req">*</span></label></span>
        <input name="reclamacao_telefone" type="text" id="reclamacao_telefone" style="width: 150px;" value="<? echo $row_suporte['reclamacao_telefone']; ?>" maxlength="13">
		</td>        
	</tr>
</table>
</div>
<? } ?>

<? if($tipo_suporte_inloco == "r"){ ?>
<div class="div_solicitacao_linhas2">
<table cellspacing="0" cellpadding="0" width="100%">
  <tr>
    <td style="text-align: left">
	<span class="label_solicitacao"><label id="label_reclamacao">Reclamação:<span id="req">*</span></label></span>
	<br>
	<textarea name="reclamacao" id="reclamacao" style="margin-top: 2px; width: 945px; height: 70px;"></textarea>
	</td>
  </tr>
</table>
</div>
<? } ?>

<? if($tipo_suporte_inloco == "r"){ ?>
<div class="div_solicitacao_linhas2">
<table cellspacing="0" cellpadding="0" width="100%">
  <tr>
    <td style="text-align: left">
	<span class="label_solicitacao"><label id="label_reclamacao_questionamento">Questionamento inicial da Success:<span id="req">*</span></label></span>
	<br>
	<textarea name="reclamacao_questionamento" id="reclamacao_questionamento" style="margin-top: 2px; width: 945px; height: 70px;"></textarea>
	</td>
  </tr>
</table>
</div>
<? } ?>

<? if($tipo_suporte_inloco == "r"){ ?>
<div class="div_solicitacao_linhas3">
<table cellspacing="0" cellpadding="0" width="945">
	<tr>

		<td style="text-align:left" width="400">
		<span class="label_solicitacao"><label id="label_reclamacao_percepcao">Percepção:<span id="req">*</span></label></span>
        <select name="reclamacao_percepcao" id="reclamacao_percepcao" style="width: 290px;">
        <option value="">Selecione ...</option>
        <option value="nervoso">nervoso</option>
        <option value="rude">rude</option>
        <option value="educacao">educado</option>
        <option value="calmo">calmo</option>
        </select>
		</td>
        
    	<td style="text-align:right">
		<span class="label_solicitacao"><label id="label_reclamacao_data_acordada">Data acordada para resposta:<span id="req">*</span></label></span>
		<input name="reclamacao_data_acordada" type="text" id="reclamacao_data_acordada" style="width: 150px;" value="<? echo $row_suporte['reclamacao_data_acordada']; ?>">
        </td>      
	</tr>
</table>
</div>
<? } ?>

<? if($tipo_suporte_inloco == "cs" or $tipo_suporte_inloco == "cn" or $tipo_suporte_inloco == "p"){ ?>
<div class="div_solicitacao_linhas3">
<table cellspacing="0" cellpadding="0" width="950">
	<tr>
		<td style="text-align:left" width="360">
        <span class="label_solicitacao"><label id="label_suporte_tipo_prioridade">Prioridade:<span id="req">*</span></label></span>
        <br>
        <select name="suporte_tipo_prioridade" id="suporte_tipo_prioridade">
        <option value="">Escolha ...</option>
        <?php
        do {  
        ?>
        <option value="<?php echo $row_suporte_tipo_prioridade['titulo']?>"><?php echo $row_suporte_tipo_prioridade['titulo']?></option>
        <?php
        } while ($row_suporte_tipo_prioridade = mysql_fetch_assoc($suporte_tipo_prioridade));
        $rows = mysql_num_rows($suporte_tipo_prioridade);
        if($rows > 0) {
        mysql_data_seek($suporte_tipo_prioridade, 0);
        $row_suporte_tipo_prioridade = mysql_fetch_assoc($suporte_tipo_prioridade);
        }
        ?>
        </select>
        </td>
        
		<td style="text-align: right">
		<span class="label_solicitacao"><label id="label_suporte_tipo_recomendacao">Recomendação:<span id="req">*</span></label></span>
        <br>
        <select name="suporte_tipo_recomendacao" id="suporte_tipo_recomendacao" style="width: 310px;">
        <option value="">Selecione ...</option>
        <?php
        do {  
        ?>
        <option value="<?php echo $row_suporte_tipo_recomendacao['titulo']?>"><?php echo $row_suporte_tipo_recomendacao['titulo']?></option>
        <?php
        } while ($row_suporte_tipo_recomendacao = mysql_fetch_assoc($suporte_tipo_recomendacao));
        $rows = mysql_num_rows($suporte_tipo_recomendacao);
        if($rows > 0) {
        mysql_data_seek($suporte_tipo_recomendacao, 0);
        $row_suporte_tipo_recomendacao = mysql_fetch_assoc($suporte_tipo_recomendacao);
        }
        ?>
        </select>
        </td>     
	</tr>
</table>
</div>
<? } ?>

<div class="div_solicitacao_linhas2">
<table cellspacing="0" cellpadding="0" width="100%">
  <tr>
    <td style="text-align: left">
	<span class="label_solicitacao"><label id="label_observacao">Observação:<span id="req">*</span></label></span>
	<br>
	<textarea name="observacao" id="observacao" style="margin-top: 2px; width: 945px; height: 100px;"><? echo $row_suporte['observacao']; ?></textarea>
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
    
    <?php if($row_suporte['solicita_visita'] <> "s"){ ?>
    <a href="suporte_gerar.php?id_suporte=<?php echo $row_suporte['id']; ?>&acao=excluir" class="botao_geral" style="width: 100px; height: 14px;" onClick="return confirmaExcluir();">Excluir</a>
    <? } ?>
    
    <?php if($tipo_suporte_inloco == "cs" and $row_suporte['solicita_visita'] == "s"){ ?>
    <a href="suporte_gerar.php?id_suporte=<?php echo $row_suporte['id']; ?>&acao=cancelar" class="botao_geral" style="width: 100px; height: 14px;" onClick="return confirmaCancelar();">Cancelar</a>
    <? } ?>
    
	<input type="hidden" name="MM_update" value="suporte" />
    <input name="id_suporte" type="hidden" value="<?php echo $row_suporte['id']; ?>" />
    
    <a href="suporte_vinculo.php?codigo_empresa=<? echo $row_suporte['codigo_empresa']; ?>&situacao=&acao=Suportes vinculados&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $suporte_editar_tabela_height; ?>&width=<? echo $suporte_editar_tabela_width; ?>&modal=true"  class="thickbox" id="botao_geral" style="width: 100px;">Suportes</a>
    
    <a href="suporte_reclamacao_vinculo.php?codigo_empresa=<? echo $row_suporte['codigo_empresa']; ?>&situacao=&acao=Suportes vinculados&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $suporte_editar_tabela_height; ?>&width=<? echo $suporte_editar_tabela_width; ?>&modal=true"  class="thickbox" id="botao_geral" style="width: 100px;">Reclamações</a>

    <a href="solicitacao_vinculo.php?codigo_empresa=<? echo $row_suporte['codigo_empresa']; ?>&situacao=&acao=Solicitações vinculadas&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $suporte_editar_tabela_height; ?>&width=<? echo $suporte_editar_tabela_width; ?>&modal=true"  class="thickbox" id="botao_geral" style="width: 100px;">Solicitações</a>

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
mysql_free_result($suporte);
mysql_free_result($manutencao_dados);
mysql_free_result($empresa_dados);
mysql_free_result($suporte_formulario_bonus_ultimo);
mysql_free_result($modcon);
	
if ($id_suporte != "-1") { // existe o suporte	
	mysql_free_result($suporte_responsavel);
	mysql_free_result($suporte_envolvido);
	mysql_free_result($geral_tipo_modulo);	
	mysql_free_result($suporte_tipo_atendimento);
	mysql_free_result($suporte_tipo_recomendacao);
	mysql_free_result($suporte_tipo_prioridade);
}
?>