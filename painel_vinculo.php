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

//region - usuário logado via SESSION
$colname_usuario = "-1";
if (isset($_SESSION['MM_Username'])) {
	$colname_usuario = $_SESSION['MM_Username'];
}
mysql_select_db($database_conexao, $conexao);
$query_usuario = sprintf("SELECT * FROM usuarios WHERE usuario = %s", GetSQLValueString($colname_usuario, "text"));
$usuario = mysql_query($query_usuario, $conexao) or die(mysql_error());
$row_usuario = mysql_fetch_assoc($usuario);
$totalRows_usuario = mysql_num_rows($usuario);
//endregion - end - usuário logado via SESSION

//region - usuario_atual
$colname_usuario_atual = "-1";
if (isset($_GET['IdUsuario'])) {
	$colname_usuario_atual = $_GET['IdUsuario'];
}
mysql_select_db($database_conexao, $conexao);
$query_usuario_atual = sprintf("
SELECT usuarios.* 
FROM 
	usuarios 
WHERE 
	usuarios.IdUsuario = %s
", 
GetSQLValueString($colname_usuario_atual, "int"));
$usuario_atual = mysql_query($query_usuario_atual, $conexao) or die(mysql_error());
$row_usuario_atual = mysql_fetch_assoc($usuario_atual);
$totalRows_usuario_atual = mysql_num_rows($usuario_atual);
//endregion - end - usuario_atual_atual

//region - painel_atual
$colname_painel_atual = "-1";
if (isset($_GET['IdPainel'])) {
	$colname_painel_atual = $_GET['IdPainel'];
}
mysql_select_db($database_conexao, $conexao);
$query_painel_atual = sprintf("
SELECT 
	painel.* 
FROM 
	painel 
WHERE 
	painel.IdPainel = %s
", 
GetSQLValueString($colname_painel_atual, "int"));
$painel_atual = mysql_query($query_painel_atual, $conexao) or die(mysql_error());
$row_painel_atual = mysql_fetch_assoc($painel_atual);
$totalRows_painel_atual = mysql_num_rows($painel_atual);
//endregion - end - painel_atual_atual

//region - painel_campo_atual
$colname_painel_campo_atual = "-1";
if (isset($_GET['IdPainelCampo'])) {
	$colname_painel_campo_atual = $_GET['IdPainelCampo'];
}
mysql_select_db($database_conexao, $conexao);
$query_painel_campo_atual = sprintf("
SELECT painel_campo.* 
FROM 
	painel_campo 
WHERE 
	painel_campo.IdPainel = %s and 
	painel_campo.IdPainelCampo = %s
", 
GetSQLValueString($row_painel_atual['IdPainel'], "int"),
GetSQLValueString($colname_painel_campo_atual, "int"));
$painel_campo_atual = mysql_query($query_painel_campo_atual, $conexao) or die(mysql_error());
$row_painel_campo_atual = mysql_fetch_assoc($painel_campo_atual);
$totalRows_painel_campo_atual = mysql_num_rows($painel_campo_atual);
//endregion - end - painel_campo_atual_atual

//region - painel_vinculo_listar
mysql_select_db($database_conexao, $conexao);
$query_painel_vinculo_listar = "
SELECT 
    painel_vinculo.* 
FROM 
	painel_vinculo 
WHERE 
	painel_vinculo.IdPainel = ".$row_painel_atual['IdPainel']."
ORDER BY 
    painel_vinculo.ordem ASC 
