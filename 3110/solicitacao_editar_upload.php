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

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING'], ENT_QUOTES, 'UTF-8');
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

// arquivos anexados
$colname_arquivos_anexados = "-1";
if (isset($_GET['id_solicitacao'])) {
  $colname_arquivos_anexados = $_GET['id_solicitacao'];
}
mysql_select_db($database_conexao, $conexao);
$query_arquivos_anexados = sprintf("SELECT * FROM solicitacao_arquivos WHERE id_solicitacao = %s ORDER BY id_arquivo DESC", GetSQLValueString($colname_arquivos_anexados, "int"));
$arquivos_anexados = mysql_query($query_arquivos_anexados, $conexao) or die(mysql_error());
$row_arquivos_anexados = mysql_fetch_assoc($arquivos_anexados);
$totalRows_arquivos_anexados = mysql_num_rows($arquivos_anexados);
// fim - arquivos anexados

// insere arquivo e descricao ---------------------------------------------------------
if ((
		$row_solicitacao['id_usuario_responsavel']==$row_usuario['IdUsuario'] or 
		$row_solicitacao['id_operador']==$row_usuario['IdUsuario'] or 
		$row_solicitacao['id_analista_orcamento']==$row_usuario['IdUsuario'] or 
		$row_solicitacao['id_executante']==$row_usuario['IdUsuario'] or 
		$row_solicitacao['id_testador']==$row_usuario['IdUsuario']
		) and ($row_solicitacao['situacao']!="solucionada" and $row_solicitacao['situacao']!="reprovada"))
{
	
// Load the common classes
require_once('includes/common/KT_common.php');

// Load the tNG classes
require_once('includes/tng/tNG.inc.php');

// Make a transaction dispatcher instance
$tNGs = new tNG_dispatcher("");

// Make unified connection variable
$conn_conexao = new KT_connection($conexao, $database_conexao);

// Start trigger
$masterValidation = new tNG_FormValidation();
$masterValidation->addField("nome_arquivo", true, "", "", "", "", "Selecione um arquivo antes de prosseguir.");
$tNGs->prepareValidation($masterValidation);
// End trigger

// Start trigger
$detailValidation = new tNG_FormValidation();
$tNGs->prepareValidation($detailValidation);
// End trigger

//start Trigger_LinkTransactions trigger
//remove this line if you want to edit the code by hand 
function Trigger_LinkTransactions(&$tNG) {
	global $ins_solicitacao_descricoes;
  $site_linkObj = new tNG_LinkedTrans($tNG, $ins_solicitacao_descricoes);
  $site_linkObj->setLink("id_arquivo");
  return $site_linkObj->Execute();
}
//end Trigger_LinkTransactions trigger

//start Trigger_FileUpload trigger
//remove this line if you want to edit the code by hand 
function Trigger_FileUpload(&$tNG) {
  $uploadObj = new tNG_FileUpload($tNG);
  $uploadObj->setFormFieldName("nome_arquivo");
  $uploadObj->setDbFieldName("nome_arquivo");
  $uploadObj->setFolder("arquivos/");
  $uploadObj->setMaxSize(5120);
  $uploadObj->setAllowedExtensions("rar, zip, txt, jpeg, jpg, pdf");
  $uploadObj->setRename("auto");
  return $uploadObj->Execute();
}
//end Trigger_FileUpload trigger

// Make an insert transaction instance
$ins_solicitacao_arquivos = new tNG_insert($conn_conexao);
$tNGs->addTransaction($ins_solicitacao_arquivos);
// Register triggers
$ins_solicitacao_arquivos->registerTrigger("STARTER", "Trigger_Default_Starter", 1, "POST", "KT_Insert1");
$ins_solicitacao_arquivos->registerTrigger("BEFORE", "Trigger_Default_FormValidation", 10, $masterValidation);
$ins_solicitacao_arquivos->registerTrigger("END", "Trigger_Default_Redirect", 99, "solicitacao_editar_upload.php?id_solicitacao={solicitacao.id}&acao={GET.acao}");
$ins_solicitacao_arquivos->registerTrigger("AFTER", "Trigger_LinkTransactions", 98);
$ins_solicitacao_arquivos->registerTrigger("ERROR", "Trigger_LinkTransactions", 98);
$ins_solicitacao_arquivos->registerTrigger("AFTER", "Trigger_FileUpload", 97);
// Add columns
$ins_solicitacao_arquivos->setTable("solicitacao_arquivos");
$ins_solicitacao_arquivos->addColumn("nome_arquivo", "FILE_TYPE", "FILES", "nome_arquivo");
$ins_solicitacao_arquivos->addColumn("id_solicitacao", "NUMERIC_TYPE", "POST", "id_solicitacao", "{solicitacao.id}");
$ins_solicitacao_arquivos->setPrimaryKey("id_arquivo", "NUMERIC_TYPE");

// Make an insert transaction instance
$ins_solicitacao_descricoes = new tNG_insert($conn_conexao);
$tNGs->addTransaction($ins_solicitacao_descricoes);
// Register triggers
$ins_solicitacao_descricoes->registerTrigger("STARTER", "Trigger_Default_Starter", 1, "POST", "");
$ins_solicitacao_descricoes->registerTrigger("BEFORE", "Trigger_Default_FormValidation", 10, $detailValidation);
// Add columns
$ins_solicitacao_descricoes->setTable("solicitacao_descricoes");
$ins_solicitacao_descricoes->addColumn("descricao", "STRING_TYPE", "POST", "descricao");
$ins_solicitacao_descricoes->addColumn("data", "DATE_TYPE", "POST", "data");
$ins_solicitacao_descricoes->addColumn("tipo_postagem", "STRING_TYPE", "POST", "tipo_postagem");
$ins_solicitacao_descricoes->addColumn("id_usuario_responsavel", "NUMERIC_TYPE", "POST", "id_usuario_responsavel");
$ins_solicitacao_descricoes->addColumn("id_solicitacao", "NUMERIC_TYPE", "POST", "id_solicitacao", "{solicitacao.id}");
$ins_solicitacao_descricoes->addColumn("id_arquivo", "NUMERIC_TYPE", "VALUE", "");
$ins_solicitacao_descricoes->setPrimaryKey("id", "NUMERIC_TYPE");

// Execute all the registered transactions
$tNGs->executeTransactions();

// Get the transaction recordset
$rssolicitacao_arquivos = $tNGs->getRecordset("solicitacao_arquivos");
$row_rssolicitacao_arquivos = mysql_fetch_assoc($rssolicitacao_arquivos);
$totalRows_rssolicitacao_arquivos = mysql_num_rows($rssolicitacao_arquivos);

// Get the transaction recordset
$rssolicitacao_descricoes = $tNGs->getRecordset("solicitacao_descricoes");
$row_rssolicitacao_descricoes = mysql_fetch_assoc($rssolicitacao_descricoes);
$totalRows_rssolicitacao_descricoes = mysql_num_rows($rssolicitacao_descricoes);

}
// fim - insere arquivo e descricao ---------------------------------------------------
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><? echo $_GET['acao']; ?> (<?php echo $row_solicitacao['id']; ?>)</title>

