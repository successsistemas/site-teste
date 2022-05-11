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

// função que limita caracteres
function limita_caracteres($string, $tamanho, $encode = 'UTF-8') {
    if( strlen($string) > $tamanho ){
        $string = mb_substr($string, 0, $tamanho, $encode);
	}
    return $string;
}
// fim - função que limita caracteres

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
$colname_comunicado_anexo = "-1";
if (isset($_GET['IdComunicado'])) {
  $colname_comunicado_anexo = $_GET['IdComunicado'];
}
mysql_select_db($database_conexao, $conexao);
$query_comunicado_anexo = sprintf("
SELECT comunicado_anexo.*, 
usuarios.nome AS usuarios_nome 
FROM comunicado_anexo 
LEFT JOIN usuarios ON comunicado_anexo.IdUsuario = usuarios.IdUsuario 
WHERE comunicado_anexo.IdComunicado = %s 
ORDER BY comunicado_anexo.IdComunicadoAnexo DESC", 
GetSQLValueString($colname_comunicado_anexo, "int"));
$comunicado_anexo = mysql_query($query_comunicado_anexo, $conexao) or die(mysql_error());
$row_comunicado_anexo = mysql_fetch_assoc($comunicado_anexo);
$totalRows_comunicado_anexo = mysql_num_rows($comunicado_anexo);
// fim - comunicado_anexo

// Load the common classes
require_once('../../includes/common/KT_common.php');

// Load the tNG classes
require_once('../../includes/tng/tNG.inc.php');

// Make a transaction dispatcher instance
$tNGs = new tNG_dispatcher("../../");

// Make unified connection variable
$conn_conexao = new KT_connection($conexao, $database_conexao);

//start Trigger_FileUpload trigger
//remove this line if you want to edit the code by hand 
function Trigger_FileUpload(&$tNG) {
  $uploadObj = new tNG_FileUpload($tNG);
  $uploadObj->setFormFieldName("arquivo");
  $uploadObj->setDbFieldName("arquivo");
  $uploadObj->setFolder("../../arquivos/comunicado");
  $uploadObj->setMaxSize(20480);
  $uploadObj->setAllowedExtensions("gif, jpg, jpe, jpeg, png, bmp, pdf, doc, docx, xls, xlsx, ppt, pptx, txt");
  $uploadObj->setRename("auto");
  return $uploadObj->Execute();
}
//end Trigger_FileUpload trigger

// Start trigger
$formValidation = new tNG_FormValidation();
$formValidation->addField("arquivo", true, "", "", "", "", "Selecione um arquivo...");
$tNGs->prepareValidation($formValidation);
// End trigger

// Make an insert transaction instance
$ins_comunicado_anexo = new tNG_insert($conn_conexao);
$tNGs->addTransaction($ins_comunicado_anexo);
// Register triggers
$ins_comunicado_anexo->registerTrigger("STARTER", "Trigger_Default_Starter", 1, "POST", "KT_Insert1");
$ins_comunicado_anexo->registerTrigger("END", "Trigger_Default_Redirect", 99, "../padrao/comunicado_anexar.php?IdComunicado=".$row_comunicado['IdComunicado'].$janela_url);
$ins_comunicado_anexo->registerTrigger("AFTER", "Trigger_FileUpload", 97);
$ins_comunicado_anexo->registerTrigger("BEFORE", "Trigger_Default_FormValidation", 10, $formValidation);
// Add columns
$ins_comunicado_anexo->setTable("comunicado_anexo");
$ins_comunicado_anexo->addColumn("IdComunicado", "NUMERIC_TYPE", "VALUE", "{comunicado.IdComunicado}");
$ins_comunicado_anexo->addColumn("IdUsuario", "NUMERIC_TYPE", "VALUE", "{usuario.IdUsuario}");
$ins_comunicado_anexo->addColumn("data_criacao", "DATE_TYPE", "POST", "data");
$ins_comunicado_anexo->addColumn("arquivo", "FILE_TYPE", "FILES", "arquivo");
$ins_comunicado_anexo->setPrimaryKey("IdComunicadoAnexo", "NUMERIC_TYPE");

// Execute all the registered transactions
$tNGs->executeTransactions();

// Get the transaction recordset
$rscomunicado_anexo = $tNGs->getRecordset("comunicado_anexo");
$row_rscomunicado_anexo = mysql_fetch_assoc($rscomunicado_anexo);
$totalRows_rscomunicado_anexo = mysql_num_rows($rscomunicado_anexo);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title></title>
<link rel="stylesheet" href="../../css/suporte.css" type="text/css" media="screen" />
<script type="text/javascript" src="../../js/jquery.js"></script>
<script type="text/javascript" src="../../funcoes.js"></script>

<script src="../../js/jquery.metadata.js" type="text/javascript"></script>
<script type="text/javascript" src="../../js/jquery.validate.js"></script>

<script type="text/javascript" src="../../js/jquery.numeric.js"></script>

