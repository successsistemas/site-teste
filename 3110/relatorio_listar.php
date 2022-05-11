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

$currentPage = $_SERVER["PHP_SELF"];

require_once('parametros.php');
require_once('funcao_dia_util.php');

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

if($row_usuario['controle_relatorio'] != "Y" and $row_usuario['controle_praca'] != "Y" ){
	header("Location: painel/index.php");
	exit;
}

// filtro praca
mysql_select_db($database_conexao, $conexao);
$query_filtro_praca = "SELECT praca FROM geral_tipo_praca ORDER BY praca ASC";
$filtro_praca = mysql_query($query_filtro_praca, $conexao) or die(mysql_error());
$row_filtro_praca = mysql_fetch_assoc($filtro_praca);
$totalRows_filtro_praca = mysql_num_rows($filtro_praca);
// fim - filtro praca

// filtro usuario_responsavel
mysql_select_db($database_conexao, $conexao);
$query_filtro_usuario_responsavel = "SELECT IdUsuario, nome FROM usuarios ORDER BY nome ASC";
$filtro_usuario_responsavel = mysql_query($query_filtro_usuario_responsavel, $conexao) or die(mysql_error());
$row_filtro_usuario_responsavel = mysql_fetch_assoc($filtro_usuario_responsavel);
$totalRows_filtro_usuario_responsavel = mysql_num_rows($filtro_usuario_responsavel);	
// fim - filtro usuario_responsavel

mysql_select_db($database_conexao, $conexao);

$where = "1=1";

// controle_relatorio =================================================================================================================
if($row_usuario['controle_relatorio'] == "Y"){
	
	$where .= " and ( 
					 relatorio_fechamento.praca = '".$row_usuario['praca']."' or 
					 relatorio_fechamento.praca <> '".$row_usuario['praca']."'
					 ) ";
	
}
// fim - controle_relatorio ===========================================================================================================

// controle_praca =================================================================================================================
else if($row_usuario['controle_praca'] == "Y"){
	
	$where .= " and ( 
					 relatorio_fechamento.praca = '".$row_usuario['praca']."' 
					 ) ";
	
} 
// fim - controle_praca ===========================================================================================================

// usuario comum =================================================================================================================
else {
	
	$where .= " and ( 
					 relatorio_fechamento.praca = '".$row_usuario['praca']."' 
					 ) ";
	
} 
// fim - usuario comum ===========================================================================================================


$where_usuario_logado = $where; // para o filtro por id (elimina todos os outros filtros)

// relatorio_fechamento - filtros --------------------------------------------------------------------------------------------------------------------------------------------
// se existe filtro de praca
if( (isset($_GET["praca"])) && ($_GET['praca'] !="") ) {
	$colname_relatorio_fechamento_praca = GetSQLValueString($_GET["praca"], "string");
	$where .= " and relatorio_fechamento.praca = '$colname_relatorio_fechamento_praca' "; 	
	$where_campos[] = "praca";
} 
// fim - se existe filtro de praca

// se existe filtro de usuario_responsavel
if( (isset($_GET["usuario_responsavel"])) && ($_GET['usuario_responsavel'] !="") ) {
	$colname_relatorio_fechamento_usuario_responsavel = $_GET['usuario_responsavel'];
	$where .= " and relatorio_fechamento.id_usuario = '".$colname_relatorio_fechamento_usuario_responsavel."' ";
	$where_campos[] = "usuario_responsavel";
} 
// fim - se existe filtro de usuario_responsavel

// se existe filtro de id
if( (isset($_GET["id"])) && ($_GET['id'] !="") ) {
	$colname_relatorio_fechamento_id = GetSQLValueString($_GET["id"], "int");
	$where = $where_usuario_logado." and relatorio_fechamento.id = '$colname_relatorio_fechamento_id' ";
	$where_campos[] = "id";
}
// fim - se existe filtro de id

