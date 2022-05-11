<?php require_once('restrito.php'); ?>
<?php require_once('Connections/conexao.php'); ?>
<?php
if (!function_exists("GetSQLValueString")) {
require_once('funcao_converte_caracter.php');

function GetSQLValueString($theValue, $theType, $theDefinedValue = "", $theNotDefinedValue = "") 
{
  $theValue = get_magic_quotes_gpc() ? stripslashes($theValue) : $theValue;

  $theValue = function_exists("mysql_real_escape_string") ? mysql_real_escape_string($theValue) : mysql_escape_string($theValue);

  switch ($theType) {
    case "text":
      //$theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
	  $theValue = ($theValue != "") ? "'" . funcao_converte_caracter($theValue) . "'" : "NULL";
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
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING'], ENT_QUOTES, 'UTF-8');
}

require_once('parametros.php');
require_once('prospeccao_funcao_update.php');
require_once('funcao_consulta_modulo_array.php');

// usuário logado via SESSION
$colname_usuario = "-1";
if (isset($_SESSION['MM_Username'])) {
  $colname_usuario = $_SESSION['MM_Username'];
}
mysql_select_db($database_conexao, $conexao);
$query_usuario = sprintf("SELECT * FROM usuarios WHERE usuario = %s", GetSQLValueString($colname_usuario, "text"));
$usuario = mysql_query($query_usuario, $conexao) or die(mysql_error());
$row_usuario = mysql_fetch_assoc($usuario);
$totalRows_usuario = mysql_num_rows($usuario);
// fim - usuário logado via SESSION

if($praca_status == 0){ header("Location: painel/index.php"); exit; } 

// prospeccao_editar (recordset) - seleciona o prospeccao atual
$colname_prospeccao_editar = "-1";
if (isset($_GET['id_prospeccao'])) {
  $colname_prospeccao_editar = $_GET['id_prospeccao'];
}
mysql_select_db($database_conexao, $conexao);
$query_prospeccao_editar = sprintf("
SELECT id, id_usuario_responsavel, status, praca, 
(SELECT nome FROM usuarios WHERE usuarios.IdUsuario = prospeccao.id_usuario_responsavel) as usuario_responsavel 
FROM prospeccao 
WHERE id = %s", GetSQLValueString($colname_prospeccao_editar, "int"));
$prospeccao_editar = mysql_query($query_prospeccao_editar, $conexao) or die(mysql_error());
$row_prospeccao_editar = mysql_fetch_assoc($prospeccao_editar);
$totalRows_prospeccao_editar = mysql_num_rows($prospeccao_editar);
// fim - prospeccao_ditar (recordset) - seleciona o prospeccao atual

// caso não tenho prospeccao, volta para listagem ********************************
if ($totalRows_prospeccao_editar < 1) { 
	$site_link_redireciona = "prospeccao.php?padrao=sim&".$prospeccao_padrao;
	echo sprintf("<meta http-equiv=\"refresh\" content=\"0; url=%s\">", $site_link_redireciona);
	exit;
}
// fim - caso não tenho prospeccao, volta para listagem **************************

// insert - LEU --------------------------------------------------
// se é usuario_responsavel
if($row_prospeccao_editar['id_usuario_responsavel']==$row_usuario['IdUsuario']) {
		$updateSQL_leu = sprintf("UPDATE prospeccao SET usuario_responsavel_leu=%s WHERE id=%s",
						   GetSQLValueString(date("Y-m-d H:i:s"), "date"),
						   GetSQLValueString($row_prospeccao_editar['id'], "int"));
		
		mysql_select_db($database_conexao, $conexao);
		$Result_leu = mysql_query($updateSQL_leu, $conexao) or die(mysql_error());
}
// fim - se é usuario_responsavel
// fim - insert - LEU  -------------------------------------------

mysql_free_result($prospeccao_editar);

// ----------------------------------------------------------------------------------------------------------------------------------------------------------------

// SELECT - prospeccao
$colname_prospeccao = "-1";
if (isset($_GET['id_prospeccao'])) {
  $colname_prospeccao = $_GET['id_prospeccao'];
}
mysql_select_db($database_conexao, $conexao);
$query_prospeccao = sprintf("
SELECT *, 
(SELECT nome FROM usuarios WHERE usuarios.IdUsuario = prospeccao.id_usuario_responsavel) as usuario_responsavel 
FROM prospeccao 
WHERE id = %s", GetSQLValueString($colname_prospeccao, "int"));
$prospeccao = mysql_query($query_prospeccao, $conexao) or die(mysql_error());
$row_prospeccao = mysql_fetch_assoc($prospeccao);
$totalRows_prospeccao = mysql_num_rows($prospeccao);
// fim - SELECT - prospeccao

// SELECT - prospeccao_concorrente
mysql_select_db($database_conexao, $conexao);
$query_prospeccao_concorrente = sprintf("
SELECT prospeccao_concorrente.* 
FROM prospeccao_concorrente 
WHERE prospeccao_concorrente.id = %s", GetSQLValueString($row_prospeccao['id_concorrente'], "int"));
$prospeccao_concorrente = mysql_query($query_prospeccao_concorrente, $conexao) or die(mysql_error());
$row_prospeccao_concorrente = mysql_fetch_assoc($prospeccao_concorrente);
$totalRows_prospeccao_concorrente = mysql_num_rows($prospeccao_concorrente);
// fim - SELECT - prospeccao_concorrente

// SELECT - prospeccao_contador
mysql_select_db($database_conexao, $conexao);
$query_prospeccao_contador = sprintf("
SELECT prospeccao_contador.* 
FROM prospeccao_contador 
WHERE prospeccao_contador.id = %s", GetSQLValueString($row_prospeccao['id_contador'], "int"));
$prospeccao_contador = mysql_query($query_prospeccao_contador, $conexao) or die(mysql_error());
$row_prospeccao_contador = mysql_fetch_assoc($prospeccao_contador);
$totalRows_prospeccao_contador = mysql_num_rows($prospeccao_contador);
// fim - SELECT - prospeccao_contador

// caso ainda tenha campos a informar (tela g/e), então cai para a página prospeccao_gerar.php
if($row_prospeccao['tela'] == "g"){
	// redireciona
	echo sprintf("<meta http-equiv=\"refresh\" content=\"0; url=%s\">", "prospeccao_gerar.php?id_prospeccao=".$row_prospeccao['id']); 
	// fim - redireciona
	exit;	
}
// fim - caso ainda tenha campos a informar (tela g/e), então cai para a página prospeccao_gerar.php

// descricao
$colname_descricao = "-1";
if (isset($_GET['id_prospeccao'])) {
  $colname_descricao = $_GET['id_prospeccao'];
}
mysql_select_db($database_conexao, $conexao);
$query_descricao = sprintf("
SELECT *, 
(SELECT nome FROM usuarios WHERE usuarios.IdUsuario = prospeccao_descricoes.id_usuario_responsavel) as usuario_responsavel 
FROM prospeccao_descricoes 
WHERE id_prospeccao = %s 
ORDER BY id DESC", GetSQLValueString($colname_descricao, "text"));
$descricao = mysql_query($query_descricao, $conexao) or die(mysql_error());
$row_descricao = mysql_fetch_assoc($descricao);
$totalRows_descricao = mysql_num_rows($descricao);
// fim - descricao

// agenda
mysql_select_db($database_conexao, $conexao);
$query_agenda = sprintf("
SELECT agenda.*, prospeccao_agenda_tipo.titulo AS prospeccao_agenda_tipo_titulo
FROM agenda 
LEFT JOIN prospeccao_agenda_tipo ON agenda.prospeccao_agenda_tipo = prospeccao_agenda_tipo.id 
WHERE id_prospeccao = %s 
ORDER BY data ASC", GetSQLValueString(@$_GET['id_prospeccao'], "text"));
$agenda = mysql_query($query_agenda, $conexao) or die(mysql_error());
$row_agenda = mysql_fetch_assoc($agenda);
$totalRows_agenda = mysql_num_rows($agenda);
// fim - agenda

// agenda_agendado
mysql_select_db($database_conexao, $conexao);
$query_agenda_agendado = sprintf("
SELECT id_agenda 
FROM agenda 
WHERE id_prospeccao = %s and status = 'a'
ORDER BY data ASC", GetSQLValueString(@$_GET['id_prospeccao'], "text"));
$agenda_agendado = mysql_query($query_agenda_agendado, $conexao) or die(mysql_error());
$row_agenda_agendado = mysql_fetch_assoc($agenda_agendado);
$totalRows_agenda_agendado = mysql_num_rows($agenda_agendado);
// fim - agenda_agendado

// arquivos em anexo
mysql_select_db($database_conexao, $conexao);
$query_arquivos_anexos = sprintf("SELECT id_arquivo FROM prospeccao_arquivos WHERE id_prospeccao = %s", GetSQLValueString($_GET['id_prospeccao'], "int"));
$arquivos_anexos = mysql_query($query_arquivos_anexos, $conexao) or die(mysql_error());
$row_arquivos_anexos = mysql_fetch_assoc($arquivos_anexos);
$totalRows_arquivos_anexos = mysql_num_rows($arquivos_anexos);
// fim - arquivos em anexo

// reclamacao_prospeccao
mysql_select_db($database_conexao, $conexao);
$query_reclamacao_prospeccao = sprintf("
SELECT id, data_suporte, situacao, titulo 
FROM suporte 
WHERE reclamacao_prospeccao = %s 
ORDER BY id ASC", GetSQLValueString($row_prospeccao['id'], "text"));
$reclamacao_prospeccao = mysql_query($query_reclamacao_prospeccao, $conexao) or die(mysql_error());
$row_reclamacao_prospeccao = mysql_fetch_assoc($reclamacao_prospeccao);
$totalRows_reclamacao_prospeccao = mysql_num_rows($reclamacao_prospeccao);
// fim - reclamacao_prospeccao

// reclamacao_consulta
mysql_select_db($database_conexao, $conexao);
$query_reclamacao_consulta = sprintf("
SELECT id, empresa, situacao, status_flag     
FROM suporte 
WHERE contrato = %s and tipo_suporte = 'r' and 
((status_flag = 'a') or (status_flag = 'f' and DATE_ADD(data_fim,INTERVAL ".$row_parametros['suporte_reclamacao_mensagem_inicial_dias']." DAY) >= now()))
", 
GetSQLValueString($row_prospeccao['contrato'], "text"));
$reclamacao_consulta = mysql_query($query_reclamacao_consulta, $conexao) or die(mysql_error());
$row_reclamacao_consulta = mysql_fetch_assoc($reclamacao_consulta);
$totalRows_reclamacao_consulta = mysql_num_rows($reclamacao_consulta);

if($totalRows_reclamacao_consulta > 0){

	$reclamacao_consulta_status = 0;
	$reclamacao_consulta_mensagem_aberta = NULL;
	$reclamacao_consulta_mensagem_fechada = NULL;
	do {

		if($row_reclamacao_consulta['status_flag'] == "f"){
			$reclamacao_consulta_mensagem_fechada .= 'Reclamação: '.$row_reclamacao_consulta['id'].' - Situação: '.$row_reclamacao_consulta['situacao'].'\n';
		} else {
			$reclamacao_consulta_status = 1;
			$reclamacao_consulta_mensagem_aberta .= 'Reclamação: '.$row_reclamacao_consulta['id'].' - Situação: '.$row_reclamacao_consulta['situacao'].'\n';
		}
		
	} while ($row_reclamacao_consulta = mysql_fetch_assoc($reclamacao_consulta));
	
	$reclamacao_consulta_mensagem_corpo = NULL;
	if($reclamacao_consulta_status == 0){
		$reclamacao_consulta_mensagem = 'ATENÇÃO:\nRECLAMAÇÃO REGISTRADA RECENTEMENTE\nCliente: '.utf8_encode($row_prospeccao['nome_razao_social']).'\n'.$reclamacao_consulta_mensagem_fechada;
		$reclamacao_consulta_mensagem_corpo = 'ATENÇÃO: CLIENTE COM RECLAMAÇÃO REGISTRADA RECENTEMENTE';
	} else if($reclamacao_consulta_status == 1){
		$reclamacao_consulta_mensagem = 'ATENÇÃO:\nRECLAMAÇÃO EM ANDAMENTO\nCliente: '.utf8_encode($row_prospeccao['nome_razao_social']).'\n'.$reclamacao_consulta_mensagem_aberta;
		$reclamacao_consulta_mensagem_corpo = 'ATENÇÃO: CLIENTE COM RECLAMAÇÃO EM ANDAMENTO';
	}
	
}
// fim - reclamacao_consulta

// prospeccao_contato (contador)
mysql_select_db($database_conexao, $conexao);
$query_prospeccao_contato = sprintf("
SELECT count(id) as retorno
FROM prospeccao_contato 
WHERE id_prospeccao = %s 
ORDER BY id ASC", GetSQLValueString($row_prospeccao['id'], "int"));
$prospeccao_contato = mysql_query($query_prospeccao_contato, $conexao) or die(mysql_error());
$row_prospeccao_contato = mysql_fetch_assoc($prospeccao_contato);
$totalRows_prospeccao_contato = mysql_num_rows($prospeccao_contato);
// fim - prospeccao_contato (contador)
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"> 
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">

<link rel="stylesheet" href="css/suporte.css" type="text/css" />
<link rel="stylesheet" href="css/suporte_imprimir.css" type="text/css" media="print" />

<script type="text/javascript" src="js/jquery.js"></script>

<link rel="stylesheet" href="css/thickbox.css" type="text/css" media="screen" />
<script type="text/javascript" src="js/thickbox.js"></script>

<script type="text/javascript" src="js/jquery.maskedinput-1.2.2.min.js"></script> 
<script type="text/javascript" src="js/jquery.rsv.js"></script> 

<script type="text/javascript"> 
function myOnComplete() { return true; }

$(document).ready(function() {
						   
	// validação
	$("#solicitacao").RSV({
	  onCompleteHandler: myOnComplete,
		rules: [
			"required,titulo,Informe o título.",
			"required,data_inicio,Informe a data de início.",
			"required,solicitante,Informe o solicitante.",
			"required,geral_tipo_modulo,Informe o módulo.",
			"required,prospeccao_tipo_atendimento,Informe o tipo de atendimento.",
			"required,anomalia,Informe a anomalia.",
			"required,orientacao,Informe a orientação.",
			"required,prospeccao_tipo_status,Informe o status."
		]
	});
	// fim - validação

	// mascara
	$('#data_inicio').mask('99-99-9999',{placeholder:" "});
	$('#data_fim').mask('99-99-9999',{placeholder:" "});
	// mascara - fim
	
});
</script>
<title>Prospecção n° <? echo $row_prospeccao['id']; ?></title>
</head>

<body>

<div class="div_solicitacao_linhas">
<table cellspacing="0" cellpadding="0" width="100%">
	<tr>
		<td style="text-align:left">
		Controle de prospecção n° <? echo $row_prospeccao['id']; ?> - <?php if($row_prospeccao['tipo_cliente']=="a"){echo "Cliente Antigo";} else if($row_prospeccao['tipo_cliente']=="n"){echo "Novo Cliente";} ?>
		</td>

		<td style="text-align: right">
		&lt;&lt; <a href="prospeccao.php?padrao=sim&<? echo $prospeccao_padrao; ?>">Voltar</a> | 
		Usuário logado: <? echo $row_usuario['nome']; ?> (<? echo $row_usuario['nivel_prospeccao']; ?>) |
		<a href="painel/padrao_sair.php">Sair</a>
        </td>
	</tr>
</table>
</div>

<div class="div_solicitacao_linhas2">
<table cellspacing="0" cellpadding="0" width="100%">
	<tr>
        <td style="text-align:left">
        <span class="label_solicitacao">Nome/Razão Social: </span>
        <?php echo utf8_encode($row_prospeccao['nome_razao_social']); ?>
        
        <!-- Alterar cliente -->
        <?php if($row_prospeccao['status_flag'] == "a"){ ?>
			<?php if($row_prospeccao['id_usuario_responsavel'] == $row_usuario['IdUsuario'] or (($row_usuario['nivel_prospeccao'] == 1 or $row_usuario['nivel_prospeccao'] == 2) and $row_usuario['praca'] == $row_prospeccao['praca'])){ ?>
            	<a href="prospeccao_editar_tabela.php?id_prospeccao=<? echo $row_prospeccao['id']; ?>&interacao=<? echo $row_prospeccao['interacao']; ?>&situacao=editar&acao=Alterar cliente&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $prospeccao_editar_tabela_height; ?>&width=<? echo $prospeccao_editar_tabela_width; ?>&modal=true" class="thickbox">
            	<img src="imagens/editar.png" id="botao_editar" align="absbottom" border="0" title="Alterar cliente">
                </a>
            <? } ?>
        <? } ?> 
        <!-- fim - Alterar cliente -->
        
        <br> 
        
        <span class="label_solicitacao">Nome Fantasia: </span>
        <?php echo utf8_encode($row_prospeccao['fantasia']); ?> 
        
        <!-- Alterar nome fantasia -->
        <?php if($row_prospeccao['status_flag'] == "a"){ ?>
			<?php if($row_prospeccao['id_usuario_responsavel'] == $row_usuario['IdUsuario'] or (($row_usuario['nivel_prospeccao'] == 1 or $row_usuario['nivel_prospeccao'] == 2) and $row_usuario['praca'] == $row_prospeccao['praca'])){ ?>
            	<a href="prospeccao_editar_tabela.php?id_prospeccao=<? echo $row_prospeccao['id']; ?>&interacao=<? echo $row_prospeccao['interacao']; ?>&situacao=editar&acao=Alterar nome fantasia&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $prospeccao_editar_tabela_height; ?>&width=<? echo $prospeccao_editar_tabela_width; ?>&modal=true" class="thickbox">
            	<img src="imagens/editar.png" id="botao_editar" align="absbottom" border="0" title="Alterar nome fantasia">
                </a>
            <? } ?>
        <? } ?> 
        <!-- fim - Alterar nome fantasia -->
        
        </td>
        
        <td style="text-align: right">
        <span class="label_solicitacao">Pessoa: </span>
        <?php if($row_prospeccao['pessoa']=="f"){ ?>Física<? } else { ?>Jurídica<? } ?>

         | 
		 
        <span class="label_solicitacao">CPF/CNPJ: </span>
        <?php echo $row_prospeccao['cpf_cnpj']; ?> 
        
        <!-- Alterar cpf/cnpj -->
        <?php if($row_prospeccao['status_flag'] == "a"){ ?>
			<?php if($row_prospeccao['id_usuario_responsavel'] == $row_usuario['IdUsuario'] or (($row_usuario['nivel_prospeccao'] == 1 or $row_usuario['nivel_prospeccao'] == 2) and $row_usuario['praca'] == $row_prospeccao['praca'])){ ?>
            	<a href="prospeccao_editar_tabela.php?id_prospeccao=<? echo $row_prospeccao['id']; ?>&interacao=<? echo $row_prospeccao['interacao']; ?>&situacao=editar&acao=Alterar cpf/cnpj&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $prospeccao_editar_tabela_height; ?>&width=<? echo $prospeccao_editar_tabela_width; ?>&modal=true" class="thickbox">
            	<img src="imagens/editar.png" id="botao_editar" align="absbottom" border="0" title="Alterar cpf/cnpj">
                </a>
            <? } ?>
        <? } ?> 
        <!-- fim - Alterar cpf/cnpj -->
        
        <br> 
        
        <span class="label_solicitacao">RG/Inscrição Estadual: </span>
        <?php echo $row_prospeccao['rg_inscricao']; ?>
        
        <!-- Alterar rg/inscrição estadual -->
        <?php if($row_prospeccao['status_flag'] == "a"){ ?>
			<?php if($row_prospeccao['id_usuario_responsavel'] == $row_usuario['IdUsuario'] or (($row_usuario['nivel_prospeccao'] == 1 or $row_usuario['nivel_prospeccao'] == 2) and $row_usuario['praca'] == $row_prospeccao['praca'])){ ?>
            	<a href="prospeccao_editar_tabela.php?id_prospeccao=<? echo $row_prospeccao['id']; ?>&interacao=<? echo $row_prospeccao['interacao']; ?>&situacao=editar&acao=Alterar rg/inscrição estadual&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $prospeccao_editar_tabela_height; ?>&width=<? echo $prospeccao_editar_tabela_width; ?>&modal=true" class="thickbox">
            	<img src="imagens/editar.png" id="botao_editar" align="absbottom" border="0" title="Alterar rg/inscrição estadual">
                </a>
            <? } ?>
        <? } ?> 
        <!-- fim - Alterar rg/inscrição estadual --> 
		
         | 
		 
        <span class="label_solicitacao">Orgão Expedidor: </span>
        <?php echo $row_prospeccao['rg_orgao_expeditor']; ?>
        </td>
	</tr>
</table>
</div>

<? if($totalRows_reclamacao_consulta > 0){ ?>
<div class="div_solicitacao_linhas4" style="color: red;">
<? echo $reclamacao_consulta_mensagem_corpo; ?>
</div>
<? } ?>

<div class="div_solicitacao_linhas3">
<table cellspacing="0" cellpadding="0" width="100%">
	<tr>
    
		<td style="text-align:left; vertical-align: top">
		<span class="label_solicitacao">Endereço: </span>
		<?php echo $row_prospeccao['endereco']; ?> <?php echo $row_prospeccao['endereco_numero']; ?> <?php echo $row_prospeccao['endereco_complemento']; ?>
        
        <!-- Alterar endereço -->
        <?php if($row_prospeccao['status_flag'] == "a"){ ?>
			<?php if($row_prospeccao['id_usuario_responsavel'] == $row_usuario['IdUsuario'] or (($row_usuario['nivel_prospeccao'] == 1 or $row_usuario['nivel_prospeccao'] == 2) and $row_usuario['praca'] == $row_prospeccao['praca'])){ ?>
            	<a href="prospeccao_editar_tabela.php?id_prospeccao=<? echo $row_prospeccao['id']; ?>&interacao=<? echo $row_prospeccao['interacao']; ?>&situacao=editar&acao=Alterar endereço&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $prospeccao_editar_tabela_height; ?>&width=<? echo $prospeccao_editar_tabela_width; ?>&modal=true" class="thickbox">
            	<img src="imagens/editar.png" id="botao_editar" align="absbottom" border="0" title="Alterar endereço">
                </a>
            <? } ?>
        <? } ?> 
        <!-- fim - Alterar endereço --> 
		
		
		 | 
		<span class="label_solicitacao">Ponto de Referência: </span>
		<?php echo $row_prospeccao['endereco_referencia']; ?>
		
        <br>
		<span class="label_solicitacao">Bairro: </span>
		<?php echo $row_prospeccao['bairro']; ?> | 

		<span class="label_solicitacao">CEP: </span>
		<?php echo $row_prospeccao['cep']; ?> | 
		
		<span class="label_solicitacao">Cidade/UF: </span>
		<?php echo $row_prospeccao['cidade']; ?>/<?php echo $row_prospeccao['uf']; ?>

		</td>
        
        <td style="text-align: right">
		<span class="label_solicitacao">Telefone: </span>
		<?php echo $row_prospeccao['telefone']; ?>
         
        <!-- Alterar telefone -->
        <?php if($row_prospeccao['status_flag'] == "a"){ ?>
			<?php if($row_prospeccao['id_usuario_responsavel'] == $row_usuario['IdUsuario'] or (($row_usuario['nivel_prospeccao'] == 1 or $row_usuario['nivel_prospeccao'] == 2) and $row_usuario['praca'] == $row_prospeccao['praca'])){ ?>
            	<a href="prospeccao_editar_tabela.php?id_prospeccao=<? echo $row_prospeccao['id']; ?>&interacao=<? echo $row_prospeccao['interacao']; ?>&situacao=editar&acao=Alterar telefone&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $prospeccao_editar_tabela_height; ?>&width=<? echo $prospeccao_editar_tabela_width; ?>&modal=true" class="thickbox">
            	<img src="imagens/editar.png" id="botao_editar" align="absbottom" border="0" title="Alterar telefone">
                </a>
            <? } ?>
        <? } ?> 
        <!-- fim - Alterar telefone -->
        
        | 
		
        <span class="label_solicitacao">Celular: </span>
		<?php echo $row_prospeccao['celular']; ?> 
        
        <!-- Alterar celular -->
        <?php if($row_prospeccao['status_flag'] == "a"){ ?>
			<?php if($row_prospeccao['id_usuario_responsavel'] == $row_usuario['IdUsuario'] or (($row_usuario['nivel_prospeccao'] == 1 or $row_usuario['nivel_prospeccao'] == 2) and $row_usuario['praca'] == $row_prospeccao['praca'])){ ?>
            	<a href="prospeccao_editar_tabela.php?id_prospeccao=<? echo $row_prospeccao['id']; ?>&interacao=<? echo $row_prospeccao['interacao']; ?>&situacao=editar&acao=Alterar celular&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $prospeccao_editar_tabela_height; ?>&width=<? echo $prospeccao_editar_tabela_width; ?>&modal=true" class="thickbox">
            	<img src="imagens/editar.png" id="botao_editar" align="absbottom" border="0" title="Alterar celular">
                </a>
            <? } ?>
        <? } ?> 
        <!-- fim - Alterar celular -->
        
        <br>  
		<span class="label_solicitacao">E-mail: </span>
        <? echo $row_prospeccao['email']; ?>
        </td>
        
	</tr>
</table>
</div>


<div class="div_solicitacao_linhas2">
<table cellspacing="0" cellpadding="0" width="100%">
	<tr>

		<td style="text-align:left">
		<span class="label_solicitacao">Responsável pela empresa: </span>
        <?php echo $row_prospeccao['empresa_responsavel']; ?>
		
		<br>
		
		<span class="label_solicitacao">Responsável por TI: </span>
        <?php echo $row_prospeccao['responsavel_por_ti']; ?>

        <!-- Alterar responsável por T.I. -->
        <?php if($row_prospeccao['status_flag'] == "a"){ ?>
			<?php if($row_prospeccao['id_usuario_responsavel'] == $row_usuario['IdUsuario'] or (($row_usuario['nivel_prospeccao'] == 1 or $row_usuario['nivel_prospeccao'] == 2) and $row_usuario['praca'] == $row_prospeccao['praca'])){ ?>
            	<a href="prospeccao_editar_tabela.php?id_prospeccao=<? echo $row_prospeccao['id']; ?>&interacao=<? echo $row_prospeccao['interacao']; ?>&situacao=editar&acao=Alterar responsável por T.I.&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $prospeccao_editar_tabela_height; ?>&width=<? echo $prospeccao_editar_tabela_width; ?>&modal=true" class="thickbox">
            	<img src="imagens/editar.png" id="botao_editar" align="absbottom" border="0" title="Alterar responsável por T.I.">
                </a>
            <? } ?>
        <? } ?> 
        <!-- fim - Alterar responsável por T.I. -->
        
        </td>
        
        <td style="text-align: right">
		<span class="label_solicitacao">Contato: </span>
        <?php echo $row_prospeccao['empresa_contato']; ?>
		
		<br>
		<span class="label_solicitacao">Aniversário do Contato: </span>
		<? if($row_prospeccao['empresa_contato_aniversario'] <> NULL){ echo date('d-m-Y', strtotime($row_prospeccao['empresa_contato_aniversario'])); } ?>
		</td>
        
	</tr>
</table>
</div>

<div class="div_solicitacao_linhas">
<table cellspacing="0" cellpadding="0" width="100%">
	<tr>
		<td style="text-align:left">
		Dados Comerciais:
		</td>
	</tr>
</table>
</div>

<div class="div_solicitacao_linhas2">
<table cellspacing="0" cellpadding="0" width="100%">
	<tr>
        <td style="text-align:left">
        <span class="label_solicitacao">Praça: </span>
        <?php echo $row_prospeccao['praca']; ?> 

		| 
		
        <span class="label_solicitacao">Representante Comercial: </span>
        <? echo $row_prospeccao['usuario_responsavel']; ?> 

		| 
		
        <span class="label_solicitacao">Ramo de atividade: </span>
        <?php echo $row_prospeccao['ramo_de_atividade']; ?> 
        <!-- Alterar Ramo de atividade -->
        <?php if($row_prospeccao['status_flag'] == "a"){ ?>
			<?php if($row_prospeccao['id_usuario_responsavel'] == $row_usuario['IdUsuario'] or (($row_usuario['nivel_prospeccao'] == 1 or $row_usuario['nivel_prospeccao'] == 2) and $row_usuario['praca'] == $row_prospeccao['praca'])){ ?>
            	<a href="prospeccao_editar_tabela.php?id_prospeccao=<? echo $row_prospeccao['id']; ?>&interacao=<? echo $row_prospeccao['interacao']; ?>&situacao=editar&acao=Alterar Ramo de atividade&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $prospeccao_editar_tabela_height; ?>&width=<? echo $prospeccao_editar_tabela_width; ?>&modal=true" class="thickbox">
            	<img src="imagens/editar.png" id="botao_editar" align="absbottom" border="0" title="Alterar Ramo de atividade">
                </a>
            <? } ?>
        <? } ?> 
        <!-- fim - Alterar Ramo de atividade -->
		
		<br>
		
		<span class="label_solicitacao">Tipo de prospect: </span>
        <?php if($row_prospeccao['ativo_passivo']=="a"){echo "ativo";} if($row_prospeccao['ativo_passivo']=="p"){echo "passivo";} ?>
        
        <!-- Alterar tipo de prospect -->
        <?php if($row_prospeccao['status_flag'] == "a"){ ?>
			<?php if($row_prospeccao['id_usuario_responsavel'] == $row_usuario['IdUsuario'] or (($row_usuario['nivel_prospeccao'] == 1 or $row_usuario['nivel_prospeccao'] == 2) and $row_usuario['praca'] == $row_prospeccao['praca'])){ ?>
            	<a href="prospeccao_editar_tabela.php?id_prospeccao=<? echo $row_prospeccao['id']; ?>&interacao=<? echo $row_prospeccao['interacao']; ?>&situacao=editar&acao=Alterar tipo de prospect&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $prospeccao_editar_tabela_height; ?>&width=<? echo $prospeccao_editar_tabela_width; ?>&modal=true" class="thickbox">
            	<img src="imagens/editar.png" id="botao_editar" align="absbottom" border="0" title="Alterar tipo de prospect">
                </a>
            <? } ?>
        <? } ?> 
        <!-- fim - Alterar tipo de prospect -->
        </td>
        
        <td style="text-align: right" width="350">  
		<span class="label_solicitacao">Indicador: </span>
        <?php 
		if($row_prospeccao['indicacao'] == "co"){echo "Contador";}
		else if($row_prospeccao['indicacao'] == "cl"){echo "Cliente";}
		else if($row_prospeccao['indicacao'] == "cs"){echo "Colaborador Success";}
		else if($row_prospeccao['indicacao'] == "fu"){echo "Funcionário";}
		else if($row_prospeccao['indicacao'] == "te"){echo "Terceiros";}
		?> / 

        <?php 
		if($row_prospeccao['indicador'] == "co"){echo $row_prospeccao['indicador_contador'];}
		else if($row_prospeccao['indicador'] == "cl"){echo $row_prospeccao['indicador_cliente'];}
		else if($row_prospeccao['indicador'] == "cs"){echo $row_prospeccao['indicador_usuario'];}
		else if($row_prospeccao['indicador'] == "fu"){echo $row_prospeccao['indicador_funcionario'];}
		else if($row_prospeccao['indicador'] == "te"){echo $row_prospeccao['indicador_terceiro'];}
		?>
		
		<br> 
		
		<span class="label_solicitacao">Nome do Indicador: </span>
        <?php echo $row_prospeccao['indicado_por']; ?>
        
        <!-- Alterar indicado por -->
        <?php if($row_prospeccao['status_flag'] == "a"){ ?>
			<?php if($row_prospeccao['id_usuario_responsavel'] == $row_usuario['IdUsuario'] or (($row_usuario['nivel_prospeccao'] == 1 or $row_usuario['nivel_prospeccao'] == 2) and $row_usuario['praca'] == $row_prospeccao['praca'])){ ?>
            	<a href="prospeccao_editar_tabela.php?id_prospeccao=<? echo $row_prospeccao['id']; ?>&interacao=<? echo $row_prospeccao['interacao']; ?>&situacao=editar&acao=Alterar indicado por&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $prospeccao_editar_tabela_height; ?>&width=<? echo $prospeccao_editar_tabela_width; ?>&modal=true" class="thickbox">
            	<img src="imagens/editar.png" id="botao_editar" align="absbottom" border="0" title="Alterar indicado por">
                </a>
            <? } ?>
        <? } ?> 
        <!-- fim - Alterar indicado por -->
        </td>
	</tr>
</table>
</div>

<div class="div_solicitacao_linhas3">
<table cellspacing="0" cellpadding="0" width="100%">
	<tr>
		<td style="text-align:left">
		<span class="label_solicitacao">Necessidades/Interesses do cliente: </span>
        <?php echo funcao_consulta_modulo_array($row_prospeccao['necessidades']); ?>
        <!-- Alterar Necessidades/Interesses do cliente -->
        <?php if($row_prospeccao['status_flag'] == "a"){ ?>
			<?php if($row_prospeccao['id_usuario_responsavel'] == $row_usuario['IdUsuario'] or (($row_usuario['nivel_prospeccao'] == 1 or $row_usuario['nivel_prospeccao'] == 2) and $row_usuario['praca'] == $row_prospeccao['praca'])){ ?>
            	<a href="prospeccao_editar_tabela.php?id_prospeccao=<? echo $row_prospeccao['id']; ?>&interacao=<? echo $row_prospeccao['interacao']; ?>&situacao=editar&acao=Alterar Necessidades/Interesses do cliente&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $prospeccao_editar_tabela_height; ?>&width=<? echo $prospeccao_editar_tabela_width; ?>&modal=true" class="thickbox">
            	<img src="imagens/editar.png" id="botao_editar" align="absbottom" border="0" title="Alterar Necessidades/Interesses do cliente">
                </a>
            <? } ?>
        <? } ?> 
        <!-- fim - Alterar Necessidades/Interesses do cliente -->
		</td>
	</tr>
</table>
</div>

<div class="div_solicitacao_linhas2">
<table cellspacing="0" cellpadding="0" width="100%">
	<tr>
		<td style="text-align:left">
		<span class="label_solicitacao">Empresa faz algum controle manual?: </span>
        <?php 
		if($row_prospeccao['empresa_controle_manual']=="s"){echo "Sim";} 
		else if($row_prospeccao['empresa_controle_manual']=="n"){echo "Não";}
		?> 
        <!-- Alterar Empresa faz algum controle manual? -->
        <?php if($row_prospeccao['status_flag'] == "a"){ ?>
			<?php if($row_prospeccao['id_usuario_responsavel'] == $row_usuario['IdUsuario'] or (($row_usuario['nivel_prospeccao'] == 1 or $row_usuario['nivel_prospeccao'] == 2) and $row_usuario['praca'] == $row_prospeccao['praca'])){ ?>
            	<a href="prospeccao_editar_tabela.php?id_prospeccao=<? echo $row_prospeccao['id']; ?>&interacao=<? echo $row_prospeccao['interacao']; ?>&situacao=editar&acao=Alterar Empresa faz algum controle manual?&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $prospeccao_editar_tabela_height; ?>&width=<? echo $prospeccao_editar_tabela_width; ?>&modal=true" class="thickbox">
            	<img src="imagens/editar.png" id="botao_editar" align="absbottom" border="0" title="Alterar Empresa faz algum controle manual?">
                </a>
            <? } ?>
        <? } ?> 
        <!-- fim - Alterar Empresa faz algum controle manual? -->
		
		<? if($row_prospeccao['empresa_controle_manual']=="s"){ ?>
		| 
		
		<span class="label_solicitacao">O que podemos ofertar para automatizar o processo manual: </span>
        <?php echo funcao_consulta_modulo_array($row_prospeccao['podemos_ofertar']); ?>
        <!-- Alterar O que podemos ofertar para automatizar o processo manual -->
        <?php if($row_prospeccao['status_flag'] == "a"){ ?>
			<?php if($row_prospeccao['id_usuario_responsavel'] == $row_usuario['IdUsuario'] or (($row_usuario['nivel_prospeccao'] == 1 or $row_usuario['nivel_prospeccao'] == 2) and $row_usuario['praca'] == $row_prospeccao['praca'])){ ?>
            	<a href="prospeccao_editar_tabela.php?id_prospeccao=<? echo $row_prospeccao['id']; ?>&interacao=<? echo $row_prospeccao['interacao']; ?>&situacao=editar&acao=Alterar O que podemos ofertar para automatizar o processo manual&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $prospeccao_editar_tabela_height; ?>&width=<? echo $prospeccao_editar_tabela_width; ?>&modal=true" class="thickbox">
            	<img src="imagens/editar.png" id="botao_editar" align="absbottom" border="0" title="Alterar O que podemos ofertar para automatizar o processo manual">
                </a>
            <? } ?>
        <? } ?> 
        <!-- fim - Alterar O que podemos ofertar para automatizar o processo manual -->
		</td>

		<td style="text-align:right">
		<span class="label_solicitacao">Nível de interesse: </span>
        <?php 
		if($row_prospeccao['nivel_interesse'] == "a"){echo "Alto";}
		else if($row_prospeccao['nivel_interesse'] == "m"){echo "Médio";}
		else if($row_prospeccao['nivel_interesse'] == "b"){echo "Baixo";}
		else if($row_prospeccao['nivel_interesse'] == "n"){echo "Nenhum";}
		?>
		
		<? } ?>
		</td>
	</tr>
</table>
</div>

<div class="div_solicitacao_linhas3">
<table cellspacing="0" cellpadding="0" width="100%">
	<tr>
		<td style="text-align:left">
		<span class="label_solicitacao">Valor da Proposta/Orçamento: </span>
        <?php echo $row_prospeccao['proposta_valor']; ?> 
		
		| 
		
		<span class="label_solicitacao">Recurso da Proposta/Orçamento: </span>
        <?php echo $row_prospeccao['proposta_recursos']; ?>
		</td>

		<td style="text-align:right">
		<span class="label_solicitacao">Validade da Proposta/Orçamento: </span>
        <?php echo $row_prospeccao['proposta_validade']; ?> 
		</td>
	</tr>
</table>
</div>

<? if($row_prospeccao['tipo_cliente']=="n") { ?>
<div class="div_solicitacao_linhas">
<table cellspacing="0" cellpadding="0" width="100%">
	<tr>
		<td style="text-align:left">
		Dados de Concorrência:
		</td>
	</tr>
</table>
</div>

<div class="div_solicitacao_linhas2">
<table cellspacing="0" cellpadding="0" width="100%">
	<tr>
		<td style="text-align:left">
		<span class="label_solicitacao">Possui sistema?: </span>
        <?php 
		if($row_prospeccao['sistema_possui']=="s"){echo "Sim";} 
		else if($row_prospeccao['sistema_possui']=="n"){echo "Não";}
		?> 
        <!-- Alterar Possui sistema -->
        <?php if($row_prospeccao['status_flag'] == "a"){ ?>
			<?php if($row_prospeccao['id_usuario_responsavel'] == $row_usuario['IdUsuario'] or (($row_usuario['nivel_prospeccao'] == 1 or $row_usuario['nivel_prospeccao'] == 2) and $row_usuario['praca'] == $row_prospeccao['praca'])){ ?>
            	<a href="prospeccao_editar_tabela.php?id_prospeccao=<? echo $row_prospeccao['id']; ?>&interacao=<? echo $row_prospeccao['interacao']; ?>&situacao=editar&acao=Alterar Possui sistema&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $prospeccao_editar_tabela_height; ?>&width=<? echo $prospeccao_editar_tabela_width; ?>&modal=true" class="thickbox">
            	<img src="imagens/editar.png" id="botao_editar" align="absbottom" border="0" title="Alterar Possui sistema">
                </a>
            <? } ?>
        <? } ?> 
        <!-- fim - Alterar Possui sistema -->
		</td>

		<td style="text-align:right">
		<span class="label_solicitacao">Nível de utilização: </span>		
        <?php 
		if($row_prospeccao['sistema_nivel_utilizacao'] == "a"){echo "Alto";}
		else if($row_prospeccao['sistema_nivel_utilizacao'] == "m"){echo "Médio";}
		else if($row_prospeccao['sistema_nivel_utilizacao'] == "b"){echo "Baixo";}
		else if($row_prospeccao['sistema_nivel_utilizacao'] == "n"){echo "Não implantado";}
		?>
        <!-- Alterar Nível de utilização -->
        <?php if($row_prospeccao['status_flag'] == "a"){ ?>
			<?php if($row_prospeccao['id_usuario_responsavel'] == $row_usuario['IdUsuario'] or (($row_usuario['nivel_prospeccao'] == 1 or $row_usuario['nivel_prospeccao'] == 2) and $row_usuario['praca'] == $row_prospeccao['praca'])){ ?>
            	<a href="prospeccao_editar_tabela.php?id_prospeccao=<? echo $row_prospeccao['id']; ?>&interacao=<? echo $row_prospeccao['interacao']; ?>&situacao=editar&acao=Alterar Nível de utilização&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $prospeccao_editar_tabela_height; ?>&width=<? echo $prospeccao_editar_tabela_width; ?>&modal=true" class="thickbox">
            	<img src="imagens/editar.png" id="botao_editar" align="absbottom" border="0" title="Alterar Nível de utilização">
                </a>
            <? } ?>
        <? } ?> 
        <!-- fim - Alterar Nível de utilização -->
		
		| 
		<span class="label_solicitacao">Nível de satisfação: </span>
        <?php 
		if($row_prospeccao['sistema_nivel_satisfacao'] == "a"){echo "Alto";}
		else if($row_prospeccao['sistema_nivel_satisfacao'] == "m"){echo "Médio";}
		else if($row_prospeccao['sistema_nivel_satisfacao'] == "b"){echo "Baixo";}
		else if($row_prospeccao['sistema_nivel_satisfacao'] == "i"){echo "Insatisfeito";}
		?>
        <!-- Alterar Nível de satisfação -->
        <?php if($row_prospeccao['status_flag'] == "a"){ ?>
			<?php if($row_prospeccao['id_usuario_responsavel'] == $row_usuario['IdUsuario'] or (($row_usuario['nivel_prospeccao'] == 1 or $row_usuario['nivel_prospeccao'] == 2) and $row_usuario['praca'] == $row_prospeccao['praca'])){ ?>
            	<a href="prospeccao_editar_tabela.php?id_prospeccao=<? echo $row_prospeccao['id']; ?>&interacao=<? echo $row_prospeccao['interacao']; ?>&situacao=editar&acao=Alterar Nível de satisfação&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $prospeccao_editar_tabela_height; ?>&width=<? echo $prospeccao_editar_tabela_width; ?>&modal=true" class="thickbox">
            	<img src="imagens/editar.png" id="botao_editar" align="absbottom" border="0" title="Alterar Nível de satisfação">
                </a>
            <? } ?>
        <? } ?> 
        <!-- fim - Alterar Nível de satisfação -->
		</td>
	</tr>
</table>
</div>
<? } ?>

<? if($row_prospeccao['tipo_cliente']=="n") { ?>
<div class="div_solicitacao_linhas3">
<table cellspacing="0" cellpadding="0" width="100%">
	<tr>
		<td style="text-align:left">
		<span class="label_solicitacao">Motivo da Satisfação/Insatisfação: </span>
        <?php echo $row_prospeccao['sistema_nivel_motivo']; ?> 
        <!-- Alterar Motivo da Satisfação/Insatisfação -->
        <?php if($row_prospeccao['status_flag'] == "a"){ ?>
			<?php if($row_prospeccao['id_usuario_responsavel'] == $row_usuario['IdUsuario'] or (($row_usuario['nivel_prospeccao'] == 1 or $row_usuario['nivel_prospeccao'] == 2) and $row_usuario['praca'] == $row_prospeccao['praca'])){ ?>
            	<a href="prospeccao_editar_tabela.php?id_prospeccao=<? echo $row_prospeccao['id']; ?>&interacao=<? echo $row_prospeccao['interacao']; ?>&situacao=editar&acao=Alterar Motivo da Satisfação/Insatisfação&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $prospeccao_editar_tabela_height; ?>&width=<? echo $prospeccao_editar_tabela_width; ?>&modal=true" class="thickbox">
            	<img src="imagens/editar.png" id="botao_editar" align="absbottom" border="0" title="Alterar Motivo da Satisfação/Insatisfação">
                </a>
            <? } ?>
        <? } ?> 
        <!-- fim - Alterar Motivo da Satisfação/Insatisfação -->
		</td>
	</tr>
</table>
</div>
<? } ?>

<? if($row_prospeccao['tipo_cliente']=="n") { ?>
<div class="div_solicitacao_linhas2">
<table cellspacing="0" cellpadding="0" width="100%">
	<tr>
		<td style="text-align:left">
		<span class="label_solicitacao">Quais recursos o cliente utiliza?: </span>
        <?php echo funcao_consulta_modulo_array($row_prospeccao['sistema_recursos']); ?> 
		
		<!-- Alterar Quais recursos o cliente utiliza? -->
        <?php if($row_prospeccao['status_flag'] == "a"){ ?>
			<?php if($row_prospeccao['id_usuario_responsavel'] == $row_usuario['IdUsuario'] or (($row_usuario['nivel_prospeccao'] == 1 or $row_usuario['nivel_prospeccao'] == 2) and $row_usuario['praca'] == $row_prospeccao['praca'])){ ?>
            	<a href="prospeccao_editar_tabela.php?id_prospeccao=<? echo $row_prospeccao['id']; ?>&interacao=<? echo $row_prospeccao['interacao']; ?>&situacao=editar&acao=Alterar Quais recursos o cliente utiliza?&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $prospeccao_editar_tabela_height; ?>&width=<? echo $prospeccao_editar_tabela_width; ?>&modal=true" class="thickbox">
            	<img src="imagens/editar.png" id="botao_editar" align="absbottom" border="0" title="Alterar Quais recursos o cliente utiliza?">
                </a>
            <? } ?>
        <? } ?> 
        <!-- fim - Alterar Quais recursos o cliente utiliza? -->
		</td>
	</tr>
</table>
</div>
<? } ?>

<? if($row_prospeccao['tipo_cliente']=="n") { ?>
<div class="div_solicitacao_linhas3">
<table cellspacing="0" cellpadding="0" width="100%">
	<tr>
		<td style="text-align:left">
		<span class="label_solicitacao">O Success tem os recursos que o cliente utiliza?: </span>
        <?php 
		if($row_prospeccao['sistema_recursos_success_possui']=="t"){echo "Totalmente";} 
		else if($row_prospeccao['sistema_recursos_success_possui']=="p"){echo "Parcialmente";}
		?> 
		
		<!-- Alterar O Success tem os recursos que o cliente utiliza? -->
        <?php if($row_prospeccao['status_flag'] == "a"){ ?>
			<?php if($row_prospeccao['id_usuario_responsavel'] == $row_usuario['IdUsuario'] or (($row_usuario['nivel_prospeccao'] == 1 or $row_usuario['nivel_prospeccao'] == 2) and $row_usuario['praca'] == $row_prospeccao['praca'])){ ?>
            	<a href="prospeccao_editar_tabela.php?id_prospeccao=<? echo $row_prospeccao['id']; ?>&interacao=<? echo $row_prospeccao['interacao']; ?>&situacao=editar&acao=Alterar O Success tem os recursos que o cliente utiliza?&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $prospeccao_editar_tabela_height; ?>&width=<? echo $prospeccao_editar_tabela_width; ?>&modal=true" class="thickbox">
            	<img src="imagens/editar.png" id="botao_editar" align="absbottom" border="0" title="Alterar O Success tem os recursos que o cliente utiliza?">
                </a>
            <? } ?>
        <? } ?> 
        <!-- fim - Alterar O Success tem os recursos que o cliente utiliza? -->
		</td>
		
		<td style="text-align:right">
		<span class="label_solicitacao">Recursos que o cliente utiliza e o Success não tem: </span>
        <?php echo $row_prospeccao['sistema_recursos_success_nao_possui']; ?> 
		
		<!-- Alterar Recursos que o cliente utiliza e o Success não tem -->
        <?php if($row_prospeccao['status_flag'] == "a"){ ?>
			<?php if($row_prospeccao['id_usuario_responsavel'] == $row_usuario['IdUsuario'] or (($row_usuario['nivel_prospeccao'] == 1 or $row_usuario['nivel_prospeccao'] == 2) and $row_usuario['praca'] == $row_prospeccao['praca'])){ ?>
            	<a href="prospeccao_editar_tabela.php?id_prospeccao=<? echo $row_prospeccao['id']; ?>&interacao=<? echo $row_prospeccao['interacao']; ?>&situacao=editar&acao=Alterar Recursos que o cliente utiliza e o Success não tem&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $prospeccao_editar_tabela_height; ?>&width=<? echo $prospeccao_editar_tabela_width; ?>&modal=true" class="thickbox">
            	<img src="imagens/editar.png" id="botao_editar" align="absbottom" border="0" title="Alterar Recursos que o cliente utiliza e o Success não tem">
                </a>
            <? } ?>
        <? } ?> 
        <!-- fim - Alterar Recursos que o cliente utiliza e o Success não tem -->
		</td>
	</tr>
</table>
</div>
<? } ?>

<? if($row_prospeccao['tipo_cliente']=="n") { ?>
<div class="div_solicitacao_linhas">
<table cellspacing="0" cellpadding="0" width="100%">
	<tr>
		<td style="text-align:left">
		Dados do Software:
		</td>
	</tr>
</table>
</div>

<div class="div_solicitacao_linhas2">
<table cellspacing="0" cellpadding="0" width="100%">
	<tr>
		<td style="text-align:left">
		<span class="label_solicitacao">Concorrente: </span>
        <? echo $row_prospeccao_concorrente['nome']; ?> 
		
		| 
		
		<span class="label_solicitacao">Empresa Representante: </span>		
        <? echo $row_prospeccao_concorrente['empresa']; ?> 
		
        <!-- Alterar concorrente -->
        <?php if($row_prospeccao['status_flag'] == "a"){ ?>
			<?php if($row_prospeccao['id_usuario_responsavel'] == $row_usuario['IdUsuario'] or (($row_usuario['nivel_prospeccao'] == 1 or $row_usuario['nivel_prospeccao'] == 2) and $row_usuario['praca'] == $row_prospeccao['praca'])){ ?>
            	<a href="prospeccao_editar_tabela.php?id_prospeccao=<? echo $row_prospeccao['id']; ?>&interacao=<? echo $row_prospeccao['interacao']; ?>&situacao=editar&acao=Alterar concorrente&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $prospeccao_editar_tabela_height; ?>&width=<? echo $prospeccao_editar_tabela_width; ?>&modal=true" class="thickbox">
            	<img src="imagens/editar.png" id="botao_editar" align="absbottom" border="0" title="Alterar concorrente">
                </a>
            <? } ?>
        <? } ?> 
        <!-- fim - Alterar concorrente -->
		</td>

		<td style="text-align:right">
		<span class="label_solicitacao">Cidade de origem: </span>		
        <? echo $row_prospeccao_concorrente['cidade_origem']; ?>
		
		| 
		<span class="label_solicitacao">Site: </span>
        <? echo $row_prospeccao_concorrente['site']; ?>
		</td>
	</tr>
</table>
</div>

<div class="div_solicitacao_linhas3">
<table cellspacing="0" cellpadding="0" width="100%">
	<tr>
		<td style="text-align:left">
		<span class="label_solicitacao">Banco de dados: </span>
        <? echo $row_prospeccao_concorrente['banco_de_dados']; ?> 
		
		| 
		
		<span class="label_solicitacao">Possui migração: </span>		
        <? 
		if($row_prospeccao_concorrente['migracao']=="s"){
			echo "Sim";
		} else if($row_prospeccao_concorrente['migracao']=="n"){
			echo "Não";
		}
		?>
		
		| <span class="label_solicitacao">Tipo de migração: </span>		
        <? 
		if($row_prospeccao_concorrente['migracao_tipo']=="c"){
			echo "Completa";
		} else if($row_prospeccao_concorrente['migracao_tipo']=="p"){
			echo "Parcial";
		} else if($row_prospeccao_concorrente['migracao_tipo']=="b"){
			echo "Cadastros Básicos";
		}
		?>

		</td>

		<td style="text-align:right">
		<span class="label_solicitacao">Recursos comercializados ou existentes no software concorrente: </span>
        <? echo $row_prospeccao_concorrente['recursos']; ?>
		</td>
	</tr>
</table>
</div>
<? } ?>

<div class="div_solicitacao_linhas">
<table cellspacing="0" cellpadding="0" width="100%">
	<tr>
		<td style="text-align:left">
		Dados Contábeis/Fiscais:
		</td>
	</tr>
</table>
</div>

<div class="div_solicitacao_linhas2">
<table cellspacing="0" cellpadding="0" width="100%">
	<tr>

		<td style="text-align:left">
		<span class="label_solicitacao">Contador: </span>
        <?php echo $row_prospeccao_contador['razao']; ?>

        <!-- Alterar contador -->
        <?php if($row_prospeccao['status_flag'] == "a"){ ?>
			<?php if($row_prospeccao['id_usuario_responsavel'] == $row_usuario['IdUsuario'] or (($row_usuario['nivel_prospeccao'] == 1 or $row_usuario['nivel_prospeccao'] == 2) and $row_usuario['praca'] == $row_prospeccao['praca'])){ ?>
            	<a href="prospeccao_editar_tabela.php?id_prospeccao=<? echo $row_prospeccao['id']; ?>&interacao=<? echo $row_prospeccao['interacao']; ?>&situacao=editar&acao=Alterar contador&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $prospeccao_editar_tabela_height; ?>&width=<? echo $prospeccao_editar_tabela_width; ?>&modal=true" class="thickbox">
            	<img src="imagens/editar.png" id="botao_editar" align="absbottom" border="0" title="Alterar contador">
                </a>
            <? } ?>
        <? } ?> 
        <!-- fim - Alterar contador -->
		<br>
        
		<span class="label_solicitacao">Telefone (cont.): </span>
        <?php echo $row_prospeccao_contador['telefone']; ?> |
        
		<span class="label_solicitacao">E-mail (cont.): </span>
        <?php echo $row_prospeccao_contador['email']; ?>
        </td>
        
        <td style="text-align: right">
        <span class="label_solicitacao">Enquadramento Fiscal: </span>
        <?php if($row_prospeccao['enquadramento_fiscal']==""){ ?>   
	        <?php echo $row_prospeccao['enquadramento_fiscal_outro']; ?>
        <? } else { ?>
            <?php echo $row_prospeccao['enquadramento_fiscal']; ?>
		<? } ?>
        
        <!-- Alterar enquadramento fiscal -->
        <?php if($row_prospeccao['status_flag'] == "a"){ ?>
			<?php if($row_prospeccao['id_usuario_responsavel'] == $row_usuario['IdUsuario'] or (($row_usuario['nivel_prospeccao'] == 1 or $row_usuario['nivel_prospeccao'] == 2) and $row_usuario['praca'] == $row_prospeccao['praca'])){ ?>
            	<a href="prospeccao_editar_tabela.php?id_prospeccao=<? echo $row_prospeccao['id']; ?>&interacao=<? echo $row_prospeccao['interacao']; ?>&situacao=editar&acao=Alterar enquadramento fiscal&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $prospeccao_editar_tabela_height; ?>&width=<? echo $prospeccao_editar_tabela_width; ?>&modal=true" class="thickbox">
            	<img src="imagens/editar.png" id="botao_editar" align="absbottom" border="0" title="Alterar enquadramento fiscal">
                </a>
            <? } ?>
        <? } ?> 
        <!-- fim - Alterar enquadramento fiscal -->

		<br>

		<span class="label_solicitacao">Obrigações fiscais exigidas: </span>
        Cupom Fiscal: 
		<?php 
		if($row_prospeccao['exige_cupom_fiscal']=="0"){echo "não";} 
		if($row_prospeccao['exige_cupom_fiscal']=="1"){echo "sim";} 
		?> | 
        NFe: 
		<?php 
		if($row_prospeccao['exige_nfe']=="0"){echo "não";} 
		if($row_prospeccao['exige_nfe']=="1"){echo "sim";} 
		?> | 
        NFCe: 
		<?php 
		if($row_prospeccao['exige_nfce']=="0"){echo "não";} 
		if($row_prospeccao['exige_nfce']=="1"){echo "sim";} 
		?> | 
        MDFe: 
		<?php 
		if($row_prospeccao['exige_mdfe']=="0"){echo "não";} 
		if($row_prospeccao['exige_mdfe']=="1"){echo "sim";} 
		?> | 
        CTE-e: 
		<?php 
		if($row_prospeccao['exige_ctee']=="0"){echo "não";} 
		if($row_prospeccao['exige_ctee']=="1"){echo "sim";} 
		?> | 
        EFD: 
		<?php 
		if($row_prospeccao['exige_efd']=="0"){echo "não";} 
		if($row_prospeccao['exige_efd']=="1"){echo "sim";} 
		?> | 
        Outras: <?php echo $row_prospeccao['exige_outro']; ?>
        
        <!-- Alterar informações fiscais -->
        <?php if($row_prospeccao['status_flag'] == "a"){ ?>
			<?php if($row_prospeccao['id_usuario_responsavel'] == $row_usuario['IdUsuario'] or (($row_usuario['nivel_prospeccao'] == 1 or $row_usuario['nivel_prospeccao'] == 2) and $row_usuario['praca'] == $row_prospeccao['praca'])){ ?>
            	<a href="prospeccao_editar_tabela.php?id_prospeccao=<? echo $row_prospeccao['id']; ?>&interacao=<? echo $row_prospeccao['interacao']; ?>&situacao=editar&acao=Alterar informações fiscais&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $prospeccao_editar_tabela_height; ?>&width=<? echo $prospeccao_editar_tabela_width; ?>&modal=true" class="thickbox">
            	<img src="imagens/editar.png" id="botao_editar" align="absbottom" border="0" title="Alterar informações fiscais">
                </a>
            <? } ?>
        <? } ?> 
        <!-- fim - Alterar informações fiscais -->

		</td>
        
	</tr>
</table>
</div>

<div class="div_solicitacao_linhas">
<table cellspacing="0" cellpadding="0" width="100%">
	<tr>
		<td style="text-align:left">
		Dados da Prospecção:
		</td>
	</tr>
</table>
</div>

<div class="div_solicitacao_linhas2">
<table cellspacing="0" cellpadding="0" width="100%">
	<tr>

		<td style="text-align:left">
		<span class="label_solicitacao">Data/hora criação: </span>
        <? echo date('d-m-Y  H:i:s', strtotime($row_prospeccao['data_prospeccao'])); ?>
		
		<br>
		
		<span class="label_solicitacao">Última Visita: </span>- 
		
		| 
		
		<span class="label_solicitacao">Última Mala Direta: </span>-
        </td>
        
        <td style="text-align: right">
        <span class="label_solicitacao">Situação: </span><?php echo $row_prospeccao['situacao']; ?>
		
		<?php if($row_prospeccao['status']!=""){ ?>
        | <span class="label_solicitacao">Status: </span><?php echo $row_prospeccao['status']; ?>
		<? } ?>
        
        <!-- Alterar status -->
        <?php if($row_prospeccao['status_flag'] == "a" and $row_prospeccao['quantidade_agendado'] > 0){ ?>
			<?php if($row_prospeccao['id_usuario_responsavel'] == $row_usuario['IdUsuario'] or (($row_usuario['nivel_prospeccao'] == 1 or $row_usuario['nivel_prospeccao'] == 2) and $row_usuario['praca'] == $row_prospeccao['praca'])){ ?>
            	<a href="prospeccao_editar_tabela.php?id_prospeccao=<? echo $row_prospeccao['id']; ?>&interacao=<? echo $row_prospeccao['interacao']; ?>&situacao=editar&acao=Alterar status&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $prospeccao_editar_tabela_height; ?>&width=<? echo $prospeccao_editar_tabela_width; ?>&modal=true" class="thickbox">
            	<img src="imagens/editar.png" id="botao_editar" align="absbottom" border="0" title="Alterar status">
                </a>
            <? } ?>
        <? } ?> 
        <!-- fim - Alterar status -->
        
        <?php if($row_prospeccao['situacao']=="venda perdida" or $row_prospeccao['situacao']=="venda realizada"){ ?>
        
            <br>
            
            <span class="label_solicitacao">Motivo da baixa: </span>
			<?php if($row_prospeccao['baixa_tipo']=="v"){ ?>Venda<? } ?>
            <?php if($row_prospeccao['baixa_tipo']=="p"){ ?>Perda<? } ?>
            
            <?php if($row_prospeccao['baixa_contrato']!=""){ ?>
            | <span class="label_solicitacao">Contrato da baixa: </span><?php echo $row_prospeccao['baixa_contrato']; ?>
            <? } ?>
			
            <?php if($row_prospeccao['baixa_tipo']=="p"){ ?>
            | <span class="label_solicitacao">Data da Perda: </span><?php echo date('d-m-Y H:i', strtotime($row_prospeccao['baixa_perda_data'])); ?> 
			| <span class="label_solicitacao">Motivo da Perda: </span><?php echo $row_prospeccao['baixa_perda_motivo']; ?>
            <? } ?>
        
        <? } ?>
		</td>
        
	</tr>
</table>
</div>

<!-- observacao -->
<div class="div_solicitacao_linhas4">
<table cellspacing="0" cellpadding="0" width="100%">
  <tr>
    <td style="text-align: left">
	<span class="label_solicitacao">Observação: </span>
	<br>
	<?php echo $row_prospeccao['observacao']; ?>
	<?php if($row_prospeccao['status_flag'] == "a"){ ?>
        <?php if($row_prospeccao['id_usuario_responsavel'] == $row_usuario['IdUsuario'] or (($row_usuario['nivel_prospeccao'] == 1 or $row_usuario['nivel_prospeccao'] == 2) and $row_usuario['praca'] == $row_prospeccao['praca'])){ ?>
            <a href="prospeccao_editar_tabela.php?id_prospeccao=<? echo $row_prospeccao['id']; ?>&interacao=<? echo $row_prospeccao['interacao']; ?>&situacao=editar&acao=Alterar observação&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $prospeccao_editar_tabela_height; ?>&width=<? echo $prospeccao_editar_tabela_width; ?>&modal=true" class="thickbox">
            <img src="imagens/editar.png" id="botao_editar" align="absbottom" border="0" title="Alterar observação"></a>
        <? } ?>
    <? } ?>
	</td>
  </tr>
</table>
</div>
<!-- fim - observacao -->


<!-- venda realizada -->
<? if($row_prospeccao['situacao']=="venda realizada") { ?>
<?
// venda_atual
mysql_select_db($database_conexao, $conexao);
$query_venda_atual = sprintf("SELECT * FROM venda WHERE id = %s", GetSQLValueString($row_prospeccao['baixa_id_venda'], "int"));
$venda_atual = mysql_query($query_venda_atual, $conexao) or die(mysql_error());
$row_venda_atual = mysql_fetch_assoc($venda_atual);
$totalRows_venda_atual = mysql_num_rows($venda_atual);
// fim - venda_atual
?>
<div class="div_solicitacao_linhas3">
<table cellspacing="0" cellpadding="0" width="100%">
	<tr>

		<td style="text-align:left">
		<span class="label_solicitacao">Contrato: </span>
        <?php echo $row_venda_atual['contrato']; ?> | 
        
		<span class="label_solicitacao">Data do contrato: </span>
        <? echo date('d-m-Y', strtotime($row_venda_atual['data_contrato'])); ?> | 
        
        <span class="label_solicitacao">Qtde de tempo para treinamento: </span>
        <?php echo $row_venda_atual['treinamento_tempo']/60; ?> hrs
        </td>
        
        <td style="text-align: right">        
        <span class="label_solicitacao">Venda: </span><?php echo $row_venda_atual['id']; ?> - 
        <a href="venda_editar.php?id_venda=<?php echo $row_venda_atual['id']; ?>&padrao=sim" target="_blank"><strong>Acessar</strong></a>
		</td>
        
	</tr>
</table>
</div>
<? mysql_free_result($venda_atual); ?>
<? } ?>
<!-- fim - venda realizada -->


<!-- venda perdida -->
<? if($row_prospeccao['situacao']=="venda perdida") { ?>
<div class="div_solicitacao_linhas3">
<table cellspacing="0" cellpadding="0" width="100%">
	<tr>

		<td style="text-align:left">
		<span class="label_solicitacao">Motivo da perda: </span>
        <?php echo $row_prospeccao['baixa_perda_motivo']; ?>
		</td>
        
        <td style="text-align:right" width="600">
        <?php if($row_prospeccao['baixa_perda_motivo']=="falta de recurso"){ ?>
        
        <div>
            Já foi verificada a existência de solicitação de implementação ligada ao recurso?: 
            <strong>
            <?php if($row_prospeccao['baixa_perda_recurso_solicitacao_existe']=="s"){echo "sim";} ?>
            <?php if($row_prospeccao['baixa_perda_recurso_solicitacao_existe']=="n"){echo "não";} ?>
            </strong>
        </div>
        
        <div>
            Existe viabilidade de criar uma solicitação de implementação do recurso?: 
            <strong>
            <?php if($row_prospeccao['baixa_perda_recurso_solicitacao_verificada']=="s"){echo "sim";} ?>
            <?php if($row_prospeccao['baixa_perda_recurso_solicitacao_verificada']=="n"){echo "não";} ?>
            </strong>
        </div>

        <div>
            Foi criada uma solicitação de sugestão para implementação do recurso?: 
            <strong>
            <?php if($row_prospeccao['baixa_perda_recurso_solicitacao_sugestao']=="s"){echo "sim";} ?>
            <?php if($row_prospeccao['baixa_perda_recurso_solicitacao_sugestao']=="n"){echo "não";} ?>
            </strong>
        </div>

        <? } ?>
        
        <?php if($row_prospeccao['baixa_perda_motivo']=="concorrência"){ ?>
        
        <div>
            Nome do programa: 
            <strong>
            <?php echo $row_prospeccao['baixa_perda_concorrencia_programa']; ?>
            </strong>
        </div>
        
        <div>
            Fator determinante na escolha do cliente: 
            <strong>
            <?php echo $row_prospeccao['baixa_perda_concorrencia_fator']; ?>
            </strong>
        </div>
        
        <? } ?>
        </td>
        
	</tr>
</table>
</div>
<? } ?>
<!-- fim - venda perdida -->


<!-- Botões ====================================================================================================================================================== --> 
<? if($row_prospeccao['situacao']!="venda perdida" and $row_prospeccao['situacao']!="venda realizada" and $row_prospeccao['situacao']!="cancelada") { ?>
<div class="div_solicitacao_linhas4" id="botoes">
<table cellspacing="0" cellpadding="0" width="100%">
<tr>
<td style="text-align:left">

<!-- Encaminhar -->
<?
if(
// nivel_prospeccao: 1 =======================================================================================================
(
	($row_usuario['nivel_prospeccao'] == 1 and $row_usuario['praca'] == $row_prospeccao['praca']) and 
	
	$row_prospeccao['status_flag'] == "a" and 
	
	(
	$row_prospeccao['situacao']=="analisada" or
	$row_prospeccao['situacao']=="em negociação"
	)		
)
// fim - nivel_prospeccao: 1 =================================================================================================
or
// nivel_prospeccao: 2 =======================================================================================================
(
	($row_usuario['nivel_prospeccao'] == 2 and $row_usuario['praca'] == $row_prospeccao['praca']) and 
	
	$row_prospeccao['status_flag'] == "a" and 
	
	(
	$row_prospeccao['situacao']=="analisada" or
	$row_prospeccao['situacao']=="em negociação"
	)		
)
// fim - nivel_prospeccao: 2 =================================================================================================
or  
// usuario_responsavel ============================================================================================================================
($row_prospeccao['id_usuario_responsavel']==$row_usuario['IdUsuario'] and
												   
		$row_prospeccao['status_flag'] == "a" and 
												   
		(
		$row_prospeccao['situacao']=="em negociação"
		)
		
)
or
($row_prospeccao['encaminhamento_id']==$row_usuario['IdUsuario'] and
												   
		$row_prospeccao['status_flag'] == "a" and 
												   
		(
		$row_prospeccao['situacao']=="analisada"
		)
		
)
// fim - usuario_responsavel =======================================================================================================================
) { ?>

	<a href="prospeccao_editar_tabela.php?id_prospeccao=<? echo $row_prospeccao['id']; ?>&interacao=<? echo $row_prospeccao['interacao']; ?>&situacao=<? echo $row_prospeccao['situacao']; ?>&acao=Encaminhar&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $prospeccao_editar_tabela_height; ?>&width=<? echo $prospeccao_editar_tabela_width; ?>&modal=true"class="thickbox" id="botao_geral2">Encaminhar</a>
	
	<a href="painel.php" target="_blank" id="botao_geral2">Painel</a>
    
<? } ?>
<!-- fim - Encaminhar -->


<!-- Encerrar -->
<?
if(
// nivel_prospeccao: 1 =======================================================================================================
(
	($row_usuario['nivel_prospeccao'] == 1 and $row_usuario['praca'] == $row_prospeccao['praca']) and 
	
	$row_prospeccao['status_flag'] == "a" and 
	
	(
	$row_prospeccao['situacao']=="analisada" or
	$row_prospeccao['situacao']=="em negociação" or 
	$row_prospeccao['situacao']=="solicitado agendamento"
	)		
)
// fim - nivel_prospeccao: 1 =================================================================================================
or
// nivel_prospeccao: 2 =======================================================================================================
(
	($row_usuario['nivel_prospeccao'] == 2 and $row_usuario['praca'] == $row_prospeccao['praca']) and 
	
	$row_prospeccao['status_flag'] == "a" and 
	
	(
	$row_prospeccao['situacao']=="analisada" or
	$row_prospeccao['situacao']=="em negociação" or 
	$row_prospeccao['situacao']=="solicitado agendamento"
	)		
)
// fim - nivel_prospeccao: 2 =================================================================================================
or  
// usuario_responsavel ============================================================================================================================
($row_prospeccao['id_usuario_responsavel']==$row_usuario['IdUsuario'] and
												   
		$row_prospeccao['status_flag'] == "a" and 
												   
		(
		$row_prospeccao['situacao']=="em negociação" or 
		$row_prospeccao['situacao']=="solicitado agendamento"
		)
		
)
or
($row_prospeccao['encaminhamento_id']==$row_usuario['IdUsuario'] and
												   
		$row_prospeccao['status_flag'] == "a" and 
												   
		(
		$row_prospeccao['situacao']=="analisada" or 
		$row_prospeccao['situacao']=="solicitado agendamento"
		)
		
)
// fim - usuario_responsavel =======================================================================================================================
) { ?>
	
	<a href="prospeccao_editar_tabela.php?id_prospeccao=<? echo $row_prospeccao['id']; ?>&interacao=<? echo $row_prospeccao['interacao']; ?>&situacao=editar&acao=Encerrar&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $prospeccao_editar_tabela_height; ?>&width=<? echo $prospeccao_editar_tabela_width; ?>&modal=true"  class="thickbox" id="botao_geral2" style="width: 70px;">Encerrar</a>
    
<? } ?>
<!-- fim - Encerrar -->


<!-- Cancelar -->
<?
if(
// nivel_prospeccao: 1 =======================================================================================================
(
	($row_usuario['nivel_prospeccao'] == 1 and $row_usuario['praca'] == $row_prospeccao['praca']) and 
	
	$row_prospeccao['status_flag'] == "a" and 
	
	(
	$row_prospeccao['situacao']=="analisada" or
	$row_prospeccao['situacao']=="em negociação" or 
	$row_prospeccao['situacao']=="solicitado agendamento"
	)		
)
// fim - nivel_prospeccao: 1 =================================================================================================
or
// nivel_prospeccao: 2 =======================================================================================================
(
	($row_usuario['nivel_prospeccao'] == 2 and $row_usuario['praca'] == $row_prospeccao['praca']) and 
	
	$row_prospeccao['status_flag'] == "a" and 
	
	(
	$row_prospeccao['situacao']=="analisada" or
	$row_prospeccao['situacao']=="em negociação" or 
	$row_prospeccao['situacao']=="solicitado agendamento"
	)		
)
// fim - nivel_prospeccao: 2 =================================================================================================
) { ?>
	
    <a href="prospeccao_editar_tabela.php?id_prospeccao=<? echo $row_prospeccao['id']; ?>&interacao=<? echo $row_prospeccao['interacao']; ?>&situacao=editar&acao=Cancelar&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $prospeccao_editar_tabela_height; ?>&width=<? echo $prospeccao_editar_tabela_width; ?>&modal=true"  class="thickbox" id="botao_geral2" style="width: 70px;">Cancelar</a>
    
<? } ?>
<!-- fim - Cancelar -->


<!-- Agendamento -->
<? if($totalRows_agenda_agendado==0){ ?>
<?
if(
// nivel_prospeccao: 1 =======================================================================================================
(
	($row_usuario['nivel_prospeccao'] == 1 and $row_usuario['praca'] == $row_prospeccao['praca']) and 
	
	$row_prospeccao['status_flag'] == "a" and 
	
	(
	$row_prospeccao['situacao']=="analisada" or
	$row_prospeccao['situacao']=="em negociação" or 
	$row_prospeccao['situacao']=="solicitado agendamento"
	)		
)
// fim - nivel_prospeccao: 1 =================================================================================================
or
// nivel_prospeccao: 2 =======================================================================================================
(
	($row_usuario['nivel_prospeccao'] == 2 and $row_usuario['praca'] == $row_prospeccao['praca']) and 
	
	$row_prospeccao['status_flag'] == "a" and 
	
	(
	$row_prospeccao['situacao']=="analisada" or
	$row_prospeccao['situacao']=="em negociação" or 
	$row_prospeccao['situacao']=="solicitado agendamento"
	)		
)
// fim - nivel_prospeccao: 2 =================================================================================================
or
// nivel_prospeccao: 3 =======================================================================================================
(
	($row_usuario['nivel_prospeccao'] == 3 and $row_usuario['praca'] == $row_prospeccao['praca']) and 
	
	$row_prospeccao['status_flag'] == "a" and 
	
	(
	$row_prospeccao['situacao']=="analisada" or
	$row_prospeccao['situacao']=="em negociação" or 
	$row_prospeccao['situacao']=="solicitado agendamento"
	)		
)
// fim - nivel_prospeccao: 3 =================================================================================================
or  
// usuario_responsavel ============================================================================================================================
($row_prospeccao['id_usuario_responsavel']==$row_usuario['IdUsuario'] and
												   
		$row_prospeccao['status_flag'] == "a" and 
												   
		(
		$row_prospeccao['situacao']=="em negociação" or 
		$row_prospeccao['situacao']=="solicitado agendamento"
		)
		
)
or
($row_prospeccao['encaminhamento_id']==$row_usuario['IdUsuario'] and
												   
		$row_prospeccao['status_flag'] == "a" and 
												   
		(
		$row_prospeccao['situacao']=="analisada" or 
		$row_prospeccao['situacao']=="solicitado agendamento"
		)
		
)
// fim - usuario_responsavel =======================================================================================================================
) { ?>
	
    <a href="prospeccao_editar_tabela.php?id_prospeccao=<? echo $row_prospeccao['id']; ?>&interacao=<? echo $row_prospeccao['interacao']; ?>&situacao=editar&acao=Agendamento&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $prospeccao_editar_tabela_height; ?>&width=<? echo $prospeccao_editar_tabela_width; ?>&modal=true"  class="thickbox" id="botao_geral2" style="width: 100px;">Agendamento</a>
    
<? } ?>
<? } ?>
<!-- fim - Agendamento -->


<!-- Solicitar agendamento -->
<? if($totalRows_agenda_agendado==0){ ?>
<?
if(
// nivel_prospeccao: 1 =======================================================================================================
(
	($row_usuario['nivel_prospeccao'] == 1 and $row_usuario['praca'] == $row_prospeccao['praca']) and 
	
	$row_prospeccao['status_flag'] == "a" and 
	$row_prospeccao['solicita_agendamento'] == "n" and 
	
	(
	$row_prospeccao['situacao']=="analisada" or
	$row_prospeccao['situacao']=="em negociação" or 
	$row_prospeccao['situacao']=="solicitado agendamento"
	)		
)
// fim - nivel_prospeccao: 1 =================================================================================================
or
// nivel_prospeccao: 2 =======================================================================================================
(
	($row_usuario['nivel_prospeccao'] == 2 and $row_usuario['praca'] == $row_prospeccao['praca']) and 
	
	$row_prospeccao['status_flag'] == "a" and 
	$row_prospeccao['solicita_agendamento'] == "n" and 
	
	(
	$row_prospeccao['situacao']=="analisada" or
	$row_prospeccao['situacao']=="em negociação" or 
	$row_prospeccao['situacao']=="solicitado agendamento"
	)		
)
// fim - nivel_prospeccao: 2 =================================================================================================
or
// nivel_prospeccao: 3 =======================================================================================================
(
	($row_usuario['nivel_prospeccao'] == 3 and $row_usuario['praca'] == $row_prospeccao['praca']) and 
	
	$row_prospeccao['status_flag'] == "a" and 
	$row_prospeccao['solicita_agendamento'] == "n" and 
	
	(
	$row_prospeccao['situacao']=="analisada" or
	$row_prospeccao['situacao']=="em negociação" or 
	$row_prospeccao['situacao']=="solicitado agendamento"
	)		
)
// fim - nivel_prospeccao: 3 =================================================================================================
or
// usuario_responsavel ============================================================================================================================
($row_prospeccao['id_usuario_responsavel']==$row_usuario['IdUsuario'] and
												   
		$row_prospeccao['status_flag'] == "a" and 
		$row_prospeccao['solicita_agendamento'] == "n" and 
		
		(
		$row_prospeccao['situacao']=="em negociação"
		)
		
)
or
($row_prospeccao['encaminhamento_id']==$row_usuario['IdUsuario'] and
												   
		$row_prospeccao['status_flag'] == "a" and 
		$row_prospeccao['solicita_agendamento'] == "n" and 
												   
		(
		$row_prospeccao['situacao']=="analisada"
		)
		
)
// fim - usuario_responsavel =======================================================================================================================
) { ?>
	
    <a href="prospeccao_editar_tabela.php?id_prospeccao=<? echo $row_prospeccao['id']; ?>&interacao=<? echo $row_prospeccao['interacao']; ?>&situacao=editar&acao=Solicitar agendamento&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $prospeccao_editar_tabela_height; ?>&width=<? echo $prospeccao_editar_tabela_width; ?>&modal=true"  class="thickbox" id="botao_geral2" style="width: 150px;">Solicitar agendamento</a>
    
<? } ?>
<? } ?>
<!-- fim - Solicitar agendamento -->


<!-- Cancelar solicitação de agendamento -->
<? if($totalRows_agenda_agendado==0){ ?>
<?
if(
// nivel_prospeccao: 1 =======================================================================================================
(
	($row_usuario['nivel_prospeccao'] == 1 and $row_usuario['praca'] == $row_prospeccao['praca']) and 
	
	$row_prospeccao['status_flag'] == "a" and 
	$row_prospeccao['solicita_agendamento'] == "s" and 
	
	(
	$row_prospeccao['situacao']=="solicitado agendamento"
	)		
)
// fim - nivel_prospeccao: 1 =================================================================================================
or
// nivel_prospeccao: 2 =======================================================================================================
(
	($row_usuario['nivel_prospeccao'] == 2 and $row_usuario['praca'] == $row_prospeccao['praca']) and 
	
	$row_prospeccao['status_flag'] == "a" and 
	$row_prospeccao['solicita_agendamento'] == "s" and 
	
	(
	$row_prospeccao['situacao']=="solicitado agendamento"
	)		
)
// fim - nivel_prospeccao: 2 =================================================================================================
or
// nivel_prospeccao: 3 =======================================================================================================
(
	($row_usuario['nivel_prospeccao'] == 3 and $row_usuario['praca'] == $row_prospeccao['praca']) and 
	
	$row_prospeccao['status_flag'] == "a" and 
	$row_prospeccao['solicita_agendamento'] == "s" and 
	
	(
	$row_prospeccao['situacao']=="analisada" or
	$row_prospeccao['situacao']=="em negociação" or 
	$row_prospeccao['situacao']=="solicitado agendamento"
	)		
)
// fim - nivel_prospeccao: 3 =================================================================================================
or
// usuario_responsavel ============================================================================================================================
($row_prospeccao['id_usuario_responsavel']==$row_usuario['IdUsuario'] and
												   
		$row_prospeccao['status_flag'] == "a" and 
		$row_prospeccao['solicita_agendamento'] == "s" and 
		
		(
		$row_prospeccao['situacao']=="solicitado agendamento"
		)
		
)
or
($row_prospeccao['encaminhamento_id']==$row_usuario['IdUsuario'] and
												   
		$row_prospeccao['status_flag'] == "a" and 
		$row_prospeccao['solicita_agendamento'] == "s" and 
												   
		(
		$row_prospeccao['situacao']=="solicitado agendamento"
		)
		
)
// fim - usuario_responsavel =======================================================================================================================
) { ?>
	
    <a href="prospeccao_editar_tabela.php?id_prospeccao=<? echo $row_prospeccao['id']; ?>&interacao=<? echo $row_prospeccao['interacao']; ?>&situacao=editar&acao=Cancelar solicitação de agendamento&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $prospeccao_editar_tabela_height; ?>&width=<? echo $prospeccao_editar_tabela_width; ?>&modal=true"  class="thickbox" id="botao_geral2" style="width: 230px;">Cancelar solicitação de agendamento</a>
    
<? } ?>
<? } ?>
<!-- fim - Cancelar solicitação de agendamento -->


<!-- Questionário -->
<?
if(
// nivel_prospeccao: 1 =======================================================================================================
(
	($row_usuario['nivel_prospeccao'] == 1 and $row_usuario['praca'] == $row_prospeccao['praca']) and 
	
	$row_prospeccao['status_flag'] == "a" and 
	
	(
	$row_prospeccao['situacao']=="analisada" or
	$row_prospeccao['situacao']=="em negociação" or 
	$row_prospeccao['situacao']=="solicitado agendamento"
	)		
)
// fim - nivel_prospeccao: 1 =================================================================================================
or
// nivel_prospeccao: 2 =======================================================================================================
(
	($row_usuario['nivel_prospeccao'] == 2 and $row_usuario['praca'] == $row_prospeccao['praca']) and 
	
	$row_prospeccao['status_flag'] == "a" and 
	
	(
	$row_prospeccao['situacao']=="analisada" or
	$row_prospeccao['situacao']=="em negociação" or 
	$row_prospeccao['situacao']=="solicitado agendamento"
	)		
)
// fim - nivel_prospeccao: 2 =================================================================================================
or  
// usuario_responsavel ============================================================================================================================
($row_prospeccao['id_usuario_responsavel']==$row_usuario['IdUsuario'] and
												   
		$row_prospeccao['status_flag'] == "a" and 
												   
		(
		$row_prospeccao['situacao']=="em negociação" or 
		$row_prospeccao['situacao']=="solicitado agendamento"
		)
		
)
or
($row_prospeccao['encaminhamento_id']==$row_usuario['IdUsuario'] and
												   
		$row_prospeccao['status_flag'] == "a" and 
												   
		(
		$row_prospeccao['situacao']=="analisada" or 
		$row_prospeccao['situacao']=="solicitado agendamento"
		)
		
)
// fim - usuario_responsavel =======================================================================================================================
) { ?>
	
    <a href="prospeccao_questionario.php?id_prospeccao=<? echo $row_prospeccao['id']; ?>&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $prospeccao_editar_tabela_height; ?>&width=<? echo $prospeccao_editar_tabela_width; ?>&modal=true"  class="thickbox" id="botao_geral2" style="width: 90px;">Questionário</a>
    
<? } ?>
<!-- fim - Questionário -->


<!-- Contatos ========================================================================================================================================= -->
<? if(
	  ($row_usuario['nivel_prospeccao'] == 1 and $row_usuario['praca'] == $row_prospeccao['praca']) or 
	  ($row_usuario['nivel_prospeccao'] == 2 and $row_usuario['praca'] == $row_prospeccao['praca']) or 
	  ($row_usuario['nivel_prospeccao'] == 3 and $row_usuario['praca'] == $row_prospeccao['praca']) or 
	  $row_usuario['controle_prospeccao'] == "Y" or 
	  $row_prospeccao['id_usuario_responsavel'] == $row_usuario['IdUsuario'] or 
	  $row_prospeccao['praca'] == $row_usuario['praca']
){ ?>

	<a href="prospeccao_editar_tabela.php?id_prospeccao=<? echo $row_prospeccao['id']; ?>&interacao=<? echo $row_prospeccao['interacao']; ?>&situacao=editar&acao=Contato&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $prospeccao_editar_tabela_height; ?>&width=<? echo $prospeccao_editar_tabela_width; ?>&modal=true"  class="thickbox" id="botao_geral2" style="width: 90px;">Contato (<? echo $row_prospeccao_contato['retorno']; ?>)</a>
    
<? } ?>
<!-- fim - Contatos =================================================================================================================================== -->


<!-- Questionar ========================================================================================================================================= -->
<? if(
	  ($row_usuario['nivel_prospeccao'] == 1 and $row_usuario['praca'] == $row_prospeccao['praca']) or 
	  ($row_usuario['nivel_prospeccao'] == 2 and $row_usuario['praca'] == $row_prospeccao['praca']) or 
	  ($row_usuario['nivel_prospeccao'] == 3 and $row_usuario['praca'] == $row_prospeccao['praca']) or 
	  $row_usuario['controle_prospeccao'] == "Y" or 
	  $row_prospeccao['id_usuario_responsavel'] == $row_usuario['IdUsuario'] or 
	  $row_prospeccao['praca'] == $row_usuario['praca']
){ ?>

	<a href="prospeccao_editar_tabela.php?id_prospeccao=<? echo $row_prospeccao['id']; ?>&interacao=<? echo $row_prospeccao['interacao']; ?>&situacao=editar&acao=Questionar&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $prospeccao_editar_tabela_height; ?>&width=<? echo $prospeccao_editar_tabela_width; ?>&modal=true"  class="thickbox" id="botao_geral2" style="width: 90px;">Questionar</a>
    
<? } ?>
<!-- fim - Questionar =================================================================================================================================== -->


<!-- Registrar reclamação ========================================================================================================================================= -->
<? if( 
	$row_prospeccao['tipo_cliente'] == "a"
){ ?>

	<a href="suporte_gerar.php?tipo_suporte=r&inloco=n&cobranca=n&cliente=<? echo $row_prospeccao['codigo_empresa']; ?>6&contrato=<? echo $row_prospeccao['contrato']; ?>&reclamacao_prospeccao=<? echo $row_prospeccao['id']; ?>" id="botao_geral2">Registrar reclamação</a>
    
<? } ?>
<!-- fim - Registrar reclamação =================================================================================================================================== -->
</td>

<td align="right" style="color:#F00; font-weight:bold;">

<!-- Aceitar / Recusar ============================================================================================================================== -->
<?
if(
// analisada ----------------------------------------------------------------------------
$row_prospeccao['situacao']=="analisada" and (

	(
	 
	$row_prospeccao['status']=="encaminhada para usuario responsavel" and 
	($row_usuario['IdUsuario']==$row_prospeccao['id_usuario_responsavel']) and 
	($row_prospeccao['status_recusa']!="1")
	
	)or(
	
	$row_prospeccao['status']=="pendente usuario responsavel" and 
	($row_usuario['IdUsuario']==$row_prospeccao['encaminhamento_id']) and 
	($row_prospeccao['status_recusa']=="1")
	
	)
	
)
// fim - analisada ----------------------------------------------------------------------------
or(
	
	($row_prospeccao['status']=="devolvida para usuario responsavel" and $row_usuario['IdUsuario']==$row_prospeccao['id_usuario_responsavel'])
	
)

) { ?>

        <div style="float:right; margin-left: 5px;">
        <a href="prospeccao_editar_tabela.php?id_prospeccao=<? echo $row_prospeccao['id']; ?>&interacao=<? echo $row_prospeccao['interacao']; ?>&situacao=<? echo $row_prospeccao['situacao']; ?>&acao=Aceitar&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $prospeccao_editar_tabela_height; ?>&width=<? echo $prospeccao_editar_tabela_width; ?>&modal=true" class="thickbox" id="botao_geral2">Aceitar</a>
    
        <a href="prospeccao_editar_tabela.php?id_prospeccao=<? echo $row_prospeccao['id']; ?>&interacao=<? echo $row_prospeccao['interacao']; ?>&situacao=<? echo $row_prospeccao['situacao']; ?>&acao=Recusar&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $prospeccao_editar_tabela_height; ?>&width=<? echo $prospeccao_editar_tabela_width; ?>&modal=true" class="thickbox" id="botao_geral2">Recusar</a>
        </div>

<? } ?>
<!-- fim - Aceitar / Recusar ======================================================================================================================== -->


<!-- Mensagens ==================================================================================================================================== -->
<? if(
	  $row_prospeccao['situacao']=="analisada" and 
	  
	  $row_prospeccao['status']=="encaminhada para usuario responsavel" and 
	  $row_prospeccao['status_recusa']!="1"
){ ?>
    
	<div id="texto_botao_geral">Aguardando aceitação do usuário responsável</div>
    
<? } ?>


<? if(
	  $row_prospeccao['situacao']=="analisada" and 
	  
	  $row_prospeccao['status']=="pendente usuario responsavel" and 
	  $row_prospeccao['status_recusa']=="1"
){ ?>
    
	<div id="texto_botao_geral">Aguardando aceitação de recusa</div>
    
<? } ?>

<?
if(
	$row_prospeccao['status']=="devolvida para usuario responsavel"
) { ?>
      
	<div id="texto_botao_geral">Aguardando aceitação de devolução</div>
    
<? } ?>
<!-- fim - Mensagens ============================================================================================================================== -->

</td>          
</tr>
</table>
</div>
<? } ?>

<? if($row_prospeccao['situacao']=="venda perdida" or $row_prospeccao['situacao']=="venda realizada" or $row_prospeccao['situacao']=="cancelada") { ?>
<div class="div_solicitacao_linhas4" id="botoes">
<table cellspacing="0" cellpadding="0" width="100%">
<tr>
<td style="text-align:left">

<!-- Questionar ========================================================================================================================================= -->
<? if(
	  ($row_usuario['nivel_prospeccao'] == 1 and $row_usuario['praca'] == $row_prospeccao['praca']) or 
	  ($row_usuario['nivel_prospeccao'] == 2 and $row_usuario['praca'] == $row_prospeccao['praca']) or 
	  ($row_usuario['nivel_prospeccao'] == 3 and $row_usuario['praca'] == $row_prospeccao['praca']) or 
	  $row_usuario['controle_prospeccao'] == "Y" or 
	  $row_prospeccao['id_usuario_responsavel'] == $row_usuario['IdUsuario'] or 
	  $row_prospeccao['praca'] == $row_usuario['praca']
){ ?>

	<a href="prospeccao_editar_tabela.php?id_prospeccao=<? echo $row_prospeccao['id']; ?>&interacao=<? echo $row_prospeccao['interacao']; ?>&situacao=editar&acao=Questionar&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $prospeccao_editar_tabela_height; ?>&width=<? echo $prospeccao_editar_tabela_width; ?>&modal=true"  class="thickbox" id="botao_geral2" style="width: 90px;">Questionar</a>
    
<? } ?>
<!-- fim - Questionar =================================================================================================================================== -->


<!-- Estornar ========================================================================================================================================= -->
<? if(
	  $row_prospeccao['situacao']=="venda perdida" and 
	  (
	  $row_usuario['controle_prospeccao'] == "Y" or 
	  $row_prospeccao['id_usuario_responsavel'] == $row_usuario['IdUsuario'] or 
	  $row_prospeccao['praca'] == $row_usuario['praca']
	  )
){ ?>

	<a href="prospeccao_editar_tabela.php?id_prospeccao=<? echo $row_prospeccao['id']; ?>&interacao=<? echo $row_prospeccao['interacao']; ?>&situacao=editar&acao=Estornar&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $prospeccao_editar_tabela_height; ?>&width=<? echo $prospeccao_editar_tabela_width; ?>&modal=true"  class="thickbox" id="botao_geral2" style="width: 90px;">Estornar</a>
    
<? } ?>
<!-- fim - Estornar =================================================================================================================================== -->

  
</td>
</tr>
</table>
</div>
<? } ?>
<!-- fim - botões -->


<!-- agenda -->
<? if($totalRows_agenda > 0){ ?>
<div class="div_solicitacao_linhas4">
<table cellspacing="0" cellpadding="0" width="100%">
  <tr>
    <td style="text-align: left">
    <span class="label_solicitacao">Agenda: </span>
	<!-- tabela -->   
    <table width="100%" border="0" cellspacing="0" cellpadding="0" style="padding: 5px;">
        <tr bgcolor="#F1F1F1">
            <td style="padding: 5px;" width="70"><strong>Número</strong></td>
            <td style="padding: 5px;" width="180"><strong>Data</strong></td>
            <td style="padding: 5px;" width="100"><strong>Status</strong></td>
			<td style="padding: 5px;" width="100"><strong>Tipo</strong></td>
            <td style="padding: 5px;" width="300"><strong>Ações</strong></td>
            <td style="padding: 5px;"><strong>Descrição</strong></td>
        </tr>
        
        <? $contador_agenda = 0; ?>
        
        <?php do { ?>
        <tr bgcolor="<? if(($contador_agenda % 2)==1){echo "#F1F1F1";}else{echo "#FFFFFF";} ?>">
            <td style="padding: 5px;"><?php echo $row_agenda['id_agenda']; ?></td>
            <td style="padding: 5px;">
				Início: <? echo date('d-m-Y  H:i:s', strtotime($row_agenda['data_inicio'])); ?>
                <br>
            	Fim:&nbsp;&nbsp;&nbsp;&nbsp; <? echo date('d-m-Y  H:i:s', strtotime($row_agenda['data'])); ?>
            </td>
            <td style="padding: 5px;">
				<?php if($row_agenda['status']=="a"){echo "Agendado";}?>
                <?php if($row_agenda['status']=="f"){echo "Finalizado";}?>
                <?php if($row_agenda['status']=="c"){echo "Cancelado";}?>
            </td> 
            <td style="padding: 5px;"><?php echo $row_agenda['prospeccao_agenda_tipo_titulo'];?></td>                  
            <td style="padding: 5px;">
            
            <?php if($row_agenda['status']=="a"){ ?>
        
        	<!-- botoes -->
            <div id="botoes">

			<? if( ($row_prospeccao['status_flag'] == "a") and (
																($row_usuario['nivel_prospeccao'] == 1 and $row_usuario['praca'] == $row_prospeccao['praca']) or 
																($row_usuario['nivel_prospeccao'] == 2 and $row_usuario['praca'] == $row_prospeccao['praca']) or 
																($row_usuario['nivel_prospeccao'] == 3 and $row_usuario['praca'] == $row_prospeccao['praca']) or 
																$row_prospeccao['id_usuario_responsavel'] == $row_usuario['IdUsuario']
																)
			){ ?>
                        
            	<a href="prospeccao_editar_tabela.php?id_prospeccao=<? echo $row_prospeccao['id']; ?>&interacao=<? echo $row_prospeccao['interacao']; ?>&situacao=editar&acao=Reagendar&resposta=&id_agenda=<? echo $row_agenda['id_agenda']; ?>&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $prospeccao_editar_tabela_height; ?>&width=<? echo $prospeccao_editar_tabela_width; ?>&modal=true"  class="thickbox" id="botao_geral2" style="width: 70px;">Reagendar</a>
            
            <? } ?>
            
			<? if( ($row_prospeccao['status_flag'] == "a") and (
																($row_usuario['nivel_prospeccao'] == 1 and $row_usuario['praca'] == $row_prospeccao['praca']) or 
																($row_usuario['nivel_prospeccao'] == 2 and $row_usuario['praca'] == $row_prospeccao['praca']) or 
																$row_prospeccao['id_usuario_responsavel'] == $row_usuario['IdUsuario']
																)
			){ ?>
            
            	<a href="prospeccao_editar_tabela.php?id_prospeccao=<? echo $row_prospeccao['id']; ?>&interacao=<? echo $row_prospeccao['interacao']; ?>&situacao=editar&acao=Finalizar agendamento&resposta=&id_agenda=<? echo $row_agenda['id_agenda']; ?>&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $prospeccao_editar_tabela_height; ?>&width=<? echo $prospeccao_editar_tabela_width; ?>&modal=true"  class="thickbox" id="botao_geral2" style="width: 70px;">Finalizar</a>
				
				<a href="prospeccao_formulario_visita.php?id_prospeccao=<? echo $row_prospeccao['id']; ?>&id_agenda=<? echo $row_agenda['id_agenda']; ?>" target="_blank" id="botao_geral2" style="width: 70px;">Formulário</a>
                
			<? } ?>
            
            </div>
        	<!-- fim - botoes -->
             
            <? } ?>
            
            </td>

            <td style="padding: 5px;">
            <?php if($row_agenda['prospeccao_responsavel_cancelado']!=""){ ?>
            Resp. pelo cancelamento: <strong><?php echo $row_agenda['prospeccao_responsavel_cancelado']; ?></strong>
            <br>
            <? } ?>
			<?php echo $row_agenda['descricao']; ?>
            </td>
        </tr>
        <? $contador_agenda = $contador_agenda + 1; ?>
        <?php } while ($row_agenda = mysql_fetch_assoc($agenda)); ?>
        
    </table>    
    <!-- fim - tabela -->
    </td>
  </tr>
</table>
</div>
<? } ?>
<!-- fim - agenda -->

<!-- reclamacao_prospeccao -->
<? if($totalRows_reclamacao_prospeccao > 0){ ?>
<div class="div_solicitacao_linhas4">
<table cellspacing="0" cellpadding="0" width="100%">
  <tr>
    <td style="text-align: left">Reclamações vinculadas: </span>
	<!-- tabela -->   
    <table width="100%" border="0" cellspacing="0" cellpadding="0" style="padding: 5px;">
        <tr bgcolor="#F1F1F1">
            <td style="padding: 5px;" width="70"><strong>Número</strong></td>
            <td style="padding: 5px;" width="180"><strong>Data</strong></td>
            <td style="padding: 5px;" width="100"><strong>Status</strong></td>
            <td style="padding: 5px;" width="300"><strong>Título</strong></td>
            <td style="padding: 5px;"><strong>Ações</strong></td>
        </tr>
        
        <? $contador_reclamacao_prospeccao = 0; ?>
        
        <?php do { ?>
        <tr bgcolor="<? if(($contador_reclamacao_prospeccao % 2)==1){echo "#F1F1F1";}else{echo "#FFFFFF";} ?>">
            <td style="padding: 5px;"><?php echo $row_reclamacao_prospeccao['id']; ?></td>
            <td style="padding: 5px;"><? echo date('d-m-Y  H:i', strtotime($row_reclamacao_prospeccao['data_suporte'])); ?></td>
            <td style="padding: 5px;"><?php echo $row_reclamacao_prospeccao['situacao']; ?></td> 
            <td style="padding: 5px;"><?php echo $row_reclamacao_prospeccao['titulo']; ?></td>
            <td style="padding: 5px;"><a href="suporte_editar.php?id_suporte=<? echo $row_reclamacao_prospeccao['id']; ?>&padrao=sim" target="_blank" id="botao_geral2" style="width: 70px;">Abrir</a></td>
        </tr>
        <? $contador_reclamacao_prospeccao = $contador_reclamacao_prospeccao + 1; ?>
        <?php } while ($row_reclamacao_prospeccao = mysql_fetch_assoc($reclamacao_prospeccao)); ?>
        
    </table>    
    <!-- fim - tabela -->
    </td>
  </tr>
</table>
</div>
<? } ?>
<!-- fim - reclamacao_prospeccao -->

<div class="div_solicitacao_linhas3">
<table cellspacing="0" cellpadding="0" width="100%">
  <tr>

    <td width="200" valign="top">

    <? if($row_prospeccao['situacao']!="venda perdida" and $row_prospeccao['situacao']!="venda realizada" and $row_prospeccao['situacao']!="cancelada"){ ?>

            <!-- usuario_responsavel leu em -->
			<? if($row_prospeccao['id_usuario_responsavel']!=""){ ?>
                <? echo $row_prospeccao['usuario_responsavel']; ?>
			<? } else { ?>
				<span style="color:#F00;">Sem responsável</span>                
			<? } ?>
            
            <!-- Alterar usuário responsável -->
            <?php if(
                     ($row_usuario['nivel_prospeccao'] == 1 and $row_usuario['praca'] == $row_prospeccao['praca']) or 
					 ($row_usuario['nivel_prospeccao'] == 2 and $row_usuario['praca'] == $row_prospeccao['praca'])
            ){ ?>
            <a href="prospeccao_editar_tabela.php?id_prospeccao=<? echo $row_prospeccao['id']; ?>&interacao=<? echo $row_prospeccao['interacao']; ?>&situacao=editar&acao=Alterar usuário responsável&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $prospeccao_editar_tabela_height; ?>&width=<? echo $prospeccao_editar_tabela_width; ?>&modal=true" class="thickbox">
            <img src="imagens/editar.png" id="botao_editar" align="absbottom" border="0" title="Alterar usuário responsável">
            </a>
            <? } ?>
            <!-- fim - Alterar usuário responsável -->

            <br>
            <span class="label_solicitacao">Responsável leu em:</span>
            
            <br>
            <? if($row_prospeccao['usuario_responsavel_leu']!=""){ echo date('d-m-Y - H:i:s', strtotime($row_prospeccao['usuario_responsavel_leu'])); } else { echo "não leu"; } ?>
            <!-- fim - usuario_responsavel leu em -->   


            <!-- duração -->
            <br><br>
            <span class="label_solicitacao">Duração:</span>
            <br>
            <?	
            $data_ini = strtotime($row_prospeccao['data_prospeccao']);
            $data_final = strtotime(date("Y-m-d H:i:s"));

			$tHoras = ($data_final - $data_ini) / 3600;
			$tMinutos = ($data_final - $data_ini) % 3600 / 60;
			
			echo sprintf('%02dh %02dm', $tHoras, $tMinutos);
            ?>
            <!-- fim - duração -->
                    
    <? } ?>

    <? if($row_prospeccao['situacao']=="venda perdida" or $row_prospeccao['situacao']=="venda realizada" or $row_prospeccao['situacao']=="cancelada"){ ?>

            <!-- usuario_responsavel leu em -->
			<? echo $row_prospeccao['usuario_responsavel']; ?>
			<br>
            <span class="label_solicitacao">Usuário Responsável</span>
            <!-- fim - usuario_responsavel leu em -->
    
    <? } ?>
    
    </td>
    <td style="padding: 0px;" valign="top">
    
	<div class="div_descricao" style="min-height: 150px;">
    
    <!-- descricao -->
    <? if ($totalRows_descricao > 0){ ?>
	<?php do { ?>

        <strong>
        <? if($row_descricao['usuario_responsavel'] != ""){echo $row_descricao['usuario_responsavel'];}else{echo "Sistema";} ?> | 
        <? echo date('d-m-Y | H:i:s', strtotime($row_descricao['data'])); ?> | 
        <?php echo $row_descricao['tipo_postagem']; ?>
        <br>
        </strong>
        
        <?php if($row_descricao['questionado'] != ""){ ?>
            Para: <strong><?php echo $row_descricao['questionado']; ?></strong>
            <br>
        <? } ?>
            
        <?php echo $row_descricao['descricao']; ?>
        
		<div style=" width: 100%; height: 1px; background-color: #CCCCCC; margin-top: 10px; margin-bottom: 10px; margin-left: 0px; margin-right: 0px;"></div>
    
    <?php } while ($row_descricao = mysql_fetch_assoc($descricao)); ?>   
    <? } ?>
    <!-- fim - descricao -->
        
	</div>
    
	</td>

  </tr>
</table>
</div>


<div class="div_solicitacao_linhas3" id="botoes">
<table cellspacing="0" cellpadding="0" width="100%">
  <tr>

    <td style="text-align: left;">
    <a href="#" class="botao_geral" style="width: 150px;" onclick="print()">Imprimir</a>

    <!-- anexos -->
    <a href="prospeccao_editar_upload.php?id_prospeccao=<? echo $row_prospeccao['id']; ?>&situacao=&acao=Arquivos em  anexo&resposta=&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $prospeccao_editar_tabela_height; ?>&width=<? echo $prospeccao_editar_tabela_width; ?>&modal=true"  class="thickbox" id="botao_geral" style="width: 150px;">Arquivos em anexo (<? echo $totalRows_arquivos_anexos; ?>)</a>
    <!-- fim - anexos -->
    
    <a href="prospeccao_questionario_visualizar.php?id_prospeccao=<? echo $row_prospeccao['id']; ?>" target="_blank" class="botao_geral" style="width: 150px;">Imprimir Questionário</a>
    
	<?php if($row_prospeccao['status_flag'] == "a"){ ?>
        <?php if($row_prospeccao['id_usuario_responsavel'] == $row_usuario['IdUsuario'] or (($row_usuario['nivel_prospeccao'] == 1 or $row_usuario['nivel_prospeccao'] == 2) and $row_usuario['praca'] == $row_prospeccao['praca'])){ ?>
			<a href="agenda_popup.php?id_usuario_responsavel=<? echo $row_usuario['IdUsuario']; ?>&data_atual=<? echo date('d-m-Y'); ?>&height=<? echo $prospeccao_editar_tabela_height; ?>&width=<? echo $prospeccao_editar_tabela_width; ?>&placeValuesBeforeTB_=savedValues&TB_iframe=true" id="botao_geral2" class="thickbox">Ver agenda</a>
		<? } ?>
	<? } ?>
    </td>


  </tr>
</table>
</div>


</body>
</html>

<!-- reclamacao_consulta -->
<? if(isset($_GET['padrao']) && ($_GET['padrao'] == "sim")){ ?>
	<? if($totalRows_reclamacao_consulta > 0){ ?>
    
        <script>
        alert('<? echo $reclamacao_consulta_mensagem; ?>');
        </script>

    <? } ?>
<? } ?>
<!-- fim - reclamacao_consulta -->

<?php
mysql_free_result($usuario);
mysql_free_result($prospeccao);
mysql_free_result($prospeccao_concorrente);
mysql_free_result($prospeccao_contador);
mysql_free_result($descricao);
mysql_free_result($agenda);
mysql_free_result($agenda_agendado);
mysql_free_result($arquivos_anexos);
mysql_free_result($reclamacao_prospeccao);
mysql_free_result($reclamacao_consulta);
mysql_free_result($prospeccao_contato);
?>