";
$painel_vinculo_listar = mysql_query($query_painel_vinculo_listar, $conexao) or die(mysql_error());
$row_painel_vinculo_listar_assoc = mysql_fetch_assoc($painel_vinculo_listar);
$totalRows_painel_vinculo_listar = mysql_num_rows($painel_vinculo_listar);
$row_painel_vinculo_listar_array = NULL;
if($totalRows_painel_vinculo_listar > 0){

    do {

        $row_painel_vinculo_listar_array[$row_painel_vinculo_listar_assoc['IdPainelVinculo']] = array(
			'IdPainelVinculo' => $row_painel_vinculo_listar_assoc['IdPainelVinculo'],
			'IdPainel' => $row_painel_vinculo_listar_assoc['IdPainel'],
			'titulo' => $row_painel_vinculo_listar_assoc['titulo'],
			'campo' => $row_painel_vinculo_listar_assoc['campo'],

			'tamanho' => $row_painel_vinculo_listar_assoc['tamanho'],
			'tipo' => $row_painel_vinculo_listar_assoc['tipo'],
			'utf8' => $row_painel_vinculo_listar_assoc['utf8'],
			'moeda' => $row_painel_vinculo_listar_assoc['moeda'],
			'alinhamento' => $row_painel_vinculo_listar_assoc['alinhamento'],
			'ordem' => $row_painel_vinculo_listar_assoc['ordem']
        );

    } while ($row_painel_vinculo_listar_assoc = mysql_fetch_assoc($painel_vinculo_listar));

}
mysql_free_result($painel_vinculo_listar);
//endregion - end - painel_vinculo_listar

$where_geral = " 1=1 ";

//region - filtros -----------------------------------------------------------------------------------------------------------------------------------------------------------

// se existe filtro de praca
if( (isset($_GET["praca"])) && ($_GET['praca'] !="") ) {
	$colname_praca = GetSQLValueString($_GET["praca"], "string");
    $where_geral .= " and usuarios.praca = '$colname_praca' ";
    $where_usuario_listar .= " and usuarios.praca = '$colname_praca' ";
} 
// end - se existe filtro de praca

// se existe filtro de usuario_responsavel
if( (isset($_GET["usuario_responsavel"])) && ($_GET['usuario_responsavel'] !="") ) {
	$colname_usuario_responsavel = $_GET['usuario_responsavel'];
    $where_geral .= " and usuarios.IdUsuario = '".$colname_usuario_responsavel."' ";
    $where_usuario_listar .= " and usuarios.IdUsuario = '".$colname_usuario_responsavel."' ";
} 
// end - se existe filtro de usuario_responsavel

//endregion end - filtros -----------------------------------------------------------------------------------------------------------------------------------------------------

$campo_filtro_data = NULL;
if(
	$row_painel_campo_atual['campo_filtro_data_padrao'] <> NULL and 
	((isset($_GET["data_inicio"]) and $_GET["data_inicio"] == NULL) or !isset($_GET["data_inicio"])) and 
	((isset($_GET["data_fim"]) and $_GET["data_fim"] == NULL) or !isset($_GET["data_fim"]))

){
	$campo_filtro_data = " and ".$row_painel_campo_atual['campo_filtro_data_padrao']." ";
} else {

	// se existe filtro de data_inicio ( somente data final )
	if( ((isset($_GET["data_fim"])) && ($_GET["data_fim"] != "")) && ($_GET["data_inicio"] == "") ) {

			// converter data em portugues para ingles
			if ( isset($_GET["data_fim"]) ) {
				$data_fim_data = substr($_GET["data_fim"],0,10);
				$data_fim_hora = " 23:59:59";
				$data_fim = implode("-",array_reverse(explode("-",$data_fim_data))).$data_fim_hora;
			}
			// converter data em portugues para ingles - fim
		
			$colname_data_fim = GetSQLValueString($data_fim, "string");
			$campo_filtro_data = " and ".$row_painel_campo_atual['campo_filtro_data']." <= '".$colname_data_fim."' ";
	}
	// end - se existe filtro de data_inicio ( somente data final )

	// se existe filtro de data_inicio ( somente data inicial )
	if( ((isset($_GET["data_inicio"])) && ($_GET["data_inicio"] != "")) && ($_GET["data_fim"] == "") ) {

			// converter data em portugues para ingles
			if ( isset($_GET["data_inicio"]) ) {
				$data_inicio_data = substr($_GET["data_inicio"],0,10);
				$data_inicio_hora = " 00:00:00";
				$data_inicio = implode("-",array_reverse(explode("-",$data_inicio_data))).$data_inicio_hora;
			}
			// converter data em portugues para ingles - fim
		
			$colname_data_inicio = GetSQLValueString($data_inicio, "string");
			$campo_filtro_data = " and ".$row_painel_campo_atual['campo_filtro_data']." >= '".$colname_data_inicio."' ";

	}
	// end - se existe filtro de data_inicio ( somente data inicial )

	// se existe filtro de data_inicio ( entre data inicial e data final )
	if( ((isset($_GET["data_inicio"])) && ($_GET["data_inicio"] != "")) && ((isset($_GET["data_fim"])) && ($_GET["data_fim"] != "")) ) {

			// converter data em portugues para ingles
			if ( isset($_GET["data_inicio"]) ) {
				$data_inicio_data = substr($_GET["data_inicio"],0,10);
				$data_inicio_hora = " 00:00:00";
				$data_inicio = implode("-",array_reverse(explode("-",$data_inicio_data))).$data_inicio_hora;
			}
			// converter data em portugues para ingles - fim

			// converter data em portugues para ingles
			if ( isset($_GET["data_fim"]) ) {
				$data_fim_data = substr($_GET["data_fim"],0,10);
				$data_fim_hora = " 23:59:59";
				$data_fim = implode("-",array_reverse(explode("-",$data_fim_data))).$data_fim_hora;
			}
			// converter data em portugues para ingles - fim
		
			$colname_data_inicio = GetSQLValueString($data_inicio, "string");
			$colname_data_fim = GetSQLValueString($data_fim, "string");

			$campo_filtro_data = " and ".$row_painel_campo_atual['campo_filtro_data']." between '$colname_data_inicio' and '$colname_data_fim' ";
	}
	// end - se existe filtro de data_inicio ( entre data inicial e data final )

}

