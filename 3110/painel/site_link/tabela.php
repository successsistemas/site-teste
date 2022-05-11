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
$formValidation->addField("ordem", true, "text", "", "", "", "Informe a ordem");
$formValidation->addField("titulo", true, "text", "", "", "", "Informe o título");
$tNGs->prepareValidation($formValidation);
// End trigger

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

// Make an insert transaction instance
$ins_site_link = new tNG_multipleInsert($conn_conexao);
$tNGs->addTransaction($ins_site_link);
// Register triggers
$ins_site_link->registerTrigger("STARTER", "Trigger_Default_Starter", 1, "POST", "KT_Insert1");
$ins_site_link->registerTrigger("BEFORE", "Trigger_Default_FormValidation", 10, $formValidation);
$ins_site_link->registerTrigger("END", "Trigger_Default_Redirect", 99, "listar.php");
// Add columns
$ins_site_link->setTable("site_link");
$ins_site_link->addColumn("ordem", "STRING_TYPE", "POST", "ordem");
$ins_site_link->addColumn("titulo", "STRING_TYPE", "POST", "titulo");
$ins_site_link->addColumn("texto", "STRING_TYPE", "POST", "texto");
$ins_site_link->addColumn("link", "STRING_TYPE", "POST", "link");
$ins_site_link->addColumn("link_janela", "STRING_TYPE", "POST", "link_janela");
$ins_site_link->setPrimaryKey("IdLink", "NUMERIC_TYPE");

// Make an update transaction instance
$upd_site_link = new tNG_multipleUpdate($conn_conexao);
$tNGs->addTransaction($upd_site_link);
// Register triggers
$upd_site_link->registerTrigger("STARTER", "Trigger_Default_Starter", 1, "POST", "KT_Update1");
$upd_site_link->registerTrigger("BEFORE", "Trigger_Default_FormValidation", 10, $formValidation);
$upd_site_link->registerTrigger("END", "Trigger_Default_Redirect", 99, "listar.php");
// Add columns
$upd_site_link->setTable("site_link");
$upd_site_link->addColumn("ordem", "STRING_TYPE", "POST", "ordem");
$upd_site_link->addColumn("titulo", "STRING_TYPE", "POST", "titulo");
$upd_site_link->addColumn("texto", "STRING_TYPE", "POST", "texto");
$upd_site_link->addColumn("link", "STRING_TYPE", "POST", "link");
$upd_site_link->addColumn("link_janela", "STRING_TYPE", "POST", "link_janela");
$upd_site_link->setPrimaryKey("IdLink", "NUMERIC_TYPE", "GET", "IdLink");

// Make an instance of the transaction object
$del_site_link = new tNG_multipleDelete($conn_conexao);
$tNGs->addTransaction($del_site_link);
// Register triggers
$del_site_link->registerTrigger("STARTER", "Trigger_Default_Starter", 1, "POST", "KT_Delete1");
$del_site_link->registerTrigger("END", "Trigger_Default_Redirect", 99, "listar.php");
// Add columns
$del_site_link->setTable("site_link");
$del_site_link->setPrimaryKey("IdLink", "NUMERIC_TYPE", "GET", "IdLink");

// Execute all the registered transactions
$tNGs->executeTransactions();

// Get the transaction recordset
$rssite_link = $tNGs->getRecordset("site_link");
$row_rssite_link = mysql_fetch_assoc($rssite_link);
$totalRows_rssite_link = mysql_num_rows($rssite_link);
// fim - usuarios

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

<script src="../../ckeditor/ckeditor.js"></script>

<script type="text/javascript">
$(function(){

	// ckeditor
	CKEDITOR.replace( 'texto_1', {
		height: '200'
	});
  // fim - ckeditor
  
});
</script>

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
if (@$_GET['IdLink'] == "") {
?>
                        <?php echo NXT_getResource("Insert_FH"); ?>
                        <?php 
// else Conditional region1
} else { ?>
                        <?php echo NXT_getResource("Update_FH"); ?>
                        <?php } 
// endif Conditional region1
?> Link</td>
                        <td align="right"><a href="javascript:history.go(-1);"><img src="../../imagens/botao_voltar.jpg" border="0" /></a></td>
                    </tr>
                </table>
                </div>
                <div class="caminho"><a href="../index.php">Página inicial</a> &gt;&gt; Link</div>
                <!-- fim - titulo -->
                
                <div class="conteudo">
                  <?php
	echo $tNGs->getErrorMsg();
