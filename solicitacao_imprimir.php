<? session_start(); ?>
<?php require('restrito.php'); ?>
<?php require_once('Connections/conexao.php'); ?>
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


// solicitação
$colname_solicitacao = "-1";
if (isset($_GET['id_solicitacao'])) {
  $colname_solicitacao = $_GET['id_solicitacao'];
}
mysql_select_db($database_conexao, $conexao);
$query_solicitacao = sprintf("
SELECT solicitacao.*, 
(SELECT nome FROM usuarios WHERE usuarios.IdUsuario = solicitacao.id_usuario_responsavel) as usuario_responsavel, 
(SELECT nome FROM usuarios WHERE usuarios.IdUsuario = solicitacao.id_operador) as nome_operador, 
(SELECT nome FROM usuarios WHERE usuarios.IdUsuario = solicitacao.id_executante) as nome_executante, 
(SELECT nome FROM usuarios WHERE usuarios.IdUsuario = solicitacao.id_analista_orcamento) as nome_analista_orcamento, 
(SELECT nome FROM usuarios WHERE usuarios.IdUsuario = solicitacao.id_testador) as nome_testador 
FROM solicitacao 
WHERE id = %s", 
GetSQLValueString($colname_solicitacao, "int"));
$solicitacao = mysql_query($query_solicitacao, $conexao) or die(mysql_error());
$row_solicitacao = mysql_fetch_assoc($solicitacao);
$totalRows_solicitacao = mysql_num_rows($solicitacao);
// fim - solicitação



// programa
$colname_geral_tipo_programa = "-1";
if (isset($row_solicitacao['id_programa'])) {
  $colname_geral_tipo_programa = $row_solicitacao['id_programa'];
}
mysql_select_db($database_conexao, $conexao);
$query_geral_tipo_programa = sprintf("SELECT * FROM geral_tipo_programa WHERE id_programa = %s", GetSQLValueString($colname_geral_tipo_programa, "int"));
$geral_tipo_programa = mysql_query($query_geral_tipo_programa, $conexao) or die(mysql_error());
$row_geral_tipo_programa = mysql_fetch_assoc($geral_tipo_programa);
$totalRows_geral_tipo_programa = mysql_num_rows($geral_tipo_programa);

// subprograma
$colname_geral_tipo_subprograma = "-1";
if (isset($row_solicitacao['id_subprograma'])) {
  $colname_geral_tipo_subprograma = $row_solicitacao['id_subprograma'];
}
mysql_select_db($database_conexao, $conexao);
$query_geral_tipo_subprograma = sprintf("SELECT * FROM geral_tipo_subprograma WHERE id_subprograma = %s", GetSQLValueString($colname_geral_tipo_subprograma, "int"));
$geral_tipo_subprograma = mysql_query($query_geral_tipo_subprograma, $conexao) or die(mysql_error());
$row_geral_tipo_subprograma = mysql_fetch_assoc($geral_tipo_subprograma);
$totalRows_geral_tipo_subprograma = mysql_num_rows($geral_tipo_subprograma);

// solicitacao_tipo_parecer
mysql_select_db($database_conexao, $conexao);
$query_solicitacao_tipo_parecer = "SELECT * FROM solicitacao_tipo_parecer ORDER BY titulo ASC";
$solicitacao_tipo_parecer = mysql_query($query_solicitacao_tipo_parecer, $conexao) or die(mysql_error());
$row_solicitacao_tipo_parecer = mysql_fetch_assoc($solicitacao_tipo_parecer);
$totalRows_solicitacao_tipo_parecer = mysql_num_rows($solicitacao_tipo_parecer);

