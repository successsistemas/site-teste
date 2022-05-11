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

// implantacao_pergunta
$colname_implantacao_pergunta = "-1";
if (isset($_GET['IdImplantacaoPergunta'])) {
  $colname_implantacao_pergunta = $_GET['IdImplantacaoPergunta'];
}
mysql_select_db($database_conexao, $conexao);
$query_implantacao_pergunta = sprintf("SELECT * FROM implantacao_pergunta WHERE IdImplantacaoPergunta = %s", GetSQLValueString($colname_implantacao_pergunta, "int"));
$implantacao_pergunta = mysql_query($query_implantacao_pergunta, $conexao) or die(mysql_error());
$row_implantacao_pergunta = mysql_fetch_assoc($implantacao_pergunta);
$totalRows_implantacao_pergunta = mysql_num_rows($implantacao_pergunta);
// fim - implantacao_pergunta

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
$formValidation->addField("descricao", true, "text", "", "", "", "Informe a resposta");
$tNGs->prepareValidation($formValidation);
// End trigger

// Make an insert transaction instance
$ins_implantacao_resposta = new tNG_multipleInsert($conn_conexao);
$tNGs->addTransaction($ins_implantacao_resposta);
// Register triggers
$ins_implantacao_resposta->registerTrigger("STARTER", "Trigger_Default_Starter", 1, "POST", "KT_Insert1");
$ins_implantacao_resposta->registerTrigger("BEFORE", "Trigger_Default_FormValidation", 10, $formValidation);
$ins_implantacao_resposta->registerTrigger("END", "Trigger_Default_Redirect", 99, "listar_resposta.php?id={implantacao.id}&IdImplantacaoPergunta={implantacao_pergunta.IdImplantacaoPergunta}");
// Add columns
$ins_implantacao_resposta->setTable("implantacao_resposta");
$ins_implantacao_resposta->addColumn("IdImplantacaoPergunta", "NUMERIC_TYPE", "POST", "IdImplantacaoPergunta");
$ins_implantacao_resposta->addColumn("data", "DATE_TYPE", "POST", "data");
$ins_implantacao_resposta->addColumn("descricao", "STRING_TYPE", "POST", "descricao");
$ins_implantacao_resposta->setPrimaryKey("IdImplantacaoResposta", "NUMERIC_TYPE");

// Make an update transaction instance
$upd_implantacao_resposta = new tNG_multipleUpdate($conn_conexao);
$tNGs->addTransaction($upd_implantacao_resposta);
// Register triggers
$upd_implantacao_resposta->registerTrigger("STARTER", "Trigger_Default_Starter", 1, "POST", "KT_Update1");
$upd_implantacao_resposta->registerTrigger("BEFORE", "Trigger_Default_FormValidation", 10, $formValidation);
$upd_implantacao_resposta->registerTrigger("END", "Trigger_Default_Redirect", 99, "listar_resposta.php?id={implantacao.id}&IdImplantacaoPergunta={implantacao_pergunta.IdImplantacaoPergunta}");
// Add columns
$upd_implantacao_resposta->setTable("implantacao_resposta");
$upd_implantacao_resposta->addColumn("IdImplantacaoPergunta", "NUMERIC_TYPE", "POST", "IdImplantacaoPergunta");
$upd_implantacao_resposta->addColumn("data", "DATE_TYPE", "POST", "data");
$upd_implantacao_resposta->addColumn("descricao", "STRING_TYPE", "POST", "descricao");
$upd_implantacao_resposta->setPrimaryKey("IdImplantacaoResposta", "NUMERIC_TYPE", "GET", "IdImplantacaoResposta");

// Make an instance of the transaction object
$del_implantacao_resposta = new tNG_multipleDelete($conn_conexao);
$tNGs->addTransaction($del_implantacao_resposta);
// Register triggers
$del_implantacao_resposta->registerTrigger("STARTER", "Trigger_Default_Starter", 1, "POST", "KT_Delete1");
$del_implantacao_resposta->registerTrigger("END", "Trigger_Default_Redirect", 99, "listar_resposta.php?id={implantacao.id}&IdImplantacaoPergunta={implantacao_pergunta.IdImplantacaoPergunta}");
// Add columns
$del_implantacao_resposta->setTable("implantacao_resposta");
$del_implantacao_resposta->setPrimaryKey("IdImplantacaoResposta", "NUMERIC_TYPE", "GET", "IdImplantacaoResposta");

