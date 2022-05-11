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
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING'], ENT_QUOTES, 'UTF-8');
}

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
SELECT * 
FROM solicitacao WHERE id = %s", 
GetSQLValueString($colname_solicitacao, "int"));
$solicitacao = mysql_query($query_solicitacao, $conexao) or die(mysql_error());
$row_solicitacao = mysql_fetch_assoc($solicitacao);
$totalRows_solicitacao = mysql_num_rows($solicitacao);
// fim - solicitação

// tempo_gasto
$colname_tempo_gasto = "-1";
if (isset($_GET['id_solicitacao'])) {
  $colname_tempo_gasto = $_GET['id_solicitacao'];
}
mysql_select_db($database_conexao, $conexao);
$query_tempo_gasto = sprintf("
SELECT *, 
(SELECT nome FROM usuarios WHERE usuarios.IdUsuario = solicitacao_tempo_gasto.id_usuario) as nome_usuario  
FROM solicitacao_tempo_gasto 
WHERE id_solicitacao = %s 
ORDER BY id_solicitacao_tempo_gasto ASC", 
GetSQLValueString($colname_tempo_gasto, "int"));
$tempo_gasto = mysql_query($query_tempo_gasto, $conexao) or die(mysql_error());
$row_tempo_gasto = mysql_fetch_assoc($tempo_gasto);
$totalRows_tempo_gasto = mysql_num_rows($tempo_gasto);
// fim - tempo_gasto

// tempo_gasto_soma
$colname_tempo_gasto_soma = "-1";
if (isset($_GET['id_solicitacao'])) {
  $colname_tempo_gasto_soma = $_GET['id_solicitacao'];
}
mysql_select_db($database_conexao, $conexao);
$query_tempo_gasto_soma = sprintf("
SELECT SUM(dia), SUM(hora), SUM(minuto), SUM(total_em_minutos) 
FROM solicitacao_tempo_gasto 
WHERE id_solicitacao = %s 
ORDER BY id_solicitacao_tempo_gasto ASC", 
GetSQLValueString($colname_tempo_gasto_soma, "int"));
$tempo_gasto_soma = mysql_query($query_tempo_gasto_soma, $conexao) or die(mysql_error());
$row_tempo_gasto_soma = mysql_fetch_assoc($tempo_gasto_soma);
$totalRows_tempo_gasto_soma = mysql_num_rows($tempo_gasto_soma);
// fim - tempo_gasto_soma

// tempo_gasto_solicitante_soma
$colname_tempo_gasto_solicitante_soma = "-1";
if (isset($_GET['id_solicitacao'])) {
  $colname_tempo_gasto_solicitante_soma = $_GET['id_solicitacao'];
}
mysql_select_db($database_conexao, $conexao);
$query_tempo_gasto_solicitante_soma = sprintf("SELECT SUM(dia), SUM(hora), SUM(minuto), SUM(total_em_minutos) FROM solicitacao_tempo_gasto WHERE id_solicitacao = %s and responsabilidade='solicitante' ORDER BY id_solicitacao_tempo_gasto ASC", GetSQLValueString($colname_tempo_gasto_solicitante_soma, "int"));
$tempo_gasto_solicitante_soma = mysql_query($query_tempo_gasto_solicitante_soma, $conexao) or die(mysql_error());
$row_tempo_gasto_solicitante_soma = mysql_fetch_assoc($tempo_gasto_solicitante_soma);
$totalRows_tempo_gasto_solicitante_soma = mysql_num_rows($tempo_gasto_solicitante_soma);
// fim - tempo_gasto_solicitante_soma

// tempo_gasto_operador_soma
$colname_tempo_gasto_operador_soma = "-1";
if (isset($_GET['id_solicitacao'])) {
  $colname_tempo_gasto_operador_soma = $_GET['id_solicitacao'];
}
mysql_select_db($database_conexao, $conexao);
$query_tempo_gasto_operador_soma = sprintf("SELECT SUM(dia), SUM(hora), SUM(minuto), SUM(total_em_minutos) FROM solicitacao_tempo_gasto WHERE id_solicitacao = %s and responsabilidade='operador' ORDER BY id_solicitacao_tempo_gasto ASC", GetSQLValueString($colname_tempo_gasto_operador_soma, "int"));
$tempo_gasto_operador_soma = mysql_query($query_tempo_gasto_operador_soma, $conexao) or die(mysql_error());
$row_tempo_gasto_operador_soma = mysql_fetch_assoc($tempo_gasto_operador_soma);
$totalRows_tempo_gasto_operador_soma = mysql_num_rows($tempo_gasto_operador_soma);
// fim - tempo_gasto_operador_soma

