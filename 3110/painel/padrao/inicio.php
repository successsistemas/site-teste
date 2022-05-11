<?php require_once('../padrao_restrito.php'); ?>
<?php require_once('../../Connections/conexao.php'); ?>
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

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

require_once('../parametros.php');
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

// função que limita caracteres
function limita_caracteres($string, $tamanho, $encode = 'UTF-8') {
    if( strlen($string) > $tamanho ){
        $string = mb_substr($string, 0, $tamanho, $encode);
	}
    return $string;
}
// fim - função que limita caracteres

// comunicado_listar
mysql_select_db($database_conexao, $conexao);
$query_comunicado_listar = sprintf("
SELECT comunicado_destinatario.*, 
comunicado.data_criacao, comunicado.assunto, comunicado.texto, comunicado.prioridade, comunicado.data_limite, comunicado.tipo, comunicado.data_reenvio, 
(SELECT COUNT(comunicado_destinatario.IdComunicadoDestinatario) FROM comunicado_destinatario WHERE comunicado_destinatario.IdComunicado = comunicado.IdComunicado and IdComunicadoHistorico IS NULL and comunicado_destinatario.responsavel = 0) AS comunicado_destinatario_contador, 
usuarios.nome AS usuario_nome 
FROM comunicado_destinatario 
LEFT JOIN comunicado ON comunicado.IdComunicado = comunicado_destinatario.IdComunicado 
LEFT JOIN usuarios ON usuarios.IdUsuario = comunicado.IdUsuario 
WHERE comunicado_destinatario.IdUsuario = %s and comunicado_destinatario.lido = 0 and comunicado_destinatario.IdComunicadoHistorico IS NULL  
ORDER BY comunicado.data_reenvio IS NULL ASC, comunicado.data_criacao DESC, comunicado.IdComunicado DESC", 
GetSQLValueString($row_usuario['IdUsuario'], "int"));
$comunicado_listar = mysql_query($query_comunicado_listar, $conexao) or die(mysql_error());
$row_comunicado_listar = mysql_fetch_assoc($comunicado_listar);
$totalRows_comunicado_listar = mysql_num_rows($comunicado_listar);
//if($totalRows_comunicado_listar ==0){header("Location: ../index.php"); exit;}
// fim - comunicado_listar

// reclamacao_aberto_suporte
// $where
$where = "1=1";
if($row_usuario['controle_suporte'] == "Y" or $row_usuario['suporte_operador_parceiro'] == "Y"){
	$where .= NULL;
} else {
	$where .= " and suporte.praca = '".$row_usuario['praca']."' ";
}
// fim - $where

mysql_select_db($database_conexao, $conexao);
$query_reclamacao_aberto_suporte = "
SELECT id, empresa, id_usuario_responsavel, data_suporte, situacao, praca, contrato, 
(SELECT nome FROM usuarios WHERE usuarios.IdUsuario = suporte.id_usuario_responsavel) as usuario_responsavel, 
(SELECT nome FROM usuarios WHERE usuarios.IdUsuario = suporte.id_usuario_envolvido) as usuario_envolvido 
FROM suporte 
WHERE $where and tipo_suporte = 'r' and status_flag <> 'f' 
ORDER BY praca ASC, id DESC
";
$reclamacao_aberto_suporte = mysql_query($query_reclamacao_aberto_suporte, $conexao) or die(mysql_error());
$row_reclamacao_aberto_suporte = mysql_fetch_assoc($reclamacao_aberto_suporte);
$totalRows_reclamacao_aberto_suporte = mysql_num_rows($reclamacao_aberto_suporte);
$reclamacao_aberto_suporte_array = NULL;
// fim - reclamacao_aberto_suporte

// usuarios_aniversario
mysql_select_db($database_conexao, $conexao);
$query_usuarios_aniversario = "
SELECT nome, praca, aniversario, telefone 
FROM usuarios
WHERE 

status = 1 and 
aniversario IS NOT NULL and 
CASE
	WHEN WEEKDAY( CONCAT( YEAR(NOW()),'-', MONTH(aniversario),'-', DAY(aniversario) ) ) = 6 THEN WEEKOFYEAR( CONCAT( YEAR(NOW()),'-', MONTH(aniversario),'-', DAY(aniversario) ) )+1 = WEEKOFYEAR( NOW()+1 ) 
	ELSE WEEKOFYEAR( CONCAT( YEAR(NOW()),'-', MONTH(aniversario),'-', DAY(aniversario) ) ) = WEEKOFYEAR( NOW() ) 
