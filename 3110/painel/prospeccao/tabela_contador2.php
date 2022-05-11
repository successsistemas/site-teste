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
$formValidation->addField("razao", true, "text", "", "", "", "obrigatório");
$formValidation->addField("cep", true, "text", "", "", "", "obrigatório");
$formValidation->addField("endereco", true, "text", "", "", "", "obrigatório");
$formValidation->addField("numero", true, "text", "", "", "", "obrigatório");
$formValidation->addField("cidade", true, "text", "", "", "", "obrigatório");
$formValidation->addField("uf", true, "text", "", "", "", "obrigatório");
$formValidation->addField("telefone", true, "text", "", "", "", "obrigatório");
$formValidation->addField("email", false, "text", "email", "", "", "Informe um e-mail válido.");
$formValidation->addField("emptec", true, "text", "", "", "", "obrigatório");
$tNGs->prepareValidation($formValidation);
// End trigger

//start Trigger_DeleteDetail trigger
//remove this line if you want to edit the code by hand
function Trigger_DeleteDetail(&$tNG) {
  $tblDelObj = new tNG_DeleteDetailRec($tNG);
  $tblDelObj->setTable("prospeccao_resposta");
  $tblDelObj->setFieldName("id");
  return $tblDelObj->Execute();
}
//end Trigger_DeleteDetail trigger

// Make an insert transaction instance
$ins_prospeccao_contador = new tNG_multipleInsert($conn_conexao);
$tNGs->addTransaction($ins_prospeccao_contador);
// Register triggers
$ins_prospeccao_contador->registerTrigger("STARTER", "Trigger_Default_Starter", 1, "POST", "KT_Insert1");
$ins_prospeccao_contador->registerTrigger("BEFORE", "Trigger_Default_FormValidation", 10, $formValidation);
$ins_prospeccao_contador->registerTrigger("END", "Trigger_Default_Redirect", 99, "listar_contador.php?id={prospeccao.id}");
// Add columns
$ins_prospeccao_contador->setTable("prospeccao_contador");
$ins_prospeccao_contador->addColumn("razao", "STRING_TYPE", "POST", "razao");
$ins_prospeccao_contador->addColumn("cep", "STRING_TYPE", "POST", "cep");
$ins_prospeccao_contador->addColumn("endereco", "STRING_TYPE", "POST", "endereco");
$ins_prospeccao_contador->addColumn("numero", "STRING_TYPE", "POST", "numero");
$ins_prospeccao_contador->addColumn("cidade", "STRING_TYPE", "POST", "cidade");
$ins_prospeccao_contador->addColumn("uf", "STRING_TYPE", "POST", "uf");
$ins_prospeccao_contador->addColumn("telefone", "STRING_TYPE", "POST", "telefone");
$ins_prospeccao_contador->addColumn("email", "STRING_TYPE", "POST", "email");
$ins_prospeccao_contador->addColumn("emptec", "STRING_TYPE", "POST", "emptec");
$ins_prospeccao_contador->setPrimaryKey("id", "NUMERIC_TYPE");

// Make an update transaction instance
$upd_prospeccao_contador = new tNG_multipleUpdate($conn_conexao);
$tNGs->addTransaction($upd_prospeccao_contador);
// Register triggers
$upd_prospeccao_contador->registerTrigger("STARTER", "Trigger_Default_Starter", 1, "POST", "KT_Update1");
$upd_prospeccao_contador->registerTrigger("BEFORE", "Trigger_Default_FormValidation", 10, $formValidation);
$upd_prospeccao_contador->registerTrigger("END", "Trigger_Default_Redirect", 99, "listar_contador.php?id={prospeccao.id}");
// Add columns
$upd_prospeccao_contador->setTable("prospeccao_contador");
$upd_prospeccao_contador->addColumn("razao", "STRING_TYPE", "POST", "razao");
$upd_prospeccao_contador->addColumn("cep", "STRING_TYPE", "POST", "cep");
$upd_prospeccao_contador->addColumn("endereco", "STRING_TYPE", "POST", "endereco");
$upd_prospeccao_contador->addColumn("numero", "STRING_TYPE", "POST", "numero");
$upd_prospeccao_contador->addColumn("cidade", "STRING_TYPE", "POST", "cidade");
$upd_prospeccao_contador->addColumn("uf", "STRING_TYPE", "POST", "uf");
$upd_prospeccao_contador->addColumn("telefone", "STRING_TYPE", "POST", "telefone");
$upd_prospeccao_contador->addColumn("email", "STRING_TYPE", "POST", "email");
$upd_prospeccao_contador->addColumn("emptec", "STRING_TYPE", "POST", "emptec");
$upd_prospeccao_contador->setPrimaryKey("id", "NUMERIC_TYPE", "GET", "id");

