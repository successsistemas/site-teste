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

// update
if ((isset($_POST["MM_update"])) && ($_POST["MM_update"] == "parametros_atual")) {

	// fim - converter entrada de 'valor_formulario_treinamento_com_manutencao' - fim
	if ( isset($_POST['valor_formulario_treinamento_com_manutencao']) ) {
		$_POST['valor_formulario_treinamento_com_manutencao'] = str_replace(',','.',$_POST['valor_formulario_treinamento_com_manutencao']);
	}
	// fim - converter entrada 'valor_formulario_treinamento_com_manutencao' - fim
	
	// fim - converter entrada de 'valor_formulario_treinamento_sem_manutencao' - fim
	if ( isset($_POST['valor_formulario_treinamento_sem_manutencao']) ) {
		$_POST['valor_formulario_treinamento_sem_manutencao'] = str_replace(',','.',$_POST['valor_formulario_treinamento_sem_manutencao']);
	}
	// fim - converter entrada 'valor_formulario_treinamento_sem_manutencao' - fim
	
	// fim - converter entrada de 'valor_formulario_extra_com_manutencao' - fim
	if ( isset($_POST['valor_formulario_extra_com_manutencao']) ) {
		$_POST['valor_formulario_extra_com_manutencao'] = str_replace(',','.',$_POST['valor_formulario_extra_com_manutencao']);
	}
	// fim - converter entrada 'valor_formulario_extra_com_manutencao' - fim
	
	// fim - converter entrada de 'valor_formulario_extra_sem_manutencao' - fim
	if ( isset($_POST['valor_formulario_extra_sem_manutencao']) ) {
		$_POST['valor_formulario_extra_sem_manutencao'] = str_replace(',','.',$_POST['valor_formulario_extra_sem_manutencao']);
	}
	// fim - converter entrada 'valor_formulario_extra_sem_manutencao' - fim

	$updateSQL = sprintf("
					   UPDATE parametros 
					   SET 
					   prazo_encerramento_solicitacao=%s,
					   encerramento_solicitacao_msg_dentro_prazo=%s,
					   encerramento_solicitacao_msg_fora_prazo=%s,
					   
					   solicitacao_nenhuma_acao_email=%s,
					   
					   prazo_encerramento_suporte=%s,
					   encerramento_suporte_msg_dentro_prazo=%s,
					   encerramento_suporte_msg_fora_prazo=%s,
					   
					   prazo_encaminhamento_suporte=%s,
					   encaminhamento_suporte_msg_dentro_prazo=%s,
					   encaminhamento_suporte_msg_fora_prazo=%s,
					   
					   suporte_auto_email_sem_movimento_dias=%s,
					   
					   prospeccao_auto_email_dias=%s,
					   prospeccao_auto_email_sem_agendamento_dias=%s,
					   prospeccao_auto_email_sem_movimento_dias=%s,

					   prospeccao_tempo_retroativo_data_contrato=%s,
					   
					   venda_auto_email_sem_movimento_dias=%s,
					   
					   ultimo_contrato=%s,
					   
					   implantacao_prazo=%s,
					   suporte_encerrar_mensagem_arquivos=%s,
					   
					   venda_formulario_treinamento_texto1=%s,
					   venda_formulario_treinamento_texto2=%s,
					   venda_formulario_treinamento_texto3=%s,
					   
					   venda_formulario_resumo_texto1=%s,
					   venda_formulario_resumo_texto2=%s,
					   venda_formulario_resumo_texto3=%s,

					   alteracao_previsao_qtde_operador_alta=%s,
					   alteracao_previsao_qtde_operador_media=%s,
					   alteracao_previsao_qtde_operador_baixa=%s,
					   alteracao_previsao_qtde_executante_alta=%s,
					   alteracao_previsao_qtde_executante_media=%s,
					   alteracao_previsao_qtde_executante_baixa=%s,
					   alteracao_previsao_qtde_testador_alta=%s,
					   alteracao_previsao_qtde_testador_media=%s,
					   alteracao_previsao_qtde_testador_baixa=%s,
					   alteracao_previsao_prazo_operador_alta=%s,
					   alteracao_previsao_prazo_operador_media=%s,
					   alteracao_previsao_prazo_operador_baixa=%s,
					   alteracao_previsao_prazo_executante_alta=%s,
					   alteracao_previsao_prazo_executante_media=%s,
					   alteracao_previsao_prazo_executante_baixa=%s,
					   alteracao_previsao_prazo_testador_alta=%s,
					   alteracao_previsao_prazo_testador_media=%s,
					   alteracao_previsao_prazo_testador_baixa=%s,
					   
					   valor_formulario_treinamento_com_manutencao=%s,
					   valor_formulario_treinamento_sem_manutencao=%s,
					   
					   valor_formulario_extra_com_manutencao=%s,
					   valor_formulario_extra_sem_manutencao=%s, 
					   
					   venda_validade_dias=%s,
					   venda_dilacao_prazo_quantidade=%s,
					   venda_dilacao_prazo_solicitar_dilacao=%s,
					   
					   venda_prazo_encerramento_mensagem=%s,
					   venda_encerramento_msg_dentro_prazo=%s,
					   venda_encerramento_msg_fora_prazo=%s, 
					   suporte_bonus_contas_receber_atraso_dias=%s, 
					   suporte_bonus_quantidade_titulos_anteriores_meses=%s, 
					   suporte_reclamacao_mensagem_inicial_dias=%s, 
					   suporte_reclamacao_encerramento_dias=%s, 
					   relatorio_inadimplencia_limite_atraso=%s, 
					   
					   prazo_excluir_comunicado=%s, 
					   
					   prospeccao_nivel_interesse_reagendamento_nenhum=%s, 
					   prospeccao_nivel_interesse_reagendamento_baixo=%s, 
					   prospeccao_nivel_interesse_reagendamento_medio=%s, 
					   prospeccao_nivel_interesse_reagendamento_alto=%s, 
					   
					   venda_prazo_envio_documentacao_dias=%s, 

					   suporte_cliente_atraso_vermelho=%s, 

					   solicitacao_alerta_correcao=%s,
					   solicitacao_alerta_correcao_dias=%s,

					   suporte_cliente_atraso_amarelo=%s, 
					   suporte_solicita_orientacoes=%s,
					   
					   aniversario=%s,
					   rodape_site=%s  
					   
					   WHERE IdParametro=%s",
                       GetSQLValueString($_POST['prazo_encerramento_solicitacao'], "int"),
					   GetSQLValueString($_POST['encerramento_solicitacao_msg_dentro_prazo'], "text"),
					   GetSQLValueString($_POST['encerramento_solicitacao_msg_fora_prazo'], "text"),
					   
					   GetSQLValueString($_POST['solicitacao_nenhuma_acao_email'], "text"),

                       GetSQLValueString($_POST['prazo_encerramento_suporte'], "int"),
					   GetSQLValueString($_POST['encerramento_suporte_msg_dentro_prazo'], "text"),
					   GetSQLValueString($_POST['encerramento_suporte_msg_fora_prazo'], "text"),
					   
                       GetSQLValueString($_POST['prazo_encaminhamento_suporte'], "int"),
					   GetSQLValueString($_POST['encaminhamento_suporte_msg_dentro_prazo'], "text"),
					   GetSQLValueString($_POST['encaminhamento_suporte_msg_fora_prazo'], "text"),
					   
					   GetSQLValueString($_POST['suporte_auto_email_sem_movimento_dias'], "int"),
					   
					   GetSQLValueString($_POST['prospeccao_auto_email_dias'], "int"),
					   GetSQLValueString($_POST['prospeccao_auto_email_sem_agendamento_dias'], "int"),
					   GetSQLValueString($_POST['prospeccao_auto_email_sem_movimento_dias'], "int"),

					   GetSQLValueString($_POST['prospeccao_tempo_retroativo_data_contrato'], "int"),
					   
					   GetSQLValueString($_POST['venda_auto_email_sem_movimento_dias'], "int"),
					   
					   GetSQLValueString($_POST['ultimo_contrato'], "text"),
					   
					   GetSQLValueString($_POST['implantacao_prazo'], "text"),
					   GetSQLValueString($_POST['suporte_encerrar_mensagem_arquivos'], "text"),
					   
					   GetSQLValueString($_POST['venda_formulario_treinamento_texto1'], "text"),
					   GetSQLValueString($_POST['venda_formulario_treinamento_texto2'], "text"),
					   GetSQLValueString($_POST['venda_formulario_treinamento_texto3'], "text"),
					   
					   GetSQLValueString($_POST['venda_formulario_resumo_texto1'], "text"),
					   GetSQLValueString($_POST['venda_formulario_resumo_texto2'], "text"),
					   GetSQLValueString($_POST['venda_formulario_resumo_texto3'], "text"),
					   
					   GetSQLValueString($_POST['alteracao_previsao_qtde_operador_alta'], "int"),
					   GetSQLValueString($_POST['alteracao_previsao_qtde_operador_media'], "int"),
					   GetSQLValueString($_POST['alteracao_previsao_qtde_operador_baixa'], "int"),
					   GetSQLValueString($_POST['alteracao_previsao_qtde_executante_alta'], "int"),
					   GetSQLValueString($_POST['alteracao_previsao_qtde_executante_media'], "int"),
					   GetSQLValueString($_POST['alteracao_previsao_qtde_executante_baixa'], "int"),
					   GetSQLValueString($_POST['alteracao_previsao_qtde_testador_alta'], "int"),
					   GetSQLValueString($_POST['alteracao_previsao_qtde_testador_media'], "int"),
					   GetSQLValueString($_POST['alteracao_previsao_qtde_testador_baixa'], "int"),
					   GetSQLValueString($_POST['alteracao_previsao_prazo_operador_alta'], "int"),
					   GetSQLValueString($_POST['alteracao_previsao_prazo_operador_media'], "int"),
					   GetSQLValueString($_POST['alteracao_previsao_prazo_operador_baixa'], "int"),
					   GetSQLValueString($_POST['alteracao_previsao_prazo_executante_alta'], "int"),
					   GetSQLValueString($_POST['alteracao_previsao_prazo_executante_media'], "int"),
					   GetSQLValueString($_POST['alteracao_previsao_prazo_executante_baixa'], "int"),
					   GetSQLValueString($_POST['alteracao_previsao_prazo_testador_alta'], "int"),
					   GetSQLValueString($_POST['alteracao_previsao_prazo_testador_media'], "int"),
					   GetSQLValueString($_POST['alteracao_previsao_prazo_testador_baixa'], "int"),
					   
					   GetSQLValueString($_POST['valor_formulario_treinamento_com_manutencao'], "text"),
					   GetSQLValueString($_POST['valor_formulario_treinamento_sem_manutencao'], "text"),
					   
					   GetSQLValueString($_POST['valor_formulario_extra_com_manutencao'], "text"),
					   GetSQLValueString($_POST['valor_formulario_extra_sem_manutencao'], "text"), 
					   
					   GetSQLValueString($_POST['venda_validade_dias'], "int"),
					   GetSQLValueString($_POST['venda_dilacao_prazo_quantidade'], "int"),
					   GetSQLValueString($_POST['venda_dilacao_prazo_solicitar_dilacao'], "int"),
					   
                       GetSQLValueString($_POST['venda_prazo_encerramento_mensagem'], "int"),
					   GetSQLValueString($_POST['venda_encerramento_msg_dentro_prazo'], "text"),
					   GetSQLValueString($_POST['venda_encerramento_msg_fora_prazo'], "text"),
					   
					   GetSQLValueString($_POST['suporte_bonus_contas_receber_atraso_dias'], "text"),
					   GetSQLValueString($_POST['suporte_bonus_quantidade_titulos_anteriores_meses'], "text"),
					   GetSQLValueString($_POST['suporte_reclamacao_mensagem_inicial_dias'], "text"),
					   GetSQLValueString($_POST['suporte_reclamacao_encerramento_dias'], "text"),
					   GetSQLValueString($_POST['relatorio_inadimplencia_limite_atraso'], "text"),
 
					   GetSQLValueString($_POST['prazo_excluir_comunicado'], "text"),
					   
					   GetSQLValueString($_POST['prospeccao_nivel_interesse_reagendamento_nenhum'], "text"),
					   GetSQLValueString($_POST['prospeccao_nivel_interesse_reagendamento_baixo'], "text"),
					   GetSQLValueString($_POST['prospeccao_nivel_interesse_reagendamento_medio'], "text"),
					   GetSQLValueString($_POST['prospeccao_nivel_interesse_reagendamento_alto'], "text"),
					   
					   GetSQLValueString($_POST['venda_prazo_envio_documentacao_dias'], "text"),

					   
					   GetSQLValueString($_POST['suporte_cliente_atraso_vermelho'], "text"),

					   GetSQLValueString($_POST['solicitacao_alerta_correcao'], "text"),
					   GetSQLValueString($_POST['solicitacao_alerta_correcao_dias'], "text"),

					   GetSQLValueString($_POST['suporte_cliente_atraso_amarelo'], "text"),
					   GetSQLValueString($_POST['suporte_solicita_orientacoes'], "text"),
			   
					   GetSQLValueString($_POST['aniversario'], "text"),
					   GetSQLValueString($_POST['rodape_site'], "text"),
					   
                       GetSQLValueString($_POST['IdParametro'], "int"));

	mysql_select_db($database_conexao, $conexao);
	$Result1 = mysql_query($updateSQL, $conexao) or die(mysql_error());

	// redireciona
	$updateGoTo = "../padrao/index.php";
	if (isset($_SERVER['QUERY_STRING'])) {
	$updateGoTo .= (strpos($updateGoTo, '?')) ? "&" : "?";
	$updateGoTo .= $_SERVER['QUERY_STRING'];
	}
	header(sprintf("Location: %s", $updateGoTo));
	// fim - redireciona
  
}
// fim - update

