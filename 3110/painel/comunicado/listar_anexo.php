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

if(
$row_usuario['controle_comunicado'] <> 'Y' and 
$row_usuario['controle_memorando'] <> 'Y' 
){
	header("Location: ../index.php"); 
	exit;
}

// comunicado
$colname_comunicado = "-1";
if (isset($_GET['IdComunicado'])) {
  $colname_comunicado = $_GET['IdComunicado'];
}
mysql_select_db($database_conexao, $conexao);
$query_comunicado = sprintf("
SELECT 
(SELECT COUNT(comunicado_destinatario.IdComunicadoDestinatario) FROM comunicado_destinatario WHERE comunicado_destinatario.IdComunicado = comunicado.IdComunicado and comunicado_destinatario.IdUsuario = ".$row_usuario['IdUsuario']." and IdComunicadoHistorico IS NULL) AS comunicado_destinatario_envolvimento, 
comunicado.*, 
usuarios.nome AS usuario_nome, 
(SELECT COUNT(comunicado_destinatario.IdComunicadoDestinatario) FROM comunicado_destinatario WHERE comunicado_destinatario.IdComunicado = comunicado.IdComunicado and IdComunicadoHistorico IS NULL and comunicado_destinatario.responsavel = 0) AS comunicado_destinatario_contador 
FROM comunicado 
LEFT JOIN usuarios ON usuarios.IdUsuario = comunicado.IdUsuario 
WHERE IdComunicado = %s", 
GetSQLValueString($colname_comunicado, "int"));
$comunicado = mysql_query($query_comunicado, $conexao) or die(mysql_error());
$row_comunicado = mysql_fetch_assoc($comunicado);
$totalRows_comunicado = mysql_num_rows($comunicado);
// fim - comunicado

// Load the common classes
require_once('../../includes/common/KT_common.php');

// Load the required classes
require_once('../../includes/tfi/TFI.php');
require_once('../../includes/tso/TSO.php');
require_once('../../includes/nav/NAV.php');

// Make unified connection variable
$conn_conexao = new KT_connection($conexao, $database_conexao);

// Filter
$tfi_listcomunicado_anexo1 = new TFI_TableFilter($conn_conexao, "tfi_listcomunicado_anexo1");
$tfi_listcomunicado_anexo1->addColumn("comunicado_anexo.IdComunicadoAnexo", "NUMERIC_TYPE", "IdComunicadoAnexo", "=");
$tfi_listcomunicado_anexo1->addColumn("comunicado_anexo.data_criacao", "DATE_TYPE", "data_criacao", "=");
$tfi_listcomunicado_anexo1->addColumn("comunicado_anexo.arquivo", "STRING_TYPE", "arquivo", "%");
$tfi_listcomunicado_anexo1->Execute();

// Sorter
$tso_listcomunicado_anexo1 = new TSO_TableSorter("rscomunicado_anexo1", "tso_listcomunicado_anexo1");
$tso_listcomunicado_anexo1->addColumn("comunicado_anexo.IdComunicadoAnexo");
$tso_listcomunicado_anexo1->addColumn("comunicado_anexo.data_criacao");
$tso_listcomunicado_anexo1->addColumn("comunicado_anexo.arquivo");
$tso_listcomunicado_anexo1->setDefault("comunicado_anexo.IdComunicadoAnexo DESC");
$tso_listcomunicado_anexo1->Execute();

// Navigation
$nav_listcomunicado_anexo1 = new NAV_Regular("nav_listcomunicado_anexo1", "rscomunicado_anexo1", "../../", $_SERVER['PHP_SELF'], 30);

//NeXTenesio3 Special List Recordset
$maxRows_rscomunicado_anexo1 = $_SESSION['max_rows_nav_listcomunicado_anexo1'];
$pageNum_rscomunicado_anexo1 = 0;
if (isset($_GET['pageNum_rscomunicado_anexo1'])) {
  $pageNum_rscomunicado_anexo1 = $_GET['pageNum_rscomunicado_anexo1'];
}
$startRow_rscomunicado_anexo1 = $pageNum_rscomunicado_anexo1 * $maxRows_rscomunicado_anexo1;

