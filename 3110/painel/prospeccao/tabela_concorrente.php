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
$formValidation->addField("nome", true, "text", "", "", "", "Informe o concorrente");
$formValidation->addField("empresa", true, "text", "", "", "", "Informe o concorrente");
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
$ins_prospeccao_concorrente = new tNG_multipleInsert($conn_conexao);
$tNGs->addTransaction($ins_prospeccao_concorrente);
// Register triggers
$ins_prospeccao_concorrente->registerTrigger("STARTER", "Trigger_Default_Starter", 1, "POST", "KT_Insert1");
$ins_prospeccao_concorrente->registerTrigger("BEFORE", "Trigger_Default_FormValidation", 10, $formValidation);
$ins_prospeccao_concorrente->registerTrigger("END", "Trigger_Default_Redirect", 99, "listar_concorrente.php");
// Add columns
$ins_prospeccao_concorrente->setTable("prospeccao_concorrente");
$ins_prospeccao_concorrente->addColumn("nome", "STRING_TYPE", "POST", "nome");
$ins_prospeccao_concorrente->addColumn("empresa", "STRING_TYPE", "POST", "empresa");
$ins_prospeccao_concorrente->addColumn("estado_origem", "STRING_TYPE", "POST", "estado_origem");
$ins_prospeccao_concorrente->addColumn("cidade_origem", "STRING_TYPE", "POST", "cidade_origem");
$ins_prospeccao_concorrente->addColumn("site", "STRING_TYPE", "POST", "site");
$ins_prospeccao_concorrente->addColumn("banco_de_dados", "STRING_TYPE", "POST", "banco_de_dados");
$ins_prospeccao_concorrente->addColumn("migracao", "STRING_TYPE", "POST", "migracao");
$ins_prospeccao_concorrente->addColumn("migracao_tipo", "STRING_TYPE", "POST", "migracao_tipo");
$ins_prospeccao_concorrente->addColumn("recursos", "STRING_TYPE", "POST", "recursos");
$ins_prospeccao_concorrente->addColumn("cidade_atuacao", "STRING_TYPE", "POST", "cidade_atuacao");
$ins_prospeccao_concorrente->setPrimaryKey("id", "NUMERIC_TYPE");

// Make an update transaction instance
$upd_prospeccao_concorrente = new tNG_multipleUpdate($conn_conexao);
$tNGs->addTransaction($upd_prospeccao_concorrente);
// Register triggers
$upd_prospeccao_concorrente->registerTrigger("STARTER", "Trigger_Default_Starter", 1, "POST", "KT_Update1");
$upd_prospeccao_concorrente->registerTrigger("BEFORE", "Trigger_Default_FormValidation", 10, $formValidation);
$upd_prospeccao_concorrente->registerTrigger("END", "Trigger_Default_Redirect", 99, "listar_concorrente.php");
// Add columns
$upd_prospeccao_concorrente->setTable("prospeccao_concorrente");
$upd_prospeccao_concorrente->addColumn("nome", "STRING_TYPE", "POST", "nome");
$upd_prospeccao_concorrente->addColumn("empresa", "STRING_TYPE", "POST", "empresa");
$upd_prospeccao_concorrente->addColumn("estado_origem", "STRING_TYPE", "POST", "estado_origem");
$upd_prospeccao_concorrente->addColumn("cidade_origem", "STRING_TYPE", "POST", "cidade_origem");
$upd_prospeccao_concorrente->addColumn("site", "STRING_TYPE", "POST", "site");
$upd_prospeccao_concorrente->addColumn("banco_de_dados", "STRING_TYPE", "POST", "banco_de_dados");
$upd_prospeccao_concorrente->addColumn("migracao", "STRING_TYPE", "POST", "migracao");
$upd_prospeccao_concorrente->addColumn("migracao_tipo", "STRING_TYPE", "POST", "migracao_tipo");
$upd_prospeccao_concorrente->addColumn("recursos", "STRING_TYPE", "POST", "recursos");
$upd_prospeccao_concorrente->addColumn("cidade_atuacao", "STRING_TYPE", "POST", "cidade_atuacao");
$upd_prospeccao_concorrente->setPrimaryKey("id", "NUMERIC_TYPE", "GET", "id");

