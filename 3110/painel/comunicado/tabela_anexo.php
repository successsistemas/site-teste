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

if(
$row_usuario['controle_comunicado'] <> 'Y' and 
$row_usuario['controle_memorando'] <> 'Y' 
){
	header("Location: ../index.php"); 
	exit;
}

// comunicado
$colname_comunicado = "-1";
if (isset($_GET['IdComunicado'])) {
  $colname_comunicado = $_GET['IdComunicado'];
}
mysql_select_db($database_conexao, $conexao);
$query_comunicado = sprintf("
SELECT 
comunicado.*, 
usuarios.nome AS usuario_nome, 
(SELECT COUNT(comunicado_destinatario.IdComunicadoDestinatario) FROM comunicado_destinatario WHERE comunicado_destinatario.IdComunicado = comunicado.IdComunicado and IdComunicadoHistorico IS NULL and comunicado_destinatario.responsavel = 0) AS comunicado_destinatario_contador 
FROM comunicado 
LEFT JOIN usuarios ON usuarios.IdUsuario = comunicado.IdUsuario 
WHERE IdComunicado = %s", 
GetSQLValueString($colname_comunicado, "int"));
$comunicado = mysql_query($query_comunicado, $conexao) or die(mysql_error());
$row_comunicado = mysql_fetch_assoc($comunicado);
$totalRows_comunicado = mysql_num_rows($comunicado);
// fim - comunicado

// Load the common classes
require_once('../../includes/common/KT_common.php');

// Load the tNG classes
require_once('../../includes/tng/tNG.inc.php');

// Load the KT_back class
require_once('../../includes/nxt/KT_back.php');

// Make a transaction dispatcher instance
$tNGs = new tNG_dispatcher("../../");

// Make unified connection variable
$conn_conexao = new KT_connection($conexao, $database_conexao);

// Start trigger
$formValidation = new tNG_FormValidation();
$formValidation->addField("arquivo", true, "", "", "", "", "Selecione uma imagem");
$tNGs->prepareValidation($formValidation);
// End trigger

//start Trigger_FileDelete trigger
//remove this line if you want to edit the code by hand 
function Trigger_FileDelete(&$tNG) {
  $deleteObj = new tNG_FileDelete($tNG);
  $deleteObj->setFolder("../../arquivos/comunicado/");
  $deleteObj->setDbFieldName("arquivo");
  return $deleteObj->Execute();
}
//end Trigger_FileDelete trigger

//start Trigger_ImageUpload trigger
//remove this line if you want to edit the code by hand 
function Trigger_ImageUpload(&$tNG) {
  $uploadObj = new tNG_ImageUpload($tNG);
  $uploadObj->setFormFieldName("arquivo");
  $uploadObj->setDbFieldName("arquivo");
  $uploadObj->setFolder("../../arquivos/comunicado/");
  $uploadObj->setMaxSize(20480);
  $uploadObj->setAllowedExtensions("gif, jpg, jpe, jpeg, png, bmp, pdf, doc, docx, xls, xlsx, ppt, pptx, txt");
  $uploadObj->setRename("auto");
  return $uploadObj->Execute();
}
//end Trigger_ImageUpload trigger

// Make an insert transaction instance
$ins_comunicado_anexo = new tNG_multipleInsert($conn_conexao);
$tNGs->addTransaction($ins_comunicado_anexo);
// Register triggers
$ins_comunicado_anexo->registerTrigger("STARTER", "Trigger_Default_Starter", 1, "POST", "KT_Insert1");
$ins_comunicado_anexo->registerTrigger("BEFORE", "Trigger_Default_FormValidation", 10, $formValidation);
$ins_comunicado_anexo->registerTrigger("END", "Trigger_Default_Redirect", 99, "listar_anexo.php?IdComunicado={comunicado.IdComunicado}");
$ins_comunicado_anexo->registerTrigger("AFTER", "Trigger_ImageUpload", 97);
// Add columns
$ins_comunicado_anexo->setTable("comunicado_anexo");
$ins_comunicado_anexo->addColumn("IdComunicado", "NUMERIC_TYPE", "POST", "IdComunicado");
$ins_comunicado_anexo->addColumn("IdUsuario", "NUMERIC_TYPE", "POST", "IdUsuario");
$ins_comunicado_anexo->addColumn("data_criacao", "DATE_TYPE", "POST", "data");
$ins_comunicado_anexo->addColumn("titulo", "STRING_TYPE", "POST", "titulo");
$ins_comunicado_anexo->addColumn("arquivo", "FILE_TYPE", "FILES", "arquivo");
$ins_comunicado_anexo->setPrimaryKey("IdComunicadoAnexo", "NUMERIC_TYPE");