// parametros_atual
mysql_select_db($database_conexao, $conexao);
$query_parametros_atual = "SELECT * FROM parametros";
$parametros_atual = mysql_query($query_parametros_atual, $conexao) or die(mysql_error());
$row_parametros_atual = mysql_fetch_assoc($parametros_atual);
$totalRows_parametros_atual = mysql_num_rows($parametros_atual);
// fim - parametros_atual
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
<title>Área de Parceiro - Success Sistemas</title>
<link href="../../css/guia_painel.css" rel="stylesheet" type="text/css">
<script src="../../js/jquery.js"></script>

<script src="../../js/jquery.metadata.js" type="text/javascript"></script>
<script type="text/javascript" src="../../js/jquery.validate.js"></script>

<script src="../../ckeditor/ckeditor.js"></script>

<script type="text/javascript" src="../../js/jquery.maskedinput.js"></script> 
<script src="../../js/jquery.price_format.1.3.js" type="text/javascript"></script> 
<script type="text/javascript" src="../../js/jquery.alphanumeric.pack.js"></script> 


<script id="demo" type="text/javascript"> 
$.metadata.setType("attr", "validate");
$(document).ready(function() {
						   
	// validação
	$("#parametros_atual").validate({
		rules: {
			IdParametro:  "required",
			prazo_encerramento_solicitacao:  "required",
			prazo_encerramento_suporte:  "required",
			prazo_encaminhamento_suporte:  "required",
			
			suporte_auto_email_sem_movimento_dias: "required",
			
			prospeccao_auto_email_dias: "required",
			prospeccao_auto_email_sem_agendamento_dias: "required",
			prospeccao_auto_email_sem_movimento_dias: "required",
			
			prospeccao_tempo_retroativo_data_contrato: "required",
			
			venda_auto_email_sem_movimento_dias: "required",
			
			alteracao_previsao_qtde_operador_alta:  "required",
			alteracao_previsao_qtde_operador_media:  "required",
			alteracao_previsao_qtde_operador_baixa:  "required",
			alteracao_previsao_qtde_executante_alta:  "required",
			alteracao_previsao_qtde_executante_media:  "required",
			alteracao_previsao_qtde_executante_baixa:  "required",
			alteracao_previsao_qtde_testador_alta:  "required",
			alteracao_previsao_qtde_testador_media:  "required",
			alteracao_previsao_qtde_testador_baixa:  "required",
			alteracao_previsao_prazo_operador_alta:  "required",
			alteracao_previsao_prazo_operador_media:  "required",
			alteracao_previsao_prazo_operador_baixa:  "required",
			alteracao_previsao_prazo_executante_alta:  "required",
			alteracao_previsao_prazo_executante_media:  "required",
			alteracao_previsao_prazo_executante_baixa:  "required",
			alteracao_previsao_prazo_testador_alta:  "required",
			alteracao_previsao_prazo_testador_media:  "required",
			alteracao_previsao_prazo_testador_baixa:  "required",
			
			venda_validade_dias: "required",
			venda_dilacao_prazo_quantidade: "required",
			venda_dilacao_prazo_solicitar_dilacao: "required",
			
			venda_prazo_encerramento_mensagem:  "required"
			
		},
		messages: {
			IdParametro:  " Nenhum IdPametro definido",
			prazo_encerramento_solicitacao: " Informe um valor",
			prazo_encerramento_suporte: " Informe um valor",
			prazo_encaminhamento_suporte: " Informe um valor", 
			
			suporte_auto_email_sem_movimento_dias: " Informe um valor", 
			
			prospeccao_auto_email_dias: " Informe um valor", 
			prospeccao_auto_email_sem_agendamento_dias: " Informe um valor", 
			prospeccao_auto_email_sem_movimento_dias: " Informe um valor", 
			
			prospeccao_tempo_retroativo_data_contrato: " Informe um valor", 
			
			venda_auto_email_sem_movimento_dias: " Informe um valor", 
			
			alteracao_previsao_qtde_operador_alta:  " <br>Informe um valor",
			alteracao_previsao_qtde_operador_media:  " <br>Informe um valor",
			alteracao_previsao_qtde_operador_baixa:  " <br>Informe um valor",
			alteracao_previsao_qtde_executante_alta:  " <br>Informe um valor",
			alteracao_previsao_qtde_executante_media:  " <br>Informe um valor",
			alteracao_previsao_qtde_executante_baixa:  " <br>Informe um valor",
			alteracao_previsao_qtde_testador_alta:  " <br>Informe um valor",
			alteracao_previsao_qtde_testador_media:  " <br>Informe um valor",
			alteracao_previsao_qtde_testador_baixa:  " <br>Informe um valor",
			alteracao_previsao_prazo_operador_alta:  " <br>Informe um valor",
			alteracao_previsao_prazo_operador_media:  " <br>Informe um valor",
			alteracao_previsao_prazo_operador_baixa:  " <br>Informe um valor",
			alteracao_previsao_prazo_executante_alta:  " <br>Informe um valor",
			alteracao_previsao_prazo_executante_media:  " <br>Informe um valor",
			alteracao_previsao_prazo_executante_baixa:  " <br>Informe um valor",
			alteracao_previsao_prazo_testador_alta:  " <br>Informe um valor",
			alteracao_previsao_prazo_testador_media:  " <br>Informe um valor",
			alteracao_previsao_prazo_testador_baixa:  " <br>Informe um valor", 
			
			venda_validade_dias: " Informe um valor",
			venda_dilacao_prazo_quantidade: " Informe um valor",
			venda_dilacao_prazo_solicitar_dilacao: " Informe um valor",
			
			venda_prazo_encerramento_mensagem:  "required"
			
		},
		onkeyup: false
	});
	// fim - validação

	// mascara - inicio
	$('#valor_formulario_treinamento_com_manutencao').priceFormat({
		prefix: '',
		centsSeparator: ',',
		thousandsSeparator: ''
	});
	
	$('#valor_formulario_treinamento_sem_manutencao').priceFormat({
		prefix: '',
		centsSeparator: ',',
		thousandsSeparator: ''
	});
	
	$('#valor_formulario_extra_com_manutencao').priceFormat({
		prefix: '',
		centsSeparator: ',',
		thousandsSeparator: ''
	});
	
	$('#valor_formulario_extra_sem_manutencao').priceFormat({
		prefix: '',
		centsSeparator: ',',
		thousandsSeparator: ''
	});
	// fim - mascara

	// ckeditor
	CKEDITOR.replace( 'venda_formulario_treinamento_texto1', {
		height: '200'
	});
	// fim - ckeditor

	// ckeditor
	CKEDITOR.replace( 'venda_formulario_treinamento_texto2', {
		height: '200'
	});
	// fim - ckeditor

	// ckeditor
	CKEDITOR.replace( 'venda_formulario_treinamento_texto3', {
		height: '200'
	});
	// fim - ckeditor

	// ckeditor
	CKEDITOR.replace( 'venda_formulario_resumo_texto1', {
		height: '200'
	});
	// fim - ckeditor

	// ckeditor
	CKEDITOR.replace( 'venda_formulario_resumo_texto2', {
		height: '200'
	});
	// fim - ckeditor

	// ckeditor
	CKEDITOR.replace( 'venda_formulario_resumo_texto3', {
		height: '200'
	});
	// fim - ckeditor

	// ckeditor
	CKEDITOR.replace( 'suporte_solicita_orientacoes', {
		height: '200'
	});
	// fim - ckeditor

	// ckeditor
	CKEDITOR.replace( 'aniversario', {
		height: '200'
	});
	// fim - ckeditor

	// rodape_site
	CKEDITOR.replace( 'rodape_site', {
		height: '200'
	});
	// fim - rodape_site
	
	
});
</script>

