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

// geral_tipo_modulo
mysql_select_db($database_conexao, $conexao);
$query_geral_tipo_modulo = "SELECT * FROM geral_tipo_modulo ORDER BY IdTipoModulo ASC";
$geral_tipo_modulo = mysql_query($query_geral_tipo_modulo, $conexao) or die(mysql_error());
$row_geral_tipo_modulo = mysql_fetch_assoc($geral_tipo_modulo);
$totalRows_geral_tipo_modulo = mysql_num_rows($geral_tipo_modulo);
// fim - geral_tipo_modulo

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
$formValidation->addField("permite_lancamentos", true, "text", "", "", "", "Informe se permite lançamentos");
//$formValidation->addField("modulo", true, "text", "", "", "", "Informe o módulo");
$formValidation->addField("codigo", true, "text", "", "", "", "Informe o codigo");
$tNGs->prepareValidation($formValidation);
// End trigger

// Make an insert transaction instance
$ins_treinamento_pergunta = new tNG_multipleInsert($conn_conexao);
$tNGs->addTransaction($ins_treinamento_pergunta);
// Register triggers
$ins_treinamento_pergunta->registerTrigger("STARTER", "Trigger_Default_Starter", 1, "POST", "KT_Insert1");
$ins_treinamento_pergunta->registerTrigger("BEFORE", "Trigger_Default_FormValidation", 10, $formValidation);
$ins_treinamento_pergunta->registerTrigger("END", "Trigger_Default_Redirect", 99, "listar_pergunta.php?id={treinamento.id}");
// Add columns
$ins_treinamento_pergunta->setTable("treinamento_pergunta");
$ins_treinamento_pergunta->addColumn("permite_lancamentos", "STRING_TYPE", "POST", "permite_lancamentos");
$ins_treinamento_pergunta->addColumn("id_modulo", "NUMERIC_TYPE", "POST", "id_modulo");
$ins_treinamento_pergunta->addColumn("data", "DATE_TYPE", "POST", "data");
$ins_treinamento_pergunta->addColumn("codigo", "STRING_TYPE", "POST", "codigo");
$ins_treinamento_pergunta->addColumn("descricao", "STRING_TYPE", "POST", "descricao");
$ins_treinamento_pergunta->addColumn("office", "STRING_TYPE", "POST", "office");
$ins_treinamento_pergunta->addColumn("standard", "STRING_TYPE", "POST", "standard");
$ins_treinamento_pergunta->setPrimaryKey("IdTreinamentoPergunta", "NUMERIC_TYPE");

// Make an update transaction instance
$upd_treinamento_pergunta = new tNG_multipleUpdate($conn_conexao);
$tNGs->addTransaction($upd_treinamento_pergunta);
// Register triggers
$upd_treinamento_pergunta->registerTrigger("STARTER", "Trigger_Default_Starter", 1, "POST", "KT_Update1");
$upd_treinamento_pergunta->registerTrigger("BEFORE", "Trigger_Default_FormValidation", 10, $formValidation);
$upd_treinamento_pergunta->registerTrigger("END", "Trigger_Default_Redirect", 99, "listar_pergunta.php?id={treinamento.id}");
// Add columns
$upd_treinamento_pergunta->setTable("treinamento_pergunta");
$upd_treinamento_pergunta->addColumn("permite_lancamentos", "STRING_TYPE", "POST", "permite_lancamentos");
$upd_treinamento_pergunta->addColumn("id_modulo", "NUMERIC_TYPE", "POST", "id_modulo");
$upd_treinamento_pergunta->addColumn("data", "DATE_TYPE", "POST", "data");
$upd_treinamento_pergunta->addColumn("codigo", "STRING_TYPE", "POST", "codigo");
$upd_treinamento_pergunta->addColumn("descricao", "STRING_TYPE", "POST", "descricao");
$upd_treinamento_pergunta->addColumn("office", "STRING_TYPE", "POST", "office");
$upd_treinamento_pergunta->addColumn("standard", "STRING_TYPE", "POST", "standard");
$upd_treinamento_pergunta->setPrimaryKey("IdTreinamentoPergunta", "NUMERIC_TYPE", "GET", "IdTreinamentoPergunta");