<link rel="stylesheet" href="css/suporte.css" type="text/css" media="screen" />
<script type="text/javascript" src="js/jquery.js"></script>

<?
if ((		
		$row_solicitacao['id_usuario_responsavel']==$row_usuario['IdUsuario'] or 
		$row_solicitacao['id_operador']==$row_usuario['IdUsuario'] or 
		$row_solicitacao['id_analista_orcamento']==$row_usuario['IdUsuario'] or 
		$row_solicitacao['id_executante']==$row_usuario['IdUsuario'] or 
		$row_solicitacao['id_testador']==$row_usuario['IdUsuario']
		) and ($row_solicitacao['situacao']!="solucionada" and $row_solicitacao['situacao']!="reprovada"))
{
	?>
    <link href="includes/skins/mxkollection3.css" rel="stylesheet" type="text/css" media="all" />
    <script src="includes/common/js/base.js" type="text/javascript"></script>
    <script src="includes/common/js/utility.js" type="text/javascript"></script>
    <script src="includes/skins/style.js" type="text/javascript"></script>
    <script type="text/javascript"> 
    function confirmaSubmit(){
        var agree=confirm("Deseja realmente excluir este arquivo ?");
        if (agree)
            return true ;
        else
            return false ;
    }
    </script>
    <?php echo $tNGs->displayValidationRules();?>
<? } ?>

</head>

<body style="text-align: center">

<div class="div_solicitacao_linhas">
<table cellspacing="0" cellpadding="0" width="100%">
	<tr>
		<td style="text-align:left">
		Solicitação número: <?php echo $row_solicitacao['id']; ?>
		</td>

		<td style="text-align: right">
		&lt;&lt; <a href="solicitacao_editar.php?id_solicitacao=<?php echo $_GET['id_solicitacao']; ?>" target="_top">Voltar</a>
        </td>
	</tr>
</table>
</div>

<div class="div_solicitacao_linhas2">
<table cellspacing="0" cellpadding="0">
	<tr>
		<td style="text-align: left">
		<strong><? echo $_GET['acao']; ?></strong>
		</td>
	</tr>
