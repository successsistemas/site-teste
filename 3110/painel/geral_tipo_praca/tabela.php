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
$formValidation->addField("praca", true, "text", "", "", "", "obrigatório");
$formValidation->addField("estado", true, "text", "", "", "", "obrigatório");
$formValidation->addField("responsavel", true, "text", "", "", "", "obrigatório");
$formValidation->addField("suporte_inloco_sim_prazo_anexo", true, "text", "", "", "", "obrigatório");
$tNGs->prepareValidation($formValidation);
// End trigger

// Make an insert transaction instance
$ins_geral_tipo_praca = new tNG_multipleInsert($conn_conexao);
$tNGs->addTransaction($ins_geral_tipo_praca);
// Register triggers
$ins_geral_tipo_praca->registerTrigger("STARTER", "Trigger_Default_Starter", 1, "POST", "KT_Insert1");
$ins_geral_tipo_praca->registerTrigger("BEFORE", "Trigger_Default_FormValidation", 10, $formValidation);
$ins_geral_tipo_praca->registerTrigger("END", "Trigger_Default_Redirect", 99, "listar.php");
// Add columns
$ins_geral_tipo_praca->setTable("geral_tipo_praca");
$ins_geral_tipo_praca->addColumn("praca", "STRING_TYPE", "POST", "praca");
$ins_geral_tipo_praca->addColumn("estado", "STRING_TYPE", "POST", "estado");
$ins_geral_tipo_praca->addColumn("responsavel", "STRING_TYPE", "POST", "responsavel");
$ins_geral_tipo_praca->addColumn("suporte_inloco_sim_prazo_anexo", "STRING_TYPE", "POST", "responsavel");
$ins_geral_tipo_praca->setPrimaryKey("IdPraca", "NUMERIC_TYPE");

// Make an update transaction instance
$upd_geral_tipo_praca = new tNG_multipleUpdate($conn_conexao);
$tNGs->addTransaction($upd_geral_tipo_praca);
// Register triggers
$upd_geral_tipo_praca->registerTrigger("STARTER", "Trigger_Default_Starter", 1, "POST", "KT_Update1");
$upd_geral_tipo_praca->registerTrigger("BEFORE", "Trigger_Default_FormValidation", 10, $formValidation);
$upd_geral_tipo_praca->registerTrigger("END", "Trigger_Default_Redirect", 99, "listar.php");
// Add columns
$upd_geral_tipo_praca->setTable("geral_tipo_praca");
$upd_geral_tipo_praca->addColumn("praca", "STRING_TYPE", "POST", "praca");
$upd_geral_tipo_praca->addColumn("estado", "STRING_TYPE", "POST", "estado");
$upd_geral_tipo_praca->addColumn("responsavel", "STRING_TYPE", "POST", "responsavel");
$upd_geral_tipo_praca->addColumn("suporte_inloco_sim_prazo_anexo", "STRING_TYPE", "POST", "suporte_inloco_sim_prazo_anexo");
$upd_geral_tipo_praca->setPrimaryKey("IdPraca", "NUMERIC_TYPE", "GET", "IdPraca");

// Make an instance of the transaction object
$del_geral_tipo_praca = new tNG_multipleDelete($conn_conexao);
$tNGs->addTransaction($del_geral_tipo_praca);
// Register triggers
$del_geral_tipo_praca->registerTrigger("STARTER", "Trigger_Default_Starter", 1, "POST", "KT_Delete1");
$del_geral_tipo_praca->registerTrigger("END", "Trigger_Default_Redirect", 99, "listar.php");
// Add columns
$del_geral_tipo_praca->setTable("geral_tipo_praca");
$del_geral_tipo_praca->setPrimaryKey("IdPraca", "NUMERIC_TYPE", "GET", "IdPraca");

// Execute all the registered transactions
$tNGs->executeTransactions();