<style>
/* erro de validação */
parametros_atual.cmxform label.error, label.error { color: red; }
div.error, label.error { display: none; }
/* fim - erro de validação */

.parametro{
	padding-bottom: 10px;
}
.parametro_complemento {
	font-size: 10px;
	font-style: italic;
	color: #999;
}
.tabela_campos{
	margin-top: 10px; 
	margin-bottom: 10px; 
	border: 1px solid #EEE;
}
.tabela_campos td{
	border: 1px solid #EEE;
	padding: 5px;
}
</style>

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
                        <td align="left">Parâmetros</td>
                        <td align="right"><a href="javascript:history.go(-1);"><img src="../../imagens/botao_voltar.jpg" border="0" /></a></td>
                    </tr>
                </table>
                </div>
                <div class="caminho"><a href="../index.php">Página inicial</a> &gt;&gt; Parâmetros</div>
                <!-- fim - titulo -->
                
                <div class="conteudo">
                
<form id="parametros_atual" name="parametros_atual" method="POST" action="<?php echo $editFormAction; ?>" class="cmxform">

<!-- prazo_encerramento_solicitacao -->
<div class="parametro">
    Prazo de encerramento das  solicitações em validação (dias): 
	<input name="prazo_encerramento_solicitacao" type="text" id="prazo_encerramento_solicitacao" value="<?php echo $row_parametros_atual['prazo_encerramento_solicitacao']; ?>" />
	<div class="parametro_complemento">0 (zero) desabilita a ferramenta</div>
    
    <br>
    Mensagem padrão dentro do prazo (2 dias):
    <br>
    <input name="encerramento_solicitacao_msg_dentro_prazo" type="text" id="encerramento_solicitacao_msg_dentro_prazo" style="width: 700px;" value="<?php echo $row_parametros_atual['encerramento_solicitacao_msg_dentro_prazo']; ?>" />
    <br>
    Mensagem padrão fora do prazo:
    <br>
    <input name="encerramento_solicitacao_msg_fora_prazo" type="text" id="encerramento_solicitacao_msg_fora_prazo" style="width: 700px;" value="<?php echo $row_parametros_atual['encerramento_solicitacao_msg_fora_prazo']; ?>" />
    
