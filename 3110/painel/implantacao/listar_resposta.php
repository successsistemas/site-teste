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

// implantacao_pergunta
$colname_implantacao_pergunta = "-1";
if (isset($_GET['IdImplantacaoPergunta'])) {
  $colname_implantacao_pergunta = $_GET['IdImplantacaoPergunta'];
}
mysql_select_db($database_conexao, $conexao);
$query_implantacao_pergunta = sprintf("SELECT * FROM implantacao_pergunta WHERE IdImplantacaoPergunta = %s", GetSQLValueString($colname_implantacao_pergunta, "int"));
$implantacao_pergunta = mysql_query($query_implantacao_pergunta, $conexao) or die(mysql_error());
$row_implantacao_pergunta = mysql_fetch_assoc($implantacao_pergunta);
$totalRows_implantacao_pergunta = mysql_num_rows($implantacao_pergunta);
// fim - implantacao_pergunta

// Load the common classes
require_once('../../includes/common/KT_common.php');

// Load the required classes
require_once('../../includes/tfi/TFI.php');
require_once('../../includes/tso/TSO.php');
require_once('../../includes/nav/NAV.php');

// Make unified connection variable
$conn_conexao = new KT_connection($conexao, $database_conexao);

// Filter
$tfi_listimplantacao_resposta1 = new TFI_TableFilter($conn_conexao, "tfi_listimplantacao_resposta1");
$tfi_listimplantacao_resposta1->addColumn("implantacao_resposta.IdImplantacaoResposta", "NUMERIC_TYPE", "IdImplantacaoResposta", "=");
$tfi_listimplantacao_resposta1->addColumn("implantacao_resposta.data", "DATE_TYPE", "data", "=");
$tfi_listimplantacao_resposta1->addColumn("implantacao_resposta.descricao", "STRING_TYPE", "descricao", "%");
$tfi_listimplantacao_resposta1->Execute();

// Sorter
$tso_listimplantacao_resposta1 = new TSO_TableSorter("rsimplantacao_resposta1", "tso_listimplantacao_resposta1");
$tso_listimplantacao_resposta1->addColumn("implantacao_resposta.IdImplantacaoResposta");
$tso_listimplantacao_resposta1->addColumn("implantacao_resposta.data");
$tso_listimplantacao_resposta1->addColumn("implantacao_resposta.descricao");
$tso_listimplantacao_resposta1->setDefault("implantacao_resposta.IdImplantacaoResposta");
$tso_listimplantacao_resposta1->Execute();

// Navigation
$nav_listimplantacao_resposta1 = new NAV_Regular("nav_listimplantacao_resposta1", "rsimplantacao_resposta1", "../../", $_SERVER['PHP_SELF'], 30);

//NeXTenesio3 Special List Recordset
$maxRows_rsimplantacao_resposta1 = $_SESSION['max_rows_nav_listimplantacao_resposta1'];
$pageNum_rsimplantacao_resposta1 = 0;
if (isset($_GET['pageNum_rsimplantacao_resposta1'])) {
  $pageNum_rsimplantacao_resposta1 = $_GET['pageNum_rsimplantacao_resposta1'];
}
$startRow_rsimplantacao_resposta1 = $pageNum_rsimplantacao_resposta1 * $maxRows_rsimplantacao_resposta1;

// Defining List Recordset variable
$NXTFilter_rsimplantacao_resposta1 = "1=1";
if (isset($_SESSION['filter_tfi_listimplantacao_resposta1'])) {
  $NXTFilter_rsimplantacao_resposta1 = $_SESSION['filter_tfi_listimplantacao_resposta1'];
}
// Defining List Recordset variable
$NXTSort_rsimplantacao_resposta1 = "implantacao_resposta.IdImplantacaoResposta";
if (isset($_SESSION['sorter_tso_listimplantacao_resposta1'])) {
  $NXTSort_rsimplantacao_resposta1 = $_SESSION['sorter_tso_listimplantacao_resposta1'];
}
mysql_select_db($database_conexao, $conexao);

$query_rsimplantacao_resposta1 = "SELECT implantacao_resposta.IdImplantacaoResposta, implantacao_resposta.data, implantacao_resposta.descricao FROM implantacao_resposta WHERE IdImplantacaoPergunta = ".sprintf("%s", GetSQLValueString($row_implantacao_pergunta['IdImplantacaoPergunta'], "int"))." and {$NXTFilter_rsimplantacao_resposta1} ORDER BY {$NXTSort_rsimplantacao_resposta1}";
$query_limit_rsimplantacao_resposta1 = sprintf("%s LIMIT %d, %d", $query_rsimplantacao_resposta1, $startRow_rsimplantacao_resposta1, $maxRows_rsimplantacao_resposta1);
$rsimplantacao_resposta1 = mysql_query($query_limit_rsimplantacao_resposta1, $conexao) or die(mysql_error());
$row_rsimplantacao_resposta1 = mysql_fetch_assoc($rsimplantacao_resposta1);

