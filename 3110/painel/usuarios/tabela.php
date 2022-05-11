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

// geral_tipo_praca
mysql_select_db($database_conexao, $conexao);
$query_praca_listar = "SELECT * FROM geral_tipo_praca ORDER BY praca ASC";
$praca_listar = mysql_query($query_praca_listar, $conexao) or die(mysql_error());
$row_praca_listar = mysql_fetch_assoc($praca_listar);
$totalRows_praca_listar = mysql_num_rows($praca_listar);
// fim - geral_tipo_praca

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

//start Trigger_CheckPasswords trigger
//remove this line if you want to edit the code by hand
function Trigger_CheckPasswords(&$tNG) {
  $myThrowError = new tNG_ThrowError($tNG);
  $myThrowError->setErrorMsg("Passwords do not match.");
  $myThrowError->setField("senha");
  $myThrowError->setFieldErrorMsg("The two passwords do not match.");
  return $myThrowError->Execute();
}
//end Trigger_CheckPasswords trigger

// Start trigger
$formValidation = new tNG_FormValidation();
$formValidation->addField("status", true, "numeric", "", "", "", "Selecione uma das opções");
$formValidation->addField("praca", true, "text", "", "", "", "Escolha uma praça");
$formValidation->addField("usuario", true, "text", "", "", "", "Escolha um usuário");
$formValidation->addField("senha", true, "text", "", "", "", "Preencha a senha");
$tNGs->prepareValidation($formValidation);
// End trigger

// Make an insert transaction instance
$ins_usuarios = new tNG_multipleInsert($conn_conexao);
$tNGs->addTransaction($ins_usuarios);
// Register triggers
$ins_usuarios->registerTrigger("STARTER", "Trigger_Default_Starter", 1, "POST", "KT_Insert1");
$ins_usuarios->registerTrigger("BEFORE", "Trigger_Default_FormValidation", 10, $formValidation);
$ins_usuarios->registerTrigger("END", "Trigger_Default_Redirect", 99, "listar.php");
$ins_usuarios->registerConditionalTrigger("{POST.senha} != {POST.re_senha}", "BEFORE", "Trigger_CheckPasswords", 50);
// Add columns
$ins_usuarios->setTable("usuarios");
$ins_usuarios->addColumn("status", "NUMERIC_TYPE", "POST", "status");
$ins_usuarios->addColumn("nome", "STRING_TYPE", "POST", "nome");
$ins_usuarios->addColumn("aniversario", "DATE_TYPE", "POST", "aniversario");
$ins_usuarios->addColumn("telefone", "STRING_TYPE", "POST", "telefone");
$ins_usuarios->addColumn("praca", "STRING_TYPE", "POST", "praca", "{usuario.praca}");
$ins_usuarios->addColumn("empresa", "STRING_TYPE", "POST", "empresa");
$ins_usuarios->addColumn("email", "STRING_TYPE", "POST", "email");
$ins_usuarios->addColumn("email2", "STRING_TYPE", "POST", "email2");
$ins_usuarios->addColumn("usuario", "STRING_TYPE", "POST", "usuario");
$ins_usuarios->addColumn("senha", "STRING_TYPE", "POST", "senha");
$ins_usuarios->addColumn("controle_usuarios", "CHECKBOX_YN_TYPE", "POST", "controle_usuarios", "N");
$ins_usuarios->addColumn("controle_programa_subprograma", "CHECKBOX_YN_TYPE", "POST", "controle_programa_subprograma", "N");
$ins_usuarios->addColumn("controle_solicitacao", "CHECKBOX_YN_TYPE", "POST", "controle_solicitacao", "N");
$ins_usuarios->addColumn("solicitacao_executante", "CHECKBOX_YN_TYPE", "POST", "solicitacao_executante", "N");
$ins_usuarios->addColumn("solicitacao_testador", "CHECKBOX_YN_TYPE", "POST", "solicitacao_testador", "N");
$ins_usuarios->addColumn("controle_suporte", "CHECKBOX_YN_TYPE", "POST", "controle_suporte", "N");
$ins_usuarios->addColumn("suporte_operador_parceiro", "CHECKBOX_YN_TYPE", "POST", "suporte_operador_parceiro", "N");
$ins_usuarios->addColumn("suporte_administrativo", "CHECKBOX_YN_TYPE", "POST", "suporte_administrativo", "N");
$ins_usuarios->addColumn("suporte_operacional", "CHECKBOX_YN_TYPE", "POST", "suporte_operacional", "N");
$ins_usuarios->addColumn("controle_prospeccao", "CHECKBOX_YN_TYPE", "POST", "controle_prospeccao", "N");
$ins_usuarios->addColumn("controle_venda", "CHECKBOX_YN_TYPE", "POST", "controle_venda", "N");
$ins_usuarios->addColumn("administrador_site", "CHECKBOX_YN_TYPE", "POST", "administrador_site", "N");
$ins_usuarios->addColumn("controle_praca", "CHECKBOX_YN_TYPE", "POST", "controle_praca", "N");
$ins_usuarios->addColumn("controle_comunicado", "CHECKBOX_YN_TYPE", "POST", "controle_comunicado", "N");
$ins_usuarios->addColumn("controle_mala_direta", "CHECKBOX_YN_TYPE", "POST", "controle_mala_direta", "N");
$ins_usuarios->addColumn("controle_memorando", "CHECKBOX_YN_TYPE", "POST", "controle_memorando", "N");
$ins_usuarios->addColumn("nivel_prospeccao", "NUMERIC_TYPE", "POST", "nivel_prospeccao");
$ins_usuarios->addColumn("nivel_venda", "NUMERIC_TYPE", "POST", "nivel_venda");
$ins_usuarios->addColumn("controle_relatorio", "CHECKBOX_YN_TYPE", "POST", "controle_relatorio", "N");
$ins_usuarios->setPrimaryKey("IdUsuario", "NUMERIC_TYPE");

