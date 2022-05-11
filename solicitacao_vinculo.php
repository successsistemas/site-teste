<?php require_once('restrito.php'); ?>
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

// empresa_dados
mysql_select_db($database_conexao, $conexao);
$query_empresa_dados = sprintf("
SELECT codigo1, nome1  
FROM da01 
WHERE codigo1 = %s and da01.sr_deleted <> 'T'", GetSQLValueString(@$_GET['codigo_empresa'], "text"));
$empresa_dados = mysql_query($query_empresa_dados, $conexao) or die(mysql_error());
$row_empresa_dados = mysql_fetch_assoc($empresa_dados);
$totalRows_empresa_dados = mysql_num_rows($empresa_dados);
// fim - empresa_dados

// solicitacao
mysql_select_db($database_conexao, $conexao);

$query_solicitacao = sprintf("
SELECT solicitacao.*, 
(SELECT nome FROM usuarios WHERE usuarios.IdUsuario = solicitacao.id_usuario_responsavel) as usuario_responsavel, 
(SELECT nome FROM usuarios WHERE usuarios.IdUsuario = solicitacao.id_operador) as nome_operador, 
(SELECT nome FROM usuarios WHERE usuarios.IdUsuario = solicitacao.id_executante) as nome_executante, 
(SELECT nome FROM usuarios WHERE usuarios.IdUsuario = solicitacao.id_analista_orcamento) as nome_analista_orcamento, 
(SELECT nome FROM usuarios WHERE usuarios.IdUsuario = solicitacao.id_testador) as nome_testador 
FROM solicitacao 
WHERE codigo_empresa = %s 
ORDER BY id DESC", 
GetSQLValueString($row_empresa_dados['codigo1'], "text"));
$solicitacao = mysql_query($query_solicitacao, $conexao) or die(mysql_error());
$row_solicitacao = mysql_fetch_assoc($solicitacao);
$totalRows_solicitacao = mysql_num_rows($solicitacao);
// fim - solicitacao

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"> 
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<script type="text/javascript" src="js/jquery.js"></script>

<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>Success Sistemas</title>
<link rel="stylesheet" href="css/suporte.css" type="text/css" media="screen" />

<!--[if !IE]> -->
<style>
body{
	overflow-y: scroll; /* se não é IE, então mostra a scroll vertical */
}
</style>
<!-- <![endif]-->

<style>
.ui-jqgrid .ui-jqgrid-btable
{
  table-layout:auto;
} 
</style>

<link rel="stylesheet" type="text/css" media="screen" href="css/jquery-ui.css" />
<link rel="stylesheet" type="text/css" media="screen" href="css/ui.jqgrid.css" />
<script src="js/grid.locale-pt-br.js" type="text/javascript"></script>
<script src="js/jquery.jqGrid.src.js" type="text/javascript"></script>

</head>

<body>

<!-- solicitacao -->
<div class="div_solicitacao_linhas" id="cabecalho_solicitacoes" style="cursor: pointer">
<table cellspacing="0" cellpadding="0" width="100%">
	<tr>
		<td style="text-align:left">
		Solicitações | 
        Cliente: <?php echo $row_empresa_dados['nome1']; ?>
		</td>
        
		<td style="text-align: right">
        &lt;&lt; <a onclick="parent.eval('tb_remove()')">Voltar</a>
        </td>
	</tr>
</table>
</div>

<? if($totalRows_solicitacao > 0){ ?>
<div id="corpo_solicitacoes" style="cursor: pointer">
<table id="solicitacao"></table>
<div id="navegacao"></div>
<script type="text/javascript">
var dados = [		 
<?php do { ?>
{
	id:"<?php echo $row_solicitacao['id']; ?>",
	titulo:"<?php echo $row_solicitacao['titulo']; ?>",
	solicitante:"<?php echo $row_solicitacao['usuario_responsavel']; ?>",
	dt_solicitacao:"<? echo $row_solicitacao['dt_solicitacao']; ?>",
	tipo:"<?php echo $row_solicitacao['tipo']; ?>",
	prioridade:"<?php echo $row_solicitacao['prioridade']; ?>",
	situacao:"<?php echo $row_solicitacao['situacao']; ?>",
	visualizar:"<? echo "<a href='solicitacao_editar.php?id_solicitacao=".$row_solicitacao['id']."&padrao=sim' target='_blank'><img src='imagens/visualizar.png' border='0' /></a>"; ?>"
},
<?php } while ($row_solicitacao = mysql_fetch_assoc($solicitacao)); ?>
];
jQuery('#solicitacao').jqGrid({
	data:dados,
	datatype: 'local',
	colNames:['Núm','Título', 'Solicitante','Criação','Tipo','Prior.','Situação',''],
	colModel :[ 
            {name:'id', index:'id', width:40, sorttype: 'integer'}, 
            {name:'titulo', index:'titulo', width:250}, 
            {name:'solicitante', index:'solicitante', width:100, align:'left'}, 
            {name:'dt_solicitacao', index:'dt_solicitacao', width:80, formatter:'date', formatoptions: { srcformat: "ISO8601Long", newformat: "d-m-Y" }, sorttype: 'date', align:'center' },
            {name:'tipo', index:'tipo', width:90, align:'center'},
            {name:'prioridade', index:'prioridade', width:50, align:'center'},
            {name:'situacao', index:'situacao', width:90, align:'center'},
            {name:'visualizar', index:'visualizar', width:30, align:'center'}
	],
	rowNum:16,
	rowList:[2,5,10,20,30,40,50,100,999999],
				loadComplete: function() {
					$("option[value=999999]").text('Todos');
				},
	pager: '#navegacao',
	//sortname: 'id',
	viewrecords: true,
	//sortorder: 'desc',
	toppager: true, // aparecer a barra de navegação também no topo
	//width: true,
	autowidth: true,
	height: "100%"		
});
</script>
</div>
<? } else { ?>
<div class="div_solicitacao_linhas4">
Nenhuma solicitação encontrada na filtragem atual.
</div>
<? } ?>
<!-- fim - solicitacao -->

</body>

</html>
<?php
mysql_free_result($usuario);
mysql_free_result($empresa_dados);
mysql_free_result($solicitacao);
?>