</table>
</div>

<div class="div_solicitacao_linhas3">
<table cellspacing="0" cellpadding="0">
	<tr>
		<td style="text-align: left">
		Título: <?php echo $row_solicitacao['titulo']; ?>
		</td>
	</tr>
</table>
</div>

<!-- formulário -->
<? if ((
		$row_solicitacao['id_usuario_responsavel']==$row_usuario['IdUsuario'] or 
		$row_solicitacao['id_operador']==$row_usuario['IdUsuario'] or 
		$row_solicitacao['id_analista_orcamento']==$row_usuario['IdUsuario'] or 
		$row_solicitacao['id_executante']==$row_usuario['IdUsuario'] or 
		$row_solicitacao['id_testador']==$row_usuario['IdUsuario']
		) and ($row_solicitacao['situacao']!="solucionada" and $row_solicitacao['situacao']!="reprovada"))
{ ?>
<div class="div_solicitacao_linhas2">
<table cellspacing="0" cellpadding="0">
	<tr>
		<td style="text-align: left">
        <form id="form" name="form" method="POST" action="<?php echo KT_escapeAttribute(KT_getFullUri()); ?>" enctype="multipart/form-data" class="cmxform">  
        <div style="padding-bottom: 10px;">
        <div class="label_solicitacao2">Anexar novo arquivo:</div>
        Extensões permitidas: rar, zip, txt, jpeg, jpg, pdf
        <br>
        <input type="file" name="nome_arquivo" id="nome_arquivo" style="width: 400px; padding: 5px;" />
        </div>
        <input type="hidden" name="id_solicitacao" value="<?php echo $row_solicitacao['id']; ?>" />
        <input type="hidden" name="id_usuario_responsavel" id="id_usuario_responsavel" value="<?php echo $row_usuario['IdUsuario']; ?>" />
        <input type="hidden" name="data" id="data" value="<?php echo date('d-m-Y H:i:s'); ?>" />
        <input type="hidden" name="tipo_postagem" id="tipo_postagem" value="Anexo de arquivo" />
        <input type="hidden" name="descricao" id="descricao" value="Novo arquivo anexado" /> 
        
        
        <!-- Botões -->  
        <input type="submit" name="KT_Insert1" id="KT_Insert1" value="Anexar arquivo" class="botao_geral2" style="width: 150px" />  
        <!-- fim - Botões -->
        </form>
        <?php echo $tNGs->displayFieldError("solicitacao_arquivos", "nome_arquivo"); ?>
		<?php	echo $tNGs->getErrorMsg(); ?>
		</td>
	</tr>
</table>
</div>
<? } ?>
<!-- fim - formulário -->


<!-- arquivos já anexados -->
<div class="div_solicitacao_linhas4">
<? if($totalRows_arquivos_anexados > 0) { ?>
<table cellspacing="0" cellpadding="0">
	<tr>
		<td style="text-align: left">
		<strong>Arquivos já anexados:</strong>
        <br>
		<?php do { ?>
            <div style=" margin-top: 10px; margin-bottom: 10px;">
            <a href="arquivos/<?php echo $row_arquivos_anexados['nome_arquivo']; ?>" target="_blank">
            - <?php echo $row_arquivos_anexados['nome_arquivo']; ?> 
            </a>
            
            <!-- botão excluir arquivo -->
			<? if ((
					$row_solicitacao['id_usuario_responsavel']==$row_usuario['IdUsuario'] or 
					$row_solicitacao['id_operador']==$row_usuario['IdUsuario'] or 
					$row_solicitacao['id_analista_orcamento']==$row_usuario['IdUsuario'] or 
					$row_solicitacao['id_executante']==$row_usuario['IdUsuario'] or 
					$row_solicitacao['id_testador']==$row_usuario['IdUsuario']
                    ) and ($row_solicitacao['situacao']!="solucionada" and $row_solicitacao['situacao']!="reprovada"))
            { ?>
            <a href="solicitacao_editar_upload_excluir.php?id_solicitacao=<?php echo $row_solicitacao['id']; ?>&id_arquivo=<?php echo $row_arquivos_anexados['id_arquivo']; ?>&acao=<? echo $_GET['acao']; ?>" target="_self">
            <img src="imagens/remove.png" border="0" align="top" title="Excluir arquivo" onClick="return confirmaSubmit()">
            </a>
            <? } ?>
            <!-- fim - botão excluir arquivo -->
            
            </div>
        <?php } while ($row_arquivos_anexados = mysql_fetch_assoc($arquivos_anexados)); ?>
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
mysql_free_result($solicitacao);

mysql_free_result($usuario);

mysql_free_result($arquivos_anexados);
?>