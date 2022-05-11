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
$formValidation->addField("codigo", true, "", "", "", "", "Selecione uma imagem");
$tNGs->prepareValidation($formValidation);
// End trigger

//start Trigger_FileDelete trigger
//remove this line if you want to edit the code by hand 
function Trigger_FileDelete(&$tNG) {
  $deleteObj = new tNG_FileDelete($tNG);
  $deleteObj->setFolder("../../imagens/site_banner_principal/");
  $deleteObj->setDbFieldName("codigo");
  return $deleteObj->Execute();
}
//end Trigger_FileDelete trigger

//start Trigger_ImageUpload trigger
//remove this line if you want to edit the code by hand 
function Trigger_ImageUpload(&$tNG) {
  $uploadObj = new tNG_ImageUpload($tNG);
  $uploadObj->setFormFieldName("codigo");
  $uploadObj->setDbFieldName("codigo");
  $uploadObj->setFolder("../../imagens/site_banner_principal/");
  $uploadObj->setMaxSize(2048);
  $uploadObj->setAllowedExtensions("gif, jpg, jpe, jpeg, png");
  $uploadObj->setRename("auto");
  return $uploadObj->Execute();
}
//end Trigger_ImageUpload trigger

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

// Make an insert transaction instance
$ins_site_banner_principal = new tNG_multipleInsert($conn_conexao);
$tNGs->addTransaction($ins_site_banner_principal);
// Register triggers
$ins_site_banner_principal->registerTrigger("STARTER", "Trigger_Default_Starter", 1, "POST", "KT_Insert1");
$ins_site_banner_principal->registerTrigger("BEFORE", "Trigger_Default_FormValidation", 10, $formValidation);
$ins_site_banner_principal->registerTrigger("END", "Trigger_Default_Redirect", 99, "listar.php");
$ins_site_banner_principal->registerTrigger("AFTER", "Trigger_ImageUpload", 97);
// Add columns
$ins_site_banner_principal->setTable("site_banner_principal");
$ins_site_banner_principal->addColumn("titulo", "STRING_TYPE", "POST", "titulo");
$ins_site_banner_principal->addColumn("codigo", "FILE_TYPE", "FILES", "codigo");
$ins_site_banner_principal->addColumn("site_link", "STRING_TYPE", "POST", "site_link");
$ins_site_banner_principal->setPrimaryKey("IdBanner", "NUMERIC_TYPE");

// Make an update transaction instance
$upd_site_banner_principal = new tNG_multipleUpdate($conn_conexao);
$tNGs->addTransaction($upd_site_banner_principal);
// Register triggers
$upd_site_banner_principal->registerTrigger("STARTER", "Trigger_Default_Starter", 1, "POST", "KT_Update1");
$upd_site_banner_principal->registerTrigger("BEFORE", "Trigger_Default_FormValidation", 10, $formValidation);
$upd_site_banner_principal->registerTrigger("END", "Trigger_Default_Redirect", 99, "listar.php");
$upd_site_banner_principal->registerTrigger("AFTER", "Trigger_ImageUpload", 97);
// Add columns
$upd_site_banner_principal->setTable("site_banner_principal");
$upd_site_banner_principal->addColumn("titulo", "STRING_TYPE", "POST", "titulo");
$upd_site_banner_principal->addColumn("codigo", "FILE_TYPE", "FILES", "codigo");
$upd_site_banner_principal->addColumn("site_link", "STRING_TYPE", "POST", "site_link");
$upd_site_banner_principal->setPrimaryKey("IdBanner", "NUMERIC_TYPE", "GET", "IdBanner");

// Make an instance of the transaction object
$del_site_banner_principal = new tNG_multipleDelete($conn_conexao);
$tNGs->addTransaction($del_site_banner_principal);
// Register triggers
$del_site_banner_principal->registerTrigger("STARTER", "Trigger_Default_Starter", 1, "POST", "KT_Delete1");
$del_site_banner_principal->registerTrigger("END", "Trigger_Default_Redirect", 99, "listar.php");
$del_site_banner_principal->registerTrigger("AFTER", "Trigger_FileDelete", 98);
// Add columns
$del_site_banner_principal->setTable("site_banner_principal");
$del_site_banner_principal->setPrimaryKey("IdBanner", "NUMERIC_TYPE", "GET", "IdBanner");

// Execute all the registered transactions
$tNGs->executeTransactions();

// Get the transaction recordset
$rssite_banner_principal = $tNGs->getRecordset("site_banner_principal");
$row_rssite_banner_principal = mysql_fetch_assoc($rssite_banner_principal);
$totalRows_rssite_banner_principal = mysql_num_rows($rssite_banner_principal);

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
if (@$_GET['IdBanner'] == "") {
?>
                        <?php echo NXT_getResource("Insert_FH"); ?>
                        <?php 
// else Conditional region1
} else { ?>
                        <?php echo NXT_getResource("Update_FH"); ?>
                        <?php } 
