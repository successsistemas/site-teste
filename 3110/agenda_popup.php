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

// verifica se existe o $_GET['usuario_atual']
if (isset($_GET["usuario_atual"])) { 

	// usuario_atual
	mysql_select_db($database_conexao, $conexao);
	$query_usuario_atual = sprintf("SELECT IdUsuario, nome FROM usuarios WHERE nome = %s", GetSQLValueString($_GET["usuario_atual"], "text"));
	$usuario_atual = mysql_query($query_usuario_atual, $conexao) or die(mysql_error());
	$row_usuario_atual = mysql_fetch_assoc($usuario_atual);
	$totalRows_usuario_atual = mysql_num_rows($usuario_atual);
	// fim - usuario_atual
	
	$_GET["id_usuario_atual"] = $row_usuario_atual["IdUsuario"];
	
	mysql_free_result($usuario_atual);

}
// fim - verifica se existe o $_GET['usuario_atual']

// verifica se existe o $_GET['id_usuario_atual']
$colname_agenda_IdUsuario = "-1"; 
if (isset($_GET["id_usuario_atual"])) { 
	$colname_agenda_IdUsuario = @$_GET["id_usuario_atual"];
} else {
	$colname_agenda_IdUsuario = "-1";
}
if(@$_GET["id_usuario_atual"] ==""){ $id_usuario_atual_ativo = "0";}
if(@$_GET["id_usuario_atual"] !=""){ $id_usuario_atual_ativo = "1";}
// fim - verifica se existe o $_GET['id_usuario_atual']

// verifica se existe o $_GET['data_atual']
$data_atual =  date('d-m-Y');
if (isset($_GET["data_atual"])) { // se não está vazio
	@$data_atual = $_GET["data_atual"];
}
if (isset($_GET["data_atual"]) and $_GET["data_atual"] =="") { // se está vazio
	$data_atual = date('d-m-Y');
}
if(@$_GET["data_atual"] ==""){ $data_atual_ativo = "0";}
if(@$_GET["data_atual"] !=""){ $data_atual_ativo = "1";}
// fim - verifica se existe o $_GET['data_atual']

// filtros -----------------------------------------------------------------------------------------------------------------------------------------------------------
$where = "1=1";

// se existe filtro de data_atual
if( $data_atual_ativo == "1" ) {

		// converter data em portugues para ingles
		if ( isset($_GET["data_atual"]) ) {
			$data_inicio_fim_data = substr($_GET["data_atual"],0,10);
			$data_inicio_fim = implode("-",array_reverse(explode("-",$data_inicio_fim_data)));
		}
		// converter data em portugues para ingles - fim
	
		$colname_agenda_data_inicio_fim = GetSQLValueString($data_inicio_fim, "string");
		$where .= " and agenda.data_inicio between '$colname_agenda_data_inicio_fim 00:00:00' and '$colname_agenda_data_inicio_fim 23:59:59' ";
		
}
// fim - se existe filtro de data_atual

// se NÃO existe filtro de data_atual
else {
	
		$colname_agenda_data_inicio_fim = GetSQLValueString(date('Y-m-d'), "string");
		$where .= " and agenda.data_inicio <= '$colname_agenda_data_inicio_fim 23:59:59' ";

}
// se NÃO existe filtro de data_atual

// se existe filtro de id_usuario_atual
if( $id_usuario_atual_ativo == "1" ) {

	// controle -----------------------------------------------------------
	if (
		$row_usuario['controle_solicitacao']=="Y" or 
		$row_usuario['controle_suporte']=="Y" or 
		$row_usuario['controle_prospeccao']=="Y" or 
		$row_usuario['controle_venda']=="Y"
		){
		
		$where .= " and ( 
						 agenda.id_usuario_responsavel = '".$colname_agenda_IdUsuario."'
						 )";
		
	} 
	// fim - controle -------------------------------------------------------
	
	// outros (somente sua praça) ---------------------------------------------------------------------------------------
	else {
		
		$where .= " and 
						(
						 suporte.praca = '".$row_usuario['praca']."' or 
						 prospeccao.praca = '".$row_usuario['praca']."' or 
						 venda.praca = '".$row_usuario['praca']."'
						 ) and (  
						agenda.id_usuario_responsavel = '".$colname_agenda_IdUsuario."'
						)";
	
	}
	// fim - (somente sua praça) ----------------------------------------------------------------------------------------
	
} 
// fim - se existe filtro de id_usuario_atual