// Defining List Recordset variable
$NXTFilter_rscomunicado_anexo1 = "1=1";
if (isset($_SESSION['filter_tfi_listcomunicado_anexo1'])) {
  $NXTFilter_rscomunicado_anexo1 = $_SESSION['filter_tfi_listcomunicado_anexo1'];
}
// Defining List Recordset variable
$NXTSort_rscomunicado_anexo1 = "comunicado_anexo.IdComunicadoAnexo DESC";
if (isset($_SESSION['sorter_tso_listcomunicado_anexo1'])) {
  $NXTSort_rscomunicado_anexo1 = $_SESSION['sorter_tso_listcomunicado_anexo1'];
}
mysql_select_db($database_conexao, $conexao);

$query_rscomunicado_anexo1 = "
SELECT 
comunicado_anexo.IdComunicadoAnexo, comunicado_anexo.data_criacao, comunicado_anexo.arquivo, 
usuarios.nome AS usuario_nome 
FROM comunicado_anexo 
LEFT JOIN usuarios ON usuarios.IdUsuario = comunicado_anexo.IdUsuario 
WHERE comunicado_anexo.IdComunicado = ".sprintf("%s", GetSQLValueString($row_comunicado['IdComunicado'], "int"))." and {$NXTFilter_rscomunicado_anexo1} 
ORDER BY {$NXTSort_rscomunicado_anexo1}";
$query_limit_rscomunicado_anexo1 = sprintf("%s LIMIT %d, %d", $query_rscomunicado_anexo1, $startRow_rscomunicado_anexo1, $maxRows_rscomunicado_anexo1);
$rscomunicado_anexo1 = mysql_query($query_limit_rscomunicado_anexo1, $conexao) or die(mysql_error());
$row_rscomunicado_anexo1 = mysql_fetch_assoc($rscomunicado_anexo1);

if (isset($_GET['totalRows_rscomunicado_anexo1'])) {
  $totalRows_rscomunicado_anexo1 = $_GET['totalRows_rscomunicado_anexo1'];
} else {
  $all_rscomunicado_anexo1 = mysql_query($query_rscomunicado_anexo1);
  $totalRows_rscomunicado_anexo1 = mysql_num_rows($all_rscomunicado_anexo1);
}
$totalPages_rscomunicado_anexo1 = ceil($totalRows_rscomunicado_anexo1/$maxRows_rscomunicado_anexo1)-1;
//End NeXTenesio3 Special List Recordset

$nav_listcomunicado_anexo1->checkBoundries();
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
<style type="text/css">
  /* Dynamic List row settings */
  .KT_col_data_criacao {width: 120px; overflow:hidden;}
  .KT_col_IdUsuario {width:150px; overflow:hidden;}
  .KT_col_arquivo {width:360px; overflow:hidden;}
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
                        <td align="left">Anexos de comunicado<?php
  $nav_listcomunicado_anexo1->Prepare();
  require("../../includes/nav/NAV_Text_Statistics.inc.php");
?></td>
                        <td align="right"><a href="javascript:history.go(-1);"><img src="../../imagens/botao_voltar.jpg" border="0" /></a></td>
                    </tr>
                </table>
                </div>
                <div class="caminho">
                <a href="../index.php">Página inicial</a> &gt;&gt; 
                <a href="../comunicado/listar.php">Comunicados</a> &gt;&gt; 
                Anexos
                </div>
                <!-- fim - titulo -->
                
                <div class="conteudo">
                
