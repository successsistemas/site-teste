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
SELECT venda.*, 
(SELECT nome FROM usuarios WHERE venda.id_usuario_responsavel = usuarios.IdUsuario) AS usuario_responsavel
FROM venda 
WHERE venda.id = %s", 
GetSQLValueString($colname_venda, "int"));
$venda = mysql_query($query_venda, $conexao) or die(mysql_error());
$row_venda = mysql_fetch_assoc($venda);
$totalRows_venda = mysql_num_rows($venda);
// fim - venda

// prospeccao
mysql_select_db($database_conexao, $conexao);
$query_prospeccao = sprintf("
SELECT id, 
(SELECT nome FROM usuarios WHERE prospeccao.id_usuario_responsavel = usuarios.IdUsuario) AS usuario_responsavel
FROM prospeccao 
WHERE id = %s", GetSQLValueString($row_venda['id_prospeccao'], "int"));
$prospeccao = mysql_query($query_prospeccao, $conexao) or die(mysql_error());
$row_prospeccao = mysql_fetch_assoc($prospeccao);
$totalRows_prospeccao = mysql_num_rows($prospeccao);
// fim - prospeccao

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
	SELECT nome1, contato1, cgc1, insc1, endereco1, bairro1, cidade1, uf1, telefone1, comercio1, cep1, ultcompra1, atraso1, status1, flag1 
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
	uf AS uf1, telefone telefone1, celular AS comercio1, cep AS cep1, '' AS ultcompra1, '' AS atraso1, '' AS status1, '' AS flag1, '' AS contato1 
	FROM prospeccao 
	WHERE id = %s", 
	GetSQLValueString($row_venda['id_prospeccao'], "text"));
	$empresa_dados = mysql_query($query_empresa_dados, $conexao) or die(mysql_error());
	$row_empresa_dados = mysql_fetch_assoc($empresa_dados);
	$totalRows_empresa_dados = mysql_num_rows($empresa_dados);
	
}
// fim - empresa_dados ---------------------

// implantacao_avaliacao_pergunta
mysql_select_db($database_conexao, $conexao);
$query_implantacao_avaliacao_pergunta = "SELECT * FROM implantacao_avaliacao_pergunta ORDER BY IdImplantacaoPergunta ASC";
$implantacao_avaliacao_pergunta = mysql_query($query_implantacao_avaliacao_pergunta, $conexao) or die(mysql_error());
$row_implantacao_avaliacao_pergunta = mysql_fetch_assoc($implantacao_avaliacao_pergunta);
$totalRows_implantacao_avaliacao_pergunta = mysql_num_rows($implantacao_avaliacao_pergunta);
// fim - implantacao_avaliacao_pergunta

// venda_modulos
mysql_select_db($database_conexao, $conexao);
$query_venda_modulos = sprintf("
SELECT geral_tipo_modulo.descricao AS modulo 
FROM venda_modulos 
LEFT JOIN geral_tipo_modulo ON venda_modulos.id_modulo = geral_tipo_modulo.IdTipoModulo 
WHERE venda_modulos.id_venda = %s", 
GetSQLValueString($_GET['id_venda'], "int"));
$venda_modulos = mysql_query($query_venda_modulos, $conexao) or die(mysql_error());
$row_venda_modulos = mysql_fetch_assoc($venda_modulos);
$totalRows_venda_modulos = mysql_num_rows($venda_modulos);
// fim - venda_modulos

// venda_agenda_treinamento
mysql_select_db($database_conexao, $conexao);
$query_venda_agenda_treinamento =  sprintf("
									   SELECT agenda.id_usuario_responsavel, agenda.data_inicio, agenda.data, agenda.venda_receptor,
									   usuarios.status AS usuarios_status, usuarios.nome 
									   FROM agenda 
									   INNER JOIN usuarios ON agenda.id_usuario_responsavel = usuarios.IdUsuario 
									   WHERE id_venda_treinamento = %s and (agenda.status = 'a' or agenda.status = 'f')
									   ORDER BY agenda.id_agenda ASC", 
									   GetSQLValueString($row_venda['id'], "int"));
$venda_agenda_treinamento = mysql_query($query_venda_agenda_treinamento, $conexao) or die(mysql_error());
$row_venda_agenda_treinamento = mysql_fetch_assoc($venda_agenda_treinamento);
$totalRows_venda_agenda_treinamento = mysql_num_rows($venda_agenda_treinamento);
// fim - venda_agenda_treinamento

