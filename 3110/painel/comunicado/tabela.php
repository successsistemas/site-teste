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

// Load the common classes
require_once('../../includes/common/KT_common.php');

// Load the tNG classes
require_once('../../includes/tng/tNG.inc.php');

// Load the KT_back class
require_once('../../includes/nxt/KT_back.php');

// Make a transaction dispatcher instance
$tNGs = new tNG_dispatcher("../../");

// rscomunicado
$colname_rscomunicado = "-1";
if (isset($_GET['IdComunicado'])) {
  $colname_rscomunicado = $_GET['IdComunicado'];
}
mysql_select_db($database_conexao, $conexao);
$query_rscomunicado = sprintf("
SELECT 
comunicado.*, 
usuarios.nome AS usuario_nome
FROM comunicado 
LEFT JOIN usuarios ON usuarios.IdUsuario = comunicado.IdUsuario
WHERE comunicado.IdComunicado = %s", 
GetSQLValueString($colname_rscomunicado, "text"));
$rscomunicado = mysql_query($query_rscomunicado, $conexao) or die(mysql_error());
$row_rscomunicado = mysql_fetch_assoc($rscomunicado);
$totalRows_rscomunicado = mysql_num_rows($rscomunicado);
// fim - rscomunicado

// destinatario_listar
$where_destinatario_listar = "status = 1";
if ($totalRows_rscomunicado == 0 and $row_usuario['controle_comunicado'] == "Y") {
	$where_destinatario_listar .= " and usuarios.IdUsuario <> ".$row_usuario['IdUsuario'];
}
mysql_select_db($database_conexao, $conexao);
$query_destinatario_listar = "
SELECT * 
FROM usuarios 
WHERE $where_destinatario_listar  
ORDER BY praca ASC, nome ASC 
"; 
$destinatario_listar = mysql_query($query_destinatario_listar, $conexao) or die(mysql_error());
$row_destinatario_listar = mysql_fetch_assoc($destinatario_listar);
$totalRows_destinatario_listar = mysql_num_rows($destinatario_listar);
// fim - destinatario_listar

// delete --------------------------------------------------------------------------------------------------------------------------
if(
(isset($row_rscomunicado['IdComunicado'])) and ($row_rscomunicado['IdComunicado'] != "") and 
(isset($_GET['acao'])) and ($_GET['acao'] == "deletar") and 
($row_rscomunicado['tipo'] == "m")
){
	
	// comunicado
	mysql_select_db($database_conexao, $conexao);	
	$delete_SQL_comunicado = sprintf("
	DELETE FROM comunicado 
	WHERE IdComunicado=%s", 
	GetSQLValueString($row_rscomunicado['IdComunicado'], "int"));
	$Result_comunicado = mysql_query($delete_SQL_comunicado, $conexao) or die(mysql_error());
	// fim - comunicado
	
	// comunicado_destinatario
	mysql_select_db($database_conexao, $conexao);	
	$delete_SQL_comunicado_destinatario = sprintf("
	DELETE FROM comunicado_destinatario 
	WHERE IdComunicado=%s", 
	GetSQLValueString($row_rscomunicado['IdComunicado'], "int"));
	$Result_comunicado_destinatario = mysql_query($delete_SQL_comunicado_destinatario, $conexao) or die(mysql_error());
	// fim - comunicado_destinatario
	
	// comunicado_historico
	mysql_select_db($database_conexao, $conexao);	
	$delete_SQL_comunicado_historico = sprintf("
	DELETE FROM comunicado_historico 
	WHERE IdComunicado=%s", 
	GetSQLValueString($row_rscomunicado['IdComunicado'], "int"));
	$Result_comunicado_historico = mysql_query($delete_SQL_comunicado_historico, $conexao) or die(mysql_error());
	// fim - comunicado_historico

	// comunicado_anexo (arquivos)
	mysql_select_db($database_conexao, $conexao);
	$query_comunicado_anexo = sprintf("
	SELECT comunicado_anexo.*  
	FROM comunicado_anexo 
	WHERE comunicado_anexo.IdComunicado = %s 
	ORDER BY comunicado_anexo.IdComunicadoAnexo DESC", 
	GetSQLValueString($row_rscomunicado['IdComunicado'], "int"));
	$comunicado_anexo = mysql_query($query_comunicado_anexo, $conexao) or die(mysql_error());
	$row_comunicado_anexo = mysql_fetch_assoc($comunicado_anexo);
	$totalRows_comunicado_anexo = mysql_num_rows($comunicado_anexo);
	
	if($totalRows_comunicado_anexo > 0){ 
		do {
			$arquivo_atual = "../../arquivos/comunicado/".$row_comunicado_anexo['arquivo'];
			if(file_exists($arquivo_atual)){
				unlink($arquivo_atual);
			}
		} while ($row_comunicado_anexo = mysql_fetch_assoc($comunicado_anexo));
	}
	mysql_free_result($comunicado_anexo);
	// fim - comunicado_anexo (arquivos)
	
	// comunicado_anexo
	mysql_select_db($database_conexao, $conexao);	
	$delete_SQL_comunicado_anexo = sprintf("
	DELETE FROM comunicado_anexo 
	WHERE IdComunicado=%s", 
	GetSQLValueString($row_rscomunicado['IdComunicado'], "int"));
	$Result_comunicado_anexo = mysql_query($delete_SQL_comunicado_anexo, $conexao) or die(mysql_error());
	// fim - comunicado_anexo

	$deleteGoTo = "listar.php?aba=enviados";
	header(sprintf("Location: %s", $deleteGoTo));
	exit;
	
}
// fim - delete --------------------------------------------------------------------------------------------------------------------