// descrições
$colname_descricao = "-1";
if (isset($_GET['id_solicitacao'])) {
  $colname_descricao = $_GET['id_solicitacao'];
}
mysql_select_db($database_conexao, $conexao);
$query_descricao = sprintf("
SELECT *, 
(SELECT nome FROM usuarios WHERE usuarios.IdUsuario = solicitacao_descricoes.id_usuario_responsavel) as usuario_responsavel 
FROM solicitacao_descricoes 
WHERE id_solicitacao = %s 
ORDER BY id DESC", 
GetSQLValueString($colname_descricao, "text"));
$descricao = mysql_query($query_descricao, $conexao) or die(mysql_error());
$row_descricao = mysql_fetch_assoc($descricao);
$totalRows_descricao = mysql_num_rows($descricao);

// arquivos
$colname_arquivos_anexos = "-1";
if (isset($_GET['id_solicitacao'])) {
  $colname_arquivos_anexos = $_GET['id_solicitacao'];
}
mysql_select_db($database_conexao, $conexao);
$query_arquivos_anexos = sprintf("SELECT * FROM solicitacao_arquivos WHERE id_solicitacao = %s", GetSQLValueString($colname_arquivos_anexos, "int"));
$arquivos_anexos = mysql_query($query_arquivos_anexos, $conexao) or die(mysql_error());
$row_arquivos_anexos = mysql_fetch_assoc($arquivos_anexos);
$totalRows_arquivos_anexos = mysql_num_rows($arquivos_anexos);

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"> 
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<script type="text/javascript" src="js/jquery.js"></script>


<script type="text/javascript" src="js/thickbox.js"></script>
<link rel="stylesheet" href="css/thickbox.css" type="text/css" media="screen" />

<title></title>
<style>
@page { margin-bottom: 2cm; margin-top: 2cm; }
body {
	font-family: Lucida Grande, Lucida Sans, Arial, sans-serif; 
	font-size: 11px;
	margin: 0px;
	color: #000000;
	overflow-y: scroll;
}
table {
	width: 100%;
}
td {
	font-family: Lucida Grande, Lucida Sans, Arial, sans-serif; 
	font-size: 11px;

	padding-left: 5px;
	padding-right: 5px;
	padding-top: 2px;
	padding-bottom: 2px;

	vertical-align: top;
}
form {
margin: 0 0 0 0;
padding: 0 0 0 0;
}
input {
	font-family: Lucida Grande, Lucida Sans, Arial, sans-serif; 
	font-size: 11px;
	color: #666666;
	text-decoration: none;
	border: 1px solid #CCCCCC;
}
select {
	font-family: Lucida Grande, Lucida Sans, Arial, sans-serif; 
	font-size: 11px;
	color: #666666;
	text-decoration: none;
	border: 1px solid #CCCCCC;
}
.textarea_medida {
	font-family: Verdana; 
	font-size: 11px;
	color: #666666;
	text-decoration: none;
	border: 1px solid #CCCCCC;
}
.div_solicitacao_linhas {

	margin-top: 4px;
	margin-left: auto;
	margin-right: auto;
	width: 98%;
}

.div_solicitacao_linhas3 {

	margin-top: 4px;
	margin-left: auto;
	margin-right: auto;
	width: 98%;
	
	border: 1px solid #c5dbec; 
	background: #dfeffc url(imagens/jqgrid/ui-bg_glass_85_dfeffc_1x400.png) 50% 50% repeat-x; 
	color: #000000;
}
.label_solicitacao {
	color: #2e6e9e;
	font-weight: bold;
}
.label_solicitacao2 {
	color: #FFFFFF;
}
</style>
<link rel="stylesheet" href="css/solicitacao.css" type="text/css" media="screen" />
<!--[if lte IE 7]>

<style>
body{
	overflow-y: hidden;
}
</style>

<![endif]-->
</head>

<body>
<form action="<?php echo $editFormAction; ?>" method="POST" name="solicitacao" id="solicitacao" >