//region - modulo
mysql_select_db($database_conexao, $conexao);

$query_modulo = sprintf("
SELECT 
	".$row_painel_atual['query_campo']." 
FROM 
    ".$row_painel_atual['modulo']." 
	".$row_painel_atual['query_join']."
WHERE 

	".$row_painel_campo_atual['campo_where'].$campo_filtro_data." and 
					
	".$row_painel_atual['query_where']." and 
	
	".$where_geral." and 
	 
	usuarios.IdUsuario = %s 

ORDER BY 
	".$row_painel_atual['modulo'].".".$row_painel_atual['modulo_id']." DESC 
",
GetSQLValueString(@$_GET['IdUsuario'], "text"));
$modulo = mysql_query($query_modulo, $conexao) or die(mysql_error());
$row_modulo = mysql_fetch_assoc($modulo);
$totalRows_modulo = mysql_num_rows($modulo);
//endregion - end - modulo
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
	body {
		overflow-y: scroll;
		/* se não é IE, então mostra a scroll vertical */
	}
</style>
<!-- <![endif]-->

<style>
	.ui-jqgrid .ui-jqgrid-btable {
		table-layout: auto;
	}
</style>

<link rel="stylesheet" type="text/css" media="screen" href="css/jquery-ui.css" />
<link rel="stylesheet" type="text/css" media="screen" href="css/ui.jqgrid.css" />
<script src="js/grid.locale-pt-br.js" type="text/javascript"></script>
<script src="js/jquery.jqGrid.src.js" type="text/javascript"></script>

</head>