// Make an update transaction instance
$upd_comunicado_anexo = new tNG_multipleUpdate($conn_conexao);
$tNGs->addTransaction($upd_comunicado_anexo);
// Register triggers
$upd_comunicado_anexo->registerTrigger("STARTER", "Trigger_Default_Starter", 1, "POST", "KT_Update1");
$upd_comunicado_anexo->registerTrigger("BEFORE", "Trigger_Default_FormValidation", 10, $formValidation);
$upd_comunicado_anexo->registerTrigger("END", "Trigger_Default_Redirect", 99, "listar_anexo.php?IdComunicado={comunicado.IdComunicado}");
$upd_comunicado_anexo->registerTrigger("AFTER", "Trigger_ImageUpload", 97);
// Add columns
$upd_comunicado_anexo->setTable("comunicado_anexo");
$upd_comunicado_anexo->addColumn("IdComunicado", "NUMERIC_TYPE", "POST", "IdComunicado");
$upd_comunicado_anexo->addColumn("data_criacao", "DATE_TYPE", "POST", "data");
$upd_comunicado_anexo->addColumn("titulo", "STRING_TYPE", "POST", "titulo");
$upd_comunicado_anexo->addColumn("arquivo", "FILE_TYPE", "FILES", "arquivo");
$upd_comunicado_anexo->setPrimaryKey("IdComunicadoAnexo", "NUMERIC_TYPE", "GET", "IdComunicadoAnexo");

// Make an instance of the transaction object
$del_comunicado_anexo = new tNG_multipleDelete($conn_conexao);
$tNGs->addTransaction($del_comunicado_anexo);
// Register triggers
$del_comunicado_anexo->registerTrigger("STARTER", "Trigger_Default_Starter", 1, "POST", "KT_Delete1");
$del_comunicado_anexo->registerTrigger("END", "Trigger_Default_Redirect", 99, "listar_anexo.php?IdComunicado={comunicado.IdComunicado}");
$del_comunicado_anexo->registerTrigger("AFTER", "Trigger_FileDelete", 98);
// Add columns
$del_comunicado_anexo->setTable("comunicado_anexo");
$del_comunicado_anexo->setPrimaryKey("IdComunicadoAnexo", "NUMERIC_TYPE", "GET", "IdComunicadoAnexo");

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
<meta http-equiv="content-language" content="pt-BR" />
<meta name="language" content="pt-BR" />
<meta name="robots" content="noindex,nofollow" />
<meta name="author" content="Juliano Martins - (34) 8805 3396" />
<meta name="copyright" content=" Copyright © Success Sistemas - Todos os direitos reservados." />
<title>Área de Parceiro - Success Sistemas</title>
<link href="../../css/guia_painel.css" rel="stylesheet" type="text/css">
<script src="../../js/jquery.js"></script>
<link href="../../includes/skins/mxkollection3.css" rel="stylesheet" type="text/css" media="all" />
<script src="../../includes/common/js/base.js" type="text/javascript"></script>
<script src="../../includes/common/js/utility.js" type="text/javascript"></script>
<script src="../../includes/skins/style.js" type="text/javascript"></script>
<?php echo $tNGs->displayValidationRules();?>
<script src="../../includes/nxt/scripts/form.js" type="text/javascript"></script>
<script src="../../includes/nxt/scripts/form.js.php" type="text/javascript"></script>
<script type="text/javascript">
$NXT_FORM_SETTINGS = {
  duplicate_buttons: false,
  show_as_grid: false,
  merge_down_value: true
}
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
                        <td align="left"><?php 
// Show IF Conditional region1 
if (@$_GET['IdComunicadoAnexo'] == "") {
?>
                        <?php echo NXT_getResource("Insert_FH"); ?>
                        <?php 
// else Conditional region1
} else { ?>
                        <?php echo NXT_getResource("Update_FH"); ?>
                        <?php } 