<div class="div_solicitacao_linhas2">
<table cellspacing="0" cellpadding="0">
	<tr>
		<td style="text-align: left">
		<span class="label_solicitacao2">Solicitação número: </span><?php echo $row_solicitacao['id']; ?>
		</td>
        
		<td style="text-align: right">
		&lt;&lt; <a style="text-decoration: none; color:#FFFFFF;" href="solicitacao.php?padrao=sim&<? echo $situacao_padrao; ?>">
		Voltar
		</a> | 
		Usuário logado: <? echo $row_usuario['nome']; ?> |
		<a style="text-decoration: none; color:#FFFFFF;" href="sair.php">Sair</a>
        </td>
	</tr>
</table>
</div>

<div class="div_solicitacao_linhas3">
<table cellspacing="0" cellpadding="0">
	<tr>
		<td style="text-align: left">
		<span class="label_solicitacao">Título: </span><?php echo $row_solicitacao['titulo']; ?>
		</td>

		<td style="text-align: right" width=" 200">
		<span class="label_solicitacao">Criação: </span><? echo date('d-m-Y  H:i', strtotime($row_solicitacao['dt_solicitacao'])); ?>
		<br>
		<span class="label_solicitacao">Núm. Controle Suporte:</span> <?php echo $row_solicitacao['protocolo_suporte']; ?>
		</td>
	</tr>
</table>
</div>

<div class="div_solicitacao_linhas">
<table cellspacing="0" cellpadding="0">
	<tr>
		<td style="text-align:left">
		<span class="label_solicitacao">Empresa:</span> <?php echo $row_solicitacao['empresa']; ?> | 
		<span class="label_solicitacao">Contrato:</span> <?php echo $row_solicitacao['contrato']; ?> | 
		<span class="label_solicitacao">Praça:</span> <?php echo $row_solicitacao['praca']; ?>
		</td>

		<td style="text-align: right">
		<span class="label_solicitacao">Versão: </span><?php echo $row_solicitacao['versao']; ?> | 
		<span class="label_solicitacao">Distribuição: </span><?php echo $row_solicitacao['geral_tipo_distribuicao']; ?>
		</td>
	</tr>
</table>
</div>

<div class="div_solicitacao_linhas3">
<table cellspacing="0" cellpadding="0">
	<tr>
		<td style="text-align:left">
		<span class="label_solicitacao">Programa: </span><?php echo $row_geral_tipo_programa['programa']; ?> | 
		<span class="label_solicitacao">Subprograma: </span><?php echo $row_geral_tipo_subprograma['subprograma']; ?>
		</td>

		<td style="text-align: right">
		<span class="label_solicitacao">Campo: </span><?php echo $row_solicitacao['campo']; ?>
		</td>

	</tr>
</table>
</div>

<div class="div_solicitacao_linhas">
<table cellspacing="0" cellpadding="0">
	<tr>

		<td style="text-align: left;">
		<span class="label_solicitacao">Data executável: </span><?php echo implode("-",array_reverse(explode("-",$row_solicitacao['data_executavel']))); ?> | 
		<span class="label_solicitacao">Hora executável: </span><?php echo $row_solicitacao['hora_executavel']; ?>
		</td>

		<td style="text-align: right">
		<span class="label_solicitacao">Banco de dados:</span>	<?php echo $row_solicitacao['tipo_bd']; ?> | 
		<span class="label_solicitacao">ECF:</span> <?php echo $row_solicitacao['geral_tipo_ecf']; ?>
		</td>

	</tr>
</table>
</div>

<div class="div_solicitacao_linhas3">
<table cellspacing="0" cellpadding="0">
	<tr>

		<td style="text-align: left">
        <span class="label_solicitacao">Tipo: </span><?php echo $row_solicitacao['tipo']; ?> | 
        <span class="label_solicitacao">Prioridade: </span><?php echo $row_solicitacao['prioridade']; ?>
		</td>

		<td style="text-align: right">
		<span class="label_solicitacao">Situação: </span><?php echo $row_solicitacao['situacao']; ?> | 
		<span class="label_solicitacao">Status: </span><?php echo $row_solicitacao['status']; ?>
		</td>

	</tr>
</table>
</div>