// Execute all the registered transactions
$tNGs->executeTransactions();

// Get the transaction recordset
$rsimplantacao_resposta = $tNGs->getRecordset("implantacao_resposta");
$row_rsimplantacao_resposta = mysql_fetch_assoc($rsimplantacao_resposta);
$totalRows_rsimplantacao_resposta = mysql_num_rows($rsimplantacao_resposta);
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
if (@$_GET['IdImplantacaoResposta'] == "") {
?>
      <?php echo NXT_getResource("Insert_FH"); ?>
      <?php 
// else Conditional region1
} else { ?>
      <?php echo NXT_getResource("Update_FH"); ?>
      <?php } 
// endif Conditional region1
?> Implantação - Pergunta - Resposta</td>
                        <td align="right"><a href="javascript:history.go(-1);"><img src="../../imagens/botao_voltar.jpg" border="0" /></a></td>
                    </tr>
                </table>
                </div>
                <div class="caminho"><a href="../index.php">Página inicial</a> &gt;&gt; Implantação - Pergunta - Resposta</div>
                <!-- fim - titulo -->
                
                <div class="conteudo">
                
<?php echo $tNGs->getErrorMsg(); ?>

<div class="KT_tng">

    <div style="margin-top: 5px; margin-bottom: 5px; font-weight:bold; color:#C00; font-size: 16px;">
	<?php echo $row_implantacao_pergunta['descricao']; ?>
    </div>
    
    <div class="KT_tngform">
    <form method="post" id="form1" action="<?php echo KT_escapeAttribute(KT_getFullUri()); ?>">
      <?php $cnt1 = 0; ?>
      <?php do { ?>
        <?php $cnt1++; ?>
        <?php 
    // Show IF Conditional region1 
    if (@$totalRows_rsimplantacao_resposta > 1) {
    ?>
          <h2><?php echo NXT_getResource("Record_FH"); ?> <?php echo $cnt1; ?></h2>
          <?php } 
    // endif Conditional region1
    ?>
        <table cellpadding="2" cellspacing="0" class="KT_tngtable">
          <tr>
            <td class="KT_th"><label for="descricao_<?php echo $cnt1; ?>">Resposta:</label></td>
            <td><textarea name="descricao_<?php echo $cnt1; ?>" id="descricao_<?php echo $cnt1; ?>" cols="100" rows="5"><?php echo KT_escapeAttribute($row_rsimplantacao_resposta['descricao']); ?></textarea>
              <?php echo $tNGs->displayFieldHint("descricao");?> <?php echo $tNGs->displayFieldError("implantacao_resposta", "descricao", $cnt1); ?></td>
          </tr>
        </table>
        <input type="hidden" name="kt_pk_implantacao_resposta_<?php echo $cnt1; ?>" class="id_field" value="<?php echo KT_escapeAttribute($row_rsimplantacao_resposta['kt_pk_implantacao_resposta']); ?>" />
    
        <input name="IdImplantacaoPergunta_<?php echo $cnt1; ?>" type="hidden" id="IdImplantacaoPergunta_<?php echo $cnt1; ?>" value="<?php echo $row_implantacao_pergunta['IdImplantacaoPergunta']; ?>" />
        <input type="hidden" name="data_<?php echo $cnt1; ?>" id="data_<?php echo $cnt1; ?>" value="<?php echo date('d-m-Y H:i:s'); ?>" />
        <?php } while ($row_rsimplantacao_resposta = mysql_fetch_assoc($rsimplantacao_resposta)); ?>
      <div class="KT_bottombuttons">
        <div>
          <?php 
      // Show IF Conditional region1
      if (@$_GET['IdImplantacaoResposta'] == "") {
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
          <input type="button" name="KT_Cancel1" value="<?php echo NXT_getResource("Cancel_FB"); ?>" onClick="return UNI_navigateCancel(event, 'listar_resposta.php?IdImplantacaoPergunta=<?php echo $row_implantacao_pergunta['IdImplantacaoPergunta']; ?>')" />
        </div>
      </div>
    </form>
    </div>
    
    <br class="clearfixplain" />
    
    <div style="border: 2px solid #CCC; padding: 5px; margin-top: 10px;">
    <a href="listar_pergunta.php">Listar perguntas</a> | 
    <a href="tabela_pergunta.php">Inserir nova pergunta</a>
    </div>
    
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