// insert --------------------------------------------------------------------------------------------------------------------------
if(
(isset($_POST["MM_insert"]) and $_POST["MM_insert"] == "form")
){
	
	// $_POST['data_limite']
	if (isset($_POST['data_limite']) and $_POST['data_limite'] != "") {
		$data_limite = substr($_POST['data_limite'],0,10);
		$_POST['data_limite'] = implode("-",array_reverse(explode("-",$data_limite)))." 23:59:59";
	} else {
		$_POST['data_limite'] = NULL;
	}
	// fim - $_POST['data_limite']

	$prioridade_justificativa = NULL;
	if (isset($_POST['prioridade_justificativa'])) {
		$prioridade_justificativa = $_POST['prioridade_justificativa'];
	}
		
	$insert_SQL_comunicado = sprintf("
	INSERT INTO comunicado (status, IdUsuario, data_criacao, prioridade, prioridade_justificativa, data_limite, tipo, assunto, texto) 
	VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s)
	",
	GetSQLValueString(1, "int"),
	GetSQLValueString($row_usuario['IdUsuario'], "int"),
	GetSQLValueString(date('Y-m-d H:i:s'), "date"),
	GetSQLValueString($_POST['prioridade'], "text"),
	GetSQLValueString($prioridade_justificativa, "text"),
	GetSQLValueString($_POST['data_limite'], "date"),
	GetSQLValueString($_POST['tipo'], "text"),
	GetSQLValueString($_POST['assunto'], "text"),
	GetSQLValueString($_POST['texto'], "text"));
	mysql_select_db($database_conexao, $conexao);
	$Result_insert_comunicado = mysql_query($insert_SQL_comunicado, $conexao) or die(mysql_error());
	
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
				SET IdComunicado=%s, IdUsuario=%s, data_criacao=%s, arquivo=%s 
				", 
				GetSQLValueString($ultimo_id, "int"),
				GetSQLValueString($row_usuario['IdUsuario'], "int"),
				GetSQLValueString(date('Y-m-d H:i:s'), "date"),
				GetSQLValueString($arquivo, "text")
				);
				mysql_select_db($database_conexao, $conexao);
				$Result_update_slider_arquivo = mysql_query($update_SQL_slider_arquivo, $conexao) or die(mysql_error());
				
			}
		}
		// fim - $funcao_upload_retorno ($arquivo)
		
	}
	// fim - INSERT - comunicado_anexo
	
	$_POST['destinatario'][] = $row_usuario['IdUsuario'];
	
	// INSERT - comunicado_destinatario
	if(count(@$_POST['destinatario']) > 0){
		foreach ($_POST['destinatario'] as $key => $value) {
			
			$responsavel = 0;
			if($value == $row_usuario['IdUsuario']){
				$responsavel = 1;
			}
					
			$insert_SQL_comunicado_destinatario = sprintf("
			INSERT INTO comunicado_destinatario (data_criacao, IdComunicado, IdUsuario, responsavel) 
			VALUES (%s, %s, %s, %s)
			",
			GetSQLValueString(date('Y-m-d H:i:s'), "date"),
			GetSQLValueString($ultimo_id, "int"),
			GetSQLValueString($value, "int"),
			GetSQLValueString($responsavel, "int"));
			mysql_select_db($database_conexao, $conexao);
			$Result_insert_comunicado_destinatario = mysql_query($insert_SQL_comunicado_destinatario, $conexao) or die(mysql_error());
			
		}
	}
	// fim - INSERT - comunicado_destinatario
	
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
	
	GetSQLValueString($ultimo_id, "int"),
	GetSQLValueString($row_usuario['IdUsuario'], "int"));
	
	mysql_select_db($database_conexao, $conexao);
	$Result_leu = mysql_query($updateSQL_leu, $conexao) or die(mysql_error());
	// fim - lido
				
	$insertGoTo = "listar.php";
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

