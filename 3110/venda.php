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

// filtro usuario_responsavel - ok
mysql_select_db($database_conexao, $conexao);
$query_filtro_id_usuario_responsavel = "SELECT IdUsuario, nome FROM usuarios ORDER BY nome ASC";
$filtro_id_usuario_responsavel = mysql_query($query_filtro_id_usuario_responsavel, $conexao) or die(mysql_error());
$row_filtro_id_usuario_responsavel = mysql_fetch_assoc($filtro_id_usuario_responsavel);
$totalRows_filtro_id_usuario_responsavel = mysql_num_rows($filtro_id_usuario_responsavel);	
// fim - filtro usuario_responsavel

mysql_select_db($database_conexao, $conexao);

$where = "1=1";
$where_agenda_treinamento = "1=1";
$where_agenda_implantacao = "1=1";

// se padrão ---------------------------------------------------------------------------------------------------------------------------------------------------------
if ( isset($_GET['padrao']) && ($_GET['padrao'] == "sim") ){

	$where_agenda_treinamento .= " and agenda.data <= '".date("Y-m-d")." 23:59:59' ";
	$where_agenda_implantacao .= " and agenda.data <= '".date("Y-m-d")." 23:59:59' ";
	$_GET['venda_agenda_treinamento_data_fim'] = date("d-m-Y");
	$_GET['venda_agenda_implantacao_data_fim'] = date("d-m-Y");

}	
// fim - se padrão ---------------------------------------------------------------------------------------------------------------------------------------------------


// controle_venda =================================================================================================================
if($row_usuario['controle_venda'] == "Y"){
	
	$where .= " and ( 
					 venda.praca = '".$row_usuario['praca']."' or 
					 venda.praca <> '".$row_usuario['praca']."' or 
					 venda.id_usuario_responsavel = '".$row_usuario['IdUsuario']."'
					 ) ";
	
}
// fim - controle_venda ===========================================================================================================

// nível 1 =============================================================================================================================
else if($row_usuario['nivel_venda'] == 1){

	$where .= " and ( 
					 venda.praca = '".$row_usuario['praca']."' or 
					 venda.id_usuario_responsavel = '".$row_usuario['IdUsuario']."'
					 ) ";
	
}
// fim - nível 1 =======================================================================================================================

// nível 2 =============================================================================================================================
else if($row_usuario['nivel_venda'] == 2){
	
	$where .= " and ( 
					 venda.praca = '".$row_usuario['praca']."' or 
					 venda.id_usuario_responsavel = '".$row_usuario['IdUsuario']."'
					 ) ";
	
}
// fim - nível 2 =======================================================================================================================

// nível 3 =============================================================================================================================
else if($row_usuario['nivel_venda'] == 3){
	
	$where .= " and ( 
					 venda.praca = '".$row_usuario['praca']."' or 
					 venda.id_usuario_responsavel = '".$row_usuario['IdUsuario']."'
					 ) ";
	
}
// fim - nível 3 =======================================================================================================================

$where_usuario_logado = $where; // para o filtro por id (elimina todos os outros filtros)


// venda - filtros --------------------------------------------------------------------------------------------------------------------------------------------
// se existe filtro de empresa
if( (isset($_GET["empresa"])) && ($_GET['empresa'] !="") ) {
	$colname_venda_empresa = GetSQLValueString($_GET["empresa"], "string");
	$where .= " and venda.empresa LIKE '%$colname_venda_empresa%' ";
	$where_campos[] = "empresa";
}
// fim - se existe filtro de empresa

// se existe filtro de praca
if( (isset($_GET["praca"])) && ($_GET['praca'] !="") ) {
	$colname_venda_praca = GetSQLValueString($_GET["praca"], "string");
	$where .= " and venda.praca = '$colname_venda_praca' "; 	
	$where_campos[] = "praca";
} 
// fim - se existe filtro de praca

// se existe filtro de usuario_responsavel
if( (isset($_GET["id_usuario_responsavel"])) && ($_GET['id_usuario_responsavel'] !="") ) {
	$colname_venda_usuario_responsavel = $_GET['id_usuario_responsavel'];
	$where .= " and venda.id_usuario_responsavel = '$colname_venda_usuario_responsavel' ";
	$where_campos[] = "usuario_responsavel";
} 
// fim - se existe filtro de usuario_responsavel

// se existe filtro de id
if( (isset($_GET["id"])) && ($_GET['id'] !="") ) {
	$colname_venda_id = GetSQLValueString($_GET["id"], "int");
	$where = $where_usuario_logado." and venda.id = '$colname_venda_id' ";
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
				$query_status .= sprintf(" venda.status = '$status' $or");

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
				$query_situacao .= sprintf(" venda.situacao = '$situacao' $or");

		}
		$where .= sprintf($query_situacao)." ) ";
		$where_campos[] = "situacao";		
}
// fim - se existe filtro de situacao


// se existe filtro de data_venda ( somente data final )
if( ((isset($_GET["data_venda_fim"])) && ($_GET["data_venda_fim"] != "")) && ($_GET["data_venda_inicio"] == "") ) {

		// converter data em portugues para ingles
		if ( isset($_GET["data_venda_fim"]) ) {
			$data_venda_fim_data = substr($_GET["data_venda_fim"],0,10);
			$data_venda_fim_hora = " 23:59:59";
			$data_venda_fim = implode("-",array_reverse(explode("-",$data_venda_fim_data))).$data_venda_fim_hora;
			$where_campos[] = "data_venda_fim";
		}
		// converter data em portugues para ingles - fim
	
		$colname_venda_data_venda_fim = GetSQLValueString($data_venda_fim, "string");
		$where .= " and venda.data_venda <= '".$colname_venda_data_venda_fim."' ";
}
// fim - se existe filtro de data_venda ( somente data final )

// se existe filtro de data_venda ( somente data inicial )
if( ((isset($_GET["data_venda_inicio"])) && ($_GET["data_venda_inicio"] != "")) && ($_GET["data_venda_fim"] == "") ) {

		// converter data em portugues para ingles
		if ( isset($_GET["data_venda_inicio"]) ) {
			$data_venda_inicio_data = substr($_GET["data_venda_inicio"],0,10);
			$data_venda_inicio_hora = " 00:00:00";
			$data_venda_inicio = implode("-",array_reverse(explode("-",$data_venda_inicio_data))).$data_venda_inicio_hora;
			$where_campos[] = "data_venda_inicio";
		}
		// converter data em portugues para ingles - fim
	
		$colname_venda_data_venda_inicio = GetSQLValueString($data_venda_inicio, "string");
		$where .= " and venda.data_venda >= '".$colname_venda_data_venda_inicio."' ";
}
// fim - se existe filtro de data_venda ( somente data inicial )

// se existe filtro de data_venda ( entre data inicial e data final )
if( ((isset($_GET["data_venda_inicio"])) && ($_GET["data_venda_inicio"] != "")) && ((isset($_GET["data_venda_fim"])) && ($_GET["data_venda_fim"] != "")) ) {

		// converter data em portugues para ingles
		if ( isset($_GET["data_venda_inicio"]) ) {
			$data_venda_inicio_data = substr($_GET["data_venda_inicio"],0,10);
			$data_venda_inicio_hora = " 00:00:00";
			$data_venda_inicio = implode("-",array_reverse(explode("-",$data_venda_inicio_data))).$data_venda_inicio_hora;
			$where_campos[] = "data_venda_inicio";
		}
		// converter data em portugues para ingles - fim

		// converter data em portugues para ingles
		if ( isset($_GET["data_venda_fim"]) ) {
			$data_venda_fim_data = substr($_GET["data_venda_fim"],0,10);
			$data_venda_fim_hora = " 23:59:59";
			$data_venda_fim = implode("-",array_reverse(explode("-",$data_venda_fim_data))).$data_venda_fim_hora;
			$where_campos[] = "data_venda_fim";
		}
		// converter data em portugues para ingles - fim
	
		$colname_venda_data_venda_inicio = GetSQLValueString($data_venda_inicio, "string");
		$colname_venda_data_venda_fim = GetSQLValueString($data_venda_fim, "string");

		$where .= " and venda.data_venda between '$colname_venda_data_venda_inicio' and '$colname_venda_data_venda_fim' "; 
}
// fim - se existe filtro de data_venda ( entre data inicial e data final )
// fim - venda - filtros --------------------------------------------------------------------------------------------------------------------------------------

