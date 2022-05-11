<?php require_once('../padrao_restrito.php'); ?>
<?php require_once('../../Connections/conexao.php'); ?>
<?php
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
$formValidation->addField("foto", true, "", "", "", "", "Selecione a foto");
$tNGs->prepareValidation($formValidation);
// End trigger

//start Trigger_FileDelete trigger
//remove this line if you want to edit the code by hand 
function Trigger_FileDelete(&$tNG) {
  $deleteObj = new tNG_FileDelete($tNG);
  $deleteObj->setFolder("../../imagens/site_link_foto/");
  $deleteObj->setDbFieldName("foto");
  return $deleteObj->Execute();
}
//end Trigger_FileDelete trigger

//start Trigger_FileUpload trigger
//remove this line if you want to edit the code by hand 
function Trigger_FileUpload(&$tNG) {
  $uploadObj = new tNG_FileUpload($tNG);
  $uploadObj->setFormFieldName("foto");
  $uploadObj->setDbFieldName("foto");
  $uploadObj->setFolder("../../imagens/site_link_foto/");
  $uploadObj->setMaxSize(3000);
  $uploadObj->setAllowedExtensions("jpg, jpeg, png, gif, bmp");
  $uploadObj->setRename("auto");
  return $uploadObj->Execute();
}
//end Trigger_FileUpload trigger

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

$colname_site_link = "-1";
if (isset($_GET['IdLink'])) {
  $colname_site_link = $_GET['IdLink'];
}
mysql_select_db($database_conexao, $conexao);
$query_site_link = sprintf("SELECT * FROM site_link WHERE IdLink = %s", GetSQLValueString($colname_site_link, "int"));
$site_link = mysql_query($query_site_link, $conexao) or die(mysql_error());
$row_site_link = mysql_fetch_assoc($site_link);
$totalRows_site_link = mysql_num_rows($site_link);

// Make an insert transaction instance
$ins_site_link_foto = new tNG_multipleInsert($conn_conexao);
$tNGs->addTransaction($ins_site_link_foto);
// Register triggers
$ins_site_link_foto->registerTrigger("STARTER", "Trigger_Default_Starter", 1, "POST", "KT_Insert1");
$ins_site_link_foto->registerTrigger("BEFORE", "Trigger_Default_FormValidation", 10, $formValidation);
$ins_site_link_foto->registerTrigger("END", "Trigger_Default_Redirect", 99, "listar_foto.php?IdLink={site_link.IdLink}");
$ins_site_link_foto->registerTrigger("AFTER", "Trigger_FileUpload", 97);
// Add columns
$ins_site_link_foto->setTable("site_link_foto");
$ins_site_link_foto->addColumn("IdLink", "NUMERIC_TYPE", "POST", "IdLink", "{site_link.IdLink}");
$ins_site_link_foto->addColumn("foto", "FILE_TYPE", "FILES", "foto");
$ins_site_link_foto->setPrimaryKey("IdLinkFoto", "NUMERIC_TYPE");

// Make an update transaction instance
$upd_site_link_foto = new tNG_multipleUpdate($conn_conexao);
$tNGs->addTransaction($upd_site_link_foto);
// Register triggers
$upd_site_link_foto->registerTrigger("STARTER", "Trigger_Default_Starter", 1, "POST", "KT_Update1");
$upd_site_link_foto->registerTrigger("BEFORE", "Trigger_Default_FormValidation", 10, $formValidation);
$upd_site_link_foto->registerTrigger("END", "Trigger_Default_Redirect", 99, "listar_foto.php?IdLink={site_link.IdLink}");
$upd_site_link_foto->registerTrigger("AFTER", "Trigger_FileUpload", 97);
// Add columns
$upd_site_link_foto->setTable("site_link_foto");
$upd_site_link_foto->addColumn("IdLink", "NUMERIC_TYPE", "POST", "IdLink");
$upd_site_link_foto->addColumn("foto", "FILE_TYPE", "FILES", "foto");
$upd_site_link_foto->setPrimaryKey("IdLinkFoto", "NUMERIC_TYPE", "GET", "IdLinkFoto");

// Make an instance of the transaction object
$del_site_link_foto = new tNG_multipleDelete($conn_conexao);
$tNGs->addTransaction($del_site_link_foto);
// Register triggers
$del_site_link_foto->registerTrigger("STARTER", "Trigger_Default_Starter", 1, "POST", "KT_Delete1");
$del_site_link_foto->registerTrigger("END", "Trigger_Default_Redirect", 99, "listar_foto.php?IdLink={site_link.IdLink}");
$del_site_link_foto->registerTrigger("AFTER", "Trigger_FileDelete", 98);
// Add columns
$del_site_link_foto->setTable("site_link_foto");
$del_site_link_foto->setPrimaryKey("IdLinkFoto", "NUMERIC_TYPE", "GET", "IdLinkFoto");

