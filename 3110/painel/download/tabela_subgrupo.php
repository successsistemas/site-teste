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

$currentPage = $_SERVER["PHP_SELF"];

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
$formValidation->addField("label", true, "text", "", "", "", "Informe o título da categoria");
$tNGs->prepareValidation($formValidation);
// End trigger

// Make an insert transaction instance
$ins_downloads_subgrupos = new tNG_multipleInsert($conn_conexao);
$tNGs->addTransaction($ins_downloads_subgrupos);
// Register triggers
$ins_downloads_subgrupos->registerTrigger("STARTER", "Trigger_Default_Starter", 1, "POST", "KT_Insert1");
$ins_downloads_subgrupos->registerTrigger("BEFORE", "Trigger_Default_FormValidation", 10, $formValidation);
$ins_downloads_subgrupos->registerTrigger("END", "Trigger_Default_Redirect", 99, "listar_subgrupo.php?id_download_grupo={GET.id_download_grupo}");
// Add columns
$ins_downloads_subgrupos->setTable("downloads_subgrupos");
$ins_downloads_subgrupos->addColumn("id_download_grupo", "NUMERIC_TYPE", "POST", "id_download_grupo");
$ins_downloads_subgrupos->addColumn("label", "STRING_TYPE", "POST", "label");
$ins_downloads_subgrupos->addColumn("descricao", "STRING_TYPE", "POST", "descricao");
$ins_downloads_subgrupos->setPrimaryKey("id_download_subgrupo", "NUMERIC_TYPE");

// Make an update transaction instance
$upd_downloads_subgrupos = new tNG_multipleUpdate($conn_conexao);
$tNGs->addTransaction($upd_downloads_subgrupos);
// Register triggers
$upd_downloads_subgrupos->registerTrigger("STARTER", "Trigger_Default_Starter", 1, "POST", "KT_Update1");
$upd_downloads_subgrupos->registerTrigger("BEFORE", "Trigger_Default_FormValidation", 10, $formValidation);
$upd_downloads_subgrupos->registerTrigger("END", "Trigger_Default_Redirect", 99, "listar_subgrupo.php?id_download_grupo={GET.id_download_grupo}");
// Add columns
$upd_downloads_subgrupos->setTable("downloads_subgrupos");
$upd_downloads_subgrupos->addColumn("id_download_grupo", "NUMERIC_TYPE", "POST", "id_download_grupo");
$upd_downloads_subgrupos->addColumn("label", "STRING_TYPE", "POST", "label");
$upd_downloads_subgrupos->addColumn("descricao", "STRING_TYPE", "POST", "descricao");
$upd_downloads_subgrupos->setPrimaryKey("id_download_subgrupo", "NUMERIC_TYPE", "GET", "id_download_subgrupo");

// Make an instance of the transaction object
$del_downloads_subgrupos = new tNG_multipleDelete($conn_conexao);
$tNGs->addTransaction($del_downloads_subgrupos);
// Register triggers
$del_downloads_subgrupos->registerTrigger("STARTER", "Trigger_Default_Starter", 1, "POST", "KT_Delete1");
$del_downloads_subgrupos->registerTrigger("END", "Trigger_Default_Redirect", 99, "listar_subgrupo.php?id_download_grupo={GET.id_download_grupo}");
// Add columns
$del_downloads_subgrupos->setTable("downloads_subgrupos");
$del_downloads_subgrupos->setPrimaryKey("id_download_subgrupo", "NUMERIC_TYPE", "GET", "id_download_subgrupo");

// Execute all the registered transactions
$tNGs->executeTransactions();