// se NÃO existe filtro de id_usuario_atual
else {

	// controle -----------------------------------------------------------
	if (
		$row_usuario['controle_solicitacao']=="Y" or 
		$row_usuario['controle_suporte']=="Y" or 
		$row_usuario['controle_prospeccao']=="Y" or 
		$row_usuario['controle_venda']=="Y"
		){
		
		$where .= " and (
						 (
						 suporte.praca = '".$row_usuario['praca']."' or 
						 prospeccao.praca = '".$row_usuario['praca']."' or 
						 venda.praca = '".$row_usuario['praca']."'
						 )
						 or 
						 agenda.id_usuario_responsavel = '".$row_usuario['IdUsuario']."'
						 )";	
		
	} 
	// fim - controle -------------------------------------------------------
	
	// outros (somente sua praça) ---------------------------------------------------------------------------------------
	else {
		
		$where .= " and ( 
						 (
						 suporte.praca = '".$row_usuario['praca']."' or 
						 prospeccao.praca = '".$row_usuario['praca']."' or 
						 venda.praca = '".$row_usuario['praca']."'
						 )
						 )";
	
	}
	// fim - (somente sua praça) ----------------------------------------------------------------------------------------

}
// se NÃO existe filtro de id_usuario_atual
// fim - filtros -----------------------------------------------------------------------------------------------------------------------------------------------------

// agenda
mysql_select_db($database_conexao, $conexao);
$query_agenda1 = "SET SESSION SQL_BIG_SELECTS=1"; 
$agenda1 = mysql_query($query_agenda1, $conexao) or die(mysql_error());

$query_agenda = "
SELECT 

agenda.id_agenda, agenda.data, agenda.data_inicio, agenda.id_usuario_responsavel, agenda.descricao, 

suporte.id AS suporte_id,
suporte.empresa AS suporte_empresa, 
suporte.data_suporte AS suporte_data_suporte, 

prospeccao.id AS prospeccao_id,
prospeccao.nome_razao_social AS prospeccao_nome_razao_social, 
prospeccao.data_prospeccao AS prospeccao_data_prospeccao, 

venda.id AS venda_id,  
venda.empresa AS venda_empresa, 
venda.data_venda AS venda_data_venda, 

usuarios.nome AS usuarios_nome 

FROM agenda 

LEFT JOIN suporte ON (suporte.id = agenda.id_suporte)
LEFT JOIN prospeccao ON (prospeccao.id = agenda.id_prospeccao)
LEFT JOIN venda ON (venda.id = agenda.id_venda_treinamento or venda.id = agenda.id_venda_implantacao)
LEFT JOIN usuarios ON agenda.id_usuario_responsavel = usuarios.IdUsuario 

WHERE $where and (agenda.status = 'a' or agenda.status = 'g')
ORDER BY agenda.data_inicio ASC, agenda.data ASC
";
$agenda = mysql_query($query_agenda, $conexao) or die(mysql_error());
$row_agenda = mysql_fetch_assoc($agenda);
$totalRows_agenda = mysql_num_rows($agenda);
// fim - agenda

// usuario_atual
mysql_select_db($database_conexao, $conexao);
$query_usuario_atual = sprintf("SELECT IdUsuario, nome FROM usuarios WHERE IdUsuario = %s", GetSQLValueString($colname_agenda_IdUsuario, "int"));
$usuario_atual = mysql_query($query_usuario_atual, $conexao) or die(mysql_error());
$row_usuario_atual = mysql_fetch_assoc($usuario_atual);
$totalRows_usuario_atual = mysql_num_rows($usuario_atual);
// fim - usuario_atual

// usuario_responsavel_alterar
mysql_select_db($database_conexao, $conexao);
$query_usuario_responsavel_alterar = "SELECT IdUsuario, nome FROM usuarios WHERE status = '1' ";

// se existe filtragem por praça ou não
if(
   $row_usuario['nivel_prospeccao'] == 1 or $row_usuario['nivel_prospeccao'] == 2 or $row_usuario['nivel_prospeccao'] == 3 or 
   $row_usuario['nivel_venda'] == 1 or $row_usuario['nivel_venda'] == 2 or $row_usuario['nivel_venda'] == 3
   ){
	
	$query_usuario_responsavel_alterar .= " and usuarios.praca = '".$row_usuario['praca']."'";
	
} else {
	
	$query_usuario_responsavel_alterar .= " and 1=1";
	
}
// fim - se existe filtragem por praça ou não