// Make an instance of the transaction object
$del_prospeccao_contador = new tNG_multipleDelete($conn_conexao);
$tNGs->addTransaction($del_prospeccao_contador);
// Register triggers
$del_prospeccao_contador->registerTrigger("STARTER", "Trigger_Default_Starter", 1, "POST", "KT_Delete1");
$del_prospeccao_contador->registerTrigger("END", "Trigger_Default_Redirect", 99, "listar_contador.php?id={prospeccao.id}");
$del_prospeccao_contador->registerTrigger("BEFORE", "Trigger_DeleteDetail", 99);
// Add columns
$del_prospeccao_contador->setTable("prospeccao_contador");
$del_prospeccao_contador->setPrimaryKey("id", "NUMERIC_TYPE", "GET", "id");

// Execute all the registered transactions
$tNGs->executeTransactions();

// Get the transaction recordset
$rsprospeccao_contador = $tNGs->getRecordset("prospeccao_contador");
$row_rsprospeccao_contador = mysql_fetch_assoc($rsprospeccao_contador);
$totalRows_rsprospeccao_contador = mysql_num_rows($rsprospeccao_contador);
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

<script src="../../js/jquery.metadata.js" type="text/javascript"></script>
<script type="text/javascript" src="../../js/jquery.validate.js"></script>

<script type="text/javascript" src="../../js/jquery.numeric.js"></script>

<script type="text/javascript" src="../../js/jquery.maskedinput-1.2.2.min.js"></script> 
<script src="../../js/jquery.price_format.1.3.js" type="text/javascript"></script> 
<script type="text/javascript" src="../../js/jquery.alphanumeric.pack.js"></script> 

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