// venda
$query_venda = "
SELECT 
venda.id, venda.status, venda.situacao, venda.data_venda, venda.id_usuario_responsavel, 
venda.empresa, venda.quantidade_agendado_treinamento, venda.quantidade_agendado_implantacao, venda.status_flag, 
venda.id_prospeccao, venda.contrato, venda.praca, venda.data_inicio, venda.data_fim, venda.observacao, 
(SELECT nome FROM usuarios WHERE venda.id_usuario_responsavel = usuarios.IdUsuario) AS usuario_responsavel

FROM venda 
WHERE $where 
ORDER BY venda.praca ASC, venda.id ASC";

$venda = mysql_query($query_venda, $conexao) or die(mysql_error());
$row_venda = mysql_fetch_assoc($venda);
$totalRows_venda = mysql_num_rows($venda);
// fim - venda


// venda_agenda_treinamento - filtros ------------------------------------------------------------------------------------------------------------------------------
// se existe filtro de venda_agenda_treinamento_status
$contador_venda_agenda_treinamento_status = 0;
$contador_venda_agenda_treinamento_status_atual = 0;
if( (isset($_GET["venda_agenda_treinamento_status"])) && ($_GET['venda_agenda_treinamento_status'] !="") ) {

		// contar quantidade de situacões atual
		foreach($_GET["venda_agenda_treinamento_status"] as $venda_agenda_treinamento_status){
				$contador_venda_agenda_treinamento_status = $contador_venda_agenda_treinamento_status + 1;
		}
		// fim - contar quantidade de situacões atual

		$query_venda_agenda_treinamento_treinamento_status=" and ( ";
		foreach($_GET["venda_agenda_treinamento_status"] as $venda_agenda_treinamento_status){
			
				$contador_venda_agenda_treinamento_status_atual = $contador_venda_agenda_treinamento_status_atual + 1; // verifica o contador atual
				$contador_total = $contador_venda_agenda_treinamento_status - $contador_venda_agenda_treinamento_status_atual; // calcula diferença de situações total - situação atual
				if($contador_total<>0){$or=" or ";}else{$or="";} // se não é a última, então insere OR
				$query_venda_agenda_treinamento_treinamento_status .= sprintf(" agenda.status = '$venda_agenda_treinamento_status' $or");

		}
		$where_agenda_treinamento .= sprintf($query_venda_agenda_treinamento_treinamento_status)." ) ";
		
} else {

	$where .= " and venda.quantidade_agendado_treinamento = 0";
	$where_agenda_treinamento .= " and agenda.id_agenda IS NULL";

}
// fim - se existe filtro de venda_agenda_treinamento_status

// se existe filtro de venda_agenda_treinamento_data ( somente data final )
if( ((isset($_GET["venda_agenda_treinamento_data_fim"])) && ($_GET["venda_agenda_treinamento_data_fim"] != "")) && (@$_GET["venda_agenda_treinamento_data_inicio"] == "") ) {

		// converter data em portugues para ingles
		if ( isset($_GET["venda_agenda_treinamento_data_fim"]) ) {
			$venda_agenda_treinamento_data_fim_data = substr($_GET["venda_agenda_treinamento_data_fim"],0,10);
			$venda_agenda_treinamento_data_fim_hora = " 23:59:59";
			$venda_agenda_treinamento_data_fim = implode("-",array_reverse(explode("-",$venda_agenda_treinamento_data_fim_data))).$venda_agenda_treinamento_data_fim_hora;
			$where_agenda_treinamento_campos[] = "venda_agenda_treinamento_data_fim";
		}
		// converter data em portugues para ingles - fim
	
		$colname_venda_venda_agenda_treinamento_data_fim = GetSQLValueString($venda_agenda_treinamento_data_fim, "string");
		$where_agenda_treinamento .= " and agenda.data <= '".$colname_venda_venda_agenda_treinamento_data_fim."' ";
}
// fim - se existe filtro de venda_agenda_treinamento_data ( somente data final )

// se existe filtro de venda_agenda_treinamento_data ( somente data inicial )
if( ((isset($_GET["venda_agenda_treinamento_data_inicio"])) && ($_GET["venda_agenda_treinamento_data_inicio"] != "")) && (@$_GET["venda_agenda_treinamento_data_fim"] == "") ) {

		// converter data em portugues para ingles
		if ( isset($_GET["venda_agenda_treinamento_data_inicio"]) ) {
			$venda_agenda_treinamento_data_inicio_data = substr($_GET["venda_agenda_treinamento_data_inicio"],0,10);
			$venda_agenda_treinamento_data_inicio_hora = " 00:00:00";
			$venda_agenda_treinamento_data_inicio = implode("-",array_reverse(explode("-",$venda_agenda_treinamento_data_inicio_data))).$venda_agenda_treinamento_data_inicio_hora;
			$where_agenda_treinamento_campos[] = "venda_agenda_treinamento_data_inicio";
		}
		// converter data em portugues para ingles - fim
	
		$colname_venda_venda_agenda_treinamento_data_inicio = GetSQLValueString($venda_agenda_treinamento_data_inicio, "string");
		
		$where_agenda_treinamento .= " and agenda.data >= '".$colname_venda_venda_agenda_treinamento_data_inicio."' ";
}
// fim - se existe filtro de venda_agenda_treinamento_data ( somente data inicial )

// se existe filtro de venda_agenda_treinamento_data ( entre data inicial e data final )
if( ((isset($_GET["venda_agenda_treinamento_data_inicio"])) && ($_GET["venda_agenda_treinamento_data_inicio"] != "")) && ((isset($_GET["venda_agenda_treinamento_data_fim"])) && ($_GET["venda_agenda_treinamento_data_fim"] != "")) ) {

		// converter data em portugues para ingles
		if ( isset($_GET["venda_agenda_treinamento_data_inicio"]) ) {
			$venda_agenda_treinamento_data_inicio_data = substr($_GET["venda_agenda_treinamento_data_inicio"],0,10);
			$venda_agenda_treinamento_data_inicio_hora = " 00:00:00";
			$venda_agenda_treinamento_data_inicio = implode("-",array_reverse(explode("-",$venda_agenda_treinamento_data_inicio_data))).$venda_agenda_treinamento_data_inicio_hora;
			$where_agenda_treinamento_campos[] = "venda_agenda_treinamento_data_inicio";
		}
		// converter data em portugues para ingles - fim

		// converter data em portugues para ingles
		if ( isset($_GET["venda_agenda_treinamento_data_fim"]) ) {
			$venda_agenda_treinamento_data_fim_data = substr($_GET["venda_agenda_treinamento_data_fim"],0,10);
			$venda_agenda_treinamento_data_fim_hora = " 23:59:59";
			$venda_agenda_treinamento_data_fim = implode("-",array_reverse(explode("-",$venda_agenda_treinamento_data_fim_data))).$venda_agenda_treinamento_data_fim_hora;
			$where_agenda_treinamento_campos[] = "venda_agenda_treinamento_data_fim";
		}
		// converter data em portugues para ingles - fim
	
		$colname_venda_venda_agenda_treinamento_data_inicio = GetSQLValueString($venda_agenda_treinamento_data_inicio, "string");
		$colname_venda_venda_agenda_treinamento_data_fim = GetSQLValueString($venda_agenda_treinamento_data_fim, "string");

		$where_agenda_treinamento .= " and agenda.data between '$colname_venda_venda_agenda_treinamento_data_inicio' and '$colname_venda_venda_agenda_treinamento_data_fim' "; 
}
// fim - se existe filtro de venda_agenda_treinamento_data ( entre data inicial e data final )
// venda_agenda_treinamento - filtros ------------------------------------------------------------------------------------------------------------------------------

// venda_agenda_treinamento
$query_venda_agenda_treinamento = "
SELECT 
venda.id, venda.status, venda.situacao, venda.data_venda, venda.id_usuario_responsavel, 
venda.empresa, venda.quantidade_agendado_treinamento, venda.quantidade_agendado_implantacao, venda.status_flag, 
venda.id_prospeccao, venda.contrato, venda.praca, venda.data_inicio, venda.data_fim, venda.observacao, 
(SELECT nome FROM usuarios WHERE venda.id_usuario_responsavel = usuarios.IdUsuario) AS usuario_responsavel, 

