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
$formValidation->addField("indice", true, "text", "", "", "", "Informe um índice");
$formValidation->addField("status", true, "int", "", "", "", "Informe um status");
$formValidation->addField("titulo", true, "text", "", "", "", "Informe um título");
$formValidation->addField("valor", true, "text", "", "", "", "Informe um valor");
$formValidation->addField("positivo_negativo", true, "text", "", "", "", "Informe...");
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
$ins_relatorio_evento = new tNG_multipleInsert($conn_conexao);
$tNGs->addTransaction($ins_relatorio_evento);
// Register triggers
$ins_relatorio_evento->registerTrigger("STARTER", "Trigger_Default_Starter", 1, "POST", "KT_Insert1");
$ins_relatorio_evento->registerTrigger("BEFORE", "Trigger_Default_FormValidation", 10, $formValidation);
$ins_relatorio_evento->registerTrigger("END", "Trigger_Default_Redirect", 99, "listar.php");
// Add columns
$ins_relatorio_evento->setTable("relatorio_evento");
$ins_relatorio_evento->addColumn("status", "NUMERIC_TYPE", "POST", "status");
$ins_relatorio_evento->addColumn("positivo_negativo", "STRING_TYPE", "POST", "positivo_negativo");
$ins_relatorio_evento->addColumn("titulo", "STRING_TYPE", "POST", "titulo");
$ins_relatorio_evento->addColumn("valor", "NUMERIC_TYPE", "POST", "valor");
$ins_relatorio_evento->addColumn("indice", "NUMERIC_TYPE", "POST", "indice");
$ins_relatorio_evento->setPrimaryKey("id", "NUMERIC_TYPE");

// Make an update transaction instance
$upd_relatorio_evento = new tNG_multipleUpdate($conn_conexao);
$tNGs->addTransaction($upd_relatorio_evento);
// Register triggers
$upd_relatorio_evento->registerTrigger("STARTER", "Trigger_Default_Starter", 1, "POST", "KT_Update1");
$upd_relatorio_evento->registerTrigger("BEFORE", "Trigger_Default_FormValidation", 10, $formValidation);
$upd_relatorio_evento->registerTrigger("END", "Trigger_Default_Redirect", 99, "listar.php");
// Add columns
$upd_relatorio_evento->setTable("relatorio_evento");
$upd_relatorio_evento->addColumn("status", "NUMERIC_TYPE", "POST", "status");
$upd_relatorio_evento->addColumn("titulo", "STRING_TYPE", "POST", "titulo");
$upd_relatorio_evento->addColumn("positivo_negativo", "STRING_TYPE", "POST", "positivo_negativo");
$upd_relatorio_evento->addColumn("valor", "NUMERIC_TYPE", "POST", "valor");
$upd_relatorio_evento->addColumn("indice", "NUMERIC_TYPE", "POST", "indice");
$upd_relatorio_evento->setPrimaryKey("id", "NUMERIC_TYPE", "GET", "id");

// Make an instance of the transaction object
$del_relatorio_evento = new tNG_multipleDelete($conn_conexao);
$tNGs->addTransaction($del_relatorio_evento);
// Register triggers
$del_relatorio_evento->registerTrigger("STARTER", "Trigger_Default_Starter", 1, "POST", "KT_Delete1");
$del_relatorio_evento->registerTrigger("END", "Trigger_Default_Redirect", 99, "listar.php");
// Add columns
$del_relatorio_evento->setTable("relatorio_evento");
$del_relatorio_evento->setPrimaryKey("id", "NUMERIC_TYPE", "GET", "id");

// Execute all the registered transactions
$tNGs->executeTransactions();

// Get the transaction recordset
$rsrelatorio_evento = $tNGs->getRecordset("relatorio_evento");
$row_rsrelatorio_evento = mysql_fetch_assoc($rsrelatorio_evento);
$totalRows_rsrelatorio_evento = mysql_num_rows($rsrelatorio_evento);
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
if (@$_GET['id'] == "") {
?>
                        <?php echo NXT_getResource("Insert_FH"); ?>
                        <?php 
// else Conditional region1
} else { ?>
                        <?php echo NXT_getResource("Update_FH"); ?>
                        <?php } 
// endif Conditional region1
?> Evento (relatórios)</td>
                        <td align="right"><a href="javascript:history.go(-1);"><img src="../../imagens/botao_voltar.jpg" border="0" /></a></td>
                    </tr>
                </table>
                </div>
                <div class="caminho"><a href="../index.php">Página inicial</a> &gt;&gt; Evento (relatórios)</div>
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
if (@$totalRows_rsrelatorio_evento > 1) {
?>
                            <h2><?php echo NXT_getResource("Record_FH"); ?> <?php echo $cnt1; ?></h2>
                            <?php } 
