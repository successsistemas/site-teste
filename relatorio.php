<?php 
ob_start();
set_time_limit(0);
ini_set('memory_limit', '-1');

require('restrito.php');
require_once('Connections/conexao.php');

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

require('suporte_funcao_update.php');
require_once('parametros.php');

// tela
$tela = "digital";
if ( isset($_GET['tela']) and $_GET['tela']=="impressao" ){
	$tela = "impressao";
}
// fim - tela

if($tela=="impressao"){			
	require_once "mpdf/mpdf.php";
}

// sql_big_selects
mysql_select_db($database_conexao, $conexao);
$query_sql_big_selects = "SET SQL_BIG_SELECTS=1";
$sql_big_selects = mysql_query($query_sql_big_selects, $conexao) or die(mysql_error());
// fim - sql_big_selects
        
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

// relatorio_modo (unico / geral_modulo / geral)
$relatorio_modo = NULL;
if( (isset($_GET["relatorio_id"])) && ($_GET['relatorio_id'] > 0) ) {
	$relatorio_modo = 'unico';
} else if( (isset($_GET["relatorio_id_grupo"])) && ($_GET['relatorio_id_grupo'] > 0) ) {
	$relatorio_modo = 'geral_modulo';
} else {
	$relatorio_modo = 'geral';
}
// fim - relatorio_modo (unico / geral_modulo / geral)

// relatorio_fechamento
$fechamento = "nao";
if (isset($_GET['fechamento']) and $_GET['fechamento']=="sim" and $row_usuario['praca'] == $_SESSION['MM_praca'] and $praca_status == 0){
	$fechamento = "sim";
} else if($praca_status == 0){
	header("Location: painel/index.php"); exit;
}	
// fim - relatorio_fechamento

// where_relatorio_id_grupo
$relatorio_id_grupo = NULL;
$where_relatorio_id_grupo = "1=1";
if( (isset($_GET["relatorio_id_grupo"])) && ($_GET['relatorio_id_grupo'] !="") ) {
	$colname_relatorio_id_grupo = GetSQLValueString($_GET["relatorio_id_grupo"], "int");
	$where_relatorio_id_grupo .= " and relatorio.id_grupo = $colname_relatorio_id_grupo ";
	$relatorio_id_grupo = $colname_relatorio_id_grupo;
}
// fim - where_relatorio_id_grupo

// where_relatorio_id_grupo_subgrupo
$relatorio_id_grupo_subgrupo = NULL;
$where_relatorio_id_grupo_subgrupo = "1=1";
if( (isset($_GET["relatorio_id_grupo_subgrupo"])) && ($_GET['relatorio_id_grupo_subgrupo'] !="") ) {
	$colname_relatorio_id_grupo_subgrupo = GetSQLValueString($_GET["relatorio_id_grupo_subgrupo"], "int");
	$where_relatorio_id_grupo_subgrupo .= " and relatorio.id_grupo_subgrupo = $colname_relatorio_id_grupo_subgrupo ";
	$relatorio_id_grupo_subgrupo = $colname_relatorio_id_grupo_subgrupo;
} 
// fim - where_relatorio_id_grupo_subgrupo

// where_relatorio_id
$relatorio_id = NULL;
$where_relatorio_id = "1=1";
if( (isset($_GET["relatorio_id"])) && ($_GET['relatorio_id'] !="") ) {
	$colname_relatorio_id = GetSQLValueString($_GET["relatorio_id"], "int");
	$where_relatorio_id .= " and relatorio.id = $colname_relatorio_id ";
	$relatorio_id = $colname_relatorio_id;
} else {
	$where_relatorio_id .= " and relatorio.geral = 1 ";
}
// fim - where_relatorio_id

// relatorio
mysql_select_db($database_conexao, $conexao);
$query_relatorio = "
SELECT relatorio.*, 
relatorio_grupo.titulo AS relatorio_grupo_titulo, 
relatorio_grupo_subgrupo.titulo AS relatorio_grupo_subgrupo_titulo, 
relatorio_grupo_geral.titulo AS relatorio_grupo_geral_titulo 
FROM relatorio 
LEFT JOIN relatorio_grupo ON relatorio.id_grupo = relatorio_grupo.id 
LEFT JOIN relatorio_grupo_subgrupo ON relatorio.id_grupo_subgrupo = relatorio_grupo_subgrupo.id 
LEFT JOIN relatorio_grupo_geral ON relatorio.id_grupo_geral = relatorio_grupo_geral.id 
WHERE 
$where_relatorio_id_grupo and 
$where_relatorio_id_grupo_subgrupo and 
$where_relatorio_id and 
status = 1 
ORDER BY relatorio.id_grupo_geral ASC, relatorio.ordem_grupo_geral ASC, relatorio_grupo.id ASC, relatorio_grupo_subgrupo.id ASC, relatorio.id ASC";

$relatorio = mysql_query($query_relatorio, $conexao) or die(mysql_error());
$row_relatorio = mysql_fetch_assoc($relatorio);
$totalRows_relatorio = mysql_num_rows($relatorio);
// fim - relatorio

// relatorio_javascript
$relatorio_javascript = mysql_query($query_relatorio, $conexao) or die(mysql_error());
$row_relatorio_javascript = mysql_fetch_assoc($relatorio_javascript);
$totalRows_relatorio_javascript = mysql_num_rows($relatorio_javascript);
// fim - relatorio_javascript

// filtros -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------

// filtro_relatorio_grupo
mysql_select_db($database_conexao, $conexao);
$query_filtro_relatorio_grupo = "SELECT * FROM relatorio_grupo ORDER BY id ASC";
$filtro_relatorio_grupo = mysql_query($query_filtro_relatorio_grupo, $conexao) or die(mysql_error());
$row_filtro_relatorio_grupo = mysql_fetch_assoc($filtro_relatorio_grupo);
$totalRows_filtro_relatorio_grupo = mysql_num_rows($filtro_relatorio_grupo);
// fim - filtro_relatorio_grupo

// filtro_relatorio_grupo_subgrupo
mysql_select_db($database_conexao, $conexao);
$query_filtro_relatorio_grupo_subgrupo = sprintf("SELECT * FROM relatorio_grupo_subgrupo WHERE id_relatorio_grupo=%s ORDER BY id_relatorio_grupo ASC, id ASC", GetSQLValueString(@$_GET['relatorio_id_grupo'], "int"));
$filtro_relatorio_grupo_subgrupo = mysql_query($query_filtro_relatorio_grupo_subgrupo, $conexao) or die(mysql_error());
$row_filtro_relatorio_grupo_subgrupo = mysql_fetch_assoc($filtro_relatorio_grupo_subgrupo);
$totalRows_filtro_relatorio_grupo_subgrupo = mysql_num_rows($filtro_relatorio_grupo_subgrupo);
// fim - filtro_relatorio_grupo_subgrupo

// filtro_geral_praca
mysql_select_db($database_conexao, $conexao);
$query_filtro_geral_praca = "SELECT praca FROM geral_tipo_praca ORDER BY praca ASC";
$filtro_geral_praca = mysql_query($query_filtro_geral_praca, $conexao) or die(mysql_error());
$row_filtro_geral_praca = mysql_fetch_assoc($filtro_geral_praca);
$totalRows_filtro_geral_praca = mysql_num_rows($filtro_geral_praca);
// fim - filtro_geral_praca

// filtro_geral_cliente
mysql_select_db($database_conexao, $conexao);
$query_filtro_geral_cliente = "
SELECT da01.nome1, da01.codigo1
FROM da37 
INNER JOIN da01 ON da37.cliente17 = da01.codigo1 
WHERE da01.sr_deleted <> 'T' and da37.sr_deleted <> 'T' 
ORDER BY da01.nome1
";
$filtro_geral_cliente = mysql_query($query_filtro_geral_cliente, $conexao) or die(mysql_error());
$row_filtro_geral_cliente = mysql_fetch_assoc($filtro_geral_cliente);
$totalRows_filtro_geral_cliente = mysql_num_rows($filtro_geral_cliente);	
// fim - filtro_geral_cliente

// filtro_geral_usuario
mysql_select_db($database_conexao, $conexao);
$query_filtro_geral_usuario = "SELECT IdUsuario, nome FROM usuarios WHERE status = 1 ORDER BY nome ASC";
$filtro_geral_usuario = mysql_query($query_filtro_geral_usuario, $conexao) or die(mysql_error());
$row_filtro_geral_usuario = mysql_fetch_assoc($filtro_geral_usuario);
$totalRows_filtro_geral_usuario = mysql_num_rows($filtro_geral_usuario);	
// fim - filtro_geral_usuario

// filtro_suporte_solicitante
mysql_select_db($database_conexao, $conexao);
$query_filtro_suporte_solicitante = "SELECT IdUsuario, nome FROM usuarios WHERE status = 1 ORDER BY nome ASC";
$filtro_suporte_solicitante = mysql_query($query_filtro_suporte_solicitante, $conexao) or die(mysql_error());
$row_filtro_suporte_solicitante = mysql_fetch_assoc($filtro_suporte_solicitante);
$totalRows_filtro_suporte_solicitante = mysql_num_rows($filtro_suporte_solicitante);	
// fim - filtro_suporte_solicitante

// filtro_suporte_atendente
mysql_select_db($database_conexao, $conexao);
$query_filtro_suporte_atendente = "SELECT IdUsuario, nome FROM usuarios WHERE status = 1 ORDER BY nome ASC";
$filtro_suporte_atendente = mysql_query($query_filtro_suporte_atendente, $conexao) or die(mysql_error());
$row_filtro_suporte_atendente = mysql_fetch_assoc($filtro_suporte_atendente);
$totalRows_filtro_suporte_atendente = mysql_num_rows($filtro_suporte_atendente);	
// fim - filtro_suporte_atendente

// filtro_suporte_tipo_atendimento
mysql_select_db($database_conexao, $conexao);
$query_filtro_suporte_tipo_atendimento = "SELECT * FROM suporte_tipo_atendimento ORDER BY IdTipoAtendimento ASC";
$filtro_suporte_tipo_atendimento = mysql_query($query_filtro_suporte_tipo_atendimento, $conexao) or die(mysql_error());
$row_filtro_suporte_tipo_atendimento = mysql_fetch_assoc($filtro_suporte_tipo_atendimento);
$totalRows_filtro_suporte_tipo_atendimento = mysql_num_rows($filtro_suporte_tipo_atendimento);
// fim - filtro_suporte_tipo_atendimento

// filtro_suporte_tipo_recomendacao
mysql_select_db($database_conexao, $conexao);
$query_filtro_suporte_tipo_recomendacao = "SELECT * FROM suporte_tipo_recomendacao ORDER BY IdTipoRecomendacao ASC";
$filtro_suporte_tipo_recomendacao = mysql_query($query_filtro_suporte_tipo_recomendacao, $conexao) or die(mysql_error());
$row_filtro_suporte_tipo_recomendacao = mysql_fetch_assoc($filtro_suporte_tipo_recomendacao);
$totalRows_filtro_suporte_tipo_recomendacao = mysql_num_rows($filtro_suporte_tipo_recomendacao);
// fim - filtro_suporte_tipo_recomendacao

// filtro_solicitacao_solicitante
mysql_select_db($database_conexao, $conexao);
$query_filtro_solicitacao_solicitante = "SELECT IdUsuario, nome FROM usuarios WHERE status = 1 ORDER BY nome ASC";
$filtro_solicitacao_solicitante = mysql_query($query_filtro_solicitacao_solicitante, $conexao) or die(mysql_error());
$row_filtro_solicitacao_solicitante = mysql_fetch_assoc($filtro_solicitacao_solicitante);
$totalRows_filtro_solicitacao_solicitante = mysql_num_rows($filtro_solicitacao_solicitante);	
// fim - filtro_solicitacao_solicitante

// filtro_solicitacao_executante
mysql_select_db($database_conexao, $conexao);
$query_filtro_solicitacao_executante = "SELECT IdUsuario, nome FROM usuarios WHERE solicitacao_executante = 'Y' and status = 1 ORDER BY nome ASC";
$filtro_solicitacao_executante = mysql_query($query_filtro_solicitacao_executante, $conexao) or die(mysql_error());
$row_filtro_solicitacao_executante = mysql_fetch_assoc($filtro_solicitacao_executante);
$totalRows_filtro_solicitacao_executante = mysql_num_rows($filtro_solicitacao_executante);	
// fim - filtro_solicitacao_executante

// filtro_solicitacao_operador
mysql_select_db($database_conexao, $conexao);
$query_filtro_solicitacao_operador = "SELECT IdUsuario, nome FROM usuarios WHERE controle_solicitacao = 'Y' and  status = 1 ORDER BY nome ASC";
$filtro_solicitacao_operador = mysql_query($query_filtro_solicitacao_operador, $conexao) or die(mysql_error());
$row_filtro_solicitacao_operador = mysql_fetch_assoc($filtro_solicitacao_operador);
$totalRows_filtro_solicitacao_operador = mysql_num_rows($filtro_solicitacao_operador);	
// fim - filtro_solicitacao_operador

// filtro_solicitacao_testador
mysql_select_db($database_conexao, $conexao);
$query_filtro_solicitacao_testador = "SELECT IdUsuario, nome FROM usuarios WHERE solicitacao_testador = 'Y' and  status = 1 ORDER BY nome ASC";
$filtro_solicitacao_testador = mysql_query($query_filtro_solicitacao_testador, $conexao) or die(mysql_error());
$row_filtro_solicitacao_testador = mysql_fetch_assoc($filtro_solicitacao_testador);
$totalRows_filtro_solicitacao_testador = mysql_num_rows($filtro_solicitacao_testador);	
// fim - filtro_solicitacao_testador

// filtro_solicitacao_tipo para
mysql_select_db($database_conexao, $conexao);
$query_filtro_solicitacao_tipo = "SELECT * FROM solicitacao_tipo_solicitacao ORDER BY IdTipoSolicitacao ASC";
$filtro_solicitacao_tipo = mysql_query($query_filtro_solicitacao_tipo, $conexao) or die(mysql_error());
$row_filtro_solicitacao_tipo = mysql_fetch_assoc($filtro_solicitacao_tipo);
$totalRows_filtro_solicitacao_tipo = mysql_num_rows($filtro_solicitacao_tipo);
// fim - filtro_solicitacao_tipo

// filtro_prospeccao_usuario_responsavel
mysql_select_db($database_conexao, $conexao);
$query_filtro_prospeccao_usuario_responsavel = "SELECT IdUsuario, nome FROM usuarios WHERE status = 1 ORDER BY nome ASC";
$filtro_prospeccao_usuario_responsavel = mysql_query($query_filtro_prospeccao_usuario_responsavel, $conexao) or die(mysql_error());
$row_filtro_prospeccao_usuario_responsavel = mysql_fetch_assoc($filtro_prospeccao_usuario_responsavel);
$totalRows_filtro_prospeccao_usuario_responsavel = mysql_num_rows($filtro_prospeccao_usuario_responsavel);	
// fim - filtro_prospeccao_usuario_responsavel

// filtro_venda_usuario_responsavel
mysql_select_db($database_conexao, $conexao);
$query_filtro_venda_usuario_responsavel = "SELECT IdUsuario, nome FROM usuarios WHERE status = 1 ORDER BY nome ASC";
$filtro_venda_usuario_responsavel = mysql_query($query_filtro_venda_usuario_responsavel, $conexao) or die(mysql_error());
$row_filtro_venda_usuario_responsavel = mysql_fetch_assoc($filtro_venda_usuario_responsavel);
$totalRows_filtro_venda_usuario_responsavel = mysql_num_rows($filtro_venda_usuario_responsavel);	
// fim - filtro_venda_usuario_responsavel

// filtro_venda_modulos
mysql_select_db($database_conexao, $conexao);
$query_filtro_venda_modulos = "SELECT * FROM geral_tipo_modulo ORDER BY IdTipoModuloCategoria ASC, ordem ASC";
$filtro_venda_modulos = mysql_query($query_filtro_venda_modulos, $conexao) or die(mysql_error());
$row_filtro_venda_modulos = mysql_fetch_assoc($filtro_venda_modulos);
$totalRows_filtro_venda_modulos = mysql_num_rows($filtro_venda_modulos);
// fim - filtro_venda_modulos
	
// fim - filtros -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------

// acessso --------------------------------------------------------------------------------------------------------------------------------------------------------------
$acesso = 0; // somente a sua praça
if(
$row_usuario['controle_relatorio'] == "Y" or // controlador de relatorio
($row_usuario['controle_solicitacao'] == "Y" and $relatorio_id_grupo == 1) or // controlador de solicitação
($row_usuario['controle_suporte'] == "Y" and $relatorio_id_grupo == 2) or // controlador de suporte
($row_usuario['controle_prospeccao'] == "Y" and $relatorio_id_grupo == 3) or // controlador de prospecção
($row_usuario['controle_venda'] == "Y" and $relatorio_id_grupo == 4) // controlador de venda
){ 

	$acesso = 1; // qualquer praça
	
}
// fim - acesso ---------------------------------------------------------------------------------------------------------------------------------------------------------

// $praca_atual
$praca_atual = $_SESSION['MM_praca'];
if($acesso == 1 and $fechamento == "nao"){
	
	if(isset($_GET['filtro_geral_praca']) and $_GET['filtro_geral_praca'] != ""){ 
		$praca_atual = $_GET['filtro_geral_praca'];
	} else {
		$praca_atual = "GERAL";
	}
	
}
// fim - $praca_atual

$data_atual = date('d-m-Y H:i');

$numero_pagina = NULL;
if($tela=="impressao" and $totalRows_relatorio == 1){
	$numero_pagina = "Página: {PAGENO}";
}

$data_criacao_atual = NULL; 
if ( isset($_GET['filtro_geral_data_criacao']) and $_GET['filtro_geral_data_criacao']!="" ){ 
	$data_criacao_atual = $_GET['filtro_geral_data_criacao'];
}

$data_criacao_fim_atual = NULL; 
if ( isset($_GET['filtro_geral_data_criacao_fim']) and $_GET['filtro_geral_data_criacao_fim']!="" ){ 
	$data_criacao_fim_atual = $_GET['filtro_geral_data_criacao_fim']; 
}

// mes_ano_atual
if ( isset($_GET["filtro_geral_data_criacao"]) ) {
	$filtro_geral_data_criacao_data = substr($_GET["filtro_geral_data_criacao"],0,10);
	$mes_ano_atual = $filtro_geral_data_criacao = implode("-",array_reverse(explode("-",$filtro_geral_data_criacao_data)));
	
	$mes_ano_atual = date('m-Y', strtotime($mes_ano_atual));
}
// fim - mes_ano_atual

// função que limita caracteres
function limita_caracteres($string, $tamanho, $encode = 'UTF-8') {
    if( strlen($string) > $tamanho ){
        $string = mb_substr($string, 0, $tamanho, $encode);
	}
    return $string;
}
// fim - função que limita caracteres

// função que transforma o total_em_minutos em ano/dia/hora/minuto
function tempo_gasto_conversao($total_em_minutos){
		
	$time = $total_em_minutos*60;
	$tempo_gasto_conversao = NULL;
	if($time >= 31556926){
		$tempo_gasto_conversao .= $value["anos"] = floor($time/31556926)." ano(s) ";
		$time = ($time%31556926);
	}
	if($time >= 86400){
	  $tempo_gasto_conversao .= $value["dias"] = floor($time/86400)." dia(s) ";
	  $time = ($time%86400);
	}
	if($time >= 3600){
	  $tempo_gasto_conversao .= $value["horas"] = floor($time/3600)." hr ";
	  $time = ($time%3600);
	}
	if($time >= 60){
	  $tempo_gasto_conversao .= $value["minutos"] = floor($time/60)." minuto(s)";
	  $time = ($time%60);
	}
	//return $tempo_gasto_conversao;
	
    $horas = sprintf('%02d', floor($total_em_minutos/60));
	$minutos = sprintf('%02d', $total_em_minutos%60);
	
	return $horas.":".$minutos." hr";
	
}
// fim - função que transforma o total_em_minutos em ano/dia/hora/minuto

// relatorio_fechamento_contador
if($relatorio_id_grupo == NULL){
	
	$emissao = NULL;
	
	if($fechamento == 'sim'){
		
		// update (geral_tipo_praca - contador)
		mysql_select_db($database_conexao, $conexao);
		$updateSQL_geral_tipo_praca = sprintf("
		UPDATE geral_tipo_praca 
		SET contador_relatorio_fechamento = contador_relatorio_fechamento + 1
		WHERE praca = %s",
		GetSQLValueString($_SESSION['MM_praca'], "text"));
		mysql_select_db($database_conexao, $conexao);
		$Result_geral_tipo_praca_update = mysql_query($updateSQL_geral_tipo_praca, $conexao) or die(mysql_error());
		// fim - update (geral_tipo_praca contador)
		
		// relatorio_fechamento_contador
		mysql_select_db($database_conexao, $conexao);
		$query_relatorio_fechamento_contador = sprintf("
		SELECT geral_tipo_praca.contador_relatorio_fechamento 
		FROM geral_tipo_praca 
		WHERE geral_tipo_praca.praca = %s", 
		GetSQLValueString($praca_atual, "text"));
		$relatorio_fechamento_contador = mysql_query($query_relatorio_fechamento_contador, $conexao) or die(mysql_error());
		$row_relatorio_fechamento_contador = mysql_fetch_assoc($relatorio_fechamento_contador);
		$totalRows_relatorio_fechamento_contador = mysql_num_rows($relatorio_fechamento_contador);
		// fim - relatorio_fechamento_contador
		
		$emissao = "<span style='padding-left: 20px;'><strong>Emissão: </strong>".@$row_relatorio_fechamento_contador['contador_relatorio_fechamento']."</span>";
		
	}
	
}
// fim - relatorio_fechamento_contador

// relatorio_grupo_geral (array)
mysql_select_db($database_conexao, $conexao);
$query_relatorio_grupo_geral = "SELECT * FROM relatorio_grupo_geral ORDER BY id ASC";
$relatorio_grupo_geral = mysql_query($query_relatorio_grupo_geral, $conexao) or die(mysql_error());
$row_relatorio_grupo_geral = mysql_fetch_assoc($relatorio_grupo_geral);
$totalRows_relatorio_grupo_geral = mysql_num_rows($relatorio_grupo_geral);
$relatorio_grupo_geral_array = NULL;
do {
	$relatorio_grupo_geral_array[] = array(
									'id' 	 => $row_relatorio_grupo_geral['id'], 
									'titulo' => $row_relatorio_grupo_geral['titulo'], 
									'titulo_exibir' => 0,
									'geral'	 => NULL
									);
} while ($row_relatorio_grupo_geral = mysql_fetch_assoc($relatorio_grupo_geral));
mysql_free_result($relatorio_grupo_geral);
// fim - relatorio_grupo_geral (array)

// Fechamento ============================================================================
if($relatorio_id_grupo == NULL and $tela == "digital"){ // geral e digital

// $where_relatorio_fechamento ------------
$where_relatorio_fechamento = "1=1";
if($acesso == 1){

	if (isset($_GET['filtro_geral_praca']) and $_GET['filtro_geral_praca'] != ""){ 
	
		$where_relatorio_fechamento .= " and ( 
						 relatorio_fechamento.praca = '".$praca_atual."'
						 ) ";
					 
	} else {
		
		$where_relatorio_fechamento .= " and ( 
						 relatorio_fechamento.praca = '".$row_usuario['praca']."' or 
						 relatorio_fechamento.praca <> '".$row_usuario['praca']."'
						 ) ";
						 
	}
	
} else {
	
	$where_relatorio_fechamento .= " and ( 
					 relatorio_fechamento.praca = '".$row_usuario['praca']."' 
					 ) ";
	
} 
// fim - $where_relatorio_fechamento ------

// relatorio_fechamento - filtros ---------
// se existe filtro de praca
if( (isset($_GET["praca"])) && ($_GET['praca'] !="") ) {
	$colname_relatorio_fechamento_praca = GetSQLValueString($_GET["praca"], "string");
	$where_relatorio_fechamento .= " and relatorio_fechamento.praca = '$colname_relatorio_fechamento_praca' "; 	
	$where_relatorio_fechamento_campos[] = "praca";
} 
// fim - se existe filtro de praca
// fim - relatorio_fechamento - filtros ---

// relatorio_fechamento
mysql_select_db($database_conexao, $conexao);
$query_relatorio_fechamento = "
SELECT relatorio_fechamento.status, relatorio_fechamento.id, relatorio_fechamento.data_criacao, relatorio_fechamento.data, relatorio_fechamento.praca, relatorio_fechamento.usuario_responsavel, relatorio_fechamento.arquivo, 
(SELECT nome FROM usuarios WHERE usuarios.IdUsuario = relatorio_fechamento.id_usuario) as usuario_responsavel 
FROM relatorio_fechamento 
WHERE $where_relatorio_fechamento and relatorio_fechamento.status = 1 
ORDER BY relatorio_fechamento.praca ASC, relatorio_fechamento.id DESC";

$relatorio_fechamento = mysql_query($query_relatorio_fechamento, $conexao) or die(mysql_error());
$row_relatorio_fechamento = mysql_fetch_assoc($relatorio_fechamento);
$totalRows_relatorio_fechamento = mysql_num_rows($relatorio_fechamento);
// fim - relatorio_fechamento

}
// fim - Fechamento ======================================================================

// funcaoConsultaUsuarioNome
function funcaoConsultaUsuarioNome($IdUsuario){

	require('Connections/conexao.php');
	
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

	// usuário logado via SESSION
	mysql_select_db($database_conexao, $conexao);
	$query_usuario_consulta_nome = sprintf("SELECT nome FROM usuarios WHERE IdUsuario = %s", GetSQLValueString(@$IdUsuario, "int"));
	$usuario_consulta_nome = mysql_query($query_usuario_consulta_nome, $conexao) or die(mysql_error());
	$row_usuario_consulta_nome = mysql_fetch_assoc($usuario_consulta_nome);
	$totalRows_usuario_consulta_nome = mysql_num_rows($usuario_consulta_nome);
	// fim - usuário logado via SESSION
	return $row_usuario_consulta_nome['nome'];
	mysql_free_result($usuario_consulta_nome);
}
// fim - funcaoConsultaUsuarioNome
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<? if($tela=="digital"){ ?>
<style>
/* erro de validação */
label.error {
	color: #C00; 
	display: none;
	font-size: 10px;
}	
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
</style>
<? } ?>
<link rel="stylesheet" href="css/guia_registro.css" type="text/css" />
<link rel="stylesheet" href="css/guia_registro_imprimir.css" type="text/css" media="print" />

