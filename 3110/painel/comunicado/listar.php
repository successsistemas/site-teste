<?php require_once('../padrao_restrito.php'); ?>
<?php require_once('../../Connections/conexao.php'); ?>
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

// filtro_usuario_IdUsuario
mysql_select_db($database_conexao, $conexao);
$query_filtro_usuario_IdUsuario = "SELECT IdUsuario, nome FROM usuarios WHERE status = 1 ORDER BY nome ASC";
$filtro_usuario_IdUsuario = mysql_query($query_filtro_usuario_IdUsuario, $conexao) or die(mysql_error());
$row_filtro_usuario_IdUsuario = mysql_fetch_assoc($filtro_usuario_IdUsuario);
$totalRows_filtro_usuario_IdUsuario = mysql_num_rows($filtro_usuario_IdUsuario);	
// fim - filtro_usuario_IdUsuario

if(
$row_usuario['controle_comunicado'] <> 'Y' and 
$row_usuario['controle_memorando'] <> 'Y' 
){
	header("Location: ../index.php"); 
	exit;
}

$aba = "recebidos";
if($row_usuario['controle_comunicado'] == "Y"){
	if (isset($_GET['aba']) and $_GET['aba'] == "enviados") {
	  $aba = "enviados";
	}
}

// Load the common classes
require_once('../../includes/common/KT_common.php');

// Load the required classes
require_once('../../includes/tfi/TFI.php');
require_once('../../includes/tso/TSO.php');
require_once('../../includes/nav/NAV.php');

// Make unified connection variable
$conn_conexao = new KT_connection($conexao, $database_conexao);

// Filter
$tfi_listcomunicado1 = new TFI_TableFilter($conn_conexao, "tfi_listcomunicado1");
$tfi_listcomunicado1->Execute();

// Sorter
$tso_listcomunicado1 = new TSO_TableSorter("rscomunicado1", "tso_listcomunicado1");
$tso_listcomunicado1->addColumn("comunicado.IdComunicado");
$tso_listcomunicado1->addColumn("comunicado.data_criacao");
$tso_listcomunicado1->addColumn("usuarios.nome");
$tso_listcomunicado1->addColumn("comunicado.assunto");
$tso_listcomunicado1->addColumn("comunicado.prioridade");
$tso_listcomunicado1->setDefault("comunicado.IdComunicado DESC");
$tso_listcomunicado1->Execute();

// Navigation
$nav_listcomunicado1 = new NAV_Regular("nav_listcomunicado1", "rscomunicado1", "../../", $_SERVER['PHP_SELF'], 50);

//NeXTenesio3 Special List Recordset
$maxRows_rscomunicado1 = $_SESSION['max_rows_nav_listcomunicado1'];
$pageNum_rscomunicado1 = 0;
if (isset($_GET['pageNum_rscomunicado1'])) {
  $pageNum_rscomunicado1 = $_GET['pageNum_rscomunicado1'];
}
$startRow_rscomunicado1 = $pageNum_rscomunicado1 * $maxRows_rscomunicado1;

// Defining List Recordset variable
$NXTFilter_rscomunicado1 = "1=1";
if (isset($_SESSION['filter_tfi_listcomunicado1'])) {
  $NXTFilter_rscomunicado1 = $_SESSION['filter_tfi_listcomunicado1'];
}
// Defining List Recordset variable
$NXTSort_rscomunicado1 = "comunicado.data_reenvio IS NULL ASC, comunicado.data_criacao DESC, comunicado.IdComunicado DESC";
if (isset($_SESSION['sorter_tso_listcomunicado1'])) {
  $NXTSort_rscomunicado1 = $_SESSION['sorter_tso_listcomunicado1'];
}

mysql_select_db($database_conexao, $conexao);

$where = "1=1";

// comunicado - filtros ----------------------------------------------------------------------------------------------------------------------------------------------

// se existe filtro de filtro_IdComunicado
if( (isset($_GET["filtro_IdComunicado"])) && ($_GET['filtro_IdComunicado'] !="") ) {
	$colname_filtro_IdComunicado = GetSQLValueString($_GET["filtro_IdComunicado"], "int");
	$where .= " and comunicado.IdComunicado = '$colname_filtro_IdComunicado' ";
	$where_campos[] = "filtro_IdComunicado";
}
// fim - se existe filtro de filtro_IdComunicado

