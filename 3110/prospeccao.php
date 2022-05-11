<? session_start(); ?>
<?php require_once('restrito.php'); ?>
<?php require_once('Connections/conexao.php'); ?>
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

$currentPage = $_SERVER["PHP_SELF"];

require_once('parametros.php');
require_once('funcao_dia_util.php');

// usuário logado via SESSION - ok
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

// filtro praca - ok
mysql_select_db($database_conexao, $conexao);
$query_filtro_praca = "SELECT praca FROM geral_tipo_praca ORDER BY praca ASC";
$filtro_praca = mysql_query($query_filtro_praca, $conexao) or die(mysql_error());
$row_filtro_praca = mysql_fetch_assoc($filtro_praca);
$totalRows_filtro_praca = mysql_num_rows($filtro_praca);
// fim - filtro praca

mysql_select_db($database_conexao, $conexao);

$where = "1=1";
$where_agenda = "1=1";


// se padrão ---------------------------------------------------------------------------------------------------------------------------------------------------------
if ( isset($_GET['padrao']) && ($_GET['padrao'] == "sim") ){

	$where_agenda .= " and agenda.data <= '".date("Y-m-d")." 23:59:59' ";
	$_GET['prospeccao_agenda_data_fim'] = date("d-m-Y");

}	
// fim - se padrão ---------------------------------------------------------------------------------------------------------------------------------------------------


// controle_prospeccao =================================================================================================================
if($row_usuario['controle_prospeccao'] == "Y"){
	
	$where .= " and ( 
					 prospeccao.praca = '".$row_usuario['praca']."' or 
					 prospeccao.praca <> '".$row_usuario['praca']."' or 
					 prospeccao.id_usuario_responsavel = '".$row_usuario['IdUsuario']."'
					 ) ";
	
}
// fim - controle_prospeccao ===========================================================================================================

// nível 1 =============================================================================================================================
else if($row_usuario['nivel_prospeccao'] == 1){
	
	$where .= " and ( 
					 prospeccao.praca = '".$row_usuario['praca']."' or 
					 prospeccao.id_usuario_responsavel = '".$row_usuario['IdUsuario']."'
					 ) ";
	
}
// fim - nível 1 =======================================================================================================================

// nível 2 =============================================================================================================================
else if($row_usuario['nivel_prospeccao'] == 2){
	
	$where .= " and ( 
					 prospeccao.praca = '".$row_usuario['praca']."' or 
					 prospeccao.id_usuario_responsavel = '".$row_usuario['IdUsuario']."'
					 ) ";
	
}
// fim - nível 2 =======================================================================================================================

// nível 3 =============================================================================================================================
else if($row_usuario['nivel_prospeccao'] == 3){
	
	$where .= " and ( 
					 prospeccao.praca = '".$row_usuario['praca']."' or 
					 prospeccao.id_usuario_responsavel = '".$row_usuario['IdUsuario']."'
					 ) ";
	
}
// fim - nível 3 =======================================================================================================================

$where_usuario_logado = $where; // para o filtro por id (elimina todos os outros filtros)


// prospeccao - filtros ----------------------------------------------------------------------------------------------------------------------------------------------
// se existe filtro de nome_razao_social
if( (isset($_GET["nome_razao_social"])) && ($_GET['nome_razao_social'] !="") ) {
	$colname_prospeccao_nome_razao_social = GetSQLValueString($_GET["nome_razao_social"], "string");
	$where .= " and prospeccao.nome_razao_social LIKE '%$colname_prospeccao_nome_razao_social%' ";
	$where_campos[] = "nome_razao_social";
}
// fim - se existe filtro de nome_razao_social

// se existe filtro de praca
if( (isset($_GET["praca"])) && ($_GET['praca'] !="") ) {
	$colname_prospeccao_praca = GetSQLValueString($_GET["praca"], "string");
	$where .= " and prospeccao.praca = '$colname_prospeccao_praca' "; 	
	$where_campos[] = "praca";
} 
// fim - se existe filtro de praca

// se existe filtro de ativo_passivo
if( (isset($_GET["ativo_passivo"])) && ($_GET['ativo_passivo'] !="") ) {
	$colname_prospeccao_ativo_passivo = $_GET['ativo_passivo'];
	$where .= " and prospeccao.ativo_passivo = '".$colname_prospeccao_ativo_passivo."' ";
	$where_campos[] = "ativo_passivo";
} 
// fim - se existe filtro de ativo_passivo

// se existe filtro de usuario_responsavel
if( (isset($_GET["usuario_responsavel"])) && ($_GET['usuario_responsavel'] !="") ) {
	$colname_prospeccao_usuario_responsavel = $_GET['usuario_responsavel'];
	$where .= " and prospeccao.id_usuario_responsavel = '".$colname_prospeccao_usuario_responsavel."' ";
	$where_campos[] = "usuario_responsavel";
} 
// fim - se existe filtro de usuario_responsavel

// se existe filtro de id
if( (isset($_GET["id"])) && ($_GET['id'] !="") ) {
	$colname_prospeccao_id = GetSQLValueString($_GET["id"], "int");
	$where = $where_usuario_logado." and prospeccao.id = '$colname_prospeccao_id' ";
	$where_campos[] = "id";
}
// fim - se existe filtro de id

// se existe filtro de status
$contador_status = 0;
$contador_status_atual = 0;
if( (isset($_GET["status"])) && ($_GET['status'] !="") ) {

		// contar quantidade de situacões atual
		foreach($_GET["status"] as $status){
				$contador_status = $contador_status + 1;
		}
		// fim - contar quantidade de situacões atual

		$query_status=" and ( ";
		foreach($_GET["status"] as $status){
				$contador_status_atual = $contador_status_atual + 1; // verifica o contador atual
				$contador_total = $contador_status - $contador_status_atual; // calcula diferença de situações total - situação atual
				if($contador_total<>0){$or=" or ";}else{$or="";} // se não é a última, então insere OR
				$query_status .= sprintf(" prospeccao.status = '$status' $or");

		}
		$where .= sprintf($query_status)." ) ";
		$where_campos[] = "status";		
}
// fim - se existe filtro de status

// se existe filtro de situacao
$contador_situacao = 0;
$contador_situacao_atual = 0;
if( (isset($_GET["situacao"])) && ($_GET['situacao'] !="") ) {

		// contar quantidade de situacões atual
		foreach($_GET["situacao"] as $situacao){
				$contador_situacao = $contador_situacao + 1;
		}
		// fim - contar quantidade de situacões atual

		$query_situacao=" and ( ";
		foreach($_GET["situacao"] as $situacao){
				$contador_situacao_atual = $contador_situacao_atual + 1; // verifica o contador atual
				$contador_total = $contador_situacao - $contador_situacao_atual; // calcula diferença de situações total - situação atual
				if($contador_total<>0){$or=" or ";}else{$or="";} // se não é a última, então insere OR
				$query_situacao .= sprintf(" prospeccao.situacao = '$situacao' $or");

		}
		$where .= sprintf($query_situacao)." ) ";
		$where_campos[] = "situacao";		
}
// fim - se existe filtro de situacao

// se existe filtro de data_prospeccao ( somente data final )
if( ((isset($_GET["data_prospeccao_fim"])) && ($_GET["data_prospeccao_fim"] != "")) && ($_GET["data_prospeccao_inicio"] == "") ) {

		// converter data em portugues para ingles
		if ( isset($_GET["data_prospeccao_fim"]) ) {
			$data_prospeccao_fim_data = substr($_GET["data_prospeccao_fim"],0,10);
			$data_prospeccao_fim_hora = " 23:59:59";
			$data_prospeccao_fim = implode("-",array_reverse(explode("-",$data_prospeccao_fim_data))).$data_prospeccao_fim_hora;
			$where_campos[] = "data_prospeccao_fim";
		}
		// converter data em portugues para ingles - fim
	
		$colname_prospeccao_data_prospeccao_fim = GetSQLValueString($data_prospeccao_fim, "string");
		$where .= " and prospeccao.data_prospeccao <= '".$colname_prospeccao_data_prospeccao_fim."' ";
}
// fim - se existe filtro de data_prospeccao ( somente data final )

// se existe filtro de data_prospeccao ( somente data inicial )
if( ((isset($_GET["data_prospeccao_inicio"])) && ($_GET["data_prospeccao_inicio"] != "")) && ($_GET["data_prospeccao_fim"] == "") ) {

		// converter data em portugues para ingles
		if ( isset($_GET["data_prospeccao_inicio"]) ) {
			$data_prospeccao_inicio_data = substr($_GET["data_prospeccao_inicio"],0,10);
			$data_prospeccao_inicio_hora = " 00:00:00";
			$data_prospeccao_inicio = implode("-",array_reverse(explode("-",$data_prospeccao_inicio_data))).$data_prospeccao_inicio_hora;
			$where_campos[] = "data_prospeccao_inicio";
		}
		// converter data em portugues para ingles - fim
	
		$colname_prospeccao_data_prospeccao_inicio = GetSQLValueString($data_prospeccao_inicio, "string");
		$where .= " and prospeccao.data_prospeccao >= '".$colname_prospeccao_data_prospeccao_inicio."' ";
}
// fim - se existe filtro de data_prospeccao ( somente data inicial )

// se existe filtro de data_prospeccao ( entre data inicial e data final )
if( ((isset($_GET["data_prospeccao_inicio"])) && ($_GET["data_prospeccao_inicio"] != "")) && ((isset($_GET["data_prospeccao_fim"])) && ($_GET["data_prospeccao_fim"] != "")) ) {

		// converter data em portugues para ingles
		if ( isset($_GET["data_prospeccao_inicio"]) ) {
			$data_prospeccao_inicio_data = substr($_GET["data_prospeccao_inicio"],0,10);
			$data_prospeccao_inicio_hora = " 00:00:00";
			$data_prospeccao_inicio = implode("-",array_reverse(explode("-",$data_prospeccao_inicio_data))).$data_prospeccao_inicio_hora;
			$where_campos[] = "data_prospeccao_inicio";
		}
		// converter data em portugues para ingles - fim

		// converter data em portugues para ingles
		if ( isset($_GET["data_prospeccao_fim"]) ) {
			$data_prospeccao_fim_data = substr($_GET["data_prospeccao_fim"],0,10);
			$data_prospeccao_fim_hora = " 23:59:59";
			$data_prospeccao_fim = implode("-",array_reverse(explode("-",$data_prospeccao_fim_data))).$data_prospeccao_fim_hora;
			$where_campos[] = "data_prospeccao_fim";
		}
		// converter data em portugues para ingles - fim
	
		$colname_prospeccao_data_prospeccao_inicio = GetSQLValueString($data_prospeccao_inicio, "string");
		$colname_prospeccao_data_prospeccao_fim = GetSQLValueString($data_prospeccao_fim, "string");

		$where .= " and prospeccao.data_prospeccao between '$colname_prospeccao_data_prospeccao_inicio' and '$colname_prospeccao_data_prospeccao_fim' "; 
}
// fim - se existe filtro de data_prospeccao ( entre data inicial e data final )