<body>

	<!-- modulo -->
	<div class="div_solicitacao_linhas" id="cabecalho_modulos" style="cursor: pointer">
		<table cellspacing="0" cellpadding="0" width="100%">
			<tr>
				<td style="text-align:left">
					<? echo utf8_encode($row_painel_atual['titulo']); ?> • <? echo utf8_encode($row_painel_campo_atual['titulo']); ?> (<? echo $totalRows_modulo; ?>) • 
					<? echo utf8_encode($row_usuario_atual['nome']); ?> • <? echo utf8_encode($row_usuario_atual['praca']); ?>
				</td>

				<td style="text-align: right">
					&lt;&lt; <a onclick="parent.eval('tb_remove()')">Voltar</a>
				</td>
			</tr>
		</table>
	</div>

	<? if($totalRows_modulo > 0){ ?>
	<div id="corpo_suportes" style="cursor: pointer">
		<table id="solicitacao"></table>
		<div id="navegacao"></div>
		<script type="text/javascript">
			var dados = [
				<?php do { ?> 
					{
						<? foreach($row_painel_vinculo_listar_array AS $row_painel_vinculo_listar_key => $row_painel_vinculo_listar){ ?>

							<?
							$campo = @$row_modulo[$row_painel_vinculo_listar['campo']]; 
							
							// tipo: date
							if($row_painel_vinculo_listar['tipo']=='date'){
							if($campo!=''){$campo = date('Y-m-d', strtotime($campo));}
							}
							// fim - tipo: date
							
							// tipo: datetime
							if($row_painel_vinculo_listar['tipo']=='datetime'){
							if($campo!=''){$campo = date('Y-m-d H:i', strtotime($campo));}
							}
							// fim - tipo: datetime

							// tipo: string
							if($row_painel_vinculo_listar['tipo']=='string'){
								$campo = GetSQLValueString($campo, "string");
							}
							// fim - tipo: string
											
							// utf8
							if($row_painel_vinculo_listar['utf8']=='s'){
							$campo = utf8_encode($campo); 
							}
							// fim - utf8
							
							// moeda
							if($row_painel_vinculo_listar['moeda']=='s'){
							$campo = 'R$ '. number_format($campo, 2, ',', '.'); 
							}
							// fim - moeda
							?>

							<? echo $row_painel_vinculo_listar['campo']; ?>: "<?php echo $campo; ?>",
						<? } ?>

						visualizar: "<? echo " <a href = '".$row_painel_atual['link_arquivo']."?".$row_painel_atual['link_arquivo_id']."=".$row_modulo['id']."&padrao=sim' target = '_blank' > <img src = 'imagens/visualizar.png' border = '0' / > </a>"; ?>"
					},
				<?php } while ($row_modulo = mysql_fetch_assoc($modulo)); ?>
			];
			jQuery('#solicitacao').jqGrid({
				data: dados,
				datatype: 'local',
				colNames: [
					<? foreach($row_painel_vinculo_listar_array AS $row_painel_vinculo_listar_key => $row_painel_vinculo_listar){ ?>
					'<? echo utf8_encode($row_painel_vinculo_listar['titulo']); ?>', 
					<? } ?>

					''
				],
				colModel: [
					<? foreach($row_painel_vinculo_listar_array AS $row_painel_vinculo_listar_key => $row_painel_vinculo_listar){ ?>
						<?
						$alinhamento = 'left';
						if($row_painel_vinculo_listar['alinhamento'] == 'l'){$alinhamento = 'left';}
						if($row_painel_vinculo_listar['alinhamento'] == 'c'){$alinhamento = 'center';}
						if($row_painel_vinculo_listar['alinhamento'] == 'r'){$alinhamento = 'right';}
						?>
						{
							name: '<? echo $row_painel_vinculo_listar['campo']; ?>',
							index: '<? echo $row_painel_vinculo_listar['campo']; ?>',
							width: <? echo $row_painel_vinculo_listar['tamanho']; ?>,
							<? if($row_painel_vinculo_listar['tipo'] == "date"){ ?>
								formatter:'date', 
								formatoptions: { srcformat: "ISO8601Long", newformat: "d-m-Y" }, 
							<? } else if($row_painel_vinculo_listar['tipo'] == "datetime"){ ?>
								formatter:'date', 
								formatoptions: { srcformat: "ISO8601Long", newformat: "d-m-Y H:i" }, 
							<? } else if($row_painel_vinculo_listar['tipo'] == "int"){ ?>
								sorttype: 'integer', 
							<? } ?>
							align: '<? echo $alinhamento; ?>'
						},
					<? } ?>
					{
						name: 'visualizar',
						index: 'visualizar',
						width: 40,
						align: 'center'
					}
				],
				rowNum: 16,
				rowList: [2, 5, 10, 20, 30, 40, 50, 100, 999999],
				loadComplete: function() {
					$("option[value=999999]").text('Todos');
				},
				pager: '#navegacao',
				//sortname: 'id',
				viewrecords: true,
				//sortorder: 'desc',
				toppager: true, // aparecer a barra de navegação também no topo
				// caption:"Suportes", barra no topo
				//width: true,
				autowidth: true,
				height: "100%"
			});
		</script>
	</div>
	<? } else { ?>
	<div class="div_solicitacao_linhas4">
		Nenhuma reclamação encontrada na filtragem atual.
	</div>
	<? } ?>
	<!-- end - modulo -->

</body>

</html>
<?php
mysql_free_result($usuario);
mysql_free_result($usuario_atual);
mysql_free_result($painel_atual);
mysql_free_result($painel_campo_atual);
mysql_free_result($modulo);
?>