// se existe filtro de filtro_prioridade
if( (isset($_GET["filtro_prioridade"])) && ($_GET['filtro_prioridade'] !="") ) {
	$colname_filtro_prioridade = GetSQLValueString($_GET["filtro_prioridade"], "string");
	$where .= " and comunicado.prioridade = '$colname_filtro_prioridade' ";
	$where_campos[] = "filtro_prioridade";
}
// fim - se existe filtro de filtro_prioridade


// se existe filtro de filtro_assunto
if( (isset($_GET["filtro_assunto"])) && ($_GET['filtro_assunto'] !="") ) {
	$colname_filtro_assunto = GetSQLValueString($_GET["filtro_assunto"], "string");
	$where .= " and comunicado.assunto LIKE '%$colname_filtro_assunto%' ";
	$where_campos[] = "filtro_assunto";
}
// fim - se existe filtro de filtro_assunto

// se existe filtro de filtro_usuario_IdUsuario
if( (isset($_GET["filtro_usuario_IdUsuario"])) && ($_GET['filtro_usuario_IdUsuario'] !="") ) {
	$colname_filtro_usuario_IdUsuario = GetSQLValueString($_GET["filtro_usuario_IdUsuario"], "string");
	$where .= " and usuarios.IdUsuario = $colname_filtro_usuario_IdUsuario ";
	$where_campos[] = "filtro_usuario_IdUsuario";
}
// fim - se existe filtro de filtro_usuario_IdUsuario

// se existe filtro de filtro_tipo
if( (isset($_GET["filtro_tipo"])) && ($_GET['filtro_tipo'] !="") ) {
	$colname_filtro_tipo = GetSQLValueString($_GET["filtro_tipo"], "string");
	$where .= " and comunicado.tipo = '$colname_filtro_tipo' ";
	$where_campos[] = "filtro_tipo";
}
// fim - se existe filtro de filtro_tipo

// se existe filtro de filtro_lido
if( (isset($_GET["filtro_lido"])) && ($_GET['filtro_lido'] !="") ) {
	$colname_filtro_lido = GetSQLValueString($_GET["filtro_lido"], "string");
	
	if($aba == "recebidos"){ 
		$where .= " and comunicado_destinatario.lido = '$colname_filtro_lido' ";
	$where_campos[] = "filtro_lido";
	} else {
		$where .= " and comunicado.lido = '$colname_filtro_lido' ";
	}
}
// fim - se existe filtro de filtro_lido

// se existe filtro de data_criacao ( somente data final )
if( ((isset($_GET["filtro_data_criacao_fim"])) && ($_GET["filtro_data_criacao_fim"] != "")) && ($_GET["filtro_data_criacao_inicio"] == "") ) {

		// converter data em portugues para ingles
		if ( isset($_GET["filtro_data_criacao_fim"]) ) {
			$data_criacao_fim_data = substr($_GET["filtro_data_criacao_fim"],0,10);
			$data_criacao_fim_hora = " 23:59:59";
			$data_criacao_fim = implode("-",array_reverse(explode("-",$data_criacao_fim_data))).$data_criacao_fim_hora;
			$where_campos[] = "data_criacao_fim";
		}
		// converter data em portugues para ingles - fim
	
		$colname_comunicado_data_criacao_fim = GetSQLValueString($data_criacao_fim, "string");
		$where .= " and comunicado.data_criacao <= '".$colname_comunicado_data_criacao_fim."' ";
}
// fim - se existe filtro de data_criacao ( somente data final )

// se existe filtro de data_criacao ( somente data inicial )
if( ((isset($_GET["filtro_data_criacao_inicio"])) && ($_GET["filtro_data_criacao_inicio"] != "")) && ($_GET["filtro_data_criacao_fim"] == "") ) {

		// converter data em portugues para ingles
		if ( isset($_GET["filtro_data_criacao_inicio"]) ) {
			$data_criacao_inicio_data = substr($_GET["filtro_data_criacao_inicio"],0,10);
			$data_criacao_inicio_hora = " 00:00:00";
			$data_criacao_inicio = implode("-",array_reverse(explode("-",$data_criacao_inicio_data))).$data_criacao_inicio_hora;
			$where_campos[] = "data_criacao_inicio";
		}
		// converter data em portugues para ingles - fim
	
		$colname_comunicado_data_criacao_inicio = GetSQLValueString($data_criacao_inicio, "string");
		$where .= " and comunicado.data_criacao >= '".$colname_comunicado_data_criacao_inicio."' ";
}
// fim - se existe filtro de data_criacao ( somente data inicial )