// Make an instance of the transaction object
$del_treinamento_pergunta = new tNG_multipleDelete($conn_conexao);
$tNGs->addTransaction($del_treinamento_pergunta);
// Register triggers
$del_treinamento_pergunta->registerTrigger("STARTER", "Trigger_Default_Starter", 1, "POST", "KT_Delete1");
$del_treinamento_pergunta->registerTrigger("END", "Trigger_Default_Redirect", 99, "listar_pergunta.php?id={treinamento.id}");
// Add columns
$del_treinamento_pergunta->setTable("treinamento_pergunta");
$del_treinamento_pergunta->setPrimaryKey("IdTreinamentoPergunta", "NUMERIC_TYPE", "GET", "IdTreinamentoPergunta");

// Execute all the registered transactions
$tNGs->executeTransactions();

// Get the transaction recordset
$rstreinamento_pergunta = $tNGs->getRecordset("treinamento_pergunta");
$row_rstreinamento_pergunta = mysql_fetch_assoc($rstreinamento_pergunta);
$totalRows_rstreinamento_pergunta = mysql_num_rows($rstreinamento_pergunta);
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
4
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
if (@$_GET['IdTreinamentoPergunta'] == "") {
?>
      <?php echo NXT_getResource("Insert_FH"); ?>
      <?php 
// else Conditional region1
} else { ?>
      <?php echo NXT_getResource("Update_FH"); ?>
      <?php } 
// endif Conditional region1
?> Questionário - Pergunta</td>
                        <td align="right"><a href="javascript:history.go(-1);"><img src="../../imagens/botao_voltar.jpg" border="0" /></a></td>
                    </tr>
                </table>
                </div>
                <div class="caminho"><a href="../index.php">Página inicial</a> &gt;&gt; Treinamento - Pergunta</div>
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
    if (@$totalRows_rstreinamento_pergunta > 1) {
    ?>
          <h2><?php echo NXT_getResource("Record_FH"); ?> <?php echo $cnt1; ?></h2>
          <?php } 
    // endif Conditional region1
    ?>
        <table cellpadding="2" cellspacing="0" class="KT_tngtable">
          <tr>
            <td class="KT_th"><label for="permite_lancamentos_<?php echo $cnt1; ?>">Permite Lançamentos:</label></td>
            <td>
    <? if (@$_GET['IdTreinamentoPergunta'] == "") { ?>        
    <select name="permite_lancamentos_<?php echo $cnt1; ?>" id="permite_lancamentos_<?php echo $cnt1; ?>">
    <option value="" <?php if (!(strcmp("", KT_escapeAttribute($row_rstreinamento_pergunta['permite_lancamentos'])))) {echo "selected=\"selected\"";} ?>>Escolha ...</option>
    <option value="s" <?php if (!(strcmp("s", KT_escapeAttribute($row_rstreinamento_pergunta['permite_lancamentos'])))) {echo "selected=\"selected\"";} ?>>Sim</option>
    <option value="n" <?php if (!(strcmp("n", KT_escapeAttribute($row_rstreinamento_pergunta['permite_lancamentos'])))) {echo "selected=\"selected\"";} ?>>Não</option>
    </select>
    <? } else { ?>
    
    	<span style="font-weight: bold;">
        <?php if (!(strcmp("s", KT_escapeAttribute($row_rstreinamento_pergunta['permite_lancamentos'])))) {echo "Sim";} ?>
        <?php if (!(strcmp("n", KT_escapeAttribute($row_rstreinamento_pergunta['permite_lancamentos'])))) {echo "Não";} ?>
        </span>
        
        <input type="hidden" name="permite_lancamentos_<?php echo $cnt1; ?>" id="permite_lancamentos_<?php echo $cnt1; ?>" value="<?php echo $row_rstreinamento_pergunta['permite_lancamentos']; ?>" />
        
    <? } ?>
    <?php echo $tNGs->displayFieldError("treinamento_pergunta", "permite_lancamentos", $cnt1); ?>
            </td>
          </tr>
		<tr>
            <td class="KT_th"><label for="id_modulo_<?php echo $cnt1; ?>">Módulo:</label></td>
            <td>      
    <select name="id_modulo_<?php echo $cnt1; ?>" id="id_modulo_<?php echo $cnt1; ?>">
      <option value="" <?php if (!(strcmp("", KT_escapeAttribute($row_rstreinamento_pergunta['id_modulo'])))) {echo "selected=\"selected\"";} ?>>Escolha ...</option>
      <?php