</div>
<!-- fim - prazo_encerramento_solicitacao -->

<div class="linha"></div>

<!-- solicitacao_nenhuma_acao_email -->
<div class="parametro">
    Mensagem padrão para solicitações sem movimentação a mais de 72 horas:
    <br>
    <input name="solicitacao_nenhuma_acao_email" type="text" id="solicitacao_nenhuma_acao_email" style="width: 700px;" value="<?php echo $row_parametros_atual['solicitacao_nenhuma_acao_email']; ?>" />
    
</div>
<!-- fim - solicitacao_nenhuma_acao_email -->

<div class="linha"></div>

<!-- prazo_encerramento_suporte -->
<div class="parametro">
    Prazo de encerramento dos  suportes em validação (dias): 
	<input name="prazo_encerramento_suporte" type="text" id="prazo_encerramento_suporte" value="<?php echo $row_parametros_atual['prazo_encerramento_suporte']; ?>" />
	<div class="parametro_complemento">0 (zero) desabilita a ferramenta</div>
    
    <br>
    Mensagem padrão dentro do prazo (2 dias):
    <br>
    <input name="encerramento_suporte_msg_dentro_prazo" type="text" id="encerramento_suporte_msg_dentro_prazo" style="width: 700px;" value="<?php echo $row_parametros_atual['encerramento_suporte_msg_dentro_prazo']; ?>" />
    <br>
    Mensagem padrão fora do prazo:
    <br>
    <input name="encerramento_suporte_msg_fora_prazo" type="text" id="encerramento_suporte_msg_fora_prazo" style="width: 700px;" value="<?php echo $row_parametros_atual['encerramento_suporte_msg_fora_prazo']; ?>" />
    
</div>
<!-- fim - prazo_encerramento_suporte -->

<div class="linha"></div>

<!-- prazo_encaminhamento_suporte -->
<div class="parametro">
    Prazo de encaminhamento dos  suportes para solicitação (dias): 
      <input name="prazo_encaminhamento_suporte" type="text" id="prazo_encaminhamento_suporte" value="<?php echo $row_parametros_atual['prazo_encaminhamento_suporte']; ?>" />
    <div class="parametro_complemento">0 (zero) desabilita a ferramenta</div>
    
    <br>
    Mensagem padrão dentro do prazo (2 dias):
    <br>
    <input name="encaminhamento_suporte_msg_dentro_prazo" type="text" id="encaminhamento_suporte_msg_dentro_prazo" style="width: 700px;" value="<?php echo $row_parametros_atual['encaminhamento_suporte_msg_dentro_prazo']; ?>" />
    <br>
    Mensagem padrão fora do prazo:
    <br>
    <input name="encaminhamento_suporte_msg_fora_prazo" type="text" id="encaminhamento_suporte_msg_fora_prazo" style="width: 700px;" value="<?php echo $row_parametros_atual['encaminhamento_suporte_msg_fora_prazo']; ?>" />
    
</div>
<!-- fim - prazo_encaminhamento_suporte -->