// tempo_gasto_testador_soma
$colname_tempo_gasto_testador_soma = "-1";
if (isset($_GET['id_solicitacao'])) {
  $colname_tempo_gasto_testador_soma = $_GET['id_solicitacao'];
}
mysql_select_db($database_conexao, $conexao);
$query_tempo_gasto_testador_soma = sprintf("SELECT SUM(dia), SUM(hora), SUM(minuto), SUM(total_em_minutos) FROM solicitacao_tempo_gasto WHERE id_solicitacao = %s and responsabilidade='testador' ORDER BY id_solicitacao_tempo_gasto ASC", GetSQLValueString($colname_tempo_gasto_testador_soma, "int"));
$tempo_gasto_testador_soma = mysql_query($query_tempo_gasto_testador_soma, $conexao) or die(mysql_error());
$row_tempo_gasto_testador_soma = mysql_fetch_assoc($tempo_gasto_testador_soma);
$totalRows_tempo_gasto_testador_soma = mysql_num_rows($tempo_gasto_testador_soma);
// fim - tempo_gasto_testador_soma

// tempo_gasto_analista_de_orcamento_soma
$colname_tempo_gasto_analista_de_orcamento_soma = "-1";
if (isset($_GET['id_solicitacao'])) {
  $colname_tempo_gasto_analista_de_orcamento_soma = $_GET['id_solicitacao'];
}
mysql_select_db($database_conexao, $conexao);
$query_tempo_gasto_analista_de_orcamento_soma = sprintf("SELECT SUM(dia), SUM(hora), SUM(minuto), SUM(total_em_minutos) FROM solicitacao_tempo_gasto WHERE id_solicitacao = %s and responsabilidade='executante' and situacao='em orçamento' ORDER BY id_solicitacao_tempo_gasto ASC", GetSQLValueString($colname_tempo_gasto_analista_de_orcamento_soma, "int"));
$tempo_gasto_analista_de_orcamento_soma = mysql_query($query_tempo_gasto_analista_de_orcamento_soma, $conexao) or die(mysql_error());
$row_tempo_gasto_analista_de_orcamento_soma = mysql_fetch_assoc($tempo_gasto_analista_de_orcamento_soma);
$totalRows_tempo_gasto_analista_de_orcamento_soma = mysql_num_rows($tempo_gasto_analista_de_orcamento_soma);
// fim - tempo_gasto_analista_de_orcamento_soma

// tempo_gasto_executante_soma
$colname_tempo_gasto_executante_soma = "-1";
if (isset($_GET['id_solicitacao'])) {
  $colname_tempo_gasto_executante_soma = $_GET['id_solicitacao'];
}
mysql_select_db($database_conexao, $conexao);
$query_tempo_gasto_executante_soma = sprintf("SELECT SUM(dia), SUM(hora), SUM(minuto), SUM(total_em_minutos) FROM solicitacao_tempo_gasto WHERE id_solicitacao = %s and responsabilidade='executante' and situacao<>'em orçamento' ORDER BY id_solicitacao_tempo_gasto ASC", GetSQLValueString($colname_tempo_gasto_executante_soma, "int"));
$tempo_gasto_executante_soma = mysql_query($query_tempo_gasto_executante_soma, $conexao) or die(mysql_error());
$row_tempo_gasto_executante_soma = mysql_fetch_assoc($tempo_gasto_executante_soma);
$totalRows_tempo_gasto_executante_soma = mysql_num_rows($tempo_gasto_executante_soma);
// fim- tempo_gasto_executante_soma

