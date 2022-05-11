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
$formValidation->addField("descricao", true, "text", "", "", "", "Informe a pergunta");
$formValidation->addField("tipo", true, "text", "", "", "", "Informe o tipo");
$tNGs->prepareValidation($formValidation);
// End trigger

//start Trigger_DeleteDetail trigger
//remove this line if you want to edit the code by hand
function Trigger_DeleteDetail(&$tNG) {
  $tblDelObj = new tNG_DeleteDetailRec($tNG);
  $tblDelObj->setTable("implantacao_avaliacao_resposta");
  $tblDelObj->setFieldName("IdImplantacaoPergunta");
  return $tblDelObj->Execute();
}
//end Trigger_DeleteDetail trigger

// Make an insert transaction instance
$ins_implantacao_avaliacao_pergunta = new tNG_multipleInsert($conn_conexao);
$tNGs->addTransaction($ins_implantacao_avaliacao_pergunta);
// Register triggers
$ins_implantacao_avaliacao_pergunta->registerTrigger("STARTER", "Trigger_Default_Starter", 1, "POST", "KT_Insert1");
$ins_implantacao_avaliacao_pergunta->registerTrigger("BEFORE", "Trigger_Default_FormValidation", 10, $formValidation);
$ins_implantacao_avaliacao_pergunta->registerTrigger("END", "Trigger_Default_Redirect", 99, "listar_avaliacao_pergunta.php?id={implantacao.id}");
// Add columns
$ins_implantacao_avaliacao_pergunta->setTable("implantacao_avaliacao_pergunta");
$ins_implantacao_avaliacao_pergunta->addColumn("tipo", "STRING_TYPE", "POST", "tipo");
$ins_implantacao_avaliacao_pergunta->addColumn("data", "DATE_TYPE", "POST", "data");
$ins_implantacao_avaliacao_pergunta->addColumn("descricao", "STRING_TYPE", "POST", "descricao");
$ins_implantacao_avaliacao_pergunta->addColumn("campo_texto", "STRING_TYPE", "POST", "campo_texto");
$ins_implantacao_avaliacao_pergunta->addColumn("campo_texto_label", "STRING_TYPE", "POST", "campo_texto_label");
$ins_implantacao_avaliacao_pergunta->addColumn("observacao", "STRING_TYPE", "POST", "observacao");
$ins_implantacao_avaliacao_pergunta->setPrimaryKey("IdImplantacaoPergunta", "NUMERIC_TYPE");

// Make an update transaction instance
$upd_implantacao_avaliacao_pergunta = new tNG_multipleUpdate($conn_conexao);
$tNGs->addTransaction($upd_implantacao_avaliacao_pergunta);
// Register triggers
$upd_implantacao_avaliacao_pergunta->registerTrigger("STARTER", "Trigger_Default_Starter", 1, "POST", "KT_Update1");
$upd_implantacao_avaliacao_pergunta->registerTrigger("BEFORE", "Trigger_Default_FormValidation", 10, $formValidation);
$upd_implantacao_avaliacao_pergunta->registerTrigger("END", "Trigger_Default_Redirect", 99, "listar_avaliacao_pergunta.php?id={implantacao.id}");
// Add columns
$upd_implantacao_avaliacao_pergunta->setTable("implantacao_avaliacao_pergunta");
$upd_implantacao_avaliacao_pergunta->addColumn("tipo", "STRING_TYPE", "POST", "tipo");
$upd_implantacao_avaliacao_pergunta->addColumn("data", "DATE_TYPE", "POST", "data");
$upd_implantacao_avaliacao_pergunta->addColumn("descricao", "STRING_TYPE", "POST", "descricao");
$upd_implantacao_avaliacao_pergunta->addColumn("campo_texto", "STRING_TYPE", "POST", "campo_texto");
$upd_implantacao_avaliacao_pergunta->addColumn("campo_texto_label", "STRING_TYPE", "POST", "campo_texto_label");
$upd_implantacao_avaliacao_pergunta->addColumn("observacao", "STRING_TYPE", "POST", "observacao");
$upd_implantacao_avaliacao_pergunta->setPrimaryKey("IdImplantacaoPergunta", "NUMERIC_TYPE", "GET", "IdImplantacaoPergunta");

