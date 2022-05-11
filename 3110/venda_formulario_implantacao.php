<? session_start(); ?>
<?php require_once('restrito.php'); ?>
<?php require_once('Connections/conexao.php'); ?>
<?php
if (!function_exists("GetSQLValueString")) {
function GetSQLValueString($theValue, $theType, $theDefinedValue = "", $theNotDefinedValue = "") 
{
  if (PHP_VERSION < 6) {
    $theValue = get_magic_quotes_gpc() ? stripslashes($theValue) : $theValue;
  }

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
      $theValue = ($theValue != "") ? doubleval($theValue) : "NULL";
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

require_once('parametros.php');

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

// venda
$colname_venda = "-1";
if (isset($_GET['id_venda'])) {
  $colname_venda = $_GET['id_venda'];
}
mysql_select_db($database_conexao, $conexao);
$query_venda = sprintf("
SELECT * 
FROM venda 
WHERE id = %s", GetSQLValueString($colname_venda, "int"));
$venda = mysql_query($query_venda, $conexao) or die(mysql_error());
$row_venda = mysql_fetch_assoc($venda);
$totalRows_venda = mysql_num_rows($venda);
// fim - venda

// $colname_contrato
$colname_contrato = "-1";
if (isset($_GET["contrato"])) {
  $colname_contrato = $_GET["contrato"];
}
// fim - $colname_contrato

// manutencao_dados
mysql_select_db($database_conexao, $conexao);
$query_manutencao_dados = sprintf("
SELECT * FROM da37 WHERE da37.codigo17 = %s and da37.sr_deleted <> 'T'", GetSQLValueString($colname_contrato, "text"));
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

// implantacao_pergunta
mysql_select_db($database_conexao, $conexao);
$query_implantacao_pergunta = "SELECT * FROM implantacao_pergunta ORDER BY IdImplantacaoPergunta ASC";
$implantacao_pergunta = mysql_query($query_implantacao_pergunta, $conexao) or die(mysql_error());
$row_implantacao_pergunta = mysql_fetch_assoc($implantacao_pergunta);
$totalRows_implantacao_pergunta = mysql_num_rows($implantacao_pergunta);
// fim - implantacao_pergunta

?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title></title>
<style>
body {
	font-family: Verdana, Geneva, sans-serif;
	font-size: 10px;
}
table.bordasimples {
	border-collapse: collapse;
	font-size: 10px;

}table.bordatransparente {
	border-collapse: inherit;
	font-size: 10px;
}
table.bordasimples tr td {
	border:1px solid #000;
	font-family: Verdana, Geneva, sans-serif;
	padding-left: 3px;
	padding-right: 3px;
	padding-top: 1px;
	padding-bottom: 1px;
	vertical-align: top;
	line-height: 1;
}
.titulo_formulario {
	font-family: Verdana, Geneva, sans-serif;
	font-size: 12px;
	padding: 3px;
}
.caixa_texto{
	margin-top: 5px; 
	margin-bottom: 5px; 
	text-align:justify; 
	font-size: 8px;
	line-height: 1;
}
.caixa_observacao{
	padding-top: 2px;
	text-align:justify; 
	font-size: 8px;
	line-height: 1.2;
}
</style>
</head>

<body>

<table width="100%" height="20" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td class="titulo_formulario" style="font-weight: bold">
      AVALIAÇÃO DE DESEMPENHO DA IMPLANTAÇÃO DO SISTEMA (<? echo $_GET['id_venda']; ?>)
    </td>
  </tr>
</table>

<table width="100%" border="0" cellspacing="0" cellpadding="0">
<tr>
    
        <td width="50%" align="left">

        <table width="100%" border="0" cellspacing="0" cellpadding="0" class="bordasimples">
        <tr>
        
        <td>
        Cliente:
        <div><?php echo utf8_encode($row_empresa_dados['nome1']); ?></div>
        </td>
        
        <td>
        CPF / CNPJ:
        <div><?php echo $row_empresa_dados['cgc1']; ?></div>
        </td>
        
        <td>
        ID / INSC. EST.:
        <div><?php echo $row_empresa_dados['insc1']; ?></div>
        </td>
        
        </tr>
        </table>
        
        <table width="100%" border="0" cellspacing="0" cellpadding="0" class="bordasimples">
        <tr>      
        <td>
        Responsável:
        <br><br>
        <div></div>
        </td>        
        <td width="20%">
        Função:
        <div></div>
        </td>
        <td width="20%">
        Data:
        <div></div>
        </td>
        <td width="20%">
        Valor do serviço:
        <div></div>
        </td>
        </tr>
        </table>
        
    </td>
        
  </tr>
</table>

<div class="caixa_texto">
<strong>Prezado Cliente</strong>,
<br>
É um prazer para a Success Sistemas e Representantes tê-lo como nosso cliente. Pensando nisso, dirigimos a você, após 60 dias da assinatura do contrato, para saber da sua satisfação quanto ao atendimento e a qualidade do sistema. Nas perguntas abaixo, quando houver afirmação negativa, será muito importante declarar detalhadamente a ocorrência em sua defesa. Este documento é uma prova do seu posicionamento, ao passo que na falta de detalhamento das respostas negativas mostrará uma condição contrária, podendo a Success Sistema interpretar da forma que desejar. Solicitamos ainda o seu apóio em relação aos comunicados fiscal, principalmente quando houver troca de aplicativo ou versão fiscal:

</div>

<table cellspacing=0 cellpadding=0 width="100%" class="bordasimples" style="margin-bottom: 5px;">
	<? $implantacao_pergunta_contador = 0; ?>
	<?php do { ?>
    <? $implantacao_pergunta_contador = $implantacao_pergunta_contador + 1; ?>
	<tr>
        <td width="70%" align="left" valign="top">

		<strong>
		<? echo $implantacao_pergunta_contador; ?> - <?php echo $row_implantacao_pergunta['descricao']; ?>
                
        <!-- implantacao_resposta -->
        <?
		// implantacao_resposta
		mysql_select_db($database_conexao, $conexao);
		$query_implantacao_resposta = sprintf("SELECT * FROM implantacao_resposta WHERE IdImplantacaoPergunta = %s", GetSQLValueString($row_implantacao_pergunta['IdImplantacaoPergunta'], "int"));
		$implantacao_resposta = mysql_query($query_implantacao_resposta, $conexao) or die(mysql_error());
		$row_implantacao_resposta = mysql_fetch_assoc($implantacao_resposta);
		$totalRows_implantacao_resposta = mysql_num_rows($implantacao_resposta);
		// fim - implantacao_resposta
		?>
        <?php do { ?>
        (&nbsp;&nbsp;) <?php echo $row_implantacao_resposta['descricao']; ?>
        <?php } while ($row_implantacao_resposta = mysql_fetch_assoc($implantacao_resposta)); ?>

        <? mysql_free_result($implantacao_resposta); ?>
        <!-- fim - implantacao_resposta -->

		<?php echo $row_implantacao_pergunta['campo_texto_label']; ?>
        
        </strong>
                
        <div class="caixa_observacao">
        OBS: <?php echo $row_implantacao_pergunta['observacao']; ?>
        </div>
        
        </td>
    </tr>
    <tr>
        <td align="center" valign="top" height="15"></td>  
	</tr>
    <?php } while ($row_implantacao_pergunta = mysql_fetch_assoc($implantacao_pergunta)); ?>
</table>


<div class="caixa_texto">
A Success Sistemas possui mais 11 anos na área de desenvolvimento de softwares comerciais, com mais de 500 cópias comercializadas em mais de 20 cidades de Minas Gerais, Distrito Federal e Goiás, tendo representantes em Unai, Brasilândia, João Pinheiro, Vazante, Cristalina e Araxá. Os nossos softwares estão com o TEF homologado e autorizado pelo DICAT.
</div>

<table cellspacing=0 cellpadding=0 width=100% class="bordasimples">
  <tr>
    <td width="33%" valign="top" align="left">
    Vendedor:
    <br><br>
    </td>
    <td width="34%" valign="top" align="left">
    Implantador:
    <br><br>
    </td>
    <td width="33%" valign="top" align="left">
    Região:
    <br><br>
    </td>
  </tr>
</table>

<table cellspacing=0 cellpadding=0 width='100%' class="bordatransparente">
  <tr>
    <td width="33%" align="center" valign="top">
    <br>
    ____________________________
    <br>
    CLIENTE
    </td>
    <td width="34%" align="center" valign="top">
    <br>
    ____________________________
    <br>
    ATENDENTE
    </td>
    <td width="33%" align="center" valign="top">
    <br>
    ____________________________
    <br>
    SUCCESS
    </td>
  </tr>
</table>

</body>
</html>
<?php
mysql_free_result($usuario);
mysql_free_result($venda);
mysql_free_result($manutencao_dados);
mysql_free_result($empresa_dados);
mysql_free_result($implantacao_pergunta);
?>