<script type="text/javascript" src="../../js/jquery.maskedinput-1.2.2.min.js"></script> 
<script src="../../js/jquery.price_format.1.3.js" type="text/javascript"></script> 
<script type="text/javascript" src="../../js/jquery.alphanumeric.pack.js"></script> 

<script type="text/javascript"> 
function confirmaSubmit(){
	var agree=confirm("Deseja realmente excluir este arquivo ?");
	if (agree)
		return true ;
	else
		return false ;
}
</script>

<!--[if !IE]> -->
<style>
body{
	overflow-y: scroll; /* se não é IE, então mostra a scroll vertical */
}
</style>
<!-- <![endif]-->

<script type="text/javascript">
$(document).ready(function() {

    
});
</script>
<link href="../../includes/skins/mxkollection3.css" rel="stylesheet" type="text/css" media="all" />
<script src="../../includes/common/js/base.js" type="text/javascript"></script>
<script src="../../includes/common/js/utility.js" type="text/javascript"></script>
<script src="../../includes/skins/style.js" type="text/javascript"></script>
<?php echo $tNGs->displayValidationRules();?>
</head>

<body>

<div class="div_solicitacao_linhas" id="cabecalho_solicitacoes" style="cursor: pointer">
<table cellspacing="0" cellpadding="0" width="100%">
	<tr>
		<td style="text-align:left">
		Comunicado número: <?php echo $row_comunicado['IdComunicado']; ?>
		</td>
        
		<td style="text-align: right">
        &lt;&lt; <a href="../padrao/comunicado_detalhe.php?IdComunicado=<? echo $row_comunicado['IdComunicado']; ?><? if($janela == "index"){ ?>&janela=index<? } ?>">Voltar</a>
        </td>
	</tr>
</table>
</div>

<div class="div_solicitacao_linhas2">
<table cellspacing="0" cellpadding="0">
	<tr>
		<td style="text-align: left">
		<strong>Arquivos em anexo</strong> 
		</td>
	</tr>
</table>
</div>

<div class="div_solicitacao_linhas3">
<table cellspacing="0" cellpadding="0">
	<tr>
		<td style="text-align: left; font-weight: bold; font-size: 14px;">
		Título: <? echo $row_comunicado['assunto']; ?>
		<div style="font-size: 12px; font-weight: normal;">Remetente: <? echo $row_comunicado['remetente']; ?></div>
		</td>
	</tr>
</table>
</div>

<div class="div_solicitacao_linhas2">
<table cellspacing="0" cellpadding="0">
	<tr>
		<td style="text-align: left">
        <form id="form" name="form" method="POST" enctype="multipart/form-data">

		<!-- Observação -->
		<div style="padding-bottom: 10px;">
        	<div class="label_solicitacao2">Arquivo:</div>
        	<input type="file" id="arquivo" name="arquivo" />
		</div>
		<!-- fim - Observação -->
        
        <!-- Botões -->
        <div>
        <input type="hidden" id="data" name="data" value="<? echo date('d-m-Y H:i:s'); ?>" />
        <input type="submit" name="KT_Insert1" id="KT_Insert1" value="Anexar" class="botao_geral2" style="width: 90px" />
        </div>
        <!-- fim - Botões -->
        
        <?php echo $tNGs->getErrorMsg(); ?>
        
        </form>
		</td>
	</tr>
</table>
</div>

<!-- arquivos já anexados -->
<div class="div_solicitacao_linhas4">
<? if($totalRows_comunicado_anexo > 0) { ?>
<table cellspacing="0" cellpadding="0">
	<tr>
		<td style="text-align: left">
		<strong>Arquivos já anexados:</strong>
        <br>
		<?php do { ?>
            <div style=" margin-top: 10px; margin-bottom: 10px;">
            <span><? echo $row_comunicado_anexo['usuarios_nome']; ?> 
            [<? echo date('d/m/Y H:i', strtotime($row_comunicado_anexo['data_criacao'])); ?>]: 
            </span> 
            <a href="../../arquivos/comunicado/<?php echo $row_comunicado_anexo['arquivo']; ?>" target="_blank">
			<?php echo $row_comunicado_anexo['arquivo']; ?> 
            </a> 
			
            <? if ($row_comunicado_anexo['IdUsuario'] == $row_usuario['IdUsuario']){ ?>
            <a href="comunicado_anexar_excluir.php?IdComunicado=<?php echo $row_comunicado_anexo['IdComunicado']; ?>&IdComunicadoAnexo=<?php echo $row_comunicado_anexo['IdComunicadoAnexo']; ?>" target="_self">
            <img src="../../imagens/remove.png" border="0" align="top" title="Excluir arquivo" onClick="return confirmaSubmit()">
            </a>
            <? } ?>
			
            </div>
        <?php } while ($row_comunicado_anexo = mysql_fetch_assoc($comunicado_anexo)); ?>
		</td>
	</tr>
</table>
<? } else { ?>
Nenhum arquivo em anexo.
<? } ?>
</div>
<!-- fim - arquivos já anexados -->
       
</body>

</html>
<?php 
mysql_free_result($usuario); 
mysql_free_result($comunicado);
mysql_free_result($comunicado_anexo);
?>