// tempo_gasto_pessoa
$colname_tempo_gasto_pessoa = "-1";
if (isset($_GET['id_solicitacao'])) {
  $colname_tempo_gasto_pessoa = $_GET['id_solicitacao'];
}
mysql_select_db($database_conexao, $conexao);
$query_tempo_gasto_pessoa = sprintf("
SELECT SUM(dia), SUM(hora), SUM(minuto), SUM(total_em_minutos), id_usuario, 
(SELECT nome FROM usuarios WHERE usuarios.IdUsuario = solicitacao_tempo_gasto.id_usuario) as nome_usuario 
FROM solicitacao_tempo_gasto 
WHERE id_solicitacao = %s GROUP BY id_usuario ASC", 
GetSQLValueString($colname_tempo_gasto_pessoa, "int"));
$tempo_gasto_pessoa = mysql_query($query_tempo_gasto_pessoa, $conexao) or die(mysql_error());
$row_tempo_gasto_pessoa = mysql_fetch_assoc($tempo_gasto_pessoa);
$totalRows_tempo_gasto_pessoa = mysql_num_rows($tempo_gasto_pessoa);
// fim - tempo_gasto_por pessoa
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title></title>

<link rel="stylesheet" href="css/solicitacao.css" type="text/css" media="screen" />
<!--[if lte IE 7]>

<style>
body{
	overflow-y: hidden;
}
</style>
<![endif]-->
<link rel="stylesheet" type="text/css" media="screen" href="css/jquery-ui.css" />
<link rel="stylesheet" type="text/css" media="screen" href="css/ui.jqgrid.css" />
<script src="js/jquery.js"></script>
<script src="js/grid.locale-pt-br.js" type="text/javascript"></script>
<script type="text/javascript">
	$.jgrid.no_legacy_api = true;
</script>
<script src="js/jquery.jqGrid.src.js" type="text/javascript"></script>
</head>

<body>

<div class="div_solicitacao_linhas2">
<table cellspacing="0" cellpadding="0">
	<tr>
		<td style="text-align: left">
		<span class="label_solicitacao2">Solicitação número: </span><?php echo $row_solicitacao['id']; ?>
		</td>
        
		<td style="text-align: right">
		<a href="solicitacao_editar.php?id_solicitacao=<?php echo $_GET['id_solicitacao']; ?>" style="text-decoration: none; color:#FFFFFF;" target="_top">&lt;&lt;  Voltar</a>
        </td>
	</tr>
</table>
</div>

<div class="div_solicitacao_linhas">
<table cellspacing="0" cellpadding="0">
	<tr>
		<td style="text-align: left">
		<span class="label_solicitacao">Tempo gasto</span>
		</td>
	</tr>
</table>
</div>

<div class="div_solicitacao_linhas3">
<table cellspacing="0" cellpadding="0">
	<tr>
		<td style="text-align: left">
		Título: <?php echo $row_solicitacao['titulo']; ?>
		</td>
	</tr>
</table>
</div>



<div class="div_solicitacao_linhas">
<table cellspacing="0" cellpadding="0">
	<tr>
		<td style="text-align: left">
		<!-- função que transforma o total_em_minutos em ano/dia/hora/minuto -->
		<?
        function tempo_gasto_conversao($total_em_minutos){
            $time=$total_em_minutos*60;
            $tempo_gasto_conversao = "";
            if($time >= 31556926){
                $tempo_gasto_conversao .= $value["anos"] = floor($time/31556926)." anos ";
                $time = ($time%31556926);
            }
            if($time >= 86400){
              $tempo_gasto_conversao .= $value["dias"] = floor($time/86400)." dias ";
              $time = ($time%86400);
            }
            if($time >= 3600){
              $tempo_gasto_conversao .= $value["horas"] = floor($time/3600)." horas ";
              $time = ($time%3600);
            }
            if($time >= 60){
              $tempo_gasto_conversao .= $value["minutos"] = floor($time/60)." minutos";
              $time = ($time%60);
            }
            return $tempo_gasto_conversao;
        }
        ?>
		<!-- fim - função que transforma o total_em_minutos em ano/dia/hora/minuto -->

		<strong>Resumo geral:</strong> <?php echo tempo_gasto_conversao($row_tempo_gasto_soma['SUM(total_em_minutos)']); ?>
		<br><br>

		<? if($row_tempo_gasto_solicitante_soma['SUM(total_em_minutos)'] > 0){ ?>
		<strong>Resumo solicitante: </strong> <?php echo tempo_gasto_conversao($row_tempo_gasto_solicitante_soma['SUM(total_em_minutos)']); ?>
		<br>
		<? } ?>

		<? if($row_tempo_gasto_operador_soma['SUM(total_em_minutos)'] > 0){ ?>
		<strong>Resumo operador:</strong> <?php echo tempo_gasto_conversao($row_tempo_gasto_operador_soma['SUM(total_em_minutos)']); ?>
		<br>
		<? } ?>

		<? if($row_tempo_gasto_analista_de_orcamento_soma['SUM(total_em_minutos)'] > 0){ ?>
		<strong>Resumo analista de orçamento:</strong> <?php echo tempo_gasto_conversao($row_tempo_gasto_analista_de_orcamento_soma['SUM(total_em_minutos)']); ?>
		<br>
		<? } ?>

		<? if($row_tempo_gasto_executante_soma['SUM(total_em_minutos)'] > 0){ ?>
		<strong>Resumo executante:</strong> <?php echo tempo_gasto_conversao($row_tempo_gasto_executante_soma['SUM(total_em_minutos)']); ?>
		<br>
		<? } ?>

		<? if($row_tempo_gasto_testador_soma['SUM(total_em_minutos)'] > 0){ ?>
		<strong>Resumo testador:</strong> <?php echo tempo_gasto_conversao($row_tempo_gasto_testador_soma['SUM(total_em_minutos)']); ?>
		<br>
		<? } ?>

		<br>