// endif Conditional region1
?>
                          <table cellpadding="2" cellspacing="0" class="KT_tngtable">
                            <tr>
                              <td class="KT_th">Código:</td>
                              <td><strong><?php echo $row_rsrelatorio_evento['id']; ?></strong></td>
                            </tr>                        

<tr>
<td class="KT_th"><label for="status_<?php echo $cnt1; ?>_1">Status:</label></td>
<td>
<div>
<input <?php if (!(strcmp(KT_escapeAttribute($row_rsrelatorio_evento['status']),"1"))) {echo "checked=\"checked\"";} ?> type="radio" name="status_<?php echo $cnt1; ?>" id="status_<?php echo $cnt1; ?>_2" value="1" />
<label for="status_<?php echo $cnt1; ?>_2">Liberado</label>
</div>

<div>
<input <?php if (!(strcmp(KT_escapeAttribute($row_rsrelatorio_evento['status']),"0"))) {echo "checked=\"checked\"";} ?> type="radio" name="status_<?php echo $cnt1; ?>" id="status_<?php echo $cnt1; ?>_1" value="0" />
<label for="status_<?php echo $cnt1; ?>_1">Bloqueado</label>
</div>
<?php echo $tNGs->displayFieldError("relatorio_evento", "status", $cnt1); ?>
</td>
</tr>


<tr>
<td class="KT_th"><label for="indice_<?php echo $cnt1; ?>">Índice:</label></td>
<td><input type="text" name="indice_<?php echo $cnt1; ?>" id="indice_<?php echo $cnt1; ?>" value="<?php echo KT_escapeAttribute($row_rsrelatorio_evento['indice']); ?>" size="20" maxlength="11" />
<?php echo $tNGs->displayFieldHint("indice");?> <?php echo $tNGs->displayFieldError("relatorio_evento", "indice", $cnt1); ?></td>
</tr>
                          
<tr>
<td class="KT_th"><label for="titulo_<?php echo $cnt1; ?>">Título:</label></td>
<td><input type="text" name="titulo_<?php echo $cnt1; ?>" id="titulo_<?php echo $cnt1; ?>" value="<?php echo KT_escapeAttribute($row_rsrelatorio_evento['titulo']); ?>" size="70" maxlength="50" />
<?php echo $tNGs->displayFieldHint("titulo");?> <?php echo $tNGs->displayFieldError("relatorio_evento", "titulo", $cnt1); ?></td>
</tr>

<tr>
<td class="KT_th"><label for="valor_<?php echo $cnt1; ?>">Valor:</label></td>
<td><input type="text" name="valor_<?php echo $cnt1; ?>" id="valor_<?php echo $cnt1; ?>" value="<?php echo KT_escapeAttribute($row_rsrelatorio_evento['valor']); ?>" size="20" maxlength="11" />
<?php echo $tNGs->displayFieldHint("valor");?> <?php echo $tNGs->displayFieldError("relatorio_evento", "valor", $cnt1); ?></td>
</tr>

<tr>
<td class="KT_th"><label for="positivo_negativo_<?php echo $cnt1; ?>">P/N:</label></td>
<td>
<select name="positivo_negativo_<?php echo $cnt1; ?>" id="positivo_negativo_<?php echo $cnt1; ?>">
<option value="p" <?php if (!(strcmp("p", KT_escapeAttribute($row_rsrelatorio_evento['positivo_negativo'])))) {echo "SELECTED";} ?>>Positivo</option>
<option value="n" <?php if (!(strcmp("n", KT_escapeAttribute($row_rsrelatorio_evento['positivo_negativo'])))) {echo "SELECTED";} ?>>Negativo</option>
</select>
<?php echo $tNGs->displayFieldHint("positivo_negativo");?> <?php echo $tNGs->displayFieldError("relatorio_evento", "positivo_negativo", $cnt1); ?></td>
</tr>

                          </table>
                      <input type="hidden" name="kt_pk_relatorio_evento_<?php echo $cnt1; ?>" class="id_field" value="<?php echo KT_escapeAttribute($row_rsrelatorio_evento['kt_pk_relatorio_evento']); ?>" />
                          <?php } while ($row_rsrelatorio_evento = mysql_fetch_assoc($rsrelatorio_evento)); ?>
                        <div class="KT_bottombuttons">
                          <div>
                            <?php 
      // Show IF Conditional region1
      if (@$_GET['id'] == "") {
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