// Get the transaction recordset
$rsdownloads_subgrupos = $tNGs->getRecordset("downloads_subgrupos");
$row_rsdownloads_subgrupos = mysql_fetch_assoc($rsdownloads_subgrupos);
$totalRows_rsdownloads_subgrupos = mysql_num_rows($rsdownloads_subgrupos);
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
  duplicate_buttons: false,
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
                        if (@$_GET['id_download_subgrupo'] == "") {
                        ?>
                              <?php echo NXT_getResource("Insert_FH"); ?>
                              <?php 
                        // else Conditional region1
                        } else { ?>
                              <?php echo NXT_getResource("Update_FH"); ?>
                              <?php } 
                        // endif Conditional region1
                        ?>
                        Subgrupo de Download
                        </td>
                        <td align="right"><a href="javascript:history.go(-1);"><img src="../../imagens/botao_voltar.jpg" border="0" /></a></td>
                    </tr>
                </table>
                </div>
                <div class="caminho"><a href="../index.php">Página inicial</a> &gt;&gt; Subgrupo de Download</div>
                <!-- fim - titulo -->
                
                <div class="conteudo">
                
<?php echo $tNGs->getErrorMsg(); ?>

<div class="KT_tng">

    <div style="margin-top: 5px; margin-bottom: 5px; font-weight:bold; color:#C00; font-size: 16px;">
    Categoria: <span style="color: #000"><?php echo $row_categoria['label']; ?></span>
    </div>
    

  <div class="KT_tngform">
    <form method="post" id="form1" action="<?php echo KT_escapeAttribute(KT_getFullUri()); ?>">
      <?php $cnt1 = 0; ?>
      <?php do { ?>
        <?php $cnt1++; ?>
        <?php 
// Show IF Conditional region1 
if (@$totalRows_rsdownloads_subgrupos > 1) {
?>
          <h2><?php echo NXT_getResource("Record_FH"); ?> <?php echo $cnt1; ?></h2>
          <?php } 
// endif Conditional region1
?>
        <table cellpadding="2" cellspacing="0" class="KT_tngtable">
          <tr>
            <td class="KT_th"><label for="label_<?php echo $cnt1; ?>">Título:</label></td>
            <td><input type="text" name="label_<?php echo $cnt1; ?>" id="label_<?php echo $cnt1; ?>" value="<?php echo KT_escapeAttribute($row_rsdownloads_subgrupos['label']); ?>" size="100" maxlength="100" />
                <?php echo $tNGs->displayFieldHint("label");?> <?php echo $tNGs->displayFieldError("downloads_subgrupos", "label", $cnt1); ?> </td>
          </tr>
          <tr>
            <td class="KT_th"><label for="descricao_<?php echo $cnt1; ?>">Descrição:</label></td>
            <td><input type="text" name="descricao_<?php echo $cnt1; ?>" id="descricao_<?php echo $cnt1; ?>" value="<?php echo KT_escapeAttribute($row_rsdownloads_subgrupos['descricao']); ?>" size="100" maxlength="100" />
                <?php echo $tNGs->displayFieldHint("descricao");?> <?php echo $tNGs->displayFieldError("downloads_subgrupos", "descricao", $cnt1); ?> </td>
          </tr>
        </table>
        <input type="hidden" name="kt_pk_downloads_subgrupos_<?php echo $cnt1; ?>" class="id_field" value="<?php echo KT_escapeAttribute($row_rsdownloads_subgrupos['kt_pk_downloads_subgrupos']); ?>" />
        <input type="hidden" name="id_download_grupo_<?php echo $cnt1; ?>" id="id_download_grupo_<?php echo $cnt1; ?>" value="<?php echo $_GET['id_download_grupo']; ?>" />
        <?php } while ($row_rsdownloads_subgrupos = mysql_fetch_assoc($rsdownloads_subgrupos)); ?>
      <div class="KT_bottombuttons">
        <div>
          <?php 
      // Show IF Conditional region1
      if (@$_GET['id_download_subgrupo'] == "") {
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
          <input type="button" name="KT_Cancel1" value="<?php echo NXT_getResource("Cancel_FB"); ?>" onclick="return UNI_navigateCancel(event, 'downloads_subgrupos.php?id_download_grupo=<?php echo $_GET['id_download_grupo']; ?>')" />
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
mysql_free_result($downloads_grupos);
?>