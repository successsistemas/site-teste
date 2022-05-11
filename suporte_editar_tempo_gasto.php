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

// suporte
$colname_suporte = "-1";
if (isset($_GET['id_suporte'])) {
  $colname_suporte = $_GET['id_suporte'];
}
mysql_select_db($database_conexao, $conexao);
$query_suporte = sprintf("
SELECT *,  
(SELECT nome FROM usuarios WHERE usuarios.IdUsuario = suporte.id_usuario_responsavel) as usuario_responsavel, 
(SELECT nome FROM usuarios WHERE usuarios.IdUsuario = suporte.id_usuario_envolvido) as usuario_envolvido
FROM suporte 
WHERE id = %s", 
GetSQLValueString($colname_suporte, "int"));
$suporte = mysql_query($query_suporte, $conexao) or die(mysql_error());
$row_suporte = mysql_fetch_assoc($suporte);
$totalRows_suporte = mysql_num_rows($suporte);
// fim - suporte

// tempo_gasto
$colname_tempo_gasto = "-1";
if (isset($_GET['id_suporte'])) {
  $colname_tempo_gasto = $_GET['id_suporte'];
}
mysql_select_db($database_conexao, $conexao);
$query_tempo_gasto = sprintf("
SELECT *, 
(SELECT nome FROM usuarios WHERE usuarios.IdUsuario = suporte_tempo_gasto.id_usuario) as nome_usuario  
FROM suporte_tempo_gasto 
WHERE id_suporte = %s 
ORDER BY id_suporte_tempo_gasto ASC", 
GetSQLValueString($colname_tempo_gasto, "int"));
$tempo_gasto = mysql_query($query_tempo_gasto, $conexao) or die(mysql_error());
$row_tempo_gasto = mysql_fetch_assoc($tempo_gasto);
$totalRows_tempo_gasto = mysql_num_rows($tempo_gasto);
// fim - tempo_gasto

// horas gastas - soma
$colname_tempo_gasto_soma = "-1";
if (isset($_GET['id_suporte'])) {
  $colname_tempo_gasto_soma = $_GET['id_suporte'];
}
mysql_select_db($database_conexao, $conexao);
$query_tempo_gasto_soma = sprintf("SELECT SUM(dia), SUM(hora), SUM(minuto), SUM(total_em_minutos) FROM suporte_tempo_gasto WHERE id_suporte = %s ORDER BY id_suporte_tempo_gasto ASC", GetSQLValueString($colname_tempo_gasto_soma, "int"));
$tempo_gasto_soma = mysql_query($query_tempo_gasto_soma, $conexao) or die(mysql_error());
$row_tempo_gasto_soma = mysql_fetch_assoc($tempo_gasto_soma);
$totalRows_tempo_gasto_soma = mysql_num_rows($tempo_gasto_soma);

// horas gastas - solicitante - soma
$colname_tempo_gasto_solicitante_soma = "-1";
if (isset($_GET['id_suporte'])) {
  $colname_tempo_gasto_solicitante_soma = $_GET['id_suporte'];
}
mysql_select_db($database_conexao, $conexao);
$query_tempo_gasto_solicitante_soma = sprintf("SELECT SUM(dia), SUM(hora), SUM(minuto), SUM(total_em_minutos) FROM suporte_tempo_gasto WHERE id_suporte = %s and responsabilidade='solicitante' ORDER BY id_suporte_tempo_gasto ASC", GetSQLValueString($colname_tempo_gasto_solicitante_soma, "int"));
$tempo_gasto_solicitante_soma = mysql_query($query_tempo_gasto_solicitante_soma, $conexao) or die(mysql_error());
$row_tempo_gasto_solicitante_soma = mysql_fetch_assoc($tempo_gasto_solicitante_soma);
$totalRows_tempo_gasto_solicitante_soma = mysql_num_rows($tempo_gasto_solicitante_soma);

// horas gastas - testador - soma
$colname_tempo_gasto_testador_soma = "-1";
if (isset($_GET['id_suporte'])) {
  $colname_tempo_gasto_testador_soma = $_GET['id_suporte'];
}
mysql_select_db($database_conexao, $conexao);
$query_tempo_gasto_testador_soma = sprintf("SELECT SUM(dia), SUM(hora), SUM(minuto), SUM(total_em_minutos) FROM suporte_tempo_gasto WHERE id_suporte = %s and responsabilidade='testador' ORDER BY id_suporte_tempo_gasto ASC", GetSQLValueString($colname_tempo_gasto_testador_soma, "int"));
$tempo_gasto_testador_soma = mysql_query($query_tempo_gasto_testador_soma, $conexao) or die(mysql_error());
$row_tempo_gasto_testador_soma = mysql_fetch_assoc($tempo_gasto_testador_soma);
$totalRows_tempo_gasto_testador_soma = mysql_num_rows($tempo_gasto_testador_soma);