END
ORDER BY aniversario ASC, nome DESC
";
$usuarios_aniversario = mysql_query($query_usuarios_aniversario, $conexao) or die(mysql_error());
$row_usuarios_aniversario = mysql_fetch_assoc($usuarios_aniversario);
$totalRows_usuarios_aniversario = mysql_num_rows($usuarios_aniversario);
$usuarios_aniversario_array = NULL;
// fim - usuarios_aniversario
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"> 
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title></title>
<link rel="stylesheet" href="../../css/suporte.css" type="text/css" media="screen" />
<script type="text/javascript" src="../../js/jquery.js"></script>

<script type="text/javascript" src="../../funcoes.js"></script>

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

<link rel="stylesheet" type="text/css" media="screen" href="../../css/jquery-ui.css" />
<link rel="stylesheet" type="text/css" media="screen" href="../../css/ui.jqgrid.css" />
<script src="../../js/grid.locale-pt-br.js" type="text/javascript"></script>
<script src="../../js/jquery.jqGrid.src.js" type="text/javascript"></script>

</head>
<body>

<div class="div_solicitacao_linhas3">
<table cellspacing="0" cellpadding="0" width="100%">
	<tr>
		<td style="text-align:left">
		<strong>Assuntos em Destaque</strong>: Para maiores detalhes verifique na Área do Parceiro.
		</td>
	</tr>
</table>
</div>

<? if((date('m-d', strtotime($row_usuario['aniversario']))) == (date('m-d'))){ ?>
<div class="div_solicitacao_linhas4">
<table width="100%" cellspacing="0" cellpadding="0">
	<tr>
		<td>
        <div style="font-size: 14px; font-weight: bold;">
		<? echo $row_parametros['aniversario']; ?>
        </div>
		</td>
	</tr>
</table>
</div>
<? } ?>
            
<!-- comunicado_listar -->
<div class="div_solicitacao_linhas">
<table cellspacing="0" cellpadding="0" width="100%">
	<tr>
		<td style="text-align:left">
		Comunicados (<? echo $totalRows_comunicado_listar; ?>):
		</td>
	</tr>
</table>
</div>

<? if($totalRows_comunicado_listar > 0){ ?>
<div>
<table id="comunicado_listar"></table>
<div id="comunicado_listar_navegacao"></div>
<script type="text/javascript">
var comunicado_listar_dados = [		 
<?php do { ?>
{
	data_criacao:"<? echo $row_comunicado_listar['data_criacao']; ?>",
	usuario_nome:"<? echo  limita_caracteres($row_comunicado_listar['usuario_nome'], 30); ?>", 
	assunto:"<?php if($row_comunicado_listar['data_reenvio'] <> NULL){ ?>*<? } ?><? echo limita_caracteres($row_comunicado_listar['assunto'], 65); ?>", 
	tipo:"<? if($row_comunicado_listar['tipo'] == "m"){ ?><span style='color: red; font-weight: bold;'>Memorando</span><? } else { ?>Comunicado<? } ?>",
	prioridade:"<? echo $row_comunicado_listar['prioridade']; ?>"
},
<?php } while ($row_comunicado_listar = mysql_fetch_assoc($comunicado_listar)); ?>
];
jQuery('#comunicado_listar').jqGrid({
	data:comunicado_listar_dados,
	datatype: 'local',
	colNames:['Data envio','Remetente','Assunto','Tipo','Prioridade'],
	colModel :[
		{name:'data_criacao', index:'data_criacao', width:150, formatter:'date', formatoptions: { srcformat: "ISO8601Long", newformat: "d-m-Y H:i" }, sorttype: 'date', align:'center' }, 
		{name:'usuario_nome', index:'usuario_nome', width:120}, 
		{name:'assunto', index:'assunto', width:300, align:'left'}, 
		{name:'tipo', index:'tipo', width:100, align:'left'}, 
		{name:'prioridade', index:'prioridade', width:70, align:'left'} 
	],
	rowNum: 999999999999,
	viewrecords: true,
	autowidth: true,
	height: "100%"		
});
</script>
</div>
<? } else { ?>
<div class="div_solicitacao_linhas4">
Nenhum comunicado até o momento.
</div>
<? } ?>
<!-- fim - comunicado_listar -->

<br>

<!-- reclamacao_aberto_suporte -->
<div class="div_solicitacao_linhas">
<table cellspacing="0" cellpadding="0" width="100%">
	<tr>
		<td style="text-align:left">
		Reclamações em andamento (<? echo $totalRows_reclamacao_aberto_suporte; ?>):
		</td>
	</tr>
</table>
</div>