<div class="linha"></div>

<!-- suporte_bonus_contas_receber_atraso_dias -->
<div class="parametro">
    Prazo máximo para atraso de pagamento em visita bônus (dias):
	<input name="suporte_bonus_contas_receber_atraso_dias" type="text" id="suporte_bonus_contas_receber_atraso_dias" value="<?php echo $row_parametros_atual['suporte_bonus_contas_receber_atraso_dias']; ?>" />
	<div class="parametro_complemento">0 (zero) desabilita a ferramenta</div>
</div>
<!-- fim - suporte_bonus_contas_receber_atraso_dias -->

<br>

<!-- suporte_bonus_quantidade_titulos_anteriores_meses -->
<div class="parametro">
    Quantidade de títulos para consulta para liberação de visita bônus - Somente Manut. Trimestral (meses):
	<input name="suporte_bonus_quantidade_titulos_anteriores_meses" type="text" id="suporte_bonus_quantidade_titulos_anteriores_meses" value="<?php echo $row_parametros_atual['suporte_bonus_quantidade_titulos_anteriores_meses']; ?>" />
	<div class="parametro_complemento">0 (zero) desabilita a ferramenta</div>
</div>
<!-- fim - suporte_bonus_quantidade_titulos_anteriores_meses -->


<div class="linha"></div>

<!-- suporte_reclamacao_mensagem_inicial_dias -->
<div class="parametro">
    Prazo para alerta das reclamações encerradas (dias):
	<input name="suporte_reclamacao_mensagem_inicial_dias" type="text" id="suporte_reclamacao_mensagem_inicial_dias" value="<?php echo $row_parametros_atual['suporte_reclamacao_mensagem_inicial_dias']; ?>" />
	<div class="parametro_complemento">0 (zero) desabilita a ferramenta</div>
</div>
<!-- fim - suporte_reclamacao_mensagem_inicial_dias -->

<div class="linha"></div>

<!-- suporte_reclamacao_encerramento_dias -->
<div class="parametro">
    Prazo para validação de suporte (inloco sim/não) vinculado a reclamações encerradas (dias):
	<input name="suporte_reclamacao_encerramento_dias" type="text" id="suporte_reclamacao_encerramento_dias" value="<?php echo $row_parametros_atual['suporte_reclamacao_encerramento_dias']; ?>" />
	<div class="parametro_complemento">0 (zero) desabilita a ferramenta</div>
</div>
<!-- fim - suporte_reclamacao_encerramento_dias -->

<div class="linha"></div>

<!-- suporte_auto_email_sem_movimento_dias -->
<div class="parametro">
    Prazo para envio de e-mail em suportes sem movimento (dias):
	  <input name="suporte_auto_email_sem_movimento_dias" type="text" id="suporte_auto_email_sem_movimento_dias" value="<?php echo $row_parametros_atual['suporte_auto_email_sem_movimento_dias']; ?>" />
	<div class="parametro_complemento">0 (zero) desabilita a ferramenta</div>
</div>
<!-- fim - suporte_auto_email_sem_movimento_dias -->

<div class="linha"></div>

<!-- prospeccao_auto_email_dias -->
<div class="parametro">
    Prazo para envio de e-mail em prospecções com agendamento atrasado (dias):
	<input name="prospeccao_auto_email_dias" type="text" id="prospeccao_auto_email_dias" value="<?php echo $row_parametros_atual['prospeccao_auto_email_dias']; ?>" />
	<div class="parametro_complemento">0 (zero) desabilita a ferramenta</div>
</div>
<!-- fim - prospeccao_auto_email_dias -->

<div class="linha"></div>

<!-- prospeccao_auto_email_sem_agendamento_dias -->
<div class="parametro">
    Prazo para envio de e-mail em prospecções sem agendamento (dias):
	  <input name="prospeccao_auto_email_sem_agendamento_dias" type="text" id="prospeccao_auto_email_sem_agendamento_dias" value="<?php echo $row_parametros_atual['prospeccao_auto_email_sem_agendamento_dias']; ?>" />
	<div class="parametro_complemento">0 (zero) desabilita a ferramenta</div>
</div>
<!-- fim - prospeccao_auto_email_sem_agendamento_dias -->

<div class="linha"></div>

<!-- prospeccao_auto_email_sem_movimento_dias -->
<div class="parametro">
    Prazo para envio de e-mail em prospecções sem movimento (dias):
	  <input name="prospeccao_auto_email_sem_movimento_dias" type="text" id="prospeccao_auto_email_sem_movimento_dias" value="<?php echo $row_parametros_atual['prospeccao_auto_email_sem_movimento_dias']; ?>" />
	<div class="parametro_complemento">0 (zero) desabilita a ferramenta</div>
</div>
<!-- fim - prospeccao_auto_email_sem_movimento_dias -->

<div class="linha"></div>

<!-- prospeccao_tempo_retroativo_data_contrato -->
<div class="parametro">
    Tempo (retroativo) para "Data do Contrato" no encerramento da prospecção (dias):
	  <input name="prospeccao_tempo_retroativo_data_contrato" type="text" id="prospeccao_tempo_retroativo_data_contrato" value="<?php echo $row_parametros_atual['prospeccao_tempo_retroativo_data_contrato']; ?>" />
	<div class="parametro_complemento">0 (zero) desabilita a ferramenta</div>
</div>
<!-- fim - prospeccao_tempo_retroativo_data_contrato -->

<div class="linha"></div>

<!-- venda_auto_email_sem_movimento_dias -->
<div class="parametro">
    Prazo para envio de e-mail em vendas sem movimento (dias):
	  <input name="venda_auto_email_sem_movimento_dias" type="text" id="venda_auto_email_sem_movimento_dias" value="<?php echo $row_parametros_atual['venda_auto_email_sem_movimento_dias']; ?>" />
	<div class="parametro_complemento">0 (zero) desabilita a ferramenta</div>
</div>
<!-- fim - venda_auto_email_sem_movimento_dias -->

<div class="linha"></div>

<!-- alteracao_previsao_qtde_alta -->
<div class="parametro">
    Quantidade de "Alteração de previsão": 
    
<table border="0" cellspacing="0" cellpadding="0" align="center" class="tabela_campos">
<tr>
    <td>&nbsp;</td>
    <td align="center" style="font-weight: bold;">Alta</td>
    <td align="center" style="font-weight: bold;">Média</td>
    <td align="center" style="font-weight: bold;">Baixa</td>
</tr>