// Execute all the registered transactions
$tNGs->executeTransactions();

// Get the transaction recordset
$rssite_link_foto = $tNGs->getRecordset("site_link_foto");
$row_rssite_link_foto = mysql_fetch_assoc($rssite_link_foto);
$totalRows_rssite_link_foto = mysql_num_rows($rssite_link_foto);
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
  duplicate_buttons: true,
  show_as_grid: true,
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
if (@$_GET['IdLinkFoto'] == "") {
?>
                        <?php echo NXT_getResource("Insert_FH"); ?>
                        <?php 
// else Conditional region1
} else { ?>
                        <?php echo NXT_getResource("Update_FH"); ?>
                        <?php } 
// endif Conditional region1
?> <?php echo $row_site_link['titulo']; ?> - Link (foto) </td>
                        <td align="right"><a href="javascript:history.go(-1);"><img src="../../imagens/botao_voltar.jpg" border="0" /></a></td>
                    </tr>
                </table>
                </div>
                <div class="caminho"><a href="../index.php">Página inicial</a> &gt;&gt; <a href="listar.php"><?php echo $row_site_link['titulo']; ?></a> &gt;&gt; Link (foto)</div>
                <!-- fim - titulo -->
                
                <div class="conteudo">
                  <?php
	echo $tNGs->getErrorMsg();
?>
                  <div class="KT_tng">
                    <div class="KT_tngform">
                      <form method="post" id="form1" action="<?php echo KT_escapeAttribute(KT_getFullUri()); ?>" enctype="multipart/form-data">
                        <?php $cnt1 = 0; ?>
                        <?php do { ?>
                          <?php $cnt1++; ?>
                        <?php 
// Show IF Conditional region1 
if (@$totalRows_rssite_link_foto > 1) {
?>
                          <h2><?php echo NXT_getResource("Record_FH"); ?> <?php echo $cnt1; ?></h2>
                          <?php } 
// endif Conditional region1
?>
                          <table cellpadding="2" cellspacing="0" class="KT_tngtable">
                            <tr>
                              <td class="KT_th"><label for="foto_<?php echo $cnt1; ?>">Foto:</label></td>
                              <td><input type="file" name="foto_<?php echo $cnt1; ?>" id="foto_<?php echo $cnt1; ?>" size="32" />
                                <?php echo $tNGs->displayFieldError("site_link_foto", "foto", $cnt1); ?></td>
                            </tr>
                          </table>
                          <input type="hidden" name="kt_pk_site_link_foto_<?php echo $cnt1; ?>" class="id_field" value="<?php echo KT_escapeAttribute($row_rssite_link_foto['kt_pk_site_link_foto']); ?>" />
                          <input type="hidden" name="IdLink_<?php echo $cnt1; ?>" id="IdLink_<?php echo $cnt1; ?>" value="<?php echo KT_escapeAttribute($row_rssite_link_foto['IdLink']); ?>" />
                          <?php } while ($row_rssite_link_foto = mysql_fetch_assoc($rssite_link_foto)); ?>
                        <div class="KT_bottombuttons">
                          <div>
                            <?php 
      // Show IF Conditional region1
      if (@$_GET['IdLinkFoto'] == "") {
      ?>
                              <input type="submit" name="KT_Insert1" id="KT_Insert1" value="<?php echo NXT_getResource("Insert_FB"); ?>" />
                              <?php 
      // else Conditional region1
      } else { ?>
                              <input type="submit" name="KT_Update1" value="<?php echo NXT_getResource("Update_FB"); ?>" />
                              <input type="submit" name="KT_Delete1" value="<?php echo NXT_getResource("Delete_FB"); ?>" onclick="return confirm('<?php echo NXT_getResource("Are you sure?"); ?>');" />
                              <?php }
      // endif Conditional region1
      ?>
<input type="button" name="KT_Cancel1" value="<?php echo NXT_getResource("Cancel_FB"); ?>" onclick="return UNI_navigateCancel(event, 'listar_foto.php?IdLink=<?php echo $row_site_link['IdLink']; ?>')" />
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
mysql_free_result($site_link);
 mysql_free_result($usuario); ?>