// Make an instance of the transaction object
$del_implantacao_avaliacao_pergunta = new tNG_multipleDelete($conn_conexao);
$tNGs->addTransaction($del_implantacao_avaliacao_pergunta);
// Register triggers
$del_implantacao_avaliacao_pergunta->registerTrigger("STARTER", "Trigger_Default_Starter", 1, "POST", "KT_Delete1");
$del_implantacao_avaliacao_pergunta->registerTrigger("END", "Trigger_Default_Redirect", 99, "listar_avaliacao_pergunta.php?id={implantacao.id}");
$del_implantacao_avaliacao_pergunta->registerTrigger("BEFORE", "Trigger_DeleteDetail", 99);
// Add columns
$del_implantacao_avaliacao_pergunta->setTable("implantacao_avaliacao_pergunta");
$del_implantacao_avaliacao_pergunta->setPrimaryKey("IdImplantacaoPergunta", "NUMERIC_TYPE", "GET", "IdImplantacaoPergunta");

// Execute all the registered transactions
$tNGs->executeTransactions();

// Get the transaction recordset
$rsimplantacao_avaliacao_pergunta = $tNGs->getRecordset("implantacao_avaliacao_pergunta");
$row_rsimplantacao_avaliacao_pergunta = mysql_fetch_assoc($rsimplantacao_avaliacao_pergunta);
$totalRows_rsimplantacao_avaliacao_pergunta = mysql_num_rows($rsimplantacao_avaliacao_pergunta);
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
                        <td align="left"><?php 
// Show IF Conditional region1 
if (@$_GET['IdImplantacaoPergunta'] == "") {
?>
      <?php echo NXT_getResource("Insert_FH"); ?>
      <?php 
// else Conditional region1
} else { ?>
      <?php echo NXT_getResource("Update_FH"); ?>
      <?php } 