agenda.data AS venda_agenda_treinamento_data, 
agenda.data_inicio AS venda_agenda_treinamento_data_inicio, 
agenda.descricao AS venda_agenda_treinamento_descricao, 
agenda.status AS venda_agenda_treinamento_status

FROM agenda 
LEFT JOIN venda ON agenda.id_venda_treinamento = venda.id
WHERE $where and agenda.id_venda_treinamento IS NOT NULL and $where_agenda_treinamento
ORDER BY venda.praca ASC, agenda.data ASC";

$venda_agenda_treinamento = mysql_query($query_venda_agenda_treinamento, $conexao) or die(mysql_error());
$row_venda_agenda_treinamento = mysql_fetch_assoc($venda_agenda_treinamento);
$totalRows_venda_agenda_treinamento = mysql_num_rows($venda_agenda_treinamento);
// fim - venda_agenda_treinamento

// venda_agenda_implantacao - filtros ------------------------------------------------------------------------------------------------------------------------------
// se existe filtro de venda_agenda_implantacao_status
$contador_venda_agenda_implantacao_status = 0;
$contador_venda_agenda_implantacao_status_atual = 0;
if( (isset($_GET["venda_agenda_implantacao_status"])) && ($_GET['venda_agenda_implantacao_status'] !="") ) {

		// contar quantidade de situacões atual
		foreach($_GET["venda_agenda_implantacao_status"] as $venda_agenda_implantacao_status){
				$contador_venda_agenda_implantacao_status = $contador_venda_agenda_implantacao_status + 1;
		}
		// fim - contar quantidade de situacões atual

		$query_venda_agenda_implantacao_implantacao_status=" and ( ";
		foreach($_GET["venda_agenda_implantacao_status"] as $venda_agenda_implantacao_status){
			
				$contador_venda_agenda_implantacao_status_atual = $contador_venda_agenda_implantacao_status_atual + 1; // verifica o contador atual
				$contador_total = $contador_venda_agenda_implantacao_status - $contador_venda_agenda_implantacao_status_atual; // calcula diferença de situações total - situação atual
				if($contador_total<>0){$or=" or ";}else{$or="";} // se não é a última, então insere OR
				$query_venda_agenda_implantacao_implantacao_status .= sprintf(" agenda.status = '$venda_agenda_implantacao_status' $or");

		}
		$where_agenda_implantacao .= sprintf($query_venda_agenda_implantacao_implantacao_status)." ) ";
		
} else {

	$where .= " and venda.quantidade_agendado_implantacao = 0";
	$where_agenda_implantacao .= " and agenda.id_agenda IS NULL";

}
// fim - se existe filtro de venda_agenda_implantacao_status

// se existe filtro de venda_agenda_implantacao_data ( somente data final )
if( ((isset($_GET["venda_agenda_implantacao_data_fim"])) && ($_GET["venda_agenda_implantacao_data_fim"] != "")) && (@$_GET["venda_agenda_implantacao_data_inicio"] == "") ) {

		// converter data em portugues para ingles
		if ( isset($_GET["venda_agenda_implantacao_data_fim"]) ) {
			$venda_agenda_implantacao_data_fim_data = substr($_GET["venda_agenda_implantacao_data_fim"],0,10);
			$venda_agenda_implantacao_data_fim_hora = " 23:59:59";
			$venda_agenda_implantacao_data_fim = implode("-",array_reverse(explode("-",$venda_agenda_implantacao_data_fim_data))).$venda_agenda_implantacao_data_fim_hora;
			$where_agenda_implantacao_campos[] = "venda_agenda_implantacao_data_fim";
		}
		// converter data em portugues para ingles - fim
	
		$colname_venda_venda_agenda_implantacao_data_fim = GetSQLValueString($venda_agenda_implantacao_data_fim, "string");
		$where_agenda_implantacao .= " and agenda.data <= '".$colname_venda_venda_agenda_implantacao_data_fim."' ";
}
// fim - se existe filtro de venda_agenda_implantacao_data ( somente data final )

// se existe filtro de venda_agenda_implantacao_data ( somente data inicial )
if( ((isset($_GET["venda_agenda_implantacao_data_inicio"])) && ($_GET["venda_agenda_implantacao_data_inicio"] != "")) && (@$_GET["venda_agenda_implantacao_data_fim"] == "") ) {

		// converter data em portugues para ingles
		if ( isset($_GET["venda_agenda_implantacao_data_inicio"]) ) {
			$venda_agenda_implantacao_data_inicio_data = substr($_GET["venda_agenda_implantacao_data_inicio"],0,10);
			$venda_agenda_implantacao_data_inicio_hora = " 00:00:00";
			$venda_agenda_implantacao_data_inicio = implode("-",array_reverse(explode("-",$venda_agenda_implantacao_data_inicio_data))).$venda_agenda_implantacao_data_inicio_hora;
			$where_agenda_implantacao_campos[] = "venda_agenda_implantacao_data_inicio";
		}
		// converter data em portugues para ingles - fim
	
		$colname_venda_venda_agenda_implantacao_data_inicio = GetSQLValueString($venda_agenda_implantacao_data_inicio, "string");
		
		$where_agenda_implantacao .= " and agenda.data >= '".$colname_venda_venda_agenda_implantacao_data_inicio."' ";
}
// fim - se existe filtro de venda_agenda_implantacao_data ( somente data inicial )

// se existe filtro de venda_agenda_implantacao_data ( entre data inicial e data final )
if( ((isset($_GET["venda_agenda_implantacao_data_inicio"])) && ($_GET["venda_agenda_implantacao_data_inicio"] != "")) && ((isset($_GET["venda_agenda_implantacao_data_fim"])) && ($_GET["venda_agenda_implantacao_data_fim"] != "")) ) {

		// converter data em portugues para ingles
		if ( isset($_GET["venda_agenda_implantacao_data_inicio"]) ) {
			$venda_agenda_implantacao_data_inicio_data = substr($_GET["venda_agenda_implantacao_data_inicio"],0,10);
			$venda_agenda_implantacao_data_inicio_hora = " 00:00:00";
			$venda_agenda_implantacao_data_inicio = implode("-",array_reverse(explode("-",$venda_agenda_implantacao_data_inicio_data))).$venda_agenda_implantacao_data_inicio_hora;
			$where_agenda_implantacao_campos[] = "venda_agenda_implantacao_data_inicio";
		}
		// converter data em portugues para ingles - fim

		// converter data em portugues para ingles
		if ( isset($_GET["venda_agenda_implantacao_data_fim"]) ) {
			$venda_agenda_implantacao_data_fim_data = substr($_GET["venda_agenda_implantacao_data_fim"],0,10);
			$venda_agenda_implantacao_data_fim_hora = " 23:59:59";
			$venda_agenda_implantacao_data_fim = implode("-",array_reverse(explode("-",$venda_agenda_implantacao_data_fim_data))).$venda_agenda_implantacao_data_fim_hora;
			$where_agenda_implantacao_campos[] = "venda_agenda_implantacao_data_fim";
		}
		// converter data em portugues para ingles - fim
	
		$colname_venda_venda_agenda_implantacao_data_inicio = GetSQLValueString($venda_agenda_implantacao_data_inicio, "string");
		$colname_venda_venda_agenda_implantacao_data_fim = GetSQLValueString($venda_agenda_implantacao_data_fim, "string");

		$where_agenda_implantacao .= " and agenda.data between '$colname_venda_venda_agenda_implantacao_data_inicio' and '$colname_venda_venda_agenda_implantacao_data_fim' "; 
}
// fim - se existe filtro de venda_agenda_implantacao_data ( entre data inicial e data final )
// venda_agenda_implantacao - filtros ------------------------------------------------------------------------------------------------------------------------------

// venda_agenda_implantacao
$query_venda_agenda_implantacao = "
SELECT 
venda.id, venda.status, venda.situacao, venda.data_venda, venda.id_usuario_responsavel, 
venda.empresa, venda.quantidade_agendado_treinamento, venda.quantidade_agendado_implantacao, venda.status_flag, 
venda.id_prospeccao, venda.contrato, venda.praca, venda.data_inicio, venda.data_fim, venda.observacao, 
(SELECT nome FROM usuarios WHERE venda.id_usuario_responsavel = usuarios.IdUsuario) AS usuario_responsavel, 