// endif Conditional region1
?> Banner inferior</td>
                        <td align="right"><a href="javascript:history.go(-1);"><img src="../../imagens/botao_voltar.jpg" border="0" /></a></td>
                    </tr>
                </table>
                </div>
                <div class="caminho"><a href="../index.php">Página inicial</a> &gt;&gt; Banner inferior</div>
                <!-- fim - titulo -->
                
                <div class="conteudo">
                  <?php echo $tNGs->getErrorMsg(); ?>
                  <div class="KT_tng">
                  
                    <div style="margin-top: 5px; margin-bottom: 5px; font-weight:bold; font-size: 16px;">
                    <?php echo $row_comunicado['assunto']; ?>
                    </div>
                    Criação: <? echo date('d-m-Y H:i', strtotime($row_comunicado['data_criacao'])); ?> | 
                    Remetente: <? echo $row_comunicado['usuario_nome']; ?> | 
                    Distribuição: 
                    <?php if($row_comunicado['comunicado_destinatario_contador'] == 1){ ?>Individual<? } else { ?>Coletivo<? } ?>
                    (<?php echo $row_comunicado['comunicado_destinatario_contador']; ?>) 
                     | 
                    Prioridade: <? echo $row_comunicado['prioridade']; ?> | 
                    Data resposta: <? echo date('d-m-Y', strtotime($row_comunicado['data_limite'])); ?>
                    <br>
    
                    <div class="KT_tngform">
                      <form method="post" id="form1" action="<?php echo KT_escapeAttribute(KT_getFullUri()); ?>" enctype="multipart/form-data">
                        <?php $cnt1 = 0; ?>
                        <?php do { ?>
                          <?php $cnt1++; ?>
                          <?php 
// Show IF Conditional region1 
if (@$totalRows_rscomunicado_anexo > 1) {
?>
                            <h2><?php echo NXT_getResource("Record_FH"); ?> <?php echo $cnt1; ?></h2>
                            <?php } 
// endif Conditional region1
?>
                          <table cellpadding="2" cellspacing="0" class="KT_tngtable">
                            <tr>
                              <td class="KT_th"><label for="titulo_<?php echo $cnt1; ?>">Título:</label></td>
                              <td><input type="text" name="titulo_<?php echo $cnt1; ?>" id="titulo_<?php echo $cnt1; ?>" value="<?php echo KT_escapeAttribute($row_rscomunicado_anexo['titulo']); ?>" size="100" maxlength="100" />
                                <?php echo $tNGs->displayFieldHint("titulo");?> <?php echo $tNGs->displayFieldError("comunicado_anexo", "titulo", $cnt1); ?></td>
                            </tr>
                            <tr>
                              <td class="KT_th"><label for="arquivo_<?php echo $cnt1; ?>">Arquivo:</label></td>
                              <td>
                              Extensão: gif, jpg, jpe, jpeg, png, bmp, pdf, doc, docx, xls, xlsx, ppt, pptx, txt
                              <br>
                              <input type="file" name="arquivo_<?php echo $cnt1; ?>" id="arquivo_<?php echo $cnt1; ?>" size="32" />
                              <?php echo $tNGs->displayFieldError("comunicado_anexo", "arquivo", $cnt1); ?>
                              <?php if($row_rscomunicado_anexo['arquivo']!=""){ ?>
                              <br>
                              <a href="../../arquivos/comunicado/<?php echo $row_rscomunicado_anexo['arquivo']; ?>" target="_blank"><?php echo $row_rscomunicado_anexo['arquivo']; ?></a>
                              <? } ?>
                              </td>
                            </tr>
                          </table>
                          <input type="hidden" name="IdComunicado_<?php echo $cnt1; ?>" id="IdComunicado_<?php echo $cnt1; ?>" value="<?php echo $row_comunicado['IdComunicado']; ?>" />
                          <input type="hidden" name="IdUsuario_<?php echo $cnt1; ?>" id="IdUsuario_<?php echo $cnt1; ?>" value="<?php echo $row_usuario['IdUsuario']; ?>" />
                          <input type="hidden" name="data_<?php echo $cnt1; ?>" id="data_<?php echo $cnt1; ?>" value="<?php echo date('d-m-Y H:i:s'); ?>" />
                          <input type="hidden" name="kt_pk_comunicado_anexo_<?php echo $cnt1; ?>" class="id_field" value="<?php echo KT_escapeAttribute($row_rscomunicado_anexo['kt_pk_comunicado_anexo']); ?>" />
                          <?php } while ($row_rscomunicado_anexo = mysql_fetch_assoc($rscomunicado_anexo)); ?>
                        <div class="KT_bottombuttons">
                          <div>
                            <?php 
      // Show IF Conditional region1
      if (@$_GET['IdComunicadoAnexo'] == "") {
      ?>
                              <input type="submit" name="KT_Insert1" id="KT_Insert1" value="<?php echo NXT_getResource("Insert_FB"); ?>" />
                              <?php 
      // else Conditional region1
      } else { ?>
                              <div class="KT_operations">
                                <input type="submit" name="KT_Insert1" value="<?php echo NXT_getResource("Insert as new_FB"); ?>" onclick="nxt_form_insertasnew(this, 'IdComunicadoAnexo')" />
                              </div>
                              <input type="submit" name="KT_Update1" value="<?php echo NXT_getResource("Update_FB"); ?>" />
                              <input type="submit" name="KT_Delete1" value="<?php echo NXT_getResource("Delete_FB"); ?>" onclick="return confirm('<?php echo NXT_getResource("Are you sure?"); ?>');" />
                              <?php }
      // endif Conditional region1
      ?>
                            <input type="button" name="KT_Cancel1" value="<?php echo NXT_getResource("Cancel_FB"); ?>" onclick="return UNI_navigateCancel(event, 'listar_anexo.php')" />
                          </div>
                        </div>
                      </form>
                    </div>
                    <br class="clearfixplain" />
                  </div>
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
mysql_free_result($comunicado);
?>