// endif Conditional region1
?> Pergunta - Avaliação de Implantação</td>
                        <td align="right"><a href="javascript:history.go(-1);"><img src="../../imagens/botao_voltar.jpg" border="0" /></a></td>
                    </tr>
                </table>
                </div>
                <div class="caminho"><a href="../index.php">Página inicial</a> &gt;&gt; Pergunta - Avaliação de Implantação</div>
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
    if (@$totalRows_rsimplantacao_avaliacao_pergunta > 1) {
    ?>
          <h2><?php echo NXT_getResource("Record_FH"); ?> <?php echo $cnt1; ?></h2>
          <?php } 
    // endif Conditional region1
    ?>
        <table cellpadding="2" cellspacing="0" class="KT_tngtable">
          <tr>
            <td class="KT_th"><label for="tipo_<?php echo $cnt1; ?>">Tipo:</label></td>
            <td>
    <? if (@$_GET['IdImplantacaoPergunta'] == "") { ?>        
    <select name="tipo_<?php echo $cnt1; ?>" id="tipo_<?php echo $cnt1; ?>">
    <option value="" <?php if (!(strcmp("", KT_escapeAttribute($row_rsimplantacao_avaliacao_pergunta['tipo'])))) {echo "selected=\"selected\"";} ?>>Escolha ...</option>
    <option value="s" <?php if (!(strcmp("s", KT_escapeAttribute($row_rsimplantacao_avaliacao_pergunta['tipo'])))) {echo "selected=\"selected\"";} ?>>Sem alternativas</option>
    <option value="r" <?php if (!(strcmp("r", KT_escapeAttribute($row_rsimplantacao_avaliacao_pergunta['tipo'])))) {echo "selected=\"selected\"";} ?>>Uma alternativa</option>
    <option value="c" <?php if (!(strcmp("c", KT_escapeAttribute($row_rsimplantacao_avaliacao_pergunta['tipo'])))) {echo "selected=\"selected\"";} ?>>Várias alternativas</option>
    </select>
    <? } else { ?>
    
    	<span style="font-weight: bold;">
        <?php if (!(strcmp("s", KT_escapeAttribute($row_rsimplantacao_avaliacao_pergunta['tipo'])))) {echo "Sem alternativas";} ?>
        <?php if (!(strcmp("r", KT_escapeAttribute($row_rsimplantacao_avaliacao_pergunta['tipo'])))) {echo "Uma alternativa";} ?>
        <?php if (!(strcmp("c", KT_escapeAttribute($row_rsimplantacao_avaliacao_pergunta['tipo'])))) {echo "Várias alternativas";} ?>
        </span>
        
        <input type="hidden" name="tipo_<?php echo $cnt1; ?>" id="tipo_<?php echo $cnt1; ?>" value="<?php echo $row_rsimplantacao_avaliacao_pergunta['tipo']; ?>" />
        
    <? } ?>
    <?php echo $tNGs->displayFieldError("implantacao_avaliacao_pergunta", "tipo", $cnt1); ?>
            </td>
          </tr>
          <tr>
            <td class="KT_th"><label for="descricao_<?php echo $cnt1; ?>">Pergunta:</label></td>
            <td>
            <textarea name="descricao_<?php echo $cnt1; ?>" id="descricao_<?php echo $cnt1; ?>" cols="80" rows="5"><?php echo KT_escapeAttribute($row_rsimplantacao_avaliacao_pergunta['descricao']); ?></textarea>
            <?php echo $tNGs->displayFieldHint("descricao");?> <?php echo $tNGs->displayFieldError("implantacao_avaliacao_pergunta", "descricao", $cnt1); ?>
            </td>
          </tr>
          <tr>
            <td class="KT_th"><label for="campo_texto_<?php echo $cnt1; ?>">Campo texto</label></td>
            <td>
    <select name="campo_texto_<?php echo $cnt1; ?>" id="campo_texto_<?php echo $cnt1; ?>">
    <option value="n" <?php if (!(strcmp("n", KT_escapeAttribute($row_rsimplantacao_avaliacao_pergunta['campo_texto'])))) {echo "selected=\"selected\"";} ?>>Não</option>
    <option value="s" <?php if (!(strcmp("s", KT_escapeAttribute($row_rsimplantacao_avaliacao_pergunta['campo_texto'])))) {echo "selected=\"selected\"";} ?>>Sim</option>
    </select>
    <?php echo $tNGs->displayFieldError("implantacao_avaliacao_pergunta", "campo_texto", $cnt1); ?>
    
            </td>
          </tr>
          <tr>
            <td class="KT_th"><label for="campo_texto_label_<?php echo $cnt1; ?>">Campo texto (descrição):</label></td>
            <td>
            <textarea name="campo_texto_label_<?php echo $cnt1; ?>" id="campo_texto_label_<?php echo $cnt1; ?>" cols="80" rows="5"><?php echo KT_escapeAttribute($row_rsimplantacao_avaliacao_pergunta['campo_texto_label']); ?></textarea>
            <?php echo $tNGs->displayFieldHint("campo_texto_label");?> <?php echo $tNGs->displayFieldError("implantacao_avaliacao_pergunta", "campo_texto_label", $cnt1); ?>
            </td>
          </tr>
          
          <tr>
            <td class="KT_th"><label for="observacao_<?php echo $cnt1; ?>">Observações:</label></td>
            <td>
            <textarea name="observacao_<?php echo $cnt1; ?>" id="observacao_<?php echo $cnt1; ?>" cols="80" rows="5"><?php echo KT_escapeAttribute($row_rsimplantacao_avaliacao_pergunta['observacao']); ?></textarea>
            <?php echo $tNGs->displayFieldHint("observacao");?> <?php echo $tNGs->displayFieldError("implantacao_avaliacao_pergunta", "observacao", $cnt1); ?>
            </td>
          </tr>
          
        </table>
        <input type="hidden" name="kt_pk_implantacao_avaliacao_pergunta_<?php echo $cnt1; ?>" class="id_field" value="<?php echo KT_escapeAttribute($row_rsimplantacao_avaliacao_pergunta['kt_pk_implantacao_avaliacao_pergunta']); ?>" />
        <input type="hidden" name="data_<?php echo $cnt1; ?>" id="data_<?php echo $cnt1; ?>" value="<?php echo date('d-m-Y H:i:s'); ?>" />
        <?php } while ($row_rsimplantacao_avaliacao_pergunta = mysql_fetch_assoc($rsimplantacao_avaliacao_pergunta)); ?>
      <div class="KT_bottombuttons">
        <div>
          <?php 
      // Show IF Conditional region1
      if (@$_GET['IdImplantacaoPergunta'] == "") {
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
          <input type="button" name="KT_Cancel1" value="<?php echo NXT_getResource("Cancel_FB"); ?>" onClick="return UNI_navigateCancel(event, 'listar_avaliacao_pergunta.php')" />
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
