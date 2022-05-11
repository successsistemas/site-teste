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
$formValidation->addField("IdTipoModuloCategoria", true, "numeric", "", "", "", "Informe uma categoria");
$formValidation->addField("descricao", true, "text", "", "", "", "Informe um título");
$tNGs->prepareValidation($formValidation);
// End trigger

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

// categoria_listar
mysql_select_db($database_conexao, $conexao);
$query_categoria_listar = "SELECT * FROM geral_tipo_modulo_categoria ORDER BY ordem ASC, IdTipoModuloCategoria ASC";
$categoria_listar = mysql_query($query_categoria_listar, $conexao) or die(mysql_error());
$row_categoria_listar = mysql_fetch_assoc($categoria_listar);
$totalRows_categoria_listar = mysql_num_rows($categoria_listar);
// fim - categoria_listar

// Make an insert transaction instance
$ins_geral_tipo_modulo = new tNG_multipleInsert($conn_conexao);
$tNGs->addTransaction($ins_geral_tipo_modulo);
// Register triggers
$ins_geral_tipo_modulo->registerTrigger("STARTER", "Trigger_Default_Starter", 1, "POST", "KT_Insert1");
$ins_geral_tipo_modulo->registerTrigger("BEFORE", "Trigger_Default_FormValidation", 10, $formValidation);
$ins_geral_tipo_modulo->registerTrigger("END", "Trigger_Default_Redirect", 99, "listar.php?IdTipoModuloCategoria={IdTipoModuloCategoria}");
// Add columns
$ins_geral_tipo_modulo->setTable("geral_tipo_modulo");
$ins_geral_tipo_modulo->addColumn("IdTipoModuloCategoria", "NUMERIC_TYPE", "POST", "IdTipoModuloCategoria");
$ins_geral_tipo_modulo->addColumn("descricao", "STRING_TYPE", "POST", "descricao");
$ins_geral_tipo_modulo->addColumn("implantacao_tempo", "NUMERIC_TYPE", "POST", "implantacao_tempo");
$ins_geral_tipo_modulo->addColumn("ordem", "NUMERIC_TYPE", "POST", "ordem");
$ins_geral_tipo_modulo->setPrimaryKey("IdTipoModulo", "NUMERIC_TYPE");

// Make an update transaction instance
$upd_geral_tipo_modulo = new tNG_multipleUpdate($conn_conexao);
$tNGs->addTransaction($upd_geral_tipo_modulo);
// Register triggers
$upd_geral_tipo_modulo->registerTrigger("STARTER", "Trigger_Default_Starter", 1, "POST", "KT_Update1");
$upd_geral_tipo_modulo->registerTrigger("BEFORE", "Trigger_Default_FormValidation", 10, $formValidation);
$upd_geral_tipo_modulo->registerTrigger("END", "Trigger_Default_Redirect", 99, "listar.php?IdTipoModuloCategoria={IdTipoModuloCategoria}");
// Add columns
$upd_geral_tipo_modulo->setTable("geral_tipo_modulo");
$upd_geral_tipo_modulo->addColumn("IdTipoModuloCategoria", "NUMERIC_TYPE", "POST", "IdTipoModuloCategoria");
$upd_geral_tipo_modulo->addColumn("descricao", "STRING_TYPE", "POST", "descricao");
$upd_geral_tipo_modulo->addColumn("implantacao_tempo", "NUMERIC_TYPE", "POST", "implantacao_tempo");
$upd_geral_tipo_modulo->addColumn("ordem", "NUMERIC_TYPE", "POST", "ordem");
$upd_geral_tipo_modulo->setPrimaryKey("IdTipoModulo", "NUMERIC_TYPE", "GET", "IdTipoModulo");

// Make an instance of the transaction object
$del_geral_tipo_modulo = new tNG_multipleDelete($conn_conexao);
$tNGs->addTransaction($del_geral_tipo_modulo);
// Register triggers
$del_geral_tipo_modulo->registerTrigger("STARTER", "Trigger_Default_Starter", 1, "POST", "KT_Delete1");
$del_geral_tipo_modulo->registerTrigger("END", "Trigger_Default_Redirect", 99, "listar.php?IdTipoModuloCategoria={IdTipoModuloCategoria}");
// Add columns
$del_geral_tipo_modulo->setTable("geral_tipo_modulo");
$del_geral_tipo_modulo->setPrimaryKey("IdTipoModulo", "NUMERIC_TYPE", "GET", "IdTipoModulo");

// Execute all the registered transactions
$tNGs->executeTransactions();

// Get the transaction recordset
$rsgeral_tipo_modulo = $tNGs->getRecordset("geral_tipo_modulo");
$row_rsgeral_tipo_modulo = mysql_fetch_assoc($rsgeral_tipo_modulo);
$totalRows_rsgeral_tipo_modulo = mysql_num_rows($rsgeral_tipo_modulo);
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
                
                <!-- descricao -->
                <div class="titulo">
                <table width="100%" border="0" cellspacing="0" cellpadding="0">
                    <tr>
                        <td align="left"><?php 