<div class="div_solicitacao_linhas">
<table cellspacing="0" cellpadding="0">
  <tr>

    <td style="padding: 5px; border: 1px solid #c5dbec">
<span class="label_solicitacao">Andamento: </span> criada

<!-- recebida -->
<? if($row_solicitacao['dt_recebimento']!="") { ?>
>> recebida
<? } ?>
<!-- fim - recebida -->

<!-- em analise -->
<? if($row_solicitacao['situacao']=="em análise") { ?>
>> em análise ( Previsão da análise:
<? 
	// previsao - inicio
	if ( isset($row_solicitacao['previsao_analise_inicio']) ) {
		echo date('d-m-Y  H:i', strtotime($row_solicitacao['previsao_analise_inicio']));
	}
	// previsao - inicio
?> à 
<? 
	// previsao
	if ( isset($row_solicitacao['previsao_analise']) ) {
		echo date('d-m-Y  H:i', strtotime($row_solicitacao['previsao_analise']));
	}
	// previsao
?> )
<? } ?>
<!-- fim - em analise -->

<!-- analisada -->
<? if($row_solicitacao['dt_aprovacao_reprovacao']!="") { ?>
>> analisada
<? } ?>
<!-- fim - analisada -->

<!-- reprovada -->
<? if($row_solicitacao['situacao']=="reprovada") { ?>
>> reprovada
<? } ?>
<!-- fim - reprovada -->

<!-- analisada -->
<? if($row_solicitacao['situacao']=="analisada") { ?>
>> analisada
	<? if($row_solicitacao['previsao_analise_orcamento']=="0000-00-00 00:00:00" and $row_solicitacao['id_analista_orcamento']!="") { ?>
    >> Aguardando aceitação do analista de orçamento
    <? } ?>
    
    <? if($row_solicitacao['previsao_analise_orcamento']=="0000-00-00 00:00:00" and $row_solicitacao['id_analista_orcamento']=="") { ?>
    >> Aguardando escolha de analista de orçamento
    <? } ?>
<? } ?>
<!-- fim - analisada -->

<!-- em orçamento -->
<? if($row_solicitacao['situacao']=="em orçamento") { ?>
        >> analisada 
        >> em orçamento
        <? if($row_solicitacao['dt_orcamento']=="") { // se não existe orçamento  ?>
        ( Previsão:
		<? 
            // previsao - inicio
            if ( isset($row_solicitacao['previsao_analise_orcamento_inicio']) ) {
                echo date('d-m-Y  H:i', strtotime($row_solicitacao['previsao_analise_orcamento_inicio']));
            }
            // previsao - inicio
        ?> à 
        <? 
            // previsao
            if ( isset($row_solicitacao['previsao_analise_orcamento']) ) {
                echo date('d-m-Y  H:i', strtotime($row_solicitacao['previsao_analise_orcamento']));
            }
            // previsao
        ?> )
        <? } // fim - se não existe orçamento 
        else {
        // se existe orçamento
        ?>
        >> Aguardando aceitação/reprovação do solicitante
        <? }
        // fim - se existe orçamento ?>
<? } ?>
<!-- fim - em orçamento -->

<!-- aprovada -->
<? if($row_solicitacao['situacao']=="aprovada") { ?>
        >> aprovada
        <? if($row_solicitacao['previsao_solucao']=="0000-00-00 00:00:00" and $row_solicitacao['id_executante']!="") { ?>
        >> Aguardando aceitação do executante
        <? } ?>

		<? if($row_solicitacao['previsao_solucao']=="0000-00-00 00:00:00" and $row_solicitacao['id_executante']=="") { ?>
        >> Aguardando escolha de executante
        <? } ?>
<? } ?>
<!-- fim - aprovada -->

