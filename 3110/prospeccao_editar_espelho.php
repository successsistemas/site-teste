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
require_once('venda_funcao_update.php');

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

// venda_editar (recordset) - seleciona o venda atual
$colname_venda_editar = "-1";
if (isset($_GET['id_venda'])) {
  $colname_venda_editar = $_GET['id_venda'];
}
mysql_select_db($database_conexao, $conexao);
$query_venda_editar = sprintf("
SELECT id, id_usuario_responsavel, status, praca, 
(SELECT nome FROM usuarios WHERE usuarios.IdUsuario = venda.id_usuario_responsavel) as usuario_responsavel 
FROM venda 
WHERE id = %s", GetSQLValueString($colname_venda_editar, "int"));
$venda_editar = mysql_query($query_venda_editar, $conexao) or die(mysql_error());
$row_venda_editar = mysql_fetch_assoc($venda_editar);
$totalRows_venda_editar = mysql_num_rows($venda_editar);
// fim - venda_ditar (recordset) - seleciona o venda atual

// caso não tenho venda, volta para listagem ********************************
if ($totalRows_venda_editar < 1) { 
	$site_link_redireciona = "venda.php?padrao=sim&".$venda_padrao;
	echo sprintf("<meta http-equiv=\"refresh\" content=\"0; url=%s\">", $site_link_redireciona);
	exit;
}
// fim - caso não tenho venda, volta para listagem **************************

// insert - LEU --------------------------------------------------
// se é usuario_responsavel
if($row_venda_editar['id_usuario_responsavel']==$row_usuario['IdUsuario']) {
		$updateSQL_leu = sprintf("UPDATE venda SET usuario_responsavel_leu=%s WHERE id=%s",
						   GetSQLValueString(date("Y-m-d H:i:s"), "date"),
						   GetSQLValueString($row_venda_editar['id'], "int"));
		
		mysql_select_db($database_conexao, $conexao);
		$Result_leu = mysql_query($updateSQL_leu, $conexao) or die(mysql_error());
}
// fim - se é usuario_responsavel
// fim - insert - LEU  -------------------------------------------

mysql_free_result($venda_editar);

// ----------------------------------------------------------------------------------------------------------------------------------------------------------------

// prospeccao
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
// fim - prospeccao

// venda
$colname_venda = "-1";
if (isset($_GET['id_venda'])) {
  $colname_venda = $_GET['id_venda'];
}
mysql_select_db($database_conexao, $conexao);
$query_venda = sprintf("
SELECT venda.*, 
(SELECT nome FROM usuarios WHERE venda.id_usuario_responsavel = usuarios.IdUsuario) AS usuario_responsavel   
FROM venda 
WHERE venda.id = %s", 
GetSQLValueString($colname_venda, "int"));
$venda = mysql_query($query_venda, $conexao) or die(mysql_error());
$row_venda = mysql_fetch_assoc($venda);
$totalRows_venda = mysql_num_rows($venda);
// fim - venda

// $colname_contrato
$colname_contrato = "-1";
if (isset($row_venda["contrato"])) {
  $colname_contrato = $row_venda["contrato"];
}
// fim - $colname_contrato

// manutencao_dados
mysql_select_db($database_conexao, $conexao);
$query_manutencao_dados = sprintf("
SELECT 
geral_tipo_praca_executor.praca, 
da37.codigo17, da37.cliente17, da37.status17, da37.obs17, da37.tpocont17, da37.visita17, da37.optacuv17, da37.versao17, da37.espmod17, versao17, espmod17, da37.datvis17, 
geral_tipo_contrato.descricao as tpocont17_descricao, 
geral_tipo_visita.descricao as visita17_descricao

FROM da37 
INNER JOIN geral_tipo_praca_executor ON da37.vendedor17 = geral_tipo_praca_executor.IdExecutor
INNER JOIN geral_tipo_contrato ON da37.tpocont17 = geral_tipo_contrato.IdTipoContrato
INNER JOIN geral_tipo_visita ON da37.visita17 = geral_tipo_visita.IdTipoVisita

WHERE da37.codigo17 = %s and da37.sr_deleted <> 'T'", 
GetSQLValueString($colname_contrato, "text"));
$manutencao_dados = mysql_query($query_manutencao_dados, $conexao) or die(mysql_error());
$row_manutencao_dados = mysql_fetch_assoc($manutencao_dados);
$totalRows_manutencao_dados = mysql_num_rows($manutencao_dados);
// fim - manutencao_dados

// empresa_dados ---------------------------
if($totalRows_manutencao_dados > 0 and $row_venda['codigo_empresa']!=""){ // contrato existe na tabela 'DA37s9'
	
	mysql_select_db($database_conexao, $conexao);
	$query_empresa_dados = sprintf("
	SELECT nome1, cgc1, insc1, endereco1, bairro1, cidade1, uf1, telefone1, comercio1, cep1, ultcompra1, atraso1, status1, flag1
	FROM da01 
	WHERE codigo1 = %s and da01.sr_deleted <> 'T'", 
	GetSQLValueString($row_manutencao_dados['cliente17'], "text"));
	$empresa_dados = mysql_query($query_empresa_dados, $conexao) or die(mysql_error());
	$row_empresa_dados = mysql_fetch_assoc($empresa_dados);
	$totalRows_empresa_dados = mysql_num_rows($empresa_dados);

}else{ // contrato NÃO existe na tabela 'DA37s9'
	
	mysql_select_db($database_conexao, $conexao);
	$query_empresa_dados = sprintf("
	SELECT nome_razao_social AS nome1, cpf_cnpj AS cgc1, rg_inscricao AS insc1, concat(endereco,' - ',endereco_numero) AS endereco1, bairro AS bairro1, cidade AS cidade1, 
	uf AS uf1, telefone telefone1, celular AS comercio1, cep AS cep1, '' AS ultcompra1, '' AS atraso1, '' AS status1, '' AS flag1
	FROM prospeccao 
	WHERE id = %s", 
	GetSQLValueString($row_venda['id_prospeccao'], "text"));
	$empresa_dados = mysql_query($query_empresa_dados, $conexao) or die(mysql_error());
	$row_empresa_dados = mysql_fetch_assoc($empresa_dados);
	$totalRows_empresa_dados = mysql_num_rows($empresa_dados);
	
}
// fim - empresa_dados ---------------------

// venda_descricoes
$colname_descricao = "-1";
if (isset($_GET['id_venda'])) {
  $colname_descricao = $_GET['id_venda'];
}
mysql_select_db($database_conexao, $conexao);
$query_descricao = sprintf("
SELECT * 
FROM venda_descricoes 
WHERE id_venda = %s 
ORDER BY id DESC", GetSQLValueString($colname_descricao, "text"));
$descricao = mysql_query($query_descricao, $conexao) or die(mysql_error());
$row_descricao = mysql_fetch_assoc($descricao);
$totalRows_descricao = mysql_num_rows($descricao);
// fim - venda_descricoes

// arquivos_anexos
mysql_select_db($database_conexao, $conexao);
$query_arquivos_anexos = sprintf("SELECT id_arquivo FROM venda_arquivos WHERE id_venda = %s", GetSQLValueString($_GET['id_venda'], "int"));
$arquivos_anexos = mysql_query($query_arquivos_anexos, $conexao) or die(mysql_error());
$row_arquivos_anexos = mysql_fetch_assoc($arquivos_anexos);
$totalRows_arquivos_anexos = mysql_num_rows($arquivos_anexos);
// fim - arquivos_anexos

// venda_modulos
mysql_select_db($database_conexao, $conexao);
$query_venda_modulos = sprintf("SELECT geral_tipo_modulo.descricao AS modulo FROM venda_modulos LEFT JOIN geral_tipo_modulo ON venda_modulos.id_modulo = geral_tipo_modulo.IdTipoModulo WHERE venda_modulos.id_venda = %s", GetSQLValueString($_GET['id_venda'], "int"));
$venda_modulos = mysql_query($query_venda_modulos, $conexao) or die(mysql_error());
$row_venda_modulos = mysql_fetch_assoc($venda_modulos);
$totalRows_venda_modulos = mysql_num_rows($venda_modulos);
// fim - venda_modulos

$avaliacao_implantacao = date('d-m-Y  H:i:s', strtotime($row_venda['data_inicio'])+($row_parametros['implantacao_prazo'] * 86400));
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
window.history.forward(1); // Desabilita a função de voltar do Browser

$(document).ready(function() {
		
	// imprime o 'espelho' e vai para a tela da 'venda'
	$('#imprimir').click(function() {
		
		print();
		
		// post
		$.post("prospeccao_editar_espelho_impressao.php", {
			   id_venda: <?php echo $row_venda['id']; ?>
			   }, function(data) {

					window.open('venda_editar.php?id_venda=<?php echo $row_venda['id']; ?>&padrao=sim', '_self');
					
			   }
		);
		// fim - post
	
	});	
	// fim - imprime o 'espelho' e vai para a tela da 'venda'

});
</script>
<title>Venda n° <?php echo $row_venda['id']; ?></title>
</head>

<body>

<div class="div_solicitacao_linhas">
<table cellspacing="0" cellpadding="0" width="100%">
	<tr>
		<td style="text-align:left">
		Espelho da Venda n° <? echo $row_venda['id']; ?> / Prospecção n° <? echo $row_venda['id_prospeccao']; ?>
		</td>

		<td style="text-align: right">
		Usuário logado: <? echo $row_usuario['nome']; ?> (<? echo $row_usuario['nivel_venda']; ?>)
        </td>
	</tr>
</table>
</div>

<div class="div_solicitacao_linhas2">
<table cellspacing="0" cellpadding="0" width="100%">
	<tr>
        <td style="text-align:left">
        <span class="label_solicitacao">Empresa: </span>
        <?php echo utf8_encode($row_empresa_dados['nome1']); ?> | 
        
        <span class="label_solicitacao">Praça: </span>
        <?php echo $row_venda['praca']; ?>
        </td>
        
        <td style="text-align: right" width="250">
        </td>
	</tr>
</table>
</div>

<div class="div_solicitacao_linhas3">
<table cellspacing="0" cellpadding="0" width="100%">
	<tr>
		<td style="text-align:left">
		<span class="label_solicitacao">Endereço: </span>
		<? echo utf8_encode($row_empresa_dados['endereco1']); ?> - <?php echo utf8_encode($row_empresa_dados['bairro1']); ?> - 
        CEP: <?php echo $row_empresa_dados['cep1']; ?> | <?php echo utf8_encode($row_empresa_dados['cidade1']); ?> - <?php echo $row_empresa_dados['uf1']; ?>        
        </td>
	</tr>
</table>
</div>

<div class="div_solicitacao_linhas4">
<table cellspacing="0" cellpadding="0" width="100%">
	<tr>
		<td style="text-align:left">
        
        <span class="label_solicitacao">Tipo do contrato: </span>
		<?php if($row_prospeccao['tipo_cliente']=="a"){echo "Antigo";} ?>
        <?php if($row_prospeccao['tipo_cliente']=="n"){echo "Novo";} ?>
        </td>
        
		<td style="text-align:right">
        <span class="label_solicitacao">Contrato: </span>
		<?php echo $colname_contrato; ?><br>
		<span class="label_solicitacao">Data do contrato: </span>
        <? echo date('d-m-Y', strtotime($row_venda['data_contrato'])); ?>
        </td>
	</tr>
</table>
</div>

<div class="div_solicitacao_linhas3">
<table cellspacing="0" cellpadding="0" width="100%">
	<tr>
		<td style="text-align:left; vertical-align: top">
        <span class="label_solicitacao">Valor da venda do software: </span>R$ <? echo number_format($row_venda['valor_venda'], 2, ',', '.'); ?>
		</td>
        
        <td style="text-align: right" width="350">

        <span class="label_solicitacao">Valor da venda do treinamento: </span>R$ <? echo number_format($row_venda['valor_treinamento'], 2, ',', '.'); ?>
        </td>
	</tr>
</table>
</div>

<div class="div_solicitacao_linhas4">
<table cellspacing="0" cellpadding="0" width="100%">
	<tr>
		<td style="text-align:left; vertical-align: top">
        <span class="label_solicitacao">Módulos: </span>
        <? $contador_venda_modulos = 0; ?>
        <? do { ?>

        	<? $contador_venda_modulos = $contador_venda_modulos + 1; ?>
        	<? echo $row_venda_modulos['modulo']; ?><? if($contador_venda_modulos < $totalRows_venda_modulos){ ?>, <? } ?>
            
        <?php } while ($row_venda_modulos = mysql_fetch_assoc($venda_modulos)); ?>
		</td>
	</tr>
</table>
</div>

<div class="div_solicitacao_linhas3">
<table cellspacing="0" cellpadding="0" width="100%">
	<tr>
		<td style="text-align:left; vertical-align: top">
        <span class="label_solicitacao">Implantação: </span>
		<?php 
		$contador_implantacao_segundo = $row_venda['implantacao_tempo']*60;

		$tHoras = $contador_implantacao_segundo / 3600;
		$tMinutos = $contador_implantacao_segundo % 3600 / 60;
		
		echo sprintf('%02dh %02dm', $tHoras, $tMinutos);
        ?>
        </td>
        
        <td style="text-align: right">
		<span class="label_solicitacao">Treinamento: </span>
		<?php 
		$contador_treinamento_segundo = $row_venda['treinamento_tempo']*60;

		$tHoras = $contador_treinamento_segundo / 3600;
		$tMinutos = $contador_treinamento_segundo % 3600 / 60;
		
		echo sprintf('%02dh %02dm', $tHoras, $tMinutos);
        ?>
        </td>
	</tr>
</table>
</div>

<div class="div_solicitacao_linhas4">
<table cellspacing="0" cellpadding="0" width="100%">
  <tr>
    <td style="text-align: left">
	<span class="label_solicitacao">Parecer: </span>
	<br>
	<?php echo $row_prospeccao['parecer']; ?>
	</td>
  </tr>
</table>
</div>

<div class="div_solicitacao_linhas3">
<table cellspacing="0" cellpadding="0" width="100%">
	<tr>
		<td style="text-align:left; vertical-align: top">
        <span class="label_solicitacao">Usuário responsável: </span><? echo $row_venda['usuario_responsavel']; ?>
        </td>
	</tr>
</table>
</div>

<div class="div_solicitacao_linhas4" id="botoes">
<table cellspacing="0" cellpadding="0" width="100%">
  <tr>
    <td style="text-align: left;">
    
    <a href="#" id="imprimir" class="botao_geral" style="width: 150px;">Imprimir</a> 
    
    <?php if($row_venda['espelho'] == 1){ ?>
    <a href="venda_editar.php?id_venda=<?php echo $row_venda['id']; ?>" class="botao_geral" style="width: 150px;">Voltar para a venda</a>  
    <? } ?>

    <a href="venda.php?padrao=sim&<? echo $venda_padrao; ?>" class="botao_geral" style="width: 100px;">Sair</a> 
    </td>
  </tr>
</table>
</div>


</body>
</html>
<?php
mysql_free_result($usuario);
mysql_free_result($prospeccao);
mysql_free_result($venda);
mysql_free_result($empresa_dados);
mysql_free_result($manutencao_dados);
mysql_free_result($descricao);
mysql_free_result($arquivos_anexos);
mysql_free_result($venda_modulos);
?>