// se existe filtro de id_contador
if( (isset($_GET["id_contador"])) && ($_GET['id_contador'] !="") ) {
	$colname_prospeccao_id_contador = $_GET['id_contador'];
	$where .= " and prospeccao.id_contador = '".$colname_prospeccao_id_contador."' ";
	$where_campos[] = "id_contador";
} 
// fim - se existe filtro de id_contador

// se existe filtro de cidade
if( (isset($_GET["cidade"])) && ($_GET['cidade'] !="") ) {
	$colname_prospeccao_cidade = GetSQLValueString($_GET["cidade"], "string");
	$where .= " and prospeccao.cidade LIKE '%$colname_prospeccao_cidade%' ";
	$where_campos[] = "cidade";
}
// fim - se existe filtro de cidade

// se existe filtro de indicador
if( (isset($_GET["indicador"])) && ($_GET['indicador'] !="") ) {
	$colname_prospeccao_indicador = GetSQLValueString($_GET["indicador"], "string");
	$where .= " and prospeccao.indicador = '$colname_prospeccao_indicador' ";
	$where_campos[] = "indicador";
}
// fim - se existe filtro de indicador

// se existe filtro de ramo_de_atividade
if( (isset($_GET["ramo_de_atividade"])) && ($_GET['ramo_de_atividade'] !="") ) {
	$colname_prospeccao_ramo_de_atividade = GetSQLValueString($_GET["ramo_de_atividade"], "string");
	$where .= " and prospeccao.ramo_de_atividade = '$colname_prospeccao_ramo_de_atividade' ";
	$where_campos[] = "ramo_de_atividade";
}
// fim - se existe filtro de ramo_de_atividade

// se existe filtro de sistema_possui
if( (isset($_GET["sistema_possui"])) && ($_GET['sistema_possui'] !="") ) {
	$colname_prospeccao_sistema_possui = GetSQLValueString($_GET["sistema_possui"], "string");
	$where .= " and prospeccao.sistema_possui = '$colname_prospeccao_sistema_possui' ";
	$where_campos[] = "sistema_possui";
}
// fim - se existe filtro de sistema_possui

// se existe filtro de migracao
if( (isset($_GET["migracao"])) && ($_GET['migracao'] !="") ) {
	$colname_prospeccao_migracao = GetSQLValueString($_GET["migracao"], "string");
	$where .= " and prospeccao_concorrente.migracao = '$colname_prospeccao_migracao' ";
	$where_campos[] = "migracao";
}
// fim - se existe filtro de migracao

// se existe filtro de nivel_interesse
if( (isset($_GET["nivel_interesse"])) && ($_GET['nivel_interesse'] !="") ) {
	$colname_prospeccao_nivel_interesse = GetSQLValueString($_GET["nivel_interesse"], "string");
	$where .= " and prospeccao.nivel_interesse = '$colname_prospeccao_nivel_interesse' ";
	$where_campos[] = "nivel_interesse";
}
// fim - se existe filtro de nivel_interesse

// se existe filtro de proposta_validade
if( (isset($_GET["proposta_validade"])) && ($_GET['proposta_validade'] !="") ) {
	$colname_prospeccao_proposta_validade = GetSQLValueString($_GET["proposta_validade"], "string");
	$where .= " and prospeccao.proposta_validade = '$colname_prospeccao_proposta_validade' ";
	$where_campos[] = "proposta_validade";
}
// fim - se existe filtro de proposta_validade

// se existe filtro de baixa_perda_motivo
if( (isset($_GET["baixa_perda_motivo"])) && ($_GET['baixa_perda_motivo'] !="") ) {
	$colname_prospeccao_baixa_perda_motivo = GetSQLValueString($_GET["baixa_perda_motivo"], "string");
	$where .= " and prospeccao.baixa_perda_motivo = '$colname_prospeccao_baixa_perda_motivo' ";
	$where_campos[] = "baixa_perda_motivo";
}
// fim - se existe filtro de baixa_perda_motivo
// fim - prospeccao - filtros ----------------------------------------------------------------------------------------------------------------------------------------

// prospeccao
$query_prospeccao = "
SELECT 
prospeccao.id, prospeccao.status, prospeccao.situacao, prospeccao.data_prospeccao, prospeccao.id_usuario_responsavel, prospeccao.praca, prospeccao.id_contador, prospeccao.sistema_possui, prospeccao.nivel_interesse, 
prospeccao.ativo_passivo, prospeccao.uf, prospeccao.cidade, prospeccao.nome_razao_social, prospeccao.quantidade_agendado, prospeccao.status_flag, 
(SELECT nome FROM usuarios WHERE usuarios.IdUsuario = prospeccao.id_usuario_responsavel) as usuario_responsavel, 
(SELECT razao FROM dbcts9 WHERE dbcts9.codigo = prospeccao.id_contador) as contador_razao 

FROM prospeccao 
LEFT JOIN prospeccao_concorrente ON prospeccao.id_concorrente = prospeccao_concorrente.id 
WHERE $where
ORDER BY prospeccao.praca ASC, prospeccao.quantidade_agendado ASC, prospeccao.id ASC";

$prospeccao = mysql_query($query_prospeccao, $conexao) or die(mysql_error());
$row_prospeccao = mysql_fetch_assoc($prospeccao);
$totalRows_prospeccao = mysql_num_rows($prospeccao);
// fim - prospeccao

// prospeccao_agenda - filtros ---------------------------------------------------------------------------------------------------------------------------------------
// se existe filtro de prospeccao_agenda_status
$contador_prospeccao_agenda_status = 0;
$contador_prospeccao_agenda_status_atual = 0;
if( (isset($_GET["prospeccao_agenda_status"])) && ($_GET['prospeccao_agenda_status'] !="") ) {

		// contar quantidade de situacões atual
		foreach($_GET["prospeccao_agenda_status"] as $prospeccao_agenda_status){
				$contador_prospeccao_agenda_status = $contador_prospeccao_agenda_status + 1;
		}
		// fim - contar quantidade de situacões atual

		$query_prospeccao_agenda_status=" and ( ";
		foreach($_GET["prospeccao_agenda_status"] as $prospeccao_agenda_status){
			
				$contador_prospeccao_agenda_status_atual = $contador_prospeccao_agenda_status_atual + 1; // verifica o contador atual
				$contador_total = $contador_prospeccao_agenda_status - $contador_prospeccao_agenda_status_atual; // calcula diferença de situações total - situação atual
				if($contador_total<>0){$or=" or ";}else{$or="";} // se não é a última, então insere OR
				$query_prospeccao_agenda_status .= sprintf(" agenda.status = '$prospeccao_agenda_status' $or");

		}
		$where_agenda .= sprintf($query_prospeccao_agenda_status)." ) ";
		
} else {

	$where .= " and prospeccao.quantidade_agendado = 0";
	$where_agenda .= " and agenda.id_agenda IS NULL";

}
// fim - se existe filtro de prospeccao_agenda_status

// se existe filtro de prospeccao_agenda_data ( somente data final )
if( ((isset($_GET["prospeccao_agenda_data_fim"])) && ($_GET["prospeccao_agenda_data_fim"] != "")) && (@$_GET["prospeccao_agenda_data_inicio"] == "") ) {

		// converter data em portugues para ingles
		if ( isset($_GET["prospeccao_agenda_data_fim"]) ) {
			$prospeccao_agenda_data_fim_data = substr($_GET["prospeccao_agenda_data_fim"],0,10);
			$prospeccao_agenda_data_fim_hora = " 23:59:59";
			$prospeccao_agenda_data_fim = implode("-",array_reverse(explode("-",$prospeccao_agenda_data_fim_data))).$prospeccao_agenda_data_fim_hora;
			$where_agenda_campos[] = "prospeccao_agenda_data_fim";
		}
		// converter data em portugues para ingles - fim
	
		$colname_prospeccao_prospeccao_agenda_data_fim = GetSQLValueString($prospeccao_agenda_data_fim, "string");
		$where_agenda .= " and agenda.data <= '".$colname_prospeccao_prospeccao_agenda_data_fim."' ";
}
// fim - se existe filtro de prospeccao_agenda_data ( somente data final )

// se existe filtro de prospeccao_agenda_data ( somente data inicial )
if( ((isset($_GET["prospeccao_agenda_data_inicio"])) && ($_GET["prospeccao_agenda_data_inicio"] != "")) && (@$_GET["prospeccao_agenda_data_fim"] == "") ) {

		// converter data em portugues para ingles
		if ( isset($_GET["prospeccao_agenda_data_inicio"]) ) {
			$prospeccao_agenda_data_inicio_data = substr($_GET["prospeccao_agenda_data_inicio"],0,10);
			$prospeccao_agenda_data_inicio_hora = " 00:00:00";
			$prospeccao_agenda_data_inicio = implode("-",array_reverse(explode("-",$prospeccao_agenda_data_inicio_data))).$prospeccao_agenda_data_inicio_hora;
			$where_agenda_campos[] = "prospeccao_agenda_data_inicio";
		}
		// converter data em portugues para ingles - fim
	
		$colname_prospeccao_prospeccao_agenda_data_inicio = GetSQLValueString($prospeccao_agenda_data_inicio, "string");
		
		$where_agenda .= " and agenda.data >= '".$colname_prospeccao_prospeccao_agenda_data_inicio."' ";
}
// fim - se existe filtro de prospeccao_agenda_data ( somente data inicial )

