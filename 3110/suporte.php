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

// clientes (empresas)
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

// $where_empresa = "1=1 and da37.status17 <> 'C'";
$where_empresa = " 1=1 ";

if ($row_usuario['controle_suporte'] == "Y" or $row_usuario['suporte_operador_parceiro'] == "Y"){ 
	$where_empresa .= " and 1=1";	
} else { // se não é controle_suporte/suporte_operador_parceiro - então realiza filtragem por praça
	$where_empresa .= " and $sql_clientes_vendedor17";	
}

$query_empresa = "
SELECT 
da01.codigo1, da01.nome1, da01.cidade1, da01.atraso1,
da37.codigo17, da37.cliente17, da37.status17
FROM da01 
INNER JOIN da37 ON da01.codigo1 = da37.cliente17
WHERE $where_empresa and da01.sr_deleted <> 'T' and da37.sr_deleted <> 'T' 
ORDER BY da01.nome1 ASC";
$empresa = mysql_query($query_empresa, $conexao) or die(mysql_error());
$row_empresa = mysql_fetch_assoc($empresa);
$totalRows_empresa = mysql_num_rows($empresa);
// fim - clientes (empresas)

// filtro empresa ok
mysql_select_db($database_conexao, $conexao);
$query_filtro_empresas = "
SELECT da01.nome1, da01.codigo1
FROM da37 
INNER JOIN da01 ON da37.cliente17 = da01.codigo1 
WHERE $where_empresa and da01.sr_deleted <> 'T' and da37.sr_deleted <> 'T' 
ORDER BY da01.nome1";
$filtro_empresas = mysql_query($query_filtro_empresas, $conexao) or die(mysql_error());
$row_filtro_empresas = mysql_fetch_assoc($filtro_empresas);
$totalRows_filtro_empresas = mysql_num_rows($filtro_empresas);
// fim - filtro empresa

// filtro praca - ok
mysql_select_db($database_conexao, $conexao);
$query_filtro_praca = "SELECT praca FROM geral_tipo_praca ORDER BY praca ASC";
$filtro_praca = mysql_query($query_filtro_praca, $conexao) or die(mysql_error());
$row_filtro_praca = mysql_fetch_assoc($filtro_praca);
$totalRows_filtro_praca = mysql_num_rows($filtro_praca);
// fim - filtro praca

// filtro usuario_responsavel - ok
mysql_select_db($database_conexao, $conexao);
$query_filtro_usuario_responsavel = "SELECT IdUsuario, nome FROM usuarios ORDER BY nome ASC";
$filtro_usuario_responsavel = mysql_query($query_filtro_usuario_responsavel, $conexao) or die(mysql_error());
$row_filtro_usuario_responsavel = mysql_fetch_assoc($filtro_usuario_responsavel);
$totalRows_filtro_usuario_responsavel = mysql_num_rows($filtro_usuario_responsavel);	
// fim - filtro usuario_responsavel

// filtro usuario_envolvido - ok
mysql_select_db($database_conexao, $conexao);
$query_filtro_usuario_envolvido = "SELECT IdUsuario, nome FROM usuarios ORDER BY nome ASC";
$filtro_usuario_envolvido = mysql_query($query_filtro_usuario_envolvido, $conexao) or die(mysql_error());
$row_filtro_usuario_envolvido = mysql_fetch_assoc($filtro_usuario_envolvido);
$totalRows_filtro_usuario_envolvido = mysql_num_rows($filtro_usuario_envolvido);	
// fim - filtro usuario_envolvido

// filtro tipo_atendimento - ok
mysql_select_db($database_conexao, $conexao);
$query_filtro_tipo_atendimento = "SELECT descricao FROM suporte_tipo_atendimento ORDER BY IdTipoAtendimento ASC";
$filtro_tipo_atendimento = mysql_query($query_filtro_tipo_atendimento, $conexao) or die(mysql_error());
$row_filtro_tipo_atendimento = mysql_fetch_assoc($filtro_tipo_atendimento);
$totalRows_filtro_tipo_atendimento = mysql_num_rows($filtro_tipo_atendimento);
// fim - filtro tipo_atendimento

// filtro tipo_recomendacao - ok
mysql_select_db($database_conexao, $conexao);
$query_filtro_tipo_recomendacao = "SELECT titulo FROM suporte_tipo_recomendacao ORDER BY IdTipoRecomendacao ASC";
$filtro_tipo_recomendacao = mysql_query($query_filtro_tipo_recomendacao, $conexao) or die(mysql_error());
$row_filtro_tipo_recomendacao = mysql_fetch_assoc($filtro_tipo_recomendacao);
$totalRows_filtro_tipo_recomendacao = mysql_num_rows($filtro_tipo_recomendacao);
// fim - filtro tipo_recomendacao


// suporte
mysql_select_db($database_conexao, $conexao);

$where = "1=1";

// controlador de suporte --------------------------------------------------------------------------------
if ($row_usuario['controle_suporte'] == "Y"){ 

		$where .= " and ( 
						 suporte.id_usuario_responsavel = '".$row_usuario['IdUsuario']."' or 
						 suporte.id_usuario_envolvido = '".$row_usuario['IdUsuario']."' or 
						 suporte.encaminhamento_id = '".$row_usuario['IdUsuario']."' or 
						 
						 (suporte.tipo_suporte = 'c' and (suporte.inloco = 's' or suporte.inloco = 'n')) or 
						 suporte.tipo_suporte = 'p' or 
						 suporte.tipo_suporte = 'r'
						 ) ";

} 
// fim - controlador de suporte --------------------------------------------------------------------------

// operador-parceiro --------------------------------------------------------------------------------------
else if ($row_usuario['suporte_operador_parceiro'] == "Y"){

		$where .= " and (
						 suporte.id_usuario_responsavel = '".$row_usuario['IdUsuario']."' or 
						 suporte.id_usuario_envolvido = '".$row_usuario['IdUsuario']."' or 
						 suporte.encaminhamento_id = '".$row_usuario['IdUsuario']."' or 
						 
						 (suporte.tipo_suporte = 'c' and suporte.inloco = 's' and suporte.praca = '".$row_usuario['praca']."') or 
						 (suporte.tipo_suporte = 'c' and suporte.inloco = 's' and suporte.cobranca = 's' and suporte.status='pendente controlador de suporte') or 
						 
						 (suporte.tipo_suporte = 'c' and inloco = 'n' and suporte.solicita_suporte = 's') or 
						 (suporte.tipo_suporte = 'c' and inloco = 'n' and suporte.praca = '".$row_usuario['praca']."') or 
						 suporte.tipo_suporte = 'p' or 
						 suporte.tipo_suporte = 'r' or 
						 
						 suporte.status_flag = 'b'
						) ";

} 
// fim - operador-parceiro -------------------------------------------------------------------------------

// controlador de praça ----------------------------------------------------------------------------------------------------
else if ($row_usuario['controle_praca']=="Y"){

		$where .= " and ( 
						 suporte.praca = '".$row_usuario['praca']."' 
						 ) ";

} 
// fim - controlador de praça ----------------------------------------------------------------------------------------------

// usuário comum -----------------------------------------------------------------------------------------------------------
else {
	
		// suporte que o usuário logado prestou + suporte que o usuário logado recebeu
		$where .= " and (
						 suporte.id_usuario_responsavel = '".$row_usuario['IdUsuario']."' or 
						 suporte.id_usuario_envolvido = '".$row_usuario['IdUsuario']."' or 
						 suporte.encaminhamento_id = '".$row_usuario['IdUsuario']."' or 
						 
						 (suporte.solicita_visita = 's' and suporte.praca = '".$row_usuario['praca']."') or 
						 (suporte.tipo_suporte = 'r' and suporte.praca = '".$row_usuario['praca']."') 
						 
						 )";

}
// fim - usuário comum ------------------------------------------------------------------------------------------------------

$where_usuario_logado = $where; // para o filtro por id (elimina todos os outros filtros)