<table id="solicitacao"></table>
<div id="navegacao"></div>
<script type="text/javascript">
var dados = [


<?php do { ?>

{
	id_solicitacao_tempo_gasto:"<?php echo $row_tempo_gasto['id_solicitacao_tempo_gasto']; ?>",
	nome_usuario:"<?php echo $row_tempo_gasto['nome_usuario']; ?>",
	responsabilidade:"<?php echo $row_tempo_gasto['responsabilidade']; ?>",
	data:"<? echo date('d-m-Y  H:i:s', strtotime($row_tempo_gasto['data'])); ?>",
	total_em_minutos:"<?php echo tempo_gasto_conversao($row_tempo_gasto['total_em_minutos']); ?>",
	situacao:"<?php echo $row_tempo_gasto['situacao']; ?>",
	acao:"<?php echo $row_tempo_gasto['acao']; ?>"
},


<?php } while ($row_tempo_gasto = mysql_fetch_assoc($tempo_gasto)); ?>

];

	jQuery('#solicitacao').jqGrid({
	   	data:dados,
		datatype: 'local',
		colNames:['Usuário','Respons.', 'Data','Tempo gasto','Situação','Ação'],
		colModel :[ 
			{name:'nome_usuario', index:'nome_usuario'}, 
			{name:'responsabilidade', index:'responsabilidade', width:60, align:'center'}, 
			{name:'data', index:'data', width:100, align:'center'}, 
			{name:'total_em_minutos', index:'total_em_minutos', width:130, align:'center'}, 
			{name:'situacao', index:'situacao', width:80, align:'center'}, 
			{name:'acao', index:'acao', width:90, align:'center'}
		],
	   	rowNum:99999999,
	    viewrecords: true,
		caption:"Relatório",
		autowidth: true,
		height: "100%"	

	});

</script>

		<br><br>

Resumo por pessoa:
<br><br>
<?php do { ?>
		<strong><?php echo $row_tempo_gasto_pessoa['nome_usuario']; ?></strong> - 
		<?php echo tempo_gasto_conversao($row_tempo_gasto_pessoa['SUM(total_em_minutos)']); ?>
		<br>

<?php } while ($row_tempo_gasto_pessoa = mysql_fetch_assoc($tempo_gasto_pessoa)); ?>


        </td>
	</tr>
</table>
</div>

</body>

</html>
<?php
mysql_free_result($solicitacao);

mysql_free_result($tempo_gasto);

mysql_free_result($tempo_gasto_soma);

mysql_free_result($tempo_gasto_solicitante_soma);
mysql_free_result($tempo_gasto_operador_soma);
mysql_free_result($tempo_gasto_analista_de_orcamento_soma);
mysql_free_result($tempo_gasto_executante_soma);
mysql_free_result($tempo_gasto_testador_soma);
mysql_free_result($tempo_gasto_pessoa);

mysql_free_result($usuario);
?>