if (isset($_GET['totalRows_rsimplantacao_resposta1'])) {
  $totalRows_rsimplantacao_resposta1 = $_GET['totalRows_rsimplantacao_resposta1'];
} else {
  $all_rsimplantacao_resposta1 = mysql_query($query_rsimplantacao_resposta1);
  $totalRows_rsimplantacao_resposta1 = mysql_num_rows($all_rsimplantacao_resposta1);
}
$totalPages_rsimplantacao_resposta1 = ceil($totalRows_rsimplantacao_resposta1/$maxRows_rsimplantacao_resposta1)-1;
//End NeXTenesio3 Special List Recordset

$nav_listimplantacao_resposta1->checkBoundries();
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
  record_counter: true
}
</script>
<style type="text/css">
  /* Dynamic List row settings */
  .KT_col_data {width: 120px; overflow:hidden;}
  .KT_col_descricao {width:400px; overflow:hidden;}
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
                        <td align="left">Implantação - Pergunta - Resposta <?php
  $nav_listimplantacao_resposta1->Prepare();
  require("../../includes/nav/NAV_Text_Statistics.inc.php");
?></td>
                        <td align="right"><a href="javascript:history.go(-1);"><img src="../../imagens/botao_voltar.jpg" border="0" /></a></td>
                    </tr>
                </table>
                </div>
                <div class="caminho"><a href="../index.php">Página inicial</a> &gt;&gt; Implantação - Pergunta - Resposta</div>
                <!-- fim - titulo -->
                
                <div class="conteudo">
                