// Get the transaction recordset
$rsgeral_tipo_praca = $tNGs->getRecordset("geral_tipo_praca");
$row_rsgeral_tipo_praca = mysql_fetch_assoc($rsgeral_tipo_praca);
$totalRows_rsgeral_tipo_praca = mysql_num_rows($rsgeral_tipo_praca);
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
  <?php echo $tNGs->displayValidationRules(); ?>
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

            <!-- praca -->
            <div class="titulo">
              <table width="100%" border="0" cellspacing="0" cellpadding="0">
                <tr>
                  <td align="left"><?php
                                    // Show IF Conditional region1 
                                    if (@$_GET['IdPraca'] == "") {
                                    ?>
                      <?php echo NXT_getResource("Insert_FH"); ?>
                    <?php
                                      // else Conditional region1
                                    } else { ?>
                      <?php echo NXT_getResource("Update_FH"); ?>
                    <?php }
                                    // endif Conditional region1
                    ?> Tipo de Praça</td>
                  <td align="right"><a href="javascript:history.go(-1);"><img src="../../imagens/botao_voltar.jpg" border="0" /></a></td>
                </tr>
              </table>
            </div>
            <div class="caminho"><a href="../index.php">Página inicial</a> &gt;&gt; Tipo de Praça</div>
            <!-- fim - praca -->

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
                      if (@$totalRows_rsgeral_tipo_praca > 1) {
                      ?>
                        <h2><?php echo NXT_getResource("Record_FH"); ?> <?php echo $cnt1; ?></h2>
                      <?php }
                      // endif Conditional region1
                      ?>
                      <table cellpadding="2" cellspacing="0" class="KT_tngtable">
                        <tr>
                          <td class="KT_th">Código:</td>
                          <td><strong><?php echo $row_rsgeral_tipo_praca['IdPraca']; ?></strong></td>
                        </tr>
                        <tr>
                          <td class="KT_th"><label for="praca_<?php echo $cnt1; ?>">Praça:</label></td>
                          <td><input type="text" name="praca_<?php echo $cnt1; ?>" id="praca_<?php echo $cnt1; ?>" value="<?php echo KT_escapeAttribute($row_rsgeral_tipo_praca['praca']); ?>" size="70" maxlength="50" />
                            <?php echo $tNGs->displayFieldHint("praca"); ?> <?php echo $tNGs->displayFieldError("geral_tipo_praca", "praca", $cnt1); ?></td>
                        </tr>
                        <tr>
                          <td class="KT_th"><label for="estado_<?php echo $cnt1; ?>">Estado:</label></td>
                          <td>

                            <select name="estado_<?php echo $cnt1; ?>" id="estado_<?php echo $cnt1; ?>">
                              <option value="MG" <?php if (!(strcmp("MG", KT_escapeAttribute($row_rsgeral_tipo_praca['estado'])))) {
                                                    echo "SELECTED";
                                                  } ?>>MG</option>
                              <option value="DF" <?php if (!(strcmp("DF", KT_escapeAttribute($row_rsgeral_tipo_praca['estado'])))) {
                                                    echo "SELECTED";
                                                  } ?>>DF</option>
                              <option value="GO" <?php if (!(strcmp("GO", KT_escapeAttribute($row_rsgeral_tipo_praca['estado'])))) {
                                                    echo "SELECTED";
                                                  } ?>>GO</option>
                            </select>

                            <?php echo $tNGs->displayFieldHint("estado"); ?> <?php echo $tNGs->displayFieldError("geral_tipo_praca", "estado", $cnt1); ?></td>
                        </tr>
                        <tr>
                          <td class="KT_th"><label for="responsavel_<?php echo $cnt1; ?>">Responsável:</label></td>
                          <td><input type="text" name="responsavel_<?php echo $cnt1; ?>" id="responsavel_<?php echo $cnt1; ?>" value="<?php echo KT_escapeAttribute($row_rsgeral_tipo_praca['responsavel']); ?>" size="70" maxlength="50" />
                            <?php echo $tNGs->displayFieldHint("responsavel"); ?> <?php echo $tNGs->displayFieldError("geral_tipo_praca", "responsavel", $cnt1); ?></td>
                        </tr>

                        <tr>
                          <td class="KT_th"><label for="suporte_inloco_sim_prazo_anexo_<?php echo $cnt1; ?>">Prazo limite para anexar<br>documentos em suportes<br>in loco Sim (dias):</label></td>
                          <td><input type="text" name="suporte_inloco_sim_prazo_anexo_<?php echo $cnt1; ?>" id="suporte_inloco_sim_prazo_anexo_<?php echo $cnt1; ?>" value="<?php echo KT_escapeAttribute($row_rsgeral_tipo_praca['suporte_inloco_sim_prazo_anexo']); ?>" size="20" maxlength="11" />
                            <?php echo $tNGs->displayFieldHint("suporte_inloco_sim_prazo_anexo"); ?> <?php echo $tNGs->displayFieldError("geral_tipo_praca", "suporte_inloco_sim_prazo_anexo", $cnt1); ?></td>
                        </tr>
                      </table>
                      <input type="hidden" name="kt_pk_geral_tipo_praca_<?php echo $cnt1; ?>" class="id_field" value="<?php echo KT_escapeAttribute($row_rsgeral_tipo_praca['kt_pk_geral_tipo_praca']); ?>" />
                    <?php } while ($row_rsgeral_tipo_praca = mysql_fetch_assoc($rsgeral_tipo_praca)); ?>
                    <div class="KT_bottombuttons">
                      <div>
                        <?php
                        // Show IF Conditional region1
                        if (@$_GET['IdPraca'] == "") {
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