<!-- em execução -->
<? if($row_solicitacao['situacao']=="em execução") { ?>
        >> aprovada
        >> em execução ( Previsão:
		<? 
            // previsao - inicio
            if ( isset($row_solicitacao['previsao_solucao_inicio']) ) {
                echo date('d-m-Y  H:i', strtotime($row_solicitacao['previsao_solucao_inicio']));
            }
            // previsao - inicio
        ?> à 
        <? 
            // previsao
            if ( isset($row_solicitacao['previsao_solucao']) ) {
                echo date('d-m-Y  H:i', strtotime($row_solicitacao['previsao_solucao']));
            }
            // previsao
        ?> )
<? } ?>
<!-- fim - em execução -->

<!-- executada -->
<? if($row_solicitacao['situacao']=="executada") { ?>
        >> aprovada
        >> executada
        
        <? if($row_solicitacao['previsao_testes']=="0000-00-00 00:00:00" and $row_solicitacao['id_testador']!="") { ?>
        >> Aguardando aceitação do testador
        <? } ?>
        
        <? if($row_solicitacao['previsao_testes']=="0000-00-00 00:00:00" and $row_solicitacao['id_testador']=="") { ?>
        >> Aguardando escolha de testador
        <? } ?>

<? } ?>
<!-- fim - executada -->

<!-- em testes -->
<? if($row_solicitacao['situacao']=="em testes") { ?>
        >> aprovada
        >> executada
        >> em testes ( Previsão:
		<? 
            // previsao - inicio
            if ( isset($row_solicitacao['previsao_testes_inicio']) ) {
                echo date('d-m-Y  H:i', strtotime($row_solicitacao['previsao_testes_inicio']));
            }
            // previsao - inicio
        ?> à 
        <? 
            // previsao
            if ( isset($row_solicitacao['previsao_testes']) ) {
                echo date('d-m-Y  H:i', strtotime($row_solicitacao['previsao_testes']));
            }
            // previsao
        ?> )
<? } ?>
<!-- fim - em testes -->

<!-- testada -->
<? if($row_solicitacao['dt_conclusao_testes']!="") { ?>
        >> aprovada
        >> executada
        >> testada
<? } ?>
<!-- fim - testada -->

<!-- em validação -->
<? if($row_solicitacao['situacao']=="em validação") { ?>
		<? if($row_solicitacao['testador_leu']=="") { ?>
        >> aprovada
        <? } ?>
>> em validacao ( Previsão:
<? 
	// previsao - inicio
	if ( isset($row_solicitacao['previsao_validacao_inicio']) ) {
		echo date('d-m-Y  H:i', strtotime($row_solicitacao['previsao_validacao_inicio']));
	}
	// previsao - inicio
?> à 
<? 
	// previsao
	if ( isset($row_solicitacao['previsao_validacao']) ) {
		echo date('d-m-Y  H:i', strtotime($row_solicitacao['previsao_validacao']));
	}
	// previsao
?> )
<? } ?>
<!-- fim - em validação -->

<!-- validada - concluída -->
<? if($row_solicitacao['dt_validacao']!="") { ?>
		<? if($row_solicitacao['testador_leu']=="") { ?>
        >> aprovada
        <? } ?>
        >> validada
<? } ?>
<!-- fim - validada - concluída -->

<!-- solucionada -->
<? if($row_solicitacao['situacao']=="solucionada") { ?>
		>> solucionada
<? } ?>
<!-- fim - solucionada -->

<!-- Questionar -->

</span>
	</td>

  </tr>
</table>
</div>