// se existe filtro de data_criacao ( somente data final )
if( ((isset($_GET["data_criacao_fim"])) && ($_GET["data_criacao_fim"] != "")) && ($_GET["data_criacao_inicio"] == "") ) {

		// converter data em portugues para ingles
		if ( isset($_GET["data_criacao_fim"]) ) {
			$data_criacao_fim_data = substr($_GET["data_criacao_fim"],0,10);
			$data_criacao_fim_hora = " 23:59:59";
			$data_criacao_fim = implode("-",array_reverse(explode("-",$data_criacao_fim_data))).$data_criacao_fim_hora;
			$where_campos[] = "data_criacao_fim";
		}
		// converter data em portugues para ingles - fim
	
		$colname_relatorio_fechamento_data_criacao_fim = GetSQLValueString($data_criacao_fim, "string");
		$where .= " and relatorio_fechamento.data_criacao <= '".$colname_relatorio_fechamento_data_criacao_fim."' ";
}
// fim - se existe filtro de data_criacao ( somente data final )

// se existe filtro de data_criacao ( somente data inicial )
if( ((isset($_GET["data_criacao_inicio"])) && ($_GET["data_criacao_inicio"] != "")) && ($_GET["data_criacao_fim"] == "") ) {

		// converter data em portugues para ingles
		if ( isset($_GET["data_criacao_inicio"]) ) {
			$data_criacao_inicio_data = substr($_GET["data_criacao_inicio"],0,10);
			$data_criacao_inicio_hora = " 00:00:00";
			$data_criacao_inicio = implode("-",array_reverse(explode("-",$data_criacao_inicio_data))).$data_criacao_inicio_hora;
			$where_campos[] = "data_criacao_inicio";
		}
		// converter data em portugues para ingles - fim
	
		$colname_relatorio_fechamento_data_criacao_inicio = GetSQLValueString($data_criacao_inicio, "string");
		$where .= " and relatorio_fechamento.data_criacao >= '".$colname_relatorio_fechamento_data_criacao_inicio."' ";
}
// fim - se existe filtro de data_criacao ( somente data inicial )

// se existe filtro de data_criacao ( entre data inicial e data final )
if( ((isset($_GET["data_criacao_inicio"])) && ($_GET["data_criacao_inicio"] != "")) && ((isset($_GET["data_criacao_fim"])) && ($_GET["data_criacao_fim"] != "")) ) {

		// converter data em portugues para ingles
		if ( isset($_GET["data_criacao_inicio"]) ) {
			$data_criacao_inicio_data = substr($_GET["data_criacao_inicio"],0,10);
			$data_criacao_inicio_hora = " 00:00:00";
			$data_criacao_inicio = implode("-",array_reverse(explode("-",$data_criacao_inicio_data))).$data_criacao_inicio_hora;
			$where_campos[] = "data_criacao_inicio";
		}
		// converter data em portugues para ingles - fim

		// converter data em portugues para ingles
		if ( isset($_GET["data_criacao_fim"]) ) {
			$data_criacao_fim_data = substr($_GET["data_criacao_fim"],0,10);
			$data_criacao_fim_hora = " 23:59:59";
			$data_criacao_fim = implode("-",array_reverse(explode("-",$data_criacao_fim_data))).$data_criacao_fim_hora;
			$where_campos[] = "data_criacao_fim";
		}
		// converter data em portugues para ingles - fim
	
		$colname_relatorio_fechamento_data_criacao_inicio = GetSQLValueString($data_criacao_inicio, "string");
		$colname_relatorio_fechamento_data_criacao_fim = GetSQLValueString($data_criacao_fim, "string");

		$where .= " and relatorio_fechamento.data_criacao between '$colname_relatorio_fechamento_data_criacao_inicio' and '$colname_relatorio_fechamento_data_criacao_fim' "; 
}
// fim - se existe filtro de data_criacao ( entre data inicial e data final )
// fim - relatorio_fechamento - filtros --------------------------------------------------------------------------------------------------------------------------------------

// relatorio_fechamento
$query_relatorio_fechamento = "
SELECT *
FROM relatorio_fechamento 
WHERE $where 
ORDER BY relatorio_fechamento.praca ASC, relatorio_fechamento.id DESC";

$relatorio_fechamento = mysql_query($query_relatorio_fechamento, $conexao) or die(mysql_error());
$row_relatorio_fechamento = mysql_fetch_assoc($relatorio_fechamento);
$totalRows_relatorio_fechamento = mysql_num_rows($relatorio_fechamento);
// fim - relatorio_fechamento


?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"> 
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link href="css/suporte.css" rel="stylesheet" type="text/css">
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

<script type="text/javascript" src="js/jquery.js"></script>

<script type="text/javascript" src="js/jquery.maskedinput-1.2.2.min.js"></script> 