// se existe filtro de data_criacao ( entre data inicial e data final )
if( ((isset($_GET["filtro_data_criacao_inicio"])) && ($_GET["filtro_data_criacao_inicio"] != "")) && ((isset($_GET["filtro_data_criacao_fim"])) && ($_GET["filtro_data_criacao_fim"] != "")) ) {

		// converter data em portugues para ingles
		if ( isset($_GET["filtro_data_criacao_inicio"]) ) {
			$data_criacao_inicio_data = substr($_GET["filtro_data_criacao_inicio"],0,10);
			$data_criacao_inicio_hora = " 00:00:00";
			$data_criacao_inicio = implode("-",array_reverse(explode("-",$data_criacao_inicio_data))).$data_criacao_inicio_hora;
			$where_campos[] = "data_criacao_inicio";
		}
		// converter data em portugues para ingles - fim

		// converter data em portugues para ingles
		if ( isset($_GET["filtro_data_criacao_fim"]) ) {
			$data_criacao_fim_data = substr($_GET["filtro_data_criacao_fim"],0,10);
			$data_criacao_fim_hora = " 23:59:59";
			$data_criacao_fim = implode("-",array_reverse(explode("-",$data_criacao_fim_data))).$data_criacao_fim_hora;
			$where_campos[] = "data_criacao_fim";
		}
		// converter data em portugues para ingles - fim
	
		$colname_comunicado_data_criacao_inicio = GetSQLValueString($data_criacao_inicio, "string");
		$colname_comunicado_data_criacao_fim = GetSQLValueString($data_criacao_fim, "string");

		$where .= " and comunicado.data_criacao between '$colname_comunicado_data_criacao_inicio' and '$colname_comunicado_data_criacao_fim' "; 
}
// fim - se existe filtro de data_criacao ( entre data inicial e data final )

// fim - comunicado - filtros ----------------------------------------------------------------------------------------------------------------------------------------

if($aba == "recebidos"){ 

$query_rscomunicado1 = "
SELECT 
	comunicado.IdComunicado, comunicado.data_criacao, comunicado.IdUsuario, comunicado.assunto, comunicado.prioridade, comunicado.data_reenvio, 
	comunicado_destinatario.lido,

	(
		SELECT 
			(
			case 
			when COUNT(comunicado_destinatario.IdComunicadoDestinatario) = 1 then usuarios.nome 
			when COUNT(comunicado_destinatario.IdComunicadoDestinatario) > 1 then concat('Coletivo (', COUNT(comunicado_destinatario.IdComunicadoDestinatario), ')')
			else 0 end 
			)
		FROM 
			comunicado_destinatario 
		LEFT JOIN 
			usuarios ON comunicado_destinatario.IdUsuario = usuarios.IdUsuario 
		WHERE 
			comunicado_destinatario.IdComunicado = comunicado.IdComunicado and IdComunicadoHistorico IS NULL and comunicado_destinatario.responsavel = 0 
		LIMIT 
			1
	) AS comunicado_destinatario_contador, 

	usuarios.nome AS usuario_nome 

FROM 
	comunicado 
LEFT JOIN 
	usuarios ON usuarios.IdUsuario = comunicado.IdUsuario 
LEFT JOIN 
	comunicado_destinatario ON comunicado.IdComunicado = comunicado_destinatario.IdComunicado and comunicado_destinatario.IdComunicadoHistorico IS NULL
WHERE 
	$where and 
	comunicado_destinatario.IdUsuario = ".$row_usuario['IdUsuario']." and 
	{$NXTFilter_rscomunicado1} 
ORDER BY 
	{$NXTSort_rscomunicado1}";

} else {
	
$query_rscomunicado1 = "
SELECT 
comunicado.IdComunicado, comunicado.data_criacao, comunicado.IdUsuario, comunicado.assunto, comunicado.prioridade, comunicado.data_reenvio, 
0 AS lido,
(SELECT COUNT(comunicado_destinatario.IdComunicadoDestinatario) FROM comunicado_destinatario WHERE comunicado_destinatario.IdComunicado = comunicado.IdComunicado and IdComunicadoHistorico IS NULL and comunicado_destinatario.responsavel = 0) AS comunicado_destinatario_contador, 
usuarios.nome AS usuario_nome 
FROM comunicado 
LEFT JOIN usuarios ON usuarios.IdUsuario = comunicado.IdUsuario 
WHERE $where and comunicado.IdUsuario = ".$row_usuario['IdUsuario']." and {$NXTFilter_rscomunicado1} 
ORDER BY {$NXTSort_rscomunicado1}";

}