?>
                  <div class="KT_tng">
                    <div class="KT_tngform">
                      <form method="post" id="form1" action="<?php echo KT_escapeAttribute(KT_getFullUri()); ?>">
                        <?php $cnt1 = 0; ?>
                        <?php do { ?>
                          <?php $cnt1++; ?>
                          <?php 
// Show IF Conditional region1 
if (@$totalRows_rssite_link > 1) {
?>
                            <h2><?php echo NXT_getResource("Record_FH"); ?> <?php echo $cnt1; ?></h2>
                            <?php } 
// endif Conditional region1
?>
                          <table cellpadding="2" cellspacing="0" class="KT_tngtable">
                            <tr>
                              <td class="KT_th"><label for="ordem_<?php echo $cnt1; ?>">Ordem:</label></td>
                              <td><input type="text" name="ordem_<?php echo $cnt1; ?>" id="ordem_<?php echo $cnt1; ?>" value="<?php echo KT_escapeAttribute($row_rssite_link['ordem']); ?>" size="20" maxlength="2" />
                                <?php echo $tNGs->displayFieldHint("ordem");?> <?php echo $tNGs->displayFieldError("site_link", "ordem", $cnt1); ?></td>
                            </tr>
                            <tr>
                              <td class="KT_th"><label for="titulo_<?php echo $cnt1; ?>">Título:</label></td>
                              <td><input type="text" name="titulo_<?php echo $cnt1; ?>" id="titulo_<?php echo $cnt1; ?>" value="<?php echo KT_escapeAttribute($row_rssite_link['titulo']); ?>" size="100" maxlength="100" />
                                <?php echo $tNGs->displayFieldHint("titulo");?> <?php echo $tNGs->displayFieldError("site_link", "titulo", $cnt1); ?></td>
                            </tr>
                            <tr>
                              <td class="KT_th"><label for="texto_<?php echo $cnt1; ?>">Texto:</label></td>
                              <td><textarea name="texto_<?php echo $cnt1; ?>" id="texto_<?php echo $cnt1; ?>" cols="120" rows="15"><?php echo KT_escapeAttribute($row_rssite_link['texto']); ?></textarea>
                              <?php echo $tNGs->displayFieldHint("texto");?> <?php echo $tNGs->displayFieldError("site_link", "texto", $cnt1); ?></td>
                            </tr>
                            <tr>
                              <td class="KT_th"><label for="link_<?php echo $cnt1; ?>">Link:</label></td>
                              <td><input type="text" name="link_<?php echo $cnt1; ?>" id="link_<?php echo $cnt1; ?>" value="<?php echo KT_escapeAttribute($row_rssite_link['link']); ?>" size="100" />
                              <?php echo $tNGs->displayFieldHint("link");?> <?php echo $tNGs->displayFieldError("site_link", "link", $cnt1); ?></td>
                            </tr>
                            <tr>
                              <td class="KT_th"><label for="link_janela_<?php echo $cnt1; ?>">Link (janela):</label></td>
                              <td><select name="link_janela_<?php echo $cnt1; ?>" id="link_janela_<?php echo $cnt1; ?>">
              <option value="" >Selecione ...</option>
              <option value="_blank" <?php if (!(strcmp("_blank", KT_escapeAttribute($row_rssite_link['link_janela'])))) {echo "SELECTED";} ?>>Nova janela</option>
              <option value="_top" <?php if (!(strcmp("_top", KT_escapeAttribute($row_rssite_link['link_janela'])))) {echo "SELECTED";} ?>>Mesma janela</option>
            </select>
                                <?php echo $tNGs->displayFieldHint("link_janela");?> <?php echo $tNGs->displayFieldError("site_link", "link_janela", $cnt1); ?></td>
                            </tr>
                          </table>
                          <input type="hidden" name="kt_pk_site_link_<?php echo $cnt1; ?>" class="id_field" value="<?php echo KT_escapeAttribute($row_rssite_link['kt_pk_site_link']); ?>" />
                          <?php } while ($row_rssite_link = mysql_fetch_assoc($rssite_link)); ?>
                        <div class="KT_bottombuttons">
                          <div>
                            <?php 
      // Show IF Conditional region1
      if (@$_GET['IdLink'] == "") {
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