// Make an update transaction instance
$upd_usuarios = new tNG_multipleUpdate($conn_conexao);
$tNGs->addTransaction($upd_usuarios);
// Register triggers
$upd_usuarios->registerTrigger("STARTER", "Trigger_Default_Starter", 1, "POST", "KT_Update1");
$upd_usuarios->registerTrigger("BEFORE", "Trigger_Default_FormValidation", 10, $formValidation);
$upd_usuarios->registerTrigger("END", "Trigger_Default_Redirect", 99, "listar.php");
$upd_usuarios->registerConditionalTrigger("{POST.senha} != {POST.re_senha}", "BEFORE", "Trigger_CheckPasswords", 50);
// Add columns
$upd_usuarios->setTable("usuarios");
$upd_usuarios->addColumn("status", "NUMERIC_TYPE", "POST", "status");
$upd_usuarios->addColumn("nome", "STRING_TYPE", "POST", "nome");
$upd_usuarios->addColumn("aniversario", "DATE_TYPE", "POST", "aniversario");
$upd_usuarios->addColumn("telefone", "STRING_TYPE", "POST", "telefone");
$upd_usuarios->addColumn("praca", "STRING_TYPE", "POST", "praca");
$upd_usuarios->addColumn("empresa", "STRING_TYPE", "POST", "empresa");
$upd_usuarios->addColumn("email", "STRING_TYPE", "POST", "email");
$upd_usuarios->addColumn("email2", "STRING_TYPE", "POST", "email2");
$upd_usuarios->addColumn("usuario", "STRING_TYPE", "POST", "usuario");
$upd_usuarios->addColumn("senha", "STRING_TYPE", "POST", "senha");
$upd_usuarios->addColumn("controle_usuarios", "CHECKBOX_YN_TYPE", "POST", "controle_usuarios");
$upd_usuarios->addColumn("controle_programa_subprograma", "CHECKBOX_YN_TYPE", "POST", "controle_programa_subprograma");
$upd_usuarios->addColumn("controle_solicitacao", "CHECKBOX_YN_TYPE", "POST", "controle_solicitacao");
$upd_usuarios->addColumn("solicitacao_executante", "CHECKBOX_YN_TYPE", "POST", "solicitacao_executante");
$upd_usuarios->addColumn("solicitacao_testador", "CHECKBOX_YN_TYPE", "POST", "solicitacao_testador");
$upd_usuarios->addColumn("controle_suporte", "CHECKBOX_YN_TYPE", "POST", "controle_suporte");
$upd_usuarios->addColumn("suporte_operador_parceiro", "CHECKBOX_YN_TYPE", "POST", "suporte_operador_parceiro", "N");
$upd_usuarios->addColumn("suporte_administrativo", "CHECKBOX_YN_TYPE", "POST", "suporte_administrativo", "N");
$upd_usuarios->addColumn("suporte_operacional", "CHECKBOX_YN_TYPE", "POST", "suporte_operacional", "N");
$upd_usuarios->addColumn("controle_prospeccao", "CHECKBOX_YN_TYPE", "POST", "controle_prospeccao", "N");
$upd_usuarios->addColumn("controle_venda", "CHECKBOX_YN_TYPE", "POST", "controle_venda", "N");
$upd_usuarios->addColumn("administrador_site", "CHECKBOX_YN_TYPE", "POST", "administrador_site");
$upd_usuarios->addColumn("controle_praca", "CHECKBOX_YN_TYPE", "POST", "controle_praca");
$upd_usuarios->addColumn("controle_comunicado", "CHECKBOX_YN_TYPE", "POST", "controle_comunicado");
$upd_usuarios->addColumn("controle_mala_direta", "CHECKBOX_YN_TYPE", "POST", "controle_mala_direta");
$upd_usuarios->addColumn("controle_memorando", "CHECKBOX_YN_TYPE", "POST", "controle_memorando");
$upd_usuarios->addColumn("nivel_prospeccao", "NUMERIC_TYPE", "POST", "nivel_prospeccao");
$upd_usuarios->addColumn("nivel_venda", "NUMERIC_TYPE", "POST", "nivel_venda");
$upd_usuarios->addColumn("controle_relatorio", "CHECKBOX_YN_TYPE", "POST", "controle_relatorio", "N");
$upd_usuarios->setPrimaryKey("IdUsuario", "NUMERIC_TYPE", "GET", "IdUsuario");