// horas gastas - analista_de_orcamento - soma
$colname_tempo_gasto_analista_de_orcamento_soma = "-1";
if (isset($_GET['id_suporte'])) {
  $colname_tempo_gasto_analista_de_orcamento_soma = $_GET['id_suporte'];
}
mysql_select_db($database_conexao, $conexao);
$query_tempo_gasto_analista_de_orcamento_soma = sprintf("SELECT SUM(dia), SUM(hora), SUM(minuto), SUM(total_em_minutos) FROM suporte_tempo_gasto WHERE id_suporte = %s and responsabilidade='executante' and situacao='em orçamento' ORDER BY id_suporte_tempo_gasto ASC", GetSQLValueString($colname_tempo_gasto_analista_de_orcamento_soma, "int"));
$tempo_gasto_analista_de_orcamento_soma = mysql_query($query_tempo_gasto_analista_de_orcamento_soma, $conexao) or die(mysql_error());
$row_tempo_gasto_analista_de_orcamento_soma = mysql_fetch_assoc($tempo_gasto_analista_de_orcamento_soma);
$totalRows_tempo_gasto_analista_de_orcamento_soma = mysql_num_rows($tempo_gasto_analista_de_orcamento_soma);

// horas gastas - executante - soma
$colname_tempo_gasto_executante_soma = "-1";
if (isset($_GET['id_suporte'])) {
  $colname_tempo_gasto_executante_soma = $_GET['id_suporte'];
}
mysql_select_db($database_conexao, $conexao);
$query_tempo_gasto_executante_soma = sprintf("SELECT SUM(dia), SUM(hora), SUM(minuto), SUM(total_em_minutos) FROM suporte_tempo_gasto WHERE id_suporte = %s and responsabilidade='executante' and situacao<>'em orçamento' ORDER BY id_suporte_tempo_gasto ASC", GetSQLValueString($colname_tempo_gasto_executante_soma, "int"));
$tempo_gasto_executante_soma = mysql_query($query_tempo_gasto_executante_soma, $conexao) or die(mysql_error());
$row_tempo_gasto_executante_soma = mysql_fetch_assoc($tempo_gasto_executante_soma);
$totalRows_tempo_gasto_executante_soma = mysql_num_rows($tempo_gasto_executante_soma);

// horas gastas - executante - soma
$colname_tempo_gasto_executante_soma = "-1";
if (isset($_GET['id_suporte'])) {
  $colname_tempo_gasto_executante_soma = $_GET['id_suporte'];
}
mysql_select_db($database_conexao, $conexao);
$query_tempo_gasto_executante_soma = sprintf("SELECT SUM(dia), SUM(hora), SUM(minuto), SUM(total_em_minutos) FROM suporte_tempo_gasto WHERE id_suporte = %s and responsabilidade='executante' and situacao<>'em orçamento' ORDER BY id_suporte_tempo_gasto ASC", GetSQLValueString($colname_tempo_gasto_executante_soma, "int"));
$tempo_gasto_executante_soma = mysql_query($query_tempo_gasto_executante_soma, $conexao) or die(mysql_error());
$row_tempo_gasto_executante_soma = mysql_fetch_assoc($tempo_gasto_executante_soma);
$totalRows_tempo_gasto_executante_soma = mysql_num_rows($tempo_gasto_executante_soma);