agenda.data AS venda_agenda_implantacao_data, 
agenda.data_inicio AS venda_agenda_implantacao_data_inicio, 
agenda.descricao AS venda_agenda_implantacao_descricao, 
agenda.status AS venda_agenda_implantacao_status

FROM agenda 
LEFT JOIN venda ON agenda.id_venda_implantacao = venda.id
WHERE $where and agenda.id_venda_implantacao IS NOT NULL and $where_agenda_implantacao
ORDER BY venda.praca ASC, agenda.data ASC";

$venda_agenda_implantacao = mysql_query($query_venda_agenda_implantacao, $conexao) or die(mysql_error());
$row_venda_agenda_implantacao = mysql_fetch_assoc($venda_agenda_implantacao);
$totalRows_venda_agenda_implantacao = mysql_num_rows($venda_agenda_implantacao);
// fim - venda_agenda_implantacao
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
	padding: 1px; !important;
}
.cor_red {
	color: #FF0000; !important;
	padding: 1px; !important;
}
.cor_blue {
	color: blue; !important;
	padding: 1px; !important;
}
.cor_green {
	color: green; !important;
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
	$('#data_venda_inicio').mask('99-99-9999',{placeholder:" "});
	$('#data_venda_fim').mask('99-99-9999',{placeholder:" "});
	
	$('#venda_agenda_treinamento_data_inicio').mask('99-99-9999',{placeholder:" "});
	$('#venda_agenda_treinamento_data_fim').mask('99-99-9999',{placeholder:" "});
	
	$('#venda_agenda_implantacao_data_inicio').mask('99-99-9999',{placeholder:" "});
	$('#venda_agenda_implantacao_data_fim').mask('99-99-9999',{placeholder:" "});
	// mascara - fim
	
	// ocultar/exibir filtros
	$('#corpo_venda_filtro').toggle();
	$('#cabecalho_venda_filtro').click(function() {
		$('#corpo_venda_filtro').toggle();
	});
	// fim - ocultar/exibir fitlros
		
	// ocultar/exibir vendas
	//$('#corpo_vendas').toggle();
	$('#cabecalho_venda').click(function() {
		$('#corpo_venda').toggle();
	});
	// fim - ocultar/exibir vendas

	// ocultar/exibir venda_agenda_treinamento
	$('#corpo_venda_agenda_treinamento').toggle();
	$('#cabecalho_venda_agenda_treinamento').click(function() {
		$('#corpo_venda_agenda_treinamento').toggle();
	});
	// fim - ocultar/exibir venda_agenda_treinamento
	
	// ocultar/exibir venda_agenda_implantacao
	$('#corpo_venda_agenda_implantacao').toggle();
	$('#cabecalho_venda_agenda_implantacao').click(function() {
		$('#corpo_venda_agenda_implantacao').toggle();
	});
	// fim - ocultar/exibir venda_agenda_implantacao

	// marcar todos
	$('#checkall_situacao').click(function () {
	$(this).parents('fieldset:eq(0)').find(':checkbox').attr('checked', this.checked);
	});
	
	$('#checkall_status').click(function () {
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
<title>Venda</title>
</head>

<body>
<? // echo $where; echo "<br><br><br>"; echo $where_agenda_treinamento; echo "<br><br><br>"; echo $where_agenda_implantacao; ?>

<!-- barra superior -->
<div class="div_solicitacao_linhas">
<table cellspacing="0" cellpadding="0" width="100%">
	<tr>
		<td style="text-align:left">
		Controle de vendas
        <font color="#3399CC"> | </font>
        <a href="prospeccao.php?padrao=sim&<? echo $prospeccao_padrao; ?>" style="color: #D1E3F1">Controle de prospecção</a>  
		<font color="#3399CC"> | </font>
        <a href="solicitacao.php?padrao=sim&<? echo $situacao_padrao; ?>" style="color: #D1E3F1">Controle de solicitação</a>
        <font color="#3399CC"> | </font>
        <a href="suporte.php?padrao=sim&<? echo $suporte_padrao; ?>" style="color: #D1E3F1">Controle de suporte</a>     
		</td>

		<td style="text-align: right">
		&lt;&lt; <a href="index.php">Voltar</a> | 
		Usuário logado: <? echo $row_usuario['nome']; ?> (<? echo $row_usuario['nivel_venda']; ?>) |
		<a href="painel/padrao_sair.php">Sair</a>
        </td>
	</tr>
</table>
</div>
<!-- fim - barra superior -->

<div class="div_solicitacao_linhas2">
Clique sobre a opção desejada para visualizar mais informações.
</div>

<!-- filtros -->
<div class="div_solicitacao_linhas" id="cabecalho_venda_filtro" style="cursor: pointer">
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

<form name="buscar" action="venda.php" method="GET">
<div id="corpo_venda_filtro">

	<!-- filtros da venda -->
	<div style="border: 1px solid #c5dbec; margin-bottom: 5px;">
    
        <div class="div_filtros2">
        <table cellspacing="0" cellpadding="0" width="100%">
            <tr>
                <td style="text-align:left" width="560px" >
                <span class="label_solicitacao">Cliente:</span>
                <input name="empresa" type="text" id="empresa" value="<? if ( isset($_GET['empresa']) ) { echo $_GET['empresa']; } ?>" style="width: 500px" /> 
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
            </tr>
        </table>
        </div>

        <div class="div_filtros">
        <table cellspacing="0" cellpadding="0" width="100%">
            <tr>
                <td style="text-align:left" width="560px" >
                <span class="label_solicitacao">Solicitante: </span>
				<input name="solicitante" type="text" id="solicitante" value="<? if ( isset($_GET['solicitante']) ) { echo $_GET['solicitante']; } ?>" style="width: 470px" /> 
                </td>
                
              	<td style="text-align:right">
                <span class="label_solicitacao">Responsável: </span>
                <select name="id_usuario_responsavel" style="width: 380px">
                <option value=""
                <?php if (!(strcmp("", isset($_GET['filtro_id_usuario_responsavel'])))) {echo "selected=\"selected\"";} ?>>Escolha ...</option>
                <?php do {  ?>
                <option value="<?php echo $row_filtro_id_usuario_responsavel['IdUsuario']; ?>"
                <?php if ( (isset($_GET['id_usuario_responsavel'])) and (!(strcmp($row_filtro_id_usuario_responsavel['IdUsuario'], $_GET['id_usuario_responsavel']))) ) {echo "selected=\"selected\"";} ?>
                >
                <?php echo utf8_encode($row_filtro_id_usuario_responsavel['nome']); ?>
                </option>
                <?php
                } while ($row_filtro_id_usuario_responsavel = mysql_fetch_assoc($filtro_id_usuario_responsavel));
                $rows = mysql_num_rows($filtro_id_usuario_responsavel);
                if($rows > 0) {
                mysql_data_seek($filtro_id_usuario_responsavel, 0);
                $row_filtro_id_usuario_responsavel = mysql_fetch_assoc($filtro_id_usuario_responsavel);
                }
                ?>
                </select>
                </td>
          </tr>
        </table>
        </div>
                
        <div class="div_filtros2">
        <table cellspacing="0" cellpadding="0" width="100%">
        <tr>
        <td style="text-align:left">
        <span class="label_solicitacao">Data criação (inicial): </span>
        <input name="data_venda_inicio" id="data_venda_inicio" type="text" value="<? 
        if ( isset($_GET['data_venda_inicio']) ){ echo $_GET['data_venda_inicio']; }
        ?>" />
        </td>
        
        <td style="text-align:right" class="div_filtros_vendas_corpo_td">
        <span class="label_solicitacao">Data criação (final): </span>
        <input name="data_venda_fim" id="data_venda_fim" type="text" value="<? 
        if ( isset($_GET['data_venda_fim']) ){ echo $_GET['data_venda_fim']; }
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

                <input name="situacao[]" type="checkbox" value="documentação pendente" 
                    <?
                    // verificar se foi selecionada
                    if(isset($_GET['situacao'])){
                        foreach($_GET["situacao"] as $situacao){
                            if($situacao=="documentação pendente"){
                                echo "checked=\"checked\"";
                            }
                        }
                    }
                    // verificar se foi selecionada
                    ?>
                />documentação pendente
    
                <input name="situacao[]" type="checkbox" value="em execução" 
                    <?
                    // verificar se foi selecionada
                    if(isset($_GET['situacao'])){
                        foreach($_GET["situacao"] as $situacao){
                            if($situacao=="em execução"){
                                echo "checked=\"checked\"";
                            }
                        }
                    }
                    // verificar se foi selecionada
                    ?>
                />em execução
                                
                <input name="situacao[]" type="checkbox" value="solucionada" 
                    <?
                    // verificar se foi selecionada
                    if(isset($_GET['situacao'])){
                        foreach($_GET["situacao"] as $situacao){
                            if($situacao=="solucionada"){
                                echo "checked=\"checked\"";
                            }
                        }
                    }
                    // verificar se foi selecionada
                    ?>
                />solucionada
                
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
        
        <div class="div_filtros2">
        <table cellspacing="0" cellpadding="0" width="100%">
            <tr>
                <td style="text-align:left" valign="top">
                <fieldset>
                    <span class="label_solicitacao">Status:</span>
                    <table width="100%" border="0" cellspacing="0" cellpadding="0">
                    <tr>
                        <td width="250" valign="top">

                        
                        <input  name="status[]" type="checkbox" class="checkbox" value="pendente operador"
                            <?
                            // verificar se foi selecionada
                            if(isset($_GET['status'])){
                                foreach($_GET["status"] as $status){
                                    if($status=="pendente operador"){
                                        echo "checked=\"checked\"";
                                    }
                                }
                            }
                            // verificar se foi selecionada
                            ?>
                        />pendente operador
                        
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
                        
                        <input  name="status[]" type="checkbox" class="checkbox" value="pendente controlador de venda"
                            <?
                            // verificar se foi selecionada
                            if(isset($_GET['status'])){
                                foreach($_GET["status"] as $status){
                                    if($status=="pendente controlador de venda"){
                                        echo "checked=\"checked\"";
                                    }
                                }
                            }
                            // verificar se foi selecionada
                            ?>
                        />pendente controlador de venda
                        
                        </td>
                        
                        <td valign="top">
                        
                        <input  name="status[]" type="checkbox" class="checkbox" value="encaminhada para operador"
                        <?
                        // verificar se foi selecionada
                        if(isset($_GET['status'])){
                            foreach($_GET["status"] as $status){
                                if($status=="encaminhada para operador"){
                                    echo "checked=\"checked\"";
                                }
                            }
                        }
                        // verificar se foi selecionada
                        ?>
                    />encaminhada para operador
                    
                    <br>                        
                    
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
                        
                        <input type="checkbox" class="checkbox" id="checkall_status"  name="checkall_status" />Marcar todos
                        
                        </td>
                    </tr>
                    </table>
				</fieldset>
                </td>
                
                <td style="text-align: right" valign="top">&nbsp;</td>
                
          </tr>
        </table>
        </div>

	</div>
	<!-- fim - filtros da venda -->


    <!-- filtros da venda_agenda_treinamento -->
    <div class="div_solicitacao_linhas">
    <table cellspacing="0" cellpadding="0" width="100%">
        <tr>
            <td style="text-align:left">
            Filtros - Agenda de Treinamento
            </td>
        </tr>
    </table>
    </div>
    
    <div style="border: 1px solid #c5dbec; margin-bottom: 5px;">
   
        <div class="div_filtros">
        <table cellspacing="0" cellpadding="0" width="100%">
        <tr>
        <td style="text-align:left">
        <span class="label_solicitacao">Data (inicial): </span>
        <input name="venda_agenda_treinamento_data_inicio" id="venda_agenda_treinamento_data_inicio" type="text" value="<? 
        if ( isset($_GET['venda_agenda_treinamento_data_inicio']) ){ echo $_GET['venda_agenda_treinamento_data_inicio']; }
        ?>" />
        </td>
        
        <td style="text-align:right" class="div_filtros_vendas_corpo_td">
        <span class="label_solicitacao">Data (final): </span>
        <input name="venda_agenda_treinamento_data_fim" id="venda_agenda_treinamento_data_fim" type="text" value="<? 
        if ( isset($_GET['venda_agenda_treinamento_data_fim']) ){ echo $_GET['venda_agenda_treinamento_data_fim']; }
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
                        
                        <input  name="venda_agenda_treinamento_status[]" type="checkbox" class="checkbox" value="a"
                            <?
                            // verificar se foi selecionada
                            if(isset($_GET['venda_agenda_treinamento_status'])){
                                foreach($_GET["venda_agenda_treinamento_status"] as $venda_agenda_treinamento_status){
                                    if($venda_agenda_treinamento_status=="a"){
                                        echo "checked=\"checked\"";
                                    }
                                }
                            }
                            // verificar se foi selecionada
                            ?>
                        />Agendado
                                                
                        <input  name="venda_agenda_treinamento_status[]" type="checkbox" class="checkbox" value="f"
                            <?
                            // verificar se foi selecionada
                            if(isset($_GET['venda_agenda_treinamento_status'])){
                                foreach($_GET["venda_agenda_treinamento_status"] as $venda_agenda_treinamento_status){
                                    if($venda_agenda_treinamento_status=="f"){
                                        echo "checked=\"checked\"";
                                    }
                                }
                            }
                            // verificar se foi selecionada
                            ?>
                        />Finalizado
                        
                        <input  name="venda_agenda_treinamento_status[]" type="checkbox" class="checkbox" value="c"
                            <?
                            // verificar se foi selecionada
                            if(isset($_GET['venda_agenda_treinamento_status'])){
                                foreach($_GET["venda_agenda_treinamento_status"] as $venda_agenda_treinamento_status){
                                    if($venda_agenda_treinamento_status=="c"){
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
                
                <td style="text-align: right" valign="top">&nbsp;</td>
                
          </tr>
        </table>
        </div>

	</div>
	<!-- fim - filtros da venda_agenda_treinamento -->


    <!-- filtros da venda_agenda_implantacao -->
    <div class="div_solicitacao_linhas">
    <table cellspacing="0" cellpadding="0" width="100%">
        <tr>
            <td style="text-align:left">
            Filtros - Agenda de Implantação
            </td>
        </tr>
    </table>
    </div>
    
    <div style="border: 1px solid #c5dbec; margin-bottom: 5px;">
   
        <div class="div_filtros">
        <table cellspacing="0" cellpadding="0" width="100%">
        <tr>
        <td style="text-align:left">
        <span class="label_solicitacao">Data (inicial): </span>
        <input name="venda_agenda_implantacao_data_inicio" id="venda_agenda_implantacao_data_inicio" type="text" value="<? 
        if ( isset($_GET['venda_agenda_implantacao_data_inicio']) ){ echo $_GET['venda_agenda_implantacao_data_inicio']; }
        ?>" />
        </td>
        
        <td style="text-align:right" class="div_filtros_vendas_corpo_td">
        <span class="label_solicitacao">Data (final): </span>
        <input name="venda_agenda_implantacao_data_fim" id="venda_agenda_implantacao_data_fim" type="text" value="<? 
        if ( isset($_GET['venda_agenda_implantacao_data_fim']) ){ echo $_GET['venda_agenda_implantacao_data_fim']; }
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
                        
                        <input  name="venda_agenda_implantacao_status[]" type="checkbox" class="checkbox" value="a"
                            <?
                            // verificar se foi selecionada
                            if(isset($_GET['venda_agenda_implantacao_status'])){
                                foreach($_GET["venda_agenda_implantacao_status"] as $venda_agenda_implantacao_status){
                                    if($venda_agenda_implantacao_status=="a"){
                                        echo "checked=\"checked\"";
                                    }
                                }
                            }
                            // verificar se foi selecionada
                            ?>
                        />Agendado
                                                
                        <input  name="venda_agenda_implantacao_status[]" type="checkbox" class="checkbox" value="f"
                            <?
                            // verificar se foi selecionada
                            if(isset($_GET['venda_agenda_implantacao_status'])){
                                foreach($_GET["venda_agenda_implantacao_status"] as $venda_agenda_implantacao_status){
                                    if($venda_agenda_implantacao_status=="f"){
                                        echo "checked=\"checked\"";
                                    }
                                }
                            }
                            // verificar se foi selecionada
                            ?>
                        />Finalizado
                        
                        <input  name="venda_agenda_implantacao_status[]" type="checkbox" class="checkbox" value="c"
                            <?
                            // verificar se foi selecionada
                            if(isset($_GET['venda_agenda_implantacao_status'])){
                                foreach($_GET["venda_agenda_implantacao_status"] as $venda_agenda_implantacao_status){
                                    if($venda_agenda_implantacao_status=="c"){
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
                
                <td style="text-align: right" valign="top">&nbsp;</td>
                
          </tr>
        </table>
        </div>

	</div>
	<!-- fim - filtros da venda_agenda_implantacao -->
    

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


<!-- venda -->
<? if($totalRows_venda > 0){ ?>
<div class="div_solicitacao_linhas" id="cabecalho_venda" style="cursor: pointer">
<table cellspacing="0" cellpadding="0" width="100%">
	<tr>
		<td style="text-align:left">
		Vendas (<? echo $totalRows_venda; ?>)
		</td>
        
		<td style="text-align: right">
		<div style="float: right; background-image:url(imagens/icone_filtro.png); width: 12px; height: 12px;"></div>
		</td>
	</tr>
</table>
</div>

<div id="corpo_venda" style="cursor: pointer">
<table id="venda"></table>
<div id="venda_navegacao"></div>
<script type="text/javascript">
var dados = [		 
<?php do { ?>

<?
// status ------------------------------------------------------------------------------------------------------------------------------------
$cor_css = "cor_black";

if($row_venda['status']=='aguardando agendamento' and $row_venda['status_flag']=='a'){
	$cor_css = "cor_red";
}
// fim - status ------------------------------------------------------------------------------------------------------------------------------
?>

{
	id:"<?php echo $row_venda['id']; ?>",
	contrato:"<?php echo $row_venda['contrato']; ?>",
	empresa:"<?php echo $row_venda['empresa']; ?>",
	<? if($row_usuario['controle_venda'] == "Y"){ ?>
		praca:"<?php echo $row_venda['praca']; ?>",
	<? } ?>
	usuario_responsavel:"<?php echo $row_venda['usuario_responsavel']; ?>",
	status:"<? echo "<div class='$cor_css'>"; ?><?php 

    if($row_venda['status']==""){echo "&nbsp;";}

    if($row_venda['status']=="pendente usuario responsavel"){echo "<span title='pendente usuario responsavel'>PenRes</span>";}
    if($row_venda['status']=="encaminhada para usuario responsavel"){echo "<span title='encaminhada para usuario responsavel'>EncRes</span>";}    
    if($row_venda['status']=="devolvida para usuario responsavel"){echo "<span title='devolvida para usuario responsavel'>DevRes</span>";}

    if($row_venda['status']=="aguardando retorno do cliente"){echo "<span title='aguardando retorno do cliente'>AguCli</span>";}
	if($row_venda['status']=="aguardando atendente"){echo "<span title='aguardando atendente'>AguAte</span>";}
	if($row_venda['status']=="aguardando agendamento"){echo "<span title='aguardando agendamento'>AguAge</span>";}

    ?><? echo "</div>"; ?>",
	situacao:"<?php echo $row_venda['situacao']; ?>",
	data_venda:"<?php echo $row_venda['data_venda']; ?>",
	visualizar:"<? echo "<a href='venda_editar.php?id_venda=".$row_venda['id']."&padrao=sim'><img src='imagens/visualizar.png' border='0' /></a>"; ?>"
},
<?php } while ($row_venda = mysql_fetch_assoc($venda)); ?>
];
jQuery('#venda').jqGrid({
	data:dados,
	datatype: 'local',
	colNames:['Núm','Contrato','Cliente',<? if($row_usuario['controle_venda'] == "Y"){ ?>'Praça',<? } ?>'Responsável','Status','Situação','Data criação',''],
	colModel :[ 
		{name:'id', index:'id', width:30, sorttype: 'integer'}, 
		{name:'contrato', index:'contrato', width:30, sorttype: 'integer'}, 
		{name:'empresa', index:'empresa'}, 
		<? if($row_usuario['controle_venda'] == "Y"){ ?>
		{name:'praca', index:'praca', width: 80, align:'center'},
		<? } ?>
		{name:'usuario_responsavel', index:'usuario_responsavel', width:70, align:'left'}, 
		{name:'status', index:'status', width: 80, align:'center'},
		{name:'situacao', index:'situacao', width: 80, align:'center'},
		{name:'data_venda', index:'data_venda', width:70, formatter:'date', formatoptions: { srcformat: "ISO8601Long", newformat: "d-m-Y H:i" }, align:'center' },
		{name:'visualizar', index:'visualizar', width:20, align:'center'} 
	],
	rowNum:20,
	rowList:[2,5,10,20,30,40,50,100,999999],
				loadComplete: function() {
					$("option[value=999999]").text('Todos');
				},
	pager: '#venda_navegacao',
	//sortname: 'id',
	viewrecords: true,
	//sortorder: 'desc',
	toppager: true, // aparecer a barra de navegação também no topo
	// caption:"Suportes", barra no topo
	autowidth: true,
	height: "100%",

	ondblClickRow: function(id){
		top.location.href="venda_editar.php?id_venda="+id+"&padrao=sim";
	}		
});
</script>
</div>
<? } else { ?>
<div class="div_solicitacao_linhas4">
Nenhuma venda encontrada na filtragem atual.

</div>
<? } ?>
<!-- fim - venda -->


<!-- venda_agenda_treinamento -->
<? if($totalRows_venda_agenda_treinamento > 0){ ?>
<div class="div_solicitacao_linhas" id="cabecalho_venda_agenda_treinamento" style="cursor: pointer; margin-top: 5px;">
<table cellspacing="0" cellpadding="0" width="100%">
	<tr>
		<td style="text-align:left">
		Agenda de Vendas de Treinamento (<? echo $totalRows_venda_agenda_treinamento; ?>)
		</td>
        
		<td style="text-align: right">
		<div style="float: right; background-image:url(imagens/icone_filtro.png); width: 12px; height: 12px;"></div>
		</td>
	</tr>
</table>
</div>

<div id="corpo_venda_agenda_treinamento" style="cursor: pointer">
<table id="venda_agenda_treinamento"></table>
<div id="venda_agenda_treinamento_navegacao"></div>
<script type="text/javascript">
var dados = [		 
<?php do { ?>

<?
// status ------------------------------------------------------------------------------------------------------------------------------------
$cor_css = "cor_black";

// geral
if($row_venda_agenda_treinamento['venda_agenda_treinamento_data_inicio']!="" and $row_venda_agenda_treinamento['venda_agenda_treinamento_data_inicio']!="0000-00-00 00:00:00"){

	$previsao_geral = funcaoAcrescentaDiasNaoUteis($row_venda_agenda_treinamento['venda_agenda_treinamento_data_inicio']); // chama a função que altera a data para o próximo dia útil
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
	
	if($row_venda_agenda_treinamento['status']=='aguardando agendamento' and $row_venda_agenda_treinamento['status_flag']=='a'){
		$cor_css = "cor_red";
	}
	
}
// fim - geral
// fim - status ------------------------------------------------------------------------------------------------------------------------------
?>

{
	id:"<?php echo $row_venda_agenda_treinamento['id']; ?>",
	contrato:"<?php echo $row_venda_agenda_treinamento['contrato']; ?>",
	empresa:"<?php echo $row_venda_agenda_treinamento['empresa']; ?>",
	<? if($row_usuario['controle_venda'] == "Y"){ ?>
		praca:"<?php echo $row_venda_agenda_treinamento['praca']; ?>",
	<? } ?>
	usuario_responsavel:"<?php echo $row_venda_agenda_treinamento['usuario_responsavel']; ?>",
	status:"<? echo "<div class='$cor_css'>"; ?><?php 

    if($row_venda_agenda_treinamento['status']==""){echo "&nbsp;";}

    if($row_venda_agenda_treinamento['status']=="pendente usuario responsavel"){echo "<span title='pendente usuario responsavel'>PenRes</span>";}
    if($row_venda_agenda_treinamento['status']=="encaminhada para usuario responsavel"){echo "<span title='encaminhada para usuario responsavel'>EncRes</span>";}    
    if($row_venda_agenda_treinamento['status']=="devolvida para usuario responsavel"){echo "<span title='devolvida para usuario responsavel'>DevRes</span>";}

    if($row_venda_agenda_treinamento['status']=="aguardando retorno do cliente"){echo "<span title='aguardando retorno do cliente'>AguCli</span>";}
	if($row_venda_agenda_treinamento['status']=="aguardando atendente"){echo "<span title='aguardando atendente'>AguAte</span>";}
	if($row_venda_agenda_treinamento['status']=="aguardando agendamento"){echo "<span title='aguardando agendamento'>AguAge</span>";}

    ?><? echo "</div>"; ?>",
	situacao:"<?php echo $row_venda_agenda_treinamento['situacao']; ?>",
	data_venda:"<?php echo $row_venda_agenda_treinamento['data_venda']; ?>",
	venda_agenda_treinamento_data_inicio:"<?php echo $row_venda_agenda_treinamento['venda_agenda_treinamento_data_inicio']; ?>",
	venda_agenda_treinamento_descricao:"<?php echo GetSQLValueString($row_venda_agenda_treinamento['venda_agenda_treinamento_descricao'], "string"); ?>",
	venda_agenda_treinamento_status:"<?php 
	if($row_venda_agenda_treinamento['venda_agenda_treinamento_status']=="a"){echo "Agendado";}
	if($row_venda_agenda_treinamento['venda_agenda_treinamento_status']=="f"){echo "Finalizado";}
	if($row_venda_agenda_treinamento['venda_agenda_treinamento_status']=="c"){echo "Cancelado";}
	?>",
	visualizar:"<? echo "<a href='venda_editar.php?id_venda=".$row_venda_agenda_treinamento['id']."&padrao=sim'><img src='imagens/visualizar.png' border='0' /></a>"; ?>"
},
<?php } while ($row_venda_agenda_treinamento = mysql_fetch_assoc($venda_agenda_treinamento)); ?>
];
jQuery('#venda_agenda_treinamento').jqGrid({
	data:dados,
	datatype: 'local',
	colNames:['Núm','Contrato','Cliente',<? if($row_usuario['controle_venda'] == "Y"){ ?>'Praça',<? } ?>'Responsável','Status','Situação','Data criação','Agenda (data)','Agenda (descrição)','Agenda (status)',''],
	colModel :[ 
		{name:'id', index:'id', width:30, sorttype: 'integer'}, 
		{name:'contrato', index:'contrato', width:30, sorttype: 'integer'}, 
		{name:'empresa', index:'empresa'}, 
		<? if($row_usuario['controle_venda'] == "Y"){ ?>
		{name:'praca', index:'praca', width: 80, align:'center'},
		<? } ?>
		{name:'usuario_responsavel', index:'usuario_responsavel', width:70, align:'left'}, 
		{name:'status', index:'status', width: 80, align:'center'},
		{name:'situacao', index:'situacao', width: 80, align:'center'},
		{name:'data_venda', index:'data_venda', width:70, formatter:'date', formatoptions: { srcformat: "ISO8601Long", newformat: "d-m-Y H:i" }, align:'center' },
		{name:'venda_agenda_treinamento_data_inicio', index:'venda_agenda_treinamento_data_inicio', width:70, formatter:'date', formatoptions: { srcformat: "ISO8601Long", newformat: "d-m-Y H:i" }, align:'center' },
		{name:'venda_agenda_treinamento_descricao', index:'venda_agenda_treinamento_descricao', width: 140, align:'left'},
		{name:'venda_agenda_treinamento_status', index:'venda_agenda_treinamento_status', width: 80, align:'center'},
		{name:'visualizar', index:'visualizar', width:20, align:'center'} 
	],
	rowNum:20,
	rowList:[2,5,10,20,30,40,50,100,999999],
				loadComplete: function() {
					$("option[value=999999]").text('Todos');
				},
	pager: '#venda_agenda_treinamento_navegacao',
	//sortname: 'id',
	viewrecords: true,
	//sortorder: 'desc',
	toppager: true, // aparecer a barra de navegação também no topo
	// caption:"Suportes", barra no topo
	autowidth: true,
	height: "100%",

	ondblClickRow: function(id){
		top.location.href="venda_editar.php?id_venda="+id+"&padrao=sim";
	}		
});
</script>
</div>
<? } else { ?>
<div class="div_solicitacao_linhas" id="cabecalho_venda_agenda_treinamento" style="cursor: pointer; margin-top: 5px;">
<table cellspacing="0" cellpadding="0" width="100%">
	<tr>
		<td style="text-align:left">
		Agenda de Vendas de Treinamento (<? echo $totalRows_venda_agenda_treinamento; ?>)
		</td>
        
		<td style="text-align: right">
		<div style="float: right; background-image:url(imagens/icone_filtro.png); width: 12px; height: 12px;"></div>
		</td>
	</tr>
</table>
</div>

<div id="corpo_venda_agenda_treinamento" style="cursor: pointer">
<table id="venda_agenda_treinamento"></table>
<div id="venda_agenda_treinamento_navegacao"></div>
Nenhuma venda encontrada na filtragem atual.
</div>
<? } ?>
<!-- fim - venda_agenda_treinamento -->


<!-- venda_agenda_implantacao -->
<? if($totalRows_venda_agenda_implantacao > 0){ ?>
<div class="div_solicitacao_linhas" id="cabecalho_venda_agenda_implantacao" style="cursor: pointer; margin-top: 5px;">
<table cellspacing="0" cellpadding="0" width="100%">
	<tr>
		<td style="text-align:left">
		Agenda de Vendas de Implantação (<? echo $totalRows_venda_agenda_implantacao; ?>)
		</td>
        
		<td style="text-align: right">
		<div style="float: right; background-image:url(imagens/icone_filtro.png); width: 12px; height: 12px;"></div>
		</td>
	</tr>
</table>
</div>

<div id="corpo_venda_agenda_implantacao" style="cursor: pointer">
<table id="venda_agenda_implantacao"></table>
<div id="venda_agenda_implantacao_navegacao"></div>
<script type="text/javascript">
var dados = [		 
<?php do { ?>

<?
// status ------------------------------------------------------------------------------------------------------------------------------------
$cor_css = "cor_black";

// geral
if($row_venda_agenda_implantacao['venda_agenda_implantacao_data_inicio']!="" and $row_venda_agenda_implantacao['venda_agenda_implantacao_data_inicio']!="0000-00-00 00:00:00"){

	$previsao_geral = funcaoAcrescentaDiasNaoUteis($row_venda_agenda_implantacao['venda_agenda_implantacao_data_inicio']); // chama a função que altera a data para o próximo dia útil
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
	
	if($row_venda_agenda_implantacao['status']=='aguardando agendamento' and $row_venda_agenda_implantacao['status_flag']=='a'){
		$cor_css = "cor_red";
	}
	
}
// fim - geral
// fim - status ------------------------------------------------------------------------------------------------------------------------------
?>

{
	id:"<?php echo $row_venda_agenda_implantacao['id']; ?>",
	contrato:"<?php echo $row_venda_agenda_implantacao['contrato']; ?>",
	empresa:"<?php echo $row_venda_agenda_implantacao['empresa']; ?>",
	<? if($row_usuario['controle_venda'] == "Y"){ ?>
		praca:"<?php echo $row_venda_agenda_implantacao['praca']; ?>",
	<? } ?>
	usuario_responsavel:"<?php echo $row_venda_agenda_implantacao['usuario_responsavel']; ?>",
	status:"<? echo "<div class='$cor_css'>"; ?><?php 

    if($row_venda_agenda_implantacao['status']==""){echo "&nbsp;";}

    if($row_venda_agenda_implantacao['status']=="pendente usuario responsavel"){echo "<span title='pendente usuario responsavel'>PenRes</span>";}
    if($row_venda_agenda_implantacao['status']=="encaminhada para usuario responsavel"){echo "<span title='encaminhada para usuario responsavel'>EncRes</span>";}    
    if($row_venda_agenda_implantacao['status']=="devolvida para usuario responsavel"){echo "<span title='devolvida para usuario responsavel'>DevRes</span>";}

    if($row_venda_agenda_implantacao['status']=="aguardando retorno do cliente"){echo "<span title='aguardando retorno do cliente'>AguCli</span>";}
	if($row_venda_agenda_implantacao['status']=="aguardando atendente"){echo "<span title='aguardando atendente'>AguAte</span>";}
	if($row_venda_agenda_implantacao['status']=="aguardando agendamento"){echo "<span title='aguardando agendamento'>AguAge</span>";}

    ?><? echo "</div>"; ?>",
	situacao:"<?php echo $row_venda_agenda_implantacao['situacao']; ?>",
	data_venda:"<?php echo $row_venda_agenda_implantacao['data_venda']; ?>",
	venda_agenda_implantacao_data_inicio:"<?php echo $row_venda_agenda_implantacao['venda_agenda_implantacao_data_inicio']; ?>",
	venda_agenda_implantacao_descricao:"<?php echo GetSQLValueString($row_venda_agenda_implantacao['venda_agenda_implantacao_descricao'], "string"); ?>",
	venda_agenda_implantacao_status:"<?php 
	if($row_venda_agenda_implantacao['venda_agenda_implantacao_status']=="a"){echo "Agendado";}
	if($row_venda_agenda_implantacao['venda_agenda_implantacao_status']=="f"){echo "Finalizado";}
	if($row_venda_agenda_implantacao['venda_agenda_implantacao_status']=="c"){echo "Cancelado";}
	?>",
	visualizar:"<? echo "<a href='venda_editar.php?id_venda=".$row_venda_agenda_implantacao['id']."&padrao=sim'><img src='imagens/visualizar.png' border='0' /></a>"; ?>"
},
<?php } while ($row_venda_agenda_implantacao = mysql_fetch_assoc($venda_agenda_implantacao)); ?>
];
jQuery('#venda_agenda_implantacao').jqGrid({
	data:dados,
	datatype: 'local',
	colNames:['Núm','Contrato','Cliente',<? if($row_usuario['controle_venda'] == "Y"){ ?>'Praça',<? } ?>'Responsável','Status','Situação','Data criação','Agenda (data)','Agenda (descrição)','Agenda (status)',''],
	colModel :[ 
		{name:'id', index:'id', width:30, sorttype: 'integer'}, 
		{name:'contrato', index:'contrato', width:30, sorttype: 'integer'}, 
		{name:'empresa', index:'empresa'}, 
		<? if($row_usuario['controle_venda'] == "Y"){ ?>
		{name:'praca', index:'praca', width: 80, align:'center'},
		<? } ?>
		{name:'usuario_responsavel', index:'usuario_responsavel', width:70, align:'left'}, 
		{name:'status', index:'status', width: 80, align:'center'},
		{name:'situacao', index:'situacao', width: 80, align:'center'},
		{name:'data_venda', index:'data_venda', width:70, formatter:'date', formatoptions: { srcformat: "ISO8601Long", newformat: "d-m-Y H:i" }, align:'center' },
		{name:'venda_agenda_implantacao_data_inicio', index:'venda_agenda_implantacao_data_inicio', width:70, formatter:'date', formatoptions: { srcformat: "ISO8601Long", newformat: "d-m-Y H:i" }, align:'center' },
		{name:'venda_agenda_implantacao_descricao', index:'venda_agenda_implantacao_descricao', width: 140, align:'left'},
		{name:'venda_agenda_implantacao_status', index:'venda_agenda_implantacao_status', width: 80, align:'center'},
		{name:'visualizar', index:'visualizar', width:20, align:'center'} 
	],
	rowNum:20,
	rowList:[2,5,10,20,30,40,50,100,999999],
				loadComplete: function() {
					$("option[value=999999]").text('Todos');
				},
	pager: '#venda_agenda_implantacao_navegacao',
	//sortname: 'id',
	viewrecords: true,
	//sortorder: 'desc',
	toppager: true, // aparecer a barra de navegação também no topo
	// caption:"Suportes", barra no topo
	autowidth: true,
	height: "100%",

	ondblClickRow: function(id){
		top.location.href="venda_editar.php?id_venda="+id+"&padrao=sim";
	}		
});
</script>
</div>
<? } else { ?>
    <div class="div_solicitacao_linhas" id="cabecalho_venda_agenda_implantacao" style="cursor: pointer; margin-top: 5px;">
    <table cellspacing="0" cellpadding="0" width="100%">
        <tr>
            <td style="text-align:left">
            Agenda de Vendas de Implantação (<? echo $totalRows_venda_agenda_implantacao; ?>)
            </td>
            
            <td style="text-align: right">
            <div style="float: right; background-image:url(imagens/icone_filtro.png); width: 12px; height: 12px;"></div>
            </td>
        </tr>
    </table>
    </div>
    
    <div id="corpo_venda_agenda_implantacao" style="cursor: pointer">
    <table id="venda_agenda_implantacao"></table>
    <div id="venda_agenda_implantacao_navegacao"></div>
    Nenhuma venda encontrada na filtragem atual.
    </div>
<? } ?>
<!-- fim - venda_agenda_implantacao -->


<!-- barra inferior -->
<div class="div_solicitacao_linhas4" style="margin-top: 5px;">
<table cellspacing="0" cellpadding="0" width="100%">
	<tr>
		<td style="text-align:left">

<a href="agenda_popup.php?height=<? echo $venda_editar_tabela_height; ?>&width=<? echo $venda_editar_tabela_width; ?>&placeValuesBeforeTB_=savedValues&TB_iframe=true" id="botao_geral2" class="thickbox">Ver agenda</a>
  
<!-- Gerar relatório -->   
<? if($totalRows_venda > "0") { // caso seja encontrada alguma venda com os filtros atuais ?>
<a href="#TB_inline?height=<? echo $venda_editar_tabela_height; ?>&width=<? echo $venda_editar_tabela_width; ?>&inlineId=gerar_relatorio&modal=true" class="thickbox" id="botao_geral2">Gerar relatório</a>
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
    <form action="venda_relatorio.php" method="post" target="_blank" id="form" name="form">
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
    <input name="relatorio_tipo" type="radio" value="venda" checked="checked" /> Vendas
    <input name="relatorio_tipo" type="radio" value="venda_agenda_treinamento" /> Agenda de Vendas de Treinamento
    <input name="relatorio_tipo" type="radio" value="venda_agenda_implantacao" /> Agenda de Vendas de Implantação
    </div>
            
    <div class="div_solicitacao_linhas4">
        Marque os campos que irão aparecer no relatório:
        <br><br>
        <!-- campos (checklist) -->
        <fieldset style="border: 0px;">
        <table width="100%" border="0" cellspacing="0" cellpadding="0">
        <tr>
        <td width="33%" valign="top">
        <input value="id" type="checkbox" name="relatorio_campos[]" checked />
        Núm. do venda
        <br>
        
        
        <input value="id_prospeccao" type="checkbox" name="relatorio_campos[]" />
        Nº da prospecção
        <br>
        
        <input value="data_venda" type="checkbox" name="relatorio_campos[]" checked />
        Data do venda
        <br>
               
        <input value="empresa" type="checkbox" name="relatorio_campos[]" checked />
        Empresa
        <br>
        
        <input value="contrato" type="checkbox" name="relatorio_campos[]" />
        Núm. do contrato
        <br>
        
        <input value="praca" type="checkbox" name="relatorio_campos[]" />
        Praça
        <br>
        
        <input value="data_inicio" type="checkbox" name="relatorio_campos[]" />
        Data início
        <br>
        
        <input value="data_fim" type="checkbox" name="relatorio_campos[]" />
        Data fim
        </td>
        <td width="33%" valign="top">
        <input value="usuario_responsavel" type="checkbox" name="relatorio_campos[]" />
        Usuário responsável
        <br>
        
        <input value="status" type="checkbox" name="relatorio_campos[]" />
        Status
        <br>
        
        <input value="situacao" type="checkbox" name="relatorio_campos[]" />
        Situação
        <br>
        
        <input value="observacao" type="checkbox" name="relatorio_campos[]" />
        Observação
        </td>

        </tr>
        </table>
        </fieldset>
        <!-- fim - campos (checklist) -->        
    </div>
    
    <!-- rodapé -->
    <div>Obs: este relatório é baseado nos filtros utilizados na tela anterior de listagem das vendas.</div>
    <div style="margin-top: 5px;">
    <input type="hidden" name="where" id="where" value="<?  echo @$where; ?>">
    <input type="hidden" name="where_agenda_treinamento" id="where_agenda_treinamento" value="<?  echo @$where_agenda_treinamento; ?>">
    <input type="hidden" name="where_agenda_implantacao" id="where_agenda_implantacao" value="<?  echo @$where_agenda_implantacao; ?>">
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
mysql_free_result($filtro_id_usuario_responsavel);
mysql_free_result($venda);
mysql_free_result($venda_agenda_treinamento);
mysql_free_result($venda_agenda_implantacao);
?>