// endif Conditional region1
?> Banner principal</td>
                        <td align="right"><a href="javascript:history.go(-1);"><img src="../../imagens/botao_voltar.jpg" border="0" /></a></td>
                    </tr>
                </table>
                </div>
                <div class="caminho"><a href="../index.php">Página inicial</a> &gt;&gt; Banner principal</div>
                <!-- fim - titulo -->
                
                <div class="conteudo">
                  <?php echo $tNGs->getErrorMsg(); ?>
                  <div class="KT_tng">
                    <div class="KT_tngform">
                      <form method="post" id="form1" action="<?php echo KT_escapeAttribute(KT_getFullUri()); ?>" enctype="multipart/form-data">
                        <?php $cnt1 = 0; ?>
                        <?php do { ?>
                          <?php $cnt1++; ?>
                          <?php 
// Show IF Conditional region1 
if (@$totalRows_rssite_banner_principal > 1) {
?>
                            <h2><?php echo NXT_getResource("Record_FH"); ?> <?php echo $cnt1; ?></h2>
                            <?php } 
// endif Conditional region1
?>
                          <table cellpadding="2" cellspacing="0" class="KT_tngtable">
                            <tr>
                              <td class="KT_th"><label for="titulo_<?php echo $cnt1; ?>">Título:</label></td>
                              <td><input type="text" name="titulo_<?php echo $cnt1; ?>" id="titulo_<?php echo $cnt1; ?>" value="<?php echo KT_escapeAttribute($row_rssite_banner_principal['titulo']); ?>" size="100" maxlength="100" />
                                <?php echo $tNGs->displayFieldHint("titulo");?> <?php echo $tNGs->displayFieldError("site_banner_principal", "titulo", $cnt1); ?></td>
                            </tr>
                            <tr>
                              <td class="KT_th"><label for="codigo_<?php echo $cnt1; ?>">Imagem:</label></td>
                              <td>
                              Tamanho: 760x250 pixels
                              <br>
                              <input type="file" name="codigo_<?php echo $cnt1; ?>" id="codigo_<?php echo $cnt1; ?>" size="32" />
                              <?php echo $tNGs->displayFieldError("site_banner_principal", "codigo", $cnt1); ?>
                              <?php if($row_rssite_banner_principal['codigo']!=""){ ?>
                              <br>
                              <a href="../../imagens/site_banner_principal/<?php echo $row_rssite_banner_principal['codigo']; ?>" target="_blank"><?php echo $row_rssite_banner_principal['codigo']; ?></a>
                              <? } ?>
                              </td>
                            </tr>
                            <tr>
                              <td class="KT_th"><label for="site_link_<?php echo $cnt1; ?>">Link:</label></td>
                              <td><input type="text" name="site_link_<?php echo $cnt1; ?>" id="site_link_<?php echo $cnt1; ?>" value="<?php echo KT_escapeAttribute($row_rssite_banner_principal['site_link']); ?>" size="100" />
                                <?php echo $tNGs->displayFieldHint("site_link");?> <?php echo $tNGs->displayFieldError("site_banner_principal", "site_link", $cnt1); ?></td>
                            </tr>
                          </table>
                          <input type="hidden" name="kt_pk_site_banner_principal_<?php echo $cnt1; ?>" class="id_field" value="<?php echo KT_escapeAttribute($row_rssite_banner_principal['kt_pk_site_banner_principal']); ?>" />
                          <?php } while ($row_rssite_banner_principal = mysql_fetch_assoc($rssite_banner_principal)); ?>
                        <div class="KT_bottombuttons">
                          <div>
                            <?php 
      // Show IF Conditional region1
      if (@$_GET['IdBanner'] == "") {
      ?>
                              <input type="submit" name="KT_Insert1" id="KT_Insert1" value="<?php echo NXT_getResource("Insert_FB"); ?>" />
                              <?php 
      // else Conditional region1
      } else { ?>
                              <div class="KT_operations">
                                <input type="submit" name="KT_Insert1" value="<?php echo NXT_getResource("Insert as new_FB"); ?>" onclick="nxt_form_insertasnew(this, 'IdBanner')" />
                              </div>
                              <input type="submit" name="KT_Update1" value="<?php echo NXT_getResource("Update_FB"); ?>" />
                              <input type="submit" name="KT_Delete1" value="<?php echo NXT_getResource("Delete_FB"); ?>" onclick="return confirm('<?php echo NXT_getResource("Are you sure?"); ?>');" />
                              <?php }
      // endif Conditional region1
      ?>
                            <input type="button" name="KT_Cancel1" value="<?php echo NXT_getResource("Cancel_FB"); ?>" onclick="return UNI_navigateCancel(event, 'listar.php')" />
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
<?php mysql_free_result($usuario); ?>