$query_usuario_responsavel_alterar .= " ORDER BY nome ASC";
$usuario_responsavel_alterar = mysql_query($query_usuario_responsavel_alterar, $conexao) or die(mysql_error());
$row_usuario_responsavel_alterar = mysql_fetch_assoc($usuario_responsavel_alterar);
$totalRows_usuario_responsavel_alterar = mysql_num_rows($usuario_responsavel_alterar);
// fim - usuario_responsavel_alterar
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"> 
<html xmlns="http://www.w3.org/1999/xhtml">
<head>

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

<link rel="stylesheet" type="text/css" media="screen" href="css/jquery-ui.css" />
<link rel="stylesheet" type="text/css" media="screen" href="css/ui.jqgrid.css" />

<script type="text/javascript" src="js/jquery.js"></script>

<script type="text/ecmascript" src="js/jquery.jqGrid.src.js"></script>
<script type="text/ecmascript" src="js/grid.locale-pt-br.js"></script>
<style>
.agenda_calendario{
	border: 1px solid #999;
	text-align: center;
}
.agenda_calendario td{
	padding-top: 2px;
	padding-bottom: 2px;
	padding-left: 5px;
	padding-right: 5px;
	border: 1px solid #999;
	font-size: 12px;
}
.agenda_calendario th{
	padding-top: 5px;
	padding-bottom: 5px;
	padding-left: 5px;
	padding-right: 5px;
	border: 1px solid #999;
	font-size: 12px;
}
.agenda_calendario_navegacao{
	text-align: center; 
	padding-bottom: 10px;
	font-size: 12px;
}
</style>
<script type="text/javascript">
$(document).ready(function() {
						   
	// ocultar/exibir filtros
	$('#corpo_calendario').hide();
	$('#cabecalho_calendario').click(function() {
		$('#corpo_calendario').toggle();
	});
	// fim - ocultar/exibir fitlros
	
	// ocultar/exibir usuario_responsavel_alterar
	$('#corpo_usuario_responsavel_alterar').hide();
	$('#cabecalho_usuario_responsavel_alterar').click(function() {
		$('#corpo_usuario_responsavel_alterar').toggle();
	});
	// fim - ocultar/exibir usuario_responsavel_alterar
	
});

// correção problema ESC para sair
$(window).keyup(function(e){
	if(e.keyCode == 27){
		parent.eval('tb_remove()');
	}
});
// fim - correção problema ESC para sair
</script>
</script>
</head>
<body>
<? // echo $where; ?>

<div class="div_solicitacao_linhas" style="cursor: pointer">
<table cellspacing="0" cellpadding="0" width="100%">
	<tr>
		<td style="text-align:left">
        <!-- Usuário responsável: -->
        <? if($id_usuario_atual_ativo == "1"){ ?>
		Usuário responsável: <? echo $row_usuario_atual['nome']; ?>
        <? } else { ?>
        Usuário responsável: Geral
        <? } ?> 
        (<? echo $totalRows_agenda; ?>)
        <!-- fim - Usuário responsável: -->
        - <span id="cabecalho_usuario_responsavel_alterar" style="font-weight: normal;">Alterar</span>
        </td>
        
		<td style="text-align: right">
        <!-- Data atual: -->
        <? if($data_atual_ativo == "1"){ ?>
		Data atual: <? echo $data_atual; ?> -        
		<?
		// mostra o dia da semana por extenso
        function diasemana($data) {
            $dia =  substr("$data", 0, 2);
            $mes =  substr("$data", 3, 2);
            $ano =  substr("$data", 6, 4);			
            $diasemana = date("w", mktime(0,0,0,$mes,$dia,$ano) );
        
            switch($diasemana) {
                case"0": $diasemana = "Domingo";       break;
                case"1": $diasemana = "Segunda-Feira"; break;
                case"2": $diasemana = "Terça-Feira";   break;
                case"3": $diasemana = "Quarta-Feira";  break;
                case"4": $diasemana = "Quinta-Feira";  break;
                case"5": $diasemana = "Sexta-Feira";   break;
                case"6": $diasemana = "Sábado";        break;
            }
            echo "$diasemana";
        }
        diasemana($data_atual);
		// fim - mostra o dia da semana por extenso
        ?>
        <? } else { ?>
        Data atual: Geral
        <? } ?>
        <!-- fim - Data atual: -->
		</td>
	</tr>
