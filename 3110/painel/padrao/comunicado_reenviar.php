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

// responder
if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "form")) {
	
	// $_POST['data_limite']
	if (isset($_POST['data_limite']) and $_POST['data_limite'] != "") {
		$data_limite = substr($_POST['data_limite'],0,10);
		$_POST['data_limite'] = implode("-",array_reverse(explode("-",$data_limite)))." 23:59:59";
	} else {
		$_POST['data_limite'] = NULL;
	}
	// fim - $_POST['data_limite']
	
	$update_SQL_comunicado = sprintf("
	UPDATE comunicado 
	SET data_limite=%s, data_reenvio=%s  
	WHERE IdComunicado=%s", 
	GetSQLValueString($_POST['data_limite'], "date"), 
	GetSQLValueString(date('Y-m-d H:i:s'), "date"), 
	
	GetSQLValueString($row_comunicado['IdComunicado'], "int"));
	mysql_select_db($database_conexao, $conexao);
	$Result_update_comunicado = mysql_query($update_SQL_comunicado, $conexao) or die(mysql_error());
	
	$_POST['destinatario'][] = $row_comunicado['IdUsuario'];
	
	// comunicado_destinatario
	if(count(@$_POST['destinatario']) > 0){
		foreach ($_POST['destinatario'] as $key => $value) {
			
			// comunicado_destinatario_consultar
			mysql_select_db($database_conexao, $conexao);
			$query_comunicado_destinatario_consultar = sprintf("
			SELECT COUNT(comunicado_destinatario.IdComunicadoDestinatario) AS retorno, comunicado_destinatario.IdComunicadoDestinatario 
			FROM comunicado_destinatario
			WHERE comunicado_destinatario.IdComunicado = %s and comunicado_destinatario.IdUsuario = %s and comunicado_destinatario.IdComunicadoHistorico IS NULL 
			ORDER BY comunicado_destinatario.IdComunicadoDestinatario ASC 
			", 
			GetSQLValueString($row_comunicado['IdComunicado'], "int"),
			GetSQLValueString($value, "int")); 
			$comunicado_destinatario_consultar = mysql_query($query_comunicado_destinatario_consultar, $conexao) or die(mysql_error());
			$row_comunicado_destinatario_consultar = mysql_fetch_assoc($comunicado_destinatario_consultar);
			$totalRows_comunicado_destinatario_consultar = mysql_num_rows($comunicado_destinatario_consultar);
			// fim - comunicado_destinatario_consultar

			if($row_comunicado_destinatario_consultar['retorno'] == 1){ 

				$update_SQL_comunicado_destinatario = sprintf("
				UPDATE comunicado_destinatario 
				SET lido=%s, lido_data=%s 
				WHERE IdComunicadoDestinatario=%s", 
				GetSQLValueString(0, "int"), 
				GetSQLValueString(NULL, "date"), 
				
				GetSQLValueString($row_comunicado_destinatario_consultar['IdComunicadoDestinatario'], "int"));
				mysql_select_db($database_conexao, $conexao);
				$Result_update_comunicado_destinatario = mysql_query($update_SQL_comunicado_destinatario, $conexao) or die(mysql_error());
				
			} else {
			
				$insert_SQL_comunicado_destinatario = sprintf("
				INSERT INTO comunicado_destinatario (data_criacao, IdComunicado, IdUsuario, responsavel) 
				VALUES (%s, %s, %s, %s)
				",
				GetSQLValueString(date('Y-m-d H:i:s'), "date"),
				GetSQLValueString($row_comunicado["IdComunicado"], "int"),
				GetSQLValueString($value, "int"),
				GetSQLValueString(0, "int"));
				mysql_select_db($database_conexao, $conexao);
				$Result_insert_comunicado_destinatario = mysql_query($insert_SQL_comunicado_destinatario, $conexao) or die(mysql_error());
				
			}
			
			mysql_free_result($comunicado_destinatario_consultar);
			
		}
	}
	// fim - comunicado_destinatario
	
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
// fim - responder

// destinatario_listar
mysql_select_db($database_conexao, $conexao);
$query_destinatario_listar = sprintf("
SELECT usuarios.IdUsuario, usuarios.nome, usuarios.praca   
FROM usuarios 
WHERE status = 1 and usuarios.IdUsuario <> %s 
ORDER BY usuarios.praca ASC, usuarios.nome ASC 
", 
GetSQLValueString($row_comunicado['IdUsuario'], "int")); 
$destinatario_listar = mysql_query($query_destinatario_listar, $conexao) or die(mysql_error());
$row_destinatario_listar = mysql_fetch_assoc($destinatario_listar);
$totalRows_destinatario_listar = mysql_num_rows($destinatario_listar);
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
	
	// validação
	$("#form").validate({
		rules: {	
			data_limite: "required",
			'destinatario[]': "required"
		},
		messages: {
			data_limite: "<br>obrigatório",
			'destinatario[]': "<br>Selecione pelo menos um destinatário.<br>"
		},
		onkeyup: false,
		submitHandler: function(form) {

			$('#submit').attr('disabled', 'disabled');
			form.submit();
			
		} 
	});
	// fim - validação
	
	$("#data_limite").mask("99-99-9999", {placeholder:" "});
	
	// data_limite
    $('#data_limite').blur(function(){

		var campo = $(this);
			
		data_limite_erro = funcao_verifica_data_valida(campo) // chamada da função (retorna 0/1)
		
		// confere se 'data entrada' é menor ou igual a 'data atual'
		if(data_limite_erro==0 && campo.val().length == 10){
			
			// data_entrada
			value = campo.val();
			var quebraDE = value.split("-");
			
			var diaDE = quebraDE[0];
			var mesDE = quebraDE[1];
			var anoDE = quebraDE[2].substr(0,4);
			
			var data_entrada = anoDE+'/'+mesDE+'/'+diaDE+ ' 23:59:59';

			var dataDE = new Date(data_entrada);

			dataDE.setHours(23, 59, 59, 59);
			// fim - data_entrada

			// data_anterior
			var dataDA = new Date(<?php echo time() * 1000 ?>);
			dataDA.setHours(23, 59, 59, 59);
			var diaDA = dataDA.getDate();
			dataDA.setDate(diaDA);
			// fim - data_anterior
			
			if(dataDE.getTime() < dataDA.getTime()){data_limite_erro = 1;}
			
		}
		// fim - confere se 'data entrada' é menor ou igual a 'data atual'
		
		// data_limite_erro
		if(data_limite_erro==1){
			
			alert("Data inválida");
			$('#data_limite').val('');
			setTimeout(function() {campo.focus();}, 100);
			return false;
			event.preventDefault();
			
		} else {
			
			data_limite_erro = 0;
			
		}
		// fim - data_limite_erro
		
    });
	// fim - data_limite
	
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
		<strong>Reenviar</strong> 
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
        
		<!-- data_limite -->
        <div style="padding-bottom: 10px;">
 	       <div class="label_solicitacao2">Data Resposta:</div>
    	   <input type="text" name="data_limite" id="data_limite" value="<?php echo date('d-m-Y', strtotime("+1 month", strtotime($row_comunicado['data_limite']))); ?>" />
		</div>
		<!-- fim - data_limite -->

		<!-- Destinatários -->
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
		<!-- fim - Destinatários -->
        
        
		<!-- Botões -->        
        <div style="padding-top: 10px;">
        
			<input type="hidden" id="IdComunicado" name="IdComunicado" value="<? echo $row_comunicado['IdComunicado']; ?>" />
	        <input type="hidden" name="MM_update" value="form" />
            
	        <input type="submit" name="submit" id="submit" value="Reenviar" class="botao_geral2" style="width: 90px" /> 

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