// se existe filtro de prospeccao_agenda_data ( entre data inicial e data final )
if( ((isset($_GET["prospeccao_agenda_data_inicio"])) && ($_GET["prospeccao_agenda_data_inicio"] != "")) && ((isset($_GET["prospeccao_agenda_data_fim"])) && ($_GET["prospeccao_agenda_data_fim"] != "")) ) {

		// converter data em portugues para ingles
		if ( isset($_GET["prospeccao_agenda_data_inicio"]) ) {
			$prospeccao_agenda_data_inicio_data = substr($_GET["prospeccao_agenda_data_inicio"],0,10);
			$prospeccao_agenda_data_inicio_hora = " 00:00:00";
			$prospeccao_agenda_data_inicio = implode("-",array_reverse(explode("-",$prospeccao_agenda_data_inicio_data))).$prospeccao_agenda_data_inicio_hora;
			$where_agenda_campos[] = "prospeccao_agenda_data_inicio";
		}
		// converter data em portugues para ingles - fim

		// converter data em portugues para ingles
		if ( isset($_GET["prospeccao_agenda_data_fim"]) ) {
			$prospeccao_agenda_data_fim_data = substr($_GET["prospeccao_agenda_data_fim"],0,10);
			$prospeccao_agenda_data_fim_hora = " 23:59:59";
			$prospeccao_agenda_data_fim = implode("-",array_reverse(explode("-",$prospeccao_agenda_data_fim_data))).$prospeccao_agenda_data_fim_hora;
			$where_agenda_campos[] = "prospeccao_agenda_data_fim";
		}
		// converter data em portugues para ingles - fim
	
		$colname_prospeccao_prospeccao_agenda_data_inicio = GetSQLValueString($prospeccao_agenda_data_inicio, "string");
		$colname_prospeccao_prospeccao_agenda_data_fim = GetSQLValueString($prospeccao_agenda_data_fim, "string");

		$where_agenda .= " and agenda.data between '$colname_prospeccao_prospeccao_agenda_data_inicio' and '$colname_prospeccao_prospeccao_agenda_data_fim' "; 
}
// fim - se existe filtro de prospeccao_agenda_data ( entre data inicial e data final )

// se existe prospeccao_agenda_atraso
if( ((isset($_GET["prospeccao_agenda_atraso"])) && ($_GET["prospeccao_agenda_atraso"] != "")) && (@$_GET["prospeccao_agenda_atraso"] == "s") ) {
	
		$colname_prospeccao_prospeccao_agenda_atraso = GetSQLValueString(date('Y-m-d H:i:s'), "date");		
		$where_agenda .= " and agenda.data < ".$colname_prospeccao_prospeccao_agenda_atraso." ";

}
// fim - se existe prospeccao_agenda_atraso

// fim - prospeccao_agenda - filtros ---------------------------------------------------------------------------------------------------------------------------------

// prospeccao_agenda
$query_prospeccao_agenda = "
SELECT 
prospeccao.id, prospeccao.status, prospeccao.situacao, prospeccao.data_prospeccao, prospeccao.id_usuario_responsavel, prospeccao.praca, 
(SELECT nome FROM usuarios WHERE usuarios.IdUsuario = prospeccao.id_usuario_responsavel) as usuario_responsavel, 
prospeccao.ativo_passivo, prospeccao.uf, prospeccao.cidade, prospeccao.nome_razao_social, prospeccao.quantidade_agendado, prospeccao.status_flag, 

agenda.id_agenda AS prospeccao_agenda_id_agenda, 
agenda.data AS prospeccao_agenda_data, 
agenda.data_inicio AS prospeccao_agenda_data_inicio, 
agenda.descricao AS prospeccao_agenda_descricao, 
agenda.status AS prospeccao_agenda_status

FROM agenda
LEFT JOIN prospeccao ON agenda.id_prospeccao = prospeccao.id 
LEFT JOIN prospeccao_concorrente ON prospeccao.id_concorrente = prospeccao_concorrente.id 
WHERE $where and agenda.id_prospeccao IS NOT NULL and $where_agenda
ORDER BY prospeccao.praca ASC, agenda.data ASC";

$prospeccao_agenda = mysql_query($query_prospeccao_agenda, $conexao) or die(mysql_error());
$row_prospeccao_agenda = mysql_fetch_assoc($prospeccao_agenda);
$totalRows_prospeccao_agenda = mysql_num_rows($prospeccao_agenda);
// fim - prospeccao_agenda

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"> 
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link href="css/suporte.css" rel="stylesheet" type="text/css">
<!--[if !IE]> -->
<style>
body{
	overflow-y: scroll; /* se não é IE, então mostra a scroll vertical */
}
</style>
<!-- <![endif]-->

<style>
label.error {
	color: red; display: none; /* erro de validação */
}

#empresa_dados {
	padding: 5px;
	font-weight:normal;
	font-family: Lucida Grande, Lucida Sans, Arial, sans-serif; 
	font-size: 12px;
}
.cliente_css {
	border: 1px solid #CCC;
	margin: 0px;
	padding: 5px;
}
.cliente_buscar_css{
	border: 1px solid #CCC;
	margin-top: 0px;
	margin-left: 0px;
	margin-right: 0px;
	margin-bottom: 5px;
	padding: 5px;
}
.cor_black {
	padding: 1px;
}
.cor_orange {
	color: #FF9900; !important;
	font-weight:bold;
	padding: 1px; !important;
}
.cor_red {
	color: #FF0000; !important;
	font-weight:bold;
	padding: 1px; !important;
}
.cor_blue {
	color: blue; !important;
	font-weight:bold;
	padding: 1px; !important;
}
.cor_green {
	color: green; !important;
	font-weight:bold;
	padding: 1px; !important;
}

.ui-jqgrid .ui-jqgrid-btable
{
  table-layout:auto;
} 
</style>

<script type="text/javascript" src="js/jquery.js"></script>

<script type="text/javascript" src="js/jquery.metadata.js" ></script>
<script type="text/javascript" src="js/jquery.validate.js"></script>

<script type="text/javascript" src="js/jquery.maskedinput-1.2.2.min.js"></script> 
<script type="text/javascript" src="js/jquery.rsv.js"></script> 

<link rel="stylesheet" href="css/thickbox.css" type="text/css" media="screen" />
<script type="text/javascript" src="js/thickbox.js"></script>

<link rel="stylesheet" type="text/css" media="screen" href="css/jquery-ui.css" />
<link rel="stylesheet" type="text/css" media="screen" href="css/ui.jqgrid.css" />
<script src="js/grid.locale-pt-br.js" type="text/javascript"></script>
<script src="js/jquery.jqGrid.src.js" type="text/javascript"></script>

<script type="text/javascript">
$.jgrid.no_legacy_api = true;
$.metadata.setType("attr", "validate");

$(document).ready(function(){
//$(document).ready(function() {
	
	// mascara
	$('#data_prospeccao_inicio').mask('99-99-9999',{placeholder:" "});
	$('#data_prospeccao_fim').mask('99-99-9999',{placeholder:" "});
	
	$('#prospeccao_agenda_data_inicio').mask('99-99-9999',{placeholder:" "});
	$('#prospeccao_agenda_data_fim').mask('99-99-9999',{placeholder:" "});
	// mascara - fim
	
	// ocultar/exibir filtros
	$('#corpo_prospeccao_filtro').toggle();
	$('#cabecalho_prospeccao_filtro').click(function() {
		$('#corpo_prospeccao_filtro').toggle();
	});
	// fim - ocultar/exibir fitlros
		
	// ocultar/exibir prospeccao
	//$('#corpo_prospeccao').toggle();
	$('#cabecalho_prospeccao').click(function() {
		$('#corpo_prospeccao').toggle();
	});
	// fim - ocultar/exibir prospeccao
	
	// ocultar/exibir prospeccao_agenda
	$('#corpo_prospeccao_agenda').toggle();
	$('#cabecalho_prospeccao_agenda').click(function() {
		$('#corpo_prospeccao_agenda').toggle();
	});
	// fim - ocultar/exibir prospeccao_agenda
	
	// marcar todos
	$('#checkall_situacao').click(function () {
	$(this).parents('fieldset:eq(0)').find(':checkbox').attr('checked', this.checked);
	});
	
	$('#checkall_status').click(function () {
		$(this).parents('fieldset:eq(0)').find(':checkbox').attr('checked', this.checked);
	});
	
	$('#checkall_necessidades').click(function () {
	$(this).parents('fieldset:eq(0)').find(':checkbox').attr('checked', this.checked);
	});
	// fim - marcar todos
	
});

// limpar formulário do filtro
function clear_form_elements(ele) {

    $(ele).find(':input').each(function() {
        switch(this.type) {
            case 'password':
            case 'select-multiple':
            case 'select-one':
            case 'text':
            case 'textarea':
                $(this).val('');
                break;
            case 'checkbox':
            case 'radio':
                this.checked = false;
        }
    });

}
// fim - limpar formulário do filtro
</script>
<title>Prospecção</title>
</head>

<body>
<? // echo $where; echo "<br><br><br>"; echo $where_agenda; ?>
<div class="div_solicitacao_linhas">
<table cellspacing="0" cellpadding="0" width="100%">
	<tr>
		<td style="text-align:left">
		Controle de prospecção
        <font color="#3399CC"> | </font>
        <a href="venda.php?padrao=sim&<? echo $venda_padrao; ?>" style="color: #D1E3F1">Controle de vendas</a> 
        <font color="#3399CC"> | </font>
        <a href="solicitacao.php?padrao=sim&<? echo $situacao_padrao; ?>" style="color: #D1E3F1">Controle de solicitação</a>
		<font color="#3399CC"> | </font>
        <a href="suporte.php?padrao=sim&<? echo $suporte_padrao; ?>" style="color: #D1E3F1">Controle de suporte</a>

		</td>

		<td style="text-align: right">
		&lt;&lt; <a href="index.php">Voltar</a> | 
		Usuário logado: <? echo $row_usuario['nome']; ?> (<? echo $row_usuario['nivel_prospeccao']; ?>) |
		<a href="painel/padrao_sair.php">Sair</a>
        </td>
	</tr>
</table>
</div>

<!-- barra superior -->
<div class="div_solicitacao_linhas4" style="margin-top: 5px;">
<table cellspacing="0" cellpadding="0" width="100%">
	<tr>
		<td style="text-align:left">
            
            <form action="prospeccao_gerar.php" enctype="multipart/form-data" name="prospeccao_gerar" id="prospeccao_gerar">
            <table width="100%" border="0" cellspacing="0" cellpadding="0">
            <tr>
                <td width="180">
                <input type="submit" name="button" id="button" value="Gerar nova prospecção" class="botao_geral2" style="width: 170px">
                </td>
                
                <td>
                <span class="label_solicitacao"><label id="label_novo_antigo">Tipo do cliente:*</label></span>
                <input type="radio" name="novo_antigo" id="novo_antigo" value="n" checked="checked"> Novo 
                <input type="radio" name="novo_antigo" id="novo_antigo" value="a"> Antigo 
                </td>
            </tr>
            </table>
			</form>
	  </td>
	</tr>