// Execute all the registered transactions
$tNGs->executeTransactions();

// Get the transaction recordset
$rsusuarios = $tNGs->getRecordset("usuarios");
$row_rsusuarios = mysql_fetch_assoc($rsusuarios);
$totalRows_rsusuarios = mysql_num_rows($rsusuarios);
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
<script type="text/javascript" src="../../funcoes.js"></script>
<script type="text/javascript" src="../../js/jquery.maskedinput-1.2.2.min.js"></script> 

<link href="../../includes/skins/mxkollection3.css" rel="stylesheet" type="text/css" media="all" />
<script src="../../includes/common/js/base.js" type="text/javascript"></script>
<script src="../../includes/common/js/utility.js" type="text/javascript"></script>
<script src="../../includes/skins/style.js" type="text/javascript"></script>
<?php echo $tNGs->displayValidationRules();?>
<script src="../../includes/nxt/scripts/form.js" type="text/javascript"></script>
<script src="../../includes/nxt/scripts/form.js.php" type="text/javascript"></script>
<script type="text/javascript">
$NXT_FORM_SETTINGS = {
  duplicate_buttons: true,
  show_as_grid: false,
  merge_down_value: false
}

$(document).ready(function() {
			   
	// mascara
	$('#aniversario_1').mask('99-99-9999',{placeholder:" "});
	// fim - mascara
	
	// aniversario - verifica se é uma data válida
    $('#aniversario_1').blur(function(){

		var campo = $(this);
		
		// erro
		var erro = funcao_verifica_data_valida(campo) // chamada da função (retorna 0/1)		
		if(erro==1){
			
			alert("Data inválida");
			$('#aniversario_1').val('');
			setTimeout(function() {campo.focus();}, 100);
			return false;
			
		}
		// fim - erro
		
    });
	// fim - aniversario - verifica se é uma data válida

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
if (@$_GET['IdUsuario'] == "") {
?>
    <?php echo NXT_getResource("Insert_FH"); ?>
    <?php 
// else Conditional region1
} else { ?>
    <?php echo NXT_getResource("Update_FH"); ?>
    <?php } 
// endif Conditional region1
?> Usuário</td>
                        <td align="right"><a href="javascript:history.go(-1);"><img src="../../imagens/botao_voltar.jpg" border="0" /></a></td>
                    </tr>
                </table>
                </div>
                <div class="caminho"><a href="../index.php">Página inicial</a> &gt;&gt; Usuário</div>
                <!-- fim - titulo -->
                
                <div class="conteudo">
                
<div class="KT_tng">
  <div class="KT_tngform">
    <form method="post" id="form1" action="<?php echo KT_escapeAttribute(KT_getFullUri()); ?>">
      <?php $cnt1 = 0; ?>
      <?php do { ?>
      <?php $cnt1++; ?>
      <?php 
// Show IF Conditional region1 
if (@$totalRows_rsusuarios > 1) {
?>
      <h2><?php echo NXT_getResource("Record_FH"); ?> <?php echo $cnt1; ?></h2>
      <?php } 
// endif Conditional region1
?>
      <table cellpadding="2" cellspacing="0" class="KT_tngtable">
      
<tr>
<td class="KT_th"><label for="status_<?php echo $cnt1; ?>_1">Status:</label></td>
<td>
<div>
<input <?php if (!(strcmp(KT_escapeAttribute($row_rsusuarios['status']),"0"))) {echo "checked=\"checked\"";} ?> type="radio" name="status_<?php echo $cnt1; ?>" id="status_<?php echo $cnt1; ?>_1" value="0" />
<label for="status_<?php echo $cnt1; ?>_1">Bloqueado</label>
</div>

<div>
<input <?php if (!(strcmp(KT_escapeAttribute($row_rsusuarios['status']),"1"))) {echo "checked=\"checked\"";} ?> type="radio" name="status_<?php echo $cnt1; ?>" id="status_<?php echo $cnt1; ?>_2" value="1" />
<label for="status_<?php echo $cnt1; ?>_2">Liberado</label>
</div>

<?php echo $tNGs->displayFieldError("usuarios", "status", $cnt1); ?>
</td>
</tr>
           
        <tr>
          <td class="KT_th"><label for="nome_<?php echo $cnt1; ?>">Nome:</label></td>
          <td>
          <input type="text" name="nome_<?php echo $cnt1; ?>" id="nome_<?php echo $cnt1; ?>" value="<?php echo KT_escapeAttribute($row_rsusuarios['nome']); ?>" size="70" maxlength="100" />
		  <?php echo $tNGs->displayFieldHint("nome");?> <?php echo $tNGs->displayFieldError("usuarios", "nome", $cnt1); ?>
          </td>
        </tr>
        
        <tr>
          <td class="KT_th"><label for="aniversario_<?php echo $cnt1; ?>">Data de aniversário:</label></td>
          <td>
          <input type="text" name="aniversario_<?php echo $cnt1; ?>" id="aniversario_<?php echo $cnt1; ?>" value="<?php echo KT_formatDate($row_rsusuarios['aniversario']); ?>" size="22" maxlength="19" />
		  <?php echo $tNGs->displayFieldHint("aniversario");?> <?php echo $tNGs->displayFieldError("usuarios", "aniversario", $cnt1); ?>
          </td>
        </tr>
        
        <tr>
          <td class="KT_th"><label for="telefone_<?php echo $cnt1; ?>">Telefone:</label></td>
          <td>
          <input type="text" name="telefone_<?php echo $cnt1; ?>" id="telefone_<?php echo $cnt1; ?>" value="<?php echo KT_formatDate($row_rsusuarios['telefone']); ?>" size="22" maxlength="19" />
		  <?php echo $tNGs->displayFieldHint("telefone");?> <?php echo $tNGs->displayFieldError("usuarios", "telefone", $cnt1); ?>
          </td>
        </tr>
        
        <tr>
          <td class="KT_th">&nbsp;</td>
          <td>&nbsp;</td>
        </tr>
        <tr>
          <td class="KT_th"><label for="praca_<?php echo $cnt1; ?>">Praça:</label></td>
          <td>
<select name="praca">
<option value="">Escolha ...</option>
<?php do { ?>
<option value="<?php echo $row_praca_listar['praca']?>"
<?php if ( (!(strcmp($row_praca_listar['praca'], $row_rsusuarios['praca']))) && (@$_GET['IdUsuario'] != "") ) {echo "selected=\"selected\"";} ?>
>
<?php echo $row_praca_listar['praca']?></option>
<?php
} while ($row_praca_listar = mysql_fetch_assoc($praca_listar));
$rows = mysql_num_rows($praca_listar);
if($rows > 0) {
mysql_data_seek($praca_listar, 0);
$row_praca_listar = mysql_fetch_assoc($praca_listar);
}
?>
</select>
              <?php echo $tNGs->displayFieldError("usuarios", "praca", $cnt1); ?> </td>
        </tr>
        <tr>
          <td class="KT_th"><label for="empresa_<?php echo $cnt1; ?>">Empresa:</label></td>
          <td><input type="text" name="empresa_<?php echo $cnt1; ?>" id="empresa_<?php echo $cnt1; ?>" value="<?php echo KT_escapeAttribute($row_rsusuarios['empresa']); ?>" size="70" maxlength="50" />
              <?php echo $tNGs->displayFieldHint("empresa");?> <?php echo $tNGs->displayFieldError("usuarios", "empresa", $cnt1); ?> </td>
        </tr>
        <tr>
          <td class="KT_th"><label for="email_<?php echo $cnt1; ?>">E-mail:</label></td>
          <td><input type="text" name="email_<?php echo $cnt1; ?>" id="email_<?php echo $cnt1; ?>" value="<?php echo KT_escapeAttribute($row_rsusuarios['email']); ?>" size="50" maxlength="255" />
              <?php echo $tNGs->displayFieldHint("email");?> <?php echo $tNGs->displayFieldError("usuarios", "email", $cnt1); ?> </td>
        </tr>
        <tr>
          <td class="KT_th"><label for="email2_<?php echo $cnt1; ?>">E-mail 2:</label></td>
          <td><input type="text" name="email2_<?php echo $cnt1; ?>" id="email2_<?php echo $cnt1; ?>" value="<?php echo KT_escapeAttribute($row_rsusuarios['email2']); ?>" size="50" maxlength="50" />
              <?php echo $tNGs->displayFieldHint("email2");?> <?php echo $tNGs->displayFieldError("usuarios", "email2", $cnt1); ?> </td>
        </tr>
        <tr>
          <td class="KT_th"><label for="usuario_<?php echo $cnt1; ?>">Usuário:</label></td>
          <td><input type="text" name="usuario_<?php echo $cnt1; ?>" id="usuario_<?php echo $cnt1; ?>" value="<?php echo KT_escapeAttribute($row_rsusuarios['usuario']); ?>" size="50" maxlength="50" <?php if (@$_GET['IdUsuario'] != ""){ ?>readonly="readonly"<? } ?> />
              <?php echo $tNGs->displayFieldHint("usuario");?> <?php echo $tNGs->displayFieldError("usuarios", "usuario", $cnt1); ?> </td>
        </tr>
        <tr>
          <td class="KT_th"><label for="senha_<?php echo $cnt1; ?>">Senha:</label></td>
          <td><input type="password" name="senha_<?php echo $cnt1; ?>" id="senha_<?php echo $cnt1; ?>" value="<?php echo KT_escapeAttribute($row_rsusuarios['senha']); ?>" size="50" maxlength="50" />
              <?php echo $tNGs->displayFieldHint("senha");?> <?php echo $tNGs->displayFieldError("usuarios", "senha", $cnt1); ?> </td>
        </tr>
        <tr>
          <td class="KT_th"><label for="re_senha_<?php echo $cnt1; ?>">Repita a senha:</label></td>
          <td><input type="password" name="re_senha_<?php echo $cnt1; ?>" id="re_senha_<?php echo $cnt1; ?>" value="<?php echo KT_escapeAttribute($row_rsusuarios['senha']); ?>" size="50" maxlength="50" />          </td>
        </tr>

<tr>        
<td colspan="2" class="KT_th">
<div class="titulo">Geral</div>
</td>
</tr>

<tr>
<td class="KT_th"><label for="administrador_site_<?php echo $cnt1; ?>">Administrador do site:</label></td>
<td><input  <?php if (!(strcmp(KT_escapeAttribute($row_rsusuarios['administrador_site']),"Y"))) {echo "checked";} ?> type="checkbox" name="administrador_site_<?php echo $cnt1; ?>" id="administrador_site_<?php echo $cnt1; ?>" value="Y" />
<?php echo $tNGs->displayFieldError("usuarios", "administrador_site", $cnt1); ?> </td>
</tr>

<tr>
<td class="KT_th"><label for="controle_usuarios_<?php echo $cnt1; ?>">Controle de  usuários:</label></td>
<td><input  <?php if (!(strcmp(KT_escapeAttribute($row_rsusuarios['controle_usuarios']),"Y"))) {echo "checked";} ?> type="checkbox" name="controle_usuarios_<?php echo $cnt1; ?>" id="controle_usuarios_<?php echo $cnt1; ?>" value="Y" />
<?php echo $tNGs->displayFieldError("usuarios", "controle_usuarios", $cnt1); ?> </td>
</tr>

<tr>
<td class="KT_th"><label for="controle_programa_subprograma_<?php echo $cnt1; ?>">Controle de programa/subprograma:</label></td>
<td><input  <?php if (!(strcmp(KT_escapeAttribute($row_rsusuarios['controle_programa_subprograma']),"Y"))) {echo "checked";} ?> type="checkbox" name="controle_programa_subprograma_<?php echo $cnt1; ?>" id="controle_programa_subprograma_<?php echo $cnt1; ?>" value="Y" />
<?php echo $tNGs->displayFieldError("usuarios", "controle_programa_subprograma", $cnt1); ?> </td>
</tr>

<tr>
<td class="KT_th">Controle de Praça:</td>
<td><input  <?php if (!(strcmp(KT_escapeAttribute($row_rsusuarios['controle_praca']),"Y"))) {echo "checked";} ?> type="checkbox" name="controle_praca_<?php echo $cnt1; ?>" id="controle_praca_<?php echo $cnt1; ?>" value="Y" />
<?php echo $tNGs->displayFieldError("usuarios", "controle_praca", $cnt1); ?> </td>
</tr>

<tr>
<td class="KT_th">Controle de Comunicados:</td>
<td><input  <?php if (!(strcmp(KT_escapeAttribute($row_rsusuarios['controle_comunicado']),"Y"))) {echo "checked";} ?> type="checkbox" name="controle_comunicado_<?php echo $cnt1; ?>" id="controle_comunicado_<?php echo $cnt1; ?>" value="Y" />
<?php echo $tNGs->displayFieldError("usuarios", "controle_comunicado", $cnt1); ?> </td>
</tr>

<tr>
<td class="KT_th">Controle de Mala Direta:</td>
<td><input  <?php if (!(strcmp(KT_escapeAttribute($row_rsusuarios['controle_mala_direta']),"Y"))) {echo "checked";} ?> type="checkbox" name="controle_mala_direta_<?php echo $cnt1; ?>" id="controle_mala_direta_<?php echo $cnt1; ?>" value="Y" />
<?php echo $tNGs->displayFieldError("usuarios", "controle_mala_direta", $cnt1); ?> </td>
</tr>

<tr>
<td class="KT_th">Controle de Memorandos:</td>
<td><input  <?php if (!(strcmp(KT_escapeAttribute($row_rsusuarios['controle_memorando']),"Y"))) {echo "checked";} ?> type="checkbox" name="controle_memorando_<?php echo $cnt1; ?>" id="controle_memorando_<?php echo $cnt1; ?>" value="Y" />
<?php echo $tNGs->displayFieldError("usuarios", "controle_memorando", $cnt1); ?> </td>
</tr>

<tr>        
<td colspan="2" class="KT_th">
<div class="titulo">Solicitações</div>
</td>
</tr>

<tr>
<td class="KT_th"><label for="controle_solicitacao_<?php echo $cnt1; ?>">Controlador:</label></td>
<td><input  <?php if (!(strcmp(KT_escapeAttribute($row_rsusuarios['controle_solicitacao']),"Y"))) {echo "checked";} ?> type="checkbox" name="controle_solicitacao_<?php echo $cnt1; ?>" id="controle_solicitacao_<?php echo $cnt1; ?>" value="Y" />
<?php echo $tNGs->displayFieldError("usuarios", "controle_solicitacao", $cnt1); ?> </td>
</tr>

<tr>
<td class="KT_th"><label for="solicitacao_executante_<?php echo $cnt1; ?>">Executante:</label></td>
<td><input  <?php if (!(strcmp(KT_escapeAttribute($row_rsusuarios['solicitacao_executante']),"Y"))) {echo "checked";} ?> type="checkbox" name="solicitacao_executante_<?php echo $cnt1; ?>" id="solicitacao_executante_<?php echo $cnt1; ?>" value="Y" />
<?php echo $tNGs->displayFieldError("usuarios", "solicitacao_executante", $cnt1); ?> </td>
</tr>

<tr>
<td class="KT_th"><label for="solicitacao_testador_<?php echo $cnt1; ?>">Testador:</label></td>
<td><input  <?php if (!(strcmp(KT_escapeAttribute($row_rsusuarios['solicitacao_testador']),"Y"))) {echo "checked";} ?> type="checkbox" name="solicitacao_testador_<?php echo $cnt1; ?>" id="solicitacao_testador_<?php echo $cnt1; ?>" value="Y" />
<?php echo $tNGs->displayFieldError("usuarios", "solicitacao_testador", $cnt1); ?> </td>
</tr>

<tr>        
<td colspan="2" class="KT_th">
<div class="titulo">Suporte</div>
</td>
</tr>

<tr>
<td class="KT_th"><label for="controle_suporte_<?php echo $cnt1; ?>">Controlador:</label></td>
<td><input  <?php if (!(strcmp(KT_escapeAttribute($row_rsusuarios['controle_suporte']),"Y"))) {echo "checked";} ?> type="checkbox" name="controle_suporte_<?php echo $cnt1; ?>" id="controle_suporte_<?php echo $cnt1; ?>" value="Y" />
<?php echo $tNGs->displayFieldError("usuarios", "controle_suporte", $cnt1); ?> </td>
</tr>
        
<tr>
<td class="KT_th"><label for="suporte_operador_parceiro_<?php echo $cnt1; ?>">Operador:</label></td>
<td><input  <?php if (!(strcmp(KT_escapeAttribute($row_rsusuarios['suporte_operador_parceiro']),"Y"))) {echo "checked";} ?> type="checkbox" name="suporte_operador_parceiro_<?php echo $cnt1; ?>" id="suporte_operador_parceiro_<?php echo $cnt1; ?>" value="Y" />
<?php echo $tNGs->displayFieldError("usuarios", "suporte_operador_parceiro", $cnt1); ?> </td>
</tr>

<tr>
<td class="KT_th"><label for="suporte_administrativo_<?php echo $cnt1; ?>">Administrativo:</label></td>
<td><input  <?php if (!(strcmp(KT_escapeAttribute($row_rsusuarios['suporte_administrativo']),"Y"))) {echo "checked";} ?> type="checkbox" name="suporte_administrativo_<?php echo $cnt1; ?>" id="suporte_administrativo_<?php echo $cnt1; ?>" value="Y" />
<?php echo $tNGs->displayFieldError("usuarios", "suporte_administrativo", $cnt1); ?> </td>
</tr>

<tr>
<td class="KT_th"><label for="suporte_operacional_<?php echo $cnt1; ?>">Operacional:</label></td>
<td><input  <?php if (!(strcmp(KT_escapeAttribute($row_rsusuarios['suporte_operacional']),"Y"))) {echo "checked";} ?> type="checkbox" name="suporte_operacional_<?php echo $cnt1; ?>" id="suporte_operacional_<?php echo $cnt1; ?>" value="Y" />
<?php echo $tNGs->displayFieldError("usuarios", "suporte_operacional", $cnt1); ?> </td>
</tr>


<tr>        
  <td colspan="2" class="KT_th">
  <div class="titulo">Prospecção</div>
  </td>
</tr>

<tr>
<td class="KT_th"><label for="controle_prospeccao_<?php echo $cnt1; ?>">Controlador:</label></td>
<td><input  <?php if (!(strcmp(KT_escapeAttribute($row_rsusuarios['controle_prospeccao']),"Y"))) {echo "checked";} ?> type="checkbox" name="controle_prospeccao_<?php echo $cnt1; ?>" id="controle_prospeccao_<?php echo $cnt1; ?>" value="Y" />
<?php echo $tNGs->displayFieldError("usuarios", "controle_prospeccao", $cnt1); ?> </td>
</tr>


<tr>        
<td colspan="2" class="KT_th">
<div class="titulo">Venda</div>
</td>
</tr>

<tr>
<td class="KT_th"><label for="controle_venda_<?php echo $cnt1; ?>">Controlador:</label></td>
<td><input  <?php if (!(strcmp(KT_escapeAttribute($row_rsusuarios['controle_venda']),"Y"))) {echo "checked";} ?> type="checkbox" name="controle_venda_<?php echo $cnt1; ?>" id="controle_venda_<?php echo $cnt1; ?>" value="Y" />
<?php echo $tNGs->displayFieldError("usuarios", "controle_venda", $cnt1); ?> </td>
</tr>


<tr>        
<td colspan="2" class="KT_th">
<div class="titulo">Relatórios</div>
</td>
</tr>

<tr>
<td class="KT_th"><label for="controle_relatorio_<?php echo $cnt1; ?>">Controlador:</label></td>
<td><input  <?php if (!(strcmp(KT_escapeAttribute($row_rsusuarios['controle_relatorio']),"Y"))) {echo "checked";} ?> type="checkbox" name="controle_relatorio_<?php echo $cnt1; ?>" id="controle_relatorio_<?php echo $cnt1; ?>" value="Y" />
<?php echo $tNGs->displayFieldError("usuarios", "controle_relatorio", $cnt1); ?> </td>
</tr>

        

<tr>
<td colspan="2" class="KT_th">
<div class="titulo">Níveis de acesso (3: executante | 2: supervisor | 1: administrador)</div>
</td>
</tr>

<tr>
    <td class="KT_th" valign="middle">
    <label for="nivel_prospeccao_<?php echo $cnt1; ?>">Prospecção:</label>
    </td>
    <td>
    <select name="nivel_prospeccao" id="nivel_prospeccao">
      <option value="3" style="width:100px;" <?php if (!(strcmp(3, $row_rsusuarios['nivel_prospeccao']))) {echo "selected=\"selected\"";} ?>>3</option>
      <option value="2" <?php if (!(strcmp(2, $row_rsusuarios['nivel_prospeccao']))) {echo "selected=\"selected\"";} ?>>2</option>
      <option value="1" <?php if (!(strcmp(1, $row_rsusuarios['nivel_prospeccao']))) {echo "selected=\"selected\"";} ?>>1</option>
    </select>
    <?php echo $tNGs->displayFieldError("usuarios", "nivel_prospeccao", $cnt1); ?>
    </td>
</tr>

<tr>
    <td class="KT_th" valign="middle">
    <label for="nivel_venda_<?php echo $cnt1; ?>">Venda:</label>
    </td>
    <td>
    <select name="nivel_venda" id="nivel_venda">
      <option value="3" style="width:100px;" <?php if (!(strcmp(3, $row_rsusuarios['nivel_venda']))) {echo "selected=\"selected\"";} ?>>3</option>
      <option value="2" <?php if (!(strcmp(2, $row_rsusuarios['nivel_venda']))) {echo "selected=\"selected\"";} ?>>2</option>
      <option value="1" <?php if (!(strcmp(1, $row_rsusuarios['nivel_venda']))) {echo "selected=\"selected\"";} ?>>1</option>
    </select>
    <?php echo $tNGs->displayFieldError("usuarios", "nivel_venda", $cnt1); ?>
    </td>
</tr>

      </table>
      <input type="hidden" name="kt_pk_usuarios_<?php echo $cnt1; ?>" class="id_field" value="<?php echo KT_escapeAttribute($row_rsusuarios['kt_pk_usuarios']); ?>" />
      <?php } while ($row_rsusuarios = mysql_fetch_assoc($rsusuarios)); ?>
      <div class="KT_bottombuttons">
        <div>
          <?php 
      // Show IF Conditional region1
      if (@$_GET['IdUsuario'] == "") {
      ?>
          <input type="submit" name="KT_Insert1" id="KT_Insert1" value="<?php echo NXT_getResource("Insert_FB"); ?>" />
          <?php 
      // else Conditional region1
      } else { ?>
			<input type="submit" name="KT_Update1" value="<?php echo NXT_getResource("Update_FB"); ?>" />
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
<?php echo $tNGs->getErrorMsg(); ?>
                
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
mysql_free_result($praca_listar);
?>