<link rel="stylesheet" type="text/css" media="screen" href="css/jquery-ui.css" />
<link rel="stylesheet" type="text/css" media="screen" href="css/ui.jqgrid.css" />
<script src="js/grid.locale-pt-br.js" type="text/javascript"></script>
<script src="js/jquery.jqGrid.src.js" type="text/javascript"></script>

<script type="text/javascript">
$.jgrid.no_legacy_api = true;

$(document).ready(function(){
	
	// mascara
	$('#data_criacao_inicio').mask('99-99-9999',{placeholder:" "});
	$('#data_criacao_fim').mask('99-99-9999',{placeholder:" "});
	// mascara - fim
	
	// ocultar/exibir filtros
	$('#corpo_relatorio_fechamento_filtro').toggle();
	$('#cabecalho_relatorio_fechamento_filtro').click(function() {
		$('#corpo_relatorio_fechamento_filtro').toggle();
	});
	// fim - ocultar/exibir fitlros
		
	// ocultar/exibir relatorio_fechamentos
	//$('#corpo_relatorio_fechamentos').toggle();
	$('#cabecalho_relatorio_fechamento').click(function() {
		$('#corpo_relatorio_fechamento').toggle();
	});
	// fim - ocultar/exibir relatorio_fechamentos

});

// limpar formulário do filtro
function clear_form_elements(ele) {

    $(ele).find(':input').each(function() {
        switch(this.type) {
            case 'password':
            case 'select-multiple':
            case 'select-one':
            case 'text':
            case 'textarea':
                $(this).val('');
                break;
            case 'checkbox':
            case 'radio':
                this.checked = false;
        }
    });

}
// fim - limpar formulário do filtro
</script>
<title>Resultados Mensais</title>
</head>

<body>

<!-- barra superior -->
<div class="div_solicitacao_linhas">
<table cellspacing="0" cellpadding="0" width="100%">
	<tr>
		<td style="text-align:left">
        
        <a href="relatorio.php?padrao=sim&amp;tela=digital&amp;relatorio_id_grupo=1&amp;relatorio_id_grupo_subgrupo=0" style="color: #D1E3F1">Relatórios de solicitação</a>
        
        <font color="#3399CC"> | </font>
        
        <a href="relatorio.php?padrao=sim&amp;tela=digital&amp;relatorio_id_grupo=2&amp;relatorio_id_grupo_subgrupo=0" style="color: #D1E3F1">Relatórios de suporte</a>
        
        <font color="#3399CC"> | </font>
        
        <a href="relatorio.php?padrao=sim&amp;tela=digital&amp;relatorio_id_grupo=3&amp;relatorio_id_grupo_subgrupo=0" style="color: #D1E3F1">Relatórios de prospecção</a>
        
        <font color="#3399CC"> | </font>
        
        <a href="relatorio.php?padrao=sim&amp;tela=digital&amp;relatorio_id_grupo=4&amp;relatorio_id_grupo_subgrupo=0" style="color: #D1E3F1">Relatórios de venda</a>
        
        <font color="#3399CC"> | </font>
        
        <a href="relatorio.php?padrao=sim&amp;tela=digital&amp;relatorio_id_grupo=5&amp;relatorio_id_grupo_subgrupo=0" style="color: #D1E3F1">Relatórios administrativos</a>   
        
        <font color="#3399CC"> | </font>
        
        <a href="relatorio.php?padrao=sim&amp;tela=digital&amp;filtro_geral_praca=<? if($row_usuario['controle_relatorio'] != "Y"){ echo $row_usuario['praca']; } ?>&amp;filtro_geral_data_criacao=<? echo date('01-m-Y'); ?>&amp;filtro_geral_data_criacao_fim=<? echo date('t-m-Y'); ?>" style="color: #D1E3F1">Relatório geral</a>  
        
        <font color="#3399CC"> | </font>
        
        Resultados Mensais
		</td>

		<td style="text-align: right">
		&lt;&lt; <a href="index.php">Voltar</a> | 
		Usuário logado: <? echo $row_usuario['nome']; ?> |
		<a href="painel/padrao_sair.php">Sair</a>
        </td>
	</tr>
</table>
</div>
<!-- fim - barra superior -->

<div class="div_solicitacao_linhas2">
Clique sobre a opção desejada para visualizar mais informações.
</div>