</table>
</div>
<!-- fim - barra superior -->

<div class="div_solicitacao_linhas2">
Clique sobre a opção desejada para visualizar mais informações.
</div>

<!-- filtros -->
<div class="div_solicitacao_linhas" id="cabecalho_prospeccao_filtro" style="cursor: pointer">
<table cellspacing="0" cellpadding="0" width="100%">
	<tr>
		<td style="text-align:left">
		Filtros
		</td>
        
		<td style="text-align: right">
		<div style="float: right; background-image:url(imagens/icone_filtro.png); width: 12px; height: 12px;"></div>
		</td>
	</tr>
</table>
</div>

<form name="buscar" action="prospeccao.php" method="GET">
<div id="corpo_prospeccao_filtro">


	<!-- filtros da prospeccao -->
	<div style="border: 1px solid #c5dbec; margin-bottom: 5px;">
    
        <div class="div_filtros2">
        <table cellspacing="0" cellpadding="0" width="100%">
            <tr>
                <td style="text-align:left" width="560px" >
                <span class="label_solicitacao">Cliente:</span>
                <input name="nome_razao_social" type="text" id="nome_razao_social" value="<? if ( isset($_GET['nome_razao_social']) ) { echo $_GET['nome_razao_social']; } ?>" style="width: 500px" /> 
                </td>

                <td style="text-align: right">
                <span class="label_solicitacao">Praça: </span>
                <select name="praca">
                <option value=""
                <?php if (!(strcmp("", isset($_GET['praca'])))) {echo "selected=\"selected\"";} ?>
                >
                Escolha ...
                </option>
                <?php do {  ?>
                <option value="<?php echo $row_filtro_praca['praca']?>"
                <?php if ( (isset($_GET['praca'])) and (!(strcmp($row_filtro_praca['praca'], $_GET['praca']))) ) {echo "selected=\"selected\"";} ?>
                >
                <?php echo $row_filtro_praca['praca']?>
                </option>
                <?php
                } while ($row_filtro_praca = mysql_fetch_assoc($filtro_praca));
                $rows = mysql_num_rows($filtro_praca);
                if($rows > 0) {
                mysql_data_seek($filtro_praca, 0);
                $row_filtro_praca = mysql_fetch_assoc($filtro_praca);
                }
                ?>
                </select>
				</td>
        
              	<td style="text-align:right" width="250px">
                <span class="label_solicitacao">Tipo de prospecção: </span>
                <select name="ativo_passivo">
                <option value=""<?php if (!(strcmp("", isset($_GET['ativo_passivo'])))) {echo "selected=\"selected\"";} ?>>Escolha ...</option>
                <option value="a"<?php if ( (isset($_GET['ativo_passivo'])) and (!(strcmp("a", $_GET['ativo_passivo']))) ) {echo "selected=\"selected\"";} ?>>Ativo</option>
                <option value="p"<?php if ( (isset($_GET['ativo_passivo'])) and (!(strcmp("p", $_GET['ativo_passivo']))) ) {echo "selected=\"selected\"";} ?>>Passivo</option>
                </select>
                </td>
            </tr>
        </table>
        </div>

        <div class="div_filtros">
		<?
		// filtro usuario_responsavel
		mysql_select_db($database_conexao, $conexao);
		$query_filtro_usuario_responsavel = "SELECT IdUsuario, nome FROM usuarios ORDER BY nome ASC";
		$filtro_usuario_responsavel = mysql_query($query_filtro_usuario_responsavel, $conexao) or die(mysql_error());
		$row_filtro_usuario_responsavel = mysql_fetch_assoc($filtro_usuario_responsavel);
		$totalRows_filtro_usuario_responsavel = mysql_num_rows($filtro_usuario_responsavel);	
		// fim - filtro usuario_responsavel
		?>
        <table cellspacing="0" cellpadding="0" width="100%">
            <tr>
                <td style="text-align:left" width="560px" >
                <span class="label_solicitacao">Solicitante: </span>
				<input name="solicitante" type="text" id="solicitante" value="<? if ( isset($_GET['solicitante']) ) { echo $_GET['solicitante']; } ?>" style="width: 470px" /> 
                </td>
                
              	<td style="text-align:right">
                <span class="label_solicitacao">Responsável: </span>
                <select name="usuario_responsavel" style="width: 380px">
                <option value=""
                <?php if (!(strcmp("", isset($_GET['filtro_usuario_responsavel'])))) {echo "selected=\"selected\"";} ?>>Escolha ...</option>
                <?php do {  ?>
                <option value="<?php echo $row_filtro_usuario_responsavel['IdUsuario']; ?>"
                <?php if ( (isset($_GET['usuario_responsavel'])) and (!(strcmp($row_filtro_usuario_responsavel['IdUsuario'], $_GET['usuario_responsavel']))) ) {echo "selected=\"selected\"";} ?>
                >
                <?php echo utf8_encode($row_filtro_usuario_responsavel['nome']); ?>
                </option>
                <?php
                } while ($row_filtro_usuario_responsavel = mysql_fetch_assoc($filtro_usuario_responsavel));
                $rows = mysql_num_rows($filtro_usuario_responsavel);
                if($rows > 0) {
                mysql_data_seek($filtro_usuario_responsavel, 0);
                $row_filtro_usuario_responsavel = mysql_fetch_assoc($filtro_usuario_responsavel);
                }
                ?>
                </select>
                </td>
          </tr>
        </table>
		<? mysql_free_result($filtro_usuario_responsavel); ?>
        </div>
                
        <div class="div_filtros2">
        <table cellspacing="0" cellpadding="0" width="100%">
        <tr>
        <td style="text-align:left">
        <span class="label_solicitacao">Data criação (inicial): </span>
        <input name="data_prospeccao_inicio" id="data_prospeccao_inicio" type="text" value="<? 
        if ( isset($_GET['data_prospeccao_inicio']) ){ echo $_GET['data_prospeccao_inicio']; }
        ?>" />
        </td>
        
        <td style="text-align:right" class="div_filtros_prospeccao_corpo_td">
        <span class="label_solicitacao">Data criação (final): </span>
        <input name="data_prospeccao_fim" id="data_prospeccao_fim" type="text" value="<? 
        if ( isset($_GET['data_prospeccao_fim']) ){ echo $_GET['data_prospeccao_fim']; }
        ?>" />
        </td>
        </tr>
        </table>
        </div>
        
        <div class="div_filtros">
        <table cellspacing="0" cellpadding="0" width="100%">
            <tr>
                <td style="text-align:left">
                <fieldset style="border: 0px;">
                <span class="label_solicitacao">Situação:</span>
                
                <input name="situacao[]" type="checkbox" value="analisada" 
                    <?
                    // verificar se foi selecionada
                    if(isset($_GET['situacao'])){
                        foreach($_GET["situacao"] as $situacao){
                            if($situacao=="analisada"){
                                echo "checked=\"checked\"";
                            }
                        }
                    }
                    // verificar se foi selecionada
                    ?>
                />analisada
    
                <input name="situacao[]" type="checkbox" value="em negociação" 
                    <?
                    // verificar se foi selecionada
                    if(isset($_GET['situacao'])){
                        foreach($_GET["situacao"] as $situacao){
                            if($situacao=="em negociação"){
                                echo "checked=\"checked\"";
                            }
                        }
                    }
                    // verificar se foi selecionada
                    ?>
                />em negociação
                
                <input name="situacao[]" type="checkbox" value="solicitado agendamento" 
                    <?
                    // verificar se foi selecionada
                    if(isset($_GET['situacao'])){
                        foreach($_GET["situacao"] as $situacao){
                            if($situacao=="solicitado agendamento"){
                                echo "checked=\"checked\"";
                            }
                        }
                    }
                    // verificar se foi selecionada
                    ?>
                />solicitado agendamento
                                
                <input name="situacao[]" type="checkbox" value="venda realizada" 
                    <?
                    // verificar se foi selecionada
                    if(isset($_GET['situacao'])){
                        foreach($_GET["situacao"] as $situacao){
                            if($situacao=="venda realizada"){
                                echo "checked=\"checked\"";
                            }
                        }
                    }
                    // verificar se foi selecionada
                    ?>
                />venda realizada
                
                <input name="situacao[]" type="checkbox" value="venda perdida" 
                    <?
                    // verificar se foi selecionada
                    if(isset($_GET['situacao'])){
                        foreach($_GET["situacao"] as $situacao){
                            if($situacao=="venda perdida"){
                                echo "checked=\"checked\"";
                            }
                        }
                    }
                    // verificar se foi selecionada
                    ?>
                />venda perdida
                
                <input name="situacao[]" type="checkbox" value="cancelada" 
                    <?
                    // verificar se foi selecionada
                    if(isset($_GET['situacao'])){
                        foreach($_GET["situacao"] as $situacao){
                            if($situacao=="cancelada"){
                                echo "checked=\"checked\"";
                            }
                        }
                    }
                    // verificar se foi selecionada
                    ?>
                />cancelada
                
                <input type="checkbox" id="checkall_situacao"  name="checkall_situacao" />Marcar todos
                </fieldset>
                </td>
            </tr>
        </table>
        </div>

        <div class="div_filtros">
        <table cellspacing="0" cellpadding="0" width="100%">
            <tr>
				
                <td style="text-align:left" width="560px">&nbsp;</td>
				
				<td style="text-align:right">
				<?
				// filtro_ramo_de_atividade
				mysql_select_db($database_conexao, $conexao);
				$query_filtro_ramo_de_atividade = "SELECT titulo FROM geral_tipo_ramo_atividade ORDER BY titulo ASC";
				$filtro_ramo_de_atividade = mysql_query($query_filtro_ramo_de_atividade, $conexao) or die(mysql_error());
				$row_filtro_ramo_de_atividade = mysql_fetch_assoc($filtro_ramo_de_atividade);
				$totalRows_filtro_ramo_de_atividade = mysql_num_rows($filtro_ramo_de_atividade);	
				// fim - filtro_ramo_de_atividade
				?>
                <span class="label_solicitacao">Ramo de atividade: </span>
                <select name="ramo_de_atividade" style="width: 200px">
                <option value=""
                <?php if (!(strcmp("", isset($_GET['filtro_ramo_de_atividade'])))) {echo "selected=\"selected\"";} ?>>Escolha ...</option>
                <?php do {  ?>
                <option value="<?php echo $row_filtro_ramo_de_atividade['titulo']; ?>"
                <?php if ( (isset($_GET['ramo_de_atividade'])) and (!(strcmp($row_filtro_ramo_de_atividade['titulo'], $_GET['ramo_de_atividade']))) ) {echo "selected=\"selected\"";} ?>
                >
                <?php echo $row_filtro_ramo_de_atividade['titulo']; ?>
                </option>
                <?php
                } while ($row_filtro_ramo_de_atividade = mysql_fetch_assoc($filtro_ramo_de_atividade));
                $rows = mysql_num_rows($filtro_ramo_de_atividade);
                if($rows > 0) {
                mysql_data_seek($filtro_ramo_de_atividade, 0);
                $row_filtro_ramo_de_atividade = mysql_fetch_assoc($filtro_ramo_de_atividade);
                }
                ?>
                </select>
				<? mysql_free_result($filtro_ramo_de_atividade); ?>
				</td>
          </tr>
        </table>
        </div>
		        
        <div class="div_filtros2">
        <table cellspacing="0" cellpadding="0" width="100%">
            <tr>
                <td style="text-align:left" valign="top">
                <fieldset>
                    <span class="label_solicitacao">Status:</span>
                    <table width="100%" border="0" cellspacing="0" cellpadding="0">
                    <tr>
                        <td width="250" valign="top">

                        
                        <input  name="status[]2" type="checkbox" class="checkbox" value="aguardando retorno do cliente"
                            <?
                            // verificar se foi selecionada
                            if(isset($_GET['status'])){
                                foreach($_GET["status"] as $status){
                                    if($status=="aguardando retorno do cliente"){
                                        echo "checked=\"checked\"";
                                    }
                                }
                            }
                            // verificar se foi selecionada
                            ?>
                        />
                        aguardando retorno do cliente <br>
                                                
                        <input  name="status[]" type="checkbox" class="checkbox" value="aguardando atendente"
                            <?
                            // verificar se foi selecionada
                            if(isset($_GET['status'])){
                                foreach($_GET["status"] as $status){
                                    if($status=="aguardando atendente"){
                                        echo "checked=\"checked\"";
                                    }
                                }
                            }
                            // verificar se foi selecionada
                            ?>
                        />aguardando atendente
                        
                        <br>
                        
                        <input  name="status[]" type="checkbox" class="checkbox" value="aguardando agendamento"
                            <?
                            // verificar se foi selecionada
                            if(isset($_GET['status'])){
                                foreach($_GET["status"] as $status){
                                    if($status=="aguardando agendamento"){
                                        echo "checked=\"checked\"";
                                    }
                                }
                            }
                            // verificar se foi selecionada
                            ?>
                        />aguardando agendamento
                        
                        </td>
                        
                        <td valign="top">                     
                    
                        <input  name="status[]" type="checkbox" class="checkbox" value="encaminhada para usuario responsavel"
                        <?
                        // verificar se foi selecionada
                        if(isset($_GET['status'])){
                            foreach($_GET["status"] as $status){
                                if($status=="encaminhada para usuario responsavel"){
                                    echo "checked=\"checked\"";
                                }
                            }
                        }
                        // verificar se foi selecionada
                        ?>
                    />encaminhada para usuario responsavel
                    
                    <br>
                    
                        <input  name="status[]" type="checkbox" class="checkbox" value="pendente usuario responsavel"
                        <?
                        // verificar se foi selecionada
                        if(isset($_GET['status'])){
                            foreach($_GET["status"] as $status){
                                if($status=="pendente usuario responsavel"){
                                    echo "checked=\"checked\"";
                                }
                            }
                        }
                        // verificar se foi selecionada
                        ?>
                    />pendente usuario responsavel
                    
                    <br>
                        
                        <input type="checkbox" class="checkbox" id="checkall_status"  name="checkall_status" />Marcar todos
                        
                        </td>
                    </tr>
                    </table>
				</fieldset>
                </td>
                
          </tr>
        </table>
        </div>
		
        <div class="div_filtros">
        <table cellspacing="0" cellpadding="0" width="100%">
            <tr>
                <td style="text-align:left">
                <span class="label_solicitacao">Necessidades/Interesses do cliente:</span>
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
							
								<input  name="necessidades[]" id="necessidades" type="checkbox" class="checkbox" value="<? echo $row_geral_tipo_modulo_listar['IdTipoModulo']; ?>"/>
								<? echo $row_geral_tipo_modulo_listar['descricao']; ?>
								
							<? } while ($row_geral_tipo_modulo_listar = mysql_fetch_assoc($geral_tipo_modulo_listar)); ?>
							
						<? mysql_free_result($geral_tipo_modulo_listar); ?>
						
					</div>
					
				<? } while ($row_geral_tipo_modulo_categoria_listar = mysql_fetch_assoc($geral_tipo_modulo_categoria_listar)); ?>
				</fieldset>
				<? mysql_free_result($geral_tipo_modulo_categoria_listar); ?>		
                </td>
            </tr>
        </table>
        </div>

        <div class="div_filtros2">
        <table cellspacing="0" cellpadding="0" width="100%">
            <tr>
                <td style="text-align:left" width="350px">
                <span class="label_solicitacao">Possui sistema: </span>
                <select name="sistema_possui" style="width: 200px">
                <option value=""
                <?php if (!(strcmp("", isset($_GET['filtro_sistema_possui'])))) {echo "selected=\"selected\"";} ?>>Escolha ...</option>
                
				<option value="s" <?php if((isset($_GET['sistema_possui'])) and (!(strcmp("s", $_GET['sistema_possui'])))){echo "selected=\"selected\"";} ?>>Sim</option>
				<option value="n" <?php if((isset($_GET['sistema_possui'])) and (!(strcmp("n", $_GET['sistema_possui'])))){echo "selected=\"selected\"";} ?>>Não</option>
                </select>
                </td>
				
				<td style="text-align:right">
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
				<select name="id_concorrente" id="id_concorrente" style="width: 250px;">
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
				</td>
          </tr>
        </table>
        </div>

        <div class="div_filtros">
        <table cellspacing="0" cellpadding="0" width="100%">
            <tr>				
				<td style="text-align:left" width="400px">
                <span class="label_solicitacao">Possuímos migração de dados: </span>
                <select name="migracao" style="width: 200px">
                <option value=""
                <?php if (!(strcmp("", isset($_GET['filtro_migracao'])))) {echo "selected=\"selected\"";} ?>>Escolha ...</option>
                
				<option value="s" <?php if((isset($_GET['migracao'])) and (!(strcmp("s", $_GET['migracao'])))){echo "selected=\"selected\"";} ?>>Sim</option>
				<option value="n" <?php if((isset($_GET['migracao'])) and (!(strcmp("n", $_GET['migracao'])))){echo "selected=\"selected\"";} ?>>Não</option>
                </select>
				</td>
				
				<td style="text-align:left" width="400px">
                <span class="label_solicitacao">Tipo de migração: </span>
                <select name="migracao_tipo" style="width: 200px">
                <option value=""
                <?php if (!(strcmp("", isset($_GET['filtro_migracao_tipo'])))) {echo "selected=\"selected\"";} ?>>Escolha ...</option>
                
				<option value="c" <?php if((isset($_GET['migracao_tipo'])) and (!(strcmp("c", $_GET['migracao_tipo'])))){echo "selected=\"selected\"";} ?>>Completa</option>
				<option value="p" <?php if((isset($_GET['migracao_tipo'])) and (!(strcmp("p", $_GET['migracao_tipo'])))){echo "selected=\"selected\"";} ?>>Parcial</option>
				<option value="b" <?php if((isset($_GET['migracao_tipo'])) and (!(strcmp("b", $_GET['migracao_tipo'])))){echo "selected=\"selected\"";} ?>>Cadastros básicos</option>
                </select>
				</td>
				
				<td style="text-align:right">
				<span class="label_solicitacao"><label id="label_sistema_nivel_satisfacao">Nível de Satisfação:<span id="req">*</span></label></span>
				<select name="sistema_nivel_satisfacao" id="sistema_nivel_satisfacao" style="width: 120px;">
				<option value=""> ...</option>
				<option value="a">Alto</option>
				<option value="m">Médio</option>
				<option value="b">Baixo</option>
				<option value="i">Insatisfeito</option>
				</select>
				</td>
          </tr>
        </table>
        </div>
		
        <div class="div_filtros2">
        <table cellspacing="0" cellpadding="0" width="100%">
            <tr>
				
				<td style="text-align:left" width="400px">
                <span class="label_solicitacao">Nível de interesse: </span>
                <select name="nivel_interesse" style="width: 200px">
                <option value=""
                <?php if (!(strcmp("", isset($_GET['filtro_nivel_interesse'])))) {echo "selected=\"selected\"";} ?>>Escolha ...</option>
				<option value="a" <?php if((isset($_GET['nivel_interesse'])) and (!(strcmp("a", $_GET['nivel_interesse'])))){echo "selected=\"selected\"";} ?>>Alto</option>
				<option value="m" <?php if((isset($_GET['nivel_interesse'])) and (!(strcmp("m", $_GET['nivel_interesse'])))){echo "selected=\"selected\"";} ?>>Médio</option>
				<option value="b" <?php if((isset($_GET['nivel_interesse'])) and (!(strcmp("b", $_GET['nivel_interesse'])))){echo "selected=\"selected\"";} ?>>Baixo</option>	
				<option value="n" <?php if((isset($_GET['nivel_interesse'])) and (!(strcmp("n", $_GET['nivel_interesse'])))){echo "selected=\"selected\"";} ?>>Nenhum</option>	
                </select>
				</td>
				
                <td style="text-align:right">
				<?
				// filtro contador
				mysql_select_db($database_conexao, $conexao);
				$query_filtro_contador = "SELECT id, razao FROM prospeccao_contador ORDER BY razao ASC";
				$filtro_contador = mysql_query($query_filtro_contador, $conexao) or die(mysql_error());
				$row_filtro_contador = mysql_fetch_assoc($filtro_contador);
				$totalRows_filtro_contador = mysql_num_rows($filtro_contador);	
				// fim - filtro contador
				?>
                <span class="label_solicitacao">Contabilidade: </span>
                <select name="id_contador" style="width: 200px">
                <option value=""
                <?php if (!(strcmp("", isset($_GET['filtro_id_contador'])))) {echo "selected=\"selected\"";} ?>>Escolha ...</option>
                <?php do {  ?>
                <option value="<?php echo $row_filtro_contador['id']; ?>"
                <?php if ( (isset($_GET['id_contador'])) and (!(strcmp($row_filtro_contador['id'], $_GET['id_contador']))) ) {echo "selected=\"selected\"";} ?>
                >
                <?php echo utf8_encode($row_filtro_contador['razao']); ?>
                </option>
                <?php
                } while ($row_filtro_contador = mysql_fetch_assoc($filtro_contador));
                $rows = mysql_num_rows($filtro_contador);
                if($rows > 0) {
                mysql_data_seek($filtro_contador, 0);
                $row_filtro_contador = mysql_fetch_assoc($filtro_contador);
                }
                ?>
                </select>
				<? mysql_free_result($filtro_contador); ?>
                </td>
				
				<td style="text-align:right">
                <span class="label_solicitacao">Indicador: </span>
                <select name="indicador" style="width: 200px">
                <option value=""
                <?php if (!(strcmp("", isset($_GET['filtro_indicador'])))) {echo "selected=\"selected\"";} ?>>Escolha ...</option>
                
				<option value="co" <?php if((isset($_GET['indicador'])) and (!(strcmp("co", $_GET['indicador'])))){echo "selected=\"selected\"";} ?>>Contador</option>
				<option value="cl" <?php if((isset($_GET['indicador'])) and (!(strcmp("cl", $_GET['indicador'])))){echo "selected=\"selected\"";} ?>>Cliente</option>
				<option value="cs" <?php if((isset($_GET['indicador'])) and (!(strcmp("cs", $_GET['indicador'])))){echo "selected=\"selected\"";} ?>>Colaborador Success</option>
				<option value="fu" <?php if((isset($_GET['indicador'])) and (!(strcmp("fu", $_GET['indicador'])))){echo "selected=\"selected\"";} ?>>Funcionário</option>
				<option value="te" <?php if((isset($_GET['indicador'])) and (!(strcmp("te", $_GET['indicador'])))){echo "selected=\"selected\"";} ?>>Terceiros</option>				
                </select>
				</td>
				
				<td style="text-align:right">
				<span class="label_solicitacao"><label id="label_cidade">Cidade:</label></span>
				<?
				// ibge_listar - para selectbox
				mysql_select_db($database_conexao, $conexao);
				$query_ibge_listar = "SELECT * FROM ibge ORDER BY ibge.cidade ASC";
				$ibge_listar = mysql_query($query_ibge_listar, $conexao) or die(mysql_error());
				$row_ibge_listar = mysql_fetch_assoc($ibge_listar);
				$totalRows_ibge_listar = mysql_num_rows($ibge_listar);
				// fim - ibge_listar - para selectbox
				?>
				<select name="cidade" id="cidade" style="width: 250px;">
                <option value="" <?php if (!(strcmp("", isset($_GET['cidade'])))) {echo "selected=\"selected\"";} ?>>Escolha ...</option>
				<?php
				do {  
				?>
				<option value="<?php echo $row_ibge_listar['cidade']?>" <?php if((isset($_GET['cidade'])) and (!(strcmp($row_ibge_listar['cidade'], $_GET['cidade'])))){echo "selected=\"selected\"";} ?>>
				<?php echo utf8_encode($row_ibge_listar['cidade']); ?>/<?php echo $row_ibge_listar['uf']; ?>
				</option>
				<?php
				} while ($row_ibge_listar = mysql_fetch_assoc($ibge_listar));
				$rows = mysql_num_rows($ibge_listar);
				if($rows > 0) {
				mysql_data_seek($ibge_listar, 0);
				$row_ibge_listar = mysql_fetch_assoc($ibge_listar);
				}
				?>
				</select>
				<? mysql_free_result($ibge_listar); ?>
				</td>
          </tr>
        </table>
        </div>
		
        <div class="div_filtros">
        <table cellspacing="0" cellpadding="0" width="100%">
            <tr>

                <td style="text-align:left" width="500px">
                <span class="label_solicitacao">Validade da Proposta/Orçamento: </span>
                <input name="proposta_validade" type="text" id="proposta_validade" value="<? if ( isset($_GET['proposta_validade']) ) { echo $_GET['proposta_validade']; } ?>" style="width: 200px" />
				</td>
				
				<td style="text-align:right">
				<span class="label_solicitacao">Motivo da perda: </span>
                <select name="baixa_perda_motivo" style="width: 200px">
                <option value=""
                <?php if (!(strcmp("", isset($_GET['filtro_baixa_perda_motivo'])))) {echo "selected=\"selected\"";} ?>>Escolha ...</option>
                
				<option value="falta de recurso" <?php if((isset($_GET['baixa_perda_motivo'])) and (!(strcmp("falta de recurso", $_GET['baixa_perda_motivo'])))){echo "selected=\"selected\"";} ?>>Falta de recurso</option>
				<option value="concorrência" <?php if((isset($_GET['baixa_perda_motivo'])) and (!(strcmp("concorrência", $_GET['baixa_perda_motivo'])))){echo "selected=\"selected\"";} ?>>Concorrência</option>
				</select>
				</td>
          </tr>
        </table>
        </div>
		
	</div>
	<!-- fim - filtros da prospeccao -->


    <!-- filtros da agenda -->
    <div class="div_solicitacao_linhas">
    <table cellspacing="0" cellpadding="0" width="100%">
        <tr>
            <td style="text-align:left">
            Filtros - Agenda
            </td>
        </tr>
    </table>
    </div>
    
    <div style="border: 1px solid #c5dbec; margin-bottom: 5px;">
   
        <div class="div_filtros">
        <table cellspacing="0" cellpadding="0" width="100%">        
        <td style="text-align: left">
        <span class="label_solicitacao">Data (inicial): </span>
        <input name="prospeccao_agenda_data_inicio" id="prospeccao_agenda_data_inicio" type="text" value="<? 
        if ( isset($_GET['prospeccao_agenda_data_inicio']) ){ echo $_GET['prospeccao_agenda_data_inicio']; }
        ?>" />
        </td>
        
        <td style="text-align:right" width="300">
        <span class="label_solicitacao">Data (final): </span>
        <input name="prospeccao_agenda_data_fim" id="prospeccao_agenda_data_fim" type="text" value="<? 
        if ( isset($_GET['prospeccao_agenda_data_fim']) ){ echo $_GET['prospeccao_agenda_data_fim']; }
        ?>" />
        </td>
        </tr>
        </table>
        </div>
           
        <div class="div_filtros2">
        <table cellspacing="0" cellpadding="0" width="100%">
            <tr>
                <td style="text-align:left" valign="top">
                <fieldset>
                    <span class="label_solicitacao">Status:</span>
                    <table width="100%" border="0" cellspacing="0" cellpadding="0">
                    <tr>
                        <td width="400" valign="top">
                        
                        <input  name="prospeccao_agenda_status[]" type="checkbox" class="checkbox" value="a"
                            <?
                            // verificar se foi selecionada
                            if(isset($_GET['prospeccao_agenda_status'])){
                                foreach($_GET["prospeccao_agenda_status"] as $prospeccao_agenda_status){
                                    if($prospeccao_agenda_status=="a"){
                                        echo "checked=\"checked\"";
                                    }
                                }
                            }
                            // verificar se foi selecionada
                            ?>
                        />Agendado
                                                
                        <input  name="prospeccao_agenda_status[]" type="checkbox" class="checkbox" value="f"
                            <?
                            // verificar se foi selecionada
                            if(isset($_GET['prospeccao_agenda_status'])){
                                foreach($_GET["prospeccao_agenda_status"] as $prospeccao_agenda_status){
                                    if($prospeccao_agenda_status=="f"){
                                        echo "checked=\"checked\"";
                                    }
                                }
                            }
                            // verificar se foi selecionada
                            ?>
                        />Finalizado
                        
                        <input  name="prospeccao_agenda_status[]" type="checkbox" class="checkbox" value="c"
                            <?
                            // verificar se foi selecionada
                            if(isset($_GET['prospeccao_agenda_status'])){
                                foreach($_GET["prospeccao_agenda_status"] as $prospeccao_agenda_status){
                                    if($prospeccao_agenda_status=="c"){
                                        echo "checked=\"checked\"";
                                    }
                                }
                            }
                            // verificar se foi selecionada
                            ?>
                        />Cancelado
                        
						</td>                        
                    </tr>
                    </table>
				</fieldset>
                </td>
                
                <td style="text-align: right" valign="top">
                <fieldset>
                    <span class="label_solicitacao">Em Atraso:</span>
                    <table width="100%" border="0" cellspacing="0" cellpadding="0">
                    <tr>
                        <td width="400" valign="top">
                        
                        <input  name="prospeccao_agenda_atraso" type="checkbox" class="checkbox" value="s"
                            <?
                            // verificar se foi selecionada
                            if(isset($_GET['prospeccao_agenda_atraso']) and $_GET['prospeccao_agenda_atraso'] == "s"){
                            	echo "checked=\"checked\"";
                            }
                            // verificar se foi selecionada
                            ?>
                        />Em atraso

                        
						</td>                        
                    </tr>
                    </table>
				</fieldset>
                </td>
                
          </tr>
        </table>
        </div>

	</div>
	<!-- fim - filtros da agenda -->
    

    <div class="div_filtros">
    <table cellspacing="0" cellpadding="0" width="100%">
        <tr>
            <td style="text-align:left">
            <input name="Filtrar" type="submit" value="Filtrar" class="botao_geral2" style="width: 100px" />
            <input onclick="clear_form_elements(this.form)" type="button" value="Limpar filtro" class="botao_geral2" style="width: 100px" />
            </td>
        </tr>
    </table>
    </div>  
          