</table>
</div>


<!-- usuario_responsavel_alterar -->
<div class="div_solicitacao_linhas4" id="corpo_usuario_responsavel_alterar">
<table cellspacing="0" cellpadding="0" width="100%">
	<tr>
		<td style="text-align:left">
		<script>
        //função de submit
        function enviar_usuario_responsavel_alterar(){
        document.getElementById('form_usuario_responsavel_alterar').submit();
        }
        </script>
        <form method="get" id="form_usuario_responsavel_alterar" name="form_usuario_responsavel_alterar" action="agenda_popup.php">
        
        <select id="id_usuario_atual" name="id_usuario_atual" style="width: 400px;">
        <option value="" <?php if (!(strcmp("", @$_GET['id_usuario_atual']))) {echo "selected=\"selected\"";} ?>>Escolha o usuário responsável...</option>
        <?php
        do {  
        ?>
        <option value="<?php echo $row_usuario_responsavel_alterar['IdUsuario']?>"<?php if (!(strcmp($row_usuario_responsavel_alterar['IdUsuario'], @$_GET['id_usuario_atual']))) {echo "selected=\"selected\"";} ?>><?php echo $row_usuario_responsavel_alterar['nome']?></option>
        <?php
        } while ($row_usuario_responsavel_alterar = mysql_fetch_assoc($usuario_responsavel_alterar));
        $rows = mysql_num_rows($usuario_responsavel_alterar);
        if($rows > 0) {
        mysql_data_seek($usuario_responsavel_alterar, 0);
        $row_usuario_responsavel_alterar = mysql_fetch_assoc($usuario_responsavel_alterar);
        }
        ?>
        </select>
        
        <? if($data_atual_ativo == "1"){ ?>
        	<input type="hidden" id="data_atual" name="data_atual" value="<? echo $data_atual; ?>" />
        <? } ?>
        
        <br>
		<a href="#" onclick="enviar_usuario_responsavel_alterar();" id="botao_geral2" style="width: 100px;">Filtrar</a>
        
        </form>
		</td>
	</tr>
</table>
</div>
<!-- fim - usuario_responsavel_alterar -->


<!-- calendario -->
<div class="div_solicitacao_linhas" id="cabecalho_calendario" style="cursor: pointer">
<table cellspacing="0" cellpadding="0" width="100%">
	<tr>
		<td style="text-align:left">
		Calendário
		</td>
        
		<td style="text-align: right">
		<div style="float: right; background-image:url(imagens/icone_filtro.png); width: 12px; height: 12px;"></div>
		</td>
	</tr>
</table>
</div>

<? include("funcao_agenda_calendario.php"); ?>
<div class="div_solicitacao_linhas4" style="margin-top: 5px;" id="corpo_calendario">
<table cellspacing="0" cellpadding="0" width="100%">
	<tr>
		<td style="text-align:left">
			<?
            // verifica se existe o $_GET['data_atual']
            $data_atual =  date('d-m-Y');
            if ( isset($_GET["data_atual"]) and $_GET["data_atual"]!="" ) {
              $data_atual = $_GET["data_atual"];
            }
            // fim - verifica se existe o $_GET['data_atual']
            
            $dia = substr($data_atual,0,2); 
            $mes = substr($data_atual,3,2); 
            $ano = substr($data_atual,6,4);
            ?>
            
            <!-- funcao_agenda_calendário -->
            <div style="text-align: center; padding:5px;">
            <? funcao_agenda_calendario($dia, $mes, $ano, 'agenda_popup.php?id_usuario_atual='.$row_usuario_atual['IdUsuario'].'&'); ?>
            </div>
            <!-- funcao_agenda_calendário -->
		</td>
	</tr>
</table>
</div>
<!-- fim - calendario -->