<tr>
    <td width="100" valign="middle">Operador</td>
    <td>
    <input name="alteracao_previsao_qtde_operador_alta" type="text" id="alteracao_previsao_qtde_operador_alta" value="<?php echo $row_parametros_atual['alteracao_previsao_qtde_operador_alta']; ?>" />
    </td>
    <td>
    <input name="alteracao_previsao_qtde_operador_media" type="text" id="alteracao_previsao_qtde_operador_media" value="<?php echo $row_parametros_atual['alteracao_previsao_qtde_operador_media']; ?>" />
    </td>
    <td>
    <input name="alteracao_previsao_qtde_operador_baixa" type="text" id="alteracao_previsao_qtde_operador_baixa" value="<?php echo $row_parametros_atual['alteracao_previsao_qtde_operador_baixa']; ?>" />
    </td>
</tr>

<tr>
    <td width="100" valign="middle">Executante</td>
    <td>
    <input name="alteracao_previsao_qtde_executante_alta" type="text" id="alteracao_previsao_qtde_executante_alta" value="<?php echo $row_parametros_atual['alteracao_previsao_qtde_executante_alta']; ?>" />
    </td>
    <td>
    <input name="alteracao_previsao_qtde_executante_media" type="text" id="alteracao_previsao_qtde_executante_media" value="<?php echo $row_parametros_atual['alteracao_previsao_qtde_executante_media']; ?>" />
    </td>
    <td>
    <input name="alteracao_previsao_qtde_executante_baixa" type="text" id="alteracao_previsao_qtde_executante_baixa" value="<?php echo $row_parametros_atual['alteracao_previsao_qtde_executante_baixa']; ?>" />
    </td>
</tr>
  
<tr>
    <td width="100" valign="middle">Testador</td>
    <td>
    <input name="alteracao_previsao_qtde_testador_alta" type="text" id="alteracao_previsao_qtde_testador_alta" value="<?php echo $row_parametros_atual['alteracao_previsao_qtde_testador_alta']; ?>" />
    </td>
    <td>
    <input name="alteracao_previsao_qtde_testador_media" type="text" id="alteracao_previsao_qtde_testador_media" value="<?php echo $row_parametros_atual['alteracao_previsao_qtde_testador_media']; ?>" />
    </td>
    <td>
    <input name="alteracao_previsao_qtde_testador_baixa" type="text" id="alteracao_previsao_qtde_testador_baixa" value="<?php echo $row_parametros_atual['alteracao_previsao_qtde_testador_baixa']; ?>" />
    </td>
</tr>
</table>

</div>
<!-- fim - alteracao_previsao_qtde_alta -->

<div class="linha"></div>

<!-- alteracao_previsao_prazo_alta -->
<div class="parametro">
    Tempo máximo para "Alterar Previsão" (dias): 
    
<table border="0" cellspacing="0" cellpadding="0" align="center" class="tabela_campos">
<tr>
    <td>&nbsp;</td>
    <td align="center" style="font-weight: bold;">Alta</td>
    <td align="center" style="font-weight: bold;">Média</td>
    <td align="center" style="font-weight: bold;">Baixa</td>
</tr>

<tr>
    <td width="100" valign="middle">Operador</td>
    <td>
    <input name="alteracao_previsao_prazo_operador_alta" type="text" id="alteracao_previsao_prazo_operador_alta" value="<?php echo $row_parametros_atual['alteracao_previsao_prazo_operador_alta']; ?>" />
    </td>
    <td>
    <input name="alteracao_previsao_prazo_operador_media" type="text" id="alteracao_previsao_prazo_operador_media" value="<?php echo $row_parametros_atual['alteracao_previsao_prazo_operador_media']; ?>" />
    </td>
    <td>
    <input name="alteracao_previsao_prazo_operador_baixa" type="text" id="alteracao_previsao_prazo_operador_baixa" value="<?php echo $row_parametros_atual['alteracao_previsao_prazo_operador_baixa']; ?>" />
    </td>
</tr>

<tr>
    <td width="100" valign="middle">Executante</td>
    <td>
    <input name="alteracao_previsao_prazo_executante_alta" type="text" id="alteracao_previsao_prazo_executante_alta" value="<?php echo $row_parametros_atual['alteracao_previsao_prazo_executante_alta']; ?>" />
    </td>
    <td>
    <input name="alteracao_previsao_prazo_executante_media" type="text" id="alteracao_previsao_prazo_executante_media" value="<?php echo $row_parametros_atual['alteracao_previsao_prazo_executante_media']; ?>" />
    </td>
    <td>
    <input name="alteracao_previsao_prazo_executante_baixa" type="text" id="alteracao_previsao_prazo_executante_baixa" value="<?php echo $row_parametros_atual['alteracao_previsao_prazo_executante_baixa']; ?>" />
    </td>
</tr>
  
<tr>
    <td width="100" valign="middle">Testador</td>
    <td>
    <input name="alteracao_previsao_prazo_testador_alta" type="text" id="alteracao_previsao_prazo_testador_alta" value="<?php echo $row_parametros_atual['alteracao_previsao_prazo_testador_alta']; ?>" />
    </td>
    <td>
    <input name="alteracao_previsao_prazo_testador_media" type="text" id="alteracao_previsao_prazo_testador_media" value="<?php echo $row_parametros_atual['alteracao_previsao_prazo_testador_media']; ?>" />
    </td>
    <td>
    <input name="alteracao_previsao_prazo_testador_baixa" type="text" id="alteracao_previsao_prazo_testador_baixa" value="<?php echo $row_parametros_atual['alteracao_previsao_prazo_testador_baixa']; ?>" />
    </td>
</tr>
</table>

</div>
<!-- fim - alteracao_previsao_prazo_alta -->


<div class="linha"></div>

<!-- valor_formulario_treinamento -->
<div class="parametro">
    Valor formulário de treinamento com manutenção: 
	R$ <input name="valor_formulario_treinamento_com_manutencao" type="text" id="valor_formulario_treinamento_com_manutencao" value="<?php echo $row_parametros_atual['valor_formulario_treinamento_com_manutencao']; ?>" />
    
    <br><br>
    
    Valor formulário de treinamento sem manutenção: 
	R$ <input name="valor_formulario_treinamento_sem_manutencao" type="text" id="valor_formulario_treinamento_sem_manutencao" value="<?php echo $row_parametros_atual['valor_formulario_treinamento_sem_manutencao']; ?>" />       

    
</div>
<!-- fim - valor_formulario_treinamento -->

<div class="linha"></div>

<!-- valor_formulario_extra -->
<div class="parametro">
    Valor formulário extra com manutenção: 
	R$ <input name="valor_formulario_extra_com_manutencao" type="text" id="valor_formulario_extra_com_manutencao" value="<?php echo $row_parametros_atual['valor_formulario_extra_com_manutencao']; ?>" />
    
    <br><br>
    
    Valor formulário extra sem manutenção: 
	R$ <input name="valor_formulario_extra_sem_manutencao" type="text" id="valor_formulario_extra_sem_manutencao" value="<?php echo $row_parametros_atual['valor_formulario_extra_sem_manutencao']; ?>" />       

    
