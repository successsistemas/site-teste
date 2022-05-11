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
$formValidation->addField("titulo", true, "text", "", "", "", "Informe a pergunta");
$tNGs->prepareValidation($formValidation);
// End trigger

//start Trigger_DeleteDetail trigger
//remove this line if you want to edit the code by hand
function Trigger_DeleteDetail(&$tNG) {
  $tblDelObj = new tNG_DeleteDetailRec($tNG);
  $tblDelObj->setTable("prospeccao_resposta");
  $tblDelObj->setFieldName("IdProcedimentoSite");
  return $tblDelObj->Execute();
}
//end Trigger_DeleteDetail trigger

// Make an insert transaction instance
$ins_geral_procedimento_site = new tNG_multipleInsert($conn_conexao);
$tNGs->addTransaction($ins_geral_procedimento_site);
// Register triggers
$ins_geral_procedimento_site->registerTrigger("STARTER", "Trigger_Default_Starter", 1, "POST", "KT_Insert1");
$ins_geral_procedimento_site->registerTrigger("BEFORE", "Trigger_Default_FormValidation", 10, $formValidation);
$ins_geral_procedimento_site->registerTrigger("END", "Trigger_Default_Redirect", 99, "listar.php?id={prospeccao.id}");
// Add columns
$ins_geral_procedimento_site->setTable("geral_procedimento_site");
$ins_geral_procedimento_site->addColumn("data", "DATE_TYPE", "POST", "data");
$ins_geral_procedimento_site->addColumn("ordem", "STRING_TYPE", "POST", "ordem");
$ins_geral_procedimento_site->addColumn("titulo", "STRING_TYPE", "POST", "titulo");
$ins_geral_procedimento_site->addColumn("descricao", "STRING_TYPE", "POST", "descricao");
$ins_geral_procedimento_site->setPrimaryKey("IdProcedimentoSite", "NUMERIC_TYPE");

// Make an update transaction instance
$upd_geral_procedimento_site = new tNG_multipleUpdate($conn_conexao);
$tNGs->addTransaction($upd_geral_procedimento_site);
// Register triggers
$upd_geral_procedimento_site->registerTrigger("STARTER", "Trigger_Default_Starter", 1, "POST", "KT_Update1");
$upd_geral_procedimento_site->registerTrigger("BEFORE", "Trigger_Default_FormValidation", 10, $formValidation);
$upd_geral_procedimento_site->registerTrigger("END", "Trigger_Default_Redirect", 99, "listar.php?id={prospeccao.id}");
// Add columns
$upd_geral_procedimento_site->setTable("geral_procedimento_site");
$upd_geral_procedimento_site->addColumn("data", "DATE_TYPE", "POST", "data");
$upd_geral_procedimento_site->addColumn("ordem", "STRING_TYPE", "POST", "ordem");
$upd_geral_procedimento_site->addColumn("titulo", "STRING_TYPE", "POST", "titulo");
$upd_geral_procedimento_site->addColumn("descricao", "STRING_TYPE", "POST", "descricao");
$upd_geral_procedimento_site->setPrimaryKey("IdProcedimentoSite", "NUMERIC_TYPE", "GET", "IdProcedimentoSite");

// Make an instance of the transaction object
$del_geral_procedimento_site = new tNG_multipleDelete($conn_conexao);
$tNGs->addTransaction($del_geral_procedimento_site);
// Register triggers
$del_geral_procedimento_site->registerTrigger("STARTER", "Trigger_Default_Starter", 1, "POST", "KT_Delete1");
$del_geral_procedimento_site->registerTrigger("END", "Trigger_Default_Redirect", 99, "listar.php?id={prospeccao.id}");
$del_geral_procedimento_site->registerTrigger("BEFORE", "Trigger_DeleteDetail", 99);
// Add columns
$del_geral_procedimento_site->setTable("geral_procedimento_site");
$del_geral_procedimento_site->setPrimaryKey("IdProcedimentoSite", "NUMERIC_TYPE", "GET", "IdProcedimentoSite");

// Execute all the registered transactions
$tNGs->executeTransactions();

// Get the transaction recordset
$rsgeral_procedimento_site = $tNGs->getRecordset("geral_procedimento_site");
$row_rsgeral_procedimento_site = mysql_fetch_assoc($rsgeral_procedimento_site);
$totalRows_rsgeral_procedimento_site = mysql_num_rows($rsgeral_procedimento_site);
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
                        <td align="left"><?php 
// Show IF Conditional region1 
if (@$_GET['IdProcedimentoSite'] == "") {
?>
      <?php echo NXT_getResource("Insert_FH"); ?>
      <?php 
// else Conditional region1
} else { ?>
      <?php echo NXT_getResource("Update_FH"); ?>
      <?php } 