do {  
?>
      <option value="<?php echo $row_geral_tipo_modulo['IdTipoModulo']?>"<?php if (!(strcmp($row_geral_tipo_modulo['IdTipoModulo'], KT_escapeAttribute($row_rstreinamento_pergunta['id_modulo'])))) {echo "selected=\"selected\"";} ?>><?php echo $row_geral_tipo_modulo['descricao']?></option>
      <?php
} while ($row_geral_tipo_modulo = mysql_fetch_assoc($geral_tipo_modulo));
  $rows = mysql_num_rows($geral_tipo_modulo);
  if($rows > 0) {
      mysql_data_seek($geral_tipo_modulo, 0);
	  $row_geral_tipo_modulo = mysql_fetch_assoc($geral_tipo_modulo);
  }
?>
    </select>
    <?php echo $tNGs->displayFieldError("treinamento_pergunta", "id_modulo", $cnt1); ?>
            </td>
          </tr>
          <tr>
			<td class="KT_th"><label for="codigo_<?php echo $cnt1; ?>">Código:</label></td>
            <td>
            <input type="text" name="codigo_<?php echo $cnt1; ?>" id="codigo_<?php echo $cnt1; ?>" value="<?php echo KT_escapeAttribute($row_rstreinamento_pergunta['codigo']); ?>" size="30" maxlength="20" />
            <?php echo $tNGs->displayFieldHint("codigo");?> <?php echo $tNGs->displayFieldError("treinamento_pergunta", "codigo", $cnt1); ?>
            </td>
          </tr>
          <tr>
            <td class="KT_th"><label for="descricao_<?php echo $cnt1; ?>">Pergunta:</label></td>
            <td>
            <textarea name="descricao_<?php echo $cnt1; ?>" id="descricao_<?php echo $cnt1; ?>" cols="80" rows="5"><?php echo KT_escapeAttribute($row_rstreinamento_pergunta['descricao']); ?></textarea>
            <?php echo $tNGs->displayFieldHint("descricao");?> <?php echo $tNGs->displayFieldError("treinamento_pergunta", "descricao", $cnt1); ?>
            </td>
          </tr>
          <tr>
            <td class="KT_th"><label for="office_<?php echo $cnt1; ?>">Office</label></td>
            <td>
    <select name="office_<?php echo $cnt1; ?>" id="office_<?php echo $cnt1; ?>">
    <option value="s" <?php if (!(strcmp("s", KT_escapeAttribute($row_rstreinamento_pergunta['office'])))) {echo "selected=\"selected\"";} ?>>Sim</option>
    <option value="n" <?php if (!(strcmp("n", KT_escapeAttribute($row_rstreinamento_pergunta['office'])))) {echo "selected=\"selected\"";} ?>>Não</option>
    </select>
    <?php echo $tNGs->displayFieldError("treinamento_pergunta", "office", $cnt1); ?>
    
            </td>
          </tr>
          <tr>
            <td class="KT_th"><label for="standard_<?php echo $cnt1; ?>">Standard</label></td>
            <td>
    <select name="standard_<?php echo $cnt1; ?>" id="standard_<?php echo $cnt1; ?>">
    <option value="s" <?php if (!(strcmp("s", KT_escapeAttribute($row_rstreinamento_pergunta['standard'])))) {echo "selected=\"selected\"";} ?>>Sim</option>
    <option value="n" <?php if (!(strcmp("n", KT_escapeAttribute($row_rstreinamento_pergunta['standard'])))) {echo "selected=\"selected\"";} ?>>Não</option>
    </select>
    <?php echo $tNGs->displayFieldError("treinamento_pergunta", "standard", $cnt1); ?>
    
            </td>
          </tr>
          
        </table>
        <input type="hidden" name="kt_pk_treinamento_pergunta_<?php echo $cnt1; ?>" class="id_field" value="<?php echo KT_escapeAttribute($row_rstreinamento_pergunta['kt_pk_treinamento_pergunta']); ?>" />
        <input type="hidden" name="data_<?php echo $cnt1; ?>" id="data_<?php echo $cnt1; ?>" value="<?php echo date('d-m-Y H:i:s'); ?>" />
        <?php } while ($row_rstreinamento_pergunta = mysql_fetch_assoc($rstreinamento_pergunta)); ?>
      <div class="KT_bottombuttons">
        <div>
          <?php 
      // Show IF Conditional region1
      if (@$_GET['IdTreinamentoPergunta'] == "") {
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
          <input type="button" name="KT_Cancel1" value="<?php echo NXT_getResource("Cancel_FB"); ?>" onClick="return UNI_navigateCancel(event, 'listar_pergunta.php')" />
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
mysql_free_result($geral_tipo_modulo);
?>