// Make an instance of the transaction object
$del_prospeccao_concorrente = new tNG_multipleDelete($conn_conexao);
$tNGs->addTransaction($del_prospeccao_concorrente);
// Register triggers
$del_prospeccao_concorrente->registerTrigger("STARTER", "Trigger_Default_Starter", 1, "POST", "KT_Delete1");
$del_prospeccao_concorrente->registerTrigger("END", "Trigger_Default_Redirect", 99, "listar_concorrente.php");
$del_prospeccao_concorrente->registerTrigger("BEFORE", "Trigger_DeleteDetail", 99);
// Add columns
$del_prospeccao_concorrente->setTable("prospeccao_concorrente");
$del_prospeccao_concorrente->setPrimaryKey("id", "NUMERIC_TYPE", "GET", "id");

// Execute all the registered transactions
$tNGs->executeTransactions();

// Get the transaction recordset
$rsprospeccao_concorrente = $tNGs->getRecordset("prospeccao_concorrente");
$row_rsprospeccao_concorrente = mysql_fetch_assoc($rsprospeccao_concorrente);
$totalRows_rsprospeccao_concorrente = mysql_num_rows($rsprospeccao_concorrente);
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

$(document).ready(function() {
	
	// migracao
	<? if(
	(@$_GET['id'] == "") or
	($row_rsprospeccao_concorrente['migracao'] == "n")
	){ ?>
	
		$('select[id="migracao_tipo_1"]').attr('disabled', true);
		$('select[id="migracao_tipo_1"]').val('');
	
	<? } ?>
	
	$("select[id=migracao_1]").change(function(){
		
		var migracao_atual = $(this).val(); // lê o valor selecionado
		if(migracao_atual == 'n'){
			$('select[id="migracao_tipo_1"]').attr('disabled', true);
			$('select[id="migracao_tipo_1"]').val('');
		} else if(migracao_atual == 's'){
			$('select[id="migracao_tipo_1"]').attr('disabled', false);
		}
		
	});
	// fim - migracao

	<? if (@$_GET['id'] <> "" and $row_rsprospeccao_concorrente['estado_origem'] <> "" and $row_rsprospeccao_concorrente['cidade_origem'] <> "") { ?>	
	$.post("consulta_cidade.php", 
		{
		uf: '<? echo $row_rsprospeccao_concorrente['estado_origem']; ?>', 
		cidade: '<? echo $row_rsprospeccao_concorrente['cidade_origem']; ?>'
		},
		function(valor){
			$("select[name=cidade_origem]").html(valor);
		}
	);
	<? } ?>
	
	// estado_origem
	$("select[name=estado_origem]").change(function(){
		$("select[name=cidade_origem]").html('<option value="0">Carregando...</option>');
		
		$.post("consulta_cidade.php", 
			{
			uf: $(this).val(), 
			cidade: '<? echo $row_rsprospeccao_concorrente['cidade_origem']; ?>'
			},
			function(valor){
				$("select[name=cidade_origem]").html(valor);
			}
		);

	})
	// estado_origem - fim
	
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
?> Concorrente</td>
                        <td align="right"><a href="javascript:history.go(-1);"><img src="../../imagens/botao_voltar.jpg" border="0" /></a></td>
                    </tr>
                </table>
                </div>
                <div class="caminho"><a href="../index.php">Página inicial</a> &gt;&gt; Concorrente</div>
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
    if (@$totalRows_rsprospeccao_concorrente > 1) {
    ?>
          <h2><?php echo NXT_getResource("Record_FH"); ?> <?php echo $cnt1; ?></h2>
          <?php } 
    // endif Conditional region1
    ?>
        <table cellpadding="2" cellspacing="0" class="KT_tngtable">
		
			<tr>
				<td class="KT_th"><label for="id_<?php echo $cnt1; ?>">Código:</label></td>
				<td>
				<input type="text" name="id_<?php echo $cnt1; ?>" id="id_<?php echo $cnt1; ?>" value="<?php echo KT_escapeAttribute($row_rsprospeccao_concorrente['id']); ?>" size="80" readonly="readonly" />
				<?php echo $tNGs->displayFieldHint("id");?> <?php echo $tNGs->displayFieldError("prospeccao_concorrente", "id", $cnt1); ?>
				</td>
			</tr>
			
			<tr>
				<td class="KT_th"><label for="nome_<?php echo $cnt1; ?>">Nome do Software:</label></td>
				<td>
				<input type="text" name="nome_<?php echo $cnt1; ?>" id="nome_<?php echo $cnt1; ?>" value="<?php echo KT_escapeAttribute($row_rsprospeccao_concorrente['nome']); ?>" size="80" maxlength="50" />
				<?php echo $tNGs->displayFieldHint("nome");?> <?php echo $tNGs->displayFieldError("prospeccao_concorrente", "nome", $cnt1); ?>
				</td>
			</tr>

			<tr>
				<td class="KT_th"><label for="empresa_<?php echo $cnt1; ?>">Empresa:</label></td>
				<td>
				<input type="text" name="empresa_<?php echo $cnt1; ?>" id="empresa_<?php echo $cnt1; ?>" value="<?php echo KT_escapeAttribute($row_rsprospeccao_concorrente['empresa']); ?>" size="80" maxlength="50" />
				<?php echo $tNGs->displayFieldHint("empresa");?> <?php echo $tNGs->displayFieldError("prospeccao_concorrente", "empresa", $cnt1); ?>
				</td>
			</tr>

			<tr>
				<td class="KT_th"><label for="estado_origem_<?php echo $cnt1; ?>">Cidade de origem:</label></td>
				<td>
				<select name="estado_origem" id="estado_origem">
				<option value="">Escolha ...</option>				
				<option value="AC" <?php if (!(strcmp("AC", $row_rsprospeccao_concorrente['estado_origem']))) {echo "selected=\"selected\"";} ?>>AC</option>
				<option value="AL" <?php if (!(strcmp("AL", $row_rsprospeccao_concorrente['estado_origem']))) {echo "selected=\"selected\"";} ?>>AL</option>
				<option value="AP" <?php if (!(strcmp("AP", $row_rsprospeccao_concorrente['estado_origem']))) {echo "selected=\"selected\"";} ?>>AP</option>
				<option value="AM" <?php if (!(strcmp("AM", $row_rsprospeccao_concorrente['estado_origem']))) {echo "selected=\"selected\"";} ?>>AM</option>
				<option value="BA" <?php if (!(strcmp("BA", $row_rsprospeccao_concorrente['estado_origem']))) {echo "selected=\"selected\"";} ?>>BA</option>
				<option value="CE" <?php if (!(strcmp("CE", $row_rsprospeccao_concorrente['estado_origem']))) {echo "selected=\"selected\"";} ?>>CE</option>
				<option value="DF" <?php if (!(strcmp("DF", $row_rsprospeccao_concorrente['estado_origem']))) {echo "selected=\"selected\"";} ?>>DF</option>
				<option value="ES" <?php if (!(strcmp("ES", $row_rsprospeccao_concorrente['estado_origem']))) {echo "selected=\"selected\"";} ?>>ES</option>
				<option value="GO" <?php if (!(strcmp("GO", $row_rsprospeccao_concorrente['estado_origem']))) {echo "selected=\"selected\"";} ?>>GO</option>
				<option value="MA" <?php if (!(strcmp("MA", $row_rsprospeccao_concorrente['estado_origem']))) {echo "selected=\"selected\"";} ?>>MA</option>
				<option value="MT" <?php if (!(strcmp("MT", $row_rsprospeccao_concorrente['estado_origem']))) {echo "selected=\"selected\"";} ?>>MT</option>
				<option value="MS" <?php if (!(strcmp("MS", $row_rsprospeccao_concorrente['estado_origem']))) {echo "selected=\"selected\"";} ?>>MS</option>
				<option value="MG" <?php if (!(strcmp("MG", $row_rsprospeccao_concorrente['estado_origem']))) {echo "selected=\"selected\"";} ?>>MG</option>
				<option value="PA" <?php if (!(strcmp("PA", $row_rsprospeccao_concorrente['estado_origem']))) {echo "selected=\"selected\"";} ?>>PA</option>
				<option value="PB" <?php if (!(strcmp("PB", $row_rsprospeccao_concorrente['estado_origem']))) {echo "selected=\"selected\"";} ?>>PB</option>
				<option value="PR" <?php if (!(strcmp("PR", $row_rsprospeccao_concorrente['estado_origem']))) {echo "selected=\"selected\"";} ?>>PR</option>
				<option value="PE" <?php if (!(strcmp("PE", $row_rsprospeccao_concorrente['estado_origem']))) {echo "selected=\"selected\"";} ?>>PE</option>
				<option value="PI" <?php if (!(strcmp("PI", $row_rsprospeccao_concorrente['estado_origem']))) {echo "selected=\"selected\"";} ?>>PI</option>
				<option value="RJ" <?php if (!(strcmp("RJ", $row_rsprospeccao_concorrente['estado_origem']))) {echo "selected=\"selected\"";} ?>>RJ</option>
				<option value="RN" <?php if (!(strcmp("RN", $row_rsprospeccao_concorrente['estado_origem']))) {echo "selected=\"selected\"";} ?>>RN</option>
				<option value="RS" <?php if (!(strcmp("RS", $row_rsprospeccao_concorrente['estado_origem']))) {echo "selected=\"selected\"";} ?>>RS</option>
				<option value="RO" <?php if (!(strcmp("RO", $row_rsprospeccao_concorrente['estado_origem']))) {echo "selected=\"selected\"";} ?>>RO</option>
				<option value="RR" <?php if (!(strcmp("RR", $row_rsprospeccao_concorrente['estado_origem']))) {echo "selected=\"selected\"";} ?>>RR</option>
				<option value="SC" <?php if (!(strcmp("SC", $row_rsprospeccao_concorrente['estado_origem']))) {echo "selected=\"selected\"";} ?>>SC</option>
				<option value="SP" <?php if (!(strcmp("SP", $row_rsprospeccao_concorrente['estado_origem']))) {echo "selected=\"selected\"";} ?>>SP</option>
				<option value="SE" <?php if (!(strcmp("SE", $row_rsprospeccao_concorrente['estado_origem']))) {echo "selected=\"selected\"";} ?>>SE</option>
				<option value="TO" <?php if (!(strcmp("TO", $row_rsprospeccao_concorrente['estado_origem']))) {echo "selected=\"selected\"";} ?>>TO</option>
				</select>
								
				<select name="cidade_origem" id="cidade_origem">
				<option value="">Selecione primeiro o estado...</option>
				</select>
				
				<?php echo $tNGs->displayFieldHint("cidade_origem");?> <?php echo $tNGs->displayFieldError("prospeccao_concorrente", "cidade_origem", $cnt1); ?>
				</td>
			</tr>
			
			<tr>
				<td class="KT_th"><label for="site_<?php echo $cnt1; ?>">Site:</label></td>
				<td>
				<input type="text" name="site_<?php echo $cnt1; ?>" id="site_<?php echo $cnt1; ?>" value="<?php echo KT_escapeAttribute($row_rsprospeccao_concorrente['site']); ?>" size="80" maxlength="50" />
				<?php echo $tNGs->displayFieldHint("site");?> <?php echo $tNGs->displayFieldError("prospeccao_concorrente", "site", $cnt1); ?>
				</td>
			</tr>
			
			<tr>
				<td class="KT_th"><label for="banco_de_dados_<?php echo $cnt1; ?>">Banco de dados:</label></td>
				<td>
				<input type="text" name="banco_de_dados_<?php echo $cnt1; ?>" id="banco_de_dados_<?php echo $cnt1; ?>" value="<?php echo KT_escapeAttribute($row_rsprospeccao_concorrente['banco_de_dados']); ?>" size="80" maxlength="50" placeholder="Ex: MySQL, SqlServer, dbf, etc..." />
				<?php echo $tNGs->displayFieldHint("banco_de_dados");?> <?php echo $tNGs->displayFieldError("prospeccao_concorrente", "banco_de_dados", $cnt1); ?>
				</td>
			</tr>
			
			
			<tr>
				<td class="KT_th"><label for="migracao_<?php echo $cnt1; ?>">Possuímos migração de dados?:</label></td>
				<td>
				<select name="migracao_<?php echo $cnt1; ?>" id="migracao_<?php echo $cnt1; ?>">
				<option value="n" <?php if (!(strcmp("n", KT_escapeAttribute($row_rsprospeccao_concorrente['migracao'])))) {echo "SELECTED";} ?>>Não</option>
				<option value="s" <?php if (!(strcmp("s", KT_escapeAttribute($row_rsprospeccao_concorrente['migracao'])))) {echo "SELECTED";} ?>>Sim</option>
				</select>
				<?php echo $tNGs->displayFieldHint("migracao");?> <?php echo $tNGs->displayFieldError("prospeccao_concorrente", "migracao", $cnt1); ?>
				</td>
			</tr>
			
			<tr>
				<td class="KT_th"><label for="migracao_tipo_<?php echo $cnt1; ?>">Tipo de migração:</label></td>
				<td>
				<select name="migracao_tipo_<?php echo $cnt1; ?>" id="migracao_tipo_<?php echo $cnt1; ?>">
				<option value="" >Selecione ...</option>
				<option value="c" <?php if (!(strcmp("c", KT_escapeAttribute($row_rsprospeccao_concorrente['migracao_tipo'])))) {echo "SELECTED";} ?>>Completa</option>
				<option value="p" <?php if (!(strcmp("p", KT_escapeAttribute($row_rsprospeccao_concorrente['migracao_tipo'])))) {echo "SELECTED";} ?>>Parcial</option>
				<option value="b" <?php if (!(strcmp("b", KT_escapeAttribute($row_rsprospeccao_concorrente['migracao_tipo'])))) {echo "SELECTED";} ?>>Cadastros básicos</option>
				</select>
				<?php echo $tNGs->displayFieldHint("migracao_tipo");?> <?php echo $tNGs->displayFieldError("prospeccao_concorrente", "migracao_tipo", $cnt1); ?>
				</td>
			</tr>
			
			<tr>
				<td class="KT_th"><label for="recursos_<?php echo $cnt1; ?>">Recursos comercializados ou<br>existentes no software concorrente:</label></td>
				<td>
				<textarea name="recursos_<?php echo $cnt1; ?>" id="recursos_<?php echo $cnt1; ?>" cols="80" rows="8"><?php echo KT_escapeAttribute($row_rsprospeccao_concorrente['recursos']); ?></textarea>
				<?php echo $tNGs->displayFieldHint("recursos");?> <?php echo $tNGs->displayFieldError("prospeccao_concorrente", "recursos", $cnt1); ?>
				</td>
			</tr>
			
			<tr>
				<td class="KT_th"><label for="cidade_atuacao_<?php echo $cnt1; ?>">Cidades de atuação:</label></td>
				<td>
				<input type="text" name="cidade_atuacao_<?php echo $cnt1; ?>" id="cidade_atuacao_<?php echo $cnt1; ?>" value="<?php echo KT_escapeAttribute($row_rsprospeccao_concorrente['cidade_atuacao']); ?>" size="80" maxlength="50" />
				<?php echo $tNGs->displayFieldHint("cidade_atuacao");?> <?php echo $tNGs->displayFieldError("prospeccao_concorrente", "cidade_atuacao", $cnt1); ?>
				</td>
			</tr>
          
        </table>
        <input type="hidden" name="kt_pk_prospeccao_concorrente_<?php echo $cnt1; ?>" class="id_field" value="<?php echo KT_escapeAttribute($row_rsprospeccao_concorrente['kt_pk_prospeccao_concorrente']); ?>" />
        <input type="hidden" name="data_<?php echo $cnt1; ?>" id="data_<?php echo $cnt1; ?>" value="<?php echo date('d-m-Y H:i:s'); ?>" />
        <?php } while ($row_rsprospeccao_concorrente = mysql_fetch_assoc($rsprospeccao_concorrente)); ?>
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
          <input type="button" name="KT_Cancel1" value="<?php echo NXT_getResource("Cancel_FB"); ?>" onClick="return UNI_navigateCancel(event, 'listar_concorrente.php')" />
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