<div class="div_solicitacao_linhas">
<table cellspacing="0" cellpadding="0">
  <tr>

    <td width="200">

	<? if($row_solicitacao['situacao']!="solucionada" and $row_solicitacao['situacao']!="reprovada"){ ?>

            <!-- solicitante leu em -->
			<? echo $row_solicitacao['usuario_responsavel']; ?>
			<br>
            <span class="label_solicitacao">Solicitante leu em:</span>
            <br>       
            <? if($row_solicitacao['solicitante_leu']!=""){ echo date('d-m-Y - H:i:s', strtotime($row_solicitacao['solicitante_leu'])); } else { echo "não leu"; } ?>
            <!-- fim - solicitante leu em -->

            <!-- operador leu em -->
			<? if($row_solicitacao['id_operador']!=""){ ?>
                <br><br>
                <? echo $row_solicitacao['nome_operador']; ?>
                <br>
                <span class="label_solicitacao">Operador leu em:</span>
                <br>        
                <? if($row_solicitacao['operador_leu']!=""){ echo date('d-m-Y - H:i:s', strtotime($row_solicitacao['operador_leu'])); } else { echo "não leu"; } ?>
			<? } ?>
            <!-- fim - operador leu em -->     

            <!-- analista de orçamento leu em -->
			<? if($row_solicitacao['id_analista_orcamento']!=""){ ?>
                <br><br>
				<? echo $row_solicitacao['nome_analista_orcamento']; ?><br>
                <span class="label_solicitacao">Analista de orçamento leu em:</span>
                <br>       
                <? if($row_solicitacao['analista_orcamento_leu']!=""){ echo date('d-m-Y - H:i:s', strtotime($row_solicitacao['analista_orcamento_leu'])); } else { echo "não leu"; } ?>
			<? } ?>
            <!-- fim - analista de orçamento leu em -->

            <!-- executante leu em -->
			<? if($row_solicitacao['id_executante']!=""){ ?>
                <br><br>
				<? echo $row_solicitacao['nome_executante']; ?>
                <span class="label_solicitacao">Executante leu em:</span>
                <br>       
                <? if($row_solicitacao['executante_leu']!=""){ echo date('d-m-Y - H:i:s', strtotime($row_solicitacao['executante_leu'])); } else { echo "não leu"; } ?>
			<? } ?>
            <!-- fim - executante leu em -->

            <!-- executante leu em -->
			<? if($row_solicitacao['id_testador']!=""){ ?>
                <br><br>
				<? echo $row_solicitacao['nome_testador']; ?>
				<br>
                <span class="label_solicitacao">Testador leu em:</span>
                <br>       
                <? if($row_solicitacao['testador_leu']!=""){ echo date('d-m-Y - H:i:s', strtotime($row_solicitacao['testador_leu'])); } else { echo "não leu"; } ?>
			<? } ?>
            <!-- fim - executante leu em -->

            <!-- duração -->
            <br><br>
            <span class="label_solicitacao">Duração:</span>
            <br>       
            <?	
            $data_ini = strtotime($row_solicitacao['dt_solicitacao']);
            $data_final = strtotime(date("Y-m-d H:i:s"));
            
            $nDias   = ($data_final - $data_ini) / (3600*24);  // dias
            $nHoras = (($data_final - $data_ini) % (3600*24)) / 3600; // horas
            $nMinutos = (	(($data_final - $data_ini) % (3600*24)) % 3600	) / 60; // minutos
            
             echo sprintf('%02dd %02dh %02dm', $nDias, $nHoras , $nMinutos);
            ?>
            <!-- fim - duração -->

    <? } ?>

	<? if($row_solicitacao['situacao']=="solucionada" or $row_solicitacao['situacao']=="reprovada"){ ?>

			<? if($row_solicitacao['solicitante_leu']!=""){ ?>
            <!-- solicitante leu em -->
			<? echo $row_solicitacao['usuario_responsavel']; ?>
			<br>
            <span class="label_solicitacao">Solicitante</span>
            <!-- fim - solicitante leu em -->
			<? } ?>

			<? if($row_solicitacao['operador_leu']!=""){ ?>    
            <!-- operador leu em -->
			<br><br>
			<? echo $row_solicitacao['nome_operador']; ?>
			<br>
            <span class="label_solicitacao">Operador</span>
            <!-- fim - operador leu em -->
			<? } ?>
        
			<? if($row_solicitacao['executante_leu']!=""){ ?>
            <!-- executante leu em -->
            <br><br>
            <? echo $row_solicitacao['nome_executante']; ?>
            <br>
            <span class="label_solicitacao">Executante</span>
            <!-- fim - executante leu em -->
			<? } ?>

			<? if($row_solicitacao['testador_leu']!=""){ ?>
            <!-- testador leu em -->
            <br><br>
            <? echo $row_solicitacao['nome_testador']; ?>
            <br>
            <span class="label_solicitacao">Testador</span>
            <!-- fim - testador leu em -->
			<? } ?>

    <? } ?>


	<? if($row_solicitacao['situacao']=="em análise" and $row_solicitacao['previsao_analise']<>$row_solicitacao['previsao_geral']){ ?>
		<br><br>
		<font color=red>
		Operador deseja alterar a data da previsão de análise da solicitação.
		</font> 
	<? } ?>


	</td>

  </tr>
