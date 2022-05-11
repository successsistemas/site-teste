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

// acessso --------------------------------------------------------------------------------------------------------------------------------------------------------------
$acesso = 0;
if (
	($row_prospeccao['status_flag'] != "f") and 
		(
		($row_usuario['nivel_prospeccao'] == 1 and $row_usuario['praca'] == $row_prospeccao['praca']) or 
		($row_usuario['nivel_prospeccao'] == 2 and $row_usuario['praca'] == $row_prospeccao['praca']) or 
		$row_prospeccao['id_usuario_responsavel'] == $row_usuario['IdUsuario'] or 
		$row_usuario['praca'] == $row_prospeccao['praca']
		)
	) {

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
	global $ins_prospeccao_descricoes;
  $site_linkObj = new tNG_LinkedTrans($tNG, $ins_prospeccao_descricoes);
  $site_linkObj->setLink("id_arquivo");
  return $site_linkObj->Execute();
}
//end Trigger_LinkTransactions trigger

// Make an instance of the transaction object
$del_prospeccao_arquivos = new tNG_delete($conn_conexao);
$tNGs->addTransaction($del_prospeccao_arquivos);
// Register triggers
$del_prospeccao_arquivos->registerTrigger("STARTER", "Trigger_Default_Starter", 1, "GET", "id_arquivo");
$del_prospeccao_arquivos->registerTrigger("END", "Trigger_Default_Redirect", 99, "prospeccao_editar_upload.php?id_prospeccao={id_prospeccao}&acao={GET.acao}");
$del_prospeccao_arquivos->registerTrigger("AFTER", "Trigger_LinkTransactions", 98);
$del_prospeccao_arquivos->registerTrigger("ERROR", "Trigger_LinkTransactions", 98);
$del_prospeccao_arquivos->registerTrigger("AFTER", "Trigger_FileDelete", 98);
// Add columns
$del_prospeccao_arquivos->setTable("prospeccao_arquivos");
$del_prospeccao_arquivos->setPrimaryKey("id_arquivo", "NUMERIC_TYPE", "GET", "id_arquivo");

// Make an insert transaction instance
$ins_prospeccao_descricoes = new tNG_insert($conn_conexao);
$tNGs->addTransaction($ins_prospeccao_descricoes);
// Register triggers
$ins_prospeccao_descricoes->registerTrigger("STARTER", "Trigger_Default_Starter", 1, "POST", "");
// Add columns
$ins_prospeccao_descricoes->setTable("prospeccao_descricoes");
$ins_prospeccao_descricoes->addColumn("descricao", "STRING_TYPE", "VALUE", "Foi excluído um arquivo em anexo");
$ins_prospeccao_descricoes->addColumn("data", "DATE_TYPE", "VALUE", date('d-m-Y H:i:s'));
$ins_prospeccao_descricoes->addColumn("tipo_postagem", "STRING_TYPE", "VALUE", "Exclusão de arquivo");
$ins_prospeccao_descricoes->addColumn("id_usuario_responsavel", "NUMERIC_TYPE", "VALUE", "{usuario.IdUsuario}");
$ins_prospeccao_descricoes->addColumn("id_prospeccao", "NUMERIC_TYPE", "GET", "id_prospeccao", "");
$ins_prospeccao_descricoes->addColumn("id_arquivo", "NUMERIC_TYPE", "VALUE", "");
$ins_prospeccao_descricoes->setPrimaryKey("id", "NUMERIC_TYPE");

// Execute all the registered transactions
$tNGs->executeTransactions();

// Get the transaction recordset
$rsprospeccao_descricoes = $tNGs->getRecordset("prospeccao_descricoes");
$row_rsprospeccao_descricoes = mysql_fetch_assoc($rsprospeccao_descricoes);
$totalRows_rsprospeccao_descricoes = mysql_num_rows($rsprospeccao_descricoes);
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

mysql_free_result($prospeccao);
?>