<script src="../../funcoes.js"></script>

<script src="../../ckeditor/ckeditor.js"></script>

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

	// ckeditor
	CKEDITOR.replace( 'texto', {
		height: '200'
	});
	// fim - ckeditor

	// validação
	$("#form").validate({
		rules: {	
			prioridade: "required",
			prioridade_justificativa: {
				required: function(element) {
					return $("#prioridade").val() == 'Alta';
				}
			},
			data_limite: "required",
			tipo: "required",
			assunto: "required",
			'destinatario[]': "required"
		},
		messages: {
			prioridade: "<br>obrigatório",
			prioridade_justificativa: "<br>obrigatório",
			data_limite: "<br>obrigatório",
			tipo: "<br>obrigatório",
			assunto: "<br>obrigatório",
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
	
	// botao_deletar	
	$('#botao_deletar').click(function () {
		var titulo_atual = '<? echo $row_rscomunicado['assunto']; ?>'
		return confirm("Confirma deletar o Memorando: " + titulo_atual + " ?");
	});
	// fim - botao_deletar

	//region - prioridade ***************************************************************************
	$("#prioridade_justificativa_caixa").hide();

	$("textarea[id='prioridade_justificativa']").val(''); // limpa 'justificativa'
	
	$("select[id='prioridade']").change(function () { // ao mudar o valor do select 'prioridade'
		$("select[id='prioridade'] option:selected").each(function () {
			prioridade_atual = $(this).text();
			
			// se prioridade é: Alta
			if( prioridade_atual=="Alta" ){
				
				$("#prioridade_justificativa_caixa").show();

				$("textarea[id='prioridade_justificativa']").val('');
								
			} 
			// fim - se prioridade é: Alta
			
			// se não
			else {
				
				$("#prioridade_justificativa_caixa").hide();

				$("textarea[id='prioridade_justificativa']").val('');

			}
			// fim - se não
			
		});
	})
	//endregion - fim - prioridade ******************************************************************

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
                <td align="left">
				<?php 
				if($row_usuario['controle_comunicado'] == 'Y'){
					// Show IF Conditional region1 
					if (@$_GET['IdComunicado'] == "") {
					?>
					<?php echo NXT_getResource("Insert_FH"); ?>
					<?php 
					// else Conditional region1
					} else { ?>
					<?php echo NXT_getResource("Update_FH"); ?>
					<?php } 
					// endif Conditional region1
				}
                ?> 
                Comunicado
                </td>
                <td align="right"><a href="javascript:history.go(-1);"><img src="../../imagens/botao_voltar.jpg" border="0" /></a></td>
                </tr>
                </table>
                </div>
                <div class="caminho"><a href="../index.php">Página inicial</a> &gt;&gt; Comunicado</div>
                <!-- fim - titulo -->
                
                <div class="conteudo">
                
                    <div class="KT_tng">
                        <div class="KT_tngform">
                        
                        <form id="form" name="form" method="POST" enctype="multipart/form-data" action="<?php echo $editFormAction; ?>" class="cmxform">
                        
                            <table cellpadding="2" cellspacing="0" class="KT_tngtable">
                                      
                              <tr>
                                <td class="KT_th">Criação:</td>
                                <td><?php echo KT_formatDate($row_rscomunicado['data_criacao']); ?></td>
                              </tr>
                              <tr>
                                <td class="KT_th">Remetente:</td>
                                <td><?php echo KT_FormatForList($row_rscomunicado['usuario_nome'], 20); ?></td>
                              </tr>
                              
                              <tr>
                                <td class="KT_th"><label for="tipo">Tipo:</label></td>
                                <td>
                               	<? if($row_usuario['controle_comunicado'] == 'Y' and $totalRows_rscomunicado == 0){ ?>
                                    <select name="tipo" id="tipo" style="width: 200px;">
                                    <option value="c" <?php if (!(strcmp("c", KT_escapeAttribute($row_rscomunicado['tipo'])))) {echo "selected=\"selected\"";} ?>>Comunicado</option>
                                    <? if($row_usuario['controle_memorando'] == 'Y'){ ?>
                                    <option value="m" <?php if (!(strcmp("m", KT_escapeAttribute($row_rscomunicado['tipo'])))) {echo "selected=\"selected\"";} ?>>Memorando</option>
                                    <? } ?>
                                    </select>
                                <? } else { ?>
                                	<?php if($row_rscomunicado['tipo'] == "c"){ ?>Comunicado<? } ?>
                                	<?php if($row_rscomunicado['tipo'] == "m"){ ?>Memorando<? } ?>
                                <? } ?>
                                </td>
                              </tr>
                              
                              <tr>
                                <td class="KT_th"><label for="assunto">Assunto:</label></td>
                                <td>
                               	<? if($row_usuario['controle_comunicado'] == 'Y'){ ?>
                                <input type="text" name="assunto" id="assunto" value="<?php echo KT_escapeAttribute($row_rscomunicado['assunto']); ?>" style="width: 628px;" />
                                <? } else { ?>
                                <?php echo $row_rscomunicado['assunto']; ?>
                                <? } ?>
                                </td>
                              </tr>
                              
                              <tr>
                                <td class="KT_th"><label for="prioridade">Prioridade:</label></td>
                                <td>

									<? if($row_usuario['controle_comunicado'] == 'Y'){ ?>

										<select name="prioridade" id="prioridade" style="width: 200px;">
										<option value="Baixa" <?php if (!(strcmp("Baixa", KT_escapeAttribute($row_rscomunicado['prioridade'])))) {echo "selected=\"selected\"";} ?>>Baixa</option>
										<option value="Média" <?php if (!(strcmp("Média", KT_escapeAttribute($row_rscomunicado['prioridade'])))) {echo "selected=\"selected\"";} ?>>Média</option>
										<option value="Alta" <?php if (!(strcmp("Alta", KT_escapeAttribute($row_rscomunicado['prioridade'])))) {echo "selected=\"selected\"";} ?>>Alta</option>
										</select>

									<? } else { ?>

										<?php echo $row_rscomunicado['prioridade']; ?>

									<? } ?>								
                                </td>
							  </tr>
							  
                              <tr id="prioridade_justificativa_caixa">
                                <td class="KT_th"><label for="prioridade">Justificativa:</label></td>
                                <td>
									<textarea name="prioridade_justificativa" id="prioridade_justificativa" cols="100" rows="5"></textarea>
                                </td>
                              </tr>
                              
                              <tr>
                                <td class="KT_th"><label for="data_limite">Data Resposta:</label></td>
                                <td>
                               	<? if($row_usuario['controle_comunicado'] == 'Y'){ ?>
                                <input type="text" name="data_limite" id="data_limite" value="<?php if(@$_GET['IdComunicado'] == "") { echo date('d-m-Y', strtotime("+1 month", strtotime(date('d-m-Y')))); } else { echo KT_formatDate($row_rscomunicado['data_limite']); } ?>" style="width: 188px;" />
                                <? } else { ?>
                                <?php echo $row_rscomunicado['data_limite']; ?>
                                <? } ?>
                                </td>
                              </tr>
                                                            
                              <tr>
                                <td class="KT_th"><label for="texto">Texto:</label></td>
                                <td>
                               	<? if($row_usuario['controle_comunicado'] == 'Y'){ ?>
                                <textarea name="texto" id="texto" cols="100" rows="15"><?php echo KT_escapeAttribute($row_rscomunicado['texto']); ?></textarea>
                                <? } else { ?>
                                <div style="width: 690px;">
                                <textarea name="texto" id="texto" cols="100" rows="15" readonly="readonly"><?php echo KT_escapeAttribute($row_rscomunicado['texto']); ?></textarea>
                                </div>
                                <? } ?>
								</td>
                              </tr>
                              
                              <? if ($totalRows_rscomunicado == 0) { ?>
                              <tr>
                                <td class="KT_th"><label for="arquivo">Anexo:</label></td>
                                <td>
                                <input type="file" id="arquivo" name="arquivo[]" />
                                </td>
                              </tr>
                              <? } ?>
                              
                              <? if($row_usuario['controle_comunicado'] == 'Y'){ ?>
                              <tr>
                                <td class="KT_th"><label for="texto">Destinatário(s):</label></td>
                                <td>
                                <fieldset style="border: 0; padding: 0;">
                                <label for="destinatario[]" class="error"></label>
                                <? if ($totalRows_rscomunicado == 0) { ?>
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
                                    WHERE IdComunicado = %s and IdComunicadoHistorico IS NULL and IdUsuario = %s 
                                    ", 
                                    GetSQLValueString($row_rscomunicado['IdComunicado'], "int"), 
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
                                        <? if ($totalRows_rscomunicado == 0) { ?>
                                            <div style="float: right;">
                                                <input type="checkbox" id="checkall_praca_situacao" name="checkall_praca_situacao[]" title="<? echo $row_destinatario_listar['praca']; ?>" /> <strong>Marcar todos da praça</strong>
                                            </div>
                                            <div style="clear: both;"></div>
										<? } ?>
                                    </div>
                                    <? } ?>
                                    <!-- fim - praca atual -->
                                    
                                    <div style="width: 300px; float: left; border: 0px solid #000; line-height: 1.2em; margin-bottom: 5px; min-height: 20px;">
                                    <input type="checkbox" id="destinatario" name="destinatario[]" value="<? echo $row_destinatario_listar['IdUsuario']; ?>" 
									<? if( ($row_destinatario_consultar['retorno'] > 0) or (isset($_GET["destinatario"]) and $_GET["destinatario"] == $row_destinatario_listar['IdUsuario']) ){ ?>checked="checked"<? } ?> 
									<? if ($totalRows_rscomunicado > 0) { ?>disabled="disabled"<? } ?> 
                           			title="<? echo $row_destinatario_listar['praca']; ?>"> 
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
                              <? } ?>
                              
                            </table>
                            
                            <div class="KT_bottombuttons">
                                <div>
                                	<? if($row_usuario['controle_comunicado'] == 'Y' and $totalRows_rscomunicado == 0){ ?>
                                    <input type="hidden" name="MM_insert" value="form" />
                                    <input type="submit" id="submit" name="submit" value="Inserir" />
                                    <? } ?>
                                    <?
									if(
									($row_usuario['controle_comunicado'] == 'Y') and 
									($row_usuario['controle_memorando'] == 'Y') and 
									($row_rscomunicado['tipo'] == "m")
									){
									?>
                                    <a href="tabela.php?IdComunicado=<? echo $row_rscomunicado['IdComunicado']; ?>&acao=deletar" id="botao_deletar"><input type="button" value="Deletar" /></a>
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
mysql_free_result($rscomunicado); 
mysql_free_result($destinatario_listar);
?>
