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

require_once('../parametros.php');
require_once('../funcao.php');

$janela = NULL;
$janela_url = NULL;
if (isset($_GET['janela'])) {
  $janela = $_GET['janela'];
  if($janela == "index"){$janela_url = "&janela=index";}
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

// função que limita caracteres
function limita_caracteres($string, $tamanho, $encode = 'UTF-8') {
    if( strlen($string) > $tamanho ){
        $string = mb_substr($string, 0, $tamanho, $encode);
	}
    return $string;
}
// fim - função que limita caracteres

// comunicado
mysql_select_db($database_conexao, $conexao);
$query_comunicado = sprintf("
SELECT 
comunicado.*, 
usuarios.nome AS remetente 
FROM comunicado 
LEFT JOIN usuarios ON usuarios.IdUsuario = comunicado.IdUsuario 
WHERE comunicado.IdComunicado = %s and 
EXISTS (
	SELECT 'x' 
	FROM comunicado_destinatario 
	WHERE comunicado_destinatario.IdUsuario = %s and comunicado_destinatario.IdComunicado = %s
)
", 
GetSQLValueString($_GET['IdComunicado'], "int"),
GetSQLValueString($row_usuario['IdUsuario'], "int"),
GetSQLValueString($_GET['IdComunicado'], "int"));
$comunicado = mysql_query($query_comunicado, $conexao) or die(mysql_error());
$row_comunicado = mysql_fetch_assoc($comunicado);
$totalRows_comunicado = mysql_num_rows($comunicado);
// fim - comunicado

// responder - UPDATE
if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form")) {

	// insert
	$responderSQL = sprintf("
	INSERT INTO comunicado_historico (IdComunicado, IdUsuario, data_criacao, texto) 
	VALUES (%s, %s, %s, %s)
	",
	GetSQLValueString($_POST["IdComunicado"], "int"),
	GetSQLValueString($row_usuario['IdUsuario'], "int"),
	GetSQLValueString(date('Y-m-d H:i:s'), "date"),
	GetSQLValueString($_POST['texto'], "text"));
	mysql_select_db($database_conexao, $conexao);
	$Result1 = mysql_query($responderSQL, $conexao) or die(mysql_error());
	// fim - insert
	
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
				GetSQLValueString($_POST["IdComunicado"], "int"),
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
			GetSQLValueString($_POST["IdComunicado"], "int"));
			
			mysql_select_db($database_conexao, $conexao);
			$Result_update_SQL_comunicado_destinatario = mysql_query($update_SQL_comunicado_destinatario, $conexao) or die(mysql_error());
			// fim - update - principal (marca como não lido o comunicado principal para os destinatários selecionados)

		}
	}
	// fim - INSERT/UPDATE - comunicado_destinatario
	
	// lido
	$updateSQL_leu = sprintf("
	UPDATE comunicado_destinatario 
	SET comunicado_destinatario.lido=%s, comunicado_destinatario.lido_data=%s 
	WHERE 
	comunicado_destinatario.IdComunicado=%s and 
	comunicado_destinatario.IdUsuario=%s and 
	IdComunicadoHistorico IS NULL
	",
	GetSQLValueString(1, "int"),
	GetSQLValueString(date("Y-m-d H:i:s"), "date"),
	
	GetSQLValueString($row_comunicado['IdComunicado'], "int"),
	GetSQLValueString($row_usuario['IdUsuario'], "int"));
	
	mysql_select_db($database_conexao, $conexao);
	$Result_leu = mysql_query($updateSQL_leu, $conexao) or die(mysql_error());
	// fim - lido
		
	// redireciona
	$responderGoTo = "../padrao/comunicado_detalhe.php?IdComunicado=".$row_comunicado['IdComunicado'].$janela_url;
	echo sprintf("<meta http-equiv=\"refresh\" content=\"0; url=%s\">", $responderGoTo); 
	exit;
	// fim - redireciona
  
}
// fim - responder - UPDATE

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
if($totalRows_destinatario_listar == 0){ echo "<meta http-equiv=\"refresh\" content=\"0; url='../padrao/comunicado_detalhe.php?IdComunicado=".$row_comunicado['IdComunicado'].$janela_url."'\">"; exit;}
// fim - destinatario_listar
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title></title>
<link rel="stylesheet" href="../../css/suporte.css" type="text/css" media="screen" />
<style>
/* erro de validação */
label.error { color: red; display: none; }	
/* fim - erro de validação */

/* calendário */
.ui-timepicker-div .ui-widget-header { margin-bottom: 8px; }
.ui-timepicker-div dl { text-align: left; }
.ui-timepicker-div dl dt { height: 25px; margin-bottom: -25px; }
.ui-timepicker-div dl dd { margin: 0 10px 10px 65px; }
.ui-timepicker-div td { font-size: 90%; }
.ui-tpicker-grid-label { background: none; border: none; margin: 0; padding: 0; }

.ui-timepicker-rtl{ direction: rtl; }
.ui-timepicker-rtl dl { text-align: right; }
.ui-timepicker-rtl dl dd { margin: 0 65px 10px 10px; }
/* fim - calendário */

.ui-datepicker-trigger {
margin-left : 5px;
vertical-align : top;
}

.ui-dialog{
	font-size: 12px;
}
</style>
<script type="text/javascript" src="../../js/jquery.js"></script>

<script type="text/javascript" src="../../funcoes.js"></script>

<script src="../../ckeditor/ckeditor.js"></script>

<script src="../../js/jquery.metadata.js" ></script>
<script src="../../js/jquery.validate.1.15.js"></script>
<script src="../../js/jquery.maskedinput-1.2.2.min.js"></script> 