<!-- filtros -->
<div class="div_solicitacao_linhas" id="cabecalho_relatorio_fechamento_filtro" style="cursor: pointer">
<table cellspacing="0" cellpadding="0" width="100%">
	<tr>
		<td style="text-align:left">
		Filtros
		</td>
        
		<td style="text-align: right">
		<div style="float: right; background-image:url(imagens/icone_filtro.png); width: 12px; height: 12px;"></div>
		</td>
	</tr>
</table>
</div>

<form name="buscar" action="relatorio_listar.php" method="GET">
<div id="corpo_relatorio_fechamento_filtro">

	<div style="border: 1px solid #c5dbec; margin-bottom: 5px;">
    
        <div class="div_filtros">
        <table cellspacing="0" cellpadding="0" width="100%">
            <tr>
                <td style="text-align:left" width="400">
                <span class="label_solicitacao">Praça: </span>
                <select name="praca">
                <option value=""
                <?php if (!(strcmp("", isset($_GET['praca'])))) {echo "selected=\"selected\"";} ?>
                >
                Escolha ...
                </option>
                <?php do {  ?>
                <option value="<?php echo $row_filtro_praca['praca']?>"
                <?php if ( (isset($_GET['praca'])) and (!(strcmp($row_filtro_praca['praca'], $_GET['praca']))) ) {echo "selected=\"selected\"";} ?>
                >
                <?php echo $row_filtro_praca['praca']?>
                </option>
                <?php
                } while ($row_filtro_praca = mysql_fetch_assoc($filtro_praca));
                $rows = mysql_num_rows($filtro_praca);
                if($rows > 0) {
                mysql_data_seek($filtro_praca, 0);
                $row_filtro_praca = mysql_fetch_assoc($filtro_praca);
                }
                ?>
                </select>
				</td>
                
                <td style="text-align:right">
                <span class="label_solicitacao">Responsável: </span>
                <select name="usuario_responsavel" style="width: 380px">
                <option value=""
                <?php if (!(strcmp("", isset($_GET['filtro_usuario_responsavel'])))) {echo "selected=\"selected\"";} ?>>Escolha ...</option>
                <?php do {  ?>
                <option value="<?php echo $row_filtro_usuario_responsavel['IdUsuario']; ?>"
                <?php if ( (isset($_GET['usuario_responsavel'])) and (!(strcmp($row_filtro_usuario_responsavel['IdUsuario'], $_GET['usuario_responsavel']))) ) {echo "selected=\"selected\"";} ?>
                >
                <?php echo utf8_encode($row_filtro_usuario_responsavel['nome']); ?>
                </option>
                <?php
                } while ($row_filtro_usuario_responsavel = mysql_fetch_assoc($filtro_usuario_responsavel));
                $rows = mysql_num_rows($filtro_usuario_responsavel);
                if($rows > 0) {
                mysql_data_seek($filtro_usuario_responsavel, 0);
                $row_filtro_usuario_responsavel = mysql_fetch_assoc($filtro_usuario_responsavel);
                }
                ?>
                </select>
                </td>
            </tr>
        </table>
        </div>
                        
        <div class="div_filtros2">
        <table cellspacing="0" cellpadding="0" width="100%">
        <tr>
        <td style="text-align:left">
        <span class="label_solicitacao">Data criação (inicial): </span>
        <input name="data_criacao_inicio" id="data_criacao_inicio" type="text" value="<? 
        if ( isset($_GET['data_criacao_inicio']) ){ echo $_GET['data_criacao_inicio']; }
        ?>" />
        </td>
        
        <td style="text-align:right" class="div_filtros_relatorio_fechamentos_corpo_td">
        <span class="label_solicitacao">Data criação (final): </span>
        <input name="data_criacao_fim" id="data_criacao_fim" type="text" value="<? 
        if ( isset($_GET['data_criacao_fim']) ){ echo $_GET['data_criacao_fim']; }
        ?>" />
        </td>
        </tr>
        </table>
        </div>
        
	</div>

    <div class="div_filtros">
    <table cellspacing="0" cellpadding="0" width="100%">
        <tr>
            <td style="text-align:left">
            <input name="Filtrar" type="submit" value="Filtrar" class="botao_geral2" style="width: 100px" />
            <input onclick="clear_form_elements(this.form)" type="button" value="Limpar filtro" class="botao_geral2" style="width: 100px" />
            </td>
        </tr>
    </table>
    </div>
        
        