<? if($tela=="digital"){ ?>

<script type="text/javascript" src="js/jquery.js"></script>
<script type="text/javascript" src="funcoes.js"></script>

<link type="text/css" href="css/jquery-ui.css" rel="stylesheet" />	
<link type="text/css" href="css/jquery-ui-timepicker-addon.css" rel="stylesheet" />	

<script type="text/javascript" src="js/jquery-ui.js"></script>
<script type="text/javascript" src="js/jquery-ui-timepicker-addon.js"></script>

<script type="text/javascript" src="js/jquery.maskedinput-1.2.2.min.js"></script> 
<script type="text/javascript" src="js/jquery.validate.js"></script>

<? if($relatorio_id_grupo == NULL){ // geral ?>
<!--[if !IE]> -->
<style>
body{
	overflow-y: scroll; /* se não é IE, então mostra a scroll vertical */
}
</style>
<!-- <![endif]-->

<style>
.ui-jqgrid .ui-jqgrid-btable{
  table-layout:auto;
} 
</style>
<link rel="stylesheet" type="text/css" media="screen" href="css/ui.jqgrid.css" />
<script src="js/grid.locale-pt-br.js" type="text/javascript"></script>
<script src="js/jquery.jqGrid.src.js" type="text/javascript"></script>
<? } ?>

<script type="text/javascript"> 
// validar diferença entre datas (maior ou igual)
jQuery.validator.addMethod("dateRange", function() {

		var is_valid = true;
		var data_inicio = $("#filtro_geral_data_criacao").val();
		var data_fim = $("#filtro_geral_data_criacao_fim").val();
												 
		if(data_inicio.length != 10){is_valid = false;}
		if(data_fim.length != 10){is_valid = false;}

		if(data_inicio != "" && data_fim != ""){
			
			// quebra data inicial
			var quebraDI=data_inicio.split("-");
			var diaDI = quebraDI[0];
			var mesDI = quebraDI[1];
			var anoDI = quebraDI[2].substr(0,4);
		
			// quebra data final
			var quebraDF=data_fim.split("-");
			var diaDF = quebraDF[0];
			var mesDF = quebraDF[1];
			var anoDF = quebraDF[2].substr(0,4);
	
			var date1 = anoDI+"-"+mesDI+"-"+diaDI;
			var date2 = anoDF+"-"+mesDF+"-"+diaDF;
			
			is_valid = date1 <= date2;
			
		}
		
		return (is_valid);
		
}, "<br>Data final deve ser maior ou igual que a data inicial");
// validar diferença entre datas (maior ou igual)

// limpar formulário de filtro
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
// fim - limpar formulário de filtro

<? if($relatorio_id_grupo == NULL){ // geral ?>$.jgrid.no_legacy_api = true;<? } ?>
$(document).ready(function() {

	<? if($relatorio_id_grupo == NULL){ // geral ?>
	// ocultar/exibir relatorio_fechamentos
	$('#corpo_relatorio_fechamento').toggle();
	$('#cabecalho_relatorio_fechamento').click(function() {
		$('#corpo_relatorio_fechamento').toggle();
	});
	// fim - ocultar/exibir relatorio_fechamentos
	<? } ?>
	
	// filtro_geral_data_mes_ano
	<? if($relatorio_id_grupo == NULL){ ?>
	var data_criacao_atual = $("input[id=filtro_geral_data_criacao]").val();
	var quebraDI = data_criacao_atual.split("-");
	var mesDI = quebraDI[1];
	var anoDI = quebraDI[2].substr(0,4);
	var date1 = mesDI+"-"+anoDI;
	$("select[id=filtro_geral_data_mes_ano]").val(date1);

	$("select[id=filtro_geral_data_mes_ano]").change(function(){
		
		var mes_ano = $(this).val();
		$("input[id=filtro_geral_data_criacao]").val('01-'+mes_ano);
		$("input[id=filtro_geral_data_criacao_fim]").val('31-'+mes_ano);
		
	});
	<? } ?>
	// fim - filtro_geral_data_mes_ano
		
	// validação
	$("#form_filtro").validate({
		rules: {
			filtro_geral_data_criacao: {required: true},
			filtro_geral_data_criacao_fim: {required: true, dateRange: true}
		},
		messages: {
			filtro_geral_data_criacao: "<br>obrigatório",
			filtro_geral_data_criacao_fim: {required: "<br>obrigatório"}
		},
		onkeyup: false
	});
	// fim - validação

	// mascara
	$('#filtro_geral_data_criacao').mask('99-99-9999',{placeholder:" "});
	$('#filtro_geral_data_criacao_fim').mask('99-99-9999',{placeholder:" "});
	// fim - mascara
	
	// relatorio_id_grupo_subgrupo (início)
	if($("select[id=relatorio_id_grupo_subgrupo]").val() > 0){
		
		// altera select 'relatorio_id'
		$.post("relatorio_seleciona_id.php", 
			  {relatorio_id_grupo_subgrupo:$("select[id=relatorio_id_grupo_subgrupo]").val(), relatorio_id: '<? if ( isset($_GET['relatorio_id']) ){ echo $_GET['relatorio_id']; } ?>'},
			  function(valor){
				 $("select[id=relatorio_id]").html(valor);
			  }
		)
		// fim - altera select 'relatorio_id'

	}
	// fim - relatorio_id_grupo_subgrupo (início)

	// relatorio_id_grupo_subgrupo
	$("select[id=relatorio_id_grupo_subgrupo]").change(function(){
		
		$("select[id=relatorio_id]").val('');
		
		$("select[id=relatorio_id]").html('<option value="0">Carregando...</option>');
		
		// altera select 'relatorio_id'
		$.post("relatorio_seleciona_id.php", 
			  {relatorio_id_grupo_subgrupo:$(this).val(), relatorio_id: ''},
			  function(valor){
				 $("select[id=relatorio_id]").html(valor);
			  }
		)
		// fim - altera select 'relatorio_id'

	});
	// fim - relatorio_id_grupo_subgrupo

	// filtro_geral_praca (início)
	if($("[id=filtro_geral_praca]").val() != ""){
		
		var praca_atual = $("[id=filtro_geral_praca]").val();
		
		// altera select 'filtro_geral_usuario' e 'filtro_geral_cliente'
		<? if(@$data_criacao_atual != ''){ ?>
		$.post("relatorio_seleciona_usuarios.php", 
			  {filtro_geral_praca: praca_atual, usuario_atual: '<? echo @$_GET['filtro_geral_usuario']; ?>'},
			  function(valor){
				 $("select[id=filtro_geral_usuario]").html(valor);
			  }
		)
		
		$.post("relatorio_seleciona_cliente.php", 
			  {filtro_geral_praca: praca_atual, cliente_atual: '<? echo @$_GET['filtro_geral_cliente']; ?>'},
			  function(valor){
				 $("select[id=filtro_geral_cliente]").html(valor);
			  }
		)
		<? } ?>
		// fim - altera select 'filtro_geral_usuario' e 'filtro_geral_cliente'
				
		// altera select 'filtro_solicitacao_solicitante'
		<? if(($row_relatorio['filtro_solicitacao_solicitante'] == 1 or $row_relatorio['filtro_solicitacao_executante'] == 1) and $relatorio_id_grupo > 0){ ?>
		$.post("relatorio_seleciona_usuarios.php", 
			  {filtro_geral_praca: praca_atual, usuario_atual: '<? echo @$_GET['filtro_solicitacao_solicitante']; ?>'},
			  function(valor){
				 $("select[id=filtro_solicitacao_solicitante]").html(valor);
			  }
		)
		<? } ?>
		// fim - altera select 'filtro_solicitacao_solicitante'
		
		// altera select 'filtro_suporte_solicitante'
		<? if($row_relatorio['filtro_suporte_solicitante'] == 1 and $relatorio_id_grupo > 0){ ?>
		$.post("relatorio_seleciona_usuarios.php", 
			  {filtro_geral_praca: praca_atual, usuario_atual: '<? echo @$_GET['filtro_suporte_solicitante']; ?>'},
			  function(valor){
				 $("select[id=filtro_suporte_solicitante]").html(valor);
			  }
		)
		<? } ?>
		// fim - altera select 'filtro_suporte_solicitante'
		
		// altera select 'filtro_prospeccao_usuario_responsavel'
		<? if($row_relatorio['filtro_prospeccao_usuario_responsavel'] == 1 and $relatorio_id_grupo > 0){ ?>
		$.post("relatorio_seleciona_usuarios.php", 
			  {filtro_geral_praca: praca_atual, usuario_atual: '<? echo @$_GET['filtro_prospeccao_usuario_responsavel']; ?>'},
			  function(valor){
				 $("select[id=filtro_prospeccao_usuario_responsavel]").html(valor);
			  }
		)
		<? } ?>
		// fim - altera select 'filtro_prospeccao_usuario_responsavel'
		
		// altera select 'filtro_venda_usuario_responsavel'
		<? if($row_relatorio['filtro_venda_usuario_responsavel'] == 1 and $relatorio_id_grupo > 0){ ?>
		$.post("relatorio_seleciona_usuarios.php", 
			  {filtro_geral_praca: praca_atual, usuario_atual: '<? echo @$_GET['filtro_venda_usuario_responsavel']; ?>'},
			  function(valor){
				 $("select[id=filtro_venda_usuario_responsavel]").html(valor);
			  }
		)
		<? } ?>
		// fim - altera select 'filtro_venda_usuario_responsavel'

	}
	// fim - filtro_geral_praca (início)
	
	// filtro_geral_praca
	$("select[id=filtro_geral_praca]").change(function(){
		
		// filtro_geral_usuario / filtro_geral_cliente	
		$("select[id=filtro_geral_usuario]").val('');
		$("select[id=filtro_geral_usuario]").html('<option value="0">Carregando...</option>');
		var praca_atual = $(this).val();
		$.post("relatorio_seleciona_usuarios.php", 
			  {filtro_geral_praca: praca_atual, filtro_geral_usuario: ''},
			  function(valor){
				 $("select[id=filtro_geral_usuario]").html(valor);
			  }
		)
		
		$("select[id=filtro_geral_cliente]").val('');
		$("select[id=filtro_geral_cliente]").html('<option value="0">Carregando...</option>');
		var praca_atual = $(this).val();
		$.post("relatorio_seleciona_cliente.php", 
			  {filtro_geral_praca: praca_atual, filtro_geral_cliente: ''},
			  function(valor){
				 $("select[id=filtro_geral_cliente]").html(valor);
			  }
		)		
		// fim - filtro_geral_usuario / filtro_geral_cliente
		
		// filtro_solicitacao_solicitante
		<? if(($row_relatorio['filtro_solicitacao_solicitante'] == 1 or $row_relatorio['filtro_solicitacao_executante'] == 1) and $relatorio_id_grupo > 0){ ?>
		$("select[id=filtro_solicitacao_solicitante]").val('');
		$("select[id=filtro_solicitacao_solicitante]").html('<option value="0">Carregando...</option>');
		var praca_atual = $(this).val();
		$.post("relatorio_seleciona_usuarios.php", 
			  {filtro_geral_praca: praca_atual, filtro_solicitacao_solicitante: ''},
			  function(valor){
				 $("select[id=filtro_solicitacao_solicitante]").html(valor);
			  }
		)
		<? } ?>
		// fim - filtro_solicitacao_solicitante
		
		// filtro_suporte_solicitante
		<? if($row_relatorio['filtro_suporte_solicitante'] == 1 and $relatorio_id_grupo > 0){ ?>
		$("select[id=filtro_suporte_solicitante]").val('');
		$("select[id=filtro_suporte_solicitante]").html('<option value="0">Carregando...</option>');
		var praca_atual = $(this).val();
		$.post("relatorio_seleciona_usuarios.php", 
			  {filtro_geral_praca: praca_atual, filtro_suporte_solicitante: ''},
			  function(valor){
				 $("select[id=filtro_suporte_solicitante]").html(valor);
			  }
		)
		<? } ?>
		// fim - filtro_suporte_solicitante
		
		// filtro_prospeccao_usuario_responsavel
		<? if($row_relatorio['filtro_prospeccao_usuario_responsavel'] == 1 and $relatorio_id_grupo > 0){ ?>
		$("select[id=filtro_prospeccao_usuario_responsavel]").val('');
		$("select[id=filtro_prospeccao_usuario_responsavel]").html('<option value="0">Carregando...</option>');
		var praca_atual = $(this).val();
		$.post("relatorio_seleciona_usuarios.php", 
			  {filtro_geral_praca: praca_atual, filtro_prospeccao_usuario_responsavel: ''},
			  function(valor){
				 $("select[id=filtro_prospeccao_usuario_responsavel]").html(valor);
			  }
		)
		<? } ?>
		// fim - filtro_prospeccao_usuario_responsavel
		
		// filtro_venda_usuario_responsavel
		<? if($row_relatorio['filtro_venda_usuario_responsavel'] == 1 and $relatorio_id_grupo > 0){ ?>
		$("select[id=filtro_venda_usuario_responsavel]").val('');
		$("select[id=filtro_venda_usuario_responsavel]").html('<option value="0">Carregando...</option>');
		var praca_atual = $(this).val();
		$.post("relatorio_seleciona_usuarios.php", 
			  {filtro_geral_praca: praca_atual, filtro_venda_usuario_responsavel: ''},
			  function(valor){
				 $("select[id=filtro_venda_usuario_responsavel]").html(valor);
			  }
		)
		<? } ?>
		// fim - filtro_venda_usuario_responsavel
	
	});
	// fim - filtro_geral_praca
	
	// calendario
	<? if($tela=="digital" and $relatorio_id_grupo!=NULL){ ?>
	
	var filtro_geral_data_criacao = $('#filtro_geral_data_criacao');
	var filtro_geral_data_criacao_fim = $('#filtro_geral_data_criacao_fim');
	
	filtro_geral_data_criacao.datepicker({ 
							   
		showOn: "button",
		buttonImage: "css/images/calendar.gif",
		buttonImageOnly: true,									
		minDate: new Date('2000/01/01'),
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
		beforeShow: function (selectedDateTime){
			
			filtro_geral_data_criacao_fim.val('');
			filtro_geral_data_criacao.datepicker('option', 'minDate', new Date('2000/01/01') ); // para 'data'
		
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
	
	filtro_geral_data_criacao_fim.datepicker({ 
							
		showOn: "button",
		buttonImage: "css/images/calendar.gif",
		buttonImageOnly: true,									
		showSecond: false,
		minDateTime: new Date(<?php echo time() * 1000 ?>),
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
		secondText: 'Segundo',
		beforeShow: function (selectedDateTime){
			
			var teste_filtro_geral_data_criacao = filtro_geral_data_criacao.datepicker('getDate');
			var teste_filtro_geral_data_criacao_fim = filtro_geral_data_criacao_fim.datepicker('getDate');
			
			if(teste_filtro_geral_data_criacao != null){
				filtro_geral_data_criacao_fim.datepicker('option', 'minDate', filtro_geral_data_criacao.datepicker('getDate') ); // para 'data'
			}else{
				filtro_geral_data_criacao_fim.datepicker('option', 'minDate', new Date(<?php echo time() * 1000 ?>) ); // para 'data'
			}
			
		}
		
	});
	
	<? } ?>
	// fim - calendario
	
	// filtro_geral_data_criacao/filtro_geral_data_criacao_fim - verifica se é uma data válida
    $('#filtro_geral_data_criacao, #filtro_geral_data_criacao_fim').blur(function(){

		var campo = $(this);
		
		// erro
		var erro = funcao_verifica_data_valida2(campo) // chamada da função (retorna 0/1)		
		if(erro==1){
			
			alert("Data inválida");
			campo.val('');
			setTimeout(function() {campo.focus();}, 100);
			return false;
			
		}
		// fim - erro
		
    });
	// fim - filtro_geral_data_criacao/filtro_geral_data_criacao_fim - verifica se é uma data válida

	// ocultar/exibir registro_tabela
	<? do { ?>
	$('#registro_titulo<? echo $row_relatorio_javascript['id']; ?>').click(function() {
		$('#registro_tabela<? echo $row_relatorio_javascript['id']; ?>').toggle();
	});
	<?php } while ($row_relatorio_javascript = mysql_fetch_assoc($relatorio_javascript)); ?>
	// fim - ocultar/exibir registro_tabela

	// ocultar/exibir filtros
	<? if ($totalRows_relatorio > 0){ ?>
		//$('#corpo_filtros').toggle();
	<? } ?>
	
	$('#cabecalho_filtros').click(function() {
		$('#corpo_filtros').toggle();
	});
	// fim - ocultar/exibir fitlros
	
	// marcar todos
	$('#checkall_filtro_suporte_situacao').click(function () {
	$(this).parents('fieldset:eq(0)').find(':checkbox').attr('checked', this.checked);
	});
	
	$('#checkall_filtro_suporte_tipo_atendimento').click(function () {
	$(this).parents('fieldset:eq(0)').find(':checkbox').attr('checked', this.checked);
	});
	
	$('#checkall_filtro_suporte_tipo_recomendacao').click(function () {
	$(this).parents('fieldset:eq(0)').find(':checkbox').attr('checked', this.checked);
	});

	$('#checkall_filtro_solicitacao_tipo').click(function () {
	$(this).parents('fieldset:eq(0)').find(':checkbox').attr('checked', this.checked);
	});
	
	$('#checkall_filtro_solicitacao_status').click(function () {
	$(this).parents('fieldset:eq(0)').find(':checkbox').attr('checked', this.checked);
	});
	
	$('#checkall_filtro_suporte_tipo_visita').click(function () {
	$(this).parents('fieldset:eq(0)').find(':checkbox').attr('checked', this.checked);
	});
	
	$('#checkall_filtro_prospeccao_situacao').click(function () {
	$(this).parents('fieldset:eq(0)').find(':checkbox').attr('checked', this.checked);
	});
	
	$('#checkall_filtro_prospeccao_status').click(function () {
	$(this).parents('fieldset:eq(0)').find(':checkbox').attr('checked', this.checked);
	});

	$('#checkall_filtro_prospeccao_baixa_perda_motivo').click(function () {
	$(this).parents('fieldset:eq(0)').find(':checkbox').attr('checked', this.checked);
	});
	
	$('#checkall_filtro_venda_situacao').click(function () {
	$(this).parents('fieldset:eq(0)').find(':checkbox').attr('checked', this.checked);
	});
	
	$('#checkall_filtro_venda_modulos').click(function () {
	$(this).parents('fieldset:eq(0)').find(':checkbox').attr('checked', this.checked);
	});
	// fim - marcar todos

	// **************************** (filtro padrão)
	$("select[id=relatorio_id]").change(function(){
		
		var relatorio_id_atual = $(this).val();
		
		// filtro_suporte_situacao
		<? if($row_relatorio['filtro_suporte_situacao'] == 0){ ?>
			if(relatorio_id_atual == 1 || relatorio_id_atual == 3){ // 1: Clientes ativos de manutenção atendidos no mês / 3: Atendimentos extra contrato
				$('#form_filtro').append('<input class="especial" name="filtro_suporte_situacao[]" type="hidden" value="solucionada" checked="checked" />');
			} else {
				$('.especial').remove();
			}
		<? } ?>
		// fim - filtro_suporte_situacao
		
	});
	// **************************** (filtro padrão)
			
});
</script>

<? } ?>

<title></title>
</head>

<body>
<? if($tela=="digital"){ ?>

    <!-- cabecalho -->
    <div class="cabecalho">
    
        <div class="cabecalho_titulo">
        <table cellspacing="0" cellpadding="0" width="100%">
            <tr>
                <td style="text-align:left">
                <a href="relatorio.php?padrao=sim&amp;tela=digital&amp;relatorio_id_grupo=1&amp;relatorio_id_grupo_subgrupo=0" style="color: <? if($relatorio_id_grupo==1){ ?>#FFF<? }else{ ?>#D1E3F1<? } ?>">Relatórios de solicitações</a>
                
                <font color="#3399CC"> | </font>
                
                <a href="relatorio.php?padrao=sim&amp;tela=digital&amp;relatorio_id_grupo=2&amp;relatorio_id_grupo_subgrupo=0" style="color: <? if($relatorio_id_grupo==2){ ?>#FFF<? }else{ ?>#D1E3F1<? } ?>">Relatórios de suportes</a>
                
                <font color="#3399CC"> | </font>
                
                <a href="relatorio.php?padrao=sim&amp;tela=digital&amp;relatorio_id_grupo=3&amp;relatorio_id_grupo_subgrupo=0" style="color: <? if($relatorio_id_grupo==3){ ?>#FFF<? }else{ ?>#D1E3F1<? } ?>">Relatórios de prospecções</a>
                
                <font color="#3399CC"> | </font>
                
                <a href="relatorio.php?padrao=sim&amp;tela=digital&amp;relatorio_id_grupo=4&amp;relatorio_id_grupo_subgrupo=0" style="color: <? if($relatorio_id_grupo==4){ ?>#FFF<? }else{ ?>#D1E3F1<? } ?>">Relatórios de vendas</a>

                <font color="#3399CC"> | </font>

                <a href="relatorio.php?padrao=sim&amp;tela=digital&amp;relatorio_id_grupo=5&amp;relatorio_id_grupo_subgrupo=0" style="color: <? if($relatorio_id_grupo==5){ ?>#FFF<? }else{ ?>#D1E3F1<? } ?>">Relatórios administrativos</a> 
                
                <? if($row_usuario['controle_relatorio']=='Y' or $row_usuario['controle_praca']=='Y'){ ?>
                <font color="#3399CC"> | </font>
                
                <a href="relatorio.php?padrao=sim&amp;tela=digital&amp;relatorio_id_grupo=0&amp;relatorio_id_grupo_subgrupo=0&amp;filtro_geral_praca=<? if($row_usuario['controle_relatorio'] != "Y"){ echo $row_usuario['praca']; } ?>&amp;filtro_geral_data_criacao=<? echo date('01-m-Y'); ?>&amp;filtro_geral_data_criacao_fim=<? echo date('t-m-Y'); ?>" style="color: <? if($relatorio_id_grupo==NULL){ ?>#FFF<? }else{ ?>#D1E3F1<? } ?>">Resultados mensais</a>
                <? } ?>
                
                </td>
        
                <td style="text-align: right">
                &lt;&lt; <a href="index.php">Voltar</a> | 
                Usuário logado: <? echo $row_usuario['nome']; ?> |
                <a href="painel/padrao_sair.php">Sair</a>
                </td>
            </tr>
        </table>
        </div>
    
    </div>
    <!-- fim - cabecalho -->
    
    <? if($totalRows_relatorio > 0){ ?>
    <!-- imprimir -->
    <div class="imprimir" style="border: 1px solid #4297d7; padding: 3px; margin-bottom: 5px; cursor: pointer; text-align: center; font-size: 14px; font-weight: bold;">
    <table border="0" cellspacing="0" cellpadding="0" align="center">
      <tr>
        <td><a href="relatorio.php?<? echo str_replace("tela=digital","tela=impressao", $_SERVER['QUERY_STRING']); ?>" target="_blank" style="text-decoration: none; color: #000;">Imprimir</a></td>
      </tr>
    </table>
    </div>
    <!-- fim - imprimir -->
    <? } ?>
    
    <? include('relatorio_filtros.php'); ?> 
    
    <? if($relatorio_id_grupo == NULL and $tela == "digital"){ // geral e digital ?>
    <!-- relatorio_fechamento ------------------------------------------------------------------------------------------ -->
    <div class="filtro_geral_titulo" id="cabecalho_relatorio_fechamento" style="cursor: pointer">
    <table cellspacing="0" cellpadding="0" width="100%">
        <tr>
            <td style="text-align:left">
            Relatórios Gerenciais Success Sistemas (<? echo $totalRows_relatorio_fechamento; ?>)
            </td>
            
            <td style="text-align: right">
            <div style="float: right; background-image:url(imagens/icone_filtro.png); width: 12px; height: 12px;"></div>
            </td>
        </tr>
    </table>
    </div>
    
	<? if($totalRows_relatorio_fechamento > 0){ ?>    
        <div id="corpo_relatorio_fechamento" style="cursor: pointer; margin-bottom: 20px;">
        <table id="relatorio_fechamento"></table>
        <div id="relatorio_fechamento_navegacao"></div>
        <script type="text/javascript">
        var dados = [		 
        <?php do { ?>
        
        <?
        // status ------------------------------------------------------------------------------------------------------------------------------------
        $cor_css = "cor_black";
        
        if($row_relatorio_fechamento['status'] == 0){
            $cor_css = "cor_red";
        }
        // fim - status ------------------------------------------------------------------------------------------------------------------------------
        ?>
        
        {
            id:"<?php echo $row_relatorio_fechamento['id']; ?>",
            data_criacao:"<?php echo $row_relatorio_fechamento['data_criacao']; ?>",
            data:"<?php echo $row_relatorio_fechamento['data']; ?>",
            <? if($acesso == 1){ ?>
            praca:"<?php echo $row_relatorio_fechamento['praca']; ?>",
            <? } ?>
            usuario_responsavel:"<?php echo $row_relatorio_fechamento['usuario_responsavel']; ?>",
            arquivo:"<?php echo $row_relatorio_fechamento['arquivo']; ?>",
            visualizar:"<? echo "<a href='relatorio/".$row_relatorio_fechamento['arquivo']."' target='_blank'><img src='imagens/visualizar.png' border='0' /></a>"; ?>"
        },
        <?php } while ($row_relatorio_fechamento = mysql_fetch_assoc($relatorio_fechamento)); ?>
        ];
        jQuery('#relatorio_fechamento').jqGrid({
            data:dados,
            datatype: 'local',
            colNames:['Núm','Data criação','Competência'<? if($acesso == 1){ ?>,'Praça'<? } ?>,'Responsável','Arquivo',''],
            colModel :[ 
                {name:'id', index:'id', width:30, sorttype: 'integer'}, 
                {name:'data_criacao', index:'data_criacao', width:70, formatter:'date', formatoptions: { srcformat: "ISO8601Long", newformat: "d-m-Y H:i" }, align:'center' },
                {name:'data', index:'data', width:70, formatter:'date', formatoptions:{srcformat:'ISO8601Long', newformat:'m-Y'}, align:'center' },
                <? if($acesso == 1){ ?>
                {name:'praca', index:'praca', width: 80, align:'center'},
                <? } ?>
                {name:'usuario_responsavel', index:'usuario_responsavel', width:70, align:'left'}, 
                {name:'arquivo', index:'arquivo'}, 
                {name:'visualizar', index:'visualizar', width:20, align:'center'} 
            ],
            rowNum:20,
            rowList:[2,5,10,20,30,40,50,100,999999],
                        loadComplete: function() {
                            $("option[value=999999]").text('Todos');
                        },
            pager: '#relatorio_fechamento_navegacao',
            //sortname: 'id',
            viewrecords: true,
            //sortorder: 'desc',
            toppager: true, // aparecer a barra de navegação também no topo
            // caption:"Suportes", barra no topo
            autowidth: true,
            height: "100%",
        
            ondblClickRow: function(id){
                top.location.href="relatorio_fechamento_editar.php?id_relatorio_fechamento="+id;
            }		
        });
        </script>
        </div>
    <? } else { ?>
        <div id="corpo_relatorio_fechamento" style="border: 1px solid #c5dbec; font-size: 11px; padding: 5px; margin-bottom: 20px;">
        Nenhum relatório encontrado.
        </div>
    <? } ?>
    <!-- fim - relatorio_fechamento ------------------------------------------------------------------------------------ -->
	<? } ?>

<? } ?>
<? 

// relatorio ------------------------------------------------------------------
if ($totalRows_relatorio > 0){

if($tela=="impressao"){
	$mpdf = new mPDF('utf-8', 'A4', '', '', 15, 15, 15, 15, 5, 7); 
	$mpdf->charset_in='UTF-8';
	$stylesheet = file_get_contents('css/guia_registro2.css');
}

// titulo_geral
$titulo_geral = "
<table class='titulo' width='100%' border='0' cellpadding='0' cellspacing='0' style='border-spacing: 0;'>
<tr>
	<td colspan='3'> Relatório Gerencial Success Sistemas </td>
</tr>
<tr>
	<td width='33%' align='left' style='font-size: 10px; text-align: left;'>&nbsp;</td>
	<td width='34%' style='font-size: 10px; font-weight: normal;'>&nbsp;</td>
	<td width='33%' align='right' style='font-size: 10px;'>
		Página: {PAGENO}
	</td>
</tr>
</table>
	
<div style='height: 1px; background-color: #000; margin-top: 5px; margin-bottom: 5px;'></div>
";
// fim - titulo_geral

// header_geral
if($relatorio_id_grupo == NULL){
	$header_geral = "
	<table class='header' width='100%' border='0' cellspacing='0' cellpadding='0' style='border-spacing: 0;'>
	
	<tr>
		<td width='50%' align='left'><span><strong>Representante:</strong></span></td>
		<td width='50%' align='right'>
			<span><strong>Data: </strong>".$data_atual."</span>
		</td>
	</tr>
	<tr>
		<td align='left'>
			<span><strong>Mês/Ano processamento: </strong>".$mes_ano_atual."</span>
		</td>
		<td align='right'>
			<span><strong>Emitido por:</strong> ".limita_caracteres($row_usuario['nome'], 15)."</span>
			$emissao
		</td>
	</tr>
	<tr>
		<td align='left'><span><strong>CNPJ:</strong></span></td>
		<td align='right'>
			<span><strong>Praça: </strong>".$praca_atual."</span> 
			<span style='padding-left: 20px;'><strong>Responsável:</strong></span>
		</td>
	</tr>
	
	</table>
	<div style='height: 1px; background-color: #000; margin-top: 5px; margin-bottom: 5px;'></div>
	";
	
}
// fim - header_geral

$contador_relatorio = 0;
$geral = NULL;
$teste = 0;
$totalizar_contrato_ativo_retorno = 0;

do {
	
	$contador_relatorio ++;

    // relatorio_campos
    mysql_select_db($database_conexao, $conexao);
    $query_relatorio_campos = sprintf("SELECT * FROM relatorio_campos WHERE id_relatorio = %s ORDER BY ordem ASC, id ASC", GetSQLValueString($row_relatorio['id'], "int"));
    $relatorio_campos = mysql_query($query_relatorio_campos, $conexao) or die(mysql_error());
    $row_relatorio_campos = mysql_fetch_assoc($relatorio_campos);
    $totalRows_relatorio_campos = mysql_num_rows($relatorio_campos);
    // fim - relatorio_campos
    
    $where = " 1=1 ";

	$relatorio_tipo = $row_relatorio['relatorio_tipo'];
	
	$relatorio_titulo = utf8_encode($row_relatorio['titulo']);
	$relatorio_grupo_geral_titulo = utf8_encode($row_relatorio['relatorio_grupo_geral_titulo']);
	$relatorio_grupo_titulo = utf8_encode($row_relatorio['relatorio_grupo_titulo']);
	$relatorio_grupo_subgrupo_titulo = utf8_encode($row_relatorio['relatorio_grupo_subgrupo_titulo']);
	
    $filtro_geral_data_criacao_atual = NULL; 
	if($row_relatorio['where_data'] == NULL){
		$filtro_geral_data_criacao_atual = '01-01-1990';
	} else if ( isset($_GET['filtro_geral_data_criacao']) and $_GET['filtro_geral_data_criacao']!="" ){ 
		$filtro_geral_data_criacao_atual = $_GET['filtro_geral_data_criacao'];
	}
	
    $filtro_geral_data_criacao_fim_atual = NULL;
	if($row_relatorio['where_data'] == NULL){
		$filtro_geral_data_criacao_fim_atual = date('d-m-Y');
	} else if ( isset($_GET['filtro_geral_data_criacao_fim']) and $_GET['filtro_geral_data_criacao_fim']!="" ){ 
		$filtro_geral_data_criacao_fim_atual = $_GET['filtro_geral_data_criacao_fim'];
	}
	
	// filtro_geral_praca_atual
	$filtro_geral_praca_atual = NULL; 
	if($acesso == 1){
		if(isset($_GET['filtro_geral_praca']) and $_GET['filtro_geral_praca'] != ""){
			$filtro_geral_praca_atual = $_GET['filtro_geral_praca']; 
		}
	} else {
		$filtro_geral_praca_atual = $_SESSION['MM_praca']; 
	}
	// fim - filtro_geral_praca_atual

	// filtro_geral_cliente_atual
	$filtro_geral_cliente_atual = NULL; 
	if ( isset($_GET['filtro_geral_cliente']) and $_GET['filtro_geral_cliente']!="" ){ 
		mysql_select_db($database_conexao, $conexao);
		$query_filtro_geral_cliente_nome = sprintf("SELECT nome1 FROM da01 WHERE codigo1 = %s and da01.sr_deleted <> 'T' LIMIT 1", GetSQLValueString($_GET['filtro_geral_cliente'], "text"));
		$filtro_geral_cliente_nome = mysql_query($query_filtro_geral_cliente_nome, $conexao) or die(mysql_error());
		$row_filtro_geral_cliente_nome = mysql_fetch_assoc($filtro_geral_cliente_nome);
		$totalRows_filtro_geral_cliente_nome = mysql_num_rows($filtro_geral_cliente_nome);
		$filtro_geral_cliente_atual = $row_filtro_geral_cliente_nome['nome1']; 
		mysql_free_result($filtro_geral_cliente_nome);
	}
	// fim - filtro_geral_cliente_atual	
	
	// filtro_geral_usuario_atual
	$filtro_geral_usuario_atual = NULL; 
	if ( $row_relatorio['filtro_geral_usuario'] == 1 and (isset($_GET['filtro_geral_usuario']) and $_GET['filtro_geral_usuario']!="") ){ 
		$filtro_geral_usuario_atual = funcaoConsultaUsuarioNome($_GET['filtro_geral_usuario']); 
	}
	// fim - filtro_geral_usuario_atual	

	// filtro_geral_usuario_area_atual
	$filtro_geral_usuario_area_atual = NULL; 
	if ( $row_relatorio['filtro_geral_usuario_area'] == 1 and (isset($_GET['filtro_geral_usuario_area']) and $_GET['filtro_geral_usuario_area']!="") ){ 
		if($_GET['filtro_geral_usuario_area'] == "a"){
			$filtro_geral_usuario_area_atual = "Administrativo";
		} else if($_GET['filtro_geral_usuario_area'] == "o"){
			$filtro_geral_usuario_area_atual = "Operacional";
		}
	}
	// fim - filtro_geral_usuario_area_atual	

	// filtro_suporte_situacao_atual
	$filtro_suporte_situacao_atual = NULL; 
	if ( isset($_GET['filtro_suporte_situacao']) and $_GET['filtro_suporte_situacao']!="" ){
	
		// contar quantidade de situacões atual
		$contador = 0;
		foreach($_GET["filtro_suporte_situacao"] as $filtro_suporte_situacao){
			$contador++;
			if($contador<>count($_GET["filtro_suporte_situacao"])){$virgula=", ";}else{$virgula="";} // se não é a última, então insere OR
			$filtro_suporte_situacao_atual .= $filtro_suporte_situacao.$virgula;
		}
		// fim - contar quantidade de situacões atual
		
	}				
	// fim - filtro_suporte_situacao_atual

	// filtro_suporte_tipo_atendimento_atual
	$filtro_suporte_tipo_atendimento_atual = NULL; 
	if ( isset($_GET['filtro_suporte_tipo_atendimento']) and $_GET['filtro_suporte_tipo_atendimento']!="" ){ 
		// contar quantidade de situacões atual
		$contador = 0;
		foreach($_GET["filtro_suporte_tipo_atendimento"] as $value){
			$contador++;
			if($contador<>count($_GET["filtro_suporte_tipo_atendimento"])){$virgula=", ";}else{$virgula="";} // se não é a última, então insere OR
			$filtro_suporte_tipo_atendimento_atual .= $value.$virgula;
		}
		// fim - contar quantidade de situacões atual
	}				
	// fim - filtro_suporte_tipo_atendimento_atual

	// filtro_suporte_tipo_recomendacao_atual
	$filtro_suporte_tipo_recomendacao_atual = NULL; 
	if ( isset($_GET['filtro_suporte_tipo_recomendacao']) and $_GET['filtro_suporte_tipo_recomendacao']!="" ){ 
		// contar quantidade de situacões atual
		$contador = 0;
		foreach($_GET["filtro_suporte_tipo_recomendacao"] as $value){
			$contador++;
			if($contador<>count($_GET["filtro_suporte_tipo_recomendacao"])){$virgula=", ";}else{$virgula="";} // se não é a última, então insere OR
			$filtro_suporte_tipo_recomendacao_atual .= $value.$virgula;
		}
		// fim - contar quantidade de situacões atual
	}				
	// fim - filtro_suporte_tipo_recomendacao_atual
	
	// filtro_suporte_tipo_visita_atual
	$filtro_suporte_tipo_visita_atual = NULL; 
	if ( isset($_GET['filtro_suporte_tipo_visita']) and $_GET['filtro_suporte_tipo_visita']!="" ){ 
		// contar quantidade de situacões atual
		$contador = 0;
		foreach($_GET["filtro_suporte_tipo_visita"] as $value){
			$contador++;
			if($contador<>count($_GET["filtro_suporte_tipo_visita"])){$virgula=", ";}else{$virgula="";} // se não é a última, então insere OR
			
			if($value==1){ $value = 'Nenhum'; }
			if($value==2){ $value = 'Sem Limite'; }
			if($value==3){ $value = 'Mensal'; }
			if($value==4){ $value = 'Trimestral'; }
			if($value==5){ $value = 'Sem visita'; }

			$filtro_suporte_tipo_visita_atual .= $value.$virgula;
		}
		// fim - contar quantidade de situacões atual
	}				
	// fim - filtro_suporte_tipo_visita_atual

	// $filtro_suporte_anexo_atual
	$filtro_suporte_anexo_atual = NULL; 
	if ( isset($_GET['filtro_suporte_anexo']) and $_GET['filtro_suporte_anexo']!="" ){ 
		$filtro_suporte_anexo_atual = $_GET['filtro_suporte_anexo'];
		if($filtro_suporte_anexo_atual=='n'){ $filtro_suporte_anexo_atual = 'Não'; }
		if($filtro_suporte_anexo_atual=='s'){ $filtro_suporte_anexo_atual = 'Sim'; }
	}
	// fim - $filtro_suporte_anexo_atual	
			
	$filtro_suporte_solicitante_atual = NULL; 
	if ( isset($_GET['filtro_suporte_solicitante']) and $_GET['filtro_suporte_solicitante']!="" ){ $filtro_suporte_solicitante_atual = funcaoConsultaUsuarioNome($_GET['filtro_suporte_solicitante']); }
	
	$filtro_suporte_atendente_atual = NULL; 
	if ( isset($_GET['filtro_suporte_atendente']) and $_GET['filtro_suporte_atendente']!="" ){ $filtro_suporte_atendente_atual = funcaoConsultaUsuarioNome($_GET['filtro_suporte_atendente']); }
	
	
	$filtro_solicitacao_solicitante_atual = NULL; 
	if ( isset($_GET['filtro_solicitacao_solicitante']) and $_GET['filtro_solicitacao_solicitante']!="" ){ $filtro_solicitacao_solicitante_atual = funcaoConsultaUsuarioNome($_GET['filtro_solicitacao_solicitante']); }
	
	$filtro_solicitacao_executante_atual = NULL; 
	if ( isset($_GET['filtro_solicitacao_executante']) and $_GET['filtro_solicitacao_executante']!="" ){ $filtro_solicitacao_executante_atual = funcaoConsultaUsuarioNome($_GET['filtro_solicitacao_executante']); }
	
	$filtro_solicitacao_operador_atual = NULL; 
	if ( isset($_GET['filtro_solicitacao_operador']) and $_GET['filtro_solicitacao_operador']!="" ){ $filtro_solicitacao_operador_atual = funcaoConsultaUsuarioNome($_GET['filtro_solicitacao_operador']); }
	
	$filtro_solicitacao_testador_atual = NULL; 
	if ( isset($_GET['filtro_solicitacao_testador']) and $_GET['filtro_solicitacao_testador']!="" ){ $filtro_solicitacao_testador_atual = funcaoConsultaUsuarioNome($_GET['filtro_solicitacao_testador']); }
	
	// filtro_solicitacao_tipo_atual
	$filtro_solicitacao_tipo_atual = NULL; 
	if ( isset($_GET['filtro_solicitacao_tipo']) and $_GET['filtro_solicitacao_tipo']!="" ){ 
		// contar quantidade de situacões atual
		$contador = 0;
		foreach($_GET["filtro_solicitacao_tipo"] as $value){
			$contador++;
			if($contador<>count($_GET["filtro_solicitacao_tipo"])){$virgula=", ";}else{$virgula="";} // se não é a última, então insere OR
			$filtro_solicitacao_tipo_atual .= $value.$virgula;
		}
		// fim - contar quantidade de situacões atual
	}				
	// fim - filtro_solicitacao_tipo_atual

	// $filtro_solicitacao_desmembrada_atual
	$filtro_solicitacao_desmembrada_atual = NULL; 
	if ( isset($_GET['filtro_solicitacao_desmembrada']) and $_GET['filtro_solicitacao_desmembrada']!="" ){ 
		$filtro_solicitacao_desmembrada_atual = $_GET['filtro_solicitacao_desmembrada'];
		if($filtro_solicitacao_desmembrada_atual=='n'){ $filtro_solicitacao_desmembrada_atual = 'Não'; }
		if($filtro_solicitacao_desmembrada_atual=='s'){ $filtro_solicitacao_desmembrada_atual = 'Sim'; }
	}
	// fim - $filtro_solicitacao_desmembrada_atual	

	// filtro_solicitacao_status_atual
	$filtro_solicitacao_status_atual = NULL; 
	if ( isset($_GET['filtro_solicitacao_status']) and $_GET['filtro_solicitacao_status']!="" ){ 
	
		// contar quantidade de situacões atual
		$contador = 0;
		foreach($_GET["filtro_solicitacao_status"] as $filtro_solicitacao_status){
			$contador++;
			if($contador<>count($_GET["filtro_solicitacao_status"])){$virgula=", ";}else{$virgula="";} // se não é a última, então insere OR
			$filtro_solicitacao_status_atual .= $filtro_solicitacao_status.$virgula;
		}
		// fim - contar quantidade de situacões atual
	}				
	// fim - filtro_solicitacao_status_atual	

	// filtro_prospeccao_situacao_atual
	$filtro_prospeccao_situacao_atual = NULL; 
	if ( isset($_GET['filtro_prospeccao_situacao']) and $_GET['filtro_prospeccao_situacao']!="" ){ 
	
		// contar quantidade de situacões atual
		$contador = 0;
		foreach($_GET["filtro_prospeccao_situacao"] as $filtro_prospeccao_situacao){
			$contador++;
			if($contador<>count($_GET["filtro_prospeccao_situacao"])){$virgula=", ";}else{$virgula="";} // se não é a última, então insere OR
			$filtro_prospeccao_situacao_atual .= $filtro_prospeccao_situacao.$virgula;
		}
		// fim - contar quantidade de situacões atual
	}				
	// fim - filtro_prospeccao_situacao_atual
	
	// filtro_prospeccao_status_atual
	$filtro_prospeccao_status_atual = NULL; 
	if ( isset($_GET['filtro_prospeccao_status']) and $_GET['filtro_prospeccao_status']!="" ){ 
	
		// contar quantidade de situacões atual
		$contador = 0;
		foreach($_GET["filtro_prospeccao_status"] as $filtro_prospeccao_status){
			$contador++;
			if($contador<>count($_GET["filtro_prospeccao_status"])){$virgula=", ";}else{$virgula="";} // se não é a última, então insere OR
			$filtro_prospeccao_status_atual .= $filtro_prospeccao_status.$virgula;
		}
		// fim - contar quantidade de situacões atual
	}				
	// fim - filtro_prospeccao_status_atual
	
	// filtro_prospeccao_usuario_responsavel_atual
	$filtro_prospeccao_usuario_responsavel_atual = NULL; 
	if ( isset($_GET['filtro_prospeccao_usuario_responsavel']) and $_GET['filtro_prospeccao_usuario_responsavel']!="" ){ $filtro_prospeccao_usuario_responsavel_atual = funcaoConsultaUsuarioNome($_GET['filtro_prospeccao_usuario_responsavel']); }
	// fim - filtro_prospeccao_usuario_responsavel_atual
	
	// $filtro_prospeccao_tipo_cliente_atual
	$filtro_prospeccao_tipo_cliente_atual = NULL; 
	if ( isset($_GET['filtro_prospeccao_tipo_cliente']) and $_GET['filtro_prospeccao_tipo_cliente']!="" ){ 
		$filtro_prospeccao_tipo_cliente_atual = $_GET['filtro_prospeccao_tipo_cliente'];
		if($filtro_prospeccao_tipo_cliente_atual=='n'){ $filtro_prospeccao_tipo_cliente_atual = 'Novo'; }
		if($filtro_prospeccao_tipo_cliente_atual=='a'){ $filtro_prospeccao_tipo_cliente_atual = 'Antigo'; }
	}
	// fim - $filtro_prospeccao_tipo_cliente_atual
	
	// $filtro_prospeccao_ativo_passivo_atual
	$filtro_prospeccao_ativo_passivo_atual = NULL; 
	if ( isset($_GET['filtro_prospeccao_ativo_passivo']) and $_GET['filtro_prospeccao_ativo_passivo']!="" ){ 
		$filtro_prospeccao_ativo_passivo_atual = $_GET['filtro_prospeccao_ativo_passivo'];
		if($filtro_prospeccao_ativo_passivo_atual=='p'){ $filtro_prospeccao_ativo_passivo_atual = 'Passivo'; }
		if($filtro_prospeccao_ativo_passivo_atual=='a'){ $filtro_prospeccao_ativo_passivo_atual = 'Ativo'; }
	}
	// fim - $filtro_prospeccao_ativo_passivo_atual

	// $filtro_prospeccao_baixa_perda_motivo_atual
	$filtro_prospeccao_baixa_perda_motivo_atual = NULL; 
	if ( isset($_GET['filtro_prospeccao_baixa_perda_motivo']) and $_GET['filtro_prospeccao_baixa_perda_motivo']!="" ){ 
		$filtro_prospeccao_baixa_perda_motivo_atual = $_GET['filtro_prospeccao_baixa_perda_motivo'];
		if($filtro_prospeccao_baixa_perda_motivo_atual=='concorrência'){ $filtro_prospeccao_baixa_perda_motivo_atual = 'concorrência'; }
		if($filtro_prospeccao_baixa_perda_motivo_atual=='falta de recurso'){ $filtro_prospeccao_baixa_perda_motivo_atual = 'falta de recurso'; }
	}
	// fim - $filtro_prospeccao_baixa_perda_motivo_atual

	// filtro_venda_situacao_atual
	$filtro_venda_situacao_atual = NULL; 
	if ( isset($_GET['filtro_venda_situacao']) and $_GET['filtro_venda_situacao']!="" ){ 
	
		// contar quantidade de situacões atual
		$contador = 0;
		foreach($_GET["filtro_venda_situacao"] as $filtro_venda_situacao){
			$contador++;
			if($contador<>count($_GET["filtro_venda_situacao"])){$virgula=", ";}else{$virgula="";} // se não é a última, então insere OR
			$filtro_venda_situacao_atual .= $filtro_venda_situacao.$virgula;
		}
		// fim - contar quantidade de situacões atual
	}				
	// fim - filtro_venda_situacao_atual
	
	// filtro_venda_usuario_responsavel_atual
	$filtro_venda_usuario_responsavel_atual = NULL; 
	if ( isset($_GET['filtro_venda_usuario_responsavel']) and $_GET['filtro_venda_usuario_responsavel']!="" ){ $filtro_venda_usuario_responsavel_atual = funcaoConsultaUsuarioNome($_GET['filtro_venda_usuario_responsavel']); }
	// fim - filtro_venda_usuario_responsavel_atual

	// $filtro_venda_tipo_cliente_atual
	$filtro_venda_tipo_cliente_atual = NULL; 
	if ( isset($_GET['filtro_venda_tipo_cliente']) and $_GET['filtro_venda_tipo_cliente']!="" ){ 
		$filtro_venda_tipo_cliente_atual = $_GET['filtro_venda_tipo_cliente'];
		if($filtro_venda_tipo_cliente_atual=='n'){ $filtro_venda_tipo_cliente_atual = 'Novo'; }
		if($filtro_venda_tipo_cliente_atual=='a'){ $filtro_venda_tipo_cliente_atual = 'Antigo'; }
	}
	// fim - $filtro_venda_tipo_cliente_atual

	// filtro_venda_modulos_atual
	$filtro_venda_modulos_atual = NULL; 
	if ( isset($_GET['filtro_venda_modulos']) and $_GET['filtro_venda_modulos']!="" ){ 
		// contar quantidade de situacões atual
		$contador = 0;
		foreach($_GET["filtro_venda_modulos"] as $value){
			$contador++;
			if($contador<>count($_GET["filtro_venda_modulos"])){$virgula=", ";}else{$virgula="";} // se não é a última, então insere OR
			$filtro_venda_modulos_atual .= $value.$virgula;
		}
		// fim - contar quantidade de situacões atual
	}				
	// fim - filtro_venda_modulos_atual	
	
	// filtro_administrativo_atraso_atual
	$filtro_administrativo_atraso_atual = 1;
	if ( isset($_GET['filtro_administrativo_atraso']) and $_GET['filtro_administrativo_atraso']!=""){ 
		$filtro_administrativo_atraso_atual = $_GET['filtro_administrativo_atraso']; 
	}
	// fim - filtro_administrativo_atraso_atual
	
    // filtros -----------------------------------------------------------------------------------------------------------------------------------------------------------
	
    // filtro_geral_praca ****************************************************
	$where_praca = $row_relatorio['where_praca'];
	
	if($where_praca <> NULL){
		if($acesso == 1){
	
			if( (isset($_GET["filtro_geral_praca"])) && ($_GET['filtro_geral_praca'] !="") ) {
				
				$colname_suporte_filtro_geral_praca = GetSQLValueString($_GET["filtro_geral_praca"], "string");
				$where .= " and $where_praca = '$colname_suporte_filtro_geral_praca' "; 	
				$where_campos[] = "filtro_geral_praca";
			}
			
		} else {
			
			$colname_suporte_filtro_geral_praca = GetSQLValueString($_SESSION['MM_praca'], "string");
			$where .= " and $where_praca = '$colname_suporte_filtro_geral_praca' "; 	
			$where_campos[] = "filtro_geral_praca";		
			
		}
	}
    // fim - se existe filtro de filtro_geral_praca **************************

	// se existe filtro de filtro_geral_data_criacao *************************	
		// converter 'data_criacao' em português para inglês
		if ( isset($_GET["filtro_geral_data_criacao"]) ) {
			$filtro_geral_data_criacao_data = substr($_GET["filtro_geral_data_criacao"],0,10);
			$filtro_geral_data_criacao_hora = " 00:00:00";
			$filtro_geral_data_criacao = implode("-",array_reverse(explode("-",$filtro_geral_data_criacao_data))).$filtro_geral_data_criacao_hora;
			$where_campos[] = "filtro_geral_data_criacao";
		}
		// fim - converter 'data_criacao' em português para inglês
		
		// converter 'data_criacao_fim' em português para inglês
		if ( isset($_GET["filtro_geral_data_criacao_fim"]) ) {
			$filtro_geral_data_criacao_fim_data = substr($_GET["filtro_geral_data_criacao_fim"],0,10);
			$filtro_geral_data_criacao_fim_hora = " 23:59:59";
			$filtro_geral_data_criacao_fim = implode("-",array_reverse(explode("-",$filtro_geral_data_criacao_fim_data))).$filtro_geral_data_criacao_fim_hora;
			$where_campos[] = "filtro_geral_data_criacao_fim";
		}
		// fim - converter 'data_criacao_fim' em português para inglês
						
		$colname_suporte_filtro_geral_data_criacao = GetSQLValueString($filtro_geral_data_criacao, "string");
		$colname_suporte_filtro_geral_data_criacao_fim = GetSQLValueString($filtro_geral_data_criacao_fim, "string");
		
		// where_data
		$where_data = " 1=1 ";
		if($row_relatorio['where_data'] != NULL){
			
			$where_data = $row_relatorio['where_data']; 
			
			// where_data_vazio
			$where_data_vazio = NULL;
			if($row_relatorio['where_data_vazio']=='s'){
				$where_data_vazio = " or data_suporte IS NULL";
			}
			// fim - where_data_vazio
					
			if($row_relatorio['where_data_periodo']=='c'){ // corrente
			
				// se filtro_geral_data_criacao/filtro_geral_data_criacao_fim ( entre data inicial e data final )
				if( 
				$where_data <> 'especial' and 
				((isset($_GET["filtro_geral_data_criacao"])) && ($_GET["filtro_geral_data_criacao"] != "")) && ((isset($_GET["filtro_geral_data_criacao_fim"])) && ($_GET["filtro_geral_data_criacao_fim"] != "")) 
				) {
					
					if($row_relatorio['where_data_atual'] == "s"){ // verifica apenas o DIA e MES da data informada no filtro		

						$where .= " and (
						DATE_FORMAT($where_data, '".date('Y', strtotime($colname_suporte_filtro_geral_data_criacao))."-%m-%d') >= '".date('Y-m-d', strtotime($colname_suporte_filtro_geral_data_criacao))."' AND
						DATE_FORMAT($where_data, '".date('Y', strtotime($colname_suporte_filtro_geral_data_criacao))."-%m-%d') <= '".date('Y-m-d', strtotime($colname_suporte_filtro_geral_data_criacao_fim))."'
						)"; // usa a 'data inicial' como ANO base para a verificação
						
					} else {
						
						$where .= " and (($where_data between '$colname_suporte_filtro_geral_data_criacao' and '$colname_suporte_filtro_geral_data_criacao_fim')".$where_data_vazio.") ";
						
					}
					
				}
				// fim - filtro_geral_data_criacao/filtro_geral_data_criacao_fim ( entre data inicial e data final )
			
			} else if($row_relatorio['where_data_periodo']=='a'){ // anterior
				
				// se filtro_geral_data_criacao/filtro_geral_data_criacao_fim ( entre data inicial e data final )
				if( 
				$where_data <> 'especial' and 
				((isset($_GET["filtro_geral_data_criacao"])) && ($_GET["filtro_geral_data_criacao"] != "")) && ((isset($_GET["filtro_geral_data_criacao_fim"])) && ($_GET["filtro_geral_data_criacao_fim"] != "")) 
				) {
	
					$where .= " and (($where_data < '$colname_suporte_filtro_geral_data_criacao' or $where_data > '$colname_suporte_filtro_geral_data_criacao_fim')".$where_data_vazio.") ";
					
				}
				// fim - filtro_geral_data_criacao/filtro_geral_data_criacao_fim ( entre data inicial e data final )
				
			}

			// where_data_especial
			if($row_relatorio['where_data'] == 'especial'){

				$where .= str_replace(
					array('{colname_suporte_filtro_geral_data_criacao}', '{colname_suporte_filtro_geral_data_criacao_fim}'), 
					array($colname_suporte_filtro_geral_data_criacao, $colname_suporte_filtro_geral_data_criacao_fim), 
					$row_relatorio['where_data_especial']
				);

			}
			// fim - where_data_especial
			
		}
		// where_data
		
		// where_data2
		$where_data2 = " 1=1 ";
		if($row_relatorio['where_data2']!=NULL){
			
			$where_data2 = $row_relatorio['where_data2'];

			// where_data2_vazio
			$where_data2_vazio = NULL;
			if($row_relatorio['where_data2_vazio']=='s'){
				$where_data2_vazio = " or data_suporte IS NULL";
			}
			// fim - where_data_vazio
					
			if($row_relatorio['where_data2_periodo']=='c'){ // conrrente
			
				// se filtro_geral_data_criacao/filtro_geral_data_criacao_fim ( entre data inicial e data final )
				if( 
				$where_data2 <> 'especial' and 
				((isset($_GET["filtro_geral_data_criacao"])) && ($_GET["filtro_geral_data_criacao"] != "")) && ((isset($_GET["filtro_geral_data_criacao_fim"])) && ($_GET["filtro_geral_data_criacao_fim"] != "")) 
				) {
					
					$where .= " and (($where_data2 between '$colname_suporte_filtro_geral_data_criacao' and '$colname_suporte_filtro_geral_data_criacao_fim')".$where_data2_vazio.") ";
					
				}
				// fim - filtro_geral_data_criacao/filtro_geral_data_criacao_fim ( entre data inicial e data final )
			
			} else if($row_relatorio['where_data2_periodo']=='a'){ // anterior
				
				// se filtro_geral_data_criacao/filtro_geral_data_criacao_fim ( entre data inicial e data final )
				if( 
				$where_data2 <> 'especial' and 
				((isset($_GET["filtro_geral_data_criacao"])) && ($_GET["filtro_geral_data_criacao"] != "")) && ((isset($_GET["filtro_geral_data_criacao_fim"])) && ($_GET["filtro_geral_data_criacao_fim"] != "")) 
				) {
	
					$where .= " and (($where_data2 < '$colname_suporte_filtro_geral_data_criacao' or $where_data2 > '$colname_suporte_filtro_geral_data_criacao_fim')".$where_data2_vazio.") ";
					
				}
				// fim - filtro_geral_data_criacao/filtro_geral_data_criacao_fim ( entre data inicial e data final )
				
			}
			
		}
		// where_data2

	// fim - se existe filtro de filtro_geral_data_criacao *******************
	    
    // se existe filtro_geral_cliente ****************************************
	if($row_relatorio['filtro_geral_cliente'] == 1){
		
		if(isset($_GET["filtro_geral_cliente"]) && ($_GET['filtro_geral_cliente'] !="")){
			
			$colname_suporte_filtro_geral_cliente = $_GET['filtro_geral_cliente'];
			
			if($row_relatorio['filtro_geral_cliente_where'] != NULL){
				
				$where .= " and ".$row_relatorio['filtro_geral_cliente_where']." = '".$colname_suporte_filtro_geral_cliente."' ";
				
			} else {
				
				$where .= " and $relatorio_tipo.codigo_empresa = '".$colname_suporte_filtro_geral_cliente."' ";
				
			}
			
			$where_campos[] = "filtro_geral_cliente";

		}

	} else if($row_relatorio['filtro_geral_cliente'] == 0){
		
		if(isset($_GET["filtro_geral_cliente"]) && ($_GET['filtro_geral_cliente'] !="")){
			$where .= " and 1 = 2 ";		
		}
		
	}	
    // fim - se existe filtro_geral_cliente **********************************
	
    // se existe filtro_geral_usuario ****************************************
	if($row_relatorio['filtro_geral_usuario'] == 1){

		if( (isset($_GET["filtro_geral_usuario"])) && ($_GET['filtro_geral_usuario'] !="") ) {

			if($row_relatorio['filtro_geral_usuario_where'] == "envolvido"){ // verifica a 'situação' atual e qual é o 'usuário' responsável por aquela etapa
				
				$colname_suporte_filtro_geral_usuario = $_GET['filtro_geral_usuario'];

				$where .= " and 
				(
				CASE solicitacao.situacao 
				WHEN 'criada' THEN solicitacao.id_usuario_responsavel = '".$colname_suporte_filtro_geral_usuario."'
				WHEN 'recebida' THEN solicitacao.id_operador = '".$colname_suporte_filtro_geral_usuario."'
				WHEN 'em análise' THEN solicitacao.id_operador = '".$colname_suporte_filtro_geral_usuario."'
				WHEN 'analisada' THEN solicitacao.id_operador = '".$colname_suporte_filtro_geral_usuario."'
				WHEN 'em orçamento' THEN solicitacao.id_analista_orcamento = '".$colname_suporte_filtro_geral_usuario."'
				WHEN 'aprovada' THEN solicitacao.id_executante = '".$colname_suporte_filtro_geral_usuario."'
				WHEN 'em execução' THEN solicitacao.id_executante = '".$colname_suporte_filtro_geral_usuario."'
				WHEN 'executada' THEN solicitacao.id_testador = '".$colname_suporte_filtro_geral_usuario."'
				WHEN 'em testes' THEN solicitacao.id_testador = '".$colname_suporte_filtro_geral_usuario."'
				WHEN 'em validação' THEN solicitacao.id_usuario_responsavel = '".$colname_suporte_filtro_geral_usuario."'
				ELSE NULL  
				END
				) ";

			} else if($row_relatorio['filtro_geral_usuario_where'] != NULL){
				
				$filtro_geral_usuario_where_array = explode(',', $row_relatorio['filtro_geral_usuario_where']);
				
				$contador_filtro_geral_usuario = 0;
				$contador_filtro_geral_usuario_atual = 0;
				if( (isset($filtro_geral_usuario_where_array)) && ($filtro_geral_usuario_where_array !="") ) {
						// contar quantidade de situacões atual
						foreach($filtro_geral_usuario_where_array as $filtro_geral_usuario_key){
								$contador_filtro_geral_usuario = $contador_filtro_geral_usuario + 1;
						}
						// fim - contar quantidade de situacões atual
				
						$query_filtro_geral_usuario=" and ( ";
						foreach($filtro_geral_usuario_where_array as $filtro_geral_usuario_key){
								$contador_filtro_geral_usuario_atual = $contador_filtro_geral_usuario_atual + 1; // verifica o contador atual
								$contador_total = $contador_filtro_geral_usuario - $contador_filtro_geral_usuario_atual; // calcula diferença de situações total - situação atual
								if($contador_total<>0){$or=" or ";}else{$or="";} // se não é a última, então insere OR
								$query_filtro_geral_usuario .= sprintf($filtro_geral_usuario_key." = '".$_GET["filtro_geral_usuario"]."' $or ");				
						}
						$where .= sprintf($query_filtro_geral_usuario)." ) ";	
				}
				
			} else {
				
				$colname_suporte_filtro_geral_usuario = $_GET['filtro_geral_usuario'];
				
				if($relatorio_tipo == 'solicitacao'){
					$where .= " and (
					$relatorio_tipo.id_usuario_responsavel = '".$colname_suporte_filtro_geral_usuario."' or  
					$relatorio_tipo.id_operador = '".$colname_suporte_filtro_geral_usuario."' or 
					$relatorio_tipo.id_executante = '".$colname_suporte_filtro_geral_usuario."' or 
					$relatorio_tipo.id_testador = '".$colname_suporte_filtro_geral_usuario."' or 
					$relatorio_tipo.id_analista_orcamento = '".$colname_suporte_filtro_geral_usuario."' or 
					$relatorio_tipo.id_operador = '".$colname_suporte_filtro_geral_usuario."' 
					) ";
				} else if($relatorio_tipo == 'suporte'){
					$where .= " and (
					$relatorio_tipo.id_usuario_responsavel = '".$colname_suporte_filtro_geral_usuario."' or  
					$relatorio_tipo.id_usuario_envolvido = '".$colname_suporte_filtro_geral_usuario."' 
					) ";
				} else if($relatorio_tipo == 'prospeccao'){
					$where .= " and (
					$relatorio_tipo.id_usuario_responsavel = '".$colname_suporte_filtro_geral_usuario."' 
					) ";
				} else if($relatorio_tipo == 'venda'){
					$where .= " and (
					$relatorio_tipo.id_usuario_responsavel = '".$colname_suporte_filtro_geral_usuario."' 
					) ";
				}
				
			}
			
			$where_campos[] = "filtro_geral_usuario";
			
		}

		if( (isset($_GET["filtro_geral_usuario_area"])) && ($_GET['filtro_geral_usuario_area'] !="") ) {
		
			$colname_suporte_filtro_geral_usuario_area = $_GET['filtro_geral_usuario_area'];
			$where .= " and usuarios.area = '".$colname_suporte_filtro_geral_usuario_area."' ";
			
			$where_campos[] = "filtro_geral_usuario_area";
			
		}
		 
	} else if($row_relatorio['filtro_geral_usuario'] == 0){
		
		if(isset($_GET["filtro_geral_usuario"]) && ($_GET['filtro_geral_usuario'] !="")){
			$where .= " and 1 = 2 ";		
		}
		
	}
	// fim - se existe filtro_geral_usuario **********************************
	
	// se existe filtro de filtro_suporte_situacao
	if($row_relatorio['filtro_suporte_situacao'] == 1){
		
		$contador_filtro_suporte_situacao = 0;
		$contador_filtro_suporte_situacao_atual = 0;
		if( (isset($_GET["filtro_suporte_situacao"])) && ($_GET['filtro_suporte_situacao'] !="") ) {
		
				// contar quantidade de situacões atual
				foreach($_GET["filtro_suporte_situacao"] as $filtro_suporte_situacao){
						$contador_filtro_suporte_situacao = $contador_filtro_suporte_situacao + 1;
				}
				// fim - contar quantidade de situacões atual
		
				$query_filtro_suporte_situacao=" and ( ";
				foreach($_GET["filtro_suporte_situacao"] as $filtro_suporte_situacao){
						$contador_filtro_suporte_situacao_atual = $contador_filtro_suporte_situacao_atual + 1; // verifica o contador atual
						$contador_total = $contador_filtro_suporte_situacao - $contador_filtro_suporte_situacao_atual; // calcula diferença de situações total - situação atual
						if($contador_total<>0){$or=" or ";}else{$or="";} // se não é a última, então insere OR
						$query_filtro_suporte_situacao .= sprintf(" suporte.situacao = '$filtro_suporte_situacao' $or");
		
				}
				$where .= sprintf($query_filtro_suporte_situacao)." ) ";
				$where_campos[] = "filtro_suporte_situacao";
		}
		
	}
	// fim - se existe filtro de filtro_suporte_situacao

	// filtro_suporte_solicitante
	if($row_relatorio['filtro_suporte_solicitante'] == 1 or $row_relatorio['filtro_suporte_atendente'] == 1){
		if( (isset($_GET["filtro_suporte_solicitante"])) && ($_GET['filtro_suporte_solicitante'] !="") ) {
		$colname_suporte_filtro_suporte_solicitante = $_GET['filtro_suporte_solicitante'];
		$where .= " and $relatorio_tipo.id_usuario_envolvido = '".$colname_suporte_filtro_suporte_solicitante."' ";
		$where_campos[] = "filtro_suporte_solicitante";
		} 
	}
    // fim - filtro_suporte_solicitante

    // filtro_suporte_atendente
	if($row_relatorio['filtro_suporte_solicitante'] == 1 or $row_relatorio['filtro_suporte_atendente'] == 1){
		if( (isset($_GET["filtro_suporte_atendente"])) && ($_GET['filtro_suporte_atendente'] !="") ) {
		$colname_suporte_filtro_suporte_atendente = $_GET['filtro_suporte_atendente'];
		$where .= " and $relatorio_tipo.id_usuario_responsavel = '".$colname_suporte_filtro_suporte_atendente."' ";
		$where_campos[] = "filtro_suporte_atendente";
		} 
	}
    // fim - filtro_suporte_atendente
		
	// se existe filtro de filtro_suporte_tipo_atendimento
	if($row_relatorio['filtro_suporte_tipo_atendimento'] == 1){
		
		$contador_filtro_suporte_tipo_atendimento = 0;
		$contador_filtro_suporte_tipo_atendimento_atual = 0;
		if( (isset($_GET["filtro_suporte_tipo_atendimento"])) && ($_GET['filtro_suporte_tipo_atendimento'] !="") ) {
		
				// contar quantidade de situacões atual
				foreach($_GET["filtro_suporte_tipo_atendimento"] as $value){
						$contador_filtro_suporte_tipo_atendimento = $contador_filtro_suporte_tipo_atendimento + 1;
				}
				// fim - contar quantidade de situacões atual
		
				$query_filtro_suporte_tipo_atendimento=" and ( ";
				foreach($_GET["filtro_suporte_tipo_atendimento"] as $value){
						$contador_filtro_suporte_tipo_atendimento_atual = $contador_filtro_suporte_tipo_atendimento_atual + 1; // verifica o contador atual
						$contador_total = $contador_filtro_suporte_tipo_atendimento - $contador_filtro_suporte_tipo_atendimento_atual; // calcula diferença de situações total - situação atual
						if($contador_total<>0){$or=" or ";}else{$or="";} // se não é a última, então insere OR
						$query_filtro_suporte_tipo_atendimento .= sprintf(" suporte.tipo_atendimento = '$value' $or");
		
				}
				$where .= sprintf($query_filtro_suporte_tipo_atendimento)." ) ";
				$where_campos[] = "filtro_suporte_tipo_atendimento";		
		}
		
	}
	// fim - se existe filtro de filtro_suporte_tipo_atendimento

	// se existe filtro de filtro_suporte_tipo_recomendacao
	if($row_relatorio['filtro_suporte_tipo_recomendacao'] == 1){
		
		$contador_filtro_suporte_tipo_recomendacao = 0;
		$contador_filtro_suporte_tipo_recomendacao_atual = 0;
		if( (isset($_GET["filtro_suporte_tipo_recomendacao"])) && ($_GET['filtro_suporte_tipo_recomendacao'] !="") ) {
		
				// contar quantidade de situacões atual
				foreach($_GET["filtro_suporte_tipo_recomendacao"] as $value){
						$contador_filtro_suporte_tipo_recomendacao = $contador_filtro_suporte_tipo_recomendacao + 1;
				}
				// fim - contar quantidade de situacões atual
		
				$query_filtro_suporte_tipo_recomendacao=" and ( ";
				foreach($_GET["filtro_suporte_tipo_recomendacao"] as $value){
						$contador_filtro_suporte_tipo_recomendacao_atual = $contador_filtro_suporte_tipo_recomendacao_atual + 1; // verifica o contador atual
						$contador_total = $contador_filtro_suporte_tipo_recomendacao - $contador_filtro_suporte_tipo_recomendacao_atual; // calcula diferença de situações total - situação atual
						if($contador_total<>0){$or=" or ";}else{$or="";} // se não é a última, então insere OR
						$query_filtro_suporte_tipo_recomendacao .= sprintf(" suporte.recomendacao = '$value' $or");
		
				}
				$where .= sprintf($query_filtro_suporte_tipo_recomendacao)." ) ";
				$where_campos[] = "filtro_suporte_tipo_recomendacao";		
		}
		
	}
	// fim - se existe filtro de filtro_suporte_tipo_recomendacao

	// se existe filtro de filtro_suporte_tipo_visita
	if($row_relatorio['filtro_suporte_tipo_visita'] == 1){
		
		$contador_filtro_suporte_tipo_visita = 0;
		$contador_filtro_suporte_tipo_visita_atual = 0;
		if( (isset($_GET["filtro_suporte_tipo_visita"])) && ($_GET['filtro_suporte_tipo_visita'] !="") ) {
		
				// contar quantidade de situacões atual
				foreach($_GET["filtro_suporte_tipo_visita"] as $value){
						$contador_filtro_suporte_tipo_visita = $contador_filtro_suporte_tipo_visita + 1;
				}
				// fim - contar quantidade de situacões atual
		
				$query_filtro_suporte_tipo_visita=" and ( ";
				foreach($_GET["filtro_suporte_tipo_visita"] as $value){
						$contador_filtro_suporte_tipo_visita_atual = $contador_filtro_suporte_tipo_visita_atual + 1; // verifica o contador atual
						$contador_total = $contador_filtro_suporte_tipo_visita - $contador_filtro_suporte_tipo_visita_atual; // calcula diferença de situações total - situação atual
						if($contador_total<>0){$or=" or ";}else{$or="";} // se não é a última, então insere OR
						$query_filtro_suporte_tipo_visita .= sprintf(" visita17 = '$value' $or");
		
				}
				$where .= sprintf($query_filtro_suporte_tipo_visita)." ) ";
				$where_campos[] = "filtro_suporte_tipo_visita";		
		}
		
	}
	// fim - se existe filtro de filtro_suporte_tipo_visita


	// filtro_suporte_anexo
	if($row_relatorio['filtro_suporte_anexo'] == 1){
		if( (isset($_GET["filtro_suporte_anexo"])) && ($_GET['filtro_suporte_anexo'] !="") ) {
		$colname_filtro_suporte_anexo = $_GET['filtro_suporte_anexo'];

		if($colname_filtro_suporte_anexo == "s"){
			$where .= " and (SELECT COUNT(suporte_arquivos.id_arquivo) FROM suporte_arquivos WHERE suporte_arquivos.id_suporte = $relatorio_tipo.id) > 0 ";
		} else if($colname_filtro_suporte_anexo == "n"){
			$where .= " and (SELECT COUNT(suporte_arquivos.id_arquivo) FROM suporte_arquivos WHERE suporte_arquivos.id_suporte = $relatorio_tipo.id) = 0 ";
		}

		$where_campos[] = "filtro_suporte_anexo";
		} 
	}
	// fim - filtro_suporte_anexo

	// filtro_solicitacao_solicitante
	if($row_relatorio['filtro_solicitacao_solicitante'] == 1){
		if( (isset($_GET["filtro_solicitacao_solicitante"])) && ($_GET['filtro_solicitacao_solicitante'] !="") ) {
		$colname_solicitacao_filtro_solicitacao_solicitante = $_GET['filtro_solicitacao_solicitante'];
		$where .= " and $relatorio_tipo.id_usuario_responsavel = '".$colname_solicitacao_filtro_solicitacao_solicitante."' ";
		$where_campos[] = "filtro_solicitacao_solicitante";
		} 
	}
    // fim - filtro_solicitacao_solicitante

	// filtro_solicitacao_executante
	if($row_relatorio['filtro_solicitacao_executante'] == 1){
		if( (isset($_GET["filtro_solicitacao_executante"])) && ($_GET['filtro_solicitacao_executante'] !="") ) {
		$colname_solicitacao_filtro_solicitacao_executante = $_GET['filtro_solicitacao_executante'];
		$where .= " and $relatorio_tipo.id_executante = '".$colname_solicitacao_filtro_solicitacao_executante."' ";
		$where_campos[] = "filtro_solicitacao_executante";
		} 
	}
    // fim - filtro_solicitacao_executante

	// filtro_solicitacao_operador
	if($row_relatorio['filtro_solicitacao_operador'] == 1){
		if( (isset($_GET["filtro_solicitacao_operador"])) && ($_GET['filtro_solicitacao_operador'] !="") ) {
		$colname_solicitacao_filtro_solicitacao_operador = $_GET['filtro_solicitacao_operador'];
		$where .= " and $relatorio_tipo.id_operador = '".$colname_solicitacao_filtro_solicitacao_operador."' ";
		$where_campos[] = "filtro_solicitacao_operador";
		} 
	}
    // fim - filtro_solicitacao_operador

	// filtro_solicitacao_testador
	if($row_relatorio['filtro_solicitacao_testador'] == 1){
		if( (isset($_GET["filtro_solicitacao_testador"])) && ($_GET['filtro_solicitacao_testador'] !="") ) {
		$colname_solicitacao_filtro_solicitacao_testador = $_GET['filtro_solicitacao_testador'];
		$where .= " and $relatorio_tipo.id_testador = '".$colname_solicitacao_filtro_solicitacao_testador."' ";
		$where_campos[] = "filtro_solicitacao_testador";
		} 
	}
    // fim - filtro_solicitacao_testador

	// se existe filtro de filtro_solicitacao_tipo
	if($row_relatorio['filtro_solicitacao_tipo'] == 1){
		
		$contador_filtro_solicitacao_tipo = 0;
		$contador_filtro_solicitacao_tipo_atual = 0;
		if( (isset($_GET["filtro_solicitacao_tipo"])) && ($_GET['filtro_solicitacao_tipo'] !="") ) {
		
				// contar quantidade de situacões atual
				foreach($_GET["filtro_solicitacao_tipo"] as $value){
						$contador_filtro_solicitacao_tipo = $contador_filtro_solicitacao_tipo + 1;
				}
				// fim - contar quantidade de situacões atual
		
				$query_filtro_solicitacao_tipo=" and ( ";
				foreach($_GET["filtro_solicitacao_tipo"] as $value){
						$contador_filtro_solicitacao_tipo_atual = $contador_filtro_solicitacao_tipo_atual + 1; // verifica o contador atual
						$contador_total = $contador_filtro_solicitacao_tipo - $contador_filtro_solicitacao_tipo_atual; // calcula diferença de situações total - situação atual
						if($contador_total<>0){$or=" or ";}else{$or="";} // se não é a última, então insere OR
						$query_filtro_solicitacao_tipo .= sprintf(" solicitacao.tipo = '$value' $or");
		
				}
				$where .= sprintf($query_filtro_solicitacao_tipo)." ) ";
				$where_campos[] = "filtro_solicitacao_tipo";		
		}
		
	}
	// fim - se existe filtro de filtro_solicitacao_tipo

    // filtro_solicitacao_desmembrada
	if($row_relatorio['filtro_solicitacao_desmembrada'] == 1){
		if( (isset($_GET["filtro_solicitacao_desmembrada"])) && ($_GET['filtro_solicitacao_desmembrada'] !="") ) {
		$colname_filtro_solicitacao_desmembrada = $_GET['filtro_solicitacao_desmembrada'];

		if($colname_filtro_solicitacao_desmembrada == "s"){
			$where .= " and $relatorio_tipo.solicitacao_desmembrada > 0 ";
		} else if($colname_filtro_solicitacao_desmembrada == "n"){
			$where .= " and $relatorio_tipo.solicitacao_desmembrada IS NULL ";
		}

		$where_campos[] = "filtro_solicitacao_desmembrada";
		} 
	}
    // fim - filtro_solicitacao_desmembrada

	// se existe filtro de filtro_solicitacao_status
	if($row_relatorio['filtro_solicitacao_status'] == 1){
		
		$contador_filtro_solicitacao_status = 0;
		$contador_filtro_solicitacao_status_atual = 0;
		if( (isset($_GET["filtro_solicitacao_status"])) && ($_GET['filtro_solicitacao_status'] !="") ) {
		
				// contar quantidade de situacões atual
				foreach($_GET["filtro_solicitacao_status"] as $filtro_solicitacao_status){
						$contador_filtro_solicitacao_status = $contador_filtro_solicitacao_status + 1;
				}
				// fim - contar quantidade de situacões atual
		
				$query_filtro_solicitacao_status=" and ( ";
				foreach($_GET["filtro_solicitacao_status"] as $filtro_solicitacao_status){
						$contador_filtro_solicitacao_status_atual = $contador_filtro_solicitacao_status_atual + 1; // verifica o contador atual
						$contador_total = $contador_filtro_solicitacao_status - $contador_filtro_solicitacao_status_atual; // calcula diferença de situações total - situação atual
						if($contador_total<>0){$or=" or ";}else{$or="";} // se não é a última, então insere OR
						$query_filtro_solicitacao_status .= sprintf(" solicitacao.status = '$filtro_solicitacao_status' $or");
		
				}
				$where .= sprintf($query_filtro_solicitacao_status)." ) ";
				$where_campos[] = "filtro_solicitacao_status";		
		}
		
	}
	// fim - se existe filtro de filtro_solicitacao_status
	




	// se existe filtro de filtro_prospeccao_situacao
	if($row_relatorio['filtro_prospeccao_situacao'] == 1){
		
		$contador_filtro_prospeccao_situacao = 0;
		$contador_filtro_prospeccao_situacao_atual = 0;
		if( (isset($_GET["filtro_prospeccao_situacao"])) && ($_GET['filtro_prospeccao_situacao'] !="") ) {
		
				// contar quantidade de situacões atual
				foreach($_GET["filtro_prospeccao_situacao"] as $filtro_prospeccao_situacao){
						$contador_filtro_prospeccao_situacao = $contador_filtro_prospeccao_situacao + 1;
				}
				// fim - contar quantidade de situacões atual
		
				$query_filtro_prospeccao_situacao=" and ( ";
				foreach($_GET["filtro_prospeccao_situacao"] as $filtro_prospeccao_situacao){
						$contador_filtro_prospeccao_situacao_atual = $contador_filtro_prospeccao_situacao_atual + 1; // verifica o contador atual
						$contador_total = $contador_filtro_prospeccao_situacao - $contador_filtro_prospeccao_situacao_atual; // calcula diferença de situações total - situação atual
						if($contador_total<>0){$or=" or ";}else{$or="";} // se não é a última, então insere OR
						$query_filtro_prospeccao_situacao .= sprintf(" prospeccao.situacao = '$filtro_prospeccao_situacao' $or");
		
				}
				$where .= sprintf($query_filtro_prospeccao_situacao)." ) ";
				$where_campos[] = "filtro_prospeccao_situacao";		
		}
		
	}
	// fim - se existe filtro de filtro_prospeccao_situacao
	
	// se existe filtro de filtro_prospeccao_status
	if($row_relatorio['filtro_prospeccao_status'] == 1){
		
		$contador_filtro_prospeccao_status = 0;
		$contador_filtro_prospeccao_status_atual = 0;
		if( (isset($_GET["filtro_prospeccao_status"])) && ($_GET['filtro_prospeccao_status'] !="") ) {
		
				// contar quantidade de situacões atual
				foreach($_GET["filtro_prospeccao_status"] as $filtro_prospeccao_status){
						$contador_filtro_prospeccao_status = $contador_filtro_prospeccao_status + 1;
				}
				// fim - contar quantidade de situacões atual
		
				$query_filtro_prospeccao_status=" and ( ";
				foreach($_GET["filtro_prospeccao_status"] as $filtro_prospeccao_status){
						$contador_filtro_prospeccao_status_atual = $contador_filtro_prospeccao_status_atual + 1; // verifica o contador atual
						$contador_total = $contador_filtro_prospeccao_status - $contador_filtro_prospeccao_status_atual; // calcula diferença de situações total - situação atual
						if($contador_total<>0){$or=" or ";}else{$or="";} // se não é a última, então insere OR
						$query_filtro_prospeccao_status .= sprintf(" prospeccao.status = '$filtro_prospeccao_status' $or");
		
				}
				$where .= sprintf($query_filtro_prospeccao_status)." ) ";
				$where_campos[] = "filtro_prospeccao_status";		
		}
		
	}
	// fim - se existe filtro de filtro_prospeccao_status
		
    // filtro_prospeccao_usuario_responsavel
	if($row_relatorio['filtro_prospeccao_usuario_responsavel'] == 1){
		if( (isset($_GET["filtro_prospeccao_usuario_responsavel"])) && ($_GET['filtro_prospeccao_usuario_responsavel'] !="") ) {
		$colname_prospeccao_filtro_prospeccao_usuario_responsavel = $_GET['filtro_prospeccao_usuario_responsavel'];
		$where .= " and $relatorio_tipo.id_usuario_responsavel = '".$colname_prospeccao_filtro_prospeccao_usuario_responsavel."' ";
		$where_campos[] = "filtro_prospeccao_usuario_responsavel";
		} 
	}
    // fim - filtro_prospeccao_usuario_responsavel

    // filtro_prospeccao_tipo_cliente
	if($row_relatorio['filtro_prospeccao_tipo_cliente'] == 1){
		if( (isset($_GET["filtro_prospeccao_tipo_cliente"])) && ($_GET['filtro_prospeccao_tipo_cliente'] !="") ) {
		$colname_prospeccao_filtro_prospeccao_tipo_cliente = $_GET['filtro_prospeccao_tipo_cliente'];
		$where .= " and $relatorio_tipo.tipo_cliente = '".$colname_prospeccao_filtro_prospeccao_tipo_cliente."' ";
		$where_campos[] = "filtro_prospeccao_tipo_cliente";
		} 
	}
    // fim - filtro_prospeccao_tipo_cliente
	
    // filtro_prospeccao_ativo_passivo
	if($row_relatorio['filtro_prospeccao_ativo_passivo'] == 1){
		if( (isset($_GET["filtro_prospeccao_ativo_passivo"])) && ($_GET['filtro_prospeccao_ativo_passivo'] !="") ) {
		$colname_prospeccao_filtro_prospeccao_ativo_passivo = $_GET['filtro_prospeccao_ativo_passivo'];
		$where .= " and $relatorio_tipo.ativo_passivo = '".$colname_prospeccao_filtro_prospeccao_ativo_passivo."' ";
		$where_campos[] = "filtro_prospeccao_ativo_passivo";
		} 
	}
    // fim - filtro_prospeccao_ativo_passivo

    // filtro_prospeccao_baixa_perda_motivo
	if($row_relatorio['filtro_prospeccao_baixa_perda_motivo'] == 1){
		if( (isset($_GET["filtro_prospeccao_baixa_perda_motivo"])) && ($_GET['filtro_prospeccao_baixa_perda_motivo'] !="") ) {
		$colname_prospeccao_filtro_prospeccao_baixa_perda_motivo = $_GET['filtro_prospeccao_baixa_perda_motivo'];
		$where .= " and $relatorio_tipo.baixa_perda_motivo = '".$colname_prospeccao_filtro_prospeccao_baixa_perda_motivo."' ";
		$where_campos[] = "filtro_prospeccao_baixa_perda_motivo";
		} 
	}
    // fim - filtro_prospeccao_baixa_perda_motivo
		
	



	// se existe filtro de filtro_venda_situacao
	if($row_relatorio['filtro_venda_situacao'] == 1){
		
		$contador_filtro_venda_situacao = 0;
		$contador_filtro_venda_situacao_atual = 0;
		if( (isset($_GET["filtro_venda_situacao"])) && ($_GET['filtro_venda_situacao'] !="") ) {
		
				// contar quantidade de situacões atual
				foreach($_GET["filtro_venda_situacao"] as $filtro_venda_situacao){
						$contador_filtro_venda_situacao = $contador_filtro_venda_situacao + 1;
				}
				// fim - contar quantidade de situacões atual
		
				$query_filtro_venda_situacao=" and ( ";
				foreach($_GET["filtro_venda_situacao"] as $filtro_venda_situacao){
						$contador_filtro_venda_situacao_atual = $contador_filtro_venda_situacao_atual + 1; // verifica o contador atual
						$contador_total = $contador_filtro_venda_situacao - $contador_filtro_venda_situacao_atual; // calcula diferença de situações total - situação atual
						if($contador_total<>0){$or=" or ";}else{$or="";} // se não é a última, então insere OR
						$query_filtro_venda_situacao .= sprintf(" venda.situacao = '$filtro_venda_situacao' $or");
		
				}
				$where .= sprintf($query_filtro_venda_situacao)." ) ";
				$where_campos[] = "filtro_venda_situacao";		
		}
		
	}
	// fim - se existe filtro de filtro_venda_situacao

    // filtro_venda_usuario_responsavel
	if($row_relatorio['filtro_venda_usuario_responsavel'] == 1){
		if( (isset($_GET["filtro_venda_usuario_responsavel"])) && ($_GET['filtro_venda_usuario_responsavel'] !="") ) {
		$colname_venda_filtro_venda_usuario_responsavel = $_GET['filtro_venda_usuario_responsavel'];
		$where .= " and $relatorio_tipo.id_usuario_responsavel = '".$colname_venda_filtro_venda_usuario_responsavel."' ";
		$where_campos[] = "filtro_venda_usuario_responsavel";
		} 
	}
    // fim - filtro_venda_usuario_responsavel
	
    // filtro_venda_tipo_cliente
	if($row_relatorio['filtro_venda_tipo_cliente'] == 1){
		if( (isset($_GET["filtro_venda_tipo_cliente"])) && ($_GET['filtro_venda_tipo_cliente'] !="") ) {
		$colname_venda_filtro_venda_tipo_cliente = $_GET['filtro_venda_tipo_cliente'];
		$where .= " and prospeccao.tipo_cliente = '".$colname_venda_filtro_venda_tipo_cliente."' ";
		$where_campos[] = "filtro_venda_tipo_cliente";
		} 
	}
    // fim - filtro_venda_tipo_cliente
	
	// se existe filtro de filtro_venda_modulos
	if($row_relatorio['filtro_venda_modulos'] == 1){
		
		$contador_filtro_venda_modulos = 0;
		$contador_filtro_venda_modulos_atual = 0;
		if( (isset($_GET["filtro_venda_modulos"])) && ($_GET['filtro_venda_modulos'] !="") ) {
		
				// contar quantidade de situacões atual
				foreach($_GET["filtro_venda_modulos"] as $value){
						$contador_filtro_venda_modulos = $contador_filtro_venda_modulos + 1;
				}
				// fim - contar quantidade de situacões atual
		
				$query_filtro_venda_modulos=" and ( ";
				foreach($_GET["filtro_venda_modulos"] as $value){
						$contador_filtro_venda_modulos_atual = $contador_filtro_venda_modulos_atual + 1; // verifica o contador atual
						$contador_total = $contador_filtro_venda_modulos - $contador_filtro_venda_modulos_atual; // calcula diferença de situações total - situação atual
						if($contador_total<>0){$or=" or ";}else{$or="";} // se não é a última, então insere OR
						$query_filtro_venda_modulos .= sprintf(" venda_modulos.modulo = '$value' $or");
		
				}
				$where .= sprintf($query_filtro_venda_modulos)." ) ";
				$where_campos[] = "filtro_venda_modulos";		
		}
		
	}
	// fim - se existe filtro de filtro_venda_modulos
	
    // filtro_administrativo_atraso
	if($row_relatorio['filtro_administrativo_atraso'] == 1){
		if( (isset($_GET["filtro_administrativo_atraso"])) && ($_GET['filtro_administrativo_atraso'] !="") ) {
		$colname_filtro_administrativo_atraso = $_GET['filtro_administrativo_atraso'];
		$where .= " and da01.atraso1 >= ".$colname_filtro_administrativo_atraso." ";
		$where_campos[] = "filtro_administrativo_atraso";
		} 
	}
    // fim - filtro_administrativo_atraso
		
	// fim - filtros -----------------------------------------------------------------------------------------------------------------------------------------------------

	// filtro_suporte_situacao_padrao
	if($row_relatorio['filtro_suporte_situacao_padrao'] <> NULL and ($relatorio_modo == 'geral_modulo' or $relatorio_modo == 'geral')){
		
		$row_relatorio['filtro_suporte_situacao_padrao'];
		$filtro_suporte_situacao_padrao_array = explode(',', $row_relatorio['filtro_suporte_situacao_padrao']);
		
		$contador_filtro_suporte_situacao = 0;
		$contador_filtro_suporte_situacao_atual = 0;
		
		if( (isset($filtro_suporte_situacao_padrao_array)) && ($filtro_suporte_situacao_padrao_array !="") ) {
		
				// contar quantidade de situacões atual
				foreach($filtro_suporte_situacao_padrao_array as $filtro_suporte_situacao){
						$contador_filtro_suporte_situacao = $contador_filtro_suporte_situacao + 1;
				}
				// fim - contar quantidade de situacões atual
		
				$query_filtro_suporte_situacao=" and ( ";
				foreach($filtro_suporte_situacao_padrao_array as $filtro_suporte_situacao){
						$contador_filtro_suporte_situacao_atual = $contador_filtro_suporte_situacao_atual + 1; // verifica o contador atual
						$contador_total = $contador_filtro_suporte_situacao - $contador_filtro_suporte_situacao_atual; // calcula diferença de situações total - situação atual
						if($contador_total<>0){$or=" or ";}else{$or="";} // se não é a última, então insere OR
						$query_filtro_suporte_situacao .= sprintf(" suporte.situacao = '$filtro_suporte_situacao' $or");
		
				}
				$where .= sprintf($query_filtro_suporte_situacao)." ) ";
				$where_campos[] = "filtro_suporte_situacao";

		}
		
	}
	// fim - filtro_suporte_situacao_padrao
	
	// filtro_administrativo_atraso_padrao
	if($row_relatorio['filtro_administrativo_atraso_padrao'] <> NULL and ($relatorio_modo == 'geral_modulo' or $relatorio_modo == 'geral')){
		
		$filtro_administrativo_atraso_padrao_array = explode(',', $row_relatorio['filtro_administrativo_atraso_padrao']);

		$contador_filtro_administrativo_atraso = 0;
		$contador_filtro_administrativo_atraso_atual = 0;
		if( (isset($filtro_administrativo_atraso_padrao_array)) && ($filtro_administrativo_atraso_padrao_array !="") ) {
		
				// contar quantidade de situacões atual
				foreach($filtro_administrativo_atraso_padrao_array as $filtro_administrativo_atraso){
						$contador_filtro_administrativo_atraso = $contador_filtro_administrativo_atraso + 1;
				}
				// fim - contar quantidade de situacões atual
		
				$query_filtro_administrativo_atraso=" and ( ";
				foreach($filtro_administrativo_atraso_padrao_array as $filtro_administrativo_atraso){
						$contador_filtro_administrativo_atraso_atual = $contador_filtro_administrativo_atraso_atual + 1; // verifica o contador atual
						$contador_total = $contador_filtro_administrativo_atraso - $contador_filtro_administrativo_atraso_atual; // calcula diferença de situações total - situação atual
						if($contador_total<>0){$or=" or ";}else{$or="";} // se não é a última, então insere OR
						$query_filtro_administrativo_atraso .= sprintf(" da01.atraso1 >= $filtro_administrativo_atraso $or");
		
				}
				$where .= sprintf($query_filtro_administrativo_atraso)." ) ";
				$where_campos[] = "filtro_administrativo_atraso";
		}
		
	}
	// fim - filtro_administrativo_atraso_padrao

    // registro ---------------------------------------------------------------------------------------------------------------------------------------------------------------------
	
    // registro
    mysql_select_db($database_conexao, $conexao);

	// parametros
	if($row_relatorio['relatorio_inadimplencia_limite_atraso'] == 1){
		$where .= " and da01.atraso1 <= ".$row_parametros['relatorio_inadimplencia_limite_atraso']." ";
	}
	// fim parametros

	// ORDER BY
	$order_by = $row_relatorio['query_order'];
	if(isset($_GET['filtro_geral_praca']) and $_GET['filtro_geral_praca'] == NULL and $acesso == 1 and $where_praca <> NULL){
		$order_by = $where_praca." ASC, ".$order_by;
	}
	// fim - ORDER BY
	
	// GROUP BY
	$group = " ";
	if($row_relatorio['query_group'] != ""){
		$group = " GROUP BY ".$row_relatorio['query_group'];
	}
    // fim - GROUP BY

	if($row_relatorio['especial'] == 0){
		
		$query_registro = "
		SELECT ".$row_relatorio['query_campos']."
		FROM 
		".$row_relatorio['query_from']." 
		".$row_relatorio['query_join']."
		WHERE ".$where.$row_relatorio['query_where'].$group.
		"ORDER BY ".$order_by." 
		";
		$registro = mysql_query($query_registro, $conexao) or die(mysql_error());
		$row_registro = mysql_fetch_assoc($registro);
		$totalRows_registro = mysql_num_rows($registro);
	
	} else if(
		($row_relatorio['especial'] == 1 and $row_relatorio['relatorio'] == 100)
	){
	
		//region - Relatório de Indicadores de Efetividade (Impacto) ------------------------------------
		if (isset($relatorio_evento_array)) {
			
			// relatorio_evento_valor
			mysql_select_db($database_conexao, $conexao);
			$query_relatorio_evento_valor = "SELECT * FROM relatorio_evento ORDER BY id ASC";
			$relatorio_evento_valor = mysql_query($query_relatorio_evento_valor, $conexao) or die(mysql_error());
			$row_relatorio_evento_valor = mysql_fetch_assoc($relatorio_evento_valor);
			$totalRows_relatorio_evento_valor = mysql_num_rows($relatorio_evento_valor);
			do { 
				$relatorio_evento_valor_array[$row_relatorio_evento_valor['id']] = array('positivo_negativo' => $row_relatorio_evento_valor['positivo_negativo'], 'valor' => $row_relatorio_evento_valor['valor']);
			} while ($row_relatorio_evento_valor = mysql_fetch_assoc($relatorio_evento_valor));
			mysql_free_result($relatorio_evento_valor);
			// fim - relatorio_evento_valor
			
			// relatorio_classificacao_nivel
			mysql_select_db($database_conexao, $conexao);
			$query_relatorio_classificacao_nivel = "SELECT * FROM relatorio_classificacao_nivel ORDER BY id ASC";
			$relatorio_classificacao_nivel = mysql_query($query_relatorio_classificacao_nivel, $conexao) or die(mysql_error());
			$row_relatorio_classificacao_nivel = mysql_fetch_assoc($relatorio_classificacao_nivel);
			$totalRows_relatorio_classificacao_nivel = mysql_num_rows($relatorio_classificacao_nivel);
			do { 
				$relatorio_classificacao_nivel_array[$row_relatorio_classificacao_nivel['id']] = $row_relatorio_classificacao_nivel['nivel'];
			} while ($row_relatorio_classificacao_nivel = mysql_fetch_assoc($relatorio_classificacao_nivel));
			mysql_free_result($relatorio_classificacao_nivel);
			// fim - relatorio_classificacao_nivel
			
			// multiplicacao
			foreach($relatorio_evento_array as $key => $value){
				ksort($value); // ordena a array pela chave
				$total_praca = 0;
				$total_positivo = 0;
				$total_negativo = 0;
				
				$multiplicacao = NULL;
				foreach($value as $key2 => $value2){
					if($key2 > 0){
						$multiplicacao = ($relatorio_evento_valor_array[$key2]['valor']*$value2)/($value[0]/$totalizar_contrato_ativo_retorno*100);
						if($relatorio_evento_valor_array[$key2]['positivo_negativo'] == 'p'){
							$total_positivo = $total_positivo + $multiplicacao;
						} else if($relatorio_evento_valor_array[$key2]['positivo_negativo'] == 'n'){
							$total_negativo = $total_negativo + $multiplicacao;
						}
					}
				}
				$total_praca = ($total_positivo - $total_negativo);
				$relatorio_efetividade_array[] = array('praca' => $key, 'total' => $total_praca, 'ordem' => NULL, 'classificacao' => NULL);
			}
			// fim - multiplicacao
						
			// ordernar pelo 'total' do maior para o menor
			foreach ($relatorio_efetividade_array as $key => $row) {
				$praca[$key] = $row['praca'];
				$total[$key]  = $row['total'];
			}
			array_multisort($total, SORT_DESC, $praca, SORT_ASC, $relatorio_efetividade_array);
			// fim - ordernar pelo 'total' do maior para o menor
			
			// ordem e classificação
			$relatorio_efetividade_array_count = 1;
			
			foreach ($relatorio_efetividade_array as $key => $value){
				
				// classificacao_atual
				$total_atual = $relatorio_efetividade_array[$key]['total'];
				if($total_atual >= $relatorio_classificacao_nivel_array[1]){
					$classificacao_atual = "Ouro";
				} else if($total_atual >= $relatorio_classificacao_nivel_array[2]){
					$classificacao_atual = "Prata";
				} else if($total_atual >= $relatorio_classificacao_nivel_array[3]){
					$classificacao_atual = "Bronze";
				} else {	
					$classificacao_atual = "Ferro";
				}
				// fim - classificacao_atual
				
				$relatorio_efetividade_array[$key]['classificacao'] = $classificacao_atual;
				$relatorio_efetividade_array[$key]['ordem'] = $relatorio_efetividade_array_count ++;
				
			}
			// fim - ordem e classificação
		
		} else {
			$relatorio_efetividade_array = array();
		}
		
		// drop
		mysql_select_db($database_conexao, $conexao);
		$query_teste_drop = "DROP TEMPORARY TABLE IF EXISTS relatorio_temporario";
		$teste_drop = mysql_query($query_teste_drop, $conexao) or die(mysql_error());
		// fim - drop
		
		// create		
		mysql_select_db($database_conexao, $conexao);
		$query_teste_create = "
		CREATE TEMPORARY TABLE relatorio_temporario (
		id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
		praca varchar(50) DEFAULT NULL,
		total double(10,2) DEFAULT NULL,
		ordem int(11) DEFAULT NULL,
		classificacao varchar(50) DEFAULT NULL
		)
		";
		$teste_create = mysql_query($query_teste_create, $conexao) or die(mysql_error());
		// fim - create
		
		// insert
		mysql_select_db($database_conexao, $conexao);
		foreach ($relatorio_efetividade_array as $key => $value){
			$query_teste_insert = "INSERT INTO relatorio_temporario (praca, total, ordem, classificacao) values ('".$value['praca']."', ".$value['total'].", ".$value['ordem'].", '".$value['classificacao']."')";
			$teste_insert = mysql_query($query_teste_insert, $conexao) or die(mysql_error());
		}
		// fim - insert
		
		// registro
		mysql_select_db($database_conexao, $conexao);
		$query_registro = "SELECT * FROM relatorio_temporario ORDER BY ordem ASC";
		$registro = mysql_query($query_registro, $conexao) or die(mysql_error());
		$row_registro = mysql_fetch_assoc($registro);
		$totalRows_registro = mysql_num_rows($registro);
		// fim - registro
		//endregion - fim - Relatório de Indicadores de Efetividade (Impacto) ------------------------------
				
	}	
    // fim - registro
	
	// array_praca (para o contador por praça)
	if(isset($_GET['filtro_geral_praca']) and $_GET['filtro_geral_praca'] == NULL and $acesso == 1 and $where_praca <> NULL){
		mysql_select_db($database_conexao, $conexao);
		$query_array_praca = "SELECT * FROM geral_tipo_praca ORDER BY IdPraca ASC";
		$array_praca = mysql_query($query_array_praca, $conexao) or die(mysql_error());
		$row_array_praca = mysql_fetch_assoc($array_praca);
		$totalRows_array_praca = mysql_num_rows($array_praca);
		$array_praca_geral = NULL;
		do {
			$array_praca_geral[] = array('praca' => $row_array_praca['praca'], 'contador' => 0);	
		} while ($row_array_praca = mysql_fetch_assoc($array_praca));
		mysql_free_result($array_praca);
	}
	// fim - array_praca (para o contador por praça)	

	
	// registro_praca
	if(isset($_GET['filtro_geral_praca']) and $_GET['filtro_geral_praca'] == NULL and $acesso == 1 and $where_praca <> NULL){
		
		$registro_praca = mysql_query($query_registro, $conexao) or die(mysql_error());
		$row_registro_praca = mysql_fetch_assoc($registro_praca);
		$totalRows_registro_praca = mysql_num_rows($registro_praca);
		
		if($totalRows_registro_praca > 0){
			do {
				
				$contador_array = 0;
				foreach($array_praca_geral as $item){
		
					if($array_praca_geral[$contador_array]['praca'] == @$row_registro_praca['praca']){
						
						$array_praca_geral[$contador_array]['contador'] = $array_praca_geral[$contador_array]['contador'] + 1;
						
					}
					$contador_array ++;
					
				}
				
			} while ($row_registro_praca = mysql_fetch_assoc($registro_praca));
		}
		
		mysql_free_result($registro_praca);
	}
	// fim - registro_praca
	
	$titulo = NULL;
	$header = NULL;
	$footer = NULL;
	$contador_filtro = 0;
	
	// titulo_grupo_geral
	$contador_array = 0;
	foreach($relatorio_grupo_geral_array as $item){
		if($row_relatorio['id_grupo_geral'] == $item['id'] and $item['titulo_exibir'] == 0){
			$titulo_grupo_geral = "
			<table class='titulo' width='100%' border='0' cellpadding='0' cellspacing='0' style='border-spacing: 0;'>
			<tr>
				<td colspan='3' style='font-size: 12px;'> $relatorio_grupo_geral_titulo </td>
			</tr>
			</table>
				
			<div style='height: 1px; background-color: #000; margin-top: 5px; margin-bottom: 5px;'></div>
			";
			$relatorio_grupo_geral_array[$contador_array]['titulo_exibir'] = 1;
		}
		$contador_array ++;
	}
	// fim - titulo_grupo_geral

	// titulo
	$titulo = 
	"
	<table class='titulo' width='100%' border='0' cellpadding='0' cellspacing='0' style='border-spacing: 0;'>
	<tr>
		<td colspan='3'> Relatório de $relatorio_titulo </td>
	</tr>
	<tr>
		<td width='33%' align='left' style='font-size: 10px; text-align: left;'>Relatório ".$contador_relatorio." de ".$totalRows_relatorio."</td>
		<td width='34%' style='font-size: 10px; font-weight: normal;'>
			$relatorio_grupo_titulo - $relatorio_grupo_subgrupo_titulo 
		</td>
		<td width='33%' align='right' style='font-size: 10px;'>
			$numero_pagina
		</td>
	</tr>
	</table>
	<div style='height: 1px; background-color: #000; margin-top: 5px; margin-bottom: 0px;'></div>
	";
	// fim - titulo
	
	// header
	if($totalRows_relatorio > 0 and $relatorio_id_grupo > 0){

		$header_filtro_geral_usuario_area_atual = NULL;
		if($row_relatorio['filtro_geral_usuario_area'] == 1 and $filtro_geral_usuario_area_atual <> NULL){
			$header_filtro_geral_usuario_area_atual = " | <strong>Área:</strong> $filtro_geral_usuario_area_atual";
		}
				
		$header .= 
		"
		<table class='header' width='100%' border='0' cellspacing='0' cellpadding='0' style='border-spacing: 0; margin-top: 5px;'>
		<tr>
			<td width='50%' align='left'>
				<strong>Período:</strong> $filtro_geral_data_criacao_atual à $filtro_geral_data_criacao_fim_atual 
			</td>
			
			<td width='50%'>
				<table width='100%' border='0' cellspacing='0' cellpadding='0' style='border-spacing: 0;'>
					<tr>
						<td width='60%' align='left'><strong>Praça:</strong> $filtro_geral_praca_atual </td>
						<td width='40%' align='right'><strong>Gerado em:</strong> ".date('d-m-Y H:i')."</td>
					</tr>
				</table>
			</td>
		</tr>
		
		<tr>
			<td width='50%' align='left'>
				<strong>Cliente:</strong> $filtro_geral_cliente_atual 
			</td>
			
			<td width='50%'>
				<strong>Usuário:</strong> $filtro_geral_usuario_atual $header_filtro_geral_usuario_area_atual 
			</td>
		</tr>
		";
		
		// filtro_solicitacao_solicitante
		if(($row_relatorio['filtro_solicitacao_solicitante'] == 1 or $row_relatorio['filtro_solicitacao_executante'] == 1) and $relatorio_id_grupo > 0){
		$header .= 
		"
		<tr>
			<td align='left' valign='top'>
				<strong>Solicitante:</strong> $filtro_solicitacao_solicitante_atual
			</td>
			<td align='left' valign='top'>
				<strong>Executante:</strong> $filtro_solicitacao_executante_atual
			</td>
		</tr>
		";
		$contador_filtro ++;
		}
		// fim - filtro_solicitacao_solicitante

		// filtro_solicitacao_operador
		if(($row_relatorio['filtro_solicitacao_operador'] == 1 or $row_relatorio['filtro_solicitacao_testador'] == 1) and $relatorio_id_grupo > 0){
		$header .= 
		"
		<tr>
			<td align='left' valign='top'>
				<strong>Operador:</strong> $filtro_solicitacao_operador_atual
			</td>
			<td align='left' valign='top'>
				<strong>Testador:</strong> $filtro_solicitacao_testador_atual
			</td>
		</tr>
		";
		$contador_filtro ++;
		}
		// fim - filtro_solicitacao_operador
		
		// filtro_solicitacao_tipo
		if($row_relatorio['filtro_solicitacao_tipo'] == 1 and $relatorio_id_grupo > 0){
		$header .= 
		"
		<tr>
			<td colspan='2' align='left' valign='top'>
				<strong>Tipo de solicitação:</strong> $filtro_solicitacao_tipo_atual
			</td>
		</tr>
		";
		$contador_filtro ++;
		}
		// fim - filtro_solicitacao_tipo


		// filtro_solicitacao_desmembrada
		if($row_relatorio['filtro_solicitacao_desmembrada'] == 1 and $relatorio_id_grupo > 0){
			$header .= 
			"
			<tr>
				<td colspan='2' align='left' valign='top'>
					<strong>Desmembrada:</strong> $filtro_solicitacao_desmembrada_atual
				</td>
			</tr>
			";
			$contador_filtro ++;
			}
			// fim - filtro_solicitacao_desmembrada
		

		// filtro_solicitacao_status
		if($row_relatorio['filtro_solicitacao_status'] == 1 and $relatorio_id_grupo > 0){ 
		$header .= 
		"
		<tr>
			<td colspan='2' align='left' valign='top'>
				<strong>Status:</strong> $filtro_solicitacao_status_atual 
			</td>
		</tr>
		";
		$contador_filtro ++;
		}
		// fim - filtro_solicitacao_status
				
		
		
		
		
		// filtro_suporte_situacao
		if($row_relatorio['filtro_suporte_situacao'] == 1 and $relatorio_id_grupo > 0){ 
		$header .= 
		"
		<tr>
			<td colspan='2' align='left' valign='top'>
				<strong>Situação:</strong> $filtro_suporte_situacao_atual 
			</td>
		</tr>
		";
		$contador_filtro ++;
		}
		// fim - filtro_suporte_situacao

		// filtro_suporte_solicitante
		if(($row_relatorio['filtro_suporte_solicitante'] == 1 or $row_relatorio['filtro_suporte_atendente'] == 1) and $relatorio_id_grupo > 0){ 
		$header .= 
		"
		<tr>
			<td width='50%' align='left' valign='top'>
				<strong>Solicitante:</strong> $filtro_suporte_solicitante_atual
			</td>
			<td width='50%' align='left' valign='top'>
				<strong>Atendente:</strong> $filtro_suporte_atendente_atual
			</td>
		</tr>
		";
		$contador_filtro ++;
		}
		// fim - filtro_suporte_solicitante
		
		// filtro_suporte_tipo_atendimento
		if($row_relatorio['filtro_suporte_tipo_atendimento'] == 1 and $relatorio_id_grupo > 0){
		$header .= 
		"
		<tr>
			<td colspan='2' align='left' valign='top'>
				<strong>Tipo de atendimento:</strong> $filtro_suporte_tipo_atendimento_atual
			</td>
		</tr>
		";
		$contador_filtro ++;
		}
		// fim - filtro_suporte_tipo_atendimento

		// filtro_suporte_tipo_recomendacao
		if($row_relatorio['filtro_suporte_tipo_recomendacao'] == 1 and $relatorio_id_grupo > 0){
		$header .= 
		"
		<tr>
			<td colspan='2' align='left' valign='top'>
				<strong>Recomendação:</strong> $filtro_suporte_tipo_recomendacao_atual
			</td>
		</tr>
		";
		$contador_filtro ++;
		}
		// fim - filtro_suporte_tipo_recomendacao

		// filtro_suporte_tipo_visita
		if($row_relatorio['filtro_suporte_tipo_visita'] == 1 and $relatorio_id_grupo > 0){
		$header .= 
		"
		<tr>
			<td colspan='2' align='left' valign='top'>
				<strong>Tipo de visita:</strong> $filtro_suporte_tipo_visita_atual
			</td>
		</tr>
		";
		$contador_filtro ++;
		}
		// fim - filtro_suporte_tipo_visita
		

		// filtro_suporte_anexo
		if($row_relatorio['filtro_suporte_anexo'] == 1 and $relatorio_id_grupo > 0){
			$header .= 
			"
			<tr>
				<td colspan='2' align='left' valign='top'>
					<strong>Anexo:</strong> $filtro_suporte_anexo_atual
				</td>
			</tr>
			";
			$contador_filtro ++;
			}
			// fim - filtro_suporte_anexo	
		
		
		
		// filtro_prospeccao_tipo_cliente
		if(($row_relatorio['filtro_prospeccao_tipo_cliente'] == 1 or $row_relatorio['filtro_prospeccao_ativo_passivo'] == 1) and $relatorio_id_grupo > 0){ 
		$header .= 
		"
		<tr>
			<td width='50%' align='left' valign='top'>
				<strong>Tipo de cliente:</strong> $filtro_prospeccao_tipo_cliente_atual 
			</td>
			<td width='50%' align='right' valign='top'>
				<strong>Prospect:</strong> $filtro_prospeccao_ativo_passivo_atual 
			</td>
		</tr>
		";
		$contador_filtro ++;
		}
		// fim - filtro_prospeccao_tipo_cliente		

		// filtro_prospeccao_situacao
		if($row_relatorio['filtro_prospeccao_situacao'] == 1 and $relatorio_id_grupo > 0){ 
		$header .= 
		"
		<tr>
			<td colspan='2' align='left' valign='top'>
				<strong>Situação:</strong> $filtro_prospeccao_situacao_atual 
			</td>
		</tr>
		";
		$contador_filtro ++;
		}
		// fim - filtro_prospeccao_situacao

		// filtro_prospeccao_status
		if($row_relatorio['filtro_prospeccao_status'] == 1 and $relatorio_id_grupo > 0){ 
		$header .= 
		"
		<tr>
			<td colspan='2' align='left' valign='top'>
				<strong>Status:</strong> $filtro_prospeccao_status_atual 
			</td>
		</tr>
		";
		$contador_filtro ++;
		}
		// fim - filtro_prospeccao_status
		
		// filtro_prospeccao_usuario_responsavel
		if($row_relatorio['filtro_prospeccao_usuario_responsavel'] == 1 and $relatorio_id_grupo > 0){
		$header .= 
		"
		<tr>
			<td align='left' valign='top'>
				<strong>Usuário responsável:</strong> $filtro_prospeccao_usuario_responsavel_atual
			</td>
		</tr>
		";
		$contador_filtro ++;
		}
		// fim - filtro_prospeccao_usuario_responsavel

		// filtro_prospeccao_baixa_perda_motivo
		if($row_relatorio['filtro_prospeccao_baixa_perda_motivo'] == 1 and $relatorio_id_grupo > 0){ 
		$header .= 
		"
		<tr>
			<td align='left' valign='top'>
				<strong>Motivo:</strong> $filtro_prospeccao_baixa_perda_motivo_atual 
			</td>
		</tr>
		";
		$contador_filtro ++;
		}
		// fim - filtro_prospeccao_baixa_perda_motivo		
		
		
		
		
		
		// filtro_venda_situacao
		if($row_relatorio['filtro_venda_situacao'] == 1 and $relatorio_id_grupo > 0){ 
		$header .= 
		"
		<tr>
			<td colspan='2' align='left' valign='top'>
				<strong>Situação:</strong> $filtro_venda_situacao_atual 
			</td>
		</tr>
		";
		$contador_filtro ++;
		}
		// fim - filtro_venda_situacao
		
		// filtro_venda_usuario_responsavel
		if($row_relatorio['filtro_venda_usuario_responsavel'] == 1 and $relatorio_id_grupo > 0){

		$header .= 
		"
		<tr>
			<td align='left' valign='top'>
				<strong>Usuário responsável:</strong> $filtro_venda_usuario_responsavel_atual
			</td>
		</tr>
		";
		$contador_filtro ++;
		}
		// fim - filtro_venda_usuario_responsavel
		
		// filtro_venda_tipo_cliente
		if($row_relatorio['filtro_venda_tipo_cliente'] == 1 and $relatorio_id_grupo > 0){ 
		$header .= 
		"
		<tr>
			<td colspan='2' align='left' valign='top'>
				<strong>Tipo de cliente:</strong> $filtro_venda_tipo_cliente_atual 
			</td>
		</tr>
		";
		$contador_filtro ++;
		}
		// fim - filtro_venda_tipo_cliente	
		
		// filtro_venda_modulos
		if($row_relatorio['filtro_venda_modulos'] == 1 and $relatorio_id_grupo > 0){ 
		$header .= 
		"
		<tr>
			<td colspan='2' align='left' valign='top'>
				<strong>Módulos:</strong> $filtro_venda_modulos_atual 
			</td>
		</tr>
		";
		$contador_filtro ++;
		}
		// fim - filtro_venda_modulos
		
		// filtro_administrativo_atraso
		if($row_relatorio['filtro_administrativo_atraso'] == 1 and $relatorio_id_grupo > 0){
		
		$header .= 
		"
		<tr>
			<td width='50%' align='left' valign='top'>
				<strong>Tempo vencido:</strong> Acima de $filtro_administrativo_atraso_atual dia(s) 
			</td>
			<td width='50%' align='right' valign='top'>
				<strong>Limite para exibição de inadimplentes :</strong> ".$row_parametros['relatorio_inadimplencia_limite_atraso']." dia(s)
			</td>
		</tr>
		";
		$contador_filtro ++;
		}
		// fim - filtro_administrativo_atraso
		
		$header .= "</table>";
		$header .= "<div style='height: 1px; background-color: #000; margin-top: 5px; margin-bottom: 0px;'></div>";
		
	}
	// fim - header

	// echo $where;
	// echo $query_registro;
   
	$totalizar_praca_rodape = NULL;
	
	// solicitacao ----------------------------------------------------
	$totalizar_solicitacao_orcamento_retorno = NULL;
	$totalizar_solicitacao_praca_orcamento_retorno = NULL;
	$totalizar_solicitacao_praca_geral_orcamento_retorno = NULL;
	
	$totalizar_solicitacao_tipo_retorno = NULL;
	$totalizar_solicitacao_praca_tipo_retorno = NULL;
	$totalizar_solicitacao_praca_geral_tipo_retorno = NULL;
	
	$totalizar_solicitacao_tempo_retorno = NULL;
	$totalizar_solicitacao_praca_tempo_retorno = NULL;
	$totalizar_solicitacao_praca_geral_tempo_retorno = NULL;
	
	$totalizar_solicitacao_tempo_usuario_responsavel_retorno = NULL;
	$totalizar_solicitacao_praca_tempo_usuario_responsavel_retorno = NULL;
	$totalizar_solicitacao_praca_geral_tempo_usuario_responsavel_retorno = NULL;

	$totalizar_solicitacao_tempo_operador_retorno = NULL;
	$totalizar_solicitacao_praca_tempo_operador_retorno = NULL;
	$totalizar_solicitacao_praca_geral_tempo_operador_retorno = NULL;

	$totalizar_solicitacao_tempo_executante_retorno = NULL;
	$totalizar_solicitacao_praca_tempo_executante_retorno = NULL;
	$totalizar_solicitacao_praca_geral_tempo_executante_retorno = NULL;
	
	$totalizar_solicitacao_tempo_testador_retorno = NULL;
	$totalizar_solicitacao_praca_tempo_testador_retorno = NULL;
	$totalizar_solicitacao_praca_geral_tempo_testador_retorno = NULL;
	
	$totalizar_solicitacao_tempo_testador_geral_retorno = NULL;
	$totalizar_solicitacao_praca_tempo_testador_geral_retorno = NULL;
	$totalizar_solicitacao_praca_geral_tempo_testador_geral_retorno = NULL;
					
	$totalizar_solicitacao_situacao_retorno = NULL;
	$totalizar_solicitacao_praca_situacao_retorno = NULL;
	$totalizar_solicitacao_praca_geral_situacao_retorno = NULL;

	$totalizar_solicitacao_status_retorno = NULL;
	$totalizar_solicitacao_praca_status_retorno = NULL;
	$totalizar_solicitacao_praca_geral_status_retorno = NULL;

	$totalizar_solicitacao_envolvido_retorno = NULL;
	$totalizar_solicitacao_praca_envolvido_retorno = NULL;
	$totalizar_solicitacao_praca_geral_envolvido_retorno = NULL;
	
	$totalizar_solicitacao_tipo_envolvido_retorno = NULL;
	$totalizar_solicitacao_praca_tipo_envolvido_retorno = NULL;
	$totalizar_solicitacao_praca_geral_tipo_envolvido_retorno = NULL;
	
	$totalizar_solicitacao_situacao_envolvido_retorno = NULL;
	$totalizar_solicitacao_praca_situacao_envolvido_retorno = NULL;
	$totalizar_solicitacao_praca_geral_situacao_envolvido_retorno = NULL;
	
	$totalizar_solicitacao_tempo_envolvido_retorno = NULL;
	$totalizar_solicitacao_praca_tempo_envolvido_retorno = NULL;
	$totalizar_solicitacao_praca_geral_tempo_envolvido_retorno = NULL;

	$totalizar_solicitacao_usuario_responsavel_retorno = NULL;
	$totalizar_solicitacao_praca_usuario_responsavel_retorno = NULL;
	$totalizar_solicitacao_praca_geral_usuario_responsavel_retorno = NULL;
	
	$totalizar_solicitacao_executante_retorno = NULL;
	$totalizar_solicitacao_praca_executante_retorno = NULL;
	$totalizar_solicitacao_praca_geral_executante_retorno = NULL;
	
	$totalizar_solicitacao_operador_retorno = NULL;
	$totalizar_solicitacao_praca_operador_retorno = NULL;
	$totalizar_solicitacao_praca_geral_operador_retorno = NULL;

	$totalizar_solicitacao_testador_retorno = NULL;
	$totalizar_solicitacao_praca_testador_retorno = NULL;
	$totalizar_solicitacao_praca_geral_testador_retorno = NULL;

	$totalizar_solicitacao_orcamento_usuario_responsavel_retorno = NULL;
	$totalizar_solicitacao_praca_orcamento_usuario_responsavel_retorno = NULL;
	$totalizar_solicitacao_praca_geral_orcamento_usuario_responsavel_retorno = NULL;
	
	$totalizar_solicitacao_tipo_responsavel_retorno = NULL;
	$totalizar_solicitacao_praca_tipo_responsavel_retorno = NULL;
	$totalizar_solicitacao_praca_geral_tipo_responsavel_retorno = NULL;
	// fim - solicitacao ----------------------------------------------
	
	// suporte --------------------------------------------------------
	$totalizar_suporte_avaliacao_atendimento_retorno = NULL;
	$totalizar_suporte_praca_avaliacao_atendimento_retorno = NULL;
	$totalizar_suporte_praca_geral_avaliacao_atendimento_retorno = NULL;
	
	$totalizar_suporte_tipo_atendimento_retorno = NULL;
	$totalizar_suporte_praca_tipo_atendimento_retorno = NULL;
	$totalizar_suporte_praca_geral_tipo_atendimento_retorno = NULL;
	
	$totalizar_suporte_tipo_recomendacao_retorno = NULL;
	$totalizar_suporte_praca_tipo_recomendacao_retorno = NULL;
	$totalizar_suporte_praca_geral_tipo_recomendacao_retorno = NULL;
	
	$totalizar_suporte_tipo_parecer_retorno = NULL;
	$totalizar_suporte_praca_tipo_parecer_retorno = NULL;
	$totalizar_suporte_praca_geral_tipo_parecer_retorno = NULL;
	
	$totalizar_suporte_tempo_retorno = NULL;
	$totalizar_suporte_praca_tempo_retorno = NULL;
	$totalizar_suporte_praca_geral_tempo_retorno = NULL;
	
	$totalizar_suporte_valor_retorno = NULL;
	$totalizar_suporte_praca_valor_retorno = NULL;
	$totalizar_suporte_praca_geral_valor_retorno = NULL;
	
	$totalizar_suporte_valor_usuario_responsavel_retorno = NULL;
	$totalizar_suporte_praca_valor_usuario_responsavel_retorno = NULL;
	$totalizar_suporte_praca_geral_valor_usuario_responsavel_retorno = NULL;
	
	$totalizar_suporte_situacao_retorno = NULL;
	$totalizar_suporte_praca_situacao_retorno = NULL;
	$totalizar_suporte_praca_geral_situacao_retorno = NULL;
	
	$totalizar_suporte_usuario_responsavel_retorno = NULL;
	$totalizar_suporte_praca_usuario_responsavel_retorno = NULL;
	$totalizar_suporte_praca_geral_usuario_responsavel_retorno = NULL;
	
	$totalizar_suporte_tempo_usuario_responsavel_retorno = NULL;
	$totalizar_suporte_praca_tempo_usuario_responsavel_retorno = NULL;
	$totalizar_suporte_praca_geral_tempo_usuario_responsavel_retorno = NULL;

	$totalizar_suporte_usuario_envolvido_retorno = NULL;
	$totalizar_suporte_praca_usuario_envolvido_retorno = NULL;
	$totalizar_suporte_praca_geral_usuario_envolvido_retorno = NULL;
	
	$totalizar_suporte_tempo_usuario_envolvido_retorno = NULL;
	$totalizar_suporte_praca_tempo_usuario_envolvido_retorno = NULL;
	$totalizar_suporte_praca_geral_tempo_usuario_envolvido_retorno = NULL;
		
	$totalizar_suporte_tipo_visita_retorno = NULL;
	$totalizar_suporte_praca_tipo_visita_retorno = NULL;
	$totalizar_suporte_praca_geral_tipo_visita_retorno = NULL;
	
	$totalizar_suporte_tipo_visita_retorno = NULL;
	$totalizar_suporte_praca_tipo_visita_retorno = NULL;
	$totalizar_suporte_praca_geral_tipo_visita_retorno = NULL;
	
	$totalizar_suporte_optante_acumulo_retorno = NULL;
	$totalizar_suporte_praca_optante_acumulo_retorno = NULL;
	$totalizar_suporte_praca_geral_optante_acumulo_retorno = NULL;
	
	$totalizar_suporte_tipo_parecer_retorno = NULL;
	$totalizar_suporte_praca_tipo_parecer_retorno = NULL;
	$totalizar_suporte_praca_geral_tipo_parecer_retorno = NULL;
	
	$totalizar_suporte_tipo_atendimento_usuario_responsavel_retorno = NULL;
	$totalizar_suporte_praca_tipo_atendimento_usuario_responsavel_retorno = NULL;
	$totalizar_suporte_praca_geral_tipo_atendimento_usuario_responsavel_retorno = NULL;
	
	$totalizar_suporte_avaliacao_atendimento_responsavel_retorno = NULL;
	$totalizar_suporte_praca_avaliacao_atendimento_responsavel_retorno = NULL;
	$totalizar_suporte_praca_geral_avaliacao_atendimento_responsavel_retorno = NULL;
	
	$totalizar_suporte_tipo_atendimento_envolvido_retorno = NULL;
	$totalizar_suporte_praca_tipo_atendimento_envolvido_retorno = NULL;
	$totalizar_suporte_praca_geral_tipo_atendimento_envolvido_retorno = NULL;
	// fim - suporte --------------------------------------------------
	
	// prospeccao -----------------------------------------------------
	$totalizar_prospeccao_baixa_perda_motivo_retorno = NULL;
	$totalizar_prospeccao_praca_baixa_perda_motivo_retorno = NULL;
	$totalizar_prospeccao_praca_geral_baixa_perda_motivo_retorno = NULL;
	
	$totalizar_prospeccao_situacao_retorno = NULL;
	$totalizar_prospeccao_praca_situacao_retorno = NULL;
	$totalizar_prospeccao_praca_geral_situacao_retorno = NULL;
	
	$totalizar_prospeccao_status_retorno = NULL;
	$totalizar_prospeccao_praca_status_retorno = NULL;
	$totalizar_prospeccao_praca_geral_status_retorno = NULL;
	
	$totalizar_prospeccao_tipo_cliente_retorno = NULL;
	$totalizar_prospeccao_praca_tipo_cliente_retorno = NULL;
	$totalizar_prospeccao_praca_geral_tipo_cliente_retorno = NULL;
	
	$totalizar_prospeccao_ativo_passivo_retorno = NULL;
	$totalizar_prospeccao_praca_ativo_passivo_retorno = NULL;
	$totalizar_prospeccao_praca_geral_ativo_passivo_retorno = NULL;
	
	$totalizar_prospeccao_usuario_responsavel_retorno = NULL;
	$totalizar_prospeccao_praca_usuario_responsavel_retorno = NULL;
	$totalizar_prospeccao_praca_geral_usuario_responsavel_retorno = NULL;
	// fim - prospeccao -----------------------------------------------
	
	// venda ----------------------------------------------------------
	$totalizar_venda_situacao_retorno = NULL;
	$totalizar_venda_praca_situacao_retorno = NULL;
	$totalizar_venda_praca_geral_situacao_retorno = NULL;

	$totalizar_venda_usuario_responsavel_retorno = NULL;
	$totalizar_venda_praca_usuario_responsavel_retorno = NULL;
	$totalizar_venda_praca_geral_usuario_responsavel_retorno = NULL;
	
	$totalizar_venda_valor_venda_retorno = NULL;
	$totalizar_venda_praca_valor_venda_retorno = NULL;
	$totalizar_venda_praca_geral_valor_venda_retorno = NULL;
	
	$totalizar_venda_valor_treinamento_retorno = NULL;
	$totalizar_venda_praca_valor_treinamento_retorno = NULL;
	$totalizar_venda_praca_geral_valor_treinamento_retorno = NULL;
	
	$totalizar_venda_modulos_retorno = NULL;
	$totalizar_venda_praca_modulos_retorno = NULL;
	$totalizar_venda_praca_geral_modulos_retorno = NULL;
	
	$totalizar_venda_tempo_retorno = NULL;
	$totalizar_venda_praca_tempo_retorno = NULL;
	$totalizar_venda_praca_geral_tempo_retorno = NULL;
	
	$totalizar_venda_tempo_gasto_retorno = NULL;
	$totalizar_venda_praca_tempo_gasto_retorno = NULL;
	$totalizar_venda_praca_geral_tempo_gasto_retorno = NULL;
	
	$totalizar_venda_tempo_gasto_usuario_responsavel_retorno = NULL;
	$totalizar_venda_praca_tempo_gasto_usuario_responsavel_retorno = NULL;
	$totalizar_venda_praca_geral_tempo_gasto_usuario_responsavel_retorno = NULL;
	
	$totalizar_venda_valor_venda_usuario_responsavel_retorno = NULL;
	$totalizar_venda_praca_valor_venda_usuario_responsavel_retorno = NULL;
	$totalizar_venda_praca_geral_valor_venda_usuario_responsavel_retorno = NULL;
	
	$totalizar_venda_valor_treinamento_responsavel_retorno = NULL;
	$totalizar_venda_praca_valor_treinamento_responsavel_retorno = NULL;
	$totalizar_venda_praca_geral_valor_treinamento_responsavel_retorno = NULL;
	
	$totalizar_venda_tempo_usuario_responsavel_retorno = NULL;
	$totalizar_venda_praca_tempo_usuario_responsavel_retorno = NULL;
	$totalizar_venda_praca_geral_tempo_usuario_responsavel_retorno = NULL;
	
	$totalizar_venda_dilacao_prazo_retorno = NULL;
	$totalizar_venda_praca_dilacao_prazo_retorno = NULL;
	$totalizar_venda_praca_geral_dilacao_prazo_retorno = NULL;
	
	$totalizar_venda_tipo_cliente_retorno = NULL;
	$totalizar_venda_praca_tipo_cliente_retorno = NULL;
	$totalizar_venda_praca_geral_tipo_cliente_retorno = NULL;
	// fim - venda ----------------------------------------------------
		
    if ($totalRows_registro > 0){
    
		// table ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
		$table = NULL;
		$table .= "<table class='registro_tabela' id='registro_tabela".$row_relatorio['id']."' cellspacing='0' cellpadding='0' style='border-spacing: 0;'>";
		
		// thead -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
		$table .= "<thead>";
		$table .= "<tr>"; 
	
		// $relatorio_campos_thead
		$relatorio_campos_thead = mysql_query($query_relatorio_campos, $conexao) or die(mysql_error());
		$row_relatorio_campos_thead = mysql_fetch_assoc($relatorio_campos_thead);
		$totalRows_relatorio_campos_thead = mysql_num_rows($relatorio_campos_thead);
		do {
	
			$table .= "<th "; 
			if($row_relatorio_campos_thead['tamanho']!=NULL){ $table .= "width='".$row_relatorio_campos_thead['tamanho']."'"; }
			$table .= ">".utf8_encode($row_relatorio_campos_thead['titulo'])."</th>";
			
		} while ($row_relatorio_campos_thead = mysql_fetch_assoc($relatorio_campos_thead));
		mysql_free_result($relatorio_campos_thead);
		// fim - $relatorio_campos_thead
					  
		$table .= "</tr>";
		$table .= "</thead>"; 
		// fim - thead -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
			
		// tbody -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
		$table .=  "<tbody>";
		
		// totalizar - início ------------------------------------------------------------------------------------------------------------------------------------
		include('relatorio_totalizar_inicio.php');
		// fim - totalizar - início -----------------------------------------------------------------------------------------------------------------------------
					 
		$registro_contador = 0;
		$praca_contador_nome = NULL;
		$contador_teste = 1;
		$total_por_praca = NULL;
		$thead_praca_contador = 0;
			
		do {
			
			// total_por_praca
			if(isset($_GET['filtro_geral_praca']) and $_GET['filtro_geral_praca'] == NULL and $acesso == 1 and $where_praca <> NULL){
				$contador_array = 0;
				foreach($array_praca_geral as $item){
					$contador_array ++;
					if($item['praca'] == @$row_registro['praca']){
						$total_por_praca = $item['contador'];
					}
				}
			}
			// fim - total_por_praca
			
			// totalizar - fórmula ---------------------------------------------------------------------------------------------------------------------
			include('relatorio_totalizar_formula.php');
			// fim - totalizar - fórmula  --------------------------------------------------------------------------------------------------------------
			
			// $relatorio_campos_tbody
			$relatorio_campos_tbody = mysql_query($query_relatorio_campos, $conexao) or die(mysql_error());
			$row_relatorio_campos_tbody = mysql_fetch_assoc($relatorio_campos_tbody);
			$totalRows_relatorio_campos_tbody = mysql_num_rows($relatorio_campos_tbody);
			// fim - $relatorio_campos_tbody
			
			$registro_contador = $registro_contador + 1;
			
			// praca (cabeçalho)
			if(isset($_GET['filtro_geral_praca']) and $_GET['filtro_geral_praca'] == NULL and $acesso == 1 and $where_praca <> NULL){
				
				if($praca_contador_nome != @$row_registro['praca']){
					
					// thead (praça) ------------------------------------------------------------------------------------------------------------------------------------------------------------------------
					if($thead_praca_contador > 0){
						$table .= "<tr>"; 
			
						// $relatorio_campos_thead
						$relatorio_campos_thead = mysql_query($query_relatorio_campos, $conexao) or die(mysql_error());
						$row_relatorio_campos_thead = mysql_fetch_assoc($relatorio_campos_thead);
						$totalRows_relatorio_campos_thead = mysql_num_rows($relatorio_campos_thead);
						do {
							
							$table .= "<th "; 
							if($row_relatorio_campos_thead['tamanho']!=NULL){ $table .= "width='".$row_relatorio_campos_thead['tamanho']."'"; }
							$table .= ">".utf8_encode($row_relatorio_campos_thead['titulo'])."</th>";
							
						} while ($row_relatorio_campos_thead = mysql_fetch_assoc($relatorio_campos_thead));
						mysql_free_result($relatorio_campos_thead);
						// fim - $relatorio_campos_thead
									  
						$table .= "</tr>";
					}
					$thead_praca_contador = 1;
					// fim - thead (praça) -------------------------------------------------------------------------------------------------------------------------------------------------------------------
	
					$table .= "
					<tr>
						<td style='padding: 5px; font-size: 14px; font-weight: bold; border-top: 1px solid #000; border-bottom: 1px solid #000; color: #000' colspan='".$totalRows_relatorio_campos_tbody."'>
					".@$row_registro['praca']."</td>
					</tr>";
					$contador_teste = 1;
					
				} else {
					$contador_teste = $contador_teste + 1;
				}
				$praca_contador_nome = @$row_registro['praca'];
				
			}
			// fim - praca (cabeçalho)
			
			// destaque (destaca o registro que pertence à praça atual)
			$destaque = NULL;
			if($row_relatorio['id_grupo_subgrupo'] == 100 and $row_registro['praca'] == $_SESSION['MM_praca']){
				if($row_relatorio['id'] == 100){
					$destaque = " style='font-weight: bold; font-size: 12px;'";
				} else {
					$destaque = " style='font-weight: bold;'";
				}
			}
			// fim - destaque (destaca o registro que pertence à praça atual)
			
			$table .=  "<tr class='";
			if (($registro_contador % 2)==0){$table .= 'linha1';}else{$table .= 'linha2';} 
			$table .=  "'".$destaque.">";
	
			// $campo
			do {
				
				$alinhamento = 'left';
				if($row_relatorio_campos_tbody['alinhamento'] == 'l'){$alinhamento = 'left';}
				if($row_relatorio_campos_tbody['alinhamento'] == 'c'){$alinhamento = 'center';}
				if($row_relatorio_campos_tbody['alinhamento'] == 'r'){$alinhamento = 'right';}
				
				$table .=  "<td style='text-align: $alinhamento;'>";
				if($row_relatorio_campos_tbody['campo']!=NULL){
					
					$campo = @$row_registro[$row_relatorio_campos_tbody['campo']]; 
					
					// tipo: date
					if($row_relatorio_campos_tbody['tipo']=='date'){
					if($campo!=''){$campo = date('d-m-Y', strtotime($campo));}
					}
					// fim - tipo: date
					
					// tipo: datetime
					if($row_relatorio_campos_tbody['tipo']=='datetime'){
					if($campo!=''){$campo = date('d-m-Y H:i', strtotime($campo));}
					}
					// fim - tipo: datetime
									
					// utf8
					if($row_relatorio_campos_tbody['utf8']=='s'){
					$campo = utf8_encode($campo); 
					}
					// fim - utf8
					
					// moeda
					if($row_relatorio_campos_tbody['moeda']=='s'){
					$campo = 'R$ '. number_format($campo, 2, ',', '.'); 
					}
					// fim - moeda
					
					// Exceções -------------------------------------------------------------------------------------------------------------------------------
					
					// solicitacao
					if($row_relatorio['id_grupo']==1){
						
						// empresa
						if($row_relatorio_campos_tbody['campo']=='empresa'){
							$campo = limita_caracteres($campo, 40);
						}
						// fim - empresa
					
						// tempo_gasto
						if($row_relatorio_campos_tbody['campo']=='tempo_gasto'){
							
							$campo = tempo_gasto_conversao($row_registro['tempo_gasto']);
							
						}
						// fim - tempo_gasto
						
						// tempo_gasto_testador
						if($row_relatorio_campos_tbody['campo']=='tempo_gasto_testador'){
	
							$campo = tempo_gasto_conversao($row_registro['tempo_gasto_testador']);
							
						}
						// fim - tempo_gasto_testador
						
						// status
						if($row_relatorio_campos_tbody['campo']=='status'){
							if($campo=="pendente solicitante"){ $campo = 'Pen. Sol.'; }
							if($campo=="pendente operador"){ $campo = 'Pen. Op.'; }
							if($campo=="pendente executante"){ $campo = 'Pen. Exec.'; }
							if($campo=="pendente testador"){ $campo = 'Pen. Test.'; }
							if($campo=="encaminhada para solicitante"){ $campo = 'Enc. Sol.'; }
							if($campo=="encaminhada para operador"){ $campo = 'Enc. Op.'; }
							if($campo=="encaminhada para executante"){ $campo = 'Enc. Exec.'; }
							if($campo=="encaminhada para testador"){ $campo = 'Enc. Test.'; }
							if($campo=="encaminhada para analista"){ $campo = 'Enc. Anali.'; }
							if($campo=="devolvida para executante"){ $campo = 'Dev. Exec.'; }
							if($campo=="devolvida para operador"){ $campo = 'Dev. Op.'; }
						}
						// fim - status
						
						// envolvido
						if($row_relatorio_campos_tbody['campo']=='envolvido'){
							$campo = limita_caracteres($campo, 10);
						}
						// fim - envolvido
					
					}
					// fim - solicitacao
	
					// suporte
					if($row_relatorio['id_grupo']==2){
						
						// empresa
						if($row_relatorio_campos_tbody['campo']=='empresa'){
							$campo = limita_caracteres($campo, 40);
						}
						// fim - empresa
						
						// tempo_gasto
						if($row_relatorio_campos_tbody['campo']=='tempo_gasto'){
							
							$campo = tempo_gasto_conversao($row_registro['tempo_gasto']);
							
						}
						// fim - tempo_gasto
					
						// tempo
						if($row_relatorio_campos_tbody['campo']=='tempo'){
							
							$campo = $campo_diferenca_data = NULL;
							
							if($row_registro['data_inicio'] <> "0000-00-00 00:00:00" and $row_registro['data_fim'] <> NULL and $row_registro['data_fim'] <> "0000-00-00 00:00:00"){
								$campo_diferenca_data = (strtotime($row_registro['data_fim']) - strtotime($row_registro['data_inicio']))/60;
								$campo = tempo_gasto_conversao($campo_diferenca_data);
							}
							
						}
						// fim - tempo
						
						// optacuv17
						if($row_relatorio_campos_tbody['campo']=='optacuv17'){
							if($campo=='N'){ $campo = 'Não'; }
							if($campo=='S'){ $campo = 'Sim'; }
						}
						// fim - optacuv17
						
						// visita17
						if($row_relatorio_campos_tbody['campo']=='visita17'){
							if($campo==1){ $campo = 'Nenhum'; }
							if($campo==2){ $campo = 'Sem Limite'; }
							if($campo==3){ $campo = 'Mensal'; }
							if($campo==4){ $campo = 'Trimestral'; }
							if($campo==5){ $campo = 'Sem visita'; }
						}
						// fim - visita17
						
						// parecer
						if($row_relatorio_campos_tbody['campo']=='parecer'){
							if($campo=="Suporte por Falta de Conhecimento ou Habilidade"){ $campo = 'Falta de Conhecimento ou Habilidade'; }
							if($campo=="Suporte Necessario"){ $campo = 'Necessário'; }
							if($campo=="Suporte Desnecessario"){ $campo = 'Desnecessário'; }
						}
						// fim - parecer
						
						// tipo_atendimento
						if($row_relatorio_campos_tbody['campo']=='tipo_atendimento'){
							if($campo=="Liberacao PDV"){ $campo = 'Lib. PDV'; }
							if($campo=="Liberacao Terminal"){ $campo = 'Lib. Terminal'; }
							if($campo=="Liberacao Servidor"){ $campo = 'Lib. Servidor'; }
							if($campo=="Liberacao Inicial Geral"){ $campo = 'Lib. Inicial Geral'; }
							if($campo=="Duvida e Orientacao"){ $campo = 'Dúv. e Orient.'; }
							if($campo=="Erro Encontrado"){ $campo = 'Erro'; }
							if($campo=="Configuracao Equipamentos"){ $campo = 'Conf. Equipamentos'; }
							if($campo=="Configuracao Sistema"){ $campo = 'Conf. Sistema'; }
							if($campo=="Necessidade de Recurso"){ $campo = 'Nec. Recurso'; }
							if($campo=="Site"){ $campo = 'Site'; }
						}
						// fim - tipo_atendimento
						
						// situacao
						if($row_relatorio_campos_tbody['campo']=='situacao'){
							if($campo=="solicitado suporte"){ $campo = 'sol. suporte'; }
							if($campo=="solicitado visita"){ $campo = 'sol. visita'; }
							if($campo=="encaminhado para solicitação"){ $campo = 'enc. solicitação'; }
						}
						// fim - situacao
					
					}
					// fim - suporte
	
					// prospeccao 
					if($row_relatorio['id_grupo']==3){
						
						// nome_razao_social
						if($row_relatorio_campos_tbody['campo']=='nome_razao_social'){
							if($row_registro['tipo_cliente'] == 'n'){
								$campo = limita_caracteres($campo, 40); // novo
							} else {
								$campo = limita_caracteres(utf8_encode($campo), 40); // antigo
							}
							$campo = strtoupper($campo);
						}
						// fim - nome_razao_social
					
						// tipo_cliente 
						if($row_relatorio_campos_tbody['campo']=='tipo_cliente'){
							if($campo=='a'){ $campo = 'antigo'; }
							if($campo=='n'){ $campo = 'novo'; }
						}
						// fim - tipo_cliente 
						
						// ativo_passivo 
						if($row_relatorio_campos_tbody['campo']=='ativo_passivo'){
							if($campo=='a'){ $campo = 'ativo'; }
							if($campo=='p'){ $campo = 'passivo'; }
						}
						// fim - ativo_passivo 
						
						// status
						if($row_relatorio_campos_tbody['campo']=='situacao'){
							if($campo=="solicitado agendamento"){ $campo = 'sol. agend.'; }
						}
						// fim - status
						
						// status
						if($row_relatorio_campos_tbody['campo']=='status'){
							if($campo=="aguardando retorno do cliente"){ $campo = 'Aguard. Cli.'; }
							if($campo=="aguardando atendente"){ $campo = 'Aguard. Aten.'; }
							if($campo=="aguardando agendamento"){ $campo = 'Aguard. Agend.'; }
							if($campo=="encaminhada para usuario responsavel"){ $campo = 'Enc. Resp.'; }
							if($campo=="pendente usuario responsavel"){ $campo = 'Pen. Resp.'; }
	
						}
						// fim - status
	
					}
					// fim - prospeccao 
					
					// venda 
					if($row_relatorio['id_grupo']==4){
						
						// empresa
						if($row_relatorio_campos_tbody['campo']=='empresa'){
							$campo = limita_caracteres($campo, 40);
						}
						// fim - empresa
					
						// tipo_cliente 
						if($row_relatorio_campos_tbody['campo']=='tipo_cliente'){
							if($campo=='a'){ $campo = 'antigo'; }
							if($campo=='n'){ $campo = 'novo'; }
						}
						// fim - tipo_cliente 
						
						// modulos 
						if($row_relatorio_campos_tbody['campo']=='modulos'){
						
							// venda_modulos
							mysql_select_db($database_conexao, $conexao);
							$query_venda_modulos = sprintf('
							SELECT 
							geral_tipo_modulo.descricao AS modulo
							FROM venda_modulos 
							LEFT JOIN geral_tipo_modulo ON venda_modulos.id_modulo = geral_tipo_modulo.IdTipoModulo 
							WHERE venda_modulos.id_venda = %s', 
							GetSQLValueString($row_registro['id'], 'int'));
							$venda_modulos = mysql_query($query_venda_modulos, $conexao) or die(mysql_error());
							$row_venda_modulos = mysql_fetch_assoc($venda_modulos);
							$totalRows_venda_modulos = mysql_num_rows($venda_modulos);
							// fim - venda_modulos
							
							$contador_venda_modulos = 0; 
							$modulos = NULL;
								   
						do {
						  $contador_venda_modulos = $contador_venda_modulos + 1;
						  $modulos .= $row_venda_modulos['modulo']; if($contador_venda_modulos < $totalRows_venda_modulos){ $modulos .= ', '; }
						} while ($row_venda_modulos = mysql_fetch_assoc($venda_modulos));
						
						$campo = $modulos;
						
						mysql_free_result($venda_modulos);
						
						}
						// fim - modulos 
						
						// dilacao_prazo 
						if($row_relatorio_campos_tbody['campo']=='dilacao_prazo'){
						if($campo==0){ $campo = 'Não'; }else{ $campo = 'Sim ('.$row_registro['dilacao_prazo'].')'; }
						}
						// fim - dilacao_prazo 
						
						// dilacao_prazo_dias
						if($row_relatorio_campos_tbody['campo']=='dilacao_prazo_dias'){
	
							$campo = $row_registro['dilacao_prazo']; 
	
						}
						// fim - dilacao_prazo_dias
		
						// tempo 
						if($row_relatorio_campos_tbody['campo']=='tempo'){
								
							$data_ini = strtotime($row_registro['agenda_data_inicio']);
							$data_final = strtotime($row_registro['agenda_data']);
			
							$tHoras = ($data_final - $data_ini) / 3600;
							$tMinutos = ($data_final - $data_ini) % 3600 / 60;
							
							$campo = sprintf('%02dh %02dm', $tHoras, $tMinutos);
						   
						}
						// fim - tempo
						
						// tempo_gasto
						if($row_relatorio_campos_tbody['campo']=='tempo_gasto'){
							
							$campo = tempo_gasto_conversao($row_registro['tempo_gasto']);
							
						}
						// fim - tempo_gasto
										
						// validade (data)
						if($row_relatorio_campos_tbody['campo']=='validade'){
	
							$venda_validade_dias = $row_parametros['venda_validade_dias'] + $row_registro['dilacao_prazo'];
							$campo = date('d-m-Y', strtotime("+$venda_validade_dias days", strtotime($row_registro['data_venda'])));
	
						}
						// fim - validade (data)
						
						// validade_dias 
						if($row_relatorio_campos_tbody['campo']=='validade_dias'){
	
							$venda_validade_dias = $row_parametros['venda_validade_dias'] + $row_registro['dilacao_prazo'];
							$validade_atual = date('d-m-Y', strtotime("+$venda_validade_dias days", strtotime($row_registro['data_venda'])));
							$diferenca = strtotime($validade_atual) - strtotime(date('Y-m-d'));
							$campo = (int)floor( $diferenca / (60 * 60 * 24)); 
	
						}
						// fim - validade_dias
						
						// situacao
						if($row_relatorio_campos_tbody['campo']=='situacao'){
							if($campo=="solicitado suporte"){ $campo = 'sol. suporte'; }
							if($campo=="solicitado visita"){ $campo = 'sol. visita'; }
							if($campo=="encaminhado para solicitação"){ $campo = 'enc. solicitação'; }
						}
						// fim - situacao
					
					}
					// fim - venda 
	
					
					// administrativo 
					if($row_relatorio['id_grupo']==5){
						
						// 0: contratos_ativos
						if($row_relatorio_campos_tbody['campo']=='contratos_ativos'){
							mysql_select_db($database_conexao, $conexao);
							$query_contratos_ativos = "
							SELECT COUNT(da37.sql_rowid) as retorno 
							FROM da37 
							LEFT JOIN da01 ON da37.cliente17 = da01.codigo1 
							LEFT JOIN geral_tipo_praca_executor ON da37.vendedor17 = geral_tipo_praca_executor.IdExecutor 
							WHERE geral_tipo_praca_executor.praca = '".$row_registro['praca']."' and (status17 = 'D' or status17 = 'P') and da01.atraso1 < 180 and da37.sr_deleted <> 'T' 
							";
							$contratos_ativos = mysql_query($query_contratos_ativos, $conexao) or die(mysql_error());
							$row_contratos_ativos = mysql_fetch_assoc($contratos_ativos);
							$totalRows_contratos_ativos = mysql_num_rows($contratos_ativos);
							$campo = $row_contratos_ativos['retorno'];
							$relatorio_evento_array[$row_registro['praca']][0] = $campo;
							$totalizar_contrato_ativo_retorno = $totalizar_contrato_ativo_retorno + $campo;
							
							mysql_free_result($contratos_ativos);
						}
						// 0: fim - contratos_ativos
						
						// -----------------------------------------------------------------------------------

						// 1: clientes_manutencao_atendidos
						if($row_relatorio_campos_tbody['campo']=='clientes_manutencao_atendidos'){
							mysql_select_db($database_conexao, $conexao);
							$query_clientes_manutencao_atendidos = "
							SELECT 
								COUNT(da37.codigo17) AS retorno
							FROM 
								da37 
							RIGHT JOIN 
								suporte ON da37.codigo17 = suporte.contrato 
							LEFT JOIN 
								agenda ON suporte.id = agenda.id_suporte  
							WHERE 								
								suporte.praca = '".$row_registro['praca']."' AND 
								(( agenda.data_inicio BETWEEN '$colname_suporte_filtro_geral_data_criacao' AND '$colname_suporte_filtro_geral_data_criacao_fim' )) AND 
								( suporte.situacao = 'solucionada' ) AND 
								suporte.tipo_suporte = 'c' AND suporte.inloco = 's' AND 
								(
									suporte.tipo_formulario = 'Manutencao' OR 
									suporte.tipo_formulario = 'Cobranca' OR 
									( suporte.tipo_formulario = 'Treinamento' AND suporte.creditar = 's' ) OR 
									( suporte.tipo_formulario = 'Reclamacao' AND suporte.creditar = 's' )
								)
							";
							$clientes_manutencao_atendidos = mysql_query($query_clientes_manutencao_atendidos, $conexao) or die(mysql_error());
							$row_clientes_manutencao_atendidos = mysql_fetch_assoc($clientes_manutencao_atendidos);
							$totalRows_clientes_manutencao_atendidos = mysql_num_rows($clientes_manutencao_atendidos);
							$campo = $row_clientes_manutencao_atendidos['retorno'];
							$relatorio_evento_array[$row_registro['praca']][1] = $campo;
							mysql_free_result($clientes_manutencao_atendidos);
						
						}
						// 2: fim - clientes_manutencao_atendidos

						// 2: clientes_manutencao_atendidos_extra
						if($row_relatorio_campos_tbody['campo']=='clientes_manutencao_atendidos_extra'){
							mysql_select_db($database_conexao, $conexao);
							$query_clientes_manutencao_atendidos_extra = "
							SELECT 
								COUNT(suporte.id) as retorno 
							FROM 
								suporte 
							LEFT JOIN 
								suporte_formulario ON suporte.id = suporte_formulario.id_suporte 
							WHERE 
								suporte.praca = '".$row_registro['praca']."' and 
								suporte.situacao = 'solucionada' and 
								suporte.tipo_suporte = 'c' and suporte.inloco = 's' and suporte_formulario.tipo_formulario = 'Extra' and 
								(suporte.data_suporte between '$colname_suporte_filtro_geral_data_criacao' and '$colname_suporte_filtro_geral_data_criacao_fim')
							";
							$clientes_manutencao_atendidos_extra = mysql_query($query_clientes_manutencao_atendidos_extra, $conexao) or die(mysql_error());
							$row_clientes_manutencao_atendidos_extra = mysql_fetch_assoc($clientes_manutencao_atendidos_extra);
							$totalRows_clientes_manutencao_atendidos_extra = mysql_num_rows($clientes_manutencao_atendidos_extra);
							$campo = $row_clientes_manutencao_atendidos_extra['retorno'];
							$relatorio_evento_array[$row_registro['praca']][2] = $campo;
							mysql_free_result($clientes_manutencao_atendidos_extra);						
						}
						// fim - 2: clientes_manutencao_atendidos_extra
						
						// 3: suporte_inloco_nao
						if($row_relatorio_campos_tbody['campo']=='suporte_inloco_nao'){
	
							mysql_select_db($database_conexao, $conexao);
							$query_suporte_inloco_nao = "
							SELECT COUNT(suporte.id) as retorno 
							FROM suporte 
							WHERE 
							suporte.tipo_suporte = 'c' and suporte.inloco = 'n' and suporte.situacao = 'solucionada' and 
							suporte.praca = '".$row_registro['praca']."' and 
							(suporte.data_suporte_fim between '$colname_suporte_filtro_geral_data_criacao' and '$colname_suporte_filtro_geral_data_criacao_fim')
							";
							$suporte_inloco_nao = mysql_query($query_suporte_inloco_nao, $conexao) or die(mysql_error());
							$row_suporte_inloco_nao = mysql_fetch_assoc($suporte_inloco_nao);
							$totalRows_suporte_inloco_nao = mysql_num_rows($suporte_inloco_nao);
							$campo = $row_suporte_inloco_nao['retorno'];
							$relatorio_evento_array[$row_registro['praca']][3] = $campo;
							mysql_free_result($suporte_inloco_nao);
						
						}
						// fim - 3: suporte_inloco_nao
						
						// 4: prospeccoes_site
						if($row_relatorio_campos_tbody['campo']=='prospeccoes_site'){
	
							mysql_select_db($database_conexao, $conexao);
							$query_prospeccoes_site = "
							SELECT COUNT(prospeccao.id) as retorno 
							FROM prospeccao 
							WHERE 
							prospeccao.status_flag = 'a' and 
							prospeccao.praca = '".$row_registro['praca']."'
							";
							$prospeccoes_site = mysql_query($query_prospeccoes_site, $conexao) or die(mysql_error());
							$row_prospeccoes_site = mysql_fetch_assoc($prospeccoes_site);
							$totalRows_prospeccoes_site = mysql_num_rows($prospeccoes_site);
							$campo = $row_prospeccoes_site['retorno'];
							$relatorio_evento_array[$row_registro['praca']][4] = $campo;
							mysql_free_result($prospeccoes_site);
						
						}
						// fim - 4: prospeccoes_site
						
						// 5: vendas_site_efetivadas
						if($row_relatorio_campos_tbody['campo']=='vendas_site_efetivadas'){
	
							mysql_select_db($database_conexao, $conexao);
							$query_vendas_site_efetivadas = "
							SELECT 
								COUNT(venda.id) as retorno 
							FROM
								venda 
							WHERE
								venda.praca = '".$row_registro['praca']."' AND 
								(( venda.data_venda BETWEEN '$colname_suporte_filtro_geral_data_criacao' and '$colname_suporte_filtro_geral_data_criacao_fim')) AND 
								venda.situacao <> 'cancelada'
							";
							$vendas_site_efetivadas = mysql_query($query_vendas_site_efetivadas, $conexao) or die(mysql_error());
							$row_vendas_site_efetivadas = mysql_fetch_assoc($vendas_site_efetivadas);
							$totalRows_vendas_site_efetivadas = mysql_num_rows($vendas_site_efetivadas);
							$campo = $row_vendas_site_efetivadas['retorno'];
							$relatorio_evento_array[$row_registro['praca']][5] = $campo;
							mysql_free_result($vendas_site_efetivadas);
						
						}
						// fim - 5: vendas_site_efetivadas
						
						// 6: treinamento_site_concluidos
						if($row_relatorio_campos_tbody['campo']=='treinamento_site_concluidos'){
	
							mysql_select_db($database_conexao, $conexao);
							$query_treinamento_site_concluidos = "
							SELECT COUNT(venda.id) as retorno
							FROM venda 
							WHERE 
							venda.praca = '".$row_registro['praca']."' and 
							venda.conclusao_implantacao_treinamento = 1 and venda.valor_treinamento > 0 and 
							(venda.conclusao_implantacao_treinamento_data between '$colname_suporte_filtro_geral_data_criacao' and '$colname_suporte_filtro_geral_data_criacao_fim')
							";
							$treinamento_site_concluidos = mysql_query($query_treinamento_site_concluidos, $conexao) or die(mysql_error());
							$row_treinamento_site_concluidos = mysql_fetch_assoc($treinamento_site_concluidos);
							$totalRows_treinamento_site_concluidos = mysql_num_rows($treinamento_site_concluidos);
							$campo = $row_treinamento_site_concluidos['retorno'];
							$relatorio_evento_array[$row_registro['praca']][6] = $campo;
							mysql_free_result($treinamento_site_concluidos);
						
						}
						// fim - 6: treinamento_site_concluidos
						
						// 7: implantacao_site_concluidos
						if($row_relatorio_campos_tbody['campo']=='implantacao_site_concluidos'){
	
							mysql_select_db($database_conexao, $conexao);
							$query_implantacao_site_concluidos = "
							SELECT COUNT(venda.id) as retorno 
							FROM venda 
							WHERE 
							venda.praca = '".$row_registro['praca']."' and 
							venda.situacao = 'solucionada' and venda.encerramento_automatico = 0 and 
							(venda.data_fim between '$colname_suporte_filtro_geral_data_criacao' and '$colname_suporte_filtro_geral_data_criacao_fim')
							";
							$implantacao_site_concluidos = mysql_query($query_implantacao_site_concluidos, $conexao) or die(mysql_error());
							$row_implantacao_site_concluidos = mysql_fetch_assoc($implantacao_site_concluidos);
							$totalRows_implantacao_site_concluidos = mysql_num_rows($implantacao_site_concluidos);
							$campo = $row_implantacao_site_concluidos['retorno'];
							$relatorio_evento_array[$row_registro['praca']][7] = $campo;
							mysql_free_result($implantacao_site_concluidos);
						
						}
						// fim - 7: implantacao_site_concluidos	
						
						// 8: solicitacoes_criadas_periodo
						if($row_relatorio_campos_tbody['campo']=='solicitacoes_criadas_periodo'){
	
							mysql_select_db($database_conexao, $conexao);
							$query_solicitacoes_criadas_periodo = "
							SELECT COUNT(solicitacao.id) as retorno 
							FROM solicitacao 
							WHERE 
							solicitacao.praca = '".$row_registro['praca']."' and 
							(solicitacao.dt_solicitacao between '$colname_suporte_filtro_geral_data_criacao' and '$colname_suporte_filtro_geral_data_criacao_fim')
							";
							$solicitacoes_criadas_periodo = mysql_query($query_solicitacoes_criadas_periodo, $conexao) or die(mysql_error());
							$row_solicitacoes_criadas_periodo = mysql_fetch_assoc($solicitacoes_criadas_periodo);
							$totalRows_solicitacoes_criadas_periodo = mysql_num_rows($solicitacoes_criadas_periodo);
							$campo = $row_solicitacoes_criadas_periodo['retorno'];
							$relatorio_evento_array[$row_registro['praca']][8] = $campo;
							mysql_free_result($solicitacoes_criadas_periodo);
						
						}
						// 8: fim - solicitacoes_criadas_periodo
						
						// 9: solicitacoes_vendidas_periodo
						if($row_relatorio_campos_tbody['campo']=='solicitacoes_vendidas_periodo'){
	
							mysql_select_db($database_conexao, $conexao);
							$query_solicitacoes_vendidas_periodo = "
							SELECT COUNT(solicitacao.id) as retorno 
							FROM solicitacao 
							WHERE 
							solicitacao.praca = '".$row_registro['praca']."' and 
							solicitacao.orcamento_os IS NOT NULL and 
							(solicitacao.dt_solicitacao between '$colname_suporte_filtro_geral_data_criacao' and '$colname_suporte_filtro_geral_data_criacao_fim')
							";
							$solicitacoes_vendidas_periodo = mysql_query($query_solicitacoes_vendidas_periodo, $conexao) or die(mysql_error());
							$row_solicitacoes_vendidas_periodo = mysql_fetch_assoc($solicitacoes_vendidas_periodo);
							$totalRows_solicitacoes_vendidas_periodo = mysql_num_rows($solicitacoes_vendidas_periodo);
							$campo = $row_solicitacoes_vendidas_periodo['retorno'];
							$relatorio_evento_array[$row_registro['praca']][9] = $campo;
							mysql_free_result($solicitacoes_vendidas_periodo);
						
						}
						// 9: fim - solicitacoes_vendidas_periodo
						
						// -----------------------------------------------------------------------------------
						
						// 10: reclamacoes_site
						if($row_relatorio_campos_tbody['campo']=='reclamacoes_site'){
	
							mysql_select_db($database_conexao, $conexao);
							$query_reclamacoes_site = "
							SELECT 
								COUNT(suporte.id) as retorno 
							FROM 
								suporte 
							WHERE 
								suporte.tipo_suporte = 'r' and 
								status_flag <> 'f' and 
								suporte.praca = '".$row_registro['praca']."'
							";
							// (suporte.data_inicio between '$colname_suporte_filtro_geral_data_criacao' and '$colname_suporte_filtro_geral_data_criacao_fim') and 
							$reclamacoes_site = mysql_query($query_reclamacoes_site, $conexao) or die(mysql_error());
							$row_reclamacoes_site = mysql_fetch_assoc($reclamacoes_site);
							$totalRows_reclamacoes_site = mysql_num_rows($reclamacoes_site);
							$campo = $row_reclamacoes_site['retorno'];
							$relatorio_evento_array[$row_registro['praca']][10] = $campo;
							mysql_free_result($reclamacoes_site);
						
						}
						// fim - 10: reclamacoes_site
						
						// 11: cancelamentos ***
						if($row_relatorio_campos_tbody['campo']=='cancelamentos'){

							mysql_select_db($database_conexao, $conexao);
							$query_cancelamentos = "
							SELECT 
								COUNT(da37.sql_rowid) as retorno
							FROM 
								da37 
							LEFT JOIN 
								da01 ON da37.cliente17 = da01.codigo1 
							LEFT JOIN 
								geral_tipo_praca_executor ON da37.vendedor17 = geral_tipo_praca_executor.IdExecutor 
							WHERE 
								geral_tipo_praca_executor.praca = '".$row_registro['praca']."' and 
								da37.status17 = 'C' and 
								(da37.datcan17 between '$colname_suporte_filtro_geral_data_criacao' and '$colname_suporte_filtro_geral_data_criacao_fim')
							";
							$cancelamentos = mysql_query($query_cancelamentos, $conexao) or die(mysql_error());
							$row_cancelamentos = mysql_fetch_assoc($cancelamentos);
							$totalRows_cancelamentos = mysql_num_rows($cancelamentos);
							$campo = $row_cancelamentos['retorno'];
							$relatorio_evento_array[$row_registro['praca']][16] = $campo;
							mysql_free_result($cancelamentos);
							
						}
						// fim - 11: cancelamentos ***
						
						// 12: clientes_manutencao_nao_atendidos
						if($row_relatorio_campos_tbody['campo']=='clientes_manutencao_nao_atendidos'){
							mysql_select_db($database_conexao, $conexao);
							$query_clientes_manutencao_nao_atendidos = "
							SELECT 
								(
								CASE
									WHEN da37.visita17 = 1 OR da37.visita17 = 5 THEN 0
									WHEN da37.visita17 = 2 THEN 'Sem Limite'
									WHEN 
										da37.visita17 = 3 OR da37.visita17 = 4 THEN 
										(
											(
												SELECT 
													Count(geral_credito.idcredito)
												FROM
													geral_credito
												WHERE
													geral_credito.contrato = da37.codigo17 AND 
													status = 1 AND 
													geral_credito.data_utilizacao IS NULL
											) 
											- 
											(
												SELECT
													Count(geral_credito.idcredito)
												FROM
													geral_credito
												WHERE
													geral_credito.contrato = da37.codigo17 AND 
													status = 1 AND 
													geral_credito.adiantamento = 's'
											)
										) 
									ELSE 0 
									end
								) AS creditos
							FROM
								da37
							LEFT JOIN 
								da01 ON da37.cliente17 = da01.codigo1
							LEFT JOIN 
								geral_tipo_praca_executor ON da37.vendedor17 = geral_tipo_praca_executor.idexecutor
							LEFT JOIN 
								suporte_ultima_visita_view ON da37.codigo17 = suporte_ultima_visita_view.contrato AND suporte_ultima_visita_view.situacao <> 'cancelada'
							WHERE
								geral_tipo_praca_executor.praca = '".$row_registro['praca']."' AND 
								(
									(suporte_ultima_visita_view.data_inicio < '$colname_suporte_filtro_geral_data_criacao' OR 
									suporte_ultima_visita_view.data_inicio > '$colname_suporte_filtro_geral_data_criacao_fim'
									) OR data_suporte IS NULL
								) AND 
								da37.status17 <> 'C' AND 
								da37.status17 <> 'B' AND 
								da01.flag1 = 0 AND 
								da37.vendedor17 <> ' ' AND 
								da37.sr_deleted <> 'T' AND 
								da01.sr_deleted <> 'T'
							HAVING 
								( creditos > 0 OR creditos = 'Sem Limite' )
							";
							$clientes_manutencao_nao_atendidos = mysql_query($query_clientes_manutencao_nao_atendidos, $conexao) or die(mysql_error());
							$row_clientes_manutencao_nao_atendidos = mysql_fetch_assoc($clientes_manutencao_nao_atendidos);
							$totalRows_clientes_manutencao_nao_atendidos = mysql_num_rows($clientes_manutencao_nao_atendidos);
							$campo = $totalRows_clientes_manutencao_nao_atendidos;
							$relatorio_evento_array[$row_registro['praca']][12] = $campo;
							mysql_free_result($clientes_manutencao_nao_atendidos);
						
						}
						// fim - 12: clientes_manutencao_nao_atendidos					

						// 13: prospeccao_atraso_contato
						if($row_relatorio_campos_tbody['campo']=='prospeccao_atraso_contato'){
							mysql_select_db($database_conexao, $conexao);
							$query_prospeccao_atraso_contato = "
							SELECT COUNT(prospeccao.id) as retorno 
							FROM prospeccao 
							LEFT JOIN agenda ON prospeccao.id = agenda.id_prospeccao and agenda.status = 'a' 
							WHERE 
							prospeccao.praca = '".$row_registro['praca']."' and 
							prospeccao.situacao <> 'venda realizada' and prospeccao.situacao <> 'venda perdida' and prospeccao.situacao <> 'cancelada' and 
							(agenda.data < now() or prospeccao.solicita_agendamento = 's' or prospeccao.status = 'aguardando agendamento') and 
							(prospeccao.data_prospeccao < '$colname_suporte_filtro_geral_data_criacao' or prospeccao.data_prospeccao > '$colname_suporte_filtro_geral_data_criacao_fim')
							"; 
							$prospeccao_atraso_contato = mysql_query($query_prospeccao_atraso_contato, $conexao) or die(mysql_error());
							$row_prospeccao_atraso_contato = mysql_fetch_assoc($prospeccao_atraso_contato);
							$totalRows_prospeccao_atraso_contato = mysql_num_rows($prospeccao_atraso_contato);
							$campo = $row_prospeccao_atraso_contato['retorno'];
							$relatorio_evento_array[$row_registro['praca']][13] = $campo;
							mysql_free_result($prospeccao_atraso_contato);
						
						}
						// fim - 13: prospeccao_atraso_contato

						// 14: venda_site_perdidas
						if($row_relatorio_campos_tbody['campo']=='venda_site_perdidas'){
							mysql_select_db($database_conexao, $conexao);
							$query_venda_site_perdidas = "
							SELECT COUNT(prospeccao.id) as retorno 
							FROM prospeccao 
							WHERE 
							prospeccao.praca = '".$row_registro['praca']."' and 
							prospeccao.baixa_tipo = 'p' and 
							(prospeccao.data_prospeccao_fim between '$colname_suporte_filtro_geral_data_criacao' and '$colname_suporte_filtro_geral_data_criacao_fim')
							";
							$venda_site_perdidas = mysql_query($query_venda_site_perdidas, $conexao) or die(mysql_error());
							$row_venda_site_perdidas = mysql_fetch_assoc($venda_site_perdidas);
							$totalRows_venda_site_perdidas = mysql_num_rows($venda_site_perdidas);
							$campo = $row_venda_site_perdidas['retorno'];
							$relatorio_evento_array[$row_registro['praca']][14] = $campo;
							mysql_free_result($venda_site_perdidas);
						
						}
						// fim - 14: venda_site_perdidas

						// 15: implantacao_site_expirada
						if($row_relatorio_campos_tbody['campo']=='implantacao_site_expirada'){	
							mysql_select_db($database_conexao, $conexao);
							$query_implantacao_site_expirada = "
							SELECT COUNT(venda.id) as retorno 
							FROM venda 
							WHERE 
							venda.praca = '".$row_registro['praca']."' and 
							venda.situacao = 'solucionada' and venda.encerramento_automatico = 1 and 
							(venda.encerramento_automatico_data between '$colname_suporte_filtro_geral_data_criacao' and '$colname_suporte_filtro_geral_data_criacao_fim')
							";
							$implantacao_site_expirada = mysql_query($query_implantacao_site_expirada, $conexao) or die(mysql_error());
							$row_implantacao_site_expirada = mysql_fetch_assoc($implantacao_site_expirada);
							$totalRows_implantacao_site_expirada = mysql_num_rows($implantacao_site_expirada);
							$campo = $row_implantacao_site_expirada['retorno'];
							$relatorio_evento_array[$row_registro['praca']][15] = $campo;
							mysql_free_result($implantacao_site_expirada);
						
						}
						// fim - 15: implantacao_site_expirada
						
						// 16: lista_negra
						if($row_relatorio_campos_tbody['campo']=='lista_negra'){
							
							mysql_select_db($database_conexao, $conexao);
							$query_lista_negra = "
							SELECT COUNT(da37.sql_rowid) as retorno
							FROM da37 
							LEFT JOIN da01 ON da37.cliente17 = da01.codigo1 
							LEFT JOIN geral_tipo_praca_executor ON da37.vendedor17 = geral_tipo_praca_executor.IdExecutor 
							WHERE geral_tipo_praca_executor.praca = '".$row_registro['praca']."' and da01.atraso1 >= 90 and da01.atraso1 <= ".$row_parametros['relatorio_inadimplencia_limite_atraso']."  and da37.sr_deleted <> 'T' 
							";
							$lista_negra = mysql_query($query_lista_negra, $conexao) or die(mysql_error());
							$row_lista_negra = mysql_fetch_assoc($lista_negra);
							$totalRows_lista_negra = mysql_num_rows($lista_negra);
							$campo = $row_lista_negra['retorno'];
							$relatorio_evento_array[$row_registro['praca']][16] = $campo;
							mysql_free_result($lista_negra);

						}
						// fim - 16: lista_negra
						
						// 17: suporte_encerrado_atraso
						if($row_relatorio_campos_tbody['campo']=='suporte_encerrado_atraso'){
							mysql_select_db($database_conexao, $conexao);
							$query_suporte_encerrado_atraso = "
							SELECT COUNT(suporte.id) as retorno 
							FROM suporte 
							WHERE 
							suporte.praca = '".$row_registro['praca']."' and 
							suporte.situacao = 'solucionada' and suporte.encerramento_automatico = 1 and 
							(suporte.encerramento_automatico_data between '$colname_suporte_filtro_geral_data_criacao' and '$colname_suporte_filtro_geral_data_criacao_fim')
							";
							$suporte_encerrado_atraso = mysql_query($query_suporte_encerrado_atraso, $conexao) or die(mysql_error());
							$row_suporte_encerrado_atraso = mysql_fetch_assoc($suporte_encerrado_atraso);
							$totalRows_suporte_encerrado_atraso = mysql_num_rows($suporte_encerrado_atraso);
							$campo = $row_suporte_encerrado_atraso['retorno'];
							$relatorio_evento_array[$row_registro['praca']][17] = $campo;
							mysql_free_result($suporte_encerrado_atraso);
						
						}
						// fim - 17: suporte_encerrado_atraso
						
						// 18: suportes_site_atraso
						if($row_relatorio_campos_tbody['campo']=='suportes_site_atraso'){
							mysql_select_db($database_conexao, $conexao);
							$query_suportes_site_atraso = "
							SELECT 
								COUNT(suporte.id) as retorno 
							FROM 
								suporte 
							WHERE 
								suporte.praca = '".$row_registro['praca']."' and 
								suporte.situacao <> 'solucionada' and suporte.situacao <> 'cancelada' and 
								(
									(tipo_suporte = 'c' and inloco = 's'and data_fim IS NOT NULL and data_fim <> '0000-00-00 00:00:00' and data_fim < NOW()) or 
									(tipo_suporte = 'c' and inloco = 'n'and situacao = 'em execução' and data_suporte IS NOT NULL and data_suporte <> '0000-00-00 00:00:00' and data_suporte < NOW()) or 
									(tipo_suporte = 'p' and inloco = 'n'and situacao = 'em execução' and data_suporte IS NOT NULL and data_suporte <> '0000-00-00 00:00:00' and data_suporte < NOW()) or 
							
									(previsao_geral IS NOT NULL and previsao_geral <> '0000-00-00 00:00:00' and previsao_geral < NOW())
								)
							";
							$suportes_site_atraso = mysql_query($query_suportes_site_atraso, $conexao) or die(mysql_error());
							$row_suportes_site_atraso = mysql_fetch_assoc($suportes_site_atraso);
							$totalRows_suportes_site_atraso = mysql_num_rows($suportes_site_atraso);
							$campo = $row_suportes_site_atraso['retorno'];
							$relatorio_evento_array[$row_registro['praca']][18] = $campo;
							mysql_free_result($suportes_site_atraso);
						}
						// fim - 18: suportes_site_atraso
						
						// 19: solicitacoes_validada_auto
						if($row_relatorio_campos_tbody['campo']=='solicitacoes_validada_auto'){
							mysql_select_db($database_conexao, $conexao);
							$query_solicitacoes_validada_auto = "
							SELECT COUNT(solicitacao.id) as retorno 
							FROM solicitacao 
							WHERE 
							solicitacao.praca = '".$row_registro['praca']."' and 
							solicitacao.situacao = 'solucionada' and solicitacao.encerramento_automatico = 1 and 
							(solicitacao.encerramento_automatico_data between '$colname_suporte_filtro_geral_data_criacao' and '$colname_suporte_filtro_geral_data_criacao_fim')
							";
							$solicitacoes_validada_auto = mysql_query($query_solicitacoes_validada_auto, $conexao) or die(mysql_error());
							$row_solicitacoes_validada_auto = mysql_fetch_assoc($solicitacoes_validada_auto);
							$totalRows_solicitacoes_validada_auto = mysql_num_rows($solicitacoes_validada_auto);
							$campo = $row_solicitacoes_validada_auto['retorno'];
							$relatorio_evento_array[$row_registro['praca']][19] = $campo;
							mysql_free_result($solicitacoes_validada_auto);
						
						}
						// fim - 19: solicitacoes_validada_auto
						
						// 20: solicitacoes_atraso
						if($row_relatorio_campos_tbody['campo']=='solicitacoes_atraso'){	
							mysql_select_db($database_conexao, $conexao);
							$query_solicitacoes_atraso = "
							SELECT COUNT(solicitacao.id) as retorno 
							FROM solicitacao 
							WHERE 
							solicitacao.praca = '".$row_registro['praca']."' and 
							solicitacao.situacao <> 'solucionada' and solicitacao.situacao <> 'reprovada' and previsao_geral < NOW() 
							";
							$solicitacoes_atraso = mysql_query($query_solicitacoes_atraso, $conexao) or die(mysql_error());
							$row_solicitacoes_atraso = mysql_fetch_assoc($solicitacoes_atraso);
							$totalRows_solicitacoes_atraso = mysql_num_rows($solicitacoes_atraso);
							$campo = $row_solicitacoes_atraso['retorno'];
							$relatorio_evento_array[$row_registro['praca']][20] = $campo;
							mysql_free_result($solicitacoes_atraso);
						
						}
						// fim - 20: solicitacoes_atraso
						
						// -----------------------------------------------------------------------------------
												
						// 21: credito_manutencao
						if($row_relatorio_campos_tbody['campo']=='credito_manutencao'){

							mysql_select_db($database_conexao, $conexao);
							$query_geral_credito_contador = "
							SELECT 
								COUNT(geral_credito.IdCredito) as retorno 
							FROM 
								geral_credito 
							LEFT JOIN 
								da37 ON da37.codigo17 = geral_credito.contrato 
							LEFT JOIN 
								da01 ON da37.cliente17 = da01.codigo1 
							LEFT JOIN 
								geral_tipo_praca_executor ON da37.vendedor17 = geral_tipo_praca_executor.IdExecutor 
							WHERE 
								geral_credito.status = 1 and 
								(geral_credito.data_criacao between '$colname_suporte_filtro_geral_data_criacao' and '$colname_suporte_filtro_geral_data_criacao_fim') and 
								geral_tipo_praca_executor.praca = '".$row_registro['praca']."' 
							";
							$geral_credito_contador = mysql_query($query_geral_credito_contador, $conexao) or die(mysql_error());
							$row_geral_credito_contador = mysql_fetch_assoc($geral_credito_contador);
							$totalRows_geral_credito_contador = mysql_num_rows($geral_credito_contador);
							$campo = $row_geral_credito_contador['retorno'];
							$relatorio_evento_array[$row_registro['praca']][21] = $campo;
							mysql_free_result($geral_credito_contador);



						}
						// fim - 21: credito_manutencao
						
						// valor_manutencao_praca
						if($row_relatorio_campos_tbody['campo']=='valor_manutencao_praca'){
							mysql_select_db($database_conexao, $conexao);
							$query_valor_manutencao_praca = "
							SELECT SUM(TRUNCATE(((porsal17/100)*788), 0)) AS retorno
							FROM da37 
							LEFT JOIN da01 ON da37.cliente17 = da01.codigo1 
							LEFT JOIN geral_tipo_praca_executor ON da37.vendedor17 = geral_tipo_praca_executor.IdExecutor 
							WHERE geral_tipo_praca_executor.praca = '".$row_registro['praca']."' and (status17 = 'D' or status17 = 'P') and da01.atraso1 < 180 and da37.porsal17 > 0.01 and da37.data17 <= CURDATE()  and da37.sr_deleted <> 'T' 
							";
							$valor_manutencao_praca = mysql_query($query_valor_manutencao_praca, $conexao) or die(mysql_error());
							$row_valor_manutencao_praca = mysql_fetch_assoc($valor_manutencao_praca);
							$totalRows_valor_manutencao_praca = mysql_num_rows($valor_manutencao_praca);
							$campo = "R$ ".number_format($row_valor_manutencao_praca['retorno'], 2, ',', '.');
							mysql_free_result($valor_manutencao_praca);
						}
						// fim - valor_manutencao_praca

						// inadimplencia_site
						if($row_relatorio_campos_tbody['campo']=='inadimplencia_site'){
							mysql_select_db($database_conexao, $conexao);
							$query_inadimplencia_site = "
							SELECT 
								SUM(da01.totatraso1) as retorno 
							FROM 
								da37 
							LEFT JOIN 
								da01 ON da37.cliente17 = da01.codigo1 
							LEFT JOIN 
								geral_tipo_praca_executor ON da37.vendedor17 = geral_tipo_praca_executor.IdExecutor 
							WHERE 
								geral_tipo_praca_executor.praca = '".$row_registro['praca']."' and 
								da01.atraso1 <= 365 and 
								da01.atraso1 >= 30 
							";
							$inadimplencia_site = mysql_query($query_inadimplencia_site, $conexao) or die(mysql_error());
							$row_inadimplencia_site = mysql_fetch_assoc($inadimplencia_site);
							$totalRows_inadimplencia_site = mysql_num_rows($inadimplencia_site);
							$campo = "R$ ".number_format($row_inadimplencia_site['retorno'], 2, ',', '.');
							mysql_free_result($inadimplencia_site);
						}
						// fim - inadimplencia_site

						// tipo_cliente 
						if($row_relatorio_campos_tbody['campo']=='tipo_cliente'){
						if($campo=='a'){ $campo = 'antigo'; }
						if($campo=='n'){ $campo = 'novo'; }
						}
						// fim - tipo_cliente 

						// ativo_passivo 
						if($row_relatorio_campos_tbody['campo']=='ativo_passivo'){
						if($campo=='a'){ $campo = 'ativo'; }
						if($campo=='p'){ $campo = 'passivo'; }
						}
						// fim - ativo_passivo 
						
						
						
						// empresa
						if($row_relatorio_campos_tbody['campo']=='empresa'){
							$campo = limita_caracteres($campo, 40);
						}
						// fim - empresa
						
						// tempo_gasto
						if($row_relatorio_campos_tbody['campo']=='tempo_gasto'){
							
							$campo = tempo_gasto_conversao($row_registro['tempo_gasto']);
							
						}
						// fim - tempo_gasto
					
						// tempo
						if($row_relatorio_campos_tbody['campo']=='tempo'){
							
							$campo = $campo_diferenca_data = NULL;
							
							if($row_registro['data_inicio'] <> "0000-00-00 00:00:00" and $row_registro['data_fim'] <> NULL and $row_registro['data_fim'] <> "0000-00-00 00:00:00"){
								$campo_diferenca_data = (strtotime($row_registro['data_fim']) - strtotime($row_registro['data_inicio']))/60;
								$campo = tempo_gasto_conversao($campo_diferenca_data);
							}
							
						}
						// fim - tempo

						// status
						if($row_relatorio_campos_tbody['campo']=='status'){
							if($campo=="pendente usuario envolvido"){ $campo = 'Pen. Env.'; }
							if($campo=="pendente usuario responsavel"){ $campo = 'Pen. Resp.'; }
							if($campo=="encaminhada para usuario responsavel"){ $campo = 'Enc. Resp.'; }
							if($campo=="devolvida para usuario responsavel"){ $campo = 'Dev. Op.'; }
							if($campo=="pendente controlador de suporte"){ $campo = 'Pen. Op.'; }
						}
						// fim - status
						
						// situacao
						if($row_relatorio_campos_tbody['campo']=='situacao'){
							if($campo=="solicitado suporte"){ $campo = 'sol. suporte'; }
							if($campo=="solicitado visita"){ $campo = 'sol. visita'; }
							if($campo=="encaminhado para solicitação"){ $campo = 'enc. solicitação'; }
						}
						// fim - situacao
	
					}
					// fim - administrativo 
	
	
					// usuario_responsavel
					if($row_relatorio_campos_tbody['campo']=='usuario_responsavel'){
						$campo = limita_caracteres($campo, 10);
					}
					// fim - usuario_responsavel
					
					// usuario_envolvido
					if($row_relatorio_campos_tbody['campo']=='usuario_envolvido'){
						$campo = limita_caracteres($campo, 10);
					}
					// fim - usuario_envolvido
	
					// nome_executante
					if($row_relatorio_campos_tbody['campo']=='nome_executante'){
						$campo = limita_caracteres($campo, 10);
					}
					// fim - nome_executante
											
					// nome_testador
					if($row_relatorio_campos_tbody['campo']=='nome_testador'){
						$campo = limita_caracteres($campo, 10);
					}
					// fim - nome_testador
					
					// fim - Exceções -------------------------------------------------------------------------------------------------------------------------
	
					$table .= $campo;
					
				}
				$table .=  "</td>";
	
			} while ($row_relatorio_campos_tbody = mysql_fetch_assoc($relatorio_campos_tbody));
			// fim $campo
			
			mysql_free_result($relatorio_campos_tbody);
			// fim - $relatorio_campos_tbody
			
			$table .=  "</tr>";
			
			// praca (rodapé)
			if(isset($_GET['filtro_geral_praca']) and $_GET['filtro_geral_praca'] == NULL and $acesso == 1 and $where_praca <> NULL){
										
				if($total_por_praca == $contador_teste){
					
					$totalizar_praca_rodape .= "
					<tr>
						<td style='padding: 5px 0px 0px 0px; text-align: left;' colspan='".$totalRows_relatorio_campos_tbody."'>
						<strong>".ucwords(strtolower(htmlentities($row_registro['praca']))).": </strong>
						Qtde: ".$total_por_praca.
						
						$totalizar_suporte_praca_geral_tempo_retorno.
						$totalizar_suporte_praca_geral_valor_retorno.
						$totalizar_suporte_praca_geral_optante_acumulo_retorno.
						
						$totalizar_venda_praca_geral_valor_venda_retorno.
						$totalizar_venda_praca_geral_valor_treinamento_retorno.
						
						$totalizar_solicitacao_praca_geral_tempo_retorno.
						$totalizar_solicitacao_praca_geral_tempo_testador_geral_retorno.
						$totalizar_solicitacao_praca_geral_orcamento_retorno.
						
						$totalizar_prospeccao_praca_geral_tipo_cliente_retorno.
						$totalizar_prospeccao_praca_geral_ativo_passivo_retorno.
						
						$totalizar_venda_praca_geral_tempo_retorno.
						$totalizar_venda_praca_geral_tempo_gasto_retorno.
	
						$totalizar_venda_praca_geral_dilacao_prazo_retorno.
						$totalizar_venda_praca_geral_tipo_cliente_retorno.
						
						
						$totalizar_suporte_praca_geral_situacao_retorno.
						$totalizar_suporte_praca_geral_avaliacao_atendimento_retorno.
						$totalizar_suporte_praca_geral_tipo_atendimento_retorno.
						$totalizar_suporte_praca_geral_tipo_recomendacao_retorno.
						$totalizar_suporte_praca_geral_tipo_parecer_retorno.
						$totalizar_suporte_praca_geral_usuario_responsavel_retorno.
						$totalizar_suporte_praca_geral_tempo_usuario_responsavel_retorno.
						$totalizar_suporte_praca_geral_usuario_envolvido_retorno.
						$totalizar_suporte_praca_geral_tempo_usuario_envolvido_retorno.
						$totalizar_suporte_praca_geral_tipo_visita_retorno.
						$totalizar_suporte_praca_geral_valor_usuario_responsavel_retorno. 
						$totalizar_suporte_praca_geral_avaliacao_atendimento_responsavel_retorno.
						$totalizar_suporte_praca_geral_tipo_atendimento_envolvido_retorno.
	
						$totalizar_solicitacao_praca_geral_situacao_retorno.
						$totalizar_solicitacao_praca_geral_status_retorno.
						$totalizar_solicitacao_praca_geral_tipo_retorno.
						$totalizar_solicitacao_praca_geral_envolvido_retorno.
						$totalizar_solicitacao_praca_geral_tipo_envolvido_retorno.
						$totalizar_solicitacao_praca_geral_situacao_envolvido_retorno.
						$totalizar_solicitacao_praca_geral_tempo_envolvido_retorno.
						$totalizar_solicitacao_praca_geral_usuario_responsavel_retorno.
						$totalizar_solicitacao_praca_geral_orcamento_usuario_responsavel_retorno.
						$totalizar_solicitacao_praca_geral_operador_retorno.
						$totalizar_solicitacao_praca_geral_executante_retorno.
						$totalizar_solicitacao_praca_geral_testador_retorno.
						$totalizar_solicitacao_praca_geral_tempo_usuario_responsavel_retorno.
						$totalizar_solicitacao_praca_geral_tempo_operador_retorno.
						$totalizar_solicitacao_praca_geral_tempo_executante_retorno.
						$totalizar_solicitacao_praca_geral_tempo_testador_retorno.
						$totalizar_solicitacao_praca_geral_tipo_responsavel_retorno.
						
						$totalizar_prospeccao_praca_geral_situacao_retorno.
						$totalizar_prospeccao_praca_geral_usuario_responsavel_retorno.
						$totalizar_prospeccao_praca_geral_status_retorno.
						$totalizar_prospeccao_praca_geral_baixa_perda_motivo_retorno.
						
						$totalizar_venda_praca_geral_situacao_retorno.
						$totalizar_venda_praca_geral_usuario_responsavel_retorno.
						$totalizar_venda_praca_geral_modulos_retorno.
						$totalizar_venda_praca_geral_valor_venda_usuario_responsavel_retorno.
						$totalizar_venda_praca_geral_valor_treinamento_responsavel_retorno.
						$totalizar_venda_praca_geral_tempo_usuario_responsavel_retorno.
						$totalizar_venda_praca_geral_tempo_gasto_usuario_responsavel_retorno.
						"
						</td>
					</tr>";
										
					$table .= "
					<tr>
						<td style='padding: 5px 5px 5px 5px; font-size: 12px; background-color: #CCC;' colspan='".$totalRows_relatorio_campos_tbody."'>
						Total de Lançamentos da praça: ".$total_por_praca.
						
						$totalizar_suporte_praca_tempo_retorno.
						$totalizar_suporte_praca_valor_retorno.
						$totalizar_suporte_praca_optante_acumulo_retorno.
						
						$totalizar_venda_praca_valor_venda_retorno.
						$totalizar_venda_praca_valor_treinamento_retorno.
						
						$totalizar_solicitacao_praca_tempo_retorno.
						$totalizar_solicitacao_praca_tempo_testador_geral_retorno.
						$totalizar_solicitacao_praca_orcamento_retorno.
						
						$totalizar_prospeccao_praca_tipo_cliente_retorno.
						$totalizar_prospeccao_praca_ativo_passivo_retorno.
						
						$totalizar_venda_praca_tempo_retorno.
						$totalizar_venda_praca_tempo_gasto_retorno.
						$totalizar_venda_praca_dilacao_prazo_retorno.
						$totalizar_venda_praca_tipo_cliente_retorno.
						
						$totalizar_suporte_praca_situacao_retorno.
						$totalizar_suporte_praca_avaliacao_atendimento_retorno.
						$totalizar_suporte_praca_tipo_atendimento_retorno.
						$totalizar_suporte_praca_tipo_recomendacao_retorno.
						$totalizar_suporte_praca_tipo_parecer_retorno.
						$totalizar_suporte_praca_usuario_responsavel_retorno.
						$totalizar_suporte_praca_tempo_usuario_responsavel_retorno.
						$totalizar_suporte_praca_usuario_envolvido_retorno.
						$totalizar_suporte_praca_tempo_usuario_envolvido_retorno.
						$totalizar_suporte_praca_tipo_visita_retorno.
						$totalizar_suporte_praca_valor_usuario_responsavel_retorno. 
						$totalizar_suporte_praca_avaliacao_atendimento_responsavel_retorno.
						$totalizar_suporte_praca_tipo_atendimento_envolvido_retorno.
						
						$totalizar_solicitacao_praca_situacao_retorno.
						$totalizar_solicitacao_praca_status_retorno.
						$totalizar_solicitacao_praca_tipo_retorno.
						$totalizar_solicitacao_praca_envolvido_retorno.
						$totalizar_solicitacao_praca_tipo_envolvido_retorno.
						$totalizar_solicitacao_praca_situacao_envolvido_retorno.
						$totalizar_solicitacao_praca_tempo_envolvido_retorno.
						$totalizar_solicitacao_praca_usuario_responsavel_retorno.
						$totalizar_solicitacao_praca_orcamento_usuario_responsavel_retorno.
						$totalizar_solicitacao_praca_operador_retorno.
						$totalizar_solicitacao_praca_executante_retorno.
						$totalizar_solicitacao_praca_testador_retorno.
						$totalizar_solicitacao_praca_tempo_usuario_responsavel_retorno.
						$totalizar_solicitacao_praca_tempo_operador_retorno.
						$totalizar_solicitacao_praca_tempo_executante_retorno.
						$totalizar_solicitacao_praca_tempo_testador_retorno.
						$totalizar_solicitacao_praca_tipo_responsavel_retorno.
						
						$totalizar_prospeccao_praca_situacao_retorno.
						$totalizar_prospeccao_praca_usuario_responsavel_retorno.
						$totalizar_prospeccao_praca_status_retorno.
						$totalizar_prospeccao_praca_baixa_perda_motivo_retorno.
						
						$totalizar_venda_praca_situacao_retorno.
						$totalizar_venda_praca_usuario_responsavel_retorno.
						$totalizar_venda_praca_modulos_retorno.
						$totalizar_venda_praca_valor_venda_usuario_responsavel_retorno.
						$totalizar_venda_praca_valor_treinamento_responsavel_retorno.
						$totalizar_venda_praca_tempo_usuario_responsavel_retorno.
						$totalizar_venda_praca_tempo_gasto_usuario_responsavel_retorno.
						"
						</td>
					</tr>				
	
					<tr>
						<td style='padding: 5px; background-color: #FFF;' colspan='".$totalRows_relatorio_campos_tbody."'>
						</td>
					</tr>
					";
				}
				
			}
			// fim - praca (rodapé)
	
		} while ($row_registro = mysql_fetch_assoc($registro));
		
		$table .=  "</tbody>";
		// fim - tbody -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
		
		$table .= "</table>";
		// fim - table ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
       
    } else {
    
		// table ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
		$table = NULL;
		$table .= "<table class='registro_tabela' id='registro_tabela".$row_relatorio['id']."' cellspacing='0' cellpadding='0'>";

			// tbody -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
			$table .=  "<tbody>"; 
			$table .=  "<tr><td><div style='border-top: 1px solid #000; margin-bottom: 5px;'></div>Nenhum registro encontrado.</td></tr>";
			$table .=  "</tbody>";
			// fim - tbody -----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
        
		$table .= "</table>";
		// fim - table ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
		    
    }
	
	// footer --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
	$footer .= "
	<div style='height: 2px; background-color: black; margin-top: 0px; margin-bottom: 0px;'></div>
	<table class='footer' border='0' cellspacing='0' cellpadding='0' style='border-spacing: 0;'>	
	<tr>
		<td valign='top' align='left'>
		
		Total de Lançamentos: $totalRows_registro

		$totalizar_suporte_tempo_retorno
		$totalizar_suporte_valor_retorno
		$totalizar_suporte_optante_acumulo_retorno
		
		$totalizar_venda_valor_venda_retorno
		$totalizar_venda_valor_treinamento_retorno

		$totalizar_solicitacao_tempo_retorno
		$totalizar_solicitacao_tempo_testador_geral_retorno 
		$totalizar_solicitacao_orcamento_retorno
		
		$totalizar_prospeccao_tipo_cliente_retorno 
		$totalizar_prospeccao_ativo_passivo_retorno 
		
		$totalizar_venda_tempo_retorno
		$totalizar_venda_tempo_gasto_retorno
		$totalizar_venda_dilacao_prazo_retorno
		$totalizar_venda_tipo_cliente_retorno
		
		$totalizar_suporte_situacao_retorno
		$totalizar_suporte_avaliacao_atendimento_retorno
		$totalizar_suporte_tipo_atendimento_retorno
		$totalizar_suporte_tipo_recomendacao_retorno
		$totalizar_suporte_tipo_parecer_retorno
		$totalizar_suporte_usuario_responsavel_retorno
		$totalizar_suporte_tempo_usuario_responsavel_retorno
		$totalizar_suporte_usuario_envolvido_retorno
		$totalizar_suporte_tempo_usuario_envolvido_retorno
		$totalizar_suporte_tipo_visita_retorno
		$totalizar_suporte_valor_usuario_responsavel_retorno 
		$totalizar_suporte_avaliacao_atendimento_responsavel_retorno
		$totalizar_suporte_tipo_atendimento_envolvido_retorno
		
		$totalizar_solicitacao_situacao_retorno
		$totalizar_solicitacao_status_retorno
		$totalizar_solicitacao_tipo_retorno
		$totalizar_solicitacao_envolvido_retorno
		$totalizar_solicitacao_tipo_envolvido_retorno
		$totalizar_solicitacao_situacao_envolvido_retorno
		$totalizar_solicitacao_tempo_envolvido_retorno
		$totalizar_solicitacao_usuario_responsavel_retorno
		$totalizar_solicitacao_orcamento_usuario_responsavel_retorno
		$totalizar_solicitacao_operador_retorno
		$totalizar_solicitacao_executante_retorno
		$totalizar_solicitacao_testador_retorno 
		$totalizar_solicitacao_tempo_usuario_responsavel_retorno 
		$totalizar_solicitacao_tempo_operador_retorno
		$totalizar_solicitacao_tempo_executante_retorno 
		$totalizar_solicitacao_tempo_testador_retorno 
		$totalizar_solicitacao_tipo_responsavel_retorno
		
		$totalizar_prospeccao_situacao_retorno 
		$totalizar_prospeccao_usuario_responsavel_retorno
		$totalizar_prospeccao_status_retorno 
		$totalizar_prospeccao_baixa_perda_motivo_retorno
		
		$totalizar_venda_situacao_retorno
		$totalizar_venda_usuario_responsavel_retorno
		$totalizar_venda_modulos_retorno
		$totalizar_venda_valor_venda_usuario_responsavel_retorno
		$totalizar_venda_valor_treinamento_responsavel_retorno
		$totalizar_venda_tempo_usuario_responsavel_retorno
		$totalizar_venda_tempo_gasto_usuario_responsavel_retorno
		
		</td>
	</tr>
	$totalizar_praca_rodape
	</table>
	";
	// fim - footer --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
	
	// retorno
	if($tela=="digital"){

		echo "<div class='registro' id='registro".$row_relatorio['id']."'>";
		if($relatorio_id_grupo == NULL){ // geral		
			echo $titulo_geral.$header_geral.$titulo_grupo_geral.$titulo;
		} else {
			echo $titulo.$header;
		}
		echo $table;
		echo @$footer;
    	echo "</div>";
    	echo "<div class='page-break'></div>";
		
		$titulo_grupo_geral = NULL;

	} else if($tela=="impressao"){
		
		// individual
		if($totalRows_relatorio == 1 and $relatorio_id > 0){ 
			
			$mpdf->WriteHTML($stylesheet,1);
			$mpdf->SetHTMLHeader($titulo.$header);
			$mpdf->AddPage('P','','','','',15,15,24+($contador_filtro*4),15,5,7);
			$mpdf->WriteHTML($table.$footer);
			
		} 
		// fim - individual
		
		// geral por módulo
		else if($totalRows_relatorio > 1 and $relatorio_id_grupo > 0){
		
			$geral .= $titulo.$header.$table.$footer."<br>";
			if($contador_relatorio == $totalRows_relatorio){
				$mpdf->WriteHTML($stylesheet,1);
				$mpdf->SetHTMLHeader($titulo_geral);
				$mpdf->AddPage('P','','','','',15,15,16,15,5,7);
				$mpdf->WriteHTML($geral);
			}
			
		} 
		// fim - geral por módulo

		// geral
		else if($totalRows_relatorio > 1 and $relatorio_id_grupo == NULL){
			
			// inicio
			$contador_array = 0;
			foreach($relatorio_grupo_geral_array as $item){
				if($row_relatorio['id_grupo_geral'] == $item['id']){
					$relatorio_grupo_geral_array[$contador_array]['geral'] .= $titulo_grupo_geral.$titulo.$header.$table.$footer."<br>";
					$titulo_grupo_geral = NULL;
				}
				
				$contador_array ++;
			}
			// fim - inicio
			
			// fórmula
			if($contador_relatorio == $totalRows_relatorio){
				foreach($relatorio_grupo_geral_array as $item){
					$mpdf->WriteHTML($stylesheet,1);
					$mpdf->SetHTMLHeader($titulo_geral.$header_geral);
					$mpdf->AddPage('P','','','','',15,15,30,15,5,7);
					$mpdf->WriteHTML($item['geral']);
				}
			}
			// fim - fórmula

		}
		// fim - geral
		
		$titulo_grupo_geral = NULL;
		
	}
	// fim - retorno
		
    mysql_free_result($relatorio_campos);
	mysql_free_result($registro);
    // fim - registro ---------------------------------------------------------------------------------------------------------------------------------------------------------------
	
	$contador_filtro = NULL;
     
} while ($row_relatorio = mysql_fetch_assoc($relatorio));
		