</div>
</form>
<!-- fim - filtros -->


<!-- prospeccao -->
<? if($totalRows_prospeccao > 0){ ?>
<div class="div_solicitacao_linhas" id="cabecalho_prospeccao" style="cursor: pointer">
<table cellspacing="0" cellpadding="0" width="100%">
	<tr>
		<td style="text-align:left">
		Prospecções (<? echo $totalRows_prospeccao; ?>)
		</td>
        
		<td style="text-align: right">
		<div style="float: right; background-image:url(imagens/icone_filtro.png); width: 12px; height: 12px;"></div>
		</td>
	</tr>
</table>
</div>

<div id="corpo_prospeccao" style="cursor: pointer">
<table id="prospeccao"></table>
<div id="prospeccao_navegacao"></div>
<script type="text/javascript">
var dados = [		 
<?php do { ?>

<?
// status ------------------------------------------------------------------------------------------------------------------------------------
$cor_css = "cor_black";

if($row_prospeccao['status']=='aguardando agendamento' and $row_prospeccao['status_flag']=='a'){
	$cor_css = "cor_red";
}
// fim - status ------------------------------------------------------------------------------------------------------------------------------
?>

{
	id:"<?php echo $row_prospeccao['id']; ?>",
	nome_razao_social:"<?php echo $row_prospeccao['nome_razao_social']; ?>",
	<? if($row_usuario['controle_prospeccao'] == "Y"){ ?>
		praca:"<?php echo $row_prospeccao['praca']; ?>",
	<? } ?>
	usuario_responsavel:"<?php echo $row_prospeccao['usuario_responsavel']; ?>",
	id_contador:"<?php echo $row_prospeccao['contador_razao']; ?>",
	sistema_possui:"<?php 
	if($row_prospeccao['sistema_possui']=="s"){echo "Sim";}
	if($row_prospeccao['sistema_possui']=="n"){echo "Não";}
	?>",
	nivel_interesse:"<?php 
	if($row_prospeccao['nivel_interesse']=="a"){echo "Alto";}
	if($row_prospeccao['nivel_interesse']=="m"){echo "Médio";}
	if($row_prospeccao['nivel_interesse']=="b"){echo "Baixo";}
	if($row_prospeccao['nivel_interesse']=="n"){echo "Nenhum";}
	?>",
	ultima_visita:"<?php echo "-"; ?>",
	
	status:"<? echo "<div class='$cor_css'>"; ?><?php 

    if($row_prospeccao['status']==""){echo "&nbsp;";}

    if($row_prospeccao['status']=="pendente usuario responsavel"){echo "<span title='pendente usuario responsavel'>PenRes</span>";}
    if($row_prospeccao['status']=="encaminhada para usuario responsavel"){echo "<span title='encaminhada para usuario responsavel'>EncRes</span>";}    
    if($row_prospeccao['status']=="devolvida para usuario responsavel"){echo "<span title='devolvida para usuario responsavel'>DevRes</span>";}

    if($row_prospeccao['status']=="aguardando retorno do cliente"){echo "<span title='aguardando retorno do cliente'>AguCli</span>";}
	if($row_prospeccao['status']=="aguardando atendente"){echo "<span title='aguardando atendente'>AguAte</span>";}
	if($row_prospeccao['status']=="aguardando agendamento"){echo "<span title='aguardando agendamento'>AguAge</span>";}

    ?><? echo "</div>"; ?>",
	situacao:"<?php echo $row_prospeccao['situacao']; ?>",
	data_prospeccao:"<?php echo $row_prospeccao['data_prospeccao']; ?>",
	visualizar:"<? echo "<a href='prospeccao_editar.php?id_prospeccao=".$row_prospeccao['id']."&padrao=sim'a><img src='imagens/visualizar.png' border='0' /></a>"; ?>"
},
<?php } while ($row_prospeccao = mysql_fetch_assoc($prospeccao)); ?>
];
jQuery('#prospeccao').jqGrid({
	data:dados,
	datatype: 'local',
	colNames:['Núm','Cliente',<? if($row_usuario['controle_prospeccao'] == "Y"){ ?>'Praça',<? } ?>'Responsável','Contador','Possui Soft.','Interesse','Última Visita','Status','Situação','Data',''],
	colModel :[ 
		{name:'id', index:'id', width:30, sorttype: 'integer'}, 
		{name:'nome_razao_social', index:'nome_razao_social'}, 
		<? if($row_usuario['controle_prospeccao'] == "Y"){ ?>
		{name:'praca', index:'praca', width: 60, align:'center'},
		<? } ?>
		{name:'usuario_responsavel', index:'usuario_responsavel', width:50, align:'left'}, 
		{name:'id_contador', index:'id_contador', width:50, align:'left'}, 
		{name:'sistema_possui', index:'sistema_possui', width:50, align:'left'}, 
		{name:'nivel_interesse', index:'nivel_interesse', width:50, align:'left'}, 
		{name:'ultima_visita', index:'ultima_visita', width:50, align:'left'}, 
		
		{name:'status', index:'status', width: 60, align:'center'},
		{name:'situacao', index:'situacao', width: 60, align:'center'},
		{name:'data_prospeccao', index:'data_prospeccao', width:60, formatter:'date', formatoptions: { srcformat: "ISO8601Long", newformat: "d-m-Y" }, align:'center' },
		{name:'visualizar', index:'visualizar', width:20, align:'center'} 
	],
	rowNum:20,
	rowList:[2,5,10,20,30,40,50,100,999999],
				loadComplete: function() {
					$("option[value=999999]").text('Todos');
				},
	pager: '#prospeccao_navegacao',
	//sortname: 'id',
	viewrecords: true,
	//sortorder: 'desc',
	toppager: true, // aparecer a barra de navegação também no topo
	// caption:"Suportes", barra no topo
	autowidth: true,
	height: "100%",

	ondblClickRow: function(id){
		top.location.href="prospeccao_editar.php?id_prospeccao="+id+"&padrao=sim";
	}		
});
</script>
</div>
<? } else { ?>
<div class="div_solicitacao_linhas4">
Nenhuma prospeção encontrada na filtragem atual.
</div>
<? } ?>
<!-- fim - prospeccao -->


