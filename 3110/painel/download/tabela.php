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

// grupo
$colname_categoria = "-1";
if (isset($_GET['id_download_grupo'])) {
  $colname_categoria = $_GET['id_download_grupo'];
}
mysql_select_db($database_conexao, $conexao);
$query_categoria = sprintf("SELECT * FROM downloads_grupos WHERE id_download_grupo = %s", GetSQLValueString($colname_categoria, "int"));
$categoria = mysql_query($query_categoria, $conexao) or die(mysql_error());
$row_categoria = mysql_fetch_assoc($categoria);
$totalRows_categoria = mysql_num_rows($categoria);
// fim - grupo

// grupo
$colname_subcategoria = "-1";
if (isset($_GET['id_download_subgrupo'])) {
  $colname_subcategoria = $_GET['id_download_subgrupo'];
}
mysql_select_db($database_conexao, $conexao);
$query_subcategoria = sprintf("SELECT * FROM downloads_subgrupos WHERE id_download_subgrupo = %s", GetSQLValueString($colname_subcategoria, "int"));
$subcategoria = mysql_query($query_subcategoria, $conexao) or die(mysql_error());
$row_subcategoria = mysql_fetch_assoc($subcategoria);
$totalRows_subcategoria = mysql_num_rows($subcategoria);
// fim - subgrupo

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
$formValidation->addField("label", true, "text", "", "", "", "Informe o t??tulo");
$tNGs->prepareValidation($formValidation);
// End trigger

// Make an insert transaction instance
$ins_downloads = new tNG_multipleInsert($conn_conexao);
$tNGs->addTransaction($ins_downloads);
// Register triggers
$ins_downloads->registerTrigger("STARTER", "Trigger_Default_Starter", 1, "POST", "KT_Insert1");
$ins_downloads->registerTrigger("BEFORE", "Trigger_Default_FormValidation", 10, $formValidation);
$ins_downloads->registerTrigger("END", "Trigger_Default_Redirect", 99, "listar.php?id_download_grupo={GET.id_download_grupo}&id_download_subgrupo={GET.id_download_subgrupo}");
// Add columns
$ins_downloads->setTable("downloads");
$ins_downloads->addColumn("id_download_subgrupo", "NUMERIC_TYPE", "POST", "id_download_subgrupo");
$ins_downloads->addColumn("label", "STRING_TYPE", "POST", "label");
$ins_downloads->addColumn("link", "STRING_TYPE", "POST", "link");
$ins_downloads->addColumn("tamanho", "STRING_TYPE", "POST", "tamanho");
$ins_downloads->setPrimaryKey("id_download", "NUMERIC_TYPE");

// Make an update transaction instance
$upd_downloads = new tNG_multipleUpdate($conn_conexao);
$tNGs->addTransaction($upd_downloads);
// Register triggers
$upd_downloads->registerTrigger("STARTER", "Trigger_Default_Starter", 1, "POST", "KT_Update1");
$upd_downloads->registerTrigger("BEFORE", "Trigger_Default_FormValidation", 10, $formValidation);
$upd_downloads->registerTrigger("END", "Trigger_Default_Redirect", 99, "listar.php?id_download_grupo={GET.id_download_grupo}&id_download_subgrupo={GET.id_download_subgrupo}");
// Add columns
$upd_downloads->setTable("downloads");
$upd_downloads->addColumn("id_download_subgrupo", "NUMERIC_TYPE", "POST", "id_download_subgrupo");
$upd_downloads->addColumn("label", "STRING_TYPE", "POST", "label");
$upd_downloads->addColumn("link", "STRING_TYPE", "POST", "link");
$upd_downloads->addColumn("tamanho", "STRING_TYPE", "POST", "tamanho");
$upd_downloads->setPrimaryKey("id_download", "NUMERIC_TYPE", "GET", "id_download");

// Make an instance of the transaction object
$del_downloads = new tNG_multipleDelete($conn_conexao);
$tNGs->addTransaction($del_downloads);
// Register triggers
$del_downloads->registerTrigger("STARTER", "Trigger_Default_Starter", 1, "POST", "KT_Delete1");
$del_downloads->registerTrigger("END", "Trigger_Default_Redirect", 99, "listar.php?id_download_grupo={GET.id_download_grupo}&id_download_subgrupo={GET.id_download_subgrupo}");
// Add columns
$del_downloads->setTable("downloads");
$del_downloads->setPrimaryKey("id_download", "NUMERIC_TYPE", "GET", "id_download");

// Execute all the registered transactions
$tNGs->executeTransactions();