$query_limit_rscomunicado1 = sprintf("%s LIMIT %d, %d", $query_rscomunicado1, $startRow_rscomunicado1, $maxRows_rscomunicado1);
$rscomunicado1 = mysql_query($query_limit_rscomunicado1, $conexao) or die(mysql_error());
$row_rscomunicado1 = mysql_fetch_assoc($rscomunicado1);

if (isset($_GET['totalRows_rscomunicado1'])) {
  $totalRows_rscomunicado1 = $_GET['totalRows_rscomunicado1'];
} else {
  $all_rscomunicado1 = mysql_query($query_rscomunicado1);
  $totalRows_rscomunicado1 = mysql_num_rows($all_rscomunicado1);
}
$totalPages_rscomunicado1 = ceil($totalRows_rscomunicado1/$maxRows_rscomunicado1)-1;
//End NeXTenesio3 Special List Recordset


$nav_listcomunicado1->checkBoundries();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"> 
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="content-language" content="pt-BR" />
<meta name="language" content="pt-BR" />
<meta name="robots" content="noindex,nofollow" />
<meta name="author" content="Juliano Martins - (34) 8805 3396" />
<meta name="copyright" content=" Copyright © Success Sistemas - Todos os direitos reservados." />
<title>Área do Parceiro - Success Sistemas</title>
<link href="../../css/guia_painel.css" rel="stylesheet" type="text/css">
<script src="../../js/jquery.js"></script>

<script src="../../js/jquery.metadata.js" ></script>
<script src="../../js/jquery.validate.1.15.js"></script>
<script src="../../js/jquery.maskedinput-1.2.2.min.js"></script> 

<link rel="stylesheet" href="../../css/thickbox.css" type="text/css" media="screen" />
<script type="text/javascript" src="../../js/thickbox.js"></script>

<link href="../../includes/skins/mxkollection3.css" rel="stylesheet" type="text/css" media="all" />
<script src="../../includes/common/js/base.js" type="text/javascript"></script>
<script src="../../includes/common/js/utility.js" type="text/javascript"></script>
<script src="../../includes/skins/style.js" type="text/javascript"></script>
<script src="../../includes/nxt/scripts/list.js" type="text/javascript"></script>
<script src="../../includes/nxt/scripts/list.js.php" type="text/javascript"></script>
<script type="text/javascript">
$NXT_LIST_SETTINGS = {
  duplicate_buttons: false,
  duplicate_navigation: false,
  row_effects: false,
  show_as_buttons: false,
  record_counter: false
}
</script>
<script type="text/javascript">
$(document).ready(function() {
	
	// mascara
	$('#filtro_data_criacao_inicio').mask('99-99-9999',{placeholder:" "});
	$('#filtro_data_criacao_fim').mask('99-99-9999',{placeholder:" "});
	// mascara - fim
	
	// acao - deletar
	$('.KT_delete_link').click(function(){
		var codigo_atual = $(this).attr('title');
		return confirm("Você tem certeza que deseja deletar o registro de código: " + codigo_atual + " ?");
	})
	// fim - acao - deletar
	
	// ocultar/exibir filtros
	$('#corpo_filtros').toggle();
	$('#cabecalho_filtros').click(function() {
		$('#corpo_filtros').toggle();
	});
	// fim - ocultar/exibir fitlros

	<? if($aba == "recebidos"){ ?>
	// kt_pk_comunicado_historico
    $("input[name=kt_pk_comunicado]").change(function() {
		
		var atual = $(this).val();
		
		$.post("comunicado_lido.php", 
		{IdComunicado: atual,
		IdUsuario: '<? echo $row_usuario['IdUsuario']; ?>'},
		function(valor){
			//alert(valor);
		});

		if ($(this).is(':checked') == true) {
			$('#KT_even_'+atual).css({'font-weight': 'bold'});
		} else {
			$('#KT_even_'+atual).css({'font-weight': 'normal'});
		}
		//$('#KT_even_'+atual).removeAttr({'font-weight': 'bold'});

    });
	// fim - kt_pk_comunicado_historico
	<? } ?>
	
});
</script>
<style type="text/css">
  /* Dynamic List row settings */
  .KT_col_IdComunicado {width:50px; overflow:hidden;}
  .KT_col_data_criacao {width:100px; overflow:hidden;}
  .KT_col_usuario_nome {width:80px; overflow:hidden;}
  .KT_col_assunto {width:230px; overflow:hidden;}
  .KT_col_distribuicao {width:80px; overflow:hidden;}
  .KT_col_prioridade {width: 40px; overflow:hidden;}