<div class="KT_tng">
  
    <div style="margin-top: 5px; margin-bottom: 5px; font-weight:bold; font-size: 16px;">
    <?php echo $row_comunicado['assunto']; ?>
    </div>
    Criação: <? echo date('d-m-Y H:i', strtotime($row_comunicado['data_criacao'])); ?> | 
    Remetente: <? echo $row_comunicado['usuario_nome']; ?> | 
    Distribuição: 
	<?php if($row_comunicado['comunicado_destinatario_contador'] == 1){ ?>Individual<? } else { ?>Coletivo<? } ?>
    (<?php echo $row_comunicado['comunicado_destinatario_contador']; ?>) 
     | 
    Prioridade: <? echo $row_comunicado['prioridade']; ?> | 
    Data resposta: <? echo date('d-m-Y', strtotime($row_comunicado['data_limite'])); ?> 
    <br>
    
    <div class="KT_tnglist">
    <form action="<?php echo KT_escapeAttribute(KT_getFullUri()); ?>" method="post" id="form1">
      <div class="KT_options"> <a href="<?php echo $nav_listcomunicado_anexo1->getShowAllLink(); ?>"><?php echo NXT_getResource("Show"); ?>
        <?php 
    // Show IF Conditional region1
    if (@$_GET['show_all_nav_listcomunicado_anexo1'] == 1) {
    ?>
          <?php echo $_SESSION['default_max_rows_nav_listcomunicado_anexo1']; ?>
          <?php 
    // else Conditional region1
    } else { ?>
          <?php echo NXT_getResource("all"); ?>
          <?php } 
    // endif Conditional region1
    ?>
    <?php echo NXT_getResource("records"); ?></a> &nbsp;
        &nbsp;
        <?php 
    // Show IF Conditional region2
    if (@$_SESSION['has_filter_tfi_listcomunicado_anexo1'] == 1) {
    ?>
          <a href="<?php echo $tfi_listcomunicado_anexo1->getResetFilterLink(); ?>"><?php echo NXT_getResource("Reset filter"); ?></a>
          <?php 
    // else Conditional region2
    } else { ?>
          <a href="<?php echo $tfi_listcomunicado_anexo1->getShowFilterLink(); ?>"><?php echo NXT_getResource("Show filter"); ?></a>
          <?php } 
    // endif Conditional region2
    ?>
      </div>
      <table cellpadding="2" cellspacing="0" class="KT_tngtable">
        <thead>
          <tr class="KT_row_order">
            <th> <input type="checkbox" name="KT_selAll" id="KT_selAll"/>
            </th>
            <th id="data_criacao" class="KT_sorter KT_col_data_criacao <?php echo $tso_listcomunicado_anexo1->getSortIcon('comunicado_anexo.data_criacao'); ?>"> <a href="<?php echo $tso_listcomunicado_anexo1->getSortLink('comunicado_anexo.data_criacao'); ?>">Data</a> </th>
            <th id="IdUsuario" class="KT_sorter KT_col_IdUsuario <?php echo $tso_listcomunicado_anexo1->getSortIcon('comunicado_anexo.IdUsuario'); ?>">Usuário</th>
            <th id="arquivo" class="KT_sorter KT_col_arquivo <?php echo $tso_listcomunicado_anexo1->getSortIcon('comunicado_anexo.arquivo'); ?>"> <a href="<?php echo $tso_listcomunicado_anexo1->getSortLink('comunicado_anexo.arquivo'); ?>">Arquivo</a> </th>
            <th>&nbsp;</th>
          </tr>
          <?php 
    // Show IF Conditional region3
    if (@$_SESSION['has_filter_tfi_listcomunicado_anexo1'] == 1) {
    ?>
            <tr class="KT_row_filter">
              <td>&nbsp;</td>
              <td><input type="text" name="tfi_listcomunicado_anexo1_data_criacao" id="tfi_listcomunicado_anexo1_data_criacao" value="<?php echo @$_SESSION['tfi_listcomunicado_anexo1_data_criacao']; ?>" size="10" maxlength="22" /></td>
              <td>&nbsp;</td>
              <td><input type="text" name="tfi_listcomunicado_anexo1_arquivo" id="tfi_listcomunicado_anexo1_arquivo" value="<?php echo KT_escapeAttribute(@$_SESSION['tfi_listcomunicado_anexo1_arquivo']); ?>" size="60" maxlength="50" /></td>
              <td><input type="submit" name="tfi_listcomunicado_anexo1" value="<?php echo NXT_getResource("Filter"); ?>" /></td>
            </tr>
            <?php } 
    // endif Conditional region3
    ?>
        </thead>
        <tbody>
          <?php if ($totalRows_rscomunicado_anexo1 == 0) { // Show if recordset empty ?>
            <tr>
              <td colspan="5"><?php echo NXT_getResource("The table is empty or the filter you've selected is too restrictive."); ?></td>
            </tr>
            <?php } // Show if recordset empty ?>
          <?php if ($totalRows_rscomunicado_anexo1 > 0) { // Show if recordset not empty ?>
            <?php do { ?>
              <tr class="<?php echo @$cnt1++%2==0 ? "" : "KT_even"; ?>">
                <td><input type="checkbox" name="kt_pk_comunicado_anexo" class="id_checkbox" value="<?php echo $row_rscomunicado_anexo1['IdComunicadoAnexo']; ?>" />
                  <input type="hidden" name="IdComunicadoAnexo" class="id_field" value="<?php echo $row_rscomunicado_anexo1['IdComunicadoAnexo']; ?>" /></td>
                <td><div class="KT_col_data_criacao"><?php echo KT_formatDate($row_rscomunicado_anexo1['data_criacao']); ?></div></td>
                <td><div class="KT_col_IdUsuario"><?php echo KT_FormatForList($row_rscomunicado_anexo1['usuario_nome'], 60); ?></div></td>
                <td><div class="KT_col_arquivo"><a href="../../arquivos/comunicado/<?php echo $row_rscomunicado_anexo1['arquivo']; ?>" target="_blank"><?php echo KT_FormatForList($row_rscomunicado_anexo1['arquivo'], 60); ?></a></div></td>
                <td>
                
                <? if($row_comunicado['comunicado_destinatario_envolvimento'] == 1 and strtotime($row_comunicado['data_limite']) >= strtotime(date('Y-m-d H:i:s'))){ ?>
                
                <a class="KT_edit_link" href="tabela_anexo.php?IdComunicado=<?php echo $row_comunicado['IdComunicado']; ?>&IdComunicadoAnexo=<?php echo $row_rscomunicado_anexo1['IdComunicadoAnexo']; ?>&amp;KT_back=1"><?php echo NXT_getResource("Editar"); ?></a>
                                  
                 <a class="KT_delete_link" href="#delete"><?php echo NXT_getResource("delete_one"); ?></a>
                 
                 <? } ?>                
                
                </td>
              </tr>
              <?php } while ($row_rscomunicado_anexo1 = mysql_fetch_assoc($rscomunicado_anexo1)); ?>
            <?php } // Show if recordset not empty ?>
        </tbody>
      </table>
      <div class="KT_bottomnav">
        <div>
          <?php
            $nav_listcomunicado_anexo1->Prepare();
            require("../../includes/nav/NAV_Text_Navigation.inc.php");
          ?>
        </div>
      </div>
      <div class="KT_bottombuttons">
        <div class="KT_operations"> 
        <a class="KT_edit_op_link" href="#" onClick="nxt_list_edit_link_form(this); return false;"><?php echo NXT_getResource(""); ?></a>         
        </div>
        
        <? if($row_comunicado['comunicado_destinatario_envolvimento'] == 1 and strtotime($row_comunicado['data_limite']) >= strtotime(date('Y-m-d H:i:s'))){ ?>
        <span>&nbsp;</span>
        <input type="hidden" name="no_new" id="no_new" value="1">
        <a class="KT_additem_op_link" href="tabela_anexo.php?IdComunicado=<?php echo $row_comunicado['IdComunicado']; ?>&KT_back=1" onClick="return nxt_list_additem(this)"><?php echo NXT_getResource("add new"); ?></a> </div>
        <? } ?>
        
    </form>
    </div>
    <br class="clearfixplain" />
    
    <div style="border: 2px solid #CCC; padding: 5px; margin-top: 10px;">
    <a href="listar.php">Listar comunicados</a>
    </div>
    
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
mysql_free_result($usuario);
mysql_free_result($rscomunicado_anexo1);
mysql_free_result($comunicado);
?>