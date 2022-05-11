<?
$where_relatorio_fechamento = "1=1";

// controle_relatorio =================================================================================================================
if($row_usuario['controle_relatorio'] == "Y"){
	
	$where_relatorio_fechamento .= " and ( 
					 relatorio_fechamento.praca = '".$row_usuario['praca']."' or 
					 relatorio_fechamento.praca <> '".$row_usuario['praca']."'
					 ) ";
	
}
// fim - controle_relatorio ===========================================================================================================

// controle_praca =================================================================================================================
else if($row_usuario['controle_praca'] == "Y"){
	
	$where_relatorio_fechamento .= " and ( 
					 relatorio_fechamento.praca = '".$row_usuario['praca']."' 
					 ) ";
	
} 
// fim - controle_praca ===========================================================================================================

// usuario comum =================================================================================================================
else {
	
	$where_relatorio_fechamento .= " and ( 
					 relatorio_fechamento.praca = '".$row_usuario['praca']."' 
					 ) ";
	
} 
// fim - usuario comum ===========================================================================================================

// relatorio_fechamento - filtros --------------------------------------------------------------------------------------------------------------------------------------------
// se existe filtro de praca
if( (isset($_GET["praca"])) && ($_GET['praca'] !="") ) {
	$colname_relatorio_fechamento_praca = GetSQLValueString($_GET["praca"], "string");
	$where_relatorio_fechamento .= " and relatorio_fechamento.praca = '$colname_relatorio_fechamento_praca' "; 	
	$where_relatorio_fechamento_campos[] = "praca";
} 
// fim - se existe filtro de praca
// fim - relatorio_fechamento - filtros --------------------------------------------------------------------------------------------------------------------------------------

// relatorio_fechamento
mysql_select_db($database_conexao, $conexao);
$query_relatorio_fechamento = "
SELECT *
FROM relatorio_fechamento 
WHERE $where_relatorio_fechamento 
ORDER BY relatorio_fechamento.praca ASC, relatorio_fechamento.id DESC";

$relatorio_fechamento = mysql_query($query_relatorio_fechamento, $conexao) or die(mysql_error());
$row_relatorio_fechamento = mysql_fetch_assoc($relatorio_fechamento);
$totalRows_relatorio_fechamento = mysql_num_rows($relatorio_fechamento);
// fim - relatorio_fechamento
?>

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

<? mysql_free_result($relatorio_fechamento); ?>