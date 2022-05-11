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

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

require_once('../funcao.php');

// Load the common classes
require_once('../../includes/common/KT_common.php');

// Load the tNG classes
require_once('../../includes/tng/tNG.inc.php');

// Load the KT_back class
require_once('../../includes/nxt/KT_back.php');

// Make a transaction dispatcher instance
$tNGs = new tNG_dispatcher("../../");

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

if(
$row_usuario['controle_comunicado'] <> 'Y' and 
$row_usuario['controle_memorando'] <> 'Y' 
){
	header("Location: ../index.php"); 
	exit;
}

// comunicado
mysql_select_db($database_conexao, $conexao);
$query_comunicado = sprintf("
SELECT 
comunicado.*, 
usuarios.nome AS usuario_nome, 
(SELECT COUNT(comunicado_destinatario.IdComunicadoDestinatario) FROM comunicado_destinatario WHERE comunicado_destinatario.IdComunicado = comunicado.IdComunicado and IdComunicadoHistorico IS NULL and comunicado_destinatario.responsavel = 0) AS comunicado_destinatario_contador 
FROM comunicado 
LEFT JOIN usuarios ON usuarios.IdUsuario = comunicado.IdUsuario 
WHERE IdComunicado = %s 
", GetSQLValueString($_GET['IdComunicado'], "int"));
$comunicado = mysql_query($query_comunicado, $conexao) or die(mysql_error());
$row_comunicado = mysql_fetch_assoc($comunicado);
$totalRows_comunicado = mysql_num_rows($comunicado);
// fim - comunicado

// rscomunicado_historico
$colname_rscomunicado_historico = "-1";
if (isset($_GET['IdComunicadoHistorico'])) {
  $colname_rscomunicado_historico = $_GET['IdComunicadoHistorico'];
}
mysql_select_db($database_conexao, $conexao);
$query_rscomunicado_historico = sprintf("
SELECT 
comunicado_historico.*, 
usuarios.nome AS usuario_nome
FROM comunicado_historico 
LEFT JOIN usuarios ON usuarios.IdUsuario = comunicado_historico.IdUsuario
WHERE comunicado_historico.IdComunicadoHistorico = %s", 
GetSQLValueString($colname_rscomunicado_historico, "text"));
$rscomunicado_historico = mysql_query($query_rscomunicado_historico, $conexao) or die(mysql_error());
$row_rscomunicado_historico = mysql_fetch_assoc($rscomunicado_historico);
$totalRows_rscomunicado_historico = mysql_num_rows($rscomunicado_historico);
// fim - rscomunicado_historico

// destinatario_listar
mysql_select_db($database_conexao, $conexao);
$query_destinatario_listar = sprintf("
SELECT 
comunicado_destinatario.*, 
usuarios.praca, usuarios.nome 
FROM comunicado_destinatario 
LEFT JOIN usuarios ON comunicado_destinatario.IdUsuario = usuarios.IdUsuario 
WHERE IdComunicado = %s and comunicado_destinatario.IdComunicadoHistorico IS NULL and usuarios.IdUsuario <> %s 
ORDER BY usuarios.praca ASC, usuarios.nome ASC 
", 
GetSQLValueString($row_comunicado['IdComunicado'], "int"), 
GetSQLValueString($row_usuario['IdUsuario'], "int")); 
$destinatario_listar = mysql_query($query_destinatario_listar, $conexao) or die(mysql_error());
$row_destinatario_listar = mysql_fetch_assoc($destinatario_listar);
$totalRows_destinatario_listar = mysql_num_rows($destinatario_listar);
// fim - destinatario_listar

// insert --------------------------------------------------------------------------------------------------------------------------
if(
(isset($_POST["MM_insert"]) and $_POST["MM_insert"] == "form") and 
(strtotime($row_comunicado['data_limite']) >= strtotime(date('Y-m-d H:i:s')))
){
	
	$insert_SQL_comunicado_historico = sprintf("
	INSERT INTO comunicado_historico (status, IdComunicado, IdUsuario, data_criacao, texto) 
	VALUES (%s, %s, %s, %s, %s)
	",
	GetSQLValueString(1, "int"),
	GetSQLValueString($row_comunicado['IdComunicado'], "int"),
	GetSQLValueString($row_usuario['IdUsuario'], "int"),
	GetSQLValueString(date('Y-m-d H:i:s'), "date"),
	GetSQLValueString($_POST['texto'], "text"));
	
	mysql_select_db($database_conexao, $conexao);
	$Result_insert_comunicado_historico = mysql_query($insert_SQL_comunicado_historico, $conexao) or die(mysql_error());
	
	$ultimo_id = mysql_insert_id($conexao);
	
	// INSERT - comunicado_anexo
	$arquivo = NULL;
	if(!empty($_FILES['arquivo']['name'][0])){	
	
		// $funcao_upload_retorno ($arquivo)
		$funcao_upload_retorno = funcao_upload("../../arquivos/comunicado/", $_FILES['arquivo']);
		foreach ($funcao_upload_retorno as $retorno) {
			if($retorno['upload_retorno']==1){
				$arquivo = $retorno['upload_nome'];
				
				$update_SQL_slider_arquivo = sprintf("
				INSERT INTO comunicado_anexo 
				SET IdComunicado=%s, IdUsuario=%s, data_criacao=%s, arquivo=%s, IdComunicadoHistorico=%s  
				", 
				GetSQLValueString($row_comunicado['IdComunicado'], "int"),
				GetSQLValueString($row_usuario['IdUsuario'], "int"),
				GetSQLValueString(date('Y-m-d H:i:s'), "date"),
				GetSQLValueString($arquivo, "text"),
				GetSQLValueString($ultimo_id, "int")
				);
				mysql_select_db($database_conexao, $conexao);
				$Result_update_slider_arquivo = mysql_query($update_SQL_slider_arquivo, $conexao) or die(mysql_error());
				
			}
		}
		// fim - $funcao_upload_retorno ($arquivo)
		
	}
	// fim - INSERT - comunicado_anexo
	
	// INSERT/UPDATE - comunicado_destinatario
	if(count(@$_POST['destinatario']) > 0){
		foreach ($_POST['destinatario'] as $key => $value) {
			
			// insert - histórico (envia o histórico para os destinatários selecionados)
			$insert_SQL_comunicado_destinatario = sprintf("
			INSERT INTO comunicado_destinatario (data_criacao, IdComunicado, IdComunicadoHistorico, IdUsuario) 
			VALUES (%s, %s, %s, %s)
			",
			GetSQLValueString(date('Y-m-d H:i:s'), "date"),
			GetSQLValueString($row_comunicado['IdComunicado'], "int"),
			GetSQLValueString($ultimo_id, "int"),
			GetSQLValueString($value, "int"));
			mysql_select_db($database_conexao, $conexao);
			$Result_insert_comunicado_destinatario = mysql_query($insert_SQL_comunicado_destinatario, $conexao) or die(mysql_error());
			// fim - insert - histórico (envia o histórico para os destinatários selecionados)
			
			// update - principal (marca como não lido o comunicado principal para os destinatários selecionados)
			$update_SQL_comunicado_destinatario = sprintf("
			UPDATE comunicado_destinatario 
			SET lido = 0, lido_data = NULL 
			WHERE IdUsuario=%s and IdComunicado=%s and IdComunicadoHistorico IS NULL", 
			GetSQLValueString($value, "int"),
			GetSQLValueString($row_comunicado["IdComunicado"], "int"));
			
			mysql_select_db($database_conexao, $conexao);
			$Result_update_SQL_comunicado_destinatario = mysql_query($update_SQL_comunicado_destinatario, $conexao) or die(mysql_error());
			// fim - update - principal (marca como não lido o comunicado principal para os destinatários selecionados)
			
		}
	}
	// fim - INSERT/UPDATE - comunicado_destinatario
				
	$insertGoTo = "listar_historico.php?IdComunicado=".$row_comunicado['IdComunicado'];
	header(sprintf("Location: %s", $insertGoTo));
	exit;
	
}
// fim - insert --------------------------------------------------------------------------------------------------------------------
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
<style>
label.error { color: red; display: none; }
</style>
<script src="../../js/jquery.js"></script>

<script src="../../js/jquery.metadata.js" ></script>
<script src="../../js/jquery.validate.1.15.js"></script>
<script src="../../js/jquery.maskedinput-1.2.2.min.js"></script> 

<link href="../../includes/skins/mxkollection3.css" rel="stylesheet" type="text/css" media="all" />
<script src="../../includes/common/js/base.js" type="text/javascript"></script>
<script src="../../includes/common/js/utility.js" type="text/javascript"></script>
<script src="../../includes/skins/style.js" type="text/javascript"></script>
<script src="../../includes/nxt/scripts/form.js" type="text/javascript"></script>
<script src="../../includes/nxt/scripts/form.js.php" type="text/javascript"></script>
<script type="text/javascript">
$NXT_FORM_SETTINGS = {
  duplicate_buttons: false,
  show_as_grid: false,
  merge_down_value: false
}

$(document).ready(function() {

	// validação
	$("#form").validate({
		rules: {	
			texto: {required: true, minlength: 10},
			'destinatario[]': "required"
		},
		messages: {
			texto: "<br>Informe o texto com no mínimo 10 caracteres",
			'destinatario[]': "<br>Selecione pelo menos um destinatário.<br>"
		},
		onkeyup: false,
		submitHandler: function(form) {

			form.submit();
			
		} 
	});
	// fim - validação
	
	$('#checkall_situacao').click(function () {
		$(this).parents('fieldset:eq(0)').find(':checkbox').prop("checked", this.checked);
	});
	
	$('input[id="checkall_praca_situacao"]').click (function () {
		var praca_atual = $(this).attr('title');	
		$('input[id="destinatario"][title="'+praca_atual+'"]').prop("checked", this.checked);
	});
	
});
</script>
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
                <td align="left">
				<?php 
				// Show IF Conditional region1 
				if (@$_GET['IdComunicadoHistorico'] == "") {
				?>
				<?php echo NXT_getResource("Insert_FH"); ?>
				<?php 
				// else Conditional region1
				} else { ?>
				<?php echo NXT_getResource("Update_FH"); ?>
				<?php } 
				// endif Conditional region1
                ?> 
                Histórico de comunicado
                </td>
                <td align="right"><a href="javascript:history.go(-1);"><img src="../../imagens/botao_voltar.jpg" border="0" /></a></td>
                </tr>
                </table>
                </div>
                <div class="caminho">
                <a href="../index.php">Página inicial</a> &gt;&gt; 
                <a href="../comunicado/listar.php">Comunicados</a> &gt;&gt; 
                <a href="../comunicado/listar_historico.php?IdComunicado=<?php echo $row_comunicado['IdComunicado']; ?>">Comunicado</a> &gt;&gt; 
                Histórico
                </div>
                <!-- fim - titulo -->
                
                <div class="conteudo">
                
                    <div class="KT_tng">
                    
                        <div style="margin-top: 5px; margin-bottom: 5px; font-weight:bold; font-size: 16px;">
                        <?php echo $row_comunicado['assunto']; ?>
                        </div>
                        Criação: <? echo date('d-m-Y H:i', strtotime($row_comunicado['data_criacao'])); ?> | 
                        Remetente: <? echo $row_comunicado['usuario_nome']; ?> | 
                        Distribuição: 
                        <?php if($row_comunicado['comunicado_destinatario_contador'] == 1){ ?>Individual<? } else { ?>Coletivo<? } ?>
                        (<?php echo $row_comunicado['comunicado_destinatario_contador']; ?>) 
                         | 
                        Prioridade: <? echo $row_comunicado['prioridade']; ?> | 
                        Data resposta: <? echo date('d-m-Y', strtotime($row_comunicado['data_limite'])); ?>
                        <br>
                        
                        <div class="KT_tngform">
                        
                        <form id="form" name="form" method="POST" enctype="multipart/form-data" action="<?php echo $editFormAction; ?>" class="cmxform">
                        
                            <table cellpadding="2" cellspacing="0" class="KT_tngtable">
                              
                                                            
                              <tr>
                                <td class="KT_th"><label for="texto">Texto:</label></td>
                                <td>
                                <textarea name="texto" id="texto" cols="100" rows="5"><?php echo KT_escapeAttribute($row_rscomunicado_historico['texto']); ?></textarea>
								</td>
                              </tr>
                              
                              <? if ($totalRows_rscomunicado_historico == 0) { ?>
                              <tr>
                                <td class="KT_th"><label for="arquivo">Anexo:</label></td>
                                <td>
                                <input type="file" id="arquivo" name="arquivo[]" />
                                </td>
                              </tr>
                              <? } ?>
                              
                              <tr>
                                <td class="KT_th"><label for="texto">Destinatário(s):</label></td>
                                <td>
                                <fieldset style="border: 0; padding: 0;">
                                <label for="destinatario[]" class="error"></label>
                                <? if ($totalRows_rscomunicado_historico == 0) { ?>
                                	<div style="margin-bottom: 20px;">
                                		<input type="checkbox" id="checkall_situacao"  name="checkall_situacao" /> <strong>Marcar todos</strong>
									</div>
								<? } ?>
                                
                                <div style="clear: both;"></div>
                                <? $praca_atual = NULL; ?>
								<? do { ?>
                                    <?
                                    // destinatario_consultar
                                    mysql_select_db($database_conexao, $conexao);
                                    $query_destinatario_consultar = sprintf("
                                    SELECT COUNT(IdComunicadoDestinatario) as retorno, lido_data 
                                    FROM comunicado_destinatario 
                                    WHERE IdComunicado = %s and IdComunicadoHistorico = %s and IdUsuario = %s 
                                    ", 
									GetSQLValueString($row_comunicado['IdComunicado'], "int"), 
                                    GetSQLValueString($row_rscomunicado_historico['IdComunicadoHistorico'], "int"), 
                                    GetSQLValueString($row_destinatario_listar['IdUsuario'], "int")); 
                                    $destinatario_consultar = mysql_query($query_destinatario_consultar, $conexao) or die(mysql_error());
                                    $row_destinatario_consultar = mysql_fetch_assoc($destinatario_consultar);
                                    $totalRows_destinatario_consultar = mysql_num_rows($destinatario_consultar);
                                    // fim - destinatario_consultar
                                    ?>
                                    
                                    <!-- praca atual -->
                                    <? if($praca_atual != $row_destinatario_listar['praca']){ ?>
                                    <div style="clear: both;"></div>
                                    <div style="padding: 5px; background-color: #DDD; font-weight: bold;">
										<? echo $row_destinatario_listar['praca']; ?>
                                        <? if ($totalRows_rscomunicado_historico == 0) { ?>
                                            <div style="float: right;">
                                                <input type="checkbox" id="checkall_praca_situacao" name="checkall_praca_situacao[]" title="<? echo $row_destinatario_listar['praca']; ?>" /> <strong>Marcar todos da praça</strong>
                                            </div>
                                            <div style="clear: both;"></div>
										<? } ?>
                                    </div>
                                    <? } ?>
                                    <!-- fim - praca atual -->
                                    
                                    <div style="width: 300px; float: left; border: 0px solid #000; line-height: 1.2em; margin-bottom: 5px; min-height: 20px;">
                                    <input type="checkbox" id="destinatario" name="destinatario[]" value="<? echo $row_destinatario_listar['IdUsuario']; ?>" <? if($row_destinatario_consultar['retorno'] > 0){ ?>checked="checked"<? } ?> <? if ($totalRows_rscomunicado_historico > 0) { ?>disabled="disabled"<? } ?> title="<? echo $row_destinatario_listar['praca']; ?>"> 
                                    <? echo $row_destinatario_listar['nome']; ?>
                                    <? if($row_destinatario_consultar['lido_data'] <> NULL){ ?>
                                    <br>
                                    Lido em: <? echo date('d-m-Y H:i', strtotime($row_destinatario_consultar['lido_data'])); ?>
                                    <? } ?>
                                    </div>
                                    
                                    <? $praca_atual = $row_destinatario_listar['praca']; ?>
                                    <? mysql_free_result($destinatario_consultar); ?>
                                <?php } while ($row_destinatario_listar = mysql_fetch_assoc($destinatario_listar)); ?>
                                </fieldset>
								</td>
                              </tr>
                              
                            </table>
                            
                            <div class="KT_bottombuttons">
                                <div>
                                	<? if($totalRows_rscomunicado_historico == 0){ ?>
                                    <input type="hidden" name="MM_insert" value="form" />
                                    <input type="submit" value="Responder" />
                                    <? } ?>
                                    <input type="submit" onclick="window.history.back();" value="Voltar" />
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
mysql_free_result($comunicado); 
mysql_free_result($rscomunicado_historico); 
mysql_free_result($destinatario_listar);
?>
