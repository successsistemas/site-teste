<?php require_once('../padrao_restrito.php'); ?>
<?php require_once('../../Connections/conexao.php'); ?>
<?php
if (!function_exists("GetSQLValueString")) {
function GetSQLValueString($theValue, $theType, $theDefinedValue = "", $theNotDefinedValue = "") 
{
  $theValue = get_magic_quotes_gpc() ? stripslashes($theValue) : $theValue;

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
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

require_once('../funcao.php');

// usuario
$colname_usuario = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_usuario = $_SESSION['MM_Username'];
}
mysql_select_db($database_conexao, $conexao);
$query_usuario = sprintf("SELECT * FROM usuarios WHERE usuario = %s", GetSQLValueString($colname_usuario, "text"));
$usuario = mysql_query($query_usuario, $conexao) or die(mysql_error());
$row_usuario = mysql_fetch_assoc($usuario);
$totalRows_usuario = mysql_num_rows($usuario);
// fim - usuario

// Load the common classes
require_once('../../includes/common/KT_common.php');

// Load the tNG classes
require_once('../../includes/tng/tNG.inc.php');

// Load the KT_back class
require_once('../../includes/nxt/KT_back.php');

// Make a transaction dispatcher instance
$tNGs = new tNG_dispatcher("../../");

// rsmala_direta
$colname_rsmala_direta = "-1";
if (isset($_GET['IdMalaDireta'])) {
  $colname_rsmala_direta = $_GET['IdMalaDireta'];
}
mysql_select_db($database_conexao, $conexao);
$query_rsmala_direta = sprintf("
SELECT 
mala_direta.*, 
usuarios.nome AS usuario_nome
FROM mala_direta 
LEFT JOIN usuarios ON usuarios.IdUsuario = mala_direta.IdUsuario
WHERE mala_direta.IdMalaDireta = %s", 
GetSQLValueString($colname_rsmala_direta, "text"));
$rsmala_direta = mysql_query($query_rsmala_direta, $conexao) or die(mysql_error());
$row_rsmala_direta = mysql_fetch_assoc($rsmala_direta);
$totalRows_rsmala_direta = mysql_num_rows($rsmala_direta);
// fim - rsmala_direta

// rsmala_direta_anexo
mysql_select_db($database_conexao, $conexao);
$query_rsmala_direta_anexo = sprintf("
SELECT 
mala_direta_anexo.*
FROM mala_direta_anexo 
WHERE mala_direta_anexo.IdMalaDireta = %s", 
GetSQLValueString($row_rsmala_direta['IdMalaDireta'], "text"));
$rsmala_direta_anexo = mysql_query($query_rsmala_direta_anexo, $conexao) or die(mysql_error());
$row_rsmala_direta_anexo = mysql_fetch_assoc($rsmala_direta_anexo);
$totalRows_rsmala_direta_anexo = mysql_num_rows($rsmala_direta_anexo);
// fim - rsmala_direta_anexo

// perfil
$perfil = $row_rsmala_direta['perfil'];
if($perfil == NULL){$perfil = "c";} // cliente
if( 
((isset($_GET["perfil"])) && ($_GET['perfil'] =="p"))
){
	$perfil = "p"; // prospect
} 
// fim - perfil

$where = "1=1";

// prospeccao - filtros ----------------------------------------------------------------------------------------------------------------------------------------------

if($perfil == "p"){
	
// se existe filtro de filtro_cliente
if( (isset($_GET["filtro_cliente"])) && ($_GET['filtro_cliente'] !="") ) {
	$colname_prospeccao_filtro_cliente = GetSQLValueString($_GET["filtro_cliente"], "string");
	$where .= " and prospeccao.nome_razao_social LIKE '%$colname_prospeccao_filtro_cliente%' ";
	$where_campos[] = "filtro_cliente";
}
// fim - se existe filtro de filtro_cliente
	
// se existe filtro de data_prospeccao ( somente data final )
if( ((isset($_GET["filtro_data_prospeccao_fim"])) && ($_GET["filtro_data_prospeccao_fim"] != "")) && ($_GET["filtro_data_prospeccao_inicio"] == "") ) {

		// converter data em portugues para ingles
		if ( isset($_GET["filtro_data_prospeccao_fim"]) ) {
			$data_prospeccao_fim_data = substr($_GET["filtro_data_prospeccao_fim"],0,10);
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
if( ((isset($_GET["filtro_data_prospeccao_inicio"])) && ($_GET["filtro_data_prospeccao_inicio"] != "")) && ($_GET["filtro_data_prospeccao_fim"] == "") ) {

		// converter data em portugues para ingles
		if ( isset($_GET["filtro_data_prospeccao_inicio"]) ) {
			$data_prospeccao_inicio_data = substr($_GET["filtro_data_prospeccao_inicio"],0,10);
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
if( ((isset($_GET["filtro_data_prospeccao_inicio"])) && ($_GET["filtro_data_prospeccao_inicio"] != "")) && ((isset($_GET["filtro_data_prospeccao_fim"])) && ($_GET["filtro_data_prospeccao_fim"] != "")) ) {

		// converter data em portugues para ingles
		if ( isset($_GET["filtro_data_prospeccao_inicio"]) ) {
			$data_prospeccao_inicio_data = substr($_GET["filtro_data_prospeccao_inicio"],0,10);
			$data_prospeccao_inicio_hora = " 00:00:00";
			$data_prospeccao_inicio = implode("-",array_reverse(explode("-",$data_prospeccao_inicio_data))).$data_prospeccao_inicio_hora;
			$where_campos[] = "data_prospeccao_inicio";
		}
		// converter data em portugues para ingles - fim

		// converter data em portugues para ingles
		if ( isset($_GET["filtro_data_prospeccao_fim"]) ) {
			$data_prospeccao_fim_data = substr($_GET["filtro_data_prospeccao_fim"],0,10);
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

// se existe filtro de filtro_status
if( (isset($_GET["filtro_status"])) && ($_GET['filtro_status'] !="") ) {
	$colname_suporte_filtro_status = GetSQLValueString($_GET["filtro_status"], "string");
	$where .= " and prospeccao.status_flag = '$colname_suporte_filtro_status' "; 	
	$where_campos[] = "filtro_status";
} 
// fim - se existe filtro de filtro_status

// se existe filtro de filtro_praca
if( (isset($_GET["filtro_praca"])) && ($_GET['filtro_praca'] !="") ) {
	$colname_suporte_filtro_praca = GetSQLValueString($_GET["filtro_praca"], "string");
	$where .= " and prospeccao.praca = '$colname_suporte_filtro_praca' "; 	
	$where_campos[] = "filtro_praca";
} 
// fim - se existe filtro de filtro_praca

// se existe filtro de filtro_id_usuario_responsavel
if( (isset($_GET["filtro_id_usuario_responsavel"])) && ($_GET['filtro_id_usuario_responsavel'] !="") ) {
	$colname_suporte_filtro_id_usuario_responsavel = GetSQLValueString($_GET["filtro_id_usuario_responsavel"], "string");
	$where .= " and prospeccao.id_usuario_responsavel = '$colname_suporte_filtro_id_usuario_responsavel' "; 	
	$where_campos[] = "filtro_id_usuario_responsavel";
} 
// fim - se existe filtro de filtro_id_usuario_responsavel

// se existe filtro de filtro_cidade
if( (isset($_GET["filtro_cidade"])) && ($_GET['filtro_cidade'] !="") ) {
	$colname_prospeccao_filtro_cidade = GetSQLValueString($_GET["filtro_cidade"], "string");
	$where .= " and prospeccao.cidade LIKE '%$colname_prospeccao_filtro_cidade%' ";
	$where_campos[] = "filtro_cidade";
}
// fim - se existe filtro de filtro_cidade

// se existe filtro de filtro_concorrente
if( (isset($_GET["filtro_concorrente"])) && ($_GET['filtro_concorrente'] !="") ) {
	$colname_prospeccao_filtro_concorrente = GetSQLValueString($_GET["filtro_concorrente"], "int");
	$where .= " and prospeccao_concorrente.id = $colname_prospeccao_filtro_concorrente ";
	$where_campos[] = "filtro_concorrente";
}
// fim - se existe filtro de filtro_concorrente

// se existe filtro de filtro_contador
if( (isset($_GET["filtro_contador"])) && ($_GET['filtro_contador'] !="") ) {
	$colname_prospeccao_filtro_contador = GetSQLValueString($_GET["filtro_contador"], "string");
	$where .= " and prospeccao_contador.razao LIKE '%$colname_prospeccao_filtro_contador%' ";
	$where_campos[] = "filtro_contador";
}
// fim - se existe filtro de filtro_contador

// se existe filtro de filtro_indicacao
if( (isset($_GET["filtro_indicacao"])) && ($_GET['filtro_indicacao'] !="") ) {
	$colname_suporte_filtro_indicacao = GetSQLValueString($_GET["filtro_indicacao"], "string");
	$where .= " and prospeccao.indicacao = '$colname_suporte_filtro_indicacao' "; 	
	$where_campos[] = "filtro_indicacao";
} 
// fim - se existe filtro de filtro_indicacao

// se existe filtro de filtro_sistema_possui
if( (isset($_GET["filtro_sistema_possui"])) && ($_GET['filtro_sistema_possui'] !="") ) {
	$colname_suporte_filtro_sistema_possui = GetSQLValueString($_GET["filtro_sistema_possui"], "string");
	$where .= " and prospeccao.sistema_possui = '$colname_suporte_filtro_sistema_possui' "; 	
	$where_campos[] = "filtro_sistema_possui";
} 
// fim - se existe filtro de filtro_sistema_possui

// se existe filtro de filtro_migracao
if( (isset($_GET["filtro_migracao"])) && ($_GET['filtro_migracao'] !="") ) {
	$colname_suporte_filtro_migracao = GetSQLValueString($_GET["filtro_migracao"], "string");
	$where .= " and prospeccao_concorrente.migracao = '$colname_suporte_filtro_migracao' "; 	
	$where_campos[] = "filtro_migracao";
} 
// fim - se existe filtro de filtro_migracao

// se existe filtro de filtro_nivel_interesse
if( (isset($_GET["filtro_nivel_interesse"])) && ($_GET['filtro_nivel_interesse'] !="") ) {
	$colname_suporte_filtro_nivel_interesse = GetSQLValueString($_GET["filtro_nivel_interesse"], "string");
	$where .= " and prospeccao.nivel_interesse = '$colname_suporte_filtro_nivel_interesse' "; 	
	$where_campos[] = "filtro_nivel_interesse";
} 
// fim - se existe filtro de filtro_nivel_interesse

// se existe filtro de filtro_baixa_perda_motivo
if( (isset($_GET["filtro_baixa_perda_motivo"])) && ($_GET['filtro_baixa_perda_motivo'] !="") ) {
	$colname_suporte_filtro_baixa_perda_motivo = GetSQLValueString($_GET["filtro_baixa_perda_motivo"], "string");
	$where .= " and prospeccao.baixa_perda_motivo = '$colname_suporte_filtro_baixa_perda_motivo' "; 	
	$where_campos[] = "filtro_baixa_perda_motivo";
} 
// fim - se existe filtro de filtro_baixa_perda_motivo

} else {
	
// se existe filtro de filtro_cliente_nome
if( (isset($_GET["filtro_cliente_nome"])) && ($_GET['filtro_cliente_nome'] !="") ) {
	$colname_prospeccao_filtro_cliente_nome = GetSQLValueString($_GET["filtro_cliente_nome"], "string");
	$where .= " and da01.nome1 LIKE '%$colname_prospeccao_filtro_cliente_nome%' ";
	$where_campos[] = "filtro_cliente_nome";
}
// fim - se existe filtro de filtro_cliente_nome

// se existe filtro de filtro_praca
if( (isset($_GET["filtro_praca"])) && ($_GET['filtro_praca'] !="") ) {
	$colname_suporte_filtro_praca = GetSQLValueString($_GET["filtro_praca"], "string");
	$where .= " and geral_tipo_praca_executor.praca = '$colname_suporte_filtro_praca' "; 	
	$where_campos[] = "filtro_praca";
} 
// fim - se existe filtro de filtro_praca

// se existe filtro de filtro_tipo_visita
if( (isset($_GET["filtro_tipo_visita"])) && ($_GET['filtro_tipo_visita'] !="") ) {
	$colname_suporte_filtro_tipo_visita = GetSQLValueString($_GET["filtro_tipo_visita"], "string");
	$where .= " and geral_tipo_visita.IdTipoVisita = '$colname_suporte_filtro_tipo_visita' "; 	
	$where_campos[] = "filtro_tipo_visita";
} 
// fim - se existe filtro de filtro_tipo_visita

// se existe filtro de filtro_optante_acumulo
if( (isset($_GET["filtro_optante_acumulo"])) && ($_GET['filtro_optante_acumulo'] !="") ) {
	$colname_suporte_filtro_optante_acumulo = GetSQLValueString($_GET["filtro_optante_acumulo"], "string");
	$where .= " and da37.optacuv17 = '$colname_suporte_filtro_optante_acumulo' "; 	
	$where_campos[] = "filtro_optante_acumulo";
} 
// fim - se existe filtro de filtro_optante_acumulo

// se existe filtro de filtro_modulo
$contador_filtro_modulo = 0;
$contador_filtro_modulo_atual = 0;
if( (isset($_GET["filtro_modulo"])) && ($_GET['filtro_modulo'] !="") ) {

		// contar quantidade de situacões atual
		foreach($_GET["filtro_modulo"] as $filtro_modulo){
				$contador_filtro_modulo = $contador_filtro_modulo + 1;
		}
		// fim - contar quantidade de situacões atual

		$query_filtro_modulo=" and ( ";
		foreach($_GET["filtro_modulo"] as $filtro_modulo){
				$contador_filtro_modulo_atual = $contador_filtro_modulo_atual + 1; // verifica o contador atual
				$contador_total = $contador_filtro_modulo - $contador_filtro_modulo_atual; // calcula diferença de situações total - situação atual
				if($contador_total<>0){$or=" or ";}else{$or="";} // se não é a última, então insere OR
				$query_filtro_modulo .= sprintf(" prospeccao.filtro_modulo = '$filtro_modulo' $or");

		}
		//$where .= sprintf($query_filtro_modulo)." ) ";
		//$where_campos[] = "filtro_modulo";

}
// fim - se existe filtro de filtro_modulo	

// se existe filtro de filtro_tipo_ramo_atividade
if( (isset($_GET["filtro_tipo_ramo_atividade"])) && ($_GET['filtro_tipo_ramo_atividade'] !="") ) {
	$colname_suporte_filtro_tipo_ramo_atividade = GetSQLValueString($_GET["filtro_tipo_ramo_atividade"], "string");
	$where .= " and geral_tipo_ramo_atividade.codigo = '$colname_suporte_filtro_tipo_ramo_atividade' "; 	
	$where_campos[] = "filtro_tipo_ramo_atividade";
} 
// fim - se existe filtro de filtro_tipo_ramo_atividade
	
}

// fim - prospeccao - filtros ----------------------------------------------------------------------------------------------------------------------------------------

// praca_listar
mysql_select_db($database_conexao, $conexao);
$query_praca_listar = "
SELECT geral_tipo_praca.* 
FROM geral_tipo_praca 
ORDER BY geral_tipo_praca.praca ASC 
";
$praca_listar = mysql_query($query_praca_listar, $conexao) or die(mysql_error());
$row_praca_listar = mysql_fetch_assoc($praca_listar);
$totalRows_praca_listar = mysql_num_rows($praca_listar);
$praca_listar_array = NULL;
if($totalRows_praca_listar > 0){
	do {

		$praca_listar_array[] = array(
		'IdPraca' => $row_praca_listar['IdPraca'], 
		'praca' => $row_praca_listar['praca']
		);
		
	} while ($row_praca_listar = mysql_fetch_assoc($praca_listar));
}
// praca_listar

// destinatario_listar
mysql_select_db($database_conexao, $conexao);
if($perfil == "p"){

	if($totalRows_rsmala_direta == 0){ // insert
	
		$query_destinatario_listar = "
		SELECT prospeccao.id, prospeccao.praca, prospeccao.nome_razao_social, prospeccao.id_usuario_responsavel, prospeccao.cidade, prospeccao.tipo_cliente 
		FROM prospeccao 
		LEFT JOIN prospeccao_concorrente ON prospeccao_concorrente.id = prospeccao.id_concorrente 
		LEFT JOIN prospeccao_contador ON prospeccao_contador.id = prospeccao.id_contador 
		WHERE $where 
		ORDER BY prospeccao.praca ASC, prospeccao.nome_razao_social ASC 
		";
		
	} else { // update
		
		$query_destinatario_listar = sprintf("
		SELECT prospeccao.id, prospeccao.praca, prospeccao.nome_razao_social, prospeccao.id_usuario_responsavel, prospeccao.cidade, prospeccao.tipo_cliente 
		FROM prospeccao 
		INNER JOIN mala_direta_destinatario ON prospeccao.id = mala_direta_destinatario.id_prospeccao 
		WHERE $where and mala_direta_destinatario.IdMalaDireta=%s 
		ORDER BY prospeccao.praca ASC, prospeccao.nome_razao_social ASC 
		", 
		GetSQLValueString($row_rsmala_direta['IdMalaDireta'], "int"));
			
	}
	
} else {

	if($totalRows_rsmala_direta == 0){ // insert
	
		$query_destinatario_listar = "
		SELECT 
		da37.codigo17, da37.cliente17, da37.optacuv17, 
		geral_tipo_visita.IdTipoVisita, geral_tipo_visita.descricao as visita17_descricao,
		geral_tipo_praca_executor.praca as praca, 
		geral_tipo_ramo_atividade.titulo AS geral_tipo_ramo_atividade_titulo, 
		da01.nome1 
		FROM da37 
		INNER JOIN da01 ON da37.cliente17 = da01.codigo1 
		INNER JOIN geral_tipo_contrato ON da37.tpocont17 = geral_tipo_contrato.IdTipoContrato
		INNER JOIN geral_tipo_visita ON da37.visita17 = geral_tipo_visita.IdTipoVisita
		INNER JOIN geral_tipo_praca_executor ON da37.vendedor17 = geral_tipo_praca_executor.IdExecutor
		LEFT JOIN geral_tipo_ramo_atividade ON da37.ramativ17 = geral_tipo_ramo_atividade.codigo 
		WHERE $where and da01.sr_deleted <> 'T' and da37.sr_deleted <> 'T' 
		ORDER BY geral_tipo_praca_executor.praca ASC, da01.nome1 ASC
		";
		
	} else { // update
		
		$query_destinatario_listar = sprintf("
		SELECT 
		da37.codigo17, da37.cliente17, da37.optacuv17, 
		geral_tipo_visita.IdTipoVisita, geral_tipo_visita.descricao as visita17_descricao,
		geral_tipo_praca_executor.praca as praca, 
		geral_tipo_ramo_atividade.titulo AS geral_tipo_ramo_atividade_titulo, 
		da01.nome1 
		FROM da37 
		INNER JOIN da01 ON da37.cliente17 = da01.codigo1 
		INNER JOIN geral_tipo_contrato ON da37.tpocont17 = geral_tipo_contrato.IdTipoContrato
		INNER JOIN geral_tipo_visita ON da37.visita17 = geral_tipo_visita.IdTipoVisita
		INNER JOIN geral_tipo_praca_executor ON da37.vendedor17 = geral_tipo_praca_executor.IdExecutor 
		INNER JOIN mala_direta_destinatario ON da37.codigo17 = mala_direta_destinatario.contrato 
		LEFT JOIN geral_tipo_ramo_atividade ON da37.ramativ17 = geral_tipo_ramo_atividade.codigo 
		WHERE $where and da01.sr_deleted <> 'T' and da37.sr_deleted <> 'T' and mala_direta_destinatario.IdMalaDireta=%s 
		ORDER BY geral_tipo_praca_executor.praca ASC, da01.nome1 ASC
		", 
		GetSQLValueString($row_rsmala_direta['IdMalaDireta'], "int"));
			
	}
	
}
										
$destinatario_listar = mysql_query($query_destinatario_listar, $conexao) or die(mysql_error());
$row_destinatario_listar = mysql_fetch_assoc($destinatario_listar);
$totalRows_destinatario_listar = mysql_num_rows($destinatario_listar);
$destinatario_listar_array = NULL;
if($totalRows_destinatario_listar > 0){
	do {
	
		if($perfil == "p"){
			
			$destinatario_listar_array[] = array(
			'id' => $row_destinatario_listar['id'], 
			'praca' => $row_destinatario_listar['praca'], 
			'nome_razao_social' => $row_destinatario_listar['nome_razao_social'],
			'tipo_cliente' => $row_destinatario_listar['tipo_cliente']
			);
			
		} else {
			
			$destinatario_listar_array[] = array(
			'codigo17' => $row_destinatario_listar['codigo17'], 
			'praca' => $row_destinatario_listar['praca'], 
			'nome1' => $row_destinatario_listar['nome1'], 
			'visita17_descricao' => $row_destinatario_listar['visita17_descricao'], 
			'optacuv17' => $row_destinatario_listar['optacuv17'], 
			'geral_tipo_ramo_atividade_titulo' => $row_destinatario_listar['geral_tipo_ramo_atividade_titulo']
			);
		
		}
		
	} while ($row_destinatario_listar = mysql_fetch_assoc($destinatario_listar));
}
// fim - destinatario_listar

// echo $where;

// funcao para filtrar registros da 'array'
function filter_by_value ($array, $index, $value){ 
	if(is_array($array) && count($array)>0) { 
	
		foreach(array_keys($array) as $key){ 
			$temp[$key] = $array[$key][$index]; 
			 
			if ($temp[$key] == $value){ 
				$newarray[$key] = $array[$key]; 
			} 
		} 
		
	} 
	return $newarray; 
} 
// fim - funcao para filtrar registros da 'array'

// insert --------------------------------------------------------------------------------------------------------------------------
if(
// ($row_usuario['controle_mala_direta'] == 'Y') and 
($totalRows_rsmala_direta == 0) and 
(isset($_POST["MM_insert"]) and $_POST["MM_insert"] == "form")
){
			
	$insert_SQL_mala_direta = sprintf("
	INSERT INTO mala_direta (IdUsuario, status, data_criacao, tipo, titulo, texto, perfil) 
	VALUES (%s, %s, %s, %s, %s, %s, %s)
	",
	GetSQLValueString($row_usuario['IdUsuario'], "int"),
	GetSQLValueString(1, "int"),
	GetSQLValueString(date('Y-m-d H:i:s'), "date"),
	GetSQLValueString($_POST['tipo'], "text"),
	GetSQLValueString($_POST['titulo'], "text"),
	GetSQLValueString($_POST['texto'], "text"),
	GetSQLValueString($perfil, "text"));
	mysql_select_db($database_conexao, $conexao);
	$Result_insert_mala_direta = mysql_query($insert_SQL_mala_direta, $conexao) or die(mysql_error());
	
	$ultimo_id = mysql_insert_id($conexao);
	
	// INSERT - mala_direta_anexo
	$arquivo = NULL;
	$anexo =  NULL;
	if(!empty($_FILES['arquivo']['name'][0])){
		
		$anexo =  $_FILES["arquivo"];
	
		// $funcao_upload_retorno ($arquivo)
		$funcao_upload_retorno = funcao_upload("../../arquivos/mala_direta/", $_FILES['arquivo']);
		foreach ($funcao_upload_retorno as $retorno) {
			if($retorno['upload_retorno']==1){
				$arquivo = $retorno['upload_nome'];
				
				$update_SQL_slider_arquivo = sprintf("
				INSERT INTO mala_direta_anexo 
				SET IdMalaDireta=%s, IdUsuario=%s, data_criacao=%s, arquivo=%s 
				", 
				GetSQLValueString($ultimo_id, "int"),
				GetSQLValueString($row_usuario['IdUsuario'], "int"),
				GetSQLValueString(date('Y-m-d H:i:s'), "date"),
				GetSQLValueString($arquivo, "text")
				);
				mysql_select_db($database_conexao, $conexao);
				$Result_update_slider_arquivo = mysql_query($update_SQL_slider_arquivo, $conexao) or die(mysql_error());
				
			}
		}
		// fim - $funcao_upload_retorno ($arquivo)
		
	}
	// fim - INSERT - mala_direta_anexo
	
	require '../../PHPMailer/PHPMailerAutoload.php';
	
	// envio do e-mail -------------------------------------------------
	function email($phpmailer_smtp, $phpmailer_email, $phpmailer_senha, $remetente, $remetente_email, $destinatario, $destinatario_email, $assunto, $conteudo, $anexo){

		$retorno = 's';
		
		if($_SERVER['SERVER_NAME'] == "localhost"){
			$destinatario_email = "juliano@agenciaclic.com.br";
		}
		
		$mail = new PHPMailer;
		
		$mail->SMTPOptions = array(
			'ssl' => array(
				'verify_peer' => false,
				'verify_peer_name' => false,
				'allow_self_signed' => true
			)
		);
		
		$mail->isSMTP(); // Set mailer to use SMTP
		$mail->Host = $phpmailer_smtp; // Specify main and backup SMTP servers
		$mail->SMTPAuth = true; // Enable SMTP authentication
		$mail->SMTPKeepAlive = true; // SMTP connection will not close after 
		$mail->Username = $phpmailer_email; // SMTP username
		$mail->Password = $phpmailer_senha; // SMTP password
		$mail->SMTPSecure = 'tls'; // Enable TLS encryption, `ssl` also accepted
		$mail->Port = 587; // TCP port to connect to
	
		$mail->isHTML(true); // Set email format to HTML
		$mail->CharSet = 'UTF-8'; // Charset da mensagem

		$mail->AddAttachment($anexo['tmp_name'], $anexo['name']);
		
		$mail->Subject = $assunto;
		$mail->Body    = stripslashes($conteudo);
		$mail->AltBody = 'Favor entrar em denuncia conosco.';
	
		$mail->addReplyTo($remetente_email, $remetente); // para quem vai a resposta
		$mail->setFrom($remetente_email, $remetente); // quem está enviando
		$mail->addAddress($destinatario_email);
		
		if(!$mail->send()) {
			$retorno = 'n';
		}
			
		$mail->ClearAddresses();
			
		$mail->SmtpClose();
		
		return $retorno;
			
	}
	// fim - envio do e-mail -------------------------------------------	
	
	$dados = http_build_query(array(
		'IdMalaDireta' => $ultimo_id
	));

	$contexto = stream_context_create(array(
	    'http' => array(
	        'method' => 'POST',
	        'content' => $dados,
	        'header' => "Content-type: application/x-www-form-urlencoded\r\n"
	        . "Content-Length: " . strlen($dados) . "\r\n",
	    )
	));
	
	$url_modelo = "http://www.success.inf.br/painel/mala_direta/";
	if($_SERVER['SERVER_NAME'] == "localhost"){
		$url_modelo = "http://localhost/success/painel/mala_direta/";
	}
	$conteudo = file_get_contents($url_modelo.'modelo.php', null, $contexto);

	// INSERT - mala_direta_destinatario
	if(count(@$_POST['destinatario']) > 0){
		foreach ($_POST['destinatario'] as $key => $value) {

			$retorno = NULL;
			
			//region destinatario_atual
			if($perfil == "p"){	
			
				// destinatario_atual
				mysql_select_db($database_conexao, $conexao);	
				$query_destinatario_atual = sprintf("
				SELECT 
				prospeccao.nome_razao_social, prospeccao.email 
				FROM mala_direta_destinatario 
				LEFT JOIN prospeccao ON mala_direta_destinatario.id_prospeccao = prospeccao.id 
				WHERE mala_direta_destinatario.id_prospeccao=%s 
				LIMIT 1 
				", 
				GetSQLValueString($value, "int"));
				$destinatario_atual = mysql_query($query_destinatario_atual, $conexao) or die(mysql_error());
				$row_destinatario_atual = mysql_fetch_assoc($destinatario_atual);
				$totalRows_destinatario_atual = mysql_num_rows($destinatario_atual);
				// fim - destinatario_atual
				
				$destinatario = $row_destinatario_atual['nome_razao_social'];
				$destinatario_email = $row_destinatario_atual['email'];
			
			} else {
				
				// destinatario_atual
				mysql_select_db($database_conexao, $conexao);	
				$query_destinatario_atual = sprintf("
				SELECT 
				da01.nome1, dc01.email1   
				FROM mala_direta_destinatario 
				LEFT JOIN da37 ON mala_direta_destinatario.contrato = da37.codigo17 
				LEFT JOIN da01 ON da37.cliente17 = da01.codigo1 
				LEFT JOIN dc01 ON dc01.codigo1 = da01.codigo1 
				WHERE mala_direta_destinatario.contrato=%s and da01.sr_deleted <> 'T' and da37.sr_deleted <> 'T' 
				LIMIT 1
				", 
				GetSQLValueString($value, "int"));
				$destinatario_atual = mysql_query($query_destinatario_atual, $conexao) or die(mysql_error());
				$row_destinatario_atual = mysql_fetch_assoc($destinatario_atual);
				$totalRows_destinatario_atual = mysql_num_rows($destinatario_atual);
				// fim - destinatario_atual
				
				$destinatario = $row_destinatario_atual['nome1'];
				$destinatario_email = $row_destinatario_atual['email1'];
				
			}
			
			mysql_free_result($destinatario_atual);
			//endregion - fim - destinatario_atual
			
			// email
			if($_POST['tipo']=="em"){

				// e-mail ----------------------------------------------				
				$assunto = $_POST['titulo'];
					
				$remetente = "Success Sistemas";
				$remetente_email = "comercial@success.inf.br";

				$retorno = email('mail.success.inf.br', 'automatico@success.inf.br', 'gersuc1987', $remetente, $remetente_email, $destinatario, $destinatario_email, $assunto, $conteudo, $anexo);
				// fim - e-mail ----------------------------------------		
				
			}
			// fim - email

			// insert
			if($perfil == "p"){	
					
				$insert_SQL_mala_direta_destinatario = sprintf("
				INSERT INTO mala_direta_destinatario (data_criacao, IdMalaDireta, IdUsuario, id_prospeccao, email) 
				VALUES (%s, %s, %s, %s, %s)
				",
				GetSQLValueString(date('Y-m-d H:i:s'), "date"),
				GetSQLValueString($ultimo_id, "int"),
				GetSQLValueString($row_usuario['IdUsuario'], "int"),
				GetSQLValueString($value, "int"),
				GetSQLValueString($retorno, "text"));
				mysql_select_db($database_conexao, $conexao);
				$Result_insert_mala_direta_destinatario = mysql_query($insert_SQL_mala_direta_destinatario, $conexao) or die(mysql_error());
	
			} else {

				$insert_SQL_mala_direta_destinatario = sprintf("
				INSERT INTO mala_direta_destinatario (data_criacao, IdMalaDireta, IdUsuario, contrato, email) 
				VALUES (%s, %s, %s, %s, %s)
				",
				GetSQLValueString(date('Y-m-d H:i:s'), "date"),
				GetSQLValueString($ultimo_id, "int"),
				GetSQLValueString($row_usuario['IdUsuario'], "int"),
				GetSQLValueString($value, "text"),
				GetSQLValueString($retorno, "text"));
				mysql_select_db($database_conexao, $conexao);
				$Result_insert_mala_direta_destinatario = mysql_query($insert_SQL_mala_direta_destinatario, $conexao) or die(mysql_error());
				
			}
			// fim - insert
	
		}
	}
	// fim - INSERT - mala_direta_destinatario

	$insertGoTo = "listar.php";
	header(sprintf("Location: %s", $insertGoTo));
	exit;
	
}
// fim - insert --------------------------------------------------------------------------------------------------------------------
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"> 
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="content-language" content="pt-BR" />
<meta name="language" content="pt-BR" />
<meta name="robots" content="noindex,nofollow" />
<meta name="author" content="Juliano Martins - (34) 8805 3396" />
<meta name="copyright" content=" Copyright © Success Sistemas - Todos os direitos reservados." />
<title>Área do Parceiro - Success Sistemas</title>
<link href="../../css/guia_painel.css" rel="stylesheet" type="text/css">
<style>
label.error { color: red; display: none; }

.tabela_destinatario{
		
}
.tabela_destinatario thead th{
	padding: 5px;
	background-color: #DDD;
}
.tabela_destinatario tbody td{
	padding: 5px;
}
</style>
<script src="../../js/jquery.js"></script>

<script src="../../js/jquery.metadata.js" ></script>
<script src="../../js/jquery.validate.1.15.js"></script>
<script src="../../js/jquery.maskedinput-1.2.2.min.js"></script> 

<script src="../../funcoes.js"></script>

<script src="../../ckeditor/ckeditor.js"></script>

<link href="../../includes/skins/mxkollection3.css" rel="stylesheet" type="text/css" media="all" />
<script src="../../includes/common/js/base.js" type="text/javascript"></script>
<script src="../../includes/common/js/utility.js" type="text/javascript"></script>
<script src="../../includes/skins/style.js" type="text/javascript"></script>
<script src="../../includes/nxt/scripts/form.js" type="text/javascript"></script>
<script src="../../includes/nxt/scripts/form.js.php" type="text/javascript"></script>
<script type="text/javascript">
$NXT_FORM_SETTINGS = {
  duplicate_buttons: false,
  show_as_grid: false,
  merge_down_value: false
}
		
$(document).ready(function() {

	<? foreach($praca_listar_array  as $praca_key => $praca_value){ ?>
	$('#corpo_filtros<? echo $praca_value['IdPraca']; ?>').toggle();
	$('#cabecalho_filtros<? echo $praca_value['IdPraca']; ?>').click(function() {
		$('#corpo_filtros<? echo $praca_value['IdPraca']; ?>').toggle();
	});
	<? } ?>
		
	// mascara
	$('#filtro_data_prospeccao_inicio').mask('99-99-9999',{placeholder:" "});
	$('#filtro_data_prospeccao_fim').mask('99-99-9999',{placeholder:" "});
	
	$('#filtro_prospeccao_agenda_data_inicio').mask('99-99-9999',{placeholder:" "});
	$('#filtro_prospeccao_agenda_data_fim').mask('99-99-9999',{placeholder:" "});
	// mascara - fim

	// ckeditor
	CKEDITOR.replace( 'texto', {
		height: '200'
	});
	// fim - ckeditor

	// validação
	$("#form").validate({
		rules: {	
			tipo: "required",
			titulo: "required",
			'destinatario[]': "required"
		},
		messages: {
			tipo: "<br>obrigatório",
			titulo: "<br>obrigatório",
			'destinatario[]': "<br>Selecione pelo menos um destinatário.<br>"
		},
		onkeyup: false,
		submitHandler: function(form) {
			$('#submit').attr('disabled', 'disabled');
			form.submit();
		} 
	});
	// fim - validação
	
	$('#checkall_situacao').click(function () {
		$(this).parents('fieldset:eq(0)').find(':checkbox').prop("checked", this.checked);
	});
	
	$('input[id="checkall_praca_situacao"]').click (function () {
		var praca_atual = $(this).attr('title');	
		$('input[id="destinatario"][title="'+praca_atual+'"]').prop("checked", this.checked);
	});
	
	// upload - arquivo (funcao_verifica_input_file)
	$("input#arquivo").change(function() {
		funcao_verifica_input_file(4096, ['pdf', 'zip', 'rar', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'bmp', 'txt', 'jpg', 'jpeg', 'gif', 'png'], $("input#arquivo"), $("#arquivo_retorno_html"));
	});
	// fim - upload - arquivo (funcao_verifica_input_file)

});
</script>
</head>
<body>

<div class="cabecalho"><? require_once('../padrao_cabecalho.php'); ?></div>

<!-- corpo -->
<div class="corpo">
	<div class="texto"> 
        <table width="100%" border="0" cellspacing="0" cellpadding="0">
            <tr>
            
                <td class="padrao_esquerda"><? require_once('../padrao_esquerda.php'); ?></td>
                                
                <td class="padrao_centro">                
                
                <!-- titulo -->
                <div class="titulo">
                <table width="100%" border="0" cellspacing="0" cellpadding="0">
                <tr>
                <td align="left">
				<?php 
				if($row_usuario['controle_mala_direta'] == 'Y'){
					// Show IF Conditional region1 
					if (@$_GET['IdMalaDireta'] == "") {
					?>
					<?php echo NXT_getResource("Insert_FH"); ?>
					<?php 
					// else Conditional region1
					} else { ?>
					<?php echo NXT_getResource("Update_FH"); ?>
					<?php } 
					// endif Conditional region1
				}
                ?> 
				<? if ($totalRows_rsmala_direta == 0) { ?>
                Inserir Mala Direta - <? if($perfil == "p"){ ?>Prospects<? } else { ?>Clientes<? } ?>
				<? } else { ?>
				Mala Direta nº <? echo $row_rsmala_direta['IdMalaDireta']; ?>
				<? } ?>
                </td>
                <td align="right"><a href="javascript:history.go(-1);"><img src="../../imagens/botao_voltar.jpg" border="0" /></a></td>
                </tr>
                </table>
                </div>
                <div class="caminho">
				<a href="../index.php">Página inicial</a> &gt;&gt; 
				<a href="listar.php">Mala Direta</a> &gt;&gt; 
				<? if ($totalRows_rsmala_direta == 0) { ?>
                Inserir Mala Direta
				<? } else { ?>
				Mala Direta nº <? echo $row_rsmala_direta['IdMalaDireta']; ?>
				<? } ?>
				</div>
                <!-- fim - titulo -->
                
                <div class="conteudo">
				
					<!-- filtro -->
					<? if ($perfil == "p" and $totalRows_rsmala_direta == 0) { ?>
					
						<div style="margin-bottom: 20px;">
						<form id="form_filtro" name="form_filtro" method="GET" enctype="multipart/form-data" action="<?php echo $editFormAction; ?>">
						
							<input type="hidden" name="perfil" id="perfil" value="<? echo $perfil; ?>" />
						
							<table width="100%" border="0" cellpadding="5" cellspacing="0" style="border: solid 1px #4F72B4;">
							
								<tr>
									<td colspan="3">
										<table width="100%" border="0" cellpadding="0" cellspacing="0">
											<tr>
												<td width="50%">
												<!-- filtro_cliente_nome -->
												<strong>Cliente:</strong><br>
												<input type="text" name="filtro_cliente_nome" id="filtro_cliente_nome" value="<? if ( isset($_GET['filtro_cliente_nome']) ) { echo $_GET['filtro_cliente_nome']; } ?>" style="width: 360px;">
												<!-- fim - filtro_cliente_nome -->
												</td>
												
												<td width="50%">
												<!-- filtro_cliente -->
												<strong>Cliente:</strong><br>
												<?
												mysql_select_db($database_conexao, $conexao);
												
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

												$where_empresa = "1=1 and da37.status17 <> 'C'";
												
												if ($row_usuario['controle_suporte'] == "Y" or $row_usuario['suporte_operador_parceiro'] == "Y"){ 
													$where_empresa .= " and 1=1";	
												} else { // se não é controle_suporte/suporte_operador_parceiro - então realiza filtragem por praça
													$where_empresa .= " and $sql_clientes_vendedor17";	
												}

												// filtro_cliente - ok
												mysql_select_db($database_conexao, $conexao);
												$query_filtro_cliente = "
												SELECT 
												da01.codigo1, da01.nome1, da01.cidade1, da01.atraso1,
												da37.codigo17, da37.cliente17, da37.status17
												FROM da01 
												INNER JOIN da37 ON da01.codigo1 = da37.cliente17
												WHERE $where_empresa and da01.sr_deleted <> 'T' and da37.sr_deleted <> 'T' 
												ORDER BY da01.nome1 ASC";
												$filtro_cliente = mysql_query($query_filtro_cliente, $conexao) or die(mysql_error());
												$row_filtro_cliente = mysql_fetch_assoc($filtro_cliente);
												$totalRows_filtro_cliente = mysql_num_rows($filtro_cliente);
												// fim - filtro_cliente
												?>												
												<select name="filtro_cliente" id="filtro_cliente" style="width: 380px;">
												<option value=""
												<?php if (!(strcmp("", isset($_GET['filtro_cliente'])))) {echo "selected=\"selected\"";} ?>
												>
												...
												</option>
												<?php do {  ?>
												<option value="<?php echo $row_filtro_cliente['codigo1']?>"
												<?php if ( (isset($_GET['filtro_cliente'])) and (!(strcmp($row_filtro_cliente['codigo1'], $_GET['filtro_cliente']))) ) {echo "selected=\"selected\"";} ?>
												>
												<?php echo $row_filtro_cliente['nome1']?>
												</option>
												<?php
												} while ($row_filtro_cliente = mysql_fetch_assoc($filtro_cliente));
												$rows = mysql_num_rows($filtro_cliente);
												if($rows > 0) {
												mysql_data_seek($filtro_cliente, 0);
												$row_filtro_cliente = mysql_fetch_assoc($filtro_cliente);
												}
												?>
												</select>
												<? mysql_free_result($filtro_cliente); ?>
												<? mysql_free_result($usuarios_geral_tipo_praca_executor); ?>
												<!-- fim - filtro_cliente -->
												</td>
											</tr>
										</table>
									</td>
									</tr>
								<tr>
									<td colspan="3">
										<table width="100%" border="0" cellpadding="0" cellspacing="0">
											<tr>
												<td width="50%">


									<!-- filtro_data_prospeccao_fim -->
									<strong>Data criação (final):</strong><br>
									<input type="text" name="filtro_data_prospeccao_fim" id="filtro_data_prospeccao_fim" value="<? if ( isset($_GET['filtro_data_prospeccao_fim']) ) { echo $_GET['filtro_data_prospeccao_fim']; } ?>" style="width: 360px;">
									<!-- fim - filtro_data_prospeccao_fim -->



												</td>
												
												<td width="50%">
									<!-- filtro_data_prospeccao_fim -->
									<strong>Data criação (final):</strong><br>
									<input type="text" name="filtro_data_prospeccao_fim" id="filtro_data_prospeccao_fim" value="<? if ( isset($_GET['filtro_data_prospeccao_fim']) ) { echo $_GET['filtro_data_prospeccao_fim']; } ?>" style="width: 370px;">
									<!-- fim - filtro_data_prospeccao_fim -->
												</td>
											</tr>
										</table>
									</td>
								</tr>

								<tr>
									<td width="33%">
									<!-- filtro_status -->
									<strong>Status:</strong><br>
									<select name="filtro_status" id="filtro_status" style="width: 240px;">
										<option value="">...</option>
										<option value="a" <?php if ( (isset($_GET['filtro_status'])) and (!(strcmp("a", $_GET['filtro_status']))) ) {echo "selected=\"selected\"";} ?>>Inativa</option>
										<option value="f" <?php if ( (isset($_GET['filtro_status'])) and (!(strcmp("f", $_GET['filtro_status']))) ) {echo "selected=\"selected\"";} ?>>Ativa</option>
									</select>
									<!-- fim - filtro_status -->
									</td>
									<td width="34%">
									<!-- filtro_agenda_data_inicio -->
									<strong>Data agenda (inicial):</strong><br>
									<input type="text" name="filtro_agenda_data_inicio" id="filtro_agenda_data_inicio" value="<? if ( isset($_GET['filtro_agenda_data_inicio']) ) { echo $_GET['filtro_agenda_data_inicio']; } ?>" style="width: 230px;">
									<!-- fim - filtro_agenda_data_inicio -->
									</td>
									<td width="33%">
									<!-- filtro_agenda_data_fim -->
									<strong>Data agenda (final):</strong><br>
									<input type="text" name="filtro_agenda_data_fim" id="filtro_agenda_data_fim" value="<? if ( isset($_GET['filtro_agenda_data_fim']) ) { echo $_GET['filtro_agenda_data_fim']; } ?>" style="width: 230px;">
									<!-- fim - filtro_agenda_data_fim -->
									</td>
								</tr>
																
								<tr>
									<td width="33%">
									<!-- filtro_praca -->
									<strong>Praça:</strong><br>
									<?
									// filtro praca - ok
									mysql_select_db($database_conexao, $conexao);
									$query_filtro_praca = "SELECT praca FROM geral_tipo_praca ORDER BY praca ASC";
									$filtro_praca = mysql_query($query_filtro_praca, $conexao) or die(mysql_error());
									$row_filtro_praca = mysql_fetch_assoc($filtro_praca);
									$totalRows_filtro_praca = mysql_num_rows($filtro_praca);
									// fim - filtro praca
									?>												
									<select name="filtro_praca" id="filtro_praca" style="width: 240px;">
									<option value=""
									<?php if (!(strcmp("", isset($_GET['filtro_praca'])))) {echo "selected=\"selected\"";} ?>
									>
									...
									</option>
									<?php do {  ?>
									<option value="<?php echo $row_filtro_praca['praca']?>"
									<?php if ( (isset($_GET['filtro_praca'])) and (!(strcmp($row_filtro_praca['praca'], $_GET['filtro_praca']))) ) {echo "selected=\"selected\"";} ?>
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
									<? mysql_free_result($filtro_praca); ?>
									<!-- fim - filtro_praca -->
									</td>
									<td width="34%">
									<!-- filtro_id_usuario_responsavel -->
									<strong>Responsável:</strong><br>
									<?
									// filtro_usuarios - ok
									mysql_select_db($database_conexao, $conexao);
									$query_filtro_usuarios = "SELECT * FROM usuarios WHERE status = 1 ORDER BY nome ASC";
									$filtro_usuarios = mysql_query($query_filtro_usuarios, $conexao) or die(mysql_error());
									$row_filtro_usuarios = mysql_fetch_assoc($filtro_usuarios);
									$totalRows_filtro_usuarios = mysql_num_rows($filtro_usuarios);
									// fim - filtro_usuarios
									?>								
									<select name="filtro_id_usuario_responsavel" id="filtro_id_usuario_responsavel" style="width: 250px;">
									<option value=""
									<?php if (!(strcmp("", isset($_GET['filtro_id_usuario_responsavel'])))) {echo "selected=\"selected\"";} ?>
									>
									...
									</option>
									<?php do {  ?>
									<option value="<?php echo $row_filtro_usuarios['IdUsuario']?>" 
									<?php if ( (isset($_GET['filtro_id_usuario_responsavel'])) and (!(strcmp($row_filtro_usuarios['IdUsuario'], $_GET['filtro_id_usuario_responsavel']))) ) {echo "selected=\"selected\"";} ?>>
									<?php echo $row_filtro_usuarios['nome']?>
									</option>
									<?php
									} while ($row_filtro_usuarios = mysql_fetch_assoc($filtro_usuarios));
									$rows = mysql_num_rows($filtro_usuarios);
									if($rows > 0) {
									mysql_data_seek($filtro_usuarios, 0);
									$row_filtro_usuarios = mysql_fetch_assoc($filtro_usuarios);
									}
									?>
									</select>
									<? mysql_free_result($filtro_usuarios); ?>
									<!-- fim - filtro_id_usuario_responsavel -->
									</td>
									<td width="33%">
									<!-- filtro_cidade -->
									<strong>Cidade:</strong><br>
									<input type="text" name="filtro_cidade" id="filtro_cidade" value="<? if ( isset($_GET['filtro_cidade']) ) { echo $_GET['filtro_cidade']; } ?>" style="width: 230px;">
									<!-- fim - filtro_cidade -->
									</td>
								</tr>
								
								<tr>
									<td>
									<!-- filtro_concorrente -->
									<strong>Concorrente:</strong><br>
									<?
									// filtro concorrente - ok
									mysql_select_db($database_conexao, $conexao);
									$query_filtro_concorrente = "SELECT id, nome FROM prospeccao_concorrente ORDER BY nome ASC";
									$filtro_concorrente = mysql_query($query_filtro_concorrente, $conexao) or die(mysql_error());
									$row_filtro_concorrente = mysql_fetch_assoc($filtro_concorrente);
									$totalRows_filtro_concorrente = mysql_num_rows($filtro_concorrente);
									// fim - filtro concorrente
									?>												
									<select name="filtro_concorrente" id="filtro_concorrente" style="width: 240px;">
									<option value=""
									<?php if (!(strcmp("", isset($_GET['filtro_concorrente'])))) {echo "selected=\"selected\"";} ?>
									>
									...
									</option>
									<?php do {  ?>
									<option value="<?php echo $row_filtro_concorrente['id']?>"
									<?php if ( (isset($_GET['filtro_concorrente'])) and (!(strcmp($row_filtro_concorrente['id'], $_GET['filtro_concorrente']))) ) {echo "selected=\"selected\"";} ?>
									>
									<?php echo $row_filtro_concorrente['nome']?>
									</option>
									<?php
									} while ($row_filtro_concorrente = mysql_fetch_assoc($filtro_concorrente));
									$rows = mysql_num_rows($filtro_concorrente);
									if($rows > 0) {
									mysql_data_seek($filtro_concorrente, 0);
									$row_filtro_concorrente = mysql_fetch_assoc($filtro_concorrente);
									}
									?>
									</select>
									<? mysql_free_result($filtro_concorrente); ?>
									<!-- fim - filtro_concorrente -->
									</td>
									<td>
									<!-- filtro_contador -->
									<strong>Contador:</strong><br>
									<input type="text" name="filtro_contador" id="filtro_contador" value="<? if ( isset($_GET['filtro_contador']) ) { echo $_GET['filtro_contador']; } ?>" style="width: 230px;">
									<!-- fim - filtro_contador -->
									</td>
									<td>
									<!-- filtro_indicacao -->
									<strong>Indicação:</strong><br>									
									<select name="filtro_indicacao" id="filtro_indicacao" style="width: 240px;">
										<option value=""> ...</option>
										<option value="co" <?php if ( (isset($_GET['filtro_indicacao'])) and (!(strcmp("co", $_GET['filtro_indicacao']))) ) {echo "selected=\"selected\"";} ?>>Contador</option>
										<option value="cl" <?php if ( (isset($_GET['filtro_indicacao'])) and (!(strcmp("cl", $_GET['filtro_indicacao']))) ) {echo "selected=\"selected\"";} ?>>Cliente</option>
										<option value="fu" <?php if ( (isset($_GET['filtro_indicacao'])) and (!(strcmp("fu", $_GET['filtro_indicacao']))) ) {echo "selected=\"selected\"";} ?>>Funcionário</option>
										<option value="cs" <?php if ( (isset($_GET['filtro_indicacao'])) and (!(strcmp("cs", $_GET['filtro_indicacao']))) ) {echo "selected=\"selected\"";} ?>>Colaborador Success</option>
										<option value="te" <?php if ( (isset($_GET['filtro_indicacao'])) and (!(strcmp("te", $_GET['filtro_indicacao']))) ) {echo "selected=\"selected\"";} ?>>Terceiros</option>
									</select>
									<!-- fim - filtro_indicacao -->

									</td>
								</tr>
								<tr>
									<td width="33%">
									<!-- filtro_sistema_possui -->
									<strong>Possui Sitema:</strong><br>
									<select name="filtro_sistema_possui" id="filtro_sistema_possui" style="width: 240px;">
										<option value="">...</option>
										<option value="n" <?php if ( (isset($_GET['filtro_sistema_possui'])) and (!(strcmp("n", $_GET['filtro_sistema_possui']))) ) {echo "selected=\"selected\"";} ?>>Não</option>
										<option value="s" <?php if ( (isset($_GET['filtro_sistema_possui'])) and (!(strcmp("s", $_GET['filtro_sistema_possui']))) ) {echo "selected=\"selected\"";} ?>>Sim</option>
									</select>
									<!-- fim - filtro_sistema_possui -->
									</td>
									<td width="34%">
									<!-- filtro_migracao -->
									<strong>Migração de Dados:</strong><br>
									<select name="filtro_migracao" id="filtro_migracao" style="width: 240px;">
										<option value="">...</option>
										<option value="s" <?php if ( (isset($_GET['filtro_migracao'])) and (!(strcmp("s", $_GET['filtro_migracao']))) ) {echo "selected=\"selected\"";} ?>>Sim</option>
										<option value="n" <?php if ( (isset($_GET['filtro_migracao'])) and (!(strcmp("n", $_GET['filtro_migracao']))) ) {echo "selected=\"selected\"";} ?>>Não</option>
									</select>
									<!-- fim - filtro_migracao -->
									</td>
									<td width="33%">
									<!-- filtro_nivel_interesse -->
									<strong>Nível de interesse:</strong><br>									
									<select name="filtro_nivel_interesse" id="filtro_nivel_interesse" style="width: 240px;">
										<option value=""> ...</option>
										<option value="a" <?php if ( (isset($_GET['filtro_nivel_interesse'])) and (!(strcmp("a", $_GET['filtro_nivel_interesse']))) ) {echo "selected=\"selected\"";} ?>>Alto</option>
										<option value="m" <?php if ( (isset($_GET['filtro_nivel_interesse'])) and (!(strcmp("m", $_GET['filtro_nivel_interesse']))) ) {echo "selected=\"selected\"";} ?>>Médio</option>
										<option value="b" <?php if ( (isset($_GET['filtro_nivel_interesse'])) and (!(strcmp("b", $_GET['filtro_nivel_interesse']))) ) {echo "selected=\"selected\"";} ?>>Baixo</option>
										<option value="n" <?php if ( (isset($_GET['filtro_nivel_interesse'])) and (!(strcmp("n", $_GET['filtro_nivel_interesse']))) ) {echo "selected=\"selected\"";} ?>>Nenhum</option>
									</select>
									<!-- fim - filtro_nivel_interesse -->
									</td>
								</tr>
								
								<tr>
									<td width="33%">
									<!-- filtro_baixa_perda_motivo -->
									<strong>Motivo da Perda:</strong><br>
									<select name="filtro_baixa_perda_motivo" id="filtro_baixa_perda_motivo" style="width: 240px;">
										<option value=""> ...</option>
										<option value="falta de recurso" <?php if ( (isset($_GET['filtro_baixa_perda_motivo'])) and (!(strcmp("falta de recurso", $_GET['filtro_baixa_perda_motivo']))) ) {echo "selected=\"selected\"";} ?>>falta de recurso</option>
										<option value="concorrência" <?php if ( (isset($_GET['filtro_baixa_perda_motivo'])) and (!(strcmp("concorrência", $_GET['filtro_baixa_perda_motivo']))) ) {echo "selected=\"selected\"";} ?>>concorrência</option>
										<option value="encerramento de atividade" <?php if ( (isset($_GET['filtro_baixa_perda_motivo'])) and (!(strcmp("encerramento de atividade", $_GET['filtro_baixa_perda_motivo']))) ) {echo "selected=\"selected\"";} ?>>encerramento de atividade</option>
									</select>
									<!-- fim - filtro_baixa_perda_motivo -->
									</td>
									<td width="34%">

									</td>
									<td width="33%">

									</td>
								</tr>
								
								<tr>
									<td colspan="3">
									<!-- filtro_modulo -->
									<strong>Módulos:</strong><br>
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
										
											<input  name="filtro_modulo[]" id="filtro_modulo" type="checkbox" class="checkbox" value="<? echo $row_geral_tipo_modulo_listar['IdTipoModulo']; ?>"/>
											<? echo $row_geral_tipo_modulo_listar['descricao']; ?>
											
										<? } while ($row_geral_tipo_modulo_listar = mysql_fetch_assoc($geral_tipo_modulo_listar)); ?>
										
									<? mysql_free_result($geral_tipo_modulo_listar); ?>
									<!-- fim - filtro_modulo -->
									</td>
								</tr>
								
								<tr>
									<td colspan="3">
									<!-- filtro_ferramenta -->
									<strong>Ferramentas:</strong><br>
									<?
									// geral_tipo_modulo_listar
									mysql_select_db($database_conexao, $conexao);
									$query_geral_tipo_modulo_listar = "
									SELECT 
									geral_tipo_modulo.IdTipoModulo, geral_tipo_modulo.IdTipoModuloCategoria, geral_tipo_modulo.descricao 
									FROM geral_tipo_modulo 
									WHERE IdTipoModuloCategoria = 3
									ORDER BY geral_tipo_modulo.IdTipoModuloCategoria ASC, geral_tipo_modulo.ordem ASC, geral_tipo_modulo.IdTipoModulo ASC";
									$geral_tipo_modulo_listar = mysql_query($query_geral_tipo_modulo_listar, $conexao) or die(mysql_error());
									$row_geral_tipo_modulo_listar = mysql_fetch_assoc($geral_tipo_modulo_listar);
									$totalRows_geral_tipo_modulo_listar = mysql_num_rows($geral_tipo_modulo_listar);
									// fim - geral_tipo_modulo_listar
									?>
									
										<? do { ?>
										
											<input  name="filtro_ferramenta[]" id="filtro_ferramenta" type="checkbox" class="checkbox" value="<? echo $row_geral_tipo_modulo_listar['IdTipoModulo']; ?>"/>
											<? echo $row_geral_tipo_modulo_listar['descricao']; ?>
											
										<? } while ($row_geral_tipo_modulo_listar = mysql_fetch_assoc($geral_tipo_modulo_listar)); ?>
										
									<? mysql_free_result($geral_tipo_modulo_listar); ?>
									<!-- fim - filtro_ferramenta -->
									</td>
								</tr>
								
								<tr>
									<td colspan="3">
									<input type="submit" value="Filtrar" />
									</td>
								</tr>
							</table>
						
						</form>
						</div>
						
					<? } else if ($totalRows_rsmala_direta == 0) { ?>
					
						<div style="margin-bottom: 20px;">
						<form id="form_filtro" name="form_filtro" method="GET" enctype="multipart/form-data" action="<?php echo $editFormAction; ?>">

							<table width="100%" border="0" cellpadding="5" cellspacing="0" style="border: solid 1px #4F72B4;">
								<tr>
									<td width="33%">
									<!-- filtro_cliente_nome -->
									<strong>Cliente:</strong><br>
									<input type="text" name="filtro_cliente_nome" id="filtro_cliente_nome" value="<? if ( isset($_GET['filtro_cliente_nome']) ) { echo $_GET['filtro_cliente_nome']; } ?>" style="width: 230px;">
									<!-- fim - filtro_cliente_nome -->
									</td>
									
									<td width="34%">
									<!-- filtro_cliente -->
									<strong>Cliente:</strong><br>
									<?
									mysql_select_db($database_conexao, $conexao);
									
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

									$where_empresa = "1=1 and da37.status17 <> 'C'";
									
									if ($row_usuario['controle_suporte'] == "Y" or $row_usuario['suporte_operador_parceiro'] == "Y"){ 
										$where_empresa .= " and 1=1";	
									} else { // se não é controle_suporte/suporte_operador_parceiro - então realiza filtragem por praça
										$where_empresa .= " and $sql_clientes_vendedor17";	
									}

									// filtro_cliente - ok
									mysql_select_db($database_conexao, $conexao);
									$query_filtro_cliente = "
									SELECT 
									da01.codigo1, da01.nome1, da01.cidade1, da01.atraso1,
									da37.codigo17, da37.cliente17, da37.status17
									FROM da01 
									INNER JOIN da37 ON da01.codigo1 = da37.cliente17
									WHERE $where_empresa and da01.sr_deleted <> 'T' and da37.sr_deleted <> 'T' 
									ORDER BY da01.nome1 ASC";
									$filtro_cliente = mysql_query($query_filtro_cliente, $conexao) or die(mysql_error());
									$row_filtro_cliente = mysql_fetch_assoc($filtro_cliente);
									$totalRows_filtro_cliente = mysql_num_rows($filtro_cliente);
									// fim - filtro_cliente
									?>												
									<select name="filtro_cliente" id="filtro_cliente" style="width: 250px;">
									<option value=""
									<?php if (!(strcmp("", isset($_GET['filtro_cliente'])))) {echo "selected=\"selected\"";} ?>
									>
									...
									</option>
									<?php do {  ?>
									<option value="<?php echo $row_filtro_cliente['codigo1']?>"
									<?php if ( (isset($_GET['filtro_cliente'])) and (!(strcmp($row_filtro_cliente['codigo1'], $_GET['filtro_cliente']))) ) {echo "selected=\"selected\"";} ?>
									>
									<?php echo $row_filtro_cliente['nome1']?>
									</option>
									<?php
									} while ($row_filtro_cliente = mysql_fetch_assoc($filtro_cliente));
									$rows = mysql_num_rows($filtro_cliente);
									if($rows > 0) {
									mysql_data_seek($filtro_cliente, 0);
									$row_filtro_cliente = mysql_fetch_assoc($filtro_cliente);
									}
									?>
									</select>
									<? mysql_free_result($filtro_cliente); ?>
									<? mysql_free_result($usuarios_geral_tipo_praca_executor); ?>
									<!-- fim - filtro_cliente -->
									</td>
									
									<td width="34%">
									<!-- filtro_tipo_ramo_atividade -->
									<strong>Ramo Atividade:</strong><br>
									<?
									// filtro_geral_tipo_ramo_atividade - ok
									mysql_select_db($database_conexao, $conexao);
									$query_filtro_geral_tipo_ramo_atividade = "SELECT * FROM geral_tipo_ramo_atividade ORDER BY titulo ASC";
									$filtro_geral_tipo_ramo_atividade = mysql_query($query_filtro_geral_tipo_ramo_atividade, $conexao) or die(mysql_error());
									$row_filtro_geral_tipo_ramo_atividade = mysql_fetch_assoc($filtro_geral_tipo_ramo_atividade);
									$totalRows_filtro_geral_tipo_ramo_atividade = mysql_num_rows($filtro_geral_tipo_ramo_atividade);
									// fim - filtro_geral_tipo_ramo_atividade
									?>								
									<select name="filtro_tipo_ramo_atividade" id="filtro_tipo_ramo_atividade" style="width: 240px;">
									<option value=""
									<?php if (!(strcmp("", isset($_GET['filtro_tipo_ramo_atividade'])))) {echo "selected=\"selected\"";} ?>
									>
									...
									</option>
									<?php do {  ?>
									<option value="<?php echo $row_filtro_geral_tipo_ramo_atividade['codigo']?>" 
									<?php if ( (isset($_GET['filtro_tipo_ramo_atividade'])) and (!(strcmp($row_filtro_geral_tipo_ramo_atividade['titulo'], $_GET['filtro_tipo_ramo_atividade']))) ) {echo "selected=\"selected\"";} ?>>
									<?php echo $row_filtro_geral_tipo_ramo_atividade['titulo']?>
									</option>
									<?php
									} while ($row_filtro_geral_tipo_ramo_atividade = mysql_fetch_assoc($filtro_geral_tipo_ramo_atividade));
									$rows = mysql_num_rows($filtro_geral_tipo_ramo_atividade);
									if($rows > 0) {
									mysql_data_seek($filtro_geral_tipo_ramo_atividade, 0);
									$row_filtro_geral_tipo_ramo_atividade = mysql_fetch_assoc($filtro_geral_tipo_ramo_atividade);
									}
									?>
									</select>
									<? mysql_free_result($filtro_geral_tipo_ramo_atividade); ?>
									<!-- fim - filtro_tipo_ramo_atividade -->
									</td>
									
								</tr>
								<tr>
									<td width="33%">
									<!-- filtro_praca -->
									<strong>Praça:</strong><br>
									<?
									// filtro praca - ok
									mysql_select_db($database_conexao, $conexao);
									$query_filtro_praca = "SELECT praca FROM geral_tipo_praca ORDER BY praca ASC";
									$filtro_praca = mysql_query($query_filtro_praca, $conexao) or die(mysql_error());
									$row_filtro_praca = mysql_fetch_assoc($filtro_praca);
									$totalRows_filtro_praca = mysql_num_rows($filtro_praca);
									// fim - filtro praca
									?>												
									<select name="filtro_praca" id="filtro_praca" style="width: 240px;">
									<option value=""
									<?php if (!(strcmp("", isset($_GET['filtro_praca'])))) {echo "selected=\"selected\"";} ?>
									>
									...
									</option>
									<?php do {  ?>
									<option value="<?php echo $row_filtro_praca['praca']?>"
									<?php if ( (isset($_GET['filtro_praca'])) and (!(strcmp($row_filtro_praca['praca'], $_GET['filtro_praca']))) ) {echo "selected=\"selected\"";} ?>
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
									<? mysql_free_result($filtro_praca); ?>
									<!-- fim - filtro_praca -->
									</td>
									<td width="34%">
									<!-- filtro_tipo_visita -->
									<strong>Tipo de visita:</strong><br>
									<?
									// filtro_geral_tipo_visita - ok
									mysql_select_db($database_conexao, $conexao);
									$query_filtro_geral_tipo_visita = "SELECT * FROM geral_tipo_visita ORDER BY descricao ASC";
									$filtro_geral_tipo_visita = mysql_query($query_filtro_geral_tipo_visita, $conexao) or die(mysql_error());
									$row_filtro_geral_tipo_visita = mysql_fetch_assoc($filtro_geral_tipo_visita);
									$totalRows_filtro_geral_tipo_visita = mysql_num_rows($filtro_geral_tipo_visita);
									// fim - filtro_geral_tipo_visita
									?>								
									<select name="filtro_tipo_visita" id="filtro_tipo_visita" style="width: 250px;">
									<option value=""
									<?php if (!(strcmp("", isset($_GET['filtro_tipo_visita'])))) {echo "selected=\"selected\"";} ?>
									>
									...
									</option>
									<?php do {  ?>
									<option value="<?php echo $row_filtro_geral_tipo_visita['IdTipoVisita']?>" 
									<?php if ( (isset($_GET['filtro_tipo_visita'])) and (!(strcmp($row_filtro_geral_tipo_visita['IdTipoVisita'], $_GET['filtro_tipo_visita']))) ) {echo "selected=\"selected\"";} ?>>
									<?php echo $row_filtro_geral_tipo_visita['descricao']?>
									</option>
									<?php
									} while ($row_filtro_geral_tipo_visita = mysql_fetch_assoc($filtro_geral_tipo_visita));
									$rows = mysql_num_rows($filtro_geral_tipo_visita);
									if($rows > 0) {
									mysql_data_seek($filtro_geral_tipo_visita, 0);
									$row_filtro_geral_tipo_visita = mysql_fetch_assoc($filtro_geral_tipo_visita);
									}
									?>
									</select>
									<? mysql_free_result($filtro_geral_tipo_visita); ?>
									<!-- fim - filtro_tipo_visita -->
									</td>
									<td width="33%">
									<!-- filtro_optante_acumulo -->
									<strong>Optante por acumulo:</strong><br>
									<select name="filtro_optante_acumulo" id="filtro_optante_acumulo" style="width: 240px;">
									<option value="" <?php if (!(strcmp("", isset($_GET['filtro_optante_acumulo'])))) {echo "selected=\"selected\"";} ?>>...</option>
									<option value="s" <?php if ( (isset($_GET['filtro_optante_acumulo'])) and (!(strcmp('s', $_GET['filtro_optante_acumulo']))) ) {echo "selected=\"selected\"";} ?>>Sim</option>
									<option value="n" <?php if ( (isset($_GET['filtro_optante_acumulo'])) and (!(strcmp('n', $_GET['filtro_optante_acumulo']))) ) {echo "selected=\"selected\"";} ?>>Não</option>
									</select>
									<!-- fim - filtro_optante_acumulo -->
									</td>
								</tr>
								
								<tr>
									<td colspan="3">
									<!-- filtro_modulo -->
									<strong>Módulos:</strong><br>
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
										
											<input  name="filtro_modulo[]" id="filtro_modulo" type="checkbox" class="checkbox" value="<? echo $row_geral_tipo_modulo_listar['IdTipoModulo']; ?>"/>
											<? echo $row_geral_tipo_modulo_listar['descricao']; ?>
											
										<? } while ($row_geral_tipo_modulo_listar = mysql_fetch_assoc($geral_tipo_modulo_listar)); ?>
										
									<? mysql_free_result($geral_tipo_modulo_listar); ?>
									<!-- fim - filtro_modulo -->
									</td>
								</tr>
								
								<tr>
									<td colspan="3">
									<!-- filtro_ferramenta -->
									<strong>Ferramentas:</strong><br>
									<?
									// geral_tipo_modulo_listar
									mysql_select_db($database_conexao, $conexao);
									$query_geral_tipo_modulo_listar = "
									SELECT 
									geral_tipo_modulo.IdTipoModulo, geral_tipo_modulo.IdTipoModuloCategoria, geral_tipo_modulo.descricao 
									FROM geral_tipo_modulo 
									WHERE IdTipoModuloCategoria = 3
									ORDER BY geral_tipo_modulo.IdTipoModuloCategoria ASC, geral_tipo_modulo.ordem ASC, geral_tipo_modulo.IdTipoModulo ASC";
									$geral_tipo_modulo_listar = mysql_query($query_geral_tipo_modulo_listar, $conexao) or die(mysql_error());
									$row_geral_tipo_modulo_listar = mysql_fetch_assoc($geral_tipo_modulo_listar);
									$totalRows_geral_tipo_modulo_listar = mysql_num_rows($geral_tipo_modulo_listar);
									// fim - geral_tipo_modulo_listar
									?>
									
										<? do { ?>
										
											<input  name="filtro_ferramenta[]" id="filtro_ferramenta" type="checkbox" class="checkbox" value="<? echo $row_geral_tipo_modulo_listar['IdTipoModulo']; ?>"/>
											<? echo $row_geral_tipo_modulo_listar['descricao']; ?>
											
										<? } while ($row_geral_tipo_modulo_listar = mysql_fetch_assoc($geral_tipo_modulo_listar)); ?>
										
									<? mysql_free_result($geral_tipo_modulo_listar); ?>
									<!-- fim - filtro_ferramenta -->
									</td>
								</tr>
								
								<tr>
									<td colspan="3">
									<input type="submit" value="Filtrar" />
									</td>
								</tr>
							</table>
						
						</form>
						</div>
						
					<? } ?>
                	<!-- fim - filtro -->


					<form id="form" name="form" method="POST" enctype="multipart/form-data" action="<?php echo $editFormAction; ?>" class="cmxform">
					
					<!-- Destinatários -->
                    <div class="KT_tng">
                        <div class="KT_tngform">
                        
                            <table cellpadding="2" cellspacing="0" class="KT_tngtable">
                            	<tr>
                              		<td colspan="2" class="KT_th"><label for="destinatario">Destinatários (<? echo $totalRows_destinatario_listar; ?>):</label></td>
                              	</tr>
                              <tr>
                                	<td colspan="2">
									<? if($totalRows_destinatario_listar > 0){ ?>
									
										<fieldset style="border: 0; padding: 0;">
										
										<label for="destinatario[]" class="error"></label>
										<? if ($totalRows_rsmala_direta == 0) { ?>
										<div style="margin-bottom: 20px;">
											<input type="checkbox" id="checkall_situacao"  name="checkall_situacao" /> <strong>Marcar todos</strong>
											</div>
										<? } else { ?>
										&nbsp;
										<? } ?>
										
										<div style="clear: both;"></div>
										
										<? foreach($praca_listar_array  as $praca_key => $praca_value){ ?>
										
											<? $destinatario_listar_filtro_array = filter_by_value($destinatario_listar_array, 'praca',  $praca_value['praca']); ?>
											
											<? if(count($destinatario_listar_filtro_array) > 0){ ?>
											
												<!-- marcar todos - praca atual -->
												<div style="clear: both;"></div>
												<div style="width: 750px; padding: 5px; border: 0px solid #000; margin-bottom: 5px; background-color: #DDD; font-weight: bold;">
													<div style="float: left; margin-right: 20px; cursor: pointer;" id="cabecalho_filtros<? echo $praca_value['IdPraca']; ?>">
														<? echo $praca_value['praca']; ?> (<? echo count($destinatario_listar_filtro_array); ?>)
													</div>
													<? if ($totalRows_rsmala_direta == 0) { ?>
														<div style="float: right;">
															<input type="checkbox" id="checkall_praca_situacao" name="checkall_praca_situacao[]" title="<? echo $praca_value['praca']; ?>" /> 
															<strong>Marcar todos da praça</strong>
														</div>
													<? } ?>
													<div style="clear: both;"></div>
												</div>
												<!-- fim - marcar todos - praca atual -->
												
												<? if($perfil == "p"){ ?>
												
													<table cellpadding="2" cellspacing="0" class="tabela_destinatario" id="corpo_filtros<? echo $praca_value['IdPraca']; ?>">
														<thead>
															<tr>
																<th width="20">&nbsp;</th>
																<th width="720">Cliente</th>
															</tr>
															
														</thead>
														
														<tbody>
															<!-- foreach praca atual -->
															<? foreach($destinatario_listar_filtro_array as $key => $value){ ?>
																		
																<tr>
																	<td>
																	<? if ($totalRows_rsmala_direta == 0) { ?>
																		<input type="checkbox" id="destinatario" name="destinatario[]" value="<? echo $value['id']; ?>" 
																		title="<? echo $value['praca']; ?>"> 
																	<? } ?>
																	</td>
																	
																	<td>
																	<? 
																	if($value['tipo_cliente']=="a"){ 
																		echo utf8_encode($value['nome_razao_social']); 
																	} else {
																		echo $value['nome_razao_social']; 
																	}
																	?>
																	</td>
																															
																</tr>
		
															<? } ?>
															<!-- fim - foreach praca atual -->
														</tbody>
													</table>
												
												<? } else { ?>
												
													<table cellpadding="2" cellspacing="0" class="tabela_destinatario" id="corpo_filtros<? echo $praca_value['IdPraca']; ?>">
														<thead>
															<tr>
																<th width="20">&nbsp;</th>
																<th width="330">Cliente</th>
																<th width="120">Ramo de Atividade</th>
																<th width="80">Contrato</th>
																<th width="80">Tipo de visita</th>
																<th width="80">Acumulo</th>
															</tr>
															
														</thead>
														
														<tbody>
															<!-- foreach praca atual -->
															<? foreach($destinatario_listar_filtro_array as $key => $value){ ?>
																		
																<tr>
																	<td>
																	<? if ($totalRows_rsmala_direta == 0) { ?>
																		<input type="checkbox" id="destinatario" name="destinatario[]" value="<? echo $value['codigo17']; ?>" 
																		title="<? echo $value['praca']; ?>"> 
																	<? } ?>
																	</td>
																	
																	<td>
																	<? echo utf8_encode($value['nome1']); ?>
																	</td>
																	
																	<td>
																	<? echo $value['geral_tipo_ramo_atividade_titulo']; ?>
																	</td>
																	
																	<td>
																	<? echo $value['codigo17']; ?>
																	</td>
																	
																	<td>
																	<? echo $value['visita17_descricao']; ?>
																	</td>
																	
																	<td>
																	<? 
																	if($value['optacuv17']=="N"){
																		echo "Não";
																	} else if($value['optacuv17']=="S"){
																		echo "Sim";
																	}
																	?>
																	</td>
																															
																</tr>
		
															<? } ?>
															<!-- fim - foreach praca atual -->
														</tbody>
													</table>
												
												<? } ?>
												
											<? } ?>
										
										<? } ?>
										
										</fieldset>
										
									<? } else { ?>
									Nenhum destinatário encontrado.
									<? } ?>
									</td>
                            	</tr>
                            </table>

                        </div>
                        <br class="clearfixplain" />
                    </div>
					<!-- fim - Destinatários -->
					
					
					<!-- mala_direta -->
					<? if($totalRows_destinatario_listar > 0){ ?>
                    <div class="KT_tng" style="margin-top: 20px;">
                        <div class="KT_tngform">
							
							<table cellpadding="2" cellspacing="0" class="KT_tngtable">
							
							<tr>
								<td class="KT_th"><label for="tipo">Tipo:</label></td>
								<td>
								<? if ($totalRows_rsmala_direta == 0) { ?>
									<!-- tipo -->
									<select name="tipo" id="tipo" style="width: 692px;" <? if ($totalRows_rsmala_direta > 0) { ?>disabled="disabled"<? } ?>>
									<option value="" <?php if (!(strcmp("", isset($row_rsmala_direta['tipo'])))) {echo "selected=\"selected\"";} ?>>...</option>
									<option value="em" <?php if ( (isset($row_rsmala_direta['tipo'])) and (!(strcmp('em', $row_rsmala_direta['tipo']))) ) {echo "selected=\"selected\"";} ?>>E-mail</option>
									<option value="et" <?php if ( (isset($row_rsmala_direta['tipo'])) and (!(strcmp('et', $row_rsmala_direta['tipo']))) ) {echo "selected=\"selected\"";} ?>>Etiqueta</option>
									<option value="po" <?php if ( (isset($row_rsmala_direta['tipo'])) and (!(strcmp('po', $row_rsmala_direta['tipo']))) ) {echo "selected=\"selected\"";} ?>>Mala direta postal</option>
									</select>
									<!-- fim - tipo -->
								<? } else{ ?>
									<?php if($row_rsmala_direta['tipo']=='em'){ ?>E-mail<? } ?>
									<?php if($row_rsmala_direta['tipo']=='et'){ ?>Etiqueta<? } ?>
									<?php if($row_rsmala_direta['tipo']=='po'){ ?>Mala direta postal<? } ?>
								<? } ?>
								</td>
							</tr>

							<tr>
								<td class="KT_th"><label for="titulo">Título:</label></td>
								<td>
								<? if ($totalRows_rsmala_direta == 0) { ?>
									<input type="text" name="titulo" id="titulo" value="<?php echo KT_escapeAttribute($row_rsmala_direta['titulo']); ?>" style="width: 680px;" />
								<? } else{ ?>
									<input type="text" name="titulo" id="titulo" value="<?php echo KT_escapeAttribute($row_rsmala_direta['titulo']); ?>" style="width: 680px;" readonly="readonly" />
								<? } ?>
								</td>
							</tr>
							
							<? if($totalRows_rsmala_direta == 0 or $totalRows_rsmala_direta_anexo > 0){ ?>
							<tr>
								<td class="KT_th"><label for="arquivo">Arquivo(s):</label></td>
								<td>
								<? if ($totalRows_rsmala_direta == 0) { ?>
									<div>Extensões permitidas: ['pdf', 'zip', 'rar', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'bmp', 'txt', 'jpg', 'jpeg', 'gif', 'png']</div>
									<div>Tamanho máximo por arquivo: 4mb</div>
									<input type="file" id="arquivo" name="arquivo[]" style="width: 680px;" multiple="multiple" />
									<div id="arquivo_retorno_html" class="arquivo_retorno_html"></div>
								<? } else{ ?>
								
									<? do { ?>
										<a href="../../arquivos/mala_direta/<? echo $row_rsmala_direta_anexo['arquivo']; ?>" target="_blank"><? echo $row_rsmala_direta_anexo['arquivo']; ?></a>
										<br>
									<? } while ($row_rsmala_direta_anexo = mysql_fetch_assoc($rsmala_direta_anexo)); ?>
									
								<? } ?>
								</td>
							</tr>
							<? } ?>
							
							<tr>
								<td class="KT_th"><label for="texto">Texto:</label></td>
								<td>
									<textarea name="texto" id="texto" cols="109" rows="15"><?php echo KT_escapeAttribute($row_rsmala_direta['texto']); ?></textarea>
								</td>
							</tr>
							</table>
                            
                            <div class="KT_bottombuttons">
                                <div>
									<? if ($totalRows_rsmala_direta == 0 and $totalRows_destinatario_listar > 0) { ?>
                                    <input type="hidden" name="MM_insert" value="form" />
                                    <input type="submit" id="submit" name="submit" value="Inserir" />
									<? } ?>
                                    <input type="button" onclick="window.history.back();" value="Voltar" />
                                </div>
                            </div>

                        </div>
                        <br class="clearfixplain" />
                    </div>
					<? } ?>
					<!-- fim - mala_direta -->
					
					</form>
                    
                </div>
                
                 
                </td>
                
            </tr>
        </table>
  	</div>
</div>
<!-- fim - corpo -->

<div class="rodape"><? require_once('../padrao_rodape.php'); ?></div>

</body>
</html>
<?php 
mysql_free_result($usuario); 
mysql_free_result($rsmala_direta); 
mysql_free_result($rsmala_direta_anexo); 
mysql_free_result($praca_listar);
mysql_free_result($destinatario_listar);
?>