// retorno (impressao)
if($tela=="impressao"){
	
	// relatorio_fechamento ----------------------
	if($fechamento == "sim"){
		
		// verifica se já existe relatório -----------------------------
		// relatorio_fechamento_confirma
		mysql_select_db($database_conexao, $conexao);
		$query_relatorio_fechamento_confirma = sprintf("
		SELECT * 
		FROM relatorio_fechamento 
		WHERE relatorio_fechamento.praca = %s and YEAR(data_criacao) = YEAR(now()) and MONTH(data_criacao) =  MONTH(now()) ORDER BY id DESC", 
		GetSQLValueString($praca_atual, "text"));
		$relatorio_fechamento_confirma = mysql_query($query_relatorio_fechamento_confirma, $conexao) or die(mysql_error());
		$row_relatorio_fechamento_confirma = mysql_fetch_assoc($relatorio_fechamento_confirma);
		$totalRows_relatorio_fechamento_confirma = mysql_num_rows($relatorio_fechamento_confirma);
		// fim - relatorio_fechamento_confirma
		
		if($totalRows_relatorio_fechamento_confirma > 0){
			
			do {
				
				unlink('relatorio/'.$row_relatorio_fechamento_confirma['arquivo']);

				// delete
				$delete_SQL_relatorio_fechamento = sprintf("
				DELETE FROM relatorio_fechamento 
				WHERE id=%s
				", 
				GetSQLValueString($row_relatorio_fechamento_confirma['id'], "int"));
				mysql_select_db($database_conexao, $conexao);
				$Result_relatorio_fechamento = mysql_query($delete_SQL_relatorio_fechamento, $conexao) or die(mysql_error());
				// fim - delete
				
			} while ($row_relatorio_fechamento_confirma = mysql_fetch_assoc($relatorio_fechamento_confirma));
			
		}
		
		mysql_free_result($relatorio_fechamento_confirma);
		// fim - verifica se já existe relatório -----------------------
		
		$arquivo = 'Relatorio-Gerencial-'.$praca_atual.'-'.$mes_ano_atual.'.pdf';
					
		// insert (relatorio_fechamento)
		mysql_select_db($database_conexao, $conexao);
		$insert_SQL_relatorio_fechamento = sprintf("
		INSERT INTO relatorio_fechamento (data_criacao, data, id_usuario, praca, arquivo, contador) 
		VALUES (%s, %s, %s, %s, %s, %s)
		",
		GetSQLValueString(date('Y-m-d H:i:s'), "date"),
		GetSQLValueString(date('Y-m-01', strtotime('-1 months', strtotime(date('Y-m-d')))), "date"),
		GetSQLValueString($row_usuario['IdUsuario'], "int"),
		GetSQLValueString($praca_atual, "text"),
		GetSQLValueString($arquivo, "text"),
		GetSQLValueString($row_relatorio_fechamento_contador['contador_relatorio_fechamento'], "int"));
		
		mysql_select_db($database_conexao, $conexao);
		$Result_insert_relatorio_fechamento = mysql_query($insert_SQL_relatorio_fechamento, $conexao) or die(mysql_error());
		// fim - insert (relatorio_fechamento)
		
		$mpdf->Output('relatorio/'.$arquivo,'F');

		header("Location: relatorio_fechamento.php?exibir=sim");
		exit;

	} else 
	// fim - relatorio_fechamento ----------------
	
	{
		//echo $titulo.$header;
		$mpdf->Output('Relatorio.pdf','I');
		
	}

}
// fim - retorno (impressao)

} 
// fim - relatorio ------------------------------------------------------------
?>
</body>
</html>
<?php
mysql_free_result($usuario);
mysql_free_result($relatorio);
mysql_free_result($relatorio_javascript);

mysql_free_result($filtro_relatorio_grupo);
mysql_free_result($filtro_relatorio_grupo_subgrupo);

mysql_free_result($filtro_geral_praca);
mysql_free_result($filtro_geral_cliente);
mysql_free_result($filtro_geral_usuario);

mysql_free_result($filtro_suporte_solicitante);
mysql_free_result($filtro_suporte_atendente);
mysql_free_result($filtro_suporte_tipo_atendimento);
mysql_free_result($filtro_suporte_tipo_recomendacao);

mysql_free_result($filtro_solicitacao_solicitante);
mysql_free_result($filtro_solicitacao_executante);
mysql_free_result($filtro_solicitacao_operador);
mysql_free_result($filtro_solicitacao_testador);
mysql_free_result($filtro_solicitacao_tipo);

mysql_free_result($filtro_prospeccao_usuario_responsavel);

mysql_free_result($filtro_venda_usuario_responsavel);

if($relatorio_id_grupo == NULL){
	if($fechamento == 'sim'){
		mysql_free_result($relatorio_fechamento_contador);
	}
}

if($relatorio_id_grupo == NULL and $tela == "digital"){ // geral e digital
	mysql_free_result($relatorio_fechamento);
}
?>