// Get the transaction recordset
$rsdownloads = $tNGs->getRecordset("downloads");
$row_rsdownloads = mysql_fetch_assoc($rsdownloads);
$totalRows_rsdownloads = mysql_num_rows($rsdownloads);

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"> 
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="content-language" content="pt-BR" />
<meta name="language" content="pt-BR" />
<meta name="robots" content="noindex,nofollow" />
<meta name="author" content="Juliano Martins - (34) 8805 3396" />
<meta name="copyright" content=" Copyright ?? Success Sistemas - Todos os direitos reservados." />
<title>??rea do Parceiro - Success Sistemas</title>
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
  show_as_grid: false,
  merge_down_value: false
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
                        <td align="left">
						<?php 
                        // Show IF Conditional region1 
                        if (@$_GET['id_download'] == "") {
                        ?>
                              <?php echo NXT_getResource("Insert_FH"); ?>
                              <?php 
                        // else Conditional region1
                        } else { ?>
                              <?php echo NXT_getResource("Update_FH"); ?>
                              <?php } 
                        // endif Conditional region1
                        ?>
                        Download
                        </td>
                        <td align="right"><a href="javascript:history.go(-1);"><img src="../../imagens/botao_voltar.jpg" border="0" /></a></td>
                    </tr>
                </table>
                </div>
                <div class="caminho"><a href="../index.php">P??gina inicial</a> &gt;&gt; Download</div>
                <!-- fim - titulo -->
                
                <div class="conteudo">
                
<?php echo $tNGs->getErrorMsg(); ?>
<div class="KT_tng">

    <div style="margin-top: 5px; margin-bottom: 5px; font-weight:bold; color:#C00; font-size: 16px;">
    Categoria: <span style="color: #000"><?php echo $row_categoria['label']; ?></span>
    <br>
    Subcategoria: <span style="color: #000"><?php echo $row_subcategoria['label']; ?></span>
    </div>

  <div class="KT_tngform">
    <form method="post" id="form1" action="<?php echo KT_escapeAttribute(KT_getFullUri()); ?>">
      <?php $cnt1 = 0; ?>
      <?php do { ?>
        <?php $cnt1++; ?>
        <?php 
// Show IF Conditional region1 
if (@$totalRows_rsdownloads > 1) {
?>
          <h2><?php echo NXT_getResource("Record_FH"); ?> <?php echo $cnt1; ?></h2>
          <?php } 
// endif Conditional region1
?>
        <table cellpadding="2" cellspacing="0" class="KT_tngtable">
          <tr>
            <td class="KT_th"><label for="label_<?php echo $cnt1; ?>">T??tulo:</label></td>
            <td><input type="text" name="label_<?php echo $cnt1; ?>" id="label_<?php echo $cnt1; ?>" value="<?php echo KT_escapeAttribute($row_rsdownloads['label']); ?>" size="100" maxlength="100" />
                <?php echo $tNGs->displayFieldHint("label");?> <?php echo $tNGs->displayFieldError("downloads", "label", $cnt1); ?> </td>
          </tr>
          <tr>
            <td class="KT_th"><label for="link_<?php echo $cnt1; ?>">Link:</label></td>
            <td><input type="text" name="link_<?php echo $cnt1; ?>" id="link_<?php echo $cnt1; ?>" value="<?php echo KT_escapeAttribute($row_rsdownloads['link']); ?>" size="100" />
                <?php echo $tNGs->displayFieldHint("link");?> <?php echo $tNGs->displayFieldError("downloads", "link", $cnt1); ?> </td>
          </tr>
          <tr>
            <td class="KT_th"><label for="tamanho_<?php echo $cnt1; ?>">Tamanho:</label></td>
            <td><input type="text" name="tamanho_<?php echo $cnt1; ?>" id="tamanho_<?php echo $cnt1; ?>" value="<?php echo KT_escapeAttribute($row_rsdownloads['tamanho']); ?>" size="50" maxlength="50" />
                <?php echo $tNGs->displayFieldHint("tamanho");?> <?php echo $tNGs->displayFieldError("downloads", "tamanho", $cnt1); ?> </td>
          </tr>
        </table>
        <input type="hidden" name="kt_pk_downloads_<?php echo $cnt1; ?>" class="id_field" value="<?php echo KT_escapeAttribute($row_rsdownloads['kt_pk_downloads']); ?>" />
        <input type="hidden" name="id_download_subgrupo_<?php echo $cnt1; ?>" id="id_download_subgrupo_<?php echo $cnt1; ?>" value="<?php echo KT_escapeAttribute($row_subcategoria['id_download_subgrupo']); ?>" />
        <?php } while ($row_rsdownloads = mysql_fetch_assoc($rsdownloads)); ?>
      <div class="KT_bottombuttons">
        <div>
          <?php 
      // Show IF Conditional region1
      if (@$_GET['id_download'] == "") {
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
          <input type="button" name="KT_Cancel1" value="<?php echo NXT_getResource("Cancel_FB"); ?>" onclick="return UNI_navigateCancel(event, 'listar.php?id_download_grupo=<?php echo $row_categoria['id_download_grupo']; ?>&id_download_subgrupo=<?php echo $row_subcategoria['id_download_subgrupo']; ?>')" />
          
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
mysql_free_result($categoria);
mysql_free_result($subcategoria);
?>