// horas gastas - por pessoa
$colname_tempo_gasto_pessoa = "-1";
if (isset($_GET['id_suporte'])) {
  $colname_tempo_gasto_pessoa = $_GET['id_suporte'];
}
mysql_select_db($database_conexao, $conexao);
$query_tempo_gasto_pessoa = sprintf("
SELECT SUM(dia), SUM(hora), SUM(minuto), SUM(total_em_minutos), id_usuario, 
(SELECT nome FROM usuarios WHERE usuarios.IdUsuario = suporte_tempo_gasto.id_usuario) as nome_usuario  
FROM suporte_tempo_gasto 
WHERE id_suporte = %s 
GROUP BY nome_usuario ASC", 
GetSQLValueString($colname_tempo_gasto_pessoa, "int"));
$tempo_gasto_pessoa = mysql_query($query_tempo_gasto_pessoa, $conexao) or die(mysql_error());
$row_tempo_gasto_pessoa = mysql_fetch_assoc($tempo_gasto_pessoa);
$totalRows_tempo_gasto_pessoa = mysql_num_rows($tempo_gasto_pessoa);
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
		<span class="label_solicitacao2">Suporte número: </span><?php echo $row_suporte['id']; ?>
		</td>
        
		<td style="text-align: right">
		<a href="suporte_editar.php?id_suporte=<?php echo $_GET['id_suporte']; ?>" style="text-decoration: none; color:#FFFFFF;" target="_top">&lt;&lt;  Voltar</a>
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
		Título: <?php echo $row_suporte['titulo']; ?>
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
        function resumo($total_em_minutos){
            $time=$total_em_minutos*60;
            $resumo = "";
            if($time >= 31556926){
                $resumo .= $value["anos"] = floor($time/31556926)." anos ";
                $time = ($time%31556926);
            }
            if($time >= 86400){
              $resumo .= $value["dias"] = floor($time/86400)." dias ";
              $time = ($time%86400);
            }
            if($time >= 3600){
              $resumo .= $value["horas"] = floor($time/3600)." horas ";
              $time = ($time%3600);
            }
            if($time >= 60){
              $resumo .= $value["minutos"] = floor($time/60)." minutos";
              $time = ($time%60);
            }
            echo $resumo;
        }
        ?>
		<!-- fim - função que transforma o total_em_minutos em ano/dia/hora/minuto -->

		<strong>Resumo geral:</strong> <?php resumo($row_tempo_gasto_soma['SUM(total_em_minutos)']); ?>
		<br><br>

		<? if($row_tempo_gasto_solicitante_soma['SUM(total_em_minutos)'] > 0){ ?>
		<strong>Resumo solicitante: </strong> <?php resumo($row_tempo_gasto_solicitante_soma['SUM(total_em_minutos)']); ?>
		<br>
		<? } ?>

		<? if($row_tempo_gasto_analista_de_orcamento_soma['SUM(total_em_minutos)'] > 0){ ?>
		<strong>Resumo analista de orçamento:</strong> <?php resumo($row_tempo_gasto_analista_de_orcamento_soma['SUM(total_em_minutos)']); ?>
		<br>
		<? } ?>

		<? if($row_tempo_gasto_executante_soma['SUM(total_em_minutos)'] > 0){ ?>
		<strong>Resumo executante:</strong> <?php resumo($row_tempo_gasto_executante_soma['SUM(total_em_minutos)']); ?>
		<br>
		<? } ?>

		<? if($row_tempo_gasto_testador_soma['SUM(total_em_minutos)'] > 0){ ?>
		<strong>Resumo testador:</strong> <?php resumo($row_tempo_gasto_testador_soma['SUM(total_em_minutos)']); ?>
		<br>
		<? } ?>

		<br>

<table id="suporte"></table>
<div id="navegacao"></div>
<script type="text/javascript">
var dados = [


<?php do { ?>

{
	id_suporte_tempo_gasto:"<?php echo $row_tempo_gasto['id_suporte_tempo_gasto']; ?>",
	nome_usuario:"<?php echo $row_tempo_gasto['nome_usuario']; ?>",
	responsabilidade:"<?php echo $row_tempo_gasto['responsabilidade']; ?>",
	data:"<? echo date('d-m-Y  H:i:s', strtotime($row_tempo_gasto['data'])); ?>",
	total_em_minutos:"<?php resumo($row_tempo_gasto['total_em_minutos']); ?>",
	situacao:"<?php echo $row_tempo_gasto['situacao']; ?>",
	acao:"<?php echo $row_tempo_gasto['acao']; ?>"
},


<?php } while ($row_tempo_gasto = mysql_fetch_assoc($tempo_gasto)); ?>

];

	jQuery('#suporte').jqGrid({
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
		<?php resumo($row_tempo_gasto_pessoa['SUM(total_em_minutos)']); ?>
		<br>

<?php } while ($row_tempo_gasto_pessoa = mysql_fetch_assoc($tempo_gasto_pessoa)); ?>


        </td>
	</tr>
</table>
</div>

</body>

</html>
<?php
mysql_free_result($suporte);

mysql_free_result($tempo_gasto);

mysql_free_result($tempo_gasto_soma);

mysql_free_result($tempo_gasto_solicitante_soma);

mysql_free_result($tempo_gasto_analista_de_orcamento_soma);

mysql_free_result($tempo_gasto_executante_soma);

mysql_free_result($tempo_gasto_testador_soma);

mysql_free_result($tempo_gasto_pessoa);

mysql_free_result($usuario);
?>