// se padrão ---------------------------------------------------------------------------------------------------------------------------------------------------------
if ( isset($_GET['padrao']) && ($_GET['padrao'] == "sim") ){

	if ($row_usuario['controle_suporte'] == "Y"){ 

		$where .= " and (
						 suporte.id_usuario_responsavel = '".$row_usuario['IdUsuario']."' or 
						 suporte.id_usuario_envolvido = '".$row_usuario['IdUsuario']."' or 
						 suporte.encaminhamento_id = '".$row_usuario['IdUsuario']."' or 
						 
						 (suporte.tipo_suporte = 'c' and suporte.inloco = 's' and suporte.cobranca = 's' and suporte.status='pendente controlador de suporte') or 
						 suporte.solicita_suporte = 's' or 
						 suporte.status_flag = 'b' or 
						 
						 ((tipo_suporte = 'c' and inloco = 's' and data_inicio <= '".date("Y-m-d")." 23:59:59') and suporte.praca = '".$row_usuario['praca']."') or 
						 ((tipo_suporte = 'c' and inloco = 'n') and suporte.praca = '".$row_usuario['praca']."') or 
						 (tipo_suporte = 'p') or 
						 (tipo_suporte = 'r')
						 ) ";
						 
	} else if ($row_usuario['suporte_operador_parceiro'] == "Y"){ 

		/* 
		$where .= " and (
						 suporte.id_usuario_responsavel = '".$row_usuario['IdUsuario']."' or 
						 suporte.id_usuario_envolvido = '".$row_usuario['IdUsuario']."' or 
						 suporte.encaminhamento_id = '".$row_usuario['IdUsuario']."' or 
						 
						 (suporte.tipo_suporte = 'c' and suporte.inloco = 's' and suporte.praca = '".$row_usuario['praca']."' and suporte.data_inicio <= '".date("Y-m-d")." 23:59:59') or 
						 (suporte.tipo_suporte = 'c' and suporte.inloco = 's' and suporte.cobranca = 's' and suporte.status='pendente controlador de suporte') or 
						 
						 (suporte.tipo_suporte = 'c' and inloco = 'n' and suporte.praca = '".$row_usuario['praca']."') or 
						 suporte.tipo_suporte = 'p' or 
						 suporte.tipo_suporte = 'r' or 
						 
						 suporte.status_flag = 'b' 
						 ) ";
		*/

	} else {

		$where .= " and (
						 (tipo_suporte = 'c' and inloco = 's' and data_inicio <= '".date("Y-m-d")." 23:59:59') or 
						 (tipo_suporte = 'c' and inloco = 'n') or 
						 (tipo_suporte = 'p') or 
						 (tipo_suporte = 'r')
						 )";

	}
	
	$_GET['data_inicio'] = date("d-m-Y");

}	
// fim - se padrão ---------------------------------------------------------------------------------------------------------------------------------------------------


// filtros -----------------------------------------------------------------------------------------------------------------------------------------------------------

// se existe filtro de título
if( (isset($_GET["titulo"])) && ($_GET['titulo'] !="") ) {

	$colname_suporte_titulo = GetSQLValueString("%" . $_GET["titulo"] . "%", "text");
	$where .= " and suporte.titulo LIKE ".$colname_suporte_titulo." ";
	$where_campos[] = "titulo";
} 
// fim - se existe filtro de título

// se existe filtro de empresa
if( (isset($_GET["empresa"])) && ($_GET['empresa'] !="") ) {
	$colname_suporte_empresa = GetSQLValueString($_GET["empresa"], "string");
	$where .= " and suporte.codigo_empresa = '$colname_suporte_empresa' ";
	$where_campos[] = "empresa";
}
// fim - se existe filtro de empresa

// se existe filtro de praca
if( (isset($_GET["praca"])) && ($_GET['praca'] !="") ) {
	$colname_suporte_praca = GetSQLValueString($_GET["praca"], "string");
	$where .= " and suporte.praca = '$colname_suporte_praca' "; 	
	$where_campos[] = "praca";
} 
// fim - se existe filtro de praca

// se existe filtro de inloco
if( (isset($_GET["inloco"])) && ($_GET['inloco'] !="") ) {
	$colname_suporte_inloco = $_GET['inloco'];
	$where .= " and suporte.inloco = '".$colname_suporte_inloco."' ";
	$where_campos[] = "inloco";
} 
// fim - se existe filtro de inloco

// se existe filtro de solicitante
if( (isset($_GET["solicitante"])) && ($_GET['solicitante'] !="") ) {
	$colname_suporte_solicitante = $_GET['solicitante'];
	$where .= " and suporte.solicitante = '".$colname_suporte_solicitante."' ";
	$where_campos[] = "solicitante";
} 
// fim - se existe filtro de solicitante

// se existe filtro de tipo_suporte
if( (isset($_GET["tipo_suporte"])) && ($_GET['tipo_suporte'] !="") ) {
	$colname_suporte_tipo_suporte = $_GET['tipo_suporte'];
	$where .= " and suporte.tipo_suporte = '".$colname_suporte_tipo_suporte."' ";
	$where_campos[] = "tipo_suporte";
} 
// fim - se existe filtro de tipo_suporte

// se existe filtro de usuario_responsavel
if( (isset($_GET["usuario_responsavel"])) && ($_GET['usuario_responsavel'] !="") ) {
	$colname_suporte_usuario_responsavel = $_GET['usuario_responsavel'];
	$where .= " and suporte.id_usuario_responsavel = '".$colname_suporte_usuario_responsavel."' ";
	$where_campos[] = "usuario_responsavel";
} 
// fim - se existe filtro de usuario_responsavel

// se existe filtro de usuario_envolvido
if( (isset($_GET["usuario_envolvido"])) && ($_GET['usuario_envolvido'] !="") ) {
	$colname_suporte_usuario_envolvido = $_GET['usuario_envolvido'];
	$where .= " and suporte.id_usuario_envolvido = '".$colname_suporte_usuario_envolvido."' ";
	$where_campos[] = "usuario_envolvido";
} 
// fim - se existe filtro de usuario_envolvido

// se existe filtro de tipo_atendimento
if( (isset($_GET["tipo_atendimento"])) && ($_GET['tipo_atendimento'] !="") ) {
	$colname_suporte_tipo_atendimento = $_GET['tipo_atendimento'];
	$where .= " and suporte.tipo_atendimento = '".$colname_suporte_tipo_atendimento."' ";
	$where_campos[] = "tipo_atendimento";
} 
// fim - se existe filtro de tipo_atendimento


// se existe filtro de data_suporte ( somente data final )
if( ((isset($_GET["data_suporte_fim"])) && ($_GET["data_suporte_fim"] != "")) && ($_GET["data_suporte"] == "") ) {

		// converter data em portugues para ingles
		if ( isset($_GET["data_suporte_fim"]) ) {
			$data_suporte_fim_data = substr($_GET["data_suporte_fim"],0,10);
			$data_suporte_fim_hora = " 23:59:59";
			$data_suporte_fim = implode("-",array_reverse(explode("-",$data_suporte_fim_data))).$data_suporte_fim_hora;
			$where_campos[] = "data_suporte_fim";
		}
		// converter data em portugues para ingles - fim
	
		$colname_suporte_data_suporte_fim = GetSQLValueString($data_suporte_fim, "string");
		$where .= " and suporte.data_suporte <= '".$colname_suporte_data_suporte_fim."' ";
}
// fim - se existe filtro de data_suporte ( somente data final )

// se existe filtro de data_suporte ( somente data inicial )
if( ((isset($_GET["data_suporte"])) && ($_GET["data_suporte"] != "")) && ($_GET["data_suporte_fim"] == "") ) {

		// converter data em portugues para ingles
		if ( isset($_GET["data_suporte"]) ) {
			$data_suporte_data = substr($_GET["data_suporte"],0,10);
			$data_suporte_hora = " 00:00:00";
			$data_suporte = implode("-",array_reverse(explode("-",$data_suporte_data))).$data_suporte_hora;
			$where_campos[] = "data_suporte";
		}
		// converter data em portugues para ingles - fim
	
		$colname_suporte_data_suporte = GetSQLValueString($data_suporte, "string");
		$where .= " and suporte.data_suporte >= '".$colname_suporte_data_suporte."' ";
}
// fim - se existe filtro de data_suporte ( somente data inicial )

// se existe filtro de data_suporte ( entre data inicial e data final )
if( ((isset($_GET["data_suporte"])) && ($_GET["data_suporte"] != "")) && ((isset($_GET["data_suporte_fim"])) && ($_GET["data_suporte_fim"] != "")) ) {

		// converter data em portugues para ingles
		if ( isset($_GET["data_suporte"]) ) {
			$data_suporte_data = substr($_GET["data_suporte"],0,10);
			$data_suporte_hora = " 00:00:00";
			$data_suporte = implode("-",array_reverse(explode("-",$data_suporte_data))).$data_suporte_hora;
			$where_campos[] = "data_suporte";
		}
		// converter data em portugues para ingles - fim

		// converter data em portugues para ingles
		if ( isset($_GET["data_suporte_fim"]) ) {
			$data_suporte_fim_data = substr($_GET["data_suporte_fim"],0,10);
			$data_suporte_fim_hora = " 23:59:59";
			$data_suporte_fim = implode("-",array_reverse(explode("-",$data_suporte_fim_data))).$data_suporte_fim_hora;
			$where_campos[] = "data_suporte_fim";
		}
		// converter data em portugues para ingles - fim
	
		$colname_suporte_data_suporte = GetSQLValueString($data_suporte, "string");
		$colname_suporte_data_suporte_fim = GetSQLValueString($data_suporte_fim, "string");

		$where .= " and suporte.data_suporte between '$colname_suporte_data_suporte' and '$colname_suporte_data_suporte_fim' "; 
}
// fim - se existe filtro de data_suporte ( entre data inicial e data final )

// filtro - agenda -------------------------------------------------------------------------------