</style>
</head>
<body>
<div class="cabecalho"><? require_once('../padrao_cabecalho.php'); ?></div>

<!-- corpo -->
<div class="corpo">
	<div class="texto"> 
        <table width="100%" border="0" cellspacing="0" cellpadding="0">
            <tr>
            
                <td class="padrao_esquerda"><? require_once('../padrao_esquerda.php'); ?></td>
                                
                <td class="padrao_centro">                
                
                <!-- titulo -->
                <div class="titulo">
                <table width="100%" border="0" cellspacing="0" cellpadding="0">
                    <tr>
                        <td align="left">Comunicados <? if($aba == "recebidos"){ echo "Recebidos"; } else { echo "Enviados"; } ?> 
                          <?php
$nav_listcomunicado1->Prepare();
require("../../includes/nav/NAV_Text_Statistics.inc.php");
?>
</td>
                        <td align="right"><a href="javascript:history.go(-1);"><img src="../../imagens/botao_voltar.jpg" border="0" /></a></td>
                    </tr>
                </table>
                </div>
                <div class="caminho"><a href="../index.php">Página inicial</a> &gt;&gt; Comunicados</div>
                <!-- fim - titulo -->
                
                <div class="conteudo">
				
				<!-- filtro -->					
				<div style="margin-bottom: 20px;">
					<div id="cabecalho_filtros" style="padding: 7px 5px 5px 5px; margin-bottom: 5px; background-color: #DDD; color: #000; font-weight: bold; cursor: pointer;">Filtros</div>
					<div id="corpo_filtros">
					<form id="form_filtro" name="form_filtro" method="GET" enctype="multipart/form-data" action="<?php echo $editFormAction; ?>">
					
						<input type="hidden" id="aba" name="aba" value="<? if ( isset($_GET['aba']) ) { echo $_GET['aba']; } ?>" />

						<table width="100%" border="0" cellpadding="5" cellspacing="0" style="border: solid 1px #4F72B4;">
							<tr>
								<td width="33%">
								<!-- filtro_IdComunicado -->
								<strong>Núm:</strong><br>
								<input type="text" name="filtro_IdComunicado" id="filtro_IdComunicado" value="<? if ( isset($_GET['filtro_IdComunicado']) ) { echo $_GET['filtro_IdComunicado']; } ?>" style="width: 230px;">
								<!-- fim - filtro_IdComunicado -->
								</td>
								
								<td width="34%">
								<!-- filtro_data_criacao_inicio -->
								<strong>Data criação (inicial):</strong><br>
								<input type="text" name="filtro_data_criacao_inicio" id="filtro_data_criacao_inicio" value="<? if ( isset($_GET['filtro_data_criacao_inicio']) ) { echo $_GET['filtro_data_criacao_inicio']; } ?>" style="width: 230px;">
								<!-- inicio - filtro_data_criacao_inicio -->
								</td>
								
								<td width="33%">
								<!-- filtro_data_criacao_fim -->
								<strong>Data criação (final):</strong><br>
								<input type="text" name="filtro_data_criacao_fim" id="filtro_data_criacao_fim" value="<? if ( isset($_GET['filtro_data_criacao_fim']) ) { echo $_GET['filtro_data_criacao_fim']; } ?>" style="width: 230px;">
								<!-- fim - filtro_data_criacao_fim -->
								</td>

								
							</tr>
							
								<td width="33%">
								<!-- filtro_usuario_IdUsuario -->
								<strong>Remetente:</strong><br>
								<select name="filtro_usuario_IdUsuario" style="width: 230px">
								<option value=""
								<?php if (!(strcmp("", isset($_GET['filtro_usuario_IdUsuario'])))) {echo "selected=\"selected\"";} ?>>Escolha ...</option>
								<?php do {  ?>
								<option value="<?php echo $row_filtro_usuario_IdUsuario['IdUsuario']; ?>"
								<?php if ( (isset($_GET['filtro_usuario_IdUsuario'])) and (!(strcmp($row_filtro_usuario_IdUsuario['IdUsuario'], $_GET['filtro_usuario_IdUsuario']))) ) {echo "selected=\"selected\"";} ?>
								>
								<?php echo $row_filtro_usuario_IdUsuario['nome']; ?>
								</option>
								<?php
								} while ($row_filtro_usuario_IdUsuario = mysql_fetch_assoc($filtro_usuario_IdUsuario));
								$rows = mysql_num_rows($filtro_usuario_IdUsuario);
								if($rows > 0) {
								mysql_data_seek($filtro_usuario_IdUsuario, 0);
								$row_filtro_usuario_IdUsuario = mysql_fetch_assoc($filtro_usuario_IdUsuario);
								}
								?>
								</select>
								<!-- fim - filtro_usuario_IdUsuario -->
								</td>
								
								<td width="34%">
								<!-- filtro_assunto -->
								<strong>Assunto:</strong><br>
								<input type="text" name="filtro_assunto" id="filtro_assunto" value="<? if ( isset($_GET['filtro_assunto']) ) { echo $_GET['filtro_assunto']; } ?>" style="width: 230px;">
								<!-- fim - filtro_assunto -->
								</td>
								
								<td width="33%">
								<!-- filtro_prioridade -->
								<strong>Prioridade:</strong><br>
								<select name="filtro_prioridade" id="filtro_prioridade" style="width: 240px;">
								<option value="" <?php if (!(strcmp("", isset($_GET['filtro_prioridade'])))) {echo "selected=\"selected\"";} ?>>...</option>
								<option value="Alta" <?php if ( (isset($_GET['filtro_prioridade'])) and (!(strcmp('Alta', $_GET['filtro_prioridade']))) ) {echo "selected=\"selected\"";} ?>>Alta</option>
								<option value="Média" <?php if ( (isset($_GET['filtro_prioridade'])) and (!(strcmp('Média', $_GET['filtro_prioridade']))) ) {echo "selected=\"selected\"";} ?>>Média</option>
								<option value="Baixa" <?php if ( (isset($_GET['filtro_prioridade'])) and (!(strcmp('Baixa', $_GET['filtro_prioridade']))) ) {echo "selected=\"selected\"";} ?>>Baixa</option>
								</select>
								</td>
								<!-- fim - filtro_prioridade -->
								
							</tr>
							
							<tr>
								<td width="50%">
								<!-- filtro_tipo -->
								<strong>Tipo:</strong><br>
								<select name="filtro_tipo" id="filtro_tipo" style="width: 240px;">
								<option value="" <?php if (!(strcmp("", isset($_GET['filtro_tipo'])))) {echo "selected=\"selected\"";} ?>>...</option>
								<option value="c" <?php if ( (isset($_GET['filtro_tipo'])) and (!(strcmp('c', $_GET['filtro_tipo']))) ) {echo "selected=\"selected\"";} ?>>Comunicado</option>
								<option value="m" <?php if ( (isset($_GET['filtro_tipo'])) and (!(strcmp('m', $_GET['filtro_tipo']))) ) {echo "selected=\"selected\"";} ?>>Memorando</option>
								</select>
								<!-- fim - filtro_tipo -->
								</td>
								
								<td width="50%">
								<!-- filtro_lido -->
								<strong>Lido:</strong><br>
								<select name="filtro_lido" id="filtro_lido" style="width: 240px;">
								<option value="" <?php if (!(strcmp("", isset($_GET['filtro_lido'])))) {echo "selected=\"selected\"";} ?>>...</option>
								<option value="1" <?php if ( (isset($_GET['filtro_lido'])) and (!(strcmp(1, $_GET['filtro_lido']))) ) {echo "selected=\"selected\"";} ?>>Lido</option>
								<option value="0" <?php if ( (isset($_GET['filtro_lido'])) and (!(strcmp(0, $_GET['filtro_lido']))) ) {echo "selected=\"selected\"";} ?>>Não Lido</option>
								</select>
								<!-- fim - filtro_lido -->
								</td>
							</tr>
							
							<tr>
								<td colspan="3">
								<input type="submit" value="Filtrar" />
								</td>
							</tr>
						</table>
					
					</form>
					</div>
				</div>
				<!-- fim - filtro -->
                
                <? if($row_usuario['controle_comunicado'] == "Y"){ ?>
                    <div style="margin-bottom: 5px; font-weight: bold; text-align: right;">
                    <? if($aba == "recebidos"){ ?>
                        <a href="listar.php?aba=enviados">[+] Ver Enviados</a>
                    <? } else { ?>
                        <a href="listar.php">[+] Ver Recebidos</a>
                    <? } ?>
                    </div>
				<? } ?>

                <div class="KT_tng">
                                
                    <div class="KT_tnglist">
                    <form action="<?php echo KT_escapeAttribute(KT_getFullUri()); ?>" method="post" id="form1">
                      <div class="KT_options"> <a href="<?php echo $nav_listcomunicado1->getShowAllLink(); ?>"><?php echo NXT_getResource("Show"); ?>
                        <?php 
                    // Show IF Conditional region1
                    if (@$_GET['show_all_nav_listcomunicado1'] == 1) {
                    ?>
                          <?php echo $_SESSION['default_max_rows_nav_listcomunicado1']; ?>
                          <?php 
                    // else Conditional region1
                    } else { ?>
                          <?php echo NXT_getResource("all"); ?>
                          <?php } 
                    // endif Conditional region1
                    ?>
                    <?php echo NXT_getResource("records"); ?></a>
                      </div>
                      <table cellpadding="2" cellspacing="0" class="KT_tngtable">
                        <thead>
                          <tr class="KT_row_order">
                            <th>&nbsp;</th>
                            
                            <th id="IdComunicado" class="KT_sorter KT_col_IdComunicado <?php echo $tso_listcomunicado1->getSortIcon('comunicado.IdComunicado'); ?>">
                            <a href="<?php echo $tso_listcomunicado1->getSortLink('comunicado.IdComunicado'); ?>">Núm</a>
                            </th>
                            
                            <th id="data_criacao" class="KT_sorter KT_col_data_criacao <?php echo $tso_listcomunicado1->getSortIcon('comunicado.data_criacao'); ?>">
                            <a href="<?php echo $tso_listcomunicado1->getSortLink('comunicado.data_criacao'); ?>">Criação</a>
                            </th>
                            
                            <th id="usuario_nome" class="KT_sorter KT_col_usuario_nome <?php echo $tso_listcomunicado1->getSortIcon('usuarios.nome'); ?>">
                            <a href="<?php echo $tso_listcomunicado1->getSortLink('usuarios.nome'); ?>">
                            Remetente
                            </a>
                            </th>
                            
                            <th id="assunto" class="KT_sorter KT_col_assunto <?php echo $tso_listcomunicado1->getSortIcon('comunicado.assunto'); ?>">
                            <a href="<?php echo $tso_listcomunicado1->getSortLink('comunicado.assunto'); ?>">Assunto</a>
                            </th>
                            
                            <th id="distribuicao" class="KT_sorter KT_col_distribuicao">Destinatário</th>
                            
                            <th id="prioridade" class="KT_sorter KT_col_prioridade <?php echo $tso_listcomunicado1->getSortIcon('comunicado.prioridade'); ?>">
                            <a href="<?php echo $tso_listcomunicado1->getSortLink('comunicado.prioridade'); ?>">Prioridade</a>
                            </th>
                            
                            <th>&nbsp;</th>
                          </tr>
                          <?php 
                    // Show IF Conditional region3
                    if (@$_SESSION['has_filter_tfi_listcomunicado1'] == 1) {
                    ?>
                            <tr class="KT_row_filter">
                              <td>&nbsp;</td>
                               <td>&nbsp;</td>
                               <td>&nbsp;</td>
                              <td>&nbsp;</td>
                              <td>&nbsp;</td>
                              <td>&nbsp;</td>
                              <td>&nbsp;</td>
                            </tr>
                            <?php } 
                    // endif Conditional region3
                    ?>
                        </thead>
                        <tbody>
                          <?php if ($totalRows_rscomunicado1 == 0) { // Show if recordset empty ?>
                            <tr>
                              <td colspan="7"><?php echo NXT_getResource("The table is empty or the filter you've selected is too restrictive."); ?></td>
                            </tr>
                            <?php } // Show if recordset empty ?>
                          <?php if ($totalRows_rscomunicado1 > 0) { // Show if recordset not empty ?>
                            <?php do { ?>
                              <tr class="<?php echo @$cnt1++%2==0 ? "" : "KT_even"; ?>" id="KT_even_<?php echo $row_rscomunicado1['IdComunicado']; ?>" <?php if($row_rscomunicado1['lido'] == 0){ ?> style="font-weight: bold;"<? } ?> >
                                <td>
                                <? if($aba == "recebidos"){ ?>
                                <input type="checkbox" id="kt_pk_comunicado" name="kt_pk_comunicado" class="id_checkbox" value="<?php echo $row_rscomunicado1['IdComunicado']; ?>" 
								<? if($row_rscomunicado1['lido'] == 0){ ?>checked="checked"<? } ?> 
                                />
                                <? } ?>
                                <input type="hidden" name="IdComunicado" class="id_field" value="<?php echo $row_rscomunicado1['IdComunicado']; ?>" /> 
                                </td>
                                <td><div class="KT_col_IdComunicado"><?php echo $row_rscomunicado1['IdComunicado']; ?></div></td>
                                <td><div class="KT_col_data_criacao"><?php echo date('d-m-Y H:i', strtotime($row_rscomunicado1['data_criacao'])); ?></div></td>
                                <td><div class="KT_col_IdUsuario" title="<?php echo $row_rscomunicado1['usuario_nome']; ?>"><?php echo KT_FormatForList($row_rscomunicado1['usuario_nome'], 20); ?></div></td>
                                <td><div class="KT_col_assunto" title="<?php echo $row_rscomunicado1['assunto']; ?>"><?php if($row_rscomunicado1['data_reenvio'] <> NULL){ ?>*<? } ?><?php echo KT_FormatForList($row_rscomunicado1['assunto'], 30); ?></div></td>
                                <td>
                                <div title="<? echo $row_rscomunicado1['comunicado_destinatario_contador']; ?>">
                                <?php echo KT_FormatForList($row_rscomunicado1['comunicado_destinatario_contador'], 20); ?>
                                </div>
                                </td>
                                <td><div class="KT_col_prioridade"><?php echo $row_rscomunicado1['prioridade']; ?></div></td>
                                <td>
                                
                                <a class="KT_edit_link thickbox" href="../padrao/comunicado_detalhe.php?IdComunicado=<?php echo $row_rscomunicado1['IdComunicado']; ?>&placeValuesBeforeTB_=savedValues&TB_iframe=true&height=<? echo $suporte_editar_tabela_height; ?>&width=<? echo $suporte_editar_tabela_width; ?>&modal=true"><?php echo NXT_getResource("Acessar"); ?></a> 
                    
                                </td>
                              </tr>
                              <?php } while ($row_rscomunicado1 = mysql_fetch_assoc($rscomunicado1)); ?>
                            <?php } // Show if recordset not empty ?>
                        </tbody>
                      </table>
                      <div class="KT_bottomnav">
                        <div>
                          <?php
                            $nav_listcomunicado1->Prepare();
                            require("../../includes/nav/NAV_Text_Navigation.inc.php");
                          ?>
                        </div>
                      </div>
                      
                      <div class="KT_bottombuttons">
                        <span>&nbsp;</span>
                        <? if($row_usuario['controle_comunicado'] == "Y"){ ?>
                        <a class="KT_additem_op_link" href="tabela.php" onClick="return nxt_list_additem(this)"><?php echo NXT_getResource("Inserir novo comunicado"); ?></a> 
                        <? } ?>
                      </div>
                        
                    </form>
                    </div>
                    <br class="clearfixplain" />
                    
                    <? if($aba == "recebidos"){ ?>
                    <input type="checkbox" id="kt_pk_comunicado" checked="checked" disabled="disabled" style="margin-top: 10px;" /> [Marcado] = Não Lido
                    <? } ?>
                    
                </div>
                
                </div>
                
                 
                </td>
                
            </tr>
        </table>
  	</div>
</div>
<!-- fim - corpo -->

<div class="rodape"><? require_once('../padrao_rodape.php'); ?></div>

</body>
</html>
<?php
mysql_free_result($filtro_usuario_IdUsuario);
mysql_free_result($usuario);
mysql_free_result($rscomunicado1);
?>