// Show IF Conditional region1 
if (@$_GET['IdTipoModulo'] == "") {
?>
                        <?php echo NXT_getResource("Insert_FH"); ?>
                        <?php 
// else Conditional region1
} else { ?>
                        <?php echo NXT_getResource("Update_FH"); ?>
                        <?php } 
// endif Conditional region1
?> Tipo de Módulo</td>
                        <td align="right"><a href="javascript:history.go(-1);"><img src="../../imagens/botao_voltar.jpg" border="0" /></a></td>
                    </tr>
                </table>
                </div>
                <div class="caminho"><a href="../index.php">Página inicial</a> &gt;&gt; Tipo de Módulo</div>
                <!-- fim - descricao -->
                
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
if (@$totalRows_rsgeral_tipo_modulo > 1) {
?>
                            <h2><?php echo NXT_getResource("Record_FH"); ?> <?php echo $cnt1; ?></h2>
                            <?php } 
// endif Conditional region1
?>
                          <table cellpadding="2" cellspacing="0" class="KT_tngtable">
                            <tr>
                              <td class="KT_th">Código:</td>
                              <td><strong><?php echo $row_rsgeral_tipo_modulo['IdTipoModulo']; ?></strong></td>
                            </tr>
                            <tr>
<td class="KT_th"><label for="IdTipoModuloCategoria_<?php echo $cnt1; ?>">Categoria:</label></td>
<td>
<select name="IdTipoModuloCategoria_<?php echo $cnt1; ?>" id="IdTipoModuloCategoria_<?php echo $cnt1; ?>">
<option value="" <?php if (!(strcmp("", KT_escapeAttribute($row_rsgeral_tipo_modulo['IdTipoModuloCategoria'])))) {echo "selected=\"selected\"";} ?>>Escolha ...</option>
<?php
do {  
?>
<option value="<?php echo $row_categoria_listar['IdTipoModuloCategoria']?>"<?php if (!(strcmp($row_categoria_listar['IdTipoModuloCategoria'], KT_escapeAttribute($row_rsgeral_tipo_modulo['IdTipoModuloCategoria'])))) {echo "selected=\"selected\"";} ?>><?php echo $row_categoria_listar['descricao']?></option>
<?php
} while ($row_categoria_listar = mysql_fetch_assoc($categoria_listar));
$rows = mysql_num_rows($categoria_listar);
if($rows > 0) {
mysql_data_seek($categoria_listar, 0);
$row_categoria_listar = mysql_fetch_assoc($categoria_listar);
}
?>
</select>
<?php echo $tNGs->displayFieldHint("IdTipoModuloCategoria");?> <?php echo $tNGs->displayFieldError("geral_tipo_modulo", "IdTipoModuloCategoria", $cnt1); ?>
</td>
</tr>

<tr>
<td class="KT_th"><label for="ordem_<?php echo $cnt1; ?>">Ordem:</label></td>
<td><input type="text" name="ordem_<?php echo $cnt1; ?>" id="ordem_<?php echo $cnt1; ?>" value="<?php echo KT_escapeAttribute($row_rsgeral_tipo_modulo['ordem']); ?>" size="20" maxlength="11" />
<?php echo $tNGs->displayFieldHint("ordem");?> <?php echo $tNGs->displayFieldError("geral_tipo_modulo", "ordem", $cnt1); ?></td>
</tr>

      
                            <tr>
                              <td class="KT_th"><label for="descricao_<?php echo $cnt1; ?>">Título:</label></td>
                              <td><input type="text" name="descricao_<?php echo $cnt1; ?>" id="descricao_<?php echo $cnt1; ?>" value="<?php echo KT_escapeAttribute($row_rsgeral_tipo_modulo['descricao']); ?>" size="70" maxlength="50" />
                                <?php echo $tNGs->displayFieldHint("descricao");?> <?php echo $tNGs->displayFieldError("geral_tipo_modulo", "descricao", $cnt1); ?></td>
                            </tr>
<tr>
<td class="KT_th"><label for="implantacao_tempo_<?php echo $cnt1; ?>">Tempo (implantação):</label></td>
<td><input type="text" name="implantacao_tempo_<?php echo $cnt1; ?>" id="implantacao_tempo_<?php echo $cnt1; ?>" value="<?php echo KT_escapeAttribute($row_rsgeral_tipo_modulo['implantacao_tempo']); ?>" size="20" maxlength="11" />
<?php echo $tNGs->displayFieldHint("implantacao_tempo");?> <?php echo $tNGs->displayFieldError("geral_tipo_modulo", "implantacao_tempo", $cnt1); ?></td>
</tr>

                          </table>
                      <input type="hidden" name="kt_pk_geral_tipo_modulo_<?php echo $cnt1; ?>" class="id_field" value="<?php echo KT_escapeAttribute($row_rsgeral_tipo_modulo['kt_pk_geral_tipo_modulo']); ?>" />
                          <?php } while ($row_rsgeral_tipo_modulo = mysql_fetch_assoc($rsgeral_tipo_modulo)); ?>
                        <div class="KT_bottombuttons">
                          <div>
                            <?php 
      // Show IF Conditional region1
      if (@$_GET['IdTipoModulo'] == "") {
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
<?php 
mysql_free_result($usuario); 
mysql_free_result($categoria_listar);
?>