<!--[if !IE]> -->
<style>
body{
	overflow-y: scroll; /* se não é IE, então mostra a scroll vertical */
}
</style>
<!-- <![endif]-->

<script type="text/javascript">
$(document).ready(function() {
	
	// ckeditor
	CKEDITOR.replace( 'texto', {
		height: '200'
	});
	// fim - ckeditor

	// validação
	$("#form").validate({
		rules: {	
			texto:  {required: true, minlength: 10},
			'destinatario[]': "required"
		},
		messages: {
			texto:  "<br>Informe o texto com no mínimo 10 caracteres",
			'destinatario[]': "<br>Selecione pelo menos um destinatário.<br>"
		},
		onkeyup: false,
		submitHandler: function(form) {

			$('#submit').attr('disabled', 'disabled');
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
</head>
<body>

<div class="div_solicitacao_linhas" id="cabecalho_solicitacoes" style="cursor: pointer">
<table cellspacing="0" cellpadding="0" width="100%">
	<tr>
		<td style="text-align:left">
		Comunicado número: <?php echo $row_comunicado['IdComunicado']; ?>
		</td>
        
		<td style="text-align: right">
        &lt;&lt; <a href="../padrao/comunicado_detalhe.php?IdComunicado=<? echo $row_comunicado['IdComunicado']; ?><? if($janela == "index"){ ?>&janela=index<? } ?>">Voltar</a>
        </td>
	</tr>
</table>
</div>

<div class="div_solicitacao_linhas3">
<table cellspacing="0" cellpadding="0">
	<tr>
		<td style="text-align: left">
		<strong>Responder</strong> 
		</td>
	</tr>
</table>
</div>

<div class="div_solicitacao_linhas3">
<table cellspacing="0" cellpadding="0">
	<tr>
		<td style="text-align: left; font-weight: bold; font-size: 14px;">
		Título: <? echo $row_comunicado['assunto']; ?> 
		<div style="font-size: 12px; font-weight: normal;">Remetente: <? echo $row_comunicado['remetente']; ?></div>
		</td>
	</tr>
</table>
</div>

<div class="div_solicitacao_linhas2">
<table cellspacing="0" cellpadding="0" width="100%">
	<tr>
		<td style="text-align: left">
        <form id="form" name="form" method="POST" action="<?php echo $editFormAction; ?>" enctype="multipart/form-data" class="cmxform">

		<!-- Observação -->
        <div style="padding-bottom: 10px;">
 	       <div class="label_solicitacao2">Texto:</div>
    	    <textarea name="texto" id="texto" cols="90" rows="10" /></textarea>
		</div>
		<!-- fim - Observação -->
        
		<!-- Anexo -->
        <div style="padding-bottom: 10px;">
 	       <div class="label_solicitacao2">Anexo:</div>
    	    <input type="file" id="arquivo" name="arquivo[]" />
		</div>
		<!-- fim - Anexo -->

		<!-- Destinatários -->
					<? if($totalRows_destinatario_listar > 0){ ?>
        <div style="padding-bottom: 10px;">
            <div class="label_solicitacao2">Destinatário(s):</div>
            <fieldset style="border: 0; padding: 0;">
            <label for="destinatario[]" class="error"></label>
            <div style="margin-bottom: 20px;">
            	<input type="checkbox" id="checkall_situacao"  name="checkall_situacao" /> <strong>Marcar todos</strong>
			</div>
            
            <div style="clear: both;"></div>
            <? $praca_atual = NULL; ?>
            <? do { ?>
            
                <!-- praca atual -->
                <? if($praca_atual != $row_destinatario_listar['praca']){ ?>
                <div style="clear: both;"></div>
                <div style="padding: 5px; background-color: #DDD; font-weight: bold;">
                    <? echo $row_destinatario_listar['praca']; ?>
                    <div style="float: right;">
                        <input type="checkbox" id="checkall_praca_situacao" name="checkall_praca_situacao[]" title="<? echo $row_destinatario_listar['praca']; ?>" /> <strong>Marcar todos da praça</strong>
                    </div>
                    <div style="clear: both;"></div>
                </div>
                <? } ?>
                <!-- fim - praca atual -->
                                    
                <div style="width: 250px; float: left; border: 0px solid #000; line-height: 1.2em; margin-bottom: 5px; min-height: 20px;">
                <input type="checkbox" id="destinatario" name="destinatario[]" value="<? echo $row_destinatario_listar['IdUsuario']; ?>" title="<? echo $row_destinatario_listar['praca']; ?>"> 
                <? echo $row_destinatario_listar['nome']; ?>
                </div>
                
                <? $praca_atual = $row_destinatario_listar['praca']; ?>
            <?php } while ($row_destinatario_listar = mysql_fetch_assoc($destinatario_listar)); ?>
            </fieldset>
		</div>
		<? } ?>
		<!-- fim - Destinatários -->
        
        
		<!-- Botões -->        
        <div style="padding-top: 10px;">
        
			<input type="hidden" id="IdComunicado" name="IdComunicado" value="<? echo $row_comunicado['IdComunicado']; ?>" />
	        <input type="hidden" name="MM_update" value="form" />
            
	        <input type="submit" name="submit" id="submit" value="Responder" class="botao_geral2" style="width: 90px" /> 

		</div>
		<!-- fim - Botões -->
        
        </form>
		</td>
	</tr>
</table>
</div>

</body>

</html>
<?php 
mysql_free_result($usuario); 
mysql_free_result($comunicado);
mysql_free_result($destinatario_listar);
?>