// se existe filtro de data_fim ( somente data final )
if( ((isset($_GET["data_fim"])) && ($_GET["data_fim"] != "")) && (@$_GET["data_inicio"] == "") ) {

		// converter data em portugues para ingles
		if ( isset($_GET["data_fim"]) ) {
			$data_fim_data = substr($_GET["data_fim"],0,10);
			$data_fim_hora = " 00:00:00";
			$data_fim = implode("-",array_reverse(explode("-",$data_fim_data))).$data_fim_hora;
		}
		// converter data em portugues para ingles - fim
	
		$colname_suporte_data_fim = GetSQLValueString($data_fim, "string");
		$where .= " and data_inicio >= '".$colname_suporte_data_fim."' ";

}
// fim - se existe filtro de data_fim ( somente data final )


// se existe filtro de data_inicio ( somente data inicial )
if( ((isset($_GET["data_inicio"])) && ($_GET["data_inicio"] != "")) && (@$_GET["data_fim"] == "") ) {

		// converter data em portugues para ingles
		if ( isset($_GET["data_inicio"]) ) {
			$data_inicio_data = substr($_GET["data_inicio"],0,10);
			$data_inicio_hora = " 23:59:59";
			$data_inicio = implode("-",array_reverse(explode("-",$data_inicio_data))).$data_inicio_hora;
		}
		// converter data em portugues para ingles - fim
	
		$colname_suporte_data_inicio = GetSQLValueString($data_inicio, "string");
		$where .= " and (
						 (tipo_suporte = 'c' and inloco = 's' and data_inicio <= '".$colname_suporte_data_inicio."') or 
						 (tipo_suporte = 'c' and inloco = 'n') or 
						 (tipo_suporte = 'p') or 
						 (tipo_suporte = 'r')
						 )";
}
// fim - se existe filtro de data_inicio ( somente data inicial )


// se existe filtro de data_inicio/data_fim ( entre data inicial e data final )
if( ((isset($_GET["data_inicio"])) && ($_GET["data_inicio"] != "")) && ((isset($_GET["data_fim"])) && ($_GET["data_fim"] != "")) ) {

		// converter data em portugues para ingles
		if ( isset($_GET["data_inicio"]) ) {
			$data_inicio_data = substr($_GET["data_inicio"],0,10);
			$data_inicio_hora = " 00:00:00";
			$data_inicio = implode("-",array_reverse(explode("-",$data_inicio_data))).$data_inicio_hora;
		}
		// converter data em portugues para ingles - fim

		// converter data em portugues para ingles
		if ( isset($_GET["data_fim"]) ) {
			$data_fim_data = substr($_GET["data_fim"],0,10);
			$data_fim_hora = " 23:59:59";
			$data_fim = implode("-",array_reverse(explode("-",$data_fim_data))).$data_fim_hora;
		}
		// converter data em portugues para ingles - fim
	
		$colname_suporte_data_inicio = GetSQLValueString($data_inicio, "string");
		$colname_suporte_data_fim = GetSQLValueString($data_fim, "string");

		$where .= " and data_inicio between '$colname_suporte_data_inicio' and '$colname_suporte_data_fim' "; 

}
// fim - se existe filtro de data_inicio/data_fim ( entre data inicial e data final )

// fim - filtro - agenda -------------------------------------------------------------------------------


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
				$query_status .= sprintf(" status = '$status' $or");

		}
		$where .= sprintf($query_status)." ) ";
		$where_campos[] = "status";		
}
// fim - se existe filtro de status

// se existe filtro de status_questionamento
$contador_status_questionamento = 0;
$contador_status_questionamento_atual = 0;
if( (isset($_GET["status_questionamento"])) && ($_GET['status_questionamento'] !="") ) {

		// contar quantidade de situacões atual
		foreach($_GET["status_questionamento"] as $status_questionamento){
				$contador_status_questionamento = $contador_status_questionamento + 1;
		}
		// fim - contar quantidade de situacões atual

		//$query_status_questionamento=" and ( status_questionamento IS NULL or ";
											
		$query_status_questionamento=" and ( ";
		foreach($_GET["status_questionamento"] as $status_questionamento){
				$contador_status_questionamento_atual = $contador_status_questionamento_atual + 1; // verifica o contador atual
				$contador_total = $contador_status_questionamento - $contador_status_questionamento_atual; // calcula diferença de situações total - situação atual
				if($contador_total<>0){$or=" or ";}else{$or="";} // se não é a última, então insere OR
				
				if($status_questionamento == "nenhum"){ // caso seja marcada a opção de nenhum questionamento, então cria um where com o IS NULL
					$query_status_questionamento .= sprintf(" status_questionamento IS NULL $or");
				} else {
					$query_status_questionamento .= sprintf(" status_questionamento = '$status_questionamento' $or");
				}

		}
		$where .= sprintf($query_status_questionamento)." ) ";
		
}
// fim - se existe filtro de status_questionamento

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
				$query_situacao .= sprintf(" situacao = '$situacao' $or");

		}
		$where .= sprintf($query_situacao)." ) ";
		$where_campos[] = "situacao";		
}
// fim - se existe filtro de situacao

// se existe filtro de id do suporte
if( (isset($_GET["id"])) && ($_GET['id'] !="") ) {
	$colname_suporte_id = GetSQLValueString($_GET["id"], "int");
	$where = $where_usuario_logado." and id = '$colname_suporte_id' ";
	$where_campos[] = "id";
}
// fim - se existe filtro de id do suporte

// se existe filtro de estorno
if( (isset($_GET["estorno"])) && ($_GET['estorno'] !="") ) {
	$colname_suporte_estorno = $_GET['estorno'];
	$where .= " and suporte.estorno = '".$colname_suporte_estorno."' ";
	$where_campos[] = "estorno";
} 
// fim - se existe filtro de estorno

// se existe filtro de tipo_recomendacao
if( (isset($_GET["tipo_recomendacao"])) && ($_GET['tipo_recomendacao'] !="") ) {
	$colname_suporte_tipo_recomendacao = $_GET['tipo_recomendacao'];
	$where .= " and suporte.recomendacao = '".$colname_suporte_tipo_recomendacao."' ";
	$where_campos[] = "tipo_recomendacao";
} 
// fim - se existe filtro de tipo_recomendacao

// se existe filtro de cobranca
if( (isset($_GET["cobranca"])) && ($_GET['cobranca'] !="") ) {
	$colname_suporte_cobranca = $_GET['cobranca'];
	$where .= " and suporte.cobranca = '".$colname_suporte_cobranca."' ";
	$where_campos[] = "cobranca";
} 
// fim - se existe filtro de cobranca

// se existe filtro de cobranca_recebimento
if( (isset($_GET["cobranca_recebimento"])) && ($_GET['cobranca_recebimento'] !="") ) {
	
	$colname_suporte_cobranca_recebimento = $_GET['cobranca_recebimento'];
	
	if($colname_suporte_cobranca_recebimento == 'n'){
		$where .= " and ( suporte.cobranca_recebimento = '".$colname_suporte_cobranca_recebimento."' or suporte.cobranca_recebimento IS NULL ) "; // 'Recebido (AC)': Não OU 'vazio'
	} else {
		$where .= " and suporte.cobranca_recebimento = '".$colname_suporte_cobranca_recebimento."' ";
	}
	
	$where_campos[] = "cobranca_recebimento";

} 
// fim - se existe filtro de cobranca_recebimento

// se existe filtro de avaliacao_atendimento
$contador_avaliacao_atendimento = 0;
$contador_avaliacao_atendimento_atual = 0;
if( (isset($_GET["avaliacao_atendimento"])) && ($_GET['avaliacao_atendimento'] !="") ) {

		// contar quantidade de situacões atual
		foreach($_GET["avaliacao_atendimento"] as $avaliacao_atendimento){
				$contador_avaliacao_atendimento = $contador_avaliacao_atendimento + 1;
		}
		// fim - contar quantidade de situacões atual

		$query_avaliacao_atendimento=" and ( ";
		foreach($_GET["avaliacao_atendimento"] as $avaliacao_atendimento){
				$contador_avaliacao_atendimento_atual = $contador_avaliacao_atendimento_atual + 1; // verifica o contador atual
				$contador_total = $contador_avaliacao_atendimento - $contador_avaliacao_atendimento_atual; // calcula diferença de situações total - situação atual
				if($contador_total<>0){$or=" or ";}else{$or="";} // se não é a última, então insere OR
				$query_avaliacao_atendimento .= sprintf(" avaliacao_atendimento = '$avaliacao_atendimento' $or");

		}
		$where .= sprintf($query_avaliacao_atendimento)." ) ";
		$where_campos[] = "avaliacao_atendimento";		
}
// fim - se existe filtro de avaliacao_atendimento

// fim - filtros -----------------------------------------------------------------------------------------------------------------------------------------------------