<!-- prospeccao_agenda -->
<? if($totalRows_prospeccao_agenda > 0){ ?>
<div class="div_solicitacao_linhas" id="cabecalho_prospeccao_agenda" style="cursor: pointer; margin-top: 5px;">
<table cellspacing="0" cellpadding="0" width="100%">
	<tr>
		<td style="text-align:left">
		Agenda de Prospecções (<? echo $totalRows_prospeccao_agenda; ?>)
		</td>
        
		<td style="text-align: right">
		<div style="float: right; background-image:url(imagens/icone_filtro.png); width: 12px; height: 12px;"></div>
		</td>
	</tr>
</table>
</div>

<div id="corpo_prospeccao_agenda" style="cursor: pointer">
<table id="prospeccao_agenda"></table>
<div id="prospeccao_agenda_navegacao"></div>
<script type="text/javascript">
var dados = [		 
<?php do { ?>

<?
// status ------------------------------------------------------------------------------------------------------------------------------------
$cor_css = "cor_black";

// geral
if($row_prospeccao_agenda['prospeccao_agenda_data_inicio']!="" and $row_prospeccao_agenda['prospeccao_agenda_data_inicio']!="0000-00-00 00:00:00"){

	$previsao_geral = funcaoAcrescentaDiasNaoUteis($row_prospeccao_agenda['prospeccao_agenda_data_inicio']); // chama a função que altera a data para o próximo dia útil
    $data1 = strtotime($previsao_geral); // converte em segundos
	
    $data2 = strtotime(date("Y-m-d H:i:s")); // converte em segundos
    $diferenca = $data2 - $data1;

	# teste
    if($diferenca>=86400 and $diferenca<172800){ // entre 24 e 48 hrs
            $cor_css = "cor_orange";
    }
    else if($diferenca>=172800){ // mais de 48hrs
            $cor_css = "cor_red";
    } else { // menos de 24hrs
            $cor_css = "cor_black";
    }
	# fim - teste
	
} else {
	
	if($row_prospeccao_agenda['status']=='aguardando agendamento' and $row_prospeccao_agenda['status_flag']=='a'){
		$cor_css = "cor_red";
	}
	
}
// fim - geral
// fim - status ------------------------------------------------------------------------------------------------------------------------------
?>

{
	id:"<?php echo $row_prospeccao_agenda['id']; ?>",
	nome_razao_social:"<?php echo $row_prospeccao_agenda['nome_razao_social']; ?>",
	<? if($row_usuario['controle_prospeccao'] == "Y"){ ?>
		praca:"<?php echo $row_prospeccao_agenda['praca']; ?>",
	<? } ?>
	usuario_responsavel:"<?php echo $row_prospeccao_agenda['usuario_responsavel']; ?>",
	status:"<? echo "<div class='$cor_css'>"; ?><?php 

    if($row_prospeccao_agenda['status']==""){echo "&nbsp;";}

    if($row_prospeccao_agenda['status']=="pendente usuario responsavel"){echo "<span title='pendente usuario responsavel'>PenRes</span>";}
    if($row_prospeccao_agenda['status']=="encaminhada para usuario responsavel"){echo "<span title='encaminhada para usuario responsavel'>EncRes</span>";}    
    if($row_prospeccao_agenda['status']=="devolvida para usuario responsavel"){echo "<span title='devolvida para usuario responsavel'>DevRes</span>";}

    if($row_prospeccao_agenda['status']=="aguardando retorno do cliente"){echo "<span title='aguardando retorno do cliente'>AguCli</span>";}
	if($row_prospeccao_agenda['status']=="aguardando atendente"){echo "<span title='aguardando atendente'>AguAte</span>";}
	if($row_prospeccao_agenda['status']=="aguardando agendamento"){echo "<span title='aguardando agendamento'>AguAge</span>";}

    ?><? echo "</div>"; ?>",
	situacao:"<?php echo $row_prospeccao_agenda['situacao']; ?>",
	data_prospeccao:"<?php echo $row_prospeccao_agenda['data_prospeccao']; ?>",
	prospeccao_agenda_data_inicio:"<?php echo $row_prospeccao_agenda['prospeccao_agenda_data_inicio']; ?>",
	prospeccao_agenda_descricao:"<?php echo GetSQLValueString($row_prospeccao_agenda['prospeccao_agenda_descricao'], "string"); ?>",
	prospeccao_agenda_status:"<?php 
	if($row_prospeccao_agenda['prospeccao_agenda_status']=="a"){echo "Agendado";}
	if($row_prospeccao_agenda['prospeccao_agenda_status']=="f"){echo "Finalizado";}
	if($row_prospeccao_agenda['prospeccao_agenda_status']=="c"){echo "Cancelado";}
	?>",
	visualizar:"<? echo "<a href='prospeccao_editar.php?id_prospeccao=".$row_prospeccao_agenda['id']."&padrao=sim'a><img src='imagens/visualizar.png' border='0' /></a>"; ?>"
},
<?php } while ($row_prospeccao_agenda = mysql_fetch_assoc($prospeccao_agenda)); ?>
];
jQuery('#prospeccao_agenda').jqGrid({
	data:dados,
	datatype: 'local',
	colNames:['Núm','Cliente',<? if($row_usuario['controle_prospeccao'] == "Y"){ ?>'Praça',<? } ?>'Responsável','Status','Situação','Data criação','Agenda (data)','Agenda (descrição)','Agenda (status)',''],
	colModel :[ 
		{name:'id', index:'id', width:30, sorttype: 'integer'}, 
		{name:'nome_razao_social', index:'nome_razao_social'}, 
		<? if($row_usuario['controle_prospeccao'] == "Y"){ ?>
		{name:'praca', index:'praca', width: 80, align:'center'},
		<? } ?>
		{name:'usuario_responsavel', index:'usuario_responsavel', width:70, align:'left'}, 
		{name:'status', index:'status', width: 80, align:'center'},
		{name:'situacao', index:'situacao', width: 80, align:'center'},
		{name:'data_prospeccao', index:'data_prospeccao', width:70, formatter:'date', formatoptions: { srcformat: "ISO8601Long", newformat: "d-m-Y H:i" }, align:'center' },
		{name:'prospeccao_agenda_data_inicio', index:'prospeccao_agenda_data_inicio', width:70, formatter:'date', formatoptions: { srcformat: "ISO8601Long", newformat: "d-m-Y H:i" }, align:'center' },
		{name:'prospeccao_agenda_descricao', index:'prospeccao_agenda_descricao', width: 140, align:'left'},
		{name:'prospeccao_agenda_status', index:'prospeccao_agenda_status', width: 80, align:'center'},
		{name:'visualizar', index:'visualizar', width:20, align:'center'} 
	],
	rowNum:20,
	rowList:[2,5,10,20,30,40,50,100,999999],
				loadComplete: function() {
					$("option[value=999999]").text('Todos');
				},
	pager: '#prospeccao_agenda_navegacao',
	//sortname: 'id',
	viewrecords: true,
	//sortorder: 'desc',
	toppager: true, // aparecer a barra de navegação também no topo
	// caption:"Suportes", barra no topo
	autowidth: true,
	height: "100%",

	ondblClickRow: function(id){
		top.location.href="prospeccao_editar.php?id_prospeccao="+id+"&padrao=sim";
	}		
});
</script>
</div>
<? } else { ?>
<div class="div_solicitacao_linhas4">
Nenhuma prospeção encontrada na filtragem atual.
</div>
<? } ?>
<!-- fim - prospeccao_agenda -->