$(document).ready(function() {
	
	$("#cep_1").numeric();
	$("#telefone_1").numeric();
	
	 $("#baixa_contrato").numeric();
	// fim - validação

	<? if (@$_GET['id'] <> "" and $row_rsprospeccao_contador['uf'] <> "" and $row_rsprospeccao_contador['cidade'] <> "") { ?>
	$.post("consulta_cidade.php", 
		{
		uf: '<? echo $row_rsprospeccao_contador['uf']; ?>', 
		cidade: '<? echo $row_rsprospeccao_contador['cidade']; ?>'
		},
		function(valor){
			$("select[name=cidade]").html(valor);
		}
	);
	<? } ?>
	
	// uf
	$("select[name=uf]").change(function(){
		$("select[name=cidade]").html('<option value="0">Carregando...</option>');
		
		$.post("consulta_cidade.php", 
			{
			uf: $(this).val(), 
			cidade: '<? echo $row_rsprospeccao_contador['cidade']; ?>'
			},
			function(valor){
				$("select[name=cidade]").html(valor);
			}
		);

	})
	// uf - fim
	
});
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
?> Contador</td>
                        <td align="right"><a href="javascript:history.go(-1);"><img src="../../imagens/botao_voltar.jpg" border="0" /></a></td>
                    </tr>
                </table>
                </div>
                <div class="caminho"><a href="../index.php">Página inicial</a> &gt;&gt; Contador</div>
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
    if (@$totalRows_rsprospeccao_contador > 1) {
    ?>
          <h2><?php echo NXT_getResource("Record_FH"); ?> <?php echo $cnt1; ?></h2>
          <?php } 
    // endif Conditional region1
    ?>
        <table cellpadding="2" cellspacing="0" class="KT_tngtable">
			<tr>
				<td class="KT_th"><label for="razao_<?php echo $cnt1; ?>">Nome:</label></td>
				<td>
				<input type="text" name="razao_<?php echo $cnt1; ?>" id="razao_<?php echo $cnt1; ?>" value="<?php echo KT_escapeAttribute($row_rsprospeccao_contador['razao']); ?>" size="90" maxlength="80" />
				<?php echo $tNGs->displayFieldHint("razao");?> <?php echo $tNGs->displayFieldError("prospeccao_contador", "razao", $cnt1); ?>
				</td>
			</tr>

			<tr>
				<td class="KT_th"><label for="cep_<?php echo $cnt1; ?>">CEP:</label></td>
				<td>
				<input type="text" name="cep_<?php echo $cnt1; ?>" id="cep_<?php echo $cnt1; ?>" value="<?php echo KT_escapeAttribute($row_rsprospeccao_contador['cep']); ?>" size="30" maxlength="8" />
				<?php echo $tNGs->displayFieldHint("cep");?> <?php echo $tNGs->displayFieldError("prospeccao_contador", "cep", $cnt1); ?>
				</td>
			</tr>
			
			<tr>
				<td class="KT_th"><label for="endereco_<?php echo $cnt1; ?>">Endereço:</label></td>
				<td>
				<input type="text" name="endereco_<?php echo $cnt1; ?>" id="endereco_<?php echo $cnt1; ?>" value="<?php echo KT_escapeAttribute($row_rsprospeccao_contador['endereco']); ?>" size="90" maxlength="80" />
				<?php echo $tNGs->displayFieldHint("endereco");?> <?php echo $tNGs->displayFieldError("prospeccao_contador", "endereco", $cnt1); ?>
				</td>
			</tr>
			
			<tr>
				<td class="KT_th"><label for="numero_<?php echo $cnt1; ?>">Número:</label></td>
				<td>
				<input type="text" name="numero_<?php echo $cnt1; ?>" id="numero_<?php echo $cnt1; ?>" value="<?php echo KT_escapeAttribute($row_rsprospeccao_contador['numero']); ?>" size="90" maxlength="80" />
				<?php echo $tNGs->displayFieldHint("numero");?> <?php echo $tNGs->displayFieldError("prospeccao_contador", "numero", $cnt1); ?>
				</td>
			</tr>
			
			
			<tr>
				<td class="KT_th"><label for="uf_<?php echo $cnt1; ?>">Cidade:</label></td>
				<td>
				<select name="uf" id="uf">
				<option value="">Escolha ...</option>				
				<option value="AC" <?php if (!(strcmp("AC", $row_rsprospeccao_contador['uf']))) {echo "selected=\"selected\"";} ?>>AC</option>
				<option value="AL" <?php if (!(strcmp("AL", $row_rsprospeccao_contador['uf']))) {echo "selected=\"selected\"";} ?>>AL</option>
				<option value="AP" <?php if (!(strcmp("AP", $row_rsprospeccao_contador['uf']))) {echo "selected=\"selected\"";} ?>>AP</option>
				<option value="AM" <?php if (!(strcmp("AM", $row_rsprospeccao_contador['uf']))) {echo "selected=\"selected\"";} ?>>AM</option>
				<option value="BA" <?php if (!(strcmp("BA", $row_rsprospeccao_contador['uf']))) {echo "selected=\"selected\"";} ?>>BA</option>
				<option value="CE" <?php if (!(strcmp("CE", $row_rsprospeccao_contador['uf']))) {echo "selected=\"selected\"";} ?>>CE</option>
				<option value="DF" <?php if (!(strcmp("DF", $row_rsprospeccao_contador['uf']))) {echo "selected=\"selected\"";} ?>>DF</option>
				<option value="ES" <?php if (!(strcmp("ES", $row_rsprospeccao_contador['uf']))) {echo "selected=\"selected\"";} ?>>ES</option>
				<option value="GO" <?php if (!(strcmp("GO", $row_rsprospeccao_contador['uf']))) {echo "selected=\"selected\"";} ?>>GO</option>
				<option value="MA" <?php if (!(strcmp("MA", $row_rsprospeccao_contador['uf']))) {echo "selected=\"selected\"";} ?>>MA</option>
				<option value="MT" <?php if (!(strcmp("MT", $row_rsprospeccao_contador['uf']))) {echo "selected=\"selected\"";} ?>>MT</option>
				<option value="MS" <?php if (!(strcmp("MS", $row_rsprospeccao_contador['uf']))) {echo "selected=\"selected\"";} ?>>MS</option>
				<option value="MG" <?php if (!(strcmp("MG", $row_rsprospeccao_contador['uf']))) {echo "selected=\"selected\"";} ?>>MG</option>
				<option value="PA" <?php if (!(strcmp("PA", $row_rsprospeccao_contador['uf']))) {echo "selected=\"selected\"";} ?>>PA</option>
				<option value="PB" <?php if (!(strcmp("PB", $row_rsprospeccao_contador['uf']))) {echo "selected=\"selected\"";} ?>>PB</option>
				<option value="PR" <?php if (!(strcmp("PR", $row_rsprospeccao_contador['uf']))) {echo "selected=\"selected\"";} ?>>PR</option>
				<option value="PE" <?php if (!(strcmp("PE", $row_rsprospeccao_contador['uf']))) {echo "selected=\"selected\"";} ?>>PE</option>
				<option value="PI" <?php if (!(strcmp("PI", $row_rsprospeccao_contador['uf']))) {echo "selected=\"selected\"";} ?>>PI</option>
				<option value="RJ" <?php if (!(strcmp("RJ", $row_rsprospeccao_contador['uf']))) {echo "selected=\"selected\"";} ?>>RJ</option>
				<option value="RN" <?php if (!(strcmp("RN", $row_rsprospeccao_contador['uf']))) {echo "selected=\"selected\"";} ?>>RN</option>
				<option value="RS" <?php if (!(strcmp("RS", $row_rsprospeccao_contador['uf']))) {echo "selected=\"selected\"";} ?>>RS</option>
				<option value="RO" <?php if (!(strcmp("RO", $row_rsprospeccao_contador['uf']))) {echo "selected=\"selected\"";} ?>>RO</option>
				<option value="RR" <?php if (!(strcmp("RR", $row_rsprospeccao_contador['uf']))) {echo "selected=\"selected\"";} ?>>RR</option>
				<option value="SC" <?php if (!(strcmp("SC", $row_rsprospeccao_contador['uf']))) {echo "selected=\"selected\"";} ?>>SC</option>
				<option value="SP" <?php if (!(strcmp("SP", $row_rsprospeccao_contador['uf']))) {echo "selected=\"selected\"";} ?>>SP</option>
				<option value="SE" <?php if (!(strcmp("SE", $row_rsprospeccao_contador['uf']))) {echo "selected=\"selected\"";} ?>>SE</option>
				<option value="TO" <?php if (!(strcmp("TO", $row_rsprospeccao_contador['uf']))) {echo "selected=\"selected\"";} ?>>TO</option>
				</select>
								
				<select name="cidade" id="cidade">
				<option value="">Selecione primeiro o estado...</option>
				</select>
				
				<?php echo $tNGs->displayFieldHint("cidade");?> <?php echo $tNGs->displayFieldError("prospeccao_contador", "cidade", $cnt1); ?>
				</td>
			</tr>
			
			
			<tr>
				<td class="KT_th"><label for="telefone_<?php echo $cnt1; ?>">Telefone:</label></td>
				<td>
				<input type="text" name="telefone_<?php echo $cnt1; ?>" id="telefone_<?php echo $cnt1; ?>" value="<?php echo KT_escapeAttribute($row_rsprospeccao_contador['telefone']); ?>" size="90" maxlength="80" />
				<?php echo $tNGs->displayFieldHint("telefone");?> <?php echo $tNGs->displayFieldError("prospeccao_contador", "telefone", $cnt1); ?>
				</td>
			</tr>
			
			<tr>
				<td class="KT_th"><label for="email_<?php echo $cnt1; ?>">E-mail:</label></td>
				<td>
				<input type="text" name="email_<?php echo $cnt1; ?>" id="email_<?php echo $cnt1; ?>" value="<?php echo KT_escapeAttribute($row_rsprospeccao_contador['email']); ?>" size="90" maxlength="80" />
				<?php echo $tNGs->displayFieldHint("email");?> <?php echo $tNGs->displayFieldError("prospeccao_contador", "email", $cnt1); ?>
				</td>
			</tr>
			
			<tr>
				<td class="KT_th"><label for="emptec_<?php echo $cnt1; ?>">Nome do Responsável:</label></td>
				<td>
				<input type="text" name="emptec_<?php echo $cnt1; ?>" id="emptec_<?php echo $cnt1; ?>" value="<?php echo KT_escapeAttribute($row_rsprospeccao_contador['emptec']); ?>" size="90" maxlength="80" />
				<?php echo $tNGs->displayFieldHint("emptec");?> <?php echo $tNGs->displayFieldError("prospeccao_contador", "emptec", $cnt1); ?>
				</td>
			</tr>
          
        </table>
        <input type="hidden" name="kt_pk_prospeccao_contador_<?php echo $cnt1; ?>" class="id_field" value="<?php echo KT_escapeAttribute($row_rsprospeccao_contador['kt_pk_prospeccao_contador']); ?>" />
        <input type="hidden" name="data_<?php echo $cnt1; ?>" id="data_<?php echo $cnt1; ?>" value="<?php echo date('d-m-Y H:i:s'); ?>" />
        <?php } while ($row_rsprospeccao_contador = mysql_fetch_assoc($rsprospeccao_contador)); ?>
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
            <input type="submit" name="KT_Delete1" value="<?php echo NXT_getResource("Delete_FB"); ?>" onClick="return confirm('<?php echo NXT_getResource("Are you sure?"); ?>');" />
            <?php }
      // endif Conditional region1
      ?>
          <input type="button" name="KT_Cancel1" value="<?php echo NXT_getResource("Cancel_FB"); ?>" onClick="return UNI_navigateCancel(event, 'listar_contador.php')" />
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