// venda_agenda_implantacao
mysql_select_db($database_conexao, $conexao);
$query_venda_agenda_implantacao =  sprintf("
									   SELECT agenda.id_usuario_responsavel, agenda.data_inicio, agenda.data, agenda.venda_receptor, 
									   usuarios.status AS usuarios_status, usuarios.nome 
									   FROM agenda 
									   INNER JOIN usuarios ON agenda.id_usuario_responsavel = usuarios.IdUsuario 
									   WHERE id_venda_implantacao = %s and (agenda.status = 'a' or agenda.status = 'f')
									   ORDER BY agenda.id_agenda ASC", 
									   GetSQLValueString($row_venda['id'], "int"));
$venda_agenda_implantacao = mysql_query($query_venda_agenda_implantacao, $conexao) or die(mysql_error());
$row_venda_agenda_implantacao = mysql_fetch_assoc($venda_agenda_implantacao);
$totalRows_venda_agenda_implantacao = mysql_num_rows($venda_agenda_implantacao);
// fim - venda_agenda_implantacao

$venda_validade_dias = $row_parametros['venda_validade_dias'] + $row_venda['dilacao_prazo'];
$validade = date('d-m-Y 23:59:59', strtotime("+$venda_validade_dias days",strtotime($row_venda['data_venda'])));
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
	line-height: 0.9;
}
.caixa_observacao{
	padding-top: 2px;
	text-align:justify; 
	font-size: 8px;
	line-height: 1;
}
</style>
</head>

<body>

<table width="100%" height="20" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td class="titulo_formulario" style="font-weight: bold">
      AVALIAÇÃO DE DESEMPENHO DA IMPLANTAÇÃO DO SISTEMA (Venda nº <? echo $_GET['id_venda']; ?>)
    </td>
  </tr>
</table>

