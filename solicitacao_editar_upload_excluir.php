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

// solicitacao
$colname_solicitacao = "-1";
if (isset($_GET['id_solicitacao'])) {
  $colname_solicitacao = $_GET['id_solicitacao'];
}
mysql_select_db($database_conexao, $conexao);
$query_solicitacao = sprintf("
SELECT solicitacao.*, 
(SELECT nome FROM usuarios WHERE usuarios.IdUsuario = solicitacao.id_usuario_responsavel) as usuario_responsavel, 
(SELECT nome FROM usuarios WHERE usuarios.IdUsuario = solicitacao.id_operador) as nome_operador, 
(SELECT nome FROM usuarios WHERE usuarios.IdUsuario = solicitacao.id_executante) as nome_executante, 
(SELECT nome FROM usuarios WHERE usuarios.IdUsuario = solicitacao.id_analista_orcamento) as nome_analista_orcamento, 
(SELECT nome FROM usuarios WHERE usuarios.IdUsuario = solicitacao.id_testador) as nome_testador
FROM solicitacao 
WHERE id = %s", 
GetSQLValueString($colname_solicitacao, "int"));
$solicitacao = mysql_query($query_solicitacao, $conexao) or die(mysql_error());
$row_solicitacao = mysql_fetch_assoc($solicitacao);
$totalRows_solicitacao = mysql_num_rows($solicitacao);
// fim - solicitacao

// acessso --------------------------------------------------------------------------------------------------------------------------------------------------------------
$acesso = 0;
if (
	($row_solicitacao['situacao'] != "solucionada" and $row_solicitacao['situacao'] != "reprovada") and 
		(
		($row_solicitacao['id_usuario_responsavel'] == $row_usuario['IdUsuario']) or 
		
		($row_usuario['controle_solicitacao'] == "Y" and $row_usuario['IdUsuario'] == $row_solicitacao['id_operador']) or  
		($row_usuario['solicitacao_executante'] == "Y" and $row_usuario['IdUsuario'] == $row_solicitacao['id_executante']) or  
		($row_usuario['solicitacao_executante'] == "Y" and $row_usuario['IdUsuario'] == $row_solicitacao['id_analista_orcamento']) or  
		($row_usuario['solicitacao_testador'] == "Y" and $row_usuario['IdUsuario'] == $row_solicitacao['id_testador']) or  
		($row_usuario['controle_praca'] == "Y" and $row_usuario['praca'] = $row_solicitacao['praca'])
		)
	) {

	$acesso = 1; // autorizado

}  else {
	
	$acesso = 0; // não autorizado
	
}

if($acesso==0){
	//echo "Acesso não autorizado !";
	echo "<script>window.top.location = 'solicitacao.php?padrao=sim&".$situacao_padrao."';</script>";
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
	global $ins_solicitacao_descricoes;
  $site_linkObj = new tNG_LinkedTrans($tNG, $ins_solicitacao_descricoes);
  $site_linkObj->setLink("id_arquivo");
  return $site_linkObj->Execute();
}
//end Trigger_LinkTransactions trigger

// Make an instance of the transaction object
$del_solicitacao_arquivos = new tNG_delete($conn_conexao);
$tNGs->addTransaction($del_solicitacao_arquivos);
// Register triggers
$del_solicitacao_arquivos->registerTrigger("STARTER", "Trigger_Default_Starter", 1, "GET", "id_arquivo");
$del_solicitacao_arquivos->registerTrigger("END", "Trigger_Default_Redirect", 99, "solicitacao_editar_upload.php?id_solicitacao={id_solicitacao}&acao={GET.acao}");
$del_solicitacao_arquivos->registerTrigger("AFTER", "Trigger_LinkTransactions", 98);
$del_solicitacao_arquivos->registerTrigger("ERROR", "Trigger_LinkTransactions", 98);
$del_solicitacao_arquivos->registerTrigger("AFTER", "Trigger_FileDelete", 98);
// Add columns
$del_solicitacao_arquivos->setTable("solicitacao_arquivos");
$del_solicitacao_arquivos->setPrimaryKey("id_arquivo", "NUMERIC_TYPE", "GET", "id_arquivo");

// Make an insert transaction instance
$ins_solicitacao_descricoes = new tNG_insert($conn_conexao);
$tNGs->addTransaction($ins_solicitacao_descricoes);
// Register triggers
$ins_solicitacao_descricoes->registerTrigger("STARTER", "Trigger_Default_Starter", 1, "POST", "");
// Add columns
$ins_solicitacao_descricoes->setTable("solicitacao_descricoes");
$ins_solicitacao_descricoes->addColumn("descricao", "STRING_TYPE", "VALUE", "Foi excluído um arquivo em anexo");
$ins_solicitacao_descricoes->addColumn("data", "DATE_TYPE", "VALUE", date('d-m-Y H:i:s'));
$ins_solicitacao_descricoes->addColumn("tipo_postagem", "STRING_TYPE", "VALUE", "Exclusão de arquivo");
$ins_solicitacao_descricoes->addColumn("id_usuario_responsavel", "NUMERIC_TYPE", "VALUE", "{usuario.IdUsuario}");
$ins_solicitacao_descricoes->addColumn("id_solicitacao", "NUMERIC_TYPE", "GET", "id_solicitacao", "");
$ins_solicitacao_descricoes->addColumn("id_arquivo", "NUMERIC_TYPE", "VALUE", "");
$ins_solicitacao_descricoes->setPrimaryKey("id", "NUMERIC_TYPE");

// Execute all the registered transactions
$tNGs->executeTransactions();

// Get the transaction recordset
$rssolicitacao_descricoes = $tNGs->getRecordset("solicitacao_descricoes");
$row_rssolicitacao_descricoes = mysql_fetch_assoc($rssolicitacao_descricoes);
$totalRows_rssolicitacao_descricoes = mysql_num_rows($rssolicitacao_descricoes);
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

mysql_free_result($solicitacao);
?>
