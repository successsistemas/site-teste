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

require_once('../parametros.php');
require_once('../funcao.php');

$janela = NULL;
$janela_url = NULL;
if (isset($_GET['janela'])) {
  $janela = $_GET['janela'];
  if($janela == "index"){$janela_url = "&janela=index";}
}

// usuarios
$colname_usuario = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_usuario = $_SESSION['MM_Username'];
}
mysql_select_db($database_conexao, $conexao);
$query_usuario = sprintf("SELECT * FROM usuarios WHERE usuario = %s", GetSQLValueString($colname_usuario, "text"));
$usuario = mysql_query($query_usuario, $conexao) or die(mysql_error());
$row_usuario = mysql_fetch_assoc($usuario);
$totalRows_usuario = mysql_num_rows($usuario);
// fim - usuarios

// comunicado
mysql_select_db($database_conexao, $conexao);
$query_comunicado = sprintf("
SELECT 
comunicado.*, 
usuarios.nome AS remetente  
FROM comunicado 
LEFT JOIN usuarios ON usuarios.IdUsuario = comunicado.IdUsuario 
WHERE comunicado.IdComunicado = %s and 
EXISTS (
	SELECT 'x' 
	FROM comunicado_destinatario 
	WHERE comunicado_destinatario.IdUsuario = %s and comunicado_destinatario.IdComunicado = %s
)
", 
GetSQLValueString($_GET['IdComunicado'], "int"),
GetSQLValueString($row_usuario['IdUsuario'], "int"),
GetSQLValueString($_GET['IdComunicado'], "int"));
$comunicado = mysql_query($query_comunicado, $conexao) or die(mysql_error());
$row_comunicado = mysql_fetch_assoc($comunicado);
$totalRows_comunicado = mysql_num_rows($comunicado);
// fim - comunicado

// comunicado_anexo
mysql_select_db($database_conexao, $conexao);
$query_comunicado_anexo = sprintf("
SELECT comunicado_anexo.* 
FROM comunicado_anexo 
WHERE comunicado_anexo.IdComunicado = %s and comunicado_anexo.IdUsuario = %s and comunicado_anexo.IdComunicadoAnexo = %s 
", 
GetSQLValueString($_GET['IdComunicado'], "int"),
GetSQLValueString($row_usuario['IdUsuario'], "int"),
GetSQLValueString($_GET['IdComunicadoAnexo'], "int"));
$comunicado_anexo = mysql_query($query_comunicado_anexo, $conexao) or die(mysql_error());
$row_comunicado_anexo = mysql_fetch_assoc($comunicado_anexo);
$totalRows_comunicado_anexo = mysql_num_rows($comunicado_anexo);
if($totalRows_comunicado_anexo  == 0){echo "<script>window.top.location = '../padrao/comunicado_detalhe.php?IdComunicado=".$row_comunicado['IdComunicado'].$janela_url."';</script>";}
// fim - comunicado_anexo

// acessso --------------------------------------------------------------------------------------------------------------------------------------------------------------
$acesso = 0;
if ($row_comunicado_anexo['IdUsuario'] == $row_usuario['IdUsuario']) {

	$acesso = 1; // autorizado

}  else {
	
	$acesso = 0; // não autorizado
	
}

if($acesso==0){
	//echo "Acesso não autorizado !";
	echo "<script>window.top.location = '../padrao/comunicado_anexar.php?IdComunicado=".$row_comunicado['IdComunicado'].$janela_url."';</script>";
	exit;
}
// fim - acesso ---------------------------------------------------------------------------------------------------------------------------------------------------------

// Load the common classes
require_once('../../includes/common/KT_common.php');

// Load the tNG classes
require_once('../../includes/tng/tNG.inc.php');

// Make a transaction dispatcher instance
$tNGs = new tNG_dispatcher("");

// Make unified connection variable
$conn_conexao = new KT_connection($conexao, $database_conexao);

//start Trigger_FileDelete trigger
//remove this line if you want to edit the code by hand 
function Trigger_FileDelete(&$tNG) {
  $deleteObj = new tNG_FileDelete($tNG);
  $deleteObj->setFolder("../../arquivos/comunicado");
  $deleteObj->setDbFieldName("arquivo");
  return $deleteObj->Execute();
}
//end Trigger_FileDelete trigger

// Make an instance of the transaction object
$del_comunicado_anexo = new tNG_delete($conn_conexao);
$tNGs->addTransaction($del_comunicado_anexo);
// Register triggers
$del_comunicado_anexo->registerTrigger("STARTER", "Trigger_Default_Starter", 1, "GET", "IdComunicadoAnexo");
$del_comunicado_anexo->registerTrigger("END", "Trigger_Default_Redirect", 99, "../padrao/comunicado_anexar.php?IdComunicado=".$row_comunicado['IdComunicado'].$janela_url);
$del_comunicado_anexo->registerTrigger("AFTER", "Trigger_FileDelete", 98);
// Add columns
$del_comunicado_anexo->setTable("comunicado_anexo");
$del_comunicado_anexo->setPrimaryKey("IdComunicadoAnexo", "NUMERIC_TYPE", "GET", "IdComunicadoAnexo");

// Execute all the registered transactions
$tNGs->executeTransactions();
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
mysql_free_result($comunicado);
mysql_free_result($comunicado_anexo);
?>