<table width="100%" border="0" cellspacing="0" cellpadding="0">
<tr>   
        <td width="50%" align="left">

        <table width="100%" border="0" cellspacing="0" cellpadding="0" class="bordasimples">
        <tr>
        
        <td width="50%">
        Cliente:
        <div><?php echo utf8_encode($row_empresa_dados['nome1']); ?></div>
        </td>
        
        <td width="25%">
        CPF / CNPJ:
        <div><?php echo shellDescriptografa($row_empresa_dados['cgc1']); ?></div>
        </td>
        
        <td width="25%">
        ID / INSC. EST.:
        <div><?php echo shellDescriptografa($row_empresa_dados['insc1']); ?></div>
        </td>
        
        </tr>
        </table>

        <table width="100%" border="0" cellspacing="0" cellpadding="0" class="bordasimples">
        <tr>   
           
        <td width="50%">
        Representante Legal: 
        <br>
		<?php echo utf8_encode($row_empresa_dados['contato1']); ?>
        </td> 

        <td width="25%">
		Resp. pelo Sistema:
        <br>
        <br>
        </td>
                       
        <td width="25%">
        Função:
        <br><br>
        </td>
                
        </tr>
        </table>
        
        <table width="100%" border="0" cellspacing="0" cellpadding="0" class="bordasimples">
        <tr>      

        <td width="25%">
        Representante Comercial:
        <br>
        <?php echo $row_prospeccao['usuario_responsavel']; ?>
        </td>        
        
        <td width="25%">
        Implantador:
        <br>
        <?php echo $row_venda['usuario_responsavel']; ?>
        </td>        
        
        <td width="25%">
          Data:
          <br>
          <? if($row_venda_agenda_implantacao['data']!=""){echo date('d-m-Y', strtotime($row_venda_agenda_implantacao['data']));} ?>
        </td>
        
        <td width="25%">
          Valor do software:
          <br>
          <? if($row_venda['valor_venda']!=""){ ?>
          R$ <? echo number_format($row_venda['valor_venda'], 2, ',', '.'); ?>
          <? } else { ?>
          R$ 0,00
          <? } ?>
        </td>
        
        </tr>
        </table>
        
        <table width="100%" border="0" cellspacing="0" cellpadding="0" class="bordasimples">
        <tr>

        <td width="25%">
        Região: 
        <br>
		<?php echo $row_venda['praca']; ?>
        </td>
        
        <td>
        Módulos:
        <br>
        <? $contador_venda_modulos = 0; ?>
        <? do { ?>

        	<? $contador_venda_modulos = $contador_venda_modulos + 1; ?>
        	<? echo $row_venda_modulos['modulo']; ?><? if($contador_venda_modulos < $totalRows_venda_modulos){ ?>, <? } ?>
            
        <?php } while ($row_venda_modulos = mysql_fetch_assoc($venda_modulos)); ?>
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
	<? $implantacao_avaliacao_pergunta_contador = 0; ?>
	<?php do { ?>
    <? $implantacao_avaliacao_pergunta_contador = $implantacao_avaliacao_pergunta_contador + 1; ?>
	<tr>

        <td width="70%" align="left" valign="top">

		<strong>
		<? echo $implantacao_avaliacao_pergunta_contador; ?> - <?php echo $row_implantacao_avaliacao_pergunta['descricao']; ?>
                
        <!-- implantacao_avaliacao_resposta -->
        <?
		// implantacao_avaliacao_resposta
		mysql_select_db($database_conexao, $conexao);
		$query_implantacao_avaliacao_resposta = sprintf("SELECT * FROM implantacao_avaliacao_resposta WHERE IdImplantacaoPergunta = %s", GetSQLValueString($row_implantacao_avaliacao_pergunta['IdImplantacaoPergunta'], "int"));
		$implantacao_avaliacao_resposta = mysql_query($query_implantacao_avaliacao_resposta, $conexao) or die(mysql_error());
		$row_implantacao_avaliacao_resposta = mysql_fetch_assoc($implantacao_avaliacao_resposta);
		$totalRows_implantacao_avaliacao_resposta = mysql_num_rows($implantacao_avaliacao_resposta);
		// fim - implantacao_avaliacao_resposta
		?>
        <?php do { ?>
        (&nbsp;&nbsp;) <?php echo $row_implantacao_avaliacao_resposta['descricao']; ?>
        <?php } while ($row_implantacao_avaliacao_resposta = mysql_fetch_assoc($implantacao_avaliacao_resposta)); ?>

        <? mysql_free_result($implantacao_avaliacao_resposta); ?>
        <!-- fim - implantacao_avaliacao_resposta -->

		<?php echo $row_implantacao_avaliacao_pergunta['campo_texto_label']; ?>
        
        </strong>
                
        <div class="caixa_observacao">
        OBS: <?php echo $row_implantacao_avaliacao_pergunta['observacao']; ?>
        </div>
        
        </td>
    </tr>
    <tr>
        <td align="center" valign="top" height="15"></td>  
	</tr>
    <?php } while ($row_implantacao_avaliacao_pergunta = mysql_fetch_assoc($implantacao_avaliacao_pergunta)); ?>
</table>


<div class="caixa_texto">
A Success Sistemas possui mais 11 anos na área de desenvolvimento de softwares comerciais, com mais de 500 cópias comercializadas em mais de 20 cidades de Minas Gerais, Distrito Federal e Goiás, tendo representantes em Unai, Brasilândia, João Pinheiro, Vazante, Cristalina e Araxá. Os nossos softwares estão com o TEF homologado e autorizado pelo DICAT.
</div>

<table cellspacing=0 cellpadding=0 width='100%' class="bordatransparente">
  <tr>
  
    <td width="25%" align="center" valign="top">
    <br>
    _______________________
    <br>
    Responsável pelo sistema
    </td>
    
    <td width="25%" align="center" valign="top">
    <br>
    _______________________
    <br>
    Representante legal
    </td>
    
    <td width="25%" align="center" valign="top">
    <br>
    _______________________
    <br>
    Implantador
    </td>
    
    <td width="25%" align="center" valign="top">
    <br>
    _______________________
    <br>
    Success Sistemas & Inf. Ltda
    </td>
    
  </tr>
</table>

</body>
</html>
<?php
mysql_free_result($usuario);
mysql_free_result($venda);
mysql_free_result($prospeccao);
mysql_free_result($manutencao_dados);
mysql_free_result($empresa_dados);
mysql_free_result($implantacao_avaliacao_pergunta);
mysql_free_result($venda_modulos);
mysql_free_result($venda_agenda_treinamento);
mysql_free_result($venda_agenda_implantacao);
?>