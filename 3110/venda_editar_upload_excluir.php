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

// venda
$colname_venda = "-1";
if (isset($_GET['id_venda'])) {
  $colname_venda = $_GET['id_venda'];
}
mysql_select_db($database_conexao, $conexao);
$query_venda = sprintf("
SELECT venda.*, 
(SELECT nome FROM usuarios WHERE venda.id_usuario_responsavel = usuarios.IdUsuario) AS usuario_responsavel
FROM venda 
WHERE venda.id = %s", 
GetSQLValueString($colname_venda, "int"));
$venda = mysql_query($query_venda, $conexao) or die(mysql_error());
$row_venda = mysql_fetch_assoc($venda);
$totalRows_venda = mysql_num_rows($venda);
// fim - venda

// acessso --------------------------------------------------------------------------------------------------------------------------------------------------------------
$acesso = 0;
if (
	($row_venda['status_flag'] != "f") and 
		(
		 ($row_usuario['nivel_venda'] == 1 and $row_usuario['praca'] == $row_venda['praca']) or 
		 ($row_usuario['nivel_venda'] == 2 and $row_usuario['praca'] == $row_venda['praca']) or 
		 $row_venda['id_usuario_responsavel'] == $row_usuario['IdUsuario'] or 
		 $row_usuario['praca'] == "MATRIZ"
		)
	) {

	$acesso = 1; // autorizado

}  else {
	
	$acesso = 0; // não autorizado
	
}

if($acesso==0){
	//echo "Acesso não autorizado !";
	echo "<script>window.top.location = 'venda.php?padrao=sim&".$venda_padrao."';</script>";
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
	global $ins_venda_descricoes;
  $site_linkObj = new tNG_LinkedTrans($tNG, $ins_venda_descricoes);
  $site_linkObj->setLink("id_arquivo");
  return $site_linkObj->Execute();
}
//end Trigger_LinkTransactions trigger

// Make an instance of the transaction object
$del_venda_arquivos = new tNG_delete($conn_conexao);
$tNGs->addTransaction($del_venda_arquivos);
// Register triggers
$del_venda_arquivos->registerTrigger("STARTER", "Trigger_Default_Starter", 1, "GET", "id_arquivo");
$del_venda_arquivos->registerTrigger("END", "Trigger_Default_Redirect", 99, "venda_editar_upload.php?id_venda={id_venda}&acao={GET.acao}");
$del_venda_arquivos->registerTrigger("AFTER", "Trigger_LinkTransactions", 98);
$del_venda_arquivos->registerTrigger("ERROR", "Trigger_LinkTransactions", 98);
$del_venda_arquivos->registerTrigger("AFTER", "Trigger_FileDelete", 98);
// Add columns
$del_venda_arquivos->setTable("venda_arquivos");
$del_venda_arquivos->setPrimaryKey("id_arquivo", "NUMERIC_TYPE", "GET", "id_arquivo");

// Make an insert transaction instance
$ins_venda_descricoes = new tNG_insert($conn_conexao);
$tNGs->addTransaction($ins_venda_descricoes);
// Register triggers
$ins_venda_descricoes->registerTrigger("STARTER", "Trigger_Default_Starter", 1, "POST", "");
// Add columns
$ins_venda_descricoes->setTable("venda_descricoes");
$ins_venda_descricoes->addColumn("descricao", "STRING_TYPE", "VALUE", "Foi excluído um arquivo em anexo");
$ins_venda_descricoes->addColumn("data", "DATE_TYPE", "VALUE", date('d-m-Y H:i:s'));
$ins_venda_descricoes->addColumn("tipo_postagem", "STRING_TYPE", "VALUE", "Exclusão de arquivo");
$ins_venda_descricoes->addColumn("id_usuario_responsavel", "NUMERIC_TYPE", "VALUE", "{usuario.IdUsuario}");
$ins_venda_descricoes->addColumn("id_venda", "NUMERIC_TYPE", "GET", "id_venda", "");
$ins_venda_descricoes->addColumn("id_arquivo", "NUMERIC_TYPE", "VALUE", "");
$ins_venda_descricoes->setPrimaryKey("id", "NUMERIC_TYPE");

// Execute all the registered transactions
$tNGs->executeTransactions();

// Get the transaction recordset
$rsvenda_descricoes = $tNGs->getRecordset("venda_descricoes");
$row_rsvenda_descricoes = mysql_fetch_assoc($rsvenda_descricoes);
$totalRows_rsvenda_descricoes = mysql_num_rows($rsvenda_descricoes);
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

mysql_free_result($venda);
?>