<!-- barra inferior -->
<div class="div_solicitacao_linhas4" style="margin-top: 5px;">
<table cellspacing="0" cellpadding="0" width="100%">
	<tr>
		<td style="text-align:left">

<a href="agenda_popup.php?height=<? echo $prospeccao_editar_tabela_height; ?>&width=<? echo $prospeccao_editar_tabela_width; ?>&placeValuesBeforeTB_=savedValues&TB_iframe=true" id="botao_geral2" class="thickbox">Ver agenda</a>
  
<!-- Gerar relatório -->   
<? if($totalRows_prospeccao > "0") { // caso seja encontrada algum suporte com os filtros atuais ?>
<a href="#TB_inline?height=<? echo $prospeccao_editar_tabela_height; ?>&width=<? echo $prospeccao_editar_tabela_width; ?>&inlineId=gerar_relatorio&modal=true" class="thickbox" id="botao_geral2">Gerar relatório</a>
<? } ?>
<!-- fim - Gerar relatório -->

		</td>
	</tr>
</table>
</div>
<!-- fim - barra inferior -->


<!-- relatórios (oculto) -->
<script>
//função de submit
function enviar(){
document.getElementById('form').submit();
}
</script>
<div id="gerar_relatorio" style="display: none;">
    <form action="prospeccao_relatorio.php" method="post" target="_blank" id="form" name="form">
    <!-- cabeçalho -->
    <div class="div_solicitacao_linhas">
    <table cellspacing="0" cellpadding="0" width="100%">
        <tr>
            <td style="text-align:left">
            Gerar relatório
            </td>
    
            <td style="text-align: right">
            &lt;&lt; <a href="#" onClick="self.parent.tb_remove();" style="color: #FFF;">Voltar</a>
            </td>
        </tr>
    </table>
    </div>
    <!-- fim - cabeçalho -->
    
    <div class="div_solicitacao_linhas4">
    <input name="relatorio_tipo" type="radio" value="prospeccao" checked="checked" /> Prospecções
    <input name="relatorio_tipo" type="radio" value="prospeccao_agenda" /> Agenda de Prospecções
    </div>
    
    <div class="div_solicitacao_linhas4">
        Marque os campos que irão aparecer no relatório:
        <br><br>
        <!-- campos (checklist) -->
        <fieldset style="border: 0px;">
        <table width="100%" border="0" cellspacing="0" cellpadding="0">
        <tr>
        <td width="25%" valign="top">
		
        <input value="id" type="checkbox" name="relatorio_campos[]" checked />
        Núm. da prospecção
        <br>
        
        
        <input value="nome_razao_social" type="checkbox" name="relatorio_campos[]" checked />
        Nome/Razão Social
        <br>
        
        <input value="pessoa" type="checkbox" name="relatorio_campos[]" checked />
        Pessoa (física/jurídica)
        <br>
        
        <input value="fantasia" type="checkbox" name="relatorio_campos[]" checked />
        Nome Fantasia
        <br>
        
        <input value="cep" type="checkbox" name="relatorio_campos[]" checked />
        CEP
        <br>
               
        <input value="endereco" type="checkbox" name="relatorio_campos[]" checked />
        Endereço
        <br>
        
        <input value="bairro" type="checkbox" name="relatorio_campos[]" />
        Bairro
        <br>
                
        <input value="cidade" type="checkbox" name="relatorio_campos[]" />
        Cidade
        <br>
        
        <input value="uf" type="checkbox" name="relatorio_campos[]" />
        Estado
		<br>
        
        <input value="praca" type="checkbox" name="relatorio_campos[]" />
        Praça
        <br>
		
        </td>
        <td width="25%" valign="top">
		
        <input value="telefone" type="checkbox" name="relatorio_campos[]" />
        Telefone
        <br>
        
        <input value="celular" type="checkbox" name="relatorio_campos[]" />
        Celular
        <br>
        
        <input value="cpf_cnpj" type="checkbox" name="relatorio_campos[]" />
        CPF/CNPJ
        <br>
        
        <input value="rg_inscricao" type="checkbox" name="relatorio_campos[]" />
        RG/Inscrição
        <br>
        
        <input value="observacao" type="checkbox" name="relatorio_campos[]" />
        Observação
        <br>
        
        <input value="data_prospeccao" type="checkbox" name="relatorio_campos[]" />
        Data de Criação
        <br>
        
        
         <input value="data_prospeccao_fim" type="checkbox" name="relatorio_campos[]" />
        Data Final
        <br>
        
        <input value="ativo_passivo" type="checkbox" name="relatorio_campos[]" />
        Tipo de prospecção (ativo/passivo)
        <br>
        
        <input value="indicado_por" type="checkbox" name="relatorio_campos[]" />
        Indicado por
        <br>
        
        <input value="responsavel_por_ti" type="checkbox" name="relatorio_campos[]" />
        Responsável por T.I.
        <br>
        
        </td>
        <td width="25%" valign="top">

        <input value="enquadramento_fiscal" type="checkbox" name="relatorio_campos[]" />
        Enquadramento Fiscal
        <br>
        
        <input value="ramo_de_atividade" type="checkbox" name="relatorio_campos[]" />
        Ramo de Atividade
        <br>
        
        <input value="contador" type="checkbox" name="relatorio_campos[]" />
        Contador
        <br>

        <input value="exige_nfe" type="checkbox" name="relatorio_campos[]" />
        Exige NFE
        <br>

        <input value="exige_cupom_fiscal" type="checkbox" name="relatorio_campos[]" />
        Exige Cupom Fiscal
        <br>
        
        <input value="exige_outro" type="checkbox" name="relatorio_campos[]" />
        Exige Outros
        <br>

        <input value="situacao" type="checkbox" name="relatorio_campos[]" checked />
        Situação
        <br>
                
        <input value="status" type="checkbox" name="relatorio_campos[]" />
        Status
        <br>
 
         <input value="usuario_responsavel" type="checkbox" name="relatorio_campos[]" />
        Usuário Responsável
        <br>
               
        </td>
		
        <td valign="top">

        <input value="sistema_possui" type="checkbox" name="relatorio_campos[]" />
        Possui Sistema?
        <br>
        
        <input value="migracao" type="checkbox" name="relatorio_campos[]" />
        Possuímos migração de dados?
        <br>
        
        <input value="migracao_tipo" type="checkbox" name="relatorio_campos[]" />
        Tipo de migração
        <br>

        <input value="prospeccao_concorrente_nome" type="checkbox" name="relatorio_campos[]" />
        Concorrente
        <br>

        <input value="sistema_nivel_satisfacao" type="checkbox" name="relatorio_campos[]" />
        Nível de Satisfação
        <br>
        
        <input value="nivel_interesse" type="checkbox" name="relatorio_campos[]" />
        Nível de Interesse
        <br>

        <input value="baixa_perda_motivo" type="checkbox" name="relatorio_campos[]" />
        Motivo da Perda
        <br>
                
        <input value="prospeccao_contador_razao" type="checkbox" name="relatorio_campos[]" />
       	Contabilidade
        <br>
               
        </td>
		
        </tr>
        </table>
        </fieldset>
        <!-- fim - campos (checklist) -->        
    </div>
    
    <!-- rodapé -->
    <div>Obs: este relatório é baseado nos filtros utilizados na tela anterior de listagem de prospecções.</div>
    <div style="margin-top: 5px;">
    <input type="hidden" name="where" id="where" value="<?  echo @$where; ?>">
    <input type="hidden" name="where_agenda" id="where_agenda" value="<?  echo @$where_agenda; ?>">
    <?
	$campos = "";
    $count = count(@$where_campos);
    if($count > 0){
        for ($i = 0; $i < $count; $i++) {
            $campos .= $where_campos[$i].";";
        }
    }
	?>
    <input type="hidden" name="campos" id="campos" value="<?  echo $campos; ?>">
    <a href="#" onclick="enviar();" id="botao_geral2">Visualizar</a>
    </div>
    <!-- fim - rodapé -->       
    </form>
</div>
<!-- fim - relatórios (oculto) -->


</body>

</html>

<?php
mysql_free_result($usuario);
mysql_free_result($filtro_praca);
mysql_free_result($prospeccao);
mysql_free_result($prospeccao_agenda);
?>