<!-- agenda -->
<? if($totalRows_agenda > 0){ ?>
<div style="cursor: pointer">
<table id="solicitacao"></table>
<div id="navegacao"></div>
<script type="text/javascript">
var data_atual_segundos = Math.round(new Date(<?php echo time() * 1000 ?>).getTime() / 1000);
var dados = [ 
<?php do { ?>
{
	id: <?php
		if($row_agenda['suporte_id']!=NULL){
			echo $row_agenda['suporte_id'];
		} else if($row_agenda['prospeccao_id']!=NULL){
			echo $row_agenda['prospeccao_id'];
		} else if($row_agenda['venda_id']!=NULL){
			echo $row_agenda['venda_id'];
		}
		?>,
	data_modulo:"<?php
		if($row_agenda['suporte_id']!=NULL){
			echo $row_agenda['suporte_data_suporte'];
		} else if($row_agenda['prospeccao_id']!=NULL){
			echo $row_agenda['prospeccao_data_prospeccao'];
		} else if($row_agenda['venda_data_venda']!=NULL){
			echo $row_agenda['venda_data_venda'];
		}
		?>",
	data_inicio:"<?php echo $row_agenda['data_inicio']; ?>",
	hora_inicio:"<?php echo $row_agenda['data_inicio']; ?>",
	data:"<?php echo $row_agenda['data']; ?>",
	usuario_responsavel: "<?php echo $row_agenda['usuarios_nome']; ?>",
	empresa:"<?php
		if($row_agenda['suporte_id']!=NULL){
			echo utf8_encode($row_agenda['suporte_empresa']);
		} if($row_agenda['prospeccao_id']!=NULL){
			echo $row_agenda['prospeccao_nome_razao_social'];
		} else if($row_agenda['venda_id']!=NULL){
			echo $row_agenda['venda_empresa'];
		}
		?>",
	descricao:"<?php echo preg_replace('/\s+/', ' ', trim($row_agenda['descricao'])); ?>",
	modulo:"<?php
		if($row_agenda['suporte_id']!=NULL){
			echo 'Suporte';
		} if($row_agenda['prospeccao_id']!=NULL){
			echo 'Prospecção';
		} else if($row_agenda['venda_id']!=NULL){
			echo 'Venda';
		}
		?>",
	visualizar:"<?php
		if($row_agenda['suporte_id']!=NULL){
			echo "<a href='suporte_editar.php?id_suporte=".$row_agenda['suporte_id']."&padrao=sim' target='_blank'><img src='imagens/visualizar.png' border='0' /></a>"; 
		} else if($row_agenda['prospeccao_id']!=NULL){
			echo "<a href='prospeccao_editar.php?id_prospeccao=".$row_agenda['prospeccao_id']."&padrao=sim' target='_blank'><img src='imagens/visualizar.png' border='0' /></a>"; 
		} else if($row_agenda['venda_id']!=NULL){
			echo "<a href='venda_editar.php?id_venda=".$row_agenda['venda_id']."&padrao=sim' target='_blank'><img src='imagens/visualizar.png' border='0' /></a>"; 
		}
		?>"
},
<?php } while ($row_agenda = mysql_fetch_assoc($agenda)); ?>
];
jQuery('#solicitacao').jqGrid({
	data:dados,
	datatype: 'local',
	colNames:['Núm','Emissão','Data','Início','Fim','Responsável','empresa','Descrição','Módulo',''],
	colModel :[ 		
		{name:'id', index:'id', width:60, sorttype: 'integer'}, // 40
		{name:'data_modulo', index:'data_modulo', width:75, sorttype: 'date', formatter:'date', formatoptions: { srcformat: "ISO8601Long", newformat: "d-m-Y" }, align:'center' },
		{name:'data_inicio', index:'data_inicio', width:70, sorttype: 'date', formatter:'date', formatoptions: { srcformat: "ISO8601Long", newformat: "d-m-Y" }, align:'center' },
		{name:'hora_inicio', index:'hora_inicio', width:60, sorttype: 'date', formatter:'date', formatoptions: { srcformat: "ISO8601Long", newformat: "H:i" }, align:'center' },
		{name:'data', index:'data', width:50, sorttype: 'date', formatter:'date', formatoptions: { srcformat: "ISO8601Long", newformat: "H:i" }, align:'center' }, 
		{name:'usuario_responsavel', index:'usuario_responsavel', width:100, align:'center'}, // 80
		{name:'empresa', index:'empresa', width:250, align:'left'}, 
		{name:'descricao', width:170, align:'left'},
		{name:'modulo', width:70, align:'left'},
		{name:'visualizar', width:20, align:'center'}
	],
	rowNum:20,
	rowList:[2,5,10,20,30,40,50,100,999999],
	loadComplete: function() {
		$("option[value=999999]").text('Todos');
	},
	pager: '#navegacao',
	toppager: true, // aparecer a barra de navegação também no topo
	multiSort: true,
	//sortname: 'data_inicio, data',
	//sortorder: 'asc',
	viewrecords: true,
	height: "100%",
	gridview: false, 
	// cores/atrasos
	afterInsertRow: function(rowid, rowdata, rowelem ) {
		
		// data
		var data = rowdata['data'];
		var quebraDI = data.split("-");
		var anoDI = quebraDI[0];
		var mesDI = quebraDI[1] - 1;
		var diaDI = quebraDI[2].substr(0,2);
		var time_inicial = quebraDI[2].substr(3,8);
		var quebraTimeDI=time_inicial.split(":");
		var horaDI = quebraTimeDI[0];
		var minutoDI = quebraTimeDI[1];
		var segundoDI = quebraTimeDI[2];
		var data = new Date(anoDI, mesDI, diaDI, horaDI, minutoDI, segundoDI, 00);
		// fim - data

		var data_segundos = Math.round(data.getTime() / 1000);
		var diferenca = (data_atual_segundos - data_segundos);
		
		if(diferenca >= 86400 && diferenca < 172800){
			$(this).jqGrid('setRowData', rowid, false, { color: 'orange' });
		} else if(diferenca >= 172800){
			$(this).jqGrid('setRowData', rowid, false, { color: 'red' });
		} else {
			$(this).jqGrid('setRowData', rowid, false, { color: 'black' });
		}
			
	}
	// fim - cores/atrasos
});	
</script>
</div>
<? } else { ?>
<div class="div_solicitacao_linhas4">
Nenhum registro encontrado na filtragem atual.
</div>
<? } ?>
<!-- fim - agenda -->