</div>
</form>
<!-- fim - filtros -->


<!-- relatorio_fechamento -->
<? if($totalRows_relatorio_fechamento > 0){ ?>
<div class="div_solicitacao_linhas" id="cabecalho_relatorio_fechamento" style="cursor: pointer">
<table cellspacing="0" cellpadding="0" width="100%">
	<tr>
		<td style="text-align:left">
		Resultados Mensais (<? echo $totalRows_relatorio_fechamento; ?>)
		</td>
        
		<td style="text-align: right">
		<div style="float: right; background-image:url(imagens/icone_filtro.png); width: 12px; height: 12px;"></div>
		</td>
	</tr>
</table>
</div>

<div id="corpo_relatorio_fechamento" style="cursor: pointer">
<table id="relatorio_fechamento"></table>
<div id="relatorio_fechamento_navegacao"></div>
<script type="text/javascript">
var dados = [		 
<?php do { ?>

<?
// status ------------------------------------------------------------------------------------------------------------------------------------
$cor_css = "cor_black";

if($row_relatorio_fechamento['status'] == 0){
	$cor_css = "cor_red";
}
// fim - status ------------------------------------------------------------------------------------------------------------------------------
?>

{
	id:"<?php echo $row_relatorio_fechamento['id']; ?>",
	data_criacao:"<?php echo $row_relatorio_fechamento['data_criacao']; ?>",
	data:"<?php echo date('d-m-Y', strtotime($row_relatorio_fechamento['data'])); ?>",
	<? if($row_usuario['controle_relatorio'] == "Y"){ ?>
	praca:"<?php echo $row_relatorio_fechamento['praca']; ?>",
	<? } ?>
	usuario_responsavel:"<?php echo $row_relatorio_fechamento['usuario_responsavel']; ?>",
	arquivo:"<?php echo $row_relatorio_fechamento['arquivo']; ?>",
	visualizar:"<? echo "<a href='relatorio/".$row_relatorio_fechamento['arquivo']."' target='_blank'><img src='imagens/visualizar.png' border='0' /></a>"; ?>"
},
<?php } while ($row_relatorio_fechamento = mysql_fetch_assoc($relatorio_fechamento)); ?>
];
jQuery('#relatorio_fechamento').jqGrid({
	data:dados,
	datatype: 'local',
	colNames:['Núm','Data criação','Refêrencia'<? if($row_usuario['controle_relatorio'] == "Y"){ ?>,'Praça'<? } ?>,'Responsável','Arquivo',''],
	colModel :[ 
		{name:'id', index:'id', width:30, sorttype: 'integer'}, 
		{name:'data_criacao', index:'data_criacao', width:70, formatter:'date', formatoptions:{newformat:'ISO8601Long'}, align:'center' },
		{name:'data', index:'data', width:70, formatter:'date', formatoptions:{srcformat:'ISO8601Long', newformat:'m-Y'}, align:'center' },
		<? if($row_usuario['controle_relatorio'] == "Y"){ ?>
		{name:'praca', index:'praca', width: 80, align:'center'},
		<? } ?>
		{name:'usuario_responsavel', index:'usuario_responsavel', width:70, align:'left'}, 
		{name:'arquivo', index:'arquivo'}, 
		{name:'visualizar', index:'visualizar', width:20, align:'center'} 
	],
	rowNum:20,
	rowList:[2,5,10,20,30,40,50,100,999999],
				loadComplete: function() {
					$("option[value=999999]").text('Todos');
				},
	pager: '#relatorio_fechamento_navegacao',
	//sortname: 'id',
	viewrecords: true,
	//sortorder: 'desc',
	toppager: true, // aparecer a barra de navegação também no topo
	// caption:"Suportes", barra no topo
	autowidth: true,
	height: "100%",

	ondblClickRow: function(id){
		top.location.href="relatorio_fechamento_editar.php?id_relatorio_fechamento="+id;
	}		
});
</script>
</div>
<? } else { ?>
<div class="div_solicitacao_linhas4">
Nenhuma relatório encontrado na filtragem atual.

</div>
<? } ?>
<!-- fim - relatorio_fechamento -->

</body>

</html>

<?php
mysql_free_result($usuario);
mysql_free_result($filtro_praca);
mysql_free_result($filtro_usuario_responsavel);
mysql_free_result($relatorio_fechamento);
?>