// endif Conditional region1
?> 
      Procedimento do site</td>
                        <td align="right"><a href="javascript:history.go(-1);"><img src="../../imagens/botao_voltar.jpg" border="0" /></a></td>
                    </tr>
                </table>
                </div>
                <div class="caminho"><a href="../index.php">Página inicial</a> &gt;&gt; Procedimentos do site</div>
                <!-- fim - titulo -->
                
                <div class="conteudo">
                
<?php echo $tNGs->getErrorMsg(); ?>
<div class="KT_tng">
    <div class="KT_tngform">
    <form method="post" id="form1" action="<?php echo KT_escapeAttribute(KT_getFullUri()); ?>">
      <?php $cnt1 = 0; ?>
      <?php do { ?>
        <?php $cnt1++; ?>
        <?php 
    // Show IF Conditional region1 
    if (@$totalRows_rsgeral_procedimento_site > 1) {
    ?>
          <h2><?php echo NXT_getResource("Record_FH"); ?> <?php echo $cnt1; ?></h2>
          <?php } 
    // endif Conditional region1
    ?>
        <table cellpadding="2" cellspacing="0" class="KT_tngtable">
          <tr>
			<td class="KT_th"><label for="ordem_<?php echo $cnt1; ?>">Ordem:</label></td>
            <td>
              <input name="ordem_<?php echo $cnt1; ?>" type="text" id="ordem_<?php echo $cnt1; ?>" value="<?php echo KT_escapeAttribute($row_rsgeral_procedimento_site['ordem']); ?>" size="20" maxlength="15" />
              <?php echo $tNGs->displayFieldHint("ordem");?> <?php echo $tNGs->displayFieldError("geral_procedimento_site", "ordem", $cnt1); ?>
              </td>
          </tr>
          <tr>
            <td class="KT_th"><label for="titulo_<?php echo $cnt1; ?>">Título:</label></td>
            <td>
              <textarea name="titulo_<?php echo $cnt1; ?>" id="titulo_<?php echo $cnt1; ?>" cols="121" rows="5"><?php echo KT_escapeAttribute($row_rsgeral_procedimento_site['titulo']); ?></textarea>
              <?php echo $tNGs->displayFieldHint("titulo");?> <?php echo $tNGs->displayFieldError("geral_procedimento_site", "titulo", $cnt1); ?>
              </td>
          </tr>
          
          <tr>
            <td class="KT_th"><label for="descricao_<?php echo $cnt1; ?>">Descrição:</label></td>
            <td>
              <textarea name="descricao_<?php echo $cnt1; ?>" id="descricao_<?php echo $cnt1; ?>" cols="120" rows="15"><?php echo KT_escapeAttribute($row_rsgeral_procedimento_site['descricao']); ?></textarea>
              
				<script type="text/javascript"> 
                //$(document).ready(function() {

                    // ckeditor
                    CKEDITOR.replace( 'descricao_<?php echo $cnt1; ?>', {
                      height: '200'
                    });
                    // fim - ckeditor
                    
                //});
                </script>
              <?php echo $tNGs->displayFieldHint("descricao");?> <?php echo $tNGs->displayFieldError("geral_procedimento_site", "descricao", $cnt1); ?>
              </td>
          </tr>
          
        </table>
        <input type="hidden" name="kt_pk_geral_procedimento_site_<?php echo $cnt1; ?>" class="id_field" value="<?php echo KT_escapeAttribute($row_rsgeral_procedimento_site['kt_pk_geral_procedimento_site']); ?>" />
        <input type="hidden" name="data_<?php echo $cnt1; ?>" id="data_<?php echo $cnt1; ?>" value="<?php echo date('d-m-Y H:i:s'); ?>" />
        <?php } while ($row_rsgeral_procedimento_site = mysql_fetch_assoc($rsgeral_procedimento_site)); ?>
      <div class="KT_bottombuttons">
        <div>
          <?php 
      // Show IF Conditional region1
      if (@$_GET['IdProcedimentoSite'] == "") {
      ?>
            <input type="submit" name="KT_Insert1" id="KT_Insert1" value="<?php echo NXT_getResource("Insert_FB"); ?>" />
            <?php 
      // else Conditional region1
      } else { ?>
            <input type="submit" name="KT_Update1" value="<?php echo NXT_getResource("Update_FB"); ?>" />
            <input type="submit" name="KT_Delete1" value="<?php echo NXT_getResource("Delete_FB"); ?>" onClick="return confirm('<?php echo NXT_getResource("Are you sure?"); ?>');" />
            <?php }
      // endif Conditional region1
      ?>
          <input type="button" name="KT_Cancel1" value="<?php echo NXT_getResource("Cancel_FB"); ?>" onClick="return UNI_navigateCancel(event, 'listar.php')" />
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