</div>
<!-- fim - valor_formulario_extra -->

<div class="linha"></div>

<!-- ultimo_contrato -->
<div class="parametro">
    Último contrato gerado: 
    <input name="ultimo_contrato" type="text" id="ultimo_contrato" value="<?php echo $row_parametros_atual['ultimo_contrato']; ?>" />
</div>
<!-- fim - ultimo_contrato -->

<div class="linha"></div>

<!-- implantacao_prazo -->
<div class="parametro">
    Prazo para implantação (dias): 
    <input name="implantacao_prazo" type="text" id="implantacao_prazo" value="<?php echo $row_parametros_atual['implantacao_prazo']; ?>" />
</div>
<!-- fim - implantacao_prazo -->

<div class="linha"></div>

<!-- suporte_encerrar_mensagem_arquivos -->
<div class="parametro">
    Mensagem padrão ao encerrar um suporte sem arquivo anexo:
    <br>
    <input name="suporte_encerrar_mensagem_arquivos" type="text" id="suporte_encerrar_mensagem_arquivos" style="width: 700px;" value="<?php echo $row_parametros_atual['suporte_encerrar_mensagem_arquivos']; ?>" />
    
</div>
<!-- fim - suporte_encerrar_mensagem_arquivos -->

<div class="linha"></div>

<!-- venda_formulario_treinamento_texto1 -->
<div class="parametro">
    Venda - Formulário de treinamento (texto 1):
    <br>
    <textarea name="venda_formulario_treinamento_texto1" type="text" id="venda_formulario_treinamento_texto1" style="width: 700px; height: 200px;"><?php echo $row_parametros_atual['venda_formulario_treinamento_texto1']; ?></textarea>
</div>
<!-- fim - venda_formulario_treinamento_texto1 -->

<div class="linha"></div>

<!-- venda_formulario_treinamento_texto2 -->
<div class="parametro">
    Venda - Formulário de treinamento (texto 2):
    <br>
    <textarea name="venda_formulario_treinamento_texto2" type="text" id="venda_formulario_treinamento_texto2" style="width: 700px; height: 200px;"><?php echo $row_parametros_atual['venda_formulario_treinamento_texto2']; ?></textarea>
</div>
<!-- fim - venda_formulario_treinamento_texto2 -->

<div class="linha"></div>

<!-- venda_formulario_treinamento_texto2 -->
<div class="parametro">
    Venda - Formulário de treinamento (texto 3):
    <br>
    <textarea name="venda_formulario_treinamento_texto3" type="text" id="venda_formulario_treinamento_texto3" style="width: 700px; height: 200px;"><?php echo $row_parametros_atual['venda_formulario_treinamento_texto3']; ?></textarea>
</div>
<!-- fim - venda_formulario_treinamento_texto3 -->

<div class="linha"></div>

<!-- venda_formulario_resumo_texto1 -->
<div class="parametro">
    Venda - Formulário de resumo da implantação e treinamento (texto 1):
    <br>
    <textarea name="venda_formulario_resumo_texto1" type="text" id="venda_formulario_resumo_texto1" style="width: 700px; height: 200px;"><?php echo $row_parametros_atual['venda_formulario_resumo_texto1']; ?></textarea>
</div>
<!-- fim - venda_formulario_resumo_texto1 -->

<div class="linha"></div>

<!-- venda_formulario_resumo_texto2 -->
<div class="parametro">
    Venda - Formulário de resumo da implantação e treinamento (texto 2):
    <br>
    <textarea name="venda_formulario_resumo_texto2" type="text" id="venda_formulario_resumo_texto2" style="width: 700px; height: 200px;"><?php echo $row_parametros_atual['venda_formulario_resumo_texto2']; ?></textarea>
</div>
<!-- fim - venda_formulario_resumo_texto2 -->

<div class="linha"></div>

<!-- venda_formulario_resumo_texto3 -->
<div class="parametro">
    Venda - Formulário de resumo da implantação e treinamento (texto 3):
    <br>
    <textarea name="venda_formulario_resumo_texto3" type="text" id="venda_formulario_resumo_texto3" style="width: 700px; height: 200px;"><?php echo $row_parametros_atual['venda_formulario_resumo_texto3']; ?></textarea>
</div>
<!-- fim - venda_formulario_resumo_texto3 -->

<div class="linha"></div>

<!-- venda_validade_dias -->
<div class="parametro">
    VENDA - Prazo para validade dos serviços (dias):
	  <input name="venda_validade_dias" type="text" id="venda_validade_dias" value="<?php echo $row_parametros_atual['venda_validade_dias']; ?>" />
	<div class="parametro_complemento">0 (zero) desabilita a ferramenta</div>
</div>
<!-- fim - venda_validade_dias -->

<div class="linha"></div>

<!-- venda_dilacao_prazo_quantidade -->
<div class="parametro">
    VENDA - Quantidade de solicitação de dilação do prazo de validade:
	  <input name="venda_dilacao_prazo_quantidade" type="text" id="venda_dilacao_prazo_quantidade" value="<?php echo $row_parametros_atual['venda_dilacao_prazo_quantidade']; ?>" />
	<div class="parametro_complemento">0 (zero) desabilita a ferramenta</div>
</div>
<!-- fim - venda_dilacao_prazo_quantidade -->

<div class="linha"></div>

<!-- venda_dilacao_prazo_solicitar_dilacao -->
<div class="parametro">
	VENDA - Prazo para solicitar dilação da validade:
	<input name="venda_dilacao_prazo_solicitar_dilacao" type="text" id="venda_dilacao_prazo_solicitar_dilacao" value="<?php echo $row_parametros_atual['venda_dilacao_prazo_solicitar_dilacao']; ?>" />
	<div class="parametro_complemento">0 (zero) desabilita a ferramenta</div>
</div>
<!-- fim - venda_dilacao_prazo_solicitar_dilacao -->

<div class="linha"></div>

<!-- venda_prazo_encerramento_mensagem -->
<div class="parametro">
    Prazo de envio das mensagem de encerramento das  vendas a vencer (dias): 
	  <input name="venda_prazo_encerramento_mensagem" type="text" id="venda_prazo_encerramento_mensagem" value="<?php echo $row_parametros_atual['venda_prazo_encerramento_mensagem']; ?>" />
	<div class="parametro_complemento">0 (zero) desabilita a ferramenta</div>
    
    <br>
    Mensagem padrão dentro do prazo (2 dias):
    <br>
    <input name="venda_encerramento_msg_dentro_prazo" type="text" id="venda_encerramento_msg_dentro_prazo" style="width: 700px;" value="<?php echo $row_parametros_atual['venda_encerramento_msg_dentro_prazo']; ?>" />
    <br>
    Mensagem padrão fora do prazo:
    <br>
    <input name="venda_encerramento_msg_fora_prazo" type="text" id="venda_encerramento_msg_fora_prazo" style="width: 700px;" value="<?php echo $row_parametros_atual['venda_encerramento_msg_fora_prazo']; ?>" />
    