</table>
</div>

<div class="div_solicitacao_linhas">
<table cellspacing="0" cellpadding="0">
  <tr>

    <td style="padding: 0px;">
<div style="border: 1px solid #c5dbec; padding: 5px; background-color:#FFFFFF;">
    <?php do { ?>
		<strong>
		<?
        $colname_descricao_usuario = "-1";
        if (isset($row_descricao['IdUsuario'])) {
        $colname_descricao_usuario = $row_descricao['IdUsuario'];
        }
        mysql_select_db($database_conexao, $conexao);
        $query_descricao_usuario = sprintf("SELECT * FROM usuarios WHERE IdUsuario = %s", GetSQLValueString($colname_descricao_usuario, "int"));
        $descricao_usuario = mysql_query($query_descricao_usuario, $conexao) or die(mysql_error());
        $row_descricao_usuario = mysql_fetch_assoc($descricao_usuario);
        $totalRows_descricao_usuario = mysql_num_rows($descricao_usuario);
        
        echo $row_descricao_usuario['nome'];
        
        mysql_free_result($descricao_usuario);
        ?>
		<?php if ($row_descricao['usuario_responsavel']!="") {echo $row_descricao['usuario_responsavel'];} // PARA SOLICITACOES ANTIGAS - responsável do post ?>
        | <? echo date('d-m-Y | H:i:s', strtotime($row_descricao['data'])); ?> | 
		<?php echo $row_descricao['tipo_postagem']; ?>
        <br>
		</strong>
        <?php if($row_descricao['questionado'] != ""){ ?>
			Para: <strong><?php echo $row_descricao['questionado']; ?></strong>
			<br>
		<? } ?>

        <?php echo $row_descricao['descricao']; ?>
		<? if ( $totalRows_descricao > 1 ) { ?>
        
		<div style=" width: 100%; height: 1px; background-color: #CCCCCC; margin-top: 10px; margin-bottom: 10px;"></div>

		<? } ?>

    <?php } while ($row_descricao = mysql_fetch_assoc($descricao)); ?>
    
        <div>
            <strong>Medida tomada: </strong>
            <br>
            <?php echo $row_solicitacao['medida_tomada']; ?>
        </div>
        
	</div>
	</td>

  </tr>
</table>
</div>

<div class="div_solicitacao_linhas3" style="padding:0px;">
<table cellspacing="0" cellpadding="0">
  <tr>



	<td style="padding:0px; text-align: left; ">&nbsp;</td>

	<td width="450" style="padding-top:0px; padding-bottom: 0px; text-align: right; vertical-align: middle;">
	<? if($totalRows_arquivos_anexos > 0) { ?>Existe(m) arquivo(s) em anexo <? } else { echo "&nbsp;"; } ?>
	</td>
  </tr>
</table>
</div>


<input type="hidden" name="MM_insert" value="solicitacao" />
</form>
</body>
</html>
<?php
mysql_free_result($geral_tipo_programa);

mysql_free_result($geral_tipo_subprograma);

mysql_free_result($descricao);

mysql_free_result($arquivos_anexos);

mysql_free_result($solicitacao_tipo_parecer);

mysql_free_result($solicitacao);

mysql_free_result($usuario);

?>