<?php require('restrito.php'); ?>
<?php require_once('Connections/conexao.php'); ?>
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

$colname_usuario = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_usuario = $_SESSION['MM_Username'];
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

// suporte
$colname_suporte = "-1";
if (isset($_GET['id_suporte'])) {
  $colname_suporte = $_GET['id_suporte'];
}
mysql_select_db($database_conexao, $conexao);
$query_suporte = sprintf("
SELECT 
	suporte.*,  
	(SELECT nome FROM usuarios WHERE usuarios.IdUsuario = suporte.id_usuario_responsavel) as usuario_responsavel, 
	(SELECT nome FROM usuarios WHERE usuarios.IdUsuario = suporte.id_usuario_envolvido) as usuario_envolvido, 
	geral_tipo_praca.suporte_inloco_sim_prazo_anexo AS geral_tipo_praca_suporte_inloco_sim_prazo_anexo 
FROM 
	suporte 
LEFT JOIN 
    geral_tipo_praca ON geral_tipo_praca.praca = suporte.praca 
WHERE 
	id = %s
", 
GetSQLValueString($colname_suporte, "int"));
$suporte = mysql_query($query_suporte, $conexao) or die(mysql_error());
$row_suporte = mysql_fetch_assoc($suporte);
$totalRows_suporte = mysql_num_rows($suporte);
// fim - suporte

$prazo_anexo_segundos = $row_suporte['geral_tipo_praca_suporte_inloco_sim_prazo_anexo'] * 86400;
$prazo_anexo_limite_segundos = strtotime($row_suporte['data_inicio']) + $prazo_anexo_segundos;
$data_atual_segundos = strtotime(date("Y-m-d 00:00:00"));

// acessso --------------------------------------------------------------------------------------------------------------------------------------------------------------
$acesso = 0;

if ( 
	(		
		$row_usuario['controle_suporte'] == "Y" or  
		$row_usuario['suporte_operador_parceiro'] == "Y" or  
		$row_suporte['id_usuario_responsavel'] == $row_usuario['IdUsuario'] or  
		$row_suporte['id_usuario_envolvido'] == $row_usuario['IdUsuario'] or 
		$row_suporte['praca'] == $row_usuario['praca']
	) and (
		(
			$row_suporte['inloco'] == "s" and 
			$row_suporte['situacao'] != "cancelada" and 
			(
				($data_atual_segundos <= $prazo_anexo_limite_segundos) or 
				($data_atual_segundos > $prazo_anexo_limite_segundos and $row_suporte['prazo_anexo_liberar'] == "s")
			)
		) or
		($row_suporte['inloco'] == "n" and $row_suporte['situacao'] != "cancelada" and $row_suporte['situacao'] != "solucionada")
	)
){

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


// Load the common classes
require_once('includes/common/KT_common.php');

// Load the tNG classes
require_once('includes/tng/tNG.inc.php');

// Make a transaction dispatcher instance
$tNGs = new tNG_dispatcher("");

// Make unified connection variable
$conn_conexao = new KT_connection($conexao, $database_conexao);

//start Trigger_FileDelete trigger
//remove this line if you want to edit the code by hand 
function Trigger_FileDelete(&$tNG) {
  $deleteObj = new tNG_FileDelete($tNG);
  $deleteObj->setFolder("arquivos/");
  $deleteObj->setDbFieldName("nome_arquivo");
  return $deleteObj->Execute();
}
//end Trigger_FileDelete trigger

//start Trigger_LinkTransactions trigger
//remove this line if you want to edit the code by hand 
function Trigger_LinkTransactions(&$tNG) {
	global $ins_suporte_descricoes;
  $site_linkObj = new tNG_LinkedTrans($tNG, $ins_suporte_descricoes);
  $site_linkObj->setLink("id_arquivo");
  return $site_linkObj->Execute();
}
//end Trigger_LinkTransactions trigger

// Make an instance of the transaction object
$del_suporte_arquivos = new tNG_delete($conn_conexao);
$tNGs->addTransaction($del_suporte_arquivos);
// Register triggers
$del_suporte_arquivos->registerTrigger("STARTER", "Trigger_Default_Starter", 1, "GET", "id_arquivo");
$del_suporte_arquivos->registerTrigger("END", "Trigger_Default_Redirect", 99, "suporte_editar_upload.php?id_suporte={id_suporte}&acao={GET.acao}&editar_tabela={GET.editar_tabela}&voltar={GET.voltar}");
$del_suporte_arquivos->registerTrigger("AFTER", "Trigger_LinkTransactions", 98);
$del_suporte_arquivos->registerTrigger("ERROR", "Trigger_LinkTransactions", 98);
$del_suporte_arquivos->registerTrigger("AFTER", "Trigger_FileDelete", 98);
// Add columns
$del_suporte_arquivos->setTable("suporte_arquivos");
$del_suporte_arquivos->setPrimaryKey("id_arquivo", "NUMERIC_TYPE", "GET", "id_arquivo");

// Make an insert transaction instance
$ins_suporte_descricoes = new tNG_insert($conn_conexao);
$tNGs->addTransaction($ins_suporte_descricoes);
// Register triggers
$ins_suporte_descricoes->registerTrigger("STARTER", "Trigger_Default_Starter", 1, "POST", "");
// Add columns
$ins_suporte_descricoes->setTable("suporte_descricoes");
$ins_suporte_descricoes->addColumn("descricao", "STRING_TYPE", "VALUE", "Foi excluído um arquivo em anexo");
$ins_suporte_descricoes->addColumn("data", "DATE_TYPE", "VALUE", date('d-m-Y H:i:s'));
$ins_suporte_descricoes->addColumn("tipo_postagem", "STRING_TYPE", "VALUE", "Exclusão de arquivo");
$ins_suporte_descricoes->addColumn("id_usuario_responsavel", "NUMERIC_TYPE", "VALUE", "{usuario.IdUsuario}");
$ins_suporte_descricoes->addColumn("id_suporte", "NUMERIC_TYPE", "GET", "id_suporte", "");
$ins_suporte_descricoes->addColumn("id_arquivo", "NUMERIC_TYPE", "VALUE", "");
$ins_suporte_descricoes->setPrimaryKey("id", "NUMERIC_TYPE");

// Execute all the registered transactions
$tNGs->executeTransactions();

// Get the transaction recordset
$rssuporte_descricoes = $tNGs->getRecordset("suporte_descricoes");
$row_rssuporte_descricoes = mysql_fetch_assoc($rssuporte_descricoes);
$totalRows_rssuporte_descricoes = mysql_num_rows($rssuporte_descricoes);
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title></title>
<link href="includes/skins/mxkollection3.css" rel="stylesheet" type="text/css" media="all" />
<script src="includes/common/js/base.js" type="text/javascript"></script>
<script src="includes/common/js/utility.js" type="text/javascript"></script>
<script src="includes/skins/style.js" type="text/javascript"></script>
</head>

<body>
<?php
	echo $tNGs->getErrorMsg();
?>
</body>
</html>
<?php
mysql_free_result($usuario);

mysql_free_result($suporte);
?>