</div>
<!-- fim - venda_prazo_encerramento_mensagem -->


<!-- venda_prazo_envio_documentacao_dias -->
<div class="parametro">
	VENDA - Prazo para envio de Documentação (dias):
	<input name="venda_prazo_envio_documentacao_dias" type="text" id="venda_prazo_envio_documentacao_dias" value="<?php echo $row_parametros_atual['venda_prazo_envio_documentacao_dias']; ?>" />
</div>
<!-- fim - venda_prazo_envio_documentacao_dias -->

<div class="linha"></div>

<!-- suporte_cliente_atraso_amarelo -->
<div class="parametro">
	SUPORTE - Prazo para mudança de cor de clientes em débito (Amarelo - dias):
	<input name="suporte_cliente_atraso_amarelo" type="text" id="suporte_cliente_atraso_amarelo" value="<?php echo $row_parametros_atual['suporte_cliente_atraso_amarelo']; ?>" />
</div>
<!-- fim - suporte_cliente_atraso_amarelo -->

<!-- suporte_cliente_atraso_vermelho -->
<div class="parametro">
	SUPORTE - Prazo para mudança de cor de clientes em débito (Vermelho - dias):
	<input name="suporte_cliente_atraso_vermelho" type="text" id="suporte_cliente_atraso_vermelho" value="<?php echo $row_parametros_atual['suporte_cliente_atraso_vermelho']; ?>" />
</div>
<!-- fim - suporte_cliente_atraso_vermelho -->

<!-- suporte_solicita_orientacoes -->
<div class="parametro">
	SUPORTE - Orientações a ser verificadas (Anomalia simulada em testes):
	<textarea name="suporte_solicita_orientacoes" id="suporte_solicita_orientacoes" style="width: 700px; height: 200px;"><?php echo $row_parametros_atual['suporte_solicita_orientacoes']; ?></textarea>
</div>
<!-- fim - suporte_solicita_orientacoes -->

<!-- solicitacao_alerta_correcao -->
<div class="parametro">
	SUPORTE - Alerta no site para clientes com X solicitações de correção:
	<input name="solicitacao_alerta_correcao" type="text" id="solicitacao_alerta_correcao" value="<?php echo $row_parametros_atual['solicitacao_alerta_correcao']; ?>" />
</div>
<!-- fim - solicitacao_alerta_correcao -->

<!-- solicitacao_alerta_correcao_dias -->
<div class="parametro">
	SUPORTE -  Alerta no site para clientes com solicitações de correção em um prazo de X dias:

	<input name="solicitacao_alerta_correcao_dias" type="text" id="solicitacao_alerta_correcao_dias" value="<?php echo $row_parametros_atual['solicitacao_alerta_correcao_dias']; ?>" />
</div>
<!-- fim - solicitacao_alerta_correcao_dias -->

<!-- relatorio_inadimplencia_limite_atraso -->
<div class="parametro">
	RELATÓRIOS - Quantidade de dias limite para exibição de inadimplentes:
	<input name="relatorio_inadimplencia_limite_atraso" type="text" id="relatorio_inadimplencia_limite_atraso" value="<?php echo $row_parametros_atual['relatorio_inadimplencia_limite_atraso']; ?>" />
</div>
<!-- fim - relatorio_inadimplencia_limite_atraso -->

<div class="linha"></div>

<!-- aniversario -->
<div class="parametro">
    Mensagem de felicitação para aniversariantes:
    <br>
    <textarea name="aniversario" type="text" id="aniversario" style="width: 700px; height: 200px;"><?php echo $row_parametros_atual['aniversario']; ?></textarea>
</div>
<!-- fim - aniversario -->

<!-- rodape_site -->
<div class="parametro">
    Rodapé Site
    <br>
    <textarea name="rodape_site" type="text" id="rodape_site" style="width: 700px; height: 200px;"><?php echo $row_parametros_atual['rodape_site']; ?></textarea>
</div>
<!-- fim - rodape_site -->

<!-- prazo_excluir_comunicado -->
<div class="parametro">
    Prazo para exclusão de comunicados automaticamente (dias):
    <br>
    <input name="prazo_excluir_comunicado" type="text" id="prazo_excluir_comunicado" value="<?php echo $row_parametros_atual['prazo_excluir_comunicado']; ?>" />
</div>
<!-- fim - prazo_excluir_comunicado -->

<div class="linha"></div>

<!-- relatorio_inadimplencia_limite_atraso -->
<div class="parametro">
	PROSPECÇÃO - Tempo para reagendamento conforme o nível de interesse na proposta (nenhum):
	<input name="prospeccao_nivel_interesse_reagendamento_nenhum" type="text" id="prospeccao_nivel_interesse_reagendamento_nenhum" value="<?php echo $row_parametros_atual['prospeccao_nivel_interesse_reagendamento_nenhum']; ?>" />
	<br><br>
	PROSPECÇÃO - Tempo para reagendamento conforme o nível de interesse na proposta (baixo):
	<input name="prospeccao_nivel_interesse_reagendamento_baixo" type="text" id="prospeccao_nivel_interesse_reagendamento_baixo" value="<?php echo $row_parametros_atual['prospeccao_nivel_interesse_reagendamento_baixo']; ?>" />
	<br><br>
	PROSPECÇÃO - Tempo para reagendamento conforme o nível de interesse na proposta (medio):
	<input name="prospeccao_nivel_interesse_reagendamento_medio" type="text" id="prospeccao_nivel_interesse_reagendamento_medio" value="<?php echo $row_parametros_atual['prospeccao_nivel_interesse_reagendamento_medio']; ?>" />
	<br><br>
	PROSPECÇÃO - Tempo para reagendamento conforme o nível de interesse na proposta (alto):
	<input name="prospeccao_nivel_interesse_reagendamento_alto" type="text" id="prospeccao_nivel_interesse_reagendamento_alto" value="<?php echo $row_parametros_atual['prospeccao_nivel_interesse_reagendamento_alto']; ?>" />
</div>
<!-- fim - relatorio_inadimplencia_limite_atraso -->

<div class="linha"></div>

<br>
<input type="submit" name="Salvar" value="Salvar" />
<input name="IdParametro" type="hidden" id="IdParametro" value="1" />
<input type="hidden" name="MM_update" value="parametros_atual" />
</form>
                
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
mysql_free_result($parametros_atual);
?>