$query_suporte = "
SELECT *,  
(SELECT nome FROM usuarios WHERE usuarios.IdUsuario = suporte.id_usuario_responsavel) as usuario_responsavel, 
(SELECT nome FROM usuarios WHERE usuarios.IdUsuario = suporte.id_usuario_envolvido) as usuario_envolvido 
FROM suporte 
WHERE $where 
ORDER BY 
case 
when prioridade LIKE '%Alta%' then 1 
when prioridade LIKE '%Média%' then 2 
when prioridade LIKE '%Baixa%' then 3 
when prioridade IS NULL then 4 
end ASC, contato DESC, id DESC";
$suporte = mysql_query($query_suporte, $conexao) or die(mysql_error());
$row_suporte = mysql_fetch_assoc($suporte);
$totalRows_suporte = mysql_num_rows($suporte);
// fim - suporte
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
#caixa_questionamentos {
	width: 500px;
	padding: 5px;
	position:absolute;
	z-index:100;
	float:left;
	
	border: 2px solid #06C;
	background-color: #FFF;
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
$(document).ready(function() {
	
	// mascara
	$('#data_suporte').mask('99-99-9999',{placeholder:" "});
	$('#data_suporte_fim').mask('99-99-9999',{placeholder:" "});
	
	$('#data_inicio').mask('99-99-9999',{placeholder:" "});
	$('#data_fim').mask('99-99-9999',{placeholder:" "});
	// mascara - fim
	
	// puxar informações clientes
	$('#cliente').change(function(event) { // quando alguma opção do select for escolhida								 
		$('#empresa_dados').html("<center><img src='imagens/loadingAnimation.gif'><br>Carregando...</center>");
		$.post("suporte_empresa_dados.php", { codigo17:$(this).val(), controle_suporte:"<? echo $row_usuario['controle_suporte']; ?>", suporte_operador_parceiro:"<? echo $row_usuario['suporte_operador_parceiro']; ?>" }, // requisita a página - inidica os campos a serem enviados
			function(data){
				$('#empresa_dados').html(data); // mostra a página que foi requisitada sem necessidade de refresh
		});
	}); 
	// fim - puxar informações clientes

	// Cliente buscar em select
	$("#cliente_buscar").bind('keyup',function(event){
		var field = $(this)[0];
		var select = $("#cliente")[0];      
		var found = false;
		for (var i = 0; i < select.options.length; i++) {
			if (select.options[i]['text'].toUpperCase().indexOf(field.value.toUpperCase()) == 0) {          
			found=true; break;
			}
		}
		// se achou algo
		if (found) {
			select.selectedIndex = i;

			if (event.keyCode == 9 || event.keyCode == 13) {
				// puxar informações clientes
				$('#empresa_dados').html("<center><img src='imagens/loadingAnimation.gif'><br>Carregando...</center>");
				$.post("suporte_empresa_dados.php", { codigo17:select.value, controle_suporte:"<? echo $row_usuario['controle_suporte']; ?>" }, // requisita a página - inidica os campos a serem enviados
					function(data){
						$('#empresa_dados').html(data); // mostra a página que foi requisitada sem necessidade de refresh
				});
				// fim - puxar informações clientes
			}
			
		}
		// fim - se achou algo			
		// se não achou ...
		else { 
			select.selectedIndex = -1;
		}
		// fim se não achou ...			
	});
	// fim - Cliente buscar em select
		
	// ocultar/exibir clientes
	$('#corpo_clientes').toggle();
	$('#cabecalho_clientes').click(function() {
		$('#corpo_clientes').toggle();
	});
	// fim - ocultar/exibir clientes

	// ocultar/exibir filtros
	$('#corpo_filtros').toggle();
	$('#cabecalho_filtros').click(function() {
		$('#corpo_filtros').toggle();
	});
	// fim - ocultar/exibir fitlros
		
	// ocultar/exibir suportes
	//$('#corpo_suportes').toggle();
	$('#cabecalho_suportes').click(function() {
		$('#corpo_suportes').toggle();
	});
	// fim - ocultar/exibir suportes
	
	// marcar todos
	$('#checkall_situacao').click(function () {
	$(this).parents('fieldset:eq(0)').find(':checkbox').attr('checked', this.checked);
	});
	
	$('#checkall_status').click(function () {
		$(this).parents('fieldset:eq(0)').find(':checkbox').attr('checked', this.checked);
	});
	
	$('#checkall_status_questionamento').click(function () {
	$(this).parents('fieldset:eq(0)').find(':checkbox').attr('checked', this.checked);
	});

	$('#checkall_avaliacao_atendimento').click(function () {
		$(this).parents('fieldset:eq(0)').find(':checkbox').attr('checked', this.checked);
	});
	// fim - marcar todos
	
	// questionamento ao passar o mouse em '?'
	$('#caixa_questionamentos').hide();
	$('.jqgrow').find('.ponto_interrogacao').mouseover(function(e) {												
																
		id_atual = $(this).attr('id');
		$('#caixa_questionamentos').html("<img src='imagens/loadingAnimation.gif'>");		
		$.post("suporte_questionamentos.php", 
			  {id:id_atual},
			  function(valor){
				  $("#caixa_questionamentos").html(valor).slideToggle("fast");
			  }
		)

	});
	
	$('.jqgrow').find('span').mouseout(function (e) {
		$("#caixa_questionamentos").hide();
	});
	// fim - questionamento ao passar o mouse em '?'
	
	$("#cliente").addClass("cliente_css");
	$("#cliente_buscar").addClass("cliente_buscar_css");
	
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
<title>Suporte</title>
</head>

<body>
<? // echo $where; ?>
<div class="div_solicitacao_linhas">
<table cellspacing="0" cellpadding="0" width="100%">
	<tr>
		<td style="text-align:left">
		Controle de suporte 
        <font color="#3399CC"> | </font>
        <a href="solicitacao.php?padrao=sim&<? echo $situacao_padrao; ?>" style="color: #D1E3F1">Controle de solicitação</a>
		<font color="#3399CC"> | </font>
        <a href="prospeccao.php?padrao=sim&<? echo $prospeccao_padrao; ?>" style="color: #D1E3F1">Controle de prospecção</a>
        <font color="#3399CC"> | </font>
        <a href="venda.php?padrao=sim&<? echo $venda_padrao; ?>" style="color: #D1E3F1">Controle de vendas</a>
		</td>

		<td style="text-align: right">
		&lt;&lt; <a href="index.php">Voltar</a> | 
		Usuário logado: <? echo $row_usuario['nome']; ?> |
		<a href="painel/padrao_sair.php">Sair</a>
        </td>
	</tr>
</table>
</div>

<div class="div_solicitacao_linhas2">
Clique sobre a opção desejada para visualizar mais informações.
</div>

<!-- clientes -->
<div class="div_solicitacao_linhas" id="cabecalho_clientes" style="cursor: pointer">
<table cellspacing="0" cellpadding="0" width="100%">
	<tr>
		<td style="text-align: left">
		Clientes (<? echo $totalRows_empresa; ?>) 
		</td>
        
		<td style="text-align: right">
		<div style="float: right; background-image:url(imagens/icone_filtro.png); width: 12px; height: 12px;"></div>
		</td>
	</tr>
</table>
</div>

<div class="div_solicitacao_linhas2" id="corpo_clientes">
<table width="100%" border="0" cellspacing="0" cellpadding="0">
    <tr>
        <td width="450px" valign="top">
        <input type="input" name="cliente_buscar" id="cliente_buscar" style="width: 438px;" value="Digite aqui o nome do cliente ..." onfocus="if(this.value=='Digite aqui o nome do cliente ...'){this.value=''};" onblur="if(this.value==''){this.value='Digite aqui o nome do cliente ...'};" />
        <br>
        <select name="cliente" id="cliente" size="20" style="width: 450px;">
        <?php
        do {  
        ?>
        <option value="<?php echo $row_empresa['codigo17']?>" style="
		<?
        // cores
        if($row_empresa['status17'] == "C"){ 

            echo " background-color:#FFF; "; // branco
            echo " color: #F00; "; // vermelho (letra)

		} else if($row_empresa['atraso1'] >= $row_parametros['suporte_cliente_atraso_vermelho']){ 

            echo " background-color: #F00; "; // vermelho
            echo " color:#FFF; "; // branco (letra)

        } else if($row_empresa['atraso1'] >= $row_parametros['suporte_cliente_atraso_amarelo']){ 

            echo " background-color: #FC0 "; // amarelo
            
		} else {

            echo " background-color:#FFF; "; // branco
            
        }
        // fim - cores
        ?>">
        <? echo utf8_encode($row_empresa['nome1']); // Codifica um string ISO-8859-1 para UTF-8 ?><? if($row_empresa['status17']=="B"){echo "***";} ?>
        </option>
        <?php
        } while ($row_empresa = mysql_fetch_assoc($empresa));
        $rows = mysql_num_rows($empresa);
        if($rows > 0) {
        mysql_data_seek($empresa, 0);
        $row_empresa = mysql_fetch_assoc($empresa);
        }
        ?>
        </select>
        </td>
        
        <td valign="top">
        <div id="empresa_dados"></div>
        </td>
    </tr>
</table>

    <div style="margin-top: 5px; padding-bottom: 5px; border: 1px solid #CCC; padding: 5px;">
        Atraso de pagamento após o prazo de carência ( 
        <font color="#FFCC00"><strong>Amarelo</strong></font>: Entre 6 e 14 dias / 
        <font color="#FF0000"><strong>Vermelho</strong></font>: Após 15 dias ) | *** manutenção bloqueada
    </div>    
</div>
<!-- fim - clientes -->


<!-- filtros -->
<div class="div_solicitacao_linhas" id="cabecalho_filtros" style="cursor: pointer">
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

<div style="border: 1px solid #c5dbec; margin-bottom: 5px;" id="corpo_filtros">
<form name="buscar" action="suporte.php" method="GET">

        <div class="div_filtros">
        <table cellspacing="0" cellpadding="0" width="100%">
            <tr>
                <td style="text-align:left" width="560px">
                <span class="label_solicitacao">Título:</span>
                <input name="titulo" type="text" id="titulo" value="<? if ( isset($_GET['titulo']) ) { echo $_GET['titulo']; } ?>" style="width: 500px" /> 
                </td>

                <td style="text-align: right">
                <span class="label_solicitacao">Número do suporte:</span>
				<input name="id" type="text" id="id" value="<? if ( isset($_GET['id']) ) { echo $_GET['id']; } ?>" size="20" /> 
                </td>
          </tr>
        </table>
        </div>

        <div class="div_filtros2">
        <table cellspacing="0" cellpadding="0" width="100%">
            <tr>
                <td style="text-align:left" width="560px" >
                <span class="label_solicitacao">Empresa:</span>
                <select name="empresa" style="width: 490px">
                <option value="">Escolha ...</option>
                <?php do { ?>
                <option value="<?php echo $row_filtro_empresas['codigo1']?>"
                <?php if ( (isset($_GET['empresa'])) and (!(strcmp($row_filtro_empresas['codigo1'], $_GET['empresa']))) ) {echo "selected=\"selected\"";} ?>
                >
                <?php echo utf8_encode($row_filtro_empresas['nome1']); ?>
                </option>
                <?php
                } while ($row_filtro_empresas = mysql_fetch_assoc($filtro_empresas));
                $rows = mysql_num_rows($filtro_empresas);
                if($rows > 0) {
                mysql_data_seek($filtro_empresas, 0);
                $row_filtro_empresas = mysql_fetch_assoc($filtro_empresas);
                }
                ?>
                </select>
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
        
              	<td style="text-align:right" width="150px">
                <span class="label_solicitacao">In-loco: </span>
                <select name="inloco">
                <option value=""<?php if (!(strcmp("", isset($_GET['inloco'])))) {echo "selected=\"selected\"";} ?>>Escolha ...</option>
                <option value="n"<?php if ( (isset($_GET['inloco'])) and (!(strcmp("n", $_GET['inloco']))) ) {echo "selected=\"selected\"";} ?>>Não</option>
                <option value="s"<?php if ( (isset($_GET['inloco'])) and (!(strcmp("s", $_GET['inloco']))) ) {echo "selected=\"selected\"";} ?>>Sim</option>
                </select>
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
                <input  name="situacao[]" type="checkbox" value="criada"
                    <?
                    // verificar se foi selecionada
                    if(isset($_GET['situacao'])){
                        foreach($_GET["situacao"] as $situacao){
                            if($situacao=="criada"){
                                echo "checked=\"checked\"";
                            }
                        }
                    }
                    // verificar se foi selecionada
                    ?>
                />criada
                
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
                
                <input name="situacao[]" type="checkbox" value="em validação" 
                    <?
                    // verificar se foi selecionada
                    if(isset($_GET['situacao'])){
                        foreach($_GET["situacao"] as $situacao){
                            if($situacao=="em validação"){
                                echo "checked=\"checked\"";
                            }
                        }
                    }
                    // verificar se foi selecionada
                    ?>
                />em validação
                
                <input name="situacao[]" type="checkbox" value="solicitado suporte" 
                    <?
                    // verificar se foi selecionada
                    if(isset($_GET['situacao'])){
                        foreach($_GET["situacao"] as $situacao){
                            if($situacao=="solicitado suporte"){
                                echo "checked=\"checked\"";
                            }
                        }
                    }
                    // verificar se foi selecionada
                    ?>
                />solicitado suporte

                <input name="situacao[]" type="checkbox" value="solicitado visita" 
                    <?
                    // verificar se foi selecionada
                    if(isset($_GET['situacao'])){
                        foreach($_GET["situacao"] as $situacao){
                            if($situacao=="solicitado visita"){
                                echo "checked=\"checked\"";
                            }
                        }
                    }
                    // verificar se foi selecionada
                    ?>
                />solicitado visita
                
                <input name="situacao[]" type="checkbox" value="encaminhado para solicitação" 
                    <?
                    // verificar se foi selecionada
                    if(isset($_GET['situacao'])){
                        foreach($_GET["situacao"] as $situacao){
                            if($situacao=="encaminhado para solicitação"){
                                echo "checked=\"checked\"";
                            }
                        }
                    }
                    // verificar se foi selecionada
                    ?>
                />encaminhado para solicitação
                
                
                                
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
                <td style="text-align:left" width="560px" >
                <span class="label_solicitacao">Solicitante: </span>
				<input name="solicitante" type="text" id="solicitante" value="<? if ( isset($_GET['solicitante']) ) { echo $_GET['solicitante']; } ?>" style="width: 470px" /> 
                </td>
                
              	<td style="text-align:right">
                <span class="label_solicitacao">Tipo de suporte: </span>
                <select name="tipo_suporte">
                <option value=""<?php if (!(strcmp("", isset($_GET['tipo_suporte'])))) {echo "selected=\"selected\"";} ?>>Escolha ...</option>
                <option value="c"<?php if ( (isset($_GET['tipo_suporte'])) and (!(strcmp("c", $_GET['tipo_suporte']))) ) {echo "selected=\"selected\"";} ?>>Cliente</option>
                <option value="p"<?php if ( (isset($_GET['tipo_suporte'])) and (!(strcmp("p", $_GET['tipo_suporte']))) ) {echo "selected=\"selected\"";} ?>>Parceiro</option>
                <option value="r"<?php if ( (isset($_GET['tipo_suporte'])) and (!(strcmp("r", $_GET['tipo_suporte']))) ) {echo "selected=\"selected\"";} ?>>Reclamação</option>
                </select>
                </td>
          </tr>
        </table>
        </div>
        
        <div class="div_filtros">
        <table cellspacing="0" cellpadding="0" width="100%">
        <tr>
            <td style="text-align:left" width="560px" >
                <span class="label_solicitacao">Responsável pelo suporte: </span>
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

            <td style="text-align:left" width="560px" >
                <span class="label_solicitacao">Envolvido: </span>
                <select name="usuario_envolvido" style="width: 380px">
                    <option value=""
                    <?php if (!(strcmp("", isset($_GET['filtro_usuario_envolvido'])))) {echo "selected=\"selected\"";} ?>>Escolha ...</option>
                    <?php do {  ?>
                    <option value="<?php echo $row_filtro_usuario_envolvido['IdUsuario']; ?>"
                    <?php if ( (isset($_GET['usuario_envolvido'])) and (!(strcmp($row_filtro_usuario_envolvido['IdUsuario'], $_GET['usuario_envolvido']))) ) {echo "selected=\"selected\"";} ?>
                    >
                    <?php echo utf8_encode($row_filtro_usuario_envolvido['nome']); ?>
                    </option>
                    <?php
                    } while ($row_filtro_usuario_envolvido = mysql_fetch_assoc($filtro_usuario_envolvido));
                    $rows = mysql_num_rows($filtro_usuario_envolvido);
                    if($rows > 0) {
                    mysql_data_seek($filtro_usuario_envolvido, 0);
                    $row_filtro_usuario_envolvido = mysql_fetch_assoc($filtro_usuario_envolvido);
                    }
                    ?>
                </select>
            </td>
            
            <td style="text-align:right">
                <span class="label_solicitacao">Tipo de atendimento: </span>
                <select name="tipo_atendimento">
                    <option value=""
                    <?php if (!(strcmp("", isset($_GET['filtro_tipo_atendimento'])))) {echo "selected=\"selected\"";} ?>>Escolha ...</option>
                    <?php do {  ?>
                    <option value="<?php echo utf8_encode($row_filtro_tipo_atendimento['descricao']); ?>"
                    <?php if ( (isset($_GET['tipo_atendimento'])) and (!(strcmp($row_filtro_tipo_atendimento['descricao'], $_GET['tipo_atendimento']))) ) {echo "selected=\"selected\"";} ?>
                    >
                    <?php echo utf8_encode($row_filtro_tipo_atendimento['descricao']); ?>
                    </option>
                    <?php
                    } while ($row_filtro_tipo_atendimento = mysql_fetch_assoc($filtro_tipo_atendimento));
                    $rows = mysql_num_rows($filtro_tipo_atendimento);
                    if($rows > 0) {
                    mysql_data_seek($filtro_tipo_atendimento, 0);
                    $row_filtro_tipo_atendimento = mysql_fetch_assoc($filtro_tipo_atendimento);
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
        <input name="data_suporte" id="data_suporte" type="text" value="<? 
        if ( isset($_GET['data_suporte']) ){ echo $_GET['data_suporte']; }
        ?>" />
        </td>
        
        <td style="text-align:right" class="div_filtros_suportes_corpo_td">
        <span class="label_solicitacao">Data criação (final): </span>
        <input name="data_suporte_fim" id="data_suporte_fim" type="text" value="<? 
        if ( isset($_GET['data_suporte_fim']) ){ echo $_GET['data_suporte_fim']; }
        ?>" />
        </td>
        </tr>
        </table>
        </div>
                
        <div class="div_filtros">
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
                        
                        <input  name="status[]" type="checkbox" class="checkbox" value="pendente usuario envolvido"
                            <?
                            // verificar se foi selecionada
                            if(isset($_GET['status'])){
                                foreach($_GET["status"] as $status){
                                    if($status=="pendente usuario envolvido"){
                                        echo "checked=\"checked\"";
                                    }
                                }
                            }
                            // verificar se foi selecionada
                            ?>
                        />pendente usuario envolvido
                        
                        <br>
                        
                        <input  name="status[]" type="checkbox" class="checkbox" value="pendente controlador de suporte"
                            <?
                            // verificar se foi selecionada
                            if(isset($_GET['status'])){
                                foreach($_GET["status"] as $status){
                                    if($status=="pendente controlador de suporte"){
                                        echo "checked=\"checked\"";
                                    }
                                }
                            }
                            // verificar se foi selecionada
                            ?>
                        />pendente controlador de suporte
                        
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
                    
                        <input  name="status[]" type="checkbox" class="checkbox" value="encaminhada para usuario envolvido"
                        <?
                        // verificar se foi selecionada
                        if(isset($_GET['status'])){
                            foreach($_GET["status"] as $status){
                                if($status=="encaminhada para usuario envolvido"){
                                    echo "checked=\"checked\"";
                                }
                            }
                        }
                        // verificar se foi selecionada
                        ?>
                    />encaminhada para usuario envolvido
                    
                    <br>
                        
                        <input type="checkbox" class="checkbox" id="checkall_status"  name="checkall_status" />Marcar todos
                        
                        </td>
                    </tr>
                    </table>
				</fieldset>
                </td>
                
                <td style="text-align: right" valign="top">
                <span class="label_solicitacao">Estorno:</span>
                <br>
                <input  name="estorno" type="checkbox" class="checkbox" value="s" <? if(isset($_GET['estorno'])=="s"){echo "checked=\"checked\"";} ?>/> 
                Suporte já estornado
                
                <br><br>
                
                <span class="label_solicitacao">Recomendação: </span>
                <br>
                <select name="tipo_recomendacao">
                <option value=""
                <?php if (!(strcmp("", isset($_GET['filtro_tipo_recomendacao'])))) {echo "selected=\"selected\"";} ?>>Escolha ...</option>
                <?php do {  ?>
                <option value="<?php echo $row_filtro_tipo_recomendacao['titulo']; ?>"
                <?php if ( (isset($_GET['tipo_recomendacao'])) and (!(strcmp($row_filtro_tipo_recomendacao['titulo'], $_GET['tipo_recomendacao']))) ) {echo "selected=\"selected\"";} ?>
                >
                <?php echo $row_filtro_tipo_recomendacao['titulo']; ?>
                </option>
                <?php
                } while ($row_filtro_tipo_recomendacao = mysql_fetch_assoc($filtro_tipo_recomendacao));
                $rows = mysql_num_rows($filtro_tipo_recomendacao);
                if($rows > 0) {
                mysql_data_seek($filtro_tipo_recomendacao, 0);
                $row_filtro_tipo_recomendacao = mysql_fetch_assoc($filtro_tipo_recomendacao);
                }
                ?>
                </select>

                <br><br>
                
				<table width="100%" border="0" cellspacing="0" cellpadding="0">
                    <tr>
                        <td valign="top">
                        <span class="label_solicitacao">Auxílio Cobrança:</span>
                		<br>
		                <input  name="cobranca" type="checkbox" class="cobranca" value="s" <? if(isset($_GET['cobranca'])=="s"){echo "checked=\"checked\"";} ?>/> 
                        </td>
                        
                        <td width="100" valign="top">
                        <span class="label_solicitacao">Recebido (AC):</span>
                		<br>
                        <select name="cobranca_recebimento">
                        <option value=""<?php if (!(strcmp("", isset($_GET['cobranca_recebimento'])))) {echo "selected=\"selected\"";} ?>>Escolha ...</option>
                        <option value="n"<?php if ( (isset($_GET['cobranca_recebimento'])) and (!(strcmp("n", $_GET['cobranca_recebimento']))) ) {echo "selected=\"selected\"";} ?>>Não</option>
                        <option value="s"<?php if ( (isset($_GET['cobranca_recebimento'])) and (!(strcmp("s", $_GET['cobranca_recebimento']))) ) {echo "selected=\"selected\"";} ?>>Sim</option>
                        </select>
                        </td>
                    </tr>
                </table>
                
                </td>
                
          </tr>
        </table>
        </div>
          
        <div class="div_filtros2">
        <table cellspacing="0" cellpadding="0" width="100%">
            <tr>
                <td style="text-align:left" valign="top">
                <fieldset style="border: 0px;">
                    <span class="label_solicitacao">Avaliação do atendimento:</span>
                        
                    <input  name="avaliacao_atendimento[]" type="checkbox" class="checkbox" value="Execelente"
                        <?
                        // verificar se foi selecionada
                        if(isset($_GET['avaliacao_atendimento'])){
                            foreach($_GET["avaliacao_atendimento"] as $avaliacao_atendimento){
                                if($avaliacao_atendimento=="Execelente"){
                                    echo "checked=\"checked\"";
                                }
                            }
                        }
                        // verificar se foi selecionada
                        ?>
                    />Execelente
                                            
                    <input  name="avaliacao_atendimento[]" type="checkbox" class="checkbox" value="Bom"
                        <?
                        // verificar se foi selecionada
                        if(isset($_GET['avaliacao_atendimento'])){
                            foreach($_GET["avaliacao_atendimento"] as $avaliacao_atendimento){
                                if($avaliacao_atendimento=="Bom"){
                                    echo "checked=\"checked\"";
                                }
                            }
                        }
                        // verificar se foi selecionada
                        ?>
                    />Bom
                    
                    <input  name="avaliacao_atendimento[]" type="checkbox" class="checkbox" value="Regular"
                        <?
                        // verificar se foi selecionada
                        if(isset($_GET['avaliacao_atendimento'])){
                            foreach($_GET["avaliacao_atendimento"] as $avaliacao_atendimento){
                                if($avaliacao_atendimento=="Regular"){
                                    echo "checked=\"checked\"";
                                }
                            }
                        }
                        // verificar se foi selecionada
                        ?>
                    />Regular
                    
                    <input  name="avaliacao_atendimento[]" type="checkbox" class="checkbox" value="Ruim"
                        <?
                        // verificar se foi selecionada
                        if(isset($_GET['avaliacao_atendimento'])){
                            foreach($_GET["avaliacao_atendimento"] as $avaliacao_atendimento){
                                if($avaliacao_atendimento=="Ruim"){
                                    echo "checked=\"checked\"";
                                }
                            }
                        }
                        // verificar se foi selecionada
                        ?>
                    />Ruim
                    
                    <input  name="avaliacao_atendimento[]" type="checkbox" class="checkbox" value="Péssimo"
                        <?
                        // verificar se foi selecionada
                        if(isset($_GET['avaliacao_atendimento'])){
                            foreach($_GET["avaliacao_atendimento"] as $avaliacao_atendimento){
                                if($avaliacao_atendimento=="Péssimo"){
                                    echo "checked=\"checked\"";
                                }
                            }
                        }
                        // verificar se foi selecionada
                        ?>
                    />Péssimo
                    
                    <input type="checkbox" class="checkbox" id="checkall_avaliacao_atendimento"  name="checkall_avaliacao_atendimento" />Marcar todos
                    
				</fieldset>
                </td>
          </tr>
        </table>
        </div>
            
        <div class="div_filtros">
        <table cellspacing="0" cellpadding="0" width="100%">
        <tr>
    
        <td style="text-align:left">
                <fieldset style="border: 0px;">
                <span class="label_solicitacao">Questionamento para:</span>
                
                <input  name="status_questionamento[]" type="checkbox" value="nenhum"
                    <?
                    // verificar se foi selecionada
                    if(isset($_GET['status_questionamento'])){
                        foreach($_GET["status_questionamento"] as $status_questionamento){
                            if($status_questionamento=="nenhum"){
                                echo "checked=\"checked\"";
                            }
                        }
                    }
                    // verificar se foi selecionada
                    ?>
                />nenhum
                
                <input  name="status_questionamento[]" type="checkbox" value="operador"
                    <?
                    // verificar se foi selecionada
                    if(isset($_GET['status_questionamento'])){
                        foreach($_GET["status_questionamento"] as $status_questionamento){
                            if($status_questionamento=="operador"){
                                echo "checked=\"checked\"";
                            }
                        }
                    }
                    // verificar se foi selecionada
                    ?>
                />operador
                
                <input  name="status_questionamento[]" type="checkbox" value="usuário responsavel"
                    <?
                    // verificar se foi selecionada
                    if(isset($_GET['status_questionamento'])){
                        foreach($_GET["status_questionamento"] as $status_questionamento){
                            if($status_questionamento=="usuário responsavel"){
                                echo "checked=\"checked\"";
                            }
                        }
                    }
                    // verificar se foi selecionada
                    ?>
                />usuário responsavel
                
                <input  name="status_questionamento[]" type="checkbox" value="usuário envolvido"
                    <?
                    // verificar se foi selecionada
                    if(isset($_GET['status_questionamento'])){
                        foreach($_GET["status_questionamento"] as $status_questionamento){
                            if($status_questionamento=="usuário envolvido"){
                                echo "checked=\"checked\"";
                            }
                        }
                    }
                    // verificar se foi selecionada
                    ?>
                />usuário envolvido
             
                <input type="checkbox" id="checkall_status_questionamento"  name="checkall_status_questionamento" />Marcar todos
                </fieldset>
        </td>
    
        </tr>
        </table>
        </div>
        
        <div class="div_filtros2">
        <table cellspacing="0" cellpadding="0" width="100%">
        <tr>
        <td style="text-align:left">
        <span class="label_solicitacao">AGENDA - Data (inicial): </span>
        <input name="data_inicio" id="data_inicio" type="text" value="<? 
        if ( isset($_GET['data_inicio']) ){ echo $_GET['data_inicio']; }
        ?>" />
        </td>
        
        <td style="text-align:right" class="div_filtros_prospeccaos_corpo_td">
        <span class="label_solicitacao">AGENDA - Data (final): </span>
        <input name="data_fim" id="data_fim" type="text" value="<? 
        if ( isset($_GET['data_fim']) ){ echo $_GET['data_fim']; }
        ?>" />
        </td>
        </tr>
        </table>
        </div>
            
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
            
</form>
</div>
<!-- fim - filtros -->


<!-- Suporte -->
<? if($totalRows_suporte > 0){ ?>
<div class="div_solicitacao_linhas" id="cabecalho_suportes" style="cursor: pointer">
<table cellspacing="0" cellpadding="0" width="100%">
	<tr>
		<td style="text-align:left">
		Suportes (<? echo $totalRows_suporte; ?>)
		</td>
        
		<td style="text-align: right">
		<div style="float: right; background-image:url(imagens/icone_filtro.png); width: 12px; height: 12px;"></div>
		</td>
	</tr>
</table>
</div>

<div id="caixa_questionamentos"></div>

<div id="corpo_suportes" style="cursor: pointer">
<table id="suportes"></table>
<div id="navegacao"></div>
<script type="text/javascript">
var dados = [		 
<?php do { ?>

<?
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
?>

<?
// (Para) tipo_suporte ----------------------------------------------------------------------------------------------------------------
$cor_tipo_suporte_css = "cor_black";
if ($tipo_suporte_inloco == "cn"){
	
	if($row_suporte['solicita_suporte']=="s" and $row_suporte['data_solicita_suporte_aceita_recusa']==""){
		$cor_tipo_suporte_css = "cor_blue";
	}

} else if ($tipo_suporte_inloco == "p"){
	
	$cor_tipo_suporte_css = "cor_green";

} else if ($tipo_suporte_inloco == "r"){
	
	$cor_tipo_suporte_css = "cor_orange";

}
// (Para) tipo_suporte ----------------------------------------------------------------------------------------------------------------
?>

<?
// status ------------------------------------------------------------------------------------------------------------------------------------
$cor_css = "cor_black";

// cs
if ($tipo_suporte_inloco=="cs" and ($row_suporte['previsao_geral']=="" or $row_suporte['previsao_geral']=="0000-00-00 00:00:00")){

	if($row_suporte['data_fim']!="" and $row_suporte['data_fim']!="0000-00-00 00:00:00"){
		
		$data_fim = funcaoAcrescentaDiasNaoUteis($row_suporte['data_fim']); // chama a função que altera a data para o próximo dia útil
		$data1 = strtotime($data_fim); // converte em segundos
		
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
	
	}

}
// fim - cs

// geral
if($row_suporte['previsao_geral']!="" and $row_suporte['previsao_geral']!="0000-00-00 00:00:00"){

	$previsao_geral = funcaoAcrescentaDiasNaoUteis($row_suporte['previsao_geral']); // chama a função que altera a data para o próximo dia útil
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
	
}
// fim - geral
// fim - status ------------------------------------------------------------------------------------------------------------------------------
?>

{
	id:"<?php echo $row_suporte['id']; ?>",
	inloco:"<?php if($row_suporte['inloco']=="s"){echo"Sim";} if($row_suporte['inloco']=="n"){echo"Não";} if($row_suporte['visita_bonus']=="s"){echo" (B)";} ?>",
	titulo:"(<?php echo $row_suporte['praca']; ?>) - <?php echo $row_suporte['titulo']; ?>",
	tipo_suporte:"<? echo "<div class='$cor_tipo_suporte_css' style='font-weight: bold;'>"; ?><?php if($row_suporte['tipo_suporte']=="c"){echo"CLI";} if($row_suporte['tipo_suporte']=="p"){echo"PAR";} if($row_suporte['tipo_suporte']=="r"){echo"REC";} ?><? echo "</div>"; ?>",
	empresa:"<?php echo utf8_encode($row_suporte['empresa']); ?>",
	usuario_responsavel:"<?php echo $row_suporte['usuario_responsavel']; ?>",
	usuario_envolvido:"<?php echo $row_suporte['usuario_envolvido']; ?>",	
	data_suporte:"<?php echo $row_suporte['data_suporte']; ?>",
    dt_alteracao:"<?

            // alterado a ...
            // ($row_suporte['previsao_geral'] != "") and 
            if ( ($row_suporte['situacao']!="solucionada" and $row_suporte['situacao']!="cancelada") ){
                // select solicitacao_descricoes
                $colname_ultima_postagem_validacao = "-1";
                if (isset($row_suporte['id'])) {
                $colname_ultima_postagem_validacao = $row_suporte['id'];
                }
                mysql_select_db($database_conexao, $conexao);
                $query_ultima_postagem_validacao = sprintf("
                SELECT data  
                    FROM suporte_descricoes 
                WHERE 
                    id_suporte = %s and tipo_postagem <> 'Questionamento'
                ORDER BY id DESC
                ", $colname_ultima_postagem_validacao);
                $ultima_postagem_validacao = mysql_query($query_ultima_postagem_validacao, $conexao) or die(mysql_error());
                $row_ultima_postagem_validacao = mysql_fetch_assoc($ultima_postagem_validacao);
                $totalRows_ultima_postagem_validacao = mysql_num_rows($ultima_postagem_validacao);
                // fim - select solicitacao_descricoes
    
                $data_ini = strtotime($row_ultima_postagem_validacao['data']);
                $data_final = strtotime(date("Y-m-d H:i:s"));
                
                $nDias   = ($data_final - $data_ini) / (3600*24);  // dias
                $nHoras = (($data_final - $data_ini) % (3600*24)) / 3600; // horas
                $nMinutos = (	(($data_final - $data_ini) % (3600*24)) % 3600	) / 60; // minutos
                
                echo $alteracao = sprintf('%02dd %02dh %02dm', $nDias, $nHoras , $nMinutos);
                mysql_free_result($ultima_postagem_validacao);
            }
            // fim - alterado a ...

    ?>",
	status:"<? echo "<div class='$cor_css'>"; ?><?php 

    if($row_suporte['status']==""){echo "&nbsp;";}
	
	if($row_suporte['status_questionamento']!=""){echo "<strong><font color='red'><span class='ponto_interrogacao' id='".$row_suporte['id']."'>?</span></font></strong> ";}

	if($row_suporte['status']=="pendente controlador de suporte"){echo "<span title='pendente controlador de suporte'>PenSup</span>";}
    if($row_suporte['status']=="pendente operador"){echo "<span title='pendente operador'>PenOpe</span>";}
    if($row_suporte['status']=="pendente usuario responsavel"){echo "<span title='pendente usuario responsavel'>PenRes</span>";}
    if($row_suporte['status']=="pendente usuario envolvido"){echo "<span title='pendente usuario envolvido'>PenEnv</span>";}

    if($row_suporte['status']=="encaminhada para operador"){echo "<span title='encaminhada para operador'>EncOpe</span>";}
    if($row_suporte['status']=="encaminhada para usuario responsavel"){echo "<span title='encaminhada para usuario responsavel'>EncRes</span>";}
    if($row_suporte['status']=="encaminhada para usuario envolvido"){echo "<span title='encaminhada para usuario envolvido'>EncEnv</span>";}
    
    if($row_suporte['status']=="devolvida para usuario responsavel"){echo "<span title='devolvida para usuario responsavel'>DevRes</span>";}

    ?><? echo "</div>"; ?>",
	prioridade:"<?php echo $row_suporte['prioridade']; ?>",
	situacao:"<?php echo $row_suporte['situacao']; ?>",
	visualizar:"<? echo "<a href='suporte_editar_upload.php?id_suporte=".$row_suporte['id']."&situacao=&acao=Arquivos em anexo&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=".$suporte_editar_tabela_height."&width=".$suporte_editar_tabela_width."' class='thickbox'><img src='imagens/anexo.png' border='0' /></a> <a href='suporte_editar.php?id_suporte=".$row_suporte['id']."&padrao=sim'><img src='imagens/visualizar.png' border='0' /></a>"; ?>"
},
<?php } while ($row_suporte = mysql_fetch_assoc($suporte)); ?>
];
jQuery('#suportes').jqGrid({
	data:dados,
	datatype: 'local',
	colNames:['Núm','In-loco','Título','Para','Cliente','Responsável','Envolvido','Início','Alterado a','Status','Prior.','Situação',''],
	colModel :[ 
		{name:'id', index:'id', width:25, sorttype: 'integer'}, 
		{name:'inloco', index:'inloco', width:25, align:'center'}, 
		{name:'titulo', index:'titulo'}, 
		{name:'tipo_suporte', index:'tipo_suporte', width:25, align:'center'}, 
		{name:'empresa', index:'empresa', width:75, align:'left'}, 
		{name:'usuario_responsavel', index:'usuario_responsavel', width:45, align:'left'}, 
		{name:'usuario_envolvido', index:'usuario_envolvido', width:45, align:'left'}, 	
		{name:'data_suporte', index:'data_suporte', width:35, formatter:'date', formatter:'date', formatoptions: { srcformat: "ISO8601Long", newformat: "d-m-Y" }, align:'center' },
        {name:'dt_alteracao', index:'dt_alteracao', width:55, align:'center', sorttype: 'date'},
		{name:'status', index:'status', width:40, align:'center', title:false},
		{name:'prioridade', index:'prioridade', width:35, align:'center', title:false},
		{name:'situacao', index:'situacao', width:55, align:'center'},
		{name:'visualizar', index:'visualizar', width:30, align:'center'} 
	],
	rowNum:20,
	rowList:[2,5,10,20,30,40,50,100,999999],
				loadComplete: function() {
					$("option[value=999999]").text('Todos');
				},
	pager: '#navegacao',
	//sortname: 'id',
	viewrecords: true,
	//sortorder: 'desc',
	toppager: true, // aparecer a barra de navegação também no topo
	// caption:"Suportes", barra no topo
	autowidth: true,
	height: "100%",

	ondblClickRow: function(id){
		top.location.href="suporte_editar.php?id_suporte="+id+"&padrao=sim";
	}		
});
</script>
</div>
<? } else { ?>
<div class="div_solicitacao_linhas4">
Nenhum suporte encontrado na filtragem atual.
</div>
<? } ?>
<!-- fim - Suporte -->


<!-- Legenda -->
<?
// se é 'controlador de suporte' ou 'administrador_site' ou 'controle_praca'
if($row_usuario['controle_suporte']=="Y" or $row_usuario['administrador_site']=="Y" or $row_usuario['controle_praca']=="Y"){
?>
    <div style="margin-top: 5px; padding-bottom: 5px; border: 1px solid #CCC; padding: 5px;">
    	<strong>Para</strong> &gt;&gt; 
        <span class="cor_blue"><strong>Azul</strong></span>: iniciado por parceiro / 
        <span class="cor_green"><strong>Verde</strong></span>: iniciado por controlador de suporte
    </div>
<?
}
// fim - se é 'controlador de suporte' ou 'administrador_site' ou 'controle_praca'
?>
<!-- fim - Legenda -->


<!-- barra inferior -->
<div class="div_solicitacao_linhas4" style="margin-top: 5px;">
<table cellspacing="0" cellpadding="0" width="100%">
	<tr>
		<td style="text-align:left">

<!-- agenda -->
<a href="agenda_popup.php?height=<? echo $suporte_editar_tabela_height; ?>&width=<? echo $suporte_editar_tabela_width; ?>&placeValuesBeforeTB_=savedValues&TB_iframe=true" id="botao_geral2" class="thickbox">Ver agenda</a>
<!-- fim - agenda -->
  
<!-- Gerar relatório -->   
<? if($totalRows_suporte > "0") { // caso seja encontrada algum suporte com os filtros atuais ?>
	<a href="#TB_inline?height=<? echo $solicitacao_editar_tabela_height; ?>&width=<? echo $solicitacao_editar_tabela_width; ?>&inlineId=gerar_relatorio&modal=true" class="thickbox" id="botao_geral2">Gerar relatório</a>    
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
    <form action="suporte_relatorio.php" method="post" target="_blank" id="form" name="form">
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
        Marque os campos que irão aparecer no relatório:
        <br><br>
        <!-- campos (checklist) -->
        <fieldset style="border: 0px;">
        <table width="100%" border="0" cellspacing="0" cellpadding="0">
        <tr>
        <td width="33%" valign="top">
        <input value="id" type="checkbox" name="relatorio_campos[]" checked />
        Núm. do suporte
        <br>
        
        
        <input value="tipo_suporte" type="checkbox" name="relatorio_campos[]" checked />
        Tipo de suporte
        <br>
        
        <input value="inloco" type="checkbox" name="relatorio_campos[]" checked />
        In-loco
        <br>
        
        <input value="titulo" type="checkbox" name="relatorio_campos[]" checked />
        Título
        <br>
        
        <input value="data_suporte" type="checkbox" name="relatorio_campos[]" checked />
        Data do suporte
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
        <input value="usuario_envolvido" type="checkbox" name="relatorio_campos[]" />
        Usuário envolvido
        <br>
        
        <input value="usuario_responsavel" type="checkbox" name="relatorio_campos[]" />
        Usuário responsável
        <br>
        
        <input value="solicitante" type="checkbox" name="relatorio_campos[]" />
        Solicitante
        <br>
        
        <input value="modulo" type="checkbox" name="relatorio_campos[]" />
        Módulo
        <br>
        
        <input value="tipo_atendimento" type="checkbox" name="relatorio_campos[]" />
        Tipo de atendimento
        <br>
        
        <input value="anomalia" type="checkbox" name="relatorio_campos[]" />
        Anomalia
        <br>
        
        <input value="orientacao" type="checkbox" name="relatorio_campos[]" />
        Orientação
        <br>
        
        <input value="parecer" type="checkbox" name="relatorio_campos[]" />
        Parecer
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
        <td valign="top">
        <input value="id_formulario" type="checkbox" name="relatorio_campos[]" />
        Número/Tipo do formulário  
        <br>
        
        <input value="recomendacao" type="checkbox" name="relatorio_campos[]" />
        Recomendação
        <br>  
        
        <input value="cobranca" type="checkbox" name="relatorio_campos[]" />
        Auxílio Cobrança
        <br>
        
        <input value="cobranca_recebimento" type="checkbox" name="relatorio_campos[]" />
        Recebido (AC)
        <br>
        
        <input value="cobranca_recebimento_justificativa" type="checkbox" name="relatorio_campos[]" />
        Justificativa (AC)
        <br>  
        
        <input value="avaliacao_atendimento" type="checkbox" name="relatorio_campos[]" />
        Avaliação de atendimento
        <br>
         
        </td>
        </tr>
        </table>
        </fieldset>
        <!-- fim - campos (checklist) -->        
    </div>
    
    <!-- rodapé -->
    <div>Obs: este relatório é baseado nos filtros utilizados na tela anterior de listagem dos suportes.</div>
    <div style="margin-top: 5px;">
    <input type="hidden" name="where" id="where" value="<?  echo @$where; ?>">

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
mysql_free_result($empresa);
mysql_free_result($filtro_empresas);
mysql_free_result($filtro_praca);
mysql_free_result($filtro_tipo_atendimento);
mysql_free_result($filtro_tipo_recomendacao);
mysql_free_result($filtro_usuario_responsavel);
mysql_free_result($filtro_usuario_envolvido);
mysql_free_result($suporte);
?>