<? if($totalRows_reclamacao_aberto_suporte > 0){ ?>
<div>
<table id="reclamacao_aberto_suporte"></table>
<div id="reclamacao_aberto_suporte_navegacao"></div>
<script type="text/javascript">
var reclamacao_aberto_suporte_dados = [		 
<?php do { ?>
{
	id:"<? echo $row_reclamacao_aberto_suporte['id']; ?>",
	<? if($row_usuario['controle_suporte'] == "Y" or $row_usuario['suporte_operador_parceiro'] == "Y"){ ?>
		praca:"<? echo  limita_caracteres($row_reclamacao_aberto_suporte['praca'], 30); ?>", 
	<? } ?>
	empresa:"<? echo limita_caracteres(utf8_encode($row_reclamacao_aberto_suporte['empresa']), 65); ?>", 
	usuario_responsavel:"<? echo limita_caracteres($row_reclamacao_aberto_suporte['usuario_responsavel'], 15); ?>",
	data_suporte:"<? echo $row_reclamacao_aberto_suporte['data_suporte']; ?>", 
	situacao:"<? echo $row_reclamacao_aberto_suporte['situacao']; ?>"	
},
<?php } while ($row_reclamacao_aberto_suporte = mysql_fetch_assoc($reclamacao_aberto_suporte)); ?>
];
jQuery('#reclamacao_aberto_suporte').jqGrid({
	data:reclamacao_aberto_suporte_dados,
	datatype: 'local',
	colNames:['Num'<? if($row_usuario['controle_suporte'] == "Y" or $row_usuario['suporte_operador_parceiro'] == "Y"){ ?>,'Praça'<? } ?>,'Cliente','Responsavel','Inicio','Situação'],
	colModel :[
		{name:'id', index:'id', width:60}, 
		<? if($row_usuario['controle_suporte'] == "Y" or $row_usuario['suporte_operador_parceiro'] == "Y"){ ?>
			{name:'praca', index:'praca', width:85}, 
		<? } ?>
		{name:'empresa', index:'empresa', width:280, align:'left'}, 
		{name:'usuario_responsavel', index:'usuario_responsavel', width:120, align:'left'}, 
		{name:'data_suporte', index:'data_suporte', width:90, formatter:'date', formatoptions: { srcformat: "ISO8601Long", newformat: "d-m-Y" }, sorttype: 'date', align:'center' }, 
		{name:'situacao', index:'situacao', width:100, align:'left'}
	],
	rowNum: 999999999999,
	viewrecords: true,
	autowidth: true,
	height: "100%"		
});
</script>
</div>
<? } else { ?>
<div class="div_solicitacao_linhas4">
Nenhuma reclamação até o momento.
</div>
<? } ?>
<!-- fim - reclamacao_aberto_suporte -->

<br>

<!-- usuarios_aniversario -->
<div class="div_solicitacao_linhas">
<table cellspacing="0" cellpadding="0" width="100%">
	<tr>
		<td style="text-align:left">
		Aniversariantes da semana (<? echo $totalRows_usuarios_aniversario; ?>):
		</td>
	</tr>
</table>
</div>

<? if($totalRows_usuarios_aniversario > 0){ ?>
<div>
<table id="usuarios_aniversario"></table>
<div id="usuarios_aniversario_navegacao"></div>
<script type="text/javascript">
var usuarios_aniversario_dados = [		 
<?php do { ?>
{
	usuario:"<? echo limita_caracteres($row_usuarios_aniversario['nome'], 35); ?>",
	praca:"<? echo limita_caracteres($row_usuarios_aniversario['praca'], 15); ?>", 
	aniversario:"<? echo $row_usuarios_aniversario['aniversario']; ?>", 
	telefone:"<? echo $row_usuarios_aniversario['telefone']; ?>"	
},
<?php } while ($row_usuarios_aniversario = mysql_fetch_assoc($usuarios_aniversario)); ?>
];
jQuery('#usuarios_aniversario').jqGrid({
	data:usuarios_aniversario_dados,
	datatype: 'local',
	colNames:['Usuário','Praça','Data','Telefone'],
	colModel :[
		{name:'usuario', index:'usuario', width:350, align:'left'}, 
		{name:'praca', index:'praca', width:130, align:'left'}, 
		{name:'aniversario', index:'aniversario', width:120, formatter:'date', formatoptions: { srcformat: "ISO8601Long", newformat: "d-m-Y" }, sorttype: 'date', align:'center' }, 
		{name:'telefone', index:'telefone', width:150, align:'left'}
	],
	rowNum: 999999999999,
	viewrecords: true,
	autowidth: true,
	height: "100%"		
});
</script>
</div>
<? } else { ?>
<div class="div_solicitacao_linhas4">
Nenhum aniversariante até o momento.
</div>
<? } ?>
<!-- fim - usuarios_aniversario -->

<br>

<table width="100%" border="0" cellspacing="0" cellpadding="0">
<tr>

<td valign="middle">
<a href="../index.php" target="_top" id="botao_geral2">Acessar o site</a>
</td>

</tr>
</table>  


</body>
</html>
<?php
mysql_free_result($usuario);
mysql_free_result($comunicado_listar);
mysql_free_result($reclamacao_aberto_suporte);
mysql_free_result($usuarios_aniversario);
 ?>