<div style="margin-top: 10px;">

<!-- mudar a data_atual (soma um dia/subtrai um dia) -->
<?
$dia = substr($data_atual,0,2); $mes = substr($data_atual,3,2); $ano = substr($data_atual,6,4); // separando a data em dia/mes/ano (data em formato brasileiro)
$data_ant = date('d-m-Y', strtotime("$ano-$mes-$dia")+3600*24*-1); // converte a data acima em segundos e soma *dias
$data_pro = date('d-m-Y', strtotime("$ano-$mes-$dia")+3600*24*1); // converte a data acima em segundos e soma *dias
?>
<a href="agenda_popup.php?id_usuario_atual=<? echo $row_usuario_atual['IdUsuario']; ?>&data_atual=<? echo $data_ant; ?>" class="botao_geral2" style="width: 150px">&lt;&lt; anterior</a>

<a href="agenda_popup.php?id_usuario_atual=<? echo $row_usuario_atual['IdUsuario']; ?>&data_atual=<? echo $data_pro; ?>" class="botao_geral2" style="width: 150px">próximo &gt;&gt;</a>
<!-- fim - mudar a data_atual (soma um dia/subtrai um dia) -->

<!-- Imprimir -->
<script>
//função de submit
function enviar(){
document.getElementById('form').submit();
}
</script>
<form action="agenda_imprimir.php" method="post" target="_blank" id="form" name="form">

<fieldset style="border: 0px;">
    <input value="id" type="hidden" name="relatorio_campos[]" />
    <input value="data_modulo" type="hidden" name="relatorio_campos[]" />
    <input value="data_inicio" type="hidden" name="relatorio_campos[]" />
    <input value="data" type="hidden" name="relatorio_campos[]" />
    <input value="id_usuario_responsavel" type="hidden" name="relatorio_campos[]" />
    <input value="empresa" type="hidden" name="relatorio_campos[]" />
    <input value="descricao" type="hidden" name="relatorio_campos[]" />
</fieldset>

<input type="hidden" name="where" id="where" value="<?  echo @$where; ?>">
<a href="#" onclick="enviar();" id="botao_geral2" style="width: 150px;">Imprimir</a>

</form>
<!-- fim - Imprimir -->

</div>

</body>

</html>
<?php
mysql_free_result($usuario);
mysql_free_result($agenda);
mysql_free_result($usuario_atual);
mysql_free_result($usuario_responsavel_alterar);
?>