<div class="KT_tng">
  
    <div style="margin-top: 5px; margin-bottom: 5px; font-weight:bold; color:#C00; font-size: 16px;">
    Pergunta: <?php echo $row_implantacao_pergunta['descricao']; ?>
    </div>
    
    <div class="KT_tnglist">
    <form action="<?php echo KT_escapeAttribute(KT_getFullUri()); ?>" method="post" id="form1">
      <div class="KT_options"> <a href="<?php echo $nav_listimplantacao_resposta1->getShowAllLink(); ?>"><?php echo NXT_getResource("Show"); ?>
        <?php 
    // Show IF Conditional region1
    if (@$_GET['show_all_nav_listimplantacao_resposta1'] == 1) {
    ?>
          <?php echo $_SESSION['default_max_rows_nav_listimplantacao_resposta1']; ?>
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
    if (@$_SESSION['has_filter_tfi_listimplantacao_resposta1'] == 1) {
    ?>
          <a href="<?php echo $tfi_listimplantacao_resposta1->getResetFilterLink(); ?>"><?php echo NXT_getResource("Reset filter"); ?></a>
          <?php 
    // else Conditional region2
    } else { ?>
          <a href="<?php echo $tfi_listimplantacao_resposta1->getShowFilterLink(); ?>"><?php echo NXT_getResource("Show filter"); ?></a>
          <?php } 
    // endif Conditional region2
    ?>
      </div>
      <table cellpadding="2" cellspacing="0" class="KT_tngtable">
        <thead>
          <tr class="KT_row_order">
            <th> <input type="checkbox" name="KT_selAll" id="KT_selAll"/>
            </th>
            <th id="data" class="KT_sorter KT_col_data <?php echo $tso_listimplantacao_resposta1->getSortIcon('implantacao_resposta.data'); ?>"> <a href="<?php echo $tso_listimplantacao_resposta1->getSortLink('implantacao_resposta.data'); ?>">Data</a> </th>
            <th id="descricao" class="KT_sorter KT_col_descricao <?php echo $tso_listimplantacao_resposta1->getSortIcon('implantacao_resposta.descricao'); ?>"> <a href="<?php echo $tso_listimplantacao_resposta1->getSortLink('implantacao_resposta.descricao'); ?>">Resposta</a> </th>
            <th>&nbsp;</th>
          </tr>
          <?php 
    // Show IF Conditional region3
    if (@$_SESSION['has_filter_tfi_listimplantacao_resposta1'] == 1) {
    ?>
            <tr class="KT_row_filter">
              <td>&nbsp;</td>
              <td><input type="text" name="tfi_listimplantacao_resposta1_data" id="tfi_listimplantacao_resposta1_data" value="<?php echo @$_SESSION['tfi_listimplantacao_resposta1_data']; ?>" size="10" maxlength="22" /></td>
              <td><input type="text" name="tfi_listimplantacao_resposta1_descricao" id="tfi_listimplantacao_resposta1_descricao" value="<?php echo KT_escapeAttribute(@$_SESSION['tfi_listimplantacao_resposta1_descricao']); ?>" size="60" maxlength="50" /></td>
              <td><input type="submit" name="tfi_listimplantacao_resposta1" value="<?php echo NXT_getResource("Filter"); ?>" /></td>
            </tr>
            <?php } 
    // endif Conditional region3
    ?>
        </thead>
        <tbody>
          <?php if ($totalRows_rsimplantacao_resposta1 == 0) { // Show if recordset empty ?>
            <tr>
              <td colspan="4"><?php echo NXT_getResource("The table is empty or the filter you've selected is too restrictive."); ?></td>
            </tr>
            <?php } // Show if recordset empty ?>
          <?php if ($totalRows_rsimplantacao_resposta1 > 0) { // Show if recordset not empty ?>
            <?php do { ?>
              <tr class="<?php echo @$cnt1++%2==0 ? "" : "KT_even"; ?>">
                <td><input type="checkbox" name="kt_pk_implantacao_resposta" class="id_checkbox" value="<?php echo $row_rsimplantacao_resposta1['IdImplantacaoResposta']; ?>" />
                  <input type="hidden" name="IdImplantacaoResposta" class="id_field" value="<?php echo $row_rsimplantacao_resposta1['IdImplantacaoResposta']; ?>" /></td>
                <td><div class="KT_col_data"><?php echo KT_formatDate($row_rsimplantacao_resposta1['data']); ?></div></td>
                <td><div class="KT_col_descricao"><?php echo KT_FormatForList($row_rsimplantacao_resposta1['descricao'], 60); ?></div></td>
                <td><a class="KT_edit_link" href="tabela_resposta.php?IdImplantacaoPergunta=<?php echo $row_implantacao_pergunta['IdImplantacaoPergunta']; ?>&IdImplantacaoResposta=<?php echo $row_rsimplantacao_resposta1['IdImplantacaoResposta']; ?>&amp;KT_back=1"><?php echo NXT_getResource("edit_one"); ?></a> <a class="KT_delete_link" href="#delete"><?php echo NXT_getResource("delete_one"); ?></a></td>
              </tr>
              <?php } while ($row_rsimplantacao_resposta1 = mysql_fetch_assoc($rsimplantacao_resposta1)); ?>
            <?php } // Show if recordset not empty ?>
        </tbody>
      </table>
      <div class="KT_bottomnav">
        <div>
          <?php
            $nav_listimplantacao_resposta1->Prepare();
            require("../../includes/nav/NAV_Text_Navigation.inc.php");
          ?>
        </div>
      </div>
      <div class="KT_bottombuttons">
        <div class="KT_operations"> <a class="KT_edit_op_link" href="#" onClick="nxt_list_edit_link_form(this); return false;"><?php echo NXT_getResource(""); ?></a> <a class="KT_delete_op_link" href="#" onClick="nxt_list_delete_link_form(this); return false;"><?php echo NXT_getResource("delete_all"); ?></a> </div>
        <span>&nbsp;</span>
        <select name="no_new" id="no_new">
          <option value="1">1</option>
          <option value="2">2</option>
          <option value="3">3</option>
          <option value="4">4</option>
          <option value="5">5</option>
          <option value="6">6</option>
          <option value="7">7</option>
          <option value="8">8</option>
          <option value="9">9</option>
          <option value="10">10</option>
        </select>
        <a class="KT_additem_op_link" href="tabela_resposta.php?IdImplantacaoPergunta=<?php echo $row_implantacao_pergunta['IdImplantacaoPergunta']; ?>&KT_back=1" onClick="return nxt_list_additem(this)"><?php echo NXT_getResource("add new"); ?></a> </div>
    </form>
    </div>
    <br class="clearfixplain" />
    
    <div style="border: 2px solid #CCC; padding: 5px; margin-top: 10px;">
    <a href="listar_pergunta.php">Listar perguntas</a> | 
    <a href="tabela_pergunta.php">Inserir nova pergunta</a>
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
mysql_free_result($rsimplantacao_resposta1);
mysql_free_result($implantacao_pergunta);
?>
